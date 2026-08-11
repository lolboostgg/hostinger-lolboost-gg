<?= $this->layout('website/layouts/master', ['meta' => $meta, 'bodyClass' => 'gm-blog']) ?>

<?= $this->start('styles') ?>
<style>
/* ============================================================
   LoLBoost.gg — Blog categories
   Shares the blog's backdrop and hero language; the grid itself is
   its own compact "one panel per game" layout.
   ============================================================ */
.gm-blog main{padding-top:calc(var(--lb-content-top, calc(112px + var(--lb-sale-h, 0px))) + 24px);padding-bottom:80px;transition:padding-top .2s ease;}
.gm-blog a{text-decoration:none;color:inherit;}
.bg2-wrap{width:min(1300px,calc(100% - 40px));margin:0 auto;position:relative;z-index:1;}

/* Flat backdrop, same base colour as the landing page. */
.gm-blog{background:#04060f;}
.bg2-bg{position:fixed;inset:0;z-index:-2;pointer-events:none;background:#04060f;}

.bg2-hero{position:relative;padding:8px 24px clamp(26px,3.4vw,40px);text-align:center;overflow:hidden;}
.bg2-eyebrow{display:inline-flex;align-items:center;gap:9px;margin:0 auto 20px;padding:9px 18px;border-radius:999px;color:rgba(255,255,255,.78);border:1px solid rgba(255,255,255,.10);background:rgba(255,255,255,.04);letter-spacing:.18em;font-size:12px;font-weight:900;text-transform:uppercase;}
.bg2-eyebrow .dot{width:7px;height:7px;border-radius:999px;background:#8fb2ff;}
.bg2-hero-title{margin:0 auto 18px;max-width:820px;font-size:clamp(30px,4vw,46px);line-height:1.12;letter-spacing:-.02em;font-weight:900;color:#fff;}
.bg2-hero-title .accent{background-image:linear-gradient(92deg,#9db7ff 0%,#7c9bff 50%,#6d8cff 100%);-webkit-background-clip:text;background-clip:text;-webkit-text-fill-color:transparent;color:#6d8cff;}
.bg2-hero-sub{max-width:620px;margin:0 auto 24px;color:rgba(238,244,255,.8);font-size:clamp(15px,1.1vw,18px);line-height:1.65;font-weight:600;}

/* Back to all articles */
.bgc-actions{display:flex;justify-content:center;gap:10px;flex-wrap:wrap;}
.bgc-btn{display:inline-flex;align-items:center;gap:9px;height:44px;padding:0 20px;border-radius:999px;font-size:.9rem;font-weight:800;
  color:#fff;border:1px solid rgba(255,255,255,.10);background:rgba(255,255,255,.04);transition:border-color .18s,background .18s,transform .18s;}
.bgc-btn:hover{border-color:rgba(124,146,255,.32);background:rgba(255,255,255,.07);transform:translateY(-2px);}

/* Search */
.bgc-search{position:relative;max-width:420px;margin:22px auto 0;}
.bgc-search input{width:100%;height:48px;padding:0 18px 0 46px;border-radius:999px;border:1px solid rgba(255,255,255,.10);
  background:rgba(255,255,255,.04);color:#fff;font-size:.92rem;font-family:inherit;box-sizing:border-box;}
.bgc-search input::placeholder{color:rgba(255,255,255,.38);}
.bgc-search input:focus{outline:none;border-color:rgba(124,146,255,.4);}
.bgc-search i{position:absolute;left:18px;top:50%;transform:translateY(-50%);color:rgba(255,255,255,.35);font-size:.85rem;pointer-events:none;}

/* Category grid */
.bgc-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(300px,1fr));gap:16px;margin-top:34px;}
.bgc-card{position:relative;display:flex;flex-direction:column;gap:12px;padding:20px;border-radius:20px;
  background:#0a0d1e;border:1px solid rgba(255,255,255,.08);box-shadow:none;
  transition:transform .2s cubic-bezier(.22,1,.36,1),border-color .2s;}
.bgc-card:hover{transform:translateY(-3px);border-color:rgba(124,146,255,.28);}
.bgc-card__top{display:flex;align-items:center;gap:13px;}
.bgc-card__icon{width:46px;height:46px;flex:0 0 46px;border-radius:14px;display:grid;place-items:center;overflow:hidden;
  background:rgba(255,255,255,.05);border:1px solid rgba(255,255,255,.09);}
.bgc-card__icon img{width:30px;height:30px;object-fit:contain;display:block;}
.bgc-card__icon i{font-size:19px;color:#9db7ff;}
.bgc-card__name{min-width:0;font-size:1.06rem;font-weight:900;line-height:1.25;color:#fff;letter-spacing:-.01em;}
.bgc-card__count{margin-left:auto;flex:0 0 auto;display:inline-flex;align-items:center;gap:6px;padding:5px 11px;border-radius:999px;
  font-size:.72rem;font-weight:800;color:#b8c4e8;background:rgba(255,255,255,.05);border:1px solid rgba(255,255,255,.09);white-space:nowrap;}
.bgc-card__desc{font-size:.86rem;line-height:1.55;color:rgba(238,244,255,.55);
  display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden;}
.bgc-card__go{margin-top:auto;display:inline-flex;align-items:center;gap:7px;font-size:.8rem;font-weight:850;color:#9db7ff;}
.bgc-card__go i{font-size:.72rem;transition:transform .18s;}
.bgc-card:hover .bgc-card__go i{transform:translateX(3px);}

.bgc-empty{margin-top:34px;padding:52px 24px;text-align:center;border-radius:20px;
  background:#0a0d1e;border:1px solid rgba(255,255,255,.08);color:rgba(238,244,255,.6);}
.bgc-empty i{display:block;margin-bottom:12px;font-size:26px;color:rgba(157,183,255,.55);}

@media(max-width:640px){
  .bgc-grid{grid-template-columns:1fr;gap:12px;margin-top:26px;}
  .bgc-card{padding:16px;border-radius:17px;}
}
</style>
<?= $this->end('styles') ?>

<?php
// Games without an own description in the admin get a neutral one-liner, so the
// cards never render with an empty body.
$bgcDescription = static function (string $name): string {
    $name = trim($name);
    return $name !== ''
        ? 'Guides, news and tips for ' . $name . ' — written by our boosters and support team.'
        : 'Guides, news and tips from the LoLBoost team.';
};
$bgcIcon = static function ($icon, string $slug): string {
    $icon = trim((string)$icon);
    if ($icon === '' && function_exists('util_game_icon_url')) $icon = (string)util_game_icon_url($slug);
    if ($icon === '') return '';
    if (!preg_match('~^https?://~i', $icon)) $icon = rtrim(BASE_URL, '/') . '/' . ltrim($icon, '/');
    return $icon;
};
$bgcTotal = 0;
foreach ($categories as $bgcRow) $bgcTotal += (int)($bgcRow['article_count'] ?? 0);
$bgcTotal += (int)$uncategorised;
?>

<div class="bg2-bg" aria-hidden="true"></div>

<div class="bg2-wrap">

  <section class="bg2-hero">
    <div class="bg2-eyebrow"><span class="dot"></span><span><?= t('LoLBoost Blog') ?></span></div>
    <h1 class="bg2-hero-title"><?= t('What we') ?> <span class="accent"><?= t('write about') ?></span></h1>
    <p class="bg2-hero-sub"><?= t('Every article we publish belongs to a game. Pick a category to read only the guides that matter to you.') ?></p>

    <div class="bgc-actions">
      <a class="bgc-btn" href="<?= BASE_URL ?>/blog"><i class="fa-solid fa-arrow-left"></i><?= t('All articles') ?><?= $bgcTotal > 0 ? ' (' . number_format($bgcTotal) . ')' : '' ?></a>
    </div>

    <div class="bgc-search">
      <i class="fa-solid fa-magnifying-glass"></i>
      <input type="text" id="bgcSearch" placeholder="<?= t('Search categories…') ?>" autocomplete="off" spellcheck="false">
    </div>
  </section>

  <?php if (empty($categories) && (int)$uncategorised === 0): ?>
    <div class="bgc-empty">
      <i class="fa-duotone fa-folder-open"></i>
      <?= t('No categories yet. Assign a game to an article in the admin area and it will show up here.') ?>
    </div>
  <?php else: ?>
    <div class="bgc-grid" id="bgcGrid">
      <?php foreach ($categories as $bgcRow):
        $bgcName  = trim((string)($bgcRow['name'] ?? ''));
        $bgcSlug  = (string)($bgcRow['slug'] ?? '');
        $bgcCount = (int)($bgcRow['article_count'] ?? 0);
        $bgcImg   = $bgcIcon($bgcRow['icon'] ?? '', $bgcSlug);
      ?>
        <a class="bgc-card" data-name="<?= esc($bgcName) ?>" href="<?= BASE_URL ?>/blog/categories/<?= rawurlencode($bgcSlug) ?>">
          <div class="bgc-card__top">
            <span class="bgc-card__icon">
              <?php if ($bgcImg !== ''): ?>
                <img src="<?= esc($bgcImg) ?>" alt="" loading="lazy" decoding="async">
              <?php else: ?>
                <i class="fa-duotone fa-gamepad"></i>
              <?php endif; ?>
            </span>
            <span class="bgc-card__name"><?= esc($bgcName ?: ucwords(str_replace('-', ' ', $bgcSlug))) ?></span>
            <span class="bgc-card__count"><?= number_format($bgcCount) ?> <?= $bgcCount === 1 ? t('article') : t('articles') ?></span>
          </div>
          <div class="bgc-card__desc"><?= esc($bgcDescription($bgcName)) ?></div>
          <span class="bgc-card__go"><?= t('Browse articles') ?> <i class="fa-solid fa-arrow-right"></i></span>
        </a>
      <?php endforeach; ?>

      <?php if ((int)$uncategorised > 0): ?>
        <a class="bgc-card" data-name="General" href="<?= BASE_URL ?>/blog">
          <div class="bgc-card__top">
            <span class="bgc-card__icon"><i class="fa-duotone fa-newspaper"></i></span>
            <span class="bgc-card__name"><?= t('General') ?></span>
            <span class="bgc-card__count"><?= number_format((int)$uncategorised) ?> <?= (int)$uncategorised === 1 ? t('article') : t('articles') ?></span>
          </div>
          <div class="bgc-card__desc"><?= t('Articles that are not tied to a single game — platform news, buying guides and general gaming topics.') ?></div>
          <span class="bgc-card__go"><?= t('Browse articles') ?> <i class="fa-solid fa-arrow-right"></i></span>
        </a>
      <?php endif; ?>
    </div>
  <?php endif; ?>

</div>

<?= $this->start('scripts') ?>
<script>
(function () {
  var input = document.getElementById('bgcSearch');
  var grid  = document.getElementById('bgcGrid');
  if (!input || !grid) return;
  input.addEventListener('input', function () {
    var term = this.value.trim().toLowerCase();
    grid.querySelectorAll('.bgc-card').forEach(function (card) {
      var name = (card.dataset.name || '').toLowerCase();
      card.style.display = (!term || name.indexOf(term) !== -1) ? '' : 'none';
    });
  });
})();
</script>
<?= $this->end('scripts') ?>
