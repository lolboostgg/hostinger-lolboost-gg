<?= $this->layout('admin/layouts/main', ['meta' => ['title' => 'Account #' . ($account['id'] ?? '') . ' | Admin']]) ?>

<?php
$account = $account ?? [];
$seller  = $seller  ?? null;
$buyer   = $buyer   ?? null;
$chat    = $chat    ?? [];
$id      = (int)($account['id'] ?? 0);
$sold    = !empty($account['sold']) && (int)$account['sold'] === 1;
$active  = (int)($account['active'] ?? 1);


// Resolve current admin for chat avatar/name. The admins table contains the real avatar URL in `icon`.
$admin = $admin ?? ($currentAdmin ?? ($currentUser ?? ($user ?? null)));
if (!is_array($admin)) $admin = [];

$sessionAdmin = $_SESSION['admin'] ?? ($_SESSION['user'] ?? []);
if (empty($admin) && is_array($sessionAdmin)) {
    $admin = $sessionAdmin;
}

$adminId = (int)($admin['id'] ?? (defined('ADMIN_ID') ? ADMIN_ID : ($_SESSION['admin_id'] ?? ($_SESSION['user_id'] ?? 0))));

// If the controller did not pass $admin, try to load it from DB using the same helpers/routes style as the boosting order view.
if ($adminId > 0 && (empty($admin['icon']) || empty($admin['username']))) {
    try {
        if (function_exists('db_get_row')) {
            $adminRow = db_get_row('admins', ['id' => $adminId, 'select' => 'id,username,icon'], 1);
            if (is_array($adminRow) && !empty($adminRow)) {
                $admin = array_merge($admin, $adminRow);
            }
        }
    } catch (Throwable $e) {}

    try {
        if ((empty($admin['icon']) || empty($admin['username'])) && isset($db) && is_object($db) && method_exists($db, 'row')) {
            $adminRow = $db->row('SELECT id, username, icon FROM admins WHERE id = ? LIMIT 1', $adminId);
            if (is_array($adminRow) && !empty($adminRow)) {
                $admin = array_merge($admin, $adminRow);
            }
        }
    } catch (Throwable $e) {}

    $pdoCandidate = null;
    foreach (['pdo', 'db', 'conn', 'database'] as $__dbVar) {
        if (isset($$__dbVar) && $$__dbVar instanceof PDO) {
            $pdoCandidate = $$__dbVar;
            break;
        }
    }

    if ($pdoCandidate instanceof PDO) {
        try {
            $stmt = $pdoCandidate->prepare('SELECT id, username, icon FROM admins WHERE id = ? LIMIT 1');
            $stmt->execute([$adminId]);
            $adminRow = $stmt->fetch(PDO::FETCH_ASSOC);
            if (is_array($adminRow) && !empty($adminRow)) {
                $admin = array_merge($admin, $adminRow);
            }
        } catch (Throwable $e) {
            // Keep fallback initials if DB is not available in this template scope.
        }
    } else {
        foreach (['mysqli', 'mysql', 'conn', 'db'] as $__dbVar) {
            if (isset($$__dbVar) && $$__dbVar instanceof mysqli) {
                try {
                    $stmt = $$__dbVar->prepare('SELECT id, username, icon FROM admins WHERE id = ? LIMIT 1');
                    if ($stmt) {
                        $stmt->bind_param('i', $adminId);
                        $stmt->execute();
                        $res = $stmt->get_result();
                        $adminRow = $res ? $res->fetch_assoc() : null;
                        if (is_array($adminRow) && !empty($adminRow)) {
                            $admin = array_merge($admin, $adminRow);
                        }
                        $stmt->close();
                    }
                } catch (Throwable $e) {
                    // Keep fallback initials if DB is not available in this template scope.
                }
                break;
            }
        }
    }
}

$adminIcon = $admin['icon']
    ?? $admin['avatar']
    ?? $admin['image']
    ?? $admin['profile_image']
    ?? $admin['profile_picture']
    ?? null;

// If only a filename is stored, use the upload folder from your admins.icon screenshot.
if ($adminIcon && !preg_match('~^(https?:)?//|^/~i', (string)$adminIcon)) {
    $adminIcon = '/public/uploads/icons/' . ltrim((string)$adminIcon, '/');
}

$adminInitial = strtoupper(substr($admin['username'] ?? $admin['name'] ?? 'A', 0, 1));

$h = fn($v) => htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');
$chatText = fn($v) => html_entity_decode((string)$v, ENT_QUOTES | ENT_HTML5, 'UTF-8');

function _chat_safe_url($url) {
    $url = html_entity_decode((string)$url, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    return preg_match('~^https?://~i', $url) ? $url : '#';
}

function _chat_linkify_plain($text) {
    $safe = htmlspecialchars(html_entity_decode((string)$text, ENT_QUOTES | ENT_HTML5, 'UTF-8'), ENT_QUOTES, 'UTF-8');
    return preg_replace_callback('~https?://[^\s<]+~i', function ($m) {
        $url = rtrim($m[0], '.,;:!?)"]');
        $tail = substr($m[0], strlen($url));
        return '<a href="' . htmlspecialchars(_chat_safe_url($url), ENT_QUOTES, 'UTF-8') . '" target="_blank" rel="noopener noreferrer">'
             . htmlspecialchars($url, ENT_QUOTES, 'UTF-8') . '</a>' . htmlspecialchars($tail, ENT_QUOTES, 'UTF-8');
    }, $safe);
}

function _chat_render_html($text) {
    $decoded = html_entity_decode((string)$text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    $html = strip_tags($decoded, '<a><br><strong><b><em><i><u><p><span>');
    $html = preg_replace('/\son\w+\s*=\s*("[^"]*"|\'[^\']*\'|[^\s>]+)/i', '', $html);
    $html = preg_replace('/\s(?:style|class|id)\s*=\s*("[^"]*"|\'[^\']*\'|[^\s>]+)/i', '', $html);
    $html = preg_replace_callback('/<a\s+[^>]*href\s*=\s*(["\'])(.*?)\1[^>]*>/i', function ($m) {
        $url = _chat_safe_url($m[2]);
        return '<a href="' . htmlspecialchars($url, ENT_QUOTES, 'UTF-8') . '" target="_blank" rel="noopener noreferrer">';
    }, $html);
    $html = preg_replace('/<a(?!\s+href=)[^>]*>/i', '<a href="#" target="_blank" rel="noopener noreferrer">', $html);
    return $html;
}


function _account_chat_fallback_icon($sender) {
    $base = defined('ICON_URL') ? rtrim((string)ICON_URL, '/') : '/public/uploads/icons';
    $sender = strtolower((string)$sender);
    if ($sender === 'admin')  return $base . '/03ce541a1f4bf8b06c924439ffcc8173.png';
    if ($sender === 'seller') return $base . '/25d1ea33c481dbacd2f2c294408d38cd.png';
    if ($sender === 'booster') return $base . '/25d1ea33c481dbacd2f2c294408d38cd.png';
    return $base . '/8515d2c8c74a3f9bae054026f6549d91.png';
}

function _account_chat_msg_icon($msg) {
    if (!is_array($msg)) return null;
    $icon = $msg['sender_icon']
        ?? $msg['sender_avatar']
        ?? $msg['avatar']
        ?? $msg['icon']
        ?? $msg['admin_icon']
        ?? null;
    return $icon ?: null;
}

$rankNames = [0=>'Unranked',1=>'Iron',2=>'Bronze',3=>'Silver',4=>'Gold',5=>'Platinum',6=>'Emerald',7=>'Diamond',8=>'Master',9=>'Grandmaster',10=>'Challenger'];
$divNames  = [1=>'IV',2=>'III',3=>'II',4=>'I'];

function _rank_label($rank, $division, $lp) {
    global $rankNames, $divNames;
    $n = $rankNames[(int)$rank] ?? 'Unknown';
    if ((int)$rank >= 1 && (int)$rank <= 7) $n .= ' ' . ($divNames[(int)$division] ?? '');
    if ($lp && $lp != 0) $n .= ' ' . (int)$lp . 'LP';
    return $n;
}

$rankLabel     = _rank_label($account['current_rank'] ?? 0, $account['current_division'] ?? 1, $account['current_lp'] ?? null);
$flexLabel     = !empty($account['flex_rank']) ? _rank_label($account['flex_rank'], $account['flex_division'] ?? 1, $account['flex_lp'] ?? null) : null;
$prevLabel     = !empty($account['previous_rank']) ? _rank_label($account['previous_rank'], $account['previous_division'] ?? 1, $account['previous_lp'] ?? null) : null;
$priceCents    = (int)($account['price'] ?? 0);
$priceEur      = number_format($priceCents / 100, 2);
$sellerFeePct  = (isset($seller['fee_percent']) && $seller['fee_percent'] !== '' && $seller['fee_percent'] !== null) ? (float)$seller['fee_percent'] : 15.0;
$sellerCutCents = (int) round($priceCents * (1 - ($sellerFeePct / 100)));
$earningsEur   = number_format($sellerCutCents / 100, 2);
$server        = strtoupper($account['server'] ?? '');
$level         = $account['level'] ?? null;
$honorLevel    = $account['honor_level'] ?? null;
$blueEssence   = $account['blue_essence'] ?? null;
$skinCount     = $account['skin_count'] ?? null;

$game   = strtolower(trim((string)($account['game'] ?? 'lol')));
$isVal  = ($game === 'val');

$champs = !empty($account['champions']) ? array_values(array_filter(explode('|', $account['champions']))) : [];
$skins  = !empty($account['skins'])     ? array_values(array_filter(explode('|', $account['skins'])))     : [];
$roles  = !empty($account['roles'])     ? array_values(array_filter(explode('|', $account['roles'])))     : [];

// Game data (val-specific fields live here)
$gameData = [];
if (!empty($account['game_data'])) {
    $gd = json_decode((string)$account['game_data'], true);
    if (is_array($gd)) $gameData = $gd;
}

// Valorant agents
$valAgentsList = [];
if (!empty($gameData['agents'])) {
    $ag = $gameData['agents'];
    if (is_string($ag)) $ag = preg_split('/[|,]/', $ag) ?: [];
    $valAgentsList = array_values(array_filter(array_map('trim', is_array($ag) ? $ag : [])));
}
$valAgentsManualCount = count($valAgentsList);
$valAgentsDisplayCount = $valAgentsManualCount > 0
    ? $valAgentsManualCount
    : (isset($account['val_agent_count']) && $account['val_agent_count'] !== null && $account['val_agent_count'] !== ''
       ? (int)$account['val_agent_count'] : 0);

// LoL champion/skin effective counts (manual list preferred, fallback to count field)
$champsDisplayCount = count($champs) > 0
    ? count($champs)
    : (isset($account['champion_count']) && $account['champion_count'] !== null && $account['champion_count'] !== ''
       ? (int)$account['champion_count'] : 0);
$skinsDisplayCount = count($skins) > 0
    ? count($skins)
    : (isset($account['skin_count']) && $account['skin_count'] !== null && $account['skin_count'] !== ''
       ? (int)$account['skin_count'] : 0);

// Val rank label
$valRank = (int)($account['rank'] ?? $gameData['val_rank'] ?? 0);
$valRankLabel = function_exists('util_get_val_rank') ? util_get_val_rank($valRank) : ($account['rank_label'] ?? 'Unranked');
$valWeaponSkins = (int)($gameData['val_weapon_skins'] ?? 0);
$valPoints      = (int)($gameData['val_points'] ?? 0);
$valRadianite   = (int)($gameData['val_radianite'] ?? 0);
$valWinrate     = (int)($gameData['val_winrate'] ?? 0);
?>

<style>
/* ── Override card base ── */
.admin-account-view .card {
  background:var(--bs-card-bg)!important;
  border:var(--bs-card-border-width) solid var(--bs-card-border-color)!important;
  border-radius:22px!important;
  box-shadow:none!important;
}
.admin-account-view .card::before { display:none!important; }
.admin-account-view .order-chat-card { overflow:hidden; }

/* ── Head card (av-head) ── */
.av-head {
  border-radius:22px;overflow:hidden;margin-bottom:20px;
  border:1px solid var(--bs-card-border-color);
  background:#25282a;
}
.av-head-body { padding:20px 22px;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;border-bottom:1px solid var(--bs-card-border-color); }
.av-head-left { display:flex;align-items:center;gap:14px; }

/* Status pills */
.av-status { display:inline-flex;align-items:center;gap:.35rem;padding:4px 11px;border-radius:99px;font-size:.75rem;font-weight:800; }
.av-status--active   { background:rgba(74,222,128,.12);border:1px solid rgba(74,222,128,.25);color:#4ade80; }
.av-status--sold     { background:rgba(251,113,133,.12);border:1px solid rgba(251,113,133,.25);color:#fb7185; }
.av-status--unlisted { background:rgba(255,255,255,.06);border:1px solid rgba(255,255,255,.12);color:rgba(255,255,255,.6); }

/* Meta pills row */
.av-meta-row { position:relative;z-index:1;display:flex;flex-wrap:wrap;gap:6px;padding:14px 22px 16px; }
.av-meta-pill { display:inline-flex;align-items:center;gap:.3rem;padding:4px 11px;border-radius:99px;font-size:.75rem;font-weight:700;background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.08);color:rgba(255,255,255,.7); }
.av-meta-pill strong { color:rgba(255,255,255,.92); }

/* Action buttons */
.av-actions { display:flex;gap:8px;flex-wrap:wrap;align-items:center; }
.av-btn-ghost { display:inline-flex;align-items:center;gap:.4rem;padding:7px 14px;border-radius:11px;font-size:.83rem;font-weight:700;background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.09);color:rgba(255,255,255,.7);cursor:pointer;transition:background .12s;text-decoration:none; }
.av-btn-ghost:hover { background:rgba(255,255,255,.09);color:#fff; }
.av-btn-primary { display:inline-flex;align-items:center;gap:.4rem;padding:7px 16px;border-radius:11px;font-size:.83rem;font-weight:800;background:linear-gradient(135deg,#6d5cff,#b05cff);border:none;color:#fff;cursor:pointer;transition:opacity .15s,transform .12s;text-decoration:none; }
.av-btn-primary:hover { opacity:.88;transform:translateY(-1px);color:#fff; }
.av-btn-warning { display:inline-flex;align-items:center;gap:.4rem;padding:7px 16px;border-radius:11px;font-size:.83rem;font-weight:800;background:rgba(251,191,36,.12);border:1px solid rgba(251,191,36,.22);color:#fbbf24;cursor:pointer;transition:background .12s; }
.av-btn-warning:hover { background:rgba(251,191,36,.20); }

/* ── Sidebar cards ── */
.av-sidebar-card {
  border-radius:18px;
  border:1px solid rgba(255,255,255,.07);
  background:#25282a;
  overflow:hidden;
  box-shadow:0 2px 16px rgba(0,0,0,.2);
}
.av-sc-header {
  display:flex;align-items:center;gap:8px;
  padding:12px 16px;
  border-bottom:1px solid rgba(255,255,255,.06);
  background:rgba(255,255,255,.02);
}
.av-sc-icon {
  width:26px;height:26px;border-radius:8px;flex-shrink:0;
  display:flex;align-items:center;justify-content:center;
  border:1px solid rgba(255,255,255,.1);font-size:.75rem;
}
.av-sc-title { font-size:.8rem;font-weight:900;color:rgba(255,255,255,.75);text-transform:uppercase;letter-spacing:.06em;flex:1; }
.av-admin-only-badge { display:inline-flex;align-items:center;padding:2px 8px;border-radius:99px;font-size:.62rem;font-weight:800;background:rgba(239,68,68,.10);border:1px solid rgba(239,68,68,.2);color:#f87171; }

/* ── Credentials list ── */
.av-creds-list { padding:4px 0 6px; }
.av-cred-item { display:flex;align-items:flex-start;justify-content:space-between;gap:12px;padding:10px 16px;border-bottom:1px solid rgba(255,255,255,.04); }
.av-cred-item:last-child { border-bottom:0; }
.av-cred-left { display:flex;align-items:center;gap:7px;min-width:112px;flex-shrink:0;padding-top:2px; }
.av-cred-ico  { font-size:.65rem;color:rgba(255,255,255,.22);width:12px;text-align:center;flex-shrink:0; }
.av-cred-lbl  { font-size:.72rem;font-weight:700;color:rgba(255,255,255,.38);white-space:normal;overflow:visible;text-overflow:clip;line-height:1.25; }
.av-cred-right { display:flex;align-items:flex-start;justify-content:flex-end;gap:6px;min-width:0;flex:1; }
.av-cred-val  { font-family:monospace;font-size:.77rem;font-weight:700;color:rgba(255,255,255,.82);max-width:none;overflow:visible;text-overflow:clip;white-space:normal;overflow-wrap:anywhere;word-break:break-word;line-height:1.35;cursor:pointer;transition:filter .2s;text-align:right; }
.av-copy-btn  { background:none;border:none;color:rgba(255,255,255,.2);cursor:pointer;padding:2px 3px;transition:color .12s;line-height:1;flex-shrink:0; }
.av-copy-btn:hover { color:#9f8cff; }
.av-copy-btn:disabled { opacity:.25;cursor:default; }

@media (max-width: 420px) {
  .admin-account-view .av-cred-item { flex-direction:column;align-items:stretch;gap:6px; }
  .admin-account-view .av-cred-left { min-width:0;width:100%; }
  .admin-account-view .av-cred-right { justify-content:space-between;width:100%; }
  .admin-account-view .av-cred-val { text-align:left; }
}

/* ── Overview / earnings ── */
.av-ov-earnings { display:flex;align-items:center;justify-content:space-around;padding:14px 16px;border-bottom:1px solid rgba(255,255,255,.06);background:rgba(255,255,255,.02); }
.av-ov-earn-item { text-align:center; }
.av-ov-earn-label { font-size:.65rem;font-weight:700;color:rgba(255,255,255,.3);text-transform:uppercase;letter-spacing:.05em;margin-bottom:3px; }
.av-ov-earn-val { font-size:.9rem;font-weight:900;color:rgba(255,255,255,.88); }
.av-ov-earn-sep { font-size:.9rem;color:rgba(255,255,255,.2);font-weight:300; }

/* Stat grid 2-col */
.av-stat-grid { display:grid;grid-template-columns:1fr 1fr;gap:0; }
.av-stat-item { display:flex;align-items:center;gap:8px;padding:9px 14px;border-bottom:1px solid rgba(255,255,255,.04);border-right:1px solid rgba(255,255,255,.04); }
.av-stat-item:nth-child(even) { border-right:0; }
.av-stat-item:nth-last-child(-n+2) { border-bottom:0; }
.av-stat-ico  { font-size:.65rem;color:rgba(255,255,255,.25);width:14px;flex-shrink:0; }
.av-stat-lbl  { font-size:.65rem;font-weight:700;color:rgba(255,255,255,.3);text-transform:uppercase;letter-spacing:.04em;line-height:1; }
.av-stat-val  { font-size:.8rem;font-weight:800;color:rgba(255,255,255,.82);margin-top:2px;line-height:1.2; }

/* Tags */
.av-tag-section { padding:12px 16px; }
.av-tag-label { font-size:.65rem;font-weight:800;color:rgba(255,255,255,.3);text-transform:uppercase;letter-spacing:.07em;margin-bottom:7px; }
.av-tag-list  { display:flex;flex-wrap:wrap;gap:5px; }
.av-tag { display:inline-flex;align-items:center;padding:3px 9px;border-radius:99px;font-size:.7rem;font-weight:700;background:rgba(255,255,255,.05);border:1px solid rgba(255,255,255,.09);color:rgba(255,255,255,.6); }
.av-tag--role { background:rgba(109,92,255,.1);border-color:rgba(109,92,255,.25);color:#c4b5fd; }
.av-tag--more { background:rgba(255,255,255,.03);color:rgba(255,255,255,.3); }

/* ── Buyer / Seller sidebar cards ── */
.av-buyer-row { display:flex;align-items:center;gap:12px;padding:14px 16px; }
.av-buyer-avi { width:38px;height:38px;border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:.95rem;font-weight:900;flex-shrink:0; }
.av-buyer-info .av-buyer-name { font-size:.85rem;font-weight:900;color:rgba(255,255,255,.9); }
.av-buyer-info .av-buyer-sub  { font-size:.72rem;color:rgba(255,255,255,.35);margin-top:1px; }

/* ── Info row (delivery instructions etc.) ── */
.av-info-row { display:flex;justify-content:space-between;align-items:center;padding:.55rem 0;border-bottom:1px solid rgba(255,255,255,.05);font-size:.875rem; }
.av-info-row:last-child { border-bottom:0; }
.av-info-row .label { color:#6d747b; }
.av-info-row .value { font-weight:500;color:#c5c8cc;text-align:right; }

/* ── Chat ── */
.av-chat-header { display:flex;align-items:center;justify-content:space-between;padding:14px 20px;border-bottom:1px solid var(--bs-card-border-color); }
.av-chat-title  { font-size:.95rem;font-weight:900;color:rgba(255,255,255,.9);display:flex;align-items:center;gap:.5rem; }
#adminChatMessages { min-height:300px;max-height:480px;overflow-y:auto;padding:1rem 1.25rem;display:flex;flex-direction:column;scroll-behavior:smooth; }
#adminChatMessages::-webkit-scrollbar{ width:5px; }
#adminChatMessages::-webkit-scrollbar-track{ background:transparent; }
#adminChatMessages::-webkit-scrollbar-thumb{ background:rgba(255,255,255,.12);border-radius:3px; }

/* lb-msg bubbles */
.lb-msg { display:flex;flex-direction:column;margin-bottom:.5rem;max-width:75%; }
.lb-msg--start { align-self:flex-start; }
.lb-msg--end   { align-self:flex-end; }
.lb-msg__head  { display:flex;align-items:center;gap:.5rem;margin-bottom:.25rem; }
.lb-msg__head--end { flex-direction:row-reverse; }
.lb-msg__avatar { width:1.75rem;height:1.75rem;border-radius:50%;object-fit:cover;flex-shrink:0; }
.lb-msg__name { font-weight:700;font-size:.8rem;line-height:1.3; }
.lb-msg__time { font-size:.72rem;opacity:.45; }
.lb-msg__bubble { padding:.55rem .85rem;border-radius:.75rem;font-size:.875rem;line-height:1.55;word-break:break-word;background:rgba(255,255,255,.07); }
.lb-msg--end .lb-msg__bubble { background:rgba(99,102,241,.22); }
.lb-badge { display:inline-flex;align-items:center;padding:.1rem .4rem;border-radius:999px;font-size:.68rem;font-weight:700;text-transform:uppercase;letter-spacing:.04em; }
.lb-badge--seller { background:rgba(99,102,241,.2);color:#818cf8; }
.lb-badge--client { background:rgba(16,185,129,.15);color:#10b981; }
.lb-badge--admin  { background:rgba(245,158,11,.15);color:#f59e0b; }
.lb-chat-empty { display:flex;flex-direction:column;align-items:center;justify-content:center;min-height:240px;opacity:.4;gap:.5rem;text-align:center; }

.admin-account-view .btn-chat-icon{
  width:2.15rem;
  height:2.15rem;
  padding:0;
  display:inline-flex;
  align-items:center;
  justify-content:center;
  border-radius:.5rem;
  flex-shrink:0;
}
.admin-account-view .lb-chat-attach-preview{
  display:flex;
  align-items:center;
  gap:.75rem;
  padding:.55rem .75rem;
  border-radius:.5rem;
  background:rgba(255,255,255,.05);
  border:1px solid rgba(255,255,255,.08);
}
[data-theme="light"] .admin-account-view .lb-chat-attach-preview{
  background:rgba(0,0,0,.03);
  border-color:rgba(0,0,0,.08);
}
.admin-account-view .lb-chat-attach-preview__thumb{
  width:2.5rem;
  height:2.5rem;
  border-radius:.35rem;
  overflow:hidden;
  flex-shrink:0;
}
.admin-account-view .lb-chat-attach-preview__thumb img{
  width:100%;
  height:100%;
  object-fit:cover;
}
.admin-account-view .lb-chat-attach-preview__meta{ min-width:0; flex:1; }
.admin-account-view .lb-chat-attach-preview__title{ font-weight:800; font-size:.82rem; }
.admin-account-view .lb-chat-attach-preview__name{ font-size:.78rem; opacity:.8; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; }
.admin-account-view .lb-chat-attach-preview__remove{
  flex-shrink:0;
  background:none;
  border:none;
  color:rgba(255,255,255,.45);
  cursor:pointer;
  padding:.2rem .35rem;
  border-radius:.35rem;
  transition:opacity .15s, background .15s;
}
.admin-account-view .lb-chat-attach-preview__remove:hover{ opacity:1; background:rgba(255,255,255,.06); }
[data-theme="light"] .admin-account-view .lb-chat-attach-preview__remove:hover{ background:rgba(0,0,0,.05); }
.admin-account-view .lb-emoji-picker{
  position:absolute;
  right:1rem;
  bottom:4.25rem;
  width:min(330px, calc(100vw - 2rem));
  background:#1f2428;
  border:1px solid rgba(255,255,255,.10);
  border-radius:14px;
  box-shadow:0 20px 60px rgba(0,0,0,.35);
  padding:.75rem;
  z-index:20;
}
.admin-account-view .card-footer{ position:relative; }
[data-theme="light"] .admin-account-view .lb-emoji-picker{ background:#fff; border-color:rgba(0,0,0,.10); }
.admin-account-view .lb-emoji-picker__head{ margin-bottom:.55rem; }
.admin-account-view .lb-emoji-picker__search{
  width:100%;
  border:1px solid rgba(255,255,255,.10);
  background:rgba(255,255,255,.05);
  color:inherit;
  border-radius:10px;
  padding:.45rem .6rem;
  font-size:.84rem;
  outline:none;
}
[data-theme="light"] .admin-account-view .lb-emoji-picker__search{ border-color:rgba(0,0,0,.10); background:rgba(0,0,0,.03); }
.admin-account-view .lb-emoji-picker__tabs{ display:flex; gap:.25rem; overflow:auto; padding-bottom:.45rem; margin-bottom:.45rem; }
.admin-account-view .lb-emoji-picker__tab{
  border:0;
  border-radius:8px;
  background:transparent;
  padding:.28rem .4rem;
  line-height:1;
  cursor:pointer;
}
.admin-account-view .lb-emoji-picker__tab.is-active,
.admin-account-view .lb-emoji-picker__tab:hover{ background:rgba(109,92,255,.18); }
.admin-account-view .lb-emoji-picker__grid{
  display:grid;
  grid-template-columns:repeat(8, 1fr);
  gap:.2rem;
  max-height:190px;
  overflow:auto;
}
.admin-account-view .lb-emoji-picker__item{
  border:0;
  background:transparent;
  border-radius:8px;
  font-size:1.15rem;
  line-height:1;
  padding:.35rem .1rem;
  cursor:pointer;
}
.admin-account-view .lb-emoji-picker__item:hover{ background:rgba(255,255,255,.08); }
[data-theme="light"] .admin-account-view .lb-emoji-picker__item:hover{ background:rgba(0,0,0,.06); }
/* System messages like seller order view */
.lb-syswrap{
  width:100%;
  max-width:100%;
  margin:.55rem 0 .85rem;
}
.lb-sys{
  display:block;
  width:100%;
  background:rgba(109,92,255,.14);
  border:1px dashed rgba(159,140,255,.45);
  border-radius:18px;
  padding:1.15rem 1.25rem;
  font-size:1rem;
  font-weight:700;
  line-height:1.65;
  color:rgba(255,255,255,.96);
  text-align:left;
  white-space:pre-line;
  opacity:1;
  box-shadow:inset 0 1px 0 rgba(255,255,255,.04);
}
.lb-sys-time{
  font-size:.78rem;
  opacity:.5;
  margin-top:.45rem;
  padding-left:.1rem;
  text-align:left;
  color:rgba(255,255,255,.55);
}
[data-theme="light"] .admin-account-view .lb-sys{
  background:rgba(109,92,255,.08);
  border-color:rgba(109,92,255,.28);
  color:rgba(17,24,39,.92);
}
[data-theme="light"] .admin-account-view .lb-sys-time{
  color:rgba(0,0,0,.5);
  opacity:1;
}


/* ─────────────────────────────────────────────
   Seller-order-view inspired polish for admin view
───────────────────────────────────────────── */
.admin-account-view{
  --av-accent:#9f8cff;
  --av-success:#4ade80;
  --av-danger:#fb7185;
  --av-warning:#fbbf24;
}
.admin-account-view .av-head{
  position:relative;
  overflow:hidden;
  border-radius:.75rem;
  border:1px solid rgba(255,255,255,.07);
  background:linear-gradient(180deg, rgba(37,40,42,.98), rgba(31,34,36,.98));
  box-shadow:0 14px 34px rgba(0,0,0,.18);
}
.admin-account-view .av-head::before{ display:none!important; }
.admin-account-view .av-head-body,
.admin-account-view .av-meta-row{
  position:relative;
  z-index:1;
}
.admin-account-view .av-head-body{
  padding:18px 20px;
  border-bottom:1px solid rgba(255,255,255,.06);
}
.admin-account-view .av-head-body > div:first-child > div:first-child{
  border-radius:.6rem!important;
  background:rgba(255,255,255,.045)!important;
  border:1px solid rgba(255,255,255,.08)!important;
  box-shadow:inset 0 1px 0 rgba(255,255,255,.05), 0 10px 24px rgba(0,0,0,.14);
}
.admin-account-view .av-head h1{
  font-size:1.15rem!important;
  letter-spacing:-.02em;
}
.admin-account-view .av-meta-row{
  padding:13px 20px 15px;
  gap:8px;
  background:rgba(0,0,0,.08);
}
.admin-account-view .av-meta-pill{
  padding:5px 11px;
  border-radius:999px;
  background:rgba(255,255,255,.045);
  border:1px solid rgba(255,255,255,.08);
  color:rgba(255,255,255,.62);
  box-shadow:inset 0 1px 0 rgba(255,255,255,.04);
}
.admin-account-view .av-meta-pill strong{ color:rgba(255,255,255,.94); }
.admin-account-view .av-status{
  padding:4px 10px;
  border-radius:999px;
  font-size:.68rem;
  letter-spacing:.02em;
  text-transform:uppercase;
}
.admin-account-view .av-actions{ gap:7px; }
.admin-account-view .av-btn-primary,
.admin-account-view .av-btn-warning,
.admin-account-view .av-btn-ghost{
  border-radius:.6rem;
  min-height:34px;
  padding:7px 13px;
}
.admin-account-view .av-btn-primary{
  background:linear-gradient(135deg, #6d5cff, #9f8cff);
  box-shadow:0 10px 24px rgba(109,92,255,.18);
}
.admin-account-view .card,
.admin-account-view .av-sidebar-card{
  border-radius:.75rem!important;
  border:1px solid rgba(255,255,255,.07)!important;
  background:linear-gradient(180deg, rgba(37,40,42,.98), rgba(31,34,36,.98))!important;
  box-shadow:0 14px 34px rgba(0,0,0,.18)!important;
}
.admin-account-view .card-header,
.admin-account-view .av-sc-header,
.admin-account-view .av-chat-header{
  background:rgba(255,255,255,.018)!important;
  border-bottom:1px solid rgba(255,255,255,.06)!important;
}
.admin-account-view .card-header-title,
.admin-account-view .av-sc-title,
.admin-account-view .av-chat-title{
  font-size:.78rem!important;
  font-weight:900!important;
  text-transform:uppercase;
  letter-spacing:.06em;
  color:rgba(255,255,255,.72)!important;
}
.admin-account-view .av-sc-icon{
  border-radius:.55rem;
  box-shadow:inset 0 1px 0 rgba(255,255,255,.05);
}
.admin-account-view .av-cred-item,
.admin-account-view .av-stat-item,
.admin-account-view .av-info-row{
  border-color:rgba(255,255,255,.055)!important;
}
.admin-account-view .av-buyer-row{
  padding:14px 16px;
}
.admin-account-view #adminChatMessages{
  background:linear-gradient(180deg, rgba(16,18,20,.26), rgba(16,18,20,.10));
}
[data-theme="light"] .admin-account-view .av-head,
[data-theme="light"] .admin-account-view .card,
[data-theme="light"] .admin-account-view .av-sidebar-card{
  background:#fff!important;
  border-color:rgba(0,0,0,.08)!important;
  box-shadow:0 12px 28px rgba(15,23,42,.07)!important;
}
[data-theme="light"] .admin-account-view .av-head::before{ display:none!important; }
[data-theme="light"] .admin-account-view .av-head-body,
[data-theme="light"] .admin-account-view .card-header,
[data-theme="light"] .admin-account-view .av-sc-header,
[data-theme="light"] .admin-account-view .av-chat-header{
  border-color:rgba(0,0,0,.07)!important;
}
[data-theme="light"] .admin-account-view .av-meta-row{ background:rgba(15,23,42,.025); }
[data-theme="light"] .admin-account-view .av-meta-pill{
  color:rgba(15,23,42,.58);
  border-color:rgba(15,23,42,.08);
  background:rgba(15,23,42,.035);
}
[data-theme="light"] .admin-account-view .av-meta-pill strong,
[data-theme="light"] .admin-account-view .av-head h1,
[data-theme="light"] .admin-account-view .card-header-title,
[data-theme="light"] .admin-account-view .av-sc-title,
[data-theme="light"] .admin-account-view .av-chat-title{
  color:rgba(15,23,42,.88)!important;
}

</style>

<div class="admin-account-view">

<!-- ── HEAD CARD ── -->
<div class="av-head mb-4">
  <div class="av-head-body">
    <div style="display:flex;align-items:center;gap:14px;flex:1;min-width:0;">
      <div style="width:52px;height:52px;border-radius:14px;background:rgba(109,92,255,.12);border:1px solid rgba(109,92,255,.22);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
        <?php if ($isVal): ?>
          <img src="/public/assets/core/main/img/val/ranks/mini/<?= $valRank ?>.png" style="width:30px;height:30px;object-fit:contain;filter:drop-shadow(0 1px 3px rgba(0,0,0,.5));" alt="">
        <?php else: ?>
          <img src="<?= util_rank_img('lol', 'mini', $account['current_rank'] ?? 0) ?>" style="width:30px;height:30px;object-fit:contain;filter:drop-shadow(0 1px 3px rgba(0,0,0,.5));" alt="">
        <?php endif ?>
      </div>
      <div style="min-width:0;">
        <div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap;">
          <h1 style="font-size:1.25rem;font-weight:950;color:rgba(255,255,255,.92);margin:0;line-height:1.2;">
            <?= $h($account['title'] ?? 'Account #' . $id) ?>
          </h1>
          <?php if ($sold): ?>
            <span class="av-status av-status--sold"><i class="fa-solid fa-check"></i> Sold</span>
          <?php elseif ($active === 0): ?>
            <span class="av-status av-status--unlisted"><i class="fa-solid fa-eye-slash"></i> Unlisted</span>
          <?php else: ?>
            <span class="av-status av-status--active"><i class="fa-solid fa-circle" style="font-size:.4rem;"></i> Active</span>
          <?php endif ?>
        </div>
        <div style="font-size:.8rem;color:rgba(255,255,255,.4);margin-top:4px;display:flex;align-items:center;gap:6px;">
          <?php if ($server): ?>
            <span style="text-transform:uppercase;font-weight:700;"><?= $h($server) ?></span><span>·</span>
          <?php endif ?>
          <span>#<?= $id ?></span>
          <span>·</span>
          <span><?= $account['created_at'] ? date('d.m.Y', strtotime($account['created_at'])) : '—' ?></span>
          <?php if ($seller): ?>
            <span>·</span>
            <a href="<?= ADMN_URL ?>/seller/<?= (int)$seller['id'] ?>/profile" style="color:rgba(255,255,255,.5);text-decoration:none;font-weight:700;">
              <i class="fa-duotone fa-store me-1"></i><?= $h($seller['username'] ?? '') ?>
            </a>
          <?php endif ?>
        </div>
      </div>
    </div>

    <div class="av-actions">
      <button type="button" class="av-btn-primary" data-bs-toggle="offcanvas" data-bs-target="#editAccountCanvas">
        <i class="fa-duotone fa-pen"></i> Edit
      </button>
      <?php if ($sold): ?>
        <form class="ajax-form d-inline" action="<?= AJAX_URL ?>" method="POST" onsubmit="return confirm('Mark this account as unsold?')">
          <input type="hidden" name="action" value="admin_mark_account_unsold">
          <input type="hidden" name="account_id" value="<?= $id ?>">
          <button type="submit" class="av-btn-warning">
            <i class="fa-duotone fa-rotate-left"></i>
            Unsold
          </button>
        </form>
      <?php else: ?>
        <form class="ajax-form d-inline" action="<?= AJAX_URL ?>" method="POST">
          <input type="hidden" name="action" value="admin_toggle_account_active">
          <input type="hidden" name="account_id" value="<?= $id ?>">
          <button type="submit" class="av-btn-warning">
            <i class="fa-duotone fa-<?= $active ? 'eye-slash' : 'eye' ?>"></i>
            <?= $active ? 'Unlist' : 'Re-list' ?>
          </button>
        </form>
      <?php endif ?>
      <?php if ($sold && !empty($buyer)): ?>
        <button type="button" class="av-btn-primary js-admin-poke-client" data-ref-type="account" data-id="<?= $id ?>">
          <i class="fa-duotone fa-hand-point-up"></i> Poke Client
        </button>
      <?php endif ?>
      <a href="<?= ADMN_URL ?>/selling-accounts" class="av-btn-ghost">
        <i class="fa-duotone fa-arrow-left"></i> Back
      </a>
    </div>
  </div>

  <div class="av-meta-row">
    <span class="av-meta-pill"><i class="fa-solid fa-euro-sign" style="color:rgba(255,255,255,.4);"></i> <strong>€<?= $priceEur ?></strong> Price</span>
    <span class="av-meta-pill"><i class="fa-solid fa-sack-dollar" style="color:#4ade80;"></i> <strong style="color:#4ade80;">€<?= $earningsEur ?></strong> Seller Earnings</span>
    <?php if ($buyer): ?>
    <span class="av-meta-pill"><i class="fa-solid fa-user" style="color:#4ade80;"></i> <strong style="color:#4ade80;"><?= $h($buyer['username'] ?? '—') ?></strong></span>
    <?php endif ?>
  </div>
</div>


<!-- ── 2-COLUMN LAYOUT ── -->
<div class="row g-4 align-items-start">

  <!-- ── LEFT col ── -->
  <div class="col-12 col-lg-8">

    <!-- Description / Delivery Instructions -->
    <?php if (!empty($account['title']) || !empty($account['delivery_instructions'])): ?>
    <div class="card mb-4">
      <div class="card-header" style="padding:14px 20px;">
        <h5 class="card-header-title mb-0"><i class="fa-duotone fa-align-left me-2"></i>Details</h5>
      </div>
      <div class="card-body" style="padding:16px 20px;">
        <?php if (!empty($account['delivery_instructions'])): ?>
        <div class="acc-section-title" style="font-size:.68rem;font-weight:700;text-transform:uppercase;letter-spacing:.08em;color:#6d747b;padding-bottom:.5rem;border-bottom:1px solid rgba(255,255,255,.07);margin-bottom:1rem;">Delivery Instructions</div>
        <div class="alert alert-soft-info mb-0" style="white-space:pre-wrap;font-size:.875rem;">
            <?= $h($account['delivery_instructions']) ?>
        </div>
        <?php endif ?>
      </div>
    </div>
    <?php endif ?>

    <!-- Skins list -->
    <?php
    $skinsList = $account['skins'] ?? null;
    if ($skinsList && !is_array($skinsList)) {
        $skinsList = explode('|', (string)$skinsList);
    }
    if (!empty($skinsList)):
    ?>
    <div class="card mb-4">
      <div class="card-header" style="padding:14px 20px;">
        <h5 class="card-header-title mb-0"><i class="fa-duotone fa-palette me-2"></i>Skins <span style="font-size:.78rem;color:rgba(255,255,255,.4);font-weight:600;"><?= count($skinsList) ?></span></h5>
      </div>
      <div class="card-body" style="padding:16px 20px;">
        <div class="d-flex flex-wrap gap-1">
          <?php foreach (array_slice($skinsList, 0, 60) as $skin): ?>
            <span class="av-tag"><?= $h(trim($skin)) ?></span>
          <?php endforeach ?>
          <?php if (count($skinsList) > 60): ?>
            <span class="av-tag av-tag--more">+<?= count($skinsList) - 60 ?> more</span>
          <?php endif ?>
        </div>
      </div>
    </div>
    <?php endif ?>

    <!-- Chat -->
    <div class="card order-chat-card mb-4">
      <div class="av-chat-header">
        <div class="av-chat-title">
          <i class="fa-duotone fa-comments" style="color:#9f8cff;"></i>
          Support Chat
        </div>
        <?php if ($buyer): ?>
        <div style="display:inline-flex;align-items:center;gap:.4rem;padding:3px 10px;border-radius:99px;background:rgba(74,222,128,.10);border:1px solid rgba(74,222,128,.20);color:#4ade80;font-size:.75rem;font-weight:700;">
          <?php if (!empty($buyer['icon'])): ?>
            <img src="<?= $h($buyer['icon']) ?>" style="width:18px;height:18px;border-radius:50%;object-fit:cover;">
          <?php else: ?>
            <span style="width:18px;height:18px;border-radius:50%;background:rgba(74,222,128,.2);display:inline-flex;align-items:center;justify-content:center;font-size:.6rem;font-weight:900;"><?= strtoupper(substr($buyer['username'] ?? 'B', 0, 1)) ?></span>
          <?php endif ?>
          <?= $h($buyer['username'] ?? '—') ?>
        </div>
        <?php endif ?>
      </div>

      <div id="adminChatMessages">
        <?php if (empty($chat)): ?>
          <div class="lb-chat-empty">
            <i class="fa-duotone fa-message-slash" style="font-size:2rem;"></i>
            <span>No messages yet.</span>
          </div>
        <?php else: ?>
          <?php
          $sellerIcon    = !empty($seller['icon']) ? $seller['icon'] : null;
          $sellerInitial = strtoupper(substr($seller['username'] ?? 'S', 0, 1));
          $buyerIcon     = !empty($buyer['icon']) ? $buyer['icon'] : null;
          $buyerInitial  = strtoupper(substr($buyer['username'] ?? 'B', 0, 1));
          // $adminIcon and $adminInitial are resolved at the top of this view from admins.icon.

          foreach ($chat as $msg):
            $isDeleted  = !empty($msg['deleted']);
            $type       = strtolower((string)($msg['sender'] ?? $msg['type'] ?? 'seller'));
            $msgSenderIcon = _account_chat_msg_icon($msg);
            $senderName = $msg['sender_name'] ?? (
                $type === 'admin' ? ($admin['username'] ?? $admin['name'] ?? 'Admin') :
                ($type === 'seller' ? ($seller['username'] ?? 'Seller') :
                ($buyer['username'] ?? 'Buyer'))
            );
            $text       = $msg['message'] ?? $msg['content'] ?? '';
            $textClean  = $chatText($text);
            $tsRaw      = $msg['time'] ?? $msg['created_at'] ?? null;
            $ts         = is_numeric($tsRaw) ? date('d.m.Y H:i', (int)$tsRaw) : (!empty($tsRaw) ? date('d.m.Y H:i', strtotime((string)$tsRaw)) : '');

            if ($type === 'system') {
          ?>
          <div class="lb-syswrap">
            <div class="lb-sys">
              <?php if ($isDeleted): ?>
                <em style="opacity:.55;">Message deleted</em>
              <?php else: ?>
                <?= _chat_render_html($textClean) ?>
              <?php endif ?>
            </div>
            <?php if (!empty($ts)): ?><div class="lb-sys-time"><?= $h($ts) ?></div><?php endif ?>
          </div>
          <?php
              continue;
            }

            $isRight = in_array($type, ['admin', 'seller']);

            if ($type === 'admin') {
                // For account chats, old JSON messages may contain a wrong sender_icon.
                // Always use the logged-in admin from admins.icon, exactly like the route provides.
                $msgIcon    = $adminIcon ?: _account_chat_fallback_icon('admin');
                $msgInitial = $adminInitial;
                $badgeCls   = 'lb-badge--admin';
                $badgeLabel = 'Admin';
            } elseif ($type === 'seller') {
                $msgIcon    = $msgSenderIcon ?: ($sellerIcon ?: _account_chat_fallback_icon('seller'));
                $msgInitial = $sellerInitial;
                $badgeCls   = 'lb-badge--seller';
                $badgeLabel = 'Seller';
            } else {
                $msgIcon    = $msgSenderIcon ?: ($buyerIcon ?: _account_chat_fallback_icon('buyer'));
                $msgInitial = $buyerInitial;
                $badgeCls   = 'lb-badge--client';
                $badgeLabel = 'Buyer';
            }
          ?>
          <div class="lb-msg <?= $isRight ? 'lb-msg--end' : 'lb-msg--start' ?>">
            <div class="lb-msg__head <?= $isRight ? 'lb-msg__head--end' : '' ?>">
              <?php if ($msgIcon): ?>
                <img class="lb-msg__avatar" src="<?= $h($msgIcon) ?>" alt="">
              <?php else: ?>
                <span class="lb-msg__avatar" style="background:<?= $isRight ? 'rgba(99,102,241,.3)' : 'rgba(74,222,128,.2)' ?>;color:<?= $isRight ? '#a5b4fc' : '#4ade80' ?>;display:inline-flex;align-items:center;justify-content:center;font-size:.7rem;font-weight:800;">
                  <?= $h($msgInitial) ?>
                </span>
              <?php endif ?>
              <div>
                <div style="display:flex;align-items:center;gap:.3rem;flex-wrap:wrap;<?= $isRight ? 'flex-direction:row-reverse;' : '' ?>">
                  <span class="lb-msg__name"><?= $h($senderName) ?></span>
                  <span class="lb-badge <?= $badgeCls ?>"><?= $badgeLabel ?></span>
                </div>
                <span class="lb-msg__time"><?= $h($ts) ?></span>
              </div>
            </div>
            <div class="lb-msg__bubble">
              <?php if ($isDeleted): ?>
                <em style="opacity:.55;">Message deleted</em>
              <?php elseif (preg_match('/\.(png|jpe?g|gif|webp)(\?.*)?$/i', $textClean)): ?>
                <img src="<?= $h($textClean) ?>" style="max-width:260px;max-height:200px;border-radius:.5rem;display:block;">
              <?php else: ?>
                <?= nl2br(_chat_linkify_plain($textClean)) ?>
              <?php endif ?>
            </div>
          </div>
          <?php endforeach ?>
        <?php endif ?>
      </div>

      <div class="card-footer">
        <form id="adminChatForm" class="row gx-2 align-items-center" action="<?= AJAX_URL ?>" method="POST" enctype="multipart/form-data">
          <input type="hidden" name="action" value="admin_account_chat_send">
          <input type="hidden" name="account_id" value="<?= $id ?>">
          <input type="hidden" name="send_as" value="admin">
          <div class="col">
            <input type="text" name="message" id="adminChatInput" class="form-control"
                   placeholder="Type your message" autocomplete="off">
          </div>
          <div class="col-auto d-flex align-items-center gap-2">
            <input type="file" class="d-none" id="adminChatFile" name="chat_image" accept=".png,.jpg,.jpeg,.gif,image/png,image/jpeg,image/gif">
            <button type="button" class="btn btn-sm btn-secondary btn-chat-icon" id="adminChatAttachBtn" aria-label="Attach image" title="Attach image">
              <i class="fa-duotone fa-paperclip"></i>
            </button>
            <button type="button" class="btn btn-sm btn-secondary lb-emoji-btn d-none d-md-inline-flex btn-chat-icon" id="adminEmojiBtn" aria-label="Emojis" title="Emojis">
              <i class="fa-regular fa-face-smile"></i>
            </button>
            <button type="submit" class="btn btn-sm btn-primary btn-chat-icon" id="adminChatSendBtn" aria-label="Send" title="Send">
              <span class="indicator-label"><i class="fa-duotone fa-paper-plane fs-5"></i></span>
              <span class="indicator-progress d-none"><span class="spinner-border spinner-border-sm align-middle"></span></span>
            </button>
          </div>
          <div class="col-12">
            <div class="lb-chat-error text-danger small mt-1 d-none" id="adminChatError"></div>
            <div class="lb-chat-attach-preview d-none mt-2" id="adminChatPreview">
              <div class="lb-chat-attach-preview__thumb">
                <img src="" alt="attachment preview" id="adminChatPreviewImg">
              </div>
              <div class="lb-chat-attach-preview__meta">
                <div class="lb-chat-attach-preview__title">Image ready to send</div>
                <div class="lb-chat-attach-preview__name" id="adminChatPreviewName"></div>
              </div>
              <button type="button" class="lb-chat-attach-preview__remove" id="adminChatRemoveBtn" aria-label="Remove" title="Remove">
                <i class="fa-duotone fa-xmark"></i>
              </button>
            </div>
            <div class="text-muted small mt-2">
              Tip: You can also paste a screenshot with <strong>Ctrl</strong> + <strong>V</strong>.
            </div>
          </div>
        </form>
        <div id="adminEmojiPicker" class="lb-emoji-picker d-none" role="dialog" aria-label="Emoji Picker">
          <div class="lb-emoji-picker__head">
            <input type="text" id="adminEmojiSearch" class="lb-emoji-picker__search" placeholder="Search emojis…">
          </div>
          <div class="lb-emoji-picker__tabs" id="adminEmojiTabs">
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
          <div class="lb-emoji-picker__grid" id="adminEmojiGrid"></div>
        </div>
      </div>
    </div>

  </div>
  <!-- ── LEFT col end ── -->


  <!-- ── RIGHT col (sidebar) ── -->
  <div class="col-12 col-lg-4">

    <!-- Seller card -->
    <?php if ($seller): ?>
    <div class="av-sidebar-card mb-3">
      <div class="av-sc-header">
        <span class="av-sc-icon" style="background:rgba(99,102,241,.12);border-color:rgba(99,102,241,.22);"><i class="fa-solid fa-store" style="color:#818cf8;font-size:.72rem;"></i></span>
        <span class="av-sc-title">Seller</span>
      </div>
      <div class="av-buyer-row">
        <?php if (!empty($seller['icon'])): ?>
          <img src="<?= $h($seller['icon']) ?>" style="width:38px;height:38px;border-radius:10px;object-fit:cover;flex-shrink:0;" alt="">
        <?php else: ?>
          <div class="av-buyer-avi" style="background:rgba(92,74,227,.2);color:#9b8bf0;"><?= strtoupper(substr($seller['username'] ?? 'S', 0, 1)) ?></div>
        <?php endif ?>
        <div class="av-buyer-info">
          <div class="av-buyer-name"><?= $h($seller['username'] ?? '') ?></div>
          <div class="av-buyer-sub"><?= $h($seller['email'] ?? '') ?></div>
        </div>
        <a href="<?= ADMN_URL ?>/seller/<?= (int)$seller['id'] ?>/profile" class="av-btn-ghost" style="font-size:.75rem;padding:5px 12px;margin-left:auto;flex-shrink:0;">
          <i class="fa-duotone fa-store"></i> Profile
        </a>
      </div>
      <div style="padding:0 16px 14px;">
        <div class="av-info-row">
          <span class="label">Seller Paid</span>
          <span class="value">
            <?= !empty($account['seller_paid']) && (int)$account['seller_paid'] === 1
                ? '<span class="badge bg-soft-success text-success">Yes</span>'
                : '<span class="badge bg-soft-secondary text-secondary">No</span>' ?>
          </span>
        </div>
      </div>
    </div>
    <?php endif ?>

    <!-- Buyer card -->
    <div class="av-sidebar-card mb-3">
      <div class="av-sc-header">
        <span class="av-sc-icon" style="background:rgba(74,222,128,.12);border-color:rgba(74,222,128,.22);"><i class="fa-solid fa-user-check" style="color:#4ade80;font-size:.72rem;"></i></span>
        <span class="av-sc-title">Buyer</span>
      </div>
      <?php if ($buyer): ?>
      <div class="av-buyer-row">
        <?php if (!empty($buyer['icon'])): ?>
          <img src="<?= $h($buyer['icon']) ?>" style="width:38px;height:38px;border-radius:10px;object-fit:cover;flex-shrink:0;" alt="">
        <?php else: ?>
          <div class="av-buyer-avi" style="background:rgba(74,222,128,.15);color:#4ade80;"><?= strtoupper(substr($buyer['username'] ?? 'B', 0, 1)) ?></div>
        <?php endif ?>
        <div class="av-buyer-info">
          <div class="av-buyer-name"><?= $h($buyer['username'] ?? '') ?></div>
          <div class="av-buyer-sub"><?= $h($buyer['email'] ?? '') ?></div>
        </div>
        <a href="<?= ADMN_URL ?>/client/<?= (int)$buyer['id'] ?>" class="av-btn-ghost" style="font-size:.75rem;padding:5px 12px;margin-left:auto;flex-shrink:0;">
          <i class="fa-duotone fa-user"></i> Profile
        </a>
      </div>
      <?php if (!empty($account['sold_at'])): ?>
      <div style="padding:0 16px 12px;">
        <div class="av-info-row">
          <span class="label">Sold At</span>
          <span class="value"><?= date('d.m.Y H:i', strtotime($account['sold_at'])) ?></span>
        </div>
      </div>
      <?php endif ?>
      <?php else: ?>
      <div style="text-align:center;padding:20px 16px;">
        <div style="font-size:1.5rem;opacity:.2;margin-bottom:6px;"><i class="fa-duotone fa-user-clock"></i></div>
        <div style="font-size:.8rem;color:rgba(255,255,255,.35);font-weight:700;">Not sold yet — no buyer.</div>
      </div>
      <?php endif ?>
    </div>

    <!-- Credentials card -->
    <div class="av-sidebar-card mb-3">
      <div class="av-sc-header">
        <span class="av-sc-icon" style="background:rgba(239,68,68,.10);border-color:rgba(239,68,68,.2);"><i class="fa-solid fa-key" style="color:#f87171;font-size:.72rem;"></i></span>
        <span class="av-sc-title">Credentials</span>
        <span class="av-admin-only-badge">Admin Only</span>
      </div>
      <div class="av-creds-list">
        <?php
        $creds = [
            'in_game_name'   => ['In-Game Name', 'fa-solid fa-gamepad'],
            'login'          => ['LoL Login',     'fa-solid fa-user'],
            'password'       => ['LoL Password',  'fa-solid fa-key'],
            'riot_username'  => ['Riot Username', 'fa-solid fa-user-shield'],
            'riot_password'  => ['Riot Password', 'fa-solid fa-lock'],
            'email'          => ['Email',         'fa-solid fa-envelope'],
            'email_password' => ['Email Password','fa-solid fa-lock'],
        ];
        foreach ($creds as $field => [$label, $icon]):
            $val  = $account[$field] ?? '';
            if ($val === 'unverified' || $val === '') continue;
            $safe = htmlspecialchars($val, ENT_QUOTES, 'UTF-8');
        ?>
        <div class="av-cred-item">
          <div class="av-cred-left">
            <i class="<?= $icon ?> av-cred-ico"></i>
            <span class="av-cred-lbl"><?= $label ?></span>
          </div>
          <div class="av-cred-right">
            <span class="av-cred-val av-sensitive" title="<?= $safe ?>"><?= $safe ?></span>
            <button class="av-copy-btn js-copy" data-copy="<?= $safe ?>">
              <i class="fa-duotone fa-copy"></i>
            </button>
          </div>
        </div>
        <?php endforeach ?>
        <?php if (!empty($account['2fa']) && $account['2fa']): ?>
        <div class="av-cred-item">
          <div class="av-cred-left"><i class="fa-solid fa-shield-halved av-cred-ico"></i><span class="av-cred-lbl">2FA</span></div>
          <div class="av-cred-right"><span class="badge" style="background:rgba(251,191,36,.15);color:#fbbf24;font-size:.7rem;">Enabled</span></div>
        </div>
        <?php endif ?>
      </div>
    </div>

    <!-- Overview card -->
    <div class="av-sidebar-card mb-3">
      <div class="av-sc-header">
        <span class="av-sc-icon" style="background:rgba(109,92,255,.12);border-color:rgba(109,92,255,.22);"><i class="fa-solid fa-chart-bar" style="color:#c4b5fd;font-size:.72rem;"></i></span>
        <span class="av-sc-title">Overview</span>
      </div>

      <!-- Earnings summary -->
      <div class="av-ov-earnings">
        <div class="av-ov-earn-item">
          <div class="av-ov-earn-label">Price</div>
          <div class="av-ov-earn-val">€<?= $priceEur ?></div>
        </div>
        <div class="av-ov-earn-sep">−</div>
        <div class="av-ov-earn-item">
          <div class="av-ov-earn-label">Fee</div>
          <div class="av-ov-earn-val" style="color:#fb7185;">−<?= number_format($sellerFeePct, ((float)$sellerFeePct == (int)$sellerFeePct ? 0 : 2)) ?>%</div>
        </div>
        <div class="av-ov-earn-sep">=</div>
        <div class="av-ov-earn-item">
          <div class="av-ov-earn-label">Seller Gets</div>
          <div class="av-ov-earn-val" style="color:#4ade80;font-size:1rem;">€<?= $earningsEur ?></div>
        </div>
      </div>

      <!-- Stat grid -->
      <div class="av-stat-grid">
        <?php
        if ($isVal) {
            $stats = [
                ['fa-solid fa-trophy',     'Val Rank',     $valRankLabel,                                              '_rank'],
                ['fa-solid fa-globe',      'Platform',     strtoupper((string)($gameData['val_platform'] ?? $server ?: '—')), null],
                ['fa-solid fa-crosshairs', 'Agents',       $valAgentsDisplayCount > 0 ? $valAgentsDisplayCount . ' Agents' : '—', '#fb923c'],
                ['fa-solid fa-gun',        'Weapon Skins', $valWeaponSkins > 0 ? $valWeaponSkins : '—',                          '#f472b6'],
                ['fa-solid fa-coins',      'VP',           $valPoints > 0 ? number_format($valPoints) : '—',                     '#a78fff'],
                ['fa-solid fa-chart-line', 'Winrate',      $valWinrate > 0 ? $valWinrate . '%' : '—',                            '#4ade80'],
                ['fa-solid fa-bolt',       'Delivery',     ucfirst($account['delivery_type'] ?? '—'),                             '#fbbf24'],
                ['fa-solid fa-id-card',    'Account ID',   '#' . $id,                                                  null],
            ];
        } else {
            $stats = [
                ['fa-solid fa-trophy',      'Solo Rank',    $rankLabel,                                              '_rank'],
                ['fa-solid fa-shield',      'Flex Rank',    $flexLabel ?? 'Unranked',                                null],
                ['fa-solid fa-globe',       'Server',       $server ?: '—',                                null],
                ['fa-solid fa-arrow-up',    'Level',        $level ?? '—',                                 null],
                ['fa-solid fa-gem',         'Blue Essence', number_format((int)($blueEssence ?? 0)),                 '#60a5fa'],
                ['fa-solid fa-award',       'Honor',        $honorLevel ?? '—',                            null],
                ['fa-solid fa-bolt',        'Delivery',     ucfirst($account['delivery_type'] ?? '—'),     '#fbbf24'],
                ['fa-solid fa-id-card',     'Account ID',   '#' . $id,                                               null],
            ];
        }
        foreach ($stats as [$ico, $lbl, $val, $clr]):
        ?>
        <div class="av-stat-item">
          <?php if ($clr === '_rank'): ?>
            <?php if ($isVal): ?>
              <img src="/public/assets/core/main/img/val/ranks/mini/<?= $valRank ?>.png" style="width:14px;height:14px;object-fit:contain;flex-shrink:0;" alt="">
            <?php else: ?>
              <img src="<?= util_rank_img('lol', 'mini', $account['current_rank'] ?? 0) ?>" style="width:14px;height:14px;object-fit:contain;flex-shrink:0;" alt="">
            <?php endif ?>
          <?php else: ?>
            <i class="<?= $ico ?> av-stat-ico" <?= $clr ? 'style="color:'.$clr.';"' : '' ?>></i>
          <?php endif ?>
          <div>
            <div class="av-stat-lbl"><?= $lbl ?></div>
            <div class="av-stat-val"><?= $h((string)$val) ?></div>
          </div>
        </div>
        <?php endforeach ?>
      </div>
    </div>

    <!-- Champions & Roles (LoL) / Agents (Val) -->
    <?php
    $showContentCard = $isVal
        ? (!empty($valAgentsList) || $valAgentsDisplayCount > 0)
        : (!empty($champs) || $champsDisplayCount > 0 || !empty($roles));
    ?>
    <?php if ($showContentCard): ?>
    <div class="av-sidebar-card mb-3">
      <div class="av-sc-header">
        <span class="av-sc-icon" style="background:rgba(109,92,255,.12);border-color:rgba(109,92,255,.22);"><i class="fa-solid fa-gamepad" style="color:#c4b5fd;font-size:.72rem;"></i></span>
        <span class="av-sc-title"><?= $isVal ? 'Agents' : 'Champions & Roles' ?></span>
      </div>
      <?php if ($isVal): ?>
        <?php if (!empty($valAgentsList)): ?>
        <div class="av-tag-section">
          <div class="av-tag-label">Agents (<?= $valAgentsDisplayCount ?>)</div>
          <div class="av-tag-list">
            <?php foreach (array_slice($valAgentsList, 0, 20) as $agent): ?>
              <span class="av-tag"><?= $h(trim($agent)) ?></span>
            <?php endforeach ?>
            <?php if (count($valAgentsList) > 20): ?><span class="av-tag av-tag--more">+<?= count($valAgentsList)-20 ?> more</span><?php endif ?>
          </div>
        </div>
        <?php elseif ($valAgentsDisplayCount > 0): ?>
        <div class="av-tag-section">
          <div class="av-tag-label">Agents</div>
          <div style="padding:8px 0;font-size:.88rem;color:rgba(255,255,255,.6);font-weight:700;">
            <i class="fa-solid fa-crosshairs me-2" style="color:#fb923c;"></i><?= $valAgentsDisplayCount ?> agents unlocked
          </div>
        </div>
        <?php endif ?>
      <?php else: ?>
        <?php if (!empty($champs)): ?>
        <div class="av-tag-section">
          <div class="av-tag-label">Champions (<?= $champsDisplayCount ?>)</div>
          <div class="av-tag-list">
            <?php foreach (array_slice($champs, 0, 15) as $c): ?>
              <span class="av-tag"><?= $h(trim($c)) ?></span>
            <?php endforeach ?>
            <?php if (count($champs) > 15): ?><span class="av-tag av-tag--more">+<?= count($champs)-15 ?> more</span><?php endif ?>
          </div>
        </div>
        <?php elseif ($champsDisplayCount > 0): ?>
        <div class="av-tag-section">
          <div class="av-tag-label">Champions</div>
          <div style="padding:8px 0;font-size:.88rem;color:rgba(255,255,255,.6);font-weight:700;">
            <i class="fa-solid fa-shield-halved me-2" style="color:#34d399;"></i><?= $champsDisplayCount ?> champions unlocked
          </div>
        </div>
        <?php endif ?>
        <?php if (!empty($roles)): ?>
        <div class="av-tag-section" style="margin-top:10px;">
          <div class="av-tag-label">Roles</div>
          <div class="av-tag-list">
            <?php foreach ($roles as $r): ?>
              <span class="av-tag av-tag--role"><?= $h(trim($r)) ?></span>
            <?php endforeach ?>
          </div>
        </div>
        <?php endif ?>
      <?php endif ?>
    </div>
    <?php endif ?>

    <!-- Quick Actions -->
    <div class="av-sidebar-card">
      <div class="av-sc-header">
        <span class="av-sc-icon" style="background:rgba(255,255,255,.04);border-color:rgba(255,255,255,.08);"><i class="fa-solid fa-bolt" style="color:rgba(255,255,255,.4);font-size:.72rem;"></i></span>
        <span class="av-sc-title">Quick Actions</span>
      </div>
      <div style="padding:12px 16px;display:flex;flex-direction:column;gap:8px;">
        <a href="<?= ADMN_URL ?>/selling-accounts" class="av-btn-ghost w-100 justify-content-center">
          <i class="fa-duotone fa-list me-1"></i> All Accounts
        </a>
        <?php if ($seller): ?>
        <a href="<?= ADMN_URL ?>/seller/<?= (int)$seller['id'] ?>/accounts" class="av-btn-ghost w-100 justify-content-center">
          <i class="fa-duotone fa-store me-1"></i> Seller's Accounts
        </a>
        <?php endif ?>
        <?php if ($buyer): ?>
        <a href="<?= ADMN_URL ?>/client/<?= (int)$buyer['id'] ?>" class="av-btn-ghost w-100 justify-content-center">
          <i class="fa-duotone fa-user me-1"></i> Buyer Profile
        </a>
        <?php endif ?>
      </div>
    </div>

  </div>
  <!-- ── RIGHT col end ── -->

</div><!-- /row -->
</div><!-- /admin-account-view -->


<?= $this->start('scripts') ?>
<script>
(function () {
    // Scroll chat to bottom
    const chatEl = document.getElementById('adminChatMessages');
    if (chatEl) chatEl.scrollTop = chatEl.scrollHeight;

    // Copy buttons
    document.querySelectorAll('.js-copy').forEach(btn => {
        btn.addEventListener('click', function () {
            const text = btn.getAttribute('data-copy');
            if (!text) return;
            navigator.clipboard.writeText(text).then(() => {
                const i = btn.querySelector('i');
                if (i) { i.className = 'fa-solid fa-check'; setTimeout(() => i.className = 'fa-duotone fa-copy', 1500); }
            });
        });
    });

    // Admin poke client
    document.querySelectorAll('.js-admin-poke-client').forEach(function(btn) {
        btn.addEventListener('click', function() {
            if (btn.disabled) return;
            const oldHtml = btn.innerHTML;
            btn.disabled = true;
            btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Sending...';
            $.post('<?= AJAX_URL ?>', {
                action: 'admin_poke_client',
                ref_type: btn.getAttribute('data-ref-type') || 'account',
                id: btn.getAttribute('data-id') || '<?= (int)$id ?>'
            }, function(resp) {
                let d = resp;
                try { if (typeof resp === 'string') d = JSON.parse(resp); } catch (e) {}
                if (d && d.sendToast && typeof create_toast === 'function') {
                    create_toast(d.sendToast.type || 'primary', d.sendToast.title || 'Notice', d.sendToast.message || 'Done');
                }
                if (d && d.playSound) {
                    try { new Audio(asset_url + '/core/dash/audio/' + d.playSound + '.mp3').play(); } catch(e) {}
                }
            }).always(function() {
                btn.disabled = false;
                btn.innerHTML = oldHtml;
            });
        });
    });


    // Chat: attachments, paste screenshots, emojis, submit
    const chatForm   = document.getElementById('adminChatForm');
    const chatInput  = document.getElementById('adminChatInput');
    const fileInput  = document.getElementById('adminChatFile');
    const attachBtn  = document.getElementById('adminChatAttachBtn');
    const preview    = document.getElementById('adminChatPreview');
    const previewImg = document.getElementById('adminChatPreviewImg');
    const prevName   = document.getElementById('adminChatPreviewName');
    const removeBtn  = document.getElementById('adminChatRemoveBtn');
    const errBox     = document.getElementById('adminChatError');
    const sendBtn    = document.getElementById('adminChatSendBtn');
    let previewUrl   = null;

    function setChatError(msg) {
        if (!errBox) return;
        if (!msg) { errBox.classList.add('d-none'); errBox.textContent = ''; return; }
        errBox.textContent = msg;
        errBox.classList.remove('d-none');
    }
    function clearChatFile() {
        if (previewUrl) { URL.revokeObjectURL(previewUrl); previewUrl = null; }
        if (fileInput)  fileInput.value = '';
        if (preview)    preview.classList.add('d-none');
        if (previewImg) previewImg.src = '';
        if (prevName)   prevName.textContent = '';
    }
    function showChatFile(file) {
        if (!file) return clearChatFile();
        if (!/^image\/(png|jpe?g|gif)$/i.test(file.type)) {
            setChatError('Only PNG/JPG/JPEG/GIF allowed.');
            clearChatFile();
            return;
        }
        setChatError('');
        if (previewUrl) URL.revokeObjectURL(previewUrl);
        previewUrl = URL.createObjectURL(file);
        if (previewImg) previewImg.src = previewUrl;
        if (prevName) prevName.textContent = file.name || 'image';
        if (preview) preview.classList.remove('d-none');
    }

    if (attachBtn && fileInput) {
        attachBtn.addEventListener('click', () => { setChatError(''); fileInput.click(); });
        fileInput.addEventListener('change', () => showChatFile(fileInput.files && fileInput.files[0]));
    }
    if (removeBtn) removeBtn.addEventListener('click', () => { setChatError(''); clearChatFile(); });

    document.addEventListener('paste', function(e) {
        if (!chatForm || !fileInput || fileInput.disabled) return;
        const active = document.activeElement;
        if (!chatForm.contains(active) && active !== chatInput) return;
        const items = (e.clipboardData && e.clipboardData.items) ? e.clipboardData.items : [];
        for (const it of items) {
            if (it && it.type && it.type.indexOf('image/') === 0) {
                const blob = it.getAsFile();
                if (!blob) continue;
                const file = new File([blob], 'pasted-image.png', { type: blob.type || 'image/png' });
                const dt = new DataTransfer();
                dt.items.add(file);
                fileInput.files = dt.files;
                showChatFile(file);
                e.preventDefault();
                break;
            }
        }
    });

    if (chatForm) {
        chatForm.addEventListener('submit', async function (e) {
            e.preventDefault();
            const msg = chatInput ? chatInput.value.trim() : '';
            const hasFile = fileInput && fileInput.files && fileInput.files.length > 0;
            if (!msg && !hasFile) { setChatError('Please type a message or attach an image.'); return; }
            setChatError('');
            if (sendBtn) {
                sendBtn.disabled = true;
                const prog = sendBtn.querySelector('.indicator-progress');
                if (prog) prog.classList.remove('d-none');
            }
            const fd = new FormData(chatForm);
            try {
                const res = await fetch(chatForm.getAttribute('action') || '<?= AJAX_URL ?>', { method: 'POST', body: fd, credentials: 'same-origin' });
                let json = {};
                try { json = await res.json(); } catch (_) {}
                if (chatInput) chatInput.value = '';
                clearChatFile();
                if (json.messages || json.success || res.ok) window.location.reload();
            } catch (err) {
                console.error(err);
                setChatError('Upload failed. Please try again.');
            } finally {
                if (sendBtn) {
                    sendBtn.disabled = false;
                    const prog = sendBtn.querySelector('.indicator-progress');
                    if (prog) prog.classList.add('d-none');
                }
            }
        });
    }

    (function initAdminEmojiPicker() {
        const btn = document.getElementById('adminEmojiBtn');
        const picker = document.getElementById('adminEmojiPicker');
        const grid = document.getElementById('adminEmojiGrid');
        const search = document.getElementById('adminEmojiSearch');
        const tabs = document.getElementById('adminEmojiTabs');
        if (!btn || !picker || !grid || !chatInput) return;

        const cats = {
            recent: ['👍','❤️','😂','🙏','🔥','✅','😄','😎','👀','💯','🎉','🚀','😉','👌','🤝','🙌'],
            smileys: ['😀','😃','😄','😁','😆','😂','🤣','😊','😉','😍','😘','😎','🥳','😇','🙂','🙃','😅','😋','🤔','😴','😭','😡'],
            gestures: ['👍','👎','👌','✌️','🤞','🤝','🙏','👏','🙌','🫶','💪','👋','🤙','☝️','👇','👀'],
            animals: ['🐶','🐱','🐭','🐹','🐰','🦊','🐻','🐼','🐨','🐯','🦁','🐸','🐵','🐧','🐔','🐺'],
            food: ['🍎','🍌','🍓','🍕','🍔','🍟','🌮','🍣','🍩','🍪','🍫','☕','🥤','🍺','🍜','🍗'],
            activities: ['⚽','🏀','🎮','🎯','🎲','🎧','🎸','🏆','🥇','🚴','🏋️','🎬','🎨','🎤','🎉','🎁'],
            travel: ['✈️','🚗','🚕','🚌','🚆','🚀','🛸','⚓','🏝️','🏔️','🌍','🌙','☀️','⭐','🔥','⚡'],
            objects: ['💡','📌','📎','🔒','🔑','💎','🧿','💰','💳','📦','🛒','📱','💻','⌨️','🖱️','🧾'],
            symbols: ['❤️','🧡','💛','💚','💙','💜','🖤','✅','❌','⚠️','❓','❗','💯','➕','➖','➡️']
        };
        let current = 'recent';

        function insertEmoji(emoji) {
            const start = chatInput.selectionStart || chatInput.value.length;
            const end = chatInput.selectionEnd || chatInput.value.length;
            chatInput.value = chatInput.value.slice(0, start) + emoji + chatInput.value.slice(end);
            chatInput.focus();
            const pos = start + emoji.length;
            chatInput.setSelectionRange(pos, pos);
        }
        function render(list) {
            grid.innerHTML = '';
            list.forEach(e => {
                const b = document.createElement('button');
                b.type = 'button';
                b.className = 'lb-emoji-picker__item';
                b.textContent = e;
                b.addEventListener('click', () => insertEmoji(e));
                grid.appendChild(b);
            });
        }
        function filter() {
            const q = (search && search.value || '').trim().toLowerCase();
            const all = Object.values(cats).flat();
            render(q ? all.filter(e => e.includes(q)) : cats[current]);
        }

        btn.addEventListener('click', function(e) {
            e.stopPropagation();
            picker.classList.toggle('d-none');
            filter();
            if (!picker.classList.contains('d-none') && search) search.focus();
        });
        if (tabs) tabs.addEventListener('click', function(e) {
            const tab = e.target.closest('[data-cat]');
            if (!tab) return;
            current = tab.getAttribute('data-cat');
            tabs.querySelectorAll('.lb-emoji-picker__tab').forEach(t => t.classList.remove('is-active'));
            tab.classList.add('is-active');
            if (search) search.value = '';
            filter();
        });
        if (search) search.addEventListener('input', filter);
        document.addEventListener('click', function(e) {
            if (!picker.contains(e.target) && e.target !== btn && !btn.contains(e.target)) {
                picker.classList.add('d-none');
            }
        });
        render(cats[current]);
    })();
})();
</script>
<?= $this->end() ?>

<!-- ── EDIT OFFCANVAS ── -->
<div class="offcanvas offcanvas-end custom-offcanvas item-canvas" tabindex="-1" id="editAccountCanvas" data-bs-backdrop="static" data-bs-keyboard="true" style="width:52vw!important;">
  <div class="offcanvas-header" style="border-bottom:1px solid rgba(255,255,255,.07);padding:16px 22px;flex-shrink:0;">
    <h5 class="offcanvas-title mb-0" style="font-weight:900;font-size:1rem;">
      <i class="fa-duotone fa-pen me-2" style="color:#c4b5fd;"></i> Edit Account #<?= $id ?>
    </h5>
    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas"></button>
  </div>

  <!-- Step indicator -->
  <div class="oc-steps" style="flex-shrink:0;display:flex;align-items:center;padding:14px 22px;border-bottom:1px solid rgba(255,255,255,.07);gap:0;">
    <div class="oc-step active" id="editStep1"><div class="oc-step-num">1</div><div class="oc-step-label">Listing Info</div></div>
    <div class="oc-step-line" id="editLine1"></div>
    <div class="oc-step" id="editStep2"><div class="oc-step-num">2</div><div class="oc-step-label">Game Data</div></div>
    <div class="oc-step-line" id="editLine2"></div>
    <div class="oc-step" id="editStep3"><div class="oc-step-num">3</div><div class="oc-step-label">Credentials</div></div>
    <div class="oc-step-line" id="editLine3"></div>
    <div class="oc-step" id="editStep4"><div class="oc-step-num">4</div><div class="oc-step-label">Images</div></div>
  </div>

  <div class="offcanvas-body" style="flex:1!important;overflow:hidden!important;display:flex!important;flex-direction:column!important;padding:0!important;">
    <form action="<?= AJAX_URL ?>" method="POST" enctype="multipart/form-data" class="ajax-form" id="editAccountForm" style="flex:1;display:flex;flex-direction:column;overflow:hidden;min-height:0;">
      <input type="hidden" name="action"     value="admin_update_selling_account">
      <input type="hidden" name="id"         value="<?= $id ?>">
      <input type="hidden" name="images_order" id="editImagesOrder" value='<?= htmlspecialchars(json_encode(json_decode($account['images'] ?? '[]', true) ?: []), ENT_QUOTES) ?>'>

      <div class="oc-scroll" style="flex:1;overflow-y:auto;padding:20px 22px;">

        <!-- STEP 1: Listing Info -->
        <div class="js-edit-step" data-step="1">
          <div class="oc-section-label"><i class="fa-solid fa-tag"></i> Listing Info</div>
          <div class="row g-3 mb-3">
            <div class="col-12">
              <label class="form-label">Title <span style="color:#f87171;">*</span></label>
              <input type="text" class="form-control" name="title" value="<?= $h($account['title'] ?? '') ?>" required>
            </div>
            <div class="col-md-6">
              <label class="form-label">Price (€) <span style="color:#f87171;">*</span></label>
              <div class="input-group">
                <span class="input-group-text">€</span>
                <input type="text" class="form-control" name="price" id="editPriceInput" value="<?= number_format($priceCents / 100, 2, '.', '') ?>" required>
                <span class="input-group-text">EUR</span>
              </div>
              <div class="form-text mt-1">Seller Earnings: <strong style="color:#4ade80;">€<span id="editEarningsPreview"><?= $earningsEur ?></span></strong> (after <?= $sellerFeePct ?>% fee)</div>
            </div>
            <div class="col-md-6">
              <label class="form-label">Server</label>
              <select class="form-select" name="server">
                <?php foreach (['EUW','EUNE','NA','TR','BR','LAS','LAN','RU','OCE'] as $srv): ?>
                  <option value="<?= $srv ?>" <?= strtoupper($account['server'] ?? '') === $srv ? 'selected' : '' ?>><?= $srv ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-md-6">
              <label class="form-label">Delivery Type</label>
              <select class="form-select" name="delivery_type">
                <option value="instant" <?= ($account['delivery_type'] ?? '') === 'instant' ? 'selected' : '' ?>>⚡ Instant Delivery</option>
                <option value="manual"  <?= ($account['delivery_type'] ?? '') === 'manual'  ? 'selected' : '' ?>>📦 Manual Delivery</option>
              </select>
            </div>
            <div class="col-md-6">
              <label class="form-label">Status</label>
              <select class="form-select" name="active">
                <option value="1" <?= $active ? 'selected' : '' ?>>✅ Active</option>
                <option value="0" <?= !$active ? 'selected' : '' ?>>🚫 Unlisted</option>
              </select>
            </div>
            <div class="col-12">
              <label class="form-label">Description <span style="color:#f87171;">*</span></label>
              <textarea class="form-control" rows="4" name="description" required><?= $h($account['description'] ?? '') ?></textarea>
            </div>
            <div class="col-12">
              <label class="form-label">Delivery Instructions</label>
              <textarea class="form-control" rows="3" name="delivery_instructions" placeholder="Instructions shown to buyer after purchase..."><?= $h($account['delivery_instructions'] ?? '') ?></textarea>
            </div>
          </div>
        </div>

        <!-- STEP 2: Game Data -->
        <div class="js-edit-step" data-step="2" style="display:none;">
          <div class="oc-section-label"><i class="fa-solid fa-gamepad"></i> Rank</div>
          <?php
          $rankOptions = [0=>'Unranked',1=>'Iron',2=>'Bronze',3=>'Silver',4=>'Gold',5=>'Platinum',6=>'Emerald',7=>'Diamond',8=>'Master',9=>'Grandmaster',10=>'Challenger'];
          $divOptions  = [1=>'IV',2=>'III',3=>'II',4=>'I'];
          $rankSets = [
            ['current', 'Current Rank', 'current_rank', 'current_division', 'current_lp'],
            ['flex',    'Flex Rank',    'flex_rank',    'flex_division',    'flex_lp'],
            ['previous','Previous Rank','previous_rank','previous_division','previous_lp'],
          ];
          foreach ($rankSets as [$key, $label, $rankField, $divField, $lpField]):
            $rankValue = (int)($account[$rankField] ?? 0);
            $showDivision = $rankValue >= 1 && $rankValue <= 7;
            $showLp = $rankValue >= 1 && $rankValue <= 10;
          ?>
          <div class="row g-2 mb-3">
            <div class="col-12"><label class="form-label"><?= $label ?></label></div>
            <div class="col-5">
              <select class="form-select js-rank-sel" name="<?= $rankField ?>" data-key="<?= $key ?>">
                <?php foreach ($rankOptions as $v => $l): ?>
                  <option value="<?= $v ?>" <?= $rankValue === $v ? 'selected' : '' ?>><?= $l ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-4 js-div-wrap-<?= $key ?>" <?= !$showDivision ? 'style="display:none"' : '' ?>>
              <select class="form-select" name="<?= $divField ?>">
                <?php foreach ($divOptions as $v => $l): ?>
                  <option value="<?= $v ?>" <?= (int)($account[$divField] ?? 1) === $v ? 'selected' : '' ?>><?= $l ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-3 js-lp-wrap-<?= $key ?>" <?= !$showLp ? 'style="display:none"' : '' ?>>
              <input type="number" class="form-control" name="<?= $lpField ?>" placeholder="LP" value="<?= (int)($account[$lpField] ?? 0) ?>" min="0" max="9999">
            </div>
          </div>
          <?php endforeach; ?>

          <div class="oc-section-label" style="margin-top:16px;"><i class="fa-solid fa-chart-bar"></i> Account Stats</div>
          <div class="row g-3 mb-3">
            <div class="col-md-4">
              <label class="form-label">Level</label>
              <input type="number" class="form-control" name="level" value="<?= (int)($account['level'] ?? 0) ?>" min="1">
            </div>
            <div class="col-md-4">
              <label class="form-label">Honor Level</label>
              <input type="number" class="form-control" name="honor_level" value="<?= (int)($account['honor_level'] ?? 0) ?>" min="0" max="5">
            </div>
            <div class="col-md-4">
              <label class="form-label">Skin Count</label>
              <input type="number" class="form-control" name="skin_count" value="<?= (int)($account['skin_count'] ?? 0) ?>" min="0">
            </div>
            <div class="col-md-4">
              <label class="form-label">Blue Essence</label>
              <input type="number" class="form-control" name="blue_essence" value="<?= (int)($account['blue_essence'] ?? 0) ?>" min="0">
            </div>
            <div class="col-md-4">
              <label class="form-label">Orange Essence</label>
              <input type="number" class="form-control" name="orange_essence" value="<?= (int)($account['orange_essence'] ?? 0) ?>" min="0">
            </div>
            <div class="col-md-4">
              <label class="form-label">Riot Points</label>
              <input type="number" class="form-control" name="riot_points" value="<?= (int)($account['riot_points'] ?? 0) ?>" min="0">
            </div>
            <div class="col-md-6">
              <label class="form-label">Win Rate (%)</label>
              <input type="number" class="form-control" name="winrate_percent" value="<?= (int)($account['winrate_percent'] ?? 0) ?>" min="0" max="100">
            </div>
            <div class="col-md-6">
              <label class="form-label">Level Up Method</label>
              <select class="form-select" name="level_up_method">
                <option value="by_hand" <?= ($account['level_up_method'] ?? 'by_hand') === 'by_hand' ? 'selected' : '' ?>>By Hand</option>
                <option value="botted"  <?= ($account['level_up_method'] ?? '') === 'botted' ? 'selected' : '' ?>>Botted</option>
              </select>
            </div>
          </div>
        </div>

        <!-- STEP 3: Credentials -->
        <div class="js-edit-step" data-step="3" style="display:none;">
          <div class="oc-section-label"><i class="fa-solid fa-key"></i> Credentials <span style="font-size:.62rem;color:#f87171;border:1px solid rgba(239,68,68,.3);background:rgba(239,68,68,.08);padding:1px 8px;border-radius:99px;margin-left:4px;font-weight:800;">Admin Only</span></div>
          <div class="row g-3 mb-3">
            <?php
            $credFields = [
              'in_game_name'   => 'In-Game Name',
              'login'          => 'LoL Login',
              'password'       => 'LoL Password',
              'riot_username'  => 'Riot Username',
              'riot_password'  => 'Riot Password',
              'email'          => 'Email',
              'email_password' => 'Email Password',
              '2fa'            => '2FA Secret',
            ];
            foreach ($credFields as $field => $label): ?>
            <div class="col-md-6">
              <label class="form-label"><?= $label ?></label>
              <input type="text" class="form-control" name="<?= $field ?>" value="<?= $h($account[$field] ?? '') ?>" autocomplete="off" style="font-family:monospace;">
            </div>
            <?php endforeach; ?>
          </div>
        </div>

        <!-- STEP 4: Images -->
        <div class="js-edit-step" data-step="4" style="display:none;">
          <div class="oc-section-label"><i class="fa-solid fa-images"></i> Image Gallery</div>
          <div id="editGalleryDropzone" class="account-upload-box text-center p-4" style="border:2px dashed rgba(255,255,255,.12);border-radius:12px;background:rgba(255,255,255,.02);cursor:pointer;">
            <i class="fa-duotone fa-images fa-xl text-primary mb-2 d-block"></i>
            <h6 class="mb-1" style="font-size:.88rem;font-weight:800;">Upload / Replace Images</h6>
            <p class="text-muted small mb-2" style="font-size:.78rem;">Click, drag & drop, or paste with <strong>Ctrl+V</strong></p>
            <button type="button" class="btn btn-primary btn-sm" id="editGalleryBtn">Select Images</button>
            <input class="d-none" type="file" id="editGalleryInput" name="images[]" multiple accept="image/*">
          </div>
          <small class="d-block mt-2 text-muted">Drag tiles to reorder. First image = main cover.</small>
          <div id="editGalleryPreview" class="row mt-3 g-2"></div>
        </div>

      </div><!-- /oc-scroll -->

      <!-- Footer -->
      <div class="oc-footer" style="flex-shrink:0;display:flex;align-items:center;justify-content:space-between;padding:12px 22px;border-top:1px solid rgba(255,255,255,.07);background:#25282a;">
        <button type="button" class="oc-btn-prev js-edit-prev" style="display:none;">
          <i class="fa-solid fa-chevron-left"></i> Previous
        </button>
        <div class="ms-auto d-flex gap-2">
          <button type="button" class="oc-btn-next js-edit-next">Next <i class="fa-solid fa-chevron-right"></i></button>
          <button type="submit" class="oc-btn-next js-edit-submit" style="display:none;"><i class="fa-solid fa-floppy-disk"></i> Save Changes</button>
        </div>
      </div>

    </form>
  </div>
</div>

<style>
.item-canvas.custom-offcanvas{display:flex!important;flex-direction:column!important;height:100%!important;}
.oc-steps{display:flex;align-items:center;gap:0;}
.oc-step{display:flex;align-items:center;gap:8px;flex:1;}
.oc-step-num{width:28px;height:28px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:.78rem;font-weight:900;background:rgba(255,255,255,.07);border:1px solid rgba(255,255,255,.12);color:rgba(255,255,255,.5);}
.oc-step.active .oc-step-num{background:linear-gradient(135deg,#6d5cff,#b05cff);border-color:transparent;color:#fff;}
.oc-step.done .oc-step-num{background:rgba(74,222,128,.15);border-color:rgba(74,222,128,.3);color:#4ade80;}
.oc-step-label{font-size:.8rem;font-weight:700;color:rgba(255,255,255,.4);}
.oc-step.active .oc-step-label{color:#c4b5fd;font-weight:900;}
.oc-step.done .oc-step-label{color:rgba(255,255,255,.6);}
.oc-step-line{flex:1;height:1px;background:rgba(255,255,255,.08);margin:0 8px;}
.oc-step-line.done{background:rgba(74,222,128,.3);}
.oc-section-label{display:flex;align-items:center;gap:6px;font-size:.68rem;font-weight:900;text-transform:uppercase;letter-spacing:.09em;color:rgba(255,255,255,.3);margin:14px 0 10px;padding-bottom:6px;border-bottom:1px solid rgba(255,255,255,.06);}
.oc-btn-next{display:inline-flex;align-items:center;gap:.45rem;background:linear-gradient(135deg,#6d5cff,#b05cff);border:none;border-radius:11px;padding:8px 20px;font-size:.87rem;font-weight:900;color:#fff;cursor:pointer;}
.oc-btn-prev{display:inline-flex;align-items:center;gap:.4rem;background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.10);border-radius:11px;padding:8px 16px;font-size:.87rem;font-weight:700;color:rgba(255,255,255,.65);cursor:pointer;}
.account-upload-box.dragover{border-color:#6366f1!important;background:rgba(99,102,241,.08)!important;}
.gallery-preview-tile{position:relative;overflow:hidden;border-radius:.5rem;background:rgba(255,255,255,.02);cursor:grab;}
.gallery-preview-tile img{width:100%!important;height:140px!important;object-fit:cover;display:block;}
.gallery-preview-tile.is-main{outline:2px solid rgba(99,102,241,.9);outline-offset:2px;}
.gallery-preview-badge{position:absolute;top:.5rem;left:.5rem;padding:.25rem .5rem;border-radius:999px;background:rgba(99,102,241,.95);color:#fff;font-size:.68rem;font-weight:600;z-index:2;}
.gallery-preview-overlay{position:absolute;inset:0;background:rgba(220,53,69,.30);display:flex;align-items:center;justify-content:center;opacity:0;transition:opacity .25s;}
.gallery-preview-tile:hover .gallery-preview-overlay{opacity:1;}
.gallery-preview-remove{border:0;background:rgba(220,53,69,.95);color:#fff;width:44px;height:44px;border-radius:12px;display:inline-flex;align-items:center;justify-content:center;}
@media(max-width:992px){.item-canvas.custom-offcanvas{width:100vw!important;}}
</style>

<script>
(function(){
  'use strict';

  /* ── Earnings preview ── */
  const priceInput = document.getElementById('editPriceInput');
  const earningsEl = document.getElementById('editEarningsPreview');
  const FEE = <?= (float)$sellerFeePct ?>;
  if (priceInput && earningsEl) {
    priceInput.addEventListener('input', function(){
      const v = parseFloat(String(this.value).replace(',','.')) || 0;
      earningsEl.textContent = (v * (1 - FEE/100)).toFixed(2);
    });
  }

  /* ── Rank selects: show/hide division & LP ── */
  document.querySelectorAll('.js-rank-sel').forEach(function(sel){
    function toggle(){
      const key = sel.getAttribute('data-key');
      const v = parseInt(sel.value, 10);
      const showDiv = v >= 1 && v <= 7;
      const showLp = v >= 1 && v <= 10;
      const divWrap = document.querySelector('.js-div-wrap-' + key);
      const lpWrap  = document.querySelector('.js-lp-wrap-' + key);
      if (divWrap) divWrap.style.display = showDiv ? '' : 'none';
      if (lpWrap)  lpWrap.style.display  = showLp ? '' : 'none';
    }
    sel.addEventListener('change', toggle);
    toggle();
  });

  /* ── Step wizard ── */
  const TOTAL_STEPS = 4;
  let currentStep = 1;

  function markStepState(){
    for (var i = 1; i <= TOTAL_STEPS; i++) {
      var stepEl = document.getElementById('editStep' + i);
      if (!stepEl) continue;
      stepEl.classList.remove('active','done');
      var num = stepEl.querySelector('.oc-step-num');
      if (i < currentStep) {
        stepEl.classList.add('done');
        if (num) num.innerHTML = '<i class="fa-solid fa-check" style="font-size:.7rem;"></i>';
      } else if (i === currentStep) {
        stepEl.classList.add('active');
        if (num) num.textContent = i;
      } else {
        if (num) num.textContent = i;
      }
      var line = document.getElementById('editLine' + i);
      if (line) line.classList.toggle('done', currentStep > i);
    }
  }

  function showStep(n){
    currentStep = Math.max(1, Math.min(TOTAL_STEPS, n));
    document.querySelectorAll('.js-edit-step').forEach(function(el){
      el.style.display = parseInt(el.dataset.step, 10) === currentStep ? '' : 'none';
    });
    var prevBtn   = document.querySelector('.js-edit-prev');
    var nextBtn   = document.querySelector('.js-edit-next');
    var submitBtn = document.querySelector('.js-edit-submit');
    if (prevBtn)   prevBtn.style.display   = currentStep > 1 ? '' : 'none';
    if (nextBtn)   nextBtn.style.display   = currentStep < TOTAL_STEPS ? '' : 'none';
    if (submitBtn) submitBtn.style.display = currentStep === TOTAL_STEPS ? '' : 'none';
    markStepState();
    var scroll = document.querySelector('#editAccountCanvas .oc-scroll');
    if (scroll) scroll.scrollTop = 0;
  }

  document.querySelector('.js-edit-next')?.addEventListener('click', function(){ showStep(currentStep + 1); });
  document.querySelector('.js-edit-prev')?.addEventListener('click', function(){ showStep(currentStep - 1); });

  // Reset to step 1 when offcanvas opens
  var canvas = document.getElementById('editAccountCanvas');
  if (canvas) canvas.addEventListener('show.bs.offcanvas', function(){ showStep(1); });

  showStep(1);

  /* ── Image Gallery ── */
  var galleryItems = [];
  var dragFromIndex = null;
  var tempSeq = 0;
  var fileInput = document.getElementById('editGalleryInput');
  var fileBtn   = document.getElementById('editGalleryBtn');
  var dropzone  = document.getElementById('editGalleryDropzone');
  var preview   = document.getElementById('editGalleryPreview');
  var orderInput= document.getElementById('editImagesOrder');

  // Init with existing images
  try {
    var existing = JSON.parse(orderInput ? orderInput.value : '[]') || [];
    galleryItems = existing.map(function(url){ return {type:'existing', url:url}; });
  } catch(e) { galleryItems = []; }

  function escHtml(s){ return String(s==null?'':s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;'); }
  function fileKey(f){ return f.name+'__'+f.size+'__'+f.lastModified; }

  function syncAndRender(){
    if (!preview) return;
    var dt = new DataTransfer();
    galleryItems.filter(function(x){ return x.type==='new'&&x.file; }).forEach(function(x){ dt.items.add(x.file); });
    if (fileInput) fileInput.files = dt.files;
    if (orderInput) orderInput.value = JSON.stringify(galleryItems.map(function(x){ return x.type==='existing'?x.url:x.tempId; }));
    preview.innerHTML = '';
    galleryItems.forEach(function(item, i){
      var src = item.type==='existing' ? item.url : URL.createObjectURL(item.file);
      var col = document.createElement('div');
      col.className = 'col-6 col-md-3';
      col.innerHTML = '<div class="gallery-preview-tile'+(i===0?' is-main':'')+'" draggable="true" data-index="'+i+'">'
        +'<div class="gallery-preview-badge" style="left:auto;right:.5rem;background:rgba(0,0,0,.45)">#'+(i+1)+'</div>'
        +(i===0?'<div class="gallery-preview-badge">MAIN</div>':'')
        +'<img src="'+escHtml(src)+'" alt="Preview">'
        +'<div class="gallery-preview-overlay"><button type="button" class="gallery-preview-remove" data-remove-index="'+i+'">×</button></div>'
        +'</div>';
      preview.appendChild(col);
    });
  }

  function addFiles(files){
    var incoming = Array.from(files||[]).filter(function(f){ return f&&f.type&&f.type.indexOf('image/')===0; });
    var existing = new Set(galleryItems.filter(function(x){ return x.type==='new'; }).map(function(x){ return fileKey(x.file); }));
    incoming.forEach(function(f){ var k=fileKey(f); if(!existing.has(k)){ galleryItems.push({type:'new',file:f,tempId:'__new__'+(tempSeq++)}); existing.add(k); }});
    syncAndRender();
  }

  if (fileInput) fileInput.addEventListener('change', function(){ addFiles(fileInput.files); });
  if (fileBtn)   fileBtn.addEventListener('click', function(e){ e.preventDefault(); if(fileInput) fileInput.click(); });
  if (dropzone){
    dropzone.addEventListener('click', function(e){ if(e.target.closest('button')) return; if(fileInput) fileInput.click(); });
    ['dragenter','dragover'].forEach(function(ev){ dropzone.addEventListener(ev,function(e){e.preventDefault();dropzone.classList.add('dragover');}); });
    ['dragleave','drop'].forEach(function(ev){ dropzone.addEventListener(ev,function(e){e.preventDefault();dropzone.classList.remove('dragover');}); });
    dropzone.addEventListener('drop', function(e){ addFiles((e.dataTransfer||{}).files||[]); });
  }
  if (preview){
    preview.addEventListener('click', function(e){ var btn=e.target.closest('.gallery-preview-remove'); if(!btn) return; var idx=parseInt(btn.getAttribute('data-remove-index'),10); if(!isNaN(idx)){ galleryItems.splice(idx,1); syncAndRender(); }});
    preview.addEventListener('dragstart', function(e){ var tile=e.target.closest('.gallery-preview-tile'); if(!tile) return; dragFromIndex=parseInt(tile.getAttribute('data-index'),10); });
    preview.addEventListener('dragover', function(e){ if(dragFromIndex!==null) e.preventDefault(); });
    preview.addEventListener('drop', function(e){ if(dragFromIndex===null) return; e.preventDefault(); var tile=e.target.closest('.gallery-preview-tile'); if(!tile) return; var to=parseInt(tile.getAttribute('data-index'),10); if(!isNaN(to)&&to!==dragFromIndex){ var moved=galleryItems.splice(dragFromIndex,1)[0]; galleryItems.splice(to,0,moved); syncAndRender(); } dragFromIndex=null; });
    preview.addEventListener('dragend', function(){ dragFromIndex=null; });
  }
  if (canvas) canvas.addEventListener('paste', function(e){ var items=(e.clipboardData||{}).items||[]; var files=[]; for(var i=0;i<items.length;i++){ if(items[i].kind==='file'){ var blob=items[i].getAsFile(); if(blob&&blob.type&&blob.type.indexOf('image/')===0) files.push(blob); }} if(files.length){e.preventDefault();addFiles(files);}});

  syncAndRender();
})();
</script>
