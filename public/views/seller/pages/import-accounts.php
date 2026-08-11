<?= $this->layout('seller/layouts/main', ['meta' => $meta ?? ['title' => 'Import Accounts | Seller Area | LoLBoost.gg']]) ?>
<?php
$import_batches = $import_batches ?? [];
$selected_batch = $selected_batch ?? null;
$import_rows = $import_rows ?? [];
$gallery_lookup = $gallery_lookup ?? [];

$sample = json_decode(<<<'JSON'
{
  "accounts": [
    {
      "external_id": "REF-EUW-SAMPLE-001",
      "title": "EUW Diamond II Account | 79 LP | 60% WR | 50K BE",
      "slug": "euw-diamond-ii-account-sample-001",
      "price": 4999,
      "rank": 7,
      "rank_label": "Diamond",
      "current_rank": 7,
      "current_division": 2,
      "current_lp": 79,
      "flex_rank": 5,
      "flex_division": 1,
      "flex_lp": 12,
      "previous_rank": 6,
      "previous_division": 1,
      "previous_lp": 0,
      "server": "euw",
      "level_up_method": "by_hand",
      "level": 30,
      "blue_essence": 50000,
      "riot_points": 0,
      "winrate_percent": 60,
      "champion_count": 40,
      "skin_count": 3,
      "roles": "MidLane|Jungle",
      "login": "account-login",
      "password": "account-password",
      "email": "mail@example.com",
      "email_password": "mail-password",
      "in_game_name": "SummonerName#EUW",
      "2fa": 0,
      "delivery_instructions": "Change the Riot password and email after purchase. Full email access is included.",
      "description": "Account details shown to the buyer.",
      "active": 1
    }
  ]
}
JSON, true);
$sampleUniqueSuffix = date('YmdHis') . '-' . strtoupper(substr(bin2hex(random_bytes(3)), 0, 6));
if (isset($sample['accounts']) && is_array($sample['accounts'])) {
  foreach ($sample['accounts'] as $sampleIndex => &$sampleAccount) {
    $sampleNo = str_pad((string)($sampleIndex + 1), 3, '0', STR_PAD_LEFT);
    $sampleAccount['external_id'] = 'REF-EUW-' . $sampleUniqueSuffix . '-' . $sampleNo;
    $sampleAccount['slug'] = 'euw-diamond-ii-account-' . strtolower($sampleUniqueSuffix) . '-' . $sampleNo;
  }
  unset($sampleAccount);
}
$sampleJson = json_encode($sample, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

$currentRows = [];
foreach ($import_rows as $row) {
  $d = json_decode((string)($row['row_data'] ?? '{}'), true);
  if (is_array($d)) { $currentRows[] = $d; }
}
$currentRowsJson = json_encode(['accounts' => $currentRows], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

if (!function_exists('lb_import_status_badge')) {
function lb_import_status_badge($status) {
  $status = strtolower((string)$status);
  $label = $status !== '' ? $status : 'draft';
  $cls = 'al-badge--unlisted';
  if (in_array($status, ['created', 'updated', 'completed'], true)) { $cls = 'al-badge--active'; $label = $status === 'completed' ? 'Completed' : ucfirst($status); }
  elseif (in_array($status, ['failed', 'completed_with_errors'], true)) { $cls = 'al-badge--sold'; $label = $status === 'failed' ? 'Failed' : 'Completed (errors)'; }
  elseif ($status === 'skipped') { $cls = 'al-badge--unlisted'; $label = 'Skipped'; }
  elseif (in_array($status, ['draft', 'review'], true)) { $cls = 'al-badge--unlisted'; $label = $status === 'review' ? 'Review' : 'Draft'; }
  return '<span class="al-badge ' . $cls . '">' . htmlspecialchars($label, ENT_QUOTES, 'UTF-8') . '</span>';
}
}

if (!function_exists('lb_field')) {
function lb_field($data, $key) {
  switch ($key) {
    case 'login': return (string)($data['login'] ?? ($data['credentials']['login'] ?? ''));
    case 'password': return (string)($data['password'] ?? ($data['credentials']['password'] ?? ''));
    case 'email': return (string)($data['email'] ?? ($data['email_login'] ?? ($data['credentials']['email_login'] ?? '')));
    case 'email_login': return (string)($data['email_login'] ?? ($data['credentials']['email_login'] ?? ($data['email'] ?? '')));
    case 'email_password': return (string)($data['email_password'] ?? ($data['credentials']['email_password'] ?? ''));
    case 'rank': return (string)($data['rank'] ?? ($data['current_rank'] ?? ''));
    case 'division': return (string)($data['division'] ?? ($data['current_division'] ?? ''));
    case 'price':
      $p = $data['price'] ?? '';
      if ($p === '' || $p === null) return '';
      if (is_string($p) && strpos($p, '.') !== false) return $p;
      return number_format(((float)$p) / 100, 2, '.', '');
    default:
      $v = $data[$key] ?? '';
      if ($v === null) return '';
      if (is_array($v) || is_object($v)) return json_encode($v, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
      return (string)$v;
  }
}
}

$gameIcons = [
  'lol' => '/public/assets/website/images/icons/league-of-legends.png',
  'val' => '/public/assets/website/images/icons/valorant.png',
  'tft' => '/public/assets/website/images/icons/teamfight-tactics.png',
];
$gameLabels = ['lol' => 'League of Legends', 'val' => 'Valorant', 'tft' => 'Teamfight Tactics'];

$listing_games = $listing_games ?? [];
foreach ($listing_games as $listingGame) {
  $gameSlug = strtolower((string)($listingGame['slug'] ?? $listingGame['game_slug'] ?? $listingGame['code'] ?? ''));
  if ($gameSlug === '') continue;
  $gameLabels[$gameSlug] = (string)($listingGame['name'] ?? $listingGame['title'] ?? strtoupper($gameSlug));
  $gameIcon = (string)($listingGame['icon'] ?? $listingGame['icon_url'] ?? $listingGame['image'] ?? $listingGame['image_url'] ?? '');
  if ($gameIcon !== '') $gameIcons[$gameSlug] = $gameIcon;
}
$iaDefaultGame = array_key_exists('lol', $gameLabels) ? 'lol' : (string)(array_key_first($gameLabels) ?? 'lol');

$ia_rank_options = [
  0 => 'Unranked',
  1 => 'Iron',
  2 => 'Bronze',
  3 => 'Silver',
  4 => 'Gold',
  5 => 'Platinum',
  6 => 'Emerald',
  7 => 'Diamond',
  8 => 'Master',
  9 => 'Grandmaster',
  10 => 'Challenger',
];

$ia_division_options = [
  1 => 'I',
  2 => 'II',
  3 => 'III',
  4 => 'IV',
];

$ia_server_options = [
  'euw' => 'Europe West',
  'eune' => 'Europe Nordic & East',
  'na' => 'North America',
  'kr' => 'Korea',
  'br' => 'Brazil',
  'lan' => 'Latin America North',
  'las' => 'Latin America South',
  'oce' => 'Oceania',
  'tr' => 'Turkey',
  'ru' => 'Russia',
  'jp' => 'Japan',
];

$ia_level_method_options = [
  'by_hand' => 'Hand leveled',
  'botted' => 'Botted',
  'unknown' => 'Unknown',
];

$ia_delivery_options = [
  'instant' => 'Instant',
  'manual' => 'Manual',
];

$ia_bool_options = [
  '0' => 'No',
  '1' => 'Yes',
];

$ia_status_options = [
  'draft' => 'Draft',
  'pending' => 'Pending',
  'listed' => 'Listed',
  'archived' => 'Archived',
];

$ia_current_game = strtolower((string)($selected_batch['game'] ?? 'lol'));

// Relevant selling account fields for reviewing imports. The game is selected when creating the import batch.
$ia_main_columns = [
  ['external_id', 'External ID', 'text', 140],
  ['title', 'Title', 'text', 280],
  ['slug', 'Slug', 'text', 240],
  ['price', 'Price (€)', 'text', 95],

  ['current_rank', 'Current Tier', 'select_rank', 150],
  ['current_division', 'Current Division', 'select_division', 150],
  ['current_lp', 'Current LP', 'number', 100],

  ['flex_rank', 'Flex Tier', 'select_rank', 150],
  ['flex_division', 'Flex Division', 'select_division', 150],
  ['flex_lp', 'Flex LP', 'number', 95],

  ['previous_rank', 'Previous Tier', 'select_rank', 150],
  ['previous_division', 'Previous Division', 'select_division', 160],
  ['previous_lp', 'Previous LP', 'number', 110],

  ['server', 'Server', 'select_server', 170],
  ['level_up_method', 'Level Method', 'select_level_method', 150],
  ['level', 'Level', 'number', 85],
  ['blue_essence', 'Blue Essence', 'number', 130],
  ['riot_points', 'Riot Points', 'number', 120],
  ['winrate_percent', 'Winrate %', 'number', 120],

  ['champion_count', 'Champion Count', 'number', 145],
  ['skin_count', 'Skin Count', 'number', 115],
  ['roles', 'Roles', 'text', 170],
  ['login', 'Login', 'text', 135],
  ['password', 'Password', 'text', 135],
  ['email', 'Email', 'text', 180],
  ['email_password', 'Email Password', 'text', 160],
  ['in_game_name', 'In-Game Name', 'text', 170],
  ['2fa', '2FA', 'select_bool', 90],
  ['delivery_instructions', 'Delivery Instructions', 'text', 300],
  ['description', 'Description', 'text', 300],
  ['active', 'Active', 'select_bool', 95],
  ['status', 'Listing Status', 'select_status', 140],
];
if ($ia_current_game === 'val') {
  $insertAfter = 0;
  foreach ($ia_main_columns as $i => $col) {
    if ($col[0] === 'skin_count') { $insertAfter = $i + 1; break; }
  }
  array_splice($ia_main_columns, $insertAfter, 0, [['val_agent_count', 'Valorant Agents', 'number', 145]]);
}

// Kept for compatibility, all relevant fields are now visible in the main table.
$ia_detail_fields = [];
?>

<?= $this->start('styles') ?>
<style>
/* ── Reused design language from My Accounts ── */
.al-page .card { background:var(--bs-card-bg)!important;border:var(--bs-card-border-width) solid var(--bs-card-border-color)!important;border-radius:22px!important;box-shadow:none!important; }
.al-page .card::before { display:none!important; }
.al-add-btn { display:inline-flex;align-items:center;gap:.5rem;background:linear-gradient(135deg,#6d5cff,#b05cff);border:none;border-radius:13px;padding:.6rem 1.4rem;font-weight:900;font-size:.9rem;color:#fff;cursor:pointer;transition:opacity .15s,transform .12s;text-decoration:none; }
.al-add-btn:hover { opacity:.88;transform:translateY(-1px);color:#fff; }
.al-add-btn:disabled { opacity:.5;cursor:not-allowed;transform:none; }

.al-table-wrap { border:1px solid rgba(255,255,255,.07);border-radius:20px;overflow:auto;background:#25282a;box-shadow:0 4px 32px rgba(0,0,0,.28);position:relative; }
.al-table { width:100%;border-collapse:collapse;border-radius:20px;display:table; }
.al-table thead tr { background:rgba(255,255,255,.03);border-bottom:1px solid rgba(255,255,255,.06); }
.al-table thead th { padding:11px 14px;font-size:.66rem;font-weight:900;color:rgba(255,255,255,.35);text-transform:uppercase;letter-spacing:.06em;white-space:nowrap;user-select:none;position:sticky;top:0;background:#25282a;z-index:2; }
.al-table tbody .al-row { border-bottom:1px solid rgba(255,255,255,.04);transition:background .12s; }
.al-table tbody .al-row:last-child { border-bottom:none; }
.al-table tbody .al-row:hover { background:rgba(109,92,255,.05); }
.al-table tbody td { padding:9px 10px;vertical-align:middle;font-size:.85rem;color:rgba(255,255,255,.8); }
.al-col-id { font-size:.72rem;font-weight:800;color:rgba(255,255,255,.25);font-variant-numeric:tabular-nums; }
.al-acc-wrap { display:flex;align-items:center;gap:11px; }
.al-acc-img { width:34px;height:34px;border-radius:9px;object-fit:contain;background:rgba(255,255,255,.04);padding:2px;flex-shrink:0;display:flex;align-items:center;justify-content:center;color:#c4b5fd;font-size:.85rem; }
.al-acc-name { font-size:.88rem;font-weight:800;color:rgba(255,255,255,.9);line-height:1.2;max-width:300px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap; }
.al-acc-sub { font-size:.74rem;color:rgba(255,255,255,.38);margin-top:1px; }
.al-badge { display:inline-flex;align-items:center;gap:.3rem;padding:4px 10px;border-radius:99px;font-size:.71rem;font-weight:800;white-space:nowrap;text-transform:capitalize; }
.al-badge--active { background:rgba(74,222,128,.12);border:1px solid rgba(74,222,128,.28);color:#4ade80; }
.al-badge--sold { background:rgba(251,113,133,.12);border:1px solid rgba(251,113,133,.28);color:#fb7185; }
.al-badge--unlisted { background:rgba(250,204,21,.12);border:1px solid rgba(250,204,21,.35);color:#facc15; }
.al-view-btn { display:inline-flex;align-items:center;gap:.35rem;padding:7px 14px;border-radius:9px;font-size:.79rem;font-weight:800;background:rgba(109,92,255,.15);border:1px solid rgba(109,92,255,.28);color:#c4b5fd;transition:background .12s,border-color .12s;text-decoration:none;white-space:nowrap;cursor:pointer; }
.al-view-btn:hover { background:rgba(109,92,255,.28);border-color:rgba(109,92,255,.55);color:#fff; }
.al-view-btn.is-sm { padding:5px 10px;font-size:.73rem; }
.al-col-date { font-size:.78rem;color:rgba(255,255,255,.38);font-variant-numeric:tabular-nums; }
.al-actions-wrap { position:relative;display:inline-block; }
.al-actions-btn { width:32px;height:32px;border-radius:9px;border:1px solid rgba(255,255,255,.09);background:rgba(255,255,255,.04);color:rgba(255,255,255,.5);font-size:.8rem;cursor:pointer;display:inline-flex;align-items:center;justify-content:center;transition:background .12s,color .12s; }
.al-actions-btn:hover { background:rgba(255,255,255,.09);color:rgba(255,255,255,.9); }
.al-actions-btn.is-open { background:rgba(109,92,255,.18);border-color:rgba(109,92,255,.4);color:#c4b5fd; }
.al-actions-menu { display:none;position:fixed;min-width:200px;z-index:9999;background:#2a2d35;border:1px solid rgba(255,255,255,.1);border-radius:13px;padding:5px;box-shadow:0 8px 32px rgba(0,0,0,.6);animation:alMenuIn .12s ease;transform-origin:top right; }
.al-actions-menu.is-open { display:block; }
@keyframes alMenuIn { from{opacity:0;transform:scale(.97)} to{opacity:1;transform:scale(1)} }
.al-action-item { display:flex;align-items:center;gap:9px;width:100%;padding:8px 11px;border-radius:8px;font-size:.8rem;font-weight:700;color:rgba(255,255,255,.72);background:none;border:none;cursor:pointer;text-decoration:none;text-align:left;transition:background .1s,color .1s; }
.al-action-item:hover { background:rgba(255,255,255,.06);color:#fff; }
.al-action-item i { width:14px;text-align:center;color:rgba(255,255,255,.3);font-size:.78rem;flex-shrink:0; }
.al-action-item:hover i { color:rgba(255,255,255,.6); }
.al-action-item kbd { margin-left:auto;font-size:.66rem;font-weight:800;color:rgba(255,255,255,.3);background:rgba(255,255,255,.06);border:1px solid rgba(255,255,255,.08);border-radius:5px;padding:1px 5px; }
.al-action-danger { color:#fb7185 !important; }
.al-action-danger:hover { background:rgba(251,113,133,.08) !important; }
.al-action-danger i { color:#fb7185 !important; }
.al-action-divider { height:1px;background:rgba(255,255,255,.06);margin:4px 0; }
.al-empty { text-align:center;padding:64px 24px;color:rgba(255,255,255,.35); }
.al-empty i { font-size:3rem;margin-bottom:12px;display:block;opacity:.3; }
.al-hero { border-radius:20px;border:1px solid rgba(255,255,255,.07);background:#25282a;padding:20px 24px;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:14px;margin-bottom:14px;box-shadow:0 2px 20px rgba(0,0,0,.22); }
.al-hero-left { display:flex;align-items:center;gap:14px; }
.al-hero-icon { width:44px;height:44px;border-radius:13px;background:linear-gradient(135deg,rgba(109,92,255,.25),rgba(176,92,255,.15));border:1px solid rgba(109,92,255,.25);display:flex;align-items:center;justify-content:center;font-size:1.1rem;color:#c4b5fd;flex-shrink:0; }
.al-hero-title { font-size:1.1rem;font-weight:950;color:rgba(255,255,255,.92);margin:0; }
.al-hero-sub { font-size:.8rem;color:rgba(255,255,255,.4);margin:2px 0 0; }
.al-toolbar-card { border-radius:16px;border:1px solid rgba(255,255,255,.07);background:#25282a;padding:12px 16px;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:10px;margin-bottom:16px;box-shadow:0 2px 16px rgba(0,0,0,.18); }

/* ── Compact single-row header bar (detail view) ── */
.ia-slimbar { border-radius:14px;border:1px solid rgba(255,255,255,.07);background:#25282a;padding:9px 14px;display:flex;align-items:center;justify-content:space-between;gap:14px;flex-wrap:wrap;margin-bottom:12px;box-shadow:0 2px 16px rgba(0,0,0,.18); }
.ia-slimbar-left { display:flex;align-items:center;gap:10px;min-width:0; }
.ia-back-btn { width:32px;height:32px;border-radius:9px;border:1px solid rgba(255,255,255,.09);background:rgba(255,255,255,.04);color:rgba(255,255,255,.6);display:flex;align-items:center;justify-content:center;flex-shrink:0;text-decoration:none;transition:background .12s,color .12s; }
.ia-back-btn:hover { background:rgba(255,255,255,.09);color:#fff; }
.ia-slimbar-title { display:flex;flex-direction:column;min-width:0;line-height:1.25; }
.ia-slimbar-title strong { font-size:.92rem;font-weight:900;color:#fff;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;max-width:46vw; }
.ia-slimbar-title span { font-size:.74rem;color:rgba(255,255,255,.42); }
.ia-slimbar-right { display:flex;align-items:center;gap:8px;flex-wrap:wrap; }
.ia-slim-btn { display:inline-flex;align-items:center;gap:6px;padding:7px 12px;border-radius:9px;font-size:.79rem;font-weight:800;background:rgba(109,92,255,.15);border:1px solid rgba(109,92,255,.28);color:#c4b5fd;cursor:pointer;white-space:nowrap; }
.ia-slim-btn:hover { background:rgba(109,92,255,.28);border-color:rgba(109,92,255,.55);color:#fff; }
.ia-slim-btn:disabled { opacity:.4;cursor:not-allowed; }
.ia-slim-btn kbd { margin-left:4px;font-size:.64rem;font-weight:800;color:rgba(255,255,255,.4);background:rgba(255,255,255,.07);border:1px solid rgba(255,255,255,.08);border-radius:4px;padding:0 4px; }
.ia-slim-import-btn { padding:7px 16px !important;font-size:.84rem !important; }

/* Custom icon dropdown for game selection (Create Import modal) */
.ia-game-select { position:relative; }
.ia-game-select-btn { width:100%;display:flex;align-items:center;gap:10px;padding:10px 12px;border-radius:10px;border:1px solid rgba(255,255,255,.12);background:rgba(255,255,255,.04);color:#fff;font-size:.9rem;font-weight:700;cursor:pointer;text-align:left; }
.ia-game-select-btn:hover { border-color:rgba(109,92,255,.4); }
.ia-game-select-btn img { width:22px;height:22px;object-fit:contain;flex-shrink:0; }
.ia-game-select-menu { display:none;position:absolute;top:calc(100% + 6px);left:0;right:0;z-index:50;background:#2a2d35;border:1px solid rgba(255,255,255,.1);border-radius:12px;padding:6px;box-shadow:0 10px 30px rgba(0,0,0,.5);max-height:min(360px,48vh);overflow:hidden; }
.ia-game-select-menu.is-open { display:flex;flex-direction:column; }
.ia-game-select-opt { width:100%;display:flex;align-items:center;gap:10px;padding:9px 10px;border-radius:9px;border:none;background:none;color:rgba(255,255,255,.78);font-size:.88rem;font-weight:700;cursor:pointer;text-align:left; }
.ia-game-select-opt:hover { background:rgba(255,255,255,.06);color:#fff; }
.ia-game-select-opt.is-active { background:rgba(109,92,255,.16);color:#c4b5fd; }
.ia-game-select-opt img { width:20px;height:20px;object-fit:contain;flex-shrink:0; }
.ia-game-select-search-wrap{position:sticky;top:0;z-index:2;padding:4px 4px 8px;background:#2a2d35}.ia-game-select-search{width:100%;height:38px;border-radius:9px;border:1px solid rgba(255,255,255,.12);background:#202328;color:#fff;padding:0 12px;font-size:.82rem;outline:none}.ia-game-select-search:focus{border-color:rgba(109,92,255,.7);box-shadow:0 0 0 3px rgba(109,92,255,.14)}.ia-game-select-options{overflow-y:auto;min-height:0;padding-right:2px}.ia-game-select-empty{display:none;padding:12px;text-align:center;color:rgba(255,255,255,.42);font-size:.8rem}

/* Full-width modal, sized to the content area rather than a small centered box */
.ia-modal-wide { max-width:min(1300px, calc(100vw - 48px)); width:100%; }
@media(max-width:900px){ .ia-modal-wide { max-width:calc(100vw - 24px); } }

/* ── Import-specific additions ── */
.ia-update-chk { display:flex;gap:7px;align-items:center;background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.08);border-radius:10px;padding:8px 12px;font-weight:800;color:rgba(255,255,255,.7);font-size:.82rem;cursor:pointer;white-space:nowrap; }
.ia-game-icon-sm { width:18px;height:18px;object-fit:contain;flex-shrink:0; }
.ia-code-card { border-radius:20px;border:1px solid rgba(255,255,255,.07);background:#25282a;box-shadow:0 2px 16px rgba(0,0,0,.18);padding:18px 20px;margin-top:18px; }
.ia-code-head { display:flex;align-items:center;justify-content:space-between;gap:12px;margin-bottom:10px; }
.ia-code-head span { font-weight:900;color:#a78fff;text-transform:uppercase;font-size:.76rem;letter-spacing:.07em; }
.ia-code { margin:0;background:rgba(0,0,0,.28);border:1px solid rgba(255,255,255,.08);border-radius:12px;padding:16px;color:#e2d9ff;white-space:pre-wrap;overflow:auto;font-size:.82rem;max-height:480px; }
.ia-drop { position:relative;min-height:120px;border:1px dashed rgba(167,139,250,.42);border-radius:16px;background:rgba(109,92,255,.055);display:flex;flex-direction:column;align-items:center;justify-content:center;text-align:center;padding:18px;cursor:pointer;overflow:hidden; }
.ia-drop input { position:absolute;inset:0;opacity:0;cursor:pointer; }
.ia-drop i { font-size:1.7rem;color:#a78fff;margin-bottom:6px; }
.ia-drop span { font-weight:900;font-size:.88rem; }
.ia-drop small { display:block;color:rgba(255,255,255,.48);margin-top:4px; }
.ia-file-row { display:none;align-items:center;gap:12px;margin-top:14px;padding:11px 14px;border-radius:12px;background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.08); }
.ia-file-row.show { display:flex; }
.ia-file-icon { width:34px;height:34px;border-radius:9px;background:rgba(109,92,255,.15);color:#c4b5fd;display:flex;align-items:center;justify-content:center;font-size:.95rem;flex-shrink:0; }
.ia-file-meta { flex:1;min-width:0; }
.ia-file-name { font-weight:800;font-size:.85rem;color:#fff;white-space:nowrap;overflow:hidden;text-overflow:ellipsis; }
.ia-file-size { font-size:.74rem;color:rgba(255,255,255,.42); }
.ia-file-remove { width:30px;height:30px;border-radius:8px;border:1px solid rgba(251,113,133,.3);background:rgba(251,113,133,.1);color:#fb7185;cursor:pointer;display:flex;align-items:center;justify-content:center;flex-shrink:0; }
.ia-file-remove:hover { background:rgba(251,113,133,.2); }

/* Editable spreadsheet-style cells */
.ia-cell-input, .ia-cell-select { width:100%;min-height:42px;background:rgba(255,255,255,.05);border:1px solid rgba(255,255,255,.09);border-radius:8px;color:rgba(255,255,255,.88);font-size:.82rem;padding:10px 10px;font-family:inherit;transition:border-color .12s,background .12s; }
.al-table tbody td { padding-top:13px !important;padding-bottom:13px !important; }
.ia-cell-input::placeholder { color:rgba(255,255,255,.22); }
.ia-cell-select { appearance:auto;-webkit-appearance:auto;color:#fff;background-color:#262a2d;border-color:rgba(109,92,255,.55); }
.ia-cell-select option { background:#1d2026;color:#fff; }
.ia-cell-select option:checked { background:#6d5cff;color:#fff; }
.ia-cell-select option:hover { background:#2f3340;color:#fff; }
.ia-cell-input:focus, .ia-cell-select:focus { outline:none;border-color:rgba(109,92,255,.55);background:rgba(109,92,255,.08); }
.ia-cell-input.is-mono { font-family:monospace; }
.ia-cell-input.is-dirty, .ia-cell-select.is-dirty { border-color:rgba(250,204,21,.5);background:rgba(250,204,21,.06); }
.ia-unsaved-pill { display:none;align-items:center;gap:6px;font-size:.78rem;font-weight:800;color:#facc15;background:rgba(250,204,21,.1);border:1px solid rgba(250,204,21,.28);border-radius:10px;padding:7px 12px; }
.ia-unsaved-pill.show { display:flex; }

/* Manual delivery checkbox cell */
.ia-chk-cell { display:flex;align-items:center;justify-content:center; }
.ia-chk { appearance:none;-webkit-appearance:none;width:19px;height:19px;border-radius:6px;border:1.5px solid rgba(255,255,255,.18);background:rgba(255,255,255,.06);cursor:pointer;position:relative;flex-shrink:0; }
.ia-chk:hover { border-color:rgba(109,92,255,.6); }
.ia-chk:checked { background:#6d5cff;border-color:#6d5cff; }
.ia-chk:checked::after { content:'';position:absolute;left:5px;top:1.5px;width:5px;height:9px;border:2px solid #fff;border-top:0;border-left:0;transform:rotate(45deg); }

/* Thumb strip inside the table */
.ia-thumbstrip { display:flex;align-items:center;gap:5px; }
.ia-thumbstrip img { width:28px;height:28px;object-fit:cover;border-radius:7px;border:1px solid rgba(255,255,255,.14); }
.ia-thumbstrip .ia-thumb-more { font-size:.68rem;font-weight:800;color:rgba(255,255,255,.45);background:rgba(255,255,255,.06);border-radius:7px;padding:0 5px;height:28px;display:flex;align-items:center; }

/* Account details modal grid */
.ia-details-grid { display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:14px; }
.ia-details-field label { display:block;font-size:.72rem;font-weight:800;color:rgba(255,255,255,.5);text-transform:uppercase;letter-spacing:.05em;margin-bottom:6px; }
.ia-details-field input, .ia-details-field select { width:100%; }
@media(max-width:560px){ .ia-details-grid{grid-template-columns:1fr;} }

/* Images manager modal */
.ia-img-list { display:flex;flex-direction:column;gap:8px;margin-top:16px;max-height:420px;overflow-y:auto; }
.ia-img-row { display:flex;align-items:center;gap:14px;padding:10px 14px;border-radius:12px;background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.08);cursor:grab; }
.ia-img-row.dragging { opacity:.4; }
.ia-img-handle { color:rgba(255,255,255,.3);font-size:.85rem;cursor:grab;flex-shrink:0; }
.ia-img-thumb { width:54px;height:54px;border-radius:10px;object-fit:cover;border:1px solid rgba(255,255,255,.12);flex-shrink:0; }
.ia-img-meta { flex:1;min-width:0; }
.ia-img-name { font-weight:800;font-size:.83rem;color:#fff;white-space:nowrap;overflow:hidden;text-overflow:ellipsis; }
.ia-img-size { font-size:.73rem;color:rgba(255,255,255,.42); }
.ia-img-remove { width:30px;height:30px;border-radius:8px;border:1px solid rgba(251,113,133,.3);background:rgba(251,113,133,.1);color:#fb7185;cursor:pointer;display:flex;align-items:center;justify-content:center;flex-shrink:0; }
.ia-img-remove:hover { background:rgba(251,113,133,.2); }
.ia-img-empty { text-align:center;padding:20px;color:rgba(255,255,255,.35);font-size:.85rem; }
@media(max-width:900px){ .ia-slimbar-title strong{max-width:60vw;} }


/* Ricardo fixes: content-container width, visible modal controls, custom update toggle, custom dark listing status */
.al-page.content.container-fluid {
  --ia-page-max: min(1720px, calc(100vw - 280px));
  width: var(--ia-page-max) !important;
  max-width: var(--ia-page-max) !important;
  margin-left: calc((100% - var(--ia-page-max)) / 2) !important;
  margin-right: calc((100% - var(--ia-page-max)) / 2) !important;
  padding-left:8px !important;
  padding-right:8px !important;
}
@media(max-width:1199.98px){
  .al-page.content.container-fluid {
    --ia-page-max: calc(100vw - 32px);
    padding-left:8px !important;
    padding-right:8px !important;
  }
}
@media(max-width:767.98px){
  .al-page.content.container-fluid {
    --ia-page-max: calc(100vw - 16px);
    padding-left:6px !important;
    padding-right:6px !important;
  }
}
.ia-slim-btn.is-danger { background:rgba(251,113,133,.10);border-color:rgba(251,113,133,.28);color:#fb7185; }
.ia-slim-btn.is-danger:hover { background:rgba(251,113,133,.18);border-color:rgba(251,113,133,.45);color:#fff; }
.modal .btn-close,
.modal .btn-close.btn-close-white {
  width:36px !important;
  height:36px !important;
  min-width:36px !important;
  border-radius:12px !important;
  border:1px solid rgba(255,255,255,.16) !important;
  background:rgba(255,255,255,.10) !important;
  background-image:none !important;
  opacity:1 !important;
  padding:0 !important;
  margin:0 !important;
  box-shadow:none !important;
  display:inline-flex !important;
  align-items:center !important;
  justify-content:center !important;
  color:rgba(255,255,255,.82) !important;
  transition:background .14s,border-color .14s,color .14s,transform .14s !important;
}
.modal .btn-close::before,
.modal .btn-close.btn-close-white::before {
  content:'\00d7';
  display:block;
  font-size:25px;
  line-height:1;
  font-weight:300;
  color:currentColor;
  transform:translateY(-1px);
}
.modal .btn-close:hover,
.modal .btn-close.btn-close-white:hover {
  background:rgba(251,113,133,.18) !important;
  border-color:rgba(251,113,133,.48) !important;
  color:#fff !important;
  transform:translateY(-1px);
}
.modal .btn-close:focus { box-shadow:0 0 0 3px rgba(109,92,255,.24) !important; }
.ia-modal-close-btn {
  display:inline-flex !important;
  align-items:center;
  justify-content:center;
  gap:7px;
  min-width:96px;
  min-height:40px;
  padding:9px 16px;
  border-radius:12px;
  border:1px solid rgba(255,255,255,.16);
  background:linear-gradient(180deg,rgba(255,255,255,.11),rgba(255,255,255,.055));
  color:rgba(255,255,255,.9) !important;
  font-size:.84rem;
  font-weight:850;
  cursor:pointer;
  opacity:1 !important;
  transition:background .14s,border-color .14s,color .14s,transform .14s;
}
.ia-modal-close-btn:hover { background:rgba(255,255,255,.14);border-color:rgba(255,255,255,.26);color:#fff !important;transform:translateY(-1px); }
.ia-modal-close-btn i { color:rgba(255,255,255,.58); }
.ia-update-chk {
  display:inline-flex !important;
  align-items:center;
  gap:10px;
  min-height:40px;
  padding:7px 14px 7px 9px !important;
  border-radius:14px !important;
  border:1px solid rgba(167,139,250,.24) !important;
  background:linear-gradient(180deg,rgba(109,92,255,.14),rgba(255,255,255,.045)) !important;
  color:rgba(255,255,255,.9) !important;
  font-size:.82rem !important;
  font-weight:900 !important;
  cursor:pointer;
  white-space:nowrap;
  user-select:none;
  box-shadow:inset 0 1px 0 rgba(255,255,255,.06);
  transition:background .14s,border-color .14s,transform .14s;
}
.ia-update-chk:hover { background:linear-gradient(180deg,rgba(109,92,255,.22),rgba(176,92,255,.10)) !important;border-color:rgba(167,139,250,.55) !important;transform:translateY(-1px); }
.ia-update-chk input {
  appearance:none !important;
  -webkit-appearance:none !important;
  width:36px !important;
  height:21px !important;
  margin:0 !important;
  border-radius:999px !important;
  border:1px solid rgba(255,255,255,.18) !important;
  background:rgba(0,0,0,.32) !important;
  position:relative;
  cursor:pointer;
  flex-shrink:0;
  transition:background .14s,border-color .14s,box-shadow .14s;
}
.ia-update-chk input::before {
  content:'';
  position:absolute;
  left:3px;
  top:3px;
  width:13px;
  height:13px;
  border-radius:50%;
  background:rgba(255,255,255,.78);
  box-shadow:0 2px 6px rgba(0,0,0,.4);
  transition:transform .14s,background .14s;
}
.ia-update-chk input:checked { background:linear-gradient(135deg,#6d5cff,#b05cff) !important;border-color:rgba(176,92,255,.9) !important;box-shadow:0 0 0 3px rgba(109,92,255,.18); }
.ia-update-chk input:checked::before { transform:translateX(15px);background:#fff; }
.ia-custom-select { position:relative;width:100%; }
.ia-custom-select-btn {
  width:100%;min-height:42px;display:flex;align-items:center;justify-content:space-between;gap:10px;
  border:1px solid rgba(109,92,255,.52);border-radius:8px;background:#202328;color:rgba(255,255,255,.92);
  padding:10px 10px;font-size:.82rem;font-weight:800;cursor:pointer;text-align:left;
}
.ia-custom-select-btn:hover,.ia-custom-select.is-open .ia-custom-select-btn { border-color:rgba(167,139,250,.75);background:#242730; }
.ia-custom-select-btn i { color:rgba(255,255,255,.52);font-size:.72rem; }
.ia-custom-select-menu { display:none;position:fixed;z-index:99999;min-width:150px;max-height:260px;overflow:auto;padding:6px;border-radius:11px;border:1px solid rgba(255,255,255,.10);background:#1d2026;box-shadow:0 14px 34px rgba(0,0,0,.55); }
.ia-custom-select.is-open .ia-custom-select-menu { display:block; }
.ia-custom-select-opt { width:100%;display:flex;align-items:center;border:0;border-radius:8px;background:transparent;color:rgba(255,255,255,.78);padding:8px 10px;font-size:.8rem;font-weight:800;cursor:pointer;text-align:left; }
.ia-custom-select-opt:hover { background:rgba(255,255,255,.07);color:#fff; }
.ia-custom-select-opt.is-active { background:linear-gradient(135deg,rgba(109,92,255,.40),rgba(176,92,255,.22));color:#fff; }

/* Custom import confirmation modal */
.ia-confirm-dialog { max-width:470px; }
.ia-confirm-content {
  border-radius:20px !important;
  border:1px solid rgba(167,139,250,.18) !important;
  background:linear-gradient(180deg,#24272a,#1f2124) !important;
  box-shadow:0 24px 70px rgba(0,0,0,.65), inset 0 1px 0 rgba(255,255,255,.04) !important;
  overflow:hidden;
}
.ia-confirm-body { padding:24px 24px 18px;display:flex;gap:16px;align-items:flex-start; }
.ia-confirm-icon {
  width:46px;height:46px;border-radius:15px;display:flex;align-items:center;justify-content:center;flex-shrink:0;
  color:#fff;background:linear-gradient(135deg,#6d5cff,#b05cff);box-shadow:0 10px 24px rgba(109,92,255,.28);
  font-size:1.05rem;
}
.ia-confirm-title { margin:0 0 6px;font-size:1.02rem;font-weight:950;color:#fff;letter-spacing:-.01em; }
.ia-confirm-text { margin:0;color:rgba(255,255,255,.58);font-size:.86rem;line-height:1.5; }
.ia-confirm-footer { padding:14px 20px 20px;display:flex;justify-content:flex-end;gap:10px;border-top:1px solid rgba(255,255,255,.06); }
.ia-confirm-cancel,
.ia-confirm-submit {
  display:inline-flex;align-items:center;justify-content:center;gap:8px;min-height:40px;padding:9px 16px;border-radius:12px;
  font-size:.84rem;font-weight:900;cursor:pointer;transition:transform .14s,background .14s,border-color .14s,opacity .14s;
}
.ia-confirm-cancel { border:1px solid rgba(255,255,255,.14);background:rgba(255,255,255,.06);color:rgba(255,255,255,.82); }
.ia-confirm-cancel:hover { background:rgba(255,255,255,.10);border-color:rgba(255,255,255,.24);color:#fff;transform:translateY(-1px); }
.ia-confirm-submit { border:0;background:linear-gradient(135deg,#6d5cff,#b05cff);color:#fff;box-shadow:0 10px 24px rgba(109,92,255,.25); }
.ia-confirm-submit:hover { opacity:.92;transform:translateY(-1px); }


/* Hide Current Division when Master, Grandmaster, or Challenger is selected */
.al-table th.ia-col-hidden,
.al-table td.ia-col-hidden { display:none !important; }
</style>
<?= $this->end() ?>

<div class="al-page content container-fluid">

<?php if (empty($selected_batch)): ?>

  <!-- ═══════════ LIST VIEW ═══════════ -->
  <div class="al-hero">
    <div class="al-hero-left">
      <div class="al-hero-icon"><i class="fa-duotone fa-file-import"></i></div>
      <div>
        <h2 class="al-hero-title">Account Imports</h2>
        <p class="al-hero-sub"><?= count($import_batches) ?> import<?= count($import_batches) !== 1 ? 's' : '' ?> total</p>
      </div>
    </div>
    <button type="button" class="al-add-btn" data-bs-toggle="modal" data-bs-target="#create-import-modal">
      <i class="fa-solid fa-plus"></i> New Import
    </button>
  </div>

  <div class="al-table-wrap">
    <table class="al-table">
      <thead>
        <tr>
          <th>Name</th>
          <th>Import ID</th>
          <th>Status</th>
          <th>Accounts</th>
          <th>Last Updated</th>
          <th class="text-end">Open</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($import_batches as $batch): $bgame = strtolower((string)($batch['game'] ?? 'lol')); ?>
        <tr class="al-row">
          <td>
            <div class="al-acc-wrap">
              <span class="al-acc-img"><img class="ia-game-icon-sm" src="<?= esc($gameIcons[$bgame] ?? $gameIcons['lol']) ?>" alt=""></span>
              <div>
                <div class="al-acc-name"><?= esc($batch['import_name'] ?? ($batch['filename'] ?? 'Account Import')) ?></div>
                <div class="al-acc-sub"><?= esc($gameLabels[$bgame] ?? strtoupper($bgame)) ?></div>
              </div>
            </div>
          </td>
          <td><span class="al-col-id">#<?= (int)($batch['id'] ?? 0) ?></span></td>
          <td><?= lb_import_status_badge($batch['status'] ?? 'draft') ?></td>
          <td><span class="al-acc-name"><?= (int)($batch['total_rows'] ?? 0) ?></span></td>
          <td><span class="al-col-date"><?= esc($batch['updated_at'] ?? ($batch['created_at'] ?? '')) ?></span></td>
          <td class="text-end">
            <a class="al-view-btn" href="<?= BASE_URL ?>/seller-area/import-accounts?id=<?= (int)$batch['id'] ?>">
              Open <i class="fa-regular fa-arrow-right"></i>
            </a>
          </td>
        </tr>
        <?php endforeach; ?>
        <?php if (empty($import_batches)): ?>
        <tr><td colspan="6">
          <div class="al-empty">
            <i class="fa-duotone fa-file-import"></i>
            <div style="font-weight:900;font-size:1rem;color:rgba(255,255,255,.6);margin-bottom:6px;">No imports yet</div>
            <div style="font-size:.85rem;">Click "New Import" to bulk-add accounts from a JSON file.</div>
          </div>
        </td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>

<?php else: $game = strtolower((string)($selected_batch['game'] ?? 'lol')); ?>

  <!-- ═══════════ DETAIL VIEW — compact single-row header ═══════════ -->
  <div class="ia-slimbar">
    <div class="ia-slimbar-left">
      <a class="ia-back-btn" href="<?= BASE_URL ?>/seller-area/import-accounts" title="Back to imports"><i class="fa-solid fa-arrow-left"></i></a>
      <img class="ia-game-icon-sm" src="<?= esc($gameIcons[$game] ?? $gameIcons['lol']) ?>" alt="">
      <div class="ia-slimbar-title">
        <strong><?= esc($selected_batch['import_name'] ?? 'Account Import') ?></strong>
        <span>#<?= (int)$selected_batch['id'] ?> · <?= count($import_rows) ?> account<?= count($import_rows) !== 1 ? 's' : '' ?><?php if ((int)($selected_batch['created_count'] ?? 0) + (int)($selected_batch['updated_count'] ?? 0) + (int)($selected_batch['failed_count'] ?? 0) > 0): ?> · <?= (int)($selected_batch['created_count'] ?? 0) ?> created, <?= (int)($selected_batch['updated_count'] ?? 0) ?> updated<?php if ((int)($selected_batch['failed_count'] ?? 0) > 0): ?>, <span style="color:#fb7185;"><?= (int)($selected_batch['failed_count'] ?? 0) ?> failed</span><?php endif; ?><?php endif; ?></span>
      </div>
    </div>
    <div class="ia-slimbar-right">
      <span class="ia-unsaved-pill" id="ia-unsaved-pill"><i class="fa-solid fa-circle-exclamation"></i> Unsaved</span>
      <button type="button" class="ia-slim-btn" data-bs-toggle="modal" data-bs-target="#json-format-modal">
        <i class="fa-regular fa-code"></i> JSON Format
      </button>
      <button type="button" class="ia-slim-btn" id="download-current-json"<?= empty($import_rows) ? ' disabled style="opacity:.4;cursor:not-allowed;"' : '' ?>>
        <i class="fa-regular fa-download"></i> Download
      </button>
      <button type="button" class="ia-slim-btn" data-bs-toggle="modal" data-bs-target="#json-upload-modal">
        <i class="fa-regular fa-file-import"></i> Load From File
      </button>
      <button type="button" class="ia-slim-btn is-danger" id="delete-import-btn" title="Delete Import">
        <i class="fa-regular fa-trash"></i> Delete Import
      </button>
      <button type="button" class="ia-slim-btn" id="save-changes-btn"<?= empty($import_rows) ? ' disabled style="opacity:.4;cursor:not-allowed;"' : '' ?>><i class="fa-regular fa-floppy-disk"></i> Save <kbd>Ctrl+S</kbd></button>
      <form class="ajax-form-lite" id="import-all-accounts-form" action="<?= AJAX_URL ?>" method="POST" data-success-redirect="https://new.lolboost.gg/seller-area/accounts" style="display:flex;align-items:center;gap:8px;">
        <input type="hidden" name="action" value="seller_import_all_accounts"><input type="hidden" name="batch_id" value="<?= (int)$selected_batch['id'] ?>">
        <label class="ia-update-chk"><input type="checkbox" name="update_existing" value="1"> Update existing</label>
        <button class="al-add-btn ia-slim-import-btn" type="button" id="open-import-confirm-btn"<?= empty($import_rows) ? ' disabled' : '' ?>><i class="fa-regular fa-cloud-arrow-up me-1"></i> Import All Accounts</button>
      </form>
      <form class="ajax-form-lite" id="delete-import-form" action="<?= AJAX_URL ?>" method="POST" onsubmit="return confirm('Delete this import and all staged rows?');" style="display:none;">
        <input type="hidden" name="action" value="seller_import_delete_batch"><input type="hidden" name="batch_id" value="<?= (int)$selected_batch['id'] ?>">
      </form>
    </div>
  </div>

  <div class="al-table-wrap" style="max-height:80vh;">
    <table class="al-table" id="ia-edit-table">
      <thead>
        <tr>
          <th style="position:sticky;left:0;z-index:3;">#</th>
          <?php foreach ($ia_main_columns as $col): ?>
            <th data-col="<?= esc($col[0]) ?>" style="min-width:<?= (int)$col[3] ?>px;"><?= esc($col[1]) ?></th>
          <?php endforeach; ?>
          <th style="min-width:150px;">Images</th>
          <th style="min-width:90px;">Import Status</th>
          <th style="min-width:200px;">Message</th>
        </tr>
      </thead>
      <tbody>
      <?php foreach ($import_rows as $idx => $row):
        $data = json_decode((string)($row['row_data'] ?? '{}'), true); if (!is_array($data)) $data = [];
        $ids = json_decode((string)($row['image_ids'] ?? '[]'), true); if (!is_array($ids)) $ids = [];
        $rid = (int)$row['id'];
        $rowTitle = (string)($data['title'] ?? ('Account #' . $rid));
        // Build this row's image list (id/url/name/size) from the page-level gallery lookup, preserving order.
        $rowImages = [];
        foreach ($ids as $iid) {
          $iid = (int)$iid;
          if (isset($gallery_lookup[$iid])) {
            $g = $gallery_lookup[$iid];
            $rowImages[] = ['id' => $iid, 'url' => $g['url'] ?? '', 'name' => $g['original_name'] ?? ('image_' . $iid), 'size' => (int)($g['size_bytes'] ?? 0)];
          }
        }
      ?>
        <tr class="al-row">
          <td style="position:sticky;left:0;background:#25282a;z-index:1;"><span class="al-col-id"><?= $idx + 1 ?></span></td>
          <?php foreach ($ia_main_columns as $col): [$key, $label, $type] = $col; $val = lb_field($data, $key); ?>
            <td data-col="<?= esc($key) ?>">
              <?php
                $selectMap = null;
                $defaultSelect = '';
                if ($type === 'select_status') { $selectMap = $ia_status_options; $defaultSelect = 'listed'; }
                elseif ($type === 'select_rank') { $selectMap = $ia_rank_options; $defaultSelect = '0'; }
                elseif ($type === 'select_division') { $selectMap = $ia_division_options; $defaultSelect = '1'; }
                elseif ($type === 'select_server') { $selectMap = $ia_server_options; $defaultSelect = strtolower((string)($data['server'] ?? 'euw')); }
                elseif ($type === 'select_level_method') { $selectMap = $ia_level_method_options; $defaultSelect = 'by_hand'; }
                elseif ($type === 'select_delivery') { $selectMap = $ia_delivery_options; $defaultSelect = 'instant'; }
                elseif ($type === 'select_bool') { $selectMap = $ia_bool_options; $defaultSelect = $key === 'active' ? '1' : '0'; }
              ?>
              <?php if (is_array($selectMap)): ?>
                <?php $selectVal = $val !== '' ? (string)$val : (string)$defaultSelect; ?>
                <select class="ia-cell-select ia-field-input" data-row="<?= $rid ?>" data-field="<?= $key ?>">
                  <?php foreach ($selectMap as $sv => $sl): $svStr = (string)$sv; ?>
                    <option value="<?= esc($svStr) ?>"<?= $selectVal === $svStr ? ' selected' : '' ?>><?= esc($sl) ?></option>
                  <?php endforeach; ?>
                </select>
              <?php else: ?>
                <input type="text" inputmode="<?= $key === 'price' ? 'decimal' : ($type === 'number' ? 'numeric' : 'text') ?>" class="ia-cell-input ia-field-input<?= in_array($key, ['login', 'password', 'email', 'email_login', 'email_password'], true) ? ' is-mono' : '' ?>" data-row="<?= $rid ?>" data-field="<?= $key ?>" value="<?= esc($val) ?>" placeholder="—">
              <?php endif; ?>
            </td>
          <?php endforeach; ?>
          <td>
            <div class="ia-thumbstrip" id="ia-thumbstrip-<?= $rid ?>">
              <?php foreach (array_slice($rowImages, 0, 3) as $img): ?><img src="<?= esc($img['url']) ?>" alt=""><?php endforeach; ?>
              <?php if (count($rowImages) > 3): ?><span class="ia-thumb-more">+<?= count($rowImages) - 3 ?></span><?php endif; ?>
            </div>
            <button type="button" class="al-view-btn is-sm mt-2 ia-open-images" data-bs-toggle="modal" data-bs-target="#images-modal" data-row-id="<?= $rid ?>" data-row-title="<?= esc($rowTitle) ?>">
              <i class="fa-regular fa-images"></i> <?= count($rowImages) ?> image<?= count($rowImages) !== 1 ? 's' : '' ?>
            </button>
          </td>
          <td><?= lb_import_status_badge($row['status'] ?? 'draft') ?></td>
          <td style="max-width:240px;white-space:normal;color:rgba(255,255,255,.55);font-size:.78rem;"><?= esc($row['message'] ?? '') ?></td>
        </tr>
      <?php endforeach; ?>
      <?php if (empty($import_rows)): ?>
        <tr><td colspan="<?= count($ia_main_columns) + 3 ?>">
          <div class="al-empty">
            <i class="fa-duotone fa-file-import"></i>
            <div style="font-weight:900;font-size:1rem;color:rgba(255,255,255,.6);margin-bottom:6px;">No accounts loaded yet</div>
            <div style="font-size:.85rem;">Click the <strong>"…"</strong> menu above and choose <strong>"Load From File"</strong> to upload your prepared JSON.</div>
          </div>
        </td></tr>
      <?php endif; ?>
      </tbody>
    </table>
  </div>

  <!-- Hidden store: per-row "Account Details" field sets, re-parented into the shared modal on demand. -->
  <div id="ia-details-store" style="display:none;">
    <?php foreach ($import_rows as $row):
      $data = json_decode((string)($row['row_data'] ?? '{}'), true); if (!is_array($data)) $data = [];
      $rid = (int)$row['id'];
    ?>
      <div class="ia-details-pane" id="ia-details-<?= $rid ?>">
        <div class="ia-details-grid">
          <?php foreach ($ia_detail_fields as $f): [$key, $label, $type] = $f; $val = lb_field($data, $key); ?>
            <div class="ia-details-field">
              <label><?= esc($label) ?></label>
              <?php if ($type === 'select_game'): ?>
                <select class="ia-cell-select ia-field-input" data-row="<?= $rid ?>" data-field="<?= $key ?>">
                  <?php foreach (['lol' => 'League of Legends', 'val' => 'Valorant', 'tft' => 'Teamfight Tactics'] as $gv => $gl): ?>
                    <option value="<?= $gv ?>"<?= $val === $gv ? ' selected' : '' ?>><?= $gl ?></option>
                  <?php endforeach; ?>
                </select>
              <?php elseif ($type === 'select_status'): ?>
                <?php $statusVal = $val !== '' ? $val : 'listed'; $statusMap = ['draft' => 'Draft', 'pending' => 'Pending', 'listed' => 'Listed', 'archived' => 'Archived']; ?>
                <input type="hidden" class="ia-field-input ia-custom-select-value" data-row="<?= $rid ?>" data-field="<?= $key ?>" value="<?= esc($statusVal) ?>">
                <div class="ia-custom-select">
                  <button type="button" class="ia-custom-select-btn"><span><?= esc($statusMap[$statusVal] ?? 'Listed') ?></span><i class="fa-solid fa-chevron-down"></i></button>
                  <div class="ia-custom-select-menu">
                    <?php foreach ($statusMap as $sv => $sl): ?>
                      <button type="button" class="ia-custom-select-opt<?= $statusVal === $sv ? ' is-active' : '' ?>" data-value="<?= $sv ?>" data-label="<?= esc($sl) ?>"><?= esc($sl) ?></button>
                    <?php endforeach; ?>
                  </div>
                </div>
              <?php elseif ($type === 'select_delivery'): ?>
                <?php $deliveryVal = $val !== '' ? $val : 'instant'; $deliveryMap = ['instant' => 'Instant', 'manual' => 'Manual']; ?>
                <input type="hidden" class="ia-field-input ia-custom-select-value" data-row="<?= $rid ?>" data-field="<?= $key ?>" value="<?= esc($deliveryVal) ?>">
                <div class="ia-custom-select">
                  <button type="button" class="ia-custom-select-btn"><span><?= esc($deliveryMap[$deliveryVal] ?? 'Instant') ?></span><i class="fa-solid fa-chevron-down"></i></button>
                  <div class="ia-custom-select-menu">
                    <?php foreach ($deliveryMap as $dv => $dl): ?>
                      <button type="button" class="ia-custom-select-opt<?= $deliveryVal === $dv ? ' is-active' : '' ?>" data-value="<?= $dv ?>" data-label="<?= esc($dl) ?>"><?= esc($dl) ?></button>
                    <?php endforeach; ?>
                  </div>
                </div>
              <?php elseif ($type === 'select_bool'): ?>
                <?php $boolVal = ($val === '1' || $val === 1) ? '1' : '0'; $boolMap = ['1' => 'Yes', '0' => 'No']; ?>
                <input type="hidden" class="ia-field-input ia-custom-select-value" data-row="<?= $rid ?>" data-field="<?= $key ?>" value="<?= esc($boolVal) ?>">
                <div class="ia-custom-select">
                  <button type="button" class="ia-custom-select-btn"><span><?= esc($boolMap[$boolVal] ?? 'No') ?></span><i class="fa-solid fa-chevron-down"></i></button>
                  <div class="ia-custom-select-menu">
                    <?php foreach ($boolMap as $bv => $bl): ?>
                      <button type="button" class="ia-custom-select-opt<?= $boolVal === $bv ? ' is-active' : '' ?>" data-value="<?= $bv ?>" data-label="<?= esc($bl) ?>"><?= esc($bl) ?></button>
                    <?php endforeach; ?>
                  </div>
                </div>
              <?php else: ?>
                <input type="text" inputmode="<?= $type === 'number' ? 'numeric' : 'text' ?>" class="ia-cell-input ia-field-input" data-row="<?= $rid ?>" data-field="<?= $key ?>" value="<?= esc($val) ?>" placeholder="—">
              <?php endif; ?>
            </div>
          <?php endforeach; ?>
        </div>
      </div>
    <?php endforeach; ?>
  </div>

<?php endif; ?>
</div>

<!-- ═══════════ Create Import Modal ═══════════ -->
<div class="modal fade" id="create-import-modal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <form class="modal-content ajax-form-lite" action="<?= AJAX_URL ?>" method="POST">
      <input type="hidden" name="action" value="seller_import_create_batch">
      <div class="modal-header">
        <h5 class="modal-title"><i class="fa-duotone fa-file-import me-2"></i>Create Import</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <label class="form-label">Game</label>
        <div class="ia-game-select" id="ia-game-select">
          <input type="hidden" name="game" id="ia-game-select-value" value="<?= esc($iaDefaultGame) ?>">
          <button type="button" class="ia-game-select-btn" id="ia-game-select-btn">
            <?php $iaDefaultIcon = $gameIcons[$iaDefaultGame] ?? ''; ?>
            <?php if ($iaDefaultIcon !== ''): ?><img src="<?= esc($iaDefaultIcon) ?>" alt=""><?php else: ?><span class="ia-game-fallback"><i class="fa-solid fa-gamepad"></i></span><?php endif; ?>
            <span id="ia-game-select-label"><?= esc($gameLabels[$iaDefaultGame] ?? 'Game') ?></span>
            <i class="fa-solid fa-chevron-down ms-auto"></i>
          </button>
          <div class="ia-game-select-menu" id="ia-game-select-menu">
            <div class="ia-game-select-search-wrap">
              <input type="search" class="ia-game-select-search" id="ia-game-select-search" placeholder="Search games..." autocomplete="off">
            </div>
            <div class="ia-game-select-options" id="ia-game-select-options">
              <?php foreach ($gameLabels as $gv => $gl): $gicon = $gameIcons[$gv] ?? ''; ?>
                <button type="button" class="ia-game-select-opt<?= $gv === $iaDefaultGame ? ' is-active' : '' ?>" data-value="<?= esc($gv) ?>" data-label="<?= esc($gl) ?>">
                  <?php if ($gicon !== ''): ?><img src="<?= esc($gicon) ?>" alt=""><?php else: ?><span class="ia-game-fallback"><i class="fa-solid fa-gamepad"></i></span><?php endif; ?>
                  <span><?= esc($gl) ?></span>
                </button>
              <?php endforeach; ?>
              <div class="ia-game-select-empty" id="ia-game-select-empty">No games found</div>
            </div>
          </div>
        </div>
        <label class="form-label mt-3">Import Name</label>
        <input class="form-control" name="name" placeholder="LoL Account Import June" required>
        <div class="form-text">Use a name to easily identify this import later.</div>
      </div>
      <div class="modal-footer">
        <button type="button" class="ia-modal-close-btn" data-bs-dismiss="modal"><i class="fa-regular fa-xmark"></i> Close</button>
        <button class="btn btn-primary" type="submit"><i class="fa-regular fa-plus me-1"></i>Create Import</button>
      </div>
    </form>
  </div>
</div>

<?php if (!empty($selected_batch)): ?>
<!-- ═══════════ Import From File Modal ═══════════ -->
<div class="modal fade" id="json-upload-modal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <form class="modal-content ajax-form-lite" id="json-upload-form" action="<?= AJAX_URL ?>" method="POST" enctype="multipart/form-data">
      <input type="hidden" name="action" value="seller_import_upload_json"><input type="hidden" name="batch_id" value="<?= (int)$selected_batch['id'] ?>">
      <div class="modal-header">
        <h5 class="modal-title"><i class="fa-duotone fa-file-json me-2"></i>Import From File</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <p class="text-muted">Upload your prepared accounts JSON file. Accounts are staged in the table first — nothing is listed until you click <strong>Import All Accounts</strong>.</p>
        <label class="ia-drop" id="ia-drop-label">
          <i class="fa-duotone fa-cloud-arrow-up"></i>
          <span>Drop your files here or click to browse</span>
          <small>Max 1 file · 5 MB max</small>
          <input type="file" name="json_file" id="ia-json-input" accept="application/json,.json" required>
        </label>
        <div class="ia-file-row" id="ia-file-row">
          <div class="ia-file-icon"><i class="fa-regular fa-file-lines"></i></div>
          <div class="ia-file-meta">
            <div class="ia-file-name" id="ia-file-name">—</div>
            <div class="ia-file-size" id="ia-file-size">—</div>
          </div>
          <button type="button" class="ia-file-remove" id="ia-file-remove" title="Remove"><i class="fa-regular fa-trash"></i></button>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="ia-modal-close-btn" data-bs-dismiss="modal"><i class="fa-regular fa-xmark"></i> Close</button>
        <button class="btn btn-primary" type="submit" id="ia-import-submit" disabled><i class="fa-regular fa-file-import me-1"></i>Import Accounts</button>
      </div>
    </form>
  </div>
</div>

<!-- ═══════════ JSON Format Reference Modal ═══════════ -->
<div class="modal fade" id="json-format-modal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title"><i class="fa-duotone fa-code me-2"></i>accounts.json format</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <pre class="ia-code"><code><?= htmlspecialchars($sampleJson, ENT_QUOTES, 'UTF-8') ?></code></pre>
      </div>
      <div class="modal-footer">
        <button type="button" class="ia-modal-close-btn" data-bs-dismiss="modal"><i class="fa-regular fa-xmark"></i> Close</button>
        <button type="button" class="btn btn-primary" id="download-sample-json"><i class="fa-regular fa-download me-1"></i>Download sample</button>
      </div>
    </div>
  </div>
</div>

<!-- ═══════════ Account Details Modal (shared, fields re-parented in) ═══════════ -->
<div class="modal fade" id="account-details-modal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title"><i class="fa-duotone fa-sliders me-2"></i>Account Details — <span id="account-details-title"></span></h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body" id="account-details-modal-body"></div>
      <div class="modal-footer">
        <button type="button" class="ia-modal-close-btn" data-bs-dismiss="modal"><i class="fa-regular fa-xmark"></i> Close</button>
        <button type="button" class="btn btn-primary" id="account-details-save"><i class="fa-regular fa-floppy-disk me-1"></i>Save Changes</button>
      </div>
    </div>
  </div>
</div>

<!-- ═══════════ Images Manager Modal ═══════════ -->
<div class="modal fade" id="images-modal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered ia-modal-wide">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title"><i class="fa-duotone fa-images me-2"></i>Images — <span id="images-modal-title"></span></h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <label class="ia-drop" id="ia-img-drop-label">
          <i class="fa-duotone fa-cloud-arrow-up"></i>
          <span>Drop images here or click to browse</span>
          <small>You can select several at once · JPG, PNG, WEBP, GIF</small>
          <input type="file" id="ia-img-input" accept="image/jpeg,image/png,image/webp,image/gif" multiple>
        </label>
        <p class="text-muted mt-2 mb-0" style="font-size:.78rem;">Drag the rows below to reorder — the first image becomes the cover photo.</p>
        <div class="ia-img-list" id="images-modal-list"></div>
      </div>
      <div class="modal-footer">
        <button type="button" class="ia-modal-close-btn" data-bs-dismiss="modal"><i class="fa-regular fa-xmark"></i> Close</button>
      </div>
    </div>
  </div>
</div>

<!-- ═══════════ Custom Import Confirm Modal ═══════════ -->
<div class="modal fade" id="import-confirm-modal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered ia-confirm-dialog">
    <div class="modal-content ia-confirm-content">
      <div class="ia-confirm-body">
        <div class="ia-confirm-icon"><i class="fa-regular fa-cloud-arrow-up"></i></div>
        <div>
          <h5 class="ia-confirm-title">Start import?</h5>
          <p class="ia-confirm-text">All staged accounts will be imported into your account shop. After the import is finished, you will be redirected to your accounts page automatically.</p>
        </div>
      </div>
      <div class="ia-confirm-footer">
        <button type="button" class="ia-confirm-cancel" data-bs-dismiss="modal"><i class="fa-regular fa-xmark"></i> Cancel</button>
        <button type="button" class="ia-confirm-submit" id="ia-confirm-import-submit"><i class="fa-regular fa-cloud-arrow-up"></i> Import Accounts</button>
      </div>
    </div>
  </div>
</div>
<?php endif; ?>

<script>
(function(){
  var AJAX_ENDPOINT = <?= json_encode(AJAX_URL) ?>;
  var BATCH_ID = <?= json_encode((int)($selected_batch['id'] ?? 0)) ?>;
  var sampleJson = <?= json_encode($sampleJson, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?>;

  function buildUniqueSampleJson() {
    try {
      var data = JSON.parse(sampleJson);
      var randomPart = Math.random().toString(36).slice(2, 8).toUpperCase();
      var datePart = new Date().toISOString().replace(/[-:.TZ]/g, '').slice(0, 14);
      var suffix = datePart + '-' + randomPart;
      if (data && Array.isArray(data.accounts)) {
        data.accounts.forEach(function(account, index) {
          var no = String(index + 1).padStart(3, '0');
          account.external_id = 'REF-EUW-' + suffix + '-' + no;
          account.slug = 'euw-diamond-ii-account-' + suffix.toLowerCase() + '-' + no;
        });
      }
      return JSON.stringify(data, null, 2);
    } catch (e) {
      return sampleJson;
    }
  }
  var currentRowsJson = <?= json_encode($currentRowsJson ?? '', JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?>;

  <?php
  $rowImagesInit = [];
  if (!empty($selected_batch)) {
    foreach ($import_rows as $row) {
      $rid = (int)$row['id'];
      $ids = json_decode((string)($row['image_ids'] ?? '[]'), true); if (!is_array($ids)) $ids = [];
      $list = [];
      foreach ($ids as $iid) {
        $iid = (int)$iid;
        if (isset($gallery_lookup[$iid])) {
          $g = $gallery_lookup[$iid];
          $list[] = ['id' => $iid, 'url' => $g['url'] ?? '', 'name' => $g['original_name'] ?? ('image_' . $iid), 'size' => (int)($g['size_bytes'] ?? 0)];
        }
      }
      $rowImagesInit[$rid] = $list;
    }
  }
  ?>
  var rowImages = <?= json_encode($rowImagesInit, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?>;
  var currentImagesRow = null;
  var currentDetailsRow = null;

  function formatSize(bytes) {
    bytes = bytes || 0;
    if (bytes < 1024) return bytes + ' B';
    if (bytes < 1024*1024) return (bytes/1024).toFixed(1) + ' KB';
    return (bytes/1024/1024).toFixed(1) + ' MB';
  }
  function downloadBlob(text, filename) {
    var blob = new Blob([text], {type: 'application/json'});
    var a = document.createElement('a');
    a.href = URL.createObjectURL(blob);
    a.download = filename;
    document.body.appendChild(a);
    a.click();
    setTimeout(function(){ URL.revokeObjectURL(a.href); a.remove(); }, 1000);
  }

  var dl = document.getElementById('download-sample-json');
  if (dl) { dl.addEventListener('click', function(){ downloadBlob(buildUniqueSampleJson(), 'lolboost-accounts-import-sample.json'); }); }
  var dlCurrent = document.getElementById('download-current-json');
  if (dlCurrent) { dlCurrent.addEventListener('click', function(){ if (!dlCurrent.hasAttribute('disabled')) downloadBlob(currentRowsJson, 'lolboost-accounts-import.json'); }); }

  var delBtn = document.getElementById('delete-import-btn');
  var delForm = document.getElementById('delete-import-form');
  if (delBtn && delForm) { delBtn.addEventListener('click', function(){ delForm.requestSubmit ? delForm.requestSubmit() : delForm.dispatchEvent(new Event('submit', {cancelable:true})); }); }

  // ── Import From File: file preview row ──
  var jsonInput = document.getElementById('ia-json-input');
  var fileRow = document.getElementById('ia-file-row');
  var fileName = document.getElementById('ia-file-name');
  var fileSize = document.getElementById('ia-file-size');
  var fileRemove = document.getElementById('ia-file-remove');
  var dropLabel = document.getElementById('ia-drop-label');
  var submitBtn = document.getElementById('ia-import-submit');
  if (jsonInput) {
    jsonInput.addEventListener('change', function(){
      if (jsonInput.files && jsonInput.files.length) {
        var f = jsonInput.files[0];
        fileName.textContent = f.name;
        fileSize.textContent = formatSize(f.size);
        fileRow.classList.add('show');
        dropLabel.style.display = 'none';
        submitBtn.removeAttribute('disabled');
      }
    });
  }
  if (fileRemove) {
    fileRemove.addEventListener('click', function(){
      jsonInput.value = '';
      fileRow.classList.remove('show');
      dropLabel.style.display = '';
      submitBtn.setAttribute('disabled', 'disabled');
    });
  }
  var uploadModalEl = document.getElementById('json-upload-modal');
  if (uploadModalEl) {
    uploadModalEl.addEventListener('hidden.bs.modal', function(){
      if (jsonInput) jsonInput.value = '';
      if (fileRow) fileRow.classList.remove('show');
      if (dropLabel) dropLabel.style.display = '';
      if (submitBtn) submitBtn.setAttribute('disabled', 'disabled');
    });
  }

  function wire(form) {
    form.addEventListener('submit', function(e){
      e.preventDefault();
      var btn = form.querySelector('button[type=submit]');
      if (btn) btn.disabled = true;
      fetch(AJAX_ENDPOINT, {method:'POST', body:new FormData(form), credentials:'same-origin'})
        .then(function(r){ return r.json(); })
        .then(function(json){
          if (json.sendToast && typeof create_toast === 'function') { create_toast(json.sendToast.type, json.sendToast.title || '', json.sendToast.message || ''); }
          if (json.redirectUrl) { window.location.href = json.redirectUrl; return; }
          if (form.dataset.successRedirect && !(json.sendToast && ['danger','error'].indexOf(String(json.sendToast.type || '').toLowerCase()) !== -1)) { window.location.href = form.dataset.successRedirect; return; }
          if (json.refreshPage) { window.location.reload(); }
        })
        .catch(function(){ if (typeof create_toast === 'function') create_toast('danger', 'Request failed', 'Please try again.'); })
        .finally(function(){ if (btn) btn.disabled = false; });
    });
  }
  document.querySelectorAll('.ajax-form-lite').forEach(wire);

  // ── "…" actions menu ──
  function iaPositionMenu(menu, btn) {
    var rect = btn.getBoundingClientRect();
    var spaceBelow = window.innerHeight - rect.bottom;
    var left = Math.max(8, rect.right - 200);
    if (spaceBelow < 240) { menu.style.top = ''; menu.style.bottom = (window.innerHeight - rect.top + 6) + 'px'; }
    else { menu.style.bottom = ''; menu.style.top = (rect.bottom + 6) + 'px'; }
    menu.style.left = left + 'px';
  }
  window.iaToggleMenu = function(btn) {
    var menu = btn.nextElementSibling;
    var isOpen = menu.classList.contains('is-open');
    document.querySelectorAll('.al-actions-menu.is-open').forEach(function(m){ m.classList.remove('is-open'); m.style.top=''; m.style.bottom=''; if (m.previousElementSibling) m.previousElementSibling.classList.remove('is-open'); });
    if (!isOpen) { iaPositionMenu(menu, btn); menu.classList.add('is-open'); btn.classList.add('is-open'); }
  };
  document.addEventListener('click', function(e){
    if (!e.target.closest('.al-actions-wrap')) {
      document.querySelectorAll('.al-actions-menu.is-open').forEach(function(m){ m.classList.remove('is-open'); m.style.top=''; m.style.bottom=''; if (m.previousElementSibling) m.previousElementSibling.classList.remove('is-open'); });
    }
  });
  window.addEventListener('scroll', function(){
    document.querySelectorAll('.al-actions-menu.is-open').forEach(function(m){ if (m.previousElementSibling) iaPositionMenu(m, m.previousElementSibling); });
  }, true);

  // ── Manual editing: track unsaved state across both the table and the modals ──
  var unsavedPill = document.getElementById('ia-unsaved-pill');
  var dirty = false;
  function markDirty(inp) {
    inp.classList.add('is-dirty');
    dirty = true;
    if (unsavedPill) unsavedPill.classList.add('show');
  }
  function wireFieldTracking(root) {
    root.querySelectorAll('.ia-field-input').forEach(function(inp){
      if (inp.dataset.iaWired) return;
      inp.dataset.iaWired = '1';
      inp.addEventListener('input', function(){ markDirty(inp); });
      inp.addEventListener('change', function(){ markDirty(inp); if (inp.dataset.field === 'current_rank') iaSyncCurrentDivisionVisibility(); });
    });
    root.querySelectorAll('.ia-field-checkbox').forEach(function(inp){
      if (inp.dataset.iaWired) return;
      inp.dataset.iaWired = '1';
      inp.addEventListener('change', function(){ dirty = true; if (unsavedPill) unsavedPill.classList.add('show'); });
    });
  }
  wireFieldTracking(document);

  // Custom dark dropdowns. Menus are fixed-position so they are not clipped by the scrollable table.
  function iaPositionCustomSelect(sel) {
    var btn = sel.querySelector('.ia-custom-select-btn');
    var menu = sel.querySelector('.ia-custom-select-menu');
    if (!btn || !menu) return;
    var rect = btn.getBoundingClientRect();
    var menuWidth = Math.max(rect.width, 150);
    var spaceBelow = window.innerHeight - rect.bottom;
    menu.style.minWidth = menuWidth + 'px';
    menu.style.left = Math.max(8, Math.min(rect.left, window.innerWidth - menuWidth - 8)) + 'px';
    if (spaceBelow < 190 && rect.top > spaceBelow) {
      menu.style.top = '';
      menu.style.bottom = Math.max(8, window.innerHeight - rect.top + 6) + 'px';
    } else {
      menu.style.bottom = '';
      menu.style.top = Math.min(rect.bottom + 6, window.innerHeight - 60) + 'px';
    }
  }
  function iaCloseCustomSelects(except) {
    document.querySelectorAll('.ia-custom-select.is-open').forEach(function(sel){
      if (sel !== except) sel.classList.remove('is-open');
    });
  }
  document.querySelectorAll('.ia-custom-select').forEach(function(sel){
    var btn = sel.querySelector('.ia-custom-select-btn');
    var label = btn ? btn.querySelector('span') : null;
    var hidden = sel.previousElementSibling && sel.previousElementSibling.classList.contains('ia-custom-select-value') ? sel.previousElementSibling : null;
    if (!btn || !hidden) return;
    btn.addEventListener('click', function(e){
      e.preventDefault();
      e.stopPropagation();
      var willOpen = !sel.classList.contains('is-open');
      iaCloseCustomSelects(sel);
      sel.classList.toggle('is-open', willOpen);
      if (willOpen) iaPositionCustomSelect(sel);
    });
    sel.querySelectorAll('.ia-custom-select-opt').forEach(function(opt){
      opt.addEventListener('click', function(e){
        e.preventDefault();
        e.stopPropagation();
        hidden.value = opt.dataset.value;
        hidden.dispatchEvent(new Event('change', {bubbles:true}));
        if (label) label.textContent = opt.dataset.label || opt.textContent;
        sel.querySelectorAll('.ia-custom-select-opt').forEach(function(o){ o.classList.remove('is-active'); });
        opt.classList.add('is-active');
        sel.classList.remove('is-open');
        markDirty(hidden);
        if (['current_rank','flex_rank','previous_rank'].indexOf(hidden.dataset.field) !== -1) iaSyncLolRankLpDivisionVisibility();
      });
    });
  });

  function iaRankIsMasterPlus(value) {
    return parseInt(value || '0', 10) >= 8;
  }

  function iaSetColumnHidden(field, hidden) {
    document.querySelectorAll('[data-col="' + field + '"]').forEach(function(el){
      el.classList.toggle('ia-col-hidden', hidden);
    });
  }

  function iaSetFieldDisabled(rowId, field, disabled, fallbackValue) {
    var input = document.querySelector('.ia-field-input[data-row="' + rowId + '"][data-field="' + field + '"]');
    if (!input) return;

    // Important: never hide single TD cells in a table.
    // Hiding only one row cell shifts all following cells under the wrong headers.
    if (disabled) {
      if (input.value && input.value !== '0') {
        input.dataset.previousValue = input.value;
      }
      input.value = fallbackValue;
      input.disabled = true;
      input.classList.add('is-disabled-by-rank');
    } else {
      if ((input.value === '0' || input.value === '') && input.dataset.previousValue) {
        input.value = input.dataset.previousValue;
      }
      input.disabled = false;
      input.classList.remove('is-disabled-by-rank');
    }
  }

  function iaSyncLolRankLpDivisionVisibility() {
    var currentRankInputs = document.querySelectorAll('.ia-field-input[data-field="current_rank"]');

    var currentNeedsDivision = false;
    var currentNeedsLp = false;
    var flexNeedsDivision = false;
    var flexNeedsLp = false;
    var previousNeedsDivision = false;
    var previousNeedsLp = false;

    currentRankInputs.forEach(function(rankInput){
      var rowId = rankInput.dataset.row;

      var rank = parseInt(rankInput.value || '0', 10);
      var isMasterPlus = rank >= 8;
      if (rank > 0 && rank < 8) currentNeedsDivision = true;
      if (isMasterPlus) currentNeedsLp = true;

      // Current rank: below Master shows division, Master+ shows LP.
      iaSetFieldDisabled(rowId, 'current_division', isMasterPlus || rank <= 0, '0');
      iaSetFieldDisabled(rowId, 'current_lp', !isMasterPlus, '0');

      // Flex rank: below Master shows division, Master+ shows LP, Unranked shows neither.
      var flexRankInput = document.querySelector('.ia-field-input[data-row="' + rowId + '"][data-field="flex_rank"]');
      var flexRank = flexRankInput ? parseInt(flexRankInput.value || '0', 10) : 0;
      var flexMasterPlus = flexRank >= 8;
      if (flexRank > 0 && flexRank < 8) flexNeedsDivision = true;
      if (flexMasterPlus) flexNeedsLp = true;
      iaSetFieldDisabled(rowId, 'flex_division', flexMasterPlus || flexRank <= 0, '0');
      iaSetFieldDisabled(rowId, 'flex_lp', !flexMasterPlus, '0');

      // Previous rank: below Master shows division, Master+ shows LP, Unranked shows neither.
      var previousRankInput = document.querySelector('.ia-field-input[data-row="' + rowId + '"][data-field="previous_rank"]');
      var previousRank = previousRankInput ? parseInt(previousRankInput.value || '0', 10) : 0;
      var previousMasterPlus = previousRank >= 8;
      if (previousRank > 0 && previousRank < 8) previousNeedsDivision = true;
      if (previousMasterPlus) previousNeedsLp = true;
      iaSetFieldDisabled(rowId, 'previous_division', previousMasterPlus || previousRank <= 0, '0');
      iaSetFieldDisabled(rowId, 'previous_lp', !previousMasterPlus, '0');
    });

    // Hide complete columns only. This keeps the table header and cells aligned.
    iaSetColumnHidden('current_division', !currentNeedsDivision);
    iaSetColumnHidden('current_lp', !currentNeedsLp);

    iaSetColumnHidden('flex_division', !flexNeedsDivision);
    iaSetColumnHidden('flex_lp', !flexNeedsLp);

    iaSetColumnHidden('previous_division', !previousNeedsDivision);
    iaSetColumnHidden('previous_lp', !previousNeedsLp);
  }

  iaSyncLolRankLpDivisionVisibility();

  document.addEventListener('change', function(e){
    var input = e.target.closest('.ia-field-input');
    if (input && ['current_rank','flex_rank','previous_rank'].indexOf(input.dataset.field) !== -1) {
      iaSyncLolRankLpDivisionVisibility();
    }
  });

  document.addEventListener('click', function(e){
    if (!e.target.closest('.ia-custom-select-menu') && !e.target.closest('.ia-custom-select')) iaCloseCustomSelects(null);
  });
  window.addEventListener('resize', function(){ document.querySelectorAll('.ia-custom-select.is-open').forEach(iaPositionCustomSelect); });
  window.addEventListener('scroll', function(){ document.querySelectorAll('.ia-custom-select.is-open').forEach(iaPositionCustomSelect); }, true);


  // ── Searchable custom icon dropdown for game selection ──
  var gameBtn = document.getElementById('ia-game-select-btn');
  var gameMenu = document.getElementById('ia-game-select-menu');
  var gameValue = document.getElementById('ia-game-select-value');
  var gameLabel = document.getElementById('ia-game-select-label');
  var gameSearch = document.getElementById('ia-game-select-search');
  var gameEmpty = document.getElementById('ia-game-select-empty');
  function filterGames() {
    if (!gameMenu) return;
    var q = String(gameSearch && gameSearch.value || '').trim().toLowerCase();
    var visible = 0;
    gameMenu.querySelectorAll('.ia-game-select-opt').forEach(function(opt){
      var haystack = ((opt.dataset.label || '') + ' ' + (opt.dataset.value || '')).toLowerCase();
      var show = q === '' || haystack.indexOf(q) !== -1;
      opt.style.display = show ? '' : 'none';
      if (show) visible++;
    });
    if (gameEmpty) gameEmpty.style.display = visible ? 'none' : 'block';
  }
  if (gameBtn && gameMenu) {
    gameBtn.addEventListener('click', function(e){
      e.stopPropagation();
      gameMenu.classList.toggle('is-open');
      if (gameMenu.classList.contains('is-open') && gameSearch) {
        gameSearch.value = '';
        filterGames();
        setTimeout(function(){ gameSearch.focus(); }, 0);
      }
    });
    if (gameSearch) {
      gameSearch.addEventListener('click', function(e){ e.stopPropagation(); });
      gameSearch.addEventListener('input', filterGames);
    }
    gameMenu.querySelectorAll('.ia-game-select-opt').forEach(function(opt){
      opt.addEventListener('click', function(){
        gameValue.value = opt.dataset.value;
        gameLabel.textContent = opt.dataset.label;
        var currentImg = gameBtn.querySelector('img');
        var nextImg = opt.querySelector('img');
        if (currentImg && nextImg) currentImg.src = nextImg.src;
        gameMenu.querySelectorAll('.ia-game-select-opt').forEach(function(o){ o.classList.remove('is-active'); });
        opt.classList.add('is-active');
        gameMenu.classList.remove('is-open');
      });
    });
    document.addEventListener('click', function(e){ if (!e.target.closest('#ia-game-select')) { gameMenu.classList.remove('is-open'); } });
  }

  function iaPriceEuroToCents(value) {
    value = String(value || '').trim();
    if (value === '') return '';
    value = value.replace(/€/g, '').replace(/\s/g, '');

    // Accept both German and English decimal notation:
    // 10,99 => 1099, 10.99 => 1099, 1.234,56 => 123456
    if (value.indexOf(',') !== -1) {
      value = value.replace(/\./g, '').replace(',', '.');
    }

    var amount = parseFloat(value);
    if (!isFinite(amount)) return '';
    return String(Math.round(amount * 100));
  }

  function collectFormData() {
    var fd = new FormData();
    var table = document.getElementById('ia-edit-table') || document;
    var rows = {};

    fd.append('action', 'seller_import_save_rows');
    fd.append('batch_id', BATCH_ID);

    // Important: only collect the editable table values.
    // Hidden modal/store fields can contain old values and would overwrite dropdown changes in PHP.
    table.querySelectorAll('.ia-field-input').forEach(function(inp){
      var row = inp.dataset.row;
      var field = inp.dataset.field;
      if (!row || !field) return;

      if (!rows[row]) rows[row] = {};

      var value = inp.value;
      if (field === 'price') {
        value = iaPriceEuroToCents(value);
      }

      rows[row][field] = value;
    });

    table.querySelectorAll('.ia-field-checkbox').forEach(function(inp){
      var row = inp.dataset.row;
      var field = inp.dataset.field;
      if (!row || !field) return;
      if (!rows[row]) rows[row] = {};
      rows[row][field] = inp.checked ? '1' : '0';
    });

    Object.keys(rows).forEach(function(row){
      Object.keys(rows[row]).forEach(function(field){
        fd.append('rows[' + row + '][' + field + ']', rows[row][field]);
      });
    });

    return fd;
  }
  function saveChanges() {
    if (BATCH_ID <= 0) return;
    if (!document.querySelector('.ia-field-input')) return;
    var fd = collectFormData();
    var btns = [document.getElementById('save-changes-btn'), document.getElementById('account-details-save')];
    btns.forEach(function(b){ if (b) b.disabled = true; });
    fetch(AJAX_ENDPOINT, {method:'POST', body:fd, credentials:'same-origin'})
      .then(function(r){ return r.json(); })
      .then(function(json){
        if (json.sendToast && typeof create_toast === 'function') { create_toast(json.sendToast.type, json.sendToast.title || '', json.sendToast.message || ''); }
        if (json.refreshPage) { window.location.reload(); }
      })
      .catch(function(){ if (typeof create_toast === 'function') create_toast('danger', 'Request failed', 'Please try again.'); })
      .finally(function(){ btns.forEach(function(b){ if (b) b.disabled = false; }); });
  }
  var saveBtn = document.getElementById('save-changes-btn');
  if (saveBtn) { saveBtn.addEventListener('click', saveChanges); }
  var detailsSaveBtn = document.getElementById('account-details-save');
  if (detailsSaveBtn) { detailsSaveBtn.addEventListener('click', saveChanges); }
  document.addEventListener('keydown', function(e){
    if ((e.ctrlKey || e.metaKey) && (e.key === 's' || e.key === 'S')) {
      if (document.querySelector('.ia-field-input')) { e.preventDefault(); saveChanges(); }
    }
  });
  window.addEventListener('beforeunload', function(e){ if (dirty) { e.preventDefault(); e.returnValue = ''; } });
  function iaOpenImportConfirm() {
    if (dirty) {
      if (typeof create_toast === 'function') { create_toast('warning', 'Unsaved changes', 'Please click "Save Changes" first, then import.'); }
      return;
    }
    var modalEl = document.getElementById('import-confirm-modal');
    if (modalEl && window.bootstrap && bootstrap.Modal) {
      bootstrap.Modal.getOrCreateInstance(modalEl).show();
    }
  }
  var openImportConfirmBtn = document.getElementById('open-import-confirm-btn');
  if (openImportConfirmBtn) {
    openImportConfirmBtn.addEventListener('click', function(e){
      e.preventDefault();
      iaOpenImportConfirm();
    });
  }
  var confirmImportBtn = document.getElementById('ia-confirm-import-submit');
  if (confirmImportBtn) {
    confirmImportBtn.addEventListener('click', function(){
      var form = document.getElementById('import-all-accounts-form');
      var modalEl = document.getElementById('import-confirm-modal');
      if (modalEl && window.bootstrap && bootstrap.Modal) { bootstrap.Modal.getOrCreateInstance(modalEl).hide(); }
      if (form) {
        if (form.requestSubmit) form.requestSubmit();
        else form.dispatchEvent(new Event('submit', {cancelable:true}));
      }
    });
  }

  // ── Account Details modal: re-parent the row's hidden field set in/out ──
  var detailsStore = document.getElementById('ia-details-store');
  var detailsBody = document.getElementById('account-details-modal-body');
  var detailsTitleEl = document.getElementById('account-details-title');
  window.openDetailsModal = function(rowId, title) {
    if (currentDetailsRow !== null) { returnDetailsPane(); }
    var pane = document.getElementById('ia-details-' + rowId);
    if (!pane) return;
    detailsBody.appendChild(pane);
    detailsTitleEl.textContent = title || ('Account #' + rowId);
    currentDetailsRow = rowId;
  };
  function returnDetailsPane() {
    if (currentDetailsRow === null) return;
    var pane = document.getElementById('ia-details-' + currentDetailsRow);
    if (pane) { detailsStore.appendChild(pane); }
    currentDetailsRow = null;
  }
  document.addEventListener('click', function(e){
    var detailsBtn = e.target.closest('.ia-open-details');
    if (detailsBtn) { window.openDetailsModal(parseInt(detailsBtn.dataset.rowId, 10), detailsBtn.dataset.rowTitle); return; }
    var imagesBtn = e.target.closest('.ia-open-images');
    if (imagesBtn) { window.openImagesModal(parseInt(imagesBtn.dataset.rowId, 10), imagesBtn.dataset.rowTitle); return; }
  });
  var detailsModalEl = document.getElementById('account-details-modal');
  if (detailsModalEl) { detailsModalEl.addEventListener('hidden.bs.modal', returnDetailsPane); }

  // ── Images manager modal: sortable, removable, multi-upload ──
  function updateThumbStrip(rowId) {
    var strip = document.getElementById('ia-thumbstrip-' + rowId);
    var imgs = rowImages[rowId] || [];
    if (strip) {
      strip.innerHTML = '';
      imgs.slice(0, 3).forEach(function(img){ var el = document.createElement('img'); el.src = img.url; strip.appendChild(el); });
      if (imgs.length > 3) { var more = document.createElement('span'); more.className = 'ia-thumb-more'; more.textContent = '+' + (imgs.length - 3); strip.appendChild(more); }
    }
    var countBtn = strip ? strip.parentElement.querySelector('.al-view-btn') : null;
    if (countBtn) { countBtn.innerHTML = '<i class="fa-regular fa-images"></i> ' + imgs.length + ' image' + (imgs.length !== 1 ? 's' : ''); }
  }

  function renderImagesList(rowId) {
    var container = document.getElementById('images-modal-list');
    var imgs = rowImages[rowId] || [];
    container.innerHTML = '';
    if (!imgs.length) {
      container.innerHTML = '<div class="ia-img-empty">No images yet — drop some above.</div>';
      return;
    }
    imgs.forEach(function(img, idx){
      var row = document.createElement('div');
      row.className = 'ia-img-row';
      row.draggable = true;
      row.dataset.idx = idx;
      row.innerHTML =
        '<span class="ia-img-handle"><i class="fa-solid fa-grip-vertical"></i></span>' +
        '<img class="ia-img-thumb" src="' + img.url + '" alt="">' +
        '<div class="ia-img-meta"><div class="ia-img-name">' + (idx === 0 ? '★ ' : '') + (img.name || ('image_' + img.id)) + '</div><div class="ia-img-size">' + formatSize(img.size) + (idx === 0 ? ' · Cover photo' : '') + '</div></div>' +
        '<button type="button" class="ia-img-remove" data-id="' + img.id + '" title="Remove"><i class="fa-regular fa-trash"></i></button>';
      container.appendChild(row);
    });
    wireImgDragSort(container, rowId);
    container.querySelectorAll('.ia-img-remove').forEach(function(btn){
      btn.addEventListener('click', function(){
        var id = parseInt(btn.dataset.id, 10);
        rowImages[rowId] = (rowImages[rowId] || []).filter(function(i){ return i.id !== id; });
        renderImagesList(rowId);
        updateThumbStrip(rowId);
        persistImageOrder(rowId);
      });
    });
  }

  function wireImgDragSort(container, rowId) {
    var dragIdx = null;
    container.querySelectorAll('.ia-img-row').forEach(function(el){
      el.addEventListener('dragstart', function(){ dragIdx = +el.dataset.idx; el.classList.add('dragging'); });
      el.addEventListener('dragend', function(){ el.classList.remove('dragging'); });
      el.addEventListener('dragover', function(e){ e.preventDefault(); });
      el.addEventListener('drop', function(e){
        e.preventDefault();
        var dropIdx = +el.dataset.idx;
        if (dragIdx === null || dragIdx === dropIdx) return;
        var imgs = rowImages[rowId];
        var moved = imgs.splice(dragIdx, 1)[0];
        imgs.splice(dropIdx, 0, moved);
        renderImagesList(rowId);
        updateThumbStrip(rowId);
        persistImageOrder(rowId);
      });
    });
  }

  function normalizeImg(r) { return {id: r.id, url: r.url, name: r.original_name || r.filename || ('image_' + r.id), size: r.size_bytes || 0}; }

  function persistImageOrder(rowId) {
    var fd = new FormData();
    fd.append('action', 'seller_import_row_set_images');
    fd.append('row_id', rowId);
    (rowImages[rowId] || []).forEach(function(img){ fd.append('image_ids[]', img.id); });
    fetch(AJAX_ENDPOINT, {method:'POST', body:fd, credentials:'same-origin'})
      .then(function(r){ return r.json(); })
      .then(function(json){
        if (json.images) { rowImages[rowId] = json.images.map(normalizeImg); renderImagesList(rowId); updateThumbStrip(rowId); }
        if (json.sendToast && typeof create_toast === 'function') { create_toast(json.sendToast.type, json.sendToast.title || '', json.sendToast.message || ''); }
      })
      .catch(function(){ if (typeof create_toast === 'function') create_toast('danger', 'Request failed', 'Please try again.'); });
  }

  function uploadImagesForRow(rowId, files) {
    if (!files || !files.length) return;
    var fd = new FormData();
    fd.append('action', 'seller_import_row_upload_images');
    fd.append('row_id', rowId);
    for (var i = 0; i < files.length; i++) { fd.append('images[]', files[i]); }
    fetch(AJAX_ENDPOINT, {method:'POST', body:fd, credentials:'same-origin'})
      .then(function(r){ return r.json(); })
      .then(function(json){
        if (json.images) { rowImages[rowId] = json.images.map(normalizeImg); renderImagesList(rowId); updateThumbStrip(rowId); }
        if (json.sendToast && typeof create_toast === 'function') { create_toast(json.sendToast.type, json.sendToast.title || '', json.sendToast.message || ''); }
      })
      .catch(function(){ if (typeof create_toast === 'function') create_toast('danger', 'Upload failed', 'Please try again.'); });
  }

  window.openImagesModal = function(rowId, title) {
    currentImagesRow = rowId;
    document.getElementById('images-modal-title').textContent = title || ('Account #' + rowId);
    renderImagesList(rowId);
  };
  var imgInput = document.getElementById('ia-img-input');
  if (imgInput) {
    imgInput.addEventListener('change', function(){
      if (currentImagesRow !== null) { uploadImagesForRow(currentImagesRow, imgInput.files); }
      imgInput.value = '';
    });
  }
  var imagesModalEl = document.getElementById('images-modal');
  if (imagesModalEl) {
    imagesModalEl.addEventListener('hidden.bs.modal', function(){ currentImagesRow = null; });
    // basic drag-and-drop file support onto the dropzone label
    var imgDropLabel = document.getElementById('ia-img-drop-label');
    if (imgDropLabel) {
      imgDropLabel.addEventListener('dragover', function(e){ e.preventDefault(); });
      imgDropLabel.addEventListener('drop', function(e){
        e.preventDefault();
        if (currentImagesRow !== null && e.dataTransfer && e.dataTransfer.files) { uploadImagesForRow(currentImagesRow, e.dataTransfer.files); }
      });
    }
  }
})();
</script>

