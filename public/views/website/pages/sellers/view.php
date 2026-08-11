<?php
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
$rankMeta = lb_seller_rank_meta($seller['rank'] ?? '', $seller['rank_icon'] ?? '');

$reviews_page = max(1, (int)($reviews_page ?? ($_GET['rpage'] ?? 1)));
$reviews_per_page = (int)($reviews_per_page ?? 10);
$review_count = (int)($review_count ?? count($reviews ?? []));
$reviews_total_pages = max(1, (int)($reviews_total_pages ?? ceil(max(0, $review_count) / max(1, $reviews_per_page))));
// Entries shown in the list = rated reviews + "No Feedback left." cards.
$reviews_total = (int)($reviews_total ?? $review_count);
?>
<?= $this->layout('website/layouts/master', ['meta' => $meta, 'bodyClass' => 'seller-profile-view']) ?>

<style>
.seller-profile-view header {
  min-height: clamp(300px, 32vh, 390px) !important;
  height: auto !important;
  box-sizing: border-box !important;
  padding: calc(var(--lb-content-top, 132px) + clamp(24px, 2.6vw, 44px)) 4.167vw clamp(44px, 5vh, 72px) !important;
  background:
    radial-gradient(ellipse 55% 75% at 74% 46%, rgba(99,102,241,.20) 0%, transparent 62%),
    radial-gradient(ellipse 38% 58% at 14% 55%, rgba(59,184,255,.10) 0%, transparent 58%),
    linear-gradient(135deg, #080716 0%, #100e24 52%, #080716 100%) !important;
  background-image:
    radial-gradient(ellipse 55% 75% at 74% 46%, rgba(99,102,241,.20) 0%, transparent 62%),
    radial-gradient(ellipse 38% 58% at 14% 55%, rgba(59,184,255,.10) 0%, transparent 58%),
    linear-gradient(135deg, #080716 0%, #100e24 52%, #080716 100%) !important;
  display: flex !important;
  align-items: flex-start !important;
  justify-content: center !important;
  flex-direction: column !important;
  position: relative;
  overflow: hidden;
}
.seller-profile-view header::before {
  content: '';
  position: absolute;
  inset: 0;
  background-image:
    linear-gradient(rgba(99,102,241,.055) 1px, transparent 1px),
    linear-gradient(90deg, rgba(99,102,241,.055) 1px, transparent 1px);
  background-size: 44px 44px;
  mask-image: linear-gradient(to right, transparent 0%, black 25%, black 75%, transparent 100%);
  pointer-events: none;
}
.seller-profile-view header::after {
  content: '';
  position: absolute;
  inset: 0;
  background: radial-gradient(ellipse at center, transparent 42%, rgba(0,0,0,.62) 100%);
  pointer-events: none;
}
.seller-profile-view header .content {
  max-width: none !important;
  width: min(1120px, 100%) !important;
  margin: clamp(58px, 4.8vw, 96px) 0 0 !important;
  padding: 0 !important;
  position: relative;
  z-index: 2;
}
.seller-profile-view header h1 {
  font-size: clamp(42px, 4vw, 68px) !important;
  line-height: .96 !important;
  margin: 0 0 18px !important;
  text-transform: uppercase;
  font-family: 'superchargestraight', sans-serif;
  background: linear-gradient(135deg,#fff 0%,#818cf8 60%,#a78bfa 100%);
  -webkit-background-clip: text;
  background-clip: text;
  -webkit-text-fill-color: transparent;
}
.seller-profile-view header p {
  max-width: 76ch;
  font-size: clamp(14px, 1.02vw, 17px);
  line-height: 1.65;
  color: rgba(255,255,255,.8);
}


.seller-profile-view .main-content {
  margin: 2vw 4.167vw 4vw;
  padding: 0;
  position: relative;
}

.seller-profile-view .main-content .sp-cover {
  --sp-banner-position: center center;
  height: 17vw;
  overflow: hidden;
  margin: -4.167vw -4.167vw 0 -4.167vw;
  border-radius: 1.5vw 1.5vw 0 0;
  background: linear-gradient(135deg,#0d0525 0%,#1e1050 50%,#0d0525 100%);
  background-size: cover;
  background-position: var(--sp-banner-position);
  position: relative;
}
.seller-profile-view .main-content .sp-cover::after {
  content:'';
  position:absolute;
  inset:0;
  background: linear-gradient(180deg,transparent 40%,rgba(8,8,22,.85) 100%),
              radial-gradient(ellipse at 65% 50%, rgba(99,102,241,.2) 0%, transparent 65%);
}
.seller-profile-view .main-content .sp-cover::before {
  content:'';
  position:absolute;
  inset:0;
  z-index:1;
  background: url('<?= ASSET_URL ?>/website/images/boosters/view-header-bg.webp') center/cover no-repeat;
  opacity: .08;
}
.seller-profile-view .main-content .sp-cover.has-banner {
  background: none !important;
}
.seller-profile-view .main-content .sp-cover.has-banner .sp-banner-img {
  position: absolute;
  inset: 0;
  width: 100%;
  height: 100%;
  object-fit: cover;
  object-position: var(--sp-banner-position);
  z-index: 0;
}

.seller-profile-view .sp-avatar-wrap {
  width: 12.5vw;
  height: 12.5vw;
  border-radius: 50%;
  overflow: hidden;
  border: .3vw solid rgba(99,102,241,.6);
  box-shadow: 0 0 2.5vw rgba(99,102,241,.4), 0 0 5vw rgba(129,140,248,.1);
  position: absolute;
  top: 5.5vw;
  left: 4vw;
  animation: sp-glow 3s ease-in-out infinite;
  z-index: 2;
}
/* Pagination */
.seller-profile-view .sp-pagination {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: .5vw;
  margin-top: 2vw;
  flex-wrap: wrap;
}
.seller-profile-view .sp-pagination button,
.seller-profile-view .sp-pagination a {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  min-width: 2.2vw;
  height: 2.2vw;
  padding: 0 .6vw;
  border-radius: .5vw;
  border: .052vw solid rgba(99,102,241,.2);
  background: rgba(99,102,241,.07);
  color: rgba(255,255,255,.55);
  font-size: .82vw;
  font-weight: 800;
  cursor: pointer;
  transition: all .15s;
  font-family: inherit;
  text-decoration: none;
}
.seller-profile-view .sp-pagination button:hover,
.seller-profile-view .sp-pagination a:hover {
  border-color: rgba(99,102,241,.45);
  background: rgba(99,102,241,.15);
  color: #fff;
}
.seller-profile-view .sp-pagination button.active,
.seller-profile-view .sp-pagination a.active {
  background: linear-gradient(135deg, rgba(99,102,241,.95), rgba(139,92,246,.95));
  border-color: transparent;
  color: #fff;
  box-shadow: 0 .25vw .9vw rgba(99,102,241,.4);
}
.seller-profile-view .sp-pagination button:disabled,
.seller-profile-view .sp-pagination a.disabled {
  opacity: .3;
  cursor: not-allowed;
  pointer-events: none;
}
.seller-profile-view .sp-pagination .sp-page-dots {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  min-width: 1.5vw;
  height: 2.2vw;
  color: rgba(255,255,255,.35);
  font-weight: 800;
}

.seller-profile-view .sp-reviews-list {
  display: flex;
  flex-direction: column;
  gap: 1vw;
}
.seller-profile-view .sp-review-card {
  background: rgba(255,255,255,.03);
  border: .052vw solid rgba(255,255,255,.08);
  border-radius: .95vw;
  padding: 1.15vw 1.2vw;
}
.seller-profile-view .sp-review-top {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 1vw;
  margin-bottom: .6vw;
  flex-wrap: wrap;
}
.seller-profile-view .sp-review-user {
  display: flex;
  align-items: center;
  gap: .75vw;
}
.seller-profile-view .sp-review-avatar,
.seller-profile-view .sp-review-avatar-ph {
  width: 2.8vw;
  height: 2.8vw;
  border-radius: 50%;
  object-fit: cover;
  flex-shrink: 0;
  border: .08vw solid rgba(99,102,241,.25);
}
.seller-profile-view .sp-review-avatar-ph {
  display: flex;
  align-items: center;
  justify-content: center;
  background: rgba(99,102,241,.12);
  color: #c7d2fe;
  font-weight: 900;
  font-size: .9vw;
}
.seller-profile-view .sp-review-name {
  font-size: .95vw;
  font-weight: 800;
  color: #fff;
  line-height: 1.1;
}
.seller-profile-view .sp-review-date {
  font-size: .72vw;
  color: rgba(255,255,255,.38);
  margin-top: .18vw;
}
.seller-profile-view .sp-review-stars {
  display: inline-flex;
  align-items: center;
  gap: .18vw;
  color: #fbbf24;
  font-size: .9vw;
}
.seller-profile-view .sp-review-rating-text {
  margin-left: .35vw;
  font-weight: 800;
  color: #fff;
  font-size: .82vw;
}
.seller-profile-view .sp-review-comment {
  color: rgba(255,255,255,.74);
  font-size: .9vw;
  line-height: 1.7;
  white-space: pre-line;
}
.seller-profile-view .sp-review-summary {
  display: flex;
  gap: 1vw;
  margin-bottom: 1.4vw;
  flex-wrap: wrap;
}
.seller-profile-view .sp-review-summary-card {
  min-width: 12vw;
  background: rgba(99,102,241,.07);
  border: .052vw solid rgba(99,102,241,.15);
  border-radius: .85vw;
  padding: 1vw 1.1vw;
}
.seller-profile-view .sp-review-summary-value {
  font-size: 1.7vw;
  font-weight: 900;
  color: #fff;
  line-height: 1;
}
.seller-profile-view .sp-review-summary-label {
  margin-top: .3vw;
  font-size: .75vw;
  text-transform: uppercase;
  letter-spacing: .06em;
  color: rgba(255,255,255,.42);
}

@media(max-width:900px){
  .seller-profile-view .sp-pagination { gap: 6px; margin-top: 18px; }
  .seller-profile-view .sp-pagination button,
.seller-profile-view .sp-pagination a { min-width: 34px; height: 34px; font-size: .78rem; border-radius: 8px; padding: 0 8px; }
}


/* ── Category pills (All / Accounts / Items) ── */

.seller-profile-view .sp-online-dot {
  position: absolute;
  right: .18vw;
  bottom: .18vw;
  width: 1.05vw;
  height: 1.05vw;
  min-width: 12px;
  min-height: 12px;
  border-radius: 999px;
  background: #22c55e;
  border: .16vw solid rgba(8,8,22,.98);
  box-shadow: 0 0 0 .18vw rgba(34,197,94,.18), 0 0 .9vw rgba(34,197,94,.55);
  z-index: 3;
}
.seller-profile-view .sp-chip.online {
  color: #22c55e;
  background: rgba(34,197,94,.10);
  border-color: rgba(34,197,94,.28);
}
.seller-profile-view .sp-chip.offline {
  color: rgba(255,255,255,.45);
  background: rgba(148,163,184,.08);
  border-color: rgba(148,163,184,.18);
}
.seller-profile-view .sp-chip.online .sp-presence-pulse,
.seller-profile-view .sp-chip.offline .sp-presence-pulse {
  width: .48vw;
  height: .48vw;
  min-width: 6px;
  min-height: 6px;
  border-radius: 999px;
  display: inline-block;
}
.seller-profile-view .sp-chip.online .sp-presence-pulse {
  background: #22c55e;
  box-shadow: 0 0 0 0 rgba(34,197,94,.55);
  animation: spPresencePulse 1.7s ease-out infinite;
}
.seller-profile-view .sp-chip.offline .sp-presence-pulse { background: rgba(148,163,184,.55); }
@keyframes spPresencePulse {
  0% { box-shadow: 0 0 0 0 rgba(34,197,94,.55); }
  70% { box-shadow: 0 0 0 .45vw rgba(34,197,94,0); }
  100% { box-shadow: 0 0 0 0 rgba(34,197,94,0); }
}

.seller-profile-view .sp-listings-filter {
  display: flex;
  gap: .5vw;
  margin-bottom: 1.5vw;
  flex-wrap: wrap;
  align-items: center;
}
.seller-profile-view .sp-filter-pill {
  display: inline-flex;
  align-items: center;
  gap: .35vw;
  font-size: .82vw;
  font-weight: 800;
  text-transform: uppercase;
  letter-spacing: .05em;
  padding: .38vw 1vw;
  border-radius: 999px;
  border: .052vw solid rgba(99,102,241,.2);
  background: rgba(99,102,241,.07);
  color: rgba(255,255,255,.55);
  cursor: pointer;
  transition: all .15s;
  font-family: inherit;
}
.seller-profile-view .sp-filter-pill:hover {
  border-color: rgba(99,102,241,.4);
  background: rgba(99,102,241,.13);
  color: #fff;
}
.seller-profile-view .sp-filter-pill.active {
  background: linear-gradient(135deg, rgba(99,102,241,.9), rgba(139,92,246,.9));
  border-color: transparent;
  color: #fff;
  box-shadow: 0 .25vw .9vw rgba(99,102,241,.35);
}
.seller-profile-view .sp-filter-pill.active .sp-tab-count {
  background: rgba(255,255,255,.22);
  color: #fff;
}
.seller-profile-view .sp-listing-section {
  margin-bottom: 1vw;
}
.seller-profile-view .sp-listing-section + .sp-listing-section {
  margin-top: .8vw;
  padding-top: 1.2vw;
  border-top: .052vw solid rgba(99,102,241,.08);
}

/* ── Filter Toolbar (shop-style pill dropdowns) ── */
.seller-profile-view .sp-filter-toolbar {
  display: flex;
  align-items: center;
  gap: .5vw;
  margin-bottom: 1.2vw;
  flex-wrap: wrap;
}
/* Search pill */
.seller-profile-view .sp-filter-search {
  display: flex;
  align-items: center;
  gap: .4vw;
  background: rgba(255,255,255,.04);
  border: .052vw solid rgba(99,102,241,.18);
  border-radius: 999px;
  padding: .42vw 1vw;
  min-width: 11vw;
  max-width: 16vw;
  flex: 1;
  transition: border-color .15s;
}
.seller-profile-view .sp-filter-search:focus-within {
  border-color: rgba(99,102,241,.5);
  background: rgba(99,102,241,.07);
}
.seller-profile-view .sp-filter-search i {
  color: rgba(255,255,255,.35);
  font-size: .8vw;
  flex-shrink: 0;
}
.seller-profile-view .sp-filter-search input {
  background: none;
  border: none;
  outline: none;
  color: #fff;
  font-size: .82vw;
  font-family: inherit;
  width: 100%;
}
.seller-profile-view .sp-filter-search input::placeholder {
  color: rgba(255,255,255,.28);
}
/* Pill dropdown wrapper */
.seller-profile-view .sp-filterpill {
  position: relative;
}
.seller-profile-view .sp-filterpill__btn {
  display: inline-flex;
  align-items: center;
  gap: .38vw;
  font-size: .82vw;
  font-weight: 700;
  padding: .42vw 1vw;
  border-radius: 999px;
  border: .052vw solid rgba(99,102,241,.2);
  background: rgba(99,102,241,.07);
  color: rgba(255,255,255,.65);
  cursor: pointer;
  transition: all .15s;
  font-family: inherit;
  white-space: nowrap;
}
.seller-profile-view .sp-filterpill__btn:hover,
.seller-profile-view .sp-filterpill__btn[aria-expanded="true"] {
  border-color: rgba(99,102,241,.5);
  background: rgba(99,102,241,.14);
  color: #fff;
}
.seller-profile-view .sp-filterpill__btn.has-value {
  border-color: rgba(99,102,241,.7);
  background: rgba(99,102,241,.22);
  color: #c7d2fe;
}
.seller-profile-view .sp-filterpill__btn .sp-filterpill__value {
  color: #a5b4fc;
  font-weight: 900;
  font-size: .75vw;
}
.seller-profile-view .sp-filterpill__caret {
  font-size: .65vw !important;
  opacity: .55;
}
/* Dropdown panel */
.seller-profile-view .sp-dd {
  position: absolute;
  top: calc(100% + .45vw);
  left: 0;
  z-index: 50;
  background: #0f1128;
  border: .052vw solid rgba(99,102,241,.3);
  border-radius: .8vw;
  box-shadow: 0 1vw 3vw rgba(0,0,0,.55);
  min-width: 15vw;
  display: none;
  overflow: hidden;
}
.seller-profile-view .sp-dd.is-open { display: block; }
.seller-profile-view .sp-dd__head {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: .65vw 1vw .55vw;
  border-bottom: .052vw solid rgba(99,102,241,.12);
  font-size: .82vw;
  font-weight: 800;
  color: rgba(255,255,255,.7);
  text-transform: uppercase;
  letter-spacing: .06em;
}
.seller-profile-view .sp-dd__close {
  background: none;
  border: none;
  color: rgba(255,255,255,.4);
  font-size: .85vw;
  cursor: pointer;
  padding: .1vw .3vw;
  transition: color .12s;
}
.seller-profile-view .sp-dd__close:hover { color: #fff; }
.seller-profile-view .sp-dd__body { padding: .5vw 0; }
/* Option items with icons */
.seller-profile-view .sp-dd-item {
  display: flex;
  align-items: center;
  gap: .6vw;
  padding: .52vw 1vw;
  font-size: .82vw;
  font-weight: 600;
  color: rgba(255,255,255,.62);
  cursor: pointer;
  transition: all .12s;
  user-select: none;
}
.seller-profile-view .sp-dd-item:hover {
  background: rgba(99,102,241,.1);
  color: #fff;
}
.seller-profile-view .sp-dd-item.active {
  color: #a5b4fc;
  background: rgba(99,102,241,.13);
}
.seller-profile-view .sp-dd-item i {
  width: 1.1vw;
  text-align: center;
  font-size: .88vw;
  flex-shrink: 0;
  color: #818cf8;
}
.seller-profile-view .sp-dd-item .sp-dd-item__badge {
  font-size: .68vw;
  font-weight: 900;
  background: rgba(99,102,241,.18);
  border: .052vw solid rgba(99,102,241,.28);
  color: #a5b4fc;
  padding: .08vw .4vw;
  border-radius: 999px;
  letter-spacing: .04em;
  flex-shrink: 0;
}
.seller-profile-view #accGameDD {
  width: max-content;
  min-width: 260px;
  max-width: min(360px, calc(100vw - 32px));
}
.seller-profile-view #accGameDD .sp-dd-item__label {
  min-width: 0;
  flex: 1 1 auto;
  color: inherit;
  line-height: 1.25;
}
.seller-profile-view #accGameDD .sp-dd-item__badge {
  margin-left: auto;
}
/* Dual range price slider */
.seller-profile-view .sp-price-wrap {
  padding: .8vw 1.1vw .9vw;
}
.seller-profile-view .sp-price-fields {
  display: flex;
  align-items: center;
  gap: .5vw;
  margin-bottom: .75vw;
}
.seller-profile-view .sp-price-field {
  flex: 1;
}
.seller-profile-view .sp-price-field label {
  display: block;
  font-size: .68vw;
  font-weight: 800;
  text-transform: uppercase;
  letter-spacing: .06em;
  color: rgba(255,255,255,.38);
  margin-bottom: .25vw;
}
.seller-profile-view .sp-price-input {
  display: flex;
  align-items: center;
  background: rgba(255,255,255,.05);
  border: .052vw solid rgba(99,102,241,.22);
  border-radius: .45vw;
  overflow: hidden;
}
.seller-profile-view .sp-price-input:focus-within {
  border-color: rgba(99,102,241,.55);
}
.seller-profile-view .sp-price-prefix {
  padding: .32vw .4vw .32vw .6vw;
  font-size: .78vw;
  color: rgba(255,255,255,.4);
  font-weight: 700;
  flex-shrink: 0;
}
.seller-profile-view .sp-price-input input {
  background: none;
  border: none;
  outline: none;
  color: #fff;
  font-size: .82vw;
  font-family: inherit;
  width: 100%;
  padding: .32vw .45vw .32vw 0;
}
.seller-profile-view .sp-price-sep {
  font-size: .82vw;
  color: rgba(255,255,255,.3);
  flex-shrink: 0;
}
/* Dual range track */
.seller-profile-view .sp-range-wrap {
  position: relative;
  height: 1.2vw;
  margin: .4vw 0 .2vw;
}
.seller-profile-view .sp-range-wrap input[type="range"] {
  position: absolute;
  width: 100%;
  appearance: none;
  -webkit-appearance: none;
  background: none;
  pointer-events: none;
  height: .3vw;
  top: 50%;
  transform: translateY(-50%);
}
.seller-profile-view .sp-range-wrap input[type="range"]::-webkit-slider-thumb {
  -webkit-appearance: none;
  appearance: none;
  width: 1.1vw;
  height: 1.1vw;
  border-radius: 50%;
  background: linear-gradient(135deg, #6366f1, #818cf8);
  border: .15vw solid #fff;
  cursor: pointer;
  pointer-events: all;
  box-shadow: 0 .15vw .5vw rgba(99,102,241,.5);
  transition: transform .12s;
}
.seller-profile-view .sp-range-wrap input[type="range"]::-webkit-slider-thumb:hover {
  transform: scale(1.2);
}
.seller-profile-view .sp-range-wrap input[type="range"]::-moz-range-thumb {
  width: 1.1vw;
  height: 1.1vw;
  border-radius: 50%;
  background: linear-gradient(135deg, #6366f1, #818cf8);
  border: .15vw solid #fff;
  cursor: pointer;
  pointer-events: all;
  box-shadow: 0 .15vw .5vw rgba(99,102,241,.5);
}
.seller-profile-view .sp-range-track {
  position: absolute;
  width: 100%;
  height: .3vw;
  background: rgba(255,255,255,.1);
  border-radius: 999px;
  top: 50%;
  transform: translateY(-50%);
  z-index: 0;
}
.seller-profile-view .sp-range-fill {
  position: absolute;
  height: 100%;
  background: linear-gradient(90deg, #6366f1, #818cf8);
  border-radius: 999px;
}
.seller-profile-view .sp-price-labels {
  display: flex;
  justify-content: space-between;
  margin-top: .45vw;
  font-size: .72vw;
  color: rgba(255,255,255,.38);
  font-weight: 600;
}
/* Reset + results */
.seller-profile-view .sp-filter-reset {
  display: inline-flex;
  align-items: center;
  gap: .3vw;
  font-size: .75vw;
  font-weight: 700;
  color: rgba(255,255,255,.3);
  cursor: pointer;
  background: none;
  border: none;
  font-family: inherit;
  padding: .3vw .6vw;
  border-radius: 999px;
  transition: color .15s, background .15s;
}
.seller-profile-view .sp-filter-reset:hover {
  color: rgba(255,255,255,.75);
  background: rgba(255,255,255,.06);
}
.seller-profile-view .sp-filter-results {
  font-size: .75vw;
  color: rgba(255,255,255,.3);
  margin-left: auto;
  white-space: nowrap;
}

@media(max-width:900px){
  .seller-profile-view .sp-listings-filter { gap: 6px; margin-bottom: 14px; }
  .seller-profile-view .sp-filter-pill { font-size: .75rem; padding: 5px 12px; }
  .seller-profile-view .sp-listing-section + .sp-listing-section { margin-top: 8px; padding-top: 12px; }
  .seller-profile-view .sp-filter-toolbar { gap: 6px; margin-bottom: 10px; }
  .seller-profile-view .sp-filter-search { max-width: 100%; min-width: 110px; padding: 6px 12px; }
  .seller-profile-view .sp-filter-search i { font-size: .8rem; }
  .seller-profile-view .sp-filter-search input { font-size: .82rem; }
  .seller-profile-view .sp-filterpill__btn { font-size: .78rem; padding: 6px 12px; }
  .seller-profile-view .sp-filterpill__value { font-size: .72rem !important; }
  .seller-profile-view .sp-filterpill__caret { font-size: .65rem !important; }
  .seller-profile-view .sp-dd { min-width: 180px; border-radius: 12px; top: calc(100% + 6px); }
  .seller-profile-view .sp-dd__head { padding: 9px 14px 8px; font-size: .78rem; }
  .seller-profile-view .sp-dd__close { font-size: .85rem; }
  .seller-profile-view .sp-dd-item { font-size: .78rem; padding: 8px 14px; gap: 8px; }
  .seller-profile-view .sp-dd-item i { width: 16px; font-size: .82rem; }
  .seller-profile-view .sp-dd-item .sp-dd-item__badge { font-size: .68rem; padding: 1px 6px; }
  .seller-profile-view .sp-price-wrap { padding: 10px 14px 12px; }
  .seller-profile-view .sp-price-field label { font-size: .68rem; }
  .seller-profile-view .sp-price-prefix { font-size: .78rem; padding: 5px 5px 5px 8px; }
  .seller-profile-view .sp-price-input input { font-size: .82rem; padding: 5px 6px 5px 0; }
  .seller-profile-view .sp-range-wrap { height: 20px; }
  .seller-profile-view .sp-range-wrap input[type="range"]::-webkit-slider-thumb { width: 16px; height: 16px; }
  .seller-profile-view .sp-range-track { height: 4px; }
  .seller-profile-view .sp-price-labels { font-size: .7rem; margin-top: 5px; }
  .seller-profile-view .sp-dd-item img { width: 18px !important; height: 18px !important; }
  .seller-profile-view .sp-filter-reset { font-size: .72rem; padding: 4px 8px; }
  .seller-profile-view .sp-filter-results { font-size: .72rem; }
}



@keyframes sp-glow {
  0%,100%{ box-shadow:0 0 2.5vw rgba(99,102,241,.4),0 0 5vw rgba(129,140,248,.1); }
  50%{ box-shadow:0 0 4vw rgba(99,102,241,.65),0 0 8vw rgba(129,140,248,.2); }
}
.seller-profile-view .sp-avatar-wrap img {
  width:100%;
  height:100%;
  object-fit:cover;
}
.seller-profile-view .sp-avatar-ph {
  width:12.5vw;
  height:12.5vw;
  border-radius:50%;
  background: linear-gradient(135deg,rgba(99,102,241,.3),rgba(129,140,248,.2));
  border: .3vw solid rgba(99,102,241,.5);
  position: absolute;
  top: 5.5vw;
  left: 4vw;
  z-index: 2;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 4vw;
  font-weight: 900;
  color: rgba(255,255,255,.6);
  box-shadow: 0 0 2.5vw rgba(99,102,241,.4);
}

.seller-profile-view .sp-details {
  margin-top: 3vw;
}

.seller-profile-view .sp-top {
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
  gap: 2vw;
  padding-bottom: 1.8vw;
  border-bottom: .104vw solid rgba(99,102,241,.1);
}
.seller-profile-view .sp-top .sp-info h5 {
  font-size: 3vw !important;
  font-weight: 900;
  font-family: 'superchargestraight', sans-serif;
  background: linear-gradient(135deg,#fff 0%,#a5b4fc 55%,#818cf8 100%);
  -webkit-background-clip: text;
  background-clip: text;
  -webkit-text-fill-color: transparent;
  display: flex;
  align-items: center;
  gap: .5vw;
  margin-bottom: .4vw;
}
.seller-profile-view .sp-top .sp-info h5 .sp-check {
  -webkit-text-fill-color: #6366f1;
  font-size: .7em;
}
.seller-profile-view .sp-top .sp-info h6 {
  font-size: 1.1vw;
  color: rgba(255,255,255,.45);
  font-weight: 400;
  margin-bottom: .8vw;
  display: flex;
  align-items: center;
  gap: .6vw;
  flex-wrap: wrap;
}
.seller-profile-view .sp-chip {
  display: inline-flex;
  align-items: center;
  gap: .28vw;
  font-size: .82vw;
  font-weight: 800;
  text-transform: uppercase;
  letter-spacing: .05em;
  padding: .32vw .82vw;
  border-radius: 999px;
  background: rgba(255,255,255,.07);
  border: .052vw solid rgba(255,255,255,.12);
  color: rgba(255,255,255,.7);
}
.seller-profile-view .sp-chip.rank {
  background: rgba(99,102,241,.12);
  border-color: rgba(99,102,241,.3);
  color: #a5b4fc;
}
.seller-profile-view .sp-chip.verified {
  background: rgba(34,197,94,.1);
  border-color: rgba(34,197,94,.3);
  color: #22c55e;
}


.seller-profile-view .sp-nav-tabs {
  display: flex;
  gap: 0;
  border-bottom: .104vw solid rgba(99,102,241,.1);
  margin-bottom: 2vw;
  overflow-x: auto;
  scrollbar-width: none;
}
.seller-profile-view .sp-nav-tabs::-webkit-scrollbar {
  display: none;
}
.seller-profile-view .sp-nav-tabs a,
.seller-profile-view .sp-nav-tabs .sp-tab-disabled {
  display: flex;
  align-items: center;
  gap: .4vw;
  font-size: 1.05vw;
  font-weight: 700;
  color: rgba(255,255,255,.4);
  padding: .9vw 1.4vw;
  border-bottom: .18vw solid transparent;
  text-decoration: none;
  white-space: nowrap;
  transition: color .15s, border-color .15s, background .15s;
}
.seller-profile-view .sp-nav-tabs a:hover {
  color: rgba(255,255,255,.7);
}
.seller-profile-view .sp-nav-tabs a.active {
  color: #818cf8;
  border-bottom-color: #818cf8;
  background: linear-gradient(180deg,transparent,rgba(99,102,241,.04));
}
.seller-profile-view .sp-nav-tabs a i,
.seller-profile-view .sp-nav-tabs .sp-tab-disabled i {
  font-size: .95vw;
}
.seller-profile-view .sp-tab-count {
  background: rgba(99,102,241,.25);
  color: #a5b4fc;
  font-size: .68vw;
  font-weight: 900;
  padding: .15vw .52vw;
  border-radius: 999px;
  margin-left: .2vw;
}
.seller-profile-view .sp-tab-disabled {
  position: relative;
  cursor: not-allowed;
  opacity: .7;
}
.seller-profile-view .sp-tab-disabled:hover {
  color: rgba(255,255,255,.72);
}
.seller-profile-view .sp-tab-disabled[data-tooltip]:hover::after {
  content: attr(data-tooltip);
  position: absolute;
  left: 50%;
  bottom: calc(100% + .45vw);
  transform: translateX(-50%);
  background: #11162a;
  color: #fff;
  border: .052vw solid rgba(99,102,241,.25);
  border-radius: .55vw;
  padding: .5vw .7vw;
  font-size: .72vw;
  font-weight: 700;
  white-space: nowrap;
  box-shadow: 0 .5vw 1.5vw rgba(0,0,0,.25);
  z-index: 20;
}
.seller-profile-view .sp-tab-disabled[data-tooltip]:hover::before {
  content: '';
  position: absolute;
  left: 50%;
  bottom: calc(100% + .12vw);
  transform: translateX(-50%);
  border-left: .35vw solid transparent;
  border-right: .35vw solid transparent;
  border-top: .35vw solid #11162a;
  z-index: 21;
}

.seller-profile-view .sp-tab-layout {
  display: grid;
  grid-template-columns: 1fr 20vw;
  gap: 2.5vw;
  align-items: start;
}
.seller-profile-view .tab-pane {
  display: none;
}
.seller-profile-view .tab-pane.active {
  display: block;
}

.seller-profile-view .sp-section {
  margin-bottom: 2vw;
}
.seller-profile-view .sp-section-label {
  font-size: .88vw;
  font-weight: 900;
  text-transform: uppercase;
  letter-spacing: .14em;
  color: #818cf8;
  margin-bottom: .85vw;
  display: flex;
  align-items: center;
  gap: .5vw;
}
.seller-profile-view .sp-section-label::before {
  content: '';
  width: .2vw;
  height: .85vw;
  border-radius: 999px;
  flex-shrink: 0;
  background: linear-gradient(180deg, #6366f1, #818cf8);
}
.seller-profile-view .sp-section-label::after {
  content: '';
  flex: 1;
  height: 1px;
  background: linear-gradient(90deg, rgba(99,102,241,.2), transparent);
}
.seller-profile-view .sp-view-all-link {
  display: inline-flex;
  align-items: center;
  gap: .4vw;
  margin-top: .9vw;
  padding: .5vw 1vw;
  border-radius: 999px;
  border: .052vw solid rgba(99,102,241,.22);
  background: linear-gradient(135deg, rgba(99,102,241,.10), rgba(139,92,246,.06));
  color: #c7d2fe;
  text-decoration: none;
  font-size: .82vw;
  font-weight: 800;
  transition: all .18s ease;
}
.seller-profile-view .sp-view-all-link:hover {
  color: #fff;
  border-color: rgba(99,102,241,.4);
  background: linear-gradient(135deg, rgba(99,102,241,.18), rgba(139,92,246,.12));
  box-shadow: 0 .5vw 1.4vw rgba(99,102,241,.16);
}
.seller-profile-view .sp-view-all-link i { transition: transform .18s ease; }
.seller-profile-view .sp-view-all-link:hover i { transform: translateX(2px); }
@media(max-width:900px){
  .seller-profile-view .sp-view-all-link { font-size: 13px; padding: 10px 16px; gap: 8px; margin-top: 12px; }
}

.seller-profile-view .sp-stats-row {
  display: flex;
  gap: 1vw;
  margin-bottom: 2vw;
  flex-wrap: wrap;
}
.seller-profile-view .sp-stat-card {
  flex: 1;
  min-width: 10vw;
  background: rgba(99,102,241,.07);
  border: .052vw solid rgba(99,102,241,.15);
  border-radius: .75vw;
  padding: 1vw 1.2vw;
  display: flex;
  align-items: center;
  gap: .75vw;
  transition: all .2s;
}
.seller-profile-view .sp-stat-card:hover {
  border-color: rgba(99,102,241,.3);
  background: rgba(99,102,241,.1);
}
.seller-profile-view .sp-stat-card .sp-stat-icon {
  width: 2.4vw;
  height: 2.4vw;
  border-radius: .5vw;
  background: rgba(99,102,241,.15);
  border: .052vw solid rgba(99,102,241,.25);
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.2vw;
  flex-shrink: 0;
  color: #a5b4fc;
}
.seller-profile-view .sp-stat-card .sp-stat-val {
  font-size: 2vw;
  font-weight: 900;
  color: #fff;
  line-height: 1;
}
.seller-profile-view .sp-stat-card .sp-stat-lbl {
  font-size: .78vw;
  color: rgba(255,255,255,.4);
  text-transform: uppercase;
  letter-spacing: .06em;
  margin-top: .18vw;
}

/* adapted listings cards */
.seller-profile-view .sp-accounts-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(17vw, 1fr));
  gap: 1vw;
}
.seller-profile-view .sp-account-card {
  background: linear-gradient(180deg, rgba(10,12,30,.98) 0%, rgba(8,8,22,.98) 100%);
  border: .052vw solid rgba(99,102,241,.12);
  border-radius: .95vw;
  overflow: hidden;
  text-decoration: none;
  color: #fff;
  display: flex;
  flex-direction: column;
  transition: border-color .18s, transform .18s, box-shadow .2s;
  cursor: pointer;
  position: relative;
}
.seller-profile-view .sp-account-card:hover {
  border-color: rgba(99,102,241,.42);
  transform: translateY(-.2vw);
  box-shadow: 0 .8vw 2vw rgba(99,102,241,.16);
  color: #fff;
  text-decoration: none;
}
.seller-profile-view .sp-card-img,
.seller-profile-view .sp-card-img-empty {
  width: 100%;
  aspect-ratio: 16/9;
  display: block;
}
.seller-profile-view .sp-card-img {
  object-fit: cover;
  background: linear-gradient(135deg,#0d0525,#1e1050);
}
.seller-profile-view .sp-card-img--product {
  box-sizing:border-box;
  object-fit:contain;
  padding:14px 20px;
  background:
    radial-gradient(circle at 50% 38%,rgba(99,102,241,.16),transparent 58%),
    linear-gradient(135deg,#10082b,#160d3d);
}
.seller-profile-view .sp-card-img-empty {
  background: linear-gradient(135deg,rgba(99,102,241,.1),rgba(129,140,248,.05));
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 2.5vw;
  color: rgba(99,102,241,.3);
}
.seller-profile-view .sp-card-img-empty img {
  width:52px;
  height:52px;
  object-fit:contain;
}
.seller-profile-view .sp-card-body {
  padding: .9vw 1vw 1vw;
  flex: 1;
  display: flex;
  flex-direction: column;
  gap: .45vw;
}
.seller-profile-view .sp-card-title {
  font-size: .95vw;
  font-weight: 800;
  color: #fff;
  line-height: 1.38;
  overflow: hidden;
  text-overflow: ellipsis;
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  min-height: 2.7em;
}
.seller-profile-view .sp-card-meta {
  font-size: .72vw;
  color: rgba(255,255,255,.38);
  font-weight: 700;
  display: flex;
  align-items: center;
  gap: .3vw;
  flex-wrap: nowrap;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}
.seller-profile-view .sp-card-meta-server {
  color: rgba(255,255,255,.55);
  font-weight: 800;
  text-transform: uppercase;
}
.seller-profile-view .sp-card-meta-sep {
  color: rgba(255,255,255,.2);
  font-weight: 400;
}
.seller-profile-view .sp-card-meta-rank {
  color: rgba(255,255,255,.55);
  font-weight: 700;
  overflow: hidden;
  text-overflow: ellipsis;
}
.seller-profile-view .sp-card-tags {
  display: flex;
  flex-wrap: wrap;
  gap: .3vw;
}
.seller-profile-view .sp-card-tags span {
  font-size: .68vw;
  font-weight: 800;
  text-transform: uppercase;
  letter-spacing: .05em;
  padding: .18vw .55vw;
  border-radius: 999px;
  background: rgba(99,102,241,.1);
  border: .052vw solid rgba(99,102,241,.2);
  color: #a5b4fc;
}
.seller-profile-view .sp-card-highlights {
  display: flex;
  flex-wrap: wrap;
  gap: .3vw;
}
.seller-profile-view .sp-card-highlights span {
  font-size: .66vw;
  font-weight: 700;
  padding: .16vw .48vw;
  border-radius: 999px;
  background: rgba(255,255,255,.04);
  border: .052vw solid rgba(255,255,255,.08);
  color: rgba(255,255,255,.62);
}
.seller-profile-view .sp-card-bottom {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: .7vw;
  margin-top: auto;
  padding-top: .55vw;
}
.seller-profile-view .sp-card-price {
  font-size: 1.42vw;
  font-weight: 900;
  color: #fff;
}
.seller-profile-view .sp-card-buy {
  display: inline-flex;
  align-items: center;
  gap: .35vw;
  font-size: .72vw;
  font-weight: 900;
  text-transform: uppercase;
  letter-spacing: .06em;
  color: #eef2ff;
  background: linear-gradient(135deg, rgba(99,102,241,.9), rgba(139,92,246,.9));
  padding: .38vw .85vw;
  border-radius: 999px;
  box-shadow: 0 .25vw .9vw rgba(99,102,241,.35);
  transition: box-shadow .18s, transform .18s;
  white-space: nowrap;
}
.seller-profile-view .sp-account-card:hover .sp-card-buy {
  box-shadow: 0 .4vw 1.4vw rgba(99,102,241,.55);
  transform: translateY(-.05vw);
}
.seller-profile-view .sp-delivery-badge {
  display: inline-flex;
  align-items: center;
  gap: .25vw;
  font-size: .62vw;
  font-weight: 900;
  text-transform: uppercase;
  padding: .12vw .45vw;
  border-radius: 999px;
  background: rgba(34,197,94,.12);
  border: .052vw solid rgba(34,197,94,.3);
  color: #22c55e;
}
.seller-profile-view .sp-empty {
  text-align: center;
  padding: 3vw 0;
  color: rgba(255,255,255,.3);
  font-size: 1.1vw;
}
.seller-profile-view .sp-empty i {
  font-size: 3vw;
  margin-bottom: 1vw;
  display: block;
}

.seller-profile-view .sp-sidebar-wrap {
  position: sticky;
  top: 5vw;
  max-height: calc(100vh - 6vw);
  overflow-y: auto;
  scrollbar-width: none;
}
.seller-profile-view .sp-sidebar-wrap::-webkit-scrollbar {
  display: none;
}
.seller-profile-view .sp-sidebar {
  background: linear-gradient(160deg,#08071a 0%,#110d30 100%);
  border: .052vw solid rgba(99,102,241,.28);
  border-radius: 1vw;
  overflow: hidden;
  box-shadow: 0 .5vw 2.5vw rgba(99,102,241,.1);
  transition: box-shadow .25s;
}
.seller-profile-view .sp-sidebar:hover {
  box-shadow: 0 .9vw 3.8vw rgba(99,102,241,.2);
}
.seller-profile-view .sp-sidebar-bar {
  height: .2vw;
  background: linear-gradient(90deg,#6366f1,#818cf8,#6366f1);
}
.seller-profile-view .sp-sidebar-body {
  padding: 1.4vw;
}
.seller-profile-view .sp-sidebar-prev {
  display: flex;
  align-items: center;
  gap: .8vw;
  padding-bottom: 1vw;
  margin-bottom: 1vw;
  border-bottom: .052vw solid rgba(99,102,241,.08);
}
.seller-profile-view .sp-sidebar-prev img,
.seller-profile-view .sp-sidebar-prev .sp-sidebar-ph {
  width: 2.95vw;
  height: 2.95vw;
  border-radius: 50%;
  object-fit: cover;
  border: .12vw solid rgba(99,102,241,.45);
  box-shadow: 0 0 0 .18vw rgba(99,102,241,.08);
  flex-shrink: 0;
}
.seller-profile-view .sp-sidebar-ph {
  background: linear-gradient(135deg, rgba(99,102,241,.22), rgba(129,140,248,.12));
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: .9vw;
  color: rgba(255,255,255,.72);
}
.seller-profile-view .sp-sidebar-name {
  font-size: 1.05vw;
  font-weight: 900;
  color: #fff;
  line-height: 1.1;
  margin-bottom: .18vw;
}
.seller-profile-view .sp-sidebar-meta {
  display: flex;
  flex-wrap: wrap;
  gap: .35vw;
}
.seller-profile-view .sp-sidebar-chip {
  display: inline-flex;
  align-items: center;
  gap: .28vw;
  font-size: .62vw;
  font-weight: 900;
  text-transform: uppercase;
  letter-spacing: .06em;
  padding: .22vw .5vw;
  border-radius: 999px;
  border: .052vw solid rgba(255,255,255,.08);
  color: rgba(255,255,255,.76);
  background: rgba(255,255,255,.04);
}
.seller-profile-view .sp-sidebar-chip.verified {
  color: #22c55e;
  background: rgba(34,197,94,.08);
  border-color: rgba(34,197,94,.22);
}
.seller-profile-view .sp-sidebar-chip.rank {
  color: #c7d2fe;
  background: rgba(99,102,241,.12);
  border-color: rgba(99,102,241,.26);
}
.seller-profile-view .sp-sidebar-title {
  font-size: 1.35vw;
  font-weight: 900;
  color: #fff;
  margin-bottom: .18vw;
}
.seller-profile-view .sp-sidebar-sub {
  font-size: .84vw;
  color: rgba(255,255,255,.42);
  margin-bottom: .75vw;
}
.seller-profile-view .sp-sidebar-div {
  height: 1px;
  background: linear-gradient(90deg, rgba(99,102,241,.18), rgba(99,102,241,.04), transparent);
  margin: .8vw 0;
}
.seller-profile-view .sp-sidebar-stats {
  display: flex;
  flex-direction: column;
  gap: .55vw;
  margin-bottom: .2vw;
}
.seller-profile-view .sp-sidebar-stat-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
  font-size: .88vw;
}
.seller-profile-view .sp-sidebar-stat-row span {
  color: rgba(255,255,255,.4);
}
.seller-profile-view .sp-sidebar-stat-row strong {
  color: #fff;
  font-weight: 800;
}

.seller-profile-view .sp-sidebar-cta {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: .4vw;
  width: 100%;
  margin-top: .2vw;
  padding: .82vw 1vw;
  border-radius: .8vw;
  text-decoration: none;
  font-size: .78vw;
  font-weight: 900;
  text-transform: uppercase;
  letter-spacing: .06em;
  color: #eef2ff;
  background: linear-gradient(135deg, rgba(99,102,241,.96), rgba(139,92,246,.96));
  box-shadow: 0 .6vw 1.8vw rgba(99,102,241,.22);
  transition: transform .18s, box-shadow .18s, opacity .18s;
}
.seller-profile-view .sp-sidebar-cta:hover {
  color: #fff;
  text-decoration: none;
  transform: translateY(-.08vw);
  box-shadow: 0 .85vw 2.2vw rgba(99,102,241,.3);
}
.seller-profile-view .sp-trust-note {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: .28vw;
  font-size: .72vw;
  color: rgba(255,255,255,.34);
  margin-top: .65vw;
}


.seller-profile-view .sp-bio-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 1vw;
  margin-bottom: 2vw;
}
.seller-profile-view .sp-info-card {
  background: rgba(255,255,255,.03);
  border: .052vw solid rgba(255,255,255,.08);
  border-radius: .95vw;
  padding: 1.15vw 1.2vw;
}
.seller-profile-view .sp-info-card.full {
  grid-column: 1 / -1;
}
.seller-profile-view .sp-flags {
  display: flex;
  flex-wrap: wrap;
  gap: .55vw;
}
.seller-profile-view .sp-flag {
  width: 2vw;
  height: 2vw;
  border-radius: 999px;
  object-fit: cover;
  border: .052vw solid rgba(255,255,255,.12);
  box-shadow: 0 0 0 .08vw rgba(255,255,255,.03);
  background: rgba(255,255,255,.04);
}
.seller-profile-view .sp-description {
  color: rgba(255,255,255,.72);
  font-size: .92vw;
  line-height: 1.7;
  white-space: pre-line;
}


.seller-profile-view .sp-top-flags {
  display: inline-flex;
  align-items: center;
  gap: .32vw;
  flex-wrap: wrap;
  margin-left: .45vw;
  vertical-align: middle;
}
.seller-profile-view .sp-top-flag {
  width: 1.28vw;
  height: 1.28vw;
  border-radius: 999px;
  object-fit: cover;
  border: .052vw solid rgba(255,255,255,.14);
  box-shadow: 0 0 0 .06vw rgba(255,255,255,.03);
  background: rgba(255,255,255,.04);
}
.seller-profile-view .sp-card-rank-icon {
  width: 1.35vw;
  height: 1.35vw;
  object-fit: contain;
  vertical-align: middle;
  margin-right: .25vw;
  flex-shrink: 0;
}
.seller-profile-view .sp-card-delivery-badge {
  position: absolute;
  top: .55vw;
  right: .55vw;
  z-index: 2;
  display: inline-flex;
  align-items: center;
  gap: .25vw;
  font-size: .65vw;
  font-weight: 900;
  text-transform: uppercase;
  letter-spacing: .05em;
  padding: .22vw .55vw;
  border-radius: 999px;
  background: rgba(34,197,94,.18);
  border: .052vw solid rgba(34,197,94,.4);
  color: #22c55e;
  backdrop-filter: blur(4px);
}
.seller-profile-view .sp-card-highlights span i {
  font-size: .75em;
  opacity: .7;
  margin-right: .18vw;
}

/* Compact seller listings: denser than the marketplace cards so more of the
   seller's inventory remains visible without excessive vertical scrolling. */
@media (min-width: 1280px) {
  .seller-profile-view .sp-accounts-grid {
    grid-template-columns: repeat(4, minmax(0, 1fr));
    gap: 14px;
  }
  .seller-profile-view .sp-account-card {
    border-radius: 16px;
  }
  .seller-profile-view .sp-card-img,
  .seller-profile-view .sp-card-img-empty {
    aspect-ratio: 16 / 7.5;
  }
  .seller-profile-view .sp-card-body {
    padding: 13px 15px 14px;
    gap: 7px;
  }
  .seller-profile-view .sp-card-meta {
    font-size: 12px;
    gap: 5px;
  }
  .seller-profile-view .sp-card-rank-icon {
    width: 18px;
    height: 18px;
    margin-right: 2px;
  }
  .seller-profile-view .sp-card-title {
    min-height: 1.4em;
    font-size: 15px;
    line-height: 1.4;
    -webkit-line-clamp: 1;
  }
  .seller-profile-view .sp-card-highlights {
    flex-wrap: nowrap;
    gap: 5px;
    overflow: hidden;
  }
  .seller-profile-view .sp-card-highlights span {
    flex: 0 0 auto;
    padding: 3px 7px;
    font-size: 10.5px;
  }
  .seller-profile-view .sp-card-bottom {
    gap: 10px;
    padding-top: 7px;
  }
  .seller-profile-view .sp-card-price {
    font-size: 22px;
  }
  .seller-profile-view .sp-card-buy {
    gap: 6px;
    padding: 7px 13px;
    font-size: 11px;
  }
  .seller-profile-view .sp-card-delivery-badge {
    top: 9px;
    right: 9px;
    padding: 4px 9px;
    font-size: 10px;
  }
}

@media (min-width: 769px) and (max-width: 1279px) {
  .seller-profile-view .sp-accounts-grid {
    grid-template-columns: repeat(3, minmax(0, 1fr));
  }
  .seller-profile-view .sp-card-img,
  .seller-profile-view .sp-card-img-empty {
    aspect-ratio: 16 / 8;
  }
}

.seller-profile-view .sp-description-text {
  color: rgba(255,255,255,.74);
  font-size: .92vw;
  line-height: 1.72;
  max-width: 62vw;
  margin-bottom: 2vw;
}


.seller-profile-view .sp-top-more-wrapper {
  position: relative;
  display: inline-flex;
}
.seller-profile-view .sp-top-more-popup {
  position: absolute;
  top: 120%;
  left: 50%;
  transform: translateX(-50%);
  display: flex;
  flex-wrap: wrap;
  gap: .35vw;
  padding: .6vw;
  background: #1a1d24;
  border: .052vw solid rgba(255,255,255,.08);
  border-radius: .6vw;
  box-shadow: 0 .5vw 1.5vw rgba(0,0,0,.4);
  opacity: 0;
  pointer-events: none;
  transition: .2s ease;
  z-index: 50;
  min-width: 10vw;
}
.seller-profile-view .sp-top-more-wrapper:hover .sp-top-more-popup {
  opacity: 1;
  pointer-events: auto;
}
.seller-profile-view .sp-top-more {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  min-width: 1.45vw;
  height: 1.45vw;
  padding: 0 .38vw;
  border-radius: 999px;
  background: rgba(255,255,255,.06);
  border: .052vw solid rgba(255,255,255,.12);
  color: rgba(255,255,255,.82);
  font-size: .68vw;
  font-weight: 800;
  line-height: 1;
  cursor: default;
}

@media(max-width:900px){
  .seller-profile-view header {
    min-height: auto !important;
    padding: calc(var(--lb-content-top, 126px) + 22px) 16px 42px !important;
  }
  .seller-profile-view header .content {
    margin: 36px 0 0 !important;
  }
  .seller-profile-view header h1 {
    font-size: clamp(36px, 11vw, 50px) !important;
    line-height: .98 !important;
  }
  .seller-profile-view header p {
    font-size: .9rem;
    line-height: 1.55;
  }

  .seller-profile-view .main-content { margin: 12px 10px; border-radius: 14px; padding: 14px; }
  .seller-profile-view .main-content .sp-cover { height: 120px; border-radius: 10px 10px 0 0; }
  .seller-profile-view .sp-avatar-wrap { width: 72px; height: 72px; top: 80px; left: 14px; border-width: 2px; }
  .seller-profile-view .sp-avatar-ph { width: 72px; height: 72px; top: 80px; left: 14px; font-size: 2rem; border-width: 2px; }
  .seller-profile-view .sp-details { margin-top: 50px; }

  .seller-profile-view .sp-top { flex-direction: column; align-items: flex-start; gap: 10px; }
  .seller-profile-view .sp-top .sp-info h5 { font-size: 1.65rem !important; }
  .seller-profile-view .sp-top .sp-info h6 { font-size: .82rem; flex-wrap: wrap; gap: 6px; margin-bottom: 8px; }
  .seller-profile-view .sp-chip { font-size: .72rem; padding: 4px 9px; }
  .seller-profile-view .sp-role-badge { font-size: .75rem; padding: 4px 10px; }

  .seller-profile-view .sp-nav-tabs a,
  .seller-profile-view .sp-nav-tabs .sp-tab-disabled { font-size: .8rem; padding: 10px 11px; }
  .seller-profile-view .sp-nav-tabs a i,
  .seller-profile-view .sp-nav-tabs .sp-tab-disabled i { font-size: .8rem; }
  .seller-profile-view .sp-tab-count { font-size: .65rem; padding: 2px 6px; }

  .seller-profile-view .sp-tab-layout { grid-template-columns: 1fr; gap: 0; }

  .seller-profile-view .sp-stats-row { gap: 8px; }
  .seller-profile-view .sp-stat-card { min-width: calc(50% - 4px); border-radius: 10px; padding: 12px; gap: 8px; }
  .seller-profile-view .sp-stat-card .sp-stat-icon { width: 32px; height: 32px; font-size: .9rem; border-radius: 7px; }
  .seller-profile-view .sp-stat-card .sp-stat-val { font-size: .88rem !important; color: #fff !important; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
  .seller-profile-view .sp-stat-card .sp-stat-lbl { font-size: .65rem !important; white-space: nowrap; }

  .seller-profile-view .sp-section-label { font-size: .8rem !important; }
  .seller-profile-view .sp-section-label::before { width: 3px; height: 12px; }

  .seller-profile-view .sp-accounts-grid { grid-template-columns: 1fr; gap: 10px; }
  .seller-profile-view .sp-account-card { border-radius: 10px; }
  .seller-profile-view .sp-card-body { padding: 9px 10px 11px; gap: 5px; }
  .seller-profile-view .sp-card-meta { font-size: .72rem !important; }
  .seller-profile-view .sp-card-rank-icon { width: 14px; height: 14px; margin-right: 3px; }
  .seller-profile-view .sp-card-title { font-size: .85rem !important; min-height: auto; line-height: 1.35; }
  .seller-profile-view .sp-card-highlights { gap: 4px; }
  .seller-profile-view .sp-card-highlights span { font-size: .68rem !important; padding: 3px 6px; }
  .seller-profile-view .sp-card-highlights span i { margin-right: 3px; }
  .seller-profile-view .sp-card-bottom { padding-top: 7px; gap: 6px; }
  .seller-profile-view .sp-card-price { font-size: 1.15rem !important; }
  .seller-profile-view .sp-card-buy { font-size: .68rem !important; gap: 4px; padding: 5px 10px; border-radius: 999px; }
  .seller-profile-view .sp-card-img-empty { font-size: 1.8rem; }
  .seller-profile-view .sp-card-delivery-badge { top: 5px; right: 5px; font-size: .65rem !important; padding: 2px 7px; gap: 3px; }
  .seller-profile-view .sp-delivery-badge { font-size: .68rem !important; padding: 3px 7px; }

  .seller-profile-view .sp-empty { font-size: .9rem; padding: 24px 0; }
  .seller-profile-view .sp-empty i { font-size: 2rem; }

  .seller-profile-view .sp-reviews-list { gap: 10px; }
  .seller-profile-view .sp-review-card { padding: 14px; border-radius: 10px; }
  .seller-profile-view .sp-review-avatar,
  .seller-profile-view .sp-review-avatar-ph { width: 42px; height: 42px; }
  .seller-profile-view .sp-review-avatar-ph { font-size: .9rem; }
  .seller-profile-view .sp-review-name { font-size: .95rem; }
  .seller-profile-view .sp-review-date { font-size: .75rem; }
  .seller-profile-view .sp-review-stars { font-size: .9rem; }
  .seller-profile-view .sp-review-rating-text { font-size: .8rem; }
  .seller-profile-view .sp-review-comment { font-size: .88rem; }
  .seller-profile-view .sp-review-summary-card { min-width: calc(50% - 5px); padding: 12px; border-radius: 10px; }
  .seller-profile-view .sp-review-summary-value { font-size: 1.2rem; }
  .seller-profile-view .sp-review-summary-label { font-size: .68rem; }

  .seller-profile-view .sp-bio-grid { grid-template-columns: 1fr; gap: 10px; margin-bottom: 18px; }
  .seller-profile-view .sp-info-card { border-radius: 10px; padding: 13px; }
  .seller-profile-view .sp-flag { width: 22px; height: 22px; }
  .seller-profile-view .sp-description { font-size: .84rem; line-height: 1.6; }
  .seller-profile-view .sp-top-flags { gap: 4px; margin-left: 6px; }
  .seller-profile-view .sp-top-flag { width: 17px; height: 17px; }
  .seller-profile-view .sp-top-more-wrapper { position: relative; display: inline-flex; }
  .seller-profile-view .sp-top-more-popup {
    position: absolute;
    top: 120%;
    left: 50%;
    transform: translateX(-50%);
    display: flex;
    flex-wrap: wrap;
    gap: 5px;
    padding: 8px;
    background: #1a1d24;
    border: 1px solid rgba(255,255,255,.08);
    border-radius: 9px;
    box-shadow: 0 8px 24px rgba(0,0,0,.4);
    opacity: 0;
    pointer-events: none;
    transition: .2s ease;
    z-index: 50;
    min-width: 140px;
  }
  .seller-profile-view .sp-top-more-wrapper:hover .sp-top-more-popup { opacity: 1; pointer-events: auto; }
  .seller-profile-view .sp-top-more { min-width: 20px; height: 20px; padding: 0 6px; font-size: 11px; }
  .seller-profile-view .sp-description-block { margin: 0 0 16px; }
  .seller-profile-view .sp-description-text { font-size: .86rem; line-height: 1.62; max-width: 100%; margin-bottom: 20px; }

  .seller-profile-view .sp-sidebar-wrap { position: static; max-height: none; overflow: visible; margin-top: 20px; }
  .seller-profile-view .sp-sidebar { border-radius: 14px; }
  .seller-profile-view .sp-sidebar-bar { height: 3px; }
  .seller-profile-view .sp-sidebar-body { padding: 15px; }
  .seller-profile-view .sp-sidebar-prev { gap: 10px; padding-bottom: 12px; margin-bottom: 12px; }
  .seller-profile-view .sp-sidebar-prev img,
  .seller-profile-view .sp-sidebar-ph { width: 42px; height: 42px; }
  .seller-profile-view .sp-sidebar-ph { font-size: .88rem; }
  .seller-profile-view .sp-sidebar-name { font-size: .95rem; }
  .seller-profile-view .sp-sidebar-meta { gap: 6px; }
  .seller-profile-view .sp-sidebar-chip { font-size: .65rem; padding: 4px 8px; }
  .seller-profile-view .sp-sidebar-title { font-size: 1.1rem; margin-bottom: 3px; }
  .seller-profile-view .sp-sidebar-sub { font-size: .8rem; margin-bottom: 8px; }
  .seller-profile-view .sp-sidebar-stats { gap: 8px; }
  .seller-profile-view .sp-sidebar-stat-row { font-size: .88rem !important; }
  .seller-profile-view .sp-sidebar-stat-row span { font-size: .88rem !important; }
  .seller-profile-view .sp-sidebar-stat-row strong { font-size: .88rem !important; }
  .seller-profile-view .sp-sidebar-cta { font-size: .8rem; padding: 12px 14px; border-radius: 10px; gap: 6px; }
  .seller-profile-view .sp-trust-note { font-size: .72rem; margin-top: 8px; gap: 4px; }
}

/* ═══════════════════════════════════════════════════════
   SP-HERO — full-bleed banner hero. Replaces the generic
   "Seller Profile" text header + the separate cover-in-card;
   the seller's own banner is now the header, with avatar,
   name and chips overlaid directly on it.
═══════════════════════════════════════════════════════ */
.sp-hero{
  position:relative;
  width:100%;
  margin-top:var(--lb-content-top, 132px);
  height:clamp(300px, 30vw, 420px);
  overflow:hidden;
  isolation:isolate;
  background:linear-gradient(135deg,#0d0525 0%,#1e1050 50%,#0d0525 100%);
}
.sp-hero__banner-img{
  position:absolute; inset:0; z-index:0;
  width:100%; height:100%; object-fit:cover; display:block;
}
.sp-hero__scrim{
  position:absolute; inset:0; z-index:1;
  background:
    linear-gradient(180deg, rgba(7,8,21,.05) 0%, rgba(7,8,21,.35) 55%, #070815 100%),
    linear-gradient(90deg, rgba(7,8,21,.7) 0%, transparent 45%);
}
.sp-hero__content{
  position:absolute; left:0; right:0; bottom:0; z-index:2;
  padding:0 4.167vw 2vw;
  display:flex; align-items:flex-end; gap:1.6vw; flex-wrap:wrap;
}
.sp-hero__content .sp-avatar-wrap,
.sp-hero__content .sp-avatar-ph{
  position:relative !important; top:auto !important; left:auto !important;
  width:8vw; height:8vw;
  flex-shrink:0;
}
.sp-hero__info{ flex:1; min-width:0; padding-bottom:.3vw; }
.sp-hero__name{
  font-size:clamp(28px, 2.6vw, 42px) !important; font-weight:900;
  line-height:1.05 !important; margin:0 0 .5vw !important;
  display:flex; align-items:center; gap:.5vw; flex-wrap:wrap;
  font-family:'superchargestraight',sans-serif;
  background:linear-gradient(135deg,#fff 0%,#a5b4fc 55%,#818cf8 100%);
  -webkit-background-clip:text; background-clip:text; -webkit-text-fill-color:transparent;
  text-shadow:0 12px 34px rgba(0,0,0,.4);
}
.sp-hero__name .sp-check{ -webkit-text-fill-color:#6366f1; font-size:.7em; }
.sp-hero__chips{ display:flex; align-items:center; gap:.6vw; flex-wrap:wrap; }

/* Card now starts directly with the nav tabs — no avatar overlap to clear */
.seller-profile-view .sp-details{ margin-top:1.2vw !important; }

/* Mobile: banner stays a plain decorative strip — avatar/name/chips
   move below it onto a solid panel instead of overlaying the image. */
@media(max-width:900px){
  .sp-hero{
    height:auto;
    isolation:auto;
    overflow:visible;
  }
  .sp-hero__banner-img{
    position:static;
    height:130px;
    width:100%;
  }
  .sp-hero__scrim{ display:none; }
  .sp-hero__content{
    position:static;
    display:flex;
    flex-wrap:wrap;
    align-items:center;
    gap:12px;
    padding:16px;
    background:#0d1021;
    border-bottom:1px solid rgba(255,255,255,.07);
  }
  .sp-hero__content .sp-avatar-wrap,
  .sp-hero__content .sp-avatar-ph{ width:56px; height:56px; }
  .sp-hero__info{ flex:1; min-width:0; padding-bottom:0; }
  .sp-hero__name{ font-size:clamp(20px,6.5vw,26px) !important; }
  .seller-profile-view .sp-details{ margin-top:16px !important; }
}

/* ---- Main content wrap — no outer card, tabs sit directly below the hero ---- */
.seller-profile-view .main-content{
  margin:2vw 4.167vw 4vw !important;
  padding:0 !important;
  background:transparent !important;
  border:none !important;
  box-shadow:none !important;
  border-radius:0 !important;
}
@media(max-width:900px){
  .seller-profile-view .main-content{ margin:1.2vw 16px 32px !important; padding:0 !important; }
}

/* =====================================================================
   Seller storefront 2026
   A focused marketplace layout: framed identity hero, compact store nav,
   full-width inventory and reviews without the old duplicate sidebar.
   ===================================================================== */
.seller-profile-view {
  background:
    radial-gradient(900px 520px at 50% 180px, rgba(79,70,229,.10), transparent 70%),
    #070812 !important;
}

@media (min-width:901px) {
  .seller-profile-view .sp-hero {
    width:min(1280px,calc(100% - 48px));
    height:270px;
    margin:calc(var(--lb-content-top,132px) + 28px) auto 0;
    border:1px solid rgba(129,140,248,.18);
    border-radius:22px;
    box-shadow:0 24px 70px rgba(0,0,0,.34),inset 0 1px 0 rgba(255,255,255,.05);
  }
  .seller-profile-view .sp-hero__scrim {
    background:
      linear-gradient(180deg,rgba(5,7,18,.08),rgba(5,7,18,.28) 48%,rgba(5,7,18,.94)),
      linear-gradient(90deg,rgba(5,7,18,.82),rgba(5,7,18,.12) 68%,rgba(5,7,18,.34));
  }
  .seller-profile-view .sp-hero__content {
    padding:0 30px 28px;
    gap:18px;
  }
  .seller-profile-view .sp-hero__content .sp-avatar-wrap,
  .seller-profile-view .sp-hero__content .sp-avatar-ph {
    width:92px;
    height:92px;
    border-width:3px;
    box-shadow:0 0 0 5px rgba(7,8,18,.78),0 16px 38px rgba(0,0,0,.42);
  }
  .seller-profile-view .sp-hero__name {
    margin-bottom:10px !important;
    font-family:inherit;
    font-size:clamp(28px,2.2vw,38px) !important;
    letter-spacing:-.025em;
    background:none;
    -webkit-text-fill-color:#fff;
    text-shadow:0 8px 24px rgba(0,0,0,.45);
  }
  .seller-profile-view .sp-hero__chips {
    gap:7px;
  }
  .seller-profile-view .sp-top-flags {
    min-height:28px;
    margin:0;
    padding:4px 9px;
    gap:5px;
    border:1px solid rgba(129,140,248,.22);
    border-radius:999px;
    background:rgba(10,12,27,.62);
    backdrop-filter:blur(10px);
  }
  .seller-profile-view .sp-top-flags__icon {
    margin-right:2px;
    color:#9da6ff;
    font-size:11px;
  }
  .seller-profile-view .sp-top-flag {
    width:18px;
    height:18px;
    margin:0;
    border-width:1px;
  }
  .seller-profile-view .sp-chip {
    min-height:28px;
    padding:5px 10px;
    font-size:11px;
    backdrop-filter:blur(10px);
  }

  .seller-profile-view .main-content {
    width:min(1280px,calc(100% - 48px));
    margin:18px auto 72px !important;
  }
  .seller-profile-view .sp-details {
    margin:0 !important;
  }
  .seller-profile-view .sp-nav-tabs {
    gap:5px;
    margin:0 0 14px;
    padding:6px;
    border:1px solid rgba(129,140,248,.14);
    border-radius:16px;
    background:rgba(10,12,27,.78);
    box-shadow:inset 0 1px 0 rgba(255,255,255,.035);
  }
  .seller-profile-view .sp-nav-tabs a {
    min-height:42px;
    padding:0 16px;
    border:0;
    border-radius:11px;
    font-size:13px;
    color:rgba(255,255,255,.52);
  }
  .seller-profile-view .sp-nav-tabs a:hover {
    color:#fff;
    background:rgba(255,255,255,.04);
  }
  .seller-profile-view .sp-nav-tabs a.active {
    color:#fff;
    border:0;
    background:linear-gradient(135deg,rgba(67,82,225,.30),rgba(99,102,241,.16));
    box-shadow:inset 0 0 0 1px rgba(129,140,248,.24);
  }
  .seller-profile-view .sp-nav-tabs a i {
    font-size:12px;
    color:#8ea5ff;
  }
  .seller-profile-view .sp-tab-count {
    padding:2px 7px;
    font-size:10px;
  }
  .seller-profile-view .sp-tab-layout {
    display:block;
  }
  .seller-profile-view .sp-sidebar-wrap {
    display:none !important;
  }
  .seller-profile-view .tab-pane.active {
    padding:24px;
    border:1px solid rgba(129,140,248,.13);
    border-radius:20px;
    background:rgba(8,10,23,.68);
    box-shadow:0 22px 65px rgba(0,0,0,.22),inset 0 1px 0 rgba(255,255,255,.025);
  }
  .seller-profile-view .sp-section {
    margin-bottom:28px;
  }
  .seller-profile-view .sp-section:last-child {
    margin-bottom:0;
  }
  .seller-profile-view .sp-section-label {
    margin-bottom:15px;
    gap:9px;
    font-size:12px;
    letter-spacing:.13em;
  }
  .seller-profile-view .sp-section-label::before {
    width:3px;
    height:15px;
    border-radius:999px;
  }
  .seller-profile-view .sp-description-block {
    margin-bottom:18px;
    padding:18px 20px;
    border:1px solid rgba(255,255,255,.06);
    border-radius:15px;
    background:rgba(255,255,255,.02);
  }
  .seller-profile-view .sp-description-text {
    max-width:80ch;
    margin:0;
    font-size:14px;
    line-height:1.65;
  }
  .seller-profile-view .sp-stats-row {
    display:grid;
    grid-template-columns:repeat(2,minmax(0,1fr));
    gap:10px;
    margin-bottom:26px;
  }
  .seller-profile-view .sp-stat-card {
    min-width:0;
    min-height:76px;
    padding:14px 16px;
    border-radius:14px;
    background:linear-gradient(145deg,rgba(255,255,255,.038),rgba(255,255,255,.018));
    border-color:rgba(129,140,248,.12);
  }
  .seller-profile-view .sp-stat-card .sp-stat-icon {
    width:38px;
    height:38px;
    border-radius:10px;
    font-size:13px;
  }
  .seller-profile-view .sp-stat-card .sp-stat-val {
    font-size:20px !important;
  }
  .seller-profile-view .sp-stat-card .sp-stat-lbl {
    margin-top:4px;
    font-size:10px !important;
  }
  .seller-profile-view .sp-listings-filter {
    margin-bottom:14px;
  }
  .seller-profile-view .sp-filter-toolbar {
    margin-bottom:18px;
    padding:12px;
    border:1px solid rgba(129,140,248,.10);
    border-radius:14px;
    background:rgba(255,255,255,.018);
  }
  .seller-profile-view .sp-review-summary {
    gap:10px;
    margin-bottom:16px;
  }
  .seller-profile-view .sp-review-summary-card {
    min-width:180px;
    padding:15px 17px;
    border-radius:13px;
  }
  .seller-profile-view .sp-review-summary-value {
    font-size:24px;
  }
  .seller-profile-view .sp-review-summary-label {
    font-size:10px;
  }
  .seller-profile-view .sp-reviews-list {
    gap:9px;
  }
  .seller-profile-view .sp-review-card {
    padding:15px 17px;
    border-radius:14px;
    background:rgba(255,255,255,.022);
  }
  .seller-profile-view #tab-listings.sp-filter-all .sp-filter-toolbar,
  .seller-profile-view #tab-listings.sp-filter-all .sp-pagination {
    display:none !important;
  }
  .seller-profile-view #tab-listings.sp-filter-all .sp-listing-section {
    display:block !important;
    padding-top:18px;
    margin-top:18px;
    border-top:1px solid rgba(255,255,255,.055);
  }
  .seller-profile-view #tab-listings.sp-filter-all .sp-accounts-grid {
    display:flex;
    gap:12px;
    overflow-x:auto;
    padding:2px 2px 10px;
    scroll-snap-type:x mandatory;
    scrollbar-width:none;
  }
  .seller-profile-view #tab-listings.sp-filter-all .sp-accounts-grid::-webkit-scrollbar {
    display:none;
  }
  .seller-profile-view #tab-listings.sp-filter-all .sp-account-card {
    flex:0 0 calc((100% - 36px)/4);
    min-width:250px;
    scroll-snap-align:start;
  }
  .seller-profile-view .sp-row-controls {
    display:none;
    align-items:center;
    gap:6px;
    margin-left:auto;
  }
  .seller-profile-view #tab-listings.sp-filter-all .sp-row-controls {
    display:inline-flex;
  }
  .seller-profile-view .sp-row-controls button {
    width:30px;
    height:30px;
    display:grid;
    place-items:center;
    border:1px solid rgba(129,140,248,.18);
    border-radius:9px;
    background:rgba(255,255,255,.035);
    color:#aeb7ff;
    cursor:pointer;
  }
  .seller-profile-view .sp-row-controls button:hover {
    color:#fff;
    border-color:rgba(129,140,248,.4);
    background:rgba(99,102,241,.14);
  }
}

@media (min-width:901px) and (max-width:1279px) {
  .seller-profile-view .sp-stats-row {
    grid-template-columns:repeat(2,minmax(0,1fr));
  }
}

.seller-profile-view .sp-account-card .sp-card-meta,
.seller-profile-view .sp-account-card .sp-card-highlights {
  display:none !important;
}
.seller-profile-view .sp-card-game {
  display:flex;
  align-items:center;
  gap:8px;
  min-width:0;
  margin-bottom:9px;
  color:rgba(206,211,234,.72);
  font-size:11px;
  font-weight:750;
  line-height:1.2;
}
.seller-profile-view .sp-card-game img {
  width:18px;
  height:18px;
  flex:0 0 18px;
  object-fit:contain;
}
.seller-profile-view .sp-card-game i {
  width:18px;
  color:#8ea5ff;
  text-align:center;
}
.seller-profile-view .sp-filter-pill {
  gap:9px;
}
.seller-profile-view .sp-section-label {
  gap:12px;
}

@media (max-width:900px) {
  html,
  body.seller-profile-view {
    width:100%;
    max-width:100%;
    overflow-x:hidden !important;
    overscroll-behavior-x:none;
  }
  .seller-profile-view .sp-details,
  .seller-profile-view .sp-tab-layout,
  .seller-profile-view .tab-pane,
  .seller-profile-view .sp-listing-section {
    box-sizing:border-box;
    width:100%;
    min-width:0;
    max-width:100%;
    overflow-x:hidden;
  }
  .seller-profile-view .tab-pane > *,
  .seller-profile-view .sp-listing-section > * {
    box-sizing:border-box;
    min-width:0;
    max-width:100%;
  }
  .seller-profile-view .sp-account-card,
  .seller-profile-view .sp-card-body,
  .seller-profile-view .sp-card-bottom {
    box-sizing:border-box;
    min-width:0;
    max-width:100%;
  }
  .seller-profile-view .sp-account-card {
    overflow:hidden;
  }
  .seller-profile-view .sp-card-game,
  .seller-profile-view .sp-card-title {
    max-width:100%;
    overflow:hidden;
  }
  .seller-profile-view .sp-hero {
    width:calc(100% - 24px);
    margin:calc(var(--lb-content-top,118px) + 12px) 12px 0;
    border:1px solid rgba(129,140,248,.14);
    border-radius:16px;
    overflow:hidden;
  }
  .seller-profile-view .sp-hero__banner-img {
    height:118px;
  }
  .seller-profile-view .sp-hero__content {
    padding:14px;
  }
  .seller-profile-view .sp-top-flags {
    margin:0;
    padding:4px 8px;
    gap:5px;
    border:1px solid rgba(129,140,248,.20);
    border-radius:999px;
    background:rgba(10,12,27,.72);
  }
  .seller-profile-view .sp-top-flag {width:18px;height:18px;margin:0;}
  .seller-profile-view .sp-hero__chips {
    display:flex;
    flex-wrap:nowrap;
    align-items:center;
    gap:6px;
    width:100%;
    overflow-x:auto;
    padding:2px 0 4px;
    scrollbar-width:none;
    -webkit-overflow-scrolling:touch;
  }
  .seller-profile-view .sp-hero__chips::-webkit-scrollbar {
    display:none;
  }
  .seller-profile-view .sp-hero__chips > * {
    flex:0 0 auto;
    white-space:nowrap;
  }
  .seller-profile-view .main-content {
    width:calc(100% - 24px);
    margin:12px !important;
  }
  .seller-profile-view .sp-nav-tabs {
    display:grid;
    grid-template-columns:repeat(3,minmax(0,1fr));
    width:100%;
    gap:5px;
    margin-bottom:12px;
    padding:5px;
    border:1px solid rgba(129,140,248,.12);
    border-radius:13px;
    background:rgba(10,12,27,.86);
  }
  .seller-profile-view .sp-nav-tabs a {
    width:100%;
    min-width:0;
    justify-content:center;
    gap:6px;
    padding:9px 6px;
    border:0;
    border-radius:9px;
  }
  .seller-profile-view .sp-listings-filter {
    box-sizing:border-box;
    display:flex;
    flex-wrap:nowrap;
    align-items:center;
    gap:7px;
    width:100%;
    min-width:0;
    max-width:100%;
    overflow-x:auto;
    overflow-y:hidden;
    contain:inline-size;
    padding:1px 0 6px;
    scrollbar-width:none;
    -webkit-overflow-scrolling:touch;
    overscroll-behavior-x:contain;
    scroll-snap-type:x proximity;
    scroll-padding-inline:0;
    touch-action:pan-y;
    cursor:grab;
    user-select:none;
  }
  .seller-profile-view .sp-listings-filter.is-dragging {
    cursor:grabbing;
    scroll-snap-type:none;
  }
  .seller-profile-view .sp-listings-filter::-webkit-scrollbar {
    display:none;
  }
  .seller-profile-view .sp-filter-pill {
    flex:0 0 auto;
    width:auto;
    min-width:0;
    max-width:none;
    min-height:34px;
    justify-content:center;
    gap:7px;
    margin:0 !important;
    padding:7px 11px;
    border-radius:999px;
    font-size:11px !important;
    line-height:1;
    white-space:nowrap;
    scroll-snap-align:start;
    scroll-snap-stop:always;
    touch-action:pan-y;
  }
  .seller-profile-view .sp-filter-pill i {
    flex:0 0 auto;
    font-size:11px;
  }
  .seller-profile-view .sp-filter-pill .sp-tab-count {
    flex:0 0 auto;
  }
  .seller-profile-view .sp-section-label {
    gap:13px !important;
    padding-left:1px;
  }
  .seller-profile-view .sp-section-label::before {
    margin-right:1px;
  }
  .seller-profile-view .sp-filter-toolbar {
    display:flex;
    flex-wrap:wrap;
    width:100%;
    max-width:100%;
    overflow:visible;
  }
  .seller-profile-view .sp-filter-search {
    box-sizing:border-box;
    flex:1 1 100%;
    width:100%;
    max-width:100%;
  }
  .seller-profile-view .sp-filterpill {
    max-width:calc(50% - 4px);
  }
  .seller-profile-view .sp-nav-tabs a.active {
    border:0;
    background:rgba(99,102,241,.15);
  }
  .seller-profile-view .tab-pane.active {
    padding:14px;
    border:1px solid rgba(129,140,248,.10);
    border-radius:15px;
    background:rgba(8,10,23,.62);
  }
  .seller-profile-view #tab-listings.sp-filter-all .sp-filter-toolbar,
  .seller-profile-view #tab-listings.sp-filter-all .sp-pagination {
    display:none !important;
  }
  .seller-profile-view #tab-listings.sp-filter-all .sp-listing-section {
    display:block !important;
    margin-top:16px;
    padding-top:16px;
    border-top:1px solid rgba(255,255,255,.055);
  }
  .seller-profile-view #tab-listings.sp-filter-all .sp-accounts-grid {
    display:flex;
    gap:10px;
    width:100%;
    max-width:100%;
    overflow-x:auto;
    overflow-y:hidden;
    padding-bottom:8px;
    scroll-snap-type:x mandatory;
    scroll-padding-inline:0;
    overscroll-behavior-x:contain;
    -webkit-overflow-scrolling:touch;
    touch-action:pan-y;
    cursor:grab;
    align-items:flex-start;
  }
  .seller-profile-view #tab-listings.sp-filter-all .sp-accounts-grid.is-swiping {
    cursor:grabbing;
    scroll-snap-type:none;
  }
  .seller-profile-view #tab-listings.sp-filter-all .sp-accounts-grid::-webkit-scrollbar {
    display:none;
  }
  .seller-profile-view #tab-listings.sp-filter-all .sp-account-card {
    box-sizing:border-box;
    flex:0 0 min(74vw,280px);
    width:min(74vw,280px);
    max-width:min(74vw,280px);
    min-width:0;
    scroll-snap-align:start;
    scroll-snap-stop:always;
    height:auto;
    align-self:flex-start;
  }
  .seller-profile-view #tab-listings.sp-filter-all .sp-card-img,
  .seller-profile-view #tab-listings.sp-filter-all .sp-card-img-empty {
    display:block;
    width:100%;
    height:clamp(115px,32vw,145px) !important;
    min-height:0;
    max-height:none;
    aspect-ratio:auto;
    object-fit:cover;
  }
  .seller-profile-view #tab-listings.sp-filter-all .sp-card-body {
    min-height:96px;
    padding:8px 10px 10px;
  }
  .seller-profile-view #tab-listings.sp-filter-all .sp-row-controls {
    display:inline-flex !important;
    margin-left:auto;
    gap:6px;
    flex:0 0 auto;
    flex-wrap:nowrap;
  }
  .seller-profile-view #tab-listings:not(.sp-filter-all) .sp-row-controls {
    display:none !important;
  }
  .seller-profile-view #tab-listings:not(.sp-filter-all) .sp-accounts-grid {
    display:grid !important;
    grid-template-columns:minmax(0,1fr);
    gap:10px;
    width:100%;
    max-width:100%;
    overflow:visible;
    padding:0;
    scroll-snap-type:none;
    touch-action:auto;
  }
  .seller-profile-view #tab-listings:not(.sp-filter-all) .sp-account-card {
    box-sizing:border-box;
    display:flex;
    flex-direction:column;
    flex:none;
    width:100%;
    max-width:100%;
    min-width:0;
    min-height:0;
    max-height:none;
    border-radius:12px;
    overflow:hidden;
  }
  .seller-profile-view #tab-listings:not(.sp-filter-all) .sp-card-img,
  .seller-profile-view #tab-listings:not(.sp-filter-all) .sp-card-img-empty {
    display:block;
    width:100%;
    height:clamp(155px,44vw,190px) !important;
    min-height:0;
    max-height:none;
    aspect-ratio:auto;
    object-fit:cover;
  }
  .seller-profile-view #tab-listings:not(.sp-filter-all) .sp-card-body {
    box-sizing:border-box;
    width:100%;
    min-width:0;
    min-height:104px;
    padding:10px 12px 12px;
    gap:6px;
    overflow:hidden;
  }
  .seller-profile-view #tab-listings:not(.sp-filter-all) .sp-card-game {
    gap:5px;
    margin-bottom:2px;
    font-size:9px;
  }
  .seller-profile-view #tab-listings:not(.sp-filter-all) .sp-card-game img {
    width:14px;
    height:14px;
    flex-basis:14px;
  }
  .seller-profile-view #tab-listings:not(.sp-filter-all) .sp-card-title {
    min-height:1.35em;
    font-size:13px;
    line-height:1.35;
    -webkit-line-clamp:2;
  }
  .seller-profile-view #tab-listings:not(.sp-filter-all) .sp-card-bottom {
    box-sizing:border-box;
    width:100%;
    min-width:0;
    gap:5px;
    padding-top:5px;
  }
  .seller-profile-view #tab-listings:not(.sp-filter-all) .sp-card-price {
    font-size:18px;
  }
  .seller-profile-view #tab-listings:not(.sp-filter-all) .sp-card-buy {
    flex:0 1 auto;
    min-width:0;
    gap:4px;
    padding:6px 8px;
    font-size:9px;
    white-space:nowrap;
  }
  .seller-profile-view #tab-listings:not(.sp-filter-all) .sp-card-delivery-badge {
    top:7px;
    right:7px;
    padding:4px 7px;
    font-size:8px;
  }
  .seller-profile-view #tab-listings:not(.sp-filter-all) .sp-pagination {
    display:flex !important;
  }
  .seller-profile-view .sp-row-controls button {
    width:30px;
    height:30px;
    min-width:30px;
    flex:0 0 30px;
    padding:0;
    display:grid;
    place-items:center;
    border-radius:50%;
    border:1px solid rgba(139,148,255,.34);
    background:linear-gradient(145deg,rgba(50,55,104,.92),rgba(21,24,50,.96));
    color:#cbd2ff;
    box-shadow:inset 0 1px 0 rgba(255,255,255,.08),0 4px 12px rgba(0,0,0,.22);
  }
  .seller-profile-view .sp-row-controls button i {
    display:block;
    margin:0;
    font-size:11px;
    line-height:1;
  }
  .seller-profile-view .sp-row-controls button:active {
    color:#fff;
    border-color:rgba(159,168,255,.6);
    background:linear-gradient(145deg,rgba(99,102,241,.52),rgba(49,46,129,.68));
    transform:scale(.96);
  }

  /* Keep every mobile listing card identical to the Latest Listings card. */
  .seller-profile-view .sp-account-card {
    border-radius:10px;
  }
  .seller-profile-view .sp-card-img,
  .seller-profile-view .sp-card-img-empty,
  .seller-profile-view #tab-listings.sp-filter-all .sp-card-img,
  .seller-profile-view #tab-listings.sp-filter-all .sp-card-img-empty,
  .seller-profile-view #tab-listings:not(.sp-filter-all) .sp-card-img,
  .seller-profile-view #tab-listings:not(.sp-filter-all) .sp-card-img-empty {
    display:block;
    width:100%;
    height:clamp(115px,32vw,145px) !important;
    min-height:0;
    max-height:145px;
    aspect-ratio:auto;
    object-fit:cover;
  }
  .seller-profile-view .sp-card-img--product,
  .seller-profile-view #tab-listings.sp-filter-all .sp-card-img--product,
  .seller-profile-view #tab-listings:not(.sp-filter-all) .sp-card-img--product {
    object-fit:contain;
    padding:10px 16px;
  }
  .seller-profile-view .sp-card-img-empty,
  .seller-profile-view #tab-listings.sp-filter-all .sp-card-img-empty,
  .seller-profile-view #tab-listings:not(.sp-filter-all) .sp-card-img-empty {
    display:flex;
    align-items:center;
    justify-content:center;
  }
  .seller-profile-view #spListingDigital .sp-card-img-empty i {
    margin:0;
    font-size:34px;
    line-height:1;
  }
  .seller-profile-view .sp-card-body,
  .seller-profile-view #tab-listings.sp-filter-all .sp-card-body,
  .seller-profile-view #tab-listings:not(.sp-filter-all) .sp-card-body {
    min-height:96px;
    padding:8px 10px 10px;
    gap:5px;
  }
  .seller-profile-view .sp-card-title,
  .seller-profile-view #tab-listings:not(.sp-filter-all) .sp-card-title {
    min-height:auto;
    font-size:.85rem !important;
    line-height:1.35;
  }
  .seller-profile-view .sp-card-price,
  .seller-profile-view #tab-listings:not(.sp-filter-all) .sp-card-price {
    font-size:1.15rem !important;
  }
  .seller-profile-view .sp-card-buy,
  .seller-profile-view #tab-listings:not(.sp-filter-all) .sp-card-buy {
    flex:0 0 auto;
    font-size:.68rem !important;
    gap:4px;
    padding:5px 10px;
    border-radius:999px;
  }
}

@media (max-width:480px) {
  .seller-profile-view .sp-nav-tabs a {
    font-size:11px;
  }
  .seller-profile-view .sp-nav-tabs a i {
    display:none;
  }
  .seller-profile-view .sp-tab-count {
    padding:2px 5px;
  }
  .seller-profile-view .sp-filter-pill {
    font-size:11px;
  }
}
</style>

<?php
if (!function_exists('seller_lol_rank_hides_division')) {
    function seller_lol_rank_hides_division($rank): bool
    {
        return in_array((int)$rank, [0, 8, 9, 10], true);
    }
}

if (!function_exists('seller_lol_rank_display_text')) {
    function seller_lol_rank_display_text($rank, $division, $lp): string
    {
        $label = util_get_lol_rank($rank);
        $lp = is_null($lp) ? null : (int)$lp;

        if ($lp !== null && $lp !== 0) {
            return $label . ' ' . $lp . 'LP';
        }

        if (seller_lol_rank_hides_division($rank)) {
            return $label;
        }

        return $label . ' ' . util_format_lol_division($division);
    }
}

if (!function_exists('seller_profile_game_key')) {
    function seller_profile_game_key($game): string
    {
        $game = strtolower(trim((string)$game));
        $game = str_replace('_', '-', $game);
        $game = preg_replace('/\s+/', '-', $game) ?: $game;
        if ($game === '') return 'lol';
        // Listings store the same game under several slugs — collapse them so the
        // filter never lists "lol" and "league-of-legends" as two separate games.
        $aliases = [
            'league' => 'lol', 'league-of-legends' => 'lol', 'leagueoflegends' => 'lol', 'leagu' => 'lol',
            'valorant' => 'val',
            'teamfight-tactics' => 'tft', 'teamfighttactics' => 'tft',
            'wildrift' => 'wild-rift', 'lol-wild-rift' => 'wild-rift',
            'overwatch' => 'ow2', 'overwatch-2' => 'ow2',
            'rocket-league' => 'rl',
            'apex-legends' => 'apex',
            'marvel-rivals' => 'rivals',
        ];
        return $aliases[$game] ?? $game;
    }
}

if (!function_exists('seller_profile_game_label')) {
    function seller_profile_game_label(string $game): string
    {
        $game = seller_profile_game_key($game);
        if (function_exists('util_game_display_name')) {
            return util_game_display_name($game);
        }

        return ucwords(str_replace('-', ' ', $game));
    }
}

if (!function_exists('seller_profile_game_icon')) {
    function seller_profile_game_icon(string $game): string
    {
        $game = seller_profile_game_key($game);
        if (function_exists('util_game_icon_url')) {
            $resolvedIcon = trim((string)util_game_icon_url($game));
            if ($resolvedIcon !== '') {
                return $resolvedIcon;
            }
        }

        // Dynamic games normally resolve through the Games admin data. Keep a
        // slug-based asset fallback for older listings that predate that link.
        return ASSET_URL . '/website/images/icons/' . rawurlencode($game) . '.png';
    }
}

if (!function_exists('seller_profile_account_url')) {
    function seller_profile_account_url(string $game, string $slug): string
    {
        $game = seller_profile_game_key($game);
        if (in_array($game, ['lol', 'league', 'league-of-legends'], true)) {
            return '/lol/account/' . rawurlencode($slug);
        }
        if (in_array($game, ['val', 'valorant'], true)) {
            return '/val/account/' . rawurlencode($slug);
        }
        return '/' . rawurlencode($game) . '/account/' . rawurlencode($slug);
    }
}

if (!function_exists('seller_profile_item_url')) {
    function seller_profile_item_url(string $game, string $slug): string
    {
        return '/' . rawurlencode(seller_profile_game_key($game)) . '/item/' . rawurlencode($slug);
    }
}

$seller     = $seller ?? [];
$accounts   = $accounts ?? [];
$items      = $items ?? [];
$total_sold = (int)($seller['seller_total_sales'] ?? $seller['seller_account_sales'] ?? $total_sold ?? 0);
if (!empty($seller['id']) && function_exists('get_seller_total_sales')) {
    try { $total_sold = (int)get_seller_total_sales((int)$seller['id']); } catch (Throwable $e) {}
}
$reviews = $reviews ?? [];
$avg_rating = (float)($avg_rating ?? 0);
$review_count = (int)($review_count ?? 0);
$seller_is_online = !empty($seller_is_online) || !empty($seller['is_online']);
$reviews_page = (int)($reviews_page ?? 1);
$reviews_total_pages = (int)($reviews_total_pages ?? 1);

$banner   = trim((string)($seller['banner'] ?? ''));
$bannerPosition = trim((string)($seller['banner_position'] ?? ''));
if ($bannerPosition === '') {
    $bannerPosition = 'center center';
}
$icon     = trim((string)($seller['icon'] ?? ''));
$username = esc($seller['username'] ?? 'Seller');
$rank     = trim((string)($seller['rank'] ?? ''));

$active_listings = array_filter($accounts, fn($a) => !(int)($a['sold'] ?? 0));
$active_listings = array_values($active_listings);
$topups = array_values($topups ?? []);
$digital_goods = array_values($digital_goods ?? []);
$seller_listing_total = count($active_listings) + count($items) + count($topups) + count($digital_goods);

$currency = $_SESSION['currency'] ?? 'EUR';
$symbol   = util_format_currency_display($currency);

if (!function_exists('seller_profile_language_map')) {
    function seller_profile_language_map(): array
    {
        return [
            'en' => 'English',
            'de' => 'Deutsch',
            'fr' => 'Français',
            'es' => 'Español',
            'pt' => 'Português',
            'it' => 'Italiano',
            'nl' => 'Nederlands',
            'pl' => 'Polski',
            'ru' => 'Русский',
            'jp' => '日本語',
            'zh' => '中文',
            'sv' => 'Svenska',
            'no' => 'Norsk',
            'da' => 'Dansk',
            'fi' => 'Suomi',
            'el' => 'Ελληνικά',
            'hu' => 'Magyar',
            'cs' => 'Čeština',
            'bg' => 'Български',
            'ro' => 'Română',
            'tr' => 'Türkçe',
            'hr' => 'Hrvatski',
            'ar' => 'العربية',
            'fili' => 'Filipino',
        ];
    }
}

if (!function_exists('seller_profile_language_flag_url')) {
    function seller_profile_language_flag_url(string $code): string
    {
        $flagMap = [
            'el' => 'gr',
            'cs' => 'cz',
            'zh' => 'ch',
        ];

        $fileCode = $flagMap[$code] ?? $code;
        $flagUrlBase = ASSET_URL . '/core/main/img/flags/';
        $flagDiskBase = rtrim($_SERVER['DOCUMENT_ROOT'], '/') . '/public/assets/core/main/img/flags/';

        if (is_file($flagDiskBase . $fileCode . '.webp')) {
            return $flagUrlBase . $fileCode . '.webp';
        }

        if (is_file($flagDiskBase . $fileCode . '.png')) {
            return $flagUrlBase . $fileCode . '.png';
        }

        return '';
    }
}

if (!function_exists('seller_profile_normalize_languages')) {
    function seller_profile_normalize_languages($raw): array
    {
        if (is_string($raw) && $raw !== '') {
            $decoded = json_decode($raw, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                $raw = $decoded;
            } elseif (strpos($raw, '|') !== false) {
                $raw = explode('|', $raw);
            } elseif (strpos($raw, ',') !== false) {
                $raw = explode(',', $raw);
            } else {
                $raw = [$raw];
            }
        }

        if (!is_array($raw)) {
            $raw = [];
        }

        $labels = seller_profile_language_map();
        $result = [];

        foreach ($raw as $entry) {
            $code = trim((string)$entry);
            if ($code === '') {
                continue;
            }

            if (isset($labels[$code])) {
                $result[] = [
                    'code' => $code,
                    'label' => $labels[$code],
                    'flag' => seller_profile_language_flag_url($code),
                ];
                continue;
            }

            $foundCode = array_search($code, $labels, true);
            if ($foundCode !== false) {
                $result[] = [
                    'code' => $foundCode,
                    'label' => $labels[$foundCode],
                    'flag' => seller_profile_language_flag_url($foundCode),
                ];
            }
        }

        $unique = [];
        $seen = [];
        foreach ($result as $item) {
            if (isset($seen[$item['code']])) {
                continue;
            }
            $seen[$item['code']] = true;
            $unique[] = $item;
        }

        return $unique;
    }
}

$seller_db_languages = $seller['languages'] ?? [];
$seller_db_description = $seller['description'] ?? '';

if (!empty($seller['id'])) {
    global $db;
    $seller_profile_row = $db->row(
        "SELECT languages, description FROM sellers WHERE id = ? LIMIT 1",
        (int)$seller['id']
    );

    if ($seller_profile_row) {
        $seller_db_languages = $seller_profile_row['languages'] ?? $seller_db_languages;
        $seller_db_description = $seller_profile_row['description'] ?? $seller_db_description;
    }
}

$seller_languages = seller_profile_normalize_languages($seller_db_languages);
$seller_description = trim((string)$seller_db_description);
$seller_languages_visible = array_slice($seller_languages, 0, 5);
$seller_languages_hidden = count($seller_languages) > 5 ? array_slice($seller_languages, 5) : [];
$seller_languages_hidden_count = count($seller_languages_hidden);
$seller_languages_tooltip = '';
if ($seller_languages_hidden_count > 0) {
    $seller_languages_tooltip = implode(', ', array_map(function ($language) {
        return $language['label'];
    }, $seller_languages_hidden));
}
?>

<div class="sp-hero">
  <?php if ($banner !== ''): ?>
    <img class="sp-hero__banner-img" src="<?= esc($banner) ?>" style="object-position: <?= esc($bannerPosition) ?>;" alt="<?= $username ?> banner">
  <?php endif; ?>
  <div class="sp-hero__scrim"></div>
  <div class="sp-hero__content">

    <?php if ($icon !== ''): ?>
      <div class="sp-avatar-wrap">
        <img src="<?= esc($icon) ?>" alt="<?= $username ?>">
        <?php if ($seller_is_online): ?><span class="sp-online-dot" title="Online now"></span><?php endif; ?>
      </div>
    <?php else: ?>
      <div class="sp-avatar-ph">
        <?= strtoupper(substr($username, 0, 2)) ?>
        <?php if ($seller_is_online): ?><span class="sp-online-dot" title="Online now"></span><?php endif; ?>
      </div>
    <?php endif; ?>

    <div class="sp-hero__info">
      <h1 class="sp-hero__name">
        <?= $username ?>
        <?php if (!empty($seller['is_active'])): ?>
          <i class="fa-solid fa-badge-check sp-check"></i>
        <?php endif; ?>
      </h1>
      <div class="sp-hero__chips">

        <?php if ($rank !== ''): ?>
          <span class="sp-chip rank" style="color:<?= esc($rankMeta['color']) ?>;background:<?= esc($rankMeta['bg']) ?>;border-color:<?= esc($rankMeta['border']) ?>;">
            <i class="fa-solid <?= esc($rankMeta['icon']) ?>" style="font-size:.8em;"></i>
            <?= esc($rank) ?>
          </span>
        <?php endif; ?>

        <span class="sp-chip <?= $seller_is_online ? 'online' : 'offline' ?>">
          <span class="sp-presence-pulse" aria-hidden="true"></span>
          <?= $seller_is_online ? 'Online' : 'Offline' ?>
        </span>
        <?php if ($avg_rating > 0): ?>
          <span class="sp-chip sp-rating-chip" style="background:rgba(251,191,36,.08);border-color:rgba(251,191,36,.3);color:#fbbf24;gap:.35vw;">
            <i class="fa-solid fa-star" style="font-size:.8em;"></i>
            <?= number_format($avg_rating, 1) ?>
            <span style="color:rgba(255,255,255,.38);font-weight:600;">(<?= $review_count ?>)</span>
          </span>
        <?php endif; ?>
        <?php if (!empty($seller_languages)): ?>
          <span class="sp-top-flags" title="Languages">
            <?php foreach ($seller_languages_visible as $language): ?>
              <?php if (!empty($language['flag'])): ?>
                <img
                  class="sp-top-flag"
                  src="<?= esc($language['flag']) ?>"
                  alt="<?= esc($language['label']) ?>"
                  title="<?= esc($language['label']) ?>">
              <?php endif; ?>
            <?php endforeach; ?>

            <?php if ($seller_languages_hidden_count > 0): ?>
              <span class="sp-top-more-wrapper">
                <span class="sp-top-more">+<?= (int)$seller_languages_hidden_count ?></span>
                <div class="sp-top-more-popup">
                  <?php foreach ($seller_languages_hidden as $language): ?>
                    <?php if (!empty($language['flag'])): ?>
                      <img
                        class="sp-top-flag"
                        src="<?= esc($language['flag']) ?>"
                        alt="<?= esc($language['label']) ?>"
                        title="<?= esc($language['label']) ?>">
                    <?php endif; ?>
                  <?php endforeach; ?>
                </div>
              </span>
            <?php endif; ?>
          </span>
        <?php endif; ?>
      </div>
    </div>

  </div>
</div>

<div class="main-content">

  <div class="sp-details">

    <div class="sp-nav-tabs" id="spNavTabs">
      <a href="#tab-overview" class="active" data-tab="tab-overview">
        <i class="fa-solid fa-gauge-high"></i> Overview
      </a>

      <a href="#tab-listings" data-tab="tab-listings">
        <i class="fa-solid fa-layer-group"></i> Listings
        <span class="sp-tab-count"><?= $seller_listing_total ?></span>
      </a>

      <a href="#tab-reviews" data-tab="tab-reviews">
        <i class="fa-solid fa-star"></i> Reviews
        <span class="sp-tab-count"><?= $reviews_total ?></span>
      </a>
    </div>

    <div class="sp-tab-layout">

      <div>

        <div class="tab-pane active" id="tab-overview">
          <?php if ($seller_description !== ''): ?>
          <div class="sp-description-block">
            <div class="sp-section-label">About this seller</div>
            <div class="sp-description-text"><?= nl2br(esc($seller_description)) ?></div>
          </div>
          <?php endif; ?>

          <div class="sp-stats-row">
            <div class="sp-stat-card">
              <div class="sp-stat-icon"><i class="fa-solid fa-check-circle"></i></div>
              <div>
                <div class="sp-stat-val"><?= $total_sold ?></div>
                <div class="sp-stat-lbl">Total Sales</div>
              </div>
            </div>

            <div class="sp-stat-card">
              <div class="sp-stat-icon"><i class="fa-solid fa-layer-group"></i></div>
              <div>
                <div class="sp-stat-val"><?= count($active_listings) ?></div>
                <div class="sp-stat-lbl">Active Listings</div>
              </div>
            </div>

          </div>

          <?php if (!empty($active_listings)): ?>
          <div class="sp-section">
            <div class="sp-section-label">Latest Listings</div>
            <div class="sp-accounts-grid">
              <?php foreach (array_slice($active_listings, 0, 3) as $account): ?>
                <?php
                  $images = json_decode($account['images'] ?? '[]', true);
                  if (!is_array($images)) $images = [];
                  $firstImage = !empty($images) ? $images[0] : '';
                  $priceDisplay = $symbol . util_format_price_display($account['price'] ?? 0);

                  $latestGame = seller_profile_game_key($account['game'] ?? 'lol');
                  $latestUrl = seller_profile_account_url($latestGame, (string)($account['slug'] ?? ''));
                ?>
                <a class="sp-account-card" href="<?= esc($latestUrl) ?>">
                  <?php if ($firstImage): ?>
                    <img class="sp-card-img" src="<?= esc($firstImage) ?>" alt="<?= esc($account['title'] ?? 'Account') ?>">
                  <?php else: ?>
                    <div class="sp-card-img-empty"><i class="fa-solid fa-shield-halved"></i></div>
                  <?php endif; ?>

                  <?php if (($account['delivery_type'] ?? '') === 'instant'): ?>
                    <span class="sp-card-delivery-badge"><i class="fa-solid fa-bolt"></i> Instant</span>
                  <?php endif; ?>

                  <div class="sp-card-body">
                    <div class="sp-card-game">
                      <img src="<?= esc(seller_profile_game_icon($latestGame)) ?>" alt="">
                      <span><?= esc(seller_profile_game_label($latestGame)) ?></span>
                    </div>
                    <div class="sp-card-title">
                      <?= esc($account['title'] ?? 'Account') ?>
                    </div>

                    <div class="sp-card-bottom">
                      <div class="sp-card-price"><?= $priceDisplay ?></div>
                      <div class="sp-card-buy">
                        View <i class="fa-solid fa-arrow-right"></i>
                      </div>
                    </div>
                  </div>
                </a>
              <?php endforeach; ?>
            </div>
            <?php if ($seller_listing_total > 3): ?>
              <a href="#tab-listings" class="sp-view-all-link" data-tab="tab-listings">
                View all <?= $seller_listing_total ?> listings <i class="fa-solid fa-arrow-right"></i>
              </a>
            <?php endif; ?>
          </div>
          <?php endif; ?>
        </div>

        <div class="tab-pane sp-filter-all" id="tab-listings">
          <?php if ($seller_listing_total === 0): ?>
            <div class="sp-empty">
              <i class="fa-solid fa-box-open"></i>
              No active listings at the moment.
            </div>
          <?php else: ?>

            <!-- ── Top category pills ── -->
            <div class="sp-listings-filter" aria-label="Listing categories">
              <button class="sp-filter-pill active" data-filter="all" onclick="spFilterListings('all',this)">
                All <span class="sp-tab-count"><?= $seller_listing_total ?></span>
              </button>
              <?php if (!empty($active_listings)): ?>
              <button class="sp-filter-pill" data-filter="accounts" onclick="spFilterListings('accounts',this)">
                <i class="fa-solid fa-shield-halved"></i> Accounts
                <span class="sp-tab-count"><?= count($active_listings) ?></span>
              </button>
              <?php endif; ?>
              <?php if (!empty($items)): ?>
              <button class="sp-filter-pill" data-filter="items" onclick="spFilterListings('items',this)">
                <i class="fa-solid fa-bag-shopping"></i> Items
                <span class="sp-tab-count"><?= count($items) ?></span>
              </button>
              <?php endif; ?>
              <?php if (!empty($topups)): ?>
              <button class="sp-filter-pill" data-filter="topups" onclick="spFilterListings('topups',this)">
                <i class="fa-solid fa-coins"></i> Top-ups
                <span class="sp-tab-count"><?= count($topups) ?></span>
              </button>
              <?php endif; ?>
              <?php if (!empty($digital_goods)): ?>
              <button class="sp-filter-pill" data-filter="digital" onclick="spFilterListings('digital',this)">
                <i class="fa-solid fa-key"></i> Digital Goods
                <span class="sp-tab-count"><?= count($digital_goods) ?></span>
              </button>
              <?php endif; ?>
            </div>

            <!-- ══════════════════════════════════════════════ -->
            <!-- ACCOUNTS SECTION                               -->
            <!-- ══════════════════════════════════════════════ -->
            <?php if (!empty($active_listings)):
              // Collect unique servers for filter pills
              $acc_servers = array_values(array_unique(array_filter(array_map(fn($a) => strtoupper(trim($a['server'] ?? '')), $active_listings))));
              sort($acc_servers);
              // Collect unique games for filter pills
              $acc_games = [];
              $acc_game_counts = [];
              foreach ($active_listings as $listingGameRow) {
                $gameKey = seller_profile_game_key($listingGameRow['game'] ?? 'lol');
                $acc_games[$gameKey] = true;
                $acc_game_counts[$gameKey] = ($acc_game_counts[$gameKey] ?? 0) + 1;
              }
              $acc_games = array_keys($acc_games);
              usort($acc_games, function ($a, $b) {
                $order = ['lol' => 1, 'val' => 2, 'valorant' => 2, 'tft' => 3];
                return ($order[$a] ?? 99) <=> ($order[$b] ?? 99) ?: strcmp($a, $b);
              });
              // Collect price min/max — accounts prices are in cents, convert to EUR
              $acc_prices = array_map(fn($a) => round((int)($a['price'] ?? 0) / 100, 2), $active_listings);
              $acc_min = (int)floor(min($acc_prices));
              $acc_max = (int)ceil(max($acc_prices));
            ?>
            <div class="sp-listing-section" id="spListingAccounts">
              <div class="sp-section-label">Active Accounts</div>

              <!-- Filter toolbar -->
              <div class="sp-filter-toolbar" id="accFilterToolbar">
                <div class="sp-filter-search">
                  <i class="fa-solid fa-magnifying-glass"></i>
                  <input type="text" placeholder="Search accounts…" id="accSearch" oninput="spApplyAccFilters()">
                </div>

                <?php if (count($acc_games) > 1): ?>
                <!-- Game pill -->
                <div class="sp-filterpill" id="accGamePill">
                  <button class="sp-filterpill__btn" id="accGameBtn" onclick="spTogglePill('accGamePill')" aria-expanded="false">
                    <i class="fa-solid fa-gamepad"></i>
                    <span>Game</span>
                    <span class="sp-filterpill__value" id="accGameVal"></span>
                    <i class="fa-solid fa-caret-down sp-filterpill__caret"></i>
                  </button>
                  <div class="sp-dd" id="accGameDD">
                    <div class="sp-dd__head">
                      Game
                      <button class="sp-dd__close" onclick="spClosePill('accGamePill')">✕</button>
                    </div>
                    <div class="sp-dd__body">
                      <div class="sp-dd-item active" data-value="" onclick="spSetAccGame('',this)">
                        <i class="fa-solid fa-layer-group"></i> All Games
                      </div>
                      <?php foreach ($acc_games as $gameKey): ?>
                      <div class="sp-dd-item" data-value="<?= esc($gameKey) ?>" onclick="spSetAccGame('<?= esc($gameKey) ?>',this)">
                        <img src="<?= esc(seller_profile_game_icon($gameKey)) ?>" alt="" style="width:1.1vw;height:1.1vw;object-fit:contain;flex-shrink:0;">
                        <span class="sp-dd-item__label"><?= esc(seller_profile_game_label($gameKey)) ?></span>
                        <span class="sp-dd-item__badge"><?= (int)($acc_game_counts[$gameKey] ?? 0) ?></span>
                      </div>
                      <?php endforeach; ?>
                    </div>
                  </div>
                </div>
                <?php endif; ?>

                <!-- Price pill -->
                <div class="sp-filterpill" id="accPricePill">
                  <button class="sp-filterpill__btn" id="accPriceBtn" onclick="spTogglePill('accPricePill')" aria-expanded="false">
                    <i class="fa-solid fa-dollar-sign"></i>
                    <span>Price</span>
                    <span class="sp-filterpill__value" id="accPriceVal"></span>
                    <i class="fa-solid fa-caret-down sp-filterpill__caret"></i>
                  </button>
                  <div class="sp-dd" id="accPriceDD">
                    <div class="sp-dd__head">
                      Price
                      <button class="sp-dd__close" onclick="spClosePill('accPricePill')">✕</button>
                    </div>
                    <div class="sp-dd__body">
                      <div class="sp-price-wrap">
                        <div class="sp-price-fields">
                          <div class="sp-price-field">
                            <label>From</label>
                            <div class="sp-price-input">
                              <span class="sp-price-prefix"><?= $symbol ?></span>
                              <input type="number" id="accPriceMin" min="0" value="<?= $acc_min ?>" oninput="spSyncAccRange()">
                            </div>
                          </div>
                          <span class="sp-price-sep">–</span>
                          <div class="sp-price-field">
                            <label>To</label>
                            <div class="sp-price-input">
                              <span class="sp-price-prefix"><?= $symbol ?></span>
                              <input type="number" id="accPriceMax" min="0" value="<?= $acc_max ?>" oninput="spSyncAccRange()">
                            </div>
                          </div>
                        </div>
                        <div class="sp-range-wrap" id="accRangeWrap">
                          <input type="range" id="accRangeMin" min="<?= $acc_min ?>" max="<?= $acc_max ?>" value="<?= $acc_min ?>" step="0.01" oninput="spOnAccRange('min')">
                          <input type="range" id="accRangeMax" min="<?= $acc_min ?>" max="<?= $acc_max ?>" value="<?= $acc_max ?>" step="0.01" oninput="spOnAccRange('max')">
                          <div class="sp-range-track"><div class="sp-range-fill" id="accRangeFill"></div></div>
                        </div>
                        <div class="sp-price-labels">
                          <span id="accPriceLabelMin"><?= $symbol . $acc_min ?></span>
                          <span id="accPriceLabelMax"><?= $symbol . $acc_max ?></span>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>

                <?php if (count($acc_servers) > 1): ?>
                <!-- Server pill -->
                <div class="sp-filterpill" id="accServerPill">
                  <button class="sp-filterpill__btn" id="accServerBtn" onclick="spTogglePill('accServerPill')" aria-expanded="false">
                    <i class="fa-solid fa-globe"></i>
                    <span>Server</span>
                    <span class="sp-filterpill__value" id="accServerVal"></span>
                    <i class="fa-solid fa-caret-down sp-filterpill__caret"></i>
                  </button>
                  <div class="sp-dd" id="accServerDD">
                    <div class="sp-dd__head">
                      Server
                      <button class="sp-dd__close" onclick="spClosePill('accServerPill')">✕</button>
                    </div>
                    <div class="sp-dd__body">
                      <div class="sp-dd-item active" data-value="" onclick="spSetAccServer('',this)">
                        <i class="fa-solid fa-globe"></i> All Servers
                      </div>
                      <?php
                      $serverFlagMap = ['EUW'=>'gb','EUNE'=>'pl','NA'=>'us','BR'=>'br','LAN'=>'mx','LAS'=>'ar','OCE'=>'au','RU'=>'ru','TR'=>'tr','JP'=>'jp','KR'=>'kr'];
                      $serverIconMap = ['EUW'=>'fa-solid fa-globe','EUNE'=>'fa-solid fa-globe','NA'=>'fa-solid fa-flag-usa','BR'=>'fa-solid fa-globe','LAN'=>'fa-solid fa-globe','LAS'=>'fa-solid fa-globe','OCE'=>'fa-solid fa-globe','RU'=>'fa-solid fa-globe','TR'=>'fa-solid fa-globe','JP'=>'fa-solid fa-globe','KR'=>'fa-solid fa-globe'];
                      foreach ($acc_servers as $srv):
                        $sIcon = $serverIconMap[$srv] ?? 'fa-solid fa-server';
                      ?>
                      <div class="sp-dd-item" data-value="<?= esc($srv) ?>" onclick="spSetAccServer('<?= esc($srv) ?>',this)">
                        <i class="<?= $sIcon ?>"></i>
                        <?= esc($srv) ?>
                        <span class="sp-dd-item__badge"><?= esc($srv) ?></span>
                      </div>
                      <?php endforeach; ?>
                    </div>
                  </div>
                </div>
                <?php endif; ?>

                <button class="sp-filter-reset" id="accResetBtn" onclick="spResetAccFilters()" style="display:none;">
                  <i class="fa-solid fa-xmark"></i> Reset
                </button>
                <span class="sp-filter-results" id="accResults"></span>
              </div>

              <div class="sp-accounts-grid" id="spListingsGrid">
                <?php foreach ($active_listings as $i => $account):
                  $images = json_decode($account['images'] ?? '[]', true);
                  if (!is_array($images)) $images = [];
                  $firstImage = !empty($images) ? $images[0] : '';
                  $priceDisplay = $symbol . util_format_price_display($account['price'] ?? 0);
                  $accServerVal = strtoupper(trim($account['server'] ?? ''));
                  $accPriceVal  = round((int)($account['price'] ?? 0) / 100, 2);
                  $accTitleVal  = strtolower($account['title'] ?? '');
                  $accGameVal   = seller_profile_game_key($account['game'] ?? 'lol');
                  $accUrl       = seller_profile_account_url($accGameVal, (string)($account['slug'] ?? ''));
                ?>
                <a class="sp-account-card"
                   href="<?= esc($accUrl) ?>"
                   data-index="<?= $i ?>"
                   data-game="<?= esc($accGameVal) ?>"
                   data-server="<?= esc($accServerVal) ?>"
                   data-price="<?= $accPriceVal ?>"
                   data-title="<?= esc($accTitleVal) ?>">
                  <?php if ($firstImage): ?>
                    <img class="sp-card-img" src="<?= esc($firstImage) ?>" alt="<?= esc($account['title'] ?? 'Account') ?>">
                  <?php else: ?>
                    <div class="sp-card-img-empty"><i class="fa-solid fa-shield-halved"></i></div>
                  <?php endif; ?>
                  <?php if (($account['delivery_type'] ?? '') === 'instant'): ?>
                    <span class="sp-card-delivery-badge"><i class="fa-solid fa-bolt"></i> Instant</span>
                  <?php endif; ?>
                  <div class="sp-card-body">
                    <div class="sp-card-game">
                      <img src="<?= esc(seller_profile_game_icon($accGameVal)) ?>" alt="">
                      <span><?= esc(seller_profile_game_label($accGameVal)) ?></span>
                    </div>
                    <div class="sp-card-title"><?= esc($account['title'] ?? 'Account') ?></div>
                    <div class="sp-card-bottom">
                      <div class="sp-card-price"><?= $priceDisplay ?></div>
                      <div class="sp-card-buy">Buy Now <i class="fa-solid fa-arrow-right"></i></div>
                    </div>
                  </div>
                </a>
                <?php endforeach; ?>
              </div>
              <div class="sp-pagination" id="spPagination"></div>
            </div>
            <?php endif; ?>

            <!-- ══════════════════════════════════════════════ -->
            <!-- ITEMS SECTION                                  -->
            <!-- ══════════════════════════════════════════════ -->
            <?php if (!empty($items)):
              // Prices are stored in cents → convert to EUR for display & filtering
              $item_types = array_values(array_unique(array_filter(array_map(fn($it) => strtolower(trim($it['type'] ?? '')), $items))));
              sort($item_types);
              $item_prices_eur = array_map(fn($it) => round((int)($it['price'] ?? 0) / 100, 2), $items);
              $item_min = (int)floor(min($item_prices_eur));
              $item_max = (int)ceil(max($item_prices_eur));

              // Reuse helper functions from items view if available
              if (!function_exists('sp_type_key')) {
                function sp_type_key(string $type): string {
                  $map = [
                    'skins' => 'skins', 'skin' => 'skins',
                    'chests-keys' => 'chests-keys', 'chest-key' => 'chests-keys', 'chest' => 'chests-keys',
                    'orbs' => 'orbs', 'orb' => 'orbs',
                    'capsules' => 'capsules', 'capsule' => 'capsules',
                    'event-pass' => 'event-pass', 'event pass' => 'event-pass', 'pass' => 'event-pass',
                    'bundles' => 'bundles', 'bundle' => 'bundles',
                    'tft-item' => 'tft-item', 'tft item' => 'tft-item', 'tft' => 'tft-item',
                    'mystery-gift' => 'mystery-gift', 'mystery gift' => 'mystery-gift', 'gifting' => 'mystery-gift',
                  ];
                  $k = strtolower(trim($type));
                  // strip spaces/underscores for matching
                  return $map[$k] ?? $map[str_replace([' ','_'], '-', $k)] ?? $k;
                }
              }
              if (!function_exists('sp_type_label')) {
                function sp_type_label(string $type): string {
                  $labels = [
                    'skins' => 'Skins', 'chests-keys' => 'Chests & Keys', 'orbs' => 'Orbs',
                    'capsules' => 'Capsules', 'event-pass' => 'Event Pass', 'bundles' => 'Bundles',
                    'tft-item' => 'TFT Item', 'mystery-gift' => 'Mystery Gift',
                  ];
                  return $labels[sp_type_key($type)] ?? ucwords(str_replace(['-','_'], ' ', $type));
                }
              }
              if (!function_exists('sp_type_img')) {
                function sp_type_img(string $type): ?string {
                  $stems = [
                    'skins' => 'skins-item', 'chests-keys' => 'chest-item', 'orbs' => 'orbs-item',
                    'capsules' => 'capsules-item', 'event-pass' => 'event-pass-item',
                    'bundles' => 'bundle-item', 'tft-item' => 'tft-item', 'mystery-gift' => null,
                  ];
                  $key = sp_type_key($type);
                  if (!array_key_exists($key, $stems) || $stems[$key] === null) return null;
                  return rtrim(ASSET_URL, '/') . '/website/images/items/' . $stems[$key] . '.webp';
                }
              }
              if (!function_exists('sp_type_fa')) {
                function sp_type_fa(string $type): string {
                  $fa = [
                    'skins' => 'fa-solid fa-shirt', 'chests-keys' => 'fa-solid fa-key',
                    'orbs' => 'fa-solid fa-circle-nodes', 'capsules' => 'fa-solid fa-capsules',
                    'event-pass' => 'fa-solid fa-ticket', 'bundles' => 'fa-solid fa-gift',
                    'tft-item' => 'fa-solid fa-chess-board', 'mystery-gift' => 'fa-solid fa-sparkles',
                  ];
                  return $fa[sp_type_key($type)] ?? 'fa-solid fa-tag';
                }
              }
              // Games represented in this seller's items — same shape as the accounts filter.
              $item_games = [];
              foreach ($items as $itemGameRow) {
                $gameKey = seller_profile_game_key($itemGameRow['game_slug'] ?? $itemGameRow['game'] ?? 'lol');
                if (!isset($item_games[$gameKey])) {
                  $item_games[$gameKey] = [
                    'label' => trim((string)($itemGameRow['game_name'] ?? '')) ?: seller_profile_game_label($gameKey),
                    'icon'  => trim((string)($itemGameRow['game_icon'] ?? '')) ?: seller_profile_game_icon($gameKey),
                    'count' => 0,
                  ];
                }
                $item_games[$gameKey]['count']++;
              }
              uasort($item_games, fn($a, $b) => strcasecmp($a['label'], $b['label']));
            ?>
            <div class="sp-listing-section" id="spListingItems">
              <div class="sp-section-label">Items</div>

              <!-- Filter toolbar -->
              <div class="sp-filter-toolbar" id="itmFilterToolbar">
                <div class="sp-filter-search">
                  <i class="fa-solid fa-magnifying-glass"></i>
                  <input type="text" placeholder="Search items…" id="itmSearch" oninput="spApplyItmFilters()">
                </div>

                <?php if (count($item_games) > 1): ?>
                <!-- Game pill -->
                <div class="sp-filterpill" id="itmGamePill">
                  <button class="sp-filterpill__btn" id="itmGameBtn" onclick="spTogglePill('itmGamePill')" aria-expanded="false">
                    <i class="fa-solid fa-gamepad"></i>
                    <span>Game</span>
                    <span class="sp-filterpill__value" id="itmGameVal"></span>
                    <i class="fa-solid fa-caret-down sp-filterpill__caret"></i>
                  </button>
                  <div class="sp-dd" id="itmGameDD">
                    <div class="sp-dd__head">
                      Game
                      <button class="sp-dd__close" onclick="spClosePill('itmGamePill')">✕</button>
                    </div>
                    <div class="sp-dd__body">
                      <div class="sp-dd-item active" data-value="" onclick="spSetItmGame('',this)">
                        <i class="fa-solid fa-layer-group"></i> All Games
                      </div>
                      <?php foreach ($item_games as $gameKey => $gameMeta): ?>
                      <div class="sp-dd-item" data-value="<?= esc($gameKey) ?>" data-label="<?= esc($gameMeta['label']) ?>" onclick="spSetItmGame('<?= esc($gameKey) ?>',this)">
                        <img src="<?= esc($gameMeta['icon']) ?>" alt="" style="width:1.1vw;height:1.1vw;object-fit:contain;flex-shrink:0;">
                        <span class="sp-dd-item__label"><?= esc($gameMeta['label']) ?></span>
                        <span class="sp-dd-item__badge"><?= (int)$gameMeta['count'] ?></span>
                      </div>
                      <?php endforeach; ?>
                    </div>
                  </div>
                </div>
                <?php endif; ?>

                <!-- Price pill -->
                <div class="sp-filterpill" id="itmPricePill">
                  <button class="sp-filterpill__btn" id="itmPriceBtn" onclick="spTogglePill('itmPricePill')" aria-expanded="false">
                    <i class="fa-solid fa-dollar-sign"></i>
                    <span>Price</span>
                    <span class="sp-filterpill__value" id="itmPriceVal"></span>
                    <i class="fa-solid fa-caret-down sp-filterpill__caret"></i>
                  </button>
                  <div class="sp-dd" id="itmPriceDD">
                    <div class="sp-dd__head">
                      Price
                      <button class="sp-dd__close" onclick="spClosePill('itmPricePill')">✕</button>
                    </div>
                    <div class="sp-dd__body">
                      <div class="sp-price-wrap">
                        <div class="sp-price-fields">
                          <div class="sp-price-field">
                            <label>From</label>
                            <div class="sp-price-input">
                              <span class="sp-price-prefix"><?= $symbol ?></span>
                              <input type="number" id="itmPriceMin" min="0" value="<?= $item_min ?>" oninput="spSyncItmRange()">
                            </div>
                          </div>
                          <span class="sp-price-sep">–</span>
                          <div class="sp-price-field">
                            <label>To</label>
                            <div class="sp-price-input">
                              <span class="sp-price-prefix"><?= $symbol ?></span>
                              <input type="number" id="itmPriceMax" min="0" value="<?= $item_max ?>" oninput="spSyncItmRange()">
                            </div>
                          </div>
                        </div>
                        <div class="sp-range-wrap" id="itmRangeWrap">
                          <input type="range" id="itmRangeMin" min="<?= $item_min ?>" max="<?= $item_max ?>" value="<?= $item_min ?>" step="0.01" oninput="spOnItmRange('min')">
                          <input type="range" id="itmRangeMax" min="<?= $item_min ?>" max="<?= $item_max ?>" value="<?= $item_max ?>" step="0.01" oninput="spOnItmRange('max')">
                          <div class="sp-range-track"><div class="sp-range-fill" id="itmRangeFill"></div></div>
                        </div>
                        <div class="sp-price-labels">
                          <span id="itmPriceLabelMin"><?= $symbol . $item_min ?></span>
                          <span id="itmPriceLabelMax"><?= $symbol . $item_max ?></span>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>

                <?php if (count($item_types) > 1): ?>
                <!-- Type pill -->
                <div class="sp-filterpill" id="itmTypePill">
                  <button class="sp-filterpill__btn" id="itmTypeBtn" onclick="spTogglePill('itmTypePill')" aria-expanded="false">
                    <i class="fa-solid fa-gamepad"></i>
                    <span>Type</span>
                    <span class="sp-filterpill__value" id="itmTypeVal"></span>
                    <i class="fa-solid fa-caret-down sp-filterpill__caret"></i>
                  </button>
                  <div class="sp-dd" id="itmTypeDD">
                    <div class="sp-dd__head">
                      Type
                      <button class="sp-dd__close" onclick="spClosePill('itmTypePill')">✕</button>
                    </div>
                    <div class="sp-dd__body">
                      <div class="sp-dd-item active" data-value="" onclick="spSetItmType('',this)">
                        <i class="fa-solid fa-layer-group"></i> All Types
                      </div>
                      <?php foreach ($item_types as $itype):
                        $tKey   = sp_type_key($itype);
                        $tLabel = sp_type_label($itype);
                        $tImg   = sp_type_img($itype);
                        $tFa    = sp_type_fa($itype);
                        // filter data-value uses the raw type from DB (lowercase)
                      ?>
                      <div class="sp-dd-item" data-value="<?= esc($itype) ?>" onclick="spSetItmType('<?= esc($itype) ?>',this)">
                        <?php if ($tImg): ?>
                          <img src="<?= esc($tImg) ?>" alt="<?= esc($tLabel) ?>" style="width:1.2vw;height:1.2vw;object-fit:contain;flex-shrink:0;">
                        <?php else: ?>
                          <i class="<?= esc($tFa) ?>"></i>
                        <?php endif; ?>
                        <?= esc($tLabel) ?>
                      </div>
                      <?php endforeach; ?>
                    </div>
                  </div>
                </div>
                <?php endif; ?>

                <button class="sp-filter-reset" id="itmResetBtn" onclick="spResetItmFilters()" style="display:none;">
                  <i class="fa-solid fa-xmark"></i> Reset
                </button>
                <span class="sp-filter-results" id="itmResults"></span>
              </div>

              <div class="sp-accounts-grid" id="spItemsGrid">
                <?php foreach ($items as $idx => $item):
                  $itemImgs = json_decode($item['images'] ?? '[]', true);
                  if (!is_array($itemImgs)) $itemImgs = [];
                  $itemFirstImg = $itemImgs[0] ?? '';
                  // Price is stored in cents
                  $itemPriceCents   = (int)($item['price'] ?? 0);
                  $itemPriceEur     = $itemPriceCents / 100;
                  $itemPriceDisplay = $symbol . number_format($itemPriceEur, 2, '.', '');
                  $itemTypeRaw   = strtolower(trim($item['type'] ?? ''));
                  $itemTypeLabel = sp_type_label($itemTypeRaw);
                  $itemTypeImg   = sp_type_img($itemTypeRaw);
                  $itemTypeFa    = sp_type_fa($itemTypeRaw);
                  $itemServerVal = strtoupper(trim($item['server'] ?? ''));
                  $itemTitleVal  = strtolower($item['title'] ?? '');
                  $itemGameVal = seller_profile_game_key($item['game_slug'] ?? $item['game'] ?? 'lol');
                  $itemUrl = seller_profile_item_url($itemGameVal, (string)($item['slug'] ?? ''));
                ?>
                <a class="sp-item-card sp-account-card"
                   href="<?= esc($itemUrl) ?>"
                   data-item-index="<?= $idx ?>"
                   data-type="<?= esc($itemTypeRaw) ?>"
                   data-game="<?= esc($itemGameVal) ?>"
                   data-price="<?= $itemPriceEur ?>"
                   data-title="<?= esc($itemTitleVal) ?>">
                  <?php if ($itemFirstImg): ?>
                    <img class="sp-card-img" src="<?= esc($itemFirstImg) ?>" alt="<?= esc($item['title'] ?? 'Item') ?>">
                  <?php else: ?>
                    <div class="sp-card-img-empty"><i class="fa-solid fa-bag-shopping"></i></div>
                  <?php endif; ?>
                  <div class="sp-card-body">
                    <div class="sp-card-game">
                      <img src="<?= esc($item['game_icon'] ?: seller_profile_game_icon($itemGameVal)) ?>" alt="">
                      <span><?= esc($item['game_name'] ?: seller_profile_game_label($itemGameVal)) ?></span>
                    </div>
                    <div class="sp-card-title"><?= esc($item['title'] ?? 'Item') ?></div>
                    <div class="sp-card-bottom">
                      <div class="sp-card-price"><?= $itemPriceDisplay ?></div>
                      <div class="sp-card-buy">Buy Now <i class="fa-solid fa-arrow-right"></i></div>
                    </div>
                  </div>
                </a>
                <?php endforeach; ?>
              </div>
              <div class="sp-pagination" id="spItemsPagination"></div>
            </div>
            <?php endif; ?>

            <?php if (!empty($topups)):
              $topup_games = [];
              foreach ($topups as $topupGameRow) {
                $gameKey = seller_profile_game_key($topupGameRow['game_slug'] ?? 'lol');
                if (!isset($topup_games[$gameKey])) {
                  $topup_games[$gameKey] = [
                    'label' => trim((string)($topupGameRow['game_name'] ?? '')) ?: seller_profile_game_label($gameKey),
                    'icon'  => seller_profile_game_icon($gameKey),
                    'count' => 0,
                  ];
                }
                $topup_games[$gameKey]['count']++;
              }
              uasort($topup_games, fn($a, $b) => strcasecmp($a['label'], $b['label']));
            ?>
            <div class="sp-listing-section" id="spListingTopups">
              <div class="sp-section-label">Top-ups</div>

              <div class="sp-filter-toolbar" id="topFilterToolbar">
                <div class="sp-filter-search">
                  <i class="fa-solid fa-magnifying-glass"></i>
                  <input type="text" placeholder="Search top-ups…" id="topSearch" oninput="spApplyTopFilters()">
                </div>

                <?php if (count($topup_games) > 1): ?>
                <div class="sp-filterpill" id="topGamePill">
                  <button class="sp-filterpill__btn" id="topGameBtn" onclick="spTogglePill('topGamePill')" aria-expanded="false">
                    <i class="fa-solid fa-gamepad"></i>
                    <span>Game</span>
                    <span class="sp-filterpill__value" id="topGameVal"></span>
                    <i class="fa-solid fa-caret-down sp-filterpill__caret"></i>
                  </button>
                  <div class="sp-dd" id="topGameDD">
                    <div class="sp-dd__head">
                      Game
                      <button class="sp-dd__close" onclick="spClosePill('topGamePill')">✕</button>
                    </div>
                    <div class="sp-dd__body">
                      <div class="sp-dd-item active" data-value="" onclick="spSetTopGame('',this)">
                        <i class="fa-solid fa-layer-group"></i> All Games
                      </div>
                      <?php foreach ($topup_games as $gameKey => $gameMeta): ?>
                      <div class="sp-dd-item" data-value="<?= esc($gameKey) ?>" data-label="<?= esc($gameMeta['label']) ?>" onclick="spSetTopGame('<?= esc($gameKey) ?>',this)">
                        <img src="<?= esc($gameMeta['icon']) ?>" alt="" style="width:1.1vw;height:1.1vw;object-fit:contain;flex-shrink:0;">
                        <span class="sp-dd-item__label"><?= esc($gameMeta['label']) ?></span>
                        <span class="sp-dd-item__badge"><?= (int)$gameMeta['count'] ?></span>
                      </div>
                      <?php endforeach; ?>
                    </div>
                  </div>
                </div>
                <?php endif; ?>

                <button class="sp-filter-reset" id="topResetBtn" onclick="spResetTopFilters()" style="display:none;">
                  <i class="fa-solid fa-xmark"></i> Reset
                </button>
                <span class="sp-filter-results" id="topResults"></span>
              </div>

              <div class="sp-accounts-grid" id="spTopupsGrid">
                <?php foreach ($topups as $topup):
                  $topupImage = trim((string)($topup['image'] ?? ''));
                  $topupGame = seller_profile_game_key($topup['game_slug'] ?? 'lol');
                ?>
                <a class="sp-account-card sp-service-card" href="/top-up/<?= (int)$topup['id'] ?>"
                   data-game="<?= esc($topupGame) ?>"
                   data-title="<?= esc(strtolower((string)($topup['title'] ?? ''))) ?>">
                  <?php if ($topupImage !== ''): ?>
                    <img class="sp-card-img sp-card-img--product" src="<?= esc($topupImage) ?>" alt="<?= esc($topup['title'] ?? 'Top-up') ?>">
                  <?php else: ?>
                    <div class="sp-card-img-empty"><img src="<?= esc(seller_profile_game_icon($topupGame)) ?>" alt=""></div>
                  <?php endif; ?>
                  <div class="sp-card-body">
                    <div class="sp-card-game"><img src="<?= esc(seller_profile_game_icon($topupGame)) ?>" alt=""><span><?= esc(seller_profile_game_label($topupGame)) ?></span></div>
                    <div class="sp-card-title"><?= esc($topup['title'] ?? 'Top-up') ?></div>
                    <div class="sp-card-bottom"><div class="sp-card-price"><?= $symbol . util_format_price_display($topup['price'] ?? 0) ?></div><div class="sp-card-buy">Buy Now <i class="fa-solid fa-arrow-right"></i></div></div>
                  </div>
                </a>
                <?php endforeach; ?>
              </div>
              <div class="sp-pagination" id="spTopupsPagination"></div>
            </div>
            <?php endif; ?>

            <?php if (!empty($digital_goods)): ?>
            <div class="sp-listing-section" id="spListingDigital">
              <div class="sp-section-label">Digital Goods</div>
              <div class="sp-accounts-grid" id="spDigitalGrid">
                <?php foreach ($digital_goods as $digital):
                  $digitalImages = json_decode((string)($digital['images'] ?? '[]'), true);
                  if (!is_array($digitalImages)) $digitalImages = [];
                  $digitalImage = trim((string)($digitalImages[0] ?? ''));
                  // Most digital goods only carry a brand icon (e.g. /website/images/…),
                  // which needs the asset host prefixed before it resolves.
                  $digitalAsset = static function ($path): string {
                      $path = trim((string)($path ?? ''));
                      if ($path === '') return '';
                      if (preg_match('#^(?:https?:)?//#i', $path)) return $path;
                      $path = preg_replace('#^/public/assets#i', '', $path);
                      $path = preg_replace('#/public/assets/#i', '/', $path);
                      return rtrim(ASSET_URL, '/') . '/' . ltrim((string)$path, '/');
                  };
                  $digitalBrandIcon = $digitalAsset($digital['brand_icon'] ?? '');
                  $digitalImage = $digitalAsset($digitalImage) ?: $digitalBrandIcon;
                ?>
                <a class="sp-account-card sp-service-card" href="/digital-good/<?= esc($digital['slug'] ?: $digital['id']) ?>">
                  <?php if ($digitalImage !== ''): ?>
                    <img class="sp-card-img sp-card-img--product" src="<?= esc($digitalImage) ?>" alt="<?= esc($digital['title'] ?? 'Digital Good') ?>"
                         <?php if ($digitalBrandIcon !== '' && $digitalBrandIcon !== $digitalImage): ?>data-fallback="<?= esc($digitalBrandIcon) ?>"<?php endif; ?>
                         onerror="if(this.dataset.fallback&&this.src!==this.dataset.fallback){this.src=this.dataset.fallback;}else{this.remove();}">
                  <?php else: ?>
                    <div class="sp-card-img-empty"><i class="fa-solid fa-key"></i></div>
                  <?php endif; ?>
                  <div class="sp-card-body">
                    <div class="sp-card-game sp-card-game--digital"><?php if ($digitalBrandIcon !== ''): ?><img src="<?= esc($digitalBrandIcon) ?>" alt=""><?php else: ?><i class="fa-solid fa-key"></i><?php endif; ?><span><?= esc($digital['category_name'] ?? 'Digital Good') ?></span></div>
                    <div class="sp-card-title"><?= esc($digital['title'] ?? 'Digital Good') ?></div>
                    <div class="sp-card-bottom"><div class="sp-card-price"><?= $symbol . util_format_price_display($digital['price'] ?? 0) ?></div><div class="sp-card-buy">Buy Now <i class="fa-solid fa-arrow-right"></i></div></div>
                  </div>
                </a>
                <?php endforeach; ?>
              </div>
              <div class="sp-pagination" id="spDigitalPagination"></div>
            </div>
            <?php endif; ?>

          <?php endif; ?>
        </div>

        <div class="tab-pane" id="tab-reviews">
          <div class="sp-section">
            <div class="sp-section-label">Seller Reviews</div>

            <div class="sp-review-summary">
              <div class="sp-review-summary-card">
                <div class="sp-review-summary-value"><?= number_format($avg_rating, 1) ?></div>
                <div class="sp-review-summary-label">Average Rating</div>
              </div>

              <div class="sp-review-summary-card">
                <div class="sp-review-summary-value"><?= $review_count ?></div>
                <div class="sp-review-summary-label">Total Reviews</div>
              </div>

            </div>


            <?php if (empty($reviews)): ?>
              <div class="sp-empty">
                <i class="fa-solid fa-star"></i>
                No reviews yet for this seller.
              </div>
            <?php else: ?>
              <div class="sp-reviews-list">
                <?php foreach ($reviews as $review): ?>
                  <?php
                    $clientName = trim((string)($review['client_username'] ?? 'Guest'));
                    $clientIcon = trim((string)($review['client_icon'] ?? ''));
                    $rating = (int)($review['rating'] ?? 0);
                    $comment = trim((string)($review['comment'] ?? ''));
                    $createdAt = !empty($review['created_at']) ? date('d.m.Y H:i', strtotime($review['created_at'])) : '';
                    $initial = strtoupper(substr(preg_replace('/[^A-Za-z0-9]/', '', $clientName) ?: 'G', 0, 1));
                    // Mask username: keep first char + stars + last char, e.g. "G******3"
                    $cleanName = preg_replace('/[^A-Za-z0-9#_\-]/', '', $clientName) ?: 'Guest';
                    $nameLen = mb_strlen($cleanName);
                    if ($nameLen <= 2) {
                        $maskedName = $cleanName[0] . str_repeat('*', 4);
                    } elseif ($nameLen <= 4) {
                        $maskedName = $cleanName[0] . str_repeat('*', $nameLen - 1);
                    } else {
                        $maskedName = $cleanName[0] . str_repeat('*', max(3, $nameLen - 2)) . $cleanName[$nameLen - 1];
                    }
                  ?>
                  <?php
                    $isPlaceholder = !empty($review['is_placeholder']);
                    if ($isPlaceholder) { $rating = 5; $comment = 'No Feedback left.'; }
                  ?>
                  <div class="sp-review-card">
                    <div class="sp-review-top">
                      <div class="sp-review-user">
                        <?php if ($clientIcon !== ''): ?>
                          <img class="sp-review-avatar" src="<?= esc($clientIcon) ?>" alt="<?= esc($initial) ?>">
                        <?php else: ?>
                          <div class="sp-review-avatar-ph"><?= $initial ?></div>
                        <?php endif; ?>

                        <div>
                          <div class="sp-review-name"><?= esc($maskedName) ?></div>
                          <div class="sp-review-date"><?= esc($createdAt) ?></div>
                        </div>
                      </div>

                      <div class="sp-review-stars">
                        <?php for ($i = 1; $i <= 5; $i++): ?>
                          <i class="fa-solid fa-star" style="opacity:<?= $i <= $rating ? '1' : '.22' ?>;"></i>
                        <?php endfor; ?>
                        <span class="sp-review-rating-text"><?= $rating ?>/5</span>
                      </div>
                    </div>

                    <?php if ($comment !== ''): ?>
                      <div class="sp-review-comment"><?= nl2br(esc($comment)) ?></div>
                    <?php endif; ?>
                  </div>
                <?php endforeach; ?>

              </div>

              <?php if ($reviews_total_pages > 1): ?>
                <?php
                  $reviewPageUrl = static function (int $page): string {
                      $path = parse_url((string)($_SERVER['REQUEST_URI'] ?? ''), PHP_URL_PATH) ?: '';
                      if ($path === '') {
                          $path = '/sellers/' . rawurlencode((string)($GLOBALS['seller']['slug'] ?? ''));
                      }
                      $query = $_GET ?? [];
                      unset($query['path']);
                      $query['rpage'] = max(1, $page);
                      return $path . '?' . http_build_query($query) . '#tab-reviews';
                  };
                ?>
                <div class="sp-pagination sp-review-pagination" aria-label="Review pagination">
                  <a href="<?= $reviews_page > 1 ? esc($reviewPageUrl($reviews_page - 1)) : '#' ?>"
                     class="sp-page-arrow <?= $reviews_page <= 1 ? 'disabled' : '' ?>"
                     aria-label="Previous reviews"
                     <?= $reviews_page <= 1 ? 'aria-disabled="true" tabindex="-1"' : '' ?>>
                    <i class="fa-solid fa-chevron-left"></i>
                  </a>

                  <?php for ($p = 1; $p <= $reviews_total_pages; $p++): ?>
                    <?php if ($p === 1 || $p === $reviews_total_pages || ($p >= $reviews_page - 2 && $p <= $reviews_page + 2)): ?>
                      <a href="<?= esc($reviewPageUrl($p)) ?>" class="<?= $p === $reviews_page ? 'active' : '' ?>"><?= $p ?></a>
                    <?php elseif ($p === $reviews_page - 3 || $p === $reviews_page + 3): ?>
                      <span class="sp-page-dots">…</span>
                    <?php endif; ?>
                  <?php endfor; ?>

                  <a href="<?= $reviews_page < $reviews_total_pages ? esc($reviewPageUrl($reviews_page + 1)) : '#' ?>"
                     class="sp-page-arrow <?= $reviews_page >= $reviews_total_pages ? 'disabled' : '' ?>"
                     aria-label="Next reviews"
                     <?= $reviews_page >= $reviews_total_pages ? 'aria-disabled="true" tabindex="-1"' : '' ?>>
                    <i class="fa-solid fa-chevron-right"></i>
                  </a>
                </div>
              <?php endif; ?>
            <?php endif; ?>
          </div>
        </div>

      </div>

    </div>
  </div>
</div>

<?= $this->start('scripts') ?>
<script>
(function(){

  // ── Generic pagination factory ──────────────────────────────────────────
  function makePagination(gridId, paginationId, perPageDesktop, perPageMobile) {
    var currentPage = 1;
    var PER_PAGE = window.innerWidth <= 900 ? perPageMobile : perPageDesktop;

    window.addEventListener('resize', function() {
      var n = window.innerWidth <= 900 ? perPageMobile : perPageDesktop;
      if (n !== PER_PAGE) { PER_PAGE = n; showPage(1); }
    });

    function getVisibleCards() {
      return Array.from(document.querySelectorAll('#' + gridId + ' .sp-account-card:not([data-filtered="1"])'));
    }

    function showPage(page) {
      currentPage = Math.max(1, Math.min(page, Math.max(1, Math.ceil(getVisibleCards().length / PER_PAGE))));
      var all = Array.from(document.querySelectorAll('#' + gridId + ' .sp-account-card'));
      var vis = getVisibleCards();
      var start = (currentPage - 1) * PER_PAGE;
      var end   = start + PER_PAGE;
      all.forEach(function(c) {
        if (c.dataset.filtered === '1') { c.style.display = 'none'; return; }
        c.style.display = '';
      });
      vis.forEach(function(c, i) {
        c.style.display = (i >= start && i < end) ? '' : 'none';
      });
      renderPagination();
    }

    function renderPagination() {
      var container = document.getElementById(paginationId);
      if (!container) return;
      var total = Math.max(1, Math.ceil(getVisibleCards().length / PER_PAGE));
      if (total <= 1) { container.innerHTML = ''; return; }
      var html = '';
      html += '<button ' + (currentPage === 1 ? 'disabled' : '') + ' data-page="prev"><i class="fa-solid fa-chevron-left"></i></button>';
      for (var p = 1; p <= total; p++) {
        if (p === 1 || p === total || (p >= currentPage - 2 && p <= currentPage + 2)) {
          html += '<button class="' + (p === currentPage ? 'active' : '') + '" data-page="' + p + '">' + p + '</button>';
        } else if (p === currentPage - 3 || p === currentPage + 3) {
          html += '<button disabled style="border:none;background:none;color:rgba(255,255,255,.3);">…</button>';
        }
      }
      html += '<button ' + (currentPage === total ? 'disabled' : '') + ' data-page="next"><i class="fa-solid fa-chevron-right"></i></button>';
      container.innerHTML = html;
      container.querySelectorAll('button[data-page]').forEach(function(btn) {
        btn.addEventListener('click', function() {
          var pg = this.dataset.page;
          if (pg === 'prev') showPage(currentPage - 1);
          else if (pg === 'next') showPage(currentPage + 1);
          else showPage(parseInt(pg));
          var grid = document.getElementById(gridId);
          if (grid) grid.scrollIntoView({ behavior: 'smooth', block: 'start' });
        });
      });
    }

    return { init: function() { showPage(1); }, refresh: function() { showPage(1); } };
  }

  var accPag = makePagination('spListingsGrid',  'spPagination',      8, 4);
  var itmPag = makePagination('spItemsGrid',     'spItemsPagination', 8, 4);
  var topPag = makePagination('spTopupsGrid',    'spTopupsPagination', 8, 4);
  var digPag = makePagination('spDigitalGrid',   'spDigitalPagination', 8, 4);

  function initAll() {
    accPag.init(); itmPag.init(); topPag.init(); digPag.init(); spInitRanges();
    var pane = document.getElementById('tab-listings');
    if (pane && pane.classList.contains('sp-filter-all')) {
      document.querySelectorAll('#tab-listings .sp-accounts-grid .sp-account-card').forEach(function(card){ card.style.display=''; });
      spInitAllSliders();
    }
  }

  if (document.getElementById('tab-listings') && document.getElementById('tab-listings').classList.contains('active')) {
    initAll();
  }
  document.addEventListener('click', function(e) {
    if (e.target.closest('[data-tab="tab-listings"]')) setTimeout(initAll, 60);
  });

  // ── Pill dropdown toggle ─────────────────────────────────────────────────
  window.spTogglePill = function(id) {
    var wrap = document.getElementById(id);
    if (!wrap) return;
    var dd   = wrap.querySelector('.sp-dd');
    var btn  = wrap.querySelector('.sp-filterpill__btn');
    var isOpen = dd.classList.contains('is-open');
    // close all
    document.querySelectorAll('.sp-dd.is-open').forEach(function(d){ d.classList.remove('is-open'); });
    document.querySelectorAll('.sp-filterpill__btn[aria-expanded="true"]').forEach(function(b){ b.setAttribute('aria-expanded','false'); });
    if (!isOpen) {
      dd.classList.add('is-open');
      btn.setAttribute('aria-expanded','true');
    }
  };
  window.spClosePill = function(id) {
    var wrap = document.getElementById(id);
    if (!wrap) return;
    wrap.querySelector('.sp-dd')?.classList.remove('is-open');
    wrap.querySelector('.sp-filterpill__btn')?.setAttribute('aria-expanded','false');
  };
  document.addEventListener('click', function(e) {
    if (!e.target.closest('.sp-filterpill')) {
      document.querySelectorAll('.sp-dd.is-open').forEach(function(d){ d.classList.remove('is-open'); });
      document.querySelectorAll('.sp-filterpill__btn[aria-expanded="true"]').forEach(function(b){ b.setAttribute('aria-expanded','false'); });
    }
  });

  // ── Dual Range helpers ───────────────────────────────────────────────────
  function spUpdateRangeFill(minId, maxId, fillId) {
    var rMin = document.getElementById(minId);
    var rMax = document.getElementById(maxId);
    var fill = document.getElementById(fillId);
    if (!rMin || !rMax || !fill) return;
    var min = parseFloat(rMin.min) || 0;
    var max = parseFloat(rMin.max) || 1;
    var lo  = (parseFloat(rMin.value) - min) / (max - min) * 100;
    var hi  = (parseFloat(rMax.value) - min) / (max - min) * 100;
    fill.style.left  = lo + '%';
    fill.style.width = Math.max(0, hi - lo) + '%';
  }

  function spInitRanges() {
    spUpdateRangeFill('accRangeMin','accRangeMax','accRangeFill');
    spUpdateRangeFill('itmRangeMin','itmRangeMax','itmRangeFill');
  }

  // Accounts range slider → sync inputs
  window.spOnAccRange = function(which) {
    var rMin = document.getElementById('accRangeMin');
    var rMax = document.getElementById('accRangeMax');
    if (!rMin || !rMax) return;
    var lo = parseFloat(rMin.value), hi = parseFloat(rMax.value);
    if (which === 'min' && lo > hi - 0.01) { rMin.value = hi - 0.01; lo = hi - 0.01; }
    if (which === 'max' && hi < lo + 0.01) { rMax.value = lo + 0.01; hi = lo + 0.01; }
    var mn = document.getElementById('accPriceMin');
    var mx = document.getElementById('accPriceMax');
    if (mn) mn.value = lo.toFixed(2);
    if (mx) mx.value = hi.toFixed(2);
    var pref = '€';
    var lMin = document.getElementById('accPriceLabelMin');
    var lMax = document.getElementById('accPriceLabelMax');
    if (lMin) lMin.textContent = pref + lo.toFixed(2);
    if (lMax) lMax.textContent = pref + hi.toFixed(2);
    spUpdateRangeFill('accRangeMin','accRangeMax','accRangeFill');
    var pv = document.getElementById('accPriceVal');
    if (pv) pv.textContent = pref + lo.toFixed(2) + ' – ' + pref + hi.toFixed(2);
    document.getElementById('accPriceBtn')?.classList.add('has-value');
    spApplyAccFilters();
  };
  // Accounts number input → sync range
  window.spSyncAccRange = function() {
    var mn = document.getElementById('accPriceMin');
    var mx = document.getElementById('accPriceMax');
    var rMin = document.getElementById('accRangeMin');
    var rMax = document.getElementById('accRangeMax');
    if (mn && rMin) rMin.value = mn.value;
    if (mx && rMax) rMax.value = mx.value;
    spUpdateRangeFill('accRangeMin','accRangeMax','accRangeFill');
    spApplyAccFilters();
  };

  // Items range slider → sync inputs
  window.spOnItmRange = function(which) {
    var rMin = document.getElementById('itmRangeMin');
    var rMax = document.getElementById('itmRangeMax');
    if (!rMin || !rMax) return;
    var lo = parseFloat(rMin.value), hi = parseFloat(rMax.value);
    if (which === 'min' && lo > hi - 0.01) { rMin.value = hi - 0.01; lo = hi - 0.01; }
    if (which === 'max' && hi < lo + 0.01) { rMax.value = lo + 0.01; hi = lo + 0.01; }
    var mn = document.getElementById('itmPriceMin');
    var mx = document.getElementById('itmPriceMax');
    if (mn) mn.value = lo.toFixed(2);
    if (mx) mx.value = hi.toFixed(2);
    var pref = '€';
    var lMin = document.getElementById('itmPriceLabelMin');
    var lMax = document.getElementById('itmPriceLabelMax');
    if (lMin) lMin.textContent = pref + lo.toFixed(2);
    if (lMax) lMax.textContent = pref + hi.toFixed(2);
    spUpdateRangeFill('itmRangeMin','itmRangeMax','itmRangeFill');
    var pv = document.getElementById('itmPriceVal');
    if (pv) pv.textContent = pref + lo.toFixed(2) + ' – ' + pref + hi.toFixed(2);
    document.getElementById('itmPriceBtn')?.classList.add('has-value');
    spApplyItmFilters();
  };
  window.spSyncItmRange = function() {
    var mn = document.getElementById('itmPriceMin');
    var mx = document.getElementById('itmPriceMax');
    var rMin = document.getElementById('itmRangeMin');
    var rMax = document.getElementById('itmRangeMax');
    if (mn && rMin) rMin.value = mn.value;
    if (mx && rMax) rMax.value = mx.value;
    spUpdateRangeFill('itmRangeMin','itmRangeMax','itmRangeFill');
    spApplyItmFilters();
  };

  // ── ACCOUNTS filter ──────────────────────────────────────────────────────
  var accState = { search: '', priceMin: null, priceMax: null, server: '', game: '' };

  window.spSetAccGame = function(val, el) {
    accState.game = val;
    document.querySelectorAll('#accGameDD .sp-dd-item').forEach(function(i){ i.classList.remove('active'); });
    el.classList.add('active');
    var btn = document.getElementById('accGameBtn');
    var valEl = document.getElementById('accGameVal');
    var label = el ? (el.childNodes[2]?.textContent || el.textContent || '').trim() : val;
    if (btn) btn.classList.toggle('has-value', val !== '');
    if (valEl) valEl.textContent = val ? label.replace(/\s+\d+$/, '') : '';
    spClosePill('accGamePill');
    spApplyAccFilters();
  };

  window.spSetAccServer = function(val, el) {
    accState.server = val;
    document.querySelectorAll('#accServerDD .sp-dd-item').forEach(function(i){ i.classList.remove('active'); });
    el.classList.add('active');
    var btn = document.getElementById('accServerBtn');
    var valEl = document.getElementById('accServerVal');
    if (btn) btn.classList.toggle('has-value', val !== '');
    if (valEl) valEl.textContent = val ? val : '';
    spClosePill('accServerPill');
    spApplyAccFilters();
  };

  window.spApplyAccFilters = function() {
    accState.search   = (document.getElementById('accSearch')?.value || '').toLowerCase();
    accState.priceMin = parseFloat(document.getElementById('accPriceMin')?.value);
    accState.priceMax = parseFloat(document.getElementById('accPriceMax')?.value);
    var initMin = parseFloat(document.getElementById('accRangeMin')?.min || 0);
    var initMax = parseFloat(document.getElementById('accRangeMin')?.max || 9999);
    var priceActive = (!isNaN(accState.priceMin) && accState.priceMin > initMin) || (!isNaN(accState.priceMax) && accState.priceMax < initMax);
    document.getElementById('accPriceBtn')?.classList.toggle('has-value', priceActive);

    var cards = Array.from(document.querySelectorAll('#spListingsGrid .sp-account-card'));
    var visible = 0;
    cards.forEach(function(card) {
      var title  = (card.dataset.title  || '').toLowerCase();
      var srv    = (card.dataset.server || '').toUpperCase();
      var game   = (card.dataset.game || 'lol').toLowerCase();
      var price  = parseFloat(card.dataset.price) || 0;
      var hide = false;
      if (accState.search && title.indexOf(accState.search) === -1) hide = true;
      if (accState.game && game !== accState.game) hide = true;
      if (accState.server && srv !== accState.server) hide = true;
      if (!isNaN(accState.priceMin) && price < accState.priceMin) hide = true;
      if (!isNaN(accState.priceMax) && price > accState.priceMax) hide = true;
      card.dataset.filtered = hide ? '1' : '0';
      if (!hide) visible++;
    });

    var hasFilter = accState.search || accState.game || accState.server || priceActive;
    var rst = document.getElementById('accResetBtn');
    if (rst) rst.style.display = hasFilter ? '' : 'none';
    var res = document.getElementById('accResults');
    if (res) res.textContent = hasFilter ? (visible + ' result' + (visible === 1 ? '' : 's')) : '';
    accPag.refresh();
  };

  window.spResetAccFilters = function() {
    accState = { search: '', priceMin: null, priceMax: null, server: '', game: '' };
    var s = document.getElementById('accSearch'); if (s) s.value = '';
    var rMin = document.getElementById('accRangeMin');
    var rMax = document.getElementById('accRangeMax');
    var initMin = parseFloat(rMin?.min || 0);
    var initMax = parseFloat(rMax?.max || 9999);
    var mn = document.getElementById('accPriceMin'); if (mn) mn.value = initMin;
    var mx = document.getElementById('accPriceMax'); if (mx) mx.value = initMax;
    if (rMin) rMin.value = initMin;
    if (rMax) rMax.value = initMax;
    spUpdateRangeFill('accRangeMin','accRangeMax','accRangeFill');
    document.querySelectorAll('#accGameDD .sp-dd-item').forEach(function(i){ i.classList.remove('active'); });
    var firstGame = document.querySelector('#accGameDD .sp-dd-item'); if (firstGame) firstGame.classList.add('active');
    document.querySelectorAll('#accServerDD .sp-dd-item').forEach(function(i){ i.classList.remove('active'); });
    var first = document.querySelector('#accServerDD .sp-dd-item'); if (first) first.classList.add('active');
    document.getElementById('accPriceBtn')?.classList.remove('has-value');
    document.getElementById('accGameBtn')?.classList.remove('has-value');
    document.getElementById('accServerBtn')?.classList.remove('has-value');
    var accPriceVal = document.getElementById('accPriceVal'); if (accPriceVal) accPriceVal.textContent = '';
    var accGameVal = document.getElementById('accGameVal'); if (accGameVal) accGameVal.textContent = '';
    var accServerVal = document.getElementById('accServerVal'); if (accServerVal) accServerVal.textContent = '';
    document.getElementById('accResetBtn').style.display = 'none';
    document.getElementById('accResults').textContent = '';
    Array.from(document.querySelectorAll('#spListingsGrid .sp-account-card')).forEach(function(c){ c.dataset.filtered = '0'; });
    accPag.refresh();
  };

  // ── ITEMS filter ─────────────────────────────────────────────────────────
  var itmState = { search: '', priceMin: null, priceMax: null, type: '', game: '' };

  window.spSetItmGame = function(val, el) {
    itmState.game = val;
    document.querySelectorAll('#itmGameDD .sp-dd-item').forEach(function(i){ i.classList.remove('active'); });
    el.classList.add('active');
    var btn = document.getElementById('itmGameBtn');
    var valEl = document.getElementById('itmGameVal');
    if (btn) btn.classList.toggle('has-value', val !== '');
    if (valEl) valEl.textContent = val ? (el.dataset.label || val) : '';
    spClosePill('itmGamePill');
    spApplyItmFilters();
  };

  window.spSetItmType = function(val, el) {
    itmState.type = val;
    document.querySelectorAll('#itmTypeDD .sp-dd-item').forEach(function(i){ i.classList.remove('active'); });
    el.classList.add('active');
    var btn = document.getElementById('itmTypeBtn');
    var valEl = document.getElementById('itmTypeVal');
    if (btn) btn.classList.toggle('has-value', val !== '');
    if (valEl) valEl.textContent = val ? val : '';
    spClosePill('itmTypePill');
    spApplyItmFilters();
  };

  window.spApplyItmFilters = function() {
    itmState.search   = (document.getElementById('itmSearch')?.value || '').toLowerCase();
    itmState.priceMin = parseFloat(document.getElementById('itmPriceMin')?.value);
    itmState.priceMax = parseFloat(document.getElementById('itmPriceMax')?.value);
    var initMin = parseFloat(document.getElementById('itmRangeMin')?.min || 0);
    var initMax = parseFloat(document.getElementById('itmRangeMin')?.max || 9999);
    var priceActive = (!isNaN(itmState.priceMin) && itmState.priceMin > initMin) || (!isNaN(itmState.priceMax) && itmState.priceMax < initMax);
    document.getElementById('itmPriceBtn')?.classList.toggle('has-value', priceActive);

    var cards = Array.from(document.querySelectorAll('#spItemsGrid .sp-account-card'));
    var visible = 0;
    cards.forEach(function(card) {
      var title = (card.dataset.title || '').toLowerCase();
      var type  = (card.dataset.type  || '').toLowerCase();
      var game  = (card.dataset.game  || '').toLowerCase();
      var price = parseFloat(card.dataset.price) || 0;
      var hide = false;
      if (itmState.search && title.indexOf(itmState.search) === -1) hide = true;
      if (itmState.type && type !== itmState.type) hide = true;
      if (itmState.game && game !== itmState.game) hide = true;
      if (!isNaN(itmState.priceMin) && price < itmState.priceMin) hide = true;
      if (!isNaN(itmState.priceMax) && price > itmState.priceMax) hide = true;
      card.dataset.filtered = hide ? '1' : '0';
      if (!hide) visible++;
    });

    var hasFilter = itmState.search || itmState.type || itmState.game || priceActive;
    var rst = document.getElementById('itmResetBtn');
    if (rst) rst.style.display = hasFilter ? '' : 'none';
    var res = document.getElementById('itmResults');
    if (res) res.textContent = hasFilter ? (visible + ' result' + (visible === 1 ? '' : 's')) : '';
    itmPag.refresh();
  };

  window.spResetItmFilters = function() {
    itmState = { search: '', priceMin: null, priceMax: null, type: '', game: '' };
    document.querySelectorAll('#itmGameDD .sp-dd-item').forEach(function(i){ i.classList.remove('active'); });
    var firstItmGame = document.querySelector('#itmGameDD .sp-dd-item'); if (firstItmGame) firstItmGame.classList.add('active');
    document.getElementById('itmGameBtn')?.classList.remove('has-value');
    var itmGameValEl = document.getElementById('itmGameVal'); if (itmGameValEl) itmGameValEl.textContent = '';
    var s = document.getElementById('itmSearch'); if (s) s.value = '';
    var rMin = document.getElementById('itmRangeMin');
    var rMax = document.getElementById('itmRangeMax');
    var initMin = parseFloat(rMin?.min || 0);
    var initMax = parseFloat(rMax?.max || 9999);
    var mn = document.getElementById('itmPriceMin'); if (mn) mn.value = initMin;
    var mx = document.getElementById('itmPriceMax'); if (mx) mx.value = initMax;
    if (rMin) rMin.value = initMin;
    if (rMax) rMax.value = initMax;
    spUpdateRangeFill('itmRangeMin','itmRangeMax','itmRangeFill');
    document.querySelectorAll('#itmTypeDD .sp-dd-item').forEach(function(i){ i.classList.remove('active'); });
    var first = document.querySelector('#itmTypeDD .sp-dd-item'); if (first) first.classList.add('active');
    document.getElementById('itmPriceBtn')?.classList.remove('has-value');
    document.getElementById('itmTypeBtn')?.classList.remove('has-value');
    document.getElementById('itmPriceVal').textContent = '';
    document.getElementById('itmTypeVal').textContent = '';
    document.getElementById('itmResetBtn').style.display = 'none';
    document.getElementById('itmResults').textContent = '';
    Array.from(document.querySelectorAll('#spItemsGrid .sp-account-card')).forEach(function(c){ c.dataset.filtered = '0'; });
    itmPag.refresh();
  };

  // ── TOP-UPS filter ───────────────────────────────────────────────────────
  var topState = { search: '', game: '' };

  window.spSetTopGame = function(val, el) {
    topState.game = val;
    document.querySelectorAll('#topGameDD .sp-dd-item').forEach(function(i){ i.classList.remove('active'); });
    el.classList.add('active');
    var btn = document.getElementById('topGameBtn');
    var valEl = document.getElementById('topGameVal');
    if (btn) btn.classList.toggle('has-value', val !== '');
    if (valEl) valEl.textContent = val ? (el.dataset.label || val) : '';
    spClosePill('topGamePill');
    spApplyTopFilters();
  };

  window.spApplyTopFilters = function() {
    topState.search = (document.getElementById('topSearch')?.value || '').toLowerCase();
    var visible = 0;
    Array.from(document.querySelectorAll('#spTopupsGrid .sp-account-card')).forEach(function(card) {
      var title = (card.dataset.title || '').toLowerCase();
      var game  = (card.dataset.game  || '').toLowerCase();
      var hide = false;
      if (topState.search && title.indexOf(topState.search) === -1) hide = true;
      if (topState.game && game !== topState.game) hide = true;
      card.dataset.filtered = hide ? '1' : '0';
      if (!hide) visible++;
    });
    var hasFilter = topState.search || topState.game;
    var rst = document.getElementById('topResetBtn');
    if (rst) rst.style.display = hasFilter ? '' : 'none';
    var res = document.getElementById('topResults');
    if (res) res.textContent = hasFilter ? (visible + ' result' + (visible === 1 ? '' : 's')) : '';
    topPag.refresh();
  };

  window.spResetTopFilters = function() {
    topState = { search: '', game: '' };
    var s = document.getElementById('topSearch'); if (s) s.value = '';
    document.querySelectorAll('#topGameDD .sp-dd-item').forEach(function(i){ i.classList.remove('active'); });
    var firstTopGame = document.querySelector('#topGameDD .sp-dd-item'); if (firstTopGame) firstTopGame.classList.add('active');
    document.getElementById('topGameBtn')?.classList.remove('has-value');
    var topGameValEl = document.getElementById('topGameVal'); if (topGameValEl) topGameValEl.textContent = '';
    var rst = document.getElementById('topResetBtn'); if (rst) rst.style.display = 'none';
    var res = document.getElementById('topResults'); if (res) res.textContent = '';
    Array.from(document.querySelectorAll('#spTopupsGrid .sp-account-card')).forEach(function(c){ c.dataset.filtered = '0'; });
    topPag.refresh();
  };

  // ── Category pills ───────────────────────────────────────────────────────
  (function initListingFilterDrag(){
    var row = document.querySelector('.sp-listings-filter');
    if (!row) return;

    var state = {
      active:false,
      moved:false,
      pointerId:null,
      startX:0,
      startY:0,
      startScroll:0,
      suppressClick:false
    };

    row.addEventListener('pointerdown',function(event){
      if (event.pointerType === 'mouse' && event.button !== 0) return;
      state.active = true;
      state.moved = false;
      state.pointerId = event.pointerId;
      state.startX = event.clientX;
      state.startY = event.clientY;
      state.startScroll = row.scrollLeft;
    });

    row.addEventListener('pointermove',function(event){
      if (!state.active || event.pointerId !== state.pointerId) return;
      var dx = event.clientX - state.startX;
      var dy = event.clientY - state.startY;
      if (!state.moved) {
        if (Math.abs(dx) < 7 && Math.abs(dy) < 7) return;
        if (Math.abs(dy) >= Math.abs(dx)) {
          state.active = false;
          return;
        }
        state.moved = true;
        row.classList.add('is-dragging');
        if (row.setPointerCapture) row.setPointerCapture(event.pointerId);
      }
      event.preventDefault();
      row.scrollLeft = state.startScroll - dx;
    });

    function finishDrag(event){
      if (!state.active) return;
      var didMove = state.moved;
      state.active = false;
      state.moved = false;
      row.classList.remove('is-dragging');
      if (row.hasPointerCapture && row.hasPointerCapture(event.pointerId)) {
        row.releasePointerCapture(event.pointerId);
      }
      if (didMove) {
        state.suppressClick = true;
        window.setTimeout(function(){ state.suppressClick = false; },400);
      }
    }

    row.addEventListener('pointerup',finishDrag);
    row.addEventListener('pointercancel',function(event){
      state.moved = false;
      finishDrag(event);
      state.suppressClick = false;
    });
    row.addEventListener('click',function(event){
      if (!state.suppressClick) return;
      state.suppressClick = false;
      event.preventDefault();
      event.stopPropagation();
    },true);
  })();

  window.spFilterListings = function(filter, btn) {
    document.querySelectorAll('.sp-filter-pill').forEach(function(b){ b.classList.remove('active'); });
    btn.classList.add('active');
    var filterRow = btn.closest('.sp-listings-filter');
    if (filterRow) {
      var targetLeft = btn.offsetLeft - (filterRow.clientWidth - btn.offsetWidth) / 2;
      var maxLeft = Math.max(0, filterRow.scrollWidth - filterRow.clientWidth);
      filterRow.scrollTo({left:Math.max(0,Math.min(targetLeft,maxLeft)),behavior:'smooth'});
    }
    var pane = document.getElementById('tab-listings');
    var acc = document.getElementById('spListingAccounts');
    var itm = document.getElementById('spListingItems');
    var top = document.getElementById('spListingTopups');
    var dig = document.getElementById('spListingDigital');
    if (pane) pane.classList.toggle('sp-filter-all', filter === 'all');
    [[acc,'accounts'],[itm,'items'],[top,'topups'],[dig,'digital']].forEach(function(pair){
      if (pair[0]) pair[0].style.display = (filter === 'all' || filter === pair[1]) ? '' : 'none';
    });
    if (filter === 'all') {
      document.querySelectorAll('#tab-listings .sp-accounts-grid .sp-account-card').forEach(function(card){ card.style.display=''; });
      spInitAllSliders();
    } else {
      document.querySelectorAll('#tab-listings .sp-accounts-grid').forEach(function(grid){
        grid.scrollLeft = 0;
        grid.classList.remove('is-swiping');
      });
      if (filter === 'accounts') accPag.refresh();
      if (filter === 'items') itmPag.refresh();
      if (filter === 'topups') topPag.refresh();
      if (filter === 'digital') digPag.refresh();
    }
  };

  function spInitAllSliders() {
    document.querySelectorAll('#tab-listings .sp-listing-section').forEach(function(section){
      var grid = section.querySelector('.sp-accounts-grid');
      var label = section.querySelector('.sp-section-label');
      if (!grid || !label) return;

      if (!grid.dataset.swipeReady) {
        grid.dataset.swipeReady = '1';
        var swipe = { active:false, horizontal:false, startX:0, startY:0, startScroll:0 };
        grid.addEventListener('touchstart', function(event) {
          var listingsPane = document.getElementById('tab-listings');
          if (!listingsPane || !listingsPane.classList.contains('sp-filter-all')) {
            swipe.active = false;
            swipe.horizontal = false;
            return;
          }
          if (!event.touches || event.touches.length !== 1) return;
          swipe.active = true;
          swipe.horizontal = false;
          swipe.startX = event.touches[0].clientX;
          swipe.startY = event.touches[0].clientY;
          swipe.startScroll = grid.scrollLeft;
        }, { passive:true });
        grid.addEventListener('touchmove', function(event) {
          if (!swipe.active || !event.touches || event.touches.length !== 1) return;
          var dx = event.touches[0].clientX - swipe.startX;
          var dy = event.touches[0].clientY - swipe.startY;
          if (!swipe.horizontal && Math.abs(dx) < 7 && Math.abs(dy) < 7) return;
          if (!swipe.horizontal) {
            if (Math.abs(dy) >= Math.abs(dx)) {
              swipe.active = false;
              return;
            }
            swipe.horizontal = true;
            grid.classList.add('is-swiping');
          }
          event.preventDefault();
          grid.scrollLeft = swipe.startScroll - dx;
        }, { passive:false });
        function finishSwipe() {
          if (!swipe.active && !swipe.horizontal) return;
          var wasHorizontal = swipe.horizontal;
          swipe.active = false;
          swipe.horizontal = false;
          grid.classList.remove('is-swiping');
          if (!wasHorizontal) return;
          var card = grid.querySelector('.sp-account-card');
          if (!card) return;
          var styles = window.getComputedStyle(grid);
          var gap = parseFloat(styles.columnGap || styles.gap || 0) || 0;
          var step = card.getBoundingClientRect().width + gap;
          grid.scrollTo({left:Math.round(grid.scrollLeft / step) * step,behavior:'smooth'});
        }
        grid.addEventListener('touchend', finishSwipe, { passive:true });
        grid.addEventListener('touchcancel', finishSwipe, { passive:true });
      }

      if (label.querySelector('.sp-row-controls')) return;
      var controls = document.createElement('span');
      controls.className = 'sp-row-controls';
      controls.innerHTML = '<button type="button" aria-label="Previous"><i class="fa-solid fa-chevron-left"></i></button><button type="button" aria-label="Next"><i class="fa-solid fa-chevron-right"></i></button>';
      var buttons = controls.querySelectorAll('button');
      function slideDistance() {
        var card = grid.querySelector('.sp-account-card');
        if (!card) return Math.max(240, grid.clientWidth * .8);
        var styles = window.getComputedStyle(grid);
        var gap = parseFloat(styles.columnGap || styles.gap || 0) || 0;
        return card.getBoundingClientRect().width + gap;
      }
      buttons[0].addEventListener('click',function(){ grid.scrollBy({left:-slideDistance(),behavior:'smooth'}); });
      buttons[1].addEventListener('click',function(){ grid.scrollBy({left: slideDistance(),behavior:'smooth'}); });
      label.appendChild(controls);
    });
  }
  setTimeout(function(){
    var pane=document.getElementById('tab-listings');
    if (pane && pane.classList.contains('sp-filter-all')) {
      document.querySelectorAll('#tab-listings .sp-accounts-grid .sp-account-card').forEach(function(card){card.style.display='';});
      spInitAllSliders();
    }
  },80);

  // ── Tab switching ────────────────────────────────────────────────────────
  document.querySelectorAll('[data-tab]').forEach(function(link){
    link.addEventListener('click', function(e){
      e.preventDefault();
      var target = this.dataset.tab;
      document.querySelectorAll('#spNavTabs a[data-tab]').forEach(function(a){ a.classList.remove('active'); });
      var activeLink = document.querySelector('#spNavTabs a[data-tab="' + target + '"]');
      if (activeLink) activeLink.classList.add('active');
      document.querySelectorAll('.tab-pane').forEach(function(p){ p.classList.remove('active'); });
      var activePane = document.getElementById(target);
      if (activePane) activePane.classList.add('active');
    });
  });

  // ── Hash-based tab open ──────────────────────────────────────────────────
  (function(){
    var hash = window.location.hash.replace('#', '');
    if (!hash) return;
    var navLink = document.querySelector('#spNavTabs a[data-tab="' + hash + '"]');
    var pane    = document.getElementById(hash);
    if (navLink && pane) {
      document.querySelectorAll('#spNavTabs a[data-tab]').forEach(function(a){ a.classList.remove('active'); });
      document.querySelectorAll('.tab-pane').forEach(function(p){ p.classList.remove('active'); });
      navLink.classList.add('active');
      pane.classList.add('active');
    }
  })();

  // ── Review pagination without changing the visible URL ───────────────────
  (function(){
    var reviewPane = document.getElementById('tab-reviews');
    if (!reviewPane) return;

    function bindReviewPagination(scope) {
      (scope || document).querySelectorAll('.sp-review-pagination a[href]:not(.disabled)').forEach(function(link){
        link.addEventListener('click', function(e){
          e.preventDefault();
          var targetUrl = this.getAttribute('href');
          if (!targetUrl || targetUrl === '#') return;

          var pagination = this.closest('.sp-review-pagination');
          if (pagination) pagination.classList.add('is-loading');

          fetch(targetUrl, {
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            credentials: 'same-origin'
          })
            .then(function(response){ return response.text(); })
            .then(function(html){
              var doc = new DOMParser().parseFromString(html, 'text/html');
              var nextPane = doc.getElementById('tab-reviews');
              if (!nextPane) return;

              var currentList = reviewPane.querySelector('.sp-reviews-list');
              var nextList = nextPane.querySelector('.sp-reviews-list');
              if (currentList && nextList) {
                currentList.innerHTML = nextList.innerHTML;
              }

              var currentPagination = reviewPane.querySelector('.sp-review-pagination');
              var nextPagination = nextPane.querySelector('.sp-review-pagination');
              if (currentPagination && nextPagination) {
                currentPagination.replaceWith(nextPagination);
                bindReviewPagination(reviewPane);
              } else if (currentPagination && !nextPagination) {
                currentPagination.remove();
              } else if (!currentPagination && nextPagination) {
                var list = reviewPane.querySelector('.sp-reviews-list');
                if (list) list.insertAdjacentElement('afterend', nextPagination);
                bindReviewPagination(reviewPane);
              }

              var reviewsTab = document.querySelector('#spNavTabs a[data-tab="tab-reviews"]');
              if (reviewsTab) reviewsTab.click();
              reviewPane.scrollIntoView({ behavior: 'smooth', block: 'start' });
            })
            .catch(function(){
              window.location.href = targetUrl;
            })
            .finally(function(){
              var activePagination = reviewPane.querySelector('.sp-review-pagination');
              if (activePagination) activePagination.classList.remove('is-loading');
            });
        });
      });
    }

    bindReviewPagination(reviewPane);
  })();

})();
</script>
<?= $this->end() ?>
