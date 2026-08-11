<?php
// Clash Boost (form ID 19) is always a Play with Booster / Duo order.
if ((int)($data['form_id'] ?? 0) === 19) {
  $data['is_duo'] = 1;
}

/**
 * CLIENT Order View (copy/paste) — Premium redesign
 * Layout:
 *  - Left: Booster status bar (waiting/assigned) + Premium Chat (same as booster/admin) + Overview
 *  - Right: Account (top) + Options (below) + Notes (below)
 */

$boosterRow = !empty($data['booster_id']) ? db_get_row('boosters', ['id' => $data['booster_id']]) : null;

$boosterName = $boosterRow['username'] ?? 'Not Assigned';
$boosterIcon = $boosterRow['icon'] ?? (ICON_URL . '/25d1ea33c481dbacd2f2c294408d38cd.png');

// --- Pending/live option values for the client UI ---
// Customers may see an "original snapshot" in other parts of the UI until an admin applies changes,
// but the edit controls should reflect the latest saved (pending) values from order_options.
$lb_live_options = db_get_row('order_options', ['order_id' => (int)($data['id'] ?? 0)], true);

$lb_client_game_key = strtolower(trim((string)($data['game'] ?? $data['game_slug'] ?? '')));
$lb_is_counter_strike_order = in_array($lb_client_game_key, [
  'cs2', 'counter-strike-2', 'counterstrike2', 'counter-strike', 'csgo'
], true) || in_array((int)($data['form_id'] ?? 0), [54, 55], true);

$lb_ui_flash_position  = $lb_live_options['flash_position'] ?? ($data['flash_position'] ?? '');
$lb_ui_is_offline_mode = isset($lb_live_options['is_offline_mode']) ? (int)$lb_live_options['is_offline_mode'] : (!empty($data['is_offline_mode']) ? 1 : 0);
$lb_ui_vpn_country     = $lb_live_options['vpn_country'] ?? ($data['vpn_country'] ?? '');


// Server change add-on (client order view)
$lb_current_server = strtolower(trim((string)($lb_live_options['server'] ?? ($data['server'] ?? ''))));
if ($lb_current_server === '') {
  $lb_current_server = 'euw';
}

// Customer view fix:
// The Customize Order modal already reads the live server from order_options,
// but the page title uses $data['server'] through util_format_boost_overview().
// After admin edits, $data can still contain the immutable customer snapshot.
// Force the visible header/overview to use the live customer server.
$data['server'] = $lb_current_server;
$lb_server_labels = [
  'euw' => 'EUW', 'eune' => 'EUNE', 'na' => 'NA', 'oce' => 'OCE', 'br' => 'BR',
  'lan' => 'LAN', 'las' => 'LAS', 'tr' => 'TR', 'ru' => 'RU', 'jp' => 'JP',
  'kr' => 'KR', 'vn' => 'VN', 'ph' => 'PH', 'sg' => 'SG', 'th' => 'TH', 'tw' => 'TW'
];
$lb_server_candidates = array_keys($lb_server_labels);
$lb_can_server_change_addon = in_array((int)($data['form_id'] ?? 0), [1, 2, 3, 4, 9, 15, 17, 18, 19, 20, 26], true);

/**
 * CLAIMED BOOSTER (boosters + booster_profiles mergen)
 * - boosters: username, icon, languages, cover, ...
 * - booster_profiles: lol_rank, roles, champions, ...
 */
$claimedBooster = [];
$claimedBoosterId = (int) ($data['booster_id'] ?? 0);


// Presence (same logic as Checkout): the booster's own availability switch.
$lbBoosterOnline = function_exists('lb_booster_is_online') ? lb_booster_is_online($claimedBoosterId) : false;

if ($claimedBoosterId) {
  $b = db_get_row('boosters', ['id' => $claimedBoosterId]);
  if (!empty($b)) {
    $claimedBooster = array_merge($claimedBooster, (array) $b);
  }

  $p = db_get_row('booster_profiles', ['booster_id' => $claimedBoosterId]);
  if (!empty($p)) {
    $claimedBooster = array_merge($claimedBooster, (array) $p);
  }
}

/**
 * Robust list parser:
 * Supports: "a|b|c" OR "a,b,c" OR '["a","b"]'
 */
$lb_parse_list = function ($v) {
  if (empty($v))
    return [];
  if (is_array($v))
    return array_values(array_filter($v));

  $v = trim((string) $v);
  if ($v === '')
    return [];

  if (strlen($v) > 1 && $v[0] === '[') {
    $j = json_decode($v, true);
    if (is_array($j))
      return array_values(array_filter($j));
  }

  if (str_contains($v, '|'))
    return array_values(array_filter(array_map('trim', explode('|', $v))));
  if (str_contains($v, ','))
    return array_values(array_filter(array_map('trim', explode(',', $v))));

  return array_values(array_filter([$v]));
};

// LP bucket display (so customers see the same ranges as in the selector: 0-20, 21-40, ...)
// Accepts ints (e.g. 21) and also strings (e.g. "21-40" or "21-40 LP").
$lb_format_lp_range = function ($lp) {
  if ($lp === null || $lp === '') {
    return '';
  }

  // If it's already a range string, keep it.
  if (!is_numeric($lp)) {
    $s = trim((string) $lp);
    if ($s === '') {
      return '';
    }
    // Contains a "number-number" pattern => assume it's the bucket.
    if (preg_match('/\d+\s*-\s*\d+/', $s)) {
      return (stripos($s, 'lp') !== false) ? $s : ($s . ' LP');
    }
    return $s . ' LP';
  }

  $n = (int) $lp;
  if ($n <= 20) {
    return '0-20 LP';
  }

  $low = (int) (floor(($n - 1) / 20) * 20 + 1);
  $high = $low + 19;
  return $low . '-' . $high . ' LP';
};

// Ranks (wie Booster-Card / Booster-Profile)
$lolranks = [
  1 => 'Iron',
  2 => 'Bronze',
  3 => 'Silver',
  4 => 'Gold',
  5 => 'Platinum',
  6 => 'Emerald',
  7 => 'Diamond',
  8 => 'Master',
  9 => 'Grandmaster',
  10 => 'Challenger'
];

// Rank parsing: "10|Challenger" oder "10"
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

// Lists
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



// Limits wie Admin Card feeling
$roles = array_values(array_filter(array_slice($roles, 0, 5)));
$langs = array_values(array_filter(array_slice($langs, 0, 5)));
$champsLimited = array_values(array_filter(array_slice($champs, 0, 4)));
$champsRemaining = max(0, count($champs) - count($champsLimited));

// Booster visuals
$boosterName = $claimedBooster['username'] ?? $boosterName;
$boosterIcon = $claimedBooster['icon'] ?? $boosterIcon;
$boosterCover = $claimedBooster['cover'] ?? null;
$boosterCover = (!empty($boosterCover) ? $boosterCover : ASSET_URL . '/core/main/img/banners/leona.jpeg');

// Booster timezone (stored in booster_profiles.timezone as IANA tz, e.g. "Europe/Berlin").
// If not set, customers see "N/A".
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


$priceText = util_format_currency_display($data['currency']) . util_format_price_display($data['price']);

// Ranked 5s client view, collect every active booster/lane on the order.
$lb_is_ranked_5s_order = ((int)($data['form_id'] ?? 0) === 29 || (string)($data['type'] ?? '') === 'ranked-5s');
$lb_is_multi_booster_order = $lb_is_ranked_5s_order
  || (in_array((int)($data['form_id'] ?? 0), [4, 19], true) && max(1, (int)($data['boosters'] ?? 1)) > 1);
$lb_intro_boosters = [];
$lb_intro_seen_boosters = [];

$lb_booster_is_online = function (int $boosterId): bool {
  return function_exists('lb_booster_is_online') ? lb_booster_is_online($boosterId) : false;
};

$lb_add_intro_booster = function (int $boosterId, string $lane = '', int $slotNo = 0) use (&$lb_intro_boosters, &$lb_intro_seen_boosters, $lb_booster_is_online, $lb_parse_list, $lolranks, $isValBannerOrder, $valRankNames) {
  if ($boosterId <= 0 || isset($lb_intro_seen_boosters[$boosterId])) return;
  $lb_intro_seen_boosters[$boosterId] = true;

  $row = function_exists('get_booster_data') ? (array)get_booster_data($boosterId) : [];
  if (empty($row)) $row = (array)db_get_row('boosters', ['id' => $boosterId], true);
  $profile = (array)db_get_row('booster_profiles', ['booster_id' => $boosterId], true);
  $row = array_merge($row, $profile);

  $rankIdLocal = 0;
  $rawRank = (string)($row['lol_rank'] ?? '');
  if ($rawRank !== '') {
    $rankParts = explode('|', $rawRank);
    $rankIdLocal = (int)($rankParts[0] ?? 0);
  }

  $valRaw = trim((string)($row['val_rank'] ?? ($row['valorant_rank'] ?? '')));
  $valTier = 0;
  $valDiv = 0;
  if ($valRaw !== '') {
    $valParts = explode('|', $valRaw);
    $valTier = (int)($valParts[0] ?? 0);
    $valDiv = (int)($valParts[1] ?? 0);
  }

  $valRankNameLocal = $valRankNames[$valTier] ?? 'Unranked';
  $valRankDivSuffixLocal = ($valTier > 0 && $valTier < 7 && $valDiv > 0) ? ' ' . (['I', 'II', 'III', 'IV'][$valDiv - 1] ?? '') : '';
  $valRankLabelLocal = trim($valRankNameLocal . $valRankDivSuffixLocal);

  $rolesLocal = $lb_parse_list($row['roles'] ?? '');
  if ($lane !== '') {
    $rolesLocal = [$lane];
  }

  $langsLocal = $lb_parse_list($row['languages'] ?? '');
  $champsLocal = $lb_parse_list($row['champions'] ?? '');
  $agentsLocal = $lb_parse_list($row['agents'] ?? '');

  $tz = trim((string)($row['timezone'] ?? ''));
  if (function_exists('util_format_timezone_display')) {
    $tzDisplay = (string)util_format_timezone_display($tz);
  } else {
    $tzDisplay = $tz !== '' ? $tz : 'N/A';
  }

  $rankNameLocal = $lolranks[$rankIdLocal] ?? 'Unranked';
  $rankIconLocal = ASSET_URL . '/core/main/img/lol/ranks/max/' . $rankIdLocal . '.png';
  $valRankIconLocal = ASSET_URL . '/core/main/img/val/ranks/mini/' . $valTier . '.png';

  $lb_intro_boosters[] = [
    'id' => $boosterId,
    'slot_no' => $slotNo,
    'lane' => $lane,
    'name' => $row['username'] ?? ('Booster #' . $boosterId),
    'icon' => $row['icon'] ?? (ICON_URL . '/25d1ea33c481dbacd2f2c294408d38cd.png'),
    'cover' => !empty($row['cover']) ? $row['cover'] : (ASSET_URL . '/core/main/img/banners/leona.jpeg'),
    'online' => $lb_booster_is_online($boosterId),
    'roles' => array_values(array_filter(array_slice($rolesLocal, 0, 5))),
    'langs' => array_values(array_filter(array_slice($langsLocal, 0, 5))),
    'champs' => array_values(array_filter($champsLocal)),
    'agents' => array_values(array_filter($agentsLocal)),
    'rank_id' => $rankIdLocal,
    'rank_name' => $rankNameLocal,
    'rank_icon' => $rankIconLocal,
    'val_rank_tier' => $valTier,
    'val_rank_label' => $valRankLabelLocal,
    'val_rank_icon' => $valRankIconLocal,
    'timezone' => $tzDisplay,
    'can_visit_profile' => ((int)($row['verified'] ?? 0) === 1 && (int)($row['show_profile'] ?? 0) === 1),
    'profile_url' => (defined('BASE_URL') ? rtrim(BASE_URL, '/') : '') . '/boosters/' . $boosterId,
  ];
};

if ($lb_is_multi_booster_order) {
  try {
    global $db;
    $rows = $db->run(
      "SELECT booster_id, role, slot_no
         FROM order_boosters
        WHERE order_id = ?
          AND status = 'ACTIVE'
          AND booster_id IS NOT NULL
          AND booster_id > 0
        ORDER BY slot_no ASC, id ASC",
      (int)($data['id'] ?? 0)
    ) ?: [];

    foreach ($rows as $r5b) {
      $lane = $lb_is_ranked_5s_order ? (string)($r5b['role'] ?? '') : '';
      $lb_add_intro_booster((int)($r5b['booster_id'] ?? 0), $lane, (int)($r5b['slot_no'] ?? 0));
    }
  } catch (Throwable $e) {}

  if ((int)($data['booster_id'] ?? 0) > 0) {
    $lb_add_intro_booster((int)$data['booster_id'], '', 1);
  }
} elseif ($claimedBoosterId > 0) {
  $lb_add_intro_booster($claimedBoosterId, '', 1);
}

// For Ranked 5s, show claimed banner if at least one booster joined via order_boosters.
$lb_has_intro_boosters = !empty($lb_intro_boosters);
$lb_booster_meta_label = $lbRoleLabel ?? 'Booster';
$lb_booster_meta_value = $boosterName;
if (!empty($lb_is_multi_booster_order) && !empty($lb_intro_boosters)) {
  $lb_booster_meta_label = 'Boosters';
  $lb_booster_meta_value = implode(', ', array_map(static function($b) {
    $name = trim((string)($b['name'] ?? ''));
    $lane = trim((string)($b['lane'] ?? ''));
    $lane = str_replace(['TopLane','MidLane','AdCarry'], ['Top','Mid','ADC'], $lane);
    return $lane !== '' ? ($name . ' (' . $lane . ')') : $name;
  }, $lb_intro_boosters));
}

$lb_selectable_boosters = [];
if (!empty($lb_intro_boosters)) {
  foreach ($lb_intro_boosters as $lbSelectableBooster) {
    $lbSelectableId = (int)($lbSelectableBooster['id'] ?? 0);
    if ($lbSelectableId <= 0) continue;
    $lbLaneLabel = trim((string)($lbSelectableBooster['lane'] ?? ''));
    $lbLaneLabel = str_replace(['TopLane','MidLane','AdCarry'], ['Top','Mid','ADC'], $lbLaneLabel);
    $lb_selectable_boosters[] = [
      'id' => $lbSelectableId,
      'name' => (string)($lbSelectableBooster['name'] ?? ('Booster #' . $lbSelectableId)),
      'icon' => (string)($lbSelectableBooster['icon'] ?? ''),
      'lane' => $lbLaneLabel,
      'status' => 'active',
    ];
  }
}
if (!empty($lb_is_multi_booster_order)) {
  $lbPendingRequestedIds = array_values(array_unique(array_filter(array_map(
    'intval',
    preg_split('/[\s,|]+/', (string)($lb_live_options['selected_boosters'] ?? ''), -1, PREG_SPLIT_NO_EMPTY)
  ))));
  $lbKnownSelectableIds = array_map(static fn($item) => (int)($item['id'] ?? 0), $lb_selectable_boosters);
  foreach ($lbPendingRequestedIds as $lbPendingRequestedId) {
    if ($lbPendingRequestedId <= 0 || in_array($lbPendingRequestedId, $lbKnownSelectableIds, true)) continue;
    $lbPendingBooster = (array)db_get_row('boosters', ['id' => $lbPendingRequestedId], true);
    if (empty($lbPendingBooster)) continue;
    $lb_selectable_boosters[] = [
      'id' => $lbPendingRequestedId,
      'name' => (string)($lbPendingBooster['username'] ?? ('Booster #' . $lbPendingRequestedId)),
      'icon' => (string)($lbPendingBooster['icon'] ?? ''),
      'lane' => '',
      'status' => 'requested',
    ];
    $lbKnownSelectableIds[] = $lbPendingRequestedId;
  }
}
$lb_default_select_booster = $lb_selectable_boosters[0] ?? [
  'id' => (int)($data['booster_id'] ?? 0),
  'name' => (string)($boosterName ?? 'Booster'),
  'icon' => (string)($boosterIcon ?? ''),
  'lane' => '',
];
$lb_has_multi_select_boosters = count($lb_selectable_boosters) > 1;

$statusKey = strtoupper($data['status'] ?? '');

// Coaching orders (form_id 15,16) use "Coach" wording and hide booster-specific UI.
$lb_formId = (int) ($data['form_id'] ?? 0);
$isCoachingOrder = in_array($lb_formId, [15, 16, 25], true) || (!empty($data['is_coaching']) && (int)($data['is_coaching'] ?? 0) === 1);
$isLolOrder = in_array($lb_formId, [1, 2, 3, 4, 9, 17, 18, 19, 20, 26], true);
$isValOrder = in_array($lb_formId, [5, 6, 7, 8], true);
$isTftOrder = in_array($lb_formId, [21, 22, 23, 24], true);
$isDuoOrder = !empty($data['is_duo']);
$isRiotOnlyOrder = false;
$showAccountCredentials = !$isCoachingOrder && !$isDuoOrder;
$accountUsernameLabel = 'Account Username';
$accountPasswordLabel = 'Account Password';
if (!$isLolOrder) {
  $accountUsernameLabel = 'Account Login';
}
$lbRoleLabel = $isCoachingOrder ? 'Coach' : 'Booster';
$lbRoleLabelLc = $isCoachingOrder ? 'coach' : 'booster';
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

$isChatAllowed = in_array($data['status'], ['IN_PROGRESS', 'PAUSED', 'COMPLETED', 'PAID'], true);
$isEditableAcc = ($data['status'] !== 'UNPAID');


if (!function_exists('lb_addon_option_icon_html')) {
  function lb_addon_option_icon_html($addon): string {
    $key = strtolower(trim((string)($addon['key'] ?? '')));
    $label = strtolower(trim((string)($addon['label'] ?? '')));
    $haystack = trim($key . ' ' . $label);

    $assetBase = defined('ASSET_URL') ? ASSET_URL : '';
    $imgBase = rtrim($assetBase, '/') . '/website/images/boost-forms/';

    $img = null;
    $fa = null;

    if (str_contains($haystack, 'priority') || str_contains($haystack, 'is_priority')) {
      $img = $imgBase . 'priority.svg';
    } elseif (str_contains($haystack, 'bonus') || str_contains($haystack, 'is_bonus_win')) {
      $img = $imgBase . 'bonus-win1.svg';
    } elseif (str_contains($haystack, 'stream') || str_contains($haystack, 'is_streaming')) {
      $img = $imgBase . 'stream-games1.svg';
    } elseif (str_contains($haystack, 'solo') || str_contains($haystack, 'is_solo_only')) {
      $img = $imgBase . 'solo-queue1.svg';
    } elseif (str_contains($haystack, 'voice') || str_contains($haystack, 'coaching') || str_contains($haystack, 'is_voice') || str_contains($haystack, 'is_coaching')) {
      $img = $imgBase . 'champs-roles1.svg';
    } elseif (str_contains($haystack, 'hidden') || str_contains($haystack, 'is_hidden_duo')) {
      // Same as Order Summary: FontAwesome instead of the SVG asset.
      $fa = 'fa-user-secret';
    } elseif (str_contains($haystack, 'champ') || str_contains($haystack, 'role')) {
      $img = $imgBase . 'champs-roles1.svg';
    } elseif (str_contains($haystack, 'undercover') || str_contains($haystack, 'winrate') || str_contains($haystack, 'is_undercover_winrate')) {
      // Same as Order Summary: FontAwesome instead of the SVG asset.
      $fa = 'fa-user-secret';
    } elseif (str_contains($haystack, 'moderate') || str_contains($haystack, 'kda') || str_contains($haystack, 'is_moderate_kda')) {
      // Same as Order Summary: FontAwesome instead of the SVG asset.
      $fa = 'fa-chart-line';
    } elseif (str_contains($haystack, 'lp')) {
      $fa = 'fa-chart-line-up';
    } elseif (str_contains($haystack, 'duo')) {
      // Fallback for add-ons without a dedicated asset, e.g. Upgrade to Duo.
      $fa = 'fa-user-group';
    } elseif (str_contains($haystack, 'tip')) {
      $fa = 'fa-gift';
    } else {
      $fa = 'fa-sparkles';
    }

    if ($img) {
      return '<span class="lbap-addon-card__icon" aria-hidden="true"><img src="' . htmlspecialchars($img, ENT_QUOTES) . '" alt=""></span>';
    }

    $fa = trim((string)$fa);
    $faClass = str_contains($fa, 'fa-') && str_contains($fa, 'fa-duotone') ? $fa : ('fa-duotone ' . $fa);
    return '<span class="lbap-addon-card__icon lbap-addon-card__icon--fa" aria-hidden="true"><i class="' . htmlspecialchars($faClass, ENT_QUOTES) . '"></i></span>';
  }
}

$options = ['roles', 'champions', 'agents', 'is_priority', 'is_streaming', 'is_solo_only', 'is_bonus_win', 'is_coaching', 'is_voice', 'is_hidden_duo', 'is_undercover_winrate', 'is_moderate_kda'];
$hasOption = false;
foreach ($options as $opt) {
  if (!empty($data[$opt])) {
    $hasOption = true;
    break;
  }
}


$canVisitProfile = (
  $claimedBoosterId > 0
  && (int) ($claimedBooster['verified'] ?? 0) === 1
  && (int) ($claimedBooster['show_profile'] ?? 0) === 1
);


if (defined('BASE_URL')) {
  $boosterProfileUrl = rtrim(BASE_URL, '/') . '/boosters/' . $claimedBoosterId;
} else {
  // ansonsten feste Domain / relativer Pfad:
  $boosterProfileUrl = '/boosters/' . $claimedBoosterId;
}


$lb_pendingRequestedBooster = ($claimedBoosterId > 0 && in_array(($data['status'] ?? ''), ['PAID', 'PROCESSING', 'IN_PROGRESS'], true) && empty($data['claimed_at']));

// Banner display rules
$showClaimedBanner = ((($claimedBoosterId > 0) || (!empty($lb_is_multi_booster_order) && !empty($lb_has_intro_boosters))) && !$lb_pendingRequestedBooster && in_array($data['status'], ['IN_PROGRESS', 'PAUSED', 'PAID'], true));
$showWaitingBanner = (empty($claimedBoosterId) && in_array($data['status'], ['PAID', 'PROCESSING'], true));
$showRequestedBoosterPendingBanner = $lb_pendingRequestedBooster;

// Review targets for completed client orders: current booster plus every booster that contributed via progress segments or payments.
$lb_review_boosters = [];
$lb_add_review_booster = function ($boosterId, $source = '') use (&$lb_review_boosters) {
  $boosterId = (int) $boosterId;
  if ($boosterId <= 0) return;
  if (!isset($lb_review_boosters[$boosterId])) {
    $row = function_exists('get_booster_data') ? (array) get_booster_data($boosterId) : (array) db_get_row('boosters', ['id' => $boosterId], true);
    if (empty($row)) $row = (array) db_get_row('boosters', ['id' => $boosterId], true);
    $lb_review_boosters[$boosterId] = [
      'id' => $boosterId,
      'username' => $row['username'] ?? ('Booster #' . $boosterId),
      'icon' => $row['icon'] ?? (defined('ICON_URL') ? ICON_URL . '/25d1ea33c481dbacd2f2c294408d38cd.png' : ''),
      'sources' => [],
    ];
  }
  if ($source !== '' && !in_array($source, $lb_review_boosters[$boosterId]['sources'], true)) {
    $lb_review_boosters[$boosterId]['sources'][] = $source;
  }
};

$lb_add_review_booster($data['booster_id'] ?? 0, 'Main Booster');
$lb_review_segments = db_get_rows('order_booster_segments', [
  'order_id' => (int)($data['id'] ?? 0),
  'order' => 'id,asc',
], true);
if (!empty($lb_review_segments)) {
  foreach ($lb_review_segments as $seg) {
    $lb_add_review_booster($seg['booster_id'] ?? 0, 'Contributed');
  }
}
$lb_review_payments = db_get_rows('booster_payments', [
  'note' => (string)($data['id'] ?? 0),
  'order' => 'id,asc',
], true);
if (!empty($lb_review_payments)) {
  foreach ($lb_review_payments as $pay) {
    $lb_add_review_booster($pay['booster_id'] ?? 0, 'Rewarded');
  }
}

$lb_reviews_by_booster = [];
$lb_all_reviews = db_get_rows('reviews', [
  'order_id' => (int)($data['id'] ?? 0),
  'order' => 'id,desc',
], true);
if (!empty($lb_all_reviews)) {
  foreach ($lb_all_reviews as $r) {
    $bid = (int)($r['booster_id'] ?? 0);
    if ($bid > 0) {
      $lb_reviews_by_booster[$bid] = $r;
      $lb_add_review_booster($bid, 'Reviewed');
    }
  }
}
$booster = function_exists('get_booster_data') ? get_booster_data((int)($data['booster_id'] ?? 0)) : ($boosterRow ?? []);
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
        $isStarted = ($startedAt !== '' && $startedAt !== '0000-00-00 00:00:00');
        $isRunning = false;

        if ($status === 'IN_PROGRESS' && !$isPaused && $isStarted) {
            $ts = strtotime($startedAt);
            if ($ts !== false) {
                $spent += max(0, time() - $ts);
                $isRunning = true;
            }
        }

        $used = min($booked, max(0, $spent));
        $remaining = max(0, $booked - $used);
        $progress = $booked > 0 ? (int)round(($used / $booked) * 100) : 0;

        $state = 'Waiting';
        if ($remaining <= 0) {
            $state = 'Finished';
            $isRunning = false;
        } elseif ($isPaused) {
            $state = 'Paused';
        } elseif ($isRunning) {
            $state = 'Running';
        }

        return [
            'booked_seconds' => $booked,
            'used_seconds' => $used,
            'remaining_seconds' => $remaining,
            'progress_percent' => max(0, min(100, $progress)),
            'status_label' => $state,
            'is_running' => $isRunning,
            'is_paused' => $isPaused,
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

<?php
// Ensure $op_mode exists before it is used in the wrapper class.
$op_mode = $op_mode ?? '';
?>

<?= $this->layout('client/layouts/main', ['meta' => ['title' => 'Orders #' . $data['id'] . ' - Customer Area | LoLBoost.gg']]) ?>

<div class="order-page-wrap <?= $op_mode === 'coaching' ? 'lb-order-is-coaching' : '' ?>">


  <!-- HEADER -->
  <div class="lb-head card mb-4">
    <div class="lb-head__top">
      <!-- Left: Icon + Title + Status -->
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
              echo '<i class="fa-duotone ' . htmlspecialchars($icon, ENT_QUOTES) . '"></i>';
            }
          ?>
          <?php $lbHeaderGameIcon = util_game_icon_url((string)($data['game'] ?? '')); ?>
          <?php if ($lbHeaderGameIcon !== ''): ?>
            <img src="<?= htmlspecialchars($lbHeaderGameIcon, ENT_QUOTES) ?>" alt="" style="position:absolute;right:-5px;bottom:-5px;width:20px;height:20px;object-fit:contain;border-radius:6px;background:#11131a;border:2px solid #11131a;">
          <?php endif; ?>
        </div>

        <div class="lb-head__title">
          <div class="lb-head__title-row">
            <h1 class="lb-head__h1">
              <?= util_format_boost_overview($data['game'], $data['type'], $data) ?>
            </h1>

            <!-- optional: tiny order id on desktop -->
            <span class="lb-head__id d-none d-lg-inline">#<?= (int) $data['id'] ?></span>
          </div>

          <div class="lb-head__sub">
            <span class="lb-status <?= $statusClass ?>">
              <span class="lb-status__dot"></span>
              <?php $statusDisplayKey = ($statusKey === 'REFUND') ? 'REFUNDED' : $statusKey; ?>
              <?= str_replace('_', ' ', $statusDisplayKey) ?>
            </span>
          </div>
        </div>
      </div>

      <!-- Right: Actions (KEEP YOUR EXISTING CODE 1:1) -->
      <div class="lb-head__actions">
        <div class="page-header-actions">
          

<?php
  // Change Booster 5-minute cooldown (no table)
  $lb_orderRow = db_get_row('orders', ['id' => $data['id']]);
  $lb_cbCooldownSeconds = 300;
  $lb_cbRemaining = 0;
  $lb_cbLast = $lb_orderRow['change_booster_last_request_at'] ?? null;
  if (!empty($lb_cbLast)) {
    $lb_cbLastTs = strtotime((string)$lb_cbLast);
    if ($lb_cbLastTs) {
      $lb_cbDiff = time() - $lb_cbLastTs;
      if ($lb_cbDiff < $lb_cbCooldownSeconds) {
        $lb_cbRemaining = $lb_cbCooldownSeconds - $lb_cbDiff;
      }
    }
  }
  $lb_cbLocked = ($lb_cbRemaining > 0);
?>

<?php if (in_array($data['status'], ['IN_PROGRESS', 'PAID'], true)): ?>
            <div class="dropdown">
              <button type="button" class="btn btn-white btn-sm dropdown-toggle lb-actions-btn"
                data-bs-toggle="dropdown" aria-expanded="false">
                <i class="fa-duotone fa-sliders me-2 d-none d-md-inline"></i>
                <span class="d-none d-md-inline">Order Actions</span>
                <i class="fa-solid fa-ellipsis-vertical d-md-none"></i>
              </button>

              <div class="dropdown-menu dropdown-menu-end mt-1">
                <span class="dropdown-header">Actions</span>

                <a class="dropdown-item d-flex align-items-center" href="#" data-bs-toggle="modal"
                    data-bs-target="#addon_payment_md">
                    <i class="fa-duotone fa-sliders me-2"></i> Customize Order
                  </a>

                <div>
                  <hr class="dropdown-divider">
                </div>

                <a class="dropdown-item d-flex align-items-center" href="#" data-bs-toggle="modal"
                    data-bs-target="#send_tip_md">
                    <i class="fa-duotone fa-gift me-2"></i> Tip Booster
                  </a>

                <div>
                  <hr class="dropdown-divider">
                </div>

                <?php if (!empty($lb_is_multi_booster_order) && !empty($lb_selectable_boosters)): ?>
                  <a class="dropdown-item d-flex align-items-center" href="#" data-bs-toggle="modal" data-bs-target="#notify_booster_md">
                    <i class="fa-duotone fa-bell-on me-2"></i> Notify Booster
                  </a>
                <?php else: ?>
                  <a class="dropdown-item d-flex align-items-center" href="#" data-id="<?= $data['id'] ?>" data-action="poke_booster">
                    <i class="fa-duotone fa-bell-on me-2"></i> <?= $showRequestedBoosterPendingBanner ? 'Notify Requested Booster' : 'Notify Booster' ?>
                  </a>
                <?php endif; ?>

                <div>
                  <hr class="dropdown-divider">
                </div>

                <a class="dropdown-item d-flex align-items-center js-change-booster-trigger" href="#" aria-disabled="<?= $lb_cbLocked ? 'true' : 'false' ?>" data-bs-toggle="modal"
                    data-bs-target="<?= $showRequestedBoosterPendingBanner ? "#client_change_booster_confirm_md" : "#client_change_booster_md" ?>" data-cb-remaining="<?= (int)$lb_cbRemaining ?>" data-cb-locked="<?= $lb_cbLocked ? 1 : 0 ?>">
                    <i class="fa-duotone fa-exchange me-2"></i> <?= $showRequestedBoosterPendingBanner ? 'Request Another Booster' : 'Change Booster' ?>
                  </a>

                <div>
                  <hr class="dropdown-divider">
                </div>

                <a class="dropdown-item d-flex align-items-center" href="#" data-bs-toggle="modal"
                    data-bs-target="#pause_order_md">
                    <i class="fa-duotone fa-circle-pause me-2"></i> Pause Order
                  </a>

              </div>
            </div>

          <?php elseif ($statusKey === 'PAUSED'): ?>
            <div class="dropdown">
              <button type="button" class="btn btn-white btn-sm dropdown-toggle lb-actions-btn"
                data-bs-toggle="dropdown" aria-expanded="false">
                <i class="fa-duotone fa-sliders me-2 d-none d-md-inline"></i>
                <span class="d-none d-md-inline">Order Actions</span>
                <i class="fa-solid fa-ellipsis-vertical d-md-none"></i>
              </button>

              <div class="dropdown-menu dropdown-menu-end mt-1">
                <span class="dropdown-header">Actions</span>

                <a class="dropdown-item d-flex align-items-center" href="#" data-bs-toggle="modal"
                    data-bs-target="#resume_order_md">
                    <i class="fa-duotone fa-play me-2"></i> Resume Order
                  </a>
              </div>
            </div>
          <?php endif; ?>
        </div>
      </div>
    </div>

    <!-- Meta pills -->
    <div class="lb-head__meta">
      <div class="lb-meta-pill">
        <div class="lb-meta-pill__k">Order</div>
        <div class="lb-meta-pill__v">#<?= (int) $data['id'] ?></div>
      </div>

      <div class="lb-meta-pill lb-meta-pill--boosters">
        <div class="lb-meta-pill__k"><?= htmlspecialchars($lb_booster_meta_label ?? $lbRoleLabel) ?></div>
        <div class="lb-meta-pill__v"><?= htmlspecialchars($lb_booster_meta_value ?? $boosterName) ?></div>
      </div>

      <div class="lb-meta-pill">
        <div class="lb-meta-pill__k">Price</div>
        <div class="lb-meta-pill__v"><?= $priceText ?></div>
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




  <div class="row g-4">

    <!-- LEFT: Booster bar + chat + overview -->
    <div class="col-lg-7">
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
        $lb_op_is_coaching_form  = $isCoachingOrder;
        $lb_op_is_count_form = ($lb_op_is_win_boost_form || $lb_op_is_placements_form || $lb_op_is_pro_games_form);
        $op_hours_target = (int) ($data['hours'] ?? 0);
        $op_mode = $lb_op_is_coaching_form ? 'coaching' : ($lb_op_is_duo_pass_form ? 'duo_time' : ($lb_op_is_count_form ? 'count' : 'rank'));
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
      ?>



      <?php if ($data['status'] === 'COMPLETED'): ?>
        <div class="card lb-review-card mb-4">
          <div class="card-header">
            <h4 class="card-header-title">Leave a Review</h4>
          </div>

          <div class="card-body lb-review-body">

            <!-- TOP ROW -->
            <div class="lb-review-top">
              <div class="lb-review-avatar">
                <img src="<?= htmlspecialchars($boosterIcon) ?>" alt="Booster Avatar">
              </div>

              <div class="lb-review-text">
                <div class="lb-review-pillrow">
                  <span class="lb-pill lb-pill--success">
                    <i class="fa-solid fa-check"></i> Completed
                  </span>

                  <a href="#" class="lb-pill lb-pill--action js-play-again" aria-label="Play again">
                    <i class="fa-solid fa-rotate-right"></i>
                    Play Again<?= $claimedBoosterId ? ' with ' . htmlspecialchars($boosterName) : '' ?>
                  </a>
                </div>

                <div class="lb-review-title">
                  GG! Your order was completed.
                </div>

                <div class="lb-review-sub">
                  It takes less than a minute — we appreciate honest feedback.
                </div>
              </div>

              <div class="lb-review-tip">
                <a href="#" data-bs-toggle="modal" data-bs-target="#send_tip_md" class="btn btn-white border">
                  <i class="fa-duotone fa-gift me-2"></i> Tip Booster
                </a>
              </div>
            </div>

            <!-- BOTTOM (centered stars) -->
            <div class="lb-review-bottom">
              <div class="lb-review-stars-label">How would you rate us?</div>

              <div id="star-rating" class="lb-review-stars" role="radiogroup" aria-label="Rating">
                <?php for ($i = 1; $i <= 5; $i++): ?>
                  <button type="button" class="lb-star" data-index="<?= $i ?>" aria-label="<?= $i ?> stars"
                    aria-checked="false">
                    <svg viewBox="0 0 24 24" aria-hidden="true">
                      <path d="M12 17.25L6.545 20.4l1.045-6.1L3 9.75l6.273-.9L12 3.75l2.727 5.1
                       6.273.9-4.59 4.55 1.045 6.1L12 17.25z" />
                    </svg>
                  </button>
                <?php endfor; ?>
              </div>

              <!-- optional hidden input if you want to submit later -->
              <input type="hidden" id="lb_review_rating" name="rating" value="0">
            </div>

          </div>
        </div>
      <?php endif; ?>



      <?php if ($isChatAllowed): ?>
        <?php if ($showClaimedBanner): ?>
<?php
$lb_isStreamingBanner = (!$isCoachingOrder) && (!empty($data['is_streaming']) && (int)$data['is_streaming'] === 1);
$lb_isVoiceBanner     = (!$isCoachingOrder) && (!$lb_isStreamingBanner) && (!empty($data['is_voice']) && (int)$data['is_voice'] === 1);
$lb_showDiscordBanner = $isCoachingOrder || $lb_isStreamingBanner || $lb_isVoiceBanner;
if ($lb_showDiscordBanner):
?>
<div class="lb-discord-banner mb-3">
  <div class="lb-discord-banner__left">
    <svg class="lb-discord-banner__logo" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 127.14 96.36"><path fill="#5865f2" d="M107.7,8.07A105.15,105.15,0,0,0,81.47,0a72.06,72.06,0,0,0-3.36,6.83A97.68,97.68,0,0,0,49,6.83,72.37,72.37,0,0,0,45.64,0,105.89,105.89,0,0,0,19.39,8.09C2.79,32.65-1.71,56.6.54,80.21h0A105.73,105.73,0,0,0,32.71,96.36,77.7,77.7,0,0,0,39.6,85.25a68.42,68.42,0,0,1-10.85-5.18c.91-.66,1.8-1.34,2.66-2a75.57,75.57,0,0,0,64.32,0c.87.71,1.76,1.39,2.66,2a68.68,68.68,0,0,1-10.87,5.19,77,77,0,0,0,6.89,11.1A105.25,105.25,0,0,0,126.6,80.22h0C129.24,52.84,122.09,29.11,107.7,8.07ZM42.45,65.69C36.18,65.69,31,60,31,53s5-12.74,11.43-12.74S54,46,53.89,53,48.84,65.69,42.45,65.69Zm42.24,0C78.41,65.69,73.25,60,73.25,53s5-12.74,11.44-12.74S96.23,46,96.12,53,91.08,65.69,84.69,65.69Z"/></svg>
    <div class="lb-discord-banner__text">
      <strong><?= $lb_isStreamingBanner ? 'Streaming Order' : 'Voice Chat' ?></strong>
      <span><?= $lb_isStreamingBanner ? 'Join the streaming channel — do not contact your booster outside the platform.' : 'Join the voice channel — do not contact your ' . ($isCoachingOrder ? 'coach' : 'booster') . ' outside the platform.' ?></span>
    </div>
  </div>
  <a class="lb-discord-banner__btn" href="https://lolboost.gg/coaching" target="_blank" rel="noopener">
    Join Voice Channel
  </a>
</div>
<?php endif; ?>

          <!-- BOOSTER INTRO (Claimed) - Ranked 5s supports multiple boosters -->
          <?php if (!empty($lb_intro_boosters)): ?>
            <?php if (count($lb_intro_boosters) > 1): ?>
              <div class="lb-r5s-booster-tabs" role="tablist" aria-label="Choose booster">
                <?php foreach ($lb_intro_boosters as $idx => $introTab): ?>
                  <button type="button" class="lb-r5s-booster-tab <?= $idx === 0 ? 'is-active' : '' ?>" data-r5s-booster-tab="<?= (int)$idx ?>">
                    <img src="<?= htmlspecialchars($introTab['icon'], ENT_QUOTES, 'UTF-8') ?>" alt="">
                    <span><?= htmlspecialchars($introTab['name'], ENT_QUOTES, 'UTF-8') ?></span>
                    <?php if (!empty($introTab['lane'])): ?>
                      <small><?= htmlspecialchars(str_replace(['TopLane','MidLane','AdCarry'], ['Top','Mid','ADC'], $introTab['lane']), ENT_QUOTES, 'UTF-8') ?></small>
                    <?php endif; ?>
                  </button>
                <?php endforeach; ?>
              </div>
            <?php endif; ?>

            <?php foreach ($lb_intro_boosters as $idx => $lbIntro): ?>
              <?php
                $boosterName = $lbIntro['name'];
                $boosterIcon = $lbIntro['icon'];
                $boosterCover = $lbIntro['cover'];
                $lbBoosterOnline = !empty($lbIntro['online']);
                $roles = $lbIntro['roles'];
                $langs = $lbIntro['langs'];
                $champs = $lbIntro['champs'];
                $champsLimited = array_values(array_filter(array_slice($champs, 0, 4)));
                $champsRemaining = max(0, count($champs) - count($champsLimited));
                $valAgents = $lbIntro['agents'];
                $valAgentsLimited = array_values(array_filter(array_slice($valAgents, 0, 4)));
                $valAgentsRemaining = max(0, count($valAgents) - count($valAgentsLimited));
                $bannerHasRank = $isValBannerOrder ? ((int)$lbIntro['val_rank_tier'] > 0) : ((int)$lbIntro['rank_id'] > 0);
                $bannerRankName = $isValBannerOrder ? $lbIntro['val_rank_label'] : $lbIntro['rank_name'];
                $bannerRankIcon = $isValBannerOrder ? $lbIntro['val_rank_icon'] : $lbIntro['rank_icon'];
                $bannerRankTitle = $isValBannerOrder ? 'Valorant Rank' : $lbIntro['rank_name'];
                $boosterTimezoneDisplay = $lbIntro['timezone'];
                $canVisitProfile = !empty($lbIntro['can_visit_profile']);
                $boosterProfileUrl = $lbIntro['profile_url'];
              ?>
              <div class="lb-r5s-booster-panel <?= $idx === 0 ? 'is-active' : '' ?>" data-r5s-booster-panel="<?= (int)$idx ?>">
          <div class="card booster-intro-card mb-4">
            <div class="booster-intro-bg" style="background-image:url('<?= htmlspecialchars($boosterCover) ?>');"></div>

            <div class="card-body booster-intro-body">

              <!-- TOP -->
              <div class="booster-intro-top">
                <div class="booster-intro-left">
                  <div class="booster-intro-avatar">
                    <span class="booster-intro-glow"></span>
                    <img src="<?= htmlspecialchars($boosterIcon) ?>" alt="Booster Avatar">
                  </div>

                  <div class="booster-intro-main">
                    <div class="booster-intro-name">
                      <span><?= htmlspecialchars($boosterName) ?></span>
                      <span class="lb-presence-pill <?= ($lbBoosterOnline ? 'online' : '') ?>">
                        <span class="lb-presence-dot <?= ($lbBoosterOnline ? 'online' : '') ?>"></span>
                        <?= ($lbBoosterOnline ? 'Online' : 'Offline') ?>
                      </span>
                    </div>

                    <?php if ($bannerHasRank): ?>
                      <div class="booster-rank-pill" title="<?= htmlspecialchars($bannerRankTitle) ?>">
                        <img src="<?= htmlspecialchars($bannerRankIcon) ?>" alt="Rank">
                        <span><?= htmlspecialchars($bannerRankName) ?></span>
                      </div>
                    <?php endif; ?>

                    <?php if (!empty($boosterTimezoneDisplay)): ?>
                      <div class="booster-rank-pill" title="<?= htmlspecialchars($lbRoleLabel) ?> Timezone">
                        <i class="fa-duotone fa-clock"></i>
                        <span><?= htmlspecialchars($boosterTimezoneDisplay) ?></span>
                      </div>
                    <?php endif; ?>
                  </div>
                </div>

                <div class="booster-intro-right">
                  <?php if ($canVisitProfile): ?>
                    <a class="visit-profile-btn" href="<?= htmlspecialchars($boosterProfileUrl) ?>" target="_blank"
                      rel="noopener">
                      <i class="fa-duotone fa-user"></i>
                      <span>View Profile</span>
                    </a>
                  <?php endif; ?>
                </div>
              </div>

              <!-- BOTTOM: 3 CARDS -->
              <div class="booster-intro-cards">

                <div class="booster-intro-block">
                  <div class="booster-intro-label"><?= $isValBannerOrder ? 'AGENTS' : 'CHAMPIONS' ?></div>
                  <div class="booster-intro-champs">
                    <?php if ($isValBannerOrder): ?>
                      <?php if (!empty($valAgentsLimited)): ?>
                        <?php foreach ($valAgentsLimited as $agent):
                          $agentKey = trim((string) $agent);
                          $agentIcon = $valAgentsData[$agentKey]['icon'] ?? '';
                          $agentName = $valAgentsData[$agentKey]['name'] ?? $agentKey; ?>
                          <?php if ($agentIcon): ?>
                            <img class="champ" src="<?= htmlspecialchars($agentIcon) ?>" alt="<?= htmlspecialchars($agentName) ?>" title="<?= htmlspecialchars($agentName) ?>">
                          <?php else: ?>
                            <span class="booster-intro-tag"><?= htmlspecialchars($agentName) ?></span>
                          <?php endif; ?>
                        <?php endforeach; ?>
                        <?php if ($valAgentsRemaining > 0): ?>
                          <?php
                            $valAgentsTooltip = [];
                            foreach ($valAgents as $agentTooltipRaw) {
                              $agentTooltipKey = trim((string) $agentTooltipRaw);
                              if ($agentTooltipKey === '') continue;
                              $valAgentsTooltip[] = [
                                'name' => (string)($valAgentsData[$agentTooltipKey]['name'] ?? $agentTooltipKey),
                                'icon' => (string)($valAgentsData[$agentTooltipKey]['icon'] ?? ''),
                              ];
                            }
                          ?>
                          <span class="more js-lb-champs-tooltip" data-title="All agents" data-items='<?= htmlspecialchars(json_encode($valAgentsTooltip, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE), ENT_QUOTES, "UTF-8") ?>'>+<?= $valAgentsRemaining ?></span>
                        <?php endif; ?>
                      <?php else: ?>
                        <span class="na">N/A</span>
                      <?php endif; ?>
                    <?php else: ?>
                      <?php if (!empty($champsLimited)): ?>
                        <?php foreach ($champsLimited as $champion): ?>
                          <img class="champ" src="<?= LOL_CHAMP_URL . '/' . $champion . '.png' ?>"
                            alt="<?= htmlspecialchars($champion) ?>">
                        <?php endforeach; ?>
                        <?php if ($champsRemaining > 0): ?>
                          <?php
                            $champsTooltip = [];
                            foreach ($champs as $champTooltipRaw) {
                              $champTooltip = trim((string) $champTooltipRaw);
                              if ($champTooltip === '') continue;
                              $champsTooltip[] = [
                                'name' => $champTooltip,
                                'icon' => rtrim(LOL_CHAMP_URL, '/') . '/' . rawurlencode($champTooltip) . '.png',
                              ];
                            }
                          ?>
                          <span class="more js-lb-champs-tooltip" data-title="All champions" data-items='<?= htmlspecialchars(json_encode($champsTooltip, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE), ENT_QUOTES, "UTF-8") ?>'>+<?= $champsRemaining ?></span>
                        <?php endif; ?>
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
                      <?php if ($valRankTier > 0): ?>
                        <img class="rank-mini-icon" src="<?= htmlspecialchars($valRankIcon) ?>" alt="<?= htmlspecialchars($valRankLabel) ?>">
                        <span><?= htmlspecialchars($valRankLabel) ?></span>
                      <?php else: ?>
                        <span class="na">N/A</span>
                      <?php endif; ?>
                    </div>
                  <?php else: ?>
                    <div class="booster-intro-roles">
                      <?php if (!empty($roles)): ?>
                        <?php foreach ($roles as $role): ?>
                          <span class="role-pill" title="<?= htmlspecialchars($role) ?>">
                            <img src="<?= ASSET_URL . '/core/main/img/lol/roles/' . $role . '.png' ?>"
                              alt="<?= htmlspecialchars($role) ?>">
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
                    <?php if (!empty($langs)): ?>
                      <?php foreach ($langs as $language):
                        $langKey = strtolower(trim((string) $language)); ?>
                        <img class="flag"
                          src="<?= ASSET_URL . '/core/main/img/languages/' . htmlspecialchars($langKey) . '.png' ?>"
                          alt="<?= htmlspecialchars($language) ?>">
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
          <?php endif; ?>


        <?php elseif ($showRequestedBoosterPendingBanner): ?>
          <div class="card waiting-banner mb-4">
            <div class="card-body">
              <div class="waiting-avatar-wrapper">
                <span class="waiting-pulse-ring"></span>
                <div class="waiting-avatar">
                  <i class="fa-duotone fa-user-clock"></i>
                </div>
              </div>

              <div class="flex-grow-1">
                <div class="waiting-title">Waiting for booster respond</div>
                <div class="waiting-sub">You will be notified as soon as <?= htmlspecialchars($boosterName) ?> responds.</div>
                <div class="mt-3">
                  <button type="button" class="btn btn-primary js-change-booster-trigger" aria-disabled="<?= $lb_cbLocked ? 'true' : 'false' ?>" data-bs-toggle="modal" data-bs-target="<?= $showRequestedBoosterPendingBanner ? "#client_change_booster_confirm_md" : "#client_change_booster_md" ?>" data-cb-remaining="<?= (int)$lb_cbRemaining ?>" data-cb-locked="<?= $lb_cbLocked ? 1 : 0 ?>">
                    <i class="fa-duotone fa-exchange me-2"></i> Don't wanna wait? Request another booster
                  </button>
                </div>
              </div>
            </div>
          </div>
        <?php elseif ($showWaitingBanner): ?>
          <!-- WAITING -->
          <div class="card waiting-banner mb-4">
            <div class="card-body">
              <div class="waiting-avatar-wrapper">
                <span class="waiting-pulse-ring"></span>
                <div class="waiting-avatar">
                  <i class="fa-duotone fa-user-clock"></i>
                </div>
              </div>

              <div class="flex-grow-1">
                <div class="waiting-title">Waiting for a <?= htmlspecialchars($lbRoleLabelLc) ?></div>
                <div class="waiting-sub">You will be notified as soon as someone accepts it.</div>
              </div>
            </div>
          </div>
        <?php endif; ?>


        <!-- CHAT -->
        <?php
          // Same rule as the booster order view: the chat stays open for 24h
          // after completion, then locks automatically.
          $chatIsCompleted   = (($data['status'] ?? '') === 'COMPLETED');
          $chatCompletedAtTs = !empty($data['completed_at']) ? strtotime((string)$data['completed_at']) : null;
          $chatGraceSeconds  = 24 * 60 * 60;
          $chatGraceActive   = false;
          $chatSecondsLeft   = 0;

          if ($chatIsCompleted && $chatCompletedAtTs) {
            $chatSecondsLeft = max(0, ($chatCompletedAtTs + $chatGraceSeconds) - time());
            $chatGraceActive = ($chatSecondsLeft > 0);
            $chatLocked      = !$chatGraceActive;
          } elseif ($chatIsCompleted) {
            // No completion timestamp: lock to be safe.
            $chatLocked = true;
          } else {
            $chatLocked = false;
          }
        ?>
        <div class="card order-chat-card mb-4">
          <div class="card-header d-flex justify-content-between align-items-center">
            <h4 class="card-header-title mb-0">Order Chat</h4>

            <?php if (in_array($data['status'], ['IN_PROGRESS', 'PAUSED'], true)): ?>
              <?php if (!empty($lb_is_multi_booster_order) && !empty($lb_selectable_boosters)): ?>
                <button type="button" class="btn btn-primary btn-notify-booster" data-bs-toggle="modal" data-bs-target="#notify_booster_md">
                  <i class="fa-duotone fa-bell-on"></i>
                  <span>Notify Booster</span>
                </button>
              <?php else: ?>
                <button class="btn btn-primary btn-notify-booster" data-id="<?= $data['id'] ?>" data-action="poke_booster">
                  <i class="fa-duotone fa-bell-on"></i>
                  <span>Notify <?= htmlspecialchars($lbRoleLabel) ?></span>
                </button>
              <?php endif; ?>
            <?php endif; ?>
          </div>

          <div class="card-body chat-bg" id="chat_messages"></div>

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
            <?php endif; ?>
            <form class="row gx-2 ajax-form<?= $chatLocked ? ' d-none' : '' ?>" action="<?= AJAX_URL ?>" <?= $chatLocked ? 'data-chat-locked="1"' : '' ?>>
              <input type="hidden" name="action" value="client_order_chat_send">
              <input type="hidden" name="order_id" value="<?= $data['id'] ?>">

              <input type="file" name="chat_image" id="lbChatImageInput" accept="image/*" class="d-none" <?= $chatLocked ? 'disabled' : '' ?>>

              <div class="col">
                <input type="text" name="message" id="lbChatMessageInput" class="form-control"
                  placeholder="<?= $chatLocked ? 'Chat is locked — order completed' : 'Type your message' ?>" <?= $chatLocked ? 'disabled' : '' ?>>
              </div>

              <div class="col-auto d-flex align-items-center gap-2">
                <button type="button" class="btn btn-sm btn-secondary" id="lbChatUploadBtn" aria-label="Upload image" title="Upload image" <?= $chatLocked ? 'disabled' : '' ?>>
                  <i class="fa-duotone fa-paperclip"></i>
                </button>
                <button type="button" class="btn btn-sm btn-secondary lb-emoji-btn d-none d-md-inline-flex"
                  id="lbEmojiBtn" aria-label="Emojis" title="Emojis" <?= $chatLocked ? 'disabled' : '' ?>>
                  <i class="fa-regular fa-face-smile"></i>
                </button>
                <button type="submit" class="btn btn-sm btn-primary" <?= $chatLocked ? 'disabled' : '' ?>>
                  <span class="indicator-label"><i class="fa-duotone fa-<?= $chatLocked ? 'lock' : 'paper-plane' ?> fs-5"></i></span>
                  <span class="indicator-progress"><span
                      class="spinner-border spinner-border-sm align-middle"></span></span>
                </button>
              </div>

              <div class="col-12 mt-2 d-none" id="lbChatImagePreviewWrap">
                <div class="lb-chat-preview">
                  <img id="lbChatImagePreview" src="" alt="preview">
                  <button type="button" class="lb-chat-preview__remove" id="lbChatImageRemove" aria-label="Remove image">
                    <i class="fa-solid fa-xmark"></i>
                  </button>
                </div>
              </div>
              </form>

            <div id="lbEmojiPicker" class="lb-emoji-picker d-none">
              <button type="button" class="lb-emoji" data-emoji="😀">😀</button>
              <button type="button" class="lb-emoji" data-emoji="😁">😁</button>
              <button type="button" class="lb-emoji" data-emoji="😂">😂</button>
              <button type="button" class="lb-emoji" data-emoji="🤣">🤣</button>
              <button type="button" class="lb-emoji" data-emoji="😊">😊</button>
              <button type="button" class="lb-emoji" data-emoji="😉">😉</button>
              <button type="button" class="lb-emoji" data-emoji="😍">😍</button>
              <button type="button" class="lb-emoji" data-emoji="😘">😘</button>
              <button type="button" class="lb-emoji" data-emoji="😎">😎</button>
              <button type="button" class="lb-emoji" data-emoji="🤔">🤔</button>
              <button type="button" class="lb-emoji" data-emoji="😴">😴</button>
              <button type="button" class="lb-emoji" data-emoji="😭">😭</button>
              <button type="button" class="lb-emoji" data-emoji="😡">😡</button>
              <button type="button" class="lb-emoji" data-emoji="👍">👍</button>
              <button type="button" class="lb-emoji" data-emoji="👎">👎</button>
              <button type="button" class="lb-emoji" data-emoji="🙏">🙏</button>
              <button type="button" class="lb-emoji" data-emoji="🙌">🙌</button>
              <button type="button" class="lb-emoji" data-emoji="👏">👏</button>
              <button type="button" class="lb-emoji" data-emoji="🎉">🎉</button>
              <button type="button" class="lb-emoji" data-emoji="🔥">🔥</button>
              <button type="button" class="lb-emoji" data-emoji="💯">💯</button>
              <button type="button" class="lb-emoji" data-emoji="✅">✅</button>
              <button type="button" class="lb-emoji" data-emoji="❌">❌</button>
              <button type="button" class="lb-emoji" data-emoji="⚡">⚡</button>
              <button type="button" class="lb-emoji" data-emoji="⭐">⭐</button>
              <button type="button" class="lb-emoji" data-emoji="💙">💙</button>
              <button type="button" class="lb-emoji" data-emoji="💚">💚</button>
              <button type="button" class="lb-emoji" data-emoji="💛">💛</button>
              <button type="button" class="lb-emoji" data-emoji="💜">💜</button>
              <button type="button" class="lb-emoji" data-emoji="🫡">🫡</button>
              <button type="button" class="lb-emoji" data-emoji="🤝">🤝</button>
              <button type="button" class="lb-emoji" data-emoji="🥳">🥳</button>
            </div>

          </div>
        </div>

      <?php endif; ?>
    </div>

    <!-- RIGHT: Account (top) + Options (below) + Notes -->
    <div class="col-lg-5">

      <?php if ($data['status'] === 'COMPLETED' && !empty($lb_review_boosters)): ?>
        <div class="card lb-actions-card mb-3">
          <div class="card-header">
            <h6 class="card-header-title mb-0">How Did It Go?</h6>
          </div>

          <div class="card-body py-2">
            <div class="lb-opt-static">
              <?php foreach ($lb_review_boosters as $lb_review_booster): ?>
                <?php
                  $lb_review_bid = (int)($lb_review_booster['id'] ?? 0);
                  $lb_booster_review = $lb_reviews_by_booster[$lb_review_bid] ?? null;
                  $lb_review_highlights = [];
                  if (!empty($lb_booster_review['highlights'])) {
                    $lb_review_highlights = json_decode($lb_booster_review['highlights'], true) ?: [];
                  }
                  $lb_can_edit_this_review = empty($lb_booster_review) || ((int)($lb_booster_review['client_edits'] ?? 0) === 0);
                  $lb_review_target_modal = '#leave_review_md';
                  $lb_review_button_text = empty($lb_booster_review) ? 'Review' : 'Edit Review';
                  $lb_review_roles = array_values(array_filter((array)($lb_review_booster['sources'] ?? [])));
                  $lb_review_role_label = 'Helped with your order';
                  if (in_array('Main Booster', $lb_review_roles, true)) {
                    $lb_review_role_label = count($lb_review_roles) > 1 ? 'Main booster · helped complete your order' : 'Main booster';
                  } elseif (in_array('Contributed', $lb_review_roles, true)) {
                    $lb_review_role_label = 'Helped complete your order';
                  } elseif (in_array('Rewarded', $lb_review_roles, true)) {
                    $lb_review_role_label = 'Part of the completed order';
                  }
                ?>
                <div class="lb-opt-row lb-opt-row--static">
                  <div class="lb-opt-left">
                    <div class="lb-opt-ico">
                      <span class="avatar">
                        <img class="avatar-img" src="<?= esc($lb_review_booster['icon'] ?? '') ?>" alt="Booster">
                      </span>
                    </div>
                    <div class="lb-opt-text">
                      <div class="lb-opt-label"><?= htmlspecialchars($lb_review_booster['username'] ?? ('Booster #' . $lb_review_bid)) ?></div>
                      <div class="lb-review-role-pill">
                        <i class="fa-duotone fa-circle-check"></i>
                        <span><?= htmlspecialchars($lb_review_role_label) ?></span>
                      </div>
                    </div>
                  </div>

                  <div class="lb-opt-right">
                    <button type="button"
                      class="btn <?= empty($lb_booster_review) ? 'btn-soft-success btn-icon' : 'btn-primary btn-sm' ?> js-client-review-open"
                      data-bs-toggle="modal"
                      data-bs-target="<?= $lb_review_target_modal ?>"
                      data-booster-id="<?= $lb_review_bid ?>"
                      data-booster-name="<?= htmlspecialchars($lb_review_booster['username'] ?? ('Booster #' . $lb_review_bid), ENT_QUOTES) ?>"
                      data-communication="<?= htmlspecialchars((string)($lb_booster_review['communication'] ?? ''), ENT_QUOTES) ?>"
                      data-skill="<?= htmlspecialchars((string)($lb_booster_review['skill'] ?? ''), ENT_QUOTES) ?>"
                      data-speed="<?= htmlspecialchars((string)($lb_booster_review['speed'] ?? ''), ENT_QUOTES) ?>"
                      data-overall="<?= htmlspecialchars((string)($lb_booster_review['overall'] ?? ''), ENT_QUOTES) ?>"
                      data-highlights="<?= htmlspecialchars(json_encode($lb_review_highlights), ENT_QUOTES) ?>"
                      data-comments="<?= htmlspecialchars((string)($lb_booster_review['comments'] ?? ''), ENT_QUOTES) ?>">
                      <?php if (empty($lb_booster_review)): ?>
                        <i class="fa fa-thumbs-up"></i>
                      <?php else: ?>
                        <?= htmlspecialchars($lb_review_button_text) ?>
                      <?php endif; ?>
                    </button>
                  </div>
                </div>
                <div class="lb-opt-divider"></div>
              <?php endforeach; ?>
            </div>
          </div>
        </div>
      <?php endif; ?>

      <?php if (in_array($data['status'], ['IN_PROGRESS', 'PAID'], true) || $statusKey === 'PAUSED'): ?>
        <div class="card lb-actions-card mb-3">
          <div class="card-header">
            <h6 class="card-header-title mb-0">Order Actions</h6>
          </div>

          <div class="card-body p-0">
            <div class="lb-actions-list">

              <?php if (in_array($data['status'], ['IN_PROGRESS', 'PAID'], true)): ?>

                <a class="lb-action-item" href="#" data-bs-toggle="modal" data-bs-target="#addon_payment_md">
                  <span class="lb-action-ico"><i class="fa-duotone fa-sliders"></i></span>
                  <span class="lb-action-txt">
                    <span class="lb-action-title">Customize Order</span>
                    <span class="lb-action-sub">Adjust your order options</span>
                  </span>
                  <span class="lb-action-go"><i class="fa-solid fa-chevron-right"></i></span>
                </a>

                <a class="lb-action-item" href="#" data-bs-toggle="modal" data-bs-target="#send_tip_md">
                  <span class="lb-action-ico"><i class="fa-duotone fa-gift"></i></span>
                  <span class="lb-action-txt">
                    <span class="lb-action-title">Tip <?= htmlspecialchars($lbRoleLabel) ?></span>
                    <span class="lb-action-sub">Send a tip with a message</span>
                  </span>
                  <span class="lb-action-go"><i class="fa-solid fa-chevron-right"></i></span>
                </a>

                <?php if (!empty($lb_is_multi_booster_order) && !empty($lb_selectable_boosters)): ?>
                  <a class="lb-action-item" href="#" data-bs-toggle="modal" data-bs-target="#notify_booster_md">
                    <span class="lb-action-ico"><i class="fa-duotone fa-bell-on"></i></span>
                    <span class="lb-action-txt">
                      <span class="lb-action-title">Notify Booster</span>
                      <span class="lb-action-sub">Choose which Ranked 5s booster to ping</span>
                    </span>
                    <span class="lb-action-go"><i class="fa-solid fa-chevron-right"></i></span>
                  </a>
                <?php else: ?>
                  <!-- WICHTIG: exakt gleiche data-attrs wie im Dropdown -->
                  <a class="lb-action-item" href="#" data-id="<?= $data['id'] ?>" data-action="poke_booster">
                    <span class="lb-action-ico"><i class="fa-duotone fa-bell-on"></i></span>
                    <span class="lb-action-txt">
                      <span class="lb-action-title">Notify <?= htmlspecialchars($lbRoleLabel) ?></span>
                      <span class="lb-action-sub">Ping your <?= htmlspecialchars($lbRoleLabelLc) ?></span>
                    </span>
                    <span class="lb-action-go"><i class="fa-solid fa-chevron-right"></i></span>
                  </a>
                <?php endif; ?>

                <a class="lb-action-item js-change-booster-trigger" href="#" aria-disabled="<?= $lb_cbLocked ? 'true' : 'false' ?>" data-bs-toggle="modal" data-bs-target="<?= $showRequestedBoosterPendingBanner ? "#client_change_booster_confirm_md" : "#client_change_booster_md" ?>" data-cb-remaining="<?= (int)$lb_cbRemaining ?>" data-cb-locked="<?= $lb_cbLocked ? 1 : 0 ?>">
                  <span class="lb-action-ico"><i class="fa-duotone fa-exchange"></i></span>
                  <span class="lb-action-txt">
                    <span class="lb-action-title">Change <?= htmlspecialchars($lbRoleLabel) ?></span>
                    <span class="lb-action-sub">Request another <?= htmlspecialchars($lbRoleLabelLc) ?></span>
                  </span>
                  <span class="lb-action-go"><i class="fa-solid fa-chevron-right"></i></span>
                </a>

                <?php
                $noteCount = !empty($data['notes']) ? count($data['notes']) : 0;
                ?>

                <a class="lb-action-item" href="#" data-bs-toggle="modal" data-bs-target="#order_notes_md">
                  <span class="lb-action-ico"><i class="fa-duotone fa-note-sticky"></i></span>
                  <span class="lb-action-txt">
                    <span class="lb-action-title">
                      Notes
                      <?php if ($noteCount > 0): ?>
                        <span class="badge bg-secondary ms-2"><?= (int) $noteCount ?></span>
                      <?php endif; ?>
                    </span>
                    <span class="lb-action-sub">View & manage notes for this order</span>
                  </span>
                  <span class="lb-action-go"><i class="fa-solid fa-chevron-right"></i></span>
                </a>

                <a class="lb-action-item lb-action-item--danger" href="#" data-bs-toggle="modal"
                  data-bs-target="#pause_order_md">
                  <span class="lb-action-ico"><i class="fa-duotone fa-circle-pause"></i></span>
                  <span class="lb-action-txt">
                    <span class="lb-action-title">Pause Order</span>
                    <span class="lb-action-sub">Temporarily pause this order</span>
                  </span>
                  <span class="lb-action-go"><i class="fa-solid fa-chevron-right"></i></span>
                </a>

              <?php elseif ($statusKey === 'PAUSED'): ?>

                <a class="lb-action-item" href="#" data-bs-toggle="modal" data-bs-target="#resume_order_md">
                  <span class="lb-action-ico"><i class="fa-duotone fa-play"></i></span>
                  <span class="lb-action-txt">
                    <span class="lb-action-title">Resume Order</span>
                    <span class="lb-action-sub">Continue this order</span>
                  </span>
                  <span class="lb-action-go"><i class="fa-solid fa-chevron-right"></i></span>
                </a>

              <?php endif; ?>


            </div>
          </div>
        </div>
      <?php endif; ?>

      <?php if ($lb_client_game_key === 'lol' || $isLolOrder || $isCoachingOrder): // LoL orders use Riot API tracking; coaching keeps its progress card ?>
      <div class="card mb-4 lb-op-card">
        <div class="card-header d-flex align-items-center justify-content-between">
          <div class="d-flex align-items-center gap-2">
            <div class="lb-op-header-ico">
              <i class="fa-duotone fa-chart-line-up"></i>
            </div>
            <h4 class="card-header-title mb-0">Order Progress</h4>
          </div>
          <?php if (!$lb_op_is_duo_pass_form && !$lb_op_is_coaching_form): ?>
            <button type="button" class="lb-op-refresh-btn" id="refreshProgressBtn" aria-label="Refresh progress"
              title="Sync with Riot API">
              <i class="fa-duotone fa-arrows-rotate"></i>
            </button>
          <?php endif; ?>
        </div>
        <div class="card-body">

          <?php if ($op_mode === 'coaching'): ?>
              <!-- Coaching: no Riot tracking -->
              <div class="lb-op-coaching-info">
                <div class="lb-op-coaching-hours"><?= esc($op_hours_target) ?></div>
                <div class="lb-op-coaching-label">Hours Purchased</div>
              </div>
              <div class="lb-op-coaching-note">
                <i class="fa-duotone fa-circle-info me-2"></i>
                Coaching orders don't have automatic Riot API tracking.
              </div>

            <?php elseif ($lb_op_is_duo_pass_form): ?>
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
            <?php elseif ($op_mode === 'count'): ?>
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
          <?php if ($op_mode !== 'coaching'): ?>

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
          <?php endif; ?>

          <?php if ($op_mode !== 'coaching'): ?>
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

          <?php if (empty(trim((string) ($data['ign'] ?? '')))): ?>
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
      <?php endif; ?>

      <!-- ACCOUNT -->
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
        var orderId = <?= (int)($data['id'] ?? 0) ?>;

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
        function render(){
          var pct = booked > 0 ? Math.round(((booked-rem)/booked)*100) : 0;
          pct = Math.max(0, Math.min(100, pct));
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
          setStatus(statusLabel);
        }
        function tick(){
          if(running && rem > 0){
            rem = Math.max(0, rem-1);
            elap++;
            if(rem <= 0){
              running = false;
              statusLabel = 'Finished';
            }
          }
          render();
        }
        function syncTimer(){
          if(typeof fetch_api !== 'function') return;
          fetch_api('check_duo_timer_status', { order_id: orderId }, function(response){
            if(!response || response.success !== true) return;
            rem = parseInt(response.remaining_seconds || 0, 10);
            elap = parseInt(response.used_seconds || 0, 10);
            booked = parseInt(response.booked_seconds || 0, 10);
            running = !!response.is_running;
            statusLabel = response.status_label || 'Waiting';
            render();
          });
        }

        // The countdown itself runs locally (tick, 1s, no network). The server is only
        // asked for the authoritative state when something can actually have changed:
        // a realtime push for this order, or the tab coming back into view. The old
        // unconditional 3s poll meant 20 full app bootstraps per minute for every open
        // order page, which is why this page showed up so heavily in the origin load.
        var syncPending = false;
        function syncTimerThrottled(){
          if (syncPending) return;
          syncPending = true;
          setTimeout(function(){ syncPending = false; syncTimer(); }, 150);
        }

        function bindDuoTimerSocket(sock){
          if (!sock || sock.__lbDuoTimerBound) return;
          sock.__lbDuoTimerBound = true;
          var onEvent = function(raw){
            var payload = (raw && raw.data && typeof raw.data === 'object')
              ? Object.assign({}, raw, raw.data)
              : (raw || {});
            if (parseInt(payload.order_id || 0, 10) !== parseInt(orderId, 10)) return;
            syncTimerThrottled();
          };
          // booster_start/pause/continue_duo_timer emit duo_timer_update; pausing or
          // resuming the whole order only changes the status, so listen to both.
          try { sock.on('duo_timer_update', onEvent); } catch(e){}
          try { sock.on('order_status_update', onEvent); } catch(e){}
        }

        bindDuoTimerSocket(window.lbSocket);
        window.addEventListener('lb-socket-ready', function(e){
          bindDuoTimerSocket((e && e.detail && e.detail.socket) || window.lbSocket);
        });

        // Catch up after the tab was hidden or the machine slept, where the local tick drifts.
        document.addEventListener('visibilitychange', function(){
          if (document.visibilityState === 'visible') syncTimerThrottled();
        });
        window.addEventListener('focus', syncTimerThrottled);

        render();
        syncTimer();
        setInterval(tick, 1000);

        // Safety net only: if the socket never connected, fall back to a slow poll
        // instead of the old 3s one. Skipped entirely while realtime is healthy.
        setInterval(function(){
          if (window.lbRealtimeConnected === true) return;
          if (document.visibilityState !== 'visible') return;
          syncTimer();
        }, 60000);
      })();
      </script>
      <?php endif; ?>

      <!-- ACCOUNT -->
      <?php if ($isEditableAcc): ?>
        <div class="card mb-4">
          <div class="card-header d-flex align-items-center justify-content-between">
            <h4 class="card-header-title mb-0">Account</h4>

            <?php
            $isRiotOnly = false;
            $savedRiotId = trim((string)($data['ign'] ?? ''));
            $accNoun = $showAccountCredentials
              ? 'Account Logins'
              : ($isLolOrder ? 'Riot ID' : 'In-Game Name');
            $hasRiot = $showAccountCredentials
              ? (!empty(trim((string)($data['login'] ?? ''))) && !empty(trim((string)($data['password'] ?? ''))))
              : ($savedRiotId !== '');
            $btnLong = $hasRiot ? "Edit {$accNoun}" : "Add {$accNoun}";
            $btnShort = $hasRiot ? "Edit" : "Add";

            $modalTitle = $hasRiot ? "Edit {$accNoun}" : "Add {$accNoun}";
            ?>
            <?php $hasLogins = $hasRiot; ?>

            <button type="button" class="btn btn-white btn-sm border lb-acc-trigger" data-bs-toggle="modal"
              data-bs-target="#account_logins_md">
              <i class="fa-duotone fa-user-pen me-2"></i>
              <span class="d-none d-sm-inline"><?= $btnLong ?></span>
              <span class="d-sm-none"><?= $btnShort ?></span>
            </button>
          </div>


          <div class="card-body">
            <div class="card-body">

              <!-- Hidden form (wird vom Modal befüllt + gespeichert) -->
              <form class="ajax-form d-none" id="account_logins_form" novalidate action="<?= AJAX_URL ?>">
                <input type="hidden" name="action" value="client_update_order_account">
                <input type="hidden" name="order_id" value="<?= htmlspecialchars($data['id'] ?? '') ?>">
                <input type="hidden" id="ign" name="ign" value="<?= htmlspecialchars($data['ign'] ?? '') ?>">
                <input type="hidden" id="rtlgn" name="login" value="<?= htmlspecialchars($data['login'] ?? '') ?>">
                <input type="hidden" id="rtpwd" name="password" value="<?= htmlspecialchars($data['password'] ?? '') ?>">
                <?php if ($isLolOrder && $showAccountCredentials): ?>
                  <input type="hidden" id="al_flash_position" name="flash_position" value="<?= htmlspecialchars($lb_ui_flash_position ?? '', ENT_QUOTES) ?>">
                <?php endif; ?>
                <?php if ($showAccountCredentials): ?>
                  <input type="hidden" id="al_is_offline_mode" name="is_offline_mode" value="<?= $lb_ui_is_offline_mode ? '1' : '0' ?>">
                  <input type="hidden" id="al_vpn_country" name="vpn_country" value="<?= htmlspecialchars($data['vpn_country'] ?? '', ENT_QUOTES) ?>">
                <?php endif; ?>
              </form>

              <div class="lb-acc-summary <?= $hasRiot ? 'is-saved' : 'is-missing' ?>">
                <div class="lb-acc-summary__icon">
                  <i class="fa-duotone <?= $hasRiot ? 'fa-shield-check' : 'fa-circle-exclamation' ?>"></i>
                </div>

                <div class="lb-acc-summary__text">
                  <div class="lb-acc-summary__title">
                    <?= htmlspecialchars($accNoun) ?>
                  </div>
                  <div class="lb-acc-summary__sub">
                    <?php if ($showAccountCredentials): ?>
                      <?= $hasRiot ? 'Your account login details are saved.' : 'Please add the account username, password and VPN country used for this order.' ?>
                    <?php else: ?>
                      <?= $hasRiot ? 'Your in-game name is saved.' : 'Please add the in-game name used for this order.' ?>
                    <?php endif; ?>
                  </div>
                  <?php if (!$showAccountCredentials && $savedRiotId !== ''): ?>
                    <div class="lb-acc-summary__riot">
                      <span class="lb-acc-summary__riot-label"><?= $isLolOrder ? 'Saved Riot ID' : 'Saved In-Game Name' ?></span>
                      <span class="lb-acc-summary__riot-value"><?= htmlspecialchars($savedRiotId, ENT_QUOTES) ?></span>
                    </div>
                  <?php endif; ?>
                </div>

                <div class="lb-acc-summary__badge">
                  <?= $hasRiot ? 'Saved' : 'Missing' ?>
                </div>
              </div>

            </div>


          </div>
        </div>
      <?php endif; ?>

      <!-- ADD ACCOUNT LOGINS -->
      <div id="account_logins_md" class="modal fade lbx-modal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
          <div class="modal-content lbx-modal__content">

            <div class="lbx-modal__header">
              <div class="lbx-modal__headLeft">
                <div class="lbx-modal__icon">
                  <i class="fa-duotone fa-user-pen"></i>
                </div>
                <div>
                  <div class="lbx-modal__title"><?= htmlspecialchars($modalTitle) ?></div>
                  <div class="lbx-modal__sub">Required to start/continue your order.</div>
                </div>
              </div>

              <button type="button" class="lbx-modal__close" data-bs-dismiss="modal" aria-label="Close">
                <i class="fa-solid fa-xmark"></i>
              </button>
            </div>

            <div class="lbx-modal__body">

              <div class="lb-row">
                <?php if ($isLolOrder): ?>
                <!-- Riot ID (LoL orders: required for Riot API tracking) -->
                <div class="lb-field">
                  <div class="lb-field-label">
                    <span class="lb-ico">🙂</span>
                    <span>Riot ID</span>
                  </div>
                  <input type="text" id="al_riot_id" class="lb-input" placeholder="Faker#1234" autocomplete="off" spellcheck="false" inputmode="text">
                  <div class="lb-riot-suggestions" id="al_riot_suggestions" role="listbox" aria-label="Previously used Riot IDs" hidden></div>
                  <div class="lb-riot-format-note">
                    <i class="fa-duotone fa-circle-info"></i>
                    <span>Enter the full Riot ID, e.g. <strong>Faker#1234</strong>. Matching accounts are verified automatically.</span>
                  </div>
                  <div class="lb-riot-inline-error" id="al_riot_error" aria-live="polite"></div>
                  <?php if ($isLolOrder): ?>
                    <div class="lb-riot-preview" id="al_riot_preview" aria-live="polite" hidden>
                      <div class="lb-riot-preview__avatar">
                        <img id="al_riot_icon" src="" alt="Riot account icon" loading="lazy">
                        <i class="fa-duotone fa-user-magnifying-glass" id="al_riot_icon_fallback"></i>
                      </div>
                      <div class="lb-riot-preview__body">
                        <div class="lb-riot-preview__label" id="al_riot_preview_label">Riot account preview</div>
                        <div class="lb-riot-preview__name" id="al_riot_name">Check your Riot ID</div>
                        <div class="lb-riot-preview__meta" id="al_riot_meta">We will show the summoner icon here when the account is found.</div>
                        <button type="button" class="lb-riot-confirm" id="al_riot_confirm" hidden>
                          <i class="fa-solid fa-circle-check me-1"></i> Account verified
                        </button>
                      </div>
                    </div>
                  <?php endif; ?>
                </div>
                <?php else: ?>
                  <div class="lb-field">
                    <div class="lb-field-label">
                      <span class="lb-ico"><i class="fa-duotone fa-gamepad"></i></span>
                      <span>In-Game Username</span>
                    </div>
                    <input type="text" id="al_game_username" class="lb-input" placeholder="Username" autocomplete="off" spellcheck="false">
                  </div>
                <?php endif; ?>
              </div>

              <?php if ($showAccountCredentials): ?>
                <div class="lb-row lb-row--2">
                  <div class="lb-field">
                    <div class="lb-field-label">
                      <span class="lb-ico">📛</span>
                      <span><?= htmlspecialchars($accountUsernameLabel) ?></span>
                    </div>
                    <input type="text" id="al_username" class="lb-input" placeholder="Username">
                  </div>

                  <div class="lb-field">
                    <div class="lb-field-label">
                      <span class="lb-ico">🔑</span>
                      <span><?= htmlspecialchars($accountPasswordLabel) ?></span>
                    </div>
                    <input type="text" id="al_password" class="lb-input" placeholder="Password">
                  </div>
                </div>
                <div class="lb-accopt">
                  <div class="lb-accopt__title">Account Options</div>

                  <?php if ($isLolOrder): ?>
                    <!-- Flash Position -->
                    <div class="lb-opt-row lb-opt-row--compact">
                      <div class="lb-opt-left">
                        <div class="lb-opt-ico">💥</div>
                        <div class="lb-opt-text">
                          <div class="lb-opt-label">Flash Position</div>
                          <div class="lb-opt-sub">Left (D) or Right (F)</div>
                        </div>
                      </div>
                      <div class="lb-opt-right lb-opt-right--w">
                        <input type="hidden" id="al_flash_position_ui" value="<?= htmlspecialchars($lb_ui_flash_position ?? '', ENT_QUOTES) ?>">
                        <div class="lb-seg" data-target="#al_flash_position_ui">
                          <button type="button" class="lb-seg-btn" data-value="D">Left (D)</button>
                          <button type="button" class="lb-seg-btn" data-value="F">Right (F)</button>
                        </div>
                      </div>
                    </div>
                  <?php endif; ?>

                  <!-- Offline Mode -->
                    <div class="lb-opt-row lb-opt-row--compact">
                      <div class="lb-opt-left">
                        <div class="lb-opt-ico">🔇</div>
                        <div class="lb-opt-text">
                          <div class="lb-opt-label">Offline Mode</div>
                          <div class="lb-opt-sub">Hide your online status</div>
                        </div>
                      </div>
                      <div class="lb-opt-right lb-opt-right--w">
                        <input type="hidden" id="al_is_offline_mode_ui" value="<?= $lb_ui_is_offline_mode ? '1' : '0' ?>">
                        <div class="lb-seg" data-target="#al_is_offline_mode_ui">
                          <button type="button" class="lb-seg-btn" data-value="0">No</button>
                          <button type="button" class="lb-seg-btn" data-value="1">Yes</button>
                        </div>
                      </div>
                    </div>

                  <!-- VPN Country -->
                  <div class="lb-opt-row lb-opt-row--compact">
                    <div class="lb-opt-left">
                      <div class="lb-opt-ico">🌎</div>
                      <div class="lb-opt-text">
                        <div class="lb-opt-label">VPN Country</div>
                        <div class="lb-opt-sub">Optional</div>
                      </div>
                    </div>

                    <div class="lb-opt-right lb-opt-right--w lb-opt-select">
                      <!-- hidden native select -->
                      <select id="al_vpn_country_ui" style="display:none" aria-hidden="true">
                        <option value="">None</option>
                        <?= util_load_country_list($lb_ui_vpn_country ?? null) ?>
                      </select>
                      <!-- custom dropdown -->
                      <div class="lb-csd" data-for="al_vpn_country_ui">
                        <div class="lb-csd__control" role="combobox" tabindex="0" aria-haspopup="listbox">
                          <span class="lb-csd__value">None</span>
                          <span class="lb-csd__arrow">
                            <svg width="12" height="12" viewBox="0 0 12 12" fill="none" xmlns="http://www.w3.org/2000/svg">
                              <path d="M2 4.5L6 8L10 4.5" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                          </span>
                        </div>
                        <div class="lb-csd__panel" role="listbox">
                          <div class="lb-csd__search-wrap">
                            <input type="text" class="lb-csd__search" placeholder="Search country…" autocomplete="off" aria-label="Search country">
                          </div>
                          <div class="lb-csd__list"></div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>



                <div class="lb-hint">
                  <i class="fa-duotone fa-shield-check" style="font-size:1.15rem;"></i>
                  <div class="small">
                    Your login details are safely protected and can only be seen by you and the <?= htmlspecialchars($lbRoleLabelLc) ?>.
                  </div>
                </div>
              <?php endif; ?>

            </div>

            <div class="lbx-modal__footer">
              <button type="button" class="lbx-modal__btn lbx-modal__btn--ghost" data-bs-dismiss="modal">Cancel</button>
              <button type="button" id="al_save" class="lbx-modal__btn lbx-modal__btn--primary">
                <i class="fa-solid fa-floppy-disk me-2"></i> Save
              </button>
            </div>

          </div>
        </div>
      </div>

      <?php
      $topOptions = ['is_priority', 'is_bonus_win', 'is_hidden_duo', 'is_streaming', 'is_solo_only', 'is_coaching', 'is_voice', 'is_undercover_winrate', 'is_moderate_kda'];

      $hasTop = false;      foreach ($topOptions as $o) {
        if (!empty($data[$o])) {
          $hasTop = true;
          break;
        }
      }

      $canEditOptions = $showAccountCredentials;

      // Card nur zeigen, wenn oben was da ist ODER das Formular überhaupt angezeigt werden darf
      $showOptionsCard = ($hasTop || $canEditOptions);
      ?>

      <?php if ($showOptionsCard): ?>
        <!-- OPTIONS -->
        <?php
        $__roles = $data['roles'] ?? '';
        $__champs = $data['champions'] ?? '';
        $lb_hasOptionsContent = ($hasTop || !empty(trim($__roles)) || !empty(trim($__champs)));
        ?>

        <?php if ($canEditOptions): ?>
          <!-- ACCOUNT OPTIONS -->
          <div class="card mb-4">
            <div class="card-header">
              <h4 class="card-header-title">Account Options</h4>
            </div>

            <div class="card-body">
              <form class="ajax-form lb-options-form" novalidate action="<?= AJAX_URL ?>" id="clientOptionsForm">
                <input type="hidden" name="action" value="client_update_order_options">
                <input type="hidden" name="order_id" value="<?= $data['id'] ?>">

                <?php
                // Optional: falls du $hasOption weiter brauchst, stell sicher es ist gesetzt
                $hasOption = false;
                foreach ($options as $option) {
                  if (!empty($data[$option])) {
                    $hasOption = true;
                    break;
                  }
                }
                ?>

                <?php if ($showAccountCredentials): ?>
                  <?php if ($isLolOrder): ?>
                    <!-- Flash Position (segmented) -->
                    <div class="lb-opt-row">
                      <div class="lb-opt-left">
                        <div class="lb-opt-ico">💥</div>
                        <div class="lb-opt-text">
                          <div class="lb-opt-label">Flash Position</div>
                          <div class="lb-opt-sub lb-opt-sub--desktop-hide">Choose Left (D) or Right (F)</div>
                        </div>
                      </div>

                      <div class="lb-opt-right lb-opt-right--w">
                        <input type="hidden" name="flash_position" id="flash_position"
                          value="<?= htmlspecialchars($lb_ui_flash_position ?? '', ENT_QUOTES) ?>">

                        <div class="lb-seg" data-target="#flash_position">
                          <button type="button" class="lb-seg-btn" data-value="D">Left (D)</button>
                          <button type="button" class="lb-seg-btn" data-value="F">Right (F)</button>
                        </div>
                      </div>
                    </div>

                  <?php endif; ?>

                    <!-- Offline Mode (segmented) -->
                    <div class="lb-opt-row">
                      <div class="lb-opt-left">
                        <div class="lb-opt-ico">🔇</div>
                        <div class="lb-opt-text">
                          <div class="lb-opt-label">Offline Mode</div>
                          <div class="lb-opt-sub lb-opt-sub--desktop-hide">Hide your online status</div>
                        </div>
                      </div>

                      <div class="lb-opt-right lb-opt-right--w">
                        <input type="hidden" name="is_offline_mode" id="is_offline_mode"
                          value="<?= $lb_ui_is_offline_mode ? '1' : '0' ?>">

                        <div class="lb-seg" data-target="#is_offline_mode">
                          <button type="button" class="lb-seg-btn" data-value="0">No</button>
                          <button type="button" class="lb-seg-btn" data-value="1">Yes</button>
                        </div>
                      </div>
                    </div>
                  <!-- VPN Country (styled select / TomSelect) -->
                  <div class="lb-opt-row">
                    <div class="lb-opt-left">
                      <div class="lb-opt-ico">🌎</div>
                      <div class="lb-opt-text">
                        <div class="lb-opt-label">VPN Country</div>
                        <div class="lb-opt-sub lb-opt-sub--desktop-hide">Optional (recommended if requested)</div>
                      </div>
                    </div>

                    <div class="lb-opt-right lb-opt-right--w lb-opt-select">
                      <!-- hidden native select for form submit -->
                      <select name="vpn_country" id="vpn_country" style="display:none" aria-hidden="true">
                        <option value="">None</option>
                        <?= util_load_country_list($lb_ui_vpn_country ?? null) ?>
                      </select>
                      <!-- custom dropdown -->
                      <div class="lb-csd" data-for="vpn_country">
                        <div class="lb-csd__control" role="combobox" tabindex="0" aria-haspopup="listbox">
                          <span class="lb-csd__value">None</span>
                          <span class="lb-csd__arrow">
                            <svg width="12" height="12" viewBox="0 0 12 12" fill="none" xmlns="http://www.w3.org/2000/svg">
                              <path d="M2 4.5L6 8L10 4.5" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                          </span>
                        </div>
                        <div class="lb-csd__panel" role="listbox">
                          <div class="lb-csd__search-wrap">
                            <input type="text" class="lb-csd__search" placeholder="Search country…" autocomplete="off" aria-label="Search country">
                          </div>
                          <div class="lb-csd__list"></div>
                        </div>
                      </div>
                    </div>
                  </div>

                  <div class="lb-opt-actions">
                    <button type="submit" class="btn btn-primary lb-opt-save">
                      <span class="indicator-label">Save Changes</span>
                      <span class="indicator-progress">
                        <span class="spinner-border spinner-border-sm align-middle"></span>
                      </span>
                    </button>
                  </div>
                </form>

              <?php endif; ?>
            </div>
          </div>
        <?php endif; ?>

        <?php if ($lb_hasOptionsContent): ?>
          <!-- OPTIONS -->
          <div class="card mb-4">
            <div class="card-header">
              <h4 class="card-header-title">Options</h4>
            </div>

            <div class="card-body">
<?php
            $__roles = $data['roles'] ?? '';
            $__champs = $data['champions'] ?? '';
            ?>
            <?php if (!empty(trim($__roles)) || !empty(trim($__champs))): ?>
              <div class="lb-options-extra">
                <?php if (!empty(trim($__roles))): ?>
                  <div class="lb-opt-row lb-opt-row--extras">
                    <div class="lb-opt-left">
                      <div class="lb-opt-ico">🧭</div>
                      <div class="lb-opt-text">
                        <div class="lb-opt-label">Roles</div>
                    <!-- subtitle removed -->
                      </div>
                    </div>
                    <div class="lb-opt-right lb-opt-right--icons">
                      <div class="lb-opt-icons"><?= util_format_roles($__roles) ?></div>
                    </div>
                  </div>
                <?php endif; ?>

                <?php if (!empty(trim($__champs))): ?>
                  <div class="lb-opt-row lb-opt-row--extras">
                    <div class="lb-opt-left">
                      <div class="lb-opt-ico">🏆</div>
                      <div class="lb-opt-text">
                        <div class="lb-opt-label">Champions</div>
                    <!-- subtitle removed -->
                      </div>
                    </div>
                    <div class="lb-opt-right lb-opt-right--icons">
                      <div class="lb-opt-icons"><?= util_format_champions($__champs) ?></div>
                    </div>
                  </div>
                <?php endif; ?>
              </div>
            <?php endif; ?>


            <?php
            // Optionen die "oben" als Read-Only angezeigt werden sollen:
            $topOptions = ['is_priority', 'is_bonus_win', 'is_hidden_duo', 'is_streaming', 'is_solo_only', 'is_coaching', 'is_voice', 'is_undercover_winrate', 'is_moderate_kda'];

            $hasTop = false;
            foreach ($topOptions as $o) {
              if (!empty($data[$o])) {
                $hasTop = true;
                break;
              }
            }
            ?>

            <?php if ($hasTop): ?>
              <div class="lb-opt-static">

                <?php
                // Option metadata: icon (SVG class or FA), label, subtitle
                $optMeta = [
                    'is_priority'           => ['ico' => '🔥', 'label' => 'Priority Boost',     'sub' => 'Order completed ~2x faster'],
                    'is_bonus_win'          => ['ico' => '🏅', 'label' => '+1 Bonus Win',        'sub' => 'Extra win after reaching goal'],
                    'is_hidden_duo'         => ['ico' => '👻', 'label' => 'Hidden Duo',          'sub' => 'Booster uses multiple accounts'],
                    'is_streaming'          => ['ico' => '🍿', 'label' => 'Stream Games',        'sub' => 'Watch your booster live'],
                    'is_solo_only'          => ['ico' => '🔏', 'label' => 'Solo Queue',          'sub' => 'Booster plays solo only'],
                    'is_coaching'           => ['ico' => '🎙️', 'label' => 'Voice Chat',          'sub' => 'Booster guides you in call'],
                    'is_voice'              => ['ico' => '🎙️', 'label' => 'Voice Chat',          'sub' => 'Booster guides you in call'],
                    'is_undercover_winrate' => ['ico' => '🕵️', 'label' => 'Undercover Winrate', 'sub' => 'Winrate kept ≤65% to avoid detection'],
                    'is_moderate_kda'       => ['ico' => '📊', 'label' => 'Moderate KDA',        'sub' => 'Average KDA kept at 4.5 or less'],
                ];
                ?>

                <?php foreach ($topOptions as $option): ?>
                  <?php if (!empty($data[$option])): ?>
                    <?php
                    $meta   = $optMeta[$option] ?? null;
                    $ico    = $meta['ico']   ?? util_format_option_emoji($option);
                    $label  = $meta['label'] ?? util_format_option($option, $data[$option])[0];
                    $sub    = $meta['sub']   ?? '';
                    ?>

                    <div class="lb-opt-row lb-opt-row--static">
                      <div class="lb-opt-left">
                        <div class="lb-opt-ico lb-opt-ico--addon"><?= lb_addon_option_icon_html(['key' => $option, 'label' => $label]) ?></div>
                        <div class="lb-opt-text">
                          <div class="lb-opt-label"><?= htmlspecialchars($label) ?></div>
                          <?php if ($sub): ?>
                            <div class="lb-opt-sub"><?= htmlspecialchars($sub) ?></div>
                          <?php endif; ?>
                        </div>
                      </div>

                      <div class="lb-opt-right">
                        <span class="lb-opt-pill is-yes">
                          <span class="lb-opt-dot"></span>
                          Yes
                        </span>
                      </div>
                    </div>

                  <?php endif; ?>
                <?php endforeach; ?>

                <div class="lb-opt-divider"></div>
              </div>
            <?php endif; ?>


            

            
            </div>
          </div>
        <?php endif; ?>

      <?php endif; ?>

<!-- OVERVIEW -->
      <div class="card mb-4 lb-overview-card">
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
              <div class="lb-ov-ico">🏷️</div>
              <div class="lb-ov-label">Discount</div>
              <div class="lb-ov-value"><?= util_format_discount_display($data['id']) ?></div>
            </li>
            <?php if (!$isCoachingOrder): ?>


            <li class="lb-ov-item">
              <div class="lb-ov-ico">🤝</div>
              <div class="lb-ov-label">Play With Booster</div>
              <div class="lb-ov-value">
                <?php $duo = !empty($data['is_duo']); ?>
                <span class="lb-ov-pill <?= $duo ? 'lb-ov-pill--yes' : 'lb-ov-pill--no' ?>">
                  <?= $duo ? 'Yes' : 'No' ?>
                </span>
              </div>
            </li>

            <?php endif; ?>
            <?php foreach (lb_order_view_purchase_fields($data) as $lbPurchaseField): ?>
              <li class="lb-ov-item">
                <div class="lb-ov-ico"><i class="<?= esc($lbPurchaseField['icon']) ?>"></i></div>
                <div class="lb-ov-label"><?= htmlspecialchars($lbPurchaseField['label'], ENT_QUOTES) ?></div>
                <div class="lb-ov-value"><?= htmlspecialchars($lbPurchaseField['value'], ENT_QUOTES) ?></div>
              </li>
            <?php endforeach; ?>

            <?php $lbClientStartLp = $lb_is_counter_strike_order ? '' : lb_order_start_lp_display($data); ?>
            <?php if ($lbClientStartLp !== ''): ?>
              <li class="lb-ov-item">
                <div class="lb-ov-ico">🏁</div>
                <div class="lb-ov-label">Start LP</div>
                <div class="lb-ov-value"><?= htmlspecialchars($lbClientStartLp) ?></div>
              </li>
            <?php endif; ?>

            <?php if (!$lb_is_counter_strike_order && !empty($data['lp_gain'])): ?>
              <li class="lb-ov-item">
                <div class="lb-ov-ico">📈</div>
                <div class="lb-ov-label">LP Gain</div>
                <div class="lb-ov-value"><?= htmlspecialchars($data['lp_gain']) ?></div>
              </li>
            <?php endif; ?>

          </ul>
        </div>
      </div>



    </div>
  </div>

</div>


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

<!-- =========================
   MODALS (keep functionality)
========================= -->

<?php
// =========================
// COMPLETED POPUP (auto open once per order)
// =========================
$lb_isCompleted = (($data['status'] ?? '') === 'COMPLETED');

// Determine which review modal should be used (leave/edit vs view)
$lb_hasReview = !empty($review);
$lb_canReview = $lb_isCompleted && (
  empty($review)
  || (!empty($review) && (int) ($review['client_edits'] ?? 0) === 0)
);

// Best-effort booster data for the popup (safe if helper isn't present)
$lb_popupBooster = [];
if ($lb_isCompleted) {
  if (function_exists('get_booster_data')) {
    $lb_popupBooster = (array) get_booster_data($data['booster_id'] ?? 0);
  } elseif (!empty($data['booster_id']) && function_exists('db_get_row')) {
    $lb_popupBooster = (array) db_get_row('boosters', ['id' => (int) $data['booster_id']]);
  }
}

$lb_popupBoosterName = $lb_popupBooster['username'] ?? ($boosterName ?? 'Booster');
$lb_popupBoosterIcon = $lb_popupBooster['icon'] ?? ($boosterIcon ?? '');
?>

<?php if ($lb_isCompleted): ?>
  <div id="completed_feedback_md" class="modal fade lb-modal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-lg modal-dialog-centered">
      <div class="modal-content">

        <div class="modal-header">
          <div class="lb-modal-head">
            <div class="lb-modal-ico lb-modal-ico--tip">
              <i class="fa-duotone fa-party-horn"></i>
            </div>

            <div class="lb-modal-headtxt">
              <h5 class="lb-modal-title">GG! Order completed 🎉</h5>
              <p class="lb-modal-sub">
                Order #<?= (int) ($data['id'] ?? 0) ?> • <?= util_format_boost_overview($data['game'], $data['type'], $data) ?>
              </p>
            </div>
          </div>

          <button type="button" class="lb-modal-x" data-bs-dismiss="modal" aria-label="Close">
            <i class="fa-solid fa-xmark"></i>
          </button>
        </div>

        <div class="modal-body">
          <div class="row g-3">

            <!-- Booster review (internal) -->
            <div class="col-12 col-md-6">
              <div class="card p-3" style="background: rgba(255, 255, 255, .03);">
                <div class="d-flex align-items-center gap-3">
                  <?php if (!empty($lb_popupBoosterIcon)): ?>
                    <span class="avatar" style="width:44px;height:44px;">
                      <img class="avatar-img" src="<?= esc($lb_popupBoosterIcon) ?>" alt="Booster">
                    </span>
                  <?php else: ?>
                    <span class="avatar" style="width:44px;height:44px;"></span>
                  <?php endif; ?>

                  <div class="flex-grow-1">
                    <div class="fw-bold">Rate your booster</div>
                    <div class="text-muted small">Quick feedback helps us match you better next time.</div>
                  </div>
                </div>

                <div class="mt-3 d-flex gap-2 flex-wrap">
                  <?php if ($lb_canReview): ?>
                    <button type="button" class="btn btn-primary" data-bs-dismiss="modal" data-bs-toggle="modal" data-bs-target="#leave_review_md">
                      <i class="fa-duotone fa-star me-2"></i> Leave / Edit Review
                    </button>
                  <?php else: ?>
                    <button type="button" class="btn btn-primary" data-bs-dismiss="modal" data-bs-toggle="modal" data-bs-target="#view_review_md">
                      <i class="fa-duotone fa-star me-2"></i> View Review
                    </button>
                  <?php endif; ?>

                  <button type="button" class="btn btn-white border" data-bs-dismiss="modal" data-bs-toggle="modal" data-bs-target="#send_tip_md">
                    <i class="fa-duotone fa-gift me-2"></i> Tip <?= htmlspecialchars($lb_popupBoosterName) ?>
                  </button>
                </div>
              </div>
            </div>

            <!-- Trustpilot -->
            <div class="col-12 col-md-6">
              <div class="card p-3" style="background: rgba(255, 255, 255, .03);">
                <div class="fw-bold">Review us on Trustpilot</div>
                <div class="text-muted small">Tap a star to open Trustpilot in a new tab.</div>

                <div class="mt-3">
                  <div id="tp-star-rating" class="lb-review-stars" role="radiogroup" aria-label="Trustpilot rating">
                    <?php for ($i = 1; $i <= 5; $i++): ?>
                      <button type="button" class="lb-star" data-index="<?= $i ?>" aria-label="<?= $i ?> stars" aria-checked="false">
                        <svg viewBox="0 0 24 24" aria-hidden="true">
                          <path d="M12 17.25L6.545 20.4l1.045-6.1L3 9.75l6.273-.9L12 3.75l2.727 5.1
                           6.273.9-4.59 4.55 1.045 6.1L12 17.25z" />
                        </svg>
                      </button>
                    <?php endfor; ?>
                  </div>
                  <input type="hidden" id="tp_review_rating" value="0">
                </div>

                <div class="mt-3">
                  <a class="btn btn-white border" href="https://www.trustpilot.com/evaluate/lolboost.gg" target="_blank" rel="noopener">
                    <i class="fa-duotone fa-arrow-up-right-from-square me-2"></i> Open Trustpilot
                  </a>
                </div>
              </div>
            </div>

            <div class="col-12">
              <div class="card p-3 p-md-4" style="background: linear-gradient(135deg, rgba(108, 92, 231, .18), rgba(0, 194, 255, .10)); border: 1px solid rgba(108, 92, 231, .45); box-shadow: 0 0 0 1px rgba(255,255,255,.03) inset, 0 10px 30px rgba(0,0,0,.18);">
                <div class="d-flex align-items-start gap-3">
                  <div class="d-flex align-items-center justify-content-center rounded-circle" style="width:44px;height:44px;min-width:44px;background: rgba(108, 92, 231, .22); border: 1px solid rgba(108, 92, 231, .35); color: #c9b8ff;">
                    <i class="fa-duotone fa-shield-heart"></i>
                  </div>

                  <div class="flex-grow-1">
                    <div class="d-flex flex-wrap align-items-center gap-2 mb-1">
                      <span class="badge text-uppercase" style="background: rgba(108, 92, 231, .18); color: #d7cbff; border: 1px solid rgba(108, 92, 231, .35); letter-spacing: .08em;">Customer Protection</span>
                      <div class="fw-bold" style="font-size: 1.05rem;">Help us keep LolBoost safe and fair</div>
                    </div>

                    <div class="small" style="color: rgba(255,255,255,.82); line-height: 1.65;">
                      If any booster tries to arrange a private boost outside our platform, please report it to us.
                      As a thank you, you may receive up to <strong style="color:#fff;">100 EUR/USD in store credit</strong> for any LolBoost service.
                    </div>
                  </div>
                </div>
              </div>
            </div>

          </div>
        </div>

	        <div class="modal-footer d-flex justify-content-between">
	          <button type="button" id="lb_completed_popup_dismiss" class="btn lb-btn lb-btn-ghost">I dont want review now</button>
	          <div class="small text-muted">You can review anytime from this order page.</div>
	        </div>

      </div>
    </div>
  </div>
<?php endif; ?>

<!-- TIP -->
<form class="ajax-form" action="<?= AJAX_URL ?>">
  <input type="hidden" name="action" value="client_send_tip">
  <input type="hidden" name="order_id" value="<?= $data['order_id'] ?? $data['id'] ?>">
  <input type="hidden" name="booster_id" id="lb_tip_booster_id" value="<?= (int)($lb_default_select_booster['id'] ?? ($data['booster_id'] ?? 0)) ?>">

  <div id="send_tip_md" class="modal fade lb-modal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">

        <div class="modal-header">
          <div class="lb-modal-head">
            <div class="lb-modal-ico lb-modal-ico--tip">
              <i class="fa-duotone fa-gift"></i>
            </div>

            <div class="lb-modal-headtxt">
              <h5 class="lb-modal-title">Tip <span id="lb_tip_booster_title"><?= htmlspecialchars($lb_default_select_booster['name'] ?? $boosterName) ?></span></h5>
              <p class="lb-modal-sub">Say thanks with a small tip (optional message)</p>
            </div>
          </div>

          <button type="button" class="lb-modal-x" data-bs-dismiss="modal" aria-label="Close">
            <i class="fa-solid fa-xmark"></i>
          </button>
        </div>


        <div class="modal-body flex-grow-1">

          <?php if (!empty($lb_is_multi_booster_order) && !empty($lb_selectable_boosters)): ?>
            <div class="lb-field-title">Choose Booster <span class="text-danger">*</span></div>
            <div class="lb-r5s-choice-grid mb-3" data-lb-choice-group="tip">
              <?php foreach ($lb_selectable_boosters as $idx => $lbChoice): ?>
                <label class="lb-r5s-choice <?= $idx === 0 ? 'is-active' : '' ?>">
                  <input type="radio" name="lb_tip_booster_choice" value="<?= (int)$lbChoice['id'] ?>" data-name="<?= htmlspecialchars($lbChoice['name'], ENT_QUOTES, 'UTF-8') ?>" <?= $idx === 0 ? 'checked' : '' ?>>
                  <?php if (!empty($lbChoice['icon'])): ?><img src="<?= htmlspecialchars($lbChoice['icon'], ENT_QUOTES, 'UTF-8') ?>" alt=""><?php endif; ?>
                  <span class="lb-r5s-choice-name"><?= htmlspecialchars($lbChoice['name'], ENT_QUOTES, 'UTF-8') ?></span>
                  <?php if (!empty($lbChoice['lane'])): ?><span class="lb-r5s-choice-lane"><?= htmlspecialchars($lbChoice['lane'], ENT_QUOTES, 'UTF-8') ?></span><?php endif; ?>
                </label>
              <?php endforeach; ?>
            </div>
          <?php endif; ?>

          <div class="lb-field-title">Tip Amount <span class="text-danger">*</span></div>

          <div class="lb-tip-grid">
            <button type="button" class="lb-tip-chip tip-btn" data-value="5.00">€5</button>
            <button type="button" class="lb-tip-chip tip-btn" data-value="10.00">€10</button>
            <button type="button" class="lb-tip-chip tip-btn" data-value="20.00">€20</button>
          </div>

          <div class="lb-tip-amount">
            <button type="button" class="btn btn-white border" id="tip-minus">−</button>
            <input type="text" name="amount" id="tip-amount" class="form-control" value="5.00">
            <button type="button" class="btn btn-white border" id="tip-plus">+</button>
          </div>

          <div class="lb-field-title">Message</div>
          <textarea class="form-control" name="note" rows="3"
            placeholder="Leave a message for the booster..."></textarea>

        </div>

        <div class="modal-footer">
          <button type="button" class="btn lb-btn lb-btn-ghost" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn lb-btn lb-btn-success">
            <i class="fa-duotone fa-paper-plane me-2"></i> Send Tip
          </button>

        </div>

      </div>
    </div>
  </div>


</form>

<?php if (!empty($lb_is_multi_booster_order) && !empty($lb_selectable_boosters)): ?>
<!-- NOTIFY BOOSTER, Ranked 5s -->
<form class="ajax-form" action="<?= AJAX_URL ?>">
  <input type="hidden" name="action" value="poke_booster">
  <input type="hidden" name="id" value="<?= (int)($data['id'] ?? 0) ?>">
  <input type="hidden" name="booster_id" id="lb_notify_booster_id" value="<?= (int)($lb_default_select_booster['id'] ?? 0) ?>">

  <div id="notify_booster_md" class="modal fade lb-modal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
          <div class="lb-modal-head">
            <div class="lb-modal-ico lb-modal-ico--tip"><i class="fa-duotone fa-bell-on"></i></div>
            <div class="lb-modal-headtxt">
              <h5 class="lb-modal-title">Notify <span id="lb_notify_booster_title"><?= htmlspecialchars($lb_default_select_booster['name'] ?? 'Booster') ?></span></h5>
              <p class="lb-modal-sub">Choose the Ranked 5s booster you want to ping.</p>
            </div>
          </div>
          <button type="button" class="lb-modal-x" data-bs-dismiss="modal" aria-label="Close"><i class="fa-solid fa-xmark"></i></button>
        </div>

        <div class="modal-body">
          <div class="lb-field-title">Booster to notify <span class="text-danger">*</span></div>
          <div class="lb-r5s-choice-grid" data-lb-choice-group="notify">
            <?php foreach ($lb_selectable_boosters as $idx => $lbChoice): ?>
              <label class="lb-r5s-choice <?= $idx === 0 ? 'is-active' : '' ?>">
                <input type="radio" name="lb_notify_booster_choice" value="<?= (int)$lbChoice['id'] ?>" data-name="<?= htmlspecialchars($lbChoice['name'], ENT_QUOTES, 'UTF-8') ?>" <?= $idx === 0 ? 'checked' : '' ?>>
                <?php if (!empty($lbChoice['icon'])): ?><img src="<?= htmlspecialchars($lbChoice['icon'], ENT_QUOTES, 'UTF-8') ?>" alt=""><?php endif; ?>
                <span class="lb-r5s-choice-name"><?= htmlspecialchars($lbChoice['name'], ENT_QUOTES, 'UTF-8') ?></span>
                <?php if (!empty($lbChoice['lane'])): ?><span class="lb-r5s-choice-lane"><?= htmlspecialchars($lbChoice['lane'], ENT_QUOTES, 'UTF-8') ?></span><?php endif; ?>
              </label>
            <?php endforeach; ?>
          </div>
          <div class="small text-muted mt-3">Only the selected booster will receive the notification.</div>
        </div>

        <div class="modal-footer">
          <button type="button" class="btn lb-btn lb-btn-ghost" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn lb-btn lb-btn-success"><i class="fa-duotone fa-bell-on me-2"></i> Notify Booster</button>
        </div>
      </div>
    </div>
  </div>
</form>
<?php endif; ?>

<!-- CHANGE BOOSTER (Pending requested booster confirm modal) -->
<form class="ajax-form" action="<?= AJAX_URL ?>">
  <input type="hidden" name="action" value="client_change_booster_request">
  <input type="hidden" name="order_id" value="<?= $data['id'] ?>">
  <?php if (empty($lb_is_multi_booster_order)): ?>
    <input type="hidden" name="booster_id" value="<?= $lb_orderRow['booster_id'] ?? '' ?>">
  <?php endif; ?>

  <div id="client_change_booster_confirm_md" class="modal fade lbx-modal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
      <div class="modal-content lb-confirmModal2">

        <div class="lb-confirmModal2__header">
          <div class="lb-confirmModal2__headLeft">
            <div class="lb-confirmModal2__icon"><i class="fa-duotone fa-user-xmark"></i></div>
            <div class="lb-confirmModal2__titles">
              <div class="lb-confirmModal2__title">Are you sure, you want request a new booster?</div>
              <div class="lb-confirmModal2__sub">Your current booster will be removed and the order will go back to the panel.</div>
            </div>
          </div>

          <button type="button" class="lb-confirmModal2__close" data-bs-dismiss="modal" aria-label="Close">
            <i class="fa-solid fa-xmark"></i>
          </button>
        </div>

        <div class="lb-confirmModal2__body">
          <div class="lb-confirmModal2__summary">
            <div class="lb-confirmModal2__row">
              <span>Order</span>
              <strong>#<?= (int)$data['id'] ?></strong>
            </div>
            <?php if (!empty($lb_is_multi_booster_order) && !empty($lb_selectable_boosters)): ?>
              <div class="lb-confirmModal2__row" style="display:block">
                <span style="display:block;margin-bottom:10px">Choose booster request</span>
                <div class="lb-r5s-choice-grid" data-lb-choice-group="change-request">
                  <?php foreach ($lb_selectable_boosters as $idx => $lbChoice): ?>
                    <label class="lb-r5s-choice <?= $idx === 0 ? 'is-active' : '' ?>">
                      <input type="radio" name="booster_id" value="<?= (int)$lbChoice['id'] ?>" <?= $idx === 0 ? 'checked' : '' ?>>
                      <?php if (!empty($lbChoice['icon'])): ?><img src="<?= htmlspecialchars($lbChoice['icon'], ENT_QUOTES, 'UTF-8') ?>" alt=""><?php endif; ?>
                      <span class="lb-r5s-choice-name"><?= htmlspecialchars($lbChoice['name'], ENT_QUOTES, 'UTF-8') ?></span>
                      <span class="lb-r5s-choice-lane"><?= ($lbChoice['status'] ?? '') === 'requested' ? 'Requested · waiting' : 'Active booster' ?></span>
                    </label>
                  <?php endforeach; ?>
                </div>
              </div>
            <?php else: ?>
              <div class="lb-confirmModal2__row">
                <span>Current booster</span>
                <strong><?= htmlspecialchars(($claimedBooster['username'] ?? $boosterName ?? $lb_orderRow['booster_username'] ?? $lb_orderRow['booster_name'] ?? 'Booster')) ?></strong>
              </div>
            <?php endif; ?>
          </div>

          <div class="lb-confirmModal2__notice">
            <div class="lb-confirmModal2__noticeIcon"><i class="fa-solid fa-circle-info"></i></div>
            <div class="lb-confirmModal2__noticeText">Next free booster will claim your order asap</div>
          </div>
        </div>

        <div class="lb-confirmModal2__footer">
          <button type="button" class="lb-confirmModal2__btn lb-confirmModal2__btn--ghost" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="lb-confirmModal2__btn lb-confirmModal2__btn--primary">
            <i class="fa-solid fa-check me-2"></i>Yes, request new booster
          </button>
        </div>

      </div>
    </div>
  </div>
</form>

<!-- CHANGE BOOSTER (Client custom modal) -->
<form class="ajax-form" action="<?= AJAX_URL ?>">
  <input type="hidden" name="action" value="client_request_booster_change">
  <input type="hidden" name="order_id" value="<?= $data['id'] ?>">
  <input type="hidden" name="booster_id" value="<?= $lb_orderRow['booster_id'] ?? '' ?>">

  <div id="client_change_booster_md" class="modal fade lbx-modal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
      <div class="modal-content lbx-modal__content">

        <div class="lbx-modal__header">
          <div class="lbx-modal__headLeft">
            <div class="lbx-modal__icon"><i class="fa-duotone fa-user-xmark"></i></div>
            <div class="lbx-modal__titles">
              <div class="lbx-modal__title">Request New <?= htmlspecialchars($lbRoleLabel) ?></div>
              <div class="lbx-modal__sub">Tell us why you need a new <?= htmlspecialchars($lbRoleLabelLc) ?>. The clearer, the faster we can help.</div>
            </div>
          </div>

          <button type="button" class="lbx-modal__close" data-bs-dismiss="modal" aria-label="Close">
            <i class="fa-solid fa-xmark"></i>
          </button>
        </div>

        <div class="lbx-modal__body">
          
<?php if ($lb_cbLocked): ?>
  <div class="alert alert-info py-2 mb-3" style="border-radius: 12px;">
    <strong>Admin checking:</strong>
    Our admin team is currently reviewing your change <?= htmlspecialchars($lbRoleLabelLc) ?> request.
    Please wait <strong><?= (int)ceil($lb_cbRemaining/60) ?></strong> minute(s) before sending another request.
  </div>
<?php endif; ?>


<label class="lbx-modal__label">Progress status</label>

<div class="lb-progressCards" role="radiogroup" aria-label="Progress status">
  <label class="lb-progressCard">
    <input type="radio" name="progress_status" value="made" checked <?= $lb_cbLocked ? "disabled" : "" ?>>
    <div class="lb-progressCard__inner">
      <div class="lb-progressCard__icon lb-progressCard__icon--ok">
        <i class="fa-solid fa-check"></i>
      </div>
      <div class="lb-progressCard__text">
        <div class="lb-progressCard__title">Progress was made</div>
        <div class="lb-progressCard__sub">Games played or LP/MMR changed.</div>
      </div>
    </div>
  </label>

  <label class="lb-progressCard lb-progressCard--danger">
    <input type="radio" name="progress_status" value="none" <?= $lb_cbLocked ? "disabled" : "" ?>>
    <div class="lb-progressCard__inner">
      <div class="lb-progressCard__icon lb-progressCard__icon--warn">
        <i class="fa-solid fa-triangle-exclamation"></i>
      </div>
      <div class="lb-progressCard__text">
        <div class="lb-progressCard__title">0 progress</div>
        <div class="lb-progressCard__sub">No games played, no LP change.</div>
      </div>
    </div>
  </label>
</div>

<div id="lb_zero_progress_note" class="lb-zeroNote lb-hide">
  <strong>Only choose “0 progress”</strong> if absolutely nothing changed.
</div>

<label class="lbx-modal__label mt-3" id="lb_order_progress_label">Order progress</label>
<input class="lbx-modal__control" id="lb_order_progress" name="order_progress" <?= $lb_cbLocked ? "disabled" : "" ?>
  placeholder="Example: Started Gold II 12LP → Now Gold I (53 LP)">
<div class="lbx-modal__help" id="lb_order_progress_help">Required unless you select <strong>0 progress</strong>.</div>

<label class="lbx-modal__label mt-3">Reason</label>

          <textarea class="lbx-modal__control" name="reason" rows="5" <?= $lb_cbLocked ? "disabled" : "" ?>
            placeholder="Example: No communication / Wrong language / Inactive / No progress..."></textarea>
        </div>

        <div class="lbx-modal__footer">
          <button type="button" class="lbx-modal__btn lbx-modal__btn--ghost" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="lbx-modal__btn lbx-modal__btn--action" <?= $lb_cbLocked ? "disabled" : "" ?>>
            <i class="fa-solid fa-arrow-right-long me-2"></i> <?= $lb_cbLocked ? "Please wait" : "Send Request" ?>
          </button>
        </div>

      </div>
    </div>
  </div>
</form>

<script>
  document.addEventListener('DOMContentLoaded', function () {
    const orderId = <?= (int)($data['id'] ?? 0) ?>;
    const cooldownSeconds = <?= (int)$lb_cbCooldownSeconds ?>;
    const lbRoleLabel = <?= json_encode($lbRoleLabel) ?>;
    const lbRoleLabelLc = <?= json_encode($lbRoleLabelLc) ?>;

    // Progress status (classic change booster modal only)
    const lbProgressRadios = Array.from(document.querySelectorAll('#client_change_booster_md input[name="progress_status"]'));
    const lbOrderProgressInput = document.getElementById('lb_order_progress');
    const lbOrderProgressLabel = document.getElementById('lb_order_progress_label');
    const lbOrderProgressHelp = document.getElementById('lb_order_progress_help');
    const lbZeroProgressNote = document.getElementById('lb_zero_progress_note');
    const lbCbLocked = <?= $lb_cbLocked ? "true" : "false" ?>;

    function lbGetProgressStatus() {
      const checked = lbProgressRadios.find(r => r.checked);
      return checked ? checked.value : 'made';
    }

    function lbSyncProgressUI() {
      if (!lbProgressRadios.length) return;
      const isZero = lbGetProgressStatus() === 'none';
      if (lbZeroProgressNote) lbZeroProgressNote.classList.toggle('lb-hide', !isZero);

      if (lbOrderProgressInput) {
        lbOrderProgressInput.disabled = lbCbLocked || isZero;
        lbOrderProgressInput.required = (!lbCbLocked && !isZero);
        if (isZero) lbOrderProgressInput.value = '';
      }

      if (lbOrderProgressLabel) lbOrderProgressLabel.style.opacity = isZero ? '.55' : '1';
      if (lbOrderProgressHelp) lbOrderProgressHelp.innerHTML = isZero
        ? 'Not required for <strong>0 progress</strong>.'
        : 'Required unless you select <strong>0 progress</strong>.';
    }

    lbProgressRadios.forEach(r => r.addEventListener('change', lbSyncProgressUI));
    lbSyncProgressUI();

    function getRemainingSeconds() {
      let remaining = <?= (int)$lb_cbRemaining ?>;
      try {
        const key = 'cb_cooldown_until_' + orderId;
        const until = parseInt(localStorage.getItem(key) || '0', 10) || 0;
        const now = Date.now();
        if (until > now) {
          const lsRemaining = Math.ceil((until - now) / 1000);
          if (lsRemaining > remaining) remaining = lsRemaining;
        }
      } catch (e) {}
      return remaining;
    }

    function setCooldown(seconds) {
      try {
        const key = 'cb_cooldown_until_' + orderId;
        localStorage.setItem(key, String(Date.now() + (seconds * 1000)));
      } catch (e) {}
    }

    function showCooldownToast(seconds) {
      const mins = Math.ceil(seconds / 60);
      if (typeof create_toast === 'function') {
        create_toast('info', 'Please wait', `You can request another booster again in ${mins} minute(s).`);
      } else {
        alert(`Please wait. You can request again in ${mins} minute(s).`);
      }
    }

    document.querySelectorAll('.js-change-booster-trigger').forEach(function (el) {
      el.addEventListener('click', function (e) {
        const remaining = getRemainingSeconds();
        if (remaining > 0) {
          e.preventDefault();
          e.stopPropagation();
          if (typeof e.stopImmediatePropagation === 'function') e.stopImmediatePropagation();
          showCooldownToast(remaining);
        }
      }, true);

      if (getRemainingSeconds() > 0) {
        el.classList.add('opacity-50');
      }
    });

    ['client_change_booster_request', 'client_request_booster_change'].forEach(function (actionValue) {
      document.querySelectorAll('form.ajax-form input[name="action"][value="' + actionValue + '"]').forEach(function (input) {
        var form = input.closest('form');
        if (!form) return;
        form.addEventListener('submit', function () {
          setCooldown(cooldownSeconds);
        });
      });
    });

    const originalHandler = window.ajax_response_handler;
    if (typeof originalHandler === 'function') {
      window.ajax_response_handler = function (res) {
        try {
          const obj = typeof res === 'string' ? JSON.parse(res) : res;
          if (obj && obj.lockChangeBooster) {
            const secs = parseInt(obj.cooldownSeconds || cooldownSeconds, 10) || cooldownSeconds;
            setCooldown(secs);
          }
        } catch (e) {}
        return originalHandler(res);
      }
    }

    // custom dropdown for al_vpn_country_ui is handled by lb-csd init script

  });
</script>


<!-- ORDER NOTES MODAL -->
<div id="order_notes_md" class="modal fade lb-modal lb-modal--note" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content">

      <div class="modal-header">
        <div class="lb-modal-head">
          <div class="lb-modal-ico">
            <i class="fa-duotone fa-note-sticky"></i>
          </div>

          <div class="lb-modal-headtxt">
            <h5 class="lb-modal-title">Order Notes</h5>
            <p class="lb-modal-sub">Keep important details for your <?= htmlspecialchars($lbRoleLabelLc) ?> & staff.</p>
          </div>
        </div>

        <button type="button" class="lb-modal-x" data-bs-dismiss="modal" aria-label="Close">
          <i class="fa-solid fa-xmark"></i>
        </button>
      </div>

      <div class="modal-body">

        <?php if (!empty($data['notes'])): ?>
          <div class="lb-notes-list">
            <?php foreach ($data['notes'] as $note): ?>
              <div class="lb-note-item">
                <div class="lb-note-ico">
                  <i class="fa-duotone fa-note-sticky"></i>
                </div>

                <div class="lb-note-content">
                  <div class="lb-note-text">
                    <?= nl2br(htmlspecialchars((string) $note['order_note'])) ?>
                  </div>

                  <div class="lb-note-meta">
                    <span class="lb-note-chip">Note #<?= (int) $note['id'] ?></span>
                    <?php if (!empty($note['created_at'])): ?>
                      <span class="lb-note-chip"><?= htmlspecialchars((string) $note['created_at']) ?></span>
                    <?php endif; ?>
                  </div>
                </div>

                <div class="lb-note-actions">
                  <button class="lb-note-action" type="button" data-bs-toggle="modal" data-bs-target="#edit-note-modal"
                    data-note-id="<?= (int) $note['id'] ?>">
                    <i class="fa-duotone fa-pen-to-square"></i>
                  </button>

                  <button class="lb-note-action lb-note-action--danger" type="button" data-bs-toggle="modal"
                    data-bs-target="#delete-note-modal" data-note-id="<?= (int) $note['id'] ?>">
                    <i class="fa-duotone fa-trash"></i>
                  </button>
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
              Add a note to keep important details — short and clear works best.
            </div>
          </div>
        <?php endif; ?>

      </div>

      <div class="modal-footer">
        <button type="button" class="btn lb-btn lb-btn-ghost" data-bs-dismiss="modal">Close</button>
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#create-note-modal">
          New Note
        </button>
      </div>

    </div>
  </div>
</div>

<!-- NOTES CRUD -->
<form class="ajax-form" action="<?= AJAX_URL ?>">
  <input type="hidden" name="action" value="client_create_note">
  <input type="hidden" name="order_id" value="<?= $data['id'] ?>">

  <div id="create-note-modal" class="modal fade lb-modal lb-modal--note" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">

        <div class="modal-header">
          <div>
            <h5 class="lb-modal-title">
              <i class="fa-duotone fa-note-sticky"></i>
              New Note
            </h5>
            <p class="lb-modal-sub">Add a note to this order.</p>
          </div>

          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>

        <div class="modal-body">
          <label class="lb-field-label">Note content</label>
          <textarea class="form-control lb-textarea" name="note" rows="6"
            placeholder="Something that we should keep in mind while processing your order..."></textarea>

          <div class="lb-helper">Tip: Keep it short & clear so staff/<?= htmlspecialchars($lbRoleLabelLc) ?> can react quickly.</div>
        </div>

        <div class="modal-footer">
          <button type="button" class="btn btn-white border" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary fw-bold px-4">Create Note</button>
        </div>

      </div>
    </div>
  </div>
</form>


<form class="ajax-form" action="<?= AJAX_URL ?>">
  <input type="hidden" name="action" value="client_edit_note">
  <input type="hidden" name="note_id" value="">

  <div id="edit-note-modal" class="modal fade lb-modal lb-modal--note" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">

        <div class="modal-header">
          <div>
            <h5 class="lb-modal-title">
              <i class="fa-duotone fa-pen-to-square"></i>
              Edit Note
            </h5>
            <p class="lb-modal-sub">Update your note for this order.</p>
          </div>

          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>

        <div class="modal-body">
          <label class="lb-field-label">Note content</label>
          <textarea class="form-control lb-textarea" name="note" rows="6"
            placeholder="Something that we should keep in mind while processing your order..."></textarea>
        </div>

        <div class="modal-footer">
          <button type="button" class="btn btn-white border" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary fw-bold px-4">Save Changes</button>
        </div>

      </div>
    </div>
  </div>
</form>


<form class="ajax-form" action="<?= AJAX_URL ?>">
  <input type="hidden" name="action" value="client_delete_note">
  <input type="hidden" name="note_id" value="">

  <div id="delete-note-modal" class="modal fade lb-modal lb-modal--note" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">

        <div class="modal-header">
          <div>
            <h5 class="lb-modal-title">
              <i class="fa-duotone fa-triangle-exclamation text-danger"></i>
              Delete Note
            </h5>
            <p class="lb-modal-sub">This action cannot be undone.</p>
          </div>

          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>

        <div class="modal-body">
          <div class="lb-hint" style="background: rgba(255,107,107,.10); border-color: rgba(255,107,107,.20);">
            <i class="fa-duotone fa-trash text-danger" style="font-size:1.1rem;"></i>
            <div>
              <div class="fw-bold">Are you sure you want to delete this note?</div>
              <div class="small opacity-75">You won’t be able to recover it afterwards.</div>
            </div>
          </div>
        </div>

        <div class="modal-footer">
          <button type="button" class="btn btn-white border" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-danger fw-bold px-4">
            <i class="fa-solid fa-trash me-2"></i> Delete
          </button>
        </div>

      </div>
    </div>
  </div>
</form>


<?php if (!$isCoachingOrder): ?>
<!-- SERVICE GUIDE (SOLO / DUO) -->
<?php
// Show once per order (per browser) while the order is active.
$serviceGuideOrderId = $data['order_id'] ?? $data['id'] ?? null;
$serviceGuideIsDuo = ((int) ($data['is_duo'] ?? 0) === 1) || ((int) ($data['is_hidden_duo'] ?? 0) === 1);
$serviceGuideType = $serviceGuideIsDuo ? 'DUO' : 'SOLO';

$serviceGuideSolo = [
  'Do not play ranked games while your SOLO order is running.',
  'If you want to play, pause your order first.',
  'Stay responsive in chat to speed up the process.',
  'Be respectful - boosters are humans and good communication helps a lot.',
  'If you need changes (add-ons / adjustments), use the dashboard options or contact support.',
];

$serviceGuideDuo = [
  'Schedule your playtime with your booster (DUO requires coordination).',
  'Be on time and stay responsive in chat so the booster can start quickly.',
  'Use the in-dashboard chat for updates (avoid switching platforms).',
  'Be respectful - boosters are humans and good communication helps a lot.',
  'If something changes, pause the order and contact support.',
];

$serviceGuideItems = $serviceGuideIsDuo ? $serviceGuideDuo : $serviceGuideSolo;

// Only show for active states (adjust if you want it to appear for more statuses).
$serviceGuideShouldShow = in_array($statusKey, ['PAID', 'IN_PROGRESS', 'PAUSED', 'PROCESSING'], true);
?>

<?php if (!empty($serviceGuideOrderId) && $serviceGuideShouldShow): ?>
  <div class="modal fade lb-modal" id="serviceGuideModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static"
    data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered modal-fullscreen-md-down">
      <div class="modal-content d-flex flex-column">

        <div class="modal-header">
          <div>
            <h5 class="lb-modal-title">Service Guide</h5>
            <p class="lb-modal-sub">Please confirm the rules before continuing.</p>
          </div>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>

        <div class="modal-body flex-grow-1">
          <div class="d-flex align-items-center gap-2 mb-3">
            <span class="badge bg-secondary sg-pill" style="border-radius:10px; padding: .45rem .6rem;">Order Type</span>
            <span class="badge bg-primary sg-pill"
              style="border-radius:10px; padding: .45rem .7rem; letter-spacing:.02em;">
              <?= htmlspecialchars($serviceGuideType) ?>
            </span>
          </div>

          <div class="small opacity-75 mb-3 sg-subtitle">Your active options and guidelines are listed below.</div>

          <ol class="m-0 p-0" style="list-style: none; display:flex; flex-direction:column; gap:.75rem;">
            <?php foreach ($serviceGuideItems as $i => $txt): ?>
              <li class="lb-hint sg-guideline"
                style="background: rgba(255,255,255,.04); border-color: rgba(255,255,255,.08);">
                <div class="d-flex align-items-center gap-3">
                  <div class="d-flex align-items-center justify-content-center sg-num"
                    style="width:34px; height:34px; border-radius:10px; background: rgba(255,255,255,.06); border: 1px solid rgba(255,255,255,.10); font-weight:800;">
                    <?= (int) ($i + 1) ?>
                  </div>
                  <div class="flex-grow-1 sg-text"><?= htmlspecialchars($txt) ?></div>
                </div>
              </li>
            <?php endforeach; ?>
          </ol>

          <div class="lb-hint mt-3 sg-alert" style="background: rgba(255,193,7,.10); border-color: rgba(255,193,7,.25);">
            <i class="fa-duotone fa-triangle-exclamation" style="font-size:1.1rem;"></i>
            <div>
              <div class="fw-bold">Not following these guidelines may result in delays or issues with your order.</div>
            </div>
          </div>
        </div>

        <div class="modal-footer" style="border-top: 1px solid rgba(255,255,255,.06);">
          <button type="button" class="btn btn-primary fw-bold w-100" id="serviceGuideAcknowledgeBtn">
            I Understand
          </button>
        </div>

      </div>
    </div>
  </div>
<?php endif; ?>



<?php endif; ?>
<!-- PAUSE / RESUME -->
<?php if (in_array($data['status'], ['IN_PROGRESS', 'PAID'], true)): ?>
  <form class="ajax-form" action="<?= AJAX_URL ?>">
    <input type="hidden" name="action" value="client_pause_order">
    <input type="hidden" name="order_id" value="<?= $data['id'] ?>">

    <div class="modal fade lb-modal lb-modal--pause" id="pause_order_md" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">

          <div class="modal-header">
            <div class="lb-modal-head">
              <div class="lb-modal-ico">
                <i class="fa-duotone fa-circle-pause"></i>
              </div>

              <div class="lb-modal-headtxt">
                <h5 class="lb-modal-title">Pause Order</h5>
                <p class="lb-modal-sub">This will temporarily stop the progress until you resume it.</p>
              </div>
            </div>

            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>

          <div class="modal-body">
            <div class="lb-modal-warning">
              <i class="fa-duotone fa-triangle-exclamation"></i>
              <div>
                <div class="lb-modal-warning-title">Are you sure?</div>
                <div class="lb-modal-warning-sub">
                  Your <?= htmlspecialchars($lbRoleLabelLc) ?> will be notified and the order will be set to <b>Paused</b>.
                </div>
              </div>
            </div>
          </div>

          <div class="modal-footer">
            <button type="button" class="btn lb-btn lb-btn-ghost" data-bs-dismiss="modal">
              Cancel
            </button>

            <button type="submit" class="btn lb-btn lb-btn-danger">
              <i class="fa-duotone fa-pause me-2"></i> Confirm Pause
            </button>
          </div>

        </div>
      </div>
    </div>

  </form>
<?php elseif ($statusKey === 'PAUSED'): ?>
  <form class="ajax-form" action="<?= AJAX_URL ?>">
    <input type="hidden" name="action" value="client_resume_order">
    <input type="hidden" name="order_id" value="<?= (int) $data['id'] ?>">

    <div class="modal fade lb-modal lb-modal--resume" id="resume_order_md" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">

          <div class="modal-header">
            <div class="lb-modal-head">
              <div class="lb-modal-ico">
                <i class="fa-duotone fa-play"></i>
              </div>

              <div class="lb-modal-headtxt">
                <h5 class="lb-modal-title">Resume Order</h5>
                <p class="lb-modal-sub">Continue progress from where your <?= htmlspecialchars($lbRoleLabelLc) ?> stopped.</p>
              </div>
            </div>

            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>

          <div class="modal-body">
            <div class="lb-modal-info">
              <i class="fa-duotone fa-circle-info"></i>
              <div>
                <div class="lb-modal-info-title">Are you sure?</div>
                <div class="lb-modal-info-sub">
                  Your <?= htmlspecialchars($lbRoleLabelLc) ?> will be notified and the order status will switch back to <b>In Progress</b>.
                </div>
              </div>
            </div>
          </div>

          <div class="modal-footer">
            <button type="button" class="btn lb-btn lb-btn-ghost" data-bs-dismiss="modal">
              Cancel
            </button>

            <button type="submit" class="btn lb-btn lb-btn-success">
              <i class="fa-duotone fa-play me-2"></i> Confirm Resume
            </button>
          </div>

        </div>
      </div>
    </div>
  </form>
<?php endif; ?>


<form id="addon-form">
  <input type="hidden" name="action" value="client_addon_payment">
  <input type="hidden" name="order_id" value="<?= $data['order_id'] ?? $data['id'] ?>">
  <input type="hidden" name="currency" value="<?= $data['currency'] ?? 'EUR' ?>">

  <div id="addon_payment_md" class="modal fade lbx-modal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-lg modal-dialog-centered">
      <div class="modal-content lbx-modal__content lbap-modal" id="step-form">

        <!-- ═══ STEP 1 ═══ -->
        <div id="step-1">
          <div class="lbx-modal__header">
            <div class="lbx-modal__headLeft">
              <div class="lbx-modal__icon">
                <i class="fa-duotone fa-sliders"></i>
              </div>
              <div>
                <div class="lbx-modal__title">Customize Order</div>
                <div class="lbx-modal__sub">Customize your order with extra services</div>
              </div>
            </div>
            <button type="button" class="lbx-modal__close" data-bs-dismiss="modal" aria-label="Close">
              <i class="fa-solid fa-xmark"></i>
            </button>
          </div>

          <div class="lbap-tabs">
            <button type="button" class="lbap-tab active" data-lbap-tab="extra-opts">
              <i class="fa-duotone fa-bolt"></i> Extra Options
            </button>
            <button type="button" class="lbap-tab" data-lbap-tab="custom-amount">
              <i class="fa-duotone fa-pen-to-square"></i> Custom Amount
            </button>
            <?php if ($lb_can_server_change_addon): ?>
            <button type="button" class="lbap-tab" data-lbap-tab="server-change">
              <i class="fa-duotone fa-globe"></i> Server Change
            </button>
            <?php endif; ?>
            <?php if ((int)($data['form_id'] ?? 0) === 1): ?>
            <button type="button" class="lbap-tab" data-lbap-tab="lp-correction">
              <i class="fa-duotone fa-chart-line-up"></i> LP Correction
            </button>
            <?php endif; ?>
          </div>

          <div class="lbx-modal__body lbap-body">

            <!-- TAB: Extra Options -->
            <div class="lbap-tab-panel active" id="lbap-extra-opts">
              <div class="lbap-two-col">

                <!-- Left: addon list -->
                <div class="lbap-addons">
                  <?php if (empty($addons)): ?>
                    <div class="lbap-empty">
                      <i class="fa-duotone fa-box-open"></i>
                      No add-ons available at the moment.
                    </div>
                  <?php else: ?>
                    <?php foreach ($addons as $count => $addon): ?>
                      <label class="lbap-addon-card" for="addon-chk-<?= $count ?>">
                        <?= lb_addon_option_icon_html($addon) ?>
                        <div class="lbap-addon-card__info">
                          <div class="lbap-addon-card__name"><?= $addon['label'] ?? 'N/A' ?></div>
                          <div class="lbap-addon-card__desc"><?= $addon['description'] ?? '' ?></div>
                        </div>
                        <div class="lbap-addon-card__right">
                          <div class="lbap-addon-card__price">
                            <?= util_format_currency_display($addon['currency']) . $addon['price_formatted'] ?>
                          </div>
                          <div class="lbap-switch">
                            <input class="js-toggle-switch lbap-switch__input" type="checkbox"
                              id="addon-chk-<?= $count ?>"
                              name="addons[<?= $addon['key'] ?>]" value="<?= $addon['price'] ?>">
                            <span class="lbap-switch__track"></span>
                          </div>
                        </div>
                      </label>
                    <?php endforeach; ?>
                  <?php endif; ?>
                </div>

                <!-- Right: summary -->
                <div class="lbap-summary">
                  <div class="lbap-summary__box">
                    <div class="lbap-summary__label">Order Total</div>
                    <div class="lbap-summary__total">
                      <span class="lbap-summary__currency"><?= util_format_currency_display($data['currency']) ?></span>
                      <span id="addon_total_price">0.00</span>
                    </div>
                    <div class="lbap-summary__notes">
                      <div class="lbap-summary__note"><i class="fa-solid fa-circle-check"></i> Excluding VAT</div>
                      <div class="lbap-summary__note"><i class="fa-solid fa-circle-check"></i> Billed as add-on to this order</div>
                    </div>
                  </div>
                  <div class="lbap-summary__hint">
                    Clicking "Continue" redirects you to the payment gateway to complete the transaction.
                  </div>
                </div>

              </div>
            </div>

            <!-- TAB: Custom Amount -->
            <div class="lbap-tab-panel" id="lbap-custom-amount">
              <div class="lbap-two-col">

                <div class="lbap-addons">
                  <div class="lbap-custom-fields">
                    <div class="lbap-field">
                      <label class="lbap-field__label">Reason</label>
                      <input name="custom_reason" type="text" placeholder="e.g. Priority Boost, Tip for Booster…" class="lbap-field__input">
                    </div>
                    <div class="lbap-field">
                      <label class="lbap-field__label">Amount</label>
                      <div class="lbap-field__amount-wrap">
                        <span class="lbap-field__currency"><?= util_format_currency_display($data['currency']) ?></span>
                        <input name="custom_amount" type="text" inputmode="decimal" autocomplete="off"
                          placeholder="0.00" class="lbap-field__input lbap-field__input--amount">
                      </div>
                    </div>
                  </div>
                </div>

                <div class="lbap-summary">
                  <div class="lbap-summary__box">
                    <div class="lbap-summary__label">Order Total</div>
                    <div class="lbap-summary__total">
                      <span class="lbap-summary__currency"><?= util_format_currency_display($data['currency']) ?></span>
                      <span id="addon_custom_total">0.00</span>
                    </div>
                    <div class="lbap-summary__notes">
                      <div class="lbap-summary__note"><i class="fa-solid fa-circle-check"></i> Excluding VAT</div>
                      <div class="lbap-summary__note"><i class="fa-solid fa-circle-check"></i> Billed as add-on to this order</div>
                    </div>
                  </div>
                  <div class="lbap-summary__hint">
                    Clicking "Continue" redirects you to the payment gateway to complete the transaction.
                  </div>
                </div>

              </div>
            </div>


            <!-- TAB: Server Change -->
            <?php if ($lb_can_server_change_addon): ?>
            <div class="lbap-tab-panel" id="lbap-server-change">
              <div class="lbap-two-col">
                <div class="lbap-addons lbap-lp-panel">
                  <div class="lbap-lp-note">
                    <i class="fa-duotone fa-circle-info"></i>
                    Select the new server for this order. The surcharge is calculated from the price JSON and only charges the difference.
                  </div>

                  <div class="lbap-lp-section">
                    <div class="lbap-lp-section__head">
                      <span class="lbap-lp-section__icon">🌍</span>
                      <div>
                        <div class="lbap-lp-section__title">Change Server</div>
                        <div class="lbap-lp-section__booked">Current server: <strong><?= htmlspecialchars($lb_server_labels[$lb_current_server] ?? strtoupper($lb_current_server)) ?></strong></div>
                      </div>
                    </div>
                    <div class="lbap-lp-grid" id="lbap-server-grid">
                      <?php foreach ($lb_server_candidates as $srv): ?>
                        <?php $isCurrentServer = ($srv === $lb_current_server); ?>
                        <button type="button"
                          class="lbap-lp-tile <?= $isCurrentServer ? 'is-booked' : '' ?>"
                          data-server-value="<?= htmlspecialchars($srv) ?>"
                          data-server-label="<?= htmlspecialchars($lb_server_labels[$srv] ?? strtoupper($srv)) ?>"
                          <?= $isCurrentServer ? 'disabled' : '' ?>>
                          <span class="lbap-lp-tile__label"><?= htmlspecialchars($lb_server_labels[$srv] ?? strtoupper($srv)) ?></span>
                          <?php if ($isCurrentServer): ?><span class="lbap-lp-tile__tag">Current</span><?php endif; ?>
                        </button>
                      <?php endforeach; ?>
                    </div>
                  </div>

                  <input type="hidden" name="server_change_to" id="server_change_to" value="">
                  <input type="hidden" name="server_change_label" id="server_change_label" value="">
                  <input type="hidden" name="server_change_price" id="server_change_price" value="0">
                </div>

                <div class="lbap-summary">
                  <div class="lbap-summary__box">
                    <div class="lbap-summary__label">Surcharge</div>
                    <div class="lbap-summary__total">
                      <span class="lbap-summary__currency"><?= util_format_currency_display($data['currency']) ?></span>
                      <span id="addon_server_total">0.00</span>
                    </div>
                    <div class="lbap-summary__notes">
                      <div class="lbap-summary__note"><i class="fa-solid fa-circle-check"></i> Based on server difference</div>
                      <div class="lbap-summary__note"><i class="fa-solid fa-circle-check"></i> Excluding VAT</div>
                    </div>
                  </div>
                  <div class="lbap-summary__hint">
                    After payment, the order server will be updated automatically.
                  </div>
                </div>
              </div>
            </div>
            <?php endif; ?>

            <!-- TAB: LP Correction -->
            <?php
            $lbHasLpGain  = isset($data['lp_gain']) && trim((string)$data['lp_gain']) !== '';
            $lbHasStartLp = array_key_exists('start_lp', $data) && trim((string)$data['start_lp']) !== '';
            if ((int)($data['form_id'] ?? 0) === 1):
            ?>
            <div class="lbap-tab-panel" id="lbap-lp-correction">
              <div class="lbap-two-col">

                <div class="lbap-addons lbap-lp-panel">

                  <div class="lbap-lp-note">
                    <i class="fa-duotone fa-circle-info"></i>
                    Your actual LP is lower than what was selected. Pick the correct values below — the surcharge is calculated automatically.
                  </div>

                  <?php
                  // ── LP GAIN section ─────────────────────────────────────
                  // Tiers ordered high→low; index 0 = highest
                  $lpGainTiers = [
                    ['label' => '30+ LP / Win',   'value' => '30+',   'idx' => 0],
                    ['label' => '25–29 LP / Win',  'value' => '25-29', 'idx' => 1],
                    ['label' => '20–24 LP / Win',  'value' => '20-24', 'idx' => 2],
                    ['label' => '10–19 LP / Win',  'value' => '10-19', 'idx' => 3],
                  ];
                  $bookedGainRaw = trim((string)($data['lp_gain'] ?? ''));
                  $bookedGainIdx = 2; // default 20-24
                  foreach ($lpGainTiers as $t) {
                    if (stripos($bookedGainRaw, $t['value']) !== false ||
                        stripos($bookedGainRaw, str_replace(' ','',$t['value'])) !== false) {
                      $bookedGainIdx = $t['idx']; break;
                    }
                  }
                  // Also match numeric like "20-24" in the raw string
                  if ($bookedGainIdx === 2 && is_numeric($bookedGainRaw)) {
                    $n = (int)$bookedGainRaw;
                    if ($n >= 30)      $bookedGainIdx = 0;
                    elseif ($n >= 25)  $bookedGainIdx = 1;
                    elseif ($n >= 20)  $bookedGainIdx = 2;
                    else               $bookedGainIdx = 3;
                  }
                  ?>

                  <?php if ($lbHasLpGain): ?>
                  <div class="lbap-lp-section">
                    <div class="lbap-lp-section__head">
                      <span class="lbap-lp-section__icon">📈</span>
                      <div>
                        <div class="lbap-lp-section__title">LP Gain per Win</div>
                        <div class="lbap-lp-section__booked">Booked: <strong><?= htmlspecialchars($bookedGainRaw) ?></strong></div>
                      </div>
                    </div>
                    <div class="lbap-lp-grid" id="lbap-lpgain-grid"
                         data-section="lpgain"
                         data-booked-idx="<?= $bookedGainIdx ?>">
                      <?php foreach ($lpGainTiers as $t):
                        $isBooked   = ($t['idx'] === $bookedGainIdx);
                        $isDisabled = ($t['idx'] <= $bookedGainIdx); // can only go lower (higher idx = lower tier)
                      ?>
                        <button type="button"
                          class="lbap-lp-tile <?= $isBooked ? 'is-booked' : '' ?> <?= $isDisabled && !$isBooked ? 'is-disabled' : '' ?>"
                          data-lp-label="<?= htmlspecialchars($t['label']) ?>"
                          data-lp-value="<?= $t['value'] ?>"
                          data-lp-idx="<?= $t['idx'] ?>"
                          <?= ($isDisabled && !$isBooked) ? 'disabled' : '' ?>>
                          <span class="lbap-lp-tile__label"><?= $t['label'] ?></span>
                          <?php if ($isBooked): ?><span class="lbap-lp-tile__tag">Booked</span><?php endif; ?>
                        </button>
                      <?php endforeach; ?>
                    </div>
                  </div>
                  <?php endif; ?>

                  <?php
                  // ── CURRENT LP section ───────────────────────────────────
                  $currentLpTiers = [
                    ['label' => '81–100 LP', 'value' => '81-100', 'idx' => 0],
                    ['label' => '61–80 LP',  'value' => '61-80',  'idx' => 1],
                    ['label' => '41–60 LP',  'value' => '41-60',  'idx' => 2],
                    ['label' => '21–40 LP',  'value' => '21-40',  'idx' => 3],
                    ['label' => '0–20 LP',   'value' => '0-20',   'idx' => 4],
                  ];
                  $bookedClpRaw = trim((string)($data['start_lp'] ?? ''));
                  $bookedClpIdx = 3; // default 21-40
                  foreach ($currentLpTiers as $t) {
                    if (stripos($bookedClpRaw, $t['value']) !== false ||
                        stripos($bookedClpRaw, str_replace(' ','',$t['value'])) !== false) {
                      $bookedClpIdx = $t['idx']; break;
                    }
                  }
                  if ($bookedClpIdx === 3 && is_numeric($bookedClpRaw)) {
                    $n = (int)$bookedClpRaw;
                    if ($n >= 81)      $bookedClpIdx = 0;
                    elseif ($n >= 61)  $bookedClpIdx = 1;
                    elseif ($n >= 41)  $bookedClpIdx = 2;
                    elseif ($n >= 21)  $bookedClpIdx = 3;
                    else               $bookedClpIdx = 4;
                  }
                  ?>

                  <?php if ($lbHasStartLp): ?>
                  <div class="lbap-lp-section">
                    <div class="lbap-lp-section__head">
                      <span class="lbap-lp-section__icon">🏁</span>
                      <div>
                        <div class="lbap-lp-section__title">Current LP</div>
                        <div class="lbap-lp-section__booked">Booked: <strong><?= htmlspecialchars($lb_format_lp_range($bookedClpRaw)) ?></strong></div>
                      </div>
                    </div>
                    <div class="lbap-lp-grid" id="lbap-clp-grid"
                         data-section="clp"
                         data-booked-idx="<?= $bookedClpIdx ?>">
                      <?php foreach ($currentLpTiers as $t):
                        $isBooked   = ($t['idx'] === $bookedClpIdx);
                        $isDisabled = ($t['idx'] <= $bookedClpIdx); // lower idx = higher LP = can't go higher
                      ?>
                        <button type="button"
                          class="lbap-lp-tile <?= $isBooked ? 'is-booked' : '' ?> <?= $isDisabled && !$isBooked ? 'is-disabled' : '' ?>"
                          data-lp-label="<?= htmlspecialchars($t['label']) ?>"
                          data-lp-value="<?= $t['value'] ?>"
                          data-lp-idx="<?= $t['idx'] ?>"
                          <?= ($isDisabled && !$isBooked) ? 'disabled' : '' ?>>
                          <span class="lbap-lp-tile__label"><?= $t['label'] ?></span>
                          <?php if ($isBooked): ?><span class="lbap-lp-tile__tag">Booked</span><?php endif; ?>
                        </button>
                      <?php endforeach; ?>
                    </div>
                  </div>
                  <?php endif; ?>

                  <input type="hidden" name="lp_correction_gain"       id="lp_correction_gain"  value="">
                  <input type="hidden" name="lp_correction_clp"        id="lp_correction_clp"   value="">
                  <input type="hidden" name="lp_correction_price"      id="lp_correction_price" value="0">

                </div>

                <div class="lbap-summary">
                  <div class="lbap-summary__box">
                    <div class="lbap-summary__label">Surcharge</div>
                    <div class="lbap-summary__total">
                      <span class="lbap-summary__currency"><?= util_format_currency_display($data['currency']) ?></span>
                      <span id="addon_lp_total">0.00</span>
                    </div>
                    <div class="lbap-summary__notes">
                      <div class="lbap-summary__note"><i class="fa-solid fa-circle-check"></i> Based on LP difference</div>
                      <div class="lbap-summary__note"><i class="fa-solid fa-circle-check"></i> Excluding VAT</div>
                    </div>
                  </div>
                  <div class="lbap-summary__hint">
                    Price per correction step is based on your order value. Both LP Gain and Current LP can be corrected at the same time.
                  </div>
                </div>

              </div>
            </div>
            <?php endif; ?>

          </div><!-- /.tab-content -->

          <div class="lbx-modal__footer">
            <button type="button" class="lbx-modal__btn lbx-modal__btn--ghost" data-bs-dismiss="modal">Cancel</button>
            <button type="button" class="lbx-modal__btn lbx-modal__btn--primary" id="addon_next_btn">
              Continue <i class="fa-solid fa-arrow-right ms-1"></i>
            </button>
          </div>
        </div>

        <!-- ═══ STEP 2: Checkout ═══ -->
        <div id="step-2" style="display: none;">
          <div class="lbx-modal__header">
            <div class="lbx-modal__headLeft">
              <div class="lbx-modal__icon">
                <i class="fa-duotone fa-credit-card"></i>
              </div>
              <div>
                <div class="lbx-modal__title">Checkout</div>
                <div class="lbx-modal__sub">Choose your payment method</div>
              </div>
            </div>
            <button type="button" class="lbx-modal__close" data-bs-dismiss="modal" aria-label="Close">
              <i class="fa-solid fa-xmark"></i>
            </button>
          </div>

          <div class="lbx-modal__body lbap-body">
            <div class="lbap-two-col">

              <!-- Payment methods -->
              <div class="lbap-addons">
                <div class="lbap-section-label">Payment Method</div>
                <ul class="payment-methods lbap-pay-list">
                  <li class="list-group-item p-0 border-0">
                    <label class="payment-item lbap-pay-item">
                      <input type="radio" name="processor" value="stripe" checked>
                      <div class="lbap-pay-item__icon lbap-pay-item__icon--stripe">
                        <i class="fa-brands fa-stripe-s"></i>
                      </div>
                      <div class="lbap-pay-item__info">
                        <div class="lbap-pay-item__name">Stripe</div>
                        <div class="lbap-pay-item__sub">Card · Apple Pay · Google Pay</div>
                      </div>
                      <span class="lbap-pay-badge lbap-pay-badge--blue">Recommended</span>
                    </label>
                  </li>
                  <li class="list-group-item p-0 border-0 mt-2">
                    <label class="payment-item lbap-pay-item">
                      <input type="radio" name="processor" value="stripe_paypal">
                      <div class="lbap-pay-item__icon lbap-pay-item__icon--paypal">
                        <i class="fa-brands fa-paypal"></i>
                      </div>
                      <div class="lbap-pay-item__info">
                        <div class="lbap-pay-item__name">PayPal</div>
                        <div class="lbap-pay-item__sub">Balance · Bank · Card</div>
                      </div>
                      <span class="lbap-pay-badge">Fast</span>
                    </label>
                  </li>
                  <!-- Coinbase is currently unavailable for add-on payments.
                       Kept disabled + hidden so it cannot be selected/clicked. -->
                  <li class="list-group-item p-0 border-0 mt-2 d-none" style="display: none;" aria-hidden="true">
                    <label class="payment-item lbap-pay-item lbap-pay-item--disabled">
                      <input type="radio" name="processor" value="coinbase" disabled>
                      <div class="lbap-pay-item__icon lbap-pay-item__icon--crypto">
                        <i class="fa-brands fa-bitcoin"></i>
                      </div>
                      <div class="lbap-pay-item__info">
                        <div class="lbap-pay-item__name">Coinbase</div>
                        <div class="lbap-pay-item__sub">Crypto Payments</div>
                      </div>
                      <span class="lbap-pay-badge lbap-pay-badge--disabled">Not available</span>
                    </label>
                  </li>
                </ul>
              </div>

              <!-- Summary -->
              <div class="lbap-summary">
                <div class="lbap-summary__box">
                  <div class="lbap-summary__label">Order Total</div>
                  <div class="lbap-summary__total">
                    <span class="lbap-summary__currency"><?= util_format_currency_display($data['currency']) ?></span>
                    <span id="addon_final_total">0.00</span>
                  </div>
                  <div class="lbap-summary__notes">
                    <div class="lbap-summary__note"><i class="fa-solid fa-circle-check"></i> Excluding VAT</div>
                    <div class="lbap-summary__note"><i class="fa-solid fa-circle-check"></i> Billed as add-on to this order</div>
                  </div>
                </div>
                <div class="lbap-summary__hint">
                  You will be redirected to the selected payment gateway to complete the transaction.
                </div>
              </div>

            </div>
          </div>

          <div class="lbx-modal__footer">
            <button type="button" class="lbx-modal__btn lbx-modal__btn--ghost" id="addon_back_btn">
              <i class="fa-solid fa-arrow-left me-1"></i> Back
            </button>
            <button type="submit" class="lbx-modal__btn lbx-modal__btn--primary">
              <div class="spinner-border spinner-border-sm d-none" role="status">
                <span class="visually-hidden">Loading...</span>
              </div>
              <i class="fa-duotone fa-lock me-1"></i>
              <span>Pay Now</span>
            </button>
          </div>
        </div>

      </div>
    </div>
  </div>
</form>

<?php
$canReview = ($data['status'] === 'COMPLETED' && !empty($lb_review_boosters));
?>

<?php if ($canReview): ?>
  <form id="leave-review-form">
    <input type="hidden" name="action" value="client_submit_review">
    <input type="hidden" name="order_id" value="<?= $data['id'] ?>">
    <input type="hidden" name="booster_id" value="<?= (int)($data['booster_id'] ?? 0) ?>" id="client_review_booster_id">

    <div id="leave_review_md" class="modal fade lb-modal lb-modal--review-full" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
          <div class="modal-header">
            <div class="lb-modal-head">
              <div class="lb-modal-ico lb-modal-ico--tip">
                <i class="fa-duotone fa-star"></i>
              </div>

              <div class="lb-modal-headtxt">
                <h5 class="lb-modal-title">Leave A Review</h5>
                <p class="lb-modal-sub">
                  Order # <?= htmlspecialchars((string) ($data['id'])) ?> •
                  <?= util_format_boost_overview($data['game'], $data['type'], $data) ?> • <span id="client_review_booster_name"><?= htmlspecialchars((string)($booster['username'] ?? $data['booster_username'] ?? 'Booster')) ?></span>
                </p>
              </div>
            </div>

            <button type="button" class="lb-modal-x" data-bs-dismiss="modal" aria-label="Close">
              <i class="fa-solid fa-xmark"></i>
            </button>
          </div>

          <div class="modal-body">
            <div class="lb-review-layout">
              <div class="lb-review-left">
                <div class="row g-3">
                  <div class="col-12 col-md-6">
                    <div class="lb-review-card p-3">
                  <h5>Communication</h5>
                  <input class="rating-input" type="text" name="score[communication]" value="">
                  <p class="text-muted mt-2 mb-0">
                    How good was the communication (updates, answers, friendliness)?
                  </p>
                    </div>
                  </div>
                  <div class="col-12 col-md-6">
                    <div class="lb-review-card p-3">
                  <h5>Skill</h5>
                  <input class="rating-input" type="text" name="score[skill]" value="">
                  <p class="text-muted mt-2 mb-0">
                    How strong was the booster in-game (decisions, consistency)?
                  </p>
                    </div>
                  </div>
                  <div class="col-12 col-md-6">
                    <div class="lb-review-card p-3">
                  <h5>Speed</h5>
                  <input class="rating-input" type="text" name="score[speed]" value="">
                  <p class="text-muted mt-2 mb-0">
                    How quickly was the order completed?
                  </p>
                    </div>
                  </div>
                  <div class="col-12 col-md-6">
                    <div class="lb-review-card p-3">
                  <h5>Overall</h5>
                  <input class="rating-input" type="text" name="score[overall]" value="">
                  <p class="text-muted mt-2 mb-0">
                    Overall impression.
                  </p>
                    </div>
                  </div>
                </div>
              </div>

              <div class="lb-review-right">
                <div class="lb-review-card lb-review-card--highlights p-3">
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
                        name="highlights[]" value="<?= htmlspecialchars($highlight) ?>" autocomplete="off"
                        >
                      <label class="btn rounded-pill" for="btn-check-<?= htmlspecialchars($highlight) ?>">
                        <?= ucwords(str_replace('_', ' ', $highlight)) ?>
                      </label>
                    <?php endforeach; ?>
                  </div>
                </div>

                <div class="lb-review-card lb-review-card--comments p-3">
                  <h5>Additional Comments (Optional)</h5>
                  <textarea class="form-control lb-textarea" name="comments" rows="3"
                    placeholder="Share more details about your experience..."></textarea>
                </div>
              </div>
            </div>
          </div>

          <div class="modal-footer d-flex justify-content-end">
            <button type="submit" class="btn btn-primary lb-btn lb-btn-success" id="addon_next_btn">
              <div class="spinner-border d-none" role="status">
                <span class="visually-hidden">Loading...</span>
              </div>
              <span>
                <span id="client_review_submit_label">Submit Review</span>
                <i class="fa-duotone fa-paper-plane ms-2"></i>
              </span>
            </button>
          </div>
        </div>
      </div>
    </div>
  </form>
<?php else: ?>
  <div id="view_review_md" class="modal fade lb-modal lb-modal--review-full" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
          <div class="lb-modal-head">
            <div class="lb-modal-ico lb-modal-ico--tip">
              <i class="fa-duotone fa-star"></i>
            </div>

            <div class="lb-modal-headtxt">
              <h5 class="lb-modal-title">Leave A Review</h5>
              <p class="lb-modal-sub">
                Order # <?= htmlspecialchars((string) ($data['id'])) ?> •
                <?= util_format_boost_overview($data['game'], $data['type'], $data) ?> • <?= htmlspecialchars((string)($booster['username'] ?? $data['booster_username'] ?? 'Booster')) ?>
              </p>
            </div>
          </div>

          <button type="button" class="lb-modal-x" data-bs-dismiss="modal" aria-label="Close">
            <i class="fa-solid fa-xmark"></i>
          </button>
        </div>

        <div class="modal-body">
          <div class="lb-review-layout">
            <div class="lb-review-left">
              <div class="row g-3">
                <div class="col-12 col-md-6">
                  <div class="lb-review-card p-3">
                    <h5>Communication</h5>
                    <input class="rating-input" type="text" name="score[communication]" value="" readonly>
                    <p class="text-muted mt-2 mb-0">How good was the communication (updates, answers, friendliness)?</p>
                  </div>
                </div>
                <div class="col-12 col-md-6">
                  <div class="lb-review-card p-3">
                    <h5>Skill</h5>
                    <input class="rating-input" type="text" name="score[skill]" value="" readonly>
                    <p class="text-muted mt-2 mb-0">How strong was the booster in-game (decisions, consistency)?</p>
                  </div>
                </div>
                <div class="col-12 col-md-6">
                  <div class="lb-review-card p-3">
                    <h5>Speed</h5>
                    <input class="rating-input" type="text" name="score[speed]" value="" readonly>
                    <p class="text-muted mt-2 mb-0">How quickly was the order completed?</p>
                  </div>
                </div>
                <div class="col-12 col-md-6">
                  <div class="lb-review-card p-3">
                    <h5>Overall</h5>
                    <input class="rating-input" type="text" name="score[overall]" value="" readonly>
                    <p class="text-muted mt-2 mb-0">Overall impression.</p>
                  </div>
                </div>
              </div>
            </div>

            <div class="lb-review-right">
              <div class="lb-review-card lb-review-card--highlights p-3">
                <h5 class="d-flex justify-content-between">
                  Highlights
                  <span id="highlight-count-view" class="text-muted">0/3</span>
                </h5>

                <div class="highlights d-flex align-items-center gap-2 flex-wrap mt-2">
                  <?php foreach (get_review_highlights() as $highlight): ?>
                    <input type="checkbox" class="btn-check" id="view-btn-check-<?= htmlspecialchars($highlight) ?>"
                      name="highlights[]" value="<?= htmlspecialchars($highlight) ?>" autocomplete="off"
                       disabled>
                    <label class="btn rounded-pill" for="view-btn-check-<?= htmlspecialchars($highlight) ?>">
                      <?= ucwords(str_replace('_', ' ', $highlight)) ?>
                    </label>
                  <?php endforeach; ?>
                </div>
              </div>

              <div class="lb-review-card lb-review-card--comments p-3">
                <h5>Additional Comments (Optional)</h5>
                <textarea class="form-control lb-textarea" name="comments" rows="3"
                  placeholder="Share more details about your experience..." readonly></textarea>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
<?php endif; ?>

<?= $this->start('scripts') ?>
<!-- tom-select removed: using custom dropdown -->
<script src="<?= ASSET_URL ?>/origin/dash/vendor/daterangepicker/moment.min.js"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/gh/kartik-v/bootstrap-star-rating@4.1.2/js/star-rating.min.js"
  type="text/javascript"></script>

<!-- with v4.1.0 Krajee SVG theme is used as default (and must be loaded as below) - include any of the other theme JS files as mentioned below (and change the theme property of the plugin) -->
<script src="https://cdn.jsdelivr.net/gh/kartik-v/bootstrap-star-rating@4.1.2/themes/krajee-fas/theme.js"></script>

<script>

  document.querySelectorAll('.tip-btn').forEach(button => {
    button.addEventListener('click', () => {
      const value = button.getAttribute('data-value');
      document.getElementById('tip-amount').value = value;
    });
  });

  const plus = document.getElementById('tip-plus');
  const minus = document.getElementById('tip-minus');
  if (plus && minus) {
    plus.addEventListener('click', () => {
      let input = document.getElementById('tip-amount');
      let current = parseFloat(input.value) || 0;
      input.value = (current + 1).toFixed(2);
    });

    minus.addEventListener('click', () => {
      let input = document.getElementById('tip-amount');
      let current = parseFloat(input.value) || 0;
      if (current > 1) input.value = (current - 1).toFixed(2);
    });
  }

  // Tip buttons (restore active state)
  document.querySelectorAll('.tip-btn').forEach(button => {
    button.addEventListener('click', () => {
      const value = button.getAttribute('data-value');
      document.getElementById('tip-amount').value = value;

      document.querySelectorAll('.tip-btn').forEach(b => b.classList.remove('is-active'));
      button.classList.add('is-active');
    });
  });

  // Optional: initial active chip based on current value
  document.addEventListener('DOMContentLoaded', () => {
    const v = (document.getElementById('tip-amount')?.value || '').trim();
    const match = document.querySelector(`.tip-btn[data-value="${v}"]`);
    if (match) match.classList.add('is-active');
  });
</script>

<script>
/* ============================================================
   LB Custom Select Dropdown — replaces TomSelect
   - Opens upward (panel above control)
   - Searchable / filterable
   - Syncs value back to hidden <select> for form submit
   - Works for both #vpn_country and #al_vpn_country_ui
   ============================================================ */
(function () {
  'use strict';

  function initLbCsd(wrapper) {
    var selectId = wrapper.dataset.for;
    var nativeSelect = document.getElementById(selectId);
    if (!nativeSelect) return;

    var control = wrapper.querySelector('.lb-csd__control');
    var panel   = wrapper.querySelector('.lb-csd__panel');
    var valueEl = wrapper.querySelector('.lb-csd__value');
    var search  = wrapper.querySelector('.lb-csd__search');
    var list    = wrapper.querySelector('.lb-csd__list');

    // Build option data from native select
    var options = Array.from(nativeSelect.options).map(function (o) {
      return { value: o.value, text: o.text, selected: o.selected || o.defaultSelected };
    });

    // Render list items
    function renderList(filter) {
      filter = (filter || '').toLowerCase().trim();
      var html = '';
      var hasResult = false;
      options.forEach(function (o) {
        if (filter && o.text.toLowerCase().indexOf(filter) === -1) return;
        hasResult = true;
        var isSel = (nativeSelect.value === o.value);
        html += '<div class="lb-csd__option' + (isSel ? ' is-selected' : '') + '" data-value="' + o.value.replace(/"/g, '&quot;') + '" role="option" aria-selected="' + isSel + '">' + o.text + '</div>';
      });
      if (!hasResult) {
        html = '<div class="lb-csd__empty">No results</div>';
      }
      list.innerHTML = html;
    }

    // Set selected value
    function selectValue(val) {
      nativeSelect.value = val;
      var opt = options.find(function(o){ return o.value === val; });
      valueEl.textContent = opt ? opt.text : 'None';
      // fire change on native select so existing code picks it up
      nativeSelect.dispatchEvent(new Event('change', { bubbles: true }));
      close();
    }

    // Sync initial display value
    var initVal = nativeSelect.value || '';
    var initOpt = options.find(function(o){ return o.value === initVal; });
    valueEl.textContent = initOpt ? initOpt.text : 'None';

    function open() {
      panel.classList.add('is-open');
      control.classList.add('is-open');
      panel.style.display = 'flex';
      search.value = '';
      renderList('');
      setTimeout(function(){ search.focus(); }, 30);
    }

    function close() {
      panel.classList.remove('is-open');
      control.classList.remove('is-open');
      panel.style.display = 'none';
    }

    function toggle() {
      panel.classList.contains('is-open') ? close() : open();
    }

    control.addEventListener('click', function(e) {
      e.stopPropagation();
      toggle();
    });

    control.addEventListener('keydown', function(e) {
      if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); toggle(); }
      if (e.key === 'Escape') close();
    });

    search.addEventListener('input', function() {
      renderList(this.value);
    });

    search.addEventListener('keydown', function(e) {
      if (e.key === 'Escape') { close(); control.focus(); }
    });

    list.addEventListener('click', function(e) {
      var opt = e.target.closest('.lb-csd__option');
      if (!opt) return;
      selectValue(opt.dataset.value);
    });

    // Close on outside click
    document.addEventListener('click', function(e) {
      if (!wrapper.contains(e.target)) close();
    });
  }

  function initAll() {
    document.querySelectorAll('.lb-csd').forEach(function(w) {
      if (!w.dataset.lbCsdInit) {
        w.dataset.lbCsdInit = '1';
        initLbCsd(w);
      }
    });
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initAll);
  } else {
    initAll();
  }

  // Re-init when modals open (Bootstrap 5)
  document.addEventListener('shown.bs.modal', function() {
    document.querySelectorAll('.lb-csd').forEach(function(w) {
      if (!w.dataset.lbCsdInit) {
        w.dataset.lbCsdInit = '1';
        initLbCsd(w);
      }
    });
  });
})();
</script>

<?php if (!empty($serviceGuideOrderId) && !empty($serviceGuideShouldShow)): ?>
  <script>
    document.addEventListener('DOMContentLoaded', function () {
      try {
        const orderId = <?= json_encode((string) $serviceGuideOrderId) ?>;
        const storageKey = `service_guide_ack_${orderId}`;
        const modalEl = document.getElementById('serviceGuideModal');
        if (!modalEl) return;


        // Viewport height helper (fixes iPhone Safari 100vh / address bar)
        const setViewportUnit = () => {
          const vv = window.visualViewport;
          const h = (vv && vv.height ? vv.height : window.innerHeight);
          document.documentElement.style.setProperty('--lb-vh', `${h * 0.01}px`);
        };

        // Keep Service Guide content fully visible (prefer no scrolling; fallback to scroll if viewport is tiny)
        const fitServiceGuideModal = () => {
          const modalEl = document.getElementById('serviceGuideModal');
          if (!modalEl) return;

          const content = modalEl.querySelector('.modal-content');
          const body = modalEl.querySelector('.modal-body');
          if (!content || !body) return;

          modalEl.classList.remove('sg-compact-1', 'sg-compact-2', 'sg-compact-3', 'sg-allow-scroll');
          body.style.overflowY = 'hidden';

          const levels = ['', 'sg-compact-1', 'sg-compact-2', 'sg-compact-3'];

          const fits = () => {
            const ch = content.getBoundingClientRect().height;
            const cs = content.scrollHeight;
            const bh = body.getBoundingClientRect().height;
            const bs = body.scrollHeight;
            return (cs <= ch + 2) && (bs <= bh + 2);
          };

          let ok = false;
          for (let i = 0; i < levels.length; i++) {
            modalEl.classList.remove('sg-compact-1', 'sg-compact-2', 'sg-compact-3');
            if (levels[i]) modalEl.classList.add(levels[i]);
            void content.offsetHeight; // force reflow
            if (fits()) { ok = true; break; }
          }

          if (!ok) {
            modalEl.classList.add('sg-allow-scroll');
            body.style.overflowY = 'auto';
          }
        };

        setViewportUnit();
        window.addEventListener('resize', setViewportUnit);
        window.addEventListener('orientationchange', setViewportUnit);
        if (window.visualViewport) {
          window.visualViewport.addEventListener('resize', setViewportUnit);
          window.visualViewport.addEventListener('scroll', setViewportUnit);
        }

        document.addEventListener('shown.bs.modal', (e) => {
          if (e?.target?.id !== 'serviceGuideModal') return;
          setViewportUnit();
          requestAnimationFrame(() => fitServiceGuideModal());
        });

        // If the modal is open and the viewport changes, re-fit
        window.addEventListener('resize', () => {
          const modalEl = document.getElementById('serviceGuideModal');
          if (modalEl && modalEl.classList.contains('show')) fitServiceGuideModal();
        });


        const pending = !localStorage.getItem(storageKey);
        // Expose state so other scripts can delay their modals (e.g. Riot/Account login modal)
        window.__serviceGuide = { orderId, storageKey, pending };

        if (pending) {
          const md = new bootstrap.Modal(modalEl, { backdrop: 'static', keyboard: false });
          md.show();
        }

        const btn = document.getElementById('serviceGuideAcknowledgeBtn');
        if (btn) {
          btn.addEventListener('click', function () {
            localStorage.setItem(storageKey, '1');
            if (window.__serviceGuide) window.__serviceGuide.pending = false;

            // Notify listeners (e.g. open Riot modal only after confirmation)
            document.dispatchEvent(new CustomEvent('serviceGuideAcknowledged', {
              detail: { orderId: orderId }
            }));

            const inst = bootstrap.Modal.getInstance(modalEl);
            if (inst) inst.hide();
          });
        }
      } catch (e) {
        // no-op
      }
    });
  </script>
<?php endif; ?>

<script>
  // Chat (premium grouped) — same logic style as booster/admin
  let msg_none = false;
  let chat_json = {};
  let order_id = <?= (int) $data['id'] ?>;
  let order_status = "<?= $data['status'] ?>";
  let order_booster_id = <?= (int)($data['booster_id'] ?? 0) ?>;
  let order_claimed_at = "<?= trim((string)($data['claimed_at'] ?? '')) ?>";
  let user_type = "client";
  let user_id = <?= (int) CLIENT_ID ?>;

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

    const isEdited = (msg_data.edited == 1 || msg_data.edited === true || msg_data.edited_at);
    const editedMark = isEdited ? ' <span class="lb-msg__edited">Edited</span>' : '';

    html += `<div class="lb-msg__bubble" data-msg-id="${message_id}">`;
    html += `<div class="lb-msg__content">${content}</div>`;
    if (isMe) {
      html += `<button type="button" class="lb-msg__edit" data-msg-id="${message_id}" title="Edit message"><i class="fa-duotone fa-pen-to-square"></i></button>`;
    }
    html += `</div>`;
    html += `<div class="lb-msg__stamp">${exactTime}${editedMark}${isMe ? renderTicks(msg_data) : ""}</div>`;
    html += `</div>`;
    return html;
  }

  // Edit own chat message (client)
  $(document).on('click', '.lb-msg__edit', function (e) {
    e.preventDefault();
    e.stopPropagation();

    const $bubble = $(this).closest('.lb-msg__bubble');
    const $content = $bubble.find('.lb-msg__content');
    const msgId = $bubble.data('msg-id');
    const id = (msgId !== undefined && msgId !== null && msgId !== '') ? msgId : $(this).data('msg-id');
    if (id === undefined || id === null || id === '') return;

    const prev = $content.html();
    function htmlToPlain(html){
      const tmp = document.createElement('div');
      tmp.innerHTML = String(html || '').replace(/<br\s*\/?\s*>/gi, "\n");
      return (tmp.textContent || tmp.innerText || '').trimEnd();
    }
    const startVal = (chat_json && chat_json[id] && chat_json[id].content)
      ? htmlToPlain(chat_json[id].content)
      : htmlToPlain(prev);

    const esc = (s) => String(s || '').replace(/[&<>\"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','\"':'&quot;','\'':'&#39;'}[c]));

    $bubble.addClass('is-editing');
    $content.html(`
      <textarea class="lb-msg__editor" rows="3">${esc(startVal)}</textarea>
      <div class="lb-msg__editor-actions">
        <button type="button" class="btn btn-sm btn-secondary lb-msg__cancel">Cancel</button>
        <button type="button" class="btn btn-sm btn-primary lb-msg__save">Save</button>
      </div>
    `);
    const $ta = $content.find('.lb-msg__editor');
    setTimeout(() => { try{$ta.focus(); $ta[0].setSelectionRange($ta.val().length, $ta.val().length);}catch(e){} }, 0);

    function cleanup(){ $bubble.removeClass('is-editing'); }

    // prevent multiple handlers if user opens editor repeatedly
    $content.off('click', '.lb-msg__cancel');
    $content.off('click', '.lb-msg__save');

    $content.on('click', '.lb-msg__cancel', function(){
      $content.html(prev);
      cleanup();
    });

    $content.on('click', '.lb-msg__save', function(){
      const newText = ($ta.val() || '').trim();
      if (!newText) return;

      fetch_api('edit_chat_message', Object.assign({}, base_data, { id: id, message: newText }))
        .done(function(res){
          try { res = JSON.parse(res); } catch (e) { res = { success: false, message: res }; }
          if (!res || res.success !== true) {
            $content.html(prev);
            cleanup();
            if (typeof create_toast === 'function') create_toast('danger', 'Error', (res && res.message) ? res.message : 'Could not edit message.');
            return;
          }
          chat_json = {};
          msg_none = false;
          load_messages();
        })
        .fail(function(){
          $content.html(prev);
          cleanup();
          if (typeof create_toast === 'function') create_toast('danger', 'Error', 'Could not edit message.');
        });
    });
  });

  function update_scroll() {
    const el = document.getElementById('chat_messages');
    if (!el) return;
    el.scrollTop = el.scrollHeight;
  }

  function update_message_notif(message_id) {
    fetch_api('update_chat_notify', Object.assign({}, base_data, { id: message_id })).done(function () { });
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
    fetch_api('load_chat', Object.assign({}, base_data)).done(function (response) {
      response = JSON.parse(response);

      const raw_list = response.messages || {};
      const chat_list = {};

      // Client view: completely hide deleted messages
      $.each(raw_list, function (key, val) {
        if (!val) return;
        const isDeleted = (val.type === 'deleted' || val.deleted == 1);
        if (isDeleted) return;
        chat_list[key] = val;
      });

      const msg_count = Object.keys(chat_list).length;

      // Re-render if anything changed (new msg OR deletion/edit)
      window.chat_json_sig = window.chat_json_sig || '';
      const new_sig = JSON.stringify(chat_list);

      if (msg_count > 0) {
        if (new_sig !== window.chat_json_sig) {
          window.chat_json_sig = new_sig;
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
          let read_html = '<div class="d-flex justify-content-end mb-2 pe-1" id="message-read-status">' + message_read + '</div>';
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
          $('#chat_messages').html('<div class="text-center"><h5 class="text-muted">No messages found.<br><br>Send one to get started!</h5></div>');
          msg_none = true;
        }
      }
    });
  }

  function checkOrderStatusSoft() {
    fetch_api('check_order_status', Object.assign({}, base_data)).done(function (resp) {
      try { resp = JSON.parse(resp); } catch(e){ return; }

      var isRanked5s = !!(resp.is_ranked_5s || resp.ranked_5s);
      var nextStatus = String(resp.order_status || resp.status || '').toUpperCase();
      var nextBoosterId = String(resp.booster_id ?? '');
      var nextClaimedAt = String(resp.claimed_at ?? '');
      var currentStatus = String(order_status || '').toUpperCase();

      // Ranked 5s gets multiple lane/booster updates while the same order remains active.
      // Do not reload the client page for those background assignment changes.
      if (isRanked5s && ['PAID','PROCESSING','IN_PROGRESS','PAUSED','COMPLETED'].includes(nextStatus)) {
        order_status = nextStatus || order_status;
        order_booster_id = nextBoosterId;
        order_claimed_at = nextClaimedAt;
        return;
      }

      var statusChanged = nextStatus && nextStatus !== currentStatus;

      // Keep the local snapshot in sync so background assignment updates do
      // not trigger repeated reload attempts on every poll cycle.
      order_booster_id = nextBoosterId;
      order_claimed_at = nextClaimedAt;

      if (statusChanged && !window.__lbOrderStatusReloadPending) {
        window.__lbOrderStatusReloadPending = true;
        order_status = nextStatus;
        if (document.visibilityState === 'visible') { message_sound(); }
        setTimeout(function () { location.reload(); }, 800);
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
    if (!data || parseInt(data.order_id || 0, 10) === parseInt(order_id, 10)) location.reload();
  };

  $(document).ready(function () {
    // Only a real click inside the chat marks messages as Read
    $(document).on('click', '#chat_messages', function(){ mark_chat_read(); });

    load_messages();
  });
  $(document).on('click', '[data-bs-target="#edit-note-modal"][data-note-id]', function (e) {
    e.preventDefault();
    var noteId = $(this).data('note-id');
    $('#edit-note-modal').closest('form').find('input[name="note_id"]').val(noteId);
  });

  $(document).on('click', '[data-bs-target="#delete-note-modal"][data-note-id]', function (e) {
    e.preventDefault();
    var noteId = $(this).data('note-id');
    $('#delete-note-modal').closest('form').find('input[name="note_id"]').val(noteId);
  });
</script>

<script>
  $(document).ready(function () {
    // Only a real click inside the chat marks messages as Read
    $(document).on('click', '#chat_messages', function(){ mark_chat_read(); });

    // new bootstrap.Modal($('#leave_review_md')).show();

    const step1 = $('#step-1');
    const step2 = $('#step-2');
    const nextBtn = $('#addon_next_btn');
    const backBtn = $('#addon_back_btn');

    // Parse money input like "5.50" OR "5,50" (also tolerates 1.234,56 / 1,234.56)
    function parseMoney(input) {
      const raw = (input || '').toString().trim();
      if (!raw) return 0;

      // remove spaces and currency symbols
      let s = raw.replace(/\s+/g, '').replace(/[^\d.,-]/g, '');

      // handle negatives just in case
      const neg = s.startsWith('-');
      if (neg) s = s.slice(1);

      const lastComma = s.lastIndexOf(',');
      const lastDot = s.lastIndexOf('.');

      if (lastComma !== -1 && lastDot !== -1) {
        // last separator is decimal separator
        if (lastComma > lastDot) {
          // 1.234,56 -> 1234.56
          s = s.replace(/\./g, '').replace(',', '.');
        } else {
          // 1,234.56 -> 1234.56
          s = s.replace(/,/g, '');
        }
      } else {
        // only comma or none -> treat comma as decimal separator
        s = s.replace(',', '.');
      }

      const n = parseFloat(s);
      if (isNaN(n)) return 0;
      return neg ? -n : n;
    }

    nextBtn.on('click', function () {
      let total = 0;
      let customAmount = parseMoney($('input[name="custom_amount"]').val()) || 0;
      const activeTab = $('.lbap-tab-panel.active').attr('id');

      if (activeTab === 'lbap-lp-correction') {
        const lpPrice = parseMoney($('#lp_correction_price').val()) || 0;
        if (lpPrice <= 0) {
          create_toast('danger', 'No Range Selected', 'Please select the actual LP range you are achieving.');
          return;
        }
        $('#addon_final_total').text(lpPrice.toFixed(2));
        step1.hide(); step2.show();
        return;
      }

      if (activeTab === 'lbap-server-change') {
        const serverPrice = parseMoney($('#server_change_price').val()) || 0;
        if (!$('#server_change_to').val()) {
          create_toast('danger', 'No Server Selected', 'Please select the new server for this order.');
          return;
        }
        if (serverPrice <= 0) {
          $('#addon_final_total').text('0.00');
          const btn = $(this);
          btn.prop('disabled', true).addClass('disabled');
          $.ajax({
            url: '<?= AJAX_URL ?>',
            type: 'POST',
            data: $('#addon-form').serialize(),
            success: function (res) {
              let response = res;
              try { response = (typeof res === 'string') ? JSON.parse(res) : res; } catch(e) {}

              // Free server change: the backend updates the order immediately.
              // Always close the modal and force a hard refresh on success, because the
              // header title is rendered server-side from PHP and cannot update reliably
              // without reloading the order page.
              if (response && response.sendToast) {
                create_toast(response.sendToast.type, response.sendToast.title, response.sendToast.message);
              }
              if (response && response.playSound) {
                try {
                  var audio = new Audio(asset_url + '/core/dash/audio/' + response.playSound + '.mp3');
                  audio.play();
                } catch(e) {}
              }

              var isError = response && response.sendToast && ['danger', 'warning', 'error'].indexOf(String(response.sendToast.type || '').toLowerCase()) !== -1;
              if (!isError) {
                var modalEl = document.getElementById('addon_payment_md');
                if (modalEl && window.bootstrap && bootstrap.Modal) {
                  var modal = bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl);
                  modal.hide();
                }
                setTimeout(function () {
                  window.location.href = window.location.href.split('#')[0];
                }, 350);
                return;
              }

              ajax_response_handler(response);
              btn.prop('disabled', false).removeClass('disabled');
            },
            error: function () {
              create_toast('danger', 'Error', 'Something went wrong. Please try again.');
              btn.prop('disabled', false).removeClass('disabled');
            }
          });
          return;
        }
        $('#addon_final_total').text(serverPrice.toFixed(2));
        step1.hide(); step2.show();
        return;
      }

      $('.js-toggle-switch:checked').each(function () {
        const price = parseFloat($(this).val()) || 0;
        total += price;
      });

      if (total <= 0 && customAmount <= 0) {
        create_toast(
          'danger',
          'No Amount Selected',
          'Please select at least one add-on or enter a custom amount to proceed.'
        );
        return;
      }

      if (activeTab === 'lbap-custom-amount') {
        $('#addon_final_total').text(customAmount.toFixed(2));
      } else {
        total = total / 100;
        $('#addon_final_total').text(total.toFixed(2));
      }

      step1.hide();
      step2.show();
    });

    backBtn.on('click', function () {
      step2.hide();
      step1.show();
    });

    // ==============================

    $('.js-toggle-switch').on('change', function () {
      let total = 0;

      $('.js-toggle-switch:checked').each(function () {
        const price = parseFloat($(this).val()) || 0;
        total += price;
      });

      total = total / 100;

      $('#addon_total_price').text(total.toFixed(2));
    });

    $('input[name="custom_amount"]').on('input', function () {
      let customAmount = parseMoney($(this).val()) || 0;
      $('#addon_custom_total').text(customAmount.toFixed(2));
    });

    // Normalize on blur (optional): always show two decimals (with dot)
    $('input[name="custom_amount"]').on('blur', function () {
      const n = parseMoney($(this).val());
      if (!n) return; // keep empty/0 as-is
      $(this).val(n.toFixed(2));
    });


    // =============================
    // LBAP custom tab switching
    document.querySelectorAll('.lbap-tab').forEach(function(btn) {
      btn.addEventListener('click', function() {
        document.querySelectorAll('.lbap-tab').forEach(function(b){ b.classList.remove('active'); });
        document.querySelectorAll('.lbap-tab-panel').forEach(function(p){ p.classList.remove('active'); });
        btn.classList.add('active');
        var panelId = 'lbap-' + btn.dataset.lbapTab;
        var panel = document.getElementById(panelId);
        if (panel) panel.classList.add('active');
      });
    });

    // ── LP Correction: two grids (LP Gain + Current LP) ─────────────
    (function () {
      const orderId  = <?= json_encode((int)($data['id'] ?? 0)) ?>;
      const formId   = <?= json_encode((int)($data['form_id'] ?? 0)) ?>;
      const ajaxUrl  = '<?= AJAX_URL ?>';
      // Fallback price (25% of order) used if backend returns 0 or for non-rank-boost forms
      const fallback = parseFloat((<?= json_encode((int)($data['price'] ?? 0)) ?> / 100 * 0.25).toFixed(2));

      var lpGainSelected = null;
      var clpSelected    = null;
      var fetchTimer     = null;

      function updateSummary(price) {
        var lpTotal  = document.getElementById('addon_lp_total');
        var priceInp = document.getElementById('lp_correction_price');
        if (lpTotal)  lpTotal.textContent = price.toFixed(2);
        if (priceInp) priceInp.value      = price > 0 ? price.toFixed(2) : '0';
      }

      function fetchPrice() {
        if (!lpGainSelected && !clpSelected) { updateSummary(0); return; }

        var lpTotal = document.getElementById('addon_lp_total');
        if (lpTotal) lpTotal.textContent = '…';

        clearTimeout(fetchTimer);
        fetchTimer = setTimeout(function() {
          // Only do real price lookup for rank boost (form_id 1)
          if (formId !== 1) {
            updateSummary(fallback);
            return;
          }
          $.ajax({
            url: ajaxUrl,
            type: 'POST',
            data: {
              action:         'client_lp_correction_price',
              order_id:       orderId,
              lp_gain_range:  lpGainSelected ? lpGainSelected.value : '',
              clp_range:      clpSelected    ? clpSelected.value    : ''
            },
            success: function(res) {
              try {
                var r = typeof res === 'string' ? JSON.parse(res) : res;
                var price = parseFloat(r.price) || 0;
                updateSummary(price > 0 ? price : fallback);
              } catch(e) { updateSummary(fallback); }
            },
            error: function() { updateSummary(fallback); }
          });
        }, 300);
      }

      function attachGrid(gridId, onSelect) {
        var grid = document.getElementById(gridId);
        if (!grid) return;
        grid.addEventListener('click', function(e) {
          var tile = e.target.closest('.lbap-lp-tile');
          if (!tile || tile.disabled || tile.classList.contains('is-disabled') || tile.classList.contains('is-booked')) return;
          grid.querySelectorAll('.lbap-lp-tile').forEach(function(t){ t.classList.remove('is-selected'); });
          tile.classList.add('is-selected');
          onSelect({ value: tile.dataset.lpValue, label: tile.dataset.lpLabel });
          fetchPrice();
        });
      }

      attachGrid('lbap-lpgain-grid', function(sel) {
        lpGainSelected = sel;
        var inp = document.getElementById('lp_correction_gain');
        if (inp) inp.value = sel.label;
      });

      attachGrid('lbap-clp-grid', function(sel) {
        clpSelected = sel;
        var inp = document.getElementById('lp_correction_clp');
        if (inp) inp.value = sel.label;
      });
    })();


    // ── Server Change: price lookup from pricing JSON ─────────────
    (function () {
      const orderId = <?= json_encode((int)($data['id'] ?? 0)) ?>;
      const ajaxUrl = '<?= AJAX_URL ?>';
      var fetchTimer = null;

      function updateServerSummary(price) {
        var total = document.getElementById('addon_server_total');
        var priceInput = document.getElementById('server_change_price');
        if (total) total.textContent = price.toFixed(2);
        if (priceInput) priceInput.value = price > 0 ? price.toFixed(2) : '0';
      }

      var grid = document.getElementById('lbap-server-grid');
      if (!grid) return;
      grid.addEventListener('click', function(e) {
        var tile = e.target.closest('.lbap-lp-tile');
        if (!tile || tile.disabled || tile.classList.contains('is-booked')) return;

        grid.querySelectorAll('.lbap-lp-tile').forEach(function(t){ t.classList.remove('is-selected'); });
        tile.classList.add('is-selected');

        var serverValue = tile.dataset.serverValue || '';
        var serverLabel = tile.dataset.serverLabel || serverValue.toUpperCase();
        $('#server_change_to').val(serverValue);
        $('#server_change_label').val(serverLabel);
        $('#addon_server_total').text('…');

        clearTimeout(fetchTimer);
        fetchTimer = setTimeout(function() {
          $.ajax({
            url: ajaxUrl,
            type: 'POST',
            data: {
              action: 'client_server_change_price',
              order_id: orderId,
              server: serverValue
            },
            success: function(res) {
              try {
                var r = typeof res === 'string' ? JSON.parse(res) : res;
                updateServerSummary(parseFloat(r.price) || 0);
              } catch(e) { updateServerSummary(0); }
            },
            error: function() { updateServerSummary(0); }
          });
        }, 250);
      });
    })();

    $('#extra-opts-tab, [data-lbap-tab="extra-opts"]').on('click', function () {
      $('#addon_custom_total').text('0.00');
      $('input[name="custom_amount"]').val('');
      $('input[name="custom_reason"]').val('');
    });

    $('[data-lbap-tab="custom-amount"]').on('click', function () {
      $('#addon_total_price').text('0.00');
      $('.js-toggle-switch').prop('checked', false);
    });


    $('[data-lbap-tab="server-change"]').on('click', function () {
      $('#addon_total_price').text('0.00');
      $('.js-toggle-switch').prop('checked', false);
      $('#addon_custom_total').text('0.00');
      $('input[name="custom_amount"]').val('');
    });

    $('[data-lbap-tab="lp-correction"]').on('click', function () {
      $('#addon_total_price').text('0.00');
      $('.js-toggle-switch').prop('checked', false);
      $('#addon_custom_total').text('0.00');
      $('input[name="custom_amount"]').val('');
    });

    // =============================

    function ajax_response_handler(res) {
      let response = (typeof res === 'string') ? JSON.parse(res) : res;

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

      if (response.refreshPageNow) {
        window.location.reload();
        return;
      }

      if (response.refreshPage && !(response.is_ranked_5s || response.ranked_5s)) {
        setTimeout(function () {
          window.location.reload();
        }, 300);
      }
    }

    $('#addon-form').on('submit', function (e) {
      e.preventDefault();
      const formData = $(this).serialize();

      $.ajax({
        url: '<?= AJAX_URL ?>',
        type: 'POST',
        data: formData,
        beforeSend: function () {
          $('#addon-form button[type="submit"] .spinner-border').removeClass('d-none');
          $('#addon-form button[type="submit"] span').hide();
          $('#addon-form button[type="submit"]').prop('disabled', true);
        },
        success: function (res) {
          const response = JSON.parse(res);

          if (response.nextstep) {
            $.ajax({
              url: '<?= AJAX_URL ?>',
              type: 'POST',
              data: {
                action: 'client_checkout',
                invoice_uuid: response.invoice_uuid,
                processor: response.processor
              },
              success: function (res) {
                ajax_response_handler(res);
              },
              error: function (xhr, status, error) {
                console.error(error);
                create_toast(
                  'danger',
                  'Error',
                  'Something went wrong during the payment process. Please try again.'
                );
                $('#addon-form button[type="submit"] .spinner-border').addClass('d-none');
                $('#addon-form button[type="submit"] span').show();
                $('#addon-form button[type="submit"]').prop('disabled', false);
              }
            });
          } else {
            ajax_response_handler(response);
            $('#addon-form button[type="submit"] .spinner-border').addClass('d-none');
            $('#addon-form button[type="submit"] span').show();
            $('#addon-form button[type="submit"]').prop('disabled', false);
          }
        },
        error: function (xhr, status, error) {
          console.error(error);
          create_toast(
            'danger',
            'Error',
            'Something went wrong. Please try again.'
          );


          $('#addon-form button[type="submit"] .spinner-border').addClass('d-none');
          $('#addon-form button[type="submit"] span').show();

          $('#addon-form button[type="submit"]').prop('disabled', false);
        }
      });
    });

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


    $(document).on('click', '.js-client-review-open', function () {
      const $btn = $(this);
      const boosterId = $btn.data('booster-id') || '';
      const boosterName = $btn.data('booster-name') || 'Booster';
      const hasReview = !!($btn.data('communication') || $btn.data('skill') || $btn.data('speed') || $btn.data('overall') || $btn.data('comments'));

      $('#client_review_booster_id').val(boosterId);
      $('#client_review_booster_name').text(boosterName);
      $('#client_review_submit_label').text(hasReview ? 'Update Review' : 'Submit Review');

      const fields = ['communication', 'skill', 'speed', 'overall'];
      fields.forEach(function (field) {
        const value = $btn.data(field) || '';
        const $input = $('#leave-review-form').find('input[name="score[' + field + ']"]');
        $input.val(value).trigger('change');
      });

      let highlights = [];
      try {
        const raw = $btn.attr('data-highlights') || '[]';
        highlights = JSON.parse(raw);
      } catch (e) {
        highlights = [];
      }

      $('#leave-review-form .highlights input[type="checkbox"]').prop('checked', false);
      highlights.forEach(function (item) {
        $('#leave-review-form .highlights input[type="checkbox"]').filter(function () {
          return String($(this).val()) === String(item);
        }).prop('checked', true);
      });
      $('#leave-review-form textarea[name="comments"]').val($btn.data('comments') || '');
      if (typeof updateCounter === 'function') updateCounter();
    });

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

<script>
  document.addEventListener("DOMContentLoaded", function () {
    const TRUSTPILOT_URL = "https://www.trustpilot.com/evaluate/lolboost.gg";

    function bindStarRating(wrapperId, outputId) {
      const wrap = document.getElementById(wrapperId);
      if (!wrap) return;

      const stars = Array.from(wrap.querySelectorAll(".lb-star"));
      const out = document.getElementById(outputId);
      let selected = parseInt(out?.value || "0", 10) || 0;

      function paint(n) {
        stars.forEach((btn) => {
          const i = parseInt(btn.dataset.index, 10);
          const on = i <= n;
          btn.classList.toggle("is-on", on);
          btn.setAttribute("aria-checked", on ? "true" : "false");
        });
      }

      stars.forEach((btn) => {
        const i = parseInt(btn.dataset.index, 10);
        btn.addEventListener("mouseenter", () => paint(i));
        btn.addEventListener("focus", () => paint(i));
        btn.addEventListener("click", () => {
          selected = i;
          if (out) out.value = String(i);
          paint(selected);
          window.open(TRUSTPILOT_URL, "_blank", "noopener");
        });
      });

      wrap.addEventListener("mouseleave", () => paint(selected));
      wrap.addEventListener("blur", () => paint(selected), true);
      paint(selected);
    }

    // Inline completed card (left column)
    bindStarRating("star-rating", "lb_review_rating");

    // Completed popup modal
    bindStarRating("tp-star-rating", "tp_review_rating");
  });
</script>

<script>
	  // Auto-open completed popup (keeps showing until user clicks "I dont want review now" or engages with review)
	  document.addEventListener('DOMContentLoaded', function () {
	    const isCompleted = <?= !empty($lb_isCompleted) ? 'true' : 'false' ?>;
	    if (!isCompleted) return;

	    const orderId = <?= (int) ($data['id'] ?? 0) ?>;
	    if (!orderId) return;

	    const key = `lb_completed_popup_${orderId}`;

	    const lbGetCookie = (name) => {
	      try {
	        const match = document.cookie.match(new RegExp('(?:^|; )' + name.replace(/[.$?*|{}()\[\]\\\/\+^]/g, '\\$&') + '=([^;]*)'));
	        return match ? decodeURIComponent(match[1]) : '';
	      } catch (e) { return ''; }
	    };
	
	    const lbSetCookie = (name, value, days) => {
	      try {
	        const maxAge = (days || 365) * 24 * 60 * 60;
	        document.cookie = `${name}=${encodeURIComponent(value)}; Max-Age=${maxAge}; Path=/; SameSite=Lax`;
	      } catch (e) { /* ignore */ }
	    };
	
	    const isDismissed = () => {
	      try {
	        if (localStorage.getItem(key) === '1') return true;
	      } catch (e) { /* ignore */ }
	      return lbGetCookie(key) === '1';
	    };
	
	    const markDismissed = () => {
	      try { localStorage.setItem(key, '1'); } catch (e) { /* ignore */ }
	      lbSetCookie(key, '1', 365);
	    };
	
	    if (isDismissed()) return;

	    const modalEl = document.getElementById('completed_feedback_md');
	    if (!modalEl || !window.bootstrap) return;

	    const modal = bootstrap.Modal.getOrCreateInstance(modalEl);

	    // Permanent dismiss button
	    const dismissBtn = document.getElementById('lb_completed_popup_dismiss');
	    if (dismissBtn) {
	      dismissBtn.addEventListener('click', function () {
	        markDismissed();
	        modal.hide();
	      });
	    }

	    // If user clicks any CTA, don't show again
	    modalEl.querySelectorAll('[data-bs-target="#leave_review_md"],[data-bs-target="#view_review_md"],[data-bs-target="#send_tip_md"],a[href*="trustpilot.com/evaluate"],#tp-star-rating .lb-star')
	      .forEach((el) => el.addEventListener('click', markDismissed));

	    // Don't clash with other modals opening
	    setTimeout(() => {
	      if (document.querySelector('.modal.show')) return;
	      // re-check in case it got dismissed fast (rare)
	      if (isDismissed()) return;
	      modal.show();
	    }, 650);
	  });
</script>

<script>
  document.addEventListener('DOMContentLoaded', function () {
    const modalEl = document.getElementById('account_logins_md');
    const btnSave = document.getElementById('al_save');

    // Prevent auto-opening Add Logins modal on COMPLETED orders
    const isCompleted = <?= !empty($lb_isCompleted) ? 'true' : 'false' ?>;

    const ign = document.getElementById('ign');
    const login = document.getElementById('rtlgn');
    const pass = document.getElementById('rtpwd');

    // finde das EXISTIERENDE account form (ajax-form)
    const accountForm = ign ? ign.closest('form') : null;

    const riotInput = document.getElementById('al_riot_id');
    const riotError = document.getElementById('al_riot_error');
    const riotSuggestions = document.getElementById('al_riot_suggestions');
    const riotPreview = document.getElementById('al_riot_preview');
    const riotIcon = document.getElementById('al_riot_icon');
    const riotIconFallback = document.getElementById('al_riot_icon_fallback');
    const riotName = document.getElementById('al_riot_name');
    const riotMeta = document.getElementById('al_riot_meta');
    const riotPreviewLabel = document.getElementById('al_riot_preview_label');
    const riotConfirmBtn = document.getElementById('al_riot_confirm');
    const riotPreviewEnabled = <?= $isLolOrder ? 'true' : 'false' ?>;
    let riotPreviewTimer = null;
    let riotPreviewController = null;
    let lastPreviewValue = '';
    let riotVerifiedValue = '';
    let riotVerifiedOk = false;
    let riotConfirmedValue = '';
    let riotPreviewInFlight = null;
    let riotSuggestionTimer = null;
    let riotSuggestionController = null;

    function normalizeRiotId(value) {
      // Trim spaces around #, but keep spaces inside tagLine (e.g. "o k")
      const parts = (value || '').split('#');
      if (parts.length < 2) return (value || '').trim();
      const gameName = parts[0].trim();
      const tagLine = parts.slice(1).join('#'); // kein trim() hier!
      return gameName + '#' + tagLine;
    }

    function isValidRiotId(value) {
      // Riot IDs: gameName (2-32 chars, no #) + # + tagLine (2-16 chars, may contain spaces/letters/digits)
      // Tags like "o k", "EUW", "NA1" are all valid.
      return /^[^#]{2,32}#.{2,16}$/.test(normalizeRiotId(value));
    }

    function setRiotError(message) {
      if (!riotError || !riotInput) return;
      riotError.textContent = message || '';
      riotInput.classList.toggle('is-invalid', !!message);
    }

    function setSaveLocked(isLocked) {
      if (!btnSave || !riotPreviewEnabled) return;
      btnSave.disabled = !!isLocked;
      btnSave.classList.toggle('is-disabled', !!isLocked);
    }

    function hideRiotSuggestions() {
      if (!riotSuggestions) return;
      riotSuggestions.hidden = true;
      riotSuggestions.innerHTML = '';
    }

    function loadRiotSuggestions(value) {
      if (!riotSuggestions || !riotPreviewEnabled) return;
      const query = (value || '').trim();
      clearTimeout(riotSuggestionTimer);

      if (query.length < 2 || query.includes('#')) {
        hideRiotSuggestions();
        return;
      }

      riotSuggestionTimer = setTimeout(function () {
        if (riotSuggestionController) riotSuggestionController.abort();
        riotSuggestionController = window.AbortController ? new AbortController() : null;

        const fd = new FormData();
        fd.append('action', 'client_riot_id_suggestions');
        fd.append('order_id', '<?= (int)($data['id'] ?? 0) ?>');
        fd.append('query', query);

        fetch('<?= AJAX_URL ?>', {
          method: 'POST',
          body: fd,
          credentials: 'same-origin',
          signal: riotSuggestionController ? riotSuggestionController.signal : undefined
        })
          .then(function (res) { return res.json(); })
          .then(function (json) {
            if ((riotInput?.value || '').trim() !== query) return;
            const suggestions = Array.isArray(json?.suggestions) ? json.suggestions : [];
            if (!suggestions.length) {
              hideRiotSuggestions();
              return;
            }

            riotSuggestions.innerHTML = '';
            suggestions.forEach(function (suggestion) {
              const riotId = String(suggestion?.riot_id || '').trim();
              if (!riotId) return;
              const button = document.createElement('button');
              button.type = 'button';
              button.className = 'lb-riot-suggestion';
              button.setAttribute('role', 'option');

              const name = document.createElement('span');
              name.className = 'lb-riot-suggestion__name';
              name.textContent = riotId;
              button.appendChild(name);

              if (suggestion?.server) {
                const server = document.createElement('span');
                server.className = 'lb-riot-suggestion__server';
                server.textContent = suggestion.server;
                button.appendChild(server);
              }

              button.addEventListener('mousedown', function (e) { e.preventDefault(); });
              button.addEventListener('click', function () {
                riotInput.value = riotId;
                hideRiotSuggestions();
                scheduleRiotPreview();
              });
              riotSuggestions.appendChild(button);
            });
            riotSuggestions.hidden = riotSuggestions.childElementCount === 0;
          })
          .catch(function (e) {
            if (!e || e.name !== 'AbortError') hideRiotSuggestions();
          });
      }, 220);
    }

    function resetRiotConfirmation() {
      riotConfirmedValue = '';
      if (riotConfirmBtn) {
        riotConfirmBtn.hidden = true;
        riotConfirmBtn.disabled = false;
        riotConfirmBtn.classList.remove('is-confirmed');
        riotConfirmBtn.innerHTML = '<i class="fa-solid fa-circle-check me-1"></i> Account verified';
      }
      setSaveLocked(true);
    }

    function markRiotConfirmed(riotId) {
      riotConfirmedValue = normalizeRiotId(riotId || riotInput?.value || '');
      if (riotConfirmBtn) {
        riotConfirmBtn.hidden = false;
        riotConfirmBtn.disabled = true;
        riotConfirmBtn.classList.add('is-confirmed');
        riotConfirmBtn.innerHTML = '<i class="fa-solid fa-circle-check me-1"></i> Account verified';
      }
      setRiotError('');
      setSaveLocked(false);
    }

    function setRiotPreview(state, data) {
      if (!riotPreview) return;
      if (state !== 'found') {
        riotVerifiedOk = false;
        riotVerifiedValue = '';
        resetRiotConfirmation();
      }
      riotPreview.hidden = false;
      riotPreview.classList.remove('is-loading', 'is-found', 'is-error', 'is-idle');
      riotPreview.classList.add('is-' + state);

      if (state === 'idle') {
        riotPreviewLabel.textContent = 'Riot account preview';
        riotName.textContent = 'Enter Riot ID to verify account';
        riotMeta.textContent = 'Example: Faker#1234';
        if (riotIcon) { riotIcon.removeAttribute('src'); riotIcon.style.display = 'none'; }
        if (riotIconFallback) riotIconFallback.style.display = 'grid';
        return;
      }

      if (state === 'loading') {
        riotPreviewLabel.textContent = 'Checking Riot account…';
        riotName.textContent = data?.riot_id || 'Looking up account';
        riotMeta.textContent = 'Please wait a moment.';
        if (riotIcon) { riotIcon.removeAttribute('src'); riotIcon.style.display = 'none'; }
        if (riotIconFallback) riotIconFallback.style.display = 'grid';
        return;
      }

      if (state === 'found') {
        riotVerifiedOk = true;
        riotVerifiedValue = normalizeRiotId(data?.riot_id || riotInput?.value || '');
        setRiotError('');
        riotPreviewLabel.textContent = 'Account found';
        riotName.textContent = data?.riot_id || 'Riot account found';
        riotMeta.textContent = data?.summoner_level ? ('Level ' + data.summoner_level + ' · ' + (data.server || '').toUpperCase()) : ((data?.server || '').toUpperCase() + ' account');
        if (riotIcon && data?.profile_icon_url) {
          riotIcon.src = data.profile_icon_url;
          riotIcon.style.display = 'block';
          if (riotIconFallback) riotIconFallback.style.display = 'none';
        }
        markRiotConfirmed(riotVerifiedValue);
        return;
      }

      if (data?.reason === 'wrong_server') {
        const foundServer = (data?.found_server_label || data?.found_server || '').toString().toUpperCase();
        const orderServer = (data?.order_server_label || data?.order_server || '').toString().toUpperCase();
        riotPreviewLabel.textContent = 'Wrong server';
        riotName.textContent = data?.account?.riot_id || data?.riot_id || 'Account found on another server';
        riotMeta.textContent = data?.message || ('This Riot ID was found on ' + foundServer + ', but this order is for ' + orderServer + '. Please contact support to change the order server. Saving is disabled.');
        setRiotError(riotMeta.textContent);
        if (riotIcon && data?.account?.profile_icon_url) {
          riotIcon.src = data.account.profile_icon_url;
          riotIcon.style.display = 'block';
          if (riotIconFallback) riotIconFallback.style.display = 'none';
        } else {
          if (riotIcon) { riotIcon.removeAttribute('src'); riotIcon.style.display = 'none'; }
          if (riotIconFallback) riotIconFallback.style.display = 'grid';
        }
        return;
      }

      const orderServer = (data?.order_server_label || data?.order_server || '').toString().toUpperCase();
      riotPreviewLabel.textContent = orderServer ? ('No ' + orderServer + ' account found') : 'Riot ID not found';
      riotName.textContent = data?.riot_id || 'Please check your Riot ID';
      riotMeta.textContent = data?.message || 'No Riot/LoL account was found for this Riot ID on the selected server. Please enter the exact Riot ID, including the #tag.';
      setRiotError(riotMeta.textContent);
      if (riotIcon) { riotIcon.removeAttribute('src'); riotIcon.style.display = 'none'; }
      if (riotIconFallback) riotIconFallback.style.display = 'grid';
    }

    function verifyRiotId(riotId, options = {}) {
      if (!riotPreviewEnabled || !riotInput || !riotPreview) return Promise.resolve(false);
      riotId = normalizeRiotId(riotId || riotInput.value || '');

      if (!isValidRiotId(riotId)) {
        setRiotError('Please enter your Riot ID in this format: Faker#1234');
        setRiotPreview('error', { riot_id: riotId, message: 'Please enter the full Riot ID with #tag, e.g. Faker#1234.' });
        return Promise.resolve(false);
      }

      if (riotVerifiedOk && riotVerifiedValue === riotId) {
        return Promise.resolve(true);
      }

      if (riotId === lastPreviewValue && riotPreviewInFlight) {
        return riotPreviewInFlight;
      }
      lastPreviewValue = riotId;

      if (riotPreviewController) riotPreviewController.abort();
      riotPreviewController = window.AbortController ? new AbortController() : null;

      setRiotError('');
      setRiotPreview('loading', { riot_id: riotId });

      const fd = new FormData();
      fd.append('action', 'client_preview_riot_account');
      fd.append('order_id', '<?= (int) ($data['id'] ?? 0) ?>');
      fd.append('riot_id', riotId);

      riotPreviewInFlight = fetch('<?= AJAX_URL ?>', {
        method: 'POST',
        body: fd,
        credentials: 'same-origin',
        signal: riotPreviewController ? riotPreviewController.signal : undefined
      })
        .then(function (res) { return res.json(); })
        .then(function (json) {
          if (json && json.ok) {
            setRiotPreview('found', json.account || { riot_id: riotId });
            return true;
          }
          setRiotPreview('error', Object.assign({ riot_id: riotId }, json || {}));
          return false;
        })
        .catch(function (e) {
          if (e && e.name === 'AbortError') return false;
          setRiotPreview('error', { riot_id: riotId, message: 'Riot ID not found' });
          return false;
        })
        .finally(function () {
          riotPreviewInFlight = null;
        });

      return riotPreviewInFlight;
    }

    function scheduleRiotPreview() {
      if (!riotPreviewEnabled || !riotInput || !riotPreview) return;
      const riotId = normalizeRiotId(riotInput.value);
      clearTimeout(riotPreviewTimer);

      if (!riotId) {
        lastPreviewValue = '';
        setRiotError('');
        setRiotPreview('idle');
        return;
      }

      if (!isValidRiotId(riotId)) {
        if (!riotId.includes('#')) {
          setRiotError('');
          resetRiotConfirmation();
          riotPreview.hidden = true;
          return;
        }
        lastPreviewValue = '';
        setRiotError('Please enter your Riot ID in this format: Faker#1234');
        setRiotPreview('error', { riot_id: riotId, message: 'Please enter the full Riot ID with #tag, e.g. Faker#1234.' });
        return;
      }

      setRiotError('');

      // Do not re-run the Riot preview just because the input blurs when the
      // client clicks the confirmation/save buttons. If the currently typed
      // Riot ID is already verified, keep the preview and confirmation state.
      if (riotVerifiedOk && riotVerifiedValue === riotId) {
        markRiotConfirmed(riotId);
        return;
      }

      riotVerifiedOk = false;
      riotVerifiedValue = '';
      resetRiotConfirmation();
      riotPreviewTimer = setTimeout(function () {
        verifyRiotId(riotId);
      }, 450);
    }

    function maybeOpenRiotModal() {
      if (isCompleted) return;
      // Auto-open wenn Riot ID leer
      const needsInGameName = !(ign?.value || '').trim();
      const needsAccountCredentials = <?= $showAccountCredentials ? 'true' : 'false' ?>
        && login && pass
        && (!(login.value || '').trim() || !(pass.value || '').trim());
      if ((needsInGameName || needsAccountCredentials) && modalEl) {
        if (window.bootstrap) bootstrap.Modal.getOrCreateInstance(modalEl).show();
      }
    }

    // Riot-Modal erst nach bestätigtem Service Guide öffnen (falls Service Guide aktiv/pending ist)
    if (window.__serviceGuide && window.__serviceGuide.pending) {
      document.addEventListener('serviceGuideAcknowledged', maybeOpenRiotModal, { once: true });
    } else {
      maybeOpenRiotModal();
    }

    // Beim Öffnen: bestehende Werte in Modal laden
    if (modalEl) {
      modalEl.addEventListener('shown.bs.modal', function () {
        const gameUsernameInput = document.getElementById('al_game_username');
        if (riotInput) {
          riotInput.value = (ign?.value || '').trim();
          scheduleRiotPreview();
        }
        if (gameUsernameInput) gameUsernameInput.value = (ign?.value || '').trim();
        const u = document.getElementById('al_username');
        const p = document.getElementById('al_password');
        if (u && login) u.value = (login.value || '').trim();
        if (p && pass) p.value = (pass.value || '').trim();
      });
    }

    if (riotInput) {
      riotInput.addEventListener('input', function () {
        // Nicht während der Eingabe normalisieren – sonst werden Leerzeichen im Tag weggefressen
        const normalized = normalizeRiotId(riotInput.value);
        loadRiotSuggestions(riotInput.value);
        scheduleRiotPreview();
      });
      riotInput.addEventListener('blur', function () {
        riotInput.value = normalizeRiotId(riotInput.value);
        scheduleRiotPreview();
      });
    }

    document.addEventListener('click', function (e) {
      if (riotSuggestions && !riotSuggestions.contains(e.target) && e.target !== riotInput) {
        hideRiotSuggestions();
      }
    });

    if (riotPreviewEnabled) setSaveLocked(true);

    function showAccountToast(type, title, message) {
      if (typeof window.create_toast === 'function') {
        window.create_toast(type, title, message);
      } else if (typeof window.sendToast === 'function') {
        window.sendToast({ type: type, title: title, message: message });
      } else if (message) {
        console.log(title + ': ' + message);
      }
    }

    // Save: per AJAX speichern, ohne Page-Reload. Dadurch bleibt /order/... stabil und es entsteht kein 404 nach dem Speichern.
    if (btnSave) {
      btnSave.addEventListener('mousedown', function (e) {
        e.preventDefault();
      });
      btnSave.addEventListener('click', async function (e) {
        if (e) {
          e.preventDefault();
          e.stopPropagation();
        }
        const gameUsernameInput = document.getElementById('al_game_username');
        const riot_id = riotPreviewEnabled
          ? normalizeRiotId((riotInput?.value || ign?.value || ''))
          : ((gameUsernameInput?.value || ign?.value || '').trim());

        if (riotPreviewEnabled) {
          btnSave.disabled = true;
          btnSave.classList.add('is-loading');
          const verified = await verifyRiotId(riot_id, { force: true });
          btnSave.disabled = false;
          btnSave.classList.remove('is-loading');

          if (!verified || !riotVerifiedOk || riotVerifiedValue !== riot_id) {
            const msg = riotMeta?.textContent || 'Riot ID not found';
            setRiotError(msg);
            document.getElementById('al_riot_id').focus();
            return;
          }

        }

        const uEl = document.getElementById('al_username');
        const pEl = document.getElementById('al_password');
        const username = uEl ? (uEl.value || '').trim() : '';
        const password = pEl ? (pEl.value || '').trim() : '';

        if (ign) ign.value = riot_id;
        if (login) login.value = username;
        if (pass) pass.value = password;

        
        // Account options (optional)
        const fpUI = document.getElementById('al_flash_position_ui');
        const omUI = document.getElementById('al_is_offline_mode_ui');
        const vpnUI = document.getElementById('al_vpn_country_ui');

        const fp = document.getElementById('al_flash_position');
        const om = document.getElementById('al_is_offline_mode');
        const vpn = document.getElementById('al_vpn_country');

        if (fp && fpUI) fp.value = (fpUI.value ?? '').trim();
        if (om && omUI) om.value = (omUI.value ?? '0').trim();
        if (vpn && vpnUI) vpn.value = (vpnUI.value ?? '').trim();
        if (!accountForm) {
          showAccountToast('danger', 'Error', 'Account form not found.');
          return;
        }

        btnSave.disabled = true;
        btnSave.classList.add('is-loading');

        try {
          const saveResponse = await fetch(accountForm.getAttribute('action') || '<?= AJAX_URL ?>', {
            method: 'POST',
            body: new FormData(accountForm),
            credentials: 'same-origin',
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
          });

          const saveText = await saveResponse.text();
          let saveJson = null;
          try { saveJson = JSON.parse(saveText); } catch (err) { saveJson = null; }

          if (!saveResponse.ok || !saveJson) {
            showAccountToast('danger', 'Error', 'Could not save Riot ID. Please try again.');
            return;
          }

          if (saveJson.sendToast) {
            showAccountToast(saveJson.sendToast.type, saveJson.sendToast.title, saveJson.sendToast.message);
          }

          if (saveJson.playSound) {
            try { new Audio(asset_url + '/core/dash/audio/' + saveJson.playSound + '.mp3').play(); } catch (err) {}
          }

          if (saveJson.validationErrors || (saveJson.sendToast && saveJson.sendToast.type === 'danger')) {
            const msg = (saveJson.validationErrors && (saveJson.validationErrors.ign || saveJson.validationErrors.riot_id)) || (saveJson.sendToast && saveJson.sendToast.message) || 'Riot ID not found';
            setRiotError(msg);
            setRiotPreview('error', Object.assign({ riot_id: riot_id, message: msg }, saveJson || {}));
            document.getElementById('al_riot_id').focus();
            return;
          }

          // Visible account card direkt aktualisieren, ohne reload.
          document.querySelectorAll('.lb-acc-summary').forEach(function (el) {
            el.classList.remove('is-missing');
            el.classList.add('is-saved');
          });
          document.querySelectorAll('.lb-acc-summary__riot-value').forEach(function (el) {
            el.textContent = riot_id;
          });
          document.querySelectorAll('.lb-acc-status').forEach(function (el) {
            el.textContent = 'Saved';
          });

          // Modal schließen
          if (modalEl && window.bootstrap) {
            bootstrap.Modal.getOrCreateInstance(modalEl).hide();
          }
        } catch (err) {
          showAccountToast('danger', 'Error', 'Could not save Riot ID. Please try again.');
        } finally {
          btnSave.disabled = false;
          btnSave.classList.remove('is-loading');
        }
      });
    }
  });
</script>

<script>
  document.addEventListener("DOMContentLoaded", function () {
    document.querySelectorAll(".lb-seg").forEach(seg => {
      const targetSel = seg.getAttribute("data-target");
      const input = document.querySelector(targetSel);
      const btns = Array.from(seg.querySelectorAll(".lb-seg-btn"));
      if (!input || !btns.length) return;

      const setActive = (val) => {
        btns.forEach(b => b.classList.toggle("is-active", (b.dataset.value ?? "") === (val ?? "")));
      };

      setActive(input.value ?? "");

      btns.forEach(btn => {
        btn.addEventListener("click", () => {
          input.value = btn.dataset.value ?? "";
          setActive(input.value);
        });
      });
    });
  });
</script>

<script>
  // Desktop emoji picker for order chat (hidden on mobile)
  document.addEventListener("DOMContentLoaded", function () {
    const btn = document.getElementById("lbEmojiBtn");
    const picker = document.getElementById("lbEmojiPicker");
    const input = document.getElementById("lbChatMessageInput");
    if (!btn || !picker || !input) return;

    function openPicker() { picker.classList.remove("d-none"); }
    function closePicker() { picker.classList.add("d-none"); }
    function togglePicker() { picker.classList.toggle("d-none"); }

    function insertEmoji(emoji) {
      const el = input;
      const start = typeof el.selectionStart === "number" ? el.selectionStart : el.value.length;
      const end = typeof el.selectionEnd === "number" ? el.selectionEnd : el.value.length;
      const text = el.value || "";
      el.value = text.slice(0, start) + emoji + text.slice(end);
      const pos = start + emoji.length;
      if (el.setSelectionRange) el.setSelectionRange(pos, pos);
      el.focus();
    }

    btn.addEventListener("click", function (e) {
      e.preventDefault();
      e.stopPropagation();
      togglePicker();
    });

    picker.addEventListener("click", function (e) {
      const b = e.target.closest("button[data-emoji]");
      if (!b) return;
      insertEmoji(b.getAttribute("data-emoji") || "");
      closePicker();
    });

    // Close when clicking outside
    document.addEventListener("click", function (e) {
      if (picker.classList.contains("d-none")) return;
      if (picker.contains(e.target) || btn.contains(e.target)) return;
      closePicker();
    });

    document.addEventListener("keydown", function (e) {
      if (e.key === "Escape" && !picker.classList.contains("d-none")) closePicker();
    });
  });
</script>

<?= $this->stop() ?>

<script>
(function () {
  // ── Refresh Order Progress (Riot rank sync) ──────────────────────────
  var asset_url = '<?= ASSET_URL ?>';
  var refreshProgressBtn    = document.getElementById('refreshProgressBtn');
  var progressStartEl       = document.getElementById('riotProgressStartRank');
  var progressCurrentEl     = document.getElementById('riotProgressCurrentRank');
  var progressCurrentImgEl  = document.getElementById('riotProgressCurrentRankImg');
  var progressWinsEl        = document.getElementById('riotProgressWins');
  var progressLossesEl      = document.getElementById('riotProgressLosses');
  var progressRecordEl      = document.getElementById('riotProgressRecord');
  var progressLastMatchEl   = document.getElementById('riotProgressLastMatch');
  var progressLastSyncEl    = document.getElementById('riotProgressLastSync');
  var progressWrBarEl       = document.getElementById('riotProgressWrBar');
  var progressSyncStateEl   = document.getElementById('riotProgressSyncState');
  var hasRiotId = <?= empty(trim((string) ($data['ign'] ?? ''))) ? 'false' : 'true' ?>;
  var isRefreshingProgress = false;
  var lastProgressRefresh = 0;
  var PROGRESS_REFRESH_COOLDOWN = 30000; // ms
  var opIsWinBoostForm = <?= $lb_op_is_win_boost_form ? 'true' : 'false' ?>;
  var opIsProGamesForm = <?= $lb_op_is_pro_games_form ? 'true' : 'false' ?>;
  var opIsDuoPassForm = <?= $lb_op_is_duo_pass_form ? 'true' : 'false' ?>;
  var opBaseTarget = <?= (int) $lb_op_base_target ?>;
  var opIsClassicRank = <?= $lb_op_is_classic_rank ? 'true' : 'false' ?>;
  var classicRankNames = ['Unranked','Salt','Wood','Silver','Gold','Platinum','Diamond','Legend'];

  var rankTierIds = {IRON:1,BRONZE:2,SILVER:3,GOLD:4,PLATINUM:5,EMERALD:6,DIAMOND:7,MASTER:8,GRANDMASTER:9,CHALLENGER:10};

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
    var cleanDiv  = (division || '').toString().trim();
    var cleanLp   = (lp != null ? lp : '').toString().trim();
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
    if (progressStartEl)       progressStartEl.textContent       = formatRankValue(progress.start_tier, progress.start_division, progress.start_lp);
    if (progressCurrentEl)     progressCurrentEl.textContent     = formatRankValue(progress.current_tier, progress.current_division, progress.current_lp);
    if (progressCurrentImgEl && progress.current_tier) progressCurrentImgEl.src = tierToImgUrl(progress.current_tier);
    if (progressWinsEl)        progressWinsEl.textContent        = String(parseInt(progress.wins   || 0, 10) || 0);
    if (progressLossesEl)      progressLossesEl.textContent      = String(parseInt(progress.losses || 0, 10) || 0);
    if (progressRecordEl)      progressRecordEl.textContent      = formatRecordValue(progress.wins, progress.losses);
    applyRecordTone(progress.wins, progress.losses);
    if (progressWrBarEl) {
      var w = parseInt(progress.wins   || 0, 10) || 0;
      var l = parseInt(progress.losses || 0, 10) || 0;
      var g = w + l;
      var pct = g > 0 ? (w / g) * 100 : 0;
      progressWrBarEl.style.width = pct.toFixed(1) + '%';
      progressWrBarEl.classList.remove('lb-op-wr-bar-fill--good');
      if (g > 0 && pct >= 60) progressWrBarEl.classList.add('lb-op-wr-bar-fill--good');
    }
    if (progressLastMatchEl) progressLastMatchEl.textContent = (progress.last_match_id || '').toString().trim();
    if (progressLastSyncEl)  progressLastSyncEl.textContent  = formatSyncDate(progress.last_sync_at);
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
    var opts   = options || {};
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
    var btn  = refreshProgressBtn;
    var icon = btn ? btn.querySelector('i') : null;
    if (btn)  btn.disabled = true;
    if (icon) icon.classList.add('fa-spin');
    setProgressSyncState('Refreshing progress from Riot API…', 'muted', true);

    var fd = new FormData();
    fd.append('action',   'refresh_order_progress');
    fd.append('order_id', '<?= (int) ($data['id'] ?? 0) ?>');
    if (silent) fd.append('silent', '1');

    fetch('<?= AJAX_URL ?>', {method: 'POST', body: fd, credentials: 'same-origin'})
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
        if (btn)  btn.disabled = false;
        if (icon) icon.classList.remove('fa-spin');
      });
  }

  if (refreshProgressBtn) {
    refreshProgressBtn.addEventListener('click', function () { syncOrderProgress({silent: false}); });
  }

  if (hasRiotId && typeof order_status !== 'undefined' && order_status === 'IN_PROGRESS') {
    setTimeout(function () { syncOrderProgress({silent: true}); }, 300);
  }
})();
</script>

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
  var ajaxUrl = '<?= AJAX_URL ?>';
  var perPage = 20;
  var currentPage = 1;
  var loading = false;
  var QUEUE_NAMES = { 420: 'Ranked Solo', 440: 'Ranked Flex', 400: 'Normal Draft', 430: 'Normal Blind', 450: 'ARAM', 900: 'URF', 1020: 'One For All', 76: 'URF' };

  function roleFile(pos) { return ROLE_MAP[pos] || null; }
  function queueName(id) { return QUEUE_NAMES[parseInt(id, 10)] || 'Match'; }
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
  function renderRows(rows) {
    if (!rows || rows.length === 0) {
      body.innerHTML = '<div class="lb-mh-placeholder">No matches tracked yet.</div>';
      return;
    }
    var html = '';
    for (var i = 0; i < rows.length; i++) {
      var m = rows[i];
      var durationSeconds = parseInt(m.duration || 0, 10) || 0;
      var resultText = (m.result || '').toString().trim().toLowerCase();
      var isRemake = parseInt(m.is_remake || 0, 10) === 1 || resultText === 'remake' || parseInt(m.game_ended_in_early_surrender || 0, 10) === 1 || (durationSeconds > 0 && durationSeconds < 300);
      var won = !isRemake && (parseInt(m.won, 10) === 1 || parseInt(m.is_win, 10) === 1);
      var rowCls = isRemake ? 'lb-mh-row--remake' : (won ? 'lb-mh-row--win' : 'lb-mh-row--loss');
      var badge = isRemake
        ? '<span class="lb-mh-badge lb-mh-badge--remake"><i class="fa-solid fa-rotate-left fa-xs me-1"></i>Remake</span>'
        : (won
          ? '<span class="lb-mh-badge lb-mh-badge--win"><i class="fa-solid fa-trophy fa-xs me-1"></i>Win</span>'
          : '<span class="lb-mh-badge lb-mh-badge--loss"><i class="fa-solid fa-skull fa-xs me-1"></i>Loss</span>');

      var champ = (m.champion || '').toString().trim();
      var champSafe = escHtml(champ);
      var champImg = champ
        ? '<img class="lb-mh-champ-img" src="' + champUrl + '/' + encodeURIComponent(champ) + '.png" alt="' + champSafe + '" loading="lazy" onerror="this.style.visibility=\'hidden\'">'
        : '<span class="lb-mh-champ-img"></span>';
      var champCol = '<div class="lb-mh-champ-col">' + champImg + '<div class="lb-mh-champ-info"><div class="lb-mh-champ-name">' + (champSafe || '—') + '</div><div class="lb-mh-queue">' + queueName(m.queue_id) + '</div></div></div>';

      var boosterName = (m.booster_name || '').toString().trim() || (m.booster_id ? ('#' + m.booster_id) : 'Unassigned');
      var boosterCol = '<div class="lb-mh-booster-col">' + boosterIconHtml(m) + '<span class="lb-mh-booster-info"><span class="lb-mh-booster-name">' + escHtml(boosterName) + '</span><span class="lb-mh-booster-sub">Booster</span></span></div>';

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
      var durCol = '<div class="lb-mh-dur-col"><span class="lb-mh-dur">' + fmtDuration(durationSeconds) + '</span><span class="lb-mh-sub">Duration</span></div>';
      var rankCol = rankColHtml(m);
      var dp = fmtDate(m.played_at);
      var dateCol = '<div class="lb-mh-date-col"><span class="lb-mh-date">' + dp[0] + '</span><span class="lb-mh-time">' + dp[1] + '</span></div>';
      html += '<div class="lb-mh-row ' + rowCls + '"><div class="lb-mh-result">' + badge + '</div>' + champCol + boosterCol + roleCol + kdaCol + durCol + rankCol + dateCol + '</div>';
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
        if (meta.total > perPage && pager) {
          pager.style.display = 'flex';
          pagerInfo.textContent = 'Page ' + meta.page + ' of ' + meta.pages;
          prevBtn.disabled = (meta.page <= 1);
          nextBtn.disabled = (meta.page >= meta.pages);
        } else if (pager) {
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
  if (modalEl) modalEl.addEventListener('show.bs.modal', function () { loadPage(1); });
  document.addEventListener('lbProgressSynced', function () {
    if (modalEl && modalEl.classList.contains('show')) loadPage(1);
  });
})();
</script>

<?= $this->start('styles') ?>
<!-- tom-select removed: using custom dropdown -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.1/dist/css/bootstrap.min.css">
<link href="https://cdn.jsdelivr.net/gh/kartik-v/bootstrap-star-rating@4.1.2/css/star-rating.min.css" media="all"
  rel="stylesheet" type="text/css" />

<!-- with v4.1.0 Krajee SVG theme is used as default (and must be loaded as below) - include any of the other theme CSS files as mentioned below (and change the theme property of the plugin) -->
<link href="https://cdn.jsdelivr.net/gh/kartik-v/bootstrap-star-rating@4.1.2/themes/krajee-svg/theme.css" media="all"
  rel="stylesheet" type="text/css" />

<style>
  /* =========================
   WRAP + CARDS
========================= */
  .order-page-wrap {
    padding: 1rem;
  }

  @media (min-width:992px) {
    .order-page-wrap {
      padding: 1.75rem;
    }
  }

  .card {
    border-radius: 1rem;
    overflow: visible;
  }

  /* wichtig für dropdowns */
  .card-header {
    padding: .8rem 1rem;
  }

  .card-body,
  .card-footer {
    padding: .85rem 1rem;
  }

  /* Nur Karten, die wirklich clippen sollen */
  .booster-intro-card,
  .waiting-banner,
  .order-chat-card {
    overflow: hidden;
  }

  .card-header-title {
    font-size: .9rem;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: .08em;
    margin: 0;
  }

  /* =========================
   PAGE HEADER (premium)
========================= */
  .page-header {
    padding: 1.4rem 1.6rem;
    margin-bottom: 1.4rem;
    border-radius: 1rem;
    background: transparent;
    border: none;
    box-shadow: none;
    position: relative;
  }

  .page-header-top {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 1rem;
  }

  .page-header-left {
    display: flex;
    align-items: flex-start;
    gap: 1rem;
    min-width: 0;
  }

  .page-header-title-wrap {
    min-width: 0;
  }

  .page-header-title {
    font-size: 1.25rem;
    font-weight: 900;
    margin: 0;
    line-height: 1.15;
  }

  .page-header-actions .btn {
    border-radius: 999px;
  }

  @media (min-width:992px) {
    .page-header-actions {
      position: absolute;
      top: 1.35rem;
      right: 1.6rem;
      z-index: 5;
    }

    .page-header-top {
      padding-right: 12rem;
    }
  }

  @media (max-width:767.98px) {
    .page-header {
      padding: 1.15rem 1rem;
      margin-bottom: 1.1rem;
    }

    .page-header-title {
      font-size: .8rem;
    }
  }

  /* Meta chips */
  .page-header-meta {
    display: flex;
    flex-wrap: wrap;
    gap: .75rem 1rem;
    margin-top: .85rem;
  }

  .page-header-meta .meta-item {
    display: flex;
    flex-direction: column;
    gap: .15rem;
    padding: .55rem .7rem;
    border-radius: .9rem;
    border: 1px solid rgba(255, 255, 255, .06);
    background: rgba(255, 255, 255, .04);
    min-width: 170px;
  }

  [data-theme="light"] .page-header-meta .meta-item {
    border-color: rgba(0, 0, 0, .06);
    background: rgba(0, 0, 0, .03);
  }

  .page-header-meta .meta-label {
    font-size: .70rem;
    text-transform: uppercase;
    letter-spacing: .06em;
    opacity: .75;
  }

  .page-header-meta .meta-value {
    font-size: .95rem;
    font-weight: 700;
    line-height: 1.2;
  }

  @media (max-width:767.98px) {
    .page-header-meta {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: .65rem;
    }

    .page-header-meta .meta-item {
      min-width: 0;
    }
  }

  @media (max-width:420px) {
    .page-header-meta {
      grid-template-columns: 1fr;
    }
  }

  /* Status pill (alt – falls du es noch nutzt) */
  .order-status-pill {
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
  }

  .order-status-pill::before {
    content: "";
    width: 7px;
    height: 7px;
    border-radius: 999px;
    background: var(--c);
    opacity: .95;
  }

  /* Premium warning hint */
  .lb-hint {
    background: rgba(255, 107, 107, .10);
    border: 1px solid rgba(255, 107, 107, .20);
    border-radius: 1rem;
    padding: .85rem 1rem;
    display: flex;
    gap: .75rem;
    align-items: flex-start;
  }

  .lb-hint i {
    margin-top: .15rem;
  }

  /* =========================
   WAITING / CLAIMED BANNER
========================= */
  .waiting-banner {
    border-radius: 1rem;
    position: relative;
    border: 1px solid rgba(255, 255, 255, .08);
    background: rgba(255, 255, 255, .03);
  }

  [data-theme="light"] .waiting-banner {
    border-color: rgba(0, 0, 0, .08);
    background: rgba(0, 0, 0, .02);
  }

  .waiting-banner .card-body {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: .85rem 1.15rem;
  }

  .waiting-avatar-wrapper {
    width: 44px;
    height: 44px;
    position: relative;
  }

  .waiting-avatar {
    width: 44px;
    height: 44px;
    border-radius: 999px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: rgba(78, 161, 255, .10);
    border: 1px solid rgba(78, 161, 255, .35);
    box-shadow: 0 0 0 6px rgba(78, 161, 255, .06);
  }

  .waiting-pulse-ring {
    position: absolute;
    inset: -6px;
    border-radius: 999px;
    border: 2px solid rgba(78, 161, 255, .25);
    animation: waitingPulse 1.6s ease-in-out infinite;
  }

  @keyframes waitingPulse {
    0% {
      transform: scale(.85);
      opacity: .35;
    }

    60% {
      transform: scale(1.10);
      opacity: .10;
    }

    100% {
      transform: scale(1.18);
      opacity: 0;
    }
  }

  .waiting-title {
    font-weight: 800;
    font-size: .95rem;
  }

  .waiting-sub {
    font-size: .78rem;
    opacity: .80;
  }

  /* =========================
   BOOSTER INTRO (v3 clean)
   (REPLACE your current Booster Intro CSS with this block)
========================= */
  .booster-intro-card {
    border-radius: 1.25rem;
    position: relative;
    border: 1px solid rgba(255, 255, 255, .10);
    background: rgba(255, 255, 255, .03);
    overflow: hidden;
    /* keep cover clipped */
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

  /* BODY */
  .booster-intro-body {
    position: relative;
    padding: 1rem 1.15rem;
    display: flex;
    flex-direction: column;
    gap: .9rem;
  }

  /* TOP ROW */
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

  .booster-intro-main {
    min-width: 0;
  }

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

/* Rank pill under name */
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

  
/* Align timezone pill with rank pill (FontAwesome baseline fix) */
.booster-rank-pill{
  vertical-align: middle;
  line-height: 1;
}

.booster-rank-pill i,
.booster-rank-pill svg{
  width: 22px;
  height: 22px;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  line-height: 1;
}

/* RIGHT */
  .booster-intro-right {
    flex: 0 0 auto;
    display: flex;
    align-items: center;
    justify-content: flex-end;
  }

  /* View Profile Button (keep your lolboost style) */
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

  .visit-profile-btn i {
    font-size: .95rem;
    opacity: .95;
  }

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

  /* BOTTOM 3 CARDS */
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
    align-items: center;
    gap: .35rem;
    flex-wrap: wrap;
  }

  /* Champs */
  .booster-intro-champs .champ {
    width: 28px;
    height: 28px;
    border-radius: 10px;
    object-fit: cover;
    border: 1px solid rgba(255, 255, 255, .12);
  }

  .booster-intro-champs .more {
    padding: .14rem .5rem;
    border-radius: 999px;
    font-weight: 900;
    font-size: .78rem;
    border: 1px solid rgba(255, 255, 255, .12);
    background: rgba(255, 255, 255, .05);
  }


  .booster-intro-champs .more.js-lb-champs-tooltip {
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
    font-weight: 800;
    color: rgba(255,255,255,.9);
    text-align: center;
  }

  /* Roles */
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

  /* Languages */
  .booster-intro-langs .flag {
    width: 28px;
    height: 28px;
    border-radius: 999px;
    object-fit: cover;
    border: 1px solid rgba(255, 255, 255, .12);
  }

  .na {
    opacity: .7;
    font-weight: 800;
  }

  /* Responsive */
  @media (max-width: 991.98px) {
    .booster-intro-cards {
      grid-template-columns: repeat(2, minmax(0, 1fr));
    }
  }

  @media (max-width: 575.98px) {
    .booster-intro-top {
      align-items: flex-start;
    }

    .booster-intro-avatar {
      width: 60px;
      height: 60px;
    }

    .booster-intro-name {
      font-size: 1.02rem;
    }

    .booster-intro-cards {
      grid-template-columns: 1fr;
    }
  }

  /* =========================
   MOBILE: View Profile oben rechts,
   Name + Rank eine Zeile drunter links
========================= */
  @media (max-width:575.98px) {

    /* Top area als Grid: Row 1 = Avatar + Button, Row 2 = Name/Rank */
    .booster-intro-top {
      display: grid !important;
      grid-template-columns: auto 1fr;
      grid-template-areas:
        "av btn"
        "main main";
      align-items: start !important;
      gap: .6rem .75rem;
    }

    /* Damit Avatar + Main direkt im Grid landen */
    .booster-intro-left {
      display: contents !important;
    }

    .booster-intro-avatar {
      grid-area: av;
      align-self: start;
    }

    .booster-intro-right {
      grid-area: btn;
      justify-self: end;
      align-self: start;
    }

    /* Name + Rank komplett in die 2. Zeile */
    .booster-intro-main {
      grid-area: main;
      margin-top: .15rem;
      min-width: 0;
    }

    /* Optional: etwas kompakter */
    .booster-intro-name {
      font-size: 1.02rem;
    }

    .booster-rank-pill {
      margin-top: .4rem;
    }
  }

  @media (max-width:575.98px) {

    /* Rank größer auf Mobile */
    .booster-rank-pill {
      font-size: 1rem !important;
      /* vorher kleiner -> jetzt größer */
      padding: .40rem .72rem !important;
      font-weight: 950 !important;
      line-height: 1.1 !important;
    }

    /* optional: Icon im Pill (falls vorhanden) auch leicht größer */
    .booster-rank-pill i,
    .booster-rank-pill svg {
      transform: scale(1.08);
      transform-origin: center;
    }
  }

  /* =========================
   CHAT (admin-like)
========================= */
  .order-chat-card .chat-bg {
    background: #1e2022;
    border-radius: 0;
  }

  [data-theme="light"] .order-chat-card .chat-bg {
    background: #F9FAFC;
  }

/* Chat locked (24h after order completion) */
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
  .order-chat-card .card-footer .form-control:disabled,
  .order-chat-card .card-footer button.btn:disabled{
    cursor: not-allowed;
  }

  #chat_messages {
    height: clamp(340px, 55vh, 520px);
    min-height: 340px;
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
    width: 36px;
    height: 36px;
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
    flex: 1;
    min-width: 0;
    line-height: 1.1;
  }

  .lb-msg__toprow {
    display: flex;
    align-items: center;
    gap: 10px;
    width: 100%;
  }

  .lb-msg__name {
    font-weight: 900;
    font-size: .92rem;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
  }

  .lb-msg__time {
    margin-left: auto;
    font-size: .74rem;
    color: rgba(255, 255, 255, .55);
    white-space: nowrap;
  }

  [data-theme="light"] .lb-msg__time {
    color: rgba(0, 0, 0, .55);
  }

  @media (max-width:575.98px) {
    .lb-msg__toprow {
      flex-wrap: wrap;
      gap: 6px 10px;
    }

    .lb-msg__time {
      margin-left: 0;
    }
  }

  .lb-badge {
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


  /* Timestamp under every bubble (exact time) */
  .lb-msg__stamp {
    margin-top: 6px;
    font-size: .72rem;
    opacity: .60;
    line-height: 1.1;
    padding: 0 2px;
  }

  .lb-msg--end .lb-msg__stamp {
    text-align: right;
  }

  .lb-msg--start .lb-msg__stamp {
    text-align: left;
  }

  /* Edit marker */
  .lb-msg__edited{
    display: inline-block;
    margin-left: 6px;
    padding: 2px 8px;
    border-radius: 999px;
    font-size: .68rem;
    line-height: 1.2;
    opacity: .85;
    border: 1px solid rgba(255,255,255,.12);
    background: rgba(255,255,255,.06);
  }

  /* Edit button (only for own messages) */
  .lb-msg__edit{
    position:absolute;
    top:8px;
    right:10px;
    width:28px;
    height:28px;
    border-radius: 10px;
    border:1px solid rgba(255,255,255,.10);
    background: rgba(255,255,255,.06);
    color: rgba(255,255,255,.85);
    display:flex;
    align-items:center;
    justify-content:center;
    opacity:0;
    pointer-events:none;
    transition: .12s ease;
  }
  .lb-msg__bubble:hover .lb-msg__edit{
    opacity:1;
    pointer-events:auto;
  }
  .lb-msg__edit:hover{ background: rgba(255,255,255,.12); transform: translateY(-1px); }

  /* Inline editor */
  .lb-msg__editor{
    width:100%;
    min-height: 76px;
    border-radius: 12px;
    border: 1px solid rgba(255,255,255,.12);
    background: rgba(0,0,0,.12);
    color: rgba(255,255,255,.92);
    padding: 10px 12px;
    outline: none;
    resize: vertical;
  }
  .lb-msg__editbar{ display:flex; gap:8px; margin-top: 10px; justify-content:flex-end; }
  .lb-msg__editbar .btn{ border-radius: 12px; }

  /* System message full width */
  .lb-syswrap {
    width: 100%;
    align-self: stretch;
  }

  .lb-sys {
    width: 100%;
    max-width: 100%;
    padding: 10px 14px;
    border-radius: 14px;
    border: 1px dashed rgba(177, 140, 255, .35);
    background: rgba(177, 140, 255, .10);
    font-weight: 800;
    font-size: .86rem;
  }

  .lb-sys-time {
    margin-top: 6px;
    font-size: .75rem;
    opacity: .65;
    color: rgba(255, 255, 255, .55);
  }

  [data-theme="light"] .lb-sys-time {
    color: rgba(0, 0, 0, .55);
  }

  /* =========================
   MODALS (lb-modal)
========================= */
  .modal-backdrop.show {
    opacity: .78 !important;
  }

  .lb-modal .modal-dialog {
    max-width: 620px;
  }

  .lb-modal .modal-lg {
    max-width: 800px;
  }

  /* =========================
   REVIEW MODAL (full-screen, no-scroll desktop)
========================= */
  .lb-modal--review-full .modal-dialog {
    width: calc(100vw - 2rem);
    max-width: calc(100vw - 2rem);
    height: auto; /* mobile/tablet: avoid vh jank */
    margin: 1rem auto;
  }

  .lb-modal--review-full .modal-content {
    height: auto; /* mobile/tablet */
    overflow: hidden;
  }

  .lb-modal--review-full .modal-header,
  .lb-modal--review-full .modal-footer {
    flex: 0 0 auto;
  }

  .lb-modal--review-full .modal-body {
    flex: 1 1 auto;
    min-height: 0;
    overflow: auto; /* default: allow scroll on smaller screens */
    padding: 1rem;
  }

  /* Layout inside review modal */
  .lb-review-layout {
    height: auto; /* mobile/tablet: avoid forced 100% height */
    display: grid;
    gap: 1rem;
  }

  @media (min-width: 992px) {
    .lb-review-layout {
      grid-template-columns: 1.15fr .85fr;
    }
  }

  .lb-review-left,
  .lb-review-right {
    min-height: 0;
  }

  .lb-review-right {
    display: flex;
    flex-direction: column;
    gap: 1rem;
  }

  .lb-review-card {
    border-radius: 16px;
    border: 1px solid rgba(255, 255, 255, .06);
    background: rgba(255, 255, 255, .03);
  }

  .lb-review-card h5 {
    font-size: .95rem;
    font-weight: 900;
    margin: 0 0 .5rem 0;
  }

  .lb-review-card p {
    font-size: .85rem;
  }

  .lb-review-card--comments {
    flex: 1 1 auto;
    min-height: 0;
    display: flex;
    flex-direction: column;
  }

  .lb-review-card--comments textarea {
    flex: 1 1 auto;
    min-height: 120px;
    resize: none;
  }

  /* Desktop: compact so everything fits without scrolling */
  @media (min-width: 992px) {

    /* Desktop: lock modal to viewport height */
    .lb-modal--review-full .modal-dialog {
      height: calc(var(--lb-vh, 1vh) * 100 - 2rem);
    }
    .lb-modal--review-full .modal-content { height: 100%; }
    .lb-review-layout { height: 100%; }

    .lb-modal--review-full .modal-body { overflow: hidden; } /* desktop: fixed height, no scroll */
    .lb-modal--review-full .modal-body {
      padding: .75rem;
    }

    .lb-review-layout { gap: .75rem; }
    .lb-review-right  { gap: .75rem; }

    /* Override p-3 padding from markup */
    .lb-modal--review-full .lb-review-card {
      padding: .75rem !important;
    }

    /* Save vertical space: hide helper copy and tighten typography */
    .lb-modal--review-full .lb-review-card p.text-muted {
      display: none;
    }

    .lb-modal--review-full .lb-review-card h5 {
      margin: 0 0 .35rem 0;
      font-size: .9rem;
    }

    /* Allow the comments area to shrink on shorter screens */
    .lb-modal--review-full .lb-review-card--comments textarea {
      min-height: 84px;
    }

    /* Make highlight pills a bit more compact */
    .lb-modal--review-full .highlights .btn {
      padding: .25rem .6rem;
      font-size: .82rem;
    }

    .lb-modal--review-full .modal-header { padding: .75rem 1rem; }
    .lb-modal--review-full .modal-footer { padding: .6rem 1rem; }
  }

  /* Desktop-only: extra short viewports */
  @media (min-width: 992px) and (max-height: 760px) {
    .lb-modal--review-full .lb-review-card p.text-muted { display: none; }
    .lb-modal--review-full .lb-review-card--comments textarea { min-height: 72px; }
  }

  /* Mobile: allow scrolling (otherwise it can become unusable on small screens) */
  @media (max-width: 575.98px) {
    .lb-modal--review-full .modal-dialog {
      width: 100vw;
      max-width: 100vw;
      height: calc(var(--lb-vh, 1vh) * 100);
      margin: 0;
    }

    .lb-modal--review-full .modal-content {
      height: calc(var(--lb-vh, 1vh) * 100) !important;
      border-radius: 0 !important;
    }

    .lb-modal--review-full .modal-body {
      overflow: auto;
    }
  }

  @media (max-width:575.98px) {
    .lb-modal .modal-dialog {
      max-width: calc(100% - 1.5rem);
    }
  }

  /* Service Guide modal sizing
   - Desktop: compact like Add-on modal, no scroll needed (content scales down if needed)
   - Mobile: full-screen and uses iOS-safe viewport height (no scroll needed)
*/
  :root {
    --lb-vh: 1vh;
  }

  #serviceGuideModal .modal-content {
    display: flex;
    flex-direction: column;
  }

  #serviceGuideModal .modal-body {
    flex: 1 1 auto;
    min-height: 0;
  }

  #serviceGuideModal .modal-footer {
    flex: 0 0 auto;
  }

  /* Mobile full height (fixes iPhone Safari 100vh issues) */
  
#client_change_booster_confirm_md .lb-confirmModal2__btn i { flex: 0 0 auto; }
#client_change_booster_confirm_md .lb-confirmModal2__btn--primary { min-width: 220px; white-space: nowrap; }
#client_change_booster_confirm_md .lb-confirmModal2__btn--ghost { min-width: 120px; }
@media (max-width: 767.98px) {
  #client_change_booster_confirm_md .lb-confirmModal2__btn--primary,
  #client_change_booster_confirm_md .lb-confirmModal2__btn--ghost { min-width: 0; white-space: normal; }
}
@media (max-width: 767.98px) {
    #serviceGuideModal .modal-dialog {
      width: 100vw;
      max-width: 100vw;
      height: calc(var(--lb-vh, 1vh) * 100);
      margin: 0;
    }

    #serviceGuideModal .modal-content {
      height: calc(var(--lb-vh, 1vh) * 100) !important;
      border-radius: 0 !important;
      overflow: hidden;
    }

    #serviceGuideModal .modal-header {
      padding: .95rem 1rem !important;
    }

    #serviceGuideModal .modal-body {
      padding: .95rem 1rem .65rem !important;
      overflow-y: hidden;
    }

    #serviceGuideModal .modal-footer {
      padding: .75rem 1rem calc(.85rem + env(safe-area-inset-bottom)) !important;
    }

    /* Make content fit without scrolling on iPhone */
    #serviceGuideModal .sg-pill {
      font-size: .75rem;
      padding: .3rem .6rem;
    }

    #serviceGuideModal .sg-subtitle {
      font-size: .9rem;
      line-height: 1.25;
    }

    #serviceGuideModal .sg-guideline {
      padding: .75rem .9rem !important;
      margin-bottom: .6rem !important;
      border-radius: 14px;
    }

    #serviceGuideModal .sg-num {
      width: 34px !important;
      height: 34px !important;
      font-size: .95rem !important;
    }

    #serviceGuideModal .sg-text {
      font-size: .92rem !important;
      line-height: 1.24;
    }

    #serviceGuideModal .sg-alert {
      padding: .75rem .9rem !important;
      margin-bottom: .7rem !important;
      border-radius: 14px;
    }

    #serviceGuideModal .btn-service-guide {
      padding: .78rem 1rem !important;
      font-size: 1rem !important;
    }
  }

  /* Desktop: cap size similar to Add-on modal and ensure no scroll */
  @media (min-width: 768px) {
    #serviceGuideModal .modal-dialog {
      width: min(740px, calc(100vw - 2.5rem));
      max-width: 740px;
      margin: 1.25rem auto;
      max-height: min(760px, calc(var(--lb-vh, 1vh) * 100 - 5rem));
    }

    #serviceGuideModal .modal-content {
      max-height: inherit;
      overflow: hidden;
    }

    #serviceGuideModal .modal-body {
      overflow-y: hidden;
    }
  }

  /* Compact levels (applied by JS when needed) */
  #serviceGuideModal.sg-compact-1 .modal-header {
    padding: .9rem 1rem !important;
  }

  #serviceGuideModal.sg-compact-1 .modal-body {
    padding: .85rem 1rem .55rem !important;
  }

  #serviceGuideModal.sg-compact-1 .modal-footer {
    padding: .75rem 1rem !important;
  }

  #serviceGuideModal.sg-compact-1 .sg-subtitle {
    font-size: .9rem;
  }

  #serviceGuideModal.sg-compact-1 .sg-guideline {
    padding: .75rem .95rem !important;
    margin-bottom: .65rem !important;
  }

  #serviceGuideModal.sg-compact-1 .sg-num {
    width: 34px !important;
    height: 34px !important;
    font-size: .95rem !important;
  }

  #serviceGuideModal.sg-compact-1 .sg-text {
    font-size: .92rem;
    line-height: 1.22;
  }

  #serviceGuideModal.sg-compact-1 .sg-alert {
    padding: .75rem .95rem !important;
    margin-bottom: .75rem !important;
  }

  #serviceGuideModal.sg-compact-1 .btn-service-guide {
    padding: .75rem 1rem !important;
  }

  #serviceGuideModal.sg-compact-2 .modal-header {
    padding: .8rem .95rem !important;
  }

  #serviceGuideModal.sg-compact-2 .modal-body {
    padding: .75rem .95rem .5rem !important;
  }

  #serviceGuideModal.sg-compact-2 .modal-footer {
    padding: .7rem .95rem !important;
  }

  #serviceGuideModal.sg-compact-2 .sg-pill {
    font-size: .72rem;
    padding: .25rem .55rem;
  }

  #serviceGuideModal.sg-compact-2 .sg-subtitle {
    font-size: .85rem;
    line-height: 1.2;
  }

  #serviceGuideModal.sg-compact-2 .sg-guideline {
    padding: .65rem .85rem !important;
    margin-bottom: .55rem !important;
  }

  #serviceGuideModal.sg-compact-2 .sg-num {
    width: 30px !important;
    height: 30px !important;
    font-size: .9rem !important;
  }

  #serviceGuideModal.sg-compact-2 .sg-text {
    font-size: .88rem;
    line-height: 1.18;
  }

  #serviceGuideModal.sg-compact-2 .sg-alert {
    padding: .65rem .85rem !important;
    margin-bottom: .65rem !important;
  }

  #serviceGuideModal.sg-compact-2 .btn-service-guide {
    padding: .68rem 1rem !important;
    font-size: .98rem !important;
  }

  #serviceGuideModal.sg-compact-3 .modal-header {
    padding: .7rem .9rem !important;
  }

  #serviceGuideModal.sg-compact-3 .modal-body {
    padding: .65rem .9rem .45rem !important;
  }

  #serviceGuideModal.sg-compact-3 .modal-footer {
    padding: .65rem .9rem !important;
  }

  #serviceGuideModal.sg-compact-3 .sg-subtitle {
    font-size: .82rem;
    line-height: 1.15;
  }

  #serviceGuideModal.sg-compact-3 .sg-guideline {
    padding: .55rem .75rem !important;
    margin-bottom: .45rem !important;
    border-radius: 12px;
  }

  #serviceGuideModal.sg-compact-3 .sg-num {
    width: 26px !important;
    height: 26px !important;
    font-size: .85rem !important;
  }

  #serviceGuideModal.sg-compact-3 .sg-text {
    font-size: .84rem;
    line-height: 1.12;
  }

  #serviceGuideModal.sg-compact-3 .sg-alert {
    padding: .55rem .75rem !important;
    margin-bottom: .55rem !important;
    border-radius: 12px;
  }

  #serviceGuideModal.sg-compact-3 .btn-service-guide {
    padding: .62rem 1rem !important;
    font-size: .95rem !important;
  }

  /* Fallback if viewport is extremely small */
  #serviceGuideModal.sg-allow-scroll .modal-body {
    overflow-y: auto !important;
    -webkit-overflow-scrolling: touch;
  }

  .lb-modal .modal-content {
    border-radius: 1.25rem;
    background: #25282A;
    border: 1px solid rgba(255, 255, 255, .08);
    box-shadow: 0 30px 90px rgba(0, 0, 0, .65);
    overflow: hidden;
  }

  .lb-modal .modal-header {
    background: rgba(255, 255, 255, .03);
    border-bottom: 1px solid rgba(255, 255, 255, .08);
    padding: 1rem 1.1rem;
  }

  .lb-modal .modal-footer {
    background: rgba(255, 255, 255, .02);
    border-top: 1px solid rgba(255, 255, 255, .08);
    padding: .9rem 1.1rem;
  }

  .lb-modal .modal-body {
    padding: 1rem 1.1rem;
  }

  .lb-modal .lb-modal-title {
    font-weight: 900;
    font-size: 1.05rem;
    margin: 0;
    display: flex;
    align-items: center;
    gap: .55rem;
  }

  .lb-modal .lb-modal-sub {
    margin: .2rem 0 0;
    font-size: .9rem;
    opacity: .7;
  }

  .lb-modal .lb-field-title {
    font-weight: 900;
    margin-bottom: .55rem;
  }

  .lb-modal textarea.form-control {
    border-radius: 1rem;
    background: rgba(255, 255, 255, .03);
    border: 1px solid rgba(255, 255, 255, .10);
    color: inherit;
  }

  .lb-modal textarea.form-control::placeholder {
    opacity: .55;
  }

  /* Notes modals */
  .lb-modal--note .modal-dialog {
    max-width: 720px;
  }

  @media (max-width:575.98px) {
    .lb-modal--note .modal-dialog {
      max-width: calc(100% - 1.5rem);
    }
  }

  .lb-modal--note .lb-textarea {
    min-height: 160px;
    resize: vertical;
  }

  .lb-modal--note .lb-helper {
    margin-top: .6rem;
    font-size: .85rem;
    opacity: .7;
  }

  /* Riot modal */
  .lb-modal--riot .modal-dialog {
    max-width: 560px;
  }

  @media (max-width:575.98px) {
    .lb-modal--riot .modal-dialog {
      max-width: calc(100% - 1.5rem);
    }
  }

  .lb-modal--riot .lb-field {
    margin-bottom: .9rem;
  }

  .lb-modal--riot .lb-field-label {
    display: block;
    font-weight: 900;
    font-size: .85rem;
    letter-spacing: .06em;
    text-transform: uppercase;
    margin-bottom: .45rem;
    opacity: .85;
  }

  .lb-modal--riot .lb-input {
    min-height: 46px;
    border-radius: 1rem;
    background: rgba(255, 255, 255, .03);
    border: 1px solid rgba(255, 255, 255, .10);
    color: rgba(255, 255, 255, .92);
    text-align: center;
  }

  .lb-modal--riot .lb-input::placeholder {
    opacity: .55;
  }

  .lb-modal--riot .lb-input:focus {
    border-color: rgba(177, 140, 255, .35);
    box-shadow: 0 0 0 .25rem rgba(177, 140, 255, .15);
  }

  /* =========================
   LB MODAL HEADER (shared)
========================= */
  .lb-modal .modal-header {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 1rem;
  }

  .lb-modal .lb-modal-head {
    display: flex;
    align-items: flex-start;
    gap: .85rem;
    min-width: 0;
  }

  .lb-modal .lb-modal-ico {
    width: 46px;
    height: 46px;
    border-radius: 16px;
    display: grid;
    place-items: center;
    background: rgba(255, 255, 255, .06);
    border: 1px solid rgba(255, 255, 255, .10);
    color: rgba(255, 255, 255, .92);
    flex: 0 0 auto;
  }

  .lb-modal .lb-modal-ico--tip {
    background: rgba(88, 101, 242, .14);
    border-color: rgba(88, 101, 242, .26);
    color: #cfd5ff;
  }

  .lb-modal .lb-modal-headtxt {
    min-width: 0;
  }

  .lb-modal .lb-modal-title {
    margin: 0;
    font-weight: 950;
    font-size: 1.05rem;
    line-height: 1.2;
  }

  .lb-modal .lb-modal-sub {
    margin: .25rem 0 0;
    opacity: .72;
    font-size: .9rem;
    line-height: 1.35;
  }

  /* custom close button */
  .lb-modal .lb-modal-x {
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
  }

  .lb-modal .lb-modal-x:hover {
    background: rgba(255, 255, 255, .07);
    border-color: rgba(255, 255, 255, .16);
    color: #fff;
    transform: translateY(-1px);
  }

  /* Light theme */
  [data-theme="light"] .lb-modal .lb-modal-x {
    background: #fff;
    border-color: rgba(0, 0, 0, .10);
    color: rgba(0, 0, 0, .70);
  }

  [data-theme="light"] .lb-modal .lb-modal-x:hover {
    background: rgba(0, 0, 0, .03);
    border-color: rgba(0, 0, 0, .14);
    color: rgba(0, 0, 0, .85);
  }

  /* =========================
   CUSTOM VPN DROPDOWN (replaces TomSelect)
========================= */
  .lb-csd {
    position: relative;
    width: 100%;
  }

  .lb-csd__control {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: .5rem;
    background: rgba(255, 255, 255, .04);
    border: 1px solid rgba(255, 255, 255, .10);
    border-radius: 1rem;
    min-height: 46px;
    padding: .55rem .9rem;
    box-shadow: inset 0 1px 0 rgba(255, 255, 255, .04);
    cursor: pointer;
    user-select: none;
    transition: border-color .15s;
  }

  .lb-csd__control:hover {
    border-color: rgba(255, 255, 255, .22);
  }

  .lb-csd__control.is-open {
    border-color: rgba(255, 255, 255, .28);
    border-bottom-left-radius: 0;
    border-bottom-right-radius: 0;
  }

  .lb-csd__value {
    flex: 1;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
    color: rgba(255, 255, 255, .92);
    font-size: 1rem;
  }

  .lb-csd__arrow {
    flex-shrink: 0;
    width: 18px;
    height: 18px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: rgba(255, 255, 255, .55);
    transition: transform .2s;
  }

  .lb-csd__control.is-open .lb-csd__arrow {
    transform: rotate(180deg);
  }

  /* opens UPWARD */
  .lb-csd__panel {
    position: absolute;
    bottom: calc(100% - 1px);
    left: 0;
    right: 0;
    background: #1f2226;
    border: 1px solid rgba(255, 255, 255, .10);
    border-radius: 1rem 1rem 0 0;
    overflow: hidden;
    box-shadow: 0 -18px 60px rgba(0, 0, 0, .55);
    z-index: 9999;
    display: none;
    flex-direction: column;
  }

  .lb-csd__panel.is-open {
    display: flex;
  }

  .lb-csd__search-wrap {
    padding: .55rem .75rem;
    border-bottom: 1px solid rgba(255, 255, 255, .07);
    flex-shrink: 0;
  }

  .lb-csd__search {
    width: 100%;
    background: rgba(255, 255, 255, .06);
    border: 1px solid rgba(255, 255, 255, .10);
    border-radius: .6rem;
    color: rgba(255, 255, 255, .92);
    font-size: .875rem;
    padding: .4rem .7rem;
    outline: none;
    transition: border-color .15s;
  }

  .lb-csd__search::placeholder {
    color: rgba(255, 255, 255, .35);
  }

  .lb-csd__search:focus {
    border-color: rgba(255, 255, 255, .25);
  }

  .lb-csd__list {
    overflow-y: auto;
    max-height: 220px;
    padding: .3rem 0;
  }

  .lb-csd__list::-webkit-scrollbar { width: 5px; }
  .lb-csd__list::-webkit-scrollbar-track { background: transparent; }
  .lb-csd__list::-webkit-scrollbar-thumb { background: rgba(255,255,255,.15); border-radius: 99px; }

  .lb-csd__option {
    padding: .55rem .9rem;
    color: rgba(255, 255, 255, .88);
    font-size: .9375rem;
    cursor: pointer;
    transition: background .1s;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
  }

  .lb-csd__option:hover,
  .lb-csd__option.is-focused {
    background: rgba(255, 255, 255, .06);
    color: rgba(255, 255, 255, .98);
  }

  .lb-csd__option.is-selected {
    background: rgba(255, 255, 255, .09);
    color: #fff;
    font-weight: 500;
  }

  .lb-csd__empty {
    padding: .7rem .9rem;
    color: rgba(255, 255, 255, .4);
    font-size: .875rem;
    text-align: center;
  }

  /* Light theme overrides */
  [data-theme="light"] .lb-csd__control {
    background: #fff;
    border-color: rgba(0, 0, 0, .10);
    color: rgba(0, 0, 0, .85);
  }

  [data-theme="light"] .lb-csd__value {
    color: rgba(0, 0, 0, .85);
  }

  [data-theme="light"] .lb-csd__arrow {
    color: rgba(0, 0, 0, .45);
  }

  [data-theme="light"] .lb-csd__panel {
    background: #fff;
    border-color: rgba(0, 0, 0, .10);
    box-shadow: 0 -12px 40px rgba(0, 0, 0, .15);
  }

  [data-theme="light"] .lb-csd__search {
    background: rgba(0, 0, 0, .04);
    border-color: rgba(0, 0, 0, .10);
    color: rgba(0, 0, 0, .85);
  }

  [data-theme="light"] .lb-csd__search::placeholder {
    color: rgba(0, 0, 0, .35);
  }

  [data-theme="light"] .lb-csd__option {
    color: rgba(0, 0, 0, .85);
  }

  [data-theme="light"] .lb-csd__option:hover,
  [data-theme="light"] .lb-csd__option.is-focused {
    background: rgba(0, 0, 0, .04);
  }

  [data-theme="light"] .lb-csd__option.is-selected {
    background: rgba(0, 0, 0, .07);
  }

  /* keep large height, but normal font */
  .form-control-lg,
  .form-select-lg,
  .ts-control {
    font-size: 1rem !important;
  }


  /* =========================
   TIP MODAL (restore premium CSS)
========================= */
  #send_tip_md .lb-tip-grid {
    display: flex;
    flex-wrap: wrap;
    gap: .55rem;
    margin: .55rem 0 1rem;
  }

  #send_tip_md .lb-tip-chip {
    border-radius: 999px;
    padding: .42rem .85rem;
    font-weight: 900;
    font-size: .9rem;
    border: 1px solid rgba(255, 255, 255, .12);
    background: rgba(255, 255, 255, .04);
    color: rgba(255, 255, 255, .92);
    box-shadow: 0 10px 30px rgba(0, 0, 0, .22);
    transition: .15s ease;
  }

  #send_tip_md .lb-tip-chip:hover {
    transform: translateY(-1px);
    background: rgba(255, 255, 255, .06);
    border-color: rgba(255, 255, 255, .18);
    color: #fff;
  }

  #send_tip_md .lb-tip-chip.is-active {
    background: rgba(88, 101, 242, .22);
    border-color: rgba(88, 101, 242, .35);
    box-shadow: 0 14px 40px rgba(0, 0, 0, .28);
    color: #fff;
  }

  #send_tip_md .lb-tip-amount {
    display: grid;
    grid-template-columns: 46px 1fr 46px;
    gap: .6rem;
    align-items: center;
    margin-bottom: 1rem;
  }

  #send_tip_md .lb-tip-amount .btn {
    width: 46px;
    height: 46px;
    border-radius: 14px;
    font-weight: 950;
    border: 1px solid rgba(255, 255, 255, .12);
    background: rgba(255, 255, 255, .04);
    color: rgba(255, 255, 255, .92);
    transition: .15s ease;
  }

  #send_tip_md .lb-tip-amount .btn:hover {
    transform: translateY(-1px);
    background: rgba(255, 255, 255, .06);
    border-color: rgba(255, 255, 255, .18);
    color: #fff;
  }

  #send_tip_md #tip-amount {
    height: 46px;
    border-radius: 14px;
    text-align: center;
    font-weight: 900;
    letter-spacing: .02em;
    background: rgba(0, 0, 0, .18);
    border: 1px solid rgba(255, 255, 255, .10);
    color: rgba(255, 255, 255, .92);
  }

  #send_tip_md #tip-amount:focus {
    border-color: rgba(88, 101, 242, .35);
    box-shadow: 0 0 0 .25rem rgba(88, 101, 242, .15);
    outline: 0;
  }

  #send_tip_md textarea.form-control {
    border-radius: 1rem;
    background: rgba(255, 255, 255, .03);
    border: 1px solid rgba(255, 255, 255, .10);
    color: rgba(255, 255, 255, .92);
  }

  #send_tip_md textarea.form-control::placeholder {
    opacity: .55;
  }

  /* Light theme */
  [data-theme="light"] #send_tip_md .lb-tip-chip {
    background: #fff;
    border-color: rgba(0, 0, 0, .10);
    color: rgba(0, 0, 0, .82);
    box-shadow: 0 12px 30px rgba(0, 0, 0, .08);
  }

  [data-theme="light"] #send_tip_md .lb-tip-chip:hover {
    background: rgba(0, 0, 0, .03);
    border-color: rgba(0, 0, 0, .14);
    color: rgba(0, 0, 0, .88);
  }

  [data-theme="light"] #send_tip_md .lb-tip-chip.is-active {
    background: rgba(88, 101, 242, .14);
    border-color: rgba(88, 101, 242, .28);
    color: rgba(0, 0, 0, .90);
  }

  [data-theme="light"] #send_tip_md .lb-tip-amount .btn {
    background: #fff;
    border-color: rgba(0, 0, 0, .10);
    color: rgba(0, 0, 0, .75);
  }

  [data-theme="light"] #send_tip_md #tip-amount {
    background: #fff;
    border-color: rgba(0, 0, 0, .10);
    color: rgba(0, 0, 0, .85);
  }

  #send_tip_md .lb-btn {
    border-radius: 999px;
    font-weight: 950;
    padding: .60rem 1.05rem;
    border: 1px solid rgba(255, 255, 255, .10);
    background: rgba(255, 255, 255, .04);
    color: rgba(255, 255, 255, .92);
  }

  #send_tip_md .lb-btn:hover {
    background: rgba(255, 255, 255, .06);
    border-color: rgba(255, 255, 255, .16);
    color: #fff;
    transform: translateY(-1px);
  }

  #send_tip_md .lb-btn-ghost {
    background: transparent;
  }

  #send_tip_md .lb-btn-success {
    background: rgba(88, 101, 242, .22);
    border-color: rgba(88, 101, 242, .35);
    color: #fff;
  }

  #send_tip_md .lb-btn-success:hover {
    background: rgba(88, 101, 242, .32);
    border-color: rgba(88, 101, 242, .45);
  }

  /* =========================
   OPTIONS (segmented + select)

   .lb-opt-section-title{
     font-weight:800;
     letter-spacing:.06em;
     text-transform:uppercase;
     font-size:.72rem;
     opacity:.65;
     margin-bottom:10px;
   }

========================= */
  .card .lb-options-form {
    margin-top: .25rem;
  }

  .lb-opt-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 1rem;
    padding: .85rem 0;
  }

  .lb-opt-row+.lb-opt-row {
    border-top: 1px solid rgba(255, 255, 255, .06);
  }

  .lb-opt-left {
    display: flex;
    align-items: center;
    gap: .75rem;
    min-width: 0;
  }

  .lb-opt-ico {
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

  .lb-opt-ico-img {
    width: 22px;
    height: 22px;
    display: block;
    object-fit: contain;
  }

  .lb-opt-text {
    min-width: 0;
  }

  .lb-opt-label {
    font-weight: 900;
    font-size: 1rem;
    white-space: nowrap;
  }

  .lb-opt-sub {
    font-size: .82rem;
    opacity: .70;
    margin-top: .15rem;
  }

  .lb-review-role-pill {
    display: inline-flex;
    align-items: center;
    gap: .4rem;
    margin-top: .32rem;
    padding: .22rem .58rem;
    border-radius: 999px;
    font-size: .74rem;
    font-weight: 800;
    line-height: 1;
    color: rgba(31, 230, 198, .95);
    background: rgba(31, 230, 198, .10);
    border: 1px solid rgba(31, 230, 198, .18);
    width: fit-content;
  }

  .lb-review-role-pill i {
    font-size: .78rem;
  }

  .lb-opt-right {
    flex: 0 0 auto;
  }

  .lb-opt-right--w {
    width: 320px;
  }

  @media (max-width:575.98px) {
    .lb-opt-right--w {
      width: 100%;
    }
  }

  .lb-seg {
    width: 100%;
    display: flex;
    padding: 6px;
    border-radius: 999px;
    border: 1px solid rgba(255, 255, 255, .10);
    background: rgba(255, 255, 255, .03);
    gap: 6px;
  }

  .lb-seg-btn {
    flex: 1 1 auto;
    border: 0;
    padding: .55rem .8rem;
    border-radius: 999px;
    font-weight: 900;
    background: transparent;
    color: rgba(255, 255, 255, .80);
    transition: .15s ease;
    white-space: nowrap;
  }

  .lb-seg-btn:hover {
    background: rgba(255, 255, 255, .06);
    color: #fff;
  }

  .lb-seg-btn.is-active {
    background: rgba(78, 161, 255, .22);
    color: #fff;
    box-shadow: 0 10px 30px rgba(0, 0, 0, .25);
  }

  .lb-opt-select .form-select {
    width: 100%;
    min-height: 48px;
    border-radius: 999px;
    background-color: rgba(255, 255, 255, .04) !important;
    border: 1px solid rgba(255, 255, 255, .10) !important;
    color: rgba(255, 255, 255, .90) !important;
  }

  .lb-opt-select .form-select:focus {
    box-shadow: 0 0 0 .25rem rgba(177, 140, 255, .15) !important;
    border-color: rgba(177, 140, 255, .35) !important;
  }

  .lb-opt-actions {
    display: flex;
    justify-content: flex-start;
    margin-top: 1rem;
  }

  .lb-opt-save {
    border-radius: 14px;
    font-weight: 900;
  }

  @media (max-width:575.98px) {
    .lb-opt-actions {
      justify-content: stretch;
    }

    .lb-opt-save {
      width: 100%;
    }
  }

  /* Responsive stacking */
  @media (max-width:767.98px) {
    .lb-opt-row {
      flex-direction: column;
      align-items: stretch;
      gap: .75rem;
    }

    .lb-opt-left {
      width: 100%;
    }
  }

  /* Desktop fix */
  @media (min-width:992px) {
    .lb-opt-sub--desktop-hide {
      display: none !important;
    }

    .lb-opt-right--w {
      width: 200px;
    }

    .lb-seg {
      padding: 4px;
      gap: 4px;
    }

    .lb-seg-btn {
      padding: .42rem .70rem;
      font-size: .88rem;
      line-height: 1.1;
    }

    .lb-opt-label {
      font-size: .95rem;
    }

    /* VPN width = 200px */
    .lb-opt-select {
      min-width: 0 !important;
    }

    .lb-opt-right--w.lb-opt-select {
      width: 200px !important;
    }

    .lb-opt-right--w.lb-opt-select .lb-csd {
      width: 100% !important;
    }
  }

  /* Static options (Priority/Bonus/Hidden Duo): always one row */
  .lb-opt-static .lb-opt-row--static {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 1rem;
    padding: .70rem 0;
  }

  .lb-opt-static .lb-opt-row--static .lb-opt-left {
    min-width: 0;
    flex: 1 1 auto;
  }

  .lb-opt-static .lb-opt-row--static .lb-opt-label {
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
  }

  .lb-opt-static .lb-opt-row--static .lb-opt-right {
    flex: 0 0 auto;
    margin-left: auto;
    display: flex;
    align-items: center;
    justify-content: flex-end;
    white-space: nowrap;
  }

  /* override global mobile stacking */
  @media (max-width:767.98px) {
    .lb-opt-static .lb-opt-row--static {
      flex-direction: row !important;
      align-items: center !important;
    }

    .lb-opt-static .lb-opt-row--static .lb-opt-left {
      width: auto !important;
    }
  }

  /* value pill */
  .lb-opt-pill {
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
  }

  .lb-opt-dot {
    width: 8px;
    height: 8px;
    border-radius: 999px;
    background: rgba(255, 255, 255, .35);
    box-shadow: 0 0 0 4px rgba(255, 255, 255, .06);
  }

  .lb-opt-pill.is-yes {
    color: #1fe6c6;
    border-color: rgba(31, 230, 198, .22);
    background: rgba(31, 230, 198, .10);
  }

  .lb-opt-pill.is-yes .lb-opt-dot {
    background: #1fe6c6;
    box-shadow: 0 0 0 4px rgba(31, 230, 198, .08);
  }

  .lb-opt-pill.is-no {
    color: #ff6b6b;
    border-color: rgba(255, 107, 107, .22);
    background: rgba(255, 107, 107, .10);
  }

  .lb-opt-pill.is-no .lb-opt-dot {
    background: #ff6b6b;
    box-shadow: 0 0 0 4px rgba(255, 107, 107, .08);
  }

  .lb-opt-pill.is-neutral {
    color: rgba(255, 255, 255, .85);
  }

  /* =========================
   NOTES LIST
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

  .lb-note-item:hover {
    transform: translateY(-1px);
    background: rgba(255, 255, 255, .045);
    border-color: rgba(255, 255, 255, .12);
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

  .lb-note-actions {
    display: flex;
    gap: .45rem;
    flex: 0 0 auto;
  }

  .lb-note-action {
    width: 38px;
    height: 38px;
    border-radius: 14px;
    display: grid;
    place-items: center;
    border: 1px solid rgba(255, 255, 255, .10);
    background: rgba(255, 255, 255, .04);
    color: rgba(255, 255, 255, .9);
    transition: .15s ease;
  }

  .lb-note-action:hover {
    background: rgba(255, 255, 255, .07);
    border-color: rgba(255, 255, 255, .16);
    transform: translateY(-1px);
  }

  .lb-note-action--danger {
    color: #ff6b6b;
    border-color: rgba(255, 107, 107, .22);
    background: rgba(255, 107, 107, .08);
  }

  .lb-note-action--danger:hover {
    background: rgba(255, 107, 107, .12);
    border-color: rgba(255, 107, 107, .30);
  }

  .lb-notes-empty {
    padding: 1.35rem 1rem;
    border-radius: 1rem;
    border: 1px dashed rgba(255, 255, 255, .12);
    background: rgba(255, 255, 255, .02);
    display: flex;
    flex-direction: column;
    align-items: center;
    text-align: center;
    gap: .55rem;
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

  .lb-notes-empty-title {
    font-weight: 900;
    font-size: 1.05rem;
  }

  .lb-notes-empty-sub {
    opacity: .72;
    max-width: 52ch;
    font-size: .9rem;
  }

  @media (max-width:575.98px) {
    .lb-note-item {
      flex-direction: column;
    }

    .lb-note-actions {
      width: 100%;
      justify-content: flex-end;
    }

    .lb-note-btn {
      width: 100%;
      display: flex;
      justify-content: center;
    }
  }

  [data-theme="light"] .lb-note-item {
    background: rgba(0, 0, 0, .02);
    border-color: rgba(0, 0, 0, .08);
  }

  [data-theme="light"] .lb-note-item:hover {
    background: rgba(0, 0, 0, .03);
    border-color: rgba(0, 0, 0, .12);
  }

  [data-theme="light"] .lb-note-text {
    color: rgba(0, 0, 0, .85);
  }

  [data-theme="light"] .lb-note-ico,
  [data-theme="light"] .lb-notes-empty-ico {
    background: #fff;
    border-color: rgba(0, 0, 0, .10);
    color: rgba(0, 0, 0, .75);
  }

  [data-theme="light"] .lb-note-action {
    background: #fff;
    border-color: rgba(0, 0, 0, .10);
    color: rgba(0, 0, 0, .75);
  }

  [data-theme="light"] .lb-notes-empty {
    background: rgba(0, 0, 0, .02);
    border-color: rgba(0, 0, 0, .12);
  }

  /* =========================
   OVERVIEW (stacked list)
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
  }

  .lb-ov-item:hover {
    background: rgba(255, 255, 255, .04);
    border-color: rgba(255, 255, 255, .12);
    transform: translateY(-1px);
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

  .lb-ov-label {
    grid-column: 2;
    grid-row: 1;
    font-weight: 900;
    font-size: .95rem;
    line-height: 1.15;
    white-space: normal;
    min-width: 0;
  }

  .lb-ov-value {
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
  }

  @media (max-width:575.98px) {
    .lb-ov-item {
      padding: .58rem .68rem;
    }

    .lb-ov-ico {
      width: 42px;
      height: 42px;
      border-radius: 15px;
    }
  }

  .lb-ov-pill {
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
  }

  .lb-ov-pill--no {
    color: #ff6b6b;
    border-color: rgba(255, 107, 107, .20);
    background: rgba(255, 107, 107, .10);
  }

  .lb-ov-pill--yes {
    color: #1fe6c6;
    border-color: rgba(31, 230, 198, .22);
    background: rgba(31, 230, 198, .10);
  }

  /* =========================
   ORDER HEADER (lb-head)
========================= */
  .lb-head.card {
    border-radius: 18px;
    border: 1px solid rgba(255, 255, 255, .07);
    background: rgba(255, 255, 255, .03);
    overflow: visible !important;
    /* dropdown */
    position: relative;
  }

  .lb-head .dropdown-menu {
    z-index: 1060;
  }

  /* Dropdown-Caret weg (Header + Aktionen) */
  .lb-head .dropdown-toggle::after {
    display: none !important;
  }

  /* Order Actions button = pill/rounded (wie Booster) */
  .lb-actions-btn {
    border-radius: 999px !important;
    padding: .48rem .85rem;
  }

  @media (max-width:767.98px) {
    .lb-actions-btn {
      width: 36px;
      height: 36px;
      padding: 0 !important;
      display: flex !important;
      align-items: center !important;
      justify-content: center !important;
      border-radius: 999px !important;
      line-height: 1 !important;
    }
    /* Hide dropdown arrow caret on mobile — icon only */
    .lb-actions-btn::after {
      display: none !important;
    }
    .lb-actions-btn .fa-ellipsis-vertical {
      font-size: 1rem;
      line-height: 1;
      display: block;
    }
  }

  .lb-head .dropdown-divider {
    border-top: 1px solid rgba(255,255,255,.08) !important;
    opacity: 1 !important;
    height: 0 !important;
    margin: .35rem .85rem !important;
  }
  /* Dropdown divider (global, dark) */
  [data-theme="dark"] .dropdown-menu .dropdown-divider {
    border-top: 1px solid rgba(255,255,255,.08) !important;
    opacity: 1 !important;
    height: 0 !important;
    margin: .35rem .85rem !important;
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

  .lb-head__icon img,
  .lb-head__icon .boost-form-svg {
    width: 1.35rem;
    height: 1.35rem;
    display: block;
  }

  @media (max-width: 575.98px) {
    .lb-head__icon img,
    .lb-head__icon .boost-form-svg {
      width: 1.1rem;
      height: 1.1rem;
    }
    .lb-head__icon i {
      font-size: 1.1rem;
    }
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
    opacity: .90;
    min-width: 0;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
  }

  /* =========================
   MOBILE: Meta-Pills IMMER 2-zeilig (Label oben, Value unten)
========================= */
  @media (max-width: 575.98px) {

    /* Pill selbst: untereinander statt nebeneinander */
    .lb-meta-pill {
      flex-direction: column !important;
      align-items: flex-start !important;
      gap: .25rem !important;
      min-width: 0 !important;
    }

    /* Label immer eigene Zeile */
    .lb-meta-pill__k {
      display: block !important;
      width: 100% !important;
      white-space: nowrap !important;
      line-height: 1.05 !important;
    }

    /* Value immer darunter: NICHT abschneiden */
    .lb-meta-pill__v {
      display: block !important;
      width: 100% !important;

      white-space: normal !important;
      /* wrap erlauben */
      overflow: visible !important;
      /* nicht verstecken */
      text-overflow: unset !important;
      /* kein ... */
      line-height: 1.15 !important;
      word-break: break-word !important;
      overflow-wrap: anywhere !important;
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

  /* Status colors */
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

  .lb-status.status-refund {
    color: #ff8a4c;
    border-color: rgba(255, 138, 76, .22);
    background: rgba(255, 138, 76, .10);
  }

  .lb-status.status-inprogress .lb-status__dot {
    background: #4ea1ff;
  }

  .lb-status.status-completed .lb-status__dot {
    background: #1fe6c6;
  }

  .lb-status.status-paused .lb-status__dot {
    background: #ffc44d;
  }

  .lb-status.status-unpaid .lb-status__dot {
    background: #ff6b6b;
  }

  .lb-status.status-paid .lb-status__dot {
    background: #b18cff;
  }

  .lb-status.status-processing .lb-status__dot {
    background: #9aa4b2;
  }

  .lb-status.status-refund .lb-status__dot {
    background: #ff8a4c;
  }

  /* Mobile: title clamp + ID unter title */
  @media (max-width:575.98px) {
    .lb-head__top {
      padding: .85rem .85rem .70rem .85rem;
    }

    .lb-head__meta {
      padding: .70rem .85rem .85rem .85rem;
    }

    .lb-head__icon {
      width: 40px;
      height: 40px;
      border-radius: 12px;
      align-self: center;
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
      font-size: .92rem;
      white-space: normal !important;
      display: -webkit-box !important;
      -webkit-box-orient: vertical !important;
      -webkit-line-clamp: 3 !important;
      overflow: hidden !important;
      text-overflow: ellipsis !important;
    }

    .lb-meta-pill {
      min-width: 0;
      flex: 1 1 calc(50% - .55rem);
    }

    .lb-meta-pill__v {
      font-size: .90rem;
    }
  }

  @media (min-width:768px) {
    .lb-meta-pill {
      min-width: 210px;
    }
  }

  /* =========================
   REVIEW CARD (final)
========================= */
  .lb-review-card {
    border-radius: 1.25rem;
    border: 1px solid rgba(255, 255, 255, .08);
    background: rgba(255, 255, 255, .03);
    overflow: hidden;
  }

  [data-theme="light"] .lb-review-card {
    border-color: rgba(0, 0, 0, .08);
    background: rgba(0, 0, 0, .02);
  }

  .lb-review-card .card-body {
    padding: 1rem 1.1rem;
  }

  .lb-review-body {
    display: flex;
    flex-direction: column;
    gap: 1rem;
  }

  .lb-review-top {
    display: flex;
    align-items: center;
    gap: 1rem;
    width: 100%;
  }

  .lb-review-avatar {
    width: 56px;
    height: 56px;
    border-radius: 999px;
    overflow: hidden;
    flex: 0 0 auto;
    border: 1px solid rgba(255, 255, 255, .10);
    box-shadow: 0 14px 40px rgba(0, 0, 0, .45);
    background: rgba(255, 255, 255, .03);
  }

  .lb-review-avatar img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    display: block;
  }

  .lb-review-text {
    min-width: 0;
    flex: 1;
  }

  .lb-review-pillrow {
    margin-bottom: .45rem;
    display: flex;
    align-items: center;
    gap: .6rem;
    flex-wrap: wrap;
  }

  .lb-pill {
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
  }

  .lb-pill--success {
    color: #1fe6c6;
    border-color: rgba(31, 230, 198, .25);
    background: rgba(31, 230, 198, .10);
  }

  .lb-pill--action {
    text-decoration: none;
    cursor: pointer;
    color: #b7b1ff;
    border-color: rgba(110, 92, 255, .35);
    background: rgba(110, 92, 255, .14);
    transition: background .15s ease, border-color .15s ease, color .15s ease;
  }

  .lb-pill--action:hover {
    color: #ffffff;
    border-color: rgba(110, 92, 255, .55);
    background: rgba(110, 92, 255, .24);
  }

  .lb-pill--action:focus-visible {
    outline: 2px solid rgba(110, 92, 255, .6);
    outline-offset: 2px;
  }

  .lb-review-title {
    font-weight: 900;
    font-size: 1.05rem;
    line-height: 1.2;
    margin: 0;
  }

  .lb-review-name {
    color: #1fe6c6;
    text-shadow: 0 1px 0 rgba(0, 0, 0, .25);
  }

  .lb-review-sub {
    margin-top: .25rem;
    opacity: .72;
    font-size: .92rem;
  }

  .lb-review-tip {
    flex: 0 0 auto;
    display: flex;
    align-items: center;
    gap: .6rem;
  }

  @media (max-width:575.98px){
    .lb-review-tip{
      width: 100%;
      flex-direction: column;
      align-items: stretch;
    }
  }

.lb-review-tip .btn {
    border-radius: 999px;
    font-weight: 900;
  }

  /* bottom centered */
  .lb-review-bottom {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: .6rem;
    padding-top: .85rem;
    border-top: 1px solid rgba(255, 255, 255, .06);
  }

  .lb-review-stars-label {
    font-weight: 900;
    letter-spacing: .06em;
    text-transform: uppercase;
    font-size: .78rem;
    opacity: .75;
    text-align: center;
  }

  .lb-review-stars {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: .55rem;
    flex-wrap: wrap;
  }

  .lb-star {
    width: 42px;
    height: 42px;
    border-radius: 14px;
    border: 1px solid rgba(255, 255, 255, .10);
    background: rgba(255, 255, 255, .03);
    display: grid;
    place-items: center;
    transition: .15s ease;
    padding: 0;
  }

  .lb-star:hover {
    transform: translateY(-1px);
    background: rgba(255, 255, 255, .05);
    border-color: rgba(255, 255, 255, .16);
  }

  .lb-star svg {
    width: 26px;
    height: 26px;
  }

  .lb-star svg path {
    fill: transparent;
    stroke: rgba(31, 230, 198, .60);
    stroke-width: 2;
    transition: fill .12s ease, stroke .12s ease, filter .12s ease;
  }

  .lb-star.is-on svg path {
    fill: rgba(31, 230, 198, 1);
    stroke: rgba(31, 230, 198, 1);
    filter: drop-shadow(0 8px 18px rgba(31, 230, 198, .22));
  }

  @media (max-width:575.98px) {
    .lb-review-top {
      flex-wrap: wrap;
      align-items: flex-start;
    }

    .lb-review-tip {
      width: 100%;
    }

    .lb-review-tip .btn {
      width: 100%;
      justify-content: center;
    }

    .lb-review-title {
      font-size: .85rem;
    }

    .lb-review-sub {
      font-size: .75rem;
    }
  }

  /* =========================
   SPECIFIC MODALS (scoped)
========================= */
  /* Change Booster Modal */
  #client_change_booster_md .lbx-modal__content {
    background: #25282A !important;
    border: 1px solid rgba(255, 255, 255, .10) !important;
    border-radius: 18px !important;
    overflow: hidden !important;
    color: rgba(255, 255, 255, .92) !important;
  }

  #client_change_booster_md .lbx-modal__header {
    padding: 16px 16px 12px;
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    border-bottom: 1px solid rgba(255, 255, 255, .08);
    background: rgba(255, 255, 255, .03);
  }

  #client_change_booster_md .lbx-modal__headLeft {
    display: flex;
    gap: 12px;
    align-items: flex-start;
  }

  #client_change_booster_md .lbx-modal__icon {
    width: 42px;
    height: 42px;
    border-radius: 14px;
    display: grid;
    place-items: center;
    background: rgba(255, 255, 255, .06);
    border: 1px solid rgba(255, 255, 255, .10);
  }

  #client_change_booster_md .lbx-modal__title {
    font-weight: 900;
    font-size: 1.05rem;
    line-height: 1.15;
  }

  #client_change_booster_md .lbx-modal__sub {
    margin-top: 4px;
    opacity: .72;
    font-size: .9rem;
  }

  #client_change_booster_md .lbx-modal__close {
    width: 42px;
    height: 42px;
    border-radius: 14px;
    display: grid;
    place-items: center;
    background: rgba(255, 255, 255, .04);
    border: 1px solid rgba(255, 255, 255, .10);
    color: rgba(255, 255, 255, .85);
  }

  #client_change_booster_md .lbx-modal__close:hover {
    background: rgba(255, 255, 255, .07);
  }

  #client_change_booster_md .lbx-modal__body {
    padding: 16px;
  }

  #client_change_booster_md .lbx-modal__label {
    font-size: .75rem;
    letter-spacing: .10em;
    text-transform: uppercase;
    font-weight: 900;
    opacity: .7;
    margin-bottom: 10px;
  }

  #client_change_booster_md .lbx-modal__control {
    width: 100%;
    border-radius: 14px;
    padding: 12px 14px;
    background: rgba(0, 0, 0, .18);
    border: 1px solid rgba(255, 255, 255, .10);
    color: rgba(255, 255, 255, .92);
    outline: none;
  }


  /* Change Booster: Progress cards */
  #client_change_booster_md .lb-progressCards {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 12px;
    margin-bottom: 12px;
  }

  @media (max-width: 575.98px) {
    #client_change_booster_md .lb-progressCards {
      grid-template-columns: 1fr;
    }
  }

  #client_change_booster_md .lb-progressCard {
    cursor: pointer;
    user-select: none;
    border-radius: 16px;
    border: 1px solid rgba(255, 255, 255, .10);
    background: rgba(255, 255, 255, .03);
    transition: background .12s ease, border-color .12s ease, transform .12s ease;
    display: block;
    overflow: hidden;
  }

  #client_change_booster_md .lb-progressCard:hover {
    background: rgba(255, 255, 255, .05);
    border-color: rgba(255, 255, 255, .14);
    transform: translateY(-1px);
  }

  #client_change_booster_md .lb-progressCard input {
    position: absolute;
    opacity: 0;
    pointer-events: none;
  }

  #client_change_booster_md .lb-progressCard__inner {
    display: flex;
    gap: 12px;
    padding: 12px 12px;
    align-items: flex-start;
  }

  #client_change_booster_md .lb-progressCard__icon {
    width: 34px;
    height: 34px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    border: 1px solid rgba(255, 255, 255, .12);
    background: rgba(0, 0, 0, .18);
    flex: 0 0 auto;
  }

  #client_change_booster_md .lb-progressCard__icon--ok {
    border-color: rgba(70, 220, 160, .25);
    background: rgba(70, 220, 160, .10);
  }

  #client_change_booster_md .lb-progressCard__icon--warn {
    border-color: rgba(255, 80, 110, .35);
    background: rgba(255, 80, 110, .12);
  }

  #client_change_booster_md .lb-progressCard__title {
    font-weight: 900;
    line-height: 1.1;
  }

  #client_change_booster_md .lb-progressCard__sub {
    opacity: .72;
    font-size: .86rem;
    margin-top: 3px;
  }

  #client_change_booster_md .lb-progressCard:has(input:checked) {
    border-color: rgba(123, 92, 255, .45);
    background: rgba(123, 92, 255, .10);
    box-shadow: 0 0 0 1px rgba(123, 92, 255, .18) inset;
  }

  #client_change_booster_md .lb-zeroNote {
    margin-top: 10px;
    border-radius: 14px;
    padding: 10px 12px;
    border: 1px solid rgba(255, 80, 110, .40);
    background: rgba(255, 80, 110, .10);
    font-size: .9rem;
    opacity: .95;
  }

  #client_change_booster_md .lb-hide {
    display: none !important;
  }

  #client_change_booster_md .lbx-modal__help {
    margin-top: 10px;
    font-size: .88rem;
    opacity: .70;
  }

  #client_change_booster_md .lbx-modal__footer {
    padding: 14px 16px;
    display: flex;
    justify-content: flex-end;
    gap: 10px;
    border-top: 1px solid rgba(255, 255, 255, .08);
    background: rgba(255, 255, 255, .02);
  }

  #client_change_booster_md .lbx-modal__btn {
    border-radius: 999px;
    padding: .55rem 1rem;
    font-weight: 900;
    border: 1px solid rgba(255, 255, 255, .10);
    background: rgba(255, 255, 255, .04);
    color: rgba(255, 255, 255, .92);
  }

  #client_change_booster_md .lbx-modal__btn:hover {
    background: rgba(255, 255, 255, .06);
    border-color: rgba(255, 255, 255, .16);
    color: #fff;
  }

  #client_change_booster_md .lbx-modal__btn--ghost {
    background: transparent;
  }

  #client_change_booster_md .lbx-modal__btn--action {
    background: rgba(255, 255, 255, .08);
  }

  /* Account Logins Modal */
  #account_logins_md .lbx-modal__content {
    background: #25282A !important;
    border: 1px solid rgba(255, 255, 255, .10) !important;
    border-radius: 18px !important;
    overflow: hidden !important;
    color: rgba(255, 255, 255, .92) !important;
    box-shadow: 0 30px 90px rgba(0, 0, 0, .65);
  }

  #account_logins_md .lbx-modal__header {
    padding: 12px 14px;
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    border-bottom: 1px solid rgba(255, 255, 255, .08);
    background: rgba(255, 255, 255, .03);
  }

  #account_logins_md .lbx-modal__headLeft {
    display: flex;
    gap: 10px;
    align-items: center;
  }

  #account_logins_md .lbx-modal__icon {
    width: 38px;
    height: 38px;
    border-radius: 12px;
    display: grid;
    place-items: center;
    background: rgba(255, 255, 255, .06);
    border: 1px solid rgba(255, 255, 255, .10);
  }

  #account_logins_md .lbx-modal__title {
    font-weight: 900;
    font-size: 1.12rem;
    line-height: 1.15;
  }

  #account_logins_md .lbx-modal__sub {
    margin-top: 3px;
    opacity: .72;
    font-size: .92rem;
  }

  #account_logins_md .lbx-modal__close {
    width: 38px;
    height: 38px;
    border-radius: 12px;
    display: grid;
    place-items: center;
    background: rgba(255, 255, 255, .04);
    border: 1px solid rgba(255, 255, 255, .10);
    color: rgba(255, 255, 255, .85);
  }

  #account_logins_md .lbx-modal__close:hover {
    background: rgba(255, 255, 255, .07);
  }

  #account_logins_md .lbx-modal__body {
    padding: 12px 14px;
  }

  #account_logins_md .lb-field {
    margin-bottom: 10px;
  }

  #account_logins_md .lb-field-label {
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

  #account_logins_md .lb-field-label .lb-ico {
    width: 34px;
    height: 34px;
    border-radius: 12px;
    display: grid;
    place-items: center;
    background: rgba(255, 255, 255, .04);
    border: 1px solid rgba(255, 255, 255, .10);
  }

  #account_logins_md .lb-input {
    width: 100%;
    min-height: 44px;
    border-radius: 12px;
    padding: 10px 13px;
    background: rgba(0, 0, 0, .18);
    border: 1px solid rgba(255, 255, 255, .10);
    color: rgba(255, 255, 255, .92);
    outline: none;
  }

  #account_logins_md .lb-input::placeholder {
    opacity: .55;
  }

  #account_logins_md .lb-input:focus {
    border-color: rgba(177, 140, 255, .35);
    box-shadow: 0 0 0 .25rem rgba(177, 140, 255, .15);
  }

  #account_logins_md .lb-row {
    display: grid;
    grid-template-columns: 1fr;
    gap: 10px;
  }

  #account_logins_md .lb-riot-format-note {
    display: flex;
    gap: 6px;
    align-items: center;
    margin-top: 6px;
    padding: 0 2px;
    border: 0;
    background: transparent;
    color: rgba(255,255,255,.58);
    font-size: .72rem;
    line-height: 1.3;
  }

  #account_logins_md .lb-riot-format-note i {
    color: #ffc44d;
    margin-top: 0;
    font-size: .7rem;
  }

  #account_logins_md .lb-riot-inline-error {
    margin-top: 8px;
    color: #ff6b6b;
    font-size: .82rem;
    font-weight: 800;
  }
  #account_logins_md .lb-riot-suggestions {
    margin-top: 7px;
    overflow: hidden;
    border: 1px solid rgba(255,255,255,.12);
    border-radius: 12px;
    background: #202426;
    box-shadow: 0 14px 32px rgba(0,0,0,.28);
  }
  #account_logins_md .lb-riot-suggestions[hidden] { display: none !important; }
  #account_logins_md .lb-riot-suggestion {
    width: 100%;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    padding: 10px 12px;
    border: 0;
    border-bottom: 1px solid rgba(255,255,255,.07);
    background: transparent;
    color: rgba(255,255,255,.88);
    text-align: left;
  }
  #account_logins_md .lb-riot-suggestion:last-child { border-bottom: 0; }
  #account_logins_md .lb-riot-suggestion:hover,
  #account_logins_md .lb-riot-suggestion:focus {
    outline: none;
    background: rgba(99,102,241,.14);
  }
  #account_logins_md .lb-riot-suggestion__name {
    min-width: 0;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
    font-size: .86rem;
    font-weight: 850;
  }
  #account_logins_md .lb-riot-suggestion__server {
    flex: 0 0 auto;
    color: #a5b4fc;
    font-size: .68rem;
    font-weight: 900;
    letter-spacing: .05em;
  }

  #account_logins_md .lb-input.is-invalid {
    border-color: rgba(255, 107, 107, .55);
    box-shadow: 0 0 0 .22rem rgba(255, 107, 107, .12);
  }

  #account_logins_md .lb-riot-preview {
    display: flex;
    gap: 9px;
    align-items: center;
    margin-top: 8px;
    padding: 8px 10px;
    border-radius: 12px;
    background: rgba(255,255,255,.04);
    border: 1px solid rgba(255,255,255,.09);
  }

  #account_logins_md .lb-riot-preview[hidden] { display: none !important; }

  #account_logins_md .lb-riot-preview__avatar {
    width: 38px;
    height: 38px;
    border-radius: 50%;
    overflow: hidden;
    display: grid;
    place-items: center;
    flex: 0 0 auto;
    background: rgba(0,0,0,.20);
    border: 1px solid rgba(255,255,255,.14);
  }

  #account_logins_md .lb-riot-preview__avatar img {
    width: 100%;
    height: 100%;
    object-fit: cover;
  }

  #account_logins_md .lb-riot-preview__avatar i {
    width: 100%;
    height: 100%;
    display: grid;
    place-items: center;
    color: rgba(255,255,255,.55);
  }

  #account_logins_md .lb-riot-preview__body { min-width: 0; flex: 1; display: grid; grid-template-columns: minmax(0,1fr) auto; align-items: center; column-gap: 10px; }
  #account_logins_md .lb-riot-preview__label { grid-column: 1; font-size: .61rem; text-transform: uppercase; letter-spacing: .07em; font-weight: 900; opacity: .50; }
  #account_logins_md .lb-riot-preview__name { grid-column: 1; margin-top: 0; font-weight: 950; font-size: .88rem; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
  #account_logins_md .lb-riot-preview__meta { grid-column: 1; margin-top: 0; font-size: .70rem; opacity: .65; }
  #account_logins_md .lb-riot-preview.is-found { border-color: rgba(31,230,198,.25); background: rgba(31,230,198,.07); }
  #account_logins_md .lb-riot-preview.is-error { border-color: rgba(255,107,107,.24); background: rgba(255,107,107,.08); }
  #account_logins_md .lb-riot-confirm { grid-column: 2; grid-row: 1 / 4; margin-top: 0; display: inline-flex; align-items: center; justify-content: center; border: 0; border-radius: 999px; padding: 6px 9px; font-size: .67rem; font-weight: 900; color: #061e1a; background: linear-gradient(135deg, #1fe6c6, #7ef5e0); box-shadow: 0 8px 18px rgba(31,230,198,.14); white-space: nowrap; }
  #account_logins_md .lb-riot-confirm[hidden] { display: none !important; }
  #account_logins_md .lb-riot-confirm.is-confirmed { color: #d8fff8; background: rgba(31,230,198,.16); border: 1px solid rgba(31,230,198,.35); box-shadow: none; }
  #account_logins_md .lbx-modal__btn.is-disabled { opacity: .55; cursor: not-allowed; }
  #account_logins_md .lb-riot-preview.is-loading .lb-riot-preview__avatar { animation: lbRiotPulse 1s ease-in-out infinite; }

  @keyframes lbRiotPulse { 0%,100%{opacity:.65} 50%{opacity:1} }

  
  /* Account Options inside Edit Logins modal */
  #account_logins_md .lb-accopt{
    margin-top: 10px;
    padding-top: 10px;
    border-top: 1px solid rgba(255,255,255,.06);
  }
  #account_logins_md .lb-accopt__title{
    font-weight: 900;
    letter-spacing: .02em;
    text-transform: uppercase;
    font-size: .78rem;
    opacity: .75;
    margin-bottom: 5px;
  }
  #account_logins_md .lb-opt-row--compact{
    padding: 7px 0;
    border-top: 1px solid rgba(255,255,255,.06);
  }
  #account_logins_md .lb-opt-row--compact:first-of-type{
    border-top: 0;
  }
  #account_logins_md .lb-opt-sub{ opacity:.65; font-size:.82rem; }
  #account_logins_md .lb-opt-ico{ width:34px; height:34px; border-radius:12px; }
  #account_logins_md .lb-seg{ transform: scale(.95); transform-origin: right center; }
  #account_logins_md .lb-hint {
    margin-top: 6px;
    padding: 7px 10px;
    gap: 8px;
    border-radius: 11px;
    align-items: center;
  }
  #account_logins_md .lb-hint i {
    margin-top: 0;
    font-size: .85rem !important;
  }
  #account_logins_md .lb-hint .small {
    font-size: .70rem;
    line-height: 1.25;
  }

@media(min-width:992px) {
    #account_logins_md .modal-dialog {
      max-width: 720px;
    }

    #account_logins_md .lb-row.lb-row--2 {
      grid-template-columns: 1fr 1fr;
    }
  }

  #account_logins_md .lbx-modal__footer {
    padding: 10px 14px;
    display: flex;
    justify-content: flex-end;
    gap: 10px;
    border-top: 1px solid rgba(255, 255, 255, .08);
    background: rgba(255, 255, 255, .02);
  }

  #account_logins_md .lbx-modal__btn {
    border-radius: 999px;
    padding: .60rem 1.05rem;
    font-weight: 900;
    border: 1px solid rgba(255, 255, 255, .10);
    background: rgba(255, 255, 255, .04);
    color: rgba(255, 255, 255, .92);
  }

  #account_logins_md .lbx-modal__btn:hover {
    background: rgba(255, 255, 255, .06);
    border-color: rgba(255, 255, 255, .16);
    color: #fff;
  }

  #account_logins_md .lbx-modal__btn--ghost {
    background: transparent;
  }

  #account_logins_md .lbx-modal__btn--primary {
    background: rgba(88, 101, 242, .85);
    border-color: rgba(88, 101, 242, .35);
  }

  #account_logins_md .lbx-modal__btn--primary:hover {
    background: rgba(88, 101, 242, .95);
  }

  /* Trigger button */
  .lb-acc-trigger {
    border-radius: 999px !important;
    padding: .55rem .95rem !important;
    font-weight: 900;
    background: rgba(255, 255, 255, .04) !important;
    border-color: rgba(255, 255, 255, .10) !important;
    color: rgba(255, 255, 255, .92) !important;
    box-shadow: 0 10px 30px rgba(0, 0, 0, .25);
  }

  .lb-acc-trigger:hover {
    transform: translateY(-1px);
    background: rgba(255, 255, 255, .06) !important;
    border-color: rgba(255, 255, 255, .16) !important;
    color: #fff !important;
  }

  @media (max-width:575.98px) {
    .lb-acc-trigger {
      padding: .55rem .75rem !important;
    }
  }

  /* Account summary */
  .lb-acc-summary {
    display: flex;
    align-items: center;
    gap: .85rem;
    padding: .95rem 1rem;
    border-radius: 1rem;
    border: 1px solid rgba(255, 255, 255, .08);
    background: rgba(255, 255, 255, .03);
  }

  .lb-acc-summary__icon {
    width: 44px;
    height: 44px;
    border-radius: 14px;
    display: grid;
    place-items: center;
    border: 1px solid rgba(255, 255, 255, .10);
    background: rgba(255, 255, 255, .04);
    flex: 0 0 auto;
  }

  .lb-acc-summary__text {
    min-width: 0;
    flex: 1;
  }

  .lb-acc-summary__title {
    font-weight: 900;
    letter-spacing: .06em;
    text-transform: uppercase;
    font-size: .78rem;
    opacity: .85;
  }

  .lb-acc-summary__sub {
    margin-top: .25rem;
    font-size: .92rem;
    opacity: .72;
  }

  .lb-acc-summary__riot {
    margin-top: .45rem;
    display: inline-flex;
    gap: .45rem;
    align-items: center;
    max-width: 100%;
    padding: .28rem .55rem;
    border-radius: 999px;
    background: rgba(255,255,255,.04);
    border: 1px solid rgba(255,255,255,.08);
    font-size: .76rem;
  }

  .lb-acc-summary__riot-label {
    opacity: .55;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: .05em;
  }

  .lb-acc-summary__riot-value {
    min-width: 0;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
    font-weight: 900;
  }

  .lb-acc-summary__badge {
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

  .lb-acc-summary.is-saved {
    border-color: rgba(31, 230, 198, .20);
  }

  .lb-acc-summary.is-saved .lb-acc-summary__badge {
    color: #1fe6c6;
    border-color: rgba(31, 230, 198, .25);
    background: rgba(31, 230, 198, .10);
  }

  .lb-acc-summary.is-missing {
    border-color: rgba(255, 196, 77, .18);
  }

  .lb-acc-summary.is-missing .lb-acc-summary__badge {
    color: #ffc44d;
    border-color: rgba(255, 196, 77, .22);
    background: rgba(255, 196, 77, .10);
  }

  @media (max-width:575.98px) {
    .lb-acc-summary {
      flex-direction: column;
      align-items: center;
      text-align: center;
    }
  }

  /* Mobile: icon-only notify booster button */
  @media (max-width:575.98px) {
    .btn-notify-booster span {
      display: none;
    }

    .btn-notify-booster {
      padding: .55rem .70rem;
      border-radius: 12px;
    }
  }

  /* =========================
   PAUSE MODAL (premium)
========================= */
  .lb-modal--pause .modal-dialog {
    max-width: 560px;
  }

  .lb-modal--pause .modal-content {
    border-radius: 1.25rem;
    background: #25282A;
    border: 1px solid rgba(255, 255, 255, .08);
    box-shadow: 0 30px 90px rgba(0, 0, 0, .65);
    overflow: hidden;
  }

  /* header */
  .lb-modal--pause .modal-header {
    background: rgba(255, 255, 255, .03);
    border-bottom: 1px solid rgba(255, 255, 255, .08);
    padding: 1rem 1.1rem;
  }

  .lb-modal--pause .lb-modal-head {
    display: flex;
    align-items: flex-start;
    gap: .85rem;
  }

  .lb-modal--pause .lb-modal-ico {
    width: 46px;
    height: 46px;
    border-radius: 16px;
    display: grid;
    place-items: center;
    background: rgba(255, 196, 77, .10);
    border: 1px solid rgba(255, 196, 77, .22);
    color: #ffc44d;
    flex: 0 0 auto;
  }

  .lb-modal--pause .lb-modal-title {
    margin: 0;
    font-weight: 950;
    font-size: 1.05rem;
    line-height: 1.2;
  }

  .lb-modal--pause .lb-modal-sub {
    margin: .25rem 0 0;
    opacity: .72;
    font-size: .9rem;
  }

  /* body */
  .lb-modal--pause .modal-body {
    padding: 1rem 1.1rem;
  }

  .lb-modal--pause .lb-modal-warning {
    display: flex;
    gap: .85rem;
    align-items: flex-start;
    padding: .9rem 1rem;
    border-radius: 1rem;
    background: rgba(255, 196, 77, .10);
    border: 1px solid rgba(255, 196, 77, .20);
  }

  .lb-modal--pause .lb-modal-warning i {
    margin-top: .1rem;
    color: #ffc44d;
    font-size: 1.1rem;
  }

  .lb-modal--pause .lb-modal-warning-title {
    font-weight: 950;
    margin-bottom: .15rem;
  }

  .lb-modal--pause .lb-modal-warning-sub {
    opacity: .8;
    font-size: .92rem;
    line-height: 1.35;
  }

  /* footer */
  .lb-modal--pause .modal-footer {
    background: rgba(255, 255, 255, .02);
    border-top: 1px solid rgba(255, 255, 255, .08);
    padding: .9rem 1.1rem;
    gap: .6rem;
  }

  /* buttons */
  .lb-modal--pause .lb-btn {
    border-radius: 999px;
    font-weight: 950;
    padding: .60rem 1.05rem;
    border: 1px solid rgba(255, 255, 255, .10);
    background: rgba(255, 255, 255, .04);
    color: rgba(255, 255, 255, .92);
  }

  .lb-modal--pause .lb-btn:hover {
    background: rgba(255, 255, 255, .06);
    border-color: rgba(255, 255, 255, .16);
    color: #fff;
    transform: translateY(-1px);
  }

  .lb-modal--pause .lb-btn-ghost {
    background: transparent;
  }

  .lb-modal--pause .lb-btn-danger {
    background: rgba(255, 107, 107, .12);
    border-color: rgba(255, 107, 107, .25);
    color: #ffb1b1;
  }

  .lb-modal--pause .lb-btn-danger:hover {
    background: rgba(255, 107, 107, .18);
    border-color: rgba(255, 107, 107, .35);
    color: #fff;
  }

  /* mobile */
  @media (max-width:575.98px) {
    .lb-modal--pause .modal-dialog {
      max-width: calc(100% - 1.25rem);
    }

    .lb-modal--pause .modal-footer {
      flex-direction: column;
      align-items: stretch;
    }

    .lb-modal--pause .lb-btn {
      width: 100%;
      justify-content: center;
    }
  }

  /* =========================
   RESUME MODAL (premium)
========================= */
  .lb-modal--resume .modal-dialog {
    max-width: 560px;
  }

  .lb-modal--resume .modal-content {
    border-radius: 1.25rem;
    background: #25282A;
    border: 1px solid rgba(255, 255, 255, .08);
    box-shadow: 0 30px 90px rgba(0, 0, 0, .65);
    overflow: hidden;
  }

  /* header */
  .lb-modal--resume .modal-header {
    background: rgba(255, 255, 255, .03);
    border-bottom: 1px solid rgba(255, 255, 255, .08);
    padding: 1rem 1.1rem;
  }

  .lb-modal--resume .lb-modal-head {
    display: flex;
    align-items: flex-start;
    gap: .85rem;
  }

  .lb-modal--resume .lb-modal-ico {
    width: 46px;
    height: 46px;
    border-radius: 16px;
    display: grid;
    place-items: center;
    background: rgba(31, 230, 198, .10);
    border: 1px solid rgba(31, 230, 198, .22);
    color: #1fe6c6;
    flex: 0 0 auto;
  }

  .lb-modal--resume .lb-modal-title {
    margin: 0;
    font-weight: 950;
    font-size: 1.05rem;
    line-height: 1.2;
  }

  .lb-modal--resume .lb-modal-sub {
    margin: .25rem 0 0;
    opacity: .72;
    font-size: .9rem;
  }

  /* body */
  .lb-modal--resume .modal-body {
    padding: 1rem 1.1rem;
  }

  .lb-modal--resume .lb-modal-info {
    display: flex;
    gap: .85rem;
    align-items: flex-start;
    padding: .9rem 1rem;
    border-radius: 1rem;
    background: rgba(31, 230, 198, .10);
    border: 1px solid rgba(31, 230, 198, .20);
  }

  .lb-modal--resume .lb-modal-info i {
    margin-top: .1rem;
    color: #1fe6c6;
    font-size: 1.1rem;
  }

  .lb-modal--resume .lb-modal-info-title {
    font-weight: 950;
    margin-bottom: .15rem;
  }

  .lb-modal--resume .lb-modal-info-sub {
    opacity: .82;
    font-size: .92rem;
    line-height: 1.35;
  }

  /* footer */
  .lb-modal--resume .modal-footer {
    background: rgba(255, 255, 255, .02);
    border-top: 1px solid rgba(255, 255, 255, .08);
    padding: .9rem 1.1rem;
    gap: .6rem;
  }

  /* buttons */
  .lb-modal--resume .lb-btn {
    border-radius: 999px;
    font-weight: 950;
    padding: .60rem 1.05rem;
    border: 1px solid rgba(255, 255, 255, .10);
    background: rgba(255, 255, 255, .04);
    color: rgba(255, 255, 255, .92);
  }

  .lb-modal--resume .lb-btn:hover {
    background: rgba(255, 255, 255, .06);
    border-color: rgba(255, 255, 255, .16);
    color: #fff;
    transform: translateY(-1px);
  }

  .lb-modal--resume .lb-btn-ghost {
    background: transparent;
  }

  .lb-modal--resume .lb-btn-success {
    background: rgba(31, 230, 198, .12);
    border-color: rgba(31, 230, 198, .25);
    color: #bff7ee;
  }

  .lb-modal--resume .lb-btn-success:hover {
    background: rgba(31, 230, 198, .18);
    border-color: rgba(31, 230, 198, .35);
    color: #fff;
  }

  /* mobile */
  @media (max-width:575.98px) {
    .lb-modal--resume .modal-dialog {
      max-width: calc(100% - 1.25rem);
    }

    .lb-modal--resume .modal-footer {
      flex-direction: column;
      align-items: stretch;
    }

    .lb-modal--resume .lb-btn {
      width: 100%;
      justify-content: center;
    }
  }

  /* ==============================================
     LBAP — Customize Order Modal Design System
  ============================================== */

  /* Modal shell — scoped to addon modal, mirrors account_logins_md */
  #addon_payment_md .lbx-modal__content {
    background: #25282A !important;
    border: 1px solid rgba(255,255,255,.10) !important;
    border-radius: 18px !important;
    overflow: hidden !important;
    color: rgba(255,255,255,.92) !important;
    box-shadow: 0 30px 90px rgba(0,0,0,.65);
  }

  #addon_payment_md .lbx-modal__header {
    padding: 16px 16px 12px;
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    border-bottom: 1px solid rgba(255,255,255,.08);
    background: rgba(255,255,255,.03);
  }

  #addon_payment_md .lbx-modal__headLeft {
    display: flex;
    gap: 12px;
    align-items: flex-start;
  }

  #addon_payment_md .lbx-modal__icon {
    width: 44px;
    height: 44px;
    border-radius: 14px;
    display: grid;
    place-items: center;
    background: rgba(255,255,255,.06);
    border: 1px solid rgba(255,255,255,.10);
    font-size: 1.1rem;
    color: rgba(255,255,255,.75);
    flex-shrink: 0;
  }

  #addon_payment_md .lbx-modal__title {
    font-weight: 900;
    font-size: 1.25rem;
    line-height: 1.15;
  }

  #addon_payment_md .lbx-modal__sub {
    margin-top: 6px;
    opacity: .72;
    font-size: .92rem;
  }

  #addon_payment_md .lbx-modal__close {
    width: 42px;
    height: 42px;
    border-radius: 14px;
    display: grid;
    place-items: center;
    background: rgba(255,255,255,.04);
    border: 1px solid rgba(255,255,255,.10);
    color: rgba(255,255,255,.85);
    cursor: pointer;
    transition: background .15s;
  }

  #addon_payment_md .lbx-modal__close:hover {
    background: rgba(255,255,255,.07);
  }

  #addon_payment_md .lbx-modal__body {
    padding: 16px;
  }

  @media (min-width: 768px) {
    #addon_payment_md .modal-dialog {
      max-width: 860px;
    }
    #addon_payment_md .lbap-addons {
      max-height: 440px;
    }
  }

  #addon_payment_md .lbx-modal__footer {
    padding: 14px 16px;
    display: flex;
    justify-content: space-between;
    gap: 10px;
    border-top: 1px solid rgba(255,255,255,.08);
    background: rgba(255,255,255,.02);
  }

  #addon_payment_md .lbx-modal__btn {
    border-radius: 999px;
    padding: .60rem 1.2rem;
    font-weight: 900;
    border: 1px solid rgba(255,255,255,.10);
    background: rgba(255,255,255,.04);
    color: rgba(255,255,255,.92);
    display: inline-flex;
    align-items: center;
    gap: .35rem;
    cursor: pointer;
    transition: background .15s, border-color .15s;
  }

  #addon_payment_md .lbx-modal__btn:hover {
    background: rgba(255,255,255,.08);
    border-color: rgba(255,255,255,.18);
    color: #fff;
  }

  #addon_payment_md .lbx-modal__btn--ghost {
    background: transparent;
  }

  #addon_payment_md .lbx-modal__btn--primary {
    background: rgba(88,101,242,.85);
    border-color: rgba(88,101,242,.35);
    color: #fff;
  }

  #addon_payment_md .lbx-modal__btn--primary:hover {
    background: rgba(88,101,242,.95);
  }

  /* Input fields inside addon modal */
  #addon_payment_md .lb-input {
    width: 100%;
    min-height: 48px;
    border-radius: 14px;
    padding: 12px 14px;
    background: rgba(0,0,0,.18);
    border: 1px solid rgba(255,255,255,.10);
    color: rgba(255,255,255,.92);
    outline: none;
  }

  #addon_payment_md .lb-input::placeholder { opacity: .55; }

  #addon_payment_md .lb-input:focus {
    border-color: rgba(177,140,255,.35);
    box-shadow: 0 0 0 .25rem rgba(177,140,255,.15);
  }

  /* Tab bar */
  .lbap-tabs {
    display: flex;
    gap: .4rem;
    padding: .75rem 1.35rem .55rem;
    background: transparent;
  }

  .lbap-tab {
    flex: 1;
    padding: .45rem .9rem;
    border-radius: .65rem;
    border: 1px solid rgba(255,255,255,.12);
    background: rgba(255,255,255,.03);
    color: rgba(255,255,255,.45);
    font-size: .8375rem;
    font-weight: 500;
    cursor: pointer;
    transition: background .18s, color .18s, border-color .18s;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: .35rem;
  }

  .lbap-tab:hover {
    background: rgba(88,101,242,.10);
    color: #a5b4fc;
    border-color: rgba(88,101,242,.40);
  }

  .lbap-tab.active {
    background: rgba(88,101,242,.20);
    border-color: rgba(88,101,242,.55);
    color: #a5b4fc;
  }

  /* Tab panels */
  .lbap-tab-panel { display: none; }
  .lbap-tab-panel.active { display: block; }

  /* Body */
  .lbap-body {
    padding: .85rem 1.35rem 1rem;
  }

  /* Two-column layout */
  .lbap-two-col {
    display: grid;
    grid-template-columns: 1fr 200px;
    gap: 1rem;
    align-items: start;
  }

  @media (max-width: 640px) {
    .lbap-two-col { grid-template-columns: 1fr; }
  }

  /* Addon list — scrollable */
  .lbap-addons {
    display: flex;
    flex-direction: column;
    gap: .4rem;
    max-height: 320px;
    overflow-y: auto;
    padding-right: 3px;
  }

  .lbap-addons::-webkit-scrollbar { width: 3px; }
  .lbap-addons::-webkit-scrollbar-track { background: transparent; }
  .lbap-addons::-webkit-scrollbar-thumb { background: rgba(255,255,255,.12); border-radius: 99px; }

  /* Addon card */
  .lbap-addon-card {
    display: flex;
    align-items: center;
    gap: .85rem;
    padding: .75rem .9rem;
    border-radius: .75rem;
    border: 1px solid rgba(255,255,255,.07);
    background: rgba(255,255,255,.03);
    cursor: pointer;
    transition: background .15s, border-color .15s;
    user-select: none;
  }

  .lbap-addon-card:hover {
    background: rgba(255,255,255,.055);
    border-color: rgba(255,255,255,.11);
  }

  .lbap-addon-card:has(.lbap-switch__input:checked) {
    background: rgba(88,101,242,.10);
    border-color: rgba(88,101,242,.45);
  }

  .lbap-addon-card__icon {
    width: 34px;
    height: 34px;
    border-radius: 12px;
    display: grid;
    place-items: center;
    flex: 0 0 34px;
    background: rgba(255,255,255,.045);
    border: 1px solid rgba(255,255,255,.08);
  }

  .lbap-addon-card__icon img {
    width: 18px;
    height: 18px;
    display: block;
    object-fit: contain;
  }

  .lbap-addon-card__icon--fa i {
    color: #fff;
    font-size: .98rem;
    line-height: 1;
  }

  .lb-opt-ico--addon .lbap-addon-card__icon {
    width: 100%;
    height: 100%;
    flex-basis: auto;
    border: 0;
    background: transparent;
  }

  .lbap-addon-card__info { flex: 1; min-width: 0; }

  .lbap-addon-card__name {
    font-weight: 600;
    color: rgba(255,255,255,.9);
    font-size: .875rem;
    margin-bottom: 2px;
  }

  .lbap-addon-card__desc {
    font-size: .78rem;
    color: rgba(255,255,255,.4);
    line-height: 1.4;
  }

  .lbap-addon-card__right {
    display: flex;
    flex-direction: column;
    align-items: flex-end;
    gap: .35rem;
    flex-shrink: 0;
  }

  .lbap-addon-card__price {
    font-size: .8125rem;
    font-weight: 600;
    color: rgba(255,255,255,.65);
    background: rgba(255,255,255,.07);
    border-radius: .45rem;
    padding: .18rem .5rem;
    white-space: nowrap;
    border: 1px solid rgba(255,255,255,.08);
  }

  .lbap-addon-card:has(.lbap-switch__input:checked) .lbap-addon-card__price {
    background: rgba(88,101,242,.25);
    color: #c7d2fe;
    border-color: rgba(88,101,242,.40);
  }

  /* Toggle switch — indigo when active */
  .lbap-switch {
    position: relative;
    width: 34px;
    height: 19px;
    flex-shrink: 0;
  }

  .lbap-switch__input {
    position: absolute;
    opacity: 0;
    width: 0; height: 0;
    pointer-events: none;
  }

  .lbap-switch__track {
    display: block;
    width: 34px;
    height: 19px;
    border-radius: 99px;
    background: rgba(255,255,255,.10);
    border: 1px solid rgba(255,255,255,.12);
    position: relative;
    transition: background .2s, border-color .2s;
  }

  .lbap-switch__track::after {
    content: '';
    position: absolute;
    top: 2px; left: 2px;
    width: 13px; height: 13px;
    border-radius: 50%;
    background: rgba(255,255,255,.4);
    transition: transform .2s, background .2s;
  }

  .lbap-switch__input:checked ~ .lbap-switch__track {
    background: rgba(88,101,242,.85);
    border-color: rgba(88,101,242,.60);
  }

  .lbap-switch__input:checked ~ .lbap-switch__track::after {
    transform: translateX(15px);
    background: #fff;
  }

  /* Empty state */
  .lbap-empty {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: .5rem;
    padding: 2rem;
    color: rgba(255,255,255,.3);
    font-size: .875rem;
    text-align: center;
  }

  .lbap-empty i { font-size: 1.6rem; opacity: .35; }

  /* Custom fields */
  .lbap-custom-fields {
    display: flex;
    flex-direction: column;
    gap: .65rem;
    padding: .15rem 0;
  }

  .lbap-field__label {
    display: block;
    font-size: .75rem;
    font-weight: 500;
    color: rgba(255,255,255,.4);
    margin-bottom: .3rem;
    text-transform: uppercase;
    letter-spacing: .05em;
  }

  .lbap-field__input {
    width: 100%;
    background: rgba(255,255,255,.04);
    border: 1px solid rgba(255,255,255,.09);
    border-radius: .65rem;
    color: rgba(255,255,255,.88);
    font-size: .9375rem;
    padding: .6rem .85rem;
    outline: none;
    transition: border-color .15s, background .15s;
  }

  .lbap-field__input::placeholder { color: rgba(255,255,255,.25); }

  .lbap-field__input:focus {
    border-color: rgba(255,255,255,.22);
    background: rgba(255,255,255,.06);
  }

  .lbap-field__amount-wrap { position: relative; }

  .lbap-field__currency {
    position: absolute;
    left: .85rem;
    top: 50%;
    transform: translateY(-50%);
    color: rgba(255,255,255,.35);
    font-size: .9375rem;
    pointer-events: none;
  }

  .lbap-field__input--amount {
    padding-left: 1.7rem;
    font-size: 1.05rem;
    font-weight: 600;
  }

  /* Summary box */
  .lbap-summary {
    display: flex;
    flex-direction: column;
    gap: .65rem;
    position: sticky;
    top: 0;
  }

  .lbap-summary__box {
    background: linear-gradient(135deg, rgba(52,211,153,.07), rgba(16,185,129,.04));
    border: 1px solid rgba(52,211,153,.20);
    border-radius: .85rem;
    padding: .9rem 1rem;
  }

  .lbap-summary__label {
    font-size: .7rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .08em;
    color: rgba(52,211,153,.70);
    margin-bottom: .4rem;
  }

  .lbap-summary__total {
    font-size: 1.55rem;
    font-weight: 700;
    color: #34d399;
    display: flex;
    align-items: baseline;
    gap: .15rem;
    margin-bottom: .65rem;
    line-height: 1;
  }

  .lbap-summary__currency {
    font-size: .95rem;
    font-weight: 500;
    opacity: .7;
  }

  .lbap-summary__notes {
    display: flex;
    flex-direction: column;
    gap: .28rem;
    padding-top: .55rem;
    border-top: 1px solid rgba(52,211,153,.12);
  }

  .lbap-summary__note {
    font-size: .77rem;
    color: rgba(255,255,255,.45);
    display: flex;
    align-items: center;
    gap: .35rem;
  }

  .lbap-summary__note i { color: #34d399; font-size: .68rem; }

  .lbap-summary__hint {
    font-size: .75rem;
    color: rgba(255,255,255,.28);
    line-height: 1.55;
    padding: 0 .1rem;
  }

  /* Section label */
  .lbap-section-label {
    font-size: .72rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .08em;
    color: rgba(255,255,255,.45);
    margin-bottom: .55rem;
  }

  /* Payment list */
  .lbap-pay-list {
    display: flex;
    flex-direction: column;
    gap: .4rem;
    list-style: none;
    padding: 0; margin: 0;
  }

  .lbap-pay-item {
    display: flex;
    align-items: center;
    gap: .8rem;
    padding: .75rem .9rem;
    border-radius: .75rem;
    border: 1px solid rgba(255,255,255,.07);
    background: rgba(255,255,255,.03);
    cursor: pointer;
    transition: background .15s, border-color .15s;
    width: 100%;
  }

  /* Stripe hover */
  .lbap-pay-item[data-method="stripe"]:hover,
  .lbap-pay-item:nth-child(1):hover { border-color: rgba(99,102,241,.35); background: rgba(99,102,241,.07); }
  /* PayPal hover */
  .lbap-pay-item[data-method="paypal"]:hover,
  .lbap-pay-item:nth-child(2):hover { border-color: rgba(0,112,243,.35); background: rgba(0,112,243,.07); }
  .lbap-pay-item input[type="radio"] {
    position: absolute;
    opacity: 0;
    pointer-events: none;
  }

  .lbap-pay-item--disabled {
    cursor: not-allowed;
    opacity: .45;
    pointer-events: none;
  }

  .lbap-pay-item__icon {
    width: 36px;
    height: 36px;
    border-radius: .6rem;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.05rem;
    flex-shrink: 0;
    transition: background .15s, border-color .15s, color .15s;
  }

  /* Per-method icon colors */
  .lbap-pay-list li:nth-child(1) .lbap-pay-item__icon { background: rgba(99,102,241,.15); border: 1px solid rgba(99,102,241,.25); color: #818cf8; }
  .lbap-pay-list li:nth-child(2) .lbap-pay-item__icon { background: rgba(0,112,243,.15); border: 1px solid rgba(0,112,243,.25); color: #60a5fa; }
  .lbap-pay-list li:nth-child(3) .lbap-pay-item__icon { background: rgba(255,255,255,.06); border: 1px solid rgba(255,255,255,.08); color: rgba(255,255,255,.35); }

  .lbap-pay-item__info { flex: 1; }

  .lbap-pay-item__name {
    font-weight: 600;
    color: rgba(255,255,255,.88);
    font-size: .875rem;
  }

  .lbap-pay-item__sub {
    font-size: .775rem;
    color: rgba(255,255,255,.38);
    margin-top: 1px;
  }

  .lbap-pay-badge {
    font-size: .7rem;
    font-weight: 600;
    padding: .18rem .5rem;
    border-radius: .4rem;
    white-space: nowrap;
    flex-shrink: 0;
  }

  /* Per-method badge colors */
  .lbap-pay-list li:nth-child(1) .lbap-pay-badge { background: rgba(99,102,241,.18); border: 1px solid rgba(99,102,241,.30); color: #a5b4fc; }
  .lbap-pay-list li:nth-child(2) .lbap-pay-badge { background: rgba(0,112,243,.15); border: 1px solid rgba(0,112,243,.25); color: #93c5fd; }
  .lbap-pay-list li:nth-child(3) .lbap-pay-badge { background: rgba(255,255,255,.06); border: 1px solid rgba(255,255,255,.08); color: rgba(255,255,255,.35); }

  /* Selected: per-method colored border */
  .lbap-pay-list li:nth-child(1) label.lbap-pay-item:has(input[type="radio"]:checked) {
    background: rgba(99,102,241,.12); border-color: rgba(99,102,241,.50);
  }
  .lbap-pay-list li:nth-child(2) label.lbap-pay-item:has(input[type="radio"]:checked) {
    background: rgba(0,112,243,.12); border-color: rgba(0,112,243,.45);
  }
  label.lbap-pay-item:has(input[type="radio"]:checked) .lbap-pay-item__name { color: #fff; }

  label.lbap-pay-item:has(input[type="radio"]:checked) .lbap-pay-item__icon {
    filter: brightness(1.2);
  }

  /* Footer + buttons now use lbx-modal__footer / lbx-modal__btn (see #addon_payment_md scoped rules above) */

  /* Light theme overrides */
  [data-theme="light"] .lbap-modal {
    background: #fff;
    border-color: rgba(0,0,0,.08);
  }

  [data-theme="light"] .lbap-header { border-color: rgba(0,0,0,.07); }
  [data-theme="light"] .lbap-header__icon { background: rgba(0,0,0,.05); border-color: rgba(0,0,0,.09); color: rgba(0,0,0,.6); }
  [data-theme="light"] .lbap-header__title { color: #111; }
  [data-theme="light"] .lbap-header__sub { color: rgba(0,0,0,.45); }
  [data-theme="light"] .lbap-close { background: rgba(0,0,0,.04); border-color: rgba(0,0,0,.09); color: rgba(0,0,0,.45); }
  [data-theme="light"] .lbap-close:hover { background: rgba(0,0,0,.08); color: rgba(0,0,0,.8); }

  [data-theme="light"] .lbap-tabs { background: transparent; }
  [data-theme="light"] .lbap-tab { background: rgba(0,0,0,.03); border-color: rgba(0,0,0,.07); color: rgba(0,0,0,.45); }
  [data-theme="light"] .lbap-tab:hover { background: rgba(0,0,0,.06); color: rgba(0,0,0,.7); }
  [data-theme="light"] .lbap-tab.active { background: rgba(0,0,0,.07); border-color: rgba(0,0,0,.16); color: rgba(0,0,0,.88); }

  [data-theme="light"] .lbap-addon-card { background: rgba(0,0,0,.02); border-color: rgba(0,0,0,.07); }
  [data-theme="light"] .lbap-addon-card:hover { background: rgba(0,0,0,.04); border-color: rgba(0,0,0,.11); }
  [data-theme="light"] .lbap-addon-card:has(.lbap-switch__input:checked) { background: rgba(0,0,0,.05); border-color: rgba(0,0,0,.18); }
  [data-theme="light"] .lbap-addon-card__icon { background: rgba(0,0,0,.035); border-color: rgba(0,0,0,.07); }
  [data-theme="light"] .lbap-addon-card__icon--fa i { color: rgba(0,0,0,.82); }
  [data-theme="light"] .lbap-addon-card__name { color: rgba(0,0,0,.85); }
  [data-theme="light"] .lbap-addon-card__desc { color: rgba(0,0,0,.45); }
  [data-theme="light"] .lbap-addon-card__price { background: rgba(0,0,0,.05); border-color: rgba(0,0,0,.08); color: rgba(0,0,0,.65); }

  [data-theme="light"] .lbap-switch__track { background: rgba(0,0,0,.10); border-color: rgba(0,0,0,.12); }
  [data-theme="light"] .lbap-switch__track::after { background: rgba(0,0,0,.4); }
  [data-theme="light"] .lbap-switch__input:checked ~ .lbap-switch__track { background: rgba(0,0,0,.22); border-color: rgba(0,0,0,.30); }
  [data-theme="light"] .lbap-switch__input:checked ~ .lbap-switch__track::after { background: #222; }

  [data-theme="light"] .lbap-summary__box { background: rgba(0,0,0,.025); border-color: rgba(0,0,0,.08); }
  [data-theme="light"] .lbap-summary__notes { border-color: rgba(0,0,0,.07); }
  [data-theme="light"] .lbap-summary__note { color: rgba(0,0,0,.4); }
  [data-theme="light"] .lbap-summary__hint { color: rgba(0,0,0,.3); }

  [data-theme="light"] .lbap-field__input { background: rgba(0,0,0,.03); border-color: rgba(0,0,0,.09); color: rgba(0,0,0,.85); }
  [data-theme="light"] .lbap-field__input:focus { border-color: rgba(0,0,0,.22); background: rgba(0,0,0,.05); }
  [data-theme="light"] .lbap-field__currency { color: rgba(0,0,0,.35); }

  [data-theme="light"] .lbap-pay-item { background: rgba(0,0,0,.02); border-color: rgba(0,0,0,.07); }
  [data-theme="light"] .lbap-pay-item:hover { background: rgba(0,0,0,.04); }
  [data-theme="light"] .lbap-pay-item__icon { background: rgba(0,0,0,.05); border-color: rgba(0,0,0,.09); color: rgba(0,0,0,.6); }
  [data-theme="light"] .lbap-pay-item__name { color: rgba(0,0,0,.85); }
  [data-theme="light"] .lbap-pay-item__sub { color: rgba(0,0,0,.4); }
  [data-theme="light"] .lbap-pay-badge { background: rgba(0,0,0,.05); border-color: rgba(0,0,0,.09); color: rgba(0,0,0,.45); }
  [data-theme="light"] label.lbap-pay-item:has(input[type="radio"]:checked) { background: rgba(0,0,0,.06); border-color: rgba(0,0,0,.18); }

  [data-theme="light"] #addon_payment_md .lbx-modal__content { background: #fff !important; }
  [data-theme="light"] #addon_payment_md .lbx-modal__btn--primary { background: rgba(88,101,242,.9); }

  /* ==============================================
     LBAP — LP Correction Tab
  ============================================== */

  .lbap-lp-panel {
    display: flex;
    flex-direction: column;
    gap: .85rem;
    max-height: none;
    overflow: visible;
  }

  /* Info note */
  .lbap-lp-note {
    display: flex;
    gap: .6rem;
    align-items: flex-start;
    padding: .6rem .75rem;
    border-radius: .65rem;
    background: rgba(245,158,11,.08);
    border: 1px solid rgba(245,158,11,.20);
    font-size: .8rem;
    color: rgba(255,255,255,.70);
    line-height: 1.45;
  }

  .lbap-lp-note i { color: #fbbf24; margin-top: 2px; flex-shrink: 0; }

  /* Section block (LP Gain or Current LP) */
  .lbap-lp-section {
    background: rgba(255,255,255,.025);
    border: 1px solid rgba(255,255,255,.07);
    border-radius: .85rem;
    padding: .75rem .85rem;
    display: flex;
    flex-direction: column;
    gap: .6rem;
  }

  .lbap-lp-section__head {
    display: flex;
    align-items: center;
    gap: .6rem;
  }

  .lbap-lp-section__icon {
    font-size: 1.15rem;
    line-height: 1;
    flex-shrink: 0;
  }

  .lbap-lp-section__title {
    font-weight: 700;
    font-size: .875rem;
    color: rgba(255,255,255,.88);
    line-height: 1.2;
  }

  .lbap-lp-section__booked {
    font-size: .775rem;
    color: rgba(255,255,255,.42);
    margin-top: 1px;
  }

  .lbap-lp-section__booked strong {
    color: #a5b4fc;
    font-weight: 600;
  }

  /* Tile grid */
  .lbap-lp-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: .35rem;
  }

  @media (max-width: 500px) {
    .lbap-lp-grid { grid-template-columns: repeat(2, 1fr); }
  }

  .lbap-lp-tile {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: .2rem;
    padding: .55rem .4rem;
    border-radius: .65rem;
    border: 1px solid rgba(255,255,255,.09);
    background: rgba(255,255,255,.03);
    color: rgba(255,255,255,.70);
    font-size: .78rem;
    font-weight: 500;
    cursor: pointer;
    transition: background .15s, border-color .15s, color .15s;
    text-align: center;
    line-height: 1.3;
  }

  .lbap-lp-tile:hover:not(:disabled):not(.is-disabled):not(.is-booked) {
    background: rgba(52,211,153,.10);
    border-color: rgba(52,211,153,.35);
    color: #6ee7b7;
  }

  .lbap-lp-tile.is-selected {
    background: rgba(52,211,153,.14);
    border-color: rgba(52,211,153,.50);
    color: #34d399;
    font-weight: 700;
  }

  .lbap-lp-tile.is-booked {
    background: rgba(99,102,241,.12);
    border-color: rgba(99,102,241,.35);
    color: #a5b4fc;
    cursor: default;
  }

  .lbap-lp-tile.is-disabled {
    opacity: .28;
    cursor: not-allowed;
  }

  .lbap-lp-tile__label { font-size: .77rem; }

  .lbap-lp-tile__tag {
    font-size: .6rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .04em;
    color: #818cf8;
  }

  /* Surcharge box */
  .lbap-lp-surcharge {
    padding: .65rem .9rem;
    border-radius: .7rem;
    background: rgba(52,211,153,.07);
    border: 1px solid rgba(52,211,153,.22);
    display: flex;
    flex-direction: column;
    gap: .3rem;
  }

  .lbap-lp-surcharge__row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    font-size: .82rem;
    color: rgba(255,255,255,.55);
  }

  .lbap-lp-surcharge__row strong {
    color: #34d399;
    font-weight: 700;
  }

  .lbap-lp-surcharge__row--total {
    padding-top: .3rem;
    border-top: 1px solid rgba(52,211,153,.15);
    margin-top: .1rem;
    color: rgba(255,255,255,.75);
  }

  .lbap-lp-surcharge__row--total strong {
    font-size: .95rem;
  }

  /* Keep payment-methods class working for JS compat */
  .payment-methods .payment-item {
    width: 100%;
    cursor: pointer;
    background: rgba(255, 255, 255, .03);
    border-radius: .75rem;
    transition: all .2s ease;
    border: 1px solid transparent;
  }

  .payment-methods .payment-item:hover {
    background: rgba(255, 255, 255, .06);
  }

  .payment-methods input[type="radio"] {
    position: absolute;
    opacity: 0;
    pointer-events: none;
  }

  .payment-methods input[type="radio"]:checked~* {
    pointer-events: none;
  }

  .payment-methods input[type="radio"]:checked~.badge {
    background: rgba(255,255,255,.15) !important;
  }

  .payment-methods label:has(input[type="radio"]:checked) {
    background: rgba(255,255,255,.07);
    border-color: rgba(255,255,255,.18);
  }

  /* Rank + View Profile responsive layout */
  .rank-visit-wrap {
    display: flex;
    flex-direction: column;
    /* DESKTOP: untereinander */
    align-items: center;
    gap: 8px;
  }

  /* MOBILE: nebeneinander */
  @media (max-width:575.98px) {
    .rank-visit-wrap {
      flex-direction: row;
      /* MOBILE: neben dem Rank-Icon */
      align-items: center;
      gap: 10px;
    }
  }

  /* Optional: sehr kleine Geräte -> darf umbrechen statt zu quetschen */
  @media (max-width:360px) {
    .rank-visit-wrap {
      flex-wrap: wrap;
    }
  }

  /* View Profile Button (LoLBoost style) */
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

  .visit-profile-btn i {
    font-size: .95rem;
    opacity: .95;
  }

  .visit-profile-btn:hover {
    transform: translateY(-1px);
    background: rgba(78, 161, 255, .18);
    border-color: rgba(78, 161, 255, .35);
    color: #8fc2ff;
  }

  /* Light theme */
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

  /* =========================
   ORDER ACTIONS CARD (extra)
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
  }

  .lb-action-item:first-child {
    border-top: 0;
  }

  .lb-action-item:hover {
    background: rgba(255, 255, 255, .05);
    transform: translateY(-1px);
  }

  [data-theme="light"] .lb-action-item {
    border-top-color: rgba(0, 0, 0, .06);
  }

  [data-theme="light"] .lb-action-item:hover {
    background: rgba(0, 0, 0, .03);
  }

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

  .lb-action-txt {
    min-width: 0;
    flex: 1;
  }

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

  .lb-action-item--danger .lb-action-title {
    color: #ffb1b1;
  }

  .lb-action-item--danger .lb-action-ico {
    background: rgba(255, 107, 107, .10);
    border-color: rgba(255, 107, 107, .22);
    color: #ffb1b1;
  }




  /* --- Desktop Emoji Picker (Order Chat) --- */
  .lb-emoji-picker {
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
  }

  .lb-emoji-picker .lb-emoji {
    background: transparent;
    border: 0;
    font-size: 22px;
    line-height: 1;
    padding: 6px;
    border-radius: 10px;
    cursor: pointer;
  }

  .lb-emoji-picker .lb-emoji:hover {
    background: rgba(255, 255, 255, 0.06);
  }

  @media (max-width: 767.98px) {

    .lb-emoji-picker,
    .lb-emoji-btn {
      display: none !important;
    }
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

  .btn-check:disabled+.btn, .btn-check[disabled]+.btn {
    opacity: 1 !important;
    cursor: not-allowed !important;
  }

  .boost-form-svg {
  filter: brightness(0) invert(1);
}

/* --- Options: Roles/Champions rows styled like other options --- */
.lb-options-extra{
  margin-top: 12px;
  padding-bottom: .85rem;
  border-bottom: 1px solid rgba(255,255,255,.06);
}
.lb-options-extra .lb-opt-row{
  padding: .85rem 0;
}
.lb-options-extra .lb-opt-ico{
  font-size:16px;
}
.lb-options-extra .lb-opt-right--icons{
  flex:1;
  min-width:0;
}
.lb-options-extra .lb-opt-icons{
  display:flex;
  /* show ALL selected items without overflowing outside the card */
  flex-wrap:wrap;
  gap:6px;
  justify-content:flex-end;
  align-content:flex-start;
  max-width:100%;
  overflow:hidden;
  padding: 2px 6px 2px 0;
}
.lb-options-extra .lb-opt-icons img,
.lb-options-extra .lb-opt-icons svg{
  flex:0 0 auto;
  margin:0 !important;
}

  /* =========================
   LOGIN MODAL: dark select
========================= */
  #account_logins_md select.form-select{
    background: rgba(18, 22, 30, .92) !important;
    color: rgba(235, 242, 255, .92) !important;
    border: 1px solid rgba(255,255,255,.10) !important;
  }
  #account_logins_md select.form-select:focus{
    box-shadow: 0 0 0 .2rem rgba(99,102,241,.25) !important;
    border-color: rgba(99,102,241,.55) !important;
  }
  #account_logins_md select.form-select option{
    background: #12161e !important;
    color: rgba(235, 242, 255, .92) !important;
  }

/* ── Discord Banner ── */
.lb-discord-banner {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: .75rem;
  background: #1e2035;
  border: 1px solid rgba(88,101,242,.4);
  border-radius: .75rem;
  padding: .75rem 1rem;
}
.lb-discord-banner__left {
  display: flex;
  align-items: center;
  gap: .75rem;
  min-width: 0;
}
.lb-discord-banner__logo {
  width: 28px;
  height: 21px;
  flex-shrink: 0;
}
.lb-discord-banner__text {
  display: flex;
  flex-direction: column;
  gap: .1rem;
  min-width: 0;
}
.lb-discord-banner__text strong {
  font-size: .825rem;
  font-weight: 700;
  color: rgba(235,242,255,.95);
  white-space: nowrap;
}
.lb-discord-banner__text span {
  font-size: .775rem;
  color: rgba(235,242,255,.55);
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}
.lb-discord-banner__btn {
  display: inline-flex;
  align-items: center;
  gap: .35rem;
  background: #5865f2;
  color: #fff !important;
  font-size: .8rem;
  font-weight: 600;
  padding: .4rem .85rem;
  border-radius: .5rem;
  text-decoration: none;
  white-space: nowrap;
  flex-shrink: 0;
  transition: background .15s;
}
.lb-discord-banner__btn:hover { background: #4752c4; color: #fff !important; }
[data-theme="light"] .lb-discord-banner { background: #eeeeff; border-color: rgba(88,101,242,.3); }
[data-theme="light"] .lb-discord-banner__text strong { color: #111; }
[data-theme="light"] .lb-discord-banner__text span { color: #555; }


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



.lb-op-card .card-header {
    padding: 14px 18px;
    border-bottom: 1px solid rgba(255, 255, 255, .06);
  }
  .lb-op-header-ico {
    width: 34px; height: 34px; border-radius: 10px;
    background: rgba(99, 102, 241, .14);
    border: 1px solid rgba(99, 102, 241, .25);
    display: inline-flex; align-items: center; justify-content: center;
    color: rgba(130, 134, 255, .95); font-size: .88rem; flex: 0 0 auto;
  }
  .lb-op-refresh-btn {
    width: 34px; height: 34px; border-radius: 10px;
    background: transparent; border: 1px solid rgba(255, 255, 255, .10);
    color: rgba(255, 255, 255, .65);
    display: inline-flex; align-items: center; justify-content: center;
    cursor: pointer; transition: background .15s, color .15s, border-color .15s; padding: 0;
  }
  .lb-op-refresh-btn:hover { background: rgba(255,255,255,.07); border-color: rgba(255,255,255,.18); color: rgba(255,255,255,.95); }
  .lb-op-refresh-btn:disabled { opacity: .45; pointer-events: none; }
  .lb-op-rank-row {
    display: flex; align-items: center; gap: 10px; padding: 14px;
    background: rgba(0,0,0,.16); border: 1px solid rgba(255,255,255,.06);
    border-radius: 16px; margin-bottom: 10px;
  }
  .lb-op-rank-box { display: flex; flex-direction: column; align-items: center; gap: 4px; flex: 1; min-width: 0; }
  .lb-op-rank-img { width: 48px; height: 48px; object-fit: contain; filter: drop-shadow(0 2px 6px rgba(0,0,0,.45)); flex: 0 0 auto; }
  .lb-op-rank-box--current .lb-op-rank-img { width: 56px; height: 56px; filter: drop-shadow(0 4px 12px rgba(99,102,241,.35)); }
  .lb-op-rank-name { font-size: .80rem; font-weight: 800; text-align: center; line-height: 1.2; word-break: break-word; }
  .lb-op-rank-label { font-size: .68rem; text-transform: uppercase; letter-spacing: .06em; font-weight: 700; opacity: .40; }
  .lb-op-rank-arrow { flex: 0 0 auto; opacity: .30; font-size: .85rem; display: flex; align-items: center; }
  .lb-op-stats { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 7px; margin-bottom: 8px; }
  .lb-op-stat {
    display: flex; flex-direction: column; align-items: center; justify-content: center;
    gap: 3px; padding: 10px 6px; border-radius: 13px;
    border: 1px solid rgba(255,255,255,.06); background: rgba(0,0,0,.12);
  }
  .lb-op-stat--win  { border-color: rgba(34,197,94,.20); background: rgba(34,197,94,.06); }
  .lb-op-stat--loss { border-color: rgba(239,68,68,.20); background: rgba(239,68,68,.06); }
  .lb-op-stat-val { font-size: 1.05rem; font-weight: 900; line-height: 1; }
  .lb-op-stat--win  .lb-op-stat-val { color: rgba(74,222,128,.95); }
  .lb-op-stat--loss .lb-op-stat-val { color: rgba(248,113,113,.95); }
  .lb-op-stat-lbl { font-size: .65rem; text-transform: uppercase; letter-spacing: .06em; font-weight: 700; opacity: .40; }
  .lb-op-wr-bar { height: 5px; background: rgba(255,255,255,.07); border-radius: 999px; overflow: hidden; margin-bottom: 14px; }
  .lb-op-wr-bar-fill { height: 100%; border-radius: 999px; background: rgba(255,255,255,.20); transition: width .5s ease; }
  .lb-op-wr-bar-fill--good { background: linear-gradient(90deg,rgba(34,197,94,.65) 0%,rgba(74,222,128,.90) 100%); }
  .lb-op-footer { display: flex; align-items: center; justify-content: space-between; gap: 6px; padding-top: 10px; border-top: 1px solid rgba(255,255,255,.06); flex-wrap: wrap; }
  .lb-op-footer-item { display: flex; align-items: center; gap: 5px; min-width: 0; }
  .lb-op-footer-label { font-size: .68rem; font-weight: 700; text-transform: uppercase; letter-spacing: .06em; opacity: .38; }
  .lb-op-footer-val { font-size: .76rem; font-weight: 700; opacity: .72; max-width: 160px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
  .lb-op-sync-state { font-size: .76rem; font-weight: 600; margin-top: 7px; min-height: 0; transition: color .2s; }
  .lb-op-sync-state:empty { display: none; }
  .lb-op-no-riot { font-size: .79rem; padding: 9px 12px; border-radius: 12px; background: rgba(255,255,255,.03); border: 1px solid rgba(255,255,255,.07); opacity: .60; }
  .lb-op-view-history {
    display: flex; align-items: center; justify-content: space-between;
    padding: 10px 0 2px; margin-top: 10px; border-top: 1px solid rgba(255,255,255,.06);
    text-decoration: none; color: inherit; opacity: .60; font-size: .78rem; font-weight: 700;
    letter-spacing: .01em; transition: opacity .15s;
  }
  .lb-op-view-history:hover { opacity: 1; color: inherit; text-decoration: none; }
  .lb-op-view-history-left { display: flex; align-items: center; gap: 7px; }
  .lb-op-view-history-left i { font-size: .82rem; }
  .lb-op-view-history-arrow { font-size: .68rem; opacity: .45; transition: transform .15s; }
  .lb-op-view-history:hover .lb-op-view-history-arrow { transform: translateX(3px); }
  [data-theme="light"] .lb-op-card .card-header { border-bottom-color: rgba(0,0,0,.07); }
  [data-theme="light"] .lb-op-header-ico { background: rgba(99,102,241,.09); border-color: rgba(99,102,241,.18); color: rgba(79,70,229,.90); }
  [data-theme="light"] .lb-op-refresh-btn { border-color: rgba(0,0,0,.12); color: rgba(0,0,0,.50); }
  [data-theme="light"] .lb-op-refresh-btn:hover { background: rgba(0,0,0,.04); border-color: rgba(0,0,0,.18); color: rgba(0,0,0,.80); }
  [data-theme="light"] .lb-op-rank-row { background: rgba(0,0,0,.04); border-color: rgba(0,0,0,.07); }
  [data-theme="light"] .lb-op-stat { background: rgba(0,0,0,.03); border-color: rgba(0,0,0,.07); }
  [data-theme="light"] .lb-op-stat--win  { background: rgba(34,197,94,.05); border-color: rgba(34,197,94,.18); }
  [data-theme="light"] .lb-op-stat--loss { background: rgba(239,68,68,.05); border-color: rgba(239,68,68,.18); }
  [data-theme="light"] .lb-op-wr-bar { background: rgba(0,0,0,.09); }
  [data-theme="light"] .lb-op-footer { border-top-color: rgba(0,0,0,.07); }
  [data-theme="light"] .lb-op-no-riot { background: rgba(0,0,0,.03); border-color: rgba(0,0,0,.07); }
  [data-theme="light"] .lb-op-view-history { border-top-color: rgba(0,0,0,.07); }
  [data-theme="light"] .lb-op-history-count { background: rgba(168,85,247,.08); border-color: rgba(168,85,247,.18); color: rgba(109,40,217,.90); }

  /* =========================
     MATCH HISTORY TRIGGER (client)
  ========================= */
  .lb-mh-history-trigger {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 12px 16px;
    background: rgba(255, 255, 255, .03);
    border: 1px solid rgba(255, 255, 255, .08);
    border-radius: 12px;
    text-decoration: none;
    color: inherit;
    opacity: .80;
    font-size: .82rem;
    font-weight: 700;
    transition: background .12s, opacity .12s, border-color .12s;
    letter-spacing: .01em;
  }
  .lb-mh-history-trigger:hover {
    background: rgba(255, 255, 255, .05);
    opacity: 1;
    color: inherit;
    text-decoration: none;
    border-color: rgba(255, 255, 255, .13);
  }
  .lb-mh-history-trigger-left { display: flex; align-items: center; gap: 8px; }
  .lb-mh-history-trigger-arrow { font-size: .68rem; opacity: .45; transition: transform .15s; }
  .lb-mh-history-trigger:hover .lb-mh-history-trigger-arrow { transform: translateX(3px); }
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
  [data-theme="light"] .lb-mh-history-trigger { background: rgba(0,0,0,.02); border-color: rgba(0,0,0,.08); }
  [data-theme="light"] .lb-mh-history-trigger:hover { background: rgba(0,0,0,.04); border-color: rgba(0,0,0,.12); }
  [data-theme="light"] .lb-op-history-count { background: rgba(168,85,247,.08); border-color: rgba(168,85,247,.18); color: rgba(109,40,217,.90); }

  /* =========================
     MATCH HISTORY MODAL (client)
  ========================= */
  .lb-mh-modal .modal-dialog   { --bs-modal-width: min(1080px, 98vw); max-width: min(1080px, 98vw) !important; }
  .lb-mh-list { transition: opacity .15s; }
  .lb-mh-modal .modal-content {
    border: 1px solid rgba(255, 255, 255, .08);
    background: var(--bs-card-bg, #1a1d23);
    border-radius: 18px;
    overflow: hidden;
  }
  .lb-mh-modal .modal-header {
    padding: 16px 20px;
    border-bottom: 1px solid rgba(255, 255, 255, .06);
    background: rgba(255, 255, 255, .02);
  }
  .lb-mh-modal .modal-body { padding: 0; }
  .lb-mh-header-ico {
    width: 36px; height: 36px; border-radius: 11px;
    background: rgba(168, 85, 247, .14);
    border: 1px solid rgba(168, 85, 247, .25);
    display: inline-flex; align-items: center; justify-content: center;
    color: rgba(196, 148, 255, .95); font-size: .90rem; flex: 0 0 auto;
  }
  /* ─ list container ─ */
  .lb-mh-list { width: 100%; }
  /* ─ header row ─ */
  .lb-mh-list-head {
    display: grid;
    grid-template-columns: 82px minmax(155px,1fr) minmax(125px,.75fr) 96px 100px 110px 175px 96px;
    align-items: center;
    padding: 7px 20px 7px 23px;
    border-bottom: 1px solid rgba(255, 255, 255, .06);
    background: rgba(0, 0, 0, .14);
  }
  .lb-mh-list-head span {
    font-size: .66rem; font-weight: 800; text-transform: uppercase;
    letter-spacing: .07em; opacity: .32; white-space: nowrap;
  }
  /* ─ match row ─ */
  .lb-mh-row {
    display: grid;
    grid-template-columns: 82px minmax(155px,1fr) minmax(125px,.75fr) 96px 100px 110px 175px 96px;
    align-items: center;
    padding: 11px 20px;
    border-bottom: 1px solid rgba(255, 255, 255, .04);
    border-left: 3px solid transparent;
    transition: background .12s;
  }
  .lb-mh-row:last-child { border-bottom: none; }
  .lb-mh-row--win  { border-left-color: rgba(34, 197, 94, .65);  background: rgba(34, 197, 94, .025); }
  .lb-mh-row--loss { border-left-color: rgba(239, 68, 68, .60);  background: rgba(239, 68, 68, .02); }
  .lb-mh-row--remake { border-left-color: rgba(56, 189, 248, .70); background: rgba(56, 189, 248, .035); }
  .lb-mh-row:hover { filter: brightness(1.08); }
  /* result */
  .lb-mh-result { display: flex; align-items: center; }
  .lb-mh-badge {
    display: inline-flex; align-items: center; justify-content: center; gap: 4px;
    padding: 4px 11px; border-radius: 999px; font-size: .68rem; font-weight: 900;
    letter-spacing: .04em; text-transform: uppercase; white-space: nowrap;
  }
  .lb-mh-badge--win  { color: rgba(74,222,128,.95); background: rgba(34,197,94,.12); border: 1px solid rgba(34,197,94,.22); }
  .lb-mh-badge--loss { color: rgba(248,113,113,.95); background: rgba(239,68,68,.12); border: 1px solid rgba(239,68,68,.22); }
  .lb-mh-badge--remake { color: rgba(125,211,252,.98); background: rgba(56,189,248,.12); border: 1px solid rgba(56,189,248,.24); }
  /* champion */
  .lb-mh-champ-col  { display: flex; align-items: center; gap: 10px; min-width: 0; }
  .lb-mh-champ-img  {
    width: 36px; height: 36px; border-radius: 10px; object-fit: cover;
    border: 1px solid rgba(255,255,255,.10); background: rgba(0,0,0,.35); flex: 0 0 auto;
  }
  .lb-mh-champ-info { display: flex; flex-direction: column; gap: 1px; min-width: 0; }
  .lb-mh-champ-name { font-weight: 800; font-size: .82rem; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
  .lb-mh-queue      { font-size: .68rem; font-weight: 600; opacity: .40; white-space: nowrap; }
  /* role */

  .lb-mh-booster-col {
    display: flex;
    align-items: center;
    gap: 8px;
    min-width: 0;
  }
  .lb-mh-booster-ico {
    width: 24px; height: 24px; border-radius: 8px;
    display: inline-flex; align-items: center; justify-content: center;
    flex: 0 0 auto;
    background: rgba(168, 85, 247, .12);
    border: 1px solid rgba(168, 85, 247, .22);
    color: rgba(196, 148, 255, .95);
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
    display: block; min-width: 0; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;
    font-size: .78rem; font-weight: 800;
  }
  .lb-mh-booster-sub {
    display: block; font-size: .66rem; font-weight: 700; opacity: .38;
    text-transform: uppercase; letter-spacing: .05em;
  }

  .lb-mh-role-col { display: flex; align-items: center; gap: 6px; font-size: .78rem; font-weight: 700; opacity: .75; }
  .lb-mh-role-img { width: 20px; height: 20px; object-fit: contain; opacity: .85; }
  /* kda */
  .lb-mh-kda-col   { display: flex; flex-direction: column; gap: 2px; }
  .lb-mh-kda       { font-size: .88rem; font-weight: 900; font-variant-numeric: tabular-nums; }
  .lb-mh-kda-sep   { opacity: .28; margin: 0 2px; font-weight: 400; }
  .lb-mh-kda-ratio { font-size: .68rem; font-weight: 700; opacity: .42; }
  /* duration */
  .lb-mh-dur-col { display: flex; flex-direction: column; gap: 2px; }
  .lb-mh-dur     { font-size: .82rem; font-weight: 800; font-variant-numeric: tabular-nums; }
  .lb-mh-sub     { font-size: .67rem; font-weight: 600; opacity: .38; text-transform: uppercase; letter-spacing: .05em; }
  /* rank snapshot */
  .lb-mh-rank-col  { display: flex; flex-direction: column; gap: 2px; }
  .lb-mh-rank-name { font-size: .74rem; font-weight: 800; white-space: nowrap; overflow: visible; text-overflow: clip; }
  /* date */
  .lb-mh-date-col { display: flex; flex-direction: column; gap: 2px; }
  .lb-mh-date     { font-size: .80rem; font-weight: 700; }
  .lb-mh-time     { font-size: .70rem; opacity: .45; font-weight: 600; }
  /* loading / empty */
  .lb-mh-placeholder {
    text-align: center; padding: 40px 20px;
    opacity: .42; font-weight: 600; font-size: .82rem;
  }
  /* pagination */
  .lb-mh-pager {
    display: flex; align-items: center; justify-content: space-between; gap: 8px;
    padding: 12px 20px; border-top: 1px solid rgba(255, 255, 255, .06);
    font-size: .78rem; background: rgba(0, 0, 0, .08);
  }
  .lb-mh-pager-info { opacity: .42; font-weight: 600; }
  .lb-mh-pager-btns { display: flex; gap: 6px; }
  .lb-mh-pager-btn {
    padding: 5px 14px; border-radius: 9px; background: transparent;
    border: 1px solid rgba(255, 255, 255, .10); color: inherit;
    font-size: .76rem; font-weight: 700; cursor: pointer;
    transition: background .12s, border-color .12s;
  }
  .lb-mh-pager-btn:hover:not(:disabled) { background: rgba(255,255,255,.07); border-color: rgba(255,255,255,.18); }
  .lb-mh-pager-btn:disabled { opacity: .22; cursor: default; }
  /* light theme */
  [data-theme="light"] .lb-mh-modal .modal-content  { border-color: rgba(0,0,0,.10); }
  [data-theme="light"] .lb-mh-modal .modal-header   { border-bottom-color: rgba(0,0,0,.07); background: rgba(0,0,0,.02); }
  [data-theme="light"] .lb-mh-header-ico            { background: rgba(168,85,247,.08); border-color: rgba(168,85,247,.18); color: rgba(109,40,217,.90); }
  [data-theme="light"] .lb-mh-list-head             { background: rgba(0,0,0,.04); border-bottom-color: rgba(0,0,0,.07); }
  [data-theme="light"] .lb-mh-row                   { border-bottom-color: rgba(0,0,0,.05); }
  [data-theme="light"] .lb-mh-row--win              { background: rgba(34,197,94,.03); }
  [data-theme="light"] .lb-mh-row--loss             { background: rgba(239,68,68,.025); }
  [data-theme="light"] .lb-mh-row--remake           { background: rgba(14,165,233,.045); }
  [data-theme="light"] .lb-mh-champ-img             { border-color: rgba(0,0,0,.10); }
  [data-theme="light"] .lb-mh-badge--win            { color: rgba(21,128,61,.95); }
  [data-theme="light"] .lb-mh-badge--loss           { color: rgba(185,28,28,.95); }
  [data-theme="light"] .lb-mh-badge--remake         { color: rgba(3,105,161,.95); }
  [data-theme="light"] .lb-mh-pager                 { border-top-color: rgba(0,0,0,.07); background: rgba(0,0,0,.03); }
  [data-theme="light"] .lb-mh-pager-btn             { border-color: rgba(0,0,0,.10); }
  [data-theme="light"] .lb-mh-pager-btn:hover:not(:disabled) { background: rgba(0,0,0,.04); border-color: rgba(0,0,0,.16); }
  @media (max-width: 700px) {
    .lb-mh-list-head { grid-template-columns: 80px 1fr 80px; }
    .lb-mh-list-head span:nth-child(n+4) { display: none; }
    .lb-mh-row { grid-template-columns: 80px 1fr 80px; }
    .lb-mh-row > *:nth-child(n+4) { display: none; }
  }


/* Coaching Order Progress, same structure as admin view */
.lb-op-coaching-info{
  border-radius: 18px;
  background: rgba(0,0,0,.18);
  border: 1px solid rgba(255,255,255,.06);
  padding: 22px 18px;
  text-align: center;
  margin-bottom: 12px;
}
.lb-op-coaching-hours{
  font-size: 34px;
  line-height: 1;
  font-weight: 950;
  color: rgba(235,238,245,.96);
}
.lb-op-coaching-label{
  margin-top: 9px;
  font-size: 12px;
  font-weight: 900;
  letter-spacing: .08em;
  text-transform: uppercase;
  color: rgba(255,255,255,.42);
}
.lb-op-coaching-note{
  display: flex;
  align-items: center;
  gap: 8px;
  border-radius: 14px;
  background: rgba(255,255,255,.04);
  border: 1px solid rgba(255,255,255,.07);
  padding: 11px 13px;
  color: rgba(255,255,255,.72);
  font-size: 13px;
}


/* Coaching orders: no Riot tracking footer/history */
.lb-order-is-coaching .lb-op-footer,
.lb-order-is-coaching #riotProgressLastMatch,
.lb-order-is-coaching #riotProgressSyncState,
.lb-order-is-coaching .lb-op-no-riot,
.lb-order-is-coaching .lb-op-warning,
.lb-order-is-coaching .lb-op-view-history,
.lb-order-is-coaching #matchHistoryModal{
  display: none !important;
}


  .lb-r5s-choice-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: .65rem;
  }
  .lb-r5s-choice {
    min-width: 0;
    display: flex;
    align-items: center;
    gap: .55rem;
    padding: .72rem .8rem;
    border-radius: 16px;
    border: 1px solid rgba(255,255,255,.10);
    background: rgba(255,255,255,.04);
    cursor: pointer;
    transition: .15s ease;
  }
  .lb-r5s-choice:hover {
    background: rgba(255,255,255,.07);
    border-color: rgba(255,255,255,.16);
  }
  .lb-r5s-choice.is-active {
    background: rgba(99,102,241,.20);
    border-color: rgba(99,102,241,.55);
    box-shadow: 0 0 0 1px rgba(99,102,241,.18) inset;
  }
  .lb-r5s-choice input { display: none; }
  .lb-r5s-choice img {
    width: 28px;
    height: 28px;
    border-radius: 999px;
    object-fit: cover;
    flex: 0 0 auto;
  }
  .lb-r5s-choice-name {
    min-width: 0;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
    font-weight: 900;
  }
  .lb-r5s-choice-lane {
    margin-left: auto;
    flex: 0 0 auto;
    padding: .20rem .48rem;
    border-radius: 999px;
    background: rgba(124,92,255,.22);
    color: rgba(255,255,255,.92);
    font-size: .72rem;
    font-weight: 900;
  }
  @media (max-width:575.98px){
    .lb-r5s-choice-grid { grid-template-columns: 1fr; }
  }

  .lb-r5s-booster-tabs {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
    margin: 0 0 14px;
  }

  .lb-r5s-booster-tab {
    display: inline-flex;
    align-items: center;
    gap: 9px;
    min-height: 44px;
    padding: 7px 12px 7px 8px;
    border: 1px solid rgba(255,255,255,.10);
    border-radius: 14px;
    background: rgba(255,255,255,.045);
    color: rgba(255,255,255,.72);
    font-weight: 900;
    cursor: pointer;
    transition: background .16s ease, border-color .16s ease, transform .16s ease;
  }

  .lb-r5s-booster-tab:hover {
    transform: translateY(-1px);
    border-color: rgba(139,92,246,.45);
    background: rgba(139,92,246,.10);
  }

  .lb-r5s-booster-tab.is-active {
    color: #fff;
    border-color: rgba(139,92,246,.72);
    background: rgba(139,92,246,.18);
  }

  .lb-r5s-booster-tab img {
    width: 30px;
    height: 30px;
    border-radius: 10px;
    object-fit: cover;
  }

  .lb-r5s-booster-tab small {
    padding: 4px 7px;
    border-radius: 999px;
    background: rgba(0,0,0,.18);
    color: rgba(255,255,255,.68);
    font-size: 11px;
    font-weight: 950;
  }

  .lb-r5s-booster-panel {
    display: none;
  }

  .lb-r5s-booster-panel.is-active {
    display: block;
  }

  @media (max-width: 560px) {
    .lb-r5s-booster-tabs {
      display: grid;
      grid-template-columns: 1fr;
    }

    .lb-r5s-booster-tab {
      width: 100%;
      justify-content: flex-start;
    }
  }


  .lb-meta-pill--boosters .lb-meta-pill__v {
    white-space: normal;
    overflow: visible;
    text-overflow: unset;
    line-height: 1.25;
  }

</style>
<script>
  document.addEventListener('DOMContentLoaded', () => {
    const btn = document.querySelector('.js-play-again');
    if (!btn) return;

    btn.addEventListener('click', (e) => {
      e.preventDefault();

      const boosterId = <?= (int) ($claimedBoosterId ?? 0) ?>;

      try {
        if (boosterId > 0) sessionStorage.setItem('booster', String(boosterId));
        else sessionStorage.removeItem('booster');
      } catch (err) {}

      window.location.href = <?= json_encode($isCoachingOrder ? '/lol/coaching' : '/lol/rank-boost') ?>;
    });
  });
</script>

<?= $this->end() ?>
<style>
/* Client order chat: image sizing */
#chat_messages img{max-width:320px;max-height:320px;width:auto;height:auto;border-radius:10px;cursor:pointer;display:inline-block;}
#chat_messages img.lb-msg__avatar{max-width:36px!important;max-height:36px!important;width:36px!important;height:36px!important;border-radius:999px;cursor:default;}
.lb-chat-preview{position:relative;display:inline-block;max-width:160px;border-radius:12px;overflow:hidden;border:1px solid rgba(255,255,255,.12);}
.lb-chat-preview img{display:block;width:100%;height:auto;max-height:160px;object-fit:cover;}
.lb-chat-preview__remove{position:absolute;top:6px;right:6px;border:0;background:rgba(0,0,0,.55);color:#fff;width:28px;height:28px;border-radius:999px;display:grid;place-items:center;}
.lb-img-modal{position:fixed;inset:0;z-index:1090;display:none;align-items:center;justify-content:center;padding:16px;background:rgba(0,0,0,.72);}
.lb-img-modal.is-open{display:flex;}
.lb-img-modal__inner{position:relative;max-width:min(980px, 96vw);max-height:86vh;}
.lb-img-modal__img{max-width:100%;max-height:86vh;border-radius:14px;display:block;}
.lb-img-modal__close{position:absolute;top:-10px;right:-10px;border:0;background:rgba(0,0,0,.7);color:#fff;width:40px;height:40px;border-radius:999px;display:grid;place-items:center;}

#client_change_booster_confirm_md .modal-dialog {
  max-width: 760px;
  width: calc(100% - 24px);
  margin: 1rem auto;
}
#client_change_booster_confirm_md .lb-confirmModal2 {
  background: #25282A;
  border: 1px solid rgba(255,255,255,.10);
  border-radius: 22px;
  overflow: hidden;
  color: rgba(255,255,255,.94);
  box-shadow: 0 30px 90px rgba(0,0,0,.65);
}
#client_change_booster_confirm_md .lb-confirmModal2__header {
  padding: 16px 16px 12px;
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
  gap: 14px;
  border-bottom: 1px solid rgba(255,255,255,.08);
  background: rgba(255,255,255,.03);
}
#client_change_booster_confirm_md .lb-confirmModal2__headLeft {
  display: flex;
  align-items: flex-start;
  gap: 12px;
  min-width: 0;
}
#client_change_booster_confirm_md .lb-confirmModal2__icon {
  width: 44px;
  height: 44px;
  flex: 0 0 44px;
  border-radius: 14px;
  display: grid;
  place-items: center;
  background: rgba(255,255,255,.06);
  border: 1px solid rgba(255,255,255,.10);
  color: rgba(255,255,255,.92);
}
#client_change_booster_confirm_md .lb-confirmModal2__title {
  font-weight: 900;
  font-size: 1.22rem;
  line-height: 1.15;
}
#client_change_booster_confirm_md .lb-confirmModal2__sub {
  margin-top: 6px;
  color: rgba(255,255,255,.72);
  font-size: .96rem;
  line-height: 1.45;
  max-width: 520px;
}
#client_change_booster_confirm_md .lb-confirmModal2__close {
  width: 42px;
  height: 42px;
  flex: 0 0 42px;
  border-radius: 14px;
  display: grid;
  place-items: center;
  background: rgba(255,255,255,.04);
  border: 1px solid rgba(255,255,255,.10);
  color: rgba(255,255,255,.85);
}
#client_change_booster_confirm_md .lb-confirmModal2__close:hover {
  background: rgba(255,255,255,.07);
  color: #fff;
}
#client_change_booster_confirm_md .lb-confirmModal2__body {
  padding: 16px;
}
#client_change_booster_confirm_md .lb-confirmModal2__summary {
  border-radius: 18px;
  border: 1px solid rgba(255,255,255,.08);
  background: rgba(255,255,255,.03);
  overflow: hidden;
}
#client_change_booster_confirm_md .lb-confirmModal2__row {
  min-height: 60px;
  padding: 0 18px;
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 16px;
  border-bottom: 1px solid rgba(255,255,255,.08);
}
#client_change_booster_confirm_md .lb-confirmModal2__row:last-child { border-bottom: 0; }
#client_change_booster_confirm_md .lb-confirmModal2__row span {
  color: rgba(255,255,255,.66);
  font-size: .98rem;
}
#client_change_booster_confirm_md .lb-confirmModal2__row strong {
  color: #fff;
  font-size: 1.02rem;
  font-weight: 800;
  text-align: right;
}
#client_change_booster_confirm_md .lb-confirmModal2__notice {
  margin-top: 14px;
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 14px 16px;
  border-radius: 16px;
  background: rgba(255,255,255,.03);
  border: 1px solid rgba(255,255,255,.08);
}
#client_change_booster_confirm_md .lb-confirmModal2__noticeIcon {
  width: 34px;
  height: 34px;
  flex: 0 0 34px;
  border-radius: 12px;
  display: grid;
  place-items: center;
  background: rgba(255,255,255,.06);
  border: 1px solid rgba(255,255,255,.10);
  color: rgba(255,255,255,.88);
}
#client_change_booster_confirm_md .lb-confirmModal2__noticeText {
  color: rgba(255,255,255,.86);
  font-size: .95rem;
  line-height: 1.45;
}
#client_change_booster_confirm_md .lb-confirmModal2__footer {
  display: flex;
  justify-content: flex-end;
  gap: 10px;
  padding: 14px 16px;
  border-top: 1px solid rgba(255,255,255,.08);
  background: rgba(255,255,255,.02);
}
#client_change_booster_confirm_md .lb-confirmModal2__btn {
  min-height: 48px;
  border-radius: 14px;
  padding: 0 18px;
  border: 1px solid rgba(255,255,255,.10);
  font-weight: 800;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  color: rgba(255,255,255,.94);
}
#client_change_booster_confirm_md .lb-confirmModal2__btn--ghost {
  background: rgba(255,255,255,.03);
}
#client_change_booster_confirm_md .lb-confirmModal2__btn--ghost:hover {
  background: rgba(255,255,255,.06);
  color: #fff;
}
#client_change_booster_confirm_md .lb-confirmModal2__btn--primary {
  background: #515dd5;
  border-color: #515dd5;
  color: #fff;
  box-shadow: 0 10px 26px rgba(81,93,213,.28);
}
#client_change_booster_confirm_md .lb-confirmModal2__btn--primary:hover {
  background: #5d69df;
  border-color: #5d69df;
  color: #fff;
}
@media (max-width: 767.98px) {
  #client_change_booster_confirm_md .modal-dialog {
    width: calc(100% - 16px);
    margin: .5rem auto;
  }
  #client_change_booster_confirm_md .lb-confirmModal2 { border-radius: 18px; }
  #client_change_booster_confirm_md .lb-confirmModal2__header,
  #client_change_booster_confirm_md .lb-confirmModal2__body,
  #client_change_booster_confirm_md .lb-confirmModal2__footer { padding-left: 12px; padding-right: 12px; }
  #client_change_booster_confirm_md .lb-confirmModal2__row { min-height: 56px; padding: 0 14px; }
  #client_change_booster_confirm_md .lb-confirmModal2__footer {
    flex-direction: column-reverse;
  }
  #client_change_booster_confirm_md .lb-confirmModal2__btn { width: 100%; }
}

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
  .lb-op-count-rank-name { font-size:.82rem; font-weight:800; line-height:1.2; word-break:break-word; }</style>

<div class="lb-img-modal" id="lbImgModal" aria-hidden="true">
  <div class="lb-img-modal__inner" role="dialog" aria-modal="true">
    <button type="button" class="lb-img-modal__close" id="lbImgModalClose" aria-label="Close">
      <i class="fa-solid fa-xmark"></i>
    </button>
    <img src="" alt="Image" class="lb-img-modal__img" id="lbImgModalImg">
  </div>
</div>


<script>
(function(){
  if (window.lbClientChatUploadInit) return;
  window.lbClientChatUploadInit = true;

  const form = document.querySelector('form.ajax-form input[name="action"][value="client_order_chat_send"]')?.closest('form');
  if (!form) return;

  const msgInput = document.getElementById('lbChatMessageInput');
  const fileInput = document.getElementById('lbChatImageInput');
  const uploadBtn = document.getElementById('lbChatUploadBtn');
  const previewWrap = document.getElementById('lbChatImagePreviewWrap');
  const previewImg = document.getElementById('lbChatImagePreview');
  const previewRemove = document.getElementById('lbChatImageRemove');

  let selectedFile = null;

  function setPreview(file){
    selectedFile = file || null;
    if (!previewWrap || !previewImg) return;
    if (!selectedFile){
      previewWrap.classList.add('d-none');
      previewImg.src = '';
      if (fileInput) fileInput.value = '';
      return;
    }
    const url = URL.createObjectURL(selectedFile);
    previewImg.src = url;
    previewWrap.classList.remove('d-none');
  }

  if (uploadBtn && fileInput){
    uploadBtn.addEventListener('click', () => fileInput.click());
    fileInput.addEventListener('change', () => {
      const f = fileInput.files && fileInput.files[0];
      if (f) setPreview(f);
    });
  }
  if (previewRemove){
    previewRemove.addEventListener('click', () => setPreview(null));
  }

  // Paste image (Ctrl+V)
  function handlePaste(e){
    const items = (e.clipboardData && e.clipboardData.items) ? Array.from(e.clipboardData.items) : [];
    const imgItem = items.find(it => it && it.type && it.type.indexOf('image/') === 0);
    if (!imgItem) return;
    const file = imgItem.getAsFile();
    if (!file) return;
    e.preventDefault();
    setPreview(file);
  }
  if (msgInput){
    msgInput.addEventListener('paste', handlePaste);
  } else {
    document.addEventListener('paste', handlePaste);
  }

  // Submit with FormData (supports image)
  form.addEventListener('submit', function(e){
    // let other handlers run? we must stop default + propagation
    e.preventDefault();
    e.stopPropagation();
    if (typeof e.stopImmediatePropagation === 'function') e.stopImmediatePropagation();

    const text = (msgInput && msgInput.value) ? msgInput.value.trim() : '';
    if (!text && !selectedFile) return;

    const fd = new FormData(form);
    // ensure message field is present
    if (msgInput) fd.set('message', text);

    if (selectedFile){
      fd.set('chat_image', selectedFile, selectedFile.name || 'image.png');
    }

    // disable submit
    const submitBtn = form.querySelector('button[type="submit"]');
    if (submitBtn) submitBtn.disabled = true;

    $.ajax({
      url: form.getAttribute('action'),
      type: 'POST',
      data: fd,
      processData: false,
      contentType: false
    }).done(function(res){
      try{
        if (typeof window.ajax_response_handler === 'function') window.ajax_response_handler(res);
      }catch(_){}
      // clear
      if (msgInput) msgInput.value = '';
      setPreview(null);
      // reload messages quickly
      try{
        window.chat_json = {};
        window.msg_none = false;
        if (typeof window.load_messages === 'function') window.load_messages();
      }catch(_){}
    }).fail(function(){
      if (typeof create_toast === 'function') create_toast('danger','Error','Could not send message.');
    }).always(function(){
      if (submitBtn) submitBtn.disabled = false;
    });
  }, true);

  // Image modal
  const modal = document.getElementById('lbImgModal');
  const modalImg = document.getElementById('lbImgModalImg');
  const modalClose = document.getElementById('lbImgModalClose');

  function openModal(src){
    if (!modal || !modalImg) return;
    modalImg.src = src;
    modal.classList.add('is-open');
    modal.setAttribute('aria-hidden','false');
    document.body.style.overflow = 'hidden';
  }
  function closeModal(){
    if (!modal || !modalImg) return;
    modal.classList.remove('is-open');
    modal.setAttribute('aria-hidden','true');
    modalImg.src = '';
    document.body.style.overflow = '';
  }

  if (modalClose) modalClose.addEventListener('click', closeModal);
  if (modal) modal.addEventListener('click', function(ev){
    if (ev.target === modal) closeModal();
  });
  document.addEventListener('keydown', function(ev){
    if (ev.key === 'Escape') closeModal();
  });

  // Intercept clicks on linked images so no new tab
  $(document).on('click', '#chat_messages a', function(ev){
    const $a = $(this);
    const $img = $a.find('img');
    if ($img.length === 0) return;
    // ignore avatars/icons
    if ($img.hasClass('lb-msg__avatar')) return;
    const href = $a.attr('href') || $img.attr('src');
    if (!href) return;
    ev.preventDefault();
    ev.stopPropagation();
    openModal(href);
  });

  // Also handle bare images
  $(document).on('click', '#chat_messages img', function(ev){
    const img = ev.currentTarget;
    if (!img) return;
    if (img.classList.contains('lb-msg__avatar')) return;
    const src = img.getAttribute('src');
    if (!src) return;
    ev.preventDefault();
    ev.stopPropagation();
    openModal(src);
  });
})();
</script>

<script>
document.addEventListener('DOMContentLoaded', function () {
  function syncChoice(group, hiddenId, titleId) {
    const wrap = document.querySelector('[data-lb-choice-group="' + group + '"]');
    const hidden = document.getElementById(hiddenId);
    const title = document.getElementById(titleId);
    if (!wrap || !hidden) return;

    function apply(input) {
      if (!input) return;
      hidden.value = input.value || '';
      if (title) title.textContent = input.dataset.name || 'Booster';
      wrap.querySelectorAll('.lb-r5s-choice').forEach(function(label){ label.classList.remove('is-active'); });
      const label = input.closest('.lb-r5s-choice');
      if (label) label.classList.add('is-active');
    }

    const checked = wrap.querySelector('input[type="radio"]:checked') || wrap.querySelector('input[type="radio"]');
    apply(checked);
    wrap.addEventListener('change', function(e){
      const input = e.target.closest('input[type="radio"]');
      if (input) apply(input);
    });
  }

  syncChoice('tip', 'lb_tip_booster_id', 'lb_tip_booster_title');
  syncChoice('notify', 'lb_notify_booster_id', 'lb_notify_booster_title');

  const changeRequestWrap = document.querySelector('[data-lb-choice-group="change-request"]');
  if (changeRequestWrap) {
    changeRequestWrap.addEventListener('change', function(e){
      const input = e.target.closest('input[type="radio"]');
      if (!input) return;
      changeRequestWrap.querySelectorAll('.lb-r5s-choice').forEach(function(label){
        label.classList.toggle('is-active', label.contains(input));
      });
    });
  }
});
</script>

<script>
/**
 * Poke/Notify cooldown (Client): lock "Notify Booster" for 5 minutes after click
 * Applies to all elements with data-action="poke_booster" (dropdown, actions card, chat header button).
 * Note: backend still enforces its own cooldown; this is purely UI/UX.
 */
document.addEventListener('DOMContentLoaded', function () {
  const ORDER_ID = <?= (int)($data['id'] ?? 0) ?>;
  const COOLDOWN_MS = 5 * 60 * 1000;
  const LS_KEY = 'lb_poke_booster_cooldown_until_' + ORDER_ID;

  function now(){ return Date.now(); }
  function getUntil(){
    try {
      const v = localStorage.getItem(LS_KEY);
      const n = parseInt(v || '0', 10);
      return isFinite(n) ? n : 0;
    } catch(e){ return 0; }
  }
  function setUntil(ts){ try { localStorage.setItem(LS_KEY, String(ts)); } catch(e){} }
  function remainingMs(){
    const rem = getUntil() - now();
    return rem > 0 ? rem : 0;
  }
  function fmtMMSS(ms){
    const total = Math.ceil(ms / 1000);
    const m = Math.floor(total / 60);
    const s = total % 60;
    return String(m).padStart(1,'0') + ':' + String(s).padStart(2,'0');
  }
  function toast(type, title, message){
    try {
      if (typeof window.create_toast === 'function') return window.create_toast(type, title, message);
      if (typeof window.sendToast === 'function') return window.sendToast({ type, title, message });
    } catch(e) {}
  }

  function getTargets(){
    const list = Array.from(document.querySelectorAll('[data-action="poke_booster"], .btn-notify-booster'));
    return Array.from(new Set(list));
  }

  function setDisabled(el, disabled, label){
    if (!el) return;

    if (!el.dataset.lbOrigHtml) el.dataset.lbOrigHtml = el.innerHTML;
    if (!el.dataset.lbOrigAria) el.dataset.lbOrigAria = el.getAttribute('aria-disabled') || '';
    if (!el.dataset.lbOrigHref) el.dataset.lbOrigHref = el.getAttribute('href') || '';

    const isBtn = el.tagName === 'BUTTON';
    if (disabled){
      el.classList.add('disabled', 'lb-cooldown-disabled');
      el.setAttribute('aria-disabled','true');
      if (isBtn) el.disabled = true;
      if (el.tagName === 'A') el.setAttribute('href', '#');

      const tAction = el.querySelector('.lb-action-title');
      const tSpan = el.querySelector('span');
      if (tAction) tAction.textContent = label;
      else if (isBtn && tSpan) tSpan.textContent = label;
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

  function applyUI(){
    const rem = remainingMs();
    const disabled = rem > 0;
    const label = disabled ? `Notify Booster (${fmtMMSS(rem)})` : 'Notify Booster';
    getTargets().forEach(el => setDisabled(el, disabled, label));
  }

  let timer = null;
  function startTimer(){
    if (timer) return;
    timer = setInterval(function(){
      if (remainingMs() <= 0){
        clearInterval(timer);
        timer = null;
      }
      applyUI();
    }, 500);
  }

  document.addEventListener('click', function(e){
    const t = e.target.closest('[data-action="poke_booster"], .btn-notify-booster');
    if (!t) return;

    const rem = remainingMs();
    if (rem > 0){
      e.preventDefault();
      e.stopPropagation();
      if (typeof e.stopImmediatePropagation === 'function') e.stopImmediatePropagation();
      toast('warning', 'Cooldown', `Please wait ${fmtMMSS(rem)} before notifying again.`);
      applyUI();
      startTimer();
      return;
    }

    setUntil(now() + COOLDOWN_MS);
    applyUI();
    startTimer();
  }, true);

  applyUI();
  if (remainingMs() > 0) startTimer();
});
</script>

<script>
document.addEventListener('DOMContentLoaded', function () {
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
