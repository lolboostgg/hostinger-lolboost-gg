<?= $this->layout('client/layouts/main', ['meta' => ['title' => 'My Orders | LoLBoost.gg']]) ?>
<?php
$orders = is_array($orders ?? null) ? $orders : [];
// Digital Goods use their own purchase table, so include them in the unified
// client order list instead of making customers visit a separate page.
if (defined('CLIENT_ID') && (int)CLIENT_ID > 0 && function_exists('dg_get_client_purchases')) {
  try {
    $knownDigitalIds = [];
    foreach ($orders as $existingOrder) {
      if (!empty($existingOrder['is_digital_good_order'])) {
        $knownDigitalIds[(int)($existingOrder['digital_good_order_id'] ?? $existingOrder['id'] ?? 0)] = true;
      }
    }
    foreach (dg_get_client_purchases((int)CLIENT_ID, '', 500, 0) as $digitalOrder) {
      $digitalId = (int)($digitalOrder['id'] ?? 0);
      if ($digitalId <= 0 || isset($knownDigitalIds[$digitalId])) continue;
      $digitalOrder['is_digital_good_order'] = true;
      $digitalOrder['digital_good_order_id'] = $digitalId;
      $orders[] = $digitalOrder;
    }
  } catch (Throwable $e) {
    // The other order types must still render if Digital Goods are unavailable.
  }
}
if (!function_exists('lb_co_h')) { function lb_co_h($v){ return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); } }
if (!function_exists('lb_co_title')) {
  function lb_co_title($v){
    return htmlspecialchars(html_entity_decode((string)$v, ENT_QUOTES | ENT_HTML5, 'UTF-8'), ENT_QUOTES, 'UTF-8');
  }
}
if (!function_exists('lb_co_money')) { function lb_co_money($cents,$cur='EUR'){ $sym = strtoupper((string)$cur)==='USD' ? '$' : '€'; return $sym . number_format(((int)$cents)/100, 2, ',', '.'); } }
if (!function_exists('lb_co_amount')) { function lb_co_amount($v,$u=''){ $n = is_numeric($v) ? rtrim(rtrim(number_format((float)$v, 2, '.', ''), '0'), '.') : (string)$v; return trim($n . ' ' . (string)$u); } }
if (!function_exists('lb_co_coin_display')) { function lb_co_coin_display($v){
  $raw = trim((string)$v);
  if ($raw === '' || $raw === '0' || $raw === '0.00') return '—';
  $raw = trim(preg_replace('~\b(lb\s*)?coins?\b|\blb\b~i', '', $raw));
  if ($raw === '' || $raw === '0' || $raw === '0.00') return '—';
  if (is_numeric($raw)) { $raw = number_format((float)$raw, 2, ',', '.'); }
  return '<span class="lb-coin-display"><span>' . lb_co_h($raw) . '</span><i class="fa-solid fa-coins"></i></span>';
} }
if (!function_exists('lb_co_date')) { function lb_co_date($v){ if (!$v) return '—'; if (function_exists('util_format_date_display')) return util_format_date_display($v); $ts=strtotime((string)$v); return $ts ? date('d.m.Y', $ts) : (string)$v; } }
if (!function_exists('lb_co_price_display')) { function lb_co_price_display($cents,$cur='EUR'){ if (function_exists('util_format_currency_display') && function_exists('util_format_price_display')) return util_format_currency_display($cur) . util_format_price_display($cents); return lb_co_money($cents,$cur); } }
 
if (!function_exists('lb_co_account_game_slug')) { function lb_co_account_game_slug($row){
  $game = strtolower(trim((string)($row['game'] ?? $row['game_slug'] ?? $row['account_game_slug'] ?? $row['account_game_name'] ?? $row['game_name'] ?? 'lol')));
  $game = str_replace(['league of legends','league-of-legends','valorant'], ['lol','lol','val'], $game);
  if ($game === 'tft' || $game === 'teamfight tactics') return 'tft';
  if ($game === 'val') return 'val';
  return 'lol';
} }
if (!function_exists('lb_co_rank_icon_html')) { function lb_co_rank_icon_html($row){
  $row = is_array($row) ? $row : [];
  $game = lb_co_account_game_slug($row);
  $rank = 0;
  if ($game === 'val') {
    $rank = (int)($row['rank'] ?? $row['val_rank'] ?? $row['current_rank'] ?? 0);
  } else {
    $rank = (int)($row['current_rank'] ?? $row['rank'] ?? 0);
  }
  if ($rank < 0) $rank = 0;
  if ($game === 'val' && $rank > 9) $rank = 9;
  if ($game !== 'val' && $rank > 10) $rank = 10;
  if (function_exists('util_rank_img')) {
    $src = util_rank_img($game, 'mini', $rank);
  } elseif (defined('ASSET_URL')) {
    $imgGame = $game === 'tft' ? 'lol' : $game;
    $src = ASSET_URL . '/core/main/img/' . $imgGame . '/ranks/mini/' . $rank . '.png';
  } else {
    return '<i class="fa-duotone fa-ranking-star"></i>';
  }
  $label = '';
  if (function_exists('util_get_rank_label')) {
    $label = util_get_rank_label($game, $rank);
  } elseif ($game === 'val' && function_exists('util_get_val_rank')) {
    $label = util_get_val_rank($rank);
  } elseif ($game !== 'val' && function_exists('util_get_lol_rank')) {
    $label = util_get_lol_rank($rank);
  }
  return '<img class="cov2-rank-img" src="' . lb_co_h($src) . '" alt="' . lb_co_h($label ?: 'Rank') . '">';
} }
if (!function_exists('lb_co_status')) { function lb_co_status($statusRaw, $paymentStatus = ''){
  $s = strtoupper(trim((string)$statusRaw)); $p = strtolower(trim((string)$paymentStatus));
  if ($s === '' && $p !== '') $s = strtoupper($p);
  if (in_array($s, ['UNPAID','PENDING_PAYMENT','WAITING_FOR_PAYMENT'], true) || in_array($p, ['unpaid','pending','awaiting_payment'], true)) return ['Unpaid','unpaid','fa-credit-card'];
  if (in_array($s, ['PAID','PAYED'], true)) return ['Paid','paid','fa-circle-check'];
  if (in_array($s, ['PAUSED','PAUSE','ON_HOLD','ONHOLD'], true)) return ['Paused','paused','fa-pause'];
  if (in_array($s, ['PROCESSING','PENDING','QUEUED','WAITING'], true)) return ['Waiting for Booster','processing','fa-spinner'];
  if (in_array($s, ['INPROGRESS','IN_PROGRESS','PROGRESS','ACTIVE'], true)) return ['In Progress','inprogress','fa-loader'];
  if (in_array($s, ['DELIVERED','SHIPPED','FULFILLED'], true)) return ['Delivered','delivered','fa-truck-fast'];
  if (in_array($s, ['COMPLETED','DONE','FINISHED'], true)) return ['Completed','completed','fa-circle-check'];
  if (in_array($s, ['REFUND','REFUNDED','PARTIALLY_REFUNDED'], true)) return [$s === 'PARTIALLY_REFUNDED' ? 'Partially Refunded' : 'Refunded','refunded','fa-rotate-left'];
  if (in_array($s, ['CANCELLED','CANCELED'], true)) return ['Cancelled','cancelled','fa-ban'];
  if (in_array($s, ['FAILED','PAYMENT_FAILED','EXPIRED'], true)) return ['Failed','failed','fa-circle-xmark'];
  if (in_array($s, ['DISPUTED','DISPUTE'], true)) return ['Disputed','disputed','fa-scale-balanced'];
  if (in_array($s, ['CHARGEBACK','CHARGED_BACK'], true)) return ['Chargeback','chargeback','fa-shield-exclamation'];
  $lbl = ucwords(strtolower(str_replace(['_','-'], ' ', $s ?: 'Processing')));
  return [$lbl, strtolower(preg_replace('~[^a-z0-9]+~i', '', $lbl)), 'fa-circle'];
} }

$rows = [];
foreach ($orders as $row) {
  $row = is_array($row) ? $row : [];
  $type = 'boost'; $typeLabel = 'Boost Order'; $typeIcon = 'fa-rocket'; $iconHtml = '<i class="fa-duotone fa-rocket"></i>'; $url = '#'; $payUrl = null;
  $id = (int)($row['order_id'] ?? $row['id'] ?? 0);
  $title = '';
  $sub = '';
  $price = (int)($row['price'] ?? $row['price_eur'] ?? 0);
  $currency = (string)($row['currency'] ?? 'EUR');
  $created = (string)($row['updated_at'] ?? $row['created_at'] ?? '');
  $coins = (string)($row['coins_used'] ?? $row['coins'] ?? $row['lb_coins'] ?? $row['points_used'] ?? '');
  $paymentStatus = (string)($row['payment_status'] ?? '');
  $statusRaw = (string)($row['status'] ?? '');

  if (!empty($row['is_digital_good_order'])) {
    $type = 'digital'; $typeLabel = 'Digital Good'; $typeIcon = 'fa-layer-group';
    $id = (int)($row['digital_good_order_id'] ?? $row['id'] ?? 0);
    $title = (string)($row['item_title'] ?? $row['title'] ?? ('Digital Good #' . $id));
    $brand = trim((string)($row['brand'] ?? ''));
    $category = trim((string)($row['category_name'] ?? 'Digital Goods'));
    $sub = trim(($brand !== '' ? $brand : $category) . ' · x' . max(1, (int)($row['quantity'] ?? 1)));
    $icon = trim((string)($row['brand_icon'] ?? $row['item_brand_icon'] ?? ''));
    if ($icon !== '' && !preg_match('~^https?://~i', $icon)) {
      $icon = ASSET_URL . '/' . ltrim((string)preg_replace('~^/?public/assets/?~', '', $icon), '/');
    }
    $iconHtml = $icon !== '' ? '<img src="'.lb_co_h($icon).'" alt="'.lb_co_h($brand ?: $category).'">' : '<i class="fa-duotone fa-layer-group"></i>';
    $url = '/profile/digital-goods/' . $id;
  } elseif (!empty($row['is_topup_order'])) {
    $type = 'topup'; $typeLabel = 'Top Up Order'; $typeIcon = 'fa-coins';
    $id = (int)($row['topup_order_id'] ?? $row['id'] ?? 0);
    $title = (string)($row['offer_title'] ?? $row['title'] ?? ('Top Up #' . $id));
    $game = (string)($row['game_name'] ?? 'Game');
    $amount = lb_co_amount($row['offer_amount'] ?? '', $row['offer_unit'] ?? '');
    $sub = trim($game . ($amount !== '' ? ' · ' . $amount : ''));
    $icon = trim((string)($row['game_icon'] ?? ''));
    $iconHtml = $icon !== '' ? '<img src="'.lb_co_h($icon).'" alt="'.lb_co_h($game).'">' : '<i class="fa-duotone fa-coins"></i>';
    $url = '/profile/top-up/' . $id;
  } elseif (!empty($row['is_item_order'])) {
    $type = 'item'; $typeLabel = 'Item Order'; $typeIcon = 'fa-gift';
    $id = (int)($row['item_order_id'] ?? $row['id'] ?? 0);
    $title = (string)($row['item_title'] ?? $row['title'] ?? ('Item #' . $id));
    $game = (string)($row['game_name'] ?? $row['item_game_name'] ?? 'Game');
    $sub = $game . ' · x' . max(1, (int)($row['quantity'] ?? 1));
    $icon = trim((string)($row['game_icon'] ?? ''));
    $iconHtml = $icon !== '' ? '<img src="'.lb_co_h($icon).'" alt="'.lb_co_h($game).'">' : '<i class="fa-duotone fa-gift"></i>';
    $url = '/profile/item/' . $id;
  } elseif (!empty($row['is_marketplace_account_order'])) {
    $type = 'account'; $typeLabel = 'Ranked Account'; $typeIcon = 'fa-ranking-star';
    $id = (int)($row['account_order_id'] ?? $row['selling_account_id'] ?? $row['id'] ?? 0);
    $title = (string)($row['account_title'] ?? $row['title'] ?? ('Account #' . $id));
    $game = (string)($row['game_name'] ?? $row['account_game_name'] ?? 'Account');
    $server = trim((string)($row['server'] ?? ''));
    $sub = trim($game . ($server !== '' ? ' · ' . strtoupper($server) : ''));
    $icon = trim((string)($row['game_icon'] ?? ''));
    $iconHtml = lb_co_rank_icon_html($row);
    $url = '/profile/account/' . $id;
  } elseif (!empty($row['is_premium_account_order'])) {
    $type = 'account'; $typeLabel = 'Smurf Account'; $typeIcon = 'fa-helmet-battle';
    $id = (int)($row['premium_account_id'] ?? $row['account_order_id'] ?? $row['id'] ?? 0);
    $title = (string)($row['package_name'] ?? $row['pkg_name'] ?? $row['login'] ?? ('Account #' . $id));
    $game = (string)($row['game_name'] ?? 'League of Legends');
    $server = trim((string)($row['package_server'] ?? $row['server'] ?? ''));
    $sub = trim($game . ($server !== '' ? ' · ' . strtoupper($server) : ''));
    $iconHtml = '<i class="fa fa-helmet-battle"></i>';
    $url = '/premium-account/' . $id;
  } elseif (!empty($row['is_egirl'])) {
    $type = 'companion'; $typeLabel = 'Companion Order'; $typeIcon = 'fa-user-group';
    $id = (int)($row['egirl_order_id'] ?? $row['id'] ?? 0);
    $title = (string)($row['service_title'] ?? 'Companion Session');
    $sub = (string)($row['egirl_username'] ?? 'GG Girl');
    $iconHtml = '<i class="fa-duotone fa-user-group"></i>';
    $url = '/egirl-order/' . $id;
  } else {
    $oid = $row['order_id'] ?? $row['id'] ?? null;
    $id = (int)$oid;
    $game = (string)($row['game'] ?? '');
    $gameNormalized = strtolower(trim($game));
    $isClassicBoost = $gameNormalized === 'lol_classic' || $gameNormalized === 'lol-classic' || str_contains($gameNormalized, 'classic');
    $formName = (string)($row['name'] ?? $row['type'] ?? 'Boost');
    $title = function_exists('util_format_boost_overview') ? util_format_boost_overview($game, (string)($row['type'] ?? ''), $row) : ((string)($row['title'] ?? $formName));
    $gameDisplay = $isClassicBoost ? 'LoL Classic' : (function_exists('util_game_display_name') ? util_game_display_name($game) : strtoupper($game));
    $sub = trim($gameDisplay . ' ' . $formName);
    $iconFile = trim((string)($row['icon'] ?? ''));
    if ($isClassicBoost) {
      $classicTier = max(0, min(7, (int)($row['start_tier'] ?? 0)));
      $classicRankName = function_exists('util_lol_classic_rank_name') ? util_lol_classic_rank_name($classicTier) : ('Rank ' . $classicTier);
      $classicRankIcon = function_exists('util_lol_classic_rank_img') ? util_lol_classic_rank_img($classicTier) : '';
      if ($classicRankIcon !== '') {
        $iconHtml = '<img class="cov2-rank-img" src="' . lb_co_h($classicRankIcon) . '" alt="' . lb_co_h($classicRankName) . '">';
      }
      $sub .= ' · ' . $classicRankName;
    } elseif ($iconFile !== '') {
      $svgUrl = ASSET_URL . '/website/images/boost-forms/boost-type-icons/' . basename($iconFile);
      $iconHtml = '<img src="'.lb_co_h($svgUrl).'" alt="">';
    }
    if (!$isClassicBoost && function_exists('util_game_icon_url')) {
      $gameBadgeUrl = util_game_icon_url($game);
      if ($gameBadgeUrl !== '') {
        $iconHtml .= '<img class="cov2-game-badge" src="'.lb_co_h($gameBadgeUrl).'" alt="">';
      }
    }
    $url = '/order/' . rawurlencode((string)$oid);
    $isUnpaid = (strtoupper($statusRaw) === 'UNPAID') || in_array(strtolower($paymentStatus), ['unpaid','pending','awaiting_payment'], true) || (isset($row['is_paid']) && (int)$row['is_paid'] === 0) || (isset($row['paid']) && (int)$row['paid'] === 0);
    if ($isUnpaid && $oid && function_exists('db_get_row')) { $inv = db_get_row('invoices', ['order_id' => $oid]); if (!empty($inv['uuid'])) $payUrl = '/checkout/' . rawurlencode((string)$inv['uuid']); }
  }

  [$statusLabel, $statusKey, $statusIcon] = lb_co_status($statusRaw, $paymentStatus);

  // Unpaid orders of every type should lead straight to checkout, exactly like boost orders.
  if (!$payUrl && $statusKey === 'unpaid' && function_exists('db_get_row')) {
    $invUuid = trim((string)($row['invoice_uuid'] ?? ''));
    if ($invUuid === '' && !empty($row['invoice_id'])) {
      $inv = db_get_row('invoices', ['id' => (int)$row['invoice_id']], 1);
      $invUuid = trim((string)($inv['uuid'] ?? ''));
    }
    // Note: invoices.order_id holds the *listing* id for marketplace orders, so it can
    // never be matched against the purchase id here — only invoice_id/uuid is reliable.
    if ($invUuid !== '') $payUrl = '/checkout/' . rawurlencode($invUuid);
  }
  $rows[] = [
    'id' => $id,
    'title' => $title ?: ('Order #' . $id),
    'sub' => $sub,
    'type' => $type,
    'type_label' => $typeLabel,
    'type_icon' => $typeIcon,
    'icon_html' => $iconHtml,
    'status_label' => $statusLabel,
    'status_key' => $statusKey,
    'status_icon' => $statusIcon,
    'price' => $price,
    'currency' => $currency,
    'coins' => $coins,
    'date' => $created,
    'url' => $url,
    'pay_url' => $payUrl,
    'search' => strtolower(trim(($title ?: '') . ' ' . $sub . ' #' . $id . ' ' . $typeLabel . ' ' . $statusLabel)),
  ];
}
usort($rows, function($a,$b){ return strtotime($b['date'] ?: '2000-01-01') <=> strtotime($a['date'] ?: '2000-01-01'); });
$typeCounts = [];
$statusCounts = [];
foreach ($rows as $r) { $typeCounts[$r['type']] = ($typeCounts[$r['type']] ?? 0) + 1; $statusCounts[$r['status_key']] = ($statusCounts[$r['status_key']] ?? 0) + 1; }
$typeOptions = [
  'boost' => ['Boost Orders','fa-rocket'],
  'account' => ['Accounts','fa-helmet-battle'],
  'topup' => ['Top Up Orders','fa-coins'],
  'companion' => ['Companion Orders','fa-user-group'],
  'currency' => ['Currency Orders','fa-coins'],
  'gamekey' => ['Game Key Orders','fa-gamepad-modern'],
  'digital' => ['Digital Goods','fa-layer-group'],
  'item' => ['Item Orders','fa-gift'],
];
$statusOptions = [
  'unpaid' => ['Unpaid','fa-xmark'],
  'processing' => ['Waiting for Booster','fa-spinner'],
  'paused' => ['Paused','fa-pause'],
  'paid' => ['Paid','fa-circle-check'],
  'inprogress' => ['In Progress','fa-loader'],
  'delivered' => ['Delivered','fa-truck-fast'],
  'completed' => ['Completed','fa-circle-check'],
  'refunded' => ['Refunded','fa-rotate-left'],
  'cancelled' => ['Cancelled','fa-ban'],
  'failed' => ['Failed','fa-circle-xmark'],
  'disputed' => ['Disputed','fa-scale-balanced'],
  'chargeback' => ['Chargeback','fa-shield-exclamation'],
];
$statusDotColors = [
  'unpaid' => '#fb7185',
  'processing' => '#a78bfa',
  'paused' => '#fbbf24',
  'paid' => '#60a5fa',
  'inprogress' => '#93c5fd',
  'delivered' => '#2dd4bf',
  'completed' => '#4ade80',
  'refunded' => '#fb923c',
  'cancelled' => '#94a3b8',
  'failed' => '#ef4444',
  'disputed' => '#f472b6',
  'chargeback' => '#e11d48',
];
?>
<?= $this->start('styles') ?>
<style>
.client-orders-v2 { max-width: 100%; }
.cov2-page-head { margin-bottom: 28px; }
.cov2-page-title { font-size: 1.35rem; font-weight: 900; color: rgba(255,255,255,.92); margin: 0 0 4px; }
.cov2-page-sub { font-size: .88rem; color: rgba(255,255,255,.48); }
.cov2-toolbar { display: flex; align-items: center; justify-content: space-between; gap: 14px; margin-bottom: 14px; }
.cov2-left { display: flex; align-items: center; gap: 10px; flex-wrap: wrap; width: 100%; }
.cov2-search { height: 40px; width: 250px; border: 1px solid rgba(255,255,255,.09); background: rgba(255,255,255,.035); border-radius: 999px; display: flex; align-items: center; gap: 8px; padding: 0 14px; color: rgba(255,255,255,.38); }
.cov2-search input { border: 0; background: transparent; outline: none; color: rgba(255,255,255,.9); width: 100%; font-size: .84rem; }
.cov2-search input::placeholder { color: rgba(255,255,255,.36); }

/* Unified dropdown-filter trigger, shared by Type / Status / Purchased At */
.cov2-filter { position: relative; }
.cov2-filter-btn,
.cov2-view-toggle { height: 40px; border: 1px solid rgba(255,255,255,.09); background: rgba(255,255,255,.035); color: rgba(255,255,255,.86); border-radius: 999px; padding: 0 16px; font-weight: 800; font-size: .78rem; display: flex; align-items: center; gap: 8px; cursor: pointer; transition: .15s ease; }
.cov2-filter-btn:hover,
.cov2-filter.is-open .cov2-filter-btn,
.cov2-view-toggle:hover { border-color: rgba(109,92,255,.38); background: rgba(109,92,255,.12); color: #fff; }
.cov2-filter-btn i.fa-chevron-down,
.cov2-filter-btn > i:last-child { font-size: .65rem; color: rgba(255,255,255,.4); margin-left: 2px; transition: transform .15s ease; }
.cov2-filter.is-open .cov2-filter-btn i.fa-chevron-down { transform: rotate(180deg); }
.cov2-filter-count { min-width: 19px; height: 19px; padding: 0 5px; border-radius: 999px; background: rgba(109,92,255,.4); color: #fff; font-size: .64rem; font-weight: 900; display: inline-flex; align-items: center; justify-content: center; }

.cov2-menu { position: absolute; top: calc(100% + 8px); left: 0; width: 250px; background: #25282a; border: 1px solid rgba(255,255,255,.08); border-radius: 14px; box-shadow: 0 22px 60px rgba(0,0,0,.42); z-index: 50; display: none; overflow: hidden; }
.cov2-filter.is-open .cov2-menu { display: block; }
.cov2-menu-search { padding: 10px; border-bottom: 1px solid rgba(255,255,255,.06); display: flex; align-items: center; gap: 8px; color: rgba(255,255,255,.38); }
.cov2-menu-search input { border: 0; background: transparent; color: rgba(255,255,255,.9); outline: none; width: 100%; font-size: .82rem; }
.cov2-menu-body { max-height: 290px; overflow: auto; padding: 7px; }
.cov2-option { display: flex; align-items: center; gap: 10px; width: 100%; padding: 8px 9px; border-radius: 9px; color: rgba(255,255,255,.72); font-size: .84rem; cursor: pointer; }
.cov2-option:hover { background: rgba(255,255,255,.05); }
.cov2-option input { appearance: none; width: 15px; height: 15px; border-radius: 4px; border: 1px solid rgba(255,255,255,.12); background: rgba(255,255,255,.035); }
.cov2-option input:checked { background: #6d5cff; border-color: #6d5cff; box-shadow: inset 0 0 0 3px #25282a; }
.cov2-option i { width: 17px; color: rgba(255,255,255,.35); }

/* Type / Status checklist inside the dropdown menu */
.cov2-check-menu { max-height: 320px; overflow: auto; padding: 7px; }
.cov2-check-item { width: 100%; display: flex; align-items: center; gap: 10px; padding: 9px 10px; border: 0; border-radius: 9px; background: transparent; color: rgba(255,255,255,.7); font-size: .82rem; font-weight: 700; text-align: left; cursor: pointer; transition: .15s ease; }
.cov2-check-item:hover { background: rgba(255,255,255,.05); color: #fff; }
.cov2-check-item.is-active { background: rgba(109,92,255,.16); color: #fff; }
.cov2-check-item i { width: 16px; text-align: center; color: rgba(255,255,255,.4); }
.cov2-check-item.is-active i { color: #b7a7ff; }
.cov2-check-dot { width: 8px; height: 8px; border-radius: 50%; flex: 0 0 auto; background: var(--dot, rgba(255,255,255,.4)); }
.cov2-check-num { margin-left: auto; min-width: 20px; height: 20px; padding: 0 6px; border-radius: 999px; background: rgba(255,255,255,.07); color: rgba(255,255,255,.65); font-size: .66rem; font-weight: 800; display: inline-flex; align-items: center; justify-content: center; }
.cov2-check-item.is-active .cov2-check-num { background: rgba(255,255,255,.16); color: #fff; }

.cov2-date-menu { width: 600px; display: none; grid-template-columns: 225px 1fr; overflow: visible; }
.cov2-filter.is-open .cov2-date-menu { display: grid; }
.cov2-date-quick { border-right: 1px solid rgba(255,255,255,.07); padding: 8px; }
.cov2-date-quick button { display: flex; align-items: center; justify-content: space-between; gap: 10px; width: 100%; text-align: left; border: 0; background: transparent; color: rgba(255,255,255,.78); border-radius: 9px; padding: 8px 10px; font-size: .84rem; }
.cov2-date-quick button > span:first-child { white-space: nowrap; }
.cov2-date-summary { color: rgba(255,255,255,.36); font-size: .68rem; font-weight: 700; white-space: nowrap; }
.cov2-date-quick button:hover,
.cov2-date-quick button.is-active { background: rgba(109,92,255,.14); color: #fff; }
.cov2-date-quick button.is-active .cov2-date-summary { color: #b7a7ff; }
.cov2-date-custom { position: relative; padding: 14px; color: rgba(255,255,255,.5); }
.cov2-date-fields { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; }
.cov2-date-field label { display: block; font-size: .72rem; text-transform: uppercase; letter-spacing: .08em; color: rgba(255,255,255,.38); margin-bottom: 7px; }
.cov2-date-input { height: 38px; width: 100%; border: 1px solid rgba(255,255,255,.09); border-radius: 10px; background: rgba(255,255,255,.035); color: rgba(255,255,255,.82); display: flex; align-items: center; justify-content: space-between; gap: 10px; padding: 0 11px; font-size: .82rem; font-weight: 800; text-align: left; }
.cov2-date-input:hover,
.cov2-date-input.is-active { border-color: rgba(109,92,255,.38); background: rgba(109,92,255,.13); color: #fff; }
.cov2-date-input i { color: rgba(165,180,252,.82); font-size: .86rem; }
.cov2-calendar { position: static; width: 100%; margin-top: 12px; border: 1px solid rgba(109,92,255,.24); border-radius: 15px; background: #1d2023; padding: 12px; display: block; }
.cov2-cal-head { display: flex; align-items: center; justify-content: space-between; gap: 8px; margin-bottom: 10px; }
.cov2-cal-head strong { color: rgba(255,255,255,.9); font-size: .86rem; font-weight: 900; }
.cov2-cal-head button,
.cov2-cal-actions button { border: 1px solid rgba(255,255,255,.08); background: rgba(255,255,255,.045); color: rgba(255,255,255,.75); border-radius: 9px; height: 30px; padding: 0 10px; font-weight: 850; }
.cov2-cal-head button:hover,
.cov2-cal-actions button:hover { border-color: rgba(109,92,255,.35); background: rgba(109,92,255,.15); color: #fff; }
.cov2-cal-week,
.cov2-cal-days { display: grid; grid-template-columns: repeat(7, 1fr); gap: 5px; }
.cov2-cal-week span { text-align: center; color: rgba(255,255,255,.34); font-size: .66rem; font-weight: 900; text-transform: uppercase; }
.cov2-cal-days { margin-top: 7px; }
.cov2-cal-day { height: 31px; border: 0; border-radius: 9px; background: transparent; color: rgba(255,255,255,.76); font-size: .78rem; font-weight: 850; }
.cov2-cal-day:hover { background: rgba(109,92,255,.15); color: #fff; }
.cov2-cal-day.is-muted { color: rgba(255,255,255,.24); }
.cov2-cal-day.is-today { box-shadow: inset 0 0 0 1px rgba(165,180,252,.45); color: #fff; }
.cov2-cal-day.is-in-range { background: rgba(109,92,255,.18); color: #ddd6fe; }
.cov2-cal-day.is-selected { background: #6d5cff; color: #fff; box-shadow: 0 8px 18px rgba(109,92,255,.24); }
.cov2-cal-actions { display: flex; align-items: center; justify-content: space-between; margin-top: 12px; }
.cov2-cal-actions button { height: 31px; font-size: .76rem; }
.cov2-cal-actions [data-cal-clear] { color: #fb7185; }
.cov2-cal-actions [data-cal-today] { color: #a5b4fc; }
.cov2-table-card { background: #25282a; border: 1px solid rgba(109,92,255,.12); border-radius: 18px; overflow: hidden; }
.cov2-table { width: 100%; border-collapse: collapse; }
.cov2-table th { height: 44px; padding: 0 14px; text-transform: uppercase; letter-spacing: .06em; font-size: .66rem; color: rgba(255,255,255,.38); font-weight: 900; border-bottom: 1px solid rgba(255,255,255,.06); background: rgba(255,255,255,.025); white-space: nowrap; }
.cov2-table td { padding: 12px 14px; border-bottom: 1px solid rgba(255,255,255,.05); vertical-align: middle; }
.cov2-table tr:last-child td { border-bottom: 0; }
.cov2-table tbody tr:hover { background: rgba(255,255,255,.018); }
.cov2-order-cell { display: flex; align-items: center; gap: 10px; min-width: 300px; }
.cov2-order-cell > div:last-child { min-width: 0; }
.cov2-order-link { color: inherit; text-decoration: none; cursor: pointer; }
.cov2-order-link:hover .cov2-order-title { color: #fff; }
.cov2-order-link:hover .cov2-icon { border-color: rgba(109,92,255,.42); background: rgba(109,92,255,.2); }
.cov2-icon { width: 42px; height: 42px; border-radius: 11px; background: rgba(109,92,255,.12); border: 1px solid rgba(109,92,255,.18); display: flex; align-items: center; justify-content: center; position: relative; overflow: visible; color: #a5b4fc; flex-shrink: 0; }
.cov2-icon img { width: 100%; height: 100%; object-fit: contain; padding: 6px; border-radius: 11px; }
.cov2-icon .cov2-rank-img { padding: 5px; filter: drop-shadow(0 2px 5px rgba(0,0,0,.45)); }
.cov2-icon .cov2-game-badge { position: absolute; right: -5px; bottom: -5px; width: 18px; height: 18px; min-width: 18px; padding: 0; border-radius: 50%; border: 2px solid #25282a; background: #1b1d1f; object-fit: cover; box-shadow: 0 6px 14px rgba(0,0,0,.45); }
.cov2-order-title { font-size: .86rem; font-weight: 900; color: rgba(255,255,255,.9); max-width: 440px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; line-height: 1.35; }
.cov2-order-sub { font-size: .72rem; color: rgba(255,255,255,.38); margin-top: 2px; }
.cov2-id-wrap { display: inline-flex; align-items: center; gap: 7px; white-space: nowrap; }
.cov2-id { font-size: .85rem; font-weight: 900; color: rgba(255,255,255,.86); }
.cov2-copy-id { width: 27px; height: 27px; display: inline-flex; align-items: center; justify-content: center; border: 1px solid rgba(255,255,255,.09); border-radius: 8px; background: rgba(255,255,255,.035); color: rgba(255,255,255,.4); cursor: pointer; transition: .15s ease; }
.cov2-copy-id:hover { color: #c4b5fd; border-color: rgba(109,92,255,.35); background: rgba(109,92,255,.13); }
.cov2-copy-id.is-copied { color: #4ade80; border-color: rgba(74,222,128,.3); background: rgba(74,222,128,.1); }
/* Status badge — pill + dot, matching Admin/Booster Orders List */
.cov2-status { display: inline-flex; align-items: center; gap: 8px; border: 1px solid rgba(255,255,255,.1); background: rgba(255,255,255,.045); border-radius: 999px; padding: 6px 12px; font-weight: 950; font-size: .68rem; letter-spacing: .07em; text-transform: uppercase; color: rgba(255,255,255,.72); white-space: nowrap; }
.cov2-status__dot { width: 6px; height: 6px; border-radius: 50%; background: currentColor; opacity: .95; flex: 0 0 auto; }
.cov2-status.unpaid { color: #fb7185; background: rgba(251,113,133,.1); border-color: rgba(251,113,133,.22); }
.cov2-status.paid { color:#60a5fa; background:rgba(96,165,250,.1); border-color:rgba(96,165,250,.22); }
.cov2-status.completed { color:#4ade80; background:rgba(74,222,128,.1); border-color:rgba(74,222,128,.2); }
.cov2-status.processing { color: #a78bfa; background: rgba(167,139,250,.1); border-color: rgba(167,139,250,.22); }
.cov2-status.inprogress { color: #93c5fd; background: rgba(147,197,253,.1); border-color: rgba(147,197,253,.2); }
.cov2-status.delivered { color:#2dd4bf; background:rgba(45,212,191,.1); border-color:rgba(45,212,191,.22); }
.cov2-status.paused { color: #fbbf24; background: rgba(251,191,36,.1); border-color: rgba(251,191,36,.22); }
.cov2-status.refunded { color:#fb923c; background:rgba(251,146,60,.1); border-color:rgba(251,146,60,.22); }
.cov2-status.cancelled { color:#94a3b8; background:rgba(148,163,184,.1); border-color:rgba(148,163,184,.22); }
.cov2-status.failed { color:#ef4444; background:rgba(239,68,68,.1); border-color:rgba(239,68,68,.22); }
.cov2-status.disputed { color:#f472b6; background:rgba(244,114,182,.1); border-color:rgba(244,114,182,.22); }
.cov2-status.chargeback { color:#e11d48; background:rgba(225,29,72,.1); border-color:rgba(225,29,72,.25); }
.cov2-price { font-weight: 900; color: rgba(255,255,255,.9); white-space: nowrap; }
.cov2-muted { color: rgba(255,255,255,.38); }
.cov2-action { display: inline-flex; align-items: center; gap: 7px; height: 32px; border-radius: 10px; border: 1px solid rgba(109,92,255,.28); background: rgba(109,92,255,.14); color: #c4b5fd; text-decoration: none; padding: 0 11px; font-size: .78rem; font-weight: 900; white-space: nowrap; }
.cov2-action:hover { color: #fff; background: rgba(109,92,255,.24); border-color: rgba(109,92,255,.42); }
.cov2-action-pay { background: rgba(239,68,68,.12); border-color: rgba(248,113,113,.3); color: #fca5a5; }
.cov2-action-pay:hover { background: rgba(239,68,68,.22); border-color: rgba(248,113,113,.5); color: #fff; }
.cov2-empty { text-align: center; padding: 56px 18px; color: rgba(255,255,255,.42); }
.cov2-footer { display: flex; align-items: center; justify-content: space-between; gap: 12px; margin-top: 14px; color: rgba(255,255,255,.38); font-size: .8rem; }
.cov2-pages { display: flex; align-items: center; gap: 6px; }
.cov2-page-btn { height: 32px; min-width: 32px; border-radius: 9px; border: 1px solid rgba(255,255,255,.09); background: rgba(255,255,255,.035); color: rgba(255,255,255,.68); font-weight: 900; }
.cov2-page-btn.is-active { background: rgba(109,92,255,.24); border-color: rgba(109,92,255,.4); color: #fff; }
.cov2-page-btn:disabled { opacity: .4; }

.lb-coin-display { display: inline-flex; align-items: center; gap: 7px; font-weight: 900; color: rgba(255,255,255,.86); white-space: nowrap; }
.lb-coin-display i { color: #a5b4fc; font-size: 13px; line-height: 1; opacity: .9; }
@media (max-width: 1100px) { .cov2-date-fields { grid-template-columns: 1fr; } .cov2-calendar { position: static; width: 100%; margin-top: 12px; } .cov2-table-card { overflow-x: auto; } .cov2-table { min-width: 980px; } .cov2-toolbar { align-items: flex-start; flex-direction: column; } .cov2-left, .cov2-search { width: 100%; } .cov2-date-menu { width: min(560px, calc(100vw - 40px)); grid-template-columns: 1fr; } .cov2-date-quick { border-right: 0; border-bottom: 1px solid rgba(255,255,255,.07); } .cov2-filter { width: 100%; } .cov2-filter-btn { width: 100%; justify-content: space-between; } .cov2-menu { width: 100%; } }
</style>
<?= $this->end() ?>
<div class="client-orders-v2">
  <div class="cov2-page-head">
    <h1 class="cov2-page-title">My Orders</h1>
    <div class="cov2-page-sub">View and manage all your orders in one place.</div>
  </div>
  <div class="cov2-toolbar">
    <div class="cov2-left">
      <label class="cov2-search"><i class="fa-duotone fa-search"></i><input id="cov2Search" type="search" placeholder="Search..." autocomplete="off"></label>

      <div class="cov2-filter" data-filter="type">
        <button class="cov2-filter-btn" type="button">
          <i class="fa-duotone fa-layer-group"></i><span>Type</span>
          <span class="cov2-filter-count" id="cov2TypeCount" hidden>0</span>
          <i class="fa-duotone fa-chevron-down"></i>
        </button>
        <div class="cov2-menu">
          <div class="cov2-check-menu" aria-label="Order type filters">
            <?php foreach ($typeOptions as $key => $opt): if (($typeCounts[$key] ?? 0) < 1) continue; ?>
              <button class="cov2-check-item" type="button" data-type-filter value="<?= lb_co_h($key) ?>">
                <i class="fa-duotone <?= lb_co_h($opt[1]) ?>"></i>
                <span><?= lb_co_h($opt[0]) ?></span>
                <span class="cov2-check-num"><?= (int)($typeCounts[$key] ?? 0) ?></span>
              </button>
            <?php endforeach; ?>
          </div>
        </div>
      </div>

      <div class="cov2-filter" data-filter="status">
        <button class="cov2-filter-btn" type="button">
          <i class="fa-duotone fa-list-check"></i><span>Status</span>
          <span class="cov2-filter-count" id="cov2StatusCount" hidden>0</span>
          <i class="fa-duotone fa-chevron-down"></i>
        </button>
        <div class="cov2-menu">
          <div class="cov2-check-menu" aria-label="Order status filters">
            <?php foreach ($statusOptions as $key => $opt): ?>
              <button class="cov2-check-item" type="button" data-status-filter value="<?= lb_co_h($key) ?>" style="--dot:<?= lb_co_h($statusDotColors[$key] ?? 'rgba(255,255,255,.4)') ?>">
                <span class="cov2-check-dot"></span>
                <span><?= lb_co_h($opt[0]) ?></span>
              </button>
            <?php endforeach; ?>
          </div>
        </div>
      </div>

      <div class="cov2-filter" data-filter="date">
        <button class="cov2-filter-btn" type="button"><i class="fa-duotone fa-calendar-range"></i><span>Purchased At</span><i class="fa-duotone fa-chevron-down"></i></button>
        <div class="cov2-menu cov2-date-menu">
          <div class="cov2-date-quick">
            <button type="button" data-date-range="all" class="is-active"><span>All Time</span></button>
            <button type="button" data-date-range="today"><span>Today</span><span class="cov2-date-summary" data-range-summary="today"></span></button>
            <button type="button" data-date-range="yesterday"><span>Yesterday</span><span class="cov2-date-summary" data-range-summary="yesterday"></span></button>
            <button type="button" data-date-range="week"><span>This Week</span><span class="cov2-date-summary" data-range-summary="week"></span></button>
            <button type="button" data-date-range="last7"><span>Last 7 Days</span><span class="cov2-date-summary" data-range-summary="last7"></span></button>
            <button type="button" data-date-range="last14"><span>Last 14 Days</span><span class="cov2-date-summary" data-range-summary="last14"></span></button>
            <button type="button" data-date-range="last30"><span>Last 30 Days</span><span class="cov2-date-summary" data-range-summary="last30"></span></button>
            <button type="button" data-date-range="custom"><span>Custom Range</span><span class="cov2-date-summary">Choose</span></button>
          </div>
          <div class="cov2-date-custom">
            <div class="cov2-date-fields">
              <div class="cov2-date-field">
                <label>1. Start date</label>
                <button class="cov2-date-input is-active" type="button" data-date-picker="from"><span data-date-display="from">Choose start</span><i class="fa-regular fa-calendar"></i></button>
                <input id="cov2DateFrom" type="hidden">
              </div>
              <div class="cov2-date-field">
                <label>2. End date</label>
                <button class="cov2-date-input" type="button" data-date-picker="to"><span data-date-display="to">Choose end</span><i class="fa-regular fa-calendar"></i></button>
                <input id="cov2DateTo" type="hidden">
              </div>
            </div>
            <div class="cov2-calendar" id="cov2Calendar" aria-hidden="true">
              <div class="cov2-cal-head">
                <button type="button" data-cal-prev><i class="fa-solid fa-chevron-left"></i></button>
                <strong data-cal-title></strong>
                <button type="button" data-cal-next><i class="fa-solid fa-chevron-right"></i></button>
              </div>
              <div class="cov2-cal-week"><span>Mo</span><span>Tu</span><span>We</span><span>Th</span><span>Fr</span><span>Sa</span><span>Su</span></div>
              <div class="cov2-cal-days" data-cal-days></div>
              <div class="cov2-cal-actions"><button type="button" data-cal-clear>Clear</button><button type="button" data-cal-today>Today</button></div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  <div class="cov2-table-card">
    <table class="cov2-table">
      <thead><tr><th>Order</th><th>ID</th><th>Status</th><th>Price</th><th>LB Coins</th><th>Last Updated</th><th></th></tr></thead>
      <tbody id="cov2Rows">
        <?php if (!$rows): ?>
          <tr><td colspan="7"><div class="cov2-empty"><i class="fa-duotone fa-cart-shopping fa-2x mb-2"></i><div>No orders yet</div><small>Your orders will appear here after checkout.</small></div></td></tr>
        <?php else: foreach ($rows as $r): ?>
          <tr class="cov2-row" data-type="<?= lb_co_h($r['type']) ?>" data-status="<?= lb_co_h($r['status_key']) ?>" data-search="<?= lb_co_h($r['search']) ?>" data-date="<?= lb_co_h(date('Y-m-d', strtotime($r['date'] ?: '2000-01-01'))) ?>">
            <td><a class="cov2-order-cell cov2-order-link" href="<?= lb_co_h(BASE_URL . ($r['pay_url'] ?: $r['url'])) ?>"><div class="cov2-icon"><?= $r['icon_html'] ?></div><div><div class="cov2-order-title" title="<?= lb_co_h(html_entity_decode((string)$r['title'], ENT_QUOTES | ENT_HTML5, 'UTF-8')) ?>"><?= lb_co_title($r['title']) ?></div><div class="cov2-order-sub"><?= lb_co_h($r['type_label']) ?><?= $r['sub'] !== '' ? ' · ' . lb_co_h($r['sub']) : '' ?></div></div></a></td>
            <td><span class="cov2-id-wrap"><span class="cov2-id">#<?= lb_co_h($r['id']) ?></span><button class="cov2-copy-id" type="button" data-copy-id="<?= lb_co_h($r['id']) ?>" title="Copy order ID" aria-label="Copy order ID #<?= lb_co_h($r['id']) ?>"><i class="fa-regular fa-copy"></i></button></span></td>
            <td><span class="cov2-status <?= lb_co_h($r['status_key']) ?>"><span class="cov2-status__dot"></span><?= lb_co_h($r['status_label']) ?></span></td>
            <td><span class="cov2-price"><?= lb_co_price_display($r['price'], $r['currency']) ?></span></td>
            <td><?= lb_co_coin_display($r['coins']) ?></td>
            <td><span class="cov2-price" style="font-size:.82rem"><?= lb_co_h(lb_co_date($r['date'])) ?></span></td>
            <td style="text-align:right"><?php if ($r['pay_url']): ?><a class="cov2-action cov2-action-pay" href="<?= lb_co_h(BASE_URL . $r['pay_url']) ?>">Pay Now <i class="fa-solid fa-arrow-right"></i></a><?php else: ?><a class="cov2-action" href="<?= lb_co_h(BASE_URL . $r['url']) ?>">View <i class="fa-solid fa-arrow-right"></i></a><?php endif; ?></td>
          </tr>
        <?php endforeach; endif; ?>
      </tbody>
    </table>
  </div>
  <?php if ($rows): ?><div class="cov2-footer"><span id="cov2Info"></span><div class="cov2-pages" id="cov2Pages"></div></div><?php endif; ?>
</div>
<?= $this->start('scripts') ?>
<script>
(function(){
  const rows = Array.from(document.querySelectorAll('.cov2-row'));
  const search = document.getElementById('cov2Search');
  const pagesEl = document.getElementById('cov2Pages');
  const infoEl = document.getElementById('cov2Info');
  const typeChecks = Array.from(document.querySelectorAll('[data-type-filter]'));
  const statusChecks = Array.from(document.querySelectorAll('[data-status-filter]'));
  const dateButtons = Array.from(document.querySelectorAll('[data-date-range]'));
  const dateFrom = document.getElementById('cov2DateFrom');
  const dateTo = document.getElementById('cov2DateTo');
  let page = 1, perPage = 12, range = 'all';
  document.querySelectorAll('.cov2-filter-btn').forEach(btn => btn.addEventListener('click', function(e){ e.stopPropagation(); const box=this.closest('.cov2-filter'); document.querySelectorAll('.cov2-filter').forEach(x=>{ if(x!==box) x.classList.remove('is-open'); }); box.classList.toggle('is-open'); }));
  document.addEventListener('click', e => { if (!e.target.closest('.cov2-filter')) document.querySelectorAll('.cov2-filter').forEach(x=>x.classList.remove('is-open')); });
  document.querySelectorAll('.cov2-menu-search input').forEach(inp => inp.addEventListener('input', function(){ const q=this.value.trim().toLowerCase(); this.closest('.cov2-menu').querySelectorAll('.cov2-option').forEach(o=>o.style.display=o.textContent.toLowerCase().includes(q)?'flex':'none'); }));
  function selected(buttons){ return buttons.filter(b=>b.classList.contains('is-active')).map(b=>b.value); }
  const dateDisplays = {
    from: document.querySelector('[data-date-display="from"]'),
    to: document.querySelector('[data-date-display="to"]')
  };
  const calendar = document.getElementById('cov2Calendar');
  const calTitle = calendar ? calendar.querySelector('[data-cal-title]') : null;
  const calDays = calendar ? calendar.querySelector('[data-cal-days]') : null;
  let activeDateTarget = 'from';
  let calMonth = new Date();
  calMonth = new Date(calMonth.getFullYear(), calMonth.getMonth(), 1);
  function pad(n){ return String(n).padStart(2, '0'); }
  function iso(d){ return d.getFullYear() + '-' + pad(d.getMonth()+1) + '-' + pad(d.getDate()); }
  function pretty(v){ if(!v) return 'Any date'; const d = new Date(v + 'T00:00:00'); return d.toLocaleDateString(undefined, {month:'short', day:'2-digit', year:'numeric'}); }
  function shortDate(d){ return d.toLocaleDateString(undefined, {month:'short', day:'numeric'}); }
  function setRangeSummaries(){
    const today = new Date();
    today.setHours(0,0,0,0);
    const shifted = days => { const d = new Date(today); d.setDate(d.getDate() + days); return d; };
    const monday = new Date(today);
    monday.setDate(today.getDate() - ((today.getDay() + 6) % 7));
    const summaries = {
      today: shortDate(today),
      yesterday: shortDate(shifted(-1)),
      week: shortDate(monday) + '–' + shortDate(today),
      last7: shortDate(shifted(-6)) + '–' + shortDate(today),
      last14: shortDate(shifted(-13)) + '–' + shortDate(today),
      last30: shortDate(shifted(-29)) + '–' + shortDate(today)
    };
    document.querySelectorAll('[data-range-summary]').forEach(el => {
      el.textContent = summaries[el.dataset.rangeSummary] || '';
    });
  }
  setRangeSummaries();
  function setCustomActive(){ dateButtons.forEach(x=>x.classList.remove('is-active')); const custom = document.querySelector('[data-date-range="custom"]'); if(custom) custom.classList.add('is-active'); range='custom'; }
  function renderCalendar(){
    if(!calendar || !calDays || !calTitle) return;
    calTitle.textContent = calMonth.toLocaleDateString(undefined, {month:'long', year:'numeric'});
    calDays.innerHTML = '';
    const first = new Date(calMonth.getFullYear(), calMonth.getMonth(), 1);
    const offset = (first.getDay()+6)%7;
    const start = new Date(first); start.setDate(first.getDate()-offset);
    const todayIso = iso(new Date());
    const fromIso = dateFrom.value;
    const toIso = dateTo.value;
    for(let i=0;i<42;i++){
      const d = new Date(start); d.setDate(start.getDate()+i);
      const val = iso(d);
      const b = document.createElement('button');
      b.type = 'button';
      const inRange = fromIso && toIso && val > fromIso && val < toIso;
      const isSelected = val === fromIso || val === toIso;
      b.className = 'cov2-cal-day' + (d.getMonth()!==calMonth.getMonth() ? ' is-muted' : '') + (val===todayIso ? ' is-today' : '') + (inRange ? ' is-in-range' : '') + (isSelected ? ' is-selected' : '');
      b.textContent = d.getDate();
      b.addEventListener('click', function(){
        if(activeDateTarget === 'from' || !dateFrom.value || dateTo.value) {
          dateFrom.value = val;
          dateTo.value = '';
          if(dateDisplays.from) dateDisplays.from.textContent = pretty(val);
          if(dateDisplays.to) dateDisplays.to.textContent = 'Select end date';
          activeDateTarget = 'to';
        } else {
          if(val < dateFrom.value) {
            dateTo.value = dateFrom.value;
            dateFrom.value = val;
          } else {
            dateTo.value = val;
          }
          if(dateDisplays.from) dateDisplays.from.textContent = pretty(dateFrom.value);
          if(dateDisplays.to) dateDisplays.to.textContent = pretty(dateTo.value);
          activeDateTarget = 'from';
        }
        setCustomActive();
        page = 1;
        draw();
        document.querySelectorAll('[data-date-picker]').forEach(x=>x.classList.toggle('is-active', x.dataset.datePicker === activeDateTarget));
        renderCalendar();
      });
      calDays.appendChild(b);
    }
  }
  document.querySelectorAll('[data-date-picker]').forEach(btn => btn.addEventListener('click', function(e){
    e.stopPropagation();
    activeDateTarget = this.dataset.datePicker;
    document.querySelectorAll('[data-date-picker]').forEach(x=>x.classList.remove('is-active'));
    this.classList.add('is-active');
    const current = activeDateTarget === 'from' ? dateFrom.value : dateTo.value;
    if(current) { const d = new Date(current + 'T00:00:00'); calMonth = new Date(d.getFullYear(), d.getMonth(), 1); }
    renderCalendar();
  }));
  if(calendar){
    calendar.addEventListener('click', e => e.stopPropagation());
    calendar.querySelector('[data-cal-prev]')?.addEventListener('click', () => { calMonth = new Date(calMonth.getFullYear(), calMonth.getMonth()-1, 1); renderCalendar(); });
    calendar.querySelector('[data-cal-next]')?.addEventListener('click', () => { calMonth = new Date(calMonth.getFullYear(), calMonth.getMonth()+1, 1); renderCalendar(); });
    calendar.querySelector('[data-cal-clear]')?.addEventListener('click', () => {
      dateFrom.value = '';
      dateTo.value = '';
      activeDateTarget = 'from';
      if(dateDisplays.from) dateDisplays.from.textContent = 'Choose start';
      if(dateDisplays.to) dateDisplays.to.textContent = 'Choose end';
      document.querySelectorAll('[data-date-picker]').forEach(x=>x.classList.toggle('is-active', x.dataset.datePicker === 'from'));
      setCustomActive(); page = 1; draw(); renderCalendar();
    });
    calendar.querySelector('[data-cal-today]')?.addEventListener('click', () => {
      const val = iso(new Date());
      dateFrom.value = val;
      dateTo.value = val;
      activeDateTarget = 'from';
      if(dateDisplays.from) dateDisplays.from.textContent = pretty(val);
      if(dateDisplays.to) dateDisplays.to.textContent = pretty(val);
      setCustomActive(); page = 1; draw(); renderCalendar();
    });
  }
  function dateOk(d){ if(range==='all') return true; const dt = new Date(d+'T00:00:00'); const now = new Date(); const today = new Date(now.getFullYear(), now.getMonth(), now.getDate()); const one = 86400000; if(range==='today') return dt.getTime()===today.getTime(); if(range==='yesterday') return dt.getTime()===today.getTime()-one; if(range==='week'){ const start=new Date(today); start.setDate(today.getDate()-((today.getDay()+6)%7)); return dt>=start && dt<=today; } if(range==='last7') return dt>=new Date(today.getTime()-6*one) && dt<=today; if(range==='last14') return dt>=new Date(today.getTime()-13*one) && dt<=today; if(range==='last30') return dt>=new Date(today.getTime()-29*one) && dt<=today; if(range==='custom'){ const f=dateFrom.value?new Date(dateFrom.value+'T00:00:00'):null; const t=dateTo.value?new Date(dateTo.value+'T23:59:59'):null; return (!f || dt>=f) && (!t || dt<=t); } return true; }
  function filtered(){ const q=(search?.value||'').trim().toLowerCase(); const types=selected(typeChecks); const stats=selected(statusChecks); return rows.filter(r => (!q || r.dataset.search.includes(q)) && (!types.length || types.includes(r.dataset.type)) && (!stats.length || stats.includes(r.dataset.status)) && dateOk(r.dataset.date)); }
  function draw(){ const list=filtered(); const total=list.length; const pages=Math.max(1,Math.ceil(total/perPage)); if(page>pages) page=1; rows.forEach(r=>r.style.display='none'); const start=(page-1)*perPage, end=Math.min(start+perPage,total); list.slice(start,end).forEach(r=>r.style.display='table-row'); if(infoEl) infoEl.textContent = total ? `Showing ${start+1}–${end} of ${total}` : 'No results'; if(pagesEl){ pagesEl.innerHTML=''; const mk=(html,disabled,active,fn)=>{const b=document.createElement('button');b.type='button';b.className='cov2-page-btn'+(active?' is-active':'');b.innerHTML=html;b.disabled=disabled;b.addEventListener('click',fn);pagesEl.appendChild(b);}; mk('<i class="fa-solid fa-chevron-left"></i>',page===1,false,()=>{page--;draw();}); for(let i=1;i<=pages;i++){ if(pages>7 && i>2 && i<pages-1 && Math.abs(i-page)>1){ if(i===3){ const s=document.createElement('span'); s.className='cov2-muted'; s.textContent='…'; pagesEl.appendChild(s);} continue;} mk(i,false,i===page,()=>{page=i;draw();}); } mk('<i class="fa-solid fa-chevron-right"></i>',page===pages,false,()=>{page++;draw();}); }}
  [search, dateFrom, dateTo].forEach(el=>el&&el.addEventListener('input',()=>{page=1;draw();}));
  const typeCountEl = document.getElementById('cov2TypeCount');
  const statusCountEl = document.getElementById('cov2StatusCount');
  function updateFilterCount(el, checks) {
    if (!el) return;
    const n = checks.filter(b => b.classList.contains('is-active')).length;
    el.textContent = n;
    el.hidden = n === 0;
  }
  [...typeChecks,...statusChecks].forEach(el=>el.addEventListener('click',()=>{
    el.classList.toggle('is-active');
    updateFilterCount(typeCountEl, typeChecks);
    updateFilterCount(statusCountEl, statusChecks);
    page=1;draw();
  }));
  document.querySelectorAll('[data-copy-id]').forEach(btn => btn.addEventListener('click', async function(){
    const value = '#' + this.dataset.copyId;
    try {
      await navigator.clipboard.writeText(value);
    } catch (e) {
      const input = document.createElement('textarea');
      input.value = value;
      input.style.position = 'fixed';
      input.style.opacity = '0';
      document.body.appendChild(input);
      input.select();
      document.execCommand('copy');
      input.remove();
    }
    const icon = this.querySelector('i');
    this.classList.add('is-copied');
    this.title = 'Copied!';
    if(icon) icon.className = 'fa-solid fa-check';
    window.setTimeout(() => {
      this.classList.remove('is-copied');
      this.title = 'Copy order ID';
      if(icon) icon.className = 'fa-regular fa-copy';
    }, 1400);
  }));
  dateButtons.forEach(b=>b.addEventListener('click',()=>{
    dateButtons.forEach(x=>x.classList.remove('is-active'));
    b.classList.add('is-active');
    range=b.dataset.dateRange;
    if(range!=='custom'){
      if(calendar) calendar.classList.remove('is-open');
      document.querySelectorAll('[data-date-picker]').forEach(x=>x.classList.remove('is-active'));
    } else {
      // Reveal the calendar right away instead of requiring an extra click on From/To.
      const fromBtn = document.querySelector('[data-date-picker="from"]');
      if (fromBtn) fromBtn.click();
    }
    page=1; draw();
  }));
  renderCalendar();
  draw();
})();
</script>
<?= $this->end() ?>
