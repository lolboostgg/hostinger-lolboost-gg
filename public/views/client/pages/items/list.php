<?= $this->layout('client/layouts/main', ['meta' => $meta]) ?>
<?= $this->start('styles') ?>
<style>
.cl-items-page .av-section-head {
  display: flex; align-items: center; justify-content: space-between; margin-bottom: 14px;
}
.cl-items-page .av-section-title {
  font-size: .72rem; font-weight: 900; text-transform: uppercase;
  letter-spacing: .09em; color: rgba(255,255,255,.35);
  display: flex; align-items: center; gap: 7px;
}
.cl-items-page .av-section-title span {
  display: inline-flex; align-items: center; justify-content: center;
  min-width: 20px; height: 20px; padding: 0 6px; border-radius: 99px;
  font-size: .68rem; font-weight: 800;
  background: rgba(255,255,255,.07); color: rgba(255,255,255,.5);
}
.av-item-card {
  background: #25282a; border: 1px solid rgba(109,92,255,.12);
  border-radius: 18px; overflow: hidden;
  transition: border-color .15s, transform .15s, box-shadow .15s;
}
.av-item-card:hover {
  border-color: rgba(109,92,255,.28); transform: translateY(-2px);
  box-shadow: 0 8px 28px rgba(0,0,0,.3);
}
.av-item-card__top {
  display: flex; align-items: center; gap: 12px;
  padding: 14px 16px 12px; border-bottom: 1px solid rgba(255,255,255,.05);
}
.av-item-card__icon {
  width: 44px; height: 44px; border-radius: 11px; flex-shrink: 0;
  background: rgba(109,92,255,.12); border: 1px solid rgba(109,92,255,.2);
  display: flex; align-items: center; justify-content: center; overflow: hidden;
}
.av-item-card__icon img { width: 100%; height: 100%; object-fit: cover; display: block; }
.av-item-card__icon i { font-size: 1.1rem; color: #a5b4fc; }
.av-item-card__info { flex: 1; min-width: 0; }
.av-item-card__title {
  font-size: .9rem; font-weight: 800; color: rgba(255,255,255,.9);
  white-space: nowrap; overflow: hidden; text-overflow: ellipsis; line-height: 1.3;
}
.av-item-card__sub {
  font-size: .72rem; color: rgba(255,255,255,.35); margin-top: 2px;
  display: flex; align-items: center; gap: 5px;
}
.av-item-card__badge {
  display: inline-flex; align-items: center; gap: .3rem;
  padding: 3px 9px; border-radius: 99px; font-size: .68rem; font-weight: 800; flex-shrink: 0;
}
.av-item-card__badge--paid      { background: rgba(74,222,128,.12);  border: 1px solid rgba(74,222,128,.22);  color: #4ade80; }
.av-item-card__badge--unpaid    { background: rgba(251,191,36,.12);  border: 1px solid rgba(251,191,36,.22);  color: #fbbf24; }
.av-item-card__badge--cancelled { background: rgba(251,113,133,.12); border: 1px solid rgba(251,113,133,.22); color: #fb7185; }
.av-item-card__badge--default   { background: rgba(255,255,255,.06); border: 1px solid rgba(255,255,255,.1);  color: rgba(255,255,255,.4); }
.av-item-card__stats {
  display: grid; grid-template-columns: repeat(3, 1fr);
  border-bottom: 1px solid rgba(255,255,255,.05);
}
.av-item-card__stat { padding: 9px 14px; border-right: 1px solid rgba(255,255,255,.05); }
.av-item-card__stat:last-child { border-right: 0; }
.av-item-card__stat-lbl {
  font-size: .6rem; font-weight: 700; text-transform: uppercase;
  letter-spacing: .05em; color: rgba(255,255,255,.28); margin-bottom: 2px;
}
.av-item-card__stat-val {
  font-size: .8rem; font-weight: 800; color: rgba(255,255,255,.8);
  white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
}
.av-item-card__footer {
  padding: 10px 16px;
  display: flex; align-items: center; justify-content: space-between; gap: 8px;
}
.av-item-card__date { font-size: .72rem; color: rgba(255,255,255,.28); }
.av-item-btn {
  display: inline-flex; align-items: center; gap: .35rem;
  padding: 6px 13px; border-radius: 10px; font-size: .78rem; font-weight: 800;
  cursor: pointer; text-decoration: none; transition: background .12s, transform .1s;
}
.av-item-btn:hover { transform: translateY(-1px); }
.av-item-btn--primary { background: rgba(109,92,255,.25); border: 1px solid rgba(109,92,255,.35); color: #c4b5fd; }
.av-item-btn--primary:hover { background: rgba(109,92,255,.38); color: #e9d5ff; }
.av-empty {
  display: flex; flex-direction: column; align-items: center;
  justify-content: center; padding: 48px 20px; text-align: center;
  border: 1px dashed rgba(255,255,255,.08); border-radius: 18px; background: rgba(255,255,255,.01);
}
.av-empty__ico   { font-size: 2.2rem; opacity: .2; margin-bottom: 10px; }
.av-empty__title { font-size: .9rem; font-weight: 800; color: rgba(255,255,255,.35); margin-bottom: 4px; }
.av-empty__sub   { font-size: .78rem; color: rgba(255,255,255,.2); }
@media (max-width: 576px) {
  .av-item-card__stats { grid-template-columns: repeat(2, 1fr); }
  .av-item-card__stat:nth-child(2) { border-right: 0; }
  .av-item-card__stat:nth-child(3) { border-top: 1px solid rgba(255,255,255,.05); border-right: 0; }
}
</style>
<?= $this->end() ?>

<?php
$orders = $items ?? $orders ?? [];

$_ic_currency = strtoupper((string)($_SESSION['currency'] ?? 'EUR'));
$_ic_symbol   = function_exists('util_format_currency_display')
    ? util_format_currency_display($_ic_currency)
    : ($_ic_currency === 'USD' ? '$' : 'EUR');
function _ic_fmt_price(int $cents, string $sym, string $cur): string {
    $eur = $cents / 100;
    $rate = 1.0;
    if ($cur !== 'EUR' && function_exists('get_exchange_rate')) {
        $r = (float)get_exchange_rate(); if ($r > 0) $rate = $r;
    }
    return $sym . number_format($eur * $rate, 2);
}
if (!function_exists('_ic_type_label')) {
    function _ic_type_label(string $t): string {
        $m = ['skins'=>'Skins','skin'=>'Skins','chests-keys'=>'Chests & Keys','chest-key'=>'Chests & Keys',
              'chest'=>'Chests & Keys','orbs'=>'Orbs','orb'=>'Orbs','capsules'=>'Capsules','capsule'=>'Capsules',
              'event-pass'=>'Event Pass','pass'=>'Event Pass','bundles'=>'Bundles','bundle'=>'Bundles',
              'tft-item'=>'TFT Item','tft'=>'TFT Item','mystery-gift'=>'Mystery Gift','gifting'=>'Mystery Gift'];
        return $m[strtolower(trim($t))] ?? ucwords(str_replace(['-','_'],' ',$t));
    }
    function _ic_type_img(string $t): ?string {
        $stems = ['skins'=>'skins-item','chests-keys'=>'chest-item','orbs'=>'orbs-item',
                  'capsules'=>'capsules-item','event-pass'=>'event-pass-item','bundles'=>'bundle-item',
                  'tft-item'=>'tft-item','mystery-gift'=>null];
        $label = strtolower(_ic_type_label($t));
        $key   = trim(preg_replace('/[^a-z0-9]+/','-',$label),'-');
        if (!array_key_exists($key,$stems)||$stems[$key]===null) return null;
        return rtrim(ASSET_URL,'/').'/website/images/items/'.$stems[$key].'.webp';
    }
    function _ic_type_fa(string $t): string {
        $m = ['skins'=>'fa-shirt','chests-keys'=>'fa-key','orbs'=>'fa-circle-nodes','capsules'=>'fa-capsules',
              'event-pass'=>'fa-ticket','bundles'=>'fa-gift','tft-item'=>'fa-chess-board','mystery-gift'=>'fa-sparkles'];
        $label = strtolower(_ic_type_label($t));
        $key   = trim(preg_replace('/[^a-z0-9]+/','-',$label),'-');
        return 'fa-solid '.($m[$key] ?? 'fa-tag');
    }
    function _ic_badge_cls(string $s): string {
        $s = strtoupper($s);
        if (in_array($s,['PAID','COMPLETED','READY_TO_GIFT','CHAT_OPENED','WAITING_FRIENDSHIP'])) return 'paid';
        if (in_array($s,['CANCELLED','REFUNDED'])) return 'cancelled';
        if ($s === 'UNPAID') return 'unpaid';
        return 'default';
    }
}
?>

<div class="cl-items-page">

  <div class="av-section-head">
    <div class="av-section-title">
      <i class="fa-solid fa-gift"></i>
      All Items
      <span><?= count($orders) ?></span>
    </div>
    <a href="<?= BASE_URL ?>/lol/items" class="av-item-btn av-item-btn--primary" style="font-size:.72rem;padding:5px 11px;">
      <i class="fa-solid fa-plus"></i> Buy Items
    </a>
  </div>

  <?php if (empty($orders)): ?>
  <div class="av-empty">
    <div class="av-empty__ico"><i class="fa-solid fa-gift"></i></div>
    <div class="av-empty__title">No item orders yet</div>
    <div class="av-empty__sub">Your purchased items will appear here after checkout.</div>
    <a href="<?= BASE_URL ?>/lol/items" class="av-item-btn av-item-btn--primary" style="margin-top:14px;">
      <i class="fa-solid fa-store"></i> Browse Items
    </a>
  </div>

  <?php else: ?>
  <div class="row g-3">
    <?php foreach ($orders as $row):
      $type     = (string)($row['type'] ?? $row['item_type'] ?? '');
      $typeLbl  = _ic_type_label($type);
      $typeImg  = _ic_type_img($type);
      $typeFa   = _ic_type_fa($type);
      $status   = (string)($row['status'] ?? 'UNPAID');
      $bdgCls   = _ic_badge_cls($status);
      $server   = strtoupper((string)($row['server'] ?? ''));
      $qty      = (int)($row['quantity'] ?? 1);
      $price    = _ic_fmt_price((int)($row['price'] ?? 0), $_ic_symbol, $_ic_currency);
    ?>
    <div class="col-12 col-md-6 col-xl-4">
      <div class="av-item-card">

        <div class="av-item-card__top">
          <div class="av-item-card__icon">
            <?php if ($typeImg): ?>
              <img src="<?= htmlspecialchars($typeImg) ?>" alt="<?= htmlspecialchars($typeLbl) ?>">
            <?php else: ?>
              <i class="<?= htmlspecialchars($typeFa) ?>"></i>
            <?php endif; ?>
          </div>
          <div class="av-item-card__info">
            <div class="av-item-card__title"><?= htmlspecialchars($row['item_title'] ?? $row['title'] ?? 'Item') ?></div>
            <div class="av-item-card__sub">
              <?php if ($server): ?><span style="font-weight:700;"><?= $server ?></span><span>·</span><?php endif; ?>
              <span><?= htmlspecialchars($typeLbl) ?></span>
              <span>·</span><span>#<?= (int)$row['id'] ?></span>
            </div>
          </div>
          <span class="av-item-card__badge av-item-card__badge--<?= $bdgCls ?>"><?= htmlspecialchars($status) ?></span>
        </div>

        <div class="av-item-card__stats">
          <div class="av-item-card__stat">
            <div class="av-item-card__stat-lbl">Price</div>
            <div class="av-item-card__stat-val"><?= $price ?></div>
          </div>
          <div class="av-item-card__stat">
            <div class="av-item-card__stat-lbl">Quantity</div>
            <div class="av-item-card__stat-val">x<?= $qty ?></div>
          </div>
          <div class="av-item-card__stat">
            <div class="av-item-card__stat-lbl">Seller</div>
            <div class="av-item-card__stat-val"><?= htmlspecialchars($row['seller_username'] ?? '—') ?></div>
          </div>
        </div>

        <div class="av-item-card__footer">
          <span class="av-item-card__date">
            <?= !empty($row['created_at']) ? date('d.m.Y', strtotime($row['created_at'])) : '—' ?>
          </span>
          <a href="<?= BASE_URL ?>/profile/item/<?= (int)$row['id'] ?>" class="av-item-btn av-item-btn--primary">
            <i class="fa-solid fa-eye"></i> View
          </a>
        </div>

      </div>
    </div>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>

</div>
