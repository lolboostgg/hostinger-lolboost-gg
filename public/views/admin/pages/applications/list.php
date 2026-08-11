<?= $this->layout('admin/layouts/main', ['meta' => [
    'title'       => 'Job Applications — Admin',
    'h1'          => 'Job Applications',
    'description' => 'Manage all incoming job applications across all roles.',
]]) ?>

<?php
$all      = $applications ?? [];
$pending  = array_filter($all, fn($r) => ($r['status'] ?? '') === 'pending');
$trial    = array_filter($all, fn($r) => ($r['status'] ?? '') === 'trial');
$accepted = array_filter($all, fn($r) => ($r['status'] ?? '') === 'accepted');
$declined = array_filter($all, fn($r) => ($r['status'] ?? '') === 'declined');

$roleLabels = [
    'lol_booster' => ['label' => 'LoL Booster', 'color' => '#c89b3c', 'bg' => 'rgba(200,155,60,.12)', 'border' => 'rgba(200,155,60,.28)', 'img' => ASSET_URL . '/website/images/icons/league-of-legends.png'],
    'tft_booster' => ['label' => 'TFT Booster', 'color' => '#0bc4e3', 'bg' => 'rgba(11,196,227,.12)',  'border' => 'rgba(11,196,227,.28)',  'img' => ASSET_URL . '/website/images/icons/teamfight-tactics.png'],
    'val_booster' => ['label' => 'VAL Booster', 'color' => '#ff4655', 'bg' => 'rgba(255,70,85,.12)',   'border' => 'rgba(255,70,85,.28)',   'img' => ASSET_URL . '/website/images/icons/valorant.png'],
    'booster'     => ['label' => 'Booster',      'color' => '#a78bfa', 'bg' => 'rgba(167,139,250,.12)', 'border' => 'rgba(167,139,250,.28)', 'img' => null],
    'gg_girl'     => ['label' => 'GG-Girl',     'color' => '#e040fb', 'bg' => 'rgba(224,64,251,.12)',  'border' => 'rgba(224,64,251,.28)',  'img' => ASSET_URL . '/website/images/gg-girl.svg'],
    'seller'      => ['label' => 'Seller',       'color' => '#4ade80', 'bg' => 'rgba(74,222,128,.12)', 'border' => 'rgba(74,222,128,.28)', 'img' => null],
];
$h = fn($v) => htmlspecialchars((string)($v ?? ''), ENT_QUOTES, 'UTF-8');

// Generic "booster" applications all share role='booster' and only differ by the
// chosen game (boost_game). Normalize that free-text game into a stable slug so we
// can offer a filter pill per game (mirrors the routing logic in ajax.php).
function jaGameSlug(string $game): string {
    $g = strtolower(trim($game));
    if ($g === '') return '';
    if (strpos($g, 'wild') !== false) return 'wild-rift';
    if (strpos($g, 'counter') !== false || strpos($g, 'cs2') !== false || strpos($g, 'cs 2') !== false) return 'cs2';
    if (strpos($g, 'fortnite') !== false) return 'fortnite';
    if (strpos($g, 'overwatch') !== false) return 'overwatch';
    if (strpos($g, 'rocket') !== false) return 'rocket-league';
    if (strpos($g, 'apex') !== false) return 'apex';
    if (strpos($g, 'marvel') !== false || strpos($g, 'rivals') !== false) return 'marvel-rivals';
    if (strpos($g, 'league of legends') !== false || $g === 'lol') return 'lol';
    if (strpos($g, 'valorant') !== false || $g === 'val') return 'val';
    if (strpos($g, 'teamfight') !== false || strpos($g, 'tft') !== false) return 'tft';
    return preg_replace('/[^a-z0-9]+/', '-', $g);
}

// Per-game filter pills for the generic booster games (LoL/TFT/VAL keep their own
// role pills). label = short chip text, img = icon asset.
$gameFilters = [
    'wild-rift'     => ['label' => 'Wild Rift',     'img' => ASSET_URL . '/website/images/icons/lol-wild-rift.png'],
    'overwatch'     => ['label' => 'Overwatch 2',   'img' => ASSET_URL . '/website/images/icons/overwatch-2.png'],
    'apex'          => ['label' => 'Apex',          'img' => ASSET_URL . '/website/images/icons/apex-legends.png'],
    'fortnite'      => ['label' => 'Fortnite',      'img' => ASSET_URL . '/website/images/icons/fortnite.png'],
    'rocket-league' => ['label' => 'Rocket League', 'img' => ASSET_URL . '/website/images/icons/rocket-league.png'],
    'cs2'           => ['label' => 'CS2',           'img' => ASSET_URL . '/website/images/icons/counter-strike-2.png'],
    'marvel-rivals' => ['label' => 'Marvel Rivals', 'img' => ASSET_URL . '/website/images/icons/marvel-rivals.png'],
];

// Only show a game pill if at least one application exists for that game, plus an
// "Other" pill for any generic booster game not in the known list above.
$gameCounts = [];
$hasOtherGame = false;
foreach ($all as $r) {
    if (($r['role'] ?? '') !== 'booster' && ($r['role'] ?? '') !== '') continue;
    $slug = jaGameSlug((string)($r['boost_game'] ?? ''));
    if ($slug === '' || in_array($slug, ['lol', 'val', 'tft'], true)) continue;
    if (isset($gameFilters[$slug])) {
        $gameCounts[$slug] = ($gameCounts[$slug] ?? 0) + 1;
    } else {
        $hasOtherGame = true;
    }
}

// Build the ordered option list for the games dropdown. LoL/TFT/VAL use their
// dedicated role filters; the generic games use "game:<slug>".
$gameDropdownItems = [
    ['filter' => 'lol_booster', 'label' => 'LoL', 'img' => ASSET_URL . '/website/images/icons/league-of-legends.png'],
    ['filter' => 'tft_booster', 'label' => 'TFT', 'img' => ASSET_URL . '/website/images/icons/teamfight-tactics.png'],
    ['filter' => 'val_booster', 'label' => 'VAL', 'img' => ASSET_URL . '/website/images/icons/valorant.png'],
];
foreach ($gameFilters as $slug => $gf) {
    if (empty($gameCounts[$slug])) continue;
    $gameDropdownItems[] = ['filter' => 'game:' . $slug, 'label' => $gf['label'], 'img' => $gf['img']];
}
if ($hasOtherGame) {
    $gameDropdownItems[] = ['filter' => 'game:other', 'label' => 'Other Games', 'img' => null];
}

function jaStatusBadge(string $status): string {
    return match($status) {
        'trial'    => '<span class="ja-badge ja-badge--trial"><i class="fa-duotone fa-flask" style="font-size:.55rem;"></i> Trial</span>',
        'accepted' => '<span class="ja-badge ja-badge--accepted"><i class="fa-solid fa-circle-check" style="font-size:.55rem;"></i> Accepted</span>',
        'declined' => '<span class="ja-badge ja-badge--declined"><i class="fa-solid fa-xmark" style="font-size:.6rem;"></i> Declined</span>',
        default    => '<span class="ja-badge ja-badge--pending"><i class="fa-duotone fa-clock" style="font-size:.55rem;"></i> Pending</span>',
    };
}

function jaViewBtn(array $row, string $roleKey, string $status): string {
    $h = fn($v) => htmlspecialchars((string)($v ?? ''), ENT_QUOTES, 'UTF-8');
    return sprintf(
        '<button type="button" class="ja-btn ja-btn--view view-app-btn" title="View Details"
            data-id="%s" data-fullname="%s" data-email="%s" data-discord="%s"
            data-role="%s" data-country="%s" data-age="%s" data-languages="%s"
            data-ingame="%s" data-region="%s" data-current-rank="%s" data-peak-rank="%s"
            data-champ-pool="%s" data-availability="%s" data-experience="%s"
            data-motivation="%s" data-referral="%s" data-status="%s"
            data-discord-invite="%s"
            data-bs-toggle="modal" data-bs-target="#appDetailModal">
            <i class="fa-duotone fa-eye"></i>
        </button>',
        $h($row['id'] ?? ''), $h($row['fullname'] ?? ''), $h($row['email'] ?? ''), $h($row['discord_tag'] ?? ''),
        $h($roleKey), $h($row['country'] ?? ''), $h($row['age'] ?? ''), $h($row['languages'] ?? ''),
        $h($row['ingame_name'] ?? ''), $h($row['server_region'] ?? ''), $h($row['current_rank'] ?? ''), $h($row['peak_rank'] ?? ''),
        $h($row['champ_pool'] ?? ''), $h($row['availability'] ?? ''), $h($row['experience'] ?? ''),
        $h($row['motivation'] ?? ''), $h($row['referral'] ?? ''), $h($status),
        $h($row['discord_invite'] ?? '')
    );
}

function jaActionButtons(int $id, string $roleKey, string $status, string $discord, bool $isBooster): string {
    $d = htmlspecialchars($discord, ENT_QUOTES);
    $html = '';
    if ($status === 'pending') {
        if ($isBooster) {
            $html .= '<button type="button" class="ja-btn ja-btn--discord discord-invite-btn" data-trial-invite="1" data-id="'.$id.'" data-discord="'.$d.'" data-role="'.$roleKey.'" title="Send Trial Invite"><i class="fa-brands fa-discord"></i> Send Trial Invite</button>';
        } else {
            $html .= '<button type="button" class="ja-btn ja-btn--accept ja-action-btn" data-action="accept_job_application" data-id="'.$id.'" data-role="'.$roleKey.'" title="Accept"><i class="fa-duotone fa-check"></i> Accept</button>';
        }
        $html .= '<button type="button" class="ja-btn ja-btn--decline ja-action-btn" data-action="decline_job_application" data-id="'.$id.'" data-role="'.$roleKey.'" title="Decline"><i class="fa-duotone fa-xmark"></i></button>';
    } elseif ($status === 'trial' && $isBooster) {
        $html .= '<button type="button" class="ja-btn ja-btn--accept ja-action-btn" data-action="accept_job_application" data-id="'.$id.'" data-role="'.$roleKey.'" title="Final Accept"><i class="fa-duotone fa-check"></i> Accept</button>';
        $html .= '<button type="button" class="ja-btn ja-btn--discord discord-invite-btn" data-id="'.$id.'" data-discord="'.$d.'" data-role="'.$roleKey.'" data-bs-toggle="modal" data-bs-target="#discordInviteModal" title="Send Discord Invite"><i class="fa-brands fa-discord"></i> Invite</button>';
        $html .= '<button type="button" class="ja-btn ja-btn--decline ja-action-btn" data-action="decline_job_application" data-id="'.$id.'" data-role="'.$roleKey.'" title="Decline"><i class="fa-duotone fa-xmark"></i></button>';
    } elseif ($status === 'accepted') {
        $html .= '<button type="button" class="ja-btn ja-btn--discord discord-invite-btn" data-id="'.$id.'" data-discord="'.$d.'" data-role="'.$roleKey.'" data-bs-toggle="modal" data-bs-target="#discordInviteModal" title="Send Discord Invite"><i class="fa-brands fa-discord"></i> Invite</button>';
    }
    return $html;
}
?>

<?= $this->start('styles') ?>
<style>
/* ── Stats ── */
.ja-stats{display:grid;grid-template-columns:repeat(auto-fit,minmax(150px,1fr));gap:12px;margin-bottom:16px;}
.ja-stat{background:#25282a;border:1px solid rgba(255,255,255,.07);border-radius:16px;padding:16px 18px;display:flex;align-items:center;gap:12px;box-shadow:0 2px 16px rgba(0,0,0,.2);transition:transform .15s;}
.ja-stat:hover{transform:translateY(-2px);}
.ja-stat-icon{width:40px;height:40px;border-radius:11px;display:flex;align-items:center;justify-content:center;font-size:1rem;flex-shrink:0;}
.ja-stat-label{font-size:.68rem;font-weight:700;color:rgba(255,255,255,.35);text-transform:uppercase;letter-spacing:.06em;margin-bottom:2px;}
.ja-stat-value{font-size:1.25rem;font-weight:900;color:rgba(255,255,255,.9);line-height:1.2;}
/* ── Hero ── */
.al-hero{border-radius:20px;border:1px solid rgba(255,255,255,.07);background:#25282a;padding:20px 24px;display:flex;align-items:center;gap:14px;flex-wrap:wrap;margin-bottom:14px;box-shadow:0 2px 20px rgba(0,0,0,.22);}
.al-hero-icon{width:44px;height:44px;border-radius:13px;background:linear-gradient(135deg,rgba(109,92,255,.25),rgba(176,92,255,.15));border:1px solid rgba(109,92,255,.25);display:flex;align-items:center;justify-content:center;font-size:1.1rem;color:#c4b5fd;flex-shrink:0;}
.al-hero-title{font-size:1.1rem;font-weight:950;color:rgba(255,255,255,.92);margin:0;}
.al-hero-sub{font-size:.8rem;color:rgba(255,255,255,.4);margin:2px 0 0;}
/* ── Toolbar ── */
.al-toolbar-card{border-radius:16px;border:1px solid rgba(255,255,255,.07);background:#25282a;padding:12px 16px;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:10px;margin-bottom:16px;box-shadow:0 2px 16px rgba(0,0,0,.18);}
/* ── Pills ── */
.al-pills{display:flex;gap:6px;flex-wrap:wrap;}
.al-pill{display:inline-flex;align-items:center;gap:.3rem;padding:5px 13px;border-radius:99px;font-size:.78rem;font-weight:800;cursor:pointer;border:1px solid rgba(255,255,255,.09);background:rgba(255,255,255,.04);color:rgba(255,255,255,.6);transition:all .12s;user-select:none;}
.al-pill:hover{background:rgba(255,255,255,.08);color:rgba(255,255,255,.85);}
.al-pill.active{background:rgba(109,92,255,.18);border-color:rgba(109,92,255,.45);color:#c4b5fd;}
.al-pill[data-filter="lol_booster"].active{background:rgba(200,155,60,.13);border-color:rgba(200,155,60,.40);color:#e5b84f;}
.al-pill[data-filter="tft_booster"].active{background:rgba(11,196,227,.12);border-color:rgba(11,196,227,.35);color:#0bc4e3;}
.al-pill[data-filter="val_booster"].active{background:rgba(255,70,85,.12);border-color:rgba(255,70,85,.35);color:#ff6b77;}
.al-pill[data-filter="gg_girl"].active{background:rgba(224,64,251,.12);border-color:rgba(224,64,251,.35);color:#e040fb;}
.al-pill[data-filter="seller"].active{background:rgba(74,222,128,.12);border-color:rgba(74,222,128,.30);color:#4ade80;}
/* Games dropdown */
.ja-game-dd{position:relative;display:inline-block;}
.ja-game-dd-btn{gap:.4rem;}
.ja-game-dd-btn img{width:14px;height:14px;object-fit:contain;}
.ja-game-dd.active .ja-game-dd-btn,.ja-game-dd-btn.active{background:rgba(109,92,255,.18);border-color:rgba(109,92,255,.45);color:#c4b5fd;}
.ja-game-dd-menu{position:absolute;top:calc(100% + 6px);left:0;z-index:60;min-width:190px;max-height:340px;overflow-y:auto;background:#2b2e30;border:1px solid rgba(255,255,255,.10);border-radius:12px;padding:6px;box-shadow:0 12px 40px rgba(0,0,0,.45);display:none;}
.ja-game-dd.open .ja-game-dd-menu{display:block;}
.ja-game-opt{display:flex;align-items:center;gap:.55rem;width:100%;padding:8px 10px;border:none;background:transparent;border-radius:9px;color:rgba(255,255,255,.72);font-size:.82rem;font-weight:700;cursor:pointer;text-align:left;font-family:inherit;transition:background .12s,color .12s;}
.ja-game-opt:hover{background:rgba(109,92,255,.14);color:#fff;}
.ja-game-opt.active{background:rgba(109,92,255,.2);color:#c4b5fd;}
/* Status pills */
.al-pill[data-status="all"].active{background:rgba(109,92,255,.18);border-color:rgba(109,92,255,.45);color:#c4b5fd;}
.al-pill[data-status="pending"].active{background:rgba(250,204,21,.12);border-color:rgba(250,204,21,.35);color:#facc15;}
.al-pill[data-status="trial"].active{background:rgba(99,102,241,.16);border-color:rgba(99,102,241,.40);color:#a5b4fc;}
.al-pill[data-status="accepted"].active{background:rgba(74,222,128,.12);border-color:rgba(74,222,128,.30);color:#4ade80;}
.al-pill[data-status="declined"].active{background:rgba(251,113,133,.12);border-color:rgba(251,113,133,.28);color:#fb7185;}
/* Remove old select styles */
.ja-select-wrap{display:none;}
.ja-status-select{display:none;}
/* ── Search ── */
.al-search-wrap{position:relative;}
.al-search-wrap input{background:rgba(255,255,255,.04)!important;border:1px solid rgba(255,255,255,.09)!important;border-radius:10px!important;color:rgba(255,255,255,.85)!important;padding:7px 12px 7px 34px!important;font-size:.84rem!important;width:220px;transition:border-color .15s,box-shadow .15s;}
.al-search-wrap input:focus{border-color:rgba(109,92,255,.45)!important;box-shadow:0 0 0 3px rgba(109,92,255,.10)!important;outline:none!important;}
.al-search-wrap input::placeholder{color:rgba(255,255,255,.25)!important;}
.al-search-icon{position:absolute;left:10px;top:50%;transform:translateY(-50%);color:rgba(255,255,255,.35);font-size:.8rem;pointer-events:none;}
/* ── Status dropdown ── */
.ja-select-wrap{position:relative;}
.ja-select-wrap::after{content:'▾';position:absolute;right:10px;top:50%;transform:translateY(-50%);color:rgba(255,255,255,.35);font-size:.75rem;pointer-events:none;}
.ja-status-select{background:rgba(255,255,255,.04)!important;border:1px solid rgba(255,255,255,.09)!important;border-radius:10px!important;color:rgba(255,255,255,.8)!important;padding:7px 30px 7px 12px!important;font-size:.84rem!important;font-weight:700;appearance:none;cursor:pointer;transition:border-color .15s;}
.ja-status-select:focus{border-color:rgba(109,92,255,.45)!important;box-shadow:0 0 0 3px rgba(109,92,255,.10)!important;outline:none!important;}
.ja-status-select option{background:#25282a;}
/* ── Table ── */
.al-table-wrap{border:1px solid rgba(255,255,255,.07);border-radius:20px;overflow:visible;background:#25282a;box-shadow:0 4px 32px rgba(0,0,0,.28);}
.al-table{width:100%;border-collapse:collapse;display:table;}
.al-table thead tr{background:rgba(255,255,255,.03);border-bottom:1px solid rgba(255,255,255,.06);}
.al-table thead th{padding:11px 16px;font-size:.68rem;font-weight:900;color:rgba(255,255,255,.35);text-transform:uppercase;letter-spacing:.07em;white-space:nowrap;}
.al-table tbody .al-row{border-bottom:1px solid rgba(255,255,255,.04);transition:background .12s;}
.al-table tbody .al-row:last-child{border-bottom:none;}
.al-table tbody .al-row:hover{background:rgba(109,92,255,.06);}
.al-table tbody td{padding:12px 16px;vertical-align:middle;font-size:.85rem;color:rgba(255,255,255,.8);}
/* ── Col styles ── */
.ja-col-id{font-size:.72rem;font-weight:800;color:rgba(255,255,255,.25);}
.ja-applicant-name{font-size:.88rem;font-weight:800;color:rgba(255,255,255,.9);}
.ja-applicant-email{font-size:.74rem;color:rgba(255,255,255,.35);margin-top:1px;}
.ja-col-muted{font-size:.8rem;color:rgba(255,255,255,.45);}
.ja-col-date{font-size:.78rem;color:rgba(255,255,255,.38);}
/* ── Role badge ── */
.ja-role-badge{display:inline-flex;align-items:center;gap:.35rem;padding:4px 10px;border-radius:99px;font-size:.72rem;font-weight:800;white-space:nowrap;}
/* ── Status badges ── */
.ja-badge{display:inline-flex;align-items:center;gap:.3rem;padding:4px 10px;border-radius:99px;font-size:.71rem;font-weight:800;white-space:nowrap;}
.ja-badge--pending{background:rgba(250,204,21,.12);border:1px solid rgba(250,204,21,.30);color:#facc15;}
.ja-badge--trial{background:rgba(99,102,241,.14);border:1px solid rgba(99,102,241,.35);color:#a5b4fc;}
.ja-badge--accepted{background:rgba(74,222,128,.12);border:1px solid rgba(74,222,128,.28);color:#4ade80;}
.ja-badge--declined{background:rgba(251,113,133,.12);border:1px solid rgba(251,113,133,.28);color:#fb7185;}
/* ── Peak rank ── */
.ja-rank{display:inline-flex;align-items:center;padding:3px 9px;border-radius:20px;font-size:.72rem;font-weight:800;background:rgba(255,255,255,.06);border:1px solid rgba(255,255,255,.10);color:rgba(255,255,255,.55);}
/* ── Action buttons ── */
.ja-actions{display:flex;align-items:center;gap:6px;justify-content:flex-end;}
.ja-btn{display:inline-flex;align-items:center;gap:.3rem;padding:6px 12px;border-radius:9px;font-size:.76rem;font-weight:800;border:none;cursor:pointer;transition:opacity .13s,transform .12s;white-space:nowrap;font-family:inherit;}
.ja-btn:hover{opacity:.85;transform:translateY(-1px);}
.ja-btn:active{transform:scale(.97);}
.ja-btn--view{background:rgba(255,255,255,.07);border:1px solid rgba(255,255,255,.10);color:rgba(255,255,255,.65);}
.ja-btn--trial{background:rgba(99,102,241,.2);border:1px solid rgba(99,102,241,.40);color:#a5b4fc;}
.ja-btn--accept{background:rgba(74,222,128,.15);border:1px solid rgba(74,222,128,.35);color:#4ade80;}
.ja-btn--decline{background:rgba(251,113,133,.13);border:1px solid rgba(251,113,133,.30);color:#fb7185;}
.ja-btn--discord{background:rgba(88,101,242,.2);border:1px solid rgba(88,101,242,.40);color:#a5b4fc;}
.ja-btn.loading{opacity:.55;pointer-events:none;}
/* ── Row update animation ── */
@keyframes ja-pulse{0%{opacity:1}40%{opacity:.45}100%{opacity:1}}
.ja-row-updating td{animation:ja-pulse .45s ease;}
/* ── Empty ── */
.al-empty{text-align:center;padding:64px 24px;color:rgba(255,255,255,.35);}
.al-empty i{font-size:3rem;margin-bottom:12px;display:block;opacity:.3;}
/* ── Footer ── */
.al-footer{display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:10px;padding:14px 0 0;}
.ja-pagination{display:flex;align-items:center;gap:6px;flex-wrap:wrap;}
.ja-page-btn{min-width:34px;height:34px;padding:0 10px;border-radius:10px;border:1px solid rgba(255,255,255,.09);background:rgba(255,255,255,.04);color:rgba(255,255,255,.62);font-size:.78rem;font-weight:900;display:inline-flex;align-items:center;justify-content:center;cursor:pointer;transition:all .12s;font-family:inherit;}
.ja-page-btn:hover:not(:disabled){background:rgba(109,92,255,.13);border-color:rgba(109,92,255,.35);color:#c4b5fd;transform:translateY(-1px);}
.ja-page-btn.active{background:rgba(109,92,255,.18);border-color:rgba(109,92,255,.45);color:#c4b5fd;}
.ja-page-btn:disabled{opacity:.35;cursor:not-allowed;}
.ja-page-ellipsis{padding:0 5px;color:rgba(255,255,255,.28);font-weight:900;}
.ja-per-page{background:rgba(255,255,255,.04)!important;border:1px solid rgba(255,255,255,.09)!important;border-radius:10px!important;color:rgba(255,255,255,.72)!important;padding:7px 10px!important;font-size:.78rem!important;font-weight:800;outline:none;}
.ja-per-page option{background:#25282a;color:#fff;}
/* ═══ MODAL ═══ */
.ja-modal .modal-content{background:#25282a;border:1px solid rgba(255,255,255,.08);border-radius:20px;overflow:hidden;}
.ja-modal .modal-header{background:linear-gradient(135deg,rgba(109,92,255,.18),rgba(109,92,255,.04));border-bottom:1px solid rgba(255,255,255,.07);padding:20px 24px 16px;}
.ja-modal .modal-icon{width:42px;height:42px;background:rgba(109,92,255,.18);border-radius:11px;display:flex;align-items:center;justify-content:center;color:#c4b5fd;font-size:1.1rem;flex-shrink:0;}
.ja-modal .modal-title{font-size:1rem;font-weight:800;color:#fff;margin:0;}
.ja-modal .modal-subtitle{font-size:.75rem;color:rgba(255,255,255,.45);margin:3px 0 0;}
.ja-modal .modal-body{padding:22px 24px;}
.ja-modal .modal-footer{padding:14px 24px;border-top:1px solid rgba(255,255,255,.07);background:rgba(0,0,0,.08);display:flex;align-items:center;justify-content:space-between;gap:10px;flex-wrap:wrap;}
/* ── Modal sections ── */
.ja-md-section{margin-bottom:20px;}
.ja-md-section:last-child{margin-bottom:0;}
.ja-md-title{font-size:.68rem;font-weight:900;color:rgba(255,255,255,.3);text-transform:uppercase;letter-spacing:.09em;margin-bottom:12px;padding-bottom:8px;border-bottom:1px solid rgba(255,255,255,.06);}
.ja-md-grid{display:grid;grid-template-columns:repeat(2,1fr);gap:12px;}
.ja-md-grid.cols3{grid-template-columns:repeat(3,1fr);}
.ja-md-grid.cols1{grid-template-columns:1fr;}
.ja-md-label{font-size:.7rem;font-weight:700;color:rgba(255,255,255,.33);text-transform:uppercase;letter-spacing:.06em;margin-bottom:4px;}
.ja-md-value{font-size:.88rem;font-weight:700;color:rgba(255,255,255,.85);word-break:break-word;}
.ja-md-value.long{white-space:pre-wrap;font-weight:500;font-size:.84rem;line-height:1.65;color:rgba(255,255,255,.72);}
/* ── Hint bars ── */
.ja-hint{display:flex;align-items:flex-start;gap:.55rem;padding:10px 14px;border-radius:11px;font-size:.8rem;line-height:1.5;margin-bottom:16px;}
.ja-hint--info{background:rgba(99,102,241,.10);border:1px solid rgba(99,102,241,.22);color:rgba(200,200,255,.82);}
.ja-hint--info i{color:#818cf8;flex-shrink:0;margin-top:.1rem;}
.ja-hint--success{background:rgba(74,222,128,.09);border:1px solid rgba(74,222,128,.22);color:rgba(180,240,200,.8);}
.ja-hint--success i{color:#4ade80;flex-shrink:0;margin-top:.1rem;}
/* ── Discord invite note ── */
.ja-invite-note{display:flex;align-items:center;gap:.5rem;padding:10px 14px;border-radius:11px;background:rgba(114,137,218,.12);border:1px solid rgba(114,137,218,.28);color:#a5b4fc;font-size:.8rem;margin-top:14px;}
.ja-invite-note i{color:#7289da;}
/* ── Discord modal inputs ── */
.ja-modal .form-label{font-size:.78rem;font-weight:700;color:rgba(255,255,255,.55);text-transform:uppercase;letter-spacing:.06em;margin-bottom:5px;}
.ja-modal .form-control{background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.09);border-radius:10px;color:#fff;font-size:.875rem;padding:.6rem 1rem;}
.ja-modal .form-control:focus{border-color:rgba(109,92,255,.5);box-shadow:0 0 0 3px rgba(109,92,255,.15);outline:none;}
.ja-modal .field-group{background:rgba(255,255,255,.02);border:1px solid rgba(255,255,255,.06);border-radius:12px;padding:14px;margin-bottom:10px;}
.ja-modal .field-group:last-child{margin-bottom:0;}
.ja-modal .field-icon{width:28px;height:28px;background:rgba(109,92,255,.12);border-radius:7px;display:flex;align-items:center;justify-content:center;color:#c4b5fd;font-size:.78rem;flex-shrink:0;}
@media only screen and (max-width:1200px){.al-table-wrap{overflow-x:auto;}.al-table{min-width:900px;}}

/* ── Custom Confirm Modal ── */
.ja-confirm-backdrop{position:fixed;inset:0;background:rgba(0,0,0,.65);backdrop-filter:blur(3px);z-index:9998;display:flex;align-items:center;justify-content:center;padding:20px;animation:ja-fade-in .15s ease;pointer-events:all;}
@keyframes ja-fade-in{from{opacity:0}to{opacity:1}}
.ja-confirm-box{background:#25282a;border:1px solid rgba(255,255,255,.10);border-radius:20px;padding:0;width:100%;max-width:420px;overflow:hidden;box-shadow:0 24px 80px rgba(0,0,0,.55);animation:ja-slide-up .18s ease;}
@keyframes ja-slide-up{from{opacity:0;transform:translateY(12px)}to{opacity:1;transform:translateY(0)}}
.ja-confirm-head{padding:20px 22px 14px;display:flex;align-items:center;gap:12px;}
.ja-confirm-icon{width:42px;height:42px;border-radius:11px;display:flex;align-items:center;justify-content:center;font-size:1.1rem;flex-shrink:0;}
.ja-confirm-icon--decline{background:rgba(251,113,133,.14);border:1px solid rgba(251,113,133,.28);color:#fb7185;}
.ja-confirm-icon--trial{background:rgba(99,102,241,.16);border:1px solid rgba(99,102,241,.35);color:#a5b4fc;}
.ja-confirm-icon--accept{background:rgba(74,222,128,.13);border:1px solid rgba(74,222,128,.30);color:#4ade80;}
.ja-confirm-title{font-size:.97rem;font-weight:900;color:#fff;margin:0;}
.ja-confirm-sub{font-size:.78rem;color:rgba(255,255,255,.42);margin:2px 0 0;}
.ja-confirm-body{padding:0 22px 16px;}
.ja-confirm-msg{font-size:.85rem;color:rgba(255,255,255,.68);line-height:1.6;margin-bottom:14px;}
.ja-confirm-note-wrap{display:none;}
.ja-confirm-note-wrap.show{display:block;}
.ja-confirm-note-label{font-size:.7rem;font-weight:700;color:rgba(255,255,255,.35);text-transform:uppercase;letter-spacing:.06em;margin-bottom:6px;}
.ja-confirm-note{width:100%;background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.09);border-radius:10px;color:#fff;font-size:.84rem;padding:.6rem 1rem;resize:vertical;min-height:70px;font-family:inherit;transition:border-color .15s;}
.ja-confirm-note:focus{border-color:rgba(251,113,133,.5);box-shadow:0 0 0 3px rgba(251,113,133,.10);outline:none;}
.ja-confirm-foot{padding:14px 22px;border-top:1px solid rgba(255,255,255,.07);background:rgba(0,0,0,.08);display:flex;align-items:center;justify-content:flex-end;gap:8px;}

/* ── Polling indicator ── */
.ja-live-dot{display:inline-flex;align-items:center;gap:5px;font-size:.72rem;color:rgba(255,255,255,.3);font-weight:700;}
.ja-live-dot::before{content:'';width:6px;height:6px;border-radius:50%;background:rgba(74,222,128,.5);display:inline-block;animation:ja-blink 2s infinite;}
@keyframes ja-blink{0%,100%{opacity:.4}50%{opacity:1}}
</style>
<?= $this->end() ?>


<!-- ── Stats ── -->
<div class="ja-stats">
    <div class="ja-stat">
        <div class="ja-stat-icon" style="background:rgba(250,204,21,.12);border:1px solid rgba(250,204,21,.2);color:#facc15;"><i class="fa-duotone fa-clock"></i></div>
        <div><div class="ja-stat-label">Pending</div><div class="ja-stat-value" id="statPending"><?= count($pending) ?></div></div>
    </div>
    <div class="ja-stat">
        <div class="ja-stat-icon" style="background:rgba(99,102,241,.14);border:1px solid rgba(99,102,241,.22);color:#a5b4fc;"><i class="fa-duotone fa-flask"></i></div>
        <div><div class="ja-stat-label">Trial</div><div class="ja-stat-value" id="statTrial"><?= count($trial) ?></div></div>
    </div>
    <div class="ja-stat">
        <div class="ja-stat-icon" style="background:rgba(74,222,128,.12);border:1px solid rgba(74,222,128,.2);color:#4ade80;"><i class="fa-duotone fa-circle-check"></i></div>
        <div><div class="ja-stat-label">Accepted</div><div class="ja-stat-value" id="statAccepted"><?= count($accepted) ?></div></div>
    </div>
    <div class="ja-stat">
        <div class="ja-stat-icon" style="background:rgba(251,113,133,.12);border:1px solid rgba(251,113,133,.2);color:#fb7185;"><i class="fa-duotone fa-xmark-circle"></i></div>
        <div><div class="ja-stat-label">Declined</div><div class="ja-stat-value" id="statDeclined"><?= count($declined) ?></div></div>
    </div>
    <div class="ja-stat">
        <div class="ja-stat-icon" style="background:rgba(109,92,255,.14);border:1px solid rgba(109,92,255,.22);color:#c4b5fd;"><i class="fa-duotone fa-file-user"></i></div>
        <div><div class="ja-stat-label">Total</div><div class="ja-stat-value"><?= count($all) ?></div></div>
    </div>
</div>

<!-- ── Hero ── -->
<div class="al-hero">
    <div class="al-hero-icon"><i class="fa-duotone fa-file-user"></i></div>
    <div>
        <h2 class="al-hero-title">Job Applications</h2>
        <p class="al-hero-sub"><?= count($all) ?> application<?= count($all) !== 1 ? 's' : '' ?> total</p>
    </div>
</div>

<!-- ── Toolbar ── -->
<div class="al-toolbar-card">
    <div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap;">
        <div class="al-pills" id="jaRoleFilters">
            <span class="al-pill active" data-filter="all"><i class="fa-duotone fa-layer-group" style="font-size:.75rem;"></i> All</span>

            <!-- Games dropdown -->
            <div class="ja-game-dd" id="jaGameDropdown">
                <button type="button" class="al-pill ja-game-dd-btn" id="jaGameBtn">
                    <span class="ja-game-dd-label"><i class="fa-duotone fa-gamepad" style="font-size:.72rem;"></i> Games</span>
                    <i class="fa-solid fa-chevron-down" style="font-size:.6rem;opacity:.6;margin-left:2px;"></i>
                </button>
                <div class="ja-game-dd-menu" id="jaGameMenu">
                    <?php foreach ($gameDropdownItems as $gi): ?>
                    <button type="button" class="ja-game-opt" data-filter="<?= $h($gi['filter']) ?>" data-label="<?= $h($gi['label']) ?>" data-img="<?= $h($gi['img'] ?? '') ?>">
                        <?php if (!empty($gi['img'])): ?>
                            <img src="<?= $gi['img'] ?>" alt="" style="width:15px;height:15px;object-fit:contain;">
                        <?php else: ?>
                            <i class="fa-duotone fa-gamepad" style="font-size:.72rem;width:15px;text-align:center;"></i>
                        <?php endif; ?>
                        <span><?= $h($gi['label']) ?></span>
                    </button>
                    <?php endforeach; ?>
                </div>
            </div>

            <span class="al-pill" data-filter="gg_girl"><img src="<?= ASSET_URL ?>/website/images/gg-girl.svg" style="width:12px;height:12px;object-fit:contain;"> GG-Girl</span>
            <span class="al-pill" data-filter="seller"><i class="fa-duotone fa-store" style="font-size:.7rem;"></i> Seller</span>
        </div>
        <div class="ja-select-wrap">
            <select id="jaStatusFilter" class="ja-status-select">
                <option value="all">All Status</option>
                <option value="pending" selected>Pending</option>
                <option value="trial">Trial</option>
                <option value="accepted">Accepted</option>
                <option value="declined">Declined</option>
            </select>
        </div>
        <div class="al-pills" id="jaStatusFilters" style="padding-left:10px;border-left:1px solid rgba(255,255,255,.07);">
            <span class="al-pill" data-status="all">All</span>
            <span class="al-pill active" data-status="pending"><i class="fa-duotone fa-clock" style="font-size:.65rem;"></i> Pending</span>
            <span class="al-pill" data-status="trial"><i class="fa-duotone fa-flask" style="font-size:.65rem;"></i> Trial</span>
            <span class="al-pill" data-status="accepted"><i class="fa-solid fa-circle-check" style="font-size:.6rem;"></i> Accepted</span>
            <span class="al-pill" data-status="declined"><i class="fa-solid fa-xmark" style="font-size:.65rem;"></i> Declined</span>
        </div>
    </div>
    <div class="al-search-wrap">
        <i class="fa-solid fa-magnifying-glass al-search-icon"></i>
        <input type="search" id="jaSearch" placeholder="Search applications…">
    </div>
</div>

<!-- ── Table ── -->
<div class="al-table-wrap">
    <table class="al-table">
        <thead>
            <tr>
                <th style="width:48px;">#</th>
                <th>Applicant</th>
                <th>Role</th>
                <th>Discord</th>
                <th>Country</th>
                <th>Peak Rank</th>
                <th>Applied</th>
                <th>Status</th>
                <th class="text-end" style="min-width:210px;">Actions</th>
            </tr>
        </thead>
        <tbody id="jaTbody">
            <?php if (empty($all)): ?>
                <tr><td colspan="9">
                    <div class="al-empty">
                        <i class="fa-duotone fa-inbox"></i>
                        <div style="font-weight:900;font-size:1rem;color:rgba(255,255,255,.6);margin-bottom:6px;">No applications yet</div>
                    </div>
                </td></tr>
            <?php else: ?>
                <?php foreach ($all as $row):
                    $roleKey   = $row['role'] ?? 'lol_booster';
                    // Legacy/broken rows saved role='' when the old ENUM rejected the
                    // generic 'booster' value — treat a blank role as generic booster
                    // so it still renders, filters, and shows the trial buttons.
                    if ($roleKey === '') $roleKey = 'booster';
                    $rm        = $roleLabels[$roleKey] ?? ['label' => ucfirst($roleKey), 'color' => '#aaa', 'bg' => 'rgba(255,255,255,.07)', 'border' => 'rgba(255,255,255,.12)', 'img' => null];
                    $status    = $row['status'] ?? 'pending';
                    $isBooster = in_array($roleKey, ['lol_booster', 'tft_booster', 'val_booster', 'booster'], true);
                    // Generic booster badge shows the actual game the applicant picked
                    // (label is escaped once at output below, so keep it raw here).
                    if ($roleKey === 'booster') {
                        $rm['label'] = !empty($row['boost_game']) ? ($row['boost_game'] . ' Booster') : 'Booster';
                        // Show the actual game icon instead of the generic store icon.
                        $rowGameSlug = jaGameSlug((string)($row['boost_game'] ?? ''));
                        if (isset($gameFilters[$rowGameSlug]['img'])) {
                            $rm['img'] = $gameFilters[$rowGameSlug]['img'];
                        }
                    }
                    $appliedFmt = isset($row['created_at']) ? date('d.m.Y H:i', strtotime($row['created_at'])) : '—';
                ?>
                <tr class="al-row"
                    id="app-row-<?= (int)$row['id'] ?>"
                    data-role="<?= $h($roleKey) ?>"
                    data-game="<?= $h(jaGameSlug((string)($row['boost_game'] ?? ''))) ?>"
                    data-status="<?= $h($status) ?>"
                    data-search="<?= $h(strtolower(($row['fullname'] ?? '') . ' ' . ($row['email'] ?? '') . ' ' . ($row['discord_tag'] ?? ''))) ?>">
                    <td><span class="ja-col-id">#<?= (int)$row['id'] ?></span></td>
                    <td>
                        <div class="ja-applicant-name"><?= $h($row['fullname'] ?? '—') ?></div>
                        <div class="ja-applicant-email"><?= $h($row['email'] ?? '') ?></div>
                    </td>
                    <td>
                        <span class="ja-role-badge" style="background:<?= $rm['bg'] ?>;border:1px solid <?= $rm['border'] ?>;color:<?= $rm['color'] ?>;">
                            <?php if (!empty($rm['img'])): ?>
                                <img src="<?= $rm['img'] ?>" alt="" style="width:13px;height:13px;object-fit:contain;flex-shrink:0;">
                            <?php else: ?>
                                <i class="fa-duotone fa-store" style="font-size:.65rem;"></i>
                            <?php endif; ?>
                            <?= $h($rm['label']) ?>
                        </span>
                    </td>
                    <td><span class="ja-col-muted"><?= $h($row['discord_tag'] ?? '—') ?></span></td>
                    <td><span class="ja-col-muted"><?= $h($row['country'] ?? '—') ?></span></td>
                    <td>
                        <?php if ($isBooster && !empty($row['peak_rank'])): ?>
                            <span class="ja-rank"><?= $h($row['peak_rank']) ?></span>
                        <?php else: ?>
                            <span style="color:rgba(255,255,255,.2);">—</span>
                        <?php endif; ?>
                    </td>
                    <td><span class="ja-col-date"><?= $h($appliedFmt) ?></span></td>
                    <td class="ja-status-cell"><?= jaStatusBadge($status) ?></td>
                    <td>
                        <div class="ja-actions ja-action-cell">
                            <?= jaViewBtn($row, $roleKey, $status) ?>
                            <?= jaActionButtons((int)$row['id'], $roleKey, $status, $h($row['discord_tag'] ?? ''), $isBooster) ?>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<!-- ── Footer ── -->
<div class="al-footer">
    <div style="display:flex;align-items:center;gap:14px;flex-wrap:wrap;">
        <div style="font-size:.82rem;color:rgba(255,255,255,.4);">Showing <strong id="jaVisibleCount">0</strong> entries</div>
        <div style="font-size:.82rem;color:rgba(255,255,255,.28);" id="jaPageInfo">Page 1 of 1</div>
        <div class="ja-live-dot">Live</div>
    </div>
    <div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap;">
        <select id="jaPerPage" class="ja-per-page" aria-label="Applications per page">
            <option value="10">10 / page</option>
            <option value="25" selected>25 / page</option>
            <option value="50">50 / page</option>
            <option value="100">100 / page</option>
        </select>
        <div class="ja-pagination" id="jaPagination"></div>
    </div>
</div>


<!-- ════════ DETAIL MODAL ════════ -->
<div class="modal fade ja-modal" id="appDetailModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable" style="max-width:680px;">
        <div class="modal-content">
            <div class="modal-header">
                <div style="display:flex;align-items:center;gap:12px;">
                    <div class="modal-icon"><i class="fa-duotone fa-file-user"></i></div>
                    <div>
                        <div class="modal-title">Application — <span id="mdFullname"></span></div>
                        <div class="modal-subtitle" id="mdRoleBadge"></div>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">

                <div id="mdFlowHint" class="ja-hint ja-hint--info" style="display:none;"></div>

                <!-- Contact -->
                <div class="ja-md-section">
                    <div class="ja-md-title">Contact Information</div>
                    <div class="ja-md-grid">
                        <div><div class="ja-md-label">Email</div><div class="ja-md-value" id="mdEmail">—</div></div>
                        <div><div class="ja-md-label">Discord Tag</div><div class="ja-md-value" id="mdDiscord">—</div></div>
                        <div><div class="ja-md-label">Country</div><div class="ja-md-value" id="mdCountry">—</div></div>
                        <div><div class="ja-md-label">Age</div><div class="ja-md-value" id="mdAge">—</div></div>
                    </div>
                    <div style="margin-top:12px;"><div class="ja-md-label">Languages</div><div class="ja-md-value" id="mdLanguages">—</div></div>
                </div>

                <!-- Details -->
                <div class="ja-md-section" id="mdDetailsSection">
                    <div class="ja-md-title" id="mdDetailsTitle">Game Details</div>
                    <div class="ja-md-grid cols3">
                        <div><div class="ja-md-label" id="mdLabelIngame">In-Game Name</div><div class="ja-md-value" id="mdIngame">—</div></div>
                        <div id="mdWrapRegion"><div class="ja-md-label" id="mdLabelRegion">Region</div><div class="ja-md-value" id="mdRegion">—</div></div>
                        <div><div class="ja-md-label" id="mdLabelCurrentRank">Current Rank</div><div class="ja-md-value" id="mdCurrentRank">—</div></div>
                    </div>
                    <div class="ja-md-grid" style="margin-top:12px;">
                        <div><div class="ja-md-label" id="mdLabelPeakRank">Peak Rank</div><div class="ja-md-value" id="mdPeakRank">—</div></div>
                        <div><div class="ja-md-label" id="mdLabelChampPool">Champ Pool / Roles</div><div class="ja-md-value" id="mdChampPool">—</div></div>
                    </div>
                </div>

                <!-- Application -->
                <div class="ja-md-section">
                    <div class="ja-md-title">Application Details</div>
                    <div class="ja-md-grid cols1" style="gap:14px;">
                        <div><div class="ja-md-label" id="mdLabelAvailability">Availability</div><div class="ja-md-value" id="mdAvailability">—</div></div>
                        <div><div class="ja-md-label" id="mdLabelExperience">Experience</div><div class="ja-md-value long" id="mdExperience">—</div></div>
                        <div><div class="ja-md-label" id="mdLabelMotivation">Motivation</div><div class="ja-md-value long" id="mdMotivation">—</div></div>
                        <div><div class="ja-md-label" id="mdLabelReferral">How heard about us</div><div class="ja-md-value" id="mdReferral">—</div></div>
                    </div>
                </div>

                <div class="ja-invite-note" id="mdInviteSentNote" style="display:none;">
                    <i class="fa-brands fa-discord"></i>
                    Discord invite sent: <strong id="mdExistingInvite" style="margin-left:4px;"></strong>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="ja-btn ja-btn--view" data-bs-dismiss="modal">Close</button>
                <div style="display:flex;gap:8px;flex-wrap:wrap;">
                    <button type="button" class="ja-btn ja-btn--trial" id="mdAcceptBtn" style="display:none;"><i class="fa-duotone fa-flask"></i> Move To Trial</button>
                    <button type="button" class="ja-btn ja-btn--discord" id="mdInviteBtn" style="display:none;"><i class="fa-brands fa-discord"></i> Discord Invite</button>
                    <button type="button" class="ja-btn ja-btn--decline" id="mdDeclineBtn" style="display:none;"><i class="fa-duotone fa-xmark"></i> Decline</button>
                </div>
            </div>
        </div>
    </div>
</div>


<!-- ════════ DISCORD INVITE MODAL ════════ -->
<div class="modal fade ja-modal" id="discordInviteModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width:460px;">
        <div class="modal-content">
            <div class="modal-header">
                <div style="display:flex;align-items:center;gap:12px;">
                    <div class="modal-icon" style="background:rgba(114,137,218,.18);"><i class="fa-brands fa-discord" style="color:#7289da;font-size:1.1rem;"></i></div>
                    <div>
                        <div class="modal-title" id="diModalTitle">Send Discord Invite</div>
                        <div class="modal-subtitle">To: <span id="diDiscordTag" style="color:rgba(255,255,255,.7);">—</span></div>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="field-group">
                    <div style="display:flex;align-items:center;gap:8px;margin-bottom:8px;">
                        <div class="field-icon"><i class="fa-brands fa-discord" style="color:#7289da;"></i></div>
                        <label class="form-label mb-0">Invite Link <span style="color:#f87171;">*</span></label>
                    </div>
                    <input type="text" class="form-control" id="diInviteLink" placeholder="https://discord.gg/yourcode">
                    <div style="margin-top:5px;font-size:.72rem;color:rgba(255,255,255,.3);">Paste your Discord server invite link.</div>
                </div>
                <div class="field-group">
                    <div style="display:flex;align-items:center;gap:8px;margin-bottom:8px;">
                        <div class="field-icon"><i class="fa-duotone fa-note-sticky"></i></div>
                        <label class="form-label mb-0">Optional Note</label>
                    </div>
                    <textarea class="form-control" id="diNote" rows="3" placeholder="Welcome! Join and introduce yourself…"></textarea>
                </div>
            </div>
            <div class="modal-footer" style="justify-content:space-between;">
                <button type="button" class="ja-btn ja-btn--view" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="ja-btn ja-btn--discord" id="diSendBtn"><i class="fa-brands fa-discord"></i> Send Invite</button>
            </div>
        </div>
    </div>
</div>


<!-- ════════ CUSTOM CONFIRM MODALS ════════ -->
<!-- Decline confirm -->
<div id="jaConfirmDecline" style="display:none;" class="ja-confirm-backdrop" role="dialog" aria-modal="true">
    <div class="ja-confirm-box">
        <div class="ja-confirm-head">
            <div class="ja-confirm-icon ja-confirm-icon--decline"><i class="fa-duotone fa-xmark-circle"></i></div>
            <div>
                <div class="ja-confirm-title">Decline Application</div>
                <div class="ja-confirm-sub" id="jaCdName">—</div>
            </div>
        </div>
        <div class="ja-confirm-body">
            <div class="ja-confirm-msg">Are you sure you want to decline this application? An email will be sent to the applicant.</div>
            <div class="ja-confirm-note-wrap show">
                <div class="ja-confirm-note-label">Optional note to applicant</div>
                <textarea class="ja-confirm-note" id="jaCdNote" placeholder="e.g. Feel free to reapply in a few months once you've reached a higher rank…"></textarea>
            </div>
        </div>
        <div class="ja-confirm-foot">
            <button type="button" class="ja-btn ja-btn--view" id="jaCdCancel">Cancel</button>
            <button type="button" class="ja-btn ja-btn--decline" id="jaCdConfirm"><i class="fa-duotone fa-xmark"></i> Decline & Notify</button>
        </div>
    </div>
</div>

<!-- Trial confirm -->
<div id="jaConfirmTrial" style="display:none;" class="ja-confirm-backdrop" role="dialog" aria-modal="true">
    <div class="ja-confirm-box">
        <div class="ja-confirm-head">
            <div class="ja-confirm-icon ja-confirm-icon--trial"><i class="fa-duotone fa-flask"></i></div>
            <div>
                <div class="ja-confirm-title">Move to Trial</div>
                <div class="ja-confirm-sub" id="jaCtName">—</div>
            </div>
        </div>
        <div class="ja-confirm-body">
            <div class="ja-confirm-msg">Move this applicant to the trial phase. They'll receive a Discord invite link to join the trial server.</div>
        </div>
        <div class="ja-confirm-foot">
            <button type="button" class="ja-btn ja-btn--view" id="jaCtCancel">Cancel</button>
            <button type="button" class="ja-btn ja-btn--trial" id="jaCtConfirm"><i class="fa-duotone fa-flask"></i> Move to Trial</button>
        </div>
    </div>
</div>

<!-- Accept confirm -->
<div id="jaConfirmAccept" style="display:none;" class="ja-confirm-backdrop" role="dialog" aria-modal="true">
    <div class="ja-confirm-box">
        <div class="ja-confirm-head">
            <div class="ja-confirm-icon ja-confirm-icon--accept"><i class="fa-solid fa-circle-check"></i></div>
            <div>
                <div class="ja-confirm-title" id="jaAcTitle">Accept Application</div>
                <div class="ja-confirm-sub" id="jaAcName">—</div>
            </div>
        </div>
        <div class="ja-confirm-body">
            <div class="ja-confirm-msg" id="jaAcMsg">Accept this application and move the applicant to the team.</div>
            <div class="ja-confirm-note-wrap" id="jaAcInviteWrap" style="display:none;">
                <div class="ja-confirm-note-label">Discord invite link (sent together with the onboarding link)</div>
                <input type="text" class="ja-confirm-note" id="jaAcInvite" placeholder="https://discord.gg/yourcode" style="min-height:auto;">
            </div>
        </div>
        <div class="ja-confirm-foot">
            <button type="button" class="ja-btn ja-btn--view" id="jaAcCancel">Cancel</button>
            <button type="button" class="ja-btn ja-btn--accept" id="jaAcConfirm"><i class="fa-duotone fa-check"></i> Accept</button>
        </div>
    </div>
</div>


<?= $this->start('scripts') ?>
<script>
(function () {
    var AJAX_URL   = '<?= AJAX_URL ?>';
    var ASSET_URL  = '<?= ASSET_URL ?>';
    var jaStorageKey = 'lolboost_admin_job_applications_filters_v1';
    var activeRole = 'all';
    var activeStatus = 'pending';
    var currentPage = 1;
    var perPage = 25;

    function loadSavedFilters() {
        try {
            var saved = JSON.parse(localStorage.getItem(jaStorageKey) || '{}');
            var validRoles = ['all', 'lol_booster', 'tft_booster', 'val_booster', 'booster', 'gg_girl', 'seller'];
            var validStatuses = ['all', 'pending', 'trial', 'accepted', 'declined'];

            // Accept per-game filters ("game:<slug>") saved from the game pills too.
            if (validRoles.indexOf(saved.role) !== -1 || (typeof saved.role === 'string' && saved.role.indexOf('game:') === 0)) activeRole = saved.role;
            if (validStatuses.indexOf(saved.status) !== -1) activeStatus = saved.status;
            if (saved.search !== undefined && document.getElementById('jaSearch')) {
                document.getElementById('jaSearch').value = String(saved.search || '');
            }
            if (saved.perPage !== undefined && document.getElementById('jaPerPage')) {
                var pp = parseInt(saved.perPage, 10);
                if ([10, 25, 50, 100].indexOf(pp) !== -1) {
                    perPage = pp;
                    document.getElementById('jaPerPage').value = String(pp);
                }
            }
        } catch (e) {}
    }

    function saveFilters() {
        try {
            var searchEl = document.getElementById('jaSearch');
            localStorage.setItem(jaStorageKey, JSON.stringify({
                role: activeRole,
                status: activeStatus,
                search: searchEl ? searchEl.value : '',
                perPage: perPage
            }));
        } catch (e) {}
    }

    // Games dropdown option map (filter value -> {label, img})
    var GAME_FILTERS = {};
    document.querySelectorAll('#jaGameMenu .ja-game-opt').forEach(function(o) {
        GAME_FILTERS[o.getAttribute('data-filter')] = {
            label: o.getAttribute('data-label') || '',
            img: o.getAttribute('data-img') || ''
        };
    });
    function isGameFilter(v) { return Object.prototype.hasOwnProperty.call(GAME_FILTERS, v); }

    function updateGameDropdown() {
        var dd = document.getElementById('jaGameDropdown');
        var btn = document.getElementById('jaGameBtn');
        var labelEl = btn ? btn.querySelector('.ja-game-dd-label') : null;
        var isGame = isGameFilter(activeRole);
        if (dd) dd.classList.toggle('active', isGame);
        if (labelEl) {
            if (isGame) {
                var g = GAME_FILTERS[activeRole];
                labelEl.innerHTML = (g.img ? '<img src="' + g.img + '" alt="">' : '<i class="fa-duotone fa-gamepad" style="font-size:.72rem;"></i>') + ' ' + g.label;
            } else {
                labelEl.innerHTML = '<i class="fa-duotone fa-gamepad" style="font-size:.72rem;"></i> Games';
            }
        }
        document.querySelectorAll('#jaGameMenu .ja-game-opt').forEach(function(o) {
            o.classList.toggle('active', o.getAttribute('data-filter') === activeRole);
        });
    }

    function syncFilterUi() {
        document.querySelectorAll('#jaRoleFilters .al-pill').forEach(function(p) {
            if (p.classList.contains('ja-game-dd-btn')) return; // dropdown handled separately
            p.classList.toggle('active', (p.getAttribute('data-filter') || 'all') === activeRole);
        });
        updateGameDropdown();

        document.querySelectorAll('#jaStatusFilters .al-pill').forEach(function(p) {
            p.classList.toggle('active', (p.getAttribute('data-status') || 'all') === activeStatus);
        });

        var statusSelect = document.getElementById('jaStatusFilter');
        if (statusSelect) statusSelect.value = activeStatus;
    }
    var modalCurrentId   = null;
    var discordCurrentId = null;
    var discordCurrentTrialInvite = false;
    var discordCurrentDiscord = '';
    var boosterRoles = ['lol_booster', 'tft_booster', 'val_booster', 'booster'];
    var suppressActionsUntil = 0;

    var roleMeta = {
        lol_booster: {label:'LoL Booster', color:'#c89b3c', bg:'rgba(200,155,60,.12)', border:'rgba(200,155,60,.28)', img: ASSET_URL + '/website/images/icons/league-of-legends.png'},
        tft_booster: {label:'TFT Booster', color:'#0bc4e3', bg:'rgba(11,196,227,.12)',  border:'rgba(11,196,227,.28)',  img: ASSET_URL + '/website/images/icons/teamfight-tactics.png'},
        val_booster: {label:'VAL Booster', color:'#ff4655', bg:'rgba(255,70,85,.12)',   border:'rgba(255,70,85,.28)',   img: ASSET_URL + '/website/images/icons/valorant.png'},
        booster:     {label:'Booster',     color:'#a78bfa', bg:'rgba(167,139,250,.12)', border:'rgba(167,139,250,.28)', img: null},
        gg_girl:     {label:'GG-Girl',     color:'#e040fb', bg:'rgba(224,64,251,.12)',  border:'rgba(224,64,251,.28)',  img: ASSET_URL + '/website/images/gg-girl.svg'},
        seller:      {label:'Seller',       color:'#4ade80', bg:'rgba(74,222,128,.12)', border:'rgba(74,222,128,.28)', img: null}
    };

    function eatEvent(e) {
        if (!e) return;
        e.preventDefault();
        e.stopPropagation();
        if (typeof e.stopImmediatePropagation === 'function') {
            e.stopImmediatePropagation();
        }
    }

    function isBooster(role) { return boosterRoles.indexOf(role) !== -1; }

    /* ── Status badge HTML ── */
    function statusBadgeHtml(s) {
        var m = {
            pending:  '<span class="ja-badge ja-badge--pending"><i class="fa-duotone fa-clock" style="font-size:.55rem;"></i> Pending</span>',
            trial:    '<span class="ja-badge ja-badge--trial"><i class="fa-duotone fa-flask" style="font-size:.55rem;"></i> Trial</span>',
            accepted: '<span class="ja-badge ja-badge--accepted"><i class="fa-solid fa-circle-check" style="font-size:.55rem;"></i> Accepted</span>',
            declined: '<span class="ja-badge ja-badge--declined"><i class="fa-solid fa-xmark" style="font-size:.6rem;"></i> Declined</span>'
        };
        return m[s] || m.pending;
    }

    /* ── Action buttons HTML ── */
    function actionBtnsHtml(id, role, status, discord) {
        var d = (discord || '').replace(/"/g, '&quot;');
        var h = '';
        if (status === 'pending') {
            if (isBooster(role))
                h += '<button type="button" class="ja-btn ja-btn--discord discord-invite-btn" data-trial-invite="1" data-id="'+id+'" data-discord="'+d+'" data-role="'+role+'" title="Send Trial Invite"><i class="fa-brands fa-discord"></i> Send Trial Invite</button>';
            else
                h += '<button type="button" class="ja-btn ja-btn--accept ja-action-btn" data-action="accept_job_application" data-id="'+id+'" data-role="'+role+'" title="Accept"><i class="fa-duotone fa-check"></i> Accept</button>';
            h += '<button type="button" class="ja-btn ja-btn--decline ja-action-btn" data-action="decline_job_application" data-id="'+id+'" data-role="'+role+'" title="Decline"><i class="fa-duotone fa-xmark"></i></button>';
        } else if (status === 'trial' && isBooster(role)) {
            h += '<button type="button" class="ja-btn ja-btn--accept ja-action-btn" data-action="accept_job_application" data-id="'+id+'" data-role="'+role+'" title="Final Accept"><i class="fa-duotone fa-check"></i> Accept</button>';
            h += '<button type="button" class="ja-btn ja-btn--discord discord-invite-btn" data-id="'+id+'" data-discord="'+d+'" data-role="'+role+'" data-bs-toggle="modal" data-bs-target="#discordInviteModal"><i class="fa-brands fa-discord"></i> Invite</button>';
            h += '<button type="button" class="ja-btn ja-btn--decline ja-action-btn" data-action="decline_job_application" data-id="'+id+'" data-role="'+role+'" title="Decline"><i class="fa-duotone fa-xmark"></i></button>';
        } else if (status === 'accepted') {
            h += '<button type="button" class="ja-btn ja-btn--discord discord-invite-btn" data-id="'+id+'" data-discord="'+d+'" data-role="'+role+'" data-bs-toggle="modal" data-bs-target="#discordInviteModal"><i class="fa-brands fa-discord"></i> Invite</button>';
        }
        return h;
    }

    /* ── Recalc stat counters ── */
    function recalcStats() {
        var c = {pending:0, trial:0, accepted:0, declined:0};
        document.querySelectorAll('#jaTbody tr.al-row').forEach(function(r) {
            var s = r.getAttribute('data-status');
            if (c[s] !== undefined) c[s]++;
        });
        ['pending','trial','accepted','declined'].forEach(function(k) {
            var el = document.getElementById('stat' + k.charAt(0).toUpperCase() + k.slice(1));
            if (el) el.textContent = c[k];
        });
    }

    /* ── Filter + Pagination ── */
    function getFilteredRows() {
        var searchEl = document.getElementById('jaSearch');
        var search = (searchEl ? searchEl.value : '').toLowerCase().trim();
        var knownGames = ['wild-rift','overwatch','apex','fortnite','rocket-league','cs2','marvel-rivals'];
        return Array.prototype.slice.call(document.querySelectorAll('#jaTbody tr.al-row')).filter(function(row) {
            var roleMatch;
            if (activeRole === 'all') {
                roleMatch = true;
            } else if (activeRole.indexOf('game:') === 0) {
                var wantGame = activeRole.slice(5);
                var rowGame = row.getAttribute('data-game') || '';
                var isGenericBooster = (row.getAttribute('data-role') === 'booster');
                if (wantGame === 'other') {
                    roleMatch = isGenericBooster && knownGames.indexOf(rowGame) === -1;
                } else {
                    roleMatch = isGenericBooster && rowGame === wantGame;
                }
            } else {
                roleMatch = row.getAttribute('data-role') === activeRole;
            }
            return roleMatch
                && (activeStatus === 'all' || row.getAttribute('data-status') === activeStatus)
                && (!search || (row.getAttribute('data-search') || '').indexOf(search) !== -1);
        });
    }

    function renderPagination(totalRows) {
        var totalPages = Math.max(1, Math.ceil(totalRows / perPage));
        if (currentPage > totalPages) currentPage = totalPages;
        if (currentPage < 1) currentPage = 1;

        var info = document.getElementById('jaPageInfo');
        if (info) info.textContent = 'Page ' + currentPage + ' of ' + totalPages;

        var wrap = document.getElementById('jaPagination');
        if (!wrap) return;
        wrap.innerHTML = '';

        function addBtn(label, page, disabled, active) {
            var btn = document.createElement('button');
            btn.type = 'button';
            btn.className = 'ja-page-btn' + (active ? ' active' : '');
            btn.innerHTML = label;
            btn.disabled = !!disabled;
            btn.addEventListener('click', function() {
                if (disabled || currentPage === page) return;
                currentPage = page;
                applyFilters(false);
            });
            wrap.appendChild(btn);
        }

        function addEllipsis() {
            var span = document.createElement('span');
            span.className = 'ja-page-ellipsis';
            span.textContent = '…';
            wrap.appendChild(span);
        }

        addBtn('<i class="fa-solid fa-chevron-left"></i>', currentPage - 1, currentPage === 1, false);

        var pages = [];
        pages.push(1);
        for (var i = currentPage - 1; i <= currentPage + 1; i++) {
            if (i > 1 && i < totalPages) pages.push(i);
        }
        if (totalPages > 1) pages.push(totalPages);
        pages = pages.filter(function(v, i, a) { return a.indexOf(v) === i; }).sort(function(a, b) { return a - b; });

        pages.forEach(function(page, idx) {
            if (idx > 0 && page - pages[idx - 1] > 1) addEllipsis();
            addBtn(String(page), page, false, page === currentPage);
        });

        addBtn('<i class="fa-solid fa-chevron-right"></i>', currentPage + 1, currentPage === totalPages, false);
    }

    function applyFilters(resetPage) {
        if (resetPage !== false) currentPage = 1;

        var rows = Array.prototype.slice.call(document.querySelectorAll('#jaTbody tr.al-row'));
        var filtered = getFilteredRows();
        var totalRows = filtered.length;
        var totalPages = Math.max(1, Math.ceil(totalRows / perPage));
        if (currentPage > totalPages) currentPage = totalPages;

        var start = (currentPage - 1) * perPage;
        var end = start + perPage;
        var visibleSet = new Set(filtered.slice(start, end));

        rows.forEach(function(row) {
            row.style.display = visibleSet.has(row) ? '' : 'none';
        });

        var vc = document.getElementById('jaVisibleCount');
        if (vc) vc.textContent = totalRows;
        renderPagination(totalRows);
    }

    /* ── Pills (All / GG-Girl / Seller) ── */
    document.querySelectorAll('#jaRoleFilters .al-pill').forEach(function(pill) {
        if (pill.classList.contains('ja-game-dd-btn')) return; // dropdown toggle, not a filter
        pill.addEventListener('click', function() {
            document.querySelectorAll('#jaRoleFilters .al-pill').forEach(function(p) {
                if (!p.classList.contains('ja-game-dd-btn')) p.classList.remove('active');
            });
            pill.classList.add('active');
            activeRole = pill.getAttribute('data-filter') || 'all';
            saveFilters();
            syncFilterUi();
            applyFilters();
        });
    });

    /* ── Games dropdown ── */
    var gameDd = document.getElementById('jaGameDropdown');
    var gameBtn = document.getElementById('jaGameBtn');
    if (gameBtn && gameDd) {
        gameBtn.addEventListener('click', function(e) {
            e.stopPropagation();
            gameDd.classList.toggle('open');
        });
        document.querySelectorAll('#jaGameMenu .ja-game-opt').forEach(function(opt) {
            opt.addEventListener('click', function() {
                activeRole = opt.getAttribute('data-filter') || 'all';
                gameDd.classList.remove('open');
                // Clear the plain pills (All/GG/Seller) since a game is now active.
                document.querySelectorAll('#jaRoleFilters .al-pill').forEach(function(p) {
                    if (!p.classList.contains('ja-game-dd-btn')) p.classList.remove('active');
                });
                saveFilters();
                syncFilterUi();
                applyFilters();
            });
        });
        document.addEventListener('click', function(e) {
            if (!gameDd.contains(e.target)) gameDd.classList.remove('open');
        });
    }

    /* ── Status pills ── */
    document.querySelectorAll('#jaStatusFilters .al-pill').forEach(function(pill) {
        pill.addEventListener('click', function() {
            document.querySelectorAll('#jaStatusFilters .al-pill').forEach(function(p) { p.classList.remove('active'); });
            pill.classList.add('active');
            activeStatus = pill.getAttribute('data-status') || 'all';
            saveFilters();
            applyFilters();
        });
    });

    var statusSelectEl = document.getElementById('jaStatusFilter');
    if (statusSelectEl) {
        statusSelectEl.addEventListener('change', function() {
            activeStatus = statusSelectEl.value || 'all';
            syncFilterUi();
            saveFilters();
            applyFilters();
        });
    }

    /* ── Search ── */
    var searchEl = document.getElementById('jaSearch');
    if (searchEl) searchEl.addEventListener('input', function() { saveFilters(); applyFilters(true); });

    var perPageEl = document.getElementById('jaPerPage');
    if (perPageEl) {
        perPage = parseInt(perPageEl.value, 10) || 25;
        perPageEl.addEventListener('change', function() {
            perPage = parseInt(perPageEl.value, 10) || 25;
            saveFilters();
            applyFilters(true);
        });
    }

    loadSavedFilters();
    syncFilterUi();
    applyFilters(true);

    /* ── AJAX action ── */
    function doAction(action, id, rowEl, note, preStatus, inviteLink) {
        note      = note      || '';
        inviteLink = inviteLink || '';
        preStatus = preStatus || (rowEl ? rowEl.getAttribute('data-status') : 'pending');

        /* Guard: prevent acting on a row that's already been updated this session */
        if (rowEl) {
            var curStatus = rowEl.getAttribute('data-status') || '';
            var isFinalised = (curStatus === 'declined' || curStatus === 'accepted');
            if (isFinalised && action === 'decline_job_application') {
                if (typeof create_toast === 'function') create_toast('warning', 'Already updated', 'This application has already been ' + curStatus + '.');
                return;
            }
        }

        var btns = rowEl ? rowEl.querySelectorAll('.ja-action-btn') : [];
        btns.forEach(function(b) { b.classList.add('loading'); });

        var fd = new FormData();
        fd.append('action', action);
        fd.append('id', id);
        if (note) fd.append('decline_note', note);
        if (inviteLink) fd.append('invite_link', inviteLink);

        fetch(AJAX_URL, {method:'POST', body:fd})
            .then(function(r) { return r.json(); })
            .then(function(res) {
                btns.forEach(function(b) { b.classList.remove('loading'); });
                if (res && res.status === 'success') {
                    var role = rowEl ? rowEl.getAttribute('data-role') : '';

                    /* Use server-provided new_status if available.
                       Otherwise derive from preStatus (the status at click-time), not the current row state. */
                    var newStatus;
                    if (res.new_status) {
                        newStatus = res.new_status;
                    } else if (action === 'decline_job_application') {
                        newStatus = 'declined';
                    } else if (isBooster(role) && preStatus === 'pending') {
                        newStatus = 'trial';
                    } else {
                        newStatus = 'accepted';
                    }

                    if (rowEl) {
                        rowEl.setAttribute('data-status', newStatus);
                        rowEl.classList.add('ja-row-updating');
                        setTimeout(function() { rowEl.classList.remove('ja-row-updating'); }, 500);

                        var sc = rowEl.querySelector('.ja-status-cell');
                        if (sc) sc.innerHTML = statusBadgeHtml(newStatus);

                        var ac = rowEl.querySelector('.ja-action-cell');
                        if (ac) {
                            var vb = ac.querySelector('.view-app-btn');
                            var discord = vb ? (vb.getAttribute('data-discord') || '') : '';
                            var vbHtml  = vb ? vb.outerHTML : '';
                            if (vb) vb.setAttribute('data-status', newStatus);
                            ac.innerHTML = vbHtml + actionBtnsHtml(id, role, newStatus, discord);
                        }
                    }

                    recalcStats();
                    applyFilters(false);
                    if (typeof create_toast === 'function') create_toast('success', 'Done', res.message || 'Updated.');
                    if (res.refresh) setTimeout(function() { location.reload(); }, 600);
                } else {
                    if (typeof create_toast === 'function') create_toast('danger', 'Error', (res && res.message) || 'Something went wrong.');
                }
            })
            .catch(function() {
                btns.forEach(function(b) { b.classList.remove('loading'); });
                if (typeof create_toast === 'function') create_toast('danger', 'Error', 'Request failed.');
            });
    }

    /* ── Custom confirm modals ── */
    var pendingAction = null; // {action, id, rowEl, note}

    function getApplicantName(rowEl) {
        if (!rowEl) return '—';
        var el = rowEl.querySelector('.ja-applicant-name');
        return el ? el.textContent.trim() : '—';
    }

    function showConfirmDecline(id, role, rowEl) {
        pendingAction = null;
        var note = document.getElementById('jaCdNote');
        if (note) note.value = '';
        var nm = document.getElementById('jaCdName');
        if (nm) nm.textContent = getApplicantName(rowEl);
        document.getElementById('jaConfirmDecline').style.display = 'flex';
        setTimeout(function() { if (note) note.focus(); }, 80);
        pendingAction = {action:'decline_job_application', id:id, role:role, rowEl:rowEl, preStatus: rowEl ? rowEl.getAttribute('data-status') : 'pending'};
    }

    function showConfirmTrial(id, role, rowEl) {
        pendingAction = null;
        var nm = document.getElementById('jaCtName');
        if (nm) nm.textContent = getApplicantName(rowEl);
        document.getElementById('jaConfirmTrial').style.display = 'flex';
        pendingAction = {action:'accept_job_application', id:id, role:role, rowEl:rowEl, preStatus:'pending', isTrial:true};
    }

    function showConfirmAccept(id, role, rowEl, isFinal) {
        pendingAction = null;
        var nm  = document.getElementById('jaAcName');
        var ttl = document.getElementById('jaAcTitle');
        var msg = document.getElementById('jaAcMsg');
        if (nm)  nm.textContent  = getApplicantName(rowEl);
        if (ttl) ttl.textContent = isFinal ? 'Final Accept' : 'Accept Application';
        if (msg) msg.textContent = isFinal
            ? 'Mark this booster as fully accepted after their trial period.'
            : 'Accept this application and welcome the applicant to the team.';

        // Seller / GG-Girl get onboarding + Discord invite in ONE step: show the
        // Discord field here so accepting sends both together. Boosters use the
        // separate trial-invite flow, so no field for them.
        var inviteWrap  = document.getElementById('jaAcInviteWrap');
        var inviteInput = document.getElementById('jaAcInvite');
        if (inviteInput) inviteInput.value = '';
        if (inviteWrap)  inviteWrap.style.display = (!isBooster(role)) ? 'block' : 'none';

        document.getElementById('jaConfirmAccept').style.display = 'flex';
        pendingAction = {action:'accept_job_application', id:id, role:role, rowEl:rowEl, preStatus: isFinal ? 'trial' : (rowEl ? rowEl.getAttribute('data-status') : 'pending')};
    }

    function closeAllConfirm() {
        ['jaConfirmDecline','jaConfirmTrial','jaConfirmAccept'].forEach(function(id) {
            var el = document.getElementById(id); if (el) el.style.display = 'none';
        });
        pendingAction = null;
        suppressActionsUntil = Date.now() + 250;
    }

    /* Cancel buttons */
    on('jaCdCancel', 'click', function(e) { eatEvent(e); closeAllConfirm(); });
    on('jaCtCancel', 'click', function(e) { eatEvent(e); closeAllConfirm(); });
    on('jaAcCancel', 'click', function(e) { eatEvent(e); closeAllConfirm(); });

    /* Click outside backdrop to close — fully consume event so nothing underneath fires */
    ['jaConfirmDecline','jaConfirmTrial','jaConfirmAccept'].forEach(function(modalId) {
        var el = document.getElementById(modalId);
        if (!el) return;
        el.addEventListener('click', function(e) {
            eatEvent(e);
            if (e.target === el) closeAllConfirm();
        });
        var box = el.querySelector('.ja-confirm-box');
        if (box) {
            box.addEventListener('click', function(e) {
                eatEvent(e);
            });
        }
    });

    /* Escape key */
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') closeAllConfirm();
    });

    /* Confirm buttons — stopPropagation prevents the click from falling through
       the fixed backdrop and re-triggering the .ja-action-btn handler below */
    on('jaCdConfirm', 'click', function(e) {
        eatEvent(e);
        if (!pendingAction) return;
        var note = (document.getElementById('jaCdNote').value || '').trim();
        var p = pendingAction; closeAllConfirm();
        doAction(p.action, p.id, p.rowEl, note, p.preStatus);
    });
    on('jaCtConfirm', 'click', function(e) {
        eatEvent(e);
        if (!pendingAction) return;
        var p = pendingAction; closeAllConfirm();
        doAction(p.action, p.id, p.rowEl, '', p.preStatus);
    });
    on('jaAcConfirm', 'click', function(e) {
        eatEvent(e);
        if (!pendingAction) return;
        var inviteInput = document.getElementById('jaAcInvite');
        var invite = inviteInput ? (inviteInput.value || '').trim() : '';
        var p = pendingAction; closeAllConfirm();
        doAction(p.action, p.id, p.rowEl, '', p.preStatus, invite);
    });

    /* ── Table action clicks → open custom confirm ── */
    document.addEventListener('pointerdown', function(e) {
        if (Date.now() < suppressActionsUntil) return;

        var anyOpen = ['jaConfirmDecline','jaConfirmTrial','jaConfirmAccept'].some(function(id) {
            var el = document.getElementById(id);
            return el && el.style.display !== 'none' && el.style.display !== '';
        });
        if (anyOpen) return;

        var btn = e.target.closest('.ja-action-btn');
        if (!btn) return;
        eatEvent(e);

        var action  = btn.getAttribute('data-action');
        var id      = parseInt(btn.getAttribute('data-id'), 10);
        var role    = btn.getAttribute('data-role') || '';
        var rowEl   = btn.closest('tr');
        var curStatus = rowEl ? rowEl.getAttribute('data-status') : '';

        if (action === 'decline_job_application') {
            showConfirmDecline(id, role, rowEl);
        } else if (action === 'accept_job_application') {
            if (isBooster(role) && curStatus === 'pending') {
                showConfirmTrial(id, role, rowEl);
            } else {
                showConfirmAccept(id, role, rowEl, curStatus === 'trial');
            }
        }
    });

    /* ── Discord invite (table) ── */
    document.addEventListener('click', function(e) {
        var btn = e.target.closest('.discord-invite-btn');
        if (!btn) return;
        openDiscordModal(
            parseInt(btn.getAttribute('data-id'), 10),
            btn.getAttribute('data-discord') || '',
            btn.getAttribute('data-trial-invite') === '1'
        );
    });

    function openDiscordModal(id, discord, trialInvite) {
        discordCurrentId = id || null;
        discordCurrentDiscord = discord || '';
        var row = discordCurrentId ? document.getElementById('app-row-' + discordCurrentId) : null;
        var role = row ? (row.getAttribute('data-role') || '') : '';
        var status = row ? (row.getAttribute('data-status') || '') : '';
        discordCurrentTrialInvite = (trialInvite === true) || (row && isBooster(role) && status === 'pending');

        var dt = document.getElementById('diDiscordTag');
        if (dt) dt.textContent = discord || '—';
        var title = document.getElementById('diModalTitle');
        if (title) title.textContent = discordCurrentTrialInvite ? 'Send Trial Invite' : 'Send Discord Invite';
        var send = document.getElementById('diSendBtn');
        if (send) send.innerHTML = discordCurrentTrialInvite ? '<i class="fa-brands fa-discord"></i> Send Trial Invite' : '<i class="fa-brands fa-discord"></i> Send Invite';
        var li = document.getElementById('diInviteLink'); if (li) li.value = '';
        var no = document.getElementById('diNote'); if (no) no.value = '';
        if (!discordCurrentId && typeof create_toast === 'function') { create_toast('danger','Error','Application ID missing.'); return; }
        var el = document.getElementById('discordInviteModal');
        if (el) bootstrap.Modal.getOrCreateInstance(el).show();
    }

    /* ── Discord send ── */
    var diBtn = document.getElementById('diSendBtn');
    if (diBtn) {
        diBtn.addEventListener('click', function() {
            var link = (document.getElementById('diInviteLink').value || '').trim();
            var note = document.getElementById('diNote').value || '';
            if (!link) { if (typeof create_toast === 'function') create_toast('warning','Missing','Please enter a Discord invite link.'); return; }
            if (!discordCurrentId) { if (typeof create_toast === 'function') create_toast('danger','Error','Application ID missing.'); return; }

            diBtn.disabled = true;
            diBtn.innerHTML = '<i class="fa-duotone fa-spinner fa-spin"></i> Sending…';

            var fd = new FormData();
            fd.append('action', discordCurrentTrialInvite ? 'move_to_trial_and_send_invite_application' : 'send_discord_invite_application');
            fd.append('id', discordCurrentId);
            fd.append('invite_link', link);
            fd.append('note', note);

            fetch(AJAX_URL, {method:'POST', body:fd})
                .then(function(r) { return r.json(); })
                .then(function(res) {
                    diBtn.disabled = false;
                    diBtn.innerHTML = discordCurrentTrialInvite ? '<i class="fa-brands fa-discord"></i> Send Trial Invite' : '<i class="fa-brands fa-discord"></i> Send Invite';
                    if (res && res.status === 'success') {
                        var el = document.getElementById('discordInviteModal');
                        if (el) bootstrap.Modal.getInstance(el).hide();
                        var note = document.getElementById('mdInviteSentNote');
                        if (note) note.style.display = 'flex';
                        var inv = document.getElementById('mdExistingInvite');
                        if (inv) inv.textContent = res.invite_link || link;
                        var row = document.getElementById('app-row-' + discordCurrentId);
                        if (row) {
                            var vb = row.querySelector('.view-app-btn');
                            if (vb) {
                                vb.setAttribute('data-discord-invite', res.invite_link || link);
                                if (res.new_status) vb.setAttribute('data-status', res.new_status);
                            }
                            if (res.new_status) {
                                row.setAttribute('data-status', res.new_status);
                                row.classList.add('ja-row-updating');
                                setTimeout(function() { row.classList.remove('ja-row-updating'); }, 500);
                                var sc = row.querySelector('.ja-status-cell');
                                if (sc) sc.innerHTML = statusBadgeHtml(res.new_status);
                                var acell = row.querySelector('.ja-action-cell');
                                if (acell) {
                                    var vb2 = acell.querySelector('.view-app-btn');
                                    var vbHtml = vb2 ? vb2.outerHTML : '';
                                    acell.innerHTML = vbHtml + actionBtnsHtml(parseInt(discordCurrentId, 10), row.getAttribute('data-role') || '', res.new_status, discordCurrentDiscord);
                                }
                                recalcStats();
                                applyFilters();
                            }
                        }
                        if (typeof create_toast === 'function') create_toast(res.email_sent ? 'success' : 'warning', res.email_sent ? 'Sent!' : 'Saved', res.message || 'Discord invite saved.');
                        if (res.refresh) setTimeout(function() { location.reload(); }, 600);
                    } else {
                        if (typeof create_toast === 'function') create_toast('danger', 'Error', (res && res.message) || 'Something went wrong.');
                    }
                })
                .catch(function() {
                    diBtn.disabled = false;
                    diBtn.innerHTML = discordCurrentTrialInvite ? '<i class="fa-brands fa-discord"></i> Send Trial Invite' : '<i class="fa-brands fa-discord"></i> Send Invite';
                    if (typeof create_toast === 'function') create_toast('danger', 'Error', 'Request failed.');
                });
        });
    }

    /* ── Detail modal ── */
    var detailModal = document.getElementById('appDetailModal');
    if (detailModal) {
        detailModal.addEventListener('show.bs.modal', function(e) {
            var btn = e.relatedTarget; if (!btn) return;
            modalCurrentId = parseInt(btn.getAttribute('data-id'), 10) || null;
            var role   = btn.getAttribute('data-role') || '';
            var invite = btn.getAttribute('data-discord-invite') || '';
            var discord = btn.getAttribute('data-discord') || '—';

            /* Always read status from the live row, not from the cached button attribute */
            var rowEl  = modalCurrentId ? document.getElementById('app-row-' + modalCurrentId) : null;
            var status = rowEl ? (rowEl.getAttribute('data-status') || '') : (btn.getAttribute('data-status') || '');

            /* Fields */
            st('mdFullname',    btn.getAttribute('data-fullname'));
            st('mdEmail',       btn.getAttribute('data-email'));
            st('mdDiscord',     discord);
            st('mdCountry',     btn.getAttribute('data-country'));
            st('mdAge',         btn.getAttribute('data-age') || '—');
            st('mdLanguages',   btn.getAttribute('data-languages'));
            st('mdIngame',      btn.getAttribute('data-ingame'));
            st('mdRegion',      btn.getAttribute('data-region'));
            st('mdCurrentRank', btn.getAttribute('data-current-rank'));
            st('mdPeakRank',    btn.getAttribute('data-peak-rank'));
            st('mdChampPool',   btn.getAttribute('data-champ-pool'));
            st('mdAvailability',btn.getAttribute('data-availability'));
            st('mdExperience',  btn.getAttribute('data-experience'));
            st('mdMotivation',  btn.getAttribute('data-motivation'));
            st('mdReferral',    btn.getAttribute('data-referral'));

            /* Role badge */
            var rm = roleMeta[role] || {label:role, color:'#aaa', bg:'rgba(255,255,255,.07)', border:'rgba(255,255,255,.10)', img:null};
            var bh = '<span style="display:inline-flex;align-items:center;gap:.3rem;padding:3px 9px;border-radius:99px;font-size:.72rem;font-weight:800;background:'+rm.bg+';border:1px solid '+rm.border+';color:'+rm.color+';">';
            if (rm.img) bh += '<img src="'+rm.img+'" style="width:12px;height:12px;object-fit:contain;">';
            else        bh += '<i class="fa-duotone fa-store" style="font-size:.65rem;"></i>';
            bh += ' '+rm.label+'</span>';
            sh('mdRoleBadge', bh);

            /* Labels per role */
            applyLabels(role);

            /* Hint + buttons */
            hd('mdFlowHint'); hd('mdAcceptBtn'); hd('mdDeclineBtn'); hd('mdInviteBtn');
            var ac = document.getElementById('mdAcceptBtn');
            var dc = document.getElementById('mdDeclineBtn');
            var iv = document.getElementById('mdInviteBtn');
            var fh = document.getElementById('mdFlowHint');

            if (status === 'pending') {
                sw('mdDeclineBtn');
                if (isBooster(role)) {
                    sw('mdInviteBtn');
                    if (iv) iv.innerHTML = '<i class="fa-brands fa-discord"></i> Send Trial Invite';
                    sh('mdFlowHint', '<i class="fa-duotone fa-circle-info"></i> Send the trial Discord invite directly. The applicant will be moved to Trial once the invite is sent.');
                    fh.className = 'ja-hint ja-hint--info'; sw('mdFlowHint');
                } else {
                    sw('mdAcceptBtn');
                    ac.className = 'ja-btn ja-btn--accept';
                    ac.innerHTML = '<i class="fa-duotone fa-check"></i> Accept';
                }
            } else if (status === 'trial' && isBooster(role)) {
                sw('mdAcceptBtn'); sw('mdDeclineBtn'); sw('mdInviteBtn');
                ac.className = 'ja-btn ja-btn--accept';
                ac.innerHTML = '<i class="fa-duotone fa-check"></i> Final Accept';
                sh('mdFlowHint', '<i class="fa-duotone fa-flask"></i> This booster is in trial. Send a Discord invite and accept or decline after the trial period.');
                fh.className = 'ja-hint ja-hint--info'; sw('mdFlowHint');
            } else if (status === 'accepted') {
                sw('mdInviteBtn');
                sh('mdFlowHint', '<i class="fa-solid fa-circle-check"></i> This application has been accepted.');
                fh.className = 'ja-hint ja-hint--success'; sw('mdFlowHint');
            }

            if (iv) { iv.setAttribute('data-id', modalCurrentId); iv.setAttribute('data-discord', discord); }

            if (invite) { sw('mdInviteSentNote'); st('mdExistingInvite', invite); }
            else         { hd('mdInviteSentNote'); st('mdExistingInvite', ''); }
        });
    }

    /* Modal footer buttons */
    on('mdAcceptBtn', 'click', function() {
        if (!modalCurrentId) return;
        var rowEl = document.getElementById('app-row-' + modalCurrentId);
        var role      = rowEl ? rowEl.getAttribute('data-role') : '';
        var curStatus = rowEl ? rowEl.getAttribute('data-status') : '';
        if (curStatus === 'accepted' || curStatus === 'declined') {
            if (typeof create_toast === 'function') create_toast('warning', 'Already updated', 'This application has already been ' + curStatus + '.');
            hide_modal('appDetailModal');
            return;
        }
        var snapStatus = curStatus; /* freeze at click time */
        hide_modal('appDetailModal');
        setTimeout(function() {
            if (isBooster(role) && snapStatus === 'pending') {
                showConfirmTrial(modalCurrentId, role, rowEl);
            } else {
                showConfirmAccept(modalCurrentId, role, rowEl, snapStatus === 'trial');
            }
        }, 300);
    });
    on('mdDeclineBtn', 'click', function() {
        if (!modalCurrentId) return;
        var rowEl = document.getElementById('app-row-' + modalCurrentId);
        var role      = rowEl ? rowEl.getAttribute('data-role') : '';
        var curStatus = rowEl ? rowEl.getAttribute('data-status') : '';
        if (curStatus === 'declined') {
            if (typeof create_toast === 'function') create_toast('warning', 'Already declined', 'This application has already been declined.');
            hide_modal('appDetailModal');
            return;
        }
        if (curStatus === 'accepted') {
            if (typeof create_toast === 'function') create_toast('warning', 'Already accepted', 'This application has already been accepted.');
            hide_modal('appDetailModal');
            return;
        }
        hide_modal('appDetailModal');
        setTimeout(function() {
            showConfirmDecline(modalCurrentId, role, rowEl);
        }, 300);
    });
    on('mdInviteBtn', 'click', function() {
        var btn = document.getElementById('mdInviteBtn');
        openDiscordModal(modalCurrentId, btn ? (btn.getAttribute('data-discord') || '') : '', true);
    });

    /* ── Tiny helpers ── */
    function st(id, v)   { var el = document.getElementById(id); if (el) el.textContent = v || '—'; }
    function sh(id, v)   { var el = document.getElementById(id); if (el) el.innerHTML   = v || ''; }
    function sw(id)      { var el = document.getElementById(id); if (el) el.style.display = ''; }
    function hd(id)      { var el = document.getElementById(id); if (el) el.style.display = 'none'; }
    function on(id, ev, fn) { var el = document.getElementById(id); if (el) el.addEventListener(ev, fn); }
    function hide_modal(id) { var el = document.getElementById(id); if (el) { var m = bootstrap.Modal.getInstance(el); if (m) m.hide(); } }

    function applyLabels(role) {
        if (role === 'seller') {
            st('mdDetailsTitle','Seller Details'); st('mdLabelIngame','Full Name / Brand'); st('mdLabelRegion','Store / Region');
            st('mdLabelCurrentRank','Services'); st('mdLabelPeakRank','Platforms / Store Links'); st('mdLabelChampPool','Stock / Extra');
            st('mdLabelAvailability','Preferred Messaging Platform'); st('mdLabelExperience','Sourcing / Experience');
            st('mdLabelMotivation','Additional Information'); st('mdLabelReferral','Referral');
        } else if (role === 'gg_girl') {
            st('mdDetailsTitle','GG Girl Details'); st('mdLabelIngame','Main Games'); st('mdLabelRegion','Country / Region');
            st('mdLabelCurrentRank','Current Rank(s)'); st('mdLabelPeakRank','Voice / Streaming Experience'); st('mdLabelChampPool','Personality / Extra');
            st('mdLabelAvailability','Availability'); st('mdLabelExperience','Gaming Experience');
            st('mdLabelMotivation','Why join?'); st('mdLabelReferral','Referral');
        } else {
            st('mdDetailsTitle','Booster Details'); st('mdLabelIngame','In-Game Name'); st('mdLabelRegion','Region');
            st('mdLabelCurrentRank','Current Rank'); st('mdLabelPeakRank','Peak Rank'); st('mdLabelChampPool','Champion Pool / Roles');
            st('mdLabelAvailability','Availability'); st('mdLabelExperience','Experience');
            st('mdLabelMotivation','Motivation / Why join?'); st('mdLabelReferral','How heard about us');
        }
    }
    /* ── Live polling: auto-refresh row statuses every 20s ── */
    var jaPolling = true;
    var jaLastPoll = Date.now();

    function jaPoll() {
        if (!jaPolling) return;

        var ids = [];
        document.querySelectorAll('#jaTbody tr.al-row').forEach(function(r) {
            var id = r.id.replace('app-row-', '');
            if (id) ids.push(id);
        });
        if (!ids.length) return;

        var fd = new FormData();
        fd.append('action', 'poll_job_application_statuses');
        fd.append('ids', ids.join(','));

        fetch(AJAX_URL, {method:'POST', body:fd})
            .then(function(r) { return r.json(); })
            .then(function(res) {
                if (!res || !res.statuses) return;
                var changed = false;
                Object.keys(res.statuses).forEach(function(id) {
                    var newStatus = res.statuses[id];
                    var rowEl = document.getElementById('app-row-' + id);
                    if (!rowEl) return;
                    var curStatus = rowEl.getAttribute('data-status');
                    if (curStatus === newStatus) return;

                    /* Status changed externally — update row live */
                    rowEl.setAttribute('data-status', newStatus);
                    rowEl.classList.add('ja-row-updating');
                    setTimeout(function() { rowEl.classList.remove('ja-row-updating'); }, 500);

                    var sc = rowEl.querySelector('.ja-status-cell');
                    if (sc) sc.innerHTML = statusBadgeHtml(newStatus);

                    var ac = rowEl.querySelector('.ja-action-cell');
                    if (ac) {
                        var vb  = ac.querySelector('.view-app-btn');
                        var discord = vb ? (vb.getAttribute('data-discord') || '') : '';
                        var vbHtml  = vb ? vb.outerHTML : '';
                        var role    = rowEl.getAttribute('data-role') || '';
                        if (vb) vb.setAttribute('data-status', newStatus);
                        ac.innerHTML = vbHtml + actionBtnsHtml(parseInt(id, 10), role, newStatus, discord);
                    }
                    changed = true;
                });
                if (changed) { recalcStats(); applyFilters(false); }
            })
            .catch(function() { /* silent — polling, not critical */ });
    }

    /* Poll every 20 seconds; pause when tab is hidden */
    setInterval(jaPoll, 20000);
    document.addEventListener('visibilitychange', function() {
        jaPolling = !document.hidden;
        /* catch up immediately when tab becomes visible again */
        if (jaPolling && Date.now() - jaLastPoll > 10000) jaPoll();
        jaLastPoll = Date.now();
    });

})();
</script>
<?= $this->end() ?>
