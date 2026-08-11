<?php
require_once __DIR__ . '/_seller_rank.php';
if (!isset($spageActiveTab) || !is_string($spageActiveTab) || $spageActiveTab === '') {
    $uri = strtolower((string)($_SERVER['REQUEST_URI'] ?? ''));
    if (strpos($uri, 'personal-details') !== false)    $spageActiveTab = 'personal-details';
    elseif (strpos($uri, 'payout-requests') !== false) $spageActiveTab = 'payout';
    elseif (strpos($uri, 'payout') !== false)          $spageActiveTab = 'payout';
    elseif (strpos($uri, 'referrals') !== false)       $spageActiveTab = 'referrals';
    else                                                $spageActiveTab = 'profile';
}
?>

<?= $this->start('styles') ?>
<style>
/* ════════════════════════════════════════════
   SELLER AREA — GLOBAL DESIGN TOKENS
   Loaded on every seller page via _shared.php
   ════════════════════════════════════════════ */
:root {
  --s-text:    rgba(255,255,255,.94);
  --s-sub:     rgba(255,255,255,.70);
  --s-muted:   rgba(255,255,255,.50);
  --s-border:  rgba(255,255,255,.07);
  --s-bg:      rgba(255,255,255,.025);
  --s-purple:  #6d5cff;
  --s-purple2: #b05cff;
  --s-green:   #4ade80;
  --s-amber:   #fbbf24;
  --s-red:     #fb7185;
  --s-blue:    #60a5fa;
}

/* ── Cards ── */
.s-card {
  background: var(--bs-card-bg) !important;
  border: var(--bs-card-border-width) solid var(--bs-card-border-color) !important;
  border-radius: 22px !important;
  box-shadow: none !important;
}
.s-card::before { display: none !important; }

/* ── Section title ── */
.s-section-title {
  font-size: .72rem; font-weight: 900; color: #9f8cff;
  text-transform: uppercase; letter-spacing: .12em; margin-bottom: 14px;
}

/* ── Divider ── */
.s-divider { height: 1px; background: var(--s-border); margin: 16px 0; }

/* ── Overview item (sidebar rows) ── */
.s-item {
  display: flex; align-items: center; gap: 11px;
  padding: 10px 12px; border: 1px solid var(--s-border);
  border-radius: 12px; background: var(--s-bg); margin-bottom: 6px;
}
.s-item-icon {
  width: 32px; height: 32px; border-radius: 9px; flex-shrink: 0;
  display: flex; align-items: center; justify-content: center;
  background: linear-gradient(135deg,rgba(109,92,255,.22),rgba(176,92,255,.12));
  border: 1px solid rgba(109,92,255,.18); color: #fff; font-size: .8rem;
}
.s-item-key { font-size: .73rem; color: var(--s-muted); margin-bottom: 1px; }
.s-item-val { font-size: .83rem; font-weight: 800; color: var(--s-text); }

/* ── Badges ── */
.s-badge {
  font-size: .66rem; font-weight: 800; padding: 2px 8px; border-radius: 99px;
  display: inline-flex; align-items: center; gap: .3rem;
}
.s-badge-ok      { background: rgba(74,222,128,.10);  border: 1px solid rgba(74,222,128,.22);  color: #4ade80; }
.s-badge-warn    { background: rgba(251,191,36,.08);  border: 1px solid rgba(251,191,36,.22);  color: #fbbf24; }
.s-badge-purple  { background: rgba(109,92,255,.14);  border: 1px solid rgba(109,92,255,.28);  color: #a78fff; }
.s-badge-red     { background: rgba(251,113,133,.10); border: 1px solid rgba(251,113,133,.22); color: #fb7185; }
.s-badge-neutral { background: rgba(255,255,255,.06); border: 1px solid rgba(255,255,255,.10); color: var(--s-muted); }

/* ── Form fields ── */
.s-form label, .s-label {
  display: block; font-size: .73rem; font-weight: 800;
  color: var(--s-muted); text-transform: uppercase; letter-spacing: .08em; margin-bottom: 6px;
}
.s-form .form-control, .s-form .form-select {
  background: rgba(255,255,255,.03) !important;
  border: 1px solid rgba(255,255,255,.09) !important;
  border-radius: 11px !important;
  color: var(--s-text) !important;
  padding: 9px 13px !important;
  font-size: .87rem !important;
  transition: border-color .15s, box-shadow .15s !important;
}
.s-form .form-control:focus, .s-form .form-select:focus {
  border-color: rgba(109,92,255,.5) !important;
  box-shadow: 0 0 0 3px rgba(109,92,255,.10) !important;
  background: rgba(109,92,255,.03) !important;
  outline: none !important;
}
.s-form .form-control::placeholder { color: rgba(255,255,255,.22) !important; }
.s-form .form-control[readonly] { opacity: .6; cursor: not-allowed; pointer-events: none; }
.s-form .form-control[disabled] { opacity: .4; cursor: not-allowed; }
.s-invalid { border-color: rgba(239,68,68,.55) !important; box-shadow: 0 0 0 3px rgba(239,68,68,.10) !important; }
.s-error   { color: var(--s-red); font-size: .74rem; margin-top: 4px; }

/* ── Primary button ── */
.s-btn-primary, .btn-primary {
  background: linear-gradient(135deg, var(--s-purple), var(--s-purple2)) !important;
  border: none !important; border-radius: 12px !important; font-weight: 900 !important;
  transition: opacity .15s, transform .12s !important;
}
.s-btn-primary:hover, .btn-primary:hover { opacity: .88; transform: translateY(-1px); }
.s-btn-primary:disabled, .btn-primary:disabled { opacity: .55; transform: none !important; }

/* ── Ghost / danger buttons ── */
.s-btn { background: rgba(255,255,255,.04); border: 1px solid rgba(255,255,255,.10);
  color: rgba(255,255,255,.75); font-size: .82rem; border-radius: 9px; padding: 5px 13px; cursor: pointer; }
.s-btn:hover { background: rgba(255,255,255,.08); color: #fff; }
.s-btn-danger { background: rgba(251,113,133,.08); border: 1px solid rgba(251,113,133,.22);
  color: var(--s-red); font-size: .82rem; border-radius: 9px; padding: 5px 13px; cursor: pointer; }
.s-btn-danger:hover { background: rgba(251,113,133,.14); }

/* ── Progress bar ── */
.s-progress-track { height: 6px; background: rgba(255,255,255,.06); border-radius: 99px; overflow: hidden; }
.s-progress-fill  { height: 100%; border-radius: 99px; background: linear-gradient(90deg, var(--s-purple), var(--s-purple2)); transition: width .4s ease; }

/* ── Feed (activity) ── */
.s-feed-item { display: flex; align-items: center; gap: 12px; padding: 10px 0; border-bottom: 1px solid var(--s-border); }
.s-feed-item:last-child { border-bottom: 0; }
.s-feed-dot { width: 8px; height: 8px; border-radius: 50%; flex-shrink: 0; }
.s-feed-dot-green { background: var(--s-green); }
.s-feed-dot-red   { background: var(--s-red); }
.s-feed-title { font-size: .86rem; font-weight: 700; color: var(--s-text); }
.s-feed-meta  { font-size: .76rem; color: var(--s-muted); }
.s-feed-badge { margin-left: auto; flex-shrink: 0; font-size: .8rem; font-weight: 800; }
.s-feed-badge-green { color: var(--s-green); }
.s-feed-badge-red   { color: var(--s-red); }

/* ── Table ── */
.s-table thead th { color: var(--s-muted); font-size: .72rem; font-weight: 800; text-transform: uppercase; letter-spacing: .08em; border-bottom: 1px solid var(--s-border) !important; }
.s-table tbody td { border-bottom: 1px solid var(--s-border) !important; color: var(--s-text); vertical-align: middle; }
.s-table tbody tr:last-child td { border-bottom: 0 !important; }

/* ── Modal ── */
.s-modal-overlay { position: fixed; inset: 0; display: none; align-items: center; justify-content: center;
  background: rgba(0,0,0,.55); backdrop-filter: blur(6px); z-index: 2000; padding: 18px; }
.s-modal-overlay.open { display: flex; }
.s-modal { width: min(520px,100%); background: rgba(20,22,30,.98); border: 1px solid var(--s-border);
  box-shadow: 0 24px 60px rgba(0,0,0,.65); border-radius: 18px; overflow: hidden; }
.s-modal-hd { display: flex; align-items: flex-start; justify-content: space-between; gap: 12px;
  padding: 16px 18px 14px; border-bottom: 1px solid var(--s-border); }
.s-modal-title { font-weight: 900; font-size: 1rem; color: var(--s-text); }
.s-modal-sub   { font-size: .78rem; color: var(--s-muted); margin-top: 2px; }
.s-modal-body  { padding: 14px 18px; color: rgba(255,255,255,.82); font-size: .87rem; }
.s-modal-ft    { display: flex; justify-content: flex-end; gap: 10px; padding: 12px 18px 16px; border-top: 1px solid var(--s-border); }
.s-modal-icon  { width: 38px; height: 38px; border-radius: 10px; flex-shrink: 0; display: flex; align-items: center; justify-content: center;
  background: rgba(255,255,255,.05); border: 1px solid rgba(255,255,255,.09); }
.s-modal-x { width: 34px; height: 34px; border-radius: 9px; border: 1px solid rgba(255,255,255,.09);
  background: rgba(255,255,255,.04); color: rgba(255,255,255,.65); display: flex; align-items: center; justify-content: center; cursor: pointer; }
.s-modal-x:hover { background: rgba(255,255,255,.08); }

/* ── Hero — identical to dashboard sdash-hero-* ── */
.spage-hero-card { background: var(--bs-card-bg) !important; border-radius: 22px !important; overflow: hidden; }
.spage-hero-banner {
  min-height: 160px; background-color: #111a44;
  background-image: radial-gradient(circle at 8% 0%,rgba(255,255,255,.10),transparent 22%),
                    radial-gradient(circle at 92% 100%,rgba(56,189,248,.12),transparent 26%),
                    linear-gradient(90deg,#111a44 0%,#312e81 48%,#0f172a 100%);
  background-size: cover; background-position: center; position: relative; overflow: hidden;
}
.spage-hero-banner-glow  { position:absolute;inset:0;background:linear-gradient(90deg,rgba(129,140,248,.18),rgba(139,92,246,.12),rgba(14,165,233,.12));mix-blend-mode:screen;pointer-events:none; }
.spage-hero-banner-noise { position:absolute;inset:0;background:linear-gradient(180deg,rgba(10,14,29,.0),rgba(10,14,29,.28) 100%);pointer-events:none; }
/* Banner edit button — bottom-right like dashboard */
.spage-banner-edit-btn { position:absolute;bottom:12px;right:14px;display:inline-flex;align-items:center;gap:.4rem;padding:.4rem .9rem;font-size:.8rem;font-weight:800;background:rgba(0,0,0,.45);border:1px solid rgba(255,255,255,.18);border-radius:8px;color:rgba(255,255,255,.85);cursor:pointer;backdrop-filter:blur(8px);transition:background .15s,border-color .15s,color .15s; }
.spage-banner-edit-btn:hover { background:rgba(109,92,255,.35);border-color:rgba(109,92,255,.55);color:#fff; }
.spage-hero-body { background: var(--bs-card-bg); }
/* Avatar — same size/style as dashboard */
.spage-avatar-wrap { position:relative;cursor:pointer;display:inline-block;flex-shrink:0; }
.spage-avatar { width:72px;height:72px;border-radius:50%;border:3px solid var(--bs-card-bg,#1e2028);background:linear-gradient(135deg,#2c3450,#22283a);display:flex;align-items:center;justify-content:center;font-size:1.7rem;font-weight:800;color:#60a5fa;overflow:hidden;box-shadow:0 8px 24px rgba(0,0,0,.30);transition:transform .12s ease; }
.spage-avatar-wrap:hover .spage-avatar { transform:translateY(-2px); }
.spage-avatar img { width:100%;height:100%;object-fit:cover;border-radius:50%; }
.spage-avatar-edit { position:absolute;bottom:0;right:0;width:22px;height:22px;border-radius:50%;background:#35383a;border:2px solid var(--bs-card-bg,#1e2028);display:flex;align-items:center;justify-content:center;color:#fff;font-size:.6rem;pointer-events:none; }
.spage-hero-name { font-size:1.2rem;font-weight:950;color:rgba(255,255,255,.92); }
.spage-hero-sub  { font-size:.82rem;color:rgba(255,255,255,.55); }
/* Stat badges — exact copy from dashboard */
.spage-stat-badge { display:inline-flex;flex-direction:column;align-items:center;background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.09);border-radius:12px;padding:.55rem .9rem;min-width:78px; }
.spage-stat-badge--lg { padding:.75rem 1.2rem;min-width:96px;border-radius:14px; }
.spage-stat-badge--lg .spage-stat-val { font-size:1.15rem; }
.spage-stat-badge--lg .spage-stat-lbl { font-size:.7rem; }
.spage-stat-val { font-size:1rem;font-weight:900;line-height:1.2;color:rgba(255,255,255,.92); }
.spage-stat-lbl { font-size:.67rem;font-weight:700;color:rgba(255,255,255,.50);text-transform:uppercase;letter-spacing:.06em;margin-top:.2rem; }
.spage-chip { display:inline-flex;align-items:center;gap:6px;padding:6px 10px;border-radius:999px;background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.10);color:rgba(255,255,255,.92);font-weight:900;font-size:.82rem; }
.spage-chip--accent  { background:linear-gradient(135deg,rgba(109,92,255,.22),rgba(176,92,255,.14));border-color:rgba(109,92,255,.35); }
.spage-chip--danger  { background:rgba(255,82,163,.10);border-color:rgba(255,82,163,.28); }
.spage-chip--warning { background:rgba(251,191,36,.10);border-color:rgba(251,191,36,.28); }
/* Change image button inside reposition modal */
#spBannerBack, #sdashBannerBack { color: rgba(255,255,255,.85) !important; border-color: rgba(255,255,255,.25) !important; background: rgba(255,255,255,.06) !important; }
#spBannerBack:hover, #sdashBannerBack:hover { background: rgba(255,255,255,.12) !important; color: #fff !important; }
/* Tabs */
.spage-tabs.nav-tabs { border-bottom:1px solid var(--s-border);margin-top:18px; }
.spage-tabs .nav-link { color:var(--s-muted);font-weight:700;border:0;border-bottom:2px solid transparent;background:transparent;padding-left:0;padding-right:0;margin-right:2rem; }
.spage-tabs .nav-link:hover { color:var(--s-sub); }
.spage-tabs .nav-link.active { color:#fff;background:transparent;border-color:var(--s-purple); }

/* ── Upload modal dropzone ── */
.sp-modal-dropzone { display: block; border: 1px dashed rgba(255,255,255,.20); border-radius: 14px; padding: 22px 18px;
  cursor: pointer; background: rgba(255,255,255,.025); text-align: center; transition: border-color .12s, background .12s, transform .08s; }
.sp-modal-dropzone:hover { border-color: rgba(109,92,255,.65); background: rgba(109,92,255,.08); transform: translateY(-1px); }
.sp-modal-preview-circle { width: 60px; height: 60px; border-radius: 50%; object-fit: cover; border: 2px solid rgba(255,255,255,.15); }
.sp-modal-preview-banner { width: 100%; height: 140px; border-radius: 14px; object-fit: cover; border: 1px solid rgba(255,255,255,.10); background: rgba(255,255,255,.03); }

@media (max-width: 767.98px) { .spage-tabs .nav-link { margin-right: 1.25rem; } }
</style>
<?= $this->end() ?>

<?php
if (!function_exists('seller_page_banner_url')) {
  function seller_page_banner_url($raw) {
    $raw = trim((string)$raw);
    if ($raw === '') return '';
    if (preg_match('~^https?://~i', $raw)) return $raw;
    return rtrim(defined('SITE_URL') ? SITE_URL : BASE_URL, '/') . '/' . ltrim($raw, '/');
  }
}

if (!function_exists('spage_normalize_rank_slug')) {
  function spage_normalize_rank_slug($value) {
    $value = strtolower(trim((string)$value));
    if ($value === '') return '';
    $value = preg_replace('/[^a-z0-9]+/', '-', $value);
    $value = trim((string)$value, '-');

    $aliases = [
      'newbie' => 'beginner',
      'rookie' => 'beginner',
      'starter' => 'beginner',
      'novice' => 'beginner',
      'bronze' => 'beginner',
      'regular' => 'intermediate',
      'skilled' => 'advanced',
      'pro' => 'expert',
      'professional' => 'expert',
      'top-rated' => 'elite',
      'legend' => 'elite',
    ];

    return $aliases[$value] ?? $value;
  }
}

if (!function_exists('spage_rank_meta_from_value')) {
  function spage_rank_meta_from_value($value, array $fallback = []) {
    $slug = spage_normalize_rank_slug($value);
    if ($slug === '') return $fallback;

    $map = [
      'beginner' => ['label' => 'Beginner', 'icon_class' => 'fa-solid fa-badge-check text-slate-400', 'color' => '#94a3b8'],
      'intermediate' => ['label' => 'Expert Seller', 'icon_class' => 'fa-solid fa-badge-check text-emerald-500', 'color' => '#22c55e'],
      'advanced' => ['label' => 'Pro Seller', 'icon_class' => 'fa-solid fa-badge-check text-violet-500', 'color' => '#8b5cf6'],
      'expert' => ['label' => 'Expert Seller', 'icon_class' => 'fa-solid fa-badge-check text-emerald-500', 'color' => '#22c55e'],
      'elite' => ['label' => 'Mythic Seller', 'icon_class' => 'fa-solid fa-badge-check text-amber-400', 'color' => '#fbbf24'],
    ];

    if (isset($map[$slug])) return $map[$slug];

    $label = ucwords(str_replace('-', ' ', $slug));
    return [
      'label' => $label,
      'icon_class' => $fallback['icon_class'] ?? 'fa-solid fa-badge-check text-slate-400',
      'color' => $fallback['color'] ?? '#a78bfa',
    ];
  }
}

if (!function_exists('spage_resolve_rank')) {
  function spage_resolve_rank(array $sellerData, $salesBasedRank) {
    $candidates = [
      $sellerData['seller_rank'] ?? null,
      $sellerData['rank'] ?? null,
      $sellerData['rank_name'] ?? null,
      $sellerData['seller_rank_name'] ?? null,
      $sellerData['tier'] ?? null,
      $sellerData['seller_tier'] ?? null,
      $sellerData['level'] ?? null,
      $sellerData['seller_level'] ?? null,
    ];

    foreach ($candidates as $candidate) {
      if (trim((string)$candidate) !== '') {
        return spage_rank_meta_from_value($candidate, is_array($salesBasedRank) ? $salesBasedRank : []);
      }
    }

    return $salesBasedRank;
  }
}
$bannerUrl      = seller_page_banner_url($seller_data['banner'] ?? '');
$bannerPosition = trim((string)($seller_data['banner_position'] ?? '50% 50%'));
if (!preg_match('/^([\d.]+)%\s+([\d.]+)%$/', $bannerPosition)) $bannerPosition = '50% 50%';
$heroAvatarRaw    = trim((string)($seller_data['icon'] ?? ''));
$heroAvatarSrc    = ($heroAvatarRaw !== '') ? (preg_match('~^https?://~i', $heroAvatarRaw) ? $heroAvatarRaw : rtrim(defined('SITE_URL') ? SITE_URL : BASE_URL, '/') . '/' . ltrim($heroAvatarRaw, '/')) : '';
$heroAvatarLetter = strtoupper(substr(preg_replace('/[^A-Za-z0-9]/', '', (string)($seller_data['username'] ?? 'S')) ?: 'S', 0, 1));
$heroBalance      = number_format((int)($seller_data['balance'] ?? 0) / 100, 2);
$heroOnboarding   = strtolower(trim((string)($seller_data['onboarding_status'] ?? 'pending')));
$heroApproved     = ($heroOnboarding === 'approved');
$heroActive       = (int)($seller_data['is_active'] ?? 0) === 1;
$heroBanned       = (int)($seller_data['is_banned'] ?? 0) === 1;
$heroSalesBase = seller_total_sales(is_array($seller_data ?? null) ? $seller_data : []);
// Override with get_seller_total_sales() if available — this is the unified function
// that correctly includes selling_items + admin_id 51 bonus for seller #28
if (function_exists('get_seller_total_sales') && !empty($seller_data['id'])) {
    $heroSalesBase = get_seller_total_sales((int)$seller_data['id']);
}
$heroRank      = function_exists('seller_resolved_rank')
    ? seller_resolved_rank(is_array($seller_data ?? null) ? $seller_data : [], $heroSalesBase)
    : spage_resolve_rank(is_array($seller_data ?? null) ? $seller_data : [], seller_rank_from_sales($heroSalesBase));
$heroSales     = (int)($heroRank['sales'] ?? $heroSalesBase);
$heroFeePct    = isset($heroRank['fee_percent']) ? (float)$heroRank['fee_percent'] : seller_effective_fee_from_rank(is_array($seller_data ?? null) ? $seller_data : [], $heroSales);
$heroEarnRate  = round(100 - $heroFeePct, 2);
$heroJoinedAt = !empty($seller_data['created_at']) ? date('M Y', strtotime((string)$seller_data['created_at'])) : '—';
$heroVerified = $heroApproved;
?>

<div class="spage-hero-card card border-0 overflow-hidden p-0 mb-4">
  <div class="spage-hero-banner" id="spHeroBanner" <?php if ($bannerUrl): ?>style="background-image:url('<?= htmlspecialchars($bannerUrl, ENT_QUOTES) ?>');background-position:<?= htmlspecialchars($bannerPosition, ENT_QUOTES) ?>"<?php endif; ?>>
    <?php if (!$bannerUrl): ?><div class="spage-hero-banner-glow"></div><div class="spage-hero-banner-noise"></div><?php endif; ?>
    <button type="button" class="spage-banner-edit-btn" data-bs-toggle="modal" data-bs-target="#spUploadBannerModal">
      <i class="fa-duotone fa-image me-2"></i>Change Banner
    </button>
  </div>
  <div class="card-body spage-hero-body px-4 pb-3 pt-3">
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">

      <!-- Left: Avatar + Name + Verified -->
      <div class="d-flex align-items-center gap-3">
        <div class="spage-avatar-wrap" data-bs-toggle="modal" data-bs-target="#spUploadAvatarModal" title="Change profile picture">
          <div class="spage-avatar">
            <?php if ($heroAvatarSrc !== ''): ?><img src="<?= htmlspecialchars($heroAvatarSrc, ENT_QUOTES) ?>" alt=""><?php else: ?><span><?= $heroAvatarLetter ?></span><?php endif; ?>
          </div>
          <span class="spage-avatar-edit"><i class="fa-solid fa-pen"></i></span>
        </div>
        <div>
          <div class="d-flex align-items-center gap-2 flex-wrap">
            <h2 class="spage-hero-name mb-0"><?= htmlspecialchars($seller_data['username'] ?? 'Seller', ENT_QUOTES) ?> <span style="font-size:.72rem;font-weight:800;color:rgba(255,255,255,.35);background:rgba(255,255,255,.07);border:1px solid rgba(255,255,255,.11);border-radius:99px;padding:2px 8px;vertical-align:middle;">#<?= (int)($seller_data['id'] ?? 0) ?></span></h2>
            <?php if ($heroBanned): ?>
              <span class="spage-chip spage-chip--danger" style="font-size:.7rem;padding:3px 9px;"><i class="fa-duotone fa-ban me-1"></i>Banned</span>
            <?php elseif ($heroVerified): ?>
              <span class="spage-chip spage-chip--accent" style="font-size:.7rem;padding:3px 9px;"><i class="fa-duotone fa-badge-check me-1"></i>Verified</span>
            <?php else: ?>
              <span class="spage-chip spage-chip--warning" style="font-size:.7rem;padding:3px 9px;"><i class="fa-duotone fa-clock me-1"></i>Pending</span>
            <?php endif; ?>
          </div>
          <div class="spage-hero-sub mt-1"><i class="<?= htmlspecialchars($heroRank['icon_class'] ?? 'fa-solid fa-badge-check text-slate-400', ENT_QUOTES) ?> fa-fw me-1" style="color:<?= htmlspecialchars($heroRank['color'] ?? '#94a3b8', ENT_QUOTES) ?>;"></i><span style="color:<?= htmlspecialchars($heroRank['color'] ?? '#94a3b8', ENT_QUOTES) ?>;font-weight:800;"><?= htmlspecialchars($heroRank['label'] ?? 'Beginner', ENT_QUOTES) ?></span><span style="color:rgba(255,255,255,.45);margin-left:8px;">· <?= (int)$heroSales ?> Sales · <?= number_format($heroFeePct, 0) ?>% Fee</span></div>
        </div>
      </div>

      <!-- Right: Stats -->
      <div class="d-flex flex-wrap gap-2 align-items-center">
        <div class="spage-stat-badge spage-stat-badge--lg">
          <span class="spage-stat-val"><?= $heroBalance ?> €</span>
          <span class="spage-stat-lbl">Balance</span>
        </div>
        <div class="spage-stat-badge spage-stat-badge--lg">
          <span class="spage-stat-val"><?= $heroJoinedAt ?></span>
          <span class="spage-stat-lbl">Joined</span>
        </div>
        <div class="spage-stat-badge spage-stat-badge--lg">
          <span class="spage-stat-val" style="color:<?= $heroRank['color'] ?>">
            <i class="<?= htmlspecialchars($heroRank['icon_class'] ?? 'fa-solid fa-badge-check text-slate-400', ENT_QUOTES) ?> me-1"></i><?= htmlspecialchars($heroRank['label'] ?? 'Beginner', ENT_QUOTES) ?>
          </span>
          <span class="spage-stat-lbl">Rank · Fee <?= number_format($heroFeePct, 0) ?>%</span>
        </div>
      </div>
    </div>

    <?php if (!isset($spageHideNav) || !$spageHideNav): ?>
    <ul class="nav nav-tabs align-items-center spage-tabs mb-0 mt-3">
      <li class="nav-item"><a class="nav-link <?= ($spageActiveTab ?? '') === 'profile'          ? 'active' : '' ?>" href="<?= BASE_URL ?>/seller-area/profile">Profile</a></li>
      <li class="nav-item"><a class="nav-link <?= ($spageActiveTab ?? '') === 'personal-details' ? 'active' : '' ?>" href="<?= BASE_URL ?>/seller-area/personal-details">Personal Details</a></li>
      <li class="nav-item"><a class="nav-link <?= ($spageActiveTab ?? '') === 'payout'           ? 'active' : '' ?>" href="<?= BASE_URL ?>/seller-area/payout">Payout</a></li>
      <li class="nav-item"><a class="nav-link <?= ($spageActiveTab ?? '') === 'referrals'        ? 'active' : '' ?>" href="<?= BASE_URL ?>/seller-area/referrals">Referral</a></li>
    </ul>
    <?php endif; ?>
  </div>
</div>

<!-- Avatar Modal -->
<form class="ajax-form" action="<?= AJAX_URL ?>" method="POST" enctype="multipart/form-data">
  <input type="hidden" name="action" value="seller_upload_profile_picture">
  <div class="modal fade" id="spUploadAvatarModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width:440px;width:calc(100% - 2rem);">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title"><i class="fa-duotone fa-image me-2"></i>Change Profile Picture</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="d-flex align-items-center gap-3 mb-4">
            <div style="flex-shrink:0;">
              <?php if (!empty($heroAvatarSrc)): ?>
                <img id="spModalPreview" src="<?= htmlspecialchars($heroAvatarSrc, ENT_QUOTES) ?>" class="sp-modal-preview-circle">
              <?php else: ?>
                <div id="spModalPreviewFallback" style="width:60px;height:60px;border-radius:50%;background:linear-gradient(135deg,#2c3450,#22283a);border:2px solid rgba(255,255,255,.12);display:flex;align-items:center;justify-content:center;font-size:1.5rem;font-weight:800;color:var(--s-blue);"><?= $heroAvatarLetter ?></div>
              <?php endif; ?>
            </div>
            <div>
              <div style="font-weight:900;color:var(--s-text);">Upload your file</div>
              <div style="font-size:.88rem;color:var(--s-muted);">PNG / JPG / WEBP — max 5 MB</div>
            </div>
          </div>
          <label class="sp-modal-dropzone" for="spAvatarFile">
            <i class="fa-duotone fa-cloud-arrow-up" style="font-size:1.6rem;color:rgba(109,92,255,.8);display:block;margin-bottom:7px;"></i>
            <div style="font-weight:900;color:var(--s-text);">Drag &amp; drop or click to choose</div>
            <div style="font-size:.83rem;color:var(--s-muted);margin-top:4px;">Recommended: square image</div>
          </label>
          <input class="form-control d-none" accept="image/*" type="file" name="image_url" id="spAvatarFile">
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button id="spAvatarSubmit" type="submit" class="btn btn-primary" disabled><i class="fa-duotone fa-cloud-arrow-up me-1"></i>Upload</button>
        </div>
      </div>
    </div>
  </div>
</form>

<!-- Banner Modal with Reposition -->
<div class="modal fade" id="spUploadBannerModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" style="max-width:560px;width:calc(100% - 2rem);">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title"><i class="fa-duotone fa-panorama me-2"></i>Change Banner</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <!-- Step 1: Upload -->
        <div id="spBannerStep1">
          <div class="sp-banner-preview-wrap mb-4" id="spBannerPreviewWrap">
            <?php if (!empty($bannerUrl)): ?>
              <img src="<?= htmlspecialchars($bannerUrl, ENT_QUOTES) ?>" class="sp-banner-preview-img" style="object-position:<?= htmlspecialchars($bannerPosition, ENT_QUOTES) ?>">
            <?php else: ?>
              <div class="sp-banner-preview-placeholder"><i class="fa-duotone fa-panorama"></i><span>No banner set</span></div>
            <?php endif; ?>
          </div>
          <label class="sp-modal-dropzone" for="spBannerFile">
            <i class="fa-duotone fa-cloud-arrow-up" style="font-size:1.6rem;color:rgba(109,92,255,.8);display:block;margin-bottom:7px;"></i>
            <div style="font-weight:900;color:var(--s-text);">Drag &amp; drop or click to choose</div>
            <div style="font-size:.83rem;color:var(--s-muted);margin-top:4px;">PNG / JPG / WEBP — Recommended: 1400×350px</div>
          </label>
          <input class="form-control d-none" accept="image/*" type="file" id="spBannerFile">
        </div>
        <!-- Step 2: Reposition -->
        <div id="spBannerStep2" style="display:none;">
          <div style="font-size:.82rem;color:rgba(255,255,255,.55);font-weight:600;display:flex;align-items:center;margin-bottom:.75rem;">
            <i class="fa-duotone fa-arrows-up-down-left-right me-2"></i>Drag the image to adjust position, then save.
          </div>
          <div class="sp-reposition-stage" id="spRepositionStage">
            <img id="spRepositionImg" src="" alt="banner" class="sp-reposition-img" draggable="false">
            <div style="position:absolute;inset:0;pointer-events:none;"><div class="sp-reposition-crosshair"></div></div>
          </div>
          <div class="d-flex align-items-center gap-2 mt-2">
            <button type="button" class="btn btn-outline-light btn-sm" id="spBannerBack"><i class="fa-solid fa-arrow-left me-1"></i>Change image</button>
            <span class="text-muted small ms-auto" id="spRepositionCoords">50% 50%</span>
          </div>
        </div>
      </div>
      <div class="modal-footer" style="gap:8px;">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button id="spBannerNextBtn"     type="button" class="btn btn-primary"  disabled style="display:none;"><i class="fa-solid fa-arrow-right me-1"></i>Adjust Position</button>
        <button id="spBannerSavePosOnly" type="button" class="btn btn-primary"  style="display:none;background:linear-gradient(135deg,#6d5cff,#b05cff);border:none;"><i class="fa-duotone fa-crosshairs me-1"></i>Save Position</button>
        <button id="spBannerSaveAll"     type="button" class="btn btn-success"  style="display:none;"><i class="fa-duotone fa-cloud-arrow-up me-1"></i>Upload &amp; Save</button>
      </div>
    </div>
  </div>
</div>
<!-- Hidden forms for banner upload & position-only save -->
<form class="ajax-form" id="spBannerUploadForm" action="<?= AJAX_URL ?>" method="POST" enctype="multipart/form-data" style="display:none;">
  <input type="hidden" name="action" value="seller_upload_banner">
  <input type="hidden" name="banner_position" id="spBannerPositionInput" value="50% 50%">
  <input type="file" name="banner_image" id="spBannerFileTransfer" accept="image/*">
</form>
<form class="ajax-form" id="spBannerPosOnlyForm" action="<?= AJAX_URL ?>" method="POST" style="display:none;">
  <input type="hidden" name="action" value="seller_save_banner_position">
  <input type="hidden" name="banner_position" id="spBannerPosOnlyInput" value="50% 50%">
</form>

<style>
.sp-banner-preview-wrap { border-radius:12px;overflow:hidden;border:1px solid rgba(255,255,255,.08); }
.sp-banner-preview-img  { width:100%;aspect-ratio:4/1;object-fit:cover;display:block;min-height:80px; }
.sp-banner-preview-placeholder { aspect-ratio:4/1;min-height:80px;display:flex;flex-direction:column;align-items:center;justify-content:center;gap:.5rem;color:rgba(255,255,255,.25);font-size:.85rem;background:rgba(255,255,255,.02); }
.sp-banner-preview-placeholder i { font-size:2rem; }
.sp-reposition-stage { position:relative;width:100%;aspect-ratio:4/1;min-height:80px;border-radius:12px;overflow:hidden;border:1px solid rgba(255,255,255,.12);cursor:grab;user-select:none;background:#0d0f1a; }
.sp-reposition-stage:active { cursor:grabbing; }
.sp-reposition-img { position:absolute;width:100%;height:100%;object-fit:cover;pointer-events:none; }
.sp-reposition-crosshair { position:absolute;top:50%;left:50%;transform:translate(-50%,-50%);width:32px;height:32px; }
.sp-reposition-crosshair::before,.sp-reposition-crosshair::after { content:'';position:absolute;background:rgba(255,255,255,.55);border-radius:1px; }
.sp-reposition-crosshair::before { width:1px;height:100%;left:50%;top:0; }
.sp-reposition-crosshair::after  { height:1px;width:100%;top:50%;left:0; }
</style>

<!-- Upload JS: inline so page-level scripts blocks never override it -->
<script>
document.addEventListener('DOMContentLoaded', function(){

  // ── Avatar upload ──
  (function(){
    var input  = document.getElementById('spAvatarFile');
    var submit = document.getElementById('spAvatarSubmit');
    if(!input || !submit) return;
    function applyFile(file){
      if(!file || !file.type.startsWith('image/')) return;
      try{ var dt=new DataTransfer(); dt.items.add(file); input.files=dt.files; }catch(e){}
      var img = document.getElementById('spModalPreview');
      var fb  = document.getElementById('spModalPreviewFallback');
      if(img){ img.src=URL.createObjectURL(file); img.classList.remove('d-none'); }
      if(fb)  fb.classList.add('d-none');
      submit.disabled = false;
    }
    input.addEventListener('change', function(){ if(input.files&&input.files[0]) applyFile(input.files[0]); });
    var dz = document.querySelector('label[for="spAvatarFile"]');
    if(dz){
      dz.addEventListener('click',    function(e){ e.preventDefault(); input.click(); });
      dz.addEventListener('dragover', function(e){ e.preventDefault(); dz.style.borderColor='rgba(109,92,255,.9)'; });
      dz.addEventListener('dragleave',function(){  dz.style.borderColor=''; });
      dz.addEventListener('drop',     function(e){ e.preventDefault(); dz.style.borderColor=''; var f=e.dataTransfer.files&&e.dataTransfer.files[0]; if(f) applyFile(f); });
    }
    var modalEl = document.getElementById('spUploadAvatarModal');
    if(modalEl) modalEl.addEventListener('shown.bs.modal', function(){ if(input.files&&input.files[0]) submit.disabled=false; });
  })();

  // ── Banner upload + reposition ──
  (function(){
    var modalEl      = document.getElementById('spUploadBannerModal');
    var fileInput    = document.getElementById('spBannerFile');
    var step1        = document.getElementById('spBannerStep1');
    var step2        = document.getElementById('spBannerStep2');
    var nextBtn      = document.getElementById('spBannerNextBtn');
    var backBtn      = document.getElementById('spBannerBack');
    var saveAllBtn   = document.getElementById('spBannerSaveAll');
    var savePosBtn   = document.getElementById('spBannerSavePosOnly');
    var reposStage   = document.getElementById('spRepositionStage');
    var reposImg     = document.getElementById('spRepositionImg');
    var coordsLabel  = document.getElementById('spRepositionCoords');
    var uploadForm   = document.getElementById('spBannerUploadForm');
    var posOnlyForm  = document.getElementById('spBannerPosOnlyForm');
    var posInput     = document.getElementById('spBannerPositionInput');
    var posOnlyInput = document.getElementById('spBannerPosOnlyInput');
    var fileTransfer = document.getElementById('spBannerFileTransfer');
    var heroBanner   = document.querySelector('.spage-hero-banner');
    if(!modalEl) return;

    var currentPos  = {x:50,y:50}, selectedFile=null, isDragging=false;
    var dragStart   = {x:0,y:0},   posAtStart={x:50,y:50};
    var initRaw = '<?= addslashes($bannerPosition) ?>';
    var im = initRaw.match(/^([\d.]+)%\s+([\d.]+)%$/);
    if(im){ currentPos.x=parseFloat(im[1]); currentPos.y=parseFloat(im[2]); }

    function posStr(){ return Math.round(currentPos.x)+'% '+Math.round(currentPos.y)+'%'; }
    function applyPosToImg(){ if(reposImg) reposImg.style.objectPosition=posStr(); if(coordsLabel) coordsLabel.textContent=posStr(); }
    // hasBanner: true if a banner is set (either from PHP or newly uploaded)
    var phpBannerUrl = '<?= addslashes($bannerUrl) ?>';
    function hasBanner(){ 
      if(phpBannerUrl) return true;
      var bi=heroBanner&&heroBanner.style.backgroundImage; 
      return bi&&bi!=='none'&&bi!==''; 
    }
    function getBannerSrc(){
      if(heroBanner&&heroBanner.style.backgroundImage&&heroBanner.style.backgroundImage!=='none'&&heroBanner.style.backgroundImage!==''){
        return heroBanner.style.backgroundImage.replace(/^url\(["']?/,'').replace(/["']?\)$/,'');
      }
      return phpBannerUrl;
    }

    function goToStep1(){
      if(step1) step1.style.display='';
      if(step2) step2.style.display='none';
      if(nextBtn){ nextBtn.style.display=selectedFile?'':'none'; nextBtn.disabled=false; }
      if(saveAllBtn) saveAllBtn.style.display='none';
      // Always show "Save Position" + auto-go to step2 if banner already exists
      if(hasBanner()){
        if(savePosBtn) savePosBtn.style.display='';
        // Also pre-load reposition stage with current banner
        var src=getBannerSrc();
        if(reposImg&&src){ reposImg.src=src; applyPosToImg(); }
      } else {
        if(savePosBtn) savePosBtn.style.display='none';
      }
    }
    function goToStep2(src){
      if(reposImg){ reposImg.src=src; reposImg.style.objectPosition=posStr(); }
      if(coordsLabel) coordsLabel.textContent=posStr();
      if(step1) step1.style.display='none';
      if(step2) step2.style.display='';
      if(nextBtn) nextBtn.style.display='none';
      if(saveAllBtn) saveAllBtn.style.display=selectedFile?'':'none';
      if(savePosBtn) savePosBtn.style.display=hasBanner()?'':'none';
    }
    function applyFile(file){
      if(!file||!file.type.startsWith('image/')) return;
      selectedFile=file;
      var url=URL.createObjectURL(file);
      var pw=document.getElementById('spBannerPreviewWrap');
      if(pw) pw.innerHTML='<img src="'+url+'" class="sp-banner-preview-img" style="object-fit:cover;">';
      if(nextBtn){ nextBtn.style.display=''; nextBtn.disabled=false; }
    }
    if(fileInput){
      fileInput.addEventListener('change',function(){ if(fileInput.files[0]) applyFile(fileInput.files[0]); });
    }
    var dz = document.querySelector('label[for="spBannerFile"]');
    if(dz){
      dz.addEventListener('click',    function(e){ e.preventDefault(); fileInput.click(); });
      dz.addEventListener('dragover', function(e){ e.preventDefault(); dz.style.borderColor='rgba(109,92,255,.9)'; });
      dz.addEventListener('dragleave',function(){  dz.style.borderColor=''; });
      dz.addEventListener('drop',     function(e){ e.preventDefault(); dz.style.borderColor=''; var f=e.dataTransfer.files&&e.dataTransfer.files[0]; if(f) applyFile(f); });
    }
    if(nextBtn) nextBtn.addEventListener('click',function(){ if(selectedFile) goToStep2(URL.createObjectURL(selectedFile)); });
    if(backBtn) backBtn.addEventListener('click', goToStep1);

    // Drag to reposition
    function startDrag(cx,cy){ isDragging=true; dragStart={x:cx,y:cy}; posAtStart={x:currentPos.x,y:currentPos.y}; }
    function moveDrag(cx,cy){
      if(!isDragging||!reposStage||!reposImg) return;
      var sw=reposStage.offsetWidth, sh=reposStage.offsetHeight;
      var iw=reposImg.naturalWidth||sw*1.5, ih=reposImg.naturalHeight||sh*1.5;
      var sc=Math.max(sw/iw,sh/ih), rw=iw*sc, rh=ih*sc;
      currentPos.x=Math.max(0,Math.min(100,posAtStart.x-(cx-dragStart.x)/Math.max(rw-sw,1)*100));
      currentPos.y=Math.max(0,Math.min(100,posAtStart.y-(cy-dragStart.y)/Math.max(rh-sh,1)*100));
      applyPosToImg();
    }
    function endDrag(){ isDragging=false; }
    if(reposStage){
      reposStage.addEventListener('mousedown',function(e){ e.preventDefault(); startDrag(e.clientX,e.clientY); });
      window.addEventListener('mousemove',function(e){ if(isDragging) moveDrag(e.clientX,e.clientY); });
      window.addEventListener('mouseup', endDrag);
      reposStage.addEventListener('touchstart',function(e){ var t=e.touches[0]; startDrag(t.clientX,t.clientY); },{passive:true});
      reposStage.addEventListener('touchmove', function(e){ var t=e.touches[0]; moveDrag(t.clientX,t.clientY); e.preventDefault(); },{passive:false});
      reposStage.addEventListener('touchend',  endDrag);
    }
    if(saveAllBtn){
      saveAllBtn.addEventListener('click',function(){
        if(!selectedFile||!uploadForm||!posInput||!fileTransfer) return;
        posInput.value=posStr();
        try{ var dt=new DataTransfer(); dt.items.add(selectedFile); fileTransfer.files=dt.files; }catch(e){}
        if(heroBanner){ heroBanner.style.backgroundImage='url('+URL.createObjectURL(selectedFile)+')'; heroBanner.style.backgroundPosition=posStr(); }
        if(typeof uploadForm.requestSubmit==='function') uploadForm.requestSubmit(); else uploadForm.submit();
      });
    }
    if(savePosBtn){
      savePosBtn.addEventListener('click',function(){
        if(!posOnlyForm||!posOnlyInput) return;
        posOnlyInput.value=posStr();
        if(heroBanner) heroBanner.style.backgroundPosition=posStr();
        if(typeof posOnlyForm.requestSubmit==='function') posOnlyForm.requestSubmit(); else posOnlyForm.submit();
        var bm=typeof bootstrap!=='undefined'&&bootstrap.Modal?bootstrap.Modal.getInstance(modalEl):null;
        if(bm) bm.hide();
      });
    }
    if(modalEl){
      modalEl.addEventListener('show.bs.modal',function(){
        goToStep1();
        // If banner exists, immediately jump to reposition step
        if(hasBanner() && !selectedFile){
          goToStep2(getBannerSrc());
        }
      });
      modalEl.addEventListener('hidden.bs.modal',function(){
        selectedFile=null; isDragging=false;
        if(fileInput) fileInput.value='';
        var dz2=document.querySelector('label[for="spBannerFile"]');
        if(dz2) dz2.style.borderColor='';
        goToStep1();
      });
    }
  })();

});
</script>
