<?php
if (!function_exists('slp_format_rating_value')) {
    function slp_format_rating_value(float $rating): string {
        if ($rating <= 0) {
            return '0/5';
        }
        $rounded = round($rating, 1);
        $value = abs($rounded - round($rounded)) < 0.01
            ? (string)(int)round($rounded)
            : number_format($rounded, 1);
        return $value . '/5';
    }
}

if (!function_exists('lb_seller_rank_meta')) {
    function lb_seller_rank_meta($rankValue = '', $storedRankIcon = ''): array
    {
        $rank = trim((string)$rankValue);
        $stored = trim((string)$storedRankIcon);
        $rankKey = strtolower($rank);

        $meta = [
            'icon' => 'fa-badge-check',
            'color' => '#94a3b8',
            'bg' => 'rgba(148,163,184,.12)',
            'border' => 'rgba(148,163,184,.28)',
            'label' => $rank,
        ];

        if ($rankKey === 'expert seller') {
            $meta['color'] = '#22c55e';
            $meta['bg'] = 'rgba(34,197,94,.12)';
            $meta['border'] = 'rgba(34,197,94,.28)';
        } elseif ($rankKey === 'pro seller') {
            $meta['color'] = '#8b5cf6';
            $meta['bg'] = 'rgba(139,92,246,.12)';
            $meta['border'] = 'rgba(139,92,246,.28)';
        } elseif ($rankKey === 'mythic seller') {
            $meta['color'] = '#fbbf24';
            $meta['bg'] = 'rgba(251,191,36,.12)';
            $meta['border'] = 'rgba(251,191,36,.28)';
        } elseif ($rankKey === 'beginner') {
            $meta['color'] = '#94a3b8';
            $meta['bg'] = 'rgba(148,163,184,.12)';
            $meta['border'] = 'rgba(148,163,184,.28)';
        }

        if ($stored !== '') {
            $storedLower = strtolower($stored);
            if (strpos($storedLower, 'text-emerald') !== false) {
                $meta['color'] = '#22c55e';
                $meta['bg'] = 'rgba(34,197,94,.12)';
                $meta['border'] = 'rgba(34,197,94,.28)';
            } elseif (strpos($storedLower, 'text-violet') !== false) {
                $meta['color'] = '#8b5cf6';
                $meta['bg'] = 'rgba(139,92,246,.12)';
                $meta['border'] = 'rgba(139,92,246,.28)';
            } elseif (strpos($storedLower, 'text-amber') !== false) {
                $meta['color'] = '#fbbf24';
                $meta['bg'] = 'rgba(251,191,36,.12)';
                $meta['border'] = 'rgba(251,191,36,.28)';
            } elseif (strpos($storedLower, 'text-slate') !== false || strpos($storedLower, 'text-gray') !== false) {
                $meta['color'] = '#94a3b8';
                $meta['bg'] = 'rgba(148,163,184,.12)';
                $meta['border'] = 'rgba(148,163,184,.28)';
            }
        }

        return $meta;
    }
}
?>
<?= $this->layout('website/layouts/master', ['meta' => $meta, 'bodyClass' => 'sellers-list-page']) ?>

<style>
.sellers-list-page { min-height: 60vh; }

/* ── Header — compact, shop-lol-accounts style (icon + title + description) ──
   main.css has a global "body:not(.landing) header { padding-top: ... !important }"
   rule that adds vertical offset to the OUTER <header>. Matching that exact
   specificity here (body.sellers-list-page header) is required to win, otherwise
   this padding:0 loses and stacks with the .content padding below, doubling the gap. */
body.sellers-list-page header {
  min-height: 0 !important;
  height: auto !important;
  box-sizing: border-box !important;
  background: #0e0c1c !important;
  border-bottom: 1px solid rgba(255,255,255,.06);
  display: block !important;
  position: relative;
  overflow: hidden;
  padding: 0 !important;
  padding-top: 0 !important;
}
.sellers-list-page header .content {
  max-width: 1500px !important;
  width: auto !important;
  margin: 0 auto !important;
  padding: calc(var(--lb-content-top, 132px) + 36px) 28px 36px !important;
  display: flex;
  align-items: center;
  gap: 22px;
  position: relative;
  z-index: 2;
}
.sellers-list-page header .hdr-icon {
  width: 74px; height: 74px; min-width: 74px;
  border-radius: 20px;
  background: rgba(255,255,255,.045);
  border: 1px solid rgba(255,255,255,.1);
  display: flex; align-items: center; justify-content: center;
  box-shadow: 0 18px 50px rgba(0,0,0,.28);
  overflow: hidden;
}
.sellers-list-page header .hdr-icon i { font-size: 30px; color: #7c6cff; }
.sellers-list-page header h1 {
  margin: 0 !important;
  font-size: 29px !important;
  line-height: 1.12 !important;
  font-weight: 950;
  letter-spacing: -.03em;
  color: #fff;
  text-transform: none;
  font-family: 'Roboto', sans-serif;
  background: none;
  -webkit-text-fill-color: initial;
}
.sellers-list-page header p {
  margin: 8px 0 0;
  max-width: 640px;
  font-size: 15px;
  line-height: 1.5;
  color: #a9adc4;
}


/* ── Body — flat, no outer card; grid starts right after the header ── */
.slp-body {
  max-width: 1500px;
  margin: 24px auto 88px;
  padding: 0 3.75vw;
  position: relative;
}
.slp-section-head {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 1vw;
  margin-bottom: 1.5vw;
  padding-bottom: 1vw;
  border-bottom: .104vw solid #232040;
}
.slp-section-title {
  font-size: .88vw;
  font-weight: 900;
  text-transform: uppercase;
  letter-spacing: .14em;
  color: #818cf8;
  display: flex;
  align-items: center;
  gap: .5vw;
}
.slp-section-title::before {
  content: '';
  width: .2vw;
  height: .85vw;
  border-radius: 999px;
  flex-shrink: 0;
  background: linear-gradient(180deg, #6366f1, #818cf8);
}
.slp-section-count {
  font-size: .84vw;
  color: rgba(255,255,255,.45);
  font-weight: 700;
}

/* ── Empty ── */
.slp-empty { text-align: center; padding: 5vw 0; color: rgba(255,255,255,.3); font-size: 1.1vw; }
.slp-empty i { display: block; font-size: 3vw; margin-bottom: 1vw; color: rgba(99,102,241,.4); }

/* ── Grid ── */
.slp-grid { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 16px; align-items: stretch; grid-auto-rows: 1fr; }

/* ── Card ── */
.slp-card {
  display: flex;
  flex-direction: column;
  align-items: center;
  text-align: center;
  text-decoration: none;
  color: #fff;
  background: #110f1f;
  border: 1px solid #232040;
  border-radius: 1vw;
  padding: 0 1.5vw 1.5vw;
  position: relative;
  overflow: hidden;
  transition: border-color .2s, transform .2s, box-shadow .2s;
  height: 100%;
  min-height: 27vw;
  box-sizing: border-box;
}

.slp-card::before {
  content: '';
  position: absolute;
  top: 0; left: 0; right: 0;
  height: .18vw;
  background: linear-gradient(90deg, #6366f1, #818cf8, #6366f1);
  opacity: 0;
  transition: opacity .2s;
}
.slp-card:hover {
  border-color: #6366f1;
  transform: translateY(-.25vw);
  box-shadow: 0 .8vw 2.5vw rgba(0,0,0,.4);
  color: #fff;
  text-decoration: none;
}
.slp-card:hover::before { opacity: 1; }


/* Banner */
.slp-card__banner {
  width: calc(100% + 3vw);
  height: 5.9vw;
  margin: 0 -1.5vw 0;
  position: relative;
  overflow: hidden;
  flex-shrink: 0;
  background: #0e0d1a;
  border-bottom: 1px solid #232040;
}
.slp-card__banner img {
  width: 100%;
  height: 100%;
  display: block;
  object-fit: cover;
}
.slp-card__banner::after {
  content: '';
  position: absolute;
  inset: 0;
  background: linear-gradient(180deg, rgba(8,8,22,.04) 0%, rgba(8,8,22,.70) 100%);
  pointer-events: none;
}
.slp-card__banner--empty::before {
  content: '';
  position: absolute;
  inset: 0;
  background-image:
    linear-gradient(rgba(255,255,255,.05) 1px, transparent 1px),
    linear-gradient(90deg, rgba(255,255,255,.05) 1px, transparent 1px);
  background-size: 28px 28px;
  opacity: .6;
}

/* Avatar */
.slp-card__avatar,
.slp-card__avatar--ph {
  width: 5vw;
  height: 5vw;
  border-radius: 50%;
  margin-top: -2.55vw;
  margin-bottom: 1.1vw;
  flex-shrink: 0;
  position: relative;
  z-index: 2;
}
.slp-card__avatar { object-fit: cover; border: .15vw solid #232040; box-shadow: 0 .3vw 1vw rgba(0,0,0,.4); }
.slp-card__avatar--ph {
  background: #18162b;
  border: .15vw solid #232040;
  box-shadow: 0 .3vw 1vw rgba(0,0,0,.4);
  display: flex; align-items: center; justify-content: center;
  font-size: 1.4vw; font-weight: 900; color: rgba(255,255,255,.65);
}

/* Name */
.slp-card__name { font-size: 1.05vw; font-weight: 900; color: #fff; margin-bottom: .45vw; line-height: 1.2; min-height: 1.35vw; display: flex; align-items: center; justify-content: center; }

/* Badges */
.slp-card__badges { display: flex; flex-wrap: wrap; justify-content: center; align-items: center; gap: .3vw; margin-bottom: .75vw; min-height: 1.55vw; }
.slp-card__badge {
  display: inline-flex; align-items: center; gap: .22vw;
  font-size: .62vw; font-weight: 900; text-transform: uppercase; letter-spacing: .05em;
  padding: .22vw .6vw; border-radius: 999px;
  background: rgba(99,102,241,.1); border: 1px solid rgba(99,102,241,.22); color: #a5b4fc;
}
.slp-card__badge.verified { background: rgba(34,197,94,.08); border-color: rgba(34,197,94,.25); color: #22c55e; }

/* Languages */
.slp-card__langs { display: flex; flex-wrap: wrap; justify-content: center; align-items: center; gap: .3vw; margin-bottom: .6vw; min-height: 1.4vw; }
.slp-card__langs--empty { visibility: hidden; }
.slp-card__lang-flag {
  width: 1.4vw; height: 1.4vw; border-radius: 50%; object-fit: cover;
  border: 1px solid rgba(255,255,255,.12);
  background: rgba(255,255,255,.04);
}
.slp-card__lang-more {
  display: inline-flex; align-items: center; justify-content: center;
  width: 1.4vw; height: 1.4vw; border-radius: 50%;
  background: rgba(255,255,255,.06); border: 1px solid rgba(255,255,255,.12);
  color: rgba(255,255,255,.6); font-size: .55vw; font-weight: 800;
}

/* Seller info stats */
.slp-card__stats {
  width: 100%;
  display: grid;
  grid-template-columns: repeat(3, minmax(0, 1fr));
  gap: .45vw;
  margin: .85vw 0 .25vw;
}
.slp-card__stat {
  min-width: 0;
  border: 1px solid #232040;
  border-radius: .72vw;
  background: #18162b;
  padding: .62vw .42vw .5vw;
}
.slp-card__stat-value {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: .28vw;
  font-size: .92vw;
  font-weight: 950;
  line-height: 1;
  color: #fff;
  white-space: nowrap;
}
.slp-card__stat-value i { color: #818cf8; font-size: .72vw; }
.slp-card__stat-value.trusted { color: #22c55e; font-size: .78vw; text-transform: uppercase; letter-spacing: .04em; }
.slp-card__stat-value.trusted i { color: #22c55e; }
.slp-card__stat-label {
  margin-top: .28vw;
  font-size: .56vw;
  font-weight: 900;
  text-transform: uppercase;
  letter-spacing: .08em;
  color: rgba(255,255,255,.48);
}
.slp-card__trust-pills {
  width: 100%;
  min-height: 3.3vw;
  display: flex;
  flex-wrap: wrap;
  align-items: center;
  justify-content: center;
  gap: .42vw;
  margin-top: .78vw;
}
.slp-card__trust-pill {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: .28vw;
  max-width: 100%;
  padding: .34vw .58vw;
  border-radius: 999px;
  border: 1px solid #232040;
  background: #18162b;
  color: rgba(238,242,255,.82);
  font-size: .61vw;
  font-weight: 900;
  line-height: 1;
  white-space: nowrap;
}
.slp-card__trust-pill i { color: #a5b4fc; font-size: .62vw; }
.slp-card__trust-pill.good {
  border-color: rgba(34,197,94,.25);
  background: rgba(34,197,94,.08);
  color: #86efac;
}
.slp-card__trust-pill.good i { color: #22c55e; }

/* Spacer + CTA */
.slp-card__spacer { flex: 1; min-height: .5vw; }
.slp-card__cta {
  margin-top: 1vw; width: 100%;
  display: inline-flex; align-items: center; justify-content: center; gap: .35vw;
  font-size: .72vw; font-weight: 900; text-transform: uppercase; letter-spacing: .06em;
  color: #eef2ff;
  background: linear-gradient(135deg, rgba(99,102,241,.9), rgba(139,92,246,.9));
  padding: .52vw 1vw; border-radius: 999px;
  box-shadow: 0 .25vw .9vw rgba(99,102,241,.3);
  transition: box-shadow .18s, transform .18s;
}
.slp-card:hover .slp-card__cta { box-shadow: 0 .4vw 1.4vw rgba(99,102,241,.5); transform: translateY(-.05vw); }

/* Compact desktop cards: four profiles per row on wide screens. */
@media(min-width:901px) {
  .slp-card {
    min-height: 420px;
    padding: 0 18px 18px;
    border-radius: 16px;
  }
  .slp-card__banner {
    width: calc(100% + 36px);
    height: 94px;
    margin: 0 -18px;
  }
  .slp-card__avatar,
  .slp-card__avatar--ph {
    width: 74px;
    height: 74px;
    margin-top: -38px;
    margin-bottom: 12px;
  }
  .slp-card__avatar { border-width: 3px; }
  .slp-card__avatar--ph { border-width: 3px; font-size: 20px; }
  .slp-card__name {
    min-height: 22px;
    margin-bottom: 7px;
    font-size: 18px;
  }
  .slp-card__badges {
    gap: 5px;
    min-height: 25px;
    margin-bottom: 9px;
  }
  .slp-card__badge { gap: 4px; padding: 4px 9px; font-size: 10px; }
  .slp-card__langs { gap: 5px; min-height: 24px; margin-bottom: 7px; }
  .slp-card__lang-flag,
  .slp-card__lang-more { width: 23px; height: 23px; }
  .slp-card__lang-more { font-size: 9px; }
  .slp-card__stats { gap: 7px; margin: 11px 0 3px; }
  .slp-card__stat { padding: 10px 6px 8px; border-radius: 11px; }
  .slp-card__stat-value { gap: 4px; font-size: 15px; }
  .slp-card__stat-value i { font-size: 12px; }
  .slp-card__stat-value.trusted { font-size: 12px; }
  .slp-card__stat-label { margin-top: 5px; font-size: 9px; }
  .slp-card__trust-pills {
    min-height: 48px;
    gap: 6px;
    margin-top: 10px;
  }
  .slp-card__trust-pill { gap: 4px; padding: 5px 8px; font-size: 10px; }
  .slp-card__trust-pill i { font-size: 10px; }
  .slp-card__cta { margin-top: 11px; padding: 9px 14px; font-size: 11px; }
}
@media(min-width:901px) and (max-width:1250px) {
  .slp-grid { grid-template-columns: repeat(3, minmax(0, 1fr)); }
}

/* ── Tooltip ── */
.slp-has-tip {
  position: relative;
  cursor: default;
}
.slp-tip {
  display: none;
  position: absolute;
  bottom: calc(100% + 8px);
  left: 50%;
  transform: translateX(-50%);
  background: #11162a;
  border: 1px solid rgba(99,102,241,.25);
  border-radius: .6vw;
  padding: .55vw .65vw;
  box-shadow: 0 .5vw 1.8vw rgba(0,0,0,.45);
  z-index: 100;
  min-width: 130px;
  max-width: 180px;
  flex-direction: column;
  gap: .35vw;
  white-space: nowrap;
}
.slp-has-tip:hover .slp-tip {
  display: flex;
}
.slp-tip::after {
  content: '';
  position: absolute;
  top: 100%;
  left: 50%;
  transform: translateX(-50%);
  border-left: 5px solid transparent;
  border-right: 5px solid transparent;
  border-top: 5px solid #11162a;
}
.slp-tip-row {
  display: flex;
  align-items: center;
  gap: .4vw;
}
.slp-tip-flag {
  width: 1.1vw;
  height: 1.1vw;
  border-radius: 50%;
  object-fit: cover;
  flex-shrink: 0;
  border: 1px solid rgba(255,255,255,.1);
}
.slp-tip-label {
  font-size: .68vw;
  color: rgba(255,255,255,.75);
  font-weight: 600;
}

/* ── Mobile ── */
@media(max-width:900px) {
  .sellers-list-page header .content {
    padding: calc(var(--lb-content-top, 126px) + 20px) 16px 24px !important;
    display: grid;
    grid-template-columns: 40px minmax(0,1fr);
    align-items: flex-start;
    gap: 10px;
  }
  .sellers-list-page header .hdr-icon { width:40px; height:40px; min-width:40px; border-radius:12px; margin-top:2px; }
  .sellers-list-page header .hdr-icon i { font-size:19px; }
  .sellers-list-page header h1 {
    font-size: 18px !important;
    line-height: 1.22 !important;
  }
  .sellers-list-page header p {
    font-size: 12.5px;
    margin-top: 5px;
  }
  .sellers-list-page .slp-body { margin: 12px 10px 88px; padding: 0; }
  .slp-section-head { flex-direction: column; align-items: flex-start; gap: 6px; margin-bottom: 14px; padding-bottom: 12px; }
  .slp-section-title { font-size: .8rem; }
  .slp-section-title::before { width: 3px; height: 12px; }
  .slp-section-count { font-size: .8rem; }
  .slp-empty { font-size: .95rem; padding: 40px 0; }
  .slp-empty i { font-size: 2.2rem; }
  .slp-grid { grid-template-columns: repeat(2, 1fr); gap: 10px; align-items: stretch; grid-auto-rows: 1fr; }
  .slp-card { border-radius: 14px; padding: 0 12px 14px; min-height: 350px; }
  .slp-card::before { height: 2px; }
  .slp-card:hover { transform: translateY(-2px); }
  .slp-card__banner { width: calc(100% + 24px); height: 82px; margin: 0 -12px 0; }
  .slp-card__avatar,
  .slp-card__avatar--ph { width: 56px; height: 56px; margin-top: -28px; margin-bottom: 12px; border-width: 2px; }
  .slp-card__avatar--ph { font-size: 1.2rem; }
  .slp-card__name { font-size: .9rem; margin-bottom: 6px; min-height: 1.2rem; }
  .slp-card__badges { gap: 4px; margin-bottom: 8px; min-height: 24px; }
  .slp-card__badge { font-size: .62rem; padding: 3px 8px; gap: 3px; }
  .slp-card__langs { gap: 4px; margin-bottom: 8px; min-height: 20px; }
  .slp-card__lang-flag { width: 20px; height: 20px; }
  .slp-card__lang-more { width: 20px; height: 20px; font-size: .6rem; }
  .slp-card__stats { gap: 6px; margin: 10px 0 4px; }
  .slp-card__stat { border-radius: 10px; padding: 8px 4px 7px; }
  .slp-card__stat-value { font-size: .86rem; gap: 4px; }
  .slp-card__stat-value i { font-size: .7rem; }
  .slp-card__stat-value.trusted { font-size: .64rem; }
  .slp-card__stat-label { margin-top: 4px; font-size: .52rem; }
  .slp-card__meta-line { margin-top: 8px; gap: 6px; font-size: .68rem; min-height: 17px; }
  .slp-card__spacer { min-height: 6px; }
  .slp-card__cta { font-size: .7rem; padding: 8px 12px; gap: 5px; margin-top: 12px; }
  .slp-tip { border-radius: 8px; padding: 8px 10px; gap: 5px; min-width: 120px; }
  .slp-tip-row { gap: 6px; }
  .slp-tip-flag { width: 16px; height: 16px; }
  .slp-tip-label { font-size: .7rem; }
}


@media(max-width:640px) {
  .sellers-list-page .slp-body {
    margin: 12px 12px 96px;
    padding: 0;
  }

  .slp-section-head {
    margin-bottom: 18px;
    padding-bottom: 14px;
  }
  .slp-section-title {
    font-size: .96rem;
    letter-spacing: .16em;
  }
  .slp-section-count {
    font-size: .95rem;
  }

  .slp-grid {
    grid-template-columns: 1fr;
    gap: 16px;
  }

  .slp-card {
    min-height: 0;
    padding: 0 18px 18px;
    border-radius: 18px;
  }

  .slp-card__banner {
    width: calc(100% + 36px);
    height: 118px;
    margin: 0 -18px 0;
  }

  .slp-card__avatar,
  .slp-card__avatar--ph {
    width: 86px;
    height: 86px;
    margin-top: -43px;
    margin-bottom: 16px;
  }
  .slp-card__avatar--ph {
    font-size: 1.55rem;
  }

  .slp-card__name {
    font-size: 1.15rem;
    margin-bottom: 10px;
    min-height: 1.45rem;
  }

  .slp-card__badges {
    gap: 7px;
    margin-bottom: 14px;
    min-height: 31px;
  }
  .slp-card__badge {
    font-size: .72rem;
    padding: 5px 10px;
    gap: 5px;
  }

  .slp-card__langs {
    gap: 7px;
    margin-bottom: 16px;
    min-height: 28px;
  }
  .slp-card__lang-flag,
  .slp-card__lang-more {
    width: 28px;
    height: 28px;
  }
  .slp-card__lang-more {
    font-size: .72rem;
  }

  .slp-card__stats {
    gap: 10px;
    margin: 16px 0 8px;
  }
  .slp-card__stat {
    border-radius: 13px;
    padding: 11px 6px 9px;
  }
  .slp-card__stat-value {
    font-size: 1rem;
    gap: 5px;
  }
  .slp-card__stat-value i {
    font-size: .82rem;
  }
  .slp-card__stat-value.trusted {
    font-size: .74rem;
  }
  .slp-card__stat-label {
    font-size: .61rem;
    margin-top: 6px;
  }

  .slp-card__trust-pills {
    min-height: 0;
    gap: 7px;
    margin-top: 14px;
  }
  .slp-card__trust-pill {
    font-size: .72rem;
    padding: 6px 10px;
    gap: 5px;
  }
  .slp-card__trust-pill i {
    font-size: .72rem;
  }

  .slp-card__spacer {
    min-height: 10px;
  }
  .slp-card__cta {
    font-size: .82rem;
    padding: 12px 16px;
    margin-top: 16px;
    border-radius: 999px;
  }
}

</style>

<?php
/* ── Helpers ── */
if (!function_exists('slp_flag_url')) {
    function slp_flag_url(string $code): string {
        $map = ['el'=>'gr','cs'=>'cz','zh'=>'ch'];
        $fc  = $map[$code] ?? $code;
        $base_url  = ASSET_URL . '/core/main/img/flags/';
        $base_disk = rtrim($_SERVER['DOCUMENT_ROOT'],'/') . '/public/assets/core/main/img/flags/';
        if (is_file($base_disk . $fc . '.webp')) return $base_url . $fc . '.webp';
        if (is_file($base_disk . $fc . '.png'))  return $base_url . $fc . '.png';
        return '';
    }
}
if (!function_exists('slp_language_map')) {
    function slp_language_map(): array {
        return [
            'en'=>'English','de'=>'Deutsch','fr'=>'Français','es'=>'Español',
            'pt'=>'Português','it'=>'Italiano','nl'=>'Nederlands','pl'=>'Polski',
            'ru'=>'Русский','jp'=>'日本語','zh'=>'中文','sv'=>'Svenska',
            'no'=>'Norsk','da'=>'Dansk','fi'=>'Suomi','el'=>'Ελληνικά',
            'hu'=>'Magyar','cs'=>'Čeština','bg'=>'Български','ro'=>'Română',
            'tr'=>'Türkçe','hr'=>'Hrvatski','ar'=>'العربية','fili'=>'Filipino',
        ];
    }
}
if (!function_exists('slp_parse_langs')) {
    function slp_parse_langs($raw): array {
        if (is_string($raw) && $raw !== '') {
            $d = json_decode($raw, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($d)) $raw = $d;
            elseif (strpos($raw,'|') !== false) $raw = explode('|', $raw);
            elseif (strpos($raw,',') !== false) $raw = explode(',', $raw);
            else $raw = [$raw];
        }
        if (!is_array($raw)) return [];
        $labels = slp_language_map();
        $out = []; $seen = [];
        foreach ($raw as $entry) {
            $code = trim((string)$entry);
            if ($code === '' || isset($seen[$code])) continue;
            if (isset($labels[$code])) {
                $url = slp_flag_url($code);
                if ($url !== '') { $out[] = ['code'=>$code,'flag'=>$url,'label'=>$labels[$code]]; $seen[$code]=true; }
            }
        }
        return $out;
    }
}

if (!function_exists('slp_seller_profile_slug')) {
    function slp_seller_profile_slug(array $seller, array $extra = []): string {
        $slug = trim((string)($seller['slug'] ?? $extra['slug'] ?? ''));
        if ($slug !== '') {
            return $slug;
        }

        $username = trim((string)($seller['username'] ?? ''));
        if ($username === '') {
            return (string)($seller['id'] ?? '');
        }

        $slug = preg_replace('/[^a-z0-9]+/i', '-', $username);
        $slug = trim((string)$slug, '-');

        return $slug !== '' ? $slug : (string)($seller['id'] ?? '');
    }
}


if (!function_exists('slp_safe_object_position')) {
    function slp_safe_object_position($raw): string {
        $pos = trim((string)$raw);
        if ($pos === '') return 'center center';
        return preg_match('/^[a-zA-Z0-9.%\s\-]+$/', $pos) ? $pos : 'center center';
    }
}

/* ── Fetch languages + listing counts per seller ── */
if (!function_exists('slp_total_sales')) {
    function slp_total_sales(int $sellerId): int
    {
        global $db;
        if ($sellerId <= 0 || empty($db)) return 0;
        if (function_exists('get_seller_total_sales')) {
            return get_seller_total_sales($sellerId);
        }

        $liveTotal = 0;
        try {
            $row = $db->row(
                "SELECT
                    COALESCE((SELECT COUNT(*) FROM selling_accounts WHERE seller_id = ? AND sold = 1), 0)
                    + COALESCE((SELECT SUM(sold_count) FROM selling_items WHERE seller_id = ?), 0)
                    + COALESCE((SELECT SUM(sold_count) FROM selling_topups WHERE seller_id = ?), 0)
                    + COALESCE((SELECT SUM(sold_count) FROM digital_goods WHERE seller_id = ?), 0) AS total_sales",
                $sellerId,
                $sellerId,
                $sellerId,
                $sellerId
            );
            $liveTotal = max(0, (int)($row['total_sales'] ?? 0));

            $adminId = $sellerId === 28 ? 51 : ($sellerId === 1 ? 2 : 0);
            if ($adminId > 0) {
                $liveTotal += max(0, (int)($db->single(
                    "SELECT COUNT(*) FROM accounts WHERE admin_id = ? AND status = 1",
                    $adminId
                ) ?: 0));
            }
        } catch (Throwable $e) {}

        $storedTotal = 0;
        try {
            $storedTotal = max(0, (int)($db->single(
                "SELECT total_sales FROM seller_stats WHERE seller_id = ? LIMIT 1",
                $sellerId
            ) ?: 0));
        } catch (Throwable $e) {}

        return max($liveTotal, $storedTotal);
    }
}

$slp_extra = [];
if (!empty($sellers)) {
    global $db;
    foreach ($sellers as $s) {
        $sid = (int)($s['id'] ?? 0);
        if (!$sid) continue;

        // languages
        $row = $db->row("SELECT languages, slug FROM sellers WHERE id = ? LIMIT 1", $sid);
        if ($row) {
            $slp_extra[$sid]['languages'] = $row['languages'];
            $slp_extra[$sid]['slug'] = $row['slug'] ?? '';
        }

        // active listings: accounts + marketplace items
        $active_accounts_row = $db->row(
            "SELECT COUNT(*) as cnt FROM selling_accounts WHERE seller_id = ? AND sold = 0 AND COALESCE(active, 1) = 1",
            $sid
        );
        $active_items_row = $db->row(
            "SELECT COUNT(*) as cnt FROM selling_items WHERE seller_id = ? AND COALESCE(active, 1) = 1",
            $sid
        );
        $active_accounts = (int)($active_accounts_row['cnt'] ?? 0);
        $active_items = (int)($active_items_row['cnt'] ?? 0);
        $slp_extra[$sid]['active_accounts'] = $active_accounts;
        $slp_extra[$sid]['active_items'] = $active_items;
        $slp_extra[$sid]['listing_count'] = $active_accounts + $active_items;

        $slp_extra[$sid]['total_sold'] = slp_total_sales($sid);

        $review_row = $db->row(
            "SELECT ROUND(AVG(rating), 1) AS avg_rating, COUNT(*) AS review_count
             FROM seller_reviews
             WHERE seller_id = ? AND approved = 1",
            $sid
        );
        $realAvgRating = (float)($review_row['avg_rating'] ?? 0);
        $realReviewCount = (int)($review_row['review_count'] ?? 0);
        // Same source of truth as the seller profile: sales without buyer feedback
        // after 24h count as 5-star "No Feedback left." entries.
        $autoReviewCount = function_exists('seller_no_feedback_entries')
            ? count(seller_no_feedback_entries($sid, 24))
            : 0;
        $totalReviewCount = $realReviewCount + $autoReviewCount;

        $slp_extra[$sid]['review_count'] = $totalReviewCount;
        $slp_extra[$sid]['avg_rating'] = $totalReviewCount > 0
            ? round((($realAvgRating * $realReviewCount) + ($autoReviewCount * 5)) / $totalReviewCount, 1)
            : 0.0;
    }
}

/* ── Filter: only sellers with at least 1 active listing ── */
$active_sellers = array_filter($sellers, function($s) use ($slp_extra) {
    $id = $s['id'] ?? 0;
    return ($slp_extra[$id]['listing_count'] ?? 0) >= 1;
});
?>

<div class="sellers-list-page">

  <header>
    <div class="content">
      <div class="hdr-icon" aria-hidden="true"><i class="fa-solid fa-store"></i></div>
      <div>
        <h1>Seller Profiles</h1>
        <p>Browse verified sellers on LolBoost.gg and explore protected marketplace listings.</p>
      </div>
    </div>
  </header>

  <div class="slp-body">
    <div class="slp-section-head">
      <div class="slp-section-title">Available Sellers</div>
      <div class="slp-section-count"><?= count($active_sellers) ?> verified seller<?= count($active_sellers) === 1 ? '' : 's' ?></div>
    </div>
    <?php if (empty($active_sellers)): ?>
      <div class="slp-empty">
        <i class="fa-solid fa-store-slash"></i>
        No active sellers at the moment.
      </div>
    <?php else: ?>
      <div class="slp-grid">
        <?php foreach ($active_sellers as $s): ?>
          <?php
            $sid   = $s['id'] ?? 0;
            $langs = slp_parse_langs($slp_extra[$sid]['languages'] ?? []);
            $langs_visible = array_slice($langs, 0, 5);
            $langs_extra   = count($langs) - count($langs_visible);
            $sellerRankMeta = lb_seller_rank_meta($s['rank'] ?? '', $s['rank_icon'] ?? '');
            $sellerProfileSlug = slp_seller_profile_slug($s, $slp_extra[$sid] ?? []);
            $sellerTotalSold = (int)($slp_extra[$sid]['total_sold'] ?? ($s['total_sold'] ?? 0));
            $sellerActiveListings = (int)($slp_extra[$sid]['listing_count'] ?? ($s['active_listings'] ?? 0));
            $sellerReviewCount = (int)($slp_extra[$sid]['review_count'] ?? ($s['review_count'] ?? 0));
            $sellerAvgRating = (float)($slp_extra[$sid]['avg_rating'] ?? ($s['avg_rating'] ?? 0));
            $sellerBanner = trim((string)($s['banner'] ?? ''));
            $sellerBannerPosition = slp_safe_object_position($s['banner_position'] ?? 'center center');
            $sellerTrustPills = [
              ['icon' => 'fa-user-check', 'label' => 'Verified profile', 'class' => 'good'],
              ['icon' => 'fa-credit-card', 'label' => 'Safe payment', 'class' => ''],
              ['icon' => 'fa-headset', 'label' => 'Support ready', 'class' => ''],
            ];
            if ($sellerReviewCount > 0) {
              $sellerTrustPills[1] = ['icon' => 'fa-star', 'label' => slp_format_rating_value($sellerAvgRating) . ' (' . number_format($sellerReviewCount) . ' ratings)', 'class' => 'good'];
            }
            if (!empty($sellerRankMeta['label']) && strtolower((string)$sellerRankMeta['label']) !== 'beginner') {
              $sellerTrustPills[2] = ['icon' => 'fa-award', 'label' => ucfirst((string)$sellerRankMeta['label']) . ' seller', 'class' => ''];
            }
          ?>
          <a class="slp-card" href="/sellers/<?= esc($sellerProfileSlug) ?>">

            <div class="slp-card__banner <?= $sellerBanner !== '' ? '' : 'slp-card__banner--empty' ?>">
              <?php if ($sellerBanner !== ''): ?>
                <img src="<?= esc($sellerBanner) ?>" alt="<?= esc($s['username']) ?> banner" style="object-position: <?= esc($sellerBannerPosition) ?>;">
              <?php endif; ?>
            </div>

            <?php if (!empty($s['icon'])): ?>
              <img class="slp-card__avatar" src="<?= esc($s['icon']) ?>" alt="<?= esc($s['username']) ?>">
            <?php else: ?>
              <div class="slp-card__avatar--ph">
                <?= strtoupper(mb_substr($s['username'], 0, 2)) ?>
              </div>
            <?php endif; ?>

            <div class="slp-card__name"><?= esc($s['username']) ?></div>

            <div class="slp-card__badges">
              <?php if (!empty($s['is_active'])): ?>
                <span class="slp-card__badge verified">
                  <i class="fa-solid fa-shield-check"></i> Verified
                </span>
              <?php endif; ?>
              <?php if (!empty($s['rank'])): ?>
                <span class="slp-card__badge" style="color: <?= esc($sellerRankMeta['color']) ?>; background: <?= esc($sellerRankMeta['bg']) ?>; border-color: <?= esc($sellerRankMeta['border']) ?>;">
                  <i class="fa-solid <?= esc($sellerRankMeta['icon']) ?>"></i> <?= esc($s['rank']) ?>
                </span>
              <?php endif; ?>
            </div>

            <?php if (!empty($langs_visible)): ?>
              <div class="slp-card__langs">
                <?php foreach ($langs_visible as $l): ?>
                  <img class="slp-card__lang-flag"
                       src="<?= esc($l['flag']) ?>"
                       alt="<?= esc($l['label']) ?>"
                       title="<?= esc($l['label']) ?>">
                <?php endforeach; ?>
                <?php if ($langs_extra > 0): ?>
                  <?php
                    $hidden_langs = array_slice($langs, 5);
                    $tooltip_html = implode('', array_map(fn($l) => '<img src="' . esc($l['flag']) . '" alt="' . esc($l['label']) . '" title="' . esc($l['label']) . '" class="slp-tip-flag"><span class="slp-tip-label">' . esc($l['label']) . '</span>', $hidden_langs));
                  ?>
                  <span class="slp-card__lang-more slp-has-tip">
                    +<?= $langs_extra ?>
                    <span class="slp-tip">
                      <?php foreach ($hidden_langs as $hl): ?>
                        <span class="slp-tip-row">
                          <img class="slp-tip-flag" src="<?= esc($hl['flag']) ?>" alt="<?= esc($hl['label']) ?>">
                          <span class="slp-tip-label"><?= esc($hl['label']) ?></span>
                        </span>
                      <?php endforeach; ?>
                    </span>
                  </span>
                <?php else: ?>
              <div class="slp-card__langs slp-card__langs--empty" aria-hidden="true">
                <span class="slp-card__lang-more">0</span>
              </div>
            <?php endif; ?>
              </div>
            <?php else: ?>
              <div class="slp-card__langs slp-card__langs--empty" aria-hidden="true">
                <span class="slp-card__lang-more">0</span>
              </div>
            <?php endif; ?>

            <div class="slp-card__stats">
              <div class="slp-card__stat">
                <div class="slp-card__stat-value"><i class="fa-solid fa-bag-shopping"></i><?= number_format($sellerTotalSold) ?></div>
                <div class="slp-card__stat-label">Total Sold</div>
              </div>
              <div class="slp-card__stat">
                <div class="slp-card__stat-value"><i class="fa-solid fa-store"></i><?= number_format($sellerActiveListings) ?></div>
                <div class="slp-card__stat-label">Offers</div>
              </div>
              <div class="slp-card__stat">
                <div class="slp-card__stat-value trusted"><i class="fa-solid fa-thumbs-up"></i></div>
                <div class="slp-card__stat-label">Status</div>
              </div>
            </div>

            <div class="slp-card__trust-pills">
              <?php foreach ($sellerTrustPills as $pill): ?>
                <span class="slp-card__trust-pill <?= esc($pill['class']) ?>">
                  <i class="fa-solid <?= esc($pill['icon']) ?>"></i><?= esc($pill['label']) ?>
                </span>
              <?php endforeach; ?>
            </div>

            <div class="slp-card__spacer"></div>

            <div class="slp-card__cta">
              View Profile <i class="fa-solid fa-arrow-right"></i>
            </div>

          </a>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>

</div>
