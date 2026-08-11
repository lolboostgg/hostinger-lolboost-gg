<?php
require_once dirname(__DIR__) . '/_seller_rank.php';
    $sold          = (int)($account['sold'] ?? 0);
    $is_order_view = strpos(trim(parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH), '/'), 'seller-area/account-order/') !== false;
    $is_refunded   = ($sold === 2);
    $effective_fee = seller_effective_fee_from_rank(is_array($seller_data ?? null) ? $seller_data : []);
    $account_id    = (int)$account['id'];

    $rank_labels = ['Unranked','Iron','Bronze','Silver','Gold','Platinum','Emerald','Diamond','Master','Grandmaster','Challenger'];
    $div_labels  = ['','IV','III','II','I'];
    $rank_label  = $rank_labels[(int)($account['current_rank'] ?? 0)] ?? 'Unranked';
    $_rank_idx   = (int)($account['current_rank'] ?? 0);
    $div_label   = ($_rank_idx === 0 || $_rank_idx >= 8) ? '' : ($div_labels[(int)($account['current_division'] ?? 0)] ?? '');
    $page_title  = htmlspecialchars($account['title'] ?? 'Account');

    $buyer         = $buyer        ?? null;
    $chat_messages = $chat_messages ?? [];

    $price_eur     = (float)($account['price'] ?? 0) / 100;
    $earnings      = round($price_eur * (1 - $effective_fee / 100), 2);

    $images = [];
    try { $images = json_decode($account['images'] ?? '[]', true) ?: []; } catch(Throwable $e) {}

    $champs = !empty($account['champions']) ? array_values(array_filter(explode('|', $account['champions']))) : [];
    $skins  = !empty($account['skins'])     ? array_values(array_filter(explode('|', $account['skins'])))     : [];
    $roles  = !empty($account['roles'])     ? array_values(array_filter(explode('|', $account['roles'])))     : [];



    if (!function_exists('seller_account_slugify')) {
        function seller_account_slugify($value): string {
            $value = trim((string)$value);
            if ($value === '') return '';
            if (function_exists('iconv')) {
                $converted = @iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $value);
                if ($converted !== false) $value = $converted;
            }
            $value = strtolower($value);
            $value = preg_replace('/[^a-z0-9]+/i', '-', $value);
            return trim((string)$value, '-');
        }
    }
    if (!function_exists('seller_account_roblox_icon_name')) {
        function seller_account_roblox_icon_name($value): string {
            $value = trim((string)$value);
            if ($value === '') return 'Others';
            if (function_exists('iconv')) {
                $converted = @iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $value);
                if ($converted !== false) $value = $converted;
            }
            $parts = preg_split('/[^a-z0-9]+/i', $value, -1, PREG_SPLIT_NO_EMPTY);
            if (!$parts) return 'Others';
            return implode('', array_map(static fn($part) => ucfirst(strtolower($part)), $parts));
        }
    }
    if (!function_exists('seller_account_asset_url')) {
        function seller_account_asset_url($path): string {
            $path = trim((string)$path);
            if ($path === '') return '';
            if (preg_match('#^https?://#i', $path) || str_starts_with($path, '//')) return $path;
            if (defined('BASE_URL') && str_starts_with($path, '/public/')) return rtrim(BASE_URL, '/') . $path;
            $assetUrl = defined('ASSET_URL') ? rtrim(ASSET_URL, '/') : rtrim(BASE_URL, '/') . '/public/assets';
            $path = preg_replace('#^/public/assets#', '', $path);
            $path = preg_replace('#^public/assets#', '', $path);
            return $assetUrl . '/' . ltrim($path, '/');
        }
    }
    $_gameRawInput = (string)($account['game'] ?? 'league-of-legends');
    $_gameRawSlug = seller_account_slugify($_gameRawInput ?: 'league-of-legends');
    $_s2s = ['lol'=>'lol','league-of-legends'=>'lol','leagu'=>'lol','valorant'=>'val','valor'=>'val','val'=>'val','teamfight-tactics'=>'tft','teamf'=>'tft','tft'=>'tft'];
    $game   = $_s2s[$_gameRawSlug] ?? $_gameRawSlug;
    $isLol  = ($game === 'lol');
    $isVal  = ($game === 'val');
    $isTft  = ($game === 'tft');
    $isGeneric = !$isLol && !$isVal && !$isTft;
    $accountGameSlug = $isLol ? 'league-of-legends' : ($isVal ? 'valorant' : ($isTft ? 'teamfight-tactics' : $_gameRawSlug));
    $accountGameNameMap = [
        'league-of-legends' => 'League of Legends',
        'valorant' => 'Valorant',
        'teamfight-tactics' => 'Teamfight Tactics',
        'roblox' => 'Roblox',
        'roblox-rivals' => 'Roblox Rivals',
    ];
    $accountGameName = $accountGameNameMap[$accountGameSlug] ?? ucwords(str_replace('-', ' ', $accountGameSlug));
    $accountGameIcon = seller_account_asset_url('/website/images/icons/' . $accountGameSlug . '.png');
    if (function_exists('util_get_all_games')) {
        try {
            foreach ((array)util_get_all_games(true) as $_gRow) {
                $_gSlug = seller_account_slugify($_gRow['slug'] ?? '');
                if ($_gSlug !== '' && $_gSlug === $accountGameSlug) {
                    if (!empty($_gRow['name'])) $accountGameName = (string)$_gRow['name'];
                    if (!empty($_gRow['icon'])) $accountGameIcon = seller_account_asset_url((string)$_gRow['icon']);
                    break;
                }
            }
        } catch (Throwable $e) {}
    }
    $game_data = [];
    if (!empty($account['game_data'])) {
        try { $game_data = json_decode((string)$account['game_data'], true) ?: []; } catch (Throwable $e) { $game_data = []; }
    }

    $accountExperienceName = '';
    if (!empty($game_data['games'])) $accountExperienceName = trim((string)$game_data['games']);
    if ($accountExperienceName === '' && !empty($game_data['experience_game'])) $accountExperienceName = trim((string)$game_data['experience_game']);
    if ($accountExperienceName === '' && !empty($game_data['experience'])) $accountExperienceName = trim((string)$game_data['experience']);
    $accountHeaderName = $accountExperienceName !== '' ? $accountExperienceName : $accountGameName;
    $accountHeaderIcon = $accountGameIcon;
    if ($accountGameSlug === 'roblox' && $accountExperienceName !== '') {
        $accountHeaderIcon = seller_account_asset_url('/website/images/roblox-icons/' . seller_account_roblox_icon_name($accountExperienceName) . '.webp');
    }

    // Val agents
    $valAgentsList = [];
    if ($isVal && !empty($game_data['agents']) && is_array($game_data['agents'])) {
        $valAgentsList = array_values(array_filter(array_map('trim', $game_data['agents'])));
    }
    $valAgentsDisplayCount = count($valAgentsList) > 0
        ? count($valAgentsList)
        : (isset($account['val_agent_count']) && $account['val_agent_count'] !== null && $account['val_agent_count'] !== ''
           ? (int)$account['val_agent_count'] : 0);

    // LoL count fallbacks
    $champsDisplayCount = count($champs) > 0 ? count($champs)
        : (isset($account['champion_count']) && $account['champion_count'] !== null && $account['champion_count'] !== ''
           ? (int)$account['champion_count'] : 0);
    $skinsDisplayCount = count($skins) > 0 ? count($skins)
        : (isset($account['skin_count']) && $account['skin_count'] !== null && $account['skin_count'] !== ''
           ? (int)$account['skin_count'] : 0);
?>
<?= $this->layout('seller/layouts/main', ['meta' => ['title' => $page_title . ' | LoLBoost.gg']]) ?>

<?= $this->start('styles') ?>
<link rel="stylesheet" href="<?= ASSET_URL ?>/origin/dash/vendor/tom-select/dist/css/tom-select.bootstrap5.css">
<style>
/* =============================================
   WRAP + CARDS  (mirrors admin-order-view)
============================================= */
.seller-account-view .account-page-wrap{
  padding-bottom:3rem;
}
@media(min-width:992px){
  .seller-account-view .account-page-wrap{
    padding-bottom:4rem;
  }
}
.seller-account-view .card{
  border-radius:.75rem;
  overflow:visible;
}
.seller-account-view .card-header{
  overflow:visible;
}
.seller-account-view .card-body,
.seller-account-view .card-footer{
  overflow:visible;
}
.seller-account-view .order-chat-card{
  overflow:hidden;
}
.seller-account-view .card-header-title{
  font-weight:700;
  font-size:1rem;
}

/* =============================================
   HEAD CARD  (lb-head)
============================================= */
.seller-account-view .lb-head{
  border-radius:.75rem;
  padding:0;
  overflow:hidden;
}
.seller-account-view .lb-head__top{
  display:flex;
  align-items:flex-start;
  justify-content:space-between;
  gap:1rem;
  padding:1.25rem 1.5rem;
  flex-wrap:wrap;
}
.seller-account-view .lb-head__left{
  display:flex;
  align-items:center;
  gap:.9rem;
  min-width:0;
  flex:1;
}
.seller-account-view .lb-head__icon{
  flex-shrink:0;
  width:2.5rem;
  height:2.5rem;
  border-radius:.6rem;
  background:rgba(99,102,241,.15);
  display:flex;
  align-items:center;
  justify-content:center;
  font-size:1.15rem;
  color:#6366f1;
}
.seller-account-view .lb-head__title{
  min-width:0;
}
.seller-account-view .lb-head__title-row{
  display:flex;
  align-items:center;
  gap:.6rem;
  flex-wrap:wrap;
}
.seller-account-view .lb-head__h1{
  font-size:1.2rem;
  font-weight:700;
  margin:0;
  line-height:1.3;
}
.seller-account-view .lb-head__id{
  font-size:.82rem;
  opacity:.45;
  font-weight:600;
  white-space:nowrap;
}
.seller-account-view .lb-head__sub{
  margin-top:.25rem;
  display:flex;
  align-items:center;
  gap:.5rem;
  flex-wrap:wrap;
}
.seller-account-view .lb-head__actions{
  flex-shrink:0;
  display:flex;
  align-items:center;
}
@media(min-width:992px){
  .seller-account-view .lb-head__actions{
    align-self:flex-start;
  }
}

/* =============================================
   META PILLS
============================================= */
.seller-account-view .lb-head__meta{
  display:flex;
  flex-wrap:wrap;
  gap:.4rem .25rem;
  padding:.75rem 1.5rem 1rem;
  border-top:1px solid rgba(255,255,255,.06);
}
[data-theme="light"] .seller-account-view .lb-head__meta{
  border-top-color:rgba(0,0,0,.07);
}
.seller-account-view .lb-meta-pill{
  display:inline-flex;
  align-items:center;
  gap:.35rem;
  border-radius:999px;
  padding:.28rem .7rem;
  font-size:.78rem;
  font-weight:600;
  background:rgba(255,255,255,.055);
  border:1px solid rgba(255,255,255,.08);
  line-height:1.4;
}
[data-theme="light"] .seller-account-view .lb-meta-pill{
  background:rgba(0,0,0,.04);
  border-color:rgba(0,0,0,.08);
}
.seller-account-view .lb-meta-pill__k{
  opacity:.5;
  font-size:.72rem;
  text-transform:uppercase;
  letter-spacing:.05em;
}
.seller-account-view .lb-meta-pill__v{
  font-weight:700;
}

/* =============================================
   STATUS PILL
============================================= */
.seller-account-view .lb-status{
  display:inline-flex;
  align-items:center;
  gap:.4rem;
  border-radius:999px;
  padding:.25rem .65rem;
  font-size:.78rem;
  font-weight:700;
  text-transform:uppercase;
  letter-spacing:.04em;
}
.seller-account-view .lb-status__dot{
  width:.5rem;
  height:.5rem;
  border-radius:50%;
  background:currentColor;
  opacity:.7;
}
.seller-account-view .lb-status--active{
  background:rgba(10,179,113,.12);
  color:#0ab371;
}
.seller-account-view .lb-status--sold{
  background:rgba(220,53,69,.12);
  color:#dc3545;
}

/* =============================================
   OVERVIEW GRID  (lb-ov)
============================================= */
.seller-account-view .lb-ov-grid{
  list-style:none;
  margin:0;
  padding:0;
  display:flex;
  flex-direction:column;
  gap:0;
}
.seller-account-view .lb-ov-item{
  display:grid;
  grid-template-columns:1.6rem 1fr auto;
  align-items:center;
  gap:.5rem;
  padding:.65rem 0;
  border-bottom:1px solid rgba(255,255,255,.06);
}
[data-theme="light"] .seller-account-view .lb-ov-item{
  border-bottom-color:rgba(0,0,0,.06);
}
.seller-account-view .lb-ov-item:last-child{
  border-bottom:none;
}
.seller-account-view .lb-ov-ico{
  font-size:1rem;
  text-align:center;
}
.seller-account-view .lb-ov-label{
  font-size:.8rem;
  opacity:.55;
  font-weight:600;
  text-transform:uppercase;
  letter-spacing:.04em;
}
.seller-account-view .lb-ov-value{
  font-weight:700;
  font-size:.88rem;
  text-align:right;
}

/* =============================================
   CREDENTIALS  (lb-acc)
============================================= */
.seller-account-view .lb-acc-details{
  display:flex;
  flex-direction:column;
  gap:0;
}
.seller-account-view .lb-acc-row{
  display:flex;
  align-items:center;
  justify-content:space-between;
  gap:.75rem;
  padding:.6rem 0;
  border-bottom:1px solid rgba(255,255,255,.06);
}
[data-theme="light"] .seller-account-view .lb-acc-row{
  border-bottom-color:rgba(0,0,0,.06);
}
.seller-account-view .lb-acc-row:last-child{
  border-bottom:none;
}
.seller-account-view .lb-acc-label{
  font-size:.8rem;
  opacity:.55;
  font-weight:600;
  white-space:nowrap;
}
.seller-account-view .lb-acc-actions{
  display:flex;
  align-items:center;
  gap:.4rem;
  min-width:0;
}
.seller-account-view .lb-acc-value{
  font-family:var(--bs-font-monospace,monospace);
  font-size:.82rem;
  font-weight:600;
  white-space:nowrap;
  overflow:hidden;
  text-overflow:ellipsis;
  max-width:160px;
  cursor:pointer;
}
.seller-account-view .lb-acc-value.is-missing{
  opacity:.3;
  font-style:italic;
}
.seller-account-view .lb-acc-copy{
  flex-shrink:0;
  background:none;
  border:none;
  color:rgba(255,255,255,.3);
  cursor:pointer;
  padding:0 .2rem;
  transition:color .15s;
  line-height:1;
}
.seller-account-view .lb-acc-copy:hover{
  color:#6366f1;
}
[data-theme="light"] .seller-account-view .lb-acc-copy{
  color:rgba(0,0,0,.3);
}
[data-theme="light"] .seller-account-view .lb-acc-copy:hover{
  color:#6366f1;
}

/* =============================================
   CHAT  (1:1 from admin-order-view)
============================================= */
.seller-account-view .order-chat-card .chat-bg{
  min-height:360px;
  max-height:480px;
  overflow-y:auto;
  padding:1.25rem;
  display:flex;
  flex-direction:column;
  gap:0;
  scroll-behavior:smooth;
}
.seller-account-view #chat_messages{
  min-height:300px;
  max-height:480px;
  overflow-y:auto;
  padding:1rem 1.25rem;
  display:flex;
  flex-direction:column;
  scroll-behavior:smooth;
}
.seller-account-view #chat_messages::-webkit-scrollbar{ width:5px; }
.seller-account-view #chat_messages::-webkit-scrollbar-track{ background:transparent; }
.seller-account-view #chat_messages::-webkit-scrollbar-thumb{ background:rgba(255,255,255,.12); border-radius:3px; }
[data-theme="light"] .seller-account-view #chat_messages::-webkit-scrollbar-thumb{ background:rgba(0,0,0,.12); }

/* chat icon buttons */
.seller-account-view .btn-chat-icon{
  width:2.15rem;
  height:2.15rem;
  padding:0;
  display:inline-flex;
  align-items:center;
  justify-content:center;
  border-radius:.5rem;
  flex-shrink:0;
}

/* attach preview */
.seller-account-view .lb-chat-attach-preview{
  display:flex;
  align-items:center;
  gap:.75rem;
  padding:.55rem .75rem;
  border-radius:.5rem;
  background:rgba(255,255,255,.05);
  border:1px solid rgba(255,255,255,.08);
}
[data-theme="light"] .seller-account-view .lb-chat-attach-preview{
  background:rgba(0,0,0,.03);
  border-color:rgba(0,0,0,.08);
}
.seller-account-view .lb-chat-attach-preview__thumb{
  width:2.5rem;
  height:2.5rem;
  border-radius:.35rem;
  overflow:hidden;
  flex-shrink:0;
}
.seller-account-view .lb-chat-attach-preview__thumb img{
  width:100%;
  height:100%;
  object-fit:cover;
}
.seller-account-view .lb-chat-attach-preview__meta{ min-width:0; flex:1; }
.seller-account-view .lb-chat-attach-preview__title{ font-weight:800; font-size:.82rem; }
.seller-account-view .lb-chat-attach-preview__name{ font-size:.78rem; opacity:.8; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; }
.seller-account-view .lb-chat-attach-preview__remove{
  flex-shrink:0;
  background:none;
  border:none;
  color:rgba(255,255,255,.45);
  cursor:pointer;
  padding:.2rem .35rem;
  border-radius:.35rem;
  transition:opacity .15s, background .15s;
}
.seller-account-view .lb-chat-attach-preview__remove:hover{ opacity:1; background:rgba(255,255,255,.06); }
[data-theme="light"] .seller-account-view .lb-chat-attach-preview__remove:hover{ background:rgba(0,0,0,.05); }

/* lb-msg  (identical to admin) */
.seller-account-view .lb-msg{
  display:flex;
  flex-direction:column;
  margin-bottom:.5rem;
  max-width:75%;
}
.seller-account-view .lb-msg--start{ align-self:flex-start; }
.seller-account-view .lb-msg--end{   align-self:flex-end;   }
.seller-account-view .lb-msg__head{
  display:flex;
  align-items:center;
  gap:.5rem;
  margin-bottom:.25rem;
}
.seller-account-view .lb-msg__head--end{
  flex-direction:row-reverse;
}
.seller-account-view .lb-msg__avatar{
  width:1.75rem;
  height:1.75rem;
  border-radius:50%;
  object-fit:cover;
  flex-shrink:0;
  cursor:default;
}
.seller-account-view .lb-msg__meta{ min-width:0; }
.seller-account-view .lb-msg__toprow{
  display:flex;
  align-items:center;
  gap:.35rem;
  flex-wrap:wrap;
}
.seller-account-view .lb-msg__name{
  font-weight:700;
  font-size:.8rem;
  line-height:1.3;
  display:flex;
  align-items:center;
  gap:.3rem;
}
.seller-account-view .lb-msg__time{
  font-size:.72rem;
  opacity:.45;
}
[data-theme="light"] .seller-account-view .lb-msg__time{ opacity:.5; }
.seller-account-view .lb-msg__bubble{
  padding:.55rem .85rem;
  border-radius:.75rem;
  font-size:.875rem;
  line-height:1.55;
  word-break:break-word;
  position:relative;
  background:rgba(255,255,255,.07);
}
[data-theme="light"] .seller-account-view .lb-msg__bubble{
  background:rgba(0,0,0,.05);
}
.seller-account-view .lb-msg--end .lb-msg__bubble{
  background:rgba(99,102,241,.22);
}
[data-theme="light"] .seller-account-view .lb-msg--end .lb-msg__bubble{
  background:rgba(99,102,241,.14);
}
.seller-account-view .lb-msg__bubble--deleted{
  opacity:.55;
  font-style:italic;
}
.seller-account-view .lb-msg__stamp{
  font-size:.7rem;
  opacity:.4;
  margin-top:.2rem;
}
.seller-account-view .lb-msg--end .lb-msg__stamp{
  text-align:right;
}
.seller-account-view .lb-msg__ticks{
  margin-left:.25rem;
}
.seller-account-view #chat_messages img{
  max-width:280px;
  max-height:220px;
  border-radius:.5rem;
  display:block;
  margin-top:.4rem;
  cursor:pointer;
  transition:opacity .15s;
}
.seller-account-view #chat_messages img:hover{ opacity:.88; }

/* lb-badge */
.lb-badge{
  display:inline-flex;
  align-items:center;
  padding:.1rem .4rem;
  border-radius:999px;
  font-size:.68rem;
  font-weight:700;
  text-transform:uppercase;
  letter-spacing:.04em;
}
.lb-badge--seller{ background:rgba(99,102,241,.2); color:#818cf8; }
.lb-badge--client{ background:rgba(16,185,129,.15); color:#10b981; }
.lb-badge--admin { background:rgba(245,158,11,.15); color:#f59e0b; }
.lb-badge--system{ background:rgba(107,114,128,.15); color:#9ca3af; }

/* lb-syswrap */
.lb-syswrap{
  width:100%;
  margin:.45rem 0 .7rem;
}
.lb-sys{
  display:block;
  width:100%;
  background:rgba(124,92,255,.10);
  border:1px dashed rgba(159,140,255,.38);
  border-radius:16px;
  padding:1rem 1.15rem;
  font-size:.95rem;
  font-weight:700;
  line-height:1.55;
  color:rgba(255,255,255,.92);
  text-align:left;
  white-space:normal;
  opacity:1;
}
.lb-sys-time{
  font-size:.72rem;
  opacity:.42;
  margin-top:.35rem;
  padding-left:.1rem;
  text-align:left;
}
[data-theme="light"] .lb-sys{
  background:rgba(109,92,255,.08);
  border-color:rgba(109,92,255,.28);
  color:rgba(17,24,39,.92);
}
[data-theme="light"] .lb-sys-time{
  color:rgba(0,0,0,.5);
  opacity:1;
}

/* chat empty */
.seller-account-view .lb-chat-empty{
  display:flex;
  flex-direction:column;
  align-items:center;
  justify-content:center;
  min-height:240px;
  opacity:.4;
  gap:.5rem;
  text-align:center;
}

/* =============================================
   GALLERY
============================================= */
.gallery-tile{
  position:relative;
  overflow:hidden;
  border-radius:.5rem;
  background:rgba(255,255,255,.02);
  cursor:grab;
}
.gallery-tile:active{ cursor:grabbing; }
.gallery-tile.is-main{ outline:2px solid rgba(99,102,241,.9); outline-offset:2px; }
.gallery-tile img{
  width:100%;
  height:130px;
  object-fit:cover;
  border-radius:inherit;
  display:block;
  transition:transform .3s;
}
.gallery-tile:hover img{ transform:scale(1.04); }
.gallery-badge{
  position:absolute;
  top:.4rem;
  left:.4rem;
  padding:.2rem .45rem;
  border-radius:999px;
  background:rgba(99,102,241,.9);
  color:#fff;
  font-size:.7rem;
  font-weight:700;
  z-index:2;
}
.gallery-remove{
  position:absolute;
  top:.4rem;
  right:.4rem;
  z-index:3;
  border:0;
  background:rgba(220,53,69,.9);
  color:#fff;
  width:28px;
  height:28px;
  border-radius:7px;
  display:inline-flex;
  align-items:center;
  justify-content:center;
  opacity:0;
  transform:translateY(-4px);
  transition:transform .15s,opacity .15s;
  pointer-events:none;
}
.gallery-tile:hover .gallery-remove{
  opacity:1;
  transform:translateY(0);
  pointer-events:auto;
}

/* =============================================
   EDIT FORM: toggles, dropzone
============================================= */
.toggle-group{display:flex;border-radius:8px;padding:5px;width:100%;position:relative}
.toggle-group input{display:none}
.toggle-label{flex:1;text-align:center;padding:10px;color:#bbb;font-weight:500;cursor:pointer;transition:.3s}
.toggle-label--disabled{opacity:.55;cursor:not-allowed!important}
input:checked+.toggle-label{background-color:#6366f1;color:#fff}
.account-upload-box{border:2px dashed rgba(255,255,255,.12);border-radius:12px;transition:all .2s;background:rgba(255,255,255,.02);cursor:pointer}
.account-upload-box:hover,.account-upload-box.dragover{border-color:#6366f1;background:rgba(99,102,241,.06)}
.ts-wrapper{min-height:42px!important}
.ts-wrapper.multi .ts-control{max-height:calc(2 * 2.2rem);overflow-y:auto;flex-wrap:wrap}
.ts-wrapper.multi .ts-control>div{background:#35383bff!important;color:#fff!important}
.ts-control .item{color:#fff}
.ts-wrapper.ts-dropup.dropdown-active .ts-dropdown{top:auto!important;bottom:calc(100% + 6px)!important;z-index:2000}
.js-validation-alert{border-radius:12px}
form.was-validated .form-control:invalid,form.was-validated .form-select:invalid{border-color:var(--bs-danger)!important}

/* image lightbox modal */
#lbImageModal .modal-dialog{ max-width:90vw; }
#lbImageModal img{ max-width:100%; max-height:80vh; border-radius:.5rem; }
</style>
<?= $this->end() ?>


<!-- ══ ACCOUNT VIEW — Modern Head ══ -->
<style>
/* Override card styles for view page */
.seller-account-view .card { background:var(--bs-card-bg)!important;border:var(--bs-card-border-width) solid var(--bs-card-border-color)!important;border-radius:22px!important;box-shadow:none!important; }
.seller-account-view .card::before { display:none!important; }

/* New head card */
.av-head {
  border-radius:22px;overflow:hidden;margin-bottom:20px;
  border:1px solid var(--bs-card-border-color);
  background:#25282a;
}
.av-head-body { padding:20px 22px;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;border-bottom:1px solid var(--bs-card-border-color); }
.av-head-left { display:flex;align-items:center;gap:14px; }
.av-rank-badge { width:56px;height:56px;border-radius:14px;background:rgba(109,92,255,.12);border:1px solid rgba(109,92,255,.22);display:flex;align-items:center;justify-content:center;flex-shrink:0; }
.av-rank-badge img { width:36px;height:36px;object-fit:contain;filter:drop-shadow(0 2px 6px rgba(0,0,0,.5)); }
.av-title { font-size:1.2rem;font-weight:950;color:rgba(255,255,255,.92);margin:0;line-height:1.2; }
.av-sub   { font-size:.82rem;color:rgba(255,255,255,.5);margin-top:4px;display:flex;align-items:center;gap:6px;flex-wrap:wrap; }

/* Status */
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
.av-btn-primary { display:inline-flex;align-items:center;gap:.4rem;padding:7px 16px;border-radius:11px;font-size:.83rem;font-weight:800;background:linear-gradient(135deg,#6d5cff,#b05cff);border:none;color:#fff;cursor:pointer;transition:opacity .15s,transform .12s; }
.av-btn-primary:hover { opacity:.88;transform:translateY(-1px); }
.av-btn-success { display:inline-flex;align-items:center;gap:.4rem;padding:7px 16px;border-radius:11px;font-size:.83rem;font-weight:800;background:rgba(74,222,128,.14);border:1px solid rgba(74,222,128,.25);color:#4ade80;cursor:pointer;transition:background .12s; }
.av-btn-success:hover { background:rgba(74,222,128,.22); }
.av-btn-warning { display:inline-flex;align-items:center;gap:.4rem;padding:7px 16px;border-radius:11px;font-size:.83rem;font-weight:800;background:rgba(251,191,36,.12);border:1px solid rgba(251,191,36,.22);color:#fbbf24;cursor:pointer;transition:background .12s; }
.av-btn-warning:hover { background:rgba(251,191,36,.20); }
.av-btn-ghost { display:inline-flex;align-items:center;gap:.4rem;padding:7px 14px;border-radius:11px;font-size:.83rem;font-weight:700;background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.09);color:rgba(255,255,255,.7);cursor:pointer;transition:background .12s;text-decoration:none; }
.av-btn-ghost:hover { background:rgba(255,255,255,.09);color:#fff; }

/* Gallery */
.av-gallery-grid { display:grid;grid-template-columns:repeat(auto-fill,minmax(180px,1fr));gap:10px; }
.av-gallery-tile { position:relative;border-radius:12px;overflow:hidden;background:#0d0f1a;cursor:pointer; }
.av-gallery-tile img { width:100%;height:160px;object-fit:cover;display:block;transition:transform .3s; }
.av-gallery-tile:hover img { transform:scale(1.04); }
.av-gallery-main-badge { position:absolute;top:8px;left:8px;padding:2px 8px;border-radius:99px;background:rgba(109,92,255,.9);color:#fff;font-size:.68rem;font-weight:800; }

/* Buyer card */
.av-buyer-card { display:flex;align-items:center;gap:14px;padding:16px;border-radius:16px;background:rgba(74,222,128,.06);border:1px solid rgba(74,222,128,.15); }
.av-buyer-avatar { width:44px;height:44px;border-radius:12px;background:rgba(74,222,128,.18);border:1px solid rgba(74,222,128,.25);display:flex;align-items:center;justify-content:center;font-size:1.1rem;font-weight:900;color:#4ade80;flex-shrink:0; }
.av-buyer-name  { font-weight:900;color:rgba(255,255,255,.9);font-size:.92rem; }
.av-buyer-email { font-size:.78rem;color:rgba(255,255,255,.45);margin-top:2px; }
.av-buyer-action { margin-left:auto;flex-shrink:0; }

/* Overview card items */
.av-ov-item { display:flex;align-items:center;justify-content:space-between;padding:10px 0;border-bottom:1px solid var(--bs-card-border-color); }
.av-ov-item:last-child { border-bottom:0; }
.av-ov-label { font-size:.8rem;color:rgba(255,255,255,.45);font-weight:700;display:flex;align-items:center;gap:.5rem; }
.av-ov-value { font-size:.88rem;font-weight:800;color:rgba(255,255,255,.88); }

/* Credential row */
.av-cred-row { display:flex;align-items:center;justify-content:space-between;gap:10px;padding:9px 0;border-bottom:1px solid var(--bs-card-border-color); }
.av-cred-row:last-child { border-bottom:0; }
.av-cred-label { font-size:.78rem;color:rgba(255,255,255,.45);font-weight:700;white-space:nowrap; }
.av-cred-val   { font-family:monospace;font-size:.82rem;font-weight:600;color:rgba(255,255,255,.85);max-width:160px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;cursor:pointer; }
.av-cred-val.empty { opacity:.3;font-style:italic; }
.av-cred-copy  { background:none;border:none;color:rgba(255,255,255,.25);cursor:pointer;padding:0 .2rem;transition:color .12s;line-height:1; }
.av-cred-copy:hover { color:#9f8cff; }

/* Chat card */
.av-chat-header { display:flex;align-items:center;justify-content:space-between;padding:14px 20px;border-bottom:1px solid var(--bs-card-border-color); }
.av-chat-title  { font-size:.95rem;font-weight:900;color:rgba(255,255,255,.9);display:flex;align-items:center;gap:.5rem; }

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
.av-seller-only-badge { display:inline-flex;align-items:center;padding:2px 8px;border-radius:99px;font-size:.62rem;font-weight:800;background:rgba(251,191,36,.10);border:1px solid rgba(251,191,36,.2);color:#fbbf24; }
.av-eye-btn { width:24px;height:24px;border-radius:6px;border:1px solid rgba(255,255,255,.09);background:rgba(255,255,255,.04);color:rgba(255,255,255,.35);font-size:.62rem;cursor:pointer;display:flex;align-items:center;justify-content:center;transition:background .12s,color .12s; }
.av-eye-btn:hover { background:rgba(109,92,255,.18);border-color:rgba(109,92,255,.3);color:#c4b5fd; }

/* Buyer row inside sidebar */
.av-buyer-row { display:flex;align-items:center;gap:12px;padding:14px 16px; }
.av-buyer-avi { width:38px;height:38px;border-radius:10px;background:rgba(74,222,128,.15);border:1px solid rgba(74,222,128,.25);display:flex;align-items:center;justify-content:center;font-size:.95rem;font-weight:900;color:#4ade80;flex-shrink:0; }
.av-buyer-info .av-buyer-name { font-size:.85rem;font-weight:900;color:rgba(255,255,255,.9); }
.av-buyer-info .av-buyer-sub  { font-size:.72rem;color:rgba(255,255,255,.35);margin-top:1px; }

/* Credentials list */
.av-creds-list { padding:4px 0 6px; }
.av-cred-item { display:flex;align-items:center;justify-content:space-between;gap:8px;padding:8px 16px;border-bottom:1px solid rgba(255,255,255,.04); }
.av-cred-item:last-child { border-bottom:0; }
.av-cred-left { display:flex;align-items:center;gap:7px;min-width:90px; }
.av-cred-ico  { font-size:.65rem;color:rgba(255,255,255,.22);width:12px;text-align:center;flex-shrink:0; }
.av-cred-lbl  { font-size:.72rem;font-weight:700;color:rgba(255,255,255,.38);white-space:nowrap; }
.av-cred-right { display:flex;align-items:center;gap:5px;min-width:0; }
.av-cred-val  { font-family:monospace;font-size:.77rem;font-weight:700;color:rgba(255,255,255,.82);max-width:110px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;cursor:pointer;transition:filter .2s; }
.av-copy-btn  { background:none;border:none;color:rgba(255,255,255,.2);cursor:pointer;padding:2px 3px;transition:color .12s;line-height:1;flex-shrink:0; }
.av-copy-btn:hover { color:#9f8cff; }
.av-copy-btn:disabled { opacity:.25;cursor:default; }
/* Hide credentials */
body.av-creds-hidden .av-sensitive { filter:blur(5px);user-select:none; }

/* Earnings summary bar */
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

/* Tags (champions/roles) */
.av-tag-section { padding:12px 16px; }
.av-tag-label { font-size:.65rem;font-weight:800;color:rgba(255,255,255,.3);text-transform:uppercase;letter-spacing:.07em;margin-bottom:7px; }
.av-tag-list  { display:flex;flex-wrap:wrap;gap:5px; }
.av-tag { display:inline-flex;align-items:center;padding:3px 9px;border-radius:99px;font-size:.7rem;font-weight:700;background:rgba(255,255,255,.05);border:1px solid rgba(255,255,255,.09);color:rgba(255,255,255,.6); }
.av-tag--role { background:rgba(109,92,255,.1);border-color:rgba(109,92,255,.25);color:#c4b5fd; }
.av-tag--more { background:rgba(255,255,255,.03);color:rgba(255,255,255,.3); }
</style>

<div class="account-page-wrap seller-account-view">

<!-- ── HEAD CARD ── -->
<div class="av-head mb-4">
  <div class="av-head-body">
    <div style="display:flex;align-items:center;gap:14px;flex:1;min-width:0;">
      <div style="width:52px;height:52px;border-radius:14px;background:rgba(109,92,255,.12);border:1px solid rgba(109,92,255,.22);display:flex;align-items:center;justify-content:center;flex-shrink:0;overflow:hidden;">
        <?php if ($isLol): ?>
          <img src="<?= util_rank_img('lol', 'mini', $account['current_rank']) ?>" style="width:30px;height:30px;object-fit:contain;filter:drop-shadow(0 1px 3px rgba(0,0,0,.5));" alt="">
        <?php else: ?>
          <img src="<?= htmlspecialchars($accountHeaderIcon, ENT_QUOTES, 'UTF-8') ?>" style="width:100%;height:100%;object-fit:cover;" alt="<?= htmlspecialchars($accountHeaderName, ENT_QUOTES, 'UTF-8') ?>" onerror="this.style.display='none';this.parentNode.innerHTML='<i class=&quot;fa-solid fa-gamepad&quot; style=&quot;font-size:1.25rem;color:#a5b4fc;&quot;></i>';">
        <?php endif; ?>
      </div>
      <div style="min-width:0;">
        <div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap;">
          <h1 style="font-size:1.25rem;font-weight:950;color:rgba(255,255,255,.92);margin:0;line-height:1.2;"><?= $page_title ?></h1>
          <?php if ($is_refunded): ?>
            <span class="av-status av-status--sold"><i class="fa-solid fa-rotate-left"></i> Refunded</span>
          <?php elseif ($sold === 1): ?>
            <span class="av-status av-status--sold"><i class="fa-solid fa-check"></i> Sold</span>
          <?php elseif ((int)($account['active'] ?? 1) === 0): ?>
            <span class="av-status av-status--unlisted"><i class="fa-solid fa-eye-slash"></i> Unlisted</span>
          <?php else: ?>
            <span class="av-status av-status--active"><i class="fa-solid fa-circle" style="font-size:.4rem;"></i> Active</span>
          <?php endif ?>
        </div>
        <div style="font-size:.8rem;color:rgba(255,255,255,.4);margin-top:4px;display:flex;align-items:center;gap:6px;">
          <span style="font-weight:800;"><?= htmlspecialchars($accountHeaderName, ENT_QUOTES, 'UTF-8') ?></span><span>·</span>
          <?php if ($isLol && !empty($account['server'])): ?><span style="text-transform:uppercase;font-weight:700;"><?= htmlspecialchars($account['server']) ?></span><span>·</span><?php endif ?>
          <span>#<?= $account_id ?></span>
          <span>·</span>
          <span><?= date('d.m.Y', strtotime($account['created_at'] ?? 'now')) ?></span>
        </div>
      </div>
    </div>

    <div class="av-actions">
      <?php if ($is_refunded): ?>
        <button type="button" class="av-btn-ghost" disabled>
          <i class="fa-duotone fa-rotate-left"></i> Refunded
        </button>
      <?php elseif ($sold === 1): ?>
        <button type="button" class="av-btn-ghost" disabled>
          <i class="fa-duotone fa-check"></i> Sold
        </button>
      <?php elseif ((int)($account['active'] ?? 1) === 0): ?>
        <button type="button" class="av-btn-success js-account-action"
                data-action="seller_mark_active" data-id="<?= $account_id ?>">
          <i class="fa-duotone fa-eye"></i> Re-list
        </button>
      <?php else: ?>
        <button type="button" class="av-btn-ghost js-account-action"
                data-action="seller_mark_unlist" data-id="<?= $account_id ?>">
          <i class="fa-duotone fa-eye-slash"></i> Unlist
        </button>
      <?php endif ?>
      <a href="<?= BASE_URL ?><?= $is_order_view ? '/seller-area/account-orders' : '/seller-area/accounts' ?>" class="av-btn-ghost">
        <i class="fa-duotone fa-arrow-left"></i> Back
      </a>
    </div>
  </div>

  <div class="av-meta-row">
    <span class="av-meta-pill"><img src="<?= htmlspecialchars($accountHeaderIcon, ENT_QUOTES, 'UTF-8') ?>" alt="" style="width:16px;height:16px;object-fit:cover;border-radius:4px;" onerror="this.remove();"> <strong><?= htmlspecialchars($accountHeaderName, ENT_QUOTES, 'UTF-8') ?></strong></span>
    <span class="av-meta-pill"><i class="fa-solid fa-euro-sign" style="color:rgba(255,255,255,.4);"></i> <strong>€<?= number_format($price_eur, 2) ?></strong> Price</span>
    <span class="av-meta-pill"><i class="fa-solid fa-sack-dollar" style="color:#4ade80;"></i> <strong style="color:#4ade80;">€<?= number_format($earnings, 2) ?></strong> Earnings</span>
    <?php if ($isLol): ?>
    <span class="av-meta-pill"><i class="fa-solid fa-trophy" style="color:rgba(255,255,255,.4);"></i> <strong><?= $rank_label ?><?= $div_label ? ' '.$div_label : '' ?></strong></span>
    <?php endif; ?>
    <?php if ($sold && !empty($buyer)): ?>
    <span class="av-meta-pill"><i class="fa-solid fa-user" style="color:#4ade80;"></i> <strong style="color:#4ade80;"><?= htmlspecialchars($buyer['username'] ?? '—') ?></strong></span>
    <?php endif ?>
  </div>
</div>


<?php /* ═════════════════════════════
   2-COLUMN LAYOUT
════════════════════════════════ */ ?>
<div class="row g-4 align-items-start">

  <?php /* ── LEFT col ── */ ?>
  <div class="col-12 col-lg-8">


    <?php /* ─ Chat card (top position when sold) ─ */ ?>
    <?php if ($sold && !empty($buyer)): ?>
    <div class="card order-chat-card mb-4">
      <div class="av-chat-header">
        <div class="av-chat-title">
          <i class="fa-duotone fa-comments" style="color:#9f8cff;"></i>
          Buyer Support Chat
        </div>
        <div class="av-buyer-chip-small" style="display:inline-flex;align-items:center;gap:.4rem;padding:3px 10px;border-radius:99px;background:rgba(74,222,128,.10);border:1px solid rgba(74,222,128,.20);color:#4ade80;font-size:.75rem;font-weight:700;">
          <span style="width:18px;height:18px;border-radius:50%;background:rgba(74,222,128,.2);display:inline-flex;align-items:center;justify-content:center;font-size:.6rem;font-weight:900;"><?= strtoupper(substr($buyer['username'] ?? 'B', 0, 1)) ?></span>
          <?= htmlspecialchars($buyer['username'] ?? '—') ?>
        </div>
      </div>
      <div class="card-body chat-bg" id="chat_messages"></div>
      <div class="card-footer">
        <form class="row gx-2 align-items-center" id="lbChatForm" action="<?= AJAX_URL ?>" method="POST" enctype="multipart/form-data">
          <input type="hidden" name="action"     value="seller_account_chat_send">
          <input type="hidden" name="account_id" value="<?= $account_id ?>">
          <div class="col">
            <input type="text" name="message" id="lbChatMessageInput" class="form-control" placeholder="Type your message">
          </div>
          <div class="col-auto d-flex align-items-center gap-2">
            <input type="file" class="d-none" id="lbChatFile" name="chat_image" accept=".png,.jpg,.jpeg,.gif,image/png,image/jpeg,image/gif">
            <button type="button" class="btn btn-sm btn-secondary btn-chat-icon" id="lbChatAttachBtn" aria-label="Attach image" title="Attach image">
              <i class="fa-duotone fa-paperclip"></i>
            </button>
            <button type="button" class="btn btn-sm btn-secondary lb-emoji-btn d-none d-md-inline-flex" id="lbEmojiBtn" aria-label="Emojis" title="Emojis">
              <i class="fa-regular fa-face-smile"></i>
            </button>
            <button type="submit" class="btn btn-sm btn-primary btn-chat-icon" id="lbChatSendBtn" aria-label="Send" title="Send">
              <span class="indicator-label"><i class="fa-duotone fa-paper-plane fs-5"></i></span>
              <span class="indicator-progress d-none"><span class="spinner-border spinner-border-sm align-middle"></span></span>
            </button>
          </div>
          <div class="col-12">
            <div class="lb-chat-error text-danger small mt-1 d-none" id="lbChatError"></div>
            <div class="lb-chat-attach-preview d-none mt-2" id="lbChatPreview">
              <div class="lb-chat-attach-preview__thumb">
                <img src="" alt="attachment preview" id="lbChatPreviewImg">
              </div>
              <div class="lb-chat-attach-preview__meta">
                <div class="lb-chat-attach-preview__title">Image ready to send</div>
                <div class="lb-chat-attach-preview__name" id="lbChatPreviewName"></div>
              </div>
              <button type="button" class="lb-chat-attach-preview__remove" id="lbChatRemoveBtn" aria-label="Remove" title="Remove">
                <i class="fa-duotone fa-xmark"></i>
              </button>
            </div>
            <div class="text-muted small mt-2">
              Tip: You can also paste a screenshot with <strong>Ctrl</strong> + <strong>V</strong>.
            </div>
          </div>
        </form>
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
      </div>
    </div>
    <?php endif ?>

    <?php /* ─ Gallery ─ */ ?>
    <?php if (!empty($images)): ?>
    <div class="card mb-4">
      <div class="card-header d-flex align-items-center justify-content-between" style="padding:14px 20px;border-bottom:1px solid var(--bs-card-border-color);">
        <h4 class="card-header-title mb-0"><i class="fa-duotone fa-images me-2" style="color:#9f8cff;"></i>Gallery <span style="font-size:.78rem;color:rgba(255,255,255,.4);font-weight:600;"><?= count($images) ?> image<?= count($images)!==1?'s':'' ?></span></h4>
      </div>
      <div class="card-body" style="padding:16px 20px;">
        <div class="av-gallery-grid">
          <?php foreach ($images as $i => $img): ?>
          <div class="av-gallery-tile" data-zoom="<?= htmlspecialchars($img) ?>">
            <?php if ($i === 0): ?><div class="av-gallery-main-badge">MAIN</div><?php endif ?>
            <img src="<?= htmlspecialchars($img) ?>" alt="" loading="lazy">
          </div>
          <?php endforeach ?>
        </div>
      </div>
    </div>
    <?php endif ?>

    <?php /* ─ Description ─ */ ?>
    <?php if (!empty($account['description'])): ?>
    <div class="card mb-4">
      <div class="card-header"><h4 class="card-header-title mb-0"><i class="fa-duotone fa-align-left me-2"></i>Description</h4></div>
      <div class="card-body"><p class="mb-0"><?= nl2br(htmlspecialchars($account['description'])) ?></p></div>
    </div>
    <?php endif ?>

    <?php /* ─ Edit form (only when not sold) ─ */ ?>
    <?php if (!$sold): ?>
    <div class="card mb-4" id="editCard">
      <div class="card-header d-flex align-items-center justify-content-between">
        <h4 class="card-header-title mb-0"><i class="fa-duotone fa-pen me-2"></i>Edit Account</h4>
        <button type="button" class="btn btn-ghost-secondary btn-sm" id="toggleEditBtn">
          <i class="fa-duotone fa-chevron-down me-1" id="toggleEditIcon"></i> Edit
        </button>
      </div>
      <div id="editFormWrap" style="display:none;">
      <form action="<?= AJAX_URL ?>" method="POST" enctype="multipart/form-data"
            class="form ajax-form js-pretty-validate" novalidate>
        <input type="hidden" name="action" value="seller_update_account">
        <input type="hidden" name="id"     value="<?= $account_id ?>">
        <div class="alert alert-danger js-validation-alert d-none m-3" role="alert"></div>

        <div class="card-body">
          <h6 class="text-muted text-uppercase fw-bold small mb-3">Listing Info</h6>
          <div class="row g-3 mb-4">
            <div class="col-md-6">
              <label class="form-label">Account Title</label>
              <input type="text" class="form-control" name="title" placeholder="EUW – Platinum I"
                     value="<?= htmlspecialchars($account['title'] ?? '') ?>" required>
            </div>
            <div class="col-md-6">
              <label class="form-label">Price</label>
              <div class="input-group">
                <span class="input-group-text">€</span>
                <input type="text" class="form-control js-price-input" name="price" placeholder="0.00"
                       value="<?= htmlspecialchars(number_format($price_eur, 2, '.', '')) ?>" required>
                <span class="input-group-text">EUR</span>
              </div>
              <div class="form-text mt-1">
                Your Earnings: <strong>€<span class="js-earnings-preview"><?= number_format($earnings, 2) ?></span></strong>
                <span class="text-muted">(after <?= $effective_fee ?>% fee)</span>
              </div>
            </div>
            <div class="col-12">
              <label class="form-label">Description</label>
              <textarea class="form-control" rows="4" name="description" required><?= htmlspecialchars($account['description'] ?? '') ?></textarea>
            </div>
            <div class="col-12">
              <label class="form-label">Image Gallery</label>
              <div id="galleryDropzone" class="account-upload-box text-center p-4">
                <i class="fa-duotone fa-images fa-2x text-primary mb-2 d-block"></i>
                <h6 class="mb-1">Upload Account Images</h6>
                <p class="text-muted small mb-3">Click to select, drag &amp; drop, or paste with <strong>Ctrl + V</strong>.</p>
                <label class="btn btn-primary btn-sm" for="galleryUpload">Select Images</label>
                <input class="visually-hidden" type="file" id="galleryUpload" name="images[]" multiple accept="image/*">
              </div>
              <small class="d-block mt-2 text-muted">(PNG, JPEG, WEBP, GIF | Max 1MB Each)</small>
              <input type="hidden" name="images_order" id="imagesOrderInput"
                     value='<?= htmlspecialchars(json_encode($images), ENT_QUOTES) ?>'>
              <div id="previewGallery" class="row mt-3 g-2"></div>
            </div>
          </div>

          <?php if (!$isGeneric): ?>
          <hr class="my-4">
          <h6 class="text-muted text-uppercase fw-bold small mb-3">Game Data</h6>
          <div class="row g-3 mb-4">
            <div class="col-12">
              <label class="form-label">Current Rank & Division</label>
              <div class="row g-2">
                <div class="col-12 current-rank">
                  <select class="form-select" name="current_rank" required><?= util_load_lol_tier_select(0, 10, $account['current_rank']) ?></select>
                </div>
                <div class="col-3 current-division d-none">
                  <select class="form-select" name="current_division"><?= util_load_lol_division_select($account['current_division']) ?></select>
                </div>
                <div class="col-3 current-lp d-none">
                  <input type="text" class="form-control" name="current_lp" placeholder="LP" value="<?= htmlspecialchars($account['current_lp'] ?? '') ?>">
                </div>
              </div>
            </div>
            <div class="col-md-6">
              <label class="form-label">Flex Rank</label>
              <div class="row g-2">
                <div class="col-12 flex-rank">
                  <select class="form-select" name="flex_rank" required><?= util_load_lol_tier_select(0, 10, $account['flex_rank']) ?></select>
                </div>
                <div class="col-5 flex-division d-none">
                  <select class="form-select" name="flex_division"><?= util_load_lol_division_select($account['flex_division']) ?></select>
                </div>
                <div class="col-5 flex-lp d-none">
                  <input type="text" class="form-control" name="flex_lp" placeholder="LP" value="<?= htmlspecialchars($account['flex_lp'] ?? '') ?>">
                </div>
              </div>
            </div>
            <div class="col-md-6">
              <label class="form-label">Previous Rank</label>
              <div class="row g-2">
                <div class="col-12 previous-rank">
                  <select class="form-select" name="previous_rank" required><?= util_load_lol_tier_select(0, 10, $account['previous_rank']) ?></select>
                </div>
                <div class="col-5 previous-division d-none">
                  <select class="form-select" name="previous_division"><?= util_load_lol_division_select($account['previous_division']) ?></select>
                </div>
                <div class="col-5 previous-lp d-none">
                  <input type="text" class="form-control" name="previous_lp" placeholder="LP" value="<?= htmlspecialchars($account['previous_lp'] ?? '') ?>">
                </div>
              </div>
            </div>
            <div class="col-md-6">
              <label class="form-label">Server</label>
              <select class="form-select" name="server" required><?= util_load_server_select($account['server']) ?></select>
            </div>
            <div class="col-md-6">
              <label class="form-label">Level Up Method</label>
              <select class="form-select" name="level_up_method" required>
                <option value="by_hand" <?= ($account['level_up_method'] ?? '') === 'by_hand' ? 'selected' : '' ?>>By Hand</option>
                <option value="botted"  <?= ($account['level_up_method'] ?? '') === 'botted'  ? 'selected' : '' ?>>Botted</option>
              </select>
            </div>
            <div class="col-md-4">
              <label class="form-label">Level</label>
              <input type="number" class="form-control" name="level" value="<?= htmlspecialchars($account['level'] ?? '') ?>">
            </div>
            <div class="col-md-4">
              <label class="form-label">Blue Essence</label>
              <input type="number" class="form-control" name="blue_essence" value="<?= htmlspecialchars($account['blue_essence'] ?? '') ?>">
            </div>
            <div class="col-md-4">
              <label class="form-label">Riot Points</label>
              <input type="number" class="form-control" name="riot_points" value="<?= htmlspecialchars($account['riot_points'] ?? '') ?>">
            </div>
            <div class="col-md-6">
              <label class="form-label">Win Rate (%)</label>
              <input type="number" class="form-control" name="winrate_percent" value="<?= htmlspecialchars($account['winrate_percent'] ?? '') ?>">
            </div>
            <div class="col-12">
              <label class="form-label">Champions</label>
              <select class="form-select" name="champions[]" id="champions" data-placeholder="Select Champions" multiple>
                <?= util_load_champions_select(explode('|', $account['champions'] ?? '')) ?>
              </select>
            </div>
            <div class="col-12">
              <label class="form-label">Skins</label>
              <select class="form-select" name="skins[]" id="skins" data-placeholder="Select Skins" multiple>
                <?php try { echo util_get_lol_skins(explode('|', $account['skins'] ?? '')); } catch(Throwable $e) { /* skins data unavailable */ } ?>
              </select>
            </div>
            <div class="col-12">
              <label class="form-label">Roles</label>
              <select class="form-select" name="roles[]" id="roles" data-placeholder="Select Roles" multiple>
                <?= util_load_roles_select(explode('|', $account['roles'] ?? '')) ?>
              </select>
            </div>
          </div>

          <?php endif; /* !$isGeneric : LoL Game Data block */ ?>

          <?php if ($isGeneric): ?>
          <hr class="my-4">
          <h6 class="text-muted text-uppercase fw-bold small mb-3">Game Data</h6>
          <input type="hidden" name="game" value="<?= htmlspecialchars($accountGameSlug) ?>">
          <input type="hidden" name="game_data" value="<?= htmlspecialchars(json_encode($game_data), ENT_QUOTES) ?>">
          <div class="row g-3 mb-4">
            <div class="col-md-4">
              <label class="form-label">Level</label>
              <input type="number" class="form-control" name="level" value="<?= htmlspecialchars($account['level'] ?? '') ?>">
            </div>
            <div class="col-md-4">
              <label class="form-label">Server / Region</label>
              <input type="text" class="form-control" name="server" value="<?= htmlspecialchars($account['server'] ?? '') ?>" placeholder="e.g. EU, NA, Global">
            </div>
            <?php
              $genFields = function_exists('util_account_schema_fields') ? util_account_schema_fields($accountGameSlug, 'show_on_upload') : [];
              foreach ($genFields as $gf):
                  $gfKey = (string)($gf['key'] ?? '');
                  if ($gfKey === '') continue;
                  $gfLabel = htmlspecialchars((string)($gf['label'] ?? ucwords(str_replace('_', ' ', $gfKey))), ENT_QUOTES, 'UTF-8');
                  $gfType  = (string)($gf['type'] ?? 'text');
                  $gfName  = function_exists('util_account_schema_input_name') ? util_account_schema_input_name($gfKey, $gfType === 'multiselect') : ('schema_' . $gfKey . ($gfType === 'multiselect' ? '[]' : ''));
                  $gfCol   = (string)($gf['col'] ?? ($gfType === 'multiselect' ? 'col-12' : 'col-md-4'));
                  $gfCur   = $game_data[$gfKey] ?? '';
                  $gfCurArr = is_array($gfCur) ? array_map('strval', $gfCur) : [(string)$gfCur];
            ?>
            <div class="<?= htmlspecialchars($gfCol, ENT_QUOTES) ?>">
              <label class="form-label"><?= $gfLabel ?></label>
              <?php if ($gfType === 'select' || $gfType === 'multiselect'): ?>
                <select class="form-select" name="<?= htmlspecialchars($gfName, ENT_QUOTES) ?>"<?= $gfType === 'multiselect' ? ' multiple' : '' ?> data-placeholder="Select <?= $gfLabel ?>">
                  <?php if ($gfType !== 'multiselect'): ?><option value="">Select <?= $gfLabel ?></option><?php endif; ?>
                  <?php foreach (($gf['options'] ?? []) as $opt):
                        $optVal = is_array($opt) ? (string)($opt['value'] ?? $opt['label'] ?? '') : (string)$opt;
                        $optLab = is_array($opt) ? (string)($opt['label'] ?? $optVal) : (string)$opt;
                        $sel = in_array($optVal, $gfCurArr, true) ? ' selected' : ''; ?>
                    <option value="<?= htmlspecialchars($optVal, ENT_QUOTES) ?>"<?= $sel ?>><?= htmlspecialchars($optLab, ENT_QUOTES) ?></option>
                  <?php endforeach; ?>
                </select>
              <?php elseif ($gfType === 'checkbox'): ?>
                <div class="form-check form-switch mt-2">
                  <input type="hidden" name="<?= htmlspecialchars($gfName, ENT_QUOTES) ?>" value="0">
                  <input class="form-check-input" type="checkbox" role="switch" name="<?= htmlspecialchars($gfName, ENT_QUOTES) ?>" value="1"<?= !empty($gfCur) ? ' checked' : '' ?>>
                </div>
              <?php elseif ($gfType === 'textarea'): ?>
                <textarea class="form-control" name="<?= htmlspecialchars($gfName, ENT_QUOTES) ?>"><?= htmlspecialchars(is_array($gfCur) ? implode(', ', $gfCur) : (string)$gfCur) ?></textarea>
              <?php else: ?>
                <input type="<?= $gfType === 'number' ? 'number' : 'text' ?>" class="form-control" name="<?= htmlspecialchars($gfName, ENT_QUOTES) ?>" value="<?= htmlspecialchars(is_array($gfCur) ? implode(', ', $gfCur) : (string)$gfCur, ENT_QUOTES) ?>">
              <?php endif; ?>
            </div>
            <?php endforeach; ?>
          </div>
          <?php endif; /* $isGeneric */ ?>

          <hr class="my-4">
          <h6 class="text-muted text-uppercase fw-bold small mb-3">Credentials</h6>
          <div class="row g-3">
            <div class="col-12">
              <div class="toggle-group bg-light border rounded p-1">
                <input type="radio" id="instant" name="delivery_type" value="instant"
                       <?= ($account['delivery_type'] ?? 'instant') === 'instant' ? 'checked' : '' ?>>
                <label for="instant" class="toggle-label rounded"><i class="fa-duotone fa-bolt me-2"></i>Instant Delivery</label>
                <input type="radio" id="manual" name="delivery_type" value="manual" disabled>
                <label for="manual" class="toggle-label rounded toggle-label--disabled" title="Manual Delivery currently disabled">
                  <i class="fa-duotone fa-truck me-2"></i>Manual Delivery
                </label>
              </div>
            </div>
            <div class="col-md-6">
              <label class="form-label">Login</label>
              <input type="text" class="form-control" name="login" value="<?= htmlspecialchars($account['login'] ?? '') ?>" required>
            </div>
            <div class="col-md-6">
              <label class="form-label">Password</label>
              <input type="text" class="form-control" name="password" value="<?= htmlspecialchars($account['password'] ?? '') ?>" required>
            </div>
            <div class="col-md-6">
              <label class="form-label">Email Verification</label>
              <select class="form-select" id="edit_email_verified" name="email_verified" onchange="toggleEditEmailFields(this.value)">
                <option value="verified"   <?= ($account['email'] ?? '') !== 'unverified' && !empty($account['email']) ? 'selected' : '' ?>>Verified</option>
                <option value="unverified" <?= ($account['email'] ?? '') === 'unverified' || empty($account['email']) ? 'selected' : '' ?>>Unverified</option>
              </select>
            </div>
            <div class="col-md-6" id="edit_email_wrap"
                 <?= ($account['email'] ?? '') === 'unverified' || empty($account['email']) ? 'style="display:none"' : '' ?>>
              <label class="form-label">Account Email</label>
              <input type="text" class="form-control" name="email" id="edit_email"
                     value="<?= htmlspecialchars(($account['email'] ?? '') !== 'unverified' ? ($account['email'] ?? '') : '') ?>" required>
            </div>
            <div class="col-md-6" id="edit_email_password_wrap"
                 <?= ($account['email'] ?? '') === 'unverified' || empty($account['email']) ? 'style="display:none"' : '' ?>>
              <label class="form-label">Email Password</label>
              <input type="text" class="form-control" name="email_password" id="edit_email_password"
                     value="<?= htmlspecialchars($account['email_password'] ?? '') ?>" required>
            </div>
            <div class="col-md-6">
              <label class="form-label">In-Game Name</label>
              <input type="text" class="form-control" name="in_game_name" value="<?= htmlspecialchars($account['in_game_name'] ?? '') ?>" required>
            </div>
            <div class="col-md-6">
              <label class="form-label">Has 2FA</label>
              <div class="form-check form-switch mt-2">
                <input type="hidden" name="has_2fa" value="0">
                <input class="form-check-input" type="checkbox" role="switch" id="has_2fa" name="has_2fa" value="1"
                       <?= !empty($account['2fa']) ? 'checked' : '' ?>>
                <label class="form-check-label" for="has_2fa">Enable</label>
              </div>
            </div>
            <div class="col-12">
              <label class="form-label">Delivery Instructions</label>
              <textarea class="form-control" rows="3" name="delivery_instructions"><?= htmlspecialchars($account['delivery_instructions'] ?? '') ?></textarea>
            </div>
          </div>
        </div>

        <div class="card-footer d-flex justify-content-end gap-2">
          <button type="button" class="btn btn-ghost-secondary" id="cancelEditBtn">Cancel</button>
          <button type="submit" class="btn btn-primary">
            <i class="fa-duotone fa-floppy-disk me-1"></i> Save Changes
          </button>
        </div>
      </form>
      </div><!-- /editFormWrap -->
    </div>
    <?php endif /* !$sold */ ?>

    <?php /* ─ Chat card (bottom position when not sold) ─ */ ?>
    <?php if (!$sold || empty($buyer)): ?>
    <div class="card order-chat-card mb-4">
      <div class="av-chat-header">
        <div class="av-chat-title">
          <i class="fa-duotone fa-comments" style="color:#9f8cff;"></i>
          Buyer Support Chat
        </div>
      </div>
      <?php if (!$sold): ?>
      <div class="card-body">
        <div class="lb-chat-empty">
          <i class="fa-duotone fa-store fa-2x mb-2"></i>
          <h6 class="mb-1">Account not sold yet</h6>
          <p class="small mb-0">Once a buyer purchases this account, you can chat with them here.</p>
        </div>
      </div>
      <?php else: ?>
      <div class="card-body">
        <div class="lb-chat-empty">
          <i class="fa-duotone fa-user-slash fa-2x mb-2"></i>
          <h6 class="mb-1">Buyer info unavailable</h6>
          <p class="small mb-0">No buyer linked to this account.</p>
        </div>
      </div>
      <?php endif ?>
    </div>
    <?php endif ?>
  </div>
  <?php /* ── LEFT col end ── */ ?>


  <?php /* ── RIGHT col (sidebar) ── */ ?>
  <div class="col-12 col-lg-4">

    <?php /* ─ Buyer / Status card ─ */ ?>
    <?php if ($sold && !empty($buyer)): ?>
    <div class="av-sidebar-card av-sidebar-card--buyer mb-3">
      <div class="av-sc-header">
        <span class="av-sc-icon" style="background:rgba(74,222,128,.12);border-color:rgba(74,222,128,.22);"><i class="fa-solid fa-user-check" style="color:#4ade80;"></i></span>
        <span class="av-sc-title">Buyer</span>
      </div>
      <div class="av-buyer-row">
        <div class="av-buyer-avi"><?= strtoupper(substr($buyer['username'] ?? 'U', 0, 1)) ?></div>
        <div class="av-buyer-info">
          <div class="av-buyer-name"><?= htmlspecialchars($buyer['username'] ?? '—') ?></div>
          <div class="av-buyer-sub">Purchased this account</div>
        </div>
        <a href="#lbChatForm" class="av-btn-success"
           onclick="document.getElementById('lbChatForm')?.scrollIntoView({behavior:'smooth'}); return false;"
           style="font-size:.75rem;padding:5px 12px;margin-left:auto;flex-shrink:0;">
          <i class="fa-duotone fa-comments"></i> Chat
        </a>
      </div>
    </div>
    <?php elseif (!$sold): ?>
    <div class="av-sidebar-card mb-3" style="text-align:center;padding:18px 20px;">
      <div style="width:40px;height:40px;border-radius:12px;background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.08);display:flex;align-items:center;justify-content:center;margin:0 auto 8px;font-size:1.1rem;color:rgba(255,255,255,.2);">
        <i class="fa-duotone fa-store"></i>
      </div>
      <div style="font-size:.8rem;color:rgba(255,255,255,.38);font-weight:700;">Listed — not yet sold</div>
    </div>
    <?php endif ?>

    <?php /* ─ Credentials card ─ */ ?>
    <div class="av-sidebar-card mb-3">
      <div class="av-sc-header">
        <span class="av-sc-icon" style="background:rgba(251,191,36,.10);border-color:rgba(251,191,36,.2);"><i class="fa-solid fa-key" style="color:#fbbf24;font-size:.72rem;"></i></span>
        <span class="av-sc-title">Credentials</span>
        <span class="av-seller-only-badge">Seller Only</span>
        <button class="av-eye-btn" id="avCredsEye" title="Show/hide" onclick="(function(btn){var b=document.body.classList.toggle('av-creds-hidden');btn.querySelector('i').className=b?'fa-solid fa-eye':'fa-solid fa-eye-slash';})(this)">
          <i class="fa-solid fa-eye-slash"></i>
        </button>
      </div>
      <div class="av-creds-list">
        <?php
        $creds = [
            'in_game_name'   => ['In-Game Name', 'fa-solid fa-gamepad'],
            'login'          => ['Login',         'fa-solid fa-user'],
            'password'       => ['Password',      'fa-solid fa-key'],
            'email'          => ['Email',         'fa-solid fa-envelope'],
            'email_password' => ['Email PW',      'fa-solid fa-lock'],
        ];
        foreach ($creds as $field => [$label, $icon]):
            $val  = $account[$field] ?? '';
            $safe = htmlspecialchars($val, ENT_QUOTES, 'UTF-8');
            if ($val === 'unverified') continue;
        ?>
        <div class="av-cred-item">
          <div class="av-cred-left">
            <i class="<?= $icon ?> av-cred-ico"></i>
            <span class="av-cred-lbl"><?= $label ?></span>
          </div>
          <div class="av-cred-right">
            <span class="av-cred-val av-sensitive js-copyable" data-copy="<?= $safe ?>" title="Click to copy"><?= $val !== '' ? $safe : '—' ?></span>
            <button class="av-copy-btn js-copy-cred" data-copy="<?= $safe ?>" <?= $val !== '' ? '' : 'disabled' ?>>
              <i class="fa-duotone fa-copy"></i>
            </button>
          </div>
        </div>
        <?php endforeach ?>
        <?php if (!empty($account['2fa'])): ?>
        <div class="av-cred-item">
          <div class="av-cred-left"><i class="fa-solid fa-shield-halved av-cred-ico"></i><span class="av-cred-lbl">2FA</span></div>
          <div class="av-cred-right"><span class="badge" style="background:rgba(251,191,36,.15);color:#fbbf24;font-size:.7rem;">Enabled</span></div>
        </div>
        <?php endif ?>
      </div>
    </div>

    <?php /* ─ Overview card ─ */ ?>
    <div class="av-sidebar-card mb-3">
      <div class="av-sc-header">
        <span class="av-sc-icon" style="background:rgba(109,92,255,.12);border-color:rgba(109,92,255,.22);"><i class="fa-solid fa-chart-bar" style="color:#c4b5fd;font-size:.72rem;"></i></span>
        <span class="av-sc-title">Overview</span>
      </div>

      <!-- Earnings summary -->
      <div class="av-ov-earnings">
        <div class="av-ov-earn-item">
          <div class="av-ov-earn-label">Listed Price</div>
          <div class="av-ov-earn-val">€<?= number_format($price_eur, 2) ?></div>
        </div>
        <div class="av-ov-earn-sep">−</div>
        <div class="av-ov-earn-item">
          <div class="av-ov-earn-label">Fee</div>
          <div class="av-ov-earn-val" style="color:#fb7185;">−<?= $effective_fee ?>%</div>
        </div>
        <div class="av-ov-earn-sep">=</div>
        <div class="av-ov-earn-item">
          <div class="av-ov-earn-label">You Earn</div>
          <div class="av-ov-earn-val" style="color:#4ade80;font-size:1rem;">€<?= number_format($earnings, 2) ?></div>
        </div>
      </div>

      <!-- Stat grid -->
      <div class="av-stat-grid">
        <?php
        $flex_label = $rank_labels[(int)($account['flex_rank'] ?? 0)] ?? 'Unranked';
        $_flex_idx  = (int)($account['flex_rank'] ?? 0);
        $flex_div   = ($_flex_idx === 0 || $_flex_idx >= 8) ? '' : ($div_labels[(int)($account['flex_division'] ?? 0)] ?? '');

        if ($isVal) {
          $val_rank_id = (int)($account['rank'] ?? ($game_data['val_rank'] ?? 0));
          $val_rank_label = function_exists('util_get_val_rank') ? util_get_val_rank($val_rank_id) : (($val_rank_id > 0) ? ('Rank '.$val_rank_id) : 'Unranked');
          $val_peak_id = (int)($game_data['val_peak_rank'] ?? 0);
          $val_peak_label = $val_peak_id > 0
            ? (function_exists('util_get_val_rank') ? util_get_val_rank($val_peak_id) : ('Rank '.$val_peak_id))
            : '—';
          $val_platform = (string)($game_data['val_platform'] ?? $game_data['platform'] ?? '—');
          $val_vp = (int)($game_data['val_points'] ?? $game_data['valorant_points'] ?? $game_data['vp'] ?? 0);
          $val_radianite = (int)($game_data['val_radianite'] ?? $game_data['radianite_points'] ?? 0);
          $val_winrate = $game_data['val_winrate'] ?? $game_data['winrate_percent'] ?? null;
          $val_skins = (int)($game_data['val_weapon_skins'] ?? $game_data['weapon_skins'] ?? 0);
          $stats = [
            ['fa-solid fa-crosshairs', 'Rank',         $val_rank_label, null],
            ['fa-solid fa-arrow-trend-up', 'Peak Rank', $val_peak_label, null],
            ['fa-solid fa-globe',      'Server',       strtoupper((string)($account['server'] ?? '—')), null],
            ['fa-solid fa-desktop',    'Platform',     $val_platform !== '' ? strtoupper($val_platform) : '—', null],
            ['fa-solid fa-arrow-up',   'Level',        $account['level'] ?? '—', null],
            ['fa-solid fa-coins',      'VP',           number_format($val_vp), '#a78bfa'],
            ['fa-solid fa-burst',      'Radianite',    number_format($val_radianite), '#60a5fa'],
            ['fa-solid fa-wand-magic-sparkles', 'Skins', (string)$val_skins, null],
            ['fa-solid fa-chart-line', 'Win Rate',     ($val_winrate !== null && $val_winrate !== '' ? (int)$val_winrate.'%' : '—'), null],
            ['fa-solid fa-bolt',       'Delivery',     ucfirst($account['delivery_type'] ?? '—'), '#fbbf24'],
          ];
        } elseif ($isGeneric) {
          // Generic game, keep the view dynamic for Roblox and future games.
          $stats = [
              ['fa-solid fa-gamepad', 'Game', $accountGameName, '#c4b5fd'],
          ];
          if ($accountExperienceName !== '') {
              $stats[] = ['fa-solid fa-layer-group', 'Experience', $accountExperienceName, null];
          }
          if (!empty($game_data)) {
              foreach ($game_data as $_gk => $_gv) {
                  if (in_array($_gk, ['games','experience_game','experience'], true)) continue;
                  if (is_scalar($_gv) && $_gv !== '' && $_gv !== null) {
                      $stats[] = ['fa-solid fa-circle-info', ucwords(str_replace('_', ' ', $_gk)), htmlspecialchars((string)$_gv), null];
                  }
              }
          }
          if (!empty($account['server'])) {
              $stats[] = ['fa-solid fa-globe', 'Server', strtoupper($account['server']), null];
          }
          if (!empty($account['level'])) {
              $stats[] = ['fa-solid fa-arrow-up', 'Level', (int)$account['level'], null];
          }
          if (!empty($account['rank_label'])) {
              $stats[] = ['fa-solid fa-trophy', 'Rank', $account['rank_label'], '#c4b5fd'];
          }
        } else {
          $stats = [
            ['fa-solid fa-trophy',     'Solo Rank',  $rank_label.($div_label?' '.$div_label:'').(!empty($account['current_lp'])?' · '.(int)$account['current_lp'].'LP':''), null],
            ['fa-solid fa-shield',     'Flex Rank',  $flex_label.($flex_div?' '.$flex_div:''), null],
            ['fa-solid fa-globe',      'Server',     strtoupper($account['server'] ?? '—'), null],
            ['fa-solid fa-arrow-up',   'Level',      $account['level'] ?? '—', null],
            ['fa-solid fa-gem',        'Blue Essence', number_format((int)($account['blue_essence']??0)), '#60a5fa'],
            ['fa-solid fa-coins',      'RP',         number_format((int)($account['riot_points']??0)), '#a78bfa'],
            ['fa-solid fa-chart-line', 'Win Rate',   (!empty($account['winrate_percent'])?(int)$account['winrate_percent'].'%':'—'), null],
            ['fa-solid fa-bolt',       'Delivery',   ucfirst($account['delivery_type']??'—'), '#fbbf24'],
          ];
        }
        foreach ($stats as [$ico, $lbl, $val, $clr]):
        ?>
        <div class="av-stat-item">
          <i class="<?= $ico ?> av-stat-ico" <?= $clr ? 'style="color:'.$clr.';"' : '' ?>></i>
          <div class="av-stat-body">
            <div class="av-stat-lbl"><?= $lbl ?></div>
            <div class="av-stat-val"><?= htmlspecialchars((string)$val) ?></div>
          </div>
        </div>
        <?php endforeach ?>
      </div>
    </div>

    <?php /* ─ Champions / Roles ─ */ ?>
    <?php
    $showContentCard = $isVal || $isLol
        ? (!empty($valAgentsList) || $valAgentsDisplayCount > 0)
        : (!empty($champs) || $champsDisplayCount > 0 || !empty($roles));
    ?>
    <?php if ($showContentCard): ?>
    <div class="av-sidebar-card mb-3">
      <div class="av-sc-header">
        <span class="av-sc-icon" style="background:rgba(109,92,255,.12);border-color:rgba(109,92,255,.22);">
          <i class="fa-solid <?= $isVal ? 'fa-crosshairs' : 'fa-gamepad' ?>" style="color:#c4b5fd;font-size:.72rem;"></i>
        </span>
        <span class="av-sc-title"><?= $isVal ? 'Agents' : 'Champions & Roles' ?></span>
      </div>
      <?php if ($isVal): ?>
        <?php if (!empty($valAgentsList)): ?>
        <div class="av-tag-section">
          <div class="av-tag-label">Agents (<?= $valAgentsDisplayCount ?>)</div>
          <div class="av-tag-list">
            <?php foreach (array_slice($valAgentsList, 0, 20) as $agent): ?>
              <span class="av-tag"><?= htmlspecialchars(trim($agent)) ?></span>
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
              <span class="av-tag"><?= htmlspecialchars(trim($c)) ?></span>
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
              <span class="av-tag av-tag--role"><?= htmlspecialchars(trim($r)) ?></span>
            <?php endforeach ?>
          </div>
        </div>
        <?php endif ?>
      <?php endif ?>
    </div>
    <?php endif ?>

  </div>
  <?php /* ── RIGHT col end ── */ ?>

</div><!-- /row -->

</div><!-- /account-page-wrap -->


<?php /* ─ Image lightbox ─ */ ?>
<div class="modal fade" id="lbImageModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content" style="background:rgba(0,0,0,.85);border:none;">
      <div class="modal-body text-center p-2">
        <img src="" id="lbImageModalImg" alt="" style="max-width:100%;max-height:80vh;border-radius:.5rem;">
      </div>
      <div class="modal-footer justify-content-center py-2 border-0">
        <button type="button" class="btn btn-sm btn-outline-light" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>


<?= $this->start('scripts') ?>
<script src="<?= ASSET_URL ?>/origin/dash/vendor/tom-select/dist/cjs/tom-select.complete.js"></script>
<script>
(function () {
  'use strict';

  /* ============================================================
     CONFIG
  ============================================================ */
  const AJAX_URL   = '<?= AJAX_URL ?>';
  const ACCOUNT_ID = <?= $account_id ?>;
  const SELLER_ID  = <?= (int)($seller_data['id'] ?? 0) ?>;
  const IS_SOLD    = <?= $sold ? 'true' : 'false' ?>;

  const SELLER_NAME = <?= json_encode(htmlspecialchars($seller_data['username'] ?? 'You', ENT_QUOTES)) ?>;
  const BUYER_NAME  = <?= json_encode(htmlspecialchars($buyer['username'] ?? 'Buyer', ENT_QUOTES)) ?>;

  <?php if ($sold && !empty($buyer)): ?>
  const BUYER_AVATAR = <?= json_encode((string)(($buyer['icon'] ?? '') ?: (ICON_URL . '/8515d2c8c74a3f9bae054026f6549d91.png'))) ?>;
  const SELLER_AVATAR = <?= json_encode((string)(($seller_data['icon'] ?? '') ?: (ICON_URL . '/03ce541a1f4bf8b06c924439ffcc8173.png'))) ?>;
  <?php endif ?>

  /* ============================================================
     COPY CREDENTIALS
  ============================================================ */
  // Start with credentials hidden
  document.body.classList.add('av-creds-hidden');
  const avEyeBtn = document.getElementById('avCredsEye');
  if (avEyeBtn) avEyeBtn.querySelector('i').className = 'fa-solid fa-eye';

  document.querySelectorAll('.js-copy-cred, .js-copyable').forEach(el => {
    el.addEventListener('click', () => {
      const val = el.getAttribute('data-copy') || '';
      if (!val) return;
      navigator.clipboard.writeText(val).then(() => {
        const i = el.querySelector('i');
        if (i) { i.className = 'fa-solid fa-check'; setTimeout(() => i.className = 'fa-duotone fa-copy', 1500); }
      }).catch(() => {});
    });
  });

  /* ============================================================
     MARK SOLD / UNSOLD
  ============================================================ */
  document.querySelectorAll('.js-account-action').forEach(btn => {
    btn.addEventListener('click', () => {
      const action = btn.getAttribute('data-action');
      const id     = btn.getAttribute('data-id');
      btn.disabled = true;
      $.post(AJAX_URL, { action, id }, resp => {
        let d = resp;
        try { if (typeof resp === 'string') d = JSON.parse(resp); } catch (e) {}
        if (d && d.refreshPage) window.location.reload();
        else btn.disabled = false;
      });
    });
  });

  /* ============================================================
     TOGGLE EDIT FORM
  ============================================================ */
  const toggleBtn  = document.getElementById('toggleEditBtn');
  const cancelBtn  = document.getElementById('cancelEditBtn');
  const editWrap   = document.getElementById('editFormWrap');
  const toggleIcon = document.getElementById('toggleEditIcon');

  function openEdit() {
    editWrap.style.display = '';
    toggleIcon.className   = 'fa-duotone fa-chevron-up me-1';
    editWrap.scrollIntoView({ behavior: 'smooth', block: 'start' });
  }
  function closeEdit() {
    editWrap.style.display = 'none';
    toggleIcon.className   = 'fa-duotone fa-chevron-down me-1';
  }
  if (toggleBtn) toggleBtn.addEventListener('click', () => editWrap.style.display === 'none' ? openEdit() : closeEdit());
  if (cancelBtn) cancelBtn.addEventListener('click', closeEdit);

  /* ============================================================
     GALLERY LIGHTBOX
  ============================================================ */
  document.querySelectorAll('.gallery-tile[data-zoom]').forEach(tile => {
    tile.addEventListener('click', () => {
      const src = tile.getAttribute('data-zoom');
      if (!src) return;
      const img = document.getElementById('lbImageModalImg');
      if (img) img.src = src;
      const modal = document.getElementById('lbImageModal');
      if (modal && window.bootstrap) bootstrap.Modal.getOrCreateInstance(modal).show();
    });
  });
  const lbImageModal = document.getElementById('lbImageModal');
  if (lbImageModal) {
    lbImageModal.addEventListener('hidden.bs.modal', () => {
      const img = document.getElementById('lbImageModalImg');
      if (img) img.src = '';
    });
  }

  /* ============================================================
     CHAT — 1:1 admin style with lb-msg renderer
  ============================================================ */
  <?php if ($sold && !empty($buyer)): ?>
  let msg_none        = false;
  let chat_json       = {};
  let chat_signature  = '';
  let isLoadingMessages = false;

  const user_type = 'seller';
  const user_id   = SELLER_ID;

  const chat_notif = new Audio(asset_url + '/core/dash/audio/new-message.mp3');
  function message_sound() { try { chat_notif.volume = 0.6; chat_notif.play(); } catch(e){} }

  function decodeHtmlEntities(str) {
    const txt = document.createElement('textarea');
    txt.innerHTML = str ?? '';
    return txt.value.replace(/\n/g, '<br>');
  }

  function escapeAttr(str) {
    try { return String(str).replace(/&/g,'&amp;').replace(/"/g,'&quot;').replace(/</g,'&lt;').replace(/>/g,'&gt;'); }
    catch(e){ return ''; }
  }

  function formatTime(ts) {
    if (!ts) return '';
    const d = new Date(ts * 1000);
    const pad = n => String(n).padStart(2,'0');
    return `${pad(d.getDate())}.${pad(d.getMonth()+1)}.${d.getFullYear()} ${pad(d.getHours())}:${pad(d.getMinutes())}`;
  }

  function getRoleBadge(sender) {
    if (sender === 'seller') return { cls: 'lb-badge--seller', label: 'Seller' };
    if (sender === 'admin')  return { cls: 'lb-badge--admin',  label: 'Admin'  };
    if (sender === 'system') return { cls: 'lb-badge--system', label: 'System' };
    return { cls: 'lb-badge--client', label: 'Buyer' };
  }

  function getFallbackAvatar(sender) {
    if (sender === 'seller') return SELLER_AVATAR;
    return BUYER_AVATAR;
  }

  function renderTicks(msg_data) {
    // For seller's own messages: "seen" means the client has read it (seen_by_client).
    // seen_for_viewer from server is always 1 for seller's own msgs (seller already saw own msg),
    // so we use seen_by_client specifically for the tick state.
    const seen = (msg_data.seen_by_client == 1 || msg_data.seen_by_client === '1');
    const delivered = (msg_data.notify == 1 || msg_data.notify === '1');
    if (seen) {
      const title = 'Client hat gelesen' + (msg_data.seen_at ? (' • ' + formatTime(msg_data.seen_at)) : '');
      return ` <span class="lb-msg__ticks" style="color:#3dd68c" title="${escapeAttr(title)}"><i class="fa-solid fa-check-double"></i></span>`;
    }
    if (delivered) return ` <span class="lb-msg__ticks text-muted" title="Zugestellt"><i class="fa-solid fa-check-double"></i></span>`;
    return ` <span class="lb-msg__ticks text-muted" title="Gesendet"><i class="fa-solid fa-check"></i></span>`;
  }

  function load_message(message_id, msg_data, isGrouped) {
    const exactTime = formatTime(msg_data.time);
    const content   = decodeHtmlEntities(msg_data.content);

    if (msg_data.sender === 'system') {
      return `<div class="lb-syswrap"><div class="lb-sys">${content}</div><div class="lb-sys-time">${exactTime}</div></div>`;
    }

    const isMe = (msg_data.sender === user_type && String(msg_data.sender_id) === String(user_id));
    const alignClass = isMe ? 'lb-msg--end' : 'lb-msg--start';
    const headClass  = isMe ? 'lb-msg__head lb-msg__head--end' : 'lb-msg__head';
    const badge  = getRoleBadge(msg_data.sender);
    const avatar = (msg_data.sender_icon && ('' + msg_data.sender_icon).length)
                 ? msg_data.sender_icon
                 : getFallbackAvatar(msg_data.sender);
    const name = isMe ? 'You' : (msg_data.sender_name || BUYER_NAME);

    let html = `<div class="lb-msg ${alignClass}">`;

    if (!isGrouped) {
      html += `<div class="${headClass}">
        <img class="lb-msg__avatar" src="${avatar}" alt="avatar">
        <div class="lb-msg__meta">
          <div class="lb-msg__toprow">
            <div class="lb-msg__name">${name} <span class="lb-badge ${badge.cls}">${badge.label}</span></div>
          </div>
        </div>
      </div>`;
    }

    const isDeleted = (msg_data.type === 'deleted' || msg_data.deleted == 1);
    const safeContent = isDeleted ? '<em>Message deleted.</em>' : content;
    const bubbleCls   = isDeleted ? 'lb-msg__bubble lb-msg__bubble--deleted' : 'lb-msg__bubble';

    html += `<div class="${bubbleCls}" data-msg-id="${message_id}">${safeContent}</div>`;
    html += `<div class="lb-msg__stamp">${exactTime}${isMe ? renderTicks(msg_data) : ''}</div>`;
    html += `</div>`;
    return html;
  }

  function update_scroll() {
    const el = document.getElementById('chat_messages');
    if (el) el.scrollTop = el.scrollHeight;
  }

  function buildChatSignature(chat_list) {
    try {
      return Object.keys(chat_list || {}).map(k => {
        const v = chat_list[k] || {};
        return [k, v.sender??'', v.sender_id??'', v.time??'', v.type??'', v.deleted??'',
                v.content??'', v.notify??'', v.seen??'', v.seen_at??''].join('~');
      }).join('||');
    } catch(e) { return String(Date.now()); }
  }


  let lbChatReadActivated = false;
  let lbChatReadRequestRunning = false;

  function lbChatCanMarkRead() {
    if (!lbChatReadActivated) return false;
    if (document.visibilityState !== 'visible') return false;
    if (!(document.hasFocus && document.hasFocus())) return false;
    const chat = document.getElementById('chat_messages');
    if (!chat) return false;
    const rect = chat.getBoundingClientRect();
    return rect.bottom > 0 && rect.top < window.innerHeight;
  }

  function lbHasUnreadPeerMessages() {
    const list = chat_json || {};
    return Object.keys(list).some(function(key) {
      const msg = list[key] || {};
      const sender = String(msg.sender || msg.sender_type || '').toLowerCase();
      if (!sender || sender === 'system' || sender === user_type) return false;
      if (user_type === 'client') return Number(msg.seen_by_client ?? 0) !== 1;
      return Number(msg.seen_by_seller ?? 0) !== 1;
    });
  }

  function lbApplyRealtimeMessages(rawList) {
    if (!Array.isArray(rawList)) return false;
    const chat_list = {};
    rawList.forEach(function(msg, idx) {
      if (!msg || msg.deleted == 1 || msg.type === 'deleted') return;
      const row = Object.assign({}, msg);
      const sender = String(row.sender || row.sender_type || '').toLowerCase();
      row.sender = sender || row.sender;
      row.seen = sender === user_type
        ? Number(row.seen_by_client ?? 0)
        : Number(row.seen_by_seller ?? 0);
      chat_list[String(row.id ?? idx)] = row;
    });
    chat_json = chat_list;
    let chat_html = '', last_sender = '', last_sender_id = 0;
    Object.keys(chat_list).forEach(function(key) {
      const val = chat_list[key];
      const isGrouped = val.sender === last_sender && String(val.sender_id) === String(last_sender_id);
      chat_html += load_message(key, val, isGrouped);
      last_sender = val.sender; last_sender_id = val.sender_id;
    });
    $('#chat_messages').html(chat_html);
    update_scroll();
    return true;
  }

  function lbActivateChatRead() {
    if (document.visibilityState !== 'visible') return;
    lbChatReadActivated = true;
    if (!lbHasUnreadPeerMessages() || lbChatReadRequestRunning) return;
    lbChatReadRequestRunning = true;
    $.post(AJAX_URL, {
      action: 'seller_account_chat_seen',
      account_id: ACCOUNT_ID,
      viewer_role: 'seller'
    }, function(resp) {
      let data = resp;
      try { if (typeof resp === 'string') data = JSON.parse(resp); } catch(e) {}
      if (data && Array.isArray(data.messages)) lbApplyRealtimeMessages(data.messages);
    }).always(function() { lbChatReadRequestRunning = false; });
  }

  function lbBindChatReadInteraction() {
    const chat = document.getElementById('chat_messages');
    const input = document.getElementById('lbChatMessageInput');
    const form = document.getElementById('lbChatForm');
    [chat, input, form].forEach(function(el) {
      if (!el || el.dataset.lbReadBound === '1') return;
      el.dataset.lbReadBound = '1';
      // Only explicit user interaction may mark messages as read.
      // Browser tab activation, restored focus, scrolling and visibility changes must never create Ajax requests.
      el.addEventListener('click', lbActivateChatRead);
      el.addEventListener('keydown', lbActivateChatRead);
    });
  }

  function load_messages(markSeen = false) {
    if (isLoadingMessages) return;
    isLoadingMessages = true;
    const payload = { action: 'seller_account_chat_load', account_id: ACCOUNT_ID, viewer_role: 'seller' };
    if (markSeen && lbChatCanMarkRead()) payload.mark_seen = 1;
    if (payload.mark_seen) lbChatReadRequestRunning = true;
    $.post(AJAX_URL, payload, function(resp) {
      isLoadingMessages = false;
      let response;
      try { response = typeof resp === 'string' ? JSON.parse(resp) : resp; } catch(e){ return; }
      const chat_list = response.messages || {};
      const msg_count = Object.keys(chat_list).length;

      if (msg_count > 0) {
        msg_none = false;
        const sig = buildChatSignature(chat_list);
        if (sig !== chat_signature) {
          chat_signature = sig;
          chat_json = chat_list;
          let chat_html = '';
          let last_sender = '', last_sender_id = 0;
          $.each(chat_list, function(key, val) {
            const isGrouped = (val.sender === last_sender && String(val.sender_id) === String(last_sender_id));
            chat_html += load_message(key, val, isGrouped);
            last_sender = val.sender; last_sender_id = val.sender_id;
          });
          $('#chat_messages').html(chat_html);
          update_scroll();
        }
      } else {
        if (!msg_none) {
          $('#chat_messages').html('<div class="lb-chat-empty"><i class="fa-duotone fa-comment-dots fa-2x mb-2"></i><span class="small">No messages yet. Start the conversation!</span></div>');
          msg_none = true;
        }
        chat_json = {}; chat_signature = '';
      }
    }).fail(function() { isLoadingMessages = false; }).always(function(){ lbChatReadRequestRunning = false; });
  }

  // ── Chat form: file attach + paste + submit ──
  (function initChatAttach() {
    const form     = document.getElementById('lbChatForm');
    if (!form) return;
    const msgInput   = document.getElementById('lbChatMessageInput');
    const fileInput  = document.getElementById('lbChatFile');
    const attachBtn  = document.getElementById('lbChatAttachBtn');
    const preview    = document.getElementById('lbChatPreview');
    const previewImg = document.getElementById('lbChatPreviewImg');
    const prevName   = document.getElementById('lbChatPreviewName');
    const removeBtn  = document.getElementById('lbChatRemoveBtn');
    const errBox     = document.getElementById('lbChatError');
    const sendBtn    = document.getElementById('lbChatSendBtn');
    let previewUrl   = null;

    function setError(msg) {
      if (!errBox) return;
      if (!msg) { errBox.classList.add('d-none'); errBox.textContent = ''; return; }
      errBox.textContent = msg; errBox.classList.remove('d-none');
    }
    function clearFile() {
      if (previewUrl) { URL.revokeObjectURL(previewUrl); previewUrl = null; }
      if (fileInput)  fileInput.value = '';
      if (preview)    preview.classList.add('d-none');
      if (previewImg) previewImg.src = '';
      if (prevName)   prevName.textContent = '';
    }
    function showFile(file) {
      if (!file) return clearFile();
      if (!/^image\/(png|jpe?g|gif)$/i.test(file.type)) { setError('Only PNG/JPG/JPEG/GIF allowed.'); clearFile(); return; }
      setError('');
      if (previewUrl) URL.revokeObjectURL(previewUrl);
      previewUrl = URL.createObjectURL(file);
      if (previewImg) previewImg.src = previewUrl;
      if (prevName)   prevName.textContent = file.name || 'image';
      if (preview)    preview.classList.remove('d-none');
    }
    if (attachBtn && fileInput) {
      attachBtn.addEventListener('click', () => { setError(''); fileInput.click(); });
      fileInput.addEventListener('change', () => showFile(fileInput.files && fileInput.files[0]));
    }
    if (removeBtn) removeBtn.addEventListener('click', () => { setError(''); clearFile(); });

    document.addEventListener('paste', function(e) {
      if (!fileInput || fileInput.disabled) return;
      const active = document.activeElement;
      if (!form.contains(active) && active !== msgInput) return;
      const items = (e.clipboardData && e.clipboardData.items) ? e.clipboardData.items : [];
      for (const it of items) {
        if (it && it.type && it.type.indexOf('image/') === 0) {
          const blob = it.getAsFile(); if (!blob) continue;
          const file = new File([blob], 'pasted-image.png', { type: blob.type || 'image/png' });
          const dt = new DataTransfer(); dt.items.add(file); fileInput.files = dt.files;
          showFile(file); e.preventDefault(); break;
        }
      }
    });

    form.addEventListener('submit', function(e) {
      e.preventDefault();
      const msg     = msgInput ? msgInput.value.trim() : '';
      const hasFile = fileInput && fileInput.files && fileInput.files.length > 0;
      if (!msg && !hasFile) { setError('Please type a message or attach an image.'); return; }
      setError('');
      if (sendBtn) {
        sendBtn.disabled = true;
        const prog = sendBtn.querySelector('.indicator-progress');
        if (prog) prog.classList.remove('d-none');
      }
      const fd = new FormData(form);
      $.ajax({
        url: form.getAttribute('action'), method: 'POST', data: fd,
        processData: false, contentType: false
      }).done(function() {
        if (msgInput) msgInput.value = '';
        clearFile();
        load_messages();
        update_scroll();
      }).fail(function() {
        setError('Upload failed. Please try again.');
      }).always(function() {
        if (sendBtn) {
          sendBtn.disabled = false;
          const prog = sendBtn.querySelector('.indicator-progress');
          if (prog) prog.classList.add('d-none');
        }
      });
    });
  })();

  // ── Image click → lightbox ──
  document.addEventListener('DOMContentLoaded', function() {
    const chat    = document.getElementById('chat_messages');
    const modal   = document.getElementById('lbImageModal');
    const modalImg = document.getElementById('lbImageModalImg');
    if (!chat || !modal || !modalImg) return;
    chat.addEventListener('click', function(e) {
      const img = e.target.closest('img');
      if (!img || !chat.contains(img)) return;
      if (img.classList.contains('lb-msg__avatar')) return;
      e.preventDefault(); e.stopPropagation();
      const src = img.getAttribute('data-full') || img.src;
      if (!src) return;
      modalImg.src = src;
      if (window.bootstrap) bootstrap.Modal.getOrCreateInstance(modal).show();
    }, true);
    modal.addEventListener('hidden.bs.modal', () => { modalImg.src = ''; });
  });

  window.lbOrderViewChatUpdate = function(data) {
    if (data && data.order_id && data.order_id !== ('acct_' + ACCOUNT_ID)) return;
    if (data && Array.isArray(data.messages)) {
      const chat_list = data.messages;
      const sig = buildChatSignature(chat_list);
      if (sig !== chat_signature) {
        chat_signature = sig;
        chat_json = chat_list;
        let chat_html = '', last_sender = '', last_sender_id = 0;
        $.each(chat_list, function(key, val) {
          const isGrouped = val.sender === last_sender && String(val.sender_id) === String(last_sender_id);
          chat_html += load_message(key, val, isGrouped);
          last_sender = val.sender; last_sender_id = val.sender_id;
        });
        $('#chat_messages').html(chat_html);
        update_scroll();
      }
      return;
    }
    load_messages(false);
  };

  function bindAccountChatSocket() {
    const sock = window.lbSocket || window.socket || null;
    if (!sock || sock.__lbSellerAccountBound === ACCOUNT_ID) return;
    sock.__lbSellerAccountBound = ACCOUNT_ID;
    try { sock.emit('join', 'sellers'); sock.emit('join', 'acct_' + String(ACCOUNT_ID)); } catch(e) {}
    const handler = function(raw) {
      let data = raw || {};
      for (let i = 0; i < 3; i++) {
        if (data && data.data && typeof data.data === 'object') data = data.data;
        else if (data && data.payload && typeof data.payload === 'object') data = data.payload;
        else break;
      }
      const matches = String(data.account_id || '') === String(ACCOUNT_ID)
        || String(data.order_id || '') === ('acct_' + String(ACCOUNT_ID));
      if (!matches) return;
      if (Array.isArray(data.messages)) {
        lbApplyRealtimeMessages(data.messages);
        return;
      }
      load_messages(false);
    };
    try { sock.on('account_chat_update', handler); } catch(e) {}
    try { sock.on('chat_update', handler); } catch(e) {}
  }

  // ── Start chat updates ──
  $(document).ready(function() {
    load_messages(false);
    update_scroll();
    lbBindChatReadInteraction();
    bindAccountChatSocket();
    setTimeout(bindAccountChatSocket, 350);
    setTimeout(bindAccountChatSocket, 1200);
    window.lbChatInterval && clearInterval(window.lbChatInterval);
    window.lbChatInterval = setInterval(function(){
      if (document.visibilityState !== 'visible') return;
      if (window.lbRealtimeConnected) return;
      load_messages(false);
    }, 60000);
  });
  <?php endif ?>

  /* ============================================================
     EDIT: TomSelect
  ============================================================ */
  function enableSmartDropup(selector) {
    const el = document.querySelector(selector);
    if (!el || !el.tomselect) return;
    const ts = el.tomselect, wrapper = ts.wrapper;
    const update = () => wrapper.classList.toggle('ts-dropup', window.innerHeight - wrapper.getBoundingClientRect().bottom < 320);
    ts.on('dropdown_open', update);
    ts.on('dropdown_close', () => wrapper.classList.remove('ts-dropup'));
    window.addEventListener('resize', update);
    window.addEventListener('scroll', update, true);
  }
  if (document.getElementById('champions')) {
    HSCore.components.HSTomSelect.init('#champions', {
      maxOptions: null, hideSelected: true,
      onItemAdd() { try { this.setTextboxValue(''); this.refreshOptions(false); } catch(e){} },
      onInitialize() {
        const ts = this;
        document.querySelectorAll('#champions option').forEach(opt => {
          const v = opt.value, t = opt.textContent, img = opt.getAttribute('data-image');
          ts.options && ts.options[v] ? ts.updateOption(v,{value:v,text:t,img}) : ts.addOption({value:v,text:t,img});
        });
        ts.refreshItems();
      },
      render: {
        option: (d,e) => `<div style="display:flex;align-items:center"><img src="${e(d.img||'')}" style="width:30px;height:30px;border-radius:5px;margin-right:8px"><span>${e(d.text)}</span></div>`,
        item:   (d,e) => `<div style="display:flex;align-items:center"><img src="${e(d.img||'')}" style="width:20px;height:20px;border-radius:5px;margin-right:5px"><span>${e(d.text)}</span></div>`
      }
    });
    HSCore.components.HSTomSelect.init('#skins', {
      maxOptions: null, hideSelected: true,
      onItemAdd() { try { this.setTextboxValue(''); this.refreshOptions(false); } catch(e){} },
      onInitialize() {
        this.options = {};
        document.querySelectorAll('#skins option').forEach(opt => this.addOption({value:opt.value,text:opt.textContent,img:opt.getAttribute('data-image')}));
      },
      render: {
        option: (d,e) => `<div style="display:flex;align-items:center"><img src="${e(d.img||'')}" style="height:30px;border-radius:5px;margin-right:8px"><span>${e(d.text)}</span></div>`,
        item:   (d,e) => `<div style="display:flex;align-items:center"><img src="${e(d.img||'')}" style="height:20px;border-radius:5px;margin-right:5px"><span>${e(d.text)}</span></div>`
      }
    });
    HSCore.components.HSTomSelect.init('#roles', { maxOptions: null, hideSelected: true, onItemAdd() { try { this.setTextboxValue(''); this.refreshOptions(false); } catch(e){} } });
    enableSmartDropup('#champions');
    enableSmartDropup('#skins');
    enableSmartDropup('#roles');
  }

  /* ── Rank toggles ─── */
  function rankToggle(prefix) {
    const sel = document.querySelector('[name="' + prefix + '_rank"]');
    if (!sel) return;
    const onChange = () => {
      const v      = parseInt(sel.value);
      const rankEl = document.querySelector('.' + prefix + '-rank');
      const divEl  = document.querySelector('.' + prefix + '-division');
      const lpEl   = document.querySelector('.' + prefix + '-lp');
      if (!rankEl) return;
      if (v === 0) {
        rankEl.className = rankEl.className.replace(/col-[89]/, 'col-12');
        divEl && divEl.classList.add('d-none');
        lpEl  && lpEl.classList.add('d-none');
      } else if (v < 8) {
        rankEl.className = rankEl.className.replace('col-12','col-9');
        divEl && divEl.classList.remove('d-none');
        lpEl  && lpEl.classList.add('d-none');
      } else {
        rankEl.className = rankEl.className.replace('col-12','col-9');
        divEl && divEl.classList.add('d-none');
        lpEl  && lpEl.classList.remove('d-none');
      }
    };
    sel.addEventListener('change', onChange); onChange();
  }
  rankToggle('current'); rankToggle('flex'); rankToggle('previous');

  /* ── Email toggle ─── */
  window.toggleEditEmailFields = function(val) {
    const wE  = document.getElementById('edit_email_wrap');
    const wP  = document.getElementById('edit_email_password_wrap');
    const iE  = document.getElementById('edit_email');
    const iP  = document.getElementById('edit_email_password');
    if (!wE) return;
    if (val === 'unverified') {
      if (iE) iE.value = 'unverified';
      if (iP) { iP.value = ''; iP.removeAttribute('required'); }
      wE.style.display = wP.style.display = 'none';
    } else {
      if (iE && iE.value === 'unverified') iE.value = '';
      if (iP) iP.setAttribute('required','required');
      wE.style.display = wP.style.display = '';
    }
  };

  /* ── Price → earnings ─── */
  const priceInput   = document.querySelector('.js-price-input');
  const earningsPrev = document.querySelector('.js-earnings-preview');
  const feePercent   = <?= (float)$effective_fee ?>;
  if (priceInput && earningsPrev) {
    priceInput.addEventListener('input', function() {
      const p = parseFloat(String(this.value).replace(',','.')) || 0;
      earningsPrev.textContent = (p * (1 - feePercent / 100)).toFixed(2);
    });
  }

  /* ── Gallery uploader ─── */
  (function initGallery() {
    const dropzone   = document.getElementById('galleryDropzone');
    const input      = document.getElementById('galleryUpload');
    const preview    = document.getElementById('previewGallery');
    const orderInput = document.getElementById('imagesOrderInput');
    if (!dropzone || !input || !preview || !orderInput) return;

    let galleryItems = [], dragFromIndex = null, initial = [];
    try { initial = JSON.parse(orderInput.value || '[]') || []; } catch(e){}
    initial.forEach(url => { if (url) galleryItems.push({ type:'existing', url, deletePayload: JSON.stringify({id: ACCOUNT_ID, image: url}) }); });

    const fileKey = f => `${f.name}__${f.size}__${f.lastModified}`;
    function syncFiles() {
      const dt = new DataTransfer();
      galleryItems.filter(it => it.type === 'file').forEach(it => dt.items.add(it.file));
      input.files = dt.files;
    }
    function syncOrder() {
      let ni = 0;
      orderInput.value = JSON.stringify(galleryItems.map(it => it.type === 'existing' ? it.url : `__new__${ni++}`));
    }
    function render() {
      preview.innerHTML = '';
      galleryItems.forEach((it, i) => {
        const col  = document.createElement('div'); col.className = 'col-6 col-md-3';
        const tile = document.createElement('div'); tile.className = `gallery-tile ${i===0?'is-main':''}`;
        tile.setAttribute('draggable','true'); tile.setAttribute('data-index', i);
        if (i === 0) { const b = document.createElement('div'); b.className='gallery-badge'; b.textContent='MAIN'; tile.appendChild(b); }
        const nb = document.createElement('div'); nb.className='gallery-badge'; nb.style.cssText='left:auto;right:.4rem;background:rgba(0,0,0,.45)'; nb.textContent=`#${i+1}`; tile.appendChild(nb);
        const img = document.createElement('img'); img.alt='';
        if (it.type === 'existing') { img.src = it.url; } else { const u = URL.createObjectURL(it.file); img.src = u; img.onload = () => URL.revokeObjectURL(u); }
        tile.appendChild(img);
        const del = document.createElement('button'); del.type='button'; del.className='gallery-remove'; del.innerHTML='<i class="fa-solid fa-trash"></i>';
        if (it.type === 'existing') { del.setAttribute('data-action','seller_delete_account_image'); del.setAttribute('data-id', it.deletePayload); }
        else { del.setAttribute('data-remove-index', i); }
        tile.appendChild(del); col.appendChild(tile); preview.appendChild(col);
      });
      syncFiles(); syncOrder();
    }
    function addFiles(files) {
      const ex = new Set(galleryItems.filter(it=>it.type==='file').map(it=>fileKey(it.file)));
      (files||[]).filter(f=>f.type.startsWith('image/')).forEach(f => { if (!ex.has(fileKey(f))) { galleryItems.push({type:'file',file:f}); ex.add(fileKey(f)); } });
      render();
    }
    render();
    input.addEventListener('change', () => addFiles(Array.from(input.files||[])));
    const lbl = document.querySelector('label[for="galleryUpload"]');
    if (lbl) lbl.addEventListener('mousedown', () => { try { input.value=''; } catch(e){} });
    dropzone.addEventListener('click', e => {
      if (e.target.closest('label[for="galleryUpload"]')) return;
      e.preventDefault();
      try { input.showPicker ? input.showPicker() : input.click(); } catch(e) { input.click(); }
    });
    preview.addEventListener('click', e => {
      const rm = e.target.closest('.gallery-remove'); if (!rm) return;
      const idxAttr = rm.getAttribute('data-remove-index');
      if (idxAttr !== null) { galleryItems.splice(parseInt(idxAttr),1); render(); return; }
      const action = rm.getAttribute('data-action'), payload = rm.getAttribute('data-id');
      if (action && payload) {
        rm.disabled = true;
        $.post(AJAX_URL, {action, id: payload}).done(resp => {
          let d = resp; try { if (typeof resp==='string') d=JSON.parse(resp); } catch(e){}
          let url = null; try { url = JSON.parse(payload).image; } catch(e){}
          if (url) { const i = galleryItems.findIndex(it=>it.type==='existing'&&it.url===url); if (i>-1) galleryItems.splice(i,1); render(); }
          else window.location.reload();
        }).fail(() => { rm.disabled=false; alert('Could not delete image.'); });
      }
    });
    preview.addEventListener('dragstart', e => { const t=e.target.closest('.gallery-tile'); if(!t) return; dragFromIndex=parseInt(t.getAttribute('data-index')); if(e.dataTransfer) e.dataTransfer.setData('text/plain', dragFromIndex); });
    preview.addEventListener('dragover',  e => { if(dragFromIndex!==null) e.preventDefault(); });
    preview.addEventListener('drop',      e => { e.preventDefault(); const t=e.target.closest('.gallery-tile'); if(!t) return; const to=parseInt(t.getAttribute('data-index')), from=dragFromIndex; dragFromIndex=null; if(!isNaN(from)&&!isNaN(to)&&from!==to){const item=galleryItems.splice(from,1)[0]; galleryItems.splice(to,0,item); render();} });
    preview.addEventListener('dragend',   () => dragFromIndex=null);
    ['dragenter','dragover'].forEach(ev => dropzone.addEventListener(ev, e=>{e.preventDefault();e.stopPropagation();dropzone.classList.add('dragover');}));
    ['dragleave','drop'].forEach(ev      => dropzone.addEventListener(ev, e=>{e.preventDefault();e.stopPropagation();dropzone.classList.remove('dragover');}));
    dropzone.addEventListener('drop', e => addFiles(Array.from(e.dataTransfer?.files||[])));
    document.addEventListener('paste', e => {
      const files = [];
      for (const item of (e.clipboardData?.items||[])) {
        if (item.kind==='file') { const b=item.getAsFile(); if(b&&b.type.startsWith('image/')){const ext=b.type.split('/')[1].replace('jpeg','jpg'); files.push(new File([b],`paste-${Date.now()}.${ext}`,{type:b.type}));} }
      }
      if (files.length) { e.preventDefault(); addFiles(files); }
    });
  })();

  /* ── Pretty validation ─── */
  $(document).on('submit', 'form.js-pretty-validate', function(e) {
    const form = this, $form = $(form);
    $form.find('.js-validation-alert').addClass('d-none');
    $form.find('.is-invalid').removeClass('is-invalid');
    if (!form.checkValidity()) {
      e.preventDefault(); e.stopImmediatePropagation();
      $form.addClass('was-validated');
      const invalid = Array.from(form.querySelectorAll(':invalid')).filter(el => !el.disabled && $(el).is(':visible'));
      const items = []; invalid.forEach(el => { const lbl = el.id ? $('label[for="'+el.id+'"]').first().text().trim() : el.getAttribute('placeholder')||el.name||'Field'; if(lbl&&!items.includes(lbl)) items.push(lbl); });
      $form.find('.js-validation-alert').html('<div class="fw-semibold mb-1">Please complete:</div><ul class="mb-0 ps-3"><li>'+items.join('</li><li>')+'</li></ul>').removeClass('d-none');
      invalid.forEach(el => $(el).addClass('is-invalid'));
      if (typeof create_toast === 'function') create_toast('danger','Missing info','Check the highlighted fields.');
      return false;
    }
  });
  $(document).on('input change', 'form.js-pretty-validate :input', function() {
    $(this).removeClass('is-invalid').closest('form').find('.js-validation-alert').addClass('d-none');
  });

})();
</script>
<?= $this->end() ?>
