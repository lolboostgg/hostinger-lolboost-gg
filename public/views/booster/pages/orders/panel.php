<?= $this->layout('booster/layouts/main', ['meta' => ['title' => 'Orders Panel - Booster Area | LoLBoost.gg', 'h1' => 'Orders Panel', 'description' => 'Claim orders here.']]) ?>

<?php
// Record meaningful booster activity when the Orders Panel is opened.
// Throttled to one database write every 30 minutes.
if ((!defined('IS_EGIRL') || !IS_EGIRL) && defined('BOOSTER_ID') && (int)BOOSTER_ID > 0) {
    $lbOrdersPanelBoosterId = (int)BOOSTER_ID;
    try {
        global $db;
        $db->run(
            "UPDATE boosters
             SET last_order_check = NOW()
             WHERE id = {$lbOrdersPanelBoosterId}
               AND last_order_check IS NOT NULL
               AND last_order_check >= DATE_SUB(NOW(), INTERVAL 14 DAY)
               AND last_order_check < DATE_SUB(NOW(), INTERVAL 30 MINUTE)"
        );
    } catch (Throwable $e) {
        // Do not block the Orders Panel if the activity timestamp cannot be written.
    }
}
?>

<?php
/**
 * Orders Panel — Card View extras
 *
 * We try to detect optional flags (Play with booster, Priority, Bonus Win, Voice Chat, etc.) from
 * multiple possible sources/keys in $row, because schema can differ across products and games.
 */

if (!function_exists('op__json_decode_if_possible')) {
  function op__json_decode_if_possible($v) {
    if (!is_string($v)) return null;
    $s = trim($v);
    if ($s === '') return null;
    $first = $s[0] ?? '';
    if ($first !== '{' && $first !== '[') return null;
    $decoded = json_decode($s, true);
    return (json_last_error() === JSON_ERROR_NONE) ? $decoded : null;
  }
}



if (!function_exists('op_client_presence_format_ago')) {
  function op_client_presence_format_ago($seconds): string {
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

if (!function_exists('op_client_presence_state')) {
  function op_client_presence_state($clientId): array {
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
      $state['label'] = $state['online'] ? 'Online' : ('Last Seen for ' . op_client_presence_format_ago($age));
      $state['title'] = $state['online'] ? 'Client is currently online' : ('Client last seen ' . op_client_presence_format_ago($age) . ' ago');
    } catch (Throwable $e) {}

    return $state;
  }
}

if (!function_exists('op_client_presence_badge_html')) {
  function op_client_presence_badge_html($clientId): string {
    $presence = op_client_presence_state($clientId);
    $cls = $presence['online'] ? 'is-online' : 'is-offline';
    $label = htmlspecialchars((string)$presence['label'], ENT_QUOTES, 'UTF-8');
    $title = htmlspecialchars((string)$presence['title'], ENT_QUOTES, 'UTF-8');
    return '<span class="op-client-presence ' . $cls . '"><span class="op-client-presence__dot"></span><span>' . $label . '</span></span>';
  }
}

if (!function_exists('op__parse_ts')) {
  function op__parse_ts($v): int {
    if ($v === null) return 0;
    if (is_int($v)) return $v;
    if (is_float($v)) return (int)$v;
    if (is_string($v)) {
      $s = trim($v);
      if ($s === '') return 0;
      // normalize common separators the UI might include (e.g. "·")
      $s = str_replace(['·','•'], ' ', $s);
      $s = preg_replace('/\s+/', ' ', $s);
      $ts = strtotime($s);
      return $ts ? (int)$ts : 0;
    }
    return 0;
  }
}
if (!function_exists('op__to_bool')) {
  function op__to_bool($v) {
    if (is_bool($v)) return $v;
    if (is_int($v)) return $v === 1;
    if (is_float($v)) return ((int)$v) === 1;
    if (is_string($v)) {
      $s = strtolower(trim($v));
      if (in_array($s, ['1', 'true', 'yes', 'y', 'on', 'enabled'], true)) return true;
      if (in_array($s, ['0', 'false', 'no', 'n', 'off', 'disabled'], true)) return false;
    }
    return null;
  }
}

if (!function_exists('op__merge_pool')) {
  function op__merge_pool(array &$pool, $source) {
    if ($source === null) return;
    if (!is_array($source)) return;

    // If it's a list like ["priority", "voice_chat"] => treat as flags set to true
    $is_list = array_keys($source) === range(0, count($source) - 1);
    if ($is_list) {
      foreach ($source as $v) {
        if (is_string($v) && $v !== '' && !array_key_exists($v, $pool)) {
          $pool[$v] = true;
        }
      }
      return;
    }

    foreach ($source as $k => $v) {
      if (!is_string($k)) continue;
      if (!array_key_exists($k, $pool)) {
        $pool[$k] = $v;
      }
    }
  }
}

if (!function_exists('op_lb_skip_option')) {
  function op_lb_skip_option(array $row, string $option): bool {
    // mirrors the logic from the order view page
    $game = strtolower(trim((string)($row['game'] ?? '')));
    $isLol = in_array($game, ['lol', 'league-of-legends', 'lol-classic', 'league-of-legends-classic'], true);
    if (in_array($option, ['flash_position', 'is_offline_mode'], true) && !$isLol) return true;
    if (($row['game'] ?? '') === 'val' && $option === 'flash_position') return true;
    // coaching (form_id 15/16): offline mode shouldn't show
    $fid = (int)($row['form_id'] ?? 0);
    if (in_array($fid, [15,16], true) && $option === 'is_offline_mode') return true;
    if ($option === 'vpn_country' && (!empty($row['is_duo']) || in_array($fid, [15,16], true))) return true;
    if (!empty($row['is_duo']) && ($option === 'flash_position' || $option === 'is_offline_mode')) return true;
    return false;
  }
}

if (!function_exists('op_lb_option_selected')) {
  function op_lb_option_selected(array $row, string $option, array $boolOptions): bool {
    if (!array_key_exists($option, $row)) return false;

    if (in_array($option, $boolOptions, true)) {
      return isset($row[$option]) && (int)$row[$option] === 1;
    }

    $v = $row[$option];

    if (is_array($v)) return !empty($v);

    $s = trim((string)$v);
    if ($s === '' || $s === '[]') return false;

    $low = strtolower($s);
    if (in_array($low, ['null','none','n/a','na','-','false','no'], true)) return false;

    return true;
  }
}

if (!function_exists('op_lb_collect_option_chips')) {
  function op_lb_collect_option_chips(array $row): array {
    $fid = (int)($row['form_id'] ?? 0);

    // TFT orders (form_id 21-25): do not show Solo/Duo options block (queue type / flash / offline etc.)
    if (in_array($fid, [21, 22, 23, 24, 25], true)) {
      return ['has' => false, 'title' => '', 'chips' => []];
    }
    // Coaching orders (form_id 15/16): no Solo/Duo options block (only champions + roles)
    if (in_array($fid, [15, 16], true)) {
      return ['has' => false, 'title' => '', 'chips' => []];
    }

    $optionKeys = [
      'roles',
      'champions',
      'agents',
      'flash_position',
      'vpn_country',
      'is_priority',
      'is_streaming',
      'is_solo_only',
      'is_bonus_win',
      'is_offline_mode',
      'is_coaching',
      'is_hidden_duo',
      'is_undercover_winrate',
      'is_moderate_kda'
    ];

    $boolOptions = ['is_priority','is_streaming','is_solo_only','is_bonus_win','is_offline_mode','is_coaching','is_hidden_duo','is_undercover_winrate','is_moderate_kda'];

    $chips = [];

    // For the panel cards we show "extra options" as chips, excluding preference lists like roles/champions/agents
    foreach ($optionKeys as $option) {
      if (in_array($option, ['roles','champions','agents'], true)) continue;
      if (op_lb_skip_option($row, $option)) continue;
      if (!op_lb_option_selected($row, $option, $boolOptions)) continue;

      $ds_opt = function_exists('util_format_option') ? util_format_option($option, $row[$option]) : [$option, $row[$option]];
      $ico = function_exists('util_format_option_emoji') ? util_format_option_emoji($option) : '✨';

      $label = trim(strip_tags((string)($ds_opt[0] ?? $option)));
      $value = trim(strip_tags((string)($ds_opt[1] ?? '')));

      if (in_array($option, $boolOptions, true)) {
        $text = $label;
      } else {
        $text = ($value !== '' && $value !== '[]') ? ($label . ': ' . $value) : $label;
      }

      $chips[] = [
        'ico' => $ico,
        'text' => $text,
      ];
    }

    $isDuo = ((int)($row['is_duo'] ?? 0) === 1);
    return [
      'has' => !empty($chips),
      'title' => $isDuo ? 'Duo options' : 'Solo options',
      'chips' => $chips,
    ];
  }
}

if (!function_exists('op_lb_build_option_pool')) {
  /**
   * Build a merged "options pool" from common nested/json fields in $row.
   * This helps when the panel list data doesn't have flattened keys like 'agents'/'champions'.
   */
  function op_lb_build_option_pool(array $row): array {
    $pool = [];

    // 1) common containers
    $candidates = [
      'options','extra_options','order_options','boost_options','boost_form','form_data','data',
      'details','requirements','meta','metadata','prefs','preferences'
    ];

    foreach ($candidates as $k) {
      if (!array_key_exists($k, $row)) continue;

      $v = $row[$k];

      if (is_array($v)) {
        op__merge_pool($pool, $v);
        continue;
      }

      $decoded = op__json_decode_if_possible($v);
      if (is_array($decoded)) op__merge_pool($pool, $decoded);
    }

    // 2) fallback: scan all json-ish string fields (bounded)
    foreach ($row as $k => $v) {
      if (!is_string($v)) continue;
      $s = trim($v);
      if ($s === '' || strlen($s) > 8000) continue;

      $decoded = op__json_decode_if_possible($s);
      if (is_array($decoded)) op__merge_pool($pool, $decoded);
    }

    return $pool;
  }
}

if (!function_exists('op_lb_pick_pref_value')) {
  function op_lb_pick_pref_value(array $row, array $pool, string $key) {
    if (array_key_exists($key, $row)) return $row[$key];
    if (array_key_exists($key, $pool)) return $pool[$key];

    $syn = [
      'agents' => ['agents','agent','val_agents','agent_pool','agents_pool','preferred_agents','selected_agents','agent_preferences','agents_preferences'],
      'champions' => ['champions','champion','champ_pool','champions_pool','preferred_champions','selected_champions','booster_champions','champion_pool'],
      'roles' => ['roles','role','preferred_roles','selected_roles','booster_roles','role_preferences','roles_preferences'],
    ];

    foreach (($syn[$key] ?? []) as $k) {
      if (array_key_exists($k, $row)) return $row[$k];
      if (array_key_exists($k, $pool)) return $pool[$k];
    }

    return null;
  }
}

if (!function_exists('op_lb_value_selected')) {
  /** Like the view-page selection logic, but for a raw value instead of a row-key. */
  function op_lb_value_selected($v): bool {
    if ($v === null) return false;
    if (is_array($v)) return !empty($v);

    $s = trim((string)$v);
    if ($s === '' || $s === '[]') return false;

    $low = strtolower($s);
    if (in_array($low, ['null','none','n/a','na','-','false','no'], true)) return false;

    return true;
  }
}

if (!function_exists('op_lb_pick_stat_value')) {
  /**
   * Pick a scalar-ish stat value (like LP info) from $row or the merged option pool.
   * Supports multiple possible key names.
   */
  function op_lb_pick_stat_value(array $row, array $pool, array $keys) {
    foreach ($keys as $k) {
      if (!is_string($k) || $k === '') continue;
      if (array_key_exists($k, $row)) return $row[$k];
      if (array_key_exists($k, $pool)) return $pool[$k];
    }
    return null;
  }
}

if (!function_exists('op_lb_normalize_range')) {
  /**
   * Normalize common range/value shapes into a readable string.
   * Examples: ['min'=>10,'max'=>19] => "10-19", [10,19] => "10-19"
   */
  function op_lb_normalize_range($v) {
    if ($v === null) return null;

    if (is_array($v)) {
      // associative range
      $min = null; $max = null;
      foreach (['min','from','start','low','lower'] as $k) { if (array_key_exists($k, $v)) { $min = $v[$k]; break; } }
      foreach (['max','to','end','high','upper'] as $k) { if (array_key_exists($k, $v)) { $max = $v[$k]; break; } }

      if ($min !== null || $max !== null) {
        $minS = ($min === null) ? '' : trim((string)$min);
        $maxS = ($max === null) ? '' : trim((string)$max);
        if ($minS !== '' && $maxS !== '') return $minS . '-' . $maxS;
        if ($minS !== '') return $minS;
        if ($maxS !== '') return $maxS;
      }

      // indexed range
      if (count($v) === 2 && array_keys($v) === [0,1]) {
        $a = trim((string)$v[0]);
        $b = trim((string)$v[1]);
        if ($a !== '' && $b !== '') return $a . '-' . $b;
      }

      // fallback: json
      $j = json_encode($v);
      return ($j !== false) ? $j : null;
    }

    // json-ish string representing a range
    $decoded = op__json_decode_if_possible($v);
    if (is_array($decoded)) {
      return op_lb_normalize_range($decoded);
    }

    $s = trim((string)$v);
    return $s === '' ? null : $s;
  }
}

if (!function_exists('op_lb_collect_lp_rows')) {
  /**
   * Collect LP related rows for display on the card (Current LP + LP Gain, optionally Start LP).
   * Data can come from flattened columns or nested/json fields.
   */
  function op_lb_collect_lp_rows(array $row): array {
    $fid = (int)($row['form_id'] ?? 0);
    $isTft = in_array($fid, [21, 22, 23, 24, 25], true);

    // Valorant: show RR instead of LP gain for specific boost forms
    // (Rank/Win/Placements/Unrated Matches + Coaching)
    $isValRr = (($row['game'] ?? '') === 'val') && in_array($fid, [5, 6, 7, 8, 16], true);

    $rows = [];
    $pool = function_exists('op_lb_build_option_pool') ? op_lb_build_option_pool($row) : [];

    // Games outside the lol/tft/val family (Rocket League, Apex, Marvel Rivals, Wild Rift, Overwatch 2, ...)
    // don't use LP/RR terminology at all — show their own rank labels from the boost form JSON instead.
    $gameRaw = strtolower((string)($row['game'] ?? ''));
    $isLolFamily = in_array($gameRaw, ['lol', 'lol_classic', 'lol-classic'], true);
    $isDynamicGame = !$isLolFamily && !$isTft && $gameRaw !== 'val';

    if ($isDynamicGame) {
        $serviceType = strtolower(trim((string)($row['type'] ?? $row['form_type'] ?? '')));
        $serviceSlug = strtolower(trim((string)($row['slug'] ?? $row['form_slug'] ?? '')));
        $isGamesService = in_array($fid, [38, 43, 44, 46, 49, 50, 52], true)
            || in_array($serviceType, ['win', 'placement'], true)
            || in_array($serviceSlug, ['win-boost', 'placement', 'placement-boost', 'placements-boost'], true);
        $isPlacementService = in_array($fid, [44, 50], true)
            || $serviceType === 'placement'
            || in_array($serviceSlug, ['placement', 'placement-boost', 'placements-boost'], true);
        $jsonData = (!empty($row['form_id']) && function_exists('lb_load_boost_form_json_by_id'))
            ? lb_load_boost_form_json_by_id($row['form_id'])
            : [];
        if (!empty($jsonData) && function_exists('lb_summary_rank_display')) {
            // Use the same authoritative tier/division configuration as the public
            // form, overview title and Discord webhook. Older Rocket League form JSON
            // still says four divisions, which turned stored Division 1 into IV here
            // while the current three-division setup correctly displays it as III.
            $rankConfig = function_exists('lb_generic_game_rank_config')
                ? lb_generic_game_rank_config($gameRaw)
                : null;
            if (is_array($rankConfig)) {
                $jsonData['form_config'] = array_replace(
                    is_array($jsonData['form_config'] ?? null) ? $jsonData['form_config'] : [],
                    $rankConfig
                );
            }
            $startTier = (int)($row['start_tier'] ?? 0);
            $endTier = (int)($row['end_tier'] ?? 0);
            if ($startTier > 0 || $isPlacementService) {
                $rows[] = [
                    'key'   => 'start_rank',
                    'icon'  => 'fa-chart-simple',
                    'label' => $isPlacementService ? 'Last Season Rank' : 'Start Rank',
                    'html'  => htmlspecialchars(lb_summary_rank_display($jsonData, $startTier, $row['start_division'] ?? null, $row['start_lp'] ?? null), ENT_QUOTES, 'UTF-8'),
                ];
            }
            if (!$isGamesService && $endTier > 0) {
                $rows[] = [
                    'key'   => 'end_rank',
                    'icon'  => 'fa-bullseye-arrow',
                    'label' => 'Target Rank',
                    'html'  => htmlspecialchars(lb_summary_rank_display($jsonData, $endTier, $row['end_division'] ?? null, $row['end_lp'] ?? null), ENT_QUOTES, 'UTF-8'),
                ];
            }
        }
        return $rows;
    }

    // For VAL RR-orders we use start_rr/end_rr instead of LP keys.
    if ($isValRr) {
      $defs = [
        'current_rr' => [
          'label' => 'Current RR',
          'icon'  => 'fa-signal-stream',
          'keys'  => ['start_rr','rr_start','startRR','rrStart','current_rr','rr_current','currentRR','rrCurrent'],
        ],
        'target_rr' => [
          'label' => 'Target RR',
          'icon'  => 'fa-bullseye-arrow',
          'keys'  => ['end_rr','rr_end','endRR','rrEnd','target_rr','rr_target','desired_rr','rr_desired'],
        ],
      ];
      $keysToRender = ['current_rr', 'target_rr'];
    } else {
      $defs = [
        'current_lp' => [
          'label' => 'Current LP',
          'icon'  => 'fa-signal-stream',
          'keys'  => ['current_lp','lp_current','currentLP','lpCurrent','lp_now','lpNow','current_lp_range','current_lp_min','current_lp_max','current_lp_from','current_lp_to'],
        ],
        'lp_gain' => [
          'label' => 'LP Gain',
          'icon'  => 'fa-arrow-trend-up',
          'keys'  => ['lp_gain','lpGain','gain_lp','gainLp','lp_gain_range','lp_gain_min','lp_gain_max','lp_gain_from','lp_gain_to','desired_lp_gain','lp_to_gain'],
        ],
        // optional, but often present in LoL boosting orders
        'start_lp' => [
          'label' => 'Start LP',
          'icon'  => 'fa-chart-simple',
          'keys'  => ['start_lp','lp_start','startLP','lpStart','start_lp_range','start_lp_min','start_lp_max','start_lp_from','start_lp_to'],
        ],
      ];
      $keysToRender = ['current_lp', 'lp_gain', 'start_lp'];
    }

    foreach ($keysToRender as $key) {
      if (!$isValRr && $isTft && $key === 'lp_gain') continue;

      $def = $defs[$key] ?? null;
      if (!$def) continue;

      $raw = op_lb_pick_stat_value($row, $pool, $def['keys'] ?? [$key]);
      if (!op_lb_value_selected($raw)) continue;

      $norm = op_lb_normalize_range($raw);
      if (!op_lb_value_selected($norm)) continue;

      // VAL RR: add " RR" suffix when not already present.
      if ($isValRr && in_array($key, ['current_rr', 'target_rr'], true)) {
        $s = trim((string)$norm);
        if ($s !== '' && stripos($s, 'rr') === false) {
          $norm = $s . ' RR';
        }
      }

      // Prefer util_format_option if it knows the key (keeps formatting consistent across the app)
      $label = $def['label'];
      $html  = htmlspecialchars((string)$norm, ENT_QUOTES, 'UTF-8');

      // util_format_option doesn't know our virtual RR keys; keep the labels stable.
      if (!$isValRr && function_exists('util_format_option')) {
        $ds = util_format_option($key, $raw);
        $l  = trim(strip_tags((string)($ds[0] ?? '')));
        $v  = (string)($ds[1] ?? '');
        if ($l !== '') $label = $l;
        if (trim($v) !== '') $html = $v;
      }

      $rows[] = [
        'key'   => $key,
        'icon'  => $def['icon'],
        'label' => $label,
        'html'  => $html,
      ];
    }

    return $rows;
  }
}

if (!function_exists('op_lb_collect_pref_rows')) {
  /**
   * Collect preference-style options (Agents / Champions / Roles) for display on cards.
   * These are NOT treated as "extra option chips" but as their own rows.
   *
   * Important: in the Orders Panel list, these values might not be flattened into $row.
   * So we build an option pool from nested/json fields and pick the right value.
   */
  function op_lb_collect_pref_rows(array $row): array {
    $prefs = [];

    $pool = function_exists('op_lb_build_option_pool') ? op_lb_build_option_pool($row) : [];

    $map = [
      'agents'    => ['icon' => 'fa-trophy-star',      'fallback' => 'Agents'],
      'champions' => ['icon' => 'fa-swords',           'fallback' => 'Booster champions'],
      'roles'     => ['icon' => 'fa-compass-drafting', 'fallback' => 'Booster roles'],
    ];

    foreach (['agents', 'champions', 'roles'] as $k) {
      $val = function_exists('op_lb_pick_pref_value') ? op_lb_pick_pref_value($row, $pool, $k) : ($row[$k] ?? null);
      if (!op_lb_value_selected($val)) continue;

      if (function_exists('util_format_option')) {
        $ds = util_format_option($k, $val); // [label, html/value]
        $label = trim(strip_tags((string)($ds[0] ?? ($map[$k]['fallback'] ?? $k))));
        $html  = (string)($ds[1] ?? '');
      } else {
        $label = $map[$k]['fallback'] ?? $k;
        $html  = htmlspecialchars(is_scalar($val) ? (string)$val : json_encode($val), ENT_QUOTES, 'UTF-8');
      }

      $htmlTrim = trim(strip_tags($html));
      $hasVisual = (stripos($html, '<img') !== false) || (stripos($html, '<svg') !== false) || (stripos($html, 'background-image') !== false);
      if ($htmlTrim === '' && !$hasVisual) continue;
$prefs[] = [
        'key'   => $k,
        'icon'  => $map[$k]['icon'] ?? 'fa-star',
        'label' => $label,
        'html'  => $html,
      ];
    }

    return $prefs;
  }
}



if (!function_exists('op_r5s_role_icon_url')) {
  function op_r5s_role_icon_url(string $role): string {
    $role = function_exists('lb_ranked_5s_normalize_role') ? lb_ranked_5s_normalize_role($role) : $role;
    return ASSET_URL . '/core/main/img/lol/roles/' . $role . '.png';
  }
}


if (!function_exists('op_r5s_role_icon_class')) {
  function op_r5s_role_icon_class(string $role): string {
    $role = function_exists('lb_ranked_5s_normalize_role') ? lb_ranked_5s_normalize_role($role) : $role;
    $map = [
      'TopLane' => 'fa-solid fa-square',
      'Jungle' => 'fa-solid fa-leaf',
      'MidLane' => 'fa-solid fa-diamond',
      'AdCarry' => 'fa-solid fa-crosshairs',
      'Support' => 'fa-solid fa-wand-magic-sparkles',
    ];
    return $map[$role] ?? 'fa-solid fa-circle';
  }
}

if (!function_exists('op_r5s_role_label')) {
  function op_r5s_role_label(string $role): string {
    return function_exists('lb_ranked_5s_role_label') ? lb_ranked_5s_role_label($role) : $role;
  }
}

?>


<!-- Orders Panel First-Visit Modal (matches dashboard modal style) -->
<style>
      #lbOrdersInfoModal .modal-content{
      background: #25282a;
      border: 1px solid rgba(255,255,255,.08);
      border-radius: 18px;
      box-shadow: 0 24px 70px rgba(0,0,0,.6);
    }

  #lbOrdersInfoModal .modal-header,
  #lbOrdersInfoModal .modal-footer{
    border-color: rgba(255,255,255,.08);
    background: #25282a;
  }
  #lbOrdersInfoModal .lb-mi{
    width: 40px; height: 44px;
    border-radius: 14px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    background: rgba(124,92,255,.12);
    border: 1px solid rgba(124,92,255,.22);
    color: #fff;
    flex: 0 0 auto;
  }
  #lbOrdersInfoModal .lb-mi i{font-size: 16px;}

  #lbOrdersInfoModal .nav{
    gap: 10px;
  }
  #lbOrdersInfoModal .nav .nav-link{
    border-radius: 999px;
    padding: 8px 14px;
    font-weight: 900;
    letter-spacing: .08em;
    font-size: 12px;
    text-transform: uppercase;
    background: rgba(0,0,0,.18);
    border: 1px solid rgba(255,255,255,.10);
    color: rgba(255,255,255,.78);
  }
  #lbOrdersInfoModal .nav .nav-link:hover{
    color: #fff;
    border-color: rgba(255,255,255,.16);
  }
  #lbOrdersInfoModal .nav .nav-link.active{
    background: rgba(124,92,255,.18);
    border-color: rgba(124,92,255,.35);
    color: #fff;
  }

  #lbOrdersInfoModal .lb-panel{
    border: 1px solid rgba(255,255,255,.08);
    background: rgba(0,0,0,.18);
    border-radius: 18px;
    padding: 16px;
  }
  #lbOrdersInfoModal .lb-panel h6{
    font-weight: 950;
    margin: 0 0 10px 0;
    letter-spacing: .01em;
  }
  #lbOrdersInfoModal .lb-panel ul{
    margin: 0;
    padding-left: 18px;
    color: rgba(255,255,255,.72);
  }
  #lbOrdersInfoModal .lb-panel li{ margin: 8px 0; }

  #lbOrdersInfoModal .lb-alert{
    margin-top: 14px;
    border-radius: 16px;
    padding: 10px 12px;
    border: 1px solid rgba(255,92,122,.22);
    background: rgba(255,92,122,.10);
    color: rgba(255,240,244,.95);
    font-weight: 900;
    font-size: 12px;
    letter-spacing: .01em;
  }

  /* Keep dialog size similar to other modals */
  #lbOrdersInfoModal .modal-dialog{ max-width: 920px; }

  /* Small screens */
  @media (max-width: 576px){
    #lbOrdersInfoModal .modal-dialog{ margin: 12px; }
    #lbOrdersInfoModal .nav .nav-link{ width: 100%; text-align:center; }
  }
   .lb-cut-pill{
    display:inline-flex;
    align-items:center;
    gap:8px;
    padding:8px 12px;
    border-radius:999px;
    font-weight:950;
    font-size:12px;
    letter-spacing:.06em;
    text-transform:uppercase;
    cursor:pointer;
    user-select:none;

    color: rgba(255,255,255,.92);
    background: rgba(124,92,255,.14);
    border: 1px solid rgba(124,92,255,.35);
    transition: transform .12s ease, box-shadow .12s ease, background .12s ease;
  }

  .lb-cut-pill i{ font-size:14px; }

  .lb-cut-pill__dot{
    width:8px; height:8px;
    border-radius:50%;
    background: rgba(124,92,255,1);
    box-shadow: 0 0 0 4px rgba(124,92,255,.18);
  }

  .lb-cut-pill:hover{
    transform: translateY(-1px);
    background: rgba(124,92,255,.20);
  }

  .lb-cut-pill:active{
    transform: translateY(0px);
  }

  /* EARNING dynamic cut UI */
  .lb-earn-cell{
    display:flex;
    align-items:center;
    justify-content:center;
    gap:10px;
    white-space:nowrap;
  }
  @media (max-width: 768px){
    .lb-earn-cell{ flex-wrap:wrap; white-space:normal; justify-content:flex-start; }
  }

  .lb-earn-amount{
    font-weight: 900;
    letter-spacing: .01em;
  }

  .lb-chip{
    display:inline-flex;
    align-items:center;
    gap:6px;
    padding:6px 10px;
    border-radius:999px;
    font-size:11px;
    font-weight:950;
    letter-spacing:.06em;
    text-transform:uppercase;
    border:1px solid rgba(255,255,255,.10);
    background: rgba(0,0,0,.18);
    color: rgba(255,255,255,.86);
  }
  .lb-chip i{ font-size:12px; opacity:.9; }

  .lb-chip-timer{
    background: rgba(255,255,255,.06);
    border-color: rgba(255,255,255,.12);
  }


  @keyframes lbPriceBump{
    0%   { transform: scale(1);   filter: brightness(1); }
    35%  { transform: scale(1.07); filter: brightness(1.35); }
    100% { transform: scale(1);   filter: brightness(1); }
  }
  .lb-price-bump{
    animation: lbPriceBump 550ms ease;
  }


  /* =========================================================
     Orders Panel — Card View (keeps DataTables working)
     ========================================================= */
  .orders-panel-table.orders-as-cards thead{
    position:absolute;
    left:-9999px;
    top:-9999px;
    width:1px;
    height:1px;
    overflow:hidden;
  }

  .orders-panel-table.orders-as-cards tbody{
    display:grid;
    grid-template-columns: repeat(auto-fill, minmax(340px, 1fr));
    gap: 14px;
    padding: 16px 16px 28px;
    align-items: stretch;
  }

  /* Card = tr */
  .orders-panel-table.orders-as-cards tbody tr{
    display:flex;
    flex-direction: column;
    background: #25282a;
    border: 1px solid #2f3235;
    border-radius: 14px;
    overflow: hidden;
    padding: 0;
    transition: border-color .16s ease, transform .16s ease;
  }
  .orders-panel-table.orders-as-cards tbody tr:hover{
    border-color: rgba(92,74,227,.50);
    transform: translateY(-2px);
  }
  .orders-panel-table.orders-as-cards tbody td.op-card{
    flex: 1;
    display: flex;
    flex-direction: column;
  }
  .orders-panel-table.orders-as-cards tbody td{
    padding: 0 !important;
    border: 0 !important;
  }
  .orders-panel-table.orders-as-cards td.op-hidden{
    display:none !important;
  }

  /* Purple accent stripe at top */
  .orders-panel-table.orders-as-cards tbody tr::before{
    content: '';
    display: block;
    height: 3px;
    background: linear-gradient(90deg, #7c5cff, #b87fff);
    flex-shrink: 0;
  }

  /* Empty state */
  .op-table-wrap.op-empty-active .orders-panel-table.orders-as-cards tbody{ display:none; }
  .op-empty-state{ display:none; padding: 44px 18px; text-align: center; }
  .op-empty-state__box{
    margin: 0 auto; max-width: 420px;
    border-radius: 12px;
    border: 1px solid #2f3235;
    background: #25282a;
    padding: 26px 18px;
  }
  .op-empty-state img{ width: 12rem; max-width: 70%; height: auto; }
  .op-empty-state__title{
    margin-top: 10px; font-weight: 500;
    color: rgba(255,255,255,.90);
  }

/* Card header */
.op-head{
  display:flex;
  align-items:flex-start;
  gap: 10px;
  padding: 14px 14px 0;
  margin-bottom: 10px;
}
.op-head-left{
  display:flex;
  align-items:flex-start;
  gap: 10px;
  min-width: 0;
}
.op-head-icon{
  width: 40px;
  height: 40px;
  border-radius: 10px;
  position: relative;
  display:flex;
  align-items:center;
  justify-content:center;
  background: #2c2f32;
  border: 1px solid #2f3235;
  flex: 0 0 auto;
}
.op-head-icon img{ width: 22px; height: 22px; display:block; }
.op-head-icon .boost-form-svg{ width: 22px !important; height: 22px !important; }

.op-game-badge{
  position: absolute;
  right: -4px;
  bottom: -4px;
  width: 16px;
  height: 16px;
  border-radius: 5px;
  background: #25282a;
  border: 1px solid #2f3235;
  padding: 2px;
  display:flex;
  align-items:center;
  justify-content:center;
}
.op-game-badge img{ width: 10px; height: 10px; display:block; }

.op-head-text{ min-width:0; }

.op-head-title{
  display:flex;
  align-items:center;
  gap: 6px;
  flex-wrap: wrap;
  font-weight: 500;
  font-size: 14px;
  line-height: 1.3;
  margin: 0;
  color: #fff;
}

.orders-panel-table.orders-as-cards tbody tr td.op-card{ position: relative; }
.op-ribbon-new{
  display: inline-flex;
  align-items: center;
  padding: 2px 7px;
  border-radius: 999px;
  font-size: 10px;
  font-weight: 500;
  color: #b087ff;
  background: rgba(124,92,255,.15);
  border: 1px solid rgba(124,92,255,.30);
  white-space: nowrap;
  flex-shrink: 0;
}

.op-head-sub{
  margin-top: 2px;
  font-size: 11px;
  color: #91989e;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

/* KV table */
.op-kv{
  margin: 0 14px 10px;
  display: flex;
  flex-direction: column;
  border-radius: 10px;
  border: 1px solid #2f3235;
  overflow: hidden;
  background: #1e2022;
}
.op-kv-row{
  display:flex;
  align-items:center;
  justify-content: space-between;
  padding: 7px 11px;
  background: transparent;
  border-bottom: 1px solid #2f3235;
}
.op-kv-row:last-child{ border-bottom: none; }
.op-kv-left{
  display:flex;
  align-items:center;
  gap: 7px;
  color: #91989e;
  font-weight: 400;
  font-size: 11px;
  text-transform: uppercase;
  letter-spacing: .06em;
}
.op-kv-left i{ font-size: 13px; }
.op-kv-right{
  color: #c5c8cc;
  font-weight: 500;
  font-size: 12px;
  display: flex;
  align-items: center;
  justify-content: flex-end;
  gap: 6px;
  flex-wrap: wrap;
  text-align: right;
}
.op-kv-right img{ width: 20px; height: 20px; border-radius: 6px; object-fit: cover; }

/* Yes/No badge */
.op-opt-badge{
  display:inline-flex;
  align-items:center;
  gap: 5px;
  padding: 3px 9px;
  border-radius: 999px;
  font-size: 11px;
  font-weight: 500;
}
.op-opt-badge--yes{
  background: rgba(29,158,117,.14);
  color: #3ecfa0;
  border: 1px solid rgba(29,158,117,.28);
}
.op-opt-badge--yes .op-opt-badge__dot{ background: #3ecfa0; }
.op-opt-badge--no{
  background: rgba(255,255,255,.05);
  color: rgba(255,255,255,.45);
  border: 1px solid rgba(255,255,255,.08);
}
.op-opt-badge--no .op-opt-badge__dot{ background: rgba(255,255,255,.35); }
.op-opt-badge__dot{
  width: 5px; height: 5px; border-radius: 50%; flex-shrink: 0;
}

/* Options chips */
.op-options{ margin: 0 14px 10px; }
.op-options-title{
  font-size: 10px; font-weight: 500;
  text-transform: uppercase; letter-spacing: .08em;
  color: rgba(255,255,255,.40); margin-bottom: 6px;
}
.op-opt-chips{ display:flex; flex-wrap: wrap; gap: 6px; }
.op-opt-chip{
  display:inline-flex; align-items:center; gap: 5px;
  padding: 4px 9px; border-radius: 999px;
  background: rgba(255,255,255,.05);
  border: 1px solid rgba(255,255,255,.08);
  color: rgba(255,255,255,.65);
  font-weight: 400; font-size: 11px;
}
.op-opt-emoji{ font-size: 12px; }

/* Notes */
.op-note-box{
  margin: 0 14px 10px;
  border-radius: 10px;
  border: 1px solid rgba(255,165,0,.20);
  background: rgba(255,140,0,.05);
  max-height: 120px;
  overflow-y: auto;
  overflow-x: hidden;
  scrollbar-width: thin;
}
.op-note-empty{
  display:flex; align-items:center; gap: 8px;
  padding: 9px 12px;
  color: rgba(255,255,255,.55); font-size: 12px;
}
.op-note-row{
  display:flex; align-items:flex-start; gap: 8px;
  padding: 9px 12px;
}
.op-note-row + .op-note-row{
  border-top: 1px solid rgba(255,165,0,.12);
}
.op-note-row-ico{
  width: 22px; height: 22px; border-radius: 7px;
  display:flex; align-items:center; justify-content:center;
  background: rgba(255,165,0,.10);
  border: 1px solid rgba(255,165,0,.18);
  color: #e09000; flex: 0 0 auto;
}
.op-note-row-text{
  flex: 1 1 auto;
  min-width: 0;
  font-size: 12px;
  line-height: 1.45;
  color: rgba(255,210,120,.85);
  white-space: normal;
  overflow-wrap: anywhere;
  word-break: break-word;
}
.op-note-row-chip{
  flex: 0 0 auto; margin-left: 6px;
  padding: 3px 8px; border-radius: 999px;
  font-weight: 500; font-size: 11px;
  color: rgba(255,255,255,.60);
  border: 1px solid rgba(255,255,255,.10);
  background: rgba(255,255,255,.05);
}
.op-note-row-chip--client{
  color: #3ecfa0;
  border-color: rgba(29,158,117,.25);
  background: rgba(29,158,117,.08);
}

/* Bottom area */
.op-bottom{
  margin-top: auto;
  display: flex;
  flex-direction: column;
}
.op-bottom-row1{
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 10px;
  padding: 12px 14px;
  border-top: 1px solid #2f3235;
}
.op-earn{ display:flex; flex-direction: column; gap: 2px; }
.op-earn-k{
  font-weight: 500;
  font-size: 10px;
  letter-spacing: .08em;
  text-transform: uppercase;
  color: #91989e;
}
.op-earn-v{
  display:flex;
  align-items:center;
  gap: 8px;
  font-weight: 500;
  font-size: 22px;
  color: #fff;
}
.op-claim-btn{
  border-radius: 10px;
  font-weight: 500;
  font-size: 13px;
  padding: 8px 18px;
  display:inline-flex;
  align-items:center;
  gap: 6px;
  background: #5c4ae3 !important;
  border: none !important;
  color: #fff !important;
  transition: opacity .15s ease, transform .15s ease;
  white-space: nowrap;
}
.op-claim-btn:hover{ opacity: .85; transform: translateY(-1px); }

/* Client footer */
.op-client-pill{
  display:flex;
  align-items:center;
  justify-content:space-between;
  gap: 14px;
  padding: 14px 18px;
  border-top: 1px solid rgba(255,255,255,.075);
  background: linear-gradient(180deg, rgba(255,255,255,.035), rgba(0,0,0,.18));
}
.op-client-pill__left{
  display:flex;
  align-items:center;
  gap: 11px;
  min-width: 0;
}
.op-client-pill__avatar{
  width: 34px;
  height: 34px;
  border-radius: 12px;
  object-fit: cover;
  flex: 0 0 auto;
  border: 1px solid rgba(255,255,255,.12);
  box-shadow: 0 8px 18px rgba(0,0,0,.24);
}
.op-client-pill__avatar--fallback{
  display:flex;
  align-items:center;
  justify-content:center;
  background: radial-gradient(circle at 35% 25%, rgba(124,92,255,.30), rgba(0,0,0,.28));
  border-color: rgba(124,92,255,.24);
  color: rgba(255,255,255,.82);
  font-size: 13px;
}
.op-client-pill__meta{
  min-width: 0;
  display:flex;
  flex-direction:column;
  gap: 2px;
}
.op-client-pill__label{
  font-size: 10px;
  line-height: 1;
  font-weight: 900;
  letter-spacing: .10em;
  text-transform: uppercase;
  color: rgba(255,255,255,.40);
}
.op-client-pill__name{
  font-size: 14px;
  line-height: 1.18;
  font-weight: 850;
  color: rgba(255,255,255,.88);
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}
.op-client-presence{
  flex: 0 0 auto;
  display:inline-flex;
  align-items:center;
  justify-content:center;
  gap: 7px;
  min-width: 92px;
  padding: 8px 12px;
  border-radius: 999px;
  border: 1px solid rgba(255,255,255,.11);
  background: rgba(255,255,255,.055);
  color: rgba(255,255,255,.72);
  font-size: 11px;
  line-height: 1;
  font-weight: 900;
  letter-spacing: .01em;
  white-space: nowrap;
}
.op-client-presence__dot{
  width: 8px;
  height: 8px;
  border-radius: 999px;
  flex: 0 0 auto;
  background: #91989e;
  box-shadow: 0 0 0 3px rgba(145,152,158,.12);
}
.op-client-presence.is-online{
  border-color: rgba(0,201,167,.28);
  background: rgba(0,201,167,.10);
  color: rgba(206,255,244,.92);
}
.op-client-presence.is-online .op-client-presence__dot{
  background: #00c9a7;
  box-shadow: 0 0 0 3px rgba(0,201,167,.16), 0 0 16px rgba(0,201,167,.45);
}
.op-client-presence.is-offline{
  border-color: rgba(145,152,158,.20);
  background: rgba(145,152,158,.08);
  color: rgba(220,224,230,.72);
}
.op-client-presence.is-offline .op-client-presence__dot{
  background: #8f98a3;
  box-shadow: 0 0 0 3px rgba(143,152,163,.13);
}

/* Timer chip */
.lb-chip-timer{
  display:inline-flex; align-items:center; gap: 4px;
  padding: 3px 7px; border-radius: 999px;
  font-size: 11px; font-weight: 500;
  background: rgba(255,255,255,.06);
  border: 1px solid rgba(255,255,255,.10);
  color: rgba(255,255,255,.55);
}
.lb-chip-timer i{ font-size: 12px; }

/* Tooltip */
.op-tooltip{
  position: fixed; z-index: 9999;
  max-width: 400px; padding: 9px 12px;
  border-radius: 10px;
  background: #121314;
  border: 1px solid #2f3235;
  color: #c5c8cc;
  font-size: 12px; line-height: 1.4;
  pointer-events: none; opacity: 0;
  transform: translateY(5px);
  transition: opacity .12s ease, transform .12s ease;
}
.op-tooltip.is-visible{ opacity: 1; transform: translateY(0); }
.op-tooltip:after{
  content: ""; position: absolute;
  width: 8px; height: 8px;
  background: #121314;
  border-left: 1px solid #2f3235;
  border-top: 1px solid #2f3235;
  transform: rotate(45deg);
  left: 12px; top: -5px;
}

.op-head-actions .btn{ border-radius: 999px; }

.op-chip{
  display:inline-flex; align-items:center; gap: 6px;
  padding: 5px 9px; border-radius: 999px;
  font-size: 11px; font-weight: 500;
  border: 1px solid rgba(255,255,255,.10);
  background: rgba(255,255,255,.05);
  color: rgba(255,255,255,.65);
  white-space: nowrap;
}
.op-chip i{ font-size: 12px; }

  
/* Toolbar (Search + Filters) */
.op-toolbar{
  display:flex;
  align-items:center;
  gap: 10px;
  flex-wrap: nowrap;
  justify-content:flex-end;
}

.op-toolbar .input-group{
  width: 340px;
  max-width: 100%;
  height: 44px;
  border-radius: 999px;
  overflow:hidden;
  padding-left: 20px;
  display:flex;
  align-items:center;
  background: rgba(0,0,0,.16);
  border: 1px solid rgba(255,255,255,.10);
}

.op-toolbar .input-group .input-group-text{
  display: flex;
  align-items: center;
  padding-left: 20px;
  padding-right: 0;
  background: transparent;
  border: 0;
  color: rgba(255,255,255,.78);
}

.op-toolbar .input-group .input-group-text i{
  margin-left: 20px;
}

.op-toolbar .input-group .form-control{
  height: 44px;
  background: transparent;
  border: 0;
  color: rgba(255,255,255,.92);
  padding-left: 15px;
  padding-right: 16px;
}
.op-toolbar .input-group .form-control::placeholder{
  color: rgba(255,255,255,.55);
}
.op-toolbar .input-group:focus-within{
  border-color: rgba(124,92,255,.35);
  box-shadow: 0 0 0 1px rgba(124,92,255,.22) inset;
}

.op-pill-group{
  display:inline-flex;
  align-items:center;
  gap: 4px;
  padding: 4px;
  border-radius: 999px;
  background: rgba(0,0,0,.16);
  border: 1px solid rgba(255,255,255,.10);
}

.op-pill{
  appearance:none;
  border: 0;
  background: transparent;
  color: rgba(255,255,255,.74);
  padding: 10px 14px;
  border-radius: 999px;
  font-weight: 950;
  letter-spacing: .02em;
  font-size: 12px;
  line-height: 1;
  cursor: pointer;
  transition: background .12s ease, color .12s ease, box-shadow .12s ease;
  user-select:none;
  white-space: nowrap;
  height: 36px;
  display:inline-flex;
  align-items:center;
  justify-content:center;
}

.op-pill:hover{
  color: rgba(255,255,255,.92);
  background: rgba(255,255,255,.06);
}

.op-pill.op-pill--active{
  color: #fff;
  background: rgba(124,92,255,.18);
  box-shadow: 0 0 0 1px rgba(124,92,255,.35) inset;
}

@media (max-width: 768px){
  .op-toolbar{ justify-content:flex-start; flex-wrap: wrap; }
  .op-toolbar .input-group{ width: 100%; }
}

  /* make earning block look like a chip */
  .orders-panel-table.orders-as-cards .booster-price{
    display:inline-flex;
    align-items:center;
    gap: 10px;
    padding: 7px 10px;
    border-radius: 999px;
    border: 1px solid rgba(124,92,255,.28);
    background: rgba(124,92,255,.10);
  }

  .orders-panel-table.orders-as-cards .lb-earn-cell{
    gap: 8px;
  }

  /* Options block inside a card (Play with booster, Priority, etc.) */
  .op-options{
    margin-top: 12px;
    border: 1px solid rgba(255,255,255,.08);
    background: rgba(0,0,0,.14);
    border-radius: 18px;
    padding: 14px;
  }
  .op-options-title{
    font-weight: 950;
    letter-spacing: .10em;
    text-transform: uppercase;
    font-size: 12px;
    color: rgba(255,255,255,.88);
    margin: 0 0 12px 0;
  }
  .op-opt-row{
    display:flex;
    align-items:center;
    justify-content: space-between;
    gap: 12px;
    padding: 12px 12px;
    border-radius: 16px;
    border: 1px solid rgba(255,255,255,.08);
    background: rgba(255,255,255,.04);
  }
  .op-opt-row + .op-opt-row{ margin-top: 10px; }

  .op-opt-left{
    display:flex;
    align-items:center;
    gap: 12px;
    min-width: 0;
  }

  .op-opt-ico{
    width: 40px;
    height: 44px;
    border-radius: 14px;
    display:inline-flex;
    align-items:center;
    justify-content:center;
    border: 1px solid rgba(255,255,255,.10);
    background: rgba(0,0,0,.18);
    color: rgba(255,255,255,.92);
    flex: 0 0 auto;
  }
  .op-opt-ico i{ font-size: 15px; }

  .op-opt-label{
    font-weight: 900;
    color: rgba(255,255,255,.92);
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
  }

  .op-opt-badge{
    display:inline-flex;
    align-items:center;
    gap: 8px;
    padding: 7px 12px;
    border-radius: 999px;
    font-weight: 950;
    letter-spacing: .08em;
    text-transform: uppercase;
    font-size: 11px;
    border: 1px solid rgba(255,255,255,.12);
    background: rgba(255,255,255,.05);
    color: rgba(255,255,255,.82);
    white-space: nowrap;
  }

  .op-opt-badge__dot{ width: 8px; height: 8px; border-radius: 50%; background: rgba(255,255,255,.55); }
  .op-opt-badge--yes{
    border-color: rgba(42, 201, 169, .35);
    background: rgba(42, 201, 169, .12);
    color: rgba(230,255,250,.95);
  }
  .op-opt-badge--yes .op-opt-badge__dot{ background: rgba(42, 201, 169, 1); box-shadow: 0 0 0 4px rgba(42, 201, 169, .16); }

  .op-opt-badge--no{
    border-color: rgba(255, 90, 90, .34);
    background: rgba(255, 90, 90, .12);
    color: rgba(255, 235, 235, .95);
  }
  .op-opt-badge--no .op-opt-badge__dot{ background: rgba(255, 90, 90, 1); box-shadow: 0 0 0 4px rgba(255, 90, 90, .14); }

  @media (max-width: 420px){
    .orders-panel-table.orders-as-cards tbody{
      grid-template-columns: 1fr;
      padding: 10px;
    }
  }



  /* OPB: pin bottom section (earning/claim) to bottom of card */
  .orders-panel-table.orders-as-cards tbody tr{
    display:flex;
    flex-direction: column;
  }
  .orders-panel-table.orders-as-cards tbody tr td.op-card{
    display:flex;
    flex-direction: column;
    flex: 1 1 auto;
    width: 100%;
  }


  /* =========================================================
     Orders Panel — Full Card Redesign V2
     Wider, calmer, balanced cards with capped champion areas
     ========================================================= */
  .orders-panel-table.orders-as-cards{
    border-collapse: separate !important;
    border-spacing: 0 !important;
  }

  .orders-panel-table.orders-as-cards tbody{
    grid-template-columns: repeat(auto-fit, minmax(520px, 1fr));
    gap: 18px;
    padding: 18px;
    align-items: start;
  }

  .orders-panel-table.orders-as-cards tbody tr{
    min-height: 0;
    background:
      radial-gradient(circle at 8% 0%, rgba(124,92,255,.16), transparent 30%),
      linear-gradient(180deg, #272a2d 0%, #202326 100%);
    border: 1px solid rgba(255,255,255,.075);
    border-radius: 22px;
    box-shadow: 0 18px 42px rgba(0,0,0,.22);
    overflow: hidden;
  }

  .orders-panel-table.orders-as-cards tbody tr::before{
    height: 4px;
    background: linear-gradient(90deg, #765cff 0%, #a875ff 45%, rgba(124,92,255,.18) 100%);
  }

  .orders-panel-table.orders-as-cards tbody tr:hover{
    transform: translateY(-2px);
    border-color: rgba(124,92,255,.48);
    box-shadow: 0 22px 54px rgba(0,0,0,.28);
  }

  .orders-panel-table.orders-as-cards tbody td.op-card{
    min-height: 0;
    width: 100%;
  }

  .op-head{
    padding: 18px 18px 0;
    margin-bottom: 14px;
  }

  .op-head-left{
    width: 100%;
    align-items: center;
    gap: 14px;
  }

  .op-head-icon{
    width: 50px;
    height: 50px;
    border-radius: 16px;
    background: rgba(255,255,255,.045);
    border-color: rgba(255,255,255,.09);
    box-shadow: inset 0 1px 0 rgba(255,255,255,.04);
  }

  .op-head-icon img,
  .op-head-icon .boost-form-svg{
    width: 27px !important;
    height: 27px !important;
  }

  .op-game-badge{
    right: -5px;
    bottom: -5px;
    width: 20px !important;
    height: 20px !important;
    border-radius: 7px;
    padding: 3px;
    background: #202326;
  }

  .op-head-text{
    min-width: 0;
    flex: 1 1 auto;
  }

  .op-head-title{
    gap: 8px;
    font-size: 16px;
    line-height: 1.25;
    font-weight: 850;
    letter-spacing: .01em;
  }

  .op-head-title > span:first-child{
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
    max-width: 100%;
  }

  .op-ribbon-new{
    padding: 3px 9px;
    font-size: 10px;
    font-weight: 850;
    color: #d9c6ff;
    background: rgba(124,92,255,.18);
    border-color: rgba(124,92,255,.36);
  }

  .op-head-sub{
    margin-top: 5px;
    font-size: 12px;
    color: rgba(197,200,204,.74);
  }

  .op-kv{
    margin: 0 18px 14px;
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 10px;
    border: 0;
    background: transparent;
    overflow: visible;
  }

  .op-kv-row{
    min-width: 0;
    display: grid;
    grid-template-columns: minmax(130px, 38%) minmax(0, 1fr);
    align-items: center;
    gap: 10px;
    padding: 12px 13px;
    border: 1px solid rgba(255,255,255,.075) !important;
    border-radius: 16px;
    background: rgba(0,0,0,.15);
  }

  .op-kv-row:last-child{
    border-bottom: 1px solid rgba(255,255,255,.075) !important;
  }

  .op-kv-left{
    min-width: 0;
    gap: 9px;
    font-size: 11px;
    font-weight: 750;
    letter-spacing: .075em;
    color: rgba(197,200,204,.68);
  }

  .op-kv-left span{
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
  }

  .op-kv-left i{
    width: 16px;
    text-align: center;
    font-size: 14px;
    color: rgba(155,141,255,.82);
  }

  .op-kv-right{
    min-width: 0;
    justify-content: flex-end;
    gap: 5px;
    font-size: 13px;
    font-weight: 750;
    color: rgba(255,255,255,.9);
    line-height: 1.25;
  }

  .op-kv-right img,
  .op-kv-right .avatar,
  .op-kv-right [class*="champ"],
  .op-kv-right [class*="role"] img{
    width: 22px !important;
    height: 22px !important;
    border-radius: 7px !important;
    flex: 0 0 auto;
  }

  .op-kv-row:has(.op-kv-right img:nth-of-type(8)),
  .op-kv-row:has(.op-kv-right img:nth-of-type(9)){
    grid-column: 1 / -1;
    grid-template-columns: 160px minmax(0, 1fr);
    align-items: start;
  }

  .op-kv-row:has(.op-kv-right img:nth-of-type(8)) .op-kv-right,
  .op-kv-row:has(.op-kv-right img:nth-of-type(9)) .op-kv-right{
    justify-content: flex-start;
    max-height: 82px;
    overflow: auto;
    padding-right: 4px;
    scrollbar-width: thin;
  }

  .op-opt-badge{
    padding: 6px 11px;
    font-size: 11px;
    font-weight: 850;
    letter-spacing: .06em;
  }

  .op-options{
    margin: 0 18px 14px;
    padding: 13px;
    border-radius: 18px;
    background: rgba(0,0,0,.12);
    border-color: rgba(255,255,255,.075);
  }

  .op-options-title{
    margin-bottom: 10px;
    font-size: 12px;
    font-weight: 900;
    color: rgba(255,255,255,.92);
  }

  .op-opt-chips{
    gap: 7px;
  }

  .op-opt-chip{
    padding: 7px 11px;
    font-size: 12px;
    font-weight: 650;
    color: rgba(255,255,255,.78);
    background: rgba(255,255,255,.055);
    border-color: rgba(255,255,255,.095);
  }

  .op-note-box{
    margin: 0 18px 14px;
    border-radius: 16px;
    max-height: 120px;
    overflow-y: auto;
    overflow-x: hidden;
    scrollbar-width: thin;
  }

  .op-note-box::-webkit-scrollbar{
    width: 6px;
  }

  .op-note-box::-webkit-scrollbar-track{
    background: transparent;
  }

  .op-note-box::-webkit-scrollbar-thumb{
    background: rgba(255,255,255,.15);
    border-radius: 999px;
  }

  .op-note-box::-webkit-scrollbar-thumb:hover{
    background: rgba(255,255,255,.25);
  }

  .op-bottom{
    margin-top: 2px;
    background: rgba(0,0,0,.14);
    border-top: 1px solid rgba(255,255,255,.075);
  }

  .op-bottom-row1{
    padding: 14px 18px;
    border-top: 0;
  }

  .op-earn-k{
    font-size: 11px;
    font-weight: 800;
    letter-spacing: .09em;
    color: rgba(197,200,204,.75);
  }

  .op-earn-v{
    font-size: 24px;
    font-weight: 950;
  }

  .orders-panel-table.orders-as-cards .booster-price{
    padding: 8px 13px;
    border-radius: 16px;
    background: rgba(124,92,255,.14);
    border-color: rgba(124,92,255,.34);
  }

  .op-claim-btn{
    min-width: 116px;
    justify-content: center;
    padding: 11px 20px;
    border-radius: 14px;
    font-size: 14px;
    font-weight: 850;
    background: linear-gradient(135deg, #6752f2 0%, #7c5cff 100%) !important;
    box-shadow: 0 10px 24px rgba(92,74,227,.24);
  }

  .op-client-pill{
    padding: 14px 18px;
  }

  .op-client-pill__avatar,
  .op-client-pill__avatar--fallback{
    width: 34px;
    height: 34px;
  }

  .op-client-pill__name{
    font-size: 14px;
  }

  @media (max-width: 1280px){
    .orders-panel-table.orders-as-cards tbody{
      grid-template-columns: 1fr;
    }
  }

  @media (max-width: 680px){
    .orders-panel-table.orders-as-cards tbody{
      grid-template-columns: 1fr;
      padding: 12px;
    }
    .op-kv{
      grid-template-columns: 1fr;
      margin-left: 14px;
      margin-right: 14px;
    }
    .op-kv-row,
    .op-kv-row:has(.op-kv-right img:nth-of-type(8)),
    .op-kv-row:has(.op-kv-right img:nth-of-type(9)){
      grid-column: auto;
      grid-template-columns: 1fr;
      gap: 8px;
    }
    .op-kv-right{
      justify-content: flex-start;
      text-align: left;
    }
    .op-head,
    .op-bottom-row1,
    .op-client-pill{
      padding-left: 14px;
      padding-right: 14px;
    }
    .op-options,
    .op-note-box{
      margin-left: 14px;
      margin-right: 14px;
    }
  }


  /* Fix long note display, wrap inside the card and remove the oversized floating tooltip */
  .op-note-box{
    max-height: 120px !important;
    overflow-y: auto !important;
    overflow-x: hidden !important;
    scrollbar-width: thin;
  }

  .op-note-row{
    min-width: 0;
  }

  .op-note-row-text{
    min-width: 0;
    white-space: normal !important;
    overflow-wrap: anywhere !important;
    word-break: break-word !important;
  }

  .op-note-box .op-tooltip,
  .op-note-row-text + .op-tooltip{
    display: none !important;
  }

  .op-note-box::-webkit-scrollbar{
    width: 6px;
  }

  .op-note-box::-webkit-scrollbar-track{
    background: transparent;
  }

  .op-note-box::-webkit-scrollbar-thumb{
    background: rgba(255,255,255,.15);
    border-radius: 999px;
  }

  .op-note-box::-webkit-scrollbar-thumb:hover{
    background: rgba(255,255,255,.25);
  }


  .op-r5s-customer-role{
    display:inline-flex;
    align-items:center;
    gap:8px;
  }

  .op-r5s-role-icon{
    width:24px;
    height:24px;
    display:inline-flex;
    align-items:center;
    justify-content:center;
    flex:0 0 24px;
  }

  .op-r5s-role-icon img{
    width:100% !important;
    height:100% !important;
    object-fit:contain;
    border-radius:0 !important;
    opacity:.9;
  }

  .op-r5s-role-icon i{
    display:none;
    color:#a7a4ff;
    font-size:18px;
  }

  .op-r5s-role-icon.is-fallback i{
    display:inline-flex;
  }

  .op-r5s-role-icon.is-fallback img{
    display:none !important;
  }

  .op-r5s-lanes{
    margin:0 18px 16px;
    padding:18px;
    border:1px solid rgba(255,255,255,.075);
    border-radius:22px;
    background:linear-gradient(180deg, rgba(255,255,255,.035), rgba(0,0,0,.12));
    box-shadow:inset 0 1px 0 rgba(255,255,255,.035);
  }

  .op-r5s-lanes__head{
    display:flex;
    align-items:center;
    justify-content:space-between;
    gap:14px;
    margin-bottom:16px;
  }

  .op-r5s-lanes__head > div{
    display:flex;
    align-items:center;
    gap:10px;
    color:rgba(255,255,255,.88);
    font-size:13px;
    font-weight:950;
    letter-spacing:.055em;
    text-transform:uppercase;
  }

  .op-r5s-lanes__head i{
    color:#9b8dff;
    font-size:17px;
  }

  .op-r5s-lanes__head small{
    color:rgba(255,255,255,.55);
    font-size:12px;
    font-weight:850;
    white-space:nowrap;
  }

  .op-r5s-lanes__grid{
    display:grid;
    grid-template-columns:repeat(4, minmax(0,1fr));
    gap:12px;
  }

  .op-r5s-lane-card{
    min-width:0;
    min-height:132px;
    padding:14px 12px;
    display:flex;
    flex-direction:column;
    align-items:center;
    justify-content:center;
    gap:11px;
    border:1px solid rgba(255,255,255,.075);
    border-radius:18px;
    background:rgba(255,255,255,.035);
  }

  .op-r5s-role-icon--large{
    width:42px;
    height:42px;
    flex-basis:42px;
  }

  .op-r5s-role-icon--large i{
    font-size:30px;
    color:#d7b46a;
  }

  .op-r5s-lane-card > span:not(.op-r5s-role-icon){
    color:rgba(255,255,255,.78);
    font-size:13px;
    font-weight:950;
    line-height:1.1;
    text-align:center;
  }

  .op-r5s-lane-claim{
    width:100%;
    min-height:38px;
    padding:0 12px;
    border:1px solid rgba(139,92,246,.8);
    border-radius:12px;
    background:rgba(139,92,246,.13);
    color:#fff;
    font-size:13px;
    font-weight:950;
    cursor:pointer;
    transition:transform .16s ease, background .16s ease, box-shadow .16s ease;
  }

  .op-r5s-lane-claim:hover{
    transform:translateY(-1px);
    background:rgba(139,92,246,.24);
    box-shadow:0 12px 24px rgba(99,102,241,.18);
  }

  .op-r5s-bottom-hint{
    color:rgba(255,255,255,.62);
    font-size:14px;
    font-weight:800;
  }

  @media (max-width:760px){
    .op-r5s-lanes{
      margin:0 14px 14px;
      padding:14px;
      border-radius:18px;
    }

    .op-r5s-lanes__head{
      align-items:flex-start;
      flex-direction:column;
      gap:6px;
    }

    .op-r5s-lanes__grid{
      grid-template-columns:repeat(2, minmax(0,1fr));
      gap:10px;
    }

    .op-r5s-lane-card{
      min-height:116px;
      border-radius:16px;
    }
  }


  /* Ranked 5s polish: compact claim modal and cleaner lane cards */
  #opClaimModal .modal-dialog,
  #opClaimModal .lb-modal-dialog,
  #opClaimModal [class*="modal-dialog"]{
    max-width: 720px !important;
  }

  #opClaimModal .modal-content,
  #opClaimModal .lb-modal-content,
  #opClaimModal [class*="modal-content"]{
    max-height: min(88vh, 760px) !important;
    overflow: hidden !important;
  }

  #opClaimModal .modal-body,
  #opClaimModal .lb-modal-body,
  #opClaimModal [class*="modal-body"]{
    max-height: calc(min(88vh, 760px) - 88px) !important;
    overflow-y: auto !important;
    scrollbar-width: thin;
  }

  #opClaimModal .op-claim-modal,
  #opClaimModal .op-claim-card,
  #opClaimModal .op-claim-content{
    max-height: min(88vh, 760px) !important;
  }

  #opClaimModal .op-claim-head,
  #opClaimModal .op-claim-summary,
  #opClaimModal .op-claim-warning,
  #opClaimModal .op-claim-confirm,
  #opClaimModal .op-claim-verify,
  #opClaimModal .op-claim-footer{
    margin-bottom: 12px !important;
  }

  #opClaimModal .op-claim-head{
    padding-bottom: 4px !important;
  }

  #opClaimModal .op-claim-warning{
    padding: 13px 15px !important;
    border-radius: 16px !important;
  }

  #opClaimModal .op-claim-warning strong,
  #opClaimModal .op-claim-warning b{
    font-size: 13px !important;
    line-height: 1.25 !important;
  }

  #opClaimModal .op-claim-warning p{
    margin: 4px 0 0 !important;
    font-size: 13px !important;
    line-height: 1.35 !important;
  }

  #opClaimModal .op-claim-confirm{
    padding: 14px 16px !important;
    border-radius: 16px !important;
  }

  #opClaimModal .op-claim-confirm ul{
    margin: 6px 0 0 18px !important;
  }

  #opClaimModal .op-claim-confirm li{
    margin: 2px 0 !important;
    font-size: 13px !important;
    line-height: 1.35 !important;
  }

  #opClaimModal .op-claim-confirm p{
    margin: 7px 0 0 !important;
    font-size: 13px !important;
    line-height: 1.35 !important;
  }

  #opClaimModal .op-claim-verify{
    padding: 14px 16px !important;
    border-radius: 16px !important;
  }

  #opClaimModal .op-claim-verify h4,
  #opClaimModal .op-claim-verify .op-verify-question{
    font-size: 18px !important;
    line-height: 1.2 !important;
  }

  #opClaimModal .op-claim-verify input{
    height: 46px !important;
  }

  #opClaimModal .op-claim-footer{
    padding-top: 8px !important;
  }

  .op-r5s-lanes{
    padding: 14px !important;
    border-radius: 18px !important;
  }

  .op-r5s-lanes__head{
    margin-bottom: 12px !important;
  }

  .op-r5s-lanes__grid{
    grid-template-columns: repeat(4, minmax(0, 1fr)) !important;
    gap: 10px !important;
  }

  .op-r5s-lane-card{
    min-height: 94px !important;
    padding: 12px 10px !important;
    gap: 7px !important;
    border-radius: 15px !important;
  }

  .op-r5s-role-icon--large{
    width: 34px !important;
    height: 34px !important;
    flex-basis: 34px !important;
  }

  .op-r5s-role-icon--large img{
    width: 34px !important;
    height: 34px !important;
    object-fit: contain !important;
    opacity: .78 !important;
    filter: none !important;
  }

  .op-r5s-role-icon--large i{
    font-size: 25px !important;
    color: #d4b16a !important;
  }

  .op-r5s-lane-card > span:not(.op-r5s-role-icon){
    font-size: 12px !important;
    font-weight: 900 !important;
  }

  .op-r5s-lane-claim{
    min-height: 32px !important;
    max-width: 82px !important;
    padding: 0 12px !important;
    font-size: 12px !important;
    border-radius: 11px !important;
  }

  @media (max-width: 760px){
    #opClaimModal .modal-dialog,
    #opClaimModal .lb-modal-dialog,
    #opClaimModal [class*="modal-dialog"]{
      max-width: calc(100vw - 20px) !important;
      margin: 10px auto !important;
    }

    #opClaimModal .modal-content,
    #opClaimModal .lb-modal-content,
    #opClaimModal [class*="modal-content"]{
      max-height: calc(100dvh - 20px) !important;
    }

    #opClaimModal .modal-body,
    #opClaimModal .lb-modal-body,
    #opClaimModal [class*="modal-body"]{
      max-height: calc(100dvh - 116px) !important;
    }

    .op-r5s-lanes__grid{
      grid-template-columns: repeat(2, minmax(0, 1fr)) !important;
    }

    .op-r5s-lane-card{
      min-height: 92px !important;
    }
  }


  /* Ranked 5s panel cleanup */
  .op-card[data-form-id="19"] .op-pref-row--flash_position,
  .op-card[data-form-id="19"] .op-pref-row--is_offline_mode,
  .op-card[data-form-id="19"] .op-pref-row--offline_mode,
  .op-card[data-form-id="29"] .op-pref-row--roles,
  .op-card[data-form-id="29"] .op-options,
  .op-card[data-form-id="29"] .op-pref-row--flash_position,
  .op-card[data-form-id="29"] .op-pref-row--is_offline_mode,
  .op-card[data-form-id="29"] .op-pref-row--offline_mode {
    display: none !important;
  }

  .op-card[data-form-id="29"] .op-r5s-customer-role {
    justify-content: flex-end;
  }

  .op-card[data-form-id="29"] .op-r5s-role-icon--customer {
    width: 28px;
    height: 28px;
    flex-basis: 28px;
  }

  .op-card[data-form-id="29"] .op-r5s-role-icon--customer img {
    width: 28px !important;
    height: 28px !important;
    opacity: .95;
  }

  .op-card[data-form-id="29"] .op-r5s-role-icon--customer i {
    font-size: 20px;
  }


  /* Ranked 5s panel cleanup, always hide duplicate Roles row and let title wrap. */
  .op-card[data-form-id="29"] .op-pref-row--roles,
  .op-card[data-form-id="29"] .op-pref-row--role,
  .op-card[data-form-id="29"] .op-kv-row:has(.op-kv-left span:first-child:last-child),
  .op-card[data-form-id="29"] .op-kv-row:has(.op-kv-left span) {
  }

  .op-card[data-form-id="29"] .op-card-title,
  .op-card[data-form-id="29"] .op-title,
  .op-card[data-form-id="29"] .op-order-title,
  .op-card[data-form-id="29"] [class*="title"] span {
    white-space: normal !important;
    overflow: visible !important;
    text-overflow: unset !important;
    display: inline !important;
    line-height: 1.18 !important;
  }

  .op-card[data-form-id="29"] .op-head,
  .op-card[data-form-id="29"] .op-card-head,
  .op-card[data-form-id="29"] .op-card-header {
    align-items: flex-start !important;
  }

  .op-card[data-form-id="29"] .op-head-main,
  .op-card[data-form-id="29"] .op-card-main,
  .op-card[data-form-id="29"] .op-title-wrap {
    min-width: 0 !important;
    overflow: visible !important;
  }


  /* Ranked 5s final panel polish, title never ellipsizes, claim modal always claimable */
  .op-card[data-form-id="29"] .op-card-top,
  .op-card[data-form-id="29"] .op-head,
  .op-card[data-form-id="29"] .op-card-head,
  .op-card[data-form-id="29"] .op-card-header,
  .op-card[data-form-id="29"] .op-order-head {
    height: auto !important;
    min-height: 0 !important;
    align-items: flex-start !important;
    overflow: visible !important;
  }

  .op-card[data-form-id="29"] .op-title,
  .op-card[data-form-id="29"] .op-card-title,
  .op-card[data-form-id="29"] .op-order-title,
  .op-card[data-form-id="29"] .op-boost-title,
  .op-card[data-form-id="29"] .op-title span,
  .op-card[data-form-id="29"] .op-card-title span,
  .op-card[data-form-id="29"] .op-order-title span,
  .op-card[data-form-id="29"] .op-boost-title span,
  .op-card[data-form-id="29"] h3,
  .op-card[data-form-id="29"] h3 span {
    max-width: none !important;
    width: auto !important;
    white-space: normal !important;
    overflow: visible !important;
    text-overflow: unset !important;
    display: block !important;
    line-height: 1.18 !important;
    word-break: normal !important;
    overflow-wrap: anywhere !important;
  }

  .op-card[data-form-id="29"] .op-meta,
  .op-card[data-form-id="29"] .op-subtitle,
  .op-card[data-form-id="29"] .op-card-subtitle {
    white-space: normal !important;
    overflow: visible !important;
    text-overflow: unset !important;
  }

  #opClaimModal .modal-dialog,
  #opClaimModal [class*="modal-dialog"] {
    max-width: 700px !important;
    margin-top: 18px !important;
    margin-bottom: 18px !important;
  }

  #opClaimModal .modal-content,
  #opClaimModal [class*="modal-content"] {
    max-height: calc(100dvh - 36px) !important;
    overflow: hidden !important;
  }

  #opClaimModal .modal-body,
  #opClaimModal [class*="modal-body"] {
    max-height: calc(100dvh - 128px) !important;
    overflow-y: auto !important;
    padding-bottom: 10px !important;
    scrollbar-width: thin;
  }

  #opClaimModal .op-claim-head,
  #opClaimModal .op-claim-summary,
  #opClaimModal .op-claim-warning,
  #opClaimModal .op-claim-confirm,
  #opClaimModal .op-claim-verify {
    margin-bottom: 10px !important;
  }

  #opClaimModal .op-claim-summary {
    min-height: 0 !important;
  }

  #opClaimModal .op-claim-warning {
    padding: 10px 13px !important;
    border-radius: 14px !important;
  }

  #opClaimModal .op-claim-warning p,
  #opClaimModal .op-claim-warning div,
  #opClaimModal .op-claim-warning span {
    font-size: 12px !important;
    line-height: 1.28 !important;
  }

  #opClaimModal .op-claim-confirm {
    padding: 11px 14px !important;
    border-radius: 14px !important;
  }

  #opClaimModal .op-claim-confirm label,
  #opClaimModal .op-claim-confirm p {
    font-size: 12px !important;
    line-height: 1.25 !important;
  }

  #opClaimModal .op-claim-confirm ul {
    margin: 5px 0 0 18px !important;
  }

  #opClaimModal .op-claim-confirm li {
    margin: 1px 0 !important;
    font-size: 12px !important;
    line-height: 1.28 !important;
  }

  #opClaimModal .op-claim-verify {
    padding: 11px 14px !important;
    border-radius: 14px !important;
  }

  #opClaimModal .op-claim-verify .op-verify-label,
  #opClaimModal .op-claim-verify small,
  #opClaimModal .op-claim-verify h6 {
    margin-bottom: 6px !important;
    font-size: 11px !important;
  }

  #opClaimModal .op-claim-verify h4,
  #opClaimModal .op-claim-verify .op-verify-question {
    font-size: 17px !important;
    line-height: 1.18 !important;
    margin: 0 !important;
  }

  #opClaimModal .op-claim-verify input,
  #opClaimModal .op-claim-verify .form-control {
    height: 42px !important;
    min-height: 42px !important;
  }

  #opClaimModal .modal-footer,
  #opClaimModal .op-claim-footer,
  #opClaimModal [class*="footer"] {
    position: sticky !important;
    bottom: 0 !important;
    z-index: 5 !important;
    padding-top: 8px !important;
    padding-bottom: 8px !important;
    margin-top: 0 !important;
    background: linear-gradient(180deg, rgba(20,20,30,0), rgba(20,20,30,.98) 28%) !important;
  }

  #opClaimModal .op-claim-footer .btn,
  #opClaimModal .modal-footer .btn,
  #opClaimModal button[type="submit"] {
    min-height: 44px !important;
  }

  @media (max-width: 760px) {
    #opClaimModal .modal-dialog,
    #opClaimModal [class*="modal-dialog"] {
      width: calc(100vw - 18px) !important;
      max-width: calc(100vw - 18px) !important;
      margin: 9px auto !important;
    }

    #opClaimModal .modal-content,
    #opClaimModal [class*="modal-content"] {
      max-height: calc(100dvh - 18px) !important;
    }

    #opClaimModal .modal-body,
    #opClaimModal [class*="modal-body"] {
      max-height: calc(100dvh - 116px) !important;
    }
  }


  /* Ranked 5s, show full order title in .op-head-title */
  .op-card[data-form-id="29"] .op-head-title {
    width: 100% !important;
    max-width: 100% !important;
    min-width: 0 !important;
    display: flex !important;
    flex-wrap: wrap !important;
    align-items: center !important;
    gap: 6px 10px !important;
    overflow: visible !important;
    white-space: normal !important;
    line-height: 1.18 !important;
  }

  .op-card[data-form-id="29"] .op-head-title > span:first-child {
    flex: 1 1 100% !important;
    min-width: 0 !important;
    max-width: 100% !important;
    display: block !important;
    white-space: normal !important;
    overflow: visible !important;
    text-overflow: unset !important;
    word-break: normal !important;
    overflow-wrap: anywhere !important;
    line-height: 1.18 !important;
  }

  .op-card[data-form-id="29"] .op-head-title .op-ribbon-new {
    flex: 0 0 auto !important;
    display: inline-flex !important;
    width: auto !important;
    max-width: none !important;
    white-space: nowrap !important;
    overflow: visible !important;
    text-overflow: unset !important;
  }


  /* Force long order titles to wrap instead of ellipsis */
  .op-head-title {
    min-width: 0 !important;
    max-width: 100% !important;
    width: 100% !important;
    display: grid !important;
    grid-template-columns: minmax(0, 1fr) auto !important;
    align-items: start !important;
    column-gap: 8px !important;
    row-gap: 6px !important;
    white-space: normal !important;
    overflow: visible !important;
    text-overflow: unset !important;
  }

  .op-head-title > span:first-child {
    min-width: 0 !important;
    max-width: 100% !important;
    width: 100% !important;
    display: block !important;
    white-space: normal !important;
    overflow: visible !important;
    text-overflow: unset !important;
    word-break: normal !important;
    overflow-wrap: anywhere !important;
    line-height: 1.18 !important;
  }

  .op-head-title .op-ribbon-new {
    width: auto !important;
    max-width: none !important;
    display: inline-flex !important;
    white-space: nowrap !important;
    overflow: visible !important;
    text-overflow: unset !important;
    justify-self: start !important;
  }

  .op-head-main,
  .op-head-copy,
  .op-card-head__main,
  .op-order-head__main {
    min-width: 0 !important;
    overflow: visible !important;
  }

  .op-card:has(.op-head-title span) .op-head,
  .op-card:has(.op-head-title span) .op-card-head,
  .op-card:has(.op-head-title span) .op-order-head {
    height: auto !important;
    min-height: 0 !important;
    overflow: visible !important;
    align-items: flex-start !important;
  }


  .op-r5s-lane-card.is-claimed {
    opacity: .48 !important;
    filter: grayscale(.45);
    border-color: rgba(255,255,255,.055) !important;
    background: rgba(255,255,255,.022) !important;
  }

  .op-r5s-lane-card.is-claimed .op-r5s-role-icon--large img,
  .op-r5s-lane-card.is-claimed .op-r5s-role-icon--large i {
    opacity: .58 !important;
  }

  .op-r5s-lane-claimed {
    min-height: 32px;
    width: 100%;
    max-width: 118px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 0 10px;
    border-radius: 11px;
    border: 1px solid rgba(255,255,255,.08);
    background: rgba(255,255,255,.045);
    color: rgba(255,255,255,.48);
    font-size: 11px;
    font-weight: 950;
    text-align: center;
    line-height: 1.1;
  }

</style>

<div class="modal fade" id="lbOrdersInfoModal" tabindex="-1" aria-labelledby="lbOrdersInfoModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">

      <div class="modal-header">
        <div class="d-flex align-items-start gap-3">
          <div class="lb-mi">
            <i class="fa-duotone fa-circle-info"></i>
          </div>
          <div>
            <h5 class="modal-title" id="lbOrdersInfoModalLabel" style="font-weight:950; letter-spacing:.01em;">Orders Panel — Important Info</h5>
            <div style="color:rgba(255,255,255,.72); font-size:13px; line-height:1.45; margin-top:4px;">
              Quick guide (shown once). Please read before taking orders.
            </div>
          </div>
        </div>

        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <div class="modal-body">
        <ul class="nav nav-pills" id="lbOrdersInfoTabs" role="tablist" style="margin-bottom:14px;">
          <li class="nav-item" role="presentation">
            <button class="nav-link active" id="lb-tab-cut" data-bs-toggle="pill" data-bs-target="#lb-pane-cut" type="button" role="tab" aria-controls="lb-pane-cut" aria-selected="true">Dynamic Cut</button>
          </li>
          <li class="nav-item" role="presentation">
            <button class="nav-link" id="lb-tab-limits" data-bs-toggle="pill" data-bs-target="#lb-pane-limits" type="button" role="tab" aria-controls="lb-pane-limits" aria-selected="false">Order Limits</button>
          </li>
          <li class="nav-item" role="presentation">
            <button class="nav-link" id="lb-tab-higher" data-bs-toggle="pill" data-bs-target="#lb-pane-higher" type="button" role="tab" aria-controls="lb-pane-higher" aria-selected="false">Higher Limit</button>
          </li>
        </ul>

        <div class="tab-content" id="lbOrdersInfoTabsContent">

          <!-- Step 1 -->
          <div class="tab-pane fade show active" id="lb-pane-cut" role="tabpanel" aria-labelledby="lb-tab-cut" tabindex="0">
            <div class="lb-panel">
              <h6>Dynamic Cut System</h6>
              <ul>
                <li>Every booster starts with the <strong>same % cut</strong>.</li>
                <li>The cut <strong>increases every 90 seconds</strong> until it reaches a <strong>maximum</strong>.</li>
                <li>The <strong>price you see is your exact earning</strong> — <strong>no fees</strong>, no hidden costs.</li>
              </ul>
              <div class="lb-alert">Reminder: Never share customer contact info or communicate outside the official order chat.</div>
            </div>
          </div>

          <!-- Step 2 -->
          <div class="tab-pane fade" id="lb-pane-limits" role="tabpanel" aria-labelledby="lb-tab-limits" tabindex="0">
            <div class="lb-panel">
              <h6>Order Limits</h6>
              <ul>
                <li>Your starting limit is <strong>2 active orders</strong>.</li>
                <li>You can have max <strong>1 Solo</strong> and <strong>1 Duo</strong> at the same time.</li>
                <li>Every booster starts with an order limit up to <strong>Emerald II</strong>.</li>
                <li>Your limit increases after you complete multiple <strong>Emerald IV → Emerald II</strong> orders.</li>
              </ul>
              <div class="lb-alert" style="border-color:rgba(124,92,255,.24); background:rgba(124,92,255,.10);">
                Paused order and need another slot? Open a Discord ticket and ask for an <strong>extra order slot</strong>.
              </div>
            </div>
          </div>

          <!-- Step 3 -->
          <div class="tab-pane fade" id="lb-pane-higher" role="tabpanel" aria-labelledby="lb-tab-higher" tabindex="0">
            <div class="lb-panel">
              <h6>Higher Limit (Fast Request)</h6>
              <ul>
                <li>Pay <strong>€25 insurance upfront</strong>.</li>
                <li>Send <strong>high-elo proofs</strong> from other boosting websites (completed orders / profile stats).</li>
                <li>We may ask for a <strong>screenshare</strong> or the <strong>profile name</strong> to verify.</li>
                <li>Open a <strong>Discord ticket</strong> to request the upgrade.</li>
              </ul>
              <div class="mt-3">
                <a class="btn btn-primary" target="_blank" rel="noopener" href="https://discord.com/channels/926928301807771708/1207383976239702087">
                  <i class="fa-brands fa-discord me-2"></i> Open Discord Ticket
                </a>
              </div>
            </div>
          </div>

        </div>
      </div>

      <div class="modal-footer d-flex justify-content-between">
        <div class="d-flex gap-2">
          <button type="button" class="btn btn-secondary" id="lbIntroCancel" data-bs-dismiss="modal">Close</button>
          <button type="button" class="btn btn-secondary" id="lbIntroBack" style="display:none;">Back</button>
        </div>

        <div class="d-flex gap-2">
          <button type="button" class="btn btn-primary" id="lbIntroNext">
            Next <i class="fa-solid fa-arrow-right ms-2"></i>
          </button>
          <button type="button" class="btn btn-primary" id="lbIntroDone" style="display:none;">
            I Understand — Continue <i class="fa-solid fa-check ms-2"></i>
          </button>
        </div>
      </div>

    </div>
  </div>
</div>

<!-- Card -->
<div class="card">
    <!-- Header -->
    <div class="card-header">
        <div class="row justify-content-between align-items-center flex-grow-1">
            <div class="col-12 col-md">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="d-flex align-items-center gap-3">
                      <h5 class="card-header-title mb-0">Orders Panel</h5>
                    
                      <button type="button" class="lb-cut-pill" id="lbOpenOrdersInfo">
                        <span class="lb-cut-pill__dot"></span>
                        <i class="fa-duotone fa-circle-info"></i>
                        <span>Cut & Limits</span>
                      </button>
                    </div>
                    <!-- Notifications handled globally in main layout -->
                </div>
            </div>

            <div class="col-auto">
                <div class="op-toolbar">
                    <!-- Search -->
                    <div class="input-group input-group-merge input-group-flush">
                        <div class="input-group-prepend input-group-text">
                            <i class="fa-duotone fa-search"></i>
                        </div>
                        <input id="datatableWithSearchInput" type="search" class="form-control"
                            placeholder="Search orders" aria-label="Search">
                    </div>
                    <!-- End Search -->

                    <!-- Region filter -->
                    <div class="op-pill-group" id="opRegionFilter" aria-label="Region filter">
                        <button type="button" class="op-pill op-pill--active" data-region="any">Any</button>
                        <button type="button" class="op-pill" data-region="eu">EU</button>
                        <button type="button" class="op-pill" data-region="na">NA</button>
                    </div>

                    <!-- Queue filter -->
                    <div class="op-pill-group" id="opQueueFilter" aria-label="Queue filter">
                        <button type="button" class="op-pill op-pill--active" data-queue="any">Any</button>
                        <button type="button" class="op-pill" data-queue="solo">Solo</button>
                        <button type="button" class="op-pill" data-queue="duo">Duo</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- End Header -->

    <!-- Table -->
    <div class="table-responsive datatable-custom op-table-wrap" id="opTableWrap">
        <table
            class="js-datatable table table-borderless table-thead-bordered table-nowrap table-align-middle card-table orders-panel-table orders-as-cards"
            data-hs-datatables-options='{
                    "columnDefs": [{
                        "targets": [4],
                        "orderable": false
                    }],
                   "order": [
                        [3, "desc"]
                    ],
                   "info": {
                     "totalQty": "#datatableEntriesInfoTotalQty"
                   },
                   "entries": "#datatableEntries",
                   "search": "#datatableWithSearchInput",
                   "isResponsive": false,
                   "isShowPaging": false,
                   "pagination": "datatableWithSearchPagination"
                 }' id="orders_table">
            <thead class="thead-light">
                <tr>
                    <th>Title</th>
                    <th>Order ID</th>
                    <th>Earning</th>
                    <th>Created At</th>
                    <th class="text-end">Action</th>
                </tr>
            </thead>

            <tbody>
                <?php foreach ($data as $row): ?>
                    <?php
                    $booster_price = function_exists('calculate_effective_booster_cut_amount')
                        ? calculate_effective_booster_cut_amount($row)
                        : calculate_booster_cut($row);
                    $cut_meta = function_exists('calculate_booster_cut_meta')
                        ? calculate_booster_cut_meta($row)
                        : ['next_change_in' => null];

                    $next_change_in = $cut_meta['next_change_in'] ?? null;
                    if ((int)($row['form_id'] ?? 0) === RANKED_5S_FORM_ID || (string)($row['type'] ?? '') === 'ranked-5s') {
                        $next_change_in = null;
                    }
                    if ((int)($row['form_id'] ?? 0) === RANKED_5S_FORM_ID || (string)($row['type'] ?? '') === 'ranked-5s') {
                        $next_change_in = null;
                    }
                    $op_opt = function_exists('op_lb_collect_option_chips') ? op_lb_collect_option_chips($row) : ['has' => false, 'title' => 'Options', 'chips' => []];
                    $op_is_duo = ((int)($row['form_id'] ?? 0) === 19) || ((int)($row['is_duo'] ?? 0) === 1);
                    if ((int)($row['form_id'] ?? 0) === 19) { $row['is_duo'] = 1; }
                    $op_region = '';
                    if (!empty($row['server'])) {
                      $op_region = strtolower((string)util_format_region_from_server(strtolower((string)$row['server'])));
                    } elseif (!empty($row['region'])) {
                      $op_region = strtolower((string)$row['region']);
                    } elseif (!empty($row['server_region'])) {
                      $op_region = strtolower((string)$row['server_region']);
                    } elseif (!empty($row['customer_region'])) {
                      $op_region = strtolower((string)$row['customer_region']);
                    }
                    // normalize common variants
                    if ($op_region === 'euw' || $op_region === 'eune' || $op_region === 'eu') $op_region = 'eu';
                    if ($op_region === 'north america' || $op_region === 'usa' || $op_region === 'us') $op_region = 'na';

                    $op_queue = $op_is_duo ? 'duo' : 'solo';

$op_fid = (int)($row['form_id'] ?? 0);
$op_is_ranked_5s = ($op_fid === RANKED_5S_FORM_ID || (string)($row['type'] ?? '') === 'ranked-5s');
$op_is_multi_booster = $op_is_ranked_5s || (in_array($op_fid, [4, 19], true) && max(1, (int)($row['boosters'] ?? 1)) > 1);
$op_is_tft = in_array($op_fid, [21, 22, 23, 24, 25], true);

                    $op_prefs = function_exists('op_lb_collect_pref_rows') ? op_lb_collect_pref_rows($row) : [];
                    if ($op_fid === 19) {
                      $op_prefs = array_values(array_filter($op_prefs, static function($p){
                        return !in_array((string)($p['key'] ?? ''), ['flash_position', 'is_offline_mode', 'offline_mode'], true);
                      }));
                    }
                    if ($op_is_ranked_5s) {
                      $op_prefs = array_values(array_filter($op_prefs, static function($p){ return (string)($p['key'] ?? '') !== 'roles'; }));
                    }
                    $op_lp_rows = function_exists('op_lb_collect_lp_rows') ? op_lb_collect_lp_rows($row) : [];
$op_r5s_customer_role = $op_is_ranked_5s && function_exists('lb_ranked_5s_normalize_role') ? lb_ranked_5s_normalize_role($row['roles'] ?? '') : '';
$op_r5s_required = $op_is_multi_booster && function_exists('lb_multi_booster_required_count') ? lb_multi_booster_required_count((int)($row['id'] ?? 0)) : max(1, min(4, (int)($row['boosters'] ?? 1)));
$op_r5s_claimed = $op_is_multi_booster && function_exists('lb_multi_booster_claimed_count') ? lb_multi_booster_claimed_count((int)($row['id'] ?? 0)) : 0;
$op_r5s_available_roles = $op_is_ranked_5s && function_exists('lb_ranked_5s_available_roles') ? lb_ranked_5s_available_roles((int)($row['id'] ?? 0), $op_r5s_customer_role) : [];

$op_r5s_all_roles = ['TopLane', 'Jungle', 'MidLane', 'AdCarry', 'Support'];
$op_r5s_claimed_roles = [];
$op_r5s_current_booster_joined = false;
if (!empty($op_is_multi_booster) && function_exists('db_get_rows')) {
  $op_r5s_claimed_rows = db_get_rows('order_boosters', [
    'order_id' => (int)($row['id'] ?? 0),
    'status' => 'ACTIVE',
    'select' => 'booster_id,role'
  ]);
  if (is_array($op_r5s_claimed_rows)) {
    foreach ($op_r5s_claimed_rows as $opClaimedRow) {
      if ((int)($opClaimedRow['booster_id'] ?? 0) === (int)BOOSTER_ID) {
        $op_r5s_current_booster_joined = true;
      }
      $opClaimedRole = function_exists('lb_ranked_5s_normalize_role')
        ? lb_ranked_5s_normalize_role($opClaimedRow['role'] ?? '')
        : trim((string)($opClaimedRow['role'] ?? ''));
      if ($opClaimedRole !== '') {
        $op_r5s_claimed_roles[$opClaimedRole] = true;
      }
    }
  }
}
if (!empty($op_is_ranked_5s) && $op_r5s_customer_role !== '') {
  $op_r5s_claimed_roles[$op_r5s_customer_role] = true;
}
$op_r5s_display_roles = !empty($op_is_ranked_5s)
  ? array_values(array_filter($op_r5s_all_roles, static function($role) use ($op_r5s_customer_role) {
      return $role !== $op_r5s_customer_role;
    }))
  : [];

// Ranked 5s: a booster who already joined must never see the same order as claimable again.
if (!empty($op_is_multi_booster) && $op_r5s_current_booster_joined) {
  continue;
}

// Hide fully staffed Ranked 5s orders from the Booster Orders Panel.
if (!empty($op_is_multi_booster) && (int)$op_r5s_required > 0 && (int)$op_r5s_claimed >= (int)$op_r5s_required) {
  continue;
}


if (!empty($op_is_ranked_5s) && !empty($op_prefs) && is_array($op_prefs)) {
  $op_prefs = array_values(array_filter($op_prefs, static function($p) {
    $key = strtolower((string)($p['key'] ?? ''));
    $label = strtolower((string)($p['label'] ?? ''));
    return !in_array($key, ['roles', 'role'], true) && $label !== 'roles' && $label !== 'role';
  }));
}

$op_game_raw = strtolower(trim((string)($row['game'] ?? '')));
$op_is_cs2 = in_array($op_game_raw, ['cs2', 'counter-strike-2', 'counterstrike2', 'counter-strike', 'csgo'], true);
$op_is_ow2 = in_array((int)($row['form_id'] ?? 0), [48, 49, 50], true)
  || in_array($op_game_raw, ['ow2', 'overwatch', 'overwatch-2', 'overwatch2'], true);
$op_is_marvel = in_array((int)($row['form_id'] ?? 0), [37, 38, 40, 41], true)
  || in_array($op_game_raw, ['rivals', 'marvel-rivals', 'marvel_rivals', 'marvelrivals'], true);
$op_is_rocket_league = in_array((int)($row['form_id'] ?? 0), [42, 43, 44], true)
  || in_array($op_game_raw, ['rl', 'rocket-league', 'rocket_league', 'rocketleague'], true);
$op_is_wild_rift = in_array((int)($row['form_id'] ?? 0), [51, 52], true)
  || in_array($op_game_raw, ['wild-rift', 'wildrift', 'lol-wild-rift', 'lol_wild_rift'], true);
$op_is_win_service = in_array((int)($row['form_id'] ?? 0), [2, 6, 22, 31, 38, 43, 46, 49, 52], true)
  || strtolower(trim((string)($row['type'] ?? ''))) === 'win'
  || strtolower(trim((string)($row['slug'] ?? ''))) === 'win-boost';

// Valorant orders (form ids from boost_forms: 5-9 (+16 coaching); user requested 5-6-7-8-9-15, we include 15/16 for safety)
$op_is_val = ($op_game_raw === 'val' || str_contains($op_game_raw, 'valorant') || in_array($op_fid, [5,6, 7, 8, 16], true));

// Game icon (visual distinction between all games on the card, including dynamically added ones)
$op_game_icon_url = function_exists('util_game_icon_url') ? (util_game_icon_url($row['game'] ?? '') ?: null) : null;
$op_game_icon_alt = function_exists('util_game_display_name') ? util_game_display_name($row['game'] ?? '') : '';

$op_game_short = function_exists('util_game_display_name') ? util_game_display_name($row['game'] ?? '') : strtoupper((string)($row['game'] ?? ''));
$op_server_short = $op_is_cs2 ? '' : (!empty($row['server']) ? strtoupper((string)$row['server']) : (!empty($row['region']) ? strtoupper((string)$row['region']) : ''));
$op_type_short = !empty($row['type'])? (function_exists('util_format_default_type')? util_format_default_type((string)$row['type']): ucwords(str_replace('_',' ', (string)$row['type']))): '';
$op_date_short = function_exists('util_format_date_display') ? util_format_date_display($row['created_at']) : (string)($row['created_at'] ?? '');
$op_created_ts = op__parse_ts($row['created_at'] ?? null);
if ($op_created_ts === 0) { $op_created_ts = op__parse_ts($row['created'] ?? null); }
if ($op_created_ts === 0) { $op_created_ts = op__parse_ts($row['date_created'] ?? null); }
$op_paid_ts = op__parse_ts($row['paid_at'] ?? null);
$op_ref_ts = ($op_paid_ts > 0) ? $op_paid_ts : $op_created_ts;
$op_is_new = ($op_ref_ts > 0) && ((time() - $op_ref_ts) <= 1800); // 30 min since paid_at (fallback created_at)
$op_details_parts = array_filter([$op_game_short, $op_server_short, $op_type_short, 'Order #' . (int)($row['id'] ?? 0), $op_date_short], function($v){ return trim((string)$v) !== ''; });
$op_details_line = implode(' · ', $op_details_parts);

$op_boost_overview = function_exists('util_format_boost_overview') ? util_format_boost_overview($row['game'], $row['type'], $row) : '';
$op_icon_html = function_exists('util_boost_form_icon_html') ? util_boost_form_icon_html($row['icon'] ?? '', 1.35, 'text-body') : '';
if (!empty($op_is_ranked_5s)) {
  $op_r5s_boosters_for_cut = max(1, min(4, (int)($row['boosters'] ?? $op_r5s_required ?? 1)));
  $booster_price = (int)floor(((int)($row['price'] ?? 0) * 50 / 100) / $op_r5s_boosters_for_cut);
  $next_change_in = null;
}
if (!empty($op_is_multi_booster) && empty($op_is_ranked_5s)) {
  $booster_price = (int)floor((((int)($row['price'] ?? 0) * 50) / 100) / max(1, (int)$op_r5s_required));
  $next_change_in = null;
}
if (!empty($op_is_ranked_5s)) {
  $op_r5s_boosters_for_cut = max(1, min(4, (int)($row['boosters'] ?? $op_r5s_required ?? 1)));
  $booster_price = (int)floor(((int)($row['price'] ?? 0) * 50 / 100) / $op_r5s_boosters_for_cut);
  $next_change_in = null;
}
$op_earning_text = util_format_currency_display($row['currency']) . util_format_price_display($booster_price);

// Notes (only booster + client)
$op_note_booster = '';
$op_note_client  = '';

// 1) fetch from order_notes table (newest first), only types booster/client
$op_note_rows = function_exists('db_get_rows')
  ? db_get_rows('order_notes', [
      'order_id' => (int)($row['id'] ?? 0),
      'type' => ['eq' => ['booster', 'client', 'customer']],
      'order' => 'id,DESC',
      'limit' => 20
    ], true)
  : [];

if (is_array($op_note_rows) && !empty($op_note_rows)) {
  foreach ($op_note_rows as $n) {
    $t = strtolower((string)($n['type'] ?? ''));
    if ($t === 'customer') $t = 'client';

    $txt = (string)($n['order_note'] ?? '');
    if ($txt === '') continue;

    if ($t === 'booster' && $op_note_booster === '') $op_note_booster = $txt;
    if ($t === 'client'  && $op_note_client  === '') $op_note_client  = $txt;

    if ($op_note_booster !== '' && $op_note_client !== '') break;
  }
}

// 2) fallback: notes stored on the order row itself (some installs)
if ($op_note_client === '') {
  $op_note_client = trim((string)($row['client_note'] ?? $row['customer_note'] ?? $row['client_notes'] ?? ''));
}
if ($op_note_booster === '') {
  $op_note_booster = trim((string)($row['admin_note'] ?? $row['admin_notes'] ?? $row['booster_note'] ?? ''));
}
                    ?>
                    <tr data-region="<?= htmlspecialchars($op_region, ENT_QUOTES, 'UTF-8') ?>" data-queue="<?= $op_queue ?>">
                        <!-- Visible card column -->
                        
<td class="op-card">
    <div class="op-head">
        <div class="op-head-left">
            <div class="op-head-icon">
              <?= !empty($op_icon_html) ? $op_icon_html : '<i class="fa-duotone fa-bolt"></i>' ?>

              <?php if (!empty($op_game_icon_url)): ?>
                <img
                  class="op-game-badge"
                  src="<?= htmlspecialchars($op_game_icon_url, ENT_QUOTES, 'UTF-8') ?>"
                  alt="<?= htmlspecialchars($op_game_icon_alt, ENT_QUOTES, 'UTF-8') ?>"
                  width="18"
                  height="18"
                  loading="lazy"
                >
              <?php endif; ?>
            </div>

            <div class="op-head-text">
              <div class="op-head-title">
                <span>
                  <?= $op_boost_overview ?>
                </span>
                <?php if (!empty($op_is_new)): ?><span class="op-ribbon-new">NEW</span><?php endif; ?>
              </div>
              <div class="op-head-sub"><?= htmlspecialchars($op_details_line, ENT_QUOTES, 'UTF-8') ?></div>
            </div>
        </div>
    </div>

<div class="op-kv">
  <?php if (!$op_is_tft && !$op_is_ow2 && !($op_is_wild_rift && $op_is_win_service) && !empty($row['queue_type'])): ?>
    <div class="op-kv-row">
      <div class="op-kv-left">
        <i class="fa-duotone fa-layer-group"></i>
        <span><?= $op_is_wild_rift ? 'Ranked Marks' : 'Queue type' ?></span>
      </div>
      <div class="op-kv-right"><?= $op_is_wild_rift ? util_format_ranked_marks($row['queue_type']) : util_format_default_type($row['queue_type']) ?></div>
    </div>
  <?php endif; ?>

  <?php if ($op_is_ow2): ?>
    <?php if (!empty($row['server'])): ?>
    <div class="op-kv-row">
      <div class="op-kv-left"><i class="fa-duotone fa-server"></i><span>Server</span></div>
      <div class="op-kv-right"><?= htmlspecialchars(util_format_default_type((string)$row['server']), ENT_QUOTES, 'UTF-8') ?></div>
    </div>
    <?php endif; ?>
    <?php if (!empty($row['platform'])): ?>
    <div class="op-kv-row">
      <div class="op-kv-left"><i class="fa-duotone fa-gamepad-modern"></i><span>Platform</span></div>
      <div class="op-kv-right"><?= htmlspecialchars((string)$row['platform'], ENT_QUOTES, 'UTF-8') ?></div>
    </div>
    <?php endif; ?>
    <?php if (!empty($row['queue_type'])): ?>
    <div class="op-kv-row">
      <div class="op-kv-left"><i class="fa-duotone fa-user-shield"></i><span>Role</span></div>
      <div class="op-kv-right"><?= htmlspecialchars(util_format_default_type($row['queue_type']), ENT_QUOTES, 'UTF-8') ?></div>
    </div>
    <?php endif; ?>
  <?php endif; ?>

  <?php if ($op_is_marvel): ?>
    <?php if (!empty($row['server'])): ?>
    <div class="op-kv-row">
      <div class="op-kv-left"><i class="fa-duotone fa-server"></i><span>Server</span></div>
      <div class="op-kv-right"><?= htmlspecialchars(util_format_default_type((string)$row['server']), ENT_QUOTES, 'UTF-8') ?></div>
    </div>
    <?php endif; ?>
    <?php if (!empty($row['platform'])): ?>
    <div class="op-kv-row">
      <div class="op-kv-left"><i class="fa-duotone fa-gamepad-modern"></i><span>Platform</span></div>
      <div class="op-kv-right"><?= htmlspecialchars(util_format_platform($row['platform']), ENT_QUOTES, 'UTF-8') ?></div>
    </div>
    <?php endif; ?>
  <?php endif; ?>

  <?php if ($op_is_rocket_league): ?>
    <?php if (!empty($row['server'])): ?>
    <div class="op-kv-row">
      <div class="op-kv-left"><i class="fa-duotone fa-server"></i><span>Server</span></div>
      <div class="op-kv-right"><?= htmlspecialchars(util_format_default_type((string)$row['server']), ENT_QUOTES, 'UTF-8') ?></div>
    </div>
    <?php endif; ?>
    <?php if (!empty($row['platform'])): ?>
    <div class="op-kv-row">
      <div class="op-kv-left"><i class="fa-duotone fa-gamepad-modern"></i><span>Platform</span></div>
      <div class="op-kv-right"><?= htmlspecialchars(util_format_platform($row['platform']), ENT_QUOTES, 'UTF-8') ?></div>
    </div>
    <?php endif; ?>
  <?php endif; ?>

<?php if (!in_array((int)($row['form_id'] ?? 0), [15,16,21,22,23,24,25,29], true)): ?>
  <div class="op-kv-row">
    <div class="op-kv-left">
      <i class="fa-duotone fa-user-group"></i>
      <span>Play with booster</span>
    </div>
    <div class="op-opt-badge <?= $op_is_duo ? 'op-opt-badge--yes' : 'op-opt-badge--no' ?>">
      <span class="op-opt-badge__dot"></span>
      <span><?= $op_is_duo ? 'YES' : 'NO' ?></span>
    </div>
  </div>
<?php endif; ?>

<?php if (!empty($op_is_ranked_5s) && !empty($op_r5s_customer_role)): ?>
  <div class="op-kv-row op-pref-row op-pref-row--r5s-customer-role">
    <div class="op-kv-left">
      <i class="fa-duotone fa-compass-drafting"></i>
      <span>Customer Role</span>
    </div>
    <div class="op-kv-right op-r5s-customer-role">
      <span class="op-r5s-role-icon op-r5s-role-icon--customer" title="<?= htmlspecialchars(op_r5s_role_label($op_r5s_customer_role), ENT_QUOTES, 'UTF-8') ?>">
        <img src="<?= htmlspecialchars(op_r5s_role_icon_url($op_r5s_customer_role), ENT_QUOTES, 'UTF-8') ?>" alt="<?= htmlspecialchars(op_r5s_role_label($op_r5s_customer_role), ENT_QUOTES, 'UTF-8') ?>" loading="lazy" onerror="this.style.display='none';this.parentNode.classList.add('is-fallback');">
        <i class="<?= htmlspecialchars(op_r5s_role_icon_class($op_r5s_customer_role), ENT_QUOTES, 'UTF-8') ?>"></i>
      </span>
    </div>
  </div>
<?php endif; ?>

<?php if (!empty($op_lp_rows)): ?>
  <?php foreach ($op_lp_rows as $lp): ?>
    <div class="op-kv-row">
      <div class="op-kv-left">
        <i class="fa-duotone <?= htmlspecialchars((string)($lp['icon'] ?? 'fa-chart-line'), ENT_QUOTES, 'UTF-8') ?>"></i>
        <span><?= htmlspecialchars((string)($lp['label'] ?? ''), ENT_QUOTES, 'UTF-8') ?></span>
      </div>
      <div class="op-kv-right"><?= $lp['html'] ?></div>
    </div>
  <?php endforeach; ?>
<?php endif; ?>

<?php if (!empty($op_prefs)): ?>
  <?php foreach ($op_prefs as $p): ?>
    <?php $op_pref_key = preg_replace('/[^a-z0-9_-]/i', '', (string)($p['key'] ?? 'pref')); ?>
    <div class="op-kv-row op-pref-row op-pref-row--<?= htmlspecialchars($op_pref_key, ENT_QUOTES, 'UTF-8') ?>">
      <div class="op-kv-left">
        <i class="fa-duotone <?= htmlspecialchars((string)($p['icon'] ?? 'fa-star'), ENT_QUOTES, 'UTF-8') ?>"></i>
        <span><?= htmlspecialchars((string)($p['label'] ?? ''), ENT_QUOTES, 'UTF-8') ?></span>
      </div>
      <div class="op-kv-right"><?= $p['html'] ?></div>
    </div>
  <?php endforeach; ?>
<?php endif; ?>

</div>

<?php if ($op_fid !== 19 && !empty($op_opt['has']) && !empty($op_opt['chips'])): ?>
  <div class="op-options">
    <div class="op-options-title"><?= htmlspecialchars((string)($op_opt['title'] ?? 'Options'), ENT_QUOTES, 'UTF-8') ?></div>

    <div class="op-opt-chips">
      <?php foreach ($op_opt['chips'] as $c): ?>
        <span class="op-opt-chip">
          <span class="op-opt-emoji"><?= $c['ico'] ?></span>
          <span><?= htmlspecialchars((string)($c['text'] ?? ''), ENT_QUOTES, 'UTF-8') ?></span>
        </span>
      <?php endforeach; ?>
    </div>
  </div>
<?php endif; ?>


<?php
  if (!isset($op_client_id)) {
    $op_client_name = '';
    $op_client_icon = '';
    $op_client_id = (int)($row['client_id'] ?? $row['customer_id'] ?? 0);
    if ($op_client_id > 0 && function_exists('db_get_row')) {
      $op_client_row = db_get_row('clients', ['id' => $op_client_id], 1);
      $op_client_icon = trim((string)($op_client_row['icon'] ?? ''));
      $op_client_name = trim((string)($op_client_row['username'] ?? ''));
      if ($op_client_name === '') $op_client_name = trim((string)($op_client_row['name'] ?? ''));
      if ($op_client_name === '') $op_client_name = trim((string)($op_client_row['display_name'] ?? ''));
      if ($op_client_name === '') $op_client_name = trim((string)($op_client_row['email'] ?? ''));
      if ($op_client_name === '') $op_client_name = 'Client #' . $op_client_id;
    }
  }
?>

<?php if (!empty($op_is_ranked_5s) && !empty($op_r5s_display_roles)): ?>
  <div class="op-r5s-lanes">
    <div class="op-r5s-lanes__head">
      <div>
        <i class="fa-duotone fa-people-group"></i>
        <span>Booster Lanes</span>
      </div>
      <small><?= (int)$op_r5s_claimed ?>/<?= (int)$op_r5s_required ?> claimed</small>
    </div>
    <div class="op-r5s-lanes__grid">
      <?php foreach ($op_r5s_display_roles as $opLane): ?>
        <?php
          $opLaneClaimed = !empty($op_r5s_claimed_roles[$opLane]);
          $opLaneCanClaim = !$opLaneClaimed
            && (int)$op_r5s_claimed < (int)$op_r5s_required
            && in_array(($row['status'] ?? ''), ['PAID','PROCESSING','IN_PROGRESS'], true);
        ?>
        <div class="op-r5s-lane-card <?= $opLaneClaimed ? 'is-claimed' : '' ?>">
          <span class="op-r5s-role-icon op-r5s-role-icon--large">
            <img src="<?= htmlspecialchars(op_r5s_role_icon_url($opLane), ENT_QUOTES, 'UTF-8') ?>" alt="" loading="lazy" onerror="this.style.display='none';this.parentNode.classList.add('is-fallback');">
            <i class="<?= htmlspecialchars(op_r5s_role_icon_class($opLane), ENT_QUOTES, 'UTF-8') ?>"></i>
          </span>
          <span><?= htmlspecialchars(op_r5s_role_label($opLane), ENT_QUOTES, 'UTF-8') ?></span>
          <?php if ($opLaneCanClaim): ?>
            <button type="button"
              class="op-r5s-lane-claim"
              data-bs-toggle="modal"
              data-bs-target="#opClaimModal"
              data-order-id="<?= (int)$row['id'] ?>"
              data-status="<?= htmlspecialchars((string)($row['status'] ?? ''), ENT_QUOTES, 'UTF-8') ?>"
              data-boost="<?= htmlspecialchars(trim(strip_tags((string)$op_boost_overview)), ENT_QUOTES, 'UTF-8') ?>"
              data-earning="<?= htmlspecialchars($op_earning_text, ENT_QUOTES, 'UTF-8') ?>"
              data-next-change-in="<?= (int)($next_change_in ?? 0) ?>"
              data-view-url="<?= BSTR_URL ?>/order/<?= (int)$row['id'] ?>"
              data-client="<?= htmlspecialchars($op_client_name, ENT_QUOTES, 'UTF-8') ?>"
              data-client-icon="<?= htmlspecialchars($op_client_icon, ENT_QUOTES, 'UTF-8') ?>"
              data-client-presence="<?= htmlspecialchars(op_client_presence_badge_html($op_client_id), ENT_QUOTES, 'UTF-8') ?>"
              data-is-duo="1"
              data-r5s="1"
              data-r5s-lane="<?= htmlspecialchars($opLane, ENT_QUOTES, 'UTF-8') ?>"
            >Claim</button>
          <?php else: ?>
            <span class="op-r5s-lane-claimed"><?= $opLaneClaimed ? 'Already claimed' : 'Full' ?></span>
          <?php endif; ?>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
<?php endif; ?>

<?php if (!empty($op_is_multi_booster) && empty($op_is_ranked_5s)): ?>
  <div class="op-r5s-lanes">
    <div class="op-r5s-lanes__head">
      <div>
        <i class="fa-duotone fa-people-group"></i>
        <span>Booster Team</span>
      </div>
      <small><?= (int)$op_r5s_claimed ?>/<?= (int)$op_r5s_required ?> claimed</small>
    </div>
  </div>
<?php endif; ?>

<?php
  // Normalize placeholders so we don't render the notes block at all
  $op_note_booster_plain = trim(preg_replace('/\s+/', ' ', strip_tags((string)$op_note_booster)));
  $op_note_client_plain  = trim(preg_replace('/\s+/', ' ', strip_tags((string)$op_note_client)));
  if (strcasecmp($op_note_booster_plain, 'No extra note provided.') === 0) $op_note_booster = '';
  if (strcasecmp($op_note_client_plain,  'No extra note provided.') === 0) $op_note_client  = '';

  $op_has_any_note = (trim((string)$op_note_booster) !== '') || (trim((string)$op_note_client) !== '');
  $op_render_note = function(string $html) {
    // Decode HTML entities first (fixes &#039; → ', &amp; → &, etc.)
    $decoded = html_entity_decode($html, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    $plain = ($decoded === strip_tags($decoded));
    if ($plain) {
      return nl2br(htmlspecialchars($decoded, ENT_QUOTES, 'UTF-8'));
    }
    return $decoded; // keep stored HTML (already decoded)
  };
  $op_note_title = function(string $html): string {
    $t = trim(preg_replace('/\s+/', ' ', strip_tags($html)));
    return htmlspecialchars($t, ENT_QUOTES, 'UTF-8');
  };
?>

<?php if ($op_has_any_note): ?>
  <div class="op-note-box">

    <?php if (trim((string)$op_note_booster) !== ''): ?>
      <div class="op-note-row" data-tip="<?= $op_note_title((string)$op_note_booster) ?>">
        <div class="op-note-row-ico"><i class="fa-duotone fa-circle-info"></i></div>
        <div class="op-note-row-text"><?= $op_render_note((string)$op_note_booster) ?></div>
      </div>
    <?php endif; ?>

    <?php if (trim((string)$op_note_client) !== ''): ?>
      <div class="op-note-row" data-tip="<?= $op_note_title((string)$op_note_client) ?>">
        <div class="op-note-row-ico"><i class="fa-duotone fa-circle-info"></i></div>
        <div class="op-note-row-text"><?= $op_render_note((string)$op_note_client) ?></div>
      </div>
    <?php endif; ?>

  </div>
<?php endif; ?>

<div class="op-bottom">
  <?php
    $op_client_name = '';
    $op_client_icon = '';
    $op_client_id = (int)($row['client_id'] ?? $row['customer_id'] ?? 0);
    if ($op_client_id > 0 && function_exists('db_get_row')) {
      $op_client_row = db_get_row('clients', ['id' => $op_client_id], 1);
      $op_client_icon = trim((string)($op_client_row['icon'] ?? ''));
      $op_client_name = trim((string)($op_client_row['username'] ?? ''));
      if ($op_client_name === '') $op_client_name = trim((string)($op_client_row['name'] ?? ''));
      if ($op_client_name === '') $op_client_name = trim((string)($op_client_row['display_name'] ?? ''));
      if ($op_client_name === '') $op_client_name = trim((string)($op_client_row['email'] ?? ''));
      if ($op_client_name === '') $op_client_name = 'Client #' . $op_client_id;
    }
  ?>

  <!-- Row 1: Earning left + Claim right -->
  <div class="op-bottom-row1">
    <div class="op-earn">
      <div class="op-earn-k">Earning</div>
      <div class="op-earn-v booster-price" data-order="<?= (int)($row['id'] ?? 0) ?>">
        <span class="lb-earn-amount"><?= htmlspecialchars($op_earning_text, ENT_QUOTES, 'UTF-8') ?></span>
        <span class="lb-chip lb-chip-timer" <?= ($next_change_in === null ? 'style="display:none"' : '') ?>>
          <i class="fa-duotone fa-timer"></i>
          <span class="lb-timer-val" data-seconds="<?= (int) $next_change_in ?>"></span>
        </span>
      </div>
    </div>

    <?php if (!empty($op_is_ranked_5s)): ?>
      <div class="op-r5s-bottom-hint">Choose a lane above</div>
    <?php elseif (
      in_array(($row['status'] ?? ''), ['PAID','PROCESSING'], true)
      || (!empty($op_is_multi_booster) && in_array(($row['status'] ?? ''), ['IN_PROGRESS'], true))
    ): ?>
      <button type="button"
        class="btn btn-primary op-claim-btn"
        data-bs-toggle="modal"
        data-bs-target="#opClaimModal"
        data-order-id="<?= (int)$row['id'] ?>"
        data-status="<?= htmlspecialchars((string)($row['status'] ?? ''), ENT_QUOTES, 'UTF-8') ?>"
        data-boost="<?= htmlspecialchars(trim(strip_tags((string)$op_boost_overview)), ENT_QUOTES, 'UTF-8') ?>"
        data-earning="<?= htmlspecialchars($op_earning_text, ENT_QUOTES, 'UTF-8') ?>"
        data-next-change-in="<?= (int)($next_change_in ?? 0) ?>"
        data-view-url="<?= BSTR_URL ?>/order/<?= (int)$row['id'] ?>"
        data-client="<?= htmlspecialchars($op_client_name, ENT_QUOTES, 'UTF-8') ?>"
        data-client-icon="<?= htmlspecialchars($op_client_icon, ENT_QUOTES, 'UTF-8') ?>"
        data-client-presence="<?= htmlspecialchars(op_client_presence_badge_html($op_client_id), ENT_QUOTES, 'UTF-8') ?>"
        data-is-duo="<?= (((int)($row['form_id'] ?? 0) === 19) || ((int)($row['is_duo'] ?? 0) === 1)) ? '1' : '0' ?>"
        data-r5s="<?= !empty($op_is_ranked_5s) ? '1' : '0' ?>"
        data-multi-booster="<?= !empty($op_is_multi_booster) ? '1' : '0' ?>"
        data-r5s-lane=""
      >
        Claim <i class="fa-solid fa-arrow-right"></i>
      </button>
    <?php else: ?>
      <a href="<?= BSTR_URL ?>/order/<?= (int)$row['id'] ?>" class="btn btn-primary op-claim-btn">
        Open <i class="fa-solid fa-arrow-right"></i>
      </a>
    <?php endif; ?>
  </div>

  <!-- Row 2: Client pill (full width) -->
  <?php if ($op_client_id > 0): ?>
  <div class="op-client-pill">
    <div class="op-client-pill__left">
      <?php if ($op_client_icon !== ''): ?>
        <img class="op-client-pill__avatar" src="<?= htmlspecialchars($op_client_icon, ENT_QUOTES, 'UTF-8') ?>" alt="" loading="lazy">
      <?php else: ?>
        <div class="op-client-pill__avatar op-client-pill__avatar--fallback"><i class="fa-duotone fa-user"></i></div>
      <?php endif; ?>
      <div class="op-client-pill__meta">
        <span class="op-client-pill__label">Client</span>
        <span class="op-client-pill__name"><?= htmlspecialchars($op_client_name, ENT_QUOTES, 'UTF-8') ?></span>
      </div>
    </div>
    <?= op_client_presence_badge_html($op_client_id) ?>
  </div>
  <?php endif; ?>

</div>

                        </td>

                        <!-- Hidden columns (keeps DataTables search/sort working) -->
                        <td class="op-hidden">#<?= $row['id'] ?></td>
                        <td class="op-hidden"><?= util_format_currency_display($row['currency']) . util_format_price_display($booster_price) ?></td>
                        <td class="op-hidden" data-order="<?= $row['created_at'] ?>"><?= $row['created_at'] ?></td>
                        <td class="op-hidden">View</td>
                    </tr>
                <?php endforeach; ?>

            </tbody>
        </table>

        <div id="opEmptyState" class="op-empty-state">
          <div class="op-empty-state__box">
            <img class="mb-3" src="<?= ASSET_URL ?>/origin/dash/svg/illustrations/oc-browse.svg" alt="" style="" data-hs-theme-appearance="default">
            <img class="mb-3" src="<?= ASSET_URL ?>/origin/dash/svg/illustrations-light/oc-browse.svg" alt="" style="" data-hs-theme-appearance="dark">
            <div class="op-empty-state__title">No Orders To Claim</div>
          </div>
        </div>
    </div>
    <!-- End Table -->

    <!-- Footer -->
    <div class="card-footer">
        <!-- Pagination -->
        <div class="row justify-content-center justify-content-sm-between align-items-sm-center">
            <div class="col-sm mb-2 mb-sm-0">
                <div class="d-flex justify-content-center justify-content-sm-start align-items-center">
                    <span class="me-2">Showing:</span>

                    <!-- Select -->
                    <div class="tom-select-custom">
                        <select id="datatableEntries" class="js-select form-select form-select-borderless w-auto"
                            autocomplete="off" data-hs-tom-select-options='{
                "searchInDropdown": false,
                "hideSearch": true
              }'>
                            <option value="4">4</option>
                            <option value="6">6</option>
                            <option value="8" selected>8</option>
                            <option value="12">12</option>
                        </select>
                    </div>
                    <!-- End Select -->

                    <span class="text-secondary me-2">of</span>

                    <!-- Pagination Quantity -->
                    <span id="datatableEntriesInfoTotalQty"></span>
                </div>
            </div>

            <div class="col-sm-auto">
                <div class="d-flex justify-content-center justify-content-sm-end">
                    <!-- Pagination -->
                    <nav id="datatableWithSearchPagination" aria-label="Activity pagination"></nav>
                </div>
            </div>
        </div>
        <!-- End Pagination -->
    </div>
    <!-- End Footer -->
</div>
<!-- End Card -->


<!-- Claim Order Modal -->
<style>
/* ── Claim Modal — full redesign ─────────────────────────────────────── */
#opClaimModal .modal-content{
  background: #161820;
  border: 1px solid rgba(255,255,255,.08);
  border-radius: 28px;
  box-shadow: 0 40px 100px rgba(0,0,0,.80), 0 0 0 1px rgba(124,92,255,.12);
  overflow: hidden;
}

/* glowing top bar */
#opClaimModal .lbcm-topbar{
  height: 4px;
  background: linear-gradient(90deg, #7c5cff, #b87fff, #7c5cff);
  background-size: 200% 100%;
  animation: lbcm-slide 3s linear infinite;
}
@keyframes lbcm-slide{ to{ background-position: -200% 0; } }

/* hero */
#opClaimModal .lbcm-hero{
  padding: 24px 24px 20px;
  display: flex;
  align-items: flex-start;
  gap: 16px;
  position: relative;
}
#opClaimModal .lbcm-hero-icon{
  width: 48px; height: 48px;
  border-radius: 16px;
  display: flex; align-items: center; justify-content: center;
  background: linear-gradient(135deg, rgba(124,92,255,.28), rgba(124,92,255,.10));
  border: 1px solid rgba(124,92,255,.35);
  box-shadow: 0 8px 28px rgba(124,92,255,.22);
  flex-shrink: 0;
  color: #fff;
  font-size: 20px;
}
#opClaimModal .lbcm-hero-text{ flex: 1; min-width: 0; }
#opClaimModal .lbcm-hero-title{
  font-size: 20px; font-weight: 950; color: #fff; margin: 0 0 3px; line-height: 1.2;
}
#opClaimModal .lbcm-hero-sub{
  font-size: 13px; color: rgba(255,255,255,.45); line-height: 1.4;
}
#opClaimModal .lbcm-close{
  position: absolute; top: 18px; right: 18px;
  width: 32px; height: 32px; border-radius: 10px;
  background: rgba(255,255,255,.06); border: 1px solid rgba(255,255,255,.10);
  color: rgba(255,255,255,.50); display:flex; align-items:center; justify-content:center;
  cursor:pointer; font-size:13px; transition: background .14s, color .14s;
}
#opClaimModal .lbcm-close:hover{ background: rgba(255,255,255,.12); color:#fff; }

/* order card inside modal */
#opClaimModal .lbcm-body{ padding: 0 20px 4px; }

#opClaimModal .lbcm-order-card{
  border-radius: 18px;
  border: 1px solid rgba(255,255,255,.08);
  background: rgba(255,255,255,.03);
  overflow: hidden;
  margin-bottom: 14px;
}
#opClaimModal .lbcm-order-top{
  display: flex;
  align-items: stretch;
  gap: 0;
}
#opClaimModal .lbcm-order-main{
  flex: 1;
  padding: 14px 16px;
  border-right: 1px solid rgba(255,255,255,.07);
  min-width: 0;
}
#opClaimModal .lbcm-order-id{
  font-size: 10px; font-weight: 950; letter-spacing: .12em;
  text-transform: uppercase; color: rgba(124,92,255,.85); margin-bottom: 5px;
}
#opClaimModal .lbcm-order-boost{
  font-size: 15px; font-weight: 950; color: #fff; line-height: 1.25; margin-bottom: 0;
}
#opClaimModal .lbcm-order-earn{
  flex: 0 0 auto;
  padding: 14px 16px;
  display: flex; flex-direction: column; align-items: flex-end; justify-content: center;
  gap: 2px;
}
#opClaimModal .lbcm-earn-label{
  font-size: 10px; font-weight: 950; letter-spacing: .10em;
  text-transform: uppercase; color: rgba(255,255,255,.35);
}
#opClaimModal .lbcm-earn-val{
  font-size: 24px; font-weight: 950; color: #fff; line-height: 1;
  text-shadow: 0 0 20px rgba(124,92,255,.35);
}

/* client row inside the order card */
#opClaimModal .lbcm-client-row{
  display: flex; align-items: center; gap: 10px;
  padding: 10px 16px;
  border-top: 1px solid rgba(255,255,255,.06);
  background: rgba(255,255,255,.02);
}
#opClaimModal .lbcm-client-avatar{
  width: 28px; height: 28px; border-radius: 50%;
  object-fit: cover;
  border: 1.5px solid rgba(255,255,255,.14);
  flex-shrink: 0;
}
#opClaimModal .lbcm-client-avatar--fallback{
  display:flex; align-items:center; justify-content:center;
  background: rgba(124,92,255,.16); border-color: rgba(124,92,255,.28);
  color: rgba(124,92,255,.9); font-size: 12px;
}
#opClaimModal .lbcm-client-info{ min-width: 0; flex: 1; }
#opClaimModal .lbcm-client-label{
  font-size: 10px; font-weight: 950; letter-spacing: .10em; text-transform: uppercase;
  color: rgba(255,255,255,.32); margin-bottom: 4px;
}
#opClaimModal .lbcm-client-line{
  display: flex; align-items: center; justify-content: space-between; gap: 12px;
  min-width: 0; width: 100%;
}
#opClaimModal .lbcm-client-name{
  font-size: 15px; font-weight: 950; color: rgba(255,255,255,.9);
  white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
}
#opClaimModal .lbcm-client-presence{ flex: 0 0 auto; }
#opClaimModal .lbcm-client-presence .op-client-presence{
  padding: 7px 11px;
  border-radius: 999px;
  font-size: 11px;
  font-weight: 950;
  letter-spacing: .02em;
  background: rgba(255,255,255,.06);
  border: 1px solid rgba(255,255,255,.10);
}

/* timer chip inside modal */
#opClaimModal .lbcm-timer-wrap{ margin-top: 4px; }

/* confirm checkbox */
#opClaimModal .lbcm-confirm{
  display:flex; align-items:flex-start; gap:12px;
  padding: 12px 14px; border-radius: 14px;
  border: 1px solid rgba(255,255,255,.07);
  background: rgba(255,255,255,.025);
  margin-bottom: 12px; cursor:pointer;
  transition: border-color .15s, background .15s;
}
#opClaimModal .lbcm-confirm:hover{ border-color:rgba(124,92,255,.30); background:rgba(124,92,255,.05); }
#opClaimModal .lbcm-confirm.is-checked{ border-color:rgba(124,92,255,.50); background:rgba(124,92,255,.09); }
#opClaimModal .lbcm-confirm input{ display:none; }
#opClaimModal .lbcm-check-box{
  width:20px; height:20px; border-radius:6px;
  border:1.5px solid rgba(255,255,255,.20); background:rgba(255,255,255,.04);
  display:flex; align-items:center; justify-content:center; flex:0 0 auto; margin-top:1px;
  transition: border-color .15s, background .15s;
}
#opClaimModal .lbcm-confirm.is-checked .lbcm-check-box{ border-color:#7c5cff; background:#7c5cff; }
#opClaimModal .lbcm-check-mark{ display:none; color:#fff; font-size:11px; }
#opClaimModal .lbcm-confirm.is-checked .lbcm-check-mark{ display:block; }
#opClaimModal .lbcm-confirm-text{ font-size:13px; color:rgba(255,255,255,.65); line-height:1.45; }
#opClaimModal .lbcm-confirm-text ul{ margin: 6px 0 0 0; padding-left: 18px; }
#opClaimModal .lbcm-confirm-text li{ margin: 3px 0; }
#opClaimModal .lbcm-duo-warning{
  display:none;
  border-radius:14px;
  border:1px solid rgba(255,166,0,.30);
  background:rgba(255,166,0,.11);
  color:#ffd27a;
  padding:12px 14px;
  margin:0 0 12px 0;
  font-size:13px;
  line-height:1.45;
}
#opClaimModal .lbcm-duo-warning.is-visible{ display:block; }
#opClaimModal .lbcm-duo-warning-title{
  display:flex;
  align-items:center;
  gap:8px;
  font-weight:950;
  letter-spacing:.08em;
  text-transform:uppercase;
  margin-bottom:4px;
  color:#ffe2a8;
}
#opClaimModal .lbcm-duo-warning-text{ color:rgba(255,226,168,.92); font-weight:700; }

/* math */
#opClaimModal .lbcm-math{
  background: rgba(0,0,0,.20); border: 1px solid rgba(255,255,255,.07);
  border-radius: 14px; padding: 13px 15px; margin-bottom: 18px;
}
#opClaimModal .lbcm-math-label{
  font-size:10px; font-weight:950; letter-spacing:.10em; text-transform:uppercase;
  color:rgba(255,255,255,.30); margin-bottom:10px;
}
#opClaimModal .lbcm-math-row{ display:flex; align-items:center; gap:10px; }
#opClaimModal .lbcm-math-q{ font-size:16px; font-weight:950; color:rgba(255,255,255,.85); }
#opClaimModal .lbcm-math-input{
  width:68px; border-radius:10px; border:1.5px solid rgba(255,255,255,.12);
  background:rgba(255,255,255,.06); color:#fff; font-weight:950; font-size:16px;
  text-align:center; padding:7px 8px; outline:none; transition:border-color .15s;
}
#opClaimModal .lbcm-math-input:focus{ border-color:rgba(124,92,255,.60); }
#opClaimModal .lbcm-math-fb{ font-size:13px; font-weight:950; }

/* footer */
#opClaimModal .lbcm-footer{
  display:flex; align-items:center; justify-content:space-between;
  gap:10px; padding: 0 20px 22px;
}
#opClaimModal .lbcm-footer-left{
  display:flex; align-items:center; gap:8px;
  font-size:11px; color:rgba(255,255,255,.30); font-weight:800;
}
#opClaimModal .lbcm-footer-left i{ font-size:13px; }
#opClaimModal .lbcm-footer-right{ display:flex; align-items:center; gap:10px; }
#opClaimModal .lbcm-btn-cancel{
  background:rgba(255,255,255,.05); border:1px solid rgba(255,255,255,.09);
  color:rgba(255,255,255,.60); border-radius:12px; padding:10px 18px;
  font-size:14px; font-weight:950; cursor:pointer; transition:background .15s;
}
#opClaimModal .lbcm-btn-cancel:hover{ background:rgba(255,255,255,.10); color:#fff; }
#opClaimModal .lbcm-btn-claim{
  background: linear-gradient(135deg, #7c5cff, #9f80ff);
  border: 1px solid rgba(255,255,255,.14);
  box-shadow: 0 6px 24px rgba(124,92,255,.50), 0 1px 0 rgba(255,255,255,.12) inset;
  color:#fff; border-radius:12px; padding:10px 22px;
  font-size:14px; font-weight:950; cursor:pointer;
  display:flex; align-items:center; gap:8px;
  transition: box-shadow .15s, transform .15s;
}
#opClaimModal .lbcm-btn-claim:hover{
  box-shadow: 0 8px 32px rgba(124,92,255,.70), 0 1px 0 rgba(255,255,255,.12) inset;
  transform: translateY(-1px);
}
#opClaimModal .lbcm-btn-claim:disabled{ opacity:.45; pointer-events:none; }
#opClaimModal .lbcm-spinner{
  display:none; width:14px; height:14px;
  border:2px solid rgba(255,255,255,.3); border-top-color:#fff;
  border-radius:50%; animation:lbcm-spin .6s linear infinite;
}
@keyframes lbcm-spin{ to{ transform:rotate(360deg); } }


  /* =========================================================
     Orders Panel — Wide 3 column layout + stacked order rows
     ========================================================= */
  .op-table-wrap{
    overflow-x: visible !important;
  }

  .card:has(#orders_table){
    width: 100%;
    max-width: none;
  }

  @media (min-width: 1400px){
    .orders-panel-table.orders-as-cards tbody{
      grid-template-columns: repeat(3, minmax(0, 1fr));
      gap: 16px;
      padding: 18px;
    }
  }

  @media (min-width: 992px) and (max-width: 1399.98px){
    .orders-panel-table.orders-as-cards tbody{
      grid-template-columns: repeat(2, minmax(0, 1fr));
    }
  }

  .op-kv{
    grid-template-columns: 1fr !important;
    gap: 8px;
  }

  .op-kv-row,
  .op-kv-row:has(.op-kv-right img:nth-of-type(8)),
  .op-kv-row:has(.op-kv-right img:nth-of-type(9)){
    grid-column: auto !important;
    grid-template-columns: minmax(135px, 36%) minmax(0, 1fr) !important;
    min-height: 46px;
  }

  .op-kv-right{
    justify-content: flex-end;
    text-align: right;
  }

  .op-kv-row:has(.op-kv-right img:nth-of-type(8)) .op-kv-right,
  .op-kv-row:has(.op-kv-right img:nth-of-type(9)) .op-kv-right{
    justify-content: flex-end;
    max-height: 72px;
  }

  @media (max-width: 680px){
    .op-kv-row,
    .op-kv-row:has(.op-kv-right img:nth-of-type(8)),
    .op-kv-row:has(.op-kv-right img:nth-of-type(9)){
      grid-template-columns: 1fr !important;
    }

    .op-kv-right,
    .op-kv-row:has(.op-kv-right img:nth-of-type(8)) .op-kv-right,
    .op-kv-row:has(.op-kv-right img:nth-of-type(9)) .op-kv-right{
      justify-content: flex-start;
      text-align: left;
    }
  }


  /* =========================================================
     Customer Champions + Claim Modal fresh polish
     ========================================================= */
  .op-pref-row--champions{
    grid-template-columns: 1fr !important;
    gap: 10px !important;
    padding: 13px !important;
    min-height: 0 !important;
    background:
      radial-gradient(circle at 0% 0%, rgba(124,92,255,.12), transparent 42%),
      rgba(0,0,0,.16) !important;
  }

  .op-pref-row--champions .op-kv-left{
    width: 100%;
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding-bottom: 6px;
    border-bottom: 1px solid rgba(255,255,255,.055);
  }

  .op-pref-row--champions .op-kv-left span{
    white-space: normal;
    overflow: visible;
    text-overflow: clip;
  }

  .op-pref-row--champions .op-kv-right{
    justify-content: flex-start !important;
    text-align: left !important;
    display: flex;
    flex-wrap: wrap;
    gap: 7px !important;
    max-height: 96px !important;
    overflow: auto;
    padding: 1px 4px 2px 0;
    scrollbar-width: thin;
  }

  .op-pref-row--champions .op-kv-right::-webkit-scrollbar{
    width: 6px;
    height: 6px;
  }

  .op-pref-row--champions .op-kv-right::-webkit-scrollbar-thumb{
    background: rgba(124,92,255,.45);
    border-radius: 999px;
  }

  .op-pref-row--champions .op-kv-right img,
  .op-pref-row--champions .op-kv-right .avatar,
  .op-pref-row--champions .op-kv-right [class*="champ"]{
    width: 26px !important;
    height: 26px !important;
    border-radius: 8px !important;
    border: 1px solid rgba(255,255,255,.12);
    box-shadow: 0 5px 12px rgba(0,0,0,.20);
  }

  .op-pref-row--roles{
    grid-template-columns: minmax(135px, 36%) minmax(0, 1fr) !important;
  }

  #opClaimModal .modal-dialog{
    max-width: 540px !important;
  }

  #opClaimModal .modal-content{
    background:
      radial-gradient(circle at 12% 0%, rgba(124,92,255,.18), transparent 36%),
      linear-gradient(180deg, #20222b 0%, #151720 100%) !important;
    border-radius: 26px !important;
    border: 1px solid rgba(150,128,255,.20) !important;
    box-shadow: 0 34px 90px rgba(0,0,0,.78), 0 0 0 1px rgba(255,255,255,.035) inset !important;
  }

  #opClaimModal .lbcm-topbar{
    height: 5px !important;
    background: linear-gradient(90deg, #765cff, #a985ff, rgba(124,92,255,.35)) !important;
  }

  #opClaimModal .lbcm-hero{
    padding: 24px 26px 18px !important;
    align-items: center !important;
  }

  #opClaimModal .lbcm-hero-icon{
    width: 54px !important;
    height: 54px !important;
    border-radius: 18px !important;
    background: linear-gradient(135deg, rgba(124,92,255,.34), rgba(124,92,255,.13)) !important;
  }

  #opClaimModal .lbcm-hero-title{
    font-size: 22px !important;
    letter-spacing: .01em;
  }

  #opClaimModal .lbcm-hero-sub{
    color: rgba(255,255,255,.62) !important;
  }

  #opClaimModal .lbcm-body{
    padding: 0 26px 8px !important;
  }

  #opClaimModal .lbcm-order-card,
  #opClaimModal .lbcm-confirm,
  #opClaimModal .lbcm-math{
    border-radius: 18px !important;
    border-color: rgba(255,255,255,.095) !important;
    background: rgba(0,0,0,.18) !important;
  }

  #opClaimModal .lbcm-order-top{
    display: grid !important;
    grid-template-columns: minmax(0, 1fr) auto;
  }

  #opClaimModal .lbcm-order-main{
    padding: 16px 18px !important;
  }

  #opClaimModal .lbcm-order-boost{
    font-size: 16px !important;
  }

  #opClaimModal .lbcm-order-earn{
    min-width: 128px;
    padding: 16px 18px !important;
    background: rgba(124,92,255,.08);
  }

  #opClaimModal .lbcm-earn-val{
    font-size: 26px !important;
  }

  #opClaimModal .lbcm-client-row{
    padding: 13px 18px !important;
  }

  #opClaimModal .lbcm-client-name{
    font-size: 14px !important;
  }

  #opClaimModal .lbcm-confirm-text{
    font-size: 13px !important;
    color: rgba(255,255,255,.72) !important;
  }

  #opClaimModal .lbcm-math-row{
    justify-content: space-between;
  }

  #opClaimModal .lbcm-math-input{
    width: 82px !important;
    height: 42px;
  }

  #opClaimModal .lbcm-footer{
    padding: 2px 26px 24px !important;
  }

  #opClaimModal .lbcm-btn-cancel,
  #opClaimModal .lbcm-btn-claim{
    border-radius: 14px !important;
    padding: 12px 20px !important;
  }

  @media (max-width: 620px){
    #opClaimModal .modal-dialog{
      margin: 10px !important;
    }

    #opClaimModal .lbcm-order-top{
      grid-template-columns: 1fr !important;
    }

    #opClaimModal .lbcm-order-main{
      border-right: 0 !important;
      border-bottom: 1px solid rgba(255,255,255,.07);
    }

    #opClaimModal .lbcm-order-earn{
      align-items: flex-start !important;
    }

    #opClaimModal .lbcm-footer{
      flex-direction: column;
      align-items: stretch;
    }

    #opClaimModal .lbcm-footer-right,
    #opClaimModal .lbcm-btn-cancel,
    #opClaimModal .lbcm-btn-claim{
      width: 100%;
      justify-content: center;
    }
  }



  /* =========================================================
     Final polish: champions panel on the right + wider claim modal
     ========================================================= */
  .op-pref-row--champions{
    grid-template-columns: minmax(150px, 34%) minmax(0, 1fr) !important;
    align-items: stretch !important;
    gap: 12px !important;
    padding: 12px !important;
    min-height: 98px !important;
    background:
      radial-gradient(circle at 0% 0%, rgba(124,92,255,.14), transparent 44%),
      rgba(0,0,0,.15) !important;
  }

  .op-pref-row--champions .op-kv-left{
    width: auto !important;
    min-width: 0 !important;
    display: flex !important;
    align-items: center !important;
    justify-content: flex-start !important;
    gap: 10px !important;
    padding: 0 12px 0 0 !important;
    border-bottom: 0 !important;
    border-right: 1px solid rgba(255,255,255,.06) !important;
  }

  .op-pref-row--champions .op-kv-left span{
    white-space: normal !important;
    overflow: visible !important;
    text-overflow: clip !important;
    line-height: 1.25 !important;
  }

  .op-pref-row--champions .op-kv-right{
    justify-content: flex-end !important;
    align-content: flex-start !important;
    text-align: right !important;
    display: flex !important;
    flex-wrap: wrap !important;
    gap: 7px !important;
    max-height: 86px !important;
    overflow: auto !important;
    padding: 2px 6px 2px 8px !important;
    border-radius: 14px !important;
    background: rgba(255,255,255,.025) !important;
    box-shadow: inset 0 0 0 1px rgba(255,255,255,.045) !important;
    scrollbar-width: thin;
  }

  .op-pref-row--champions .op-kv-right img,
  .op-pref-row--champions .op-kv-right .avatar,
  .op-pref-row--champions .op-kv-right [class*="champ"]{
    width: 29px !important;
    height: 29px !important;
    border-radius: 9px !important;
    border: 1px solid rgba(255,255,255,.14) !important;
    box-shadow: 0 6px 14px rgba(0,0,0,.28) !important;
  }

  @media (max-width: 680px){
    .op-pref-row--champions{
      grid-template-columns: 1fr !important;
      min-height: 0 !important;
    }

    .op-pref-row--champions .op-kv-left{
      border-right: 0 !important;
      padding-right: 0 !important;
    }

    .op-pref-row--champions .op-kv-right{
      justify-content: flex-start !important;
      text-align: left !important;
    }
  }

  #opClaimModal .modal-dialog{
    max-width: 760px !important;
  }

  #opClaimModal .modal-content{
    border-radius: 30px !important;
  }

  #opClaimModal .lbcm-hero{
    padding: 28px 34px 22px !important;
    gap: 18px !important;
  }

  #opClaimModal .lbcm-hero-icon{
    width: 58px !important;
    height: 58px !important;
    border-radius: 20px !important;
    font-size: 22px !important;
  }

  #opClaimModal .lbcm-hero-title{
    font-size: 26px !important;
  }

  #opClaimModal .lbcm-hero-sub{
    font-size: 14px !important;
    color: rgba(255,255,255,.68) !important;
  }

  #opClaimModal .lbcm-close{
    top: 24px !important;
    right: 24px !important;
    width: 38px !important;
    height: 38px !important;
    border-radius: 13px !important;
  }

  #opClaimModal .lbcm-body{
    padding: 0 34px 10px !important;
  }

  #opClaimModal .lbcm-order-card{
    margin-bottom: 16px !important;
    background: rgba(0,0,0,.20) !important;
  }

  #opClaimModal .lbcm-order-top{
    grid-template-columns: minmax(0, 1fr) 190px !important;
  }

  #opClaimModal .lbcm-order-main{
    padding: 22px 24px !important;
  }

  #opClaimModal .lbcm-order-id{
    font-size: 11px !important;
    margin-bottom: 8px !important;
  }

  #opClaimModal .lbcm-order-boost{
    font-size: 22px !important;
    line-height: 1.18 !important;
  }

  #opClaimModal .lbcm-order-earn{
    min-width: 190px !important;
    align-items: center !important;
    text-align: center !important;
    background:
      radial-gradient(circle at 50% 0%, rgba(155,128,255,.22), transparent 62%),
      rgba(124,92,255,.10) !important;
  }

  #opClaimModal .lbcm-earn-label{
    font-size: 11px !important;
    color: rgba(255,255,255,.48) !important;
  }

  #opClaimModal .lbcm-earn-val{
    font-size: 36px !important;
    letter-spacing: -.03em !important;
  }

  #opClaimModal .lbcm-client-row{
    padding: 16px 24px !important;
    gap: 14px !important;
  }

  #opClaimModal .lbcm-client-avatar{
    width: 36px !important;
    height: 36px !important;
  }

  #opClaimModal .lbcm-client-label{
    font-size: 11px !important;
    color: rgba(255,255,255,.45) !important;
  }

  #opClaimModal .lbcm-client-name{
    font-size: 16px !important;
    color: rgba(255,255,255,.9) !important;
  }

  #opClaimModal .lbcm-confirm{
    padding: 16px 18px !important;
    margin-bottom: 14px !important;
  }

  #opClaimModal .lbcm-confirm-text{
    font-size: 14px !important;
    line-height: 1.5 !important;
  }

  #opClaimModal .lbcm-math{
    padding: 18px 20px !important;
    margin-bottom: 20px !important;
  }

  #opClaimModal .lbcm-math-label{
    font-size: 11px !important;
    color: rgba(255,255,255,.42) !important;
  }

  #opClaimModal .lbcm-math-q{
    font-size: 20px !important;
  }

  #opClaimModal .lbcm-math-input{
    width: 104px !important;
    height: 48px !important;
    font-size: 18px !important;
  }

  #opClaimModal .lbcm-footer{
    padding: 4px 34px 28px !important;
  }

  #opClaimModal .lbcm-footer-left{
    font-size: 12px !important;
    color: rgba(255,255,255,.42) !important;
  }

  #opClaimModal .lbcm-btn-cancel,
  #opClaimModal .lbcm-btn-claim{
    min-height: 50px !important;
    padding: 13px 24px !important;
    font-size: 15px !important;
  }

  #opClaimModal .lbcm-btn-claim{
    min-width: 180px !important;
    justify-content: center !important;
  }

  @media (max-width: 820px){
    #opClaimModal .modal-dialog{
      max-width: calc(100vw - 22px) !important;
      margin: 11px !important;
    }
  }

  @media (max-width: 620px){
    #opClaimModal .lbcm-hero,
    #opClaimModal .lbcm-body,
    #opClaimModal .lbcm-footer{
      padding-left: 18px !important;
      padding-right: 18px !important;
    }

    #opClaimModal .lbcm-order-top{
      grid-template-columns: 1fr !important;
    }

    #opClaimModal .lbcm-order-earn{
      min-width: 0 !important;
      align-items: flex-start !important;
      text-align: left !important;
    }
  }

  /* Keep the claim modal fully usable at browser zoom and on short viewports. */
  #opClaimModal{
    padding: 8px !important;
    overflow: hidden !important;
  }

  #opClaimModal .modal-dialog{
    width: min(760px, calc(100vw - 16px)) !important;
    max-width: min(760px, calc(100vw - 16px)) !important;
    height: auto !important;
    max-height: calc(100dvh - 16px) !important;
    margin: 0 auto !important;
  }

  #opClaimModal .modal-content,
  #opClaimModal .lbcm-form{
    display: flex !important;
    flex-direction: column !important;
    width: 100% !important;
    max-height: calc(100dvh - 16px) !important;
    min-height: 0 !important;
    overflow: hidden !important;
  }

  #opClaimModal .lbcm-topbar,
  #opClaimModal .lbcm-hero,
  #opClaimModal .lbcm-footer{
    flex: 0 0 auto !important;
  }

  #opClaimModal .lbcm-body{
    flex: 1 1 auto !important;
    min-height: 0 !important;
    max-height: none !important;
    overflow-y: auto !important;
    overflow-x: hidden !important;
    overscroll-behavior: contain;
    scrollbar-width: thin;
  }

  #opClaimModal .lbcm-body::-webkit-scrollbar{
    width: 6px;
  }

  #opClaimModal .lbcm-body::-webkit-scrollbar-thumb{
    background: rgba(255,255,255,.16);
    border-radius: 999px;
  }

  @media (max-height: 900px){
    #opClaimModal .lbcm-hero{
      padding: 18px 24px 14px !important;
    }

    #opClaimModal .lbcm-hero-icon{
      width: 48px !important;
      height: 48px !important;
      border-radius: 16px !important;
    }

    #opClaimModal .lbcm-hero-title{
      font-size: 22px !important;
    }

    #opClaimModal .lbcm-body{
      padding: 0 24px 6px !important;
    }

    #opClaimModal .lbcm-order-main,
    #opClaimModal .lbcm-order-earn{
      padding-top: 16px !important;
      padding-bottom: 16px !important;
    }

    #opClaimModal .lbcm-client-row{
      padding-top: 12px !important;
      padding-bottom: 12px !important;
    }

    #opClaimModal .lbcm-confirm{
      padding: 12px 15px !important;
      margin-bottom: 10px !important;
    }

    #opClaimModal .lbcm-math{
      padding: 13px 16px !important;
      margin-bottom: 12px !important;
    }

    #opClaimModal .lbcm-footer{
      padding: 8px 24px 16px !important;
    }

    #opClaimModal .lbcm-btn-cancel,
    #opClaimModal .lbcm-btn-claim{
      min-height: 44px !important;
      padding-top: 10px !important;
      padding-bottom: 10px !important;
    }
  }

  @media (max-height: 740px){
    #opClaimModal .lbcm-hero{
      padding-top: 12px !important;
      padding-bottom: 10px !important;
    }

    #opClaimModal .lbcm-hero-icon{
      width: 42px !important;
      height: 42px !important;
    }

    #opClaimModal .lbcm-hero-title{
      font-size: 20px !important;
    }

    #opClaimModal .lbcm-hero-sub{
      font-size: 12px !important;
    }

    #opClaimModal .lbcm-order-card{
      margin-bottom: 10px !important;
    }

    #opClaimModal .lbcm-confirm-text{
      font-size: 12px !important;
      line-height: 1.35 !important;
    }

    #opClaimModal .lbcm-footer-left{
      display: none !important;
    }

    #opClaimModal .lbcm-footer{
      justify-content: flex-end !important;
      padding-bottom: 10px !important;
    }
  }

  /* Compact claim modal */
  #opClaimModal .modal-dialog{max-width:620px !important}
  #opClaimModal .lbcm-hero{padding:14px 18px 10px !important}
  #opClaimModal .lbcm-hero-icon{width:40px !important;height:40px !important;border-radius:13px !important}
  #opClaimModal .lbcm-hero-title{font-size:19px !important}
  #opClaimModal .lbcm-hero-sub{font-size:12px !important}
  #opClaimModal .lbcm-close{width:34px !important;height:34px !important}
  #opClaimModal .lbcm-body{padding:0 18px 2px !important}
  #opClaimModal .lbcm-order-card{margin-bottom:9px !important}
  #opClaimModal .lbcm-order-main,
  #opClaimModal .lbcm-order-earn{padding:12px 14px !important}
  #opClaimModal .lbcm-order-boost{font-size:16px !important}
  #opClaimModal .lbcm-earn-val{font-size:25px !important}
  #opClaimModal .lbcm-client-row{padding:9px 14px !important}
  #opClaimModal .lbcm-client-avatar{width:32px !important;height:32px !important}
  #opClaimModal .lbcm-duo-warning{padding:9px 12px !important;margin-bottom:9px !important}
  #opClaimModal .lbcm-duo-warning-title{font-size:11px !important;margin-bottom:2px !important}
  #opClaimModal .lbcm-duo-warning-text{font-size:12px !important;line-height:1.35 !important}
  #opClaimModal .lbcm-confirm{padding:10px 12px !important;margin-bottom:9px !important}
  #opClaimModal .lbcm-confirm-text{font-size:12px !important;line-height:1.35 !important}
  #opClaimModal .lbcm-confirm-text ul{margin:3px 0 0 !important}
  #opClaimModal .lbcm-math{padding:9px 12px !important;margin-bottom:9px !important;display:flex;align-items:center;justify-content:space-between;gap:12px}
  #opClaimModal .lbcm-math-label{margin:0 !important;white-space:nowrap}
  #opClaimModal .lbcm-math-row{gap:8px !important}
  #opClaimModal .lbcm-math-input{width:72px !important;height:36px !important}
  #opClaimModal .lbcm-footer{padding:8px 18px 12px !important}
  #opClaimModal .lbcm-btn-cancel,
  #opClaimModal .lbcm-btn-claim{min-height:40px !important;padding:8px 16px !important}
  @media(max-width:640px){
    #opClaimModal .modal-dialog{margin:8px !important}
    #opClaimModal .lbcm-math{align-items:flex-start;flex-direction:column}
  }
</style>

<div id="opClaimModal" class="modal fade" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" style="max-width:760px;" role="document">
    <div class="modal-content">
      <form class="lbcm-form m-0" action="<?= AJAX_URL ?>" novalidate>
        <input type="hidden" name="action" value="booster_claim_order">
        <input type="hidden" name="order_id" id="opClaimOrderId" value="">
        <input type="hidden" name="ranked_5s_lane" id="opClaimRanked5sLane" value="">

        <!-- glowing top bar -->
        <div class="lbcm-topbar"></div>

        <!-- hero -->
        <div class="lbcm-hero">
          <div class="lbcm-hero-icon"><i class="fa-duotone fa-bolt"></i></div>
          <div class="lbcm-hero-text">
            <div class="lbcm-hero-title">Ready to claim?</div>
            <div class="lbcm-hero-sub">Claim it and start now.</div>
          </div>
          <button type="button" class="lbcm-close" data-bs-dismiss="modal" aria-label="Close">
            <i class="fa-solid fa-xmark"></i>
          </button>
        </div>

        <div class="lbcm-body">

          <!-- order card -->
          <div class="lbcm-order-card">
            <div class="lbcm-order-top">
              <div class="lbcm-order-main">
                <div class="lbcm-order-id" id="opClaimOrderLabel"></div>
                <div class="lbcm-order-boost" id="opClaimBoost"></div>
                <div class="lbcm-timer-wrap">
                  <span id="opClaimTimerChip" class="lb-chip lb-chip-timer" style="display:none;margin-top:6px;">
                    <i class="fa-duotone fa-timer"></i>
                    <span id="opClaimTimerVal" class="lb-timer-val"></span>
                  </span>
                </div>
              </div>
              <div class="lbcm-order-earn">
                <div class="lbcm-earn-label">Earning</div>
                <div class="lbcm-earn-val" id="opClaimEarning"></div>
              </div>
            </div>

            <!-- client row -->
            <div class="lbcm-client-row" id="opClaimClientRow" style="display:none;">
              <div id="opClaimClientAvatar" class="lbcm-client-avatar lbcm-client-avatar--fallback">
                <i class="fa-duotone fa-user"></i>
              </div>
              <div class="lbcm-client-info">
                <div class="lbcm-client-label">Client</div>
                <div class="lbcm-client-line">
                  <div class="lbcm-client-name" id="opClaimClientName"></div>
                  <div class="lbcm-client-presence" id="opClaimClientPresence"></div>
                </div>
              </div>
            </div>
          </div>

          <!-- claim warning, text changes for Solo / Play with Booster orders -->
          <div class="lbcm-duo-warning" id="opClaimDuoWarning">
            <div class="lbcm-duo-warning-title" id="opClaimWarningTitle"><i class="fa-duotone fa-triangle-exclamation"></i> Before you claim</div>
            <div class="lbcm-duo-warning-text" id="opClaimWarningText">Only claim if you can start now.</div>
          </div>

          <!-- confirm -->
          <label class="lbcm-confirm" id="opClaimConfirmLabel">
            <input type="checkbox" id="opClaimConfirm">
            <div class="lbcm-check-box"><i class="fa-solid fa-check lbcm-check-mark"></i></div>
            <div class="lbcm-confirm-text" id="opClaimConfirmText">I'm ready now and will message the client.</div>
          </label>

          <!-- math captcha -->
          <div class="lbcm-math">
            <div class="lbcm-math-label">Quick check</div>
            <div class="lbcm-math-row">
              <span class="lbcm-math-q" id="opMathQuestion"></span>
              <input class="lbcm-math-input" type="text" id="opMathAnswer" inputmode="numeric" autocomplete="off" maxlength="4" placeholder="?">
              <span class="lbcm-math-fb" id="opMathFeedback" style="display:none;"></span>
            </div>
          </div>

        </div>

        <div class="lbcm-footer">
          <div class="lbcm-footer-left">
            <i class="fa-duotone fa-shield-check"></i>
            Starts right away
          </div>
          <div class="lbcm-footer-right">
            <button type="button" class="lbcm-btn-cancel" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="lbcm-btn-claim" id="opClaimSubmitBtn">
              <span class="lbcm-spinner" id="opClaimSpinner"></span>
              <i class="fa-duotone fa-play" id="opClaimBtnIcon"></i>
              <span id="opClaimBtnLabel">Claim Order</span>
            </button>
          </div>
        </div>

      </form>
    </div>
  </div>
</div>

<?= $this->start('scripts') ?>
<script>
    $(document).on('ready', function () {
        // FIRST VISIT MODAL (orders panel)
        // =======================================================
        (function () {
            const INTRO_KEY = 'lb_orders_panel_info_modal_v3'; // bump to show again
            const modalEl = document.getElementById('lbOrdersInfoModal');
            if (!modalEl) return;

            let seen = false;
            try { seen = localStorage.getItem(INTRO_KEY) === '1'; } catch (e) { seen = false; }

            // Show once
            if (!seen) {
                try {
                    const modal = new bootstrap.Modal(modalEl, { backdrop: 'static', keyboard: false });
                    modal.show();
                } catch (e) {
                    // Fallback: if bootstrap object isn't available
                    modalEl.classList.add('show');
                    modalEl.style.display = 'block';
                    document.body.classList.add('modal-open');
                }
            }

            // Step controls
            const tabs = ['lb-tab-cut', 'lb-tab-limits', 'lb-tab-higher'];
            let idx = 0;

            const btnBack = document.getElementById('lbIntroBack');
            const btnNext = document.getElementById('lbIntroNext');
            const btnDone = document.getElementById('lbIntroDone');

            function showTab(i) {
                idx = Math.max(0, Math.min(i, tabs.length - 1));
                const el = document.getElementById(tabs[idx]);
                if (!el) return;
                try {
                    bootstrap.Tab.getOrCreateInstance(el).show();
                } catch (e) {
                    // no-op
                    el.click();
                }

                // buttons
                if (idx === 0) {
                    btnBack.style.display = 'none';
                } else {
                    btnBack.style.display = '';
                }

                if (idx === tabs.length - 1) {
                    btnNext.style.display = 'none';
                    btnDone.style.display = '';
                } else {
                    btnNext.style.display = '';
                    btnDone.style.display = 'none';
                }
            }

            btnBack?.addEventListener('click', function () { showTab(idx - 1); });
            btnNext?.addEventListener('click', function () { showTab(idx + 1); });

            function markSeen() {
                try { localStorage.setItem(INTRO_KEY, '1'); } catch (e) {}
            }

            // Mark as seen on close or done
            modalEl.addEventListener('hidden.bs.modal', markSeen);
            btnDone?.addEventListener('click', function () {
                markSeen();
                try { bootstrap.Modal.getOrCreateInstance(modalEl).hide(); } catch (e) {
                    modalEl.classList.remove('show');
                    modalEl.style.display = 'none';
                    document.body.classList.remove('modal-open');
                }
            });

            // If user manually clicks tabs, update buttons
            modalEl.addEventListener('shown.bs.tab', function (e) {
                const id = e?.target?.id;
                const newIdx = tabs.indexOf(id);
                if (newIdx >= 0) showTab(newIdx);
            });

            // Init button state
            showTab(0);
        })();


// CLAIM MODAL (from cards)
// =======================================================
(function () {
    const modalEl = document.getElementById('opClaimModal');
    if (!modalEl) return;

    let tick = null;
    let claimStatusPoll = null;
    let fallbackLeft = null;
    let claimLocked = false;

    // Redirect to order page after successful claim
    const formEl = modalEl.querySelector('form');
    let redirectUrl = '';

    function formatMMSS(totalSeconds) {
        const s = Math.max(0, parseInt(totalSeconds, 10) || 0);
        const mm = String(Math.floor(s / 60)).padStart(2, '0');
        const ss = String(s % 60).padStart(2, '0');
        return `${mm}:${ss}`;
    }

    function setTimerText(text) {
        const chip = document.getElementById('opClaimTimerChip');
        const val = document.getElementById('opClaimTimerVal');
        if (!chip || !val) return;

        const t = (text || '').trim();
        if (!t) {
            chip.style.display = 'none';
            val.textContent = '';
            return;
        }

        chip.style.display = '';
        val.textContent = t;
    }

    function syncFromCard(orderId) {
        const card = document.querySelector(`.booster-price[data-order="${orderId}"]`);
        const t = card?.querySelector('.lb-timer-val')?.textContent || '';
        if (t.trim()) {
            fallbackLeft = null;
            setTimerText(t);
            return true;
        }
        return false;
    }

    function showClaimNotice(message, type) {
        const text = (message || 'Order claimed by another booster').trim();
        let box = document.getElementById('opClaimNotice');
        if (!box) {
            box = document.createElement('div');
            box.id = 'opClaimNotice';
            box.style.position = 'fixed';
            box.style.left = '50%';
            box.style.top = '22px';
            box.style.transform = 'translateX(-50%)';
            box.style.zIndex = '99999';
            box.style.maxWidth = '520px';
            box.style.width = 'calc(100vw - 28px)';
            box.style.padding = '14px 18px';
            box.style.borderRadius = '16px';
            box.style.fontWeight = '900';
            box.style.textAlign = 'center';
            box.style.boxShadow = '0 18px 50px rgba(0,0,0,.45)';
            document.body.appendChild(box);
        }
        box.textContent = text;
        box.style.background = (type === 'success') ? 'rgba(0,201,167,.16)' : 'rgba(237,76,120,.95)';
        box.style.border = (type === 'success') ? '1px solid rgba(0,201,167,.35)' : '1px solid rgba(255,120,155,.95)';
        box.style.color = '#fff';
        box.style.display = 'block';
        clearTimeout(box._hideTimer);
        box._hideTimer = setTimeout(() => { box.style.display = 'none'; }, 3400);
    }

    function hideClaimModalSoon() {
        setTimeout(function(){
            try { bootstrap.Modal.getOrCreateInstance(modalEl).hide(); }
            catch(e) { modalEl.classList.remove('show'); modalEl.style.display = 'none'; document.body.classList.remove('modal-open'); }
        }, 900);
    }

    function isAlreadyClaimedMessage(message) {
        const m = String(message || '').toLowerCase();
        return m.includes('already') || m.includes('claimed') || m.includes('assigned') || m.includes('taken') || m.includes('nicht mehr') || m.includes('schon') || m.includes('vergeben') || m.includes('angenommen');
    }

    function resetClaimButton() {
        claimLocked = false;
        const btn = document.getElementById('opClaimSubmitBtn');
        const spinner = document.getElementById('opClaimSpinner');
        const btnIcon = document.getElementById('opClaimBtnIcon');
        const btnLbl = document.getElementById('opClaimBtnLabel');

        if (btn) btn.disabled = false;
        if (spinner) spinner.style.display = 'none';
        if (btnIcon) btnIcon.style.display = '';
        if (btnLbl) btnLbl.textContent = 'Claim Order';
    }

    function lockClaimButton(label) {
        claimLocked = true;
        const btn = document.getElementById('opClaimSubmitBtn');
        const spinner = document.getElementById('opClaimSpinner');
        const btnIcon = document.getElementById('opClaimBtnIcon');
        const btnLbl = document.getElementById('opClaimBtnLabel');

        if (btn) btn.disabled = true;
        if (spinner) spinner.style.display = 'none';
        if (btnIcon) btnIcon.style.display = 'none';
        if (btnLbl) btnLbl.textContent = label || 'Already claimed';
    }

    function removeOrderCard(orderId) {
        if (!orderId) return;
        const trigger = document.querySelector(`[data-order-id="${CSS.escape(String(orderId))}"]`);
        const row = trigger ? trigger.closest('tr') : null;
        if (row) {
            row.style.opacity = '0';
            row.style.transform = 'scale(.98)';
            setTimeout(function(){ row.remove(); }, 260);
        }
    }

    function showOrderAlreadyClaimed(orderId, message, shouldHideModal) {
        lockClaimButton('Already claimed');
        removeOrderCard(orderId);
        showClaimNotice(message || 'Order claimed by another booster', 'error');
        if (shouldHideModal !== false) hideClaimModalSoon();
    }

    function startClaimStatusPolling(orderId) {
        if (claimStatusPoll) clearInterval(claimStatusPoll);
        claimStatusPoll = null;
        // Claiming is validated atomically by the backend. The panel already receives
        // live socket updates, so a separate 2-second AJAX poll only creates noise and
        // can compete with the actual claim request.
    }

    function startTick(orderId) {
        if (tick) clearInterval(tick);
        tick = setInterval(() => {
            // Prefer syncing from the card timer (source of truth)
            if (orderId && syncFromCard(orderId)) return;

            // Fallback countdown inside modal (only if we have seconds)
            if (fallbackLeft !== null && fallbackLeft > 0) {
                fallbackLeft -= 1;
                setTimerText(formatMMSS(fallbackLeft));
                return;
            }

            setTimerText('');
        }, 1000);
    }

    modalEl.addEventListener('show.bs.modal', function (ev) {
        const btn = ev.relatedTarget;
        if (!btn) return;

        const orderId = btn.getAttribute('data-order-id') || '';
        const status = btn.getAttribute('data-status') || '';
        const boost = btn.getAttribute('data-boost') || '';
        const earning = btn.getAttribute('data-earning') || '';
        const nextIn = parseInt(btn.getAttribute('data-next-change-in') || '0', 10);
        const isDuo = (btn.getAttribute('data-is-duo') || '0') === '1';
        modalEl.dataset.claimIsDuo = isDuo ? '1' : '0';
        const isRanked5s = (btn.getAttribute('data-r5s') || '0') === '1';
        const ranked5sLane = btn.getAttribute('data-r5s-lane') || '';
        redirectUrl = btn.getAttribute('data-view-url') || '';
        resetClaimButton();

        const laneInput = document.getElementById('opClaimRanked5sLane');
        if (laneInput) laneInput.value = ranked5sLane;

        const inp = document.getElementById('opClaimOrderId');
        if (inp) inp.value = orderId;

        const lbl = document.getElementById('opClaimOrderLabel');
        if (lbl) lbl.textContent = '#' + orderId;

        const bs = document.getElementById('opClaimBoost');
        if (bs) bs.textContent = boost;

        // Client: name + avatar
        const clientVal  = btn.getAttribute('data-client') || '';
        const clientIcon = btn.getAttribute('data-client-icon') || '';
        const clientPresence = btn.getAttribute('data-client-presence') || '';
        const clientRow  = document.getElementById('opClaimClientRow');
        const clientName = document.getElementById('opClaimClientName');
        const clientPresenceEl = document.getElementById('opClaimClientPresence');
        const clientAvEl = document.getElementById('opClaimClientAvatar');
        if (clientRow) {
            if (clientVal) {
                if (clientName) clientName.textContent = clientVal;
                if (clientPresenceEl) clientPresenceEl.innerHTML = clientPresence;
                if (clientAvEl) {
                    if (clientIcon) {
                        clientAvEl.outerHTML = '<img id="opClaimClientAvatar" class="lbcm-client-avatar" src="' + clientIcon.replace(/"/g,'&quot;') + '" alt="" loading="lazy">';
                    } else {
                        clientAvEl.className = 'lbcm-client-avatar lbcm-client-avatar--fallback';
                        clientAvEl.innerHTML = '<i class="fa-duotone fa-user"></i>';
                    }
                }
                clientRow.style.display = 'flex';
            } else {
                clientRow.style.display = 'none';
            }
        }

        const er = document.getElementById('opClaimEarning');
        if (er) {
            let modalEarning = earning;
            const card = document.querySelector(`.booster-price[data-order="${orderId}"]`);
            const cardAmount = (card?.querySelector('.lb-earn-amount')?.textContent || '').trim();
            if (cardAmount) modalEarning = cardAmount;
            er.textContent = modalEarning;
        }

        // Dynamic confirmation text. is_duo = 1 means Play with Booster order.
        var confirmText = document.getElementById('opClaimConfirmText');
        var claimWarning = document.getElementById('opClaimDuoWarning');
        var claimWarningTitle = document.getElementById('opClaimWarningTitle');
        var claimWarningText = document.getElementById('opClaimWarningText');
        if (claimWarning) {
            claimWarning.classList.add('is-visible');
        }
        if (isRanked5s) {
            const laneLabel = ranked5sLane ? ranked5sLane.replace('TopLane','Top').replace('MidLane','Mid').replace('AdCarry','ADC') : 'selected lane';
            if (claimWarningTitle) claimWarningTitle.innerHTML = '<i class="fa-duotone fa-triangle-exclamation"></i> Your lane: ' + laneLabel;
            if (claimWarningText) claimWarningText.textContent = 'Be ready to start and coordinate in chat.';
            if (confirmText) {
                confirmText.textContent = 'I can play this lane, start now, and use the order chat.';
            }
        } else if (isDuo) {
            if (claimWarningTitle) claimWarningTitle.innerHTML = '<i class="fa-duotone fa-triangle-exclamation"></i> Play with client';
            if (claimWarningText) claimWarningText.textContent = 'Have your account ready and start now.';
            if (confirmText) {
                confirmText.textContent = "I'm ready now and will message the client.";
            }
        } else {
            if (claimWarningTitle) claimWarningTitle.innerHTML = '<i class="fa-duotone fa-triangle-exclamation"></i> Start now';
            if (claimWarningText) claimWarningText.textContent = 'Only claim if you are ready.';
            if (confirmText) {
                confirmText.textContent = "I'm ready now and won't delay the order.";
            }
        }

        // Reset checkbox
        var cb = document.getElementById('opClaimConfirm');
        var confirmLabel = document.getElementById('opClaimConfirmLabel');
        if (cb) cb.checked = false;
        if (confirmLabel) confirmLabel.classList.remove('is-checked');

        // Generate new math question on each modal open
        (function() {
            const ops = [
                function(){ var a=Math.floor(Math.random()*9)+1, b=Math.floor(Math.random()*9)+1; return {q:a+' + '+b+' =', ans:a+b}; },
                function(){ var a=Math.floor(Math.random()*5)+5, b=Math.floor(Math.random()*a)+1; return {q:a+' - '+b+' =', ans:a-b}; },
                function(){ var a=Math.floor(Math.random()*5)+2, b=Math.floor(Math.random()*5)+2; return {q:a+' x '+b+' =', ans:a*b}; },
            ];
            var op = ops[Math.floor(Math.random()*ops.length)]();
            var q = document.getElementById('opMathQuestion');
            var ans = document.getElementById('opMathAnswer');
            var fb = document.getElementById('opMathFeedback');
            if (q) q.textContent = 'What is ' + op.q;
            if (ans) { ans.value = ''; ans.dataset.correct = op.ans; ans.style.borderColor = 'rgba(255,255,255,.15)'; }
            if (fb) { fb.style.display = 'none'; fb.textContent = ''; }
        })();

        // Timer: first try to mirror whatever the card is showing; else use fallback seconds
        if (!syncFromCard(orderId) && Number.isFinite(nextIn) && nextIn > 0) {
            fallbackLeft = nextIn;
            setTimerText(formatMMSS(fallbackLeft));
        } else if (!syncFromCard(orderId)) {
            setTimerText('');
        }

        startTick(orderId);
        startClaimStatusPolling(orderId);
    });

// Checkbox toggle visual
var confirmLabel = document.getElementById('opClaimConfirmLabel');
var confirmCb = document.getElementById('opClaimConfirm');
if (confirmLabel && confirmCb) {
    confirmCb.addEventListener('change', function() {
        confirmLabel.classList.toggle('is-checked', confirmCb.checked);
    });
}

// Core validation function – returns true if allowed to proceed
function lbcmValidate() {
    // 1) Checkbox must be checked
    var cb = document.getElementById('opClaimConfirm');
    if (!cb || !cb.checked) {
        var cl = document.getElementById('opClaimConfirmLabel');
        if (cl) {
            cl.style.borderColor = '#e74c3c';
            cl.style.background = 'rgba(231,76,60,.12)';
            setTimeout(function(){ cl.style.borderColor = ''; cl.style.background = ''; }, 2000);
        }
        return false;
    }

    // 2) Math answer must be correct
    var mathAns = document.getElementById('opMathAnswer');
    var mathFb  = document.getElementById('opMathFeedback');
    var mathCorrect = parseInt((mathAns ? mathAns.dataset.correct : ''), 10);
    var mathGiven   = parseInt(((mathAns ? mathAns.value : '') || '').trim(), 10);
    if (!mathAns || isNaN(mathGiven) || mathGiven !== mathCorrect) {
        if (mathAns) { mathAns.style.borderColor = '#e74c3c'; mathAns.focus(); }
        if (mathFb) { mathFb.textContent = 'Wrong answer \u2013 try again'; mathFb.style.color = '#e74c3c'; mathFb.style.fontWeight = '950'; mathFb.style.display = ''; }
        return false;
    }
    if (mathAns) mathAns.style.borderColor = '#2ecc71';
    if (mathFb) { mathFb.textContent = '\u2713'; mathFb.style.color = '#2ecc71'; mathFb.style.display = ''; }
    return true;
}

// Block the Claim Order button click directly (catches any path incl. global ajax-form handlers)
var claimBtn = document.getElementById('opClaimSubmitBtn');
if (claimBtn) {
    claimBtn.addEventListener('click', function(e) {
        if (!lbcmValidate()) {
            e.preventDefault();
            e.stopImmediatePropagation();
        }
    }, true); // capture phase – fires before everything else
}

// Also block form submit (own fetch handler)
if (formEl) {
    formEl.addEventListener('submit', async function (e) {
        e.preventDefault();
        e.stopImmediatePropagation();

        if (!lbcmValidate()) return;

        const btn = document.getElementById('opClaimSubmitBtn');
        const spinner = document.getElementById('opClaimSpinner');
        const btnIcon = document.getElementById('opClaimBtnIcon');
        const btnLbl = document.getElementById('opClaimBtnLabel');

        if (btn) btn.disabled = true;
        if (spinner) spinner.style.display = 'block';
        if (btnIcon) btnIcon.style.display = 'none';
        if (btnLbl) btnLbl.textContent = 'Claiming...';

        try {
            const fd = new FormData(formEl);
            const res = await fetch(formEl.getAttribute('action') || window.location.href, {
                method: 'POST',
                body: fd,
                credentials: 'same-origin',
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            });

            const raw = await res.text();
            let data = null;
            try {
                data = JSON.parse(raw);
            } catch (e) {
                // Some installations prepend notices or whitespace to the JSON response.
                // Recover the final JSON object so the exact server error is still shown.
                const start = raw.indexOf('{');
                const end = raw.lastIndexOf('}');
                if (start !== -1 && end > start) {
                    try { data = JSON.parse(raw.slice(start, end + 1)); } catch (ignored) { data = null; }
                }
            }

            const ok = data
                ? (
                    data.success === true ||
                    data.status === true ||
                    data.result === true ||
                    data.type === 'success' ||
                    data.status === 'success' ||
                    (typeof data.redirect === 'string' && data.redirect.length > 0) ||
                    (typeof data.redirectUrl === 'string' && data.redirectUrl.length > 0)
                )
                : false;

            if (ok) {
                const oid = document.getElementById('opClaimOrderId')?.value || '';
                const url = (data && typeof data.redirect === 'string' && data.redirect) ? data.redirect :
                    ((data && typeof data.redirectUrl === 'string' && data.redirectUrl) ? data.redirectUrl :
                    (redirectUrl || (oid ? (`<?= BSTR_URL ?>/order/` + oid) : '')));
                if (url) { window.location.href = url; return; }
                window.location.reload();
                return;
            }

            function findServerMessage(value, depth) {
                if (depth > 6 || value === null || typeof value === 'undefined') return '';
                if (typeof value === 'string') {
                    const text = value.trim();
                    if (!text) return '';
                    if ((text.startsWith('{') && text.endsWith('}')) || (text.startsWith('[') && text.endsWith(']'))) {
                        try { return findServerMessage(JSON.parse(text), depth + 1); } catch (ignored) {}
                    }
                    return text;
                }
                if (Array.isArray(value)) {
                    for (const item of value) {
                        const found = findServerMessage(item, depth + 1);
                        if (found) return found;
                    }
                    return '';
                }
                if (typeof value === 'object') {
                    const preferredKeys = ['message', 'error', 'msg', 'text', 'description', 'detail'];
                    for (const key of preferredKeys) {
                        if (Object.prototype.hasOwnProperty.call(value, key)) {
                            const found = findServerMessage(value[key], depth + 1);
                            if (found) return found;
                        }
                    }
                    const nestedKeys = ['sendToast', 'toast', 'data', 'response', 'result'];
                    for (const key of nestedKeys) {
                        if (Object.prototype.hasOwnProperty.call(value, key)) {
                            const found = findServerMessage(value[key], depth + 1);
                            if (found) return found;
                        }
                    }
                }
                return '';
            }

            let msg = findServerMessage(data, 0);

            // Recover a message from malformed JSON or PHP output around the JSON.
            if (!msg && raw) {
                const messageMatch = raw.match(/[\"'](?:message|error|msg)[\"']\s*:\s*[\"']((?:\\.|[^\"'])*)[\"']/i);
                if (messageMatch && messageMatch[1]) {
                    try { msg = JSON.parse('\"' + messageMatch[1].replace(/\"/g, '\\\"') + '\"'); }
                    catch (ignored) { msg = messageMatch[1]; }
                }
            }

            // Never guess that every failed claim is caused by an order limit.
            // Only show the slot message when the backend explicitly identifies that error.
            if (data && data.error_code === 'ORDER_LIMIT_REACHED') {
                const limitType = data.limit_type === 'duo' ? 'duo' : 'solo';
                const activeOrders = Number.isFinite(Number(data.active_orders)) ? Number(data.active_orders) : null;
                const orderLimit = Number.isFinite(Number(data.order_limit)) ? Number(data.order_limit) : null;
                if (!msg) {
                    msg = orderLimit === 0
                        ? `You can’t claim ${limitType} orders because your ${limitType} order limit is set to 0.`
                        : `You don’t have any free ${limitType} order slots${activeOrders !== null && orderLimit !== null ? ` (${activeOrders}/${orderLimit})` : ''}.`;
                }
            }
            if (!msg) {
                msg = 'Could not claim this order. Please reload the Orders Panel and try again.';
            }
            const oid = document.getElementById('opClaimOrderId')?.value || '';
            if (data && (data.claimed === true || data.removeOrder === true)) {
                showOrderAlreadyClaimed(oid, msg, true);
            } else {
                showClaimNotice(msg, 'error');
            }
        } catch (err) {
            console.error('Order claim request failed:', err);
            showClaimNotice('Could not claim this order. Please reload the Orders Panel and try again.', 'error');
        } finally {
            if (!claimLocked) {
                if (btn) btn.disabled = false;
                if (spinner) spinner.style.display = 'none';
                if (btnIcon) btnIcon.style.display = '';
                if (btnLbl) btnLbl.textContent = 'Claim Order';
            }
        }
    });
}

    modalEl.addEventListener('hidden.bs.modal', function () {
        if (tick) clearInterval(tick);
        if (claimStatusPoll) clearInterval(claimStatusPoll);
        tick = null;
        claimStatusPoll = null;
        fallbackLeft = null;
        claimLocked = false;
        setTimerText('');
        resetClaimButton();
    });
})();



// Open claim modal from browser notification click
// =======================================================
(function () {
    function cssEscape(v) {
        if (window.CSS && typeof window.CSS.escape === 'function') return window.CSS.escape(String(v));
        return String(v).replace(/[^a-zA-Z0-9_-]/g, '\\$&');
    }

    function showModalWithTrigger(trigger) {
        var modalEl = document.getElementById('opClaimModal');
        if (!modalEl || !trigger || typeof bootstrap === 'undefined') return false;
        bootstrap.Modal.getOrCreateInstance(modalEl, { backdrop: 'static', keyboard: false }).show(trigger);
        return true;
    }

    window.lb_open_booster_claim_modal = function(orderId, orderData) {
        orderId = parseInt(orderId, 10) || 0;
        if (!orderId) return false;

        var realBtn = document.querySelector('.op-claim-btn[data-order-id="' + cssEscape(orderId) + '"]');
        if (realBtn) {
            realBtn.click();
            return true;
        }

        var data = orderData || {};
        var fakeBtn = document.createElement('button');
        fakeBtn.type = 'button';
        fakeBtn.setAttribute('data-order-id', String(orderId));
        fakeBtn.setAttribute('data-status', 'PAID');
        fakeBtn.setAttribute('data-boost', String(data.details || data.name || 'Order #' + orderId));
        fakeBtn.setAttribute('data-earning', '');
        fakeBtn.setAttribute('data-next-change-in', '0');
        fakeBtn.setAttribute('data-view-url', '<?= BSTR_URL ?>/order/' + orderId);
        fakeBtn.setAttribute('data-client', String(data.client_username || ''));
        fakeBtn.setAttribute('data-client-icon', String(data.client_icon || ''));
        fakeBtn.setAttribute('data-client-presence', '');
        return showModalWithTrigger(fakeBtn);
    };

    function getPendingClaimData(orderId) {
        try {
            var raw = localStorage.getItem('lb_pending_claim_order');
            if (!raw) return null;
            var data = JSON.parse(raw);
            if (!data || parseInt(data.order_id, 10) !== orderId) return null;
            if (data.ts && (Date.now() - parseInt(data.ts, 10)) > 300000) return null;
            return data;
        } catch(e) { return null; }
    }

    function consumePendingClaim() {
        var params = new URLSearchParams(window.location.search || '');
        var orderId = parseInt(params.get('claim_order') || '0', 10) || 0;
        if (!orderId) {
            try {
                var raw = localStorage.getItem('lb_pending_claim_order');
                if (raw) orderId = parseInt((JSON.parse(raw) || {}).order_id, 10) || 0;
            } catch(e) {}
        }
        if (!orderId) return;

        var data = getPendingClaimData(orderId) || {};
        var tries = 0;
        var timer = setInterval(function(){
            tries++;
            if (window.lb_open_booster_claim_modal(orderId, data) || tries >= 20) {
                clearInterval(timer);
                try { localStorage.removeItem('lb_pending_claim_order'); } catch(e) {}
                if (params.has('claim_order') && window.history && window.history.replaceState) {
                    params.delete('claim_order');
                    var nextUrl = window.location.pathname + (params.toString() ? ('?' + params.toString()) : '') + window.location.hash;
                    window.history.replaceState({}, document.title, nextUrl);
                }
            }
        }, 250);
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', consumePendingClaim);
    } else {
        consumePendingClaim();
    }
})();

// Notes tooltip (custom)
// =======================================================
(function () {
    const tip = document.createElement('div');
    tip.className = 'op-tooltip';
    document.body.appendChild(tip);

    let active = null;

    function show(el) {
        const text = (el?.getAttribute('data-tip') || '').trim();
        if (!text) return;

        // Remove any native title-tooltips inside the note row (they look out of place)
        try {
            const withTitle = el.querySelectorAll('[title]');
            withTitle.forEach((n) => {
                if (n.dataset._opTitle === undefined) {
                    n.dataset._opTitle = n.getAttribute('title') || '';
                    n.removeAttribute('title');
                }
            });
            if (el.getAttribute('title')) {
                el.dataset._opTitle = el.getAttribute('title') || '';
                el.removeAttribute('title');
            }
        } catch (e) {}

        tip.textContent = text;
        tip.classList.add('is-visible');
        active = el;
    }

    function hide() {
        tip.classList.remove('is-visible');

        // Restore native title attributes we removed (keep behaviour elsewhere intact)
        try {
            if (active) {
                const restore = active.querySelectorAll('[data-_op-title], [data-_optitle], [data-_opTitle]');
            }
            if (active) {
                const nodes = active.querySelectorAll('[data-_opTitle]');
                nodes.forEach((n) => {
                    const v = n.dataset._opTitle;
                    if (v !== undefined && v !== '') n.setAttribute('title', v);
                    delete n.dataset._opTitle;
                });
                if (active.dataset && active.dataset._opTitle !== undefined) {
                    const v = active.dataset._opTitle;
                    if (v !== '') active.setAttribute('title', v);
                    delete active.dataset._opTitle;
                }
            }
        } catch (e) {}

        active = null;
    }

    function move(e) {
        if (!active) return;
        const pad = 14;
        const w = tip.offsetWidth;
        const h = tip.offsetHeight;

        let x = e.clientX + pad;
        let y = e.clientY + pad;

        // keep in viewport
        const vw = window.innerWidth;
        const vh = window.innerHeight;
        if (x + w + 8 > vw) x = Math.max(8, vw - w - 8);
        if (y + h + 8 > vh) y = Math.max(8, vh - h - 8);

        tip.style.left = x + 'px';
        tip.style.top  = y + 'px';
    }

    document.addEventListener('mouseover', (e) => {
        const el = e.target?.closest?.('.op-note-row');
        if (!el) return;
        show(el);
    });

    document.addEventListener('mouseout', (e) => {
        const el = e.target?.closest?.('.op-note-row');
        if (!el) return;
        // Only hide if leaving the note row
        if (active === el) hide();
    });

    document.addEventListener('mousemove', move);

    // In case the page scrolls while hovering
    window.addEventListener('scroll', () => { if (active) hide(); }, { passive: true });
})();

// INITIALIZATION OF DATATABLES
        // =======================================================
        HSCore.components.HSDatatables.init($('#orders_table'), {
            language: {
                zeroRecords: ``
            }
        });

        // FILTERS (Region / Solo-Duo)
        // =======================================================
        (function () {
            const $table = $('#orders_table');
            if (!$table.length || !$.fn.dataTable) return;

            const dt = $table.DataTable();

            const $wrap = $('#opTableWrap');
            const $empty = $('#opEmptyState');
            function syncEmptyState(){
                const cnt = dt.rows({ filter: 'applied' }).count();
                if (cnt === 0){
                    $wrap.addClass('op-empty-active');
                    $empty.show();
                } else {
                    $wrap.removeClass('op-empty-active');
                    $empty.hide();
                }
            }
            dt.on('draw', syncEmptyState);
            syncEmptyState();

            let region = 'any';
            let queue = 'any';

            // DataTables custom filter (scoped to this table)
            $.fn.dataTable.ext.search.push(function (settings, data, dataIndex) {
                if (settings.nTable !== $table[0]) return true;

                const node = dt.row(dataIndex).node();
                if (!node) return true;

                const r = String(node.getAttribute('data-region') || '').toLowerCase();
                const q = String(node.getAttribute('data-queue') || '').toLowerCase();

                if (region !== 'any' && r !== region) return false;
                if (queue !== 'any' && q !== queue) return false;

                return true;
            });

            function setActive($group, attr, val) {
                $group.find('.op-pill').removeClass('op-pill--active');
                $group.find(`.op-pill[${attr}="${val}"]`).addClass('op-pill--active');
            }

            $('#opRegionFilter').on('click', '.op-pill', function () {
                region = String($(this).data('region') || 'any');
                setActive($('#opRegionFilter'), 'data-region', region);
                dt.draw();
            });

            $('#opQueueFilter').on('click', '.op-pill', function () {
                queue = String($(this).data('queue') || 'any');
                setActive($('#opQueueFilter'), 'data-queue', queue);
                dt.draw();
            });

            // initial draw so filters are applied consistently
            dt.draw();
        })();

                                // PRICE UPDATER (countdown + instant refresh without F5) — fixed (auto refresh at 00:00 + no freeze)
                // =======================================================
                (function () {
                    const nextChangeAtByOrder = new Map();      // orderId -> absolute server timestamp (ms) of the next cut step
                    const lastRefreshTryByOrder = new Map();    // orderId -> client timestamp (ms) of last instant refresh try


                    const pendingSyncByOrder = new Map();       // orderId -> true while we are syncing price/cut
                    let lastFetchAt = 0;
                    let serverOffsetMs = 0; // serverNowMs = Date.now() - serverOffsetMs
                    const expiredPriceSyncIds = new Set();
                    let expiredPriceSyncTimer = null;

                    const refreshCooldownMs = 2000;
                    const DEFAULT_STEP_SECONDS = 90; // booster cut step interval (seconds)

                    function serverNowMs() {
                        return Date.now() - serverOffsetMs;
                    }

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

                    function showTimerChip(td, show) {
                        const chip = td?.querySelector('.lb-chip-timer');
                        if (!chip) return;
                        chip.style.display = show ? '' : 'none';
                    }

                    function clearTimerText(td) {
                        const timerEl = td?.querySelector('.lb-timer-val');
                        if (timerEl) timerEl.textContent = '';
                    }

                    function setNextChange(orderId, nextChangeInSeconds, td, serverTsSeconds) {
                        const id = String(orderId);

                        if (nextChangeInSeconds === null || typeof nextChangeInSeconds === 'undefined') {
                            nextChangeAtByOrder.delete(id);
                            lastRefreshTryByOrder.delete(id);
                            showTimerChip(td, false);
                            clearTimerText(td);
                            return;
                        }

                        let sec = parseInt(nextChangeInSeconds, 10);
                        if (!Number.isFinite(sec) || sec <= 0) sec = 1;

                        const serverNow = (Number.isFinite(serverTsSeconds) && serverTsSeconds > 0)
                            ? (serverTsSeconds * 1000)
                            : serverNowMs();

                        nextChangeAtByOrder.set(id, serverNow + sec * 1000);
                        lastRefreshTryByOrder.delete(id);

                        // Timer-Chip einblenden, aber Text NICHT überschreiben —
                        // der 1s-Tick rendert den Countdown kontinuierlich ohne Sprung.
                        showTimerChip(td, true);
                    }

                    function collectOrderIds() {
                        return [...document.querySelectorAll('.booster-price')]
                            .map(td => td.getAttribute('data-order'))
                            .filter(Boolean);
                    }

                    // Fetch updated prices. If orderIds is provided, fetch only those (used for instant refresh at 00:00)
                    function fetchUpdatedPrices(force = false, orderIds = null, broadcast = false) {
                        const now = Date.now();
                        if (!force && now - lastFetchAt < 700) return;
                        lastFetchAt = now;

                        const ids = Array.isArray(orderIds) && orderIds.length ? orderIds : collectOrderIds();
                        if (!ids.length) return;

                        $.ajax({
                            url: '<?= AJAX_URL ?>',
                            method: 'POST',
                                                        data: { action: 'get_updated_prices', order_ids: ids, broadcast_price_update: broadcast ? 1 : 0, _ts: Date.now() },
                            success: function (response) {
                                if (typeof response === 'string') {
                                    try { response = JSON.parse(response); } catch (e) {}
                                }

                                if (!response || !response.success) {
                                    // If we tried an instant refresh for a specific order and it failed, retry soon.
                                    if (Array.isArray(orderIds) && orderIds.length) {
                                        setTimeout(() => fetchUpdatedPrices(true, orderIds), 1200);
                                    }
                                    return;
                                }

                                const serverTs = parseInt(response.server_ts, 10);
                                if (Number.isFinite(serverTs) && serverTs > 0) {
                                    serverOffsetMs = Date.now() - (serverTs * 1000);
                                }

                                const returnedIds = new Set((response.data || []).map(o => String(o.order_id)));

                                (response.data || []).forEach(order => {
                                    const td = document.querySelector(`.booster-price[data-order="${order.order_id}"]`);
                                    if (!td) return;

                                    const amountEl = td.querySelector('.lb-earn-amount');
                                    const prev = (amountEl?.textContent || '').trim();
                                    const next = String(order.price || '').trim();

                                    if (amountEl) {
                                        amountEl.innerHTML = next;
                                        if (prev && prev !== next) bump(amountEl);
                                    }

                                    const rowEl = td.closest('tr');
                                    const claimBtn = rowEl ? rowEl.querySelector('.op-claim-btn[data-order-id]') : null;
                                    if (claimBtn) {
                                        claimBtn.setAttribute('data-earning', next);
                                        if (typeof order.next_change_in !== 'undefined' && order.next_change_in !== null) {
                                            claimBtn.setAttribute('data-next-change-in', String(order.next_change_in));
                                        }
                                    }

                                    setNextChange(order.order_id, order.next_change_in, td, serverTs);
                                    pendingSyncByOrder.delete(String(order.order_id));
                                });

                                // Only remove missing rows on a FULL refresh (not on instant refresh for a single order)
                                if (!orderIds) {
                                    document.querySelectorAll('.booster-price').forEach(td => {
                                        const id = String(td.getAttribute('data-order') || '');
                                        if (id && !returnedIds.has(id)) {
                                            const row = td.closest('tr');
                                            const text = row ? (row.textContent || '') : '';
                                            const claimedTextEl = row ? row.querySelector('.op-r5s-lanes__head small') : null;
                                            const claimedText = claimedTextEl ? claimedTextEl.textContent.trim() : '';
                                            const claimedMatch = claimedText.match(/(\d+)\s*\/\s*(\d+)/);
                                            const isOpenRanked5s = text.indexOf('Ranked 5s') !== -1
                                                && claimedMatch
                                                && parseInt(claimedMatch[1], 10) < parseInt(claimedMatch[2], 10);

                                            if (isOpenRanked5s) {
                                                return;
                                            }

                                            row?.remove();
                                            nextChangeAtByOrder.delete(id);
                                            lastRefreshTryByOrder.delete(id);
                                        }
                                    });
                                }
                            },
                            error: function () {
                                if (Array.isArray(orderIds) && orderIds.length) {
                                    setTimeout(() => fetchUpdatedPrices(true, orderIds), 1200);
                                }
                            }
                        });
                    }

                    function queueExpiredPriceSync(orderId) {
                        const id = String(orderId || '');
                        if (!id || pendingSyncByOrder.get(id)) return;

                        pendingSyncByOrder.set(id, true);
                        expiredPriceSyncIds.add(id);

                        if (expiredPriceSyncTimer) return;
                        expiredPriceSyncTimer = setTimeout(function () {
                            const ids = Array.from(expiredPriceSyncIds);
                            expiredPriceSyncIds.clear();
                            expiredPriceSyncTimer = null;

                            if (!ids.length) return;

                            // PHP recalculates the authoritative price and broadcasts a
                            // price_update event to every open booster panel via WebSocket.
                            fetchUpdatedPrices(true, ids, true);
                        }, 120);
                    }

                    function initTimersFromDom() {
                        document.querySelectorAll('.booster-price .lb-timer-val[data-seconds]').forEach(el => {
                            const td = el.closest('.booster-price');
                            const orderId = td?.getAttribute('data-order');
                            let sec = parseInt(el.getAttribute('data-seconds') || '0', 10);

                            if (!orderId) return;

                            // data-seconds="0" means next_change_in was null (max cut reached) → hide timer
                            if (!Number.isFinite(sec) || sec <= 0) {
                                showTimerChip(td, false);
                                nextChangeAtByOrder.delete(String(orderId));
                                return;
                            }

                            // Option B: Bereits laufenden Timer NICHT zurücksetzen.
                            // Der Price-Updater-Tick pflegt den State weiter; nur neue Orders initialisieren.
                            if (nextChangeAtByOrder.has(String(orderId))) {
                                showTimerChip(td, true);
                                return;
                            }

                            nextChangeAtByOrder.set(String(orderId), serverNowMs() + sec * 1000);
                            showTimerChip(td, true);
                            el.textContent = formatMMSS(sec);
                        });
                    }

                    // Timer-State nach außen exponieren, damit refreshOrdersPanel
                    // beim Panel-Reload keine laufenden Timer zurücksetzt.
                    window._lbNextChangeAt = nextChangeAtByOrder;
                    window.initTimersFromDom = initTimersFromDom;

                    // Init from server-rendered data-seconds (if present)
                    initTimersFromDom();

                    // Initial price sync once after page load.
                    fetchUpdatedPrices(true);

                    window.lbOrdersPanelPriceUpdate = function(data){
                        if (data && data.order_id && data.price) {
                            const td = document.querySelector(`.booster-price[data-order="${data.order_id}"]`);
                            if (td) {
                                const amountEl = td.querySelector('.lb-earn-amount');
                                const prev = (amountEl?.textContent || '').trim();
                                const next = String(data.price || '').trim();
                                if (amountEl) {
                                    amountEl.innerHTML = next;
                                    if (prev && prev !== next) bump(amountEl);
                                }
                                if (typeof data.next_change_in !== 'undefined') {
                                    setNextChange(data.order_id, data.next_change_in, td, data.server_ts || null);
                                }
                                return;
                            }
                        }
                        // Fallback only for event payloads without full price data.
                        fetchUpdatedPrices(true);
                    };

                    // Realtime mode:
                    // Kein dauerhaftes 5s-Price-Polling mehr.
                    // Preise werden initial geladen, bei Timer-Ablauf synchronisiert
                    // und zusätzlich bei WebSocket Events aktualisiert.
                    function attachRealtimePriceUpdates() {
                        const socket = window.lbSocket;
                        if (!socket || socket.__lbPanelPriceHandlersAttached) return;
                        socket.__lbPanelPriceHandlersAttached = true;

                        socket.on('orders_panel_update', function (data) {
                            if (typeof window.lbOrdersPanelPriceUpdate === 'function') window.lbOrdersPanelPriceUpdate(data || {});
                        });

                        socket.on('price_update', function (data) {
                            if (typeof window.lbOrdersPanelPriceUpdate === 'function') window.lbOrdersPanelPriceUpdate(data || {});
                        });

                        socket.on('new_order', function () {
                            setTimeout(function () {
                                if (typeof window.initTimersFromDom === 'function') window.initTimersFromDom();
                                // Do not fetch prices here. Panel partial refresh handles new rows.
                            }, 500);
                        });
                    }

                    attachRealtimePriceUpdates();
                    setTimeout(attachRealtimePriceUpdates, 1000);
                    setTimeout(attachRealtimePriceUpdates, 3000);

                    // Fallback nur wenn WebSocket nicht verbunden ist.
                    // Dadurch bleibt das Panel nutzbar, aber ohne AJAX-Dauerfeuer.
                    setInterval(() => {
                        if (document.visibilityState === 'hidden') return;
                        if (window.lbRealtimeConnected === true) return;
                        fetchUpdatedPrices(false);
                    }, 180000);

                    // Tick UI every second + instant refresh at 00:00 (with cooldown + auto retry)
                    setInterval(() => {
                        const nowServer = serverNowMs();

                        document.querySelectorAll('.booster-price').forEach(td => {
                            const orderId = String(td.getAttribute('data-order') || '');
                            const nextAt = nextChangeAtByOrder.get(orderId);

                            if (!nextAt) return;

                            const left = Math.ceil((nextAt - nowServer) / 1000);
                            const timerEl = td.querySelector('.lb-timer-val');

                            if (left > 0) {
                                if (timerEl) timerEl.textContent = formatMMSS(left);
                                return;
                            }

                            // left <= 0: cut step should happen now -> refresh without F5
                            // left <= 0: restart countdown immediately (no "...") and sync in background
                            nextChangeAtByOrder.set(orderId, nowServer + (DEFAULT_STEP_SECONDS * 1000));
                            if (timerEl) timerEl.textContent = formatMMSS(DEFAULT_STEP_SECONDS);
                            // Always ask PHP for the authoritative value at expiry.
                            // The request is batched and PHP broadcasts the result through
                            // price_update, so every connected booster panel changes together.
                            queueExpiredPriceSync(orderId);
                        });
                    }, 1000);
                })();;

    });


// =======================================================
// PARTIAL REFRESH: nur Orders Panel aktualisieren (ohne Reload)
// - lädt die aktuelle Seite im Hintergrund
// - extrahiert nur #orders_table tbody
// - updatet DataTables rows, Toast/Sound bleibt ununterbrochen
// =======================================================
(function () {
  let inFlight = false;
  let pending = false;
  let consecutiveFailures = 0;
  let lastRefreshAt = 0;
  let missedWhileHidden = false;
  let gapTimer = null;

  // Each refresh re-fetches the ENTIRE orders page just to extract one tbody, so
  // it is the most expensive request this panel makes. Two guards keep it from
  // exhausting the DB connection limit:
  //  - MIN_GAP_MS: a hard floor between two refreshes. The debounce alone did not
  //    provide one, so a burst of socket events meant a burst of full renders.
  //  - the visibility check below.
  const MIN_GAP_MS = 10000;

  async function refreshOrdersPanel() {
    const tableEl = document.querySelector('#orders_table');
    if (!tableEl) return;

    // Boosters play fullscreen, so this tab is hidden nearly all the time — yet it
    // still answered every broadcast with a full page fetch. Defer instead and
    // catch up once the tab is looked at again.
    if (document.visibilityState === 'hidden') { missedWhileHidden = true; return; }

    // Own timer handle rather than reusing `pending`, which already means
    // "a refresh finished while another was in flight" — overloading it would
    // let the finally block below schedule a second, duplicate refresh.
    const sinceLast = Date.now() - lastRefreshAt;
    if (sinceLast < MIN_GAP_MS) {
      if (gapTimer === null) {
        gapTimer = setTimeout(() => { gapTimer = null; refreshOrdersPanel(); }, MIN_GAP_MS - sinceLast);
      }
      return;
    }
    lastRefreshAt = Date.now();

    if (inFlight) { pending = true; return; }
    inFlight = true;
    pending = false;

    try {
      const base = window.location.href.split('#')[0];
      const url = base + (base.includes('?') ? '&' : '?') + '_lb_refresh=' + Date.now();

      const r = await fetch(url, {
        method: 'GET',
        credentials: 'include',
        cache: 'no-store',
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
      });

      if (!r.ok) throw new Error('HTTP ' + r.status);
      consecutiveFailures = 0;

      const html = await r.text();
      const doc = new DOMParser().parseFromString(html, 'text/html');
      const newTbody = doc.querySelector('#orders_table tbody');
      if (!newTbody) return;

      // -------------------------------------------------------
      // SMART DIFF: nur geänderte Rows updaten, Timer nie anfassen
      // -------------------------------------------------------
      const tmpClean = document.createElement('tbody');
      tmpClean.innerHTML = newTbody.innerHTML;

      // Neue Rows als Map: orderId -> <tr>
      const newRowMap = new Map();
      tmpClean.querySelectorAll('tr').forEach(tr => {
        const priceEl = tr.querySelector('.booster-price[data-order]');
        const id = priceEl?.getAttribute('data-order');
        if (id) newRowMap.set(String(id), tr);
      });

      const freshIds = new Set(newRowMap.keys());

      // Veraltete Orders aus dem Timer-State entfernen
      if (window._lbNextChangeAt) {
        window._lbNextChangeAt.forEach((_, id) => {
          if (!freshIds.has(id)) window._lbNextChangeAt.delete(id);
        });
      }

      const tbody = tableEl.querySelector('tbody');

      // DataTables instance (may or may not exist)
      let dt = null;
      try {
        if (window.$ && $.fn && $.fn.dataTable && $.fn.dataTable.isDataTable('#orders_table')) {
          dt = $('#orders_table').DataTable();
        }
      } catch(e) {}

      let needsDraw = false;

      // Bestehende Rows: updaten oder entfernen
      if (tbody) {
        tbody.querySelectorAll('tr').forEach(existingTr => {
          const priceEl = existingTr.querySelector('.booster-price[data-order]');
          const id = priceEl?.getAttribute('data-order');
          if (!id) return;

          if (!freshIds.has(id)) {
            // Order no longer exists in the refreshed panel output.
            // For Ranked 5s this also means all requested booster slots are filled, so the card must disappear like normal claimed orders.
            if (dt) {
              try { dt.row(existingTr).remove(); needsDraw = true; return; } catch(e) {}
            }
            existingTr.remove();
            return;
          }

          // Row existiert noch → nur nicht-Timer-Inhalte updaten
          const newTr = newRowMap.get(id);
          if (!newTr) return;

          // Jede Zelle einzeln vergleichen, Timer-Chip (.lb-chip-timer) komplett überspringen
          existingTr.querySelectorAll('td, th').forEach((existingTd, i) => {
            const newTd = newTr.querySelectorAll('td, th')[i];
            if (!newTd) return;

            // Timer-Chip in dieser Zelle? → nicht anfassen
            if (existingTd.querySelector('.lb-chip-timer') || existingTd.classList.contains('lb-chip-timer')) return;

            // Nur updaten wenn sich der HTML-Inhalt tatsächlich geändert hat
            // Dabei den aktuellen Timer-Text aus dem DOM retten und danach wiederherstellen
            const timerEl = existingTd.querySelector('.lb-timer-val');
            const savedTimerText = timerEl ? timerEl.textContent : null;

            const existingHtml = existingTd.innerHTML.replace(/\s+/g, ' ').trim();
            const newHtml      = newTd.innerHTML.replace(/\s+/g, ' ').trim();

            if (existingHtml !== newHtml) {
              existingTd.innerHTML = newTd.innerHTML;
              // Timer-Text wiederherstellen falls die Zelle einen Timer enthält
              if (savedTimerText !== null) {
                const restoredTimerEl = existingTd.querySelector('.lb-timer-val');
                if (restoredTimerEl) restoredTimerEl.textContent = savedTimerText;
              }
              // DataTables über geänderte Zelle informieren
              if (dt) { try { dt.row(existingTr).invalidate('dom'); needsDraw = true; } catch(e) {} }
            }
          });

          // Diese Row als verarbeitet markieren
          newRowMap.delete(id);
        });

        // Neue Rows (noch nicht im DOM) einfügen — über DT-API damit Filter/Suche/Paging greift
        newRowMap.forEach(newTr => {
          if (dt) {
            try { dt.row.add(newTr).draw(false); return; } catch(e) {}
          }
          // Fallback ohne DataTables
          tbody.appendChild(newTr);
          needsDraw = false; // already drawn above
        });
      }

      // Einmalig draw() für Removes + Invalidates (nicht für neue Rows, die draw(false) bereits selbst aufrufen)
      if (needsDraw && dt) {
        try { dt.draw(false); } catch(e) {}
      }

      // Safety net: no matter how it happened (race between two overlapping refreshes,
      // a stale DataTables cache entry, etc.), the same order should never appear as
      // two rows. Scan once after the merge and drop any extras, keeping the first.
      if (tbody) {
        const seenIds = new Set();
        let removedAny = false;
        tbody.querySelectorAll('tr').forEach(tr => {
          const priceEl = tr.querySelector('.booster-price[data-order]');
          const id = priceEl?.getAttribute('data-order');
          if (!id) return;
          if (seenIds.has(id)) {
            if (dt) {
              try { dt.row(tr).remove(); removedAny = true; return; } catch(e) {}
            }
            tr.remove();
            removedAny = true;
            return;
          }
          seenIds.add(id);
        });
        if (removedAny && dt) {
          try { dt.draw(false); } catch(e) {}
        }
      }

      // Nur neue Orders initialisieren (laufende Timer bleiben durch Option B unangetastet)
      if (typeof window.initTimersFromDom === 'function') window.initTimersFromDom();
    } catch (e) {
      consecutiveFailures++;
      console.log('lb_refresh_orders_panel failed:', e);
    } finally {
      inFlight = false;
      if (pending) {
        // A failed refresh used to retry after a fixed 250ms. During a server
        // outage every client's request fails instantly, so they all hammered
        // four times a second and kept the outage alive. Back off instead:
        // 250ms, 500ms, 1s, 2s ... capped at 30s, reset on the first success.
        const backoff = consecutiveFailures === 0
          ? 250
          : Math.min(250 * Math.pow(2, consecutiveFailures), 30000);
        setTimeout(refreshOrdersPanel, backoff + Math.floor(Math.random() * 1000));
      }
    }
  }

  window.lb_refresh_orders_panel = refreshOrdersPanel;

  // Realtime refresh:
  // Neue Orders und Panel Updates kommen über Socket.IO aus layouts/main.php.
  // Dieses Polling läuft nur noch als Fallback, falls WebSocket getrennt ist.
  function attachRealtimePanelUpdates() {
    const socket = window.lbSocket;
    if (!socket || socket.__lbPanelRefreshHandlersAttached) return;
    socket.__lbPanelRefreshHandlersAttached = true;

    let lbPanelRefreshDebounce = null;

    // A socket event is broadcast to EVERY booster with this panel open, and each
    // one answers by re-fetching the whole orders page. With a fixed delay they
    // all fire in the same instant, so one order change became a burst of heavy
    // simultaneous requests — the pattern that exhausted the DB connection limit.
    // Spreading them over a few seconds keeps the data just as fresh but turns
    // the spike into a trickle.
    function schedulePanelRefresh(delay) {
      clearTimeout(lbPanelRefreshDebounce);
      const jitter = Math.floor(Math.random() * 4000);
      lbPanelRefreshDebounce = setTimeout(refreshOrdersPanel, (delay || 350) + jitter);
    }

    socket.on('orders_panel_update', function (payload) {
      if (payload && payload.silent === true) {
        schedulePanelRefresh(500);
        return;
      }
      schedulePanelRefresh(350);
    });

    socket.on('ranked_5s_lanes_update', function () {
      schedulePanelRefresh(650);
    });

    socket.on('new_order', function (payload) {
      if (payload && (payload.silent === true || payload.form_id === <?= RANKED_5S_FORM_ID ?> || payload.type === 'ranked_5s')) {
        schedulePanelRefresh(650);
        return;
      }
      // Went through schedulePanelRefresh too: a plain setTimeout here bypassed
      // both the debounce and the jitter, so a new order still hit every open
      // panel at the same moment.
      schedulePanelRefresh(250);
    });
  }

  attachRealtimePanelUpdates();
  setTimeout(attachRealtimePanelUpdates, 1000);
  setTimeout(attachRealtimePanelUpdates, 3000);

  // Catch up on whatever was skipped while the tab was hidden, so deferring a
  // refresh never means showing stale orders once the booster looks back.
  document.addEventListener('visibilitychange', function () {
    if (document.visibilityState !== 'visible' || !missedWhileHidden) return;
    missedWhileHidden = false;
    refreshOrdersPanel();
  });

  const PANEL_REFRESH_FALLBACK_MS = 180000;
  setInterval(() => {
    if (document.visibilityState === 'hidden') return;
    if (window.lbRealtimeConnected === true) return;
    refreshOrdersPanel();
  }, PANEL_REFRESH_FALLBACK_MS);
})();


// Manual opener for the info modal (Cut & Limits)
$('#lbOpenOrdersInfo').on('click', function () {
    const modalEl = document.getElementById('lbOrdersInfoModal');
    if (!modalEl) return;

    try {
        const modal = bootstrap.Modal.getOrCreateInstance(modalEl, { backdrop: 'static', keyboard: false });
        modal.show();
    } catch (e) {
        // fallback
        modalEl.classList.add('show');
        modalEl.style.display = 'block';
        document.body.classList.add('modal-open');
    }
});

</script>
<?= $this->end() ?>
<style>
/* Patch: Play with booster badge soll sich wie ein normaler rechter Wert verhalten */
.op-kv-row > .op-opt-badge{
  justify-self: end !important;
  align-self: center !important;
  width: auto !important;
  min-width: 0 !important;
  max-width: max-content !important;
  flex: 0 0 auto !important;
  display: inline-flex !important;
  padding: 6px 12px !important;
  white-space: nowrap !important;
}

.op-kv-row > .op-opt-badge--yes,
.op-kv-row > .op-opt-badge--no{
  min-width: 0 !important;
}

.op-kv-row > .op-opt-badge span:last-child{
  flex: 0 0 auto !important;
}
</style>
<style>
/* Patch: Order Labels vollständig anzeigen */
.op-kv-row{
  grid-template-columns: minmax(172px, 42%) minmax(0, 1fr) !important;
}

.op-kv-left span{
  overflow: visible !important;
  text-overflow: clip !important;
  white-space: nowrap !important;
}

@media (max-width: 640px){
  .op-kv-row{
    grid-template-columns: 1fr !important;
  }
}
</style>

<style>
.op-tooltip{display:none !important;}
</style>


<script>
(function(){
  function polishRanked5sPanel(){
    document.querySelectorAll('.op-card[data-form-id="29"]').forEach(function(card){
      card.querySelectorAll('.op-kv-row, .op-pref-row').forEach(function(row){
        var label = row.querySelector('.op-kv-left span');
        if (label && label.textContent.trim().toLowerCase() === 'roles') {
          row.style.display = 'none';
        }
      });

      card.querySelectorAll('[class*="title"] span').forEach(function(span){
        if ((span.textContent || '').indexOf('Ranked 5s') !== -1) {
          span.style.whiteSpace = 'normal';
          span.style.overflow = 'visible';
          span.style.textOverflow = 'clip';
          span.style.display = 'inline';
          span.style.lineHeight = '1.18';
        }
      });
    });
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', polishRanked5sPanel);
  } else {
    polishRanked5sPanel();
  }

  setTimeout(polishRanked5sPanel, 300);
  setTimeout(polishRanked5sPanel, 1000);
})();
</script>


<script>
(function(){
  function fixRanked5sPanelTitleAndModal(){
    document.querySelectorAll('.op-card[data-form-id="29"]').forEach(function(card){
      card.querySelectorAll('span, h3, .op-title, .op-card-title, .op-order-title, .op-boost-title').forEach(function(el){
        var text = (el.textContent || '').trim();
        if (text.indexOf('Ranked 5s') !== -1) {
          el.style.whiteSpace = 'normal';
          el.style.overflow = 'visible';
          el.style.textOverflow = 'clip';
          el.style.display = 'block';
          el.style.maxWidth = 'none';
          el.style.lineHeight = '1.18';
          el.style.overflowWrap = 'anywhere';
        }
      });
    });

    var modal = document.getElementById('opClaimModal');
    if (modal) {
      modal.querySelectorAll('*').forEach(function(el){
        var txt = (el.textContent || '').trim();
        if (txt.indexOf('Only claim if you can start with the customer') !== -1) {
          el.textContent = txt.replace('Only claim if you can start with the customer and the other boosters immediately.', 'Start only if you are ready now.');
        }
      });
    }
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', fixRanked5sPanelTitleAndModal);
  } else {
    fixRanked5sPanelTitleAndModal();
  }

  document.addEventListener('click', function(){
    setTimeout(fixRanked5sPanelTitleAndModal, 50);
    setTimeout(fixRanked5sPanelTitleAndModal, 250);
  });

  setTimeout(fixRanked5sPanelTitleAndModal, 400);
  setTimeout(fixRanked5sPanelTitleAndModal, 1200);
})();
</script>


<script>
(function(){
  function fixRanked5sHeadTitle(){
    document.querySelectorAll('.op-card[data-form-id="29"] .op-head-title').forEach(function(title){
      title.style.width = '100%';
      title.style.maxWidth = '100%';
      title.style.display = 'flex';
      title.style.flexWrap = 'wrap';
      title.style.overflow = 'visible';
      title.style.whiteSpace = 'normal';

      var main = title.querySelector('span:first-child');
      if (main) {
        main.style.flex = '1 1 100%';
        main.style.maxWidth = '100%';
        main.style.display = 'block';
        main.style.whiteSpace = 'normal';
        main.style.overflow = 'visible';
        main.style.textOverflow = 'clip';
        main.style.overflowWrap = 'anywhere';
        main.style.lineHeight = '1.18';
      }

      var badge = title.querySelector('.op-ribbon-new');
      if (badge) {
        badge.style.flex = '0 0 auto';
        badge.style.whiteSpace = 'nowrap';
      }
    });
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', fixRanked5sHeadTitle);
  } else {
    fixRanked5sHeadTitle();
  }

  setTimeout(fixRanked5sHeadTitle, 250);
  setTimeout(fixRanked5sHeadTitle, 1000);
})();
</script>


<script>
(function(){
  function forceOrderTitleWrap(){
    document.querySelectorAll('.op-head-title').forEach(function(title){
      var main = title.querySelector('span:first-child');
      if (!main) return;

      title.style.minWidth = '0';
      title.style.maxWidth = '100%';
      title.style.width = '100%';
      title.style.display = 'grid';
      title.style.gridTemplateColumns = 'minmax(0, 1fr) auto';
      title.style.alignItems = 'start';
      title.style.columnGap = '8px';
      title.style.rowGap = '6px';
      title.style.whiteSpace = 'normal';
      title.style.overflow = 'visible';
      title.style.textOverflow = 'clip';

      main.style.minWidth = '0';
      main.style.maxWidth = '100%';
      main.style.width = '100%';
      main.style.display = 'block';
      main.style.whiteSpace = 'normal';
      main.style.overflow = 'visible';
      main.style.textOverflow = 'clip';
      main.style.wordBreak = 'normal';
      main.style.overflowWrap = 'anywhere';
      main.style.lineHeight = '1.18';

      var badge = title.querySelector('.op-ribbon-new');
      if (badge) {
        badge.style.width = 'auto';
        badge.style.maxWidth = 'none';
        badge.style.whiteSpace = 'nowrap';
        badge.style.overflow = 'visible';
        badge.style.textOverflow = 'clip';
        badge.style.justifySelf = 'start';
      }

      var parent = title.parentElement;
      while (parent && parent !== document.body) {
        if (parent.className && /op-head|op-card-head|op-order-head|op-head-main|op-head-copy/.test(String(parent.className))) {
          parent.style.minWidth = '0';
          parent.style.height = 'auto';
          parent.style.overflow = 'visible';
          parent.style.alignItems = 'flex-start';
        }
        parent = parent.parentElement;
      }
    });
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', forceOrderTitleWrap);
  } else {
    forceOrderTitleWrap();
  }

  setTimeout(forceOrderTitleWrap, 150);
  setTimeout(forceOrderTitleWrap, 700);
  document.addEventListener('click', function(){ setTimeout(forceOrderTitleWrap, 80); });
})();
</script>
