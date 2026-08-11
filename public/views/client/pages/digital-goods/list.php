<?php
/* Client: Digital Goods Orders — /profile/digital-goods */
?>
<?= $this->layout('client/layouts/main', ['meta' => $meta ?? [
    'title'       => 'My Digital Goods | LoLBoost.gg',
    'h1'          => 'My Digital Goods',
    'description' => 'View your purchased digital goods.',
]]) ?>

<?= $this->start('styles') ?>
<style>
/* ── Section header ── */
.cl-dg-page .av-section-head {
  display: flex; align-items: center; justify-content: space-between; gap: 14px; margin-bottom: 20px;
}
.cl-dg-page .av-section-title {
  font-size: .72rem; font-weight: 900; text-transform: uppercase;
  letter-spacing: .09em; color: rgba(255,255,255,.38);
  display: flex; align-items: center; gap: 8px;
}
.cl-dg-page .av-section-title i { color: #8b5cf6; font-size: .85rem; }
.cl-dg-page .av-section-title span {
  display: inline-flex; align-items: center; justify-content: center;
  min-width: 22px; height: 22px; padding: 0 7px; border-radius: 99px;
  font-size: .68rem; font-weight: 800;
  background: rgba(139,92,246,.15); border: 1px solid rgba(139,92,246,.25); color: #ddd6fe;
}

/* ── Card shell ── */
.av-acc-card {
  background: #25282a;
  border: 1px solid rgba(255,255,255,.07);
  border-radius: 20px; overflow: hidden;
  box-shadow: 0 4px 24px rgba(0,0,0,.22);
  transition: border-color .15s, transform .15s, box-shadow .2s;
  display: flex; flex-direction: column; height: 100%;
}
.av-acc-card:hover {
  border-color: rgba(255,255,255,.13);
  transform: translateY(-3px);
  box-shadow: 0 12px 36px rgba(0,0,0,.35);
}
.av-acc-card--dg-unpaid {
  border-color: rgba(251,191,36,.15);
  background: linear-gradient(160deg, rgba(251,191,36,.05) 0%, #25282a 60%);
}
.av-acc-card--dg-unpaid:hover { border-color: rgba(251,191,36,.32); }
.av-acc-card--dg-active {
  border-color: rgba(139,92,246,.15);
  background: linear-gradient(160deg, rgba(139,92,246,.055) 0%, #25282a 60%);
}
.av-acc-card--dg-active:hover { border-color: rgba(139,92,246,.32); box-shadow: 0 12px 36px rgba(139,92,246,.1); }
.av-acc-card--dg-done {
  border-color: rgba(74,222,128,.14);
  background: linear-gradient(160deg, rgba(74,222,128,.045) 0%, #25282a 60%);
}
.av-acc-card--dg-done:hover { border-color: rgba(74,222,128,.32); }
.av-acc-card--dg-bad {
  border-color: rgba(251,113,133,.14);
  background: linear-gradient(160deg, rgba(251,113,133,.045) 0%, #25282a 60%);
}
.av-acc-card--dg-bad:hover { border-color: rgba(251,113,133,.32); }

/* ── Accent line at top ── */
.av-acc-card__top {
  display: flex; align-items: center; gap: 13px;
  padding: 16px 18px 14px;
  border-bottom: 1px solid rgba(255,255,255,.05);
  position: relative;
}
.av-acc-card--dg-unpaid .av-acc-card__top::before,
.av-acc-card--dg-active .av-acc-card__top::before,
.av-acc-card--dg-done   .av-acc-card__top::before,
.av-acc-card--dg-bad    .av-acc-card__top::before {
  content: ''; position: absolute; top: 0; left: 0; right: 0; height: 2px;
  border-radius: 20px 20px 0 0;
}
.av-acc-card--dg-unpaid .av-acc-card__top::before { background: linear-gradient(90deg, #fbbf24, #fde68a, transparent); }
.av-acc-card--dg-active .av-acc-card__top::before { background: linear-gradient(90deg, #8b5cf6, #c4b5fd, transparent); }
.av-acc-card--dg-done   .av-acc-card__top::before { background: linear-gradient(90deg, #4ade80, #bbf7d0, transparent); }
.av-acc-card--dg-bad    .av-acc-card__top::before { background: linear-gradient(90deg, #fb7185, #fecdd3, transparent); }

/* ── Rank icon (thumb) ── */
.av-acc-card__rank-ico {
  width: 44px; height: 44px; border-radius: 13px; flex-shrink: 0;
  background: rgba(139,92,246,.1); border: 1px solid rgba(139,92,246,.22);
  display: flex; align-items: center; justify-content: center; overflow: hidden;
  box-shadow: 0 2px 12px rgba(139,92,246,.1);
}
.av-acc-card--dg-unpaid .av-acc-card__rank-ico { background: rgba(251,191,36,.1); border-color: rgba(251,191,36,.22); }
.av-acc-card--dg-done   .av-acc-card__rank-ico { background: rgba(74,222,128,.1);  border-color: rgba(74,222,128,.22); }
.av-acc-card--dg-bad    .av-acc-card__rank-ico { background: rgba(251,113,133,.1); border-color: rgba(251,113,133,.22); }
.av-acc-card__rank-ico img { width: 100%; height: 100%; object-fit: cover; display: block; }
.av-acc-card__rank-ico--brand img { width: 70%; height: 70%; object-fit: contain; }
.av-acc-card__rank-ico--brand {
  background: rgba(255,255,255,.12);
  border-color: rgba(255,255,255,.16);
  box-shadow: 0 8px 24px rgba(0,0,0,.28);
}
.av-acc-card__rank-ico i  { font-size: 1.1rem; color: #c4b5fd; }
.av-acc-card--dg-unpaid .av-acc-card__rank-ico i { color: #fde68a; }
.av-acc-card--dg-done   .av-acc-card__rank-ico i { color: #4ade80; }
.av-acc-card--dg-bad    .av-acc-card__rank-ico i { color: #fb7185; }

/* ── Card info ── */
.av-acc-card__info { flex: 1; min-width: 0; }
.av-acc-card__title {
  font-size: .92rem; font-weight: 800; color: rgba(255,255,255,.92);
  white-space: nowrap; overflow: hidden; text-overflow: ellipsis; line-height: 1.3;
}
.av-acc-card__sub {
  font-size: .7rem; color: rgba(255,255,255,.3); margin-top: 3px;
  display: flex; align-items: center; gap: 5px; flex-wrap: wrap;
}

/* ── Status badge ── */
.av-acc-card__type-tag {
  display: inline-flex; align-items: center; gap: 4px;
  padding: 4px 10px; border-radius: 99px;
  font-size: .66rem; font-weight: 900; flex-shrink: 0;
  text-transform: uppercase; letter-spacing: .05em;
}
.av-acc-card__type-tag--delivered,
.av-acc-card__type-tag--completed { background: rgba(74,222,128,.1);   border: 1px solid rgba(74,222,128,.25);   color: #4ade80; }
.av-acc-card__type-tag--paid,
.av-acc-card__type-tag--processing { background: rgba(139,92,246,.13); border: 1px solid rgba(139,92,246,.28);   color: #c4b5fd; }
.av-acc-card__type-tag--unpaid     { background: rgba(251,191,36,.11);  border: 1px solid rgba(251,191,36,.26);  color: #fbbf24; }
.av-acc-card__type-tag--cancelled,
.av-acc-card__type-tag--refunded   { background: rgba(251,113,133,.11); border: 1px solid rgba(251,113,133,.25); color: #fb7185; }
.av-acc-card__type-tag--default    { background: rgba(255,255,255,.06); border: 1px solid rgba(255,255,255,.1);  color: rgba(255,255,255,.42); }

/* ── Stats grid ── */
.av-acc-card__stats {
  display: grid; grid-template-columns: repeat(3, 1fr);
  border-bottom: 1px solid rgba(255,255,255,.05);
  background: rgba(0,0,0,.1);
}
.av-acc-card__stat { padding: 10px 16px; border-right: 1px solid rgba(255,255,255,.05); }
.av-acc-card__stat:last-child { border-right: 0; }
.av-acc-card__stat-lbl {
  font-size: .58rem; font-weight: 800; text-transform: uppercase;
  letter-spacing: .07em; color: rgba(255,255,255,.25); margin-bottom: 4px;
}
.av-acc-card__stat-val {
  font-size: .82rem; font-weight: 800; color: rgba(255,255,255,.85);
  white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
}

/* ── Note row ── */
.av-acc-card__note {
  padding: 11px 16px; border-bottom: 1px solid rgba(255,255,255,.05);
  display: flex; align-items: center; gap: 8px; min-height: 44px;
  font-size: .72rem; color: rgba(255,255,255,.35);
}
.av-acc-card__note i { color: rgba(139,92,246,.6); }

/* ── Footer ── */
.av-acc-card__spacer { flex: 1; }
.av-acc-card__footer {
  padding: 11px 16px;
  display: flex; align-items: center; justify-content: space-between; gap: 8px;
  background: rgba(0,0,0,.08);
}
.av-acc-card__date {
  font-size: .7rem; color: rgba(255,255,255,.25);
  display: flex; align-items: center; gap: 5px;
}
.av-acc-card__date::before {
  content: ''; display: inline-block; width: 5px; height: 5px;
  border-radius: 50%; background: rgba(255,255,255,.15);
}

/* ── Buttons ── */
.av-acc-btn {
  display: inline-flex; align-items: center; gap: .35rem;
  padding: 7px 15px; border-radius: 11px; font-size: .78rem; font-weight: 800;
  cursor: pointer; text-decoration: none; transition: all .12s;
}
.av-acc-btn:hover { transform: translateY(-1px); text-decoration: none; }
.av-acc-btn--primary {
  background: rgba(109,92,255,.22); border: 1px solid rgba(109,92,255,.32); color: #c4b5fd;
}
.av-acc-btn--primary:hover {
  background: rgba(109,92,255,.35); border-color: rgba(109,92,255,.5);
  color: #e9d5ff; box-shadow: 0 4px 16px rgba(109,92,255,.2);
}

/* ── Filter dropdown ── */
.av-buy-switch { position: relative; }
.av-buy-switch__menu {
  position: absolute; right: 0; top: calc(100% + 8px); min-width: 210px; z-index: 30;
  padding: 8px; border-radius: 16px; background: #1e2022;
  border: 1px solid rgba(255,255,255,.09); box-shadow: 0 20px 48px rgba(0,0,0,.45); display: none;
}
.av-buy-switch.is-open .av-buy-switch__menu { display: block; }
.av-buy-switch__item {
  display: flex; align-items: center; justify-content: space-between; gap: 11px;
  width: 100%; padding: 10px 12px; border-radius: 11px; text-decoration: none;
  color: rgba(255,255,255,.82); border: 1px solid transparent;
  background: transparent; font-size: .78rem; font-weight: 800; transition: all .12s;
}
.av-buy-switch__item:hover,
.av-buy-switch__item.is-active {
  background: rgba(109,92,255,.1); border-color: rgba(109,92,255,.22); color: #fff;
}
.av-filter-dot { width: 8px; height: 8px; border-radius: 50%; background: rgba(255,255,255,.25); }
.av-filter-dot--paid      { background: #c084fc; }
.av-filter-dot--delivered { background: #4ade80; }
.av-filter-dot--unpaid    { background: #fbbf24; }
.av-filter-dot--bad       { background: #fb7185; }

/* ── Unread dot ── */
.av-unread-dot {
  width: 8px; height: 8px; border-radius: 50%;
  background: #8b5cf6; box-shadow: 0 0 8px rgba(139,92,246,.8);
  display: inline-flex; flex-shrink: 0;
}

/* ── Empty state ── */
.av-empty {
  display: flex; flex-direction: column; align-items: center; justify-content: center;
  padding: 56px 20px; text-align: center;
  border: 1px dashed rgba(255,255,255,.08); border-radius: 20px; background: rgba(255,255,255,.01);
}
.av-empty__ico   { font-size: 2.6rem; opacity: .18; margin-bottom: 12px; }
.av-empty__title { font-size: .95rem; font-weight: 800; color: rgba(255,255,255,.32); margin-bottom: 6px; }
.av-empty__sub   { font-size: .8rem; color: rgba(255,255,255,.18); }

@media (max-width: 576px) {
  .cl-dg-page .av-section-head { align-items: flex-start; flex-direction: column; }
  .av-acc-card__stats { grid-template-columns: repeat(2, 1fr); }
  .av-acc-card__stat:nth-child(2) { border-right: 0; }
  .av-acc-card__stat:nth-child(3) { border-top: 1px solid rgba(255,255,255,.05); border-right: 0; }
}
</style>
<?= $this->end() ?>

<?php
$purchases    = $purchases ?? [];
$statusFilter = strtoupper(trim((string)($status ?? '')));

$h = fn($v): string => htmlspecialchars((string)($v ?? ''), ENT_QUOTES, 'UTF-8');

$currencyCode   = strtoupper((string)($_SESSION['currency'] ?? 'EUR'));
$currencySymbol = function_exists('util_format_currency_display') ? util_format_currency_display($currencyCode) : ($currencyCode === 'USD' ? '$' : '€');
$rate = 1.0;
if ($currencyCode !== 'EUR' && function_exists('get_exchange_rate')) {
    $tmpRate = (float)get_exchange_rate(); if ($tmpRate > 0) $rate = $tmpRate;
}
$formatMoney = fn($cents): string => $currencySymbol . number_format(((int)$cents / 100) * $rate, 2);

$assetUrl = defined('ASSET_URL') ? rtrim(ASSET_URL, '/') : '';
$normalizeAssetPath = static function ($path) use ($assetUrl): string {
    $path = trim((string)($path ?? ''));
    if ($path === '') return '';
    $path = html_entity_decode($path, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    $path = str_replace('\\', '/', $path);
    if (preg_match('#^https?://#i', $path) || str_starts_with($path, '//')) return $path;

    $path = preg_replace('#^/public/assets#', '', $path);
    $path = preg_replace('#^public/assets#', '', $path);
    $path = preg_replace('#/public/assets/#', '/', $path);
    $path = '/' . ltrim((string)$path, '/');

    return $assetUrl !== '' ? $assetUrl . $path : $path;
};

$brandBannerGradient = static function (string $brand, string $category, string $title): string {
    $seed = strtolower($brand !== '' ? $brand : ($category !== '' ? $category : $title));
    $palettes = [
        'youtube'     => ['#1a0000','#7f0000','#b91c1c'],
        'netflix'     => ['#0a0000','#7f0000','#991b1b'],
        'spotify'     => ['#001a0d','#14532d','#15803d'],
        'discord'     => ['#0d0f1f','#3730a3','#5865f2'],
        'twitch'      => ['#1a0033','#6d28d9','#9146ff'],
        'steam'       => ['#0a0e14','#1e3a5f','#1b73b4'],
        'xbox'        => ['#001a00','#14532d','#16a34a'],
        'playstation' => ['#00001a','#1e3a8a','#2563eb'],
        'amazon'      => ['#1a0d00','#92400e','#d97706'],
        'apple'       => ['#0a0a0a','#1c1c1e','#3a3a3c'],
        'google'      => ['#0d1117','#1e3a5f','#4285f4'],
        'microsoft'   => ['#001228','#1e3a5f','#0078d4'],
        'default'     => ['#0f0e27','#1e1b4b','#312e81'],
    ];
    $picked = $palettes['default'];
    foreach ($palettes as $key => $palette) {
        if ($key !== 'default' && str_contains($seed, $key)) {
            $picked = $palette;
            break;
        }
    }
    return "linear-gradient(135deg,{$picked[0]} 0%,{$picked[1]} 48%,{$picked[2]} 100%)";
};

$cardVariant = fn(string $s): string => match(strtolower($s)) {
    'unpaid'                => 'av-acc-card--dg-unpaid',
    'delivered','completed' => 'av-acc-card--dg-done',
    'cancelled','refunded'  => 'av-acc-card--dg-bad',
    default                 => 'av-acc-card--dg-active',
};

$badgeVariant = fn(string $s): string => match(strtolower($s)) {
    'delivered','completed' => 'delivered',
    'paid','processing'     => 'paid',
    'unpaid'                => 'unpaid',
    'cancelled','refunded'  => 'cancelled',
    default                 => 'default',
};

$statusLabel = fn(string $s): string => match(strtoupper($s)) {
    'DELIVERED'  => 'Delivered',
    'COMPLETED'  => 'Completed',
    'PAID'       => 'Paid',
    'PROCESSING' => 'Processing',
    'UNPAID'     => 'Unpaid',
    'CANCELLED'  => 'Cancelled',
    'REFUNDED'   => 'Refunded',
    default      => strtoupper($s),
};

$filterLinks = [
    ''          => ['All',       ''],
    'UNPAID'    => ['Unpaid',    'av-filter-dot--unpaid'],
    'PAID'      => ['Paid',      'av-filter-dot--paid'],
    'DELIVERED' => ['Delivered', 'av-filter-dot--delivered'],
    'COMPLETED' => ['Completed', 'av-filter-dot--delivered'],
    'CANCELLED' => ['Cancelled', 'av-filter-dot--bad'],
];
?>

<div class="cl-dg-page">

  <div class="av-section-head">
    <div class="av-section-title">
      <i class="fa-solid fa-layer-group"></i>
      Digital Goods
      <span><?= count($purchases) ?></span>
    </div>

    <div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap;">
      <a href="<?= BASE_URL ?>/digital-goods" class="av-acc-btn av-acc-btn--primary" style="font-size:.72rem;padding:5px 11px;">
        <i class="fa-solid fa-store"></i> Buy Digital Goods
      </a>
      <div class="av-buy-switch" data-dg-filter>
        <button type="button" class="av-acc-btn av-acc-btn--primary" style="font-size:.72rem;padding:5px 11px;min-width:100px;justify-content:center;" aria-haspopup="true" aria-expanded="false">
          <i class="fa-solid fa-filter"></i>
          <?= $statusFilter !== '' ? $h($statusLabel($statusFilter)) : 'Filter' ?>
        </button>
        <div class="av-buy-switch__menu">
          <?php foreach ($filterLinks as $value => [$label, $dotClass]): ?>
          <a href="?status=<?= urlencode($value) ?>" class="av-buy-switch__item <?= $statusFilter === $value ? 'is-active' : '' ?>">
            <span><?= $h($label) ?></span>
            <span class="av-filter-dot <?= $h($dotClass) ?>"></span>
          </a>
          <?php endforeach; ?>
        </div>
      </div>
    </div>
  </div>

  <?php if (empty($purchases)): ?>
  <div class="av-empty">
    <div class="av-empty__ico"><i class="fa-solid fa-layer-group"></i></div>
    <div class="av-empty__title">No digital goods yet</div>
    <div class="av-empty__sub">Your purchased digital goods will appear here.</div>
    <a href="<?= BASE_URL ?>/digital-goods" class="av-acc-btn av-acc-btn--primary" style="margin-top:14px;">
      <i class="fa-solid fa-store"></i> Browse Digital Goods
    </a>
  </div>

  <?php else: ?>
  <div class="row g-3">
    <?php foreach ($purchases as $p):
      $id          = (int)($p['id'] ?? 0);
      $statusRaw   = strtoupper(trim((string)($p['status'] ?? 'UNPAID')));
      $title       = (string)($p['item_title'] ?? 'Digital Good');
      $brand       = trim((string)($p['brand'] ?? ''));
      $catName     = trim((string)($p['category_name'] ?? ''));
      $qty         = max(1, (int)($p['quantity'] ?? 1));
      $price       = $formatMoney((int)($p['price'] ?? 0));
      $seller      = (string)($p['seller_username'] ?? 'Seller');
      $date        = !empty($p['created_at']) ? date('d M Y', strtotime($p['created_at'])) : '—';
      $hasUnread   = !empty($p['has_unread']);
      $imgs        = json_decode((string)($p['item_images'] ?? $p['images'] ?? '[]'), true);
      $imageThumb  = is_array($imgs) && !empty($imgs[0]) ? $normalizeAssetPath($imgs[0]) : null;
      $brandIcon   = $normalizeAssetPath($p['brand_icon'] ?? $p['item_brand_icon'] ?? '');
      $thumb       = $brandIcon !== '' ? $brandIcon : $imageThumb;
      $thumbIsBrandIcon = $brandIcon !== '' && $thumb === $brandIcon;
      $bannerGrad  = $thumbIsBrandIcon ? $brandBannerGradient($brand, $catName, $title) : '';
      $label       = $statusLabel($statusRaw);
      $delivType   = trim((string)($p['delivery_type'] ?? $p['item_delivery_type'] ?? 'manual'));
      $cardCls     = $cardVariant($statusRaw);
      $badgeCls    = $badgeVariant($statusRaw);
      $statusIcon  = in_array($statusRaw, ['DELIVERED','COMPLETED'], true)
                     ? 'fa-circle-check' : ($statusRaw === 'UNPAID' ? 'fa-clock' : 'fa-bag-shopping');
    ?>
    <div class="col-12 col-md-6 col-xl-4 d-flex">
      <div class="av-acc-card w-100 <?= $cardCls ?>">

        <!-- Top -->
        <div class="av-acc-card__top">
          <div class="av-acc-card__rank-ico <?= $thumbIsBrandIcon ? 'av-acc-card__rank-ico--brand' : '' ?>" style="<?= $thumbIsBrandIcon ? 'background:' . $h($bannerGrad) . ';' : '' ?>">
            <?php if ($thumb): ?>
              <img src="<?= $h($thumb) ?>" alt="<?= $h($brand !== '' ? $brand : $title) ?>">
            <?php else: ?>
              <i class="fa-solid fa-layer-group"></i>
            <?php endif; ?>
          </div>

          <div class="av-acc-card__info">
            <div class="av-acc-card__title"><?= $h($title) ?></div>
            <div class="av-acc-card__sub">
              <?php if ($catName !== ''): ?>
                <span><?= $h($catName) ?></span><span>·</span>
              <?php endif; ?>
              <span>#DG<?= $id ?></span>
              <span>·</span>
              <span style="font-weight:900;text-transform:uppercase;font-size:.6rem;color:rgba(255,255,255,.22);"><?= $brand !== '' ? $h($brand) : 'Digital Good' ?></span>
              <?php if ($hasUnread): ?>
                <span class="av-unread-dot" title="New message from seller"></span>
              <?php endif; ?>
            </div>
          </div>

          <span class="av-acc-card__type-tag av-acc-card__type-tag--<?= $badgeCls ?>">
            <i class="fa-solid <?= $statusIcon ?>" style="font-size:.55rem;"></i>
            <?= $h($label) ?>
          </span>
        </div>

        <!-- Stats -->
        <div class="av-acc-card__stats">
          <div class="av-acc-card__stat">
            <div class="av-acc-card__stat-lbl">Total</div>
            <div class="av-acc-card__stat-val"><?= $h($price) ?></div>
          </div>
          <div class="av-acc-card__stat">
            <div class="av-acc-card__stat-lbl">Quantity</div>
            <div class="av-acc-card__stat-val">×<?= $qty ?></div>
          </div>
          <div class="av-acc-card__stat">
            <div class="av-acc-card__stat-lbl">Seller</div>
            <div class="av-acc-card__stat-val"><?= $h($seller) ?></div>
          </div>
        </div>

        <!-- Note -->
        <div class="av-acc-card__note">
          <i class="fa-solid fa-truck-fast"></i>
          <span><?= $h(ucfirst(str_replace('_', ' ', $delivType ?: 'manual'))) ?> delivery · Chat with seller</span>
        </div>

        <!-- Spacer pushes footer to bottom -->
        <div class="av-acc-card__spacer"></div>

        <!-- Footer -->
        <div class="av-acc-card__footer">
          <span class="av-acc-card__date"><?= $h($date) ?></span>
          <a href="<?= BASE_URL ?>/profile/digital-goods/<?= $id ?>" class="av-acc-btn av-acc-btn--primary">
            <?php if ($hasUnread): ?>
              <i class="fa-solid fa-comment-dots"></i> View · New msg
            <?php else: ?>
              <i class="fa-duotone fa-eye"></i> View
            <?php endif; ?>
          </a>
        </div>

      </div>
    </div>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>

</div>

<?= $this->start('scripts') ?>
<script>
(function () {
  document.querySelectorAll('[data-dg-filter]').forEach(function (box) {
    var btn = box.querySelector('button');
    if (!btn) return;
    btn.addEventListener('click', function (e) {
      e.preventDefault();
      var open = box.classList.toggle('is-open');
      btn.setAttribute('aria-expanded', open ? 'true' : 'false');
    });
    document.addEventListener('click', function (e) {
      if (!box.contains(e.target)) {
        box.classList.remove('is-open');
        btn.setAttribute('aria-expanded', 'false');
      }
    });
  });
})();
</script>
<?= $this->end() ?>
