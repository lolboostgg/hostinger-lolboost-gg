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

        global $db;
        if (!empty($db)) {
            try {
                $value = $db->cell(
                    "SELECT total_sales FROM seller_stats WHERE seller_id = ? LIMIT 1",
                    $sellerId
                );
                if ($value !== false && $value !== null) {
                    return $cache[$sellerId] = max(0, (int)$value);
                }
            } catch (Throwable $e) {
            }
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
 include_once __DIR__ . '/../../components/seller/seller-footer.php'; ?>

<?php
if (!function_exists('seller_detail_is_online')) {
    function seller_detail_is_online($seller): bool
    {
        if (empty($seller) || !is_array($seller)) return false;
        if (array_key_exists('is_online', $seller)) return (int)$seller['is_online'] === 1;
        if (array_key_exists('seller_is_online', $seller)) return (int)$seller['seller_is_online'] === 1;
        $sellerId = (int)($seller['id'] ?? 0);
        if ($sellerId <= 0) return false;
        try {
            global $db;
            if (empty($db)) return false;
            $table = $db->cell("SHOW TABLES LIKE 'seller_session_logs'");
            if (empty($table)) return false;
            $row = $db->row(
                "SELECT 1 AS online FROM seller_session_logs sslog WHERE sslog.seller_id = ? AND sslog.created_at >= (NOW() - INTERVAL 5 MINUTE) ORDER BY sslog.id DESC LIMIT 1",
                $sellerId
            );
            return !empty($row);
        } catch (Throwable $e) {
            return false;
        }
    }
}
?>
<?php
// Safe defaults in case route didn't pass these variables

if (!function_exists('lb_seller_profile_slug_from_array')) {
    function lb_seller_profile_slug_from_array(array $seller): string
    {
        $slug = trim((string)($seller['slug'] ?? ''));
        $username = trim((string)($seller['username'] ?? ''));
        $value = $slug !== '' ? $slug : $username;
        $value = html_entity_decode($value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $value = preg_replace('/\s+/', '-', $value);
        $value = preg_replace('/[^\pL\pN_-]+/u', '', (string)$value);
        $value = trim((string)$value, '-');
        return $value !== '' ? $value : (string)($seller['id'] ?? '');
    }
}

if (!isset($seller)) $seller = null;
if (!isset($seller_accounts)) $seller_accounts = [];
?>
<?php
    $champions = array_values(array_filter(array_map('trim', explode('|', (string)($account['champions'] ?? '')))));
    $skins = array_values(array_filter(array_map('trim', explode('|', (string)($account['skins'] ?? '')))));
    $roles = array_values(array_filter(array_map('trim', explode('|', (string)($account['roles'] ?? '')))));

    $valGameData = json_decode((string)($account['game_data'] ?? '{}'), true) ?: [];

    if (!function_exists('view_val_rank_img')) {
        function view_val_rank_img(int $rankId, string $size = 'mini'): string
        {
            return ASSET_URL . "/core/main/img/val/ranks/{$size}/{$rankId}.png";
        }
    }

    if (!function_exists('view_val_data_get')) {
        function view_val_data_get(array $data, array $keys, $default = null)
        {
            foreach ($keys as $key) {
                if (array_key_exists($key, $data) && $data[$key] !== '' && $data[$key] !== null) {
                    return $data[$key];
                }
            }
            return $default;
        }
    }

    $valRank        = (int)($account['rank'] ?? 0);
    $valPeakRank    = (int)view_val_data_get($valGameData, ['val_peak_rank', 'peak_rank'], 0);
    $valAct         = (string)view_val_data_get($valGameData, ['val_act', 'act'], '');
    $valPlatform    = (string)view_val_data_get($valGameData, ['val_platform', 'platform'], '');
    $valPoints      = (int)view_val_data_get($valGameData, ['val_points', 'valorant_points', 'vp'], 0);
    $valRadianite   = (int)view_val_data_get($valGameData, ['val_radianite', 'radianite_points', 'radianite'], 0);
    $valWinrate     = view_val_data_get($valGameData, ['val_winrate', 'winrate_percent', 'winrate'], '');
    $valRankedReady = !empty(view_val_data_get($valGameData, ['val_ranked_ready', 'ranked_ready'], false));
    $valAgents      = view_val_data_get($valGameData, ['agents', 'val_agents'], []);
    if (is_string($valAgents)) {
        $valAgents = preg_split('/[|,]/', $valAgents) ?: [];
    }
    $valAgents = array_values(array_filter(array_map('trim', is_array($valAgents) ? $valAgents : [])));
    $valSkins = (int)view_val_data_get($valGameData, ['val_weapon_skins', 'weapon_skins', 'skins'], 0);
    $valRankLabel = trim((string)($account['rank_label'] ?? '')) ?: util_get_val_rank($valRank);
?>

<?php
    function should_hide_lol_division($rank): bool
    {
        return in_array((int)$rank, [0, 8, 9, 10], true);
    }

    function format_lol_rank_display($rank, $lp, $division): string
    {
        // Coerce to string: rank helpers return null for empty ranks, which would
        // throw a TypeError on this string-typed function and crash the page.
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


if (!function_exists('account_view_seller_total_sales_fixed')) {
    function account_view_seller_total_sales_fixed(array $seller): int
    {
        $sellerId = (int)($seller['id'] ?? $seller['seller_id'] ?? 0);
        $fallback = (int)($seller['seller_total_sales'] ?? $seller['total_sales'] ?? $seller['total_sold'] ?? $seller['seller_sold'] ?? 0);
        return lb_db_seller_total_sales($sellerId, $fallback);
    }
}

// Unified total sales across accounts, items, top-ups and digital goods.
$sellerTotalSoldDisplay = !empty($seller) && is_array($seller)
    ? account_view_seller_total_sales_fixed($seller)
    : 0;
?>

<?= $this->start('styles') ?>
<link rel="stylesheet" type="text/css" href="//cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick.css" />
<link rel="stylesheet" href="https://unpkg.com/baguettebox.js@1.11.1/dist/baguetteBox.min.css" />
<style>
.seller-online-dot {
    width: 8px;
    height: 8px;
    border-radius: 999px;
    display: inline-flex;
    margin-left: 2px;
    background: #22c55e;
    box-shadow: 0 0 0 0 rgba(34,197,94,.7), 0 0 12px rgba(34,197,94,.95);
    animation: sellerOnlinePulse 1.35s ease-in-out infinite;
    flex: 0 0 auto;
}
@keyframes sellerOnlinePulse {
    0% { transform: scale(.9); box-shadow: 0 0 0 0 rgba(34,197,94,.7), 0 0 10px rgba(34,197,94,.75); }
    70% { transform: scale(1.18); box-shadow: 0 0 0 7px rgba(34,197,94,0), 0 0 16px rgba(34,197,94,.95); }
    100% { transform: scale(.9); box-shadow: 0 0 0 0 rgba(34,197,94,0), 0 0 10px rgba(34,197,94,.75); }
}
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

/* Seller chat inline button */
.seller-profile-card__chat {
    display:inline-flex; align-items:center; justify-content:center; gap:9px;
    width:100%; min-height:52px; padding:14px 20px; border-radius:999px;
    background: rgba(109,92,255,.15);
    border:1.5px solid rgba(109,92,255,.5); color:#fff;
    font-weight:800; font-size:16px; line-height:1; white-space:nowrap;
    cursor:pointer; transition: background .2s, border-color .2s, box-shadow .2s;
    margin-top:10px;
}
.seller-profile-card__chat:hover {
    background: rgba(109,92,255,.28);
    border-color: rgba(109,92,255,.8);
    box-shadow: 0 0 20px rgba(109,92,255,.25);
    color:#fff;
}
.seller-profile-card__chat:disabled { opacity:.45; cursor:not-allowed; }
@media (max-width:767px) {
    .seller-profile-card__chat { font-size:14px; }
}

.sticky-button {
    display: none !important;
}
.sticky-button .sticky-chat-btn {
    background: rgba(99,102,241,.16);
    border: 1px solid rgba(99,102,241,.45);
    color: #fff;
    white-space: nowrap;
}
.sticky-button .sticky-chat-btn:hover {
    background: rgba(99,102,241,.28);
}
.sticky-button .sticky-chat-btn:disabled {
    opacity: .45;
    cursor: not-allowed;
}


/* Mobile sticky buy bar redesign + hide bottom navigation while visible */
@media (max-width: 767px) {
    .view-account-page .sticky-button {
        position: fixed !important;
        left: 0 !important;
        right: 0 !important;
        bottom: 0 !important;
        z-index: 999990 !important;
        display: flex !important;
        align-items: center !important;
        justify-content: space-between !important;
        gap: 9px !important;
        padding: 9px 10px calc(9px + env(safe-area-inset-bottom)) !important;
        background: #0e111a !important;
        border-top: 1px solid rgba(116,105,255,.48) !important;
        box-shadow: 0 -10px 28px rgba(0,0,0,.45) !important;
        transform: translateY(110%) !important;
        opacity: 0 !important;
        pointer-events: none !important;
        transition: transform .22s ease, opacity .16s ease !important;
    }

    .view-account-page.view-sticky-buy-visible .sticky-button,
    body.view-sticky-buy-visible .sticky-button {
        transform: translateY(0) !important;
        opacity: 1 !important;
        pointer-events: auto !important;
    }

    .view-account-page .sticky-button form {
        flex: 1 1 auto !important;
        width: auto !important;
        min-width: 0 !important;
        margin: 0 !important;
    }

    .view-account-page .sticky-button .btn {
        width: 100% !important;
        min-height: 48px !important;
        border-radius: 7px !important;
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
        gap: 8px !important;
        padding: 0 12px !important;
        background: #1677ff !important;
        border: 0 !important;
        color: #fff !important;
        font-size: 14px !important;
        font-weight: 800 !important;
        letter-spacing: -.01em !important;
        box-shadow: none !important;
        white-space: nowrap !important;
    }

    .view-account-page .sticky-button .btn i {
        font-size: 15px !important;
        margin-right: 2px !important;
    }

    /* Seller chat button becomes the right button inside the sticky bottom area on mobile. */
    .view-account-page .lbc-floating,
    body.view-account-page .lbc-floating {
        display: none !important;
        position: fixed !important;
        right: 10px !important;
        bottom: calc(9px + env(safe-area-inset-bottom)) !important;
        z-index: 999991 !important;
        width: 48px !important;
        height: 48px !important;
        border-radius: 7px !important;
        background: #242936 !important;
        border: 1px solid rgba(255,255,255,.10) !important;
        color: #d7dbe7 !important;
        box-shadow: none !important;
        font-size: 17px !important;
        transform: none !important;
    }

    .view-account-page.view-sticky-buy-visible .lbc-floating,
    body.view-sticky-buy-visible .lbc-floating {
        display: grid !important;
        place-items: center !important;
    }

    .view-account-page.view-sticky-buy-visible .sticky-button form,
    body.view-sticky-buy-visible .sticky-button form {
        padding-right: 57px !important;
    }

    .view-account-page.view-sticky-buy-visible .lb-mobile-bottomnav,
    .view-account-page.view-sticky-buy-visible .lb-mobile-bottomnav--count-3,
    body.view-sticky-buy-visible .lb-mobile-bottomnav,
    body.view-sticky-buy-visible .lb-mobile-bottomnav--count-3 {
        opacity: 0 !important;
        visibility: hidden !important;
        pointer-events: none !important;
        transform: translateY(120%) !important;
        transition: transform .2s ease, opacity .16s ease, visibility .16s ease !important;
    }

    .view-account-page.view-sticky-buy-visible {
        padding-bottom: calc(74px + env(safe-area-inset-bottom)) !important;
    }
}

/* Seller profile and more-items seller footer compact fix */
.view-account-page .seller-profile-card__name{
    display:inline-flex!important;
    align-items:center!important;
    gap:6px!important;
    flex-wrap:nowrap!important;
    max-width:100%!important;
}
.view-account-page .seller-profile-card__rank-icon{
    margin-left:0!important;
    font-size:15px!important;
    line-height:1!important;
    cursor:help!important;
    filter:drop-shadow(0 0 8px currentColor)!important;
    transform:none!important;
}
.view-account-page .seller-profile-card__name .verified{
    margin-left:0!important;
    cursor:help!important;
}
.view-account-page .seller-profile-card .seller-online-dot{
    margin-left:0!important;
}
.view-account-page .seller-profile-card .seller-stat--green{
    min-width:46px!important;
    padding:8px 12px!important;
}
.view-account-page .seller-profile-card .seller-stat--green .seller-stat__label{
    display:none!important;
}
.view-account-page .seller-profile-card .seller-stat--green .seller-stat__value{
    color:#22c55e!important;
}
.view-account-page .seller-profile-card .seller-stat--green .seller-stat__value i{
    font-size:14px!important;
}

/* More items slider: keep dot and rank badge directly next to the seller name, trusted icon only */
.view-account-page .saf-slide .seller-info{
    min-width:0!important;
    overflow:hidden!important;
    gap:8px!important;
}
.view-account-page .saf-slide .seller-info__left{
    flex:1 1 auto!important;
    min-width:0!important;
    overflow:hidden!important;
    display:flex!important;
    align-items:center!important;
    gap:7px!important;
}
.view-account-page .saf-slide .seller-info__name,
.view-account-page .saf-slide .seller-rank-trigger{
    display:inline-flex!important;
    align-items:center!important;
    gap:5px!important;
    min-width:0!important;
    max-width:100%!important;
    overflow:hidden!important;
}
.view-account-page .saf-slide .seller-info__name-text{
    min-width:0!important;
    overflow:hidden!important;
    text-overflow:ellipsis!important;
    white-space:nowrap!important;
}
.view-account-page .saf-slide .seller-info__verified{
    flex:0 0 auto!important;
    margin-left:0!important;
    font-size:13px!important;
    line-height:1!important;
    transform:none!important;
    cursor:help!important;
    filter:drop-shadow(0 0 8px currentColor)!important;
}
.view-account-page .saf-slide .seller-online-dot{
    flex:0 0 8px!important;
    margin-left:0!important;
}
.view-account-page .saf-slide .seller-info__right{
    flex:0 0 auto!important;
    display:inline-flex!important;
    align-items:center!important;
    gap:6px!important;
}
.view-account-page .saf-slide .seller-info__rating{
    width:30px!important;
    min-width:30px!important;
    height:26px!important;
    padding:0!important;
    justify-content:center!important;
    font-size:0!important;
}
.view-account-page .saf-slide .seller-info__rating i{
    font-size:13px!important;
    margin:0!important;
}


/* Final seller profile polish, shared with account cards in More Items. */
.view-account-page .seller-profile-card__name{
  position:relative!important;
  display:inline-flex!important;
  align-items:center!important;
  gap:6px!important;
  min-width:0!important;
  white-space:nowrap!important;
}
.view-account-page .seller-profile-card__rank-icon{
  margin-left:0!important;
  cursor:help!important;
}
.view-account-page .seller-profile-card__name .seller-rank-tooltip{
  position:absolute;
  left:0;
  bottom:calc(100% + 10px);
  transform:translateY(8px) scale(.96);
  transform-origin:left bottom;
  opacity:0;
  visibility:hidden;
  pointer-events:none;
  display:inline-flex;
  align-items:center;
  gap:8px;
  white-space:nowrap;
  padding:8px 12px;
  border-radius:12px;
  background:linear-gradient(180deg, rgba(20,24,48,.98), rgba(10,13,30,.98));
  border:1px solid rgba(255,255,255,.14);
  box-shadow:0 14px 30px rgba(0,0,0,.32);
  color:#fff;
  font-size:12px;
  font-weight:800;
  line-height:1;
  z-index:9999;
  transition:opacity .18s ease, transform .18s ease, visibility .18s ease;
}
.view-account-page .seller-profile-card__name:hover .seller-rank-tooltip{
  opacity:1;
  visibility:visible;
  transform:translateY(0) scale(1);
}
.view-account-page .seller-stat--green{
  min-width:42px!important;
  padding:8px 12px!important;
}
.view-account-page .seller-stat--green .seller-stat__label{
  display:none!important;
}
.view-account-page .seller-stat--green .seller-stat__value{
  font-size:0!important;
}
.view-account-page .seller-stat--green .seller-stat__value i{
  font-size:13px!important;
}
.view-account-page .saf-slide .seller-rank-trigger,
.view-account-page .saf-slide .seller-info__name{
  display:inline-flex!important;
  align-items:center!important;
  gap:6px!important;
  min-width:0!important;
}
.view-account-page .saf-slide .seller-info__verified{
  font-size:13px!important;
  margin-left:0!important;
  line-height:1!important;
  transform:none!important;
  cursor:help!important;
}
.view-account-page .saf-slide .seller-info__rating{
  width:30px!important;
  min-width:30px!important;
  height:26px!important;
  padding:0!important;
  justify-content:center!important;
  font-size:0!important;
}
.view-account-page .saf-slide .seller-info__rating i{
  font-size:13px!important;
  margin:0!important;
}

</style>






<style id="lbMobileFocusBarsCss">
/* Mobile account view focus mode: top bars are visible only at the very top of the page. */
@media (max-width: 767px), (hover: none) and (pointer: coarse) {
    html.lb-mobile-bars-hidden body.view-account-page .navbar-mobile,
    html.lb-mobile-bars-hidden body.view-account-page .navbar-mobile.scrolled,
    html.lb-mobile-bars-hidden body.view-account-page .lb-mobile-gamebar,
    body.view-account-page.lb-mobile-bars-hidden .navbar-mobile,
    body.view-account-page.lb-mobile-bars-hidden .navbar-mobile.scrolled,
    body.view-account-page.lb-mobile-bars-hidden .lb-mobile-gamebar {
        transform: translate3d(0,-145%,0) !important;
        opacity: 0 !important;
        visibility: hidden !important;
        pointer-events: none !important;
    }

    html.lb-mobile-bars-hidden body.view-account-page .lb-mobile-gamebar,
    body.view-account-page.lb-mobile-bars-hidden .lb-mobile-gamebar {
        transform: translate3d(0,-260%,0) !important;
    }

    body.view-account-page .navbar-mobile,
    body.view-account-page .navbar-mobile.scrolled,
    body.view-account-page .lb-mobile-gamebar {
        transition: transform .22s ease, opacity .16s ease, visibility .16s ease !important;
        will-change: transform, opacity !important;
    }
}
</style>

<script>
(function () {
    'use strict';

    var mq = window.matchMedia('(max-width: 767px), (hover: none) and (pointer: coarse)');
    var ticking = false;
    var topThreshold = 8;

    function getY() {
        return window.scrollY || document.documentElement.scrollTop || document.body.scrollTop || 0;
    }

    function setHidden(hidden) {
        document.documentElement.classList.toggle('lb-mobile-bars-hidden', !!hidden);
        document.body.classList.toggle('lb-mobile-bars-hidden', !!hidden);
    }

    function updateMobileBars() {
        ticking = false;

        if (!mq.matches) {
            setHidden(false);
            return;
        }

        setHidden(getY() > topThreshold);
    }

    function requestUpdate() {
        if (ticking) return;
        ticking = true;
        window.requestAnimationFrame(updateMobileBars);
    }

    window.addEventListener('scroll', requestUpdate, { passive: true });
    window.addEventListener('resize', requestUpdate, { passive: true });
    window.addEventListener('orientationchange', function () {
        setTimeout(requestUpdate, 80);
    }, { passive: true });

    if (mq.addEventListener) mq.addEventListener('change', requestUpdate);
    else mq.addListener(requestUpdate);

    document.addEventListener('DOMContentLoaded', requestUpdate);
    window.addEventListener('load', requestUpdate, { passive: true });
    setInterval(updateMobileBars, 350);

    requestUpdate();
})();
</script>



<style>
/* Mobile polish: seller profile in one row, trust badges in one row, testimonials spacing. */
@media (max-width: 767px) {
    .view-account-page .seller-profile-card--mobile-inject.lb-seller-footer,
    .view-account-page .seller-profile-card--mobile-inject.lb-seller-footer--profile,
    .view-account-page .seller-profile-card--mobile-inject.lb-seller-footer--mobile {
        display: flex !important;
        flex-direction: row !important;
        align-items: center !important;
        justify-content: space-between !important;
        flex-wrap: nowrap !important;
        gap: 10px !important;
        width: 100% !important;
        padding: 14px 16px !important;
        box-sizing: border-box !important;
    }

    .view-account-page .seller-profile-card--mobile-inject .lb-seller-footer__left {
        flex: 1 1 auto !important;
        min-width: 0 !important;
        width: auto !important;
        gap: 10px !important;
        overflow: visible !important;
    }

    .view-account-page .seller-profile-card--mobile-inject .lb-seller-footer__avatar,
    .view-account-page .seller-profile-card--mobile-inject .lb-seller-footer__avatar-placeholder {
        width: 48px !important;
        height: 48px !important;
        min-width: 48px !important;
        min-height: 48px !important;
        flex: 0 0 48px !important;
    }

    .view-account-page .seller-profile-card--mobile-inject .lb-seller-footer__meta {
        min-width: 0 !important;
        overflow: visible !important;
    }

    .view-account-page .seller-profile-card--mobile-inject .lb-seller-footer__name {
        max-width: 100% !important;
        min-width: 0 !important;
    }

    .view-account-page .seller-profile-card--mobile-inject .lb-seller-footer__name-text {
        max-width: 128px !important;
        font-size: 14px !important;
        overflow: hidden !important;
        text-overflow: ellipsis !important;
        white-space: nowrap !important;
    }

    .view-account-page .seller-profile-card--mobile-inject .lb-seller-footer__right {
        flex: 0 0 auto !important;
        width: auto !important;
        justify-content: flex-end !important;
        align-items: center !important;
        gap: 8px !important;
        margin-left: auto !important;
        white-space: nowrap !important;
    }

    .view-account-page .seller-profile-card--mobile-inject .lb-seller-footer__stat {
        height: 38px !important;
        min-height: 38px !important;
        min-width: 52px !important;
        padding: 0 10px !important;
        flex-direction: column !important;
    }

    .view-account-page .seller-profile-card--mobile-inject .lb-seller-footer__trusted {
        width: 46px !important;
        min-width: 46px !important;
        padding: 0 !important;
    }

    .view-account-page .account-testimonials.account-testimonials--right {
        margin-top: 28px !important;
        margin-bottom: 34px !important;
        padding-bottom: 10px !important;
        clear: both !important;
    }

    .view-account-page .trust-badges {
        display: grid !important;
        grid-template-columns: 1fr 1fr !important;
        gap: 8px !important;
        width: 100% !important;
    }

    .view-account-page .trust-badge {
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        flex-direction: row !important;
        flex-wrap: nowrap !important;
        gap: 7px !important;
        white-space: nowrap !important;
        min-width: 0 !important;
        width: 100% !important;
        line-height: 1 !important;
        text-align: center !important;
    }

    .view-account-page .trust-badge i {
        flex: 0 0 auto !important;
        margin: 0 !important;
        line-height: 1 !important;
    }
}

@media (max-width: 380px) {
    .view-account-page .seller-profile-card--mobile-inject .lb-seller-footer__name-text {
        max-width: 102px !important;
    }
    .view-account-page .seller-profile-card--mobile-inject .lb-seller-footer__stat {
        min-width: 48px !important;
        padding: 0 8px !important;
    }
    .view-account-page .seller-profile-card--mobile-inject .lb-seller-footer__trusted {
        width: 42px !important;
        min-width: 42px !important;
    }
    .view-account-page .trust-badge {
        font-size: 11px !important;
        gap: 5px !important;
    }
}
</style>


<style>
/* Mobile seller profile: keep avatar, sold by/name, sold count and trusted icon in one single row. */
@media (max-width: 767px) {
    .view-account-page .seller-profile-card--mobile-inject.lb-seller-footer,
    .view-account-page .seller-profile-card--mobile-inject.lb-seller-footer--profile,
    .view-account-page .seller-profile-card--mobile-inject.lb-seller-footer--mobile {
        flex-direction: row !important;
        align-items: center !important;
        justify-content: space-between !important;
        flex-wrap: nowrap !important;
        gap: 8px !important;
        min-height: 70px !important;
        padding: 12px 14px !important;
        overflow: visible !important;
    }

    .view-account-page .seller-profile-card--mobile-inject .lb-seller-footer__left {
        display: flex !important;
        align-items: center !important;
        flex: 1 1 auto !important;
        min-width: 0 !important;
        gap: 9px !important;
        overflow: visible !important;
    }

    .view-account-page .seller-profile-card--mobile-inject .lb-seller-footer__avatar,
    .view-account-page .seller-profile-card--mobile-inject .lb-seller-footer__avatar-placeholder {
        width: 44px !important;
        height: 44px !important;
        min-width: 44px !important;
        min-height: 44px !important;
        flex: 0 0 44px !important;
    }

    .view-account-page .seller-profile-card--mobile-inject .lb-seller-footer__meta {
        display: flex !important;
        flex-direction: row !important;
        align-items: center !important;
        gap: 5px !important;
        min-width: 0 !important;
        overflow: visible !important;
        white-space: nowrap !important;
    }

    .view-account-page .seller-profile-card--mobile-inject .lb-seller-footer__label {
        display: inline-flex !important;
        align-items: center !important;
        flex: 0 0 auto !important;
        margin: 0 !important;
        font-size: 9px !important;
        line-height: 1 !important;
        letter-spacing: .04em !important;
        white-space: nowrap !important;
    }

    .view-account-page .seller-profile-card--mobile-inject .lb-seller-footer__name {
        display: inline-flex !important;
        align-items: center !important;
        flex: 1 1 auto !important;
        min-width: 0 !important;
        gap: 4px !important;
        overflow: visible !important;
        white-space: nowrap !important;
    }

    .view-account-page .seller-profile-card--mobile-inject .lb-seller-footer__name-text {
        max-width: 76px !important;
        font-size: 14px !important;
        line-height: 1 !important;
        overflow: hidden !important;
        text-overflow: ellipsis !important;
        white-space: nowrap !important;
    }

    .view-account-page .seller-profile-card--mobile-inject .lb-seller-footer__verified {
        font-size: 13px !important;
        flex: 0 0 auto !important;
    }

    .view-account-page .seller-profile-card--mobile-inject .lb-seller-footer__right {
        display: inline-flex !important;
        flex-direction: row !important;
        align-items: center !important;
        justify-content: flex-end !important;
        flex: 0 0 auto !important;
        width: auto !important;
        gap: 6px !important;
        margin-left: 4px !important;
        white-space: nowrap !important;
    }

    .view-account-page .seller-profile-card--mobile-inject .lb-seller-footer__stat {
        display: inline-flex !important;
        flex-direction: row !important;
        align-items: center !important;
        justify-content: center !important;
        height: 34px !important;
        min-height: 34px !important;
        min-width: 58px !important;
        padding: 0 8px !important;
        gap: 4px !important;
        border-radius: 9px !important;
        white-space: nowrap !important;
    }

    .view-account-page .seller-profile-card--mobile-inject .lb-seller-footer__stat strong {
        font-size: 13px !important;
        line-height: 1 !important;
    }

    .view-account-page .seller-profile-card--mobile-inject .lb-seller-footer__stat small {
        font-size: 9px !important;
        line-height: 1 !important;
        letter-spacing: 0 !important;
    }

    .view-account-page .seller-profile-card--mobile-inject .lb-seller-footer__trusted {
        width: 38px !important;
        min-width: 38px !important;
        padding: 0 !important;
    }
}

@media (max-width: 380px) {
    .view-account-page .seller-profile-card--mobile-inject .lb-seller-footer__avatar,
    .view-account-page .seller-profile-card--mobile-inject .lb-seller-footer__avatar-placeholder {
        width: 40px !important;
        height: 40px !important;
        min-width: 40px !important;
        min-height: 40px !important;
        flex-basis: 40px !important;
    }
    .view-account-page .seller-profile-card--mobile-inject .lb-seller-footer__name-text {
        max-width: 58px !important;
        font-size: 13px !important;
    }
    .view-account-page .seller-profile-card--mobile-inject .lb-seller-footer__label {
        font-size: 8px !important;
    }
    .view-account-page .seller-profile-card--mobile-inject .lb-seller-footer__stat {
        min-width: 52px !important;
        padding: 0 6px !important;
    }
    .view-account-page .seller-profile-card--mobile-inject .lb-seller-footer__trusted {
        width: 34px !important;
        min-width: 34px !important;
    }
}
</style>



<style>
/* Mobile detail page polish: highlight pills side by side + cleaner gallery card. */
@media (max-width: 767px) {
    .view-account-page .container > .highlights {
        display: flex !important;
        flex-direction: row !important;
        flex-wrap: nowrap !important;
        align-items: center !important;
        gap: 8px !important;
        width: 100% !important;
        overflow-x: auto !important;
        overflow-y: hidden !important;
        padding-bottom: 2px !important;
        margin: 14px 0 22px !important;
        scrollbar-width: none !important;
        -webkit-overflow-scrolling: touch !important;
    }

    .view-account-page .container > .highlights::-webkit-scrollbar {
        display: none !important;
    }

    .view-account-page .container > .highlights .badge {
        flex: 0 0 auto !important;
        width: auto !important;
        min-width: 0 !important;
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        gap: 7px !important;
        padding: 9px 13px !important;
        border-radius: 999px !important;
        font-size: 12px !important;
        font-weight: 750 !important;
        line-height: 1 !important;
        white-space: nowrap !important;
        color: #fff !important;
        background: linear-gradient(135deg, rgba(99,102,241,.22), rgba(99,102,241,.10)) !important;
        border: 1px solid rgba(111,104,255,.72) !important;
        box-shadow: inset 0 1px 0 rgba(255,255,255,.08), 0 10px 24px rgba(0,0,0,.18) !important;
    }

    .view-account-page .container > .highlights .badge i {
        flex: 0 0 auto !important;
        margin: 0 !important;
        font-size: 12px !important;
        color: #fff !important;
        line-height: 1 !important;
    }

    .view-account-page .gallery-mobile.card {
        margin-top: 8px !important;
        margin-bottom: 22px !important;
        padding: 0 !important;
        overflow: hidden !important;
        border-radius: 18px !important;
        background: linear-gradient(180deg, rgba(255,255,255,.055), rgba(255,255,255,.025)) !important;
        border: 1px solid rgba(116,107,255,.32) !important;
        box-shadow: 0 16px 36px rgba(0,0,0,.22) !important;
    }

    .view-account-page .gallery-mobile .gallery-header,
    .view-account-page .gallery-mobile .card-header {
        display: flex !important;
        align-items: center !important;
        justify-content: space-between !important;
        gap: 12px !important;
        padding: 15px 16px !important;
        min-height: 0 !important;
        border-bottom: 1px solid rgba(255,255,255,.075) !important;
        background: rgba(255,255,255,.025) !important;
    }

    .view-account-page .gallery-mobile .gallery-header h4,
    .view-account-page .gallery-mobile .card-header h4 {
        display: inline-flex !important;
        align-items: center !important;
        gap: 8px !important;
        margin: 0 !important;
        color: #fff !important;
        font-size: 18px !important;
        font-weight: 850 !important;
        line-height: 1 !important;
    }

    .view-account-page .gallery-mobile .gallery-header h4 i,
    .view-account-page .gallery-mobile .card-header h4 i {
        color: #766dff !important;
        font-size: 17px !important;
        margin: 0 !important;
    }

    .view-account-page .gallery-mobile .controls {
        display: inline-flex !important;
        align-items: center !important;
        gap: 7px !important;
        flex: 0 0 auto !important;
    }

    .view-account-page .gallery-mobile .controls .btn {
        width: 42px !important;
        height: 42px !important;
        min-width: 42px !important;
        min-height: 42px !important;
        padding: 0 !important;
        border-radius: 11px !important;
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        background: linear-gradient(135deg, #6c63ff, #5a55e9) !important;
        border: 1px solid rgba(255,255,255,.10) !important;
        box-shadow: 0 10px 22px rgba(99,102,241,.24) !important;
        color: #fff !important;
    }

    .view-account-page .gallery-mobile .controls .btn i {
        font-size: 15px !important;
        line-height: 1 !important;
        margin: 0 !important;
    }

    .view-account-page .gallery-mobile .card-body {
        padding: 14px !important;
        min-height: 0 !important;
        background: transparent !important;
    }

    .view-account-page .gallery-mobile .gallery,
    .view-account-page .gallery-mobile .slick-list,
    .view-account-page .gallery-mobile .slick-track,
    .view-account-page .gallery-mobile .slide {
        min-height: 0 !important;
    }

    .view-account-page .gallery-mobile .slide a {
        display: block !important;
        width: 100% !important;
        overflow: hidden !important;
        border-radius: 12px !important;
        background: rgba(5,7,20,.65) !important;
        border: 1px solid rgba(255,255,255,.06) !important;
    }

    .view-account-page .gallery-mobile .slide img,
    .view-account-page .gallery-mobile .gallery img {
        display: block !important;
        width: 100% !important;
        height: auto !important;
        aspect-ratio: 16 / 9 !important;
        object-fit: contain !important;
        object-position: center !important;
        background: rgba(5,7,20,.72) !important;
    }

    .view-account-page .gallery-mobile .slick-dots {
        margin-top: 10px !important;
    }
}

@media (max-width: 380px) {
    .view-account-page .container > .highlights .badge {
        padding: 8px 11px !important;
        font-size: 11px !important;
        gap: 6px !important;
    }

    .view-account-page .gallery-mobile .controls .btn {
        width: 39px !important;
        height: 39px !important;
        min-width: 39px !important;
        min-height: 39px !important;
    }
}
</style>


<style>
/* Mobile: hide the desktop seller footer profile so only the dedicated mobile seller block is shown. */
@media (max-width: 767px) {
    .view-account-page .right .lb-seller-footer.lb-seller-footer--profile,
    .view-account-page .right .lb-seller-footer--profile {
        display: none !important;
    }
}
</style>

<style id="lb-lol-view-redesign-2026">
/* =========================================================================
   LoL "View Account" page — visual redesign to match the Ranked Shop / lolboost.gg style.
   CSS-only, scoped to body.view-account-page. Cosmetic tokens (bg/border/color/
   radius/shadow) are applied globally; layout/spacing lives in @media(min-width:768px)
   so the existing, hand-tuned mobile layout stays untouched.
   Design tokens shared with shop_lol:
     page bg #070815 · panel #0d1021 / hover #10142a · border rgba(255,255,255,.07)
     text #f7f8ff · muted #8f97b5 · primary #6366f1 / #4f6ef7 · accent #8ea5ff / #92a7ff
   ========================================================================= */

/* ---- Page background --------------------------------------------------- */
body.view-account-page{
  --lbv-panel:#0d1021;
  --lbv-panel-2:#10142a;
  --lbv-border:rgba(255,255,255,.07);
  --lbv-border-strong:rgba(124,146,255,.30);
  --lbv-text:#f7f8ff;
  --lbv-muted:#8f97b5;
  --lbv-primary:#6366f1;
  --lbv-accent:#8ea5ff;
  background:
    radial-gradient(1100px 620px at 18% -6%,rgba(79,110,247,.12),transparent 60%),
    radial-gradient(900px 560px at 88% 2%,rgba(99,102,241,.08),transparent 58%),
    #070815 !important;
  color:var(--lbv-text)!important;
}
body.view-account-page main{overflow:visible!important;}

/* ---- Hero header (band) ------------------------------------------------ */
body.view-account-page > header,
body.view-account-page main > header{
  position:relative!important;
  isolation:isolate!important;
  text-align:left!important;
  background:linear-gradient(180deg,#0b0d1c 0%,#080916 100%)!important;
  border-bottom:1px solid rgba(255,255,255,.06)!important;
  overflow:hidden!important;
  margin:0!important;
}
body.view-account-page > header::before{
  content:"";
  position:absolute; inset:0; z-index:-1; opacity:.16;
  background-image:
    linear-gradient(to right,rgba(255,255,255,.05) 1px,transparent 1px),
    linear-gradient(to bottom,rgba(255,255,255,.05) 1px,transparent 1px);
  background-size:56px 56px;
  -webkit-mask-image:linear-gradient(90deg,#000,transparent 82%);
          mask-image:linear-gradient(90deg,#000,transparent 82%);
}
body.view-account-page > header .content{
  width:min(1320px,calc(100% - 40px))!important;
  max-width:1320px!important;
  margin:0 auto!important;
  padding:42px 0 30px!important;
}
body.view-account-page > header .content::before{
  content:"League of Legends";
  display:block;
  margin:0 0 10px;
  color:var(--lbv-accent);
  font-size:11px; font-weight:900; letter-spacing:.18em; text-transform:uppercase;
}
body.view-account-page > header h1{
  margin:0!important;
  color:#fff!important;
  font-size:clamp(26px,3vw,40px)!important;
  font-weight:950!important;
  line-height:1.08!important;
  letter-spacing:-.03em!important;
}
body.view-account-page > header p{
  margin:12px 0 0!important;
  max-width:660px!important;
  color:var(--lbv-muted)!important;
  font-size:15px!important;
  font-weight:500!important;
  line-height:1.6!important;
}

/* ---- Main container width --------------------------------------------- */
body.view-account-page > .container,
body.view-account-page main > .container{
  width:min(1320px,calc(100% - 40px))!important;
  max-width:1320px!important;
  margin-inline:auto!important;
}

/* ---- Account title + rank icon ---------------------------------------- */
body.view-account-page .container > .title{
  color:#fff!important;
  font-weight:900!important;
  letter-spacing:-.02em!important;
}
body.view-account-page .container > .title .rank-icon{
  display:grid!important; place-items:center!important;
  background:#11152a!important;
  border:1px solid rgba(124,146,255,.20)!important;
  box-shadow:inset 0 1px 0 rgba(255,255,255,.04)!important;
}

/* ---- Highlight badges (under title) ----------------------------------- */
body.view-account-page .container > .highlights .badge{
  background:rgba(79,110,247,.10)!important;
  border:1px solid rgba(124,159,255,.20)!important;
  color:#aebdff!important;
  font-weight:800!important;
}
body.view-account-page .container > .highlights .badge i{color:#8ea5ff!important;}

/* ---- Panels / cards ---------------------------------------------------- */
body.view-account-page .card{
  background:var(--lbv-panel)!important;
  border:1px solid var(--lbv-border)!important;
  box-shadow:0 14px 40px rgba(0,0,0,.24)!important;
  color:var(--lbv-text)!important;
}
body.view-account-page .card .card-header{
  background:transparent!important;
  border-bottom:1px solid rgba(255,255,255,.06)!important;
}
body.view-account-page .card .card-header h4{
  color:#fff!important; font-weight:900!important; letter-spacing:-.01em!important;
}
body.view-account-page .card .card-header h4 i{color:var(--lbv-accent)!important;}
body.view-account-page .card .card-body .description{color:#aab0c9!important;line-height:1.65!important;}

/* ---- Feature grid (Account Details) ----------------------------------- */
body.view-account-page .features .feature{
  background:rgba(255,255,255,.03)!important;
  border:1px solid rgba(255,255,255,.06)!important;
  border-radius:14px!important;
}
body.view-account-page .features .feature h6{
  color:var(--lbv-muted)!important;
  text-transform:uppercase!important; letter-spacing:.05em!important;
  font-weight:800!important;
}
body.view-account-page .features .feature span{color:#eef0ff!important;font-weight:700!important;}
body.view-account-page .features .feature span i.text-primary{color:var(--lbv-accent)!important;}

/* ---- Tabs (Champions / Skins / Roles) --------------------------------- */
body.view-account-page .card .nav-tabs a{
  color:var(--lbv-muted)!important; font-weight:800!important;
  border:1px solid transparent!important; border-radius:11px!important;
}
body.view-account-page .card .nav-tabs a:hover{
  color:#e8ebff!important; background:rgba(255,255,255,.04)!important;
}
body.view-account-page .card .nav-tabs a.active{
  color:#fff!important;
  background:rgba(99,102,241,.14)!important;
  border-color:rgba(124,146,255,.30)!important;
}
body.view-account-page .card .nav-tabs a .count-badge{
  background:rgba(99,102,241,.20)!important;
  color:#c7d2fe!important;
  border:1px solid rgba(124,146,255,.24)!important;
  font-weight:800!important;
}

/* ---- Champion / Skin / Role tiles ------------------------------------- */
body.view-account-page .champs .champ,
body.view-account-page .roles .role{
  background:rgba(255,255,255,.03)!important;
  border:1px solid rgba(255,255,255,.06)!important;
  border-radius:12px!important;
}
body.view-account-page .champs .champ small,
body.view-account-page .roles .role small{color:#c4c9df!important;}
body.view-account-page .skins .skin img{border:1px solid rgba(255,255,255,.08)!important;}
body.view-account-page .empty-hint{color:var(--lbv-muted)!important;}

/* ---- Checkout box ------------------------------------------------------ */
body.view-account-page .card#hide-sticky .tagline{color:#aab0c9!important;}
body.view-account-page .checkout-features li{color:#d6d9ec!important;font-weight:600!important;}
body.view-account-page .checkout-features li i{color:#8ea5ff!important;}
body.view-account-page .totals .price{color:#fff!important;font-weight:950!important;letter-spacing:-.02em!important;}
body.view-account-page .totals .price small{color:var(--lbv-muted)!important;}

/* Primary buy button */
body.view-account-page .totals form > .btn{
  background:linear-gradient(135deg,#6366f1,#4f46e5)!important;
  border:1px solid rgba(124,146,255,.40)!important;
  color:#fff!important;
  font-weight:900!important;
  letter-spacing:.01em!important;
  box-shadow:0 12px 30px rgba(79,70,229,.30)!important;
  transition:transform .15s ease,box-shadow .2s ease,filter .2s ease!important;
}
body.view-account-page .totals form > .btn:hover{
  filter:brightness(1.05)!important;
  transform:translateY(-1px)!important;
  box-shadow:0 16px 38px rgba(79,70,229,.40)!important;
}
/* Chat-with-seller (secondary) */
body.view-account-page .seller-profile-card__chat{
  background:rgba(255,255,255,.045)!important;
  border:1px solid rgba(124,146,255,.22)!important;
  color:#c7d2fe!important; font-weight:800!important;
}
body.view-account-page .seller-profile-card__chat:hover{
  background:rgba(99,102,241,.16)!important;
  border-color:rgba(124,146,255,.40)!important;
  color:#fff!important;
}

/* Trust badges */
body.view-account-page .trust-badge{
  background:rgba(255,255,255,.035)!important;
  border:1px solid rgba(255,255,255,.07)!important;
  color:#c4c9df!important;
}
body.view-account-page .trust-badge i{color:#8ea5ff!important;}

/* ---- Seller profile card (right column) ------------------------------- */
body.view-account-page .right .seller-profile-card{
  background:var(--lbv-panel)!important;
  border:1px solid rgba(124,146,255,.18)!important;
}

/* ---- "More from seller" full-width band ------------------------------- */
body.view-account-page .seller-accounts-fullwidth{
  background:linear-gradient(180deg,#0a0c1a,#080915)!important;
  border-top:1px solid rgba(255,255,255,.06)!important;
  border-bottom:1px solid rgba(255,255,255,.06)!important;
}
body.view-account-page .seller-accounts-fullwidth__title{color:#fff!important;font-weight:900!important;}
body.view-account-page .seller-accounts-fullwidth__title i{color:var(--lbv-accent)!important;}
body.view-account-page .saf-prev,
body.view-account-page .saf-next{
  background:rgba(255,255,255,.05)!important;
  border:1px solid rgba(255,255,255,.10)!important;
  color:#fff!important;
}
body.view-account-page .saf-prev:hover,
body.view-account-page .saf-next:hover{
  background:rgba(99,102,241,.22)!important;
  border-color:rgba(124,146,255,.45)!important;
}

/* ---- Testimonials ------------------------------------------------------ */
body.view-account-page .account-testimonials .section-title{color:#fff!important;font-weight:900!important;}
body.view-account-page .account-testimonials .section-title i{color:#f5a623!important;}
body.view-account-page .testimonial-card{
  background:var(--lbv-panel)!important;
  border:1px solid var(--lbv-border)!important;
}
body.view-account-page .testimonial-card:hover{border-color:rgba(124,146,255,.30)!important;}
body.view-account-page .testimonial-card__text{color:#c4c9df!important;}
body.view-account-page .testimonials-dots .dot.active{background:var(--lbv-primary)!important;}

/* ---- Sticky mobile buy bar -------------------------------------------- */
body.view-account-page .sticky-button .btn{
  background:linear-gradient(135deg,#6366f1,#4f46e5)!important;
  border:1px solid rgba(124,146,255,.40)!important;
  color:#fff!important; font-weight:900!important;
  box-shadow:0 -2px 24px rgba(79,70,229,.28)!important;
}

/* ---- Pagination (if present) ------------------------------------------ */
body.view-account-page .pagination a,
body.view-account-page .pagination span{
  border-color:rgba(255,255,255,.08)!important;
  background:rgba(255,255,255,.04)!important;
  color:rgba(242,244,255,.78)!important;
}
body.view-account-page .pagination .active,
body.view-account-page .pagination a:hover{
  background:linear-gradient(135deg,#4f6ef7,#3b58e8)!important;
  border-color:rgba(124,159,255,.32)!important; color:#fff!important;
}

/* =========================================================================
   Desktop layout / spacing (>=768px). Kept out of the mobile scope so the
   existing responsive rules remain the single source of truth on phones.
   ========================================================================= */

  body.view-account-page .container > .title{
    display:flex!important; align-items:center!important; gap:14px!important;
    margin:6px 0 14px!important;
    padding-top: var(--lb-content-top, 0px) !important;
    font-size:clamp(20px,1.9vw,26px)!important;
  }
  body.view-account-page .container > .title .rank-icon{
    width:52px!important; height:52px!important; min-width:52px!important;
    padding:0!important; border-radius:15px!important;
  }
  body.view-account-page .container > .title .rank-icon img{height:32px!important;width:auto!important;}

  body.view-account-page .container > .highlights .badge{
    padding:7px 12px!important; border-radius:999px!important; font-size:12px!important;
  }

  body.view-account-page .card{border-radius:18px!important;}
  body.view-account-page .features .feature{padding:13px 14px!important;}
  body.view-account-page .card .nav-tabs a{padding:8px 12px!important;}
}
</style>

<style id="lb-lol-view-wider-2026">
/* =========================================================================
   Wider layout + shop-style bottom cards.
   The site renders at ~0.88 zoom, so widths are intentionally larger to fill
   the viewport. All rules are desktop-only (>=768px) so the tuned mobile
   layout stays exactly as-is.
   ========================================================================= */
@media (min-width:768px){

  /* ---- Wider hero band + main content column ---- */
  body.view-account-page > header .content,
  body.view-account-page main > header .content,
  body.view-account-page > .container,
  body.view-account-page main > .container{
    width:min(1560px,calc(100% - 40px))!important;
    max-width:1560px!important;
  }

  /* ---- Give the two columns a touch more room ---- */
  body.view-account-page .layout{gap:28px!important;}
  body.view-account-page .layout .right{
    width:24vw!important;
    min-width:340px!important;
    max-width:420px!important;
  }

  /* ---- Wider "More from seller" band (4 shop-sized cards fit) ---- */
  body.view-account-page .seller-accounts-fullwidth__inner{
    max-width:min(1560px,92%)!important;
  }

  /* =======================================================================
     Bottom slider cards — match the shop_lol card look.
     ======================================================================= */
  body.view-account-page .saf-slide .account-card{
    padding:18px!important;
    border-radius:18px!important;
    background:var(--lbv-panel,#0d1021)!important;
    border:1px solid rgba(255,255,255,.075)!important;
    box-shadow:0 14px 40px rgba(0,0,0,.24)!important;
    transition:transform .2s ease,border-color .2s ease,background .2s ease!important;
  }
  body.view-account-page .saf-slide .account-card:hover{
    transform:translateY(-2px)!important;
    background:var(--lbv-panel-2,#10142a)!important;
    border-color:rgba(124,146,255,.34)!important;
  }

  /* Title + rank icon */
  body.view-account-page .saf-slide .account-card .title{
    font-size:15px!important; font-weight:850!important; gap:9px!important;
    color:#fff!important; margin-bottom:6px!important;
  }
  body.view-account-page .saf-slide .account-card .title .rank-icon img,
  body.view-account-page .saf-slide .account-card .title img{height:26px!important;width:auto!important;}
  body.view-account-page .saf-slide .account-card .excerpt{font-size:12px!important;color:rgba(230,233,255,.55)!important;}

  /* Cover image */
  body.view-account-page .saf-slide .account-card .image-box{margin:14px 0!important;border-radius:14px!important;}
  body.view-account-page .saf-slide .account-card .image-box > img{max-height:200px!important;border-radius:14px!important;}

  /* Spec badges */
  body.view-account-page .saf-slide .account-card .highlights{gap:7px!important;margin-bottom:14px!important;}
  body.view-account-page .saf-slide .account-card .highlights .badge{
    font-size:11px!important; padding:5px 9px!important; border-radius:9px!important;
    background:rgba(99,102,241,.16)!important;
    border:1px solid rgba(124,146,255,.20)!important;
    color:#c7d2fe!important;
  }

  /* Price + buy button */
  body.view-account-page .saf-slide .account-card .totals .price-eur{
    font-size:22px!important; font-weight:900!important; color:#fff!important; letter-spacing:-.02em!important;
  }
  body.view-account-page .saf-slide .account-card .totals .btn{
    padding:9px 16px!important; font-size:13px!important; border-radius:12px!important;
    background:linear-gradient(135deg,#6366f1,#4f46e5)!important;
    border:1px solid rgba(124,146,255,.40)!important;
    color:#fff!important; font-weight:800!important;
    box-shadow:0 8px 20px rgba(79,70,229,.26)!important;
  }
  body.view-account-page .saf-slide .account-card .totals .btn:hover{filter:brightness(1.06)!important;}

  /* Seller footer -> shop-style fuller strip */
  body.view-account-page .saf-slide .seller-info{
    padding:12px 14px!important; margin-top:14px!important; border-radius:12px!important;
    background:linear-gradient(180deg,rgba(255,255,255,.02),rgba(255,255,255,.05))!important;
    border:1px solid rgba(255,255,255,.08)!important;
  }
  body.view-account-page .saf-slide .seller-info__avatar{
    width:34px!important; height:34px!important;
    border:2px solid rgba(150,109,255,.35)!important;
    box-shadow:0 0 0 3px rgba(122,92,255,.08)!important;
  }
  body.view-account-page .saf-slide .seller-info__name{font-size:13px!important;font-weight:800!important;}
  body.view-account-page .saf-slide .seller-info__sold{font-size:11px!important;}

  /* Nav arrows to match panel look */
  body.view-account-page .saf-prev,
  body.view-account-page .saf-next{width:34px!important;height:34px!important;font-size:12px!important;}
}

/* Very wide screens: cap card scale so 4 stay shop-proportioned */
@media (min-width:1500px){
  body.view-account-page .saf-slide .account-card .image-box > img{max-height:210px!important;}
}
</style>

<style id="lb-lol-view-trust-2026">
/* =========================================================================
   1) Bottom slider cards -> exact shop_lol card anatomy
      (flush seller footer, lowered 3-column highlight chips)
   2) Trust bar under the account title
   3) Full-width "Buyer Protection" section
   Desktop rules (>=768px) for the slider so mobile stays as-is.
   ========================================================================= */

/* ---- 1) Slider cards match shop_lol -------------------------------------- */
@media (min-width:768px){
  /* Card shell: no inner padding, clip so the footer sits flush at the edge */
  body.view-account-page .saf-slide .account-card{
    padding:0!important;
    overflow:hidden!important;
    border-radius:18px!important;
    background:#0d1021!important;
    border:1px solid rgba(255,255,255,.075)!important;
    box-shadow:0 14px 40px rgba(0,0,0,.24)!important;
  }
  body.view-account-page .saf-slide .account-card > .cover-link{
    display:flex!important; flex-direction:column!important; flex:1 1 auto!important;
    padding:16px 16px 0!important;
  }

  /* Title / excerpt */
  body.view-account-page .saf-slide .account-card .title{
    font-size:15px!important; font-weight:850!important; gap:10px!important;
    color:#fff!important; margin-bottom:6px!important; padding-right:44px!important;
  }
  body.view-account-page .saf-slide .account-card .excerpt{
    font-size:11.5px!important; color:#8f97b5!important; margin:0 0 12px!important;
  }

  /* Cover image */
  body.view-account-page .saf-slide .account-card .image-box{
    height:180px!important; min-height:180px!important; max-height:180px!important;
    margin:0!important; border-radius:14px!important; background:#070912!important;
    border:1px solid rgba(255,255,255,.07)!important;
  }
  body.view-account-page .saf-slide .account-card .image-box > img{
    height:100%!important; max-height:180px!important; width:100%!important;
    object-fit:cover!important; border-radius:14px!important;
  }

  /* Highlights: sit LOWER (12px gap from the image) as a tidy 3-col chip grid */
  body.view-account-page .saf-slide .account-card .highlights{
    display:grid!important; grid-template-columns:repeat(3,minmax(0,1fr))!important;
    gap:6px!important; margin:12px 0 0!important; padding:0!important;
    min-height:64px!important; align-content:flex-start!important;
  }
  body.view-account-page .saf-slide .account-card .highlights .badge{
    min-width:0!important; min-height:30px!important; padding:6px 8px!important;
    border-radius:9px!important; justify-content:flex-start!important;
    background:#151827!important; border:1px solid rgba(255,255,255,.07)!important;
    color:#b8bfd8!important; font-size:10.5px!important; font-weight:700!important;
    overflow:hidden!important; text-overflow:ellipsis!important; white-space:nowrap!important;
  }
  body.view-account-page .saf-slide .account-card .highlights .badge i{color:#8ea5ff!important;}

  /* Totals row separated by a hairline like the shop */
  body.view-account-page .saf-slide .account-card .totals{
    margin-top:auto!important; padding:14px 0 16px!important;
    border-top:1px solid rgba(255,255,255,.065)!important;
  }
  body.view-account-page .saf-slide .account-card .totals .price-eur{font-size:20px!important;}

  /* Seller footer: flush full-width bar with rounded bottom corners (shop look) */
  body.view-account-page .saf-slide .account-card > .seller-info{
    margin:0!important; min-height:54px!important; padding:10px 16px!important;
    border:0!important; border-top:1px solid rgba(255,255,255,.065)!important;
    border-radius:0 0 18px 18px!important;
    background:#101322!important;
  }
  body.view-account-page .saf-slide .seller-info__avatar{width:34px!important;height:34px!important;}
  body.view-account-page .saf-slide .seller-info__name{font-size:13px!important;}
  body.view-account-page .saf-slide .seller-info__sold{font-size:11px!important;}

  /* Delivery badge chip */
  body.view-account-page .saf-slide .account-card .delivery-type{
    top:16px!important; right:16px!important;
    width:32px!important; height:32px!important; min-width:32px!important; min-height:32px!important;
    display:grid!important; place-items:center!important;
    border-radius:10px!important; background:#090b17!important;
    border:1px solid rgba(255,255,255,.10)!important;
  }
}

/* ---- 2) Trust bar under the account title -------------------------------- */
.lbv-trustbar{
  display:grid; grid-template-columns:repeat(4,1fr); gap:12px; margin:18px 0 4px;
}
.lbv-trustbar__item{
  display:flex; align-items:center; gap:12px;
  padding:13px 15px; border-radius:14px;
  background:#0d1021; border:1px solid rgba(255,255,255,.07);
}
.lbv-trustbar__icon{
  width:40px; height:40px; min-width:40px; border-radius:11px;
  display:grid; place-items:center; font-size:16px; color:#8ea5ff;
  background:rgba(99,102,241,.12); border:1px solid rgba(124,146,255,.22);
}
.lbv-trustbar__title{font-size:13px; font-weight:800; color:#fff; line-height:1.15;}
.lbv-trustbar__sub{font-size:11px; color:#8f97b5; margin-top:2px; line-height:1.2;}
@media (max-width:767px){
  .lbv-trustbar{grid-template-columns:1fr 1fr; gap:8px; margin:14px 0 2px;}
  .lbv-trustbar__item{padding:11px 12px; gap:10px; border-radius:12px;}
  .lbv-trustbar__icon{width:34px; height:34px; min-width:34px; font-size:14px; border-radius:10px;}
  .lbv-trustbar__title{font-size:12px;}
  .lbv-trustbar__sub{display:none;}
}

/* ---- 3) Buyer Protection section ----------------------------------------- */
.lbv-trust-section{
  border-top:1px solid rgba(255,255,255,.06);
  border-bottom:1px solid rgba(255,255,255,.06);
  background:linear-gradient(180deg,#0a0c1a,#080915);
  padding:46px 0;
}
.lbv-trust-section__inner{width:min(1560px,92%); max-width:1560px; margin:0 auto;}
.lbv-trust-section__head{text-align:center; margin-bottom:26px;}
.lbv-trust-section__kicker{color:#8ea5ff; font-size:11px; font-weight:900; letter-spacing:.18em; text-transform:uppercase;}
.lbv-trust-section__title{color:#fff; font-size:clamp(22px,2.4vw,30px); font-weight:950; letter-spacing:-.02em; margin:8px 0 0;}
.lbv-trust-section__desc{color:#8f97b5; font-size:14px; line-height:1.6; margin:9px auto 0; max-width:580px;}

.lbv-stats{display:grid; grid-template-columns:repeat(4,1fr); gap:12px; margin-bottom:22px;}
.lbv-stat{text-align:center; padding:18px 12px; border-radius:16px; background:#0d1021; border:1px solid rgba(255,255,255,.07);}
.lbv-stat__value{font-size:26px; font-weight:950; color:#fff; letter-spacing:-.02em; line-height:1;}
.lbv-stat__value i{color:#4ade80; font-size:20px;}
.lbv-stat__label{font-size:11px; color:#8f97b5; font-weight:700; text-transform:uppercase; letter-spacing:.05em; margin-top:7px;}

.lbv-trust-grid{display:grid; grid-template-columns:repeat(3,1fr); gap:14px;}
.lbv-trust-card{
  padding:22px; border-radius:18px; background:#0d1021; border:1px solid rgba(255,255,255,.07);
  transition:transform .2s ease, border-color .2s ease;
}
.lbv-trust-card:hover{transform:translateY(-2px); border-color:rgba(124,146,255,.28);}
.lbv-trust-card__icon{
  width:48px; height:48px; border-radius:14px; display:grid; place-items:center;
  font-size:20px; color:#8ea5ff; margin-bottom:14px;
  background:rgba(99,102,241,.12); border:1px solid rgba(124,146,255,.22);
}
.lbv-trust-card__title{font-size:16px; font-weight:900; color:#fff; margin-bottom:6px;}
.lbv-trust-card__text{font-size:13px; color:#9299b5; line-height:1.6;}

.lbv-pay{
  display:flex; align-items:center; justify-content:center; gap:14px; flex-wrap:wrap;
  margin-top:26px; padding-top:22px; border-top:1px solid rgba(255,255,255,.06);
}
.lbv-pay__label{font-size:12px; color:#8f97b5; font-weight:700;}
.lbv-pay__methods{display:flex; align-items:center; gap:10px; flex-wrap:wrap;}
.lbv-pay__chip{
  display:inline-flex; align-items:center; gap:7px;
  padding:7px 12px; border-radius:10px; font-size:12px; font-weight:700; color:#c4c9df;
  background:rgba(255,255,255,.035); border:1px solid rgba(255,255,255,.08);
}
.lbv-pay__chip i{color:#8ea5ff;}
@media (max-width:767px){
  .lbv-trust-section{padding:32px 0;}
  .lbv-stats{grid-template-columns:1fr 1fr;}
  .lbv-stat__value{font-size:22px;}
  .lbv-trust-grid{grid-template-columns:1fr;}
  .lbv-trust-card{padding:18px;}
}
</style>

<style id="lb-lol-view-refine-2026">
/* =========================================================================
   Senior design pass — reduce "box soup", add rhythm, dynamic accents and
   an unmistakable checkout focus. Lighter, connected surfaces instead of a
   stack of identical dark rectangles.
   ========================================================================= */

/* Hide the leftover boxy trust styles from the previous iteration */
body.view-account-page .lbv-trustbar{display:none!important;}

/* ---- Slim guarantee ribbon (replaces the 4-box trust bar) --------------- */
.lbv-ribbon{
  display:flex; flex-wrap:wrap; align-items:center; justify-content:center;
  gap:6px 4px; margin:16px 0 22px; padding:13px 16px;
  border-top:1px solid rgba(255,255,255,.07);
  border-bottom:1px solid rgba(255,255,255,.07);
}
.lbv-ribbon__item{
  display:inline-flex; align-items:center; gap:8px;
  font-size:13px; font-weight:750; color:#d4d8ea; white-space:nowrap;
}
.lbv-ribbon__item i{color:#8ea5ff; font-size:14px;}
.lbv-ribbon__dot{width:4px; height:4px; border-radius:50%; background:rgba(255,255,255,.18); margin:0 14px;}
@media (max-width:767px){
  body.view-account-page .lbv-ribbon{
    display:grid;
    grid-template-columns:repeat(2,minmax(0,1fr));
    gap:8px;
    margin:14px 0 20px;
    padding:12px 0;
    border-top:1px solid rgba(255,255,255,.07);
    border-bottom:1px solid rgba(255,255,255,.07);
  }
  body.view-account-page .lbv-ribbon__item{
    min-width:0;
    min-height:42px;
    padding:9px 10px;
    gap:8px;
    border:1px solid rgba(126,145,255,.14);
    border-radius:11px;
    background:linear-gradient(135deg,rgba(116,105,255,.10),rgba(255,255,255,.025));
    color:#e4e7f4;
    font-size:11px!important;
    font-weight:800;
    line-height:1.25;
    white-space:normal;
  }
  body.view-account-page .lbv-ribbon__item i{
    width:25px;
    height:25px;
    flex:0 0 25px;
    display:grid;
    place-items:center;
    border-radius:8px;
    background:rgba(111,126,255,.14);
    color:#9cadff;
    font-size:12px!important;
  }
  body.view-account-page .lbv-ribbon__item:last-of-type{
    grid-column:1 / -1;
    justify-content:center;
  }
  body.view-account-page .lbv-ribbon__dot{display:none;}
}

/* ---- Softer, connected content panels (less "box") --------------------- */
body.view-account-page .left .card,
body.view-account-page .right .card{
  background:linear-gradient(180deg,#0e1122,#0b0e1b)!important;
  border:1px solid rgba(255,255,255,.06)!important;
  box-shadow:none!important;
  border-radius:20px!important;
}
body.view-account-page .left .card:hover,
body.view-account-page .right .card:not(#hide-sticky):hover{
  border-color:rgba(124,146,255,.16)!important;
}
body.view-account-page .card .card-header{
  border-bottom:1px solid rgba(255,255,255,.055)!important;
  padding-bottom:14px!important;
}
body.view-account-page .card .card-header h4{
  font-size:15px!important; letter-spacing:-.01em!important;
  text-transform:none!important;
}
body.view-account-page .card .card-header h4 i{
  color:#8ea5ff!important;
  background:rgba(99,102,241,.12);
  border:1px solid rgba(124,146,255,.18);
  width:30px; height:30px; border-radius:9px;
  display:inline-grid; place-items:center; margin-right:10px!important; font-size:13px!important;
}

/* Account description reads like editorial copy, not a data dump */
body.view-account-page .card .card-body .description{
  color:#b3b8cf!important; font-size:13.5px!important; line-height:1.7!important;
}

/* ---- Overview stat grid: clean, scannable, low-box ---------------------- */
@media (min-width:768px){
  body.view-account-page .features{
    display:grid!important; grid-template-columns:repeat(3,minmax(0,1fr))!important; gap:10px!important;
  }
  body.view-account-page .features .feature{
    background:rgba(255,255,255,.02)!important;
    border:1px solid rgba(255,255,255,.05)!important;
    border-radius:13px!important; padding:13px 15px!important;
    transition:border-color .18s ease, background .18s ease;
  }
  body.view-account-page .features .feature:hover{
    border-color:rgba(124,146,255,.24)!important;
    background:rgba(99,102,241,.05)!important;
  }
}
body.view-account-page .features .feature h6{
  color:#8b93b0!important; font-size:10px!important; letter-spacing:.07em!important;
}
body.view-account-page .features .feature span{font-size:14px!important;}

/* ---- Tabs: pill-style, dynamic ----------------------------------------- */
body.view-account-page .card .nav-tabs{gap:8px!important;}
body.view-account-page .card .nav-tabs a{
  border-radius:999px!important; padding:8px 14px!important;
  background:rgba(255,255,255,.03)!important; border:1px solid rgba(255,255,255,.06)!important;
}
body.view-account-page .card .nav-tabs a.active{
  background:linear-gradient(135deg,rgba(99,102,241,.9),rgba(79,70,229,.9))!important;
  border-color:transparent!important; color:#fff!important;
  box-shadow:0 8px 20px rgba(79,70,229,.30)!important;
}
body.view-account-page .card .nav-tabs a.active .count-badge{
  background:rgba(255,255,255,.22)!important; color:#fff!important; border-color:transparent!important;
}

/* ---- Checkout = the hero element: elevated + accent + dynamic ---------- */
body.view-account-page .right .card#hide-sticky{
  position:relative!important; overflow:hidden!important;
  background:linear-gradient(180deg,rgba(26,30,60,.55),rgba(11,14,30,.82))!important;
  border:1px solid rgba(124,146,255,.24)!important;
  box-shadow:0 24px 60px rgba(5,6,20,.55)!important;
  border-radius:22px!important;
}
body.view-account-page .right .card#hide-sticky::before{
  content:""; position:absolute; top:0; left:0; right:0; height:3px;
  background:linear-gradient(90deg,#6366f1,#8b5cf6,#6366f1);
  background-size:200% 100%; animation:lbvAccentSlide 6s linear infinite;
}
@keyframes lbvAccentSlide{0%{background-position:0 0}100%{background-position:200% 0}}
body.view-account-page .right .card#hide-sticky .totals .price{
  font-size:34px!important; line-height:1!important;
}
body.view-account-page .right .card#hide-sticky .checkout-features li{font-size:13px!important;}

/* Buy button: bigger, glowing, alive on hover */
body.view-account-page .totals form > .btn{
  min-height:54px!important; font-size:15px!important; border-radius:14px!important;
  background:linear-gradient(135deg,#7c83ff,#5b57ff 55%,#4f46e5)!important;
  box-shadow:0 14px 34px rgba(91,87,255,.38)!important;
}

/* ---- Section spacing rhythm -------------------------------------------- */
@media (min-width:768px){
  body.view-account-page .left > .card{margin-bottom:18px!important;}
}

/* ---- Trust band (replaces the boxy buyer-protection section) ----------- */
.lbv-trustband{
  background:
    radial-gradient(700px 200px at 15% 0%,rgba(99,102,241,.10),transparent 65%),
    linear-gradient(180deg,#0a0c1a,#080915);
  border-top:1px solid rgba(255,255,255,.06);
  border-bottom:1px solid rgba(255,255,255,.06);
  padding:26px 0;
}
.lbv-trustband__inner{
  width:min(1560px,92%); max-width:1560px; margin:0 auto;
  display:flex; align-items:center; justify-content:space-between; gap:24px 40px; flex-wrap:wrap;
}
.lbv-trustband__lead{display:flex; align-items:center; gap:16px; min-width:0;}
.lbv-trustband__rating{
  display:flex; flex-direction:column; gap:3px; padding-right:18px;
  border-right:1px solid rgba(255,255,255,.09);
}
.lbv-trustband__stars{color:#fbbf24; font-size:12px; letter-spacing:2px;}
.lbv-trustband__score{font-size:28px; font-weight:950; color:#fff; line-height:1; letter-spacing:-.02em;}
.lbv-trustband__score small{font-size:13px; color:#8f97b5; font-weight:700;}
.lbv-trustband__leadtext{display:flex; flex-direction:column; gap:2px;}
.lbv-trustband__leadtext strong{color:#fff; font-size:15px; font-weight:850;}
.lbv-trustband__leadtext span{color:#8f97b5; font-size:12.5px;}
.lbv-trustband__points{display:flex; align-items:center; gap:14px 28px; flex-wrap:wrap;}
.lbv-trustband__point{display:flex; align-items:center; gap:10px; color:#c9cee4; font-size:13.5px; font-weight:750;}
.lbv-trustband__point i{color:#8ea5ff; font-size:17px; width:20px; text-align:center;}
@media (max-width:960px){
  .lbv-trustband__inner{flex-direction:column; align-items:flex-start;}
}
@media (max-width:767px){
  .lbv-trustband{padding:20px 0;}
  .lbv-trustband__points{gap:12px 20px;}
  .lbv-trustband__point{font-size:12.5px;}
  .lbv-trustband__score{font-size:24px;}
}

/* ---- Testimonials header a touch more editorial ------------------------ */
body.view-account-page .account-testimonials{border-top:1px solid rgba(255,255,255,.05)!important;}
</style>

<style id="lb-lol-view-fix-2026">
/* =========================================================================
   Fix pass:
   1) seller name no longer underlined
   2) slider card border no longer clipped top/bottom
   3) right column sticky while scrolling (until the more-from-seller band)
   4) larger, more readable typography (site runs at ~0.88 zoom)
   ========================================================================= */

/* 1) Kill the underline on seller name links (slider + footers) */
body.view-account-page .seller-info__name,
body.view-account-page .seller-info__name *,
body.view-account-page .seller-rank-trigger,
body.view-account-page .seller-info__name-text,
body.view-account-page .lb-seller-footer__name,
body.view-account-page .lb-seller-footer__name *{
  text-decoration:none!important;
}

/* 2) Stop the slick slider from clipping the card border top/bottom */
body.view-account-page .saf-slider .slick-list{
  padding-top:10px!important; padding-bottom:10px!important;
  margin-top:-10px!important; margin-bottom:-10px!important;
}
body.view-account-page .saf-slider,
body.view-account-page .seller-accounts-fullwidth__inner{overflow:visible!important;}

/* 3) Sticky right column (seller card + checkout + gallery move with scroll) */
@media (min-width:992px){
  body.view-account-page .container,
  body.view-account-page .layout{overflow:visible!important;}
  body.view-account-page .layout{align-items:flex-start!important;}
  body.view-account-page .layout .right{
    position:sticky!important;
    top:96px!important;
    align-self:flex-start!important;
  }
}

/* 4) Bigger, clearer typography (compensating the 0.88 site zoom) --------- */
@media (min-width:768px){
  /* Account headline */
  body.view-account-page .container > .title{font-size:clamp(24px,2.1vw,32px)!important;}
  body.view-account-page .container > .title .rank-icon{width:56px!important;height:56px!important;min-width:56px!important;}
  body.view-account-page .container > .title .rank-icon img{height:34px!important;}

  /* Guarantee ribbon */
  body.view-account-page .lbv-ribbon__item{font-size:15px!important;}
  body.view-account-page .lbv-ribbon__item i{font-size:15px!important;}

  /* Panel headers */
  body.view-account-page .card .card-header h4{font-size:17px!important;}
  body.view-account-page .card .card-header h4 i{width:34px;height:34px;font-size:14px!important;}

  /* Description copy */
  body.view-account-page .card .card-body .description{font-size:15px!important;line-height:1.8!important;}

  /* Overview stat grid */
  body.view-account-page .features .feature h6{font-size:11.5px!important;}
  body.view-account-page .features .feature span{font-size:16px!important;}
  body.view-account-page .features .feature span img{width:28px!important;}

  /* Tabs */
  body.view-account-page .card .nav-tabs a{font-size:14.5px!important;}
  body.view-account-page .card .nav-tabs a .count-badge{font-size:12px!important;}
  body.view-account-page .empty-hint{font-size:14px!important;}

  /* Checkout */
  body.view-account-page .card#hide-sticky .tagline{font-size:15px!important;line-height:1.6!important;}
  body.view-account-page .checkout-features li{font-size:15px!important;}
  body.view-account-page .totals .price{font-size:38px!important;}
  body.view-account-page .totals .price small{font-size:15px!important;}
  body.view-account-page .totals form > .btn{font-size:16px!important;}
  body.view-account-page .seller-profile-card__chat{font-size:15px!important;}
  body.view-account-page .trust-badge{font-size:13px!important;}

  /* Seller profile card */
  body.view-account-page .lb-seller-footer--profile .lb-seller-footer__name-text{font-size:17px!important;}
  body.view-account-page .lb-seller-footer--profile .lb-seller-footer__label{font-size:12px!important;}
  body.view-account-page .lb-seller-footer--profile .lb-seller-footer__stat strong{font-size:17px!important;}
  body.view-account-page .lb-seller-footer--profile .lb-seller-footer__stat small{font-size:10px!important;}

  /* Testimonials */
  body.view-account-page .testimonial-card__text{font-size:14.5px!important;line-height:1.7!important;}
  body.view-account-page .testimonial-card__author-name{font-size:13px!important;}
  body.view-account-page .testimonial-card__author-rank{font-size:11.5px!important;}
  body.view-account-page .account-testimonials .section-title{font-size:19px!important;}

  /* "More from seller" slider cards */
  body.view-account-page .saf-slide .account-card .title{font-size:16px!important;}
  body.view-account-page .saf-slide .account-card .title .rank-icon img,
  body.view-account-page .saf-slide .account-card .title img{height:28px!important;}
  body.view-account-page .saf-slide .account-card .excerpt{font-size:12.5px!important;}
  body.view-account-page .saf-slide .account-card .highlights .badge{font-size:12px!important;}
  body.view-account-page .saf-slide .account-card .totals .price-eur{font-size:23px!important;}
  body.view-account-page .saf-slide .account-card .totals .btn{font-size:14px!important;}
  body.view-account-page .saf-slide .seller-info__name,
  body.view-account-page .saf-slide .seller-info__name-text{font-size:14px!important;}
  body.view-account-page .saf-slide .seller-info__sold{font-size:12.5px!important;}
  body.view-account-page .seller-accounts-fullwidth__title{font-size:19px!important;}
}
</style>

<style id="lb-lol-view-final-spacing-fixes-2026">
/* Keep the page header in normal document flow so it always starts below the
   navbar, regardless of whether the sale banner is visible. */
body.view-account-page > header,
body.view-account-page main > header {
    position: relative !important;
    inset: auto !important;
    top: auto !important;
    margin-top: 0 !important;
    transform: none !important;
}

@media (min-width: 768px) {
    /* Preserve a clearer gap between checkout controls and the seller footer. */
    body.view-account-page .saf-slide .account-card .totals {
        padding-bottom: 22px !important;
    }

    /* The source seller avatar is small, avoid enlarging it enough to look soft. */
    body.view-account-page .saf-slide .seller-info__avatar {
        width: 30px !important;
        height: 30px !important;
        min-width: 30px !important;
        min-height: 30px !important;
        object-fit: cover !important;
        filter: none !important;
        transform: none !important;
        image-rendering: auto !important;
    }
}
</style>

<style id="lb-account-initial-scroll-fix">
/* Prevent late gallery and slider layout changes from anchoring the viewport
   to the testimonials section during the initial page render. */
body.view-account-page .gallery,
body.view-account-page .gallery-mobile,
body.view-account-page .account-testimonials,
body.view-account-page .seller-accounts-fullwidth {
    overflow-anchor: none;
}

body.view-account-page #account-page-container {
    overflow-anchor: auto;
    scroll-margin-top: 0;
}
</style>



<style id="lb-mobile-tabs-account-cards-fix-2026">
@media (max-width: 767px) {
  /* Champions, skins and roles: replace oversized circles with a clean segmented bar */
  body.view-account-page .card .card-header:has(.nav-tabs) {
    padding: 10px !important;
  }

  body.view-account-page .card .nav-tabs {
    width: 100% !important;
    display: grid !important;
    grid-template-columns: repeat(3, minmax(0, 1fr)) !important;
    gap: 6px !important;
    padding: 4px !important;
    margin: 0 !important;
    border-radius: 13px !important;
    background: rgba(255,255,255,.025) !important;
    border: 1px solid rgba(255,255,255,.06) !important;
  }

  body.view-account-page .card .nav-tabs a {
    min-width: 0 !important;
    width: 100% !important;
    min-height: 58px !important;
    padding: 8px 5px !important;
    margin: 0 !important;
    border-radius: 10px !important;
    display: grid !important;
    grid-template-columns: auto auto !important;
    grid-template-rows: auto auto !important;
    align-items: center !important;
    justify-content: center !important;
    align-content: center !important;
    column-gap: 5px !important;
    row-gap: 4px !important;
    font-size: 12px !important;
    line-height: 1.05 !important;
    white-space: nowrap !important;
    background: transparent !important;
    border: 1px solid transparent !important;
    box-shadow: none !important;
    transform: none !important;
  }

  body.view-account-page .card .nav-tabs a > i {
    grid-column: 1 !important;
    grid-row: 1 !important;
    margin: 0 !important;
    font-size: 14px !important;
    color: #8f96b3 !important;
  }

  body.view-account-page .card .nav-tabs a .count-badge {
    grid-column: 1 / -1 !important;
    grid-row: 2 !important;
    justify-self: center !important;
    min-width: 27px !important;
    height: 22px !important;
    padding: 0 7px !important;
    display: inline-flex !important;
    align-items: center !important;
    justify-content: center !important;
    border-radius: 7px !important;
    font-size: 11px !important;
    line-height: 1 !important;
    margin: 0 !important;
  }

  body.view-account-page .card .nav-tabs a.active {
    background: linear-gradient(135deg, rgba(99,102,241,.34), rgba(79,70,229,.23)) !important;
    border-color: rgba(124,146,255,.34) !important;
    box-shadow: 0 8px 18px rgba(79,70,229,.16) !important;
  }

  body.view-account-page .card .nav-tabs a.active > i {
    color: #fff !important;
  }

  body.view-account-page .card .tab-content {
    padding-top: 0 !important;
  }

  body.view-account-page .card .empty-hint {
    min-height: 72px !important;
    padding: 20px 16px !important;
    display: flex !important;
    align-items: center !important;
    justify-content: center !important;
    text-align: center !important;
    font-size: 13px !important;
    line-height: 1.45 !important;
  }

  /* More seller accounts: compact mobile shop card */
  body.view-account-page .seller-accounts-fullwidth {
    padding: 22px 0 26px !important;
  }

  body.view-account-page .seller-accounts-fullwidth__inner {
    max-width: calc(100% - 28px) !important;
  }

  body.view-account-page .saf-slider .slick-slide {
    padding: 0 4px !important;
  }

  body.view-account-page .saf-slide .account-card {
    padding: 0 !important;
    border-radius: 16px !important;
    overflow: hidden !important;
    background: #0d1021 !important;
    border: 1px solid rgba(255,255,255,.08) !important;
    box-shadow: 0 14px 32px rgba(0,0,0,.28) !important;
  }

  body.view-account-page .saf-slide .account-card > .cover-link {
    padding: 14px 14px 0 !important;
    display: flex !important;
    flex-direction: column !important;
    min-width: 0 !important;
  }

  body.view-account-page .saf-slide .account-card .title {
    min-height: 30px !important;
    padding: 0 70px 0 0 !important;
    margin: 0 0 6px !important;
    gap: 8px !important;
    font-size: 14px !important;
    line-height: 1.25 !important;
    font-weight: 850 !important;
    white-space: normal !important;
  }

  body.view-account-page .saf-slide .account-card .title > img,
  body.view-account-page .saf-slide .account-card .title .rank-icon,
  body.view-account-page .saf-slide .account-card .title .rank-icon img {
    width: 26px !important;
    height: 26px !important;
    min-width: 26px !important;
    object-fit: contain !important;
    padding: 0 !important;
    background: transparent !important;
  }

  body.view-account-page .saf-slide .account-card .excerpt {
    display: -webkit-box !important;
    -webkit-box-orient: vertical !important;
    -webkit-line-clamp: 2 !important;
    overflow: hidden !important;
    margin: 0 0 11px !important;
    font-size: 11px !important;
    line-height: 1.45 !important;
    color: #8f97b5 !important;
  }

  body.view-account-page .saf-slide .account-card .image-box {
    width: 100% !important;
    height: 178px !important;
    min-height: 178px !important;
    max-height: 178px !important;
    margin: 0 !important;
    border-radius: 12px !important;
    overflow: hidden !important;
    background: #070912 !important;
    border: 1px solid rgba(255,255,255,.07) !important;
  }

  body.view-account-page .saf-slide .account-card .image-box > img {
    width: 100% !important;
    height: 100% !important;
    max-height: none !important;
    object-fit: cover !important;
    border-radius: 0 !important;
  }

  body.view-account-page .saf-slide .account-card .image-box > .badge {
    right: 8px !important;
    bottom: 8px !important;
    padding: 5px 8px !important;
    border-radius: 8px !important;
    font-size: 10px !important;
    background: rgba(7,9,18,.88) !important;
    border: 1px solid rgba(255,255,255,.10) !important;
  }

  body.view-account-page .saf-slide .account-card .highlights {
    display: grid !important;
    grid-template-columns: repeat(2, minmax(0, 1fr)) !important;
    gap: 6px !important;
    margin: 11px 0 0 !important;
    padding: 0 !important;
  }

  body.view-account-page .saf-slide .account-card .highlights .badge {
    min-width: 0 !important;
    min-height: 30px !important;
    padding: 6px 8px !important;
    border-radius: 8px !important;
    display: flex !important;
    align-items: center !important;
    justify-content: flex-start !important;
    gap: 6px !important;
    overflow: hidden !important;
    text-overflow: ellipsis !important;
    white-space: nowrap !important;
    font-size: 10px !important;
    line-height: 1 !important;
    color: #bec4da !important;
    background: #151827 !important;
    border: 1px solid rgba(255,255,255,.065) !important;
  }

  body.view-account-page .saf-slide .account-card .highlights .badge:last-child:nth-child(odd) {
    grid-column: 1 / -1 !important;
  }

  body.view-account-page .saf-slide .account-card .highlights .badge i {
    flex: 0 0 auto !important;
    color: #8ea5ff !important;
  }

  body.view-account-page .saf-slide .account-card .totals {
    width: 100% !important;
    margin: 12px 0 0 !important;
    padding: 12px 0 14px !important;
    border-top: 1px solid rgba(255,255,255,.065) !important;
    gap: 10px !important;
  }

  body.view-account-page .saf-slide .account-card .totals .price-eur {
    display: inline-flex !important;
    align-items: baseline !important;
    gap: 4px !important;
    font-size: 20px !important;
    line-height: 1 !important;
    font-weight: 900 !important;
    letter-spacing: -.02em !important;
  }

  body.view-account-page .saf-slide .account-card .totals .btn {
    min-height: 38px !important;
    padding: 0 13px !important;
    border-radius: 10px !important;
    display: inline-flex !important;
    align-items: center !important;
    justify-content: center !important;
    gap: 6px !important;
    font-size: 12px !important;
    font-weight: 850 !important;
    background: linear-gradient(135deg,#6366f1,#4f46e5) !important;
    border: 1px solid rgba(124,146,255,.35) !important;
    box-shadow: 0 8px 18px rgba(79,70,229,.24) !important;
  }

  body.view-account-page .saf-slide .account-card > .seller-info {
    width: 100% !important;
    min-height: 54px !important;
    margin: 0 !important;
    padding: 9px 14px !important;
    border: 0 !important;
    border-top: 1px solid rgba(255,255,255,.065) !important;
    border-radius: 0 !important;
    background: #101322 !important;
    overflow: visible !important;
  }

  body.view-account-page .saf-slide .seller-info__left {
    min-width: 0 !important;
    gap: 8px !important;
  }

  body.view-account-page .saf-slide .seller-info__avatar {
    width: 31px !important;
    height: 31px !important;
    min-width: 31px !important;
  }

  body.view-account-page .saf-slide .seller-info__name {
    min-width: 0 !important;
    font-size: 12px !important;
  }

  body.view-account-page .saf-slide .seller-info__name-text {
    max-width: 115px !important;
  }

  body.view-account-page .saf-slide .seller-info__sold {
    height: 26px !important;
    padding: 0 8px !important;
    display: inline-flex !important;
    align-items: center !important;
    border-radius: 8px !important;
    font-size: 10px !important;
    white-space: nowrap !important;
  }

  body.view-account-page .saf-slide .account-card > .delivery-type {
    top: 13px !important;
    right: 13px !important;
    width: 30px !important;
    height: 30px !important;
    min-width: 30px !important;
    min-height: 30px !important;
    display: grid !important;
    place-items: center !important;
    border-radius: 9px !important;
    background: #090b17 !important;
    border: 1px solid rgba(255,255,255,.10) !important;
    font-size: 12px !important;
  }

  body.view-account-page .saf-slide .account-card > .account-card__recommended-icon {
    right: 49px !important;
    color: #f5c451 !important;
  }
}
</style>

<style id="lb-mobile-account-title-header-offset-2026">
/* Content always starts right after the fixed navbar / lb-game-subnav (or
   mobile-gamebar), on both mobile and desktop. --lb-content-top is measured
   live via JS and already accounts for the sale banner and whichever bars
   are actually visible, so no hardcoded breakpoint values are needed here. */
body.view-account-page #account-page-title {
  margin-top: 0 !important;
}
</style>
<?= $this->stop() ?>

<div class="container" id="account-page-container">
    <?= $this->insert('website/components/marketplace-breadcrumbs', [
        'type' => 'accounts',
        'gameSlug' => 'valorant',
        'gameName' => 'Valorant',
        'currentTitle' => (string)($account['title'] ?? ''),
    ]) ?>
    <h3 class="title" id="account-page-title">
        <div class="rank-icon">
            <img src="<?= htmlspecialchars(view_val_rank_img($valRank)) ?>" alt="<?= htmlspecialchars($valRankLabel) ?>">
        </div>
        <?= $account['title'] ?>
    </h3>

    <div class="lbv-ribbon">
        <span class="lbv-ribbon__item"><i class="fas fa-bolt"></i><?= t('Instant Delivery') ?></span>
        <span class="lbv-ribbon__dot"></span>
        <span class="lbv-ribbon__item"><i class="fas fa-shield-halved"></i><?= t('14-Day Warranty') ?></span>
        <span class="lbv-ribbon__dot"></span>
        <span class="lbv-ribbon__item"><i class="fas fa-envelope-circle-check"></i><?= t('Full Email Access') ?></span>
        <span class="lbv-ribbon__dot"></span>
        <span class="lbv-ribbon__item"><i class="fas fa-lock"></i><?= t('Secure Checkout') ?></span>
        <span class="lbv-ribbon__dot"></span>
        <span class="lbv-ribbon__item"><i class="fas fa-headset"></i><?= t('24/7 Support') ?></span>
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
            <?php
                echo lb_render_seller_footer(array_merge((array)$seller, [
                    'total_sold' => $sellerTotalSoldDisplay,
                    'seller_total_sales' => $sellerTotalSoldDisplay,
                    'is_online' => seller_detail_is_online((array)$seller) ? 1 : 0,
                ]), ['variant' => 'mobile-profile', 'class' => 'seller-profile-card--mobile-inject']);
            ?>
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
                            <h6><?= t('Rank') ?></h6>
                            <span>
                                <img src="<?= htmlspecialchars(view_val_rank_img($valRank)) ?>" class="img-fluid" style="width:25px;height:auto;" alt="<?= htmlspecialchars(util_get_val_rank($valRank)) ?>">
                                <?= htmlspecialchars($valRankLabel) ?>
                            </span>
                        </div>
                        <div class="feature">
                            <h6><?= t('Peak Rank') ?></h6>
                            <span>
                                <img src="<?= htmlspecialchars(view_val_rank_img($valPeakRank)) ?>" class="img-fluid" style="width:25px;height:auto;" alt="<?= htmlspecialchars(util_get_val_rank($valPeakRank)) ?>">
                                <?= htmlspecialchars(util_get_val_rank($valPeakRank)) ?>
                            </span>
                        </div>
                        <?php if ($valPlatform): ?>
                        <div class="feature">
                            <h6><?= t('Platform') ?></h6>
                            <span>
                                <i class="text-primary me-2 fs-5 fas fa-desktop"></i>
                                <?= htmlspecialchars($valPlatform) ?>
                            </span>
                        </div>
                        <?php endif ?>
                        <div class="feature">
                            <h6><?= t('Server') ?></h6>
                            <span>
                                <i class="text-primary me-2 fs-5 fas fa-server"></i>
                                <?= strtoupper($account['server'] ?? '') ?>
                            </span>
                        </div>
                        <div class="feature">
                            <h6><?= t('Level') ?></h6>
                            <span>
                                <i class="text-primary me-2 fs-5 fas fa-arrow-turn-up"></i>
                                <?= (int)($account['level'] ?? 0) ?>
                            </span>
                        </div>
                        <?php if ($valPoints > 0): ?>
                        <div class="feature">
                            <h6><?= t('Valorant Points') ?></h6>
                            <span>
                                <i class="text-primary me-2 fs-5 fas fa-coins"></i>
                                <?= number_format($valPoints) ?>
                            </span>
                        </div>
                        <?php endif ?>
                        <?php if ($valRadianite > 0): ?>
                        <div class="feature">
                            <h6><?= t('Radianite Points') ?></h6>
                            <span>
                                <i class="text-primary me-2 fs-5 fas fa-gem"></i>
                                <?= number_format($valRadianite) ?>
                            </span>
                        </div>
                        <?php endif ?>
                        <?php if ($valWinrate !== ''): ?>
                        <div class="feature">
                            <h6><?= t('Winrate') ?></h6>
                            <span>
                                <i class="text-primary me-2 fs-5 fas fa-percentage"></i>
                                <?= htmlspecialchars($valWinrate) ?>%
                            </span>
                        </div>
                        <?php endif ?>
                        <div class="feature">
                            <h6><?= t('Ranked Ready') ?></h6>
                            <span>
                                <i class="text-primary me-2 fs-5 fas fa-<?= $valRankedReady ? 'check-circle' : 'times-circle' ?>"></i>
                                <?= $valRankedReady ? t('Yes') : t('No') ?>
                            </span>
                        </div>
                        <?php if (!empty($valAgents)): ?>
                        <div class="feature">
                            <h6><?= t('Agents') ?></h6>
                            <span>
                                <i class="text-primary me-2 fs-5 fas fa-crosshairs"></i>
                                <?= count($valAgents) ?>
                            </span>
                        </div>
                        <?php endif ?>
                        <?php if ($valSkins > 0): ?>
                        <div class="feature">
                            <h6><?= t('Weapon Skins') ?></h6>
                            <span>
                                <i class="text-primary me-2 fs-5 fas fa-gun"></i>
                                <?= number_format($valSkins) ?>
                            </span>
                        </div>
                        <?php endif ?>
                    </div>
                </div>
            </div>

            <?php if (!empty($valAgents)): ?>
            <div class="card">
                <div class="card-header">
                    <div class="nav-tabs">
                        <a href="#agents-list" class="active">
                            <i class="fas fa-crosshairs me-1"></i><?= t('Agents') ?><span class="count-badge"><?= count($valAgents) ?></span>
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="tab-content">
                        <div id="agents-list" class="tab-pane active">
                            <div class="champs">
                                <?php
                                $agentsJson = file_exists(SYS_PATH . '/public/uploads/lists/val-agents.json')
                                    ? json_decode(file_get_contents(SYS_PATH . '/public/uploads/lists/val-agents.json'), true)
                                    : [];
                                foreach ($valAgents as $agentKey):
                                    $agentKey = trim($agentKey);
                                    $agentName = $agentsJson[$agentKey]['name'] ?? ucfirst($agentKey);
                                    $agentIcon = $agentsJson[$agentKey]['icon'] ?? '';
                                ?>
                                    <div class="champ">
                                        <?php if ($agentIcon): ?>
                                            <img src="<?= htmlspecialchars($agentIcon) ?>" class="rounded-circle border" style="height:40px;width:40px;object-fit:cover;" alt="<?= htmlspecialchars($agentName) ?>">
                                        <?php endif ?>
                                        <small><?= htmlspecialchars($agentName) ?></small>
                                    </div>
                                <?php endforeach ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif ?>



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
                        <a href="/val/accounts" class="trev-viewall"><?= t('View all') ?> <i class="fas fa-arrow-right"></i></a>
                    </div>
                </div>
                <div class="testimonials-slider-wrap">
                    <div class="testimonials-slider">
                        <div class="testimonial-card">
                            <div class="testimonial-card__stars">
                                <i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i>
                            </div>
                            <div class="testimonial-card__text"><?= t('Rank boost was fast and smooth. Great communication, secure handoff, and the account stayed exactly as promised.') ?></div>
                            <div class="testimonial-card__author">
                                <img src="<?= ICON_URL ?>/6b008b42-9969-4cae-a0b0-0e859abefaf3.png" alt="" class="testimonial-card__author-avatar">
                                <div class="testimonial-card__author-info">
                                    <div class="testimonial-card__author-name">J****</div>
                                    <div class="testimonial-card__author-rank">Ascendant Account · EU</div>
                                </div>
                            </div>
                        </div>
                        <div class="testimonial-card">
                            <div class="testimonial-card__stars">
                                <i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i>
                            </div>
                            <div class="testimonial-card__text"><?= t('Bought a verified ranked Valorant account. Delivery was instant and support helped me with email access and security setup right away.') ?></div>
                            <div class="testimonial-card__author">
                                <img src="<?= ICON_URL ?>/790af80a-47ab-4450-95a6-7953d67939c6.png" alt="" class="testimonial-card__author-avatar">
                                <div class="testimonial-card__author-info">
                                    <div class="testimonial-card__author-name">S*****</div>
                                    <div class="testimonial-card__author-rank">Ranked Valorant Account · NA</div>
                                </div>
                            </div>
                        </div>
                        <div class="testimonial-card">
                            <div class="testimonial-card__stars">
                                <i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i>
                            </div>
                            <div class="testimonial-card__text"><?= t('Needed a clean smurf for duo queue. Fast delivery, smooth login, and perfect for fresh placements.') ?></div>
                            <div class="testimonial-card__author">
                                <img src="<?= ICON_URL ?>/7d0ab91d-d9fb-4da6-9a9a-c6f39b9327d5.jpeg" alt="" class="testimonial-card__author-avatar">
                                <div class="testimonial-card__author-info">
                                    <div class="testimonial-card__author-name">T*****</div>
                                    <div class="testimonial-card__author-rank">Hand-Leveled Smurf · EU</div>
                                </div>
                            </div>
                        </div>
                        <div class="testimonial-card">
                            <div class="testimonial-card__stars">
                                <i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i>
                            </div>
                            <div class="testimonial-card__text"><?= t('Super smooth transaction. Got my ranked account within minutes of payment, and the seller helped me through every verification step.') ?></div>
                            <div class="testimonial-card__author">
                                <img src="<?= ICON_URL ?>/6b008b42-9969-4cae-a0b0-0e859abefaf3.png" alt="" class="testimonial-card__author-avatar">
                                <div class="testimonial-card__author-info">
                                    <div class="testimonial-card__author-name">M*****</div>
                                    <div class="testimonial-card__author-rank">Platinum Account · NA</div>
                                </div>
                            </div>
                        </div>
                        <div class="testimonial-card">
                            <div class="testimonial-card__stars">
                                <i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i>
                            </div>
                            <div class="testimonial-card__text"><?= t('Second time buying here. Fast delivery, clean account details, and support solved a small issue the same day.') ?></div>
                            <div class="testimonial-card__author">
                                <img src="<?= ICON_URL ?>/790af80a-47ab-4450-95a6-7953d67939c6.png" alt="" class="testimonial-card__author-avatar">
                                <div class="testimonial-card__author-info">
                                    <div class="testimonial-card__author-name">R*****</div>
                                    <div class="testimonial-card__author-rank">Diamond Account · EU</div>
                                </div>
                            </div>
                        </div>
                        <div class="testimonial-card">
                            <div class="testimonial-card__stars">
                                <i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i>
                            </div>
                            <div class="testimonial-card__text"><?= t('Account came exactly as described with the listed level and content. Delivery was quick and the dashboard was easy to use.') ?></div>
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
                            <div class="testimonial-card__text"><?= t('Legit site. Account came with the listed skins and agents, no issues at all. Played several matches already and everything works perfectly.') ?></div>
                            <div class="testimonial-card__author">
                                <img src="<?= ICON_URL ?>/6b008b42-9969-4cae-a0b0-0e859abefaf3.png" alt="" class="testimonial-card__author-avatar">
                                <div class="testimonial-card__author-info">
                                    <div class="testimonial-card__author-name">F*****</div>
                                    <div class="testimonial-card__author-rank">Gold Account · NA</div>
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
            <?php
                echo lb_render_seller_footer(array_merge((array)$seller, [
                    'total_sold' => $sellerTotalSoldDisplay,
                    'seller_total_sales' => $sellerTotalSoldDisplay,
                    'is_online' => seller_detail_is_online((array)$seller) ? 1 : 0,
                ]), ['variant' => 'profile']);
            ?>
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

                    <?php
                        $sellerChatAllowedInline = !empty($seller) && (
                            !array_key_exists('allow_chat_requests', (array)$seller)
                            || (int)($seller['allow_chat_requests'] ?? 1) === 1
                        );
                    ?>
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
                            <?php if (!empty($seller) && ($sellerChatAllowedInline ?? true)): ?>
                            <button type="button"
                                    class="seller-profile-card__chat"
                                    style="width:100%;margin-top:10px;"
                                    title="<?= t('Message Seller') ?>"
                                    onclick="return window.openSellerChatModal ? window.openSellerChatModal(event) : true;"
                                    data-bs-toggle="modal"
                                    data-bs-target="#sellerChatModal"
                                    data-toggle="modal"
                                    data-target="#sellerChatModal">
                                <i class="fa-solid fa-comment-dots"></i>
                                <span><?= t('Chat with seller') ?></span>
                            </button>
                            <?php elseif (!empty($seller)): ?>
                            <button type="button"
                                    class="seller-profile-card__chat"
                                    style="width:100%;margin-top:10px;opacity:.45;cursor:not-allowed;"
                                    title="<?= t('Message Seller') ?>"
                                    disabled>
                                <i class="fa-solid fa-comment-dots"></i>
                                <span><?= t('Chat with seller') ?></span>
                            </button>
                            <?php endif; ?>
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
                        <a href="/val/accounts" class="trev-viewall"><?= t('View all') ?> <i class="fas fa-arrow-right"></i></a>
                    </div>
                </div>
                <div class="testimonials-slider-wrap">
                    <div class="testimonials-slider testimonials-slider-r">
                        <div class="testimonial-card">
                            <div class="testimonial-card__stars"><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i></div>
                            <div class="testimonial-card__text"><?= t('Rank boost was fast and smooth. Great communication, secure handoff, and the account stayed exactly as promised.') ?></div>
                            <div class="testimonial-card__author">
                                <img src="<?= ICON_URL ?>/6b008b42-9969-4cae-a0b0-0e859abefaf3.png" alt="" class="testimonial-card__author-avatar">
                                <div class="testimonial-card__author-info">
                                    <div class="testimonial-card__author-name">J****</div>
                                    <div class="testimonial-card__author-rank">Ascendant Account · EU</div>
                                </div>
                            </div>
                        </div>
                        <div class="testimonial-card">
                            <div class="testimonial-card__stars"><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i></div>
                            <div class="testimonial-card__text"><?= t('Bought a verified ranked Valorant account. Delivery was instant and support helped me with email access and security setup right away.') ?></div>
                            <div class="testimonial-card__author">
                                <img src="<?= ICON_URL ?>/790af80a-47ab-4450-95a6-7953d67939c6.png" alt="" class="testimonial-card__author-avatar">
                                <div class="testimonial-card__author-info">
                                    <div class="testimonial-card__author-name">S*****</div>
                                    <div class="testimonial-card__author-rank">Ranked Valorant Account · NA</div>
                                </div>
                            </div>
                        </div>
                        <div class="testimonial-card">
                            <div class="testimonial-card__stars"><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i></div>
                            <div class="testimonial-card__text"><?= t('Needed a clean smurf for duo queue. Fast delivery, smooth login, and perfect for fresh placements.') ?></div>
                            <div class="testimonial-card__author">
                                <img src="<?= ICON_URL ?>/7d0ab91d-d9fb-4da6-9a9a-c6f39b9327d5.jpeg" alt="" class="testimonial-card__author-avatar">
                                <div class="testimonial-card__author-info">
                                    <div class="testimonial-card__author-name">T*****</div>
                                    <div class="testimonial-card__author-rank">Hand-Leveled Smurf · EU</div>
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
                                    <div class="testimonial-card__author-rank">Platinum Account · NA</div>
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
                                    <div class="testimonial-card__author-rank">Diamond Account · EU</div>
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

<?php
// More items: show only active, listed and unsold accounts.
$seller_accounts = array_values(array_filter((array)$seller_accounts, static function ($sa) {
    if (!is_array($sa)) return false;

    $active = array_key_exists('active', $sa) ? (int)$sa['active'] : 1;
    $sold   = array_key_exists('sold', $sa) ? (int)$sa['sold'] : 0;

    return $active === 1 && $sold === 0;
}));
?>
<?php if (!empty($seller_accounts)): ?>
<?php $sellerAccountsCount = count($seller_accounts); ?>
<div class="seller-accounts-fullwidth <?= $sellerAccountsCount === 1 ? 'seller-accounts-fullwidth--single' : '' ?>">
    <div class="seller-accounts-fullwidth__inner">
        <div class="seller-accounts-fullwidth__head">
            <div class="seller-accounts-fullwidth__title">
                <i class="fas fa-layer-group"></i>
                <?= t('More from') ?> <a href="/sellers/<?= htmlspecialchars(lb_seller_profile_slug_from_array($seller ?? []), ENT_QUOTES) ?>" style="color:#6366f1;text-decoration:none;"><?= htmlspecialchars($seller['username'] ?? '') ?></a>
            </div>
            <?php if ($sellerAccountsCount > 1): ?>
            <div class="seller-accounts-fullwidth__controls">
                <button type="button" class="saf-prev"><i class="fas fa-chevron-left"></i></button>
                <button type="button" class="saf-next"><i class="fas fa-chevron-right"></i></button>
                <a href="/val/accounts" class="saf-viewall"><?= t('View all') ?> <i class="fas fa-arrow-right"></i></a>
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



<?php
// Seller direct chat modal variables
$seller_id        = (int)($seller['id'] ?? 0);
$seller_name_raw  = (string)($seller['username'] ?? 'Seller');
$seller_name      = htmlspecialchars($seller_name_raw, ENT_QUOTES, 'UTF-8');
$seller_icon_raw  = (string)($seller['icon'] ?? '');
$seller_icon      = htmlspecialchars($seller_icon_raw, ENT_QUOTES, 'UTF-8');
$ref_type         = 'account';
$ref_id           = (int)($account['id'] ?? 0);
$chat_allowed     = (bool)(!empty($seller['allow_chat_requests']) || !array_key_exists('allow_chat_requests', (array)$seller));
$seller_initials  = strtoupper(substr(trim($seller_name_raw) ?: 'S', 0, 2));
$ajax_url_inline  = defined('AJAX_URL') ? AJAX_URL : (defined('BASE_URL') ? rtrim(BASE_URL, '/') . '/ajax' : '/ajax');
$client_logged_in_inline = (defined('CLIENT_ID') && (int)CLIENT_ID > 0);
$base_url_inline  = defined('BASE_URL') ? rtrim(BASE_URL, '/') : '';

// Account info for the modal header
$item_title  = htmlspecialchars($account['title'] ?? '', ENT_QUOTES, 'UTF-8');
$val_rank    = function_exists('util_get_val_rank') ? util_get_val_rank((int)($account['rank'] ?? 0)) : '';
$item_type   = htmlspecialchars($val_rank ?: 'Val Account', ENT_QUOTES, 'UTF-8');
$item_server = htmlspecialchars(strtoupper((string)($account['server'] ?? '')), ENT_QUOTES, 'UTF-8');
$item_price  = $accountPriceDisplay['with_symbol'] ?? '';
$item_img    = '';
$item_fa     = 'fa-solid fa-crosshairs';
?>
<?php if ($seller_id): ?>
<style>
/* ════════════════════════════════════
   SELLER CHAT MODAL – komplett neu
   ════════════════════════════════════ */

/* Overlay */
.lbc-overlay {
    position: fixed; inset: 0; z-index: 999999;
    display: none; align-items: center; justify-content: center;
    padding: 16px;
    background: rgba(4, 6, 18, 0.82);
    backdrop-filter: blur(16px);
    -webkit-backdrop-filter: blur(16px);
}
.lbc-overlay.is-open { display: flex; }

/* Panel */
.lbc-panel {
    width: min(620px, 100%);
    max-height: calc(100vh - 32px);
    display: flex; flex-direction: column;
    border-radius: 20px;
    background: #0d1226;
    border: 1px solid rgba(109, 92, 255, 0.3);
    box-shadow:
        0 0 0 1px rgba(109, 92, 255, 0.12),
        0 32px 80px rgba(0, 0, 0, 0.7),
        0 8px 24px rgba(109, 92, 255, 0.12);
    overflow: hidden;
    color: #fff;
}

/* ── Header ── */
.lbc-head {
    display: flex; align-items: center; justify-content: space-between; gap: 12px;
    padding: 18px 20px;
    background: linear-gradient(135deg, rgba(109,92,255,.4) 0%, rgba(99,60,220,.25) 100%);
    border-bottom: 1px solid rgba(109, 92, 255, 0.25);
    flex-shrink: 0;
}
.lbc-head-left { display: flex; align-items: center; gap: 12px; min-width: 0; }
.lbc-avatar {
    width: 44px; height: 44px; border-radius: 12px; flex-shrink: 0;
    object-fit: cover; display: grid; place-items: center;
    background: linear-gradient(135deg, #6d5cff, #7c3aed);
    font-weight: 900; font-size: 15px; color: #fff;
    border: 1.5px solid rgba(255,255,255,.18);
    box-shadow: 0 4px 14px rgba(109,92,255,.45);
}
.lbc-head-info { min-width: 0; }
.lbc-head-name {
    font-size: 15px; font-weight: 800; color: #fff;
    white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
    display: block;
}
.lbc-head-sub {
    font-size: 11px; color: rgba(255,255,255,.55); margin-top: 2px;
    display: flex; align-items: center; gap: 5px;
}
.lbc-head-sub i { font-size: 10px; color: #6d5cff; }
.lbc-close {
    width: 34px; height: 34px; border-radius: 10px; flex-shrink: 0;
    border: 0; background: rgba(255,255,255,.1); color: #fff;
    font-size: 18px; font-weight: 700; cursor: pointer;
    display: grid; place-items: center;
    transition: background .18s;
}
.lbc-close:hover { background: rgba(255,255,255,.2); }

/* ── Item preview strip ── */
.lbc-item-strip {
    display: flex; align-items: center; gap: 12px;
    padding: 12px 20px;
    background: rgba(109, 92, 255, 0.07);
    border-bottom: 1px solid rgba(109, 92, 255, 0.15);
    flex-shrink: 0;
}
.lbc-item-icon {
    width: 36px; height: 36px; border-radius: 10px; flex-shrink: 0;
    background: rgba(109,92,255,.15);
    border: 1px solid rgba(109,92,255,.25);
    display: grid; place-items: center;
    font-size: 15px; color: #a5b4fc;
}
.lbc-item-icon img { width: 20px; height: 20px; object-fit: contain; }
.lbc-item-meta { min-width: 0; flex: 1; }
.lbc-item-title {
    font-size: 13px; font-weight: 700; color: #fff;
    white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
    display: block;
}
.lbc-item-tags { display: flex; gap: 6px; margin-top: 3px; flex-wrap: wrap; }
.lbc-item-tag {
    font-size: 10px; font-weight: 700; padding: 2px 8px; border-radius: 999px;
    background: rgba(109,92,255,.18); border: 1px solid rgba(109,92,255,.3);
    color: #c4b5fd;
}
.lbc-item-price {
    font-size: 15px; font-weight: 900; color: #fff;
    white-space: nowrap; flex-shrink: 0;
}

/* ── Guidelines ── */
/* ── Guidelines – always visible, no toggle ── */
.lbc-guidelines {
    padding: 14px 20px 16px;
    border-bottom: 1px solid rgba(109, 92, 255, 0.18);
    background: rgba(109, 92, 255, 0.06);
    flex-shrink: 0;
}
.lbc-guide-header {
    display: flex; align-items: center; gap: 8px;
    font-size: 11px; font-weight: 800; letter-spacing: .06em;
    text-transform: uppercase; color: rgba(255,255,255,.55);
    margin-bottom: 10px;
}
.lbc-guide-header i { color: #6d5cff; font-size: 13px; }

.lbc-guide-text {
    font-size: 12.5px; color: rgba(255,255,255,.6); line-height: 1.6;
    margin-bottom: 12px;
}

/* Good / Avoid columns */
.lbc-guide-cols {
    display: grid; grid-template-columns: 1fr 1fr; gap: 10px;
}
.lbc-guide-col {
    border-radius: 10px; padding: 11px 13px;
}
.lbc-guide-col--good {
    background: rgba(34,197,94,.09);
    border: 1px solid rgba(34,197,94,.22);
}
.lbc-guide-col--bad {
    background: rgba(239,68,68,.08);
    border: 1px solid rgba(239,68,68,.2);
}
.lbc-guide-col-head {
    display: flex; align-items: center; gap: 6px;
    font-size: 11px; font-weight: 800; margin-bottom: 9px;
    text-transform: uppercase; letter-spacing: .05em;
}
.lbc-guide-col--good .lbc-guide-col-head { color: #4ade80; }
.lbc-guide-col--bad .lbc-guide-col-head { color: #f87171; }
.lbc-guide-col-head i { font-size: 12px; }
.lbc-guide-items { display: flex; flex-direction: column; gap: 6px; }
.lbc-guide-item {
    font-size: 12.5px; color: rgba(255,255,255,.75); line-height: 1.45;
    padding: 2px 0;
}
.lbc-guide-col--bad .lbc-guide-item {
    text-decoration: line-through;
    color: rgba(248,113,113,.65);
}

/* ── Messages ── */
.lbc-body {
    flex: 1; min-height: 200px; max-height: 320px;
    overflow-y: auto; padding: 16px 20px;
    background: #080c1c;
    display: flex; flex-direction: column; gap: 10px;
    scrollbar-width: thin; scrollbar-color: rgba(109,92,255,.3) transparent;
}
.lbc-body::-webkit-scrollbar { width: 4px; }
.lbc-body::-webkit-scrollbar-thumb { background: rgba(109,92,255,.35); border-radius: 4px; }

.lbc-empty {
    margin: auto; text-align: center; padding: 16px 0;
    display: flex; flex-direction: column; align-items: center; gap: 8px;
}
.lbc-empty-icon {
    width: 52px; height: 52px; border-radius: 16px;
    background: rgba(109,92,255,.12); border: 1px solid rgba(109,92,255,.2);
    display: grid; place-items: center;
    font-size: 22px; color: #6d5cff;
}
.lbc-empty-title { font-size: 14px; font-weight: 700; color: rgba(255,255,255,.7); }
.lbc-empty-sub { font-size: 12px; color: rgba(255,255,255,.3); }

/* Messages */
.lbc-msg { display: flex; gap: 8px; align-items: flex-end; }
.lbc-msg.me { flex-direction: row-reverse; }
.lbc-msg-av {
    width: 28px; height: 28px; border-radius: 9px; flex-shrink: 0;
    background: linear-gradient(135deg, #6d5cff, #7c3aed);
    color: #fff; font-size: 9px; font-weight: 900;
    display: grid; place-items: center; overflow: hidden;
}
.lbc-msg-av img { width: 100%; height: 100%; object-fit: cover; }
.lbc-msg-bubble {
    max-width: 75%; padding: 9px 13px;
    border-radius: 16px 16px 16px 4px;
    background: rgba(109,92,255,.2);
    border: 1px solid rgba(109,92,255,.25);
    font-size: 13px; line-height: 1.5; color: #fff; word-break: break-word;
}
.lbc-msg.me .lbc-msg-bubble {
    border-radius: 16px 16px 4px 16px;
    background: linear-gradient(135deg, rgba(109,92,255,.55), rgba(124,58,237,.45));
    border-color: rgba(109,92,255,.45);
}
.lbc-msg-time { font-size: 10px; color: rgba(255,255,255,.28); margin-top: 3px; }
.lbc-msg.me .lbc-msg-time { text-align: right; }
.lbc-msg-bubble img { max-width: 190px; border-radius: 10px; cursor: pointer; display: block; margin-top: 4px; }

/* ── Footer / Composer ── */
.lbc-footer {
    padding: 12px 16px 14px;
    background: #0a0e1e;
    border-top: 1px solid rgba(109, 92, 255, 0.15);
    flex-shrink: 0;
}
.lbc-preview {
    display: none; align-items: center; gap: 8px;
    margin-bottom: 10px; padding: 7px 10px; border-radius: 10px;
    background: rgba(109,92,255,.1); border: 1px solid rgba(109,92,255,.2);
    color: rgba(255,255,255,.7);
}
.lbc-preview.is-open { display: flex; }
.lbc-preview img { width: 38px; height: 30px; object-fit: cover; border-radius: 7px; }
.lbc-preview small { flex: 1; font-size: 11px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
.lbc-preview-rm {
    border: 0; background: rgba(255,255,255,.1); color: #fff;
    border-radius: 7px; width: 22px; height: 22px; cursor: pointer;
    display: grid; place-items: center; font-size: 14px;
}
.lbc-compose { display: flex; gap: 8px; align-items: center; }
.lbc-compose-input {
    flex: 1; min-width: 0;
    border: 1px solid rgba(109, 92, 255, 0.25);
    background: rgba(255, 255, 255, 0.05);
    color: #fff; border-radius: 999px;
    padding: 11px 16px; font-size: 13px; outline: none;
    transition: border-color .2s, background .2s;
}
.lbc-compose-input::placeholder { color: rgba(255,255,255,.3); }
.lbc-compose-input:focus {
    border-color: rgba(109, 92, 255, 0.65);
    background: rgba(109, 92, 255, 0.08);
}
.lbc-img-btn, .lbc-send-btn {
    border: 0; display: grid; place-items: center; cursor: pointer; flex-shrink: 0;
    border-radius: 12px; transition: background .18s, filter .18s;
}
.lbc-img-btn {
    width: 40px; height: 40px;
    background: rgba(255,255,255,.06);
    border: 1px solid rgba(255,255,255,.08);
    color: rgba(255,255,255,.45); font-size: 15px;
}
.lbc-img-btn:hover { background: rgba(109,92,255,.2); color: #a5b4fc; }
.lbc-send-btn {
    width: 42px; height: 42px;
    background: linear-gradient(135deg, #6d5cff, #7c3aed);
    color: #fff; font-size: 15px;
    box-shadow: 0 4px 14px rgba(109,92,255,.45);
}
.lbc-send-btn:hover { filter: brightness(1.1); }
.lbc-send-btn:disabled { opacity: .38; cursor: not-allowed; box-shadow: none; }

/* ── Floating bubble ── */
.lbc-floating {
    position: fixed; right: 22px; bottom: 22px; z-index: 999998;
    width: 52px; height: 52px; border: 0; border-radius: 16px;
    background: linear-gradient(135deg, #6d5cff, #7c3aed);
    color: #fff; font-size: 20px;
    box-shadow: 0 12px 32px rgba(109,92,255,.5);
    cursor: pointer; display: grid; place-items: center;
    transition: transform .18s, box-shadow .18s;
}
.lbc-floating:hover { transform: translateY(-2px); box-shadow: 0 16px 40px rgba(109,92,255,.6); }
.lbc-dot {
    position: absolute; right: -3px; top: -3px;
    width: 12px; height: 12px; border-radius: 50%;
    background: #ef4444; border: 2px solid #0d1226; display: none;
}
body.lbc-lock { overflow: hidden; }

@media (max-width: 660px) {
    .lbc-overlay { padding: 0; align-items: flex-end; }
    .lbc-panel { width: 100%; border-radius: 20px 20px 0 0; max-height: 93vh; }
    .lbc-body { max-height: 45vh; }
    .lbc-guide-cols { grid-template-columns: 1fr; }
    .lbc-floating { right: 14px; bottom: 14px; }
}
</style>


<style>
/* Final mobile sticky checkout alignment: desktop hidden, mobile chat integrated into bottom bar. */
@media (min-width: 768px) {
    body.view-account-page .sticky-button,
    body.view-account-page .lbc-floating {
        display: none !important;
    }
}
@media (max-width: 767px) {
    body.view-account-page:not(.view-sticky-buy-visible) .lbc-floating {
        display: none !important;
    }
    body.view-account-page.view-sticky-buy-visible .lb-mobile-bottomnav,
    body.view-account-page.view-sticky-buy-visible .lb-mobile-bottomnav--count-3 {
        display: none !important;
    }
}
</style>
<!-- Floating trigger -->
<button type="button" class="lbc-floating" id="lbcTrigger" data-seller-chat-open <?= !$chat_allowed ? 'disabled' : '' ?>>
    <i class="fa-solid fa-comment-dots"></i>
    <span class="lbc-dot" id="lbcUnreadDot"></span>
</button>

<!-- Modal overlay -->
<div id="sellerChatModal" class="lbc-overlay" aria-hidden="true">
  <div class="lbc-panel" role="dialog" aria-modal="true">

    <!-- Header -->
    <div class="lbc-head">
      <div class="lbc-head-left">
        <?php if ($seller_icon): ?>
          <img class="lbc-avatar" src="<?= $seller_icon ?>" alt="<?= $seller_name ?>">
        <?php else: ?>
          <div class="lbc-avatar"><?= htmlspecialchars($seller_initials, ENT_QUOTES, 'UTF-8') ?></div>
        <?php endif; ?>
        <div class="lbc-head-info">
          <span class="lbc-head-name"><?= $seller_name ?></span>
          <span class="lbc-head-sub">
            <i class="fa-solid fa-shield-check"></i>
            <?= $chat_allowed ? t('Ask a question before buying') : t('Not accepting messages') ?>
          </span>
        </div>
      </div>
      <button type="button" class="lbc-close" data-seller-chat-close aria-label="Close">×</button>
    </div>

    <!-- Item preview strip -->
    <?php if (!empty($item_title)): ?>
    <div class="lbc-item-strip">
      <div class="lbc-item-icon">
        <?php if ($item_img): ?><img src="<?= htmlspecialchars($item_img, ENT_QUOTES, 'UTF-8') ?>" alt=""><?php else: ?><i class="<?= htmlspecialchars($item_fa, ENT_QUOTES, 'UTF-8') ?>"></i><?php endif; ?>
      </div>
      <div class="lbc-item-meta">
        <span class="lbc-item-title"><?= $item_title ?></span>
        <div class="lbc-item-tags">
          <?php if ($item_type): ?><span class="lbc-item-tag"><?= $item_type ?></span><?php endif; ?>
          <?php if ($item_server): ?><span class="lbc-item-tag"><?= $item_server ?></span><?php endif; ?>
        </div>
      </div>
      <?php if ($item_price): ?><div class="lbc-item-price"><?= htmlspecialchars($item_price, ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
    </div>
    <?php endif; ?>

    <?php if (!$chat_allowed): ?>
      <div class="lbc-body">
        <div class="lbc-empty">
          <div class="lbc-empty-icon"><i class="fa-solid fa-comment-slash"></i></div>
          <div class="lbc-empty-title"><?= t('Not accepting messages') ?></div>
          <div class="lbc-empty-sub"><?= t('This seller is currently not accepting new chat requests.') ?></div>
        </div>
      </div>
    <?php else: ?>

      <!-- Messaging Guidelines – always visible -->
      <div class="lbc-guidelines" id="lbcGuidelines">
        <div class="lbc-guide-header">
          <i class="fa-solid fa-shield-check"></i>
          <?= t('Messaging Guidelines') ?>
        </div>
        <p class="lbc-guide-text"><?= t('Keep all communication and payment inside the platform. Do not share external contacts or login details before purchase.') ?></p>
        <div class="lbc-guide-cols">
          <div class="lbc-guide-col lbc-guide-col--good">
            <div class="lbc-guide-col-head"><i class="fa-solid fa-circle-check"></i><?= t('Good Examples') ?></div>
            <div class="lbc-guide-items">
              <div class="lbc-guide-item"><?= t('Can you tell me if this account has any restrictions?') ?></div>
              <div class="lbc-guide-item"><?= t('Does this include full access and original email?') ?></div>
              <div class="lbc-guide-item"><?= t('Can you show me in-game screenshots?') ?></div>
              <div class="lbc-guide-item"><?= t('Would you be open to negotiating the price?') ?></div>
            </div>
          </div>
          <div class="lbc-guide-col lbc-guide-col--bad">
            <div class="lbc-guide-col-head"><i class="fa-solid fa-circle-xmark"></i><?= t('Avoid These') ?></div>
            <div class="lbc-guide-items">
              <div class="lbc-guide-item"><?= t("Let's talk over Telegram instead.") ?></div>
              <div class="lbc-guide-item"><?= t('Can I pay after you deliver the item?') ?></div>
              <div class="lbc-guide-item"><?= t('Send me login details first, I\'ll pay after.') ?></div>
              <div class="lbc-guide-item"><?= t('I work for the marketplace, I need your login.') ?></div>
            </div>
          </div>
        </div>
      </div>

      <!-- Message body -->
      <div class="lbc-body" id="lbcMessages">
        <div class="lbc-empty" id="lbcEmpty">
          <div class="lbc-empty-icon"><i class="fa-solid fa-comments"></i></div>
          <div class="lbc-empty-title"><?= t('Ask') ?> <?= $seller_name ?> <?= t('about this listing') ?></div>
          <div class="lbc-empty-sub"><?= t('Messages stay protected inside the platform.') ?></div>
        </div>
      </div>

      <!-- Footer / Compose -->
      <div class="lbc-footer">
        <form id="lbcForm" autocomplete="off">
          <input type="hidden" name="action"   value="client_seller_chat_send">
          <input type="hidden" name="seller_id" value="<?= $seller_id ?>">
          <input type="hidden" name="ref_type"  value="<?= htmlspecialchars($ref_type, ENT_QUOTES, 'UTF-8') ?>">
          <input type="hidden" name="ref_id"    value="<?= $ref_id ?>">
          <input type="file"   name="chat_image" id="lbcFileInput" accept="image/*" hidden>
          <div class="lbc-preview" id="lbcPreview">
            <img id="lbcPreviewThumb" src="" alt="">
            <small id="lbcPreviewName"></small>
            <button type="button" class="lbc-preview-rm" id="lbcPreviewRemove">×</button>
          </div>
          <div class="lbc-compose">
            <button type="button" class="lbc-img-btn" id="lbcImgBtn" title="<?= t('Attach image') ?>">
              <i class="fa-solid fa-image"></i>
            </button>
            <input type="text" name="message" id="lbcMsgInput"
                   class="lbc-compose-input"
                   placeholder="<?= t('Ask') ?> <?= $seller_name ?>..."
                   autocomplete="off">
            <button type="submit" class="lbc-send-btn" id="lbcSendBtn" disabled>
              <i class="fa-solid fa-paper-plane"></i>
            </button>
          </div>
        </form>
      </div>

    <?php endif; ?>
  </div>
</div>

<!-- Auth overlay (dark-themed) -->
<style>
.lbc-auth-overlay{position:fixed;inset:0;z-index:1000000;background:rgba(4,6,18,.85);backdrop-filter:blur(16px);display:none;align-items:center;justify-content:center;padding:20px}
.lbc-auth-overlay.is-open{display:flex}
.lbc-auth-card{width:min(460px,calc(100vw - 24px));background:#0d1226;border:1px solid rgba(109,92,255,.3);border-radius:20px;box-shadow:0 32px 80px rgba(0,0,0,.7);color:#fff;padding:32px;position:relative}
.lbc-auth-close{position:absolute;right:18px;top:18px;width:34px;height:34px;border:0;border-radius:10px;background:rgba(255,255,255,.1);color:#fff;font-size:20px;cursor:pointer;display:grid;place-items:center}
.lbc-auth-title{font-size:20px;font-weight:900;margin-bottom:22px;color:#fff}
.lbc-auth-tabs{display:grid;grid-template-columns:1fr 1fr;border:1px solid rgba(109,92,255,.3);border-radius:999px;padding:3px;background:rgba(109,92,255,.08);margin-bottom:20px}
.lbc-auth-tab{height:36px;border:0;background:transparent;color:rgba(255,255,255,.55);border-radius:999px;font-weight:700;font-size:13px;cursor:pointer;transition:.18s}
.lbc-auth-tab.is-active{background:linear-gradient(135deg,#6d5cff,#7c3aed);color:#fff;box-shadow:0 4px 14px rgba(109,92,255,.4)}
.lbc-auth-form{display:none}.lbc-auth-form.is-active{display:block}
.lbc-auth-label{display:block;font-size:13px;font-weight:700;margin:14px 0 6px;color:rgba(255,255,255,.65)}
.lbc-auth-input{width:100%;height:48px;border-radius:12px;border:1px solid rgba(109,92,255,.25);background:rgba(255,255,255,.05);color:#fff;font-size:14px;padding:0 14px;outline:none;transition:.18s;box-sizing:border-box}
.lbc-auth-input:focus{border-color:rgba(109,92,255,.65);background:rgba(109,92,255,.09)}
.lbc-auth-row{display:flex;align-items:center;justify-content:space-between;gap:12px;margin:12px 0}
.lbc-auth-check{display:flex;align-items:center;gap:7px;font-size:12px;color:rgba(255,255,255,.55)}
.lbc-auth-check input{accent-color:#6d5cff;width:14px;height:14px}
.lbc-auth-submit{width:100%;height:48px;border:0;border-radius:999px;background:linear-gradient(135deg,#6d5cff,#7c3aed);color:#fff;font-weight:800;font-size:15px;cursor:pointer;margin-top:6px;box-shadow:0 4px 18px rgba(109,92,255,.4);transition:.18s}
.lbc-auth-submit:hover{filter:brightness(1.1)}
.lbc-auth-error{display:none;margin:10px 0 0;padding:9px 12px;border-radius:10px;background:rgba(239,68,68,.1);border:1px solid rgba(239,68,68,.22);color:#fca5a5;font-size:12px}
.lbc-auth-error.is-open{display:block}
.lbc-auth-socials{display:grid;grid-template-columns:1fr 1fr;gap:8px;margin-top:14px;padding-top:14px;border-top:1px solid rgba(255,255,255,.07)}
.lbc-auth-social{height:44px;border:0;border-radius:12px;color:#fff;font-weight:700;font-size:13px;text-decoration:none;display:flex;align-items:center;justify-content:center;gap:7px}
.lbc-auth-google{background:#ea4335}.lbc-auth-discord{background:#5865f2}
@media(max-width:480px){.lbc-auth-card{padding:24px 18px}.lbc-auth-socials{grid-template-columns:1fr}}
</style>

<div id="lbcAuthOverlay" class="lbc-auth-overlay" aria-hidden="true">
  <div class="lbc-auth-card" role="dialog" aria-modal="true">
    <button type="button" class="lbc-auth-close" data-lbc-auth-close>&times;</button>
    <div class="lbc-auth-title"><?= t('Sign in to message seller') ?></div>
    <div class="lbc-auth-tabs">
      <button type="button" class="lbc-auth-tab is-active" data-lbc-auth-tab="login"><i class="fa-solid fa-lock-open me-1"></i> Login</button>
      <button type="button" class="lbc-auth-tab" data-lbc-auth-tab="register"><i class="fa-solid fa-user-plus me-1"></i> Register</button>
    </div>
    <form class="lbc-auth-form is-active" id="lbcLoginForm" autocomplete="on">
      <input type="hidden" name="action" value="auth_client_login">
      <input type="hidden" name="redirectUrl" value="<?= htmlspecialchars($_SERVER['REQUEST_URI'] ?? '/', ENT_QUOTES, 'UTF-8') ?>">
      <label class="lbc-auth-label">Email</label>
      <input class="lbc-auth-input" type="email" name="email" required>
      <label class="lbc-auth-label">Password</label>
      <input class="lbc-auth-input" type="password" name="password" required>
      <div class="lbc-auth-row">
        <label class="lbc-auth-check"><input type="checkbox" name="remember_me" value="1"> Remember me</label>
        <a href="<?= $base_url_inline ?>/forgot-password" style="color:#a5b4fc;font-size:12px;text-decoration:none">Forgot password?</a>
      </div>
      <button class="lbc-auth-submit" type="submit">Sign in</button>
      <div class="lbc-auth-error" id="lbcLoginError"></div>
    </form>
    <form class="lbc-auth-form" id="lbcRegisterForm" autocomplete="on">
      <input type="hidden" name="action" value="auth_client_register">
      <input type="hidden" name="redirectUrl" value="<?= htmlspecialchars($_SERVER['REQUEST_URI'] ?? '/', ENT_QUOTES, 'UTF-8') ?>">
      <label class="lbc-auth-label">Username</label>
      <input class="lbc-auth-input" type="text" name="username" required>
      <label class="lbc-auth-label">Email</label>
      <input class="lbc-auth-input" type="email" name="email" required>
      <label class="lbc-auth-label">Password</label>
      <input class="lbc-auth-input" type="password" name="password" minlength="6" required>
      <div class="lbc-auth-row">
        <label class="lbc-auth-check"><input type="checkbox" name="tos" value="1" required> I agree to the terms</label>
      </div>
      <button class="lbc-auth-submit" type="submit">Create account</button>
      <div class="lbc-auth-error" id="lbcRegisterError"></div>
    </form>
    <div class="lbc-auth-socials">
      <a class="lbc-auth-social lbc-auth-google" href="<?= $base_url_inline ?>/auth/google"><i class="fab fa-google"></i> Google</a>
      <a class="lbc-auth-social lbc-auth-discord" href="<?= $base_url_inline ?>/auth/discord"><i class="fab fa-discord"></i> Discord</a>
    </div>
  </div>
</div>

<script>
(function () {
    'use strict';
    if (window.lbcChatReady) return;
    window.lbcChatReady = true;

    /* ── Config ── */
    const SELLER_ID  = <?= (int)$seller_id ?>;
    const SELLER_ICO = <?= json_encode($seller_icon_raw) ?>;
    const SELLER_INI = <?= json_encode($seller_initials) ?>;
    const AJAX_URL   = <?= json_encode($ajax_url_inline) ?>;
    const CHAT_OK    = <?= $chat_allowed ? 'true' : 'false' ?>;
    const LOGGED_IN  = <?= $client_logged_in_inline ? 'true' : 'false' ?>;
    const BASE_URL   = <?= json_encode($base_url_inline) ?>;
    const AGREED_KEY = 'lbcAgreed_' + SELLER_ID;

    /* ── Elements ── */
    const overlay    = document.getElementById('sellerChatModal');
    const msgBox     = document.getElementById('lbcMessages');
    const form       = document.getElementById('lbcForm');
    const inp        = document.getElementById('lbcMsgInput');
    const sendBtn    = document.getElementById('lbcSendBtn');
    const fileInput  = document.getElementById('lbcFileInput');
    const imgBtn     = document.getElementById('lbcImgBtn');
    const preview    = document.getElementById('lbcPreview');
    const thumb      = document.getElementById('lbcPreviewThumb');
    const prevName   = document.getElementById('lbcPreviewName');
    const prevRm     = document.getElementById('lbcPreviewRemove');
    const dot        = document.getElementById('lbcUnreadDot');
    const guideWrap  = document.getElementById('lbcGuidelines');
    const guideToggle= document.getElementById('lbcGuideToggle');
    const agreeChk   = null; // no checkbox in this version

    /* ── State ── */
    let poll = null, sig = '', conv = null;
    let agreed = true; // no checkbox needed, guidelines are always visible

    /* ── Auth modal ── */
    const authOverlay = document.getElementById('lbcAuthOverlay');
    function authOpen(tab) {
        if (!authOverlay) return;
        authOverlay.classList.add('is-open');
        authOverlay.setAttribute('aria-hidden', 'false');
        document.body.classList.add('lbc-lock');
        document.querySelectorAll('[data-lbc-auth-tab]').forEach(b =>
            b.classList.toggle('is-active', b.dataset.lbcAuthTab === (tab || 'login')));
        document.querySelectorAll('.lbc-auth-form').forEach(f =>
            f.classList.toggle('is-active',
                (tab === 'register' && f.id === 'lbcRegisterForm') ||
                (tab !== 'register' && f.id === 'lbcLoginForm')));
    }
    function authClose() {
        if (!authOverlay) return;
        authOverlay.classList.remove('is-open');
        authOverlay.setAttribute('aria-hidden', 'true');
        document.body.classList.remove('lbc-lock');
    }
    window.lbOpenClientAuth = authOpen;
    window.openLoginModal   = () => authOpen('login');

    document.addEventListener('click', e => {
        if (e.target.closest('[data-lbc-auth-tab]')) {
            authOpen(e.target.closest('[data-lbc-auth-tab]').dataset.lbcAuthTab);
        }
        if (e.target.closest('[data-lbc-auth-close]') || e.target === authOverlay) authClose();
    });

    /* Auth form submit */
    function bindAuth(formId, errId) {
        const f = document.getElementById(formId);
        const er = document.getElementById(errId);
        if (!f) return;
        f.addEventListener('submit', async e => {
            e.preventDefault();
            if (er) er.classList.remove('is-open');
            const btn = f.querySelector('[type=submit]');
            if (btn) btn.disabled = true;
            try {
                const res = await fetch(BASE_URL + '/ajax', { method: 'POST', body: new FormData(f), credentials: 'same-origin' });
                const d = await parseJson(res);
                if (d.redirectUrl) { location.href = d.redirectUrl; return; }
                if (d.refreshPage || d.playSound === 'success') { location.reload(); return; }
                if (er) { er.textContent = d.message || d.error || 'Something went wrong.'; er.classList.add('is-open'); }
            } catch { if (er) { er.textContent = 'Request failed.'; er.classList.add('is-open'); } }
            finally { if (btn) btn.disabled = false; }
        });
    }
    bindAuth('lbcLoginForm', 'lbcLoginError');
    bindAuth('lbcRegisterForm', 'lbcRegisterError');

    /* ── Helper: JSON parse ── */
    function parseJson(r) {
        return r.text().then(t => {
            t = (t || '').trim();
            try { return JSON.parse(t); } catch (_) {
                const a = t.indexOf('{'), b = t.lastIndexOf('}');
                if (a !== -1 && b > a) return JSON.parse(t.slice(a, b + 1));
                throw new Error(t.slice(0, 200) || 'Invalid response');
            }
        });
    }

    /* ── Trigger auth if not logged in ── */
    function requireAuth() {
        chatClose();
        if (window.lbOpenClientAuth) { window.lbOpenClientAuth('login'); return; }
        /* fallbacks */
        const ids = ['authModal', 'loginModal', 'clientAuthModal'];
        for (const id of ids) {
            const el = document.getElementById(id);
            if (!el) continue;
            try { if (window.bootstrap?.Modal) { window.bootstrap.Modal.getOrCreateInstance(el).show(); return; } } catch (_) {}
            try { if (window.jQuery?.fn?.modal) { jQuery(el).modal('show'); return; } } catch (_) {}
        }
        document.dispatchEvent(new CustomEvent('lb:open-auth', { detail: { tab: 'login', source: 'seller-chat' } }));
    }

    /* ── Chat open / close ── */
    function chatOpen() {
        if (!LOGGED_IN) { requireAuth(); return; }
        if (!overlay) return;
        overlay.classList.add('is-open');
        overlay.setAttribute('aria-hidden', 'false');
        document.body.classList.add('lbc-lock');
        if (CHAT_OK) {
            syncSend();
            loadMessages();
            clearInterval(poll);
            poll = setInterval(loadMessages, 4000);
            setTimeout(() => inp && inp.focus(), 100);
        }
    }
    function chatClose() {
        if (!overlay) return;
        overlay.classList.remove('is-open');
        overlay.setAttribute('aria-hidden', 'true');
        document.body.classList.remove('lbc-lock');
        clearInterval(poll);
    }

    /* ── Click delegation ── */
    document.addEventListener('click', e => {
        if (e.target.closest('[data-seller-chat-open]')) { e.preventDefault(); chatOpen(); return; }
        if (e.target.closest('[data-seller-chat-close]')) { e.preventDefault(); chatClose(); return; }
        if (e.target === overlay) chatClose();
    });
    document.addEventListener('keydown', e => { if (e.key === 'Escape') { chatClose(); authClose(); } });

    /* ── Guidelines always visible, no toggle needed ── */
    function syncSend() {
        if (sendBtn) sendBtn.disabled = false; // always enabled
    }
    syncSend();

    /* ── Escape HTML ── */
    function esc(s) {
        return String(s || '').replace(/[&<>"']/g, m => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'}[m]));
    }

    /* ── Avatar ── */
    function avatar(isSeller) {
        if (isSeller && SELLER_ICO) return `<div class="lbc-msg-av"><img src="${esc(SELLER_ICO)}" alt=""></div>`;
        return `<div class="lbc-msg-av">${esc(isSeller ? SELLER_INI : 'ME')}</div>`;
    }

    /* ── Render message ── */
    function renderMsg(m) {
        if (!msgBox) return;
        const empty = document.getElementById('lbcEmpty');
        if (empty) empty.remove();
        const isSeller = m.sender_type === 'seller';
        const isImg    = m.message_type === 'image';
        const el = document.createElement('div');
        el.className = 'lbc-msg' + (isSeller ? '' : ' me');
        const body = isImg
            ? `<img src="${esc(m.body)}" onclick="window.open(this.src,'_blank')" alt="">`
            : esc(m.body).replace(/\n/g, '<br>');
        el.innerHTML = `${avatar(isSeller)}<div><div class="lbc-msg-bubble">${body}</div><div class="lbc-msg-time">${esc(m.created_at_fmt || '')}</div></div>`;
        msgBox.appendChild(el);
        msgBox.scrollTop = msgBox.scrollHeight;
    }

    /* ── Load messages ── */
    function loadMessages() {
        if (!CHAT_OK || !msgBox) return;
        const fd = new FormData();
        fd.append('action', 'client_seller_chat_load');
        fd.append('seller_id', SELLER_ID);
        fd.append('sig', sig);
        if (conv) fd.append('conv_id', conv);
        fetch(AJAX_URL, { method: 'POST', body: fd, credentials: 'same-origin' })
            .then(parseJson)
            .then(d => {
                if (d.conv_id) conv = d.conv_id;
                if (d.sig && d.sig !== sig) {
                    sig = d.sig;
                    msgBox.innerHTML = '';
                    (d.messages || []).forEach(renderMsg);
                    if ((d.messages || []).length) {
                        agreed = true;
                        syncSend();
                    }
                }
                if (dot) dot.style.display = (d.unread_client > 0) ? 'block' : 'none';
            })
            .catch(() => {});
    }

    /* ── Image attach ── */
    if (imgBtn && fileInput) imgBtn.onclick = () => fileInput.click();
    if (fileInput) fileInput.onchange = function () {
        const f = this.files[0];
        if (!f) return;
        const r = new FileReader();
        r.onload = ev => { if (thumb) thumb.src = ev.target.result; };
        r.readAsDataURL(f);
        if (prevName) prevName.textContent = f.name;
        if (preview) preview.classList.add('is-open');
    };
    if (prevRm) prevRm.onclick = () => {
        if (fileInput) fileInput.value = '';
        if (preview) preview.classList.remove('is-open');
    };

    /* ── Compose: enable send when has text ── */
    if (inp) inp.addEventListener('input', syncSend);

    /* ── Send message ── */
    if (form) form.onsubmit = async function (e) {
        e.preventDefault();
        if (!LOGGED_IN) { requireAuth(); return; }
        const text = (inp ? inp.value : '').trim();
        const hasFile = fileInput && fileInput.files[0];
        if (!text && !hasFile) return;
        if (sendBtn) sendBtn.disabled = true;
        const fd = new FormData(form);
        fd.set('action', 'client_seller_chat_send');
        fd.set('seller_id', SELLER_ID);
        if (conv) fd.set('conv_id', conv);
        try {
            const d = await fetch(AJAX_URL, { method: 'POST', body: fd, credentials: 'same-origin' }).then(parseJson);
            if (d.success) {
                conv = d.conv_id || conv;
                if (text) renderMsg({ sender_type: 'client', body: text, created_at_fmt: d.created_at, message_type: 'text' });
                if (d.image_url) renderMsg({ sender_type: 'client', body: d.image_url, created_at_fmt: d.created_at, message_type: 'image' });
                if (inp) inp.value = '';
                if (fileInput) fileInput.value = '';
                if (preview) preview.classList.remove('is-open');
                // Redirect to chat page after successful send
                setTimeout(function() { window.location.href = BASE_URL + '/profile/chat'; }, 600);
            } else {
                const em = d.message || d.error || (d.sendToast && d.sendToast.message) || 'Could not send message.';
                if (d.auth_required || /log.?in|unauthorized/i.test(em)) requireAuth();
                else alert(em);
            }
        } catch (err) {
            alert(err?.message || 'Could not send message.');
        } finally {
            syncSend();
        }
    };

    /* Enter to send */
    if (inp) inp.onkeydown = e => {
        if (e.key === 'Enter' && !e.shiftKey) { e.preventDefault(); form && form.dispatchEvent(new Event('submit', { cancelable: true })); }
    };

})();
</script>
<?php endif; ?>


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
            if (!$stickySection.length || !$hideSticky.length) return;

            var isMobile = window.matchMedia && window.matchMedia('(max-width: 767px)').matches;
            var windowHeight = $(window).height();
            var elementTop = $hideSticky.offset().top;
            var elementBottom = elementTop + $hideSticky.outerHeight();
            var scrollTop = $(window).scrollTop();
            var checkoutVisible = (scrollTop + windowHeight > elementTop && scrollTop < elementBottom);
            var shouldShowSticky = isMobile && !checkoutVisible;

            $('body').toggleClass('view-sticky-buy-visible', shouldShowSticky);
            $('.view-account-page').toggleClass('view-sticky-buy-visible', shouldShowSticky);

            if (!isMobile) {
                $stickySection.css({ transform: '', transition: '' });
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
