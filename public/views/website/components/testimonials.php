<?php
if (!function_exists('gm_render_stars')) {
    function gm_render_stars($n = 5) {
        $out = '';
        for ($i = 0; $i < 5; $i++) {
            $out .= '<i class="fa-solid fa-star' . ($i < (int)$n ? '' : ' empty') . '" aria-hidden="true"></i>';
        }
        return $out;
    }
}

$gmReviewsRow1 = [
    ['u' => 'A', 'name' => 'Alex M.',  'title' => t('Valorant Account'),  'rating' => 5, 'txt' => t('Super fast delivery and the account was exactly as described. Highly recommend.'), 'tag' => 'Valorant'],
    ['u' => 'S', 'name' => 'Sophie K.', 'title' => t('LoL Boosting'),     'rating' => 5, 'txt' => t('Climbed from Gold to Diamond in two weeks. The booster was a pro and very communicative.'), 'tag' => 'League of Legends'],
    ['u' => 'J', 'name' => 'Jake R.',   'title' => t('Fortnite Skins'),   'rating' => 5, 'txt' => t('Got rare skins at a great price. Account transfer was smooth and quick.'), 'tag' => 'Fortnite'],
    ['u' => 'M', 'name' => 'Maria T.',  'title' => t('V-Bucks Top-up'),   'rating' => 5, 'txt' => t('Instant delivery, no issues. Will buy again without hesitation.'), 'tag' => 'Fortnite'],
    ['u' => 'L', 'name' => 'Lukas B.',  'title' => t('CS2 Account'),      'rating' => 5, 'txt' => t('Prime account with a lot of hours. Exactly what I needed for competitive play.'), 'tag' => 'CS2'],
];

$gmReviewsRow2 = [
    ['u' => 'P', 'name' => 'Paula N.',  'title' => t('Coaching Session'), 'rating' => 5, 'txt' => t('The coach improved my positioning and game sense drastically. Worth every cent.'), 'tag' => 'Coaching'],
    ['u' => 'T', 'name' => 'Tom C.',    'title' => t('TFT Boosting'),     'rating' => 5, 'txt' => t('Hit Challenger on TFT faster than I expected. Booster explained their strategy too.'), 'tag' => 'TFT'],
    ['u' => 'E', 'name' => 'Eva H.',    'title' => t('Riot Points'),      'rating' => 5, 'txt' => t('RP showed up in seconds. Cheapest and fastest place I\'ve found.'), 'tag' => 'Valorant'],
    ['u' => 'N', 'name' => 'Noah W.',   'title' => t('Apex Account'),     'rating' => 5, 'txt' => t('Great selection of accounts. Found one with all the heirlooms I wanted.'), 'tag' => 'Apex Legends'],
    ['u' => 'C', 'name' => 'Clara Z.',  'title' => t('Gift Card'),        'rating' => 5, 'txt' => t('PSN code worked instantly. Super smooth experience from start to finish.'), 'tag' => 'PSN'],
];
?>

<style id="lb-landing-testimonials-css">
.lbLandingReviews,
.lbLandingReviews *{
  box-sizing:border-box;
}
.lbLandingReviews{
  position:relative;
  width:100%;
  overflow:hidden;
  padding:58px 0 64px;
  color:#fff;
  background:transparent!important;
}
.lbLandingReviews__wrap{
  width:min(1320px, calc(100vw - 32px));
  margin:0 auto;
}
.lbLandingReviews__head{
  display:flex;
  align-items:flex-end;
  justify-content:space-between;
  gap:28px;
  margin:0 auto 28px;
}
.lbLandingReviews__copy{
  min-width:0;
}
.lbLandingReviews__tag{
  display:inline-flex;
  align-items:center;
  gap:10px;
  margin-bottom:12px;
  color:rgba(146,157,255,.86);
  font-size:12px;
  font-weight:950;
  letter-spacing:.18em;
  text-transform:uppercase;
}
.lbLandingReviews__tag::before{
  content:"";
  width:26px;
  height:2px;
  border-radius:999px;
  background:linear-gradient(90deg,#5b62f6,#7c9fff);
}
.lbLandingReviews__title{
  margin:0;
  font-size:clamp(30px, 3.1vw, 46px);
  line-height:1.06;
  letter-spacing:-.04em;
  font-weight:950;
}
.lbLandingReviews__lead{
  margin:12px 0 0;
  max-width:640px;
  color:rgba(235,240,255,.58);
  font-size:15.5px;
  line-height:1.62;
  font-weight:650;
}
.lbLandingReviews__score{
  flex:0 0 auto;
  display:inline-flex;
  align-items:center;
  gap:12px;
  min-height:44px;
  padding:0 16px;
  border-radius:999px;
  background:rgba(255,255,255,.045);
  border:1px solid rgba(255,255,255,.095);
}
.lbLandingReviews__score strong{
  font-size:13px;
  font-weight:950;
  white-space:nowrap;
}
.lbLandingReviews__score span{
  color:rgba(255,255,255,.54);
  font-size:12px;
  font-weight:850;
  white-space:nowrap;
}
.lbLandingReviews__scoreStars{
  display:inline-flex;
  gap:3px;
  color:#fbbf24!important;
  font-size:11px;
}
.lbLandingReviews__marquee{
  position:relative;
  width:100%;
  overflow:hidden;
  padding:4px 0;
  background:transparent!important;
}
.lbLandingReviews__marquee + .lbLandingReviews__marquee{
  margin-top:12px;
}
.lbLandingReviews__fadeL,
.lbLandingReviews__fadeR{
  display:none!important;
}
.lbLandingReviews__row{
  overflow:hidden;
}
.lbLandingReviews__track{
  display:flex;
  gap:14px;
  width:max-content;
  animation:lbReviewsMarquee 55s linear infinite;
  will-change:transform;
}
.lbLandingReviews__track.is-reverse{
  animation-direction:reverse;
}
.lbLandingReviewCard{
  flex:0 0 318px;
  min-height:188px;
  padding:18px 20px;
  border-radius:20px;
  background:rgba(255,255,255,.035);
  border:1px solid rgba(255,255,255,.085);
  box-shadow:none;
  transition:transform .18s ease, border-color .18s ease, background .18s ease;
}
.lbLandingReviewCard:hover{
  transform:translateY(-2px);
  border-color:rgba(129,140,248,.26);
  background:rgba(255,255,255,.052);
}
.lbLandingReviewCard__top{
  display:flex;
  align-items:center;
  gap:10px;
  margin-bottom:11px;
}
.lbLandingReviewCard__avatar{
  width:38px;
  height:38px;
  border-radius:14px;
  flex-shrink:0;
  display:flex;
  align-items:center;
  justify-content:center;
  background:rgba(99,102,241,.26);
  border:1px solid rgba(129,140,248,.24);
  font-weight:950;
  font-size:14px;
  color:#fff;
}
.lbLandingReviewCard__avatar img{width:100%;height:100%;display:block;object-fit:cover;border-radius:inherit;}
.lbLandingReviewCard__name{
  display:block;
  font-size:13px;
  font-weight:950;
  color:#fff;
}
.lbLandingReviewCard__title{
  display:block;
  margin-top:3px;
  font-size:11px;
  color:rgba(255,255,255,.48);
  font-weight:800;
}
.lbLandingReviewCard__stars{
  display:flex;
  gap:3px;
  color:#fbbf24;
  font-size:11px;
  margin-bottom:9px;
}
.lbLandingReviewCard__stars .empty{
  color:rgba(255,255,255,.16);
}
.lbLandingReviewCard__text{
  font-size:13.5px;
  line-height:1.58;
  color:rgba(255,255,255,.68);
  margin:0 0 13px;
  font-weight:650;
}
.lbLandingReviewCard__tag{
  display:inline-flex;
  align-items:center;
  min-height:28px;
  padding:0 11px;
  border-radius:999px;
  font-size:11px;
  font-weight:900;
  color:rgba(255,255,255,.76);
  background:rgba(255,255,255,.052);
  border:1px solid rgba(255,255,255,.10);
}
@keyframes lbReviewsMarquee{
  from{transform:translateX(0)}
  to{transform:translateX(-50%)}
}
.lbLandingReviews__marquee:hover .lbLandingReviews__track{
  animation-play-state:paused;
}
@media (prefers-reduced-motion:reduce){
  .lbLandingReviews__track{animation:none;}
}
@media (max-width:900px){
  .lbLandingReviews__head{
    display:block;
  }
  .lbLandingReviews__score{
    margin-top:18px;
  }
}
@media (max-width:760px){
  .lbLandingReviews{
    padding:44px 0 52px;
  }
  .lbLandingReviews__wrap{
    width:calc(100vw - 28px);
  }
  .lbLandingReviews__head{
    margin-bottom:22px;
  }
  .lbLandingReviews__tag{
    font-size:11px;
  }
  .lbLandingReviews__title{
    font-size:31px;
  }
  .lbLandingReviews__lead{
    font-size:14px;
    line-height:1.56;
  }
  .lbLandingReviews__score{
    min-height:0;
    border-radius:16px;
    align-items:flex-start;
    flex-direction:column;
    gap:7px;
    padding:12px 14px;
  }
  .lbLandingReviewCard{
    flex-basis:286px;
    min-height:196px;
    padding:17px;
  }
}
</style>

<section class="lbLandingReviews" id="reviews">
  <div class="lbLandingReviews__wrap">
    <div class="lbLandingReviews__head">
      <div class="lbLandingReviews__copy">
        <div class="lbLandingReviews__tag"><?= t('Reviews') ?></div>
        <h2 class="lbLandingReviews__title"><?= t('Community Trust') ?></h2>
        <p class="lbLandingReviews__lead"><?= t('Real feedback from customers who ordered boosts, accounts, coaching and marketplace items.') ?></p>
      </div>
      <div class="lbLandingReviews__score" aria-label="Overall rating">
        <strong><?= t('Rated Excellent') ?></strong>
        <span class="lbLandingReviews__scoreStars" aria-label="5 stars"><?= gm_render_stars(5) ?></span>
        <span><?= t('1000+ customer ratings') ?></span>
      </div>
    </div>
  </div>

  <div class="lbLandingReviews__marquee" aria-label="Testimonials">
    <span class="lbLandingReviews__fadeL" aria-hidden="true"></span>
    <span class="lbLandingReviews__fadeR" aria-hidden="true"></span>
    <div class="lbLandingReviews__row">
      <div class="lbLandingReviews__track">
        <?php foreach ([$gmReviewsRow1, $gmReviewsRow1] as $row): foreach ($row as $review): ?>
          <article class="lbLandingReviewCard">
            <div class="lbLandingReviewCard__top">
              <div class="lbLandingReviewCard__avatar"><img src="/public/assets/website/images/reviews/default.webp" alt="" loading="lazy"></div>
              <div>
                <b class="lbLandingReviewCard__name"><?= htmlspecialchars($review['name'], ENT_QUOTES) ?></b>
                <span class="lbLandingReviewCard__title"><?= htmlspecialchars($review['title'], ENT_QUOTES) ?></span>
              </div>
            </div>
            <div class="lbLandingReviewCard__stars"><?= gm_render_stars($review['rating']) ?></div>
            <p class="lbLandingReviewCard__text"><?= htmlspecialchars($review['txt'], ENT_QUOTES) ?></p>
            <span class="lbLandingReviewCard__tag"><?= htmlspecialchars($review['tag'], ENT_QUOTES) ?></span>
          </article>
        <?php endforeach; endforeach; ?>
      </div>
    </div>
  </div>

  <div class="lbLandingReviews__marquee" aria-label="Testimonials">
    <span class="lbLandingReviews__fadeL" aria-hidden="true"></span>
    <span class="lbLandingReviews__fadeR" aria-hidden="true"></span>
    <div class="lbLandingReviews__row">
      <div class="lbLandingReviews__track is-reverse">
        <?php foreach ([$gmReviewsRow2, $gmReviewsRow2] as $row): foreach ($row as $review): ?>
          <article class="lbLandingReviewCard">
            <div class="lbLandingReviewCard__top">
              <div class="lbLandingReviewCard__avatar"><img src="/public/assets/website/images/reviews/default.webp" alt="" loading="lazy"></div>
              <div>
                <b class="lbLandingReviewCard__name"><?= htmlspecialchars($review['name'], ENT_QUOTES) ?></b>
                <span class="lbLandingReviewCard__title"><?= htmlspecialchars($review['title'], ENT_QUOTES) ?></span>
              </div>
            </div>
            <div class="lbLandingReviewCard__stars"><?= gm_render_stars($review['rating']) ?></div>
            <p class="lbLandingReviewCard__text"><?= htmlspecialchars($review['txt'], ENT_QUOTES) ?></p>
            <span class="lbLandingReviewCard__tag"><?= htmlspecialchars($review['tag'], ENT_QUOTES) ?></span>
          </article>
        <?php endforeach; endforeach; ?>
      </div>
    </div>
  </div>
</section>
