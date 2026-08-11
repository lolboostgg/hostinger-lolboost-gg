<?= $this->layout('admin/layouts/main', ['meta' => $meta]) ?>

<style>
/* Wider egirl admin pages, matched to booster admin layout. */
@media (min-width: 992px) {
  body .content.container,
  body .content .container,
  body main .container,
  body .main .container,
  body .page-content .container,
  body .container-fluid {
    max-width: min(1760px, calc(100vw - 48px)) !important;
  }
}
@media (min-width: 1400px) {
  body .container,
  body .container-lg,
  body .container-xl,
  body .container-xxl {
    max-width: min(1760px, calc(100vw - 48px)) !important;
  }
}
@media (max-width: 991.98px) {
  body .content.container,
  body .content .container,
  body main .container,
  body .main .container,
  body .page-content .container,
  body .container,
  body .container-fluid {
    max-width: 100% !important;
    padding-left: 1rem !important;
    padding-right: 1rem !important;
  }
}
</style>

<?php
  $egirl    = $egirl    ?? [];
  $services = $services ?? [];
  $orders   = $orders   ?? [];
  $payments = $payments ?? [];

  $egirlId     = (int)($egirl['id'] ?? 0);
  $usernameRaw = (string)($egirl['username'] ?? '');
  $username    = htmlspecialchars($usernameRaw, ENT_QUOTES);
  $email       = htmlspecialchars((string)($egirl['email'] ?? ''), ENT_QUOTES);
  $discord     = htmlspecialchars((string)($egirl['discord'] ?? ''), ENT_QUOTES);
  $balanceCents = (int)($egirl['balance'] ?? 0);
  $balanceEuro  = number_format($balanceCents / 100, 2);

  $isBanned   = (int)($egirl['is_banned'] ?? 0);
  $isVerified = (int)($egirl['verified']  ?? 0);

  // is_banned=1 + verified=0 = pending onboarding (system sets is_banned=1 for unverified)
  // is_banned=1 + verified=1 = actually banned
  $isReallyBanned = ($isBanned && $isVerified);

  if ($isReallyBanned)  { $statusLabel = 'Banned';  $statusClass = 'bg-soft-danger text-danger';   $statusIcon = 'fa-ban'; }
  elseif (!$isVerified) { $statusLabel = 'Pending'; $statusClass = 'bg-soft-warning text-warning'; $statusIcon = 'fa-clock'; }
  else                  { $statusLabel = 'Active';  $statusClass = 'bg-soft-success text-success'; $statusIcon = 'fa-circle-check'; }

  $totalSessions = (int)($egirl['total_sessions'] ?? 0);
  $reviewAvg     = number_format((float)($egirl['review_avg'] ?? 0), 1);
  $reviewCount   = (int)($egirl['review_count'] ?? 0);
  $paymentCount  = count($payments);

  $currentCut    = (isset($egirl['egirl_cut_percent']) && $egirl['egirl_cut_percent'] !== null) ? (float)$egirl['egirl_cut_percent'] : null;
  $displayCut    = $currentCut !== null ? $currentCut : 60.0;
  $platformShare = round(100 - $displayCut, 2);
  $isDefault     = $currentCut === null;

  $iconRaw   = trim((string)($egirl['icon'] ?? ''));
  $avatarSrc = '';
  if ($iconRaw !== '') {
    $avatarSrc = preg_match('~^https?://~i', $iconRaw)
      ? $iconRaw
      : rtrim(SITE_URL ?? '', '/') . '/' . ltrim($iconRaw, '/');
  }
  $avatarLetters = strtoupper(substr(preg_replace('/[^A-Za-z0-9]/', '', $usernameRaw ?: 'E') ?: 'E', 0, 1));

  $bannerRaw = trim((string)($egirl['banner'] ?? ''));
  // Fallback: use booster cover if no egirl banner is set
  if ($bannerRaw === '') {
    $bannerRaw = trim((string)($egirl['booster_cover'] ?? ''));
  }
  // Second fallback: b.* already includes cover directly
  if ($bannerRaw === '') {
    $bannerRaw = trim((string)($egirl['cover'] ?? ''));
  }
  $bannerSrc = '';
  if ($bannerRaw !== '') {
    $bannerSrc = preg_match('~^https?://~i', $bannerRaw)
      ? $bannerRaw
      : rtrim(SITE_URL ?? '', '/') . '/' . ltrim($bannerRaw, '/');
  }

  $gimap = ['lol' => 'league-of-legends', 'val' => 'valorant', 'tft' => 'teamfight-tactics'];
  $gamesList = array_filter(explode('|', $egirl['games'] ?? ''));

  $langImgMap  = ['en'=>'en.png','de'=>'de.png','fr'=>'fr.png','es'=>'es.png','tr'=>'tr.png','pt'=>'pt.png','it'=>'it.png','pl'=>'pl.png','ru'=>'ru.webp','nl'=>'nl.png','sv'=>'sv.png','da'=>'da.webp','no'=>'no.webp','fi'=>'fi.webp','cs'=>'cz.webp','ro'=>'ro.png','hu'=>'hu.webp','uk'=>'uk.png','ar'=>'ar.png','zh'=>'chinese.png','ja'=>'ja.webp','ko'=>'ko.png','el'=>'el.png','hr'=>'hr.png','bg'=>'bg.webp','vn'=>'vn.webp','ph'=>'ph.webp','th'=>'th.webp'];
  $langNameMap = ['en'=>'English','de'=>'German','fr'=>'French','es'=>'Spanish','tr'=>'Turkish','pt'=>'Portuguese','it'=>'Italian','pl'=>'Polish','ru'=>'Russian','nl'=>'Dutch','sv'=>'Swedish','da'=>'Danish','no'=>'Norwegian','fi'=>'Finnish','cs'=>'Czech','ro'=>'Romanian','hu'=>'Hungarian','uk'=>'Ukrainian','ar'=>'Arabic','zh'=>'Chinese','ja'=>'Japanese','ko'=>'Korean','el'=>'Greek','hr'=>'Croatian','bg'=>'Bulgarian','vn'=>'Vietnamese','ph'=>'Filipino','th'=>'Thai'];
  $langBase    = ASSET_URL . '/core/main/img/languages/';
  $langsList   = array_filter(array_map('trim', explode('|', $egirl['languages'] ?? '')));

  $egGames    = array_values(array_filter(explode('|', (string)($egirl['games'] ?? ''))));
  $egLangs    = trim((string)($egirl['languages'] ?? ''));
  $egTimezone = trim((string)($egirl['timezone']  ?? ''));
  $egCountry  = trim((string)($egirl['country']   ?? ''));
  $egVoice    = !empty($egirl['voice_chat']);
  $egShow     = !empty($egirl['show_profile']);
  $egBio      = (string)($egirl['bio'] ?? '');
  for ($i = 0; $i < 3; $i++) {
    $decoded = html_entity_decode($egBio, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    if ($decoded === $egBio) break;
    $egBio = $decoded;
  }
  $LOL_TIERS  = ['Iron','Bronze','Silver','Gold','Platinum','Emerald','Diamond','Master','Grandmaster','Challenger'];
  $VAL_TIERS  = ['Iron','Bronze','Silver','Gold','Platinum','Diamond','Ascendant','Immortal','Radiant'];
  $DIVISIONS  = ['I','II','III','IV'];
  $NO_DIV     = ['Master','Grandmaster','Challenger','Immortal','Radiant'];
  $p = fn($s) => ['tier'=>explode(' ',trim($s),2)[0]??'','div'=>explode(' ',trim($s),2)[1]??''];
  $egLolP = $p($egirl['lol_rank']??'');
  $egValP = $p($egirl['val_rank']??'');
  $egTftP = $p($egirl['tft_rank']??'');
?>
<link rel="stylesheet" href="<?= ASSET_URL ?>/origin/dash/vendor/tom-select/dist/css/tom-select.bootstrap5.css">
<style>
.game-chip{display:inline-flex;align-items:center;gap:8px;padding:8px 14px;border-radius:10px;cursor:pointer;border:1px solid rgba(255,255,255,.1);background:rgba(255,255,255,.04);font-size:.88rem;font-weight:700;color:rgba(255,255,255,.5);transition:all .15s;user-select:none;}
.game-chip img{width:20px;height:20px;object-fit:contain;}
.game-chip.selected{border-color:rgba(168,85,247,.7);background:rgba(168,85,247,.15);color:#fff;}
.game-chip[data-game="lol"].selected{border-color:rgba(200,170,60,.7);background:rgba(200,170,60,.12);color:#c8aa3c;}
.game-chip[data-game="val"].selected{border-color:rgba(255,70,85,.7);background:rgba(255,70,85,.1);color:#ff6b77;}
.game-chip[data-game="tft"].selected{border-color:rgba(100,180,255,.7);background:rgba(100,180,255,.1);color:#64b4ff;}
.rank-section{display:none;}.rank-section.visible{display:block;}
.ts-control,.ts-wrapper.single .ts-control,.ts-wrapper.multi .ts-control{background:rgba(0,0,0,.25)!important;border-color:rgba(255,255,255,.10)!important;color:#e9e9ef!important;min-height:44px;}
.ts-control .item{background:rgba(99,102,241,.2)!important;border:1px solid rgba(99,102,241,.35)!important;color:#fff!important;}
.ts-control input{color:#e9e9ef!important;}
.ts-dropdown{background:rgba(16,18,26,.98)!important;border-color:rgba(255,255,255,.1)!important;color:#e9e9ef!important;z-index:9999!important;}
.ts-dropdown .option{color:#e9e9ef!important;}
.ts-dropdown .active{background:rgba(99,102,241,.2)!important;}
.filter-pill-group{display:flex;gap:.5rem;flex-wrap:wrap;}
.filter-pill{border:1px solid rgba(255,255,255,.1);background:rgba(255,255,255,.03);color:var(--bs-secondary-color, #9ca3af);padding:.45rem .8rem;border-radius:999px;font-size:.78rem;font-weight:600;line-height:1;cursor:pointer;transition:all .15s ease;}
.filter-pill:hover,.filter-pill.active{background:rgba(108,92,231,.18);border-color:rgba(108,92,231,.4);color:#fff;}
.eg-actions-btn{border-radius:999px !important;padding:.48rem .95rem;font-weight:700;}
.eg-actions-btn::after{display:none !important;}
/* The header card clips its banner with overflow:hidden, which also cut off the
   Actions dropdown. Move the clipping onto the banner itself so the menu can escape. */
.seller-profile-header{overflow:visible !important;}
.seller-profile-header .seller-profile-banner{border-top-left-radius:inherit;border-top-right-radius:inherit;overflow:hidden;}
.seller-profile-header .dropdown-menu{z-index:1055;min-width:230px;box-shadow:0 20px 60px rgba(0,0,0,.55);}
.eg-review-card{border:1px solid rgba(255,255,255,.08);background:rgba(255,255,255,.03);border-radius:14px;padding:14px 16px;}
.eg-review-card + .eg-review-card{margin-top:12px;}
.eg-review-avatar{width:38px;height:38px;border-radius:50%;object-fit:cover;flex:0 0 auto;background:rgba(255,255,255,.06);}
.eg-review-stars{color:#ffc44d;letter-spacing:2px;font-size:.9rem;}
.eg-review-text{margin-top:10px;color:rgba(255,255,255,.78);font-size:.9rem;line-height:1.5;white-space:pre-wrap;word-break:break-word;}
.seller-sticky-col{position:sticky;top:1.5rem;align-self:flex-start;}
@media (max-width: 991.98px){.seller-sticky-col{position:static;}}
</style>

<div class="card mb-4 overflow-hidden seller-profile-header">
  <div class="seller-profile-banner position-relative" <?php if ($bannerSrc): ?>style="background-image:url('<?= htmlspecialchars($bannerSrc, ENT_QUOTES) ?>');background-size:cover;background-position:center;"<?php endif; ?>>
    <div class="seller-profile-banner-glow"></div>
  </div>

  <div class="card-body pt-0">
    <div class="d-flex justify-content-end mt-3 mb-0">
      <div class="dropdown">
        <button type="button" class="btn btn-white btn-sm dropdown-toggle eg-actions-btn" data-bs-toggle="dropdown" aria-expanded="false">
          <i class="fa-solid fa-ellipsis-vertical me-2"></i> Actions
        </button>
        <div class="dropdown-menu dropdown-menu-end mt-1">
          <span class="dropdown-header">Balance Actions</span>
          <a class="dropdown-item d-flex align-items-center" href="#" data-bs-toggle="modal" data-bs-target="#addMoneyModal">
            <i class="fa-duotone fa-circle-plus dropdown-item-icon"></i> Add Money
          </a>
          <a class="dropdown-item d-flex align-items-center" href="#" data-bs-toggle="modal" data-bs-target="#fineAccountModal">
            <i class="fa-duotone fa-triangle-exclamation dropdown-item-icon"></i> Fine E-Girl
          </a>

          <?php if ($isVerified && !$isBanned): ?>
            <div><hr class="dropdown-divider"></div>
            <span class="dropdown-header">Account</span>
            <a class="dropdown-item d-flex align-items-center js-egirl-resend" href="#" data-id="<?= $egirlId ?>">
              <i class="fa-duotone fa-paper-plane dropdown-item-icon"></i> Resend Login
            </a>
          <?php endif; ?>

          <div><hr class="dropdown-divider"></div>
          <?php if (!$isVerified): ?>
            <?php /* Pending onboarding — is_banned=1 is set by system for all unverified */ ?>
            <span class="dropdown-header">Onboarding</span>
            <a class="dropdown-item d-flex align-items-center text-success js-egirl-verify" href="#" data-id="<?= $egirlId ?>">
              <i class="fa-duotone fa-check dropdown-item-icon"></i> Verify &amp; Send Login
            </a>
            <a class="dropdown-item d-flex align-items-center text-danger js-egirl-decline" href="#" data-id="<?= $egirlId ?>">
              <i class="fa-duotone fa-times dropdown-item-icon"></i> Decline
            </a>
          <?php elseif ($isReallyBanned): ?>
            <span class="dropdown-header">Danger Zone</span>
            <a class="dropdown-item d-flex align-items-center text-success js-egirl-ban" href="#" data-id="<?= $egirlId ?>" data-ban="0">
              <i class="fa-duotone fa-unlock dropdown-item-icon"></i> Unban E-Girl
            </a>
          <?php else: ?>
            <span class="dropdown-header">Danger Zone</span>
            <a class="dropdown-item d-flex align-items-center text-danger js-egirl-ban" href="#" data-id="<?= $egirlId ?>" data-ban="1">
              <i class="fa-duotone fa-ban dropdown-item-icon"></i> Ban E-Girl
            </a>
          <?php endif; ?>
        </div>
      </div>
    </div>

    <div class="text-center seller-profile-meta">
      <div class="seller-profile-avatar mx-auto">
        <?php if ($avatarSrc): ?>
          <img src="<?= htmlspecialchars($avatarSrc, ENT_QUOTES) ?>" alt="<?= $username ?>">
        <?php else: ?>
          <span><?= $avatarLetters ?></span>
        <?php endif; ?>
      </div>

      <div class="d-inline-flex align-items-center justify-content-center gap-2 flex-wrap mt-3">
        <h2 class="page-header-title mb-0"><?= $username ?: 'E-Girl' ?></h2>
        <span class="badge <?= $statusClass ?>"><i class="fa-duotone <?= $statusIcon ?> me-1"></i><?= $statusLabel ?></span>
      </div>

      <div class="text-muted mt-2 small d-flex align-items-center justify-content-center gap-3 flex-wrap">
        <span><i class="fa-duotone fa-star-shooting me-1"></i>E-Girl Account</span>
        <span><i class="fa-duotone fa-hashtag me-1"></i><?= $egirlId ?></span>
        <?php if ($email): ?>
          <span><i class="fa-duotone fa-envelope me-1"></i><?= $email ?></span>
        <?php endif; ?>
      </div>

      <div class="row g-2 justify-content-center mt-4 seller-stat-row">
        <div class="col-6 col-md-3 col-xl-2">
          <div class="seller-stat-pill">
            <div class="seller-stat-value">€<?= $balanceEuro ?></div>
            <div class="seller-stat-label">Balance</div>
          </div>
        </div>
        <div class="col-6 col-md-3 col-xl-2">
          <div class="seller-stat-pill">
            <div class="seller-stat-value text-info"><?= $totalSessions ?></div>
            <div class="seller-stat-label">Sessions</div>
          </div>
        </div>
        <div class="col-6 col-md-3 col-xl-2">
          <div class="seller-stat-pill">
            <div class="seller-stat-value text-warning"><?= $reviewAvg ?></div>
            <div class="seller-stat-label">Avg Rating</div>
          </div>
        </div>
        <div class="col-6 col-md-3 col-xl-2">
          <div class="seller-stat-pill">
            <div class="seller-stat-value"><?= $displayCut ?>%</div>
            <div class="seller-stat-label">E-Girl Cut</div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<?php
  $page    = $page    ?? 'overview';
  $baseUrl = $baseUrl ?? (ADMN_URL . '/egirl/' . $egirlId);
  // Counts come from the route as real table totals — the per-page result sets are
  // capped (Overview loads 5 bookings) or missing entirely on other tabs.
  $tabCounts = $tabCounts ?? [];
  $navPages = [
    'overview' => ['label' => 'Overview', 'count' => null],
    'services' => ['label' => 'Services', 'count' => (int)($tabCounts['services'] ?? count($services ?? []))],
    'bookings' => ['label' => 'Bookings', 'count' => (int)($tabCounts['bookings'] ?? count($orders ?? []))],
    'payments' => ['label' => 'Payments', 'count' => (int)($tabCounts['payments'] ?? count($payments ?? []))],
    'reviews'  => ['label' => 'Reviews',  'count' => (int)($tabCounts['reviews']  ?? count($reviews ?? []))],
  ];
?>

<div class="border-bottom mb-4 seller-tab-wrap">
  <ul class="nav nav-tabs seller-nav-tabs" id="sellerViewTabs" role="tablist">
    <?php foreach ($navPages as $slug => $nav):
      $isActive = ($page === $slug); ?>
      <li class="nav-item">
        <a class="nav-link <?= $isActive?'active':'' ?>" href="<?= $baseUrl ?>/<?= $slug ?>">
          <?= $nav['label'] ?>
          <?php if ($nav['count'] !== null): ?>
            <span class="badge bg-soft-secondary text-secondary ms-1"><?= $nav['count'] ?></span>
          <?php endif; ?>
        </a>
      </li>
    <?php endforeach; ?>
  </ul>
</div>

<?php // ── PAGE: OVERVIEW ──────────────────────────────────────────
if ($page === 'overview'): ?>

<div class="row g-4 align-items-start">
  <div class="col-lg-4">
    <div class="d-grid gap-4 seller-sticky-col">

      <div class="card">
        <div class="card-header"><h4 class="card-header-title">Overview</h4></div>
        <div class="card-body">
          <ul class="list-unstyled list-py-2 text-dark mb-0">
            <li class="pb-0"><span class="card-subtitle">Account</span></li>
            <li><i class="fa-solid fa-hashtag dropdown-item-icon"></i> <?= $egirlId ?></li>
            <li><i class="fa-duotone fa-user dropdown-item-icon"></i> <?= $username ?: '—' ?></li>
            <li><i class="fa-duotone fa-envelope dropdown-item-icon"></i> <?= $email ?: '—' ?></li>
            <li><i class="fa-brands fa-discord dropdown-item-icon"></i> <?= $discord ?: '—' ?></li>
            <li><i class="fa-duotone fa-wallet dropdown-item-icon"></i> €<?= $balanceEuro ?></li>
            <li><i class="fa-duotone <?= $statusIcon ?> dropdown-item-icon"></i> <?= $statusLabel ?></li>
            <li class="pt-4 pb-1"><span class="card-subtitle">Games</span></li>
            <li>
              <?php if (!empty($gamesList)): ?>
                <div class="d-flex flex-wrap gap-2 mt-1">
                  <?php foreach ($gamesList as $g):
                    $g = trim((string)$g);
                    if ($g === '') continue;
                    // util_game_icon_url() resolves lol/val/tft plus every dynamically added
                    // game, so the sidebar shows icons instead of raw slug text.
                    $gIcon  = function_exists('util_game_icon_url') ? util_game_icon_url($g) : '';
                    $gLabel = function_exists('util_game_display_name')
                      ? util_game_display_name($g)
                      : ucwords(str_replace(['-', '_'], ' ', $g));
                    $gLabel = htmlspecialchars($gLabel, ENT_QUOTES, 'UTF-8');
                  ?>
                    <?php if ($gIcon !== ''): ?>
                      <img src="<?= htmlspecialchars($gIcon, ENT_QUOTES) ?>" title="<?= $gLabel ?>" alt="<?= $gLabel ?>" style="width:26px;height:26px;object-fit:contain;border-radius:7px;">
                    <?php else: ?>
                      <span class="badge bg-soft-secondary text-secondary" title="<?= $gLabel ?>"><?= $gLabel ?></span>
                    <?php endif; ?>
                  <?php endforeach; ?>
                </div>
              <?php else: ?><span class="text-muted">—</span><?php endif; ?>
            </li>
            <li class="pt-3 pb-1"><span class="card-subtitle">Languages</span></li>
            <li>
              <?php if (!empty($langsList)): ?>
                <div class="d-flex flex-wrap gap-1 mt-1">
                  <?php foreach ($langsList as $lc): $lc=trim($lc); if(!$lc) continue; $img=$langImgMap[$lc]??null; $lname=$langNameMap[$lc]??strtoupper($lc); if(!$img) continue; ?>
                    <span title="<?= htmlspecialchars($lname) ?>" style="display:inline-flex;align-items:center;gap:.3rem;padding:.25rem .55rem;border-radius:7px;background:rgba(255,255,255,.05);border:1px solid rgba(255,255,255,.1);font-size:.72rem;font-weight:700;color:rgba(255,255,255,.75);">
                      <img src="<?= $langBase . $img ?>" style="width:18px;height:12px;object-fit:cover;border-radius:2px;flex-shrink:0;" alt="" onerror="this.closest('span').style.display='none'">
                      <?= strtoupper(htmlspecialchars($lc)) ?>
                    </span>
                  <?php endforeach; ?>
                </div>
              <?php else: ?><span class="text-muted">—</span><?php endif; ?>
            </li>
            <li class="pt-3"><i class="fa-duotone fa-star dropdown-item-icon text-warning"></i> <?= $reviewCount ?> review<?= $reviewCount!==1?'s':'' ?> (⌀ <?= $reviewAvg ?>)</li>
          </ul>
        </div>
      </div>

      <div class="card">
        <div class="card-header"><h4 class="card-header-title">Quick Stats</h4></div>
        <div class="card-body">
          <div class="d-flex justify-content-between align-items-center py-2 border-bottom"><span class="text-muted">Services</span><span class="fw-semibold"><?= count($services) ?></span></div>
          <div class="d-flex justify-content-between align-items-center py-2 border-bottom"><span class="text-muted">Total sessions</span><span class="fw-semibold"><?= $totalSessions ?></span></div>
          <div class="d-flex justify-content-between align-items-center py-2 border-bottom"><span class="text-muted">Reviews</span><span class="fw-semibold"><?= $reviewCount ?></span></div>
          <div class="d-flex justify-content-between align-items-center pt-2"><span class="text-muted">Payment entries</span><span class="fw-semibold"><?= $paymentCount ?></span></div>
        </div>
      </div>

      <div class="card">
        <div class="card-header d-flex align-items-center justify-content-between">
          <h4 class="card-header-title"><i class="fa-solid fa-percent me-2 text-primary"></i>Revenue Share</h4>
          <span class="badge <?= $isDefault?'bg-soft-secondary text-secondary':'bg-soft-primary text-primary' ?>"><?= $isDefault?'Default':'Custom' ?></span>
        </div>
        <div class="card-body">
          <div class="d-flex justify-content-between small mb-1">
            <span class="text-success fw-semibold"><i class="fa-solid fa-user me-1"></i>E-Girl <?= $displayCut ?>%</span>
            <span class="text-primary fw-semibold"><?= $platformShare ?>% Platform<i class="fa-solid fa-building ms-1"></i></span>
          </div>
          <div class="progress mb-3" style="height:10px;border-radius:8px;">
            <div class="progress-bar bg-success" id="cutBarEg" style="width:<?= $displayCut ?>%"></div>
            <div class="progress-bar bg-primary" style="width:<?= $platformShare ?>%"></div>
          </div>
          <div class="d-flex justify-content-between small text-muted mb-4">
            <span>€<?= number_format($displayCut/10,2) ?> per €10</span>
            <span>€<?= number_format($platformShare/10,2) ?> per €10</span>
          </div>
          <div class="input-group input-group-sm mb-2">
            <span class="input-group-text"><i class="fa-solid fa-wallet"></i></span>
            <input type="number" class="form-control" id="egirlCutInput" min="1" max="99" step="0.5" placeholder="60 (platform default)" value="<?= $isDefault?'':htmlspecialchars($displayCut) ?>">
            <span class="input-group-text">%</span>
            <button class="btn btn-primary" id="btnSaveCut"><i class="fa-solid fa-floppy-disk"></i> Save</button>
          </div>
          <?php if (!$isDefault): ?>
            <a href="#" id="btnResetCut" class="small text-muted"><i class="fa-solid fa-rotate-left me-1"></i>Reset to platform default (60%)</a>
          <?php endif; ?>
        </div>
      </div>

    </div>
  </div>

  <div class="col-lg-8">
    <div class="card mb-4">
      <div class="card-header"><h4 class="card-header-title"><i class="fa-duotone fa-user-pen me-2 text-primary"></i>Profile Details</h4></div>
      <div class="card-body">
        <div class="row mb-4"><label class="col-sm-3 col-form-label form-label">Bio</label><div class="col-sm-9"><textarea class="form-control" id="adminProfileBio" rows="4"><?= htmlspecialchars($egBio) ?></textarea></div></div>
        <div class="row mb-4"><label class="col-sm-3 col-form-label form-label">Languages</label><div class="col-sm-9"><select class="form-select" id="adminProfileLanguages" multiple autocomplete="off"><?= util_load_languages_select($egLangs) ?></select></div></div>
        <div class="row mb-4"><label class="col-sm-3 col-form-label form-label">Country</label><div class="col-sm-9"><input type="text" class="form-control" id="adminProfileCountry" value="<?= htmlspecialchars($egCountry) ?>" placeholder="e.g. Germany"></div></div>
        <div class="row mb-4"><label class="col-sm-3 col-form-label form-label">Timezone</label><div class="col-sm-9"><select class="form-select" id="adminProfileTimezone"><?php if(function_exists('util_load_timezones_select')) echo util_load_timezones_select($egTimezone); else foreach(DateTimeZone::listIdentifiers() as $tz) echo '<option value="'.htmlspecialchars($tz).'"'.($tz===$egTimezone?' selected':'').'>'.htmlspecialchars($tz).'</option>'; ?></select></div></div>
        <div class="row mb-4"><label class="col-sm-3 col-form-label form-label">Discord</label><div class="col-sm-9"><input type="text" class="form-control" id="adminProfileDiscord" value="<?= $discord ?>" placeholder="username#0000 or @username"></div></div>
        <div class="row mb-4">
          <label class="col-sm-3 col-form-label form-label">Games</label>
          <div class="col-sm-9">
            <?php
              // Same source as the E-Girl's own setup wizard, so the keys stored in
              // egirl_profiles.games round-trip and every enabled game is offered here.
              $pickerGames = function_exists('lb_egirl_game_options') ? lb_egirl_game_options() : [];
              // Anything already saved but no longer offered must stay visible, otherwise
              // saving the form would silently drop it.
              foreach ($egGames as $savedGame) {
                $savedGame = trim((string)$savedGame);
                if ($savedGame === '' || isset($pickerGames[$savedGame])) continue;
                $pickerGames[$savedGame] = [
                  'label' => function_exists('util_game_display_name') ? util_game_display_name($savedGame) : ucwords(str_replace(['-', '_'], ' ', $savedGame)),
                  'icon'  => function_exists('util_game_icon_url') ? util_game_icon_url($savedGame) : '',
                ];
              }
            ?>
            <div class="d-flex gap-2 flex-wrap" id="adminGamePicker">
              <?php foreach ($pickerGames as $gk => $gv):
                $gkSafe = htmlspecialchars((string)$gk, ENT_QUOTES);
                $gLabel = htmlspecialchars((string)($gv['label'] ?? $gk), ENT_QUOTES);
                $gIcon  = trim((string)($gv['icon'] ?? ''));
              ?>
                <div class="game-chip <?= in_array((string)$gk, $egGames, true) ? 'selected' : '' ?>" data-game="<?= $gkSafe ?>" onclick="adminToggleGame(this)">
                  <?php if ($gIcon !== ''): ?><img src="<?= htmlspecialchars($gIcon, ENT_QUOTES) ?>" alt=""><?php endif; ?><?= $gLabel ?>
                </div>
              <?php endforeach; ?>
            </div>
            <input type="hidden" id="adminProfileGames" value="<?= htmlspecialchars(implode('|',$egGames)) ?>">
          </div>
        </div>
        <div class="row mb-4"><label class="col-sm-3 col-form-label form-label">Voice Chat</label><div class="col-sm-9"><div class="form-check form-switch"><input type="hidden" id="adminVoiceChatHidden" value="<?= $egVoice?'1':'0' ?>"><input class="form-check-input" type="checkbox" id="adminVoiceChatToggle" <?= $egVoice?'checked':'' ?> onchange="document.getElementById('adminVoiceChatHidden').value=this.checked?'1':'0'"><label class="form-check-label" for="adminVoiceChatToggle">Voice Chat available</label></div></div></div>
        <div class="row mb-4"><label class="col-sm-3 col-form-label form-label" for="adminProfilePassword">Password</label><div class="col-sm-9"><input type="password" class="form-control" id="adminProfilePassword" autocomplete="new-password" placeholder="Leave empty to not change"></div></div>
        <div class="row mb-0"><label class="col-sm-3 col-form-label form-label">Visibility</label><div class="col-sm-9"><div class="form-check form-switch"><input type="hidden" id="adminShowProfileHidden" value="<?= $egShow?'1':'0' ?>"><input class="form-check-input" type="checkbox" id="adminShowProfileToggle" <?= $egShow?'checked':'' ?> onchange="document.getElementById('adminShowProfileHidden').value=this.checked?'1':'0'"><label class="form-check-label" for="adminShowProfileToggle">Show profile on GG-Girls page</label></div></div></div>
      </div>
      <div class="card-footer d-flex justify-content-end">
        <button class="btn btn-primary" id="adminBtnSaveProfile">
          <span class="indicator-label"><i class="fa-solid fa-floppy-disk me-1"></i>Save Profile</span>
          <span class="indicator-progress" style="display:none"><span class="spinner-border spinner-border-sm align-middle me-1"></span>Saving...</span>
          <span class="indicator-success" style="display:none"><i class="fa-regular fa-circle-check me-1"></i>Saved!</span>
        </button>
      </div>
    </div>


    <div class="card">
      <div class="card-header"><h4 class="card-header-title">Rank Info <span class="text-muted fw-normal small">(optional)</span></h4></div>
      <div class="card-body">
        <?php
          // One rank row per game that actually has a ladder, driven by the same config
          // as the GG-Girl's own profile. lol/val/tft keep their dedicated columns; every
          // other game is saved into the game_ranks JSON column.
          $savedRanks   = function_exists('lb_egirl_game_ranks') ? lb_egirl_game_ranks($egirl) : [];
          $rankGames    = [];
          $rankLadders  = [];
          foreach ($pickerGames as $gk => $gv) {
            $tiers = $gv['tiers'] ?? (function_exists('lb_egirl_game_tiers') ? lb_egirl_game_tiers((string)$gk) : []);
            if (empty($tiers)) continue; // no ladder for this game — nothing to enter
            $rankGames[$gk] = ['label' => (string)($gv['label'] ?? $gk), 'icon' => (string)($gv['icon'] ?? ''), 'tiers' => $tiers];
            $ladder = [];
            foreach ($tiers as $tierName => $divCount) {
              $ladder[$tierName] = function_exists('lb_egirl_division_labels') ? lb_egirl_division_labels((int)$divCount) : [];
            }
            $rankLadders[$gk] = $ladder;
          }
        ?>
        <?php foreach ($rankGames as $gk => $gv):
          $saved   = $p($savedRanks[$gk] ?? '');
          $gkSafe  = htmlspecialchars((string)$gk, ENT_QUOTES);
          $divList = $rankLadders[$gk][$saved['tier']] ?? [];
        ?>
          <div class="rank-section mb-4 <?= in_array((string)$gk, $egGames, true) ? 'visible' : '' ?>" data-rank-game="<?= $gkSafe ?>">
            <label class="form-label d-flex align-items-center gap-2">
              <?php if (!empty($gv['icon'])): ?><img src="<?= htmlspecialchars($gv['icon'], ENT_QUOTES) ?>" style="width:18px;height:18px;object-fit:contain;" alt=""><?php endif; ?>
              <?= htmlspecialchars($gv['label'], ENT_QUOTES) ?> Rank
            </label>
            <div class="row g-2">
              <div class="col-sm-6">
                <select class="form-select js-rank-tier" data-rank-game="<?= $gkSafe ?>">
                  <option value="">— Not set —</option>
                  <?php foreach ($gv['tiers'] as $tierName => $divCount): ?>
                    <option value="<?= htmlspecialchars((string)$tierName, ENT_QUOTES) ?>" <?= $saved['tier'] === (string)$tierName ? 'selected' : '' ?>><?= htmlspecialchars((string)$tierName, ENT_QUOTES) ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
              <div class="col-sm-6 js-rank-div-wrap" data-rank-game="<?= $gkSafe ?>" style="<?= empty($divList) ? 'display:none' : '' ?>">
                <select class="form-select js-rank-div" data-rank-game="<?= $gkSafe ?>">
                  <option value="">— Division —</option>
                  <?php foreach ($divList as $d): ?>
                    <option value="<?= htmlspecialchars((string)$d, ENT_QUOTES) ?>" <?= $saved['div'] === (string)$d ? 'selected' : '' ?>><?= htmlspecialchars((string)$d, ENT_QUOTES) ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
        <p id="adminRankNoGame" class="text-muted small mb-0 <?= !empty($egGames) ? 'd-none' : '' ?>">Select games above to set ranks.</p>
      </div>
      <script>window.EG_RANK_LADDERS = <?= json_encode($rankLadders, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?>;</script>
    </div>
  </div>
</div>

<?php // ── PAGE: SERVICES ────────────────────────────────────────
elseif ($page === 'services'): ?>

<div class="card">
  <div class="card-header d-flex align-items-center justify-content-between flex-wrap gap-2">
    <h4 class="card-header-title mb-0">Services</h4>
    <input type="search" class="form-control form-control-sm" id="filterServices" placeholder="Search services…" style="max-width:220px;">
  </div>
  <?php if (!empty($services)): ?>
    <div class="table-responsive">
      <table class="table table-borderless table-thead-bordered table-nowrap table-align-middle card-table" id="tblServices">
        <thead class="thead-light"><tr><th>Title</th><th>Type</th><th>Duration</th><th>Voice</th><th class="text-end">Price</th></tr></thead>
        <tbody>
          <?php foreach ($services as $s): ?>
            <tr>
              <td class="fw-500"><?= htmlspecialchars($s['title']) ?></td>
              <td class="text-muted"><?= htmlspecialchars($s['type']) ?></td>
              <td class="text-muted"><?= (int)$s['unit_value'] ?> <?= htmlspecialchars($s['unit_type']) ?></td>
              <td><?= !empty($s['includes_voice'])?'<span class="badge bg-soft-success text-success"><i class="fa-solid fa-microphone me-1"></i>Yes</span>':'<span class="text-muted">—</span>' ?></td>
              <td class="text-end fw-500 text-success">€<?= number_format($s['price_cents']/100,2) ?></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php else: ?>
    <div class="card-body text-muted">No services yet.</div>
  <?php endif; ?>
</div>

<?php // ── PAGE: BOOKINGS ────────────────────────────────────────
elseif ($page === 'bookings'): ?>

<div class="card">
  <div class="card-header d-flex align-items-center justify-content-between flex-wrap gap-2">
    <h4 class="card-header-title mb-0">Bookings</h4>
    <div class="d-flex gap-2 flex-wrap align-items-center justify-content-end">
      <input type="search" class="form-control form-control-sm" id="filterBookings" placeholder="Search…" style="max-width:180px;">
      <div class="filter-pill-group" id="filterBookingStatus">
        <button type="button" class="filter-pill active" data-value="">All</button>
        <?php foreach(['PAID','IN_PROGRESS','COMPLETED','UNPAID','CANCELLED','REFUNDED'] as $st): ?>
          <button type="button" class="filter-pill" data-value="<?= $st ?>"><?= $st ?></button>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
  <?php if (!empty($orders)): ?>
    <div class="table-responsive">
      <table class="table table-borderless table-thead-bordered table-nowrap table-align-middle card-table" id="tblBookings">
        <thead class="thead-light"><tr><th>#</th><th>Client</th><th>Service</th><th class="text-end">Price</th><th>Status</th><th>Date</th><th class="text-end">Actions</th></tr></thead>
        <tbody>
          <?php foreach ($orders as $o):
            $sc=['PAID'=>'primary','IN_PROGRESS'=>'info','COMPLETED'=>'success','UNPAID'=>'danger','CANCELLED'=>'warning','REFUNDED'=>'secondary'][$o['status']]??'secondary'; ?>
            <tr>
              <td class="fw-500"><a href="<?= ADMN_URL ?>/egirl/order/<?= $o['id'] ?>">#<?= $o['id'] ?></a></td>
              <td><?= htmlspecialchars($o['client_username']??'—') ?></td>
              <td class="text-muted"><?= htmlspecialchars($o['service_title']??$o['service_type']??'—') ?></td>
              <td class="text-end fw-500">€<?= number_format(($o['price']??0)/100,2) ?></td>
              <td><span class="badge bg-soft-<?=$sc?> text-<?=$sc?>" data-status="<?= $o['status'] ?>"><?= $o['status'] ?></span></td>
              <td class="text-muted"><?= date('d.m.Y',strtotime($o['created_at'])) ?></td>
              <td class="text-end">
                <a href="<?= ADMN_URL ?>/egirl/order/<?= (int)$o['id'] ?>" class="btn btn-white btn-sm">
                  <i class="fa-duotone fa-eye me-1 fs-6"></i> View Booking
                </a>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php else: ?>
    <div class="card-body text-muted">No bookings yet.</div>
  <?php endif; ?>
</div>

<?php // ── PAGE: PAYMENTS ────────────────────────────────────────
elseif ($page === 'payments'): ?>

<div class="card">
  <div class="card-header d-flex align-items-center justify-content-between flex-wrap gap-2">
    <h4 class="card-header-title mb-0">Payment History</h4>
    <div class="d-flex gap-2 flex-wrap align-items-center justify-content-end">
      <input type="search" class="form-control form-control-sm" id="filterPayments" placeholder="Search…" style="max-width:180px;">
      <div class="filter-pill-group" id="filterPaymentType">
        <button type="button" class="filter-pill active" data-value="">All</button>
        <?php
          $types = array_values(array_filter(array_unique(array_column($payments, 'type'))));
          foreach ($types as $t): ?>
          <button type="button" class="filter-pill" data-value="<?= htmlspecialchars($t) ?>"><?= htmlspecialchars($t) ?></button>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
  <?php if (!empty($payments)): ?>
    <div class="table-responsive">
      <table class="table table-borderless table-thead-bordered table-nowrap table-align-middle card-table" id="tblPayments">
        <thead class="thead-light"><tr><th>Date</th><th>Type</th><th>Note</th><th class="text-end">Amount</th><th class="text-end">Balance After</th></tr></thead>
        <tbody>
          <?php foreach ($payments as $p): ?>
            <tr>
              <td class="text-muted"><?= date('d.m.Y',strtotime($p['created_at'])) ?></td>
              <td><span class="badge bg-soft-secondary text-secondary" data-type="<?= htmlspecialchars($p['type']) ?>"><?= htmlspecialchars($p['type']) ?></span></td>
              <td class="text-muted" style="max-width:280px;white-space:normal"><?= htmlspecialchars($p['note']??'—') ?></td>
              <td class="text-end fw-500 <?= $p['amount']>=0?'text-success':'text-danger' ?>"><?= $p['amount']>=0?'+':'' ?>€<?= number_format(abs($p['amount'])/100,2) ?></td>
              <td class="text-end text-muted"><?php if($p['balance_update']): [$old,$new]=explode('|',$p['balance_update']); ?>€<?= number_format((int)$new/100,2) ?><?php else: ?>—<?php endif; ?></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php else: ?>
    <div class="card-body text-muted">No payment history available.</div>
  <?php endif; ?>
</div>

<?php // ── PAGE: REVIEWS ─────────────────────────────────────────
elseif ($page === 'reviews'):
  $reviews = $reviews ?? [];
?>

<?php
  $hiddenReviewCount = 0;
  foreach ($reviews as $rev) { if ((int)($rev['approved'] ?? 0) !== 1) $hiddenReviewCount++; }
?>
<div class="card">
  <div class="card-header">
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
      <h4 class="card-header-title mb-0">
        Reviews
        <span class="badge bg-soft-secondary text-secondary ms-2"><?= count($reviews) ?></span>
        <?php if ($hiddenReviewCount > 0): ?>
          <span class="badge bg-soft-danger text-danger ms-1"><?= $hiddenReviewCount ?> hidden</span>
        <?php endif; ?>
      </h4>

      <div class="input-group input-group-merge input-group-flush" style="max-width:220px">
        <div class="input-group-prepend input-group-text"><i class="fa-duotone fa-search"></i></div>
        <input id="egReviewsSearch" type="search" class="form-control" placeholder="Search reviews">
      </div>
    </div>
  </div>

  <div class="filter-bar px-4 py-3 d-flex align-items-center gap-2 flex-wrap border-bottom">
    <label class="text-muted small mb-0 me-1">Status</label>
    <button type="button" class="filter-pill active" data-review-filter="">All</button>
    <button type="button" class="filter-pill" data-review-filter="visible"><span class="pill-dot" style="background:#00c9a7"></span> Visible</button>
    <button type="button" class="filter-pill" data-review-filter="hidden"><span class="pill-dot" style="background:#ed4c78"></span> Hidden</button>
  </div>

  <?php if (!empty($reviews)): ?>
    <div class="table-responsive datatable-custom">
      <table class="js-datatable table table-borderless table-thead-bordered table-nowrap table-align-middle card-table"
             id="egReviewsTable"
             data-hs-datatables-options='{
                 "order": [[3, "desc"]],
                 "search": "#egReviewsSearch",
                 "isResponsive": false,
                 "isShowPaging": false,
                 "pagination": "egReviewsPagination",
                 "entries": "#egReviewsEntries",
                 "info": {"totalQty": "#egReviewsTotalQty"}
             }'>
        <thead class="thead-light">
          <tr>
            <th>Client</th>
            <th>Rating</th>
            <th>Comment</th>
            <th>Date</th>
            <th class="text-center">Status</th>
            <th class="text-end">Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($reviews as $rev):
            $revId       = (int)($rev['id'] ?? 0);
            $revRating   = max(0, min(5, (int)($rev['rating'] ?? 0)));
            $isHidden    = (int)($rev['approved'] ?? 0) !== 1;
            $revStatus   = $isHidden ? 'hidden' : 'visible';
            $revClient   = trim((string)($rev['client_username'] ?? '')) ?: 'Guest';
            $revComment  = trim((string)($rev['comment'] ?? ''));
            $revStamp    = !empty($rev['created_at']) ? strtotime((string)$rev['created_at']) : 0;
          ?>
            <tr data-review-status="<?= $revStatus ?>">
              <td>
                <?php
                  $revIcon     = trim((string)($rev['client_icon'] ?? ''));
                  $revClientId = (int)($rev['client_id'] ?? 0);
                ?>
                <?php if ($revClientId > 0): ?><a class="d-flex align-items-center gap-2 text-reset" href="<?= ADMN_URL ?>/client/<?= $revClientId ?>"><?php else: ?><div class="d-flex align-items-center gap-2"><?php endif; ?>
                  <?php if ($revIcon !== ''): ?>
                    <img class="eg-review-avatar" src="<?= htmlspecialchars($revIcon, ENT_QUOTES) ?>" alt="" loading="lazy" style="width:30px;height:30px;">
                  <?php endif; ?>
                  <span class="fw-500"><?= htmlspecialchars($revClient, ENT_QUOTES) ?></span>
                <?php if ($revClientId > 0): ?></a><?php else: ?></div><?php endif; ?>
              </td>

              <td data-order="<?= $revRating ?>">
                <div class="d-flex align-items-center gap-1">
                  <?php for ($i = 1; $i <= 5; $i++): ?>
                    <i class="fa-solid fa-star <?= $i <= $revRating ? 'text-warning' : 'text-muted opacity-25' ?>"></i>
                  <?php endfor; ?>
                  <span class="ms-2 fw-500"><?= $revRating ?>/5</span>
                </div>
              </td>

              <td class="text-muted" style="max-width:420px;white-space:normal;">
                <?= $revComment !== '' ? nl2br(htmlspecialchars($revComment, ENT_QUOTES)) : '—' ?>
              </td>

              <td class="text-muted" data-order="<?= $revStamp ?>">
                <?= $revStamp ? date('d.m.Y H:i', $revStamp) : '—' ?>
              </td>

              <td class="text-center">
                <?php if ($isHidden): ?>
                  <span class="badge bg-soft-danger text-danger">Hidden</span>
                <?php else: ?>
                  <span class="badge bg-soft-success text-success">Visible</span>
                <?php endif; ?>
              </td>

              <td class="text-end">
                <div class="d-inline-flex gap-2">
                  <?php if ($isHidden): ?>
                    <button type="button" class="btn btn-white btn-sm js-egirl-review" data-review="<?= $revId ?>" data-approved="1" title="Show review">
                      <i class="fa-duotone fa-eye"></i>
                    </button>
                  <?php else: ?>
                    <button type="button" class="btn btn-white btn-sm js-egirl-review" data-review="<?= $revId ?>" data-approved="0" title="Hide review">
                      <i class="fa-duotone fa-eye-slash"></i>
                    </button>
                  <?php endif; ?>
                  <button type="button" class="btn btn-danger btn-sm js-egirl-review" data-review="<?= $revId ?>" data-approved="-1" title="Delete review">
                    <i class="fa-duotone fa-trash"></i>
                  </button>
                </div>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>

    <div class="card-footer">
      <div class="row justify-content-center justify-content-sm-between align-items-sm-center">
        <div class="col-sm mb-2 mb-sm-0">
          <div class="d-flex align-items-center gap-2">
            <span>Showing:</span>
            <div class="tom-select-custom">
              <select id="egReviewsEntries" class="js-select form-select form-select-borderless w-auto"
                      data-hs-tom-select-options='{"searchInDropdown":false,"hideSearch":true}'>
                <option value="10" selected>10</option>
                <option value="25">25</option>
                <option value="50">50</option>
              </select>
            </div>
            <span class="text-secondary">of</span>
            <span id="egReviewsTotalQty"></span>
          </div>
        </div>
        <div class="col-sm-auto"><nav id="egReviewsPagination"></nav></div>
      </div>
    </div>
  <?php else: ?>
    <div class="card-body text-muted">No reviews available.</div>
  <?php endif; ?>
</div>

<script>
$(document).on('ready', function () {
    if (!document.getElementById('egReviewsTable')) return;
    HSCore.components.HSDatatables.init($('#egReviewsTable'), {
        language: { zeroRecords: '<div class="text-center p-4 text-muted">No reviews match the current filter.</div>' }
    });

    var dt = $('#egReviewsTable').DataTable();
    var activeFilter = '';

    $.fn.dataTable.ext.search.push(function (settings, data, dataIndex) {
        if (settings.nTable.id !== 'egReviewsTable') return true;
        if (!activeFilter) return true;
        return ($(dt.row(dataIndex).node()).data('review-status') || '') === activeFilter;
    });

    $('[data-review-filter]').on('click', function () {
        $('[data-review-filter]').removeClass('active');
        $(this).addClass('active');
        activeFilter = $(this).data('review-filter');
        dt.draw();
    });
});
</script>

<?php endif; ?>

<!-- Adjust Balance Modal -->
<div class="modal fade" id="addMoneyModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <form class="ajax-form" action="<?= AJAX_URL ?>" method="POST">
        <input type="hidden" name="action" value="admin_adjust_egirl_balance">
        <input type="hidden" name="egirl_id" value="<?= $egirlId ?>">
        <input type="hidden" name="mode" value="add">
        <div class="modal-header">
          <h5 class="modal-title">Add Money</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <label class="form-label">Reason</label>
            <select class="form-select" name="reason">
              <option value="order_completion">Completed Order</option>
              <option value="private_order">Private Order</option>
              <option value="payment_error">Payment Error</option>
              <option value="client_tip">Client Tip</option>
              <option value="other">Other</option>
            </select>
          </div>
          <div class="alert alert-soft-info mb-4">
            Current balance: <strong>€<?= $balanceEuro ?></strong>
          </div>
          <div class="mb-3">
            <label class="form-label">Amount (€)</label>
            <input type="number" min="0.01" step="0.01" class="form-control" name="amount" placeholder="0.00" required>
          </div>
          <div class="mb-0">
            <label class="form-label">Note</label>
            <input type="text" class="form-control" name="note" placeholder="Reason for adjustment">
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-white" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-success">Add Money</button>
        </div>
      </form>
    </div>
  </div>
</div>

<style>
  .seller-profile-banner {
    min-height: 120px;
    background:
      radial-gradient(circle at top left, rgba(255,255,255,.12), transparent 24%),
      radial-gradient(circle at bottom right, rgba(59,130,246,.20), transparent 28%),
      linear-gradient(90deg, #0b1020 0%, #1d2b64 52%, #111827 100%);
  }
  .seller-profile-banner-glow {
    position: absolute; inset: 0;
    background: linear-gradient(90deg, rgba(99,102,241,.32), rgba(124,58,237,.15), rgba(14,165,233,.22));
  }
  .seller-profile-meta { margin-top: -48px; }
  .seller-profile-avatar {
    width: 96px; height: 96px; border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    font-size: 2rem; font-weight: 800; color: #60a5fa;
    background: linear-gradient(180deg, #2b3146 0%, #232938 100%);
    border: 4px solid var(--bs-card-bg, #1f2937);
    box-shadow: 0 12px 30px rgba(0,0,0,.28); overflow: hidden;
  }
  .seller-profile-avatar img { width: 100%; height: 100%; object-fit: cover; border-radius: 50%; }
  .seller-stat-pill {
    height: 100%; border: 1px solid rgba(255,255,255,.08);
    border-radius: .85rem; background: rgba(255,255,255,.03);
    padding: .85rem 1rem; text-align: center;
  }
  .seller-stat-value { font-size: 1.2rem; font-weight: 700; line-height: 1.1; }
  .seller-stat-label { font-size: .72rem; letter-spacing: .04em; text-transform: uppercase; color: var(--bs-secondary-color, #9ca3af); margin-top: .25rem; }
  .seller-nav-tabs { gap: .6rem; border-bottom: 0; }
  .seller-nav-tabs .nav-link {
    border: 0; border-bottom: 2px solid transparent;
    color: var(--bs-secondary-color, #9ca3af);
    padding: .7rem .15rem; border-radius: 0; font-weight: 500; background: transparent;
  }
  .seller-nav-tabs .nav-link.active,
  .seller-nav-tabs .nav-link:hover {
    color: var(--bs-white, #fff);
    border-bottom-color: var(--bs-primary, #6c5ce7);
    background: transparent;
  }
  @media (max-width: 991.98px) {
    .seller-profile-meta { margin-top: -36px; }
    .seller-profile-avatar { width: 84px; height: 84px; font-size: 1.7rem; }
  }
</style>

<script>
function egHandleResponse(res) {
    try { res = (typeof res === 'string') ? JSON.parse(res) : res; } catch(e) {}
    if (res.sendToast && typeof create_toast === 'function') {
        create_toast(res.sendToast.type || 'success', res.sendToast.title || '', res.sendToast.message || '');
    }
    if (res.playSound && typeof play_sound === 'function') {
        play_sound(res.playSound);
    }
    if (res.redirectUrl) {
        setTimeout(() => { window.location.href = res.redirectUrl; }, 800);
    } else if (res.refreshPage || res.success) {
        setTimeout(() => location.reload(), 800);
    }
}

// These actions now live in the Actions dropdown as <a href="#">, so every handler
// has to swallow the click instead of letting the page jump to the top.
// Verify
document.querySelectorAll('.js-egirl-verify').forEach(btn => {
  btn.addEventListener('click', function (e) {
    e.preventDefault();
    if (!confirm('Verify this E-Girl and send login credentials?')) return;
    $.post('<?= AJAX_URL ?>', { action: 'admin_verify_egirl', egirl_id: this.dataset.id }, egHandleResponse);
  });
});
// Decline
document.querySelectorAll('.js-egirl-decline').forEach(btn => {
  btn.addEventListener('click', function (e) {
    e.preventDefault();
    if (!confirm('Decline and DELETE this E-Girl application? This cannot be undone.')) return;
    $.post('<?= AJAX_URL ?>', { action: 'admin_decline_egirl', egirl_id: this.dataset.id }, egHandleResponse);
  });
});
// Resend Login
document.querySelectorAll('.js-egirl-resend').forEach(btn => {
  btn.addEventListener('click', function (e) {
    e.preventDefault();
    if (!confirm('Resend login email with a new password?')) return;
    $.post('<?= AJAX_URL ?>', { action: 'admin_resend_egirl_login', egirl_id: this.dataset.id }, egHandleResponse);
  });
});
// Ban / Unban
document.querySelectorAll('.js-egirl-ban').forEach(btn => {
  btn.addEventListener('click', function (e) {
    e.preventDefault();
    const ban = this.dataset.ban;
    if (ban == 1 && !confirm('Ban this E-Girl?')) return;
    $.post('<?= AJAX_URL ?>', { action: 'admin_egirl_ban', egirl_id: this.dataset.id, ban }, egHandleResponse);
  });
});
// Reviews: approve / delete
document.querySelectorAll('.js-egirl-review').forEach(btn => {
  btn.addEventListener('click', function () {
    const approved = this.dataset.approved;
    if (approved == -1 && !confirm('Delete this review? This cannot be undone.')) return;
    $.post('<?= AJAX_URL ?>', { action: 'admin_egirl_review_update', review_id: this.dataset.review, approved }, function () {
      location.reload();
    });
  });
});

// Revenue Share
(function () {
  const AJAX = '<?= AJAX_URL ?>', EID = <?= (int)$egirlId ?>;
  const input = document.getElementById('egirlCutInput');
  const barEg  = document.getElementById('cutBarEg');
  if (!input) return;
  input.addEventListener('input', function () {
    const pct = parseFloat(this.value) || 60;
    if (barEg) barEg.style.width = Math.min(99, Math.max(1, pct)) + '%';
  });
  document.getElementById('btnSaveCut')?.addEventListener('click', function () {
    const val = input.value.trim();
    if (val !== '' && (parseFloat(val) < 1 || parseFloat(val) > 99)) { alert('Cut must be between 1% and 99%.'); return; }
    $.post(AJAX, { action: 'admin_egirl_set_cut', egirl_id: EID, cut_percent: val }, egHandleResponse);
  });
  document.getElementById('btnResetCut')?.addEventListener('click', function (e) {
    e.preventDefault();
    $.post(AJAX, { action: 'admin_egirl_set_cut', egirl_id: EID, cut_percent: '' }, egHandleResponse);
  });
})();

// Scroll-spy tab activation
(function () {
  function ready(fn) { if (document.readyState !== 'loading') fn(); else document.addEventListener('DOMContentLoaded', fn); }
  ready(function () {
    var tabs = document.querySelectorAll('#sellerViewTabs .nav-link');
    function setActive(link) { tabs.forEach(t => t.classList.remove('active')); if (link) link.classList.add('active'); }
    tabs.forEach(function (link) {
      link.addEventListener('click', function (e) {
        var targetId = link.getAttribute('href');
        if (!targetId || targetId.charAt(0) !== '#') return;
        var target = document.querySelector(targetId);
        if (!target) return;
        e.preventDefault(); setActive(link);
        target.scrollIntoView({ behavior: 'smooth', block: 'start' });
        if (history.replaceState) history.replaceState(null, '', targetId);
      });
    });
    var sections = ['#seller-overview','#seller-revenue','#seller-accounts','#seller-payouts','#seller-payments']
      .map(s => document.querySelector(s)).filter(Boolean);
    if ('IntersectionObserver' in window && sections.length) {
      var obs = new IntersectionObserver(function (entries) {
        var visible = entries.filter(e => e.isIntersecting).sort((a,b) => b.intersectionRatio - a.intersectionRatio);
        if (!visible.length) return;
        var id = '#' + visible[0].target.id;
        setActive(document.querySelector('#sellerViewTabs .nav-link[href="' + id + '"]'));
      }, { rootMargin: '-18% 0px -65% 0px', threshold: [0.1, 0.25, 0.5] });
      sections.forEach(s => obs.observe(s));
    }
    if (window.location.hash) {
      var initialLink = document.querySelector('#sellerViewTabs .nav-link[href="' + window.location.hash + '"]');
      if (initialLink) setActive(initialLink);
    }
  });
})();
</script>

<script src="<?= ASSET_URL ?>/origin/dash/vendor/tom-select/dist/js/tom-select.complete.min.js"></script>
<script>
const AJAX_URL_EGIRL = '<?= AJAX_URL ?>';
const EGIRL_ID = <?= (int)$egirlId ?>;
const NO_DIV = ['Master','Grandmaster','Challenger','Immortal','Radiant'];

// ── Revenue Share ──
(function(){
    const input=document.getElementById('egirlCutInput');
    const bar=document.getElementById('cutBarEg');
    if(!input) return;
    input.addEventListener('input',function(){const p=parseFloat(this.value)||60;if(bar)bar.style.width=Math.min(99,Math.max(1,p))+'%';});
    document.getElementById('btnSaveCut')?.addEventListener('click',function(){
        const v=input.value.trim();
        if(v!==''&&(parseFloat(v)<1||parseFloat(v)>99)){alert('Cut must be 1–99%.');return;}
        $.post(AJAX_URL_EGIRL,{action:'admin_egirl_set_cut',egirl_id:EGIRL_ID,cut_percent:v},egHandleResponse);
    });
    document.getElementById('btnResetCut')?.addEventListener('click',function(e){
        e.preventDefault();
        $.post(AJAX_URL_EGIRL,{action:'admin_egirl_set_cut',egirl_id:EGIRL_ID,cut_percent:''},egHandleResponse);
    });
})();

// ── Profile Page ──
(function(){
    const el = document.getElementById('adminProfileLanguages');
    if (!el) return;

    const tsLangs = new TomSelect(el, {
        plugins:['remove_button'], persist:false, create:false,
        render:{
            option:function(data,escape){
                const f=el.querySelector(`option[value="${data.value}"]`)?.dataset?.flag||'';
                const img=f?`<img src="${escape(f)}" style="width:18px;height:12px;object-fit:cover;border-radius:2px;margin-right:7px;vertical-align:middle;" onerror="this.style.display='none'">`:'';
                return `<div style="display:flex;align-items:center;">${img}<span>${escape(data.text)}</span></div>`;
            },
            item:function(data,escape){
                const f=document.querySelector(`#adminProfileLanguages option[value="${data.value}"]`)?.dataset?.flag||'';
                const img=f?`<img src="${escape(f)}" style="width:16px;height:11px;object-fit:cover;border-radius:2px;margin-right:5px;vertical-align:middle;" onerror="this.style.display='none'">`:'';
                return `<div style="display:flex;align-items:center;">${img}<span>${escape(data.text)}</span></div>`;
            }
        }
    });

    const RANK_LADDERS = window.EG_RANK_LADDERS || {};

    window.adminToggleGame = function(chip){
        chip.classList.toggle('selected');
        const games=[...document.querySelectorAll('#adminGamePicker .game-chip.selected')].map(c=>c.dataset.game);
        document.getElementById('adminProfileGames').value=games.join('|');
        // Show the rank row of every selected game that has a ladder.
        document.querySelectorAll('.rank-section[data-rank-game]').forEach(sec=>{
            sec.classList.toggle('visible',games.includes(sec.dataset.rankGame));
        });
        const ng=document.getElementById('adminRankNoGame');
        if(ng) ng.classList.toggle('d-none',games.length>0);
    };

    // Division options depend on the picked tier, e.g. LoL Master has none at all.
    function syncDivisions(game){
        const tierSel=document.querySelector(`.js-rank-tier[data-rank-game="${game}"]`);
        const divSel =document.querySelector(`.js-rank-div[data-rank-game="${game}"]`);
        const wrap   =document.querySelector(`.js-rank-div-wrap[data-rank-game="${game}"]`);
        if(!tierSel||!divSel||!wrap) return;
        const labels=(RANK_LADDERS[game]||{})[tierSel.value]||[];
        const previous=divSel.value;
        divSel.innerHTML='<option value="">— Division —</option>'+labels.map(l=>`<option value="${l}">${l}</option>`).join('');
        if(labels.includes(previous)) divSel.value=previous;
        wrap.style.display=labels.length?'':'none';
    }

    document.querySelectorAll('.js-rank-tier[data-rank-game]').forEach(sel=>{
        sel.addEventListener('change',()=>syncDivisions(sel.dataset.rankGame));
    });

    function getRank(g){
        const tier=document.querySelector(`.js-rank-tier[data-rank-game="${g}"]`)?.value||'';
        if(!tier) return '';
        const labels=(RANK_LADDERS[g]||{})[tier]||[];
        if(!labels.length) return tier;
        const div=document.querySelector(`.js-rank-div[data-rank-game="${g}"]`)?.value||'';
        return div?tier+' '+div:tier;
    }

    // Ranks of every game other than lol/val/tft go into the game_ranks JSON column.
    function getExtraRanks(){
        const out={};
        document.querySelectorAll('.rank-section[data-rank-game]').forEach(sec=>{
            const g=sec.dataset.rankGame;
            if(['lol','val','tft'].includes(g)) return;
            const r=getRank(g);
            if(r) out[g]=r;
        });
        return out;
    }

    function btnLoad(btn){btn.disabled=true;btn.querySelector('.indicator-label')&&(btn.querySelector('.indicator-label').style.display='none');btn.querySelector('.indicator-progress')&&(btn.querySelector('.indicator-progress').style.display='inline');}
    function btnOk(btn){btn.querySelector('.indicator-progress')&&(btn.querySelector('.indicator-progress').style.display='none');btn.querySelector('.indicator-success')&&(btn.querySelector('.indicator-success').style.display='inline');setTimeout(()=>{btn.disabled=false;btn.querySelector('.indicator-label')&&(btn.querySelector('.indicator-label').style.display='inline');btn.querySelector('.indicator-success')&&(btn.querySelector('.indicator-success').style.display='none');},2200);}

    document.getElementById('adminBtnSaveProfile')?.addEventListener('click',function(){
        const btn=this; btnLoad(btn);
        $.post(AJAX_URL_EGIRL,{
            action:'admin_egirl_save_profile',egirl_id:EGIRL_ID,
            bio:document.getElementById('adminProfileBio')?.value||'',
            languages:tsLangs.getValue().join('|'),
            country:document.getElementById('adminProfileCountry')?.value||'',
            timezone:document.getElementById('adminProfileTimezone')?.value||'',
            discord:document.getElementById('adminProfileDiscord')?.value||'',
            password:document.getElementById('adminProfilePassword')?.value||'',
            games:document.getElementById('adminProfileGames')?.value||'',
            lol_rank:getRank('lol'),val_rank:getRank('val'),tft_rank:getRank('tft'),
            game_ranks:JSON.stringify(getExtraRanks()),
            voice_chat:document.getElementById('adminVoiceChatHidden')?.value||'0',
            show_profile:document.getElementById('adminShowProfileHidden')?.value||'0',
        },function(res){
            if(typeof res==='string')try{res=JSON.parse(res);}catch(e){}
            if(res?.success){
                const pw=document.getElementById('adminProfilePassword');
                if(pw) pw.value='';
                btnOk(btn);
            }else{btn.disabled=false;btn.querySelector('.indicator-label').style.display='inline';btn.querySelector('.indicator-progress').style.display='none';}
            if(res?.sendToast&&typeof create_toast==='function')create_toast(res.sendToast.type||'success',res.sendToast.title||'',res.sendToast.message||'');
        }).fail(()=>{btn.disabled=false;});
    });
})();

// ── Table filters ──
function tableFilter(inputId, tableId, colIndices) {
    const input = document.getElementById(inputId);
    const table = document.getElementById(tableId);
    if (!input || !table) return;
    input.addEventListener('input', function() {
        const q = this.value.toLowerCase();
        table.querySelectorAll('tbody tr').forEach(tr => {
            const text = colIndices.map(i => tr.cells[i]?.textContent||'').join(' ').toLowerCase();
            tr.style.display = text.includes(q) ? '' : 'none';
        });
    });
}
function pillFilter(groupId, tableId, colIndex) {
    const group = document.getElementById(groupId);
    const table = document.getElementById(tableId);
    if (!group || !table) return;
    group.querySelectorAll('.filter-pill').forEach(btn => {
        btn.addEventListener('click', function() {
            const q = (this.dataset.value || '').toLowerCase();
            group.querySelectorAll('.filter-pill').forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            table.querySelectorAll('tbody tr').forEach(tr => {
                const text = (tr.cells[colIndex]?.textContent || '').toLowerCase();
                tr.style.display = (!q || text.includes(q)) ? '' : 'none';
            });
        });
    });
}

tableFilter('filterServices', 'tblServices', [0,1]);
tableFilter('filterBookings', 'tblBookings', [0,1,2]);
pillFilter('filterBookingStatus', 'tblBookings', 4);
tableFilter('filterPayments', 'tblPayments', [1,2]);
pillFilter('filterPaymentType', 'tblPayments', 1);
</script>

<div class="modal fade" id="fineAccountModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <form class="ajax-form" action="<?= AJAX_URL ?>" method="POST">
        <input type="hidden" name="action" value="admin_adjust_egirl_balance">
        <input type="hidden" name="egirl_id" value="<?= $egirlId ?>">
        <input type="hidden" name="mode" value="fine">
        <div class="modal-header">
          <h5 class="modal-title"><i class="fa-duotone fa-triangle-exclamation me-2 text-danger"></i>Fine E-Girl</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="alert alert-soft-danger mb-4">The fine is deducted from the current balance immediately.</div>
          <div class="mb-3"><label class="form-label">Reason</label><input type="text" class="form-control" name="reason" placeholder="Reason for the fine" required></div>
          <div class="mb-3"><label class="form-label">Note <span class="text-muted">(optional)</span></label><textarea class="form-control" name="note" rows="2" placeholder="Additional details"></textarea></div>
          <div><label class="form-label">Fine Amount (€)</label><input type="number" min="0.01" step="0.01" class="form-control" name="amount" placeholder="0.00" required></div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-white" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-danger"><i class="fa-solid fa-gavel me-1"></i>Apply Fine</button>
        </div>
      </form>
    </div>
  </div>
</div>
