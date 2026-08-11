<?php echo $this->layout('seller/layouts/main', ['meta' => ['title' => (($item['title'] ?? 'Item') . ' | LoLBoost.gg')]]); ?>

<?php
require_once dirname(__DIR__) . '/_seller_rank.php';
$item = is_array($item ?? null) ? $item : [];
$seller_data = is_array($seller_data ?? null) ? $seller_data : [];

$images = json_decode((string)($item['images'] ?? '[]'), true);
if (!is_array($images)) $images = [];
$cover = $images[0] ?? (defined('ASSET_URL') ? ASSET_URL . '/public/uploads/icons/default2.png' : '');

$priceCents = (int)($item['price'] ?? 0);
$priceRaw = $priceCents / 100;
$effective_fee = seller_effective_fee_from_rank(is_array($seller_data ?? null) ? $seller_data : []);
$earningsRaw = $priceRaw * (1 - ($effective_fee / 100));
$soldCount = (int)($item['sold_count'] ?? 0);
$status = $soldCount > 0 ? 'Sold' : (((int)($item['active'] ?? 1) === 1) ? 'Active' : 'Unlisted');
$badgeCls = $status === 'Sold' ? 'iv-badge--sold' : ($status === 'Active' ? 'iv-badge--active' : 'iv-badge--unlisted');
?>

<?php echo $this->start('styles'); ?>
<style>
.iv-wrap{display:flex;flex-direction:column;gap:18px}
.iv-card{background:#25282a;border:1px solid rgba(255,255,255,.07);border-radius:20px;box-shadow:0 4px 32px rgba(0,0,0,.22)}
.iv-hero{padding:24px}
.iv-hero-top{display:flex;justify-content:space-between;align-items:flex-start;gap:16px;flex-wrap:wrap}
.iv-hero-left{display:flex;gap:16px;align-items:center;min-width:0}
.iv-cover{width:84px;height:84px;border-radius:16px;object-fit:cover;background:rgba(255,255,255,.04);flex-shrink:0}
.iv-title{font-size:1.4rem;font-weight:900;color:#fff;margin:0 0 6px;line-height:1.15}
.iv-sub{color:rgba(255,255,255,.4);font-size:.85rem}
.iv-meta{display:flex;flex-wrap:wrap;gap:8px;margin-top:10px}
.iv-chip{display:inline-flex;align-items:center;gap:.35rem;padding:6px 10px;border-radius:999px;background:rgba(255,255,255,.05);border:1px solid rgba(255,255,255,.08);color:rgba(255,255,255,.72);font-size:.76rem;font-weight:800}
.iv-btn{display:inline-flex;align-items:center;justify-content:center;gap:.45rem;min-height:40px;padding:0 16px;border-radius:11px;border:1px solid rgba(255,255,255,.10);text-decoration:none;cursor:pointer;background:rgba(255,255,255,.04);color:#fff;font-weight:800}
.iv-btn:hover{background:rgba(255,255,255,.08);color:#fff}
.iv-btn-primary{background:linear-gradient(135deg,#6d5cff,#b05cff);border:none}
.iv-grid{display:grid;grid-template-columns:minmax(0,1fr) 360px;gap:18px}
.iv-section{padding:20px}
.iv-section-title{font-size:.78rem;text-transform:uppercase;letter-spacing:.08em;color:rgba(255,255,255,.35);font-weight:900;margin:0 0 12px}
.iv-gallery{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:12px}
.iv-gallery img{width:100%;height:180px;object-fit:cover;border-radius:14px;background:rgba(255,255,255,.04)}
.iv-empty-gallery{height:220px;border:1px dashed rgba(255,255,255,.12);border-radius:16px;display:flex;align-items:center;justify-content:center;color:rgba(255,255,255,.28)}
.iv-desc{color:rgba(255,255,255,.82);line-height:1.7;font-size:.92rem;white-space:pre-wrap;word-break:break-word}
.iv-stat-list{display:flex;flex-direction:column;gap:10px}
.iv-stat{display:flex;align-items:center;justify-content:space-between;gap:14px;padding:11px 0;border-bottom:1px solid rgba(255,255,255,.06)}
.iv-stat:last-child{border-bottom:none}
.iv-stat-label{color:rgba(255,255,255,.45);font-size:.83rem}
.iv-stat-value{color:#fff;font-weight:800}
.iv-badge{display:inline-flex;align-items:center;gap:.35rem;padding:6px 10px;border-radius:999px;font-size:.74rem;font-weight:900}
.iv-badge--active{background:rgba(74,222,128,.12);border:1px solid rgba(74,222,128,.28);color:#4ade80}
.iv-badge--unlisted{background:rgba(250,204,21,.12);border:1px solid rgba(250,204,21,.30);color:#facc15}
.iv-badge--sold{background:rgba(251,113,133,.12);border:1px solid rgba(251,113,133,.28);color:#fb7185}
@media (max-width:1200px){.iv-grid{grid-template-columns:1fr}}
@media (max-width:768px){.iv-gallery{grid-template-columns:repeat(2,minmax(0,1fr))}.iv-gallery img{height:140px}}
@media (max-width:520px){.iv-hero-left{align-items:flex-start}.iv-cover{width:68px;height:68px}.iv-title{font-size:1.1rem}.iv-gallery{grid-template-columns:1fr}.iv-gallery img{height:180px}}
</style>
<?php echo $this->end(); ?>

<div class="iv-wrap">
  <div class="iv-card iv-hero">
    <div class="iv-hero-top">
      <div class="iv-hero-left">
        <?php if (!empty($cover)): ?>
          <img class="iv-cover" src="<?= htmlspecialchars($cover) ?>" alt="">
        <?php endif; ?>
        <div style="min-width:0">
          <h1 class="iv-title"><?= htmlspecialchars($item['title'] ?? 'Untitled Item') ?></h1>
          <div class="iv-sub">#<?= (int)($item['id'] ?? 0) ?><?= !empty($item['slug']) ? ' • ' . htmlspecialchars((string)$item['slug']) : '' ?></div>
          <div class="iv-meta">
            <span class="iv-chip"><?= htmlspecialchars(strtoupper((string)($item['server'] ?? 'EUW'))) ?></span>
            <span class="iv-chip"><?= htmlspecialchars(ucwords(str_replace(['_', '-'], ' ', (string)($item['type'] ?? 'Item')))) ?></span>
            <span class="iv-chip">Stock <?= (int)($item['stock'] ?? 1) ?></span>
            <span class="iv-chip">Sold <?= $soldCount ?></span>
            <span class="iv-badge <?= $badgeCls ?>"><?= $status ?></span>
          </div>
        </div>
      </div>

      <div style="display:flex;gap:10px;flex-wrap:wrap">
        <a class="iv-btn" href="<?= BASE_URL ?>/seller-area/items">Back</a>
        <button class="iv-btn iv-btn-primary" type="button" data-bs-toggle="offcanvas" data-bs-target="#editItemCanvas<?= (int)($item['id'] ?? 0) ?>">Edit Item</button>
      </div>
    </div>
  </div>

  <div class="iv-grid">
    <div class="iv-card iv-section">
      <h2 class="iv-section-title">Gallery</h2>
      <?php if (!empty($images)): ?>
        <div class="iv-gallery">
          <?php foreach ($images as $img): ?>
            <a href="<?= htmlspecialchars((string)$img) ?>" target="_blank" rel="noopener">
              <img src="<?= htmlspecialchars((string)$img) ?>" alt="">
            </a>
          <?php endforeach; ?>
        </div>
      <?php else: ?>
        <div class="iv-empty-gallery"><div>No images uploaded yet.</div></div>
      <?php endif; ?>
    </div>

    <div class="iv-card iv-section">
      <h2 class="iv-section-title">Overview</h2>
      <div class="iv-stat-list">
        <div class="iv-stat"><div class="iv-stat-label">Price</div><div class="iv-stat-value">€<?= number_format($priceRaw, 2) ?></div></div>
        <div class="iv-stat"><div class="iv-stat-label">Earnings</div><div class="iv-stat-value" style="color:#4ade80">€<?= number_format($earningsRaw, 2) ?></div></div>
        <div class="iv-stat"><div class="iv-stat-label">Stock</div><div class="iv-stat-value"><?= (int)($item['stock'] ?? 1) ?></div></div>
        <div class="iv-stat"><div class="iv-stat-label">Min Qty</div><div class="iv-stat-value"><?= (int)($item['min_purchase_qty'] ?? 1) ?></div></div>
        <div class="iv-stat"><div class="iv-stat-label">Max Qty</div><div class="iv-stat-value"><?= !empty($item['max_purchase_qty']) ? (int)$item['max_purchase_qty'] : '—' ?></div></div>
        <div class="iv-stat"><div class="iv-stat-label">Friendship Days</div><div class="iv-stat-value"><?= (int)($item['requires_friendship_days'] ?? 7) ?></div></div>
        <div class="iv-stat"><div class="iv-stat-label">Created</div><div class="iv-stat-value"><?= !empty($item['created_at']) ? htmlspecialchars((string)$item['created_at']) : '—' ?></div></div>
      </div>
    </div>

    <div class="iv-card iv-section" style="grid-column:1 / -1">
      <h2 class="iv-section-title">Description</h2>
      <div class="iv-desc"><?= htmlspecialchars((string)($item['description'] ?? '')) ?></div>
    </div>
  </div>
</div>
