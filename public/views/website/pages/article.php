<?= $this->layout('website/layouts/master', ['meta' => $meta, 'bodyClass' => 'article-page']) ?>

<?= $this->start('styles') ?>
<style>
/* ============================================================
   LoLBoost.gg — Article (same visual system as Lootboxes/Loyalty/Blog)
   ============================================================ */
.article-page main{padding-top:calc(var(--lb-content-top, calc(112px + var(--lb-sale-h, 0px))) + 24px);padding-bottom:80px;transition:padding-top .2s ease;}
.article-page a{text-decoration:none;color:inherit;}

/* Flat backdrop, same base colour as the landing page. */
.article-page{background:#04060f;}
.at2-bg{position:fixed;inset:0;z-index:-2;pointer-events:none;background:#04060f;}

/* Card recipe */
.at2-card{background:#0a0d1e;border:1px solid rgba(255,255,255,.08);border-radius:18px;box-shadow:none;}

/* Reading progress bar */
#readingProgress{position:fixed;top:0;left:0;width:100%;height:3px;z-index:1200;background:rgba(255,255,255,.06);}
#readingProgress .bar{height:100%;width:0;background:#4f6ef7;transition:width .1s linear;}

/* Hero (plain div — a literal <header> tag triggers a sitewide !important padding-top rule that doubles the navbar offset) */
.at2-hero{padding:8px 24px clamp(24px,3vw,38px);text-align:center;}
.at2-eyebrow{display:inline-flex;align-items:center;gap:9px;margin:0 auto 18px;padding:9px 18px;border-radius:999px;color:rgba(255,255,255,.78);border:1px solid rgba(255,255,255,.10);background:rgba(255,255,255,.04);letter-spacing:.18em;font-size:12px;font-weight:900;text-transform:uppercase;transition:border-color .16s,background .16s;}
a.at2-eyebrow:hover{border-color:rgba(124,146,255,.32);background:rgba(255,255,255,.07);color:#fff;}
.at2-eyebrow .dot{width:7px;height:7px;border-radius:999px;background:#8fb2ff;box-shadow:0 0 0 6px rgba(109,140,255,.16),0 0 18px rgba(109,140,255,.6);}
.at2-title{margin:0 auto 18px;max-width:900px;font-size:clamp(26px,3.6vw,42px);line-height:1.15;letter-spacing:-.02em;font-weight:900;color:#fff;}
.at2-meta{display:flex;align-items:center;justify-content:center;gap:18px;flex-wrap:wrap;color:rgba(255,255,255,.5);font-size:.86rem;font-weight:700;}
.at2-meta span{display:inline-flex;align-items:center;gap:7px;}
.at2-meta i{color:#8fb2ff;}

/* Outer grid: left banner | article content | right banner */
.article-page-wrap{display:grid;grid-template-columns:230px minmax(0,1fr) 230px;gap:52px;width:calc(100vw - 96px);max-width:1880px;margin:0 auto 80px;padding:0;align-items:start;}
.article-page-wrap > .container{width:100%;max-width:100%;padding-left:0;padding-right:0;}
.blog-side-banner{position:sticky;top:120px;align-self:start;display:block;width:230px;z-index:20;margin-top:38px;transition:transform .2s ease,opacity .2s ease;}
.blog-side-banner-left{justify-self:start;}
.blog-side-banner-right{justify-self:end;}
.blog-side-banner:hover{transform:scale(1.03);}
.blog-side-banner img{width:100%;height:auto;display:block;border-radius:18px;}
@media(min-width:1800px){.article-page-wrap{width:calc(100vw - 120px);grid-template-columns:250px minmax(0,1fr) 250px;gap:60px;}.blog-side-banner{width:250px;}}
@media(max-width:1500px){.article-page-wrap{width:calc(100vw - 48px);grid-template-columns:205px minmax(0,1fr) 205px;gap:28px;}.blog-side-banner{width:205px;}}
@media(max-width:1200px){.article-page-wrap{display:block;width:min(1200px,calc(100% - 40px));max-width:none;margin:0 auto;padding:0;}.blog-side-banner{display:none;}}

/* Layout: TOC + Content */
.at2-layout{display:grid;grid-template-columns:220px minmax(0,1fr);gap:26px;align-items:start;padding-top:10px;}
.at2-toc{position:sticky;top:calc(var(--lb-content-top, 112px) + 16px);padding:18px;}
.at2-toc-head{display:flex;align-items:center;justify-content:space-between;gap:10px;margin-bottom:10px;}
.at2-toc-head h4{margin:0;font-size:.8rem;font-weight:900;color:#fff;text-transform:uppercase;letter-spacing:.08em;}
.at2-toc-toggle{display:none;}
#tocNav{display:flex;flex-direction:column;gap:2px;max-height:60vh;overflow-y:auto;}
#tocNav a{display:block;padding:7px 9px;border-radius:9px;color:rgba(255,255,255,.55);font-size:.82rem;font-weight:700;line-height:1.4;border-left:2px solid transparent;transition:color .15s ease,background .15s ease,border-color .15s ease;}
#tocNav a:hover{color:#fff;background:rgba(109,140,255,.08);}
#tocNav a.active{color:#9db7ff;border-left-color:#6d8cff;background:rgba(109,140,255,.1);}

/* Share row */
.at2-share-row{display:flex;justify-content:flex-end;margin-bottom:18px;}
.meta-btn{display:inline-flex;align-items:center;gap:8px;border:1px solid rgba(255,255,255,.12);background:rgba(255,255,255,.04);color:rgba(255,255,255,.8);border-radius:10px;padding:8px 14px;font-weight:800;font-size:.82rem;cursor:pointer;font-family:inherit;transition:border-color .15s ease,color .15s ease,background .15s ease;}
.meta-btn:hover{color:#fff;border-color:rgba(109,140,255,.4);background:rgba(109,140,255,.1);}

.at2-content-card{padding:clamp(24px,4vw,48px);}
.article-content{color:rgba(255,255,255,.78);font-size:1rem;line-height:1.8;}
.article-content h1,.article-content h2,.article-content h3{color:#fff;font-weight:900;letter-spacing:-.01em;margin:1.6em 0 .6em;}
.article-content h1{font-size:1.7rem;}
.article-content h2{font-size:1.4rem;}
.article-content h3{font-size:1.15rem;}
.article-content p{margin:0 0 1.1em;}
.article-content img{max-width:100%;height:auto;border-radius:16px;margin:1.2em 0;}
.article-content a{color:#9db7ff;text-decoration:underline;text-underline-offset:3px;}
.article-content a:hover{color:#c7d6ff;}
.article-content ul,.article-content ol{margin:0 0 1.1em;padding-left:1.4em;}
.article-content li{margin-bottom:.4em;}
.article-content blockquote{margin:1.4em 0;padding:14px 18px;border-left:3px solid #6d8cff;background:rgba(109,140,255,.08);border-radius:0 12px 12px 0;color:rgba(255,255,255,.7);}
.article-content code{background:rgba(0,0,0,.3);border:1px solid rgba(109,140,255,.2);border-radius:6px;padding:2px 6px;font-size:.9em;}

@media(max-width:960px){
  .at2-layout{grid-template-columns:1fr;}
  .at2-toc{position:relative;top:auto;display:none;}
  .at2-toc.open{display:block;}
  .at2-toc-toggle{display:inline-flex;align-items:center;gap:8px;border:1px solid rgba(255,255,255,.12);background:rgba(255,255,255,.04);color:rgba(255,255,255,.8);border-radius:10px;padding:8px 12px;font-weight:800;font-size:.82rem;cursor:pointer;}
}

/* Back to top */
#backToTop{position:fixed;right:24px;bottom:24px;z-index:900;display:none;align-items:center;justify-content:center;width:48px;height:48px;border-radius:50%;background:#4f6ef7;border:0;color:#fff;box-shadow:none;cursor:pointer;font-size:1rem;}
#backToTop:hover{filter:brightness(1.08);}

/* Breadcrumb */
.at2-crumbs{width:min(1300px,calc(100% - 40px));margin:0 auto 18px;display:flex;align-items:center;flex-wrap:wrap;gap:9px;
  font-size:.8rem;font-weight:700;color:rgba(255,255,255,.42);}
.at2-crumbs a{color:rgba(255,255,255,.62);transition:color .16s;}
.at2-crumbs a:hover{color:#fff;}
.at2-crumbs i{font-size:.6rem;opacity:.35;}
.at2-crumbs span[aria-current]{color:rgba(255,255,255,.85);max-width:min(460px,60vw);overflow:hidden;text-overflow:ellipsis;white-space:nowrap;}
@media(max-width:640px){.at2-crumbs{font-size:.74rem;gap:7px;}.at2-crumbs span[aria-current]{max-width:100%;white-space:normal;}}
</style>
<?= $this->end() ?>

<div class="at2-bg" aria-hidden="true"></div>

<div id="readingProgress" aria-hidden="true"><div class="bar"></div></div>

<?php
// Breadcrumb path: Gaming Blog › Categories › <Game> › <Article>.
// The category steps only appear when the article is assigned to a game.
$atCatName = trim((string)($article['game_name'] ?? ''));
$atCatSlug = trim((string)($article['game_slug'] ?? ''));
?>
<nav class="at2-crumbs" aria-label="<?= t('Breadcrumb') ?>">
  <a href="<?= BASE_URL ?>/blog"><?= t('Gaming Blog') ?></a>
  <?php if ($atCatSlug !== ''): ?>
    <i class="fa-solid fa-chevron-right"></i>
    <a href="<?= BASE_URL ?>/blog/categories"><?= t('Categories') ?></a>
    <i class="fa-solid fa-chevron-right"></i>
    <a href="<?= BASE_URL ?>/blog/categories/<?= rawurlencode($atCatSlug) ?>"><?= esc($atCatName ?: ucwords(str_replace('-', ' ', $atCatSlug))) ?></a>
  <?php endif; ?>
  <i class="fa-solid fa-chevron-right"></i>
  <span aria-current="page"><?= esc($article['title']) ?></span>
</nav>

<div class="at2-hero">
  <?php if ($atCatSlug !== ''): ?>
    <a class="at2-eyebrow" href="<?= BASE_URL ?>/blog/categories/<?= rawurlencode($atCatSlug) ?>"><span class="dot"></span><span><?= esc($atCatName ?: ucwords(str_replace('-', ' ', $atCatSlug))) ?></span></a>
  <?php else: ?>
    <div class="at2-eyebrow"><span class="dot"></span><span><?= t('LoLBoost Blog') ?></span></div>
  <?php endif; ?>
  <h1 class="at2-title"><?= esc($article['title']) ?></h1>
  <div class="at2-meta">
    <span><i class="fa-duotone fa-calendar"></i><time datetime="<?= $article['updated_at'] ?>"><?= util_format_date_display_hm($article['updated_at']) ?></time></span>
    <span><i class="fa-duotone fa-clock"></i><span id="readingTime">—</span></span>
  </div>
</div>

<div class="article-page-wrap">
  <a class="blog-side-banner blog-side-banner-left" href="https://lolboost.gg/lol/rank-boost" aria-label="Boosting">
    <img src="/public/assets/website/images/boosting_blog.png" alt="Boosting" onerror="this.onerror=null;this.src='/assets/website/images/boosting_blog.png';">
  </a>

  <div class="container">
    <div class="at2-share-row">
      <button class="meta-btn" id="shareBtn" type="button">
        <i class="fa-duotone fa-link"></i>
        <span><?= t('Copy Link') ?></span>
      </button>
    </div>

    <div class="at2-layout">
      <aside class="at2-toc at2-card" id="articleToc" aria-label="<?= t('Table of contents') ?>">
        <div class="at2-toc-head">
          <h4><?= t('On this page') ?></h4>
          <button class="at2-toc-toggle" id="tocToggle" type="button">
            <i class="fa-duotone fa-list"></i>
            <span><?= t('Contents') ?></span>
          </button>
        </div>
        <nav id="tocNav"></nav>
      </aside>

      <div class="at2-content-card at2-card">
        <article class="article-content" id="articleContent">
          <?php
          if (strtotime($article['created_at']) <= strtotime('2025-09-13')) {
              echo parse_article_content($article['content']);
          } else {
              echo $article['content'];
          }
          ?>
        </article>
      </div>
    </div>
  </div>

  <a class="blog-side-banner blog-side-banner-right" href="https://lolboost.gg/lol/accounts" aria-label="Account Marketplace">
    <img src="/public/assets/website/images/accounts_blog.png" alt="Account Marketplace" onerror="this.onerror=null;this.src='/assets/website/images/accounts_blog.png';">
  </a>
</div>

<button id="backToTop" type="button" aria-label="<?= t('Back to top') ?>">
    <i class="fa-duotone fa-arrow-up"></i>
</button>

<script>
(function(){
  const content = document.getElementById('articleContent');
  const tocNav = document.getElementById('tocNav');
  const toc = document.getElementById('articleToc');
  const tocToggle = document.getElementById('tocToggle');
  const progressBar = document.querySelector('#readingProgress .bar');
  const backToTop = document.getElementById('backToTop');
  const shareBtn = document.getElementById('shareBtn');
  const readingTimeEl = document.getElementById('readingTime');

  if(!content) return;

  // --- Reading time ---
  try {
    const text = content.innerText || '';
    const words = text.trim().split(/\s+/).filter(Boolean).length;
    const minutes = Math.max(1, Math.round(words / 200));
    readingTimeEl.textContent = minutes + ' min read';
  } catch(e) {}

  // --- Build TOC from important headings only (H1 + H2) ---
  let headings = Array.from(content.querySelectorAll('h1, h2'));
  // filter out empty/very short headings
  headings = headings.filter(h => (h.textContent || '').trim().length >= 4);
  // keep TOC compact
  const maxTocItems = 14;
  if(headings.length > maxTocItems) headings = headings.slice(0, maxTocItems);
  const makeId = (s) => (s || '')
    .toLowerCase()
    .trim()
    .replace(/[^a-z0-9\s-]/g,'')
    .replace(/\s+/g,'-')
    .replace(/-+/g,'-');

  if(headings.length && tocNav){
    const frag = document.createDocumentFragment();
    headings.forEach((h, idx) => {
      if(!h.id){
        const base = makeId(h.textContent) || ('section-' + (idx+1));
        let id = base;
        let n = 2;
        while(document.getElementById(id)) { id = base + '-' + n++; }
        h.id = id;
      }
      const a = document.createElement('a');
      a.href = '#' + h.id;
      a.textContent = h.textContent || ('Section ' + (idx+1));
      a.className = 'toc-h2';
      frag.appendChild(a);
    });
    tocNav.appendChild(frag);
  } else {
    // hide TOC if there is nothing to show
    if(toc) toc.style.display = 'none';
  }

  // --- Mobile TOC toggle ---
  if(tocToggle && toc){
    tocToggle.addEventListener('click', () => {
      toc.classList.toggle('open');
    });
  }

  // --- Active TOC + progress ---
  const links = tocNav ? Array.from(tocNav.querySelectorAll('a')) : [];
  const linkById = new Map(links.map(a => [a.getAttribute('href')?.slice(1), a]));

  const setActive = () => {
    // progress
    const doc = document.documentElement;
    const scrollTop = window.scrollY || doc.scrollTop;
    const scrollHeight = doc.scrollHeight - doc.clientHeight;
    const p = scrollHeight > 0 ? (scrollTop / scrollHeight) * 100 : 0;
    if(progressBar) progressBar.style.width = Math.min(100, Math.max(0, p)) + '%';

    // back to top
    if(backToTop){
      if(scrollTop > 500) backToTop.style.display = 'flex';
      else backToTop.style.display = 'none';
    }

    // toc active
    if(!headings.length || !links.length) return;
    let current = headings[0];
    const offset = 140; // account for nav
    for(const h of headings){
      const rect = h.getBoundingClientRect();
      if(rect.top - offset <= 0) current = h;
      else break;
    }
    links.forEach(a => a.classList.remove('active'));
    const a = linkById.get(current.id);
    if(a) a.classList.add('active');
  };

  window.addEventListener('scroll', setActive, { passive: true });
  window.addEventListener('resize', setActive);
  setActive();

  // --- Back to top ---
  if(backToTop){
    backToTop.addEventListener('click', () => window.scrollTo({ top: 0, behavior: 'smooth' }));
  }

  // --- Copy Link ---
  if(shareBtn){
    shareBtn.addEventListener('click', async () => {
      const url = window.location.href;
      const label = shareBtn.querySelector('span');
      const old = label?.textContent || 'Copy Link';

      try{
        if(navigator.clipboard && window.isSecureContext){
          await navigator.clipboard.writeText(url);
        } else {
          const ta = document.createElement('textarea');
          ta.value = url;
          ta.setAttribute('readonly', '');
          ta.style.position = 'fixed';
          ta.style.top = '-9999px';
          document.body.appendChild(ta);
          ta.select();
          document.execCommand('copy');
          document.body.removeChild(ta);
        }
        if(label) label.textContent = 'Copied';
        setTimeout(() => { if(label) label.textContent = old; }, 1200);
      } catch(e){}
    });
  }

})();
</script>
