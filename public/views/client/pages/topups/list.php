<?= $this->layout('client/layouts/main', ['meta' => ['title' => 'My Top Ups | LoLBoost.gg', 'h1' => 'My Top Ups']]) ?>
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
.av-item-card__icon img { width: 100%; height: 100%; object-fit: contain; display: block; padding: 5px; }
.av-item-card__icon i { font-size: 1.1rem; color: #a5b4fc; }
.av-item-card__info { flex: 1; min-width: 0; }
.av-item-card__title {
  font-size: .9rem; font-weight: 800; color: rgba(255,255,255,.9);
  white-space: nowrap; overflow: hidden; text-overflow: ellipsis; line-height: 1.3;
}
.av-item-card__sub {
  font-size: .72rem; color: rgba(255,255,255,.35); margin-top: 2px;
  display: flex; align-items: center; gap: 5px; flex-wrap: wrap;
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
$orders = is_array($topups ?? null) ? $topups : (is_array($orders ?? null) ? $orders : []);

$_tu_currency = strtoupper((string)($_SESSION['currency'] ?? 'EUR'));
$_tu_symbol   = function_exists('util_format_currency_display')
    ? util_format_currency_display($_tu_currency)
    : ($_tu_currency === 'USD' ? '$' : '€');

if (!function_exists('_tu_h')) {
    function _tu_h($v): string { return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }
    function _tu_fmt_price(int $cents, string $sym, string $cur): string {
        $eur = $cents / 100;
        $rate = 1.0;
        if ($cur !== 'EUR' && function_exists('get_exchange_rate')) {
            $r = (float)get_exchange_rate();
            if ($r > 0) $rate = $r;
        }
        return $sym . number_format($eur * $rate, 2);
    }
    function _tu_amount($v, $u = ''): string {
        $n = is_numeric($v) ? rtrim(rtrim(number_format((float)$v, 2, '.', ''), '0'), '.') : trim((string)$v);
        return trim($n . ' ' . trim((string)$u));
    }
    function _tu_wait(array $r): string {
        $v = (int)($r['waiting_time_value'] ?? 0);
        $u = strtolower((string)($r['waiting_time_unit'] ?? 'minutes'));
        if ($v <= 0 && !empty($r['waiting_time_minutes'])) {
            $m = (int)$r['waiting_time_minutes'];
            if ($m % 1440 === 0 && $m > 0) { $v = (int)($m / 1440); $u = 'days'; }
            elseif ($m % 60 === 0 && $m > 0) { $v = (int)($m / 60); $u = 'hours'; }
            else { $v = $m; $u = 'minutes'; }
        }
        if ($v <= 0) { $v = 0; $u = 'minutes'; }
        $base = rtrim($u, 's');
        return $v . ' ' . $base . ($v === 1 ? '' : 's');
    }
    function _tu_badge_cls(string $s): string {
        $s = strtoupper($s);
        if (in_array($s, ['PAID','COMPLETED','DELIVERED','PROCESSING'], true)) return 'paid';
        if (in_array($s, ['CANCELLED','REFUNDED'], true)) return 'cancelled';
        if ($s === 'UNPAID') return 'unpaid';
        return 'default';
    }
    function _tu_game_name(array $row): string {
        $name = trim((string)($row['game_name'] ?? $row['db_game_name'] ?? ''));
        $slug = trim((string)($row['game_slug'] ?? $row['game'] ?? ''));
        if ($name !== '') return $name;
        return $slug !== '' ? ucwords(str_replace('-', ' ', $slug)) : 'Game';
    }
    function _tu_game_icon(array $row): string {
        return trim((string)($row['game_icon'] ?? ''));
    }
}
?>

<div class="cl-items-page">

  <div class="av-section-head">
    <div class="av-section-title">
      <i class="fa-duotone fa-coins"></i>
      All Top Ups
      <span><?= count($orders) ?></span>
    </div>
    <a href="<?= BASE_URL ?>/services/top-ups" class="av-item-btn av-item-btn--primary" style="font-size:.72rem;padding:5px 11px;">
      <i class="fa-solid fa-plus"></i> Buy Top Ups
    </a>
  </div>

  <?php if (empty($orders)): ?>
  <div class="av-empty">
    <div class="av-empty__ico"><i class="fa-duotone fa-coins"></i></div>
    <div class="av-empty__title">No top up orders yet</div>
    <div class="av-empty__sub">Your purchased top ups will appear here after checkout.</div>
    <a href="<?= BASE_URL ?>/services/top-ups" class="av-item-btn av-item-btn--primary" style="margin-top:14px;">
      <i class="fa-solid fa-store"></i> Browse Top Ups
    </a>
  </div>

  <?php else: ?>
  <div class="row g-3">
    <?php foreach ($orders as $row):
      $status   = (string)($row['status'] ?? 'PAID');
      $bdgCls   = _tu_badge_cls($status);
      $qty      = (int)($row['quantity'] ?? 1);
      $price    = _tu_fmt_price((int)($row['price'] ?? 0), $_tu_symbol, $_tu_currency);
      $gameName = _tu_game_name($row);
      $gameIcon = _tu_game_icon($row);
      $offer    = (string)($row['offer_title'] ?? 'Top Up');
      $amount   = _tu_amount($row['offer_amount'] ?? '', $row['offer_unit'] ?? '');
      $region   = trim((string)($row['region'] ?? ''));
      $platform = trim((string)($row['platform'] ?? ''));
    ?>
    <div class="col-12 col-md-6 col-xl-4">
      <div class="av-item-card">

        <div class="av-item-card__top">
          <div class="av-item-card__icon">
            <?php if ($gameIcon): ?>
              <img src="<?= _tu_h($gameIcon) ?>" alt="<?= _tu_h($gameName) ?>">
            <?php else: ?>
              <i class="fa-duotone fa-coins"></i>
            <?php endif; ?>
          </div>
          <div class="av-item-card__info">
            <div class="av-item-card__title"><?= _tu_h($offer) ?></div>
            <div class="av-item-card__sub">
              <span style="font-weight:800;color:rgba(255,255,255,.62);"><?= _tu_h($gameName) ?></span>
              <?php if ($amount): ?><span>·</span><span><?= _tu_h($amount) ?></span><?php endif; ?>
              <?php if ($region): ?><span>·</span><span><?= _tu_h($region) ?></span><?php endif; ?>
              <span>·</span><span>#<?= (int)($row['id'] ?? 0) ?></span>
            </div>
          </div>
          <span class="av-item-card__badge av-item-card__badge--<?= $bdgCls ?>"><?= _tu_h($status) ?></span>
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
            <div class="av-item-card__stat-val"><?= _tu_h($row['seller_username'] ?? '—') ?></div>
          </div>
        </div>

        <div class="av-item-card__footer">
          <span class="av-item-card__date">
            <?= !empty($row['created_at']) ? date('d.m.Y', strtotime($row['created_at'])) : '—' ?>
            <?php if ($platform): ?> · <?= _tu_h($platform) ?><?php endif; ?>
            · <?= _tu_h(_tu_wait($row)) ?>
          </span>
          <a href="<?= BASE_URL ?>/profile/top-up/<?= (int)($row['id'] ?? 0) ?>" class="av-item-btn av-item-btn--primary">
            <i class="fa-solid fa-eye"></i> View
          </a>
        </div>

      </div>
    </div>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>

</div>
