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

$gameConfig = is_array($gameConfig ?? null) ? $gameConfig : [];
$cfg = is_array($topupsConfig ?? null) ? $topupsConfig : [];
$schema = is_array($topupSchema ?? null) ? $topupSchema : [];
$topups = is_array($topups ?? null) ? $topups : [];
$topupRegions = is_array($topupRegions ?? null) ? $topupRegions : [];
$gameSlug = (string)($game ?? ($gameConfig['slug'] ?? ''));
$gameName = (string)($gameConfig['name'] ?? ucwords(str_replace('-', ' ', $gameSlug)));
$gameIcon = (string)($gameConfig['icon'] ?? '');
$gameBanner = (string)($gameConfig['banner'] ?? '');
$serviceLabel = (string)($cfg['service_label'] ?? 'Top Up');
$pageTitle = (string)($cfg['page_title'] ?? ('Buy ' . $gameName . ' ' . $serviceLabel));
$pageDesc = (string)($cfg['page_description'] ?? ('Grab ' . $gameName . ' ' . $serviceLabel . ' at a fair price and get secure delivery.'));
$amountLabel = (string)($cfg['amount_label'] ?? 'Available Offers');
$regionLabel = (string)($cfg['region_label'] ?? 'Region');
$checkoutFields = isset($schema['checkout_fields']) && is_array($schema['checkout_fields']) ? $schema['checkout_fields'] : [];
if (!$checkoutFields) {
    $checkoutFields = [['key' => 'account_id', 'label' => 'Account ID', 'type' => 'text', 'required' => true]];
}
$isLolTopupShop = in_array(strtolower(trim((string)$gameSlug)), ['league-of-legends', 'lol', 'league'], true);
if ($isLolTopupShop && $checkoutFields) {
    foreach ($checkoutFields as &$tuCheckoutField) {
        if (!is_array($tuCheckoutField)) continue;
        $tuCheckoutKey = strtolower(trim((string)($tuCheckoutField['key'] ?? '')));
        $tuCheckoutLabel = strtolower(trim((string)($tuCheckoutField['label'] ?? '')));
        if ($tuCheckoutKey === 'summoner_name' || $tuCheckoutKey === 'summoner' || $tuCheckoutLabel === 'summoner name') {
            $tuCheckoutField['label'] = 'Riot ID';
            if (empty($tuCheckoutField['placeholder']) || stripos((string)$tuCheckoutField['placeholder'], 'summoner') !== false) {
                $tuCheckoutField['placeholder'] = 'Enter Riot ID';
            }
        }
    }
    unset($tuCheckoutField);
}

if (!function_exists('lb_tu_region_map')) {
    function lb_tu_region_map($gameSlug = '') {
        $lol = [
            'euw' => 'EU-West', 'eune' => 'EU-Nordic & East', 'na' => 'North America', 'br' => 'Brazil',
            'lan' => 'Latin America North', 'las' => 'Latin America South', 'oce' => 'Oceania', 'ru' => 'Russia',
            'tr' => 'Turkey', 'jp' => 'Japan', 'kr' => 'Korea', 'pbe' => 'PBE', 'me' => 'Middle East',
            'vn' => 'Vietnam', 'ph' => 'Philippines', 'sg' => 'Singapore', 'th' => 'Thailand', 'tw' => 'Taiwan',
            'global' => 'Global'
        ];
        return in_array((string)$gameSlug, ['league-of-legends','lol','league'], true) ? $lol : ['global' => 'Global'];
    }
}
if (!function_exists('lb_tu_region_key')) {
    function lb_tu_region_key($value, $gameSlug = '') {
        $raw = strtolower(trim((string)$value));
        if ($raw === '') return 'global';
        $raw = str_replace(['_', ' '], '-', $raw);
        $aliases = [
            'eu-west' => 'euw', 'euwest' => 'euw', 'euw' => 'euw',
            'eu-nordic-&-east' => 'eune', 'eu-nordic-and-east' => 'eune', 'eune' => 'eune',
            'north-america' => 'na', 'na' => 'na',
            'brazil' => 'br', 'br' => 'br',
            'latin-america-north' => 'lan', 'lan' => 'lan',
            'latin-america-south' => 'las', 'las' => 'las',
            'oceania' => 'oce', 'oce' => 'oce',
            'russia' => 'ru', 'ru' => 'ru',
            'turkey' => 'tr', 'tr' => 'tr',
            'japan' => 'jp', 'jp' => 'jp',
            'korea' => 'kr', 'kr' => 'kr',
            'middle-east' => 'me', 'me' => 'me',
            'vietnam' => 'vn', 'vn' => 'vn',
            'philippines' => 'ph', 'ph' => 'ph',
            'singapore' => 'sg', 'sg' => 'sg',
            'thailand' => 'th', 'th' => 'th',
            'taiwan' => 'tw', 'tw' => 'tw',
            'pbe' => 'pbe', 'global' => 'global'
        ];
        return $aliases[$raw] ?? preg_replace('/[^a-z0-9]+/', '-', $raw);
    }
}
if (!function_exists('lb_tu_region_label')) {
    function lb_tu_region_label($value, $gameSlug = '') {
        $map = lb_tu_region_map($gameSlug);
        $key = lb_tu_region_key($value, $gameSlug);
        if (isset($map[$key])) return $map[$key];
        $v = trim((string)$value);
        return $v !== '' ? $v : 'Global';
    }
}
if (!function_exists('lb_tu_region_icon')) {
    function lb_tu_region_icon($key): string {
        $key = strtolower((string)$key);
        $flags = [
            'euw' => 'fa-earth-europe', 'eune' => 'fa-mountain-sun', 'na' => 'fa-earth-americas', 'br' => 'fa-flag',
            'lan' => 'fa-earth-americas', 'las' => 'fa-earth-americas', 'oce' => 'fa-earth-oceania', 'ru' => 'fa-globe',
            'tr' => 'fa-flag', 'jp' => 'fa-torii-gate', 'kr' => 'fa-yin-yang', 'pbe' => 'fa-flask', 'me' => 'fa-mosque',
            'vn' => 'fa-star', 'ph' => 'fa-sun', 'sg' => 'fa-city', 'th' => 'fa-gopuram', 'tw' => 'fa-cloud', 'global' => 'fa-globe'
        ];
        return $flags[$key] ?? 'fa-globe';
    }
}
if (!function_exists('lb_topup_currency_code')) {
    function lb_topup_currency_code(): string {
        $currency = strtoupper(trim((string)($_SESSION['currency'] ?? $_SESSION['currency_code'] ?? 'EUR')));
        return $currency !== '' ? $currency : 'EUR';
    }
}
if (!function_exists('lb_topup_currency_rate')) {
    function lb_topup_currency_rate(string $currencyCode): float {
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
if (!function_exists('lb_topup_currency_symbol')) {
    function lb_topup_currency_symbol(string $currencyCode): string {
        $currencyCode = strtoupper(trim($currencyCode));
        if (function_exists('util_format_currency_display')) {
            return util_format_currency_display($currencyCode);
        }
        return $currencyCode === 'USD' ? '$' : '€';
    }
}
if (!function_exists('lb_topup_convert_cents')) {
    function lb_topup_convert_cents(int $cents, string $currencyCode): int {
        $currencyCode = strtoupper(trim($currencyCode));
        if ($currencyCode === 'EUR') return $cents;
        return (int)round($cents * lb_topup_currency_rate($currencyCode));
    }
}
if (!function_exists('lb_topup_money')) {
    function lb_topup_money($cents, ?string $currencyCode = null) {
        $currencyCode = strtoupper(trim((string)($currencyCode ?: lb_topup_currency_code())));
        $converted = lb_topup_convert_cents((int)$cents, $currencyCode);
        $symbol = lb_topup_currency_symbol($currencyCode);
        $formatted = function_exists('util_format_price_display')
            ? util_format_price_display($converted)
            : number_format($converted / 100, 2, $currencyCode === 'EUR' ? ',' : '.', $currencyCode === 'EUR' ? '.' : ',');
        return $symbol . $formatted;
    }
}

$topupCurrencyCode = lb_topup_currency_code();
$topupCurrencyRate = lb_topup_currency_rate($topupCurrencyCode);
$topupCurrencySymbol = lb_topup_currency_symbol($topupCurrencyCode);
if (!function_exists('lb_topup_auto_img')) {
    function lb_topup_auto_img(array $row, string $gameSlug = ''): string {
        $slug = strtolower(trim($gameSlug ?: (string)($row['game_slug'] ?? $row['db_game_slug'] ?? $row['game'] ?? '')));
        $unit = strtolower(trim((string)($row['offer_unit'] ?? $row['unit'] ?? '')));
        $searchText = implode(' ', [
            (string)($row['offer_amount'] ?? ''),
            (string)($row['amount'] ?? ''),
            (string)($row['offer_title'] ?? ''),
            (string)($row['title'] ?? ''),
            (string)($row['name'] ?? ''),
            (string)($row['offer_key'] ?? ''),
            (string)($row['image'] ?? ''),
        ]);

        $amount = '';
        if (preg_match('~rp\s*([0-9]{3,5})|([0-9]{3,5})\s*(?:rp|riot\s*points?)|rp([0-9]{3,5})~i', $searchText, $m)) {
            foreach ([1, 2, 3] as $idx) {
                if (!empty($m[$idx])) { $amount = preg_replace('/[^0-9]/', '', $m[$idx]); break; }
            }
        }
        if ($amount === '') {
            $amount = preg_replace('/[^0-9]/', '', (string)($row['offer_amount'] ?? $row['amount'] ?? ''));
        }

        $validRp = ['460','574','1005','1380','2105','2800','3625','4500','5295','6500','10875','13500'];
        $isLol = in_array($slug, ['league-of-legends','lol','league'], true);
        $looksLikeRp = ($unit === '' || strpos($unit, 'rp') !== false || strpos($unit, 'riot') !== false || stripos($searchText, 'rp') !== false || stripos($searchText, 'riot points') !== false);

        if ($isLol && $amount !== '' && in_array($amount, $validRp, true) && $looksLikeRp) {
            return '/public/assets/website/images/league-of-legends/riot-points/rp' . $amount . '.webp';
        }
        return '';
    }
}
if (!function_exists('lb_topup_img')) {
    function lb_topup_img($row) {
        $auto = lb_topup_auto_img(is_array($row) ? $row : [], (string)($GLOBALS['gameSlug'] ?? ''));
        if ($auto !== '') return $auto;
        return (string)($row['image'] ?? '');
    }
}

$normalizedTopups = [];
foreach ($topups as $row) {
    if (!is_array($row)) continue;
    $regionKey = lb_tu_region_key($row['region'] ?? 'Global', $gameSlug);
    $row['region'] = $regionKey;
    $row['region_label'] = lb_tu_region_label($regionKey, $gameSlug);
    $autoImage = lb_topup_auto_img($row, $gameSlug);
    if ($autoImage !== '') { $row['image'] = $autoImage; }
    if (empty($row['offer_key'])) {
        $row['offer_key'] = strtolower(trim(preg_replace('/[^a-z0-9]+/i', '-', (string)($row['offer_title'] ?? 'offer')), '-'));
    }
    $normalizedTopups[] = $row;
}
$topups = $normalizedTopups;

if (!function_exists('lb_enrich_rows_with_seller_stats')) {
    function lb_enrich_rows_with_seller_stats(array $rows): array
    {
        $sellerIds = [];
        foreach ($rows as $row) {
            $sellerId = (int)($row['seller_id'] ?? $row['id'] ?? 0);
            if ($sellerId > 0) $sellerIds[$sellerId] = $sellerId;
        }
        if (!$sellerIds) return $rows;

        $stats = [];
        global $db;
        if (!empty($db)) {
            try {
                $ids = array_values($sellerIds);
                $placeholders = implode(',', array_fill(0, count($ids), '?'));
                $result = $db->run(
                    "SELECT seller_id, total_sales AS service_sales FROM seller_stats WHERE seller_id IN ($placeholders)",
                    ...$ids
                ) ?: [];
                foreach ($result as $statRow) {
                    $stats[(int)($statRow['seller_id'] ?? 0)] = max(0, (int)($statRow['service_sales'] ?? 0));
                }
            } catch (Throwable $e) {}
        }

        foreach ($rows as &$row) {
            $sellerId = (int)($row['seller_id'] ?? $row['id'] ?? 0);
            if ($sellerId > 0 && array_key_exists($sellerId, $stats)) {
                $row['seller_total_sales'] = $stats[$sellerId];
                $row['total_sales'] = $stats[$sellerId];
                $row['total_sold'] = $stats[$sellerId];
                $row['seller_sold'] = $stats[$sellerId];
            }
        }
        unset($row);
        return $rows;
    }
}
$topups = lb_enrich_rows_with_seller_stats($topups);

$regionSources = [];
foreach ($topups as $row) {
    $regionSources[(string)$row['region']] = (string)$row['region_label'];
}
if (!$regionSources) {
    $map = lb_tu_region_map($gameSlug);
    foreach ($map as $k => $label) $regionSources[$k] = $label;
}
$selectedRegion = isset($selectedTopupRegion) && trim((string)$selectedTopupRegion) !== '' ? lb_tu_region_key($selectedTopupRegion, $gameSlug) : '';
if ($selectedRegion === '' || !isset($regionSources[$selectedRegion])) {
    $keys = array_keys($regionSources);
    $selectedRegion = $keys ? (string)$keys[0] : 'global';
}

$offersByKey = [];
foreach ($topups as $r) {
    $regionKey = (string)($r['region'] ?? 'global');
    $offerKey = (string)($r['offer_key'] ?? 'offer');
    $groupKey = $regionKey . '||' . $offerKey;
    if (!isset($offersByKey[$groupKey])) $offersByKey[$groupKey] = [];
    $offersByKey[$groupKey][] = $r;
}
$offerCards = [];
foreach ($offersByKey as $groupKey => $rows) {
    usort($rows, function ($a, $b) {
        return ((int)($a['price'] ?? 0) <=> (int)($b['price'] ?? 0)) ?: ((int)($a['waiting_time_minutes'] ?? 0) <=> (int)($b['waiting_time_minutes'] ?? 0));
    });
    $best = $rows[0];
    $best['_offer_rows'] = $rows;
    $offerCards[] = $best;
}
usort($offerCards, function ($a, $b) use ($selectedRegion) {
    $ar = (string)($a['region'] ?? 'global') === $selectedRegion ? 0 : 1;
    $br = (string)($b['region'] ?? 'global') === $selectedRegion ? 0 : 1;
    return ($ar <=> $br) ?: ((float)($a['offer_amount'] ?? 0) <=> (float)($b['offer_amount'] ?? 0)) ?: ((int)($a['price'] ?? 0) <=> (int)($b['price'] ?? 0));
});
?>
<?= $this->layout('website/layouts/master', ['meta' => $meta ?? [], 'bodyClass' => 'topups-shop-page ranked-accounts-page']) ?>


<?php
/* ---------------------------------------------------------------------
 * Seller footer component (avatar / online dot / verified badge / sold
 * & trusted chips) — reused here so the seller card inside "Seller
 * Instructions" and every row of "Other Sellers" renders with the exact
 * same markup/CSS as the rest of the site.
 * Adjust the path below if your component lives somewhere else.
 * ------------------------------------------------------------------- */
if (!function_exists('lb_render_seller_footer')) {
    $lbSellerFooterCandidates = [
        defined('ROOT_PATH') ? rtrim((string)ROOT_PATH, '/') . '/public/views/website/pages/components/seller/seller-footer.php' : '',
        defined('VIEWS_PATH') ? rtrim((string)VIEWS_PATH, '/') . '/website/pages/components/seller/seller-footer.php' : '',
        defined('APP_ROOT') ? rtrim((string)APP_ROOT, '/') . '/public/views/website/pages/components/seller/seller-footer.php' : '',
        __DIR__ . '/components/seller/seller-footer.php',
        __DIR__ . '/../components/seller/seller-footer.php',
        __DIR__ . '/../../components/seller/seller-footer.php',
        isset($_SERVER['DOCUMENT_ROOT']) ? rtrim((string)$_SERVER['DOCUMENT_ROOT'], '/') . '/public/views/website/pages/components/seller/seller-footer.php' : '',
    ];
    foreach ($lbSellerFooterCandidates as $lbSellerFooterCandidate) {
        if ($lbSellerFooterCandidate !== '' && is_file($lbSellerFooterCandidate)) {
            include_once $lbSellerFooterCandidate;
            break;
        }
    }
}

/* Trust-signal config. The Trustpilot row is shown by default and can
 * still be overridden through the page config if needed. */
$tuTrustpilotRating  = isset($cfg['trustpilot_rating']) && $cfg['trustpilot_rating'] !== '' ? (float)$cfg['trustpilot_rating'] : 4.9;
$tuTrustpilotReviews = isset($cfg['trustpilot_review_count']) && $cfg['trustpilot_review_count'] !== '' ? (int)$cfg['trustpilot_review_count'] : 1000;
$tuTrustpilotUrl     = (string)($cfg['trustpilot_url'] ?? 'https://www.trustpilot.com');
$tuShowTrustpilot    = true;

$tuSiteRating  = isset($cfg['site_rating']) && $cfg['site_rating'] !== '' ? (float)$cfg['site_rating'] : null;
$tuSiteOrders  = isset($cfg['site_orders_count']) && $cfg['site_orders_count'] !== '' ? (int)$cfg['site_orders_count'] : null;
$tuShowTrustedBy = $tuSiteRating !== null && $tuSiteOrders !== null;
?>
<style>
/* =========================================================================
   Top Up shop — GameBoost-inspired layout, fully responsive
   ========================================================================= */
body.topups-shop-page,
.topups-shop-page .site-main,
.topups-shop-page main{color:#fff}
.lb-topups{min-height:100vh;color:#fff;padding-top:var(--tu-safe-top,var(--lb-content-top,184px));padding-bottom:40px;box-sizing:border-box}
.lb-topups.lb-topups--empty{padding-top:0!important}
.lb-topups *{box-sizing:border-box}
.lb-topups__wrap{max-width:1500px;margin:0 auto;padding:0 28px}

/* Hero */
.lb-topups__hero{position:relative;border-bottom:1px solid rgba(255,255,255,.06);overflow:hidden}
.lb-topups__hero-inner{position:relative;display:flex;align-items:center;gap:22px;min-height:170px;padding:36px 0}
.lb-topups__icon{width:74px;height:74px;min-width:74px;border-radius:20px;background:rgba(255,255,255,.045);border:1px solid rgba(255,255,255,.1);display:flex;align-items:center;justify-content:center;box-shadow:0 18px 50px rgba(0,0,0,.28)}
.lb-topups__icon img{width:46px;height:46px;border-radius:12px;object-fit:cover}
.lb-topups__icon i{font-size:30px;color:#7c6cff}
.lb-topups__eyebrow{font-size:12px;letter-spacing:.13em;text-transform:uppercase;color:#8b9bff;font-weight:900;margin-bottom:8px}
.lb-topups__title{margin:0;font-size:29px;line-height:1.12;font-weight:950;letter-spacing:-.03em}
.lb-topups__desc{margin:8px 0 0;color:#a9adc4;font-size:15px;max-width:640px}

/* Layout */
.lb-topups__layout{display:grid;grid-template-columns:minmax(0,1fr) 380px;gap:32px;padding:38px 0 110px}
.lb-topups__section-title{font-size:24px;font-weight:950;letter-spacing:-.025em;margin:0 0 14px}

/* Regions */
.lb-topups__regions{display:flex;flex-wrap:wrap;gap:11px;margin-bottom:32px}
.lb-topups__pill{appearance:none;border:1px solid rgba(255,255,255,.09);background:#10141e;color:#fff;border-radius:999px;padding:8px 16px 8px 8px;display:inline-flex;align-items:center;gap:10px;font-weight:850;font-size:14px;transition:.15s ease;cursor:pointer}
.lb-topups__pill:hover{border-color:rgba(59,130,246,.5);background:#131a27}
.lb-topups__pill-icon{width:30px;height:30px;min-width:30px;border-radius:50%;background:#0a0f18;border:1px solid rgba(255,255,255,.08);display:grid;place-items:center;color:#eaf2ff;font-size:13px}
.lb-topups__pill.is-active{background:linear-gradient(135deg,#102746,#17396c);border-color:#2f7dff;box-shadow:0 0 0 1px rgba(47,125,255,.35),0 12px 28px rgba(47,125,255,.15)}
.lb-topups__check{width:24px;height:24px;min-width:24px;border-radius:999px;border:1px solid rgba(255,255,255,.1);display:grid;place-items:center;margin-left:2px;font-size:11px}
.lb-topups__pill.is-active .lb-topups__check{background:#3672ff;border-color:#3672ff}

/* Offers grid */
.lb-topups__offers{display:grid;grid-template-columns:repeat(5,minmax(0,1fr));gap:14px}
.lb-topups__offer{position:relative;appearance:none;text-align:left;border:1px solid rgba(255,255,255,.085);background:linear-gradient(180deg,#111827,#0f141d);color:#fff;border-radius:16px;overflow:hidden;display:grid;grid-template-rows:100px auto;min-height:172px;cursor:pointer;transition:.15s ease;padding:0}
.lb-topups__offer:hover{transform:translateY(-2px);border-color:rgba(59,130,246,.55);background:linear-gradient(180deg,#142033,#101722)}
.lb-topups__offer.is-active{background:linear-gradient(180deg,#173b70 0%,#162947 50%,#101722 51%);border-color:#2f7dff;box-shadow:0 0 0 1px rgba(47,125,255,.3),0 16px 40px rgba(47,125,255,.13)}
.lb-topups__offer-badge{position:absolute;top:10px;left:10px;z-index:2;display:none;align-items:center;gap:5px;padding:4px 9px;border-radius:999px;background:linear-gradient(135deg,#ff9f43,#ff6b6b);color:#1a0e00;font-size:10.5px;font-weight:950;letter-spacing:.02em;text-transform:uppercase;box-shadow:0 6px 14px rgba(255,107,107,.3)}
.lb-topups__offer.is-popular .lb-topups__offer-badge{display:inline-flex}
.lb-topups__offer-cashback{position:absolute;bottom:10px;left:10px;z-index:2;display:none;align-items:center;gap:4px;padding:3px 8px;border-radius:999px;background:rgba(250,204,21,.14);border:1px solid rgba(250,204,21,.35);color:#facc15;font-size:10.5px;font-weight:900}
.lb-topups__offer-media{display:flex;align-items:center;justify-content:center;padding:16px 16px 6px;background:radial-gradient(circle at 50% 45%,rgba(255,196,70,.13),transparent 58%)}
.lb-topups__offer-img{height:80px;width:100%;display:flex;align-items:center;justify-content:center}
.lb-topups__offer-img img{max-width:108px;max-height:74px;object-fit:contain;filter:drop-shadow(0 10px 16px rgba(255,190,60,.2))}
.lb-topups__offer-img i{font-size:40px;color:#7c6cff}
.lb-topups__offer-info{display:grid;gap:5px;padding:13px 16px 14px;background:rgba(5,8,13,.28);border-top:1px solid rgba(255,255,255,.06)}
.lb-topups__offer-title{font-size:17px;line-height:1.15;font-weight:950;color:#fff;letter-spacing:-.02em}
.lb-topups__offer-price{font-size:16px;color:#b9c1d3;font-weight:800}
.lb-topups__offer-check{position:absolute;top:10px;right:10px;width:27px;height:27px;border-radius:9px;border:1px solid rgba(255,255,255,.1);background:rgba(7,10,16,.72);backdrop-filter:blur(8px);display:grid;place-items:center;color:transparent;z-index:2}
.lb-topups__offer.is-active .lb-topups__offer-check{background:#3672ff;color:#fff;border-color:#3672ff}
.lb-topups__empty{padding:54px 18px;border:1px dashed rgba(255,255,255,.14);border-radius:16px;text-align:center;color:#a9adc4;background:rgba(255,255,255,.025)}

.lb-shop-empty-notify-offset{
    --lb-empty-extra-top:0px;
    --lb-empty-top-gap:112px;
    --lb-empty-bottom-gap:112px;
    padding-top:calc(var(--lb-empty-top-gap) + var(--lb-empty-extra-top))!important;
    padding-bottom:var(--lb-empty-bottom-gap)!important;
    min-height:calc(100svh - var(--lb-empty-page-chrome, 360px));
    display:flex;
    align-items:center;
    justify-content:center;
}
.lb-shop-empty-notify-offset > .lb-cs2{margin:0 auto!important;}
@media(max-width:1180px){.lb-shop-empty-notify-offset{--lb-empty-top-gap:100px;--lb-empty-bottom-gap:100px;}}
@media(max-width:920px){.lb-shop-empty-notify-offset{--lb-empty-top-gap:88px;--lb-empty-bottom-gap:88px;}}
@media(max-width:760px){.lb-shop-empty-notify-offset{--lb-empty-top-gap:58px;--lb-empty-bottom-gap:68px;min-height:auto;}}
@media(max-width:420px){.lb-shop-empty-notify-offset{--lb-empty-top-gap:50px;--lb-empty-bottom-gap:60px;}}

/* Sidebar */
.lb-topups__side{position:relative;min-width:0}
.lb-topups__side-sticky{position:sticky;top:140px;align-self:start}
.lb-topups__checkout{position:relative;background:#12161f;border:1px solid rgba(255,255,255,.09);border-radius:18px;overflow:hidden;box-shadow:0 20px 60px rgba(0,0,0,.3)}
.lb-topups__row{display:flex;align-items:center;justify-content:space-between;gap:16px;padding:18px 20px;border-bottom:1px solid rgba(255,255,255,.07)}
.lb-topups__label{text-transform:uppercase;color:#9197ab;font-size:11.5px;font-weight:900;letter-spacing:.03em}
.lb-topups__price-line{display:flex;align-items:baseline;gap:6px;margin-top:4px}
.lb-topups__price{font-size:34px;line-height:1;font-weight:950;letter-spacing:-.03em}
.lb-topups__currency{font-size:14px;color:#a6a1b8}
.lb-topups__cashback-badge{display:none;align-items:center;gap:5px;margin-top:8px;padding:3px 9px;border-radius:999px;background:rgba(250,204,21,.12);border:1px solid rgba(250,204,21,.32);color:#facc15;font-size:11px;font-weight:900;width:fit-content}
.lb-topups__card-img{width:60px;height:60px;object-fit:contain}
.lb-topups__muted{color:#a3a7ba}
.lb-topups__qty{display:grid;grid-template-columns:38px 46px 38px;align-items:center;background:#0c0f17;border:1px solid rgba(255,255,255,.1);border-radius:12px;overflow:hidden}
.lb-topups__qty button{height:42px;background:transparent;border:0;color:#fff;font-size:21px;font-weight:900;cursor:pointer}
.lb-topups__qty button:hover{background:rgba(255,255,255,.06)}
.lb-topups__qty strong{text-align:center}
.lb-topups__field{position:relative;display:grid;grid-template-columns:1fr 170px;align-items:center;gap:14px;padding:12px 20px}
.lb-topups__field input{width:100%;background:#0c0f17;border:1px solid rgba(255,255,255,.12);border-radius:10px;color:#fff;padding:12px 13px;font-size:14px;transition:border-color .15s ease,box-shadow .15s ease}
.lb-topups__field input:focus{outline:none;border-color:#3b82f6}
.lb-topups__field.has-error input{border-color:#f59e0b;box-shadow:0 0 0 1px rgba(245,158,11,.22)}
.lb-topups__field-error{position:absolute;top:calc(100% - 2px);left:20px;right:20px;z-index:30;display:inline-flex;align-items:center;gap:9px;padding:9px 13px;border-radius:11px;background:#1a1f2b;border:1px solid rgba(245,158,11,.4);box-shadow:0 16px 32px rgba(0,0,0,.4);font-size:12.5px;font-weight:800;color:#fde9c8;animation:lbFieldErrorIn .15s ease}
.lb-topups__field-error:before{content:"";position:absolute;left:18px;top:-5px;width:9px;height:9px;background:#1a1f2b;border-left:1px solid rgba(245,158,11,.4);border-top:1px solid rgba(245,158,11,.4);transform:rotate(45deg)}
.lb-topups__field-error i{color:#f59e0b;font-size:13px;flex:0 0 auto}
@keyframes lbFieldErrorIn{from{opacity:0;transform:translateY(-4px)}to{opacity:1;transform:translateY(0)}}
.lb-topups__actions{display:grid;grid-template-columns:52px 1fr;gap:10px;padding:18px 20px 20px}
.lb-topups__bookmark,.lb-topups__buy{height:50px;border:0;border-radius:12px;color:#fff;font-weight:950;cursor:pointer;font-size:15px}
.lb-topups__bookmark{background:rgba(255,255,255,.08)}
.lb-topups__bookmark:hover{background:rgba(255,255,255,.13)}
.lb-topups__buy{background:linear-gradient(135deg,#3672ff,#2f6cff)}
.lb-topups__buy:hover{filter:brightness(1.08)}

/* Seller instructions card */
.lb-topups__seller-card{margin-top:16px;background:#12161f;border:1px solid rgba(255,255,255,.09);border-radius:18px;overflow:hidden}
.lb-topups__seller-card h3{margin:0;padding:18px 20px;border-bottom:1px solid rgba(255,255,255,.07);font-size:17px;font-weight:900}
.lb-topups__seller-body{padding:18px 20px 6px}
.lb-topups__instructions-text{margin:0;color:#a3a7ba;line-height:1.65;font-size:14px;display:-webkit-box;-webkit-box-orient:vertical;-webkit-line-clamp:3;overflow:hidden}
.lb-topups__seller-body.is-expanded .lb-topups__instructions-text{-webkit-line-clamp:unset;overflow:visible}
.lb-topups__instructions-toggle{display:none;align-items:center;gap:6px;margin-top:8px;padding:0;background:none;border:0;color:#6ea1ff;font-weight:850;font-size:13px;cursor:pointer}
.lb-topups__instructions-toggle.is-visible{display:inline-flex}
.lb-topups__instructions-toggle i{font-size:11px;transition:transform .15s ease}
.lb-topups__seller-body.is-expanded .lb-topups__instructions-toggle i{transform:rotate(180deg)}
.lb-topups__seller-slot{margin:14px -20px -8px}
.lb-topups__seller-slot .lb-seller-footer{margin-top:0;border-radius:0;background:rgba(255,255,255,.025);padding:14px 20px}

/* Trust + Trustpilot strip */
.lb-topups__trust{display:grid;grid-template-columns:1fr 1fr;margin-top:16px;border:1px solid rgba(255,255,255,.09);border-radius:16px 16px 0 0;overflow:hidden;background:#12161f}
.lb-topups__trust div{padding:16px;display:flex;gap:11px;align-items:center;font-weight:900;font-size:13.5px}
.lb-topups__trust div+div{border-left:1px solid rgba(255,255,255,.07)}
.lb-topups__trust i{width:32px;height:32px;min-width:32px;border-radius:999px;display:grid;place-items:center;background:rgba(59,130,246,.18);color:#60a5fa}
.lb-topups__trustpilot{margin-top:-1px;display:flex;align-items:center;justify-content:center;gap:8px 10px;flex-wrap:wrap;padding:14px 16px 15px;border-radius:0 0 16px 16px;background:linear-gradient(180deg,rgba(0,182,122,.105),rgba(0,182,122,.075));border:1px solid rgba(0,182,122,.25);box-shadow:inset 0 1px 0 rgba(0,182,122,.12);font-size:13px;font-weight:900;color:#ecfff6;text-align:center;text-decoration:none!important;cursor:default}
.lb-topups__trustpilot:hover{color:#ecfff6;text-decoration:none!important}
.lb-topups__trustpilot strong{color:#fff;font-weight:950;white-space:nowrap;text-decoration:none!important}
.lb-topups__trustpilot-label{color:#fff;font-weight:950;white-space:nowrap;text-decoration:none!important}
.lb-topups__trustpilot .stars{display:inline-flex;align-items:center;gap:3px;text-decoration:none!important}
.lb-topups__trustpilot .stars span{width:18px;height:18px;border-radius:4px;background:#00b67a;color:#fff;display:grid;place-items:center;font-size:11px;line-height:1;font-weight:950;text-decoration:none!important}
.lb-topups__trustpilot-brand{flex:0 0 100%;display:flex;align-items:center;justify-content:center;gap:7px;color:#fff;font-weight:950;text-decoration:none!important}
.lb-topups__trustpilot-brand i{color:#00b67a;font-size:20px;filter:drop-shadow(0 0 10px rgba(0,182,122,.22))}
.lb-topups__trustpilot *{text-decoration:none!important}

/* Other sellers */
.lb-topups__other-wrap{grid-column:1 / -1;margin-top:14px}
.lb-topups__other-title{font-size:24px;font-weight:950;margin:0 0 14px}
.lb-topups__other-list{display:grid;gap:12px;background:transparent;border:0;border-radius:0;overflow:visible}
.lb-topups__other{position:relative;display:grid;grid-template-columns:minmax(240px,1.35fr) repeat(3,minmax(110px,.6fr)) minmax(120px,.55fr);align-items:center;gap:16px;padding:16px 18px;border:1px solid rgba(255,255,255,.09);border-radius:16px;background:#10141e;cursor:pointer;transition:background .15s ease,border-color .15s ease,transform .15s ease}
.lb-topups__other:hover{background:#121a27;border-color:rgba(59,130,246,.48);transform:translateY(-1px)}
.lb-topups__other:active{background:#142036}
.lb-topups__other-head{display:flex;align-items:center;gap:12px;min-width:0}
.lb-topups__other-avatar-wrap{position:relative;width:46px;height:46px;min-width:46px}
.lb-topups__other-avatar{width:46px;height:46px;border-radius:50%;object-fit:cover;background:#0a0f18;border:1px solid rgba(255,255,255,.10)}
.lb-topups__other-online{position:absolute;right:0;bottom:1px;width:13px;height:13px;border-radius:50%;background:#22c55e;border:2px solid #10141e}
.lb-topups__other-name-line{display:flex;align-items:center;gap:6px;min-width:0;font-weight:950;color:#fff;font-size:15px}
.lb-topups__other-name{min-width:0;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}
.lb-topups__other-verified{color:#3b82f6;font-size:14px;flex:0 0 auto}
.lb-topups__other-rating{display:flex;align-items:center;gap:8px;margin-top:4px;color:#9aa3b8;font-size:12.5px;white-space:nowrap}
.lb-topups__other-rating strong{display:inline-flex;align-items:center;gap:4px;color:#22c55e;font-weight:900}
.lb-topups__other-col-label{display:block;color:#9197ab;font-size:10.5px;font-weight:900;text-transform:uppercase;letter-spacing:.045em;margin-bottom:5px}
.lb-topups__other-unit{font-weight:950;font-size:14px;color:#fff}
.lb-topups__other-price{text-align:right}
.lb-topups__other-price strong{font-size:18px;font-weight:950;letter-spacing:-.02em}
.lb-topups__other-price small{color:#a3a7ba;font-size:12px;font-weight:800}
.lb-topups__other-arrow{display:none}

/* Trusted-by banner */
.lb-topups__trustedby{max-width:1500px;margin:0 auto;padding:10px 28px 100px;display:flex;align-items:center;justify-content:space-between;gap:24px;flex-wrap:wrap}
.lb-topups__trustedby h2{margin:0;font-size:30px;font-weight:950;letter-spacing:-.03em;max-width:480px}
.lb-topups__trustedby-card{display:flex;align-items:center;gap:14px;padding:16px 22px;border:1px solid rgba(255,255,255,.09);border-radius:16px;background:#12161f}
.lb-topups__trustedby-badge{width:46px;height:46px;min-width:46px;border-radius:50%;background:rgba(34,197,94,.14);color:#22c55e;display:grid;place-items:center;font-size:19px}
.lb-topups__trustedby-card strong{font-size:17px}
.lb-topups__trustedby-card .rating{color:#22c55e}
.lb-topups__trustedby-card small{display:block;color:#a3a7ba;font-size:12.5px;margin-top:2px}

/* ============================ RESPONSIVE ============================ */
@media(max-width:1300px){
  .lb-topups__offers{grid-template-columns:repeat(4,minmax(0,1fr))}
}
@media(max-width:1180px){
  .lb-topups{--tu-safe-top:170px}
  .lb-topups__layout{grid-template-columns:1fr}
  .lb-topups__side-sticky{position:static}
  .lb-topups__offers{grid-template-columns:repeat(3,minmax(0,1fr))}
}
@media(max-width:900px){
  .lb-topups{--tu-safe-top:150px;padding-bottom:30px}
  .lb-topups__wrap{padding:0 20px}
  .lb-topups__hero-inner{min-height:140px;padding:26px 0}
  .lb-topups__title{font-size:24px}
  .lb-topups__layout{padding:30px 0 88px}
  .lb-topups__other{grid-template-columns:1fr 1fr;gap:13px 16px;padding:16px;position:relative}
  .lb-topups__other-head{grid-column:1 / -1}
  .lb-topups__other-price{position:absolute;right:16px;top:17px;text-align:right}
  .lb-topups__other-price strong{font-size:18px}
  .lb-topups__other-price small{font-size:11px}
  .lb-topups__other-col-label{font-size:10px;margin-bottom:4px}
  .lb-topups__other-unit{font-size:13px}
  .lb-topups__trustedby{flex-direction:column;align-items:flex-start;padding-bottom:84px}
}

/* --- Phone layout: this is intentionally NOT just a squeezed desktop
   grid. Region AND Offers both become full-width, swipeable sliders
   (one slide ≈ the screen width, next one peeking on the edge) with a
   visible scroll-thumb underneath, instead of stacking everything. --- */
@media(max-width:760px){
  html,body{overflow-x:hidden}
  .topups-shop-page .site-main,
  .lb-topups{--tu-safe-top:104px;padding-bottom:92px;overflow:hidden}
  .lb-topups__wrap{width:100%;max-width:none;padding:0 16px}
  .lb-topups__hero{margin:0;background:transparent;border:0;border-radius:0;overflow:visible;box-shadow:none}
  .lb-topups__hero:before{opacity:.22;background:linear-gradient(180deg,rgba(8,11,18,.12),rgba(8,11,18,.72)),var(--tu-banner);background-size:cover;background-position:center}
  .lb-topups__hero-inner{padding:14px 16px 18px;min-height:0;align-items:center;gap:12px}
  .lb-topups__icon{width:38px;height:38px;min-width:38px;border-radius:12px;background:rgba(99,102,241,.12);border:1px solid rgba(124,108,255,.20);box-shadow:none}
  .lb-topups__icon img{width:26px;height:26px;border-radius:8px}
  .lb-topups__icon i{font-size:18px}
  .lb-topups__eyebrow{font-size:10px;letter-spacing:.08em;color:#8fa7ff;margin:0 0 4px}
  .lb-topups__title{font-size:19px;line-height:1.18;letter-spacing:-.02em;max-width:300px}
  .lb-topups__desc{font-size:13px;line-height:1.35;margin-top:6px;color:#a8adbb;max-width:315px}
  .lb-topups__layout{display:flex;flex-direction:column;padding:36px 16px 86px;gap:24px}
  .lb-topups__section-title{font-size:24px;line-height:1.1;margin:0 0 14px;letter-spacing:-.035em}

  .lb-topups__regions{display:grid;grid-auto-flow:column;grid-template-rows:repeat(2,52px);grid-auto-columns:minmax(154px,1fr);gap:10px;margin:0 -16px 30px;padding:0 16px 13px;overflow-x:auto;scroll-snap-type:x proximity;-webkit-overflow-scrolling:touch;scrollbar-width:thin;scrollbar-color:#3672ff rgba(255,255,255,.12)}
  .lb-topups__regions::-webkit-scrollbar{height:5px}
  .lb-topups__regions::-webkit-scrollbar-track{background:rgba(255,255,255,.12);border-radius:99px;margin:0 16px}
  .lb-topups__regions::-webkit-scrollbar-thumb{background:#3672ff;border-radius:99px}
  .lb-topups__pill{width:100%;height:52px;justify-content:flex-start;padding:9px 11px;border-radius:18px;background:#0d1119;border-color:rgba(255,255,255,.1);font-size:13.5px;font-weight:900;gap:9px;scroll-snap-align:start;white-space:nowrap;overflow:hidden}
  .lb-topups__pill span:nth-child(2){overflow:hidden;text-overflow:ellipsis}
  .lb-topups__pill-icon{width:31px;height:31px;min-width:31px;font-size:12px;background:#070a10;border-color:rgba(255,255,255,.1)}
  .lb-topups__check{width:27px;height:27px;min-width:27px;margin-left:auto;font-size:11px;border-radius:50%;background:#111722;color:transparent}
  .lb-topups__pill.is-active{background:linear-gradient(135deg,#122f5a,#143f78);border-color:#2f7dff;box-shadow:inset 0 0 0 1px rgba(47,125,255,.28)}
  .lb-topups__pill.is-active .lb-topups__check{color:#fff;background:#3672ff}

  .lb-topups__offers{display:flex;flex-wrap:nowrap;gap:14px;margin:0 -16px 18px;padding:0 16px 14px;overflow-x:auto;scroll-snap-type:x proximity;-webkit-overflow-scrolling:touch;scrollbar-width:none}
  .lb-topups__offers::-webkit-scrollbar{display:none}
  .lb-topups__offer{flex:0 0 145px;grid-template-rows:92px auto;min-height:168px;border-radius:14px;scroll-snap-align:start;background:#101620;border-color:rgba(255,255,255,.09)}
  .lb-topups__offer.is-active{background:linear-gradient(180deg,#183d72 0%,#143461 52%,#101620 53%);box-shadow:inset 0 0 0 1px #2f7dff}
  .lb-topups__offer-badge{display:none;font-size:10px;padding:4px 8px;top:8px;left:8px;border-radius:8px;background:#3672ff;color:#fff;box-shadow:none}
  .lb-topups__offer.is-popular .lb-topups__offer-badge{display:inline-flex}
  .lb-topups__offer-check{width:25px;height:25px;top:8px;right:8px;border-radius:9px;background:rgba(8,11,18,.65)}
  .lb-topups__offer-media{padding:24px 8px 6px;background:radial-gradient(circle at 50% 58%,rgba(250,204,21,.15),transparent 58%)}
  .lb-topups__offer-img{height:58px}
  .lb-topups__offer-img img{max-width:82px;max-height:56px}
  .lb-topups__offer-img i{font-size:30px}
  .lb-topups__offer-info{padding:11px 11px 12px;gap:4px;background:rgba(5,8,13,.36)}
  .lb-topups__offer-title{font-size:14px;line-height:1.15}
  .lb-topups__offer-price{font-size:13px;color:#d5dbea}

  .lb-topups__side{margin:0}
  .lb-topups__side-sticky{position:static}
  .lb-topups__checkout,.lb-topups__seller-card,.lb-topups__trust,.lb-topups__trustpilot{border-radius:16px;background:#11161f;border-color:rgba(255,255,255,.1)}
  .lb-topups__checkout{box-shadow:none;overflow:visible}
  .lb-topups__row{padding:15px 16px;gap:12px}
  .lb-topups__label{font-size:10px;letter-spacing:.05em;color:#a4aabc}
  .lb-topups__price-line{gap:6px;margin-top:5px}
  .lb-topups__price{font-size:31px;letter-spacing:-.04em}
  .lb-topups__currency{font-size:12px;color:#a4aabc}
  .lb-topups__card-img{width:58px;height:58px}
  .lb-topups__qty{grid-template-columns:42px 54px 42px;border-radius:10px}
  .lb-topups__qty button{height:36px}
  .lb-topups__field{grid-template-columns:1fr;padding:10px 16px;gap:8px}
  .lb-topups__field input{height:44px;border-radius:10px;font-size:13px;background:#0a0e15}
  .lb-topups__actions{grid-template-columns:52px 1fr;padding:14px 16px 16px}
  .lb-topups__bookmark{display:block;height:45px;border-radius:9px;background:rgba(255,255,255,.1)}
  .lb-topups__buy{height:45px;border-radius:9px;font-size:14px;background:#2f6cff}

  .lb-topups__seller-card{margin-top:18px;overflow:hidden}
  .lb-topups__seller-card h3{padding:17px 16px;font-size:17px;border-bottom:0}
  .lb-topups__seller-body{padding:0 16px 4px}
  .lb-topups__instructions-text{font-size:13px;line-height:1.55;color:#aeb4c2;-webkit-line-clamp:4}
  .lb-topups__instructions-toggle{margin:8px auto 4px;color:#fff;font-size:13px;text-shadow:0 2px 10px rgba(0,0,0,.8)}
  .lb-topups__instructions-toggle.is-visible{display:flex}
  .lb-topups__seller-slot{margin:12px -16px -4px}
  .lb-topups__seller-slot .lb-seller-footer{padding:13px 16px;background:#121821;border-top:1px solid rgba(255,255,255,.07)}

  .lb-topups__trust{grid-template-columns:1fr;margin-top:18px}
  .lb-topups__trust div{padding:14px 16px;font-size:14px}
  .lb-topups__trust div+div{border-left:0;border-top:1px solid rgba(255,255,255,.07)}
  .lb-topups__trust i{width:34px;height:34px;min-width:34px}
  .lb-topups__trustpilot{margin-top:-1px;border-radius:0 0 16px 16px;border-top:1px solid rgba(0,182,122,.25);font-size:12px;padding:14px 12px 15px;gap:7px 9px;background:linear-gradient(180deg,rgba(0,182,122,.11),rgba(0,182,122,.075))}
  .lb-topups__trustpilot .stars span{width:17px;height:17px;font-size:10px}
  .lb-topups__trustpilot-brand{margin-top:1px}

  .lb-topups__other-wrap{margin-top:30px}
  .lb-topups__other-title{font-size:22px;margin-bottom:13px;letter-spacing:-.03em}
  .lb-topups__other-list{border-radius:0;border-left:0;border-right:0;margin:0 -16px;background:#10151d}
  .lb-topups__other{display:grid;grid-template-columns:1fr auto;gap:14px 10px;padding:16px;border-radius:0;border-bottom:1px solid rgba(255,255,255,.08);position:relative}
  .lb-topups__other-seller{grid-column:1 / 2;min-width:0}
  .lb-topups__other-seller .lb-seller-footer{padding:0;background:transparent;border:0;min-height:44px}
  .lb-topups__other-seller .lb-seller-footer__avatar,.lb-topups__other-seller .lb-seller-footer__avatar-placeholder{width:44px;height:44px;min-width:44px}
  .lb-topups__other-seller .lb-seller-footer__name-text{font-size:15px;max-width:145px}
  .lb-topups__other-seller .lb-seller-footer__stat{height:20px;min-height:20px;font-size:10px;padding:0 6px}
  .lb-topups__other-price{grid-column:2 / 3;grid-row:1;align-self:center;text-align:right;white-space:nowrap}
  .lb-topups__other-price strong{font-size:19px;line-height:1;color:#fff;white-space:nowrap}
  .lb-topups__other-meta:not(.lb-topups__other-price){display:block;min-width:0}
  .lb-topups__other-col-label{display:block;font-size:10px;margin-bottom:6px;color:#8f96a7}
  .lb-topups__other-unit{font-size:13px;font-weight:900;color:#eef2ff}
  .lb-topups__other-arrow{display:none}

  .lb-topups__trustedby{margin:0;padding:34px 16px 92px;align-items:center;text-align:center}
  .lb-topups__trustedby h2{font-size:35px;line-height:1.12;letter-spacing:-.05em;max-width:330px;margin:0 auto}
  .lb-topups__trustedby-card{width:100%;justify-content:flex-start;text-align:left;padding:16px;border-radius:12px;background:#11161f}
}
@media(max-width:480px){
  .lb-topups{--tu-safe-top:96px}
  .lb-topups__wrap{padding:0 14px}
  .lb-topups__hero{margin:0}
  .lb-topups__layout{padding-left:14px;padding-right:14px}
  .lb-topups__regions{margin-left:-14px;margin-right:-14px;padding-left:14px;padding-right:14px;grid-auto-columns:156px}
  .lb-topups__offers{margin-left:-14px;margin-right:-14px;padding-left:14px;padding-right:14px}
  .lb-topups__offer{flex-basis:140px}
  .lb-topups__other-list{margin-left:-14px;margin-right:-14px}
  .lb-topups__trustedby{padding-left:14px;padding-right:14px}
  .lb-topups__trustedby h2{font-size:32px}
}

/* Cleaner GameBoost-like phone flow */
@media(max-width:760px){
  .lb-topups__layout{display:flex;flex-direction:column}
  .lb-topups__side{order:2}
  .lb-topups__other-wrap{order:3;margin-top:4px}
  .lb-topups__regions{display:grid;grid-auto-flow:column;grid-auto-columns:minmax(148px,48%);grid-template-rows:repeat(2,46px);align-items:stretch}
  .lb-topups__pill{height:46px;min-width:0;width:auto;flex:none;border-radius:16px;background:rgba(12,16,24,.58)}
  .lb-topups__offers{padding-bottom:10px}
  .lb-topups__offer{border-radius:12px;background:#10141d}
  .lb-topups__checkout,.lb-topups__seller-card,.lb-topups__trust,.lb-topups__trustpilot,.lb-topups__other-list{border-radius:14px;background:#11151e}
  .lb-topups__checkout{overflow:hidden}
  .lb-topups__row{min-height:56px}
  .lb-topups__seller-card{margin-top:12px}
  .lb-topups__other-title{margin-top:10px}
}


/* Final mobile cleanup: account-shop-like spacing and readable other sellers */
@media(max-width:760px){
  .lb-topups__other-list{margin:0!important;background:transparent!important;border-radius:0!important;display:grid!important;gap:12px!important}
  .lb-topups__other{grid-template-columns:1fr 1fr!important;border:1px solid rgba(255,255,255,.09)!important;border-radius:16px!important;background:#111822!important;padding:16px!important;gap:14px 16px!important;box-shadow:none!important}
  .lb-topups__other-head{grid-column:1 / -1!important;min-width:0!important}
  .lb-topups__other-seller-meta{min-width:0!important;display:block!important}
  .lb-topups__other-price{position:absolute!important;right:16px!important;top:18px!important;grid-column:auto!important;text-align:right!important;white-space:nowrap!important}
  .lb-topups__other-price strong{display:inline!important;font-size:19px!important;line-height:1!important;color:#fff!important;white-space:nowrap!important}
  .lb-topups__other-price small{font-size:11px!important;color:#a3a7ba!important;margin-left:2px!important}
  .lb-topups__other-name-line{padding-right:104px!important;max-width:100%!important}
  .lb-topups__other-rating{max-width:100%!important;overflow:hidden!important;text-overflow:ellipsis!important}
  .lb-topups__other-meta:not(.lb-topups__other-price){display:block!important;min-width:0!important}
  .lb-topups__other-meta:nth-child(4){grid-column:1 / -1!important}
  .lb-topups__other-col-label{display:block!important;font-size:10px!important;margin-bottom:5px!important;color:#8f96a7!important}
  .lb-topups__other-unit{font-size:13px!important;font-weight:900!important;color:#eef2ff!important}
  .lb-topups__other-arrow,.lb-topups__other-seller{display:none!important}
}

/* Final hero text visibility fix: never clip title/description on mobile */
@media(max-width:760px){
  .lb-topups__hero{overflow:visible!important;background:transparent!important;border:0!important}
  .lb-topups__hero:before{display:none!important}
  .lb-topups__hero-inner{
    width:100%!important;
    max-width:100%!important;
    min-width:0!important;
    display:grid!important;
    grid-template-columns:42px minmax(0,1fr)!important;
    align-items:flex-start!important;
    gap:10px!important;
    padding:10px 16px 12px!important;
    margin:0!important;
    min-height:0!important;
    overflow:visible!important;
  }
  .lb-topups__hero-inner > div:last-child{
    min-width:0!important;
    width:100%!important;
    max-width:100%!important;
    overflow:visible!important;
  }
  .lb-topups__icon{
    width:40px!important;
    height:40px!important;
    min-width:40px!important;
    border-radius:12px!important;
    margin-top:2px!important;
  }
  .lb-topups__icon img{width:25px!important;height:25px!important}
  .lb-topups__icon i{font-size:19px!important}
  .lb-topups__eyebrow{
    display:block!important;
    margin:0 0 4px!important;
    font-size:10px!important;
    line-height:1.15!important;
    white-space:normal!important;
    overflow:visible!important;
  }
  .lb-topups__title{
    display:block!important;
    width:100%!important;
    max-width:none!important;
    margin:0!important;
    font-size:18px!important;
    line-height:1.22!important;
    white-space:normal!important;
    overflow:visible!important;
    text-overflow:clip!important;
    overflow-wrap:break-word!important;
    word-break:normal!important;
  }
  .lb-topups__desc{
    display:block!important;
    width:100%!important;
    max-width:none!important;
    margin:5px 0 0!important;
    font-size:12.5px!important;
    line-height:1.35!important;
    white-space:normal!important;
    overflow:visible!important;
    text-overflow:clip!important;
  }
}
@media(max-width:380px){
  .lb-topups__hero-inner{grid-template-columns:38px minmax(0,1fr)!important;padding-left:14px!important;padding-right:14px!important}
  .lb-topups__icon{width:36px!important;height:36px!important;min-width:36px!important}
  .lb-topups__title{font-size:17px!important}
  .lb-topups__desc{font-size:12px!important}
}


/* Mobile final fix: hero spacing + compact table style for other sellers */
@media(max-width:760px){
  .lb-topups__hero{
    margin-bottom:22px!important;
    overflow:visible!important;
  }
  .lb-topups__hero-inner{
    padding:14px 16px 24px!important;
  }
  .lb-topups__layout{
    padding-top:0!important;
    gap:26px!important;
  }
  .lb-topups__section-title{
    margin-top:0!important;
    margin-bottom:16px!important;
  }
  .lb-topups__regions{
    margin-bottom:34px!important;
  }

  .lb-topups__other-wrap{
    margin-top:14px!important;
  }
  .lb-topups__other-title{
    font-size:22px!important;
    line-height:1.15!important;
    margin:0 0 12px!important;
  }
  .lb-topups__other-list{
    display:grid!important;
    gap:10px!important;
    margin:0!important;
    background:transparent!important;
    border:0!important;
  }
  .lb-topups__other{
    display:grid!important;
    grid-template-columns:1fr auto!important;
    grid-template-areas:
      "seller price"
      "stock stock"
      "min delivery"!important;
    align-items:center!important;
    gap:12px 14px!important;
    padding:14px!important;
    border-radius:16px!important;
    border:1px solid rgba(255,255,255,.09)!important;
    background:#111822!important;
    overflow:hidden!important;
  }
  .lb-topups__other-head{
    grid-area:seller!important;
    grid-column:auto!important;
    min-width:0!important;
    padding-right:0!important;
  }
  .lb-topups__other-avatar-wrap,
  .lb-topups__other-avatar{
    width:42px!important;
    height:42px!important;
    min-width:42px!important;
  }
  .lb-topups__other-name-line{
    padding-right:0!important;
    max-width:100%!important;
  }
  .lb-topups__other-name{
    max-width:128px!important;
  }
  .lb-topups__other-rating{
    font-size:11.5px!important;
    margin-top:3px!important;
  }
  .lb-topups__other-price{
    grid-area:price!important;
    position:static!important;
    align-self:start!important;
    text-align:right!important;
    white-space:nowrap!important;
  }
  .lb-topups__other-price .lb-topups__other-col-label{
    text-align:right!important;
  }
  .lb-topups__other-price strong{
    display:inline-block!important;
    font-size:18px!important;
    line-height:1.05!important;
  }
  .lb-topups__other-price small{
    display:inline-block!important;
    font-size:11px!important;
    margin-left:2px!important;
  }
  .lb-topups__other-meta:not(.lb-topups__other-price){
    display:block!important;
    min-width:0!important;
    background:rgba(255,255,255,.025)!important;
    border:1px solid rgba(255,255,255,.055)!important;
    border-radius:12px!important;
    padding:10px!important;
  }
  .lb-topups__other-meta:nth-child(2){
    grid-area:stock!important;
  }
  .lb-topups__other-meta:nth-child(3){
    grid-area:min!important;
  }
  .lb-topups__other-meta:nth-child(4){
    grid-area:delivery!important;
    grid-column:auto!important;
  }
  .lb-topups__other-col-label{
    display:block!important;
    font-size:9.5px!important;
    line-height:1.1!important;
    margin:0 0 6px!important;
    color:#8d96aa!important;
    letter-spacing:.045em!important;
  }
  .lb-topups__other-unit{
    display:block!important;
    font-size:12.5px!important;
    line-height:1.2!important;
    font-weight:950!important;
    color:#fff!important;
  }
}
@media(max-width:380px){
  .lb-topups__other{
    grid-template-columns:1fr!important;
    grid-template-areas:
      "seller"
      "price"
      "stock"
      "min"
      "delivery"!important;
  }
  .lb-topups__other-price,
  .lb-topups__other-price .lb-topups__other-col-label{
    text-align:left!important;
  }
  .lb-topups__other-name{
    max-width:190px!important;
  }
}



/* Ricardo mobile polish: no clipped offer border, more Region spacing, GameBoost-like other sellers row */
@media(max-width:760px){
  .lb-topups__layout > div > .lb-topups__section-title:first-child{
    padding-top:18px!important;
    margin-bottom:16px!important;
  }
  .lb-topups__offers{
    padding-top:3px!important;
  }
  .lb-topups__offer{
    overflow:hidden!important;
  }
  .lb-topups__offer.is-active{
    box-shadow:inset 0 0 0 1px #2f7dff!important;
  }

  .lb-topups__other-wrap{
    margin-top:22px!important;
  }
  .lb-topups__other-title{
    font-size:22px!important;
    margin:0 0 14px!important;
  }
  .lb-topups__other-list{
    display:grid!important;
    gap:0!important;
    margin:0 -16px!important;
    background:#10141c!important;
    border-top:1px solid rgba(255,255,255,.07)!important;
    border-bottom:1px solid rgba(255,255,255,.07)!important;
    border-radius:0!important;
    overflow:hidden!important;
  }
  .lb-topups__other{
    display:grid!important;
    grid-template-columns:minmax(0,1fr) minmax(78px,.72fr) minmax(92px,.88fr)!important;
    grid-template-areas:
      "seller seller price"
      "stock min delivery"!important;
    align-items:center!important;
    column-gap:12px!important;
    row-gap:17px!important;
    padding:16px!important;
    border:0!important;
    border-radius:0!important;
    border-bottom:1px solid rgba(255,255,255,.075)!important;
    background:#10141c!important;
    box-shadow:none!important;
    overflow:visible!important;
  }
  .lb-topups__other:last-child{
    border-bottom:0!important;
  }
  .lb-topups__other:hover{
    transform:none!important;
    background:#121824!important;
  }
  .lb-topups__other-head{
    grid-area:seller!important;
    grid-column:auto!important;
    min-width:0!important;
    gap:10px!important;
    padding:0!important;
  }
  .lb-topups__other-avatar-wrap,
  .lb-topups__other-avatar{
    width:43px!important;
    height:43px!important;
    min-width:43px!important;
  }
  .lb-topups__other-online{
    width:13px!important;
    height:13px!important;
    right:0!important;
    bottom:0!important;
    border-color:#10141c!important;
  }
  .lb-topups__other-name-line{
    padding-right:0!important;
    max-width:100%!important;
    font-size:15px!important;
    line-height:1.1!important;
  }
  .lb-topups__other-name{
    max-width:135px!important;
  }
  .lb-topups__other-rating{
    display:flex!important;
    align-items:center!important;
    gap:8px!important;
    max-width:190px!important;
    margin-top:5px!important;
    font-size:11.5px!important;
    line-height:1.2!important;
    color:#8f96a6!important;
    overflow:hidden!important;
    white-space:nowrap!important;
    text-overflow:ellipsis!important;
  }
  .lb-topups__other-rating strong{
    color:#20d967!important;
    font-size:11.5px!important;
  }
  .lb-topups__other-rating span{
    overflow:hidden!important;
    text-overflow:ellipsis!important;
  }
  .lb-topups__other-price{
    grid-area:price!important;
    position:static!important;
    align-self:start!important;
    justify-self:end!important;
    text-align:right!important;
    white-space:nowrap!important;
    background:transparent!important;
    border:0!important;
    border-radius:0!important;
    padding:0!important;
  }
  .lb-topups__other-price .lb-topups__other-col-label{
    display:none!important;
  }
  .lb-topups__other-price strong{
    display:inline!important;
    font-size:19px!important;
    line-height:1!important;
    letter-spacing:-.03em!important;
    color:#fff!important;
  }
  .lb-topups__other-price small{
    display:inline!important;
    margin-left:2px!important;
    font-size:11px!important;
    color:#a4aaba!important;
  }
  .lb-topups__other-meta:not(.lb-topups__other-price){
    display:block!important;
    min-width:0!important;
    background:transparent!important;
    border:0!important;
    border-radius:0!important;
    padding:0!important;
  }
  .lb-topups__other-meta:nth-child(2){grid-area:stock!important}
  .lb-topups__other-meta:nth-child(3){grid-area:min!important}
  .lb-topups__other-meta:nth-child(4){grid-area:delivery!important;grid-column:auto!important}
  .lb-topups__other-col-label{
    display:block!important;
    margin:0 0 7px!important;
    font-size:11.5px!important;
    line-height:1.1!important;
    letter-spacing:0!important;
    text-transform:none!important;
    font-weight:700!important;
    color:#9aa1ae!important;
  }
  .lb-topups__other-unit{
    display:block!important;
    font-size:13.5px!important;
    line-height:1.2!important;
    font-weight:900!important;
    color:#f3f6ff!important;
  }
  .lb-topups__other-arrow,
  .lb-topups__other-seller{
    display:none!important;
  }
}
@media(max-width:380px){
  .lb-topups__other-list{
    margin-left:-14px!important;
    margin-right:-14px!important;
  }
  .lb-topups__other{
    grid-template-columns:minmax(0,1fr) minmax(72px,.72fr) minmax(86px,.88fr)!important;
    column-gap:9px!important;
    padding:15px 14px!important;
  }
  .lb-topups__other-name{max-width:118px!important}
  .lb-topups__other-rating{max-width:165px!important;font-size:11px!important}
  .lb-topups__other-price strong{font-size:18px!important}
  .lb-topups__other-col-label{font-size:11px!important}
  .lb-topups__other-unit{font-size:13px!important}
}


/* Background sync with dynamic item shop: no right-side blue desktop glow */
body.topups-shop-page,
.topups-shop-page .site-main,
.topups-shop-page main,
.topups-shop-page .lb-topups{
}
.topups-shop-page .lb-topups{
  background-image:none!important;
}


/* Desktop polish: sticky right cards and narrower Other Sellers like Items shop */
@media(min-width:1181px){
  .lb-topups__layout{
    align-items:start!important;
    grid-template-columns:minmax(0,1fr) 420px!important;
    gap:32px!important;
  }
  .lb-topups__side{
    grid-column:2!important;
    grid-row:1 / span 2!important;
    align-self:start!important;
    min-width:0!important;
  }
  .lb-topups__side-sticky{
    position:sticky!important;
    top:96px!important;
    align-self:start!important;
    z-index:5!important;
  }
  .lb-topups__other-wrap{
    grid-column:1 / 2!important;
    grid-row:2!important;
    max-width:100%!important;
    margin-top:44px!important;
  }
  .lb-topups__other-list{
    max-width:100%!important;
  }
  .lb-topups__other{
    grid-template-columns:minmax(220px,1.2fr) minmax(120px,.58fr) minmax(120px,.58fr) minmax(150px,.7fr) minmax(125px,.55fr)!important;
    padding:16px 18px!important;
  }
}

@media(min-width:1181px) and (max-width:1450px){
  .lb-topups__layout{
    grid-template-columns:minmax(0,1fr) 380px!important;
    gap:28px!important;
  }
  .lb-topups__other{
    grid-template-columns:minmax(200px,1.15fr) minmax(105px,.55fr) minmax(105px,.55fr) minmax(130px,.65fr) minmax(115px,.52fr)!important;
  }
}

</style>

<div class="lb-topups <?= !$offerCards ? 'lb-topups--empty' : '' ?>" style="--tu-banner:url('<?= htmlspecialchars($gameBanner ?: '/public/assets/website/images/banner/lol-banner.webp', ENT_QUOTES) ?>')" data-currency="<?= htmlspecialchars($topupCurrencyCode, ENT_QUOTES) ?>" data-currency-rate="<?= htmlspecialchars((string)$topupCurrencyRate, ENT_QUOTES) ?>" data-currency-symbol="<?= htmlspecialchars($topupCurrencySymbol, ENT_QUOTES) ?>" data-topups='<?= htmlspecialchars(json_encode($topups, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES), ENT_QUOTES) ?>'>
    <?php if (function_exists('lb_seller_footer_css')) echo lb_seller_footer_css(); ?>

    <?php if ($offerCards): ?>
    <section class="lb-topups__hero">
        <div class="lb-topups__wrap lb-topups__hero-inner">
            <div class="lb-topups__icon">
                <i class="fa-solid fa-coins"></i>
            </div>
            <div>
                <div class="lb-topups__eyebrow"><?= htmlspecialchars($serviceLabel) ?></div>
                <h1 class="lb-topups__title"><?= htmlspecialchars($pageTitle) ?></h1>
                <p class="lb-topups__desc"><?= htmlspecialchars($pageDesc) ?></p>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <?php if (!$offerCards): ?>

        <div class="lb-shop-empty-notify-offset">
            <?= $this->insert('website/pages/components/coming-soon-notify', [
                'game' => $gameSlug,
                'gameConfig' => ['name' => $gameName],
                'gameIcon' => $gameIcon,
                'service' => 'top-ups',
                'title' => 'Coming soon',
                'text' => 'There are no top-up offers for this game yet. Leave your email and we will notify you as soon as top-ups are available.'
            ]) ?>
        </div>

<script>
(function(){
  if(window.lbShopEmptyNotifySyncLoaded) return;
  window.lbShopEmptyNotifySyncLoaded = true;

  function getGameNavBottom(){
    var selectors = ['.lb-game-subnav', '.game-subnav', '[class*="game-subnav"]'];
    var best = 0;
    selectors.forEach(function(selector){
      Array.prototype.forEach.call(document.querySelectorAll(selector), function(el){
        var cs = window.getComputedStyle(el);
        if(cs.display === 'none' || cs.visibility === 'hidden') return;
        var r = el.getBoundingClientRect();
        if(r.width < 120 || r.height < 20) return;
        if(r.top > window.innerHeight || r.bottom < 0) return;
        best = Math.max(best, r.bottom);
      });
    });
    return best;
  }

  function syncEmptyNotify(){
    var blocks = document.querySelectorAll('.lb-shop-empty-notify-offset');
    if(!blocks.length) return;

    window.requestAnimationFrame(function(){
      var navBottom = getGameNavBottom();
      if(!navBottom) return;

      var isMobile = window.matchMedia('(max-width:760px)').matches;
      var gap = isMobile ? 76 : 82;
      var desiredTop = Math.round(navBottom + gap);
      var viewportBottom = window.innerHeight || document.documentElement.clientHeight || 0;

      blocks.forEach(function(block){
        block.style.setProperty('--lb-empty-extra-top', '0px');

        var target = block.firstElementChild || block;
        var currentTop = Math.round(target.getBoundingClientRect().top);
        var extra = Math.max(0, desiredTop - currentTop);

        block.style.setProperty('--lb-empty-extra-top', extra + 'px');

        var afterTop = Math.round((target.getBoundingClientRect().top || currentTop) + extra);
        var bottomGap = Math.max(isMobile ? 72 : 96, Math.round(viewportBottom - afterTop - (isMobile ? 430 : 520)));
        block.style.setProperty('--lb-empty-bottom-gap', bottomGap + 'px');
        block.style.setProperty('--lb-empty-page-chrome', Math.max(260, Math.round(navBottom + bottomGap)) + 'px');
      });
    });
  }

  syncEmptyNotify();
  window.addEventListener('load', syncEmptyNotify, {once:true});
  window.addEventListener('resize', syncEmptyNotify, {passive:true});
  window.addEventListener('orientationchange', syncEmptyNotify, {passive:true});
  setTimeout(syncEmptyNotify, 120);
  setTimeout(syncEmptyNotify, 450);
  setTimeout(syncEmptyNotify, 900);
  if('ResizeObserver' in window){
    var ro = new ResizeObserver(syncEmptyNotify);
    ['.lb-game-subnav','.game-subnav','[class*="game-subnav"]','.lb-sale-banner','.wm-tip-banner','header'].forEach(function(selector){
      Array.prototype.forEach.call(document.querySelectorAll(selector), function(el){ ro.observe(el); });
    });
  }
})();
</script>


<style id="lb-empty-bg-cleanup-final">
/* Remove empty state grid and glow backgrounds on marketplace coming soon pages. */
.lb-shop-empty-notify-offset,
.lb-shop-empty-notify-offset::before,
.lb-shop-empty-notify-offset::after,
.lb-cs2,
.lb-cs2::before,
.lb-cs2::after,
.lb-cs2__grid,
.lb-cs2__grid::before,
.lb-cs2__grid::after,
.lb-cs2__aurora,
.lb-cs2__aurora::before,
.lb-cs2__aurora::after,
.lb-topups--empty,
.lb-topups--empty::before,
.lb-topups--empty::after{
  background-image:none !important;
  -webkit-mask-image:none !important;
  mask-image:none !important;
}
.lb-cs2__grid,
.lb-cs2__aurora{
  display:none !important;
}
</style>
    <?php else: ?>

    <main class="lb-topups__wrap lb-topups__layout">
        <div>
            <h2 class="lb-topups__section-title"><?= htmlspecialchars($regionLabel) ?></h2>
            <div class="lb-topups__regions" id="tuRegions">
                <?php foreach ($regionSources as $regionKey => $regionText): $isActive = ((string)$regionKey === (string)$selectedRegion); ?>
                    <button class="lb-topups__pill <?= $isActive ? 'is-active' : '' ?>" type="button" data-region="<?= htmlspecialchars((string)$regionKey) ?>"><span class="lb-topups__pill-icon"><i class="fa-solid <?= htmlspecialchars(lb_tu_region_icon($regionKey)) ?>"></i></span><span><?= htmlspecialchars((string)$regionText) ?></span><span class="lb-topups__check"><?= $isActive ? '✓' : '' ?></span></button>
                <?php endforeach; ?>
            </div>

            <h2 class="lb-topups__section-title"><?= htmlspecialchars($amountLabel) ?></h2>
                <div class="lb-topups__offers" id="tuOffers">
                    <?php $tuFirstActiveDone = false; foreach ($offerCards as $i => $o):
                        $isActiveCard = ((string)($o['region'] ?? 'global') === (string)$selectedRegion && !$tuFirstActiveDone);
                        if ($isActiveCard) $tuFirstActiveDone = true;
                        $cashback = isset($o['cashback_percent']) && $o['cashback_percent'] !== '' ? (string)$o['cashback_percent'] : '';
                    ?>
                        <button class="lb-topups__offer <?= $isActiveCard ? 'is-active' : '' ?> <?= $i === 0 ? 'is-popular' : '' ?>" type="button" data-key="<?= htmlspecialchars((string)$o['offer_key']) ?>" data-region="<?= htmlspecialchars((string)($o['region'] ?: 'Global')) ?>" data-cashback="<?= htmlspecialchars($cashback) ?>">
                            <span class="lb-topups__offer-badge"><i class="fa-solid fa-fire"></i> Popular</span>
                            <span class="lb-topups__offer-check"><i class="fa-solid fa-check"></i></span>
                            <span class="lb-topups__offer-media"><span class="lb-topups__offer-img"><?php if (lb_topup_img($o)): ?><img src="<?= htmlspecialchars(lb_topup_img($o)) ?>" alt=""><?php else: ?><i class="fa-solid fa-coins"></i><?php endif; ?></span></span>
                            <span class="lb-topups__offer-info"><strong class="lb-topups__offer-title"><?= htmlspecialchars((string)$o['offer_title']) ?></strong><span class="lb-topups__offer-price"><?= lb_topup_money($o['price']) ?></span></span>
                        </button>
                    <?php endforeach; ?>
                </div>

        </div>

        <aside class="lb-topups__side">
            <div class="lb-topups__side-sticky">
                <div class="lb-topups__checkout">
                    <div class="lb-topups__row">
                        <div>
                            <div class="lb-topups__label">Offer Price</div>
                            <div class="lb-topups__price-line"><span class="lb-topups__price" id="tuPrice"><?= lb_topup_money(0, $topupCurrencyCode) ?></span><span class="lb-topups__currency" id="tuCurrency"><?= htmlspecialchars($topupCurrencyCode) ?></span></div>
                            <div class="lb-topups__cashback-badge" id="tuCashback"><i class="fa-solid fa-coins"></i> <span id="tuCashbackValue">0</span>% Cashback</div>
                        </div>
                        <img class="lb-topups__card-img" id="tuImg" src="" alt="" style="display:none">
                    </div>
                    <div class="lb-topups__row"><span class="lb-topups__muted">Delivery time</span><strong><i class="fa-solid fa-clock"></i> <span id="tuTime">0 min</span></strong></div>
                    <div class="lb-topups__row"><span class="lb-topups__muted">Quantity</span><div class="lb-topups__qty"><button type="button" id="tuMinus">−</button><strong id="tuQty">1</strong><button type="button" id="tuPlus">+</button></div></div>
                    <form action="<?= defined('AJAX_URL') ? AJAX_URL : '/ajax' ?>" class="ajax-form" id="tuBuyForm" method="post">
                        <input type="hidden" name="action" value="prepare_topup_order">
                        <input type="hidden" name="topup_id" id="tuTopupId" value="">
                        <input type="hidden" name="quantity" id="tuQtyHidden" value="1">
                        <input type="hidden" name="region" id="tuRegionHidden" value="<?= htmlspecialchars((string)$selectedRegion) ?>">
                        <input type="hidden" name="server" id="tuServerHidden" value="<?= htmlspecialchars((string)$selectedRegion) ?>">
                        <?php foreach ($checkoutFields as $f):
                            $k = preg_replace('/[^a-z0-9_]/i', '', (string)($f['key'] ?? ''));
                            if (!$k) continue;
                            if (in_array(strtolower($k), ['server','region','topup_region','region_server'], true)) continue;
                        ?>
                            <?php
                            $tuFieldLabel = (string)($f['label'] ?? $k);
                            $tuFieldPlaceholder = (string)($f['placeholder'] ?? ('Enter ' . $tuFieldLabel));
                            if ($isLolTopupShop && (strtolower((string)$k) === 'summoner_name' || strtolower(trim($tuFieldLabel)) === 'summoner name')) {
                                $tuFieldLabel = 'Riot ID';
                                if ($tuFieldPlaceholder === '' || stripos($tuFieldPlaceholder, 'summoner') !== false) $tuFieldPlaceholder = 'Enter Riot ID';
                            }
                            ?>
                            <label class="lb-topups__field"><span class="lb-topups__muted"><?= htmlspecialchars($tuFieldLabel) ?></span><input type="text" name="<?= htmlspecialchars($k) ?>" placeholder="<?= htmlspecialchars($tuFieldPlaceholder) ?>" <?= !empty($f['required']) ? 'required' : '' ?>></label>
                        <?php endforeach; ?>
                        <div class="lb-topups__actions"><button class="lb-topups__bookmark" type="button" aria-label="Bookmark"><i class="fa-regular fa-bookmark"></i></button><button class="lb-topups__buy" type="submit">Buy now <i class="fa-solid fa-arrow-right ms-2"></i></button></div>
                    </form>
                </div>

                <section class="lb-topups__seller-card">
                    <h3>Seller Instructions</h3>
                    <div class="lb-topups__seller-body" id="tuSellerBody">
                        <p id="tuInstructions" class="lb-topups__instructions-text"></p>
                        <button type="button" id="tuInstructionsToggle" class="lb-topups__instructions-toggle"><span>View full instructions</span><i class="fa-solid fa-chevron-down"></i></button>
                        <div id="tuSeller" class="lb-topups__seller-slot"></div>
                    </div>
                </section>

                <div class="lb-topups__trust"><div><i class="fa-solid fa-bolt"></i> Fast Delivery</div><div><i class="fa-solid fa-headset"></i> 24/7 Support</div></div>

                <?php if ($tuShowTrustpilot): ?>
                <div class="lb-topups__trustpilot" role="img" aria-label="Trustpilot Excellent, <?= htmlspecialchars(number_format($tuTrustpilotRating, 1)) ?> out of 5, 1000+ reviews">
                    <span class="lb-topups__trustpilot-label">Excellent</span>
                    <span class="stars" aria-hidden="true"><span>★</span><span>★</span><span>★</span><span>★</span><span>★</span></span>
                    <strong>1000+ reviews</strong>
                    <span class="lb-topups__trustpilot-brand"><i class="fa-solid fa-star"></i> Trustpilot</span>
                </div>
                <?php endif; ?>
            </div>
        </aside>
        <section class="lb-topups__other-wrap" id="tuOtherWrap" style="display:none">
            <h2 class="lb-topups__other-title">Other Sellers · <span id="tuOtherCount">0</span></h2>
            <div class="lb-topups__other-list" id="tuOther"></div>
        </section>
    </main>

    <?php endif; ?>

    <?php if ($tuShowTrustedBy): ?>
    <section class="lb-topups__trustedby">
        <h2>Trusted by Millions of Gamers Worldwide</h2>
        <div class="lb-topups__trustedby-card">
            <span class="lb-topups__trustedby-badge"><i class="fa-solid fa-shield-check"></i></span>
            <div>
                <strong>Excellent <span class="rating"><?= htmlspecialchars(number_format($tuSiteRating, 1)) ?></span> out of 5.0</strong>
                <small>Based on <?= htmlspecialchars(number_format($tuSiteOrders)) ?>+ orders</small>
            </div>
        </div>
    </section>
    <?php endif; ?>
</div>

<?php if ($offerCards): ?>
<script>
(function(){
  var root=document.querySelector('.lb-topups'); if(!root) return;

  function syncTopSpacing(){
    var maxBottom=0;
    Array.prototype.forEach.call(document.body ? document.body.querySelectorAll('*') : [], function(el){
      if(el===root || root.contains(el)) return;
      var cs=window.getComputedStyle(el);
      if(cs.display==='none' || cs.visibility==='hidden') return;
      if(cs.position!=='fixed' && cs.position!=='sticky') return;
      var r=el.getBoundingClientRect();
      if(r.width<80 || r.height<18) return;
      if(r.top>220 || r.bottom<0) return;
      if(r.bottom > window.innerHeight-120) return;
      maxBottom=Math.max(maxBottom, r.bottom);
    });
    var fallback=window.matchMedia('(max-width:760px)').matches ? 104 : (window.matchMedia('(max-width:900px)').matches ? 150 : 184);
    var next=Math.max(24, Math.round(maxBottom+24));
    root.style.setProperty('--tu-safe-top', (maxBottom ? next : fallback)+'px');
  }
  syncTopSpacing();
  window.addEventListener('resize', syncTopSpacing, {passive:true});
  window.addEventListener('load', syncTopSpacing, {once:true});
  var rows=[]; try{rows=JSON.parse(root.dataset.topups||'[]')||[]}catch(e){rows=[]}
  var activeRegionEl=document.querySelector('#tuRegions .is-active'); var region=activeRegionEl ? (activeRegionEl.dataset.region||'global') : 'global';
  var activeOfferEl=document.querySelector('#tuOffers .is-active'); var key=activeOfferEl ? (activeOfferEl.dataset.key||'') : '';
  var qty=1;
  var forcedId=null; /* set when a specific seller is picked from "Other Sellers" */
  var currencyCode=String(root.dataset.currency||'EUR').toUpperCase();
  var currencyRate=Number(root.dataset.currencyRate||1);
  if(!currencyRate || currencyRate<0) currencyRate=1;
  var currencySymbol=root.dataset.currencySymbol || (currencyCode==='USD'?'$':'€');

  function esc(s){return String(s||'').replace(/[&<>"']/g,function(m){return {'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'}[m]})}
  function formatConverted(cents,multiplier){
    var value=(Number(cents||0)/100)*currencyRate*(multiplier||1);
    try{return new Intl.NumberFormat(currencyCode==='EUR'?'de-DE':'en-US',{style:'currency',currency:currencyCode}).format(value)}
    catch(e){return currencySymbol+value.toFixed(2).replace('.', currencyCode==='EUR'?',':'.')}
  }
  function money(c){return formatConverted(c, qty)}
  function unitMoney(c){return formatConverted(c, 1)}
  function time(r){var v=Number(r.waiting_time_value||0),u=r.waiting_time_unit||'minutes'; if(u==='minutes') return v+' min'; if(u==='hours') return v+' h'; return v+' d'}

  /* Mirrors the markup produced by lb_render_seller_footer($seller, ['variant' => 'card']) */
  function sellerHtml(r){
    var icon=r.seller_icon||'/public/assets/website/images/logo.svg';
    var name=r.seller_username||'Seller';
    var sold=Number(r.seller_total_sales||r.sales||0);
    var online=!!(r.seller_is_online||r.is_online);
    var verified=!!(r.seller_is_active||r.seller_rank||r.is_active);
    var rankTitle=r.seller_rank||'Verified Seller';
    var html='<div class="lb-seller-footer">';
    html+='<div class="lb-seller-footer__left">';
    html+='<img class="lb-seller-footer__avatar" src="'+esc(icon)+'" alt="'+esc(name)+'">';
    html+='<div class="lb-seller-footer__meta">';
    html+='<a class="lb-seller-footer__name" href="javascript:void(0)">';
    html+='<span class="lb-seller-footer__name-text">'+esc(name)+'</span>';
    if(online) html+='<span class="lb-seller-footer__online" title="Online" aria-label="Online"></span>';
    if(verified) html+='<i class="fa-solid fa-badge-check lb-seller-footer__verified" style="color:#3b82f6;"></i><span class="lb-seller-footer__tooltip"><i class="fa-solid fa-badge-check" style="color:#3b82f6;"></i>'+esc(rankTitle)+'</span>';
    html+='</a></div></div>';
    html+='<div class="lb-seller-footer__right">';
    html+='<span class="lb-seller-footer__stat lb-seller-footer__sold" title="'+esc(sold)+' Sold">'+esc(sold)+' Sold</span>';
    html+='<span class="lb-seller-footer__stat lb-seller-footer__trusted" title="Trusted" aria-label="Trusted"><i class="fas fa-thumbs-up"></i></span>';
    html+='</div></div>';
    return html;
  }

  function syncPopularBadge(){
    /* Popular is a static shop highlight now. Selecting an offer must not turn it into Popular. */
  }

  function refreshOffers(){
    var first=null;
    document.querySelectorAll('#tuOffers .lb-topups__offer').forEach(function(el){
      var exact=el.dataset.region===region; var fallback=el.dataset.region==='global'; var show=exact||fallback;
      el.style.display=show?'':'none';
      if(show&&!first) first=el;
    });
    var active=document.querySelector('#tuOffers .lb-topups__offer.is-active');
    if(!active || active.style.display==='none'){document.querySelectorAll('#tuOffers .lb-topups__offer').forEach(function(x){x.classList.remove('is-active')}); if(first){first.classList.add('is-active'); key=first.dataset.key}}
    syncPopularBadge();
    refresh();
  }

  function refresh(){
    var same=rows.filter(function(r){return String(r.offer_key||'')===String(key) && String(r.region||'global')===String(region)}).sort(function(a,b){return (Number(a.price)-Number(b.price))||(Number(a.waiting_time_minutes)-Number(b.waiting_time_minutes))});
    if(!same.length){same=rows.filter(function(r){return String(r.offer_key||'')===String(key) && String(r.region||'global')==='Global'}).sort(function(a,b){return (Number(a.price)-Number(b.price))||(Number(a.waiting_time_minutes)-Number(b.waiting_time_minutes))});}
    var best=same[0]||rows[0]; if(!best) return;

    if(forcedId!=null){
      var forcedRow=null;
      for(var fi=0; fi<same.length; fi++){ if(String(same[fi].id)===String(forcedId)){ forcedRow=same[fi]; break; } }
      if(forcedRow) best=forcedRow; else forcedId=null; /* selected seller has no offer in this region/amount anymore */
    }

    document.getElementById('tuPrice').textContent=money(best.price);
    document.getElementById('tuTime').textContent=time(best);
    document.getElementById('tuQty').textContent=qty;
    document.getElementById('tuQtyHidden').value=qty;
    document.getElementById('tuTopupId').value=best.id||'';
    var rh=document.getElementById('tuRegionHidden'); var sh=document.getElementById('tuServerHidden'); if(rh)rh.value=region; if(sh)sh.value=region;

    var im=best.image||'', imEl=document.getElementById('tuImg'); if(im){imEl.src=im; imEl.style.display='block'}else{imEl.style.display='none'}

    var cashback=Number(best.cashback_percent||0), cbEl=document.getElementById('tuCashback');
    if(cashback>0){document.getElementById('tuCashbackValue').textContent=cashback; cbEl.style.display='inline-flex'}else{cbEl.style.display='none'}

    var instrText=best.instructions||'Please provide the correct account details so the seller can deliver your top up.';
    var instrEl=document.getElementById('tuInstructions'); instrEl.textContent=instrText;
    var bodyEl=document.getElementById('tuSellerBody'); bodyEl.classList.remove('is-expanded');
    var toggleEl=document.getElementById('tuInstructionsToggle'); toggleEl.querySelector('span').textContent='View full instructions';
    toggleEl.classList.remove('is-visible');
    requestAnimationFrame(function(){
      if(instrEl.scrollHeight - 2 > instrEl.clientHeight){ toggleEl.classList.add('is-visible'); }
    });

    document.getElementById('tuSeller').innerHTML=sellerHtml(best);

    var others=same.filter(function(r){return String(r.id)!==String(best.id)}), wrap=document.getElementById('tuOtherWrap'), out=document.getElementById('tuOther');
    document.getElementById('tuOtherCount').textContent=others.length;
    wrap.style.display=others.length?'block':'none';
    out.innerHTML=others.map(function(r){
      var icon=r.seller_icon||'/public/assets/website/images/logo.svg';
      var name=r.seller_username||'Seller';
      var online=!!(r.seller_is_online||r.is_online);
      var verified=!!(r.seller_is_active||r.seller_rank||r.is_active);
      var rating=r.seller_rating_percent||r.seller_success_rate||r.rating_percent||r.success_rate||'';
      var reviews=r.seller_reviews||r.seller_review_count||r.reviews||r.review_count||'';
      var ratingHtml=rating ? '<strong><i class="fa-solid fa-thumbs-up"></i> '+esc(rating)+'</strong>' : '<strong><i class="fa-solid fa-thumbs-up"></i> Trusted</strong>';
      var reviewsHtml=reviews ? '<span>'+esc(reviews)+' Reviews</span>' : '';
      return '<div class="lb-topups__other" data-seller-offer="'+esc(r.id||'')+'" role="button" tabindex="0">'
        +'<div class="lb-topups__other-head"><span class="lb-topups__other-avatar-wrap"><img class="lb-topups__other-avatar" src="'+esc(icon)+'" alt="'+esc(name)+'">'+(online?'<span class="lb-topups__other-online"></span>':'')+'</span><span class="lb-topups__other-seller-meta"><span class="lb-topups__other-name-line"><span class="lb-topups__other-name">'+esc(name)+'</span>'+(verified?'<i class="fa-solid fa-badge-check lb-topups__other-verified"></i>':'')+'</span><span class="lb-topups__other-rating">'+ratingHtml+reviewsHtml+'</span></span></div>'
        +'<div class="lb-topups__other-meta"><span class="lb-topups__other-col-label">Stock</span><span class="lb-topups__other-unit">'+esc(r.stock||999)+' Unit</span></div>'
        +'<div class="lb-topups__other-meta"><span class="lb-topups__other-col-label">Min Qty.</span><span class="lb-topups__other-unit">'+esc(r.min_quantity||1)+' Unit</span></div>'
        +'<div class="lb-topups__other-meta"><span class="lb-topups__other-col-label">Delivery Time</span><span class="lb-topups__other-unit"><i class="fa-solid fa-clock"></i> '+time(r)+'</span></div>'
        +'<div class="lb-topups__other-meta lb-topups__other-price"><span class="lb-topups__other-col-label">Price</span><strong>'+unitMoney(r.price)+'</strong><small>/Unit</small></div>'
        +'<button class="lb-topups__other-arrow" type="button" aria-label="Select this seller" title="Use this seller"><i class="fa-solid fa-chevron-right"></i></button>'
        +'</div>';
    }).join('');
  }

  document.querySelectorAll('#tuRegions .lb-topups__pill').forEach(function(b){
    b.addEventListener('click',function(){
      region=b.dataset.region;
      forcedId=null;
      try{var u=new URL(window.location.href);u.searchParams.set('region', region); window.history.replaceState({}, '', u.toString());}catch(e){}
      document.querySelectorAll('#tuRegions .lb-topups__pill').forEach(function(x){x.classList.remove('is-active');var c=x.querySelector('.lb-topups__check');if(c)c.textContent='';});
      b.classList.add('is-active'); var c=b.querySelector('.lb-topups__check'); if(c)c.textContent='✓';
      refreshOffers();
    });
  });

  document.querySelectorAll('#tuOffers .lb-topups__offer').forEach(function(b){
    b.addEventListener('click',function(){
      key=b.dataset.key;
      forcedId=null;
      document.querySelectorAll('#tuOffers .lb-topups__offer').forEach(function(x){x.classList.remove('is-active')});
      b.classList.add('is-active');
      syncPopularBadge();
      refresh();
    });
  });

  document.addEventListener('click',function(e){
    var btn=e.target.closest('[data-seller-offer]'); if(!btn) return;
    var id=btn.dataset.sellerOffer; var row=null;
    for(var ri=0;ri<rows.length;ri++){if(String(rows[ri].id)===String(id)){row=rows[ri];break;}}
    if(!row) return;
    forcedId=id;
    key=row.offer_key||key; region=row.region||region;
    document.querySelectorAll('#tuRegions .lb-topups__pill').forEach(function(x){var on=x.dataset.region===region;x.classList.toggle('is-active',on);var c=x.querySelector('.lb-topups__check');if(c)c.textContent=on?'✓':''});
    document.querySelectorAll('#tuOffers .lb-topups__offer').forEach(function(x){x.classList.toggle('is-active', x.dataset.key===key)});
    syncPopularBadge();
    refreshOffers();
  });
  document.addEventListener('keydown',function(e){
    if(e.key!=='Enter' && e.key!==' ') return;
    var row=e.target.closest('.lb-topups__other[data-seller-offer]'); if(!row) return;
    e.preventDefault(); row.click();
  });

  document.getElementById('tuInstructionsToggle').addEventListener('click',function(){
    var bodyEl=document.getElementById('tuSellerBody'); var expanded=bodyEl.classList.toggle('is-expanded');
    this.querySelector('span').textContent=expanded?'Show less':'View full instructions';
  });

  /* Custom, on-brand validation bubble instead of the plain browser tooltip */
  var buyForm=document.getElementById('tuBuyForm');
  if(buyForm){
    buyForm.setAttribute('novalidate','novalidate');
    function tuClearFieldError(wrap){
      wrap.classList.remove('has-error');
      var t=wrap.querySelector('.lb-topups__field-error'); if(t) t.remove();
    }
    function tuShowFieldError(wrap, message){
      tuClearFieldError(wrap);
      wrap.classList.add('has-error');
      var tip=document.createElement('div');
      tip.className='lb-topups__field-error';
      tip.innerHTML='<i class="fa-solid fa-triangle-exclamation"></i><span>'+esc(message)+'</span>';
      wrap.appendChild(tip);
    }
    buyForm.querySelectorAll('.lb-topups__field input').forEach(function(inp){
      inp.addEventListener('input',function(){ var w=inp.closest('.lb-topups__field'); if(w) tuClearFieldError(w); });
    });
    buyForm.addEventListener('submit', function(e){
      var wraps=buyForm.querySelectorAll('.lb-topups__field'); var firstInvalid=null;
      wraps.forEach(function(w){ tuClearFieldError(w); });
      wraps.forEach(function(w){
        var inp=w.querySelector('input'); if(!inp) return;
        var isEmpty=inp.hasAttribute('required') && String(inp.value||'').trim()==='';
        if(isEmpty || !inp.checkValidity()){
          tuShowFieldError(w, isEmpty ? 'Please fill out this field.' : (inp.validationMessage || 'Please fill out this field.'));
          if(!firstInvalid) firstInvalid=inp;
        }
      });
      if(firstInvalid){ e.preventDefault(); e.stopPropagation(); e.stopImmediatePropagation(); firstInvalid.focus(); return false; }
    }, true);
  }

  document.getElementById('tuMinus').addEventListener('click',function(){qty=Math.max(1,qty-1);refresh()});
  document.getElementById('tuPlus').addEventListener('click',function(){qty++;refresh()});

  refreshOffers();
})();
</script>
<?php endif; ?>


<style id="lb-mobile-topups-hero-notify-final-fix">
@media(max-width:760px){
  .topups-shop-page .lb-topups,
  .lb-topups{overflow:visible!important;}
  .lb-topups__hero{background:#0f0c1f!important;border-bottom:1px solid rgba(255,255,255,.06)!important;margin:0!important;}
  .lb-topups__hero:before{display:none!important;}
  .lb-topups__wrap{padding-left:16px!important;padding-right:16px!important;}
  .lb-topups__hero-inner{padding:12px 0 18px!important;gap:10px!important;align-items:flex-start!important;}
  .lb-topups__icon{width:38px!important;height:38px!important;min-width:38px!important;border-radius:12px!important;margin-top:1px!important;}
  .lb-topups__icon img{width:25px!important;height:25px!important;border-radius:8px!important;}
  .lb-topups__title{font-size:17px!important;line-height:1.18!important;max-width:none!important;}
  .lb-topups__desc{font-size:12px!important;line-height:1.32!important;margin-top:5px!important;max-width:none!important;}
  .lb-topups__layout{padding-top:18px!important;}
  .lb-shop-empty-notify-offset{
    --lb-empty-top-gap:74px!important;
    --lb-empty-bottom-gap:74px!important;
    min-height:calc(100svh - var(--lb-empty-page-chrome, 250px))!important;
    padding-left:16px!important;
    padding-right:16px!important;
  }
}
@media(max-width:420px){
  .lb-topups__wrap{padding-left:14px!important;padding-right:14px!important;}
  .lb-topups__hero-inner{padding:12px 0 16px!important;}
  .lb-shop-empty-notify-offset{--lb-empty-top-gap:78px!important;--lb-empty-bottom-gap:72px!important;}
}
</style>


<style id="lb-topups-dynamic-header-seat-final-v2">
html{scroll-padding-top:calc(var(--lb-content-top, 0px) + 18px)!important;}
.lb-topups{
  --tu-safe-top:var(--lb-content-top, 0px)!important;
  padding-top:var(--lb-content-top, 0px)!important;
}
.lb-topups.lb-topups--empty{
  padding-top:0!important;
}
.lb-topups__hero{
  background:#0e0c1c!important;
  border:0!important;
  border-bottom:0!important;
  margin:0!important;
}
.lb-shop-empty-notify-offset{
  padding-top:calc(var(--lb-content-top, 0px) + 42px)!important;
  padding-bottom:72px!important;
  min-height:calc(100svh - var(--lb-content-top, 0px))!important;
}
@media(max-width:760px){
  .lb-topups{
    padding-top:var(--lb-content-top, 0px)!important;
  }
  .lb-topups__hero{
    background:#0e0c1c!important;
    border:0!important;
    border-bottom:0!important;
  }
  .lb-shop-empty-notify-offset{
    padding-top:calc(var(--lb-content-top, 0px) + 22px)!important;
    padding-bottom:108px!important;
    min-height:calc(100svh - var(--lb-content-top, 0px))!important;
  }
}
</style>

<style id="lb-topups-coming-soon-unified-position-final">
/* Top-ups uses a different wrapper than accounts/items. This final seat normalizes
   the empty Coming soon block to the same visual position below the dynamic header. */
body.topups-shop-page .lb-topups.lb-topups--empty{
  padding-top:0!important;
  min-height:100svh!important;
}
body.topups-shop-page .lb-topups.lb-topups--empty .lb-shop-empty-notify-offset{
  padding-top:calc(var(--lb-content-top, 0px) + 92px)!important;
  padding-bottom:88px!important;
  min-height:calc(100svh - var(--lb-content-top, 0px))!important;
  display:flex!important;
  align-items:center!important;
  justify-content:center!important;
}
body.topups-shop-page .lb-topups.lb-topups--empty .lb-shop-empty-notify-offset > .lb-cs2{
  margin:0 auto!important;
}
@media(max-width:760px){
  body.topups-shop-page .lb-topups.lb-topups--empty .lb-shop-empty-notify-offset{
    padding-top:calc(var(--lb-content-top, 0px) + 64px)!important;
    padding-bottom:128px!important;
    min-height:calc(100svh - var(--lb-content-top, 0px))!important;
    padding-left:16px!important;
    padding-right:16px!important;
  }
}
@media(max-width:420px){
  body.topups-shop-page .lb-topups.lb-topups--empty .lb-shop-empty-notify-offset{
    padding-top:calc(var(--lb-content-top, 0px) + 58px)!important;
  }
}
</style>

<style id="lb-topups-hero-no-background">
/* Hero has no colour of its own — it shows the page background. */
html body .lb-topups__hero,
html body.topups-shop-page .lb-topups__hero,
html body main > .lb-topups__hero:first-child,
html body .page-zoom > main > .lb-topups__hero:first-child{
  background:transparent !important;
  background-color:transparent !important;
  background-image:none !important;
  box-shadow:none !important;
}
html body .lb-topups__hero::before,
html body .lb-topups__hero::after{
  content:none !important;
  display:none !important;
  background:none !important;
  opacity:0 !important;
}
</style>
