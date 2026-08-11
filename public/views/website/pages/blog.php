<?= $this->layout('website/layouts/master', ['meta' => $meta, 'bodyClass' => 'gm-blog']) ?>

<?= $this->start('styles') ?>
<style>
/* ============================================================
   LoLBoost.gg — Blog (same visual system as Lootboxes/Loyalty)
   ============================================================ */
.gm-blog main{padding-top:calc(var(--lb-content-top, calc(112px + var(--lb-sale-h, 0px))) + 24px);padding-bottom:80px;transition:padding-top .2s ease;}
.gm-blog a{text-decoration:none;color:inherit;}
.bg2-wrap{width:min(1300px,calc(100% - 40px));margin:0 auto;position:relative;z-index:1;}

/* Flat backdrop, same base colour as the landing page. */
.gm-blog{background:#030817;}
.bg2-bg{position:fixed;inset:0;z-index:-2;pointer-events:none;background:#030817;}

/* Card recipe */
.bg2-card{background:#0a0d1e;border:1px solid rgba(255,255,255,.08);border-radius:18px;box-shadow:none;}

/* Scroll reveal (kept from the previous version) */
.bg2-fade{opacity:0;transform:translateY(22px);transition:opacity .6s cubic-bezier(.22,1,.36,1),transform .6s cubic-bezier(.22,1,.36,1);}
.bg2-fade.on{opacity:1;transform:none;}
@media(prefers-reduced-motion:reduce){.bg2-fade{opacity:1;transform:none;transition:none;}}

/* Hero — centered, same DNA as loyalty/lootboxes/landing */
.bg2-hero{position:relative;padding:8px 24px clamp(30px,4vw,48px);text-align:center;overflow:hidden;}
.bg2-eyebrow{display:inline-flex;align-items:center;gap:9px;margin:0 auto 20px;padding:9px 18px;border-radius:999px;color:rgba(255,255,255,.78);border:1px solid rgba(255,255,255,.10);background:rgba(255,255,255,.04);letter-spacing:.18em;font-size:12px;font-weight:900;text-transform:uppercase;}
.bg2-eyebrow .dot{width:7px;height:7px;border-radius:999px;background:#8fb2ff;}
@keyframes bg2Pulse{0%,100%{box-shadow:0 0 0 6px rgba(109,140,255,.16),0 0 18px rgba(109,140,255,.6);}50%{box-shadow:0 0 0 9px rgba(109,140,255,.22),0 0 26px rgba(109,140,255,.85);}}
@media(prefers-reduced-motion:reduce){.bg2-eyebrow .dot{animation:none!important;}}
.bg2-hero-title{margin:0 auto 18px;max-width:820px;font-size:clamp(30px,4vw,46px);line-height:1.12;letter-spacing:-.02em;font-weight:900;color:#fff;}
.bg2-hero-title .accent{background-image:linear-gradient(92deg,#9db7ff 0%,#7c9bff 50%,#6d8cff 100%);-webkit-background-clip:text;background-clip:text;-webkit-text-fill-color:transparent;color:#6d8cff;}
.bg2-hero-sub{max-width:600px;margin:0 auto 28px;color:rgba(238,244,255,.8);font-size:clamp(15px,1.1vw,18px);line-height:1.65;font-weight:600;}

/* Section rhythm */
.bg2-section{padding:34px 0;}
.bg2-section + .bg2-section{border-top:1px solid rgba(255,255,255,.06);}
.bg2-section-head{display:flex;align-items:center;justify-content:space-between;gap:16px;flex-wrap:wrap;margin-bottom:20px;}
.bg2-section-head h2{margin:0;font-size:clamp(20px,2.2vw,26px);font-weight:900;color:#fff;letter-spacing:-.01em;}
.bg2-count-pill{padding:5px 13px;border-radius:999px;background:rgba(255,255,255,.05);border:1px solid rgba(255,255,255,.09);font-size:12px;font-weight:700;color:rgba(255,255,255,.5);}

.bg2-tag{padding:5px 12px;border-radius:999px;background:rgba(255,255,255,.05);border:1px solid rgba(255,255,255,.09);font-size:11px;font-weight:800;color:#b8c4e8;letter-spacing:.06em;text-transform:uppercase;}
.bg2-date{font-size:12px;color:rgba(255,255,255,.45);font-weight:700;display:flex;align-items:center;gap:6px;}

/* Article grid */
.bg2-grid{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:16px;}
.bg2-article{overflow:hidden;display:flex;flex-direction:column;transition:transform .2s ease,border-color .2s ease;}
.bg2-article:hover{transform:translateY(-3px);border-color:rgba(124,146,255,.28);}
.bg2-art-img{width:100%;aspect-ratio:16/9;overflow:hidden;}
.bg2-art-img img{width:100%;height:100%;object-fit:cover;transition:transform .45s ease;}
.bg2-article:hover .bg2-art-img img{transform:scale(1.06);}
.bg2-art-body{padding:18px 18px 14px;display:flex;flex-direction:column;gap:9px;flex:1;}
.bg2-art-title{font-size:.98rem;font-weight:900;color:#fff;letter-spacing:-.01em;line-height:1.3;}
.bg2-art-excerpt{font-size:.84rem;line-height:1.6;color:rgba(255,255,255,.55);flex:1;}
.bg2-art-footer{padding:12px 18px 16px;border-top:1px solid rgba(255,255,255,.07);display:flex;align-items:center;justify-content:space-between;}
.bg2-art-link{font-size:.8rem;font-weight:800;color:#9db7ff;display:flex;align-items:center;gap:6px;}
.bg2-article:hover .bg2-art-link{color:#c7d6ff;}
@media(max-width:960px){.bg2-grid{grid-template-columns:repeat(2,minmax(0,1fr));}}
@media(max-width:600px){.bg2-grid{grid-template-columns:1fr;}}

/* Pagination */
.bg2-pagination{display:flex;align-items:center;justify-content:center;gap:6px;padding-top:8px;}
.bg2-page-btn{display:inline-flex;align-items:center;justify-content:center;min-width:42px;height:42px;padding:0 14px;border-radius:12px;border:1px solid rgba(255,255,255,.1);background:rgba(255,255,255,.04);color:rgba(255,255,255,.6);font-size:13px;font-weight:800;text-decoration:none;transition:background .16s ease,border-color .16s ease,color .16s ease,transform .16s ease;}
.bg2-page-btn:hover:not(.disabled):not(.active){transform:translateY(-1px);border-color:rgba(109,140,255,.4);color:#fff;}
.bg2-page-btn.active{background:#4f6ef7;border-color:transparent;color:#fff;}
.bg2-page-btn.disabled{opacity:.32;pointer-events:none;}

/* Game badge on the article cover (top right). */
.bg2-art-img { position: relative; }
.bg2-game-badge{
  position:absolute; top:12px; right:12px; z-index:3;
  width:38px; height:38px; border-radius:11px;
  display:flex; align-items:center; justify-content:center;
  background:rgba(12,14,22,.72); backdrop-filter:blur(6px);
  border:1px solid rgba(255,255,255,.14);
  box-shadow:0 6px 18px rgba(0,0,0,.35);
}
.bg2-game-badge img{ width:24px; height:24px; object-fit:contain; display:block; }

/* Category header + hero actions */
.bg2-cat-icon{width:1em;height:1em;vertical-align:-.12em;margin-right:.35em;object-fit:contain;display:inline-block;}
.bg2-hero-actions{display:flex;justify-content:center;gap:10px;flex-wrap:wrap;margin-top:4px;}
.bg2-hero-btn{display:inline-flex;align-items:center;gap:9px;height:44px;padding:0 20px;border-radius:999px;
  font-size:.9rem;font-weight:800;color:#fff;border:1px solid rgba(255,255,255,.10);background:rgba(255,255,255,.04);
  transition:border-color .18s,background .18s,transform .18s;}
.bg2-hero-btn:hover{border-color:rgba(124,146,255,.32);background:rgba(255,255,255,.07);transform:translateY(-2px);}
.bg2-hero-btn--accent{border-color:rgba(124,146,255,.28);background:rgba(79,110,247,.14);}
</style>
<?= $this->end() ?>

<?php
// Blog posts can be tagged with a game; the badge is rendered on the cover image.
$bgGameBadge = static function (array $article): string {
    $icon = trim((string)($article['game_icon'] ?? ''));
    if ($icon === '' && !empty($article['game_slug']) && function_exists('util_game_icon_url')) {
        $icon = (string)util_game_icon_url((string)$article['game_slug']);
    }
    if ($icon === '') return '';
    if (!preg_match('~^https?://~i', $icon)) $icon = rtrim(BASE_URL, '/') . '/' . ltrim($icon, '/');
    $name = trim((string)($article['game_name'] ?? ''));
    return '<span class="bg2-game-badge" title="' . htmlspecialchars($name, ENT_QUOTES) . '">'
         . '<img src="' . htmlspecialchars($icon, ENT_QUOTES) . '" alt="' . htmlspecialchars($name, ENT_QUOTES) . '" loading="lazy"></span>';
};
?>
<div class="bg2-bg" aria-hidden="true"></div>


<div class="bg2-wrap">

  <?php
    // A ?game= filter turns the generic blog header into the category header the
    // "Blog categories" page links to.
    $bgCategory = $category ?? [];
    $bgCatName  = trim((string)($bgCategory['name'] ?? ''));
    $bgCatIcon  = trim((string)($bgCategory['icon'] ?? ''));
    if ($bgCatIcon === '' && !empty($bgCategory['slug']) && function_exists('util_game_icon_url')) {
        $bgCatIcon = (string)util_game_icon_url((string)$bgCategory['slug']);
    }
    if ($bgCatIcon !== '' && !preg_match('~^https?://~i', $bgCatIcon)) {
        $bgCatIcon = rtrim(BASE_URL, '/') . '/' . ltrim($bgCatIcon, '/');
    }
  ?>
  <section class="bg2-hero">
    <?php if ($bgCatName !== ''): ?>
      <div class="bg2-eyebrow bg2-fade"><span class="dot"></span><span><?= t('Blog Category') ?></span></div>
      <h1 class="bg2-hero-title bg2-fade">
        <?php if ($bgCatIcon !== ''): ?><img class="bg2-cat-icon" src="<?= esc($bgCatIcon) ?>" alt="" loading="eager" decoding="async"><?php endif; ?>
        <span class="accent"><?= esc($bgCatName) ?></span> <?= t('Articles') ?>
      </h1>
      <p class="bg2-hero-sub bg2-fade"><?= t('All guides, news and tips we published for this game.') ?></p>
    <?php else: ?>
      <div class="bg2-eyebrow bg2-fade"><span class="dot"></span><span><?= t('LoLBoost Blog') ?></span></div>
      <h1 class="bg2-hero-title bg2-fade"><?= t('Guides &') ?> <span class="accent"><?= t('Insights') ?></span></h1>
      <p class="bg2-hero-sub bg2-fade"><?= t('Browse through a wide variety of gaming guides and articles to find the latest tips, tricks, and insights to enhance your gaming experience.') ?></p>
    <?php endif; ?>

    <div class="bg2-hero-actions bg2-fade">
      <?php if ($bgCatName !== ''): ?>
        <a class="bg2-hero-btn" href="<?= BASE_URL ?>/blog"><i class="fa-solid fa-arrow-left"></i><?= t('All articles') ?></a>
      <?php endif; ?>
      <a class="bg2-hero-btn bg2-hero-btn--accent" href="<?= BASE_URL ?>/blog/categories"><i class="fa-solid fa-layer-group"></i><?= t('Browse categories') ?></a>
    </div>
  </section>

  <section class="bg2-section" style="border-top:0;padding-top:8px;">
    <div class="bg2-section-head bg2-fade">
      <h2><?= t('Latest Articles') ?></h2>
      <span class="bg2-count-pill"><?= count($articles) ?> <?= t('articles') ?></span>
    </div>
    <div class="bg2-grid">
      <?php foreach (is_array($articles) ? $articles : [] as $article): ?>
        <article class="bg2-card bg2-article bg2-fade">
          <a href="<?= BASE_URL ?>/blog/<?= $article['slug'] ?>" class="bg2-art-img">
            <img src="<?= $article['image_url'] ?>" alt="<?= esc($article['title']) ?>" loading="lazy">
            <?= $bgGameBadge($article) ?>
          </a>
          <div class="bg2-art-body">
            <span class="bg2-tag" style="align-self:flex-start;"><?= t('Article') ?></span>
            <h3 class="bg2-art-title"><?= esc($article['title']) ?></h3>
            <p class="bg2-art-excerpt"><?= substr($article['excerpt'], 0, 120) ?>...</p>
          </div>
          <div class="bg2-art-footer">
            <span class="bg2-date"><i class="fa-solid fa-calendar"></i><time datetime="<?= $article['updated_at'] ?>"><?= util_format_date_display($article['updated_at']) ?></time></span>
            <a href="<?= BASE_URL ?>/blog/<?= $article['slug'] ?>" class="bg2-art-link"><?= t('Read') ?> <i class="fa-solid fa-arrow-right"></i></a>
          </div>
        </article>
      <?php endforeach; ?>
    </div>

<?php
// Paging stays inside the active category path (/blog/categories/<slug>?page=2).
$bgPageQuery = static function ($page) use ($pagination) {
    $base = !empty($pagination['category'])
        ? BASE_URL . '/blog/categories/' . rawurlencode((string)$pagination['category'])
        : BASE_URL . '/blog';
    return $base . ($page > 1 ? ('?page=' . (int)$page) : '');
}; ?>
    <div class="bg2-pagination bg2-fade" style="margin-top:26px;">
      <a href="<?= $bgPageQuery($pagination['page'] - 1) ?>" class="bg2-page-btn <?= $pagination['page'] <= 1 ? 'disabled' : '' ?>"><i class="fa-solid fa-chevron-left"></i></a>
      <?php for ($i = 1; $i <= $pagination['totalPages']; $i++): ?>
        <?php
          $diff = abs($i - $pagination['page']);
          if ($pagination['totalPages'] > 7 && $diff > 2 && $i !== 1 && $i !== $pagination['totalPages']):
            if ($diff === 3): ?><span class="bg2-page-btn" style="pointer-events:none;border:none;background:none;">…</span><?php endif;
            continue;
          endif;
        ?>
        <a href="<?= $bgPageQuery($i) ?>" class="bg2-page-btn <?= $i == $pagination['page'] ? 'active' : '' ?>"><?= $i ?></a>
      <?php endfor; ?>
      <a href="<?= $bgPageQuery($pagination['page'] + 1) ?>" class="bg2-page-btn <?= $pagination['page'] >= $pagination['totalPages'] ? 'disabled' : '' ?>"><i class="fa-solid fa-chevron-right"></i></a>
    </div>
  </section>

</div>

<?= $this->start('scripts') ?>
<script>
(function(){
  var io = new IntersectionObserver(function(entries){
    entries.forEach(function(e){ if(e.isIntersecting){ e.target.classList.add('on'); io.unobserve(e.target); } });
  },{threshold:.08, rootMargin:'0px 0px -28px 0px'});
  document.querySelectorAll('.bg2-fade').forEach(function(el){ io.observe(el); });
})();
</script>
<?= $this->end() ?>
