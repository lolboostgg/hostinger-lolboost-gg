<?= $this->layout('admin/layouts/main', ['meta' => ['title' => 'Account Orders | Admin Area']]) ?>

<?php
$canViewAllAccounts = true;
if (defined('ADMIN_DATA') && is_array(ADMIN_DATA) && array_key_exists('can_view_all_accounts', ADMIN_DATA)) {
    $canViewAllAccounts = ((int)ADMIN_DATA['can_view_all_accounts'] === 1);
}
$restrictToOwn = !$canViewAllAccounts;

// Pre-build the filtered data array so we have a clean count
$rows = [];
foreach ($data as $row) {
    if ($restrictToOwn) {
        if (empty($row['admin_id']) || (int)$row['admin_id'] !== (int)ADMIN_ID) continue;
    }
    $rows[] = $row;
}


$smurfRows = (isset($smurfRows) && is_array($smurfRows)) ? $smurfRows : [];
$totalAccountOrders = count($rows) + count($smurfRows);
$accountOrdersLimit = isset($accountOrdersLimit) ? (int)$accountOrdersLimit : 0;
$h = fn($v) => htmlspecialchars((string)($v ?? ''), ENT_QUOTES, 'UTF-8');

$alCleanText = static function ($value): string {
    $text = (string)($value ?? '');
    for ($i = 0; $i < 3; $i++) {
        $decoded = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        if ($decoded === $text) break;
        $text = $decoded;
    }
    return $text;
};

$gameIconFor = static function (string $game): string {
    return $game === 'val'
        ? '/public/assets/website/images/icons/valorant.png'
        : '/public/assets/website/images/icons/league-of-legends.png';
};
$formatCents = static function ($value): string {
    $value = (int)round((float)($value ?? 0));
    return function_exists('util_format_price_display') ? util_format_price_display($value) : number_format($value / 100, 2);
};
$fmtDate = static function ($value): array {
    $ts = !empty($value) ? strtotime((string)$value) : 0;
    return [$ts ?: 0, $ts ? date('d.m.Y', $ts) : '—'];
};
$accountOrderRows = [];

foreach ($rows as $acc) {
    $soldState  = (int)($acc['sold'] ?? 0);
    $sold       = $soldState === 1;
    $refunded   = $soldState === 2;
    $active     = (int)($acc['active'] ?? 1) === 1;
    $priceRaw   = (int)round((float)($acc['price'] ?? 0));
    $earningsRaw = (int)round($priceRaw * 0.85);
    $status     = $refunded ? 'Refunded' : ($sold ? 'Sold' : ($active ? 'Active' : 'Unlisted'));
    $badgeCls   = $refunded ? 'al-badge--refunded' : ($sold ? 'al-badge--sold' : ($active ? 'al-badge--active' : 'al-badge--unlisted'));
    [$createdAtTs, $createdAtFmt] = $fmtDate($acc['created_at'] ?? null);
    [$soldAtTs, $soldAtFmt] = $fmtDate($acc['sold_at'] ?? null);

    $game = strtolower((string)($acc['game'] ?? 'lol'));
    if ($game === 'valorant') $game = 'val';
    if (!in_array($game, ['lol', 'val'], true)) $game = 'lol';
    $gameLabel = $game === 'val' ? 'VAL' : 'LoL';

    $gameData = [];
    if (!empty($acc['game_data'])) {
        $decodedGameData = json_decode((string)$acc['game_data'], true);
        if (is_array($decodedGameData)) $gameData = $decodedGameData;
    }

    if ($game === 'val') {
        $valRankId = (int)($acc['rank'] ?? 0);
        if ($valRankId <= 0 && !empty($gameData['val_rank'])) $valRankId = (int)$gameData['val_rank'];
        $rankLabel = function_exists('util_get_val_rank') ? util_get_val_rank($valRankId) : trim((string)($acc['rank_label'] ?? ''));
        if ($rankLabel === '') $rankLabel = !empty($acc['rank_label']) ? (string)$acc['rank_label'] : (!empty($gameData['val_rank']) ? ('Rank ' . (int)$gameData['val_rank']) : 'Valorant');
        if (!empty($gameData['val_points'])) $rankLabel .= ' ' . (int)$gameData['val_points'] . ' RR';
        $rankImgSrc = '/public/assets/core/main/img/val/ranks/mini/' . max(0, $valRankId) . '.png';
    } else {
        $rankLabel = function_exists('util_get_lol_rank') ? util_get_lol_rank($acc['current_rank'] ?? 0) : 'Rank ' . ($acc['current_rank'] ?? '');
        if ((int)($acc['current_rank'] ?? 0) > 0 && (int)($acc['current_rank'] ?? 0) < 8) {
            $rankLabel .= ' ' . (function_exists('util_format_lol_division') ? util_format_lol_division($acc['current_division'] ?? null) : '');
        } elseif ((int)($acc['current_rank'] ?? 0) >= 8 && !empty($acc['current_lp'])) {
            $rankLabel .= ' ' . (int)$acc['current_lp'] . 'LP';
        }
        $rankImgSrc = function_exists('util_rank_img') ? util_rank_img('lol','mini',$acc['current_rank'] ?? 0) : '';
    }

    $customerDisplay = '—';
    $customerProfileId = null;
    if ($sold || $refunded) {
        $customerProfileId = !empty($acc['client_id']) ? (int)$acc['client_id'] : (!empty($acc['customer_id']) ? (int)$acc['customer_id'] : null);
        $customerDisplay = $acc['customer'] ?? $acc['customer_username'] ?? $acc['buyer_username'] ?? $acc['buyer_name'] ?? $acc['client_username'] ?? $acc['username'] ?? null;
        if (!$customerDisplay && $customerProfileId) $customerDisplay = 'Guest#' . $customerProfileId;
        if (!$customerDisplay) $customerDisplay = '—';
    }
    $sellerId = (int)($acc['seller_id'] ?? 0);
    $sellerName = $acc['seller_username'] ?? $acc['seller_name'] ?? null;
    if (!$sellerName && $sellerId > 0) $sellerName = 'Seller #' . $sellerId;
    if (!$sellerName) $sellerName = '—';

    $searchStr = strtolower((string)($acc['id'] ?? '') . ' #' . ($acc['id'] ?? '') . ' ' . $gameLabel . ' ' . $game . ' ' . ($acc['title'] ?? '') . ' ' . ($acc['server'] ?? '') . ' ' . $rankLabel . ' ' . ($sellerName ?? '') . ' ' . $customerDisplay);

    $accountOrderRows[] = [
        'id' => (int)($acc['id'] ?? 0),
        'source' => 'seller',
        'sourceLabel' => 'Ranked Account',
        'status' => $status,
        'badgeCls' => $badgeCls,
        'active' => $active ? 1 : 0,
        'sold' => $sold ? 1 : 0,
        'refunded' => $refunded ? 1 : 0,
        'game' => $game,
        'gameLabel' => $gameLabel,
        'gameIcon' => $gameIconFor($game),
        'search' => $searchStr,
        'price' => $priceRaw,
        'priceText' => '€' . $formatCents($priceRaw),
        'earnings' => $earningsRaw,
        'earningsText' => '€' . $formatCents($earningsRaw),
        'date' => $createdAtTs,
        'dateText' => $createdAtFmt,
        'soldAt' => $soldAtTs,
        'soldAtText' => ($sold || $refunded) ? $soldAtFmt : '—',
        'href' => ADMN_URL . '/selling-account/' . (int)($acc['id'] ?? 0),
        'checkboxValue' => (string)(int)($acc['id'] ?? 0),
        'checkboxDisabled' => $sold ? 1 : 0,
        'checkboxTitle' => $sold ? 'Sold accounts cannot be deleted' : '',
        'avatarType' => 'rank',
        'avatarImg' => $rankImgSrc,
        'avatarFallback' => $gameLabel,
        'avatarFallbackClass' => 'al-game-avatar--' . $game,
        'accountName' => strtoupper((string)($acc['server'] ?? '')) . ' — ' . $rankLabel,
        'accountSub' => (string)($acc['title'] ?? ''),
        'login' => (string)($acc['login'] ?? ''),
        'password' => (string)($acc['password'] ?? ''),
        'buyerId' => $customerProfileId ? (int)$customerProfileId : 0,
        'buyerName' => (string)$customerDisplay,
        'sellerId' => $sellerId,
        'sellerName' => (string)$sellerName,
    ];
}

foreach ($smurfRows as $row) {
    $id = (int)($row['id'] ?? 0);
    $packageId = (int)($row['package_id'] ?? 0);
    $packageName = $row['package_name'] ?? ($packageId ? 'Package #' . $packageId : 'Smurf Account');
    $accountStatus = (int)($row['status'] ?? 0);
    $isSold = $accountStatus === 1 && !empty($row['client_id']);
    $isUnlisted = $accountStatus === 2;
    $isActive = !$isSold && !$isUnlisted;
    $status = $isSold ? 'Sold' : ($isUnlisted ? 'Unlisted' : 'Active');
    $badgeCls = $isSold ? 'al-badge--sold' : ($isUnlisted ? 'al-badge--unlisted' : 'al-badge--active');

    $soldPrice = $row['sold_price'] ?? null;
    $packagePrice = $row['package_price'] ?? null;
    $priceToShow = ($isSold && $soldPrice !== null && $soldPrice !== '') ? $soldPrice : $packagePrice;
    $priceRaw = (int)($priceToShow ?? 0);
    $clientId = (int)($row['client_id'] ?? 0);
    $clientUsername = $row['client_username'] ?? ($clientId ? ('Guest#' . $clientId) : '—');
    $adminId = (int)($row['admin_id'] ?? 0);
    $adminName = $row['uploaded_by_admin'] ?? ($adminId ? ('Admin #' . $adminId) : '—');
    [$soldAtTs, $soldAtFmt] = $fmtDate($row['sold_at'] ?? null);
    [$createdAtTs, $createdAtFmt] = $fmtDate($row['created_at'] ?? null);
    $smurfGameId = (int)($row['game_id'] ?? $row['package_game_id'] ?? 1);
    $smurfGame = ($smurfGameId === 2) ? 'val' : 'lol';
    $smurfGameLabel = ($smurfGame === 'val') ? 'VAL' : 'LoL';
    $adminNameKey = strtolower(trim((string)$adminName));
    $smurfEarningsRaw = 0;
    if ($isSold) {
        if ($adminId === 2 || $adminNameKey === 'ricardo') $smurfEarningsRaw = $priceRaw;
        elseif ($adminId === 51 || $adminNameKey === 'skrill') $smurfEarningsRaw = (int)round($priceRaw * 0.30);
    }
    $searchStr = strtolower('smurf account order ' . $id . ' ' . $smurfGameLabel . ' ' . $packageName . ' ' . $clientUsername . ' ' . $adminName . ' ' . ($row['login'] ?? ''));

    $accountOrderRows[] = [
        'id' => $id,
        'source' => 'smurf',
        'sourceLabel' => 'Smurf Account',
        'status' => $status,
        'badgeCls' => $badgeCls,
        'active' => $isActive ? 1 : 0,
        'sold' => $isSold ? 1 : 0,
        'refunded' => 0,
        'game' => $smurfGame,
        'gameLabel' => $smurfGameLabel,
        'gameIcon' => $gameIconFor($smurfGame),
        'search' => $searchStr,
        'price' => $priceRaw,
        'priceText' => $priceToShow !== null && $priceToShow !== '' ? ('€' . $formatCents($priceRaw)) : '—',
        'earnings' => $smurfEarningsRaw,
        'earningsText' => $smurfEarningsRaw > 0 ? ('€' . $formatCents($smurfEarningsRaw)) : '—',
        'date' => $createdAtTs,
        'dateText' => $createdAtFmt,
        'soldAt' => $soldAtTs,
        'soldAtText' => $isSold ? $soldAtFmt : '—',
        'href' => ADMN_URL . '/account/' . $id,
        'checkboxValue' => 'smurf-' . $id,
        'checkboxDisabled' => 1,
        'checkboxTitle' => 'Smurf accounts are managed through their account detail page',
        'avatarType' => 'smurf',
        'avatarImg' => '',
        'avatarFallback' => '',
        'avatarFallbackClass' => '',
        'accountName' => strtoupper((string)($row['package_server'] ?? '')) . ' — ' . $packageName,
        'accountSub' => (string)($row['data'] ?? ''),
        'login' => (string)($row['login'] ?? ''),
        'password' => (string)($row['password'] ?? ''),
        'buyerId' => $clientId,
        'buyerName' => (string)($isSold ? $clientUsername : '—'),
        'sellerId' => 0,
        'sellerName' => (string)$adminName,
    ];
}

// Normalize all values before they are sent to the virtual table.
// This prevents double encoded output like @blackmoza33@&amp;amp; in the list.
array_walk_recursive($accountOrderRows, static function (&$value) use ($alCleanText): void {
    if (is_string($value)) {
        $value = $alCleanText($value);
    }
});

?>

<?= $this->start('styles') ?>
<link rel="stylesheet" href="<?= ASSET_URL ?>/origin/dash/vendor/tom-select/dist/css/tom-select.bootstrap5.css">
<style>

/* Page should use the full admin content width on this view */
body.has-navbar-vertical-aside-show-xl main#content.main .content.container,
main#content.main .content.container,
.content.container:has(.al-page){
  max-width:none!important;
  width:100%!important;
  padding-left:12px!important;
  padding-right:12px!important;
}
.al-page{width:100%;max-width:none;margin:0 auto;}
/* ── Custom checkbox ── */
.al-chk{appearance:none;-webkit-appearance:none;width:17px;height:17px;border-radius:5px;border:1.5px solid rgba(255,255,255,.18);background:rgba(255,255,255,.06);cursor:pointer;flex-shrink:0;position:relative;transition:background .12s,border-color .12s;display:inline-block;vertical-align:middle;}
.al-chk:hover{border-color:rgba(109,92,255,.6);background:rgba(109,92,255,.12);}
.al-chk:checked{background:#6d5cff;border-color:#6d5cff;}
.al-chk:checked::after{content:'';position:absolute;left:4px;top:1.5px;width:5px;height:9px;border:2px solid #fff;border-top:0;border-left:0;transform:rotate(45deg);}
.al-chk:indeterminate{background:rgba(109,92,255,.4);border-color:rgba(109,92,255,.7);}
.al-chk:indeterminate::after{content:'';position:absolute;left:3px;top:6.5px;width:9px;height:2px;background:#fff;border-radius:1px;}
.al-chk:disabled{opacity:.3;cursor:not-allowed;}

/* ── Pills ── */
.al-pills{display:flex;gap:6px;flex-wrap:wrap;}
.al-pill{display:inline-flex;align-items:center;gap:.3rem;padding:5px 13px;border-radius:99px;font-size:.78rem;font-weight:800;cursor:pointer;border:1px solid rgba(255,255,255,.09);background:rgba(255,255,255,.04);color:rgba(255,255,255,.6);transition:background .12s,border-color .12s,color .12s;user-select:none;}
.al-pill:hover{background:rgba(255,255,255,.08);color:rgba(255,255,255,.85);}
.al-pill.active{background:rgba(109,92,255,.18);border-color:rgba(109,92,255,.45);color:#c4b5fd;}
.al-pill[data-status="Active"].active{background:rgba(74,222,128,.12);border-color:rgba(74,222,128,.30);color:#4ade80;}
.al-pill[data-status="Sold"].active{background:rgba(251,113,133,.12);border-color:rgba(251,113,133,.30);color:#fb7185;}
.al-pill[data-status="Unlisted"].active{background:rgba(250,204,21,.12);border-color:rgba(250,204,21,.35);color:#facc15;}
.al-pill[data-game="lol"].active{background:rgba(96,165,250,.13);border-color:rgba(96,165,250,.35);color:#93c5fd;}
.al-pill[data-game="val"].active{background:rgba(251,113,133,.13);border-color:rgba(251,113,133,.35);color:#fb7185;}

/* ── Search ── */
.al-search-wrap{position:relative;}
.al-search-wrap input{background:rgba(255,255,255,.04)!important;border:1px solid rgba(255,255,255,.09)!important;border-radius:10px!important;color:rgba(255,255,255,.85)!important;padding:7px 12px 7px 34px!important;font-size:.84rem!important;width:220px;transition:border-color .15s,box-shadow .15s;}
.al-search-wrap input:focus{border-color:rgba(109,92,255,.45)!important;box-shadow:0 0 0 3px rgba(109,92,255,.10)!important;outline:none!important;}
.al-search-wrap input::placeholder{color:rgba(255,255,255,.25)!important;}
.al-search-icon{position:absolute;left:10px;top:50%;transform:translateY(-50%);color:rgba(255,255,255,.35);font-size:.8rem;pointer-events:none;}

/* ── Table ── */
.al-table-wrap{border:1px solid rgba(255,255,255,.07);border-radius:20px;overflow-x:auto;overflow-y:visible;background:#25282a;box-shadow:0 4px 32px rgba(0,0,0,.28);position:relative;width:100%;}
.al-table{width:100%;min-width:1120px;border-collapse:collapse;border-radius:20px;overflow:hidden;display:table;table-layout:auto;}
.al-table thead tr{background:rgba(255,255,255,.03);border-bottom:1px solid rgba(255,255,255,.06);}
.al-table thead th{padding:11px 16px;font-size:.68rem;font-weight:900;color:rgba(255,255,255,.35);text-transform:uppercase;letter-spacing:.07em;white-space:nowrap;user-select:none;}
.al-table thead th.sortable{cursor:pointer;}
.al-table thead th.sortable:hover{color:rgba(255,255,255,.7);}
.al-table thead th .sort-icon{margin-left:4px;opacity:.35;font-size:.6rem;}
.al-table thead th.sort-asc .sort-icon,.al-table thead th.sort-desc .sort-icon{opacity:1;color:#c4b5fd;}
.al-table tbody .al-row{border-bottom:1px solid rgba(255,255,255,.04);transition:background .12s;cursor:pointer;}
.al-table tbody .al-row:last-child{border-bottom:none;}
.al-table tbody .al-row:hover{background:rgba(109,92,255,.08);}
.al-table tbody td{padding:13px 16px;vertical-align:middle;font-size:.85rem;color:rgba(255,255,255,.8);}

/* ── Cols ── */
.al-col-id{font-size:.72rem;font-weight:800;color:rgba(255,255,255,.25);font-variant-numeric:tabular-nums;}
.al-acc-wrap{display:flex;align-items:center;gap:11px;}
.al-acc-img{width:34px;height:34px;border-radius:9px;object-fit:contain;background:rgba(255,255,255,.04);padding:2px;flex-shrink:0;}
.al-game-avatar{width:34px;height:34px;border-radius:9px;display:inline-flex;align-items:center;justify-content:center;font-weight:900;font-size:.82rem;letter-spacing:.02em;flex-shrink:0;border:1px solid rgba(255,255,255,.10);}
.al-game-avatar--lol{background:rgba(96,165,250,.12);color:#93c5fd;}
.al-game-avatar--val{background:rgba(251,113,133,.12);color:#fb7185;}
.al-smurf-avatar{width:34px;height:34px;border-radius:9px;display:inline-flex;align-items:center;justify-content:center;flex-shrink:0;border:1px solid rgba(196,181,253,.28);background:rgba(196,181,253,.12);color:#c4b5fd;font-size:1rem;}
.al-smurf-avatar--small{width:30px;height:30px;border-radius:8px;font-size:.95rem;}
.al-acc-name{font-size:.88rem;font-weight:800;color:rgba(255,255,255,.9);line-height:1.2;}
.al-acc-sub{font-size:.74rem;color:rgba(255,255,255,.38);margin-top:1px;max-width:180px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;}
.al-col-price{font-size:.9rem;font-weight:800;color:rgba(255,255,255,.9);font-variant-numeric:tabular-nums;}
.al-col-earnings{font-size:.85rem;font-weight:700;color:#4ade80;font-variant-numeric:tabular-nums;}
.al-col-earnings--empty{color:rgba(255,255,255,.25)!important;}
.al-col-date{font-size:.78rem;color:rgba(255,255,255,.38);font-variant-numeric:tabular-nums;}
.al-game-icon{width:26px;height:26px;object-fit:contain;display:block;filter:drop-shadow(0 2px 4px rgba(0,0,0,.25));}
.al-game-cell{display:inline-flex;align-items:center;justify-content:center;width:30px;height:30px;border-radius:8px;background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.07);}
.al-filter-game-icon{width:16px;height:16px;object-fit:contain;display:inline-block;vertical-align:-3px;filter:drop-shadow(0 1px 3px rgba(0,0,0,.25));}

/* ── Status badges ── */
.al-badge{display:inline-flex;align-items:center;gap:.3rem;padding:4px 10px;border-radius:99px;font-size:.71rem;font-weight:800;white-space:nowrap;}
.al-badge--active{background:rgba(74,222,128,.12);border:1px solid rgba(74,222,128,.28);color:#4ade80;}
.al-badge--sold{background:rgba(251,113,133,.12);border:1px solid rgba(251,113,133,.28);color:#fb7185;}
.al-badge--unlisted{background:rgba(250,204,21,.12);border:1px solid rgba(250,204,21,.30);color:#facc15;}
.al-badge--refunded{background:rgba(148,163,184,.12);border:1px solid rgba(148,163,184,.25);color:#94a3b8;}
.al-badge--source{background:rgba(96,165,250,.12);border:1px solid rgba(96,165,250,.28);color:#93c5fd;}
.al-badge--smurf{background:rgba(196,181,253,.12);border:1px solid rgba(196,181,253,.28);color:#c4b5fd;}

/* ── Creds ── */
.al-creds-wrap{display:flex;flex-direction:column;gap:3px;}
.al-cred-row{display:inline-flex;align-items:center;gap:5px;font-size:.76rem;color:rgba(255,255,255,.5);}
.al-cred-row i{color:rgba(255,255,255,.25);font-size:.7rem;width:12px;text-align:center;flex-shrink:0;}
.al-cred-val{font-weight:700;color:rgba(255,255,255,.72);font-family:monospace;font-size:.77rem;max-width:130px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;transition:filter .2s;}
.al-cred-none{font-size:.74rem;color:rgba(255,255,255,.2);}
body.al-creds-hidden .al-cred-val{filter:blur(5px);user-select:none;}

/* ── User links ── */
.al-user-link{color:#c4b5fd;text-decoration:none;font-weight:700;font-size:.82rem;}
.al-user-link:hover{color:#fff;text-decoration:underline;}
.al-user-link--green{color:#4ade80;}
.al-user-link--green:hover{color:#86efac;}

/* ── Actions dropdown ── */
.al-actions-wrap{position:relative;display:inline-block;}
.al-actions-btn{width:32px;height:32px;border-radius:9px;border:1px solid rgba(255,255,255,.09);background:rgba(255,255,255,.04);color:rgba(255,255,255,.5);font-size:.8rem;cursor:pointer;display:inline-flex;align-items:center;justify-content:center;transition:background .12s,color .12s;}
.al-actions-btn:hover{background:rgba(255,255,255,.09);color:rgba(255,255,255,.9);}
.al-actions-btn.is-open{background:rgba(109,92,255,.18);border-color:rgba(109,92,255,.4);color:#c4b5fd;}
.al-actions-menu{display:none;position:fixed;min-width:190px;z-index:9999;background:#2a2d35;border:1px solid rgba(255,255,255,.1);border-radius:13px;padding:5px;box-shadow:0 8px 32px rgba(0,0,0,.6);}
.al-actions-menu.is-open{display:block;}
.al-action-item{display:flex;align-items:center;gap:9px;width:100%;padding:8px 11px;border-radius:8px;font-size:.8rem;font-weight:700;color:rgba(255,255,255,.72);background:none;border:none;cursor:pointer;text-decoration:none;text-align:left;transition:background .1s,color .1s;}
.al-action-item:hover{background:rgba(255,255,255,.06);color:#fff;}
.al-action-item i{width:14px;text-align:center;color:rgba(255,255,255,.3);font-size:.78rem;flex-shrink:0;}
.al-action-danger{color:#fb7185!important;}
.al-action-danger:hover{background:rgba(251,113,133,.08)!important;}
.al-action-danger i{color:#fb7185!important;}
.al-view-btn{display:inline-flex;align-items:center;justify-content:center;gap:8px;min-width:78px;padding:8px 14px;border-radius:12px;border:1px solid rgba(139,92,246,.55);background:linear-gradient(135deg,rgba(124,58,237,.95),rgba(59,130,246,.72));color:#fff!important;font-size:.78rem;font-weight:950;letter-spacing:.01em;text-decoration:none;white-space:nowrap;box-shadow:0 8px 22px rgba(0,0,0,.28),0 0 0 1px rgba(255,255,255,.05) inset;transition:transform .12s,filter .12s,box-shadow .12s;}
.al-view-btn:hover{transform:translateY(-1px);filter:brightness(1.08);box-shadow:0 10px 26px rgba(0,0,0,.34),0 0 0 1px rgba(255,255,255,.08) inset;color:#fff!important;text-decoration:none;}
.al-view-btn i{font-size:.82rem;color:#fff;}
.al-actions-wrap,.al-actions-btn,.al-actions-menu{display:none!important;}

/* ── Hero ── */
.al-hero{border-radius:20px;border:1px solid rgba(255,255,255,.07);background:#25282a;padding:18px 22px;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:14px;margin-bottom:14px;box-shadow:0 2px 20px rgba(0,0,0,.22);}
.al-hero-left{display:flex;align-items:center;gap:14px;}
.al-hero-icon{width:44px;height:44px;border-radius:13px;background:linear-gradient(135deg,rgba(109,92,255,.25),rgba(176,92,255,.15));border:1px solid rgba(109,92,255,.25);display:flex;align-items:center;justify-content:center;font-size:1.1rem;color:#c4b5fd;flex-shrink:0;}
.al-hero-title{font-size:1.1rem;font-weight:950;color:rgba(255,255,255,.92);margin:0;}
.al-hero-sub{font-size:.8rem;color:rgba(255,255,255,.4);margin:2px 0 0;}
.al-add-btn{display:inline-flex;align-items:center;gap:.5rem;background:linear-gradient(135deg,#6d5cff,#b05cff);border:none;border-radius:13px;padding:.6rem 1.4rem;font-weight:900;font-size:.9rem;color:#fff;cursor:pointer;transition:opacity .15s,transform .12s;text-decoration:none;}
.al-add-btn:hover{opacity:.88;transform:translateY(-1px);color:#fff;}

/* ── Toolbar ── */
.al-toolbar-card{border-radius:16px;border:1px solid rgba(255,255,255,.07);background:#25282a;padding:10px 14px;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:10px;margin-bottom:16px;box-shadow:0 2px 16px rgba(0,0,0,.18);}

/* ── Empty / Pagination ── */
.al-empty{text-align:center;padding:64px 24px;color:rgba(255,255,255,.35);}
.al-empty i{font-size:3rem;margin-bottom:12px;display:block;opacity:.3;}
.al-footer{display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:10px;padding:14px 0 0;}
.al-pg-btn{width:32px;height:32px;border-radius:8px;border:1px solid rgba(255,255,255,.09);background:rgba(255,255,255,.04);color:rgba(255,255,255,.7);font-size:.8rem;font-weight:700;display:inline-flex;align-items:center;justify-content:center;cursor:pointer;transition:background .12s;}
.al-pg-btn:hover:not(:disabled){background:rgba(255,255,255,.09);}
.al-pg-btn.al-pg-active{background:rgba(109,92,255,.25);border-color:rgba(109,92,255,.45);color:#c4b5fd;}
.al-pg-btn:disabled{opacity:.35;cursor:not-allowed;}

@media only screen and (max-width:1200px){.al-table{min-width:1120px;}}
</style>
<?= $this->end() ?>


<div class="al-page">

  <!-- Hero -->
  <div class="al-hero">
    <div class="al-hero-left">
      <div class="al-hero-icon"><i class="fa-duotone fa-helmet-battle"></i></div>
      <div>
        <h2 class="al-hero-title">Account Orders</h2>
        <p class="al-hero-sub"><?= $accountOrdersLimit > 0 ? 'Latest' : 'All' ?> <?= $totalAccountOrders ?> account order<?= $totalAccountOrders !== 1 ? 's' : '' ?> loaded, <?= count($rows) ?> ranked account<?= count($rows) !== 1 ? 's' : '' ?>, <?= count($smurfRows) ?> smurf account<?= count($smurfRows) !== 1 ? 's' : '' ?></p>
      </div>
    </div>
    <a class="al-add-btn" href="<?= ADMN_URL ?>/account-package/add">
      <i class="fa-solid fa-plus"></i> Add Account
    </a>
  </div>

  <!-- Toolbar -->
  <div class="al-toolbar-card">
    <div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap;flex:1;">
      <div class="al-pills" id="alGameFilters">
        <span class="al-pill active" data-game="all"><i class="fa-solid fa-gamepad-modern"></i> All Games</span>
        <span class="al-pill" data-game="lol"><img class="al-filter-game-icon" src="/public/assets/website/images/icons/league-of-legends.png" alt="LoL"> LoL</span>
        <span class="al-pill" data-game="val"><img class="al-filter-game-icon" src="/public/assets/website/images/icons/valorant.png" alt="VAL"> VAL</span>
      </div>
      <div class="al-pills" id="alStatusFilters">
        <span class="al-pill" data-status="all">All</span>
        <span class="al-pill" data-status="Active"><i class="fa-solid fa-circle" style="font-size:.45rem;"></i> Active</span>
        <span class="al-pill" data-status="Unlisted">Unlisted</span>
        <span class="al-pill active" data-status="Sold"><i class="fa-solid fa-check"></i> Sold</span>
        <span class="al-pill" data-status="Refunded">Refunded</span>
      </div>
      <div class="al-pills" id="alSourceFilters">
        <span class="al-pill active" data-source="all">All Accounts</span>
        <span class="al-pill" data-source="seller"><i class="fa-solid fa-trophy"></i> Ranked Account</span>
        <span class="al-pill" data-source="smurf"><i class="fa-solid fa-helmet-battle"></i> Smurf Account</span>
      </div>
      <button type="button" id="alBulkDeleteBtn"
              style="display:none;align-items:center;gap:.4rem;padding:6px 14px;border-radius:10px;background:rgba(251,113,133,.14);border:1px solid rgba(251,113,133,.28);color:#fb7185;font-size:.8rem;font-weight:800;cursor:pointer;">
        <i class="fa-duotone fa-trash"></i> Delete selected (<span id="alBulkCount">0</span>)
      </button>
    </div>
    <div class="al-search-wrap">
      <i class="fa-solid fa-magnifying-glass al-search-icon"></i>
      <input type="search" id="alSearch" placeholder="Search account orders…">
    </div>
  </div>

  <!-- Table -->
  <div class="al-table-wrap">
    <table class="al-table" id="alGrid">
      <thead>
        <tr>
          <th style="width:36px;padding:10px 8px;">
            <input type="checkbox" id="alChkAll" class="al-chk" aria-label="Select all">
          </th>
          <th class="sortable" data-col="id">ID <i class="fa-solid fa-sort sort-icon"></i></th>
          <th>Account</th>
          <th class="sortable" data-col="game" style="padding-left:8px;padding-right:8px;">Game <i class="fa-solid fa-sort sort-icon"></i></th>
          <th class="sortable" data-col="price">Price <i class="fa-solid fa-sort sort-icon"></i></th>
          <th class="sortable" data-col="earnings">Earnings <i class="fa-solid fa-sort sort-icon"></i></th>
          <th>Status</th>
          <th>
            <span style="display:inline-flex;align-items:center;gap:6px;">
              Credentials
              <button id="alCredsToggle"
                onclick="event.stopPropagation();(function(btn){var h=document.body.classList.toggle('al-creds-hidden');btn.querySelector('i').className=h?'fa-solid fa-eye':'fa-solid fa-eye-slash';})(this)"
                style="width:18px;height:18px;border-radius:5px;border:none;background:rgba(255,255,255,.08);color:rgba(255,255,255,.4);font-size:.62rem;cursor:pointer;display:inline-flex;align-items:center;justify-content:center;" title="Show/hide credentials">
                <i class="fa-solid fa-eye-slash"></i>
              </button>
            </span>
          </th>
          <?php if ($canViewAllAccounts): ?>
          <th>Buyer</th>
          <th>Seller / Uploaded By</th>
          <?php endif; ?>
          <th class="sortable" data-col="soldAt">Sold At <i class="fa-solid fa-sort sort-icon"></i></th>
          <th class="sortable" data-col="date">Created <i class="fa-solid fa-sort sort-icon"></i></th>
          <th class="text-end">Action</th>
        </tr>
      </thead>
      <tbody id="alTbody"></tbody>
    </table>
  </div>

  <!-- Footer / Pagination -->
  <div class="al-footer">
    <div style="font-size:.82rem;color:rgba(255,255,255,.4);">
      Showing <span id="alShowing">—</span> of <span id="alTotal">—</span>
    </div>
    <div style="display:flex;gap:5px;flex-wrap:wrap;" id="alPagination"></div>
  </div>

</div>


<?= $this->start('scripts') ?>
<script>
window.AL_ACCOUNT_ROWS = <?= json_encode($accountOrderRows, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?>;
window.AL_CAN_VIEW_ALL_ACCOUNTS = <?= $canViewAllAccounts ? 'true' : 'false' ?>;
window.AL_EMPTY_COLSPAN = <?= $canViewAllAccounts ? 13 : 11 ?>;

// ── Actions menu fallback, kept for old cached buttons ─────────────────
document.addEventListener('click', function(e) {
  if (!e.target.closest('.al-actions-wrap')) {
    document.querySelectorAll('.al-actions-menu.is-open').forEach(function(m) {
      m.classList.remove('is-open');
      if (m.previousElementSibling) m.previousElementSibling.classList.remove('is-open');
    });
  }
});
window.alToggleMenu = function(btn) {
  var menu = btn.nextElementSibling;
  if (!menu) return;
  var isOpen = menu.classList.contains('is-open');
  document.querySelectorAll('.al-actions-menu.is-open').forEach(function(m) {
    m.classList.remove('is-open');
    if (m.previousElementSibling) m.previousElementSibling.classList.remove('is-open');
  });
  if (!isOpen) {
    var rect = btn.getBoundingClientRect();
    menu.style.top  = (rect.bottom + 6) + 'px';
    menu.style.left = Math.max(8, rect.right - 200) + 'px';
    menu.classList.add('is-open');
    btn.classList.add('is-open');
  }
};

// ── Delete ────────────────────────────────────────────────
window.adminDeleteAccount = function(btn) {
  if (!confirm('Delete this account? This cannot be undone.')) return;
  btn.disabled = true;
  $.post('<?= AJAX_URL ?>', { action: 'admin_delete_selling_account', id: btn.getAttribute('data-id') }, function(resp) {
    var d = resp; try { if (typeof resp === 'string') d = JSON.parse(resp); } catch(e) {}
    if (d && d.sendToast && typeof create_toast === 'function') create_toast(d.sendToast.type, d.sendToast.title, d.sendToast.message);
    if (d && d.refreshPage) window.location.reload();
    else btn.disabled = false;
  }).fail(function() {
    if (typeof create_toast === 'function') create_toast('danger', 'Error', 'Could not delete account.');
    btn.disabled = false;
  });
};

// ── Bulk select ───────────────────────────────────────────
(function() {
  var selected = new Set();
  var $bulkBtn  = $('#alBulkDeleteBtn');
  var $bulkCnt  = $('#alBulkCount');
  var $chkAll   = $('#alChkAll');

  function updateUI() {
    var n = selected.size;
    $bulkCnt.text(n);
    $bulkBtn.css('display', n > 0 ? 'inline-flex' : 'none');
    var $rows = $('.al-row-chk:not(:disabled)');
    var total = $rows.length;
    var checked = $rows.filter(function() { return selected.has(String(this.value)); }).length;
    if (total === 0 || checked === 0) $chkAll.prop('checked', false).prop('indeterminate', false);
    else if (checked === total) $chkAll.prop('checked', true).prop('indeterminate', false);
    else $chkAll.prop('checked', false).prop('indeterminate', true);
  }
  $(document).on('change', '.al-row-chk', function(e) {
    e.stopPropagation();
    if (this.checked) selected.add(String(this.value));
    else selected.delete(String(this.value));
    updateUI();
  });
  $chkAll.on('change', function() {
    var shouldCheck = this.checked;
    $('.al-row-chk:not(:disabled)').each(function() {
      var id = String(this.value);
      this.checked = shouldCheck;
      if (shouldCheck) selected.add(id); else selected.delete(id);
    });
    updateUI();
  });
  $bulkBtn.on('click', function() {
    if (!selected.size) return;
    var ids = Array.from(selected).map(Number).filter(isFinite);
    if (!ids.length || !confirm('Delete ' + ids.length + ' account(s)? This cannot be undone.')) return;
    $bulkBtn.prop('disabled', true);
    $.ajax({ type: 'post', url: '<?= AJAX_URL ?>', data: { action: 'admin_bulk_delete_selling_accounts', ids: ids }, dataType: 'json',
      success: function(d) {
        if (d && d.sendToast && typeof create_toast === 'function') create_toast(d.sendToast.type, d.sendToast.title, d.sendToast.message);
        selected.clear(); window.location.reload();
      },
      error: function() {
        if (typeof create_toast === 'function') create_toast('danger', 'Error', 'Could not delete accounts.');
        $bulkBtn.prop('disabled', false);
      }
    });
  });

  window.alIsSelected = function(value) { return selected.has(String(value)); };
  window.alSyncBulk = updateUI;
  updateUI();
})();

// ── Virtual table render, filtering, sorting, pagination ───────────────
(function() {
  var PER_PAGE = 25;
  var rows = Array.isArray(window.AL_ACCOUNT_ROWS) ? window.AL_ACCOUNT_ROWS : [];
  // Account Orders zeigt standardmaessig nur verkaufte Accounts.
  var filter = 'Sold';
  var gameFilter = 'all';
  var sourceFilter = 'all';
  var search = '';
  var page = 1;
  var sortCol = 'soldAt';
  var sortDir = 'desc';

  var tbody = document.getElementById('alTbody');
  var showEl = document.getElementById('alShowing');
  var totEl = document.getElementById('alTotal');
  var pageEl = document.getElementById('alPagination');
  var srchEl = document.getElementById('alSearch');
  var pills = document.querySelectorAll('#alStatusFilters .al-pill');
  var gamePills = document.querySelectorAll('#alGameFilters .al-pill');
  var sourcePills = document.querySelectorAll('#alSourceFilters .al-pill');
  var ths = document.querySelectorAll('.al-table thead th.sortable');
  var canViewAll = !!window.AL_CAN_VIEW_ALL_ACCOUNTS;

  function decodeEntities(v) {
    var text = String(v == null ? '' : v);
    for (var i = 0; i < 4; i++) {
      var el = document.createElement('textarea');
      el.innerHTML = text;
      var decoded = el.value;
      if (decoded === text) break;
      text = decoded;
    }
    return text;
  }
  function esc(v) {
    return decodeEntities(v).replace(/[&<>'"]/g, function(c) {
      return {'&':'&amp;','<':'&lt;','>':'&gt;',"'":'&#039;','"':'&quot;'}[c];
    });
  }
  function attr(v) { return esc(v); }
  function getSorted(arr) {
    return arr.slice().sort(function(a, b) {
      var av = a[sortCol] == null ? '' : a[sortCol];
      var bv = b[sortCol] == null ? '' : b[sortCol];
      var an = parseFloat(av), bn = parseFloat(bv);
      var cmp = (!isFinite(an) || !isFinite(bn)) ? String(av).localeCompare(String(bv), undefined, {numeric:true}) : an - bn;
      return sortDir === 'asc' ? cmp : -cmp;
    });
  }
  function getFiltered() {
    return rows.filter(function(r) {
      return (filter === 'all' || r.status === filter)
        && (gameFilter === 'all' || r.game === gameFilter)
        && (sourceFilter === 'all' || r.source === sourceFilter)
        && (!search || String(r.search || '').indexOf(search) !== -1);
    });
  }
  function statusIcon(r) {
    if (r.sold || r.refunded) return '<i class="fa-solid fa-check"></i>';
    if (r.active) return '<i class="fa-solid fa-circle" style="font-size:.4rem;"></i>';
    return '<i class="fa-solid fa-eye-slash"></i>';
  }
  function rowHtml(r) {
    var checked = window.alIsSelected && window.alIsSelected(r.checkboxValue) ? ' checked' : '';
    var disabled = r.checkboxDisabled ? ' disabled' : '';
    var title = r.checkboxTitle ? ' title="' + attr(r.checkboxTitle) + '"' : '';
    var avatar = '';
    if (r.avatarType === 'smurf') {
      avatar = '<span class="al-smurf-avatar"><i class="fa-solid fa-helmet-battle"></i></span>';
    } else {
      avatar = '<img class="al-acc-img" src="' + attr(r.avatarImg) + '" alt="' + attr(r.accountName) + '" onerror="this.style.display=\'none\';this.nextElementSibling.style.display=\'inline-flex\';">'
        + '<span class="al-game-avatar ' + attr(r.avatarFallbackClass) + '" style="display:none;">' + esc(r.avatarFallback) + '</span>';
    }
    var sub = r.accountSub ? '<div class="al-acc-sub" title="' + attr(r.accountSub) + '">' + esc(r.accountSub) + '</div>' : '';
    var creds = '';
    if (r.login || r.password) {
      creds = '<div class="al-creds-wrap">'
        + (r.login ? '<div class="al-cred-row"><i class="fa-solid fa-user"></i><span class="al-cred-val">' + esc(r.login) + '</span></div>' : '')
        + (r.password ? '<div class="al-cred-row"><i class="fa-solid fa-key"></i><span class="al-cred-val">' + esc(r.password) + '</span></div>' : '')
        + '</div>';
    } else {
      creds = '<span class="al-cred-none">—</span>';
    }
    var buyerSeller = '';
    if (canViewAll) {
      var buyer = r.buyerId && r.buyerName !== '—'
        ? '<a class="al-user-link al-user-link--green" href="<?= ADMN_URL ?>/client/' + Number(r.buyerId) + '" title="Open client #' + Number(r.buyerId) + '">' + esc(r.buyerName) + '</a>'
        : '<span style="color:rgba(255,255,255,.2);">—</span>';
      var seller = '';
      if (r.source === 'seller' && r.sellerId && r.sellerName !== '—') {
        seller = '<a class="al-user-link" href="<?= ADMN_URL ?>/seller/' + Number(r.sellerId) + '/profile">' + esc(r.sellerName) + '</a>';
      } else if (r.sellerName && r.sellerName !== '—') {
        seller = '<span class="al-user-link">' + esc(r.sellerName) + '</span>';
      } else {
        seller = '<span style="color:rgba(255,255,255,.2);">—</span>';
      }
      buyerSeller = '<td onclick="event.stopPropagation()">' + buyer + '</td><td onclick="event.stopPropagation()">' + seller + '</td>';
    }
    var earningsEmpty = r.earnings > 0 ? '' : ' al-col-earnings--empty';
    return '<tr class="al-row" data-id="' + Number(r.id) + '" onclick="window.location=\'' + attr(r.href) + '\'">'
      + '<td style="padding:10px 8px;vertical-align:middle;" onclick="event.stopPropagation()"><input type="checkbox" class="al-row-chk al-chk" value="' + attr(r.checkboxValue) + '"' + disabled + checked + title + '></td>'
      + '<td><span class="al-col-id">#' + Number(r.id) + '</span></td>'
      + '<td><div class="al-acc-wrap">' + avatar + '<div><div class="al-acc-name">' + esc(r.accountName) + '</div>' + sub + '</div></div></td>'
      + '<td><span class="al-game-cell" title="' + attr(r.gameLabel) + '"><img class="al-game-icon" src="' + attr(r.gameIcon) + '" alt="' + attr(r.gameLabel) + '"></span></td>'
      + '<td><span class="al-col-price">' + esc(r.priceText) + '</span></td>'
      + '<td><span class="al-col-earnings' + earningsEmpty + '">' + esc(r.earningsText) + '</span></td>'
      + '<td><span class="al-badge ' + attr(r.badgeCls) + '">' + statusIcon(r) + ' ' + esc(r.status) + '</span></td>'
      + '<td onclick="event.stopPropagation()">' + creds + '</td>'
      + buyerSeller
      + '<td><span class="al-col-date">' + esc(r.soldAtText) + '</span></td>'
      + '<td><span class="al-col-date">' + esc(r.dateText) + '</span></td>'
      + '<td class="text-end" onclick="event.stopPropagation()"><a class="al-view-btn" href="' + attr(r.href) + '"><i class="fa-solid fa-eye"></i> View</a></td>'
      + '</tr>';
  }
  function emptyHtml() {
    return '<tr><td colspan="' + Number(window.AL_EMPTY_COLSPAN || 13) + '"><div class="al-empty"><i class="fa-duotone fa-helmet-battle"></i><div style="font-weight:900;font-size:1rem;color:rgba(255,255,255,.6);margin-bottom:6px;">No accounts found</div></div></td></tr>';
  }
  function render() {
    var filtered = getSorted(getFiltered());
    var total = filtered.length;
    var pages = Math.max(1, Math.ceil(total / PER_PAGE));
    if (page > pages) page = pages;
    var start = (page - 1) * PER_PAGE;
    var end = start + PER_PAGE;
    var pageRows = filtered.slice(start, end);

    if (tbody) tbody.innerHTML = pageRows.length ? pageRows.map(rowHtml).join('') : emptyHtml();
    if (showEl) showEl.textContent = total > 0 ? (start + 1) + '–' + Math.min(end, total) : '0';
    if (totEl) totEl.textContent = total;

    ths.forEach(function(th) {
      th.classList.remove('sort-asc', 'sort-desc');
      if (th.dataset.col === sortCol) th.classList.add('sort-' + sortDir);
    });
    if (typeof window.alSyncBulk === 'function') window.alSyncBulk();

    if (!pageEl) return;
    pageEl.innerHTML = '';
    if (pages <= 1) return;
    function btn(label, p, disabled, active) {
      var b = document.createElement('button');
      b.className = 'al-pg-btn' + (active ? ' al-pg-active' : '');
      b.innerHTML = label;
      b.disabled = !!disabled;
      if (!disabled) b.addEventListener('click', function() { page = p; render(); });
      return b;
    }
    pageEl.appendChild(btn('<i class="fa-solid fa-chevron-left"></i>', page - 1, page === 1, false));
    for (var i = 1; i <= pages; i++) {
      if (pages > 7 && i > 2 && i < pages - 1 && Math.abs(i - page) > 1) {
        if (i === 3 || i === pages - 2) {
          var d = document.createElement('span');
          d.style.cssText = 'color:rgba(255,255,255,.3);padding:0 4px;line-height:32px;';
          d.textContent = '…';
          pageEl.appendChild(d);
        }
        continue;
      }
      pageEl.appendChild(btn(i, i, false, i === page));
    }
    pageEl.appendChild(btn('<i class="fa-solid fa-chevron-right"></i>', page + 1, page === pages, false));
  }
  gamePills.forEach(function(p) {
    p.addEventListener('click', function() {
      gamePills.forEach(function(x) { x.classList.remove('active'); });
      p.classList.add('active');
      gameFilter = p.dataset.game || 'all';
      page = 1;
      render();
    });
  });
  sourcePills.forEach(function(p) {
    p.addEventListener('click', function() {
      sourcePills.forEach(function(x) { x.classList.remove('active'); });
      p.classList.add('active');
      sourceFilter = p.dataset.source || 'all';
      page = 1;
      render();
    });
  });
  pills.forEach(function(p) {
    p.addEventListener('click', function() {
      pills.forEach(function(x) { x.classList.remove('active'); });
      p.classList.add('active');
      filter = p.dataset.status || 'all';
      page = 1;
      render();
    });
  });
  ths.forEach(function(th) {
    th.addEventListener('click', function() {
      var col = th.dataset.col;
      if (sortCol === col) sortDir = sortDir === 'asc' ? 'desc' : 'asc';
      else { sortCol = col; sortDir = 'desc'; }
      page = 1;
      render();
    });
  });
  if (srchEl) srchEl.addEventListener('input', function() {
    search = srchEl.value.trim().toLowerCase();
    page = 1;
    render();
  });
  render();
})();
</script>
<?= $this->end() ?>
