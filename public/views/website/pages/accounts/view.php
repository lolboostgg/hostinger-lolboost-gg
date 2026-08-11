<?= $this->layout('website/layouts/master', ['meta' => $meta, 'bodyClass' => 'view-account-page']) ?>
<?php

if (!function_exists('lb_db_seller_total_sales')) {
    function lb_db_seller_total_sales(int $sellerId, int $fallback = 0): int
    {
        static $cache = [];

        if ($sellerId <= 0) {
            return max(0, $fallback);
        }
        if (array_key_exists($sellerId, $cache)) {
            return $cache[$sellerId];
        }

        if (function_exists('get_seller_total_sales')) {
            try {
                return $cache[$sellerId] = max(0, (int)get_seller_total_sales($sellerId));
            } catch (Throwable $e) {
            }
        }

        return $cache[$sellerId] = max(0, $fallback);
    }
}

// Safe defaults in case route didn't pass these variables
if (!isset($seller)) $seller = null;
if (!isset($seller_accounts)) $seller_accounts = [];
?>
<?php
    $champions = array_values(array_filter(array_map('trim', explode('|', (string)($account['champions'] ?? '')))));
    $skins = array_values(array_filter(array_map('trim', explode('|', (string)($account['skins'] ?? '')))));
    $roles = array_values(array_filter(array_map('trim', explode('|', (string)($account['roles'] ?? '')))));
?>

<?php
    function should_hide_lol_division($rank): bool
    {
        return in_array((int)$rank, [0, 8, 9, 10], true);
    }

    function format_lol_rank_display($rank, $lp, $division): string
    {
        // Coerce to string: util_get_lol_rank()/util_format_lol_division() can
        // return null for empty ranks, which would throw a TypeError on this
        // string-typed function and crash the account page.
        $label = (string) util_get_lol_rank($rank);

        if ($lp !== null && (int)$lp !== 0) {
            return $label . ' ' . (int)$lp . 'LP';
        }

        if (should_hide_lol_division($rank)) {
            return $label;
        }

        return trim($label . ' ' . (string) util_format_lol_division($division));
    }
?>

<?php
if (!function_exists('account_view_detect_currency_rate')) {
    function account_view_detect_currency_rate(string $currencyCode): float
    {
        $currencyCode = strtoupper(trim($currencyCode));
        if ($currencyCode === 'EUR') return 1.0;

        $candidates = [];
        if (function_exists('get_exchange_rate')) $candidates[] = get_exchange_rate();
        if (isset($_SESSION['exchange_rates']) && is_array($_SESSION['exchange_rates']) && isset($_SESSION['exchange_rates'][$currencyCode])) $candidates[] = $_SESSION['exchange_rates'][$currencyCode];
        if (isset($_SESSION['currency_rates']) && is_array($_SESSION['currency_rates']) && isset($_SESSION['currency_rates'][$currencyCode])) $candidates[] = $_SESSION['currency_rates'][$currencyCode];
        if (isset($_SESSION['rates']) && is_array($_SESSION['rates']) && isset($_SESSION['rates'][$currencyCode])) $candidates[] = $_SESSION['rates'][$currencyCode];
        if (isset($_SESSION['currency_rate']) && is_numeric($_SESSION['currency_rate'])) $candidates[] = $_SESSION['currency_rate'];
        if (isset($_SESSION['currency_multiplier']) && is_numeric($_SESSION['currency_multiplier'])) $candidates[] = $_SESSION['currency_multiplier'];

        foreach ($candidates as $rate) {
            $rate = (float)$rate;
            if ($rate > 0) return $rate;
        }

        return 1.0;
    }
}

if (!function_exists('account_view_convert_price_cents')) {
    function account_view_convert_price_cents(int $priceCents, string $currencyCode): int
    {
        $currencyCode = strtoupper(trim($currencyCode));
        if ($currencyCode === 'EUR') return $priceCents;

        $rate = account_view_detect_currency_rate($currencyCode);
        return (int)round($priceCents * $rate);
    }
}

if (!function_exists('account_view_format_price')) {
    function account_view_format_price(int $priceCents, string $currencyCode): array
    {
        $convertedCents = account_view_convert_price_cents($priceCents, $currencyCode);
        $symbol = function_exists('util_format_currency_display')
            ? util_format_currency_display($currencyCode)
            : ($currencyCode === 'USD' ? '$' : '€');

        return [
            'cents' => $convertedCents,
            'formatted' => (function_exists('util_format_price_display')
                ? util_format_price_display($convertedCents)
                : number_format($convertedCents / 100, 2, '.', ',')),
            'symbol' => $symbol,
            'with_symbol' => $symbol . (function_exists('util_format_price_display')
                ? util_format_price_display($convertedCents)
                : number_format($convertedCents / 100, 2, '.', ',')),
        ];
    }
}

$accountCurrencyCode = strtoupper((string)($_SESSION['currency'] ?? 'EUR'));
$accountPriceDisplay = account_view_format_price((int)($account['price'] ?? 0), $accountCurrencyCode);

$sellerTotalSoldDisplay = lb_db_seller_total_sales(
    (int)($seller['id'] ?? 0),
    (int)($seller['seller_total_sales'] ?? $seller['total_sales'] ?? $seller['total_sold'] ?? $seller['seller_sold'] ?? 0)
);
?>

<?= $this->start('styles') ?>
<link rel="stylesheet" type="text/css" href="//cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick.css" />
<link rel="stylesheet" href="https://unpkg.com/baguettebox.js@1.11.1/dist/baguetteBox.min.css" />
<style>
/* ── Layout overflow fix ── */
.view-account-page .layout .left {
    min-width: 0;
    overflow: hidden;
}
.view-account-page .layout .right {
    flex-shrink: 0;
    width: 26.042vw;
    min-width: 0;
}

/* ── Seller Profile Card ────────────────────────────────── */
.seller-profile-card {
    background: rgba(255,255,255,0.04);
    border: 1px solid rgba(99,102,241,0.2);
    border-radius: 16px;
    padding: 20px 24px;
    margin-bottom: 24px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 16px;
}

.seller-profile-card__left {
    display: flex;
    align-items: center;
    gap: 14px;
}

.seller-profile-card__avatar {
    width: 52px;
    height: 52px;
    border-radius: 50%;
    object-fit: cover;
    border: 2px solid rgba(99,102,241,0.5);
    box-shadow: 0 0 0 4px rgba(99,102,241,0.1);
    flex-shrink: 0;
}

.seller-profile-card__avatar-placeholder {
    width: 52px;
    height: 52px;
    border-radius: 50%;
    background: linear-gradient(135deg, rgba(99,102,241,0.3), rgba(139,92,246,0.2));
    border: 2px solid rgba(99,102,241,0.4);
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    font-size: 20px;
    color: rgba(255,255,255,0.6);
}

.seller-profile-card__info {
    display: flex;
    flex-direction: column;
    gap: 4px;
}

.seller-profile-card__name {
    font-size: 15px;
    font-weight: 700;
    color: #fff;
    display: flex;
    align-items: center;
    gap: 6px;
}

.seller-profile-card__name .verified {
    color: #6366f1;
    font-size: 13px;
}
.seller-profile-card__rank-icon {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    margin-left: 8px;
    font-size: 18px;
    line-height: 1;
    filter: drop-shadow(0 0 10px currentColor);
    transform: translateY(1px);
}

.saf-slider .seller-info__verified,
.more-accounts-section .seller-info__verified {
    font-size: 22px !important;
    filter: drop-shadow(0 0 12px currentColor);
    transform: translateY(1px);
}

.seller-profile-card__label {
    font-size: 11px;
    color: rgba(255,255,255,0.4);
    text-transform: uppercase;
    letter-spacing: 0.06em;
    font-weight: 600;
}

.seller-profile-card__stats {
    display: flex;
    align-items: center;
    gap: 10px;
    flex-shrink: 0;
}

.seller-stat {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 2px;
    padding: 8px 14px;
    background: rgba(255,255,255,0.04);
    border: 1px solid rgba(255,255,255,0.07);
    border-radius: 10px;
    text-align: center;
}

.seller-stat__value {
    font-size: 16px;
    font-weight: 800;
    color: #fff;
    line-height: 1;
}

.seller-stat__label {
    font-size: 10px;
    color: rgba(255,255,255,0.4);
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.05em;
}

.seller-stat--green .seller-stat__value { color: #4caf7d; }

/* ── More Accounts Slider ─────────────────────────────── */
.more-accounts-section {
    margin-top: 48px;
    padding-top: 40px;
    border-top: 1px solid rgba(255,255,255,0.06);
}

.more-accounts-section .section-head {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 20px;
}

.more-accounts-section .section-title {
    font-size: 18px;
    font-weight: 700;
    color: #fff;
    display: flex;
    align-items: center;
    gap: 10px;
}

.more-accounts-section .section-title i {
    color: #6366f1;
}

.more-accounts-section .section-link {
    font-size: 13px;
    color: #6366f1;
    font-weight: 600;
    text-decoration: none;
    display: flex;
    align-items: center;
    gap: 5px;
    opacity: 0.85;
    transition: opacity .2s;
}

.more-accounts-section .section-link:hover { opacity: 1; }

.more-accounts-slider {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
    gap: 14px;
}

.more-account-card {
    background: rgba(255,255,255,0.04);
    border: 1px solid rgba(114,110,142,0.12);
    border-radius: 14px;
    overflow: hidden;
    transition: border-color .2s, transform .2s;
    text-decoration: none;
    display: block;
}

.more-account-card:hover {
    border-color: rgba(99,102,241,0.4);
    transform: translateY(-2px);
}

.more-account-card__img {
    width: 100%;
    height: 110px;
    object-fit: cover;
    display: block;
    background: rgba(255,255,255,0.03);
}

.more-account-card__img-placeholder {
    width: 100%;
    height: 110px;
    background: linear-gradient(135deg, rgba(99,102,241,0.08), rgba(139,92,246,0.05));
    display: flex;
    align-items: center;
    justify-content: center;
}

.more-account-card__img-placeholder img {
    width: 40px;
    height: auto;
    opacity: 0.5;
}

.more-account-card__body {
    padding: 10px 12px 12px;
}

.more-account-card__rank {
    font-size: 12px;
    font-weight: 700;
    color: #fff;
    display: flex;
    align-items: center;
    gap: 6px;
    margin-bottom: 4px;
}

.more-account-card__rank img {
    width: 18px;
    height: auto;
}

.more-account-card__meta {
    font-size: 10px;
    color: rgba(255,255,255,0.4);
    margin-bottom: 8px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.more-account-card__price {
    font-size: 14px;
    font-weight: 800;
    color: #fff;
}

/* ── Testimonials in view page ───────────────────────── */
.account-testimonials {
    margin-top: 48px;
    padding-top: 40px;
    border-top: 1px solid rgba(255,255,255,0.06);
}

.account-testimonials .section-head {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 20px;
}

.account-testimonials .section-title {
    font-size: 18px;
    font-weight: 700;
    color: #fff;
    display: flex;
    align-items: center;
    gap: 10px;
    margin-bottom: 0;
}

.account-testimonials .section-title i { color: #f5a623; }

/* Slider Controls */
.testimonials-controls {
    display: flex;
    align-items: center;
    gap: 8px;
}

.testimonials-controls .trev-btn {
    width: 34px;
    height: 34px;
    border-radius: 50%;
    border: 1px solid rgba(255,255,255,0.15);
    background: rgba(255,255,255,0.05);
    color: #fff;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 12px;
    transition: background .2s, border-color .2s;
    flex-shrink: 0;
}
.testimonials-controls .trev-btn:hover {
    background: rgba(99,102,241,0.25);
    border-color: rgba(99,102,241,0.6);
}

/* ── "View all" Button ── */
.trev-viewall {
    display: inline-flex;
    align-items: center;
    gap: 7px;
    padding: 8px 16px;
    background: linear-gradient(135deg, rgba(99,102,241,0.15), rgba(139,92,246,0.1));
    border: 1px solid rgba(99,102,241,0.35);
    border-radius: 100px;
    color: #a5b4fc;
    font-size: 12px;
    font-weight: 600;
    text-decoration: none;
    letter-spacing: 0.04em;
    transition: background .2s, border-color .2s, color .2s, transform .15s;
    white-space: nowrap;
}
.trev-viewall:hover {
    background: linear-gradient(135deg, rgba(99,102,241,0.28), rgba(139,92,246,0.2));
    border-color: rgba(99,102,241,0.65);
    color: #c7d2fe;
    transform: translateY(-1px);
}
.trev-viewall i {
    font-size: 10px;
    transition: transform .2s;
}
.trev-viewall:hover i {
    transform: translateX(3px);
}

/* ── "View all" also for saf section ── */
.saf-viewall {
    display: inline-flex !important;
    align-items: center;
    gap: 7px;
    padding: 7px 14px !important;
    background: linear-gradient(135deg, rgba(99,102,241,0.15), rgba(139,92,246,0.1)) !important;
    border: 1px solid rgba(99,102,241,0.35) !important;
    border-radius: 100px !important;
    color: #a5b4fc !important;
    font-size: 12px !important;
    font-weight: 600 !important;
    text-decoration: none !important;
    letter-spacing: 0.04em !important;
    transition: background .2s, border-color .2s, color .2s, transform .15s !important;
    white-space: nowrap !important;
    margin-left: 6px !important;
    opacity: 1 !important;
}
.saf-viewall:hover {
    background: linear-gradient(135deg, rgba(99,102,241,0.28), rgba(139,92,246,0.2)) !important;
    border-color: rgba(99,102,241,0.65) !important;
    color: #c7d2fe !important;
    transform: translateY(-1px) !important;
}

/* ── Testimonial Slider Track ── */
.testimonials-slider-wrap {
    overflow: hidden;
    position: relative;
    width: 100%;
}

.testimonials-slider {
    display: flex;
    flex-wrap: nowrap;          /* CRITICAL – karten nicht umbrechen */
    gap: 14px;
    will-change: transform;
    cursor: grab;
    user-select: none;
    /* Kein transition hier – wird per JS gesetzt */
}
.testimonials-slider:active { cursor: grabbing; }

.testimonials-slider .testimonial-card {
    /* Breite wird komplett per JS gesetzt – kein flex shorthand hier */
    flex-shrink: 0;
    min-width: 0;
    box-sizing: border-box;
}

.testimonial-card {
    background: rgba(255,255,255,0.03);
    border: 1px solid rgba(255,255,255,0.07);
    border-radius: 16px;
    padding: 20px;
    display: flex;
    flex-direction: column;
    gap: 12px;
    transition: border-color .2s, transform .2s;
    position: relative;
    overflow: hidden;
}

.testimonial-card::before {
    content: '"';
    position: absolute;
    top: -4px;
    right: 16px;
    font-size: 72px;
    line-height: 1;
    color: rgba(99,102,241,0.12);
    font-family: Georgia, serif;
    pointer-events: none;
}

.testimonial-card:hover {
    border-color: rgba(99,102,241,0.3);
    transform: translateY(-2px);
}

.testimonial-card__stars {
    display: flex;
    gap: 3px;
    color: #f5a623;
    font-size: 12px;
}

.testimonial-card__text {
    font-size: 13px;
    color: rgba(255,255,255,0.75);
    line-height: 1.65;
    flex: 1;
}

.testimonial-card__author {
    display: flex;
    align-items: center;
    gap: 10px;
    padding-top: 12px;
    border-top: 1px solid rgba(255,255,255,0.06);
    margin-top: auto;
}

.testimonial-card__author-avatar {
    width: 36px;
    height: 36px;
    border-radius: 50%;
    object-fit: cover;
    flex-shrink: 0;
    border: 2px solid rgba(99,102,241,0.3);
}

.testimonial-card__author-info {}

.testimonial-card__author-name {
    font-size: 12px;
    font-weight: 700;
    color: #fff;
}

.testimonial-card__author-rank {
    font-size: 10px;
    color: rgba(255,255,255,0.4);
    margin-top: 1px;
}

/* Slider dots */
.testimonials-dots {
    display: flex;
    justify-content: center;
    gap: 6px;
    margin-top: 16px;
}
.testimonials-dots .dot {
    width: 6px;
    height: 6px;
    border-radius: 50%;
    background: rgba(255,255,255,0.15);
    cursor: pointer;
    transition: background .2s, transform .2s;
}
.testimonials-dots .dot.active {
    background: #6366f1;
    transform: scale(1.3);
}

@media (max-width: 767px) {

    /* ── Layout: Spalten stacken ── */
    .view-account-page .layout {
        flex-direction: column !important;
    }
    .view-account-page .layout .left,
    .view-account-page .layout .right {
        width: 100% !important;
        max-width: 100% !important;
        min-width: 0 !important;
        flex-shrink: unset !important;
        overflow: visible !important;
    }

    /* ── Title: Größe reduzieren ── */
    .view-account-page .title {
        font-size: 4vw !important;
        gap: 2.5vw !important;
    }
    .view-account-page .title .rank-icon {
        padding: 2vw !important;
    }
    .view-account-page .title .rank-icon img {
        height: 7vw !important;
    }

    /* ── Seller Profile Card (Mobile inject) ── */
    .seller-profile-card--mobile-inject {
        display: flex !important;
        flex-direction: row !important;
        align-items: center !important;
        justify-content: space-between !important;
        padding: 14px 16px !important;
        border-radius: 12px !important;
        margin-bottom: 16px !important;
        gap: 10px !important;
        flex-wrap: wrap !important;
    }
    .seller-profile-card--mobile-inject .seller-profile-card__stats {
        width: auto !important;
        justify-content: flex-end !important;
        gap: 8px !important;
    }
    .seller-profile-card--mobile-inject .seller-stat {
        padding: 6px 10px !important;
    }
    .right .seller-profile-card {
        display: none !important;
    }

    /* ── Checkout: volle Breite ── */
    .view-account-page .card#hide-sticky {
        width: 100% !important;
        box-sizing: border-box !important;
    }
    .view-account-page .totals {
        flex-direction: column !important;
        align-items: stretch !important;
        gap: 12px !important;
        margin-top: 20px !important;
    }
    .view-account-page .totals form {
        width: 100% !important;
    }
    .view-account-page .totals .btn {
        width: 100% !important;
        text-align: center !important;
        justify-content: center !important;
    }

    /* ── Testimonials ── */
    .account-testimonials--left { display: none !important; }
    .account-testimonials--right { display: block !important; margin-top: 20px; }
    .account-testimonials { overflow: hidden; }

    /* ── Seller card misc ── */
    .seller-profile-card { flex-direction: column; align-items: flex-start; }
    .seller-profile-card__stats { width: 100%; justify-content: flex-start; }
    .more-accounts-slider { grid-template-columns: repeat(2, 1fr); }
    .testimonials-controls { gap: 6px; }
    .trev-viewall { padding: 6px 12px; font-size: 11px; }

    /* ── Account Cards im SAF-Slider: Shop-Page Stil ── */
    .saf-slide .account-card {
        display: flex !important;
        flex-direction: column !important;
        grid-template-columns: unset !important;
        grid-template-rows: unset !important;
        border-radius: 5.116vw !important;
        border: 0.698vw solid rgba(114,110,142,0.1) !important;
        overflow: visible !important;
        background-color: #0a0a18 !important;
        padding: 6.977vw !important;
        position: relative !important;
    }
    .saf-slide .account-card .cover-link {
        display: flex !important;
        flex-direction: column !important;
    }
    .saf-slide .account-card .title {
        font-size: 4.186vw !important;
        font-weight: 500 !important;
        display: flex !important;
        align-items: center !important;
        gap: 2.326vw !important;
        margin-bottom: 0 !important;
        white-space: normal !important;
        -webkit-line-clamp: unset !important;
        overflow: visible !important;
        grid-column: unset !important;
        grid-row: unset !important;
    }
    .saf-slide .account-card .title img,
    .saf-slide .account-card .title .rank-icon img {
        height: 9.302vw !important;
        width: auto !important;
    }
    .saf-slide .account-card .excerpt {
        margin: 3.488vw 0 2.326vw !important;
        font-size: 3.256vw !important;
        grid-column: unset !important;
        grid-row: unset !important;
        -webkit-line-clamp: unset !important;
    }
    .saf-slide .account-card .image-box {
        position: relative !important;
        margin: 4.651vw 0 !important;
        border-radius: 2.326vw !important;
        height: auto !important;
        overflow: hidden !important;
        grid-column: unset !important;
        grid-row: unset !important;
    }
    .saf-slide .account-card .image-box > img {
        max-height: 58.14vw !important;
        height: auto !important;
        width: 100% !important;
        object-fit: cover !important;
    }
    .saf-slide .account-card .highlights {
        gap: 2.326vw !important;
        flex-wrap: wrap !important;
        margin-bottom: 4.651vw !important;
        margin-top: 0 !important;
        grid-column: unset !important;
        grid-row: unset !important;
    }
    .saf-slide .account-card .highlights .badge {
        font-size: 3.256vw !important;
        padding: 1.163vw 2.326vw !important;
        border-radius: 1.163vw !important;
    }
    .saf-slide .account-card .totals {
        display: flex !important;
        align-items: center !important;
        justify-content: space-between !important;
        flex-direction: row !important;
        flex-wrap: nowrap !important;
        margin-top: auto !important;
        grid-column: unset !important;
        grid-row: unset !important;
    }
    .saf-slide .account-card .totals .price-eur {
        font-size: 6.512vw !important;
        font-weight: 800 !important;
        white-space: nowrap !important;
        flex-shrink: 0 !important;
    }
    .saf-slide .account-card .totals .btn {
        padding: 2.326vw 4.651vw !important;
        font-size: 3.721vw !important;
        width: auto !important;
        flex-shrink: 0 !important;
    }
    .saf-slide .account-card .delivery-type {
        font-size: 4.651vw !important;
        position: absolute !important;
        top: 6.977vw !important;
        right: 6.977vw !important;
    }
    .saf-slide .seller-info {
        padding: 8px 10px !important;
        border-radius: 8px !important;
        margin-top: 12px !important;
    }
    .saf-slide .seller-info__avatar { width: 22px !important; height: 22px !important; }
    .saf-slide .seller-info__name { font-size: 11px !important; }
    .saf-slide .seller-info__sold { font-size: 10px !important; }
}
@media (min-width: 768px) {
    .seller-profile-card--mobile-inject { display: none !important; }
    .account-testimonials--right { display: none !important; }
    .account-testimonials--left { display: block; }
}

/* ── Full-width Seller Accounts Slider ── */
.seller-accounts-fullwidth {
    background: rgba(255,255,255,0.02);
    border-top: 1px solid rgba(255,255,255,0.06);
    border-bottom: 1px solid rgba(255,255,255,0.06);
    padding: 2.5vw 0;
    margin-bottom: 0;
}

.seller-accounts-fullwidth__inner {
    max-width: 85%;
    margin: 0 auto;
}

.seller-accounts-fullwidth__head {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 1.25vw;
}

.seller-accounts-fullwidth__title {
    font-size: 1.15vw;
    font-weight: 700;
    color: #fff;
    display: flex;
    align-items: center;
    gap: 0.625vw;
}

.seller-accounts-fullwidth__title i { color: #6366f1; }
.seller-accounts-fullwidth__title span { color: #6366f1; }

.seller-accounts-fullwidth__controls {
    display: flex;
    align-items: center;
    gap: 0.521vw;
}

.saf-prev, .saf-next {
    width: 2.083vw;
    height: 2.083vw;
    border-radius: 50%;
    border: 1px solid rgba(255,255,255,0.15);
    background: rgba(255,255,255,0.06);
    color: #fff;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.729vw;
    transition: background .2s, border-color .2s;
}

.saf-prev:hover, .saf-next:hover {
    background: rgba(99,102,241,0.25);
    border-color: rgba(99,102,241,0.6);
}

.saf-viewall {
    font-size: 0.729vw;
    font-weight: 600;
    color: #6366f1;
    text-decoration: none;
    display: flex;
    align-items: center;
    gap: 0.26vw;
    margin-left: 0.521vw;
    opacity: 0.85;
    transition: opacity .2s;
}
.saf-viewall:hover { opacity: 1; }

/* Slick slide padding */
.saf-slider .slick-slide { padding: 0 0.521vw; }
.saf-slider .slick-list  { margin: 0 -0.521vw; cursor: grab; }
.saf-slider .slick-list:active { cursor: grabbing; }
.saf-slider.slick-initialized .slick-slide a { pointer-events: auto; }
.saf-slider.dragging .slick-slide a,
.saf-slider.dragging .slick-slide .cover-link { pointer-events: none; }

/* Slick: remove blue outline on focused slides */
.saf-slider .slick-slide { outline: none; }
.saf-slider .slick-slide:focus { outline: none; }

/* Single account layout */
.seller-accounts-fullwidth--single .saf-slider {
    display: flex;
    justify-content: flex-start;
}

.seller-accounts-fullwidth--single .saf-slide {
    width: 100%;
    max-width: 24rem;
}

.seller-accounts-fullwidth--single .slick-list,
.seller-accounts-fullwidth--single .slick-track {
    width: auto !important;
    transform: none !important;
}

/* Account card styles scoped to slider */
.saf-slide .account-card {
    border-radius: 1.146vw;
    border: 0.156vw solid rgba(114, 110, 142, 0.1);
    overflow: visible;
    background-color: rgba(255,255,255,.06);
    padding: 1.563vw;
    position: relative;
    display: flex;
    flex-direction: column;
}
.saf-slide .account-card .cover-link {
    flex: 1;
    display: flex;
    flex-direction: column;
    text-decoration: none;
    color: inherit;
}
.saf-slide .account-card .title {
    font-size: 0.938vw;
    font-weight: 500;
    display: flex;
    align-items: center;
    gap: 0.521vw;
    color: #fff;
    margin-bottom: 0.4vw;
}
.saf-slide .account-card .title .rank-icon,
.saf-slide .account-card .title img {
    height: 2.083vw !important;
    width: auto !important;
    display: inline-block !important;
    visibility: visible !important;
    opacity: 1 !important;
}
/* Override view-account-page rank-icon box styles */
.saf-slide .account-card .title .rank-icon {
    background-color: transparent !important;
    padding: 0 !important;
    border-radius: 0 !important;
}
.saf-slide .account-card .title .rank-icon img {
    height: 2.083vw !important;
    width: auto !important;
}

/* Hover border effect */
.saf-slide .account-card {
    transition: border-color 0.2s ease, transform 0.2s ease;
}
.saf-slide .account-card:hover {
    border-color: rgba(99, 102, 241, 0.6) !important;
}
.saf-slide .account-card .excerpt {
    font-size: 0.729vw;
    color: rgba(255,255,255,0.5);
    margin-bottom: 0.3vw;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
.saf-slide .account-card .image-box {
    position: relative;
    margin: 1.042vw 0;
    border-radius: 0.5vw;
    overflow: hidden;
}
.saf-slide .account-card .image-box > img {
    max-height: 13.021vw;
    width: 100%;
    object-fit: cover;
    display: block;
}
.saf-slide .account-card .image-box .badge {
    position: absolute;
    right: 0.781vw;
    bottom: 0.781vw;
    border-radius: 0.26vw;
    padding: 0.26vw 0.521vw;
    gap: 0.417vw;
    display: flex;
    align-items: center;
    background: rgba(0,0,0,0.65);
    color: #fff;
    font-size: 0.65vw;
}
.saf-slide .account-card .highlights {
    gap: 0.521vw;
    display: flex;
    align-items: center;
    flex-wrap: wrap;
    margin-bottom: 1.042vw;
}
.saf-slide .account-card .highlights .badge {
    font-size: 0.729vw;
    background-color: rgba(99, 102, 241, 0.3);
    color: #fff;
    gap: 0.417vw;
    display: inline-flex;
    align-items: center;
    border-radius: 0.26vw;
    padding: 0.26vw 0.521vw;
}
.saf-slide .account-card .totals {
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: nowrap;
    gap: 8px;
    margin-top: auto;
}
.saf-slide .account-card .totals .price-eur {
    font-size: 1.458vw;
    font-weight: 800;
    color: #fff;
    flex-shrink: 0;
    white-space: nowrap;
}
.saf-slide .account-card .totals .btn {
    padding: 0.521vw 1.042vw;
    font-size: 0.833vw;
    flex-shrink: 0;
    width: auto;
    white-space: nowrap;
}
.saf-slide .account-card .delivery-type {
    font-size: 1.042vw;
    position: absolute;
    top: 1.563vw;
    right: 1.563vw;
}
/* Seller info in slider */
.saf-slide .seller-info {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 0.417vw;
    padding: 0.573vw 0.729vw;
    margin-top: 0.833vw;
    background: rgba(255,255,255,0.04);
    border: 0.078vw solid rgba(255,255,255,0.07);
    border-radius: 0.573vw;
}
.saf-slide .seller-info__left {
    display: flex;
    align-items: center;
    gap: 0.417vw;
}
.saf-slide .seller-info__avatar {
    width: 1.563vw;
    height: 1.563vw;
    border-radius: 50%;
    object-fit: cover;
    border: 0.078vw solid rgba(99,102,241,0.5);
}
.saf-slide .seller-info__name {
    font-size: 0.729vw;
    font-weight: 700;
    color: #fff;
    display: flex;
    align-items: center;
    gap: 0.26vw;
}
.saf-slide .seller-info__verified {
    color: #6366f1;
    font-size: 0.6vw;
}
.saf-slide .seller-info__right {
    display: flex;
    align-items: center;
    gap: 0.417vw;
}
.saf-slide .seller-info__sold {
    font-size: 0.625vw;
    font-weight: 600;
    color: rgba(255,255,255,0.5);
    padding: 0.156vw 0.417vw;
    background: rgba(255,255,255,0.05);
    border-radius: 0.26vw;
}

/* Mobile */
@media (max-width: 767px) {
    .saf-slide .account-card { border-radius: 12px; padding: 14px; }
    .saf-slide .account-card .title { font-size: 12px; gap: 6px; }
    .saf-slide .account-card .title img { height: 22px; }
    .saf-slide .account-card .image-box > img { max-height: 140px; }
    .saf-slide .account-card .highlights .badge { font-size: 10px; padding: 3px 6px; }
    .saf-slide .account-card .totals .price-eur { font-size: 16px; }
    .saf-slide .account-card .totals .btn { font-size: 12px; padding: 7px 12px; }
    .saf-slide .seller-info { padding: 8px 10px; border-radius: 8px; }
    .saf-slide .seller-info__avatar { width: 22px; height: 22px; }
    .saf-slide .seller-info__name { font-size: 11px; }
    .saf-slide .seller-info__sold { font-size: 10px; }
}

/* Fix totals layout inside slider */
.saf-slide .account-card .totals {
    display: flex !important;
    align-items: center !important;
    justify-content: space-between !important;
    flex-wrap: nowrap !important;
    gap: 8px !important;
}
.saf-slide .account-card .totals .price-eur {
    flex-shrink: 0;
    white-space: nowrap;
}
.saf-slide .account-card .totals .btn {
    flex-shrink: 0;
    width: auto !important;
    white-space: nowrap;
}



@media (max-width: 767px) {
    .seller-accounts-fullwidth { padding: 24px 0; }
    .seller-accounts-fullwidth__inner { max-width: 92%; }
    .seller-accounts-fullwidth__title { font-size: 15px; gap: 8px; }
    .saf-prev, .saf-next { width: 32px; height: 32px; font-size: 11px; }
    .saf-viewall { font-size: 11px; }
    .saf-slider .slick-slide { padding: 0 6px; }
    .saf-slider .slick-list  { margin: 0 -6px; }

    .seller-accounts-fullwidth--single .saf-slide {
        max-width: 100%;
    }
}
</style>
<?= $this->stop() ?>

<header>
    <div class="content">
        <h1><?= t('Buy League of Legends Account') ?></h1>
        <p><?= t('Buy this high-quality League of Legends account today and start playing instantly.') ?></p>
    </div>
</header>
<div class="container">
    <?= $this->insert('website/components/marketplace-breadcrumbs', [
        'type' => 'accounts',
        'gameSlug' => 'league-of-legends',
        'gameName' => 'League of Legends',
        'currentTitle' => (string)($account['title'] ?? ''),
    ]) ?>
    <h3 class="title">
        <div class="rank-icon">
            <img src="<?= util_rank_img('lol', 'mini', $account['current_rank']) ?>">
        </div>
        <?= $account['title'] ?>
    </h3>
    <div class="highlights">
        <div class="badge">
            <i class="fas fa-bolt"></i><?= t('Instant Account Delivery') ?>
        </div>
        <div class="badge">
            <i class="fas fa-shield-alt"></i><?= t('Free Warranty Support') ?>
        </div>
    </div>

    <div class="layout">
        <div class="left">
            <div class="card gallery-mobile">
                <div class="card-header gallery-header">
                    <h4>
                        <i class="fas fa-images me-2"></i><?= t('Gallery') ?>
                    </h4>
                    <div class="controls">
                        <button type="button" class="btn prev">
                            <i class="fas fa-chevron-left"></i>
                        </button>
                        <button type="button" class="btn next">
                            <i class="fas fa-chevron-right"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="gallery">
                        <?php foreach (json_decode($account['images']) as $image): ?>
                            <div class="slide">
                                <a href="<?= $image ?>">
                                    <img src="<?= $image ?>" alt="<?= $account['title'] ?>">
                                </a>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <?php if (!empty($seller)): ?>
            <!-- ── Sold By (Mobile only – between gallery and account details) ── -->
            <div class="seller-profile-card seller-profile-card--mobile-inject">
                <div class="seller-profile-card__left">
                    <?php if (!empty($seller['icon'])): ?>
                        <img src="<?= htmlspecialchars($seller['icon']) ?>" alt="<?= htmlspecialchars($seller['username']) ?>" class="seller-profile-card__avatar">
                    <?php else: ?>
                        <div class="seller-profile-card__avatar-placeholder">
                            <i class="fas fa-user"></i>
                        </div>
                    <?php endif; ?>
                    <div class="seller-profile-card__info">
                        <div class="seller-profile-card__label"><?= t('Sold by') ?></div>
                        <?php
                            $rankLabel = trim((string)($seller['rank'] ?? ''));
                            $rankIconClass = trim((string)($seller['rank_icon'] ?? ''));
                            if ($rankIconClass === '' && $rankLabel !== '') {
                                $rankMap = [
                                    'beginner'      => 'fa-solid fa-badge-check text-slate-400',
                                    'expert seller' => 'fa-solid fa-badge-check text-emerald-500',
                                    'pro seller'    => 'fa-solid fa-badge-check text-violet-500',
                                    'mythic seller' => 'fa-solid fa-badge-check text-amber-400',
                                ];
                                $rankIconClass = $rankMap[strtolower($rankLabel)] ?? 'fa-solid fa-badge-check text-slate-400';
                            }

                            $rankIconColor = '#94a3b8';
                            if (stripos($rankIconClass, 'text-emerald-500') !== false) {
                                $rankIconColor = '#22c55e';
                            } elseif (stripos($rankIconClass, 'text-violet-500') !== false) {
                                $rankIconColor = '#8b5cf6';
                            } elseif (stripos($rankIconClass, 'text-amber-400') !== false) {
                                $rankIconColor = '#fbbf24';
                            } elseif (stripos($rankIconClass, 'text-slate-400') !== false) {
                                $rankIconColor = '#94a3b8';
                            }
                        ?>
                        <a href="/sellers/<?= htmlspecialchars($seller['username']) ?>" class="seller-profile-card__name" style="text-decoration:none;color:inherit;">
                            <?= htmlspecialchars($seller['username']) ?>
                            <?php if ($rankLabel !== ''): ?>
                                <i class="fa-solid fa-badge-check seller-profile-card__rank-icon" style="color:<?= htmlspecialchars($rankIconColor, ENT_QUOTES) ?>;" title="<?= htmlspecialchars($rankLabel, ENT_QUOTES) ?>"></i>
                            <?php elseif (!empty($seller['is_active'])): ?>
                                <i class="fas fa-circle-check verified" title="Verified Seller"></i>
                            <?php endif; ?>
                            <?php if ($sellerTotalSoldDisplay >= 10): ?>
                                <span style="display:inline-flex;align-items:center;padding:3px 8px;margin-left:6px;border-radius:999px;font-size:11px;font-weight:800;line-height:1;background:rgba(99,102,241,0.18);border:1px solid rgba(99,102,241,0.35);color:#c7d2fe;">
                                    Recommended
                                </span>
                            <?php endif; ?>
                        </a>
                    </div>
                </div>
                <div class="seller-profile-card__stats">
                    <div class="seller-stat">
                        <div class="seller-stat__value"><?= number_format($sellerTotalSoldDisplay) ?></div>
                        <div class="seller-stat__label"><?= t('Sold') ?></div>
                    </div>
                    <div class="seller-stat seller-stat--green">
                        <div class="seller-stat__value"><i class="fas fa-thumbs-up" style="font-size:13px;"></i></div>
                        <div class="seller-stat__label"><?= t('Trusted') ?></div>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <div class="card">
                <div class="card-header">
                    <h4>
                        <i class="fas fa-info-circle me-2"></i><?= t('Account Details') ?>
                    </h4>
                </div>
                <div class="card-body">
                    <p class="description">
                        <?= nl2br($account['description']) ?>
                    </p>

                    <div class="features">
                        <div class="feature">
                            <h6><?= t('Current Rank') ?></h6>
                            <span>
                                <img src="<?= util_rank_img('lol', 'mini', $account['current_rank']) ?>" class="img-fluid" style="width: 25px; height: auto;">
                                <?= format_lol_rank_display($account['current_rank'], $account['current_lp'], $account['current_division']) ?>
                            </span>
                        </div>
                        <div class="feature">
                            <h6><?= t('Flex Rank') ?></h6>
                            <span>
                                <img src="<?= util_rank_img('lol', 'mini', $account['flex_rank']) ?>" class="img-fluid" style="width: 25px; height: auto;">
                                <?= format_lol_rank_display($account['flex_rank'], $account['flex_lp'], $account['flex_division']) ?>
                            </span>
                        </div>
                        <div class="feature">
                            <h6><?= t('Previous Rank') ?></h6>
                            <span>
                                <img src="<?= util_rank_img('lol', 'mini', $account['previous_rank']) ?>" class="img-fluid" style="width: 25px; height: auto;">
                                <?= format_lol_rank_display($account['previous_rank'], $account['previous_lp'], $account['previous_division']) ?>
                            </span>
                        </div>
                        <div class="feature">
                            <h6><?= t('Server') ?></h6>
                            <span>
                                <i class="text-primary me-2 fs-5 fas fa-server"></i>
                                <?= util_format_server($account['server']) ?>
                            </span>
                        </div>
                        <div class="feature">
                            <h6><?= t('Level') ?></h6>
                            <span>
                                <i class="text-primary me-2 fs-5 fas fa-arrow-turn-up"></i>
                                <?= $account['level'] ?>
                            </span>
                        </div>
                        <div class="feature">
                            <h6><?= t('Blue Essence') ?></h6>
                            <span>
                                <i class="text-primary me-2 fs-5 fas fa-gem"></i>
                                <?= $account['blue_essence'] ?>
                            </span>
                        </div>
                        <div class="feature">
                            <h6><?= t('Riot Points') ?></h6>
                            <span>
                                <i class="text-primary me-2 fs-5 fas fa-hand-back-fist"></i>
                                <?= $account['riot_points'] ?>
                            </span>
                        </div>
                        <div class="feature">
                            <h6><?= t('Champions') ?></h6>
                            <span>
                                <i class="text-primary me-2 fs-5 fas fa-helmet-battle"></i>
                                <?= count($champions) ?>
                            </span>
                        </div>
                        <div class="feature">
                            <h6><?= t('Skins') ?></h6>
                            <span>
                                <i class="text-primary me-2 fs-5 fas fa-masks-theater"></i>
                                <?= count($skins) ?>
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <div class="nav-tabs">
                        <a href="#champs-list" class="active">
                            <i class="fas fa-helmet-battle me-1"></i><?= t('Champions') ?><span class="count-badge"><?= count($champions) ?></span>
                        </a>
                        <a href="#skins-list">
                            <i class="fas fa-masks-theater me-1"></i><?= t('Skins') ?><span class="count-badge"><?= count($skins) ?></span>
                        </a>
                        <a href="#roles-list">
                            <i class="fas fa-users me-1"></i><?= t('Roles') ?><span class="count-badge"><?= count($roles) ?></span>
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="tab-content">
                        <div id="champs-list" class="tab-pane active">
                            <div class="champs">
                                <?php if (empty($champions)): ?>
                                    <div class="empty-hint"><?= t('For more Informations check the Images') ?></div>
                                <?php else: ?>
                                    <?php foreach ($champions as $champion): ?>
                                        <div class="champ">
                                            <img src="<?= LOL_CHAMP_URL ?>/<?= $champion ?>.png">
                                            <small><?= ucfirst($champion) ?></small>
                                        </div>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div id="skins-list" class="tab-pane">
                            <div class="skins">
                                <?php if (empty($skins)): ?>
                                    <div class="empty-hint"><?= t('For more Informations check the Images') ?></div>
                                <?php else: ?>
                                    <?php foreach ($skins as $skin): ?>
                                        <div class="skin">
                                            <img src="<?= 'https://ddragon.leagueoflegends.com/cdn/img/champion/loading/' . $skin . '.jpg' ?>" class="img-fluid rounded" data-tooltip="<?= util_get_skin_label($skin) ?>">
                                        </div>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div id="roles-list" class="tab-pane">
                            <div class="roles">
                                <?php if (empty($roles)): ?>
                                    <div class="empty-hint"><?= t('For more Informations check the Images') ?></div>
                                <?php else: ?>
                                    <?php foreach ($roles as $role): ?>
                                        <div class="role">
                                            <img src="<?= ASSET_URL ?>/core/main/img/lol/roles/<?= ucfirst($role) ?>.svg">
                                            <small><?= ucfirst($role) ?></small>
                                        </div>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>



            <!-- ── Testimonials ── -->
            <div class="account-testimonials account-testimonials--left">
                <div class="section-head">
                    <div class="section-title">
                        <i class="fas fa-star"></i>
                        <?= t('What our customers say') ?>
                    </div>
                    <div class="testimonials-controls">
                        <button type="button" class="trev-btn trev-prev" aria-label="Previous">
                            <i class="fas fa-chevron-left"></i>
                        </button>
                        <button type="button" class="trev-btn trev-next" aria-label="Next">
                            <i class="fas fa-chevron-right"></i>
                        </button>
                        <a href="/lol/accounts" class="trev-viewall"><?= t('View all') ?> <i class="fas fa-arrow-right"></i></a>
                    </div>
                </div>
                <div class="testimonials-slider-wrap">
                    <div class="testimonials-slider">
                        <div class="testimonial-card">
                            <div class="testimonial-card__stars">
                                <i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i>
                            </div>
                            <div class="testimonial-card__text"><?= t('Division boost with role/champ prefs. Booster picked up in 20 minutes and finished under 24h. Clean execution and constant dashboard updates.') ?></div>
                            <div class="testimonial-card__author">
                                <img src="<?= ICON_URL ?>/6b008b42-9969-4cae-a0b0-0e859abefaf3.png" alt="" class="testimonial-card__author-avatar">
                                <div class="testimonial-card__author-info">
                                    <div class="testimonial-card__author-name">J****</div>
                                    <div class="testimonial-card__author-rank">Gold II ➠ Platinum III · EUW</div>
                                </div>
                            </div>
                        </div>
                        <div class="testimonial-card">
                            <div class="testimonial-card__stars">
                                <i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i>
                            </div>
                            <div class="testimonial-card__text"><?= t('Bought a verified ranked account. Instant delivery, hand-leveled, clean history. Support helped with email link + 2FA in minutes.') ?></div>
                            <div class="testimonial-card__author">
                                <img src="<?= ICON_URL ?>/790af80a-47ab-4450-95a6-7953d67939c6.png" alt="" class="testimonial-card__author-avatar">
                                <div class="testimonial-card__author-info">
                                    <div class="testimonial-card__author-name">S*****</div>
                                    <div class="testimonial-card__author-rank">Ranked Account · NA</div>
                                </div>
                            </div>
                        </div>
                        <div class="testimonial-card">
                            <div class="testimonial-card__stars">
                                <i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i>
                            </div>
                            <div class="testimonial-card__text"><?= t('Needed a clean smurf for duo. ARAM leveled, normal MMR intact, quick delivery. Perfect for fresh placements.') ?></div>
                            <div class="testimonial-card__author">
                                <img src="<?= ICON_URL ?>/7d0ab91d-d9fb-4da6-9a9a-c6f39b9327d5.jpeg" alt="" class="testimonial-card__author-avatar">
                                <div class="testimonial-card__author-info">
                                    <div class="testimonial-card__author-name">T*****</div>
                                    <div class="testimonial-card__author-rank">Hand-Leveled Smurf · EUW</div>
                                </div>
                            </div>
                        </div>
                        <div class="testimonial-card">
                            <div class="testimonial-card__stars">
                                <i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i>
                            </div>
                            <div class="testimonial-card__text"><?= t('Super smooth transaction. Got my Platinum account within 5 minutes of payment. The seller was very responsive and helped me through verification steps.') ?></div>
                            <div class="testimonial-card__author">
                                <img src="<?= ICON_URL ?>/6b008b42-9969-4cae-a0b0-0e859abefaf3.png" alt="" class="testimonial-card__author-avatar">
                                <div class="testimonial-card__author-info">
                                    <div class="testimonial-card__author-name">M*****</div>
                                    <div class="testimonial-card__author-rank">Platinum IV · EUNE</div>
                                </div>
                            </div>
                        </div>
                        <div class="testimonial-card">
                            <div class="testimonial-card__stars">
                                <i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i>
                            </div>
                            <div class="testimonial-card__text"><?= t('Second time buying here. Always fast, always clean accounts. The warranty feature is great – had a small issue and support fixed it same day.') ?></div>
                            <div class="testimonial-card__author">
                                <img src="<?= ICON_URL ?>/790af80a-47ab-4450-95a6-7953d67939c6.png" alt="" class="testimonial-card__author-avatar">
                                <div class="testimonial-card__author-info">
                                    <div class="testimonial-card__author-name">R*****</div>
                                    <div class="testimonial-card__author-rank">Diamond IV · EUW</div>
                                </div>
                            </div>
                        </div>
                        <div class="testimonial-card">
                            <div class="testimonial-card__stars">
                                <i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i>
                            </div>
                            <div class="testimonial-card__text"><?= t('Level 30 account with tons of BE. Everything as described. Quick delivery and the dashboard was super easy to use. Highly recommend.') ?></div>
                            <div class="testimonial-card__author">
                                <img src="<?= ICON_URL ?>/7d0ab91d-d9fb-4da6-9a9a-c6f39b9327d5.jpeg" alt="" class="testimonial-card__author-avatar">
                                <div class="testimonial-card__author-info">
                                    <div class="testimonial-card__author-name">K*****</div>
                                    <div class="testimonial-card__author-rank">Unranked Smurf · TR</div>
                                </div>
                            </div>
                        </div>
                        <div class="testimonial-card">
                            <div class="testimonial-card__stars">
                                <i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i>
                            </div>
                            <div class="testimonial-card__text"><?= t('Legit site. Account came with all the champs listed, no bans or flags. Played 20+ games already, zero issues. Will buy again for my friend.') ?></div>
                            <div class="testimonial-card__author">
                                <img src="<?= ICON_URL ?>/6b008b42-9969-4cae-a0b0-0e859abefaf3.png" alt="" class="testimonial-card__author-avatar">
                                <div class="testimonial-card__author-info">
                                    <div class="testimonial-card__author-name">F*****</div>
                                    <div class="testimonial-card__author-rank">Gold III · NA</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="testimonials-dots"></div>
            </div>

        </div><!-- /.left -->

        <div class="right">
            <?php if (!empty($seller)): ?>
            <!-- ── Seller Profile Card ── -->
            <div class="seller-profile-card">
                <div class="seller-profile-card__left">
                    <?php if (!empty($seller['icon'])): ?>
                        <img src="<?= htmlspecialchars($seller['icon']) ?>" alt="<?= htmlspecialchars($seller['username']) ?>" class="seller-profile-card__avatar">
                    <?php else: ?>
                        <div class="seller-profile-card__avatar-placeholder">
                            <i class="fas fa-user"></i>
                        </div>
                    <?php endif; ?>
                    <div class="seller-profile-card__info">
                        <div class="seller-profile-card__label"><?= t('Sold by') ?></div>
                        <?php
                            $rankLabel = trim((string)($seller['rank'] ?? ''));
                            $rankIconClass = trim((string)($seller['rank_icon'] ?? ''));
                            if ($rankIconClass === '' && $rankLabel !== '') {
                                $rankMap = [
                                    'beginner'      => 'fa-solid fa-badge-check text-slate-400',
                                    'expert seller' => 'fa-solid fa-badge-check text-emerald-500',
                                    'pro seller'    => 'fa-solid fa-badge-check text-violet-500',
                                    'mythic seller' => 'fa-solid fa-badge-check text-amber-400',
                                ];
                                $rankIconClass = $rankMap[strtolower($rankLabel)] ?? 'fa-solid fa-badge-check text-slate-400';
                            }

                            $rankIconColor = '#94a3b8';
                            if (stripos($rankIconClass, 'text-emerald-500') !== false) {
                                $rankIconColor = '#22c55e';
                            } elseif (stripos($rankIconClass, 'text-violet-500') !== false) {
                                $rankIconColor = '#8b5cf6';
                            } elseif (stripos($rankIconClass, 'text-amber-400') !== false) {
                                $rankIconColor = '#fbbf24';
                            } elseif (stripos($rankIconClass, 'text-slate-400') !== false) {
                                $rankIconColor = '#94a3b8';
                            }
                        ?>
                        <a href="/sellers/<?= htmlspecialchars($seller['username']) ?>" class="seller-profile-card__name" style="text-decoration:none;color:inherit;">
                            <?= htmlspecialchars($seller['username']) ?>
                            <?php if ($rankLabel !== ''): ?>
                                <i class="fa-solid fa-badge-check seller-profile-card__rank-icon" style="color:<?= htmlspecialchars($rankIconColor, ENT_QUOTES) ?>;" title="<?= htmlspecialchars($rankLabel, ENT_QUOTES) ?>"></i>
                            <?php elseif (!empty($seller['is_active'])): ?>
                                <i class="fas fa-circle-check verified" title="Verified Seller"></i>
                            <?php endif; ?>
                            <?php if ($sellerTotalSoldDisplay >= 10): ?>
                                <span style="display:inline-flex;align-items:center;padding:3px 8px;margin-left:6px;border-radius:999px;font-size:11px;font-weight:800;line-height:1;background:rgba(99,102,241,0.18);border:1px solid rgba(99,102,241,0.35);color:#c7d2fe;">
                                    Recommended
                                </span>
                            <?php endif; ?>
                        </a>
                    </div>
                </div>
                <div class="seller-profile-card__stats">
                    <div class="seller-stat">
                        <div class="seller-stat__value"><?= number_format($sellerTotalSoldDisplay) ?></div>
                        <div class="seller-stat__label"><?= t('Sold') ?></div>
                    </div>
                    <div class="seller-stat seller-stat--green">
                        <div class="seller-stat__value"><i class="fas fa-thumbs-up" style="font-size:13px;"></i></div>
                        <div class="seller-stat__label"><?= t('Trusted') ?></div>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <div class="card" id="hide-sticky">
                <div class="card-header">
                    <h4>
                        <i class="fas fa-shopping-cart me-2"></i><?= t('Checkout') ?>
                    </h4>
                </div>
                <div class="card-body">
                    <?php if ($account['delivery_type'] == 'instant'): ?>
                        <p class="tagline">
                            <?= t('Right after checkout, your account details will be delivered. No waiting, no stress.') ?>
                        </p>
                    <?php else: ?>
                        <p class="tagline"><?= t('Your account will be delivered manually by our team. You can claim it instantly via Live Chat or receive the login details by email within 1 hour after your purchase.') ?></p>
                    <?php endif; ?>

                    <ul class="checkout-features">
                        <?php if ($account['delivery_type'] == 'instant'): ?>
                            <li><i class="fas fa-badge-check"></i><?= t('Ready to play in seconds') ?></li>
                            <li><i class="fas fa-badge-check"></i><?= t('Full access (email & password changeable)') ?></li>
                            <li><i class="fas fa-badge-check"></i><?= t('Free warranty and support') ?></li>
                        <?php else: ?>
                            <li><i class="fas fa-badge-check"></i><?= t('Secure manual delivery process') ?></li>
                            <li><i class="fas fa-badge-check"></i><?= t('Claim via Live Chat for fastest access') ?></li>
                            <li><i class="fas fa-badge-check"></i><?= t('Login details also sent to your email within 60 minutes') ?></li>
                        <?php endif; ?>
                    </ul>

                    <div class="totals">
                        <div class="price">
                            <?= htmlspecialchars($accountPriceDisplay['with_symbol']) ?>
                            <small class="text-dark fw-medium">
                                <?= $_SESSION['currency'] ?>
                            </small>
                        </div>
                        <form action="<?= AJAX_URL ?>" class="ajax-form">
                            <input type="hidden" name="action" value="prepare_lol_account_order">
                            <input type="hidden" name="account_id" value="<?= $account['id'] ?>">
                            <button type="submit" class="btn">
                                <span class="indicator-label">
                                    <i class="fas fa-shopping-cart me-2"></i><?= t('Buy Account Now') ?></span>
                                <span class="indicator-progress">
                                    <span class="loader"></span>
                                </span>
                            </button>
                            <div class="trust-badges">
                                <div class="trust-badge">
                                    <i class="fa-solid fa-shield-halved"></i>
                                    Secure Checkout
                                </div>
                                <div class="trust-badge">
                                    <i class="fa-solid fa-envelope-circle-check"></i>
                                    Mail Changeable
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- ── Testimonials (Mobile only – under checkout) ── -->
            <div class="account-testimonials account-testimonials--right">
                <div class="section-head">
                    <div class="section-title">
                        <i class="fas fa-star"></i>
                        <?= t('What our customers say') ?>
                    </div>
                    <div class="testimonials-controls">
                        <button type="button" class="trev-btn trev-prev-r" aria-label="Previous">
                            <i class="fas fa-chevron-left"></i>
                        </button>
                        <button type="button" class="trev-btn trev-next-r" aria-label="Next">
                            <i class="fas fa-chevron-right"></i>
                        </button>
                        <a href="/lol/accounts" class="trev-viewall"><?= t('View all') ?> <i class="fas fa-arrow-right"></i></a>
                    </div>
                </div>
                <div class="testimonials-slider-wrap">
                    <div class="testimonials-slider testimonials-slider-r">
                        <div class="testimonial-card">
                            <div class="testimonial-card__stars"><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i></div>
                            <div class="testimonial-card__text"><?= t('Division boost with role/champ prefs. Booster picked up in 20 minutes and finished under 24h. Clean execution and constant dashboard updates.') ?></div>
                            <div class="testimonial-card__author">
                                <img src="<?= ICON_URL ?>/6b008b42-9969-4cae-a0b0-0e859abefaf3.png" alt="" class="testimonial-card__author-avatar">
                                <div class="testimonial-card__author-info">
                                    <div class="testimonial-card__author-name">J****</div>
                                    <div class="testimonial-card__author-rank">Gold II ➠ Platinum III · EUW</div>
                                </div>
                            </div>
                        </div>
                        <div class="testimonial-card">
                            <div class="testimonial-card__stars"><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i></div>
                            <div class="testimonial-card__text"><?= t('Bought a verified ranked account. Instant delivery, hand-leveled, clean history. Support helped with email link + 2FA in minutes.') ?></div>
                            <div class="testimonial-card__author">
                                <img src="<?= ICON_URL ?>/790af80a-47ab-4450-95a6-7953d67939c6.png" alt="" class="testimonial-card__author-avatar">
                                <div class="testimonial-card__author-info">
                                    <div class="testimonial-card__author-name">S*****</div>
                                    <div class="testimonial-card__author-rank">Ranked Account · NA</div>
                                </div>
                            </div>
                        </div>
                        <div class="testimonial-card">
                            <div class="testimonial-card__stars"><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i></div>
                            <div class="testimonial-card__text"><?= t('Needed a clean smurf for duo. ARAM leveled, normal MMR intact, quick delivery. Perfect for fresh placements.') ?></div>
                            <div class="testimonial-card__author">
                                <img src="<?= ICON_URL ?>/7d0ab91d-d9fb-4da6-9a9a-c6f39b9327d5.jpeg" alt="" class="testimonial-card__author-avatar">
                                <div class="testimonial-card__author-info">
                                    <div class="testimonial-card__author-name">T*****</div>
                                    <div class="testimonial-card__author-rank">Hand-Leveled Smurf · EUW</div>
                                </div>
                            </div>
                        </div>
                        <div class="testimonial-card">
                            <div class="testimonial-card__stars"><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i></div>
                            <div class="testimonial-card__text"><?= t('Super smooth transaction. Got my Platinum account within 5 minutes. The seller was very responsive and helped me through verification steps.') ?></div>
                            <div class="testimonial-card__author">
                                <img src="<?= ICON_URL ?>/6b008b42-9969-4cae-a0b0-0e859abefaf3.png" alt="" class="testimonial-card__author-avatar">
                                <div class="testimonial-card__author-info">
                                    <div class="testimonial-card__author-name">M*****</div>
                                    <div class="testimonial-card__author-rank">Platinum IV · EUNE</div>
                                </div>
                            </div>
                        </div>
                        <div class="testimonial-card">
                            <div class="testimonial-card__stars"><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i></div>
                            <div class="testimonial-card__text"><?= t('Second time buying here. Always fast, always clean accounts. Warranty feature is great – had a small issue and support fixed it same day.') ?></div>
                            <div class="testimonial-card__author">
                                <img src="<?= ICON_URL ?>/790af80a-47ab-4450-95a6-7953d67939c6.png" alt="" class="testimonial-card__author-avatar">
                                <div class="testimonial-card__author-info">
                                    <div class="testimonial-card__author-name">R*****</div>
                                    <div class="testimonial-card__author-rank">Diamond IV · EUW</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="testimonials-dots testimonials-dots-r"></div>
            </div>

            <div class="card gallery-desktop">
                <div class="card-header gallery-header">
                    <h4>
                        <i class="fas fa-images me-2"></i><?= t('Gallery') ?>
                    </h4>
                    <div class="controls">
                        <button type="button" class="btn prev">
                            <i class="fas fa-chevron-left"></i>
                        </button>
                        <button type="button" class="btn next">
                            <i class="fas fa-chevron-right"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="gallery">
                        <?php foreach (json_decode($account['images']) as $image): ?>
                            <div class="slide">
                                <a href="<?= $image ?>">
                                    <img src="<?= $image ?>" alt="<?= $account['title'] ?>">
                                </a>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div><!-- /.right -->
    </div>
</div>

<?php if (!empty($seller_accounts)): ?>
<?php $sellerAccountsCount = count($seller_accounts); ?>
<div class="seller-accounts-fullwidth <?= $sellerAccountsCount === 1 ? 'seller-accounts-fullwidth--single' : '' ?>">
    <div class="seller-accounts-fullwidth__inner">
        <div class="seller-accounts-fullwidth__head">
            <div class="seller-accounts-fullwidth__title">
                <i class="fas fa-layer-group"></i>
                <?= t('More from') ?> <a href="/sellers/<?= htmlspecialchars($seller['username'] ?? '') ?>" style="color:#6366f1;text-decoration:none;"><?= htmlspecialchars($seller['username'] ?? '') ?></a>
            </div>
            <?php if ($sellerAccountsCount > 1): ?>
            <div class="seller-accounts-fullwidth__controls">
                <button type="button" class="saf-prev"><i class="fas fa-chevron-left"></i></button>
                <button type="button" class="saf-next"><i class="fas fa-chevron-right"></i></button>
                <a href="/lol/accounts" class="saf-viewall"><?= t('View all') ?> <i class="fas fa-arrow-right"></i></a>
            </div>
            <?php endif; ?>
        </div>
        <?php
            // Enrich seller_accounts with seller info so account-cards.php seller strip works
            $accounts_for_slider = array_map(function($sa) use ($seller, $accountCurrencyCode, $sellerTotalSoldDisplay) {
                $sa['seller_username']  = $seller['username'] ?? null;
                $sa['seller_icon']      = $seller['icon'] ?? null;
                $sa['seller_is_active'] = $seller['is_active'] ?? 0;
                $sa['seller_total_sales'] = $sellerTotalSoldDisplay;
                $sa['seller_sold']        = $sellerTotalSoldDisplay;
                $sa['seller_rating']    = null;
                $sa['seller_rank']      = $seller['rank'] ?? null;
                $sa['seller_rank_icon'] = $seller['rank_icon'] ?? null;
                $sa['price']            = account_view_convert_price_cents((int)($sa['price'] ?? 0), $accountCurrencyCode);
                return $sa;
            }, $seller_accounts);
        ?>
        <div class="saf-slider">
            <?php foreach ($accounts_for_slider as $_sa): ?>
            <div class="saf-slide">
                <?php echo $this->insert('website/components/accounts/account-cards', ['accounts' => [$_sa]]); ?>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>
<?php endif; ?>

<div class="sticky-button">
    <form action="<?= AJAX_URL ?>" class="ajax-form">
        <input type="hidden" name="action" value="prepare_lol_account_order">
        <input type="hidden" name="account_id" value="<?= $account['id'] ?>">
        <button type="submit" class="btn">
            <i class="fas fa-shopping-cart me-2"></i>
            Buy Account Now -
            <?= htmlspecialchars($accountPriceDisplay['with_symbol']) ?>
        </button>
    </form>
</div>

<?= $this->start('scripts') ?>
<script type="text/javascript" src="//cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick.min.js"></script>

<script>
    $(document).ready(function () {
        $('.gallery').slick({
            slidesToShow: 1,
            slidesToScroll: 1,
            arrows: true,
            fade: false,
            autoplay: false,
            infinite: false,
            dots: false,
            prevArrow: $('.prev'),
            nextArrow: $('.next'),
        });

        var $stickySection = $(".sticky-button");
        var $hideSticky = $("#hide-sticky");

        function checkVisibility() {
            var windowHeight = $(window).height();
            var elementTop = $hideSticky.offset().top;
            var elementBottom = elementTop + $hideSticky.outerHeight();
            var scrollTop = $(window).scrollTop();

            if (scrollTop + windowHeight > elementTop && scrollTop < elementBottom) {
                $stickySection.css({
                    transform: "translateY(100%)",
                    transition: "transform 0.3s ease-in-out"
                });
            } else {
                $stickySection.css({
                    transform: "translateY(0)",
                    transition: "transform 0.3s ease-in-out"
                });
            }
        }

        $(window).on("scroll", checkVisibility);
        checkVisibility();


        // Seller accounts slider
        if ($('.saf-slider').length && $('.saf-slider .saf-slide').length > 1) {
            // Prevent link clicks while dragging
            var safDragging = false;
            $('.saf-slider').on('mousedown touchstart', function(){ safDragging = false; })
                            .on('mousemove touchmove', function(){ safDragging = true; });
            $(document).on('click', '.saf-slider .cover-link', function(e){
                if (safDragging) { e.preventDefault(); safDragging = false; }
            });

            $('.saf-slider').slick({
                slidesToShow: 4,
                slidesToScroll: 1,
                arrows: true,
                infinite: false,
                dots: false,
                draggable: true,
                swipe: true,
                swipeToSlide: true,
                touchMove: true,
                touchThreshold: 5,
                speed: 400,
                cssEase: 'cubic-bezier(0.25, 0.46, 0.45, 0.94)',
                prevArrow: $('.saf-prev'),
                nextArrow: $('.saf-next'),
                responsive: [
                    { breakpoint: 1200, settings: { slidesToShow: 3 } },
                    { breakpoint: 900,  settings: { slidesToShow: 2 } },
                    { breakpoint: 600,  settings: { slidesToShow: 1 } }
                ]
            });
        }

        // ── Testimonial Slider ──────────────────────────────────────────
        function initTestimonialSlider(sliderEl, dotsEl, prevBtn, nextBtn) {
            if (!sliderEl) return;
            const cards = sliderEl.querySelectorAll('.testimonial-card');
            if (!cards.length) return;

            var current = 0;
            var cardWidth = 0;
            var isDragging = false;
            var startX = 0;
            var startTranslate = 0;
            var currentTranslate = 0;
            var GAP = 14;

            function getWrapWidth() {
                var wrap = sliderEl.parentElement;
                if (!wrap) return 300;
                // getBoundingClientRect gibt zoom-korrekte Werte zurück
                return wrap.getBoundingClientRect().width;
            }

            function getVisibleCount() {
                var w = getWrapWidth();
                if (w >= 860) return 3;
                if (w >= 540) return 2;
                return 1;
            }

            function getCardWidth() {
                var vc = getVisibleCount();
                var w = getWrapWidth();
                if (vc === 1) return w * 0.88; // peek
                return (w - GAP * (vc - 1)) / vc;
            }

            function maxIndex() {
                return Math.max(0, cards.length - getVisibleCount());
            }

            function buildDots() {
                if (!dotsEl) return;
                var vc = getVisibleCount();
                var count = Math.ceil(cards.length / vc);
                dotsEl.innerHTML = '';
                for (var i = 0; i < count; i++) {
                    (function(idx) {
                        var d = document.createElement('span');
                        d.className = 'dot' + (idx === 0 ? ' active' : '');
                        d.addEventListener('click', function() { goTo(idx * vc); });
                        dotsEl.appendChild(d);
                    })(i);
                }
            }

            function updateDots() {
                if (!dotsEl) return;
                var vc = getVisibleCount();
                var dotIdx = Math.round(current / vc);
                dotsEl.querySelectorAll('.dot').forEach(function(d, i) {
                    d.classList.toggle('active', i === dotIdx);
                });
            }

            function applyTransform(tx, animate) {
                sliderEl.style.transition = animate
                    ? 'transform 0.42s cubic-bezier(0.25,0.46,0.45,0.94)'
                    : 'none';
                sliderEl.style.transform = 'translateX(' + tx + 'px)';
            }

            function goTo(idx) {
                current = Math.max(0, Math.min(idx, maxIndex()));
                cardWidth = getCardWidth();
                currentTranslate = -current * (cardWidth + GAP);
                applyTransform(currentTranslate, true);
                updateDots();
            }

            function setup() {
                cardWidth = getCardWidth();
                var wrapW = getWrapWidth();
                cards.forEach(function(c) {
                    c.style.flex = '0 0 ' + cardWidth + 'px';
                    c.style.width = cardWidth + 'px';
                    c.style.maxWidth = cardWidth + 'px';
                    c.style.minWidth = '0';
                    c.style.boxSizing = 'border-box';
                });
                sliderEl.style.gap = GAP + 'px';
                sliderEl.style.position = 'relative';
                current = Math.min(current, maxIndex());
                currentTranslate = -current * (cardWidth + GAP);
                applyTransform(currentTranslate, false);
                buildDots();
                updateDots();
            }

            if (prevBtn) prevBtn.addEventListener('click', function() { goTo(current - getVisibleCount()); });
            if (nextBtn) nextBtn.addEventListener('click', function() { goTo(current + getVisibleCount()); });

            // Mouse drag
            sliderEl.addEventListener('mousedown', function(e) {
                isDragging = true;
                startX = e.clientX;
                startTranslate = currentTranslate;
                sliderEl.style.transition = 'none';
            });
            window.addEventListener('mousemove', function(e) {
                if (!isDragging) return;
                applyTransform(startTranslate + (e.clientX - startX), false);
            });
            window.addEventListener('mouseup', function(e) {
                if (!isDragging) return;
                isDragging = false;
                var diff = e.clientX - startX;
                var thresh = (getCardWidth() + GAP) * 0.22;
                if (diff < -thresh) goTo(current + 1);
                else if (diff > thresh) goTo(current - 1);
                else goTo(current);
            });

            // Touch drag
            sliderEl.addEventListener('touchstart', function(e) {
                startX = e.touches[0].clientX;
                startTranslate = currentTranslate;
                sliderEl.style.transition = 'none';
            }, { passive: true });
            sliderEl.addEventListener('touchmove', function(e) {
                applyTransform(startTranslate + (e.touches[0].clientX - startX), false);
            }, { passive: true });
            sliderEl.addEventListener('touchend', function(e) {
                var diff = e.changedTouches[0].clientX - startX;
                var thresh = (getCardWidth() + GAP) * 0.22;
                if (diff < -thresh) goTo(current + 1);
                else if (diff > thresh) goTo(current - 1);
                else goTo(current);
            });

            sliderEl.addEventListener('click', function(e) {
                if (Math.abs(e.clientX - startX) > 5) e.preventDefault();
            });

            var resizeTimer;
            window.addEventListener('resize', function() {
                clearTimeout(resizeTimer);
                resizeTimer = setTimeout(setup, 80);
            });

            setup();
        }

        // Init both sliders after layout is painted
        setTimeout(function() {
            var sliderL  = document.querySelector('.account-testimonials--left  .testimonials-slider');
            var dotsL    = document.querySelector('.account-testimonials--left  .testimonials-dots');
            var prevL    = document.querySelector('.account-testimonials--left  .trev-prev');
            var nextL    = document.querySelector('.account-testimonials--left  .trev-next');
            initTestimonialSlider(sliderL, dotsL, prevL, nextL);

            var sliderR  = document.querySelector('.account-testimonials--right .testimonials-slider-r');
            var dotsR    = document.querySelector('.account-testimonials--right .testimonials-dots-r');
            var prevR    = document.querySelector('.account-testimonials--right .trev-prev-r');
            var nextR    = document.querySelector('.account-testimonials--right .trev-next-r');
            initTestimonialSlider(sliderR, dotsR, prevR, nextR);
        }, 50);

        if (typeof baguetteBox === 'undefined') {
            $.getScript('https://unpkg.com/baguettebox.js@1.11.1/dist/baguetteBox.min.js')
                .done(function () { baguetteBox.run('.gallery', { animation: 'fade' }); });
        } else {
            baguetteBox.run('.gallery', { animation: 'fade' });
        }
    });
</script>
<?= $this->stop() ?>
