<?= $this->layout('booster/layouts/main', ['meta' => ['title' => 'Orders List - Booster Area | LoLBoost.gg']]) ?>

<?php
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
    $state = ['known'=>false,'online'=>false,'last_seen_at'=>null,'label'=>'Offline','title'=>'No client presence/activity found yet','age_seconds'=>null];
    if ($clientId <= 0) return $state;
    global $db;
    try {
      $rows = $db->run("SELECT created_at, TIMESTAMPDIFF(SECOND, created_at, NOW()) AS age_seconds, device_info FROM client_session_logs WHERE client_id = {$clientId} ORDER BY CASE WHEN device_info LIKE '%dashboard_presence%' THEN 0 ELSE 1 END, created_at DESC, id DESC LIMIT 1");
      $row = (!empty($rows) && is_array($rows)) ? reset($rows) : null;
      if (empty($row) || empty($row['created_at'])) return $state;
      $age = isset($row['age_seconds']) ? (int)$row['age_seconds'] : null;
      if ($age === null || $age < 0) { $ts = strtotime((string)$row['created_at']); if (!$ts) return $state; $age = max(0, time() - $ts); }
      $state['known'] = true; $state['last_seen_at'] = (string)$row['created_at']; $state['age_seconds'] = $age;
      $state['online'] = ($age <= 120);
      $state['label'] = $state['online'] ? 'Online' : ('Last Seen ' . lb_client_presence_format_ago($age));
      $state['title'] = $state['online'] ? 'Client is currently online' : ('Client last seen ' . lb_client_presence_format_ago($age) . ' ago');
    } catch (Throwable $e) {}
    return $state;
  }
}
if (!function_exists('lb_client_presence_badge_html')) {
  function lb_client_presence_badge_html($clientId): string {
    $p = lb_client_presence_state($clientId);
    $cls = $p['online'] ? 'is-online' : 'is-offline';
    return '<span class="lb-client-presence '.$cls.'" title="'.htmlspecialchars((string)$p['title'],ENT_QUOTES,'UTF-8').'"><span class="lb-client-presence__dot"></span><span>'.htmlspecialchars((string)$p['label'],ENT_QUOTES,'UTF-8').'</span></span>';
  }
}
if (!function_exists('op__json_decode_if_possible')) {
  function op__json_decode_if_possible($v) {
    if (!is_string($v)) return null; $s=trim($v); if($s==='') return null; $f=$s[0]??''; if($f!=='{'&&$f!=='[') return null; $d=json_decode($s,true); return (json_last_error()===JSON_ERROR_NONE)?$d:null;
  }
}
if (!function_exists('op_lb_value_selected')) {
  function op_lb_value_selected($v): bool {
    if ($v===null) return false; if (is_array($v)) return !empty($v); $s=trim((string)$v); if($s===''||$s==='[]') return false; $l=strtolower($s); if(in_array($l,['null','none','n/a','na','-','false','no'],true)) return false; return true;
  }
}
if (!function_exists('op_lb_skip_option')) {
  function op_lb_skip_option(array $row, string $option): bool {
    $game=strtolower(trim((string)($row['game']??'')));
    $isLol=in_array($game,['lol','league-of-legends','lol-classic','league-of-legends-classic'],true);
    if(in_array($option,['flash_position','is_offline_mode'],true)&&!$isLol) return true;
    if (($row['game']??'')==='val'&&$option==='flash_position') return true;
    $fid=(int)($row['form_id']??0);
    if(in_array($fid,[15,16],true)&&$option==='is_offline_mode') return true;
    if($option==='vpn_country'&&(!empty($row['is_duo'])||in_array($fid,[15,16],true))) return true;
    if(($fid===19||!empty($row['is_duo']))&&($option==='flash_position'||$option==='is_offline_mode')) return true;
    return false;
  }
}
if (!function_exists('op_lb_option_selected')) {
  function op_lb_option_selected(array $row, string $option, array $boolOptions): bool {
    if (!array_key_exists($option,$row)) return false;
    if (in_array($option,$boolOptions,true)) return isset($row[$option])&&(int)$row[$option]===1;
    $v=$row[$option]; if(is_array($v)) return !empty($v); $s=trim((string)$v); if($s===''||$s==='[]') return false;
    $l=strtolower($s); if(in_array($l,['null','none','n/a','na','-','false','no'],true)) return false; return true;
  }
}
if (!function_exists('op_lb_build_option_pool')) {
  function op_lb_build_option_pool(array $row): array {
    $pool=[];
    foreach (['options','extra_options','order_options','boost_options','boost_form','form_data','data','details','requirements','meta','metadata','prefs','preferences'] as $k) {
      if (!array_key_exists($k,$row)) continue; $v=$row[$k];
      if (is_array($v)) { if(function_exists('op__merge_pool')) op__merge_pool($pool,$v); continue; }
      $d=op__json_decode_if_possible($v); if(is_array($d)&&function_exists('op__merge_pool')) op__merge_pool($pool,$d);
    }
    foreach ($row as $k=>$v) { if(!is_string($v)) continue; $s=trim($v); if($s===''||strlen($s)>8000) continue; $d=op__json_decode_if_possible($s); if(is_array($d)&&function_exists('op__merge_pool')) op__merge_pool($pool,$d); }
    return $pool;
  }
}
if (!function_exists('op_lb_pick_pref_value')) {
  function op_lb_pick_pref_value(array $row, array $pool, string $key) {
    if (array_key_exists($key,$row)) return $row[$key]; if (array_key_exists($key,$pool)) return $pool[$key];
    $syn=['agents'=>['agents','agent','val_agents','agent_pool','preferred_agents','selected_agents'],'champions'=>['champions','champion','champ_pool','preferred_champions','selected_champions','booster_champions'],'roles'=>['roles','role','preferred_roles','selected_roles','booster_roles']];
    foreach (($syn[$key]??[]) as $k) { if(array_key_exists($k,$row)) return $row[$k]; if(array_key_exists($k,$pool)) return $pool[$k]; }
    return null;
  }
}

/* ── Option metadata: label + SVG/FA icon ── */
if (!function_exists('op_lb_option_meta')) {
  function op_lb_option_meta(string $option): array {
    $svgBase = 'https://lolboost.gg/public/assets/website/images/boost-forms/';
    $map = [
      'is_priority'           => ['label'=>'Priority',        'svg'=> $svgBase.'priority.svg',      'cls'=>'t-priority'],
      'is_streaming'          => ['label'=>'Streaming',       'svg'=> $svgBase.'stream-games1.svg', 'cls'=>'t-stream'],
      'is_solo_only'          => ['label'=>'Solo Only',       'svg'=> $svgBase.'solo-queue1.svg',   'cls'=>'t-soloonly'],
      'is_bonus_win'          => ['label'=>'Bonus Win',       'svg'=> $svgBase.'bonus-win1.svg',    'cls'=>'t-bonus'],
      'is_offline_mode'       => ['label'=>'Offline Mode',    'fa' => 'fa-solid fa-moon',           'cls'=>'t-stealth'],
      'is_coaching'           => ['label'=>'Coaching',        'fa' => 'fa-solid fa-microphone-lines','cls'=>'t-stream'],
      'is_hidden_duo'         => ['label'=>'Hidden Duo',      'fa' => 'fa-duotone fa-user-secret',  'cls'=>'t-stealth'],
      'is_undercover_winrate' => ['label'=>'Undercover WR',   'fa' => 'fa-solid fa-user-secret',    'cls'=>'t-stealth'],
      'is_moderate_kda'       => ['label'=>'Moderate KDA',    'fa' => 'fa-solid fa-chart-line',     'cls'=>'t-kda'],
      'vpn_country'           => ['label'=>'VPN',             'fa' => 'fa-solid fa-globe',          'cls'=>'t-vpn'],
    ];
    return $map[$option] ?? ['label'=> ucwords(str_replace('_',' ',$option)), 'fa'=>'fa-solid fa-circle', 'cls'=>'t-stealth'];
  }
}

if (!function_exists('op_lb_collect_active_options')) {
  /**
   * Returns flat list of active options for a row.
   * Each item: ['label'=>string, 'ico_html'=>string, 'key'=>string]
   */
  function op_lb_collect_active_options(array $row): array {
    $fid = (int)($row['form_id']??0);
    if (in_array($fid,[15,16],true)) return [];

    $boolKeys = ['is_priority','is_streaming','is_solo_only','is_bonus_win','is_offline_mode','is_hidden_duo','is_undercover_winrate','is_moderate_kda'];
    $extraKeys = ['vpn_country'];
    $allKeys = array_merge($boolKeys, $extraKeys);
    $result = [];

    foreach ($allKeys as $k) {
      if (op_lb_skip_option($row,$k)) continue;
      if (!op_lb_option_selected($row,$k,$boolKeys)) continue;

      $meta = op_lb_option_meta($k);
      $label = $meta['label'];

      // For non-bool (like vpn_country) add the value
      if (!in_array($k,$boolKeys,true)) {
        $v = trim(strip_tags((string)($row[$k]??'')));
        if ($v !== '' && strtolower($v) !== 'null') $label .= ': '.$v;
      }

      // Build icon html
      if (!empty($meta['svg'])) {
        $ico = '<img class="lb-tag__icon lb-tag__icon--image" src="'
          . htmlspecialchars($meta['svg'], ENT_QUOTES, 'UTF-8')
          . '" alt="" aria-hidden="true" loading="lazy">';
      } else {
        $ico = '<i class="'.htmlspecialchars($meta['fa']??'fa-solid fa-circle',ENT_QUOTES,'UTF-8').' lb-tag__icon" aria-hidden="true"></i>';
      }
      $result[] = ['key'=>$k, 'label'=>$label, 'ico'=>$ico, 'cls'=>($meta['cls']??'t-stealth')];
    }
    return $result;
  }
}


if (!function_exists('op_lb_render_pref_group')) {
  /**
   * Render specific champion/lane/agent preferences as one compact badge in the Orders List.
   * The selected images stay inside the hover/focus tooltip so the table row never gets stretched.
   */
  function op_lb_render_pref_group(array $items, int $maxPreview = 8, ?string $badgeLabel = null): string {
    $html = '';
    $tooltipLabel = 'Selected options';
    foreach ($items as $item) {
      if (!empty($item['label'])) $tooltipLabel = (string)$item['label'];
      $html .= (string)($item['html'] ?? '');
    }
    if (trim($html) === '') return '';

    if ($badgeLabel === null || trim($badgeLabel) === '') {
      $low = strtolower($tooltipLabel);
      if (str_contains($low, 'role') || str_contains($low, 'lane')) $badgeLabel = 'Specific Lanes';
      elseif (str_contains($low, 'agent')) $badgeLabel = 'Specific Agents';
      else $badgeLabel = 'Specific Champions';
    }

    if (!preg_match_all('/<img\b[^>]*>/i', $html, $m)) {
      $plain = trim(strip_tags($html));
      if ($plain === '') return '';
      return '<span class="lb-pref-pill lb-pref-group lb-pref-group--tooltip" tabindex="0" aria-label="'.htmlspecialchars($tooltipLabel, ENT_QUOTES, 'UTF-8').'">'
        . '<i class="fa-solid fa-list-check lb-pref-pill__icon" aria-hidden="true"></i>'
        . '<span>'.htmlspecialchars($badgeLabel, ENT_QUOTES, 'UTF-8').'</span>'
        . '<span class="lb-pref-tooltip" role="tooltip"><span class="lb-pref-tooltip__title">'.htmlspecialchars($tooltipLabel, ENT_QUOTES, 'UTF-8').'</span><span class="lb-pref-tooltip__text">'.htmlspecialchars($plain, ENT_QUOTES, 'UTF-8').'</span></span>'
        . '</span>';
    }

    $imgs = $m[0];
    $count = count($imgs);
    return '<span class="lb-pref-pill lb-pref-group lb-pref-group--tooltip" tabindex="0" aria-label="'.htmlspecialchars($tooltipLabel, ENT_QUOTES, 'UTF-8').'">'
      . '<i class="fa-solid fa-list-check lb-pref-pill__icon" aria-hidden="true"></i>'
      . '<span>'.htmlspecialchars($badgeLabel, ENT_QUOTES, 'UTF-8').'</span>'
      . '<span class="lb-pref-count">'.(int)$count.'</span>'
      . '<span class="lb-pref-tooltip" role="tooltip"><span class="lb-pref-tooltip__title">'.htmlspecialchars($tooltipLabel, ENT_QUOTES, 'UTF-8').'</span><span class="lb-pref-tooltip__icons">'.implode('', $imgs).'</span></span>'
      . '</span>';
  }
}

if (!function_exists('op_lb_collect_pref_rows')) {
  function op_lb_collect_pref_rows(array $row): array {
    $prefs=[]; $pool=function_exists('op_lb_build_option_pool')?op_lb_build_option_pool($row):[];
    $map=['agents'=>['fallback'=>'Agents'],'champions'=>['fallback'=>'Champions'],'roles'=>['fallback'=>'Roles']];
    foreach (['agents','champions','roles'] as $k) {
      $val=function_exists('op_lb_pick_pref_value')?op_lb_pick_pref_value($row,$pool,$k):($row[$k]??null);
      if (!op_lb_value_selected($val)) continue;
      if (function_exists('util_format_option')) { $ds=util_format_option($k,$val); $label=trim(strip_tags((string)($ds[0]??($map[$k]['fallback']??$k)))); $html=(string)($ds[1]??''); }
      else { $label=$map[$k]['fallback']??$k; $html=htmlspecialchars(is_scalar($val)?(string)$val:json_encode($val),ENT_QUOTES,'UTF-8'); }
      $t=trim(strip_tags($html)); $hv=stripos($html,'<img')!==false||stripos($html,'<svg')!==false;
      if ($t===''&&!$hv) continue;
      $prefs[]=['key'=>$k,'label'=>$label,'html'=>$html];
    }
    return $prefs;
  }
}

// Games actually present in THIS booster's own orders — the Game filter should only ever
// offer choices that can return results, not the entire game catalog.
$_boosterOwnGameSlugs = [];
foreach ($data as $_bgRow) {
  $_bgFid = (int)($_bgRow['form_id'] ?? 0);
  $_bgRaw = strtolower(trim((string)($_bgRow['game'] ?? '')));
  $_bgIsClassic = $_bgRaw === 'lol_classic' || $_bgRaw === 'lol-classic' || str_contains($_bgRaw, 'classic');
  $_bgIsTft = in_array($_bgFid, [21, 22, 23, 24, 25], true) || str_contains($_bgRaw, 'tft') || str_contains($_bgRaw, 'teamfight');
  $_bgIsVal = $_bgRaw === 'val' || str_contains($_bgRaw, 'valorant') || in_array($_bgFid, [5, 6, 7, 8, 16], true);
  if      ($_bgIsClassic) $_bgSlug = 'lol_classic';
  elseif  ($_bgIsTft) $_bgSlug = 'tft';
  elseif  ($_bgIsVal) $_bgSlug = 'val';
  elseif  ($_bgRaw === 'lol' || str_contains($_bgRaw, 'league')) $_bgSlug = 'lol';
  else    $_bgSlug = $_bgRaw;
  if ($_bgSlug !== '') $_boosterOwnGameSlugs[$_bgSlug] = true;
}
$_bgLabels = ['lol' => 'LoL', 'lol_classic' => 'LoL Classic', 'val' => 'Valorant', 'tft' => 'TFT'];
$_bgIcons = [
  'lol' => '/public/assets/website/images/icons/league-of-legends.png',
  'lol_classic' => '/public/assets/website/images/icons/lol-classic.png',
  'val' => '/public/assets/website/images/icons/valorant.png',
  'tft' => '/public/assets/website/images/icons/teamfight-tactics.png',
];
$_boosterGameFilterOptions = [];
foreach (array_keys($_boosterOwnGameSlugs) as $_bgs) {
  $_boosterGameFilterOptions[$_bgs] = [
    'label' => $_bgLabels[$_bgs] ?? (function_exists('util_game_display_name') ? util_game_display_name($_bgs) : strtoupper($_bgs)),
    'icon'  => $_bgIcons[$_bgs] ?? (function_exists('util_game_icon_url') ? util_game_icon_url($_bgs) : ''),
  ];
}
uasort($_boosterGameFilterOptions, fn($a, $b) => strcasecmp($a['label'], $b['label']));
?>

<?= $this->start('styles') ?>
<link rel="stylesheet" href="<?= ASSET_URL ?>/origin/dash/vendor/tom-select/dist/css/tom-select.bootstrap5.css">
<style>
/* ─────────────────────────────────────────────────────────
   BOOSTER ORDERS LIST
───────────────────────────────────────────────────────── */
:root{
  --lb-ctrl-bg:     rgba(255,255,255,.06);
  --lb-ctrl-bd:     rgba(255,255,255,.10);
  --lb-ctrl-inset:  0 10px 26px rgba(0,0,0,.35) inset;
  --lb-text:        rgba(255,255,255,.88);
  --lb-muted:       rgba(255,255,255,.52);
  --lb-subtle:      rgba(255,255,255,.28);
}

/* ── Wide layout ── */
@media(min-width:992px){
  .content.container,.content-container.container,.container.content-container{max-width:100% !important;padding-left:1.25rem !important;padding-right:1.25rem !important;}
}

/* ── Search input ── */
.orders-search{display:flex;align-items:center;gap:.35rem;border:1px solid var(--lb-ctrl-bd);border-radius:999px !important;background:var(--lb-ctrl-bg);box-shadow:var(--lb-ctrl-inset);padding:.18rem .55rem;min-height:2.30rem;overflow:hidden;}
.orders-search .input-group-text{border:0!important;background:transparent!important;color:var(--lb-muted);padding:0 .35rem 0 .7rem;}
.orders-search .form-control{border:0!important;background:transparent!important;color:var(--lb-text);padding:0;min-width:11rem;border-radius:999px!important;}
.orders-search .form-control::placeholder{color:var(--lb-subtle);}
.orders-search:focus-within{border-color:rgba(var(--bs-primary-rgb),.55);box-shadow:var(--lb-ctrl-inset),0 0 0 .18rem rgba(var(--bs-primary-rgb),.14);}

/* ── Compact dropdown filter (Game / Status) — replaces the old pill wall ── */
.lb-dropfilter{position:relative;min-width:170px;}
.lb-dropfilter-toggle{width:100%;display:flex;align-items:center;justify-content:space-between;gap:.6rem;height:2.30rem;padding:0 .8rem;border-radius:999px;border:1px solid var(--lb-ctrl-bd);background:var(--lb-ctrl-bg);box-shadow:var(--lb-ctrl-inset);color:var(--lb-text);font-size:.78rem;font-weight:700;cursor:pointer;transition:.15s ease;}
.lb-dropfilter-toggle:hover,.lb-dropfilter.open .lb-dropfilter-toggle{border-color:rgba(var(--bs-primary-rgb),.42);color:#fff;}
.lb-dropfilter-toggle i.fa-chevron-down{font-size:.68rem;color:var(--lb-muted);transition:transform .15s ease;}
.lb-dropfilter.open .lb-dropfilter-toggle i.fa-chevron-down{transform:rotate(180deg);}
.lb-dropfilter-choice{display:flex;align-items:center;gap:.5rem;min-width:0;}
.lb-dropfilter-choice img{width:16px;height:16px;object-fit:contain;border-radius:3px;flex:0 0 auto;}
.lb-dropfilter-choice .lb-dropfilter-dot{width:8px;height:8px;border-radius:50%;flex:0 0 auto;background:var(--dot,rgba(255,255,255,.4));}
.lb-dropfilter-choice span{overflow:hidden;text-overflow:ellipsis;white-space:nowrap;}
.lb-dropfilter-menu{position:absolute;top:calc(100% + 8px);left:0;min-width:230px;z-index:60;display:none;border-radius:14px;border:1px solid rgba(255,255,255,.10);background:#15181b;box-shadow:0 20px 50px rgba(0,0,0,.48);}
.lb-dropfilter.open .lb-dropfilter-menu{display:block;}
.lb-dropfilter-search{display:flex;align-items:center;gap:.5rem;padding:.55rem .7rem;border-bottom:1px solid rgba(255,255,255,.08);color:var(--lb-muted);}
.lb-dropfilter-search input{flex:1;min-width:0;border:0;background:transparent;color:var(--lb-text);font-size:.8rem;outline:none;}
.lb-dropfilter-options{max-height:280px;overflow:auto;padding:.4rem;}
.lb-dropfilter-option{width:100%;display:flex;align-items:center;gap:.55rem;padding:.5rem .6rem;border:0;border-radius:9px;background:transparent;color:var(--lb-muted);font-size:.78rem;font-weight:700;text-align:left;cursor:pointer;transition:.15s ease;}
.lb-dropfilter-option:hover{background:rgba(255,255,255,.06);color:var(--lb-text);}
.lb-dropfilter-option.is-active{background:rgba(var(--bs-primary-rgb),.16);color:#fff;}
.lb-dropfilter-option img{width:16px;height:16px;object-fit:contain;border-radius:3px;flex:0 0 auto;}
.lb-dropfilter-option .lb-dropfilter-dot{width:8px;height:8px;border-radius:50%;flex:0 0 auto;}

/* Vertical separator between filter groups */
.lb-filter-sep{width:1px;height:1.3rem;background:rgba(255,255,255,.11);flex:0 0 auto;}

/* ── Table base ── */
#orders_table thead th{font-size:.72rem;letter-spacing:.06em;text-transform:uppercase;color:rgba(255,255,255,.65);}
#orders_table tbody td{padding-top:.80rem;padding-bottom:.80rem;vertical-align:middle;}
#orders_table tbody tr{transition:background .14s;}
#orders_table tbody tr:hover{background:rgba(255,255,255,.025);}
#orders_table td{overflow:visible;}
#orders_table_wrapper .table-responsive{overflow:visible!important;}
.lb-nowrap{white-space:nowrap;}
.lb-titlecell{white-space:normal;min-width:320px;width:320px;}
#orders_table td[data-label="Client"]{min-width:140px;max-width:160px;width:150px;}
#orders_table td[data-label="Order ID"]{min-width:100px;width:100px;}

/* ── Boost icon + game badge ── */
.lb-bf-ico-host{position:relative;overflow:visible!important;}
.lb-bf-ico-host .avatar-initials{display:flex;align-items:center;justify-content:center;}
.lb-game-badge{position:absolute;right:-6px;bottom:-6px;width:19px;height:19px;border-radius:50%;background:rgba(14,14,18,.9);border:1px solid rgba(255,255,255,.13);padding:2px;}

/* ── Order ID ── */
.lb-orderid{display:inline-flex;align-items:center;gap:.45rem;}
.lb-orderid-link{color:var(--lb-text);text-decoration:none;font-weight:800;}
.lb-orderid-link:hover{color:#fff;text-decoration:underline;}
.lb-copybtn{border:1px solid rgba(255,255,255,.11);background:rgba(255,255,255,.06);color:var(--lb-muted);width:26px;height:26px;border-radius:8px;display:inline-flex;align-items:center;justify-content:center;padding:0;font-size:.72rem;}
.lb-copybtn:hover{color:var(--lb-text);border-color:rgba(var(--bs-primary-rgb),.45);}
.lb-copybtn:active{transform:translateY(1px);}

/* ── Client presence ── */
.lb-client-cell{display:flex;align-items:center;gap:.5rem;flex-wrap:wrap;}
.lb-client-presence{display:inline-flex;align-items:center;gap:.32rem;border-radius:999px;padding:.16rem .48rem;font-size:.70rem;font-weight:800;line-height:1;border:1px solid rgba(255,255,255,.09);background:rgba(255,255,255,.04);white-space:nowrap;}
.lb-client-presence__dot{width:.42rem;height:.42rem;border-radius:50%;display:inline-block;}
.lb-client-presence.is-online{color:#45f0b1;border-color:rgba(45,212,138,.30);background:rgba(45,212,138,.09);}
.lb-client-presence.is-online .lb-client-presence__dot{background:#27e6a1;box-shadow:0 0 6px rgba(39,230,161,.45);}
.lb-client-presence.is-offline{color:var(--lb-muted);border-color:rgba(255,255,255,.09);}
.lb-client-presence.is-offline .lb-client-presence__dot{background:rgba(255,255,255,.32);}

/* ══════════════════════════════════════════════════════
   OPTIONS CELL, compact summary with details popover
══════════════════════════════════════════════════════ */
.lb-opts-cell{
  min-width:215px;
  max-width:260px;
  width:235px;
}
#orders_table td[data-label="Options"]{
  min-width:215px;
}

.lb-opts-summary{
  display:flex;
  align-items:center;
  gap:.45rem;
  position:relative;
  width:max-content;
  max-width:100%;
}

.lb-queue-pill{
  display:inline-flex;
  align-items:center;
  gap:.38rem;
  min-height:28px;
  padding:.34rem .62rem;
  border-radius:9px;
  font-size:.67rem;
  font-weight:900;
  letter-spacing:.04em;
  text-transform:uppercase;
  border:1px solid transparent;
  white-space:nowrap;
}
.lb-queue-pill__icon{
  width:19px;
  height:19px;
  display:inline-flex;
  align-items:center;
  justify-content:center;
  border-radius:6px;
  background:rgba(255,255,255,.07);
}
.lb-queue-pill.is-solo,
.lb-queue-pill.is-duo{
  color:rgba(255,255,255,.90);
  background:rgba(255,255,255,.055);
  border-color:rgba(255,255,255,.14);
}
.lb-queue-pill.is-solo .lb-queue-pill__icon{
  background:rgba(255,255,255,.08);
}
.lb-queue-pill.is-duo .lb-queue-pill__icon{
  background:rgba(255,255,255,.11);
}

.lb-options-trigger{
  display:inline-flex;
  align-items:center;
  gap:.34rem;
  min-height:28px;
  padding:.34rem .54rem;
  border-radius:9px;
  color:rgba(255,255,255,.66);
  background:rgba(255,255,255,.035);
  border:1px solid rgba(255,255,255,.09);
  font-size:.64rem;
  font-weight:850;
  white-space:nowrap;
  cursor:pointer;
  transition:.15s ease;
}
.lb-options-trigger:hover,
.lb-options-trigger:focus{
  color:#fff;
  background:rgba(255,255,255,.065);
  border-color:rgba(124,92,255,.30);
  outline:none;
}
.lb-options-trigger i{
  font-size:.62rem;
  color:#a78bfa;
}

.lb-opts-summary::after{
  content:"";
  position:absolute;
  left:0;
  top:100%;
  width:100%;
  height:12px;
  background:transparent;
}

.lb-options-popover{
  position:absolute;
  left:0;
  top:calc(100% + 6px);
  z-index:150;
  width:300px;
  max-width:min(360px,calc(100vw - 36px));
  display:none;
  padding:.72rem;
  border-radius:12px;
  background:#15181b;
  border:1px solid rgba(255,255,255,.12);
  box-shadow:0 20px 50px rgba(0,0,0,.48);
  overflow:visible;
}
.lb-options-popover::before{
  content:"";
  position:absolute;
  left:26px;
  bottom:100%;
  border:7px solid transparent;
  border-bottom-color:rgba(255,255,255,.12);
}
.lb-options-popover::after{
  content:"";
  position:absolute;
  left:27px;
  bottom:100%;
  transform:translateY(1px);
  border:6px solid transparent;
  border-bottom-color:#15181b;
}
.lb-opts-summary:hover .lb-options-popover,
.lb-opts-summary:focus-within .lb-options-popover,
.lb-opts-summary.is-open .lb-options-popover,
.lb-opts-summary.is-hover-open .lb-options-popover{
  display:block;
}

.lb-options-popover__title{
  display:flex;
  align-items:center;
  justify-content:space-between;
  gap:.5rem;
  margin-bottom:.58rem;
  color:#fff;
  font-size:.70rem;
  font-weight:900;
  letter-spacing:.055em;
  text-transform:uppercase;
}
.lb-options-popover__count{
  display:inline-flex;
  align-items:center;
  justify-content:center;
  min-width:20px;
  height:18px;
  padding:0 .35rem;
  border-radius:6px;
  background:rgba(124,92,255,.14);
  border:1px solid rgba(124,92,255,.22);
  color:#c4b5fd;
  font-size:.58rem;
}

.lb-options-popover__grid{
  display:flex;
  flex-wrap:wrap;
  gap:.34rem;
}

.lb-tag{
  --tag-color:rgba(255,255,255,.72);
  --tag-border:rgba(255,255,255,.11);
  --tag-bg:rgba(255,255,255,.045);
  display:inline-flex;
  align-items:center;
  gap:.28rem;
  min-height:24px;
  padding:.28rem .50rem;
  border-radius:7px;
  font-size:.61rem;
  font-weight:800;
  line-height:1;
  white-space:nowrap;
  color:var(--tag-color);
  border:1px solid var(--tag-border);
  background:var(--tag-bg);
}
.lb-tag__icon{
  color:currentColor;
  opacity:.84;
  flex:0 0 auto;
}
.lb-tag i.lb-tag__icon{
  font-size:.64rem;
}
.lb-tag__icon--svg{
  width:11px;
  height:11px;
  display:inline-block;
  background-color:currentColor;
  -webkit-mask-image:var(--icon);
  mask-image:var(--icon);
  -webkit-mask-repeat:no-repeat;
  mask-repeat:no-repeat;
  -webkit-mask-position:center;
  mask-position:center;
  -webkit-mask-size:contain;
  mask-size:contain;
}
.lb-tag__icon--image{
  width:12px;
  height:12px;
  object-fit:contain;
  flex:0 0 12px;
  opacity:1;
}
.lb-tag.t-priority .lb-tag__icon--image{
  filter:brightness(0) saturate(100%) invert(82%) sepia(45%) saturate(968%) hue-rotate(341deg) brightness(103%) contrast(94%);
}
.lb-tag.t-stream .lb-tag__icon--image{
  filter:brightness(0) saturate(100%) invert(64%) sepia(67%) saturate(2310%) hue-rotate(189deg) brightness(104%) contrast(101%);
}
.lb-tag.t-soloonly .lb-tag__icon--image{
  filter:brightness(0) saturate(100%) invert(78%) sepia(31%) saturate(1246%) hue-rotate(116deg) brightness(100%) contrast(89%);
}
.lb-tag.t-bonus .lb-tag__icon--image{
  filter:brightness(0) saturate(100%) invert(74%) sepia(25%) saturate(1292%) hue-rotate(211deg) brightness(103%) contrast(101%);
}
.lb-tag.t-priority{--tag-color:#f8bd57;--tag-border:rgba(248,189,87,.22);--tag-bg:rgba(248,189,87,.07);}
.lb-tag.t-bonus{--tag-color:#bd9cff;--tag-border:rgba(189,156,255,.22);--tag-bg:rgba(189,156,255,.07);}
.lb-tag.t-stream{--tag-color:#68adff;--tag-border:rgba(104,173,255,.22);--tag-bg:rgba(104,173,255,.07);}
.lb-tag.t-soloonly{--tag-color:#42ddc7;--tag-border:rgba(66,221,199,.22);--tag-bg:rgba(66,221,199,.07);}
.lb-tag.t-stealth{--tag-color:rgba(255,255,255,.64);--tag-border:rgba(255,255,255,.10);--tag-bg:rgba(255,255,255,.035);}
.lb-tag.t-kda{--tag-color:#ffad7a;--tag-border:rgba(255,173,122,.22);--tag-bg:rgba(255,173,122,.07);}
.lb-tag.t-vpn{--tag-color:rgba(255,255,255,.62);--tag-border:rgba(255,255,255,.10);--tag-bg:rgba(255,255,255,.035);}

.lb-pref-pill{
  display:inline-flex;
  align-items:center;
  gap:.28rem;
  min-height:24px;
  padding:.27rem .48rem;
  border-radius:7px;
  font-size:.61rem;
  font-weight:800;
  color:rgba(255,255,255,.70);
  background:rgba(255,255,255,.035);
  border:1px solid rgba(255,255,255,.085);
  white-space:nowrap;
}
.lb-pref-pill__icon{
  font-size:.58rem;
  color:rgba(255,255,255,.44);
}
.lb-pref-count{
  display:inline-flex;
  align-items:center;
  justify-content:center;
  min-width:18px;
  height:16px;
  padding:0 .30rem;
  border-radius:5px;
  font-size:.56rem;
  font-weight:950;
  color:rgba(255,255,255,.72);
  background:rgba(255,255,255,.07);
  border:1px solid rgba(255,255,255,.08);
}
.lb-pref-tooltip{
  position:absolute;
  left:0;
  bottom:calc(100% + 8px);
  z-index:260;
  width:max-content;
  max-width:min(360px,calc(100vw - 48px));
  display:none;
  flex-direction:column;
  gap:.45rem;
  padding:.65rem;
  border-radius:10px;
  background:#101316;
  border:1px solid rgba(255,255,255,.12);
  box-shadow:0 18px 42px rgba(0,0,0,.48);
  color:rgba(255,255,255,.92);
}
.lb-pref-tooltip::after{
  content:"";
  position:absolute;
  left:16px;
  top:100%;
  border:6px solid transparent;
  border-top-color:#101316;
}
.lb-pref-tooltip__title{
  font-size:.64rem;
  font-weight:900;
  letter-spacing:.05em;
  text-transform:uppercase;
  color:rgba(255,255,255,.56);
}
.lb-pref-tooltip__icons{
  display:flex;
  flex-wrap:wrap;
  gap:.32rem;
  max-width:320px;
}
.lb-pref-tooltip__icons img{
  width:28px;
  height:28px;
  border-radius:7px;
  object-fit:cover;
  border:1px solid rgba(255,255,255,.10);
}
.lb-pref-tooltip__text{
  font-size:.72rem;
  line-height:1.35;
  color:rgba(255,255,255,.84);
  white-space:normal;
  max-width:290px;
}
.lb-pref-group{
  position:relative;
}
.lb-pref-group:hover .lb-pref-tooltip,
.lb-pref-group:focus-within .lb-pref-tooltip{
  display:flex;
}

.lb-no-opts{
  display:inline-flex;
  align-items:center;
  min-height:25px;
  padding:.28rem .52rem;
  border-radius:7px;
  font-size:.66rem;
  color:rgba(255,255,255,.36);
  background:rgba(255,255,255,.025);
  border:1px dashed rgba(255,255,255,.08);
}

/* Softer table rows */
#orders_table tbody tr{
  border-bottom:1px solid rgba(255,255,255,.045);
}
#orders_table tbody tr:hover{
  background:linear-gradient(90deg,rgba(124,92,255,.045),rgba(255,255,255,.018));
}
#orders_table tbody td{
  padding-top:1rem;
  padding-bottom:1rem;
}
#orders_table td[data-label="Earning"]{
  font-weight:850;
  color:#f5f7fb;
}
#orders_table td[data-label="Action"] .btn{
  border-radius:9px;
  min-height:34px;
  padding:.42rem .72rem;
  border-color:rgba(255,255,255,.10);
  background:rgba(255,255,255,.035);
}
#orders_table td[data-label="Action"] .btn:hover{
  color:#fff;
  border-color:rgba(var(--bs-primary-rgb),.36);
  background:rgba(var(--bs-primary-rgb),.10);
}

/* ── Status badge ── */
.lb-status{display:inline-flex;align-items:center;gap:.42rem;padding:.30rem .65rem;border-radius:999px;font-weight:950;font-size:.70rem;letter-spacing:.08em;text-transform:uppercase;border:1px solid rgba(255,255,255,.09);background:rgba(255,255,255,.04);color:var(--lb-text);white-space:nowrap;}
.lb-status__dot{width:6px;height:6px;border-radius:50%;background:currentColor;opacity:.95;flex:0 0 auto;}
.lb-status.status-inprogress{color:#4ea1ff;border-color:rgba(78,161,255,.24);background:rgba(78,161,255,.11);}
.lb-status.status-completed {color:#1fe6c6;border-color:rgba(31,230,198,.20);background:rgba(31,230,198,.09);}
.lb-status.status-paused    {color:#ffc44d;border-color:rgba(255,196,77,.22);background:rgba(255,196,77,.09);}
.lb-status.status-refunded  {color:#ff8a3d;border-color:rgba(255,138,61,.26);background:rgba(255,138,61,.11);}

/* ── Mobile: compact table, not cards ── */
@media(max-width:767.98px){
  .card-header .row{gap:.35rem;}
  .orders-search{width:100%;}
  .orders-search .form-control{min-width:0;}
  .lb-dropfilter{width:100%;}
  .lb-filter-sep{display:none;}

  .table-responsive{
    overflow-x:auto!important;
    overflow-y:visible!important;
    -webkit-overflow-scrolling:touch;
    border-top:1px solid rgba(255,255,255,.07);
  }

  #orders_table{
    display:table!important;
    width:max-content!important;
    min-width:860px;
    table-layout:auto;
    margin-bottom:0;
  }
  #orders_table thead{display:table-header-group!important;}
  #orders_table tbody{display:table-row-group!important;}
  #orders_table tr{display:table-row!important;}
  #orders_table th,#orders_table td{display:table-cell!important;width:auto!important;}

  #orders_table thead th{
    position:sticky;
    top:0;
    z-index:4;
    background:#25292a;
    padding:.62rem .55rem;
    font-size:.62rem;
    letter-spacing:.045em;
    white-space:nowrap;
  }
  #orders_table tbody td{
    padding:.58rem .55rem!important;
    border-top:1px solid rgba(255,255,255,.055)!important;
    white-space:nowrap;
  }
  #orders_table tbody tr{
    border:0!important;
    background:transparent!important;
    border-radius:0!important;
    padding:0!important;
    margin:0!important;
  }
  #orders_table tbody tr:nth-child(even){background:rgba(255,255,255,.018)!important;}

  /* First column should scroll horizontally with the rest of the table on mobile. */
  #orders_table th:first-child,
  #orders_table td:first-child{
    position:static!important;
    left:auto!important;
    z-index:auto;
    box-shadow:none;
  }

  .lb-titlecell{min-width:230px!important;width:230px!important;max-width:230px;}
  .lb-titlecell .avatar{width:2.25rem;height:2.25rem;min-width:2.25rem;}
  .lb-titlecell .ms-3{margin-left:.65rem!important;min-width:0;}
  .lb-titlecell .h4{font-size:.88rem;line-height:1.15;max-width:170px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;}
  .lb-titlecell small{display:block;max-width:170px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;}
  .lb-game-badge{width:17px;height:17px;right:-5px;bottom:-5px;}

  #orders_table td[data-label="Order ID"]{min-width:94px!important;}
  #orders_table td[data-label="Client"]{min-width:170px!important;max-width:190px!important;}
  .lb-client-cell{gap:.35rem;flex-wrap:nowrap;}
  .lb-client-presence{font-size:.62rem;padding:.14rem .38rem;}

  .lb-opts-cell,
  #orders_table td[data-label="Options"]{min-width:210px!important;max-width:260px!important;}
  .lb-opts-inner{display:flex;flex-wrap:wrap;gap:.22rem;max-width:280px;}
  .lb-tag{font-size:.58rem;padding:.15rem .35rem;gap:.18rem;}
  .lb-pref-pill{height:22px;font-size:.58rem;padding:0 .35rem;}
  .lb-pref-img{width:18px;height:18px;border-radius:5px;}

  .lb-status{font-size:.60rem;padding:.24rem .48rem;letter-spacing:.055em;}
  #orders_table td[data-label="Earning"],
  #orders_table td[data-label="Status"],
  #orders_table td[data-label="Created"],
  #orders_table td[data-label="Action"]{min-width:100px!important;}
  #orders_table td[data-label="Action"] a.btn{white-space:nowrap;}
}
</style>
<?= $this->end() ?>


<!-- Card -->
<div class="card">

  <!-- ── Header ── -->
  <div class="card-header">
    <div class="row justify-content-between align-items-center flex-grow-1 gy-2">

      <div class="col-12 col-md-auto">
        <h5 class="card-header-title mb-0">Orders List</h5>
      </div>

      <!-- Search -->
      <div class="col-12 col-sm-auto">
        <form>
          <div class="input-group input-group-merge orders-search">
            <div class="input-group-prepend input-group-text"><i class="fa-duotone fa-search"></i></div>
            <input id="datatableWithSearchInput" type="search" class="form-control" placeholder="Search orders" aria-label="Search orders">
          </div>
        </form>
      </div>

      <!-- Filters -->
      <div class="col-12 col-sm-auto">
        <div class="d-flex align-items-center flex-wrap gap-2">

          <!-- Game filter — drives the hidden col 8. Only games this booster actually boosts. -->
          <div class="lb-dropfilter" id="boosterOrdersGameFilter" data-default-icon="fa-duotone fa-gamepad-modern">
            <button type="button" class="lb-dropfilter-toggle" aria-haspopup="true" aria-expanded="false">
              <span class="lb-dropfilter-choice" id="boosterOrdersGameFilterLabel"><i class="fa-duotone fa-gamepad-modern"></i><span>All Games</span></span>
              <i class="fa-duotone fa-chevron-down"></i>
            </button>
            <div class="lb-dropfilter-menu" role="menu">
              <div class="lb-dropfilter-options">
                <button type="button" class="lb-dropfilter-option is-active" data-value="" data-label="All Games" role="menuitem">All Games</button>
                <?php foreach ($_boosterGameFilterOptions as $_bgs => $_bgOpt): ?>
                <button type="button" class="lb-dropfilter-option" data-value="<?= htmlspecialchars($_bgs, ENT_QUOTES, 'UTF-8') ?>" data-label="<?= htmlspecialchars($_bgOpt['label'], ENT_QUOTES, 'UTF-8') ?>" data-icon="<?= htmlspecialchars($_bgOpt['icon'], ENT_QUOTES, 'UTF-8') ?>" role="menuitem">
                  <?php if ($_bgOpt['icon'] !== ''): ?><img src="<?= htmlspecialchars($_bgOpt['icon'], ENT_QUOTES, 'UTF-8') ?>" alt=""><?php endif; ?>
                  <?= htmlspecialchars($_bgOpt['label'], ENT_QUOTES, 'UTF-8') ?>
                </button>
                <?php endforeach; ?>
              </div>
            </div>
          </div>

          <!-- Status filter — drives col 5 -->
          <div class="lb-dropfilter" id="boosterOrdersStatusFilter" data-default-icon="fa-duotone fa-list-check">
            <button type="button" class="lb-dropfilter-toggle" aria-haspopup="true" aria-expanded="false">
              <span class="lb-dropfilter-choice" id="boosterOrdersStatusFilterLabel"><i class="fa-duotone fa-list-check"></i><span>Any Status</span></span>
              <i class="fa-duotone fa-chevron-down"></i>
            </button>
            <div class="lb-dropfilter-menu" role="menu">
              <div class="lb-dropfilter-options">
                <button type="button" class="lb-dropfilter-option is-active" data-value="" data-label="Any Status" role="menuitem">Any Status</button>
                <button type="button" class="lb-dropfilter-option" data-value="In Progress" data-label="In Progress" data-dot="#4ea1ff" role="menuitem"><span class="lb-dropfilter-dot" style="background:#4ea1ff"></span>In Progress</button>
                <button type="button" class="lb-dropfilter-option" data-value="Paused" data-label="Paused" data-dot="#ffc44d" role="menuitem"><span class="lb-dropfilter-dot" style="background:#ffc44d"></span>Paused</button>
                <button type="button" class="lb-dropfilter-option" data-value="Refunded" data-label="Refunded" data-dot="#ff8a3d" role="menuitem"><span class="lb-dropfilter-dot" style="background:#ff8a3d"></span>Refunded</button>
                <button type="button" class="lb-dropfilter-option" data-value="Completed" data-label="Completed" data-dot="#1fe6c6" role="menuitem"><span class="lb-dropfilter-dot" style="background:#1fe6c6"></span>Completed</button>
              </div>
            </div>
          </div>

        </div>
      </div>

    </div>
  </div>
  <!-- End Header -->


  <!-- ── Table ── -->
  <div class="table-responsive datatable-custom">
    <table
      class="js-datatable table table-borderless table-thead-bordered table-align-middle card-table"
      data-hs-datatables-options='{
        "columnDefs": [
          {"targets": [3],  "orderable": false},
          {"targets": [7],  "orderable": false},
          {"targets": [8],  "visible": false, "searchable": true}
        ],
        "order":   [[6, "desc"]],
        "info":    {"totalQty": "#datatableEntriesInfoTotalQty"},
        "entries": "#datatableEntries",
        "search":  "#datatableWithSearchInput",
        "isResponsive": false,
        "isShowPaging":  false,
        "pagination":    "datatableWithSearchPagination"
      }' id="orders_table">

      <thead class="thead-light">
        <tr>
          <th>Title</th>       <!-- 0 -->
          <th>Order ID</th>    <!-- 1 -->
          <th>Client</th>      <!-- 2 -->
          <th>Options</th>     <!-- 3 -->
          <th>Earning</th>     <!-- 4 -->
          <th>Status</th>      <!-- 5 -->
          <th>Created At</th>  <!-- 6 -->
          <th class="text-end">Action</th> <!-- 7 -->
          <th>_game</th>       <!-- 8 hidden, used for game filter -->
        </tr>
      </thead>

      <tbody>
      <?php foreach ($data as $row):
        $orderIdLink = (int)($row['order_id'] ?? $row['id'] ?? 0);
        $fid         = (int)($row['form_id'] ?? 0);
        $isCoaching  = in_array($fid,[15,16],true);
        $isDuo       = ($fid === 19) || ((int)($row['is_duo']??0)===1);
        if ($fid === 19) { $row['is_duo'] = 1; }
        $lbClientId  = (int)($row['client_id'] ?? ($row['customer_id'] ?? ($row['client_data']['id'] ?? 0)));

        /* Title */
        $gameRawForLabel = strtolower(trim((string)($row['game'] ?? '')));
        $isClassicLabel = $gameRawForLabel === 'lol_classic'
            || $gameRawForLabel === 'lol-classic'
            || str_contains($gameRawForLabel, 'classic');
        if ($isClassicLabel) {
          $gameShort = 'LoL Classic';
        } elseif ($gameRawForLabel === 'lol') {
          $gameShort = 'LoL';
        } elseif ($gameRawForLabel === 'val' || str_contains($gameRawForLabel, 'valorant')) {
          $gameShort = 'VAL';
        } elseif ($gameRawForLabel === 'tft' || str_contains($gameRawForLabel, 'teamfight')) {
          $gameShort = 'TFT';
        } else {
          $gameShort = function_exists('util_game_display_name') ? util_game_display_name($gameRawForLabel) : strtoupper((string)($row['game'] ?? ''));
        }
        $iconHtml  = function_exists('util_boost_form_icon_html') ? util_boost_form_icon_html($row['icon']??'',1.5,'text-body') : '';
        $titleHtml = function_exists('util_format_boost_overview') ? util_format_boost_overview($row['game'],$row['type'],$row) : htmlspecialchars((string)($row['title']??''),ENT_QUOTES,'UTF-8');
        $subHtml   = htmlspecialchars(trim($gameShort.' '.(string)($row['name']??'')),ENT_QUOTES,'UTF-8');

        /* Game slug (normalised) → written into hidden col 8 */
        $op_game_raw = strtolower(trim((string)($row['game']??'')));
        $op_is_classic = $op_game_raw === 'lol_classic' || $op_game_raw === 'lol-classic' || str_contains($op_game_raw, 'classic');
        $op_is_tft   = in_array($fid,[21,22,23,24,25],true)||str_contains($op_game_raw,'tft')||str_contains($op_game_raw,'teamfight');
        $op_is_val   = $op_game_raw==='val'||str_contains($op_game_raw,'valorant')||in_array($fid,[5,6,7,8,16],true);
        if      ($op_is_classic) $gameSlug = 'lol_classic';
        elseif  ($op_is_tft) $gameSlug = 'tft';
        elseif  ($op_is_val) $gameSlug = 'val';
        elseif  ($op_game_raw==='lol'||str_contains($op_game_raw,'league')) $gameSlug = 'lol';
        else    $gameSlug = $op_game_raw;

        if ($op_is_classic) {
          $classicTier = max(0, min(7, (int)($row['start_tier'] ?? 0)));
          $classicRankName = util_lol_classic_rank_name($classicTier);
          $classicRankIcon = util_lol_classic_rank_img($classicTier);
          $iconHtml = '<img src="' . htmlspecialchars($classicRankIcon, ENT_QUOTES, 'UTF-8') . '" alt="' . htmlspecialchars($classicRankName, ENT_QUOTES, 'UTF-8') . '" style="width:30px;height:30px;object-fit:contain;">';
          $subHtml = htmlspecialchars(trim($gameShort . ' ' . (string)($row['name'] ?? '') . ' · ' . $classicRankName), ENT_QUOTES, 'UTF-8');
        }

        /* Game badge icon */
        $gameBadgeUrl = null; $gameBadgeAlt = '';
        if      ($op_is_classic) { $gameBadgeUrl='/public/assets/website/images/icons/lol-classic.png'; $gameBadgeAlt='LoL Classic'; }
        elseif  ($op_is_tft) { $gameBadgeUrl='/public/assets/website/images/icons/teamfight-tactics.png'; $gameBadgeAlt='TFT'; }
        elseif  ($op_is_val) { $gameBadgeUrl='/public/assets/website/images/icons/valorant.png'; $gameBadgeAlt='Valorant'; }
        elseif  ($gameSlug==='lol') { $gameBadgeUrl='/public/assets/website/images/icons/league-of-legends.png'; $gameBadgeAlt='LoL'; }
        elseif  (function_exists('util_game_icon_url')) {
          $_dynBadge = util_game_icon_url($gameSlug);
          if ($_dynBadge !== '') { $gameBadgeUrl = $_dynBadge; $gameBadgeAlt = function_exists('util_game_display_name') ? util_game_display_name($gameSlug) : $gameSlug; }
        }

        /* Options */
        $activeOpts = function_exists('op_lb_collect_active_options') ? op_lb_collect_active_options($row) : [];
        $prefs      = function_exists('op_lb_collect_pref_rows')      ? op_lb_collect_pref_rows($row)      : [];
        $prefChamp=[]; $prefRoles=[]; $prefAgents=[];
        foreach ($prefs as $p) {
          $lbl = strtolower((string)($p['label']??''));
          if      (str_contains($lbl,'champ')) $prefChamp[]  = $p;
          elseif  (str_contains($lbl,'role'))  $prefRoles[]  = $p;
          elseif  (str_contains($lbl,'agent')) $prefAgents[] = $p;
          else    $prefChamp[] = $p;
        }

        /* Earning */
        $price    = $row['price']??0;
        $currency = function_exists('util_format_currency_display') ? util_format_currency_display($row['currency']) : '€';
        $earning  = null;
        $isRanked5sListRow = function_exists('lb_is_multi_booster_order')
          ? lb_is_multi_booster_order((array)$row)
          : ((int)($row['form_id'] ?? 0) === RANKED_5S_FORM_ID || (string)($row['type'] ?? '') === 'ranked-5s');

        if ($isRanked5sListRow) {
          $boostersCount = max(1, min(4, (int)($row['boosters'] ?? 1)));

          if (strtoupper((string)($row['status'] ?? '')) === 'COMPLETED') {
            $payRows = [];
            try {
              global $db;
              $payRows = $db->run(
                "SELECT amount
                   FROM booster_payments
                  WHERE note = ?
                    AND type = 'order_completion'
                    AND booster_id = ?
                    AND amount > 0
                  ORDER BY id DESC
                  LIMIT 1",
                (string)($row['id'] ?? 0),
                (int)(defined('BOOSTER_ID') ? BOOSTER_ID : 0)
              ) ?: [];
            } catch (Throwable $e) {}

            if (isset($payRows[0]['amount'])) {
              $earning = (int)$payRows[0]['amount'];
            }
          }

          if ($earning === null) {
            $row['booster_cut'] = 50;
            $earning = function_exists('lb_ranked_5s_booster_earning_cents')
              ? lb_ranked_5s_booster_earning_cents((array)$row)
              : (int)floor((((float)$price * 50) / 100) / $boostersCount);
          }
        } elseif ($row['status']==='PAID') {
          $earning = function_exists('calculate_booster_cut') ? calculate_booster_cut($row) : null;
        } elseif (in_array($row['status'],['IN_PROGRESS','COMPLETED','PAUSED'],true)) {
          $earning=(($row['booster_cut']??0)/100)*$price;
        }
      ?>
        <tr>

          <!-- 0 Title -->
          <td class="fw-500 lb-titlecell" data-label="Title">
            <a class="d-flex align-items-center text-decoration-none" href="<?= BASE_URL ?>/booster-area/order/<?= $orderIdLink ?>">
              <div class="avatar avatar-light avatar-rounded lb-bf-ico-host">
                <span class="avatar-initials"><?= $iconHtml ?></span>
                <?php if (!empty($gameBadgeUrl)): ?>
                  <img class="lb-game-badge" src="<?= htmlspecialchars($gameBadgeUrl,ENT_QUOTES,'UTF-8') ?>" alt="<?= htmlspecialchars($gameBadgeAlt,ENT_QUOTES,'UTF-8') ?>" title="<?= htmlspecialchars($gameBadgeAlt,ENT_QUOTES,'UTF-8') ?>" loading="lazy">
                <?php endif; ?>
              </div>
              <div class="ms-3">
                <span class="d-block text-body h4 mb-0 fw-bold"><?= $titleHtml ?></span>
                <small class="text-muted"><?= $subHtml ?></small>
              </div>
            </a>
          </td>

          <!-- 1 Order ID -->
          <td class="fw-500 lb-nowrap" data-label="Order ID">
            <div class="lb-orderid">
              <a class="lb-orderid-link" href="<?= BASE_URL ?>/booster-area/order/<?= $orderIdLink ?>">#<?= $orderIdLink ?></a>
              <button type="button" class="lb-copybtn js-copy-orderid" data-copy="<?= $orderIdLink ?>" title="Copy" aria-label="Copy Order ID">
                <i class="fa-solid fa-copy"></i>
              </button>
            </div>
          </td>

          <!-- 2 Client -->
          <td class="fw-500 lb-nowrap" data-label="Client">
            <div class="lb-client-cell">
              <?= util_format_user($row['client_data']['username']??'Unknown',$row['client_data']['icon']??null) ?>
              <?= lb_client_presence_badge_html($lbClientId) ?>
            </div>
          </td>

          <!-- 3 Options -->
          <td class="fw-500 lb-opts-cell" data-label="Options">
            <?php if ($isCoaching): ?>
              <span class="lb-no-opts">Coaching</span>
            <?php else: ?>
              <?php
                $optionDetailCount = count($activeOpts);
                if (!empty($prefChamp)) $optionDetailCount++;
                if (!empty($prefRoles)) $optionDetailCount++;
                if (!empty($prefAgents)) $optionDetailCount++;
              ?>
              <div class="lb-opts-summary">

                <span class="lb-queue-pill <?= $isDuo ? 'is-duo' : 'is-solo' ?>">
                  <span class="lb-queue-pill__icon">
                    <i class="<?= $isDuo ? 'fa-solid fa-people-group' : 'fa-solid fa-user' ?>"></i>
                  </span>
                  <?= $isDuo ? 'Duo' : 'Solo' ?>
                </span>

                <?php if ($optionDetailCount > 0): ?>
                  <button type="button" class="lb-options-trigger" aria-label="Show order options">
                    <i class="fa-solid fa-sliders"></i>
                    <?= $optionDetailCount ?> <?= $optionDetailCount === 1 ? 'Option' : 'Options' ?>
                  </button>

                  <div class="lb-options-popover">
                    <div class="lb-options-popover__title">
                      <span>Order Options</span>
                      <span class="lb-options-popover__count"><?= $optionDetailCount ?></span>
                    </div>

                    <div class="lb-options-popover__grid">
                      <?php foreach ($activeOpts as $o): ?>
                        <span class="lb-tag <?= htmlspecialchars($o['cls'],ENT_QUOTES,'UTF-8') ?>">
                          <?= $o['ico'] ?>
                          <?= htmlspecialchars($o['label'],ENT_QUOTES,'UTF-8') ?>
                        </span>
                      <?php endforeach; ?>

                      <?= function_exists('op_lb_render_pref_group') ? op_lb_render_pref_group($prefChamp, 8, 'Champions') : '' ?>
                      <?= function_exists('op_lb_render_pref_group') ? op_lb_render_pref_group($prefRoles, 6, 'Lanes') : '' ?>
                      <?= function_exists('op_lb_render_pref_group') ? op_lb_render_pref_group($prefAgents, 8, 'Agents') : '' ?>
                    </div>
                  </div>
                <?php endif; ?>

              </div>
            <?php endif; ?>
          </td>

          <!-- 4 Earning -->
          <td class="fw-500 lb-nowrap" data-label="Earning">
            <?php
            if ($earning!==null) echo $currency.(function_exists('util_format_price_display')?util_format_price_display($earning):number_format((float)$earning,2));
            else echo '-';
            ?>
          </td>

          <!-- 5 Status -->
          <td class="fw-500 lb-nowrap" data-label="Status">
            <?= util_format_boost_status($row['status']) ?>
          </td>

          <!-- 6 Created At -->
          <td class="fw-500 lb-nowrap" data-label="Created" data-order="<?= $row['created_at'] ?>">
            <?= util_format_date_display_panel($row['created_at']) ?>
          </td>

          <!-- 7 Action -->
          <td class="text-end lb-nowrap" data-label="Action">
            <a href="<?= BASE_URL ?>/booster-area/order/<?= $orderIdLink ?>" class="btn btn-white btn-sm d-none d-md-inline-flex">
              <i class="fa-duotone fa-eye me-1 fs-6"></i> View
            </a>
            <a href="<?= BASE_URL ?>/booster-area/order/<?= $orderIdLink ?>" class="btn btn-primary btn-sm d-md-none w-100 justify-content-center">
              <i class="fa-duotone fa-eye me-1 fs-6"></i> Open order
            </a>
          </td>

          <!-- 8 Hidden game slug (for DataTables game filter, not displayed) -->
          <td><?= htmlspecialchars($gameSlug,ENT_QUOTES,'UTF-8') ?></td>

        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
  <!-- End Table -->


  <!-- Footer -->
  <div class="card-footer">
    <div class="row justify-content-center justify-content-sm-between align-items-sm-center">
      <div class="col-sm mb-2 mb-sm-0">
        <div class="d-flex justify-content-center justify-content-sm-start align-items-center">
          <span class="me-2">Showing:</span>
          <div class="tom-select-custom">
            <select id="datatableEntries" class="js-select form-select form-select-borderless w-auto"
              autocomplete="off" data-hs-tom-select-options='{"searchInDropdown":false,"hideSearch":true}'>
              <option value="4">4</option>
              <option value="6">6</option>
              <option value="8">8</option>
              <option value="12" selected>12</option>
            </select>
          </div>
          <span class="text-secondary me-2">of</span>
          <span id="datatableEntriesInfoTotalQty"></span>
        </div>
      </div>
      <div class="col-sm-auto">
        <div class="d-flex justify-content-center justify-content-sm-end">
          <nav id="datatableWithSearchPagination" aria-label="Activity pagination"></nav>
        </div>
      </div>
    </div>
  </div>

</div>
<!-- End Card -->


<?= $this->start('scripts') ?>
<script>
$(document).on('ready', function () {

  /* ── Init DataTables ── */
  HSCore.components.HSDatatables.init($('#orders_table'), {
    language: {
      zeroRecords: `<div class="text-center p-4">
        <img class="mb-3" src="<?= ASSET_URL ?>/origin/dash/svg/illustrations/oc-browse.svg" style="width:10rem;" data-hs-theme-appearance="default">
        <img class="mb-3" src="<?= ASSET_URL ?>/origin/dash/svg/illustrations-light/oc-browse.svg" style="width:10rem;" data-hs-theme-appearance="dark">
        <p class="mb-0">No data to show</p>
      </div>`
    }
  });

  const DT_ID = 'orders_table';
  function dt() { return HSCore.components.HSDatatables.getItem(DT_ID); }

  /* ── Generic dropdown-filter handler ── */
  function initDropFilter(rootId, onSelect) {
    const root = document.getElementById(rootId);
    if (!root) return;
    const toggle = root.querySelector('.lb-dropfilter-toggle');
    const label  = root.querySelector('.lb-dropfilter-choice[id]');

    toggle.addEventListener('click', function (e) {
      e.preventDefault();
      e.stopPropagation();
      document.querySelectorAll('.lb-dropfilter.open').forEach(x => { if (x !== root) x.classList.remove('open'); });
      root.classList.toggle('open');
      toggle.setAttribute('aria-expanded', root.classList.contains('open') ? 'true' : 'false');
    });

    root.querySelectorAll('.lb-dropfilter-option').forEach(function (opt) {
      opt.addEventListener('click', function (e) {
        e.preventDefault();
        e.stopPropagation();
        root.querySelectorAll('.lb-dropfilter-option').forEach(o => o.classList.remove('is-active'));
        opt.classList.add('is-active');

        const val  = opt.dataset.value || '';
        const lbl  = opt.dataset.label || 'All';
        const icon = opt.dataset.icon || '';
        const dot  = opt.dataset.dot || '';
        let html = '';
        if (icon) html = '<img src="' + icon + '" alt="">';
        else if (dot) html = '<span class="lb-dropfilter-dot" style="background:' + dot + '"></span>';
        else html = '<i class="' + (root.dataset.defaultIcon || 'fa-duotone fa-circle') + '"></i>';
        if (label) label.innerHTML = html + '<span>' + lbl + '</span>';

        root.classList.remove('open');
        toggle.setAttribute('aria-expanded', 'false');
        onSelect(val);
      });
    });
  }

  document.addEventListener('click', function () {
    document.querySelectorAll('.lb-dropfilter.open').forEach(x => x.classList.remove('open'));
  });

  /* ── Game filter → hidden column 8 (exact match via regex) ── */
  initDropFilter('boosterOrdersGameFilter', function (val) {
    const d = dt(); if (!d) return;
    // val='' → show all; otherwise exact match on the hidden game slug
    d.column(8).search(val === '' ? '' : '^' + val + '$', /*regex=*/true, /*smart=*/false).draw();
  });

  /* ── Status filter → column 5 ── */
  initDropFilter('boosterOrdersStatusFilter', function (val) {
    const d = dt(); if (!d) return;
    d.column(5).search(val).draw();
  });

  /* ── Status badge rendering ── */
  function normalizeStatus(raw) {
    const u = (raw ?? '').toString().replace(/<[^>]*>/g, '').trim().toUpperCase().replace(/\s+/g, ' ');
    const map = {
      'IN PROGRESS': ['In Progress','status-inprogress'],
      'IN_PROGRESS': ['In Progress','status-inprogress'],
      'INPROGRESS':  ['In Progress','status-inprogress'],
      'PAUSED':      ['Paused',     'status-paused'],
      'REFUND':      ['Refunded',   'status-refunded'],
      'REFUNDED':    ['Refunded',   'status-refunded'],
      'REFUNDEDED':  ['Refunded',   'status-refunded'],
      'COMPLETED':   ['Completed',  'status-completed'],
    };
    if (map[u]) return { label: map[u][0], cls: map[u][1] };
    return { label: u.toLowerCase().replace(/(^|\s)[a-z]/g, m => m.toUpperCase()) || 'Unknown', cls: 'status-inprogress' };
  }

  function applyStatusBadges() {
    const d = dt(); if (!d) return;
    d.rows({ page: 'current' }).every(function() {
      const node = this.node(); if (!node) return;
      const td = node.querySelector('td:nth-child(6)'); if (!td) return; // col 5 = 6th child
      const { label, cls } = normalizeStatus(td.textContent);
      td.innerHTML = '<span class="lb-status ' + cls + '"><span class="lb-status__dot" aria-hidden="true"></span><span>' + label + '</span></span>';
    });
  }

  const dtInst = dt();
  if (dtInst) {
    applyStatusBadges();
    dtInst.on('draw', applyStatusBadges);
  }

  /* ── Keep options popover open while moving into it ── */
  document.querySelectorAll('.lb-opts-summary').forEach(function(summary) {
    let closeTimer = null;

    summary.addEventListener('mouseenter', function() {
      if (closeTimer) {
        clearTimeout(closeTimer);
        closeTimer = null;
      }
      summary.classList.add('is-hover-open');
    });

    summary.addEventListener('mouseleave', function() {
      closeTimer = setTimeout(function() {
        summary.classList.remove('is-hover-open');
      }, 220);
    });
  });

  /* ── Options popover click support ── */
  document.addEventListener('click', function(e) {
    const trigger = e.target.closest('.lb-options-trigger');
    document.querySelectorAll('.lb-opts-summary.is-open').forEach(function(summary) {
      if (!trigger || summary !== trigger.closest('.lb-opts-summary')) {
        summary.classList.remove('is-open');
      }
    });

    if (trigger) {
      e.preventDefault();
      e.stopPropagation();
      trigger.closest('.lb-opts-summary')?.classList.toggle('is-open');
    }
  });

  /* ── Copy Order ID ── */
  document.addEventListener('click', function(e) {
    const btn = e.target.closest('.js-copy-orderid');
    if (!btn) return;
    e.preventDefault(); e.stopPropagation();
    const val = btn.getAttribute('data-copy'); if (!val) return;
    const txt = '#' + val;
    function done(ok) {
      btn.classList.add('is-copied');
      setTimeout(() => btn.classList.remove('is-copied'), 700);
      if (typeof window.lb_toast === 'function')
        window.lb_toast(ok ? 'success' : 'danger', ok ? 'Copied' : 'Copy failed', ok ? (txt + ' copied') : 'Could not copy');
    }
    if (navigator.clipboard?.writeText) { navigator.clipboard.writeText(txt).then(() => done(true)).catch(() => done(false)); return; }
    try { const ta = document.createElement('textarea'); ta.value = txt; ta.style.cssText = 'position:fixed;left:-9999px'; document.body.appendChild(ta); ta.select(); done(!!document.execCommand('copy')); document.body.removeChild(ta); } catch(_) { done(false); }
  });

});
</script>
<?= $this->end() ?>
