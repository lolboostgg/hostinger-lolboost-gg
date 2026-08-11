<?php
/**
 * Header (with updated Season Sale Banner)
 * NOTE: This file is generated from the code you provided in chat, with only the banner CSS/HTML/JS adjusted.
 */
?>

<?php
  // Trustpilot link helper (defaults to your domain's Trustpilot review page)
  $tpHost = isset($_SERVER['HTTP_HOST']) ? preg_replace('/^www\./i', '', $_SERVER['HTTP_HOST']) : '';
  $trustpilotUrl = $tpHost ? ('https://www.trustpilot.com/review/' . $tpHost) : 'https://www.trustpilot.com/';

  // Cache-busting helper for header assets extracted into external, cacheable
  // files (same approach as master.php's $assetVersion, kept local here since
  // this partial can't rely on the parent template's closure being in scope).
  if (!function_exists('lb_header_asset_version')) {
      function lb_header_asset_version(string $relativePath): string {
          $publicRoot = rtrim((string)($_SERVER['DOCUMENT_ROOT'] ?? ''), '/');
          $fullPath = $publicRoot . '/' . ltrim($relativePath, '/');
          return is_file($fullPath) ? (string)filemtime($fullPath) : '1';
      }
  }
?>

<?php
  $lbRequestPath = strtok($_SERVER['REQUEST_URI'] ?? '/', '?') ?: '/';
  $lbSegments = array_values(array_filter(explode('/', trim($lbRequestPath, '/'))));
  $lbGames = util_game_nav_config();
  $lbCurrentGame = $lbSegments[0] ?? null;
  $lbCurrentGame = isset($lbGames[$lbCurrentGame]) ? $lbCurrentGame : null;
  $lbCurrentCategory = null;
  if ($lbCurrentGame && isset($lbSegments[1])) {
      if (in_array($lbSegments[1], ['accounts', 'premium-accounts', 'account'], true)) {
          $lbCurrentCategory = 'accounts';
      } elseif (in_array($lbSegments[1], ['items', 'item', 'selling_item'], true)) {
          $lbCurrentCategory = 'items';
      } elseif (in_array($lbSegments[1], ['top-ups', 'topups', 'currencies', 'currency'], true)) {
          $lbCurrentCategory = 'topups';
      } elseif ($lbSegments[1] === 'coaching') {
          $lbCurrentCategory = 'coaching';
      } else {
          // Any concrete boost-form route, for example /rank-boost or /win-boost.
          $lbCurrentCategory = 'boosting';
      }
  }
  $lbBoostForms = $lbCurrentGame ? util_load_game_boost_forms($lbCurrentGame) : [];
  $lbHasGameNav = $lbCurrentGame && !empty($lbGames[$lbCurrentGame]);

  if (!function_exists('lb_header_topups_enabled_for_game')) {
      function lb_header_topups_enabled_for_game(string $gameSlug): bool {
          $gameSlug = trim($gameSlug);
          if ($gameSlug === '') return false;

          $gameRow = function_exists('util_get_game_by_slug') ? (util_get_game_by_slug($gameSlug) ?: []) : [];
          $gameId = (int)($gameRow['id'] ?? 0);

          if ($gameId > 0 && function_exists('util_game_has_service')) {
              return util_game_has_service($gameId, 'topups')
                  || util_game_has_service($gameId, 'top-ups')
                  || util_game_has_service($gameId, 'currencies');
          }

          return in_array($gameSlug, ['league-of-legends', 'lol', 'league'], true);
      }
  }

  if (!function_exists('lb_header_game_service_enabled')) {
      function lb_header_game_service_enabled(string $gameSlug, string $serviceKey, array $gameData = []): bool {
          $serviceKey = strtolower(trim($serviceKey));
          $categories = (array)($gameData['categories'] ?? []);
          if (array_key_exists($serviceKey, $categories)) return true;
          if ($serviceKey === 'topups' && (array_key_exists('top-ups', $categories) || array_key_exists('currencies', $categories) || array_key_exists('currency', $categories))) return true;

          $gameRow = function_exists('util_get_game_by_slug') ? (util_get_game_by_slug($gameSlug) ?: []) : [];
          $gameId = (int)($gameRow['id'] ?? 0);

          if ($gameId > 0 && function_exists('util_game_has_service')) {
              $aliases = [
                  'boosting' => ['boosting', 'boost', 'rank-boost'],
                  'accounts' => ['accounts', 'account', 'premium-accounts'],
                  'items'    => ['items', 'item', 'selling-items'],
                  'topups'   => ['topups', 'top-ups', 'currencies', 'currency'],
              ];
              foreach (($aliases[$serviceKey] ?? [$serviceKey]) as $alias) {
                  try {
                      if (util_game_has_service($gameId, $alias)) return true;
                  } catch (Throwable $e) {
                      // Keep header rendering safe if the optional service helper fails.
                  }
              }
          }

          if ($serviceKey === 'topups') return lb_header_topups_enabled_for_game($gameSlug);

          return false;
      }
  }

  foreach ($lbGames as $lbGameSlug => $lbGameData) {
      $lbGameSlug = (string)$lbGameSlug;
      $lbGameData = is_array($lbGameData) ? $lbGameData : [];
      $lbCatDefaults = [
          'boosting' => ['label' => 'Boosting', 'href' => '/' . trim($lbGameSlug, '/') . '/rank-boost'],
          'accounts' => ['label' => 'Accounts', 'href' => '/' . trim($lbGameSlug, '/') . '/accounts'],
          'items'    => ['label' => 'Items', 'href' => '/' . trim($lbGameSlug, '/') . '/items'],
          'topups'   => ['label' => 'Top-ups', 'href' => '/' . trim($lbGameSlug, '/') . '/top-ups'],
      ];

      foreach ($lbCatDefaults as $lbCatKey => $lbCatDefault) {
          if (!lb_header_game_service_enabled($lbGameSlug, $lbCatKey, $lbGameData)) continue;
          if ($lbCatKey === 'topups') {
              $lbTopupCfg = function_exists('lb_get_topups_page_config') ? (lb_get_topups_page_config($lbGameSlug) ?: []) : [];
              $lbCatDefault['label'] = (string)($lbTopupCfg['service_label'] ?? $lbCatDefault['label']);
          }
          if (empty($lbGames[$lbGameSlug]['categories'][$lbCatKey])) {
              $lbGames[$lbGameSlug]['categories'][$lbCatKey] = $lbCatDefault;
          }
      }
  }

  if (!function_exists('lb_header_game_service_listing_count')) {
      function lb_header_game_service_listing_count(string $gameSlug, string $serviceKey, array $gameData = []): ?int {
          $serviceKey = strtolower(trim($serviceKey));
          if ($serviceKey === '') return null;

          $keyMap = [
              'boosting' => ['boosting_count', 'boost_count', 'boosts_count', 'boosting_offers', 'rank_boost_count'],
              'accounts' => ['accounts_count', 'account_count', 'accounts_offers', 'account_offers'],
              'items'    => ['items_count', 'item_count', 'items_offers', 'item_offers'],
              'topups'   => ['topups_count', 'topup_count', 'topups_offers', 'topup_offers', 'currency_count', 'currencies_count'],
          ];

          foreach (($keyMap[$serviceKey] ?? []) as $key) {
              if (array_key_exists($key, $gameData) && is_numeric($gameData[$key])) {
                  return max(0, (int)$gameData[$key]);
              }
          }

          $gameRow = function_exists('util_get_game_by_slug') ? (util_get_game_by_slug($gameSlug) ?: []) : [];
          if (is_array($gameRow) && !empty($gameRow) && function_exists('lb_count_service_offers')) {
              $serviceAliases = [
                  'boosting' => ['boosting'],
                  'accounts' => ['accounts'],
                  'items'    => ['items'],
                  'topups'   => ['topups', 'top-ups', 'currencies', 'currency'],
              ];
              foreach (($serviceAliases[$serviceKey] ?? [$serviceKey]) as $alias) {
                  try {
                      $count = lb_count_service_offers($gameRow, $alias);
                      if (is_numeric($count)) {
                          return max(0, (int)$count);
                      }
                  } catch (Throwable $e) {
                      // Optional counters must never break the header.
                  }
              }
          }

          // Header.php is rendered before the route helper lb_count_service_offers() can be available
          // on some pages. In that case the modal had no category specific zero counts, so filtering
          // to Accounts/Items/Top-ups could not know that the selected category is still empty.
          global $db;
          if (empty($db)) return null;

          $slug = strtolower(trim((string)$gameSlug));
          $aliases = array_values(array_unique(array_filter([
              $slug,
              str_replace('-', ' ', $slug),
              strtolower(trim((string)($gameData['name'] ?? ''))),
              strtolower(trim((string)($gameData['label'] ?? ''))),
              strtolower(trim((string)($gameData['game']['name'] ?? ''))),
              strtolower(trim((string)($gameData['game']['short_code'] ?? ''))),
          ], static fn($v) => $v !== '')));

          if ($slug === 'league-of-legends') {
              $aliases = array_values(array_unique(array_merge($aliases, ['lol', 'league', 'league of legends'])));
          } elseif ($slug === 'valorant') {
              $aliases = array_values(array_unique(array_merge($aliases, ['val', 'valo', 'valorant'])));
          } elseif ($slug === 'teamfight-tactics') {
              $aliases = array_values(array_unique(array_merge($aliases, ['tft', 'teamfight tactics'])));
          }

          $inList = implode(',', array_map(static fn($v) => "'" . addslashes($v) . "'", $aliases ?: [$slug]));
          $gameId = (int)($gameData['game']['id'] ?? $gameData['id'] ?? 0);

          try {
              if ($serviceKey === 'boosting') {
                  $forms = function_exists('util_load_game_boost_forms') ? (util_load_game_boost_forms($slug) ?: []) : [];
                  return count($forms);
              }

              if ($serviceKey === 'accounts') {
                  $lolExtra = ($slug === 'league-of-legends') ? " OR game IS NULL OR game = ''" : '';
                  return (int)$db->single("SELECT COUNT(*) FROM selling_accounts WHERE COALESCE(sold,0) = 0 AND COALESCE(active,1) = 1 AND (LOWER(COALESCE(game,'')) IN ({$inList}){$lolExtra})");
              }

              if ($serviceKey === 'items') {
                  if ($gameId > 0) {
                      $byId = (int)$db->single("SELECT COUNT(*) FROM selling_items WHERE COALESCE(active,1) = 1 AND game_id = {$gameId}");
                      if ($byId > 0) return $byId;
                  }
                  return (int)$db->single("SELECT COUNT(*) FROM selling_items WHERE COALESCE(active,1) = 1 AND LOWER(COALESCE(game,'')) IN ({$inList})");
              }

              if ($serviceKey === 'topups') {
                  if ($gameId > 0) {
                      $byId = (int)$db->single("SELECT COUNT(*) FROM selling_topups WHERE COALESCE(active,1) = 1 AND game_id = {$gameId}");
                      if ($byId > 0) return $byId;
                  }
                  return (int)$db->single("SELECT COUNT(*) FROM selling_topups WHERE COALESCE(active,1) = 1 AND LOWER(COALESCE(game_slug,'')) IN ({$inList})");
              }
          } catch (Throwable $e) {
              return null;
          }

          return null;
      }
  }

  if (!function_exists('lb_header_game_listing_count')) {
      function lb_header_game_listing_count(string $gameSlug, array $gameData = []): int {
          $explicitKeys = ['active_offers', 'offer_count', 'listing_count', 'listings_count', 'total_offers', 'total_listings'];
          foreach ($explicitKeys as $key) {
              if (array_key_exists($key, $gameData) && is_numeric($gameData[$key])) {
                  return max(0, (int)$gameData[$key]);
              }
          }

          $gameRow = function_exists('util_get_game_by_slug') ? (util_get_game_by_slug($gameSlug) ?: []) : [];
          $total = 0;
          $hasRealCount = false;

          if (is_array($gameRow) && !empty($gameRow) && function_exists('lb_count_service_offers')) {
              foreach (['boosting', 'accounts', 'items', 'topups', 'top-ups', 'currencies', 'currency'] as $serviceKey) {
                  try {
                      $count = lb_count_service_offers($gameRow, $serviceKey);
                      if (is_numeric($count)) {
                          $total += max(0, (int)$count);
                          $hasRealCount = true;
                      }
                  } catch (Throwable $e) {
                      // Keep the header safe even when one optional counter is unavailable.
                  }
              }
          }

          if ($hasRealCount) return $total;

          foreach (['boosting_count', 'accounts_count', 'items_count', 'topup_count', 'topups_count', 'currency_count'] as $key) {
              if (array_key_exists($key, $gameData) && is_numeric($gameData[$key])) {
                  $total += max(0, (int)$gameData[$key]);
                  $hasRealCount = true;
              }
          }

          if ($hasRealCount) return $total;

          // Last fallback: if no counter exists, avoid marking existing configured games as soon by accident.
          return empty($gameData['categories']) ? 0 : 1;
      }
  }

  foreach ($lbGames as $lbGameSlug => $lbGameData) {
      $lbGameSlug = (string)$lbGameSlug;
      $lbGameData = is_array($lbGameData) ? $lbGameData : [];
      $lbListingCount = lb_header_game_listing_count($lbGameSlug, $lbGameData);
      $lbGames[$lbGameSlug]['active_offers'] = $lbListingCount;
      $lbGames[$lbGameSlug]['is_coming_soon'] = ($lbListingCount <= 0);

      $lbSoonCats = [];
      $lbCategoryCounts = [];
      $lbCategoryKeys = array_keys((array)($lbGameData['categories'] ?? []));

      // Only categories that are really configured for this game may become a Soon category.
      // The previous fallback marked every missing service as Soon, so the quick tabs showed
      // almost all games as Boosting / Accounts / Top-ups / Items and counts like 275.
      foreach (['boosting', 'accounts', 'items', 'topups'] as $lbServiceKey) {
          $lbServiceConfigured = in_array($lbServiceKey, $lbCategoryKeys, true);
          $lbServiceCount = lb_header_game_service_listing_count($lbGameSlug, $lbServiceKey, $lbGameData);

          if ($lbServiceCount === null) {
              $lbServiceCount = $lbServiceConfigured ? 0 : null;
          }

          $lbCategoryCounts[$lbServiceKey] = max(0, (int)($lbServiceCount ?? 0));

          if ($lbServiceConfigured && (int)($lbServiceCount ?? 0) <= 0) {
              $lbSoonCats[] = $lbServiceKey;
          }

          if (!$lbServiceConfigured && is_numeric($lbServiceCount) && (int)$lbServiceCount > 0) {
              $lbCategoryKeys[] = $lbServiceKey;
          }
      }

      // A completely empty game still gets its Soon badge in the All view, but it should not
      // be counted inside every service filter unless that service exists for the game.
      $lbGames[$lbGameSlug]['category_offer_counts'] = $lbCategoryCounts;
      $lbGames[$lbGameSlug]['soon_categories'] = array_values(array_unique($lbSoonCats));
  }
?>

<link rel="stylesheet" href="<?= ASSET_URL ?>/website/css/header-consolidated.css?v=<?= lb_header_asset_version("/public/assets/website/css/header-consolidated.css") ?>">

<?php
    // Alle unterstützten Sprachen (Code => Label)
    $languageLabels = [
        'en'  => 'English',
        'de'  => 'Deutsch',
        'fr'  => 'Français',
        'es'  => 'Español',
        'pt'  => 'Português',
        'it'  => 'Italiano',
        'nl'  => 'Nederlands',
        'pl'  => 'Polski',
        'ru'  => 'Русский',
        'jp'  => '日本語',
        'zh'  => '中文',
        'sv'  => 'Svenska',
        'no'  => 'Norsk',
        'da'  => 'Dansk',
        'fi'  => 'Suomi',
        'el'  => 'Ελληνικά',
        'hu'  => 'Magyar',
        'cs'  => 'Čeština',
        'bg'  => 'Български',
        'ro'  => 'Română',
        'tr'  => 'Türkçe',
        'hr'  => 'Hrvatski',
        'ar'  => 'العربية',
        'fil' => 'Filipino',
        'id'  => 'Bahasa Indonesia',
        'th'  => 'ไทย',
    ];

    // Aktuelle Sprache ermitteln (Fallback: en)
    $currentLang = (defined('LANG') && isset($languageLabels[LANG])) ? LANG : 'en';

    $currentLangLabel = $languageLabels[$currentLang];

    // Aktuelle Währung
    $currentCurrency = $_SESSION['currency'] ?? 'EUR';
?>

<?php
  // =========================
  // 🎁 Giveaway (optional) — Daten + Banner + Modal
  // =========================
  // Shows only if an ACTIVE giveaway exists
  // =========================
  $activeGiveaway = null;
  try {
    if (function_exists('giveaway_get_active')) {
      $activeGiveaway = giveaway_get_active();
    }
  } catch (Throwable $e) {
    $activeGiveaway = null;
  }

  $gwId      = is_array($activeGiveaway) ? (int)($activeGiveaway['id'] ?? 0) : 0;
  $gwTitle   = is_array($activeGiveaway) ? (string)($activeGiveaway['title'] ?? 'Giveaway') : 'Giveaway';
  $gwEnds    = is_array($activeGiveaway) ? (string)($activeGiveaway['ends_at'] ?? '') : '';
  $gwWinners = is_array($activeGiveaway) ? (int)($activeGiveaway['winners_count'] ?? 0) : 0;
$gwPrizes = [];
try {
  // Fetch prizes (1st/2nd/3rd etc.) for the modal
  if ($gwId > 0) {
    if (function_exists('db_get_rows')) {
      $gwPrizes = db_get_rows('giveaway_prizes', [
        'giveaway_id' => $gwId,
        'order' => 'position,ASC'
      ], true);
      if (!is_array($gwPrizes)) $gwPrizes = [];
    } elseif (isset($db) && method_exists($db, 'run')) {
      // Fallback: build a safe query using an integer giveaway id (run() accepts a single SQL string in this codebase)
      $gwIdInt = intval($gwId);
      $rows = $db->run("SELECT `position`, `name`, `description` FROM `giveaway_prizes` WHERE `giveaway_id` = {$gwIdInt} ORDER BY `position` ASC");
      if (is_array($rows)) $gwPrizes = $rows;
    } elseif (isset($db) && method_exists($db, 'query')) {
      $gwIdInt = intval($gwId);
      $stmt = $db->query("SELECT `position`, `name`, `description` FROM `giveaway_prizes` WHERE `giveaway_id` = {$gwIdInt} ORDER BY `position` ASC");
      if ($stmt) $gwPrizes = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }
  }
} catch (Throwable $e) {
  $gwPrizes = [];
}

  // Logged in? (client or booster)
  $isLoggedIn = ((defined('CLIENT_DATA') && CLIENT_DATA != false) || (defined('BOOSTER_DATA') && BOOSTER_DATA != false));

?>
<?php if ($gwId > 0): ?>
<style>#lbGiveawayOverlay{position:fixed;inset:0;z-index:2147483646;display:flex;align-items:center;justify-content:center;padding:calc(18px + env(safe-area-inset-top)) 18px calc(18px + env(safe-area-inset-bottom));background:rgba(2,6,23,.72);backdrop-filter:blur(10px);-webkit-backdrop-filter:blur(10px);opacity:0;pointer-events:none;transition:opacity .18s ease;}
#lbGiveawayOverlay.is-visible{opacity:1;pointer-events:auto;}
body.gw-open #tawkchat-container,body.gw-open .tawk-min-container,body.gw-open iframe[title*="chat"],body.gw-open iframe[title*="Chat"]{display:none !important;visibility:hidden !important;pointer-events:none !important;}
#lbGiveawayModal{position:relative;z-index:2147483647;width:100%;max-width:610px;max-height:calc(100vh - 36px);overflow:auto;-webkit-overflow-scrolling:touch;border-radius:22px;border:1px solid transparent;background:linear-gradient(180deg,rgba(17,15,31,1),rgba(10,11,23,1)) padding-box,linear-gradient(135deg,rgba(112,93,255,.95),rgba(0,194,255,.70),rgba(255,255,255,.14)) border-box;box-shadow:0 28px 90px rgba(0,0,0,.82),0 0 0 1px rgba(255,255,255,.06) inset;color:#f9fafb;}
@supports (height:100dvh){#lbGiveawayModal{max-height:calc(100dvh - 36px);}
}
#lbGiveawayModal .gw-pad{padding:18px;}
#lbGiveawayModal .gw-hero{position:relative;padding:18px 18px 14px;border-bottom:1px solid rgba(255,255,255,.08);background:radial-gradient(800px 240px at 15% 0%,rgba(112,93,255,.25),transparent 60%),radial-gradient(700px 220px at 85% 20%,rgba(0,194,255,.18),transparent 55%),rgba(0,0,0,.08);}
#lbGiveawayModal .gw-heroTop{display:flex;align-items:flex-start;justify-content:space-between;gap:12px;}
#lbGiveawayModal .gw-badges{display:flex;flex-wrap:wrap;gap:8px;margin-bottom:10px;}
#lbGiveawayModal .gw-badge{display:inline-flex;align-items:center;gap:8px;padding:7px 10px;border-radius:999px;font-size:12px;font-weight:900;letter-spacing:.06em;text-transform:uppercase;background:rgba(255,255,255,.06);border:1px solid rgba(255,255,255,.10);color:rgba(255,255,255,.92);}
#lbGiveawayModal .gw-badge.muted{font-weight:800;opacity:.85;letter-spacing:.04em;text-transform:none;}
#lbGiveawayModal .gw-title{font-weight:1000;font-size:20px;letter-spacing:.02em;line-height:1.2;}
#lbGiveawayModal .gw-sub{margin-top:6px;font-size:13px;color:rgba(229,231,235,.74);line-height:1.45;}
#lbGiveawayModal .gw-x{border:none;width:40px;height:40px;border-radius:999px;background:rgba(255,255,255,.06);color:rgba(255,255,255,.92);cursor:pointer;flex:0 0 auto;}
#lbGiveawayModal .gw-x:hover{background:rgba(255,255,255,.10);}
#lbGiveawayModal .gw-countdownWrap{margin-top:14px;padding:12px;border-radius:16px;background:rgba(0,0,0,.18);border:1px solid rgba(255,255,255,.10);}
#lbGiveawayModal .gw-countdownLabel{display:flex;align-items:center;justify-content:space-between;gap:10px;color:rgba(229,231,235,.72);font-size:12px;font-weight:800;letter-spacing:.08em;text-transform:uppercase;margin-bottom:10px;}
#lbGiveawayModal .gw-countdown{display:grid;grid-template-columns:repeat(4,1fr);gap:10px;}
#lbGiveawayModal .gw-timebox{padding:10px 10px 9px;border-radius:14px;background:rgba(255,255,255,.05);border:1px solid rgba(255,255,255,.10);text-align:center;}
#lbGiveawayModal .gw-timeNum{display:block;font-family:ui-monospace,SFMono-Regular,Menlo,Monaco,Consolas,"Liberation Mono","Courier New",monospace;font-variant-numeric:tabular-nums;font-weight:1000;font-size:18px;letter-spacing:.02em;}
#lbGiveawayModal .gw-timeUnit{display:block;margin-top:4px;font-size:11px;font-weight:900;letter-spacing:.10em;text-transform:uppercase;color:rgba(229,231,235,.70);}
#lbGiveawayModal .gw-stats{display:grid;grid-template-columns:repeat(3,1fr);gap:10px;margin-top:12px;}
#lbGiveawayModal .gw-stat{background:rgba(0,0,0,.14);border:1px solid rgba(255,255,255,.10);border-radius:16px;padding:12px;min-width:0;}
#lbGiveawayModal .gw-statK{color:rgba(229,231,235,.72);font-size:12px;font-weight:800;letter-spacing:.06em;text-transform:uppercase;display:flex;align-items:center;gap:8px;}
#lbGiveawayModal .gw-statV{margin-top:6px;font-weight:1000;font-size:14px;line-height:1.25;}
#lbGiveawayModal .gw-statV small{display:block;margin-top:3px;font-weight:700;font-size:12px;color:rgba(229,231,235,.70);}
#lbGiveawayModal .gw-section{padding:16px 18px 0;}
#lbGiveawayModal .gw-sectionTitle{font-weight:1000;font-size:13px;letter-spacing:.10em;text-transform:uppercase;color:rgba(255,255,255,.88);margin-bottom:10px;}
#lbGiveawayModal .gw-steps{margin:0;padding:0;list-style:none;display:grid;gap:10px;}
#lbGiveawayModal .gw-step{display:flex;gap:10px;align-items:flex-start;padding:12px;border-radius:16px;background:rgba(0,0,0,.16);border:1px solid rgba(255,255,255,.10);color:rgba(255,255,255,.90);font-size:13px;line-height:1.55;}
#lbGiveawayModal .gw-stepIcon{width:34px;height:34px;border-radius:12px;display:flex;align-items:center;justify-content:center;flex:0 0 auto;background:rgba(112,93,255,.18);border:1px solid rgba(112,93,255,.30);font-size:15px;}
#lbGiveawayModal .gw-step b{font-weight:1000;}
#lbGiveawayModal .gw-prizes{margin-top:12px;padding:12px;border-radius:16px;background:rgba(0,0,0,.16);border:1px solid rgba(255,255,255,.10);}
#lbGiveawayModal .gw-prize{display:flex;align-items:flex-start;justify-content:space-between;gap:12px;padding:12px;border-radius:14px;background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.08);margin-bottom:10px;}
#lbGiveawayModal .gw-prize:last-child{margin-bottom:0;}
#lbGiveawayModal .gw-prize[data-rank="1"]{background:radial-gradient(500px 140px at 10% 0%,rgba(255,215,0,.18),transparent 55%),rgba(255,255,255,.04);}
#lbGiveawayModal .gw-prize[data-rank="2"]{background:radial-gradient(500px 140px at 10% 0%,rgba(192,192,192,.16),transparent 55%),rgba(255,255,255,.04);}
#lbGiveawayModal .gw-prize[data-rank="3"]{background:radial-gradient(500px 140px at 10% 0%,rgba(205,127,50,.16),transparent 55%),rgba(255,255,255,.04);}
#lbGiveawayModal .gw-prizeLeft{min-width:0;}
#lbGiveawayModal .gw-prizeRank{display:flex;align-items:center;gap:8px;font-weight:1000;font-size:12px;color:rgba(229,231,235,.78);letter-spacing:.06em;text-transform:uppercase;}
#lbGiveawayModal .gw-prizeName{font-weight:1000;font-size:15px;margin-top:4px;line-height:1.25;}
#lbGiveawayModal .gw-prizeDesc{font-size:12px;color:rgba(229,231,235,.72);margin-top:6px;line-height:1.45;}
#lbGiveawayModal .gw-actions{position:sticky;bottom:0;padding:12px 18px 16px;margin-top:16px;background:linear-gradient(180deg,rgba(10,11,23,0),rgba(10,11,23,.92) 25%,rgba(10,11,23,1));border-top:1px solid rgba(255,255,255,.08);display:flex;gap:10px;flex-wrap:wrap;}
#lbGiveawayModal .gw-btn{flex:1;min-width:180px;text-decoration:none;display:flex;align-items:center;justify-content:center;padding:11px 14px;border-radius:999px;font-weight:1000;letter-spacing:.08em;font-size:13px;text-transform:uppercase;border:1px solid rgba(255,255,255,.14);background:rgba(255,255,255,.06);color:#fff;}
#lbGiveawayModal .gw-btn:hover{background:rgba(255,255,255,.10);}
#lbGiveawayModal .gw-btn.primary{border:0;background:radial-gradient(circle at 25% 35%,rgba(255,255,255,.18),transparent 55%),linear-gradient(135deg,rgba(112,93,255,.98),rgba(0,194,255,.80));}
@media (min-width:980px){#lbGiveawayOverlay{overflow-y:auto;scrollbar-width:none;}
#lbGiveawayOverlay::-webkit-scrollbar{width:0;height:0;}
#lbGiveawayModal{width:94vw;max-width:1180px;max-height:none;overflow:visible;}
#lbGiveawayModal .gw-body{display:grid;grid-template-columns:1.05fr .95fr;gap:16px;padding:16px 18px 0;}
#lbGiveawayModal .gw-section{padding:0;}
#lbGiveawayModal .gw-prizes{margin-top:0;}
#lbGiveawayModal .gw-actions{margin-top:14px;}
#lbGiveawayModal .gw-hero{padding:22px 26px 18px;}
#lbGiveawayModal .gw-badge{font-size:13px;padding:8px 12px;}
#lbGiveawayModal .gw-title{font-size:30px;}
#lbGiveawayModal .gw-sub{font-size:15px;max-width:900px;}
#lbGiveawayModal .gw-x{width:44px;height:44px;}
#lbGiveawayModal .gw-hero{display:grid;grid-template-columns:1.15fr .85fr;grid-template-areas:"top top" "countdown stats";gap:14px;}
#lbGiveawayModal .gw-heroTop{grid-area:top;}
#lbGiveawayModal .gw-countdownWrap{grid-area:countdown;margin-top:12px;}
#lbGiveawayModal .gw-stats{grid-area:stats;margin-top:12px;grid-template-columns:1fr;align-content:start;}
#lbGiveawayModal .gw-stat{padding:14px;}
#lbGiveawayModal .gw-countdownWrap{margin-top:16px;padding:16px;border-radius:20px;background:radial-gradient(700px 240px at 12% 5%,rgba(112,93,255,.35),transparent 60%),radial-gradient(700px 240px at 88% 20%,rgba(0,194,255,.25),transparent 55%),rgba(0,0,0,.20);border:1px solid rgba(255,255,255,.16);box-shadow:0 0 0 1px rgba(112,93,255,.22) inset,0 18px 60px rgba(112,93,255,.18);}
#lbGiveawayModal .gw-countdownLabel{font-size:13px;margin-bottom:12px;}
#lbGiveawayModal .gw-countdown{gap:14px;}
#lbGiveawayModal .gw-timebox{padding:14px 12px 13px;border-radius:18px;background:rgba(255,255,255,.07);border:1px solid rgba(255,255,255,.16);box-shadow:0 0 0 1px rgba(0,194,255,.14) inset;}
#lbGiveawayModal .gw-timeNum{font-size:32px;}
#lbGiveawayModal .gw-timeUnit{font-size:12px;}
#lbGiveawayModal .gw-stats{gap:14px;margin-top:14px;}
#lbGiveawayModal .gw-stat{padding:16px;border-radius:18px;}
#lbGiveawayModal .gw-statK{font-size:13px;}
#lbGiveawayModal .gw-statV{font-size:15px;}
#lbGiveawayModal .gw-statV small{font-size:13px;}
#lbGiveawayModal .gw-sectionTitle{font-size:14px;margin-bottom:12px;}
#lbGiveawayModal .gw-steps{gap:12px;}
#lbGiveawayModal .gw-step{padding:14px;border-radius:18px;font-size:14px;}
#lbGiveawayModal .gw-stepIcon{width:40px;height:40px;border-radius:14px;font-size:18px;}
#lbGiveawayModal .gw-prizes{padding:14px;border-radius:18px;}
#lbGiveawayModal .gw-prize{padding:14px;border-radius:16px;}
#lbGiveawayModal .gw-prizeName{font-size:17px;}
#lbGiveawayModal .gw-prizeDesc{font-size:13px;}
#lbGiveawayModal .gw-actions{padding:14px 22px 18px;gap:12px;}
#lbGiveawayModal .gw-btn{min-width:220px;padding:14px 18px;font-size:14px;}
#lbGiveawayModal .gw-hero{display:grid;grid-template-columns:1.15fr .85fr;grid-template-areas:"top top" "countdown stats";gap:14px;}
#lbGiveawayModal .gw-heroTop{grid-area:top;}
#lbGiveawayModal .gw-countdownWrap{grid-area:countdown;margin-top:12px;}
#lbGiveawayModal .gw-stats{grid-area:stats;margin-top:12px;grid-template-columns:1fr;gap:12px;}
#lbGiveawayModal .gw-prizes{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:12px;}
#lbGiveawayModal .gw-prize{margin-bottom:0;height:100%;}
#lbGiveawayModal .gw-body{gap:14px;padding:14px 18px 0;}
#lbGiveawayModal .gw-step{padding:12px;line-height:1.45;}
#lbGiveawayModal .gw-actions{padding:12px 22px 14px;}
#lbGiveawayModal .gw-btn{min-width:200px;padding:13px 16px;font-size:13px;}
}
@media (min-width:980px) and (max-height:820px){#lbGiveawayModal .gw-hero{gap:12px;}
#lbGiveawayModal .gw-title{font-size:26px;}
#lbGiveawayModal .gw-sub{font-size:14px;}
#lbGiveawayModal .gw-countdownWrap{padding:14px;}
#lbGiveawayModal .gw-timeNum{font-size:28px;}
#lbGiveawayModal .gw-timebox{padding:12px 10px 11px;}
#lbGiveawayModal .gw-stat{padding:12px;}
#lbGiveawayModal .gw-statV{margin-top:4px;}
#lbGiveawayModal .gw-step{padding:11px;font-size:13px;}
#lbGiveawayModal .gw-stepIcon{width:36px;height:36px;font-size:15px;}
#lbGiveawayModal .gw-prize{padding:12px;}
#lbGiveawayModal .gw-prizeName{font-size:15px;}
#lbGiveawayModal .gw-prizeDesc{font-size:12px;margin-top:4px;}
#lbGiveawayModal .gw-actions{padding-bottom:12px;}
#lbGiveawayModal .gw-btn{padding:12px 14px;}
}
@media (max-width:560px){#lbGiveawayOverlay{padding:calc(12px + env(safe-area-inset-top)) 12px calc(12px + env(safe-area-inset-bottom));align-items:flex-start;}
#lbGiveawayModal{max-width:100%;margin-top:8px;}
#lbGiveawayModal .gw-countdown{grid-template-columns:repeat(2,1fr);}
#lbGiveawayModal .gw-stats{grid-template-columns:1fr;}
#lbGiveawayModal .gw-actions{flex-direction:column;}
#lbGiveawayModal .gw-btn{width:100%;min-width:0;}
}
@media (min-width:980px){#lbGiveawayOverlay{align-items:flex-start;padding-top:18px;}
#lbGiveawayModal{max-height:calc(100vh - 36px);overflow:hidden;}
#lbGiveawayModal .gw-hero{display:grid;grid-template-columns:1fr;grid-template-areas:"top" "countdown" "stats";gap:12px;padding:18px 22px 14px;}
#lbGiveawayModal .gw-heroTop{grid-area:top;}
#lbGiveawayModal .gw-countdownWrap{grid-area:countdown;align-self:start;margin-top:0 !important;padding:12px 14px;border-radius:18px;}
#lbGiveawayModal .gw-countdownLabel{margin-bottom:10px;}
#lbGiveawayModal .gw-countdown{gap:12px;}
#lbGiveawayModal .gw-timebox{padding:12px 10px 11px;border-radius:16px;}
#lbGiveawayModal .gw-timeNum{font-size:30px;}
#lbGiveawayModal .gw-timeUnit{font-size:12px;}
#lbGiveawayModal .gw-stats{grid-area:stats;margin-top:0 !important;grid-template-columns:repeat(3,minmax(0,1fr)) !important;gap:12px !important;align-content:start;}
#lbGiveawayModal .gw-stat{padding:12px !important;border-radius:16px !important;}
#lbGiveawayModal .gw-statK{font-size:12px !important;}
#lbGiveawayModal .gw-statV{font-size:15px !important;}
#lbGiveawayModal .gw-statV small{display:block;font-size:12px !important;opacity:.78;margin-top:2px;}
#lbGiveawayModal .gw-body{gap:12px !important;padding:12px 18px 0 !important;}
#lbGiveawayModal .gw-sectionTitle{margin-bottom:10px !important;}
#lbGiveawayModal .gw-steps{display:grid !important;grid-template-columns:repeat(3,minmax(0,1fr)) !important;gap:10px !important;}
#lbGiveawayModal .gw-step{padding:12px !important;border-radius:16px !important;font-size:13px !important;min-height:64px;}
#lbGiveawayModal .gw-stepText{display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden;}
#lbGiveawayModal .gw-prizes{padding:12px !important;border-radius:18px !important;}
#lbGiveawayModal .gw-prize{padding:12px !important;border-radius:16px !important;}
#lbGiveawayModal .gw-prizeName{font-size:16px !important;}
#lbGiveawayModal .gw-actions{position:static !important;margin-top:12px !important;padding:12px 22px 16px !important;background:none !important;}
}
@media (min-width:980px) and (max-height:760px){#lbGiveawayModal{transform:scale(.94);transform-origin:top center;}
}
@media (min-width:980px) and (max-height:680px){#lbGiveawayModal{transform:scale(.90);transform-origin:top center;}
}
.mega-dropdown.gmBoostDropdownV2{overflow:visible;}
.mega-dropdown.gmBoostDropdownV2{overflow:visible !important;overflow-x:hidden !important;max-height:none !important;height:auto !important;}
.mega-dropdown.gmBoostDropdownV2 *{max-height:none;}
.mega-dropdown.gmBoostDropdownV2::-webkit-scrollbar{width:0;height:0;}
.mega-dropdown.gmBoostDropdownV2 .gmBoostWrap{display:grid;grid-template-columns:340px 1fr;gap:0;gap:18px;align-items:start;}
.mega-dropdown.gmBoostDropdownV2 .gmBoostGamesCol{padding:16px 14px;border-radius:18px 0 0 18px;background:rgba(255,255,255,0.025);border-right:1px solid rgba(255,255,255,0.07);}
.mega-dropdown.gmBoostDropdownV2 .gmBoostGameItem{display:flex;align-items:center;gap:12px;padding:12px 14px;border-radius:16px;text-decoration:none;color:rgba(255,255,255,.92);background:rgba(255,255,255,.03);border:1px solid rgba(255,255,255,.05);margin:10px 6px;}
.mega-dropdown.gmBoostDropdownV2 .gmBoostGameItem:hover{background:rgba(255,255,255,.05);}
.mega-dropdown.gmBoostDropdownV2 .gmBoostGameItem.is-active{background:rgba(124,107,255,.18);border-color:rgba(124,107,255,.35);box-shadow:0 10px 26px rgba(0,0,0,.35);}
.mega-dropdown.gmBoostDropdownV2 .gmBoostGameIcon{width:26px;height:26px;border-radius:12px;display:inline-flex;align-items:center;justify-content:center;background:rgba(0,0,0,.20);border:1px solid rgba(255,255,255,.10);overflow:hidden;flex-shrink:0;}
.mega-dropdown.gmBoostDropdownV2 .gmBoostGameIcon img{width:100%;height:100%;object-fit:cover;display:block;}
.mega-dropdown.gmBoostDropdownV2 .gmBoostGameLabel{font-weight:800;font-size:14px;}
.mega-dropdown.gmBoostDropdownV2 .gmBoostServicesCol{padding:16px 18px;border-radius:0 18px 18px 0;background:rgba(255,255,255,0.012);}
.mega-dropdown.gmBoostDropdownV2 .gmBoostRightHead{display:flex;flex-direction:column;gap:4px;padding:6px 6px 10px;}
.mega-dropdown.gmBoostDropdownV2 .gmBoostRightHead span{font-weight:900;font-size:14px;color:rgba(255,255,255,.92);}
.mega-dropdown.gmBoostDropdownV2 .gmBoostRightHead small{font-size:12px;color:rgba(255,255,255,.55);}
.mega-dropdown.gmBoostDropdownV2 .gmBoostServicesGrid{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:12px;padding:6px;}
.mega-dropdown.gmBoostDropdownV2 .mega-pill{width:100%;border-radius:16px !important;}
.mega-dropdown.gmBoostDropdownV2 .gmBoostServicesGrid .mega-pill{margin:0 !important;}
@media (max-width:1100px){.mega-dropdown.gmBoostDropdownV2 .gmBoostServicesGrid{grid-template-columns:repeat(2,minmax(0,1fr));}
}
@media (max-width:860px){.mega-dropdown.gmBoostDropdownV2 .gmBoostWrap{grid-template-columns:1fr;}
.mega-dropdown.gmBoostDropdownV2 .gmBoostServicesGrid{grid-template-columns:repeat(2,minmax(0,1fr));}
}
@media (max-width:520px){.mega-dropdown.gmBoostDropdownV2 .gmBoostServicesGrid{grid-template-columns:1fr;}
}
@media (min-width:992px){.mega-dropdown.gmBoostDropdownV2{width:1180px;max-width:calc(100vw - 80px);}
}</style>

<div class="lb-sale-banner" id="lbGiveawayBanner" role="region" aria-label="Giveaway"
     data-ends-at="<?= htmlspecialchars($gwEnds, ENT_QUOTES, 'UTF-8') ?>"
     data-title="<?= htmlspecialchars($gwTitle, ENT_QUOTES, 'UTF-8') ?>"
     data-winners="<?= (int)$gwWinners ?>">
  <div class="lb-sale-bar" id="lbGiveawayBar">

    <div class="lb-sale-mobileRow" aria-label="Giveaway announcement">
      <div class="lb-sale-mobileLeft">
        <div class="lb-sale-mobileSave">GIVE<span class="accent">AWAY</span></div>
      </div>

      <div class="lb-sale-mobileMid">
        <div class="lb-sale-mobileLabel">ENDS IN</div>
        <div class="lb-sale-mobileTimer" aria-live="polite">
          <span class="lb-sale-iconPill lb-sale-mobileTimerIcon" aria-hidden="true">⏳</span>
          <span class="lb-sale-mobileTimerValue" id="lbGwCountdownMobile">…</span>
        </div>
      </div>
      <button class="lb-sale-close" type="button" aria-label="Close giveaway banner" id="lbGwClose">✕</button>
    </div>

    <div class="lb-sale-desktopRow" aria-label="Giveaway announcement">
      <div class="lb-sale-desktopMain">
        <div class="lb-sale-desktopLeft">
          <div class="lb-sale-desktopSave">GIVE<span class="accent">AWAY</span></div>
          <div class="lb-sale-desktopSub"><?= htmlspecialchars($gwTitle, ENT_QUOTES, 'UTF-8') ?></div>
        </div>

        <span class="lb-sale-iconPill lb-sale-sepIcon" aria-hidden="true">🎁</span>

        <div class="lb-sale-desktopCenter">
          <div class="lb-sale-desktopLabel">ENDS IN</div>
          <div class="lb-sale-desktopTimer" aria-live="polite">
            <span class="lb-sale-iconPill lb-sale-deskIcon" aria-hidden="true">⏳</span>
            <span class="lb-sale-deskBox"><span class="lb-sale-deskNum" id="lbGwDays">00</span><span class="lb-sale-deskUnit">d</span></span>
            <span class="lb-sale-deskBox"><span class="lb-sale-deskNum" id="lbGwHours">00</span><span class="lb-sale-deskUnit">h</span></span>
            <span class="lb-sale-deskBox"><span class="lb-sale-deskNum" id="lbGwMins">00</span><span class="lb-sale-deskUnit">m</span></span>
            <span class="lb-sale-deskBox"><span class="lb-sale-deskNum" id="lbGwSecs">00</span><span class="lb-sale-deskUnit">s</span></span>
          </div>

          <button class="lb-sale-cta" type="button" id="lbGwOpen">VIEW DETAILS</button>
        </div>
      </div>

      <button class="lb-sale-close" type="button" aria-label="Close giveaway banner" id="lbGwCloseDesk">✕</button>
    </div>

  </div>
</div>

<div id="lbGiveawayOverlay" aria-hidden="true">
  <div id="lbGiveawayModal" role="dialog" aria-modal="true" aria-label="Giveaway details">
    <div class="gw-hero">
      <div class="gw-heroTop">
        <div>
          <div class="gw-badges">
            <span class="gw-badge">🎁 Giveaway</span>
            <span class="gw-badge">🏆 <?= (int)$gwWinners ?> Winner<?= ((int)$gwWinners === 1 ? '' : 's') ?></span>
            <span class="gw-badge muted">1 ticket per paid order</span>
          </div>

          <div class="gw-title" id="lbGwModalTitle"><?= htmlspecialchars($gwTitle, ENT_QUOTES, 'UTF-8') ?></div>
          <div class="gw-sub">Collect tickets by placing <b>PAID</b> orders. More tickets = higher chance to win.</div>
        </div>

        <button class="gw-x" type="button" id="lbGwModalClose" aria-label="Close">✕</button>
      </div>

      <div class="gw-countdownWrap">
        <div class="gw-countdownLabel">
          <span>Ends in</span>
          <span id="lbGwModalCountdown" style="display:none;">…</span>
        </div>

        <div class="gw-countdown" aria-label="Time remaining">
          <div class="gw-timebox">
            <span class="gw-timeNum" id="lbGwModalDays">00</span>
            <span class="gw-timeUnit">Days</span>
          </div>
          <div class="gw-timebox">
            <span class="gw-timeNum" id="lbGwModalHours">00</span>
            <span class="gw-timeUnit">Hours</span>
          </div>
          <div class="gw-timebox">
            <span class="gw-timeNum" id="lbGwModalMins">00</span>
            <span class="gw-timeUnit">Mins</span>
          </div>
          <div class="gw-timebox">
            <span class="gw-timeNum" id="lbGwModalSecs">00</span>
            <span class="gw-timeUnit">Secs</span>
          </div>
        </div>
      </div>

      <div class="gw-stats">
        <div class="gw-stat">
          <div class="gw-statK">🏆 Winners</div>
          <div class="gw-statV"><?= (int)$gwWinners ?></div>
        </div>

        <div class="gw-stat">
          <div class="gw-statK">🎟️ Your tickets</div>
          <?php if ($isLoggedIn): ?>
            <div class="gw-statV">Check in Profile<small>/profile/giveaway</small></div>
          <?php else: ?>
            <div class="gw-statV">Login required<small>Log in to view your tickets</small></div>
          <?php endif; ?>
        </div>

        <div class="gw-stat">
          <div class="gw-statK">⚖️ Odds</div>
          <div class="gw-statV">Weighted by tickets<small>More tickets = higher chance</small></div>
        </div>
      </div>
    </div>

    <div class="gw-body">
      <div class="gw-section">
      <div class="gw-sectionTitle">How it works</div>
      <ul class="gw-steps">
        <li class="gw-step">
          <span class="gw-stepIcon">✅</span>
          <div>Get <b>1 ticket</b> per <b>PAID</b> order (Stripe/Coins).</div>
        </li>
        <li class="gw-step">
          <span class="gw-stepIcon">↩️</span>
          <div>If an order becomes <b>UNPAID</b> or <b>REFUNDED</b>, the ticket is removed.</div>
        </li>
        <li class="gw-step">
          <span class="gw-stepIcon">🎯</span>
          <div><b>More tickets</b> = higher chance to win.</div>
        </li>
      </ul>
    </div>

    <div class="gw-section">
      <div class="gw-sectionTitle">Prizes</div>
      <div class="gw-prizes">
        <?php if (!empty($gwPrizes)): ?>
          <?php foreach ($gwPrizes as $p): ?>
            <?php
              $pos = (int)($p['position'] ?? 0);
              $pName = (string)($p['name'] ?? '');
              $pDesc = (string)($p['description'] ?? '');

              $medal = '#'.$pos;
              if ($pos === 1) $medal = '🥇';
              elseif ($pos === 2) $medal = '🥈';
              elseif ($pos === 3) $medal = '🥉';
            ?>
            <div class="gw-prize" data-rank="<?= $pos ?>">
              <div class="gw-prizeLeft">
                <div class="gw-prizeRank"><?= $medal ?> <span>#<?= $pos ?> Prize</span></div>
                <div class="gw-prizeName"><?= htmlspecialchars($pName, ENT_QUOTES, 'UTF-8') ?></div>
                <?php if ($pDesc !== ''): ?>
                  <div class="gw-prizeDesc"><?= htmlspecialchars($pDesc, ENT_QUOTES, 'UTF-8') ?></div>
                <?php endif; ?>
              </div>
            </div>
          <?php endforeach; ?>
        <?php else: ?>
          <div class="gw-prize">
            <div class="gw-prizeLeft">
              <div class="gw-prizeName">Prizes will be announced soon.</div>
            </div>
          </div>
        <?php endif; ?>
      </div>
    </div>

    </div>

    <div class="gw-actions">
      <a class="gw-btn primary" href="/giveaway">Open Giveaway Page</a>
      <?php if ($isLoggedIn): ?>
        <a class="gw-btn" href="/profile/giveaway">My Tickets</a>
      <?php endif; ?>
  <div class="gmTrustItem">
    <span class="gmTrustIcon" aria-hidden="true"><i class="fas fa-headset"></i></span>
    <span class="gmTrustText">24/7 Support</span>
  </div>
  <div class="gmTrustItem">
    <span class="gmTrustIcon" aria-hidden="true"><i class="fas fa-shield-halved"></i></span>
    <span class="gmTrustText">Secure Boosting</span>
  </div>
  <a class="gmTrustItem gmTrustItem--tp" href="<?= htmlspecialchars($trustpilotUrl, ENT_QUOTES, 'UTF-8') ?>" target="_blank" rel="noopener" aria-label="Trustpilot reviews">
    <span class="tpBadge">
      <span class="tpBadge__excellent">Excellent</span>
      <span class="tpBadge__stars" aria-hidden="true">
        <i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i>
      </span>
      <span class="tpBadge__reviews">Reviews on</span>
      <span class="gmTrustIcon tpBadge__tpIcon" aria-hidden="true"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M12 2l2.4 7.4H22l-6.2 4.5 2.4 7.4L12 17l-6.2 4.3 2.4-7.4L2 9.4h7.6L12 2z" fill="#fff"/></svg></span>
    </span>
  </a>
</div>
    </div>
  </div>
</div>

<script>
(function(){
  // IMPORTANT: this script must run AFTER the nav is in the DOM.
  // The Sale banner script sits later in the file, so it worked.
  // The Giveaway banner markup is earlier, so we defer init until DOM is ready.

  function init(){
    var banner = document.getElementById('lbGiveawayBanner');
    if(!banner) return;
    if (window.getComputedStyle(banner).display === 'none') return;

  // ---------- Keep giveaway banner ABOVE fixed headers (same behavior as Sale) ----------
  function headerCandidates(){
    var sel = 'nav.navbar-top, nav.navbar-mobile, .navbar-top, .navbar-mobile';
    var els = Array.prototype.slice.call(document.querySelectorAll(sel));
    var out = [];
    for (var i=0;i<els.length;i++){
      if (out.indexOf(els[i]) === -1) out.push(els[i]);
    }
    return out;
  }

  function applyHeaderOffset(forceOn){
    var h = banner.offsetHeight || 0;
    var els = headerCandidates();
    for (var i=0;i<els.length;i++){
      var el = els[i];
      var cs = window.getComputedStyle ? getComputedStyle(el) : el.currentStyle;
      var pos = cs && cs.position ? cs.position : '';
      if (pos !== 'fixed') continue;

      if (el.dataset && el.dataset.lbInitTop == null) el.dataset.lbInitTop = (cs.top && cs.top !== 'auto') ? cs.top : '0px';
      var base = parseFloat(el.dataset ? el.dataset.lbInitTop : '0');
      var baseTop = isNaN(base) ? 0 : base;

      if (forceOn) el.style.top = (baseTop + h) + 'px';
      else el.style.top = (el.dataset && el.dataset.lbInitTop) ? el.dataset.lbInitTop : '0px';
    }
  }

  function updateHeaderOffsetOnScroll(){
    var h = banner.offsetHeight || 0;
    var y = (typeof window.pageYOffset === 'number') ? window.pageYOffset : (document.documentElement.scrollTop || document.body.scrollTop || 0);
    var on = y < (h - 1);
    applyHeaderOffset(on);
  }

    updateHeaderOffsetOnScroll();
    try { window.addEventListener('resize', updateHeaderOffsetOnScroll, false); } catch(e){}
    try { window.addEventListener('scroll', updateHeaderOffsetOnScroll, false); } catch(e){}

  var overlay = document.getElementById('lbGiveawayOverlay');
  var modal = document.getElementById('lbGiveawayModal');

  var btnOpen = document.getElementById('lbGwOpen');
  var btnCloseM = document.getElementById('lbGwClose');
  var btnCloseD = document.getElementById('lbGwCloseDesk');
  var btnModalClose = document.getElementById('lbGwModalClose');

  function parseEnds(raw){
    if(!raw) return null;
    // Accept MySQL "YYYY-MM-DD HH:MM:SS" -> ISO
    if(raw.indexOf('T') === -1 && raw.indexOf(' ') !== -1){ raw = raw.replace(' ', 'T'); }
    var d = new Date(raw);
    if(isNaN(d.getTime())) return null;
    return d;
  }
  var endsAt = parseEnds(banner.getAttribute('data-ends-at') || '');

  function pad2(n){ return String(n).padStart(2,'0'); }
  function fmt(ms){
    if(ms <= 0) return 'Ended';
    var s = Math.floor(ms/1000);
    var d = Math.floor(s/86400); s -= d*86400;
    var h = Math.floor(s/3600);  s -= h*3600;
    var m = Math.floor(s/60);    s -= m*60;
    return d+'d '+pad2(h)+'h '+pad2(m)+'m '+pad2(s)+'s';
  }

  var mobile = document.getElementById('lbGwCountdownMobile');
  var dEl = document.getElementById('lbGwDays');
  var hEl = document.getElementById('lbGwHours');
  var mEl = document.getElementById('lbGwMins');
  var sEl = document.getElementById('lbGwSecs');
  var modalCountdown = document.getElementById('lbGwModalCountdown');
  var mdEl = document.getElementById('lbGwModalDays');
  var mhEl = document.getElementById('lbGwModalHours');
  var mmEl = document.getElementById('lbGwModalMins');
  var msEl = document.getElementById('lbGwModalSecs');

  function tick(){
    if(!endsAt){
      if(mobile) mobile.textContent = '—';
      if(modalCountdown) modalCountdown.textContent = '—';
      if(mdEl) mdEl.textContent = '—';
      if(mhEl) mhEl.textContent = '—';
      if(mmEl) mmEl.textContent = '—';
      if(msEl) msEl.textContent = '—';
      return;
    }
    var now = new Date();
    var ms = endsAt.getTime() - now.getTime();

    if(mobile) mobile.textContent = fmt(ms);
    if(modalCountdown) modalCountdown.textContent = fmt(ms);

    var total = Math.max(0, Math.floor(ms/1000));
    var days = Math.floor(total/86400);
    var rem = total - days*86400;
    var hrs = Math.floor(rem/3600); rem -= hrs*3600;
    var mins = Math.floor(rem/60);  rem -= mins*60;
    var secs = rem;

    if(dEl) dEl.textContent = String(days);
    if(hEl) hEl.textContent = pad2(hrs);
    if(mEl) mEl.textContent = pad2(mins);
    if(sEl) sEl.textContent = pad2(secs);
    if(mdEl) mdEl.textContent = String(days);
    if(mhEl) mhEl.textContent = pad2(hrs);
    if(mmEl) mmEl.textContent = pad2(mins);
    if(msEl) msEl.textContent = pad2(secs);
  }
  tick();
  try { setInterval(tick, 1000); } catch(e){}

  var __lbScrollY = 0;
  function setGwOpen(isOpen){
    document.body.classList.toggle('gw-open', !!isOpen);
    try{
      if(window.Tawk_API){
        if(isOpen && typeof window.Tawk_API.hideWidget === 'function') window.Tawk_API.hideWidget();
        if(!isOpen && typeof window.Tawk_API.showWidget === 'function') window.Tawk_API.showWidget();
      }
    }catch(e){}
  }
  function openModal(){
    if(!overlay) return;
    overlay.classList.add('is-visible');
    overlay.setAttribute('aria-hidden','false');
    setGwOpen(true);

    // Lock background scroll (works reliably on iPhone Safari)
    __lbScrollY = window.scrollY || window.pageYOffset || 0;
    document.body.style.position = 'fixed';
    document.body.style.top = (-__lbScrollY) + 'px';
    document.body.style.left = '0';
    document.body.style.right = '0';
    document.body.style.width = '100%';

    // Ensure modal starts at top
    if(modal) modal.scrollTop = 0;
    try {
      if(btnModalClose) setTimeout(function(){ btnModalClose.focus(); }, 20);
    } catch(e){}
  }
  function closeModal(){
    if(!overlay) return;
    overlay.classList.remove('is-visible');
    overlay.setAttribute('aria-hidden','true');
    setGwOpen(false);

    // Restore background scroll
    document.body.style.position = '';
    document.body.style.top = '';
    document.body.style.left = '';
    document.body.style.right = '';
    document.body.style.width = '';
    window.scrollTo(0, __lbScrollY);
  }

  // open on banner click (except close buttons)
  banner.addEventListener('click', function(e){
    var t = e.target;
    if(!t) return;
    if(t.id === 'lbGwClose' || t.id === 'lbGwCloseDesk') return;
    openModal();
  });

  if(btnOpen) btnOpen.addEventListener('click', function(e){ e.stopPropagation(); openModal(); });

  function hideBanner(){
    banner.style.display = 'none';
    try { localStorage.setItem('lb_hide_gw_banner', '1'); } catch(e){}
    // reset fixed header position when banner is hidden
    applyHeaderOffset(false);
  }
  try {
    if(localStorage.getItem('lb_hide_gw_banner') === '1') hideBanner();
  } catch(e){}

  if(btnCloseM) btnCloseM.addEventListener('click', function(e){ e.stopPropagation(); hideBanner(); });
  if(btnCloseD) btnCloseD.addEventListener('click', function(e){ e.stopPropagation(); hideBanner(); });

  if(btnModalClose) btnModalClose.addEventListener('click', closeModal);

  // Reset body scroll-lock on navigation (fixes iOS bfcache scroll issue)
  window.addEventListener('pagehide', function() {
    document.body.style.position = '';
    document.body.style.top = '';
    document.body.style.left = '';
    document.body.style.right = '';
    document.body.style.width = '';
  });
  window.addEventListener('pageshow', function(e) {
    // bfcache restore: always scroll to top
    if (e.persisted) {
      document.body.style.position = '';
      document.body.style.top = '';
      window.scrollTo(0, 0);
    }
  });
  if(overlay) overlay.addEventListener('click', function(e){
    if(e.target === overlay) closeModal();
  });

    document.addEventListener('keydown', function(e){
      if(e.key === 'Escape') closeModal();
    });
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', function(){
      init();
      // Safety: some themes swap/rehydrate headers after DOMContentLoaded.
      // Re-apply once after a short delay.
      setTimeout(init, 250);
    });
  } else {
    init();
    setTimeout(init, 250);
  }
})();
</script>
<?php endif; ?>

<?php
// ── Load all banner settings from DB (one cached query) ───────────────────
if (!function_exists('_lb_banner_settings')) {
    function _lb_banner_settings(): array {
        global $db;
        static $cfg = null;
        if ($cfg !== null) return $cfg;
        $cfg = [];
        try {
            $rows = $db->run("SELECT `key`, `value` FROM `site_settings` WHERE `key` LIKE 'banner_%'") ?: [];
            foreach ($rows as $r) { $cfg[$r['key']] = (string)$r['value']; }
        } catch (\Throwable $e) {}
        return $cfg;
    }
}
function _bv(string $key, string $default = ''): string {
    $cfg = _lb_banner_settings();
    return array_key_exists($key, $cfg) ? $cfg[$key] : $default;
}
function _bt(string $key, string $default = ''): string {
    return t(_bv($key, $default));
}

$lb_type   = _bv('banner_type', 'progames');
$lb_ls_ver = _bv('banner_ls_version', '12');
$lb_ls_key = 'lb_season_banner_closed_v' . $lb_ls_ver;

$lb_pg = [
    'badge_text'   => _bt('banner_pg_badge_text',         'Just Launched'),
    'badge_color'  => _bv('banner_pg_badge_color',        '#8ea5ff'),
    'badge_border' => _bv('banner_pg_badge_border',       'rgba(142,165,255,0.34)'),
    'icon_url'     => _bv('banner_pg_icon_url',           ''),
    'title'        => _bt('banner_pg_title',              '{ACCENT}Every Game, Every Deal{/ACCENT} — All In One Place'),
    'title_color'  => _bv('banner_pg_title_color',        '#ffffff'),
    'accent_color' => _bv('banner_pg_accent_color',       '#8ea5ff'),
    'bg_from'      => _bv('banner_pg_bg_color_from',      '#0d1021'),
    'bg_to'        => _bv('banner_pg_bg_color_to',        '#171c3d'),
    'bg_image'     => _bv('banner_pg_bg_image',           ''),
    'bg_opacity'   => _bv('banner_pg_bg_image_opacity',   '0.62'),
    'pill_1'       => _bt('banner_pg_pill_1',             '50+ Games'),
    'pill_2'       => _bt('banner_pg_pill_2',             'Instant Delivery'),
    'pill_3'       => _bt('banner_pg_pill_3',             '24/7 Support'),
    'pill_4'       => _bt('banner_pg_pill_4',             ''),
    'pill_5'       => _bt('banner_pg_pill_5',             ''),
    'cta_text'     => _bt('banner_pg_cta_text',           'Explore Now'),
    'cta_link'     => _bv('banner_pg_cta_link',           '/'),
    'cta_bg_from'  => _bv('banner_pg_cta_bg_from',        '#7c83ff'),
    'cta_bg_to'    => _bv('banner_pg_cta_bg_to',          '#4f46e5'),
    'cta_color'    => _bv('banner_pg_cta_color',          '#ffffff'),
    'mob_title'    => _bt('banner_pg_mob_title',          'Just Launched'),
    'mob_sub'      => _bt('banner_pg_mob_sub',            'Every game, every deal — explore now'),
    'mob_title_c'  => _bv('banner_pg_mob_title_color',    '#ffffff'),
    'mob_sub_c'    => _bv('banner_pg_mob_sub_color',      'rgba(255,255,255,0.45)'),
    'mob_cta_text' => _bt('banner_pg_mob_cta_text',       'Explore Now'),
    'mob_cta_link' => _bv('banner_pg_mob_cta_link',       '/'),
];

$lb_sale = [
    'headline'     => _bt('banner_sale_headline',         'NEW SEASON {ACCENT}SALE{/ACCENT}'),
    'title_color'  => _bv('banner_sale_title_color',      '#ffffff'),
    'accent_from'  => _bv('banner_sale_accent_from',      '#f59e0b'),
    'accent_to'    => _bv('banner_sale_accent_to',        '#ef4444'),
    'sub'          => _bt('banner_sale_sub',              ''),
    'sub_color'    => _bv('banner_sale_sub_color',        'rgba(255,255,255,0.45)'),
    'bg_from'      => _bv('banner_sale_bg_color_from',    '#0b0f1e'),
    'bg_to'        => _bv('banner_sale_bg_color_to',      '#0b0f1e'),
    'bg_img_l'     => _bv('banner_sale_bg_image_left',    ''),
    'bg_img_r'     => _bv('banner_sale_bg_image_right',   ''),
    'bg_opacity'   => _bv('banner_sale_bg_image_opacity', '0.50'),
    'bg_l_x_d'      => _bv('banner_sale_bg_left_pos_x_desktop',  '50'),
    'bg_l_y_d'      => _bv('banner_sale_bg_left_pos_y_desktop',  '50'),
    'bg_r_x_d'      => _bv('banner_sale_bg_right_pos_x_desktop', '50'),
    'bg_r_y_d'      => _bv('banner_sale_bg_right_pos_y_desktop', '50'),
    'bg_l_x_m'      => _bv('banner_sale_bg_left_pos_x_mobile',   '50'),
    'bg_l_y_m'      => _bv('banner_sale_bg_left_pos_y_mobile',   '50'),
    'bg_r_x_m'      => _bv('banner_sale_bg_right_pos_x_mobile',  '50'),
    'bg_r_y_m'      => _bv('banner_sale_bg_right_pos_y_mobile',  '50'),
    'ends_at'      => _bv('banner_sale_ends_at',          ''),
    'cd_label'     => _bt('banner_sale_countdown_label',  'ENDS IN'),
    'cd_color'     => _bv('banner_sale_countdown_color',  '#ffffff'),
    'cd_unit_bg'   => _bv('banner_sale_countdown_unit_bg','rgba(245,158,11,0.08)'),
    'cd_border'    => _bv('banner_sale_countdown_border', 'rgba(245,158,11,0.30)'),
    'cta_1_text'   => _bt('banner_sale_cta_1_text',       'TFT Boost'),
    'cta_1_link'   => _bv('banner_sale_cta_1_link',       '/tft/rank-boost'),
    'cta_1_icon'   => _bv('banner_sale_cta_1_icon',       ''),
    'cta_1_from'   => _bv('banner_sale_cta_1_bg_from',    '#d97706'),
    'cta_1_to'     => _bv('banner_sale_cta_1_bg_to',      '#b45309'),
    'cta_2_text'   => _bt('banner_sale_cta_2_text',       'Val Boost'),
    'cta_2_link'   => _bv('banner_sale_cta_2_link',       '/val/rank-boost'),
    'cta_2_icon'   => _bv('banner_sale_cta_2_icon',       ''),
    'cta_2_from'   => _bv('banner_sale_cta_2_bg_from',    '#dc2626'),
    'cta_2_to'     => _bv('banner_sale_cta_2_bg_to',      '#991b1b'),
    'mob_pct'      => _bt('banner_sale_mob_pct',          '20% OFF'),
    'mob_pct_from' => _bv('banner_sale_mob_pct_color_from','#f59e0b'),
    'mob_pct_to'   => _bv('banner_sale_mob_pct_color_to', '#ef4444'),
    'mob_label'    => _bt('banner_sale_mob_label',        'New Season Sale'),
    'mob_label_c'  => _bv('banner_sale_mob_label_color',  'rgba(255,255,255,0.45)'),
];

function lb_esc(string $s): string { return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); }
function lb_accent_pg(string $raw, string $color): string {
    return preg_replace('/\{ACCENT\}(.*?)\{\/ACCENT\}/s', '<span style="color:'.lb_esc($color).'">$1</span>', lb_esc($raw));
}
function lb_accent_sale(string $raw, string $from, string $to): string {
    $g = 'background:linear-gradient(90deg,'.lb_esc($from).','.lb_esc($to).');-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;text-shadow:none;';
    return preg_replace('/\{ACCENT\}(.*?)\{\/ACCENT\}/s', '<span style="'.$g.'">$1</span>', lb_esc($raw));
}
function lb_sale_icon_html(string $icon, string $class = 'lbSaleCtaIcon'): string {
    $icon = trim($icon);
    if ($icon === '') return '';
    if (preg_match('/^(fa[srbl]?\s|fa-)/', $icon)) {
        if (strpos($icon, 'fa-') === 0) $icon = 'fas ' . $icon;
        return '<i class="' . lb_esc(trim($icon . ' ' . $class)) . '" aria-hidden="true"></i>';
    }
    return '<img class="' . lb_esc($class) . '" src="' . lb_esc($icon) . '" alt="">';
}
$lb_pill_icons = [
    '<svg width="10" height="10" viewBox="0 0 14 14" fill="none" aria-hidden="true"><circle cx="7" cy="7" r="5.5" stroke="currentColor" stroke-width="1.3"/><path d="M5 7l1.5 1.5L9.5 5.5" stroke="currentColor" stroke-width="1.3" stroke-linecap="round" stroke-linejoin="round"/></svg>',
    '<svg width="10" height="10" viewBox="0 0 14 14" fill="none" aria-hidden="true"><path d="M7 2v5l3 2" stroke="currentColor" stroke-width="1.3" stroke-linecap="round" stroke-linejoin="round"/><circle cx="7" cy="7" r="5.5" stroke="currentColor" stroke-width="1.3"/></svg>',
    '<svg width="10" height="10" viewBox="0 0 14 14" fill="none" aria-hidden="true"><path d="M7 1.5l1.5 3 3.5.5-2.5 2.5.6 3.5L7 9.5l-3.1 1.5.6-3.5L2 5l3.5-.5z" stroke="currentColor" stroke-width="1.3" stroke-linejoin="round"/></svg>',
    '<svg width="10" height="10" viewBox="0 0 14 14" fill="none" aria-hidden="true"><rect x="2" y="5" width="10" height="7" rx="2" stroke="currentColor" stroke-width="1.3"/><path d="M5 5V3.5a2 2 0 0 1 4 0V5" stroke="currentColor" stroke-width="1.3" stroke-linecap="round"/></svg>',
    '<svg width="10" height="10" viewBox="0 0 14 14" fill="none" aria-hidden="true"><rect x="1.5" y="4" width="11" height="7.5" rx="2.5" stroke="currentColor" stroke-width="1.3"/><path d="M5 7.5h1.5M7.5 7.5h1.5" stroke="currentColor" stroke-width="1.3" stroke-linecap="round"/></svg>',
];

// Hide the sale/progames banner on account shop pages so the account grid gets full focus.
// Keep this in the header system so navbar offsets and --lb-sale-h stay correct without any F5/reload workaround.
$lbHideBannerOnAccountShop = in_array($lbRequestPath, [
    '/lol/accounts',
    '/val/accounts',
    '/lol/premium-accounts',
    '/val/premium-accounts',
], true);

if ($lbHideBannerOnAccountShop) {
    $lb_type = 'off';
}
?>
<?php if ($lb_type !== 'off'): ?>

<script>(function(){try{var k='<?= $lb_ls_key ?>';if(localStorage.getItem(k)==='1'){document.write('<style>#lbSaleBanner{display:none!important}</style>');}}catch(e){}}());</script>

<div class="lb-sale-banner" id="lbSaleBanner" role="region"
     data-ends-at="<?= lb_esc($lb_sale['ends_at']) ?>"
     data-link="<?= lb_esc($lb_type === 'progames' ? $lb_pg['cta_link'] : $lb_sale['cta_1_link']) ?>"
     aria-label="<?= lb_esc($lb_type === 'progames' ? t('Pro Games') : t('Sale')) ?>">

  <?php $lb_bg_style = $lb_type === 'progames'
      ? 'background:linear-gradient(90deg,'.lb_esc($lb_pg['bg_from']).','.lb_esc($lb_pg['bg_to']).');'
      : 'background:linear-gradient(90deg,'.lb_esc($lb_sale['bg_from']).','.lb_esc($lb_sale['bg_to']).');'; ?>
  <div class="lb-sale-bar <?= $lb_type === 'progames' ? 'lbPG-bar' : 'lbSalePG-bar' ?>" id="lbSaleBar" style="<?= $lb_bg_style ?>">

    <?php if ($lb_type === 'progames'): ?>
    <?php if ($lb_pg['bg_image']): ?>
    <div class="lbPG-bg" aria-hidden="true" style="position:absolute;inset:0;background:url('<?= lb_esc($lb_pg['bg_image']) ?>') center/cover no-repeat;opacity:<?= lb_esc($lb_pg['bg_opacity']) ?>;pointer-events:none;"></div>
    <?php else: ?>
    <div class="lbPG-bg" aria-hidden="true"></div>
    <?php endif; ?>

    <div class="lbPG-visualBackdrop" aria-hidden="true"></div>

    <div class="lbPG-mobile">
      <div class="lbPG-mob-art" aria-hidden="true">
        <img src="<?= ASSET_URL ?>/website/images/icons/league-of-legends.png" alt="">
        <img src="<?= ASSET_URL ?>/website/images/icons/valorant.png" alt="">
      </div>
      <div class="lbPG-mob-text">
        <span class="lbPG-mob-title" style="color:<?= lb_esc($lb_pg['mob_title_c']) ?>;"><?= lb_esc($lb_pg['mob_title']) ?></span>
        <span class="lbPG-mob-sub"   style="color:<?= lb_esc($lb_pg['mob_sub_c']) ?>;"><?= lb_esc($lb_pg['mob_sub']) ?></span>
      </div>
      <a href="<?= lb_esc($lb_pg['mob_cta_link']) ?>" class="lbPG-mob-cta"
         style="background:linear-gradient(135deg,<?= lb_esc($lb_pg['cta_bg_from']) ?>,<?= lb_esc($lb_pg['cta_bg_to']) ?>);color:<?= lb_esc($lb_pg['cta_color']) ?>;">
        <?= lb_esc($lb_pg['mob_cta_text']) ?>
      </a>
      <button class="lbPG-mob-close lb-sale-close" type="button" aria-label="<?= lb_esc(t('Close banner')) ?>" data-lb-close>✕</button>
    </div>

    <div class="lbPG-desktop lb-sale-desktopRow">
      <div class="lbPG-left lbPG-left--balance" aria-hidden="true"></div>

      <div class="lbPG-center">
        <div class="lbPG-title" style="color:<?= lb_esc($lb_pg['title_color']) ?>;"><?= lb_accent_pg($lb_pg['title'], $lb_pg['accent_color']) ?></div>
        <div class="lbPG-pills">
          <?php $lb_pills = array_values(array_filter([$lb_pg['pill_1'],$lb_pg['pill_2'],$lb_pg['pill_3'],$lb_pg['pill_4'],$lb_pg['pill_5']]));
          foreach ($lb_pills as $lb_pi => $lb_pill):
              if ($lb_pi > 0): ?><span class="lbPG-pill-sep"></span><?php endif; ?>
              <span class="lbPG-pill"><?= $lb_pill_icons[$lb_pi % 5] ?><?= lb_esc($lb_pill) ?></span>
          <?php endforeach; ?>
        </div>
      </div>

      <div class="lbPG-right">
        <a class="lbPG-cta lbGG-cta-btn" href="<?= lb_esc($lb_pg['cta_link']) ?>"
           style="background:linear-gradient(135deg,<?= lb_esc($lb_pg['cta_bg_from']) ?>,<?= lb_esc($lb_pg['cta_bg_to']) ?>);color:<?= lb_esc($lb_pg['cta_color']) ?>;">
          <span><?= lb_esc($lb_pg['cta_text']) ?></span>
          <svg width="14" height="14" viewBox="0 0 14 14" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true"><path d="M2.5 7h9M8 3.5l3.5 3.5-3.5 3.5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
        </a>
        <button class="lbPG-close lb-sale-close" type="button" aria-label="<?= lb_esc(t('Close banner')) ?>" data-lb-close>✕</button>
      </div>
    </div>

    <?php elseif ($lb_type === 'sale'): ?>
    <?php if ($lb_sale['bg_img_l']): ?><div class="lbNew-bg-tft" aria-hidden="true" style="background-image:url('<?= lb_esc($lb_sale['bg_img_l']) ?>');background-position:<?= lb_esc($lb_sale['bg_l_x_d']) ?>% <?= lb_esc($lb_sale['bg_l_y_d']) ?>%;opacity:<?= lb_esc($lb_sale['bg_opacity']) ?>;"></div><?php endif; ?>
    <?php if ($lb_sale['bg_img_r']): ?><div class="lbNew-bg-val" aria-hidden="true" style="background-image:url('<?= lb_esc($lb_sale['bg_img_r']) ?>');background-position:<?= lb_esc($lb_sale['bg_r_x_d']) ?>% <?= lb_esc($lb_sale['bg_r_y_d']) ?>%;opacity:<?= lb_esc($lb_sale['bg_opacity']) ?>;"></div><?php endif; ?>
    <style>@media (max-width:639px){#lbSaleBanner .lbNew-bg-tft{background-position:<?= lb_esc($lb_sale['bg_l_x_m']) ?>% <?= lb_esc($lb_sale['bg_l_y_m']) ?>% !important;}
#lbSaleBanner .lbNew-bg-val{background-position:<?= lb_esc($lb_sale['bg_r_x_m']) ?>% <?= lb_esc($lb_sale['bg_r_y_m']) ?>% !important;}
}</style>
    <div class="lbNew-bg-overlay" aria-hidden="true"></div>

    <div class="lb-sale-mobileRow">
      <div class="lbNew-mob-left" style="min-width:0;flex:1;">
        <div class="lbNew-mob-texts" style="min-width:0;">
          <div class="lbNew-mob-pct" style="background:linear-gradient(90deg,<?= lb_esc($lb_sale['mob_pct_from']) ?>,<?= lb_esc($lb_sale['mob_pct_to']) ?>);-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;"><?= lb_esc($lb_sale['mob_pct']) ?></div>
          <?php if ($lb_sale['ends_at']): ?>
          <div class="lbSalePG-mobile-timer">
            <?= lb_esc($lb_sale['cd_label']) ?>
            <strong id="lbMobD">00</strong>D<span class="sep">•</span><strong id="lbMobH">00</strong>H<span class="sep">•</span><strong id="lbMobM">00</strong>M<span class="sep">•</span><strong id="lbMobS">00</strong>S
          </div>
          <?php else: ?>
          <div class="lbNew-mob-label" style="color:<?= lb_esc($lb_sale['mob_label_c']) ?>;"><?= lb_esc($lb_sale['mob_label']) ?></div>
          <?php endif; ?>
        </div>
      </div>
      <?php if ($lb_sale['cta_1_text'] && $lb_sale['cta_1_link']): ?>
      <a class="lbSalePG-mobile-cta" href="<?= lb_esc($lb_sale['cta_1_link']) ?>" style="background:linear-gradient(135deg,<?= lb_esc($lb_sale['cta_1_from']) ?>,<?= lb_esc($lb_sale['cta_1_to']) ?>);">
        <?= lb_sale_icon_html($lb_sale['cta_1_icon']) ?>
        <?= lb_esc($lb_sale['cta_1_text']) ?>
      </a>
      <?php endif; ?>
      <button class="lb-sale-close" type="button" aria-label="<?= lb_esc(t('Close banner')) ?>" data-lb-close>✕</button>
    </div>

    <div class="lbPG-desktop lb-sale-desktopRow lbSalePG-desktop">
      <div class="lbPG-left">
        <div class="lbPG-badge lbSalePG-badge" style="border-color:<?= lb_esc($lb_sale['cd_border']) ?>;">
          <span class="lbPG-badge-ring lbSalePG-badge-ring" style="background:<?= lb_esc($lb_sale['accent_from']) ?>;box-shadow:0 0 0 2px <?= lb_esc($lb_sale['accent_from']) ?>44;"></span>
          <span class="lbPG-badge-text lbSalePG-badge-text" style="color:<?= lb_esc($lb_sale['accent_from']) ?>;">Sale</span>
        </div>
      </div>

      <div class="lbPG-center lbSalePG-center">
        <div class="lbPG-title lbSalePG-title" style="color:<?= lb_esc($lb_sale['title_color']) ?>;"><?= lb_accent_sale($lb_sale['headline'], $lb_sale['accent_from'], $lb_sale['accent_to']) ?></div>
        <?php if ($lb_sale['ends_at']): ?>
        <div class="lbSalePG-countdown-line" style="color:<?= lb_esc($lb_sale['sub_color']) ?>;">
          <span class="lbSalePG-countdown-label"><?= lb_esc($lb_sale['cd_label']) ?></span>
          <span class="lbSalePG-countdown-time">
            <strong id="lbSaleD" style="color:<?= lb_esc($lb_sale['accent_from']) ?>;">00</strong>D
            <span>•</span>
            <strong id="lbSaleH" style="color:<?= lb_esc($lb_sale['accent_from']) ?>;">00</strong>H
            <span>•</span>
            <strong id="lbSaleM" style="color:<?= lb_esc($lb_sale['accent_from']) ?>;">00</strong>M
            <span>•</span>
            <strong id="lbSaleS" style="color:<?= lb_esc($lb_sale['accent_from']) ?>;">00</strong>S
          </span>
        </div>
        <?php endif; ?>
      </div>

      <div class="lbPG-right">
        <?php if ($lb_sale['cta_1_text'] && $lb_sale['cta_1_link']): ?>
        <a class="lbPG-cta lbSalePG-cta" href="<?= lb_esc($lb_sale['cta_1_link']) ?>"
           style="background:linear-gradient(135deg,<?= lb_esc($lb_sale['cta_1_from']) ?>,<?= lb_esc($lb_sale['cta_1_to']) ?>);color:#fff;">
          <?= lb_sale_icon_html($lb_sale['cta_1_icon']) ?>
          <span><?= lb_esc($lb_sale['cta_1_text']) ?></span>
          <svg width="14" height="14" viewBox="0 0 14 14" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true"><path d="M2.5 7h9M8 3.5l3.5 3.5-3.5 3.5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
        </a>
        <?php endif; ?>
        <button class="lbPG-close lb-sale-close" type="button" aria-label="<?= lb_esc(t('Close banner')) ?>" data-lb-close>✕</button>
      </div>
    </div>
    <?php endif; ?>
  </div>
</div>

<script>
(function(){
  var banner = document.getElementById('lbSaleBanner');
  if (!banner) return;
  if (window.getComputedStyle(banner).display === 'none') return;
  var LS_KEY = '<?= $lb_ls_key ?>';
  function lsGet(k){ try{ return localStorage.getItem(k); }catch(e){ return null; } }
  function lsSet(k,v){ try{ localStorage.setItem(k,v); }catch(e){} }
  if (lsGet(LS_KEY) === '1') { banner.style.display = 'none'; return; }
  var root = document.documentElement;
  function setVar(on){
    var h = on && banner ? (banner.offsetHeight||0) : 0;
    root.style.setProperty('--lb-sale-h', h+'px');
    root.style.setProperty('--mobile-banner-offset', h+'px');

    // Push fixed navbars down while the banner is visible, and move them up immediately after close.
    var navs = document.querySelectorAll('nav.navbar-top, nav.navbar-mobile, .navbar-top, .navbar-mobile');
    for (var ni = 0; ni < navs.length; ni++) {
      var nav = navs[ni];
      if (!nav || nav.dataset.lbBannerTopManaged === '1') continue;
      var cs = window.getComputedStyle ? getComputedStyle(nav) : nav.currentStyle;
      if (!cs || cs.position !== 'fixed') continue;
      nav.dataset.lbBannerTopManaged = '1';
      nav.style.top = h + 'px';
    }
    for (var nj = 0; nj < navs.length; nj++) {
      var nav2 = navs[nj];
      if (!nav2 || nav2.dataset.lbBannerTopManaged !== '1') continue;
      nav2.style.top = h + 'px';
    }

    // Let other layout scripts recalculate header/filter offsets right away.
    try { window.dispatchEvent(new CustomEvent('lb:banner-layout-update', { detail: { height: h } })); } catch(e) {}
  }
  setVar(true);
  window.addEventListener('resize', function(){ setVar(true); });
  window.requestAnimationFrame(function(){ setVar(true); });
  setTimeout(function(){ setVar(true); }, 100);
  var bar = document.getElementById('lbSaleBar');
  var mobileLink = banner.getAttribute('data-link') || '/';
  function isMob(){ return window.innerWidth <= 639; }
  if (bar){ bar.onclick = function(e){ if (!isMob()) return; var t = e.target; while(t&&t!==bar){ if(t.getAttribute&&t.getAttribute('data-lb-close')!==null) return; t=t.parentNode; } window.location.href=mobileLink; }; }
  var closeBtns = banner.querySelectorAll('[data-lb-close]');
  for (var i=0; i<closeBtns.length; i++){ closeBtns[i].onclick = function(e){
    e=e||window.event;
    if(e.preventDefault)e.preventDefault();
    if(e.stopPropagation)e.stopPropagation();
    banner.style.display='none';
    lsSet(LS_KEY,'1');
    setVar(false);
    if (document.documentElement.classList) document.documentElement.classList.add('lb-sale-banner-hidden');
    try { window.dispatchEvent(new CustomEvent('lb:banner-layout-update', { detail: { height: 0 } })); } catch(err) {}
    setTimeout(function(){ setVar(false); }, 50);
    setTimeout(function(){ setVar(false); }, 150);
    return false;
  }; }
<?php if ($lb_type === 'sale' && $lb_sale['ends_at']): ?>
  (function(){
    var endsAt = new Date('<?= str_replace(' ', 'T', $lb_sale['ends_at']) ?>');
    if (isNaN(endsAt.getTime())) return;
    function pad(n){ return ('0'+n).slice(-2); }
    function upd(){
      var diff = Math.max(0, Math.floor((endsAt - Date.now())/1000));
      var d=Math.floor(diff/86400); diff-=d*86400; var h=Math.floor(diff/3600); diff-=h*3600; var m=Math.floor(diff/60); diff-=m*60; var s=diff;
      function set(id,v){ var el=document.getElementById(id); if(el) el.textContent=v; }
      set('lbSaleD',String(d)); set('lbMobD',String(d));
      set('lbSaleH',pad(h));    set('lbMobH',pad(h));
      set('lbSaleM',pad(m));    set('lbMobM',pad(m));
      set('lbSaleS',pad(s));    set('lbMobS',pad(s));
    }
    upd(); setInterval(upd, 1000);
  })();
<?php endif; ?>
})();
</script>

<style id="lb-sale-scroll-collapse-css">
/* Mobile: collapse the promo/sale banner (lbPG-mobile etc.) as soon as the user
   scrolls, so the fixed navbar-mobile slides up to the very top. */
#lbSaleBanner{ transition:max-height .3s ease; }
#lbSaleBanner.lb-sale-scroll-hidden{ overflow:hidden !important; }
@media (max-width:639px){
  nav.navbar-mobile, .navbar-mobile{ transition:top .3s ease; }
}
</style>
<script id="lb-sale-scroll-collapse-js">
(function(){
  var banner = document.getElementById('lbSaleBanner');
  if(!banner) return;
  var root = document.documentElement;
  var collapsed = false;

  function isMob(){ return window.innerWidth <= 639; }
  function navs(){ return document.querySelectorAll('nav.navbar-mobile, .navbar-mobile'); }
  function isDismissed(){ return window.getComputedStyle(banner).display === 'none'; }

  function collapse(on){
    if(on === collapsed) return;
    collapsed = on;
    if(on){
      banner.style.maxHeight = banner.offsetHeight + 'px';
      banner.offsetHeight; // force reflow so the transition runs
      banner.classList.add('lb-sale-scroll-hidden');
      banner.style.maxHeight = '0px';
      root.style.setProperty('--lb-sale-h', '0px');
      root.style.setProperty('--mobile-banner-offset', '0px');
      var ns = navs(); for(var i=0;i<ns.length;i++){ ns[i].style.top = '0px'; }
    }else{
      banner.classList.remove('lb-sale-scroll-hidden');
      var h = banner.scrollHeight;
      banner.style.maxHeight = h + 'px';
      root.style.setProperty('--lb-sale-h', h + 'px');
      root.style.setProperty('--mobile-banner-offset', h + 'px');
      var ns = navs(); for(var j=0;j<ns.length;j++){ ns[j].style.top = h + 'px'; }
      setTimeout(function(){ if(!collapsed) banner.style.maxHeight = ''; }, 320);
    }
  }

  function onScroll(){
    if(!isMob()){ if(collapsed) collapse(false); return; }
    if(isDismissed()) return;
    var y = window.pageYOffset || document.documentElement.scrollTop || 0;
    collapse(y > 30);
  }

  window.addEventListener('scroll', onScroll, {passive:true});
  window.addEventListener('resize', onScroll, {passive:true});
  onScroll();
})();
</script>
<?php endif; // $lb_type !== 'off' ?>

<style id="lb-marketplace-search-css">
/* ═══════════════════════════════════════════════════════════════
   LB MARKETPLACE SEARCH — inline field + dropdown (no modal)
   Prefix: .lbms   |  no !important, single source of truth
   ═══════════════════════════════════════════════════════════════ */
.lbms{
  --lbms-bg:#0b1024;
  --lbms-bg-soft:#111832;
  --lbms-line:rgba(148,163,184,.16);
  --lbms-line-strong:rgba(129,160,255,.42);
  --lbms-text:#eef2ff;
  --lbms-muted:rgba(203,213,255,.60);
  --lbms-accent:#6d8bff;
  --lbms-accent-soft:rgba(109,139,255,.16);
  --lbms-gold:#f0b34a;
  --lbms-radius:18px;

  position:relative;
  flex:1 1 640px;
  max-width:760px;
  margin:0 28px;
  z-index:10050;
}

/* ── Field ───────────────────────────────────────────────────── */
.lbms__field{
  display:flex;
  align-items:center;
  gap:12px;
  height:52px;
  padding:0 8px 0 18px;
  border-radius:999px;
  background:linear-gradient(180deg,rgba(255,255,255,.06),rgba(255,255,255,.03));
  border:1px solid var(--lbms-line);
  box-shadow:inset 0 1px 0 rgba(255,255,255,.05);
  transition:border-color .16s ease, box-shadow .16s ease, background .16s ease;
  cursor:text;
}
.lbms__field:hover{ border-color:rgba(148,163,184,.30); }
.lbms.is-open .lbms__field,
.lbms__field:focus-within{
  border-color:var(--lbms-line-strong);
  box-shadow:0 0 0 4px rgba(109,139,255,.14), inset 0 1px 0 rgba(255,255,255,.06);
  background:rgba(11,16,36,.92);
}
.lbms__fieldIcon{ color:rgba(203,213,255,.66); font-size:16px; flex:0 0 auto; }
.lbms__input{
  flex:1 1 auto;
  min-width:0;
  height:100%;
  border:0;
  outline:0;
  background:transparent;
  color:var(--lbms-text);
  font-size:16px;
  font-weight:700;
  letter-spacing:-.01em;
}
.lbms__input::placeholder{ color:rgba(203,213,255,.48); font-weight:600; }
.lbms__clear,
.lbms__sheetClose{
  flex:0 0 auto;
  width:36px; height:36px;
  display:none;
  align-items:center; justify-content:center;
  border:0; border-radius:999px;
  background:rgba(255,255,255,.06);
  color:var(--lbms-muted);
  cursor:pointer;
  transition:background .16s ease,color .16s ease;
}
.lbms__clear:hover,
.lbms__sheetClose:hover{ background:rgba(255,255,255,.12); color:#fff; }
.lbms__clear:not([hidden]){ display:flex; }
.lbms__hint{
  flex:0 0 auto;
  display:grid; place-items:center;
  min-width:26px; height:26px;
  margin-right:8px;
  padding:0 7px;
  border-radius:8px;
  border:1px solid var(--lbms-line);
  background:rgba(255,255,255,.04);
  color:var(--lbms-muted);
  font-size:12px; font-weight:800;
}
.lbms.is-open .lbms__hint{ display:none; }

/* ── Panel ───────────────────────────────────────────────────── */
.lbms__panel{
  position:absolute;
  z-index:20;
  top:calc(100% + 12px);
  left:50%;
  right:auto;
  width:min(1080px,calc(100vw - 48px));
  transform:translateX(-50%);
  display:flex;
  flex-direction:column;
  max-height:min(70vh,620px);
  border-radius:var(--lbms-radius);
  border:1px solid rgba(129,160,255,.22);
  background:linear-gradient(180deg,rgba(13,18,40,.99),rgba(8,12,28,.99));
  box-shadow:0 30px 80px rgba(0,0,0,.60);
  overflow:hidden;
  animation:lbmsIn .14s ease-out;
}
.lbms__panel[hidden]{ display:none; }
@keyframes lbmsIn{ from{ opacity:0; transform:translate(-50%,-6px); } to{ opacity:1; transform:translate(-50%,0); } }

/* Service buttons (top row) */
.lbms__tabs{
  flex:0 0 auto;
  display:grid;
  grid-template-columns:repeat(6,minmax(0,1fr));
  gap:8px;
  padding:14px 14px 12px;
  border-bottom:1px solid rgba(255,255,255,.06);
  background:rgba(255,255,255,.02);
}
.lbms__tab{
  display:flex; align-items:center; justify-content:center; gap:8px;
  min-width:0;
  height:46px;
  padding:0 12px;
  border-radius:13px;
  border:1px solid var(--lbms-line);
  background:rgba(255,255,255,.03);
  color:rgba(226,232,255,.80);
  font-size:13px; font-weight:800;
  cursor:pointer;
  transition:.14s ease;
}
.lbms__tab span{ white-space:nowrap; }
.lbms__tab:hover{ border-color:rgba(129,160,255,.34); color:#fff; }
.lbms__tab.is-active{
  background:var(--lbms-accent-soft);
  border-color:var(--lbms-line-strong);
  color:#fff;
  box-shadow:inset 0 1px 0 rgba(255,255,255,.06);
}
.lbms__tab i{ font-size:13px; opacity:.85; flex:0 0 auto; }
.lbms__tabCount{ font-size:12px; font-weight:800; color:var(--lbms-muted); flex:0 0 auto; }
.lbms__tab.is-active .lbms__tabCount{ color:#cfd9ff; }

/* Toolbar */
.lbms__toolbar{
  flex:0 0 auto;
  display:flex; align-items:center; justify-content:space-between; gap:12px;
  padding:12px 16px 10px;
}
.lbms__count{ font-size:12px; font-weight:800; letter-spacing:.08em; text-transform:uppercase; color:var(--lbms-muted); }
.lbms__sort{ display:flex; gap:6px; }
.lbms__sortBtn{
  height:32px; padding:0 14px;
  border-radius:9px;
  border:1px solid var(--lbms-line);
  background:transparent;
  color:rgba(226,232,255,.72);
  font-size:12px; font-weight:800;
  cursor:pointer;
  transition:.14s ease;
}
.lbms__sortBtn:hover{ color:#fff; border-color:rgba(129,160,255,.32); }
.lbms__sortBtn.is-active{ background:var(--lbms-accent-soft); border-color:var(--lbms-line-strong); color:#fff; }

/* Results */
.lbms__scroll{ flex:1 1 auto; min-height:0; overflow-y:auto; padding:2px 12px 12px; overscroll-behavior:contain; }
.lbms__scroll::-webkit-scrollbar{ width:8px; }
.lbms__scroll::-webkit-scrollbar-thumb{ background:rgba(129,160,255,.34); border-radius:999px; }
.lbms__scroll::-webkit-scrollbar-track{ background:transparent; }

.lbms__grid{
  display:grid;
  grid-template-columns:repeat(auto-fill,minmax(240px,1fr));
  gap:8px;
}
.lbms__groupHeading{
  grid-column:1 / -1;
  display:flex;
  align-items:center;
  gap:9px;
  min-height:28px;
  margin:4px 2px 0;
  color:#aab5df;
  font-size:10px;
  font-weight:900;
  letter-spacing:.11em;
  text-transform:uppercase;
}
.lbms__groupHeading[hidden]{ display:none !important; }
.lbms__groupHeading::after{
  content:"";
  height:1px;
  flex:1;
  background:linear-gradient(90deg,rgba(129,140,248,.25),transparent);
}
.lbms__card{
  position:relative;
  border-radius:14px;
  border:1px solid rgba(148,163,184,.12);
  background:rgba(255,255,255,.025);
  transition:border-color .14s ease, background .14s ease, transform .14s ease;
}
.lbms__card[hidden]{ display:none; }
.lbms__card:hover,
.lbms__card.is-active{
  border-color:var(--lbms-line-strong);
  background:var(--lbms-accent-soft);
}
.lbms__cardMain{
  display:flex; align-items:center; gap:12px;
  padding:11px 12px;
  color:var(--lbms-text);
  text-decoration:none;
  min-height:62px;
}
.lbms__cardIcon{
  flex:0 0 40px;
  width:40px; height:40px;
  display:grid; place-items:center;
  border-radius:12px;
  overflow:hidden;
  background:rgba(255,255,255,.06);
  border:1px solid rgba(255,255,255,.07);
}
.lbms__cardIcon img{ width:100%; height:100%; object-fit:cover; display:block; }
.lbms__cardIcon--flat{ color:#a9bcff; font-size:15px; }
.lbms__cardIcon--product img{ object-fit:contain; padding:3px; }
.lbms__card--pink{
  border-color:rgba(244,114,182,.42);
  background:linear-gradient(135deg,rgba(236,72,153,.16),rgba(168,85,247,.08));
}
.lbms__card--pink:hover,
.lbms__card--pink.is-active{
  border-color:rgba(244,114,182,.78);
  background:linear-gradient(135deg,rgba(236,72,153,.25),rgba(168,85,247,.13));
}
.lbms__card--pink .lbms__cardIcon{
  color:#fff;
  border-color:rgba(251,113,133,.48);
  background:linear-gradient(135deg,#ec4899,#a855f7);
}
.lbms__card--pink .lbms__cardTitle{ color:#f9a8d4; }
.lbms__card--product{
  grid-column:span 2;
  border-color:rgba(0,182,122,.20);
  background:linear-gradient(135deg,rgba(0,182,122,.065),rgba(255,255,255,.025));
}
.lbms__card--product:hover,
.lbms__card--product.is-active{
  border-color:rgba(0,182,122,.48);
  background:linear-gradient(135deg,rgba(0,182,122,.12),rgba(99,102,241,.08));
}
.lbms__card--product .lbms__cardMain{ min-height:72px; }
.lbms__card--product .lbms__cardIcon{
  width:46px;
  height:46px;
  flex:0 0 46px;
  border-radius:12px;
  background:rgba(255,255,255,.08);
}
.lbms__productKind{
  display:block;
  margin-bottom:2px;
  color:#34d399;
  font-size:9px;
  font-weight:900;
  line-height:1;
  letter-spacing:.10em;
  text-transform:uppercase;
}
.lbms__productPrice{
  margin-left:auto;
  padding-left:8px;
  color:#fff;
  font-size:13px;
  font-weight:900;
  white-space:nowrap;
}
.lbms__productGameIcon{
  position:absolute;
  right:8px;
  bottom:6px;
  width:20px;
  height:20px;
  display:grid;
  place-items:center;
  border-radius:6px;
  overflow:hidden;
  background:rgba(7,12,30,.82);
  border:1px solid rgba(255,255,255,.10);
  box-shadow:0 2px 8px rgba(0,0,0,.28);
  pointer-events:none;
}
.lbms__productGameIcon img{ width:100%; height:100%; object-fit:contain; padding:2px; display:block; }
.lbms__cardBody{ display:flex; flex-direction:column; gap:3px; min-width:0; }
.lbms__cardTitle{
  font-size:14px; font-weight:800; line-height:1.2; color:#fff;
  white-space:nowrap; overflow:hidden; text-overflow:ellipsis;
}
.lbms__cardMeta{
  font-size:11.5px; font-weight:600; color:var(--lbms-muted);
  white-space:nowrap; overflow:hidden; text-overflow:ellipsis;
}
.lbms__soon{
  position:absolute; top:9px; right:9px;
  padding:2px 8px;
  border-radius:999px;
  background:rgba(240,179,74,.14);
  border:1px solid rgba(240,179,74,.34);
  color:var(--lbms-gold);
  font-size:10px; font-weight:900; letter-spacing:.04em;
}
.lbms__card.is-soon .lbms__cardIcon{ opacity:.62; }

.lbms__empty{
  display:flex; flex-direction:column; align-items:center; gap:6px;
  padding:44px 16px;
  text-align:center;
  color:var(--lbms-muted);
}
.lbms__empty[hidden]{ display:none; }
.lbms__empty i{ font-size:20px; opacity:.5; margin-bottom:4px; }
.lbms__empty strong{ color:#fff; font-size:15px; font-weight:800; }
.lbms__empty span{ font-size:13px; }

.lbms__foot{
  flex:0 0 auto;
  display:flex; align-items:center; justify-content:center; flex-wrap:wrap; gap:8px 18px;
  padding:11px 16px;
  border-top:1px solid rgba(255,255,255,.05);
  background:rgba(255,255,255,.015);
}
.lbms__trust{
  display:inline-flex; align-items:center; gap:7px;
  color:rgba(203,213,255,.62);
  font-size:12px; font-weight:700;
}
.lbms__trust i{ font-size:11px; color:#8fa6ff; opacity:.9; }
.lbms__tp{
  display:inline-flex; align-items:center; gap:7px;
  padding:5px 10px;
  border-radius:9px;
  border:1px solid rgba(0,182,122,.28);
  background:rgba(0,182,122,.10);
  color:#eaf6f1;
  font-size:12px; font-weight:700;
  text-decoration:none;
  transition:.14s ease;
}
.lbms__tp:hover{ background:rgba(0,182,122,.18); border-color:rgba(0,182,122,.48); }
.lbms__tp b{ font-weight:900; color:#fff; }
.lbms__tpStars{ display:inline-flex; gap:2px; color:#00b67a; font-size:11px; }
.lbms__tpText{ color:rgba(226,240,235,.78); }
.lbms__tpLogo{
  display:grid; place-items:center;
  width:20px; height:20px;
  border-radius:5px;
  background:#00b67a;
  color:#fff;
  font-size:10px;
}

/* Scrim (desktop click-catcher) */
.lbms__scrim{
  position:fixed; inset:0;
  z-index:-1;
  background:rgba(3,6,18,.45);
  opacity:0;
  pointer-events:none;
  transition:opacity .16s ease;
}
.lbms.is-open .lbms__scrim{ opacity:1; }

/* ── Tablet / mobile: field lives in the mobile bar, panel = sheet ── */
@media (max-width:1040px){
  .lbms{
    position:fixed;
    inset:0;
    margin:0;
    max-width:none;
    z-index:2147483000;
    display:none;
    flex-direction:column;
    background:#070b1a;
    padding:10px 10px 0;
  }
  .lbms.is-open{ display:flex; }

  .lbms__field{ flex:0 0 auto; height:50px; border-radius:14px; padding:0 6px 0 14px; }
  .lbms__sheetClose{ display:flex; }
  .lbms__hint{ display:none; }
  .lbms__input{ font-size:15px; }

  .lbms__panel{
    position:static;
    width:auto;
    transform:none;
    margin-top:10px;
    flex:1 1 auto;
    min-height:0;
    max-height:none;
    border:0;
    border-radius:0;
    background:transparent;
    box-shadow:none;
    animation:none;
  }

  /* service buttons: horizontal scroller, never squashed */
  .lbms__tabs{
    flex:0 0 auto;
    display:flex;
    gap:8px;
    padding:0 0 12px;
    overflow-x:auto;
    scrollbar-width:none;
    background:transparent;
    border-bottom:1px solid rgba(255,255,255,.06);
  }
  .lbms__tabs::-webkit-scrollbar{ display:none; }
  .lbms__tab{ flex:0 0 auto; height:42px; padding:0 13px; }

  .lbms__toolbar{ padding:12px 2px 10px; }
  .lbms__scroll{ padding:0 0 12px; }

  .lbms__grid{ grid-template-columns:repeat(2,minmax(0,1fr)); gap:8px; }
  .lbms__cardMain{ padding:10px; gap:10px; min-height:58px; }
  .lbms__cardIcon{ flex:0 0 36px; width:36px; height:36px; border-radius:10px; }
  .lbms__cardTitle{
    white-space:normal;
    display:-webkit-box;
    -webkit-line-clamp:2;
    -webkit-box-orient:vertical;
    overflow:hidden;
    font-size:13px;
    line-height:1.2;
  }
  .lbms__cardMeta{ font-size:11px; }
  .lbms__soon{ top:6px; right:6px; padding:1px 6px; font-size:9px; }

  /* trust bar sticks to the bottom of the sheet */
  .lbms__foot{
    flex:0 0 auto;
    gap:6px 12px;
    padding:10px 8px calc(10px + env(safe-area-inset-bottom));
    margin:0 -10px;
    background:rgba(255,255,255,.02);
  }
  .lbms__trust{ font-size:11px; }
  .lbms__tp{ font-size:11px; padding:4px 8px; }

  .lbms__scrim{ display:none; }
}
html.lbms-locked, body.lbms-locked{ overflow:hidden; }


/* Marketplace mega menu is force-hidden while the search dropdown is open */
.navbar-top .gmUnifiedNav.lbms-hide-mega .gmUnifiedMega,
.navbar-top .gmUnifiedNav.lbms-hide-mega:hover .gmUnifiedMega,
.navbar-top .gmUnifiedNav.lbms-hide-mega:focus-within .gmUnifiedMega,
.navbar-top .gmUnifiedNav.lbms-hide-mega.is-market-open .gmUnifiedMega{
  display:none !important;
  opacity:0 !important;
  visibility:hidden !important;
  pointer-events:none !important;


}
</style>

<?php
$lbHeaderClientCoins = 0.0;
$lbHeaderRewardPoints = 0.0;
$lbHeaderBalanceSource = [];
if (defined('CLIENT_DATA') && is_array(CLIENT_DATA)) {
    $lbHeaderBalanceSource = CLIENT_DATA;
} elseif (defined('BOOSTER_DATA') && is_array(BOOSTER_DATA)) {
    $lbHeaderBalanceSource = BOOSTER_DATA;
}
$lbHeaderClientCoins = (float)($lbHeaderBalanceSource['points'] ?? $lbHeaderBalanceSource['lb_coins'] ?? 0);
$lbHeaderRewardPoints = (float)($lbHeaderBalanceSource['reward_points'] ?? $lbHeaderBalanceSource['rewards'] ?? 0);
try {
    if (defined('CLIENT_ID') && (int)CLIENT_ID > 0 && function_exists('db_get_row')) {
        $lbHeaderFreshClient = db_get_row('clients', [
            'id' => (int)CLIENT_ID,
            'select' => 'points,reward_points'
        ], 1);
        if (is_array($lbHeaderFreshClient)) {
            $lbHeaderClientCoins = (float)($lbHeaderFreshClient['points'] ?? $lbHeaderClientCoins);
            $lbHeaderRewardPoints = (float)($lbHeaderFreshClient['reward_points'] ?? $lbHeaderRewardPoints);
        }
    }
} catch (Throwable $e) {}
$lbHeaderAmount = static function ($value): string {
    $value = (float)$value;
    if (abs($value - round($value)) < 0.00001) return number_format($value, 0, '.', ',');
    return rtrim(rtrim(number_format($value, 2, '.', ','), '0'), '.');
};
?>

<style id="lb-desktop-client-menu-styles">
@media (min-width: 992px){
  .lb-client-tools{display:flex;align-items:center;gap:14px;position:relative;}
  .lb-client-tool,.lb-client-profile{position:relative;}
  .lb-client-icon-btn{width:54px;height:54px;min-width:54px;padding:0;border-radius:18px;border:1px solid rgba(255,255,255,.13);background:linear-gradient(180deg,rgba(255,255,255,.09),rgba(255,255,255,.035));color:#fff;display:inline-flex;align-items:center;justify-content:center;position:relative;cursor:pointer;transition:.16s ease;box-shadow:inset 0 1px 0 rgba(255,255,255,.05),0 10px 28px rgba(0,0,0,.18);}
  .lb-client-icon-btn:hover,.lb-client-tool.is-open .lb-client-icon-btn{background:linear-gradient(180deg,rgba(124,107,255,.24),rgba(124,107,255,.11));border-color:rgba(124,107,255,.48);transform:translateY(-2px);box-shadow:0 14px 34px rgba(0,0,0,.28),0 0 0 1px rgba(124,107,255,.1);}
  .lb-client-icon-btn i{font-size:22px;color:#fff;filter:drop-shadow(0 4px 10px rgba(0,0,0,.28));}
  .lb-client-badge{position:absolute;top:-6px;right:-6px;width:21px;min-width:21px;height:21px;border-radius:999px;background:linear-gradient(180deg,#8b5cf6,#6d4cff);color:#fff;border:2px solid #090817;display:flex;align-items:center;justify-content:center;padding:0;font-size:10px;font-weight:900;line-height:1;box-shadow:0 8px 18px rgba(109,76,255,.35);text-align:center;}

  .lb-client-tool-menu{position:absolute;right:0;top:calc(100% + 12px);width:390px;padding:0;border-radius:22px;background:linear-gradient(180deg,rgba(15,16,28,.99),rgba(7,8,17,.99));border:1px solid rgba(124,107,255,.26);box-shadow:0 28px 90px rgba(0,0,0,.62),inset 0 1px 0 rgba(255,255,255,.05);opacity:0;visibility:hidden;pointer-events:none;transform:translateY(8px);transition:.16s ease;z-index:100450;overflow:hidden;}
  .lb-client-tool.is-open .lb-client-tool-menu{opacity:1;visibility:visible;pointer-events:auto;transform:none;}
  .lb-client-tool-head{min-height:62px;padding:0 18px;display:flex;align-items:center;justify-content:space-between;gap:12px;border-bottom:1px solid rgba(255,255,255,.08);}
  .lb-client-tool-title{display:flex;align-items:center;gap:11px;font-size:17px;font-weight:950;color:#fff;}
  .lb-client-tool-title i{font-size:19px;color:#a5b4fc;}
  .lb-client-tool-link{font-size:13px;font-weight:850;color:rgba(199,210,254,.78);text-decoration:none;}
  .lb-client-tool-link:hover{color:#fff;}
  .lb-client-tool-body{padding:14px;}
  .lb-client-tool-summary{display:flex;align-items:center;gap:13px;padding:14px;border-radius:15px;border:1px solid rgba(255,255,255,.07);background:rgba(255,255,255,.035);}
  .lb-client-tool-summary-icon{width:44px;height:44px;flex:0 0 44px;border-radius:14px;display:grid;place-items:center;background:rgba(99,102,241,.15);border:1px solid rgba(129,140,248,.22);color:#c7d2fe;font-size:18px;}
  .lb-client-tool-summary strong{display:block;font-size:14.5px;color:#fff;font-weight:900;margin-bottom:3px;}
  .lb-client-tool-summary span{display:block;font-size:12.5px;color:rgba(255,255,255,.48);line-height:1.45;}

  .settings-pill{height:52px !important;min-height:52px !important;padding:0 16px !important;border-radius:18px !important;border:1px solid rgba(255,255,255,.13) !important;background:linear-gradient(180deg,rgba(255,255,255,.08),rgba(255,255,255,.035)) !important;box-shadow:inset 0 1px 0 rgba(255,255,255,.05),0 10px 28px rgba(0,0,0,.18) !important;gap:10px !important;min-width:188px !important;justify-content:flex-start !important;}
  .settings-pill:hover{border-color:rgba(124,107,255,.45) !important;background:linear-gradient(180deg,rgba(124,107,255,.20),rgba(124,107,255,.09)) !important;transform:translateY(-2px);}
  .settings-pill-flag{width:24px !important;height:24px !important;border-radius:999px !important;}
  .settings-pill-lang,.settings-pill-cur{font-size:15px !important;font-weight:850 !important;}
  .settings-pill-lang::after{content:" /" !important;margin:0 3px !important;}
  .settings-pill-chevron{font-size:13px !important;margin-left:3px !important;color:#c7d2fe !important;}

  .lb-client-avatar-toggle.lb-client-profile-summary{min-width:246px;height:62px;padding:2px 2px 2px 0;border-radius:0;border:none;background:transparent;color:#fff;display:flex;align-items:center;gap:13px;cursor:pointer;transition:.16s ease;box-shadow:none;}
  .lb-client-avatar-toggle.lb-client-profile-summary:hover,.lb-client-profile.is-open .lb-client-avatar-toggle.lb-client-profile-summary{background:transparent;border:none;transform:translateY(-1px);box-shadow:none;}
  .lb-profile-avatar{width:58px;height:58px;flex:0 0 58px;border-radius:18px;overflow:hidden;display:flex;align-items:center;justify-content:center;background:rgba(8,11,27,.82);border:1.5px solid rgba(118,130,255,.68);box-shadow:0 0 0 4px rgba(99,102,241,.14),0 14px 30px rgba(38,52,144,.26);}
  .lb-profile-avatar img{width:100%;height:100%;object-fit:cover;display:block;}
  .lb-profile-avatar i{font-size:26px;color:#fff;}
  .lb-profile-meta{min-width:0;display:flex;flex-direction:column;align-items:flex-start;justify-content:center;gap:3px;flex:1 1 auto;padding-top:1px;}
  .lb-profile-name{max-width:100%;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;font-size:15.5px;font-weight:900;line-height:1.05;color:#fff;}
  .lb-profile-role{max-width:100%;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;font-size:12.5px;font-weight:800;line-height:1.05;color:rgba(255,255,255,.58);margin-top:1px;}
  .lb-profile-chevron{display:inline-flex;align-items:center;justify-content:center;width:18px;flex:0 0 18px;color:#c7d2fe;font-size:14px;opacity:.92;margin-left:2px;}

  .lb-client-dropdown{position:absolute;right:0;top:calc(100% + 12px);width:355px;padding:14px;border-radius:24px;background:linear-gradient(180deg,rgba(18,18,34,.98),rgba(8,9,20,.98));border:1px solid rgba(124,107,255,.28);box-shadow:0 28px 90px rgba(0,0,0,.62),inset 0 1px 0 rgba(255,255,255,.05);opacity:0;visibility:hidden;pointer-events:none;transform:translateY(8px);transition:.16s ease;z-index:100400;}
  .lb-client-profile.is-open .lb-client-dropdown{opacity:1;visibility:visible;pointer-events:auto;transform:none;}
  .lb-client-dropdown-head{display:flex;align-items:center;gap:14px;padding:8px 8px 16px;border-bottom:1px solid rgba(255,255,255,.08);margin-bottom:10px;}
  .lb-client-dropdown-avatar{width:56px;height:56px;border-radius:18px;overflow:hidden;background:rgba(124,107,255,.18);border:1px solid rgba(124,107,255,.34);display:flex;align-items:center;justify-content:center;flex:0 0 56px;}
  .lb-client-dropdown-avatar img{width:100%;height:100%;object-fit:cover;}
  .lb-client-dropdown-avatar i{font-size:20px;color:#c7d2fe;}
  .lb-client-dropdown-name{font-size:18px;font-weight:900;color:#fff;line-height:1.15;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;}
  .lb-client-dropdown-rank{margin-top:5px;font-size:14px;font-weight:800;color:rgba(255,255,255,.46);}
  .lb-client-dropdown-balances{display:grid;grid-template-columns:1fr;gap:10px;padding:2px 0 12px;}
  .lb-client-balance-row{min-height:50px;padding:0 14px;border-radius:15px;border:1px solid rgba(129,140,248,.16);background:rgba(255,255,255,.035);display:flex;align-items:center;justify-content:space-between;gap:12px;}
  .lb-client-balance-label{display:flex;align-items:center;gap:10px;font-size:14px;font-weight:850;color:rgba(226,232,240,.72);}
  .lb-client-balance-icon{width:32px;height:32px;border-radius:11px;display:flex;align-items:center;justify-content:center;background:rgba(99,102,241,.16);border:1px solid rgba(129,140,248,.20);color:#a5b4fc;font-size:14px;}
  .lb-client-balance-icon.rewards{background:rgba(168,85,247,.13);border-color:rgba(192,132,252,.20);color:#d8b4fe;}
  .lb-client-balance-value{font-size:17px;font-weight:950;color:#fff;white-space:nowrap;}
  /* Booster availability switch */
  /* overflow:hidden on .lb-profile-avatar clips the dot, so it sits above the avatar box */
  .lb-avail-avatar{position:relative;overflow:visible !important;z-index:2;}
  .lb-avail-avatar > img{border-radius:inherit;}
  /* every ancestor up to the trigger clips the dot, so none of them may hide overflow */
  :has(> .lb-avail-avatar),
  :has(> * > .lb-avail-avatar){overflow:visible !important;}
  .lb-avail-avatar-dot{position:absolute;z-index:5;right:-3px;bottom:-3px;width:15px;height:15px;border-radius:50%;background:#94a3b8;border:3px solid #0b1020;box-shadow:0 0 0 1px rgba(148,163,184,.35),0 2px 6px rgba(0,0,0,.5);}
  .lb-avail-avatar[data-status="online"] .lb-avail-avatar-dot{background:#22c55e;box-shadow:0 0 0 2px rgba(34,197,94,.28);}
  .lb-avail-avatar[data-status="away"] .lb-avail-avatar-dot{background:#f59e0b;box-shadow:0 0 0 2px rgba(245,158,11,.28);}
  .lb-avail-block{padding:2px 0 12px;}
  .lb-avail-block.is-busy{opacity:.55;pointer-events:none;}
  .lb-avail-block-head{display:flex;align-items:center;justify-content:space-between;gap:10px;padding:0 2px 8px;}
  .lb-avail-block-title{font-size:12px;font-weight:900;letter-spacing:.06em;text-transform:uppercase;color:rgba(226,232,240,.5);}
  .lb-avail-block-current{display:flex;align-items:center;gap:7px;font-size:13px;font-weight:850;color:#fff;}
  .lb-avail-block-current .lb-avail-avatar-dot{position:static;width:9px;height:9px;border-width:0;box-shadow:none;}
  .lb-avail-block[data-status="online"] .lb-avail-block-current .lb-avail-avatar-dot{background:#22c55e;}
  .lb-avail-block[data-status="away"] .lb-avail-block-current .lb-avail-avatar-dot{background:#f59e0b;}
  .lb-avail-block-options{display:grid;grid-template-columns:repeat(3,1fr);gap:8px;}
  .lb-avail-block-btn{display:flex;align-items:center;justify-content:center;gap:7px;min-height:42px;padding:8px 6px;border-radius:13px;border:1px solid rgba(255,255,255,.075);background:rgba(255,255,255,.035);color:rgba(226,232,240,.72);font-size:13px;font-weight:850;cursor:pointer;transition:.14s ease;}
  .lb-avail-block-btn:hover{background:rgba(124,107,255,.15);border-color:rgba(124,107,255,.32);color:#fff;}
  .lb-avail-block-btn.is-active{background:rgba(124,107,255,.22);border-color:rgba(124,107,255,.55);color:#fff;}
  .lb-avail-block-dot{width:9px;height:9px;border-radius:50%;background:#94a3b8;flex:0 0 auto;}
  .lb-avail-block-dot--online{background:#22c55e;}
  .lb-avail-block-dot--away{background:#f59e0b;}
  .lb-client-dropdown-grid{display:grid;grid-template-columns:1fr 1fr;gap:10px;padding:4px 0 10px;}
  .lb-client-dropdown-link{min-height:62px;padding:11px 13px;border-radius:14px;border:1px solid rgba(255,255,255,.075);background:rgba(255,255,255,.035);color:#fff;text-decoration:none;display:flex;align-items:center;gap:10px;transition:.14s ease;}
  .lb-client-dropdown-link:hover{background:rgba(124,107,255,.15);border-color:rgba(124,107,255,.32);transform:translateY(-1px);}
  .lb-client-dropdown-link i{width:20px;text-align:center;color:#a5b4fc;font-size:17px;}
  .lb-client-dropdown-link span{font-size:14px;font-weight:850;line-height:1.2;}
  .lb-client-dropdown-foot{padding-top:8px;border-top:1px solid rgba(255,255,255,.08);display:flex;gap:8px;}
  .lb-client-dropdown-foot a{flex:1;min-height:48px;border-radius:13px;border:1px solid rgba(255,255,255,.08);background:rgba(255,255,255,.035);color:rgba(255,255,255,.76);text-decoration:none;display:flex;align-items:center;justify-content:center;gap:9px;font-size:13.5px;font-weight:900;}
  .lb-client-dropdown-foot a:hover{color:#fff;background:rgba(255,255,255,.07);}
  .lb-client-dropdown-foot .lb-client-logout{color:#fda4af;border-color:rgba(251,113,133,.18);background:rgba(251,113,133,.06);}
}
</style>
<style id="lb-dashboard-notification-styles">
@media (min-width: 992px){
  .lb-notif-clear{border:0;background:transparent;padding:6px 0;cursor:pointer;font-family:inherit;}
  .lb-dashboard-notif-body{padding:0!important;max-height:min(520px,68vh);overflow-y:auto;scrollbar-width:thin;scrollbar-color:rgba(255,255,255,.16) transparent;}
  .lb-dashboard-notif-body::-webkit-scrollbar{width:8px;}
  .lb-dashboard-notif-body::-webkit-scrollbar-thumb{background:rgba(255,255,255,.16);border-radius:999px;border:2px solid transparent;background-clip:padding-box;}
  .lb-dashboard-notif-item{display:flex;align-items:flex-start;gap:12px;padding:14px 16px;border-bottom:1px solid rgba(255,255,255,.07);color:inherit;text-decoration:none;transition:background .15s ease;}
  .lb-dashboard-notif-item:hover{background:rgba(255,255,255,.045);color:inherit;}
  .lb-dashboard-notif-item:last-child{border-bottom:0;}
  .lb-dashboard-notif-icon{width:42px;height:42px;flex:0 0 42px;border-radius:13px;display:grid;place-items:center;background:rgba(255,255,255,.065);border:1px solid rgba(255,255,255,.10);color:#eef2ff;font-size:17px;}
  .lb-dashboard-notif-main{min-width:0;flex:1;}
  .lb-dashboard-notif-top{display:flex;align-items:flex-start;justify-content:space-between;gap:10px;}
  .lb-dashboard-notif-title{margin:0;color:#fff;font-size:14px;font-weight:900;line-height:1.3;}
  .lb-dashboard-notif-time{font-size:11px;color:rgba(255,255,255,.38);white-space:nowrap;padding-top:2px;}
  .lb-dashboard-notif-sub{margin:5px 0 0;color:rgba(255,255,255,.52);font-size:12.5px;line-height:1.45;}
  .lb-dashboard-notif-actions{display:flex;align-items:center;gap:7px;margin-left:4px;}
  .lb-dashboard-notif-dot{width:7px;height:7px;border-radius:50%;background:#8b5cf6;box-shadow:0 0 0 4px rgba(139,92,246,.10);}
  .lb-dashboard-notif-read{width:27px;height:27px;border:0;border-radius:9px;background:rgba(255,255,255,.07);color:rgba(255,255,255,.72);display:grid;place-items:center;cursor:pointer;}
  .lb-dashboard-notif-read:hover{background:rgba(255,255,255,.13);color:#fff;}
  .lb-dashboard-notif-loading,.lb-dashboard-notif-empty{min-height:112px;padding:22px;display:flex;align-items:center;justify-content:center;gap:9px;color:rgba(255,255,255,.48);font-size:13px;text-align:center;}
  .lb-dashboard-notif-foot{padding:10px 14px;border-top:1px solid rgba(255,255,255,.07);text-align:center;color:rgba(255,255,255,.35);font-size:11.5px;}
}
</style>
<style id="lb-header-more-style">
@media (min-width:1025px){
  .navbar-top .lb-header-more{position:relative;}
  .navbar-top .lb-header-more-trigger{
    display:inline-flex;align-items:center;gap:7px;padding:8px 14px;border:0;border-radius:8px;
    background:transparent;color:#fff;font:inherit;font-size:19px;font-weight:600;cursor:pointer;
  }
  .navbar-top .lb-header-more-trigger:hover{background:rgba(255,255,255,.06);}
  .navbar-top .lb-header-more-trigger>i{font-size:10px;color:rgba(255,255,255,.55);transition:transform .16s ease;}
  .navbar-top .lb-header-more-menu{
    position:absolute;top:calc(100% + 18px);left:50%;width:510px;padding:12px;
    display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:8px;
    border:1px solid rgba(255,255,255,.12);border-radius:24px;
    background:rgba(7,11,22,.96);
    box-shadow:0 28px 80px rgba(0,0,0,.62),inset 0 1px 0 rgba(255,255,255,.06);
    backdrop-filter:blur(22px);-webkit-backdrop-filter:blur(22px);
    opacity:0;visibility:hidden;pointer-events:none;transform:translate(-50%,8px);
    transition:opacity .16s ease,transform .16s ease,visibility .16s ease;z-index:10020;
  }
  .navbar-top .lb-header-more-menu::before{
    content:"";
    position:absolute;
    left:0;
    right:0;
    top:-22px;
    height:24px;
  }
  .navbar-top .lb-header-more:hover .lb-header-more-menu,
  .navbar-top .lb-header-more:focus-within .lb-header-more-menu{
    opacity:1;visibility:visible;pointer-events:auto;transform:translate(-50%,0);
  }
  .navbar-top .lb-header-more:hover .lb-header-more-trigger>i,
  .navbar-top .lb-header-more:focus-within .lb-header-more-trigger>i{transform:rotate(180deg);}
  .navbar-top .lb-header-more-menu>a{
    min-height:116px;display:flex;flex-direction:column;align-items:flex-start;justify-content:space-between;
    gap:12px;padding:16px;border-radius:17px;color:#fff;text-decoration:none;
    background:rgba(255,255,255,.035);border:1px solid rgba(255,255,255,.07);
    transition:transform .16s ease,background .16s ease,border-color .16s ease;
  }
  .navbar-top .lb-header-more-menu>a:nth-child(1){background:linear-gradient(145deg,rgba(245,158,11,.13),rgba(255,255,255,.025));}
  .navbar-top .lb-header-more-menu>a:nth-child(2){background:linear-gradient(145deg,rgba(59,130,246,.13),rgba(255,255,255,.025));}
  .navbar-top .lb-header-more-menu>a:nth-child(3){background:linear-gradient(145deg,rgba(139,92,246,.13),rgba(255,255,255,.025));}
  .navbar-top .lb-header-more-menu>a:hover{transform:translateY(-3px);background:rgba(255,255,255,.08);border-color:rgba(255,255,255,.16);}
  .navbar-top .lb-header-more-icon{
    width:38px;height:38px;flex:0 0 38px;border-radius:12px;display:grid;place-items:center;
    background:rgba(99,102,241,.13);border:1px solid rgba(129,140,248,.18);color:#a5b4fc;
  }
  .navbar-top .lb-header-more-icon--reviews{background:rgba(251,191,36,.10);border-color:rgba(251,191,36,.18);color:#fbbf24;}
  .navbar-top .lb-header-more-menu strong{display:block;font-size:14px;line-height:1.2;}
  .navbar-top .lb-header-more-menu small{display:block;margin-top:4px;color:rgba(255,255,255,.48);font-size:11px;line-height:1.25;}
  @media (max-width:1536px){
    .navbar-top .lb-header-more-trigger{padding:7px 10px;font-size:clamp(14px,1.05vw,17px);line-height:1.15;}
  }
}
</style>
<style id="lb-marketplace-dropdown-polish">
@media (min-width:1025px){
  /* Match the marketplace search panel instead of using a separate blue surface. */
  .navbar-top .gmUnifiedShell{
    padding:10px !important;
    border-radius:32px !important;
    background:#0b1024 !important;
    border-color:rgba(129,160,255,.22) !important;
    box-shadow:0 42px 130px rgba(0,0,0,.74),inset 0 1px 0 rgba(255,255,255,.07) !important;
  }
  .navbar-top .gmUnifiedHead{
    min-height:78px !important;
    padding:14px 16px !important;
    border:0 !important;
    border-radius:22px !important;
    background:#111832 !important;
  }
  .navbar-top .gmUnifiedIcon{
    border-radius:999px !important;
    background:#6d8bff !important;
    border:0 !important;
    box-shadow:0 12px 30px rgba(79,110,247,.25) !important;
  }
  .navbar-top .gmUnifiedEyebrow{color:#a9bcff !important;}
  .navbar-top .gmUnifiedSearchMini{
    height:44px !important;
    min-width:220px !important;
    padding:0 17px !important;
    border:1px solid rgba(148,163,184,.16) !important;
    border-radius:999px !important;
    background:rgba(255,255,255,.04) !important;
    color:rgba(226,232,255,.82) !important;
    box-shadow:inset 0 1px 0 rgba(255,255,255,.05) !important;
  }
  .navbar-top .gmUnifiedSearchMini:hover{
    color:#fff !important;
    border-color:rgba(129,160,255,.42) !important;
    background:rgba(11,16,36,.92) !important;
    box-shadow:0 0 0 4px rgba(109,139,255,.14),inset 0 1px 0 rgba(255,255,255,.06) !important;
  }
  .navbar-top .gmUnifiedSearchMini i{color:#8fa6ff !important;}
  .navbar-top .gmUnifiedBody{
    padding:12px 0 !important;
    gap:10px !important;
    grid-template-columns:1.2fr .9fr .9fr !important;
  }
  .navbar-top .gmUnifiedSection{
    padding:14px !important;
    border-radius:22px !important;
    background:rgba(255,255,255,.025) !important;
    border:1px solid rgba(255,255,255,.065) !important;
  }
  .navbar-top .gmUnifiedSectionHead strong{
    color:rgba(255,255,255,.55) !important;
    letter-spacing:.14em !important;
  }
  .navbar-top .gmUnifiedAll{
    color:#cfd9ff !important;
    background:rgba(109,139,255,.16) !important;
    border:1px solid rgba(129,160,255,.32) !important;
    box-shadow:none !important;
  }
  .navbar-top .gmUnifiedGameGrid{gap:7px !important;}
  .navbar-top .gmUnifiedGame{
    min-height:54px !important;
    padding:8px 10px !important;
    border:0 !important;
    border-radius:12px !important;
    background:transparent !important;
    box-shadow:none !important;
  }
  .navbar-top .gmUnifiedGame:hover{
    background:rgba(109,139,255,.12) !important;
    box-shadow:none !important;
    transform:translateX(3px) !important;
  }
  /* The game rows are a simple icon + label list, so the tile content is
     vertically centered instead of pushed apart by the card layout. */
  .navbar-top .gmUnifiedSection--games .gmUnifiedGame{
    justify-content:center !important;
    gap:0 !important;
  }
  .navbar-top .gmUnifiedSection--games .gmUnifiedGameTop{
    align-items:center !important;
  }
  .navbar-top .gmUnifiedSection--games .gmUnifiedGameIcon{
    width:38px !important;height:38px !important;min-width:38px !important;
    border-radius:10px !important;background:transparent !important;
    border:0 !important;box-shadow:none !important;
  }
  .navbar-top .gmUnifiedSection--games .gmUnifiedGameIcon img{
    padding:0 !important;
  }
  .navbar-top .gmUnifiedGameName{font-size:14px !important;}
  /* The label ships with line-height:1.15, which pushes the text baseline below
     the icon center. line-height:1 + align-self makes it optically centered. */
  .navbar-top .gmUnifiedSection--games .gmUnifiedGameName{
    align-self:center !important;
    line-height:1 !important;
  }
  .navbar-top .gmUnifiedCardList{gap:7px !important;}
  .navbar-top .gmUnifiedCard{
    min-height:52px !important;
    padding:8px 10px !important;
    border:0 !important;
    border-radius:13px !important;
    background:rgba(255,255,255,.035) !important;
    box-shadow:none !important;
  }
  .navbar-top .gmUnifiedCard:hover{
    transform:translateX(3px) !important;
    background:rgba(255,255,255,.075) !important;
    box-shadow:none !important;
  }
  .navbar-top .gmUnifiedCardIcon{
    width:34px !important;height:34px !important;border-radius:10px !important;
    box-shadow:none !important;
  }
  .navbar-top .gmUnifiedCardName{font-size:14px !important;margin:0 !important;}
  .navbar-top .gmUnifiedFooter{padding:0 !important;}
  .navbar-top .gmUnifiedTrust{
    min-height:42px !important;
    border:0 !important;
    border-radius:18px !important;
    background:rgba(255,255,255,.025) !important;
    box-shadow:none !important;
  }
  .navbar-top .gmUnifiedTrust > span{
    border:0 !important;
    background:transparent !important;
    color:rgba(255,255,255,.55) !important;
  }
  .navbar-top .gmUnifiedTrust i{color:#8fa6ff !important;}
  .navbar-top .gmUnifiedHeading{font-size:25px !important;}
  .navbar-top .gmUnifiedMega::before{height:24px !important;}
  .navbar-top .gmUnifiedBrowsePanel{
    background:#0b1024 !important;
    border-color:rgba(129,160,255,.30) !important;
  }

  /* Preserve the complete game artwork instead of cropping it into a square. */
  .navbar-top .gmUnifiedGameIcon img,
  .navbar-top .gmUnifiedBrowseIcon img{
    width:100% !important;
    height:100% !important;
    padding:3px !important;
    box-sizing:border-box !important;
    object-fit:contain !important;
    object-position:center !important;
  }

  /* Service cards are intentionally title-only. */
  .navbar-top .gmUnifiedSection:nth-of-type(2) .gmUnifiedCard{
    min-height:58px !important;
  }
  .navbar-top .gmUnifiedSection:nth-of-type(2) .gmUnifiedCardBody{
    display:flex !important;
    align-items:center !important;
  }
}
</style>
<style id="lb-marketplace-dropdown-unified">
@media (min-width:1025px){
  /* One visual system for every Marketplace entry. */
  .navbar-top .gmUnifiedMega{
    width:min(1060px,calc(100vw - 40px)) !important;
  }
  .navbar-top .gmUnifiedShell{
    padding:12px !important;
    border-radius:26px !important;
    background:#080d1d !important;
    border:1px solid #202945 !important;
    box-shadow:0 30px 90px rgba(0,0,0,.68) !important;
  }
  .navbar-top .gmUnifiedHead{
    min-height:72px !important;
    height:72px !important;
    padding:0 18px !important;
    border-radius:18px !important;
    background:#10172d !important;
    border:1px solid #202945 !important;
  }
  .navbar-top .gmUnifiedTitle{gap:12px !important;}
  .navbar-top .gmUnifiedIcon{
    width:40px !important;
    height:40px !important;
    min-width:40px !important;
    border-radius:12px !important;
    background:#414fc4 !important;
    border:1px solid #5969dc !important;
    box-shadow:none !important;
  }
  .navbar-top .gmUnifiedEyebrow{
    font-size:11px !important;
    line-height:1 !important;
    letter-spacing:.12em !important;
    color:#9eafff !important;
  }
  .navbar-top .gmUnifiedHeading{
    margin-top:4px !important;
    font-size:22px !important;
    line-height:1 !important;
    letter-spacing:-.025em !important;
  }
  .navbar-top .gmUnifiedSearchMini{
    width:238px !important;
    min-width:238px !important;
    height:44px !important;
    min-height:44px !important;
    padding:0 15px !important;
    justify-content:flex-start !important;
    gap:9px !important;
    border-radius:13px !important;
    background:#151b2e !important;
    border:1px solid #2a3249 !important;
    box-shadow:none !important;
  }
  .navbar-top .gmUnifiedSearchMini:hover{
    transform:none !important;
    background:#1b2238 !important;
    border-color:#596dea !important;
    box-shadow:none !important;
  }
  .navbar-top .gmUnifiedSearchMini i,
  .navbar-top .lbms__fieldIcon,
  .navbar-top .gmHeaderSearchTriggerIcon,
  .gmHeaderSearchInputWrap>i,
  .gmHeaderCommandInputWrap>i{
    background:transparent !important;
    box-shadow:none !important;
  }
  .navbar-top .gmUnifiedBody{
    display:grid !important;
    grid-template-columns:1.35fr .76fr 1.05fr !important;
    align-items:start !important;
    gap:10px !important;
    padding:10px 0 !important;
  }
  .navbar-top .gmUnifiedSection{
    display:flex !important;
    flex-direction:column !important;
    align-self:start !important;
    height:auto !important;
    min-width:0 !important;
    padding:13px !important;
    border-radius:18px !important;
    background:#0e1426 !important;
    border:1px solid #20283d !important;
  }
  .navbar-top .gmUnifiedSectionHead{
    height:28px !important;
    min-height:28px !important;
    margin:0 0 8px !important;
  }
  .navbar-top .gmUnifiedSectionHead strong{
    font-size:11px !important;
    color:#9299ad !important;
    letter-spacing:.1em !important;
  }
  .navbar-top .gmUnifiedAll{
    height:28px !important;
    min-height:28px !important;
    padding:0 11px !important;
    border-radius:9px !important;
    background:#171e34 !important;
    border:1px solid #354166 !important;
    color:#cdd6ff !important;
  }
  .navbar-top .gmUnifiedGameGrid{
    display:grid !important;
    grid-template-columns:repeat(2,minmax(0,1fr)) !important;
    grid-auto-rows:58px !important;
    gap:8px !important;
  }
  .navbar-top .gmUnifiedCardList{
    display:grid !important;
    grid-auto-rows:58px !important;
    gap:8px !important;
  }
  .navbar-top .gmUnifiedGame,
  .navbar-top .gmUnifiedCard,
  .navbar-top .gmUnifiedSection:nth-of-type(2) .gmUnifiedCard{
    width:100% !important;
    min-width:0 !important;
    height:58px !important;
    min-height:58px !important;
    max-height:58px !important;
    margin:0 !important;
    padding:8px 10px !important;
    display:flex !important;
    flex-direction:row !important;
    align-items:center !important;
    justify-content:flex-start !important;
    gap:10px !important;
    border-radius:13px !important;
    background:#171d31 !important;
    border:1px solid transparent !important;
    box-shadow:none !important;
    transform:none !important;
  }
  .navbar-top .gmUnifiedGame:hover,
  .navbar-top .gmUnifiedCard:hover{
    background:#1d2540 !important;
    border-color:#495baf !important;
    box-shadow:none !important;
    transform:none !important;
  }
  .navbar-top .gmUnifiedSection--games .gmUnifiedGameTop{
    display:flex !important;
    width:100% !important;
    min-width:0 !important;
    align-items:center !important;
    gap:10px !important;
  }
  .navbar-top .gmUnifiedGameIcon,
  .navbar-top .gmUnifiedCardIcon,
  .navbar-top .gmUnifiedSection--games .gmUnifiedGameIcon{
    position:static !important;
    width:38px !important;
    height:38px !important;
    min-width:38px !important;
    flex:0 0 38px !important;
    margin:0 !important;
    border-radius:10px !important;
    background:#222b4a !important;
    border:1px solid #35436d !important;
    box-shadow:none !important;
  }
  .navbar-top .gmUnifiedBody .gmUnifiedSection .gmUnifiedCard .gmUnifiedCardIcon[class]{
    background:#414fc4 !important;
    border-color:#5969dc !important;
    color:#fff !important;
    box-shadow:none !important;
  }
  .navbar-top .gmUnifiedBody .gmUnifiedSection .gmUnifiedCard .gmUnifiedCardIcon i{
    color:#fff !important;
  }
  .navbar-top .gmUnifiedBody .gmUnifiedSection .gmUnifiedCard--egirls{
    border-color:rgba(244,114,182,.5) !important;
    background:linear-gradient(135deg,rgba(236,72,153,.2),rgba(168,85,247,.1)) !important;
  }
  .navbar-top .gmUnifiedBody .gmUnifiedSection .gmUnifiedCard--egirls:hover{
    border-color:rgba(244,114,182,.85) !important;
    background:linear-gradient(135deg,rgba(236,72,153,.3),rgba(168,85,247,.16)) !important;
  }
  .navbar-top .gmUnifiedBody .gmUnifiedSection .gmUnifiedCard--egirls .gmUnifiedCardIcon[class]{
    background:linear-gradient(135deg,#ec4899,#a855f7) !important;
    border-color:#f472b6 !important;
  }
  .navbar-top .gmUnifiedBody .gmUnifiedSection .gmUnifiedCard--egirls .gmUnifiedCardName{
    color:#f9a8d4 !important;
  }
  .navbar-top .gmUnifiedGameIcon img{
    width:100% !important;
    height:100% !important;
    padding:2px !important;
    object-fit:contain !important;
  }
  .navbar-top .gmUnifiedGameName,
  .navbar-top .gmUnifiedCardName{
    min-width:0 !important;
    margin:0 !important;
    font-size:13.5px !important;
    line-height:1.15 !important;
    font-weight:850 !important;
    white-space:nowrap !important;
    overflow:hidden !important;
    text-overflow:ellipsis !important;
  }
  .navbar-top .gmUnifiedCardBody{
    min-width:0 !important;
    display:flex !important;
    flex:1 !important;
    flex-direction:column !important;
    align-items:flex-start !important;
    justify-content:center !important;
    gap:2px !important;
    text-align:left !important;
  }
  .navbar-top .gmUnifiedBody .gmUnifiedSection:nth-of-type(2) .gmUnifiedCard .gmUnifiedCardBody,
  .navbar-top .gmUnifiedBody .gmUnifiedSection:nth-of-type(3) .gmUnifiedCard .gmUnifiedCardBody{
    display:flex !important;
    flex:1 1 auto !important;
    flex-direction:column !important;
    align-items:flex-start !important;
    justify-content:center !important;
    text-align:left !important;
  }
  .navbar-top .gmUnifiedBody .gmUnifiedSection:nth-of-type(2) .gmUnifiedCardName,
  .navbar-top .gmUnifiedBody .gmUnifiedSection:nth-of-type(3) .gmUnifiedCardName,
  .navbar-top .gmUnifiedBody .gmUnifiedSection:nth-of-type(3) .gmUnifiedCardDesc{
    width:100% !important;
    text-align:left !important;
  }
  .navbar-top .gmUnifiedCardDesc{
    margin:0 !important;
    font-size:11px !important;
    line-height:1.15 !important;
    color:#81899f !important;
  }
  .navbar-top .gmUnifiedMega{
    width:min(880px,calc(100vw - 40px)) !important;
  }
  .navbar-top .gmUnifiedBody{
    display:block !important;
  }
  .navbar-top .gmUnifiedSection--basic-services{
    width:100% !important;
  }
  .navbar-top .gmUnifiedSection--basic-services .gmUnifiedCardList{
    grid-template-columns:repeat(3,minmax(0,1fr)) !important;
    grid-auto-rows:64px !important;
  }
  .navbar-top .gmUnifiedSection--basic-services .gmUnifiedCard{
    height:64px !important;
    min-height:64px !important;
    max-height:64px !important;
  }
  .navbar-top .gmUnifiedHead{
    min-height:52px !important;
    height:52px !important;
    padding:0 !important;
    border:0 !important;
    border-radius:0 !important;
    background:transparent !important;
  }
  .navbar-top .gmUnifiedSearchMini{
    width:100% !important;
    min-width:0 !important;
    height:48px !important;
    min-height:48px !important;
    border-radius:14px !important;
  }
  .navbar-top .gmUnifiedBody{
    padding:8px 0 10px !important;
  }
  .navbar-top .gmUnifiedSection--basic-services{
    padding:0 !important;
    border:0 !important;
    border-radius:0 !important;
    background:transparent !important;
  }
  .navbar-top .gmUnifiedSection--basic-services .gmUnifiedSectionHead{
    height:24px !important;
    min-height:24px !important;
    margin:0 0 7px !important;
  }
  .navbar-top .gmUnifiedFooter{
    margin:0 -12px -12px !important;
    padding:0 12px !important;
    background:rgba(255,255,255,.012) !important;
    border-radius:0 0 25px 25px !important;
  }
  .navbar-top .gmUnifiedTrust{
    min-height:48px !important;
    height:auto !important;
    padding:8px 10px !important;
    gap:10px 14px !important;
    justify-content:center !important;
    border-radius:0 !important;
    background:transparent !important;
    border:0 !important;
    border-top:1px solid rgba(255,255,255,.08) !important;
  }
  .navbar-top .gmUnifiedTrust > span{
    min-height:30px !important;
    height:30px !important;
    padding:0 !important;
    gap:7px !important;
    background:transparent !important;
    border:0 !important;
    font-size:11px !important;
  }
  .navbar-top .gmUnifiedTrust .lbms__tp{
    margin-left:8px !important;
    height:32px !important;
    min-height:32px !important;
    padding:5px 10px !important;
    gap:7px !important;
    border-radius:9px !important;
    border:1px solid rgba(0,182,122,.42) !important;
    background:rgba(0,182,122,.12) !important;
    font-size:11px !important;
    line-height:1 !important;
    white-space:nowrap !important;
    box-shadow:none !important;
  }
  .navbar-top .gmUnifiedTrust .lbms__tp > span{
    width:auto !important;
    min-width:0 !important;
    height:auto !important;
    min-height:0 !important;
    margin:0 !important;
    padding:0 !important;
    border:0 !important;
    border-radius:0 !important;
    background:transparent !important;
    box-shadow:none !important;
  }
  .navbar-top .gmUnifiedTrust .lbms__tp b{
    font-size:11px !important;
    line-height:1 !important;
  }
  .navbar-top .gmUnifiedTrust .lbms__tpStars{
    gap:2px !important;
    font-size:10px !important;
    line-height:1 !important;
  }
  .navbar-top .gmUnifiedTrust .lbms__tpText{
    font-size:10px !important;
    line-height:1 !important;
  }
  .navbar-top .gmUnifiedTrust .lbms__tpLogo{
    width:20px !important;
    height:20px !important;
    min-width:20px !important;
    min-height:20px !important;
    border-radius:5px !important;
    font-size:9px !important;
    display:inline-grid !important;
    place-items:center !important;
    background:transparent !important;
  }
  .navbar-top .gmUnifiedTrust .lbms__tpStars,
  .navbar-top .gmUnifiedTrust .lbms__tpStars i{
    color:#00b67a !important;
  }
  .navbar-top .gmUnifiedTrust .lbms__tpLogo,
  .navbar-top .gmUnifiedTrust .lbms__tpLogo i{
    color:#00b67a !important;
  }

  /* "More" uses the same compact card language as Marketplace. */
  .navbar-top .lb-header-more .lb-header-more-menu{
    top:calc(100% + 16px) !important;
    left:50% !important;
    width:520px !important;
    padding:10px !important;
    grid-template-columns:repeat(3,minmax(0,1fr)) !important;
    gap:8px !important;
    border-radius:20px !important;
    background:#080d1d !important;
    border:1px solid #202945 !important;
    box-shadow:0 30px 90px rgba(0,0,0,.68) !important;
    backdrop-filter:none !important;
    -webkit-backdrop-filter:none !important;
  }
  .navbar-top .lb-header-more .lb-header-more-menu>a,
  .navbar-top .lb-header-more .lb-header-more-menu>a:nth-child(1),
  .navbar-top .lb-header-more .lb-header-more-menu>a:nth-child(2),
  .navbar-top .lb-header-more .lb-header-more-menu>a:nth-child(3){
    min-width:0 !important;
    min-height:76px !important;
    height:76px !important;
    padding:10px !important;
    display:flex !important;
    flex-direction:row !important;
    align-items:center !important;
    justify-content:flex-start !important;
    gap:10px !important;
    border-radius:13px !important;
    background:#171d31 !important;
    border:1px solid transparent !important;
    box-shadow:none !important;
    transform:none !important;
  }
  .navbar-top .lb-header-more .lb-header-more-menu>a:hover{
    background:#1d2540 !important;
    border-color:#495baf !important;
    box-shadow:none !important;
    transform:none !important;
  }
  .navbar-top .lb-header-more .lb-header-more-icon,
  .navbar-top .lb-header-more .lb-header-more-icon--reviews{
    width:38px !important;
    height:38px !important;
    min-width:38px !important;
    flex:0 0 38px !important;
    border-radius:10px !important;
    background:#414fc4 !important;
    border:1px solid #5969dc !important;
    color:#fff !important;
    box-shadow:none !important;
  }
  .navbar-top .lb-header-more .lb-header-more-menu>a>span:last-child{
    min-width:0 !important;
    display:block !important;
    text-align:left !important;
  }
  .navbar-top .lb-header-more .lb-header-more-menu strong{
    margin:0 !important;
    color:#fff !important;
    font-size:13px !important;
    line-height:1.15 !important;
    white-space:nowrap !important;
  }
  .navbar-top .lb-header-more .lb-header-more-menu small{
    margin-top:3px !important;
    color:#81899f !important;
    font-size:10.5px !important;
    line-height:1.15 !important;
    white-space:normal !important;
  }
}
</style>
<nav class="navbar-top">
    <div class="left">
        <a href="/">
            <img src="<?= ASSET_URL ?>/website/images/logo.svg" alt="Logo">
        </a>

        <ul>

<li class="nav-has-mega lbMarketplaceNav gmUnifiedNav">
  <a href="#" class="mega-link lbMarketplaceTrigger gmUnifiedTrigger" onclick="return false;">
    <?= t("Marketplace") ?>
    <i class="fas fa-chevron-down" aria-hidden="true"></i>
  </a>

  <div class="gmUnifiedMega" id="gmUnifiedMega" aria-label="Marketplace menu">
    <div class="gmUnifiedShell">
      <div class="gmUnifiedHead">
        <button type="button" class="gmUnifiedSearchMini" data-lbms-open>
          <i class="fas fa-search" aria-hidden="true"></i>
          <span><?= t('Search marketplace') ?></span>
        </button>
      </div>

      <div class="gmUnifiedBody">
        <section class="gmUnifiedSection gmUnifiedSection--basic-services">
          <div class="gmUnifiedSectionHead"><strong><?= t('Services') ?></strong></div>
          <div class="gmUnifiedCardList">
            <a class="gmUnifiedCard" href="/services/boosting"><span class="gmUnifiedCardIcon"><i class="fas fa-rocket"></i></span><span class="gmUnifiedCardBody"><span class="gmUnifiedCardName"><?= t('Boosting') ?></span><span class="gmUnifiedCardDesc"><?= t('Rank up with professional boosters') ?></span></span></a>
            <a class="gmUnifiedCard" href="/services/accounts"><span class="gmUnifiedCardIcon gmUnifiedCardIcon--gold"><i class="fas fa-user-shield"></i></span><span class="gmUnifiedCardBody"><span class="gmUnifiedCardName"><?= t('Accounts') ?></span><span class="gmUnifiedCardDesc"><?= t('Browse ready-to-play accounts') ?></span></span></a>
            <a class="gmUnifiedCard" href="/services/items"><span class="gmUnifiedCardIcon gmUnifiedCardIcon--blue"><i class="fas fa-gem"></i></span><span class="gmUnifiedCardBody"><span class="gmUnifiedCardName"><?= t('Items & Skins') ?></span><span class="gmUnifiedCardDesc"><?= t('Find game items and skins') ?></span></span></a>
            <a class="gmUnifiedCard" href="/services/coaching"><span class="gmUnifiedCardIcon gmUnifiedCardIcon--teal"><i class="fas fa-chalkboard-user"></i></span><span class="gmUnifiedCardBody"><span class="gmUnifiedCardName"><?= t('Coaching') ?></span><span class="gmUnifiedCardDesc"><?= t('Improve with personal coaching') ?></span></span></a>
            <a class="gmUnifiedCard" href="/services/top-ups"><span class="gmUnifiedCardIcon gmUnifiedCardIcon--gold"><i class="fas fa-coins"></i></span><span class="gmUnifiedCardBody"><span class="gmUnifiedCardName"><?= t('Top-ups') ?></span><span class="gmUnifiedCardDesc"><?= t('Top up game currencies') ?></span></span></a>
            <a class="gmUnifiedCard gmUnifiedCard--egirls" href="/egirls"><span class="gmUnifiedCardIcon gmUnifiedCardIcon--rose"><i class="fas fa-headset"></i></span><span class="gmUnifiedCardBody"><span class="gmUnifiedCardName"><?= t('Gamer Girls') ?></span><span class="gmUnifiedCardDesc"><?= t('Book gaming and chat sessions') ?></span></span></a>
            <a class="gmUnifiedCard" href="/digital-goods"><span class="gmUnifiedCardIcon gmUnifiedCardIcon--cyan"><i class="fas fa-box-open"></i></span><span class="gmUnifiedCardBody"><span class="gmUnifiedCardName"><?= t('Digital Goods') ?></span><span class="gmUnifiedCardDesc"><?= t('Subscriptions, software and more') ?></span></span></a>
          </div>
        </section>
      </div>

      <div class="gmUnifiedFooter">
        <div class="gmUnifiedTrust">
          <span><i class="fas fa-bolt"></i><?= t('Instant Delivery') ?></span>
          <span><i class="fas fa-shield-halved"></i><?= t('Buyer Protection') ?></span>
          <span><i class="fas fa-lock"></i><?= t('Secure Payments') ?></span>
          <span><i class="fas fa-headset"></i><?= t('24/7 Support') ?></span>
          <a class="lbms__tp" href="<?= htmlspecialchars($trustpilotUrl, ENT_QUOTES, 'UTF-8') ?>" target="_blank" rel="noopener" aria-label="Trustpilot">
            <b>Excellent</b>
            <span class="lbms__tpStars" aria-hidden="true"><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i></span>
            <span class="lbms__tpText">1,000+ <?= t('Reviews on') ?></span>
            <span class="lbms__tpLogo" aria-hidden="true"><i class="fa-solid fa-star"></i></span>
          </a>
        </div>
      </div>
    </div>
  </div>
</li>

            <li><a href="/lootboxes"><?= t('Lootboxes') ?></a></li>
            <li class="lb-header-more">
                <button type="button" class="lb-header-more-trigger" aria-haspopup="true">
                    <span><?= t('More') ?></span>
                    <i class="fa-solid fa-chevron-down" aria-hidden="true"></i>
                </button>
                <div class="lb-header-more-menu">
                    <a href="/reviews">
                        <span class="lb-header-more-icon lb-header-more-icon--reviews"><i class="fa-solid fa-star" aria-hidden="true"></i></span>
                        <span><strong><?= t('Reviews') ?></strong><small><?= t('Customer feedback') ?></small></span>
                    </a>
                    <a href="/blog">
                        <span class="lb-header-more-icon"><i class="fa-solid fa-newspaper" aria-hidden="true"></i></span>
                        <span><strong><?= t('Blog') ?></strong><small><?= t('News and guides') ?></small></span>
                    </a>
                    <a href="/work-with-us">
                        <span class="lb-header-more-icon"><i class="fa-solid fa-briefcase" aria-hidden="true"></i></span>
                        <span><strong><?= t('Work with us') ?></strong><small><?= t('Join our team') ?></small></span>
                    </a>
                </div>
            </li>
        </ul>
    </div>

<?php
  /* ────────────────────────────────────────────────────────────────
     MARKETPLACE SEARCH (inline field + dropdown, no modal)
     Data is prepared once and rendered server side; the dropdown
     filters/sorts client side.
     ──────────────────────────────────────────────────────────────── */
  $lbmsCatLabels = [
      'boosting' => t('Boosting'),
      'accounts' => t('Accounts'),
      'topups'   => t('Top-ups'),
      'items'    => t('Items'),
  ];
  $lbmsCatIcons = [
      'boosting' => 'fa-solid fa-rocket',
      'accounts' => 'fa-solid fa-user-shield',
      'topups'   => 'fa-solid fa-coins',
      'items'    => 'fa-solid fa-box',
  ];
  $lbmsAliasMap = [
      'league-of-legends'   => 'lol league summoners rift elo rank boost riot points rp',
      'valorant'            => 'val valo riot vp points skins',
      'teamfight-tactics'   => 'tft autobattler riot',
      'call-of-duty'        => 'cod warzone mw bo6 black ops',
      'apex-legends'        => 'apex ea battle royale',
      'arc-raiders'         => 'arc embark',
      'fortnite'            => 'fn epic vbucks v-bucks skins',
      'marvel-rivals'       => 'marvel rivals netease',
      'rocket-league'       => 'rl cars credits',
      'overwatch-2'         => 'ow ow2 blizzard',
      'genshin-impact'      => 'genshin hoyoverse primogems',
      'clash-of-clans'      => 'coc supercell',
      'brawl-stars'         => 'bs supercell',
      'grand-theft-auto-v'  => 'gta gta5 gta v rockstar',
      'grand-theft-auto-vi' => 'gta6 gta vi rockstar',
      'minecraft'           => 'mc mojang server',
      'roblox'              => 'rbx robux',
  ];

  $lbmsItems  = [];
  $lbmsCounts = ['all' => 0, 'boosting' => 0, 'accounts' => 0, 'topups' => 0, 'items' => 0, 'digital' => 0];

  $lbmsSeenGames = [];
  foreach (($lbGames ?? []) as $lbmsGame) {
      $lbmsName  = (string)($lbmsGame['name'] ?? ($lbmsGame['label'] ?? 'Game'));
      $lbmsSlug  = trim((string)($lbmsGame['slug'] ?? ''), '/');
      if ($lbmsSlug === '') continue;
      $lbmsGameKey = strtolower(preg_replace('/[^a-z0-9]+/i', '-', $lbmsSlug));
      if (isset($lbmsSeenGames[$lbmsGameKey])) continue;
      $lbmsSeenGames[$lbmsGameKey] = true;
      $lbmsIcon  = (string)($lbmsGame['icon'] ?? '');
      $lbmsCats  = (array)($lbmsGame['categories'] ?? []);
      $lbmsSoon  = !empty($lbmsGame['is_coming_soon']);
      $lbmsSoonCats = array_values(array_filter(array_map('strval', (array)($lbmsGame['soon_categories'] ?? []))));
      $lbmsCounts_g = is_array($lbmsGame['category_offer_counts'] ?? null) ? $lbmsGame['category_offer_counts'] : [];
      $lbmsOffers = max(0, (int)($lbmsGame['active_offers'] ?? $lbmsGame['offer_count'] ?? 0));

      // categories this game is listed under (incl. "soon" categories)
      $lbmsKeys = array_values(array_unique(array_merge(array_keys($lbmsCats), $lbmsSoonCats)));
      $lbmsKeys = array_values(array_intersect($lbmsKeys, ['boosting', 'accounts', 'topups', 'items', 'coaching']));

      // quick action chips (only categories with real listings)
      $lbmsActions = [];
      foreach (['boosting', 'accounts', 'items', 'topups'] as $lbmsKey) {
          if ($lbmsSoon || empty($lbmsCats[$lbmsKey])) continue;
          if ((int)($lbmsCounts_g[$lbmsKey] ?? 0) <= 0) continue;
          $lbmsHrefDefault = $lbmsKey === 'boosting'
              ? '/' . $lbmsSlug . '/rank-boost'
              : '/' . $lbmsSlug . '/' . ($lbmsKey === 'topups' ? 'top-ups' : $lbmsKey);
          $lbmsActions[$lbmsKey] = [
              'href'  => (string)($lbmsCats[$lbmsKey]['href'] ?? $lbmsHrefDefault),
              'label' => $lbmsCatLabels[$lbmsKey],
              'icon'  => $lbmsCatIcons[$lbmsKey],
          ];
      }

      $lbmsAliases = '';
      foreach ($lbmsAliasMap as $lbmsAliasKey => $lbmsAliasWords) {
          if (strpos($lbmsSlug, $lbmsAliasKey) !== false || strpos($lbmsAliasKey, $lbmsSlug) !== false) {
              $lbmsAliases .= ' ' . $lbmsAliasWords;
          }
      }

      $lbmsItems[] = [
          'name'    => $lbmsName,
          'slug'    => $lbmsSlug,
          'icon'    => $lbmsIcon,
          'href'    => '/' . $lbmsSlug,
          'soon'    => $lbmsSoon,
          'cats'    => $lbmsKeys,
          'actions' => $lbmsActions,
          'offers'  => $lbmsOffers,
          'service_offers' => [
              'boosting' => max(0, (int)($lbmsCounts_g['boosting'] ?? 0)),
              'accounts' => max(0, (int)($lbmsCounts_g['accounts'] ?? 0)),
              'topups'   => max(0, (int)($lbmsCounts_g['topups'] ?? 0)),
              'items'    => max(0, (int)($lbmsCounts_g['items'] ?? 0)),
          ],
          'search'  => strtolower(trim($lbmsName . ' ' . $lbmsSlug . ' ' . implode(' ', $lbmsKeys) . ' ' . $lbmsAliases)),
      ];

      $lbmsCounts['all']++;
      foreach (['boosting', 'accounts', 'topups', 'items'] as $lbmsKey) {
          if (in_array($lbmsKey, $lbmsKeys, true)) $lbmsCounts[$lbmsKey]++;
      }
  }

  $lbmsGameIconMap = [];
  foreach ($lbmsItems as $lbmsGameItemForIcon) {
      $lbmsIconSlug = strtolower(trim((string)($lbmsGameItemForIcon['slug'] ?? ''), '/'));
      $lbmsIconPath = trim((string)($lbmsGameItemForIcon['icon'] ?? ''));
      if ($lbmsIconSlug !== '' && $lbmsIconPath !== '') {
          $lbmsGameIconMap[$lbmsIconSlug] = $lbmsIconPath;
      }
  }
  $lbmsResolveGameIcon = static function (string $slug) use ($lbmsGameIconMap): string {
      $slug = strtolower(trim($slug, '/'));
      $aliases = [
          'lol' => 'league-of-legends',
          'league' => 'league-of-legends',
          'val' => 'valorant',
          'valo' => 'valorant',
          'gta5' => 'grand-theft-auto-v',
          'gta-v' => 'grand-theft-auto-v',
          'gta6' => 'grand-theft-auto-vi',
          'gta-vi' => 'grand-theft-auto-vi',
      ];
      $slug = $aliases[$slug] ?? $slug;
      if (isset($lbmsGameIconMap[$slug])) return $lbmsGameIconMap[$slug];
      foreach ($lbmsGameIconMap as $knownSlug => $icon) {
          if (str_contains($knownSlug, $slug) || str_contains($slug, $knownSlug)) return $icon;
      }
      return '';
  };

  // Digital goods entries (not tied to a game)
  $lbmsDigital = [
      ['href' => '/digital-goods/streaming',             'icon' => 'fa-solid fa-play',      'title' => t('Streaming & Music'),      'meta' => t('Netflix, Spotify, YouTube & more'),   'search' => 'streaming music netflix spotify youtube disney hbo'],
      ['href' => '/digital-goods/software',              'icon' => 'fa-solid fa-microchip', 'title' => t('Software & Tools'),       'meta' => t('Tools, licenses & productivity'),      'search' => 'software tools licenses office windows productivity'],
      ['href' => '/digital-goods/subscriptions',         'icon' => 'fa-solid fa-rotate',    'title' => t('Subscriptions'),          'meta' => t('Premium memberships & accounts'),      'search' => 'subscriptions premium memberships'],
      ['href' => '/digital-goods/discord',               'icon' => 'fa-brands fa-discord',  'title' => t('Discord'),                'meta' => t('Nitro, boosts & subscriptions'),       'search' => 'discord nitro boosts'],
      ['href' => '/digital-goods/ingame-currency',       'icon' => 'fa-solid fa-coins',     'title' => t('Ingame Currency'),        'meta' => t('V-Bucks, Robux, Riot Points & more'),  'search' => 'ingame currency vbucks robux riot points coins'],
      ['href' => '/digital-goods/gaming-subscriptions',  'icon' => 'fa-solid fa-gamepad',   'title' => t('Gaming Subscriptions'),   'meta' => t('PSN, Xbox, Steam & more'),             'search' => 'gaming subscriptions xbox game pass playstation plus psn steam'],
  ];
  $lbmsDirectories = [
      ['href' => '/egirls',   'icon' => 'fa-solid fa-headset', 'title' => t('Gamer Girls'), 'meta' => t('Play, chat and book sessions'), 'search' => 'gamer girl gamer girls gamergirl gamergirls egirl egirls e-girl e-girls ggirl ggirls gg girl gg girls', 'accent' => 'pink'],
      ['href' => '/boosters', 'icon' => 'fa-solid fa-users',   'title' => t('Boosters'),    'meta' => t('Browse verified professional boosters'), 'search' => 'booster boosters pro player pro players boosting professionals'],
  ];

  // Individual active Digital Goods must be searchable by their real product
  // title/slug/brand, not only through broad category cards.
  $lbmsDigitalProducts = [];
  try {
      global $db;
      if (isset($db) && is_object($db)) {
          $lbmsProductRows = $db->run(
              "SELECT dg.id, dg.title, dg.slug, dg.brand, dg.images, dg.brand_icon,
                      dg.price, dg.currency, dgc.name AS category_name
               FROM digital_goods dg
               LEFT JOIN digital_good_categories dgc ON dgc.id = dg.category_id
               WHERE dg.active = 1 AND dg.stock > 0
               ORDER BY dg.sold_count DESC, dg.id DESC
               LIMIT 250"
          ) ?: [];
          foreach ($lbmsProductRows as $lbmsProduct) {
              $lbmsProductSlug = trim((string)($lbmsProduct['slug'] ?? ''));
              if ($lbmsProductSlug === '' && function_exists('lb_dg_public_slug')) {
                  $lbmsProductSlug = (string)lb_dg_public_slug($lbmsProduct);
              }
              if ($lbmsProductSlug === '') continue;
              $lbmsProductTitle = trim((string)($lbmsProduct['title'] ?? 'Digital Good'));
              $lbmsProductBrand = trim((string)($lbmsProduct['brand'] ?? ''));
              $lbmsProductCategory = trim((string)($lbmsProduct['category_name'] ?? 'Digital Goods'));
              $lbmsProductCurrency = strtoupper(trim((string)($lbmsProduct['currency'] ?? 'EUR')));
              $lbmsProductSymbol = $lbmsProductCurrency === 'USD' ? '$' : ($lbmsProductCurrency === 'GBP' ? '£' : '€');
              $lbmsProductImages = json_decode((string)($lbmsProduct['images'] ?? '[]'), true);
              $lbmsProductImage = is_array($lbmsProductImages) && !empty($lbmsProductImages[0])
                  ? trim((string)$lbmsProductImages[0])
                  : trim((string)($lbmsProduct['brand_icon'] ?? ''));
              if ($lbmsProductImage !== '' && !preg_match('#^(?:https?:)?//#i', $lbmsProductImage) && !str_starts_with($lbmsProductImage, 'data:')) {
                  $lbmsProductImage = preg_replace('#^/public/assets#', '', $lbmsProductImage);
                  $lbmsProductImage = preg_replace('#/public/assets/#', '/', $lbmsProductImage);
                  $lbmsProductImage = (defined('ASSET_URL') ? rtrim(ASSET_URL, '/') : '') . '/' . ltrim((string)$lbmsProductImage, '/');
              }
              $lbmsDigitalProducts[] = [
                  'href' => '/digital-good/' . rawurlencode($lbmsProductSlug),
                  'icon' => 'fa-solid fa-gem',
                  'image' => $lbmsProductImage,
                  'title' => $lbmsProductTitle,
                  'meta' => trim(($lbmsProductBrand !== '' ? $lbmsProductBrand . ' • ' : '') . $lbmsProductCategory),
                  'price' => $lbmsProductSymbol . number_format(max(0, (int)($lbmsProduct['price'] ?? 0)) / 100, 2, '.', ''),
                  'search' => strtolower(trim($lbmsProductTitle . ' ' . $lbmsProductSlug . ' ' . $lbmsProductBrand . ' ' . $lbmsProductCategory . ' digital good product')),
              ];
          }
      }
  } catch (Throwable $e) {
      $lbmsDigitalProducts = [];
  }

  // Individual marketplace account listings are also included in the header
  // search, so searches such as "league account" return purchasable products
  // directly instead of only the League of Legends category.
  $lbmsAccountProducts = [];
  try {
      global $db;
      if (isset($db) && is_object($db)) {
          $lbmsAccountRows = $db->run(
              "SELECT id, title, slug, game, server, price, images,
                      current_rank, current_division, current_lp, rank, rank_label,
                      level, blue_essence, riot_points
               FROM selling_accounts
               WHERE COALESCE(sold, 0) = 0 AND COALESCE(active, 1) = 1
                 AND slug IS NOT NULL AND slug <> ''
               ORDER BY created_at DESC, id DESC
               LIMIT 180"
          ) ?: [];

          foreach ($lbmsAccountRows as $lbmsAccount) {
              $lbmsAccountSlug = trim((string)($lbmsAccount['slug'] ?? ''));
              if ($lbmsAccountSlug === '') continue;

              $lbmsAccountGameRaw = strtolower(trim((string)($lbmsAccount['game'] ?? 'league-of-legends')));
              if (in_array($lbmsAccountGameRaw, ['', 'lol', 'league', 'leagu', 'league-of-legends'], true)) {
                  $lbmsAccountGameSlug = 'lol';
                  $lbmsAccountGameName = 'League of Legends';
                  $lbmsAccountGameSearch = 'league of legends lol league account accounts';
              } elseif (in_array($lbmsAccountGameRaw, ['val', 'valor', 'valorant'], true)) {
                  $lbmsAccountGameSlug = 'val';
                  $lbmsAccountGameName = 'Valorant';
                  $lbmsAccountGameSearch = 'valorant val valo account accounts';
              } else {
                  $lbmsAccountGameSlug = trim($lbmsAccountGameRaw, '/');
                  $lbmsAccountGameName = ucwords(str_replace('-', ' ', $lbmsAccountGameSlug));
                  $lbmsAccountGameSearch = str_replace('-', ' ', $lbmsAccountGameSlug) . ' account accounts';
              }

              $lbmsAccountTitle = trim((string)($lbmsAccount['title'] ?? ''));
              if ($lbmsAccountTitle === '') $lbmsAccountTitle = $lbmsAccountGameName . ' Account';
              $lbmsAccountServer = strtoupper(trim((string)($lbmsAccount['server'] ?? '')));

              $lbmsAccountRank = '';
              if ($lbmsAccountGameSlug === 'lol' && function_exists('util_get_lol_rank')) {
                  $lbmsAccountRank = trim((string)util_get_lol_rank((int)($lbmsAccount['current_rank'] ?? 0)));
                  $lbmsDivision = (int)($lbmsAccount['current_division'] ?? 0);
                  if ($lbmsDivision > 0 && function_exists('util_format_lol_division')) {
                      $lbmsAccountRank .= ' ' . trim((string)util_format_lol_division($lbmsDivision));
                  }
              } elseif (trim((string)($lbmsAccount['rank_label'] ?? '')) !== '') {
                  $lbmsAccountRank = trim((string)$lbmsAccount['rank_label']);
              } elseif (function_exists('util_get_rank_label')) {
                  $lbmsAccountRank = trim((string)util_get_rank_label($lbmsAccountGameSlug, (int)($lbmsAccount['rank'] ?? 0)));
              }

              $lbmsAccountMetaParts = array_values(array_filter([
                  $lbmsAccountGameName,
                  $lbmsAccountServer,
                  $lbmsAccountRank,
              ], static fn($v) => trim((string)$v) !== ''));

              $lbmsAccountImages = json_decode((string)($lbmsAccount['images'] ?? '[]'), true);
              $lbmsAccountImage = is_array($lbmsAccountImages) && !empty($lbmsAccountImages[0])
                  ? trim((string)$lbmsAccountImages[0])
                  : '';
              if ($lbmsAccountImage !== '' && !preg_match('#^(?:https?:)?//#i', $lbmsAccountImage) && !str_starts_with($lbmsAccountImage, 'data:')) {
                  $lbmsAccountImage = preg_replace('#^/public/assets#', '', $lbmsAccountImage);
                  $lbmsAccountImage = preg_replace('#/public/assets/#', '/', $lbmsAccountImage);
                  $lbmsAccountImage = (defined('ASSET_URL') ? rtrim(ASSET_URL, '/') : '') . '/' . ltrim((string)$lbmsAccountImage, '/');
              }

              $lbmsAccountProducts[] = [
                  'href'   => '/' . rawurlencode($lbmsAccountGameSlug) . '/account/' . rawurlencode($lbmsAccountSlug),
                  'image'  => $lbmsAccountImage,
                  'game_icon' => $lbmsResolveGameIcon($lbmsAccountGameSlug),
                  'title'  => $lbmsAccountTitle,
                  'meta'   => implode(' • ', $lbmsAccountMetaParts),
                  'price'  => '€' . number_format(max(0, (int)($lbmsAccount['price'] ?? 0)) / 100, 2, '.', ''),
                  'search' => strtolower(trim(
                      $lbmsAccountTitle . ' ' . $lbmsAccountSlug . ' ' .
                      $lbmsAccountGameSearch . ' ' . $lbmsAccountServer . ' ' .
                      $lbmsAccountRank . ' level ' . (int)($lbmsAccount['level'] ?? 0) . ' ' .
                      (int)($lbmsAccount['blue_essence'] ?? 0) . ' blue essence ' .
                      (int)($lbmsAccount['riot_points'] ?? 0) . ' riot points rp listing product'
                  )),
              ];
          }
      }
  } catch (Throwable $e) {
      $lbmsAccountProducts = [];
  }

  // Searchable item, top-up and boosting results.
  $lbmsItemProducts = [];
  $lbmsTopupProducts = [];
  $lbmsBoostingProducts = [];

  try {
      global $db;
      if (isset($db) && is_object($db)) {
          $lbmsItemRows = $db->run(
              "SELECT si.id, si.title, si.slug, si.game, si.server, si.price, si.currency, si.images,
                      g.name AS game_name, g.slug AS game_slug
               FROM selling_items si
               LEFT JOIN games g ON g.id = si.game_id
               WHERE COALESCE(si.active, 1) = 1 AND COALESCE(si.stock, 1) > 0
               ORDER BY si.sold_count DESC, si.created_at DESC, si.id DESC
               LIMIT 180"
          ) ?: [];

          foreach ($lbmsItemRows as $lbmsItemRow) {
              $gameSlug = trim((string)($lbmsItemRow['game_slug'] ?? $lbmsItemRow['game'] ?? 'league-of-legends'), '/');
              if ($gameSlug === '') $gameSlug = 'league-of-legends';
              $gameName = trim((string)($lbmsItemRow['game_name'] ?? ''));
              if ($gameName === '') $gameName = ucwords(str_replace('-', ' ', $gameSlug));
              $slug = trim((string)($lbmsItemRow['slug'] ?? ''));
              if ($slug === '') $slug = (string)(int)($lbmsItemRow['id'] ?? 0);
              $title = trim((string)($lbmsItemRow['title'] ?? 'Item'));
              $server = strtoupper(trim((string)($lbmsItemRow['server'] ?? '')));
              $currency = strtoupper(trim((string)($lbmsItemRow['currency'] ?? 'EUR')));
              $symbol = $currency === 'USD' ? '$' : ($currency === 'GBP' ? '£' : '€');
              $images = json_decode((string)($lbmsItemRow['images'] ?? '[]'), true);
              $image = is_array($images) && !empty($images[0]) ? trim((string)$images[0]) : '';
              if ($image !== '' && !preg_match('#^(?:https?:)?//#i', $image) && !str_starts_with($image, 'data:')) {
                  $image = preg_replace('#^/public/assets#', '', $image);
                  $image = preg_replace('#/public/assets/#', '/', $image);
                  $image = (defined('ASSET_URL') ? rtrim(ASSET_URL, '/') : '') . '/' . ltrim((string)$image, '/');
              }
              $lbmsItemProducts[] = [
                  'href' => '/' . rawurlencode($gameSlug) . '/item/' . rawurlencode($slug),
                  'image' => $image,
                  'game_icon' => $lbmsResolveGameIcon($gameSlug),
                  'title' => $title,
                  'meta' => implode(' • ', array_values(array_filter([$gameName, $server]))),
                  'price' => $symbol . number_format(max(0, (int)($lbmsItemRow['price'] ?? 0)) / 100, 2, '.', ''),
                  'search' => strtolower(trim($title . ' ' . $slug . ' ' . $gameName . ' ' . str_replace('-', ' ', $gameSlug) . ' ' . $server . ' item items skin skins product listing')),
              ];
          }

          $lbmsTopupRows = $db->run(
              "SELECT st.id, st.offer_title, st.game_slug, st.game_name, st.region, st.platform,
                      st.price, st.currency, st.image, g.slug AS db_game_slug, g.name AS db_game_name
               FROM selling_topups st
               LEFT JOIN games g ON g.id = st.game_id
               WHERE COALESCE(st.active, 1) = 1 AND COALESCE(st.stock, 1) > 0
               ORDER BY st.sold_count DESC, st.created_at DESC, st.id DESC
               LIMIT 180"
          ) ?: [];

          foreach ($lbmsTopupRows as $lbmsTopupRow) {
              $gameSlug = trim((string)($lbmsTopupRow['db_game_slug'] ?? $lbmsTopupRow['game_slug'] ?? 'league-of-legends'), '/');
              if ($gameSlug === '') $gameSlug = 'league-of-legends';
              $gameName = trim((string)($lbmsTopupRow['db_game_name'] ?? $lbmsTopupRow['game_name'] ?? ''));
              if ($gameName === '') $gameName = ucwords(str_replace('-', ' ', $gameSlug));
              $title = trim((string)($lbmsTopupRow['offer_title'] ?? 'Top-up'));
              $region = strtoupper(trim((string)($lbmsTopupRow['region'] ?? '')));
              $platform = trim((string)($lbmsTopupRow['platform'] ?? ''));
              $currency = strtoupper(trim((string)($lbmsTopupRow['currency'] ?? 'EUR')));
              $symbol = $currency === 'USD' ? '$' : ($currency === 'GBP' ? '£' : '€');
              $image = trim((string)($lbmsTopupRow['image'] ?? ''));
              if ($image !== '' && !preg_match('#^(?:https?:)?//#i', $image) && !str_starts_with($image, 'data:')) {
                  $image = preg_replace('#^/public/assets#', '', $image);
                  $image = preg_replace('#/public/assets/#', '/', $image);
                  $image = (defined('ASSET_URL') ? rtrim(ASSET_URL, '/') : '') . '/' . ltrim((string)$image, '/');
              }
              $lbmsTopupProducts[] = [
                  'href' => '/' . rawurlencode($gameSlug) . '/top-ups',
                  'image' => $image,
                  'game_icon' => $lbmsResolveGameIcon($gameSlug),
                  'title' => $title,
                  'meta' => implode(' • ', array_values(array_filter([$gameName, $region, $platform]))),
                  'price' => $symbol . number_format(max(0, (int)($lbmsTopupRow['price'] ?? 0)) / 100, 2, '.', ''),
                  'search' => strtolower(trim($title . ' ' . $gameName . ' ' . str_replace('-', ' ', $gameSlug) . ' ' . $region . ' ' . $platform . ' topup top up currency coins credits product listing')),
              ];
          }
      }
  } catch (Throwable $e) {
      $lbmsItemProducts = [];
      $lbmsTopupProducts = [];
  }

  foreach ($lbmsItems as $lbmsGameItem) {
      if (!empty($lbmsGameItem['soon']) || empty($lbmsGameItem['actions']['boosting'])) continue;
      $boostAction = $lbmsGameItem['actions']['boosting'];
      $lbmsBoostingProducts[] = [
          'href' => (string)$boostAction['href'],
          'image' => (string)($lbmsGameItem['icon'] ?? ''),
          'game_icon' => (string)($lbmsGameItem['icon'] ?? ''),
          'title' => (string)$lbmsGameItem['name'] . ' ' . t('Boosting'),
          'meta' => t('Rank Boosting Service'),
          'price' => '',
          'search' => strtolower(trim((string)$lbmsGameItem['search'] . ' rank boost boosting service elo wins placement coaching')),
      ];
  }

  $lbmsCounts['boosting'] += count($lbmsBoostingProducts);
  $lbmsCounts['accounts'] += count($lbmsAccountProducts);
  $lbmsCounts['topups'] += count($lbmsTopupProducts);
  $lbmsCounts['items'] += count($lbmsItemProducts);
  $lbmsCounts['digital'] = count($lbmsDigital) + count($lbmsDigitalProducts);
  $lbmsCounts['all'] += count($lbmsDigital) + count($lbmsDigitalProducts) + count($lbmsAccountProducts)
      + count($lbmsItemProducts) + count($lbmsTopupProducts) + count($lbmsBoostingProducts) + count($lbmsDirectories);

  $lbmsTabs = [
      'all'      => ['label' => t('All'),           'icon' => 'fa-solid fa-layer-group'],
      'boosting' => ['label' => t('Boosting'),      'icon' => 'fa-solid fa-rocket'],
      'accounts' => ['label' => t('Accounts'),      'icon' => 'fa-solid fa-user-shield'],
      'topups'   => ['label' => t('Top-ups'),       'icon' => 'fa-solid fa-coins'],
      'items'    => ['label' => t('Items'),         'icon' => 'fa-solid fa-box'],
      'digital'  => ['label' => t('Digital Goods'), 'icon' => 'fa-solid fa-gem'],
  ];
?>
<div class="lbms" id="lbms">
  <div class="lbms__field" id="lbmsField">
    <i class="fa-solid fa-magnifying-glass lbms__fieldIcon" aria-hidden="true"></i>
    <input
      type="text"
      class="lbms__input"
      id="lbmsInput"
      autocomplete="off"
      spellcheck="false"
      role="combobox"
      aria-expanded="false"
      aria-controls="lbmsPanel"
      aria-autocomplete="list"
      placeholder="<?= t('Search games, boosts, accounts, top-ups, digital goods...') ?>">
    <button type="button" class="lbms__clear" id="lbmsClear" aria-label="<?= t('Clear') ?>" hidden>
      <i class="fa-solid fa-xmark" aria-hidden="true"></i>
    </button>
    <span class="lbms__hint" aria-hidden="true">/</span>
    <button type="button" class="lbms__sheetClose" id="lbmsSheetClose" aria-label="<?= t('Close') ?>">
      <i class="fa-solid fa-xmark" aria-hidden="true"></i>
    </button>
  </div>

  <div class="lbms__panel" id="lbmsPanel" role="listbox" aria-label="<?= t('Search results') ?>" hidden>
    <div class="lbms__tabs" role="tablist" aria-label="<?= t('Categories') ?>">
      <?php foreach ($lbmsTabs as $lbmsTabKey => $lbmsTab): ?>
        <button type="button"
                class="lbms__tab<?= $lbmsTabKey === 'all' ? ' is-active' : '' ?>"
                data-lbms-tab="<?= $lbmsTabKey ?>"
                role="tab"
                aria-selected="<?= $lbmsTabKey === 'all' ? 'true' : 'false' ?>">
          <i class="<?= $lbmsTab['icon'] ?>" aria-hidden="true"></i>
          <span><?= htmlspecialchars($lbmsTab['label'], ENT_QUOTES, 'UTF-8') ?></span>
          <b class="lbms__tabCount"><?= (int)($lbmsCounts[$lbmsTabKey] ?? 0) ?></b>
        </button>
      <?php endforeach; ?>
    </div>

    <div class="lbms__toolbar">
      <span class="lbms__count" id="lbmsCount" aria-live="polite" data-one="<?= t('result') ?>" data-many="<?= t('results') ?>"></span>
      <div class="lbms__sort" role="group" aria-label="<?= t('Sort') ?>">
        <button type="button" class="lbms__sortBtn is-active" data-lbms-sort="popular"><?= t('Popular') ?></button>
        <button type="button" class="lbms__sortBtn" data-lbms-sort="az">A–Z</button>
      </div>
    </div>

    <div class="lbms__scroll">
      <div class="lbms__grid" id="lbmsGrid">
        <?php foreach ($lbmsItems as $lbmsIdx => $lbmsItem): ?>
          <div class="lbms__card<?= $lbmsItem['soon'] ? ' is-soon' : '' ?>"
               data-lbms-item="game"
               data-cats="<?= htmlspecialchars(implode(' ', $lbmsItem['cats']), ENT_QUOTES, 'UTF-8') ?>"
               data-search="<?= htmlspecialchars($lbmsItem['search'], ENT_QUOTES, 'UTF-8') ?>"
               data-name="<?= htmlspecialchars(strtolower($lbmsItem['name']), ENT_QUOTES, 'UTF-8') ?>"
               data-offers="<?= (int)$lbmsItem['offers'] ?>"
               data-offers-boosting="<?= (int)($lbmsItem['service_offers']['boosting'] ?? 0) ?>"
               data-offers-accounts="<?= (int)($lbmsItem['service_offers']['accounts'] ?? 0) ?>"
               data-offers-topups="<?= (int)($lbmsItem['service_offers']['topups'] ?? 0) ?>"
               data-offers-items="<?= (int)($lbmsItem['service_offers']['items'] ?? 0) ?>"
               data-order="<?= (int)$lbmsIdx ?>"
               data-href-default="<?= htmlspecialchars($lbmsItem['href'], ENT_QUOTES, 'UTF-8') ?>"
               data-href-boosting="<?= htmlspecialchars('/' . $lbmsItem['slug'] . '/rank-boost', ENT_QUOTES, 'UTF-8') ?>"
               data-href-accounts="<?= htmlspecialchars('/' . $lbmsItem['slug'] . '/accounts', ENT_QUOTES, 'UTF-8') ?>"
               data-href-topups="<?= htmlspecialchars('/' . $lbmsItem['slug'] . '/top-ups', ENT_QUOTES, 'UTF-8') ?>"
               data-href-items="<?= htmlspecialchars('/' . $lbmsItem['slug'] . '/items', ENT_QUOTES, 'UTF-8') ?>"
               data-href="<?= htmlspecialchars($lbmsItem['href'], ENT_QUOTES, 'UTF-8') ?>">
            <a class="lbms__cardMain" href="<?= htmlspecialchars($lbmsItem['href'], ENT_QUOTES, 'UTF-8') ?>" role="option" tabindex="-1">
              <span class="lbms__cardIcon">
                <?php if ($lbmsItem['icon']): ?>
                  <img src="<?= htmlspecialchars($lbmsItem['icon'], ENT_QUOTES, 'UTF-8') ?>" alt="" loading="lazy">
                <?php else: ?>
                  <i class="fa-solid fa-gamepad" aria-hidden="true"></i>
                <?php endif; ?>
              </span>
              <span class="lbms__cardBody">
                <span class="lbms__cardTitle"><?= htmlspecialchars($lbmsItem['name'], ENT_QUOTES, 'UTF-8') ?></span>
                <span class="lbms__cardMeta">
                  <?php if ($lbmsItem['soon']): ?>
                    <?= t('Coming soon') ?>
                  <?php else: ?>
                    <?= number_format((int)$lbmsItem['offers'], 0, '.', ',') ?> <?= (int)$lbmsItem['offers'] === 1 ? t('offer') : t('offers') ?>
                  <?php endif; ?>
                </span>
              </span>
              <span class="lbms__soon"<?= $lbmsItem['soon'] ? '' : ' hidden' ?>><?= t('Soon') ?></span>
            </a>
          </div>
        <?php endforeach; ?>

        <div class="lbms__groupHeading" id="lbmsCategoryHeading" hidden><?= t('Categories') ?></div>
        <?php foreach ($lbmsDigital as $lbmsDIdx => $lbmsD): ?>
          <div class="lbms__card lbms__card--digital"
               data-lbms-item="digital"
               data-cats="digital"
               data-search="<?= htmlspecialchars(strtolower($lbmsD['title'] . ' ' . $lbmsD['search']), ENT_QUOTES, 'UTF-8') ?>"
               data-name="<?= htmlspecialchars(strtolower($lbmsD['title']), ENT_QUOTES, 'UTF-8') ?>"
               data-offers="0"
               data-order="<?= 900 + (int)$lbmsDIdx ?>"
               data-href="<?= htmlspecialchars($lbmsD['href'], ENT_QUOTES, 'UTF-8') ?>">
            <a class="lbms__cardMain" href="<?= htmlspecialchars($lbmsD['href'], ENT_QUOTES, 'UTF-8') ?>" role="option" tabindex="-1">
              <span class="lbms__cardIcon lbms__cardIcon--flat"><i class="<?= $lbmsD['icon'] ?>" aria-hidden="true"></i></span>
              <span class="lbms__cardBody">
                <span class="lbms__cardTitle"><?= htmlspecialchars($lbmsD['title'], ENT_QUOTES, 'UTF-8') ?></span>
                <span class="lbms__cardMeta"><?= htmlspecialchars($lbmsD['meta'], ENT_QUOTES, 'UTF-8') ?></span>
              </span>
            </a>
          </div>
        <?php endforeach; ?>

        <?php foreach ($lbmsDirectories as $lbmsDirectoryIdx => $lbmsDirectory): ?>
          <div class="lbms__card lbms__card--digital<?= (($lbmsDirectory['accent'] ?? '') === 'pink') ? ' lbms__card--pink' : '' ?>"
               data-lbms-item="directory"
               data-cats=""
               data-search="<?= htmlspecialchars(strtolower($lbmsDirectory['title'] . ' ' . $lbmsDirectory['search']), ENT_QUOTES, 'UTF-8') ?>"
               data-name="<?= htmlspecialchars(strtolower($lbmsDirectory['title']), ENT_QUOTES, 'UTF-8') ?>"
               data-offers="0"
               data-order="<?= 700 + (int)$lbmsDirectoryIdx ?>"
               data-href="<?= htmlspecialchars($lbmsDirectory['href'], ENT_QUOTES, 'UTF-8') ?>">
            <a class="lbms__cardMain" href="<?= htmlspecialchars($lbmsDirectory['href'], ENT_QUOTES, 'UTF-8') ?>" role="option" tabindex="-1">
              <span class="lbms__cardIcon lbms__cardIcon--flat"><i class="<?= $lbmsDirectory['icon'] ?>" aria-hidden="true"></i></span>
              <span class="lbms__cardBody">
                <span class="lbms__cardTitle"><?= htmlspecialchars($lbmsDirectory['title'], ENT_QUOTES, 'UTF-8') ?></span>
                <span class="lbms__cardMeta"><?= htmlspecialchars($lbmsDirectory['meta'], ENT_QUOTES, 'UTF-8') ?></span>
              </span>
            </a>
          </div>
        <?php endforeach; ?>

        <div class="lbms__groupHeading" id="lbmsProductHeading" hidden><?= t('Products') ?></div>
        <?php foreach ($lbmsAccountProducts as $lbmsAccountProductIdx => $lbmsProduct): ?>
          <div class="lbms__card lbms__card--digital lbms__card--product"
               data-lbms-item="account-product"
               data-search-only="1"
               data-cats="accounts"
               data-search="<?= htmlspecialchars($lbmsProduct['search'], ENT_QUOTES, 'UTF-8') ?>"
               data-name="<?= htmlspecialchars(strtolower($lbmsProduct['title']), ENT_QUOTES, 'UTF-8') ?>"
               data-offers="0"
               data-order="<?= 950 + (int)$lbmsAccountProductIdx ?>"
               data-href="<?= htmlspecialchars($lbmsProduct['href'], ENT_QUOTES, 'UTF-8') ?>">
            <a class="lbms__cardMain" href="<?= htmlspecialchars($lbmsProduct['href'], ENT_QUOTES, 'UTF-8') ?>" role="option" tabindex="-1">
              <span class="lbms__cardIcon lbms__cardIcon--flat lbms__cardIcon--product">
                <?php if (($lbmsProduct['image'] ?? '') !== ''): ?>
                  <img src="<?= htmlspecialchars($lbmsProduct['image'], ENT_QUOTES, 'UTF-8') ?>"
                       alt="<?= htmlspecialchars($lbmsProduct['title'], ENT_QUOTES, 'UTF-8') ?>"
                       loading="lazy"
                       onerror="this.hidden=true">
                <?php endif; ?>

              </span>
              <span class="lbms__cardBody">
                <span class="lbms__productKind"><?= t('Account Listing') ?></span>
                <span class="lbms__cardTitle"><?= htmlspecialchars($lbmsProduct['title'], ENT_QUOTES, 'UTF-8') ?></span>
                <span class="lbms__cardMeta"><?= htmlspecialchars($lbmsProduct['meta'], ENT_QUOTES, 'UTF-8') ?></span>
              </span>
              <span class="lbms__productPrice"><?= htmlspecialchars($lbmsProduct['price'], ENT_QUOTES, 'UTF-8') ?></span>
              <?php if (($lbmsProduct['game_icon'] ?? '') !== ''): ?>
                <span class="lbms__productGameIcon" aria-hidden="true"><img src="<?= htmlspecialchars($lbmsProduct['game_icon'], ENT_QUOTES, 'UTF-8') ?>" alt="" loading="lazy"></span>
              <?php endif; ?>
            </a>
          </div>
        <?php endforeach; ?>

        <?php
          $lbmsMarketplaceProductGroups = [
              ['items' => $lbmsBoostingProducts, 'type' => 'boost-product', 'cat' => 'boosting', 'kind' => t('Boosting Service'), 'start' => 1100],
              ['items' => $lbmsItemProducts, 'type' => 'item-product', 'cat' => 'items', 'kind' => t('Item Listing'), 'start' => 1300],
              ['items' => $lbmsTopupProducts, 'type' => 'topup-product', 'cat' => 'topups', 'kind' => t('Top-up Listing'), 'start' => 1500],
          ];
        ?>
        <?php foreach ($lbmsMarketplaceProductGroups as $lbmsProductGroup): ?>
          <?php foreach ($lbmsProductGroup['items'] as $lbmsProductIdx => $lbmsProduct): ?>
            <div class="lbms__card lbms__card--digital lbms__card--product"
                 data-lbms-item="<?= htmlspecialchars($lbmsProductGroup['type'], ENT_QUOTES, 'UTF-8') ?>"
                 data-search-only="1"
                 data-cats="<?= htmlspecialchars($lbmsProductGroup['cat'], ENT_QUOTES, 'UTF-8') ?>"
                 data-search="<?= htmlspecialchars($lbmsProduct['search'], ENT_QUOTES, 'UTF-8') ?>"
                 data-name="<?= htmlspecialchars(strtolower($lbmsProduct['title']), ENT_QUOTES, 'UTF-8') ?>"
                 data-offers="0"
                 data-order="<?= (int)$lbmsProductGroup['start'] + (int)$lbmsProductIdx ?>"
                 data-href="<?= htmlspecialchars($lbmsProduct['href'], ENT_QUOTES, 'UTF-8') ?>">
              <a class="lbms__cardMain" href="<?= htmlspecialchars($lbmsProduct['href'], ENT_QUOTES, 'UTF-8') ?>" role="option" tabindex="-1">
                <span class="lbms__cardIcon lbms__cardIcon--flat lbms__cardIcon--product">
                  <?php if (($lbmsProduct['image'] ?? '') !== ''): ?>
                    <img src="<?= htmlspecialchars($lbmsProduct['image'], ENT_QUOTES, 'UTF-8') ?>"
                         alt="<?= htmlspecialchars($lbmsProduct['title'], ENT_QUOTES, 'UTF-8') ?>"
                         loading="lazy"
                         onerror="this.hidden=true">
                  <?php endif; ?>
                </span>
                <span class="lbms__cardBody">
                  <span class="lbms__productKind"><?= htmlspecialchars($lbmsProductGroup['kind'], ENT_QUOTES, 'UTF-8') ?></span>
                  <span class="lbms__cardTitle"><?= htmlspecialchars($lbmsProduct['title'], ENT_QUOTES, 'UTF-8') ?></span>
                  <span class="lbms__cardMeta"><?= htmlspecialchars($lbmsProduct['meta'], ENT_QUOTES, 'UTF-8') ?></span>
                </span>
                <?php if (($lbmsProduct['price'] ?? '') !== ''): ?>
                  <span class="lbms__productPrice"><?= htmlspecialchars($lbmsProduct['price'], ENT_QUOTES, 'UTF-8') ?></span>
                <?php endif; ?>
                <?php if (($lbmsProduct['game_icon'] ?? '') !== ''): ?>
                  <span class="lbms__productGameIcon" aria-hidden="true"><img src="<?= htmlspecialchars($lbmsProduct['game_icon'], ENT_QUOTES, 'UTF-8') ?>" alt="" loading="lazy"></span>
                <?php endif; ?>
              </a>
            </div>
          <?php endforeach; ?>
        <?php endforeach; ?>

        <?php foreach ($lbmsDigitalProducts as $lbmsProductIdx => $lbmsProduct): ?>
          <div class="lbms__card lbms__card--digital lbms__card--product"
               data-lbms-item="digital"
               data-search-only="1"
               data-cats="digital"
               data-search="<?= htmlspecialchars($lbmsProduct['search'], ENT_QUOTES, 'UTF-8') ?>"
               data-name="<?= htmlspecialchars(strtolower($lbmsProduct['title']), ENT_QUOTES, 'UTF-8') ?>"
               data-offers="0"
               data-order="<?= 1000 + (int)$lbmsProductIdx ?>"
               data-href="<?= htmlspecialchars($lbmsProduct['href'], ENT_QUOTES, 'UTF-8') ?>">
            <a class="lbms__cardMain" href="<?= htmlspecialchars($lbmsProduct['href'], ENT_QUOTES, 'UTF-8') ?>" role="option" tabindex="-1">
              <span class="lbms__cardIcon lbms__cardIcon--flat lbms__cardIcon--product">
                <?php if (($lbmsProduct['image'] ?? '') !== ''): ?>
                  <img src="<?= htmlspecialchars($lbmsProduct['image'], ENT_QUOTES, 'UTF-8') ?>"
                       alt="<?= htmlspecialchars($lbmsProduct['title'], ENT_QUOTES, 'UTF-8') ?>"
                       loading="lazy"
                       onerror="this.hidden=true;this.nextElementSibling.hidden=false">
                <?php endif; ?>
                <i class="<?= $lbmsProduct['icon'] ?>" aria-hidden="true"<?= (($lbmsProduct['image'] ?? '') !== '') ? ' hidden' : '' ?>></i>
              </span>
              <span class="lbms__cardBody">
                <span class="lbms__productKind"><?= t('Listing') ?></span>
                <span class="lbms__cardTitle"><?= htmlspecialchars($lbmsProduct['title'], ENT_QUOTES, 'UTF-8') ?></span>
                <span class="lbms__cardMeta"><?= htmlspecialchars($lbmsProduct['meta'], ENT_QUOTES, 'UTF-8') ?></span>
              </span>
              <span class="lbms__productPrice"><?= htmlspecialchars($lbmsProduct['price'], ENT_QUOTES, 'UTF-8') ?></span>
            </a>
          </div>
        <?php endforeach; ?>
      </div>

      <div class="lbms__empty" id="lbmsEmpty" hidden>
        <i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i>
        <strong><?= t('No results found.') ?></strong>
        <span><?= t('Try another game, service or spelling.') ?></span>
      </div>
    </div>

    <div class="lbms__foot">
      <span class="lbms__trust"><i class="fa-solid fa-bolt" aria-hidden="true"></i><?= t('Instant Delivery') ?></span>
      <span class="lbms__trust"><i class="fa-solid fa-shield-halved" aria-hidden="true"></i><?= t('Buyer Protection') ?></span>
      <span class="lbms__trust"><i class="fa-solid fa-lock" aria-hidden="true"></i><?= t('Secure Payments') ?></span>
      <span class="lbms__trust"><i class="fa-solid fa-headset" aria-hidden="true"></i><?= t('24/7 Support') ?></span>
      <a class="lbms__tp" href="<?= htmlspecialchars($trustpilotUrl, ENT_QUOTES, 'UTF-8') ?>" target="_blank" rel="noopener" aria-label="Trustpilot">
        <b>Excellent</b>
        <span class="lbms__tpStars" aria-hidden="true">
          <i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i>
        </span>
        <span class="lbms__tpText">1,000+ <?= t('Reviews on') ?></span>
        <span class="lbms__tpLogo" aria-hidden="true"><i class="fa-solid fa-star"></i></span>
      </a>
    </div>
  </div>
</div>

    <style>
    /* ── Desktop site-settings dropdown (landing palette: #6366F1 on deep navy) ── */
    .navbar-top .dropdown-menu { position:relative; }
    .lb-setdrop{
        position:absolute; top:calc(100% + 10px); right:0; z-index:100300;
        width:340px; max-width:min(340px, calc(100vw - 32px));
        padding:8px;
        border-radius:18px;
        background:#0f1220;
        border:1px solid rgba(255,255,255,.09);
        box-shadow:0 28px 70px rgba(0,0,0,.6), inset 0 1px 0 rgba(255,255,255,.045);
        opacity:0; visibility:hidden; transform:translateY(8px);
        transition:opacity .16s ease, transform .16s ease, visibility .16s ease;
    }
    .lb-setdrop[hidden]{ display:none; }
    .lb-setdrop.is-open{ opacity:1; visibility:visible; transform:translateY(0); }
    .lb-setdrop__sec{ padding:0 6px; }
    .lb-setdrop__head{
        display:flex; align-items:center; justify-content:space-between; gap:12px;
        padding:8px 8px 10px;
    }
    .lb-setdrop__label{
        display:block;
        font-size:15px; font-weight:800; color:#fff; letter-spacing:-.01em;
    }
    /* Selections stay pending until Save is pressed — the button is the only
       thing that triggers the reload. */
    .lb-setdrop__save{
        flex:0 0 auto;
        padding:7px 16px; border:0; border-radius:10px;
        background:#6366F1; color:#fff;
        font-size:13px; font-weight:800; font-family:inherit; line-height:1;
        cursor:pointer; transition:background .15s ease, opacity .15s ease;
    }
    .lb-setdrop__save:hover{ background:#5558e8; }
    .lb-setdrop__save:disabled{
        background:rgba(255,255,255,.06); color:rgba(255,255,255,.34);
        cursor:default;
    }
    .lb-setdrop__langs{
        max-height:290px; overflow-y:auto; padding-right:4px;
        scrollbar-width:thin; scrollbar-color:rgba(99,102,241,.55) transparent;
    }
    .lb-setdrop__langs::-webkit-scrollbar{ width:6px; }
    .lb-setdrop__langs::-webkit-scrollbar-thumb{ background:rgba(99,102,241,.5); border-radius:999px; }
    .lb-setdrop__opt{
        display:flex; align-items:center; gap:12px; width:100%;
        padding:10px 12px; border:0; border-radius:12px;
        background:transparent; color:rgba(255,255,255,.78);
        font-size:14.5px; font-weight:600; font-family:inherit; text-align:left;
        cursor:pointer; transition:background .14s ease, color .14s ease;
    }
    .lb-setdrop__opt:hover{ background:rgba(255,255,255,.05); color:#fff; }
    .lb-setdrop__opt.is-active{ background:rgba(99,102,241,.14); color:#fff; }
    .lb-setdrop__opt img{
        width:26px; height:19px; object-fit:cover; border-radius:4px; flex-shrink:0;
        box-shadow:0 2px 6px rgba(0,0,0,.4);
    }
    .lb-setdrop__opt span{ flex:1; min-width:0; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; }
    .lb-setdrop__opt i{ color:#818cf8; font-size:13px; opacity:0; }
    .lb-setdrop__opt.is-active i{ opacity:1; }
    .lb-setdrop__foot{
        display:flex; align-items:center; justify-content:space-between; gap:12px;
        margin-top:8px; padding:10px 8px 6px;
        border-top:1px solid rgba(255,255,255,.07);
    }
    .lb-setdrop__cur{
        display:flex; gap:4px; padding:4px; border-radius:999px;
        background:rgba(255,255,255,.045); border:1px solid rgba(255,255,255,.08);
    }
    .lb-setdrop__curbtn{
        display:inline-flex; align-items:center; gap:7px;
        padding:7px 14px; border:0; border-radius:999px;
        background:transparent; color:rgba(255,255,255,.6);
        font-size:13px; font-weight:800; font-family:inherit; cursor:pointer;
        transition:background .15s ease, color .15s ease, box-shadow .15s ease;
    }
    .lb-setdrop__curbtn img{ width:16px; height:16px; border-radius:50%; object-fit:cover; }
    .lb-setdrop__curbtn:hover{ color:#fff; }
    .lb-setdrop__curbtn.is-active{
        background:#6366F1; color:#fff;
        box-shadow:0 6px 18px rgba(99,102,241,.35);
    }
    /* Phones keep the full modal — the pill is not rendered there anyway. */
    @media (max-width:767px){ .lb-setdrop{ display:none !important; } }
    </style>

    <div class="right">

       <div class="dropdown-menu">
            <button type="button" class="btn secondary settings-pill" id="openSiteSettings">
                <span class="settings-pill-flag" id="settings-pill-flag">
                    <img
                        id="settings-pill-flag-img"
                        src="<?= $currentLang === 'de'
                            ? '/public/assets/core/main/img/flags/de.png'
                            : '/public/assets/core/main/img/flags/en.png' ?>"
                        alt="<?= htmlspecialchars($currentLangLabel) ?>"
                    >
                </span>

                <span class="settings-pill-text">
                    <span class="settings-pill-lang" id="settings-pill-lang">
                        <?= htmlspecialchars(strtoupper($currentLang === 'de' ? 'DE' : 'EN')) ?>
                    </span>
                    <span class="settings-pill-cur" id="settings-pill-cur">
                        <?= htmlspecialchars($currentCurrency) ?>
                    </span>
                </span>

                <span class="settings-pill-chevron">▾</span>
            </button>

            <!-- Desktop settings dropdown — replaces the full-screen modal on >=768px.
                 Its language rows are cloned from #options-lang at runtime so there is
                 only one source of truth for the language list. -->
            <div class="lb-setdrop" id="siteSettingsDrop" hidden>
                <div class="lb-setdrop__head">
                    <span class="lb-setdrop__label"><?= t('Language') ?></span>
                    <button type="button" class="lb-setdrop__save" id="lbSetdropSave" disabled><?= t('Save') ?></button>
                </div>
                <div class="lb-setdrop__sec">
                    <div class="lb-setdrop__langs" id="lbSetdropLangs"></div>
                </div>
                <div class="lb-setdrop__foot">
                    <span class="lb-setdrop__label"><?= t('Currency') ?></span>
                    <div class="lb-setdrop__cur" id="lbSetdropCur">
                        <button type="button" class="lb-setdrop__curbtn" data-cur="EUR">
                            <img src="<?= ASSET_URL ?>/origin/dash/vendor/flag-icon-css/flags/1x1/eu.svg" alt="">EUR
                        </button>
                        <button type="button" class="lb-setdrop__curbtn" data-cur="USD">
                            <img src="<?= ASSET_URL ?>/origin/dash/vendor/flag-icon-css/flags/1x1/us.svg" alt="">USD
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <?php if (BOOSTER_DATA != false): ?>
            <?php
              $lbDesktopBoosterIcon = trim((string)(BOOSTER_DATA['icon'] ?? BOOSTER_DATA['avatar'] ?? BOOSTER_DATA['profile_picture'] ?? ''));
              $lbBoosterUnreadNotifications = max(0, (int)(BOOSTER_DATA['unread_notifications'] ?? BOOSTER_DATA['notification_count'] ?? 0));
              $lbBoosterUnreadChats = max(0, (int)(BOOSTER_DATA['unread_chats'] ?? BOOSTER_DATA['unread_messages'] ?? BOOSTER_DATA['chat_count'] ?? 0));
              // E-Girls are stored in the boosters table too; give them their own
              // menu links (egirl-area routes) instead of the normal boost order links.
              $lbIsEgirl = (int)(BOOSTER_DATA['is_egirl'] ?? 0) === 1;
              $lbBoosterRankLabel = $lbIsEgirl
                  ? 'GG-Girl'
                  : (trim((string)(BOOSTER_DATA['rank_name'] ?? BOOSTER_DATA['rank'] ?? BOOSTER_DATA['tier'] ?? 'Booster')) ?: 'Booster');

              // Availability switch, shares its state with the booster dashboard header.
              $lbAvailStatuses = function_exists('lb_booster_presence_statuses') ? lb_booster_presence_statuses() : [];
              $lbAvailState = (function_exists('lb_booster_presence_state') && defined('BOOSTER_ID') && BOOSTER_ID)
                  ? lb_booster_presence_state(BOOSTER_ID)
                  : ['status' => 'offline'];
              $lbAvailStatus = $lbAvailState['status'] ?? 'offline';
              $lbAvailLabel = $lbAvailStatuses[$lbAvailStatus]['label'] ?? 'Offline';
            ?>
            <div class="lb-client-tools lb-booster-tools">
              <div class="lb-client-tool" data-lb-tool id="lbBoosterNotificationTool">
                <button type="button" class="lb-client-icon-btn" data-lb-tool-toggle id="lbNotifDropdown" aria-label="<?= t('Notifications') ?>" title="<?= t('Notifications') ?>"><i class="fa-solid fa-bell" aria-hidden="true"></i><span class="lb-client-badge" id="lbNotifBadge" style="<?= $lbBoosterUnreadNotifications > 0 ? '' : 'display:none' ?>"><?= $lbBoosterUnreadNotifications > 99 ? '99+' : $lbBoosterUnreadNotifications ?></span></button>
                <div class="lb-client-tool-menu">
                  <div class="lb-client-tool-head">
                    <div class="lb-client-tool-title"><i class="fa-solid fa-bell"></i><?= t('Notifications') ?></div>
                    <button type="button" class="lb-client-tool-link lb-notif-clear" id="lbNotifMarkAll"><?= t('Mark all as read') ?></button>
                  </div>
                  <div class="lb-client-tool-body lb-dashboard-notif-body" id="lbNotifList"><div class="lb-dashboard-notif-loading"><i class="fa-solid fa-spinner fa-spin"></i><span><?= t('Loading notifications...') ?></span></div></div>
                  <div class="lb-dashboard-notif-foot"><?= t('That’s all for now') ?></div>
                </div>
              </div>
              <div class="lb-client-profile" data-lb-client-profile>
                <button type="button" class="lb-client-avatar-toggle lb-client-profile-summary" data-lb-client-profile-toggle aria-expanded="false" aria-haspopup="true" aria-label="<?= htmlspecialchars(BOOSTER_DATA['username']) ?>" title="<?= htmlspecialchars(BOOSTER_DATA['username']) ?>">
                  <span class="lb-profile-avatar lb-avail-avatar" data-lb-avail-root data-status="<?= htmlspecialchars($lbAvailStatus, ENT_QUOTES, 'UTF-8') ?>"><?php if ($lbDesktopBoosterIcon !== ''): ?><img src="<?= htmlspecialchars($lbDesktopBoosterIcon, ENT_QUOTES, 'UTF-8') ?>" alt="<?= htmlspecialchars(BOOSTER_DATA['username'], ENT_QUOTES, 'UTF-8') ?>"><?php else: ?><i class="fa-solid fa-user-ninja" aria-hidden="true"></i><?php endif; ?><span class="lb-avail-avatar-dot" aria-hidden="true"></span></span>
                  <span class="lb-profile-meta"><span class="lb-profile-name"><?= htmlspecialchars(BOOSTER_DATA['username']) ?></span><span class="lb-profile-role"><?= htmlspecialchars($lbBoosterRankLabel) ?></span></span>
                  <span class="lb-profile-chevron"><i class="fa-solid fa-chevron-down" aria-hidden="true"></i></span>
                </button>
                <div class="lb-client-dropdown" data-lb-client-dropdown>
                  <div class="lb-client-dropdown-head">
                    <div class="lb-client-dropdown-avatar">
                      <?php if ($lbDesktopBoosterIcon !== ''): ?><img src="<?= htmlspecialchars($lbDesktopBoosterIcon, ENT_QUOTES, 'UTF-8') ?>" alt=""><?php else: ?><i class="fa-solid fa-user-ninja"></i><?php endif; ?>
                    </div>
                    <div style="min-width:0">
                      <div class="lb-client-dropdown-name"><?= htmlspecialchars(BOOSTER_DATA['username']) ?></div>
                      <div class="lb-client-dropdown-rank"><?= htmlspecialchars($lbBoosterRankLabel) ?></div>
                    </div>
                  </div>
                  <div class="lb-avail-block" data-lb-avail-root data-status="<?= htmlspecialchars($lbAvailStatus, ENT_QUOTES, 'UTF-8') ?>">
                    <div class="lb-avail-block-head">
                      <span class="lb-avail-block-title"><?= t('Availability') ?></span>
                      <span class="lb-avail-block-current"><span class="lb-avail-avatar-dot"></span><span data-lb-avail-label><?= htmlspecialchars($lbAvailLabel) ?></span></span>
                    </div>
                    <div class="lb-avail-block-options">
                      <?php foreach ($lbAvailStatuses as $lbAvailKey => $lbAvailMeta): ?>
                      <button type="button" class="lb-avail-block-btn<?= $lbAvailKey === $lbAvailStatus ? ' is-active' : '' ?>" data-lb-avail-option="<?= htmlspecialchars($lbAvailKey, ENT_QUOTES, 'UTF-8') ?>" title="<?= htmlspecialchars($lbAvailMeta['sub'], ENT_QUOTES, 'UTF-8') ?>">
                        <span class="lb-avail-block-dot lb-avail-block-dot--<?= htmlspecialchars($lbAvailKey, ENT_QUOTES, 'UTF-8') ?>"></span>
                        <?= htmlspecialchars($lbAvailMeta['label']) ?>
                      </button>
                      <?php endforeach; ?>
                    </div>
                  </div>
                  <div class="lb-client-dropdown-grid">
                  <?php if ($lbIsEgirl): ?>
                    <a class="lb-client-dropdown-link" href="/booster-area/egirl-dashboard"><i class="fa-solid fa-grid-2"></i><span><?= t('Dashboard') ?></span></a>
                    <a class="lb-client-dropdown-link" href="/booster-area/egirl-panel"><i class="fa-solid fa-bolt"></i><span><?= t('Available Bookings') ?></span></a>
                    <a class="lb-client-dropdown-link" href="/booster-area/egirl-orders"><i class="fa-solid fa-list-check"></i><span><?= t('My Bookings') ?></span></a>
                    <a class="lb-client-dropdown-link" href="/booster-area/egirl-services"><i class="fa-solid fa-hand-holding-heart"></i><span><?= t('Services') ?></span></a>
                    <a class="lb-client-dropdown-link" href="/booster-area/egirl-payments"><i class="fa-solid fa-credit-card"></i><span><?= t('Payments') ?></span></a>
                    <a class="lb-client-dropdown-link" href="/booster-area/egirl-profile"><i class="fa-solid fa-user"></i><span><?= t('Profile') ?></span></a>
                  <?php else: ?>
                    <a class="lb-client-dropdown-link" href="/booster-area/dashboard"><i class="fa-solid fa-grid-2"></i><span><?= t('Dashboard') ?></span></a>
                    <a class="lb-client-dropdown-link" href="/booster-area/orders-panel"><i class="fa-solid fa-bolt"></i><span><?= t('Orders Panel') ?></span></a>
                    <a class="lb-client-dropdown-link" href="/booster-area/orders"><i class="fa-solid fa-list-check"></i><span><?= t('My Orders') ?></span></a>
                    <a class="lb-client-dropdown-link" href="/booster-area/payments"><i class="fa-solid fa-credit-card"></i><span><?= t('Payments') ?></span></a>
                    <a class="lb-client-dropdown-link" href="/booster-area/profile"><i class="fa-solid fa-user"></i><span><?= t('Profile') ?></span></a>
                    <a class="lb-client-dropdown-link" href="/booster-area/settings"><i class="fa-solid fa-gear"></i><span><?= t('Settings') ?></span></a>
                  <?php endif; ?>
                  </div>
                  <div class="lb-client-dropdown-foot">
                    <a href="/booster-area/<?= $lbIsEgirl ? 'egirl-dashboard' : 'dashboard' ?>"><i class="fa-solid fa-arrow-up-right-from-square"></i><?= t('Open dashboard') ?></a>
                    <a class="lb-client-logout" href="/logout"><i class="fa-solid fa-right-from-bracket"></i><?= t('Logout') ?></a>
                  </div>
                </div>
              </div>
            </div>
            <?= $this->insert('shared/booster-availability-runtime') ?>
        <?php elseif (defined('SELLER_DATA') && SELLER_DATA != false): ?>
            <?php
              $lbSellerIcon = trim((string)(SELLER_DATA['icon'] ?? SELLER_DATA['avatar'] ?? SELLER_DATA['profile_picture'] ?? ''));
              $lbSellerUnreadNotifications = max(0, (int)(SELLER_DATA['unread_notifications'] ?? SELLER_DATA['notification_count'] ?? 0));

              // Rangname: die Session haengt ihn (anders als beim Booster) nicht an,
              // deshalb hier defensiv nachschlagen.
              $lbSellerRankLabel = trim((string)(SELLER_DATA['rank_name'] ?? SELLER_DATA['rank'] ?? ''));
              if ($lbSellerRankLabel === '' && !empty(SELLER_DATA['seller_rank_id']) && function_exists('db_get_row')) {
                  try {
                      $lbSellerRankRow = db_get_row('seller_ranks', ['id' => (int)SELLER_DATA['seller_rank_id'], 'select' => 'name']);
                      if (is_array($lbSellerRankRow) && !empty($lbSellerRankRow['name'])) {
                          $lbSellerRankLabel = (string)$lbSellerRankRow['name'];
                      }
                  } catch (Throwable $e) {
                      // Header darf daran nicht sterben.
                  }
              }
              if ($lbSellerRankLabel === '') $lbSellerRankLabel = 'Seller';
            ?>
            <div class="lb-client-tools lb-seller-tools">
              <div class="lb-client-tool" data-lb-tool id="lbSellerNotificationTool">
                <button type="button" class="lb-client-icon-btn" data-lb-tool-toggle id="lbNotifDropdown" aria-label="<?= t('Notifications') ?>" title="<?= t('Notifications') ?>"><i class="fa-solid fa-bell" aria-hidden="true"></i><span class="lb-client-badge" id="lbNotifBadge" style="<?= $lbSellerUnreadNotifications > 0 ? '' : 'display:none' ?>"><?= $lbSellerUnreadNotifications > 99 ? '99+' : $lbSellerUnreadNotifications ?></span></button>
                <div class="lb-client-tool-menu">
                  <div class="lb-client-tool-head">
                    <div class="lb-client-tool-title"><i class="fa-solid fa-bell"></i><?= t('Notifications') ?></div>
                    <button type="button" class="lb-client-tool-link lb-notif-clear" id="lbNotifMarkAll"><?= t('Mark all as read') ?></button>
                  </div>
                  <div class="lb-client-tool-body lb-dashboard-notif-body" id="lbNotifList"><div class="lb-dashboard-notif-loading"><i class="fa-solid fa-spinner fa-spin"></i><span><?= t('Loading notifications...') ?></span></div></div>
                  <div class="lb-dashboard-notif-foot"><?= t('That’s all for now') ?></div>
                </div>
              </div>
              <div class="lb-client-profile" data-lb-client-profile>
                <button type="button" class="lb-client-avatar-toggle lb-client-profile-summary" data-lb-client-profile-toggle aria-expanded="false" aria-haspopup="true" aria-label="<?= htmlspecialchars(SELLER_DATA['username']) ?>" title="<?= htmlspecialchars(SELLER_DATA['username']) ?>">
                  <span class="lb-profile-avatar"><?php if ($lbSellerIcon !== ''): ?><img src="<?= htmlspecialchars($lbSellerIcon, ENT_QUOTES, 'UTF-8') ?>" alt="<?= htmlspecialchars(SELLER_DATA['username'], ENT_QUOTES, 'UTF-8') ?>"><?php else: ?><i class="fa-solid fa-store" aria-hidden="true"></i><?php endif; ?></span>
                  <span class="lb-profile-meta"><span class="lb-profile-name"><?= htmlspecialchars(SELLER_DATA['username']) ?></span><span class="lb-profile-role"><?= htmlspecialchars($lbSellerRankLabel) ?></span></span>
                  <span class="lb-profile-chevron"><i class="fa-solid fa-chevron-down" aria-hidden="true"></i></span>
                </button>
                <div class="lb-client-dropdown" data-lb-client-dropdown>
                  <div class="lb-client-dropdown-head">
                    <div class="lb-client-dropdown-avatar">
                      <?php if ($lbSellerIcon !== ''): ?><img src="<?= htmlspecialchars($lbSellerIcon, ENT_QUOTES, 'UTF-8') ?>" alt=""><?php else: ?><i class="fa-solid fa-store"></i><?php endif; ?>
                    </div>
                    <div style="min-width:0">
                      <div class="lb-client-dropdown-name"><?= htmlspecialchars(SELLER_DATA['username']) ?></div>
                      <div class="lb-client-dropdown-rank"><?= htmlspecialchars($lbSellerRankLabel) ?></div>
                    </div>
                  </div>
                  <div class="lb-client-dropdown-grid">
                    <a class="lb-client-dropdown-link" href="/seller-area/dashboard"><i class="fa-solid fa-grid-2"></i><span><?= t('Dashboard') ?></span></a>
                    <a class="lb-client-dropdown-link" href="/seller-area/chat"><i class="fa-solid fa-comments"></i><span><?= t('Chat Inbox') ?></span></a>
                    <a class="lb-client-dropdown-link" href="/seller-area/accounts"><i class="fa-solid fa-layer-group"></i><span><?= t('My Accounts') ?></span></a>
                    <a class="lb-client-dropdown-link" href="/seller-area/account-orders"><i class="fa-solid fa-cart-shopping"></i><span><?= t('Account Orders') ?></span></a>
                    <a class="lb-client-dropdown-link" href="/seller-area/items"><i class="fa-solid fa-gem"></i><span><?= t('My Items') ?></span></a>
                    <a class="lb-client-dropdown-link" href="/seller-area/item-orders"><i class="fa-solid fa-list-check"></i><span><?= t('Item Orders') ?></span></a>
                    <a class="lb-client-dropdown-link" href="/seller-area/profile"><i class="fa-solid fa-user"></i><span><?= t('Profile') ?></span></a>
                  </div>
                  <div class="lb-client-dropdown-foot">
                    <a href="/seller-area/dashboard"><i class="fa-solid fa-arrow-up-right-from-square"></i><?= t('Open dashboard') ?></a>
                    <a class="lb-client-logout" href="/logout"><i class="fa-solid fa-right-from-bracket"></i><?= t('Logout') ?></a>
                  </div>
                </div>
              </div>
            </div>
        <?php elseif (CLIENT_DATA != false): ?>
            <?php
              $lbDesktopClientIcon = !empty(CLIENT_DATA['icon']) ? (string) CLIENT_DATA['icon'] : '';
              $lbClientUnreadNotifications = max(0, (int)(CLIENT_DATA['unread_notifications'] ?? CLIENT_DATA['notification_count'] ?? 0));
              $lbClientUnreadChats = max(0, (int)(CLIENT_DATA['unread_chats'] ?? CLIENT_DATA['unread_messages'] ?? CLIENT_DATA['chat_count'] ?? 0));
              $lbClientRankLabel = trim((string)(CLIENT_DATA['rank_name'] ?? CLIENT_DATA['rank'] ?? CLIENT_DATA['tier'] ?? 'Member')) ?: 'Member';
            ?>
            <div class="lb-client-tools">
              <div class="lb-client-tool" data-lb-tool id="lbClientNotificationTool">
                <button type="button" class="lb-client-icon-btn" data-lb-tool-toggle id="lbNotifDropdown" aria-label="<?= t('Notifications') ?>" title="<?= t('Notifications') ?>"><i class="fa-solid fa-bell" aria-hidden="true"></i><span class="lb-client-badge" id="lbNotifBadge" style="<?= $lbClientUnreadNotifications > 0 ? '' : 'display:none' ?>"><?= $lbClientUnreadNotifications > 99 ? '99+' : $lbClientUnreadNotifications ?></span></button>
                <div class="lb-client-tool-menu">
                  <div class="lb-client-tool-head">
                    <div class="lb-client-tool-title"><i class="fa-solid fa-bell"></i><?= t('Notifications') ?></div>
                    <button type="button" class="lb-client-tool-link lb-notif-clear" id="lbNotifMarkAll"><?= t('Mark all as read') ?></button>
                  </div>
                  <div class="lb-client-tool-body lb-dashboard-notif-body" id="lbNotifList"><div class="lb-dashboard-notif-loading"><i class="fa-solid fa-spinner fa-spin"></i><span><?= t('Loading notifications...') ?></span></div></div>
                  <div class="lb-dashboard-notif-foot"><?= t('That’s all for now') ?></div>
                </div>
              </div>
              <div class="lb-client-profile" data-lb-client-profile>
                <button type="button" class="lb-client-avatar-toggle lb-client-profile-summary" data-lb-client-profile-toggle aria-expanded="false" aria-haspopup="true" aria-label="<?= htmlspecialchars(CLIENT_DATA['username']) ?>" title="<?= htmlspecialchars(CLIENT_DATA['username']) ?>">
                  <span class="lb-profile-avatar"><?php if ($lbDesktopClientIcon !== ''): ?><img src="<?= htmlspecialchars($lbDesktopClientIcon, ENT_QUOTES, 'UTF-8') ?>" alt="<?= htmlspecialchars(CLIENT_DATA['username'], ENT_QUOTES, 'UTF-8') ?>"><?php else: ?><i class="fa-solid fa-user" aria-hidden="true"></i><?php endif; ?></span>
                  <span class="lb-profile-meta"><span class="lb-profile-name"><?= htmlspecialchars(CLIENT_DATA['username']) ?></span><span class="lb-profile-role"><?= htmlspecialchars($lbClientRankLabel) ?></span></span>
                  <span class="lb-profile-chevron"><i class="fa-solid fa-chevron-down" aria-hidden="true"></i></span>
                </button>
                <div class="lb-client-dropdown" data-lb-client-dropdown>
                  <div class="lb-client-dropdown-head">
                    <div class="lb-client-dropdown-avatar">
                      <?php if ($lbDesktopClientIcon !== ''): ?><img src="<?= htmlspecialchars($lbDesktopClientIcon, ENT_QUOTES, 'UTF-8') ?>" alt=""><?php else: ?><i class="fa-solid fa-user"></i><?php endif; ?>
                    </div>
                    <div style="min-width:0">
                      <div class="lb-client-dropdown-name"><?= htmlspecialchars(CLIENT_DATA['username']) ?></div>
                      <div class="lb-client-dropdown-rank"><?= htmlspecialchars($lbClientRankLabel) ?></div>
                    </div>
                  </div>
                  <div class="lb-client-dropdown-balances">
                    <div class="lb-client-balance-row">
                      <span class="lb-client-balance-label"><span class="lb-client-balance-icon"><i class="fa-solid fa-coins"></i></span><?= t('LB Coins') ?></span>
                      <span class="lb-client-balance-value"><?= htmlspecialchars($lbHeaderAmount($lbHeaderClientCoins), ENT_QUOTES, 'UTF-8') ?></span>
                    </div>
                    <div class="lb-client-balance-row">
                      <span class="lb-client-balance-label"><span class="lb-client-balance-icon rewards"><i class="fa-solid fa-gift"></i></span><?= t('Reward Points') ?></span>
                      <span class="lb-client-balance-value"><?= htmlspecialchars($lbHeaderAmount($lbHeaderRewardPoints), ENT_QUOTES, 'UTF-8') ?></span>
                    </div>
                  </div>
                  <div class="lb-client-dropdown-grid">
                    <a class="lb-client-dropdown-link" href="/profile/overview"><i class="fa-solid fa-grid-2"></i><span><?= t('Dashboard') ?></span></a>
                    <a class="lb-client-dropdown-link" href="/profile/orders"><i class="fa-solid fa-cart-shopping"></i><span><?= t('My Orders') ?></span></a>
                    <a class="lb-client-dropdown-link" href="/profile/chat"><i class="fa-solid fa-comments"></i><span><?= t('My Chats') ?></span></a>
                    <a class="lb-client-dropdown-link" href="/profile/payments"><i class="fa-solid fa-credit-card"></i><span><?= t('Payments') ?></span></a>
                    <a class="lb-client-dropdown-link" href="/profile/rewards"><i class="fa-solid fa-gift"></i><span><?= t('Rewards') ?></span></a>
                    <a class="lb-client-dropdown-link" href="/profile/settings"><i class="fa-solid fa-gear"></i><span><?= t('Settings') ?></span></a>
                  </div>
                  <div class="lb-client-dropdown-foot">
                    <a href="/profile/overview"><i class="fa-solid fa-arrow-up-right-from-square"></i><?= t('Open profile') ?></a>
                    <a class="lb-client-logout" href="/logout"><i class="fa-solid fa-right-from-bracket"></i><?= t('Logout') ?></a>
                  </div>
                </div>
              </div>
            </div>
        <?php else: ?>
            <button type="button" class="btn primary" id="login-btn" data-login-trigger="1">
                <i class="fas fa-right-to-bracket" aria-hidden="true"></i>
                <?= t('Login') ?>
            </button>
        <?php endif; ?>
    </div>
</nav>

<script id="lb-desktop-client-profile-menu">
(function(){
  function init(){
    var tools=Array.from(document.querySelectorAll('[data-lb-tool]'));
    tools.forEach(function(tool){
      var btn=tool.querySelector('[data-lb-tool-toggle]');
      if(!btn) return;
      btn.addEventListener('click',function(e){
        e.stopPropagation();
        tools.forEach(function(other){if(other!==tool) other.classList.remove('is-open');});
        var p=document.querySelector('[data-lb-client-profile]'); if(p) p.classList.remove('is-open');
        tool.classList.toggle('is-open');
      });
    });
    var profile=document.querySelector('[data-lb-client-profile]');
    if(!profile) return;
    var toggle=profile.querySelector('[data-lb-client-profile-toggle]');
    if(!toggle) return;
    toggle.addEventListener('click',function(e){
      e.preventDefault();e.stopPropagation();
      tools.forEach(function(tool){tool.classList.remove('is-open');});
      var open=profile.classList.toggle('is-open');
      toggle.setAttribute('aria-expanded',open?'true':'false');
    });
    document.addEventListener('click',function(e){
      if(!profile.contains(e.target)){
        profile.classList.remove('is-open');
      }
      if(!e.target.closest('[data-lb-tool]')){
        tools.forEach(function(tool){tool.classList.remove('is-open');});
        toggle.setAttribute('aria-expanded','false');
      }
    });
    document.addEventListener('keydown',function(e){
      if(e.key==='Escape'){
        profile.classList.remove('is-open');
        toggle.setAttribute('aria-expanded','false');
      }
    });
  }
  if(document.readyState==='loading') document.addEventListener('DOMContentLoaded',init,{once:true}); else init();
})();
</script>

<script>
(function(){
  // Sellers were missing from this guard, so for a seller-only login the whole
  // notification script bailed out on line 1: no fetch ever ran and the panel
  // was stuck on "Loading notifications..." forever.
  if(!(<?= (defined('BOOSTER_DATA') && BOOSTER_DATA != false) || (defined('SELLER_DATA') && SELLER_DATA != false) || (defined('CLIENT_DATA') && CLIENT_DATA != false) ? 'true' : 'false' ?>)) return;
  const AJAX_URL = "<?= AJAX_URL ?>";
  // Sellers were missing here entirely. Anyone who had both a seller and a
  // client account got scope 'client', so their seller notifications never
  // appeared in this header — it looked like nothing loaded at all.
  // Order follows the existing rule that a dashboard role beats the client role.
  const scope = <?= (defined('BOOSTER_DATA') && BOOSTER_DATA != false)
      ? "'booster'"
      : ((defined('SELLER_DATA') && SELLER_DATA != false) ? "'seller'" : "'client'") ?>;
  const orderBase = scope === 'booster' ? "<?= defined('BSTR_URL') ? BSTR_URL : BASE_URL . '/booster-area' ?>" : "<?= BASE_URL ?>";

  function escapeHtml(s){ return String(s||'').replace(/[&<>"']/g, m => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'}[m])); }
  function parseData(d){ if(!d) return {}; try{ return (typeof d === 'string') ? JSON.parse(d) : d; }catch(e){ return {}; } }
  // Some notifications store plain values (e.g. egirl order ids). Blindly running
  // atob() on those produced binary garbage in links like /order/%C3%97%C2%8D.
  function decodeB64(v){ const s=String(v||'').trim(); if(!s) return ''; if(/^\d+$/.test(s)) return s; if(!/^[A-Za-z0-9+/]+={0,2}$/.test(s) || (s.length % 4) !== 0) return s; try { const bin=atob(s); if(/[\x00-\x08\x0b\x0c\x0e-\x1f]/.test(bin)) return s; const bytes=Uint8Array.from(bin,c=>c.charCodeAt(0)); return new TextDecoder('utf-8',{fatal:true}).decode(bytes); } catch(e){ return s; } }
  function decodeReason(v){ if(!v) return ''; let s=String(v).trim(); if(!s) return ''; if(/^[a-z][a-z0-9_]+$/.test(s)){ s=s.replace(/_/g,' ').replace(/^\w/, c => c.toUpperCase()); } return s; }
  function lbDecodeMaybeBase64Number(v){ if(v===null||v===undefined) return null; if(typeof v==='number') return v; let s=String(v).trim(); if(!s) return null; if(!/^-?\d+(?:\.\d+)?$/.test(s) && /^[A-Za-z0-9+/=]+$/.test(s) && (s.length % 4 === 0)){ try{ const dec=atob(s); if(/^-?\d+(?:\.\d+)?$/.test(dec.trim())) s=dec.trim(); }catch(e){} } s=s.replace(/EUR|€/gi,'').trim().replace(',','.'); const n=Number(s); return Number.isFinite(n)?n:null; }
  function lbFormatEurFromCents(v){ const n=lbDecodeMaybeBase64Number(v); if(n===null) return ''; const eur=Math.round(n)/100; return eur.toFixed(2)+' €'; }
  async function post(data){ const form=new URLSearchParams(); form.append('scope', scope); Object.entries(data).forEach(([k,val])=>form.append(k, String(val))); const res=await fetch(AJAX_URL,{method:'POST',headers:{'Content-Type':'application/x-www-form-urlencoded'},body:form.toString()}); return await res.json(); }
  function setBadge(n){ const badge=document.getElementById('lbNotifBadge'); if(!badge) return; const v=parseInt(n||0,10); if(v>0){ badge.textContent=v>99?'99+':String(v); badge.style.display='flex'; } else { badge.textContent='0'; badge.style.display='none'; } }
  function mapNotif(row){
    const type=row.type||''; const data=parseData(row.data); const created=row.created_at||''; let time='';
    if(created){ const utcStr=created.trim().replace(' ','T').replace(/(\.\d+)?$/, 'Z'); try{ const d=new Date(utcStr); if(!isNaN(d)){ const pad=n=>String(n).padStart(2,'0'); time=d.getFullYear()+'-'+pad(d.getMonth()+1)+'-'+pad(d.getDate())+' '+pad(d.getHours())+':'+pad(d.getMinutes()); } }catch(e){ time=created.slice(0,16).replace('T',' '); } }
    const seen=parseInt(row.is_seen||0,10)===1; const isBooster=scope==='booster'; const isClient=scope==='client'; let title='Notification'; let subtitle=type; let icon='fa-solid fa-bell'; let url=''; const isEgirlNotif=String(type).indexOf('egirl_')===0;
    const encodedOrderId=data.order_id||data.orderId||null; if(encodedOrderId){ const oid=decodeB64(encodedOrderId); if(oid) url=isEgirlNotif ? (orderBase + '/egirl-order/' + oid) : (orderBase + '/order/' + oid); }
    const oidNum=encodedOrderId ? (parseInt(decodeB64(encodedOrderId),10)||0) : 0; const oidStr=oidNum>0 ? (' #' + oidNum) : ''; const decodeName=v=>v ? (decodeB64(String(v)).trim()||null) : null; const clientName=decodeName(data.client_username||data.client||data.customer||null); const boosterName=decodeName(data.booster_username||data.booster||null);
    if(type==='ds_msg_notif_client'){ title='New message'; subtitle=boosterName ? (boosterName + ' sent you a message') : 'Your booster sent you a message'; icon='fa-solid fa-comment-dots'; }
    else if(type==='poke_client'){ title='You were poked'; subtitle=(boosterName ? boosterName : 'Your booster') + ' poked you'; icon='fa-solid fa-hand-point-up'; }
    else if(type==='invoice_paid' || type==='invoice_payment_succeeded'){ title='Payment received'; subtitle='Your payment was received'; icon='fa-solid fa-receipt'; if(!url) url=orderBase + '/profile/billing'; }
    else if(type==='client_custom_invoice'){ title='New invoice'; subtitle='You have a new invoice to pay'; icon='fa-solid fa-file-invoice'; if(!url) url=orderBase + '/profile/billing'; }
    else if(type==='order_refunded'){ title='Order refunded'; subtitle='Your order' + oidStr + ' has been refunded'; icon='fa-solid fa-rotate-left'; }
    else if(type==='ds_msg_notif_booster'){ title='New message'; subtitle=clientName ? (clientName + ' sent you a message') : 'A customer sent you a message'; icon='fa-solid fa-comment-dots'; }
    else if(type==='poke_booster'){ title='You were poked'; subtitle=(clientName ? clientName : 'A customer') + ' poked you'; icon='fa-solid fa-hand-point-up'; }
    else if(type==='booster_money_added'){ title='Payout balance updated'; const eurAdd=lbFormatEurFromCents(data.amount); const balAdd=lbFormatEurFromCents(data.balance || data.new_balance); const reasonAdd=decodeReason(data.reason); subtitle='+ ' + (eurAdd || '?') + ' added to your balance' + (reasonAdd ? ' · ' + reasonAdd : '') + (balAdd ? ' · Available for payout: ' + balAdd : ''); icon='fa-solid fa-circle-plus'; }
    else if(type==='booster_money_fined'){ title='⚠️ Fine applied'; const eurFine=lbFormatEurFromCents(data.amount || data.money_fined || data.money_removed); const balFine=lbFormatEurFromCents(data.balance || data.new_balance); const reasonFine=decodeReason(data.reason); subtitle='- ' + (eurFine || '?') + (reasonFine ? ' · ' + reasonFine : '') + (balFine ? ' · Available for payout: ' + balFine : ''); icon='fa-solid fa-triangle-exclamation'; }
    else if(type==='booster_balance_withdrawn'){ title='Payout processed'; const eurWith=lbFormatEurFromCents(data.amount || data.withdrawn); const balWith=lbFormatEurFromCents(data.balance || data.new_balance); subtitle='- ' + (eurWith || '?') + ' withdrawn from your payout balance' + (balWith ? ' · Remaining: ' + balWith : ''); icon='fa-solid fa-money-check-dollar'; }
    else if(type==='order_claimed'){ title='Order claimed'; subtitle=isClient ? ('Your order' + oidStr + ' has been claimed') : ('You claimed order' + oidStr); icon='fa-solid fa-play'; }
    else if(type==='order_paused'){ title='Order paused'; subtitle=isClient ? ('Your order' + oidStr + ' has been paused') : ('Order' + oidStr + ' has been paused'); icon='fa-solid fa-pause'; }
    else if(type==='order_completed'){ title='Order completed'; icon='fa-solid fa-check'; if(data.payout_cents || data.payout || data.amount){ const eur=lbFormatEurFromCents(data.payout_cents || data.payout || data.amount); subtitle='You earned' + (eur ? ' (+' + eur + ')' : '') + (oidStr ? ' · Order' + oidStr : ''); } else { subtitle=isClient ? ('Your order' + oidStr + ' has been completed') : ('Order' + oidStr + ' has been completed'); } }
    else if(type==='booster_request' || type==='booster_ready_request'){ title='Boost request'; subtitle=clientName ? (clientName + ' requested you for order' + oidStr) : ('You received a boost request' + oidStr); icon='fa-solid fa-bolt'; }
    else if(type==='booster_assigned'){ title='Order assigned'; subtitle=isClient ? ('A booster has been assigned to your order' + oidStr) : (clientName ? ('You were assigned to an order from ' + clientName + oidStr) : ('You were assigned to order' + oidStr)); icon='fa-solid fa-user-check'; }
    else if(type==='booster_removed'){ title='Boost update'; subtitle=isClient ? ('Your booster was removed from order' + oidStr) : ('You were removed from order' + oidStr); icon='fa-solid fa-user-minus'; }
    else if(type==='booster_request_declined'){ title='Boost update'; subtitle='You declined a boost request' + oidStr; icon='fa-solid fa-user-xmark'; }
    else if(type==='egirl_booking_paid'){ title='New booking paid'; subtitle=clientName ? (clientName + ' booked you' + oidStr) : ('You received a new paid booking' + oidStr); icon='fa-solid fa-calendar-check'; }
    else if(type==='egirl_order_assigned'){ title='Booking assigned'; subtitle=clientName ? ('You were assigned to a booking from ' + clientName + oidStr) : ('You were assigned to booking' + oidStr); icon='fa-solid fa-user-check'; }
    else if(type==='egirl_order_removed' || type==='egirl_removed_from_order'){ title='Booking removed'; subtitle='You were removed from booking' + oidStr; icon='fa-solid fa-user-minus'; }
    else if(type==='egirl_order_paused'){ title='Booking paused'; subtitle='Booking' + oidStr + ' has been paused'; icon='fa-solid fa-pause'; }
    else if(type==='egirl_order_unpaused'){ title='Booking unpaused'; subtitle='Booking' + oidStr + ' has been resumed'; icon='fa-solid fa-play'; }
    else if(type==='egirl_new_message'){ title='New message'; subtitle=clientName ? (clientName + ' sent you a message') : 'You received a new booking message'; icon='fa-solid fa-comment-dots'; }
    else if(type==='egirl_session_completed_booster'){ title='Booking completed'; const eur=lbFormatEurFromCents(data.amount || data.payout || data.payout_cents); subtitle='You earned' + (eur ? ' (+' + eur + ')' : '') + (oidStr ? ' · Booking' + oidStr : ''); icon='fa-solid fa-circle-check'; }
    else if(type==='egirl_tip_received'){ title='Tip received'; const eurTip=lbFormatEurFromCents(data.amount); subtitle='You received a tip' + (eurTip ? ' (+' + eurTip + ')' : ''); icon='fa-solid fa-hand-holding-heart'; }
    else if(type==='egirl_fine_received'){ title='⚠️ Fine applied'; const eurFineEg=lbFormatEurFromCents(data.amount); const reasonEg=decodeReason(data.reason); subtitle='- ' + (eurFineEg || '?') + (reasonEg ? ' · ' + reasonEg : ''); icon='fa-solid fa-triangle-exclamation'; }
    else if(type==='egirl_balance_added'){ title='Balance updated'; const eurBal=lbFormatEurFromCents(data.amount); const reasonBal=decodeReason(data.reason); subtitle='+ ' + (eurBal || '?') + ' added to your balance' + (reasonBal ? ' · ' + reasonBal : ''); icon='fa-solid fa-circle-plus'; }
    else if(type==='egirl_payout_received'){ title='Payout received'; const eurPay=lbFormatEurFromCents(data.amount); subtitle='Your payout was paid' + (eurPay ? ' (' + eurPay + ')' : ''); icon='fa-solid fa-money-check-dollar'; }
    else if(type==='egirl_payout_rejected'){ title='Payout rejected'; const reasonReject=decodeReason(data.reason); subtitle='Your payout request was rejected' + (reasonReject ? ' · ' + reasonReject : ''); icon='fa-solid fa-circle-xmark'; }
    // Seller notifications reach this header too now. Without these cases they
    // would fall through to the generic branch and show the raw type string
    // (e.g. "seller_item_sold") as their subtitle.
    else if(type==='welcome_seller'){ title='Seller account approved'; subtitle='Your seller account is ready to use'; icon='fa-solid fa-store'; if(!url) url=orderBase + '/seller-area/dashboard'; }
    else if(type==='seller_account_sold' || type==='account_order_paid'){ title='Account sold'; subtitle='You made a new account sale'; icon='fa-solid fa-shield-halved'; if(!url) url=orderBase + '/seller-area/account-orders'; }
    else if(type==='seller_item_sold' || type==='item_sold' || type==='item_order_paid'){ title='Item sold'; subtitle='You made a new item sale'; icon='fa-solid fa-gift'; if(!url) url=orderBase + '/seller-area/item-orders'; }
    else if(type==='topup_sold'){ title='Top up sold'; subtitle='You made a new top up sale'; icon='fa-solid fa-coins'; if(!url) url=orderBase + '/seller-area/top-up-orders'; }
    else if(type==='digital_good_sold'){ title='Digital good sold'; subtitle='You made a new digital goods sale'; icon='fa-solid fa-box-open'; if(!url) url=orderBase + '/seller-area/digital-goods/orders'; }
    else if(type==='seller_chat_message' || type==='seller_new_message'){ title='New customer message'; subtitle=clientName ? (clientName + ' sent you a message') : 'A customer sent you a message'; icon='fa-solid fa-comments'; if(!url) url=orderBase + '/seller-area/chat'; }
    else if(type==='seller_unread_message'){ title='Unread customer message'; subtitle=clientName ? (clientName + ' is still waiting for your reply') : 'A customer is waiting for your reply'; icon='fa-solid fa-comment-dots'; if(!url) url=orderBase + '/seller-area/chat'; }
    else if(type==='poke_seller'){ title='You were poked'; subtitle=(clientName ? clientName : 'A customer') + ' is waiting for your response'; icon='fa-solid fa-hand-point-up'; }
    else if(type==='seller_payout_request' || type==='seller_payout_paid' || type==='seller_payout_rejected'){ title=(type==='seller_payout_paid' ? 'Payout paid' : (type==='seller_payout_rejected' ? 'Payout rejected' : 'Payout requested')); const eurSellerPayout=lbFormatEurFromCents(data.amount_cents || data.amount); subtitle=eurSellerPayout ? ('Amount ' + eurSellerPayout) : 'Your payout status changed'; icon=(type==='seller_payout_rejected' ? 'fa-solid fa-circle-xmark' : 'fa-solid fa-money-bill-transfer'); if(!url) url=orderBase + '/seller-area/payout'; }
    else if(type==='seller_money_added' || type==='seller_money_fined'){ const isFine=(type==='seller_money_fined'); title=isFine ? '⚠️ Fine applied' : 'Balance updated'; const eurSeller=lbFormatEurFromCents(data.amount); subtitle=eurSeller ? ((isFine ? '-' : '+') + eurSeller) : 'Your seller balance changed'; icon=isFine ? 'fa-solid fa-sack-xmark' : 'fa-solid fa-sack-dollar'; if(!url) url=orderBase + '/seller-area/payments'; }
    return {id: row.id, title, subtitle, icon, url, time, seen};
  }
  function render(rows){ const list=document.getElementById('lbNotifList'); if(!list) return; if(!rows || !rows.length){ list.innerHTML='<div class="lb-dashboard-notif-empty">No notifications yet.</div>'; return; } const items=rows.map(mapNotif); list.innerHTML=items.map(n=>{ const href=n.url ? n.url : 'javascript:;'; const target=n.url ? '' : ' tabindex="-1"'; return `<a class="lb-dashboard-notif-item" data-id="${n.id}" href="${href}" ${target}><div class="lb-dashboard-notif-icon"><i class="${escapeHtml(n.icon)}"></i></div><div class="lb-dashboard-notif-main"><div class="lb-dashboard-notif-top"><p class="lb-dashboard-notif-title">${escapeHtml(n.title)}</p><div class="lb-dashboard-notif-actions"><div class="lb-dashboard-notif-time">${escapeHtml(n.time)}</div>${!n.seen ? '<span class="lb-dashboard-notif-dot"></span>' : ''}${!n.seen ? '<button class="lb-dashboard-notif-read" type="button" data-id="'+n.id+'" title="Mark as read"><i class="fa-solid fa-check"></i></button>' : ''}</div></div><p class="lb-dashboard-notif-sub">${escapeHtml(n.subtitle)}</p></div></a>`; }).join(''); }
  const listEl=document.getElementById('lbNotifList');
  if(listEl){ listEl.addEventListener('click', async (e)=>{ const btn=e.target.closest('.lb-dashboard-notif-read'); if(!btn) return; e.preventDefault(); e.stopPropagation(); const id=parseInt(btn.getAttribute('data-id')||'0',10); if(!id) return; btn.disabled=true; try{ const r=await post({action:'notifications_mark_read', id}); if(r && r.success){ const item=btn.closest('.lb-dashboard-notif-item'); if(item){ item.querySelectorAll('.lb-dashboard-notif-dot, .lb-dashboard-notif-read').forEach(el=>el.remove()); } const b=document.getElementById('lbNotifBadge'); const cur=b ? parseInt(b.textContent||'0',10) : 0; if(cur>0) setBadge(cur-1); } else { btn.disabled=false; } } catch(err){ btn.disabled=false; } }); listEl.addEventListener('click', (e)=>{ const a=e.target.closest('a.lb-dashboard-notif-item'); if(!a) return; const unread=a.querySelector('.lb-dashboard-notif-dot'); if(!unread) return; const id=parseInt(a.getAttribute('data-id')||'0',10); if(!id) return; post({action:'notifications_mark_read', id}).then(r=>{ if(r && r.success){ a.querySelectorAll('.lb-dashboard-notif-dot, .lb-dashboard-notif-read').forEach(el=>el.remove()); const b=document.getElementById('lbNotifBadge'); const cur=b ? parseInt(b.textContent||'0',10) : 0; if(cur>0) setBadge(cur-1); } }).catch(()=>{}); }); }
  async function refreshBadge(){ const r=await post({action:'notifications_unread_count'}); if(r && r.success) setBadge(r.unread||0); }
  // A failed request used to leave the spinner running forever, which looked
  // exactly like "nothing loads". Always leave a final state behind.
  function renderNotifError(){ const list=document.getElementById('lbNotifList'); if(list) list.innerHTML='<div class="lb-dashboard-notif-empty">Could not load notifications. Please try again.</div>'; }
  async function refreshList(){ try{ const r=await post({action:'notifications_list', limit:25, since_id:0}); if(r && r.success){ setBadge(r.unread||0); render(r.items||[]); } else { renderNotifError(); } } catch(e){ renderNotifError(); } }
  refreshBadge().catch(()=>{}); window.lbRefreshNotificationBadge=function(){ return refreshBadge().catch(()=>{}); }; setInterval(()=>{ if(!window.lbRealtimeConnected) refreshBadge().catch(()=>{}); }, 60000);
  const dd=document.getElementById('lbNotifDropdown'); if(dd){ dd.addEventListener('click', ()=>refreshList().catch(()=>{})); }
  const markAll=document.getElementById('lbNotifMarkAll'); if(markAll){ markAll.addEventListener('click', async (e)=>{ e.preventDefault(); const r=await post({action:'notifications_mark_all_read'}); if(r && r.success){ setBadge(0); refreshList().catch(()=>{}); } }); }
})();
</script>

<?php if ($lbCurrentGame && !empty($lbGames[$lbCurrentGame])):
  $lbGameConfig = $lbGames[$lbCurrentGame];
  $lbCategoryIcons = [
      'boosting' => 'fa-solid fa-rocket',
      'coaching' => 'fa-solid fa-user-graduate',
      'accounts' => 'fa-solid fa-box-archive',
      'items' => 'fa-solid fa-box',
      'topups' => 'fa-solid fa-coins',
  ];
?>
<div class="lb-game-subnav">
  <div class="lb-game-subnav__inner">
    <a href="/<?= htmlspecialchars(trim((string)$lbCurrentGame, '/'), ENT_QUOTES) ?>" class="lb-game-subnav__brand">
      <span class="lb-game-subnav__brand-badge">
        <img src="<?= htmlspecialchars($lbGameConfig['icon'], ENT_QUOTES) ?>" alt="<?= htmlspecialchars($lbGameConfig['name'], ENT_QUOTES) ?>" class="lb-game-subnav__game-icon">
      </span>
      <span class="lb-game-subnav__brand-name"><?= htmlspecialchars($lbGameConfig['name'], ENT_QUOTES) ?></span>
    </a>

    <div class="lb-game-subnav__center">
      <?php foreach ($lbGameConfig['categories'] as $lbCategoryKey => $lbCategory): ?>
        <a href="<?= htmlspecialchars($lbCategory['href'], ENT_QUOTES) ?>" class="lb-game-subnav__pill <?= $lbCurrentCategory === $lbCategoryKey ? 'is-active' : '' ?>">
          <i class="<?= $lbCategoryIcons[$lbCategoryKey] ?? 'fa-solid fa-circle' ?>"></i>
          <span><?= t($lbCategory['label']) ?></span>
        </a>
      <?php endforeach; ?>
      <?php if ($lbCurrentCategory === 'boosting'): ?>
        <a href="/boosters/" class="lb-game-subnav__pill">
          <i class="fa-solid fa-users"></i>
          <span><?= t('Our Boosters') ?></span>
        </a>
      <?php endif; ?>
    </div>

    <div class="lb-game-subnav__actions">
      <a href="#" role="button" data-tawk-open="1" class="lb-game-subnav__btn lb-game-subnav__btn--ghost">
        <i class="fa-solid fa-headset"></i>
        <span><?= t('Support 24/7') ?></span>
      </a>
      <a href="/discord" class="lb-game-subnav__btn lb-game-subnav__btn--primary">
        <i class="fa-brands fa-discord"></i>
        <span><?= t('Join Discord') ?></span>
      </a>
    </div>
  </div>
</div>
<?php endif; ?>

<script id="lb-tawk-open-js">
/* Standalone live-chat opener for [data-tawk-open]. Deliberately independent of
   the [data-tawk-mobile-support] handler further down: that one lives inside a
   block that returns early when the mobile sidenav markup is missing, so it
   never binds on some layouts. Delegated on document, so it also covers buttons
   that are rendered by page views after this script. */
(function () {
  function openTawk() {
    if (!window.Tawk_API) return false;
    document.body.classList.add('tawk-support-open');
    try { if (typeof window.Tawk_API.showWidget === 'function') window.Tawk_API.showWidget(); } catch (e) {}
    try {
      if (typeof window.Tawk_API.maximize === 'function') { window.Tawk_API.maximize(); return true; }
    } catch (e) {}
    return false;
  }

  document.addEventListener('click', function (e) {
    var btn = e.target.closest ? e.target.closest('[data-tawk-open]') : null;
    if (!btn) return;
    e.preventDefault();
    if (openTawk()) return;
    // Tawk loads async — retry until its API shows up.
    var tries = 0;
    var timer = setInterval(function () {
      tries += 1;
      if (openTawk() || tries >= 20) clearInterval(timer);
    }, 250);
  });
})();
</script>

<nav class="navbar-mobile">
    <div class="right">
        <div class="dropdown-menu">
            <div class="btn secondary settings-pill" id="openSiteSettingsMobile">
                <span class="settings-pill-flag" id="settings-pill-flag-mobile">
                    <img
                        id="settings-pill-flag-img-mobile"
                        src="<?= $currentLang === 'de'
                            ? '/public/assets/core/main/img/flags/de.png'
                            : '/public/assets/core/main/img/flags/en.png' ?>"
                        alt="<?= htmlspecialchars($currentLangLabel) ?>"
                    >
                </span>
                <span class="settings-pill-text">
                    <span class="settings-pill-lang" id="settings-pill-lang-mobile">
                        <?= htmlspecialchars(strtoupper($currentLang), ENT_QUOTES, 'UTF-8') ?>
                    </span>
                    <span class="settings-pill-cur" id="settings-pill-cur-mobile">
                        <?= htmlspecialchars($currentCurrency) ?>
                    </span>
                </span>
                <span class="settings-pill-chevron">▾</span>
            </div>
        </div>

        <button type="button" class="menu-icon" aria-label="Menu">
            <img src="<?= ASSET_URL ?>/website/images/menu.svg" alt="Menu Icon">
        </button>

        <button type="button" class="mobile-search-icon" data-lbms-open aria-label="<?= t('Search') ?>">
            <i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i>
        </button>
    </div>

    <div class="logo">
        <img src="<?= ASSET_URL ?>/website/images/logo.svg" alt="Logo" onclick="window.location.href='/'">
    </div>

    <?php if (CLIENT_DATA != false): ?>
        <?php $lbMobileClientIcon = !empty(CLIENT_DATA['icon']) ? (string) CLIENT_DATA['icon'] : ''; ?>
        <button onclick="window.location.href='/profile/overview'" type="button" id="login-btn-mobile-header" class="btn primary mobile-top-profile-btn<?= $lbMobileClientIcon !== '' ? ' has-client-avatar' : '' ?>" aria-label="<?= htmlspecialchars(CLIENT_DATA['username']) ?>" title="<?= htmlspecialchars(CLIENT_DATA['username']) ?>" data-mobile-profile="1">
            <?php if ($lbMobileClientIcon !== ''): ?>
                <img class="mobile-top-profile-avatar" src="<?= htmlspecialchars($lbMobileClientIcon) ?>" alt="<?= htmlspecialchars(CLIENT_DATA['username']) ?>">
            <?php else: ?>
                <i class="fas fa-user" aria-hidden="true"></i>
            <?php endif; ?>
        </button>
    <?php elseif (BOOSTER_DATA != false): ?>
        <button onclick="window.location.href='/booster-area/dashboard'" type="button" id="login-btn-mobile-header" class="btn primary mobile-top-profile-btn" aria-label="<?= htmlspecialchars(BOOSTER_DATA['username']) ?>" title="<?= htmlspecialchars(BOOSTER_DATA['username']) ?>" data-mobile-profile="1">
            <i class="fas fa-user" aria-hidden="true"></i>
        </button>
    <?php else: ?>
        <button type="button" id="login-btn-mobile-header" class="btn primary mobile-top-profile-btn is-login-state" aria-label="<?= t('Login') ?>" title="<?= t('Login') ?>" data-login-trigger="1">
            <span><?= t('Login') ?></span>
            <i class="fas fa-user" aria-hidden="true"></i>
        </button>
    <?php endif; ?>
</nav>

<?php if ($lbCurrentGame && !empty($lbGames[$lbCurrentGame])):
  $lbShowMobileBottomnav = in_array($lbCurrentGame, ['lol', 'val'], true);
  $lbShowMobileItemsTab = $lbCurrentGame === 'lol' && !empty($lbGameConfig['categories']['items']);
  $lbMobileBottomnavCount = $lbCurrentGame === 'lol' ? 3 : 2;
  $lbMobileAccountsHref = $lbGameConfig['categories']['accounts']['href'] ?? $lbGameConfig['categories']['boosting']['href'] ?? $lbGameConfig['categories']['items']['href'] ?? '#';
  $lbMobileBottomItemsHref = $lbGameConfig['categories']['items']['href'] ?? $lbGameConfig['categories']['boosting']['href'] ?? $lbGameConfig['categories']['accounts']['href'] ?? '#';
?>
<?php $lbIsBoostForm = ($lbCurrentCategory === 'boosting'); ?>
<?php if (!$lbIsBoostForm): ?>
<div class="lb-mobile-gamebar">
  <?php // Games without a boosting category (e.g. account-only ones) left this
        // undefined, which logged two warnings on every single page view. ?>
  <a href="<?= htmlspecialchars($lbGameConfig['categories']['boosting']['href'] ?? $lbMobileAccountsHref, ENT_QUOTES) ?>" class="lb-mobile-gamebar__brand">
    <span class="lb-mobile-gamebar__iconwrap"><img src="<?= htmlspecialchars($lbGameConfig['icon'], ENT_QUOTES) ?>" alt="<?= htmlspecialchars($lbGameConfig['name'], ENT_QUOTES) ?>"></span>
    <span class="lb-mobile-gamebar__name"><?= htmlspecialchars($lbGameConfig['name'], ENT_QUOTES) ?></span>
  </a>

  <a href="#" class="lb-mobile-gamebar__support" data-tawk-mobile-support="1" role="button">
    <i class="fa-solid fa-headset"></i>
    <span><?= t('Support 24/7') ?></span>
  </a>
</div>
<?php endif; ?>

<?php if ($lbShowMobileBottomnav && !$lbIsBoostForm): ?>
<div class="lb-mobile-bottomnav lb-mobile-bottomnav--count-<?= (int) $lbMobileBottomnavCount ?>">
  <a href="<?= htmlspecialchars($lbGameConfig['categories']['boosting']['href'], ENT_QUOTES) ?>" class="lb-mobile-bottomnav__item <?= $lbCurrentCategory === 'boosting' ? 'is-active' : '' ?>">
    <i class="fa-solid fa-rocket"></i>
    <span><?= t('Boosting') ?></span>
  </a>
  <a href="<?= htmlspecialchars($lbMobileAccountsHref, ENT_QUOTES) ?>" class="lb-mobile-bottomnav__item <?= $lbCurrentCategory === 'accounts' ? 'is-active' : '' ?>">
    <i class="fa-solid fa-box-archive"></i>
    <span><?= t('Accounts') ?></span>
  </a>
  <?php if ($lbShowMobileItemsTab): ?>
  <a href="<?= htmlspecialchars($lbMobileBottomItemsHref, ENT_QUOTES) ?>" class="lb-mobile-bottomnav__item <?= $lbCurrentCategory === 'items' ? 'is-active' : '' ?>">
    <i class="fa-solid fa-box"></i>
    <span><?= t('Items') ?></span>
  </a>
  <?php endif; ?>
</div>
<?php endif; ?>
<?php endif; ?>

<div class="sidenav-mob">
    <div class="sidenav-header">
        <button type="button" class="mob-back-btn" data-mob-back aria-label="Back">
            <i class="fas fa-arrow-left"></i>
        </button>

        <div class="logo">
            <img src="<?= ASSET_URL ?>/website/images/logo-footer.webp" alt="Logo" onclick="window.location.href='/'">
        </div>

        <div class="mob-header-title" data-mob-title>
            <?= t('Boosting') ?>
        </div>

        <button type="button" class="close-sidenav">
            <i class="fas fa-times"></i>
        </button>
    </div>

    <div class="sidenav-menu">

        <div class="mob-view is-active" data-mob-view="main">
            <ul class="mob-main-list mob-main-list--general">
                <li>
                    <a href="/" class="mob-main-item">
                        <span class="mob-main-left">
                            <span class="mob-main-icon mob-main-icon--blue"><i class="fas fa-house"></i></span>
                            <span class="mob-main-copy">
                                <span class="mob-main-label"><?= t('Home') ?></span>
                                <span class="mob-main-sub"><?= t('Back to the start page') ?></span>
                            </span>
                        </span>
                        <span class="mob-main-chevron"><i class="fas fa-chevron-right"></i></span>
                    </a>
                </li>
                <li>
                    <a href="/services/accounts" class="mob-main-item">
                        <span class="mob-main-left">
                            <span class="mob-main-icon mob-main-icon--purple"><i class="fas fa-store"></i></span>
                            <span class="mob-main-copy">
                                <span class="mob-main-label"><?= t('Marketplace') ?></span>
                                <span class="mob-main-sub"><?= t('Accounts, boosts and digital goods') ?></span>
                            </span>
                        </span>
                        <span class="mob-main-chevron"><i class="fas fa-chevron-right"></i></span>
                    </a>
                </li>
                <li>
                    <a href="/blog" class="mob-main-item">
                        <span class="mob-main-left">
                            <span class="mob-main-icon mob-main-icon--slate"><i class="fas fa-rss"></i></span>
                            <span class="mob-main-copy">
                                <span class="mob-main-label"><?= t('Blog') ?></span>
                                <span class="mob-main-sub"><?= t('Guides and updates') ?></span>
                            </span>
                        </span>
                        <span class="mob-main-chevron"><i class="fas fa-chevron-right"></i></span>
                    </a>
                </li>
                <li>
                    <a href="/work-with-us" class="mob-main-item">
                        <span class="mob-main-left">
                            <span class="mob-main-icon mob-main-icon--cyan"><i class="fas fa-handshake"></i></span>
                            <span class="mob-main-copy">
                                <span class="mob-main-label"><?= t('Work with us') ?></span>
                                <span class="mob-main-sub"><?= t('Become a booster or seller') ?></span>
                            </span>
                        </span>
                        <span class="mob-main-chevron"><i class="fas fa-chevron-right"></i></span>
                    </a>
                </li>
                <li>
                    <a href="/lootboxes" class="mob-main-item">
                        <span class="mob-main-left">
                            <span class="mob-main-icon mob-main-icon--cyan"><i class="fas fa-box-open"></i></span>
                            <span class="mob-main-copy">
                                <span class="mob-main-label"><?= t('Lootboxes') ?></span>
                                <span class="mob-main-sub"><?= t('Open boxes and win rewards') ?></span>
                            </span>
                        </span>
                        <span class="mob-main-chevron"><i class="fas fa-chevron-right"></i></span>
                    </a>
                </li>
                <li>
                    <a href="/loyalty" class="mob-main-item">
                        <span class="mob-main-left">
                            <span class="mob-main-icon mob-main-icon--purple"><i class="fas fa-gem"></i></span>
                            <span class="mob-main-copy">
                                <span class="mob-main-label"><?= t('Loyalty') ?></span>
                                <span class="mob-main-sub"><?= t('Rewards and LB Coins') ?></span>
                            </span>
                        </span>
                        <span class="mob-main-chevron"><i class="fas fa-chevron-right"></i></span>
                    </a>
                </li>
                <li>
                    <a href="/reviews" class="mob-main-item">
                        <span class="mob-main-left">
                            <span class="mob-main-icon mob-main-icon--gold"><i class="fas fa-star"></i></span>
                            <span class="mob-main-copy">
                                <span class="mob-main-label"><?= t('Reviews') ?></span>
                                <span class="mob-main-sub"><?= t('Customer feedback') ?></span>
                            </span>
                        </span>
                        <span class="mob-main-chevron"><i class="fas fa-chevron-right"></i></span>
                    </a>
                </li>
                <li>
                    <a href="/boosters" class="mob-main-item">
                        <span class="mob-main-left">
                            <span class="mob-main-icon mob-main-icon--blue"><i class="fas fa-trophy"></i></span>
                            <span class="mob-main-copy">
                                <span class="mob-main-label"><?= t('Boosters') ?></span>
                                <span class="mob-main-sub"><?= t('Meet our top players') ?></span>
                            </span>
                        </span>
                        <span class="mob-main-chevron"><i class="fas fa-chevron-right"></i></span>
                    </a>
                </li>
                <li>
                    <a href="/egirls" class="mob-main-item">
                        <span class="mob-main-left">
                            <span class="mob-main-icon mob-main-icon--pink"><i class="fas fa-heart"></i></span>
                            <span class="mob-main-copy">
                                <span class="mob-main-label"><?= t('E-Girls') ?></span>
                                <span class="mob-main-sub"><?= t('Play with verified companions') ?></span>
                            </span>
                        </span>
                        <span class="mob-main-chevron"><i class="fas fa-chevron-right"></i></span>
                    </a>
                </li>
                <li>
                    <a href="/contact" class="mob-main-item">
                        <span class="mob-main-left">
                            <span class="mob-main-icon mob-main-icon--cyan"><i class="fas fa-envelope"></i></span>
                            <span class="mob-main-copy">
                                <span class="mob-main-label"><?= t('Contact') ?></span>
                                <span class="mob-main-sub"><?= t('Questions and business inquiries') ?></span>
                            </span>
                        </span>
                        <span class="mob-main-chevron"><i class="fas fa-chevron-right"></i></span>
                    </a>
                </li>
                <li>
                    <a href="#" class="mob-main-item" data-tawk-mobile-support="1">
                        <span class="mob-main-left">
                            <span class="mob-main-icon mob-main-icon--green"><i class="fas fa-headset"></i></span>
                            <span class="mob-main-copy">
                                <span class="mob-main-label"><?= t('Support') ?></span>
                                <span class="mob-main-sub"><?= t('Live chat, 24/7') ?></span>
                            </span>
                        </span>
                        <span class="mob-main-chevron"><i class="fas fa-chevron-right"></i></span>
                    </a>
                </li>
            </ul>
        </div>

        <div class="mob-view" data-mob-view="boosting">
            <ul class="mob-game-list">
                <li class="mob-game-item">
                    <a class="mob-game-link" href="/lol/rank-boost">
                        <span class="mob-game-left">
                            <img class="mob-game-icon" src="<?= ASSET_URL ?>/website/images/icons/league-of-legends.png" alt="League of Legends" loading="lazy">
                            <span class="mob-game-info">
                                <span class="mob-game-name">League of Legends</span>
                                <span class="mob-game-meta">Ranked • Duo • Coaching</span>
                            </span>
                        </span>
                        <span class="mob-game-chevron" aria-hidden="true"><i class="fas fa-chevron-right"></i></span>
                    </a>
                </li>
                <li class="mob-game-item">
                    <a class="mob-game-link" href="/val/rank-boost">
                        <span class="mob-game-left">
                            <img class="mob-game-icon" src="<?= ASSET_URL ?>/website/images/icons/valorant.png" alt="Valorant" loading="lazy">
                            <span class="mob-game-info">
                                <span class="mob-game-name">Valorant</span>
                                <span class="mob-game-meta">Ranked • Wins • Coaching</span>
                            </span>
                        </span>
                        <span class="mob-game-chevron" aria-hidden="true"><i class="fas fa-chevron-right"></i></span>
                    </a>
                </li>
                <li class="mob-game-item">
                    <a class="mob-game-link" href="/tft/rank-boost">
                        <span class="mob-game-left">
                            <img class="mob-game-icon" src="<?= ASSET_URL ?>/website/images/icons/teamfight-tactics.png" alt="Teamfight Tactics" loading="lazy">
                            <span class="mob-game-info">
                                <span class="mob-game-name">Teamfight Tactics</span>
                                <span class="mob-game-meta">Ranked • Double Up • Placements</span>
                            </span>
                        </span>
                        <span class="mob-game-chevron" aria-hidden="true"><i class="fas fa-chevron-right"></i></span>
                    </a>
                </li>

            </ul>
        </div>

        <div class="mob-view" data-mob-view="coaching">
            <ul class="mob-game-list">
                <li class="mob-game-item">
                    <a class="mob-game-link" href="/lol/coaching">
                        <span class="mob-game-left">
                            <img class="mob-game-icon" src="<?= ASSET_URL ?>/website/images/icons/league-of-legends.png" alt="League of Legends" loading="lazy">
                            <span class="mob-game-info">
                                <span class="mob-game-name">League of Legends</span>
                                <span class="mob-game-meta">Co-Pilot • VOD Review</span>
                            </span>
                        </span>
                        <span class="mob-game-chevron"><i class="fas fa-chevron-right"></i></span>
                    </a>
                </li>
                <li class="mob-game-item">
                    <a class="mob-game-link" href="/tft/coaching">
                        <span class="mob-game-left">
                            <img class="mob-game-icon" src="<?= ASSET_URL ?>/website/images/icons/teamfight-tactics.png" alt="Teamfight Tactics" loading="lazy">
                            <span class="mob-game-info">
                                <span class="mob-game-name">Teamfight Tactics</span>
                                <span class="mob-game-meta">Co-Pilot • VOD Review</span>
                            </span>
                        </span>
                        <span class="mob-game-chevron"><i class="fas fa-chevron-right"></i></span>
                    </a>
                </li>
                <li class="mob-game-item">
                    <a class="mob-game-link mob-game-link--val" href="/val/coaching">
                        <span class="mob-game-left">
                            <img class="mob-game-icon" src="<?= ASSET_URL ?>/website/images/icons/valorant.png" alt="Valorant" loading="lazy">
                            <span class="mob-game-info">
                                <span class="mob-game-name">Valorant</span>
                                <span class="mob-game-meta">Co-Pilot • VOD Review</span>
                            </span>
                        </span>
                        <span class="mob-game-chevron"><i class="fas fa-chevron-right"></i></span>
                    </a>
                </li>
            </ul>
        </div>

        <div class="mob-view" data-mob-view="accounts">
            <ul class="mob-game-list">
                <li class="mob-game-item">
                    <a class="mob-game-link mob-game-link--smurf" href="/lol/premium-accounts">
                        <span class="mob-game-left">
                            <img class="mob-game-icon" src="<?= ASSET_URL ?>/website/images/icons/league-of-legends.png" alt="League of Legends" loading="lazy">
                            <span class="mob-game-info">
                                <span class="mob-game-name">League of Legends</span>
                                <span class="mob-game-meta">Smurf • Ranked</span>
                            </span>
                        </span>
                        <span class="mob-game-chevron" aria-hidden="true"><i class="fas fa-chevron-right"></i></span>
                    </a>
                </li>
                <li class="mob-game-item">
                    <a class="mob-game-link mob-game-link--val" href="/val/premium-accounts">
                        <span class="mob-game-left">
                            <img class="mob-game-icon" src="<?= ASSET_URL ?>/website/images/icons/valorant.png" alt="Valorant" loading="lazy">
                            <span class="mob-game-info">
                                <span class="mob-game-name">Valorant</span>
                                <span class="mob-game-meta">Smurf • Ranked</span>
                            </span>
                        </span>
                        <span class="mob-game-chevron" aria-hidden="true"><i class="fas fa-chevron-right"></i></span>
                    </a>
                </li>
            </ul>
        </div>

    </div>

    <div class="sidenav-footer">
        <div class="mobile-menu-utility-bar">
            <div class="mobile-menu-settings-slot"></div>
            <a href="/discord" class="mobile-menu-utility-btn mobile-menu-utility-btn--discord" aria-label="Discord">
                <i class="fab fa-discord"></i>
            </a>
            <a href="#" class="mobile-menu-utility-btn mobile-menu-utility-btn--help" data-tawk-mobile-support="1" aria-label="<?= t('Live Chat') ?>" title="<?= t('Live Chat') ?>">
                <i class="fas fa-comments"></i>
            </a>
        </div>
    </div>
</div>

<script>(function(){
  function ready(fn){
    if(document.readyState === 'loading') document.addEventListener('DOMContentLoaded', fn);
    else fn();
  }

  ready(function(){
    var sidenav   = document.querySelector('.sidenav-mob');
    var backBtn   = document.querySelector('[data-mob-back]');
    var titleEl   = document.querySelector('[data-mob-title]');
    var mainView  = document.querySelector('.sidenav-mob [data-mob-view="main"]');
    var closeBtn  = document.querySelector('.close-sidenav');

    if(!sidenav || !backBtn || !mainView) return;

    var currentView = null;

    var viewLabels = {
      boosting: '<?= t('Boosting') ?>',
      coaching: '<?= t('Coaching') ?>',
      accounts: '<?= t('Accounts') ?>'
    };

    function showSub(key){
      var target = document.querySelector('.sidenav-mob [data-mob-view="' + key + '"]');
      if(!target) return;
      currentView = key;
      sidenav.classList.add('is-sub-view');
      mainView.classList.remove('is-active');
      // hide all other sub views
      document.querySelectorAll('.sidenav-mob .mob-view').forEach(function(v){ v.classList.remove('is-active'); });
      target.classList.add('is-active');
      if(titleEl) titleEl.textContent = viewLabels[key] || key;
    }

    function showMain(){
      currentView = null;
      sidenav.classList.remove('is-sub-view');
      document.querySelectorAll('.sidenav-mob .mob-view').forEach(function(v){ v.classList.remove('is-active'); });
      mainView.classList.add('is-active');
    }

    function cleanupMobileNavOverlay(){
      // Remove all global open/overlay classes that can keep the page grey after
      // the mobile sidenav is closed. Some scripts add these to <body>, others
      // to <html>, so both have to be cleaned.
      var classes = [
        'sidenav-open',
        'tawk-support-open',
        'mobile-menu-open',
        'nav-open',
        'sidebar-open',
        'offcanvas-open',
        'overlay',
        'modal-open'
      ];

      document.body.classList.remove.apply(document.body.classList, classes);
      document.documentElement.classList.remove.apply(document.documentElement.classList, classes);

      // Remove only nav/backdrop overlays. Do not remove #siteSettings itself,
      // because that settings modal is reused later.
      document.querySelectorAll(
        '.sidenav-backdrop, .mobile-menu-backdrop, .nav-backdrop, .sidebar-backdrop, .offcanvas-backdrop, .menu-backdrop, ' +
        '.sidenav-overlay, .mobile-menu-overlay, .nav-overlay, .sidebar-overlay, .offcanvas-overlay, .menu-overlay, ' +
        '.modal-backdrop, .auth-modal-backdrop, .overlay-backdrop'
      ).forEach(function(x){
        if(x && x.parentNode) x.parentNode.removeChild(x);
      });
    }

    function closeMobileSidenav(e){
      if(e){
        e.preventDefault();
        e.stopPropagation();
        if(e.stopImmediatePropagation) e.stopImmediatePropagation();
      }

      showMain();

      // main.css opens the mobile nav with .show; older/custom scripts may use
      // one of the other common open-state classes. Remove all of them so the
      // X button reliably closes the menu on mobile browsers.
      sidenav.classList.remove('show', 'open', 'active', 'is-open', 'is-visible', 'in');
      sidenav.setAttribute('aria-hidden', 'true');

      cleanupMobileNavOverlay();

      // Run cleanup again after the same tap/transition cycle. This catches
      // delayed mobile click handlers or CSS transition callbacks that re-add
      // the grey overlay after the X button was pressed.
      window.clearTimeout(window.__lbSidenavOverlayCleanupTimer);
      window.__lbSidenavOverlayCleanupTimer = window.setTimeout(cleanupMobileNavOverlay, 0);
      window.setTimeout(cleanupMobileNavOverlay, 35);
      window.setTimeout(cleanupMobileNavOverlay, 90);

      lbSuppressMobileLogin(300);
      syncTawkMobileState();
    }

    window.lbCloseMobileSidenav = closeMobileSidenav;

    // open sub-views
    document.querySelectorAll('[data-mob-open]').forEach(function(btn){
      btn.addEventListener('click', function(e){
        e.preventDefault();
        showSub(btn.getAttribute('data-mob-open'));
      });
    });

    backBtn.addEventListener('click', function(e){
      e.preventDefault();
      showMain();
    });

    if(closeBtn) {
      closeBtn.addEventListener('pointerdown', closeMobileSidenav, true);
      closeBtn.addEventListener('touchstart', closeMobileSidenav, true);
      closeBtn.addEventListener('click', closeMobileSidenav, true);
    }

    // -- Tawk: on LoL boost mobile keep widget hidden by default.
    // It opens only when the Support 24/7 button in .lb-mobile-gamebar is clicked.
    function isMobileViewport(){
      return window.matchMedia && window.matchMedia('(max-width: 767px)').matches;
    }
    function isLolBoostPage(){
      return document.body && document.body.classList.contains('lol-boost');
    }
    function isMobileLolBoost(){
      return isMobileViewport() && isLolBoostPage();
    }
    function hasActiveMobileBottomnav(){
      if(!isMobileViewport()) return false;
      var nav = document.querySelector('.lb-mobile-bottomnav');
      if(!nav) return false;
      var style = window.getComputedStyle ? window.getComputedStyle(nav) : null;
      if(style && (style.display === 'none' || style.visibility === 'hidden' || style.opacity === '0')) return false;
      var rect = nav.getBoundingClientRect();
      return rect.width > 0 && rect.height > 0 && rect.bottom > 0 && rect.top < window.innerHeight;
    }
    function syncBottomnavBodyClass(){
      if(document.body){
        document.body.classList.toggle('lb-mobile-bottomnav-active', hasActiveMobileBottomnav());
      }
    }
    function tawkHide(){
      try{
        if(window.Tawk_API && typeof window.Tawk_API.hideWidget === 'function'){
          window.Tawk_API.hideWidget();
        }
      }catch(e){}
    }
    function tawkShow(){
      try{
        if(window.Tawk_API && typeof window.Tawk_API.showWidget === 'function'){
          window.Tawk_API.showWidget();
        }
      }catch(e){}
    }
    function tawkOpenChat(){
      document.body.classList.add('tawk-support-open');
      tawkShow();
      try{
        if(window.Tawk_API && typeof window.Tawk_API.maximize === 'function'){
          window.Tawk_API.maximize();
        }
      }catch(e){}
    }
    function shouldHideTawk(){
      syncBottomnavBodyClass();
      return (isMobileLolBoost() || hasActiveMobileBottomnav()) && !document.body.classList.contains('tawk-support-open');
    }
    function syncTawkMobileState(){
      syncBottomnavBodyClass();
      if(shouldHideTawk()){
        tawkHide();
      }
    }

    var mobileSupportBtns = document.querySelectorAll('[data-tawk-mobile-support]');
    if(mobileSupportBtns && mobileSupportBtns.length){
      mobileSupportBtns.forEach(function(mobileSupportBtn){
        mobileSupportBtn.addEventListener('click', function(e){
          e.preventDefault();
          tawkOpenChat();
          var tries = 0;
          var timer = setInterval(function(){
            tries += 1;
            tawkOpenChat();
            if((window.Tawk_API && typeof window.Tawk_API.maximize === 'function') || tries >= 20){
              clearInterval(timer);
            }
          }, 250);
        });
      });
    }

    syncTawkMobileState();
    window.addEventListener('resize', syncTawkMobileState);
    window.addEventListener('scroll', syncTawkMobileState, { passive: true });
    document.addEventListener('visibilitychange', syncTawkMobileState);

    if(window.MutationObserver){
      var bottomnavObserver = new MutationObserver(syncTawkMobileState);
      bottomnavObserver.observe(document.documentElement, { childList: true, subtree: true, attributes: true, attributeFilter: ['class','style'] });
    }

    var menuIconBtn = document.querySelector('.menu-icon');

    function lbForceCloseAuthModal(){
      var m = document.getElementById('login_modal');
      if (!m) return;

      m.classList.remove('show', 'open', 'active', 'is-visible', 'in');
      m.setAttribute('aria-hidden', 'true');
      m.style.display = 'none';
      m.style.pointerEvents = 'none';

      // Common leftovers from Bootstrap/custom modal scripts.
      document.body.classList.remove('modal-open', 'auth-modal-open', 'login-modal-open');
      document.documentElement.classList.remove('modal-open', 'auth-modal-open', 'login-modal-open');
      document.querySelectorAll('.modal-backdrop, .auth-modal-backdrop, .modal-overlay').forEach(function(x){
        if (x && x.parentNode) x.parentNode.removeChild(x);
      });
    }

    function lbSuppressMobileLogin(ms){
      window.__lbSuppressMobileLoginUntil = Date.now() + (ms || 1400);
      lbForceCloseAuthModal();
    }
    window.lbSuppressMobileLogin = lbSuppressMobileLogin;

    function lbIsLoginTrigger(el){
      return !!(el && el.closest && el.closest('[data-login-trigger="1"], #login-btn-mobile-header.is-login-state, #login-btn-mob, #login-btn'));
    }

    function lbBlockSuppressedLoginEvent(e){
      if (Date.now() >= (window.__lbSuppressMobileLoginUntil || 0)) return;
      lbForceCloseAuthModal();
      if (!lbIsLoginTrigger(e.target)) return;
      e.preventDefault();
      e.stopPropagation();
      if (e.stopImmediatePropagation) e.stopImmediatePropagation();
    }

    if (window.MutationObserver) {
      var lbAuthGuardObserver = new MutationObserver(function(){
        if (Date.now() < (window.__lbSuppressMobileLoginUntil || 0)) {
          lbForceCloseAuthModal();
        }
      });
      lbAuthGuardObserver.observe(document.documentElement, {
        childList: true,
        subtree: true,
        attributes: true,
        attributeFilter: ['class', 'style', 'aria-hidden']
      });
    }

    // Mobile Safari/Chrome can fire a delayed click after closing the sidenav.
    // Capture-phase guard blocks that delayed click before it reaches the login buttons.
    document.addEventListener('click', lbBlockSuppressedLoginEvent, true);
    document.addEventListener('pointerdown', lbBlockSuppressedLoginEvent, true);
    document.addEventListener('pointerup', lbBlockSuppressedLoginEvent, true);
    document.addEventListener('touchstart', lbBlockSuppressedLoginEvent, true);
    document.addEventListener('touchend', lbBlockSuppressedLoginEvent, true);

    var mobileLoginBtn = document.getElementById('login-btn-mobile-header');
    if (mobileLoginBtn && mobileLoginBtn.getAttribute('data-login-trigger') === '1') {
      mobileLoginBtn.addEventListener('click', function(e){
        if (Date.now() < (window.__lbSuppressMobileLoginUntil || 0)) {
          e.preventDefault();
          e.stopPropagation();
          if (e.stopImmediatePropagation) e.stopImmediatePropagation();
          return;
        }
        e.preventDefault();
        var sideLoginBtn = document.querySelector('.sidenav-mob #login-btn-mob, #login-btn-mob');
        if (sideLoginBtn && sideLoginBtn !== mobileLoginBtn) {
          sideLoginBtn.click();
          return;
        }
        var desktopLoginBtn = document.getElementById('login-btn');
        if (desktopLoginBtn) {
          desktopLoginBtn.click();
        }
      });
    }
    if(menuIconBtn){
      menuIconBtn.addEventListener('click', function(){
        setTimeout(function(){
          document.body.classList.add('sidenav-open');
          document.body.classList.remove('tawk-support-open');
          tawkHide();
        }, 50);
      });
    }
    if(closeBtn) {
      ['pointerdown', 'touchstart', 'click'].forEach(function(evtName){
        closeBtn.addEventListener(evtName, closeMobileSidenav, true);
      });
    }

    // MutationObserver: watch sidenav for any class/style change to detect open/close.
    // Important: do NOT call tawkShow() automatically on LoL boost mobile; Support button controls that.
    if(sidenav && window.MutationObserver){
      var lbSidenavWasVisible = false;
      var navObserver = new MutationObserver(function(){
        var rect = sidenav.getBoundingClientRect();
        var visible = rect.left < window.innerWidth && rect.right > 0 && rect.width > 0;
        if(lbSidenavWasVisible && !visible){
          lbSuppressMobileLogin(850);
        }
        lbSidenavWasVisible = visible;
        document.body.classList.toggle('sidenav-open', visible);
        if(visible){
          document.body.classList.remove('tawk-support-open');
          tawkHide();
        } else {
          cleanupMobileNavOverlay();
          syncTawkMobileState();
        }
      });
      navObserver.observe(sidenav, { attributes: true, attributeFilter: ['class','style'] });
    }
  });
})();
</script>

<div class="site-settings-overlay" id="siteSettings" hidden aria-hidden="true">
    <div class="site-settings-modal">
        <aside class="settings-visual" aria-hidden="true">
            <div class="settings-kicker"><i class="fa-solid fa-sliders"></i> Preferences</div>
            <div class="settings-visual-card"><i class="fa-solid fa-globe"></i></div>
            <div class="settings-visual-copy">
                <h3>Site<span>Settings</span></h3>
                <p>Choose your language and currency for a smoother checkout experience.</p>
            </div>
            <div class="settings-visual-stats">
                <div class="settings-visual-stat"><strong>20+</strong><span>Languages</span></div>
                <div class="settings-visual-stat"><strong>2</strong><span>Currencies</span></div>
            </div>
        </aside>
        <div class="settings-panel">
        <div class="settings-header">
            <div class="settings-header-left">
                <div class="settings-icon">🌐</div>
                <div>
                    <div class="settings-title"><?= t('Site Settings') ?></div>
                    <div class="settings-subtitle"><?= t('Set your preferred language and currency.') ?></div>
                </div>
            </div>
            <button class="settings-close-btn" type="button" data-close>✕</button>
        </div>

        <div class="settings-section" data-type="language">
            <div class="settings-label"><?= t('Language') ?></div>

            <div class="settings-select" data-toggle="language">
                <div class="settings-select-left">
                    <div class="flag-circle">
                        <img id="flag-lang-img" src="/public/assets/core/main/img/flags/en.png" alt="English">
                    </div>
                    <div class="settings-select-value" id="value-lang">English</div>
                </div>
                <div class="chevron">▾</div>
            </div>

            <div class="settings-options" id="options-lang">

                <button class="settings-option active" type="button" data-lang="en" data-flag-src="/public/assets/core/main/img/flags/en.png">
                    <div class="flag-circle"><img src="/public/assets/core/main/img/flags/en.png" alt="English"></div>
                    <span>English</span>
                </button>

                <button class="settings-option" type="button" data-lang="de" data-flag-src="/public/assets/core/main/img/flags/de.png">
                    <div class="flag-circle"><img src="/public/assets/core/main/img/flags/de.png" alt="Deutsch"></div>
                    <span>Deutsch</span>
                </button>

                <button class="settings-option" type="button" data-lang="fr" data-flag-src="/public/assets/core/main/img/flags/fr.webp">
                    <div class="flag-circle"><img src="/public/assets/core/main/img/flags/fr.webp" alt="Français"></div>
                    <span>Français</span>
                </button>

                <button class="settings-option" type="button" data-lang="es" data-flag-src="/public/assets/core/main/img/flags/es.png">
                    <div class="flag-circle"><img src="/public/assets/core/main/img/flags/es.png" alt="Español"></div>
                    <span>Español</span>
                </button>

                <button class="settings-option" type="button" data-lang="pt" data-flag-src="/public/assets/core/main/img/flags/pt.png">
                    <div class="flag-circle"><img src="/public/assets/core/main/img/flags/pt.png" alt="Português"></div>
                    <span>Português</span>
                </button>

                <button class="settings-option" type="button" data-lang="it" data-flag-src="/public/assets/core/main/img/flags/it.png">
                    <div class="flag-circle"><img src="/public/assets/core/main/img/flags/it.png" alt="Italiano"></div>
                    <span>Italiano</span>
                </button>

                <button class="settings-option" type="button" data-lang="nl" data-flag-src="/public/assets/core/main/img/flags/nl.png">
                    <div class="flag-circle"><img src="/public/assets/core/main/img/flags/nl.png" alt="Nederlands"></div>
                    <span>Nederlands</span>
                </button>

                <button class="settings-option" type="button" data-lang="jp" data-flag-src="/public/assets/core/main/img/flags/jp.webp">
                    <div class="flag-circle"><img src="/public/assets/core/main/img/flags/jp.webp" alt="日本語"></div>
                    <span>日本語</span>
                </button>

                <button class="settings-option" type="button" data-lang="zh" data-flag-src="/public/assets/core/main/img/flags/ch.png">
                    <div class="flag-circle"><img src="/public/assets/core/main/img/flags/ch.png" alt="中文"></div>
                    <span>中文</span>
                </button>

                <button class="settings-option" type="button" data-lang="ru" data-flag-src="/public/assets/core/main/img/flags/ru.webp">
                    <div class="flag-circle"><img src="/public/assets/core/main/img/flags/ru.webp" alt="Русский"></div>
                    <span>Русский</span>
                </button>

                <button class="settings-option" type="button" data-lang="pl" data-flag-src="/public/assets/core/main/img/flags/pl.webp">
                    <div class="flag-circle"><img src="/public/assets/core/main/img/flags/pl.webp" alt="Polski"></div>
                    <span>Polski</span>
                </button>

                <button class="settings-option" type="button" data-lang="sv" data-flag-src="/public/assets/core/main/img/flags/sv.webp">
                    <div class="flag-circle"><img src="/public/assets/core/main/img/flags/sv.webp" alt="Svenska"></div>
                    <span>Svenska</span>
                </button>

                <button class="settings-option" type="button" data-lang="ro" data-flag-src="/public/assets/core/main/img/flags/ro.webp">
                    <div class="flag-circle"><img src="/public/assets/core/main/img/flags/ro.webp" alt="Română"></div>
                    <span>Română</span>
                </button>

                <button class="settings-option" type="button" data-lang="cs" data-flag-src="/public/assets/core/main/img/flags/cz.webp">
                    <div class="flag-circle"><img src="/public/assets/core/main/img/flags/cz.webp" alt="Čeština"></div>
                    <span>Čeština</span>
                </button>

                <button class="settings-option" type="button" data-lang="el" data-flag-src="/public/assets/core/main/img/flags/gr.png">
                    <div class="flag-circle"><img src="/public/assets/core/main/img/flags/gr.png" alt="Ελληνικά"></div>
                    <span>Ελληνικά</span>
                </button>

                <button class="settings-option" type="button" data-lang="no" data-flag-src="/public/assets/core/main/img/flags/no.webp">
                    <div class="flag-circle"><img src="/public/assets/core/main/img/flags/no.webp" alt="Norsk"></div>
                    <span>Norsk</span>
                </button>

                <button class="settings-option" type="button" data-lang="da" data-flag-src="/public/assets/core/main/img/flags/da.webp">
                    <div class="flag-circle"><img src="/public/assets/core/main/img/flags/da.webp" alt="Dansk"></div>
                    <span>Dansk</span>
                </button>

                <button class="settings-option" type="button" data-lang="fi" data-flag-src="/public/assets/core/main/img/flags/fi.webp">
                    <div class="flag-circle"><img src="/public/assets/core/main/img/flags/fi.webp" alt="Suomi"></div>
                    <span>Suomi</span>
                </button>

                <button class="settings-option" type="button" data-lang="bg" data-flag-src="/public/assets/core/main/img/flags/bg.webp">
                    <div class="flag-circle"><img src="/public/assets/core/main/img/flags/bg.webp" alt="Български"></div>
                    <span>Български</span>
                </button>

                <button class="settings-option" type="button" data-lang="hu" data-flag-src="/public/assets/core/main/img/flags/hu.webp">
                    <div class="flag-circle"><img src="/public/assets/core/main/img/flags/hu.webp" alt="Magyar"></div>
                    <span>Magyar</span>
                </button>

                <button class="settings-option" type="button" data-lang="hr" data-flag-src="/public/assets/core/main/img/flags/hr.webp">
                    <div class="flag-circle"><img src="/public/assets/core/main/img/flags/hr.webp" alt="Hrvatski"></div>
                    <span>Hrvatski</span>
                </button>

                <button class="settings-option" type="button" data-lang="ar" data-flag-src="/public/assets/core/main/img/flags/ar.png">
                    <div class="flag-circle"><img src="/public/assets/core/main/img/flags/ar.png" alt="العربية"></div>
                    <span>العربية</span>
                </button>

                <button class="settings-option" type="button" data-lang="tr" data-flag-src="/public/assets/core/main/img/flags/tr.png">
                    <div class="flag-circle"><img src="/public/assets/core/main/img/flags/tr.png" alt="Türkçe"></div>
                    <span>Türkçe</span>
                </button>
            </div>
        </div>

        <div class="settings-section" data-type="currency">
            <div class="settings-label"><?= t('Currency') ?></div>

            <div class="currency-segment" id="currencySegment" role="group" aria-label="<?= t('Currency') ?>">
                <button type="button"
                        class="currency-btn <?= $currentCurrency === 'EUR' ? 'active' : '' ?>"
                        data-cur="EUR">
                    <span class="flag-circle">
                        <img src="<?= ASSET_URL ?>/origin/dash/vendor/flag-icon-css/flags/1x1/eu.svg" alt="EUR">
                    </span>
                    <span>EUR</span>
                </button>

                <button type="button"
                        class="currency-btn <?= $currentCurrency === 'USD' ? 'active' : '' ?>"
                        data-cur="USD">
                    <span class="flag-circle">
                        <img src="<?= ASSET_URL ?>/origin/dash/vendor/flag-icon-css/flags/1x1/us.svg" alt="USD">
                    </span>
                    <span>USD</span>
                </button>
            </div>
        </div>

<div class="settings-footer">
            <button class="settings-btn ghost" type="button" data-close><?= t('Cancel') ?></button>
            <button class="settings-btn primary" type="button" id="saveSettings"><?= t('Save Changes') ?></button>
        </div>
        </div>
    </div>
</div>

<script>
    // Currency-Wechsel – mit optionalem Callback statt hartem reload()
    function changeCurrency(currency, callback) {
        $.ajax({
            url: "<?= AJAX_URL ?>",
            type: "POST",
            data: {
                action: "change_currency",
                currency: currency,
            },
            success: function (response) {
                if (typeof callback === 'function') {
                    callback();
                } else {
                    location.reload();
                }
            },
        });
    }

   function changeLanguage(lang) {
    const oneYear = 60*60*24*365;

    // Sprache merken
    document.cookie = "lang=" + encodeURIComponent(lang) + "; path=/; max-age=" + oneYear + "; samesite=Lax";

    // Wichtig: markiert „User hat bewusst gewählt“ -> Auto-Detect stoppt
    document.cookie = "lang_manual=1; path=/; max-age=" + oneYear + "; samesite=Lax";

    // optional: auch localStorage
    try { localStorage.setItem('lang', lang); } catch(e) {}

    // gleiche URL behalten, nur neu laden
    location.reload();
}
</script>

<script>
(function () {
    const overlay        = document.getElementById('siteSettings');
    const openBtnDesktop = document.getElementById('openSiteSettings');
    const openBtnMobile  = document.getElementById('openSiteSettingsMobile');
    const saveBtn        = document.getElementById('saveSettings');

    if (!overlay || !saveBtn) return;
    overlay.classList.remove('is-visible');
    overlay.setAttribute('aria-hidden', 'true');
    overlay.hidden = true;
    overlay.style.display = 'none';

    const langOptions = document.getElementById('options-lang');
    const LANGUAGE_META = {};

    if (langOptions) {
        langOptions.querySelectorAll('.settings-option').forEach(opt => {
            const code = opt.dataset.lang;
            if (!code) return;

            const label = opt.dataset.label ||
                (opt.querySelector('span') ? opt.querySelector('span').textContent.trim() : code.toUpperCase());

            const flagSrc = opt.dataset.flagSrc || '';

            LANGUAGE_META[code] = { label: label, flag: flagSrc };
        });
    }

    function getLangMeta(code) {
        if (LANGUAGE_META[code]) return LANGUAGE_META[code];
        const firstKey = Object.keys(LANGUAGE_META)[0];
        return firstKey ? LANGUAGE_META[firstKey] : { label: code, flag: '' };
    }

    const phpLang = '<?= $currentLang ?>';
    const initialCur  = '<?= $currentCurrency ?>';

    const fallbackLangCode = LANGUAGE_META[phpLang] ? phpLang : (Object.keys(LANGUAGE_META)[0] || 'en');
    const initialLang = fallbackLangCode;

    let selectedLang  = initialLang;
    let selectedCur   = initialCur;

    const pillFlagDesktop = document.getElementById('settings-pill-flag');
    const pillLangDesktop = document.getElementById('settings-pill-lang');
    const pillCurDesktop  = document.getElementById('settings-pill-cur');

    const pillFlagMobile = document.getElementById('settings-pill-flag-mobile');
    const pillLangMobile = document.getElementById('settings-pill-lang-mobile');
    const pillCurMobile  = document.getElementById('settings-pill-cur-mobile');

    const langToggle  = overlay.querySelector('[data-toggle="language"]');
    const langValueEl = document.getElementById('value-lang');
    const langFlagImg = document.getElementById('flag-lang-img');

    const curSegment  = document.getElementById('currencySegment');

    function closeMobileSidenavBeforeSettings() {
        const sidenav = document.querySelector('.sidenav-mob, .side-nav-mob, .sidenav-mobile, .side-nav-mobile, .mobile-menu, #mobileMenu, #mobile-menu');
        if (sidenav) {
            sidenav.classList.remove('open', 'active', 'show', 'is-open', 'is-visible', 'is-sub-view');
            sidenav.setAttribute('aria-hidden', 'true');
        }
        document.body.classList.remove('sidenav-open');
    }

    function lbStartMobileLoginSuppress(ms) {
        const isMobile = !window.matchMedia || window.matchMedia('(max-width: 767px)').matches;
        if (!isMobile) return;

        if (typeof window.lbSuppressMobileLogin === 'function') {
            window.lbSuppressMobileLogin(ms || 300);
        } else {
            window.__lbSuppressMobileLoginUntil = Date.now() + (ms || 300);
            const loginModal = document.getElementById('login_modal');
            if (loginModal) {
                loginModal.classList.remove('show', 'open', 'active', 'is-visible', 'in');
                loginModal.setAttribute('aria-hidden', 'true');
                loginModal.style.display = 'none';
                loginModal.style.pointerEvents = 'none';
            }
            document.body.classList.remove('modal-open', 'auth-modal-open', 'login-modal-open');
            document.documentElement.classList.remove('modal-open', 'auth-modal-open', 'login-modal-open');
        }
    }

    function openSettings(fromMobile) {
        if (fromMobile || (window.matchMedia && window.matchMedia('(max-width: 767px)').matches)) {
            closeMobileSidenavBeforeSettings();
        }
        overlay.hidden = false;
        overlay.style.display = 'flex';
        overlay.setAttribute('aria-hidden', 'false');
        overlay.classList.add('is-visible');
        document.documentElement.classList.add('site-settings-open');
        document.body.classList.add('site-settings-open');
    }

    function closeSettings(e) {
        if (e) {
            e.preventDefault();
            e.stopPropagation();
            if (e.stopImmediatePropagation) e.stopImmediatePropagation();
        }

        // Prevent mobile "ghost click" / click-through from opening the login modal beneath it.
        lbStartMobileLoginSuppress(250);

        window.clearTimeout(window.__lbSettingsOverlayHideTimer);
        overlay.classList.remove('is-visible');
        overlay.setAttribute('aria-hidden', 'true');
        overlay.hidden = true;
        overlay.style.display = 'none';

        document.documentElement.classList.remove(
            'site-settings-open',
            'overlay',
            'modal-open',
            'auth-modal-open',
            'login-modal-open'
        );
        document.body.classList.remove(
            'site-settings-open',
            'overlay',
            'modal-open',
            'auth-modal-open',
            'login-modal-open'
        );

        document.querySelectorAll(
            '.modal-backdrop, .auth-modal-backdrop, .modal-overlay, .overlay-backdrop'
        ).forEach(function(el){ el.remove(); });

        // Extra cleanup in case another handler/CSS rule re-opens or re-displays the layer in the same tap cycle.
        window.__lbSettingsOverlayHideTimer = window.setTimeout(function(){
            overlay.classList.remove('is-visible');
            overlay.setAttribute('aria-hidden', 'true');
            overlay.hidden = true;
            overlay.style.display = 'none';

            document.documentElement.classList.remove(
                'site-settings-open',
                'overlay',
                'modal-open',
                'auth-modal-open',
                'login-modal-open'
            );
            document.body.classList.remove(
                'site-settings-open',
                'overlay',
                'modal-open',
                'auth-modal-open',
                'login-modal-open'
            );

            document.querySelectorAll(
                '.modal-backdrop, .auth-modal-backdrop, .modal-overlay, .overlay-backdrop'
            ).forEach(function(el){ el.remove(); });

            // Desktop must not trigger the mobile login suppress guard here.
            lbStartMobileLoginSuppress(300);
        }, 0);

        window.setTimeout(function(){
            // Desktop must not trigger the mobile login suppress guard here.
            lbStartMobileLoginSuppress(250);
        }, 25);
    }

    function closeSettingsBeforeAuth() {
        if (!overlay || overlay.hidden) return;

        window.clearTimeout(window.__lbSettingsOverlayHideTimer);
        window.__lbSuppressMobileLoginUntil = 0;
        overlay.classList.remove('is-visible');
        overlay.setAttribute('aria-hidden', 'true');
        overlay.hidden = true;
        overlay.style.display = 'none';

        document.documentElement.classList.remove(
            'site-settings-open',
            'overlay',
            'modal-open',
            'auth-modal-open',
            'login-modal-open'
        );
        document.body.classList.remove(
            'site-settings-open',
            'overlay',
            'modal-open',
            'auth-modal-open',
            'login-modal-open'
        );

        document.querySelectorAll(
            '.modal-backdrop, .auth-modal-backdrop, .modal-overlay, .overlay-backdrop'
        ).forEach(function(el){
            if (el && el.parentNode) el.parentNode.removeChild(el);
        });
    }

    function getCurrencyFlagImg(cur) {
        if (cur === 'USD') return '<?= ASSET_URL ?>/origin/dash/vendor/flag-icon-css/flags/1x1/us.svg';
        return '<?= ASSET_URL ?>/origin/dash/vendor/flag-icon-css/flags/1x1/eu.svg';
    }

    const LANG_SHORT = { en:'EN', de:'DE', fr:'FR', es:'ES', pt:'PT', it:'IT', nl:'NL', pl:'PL', ru:'RU', jp:'JP', zh:'ZH' };

    function syncNavbarPills() {
        const meta = getLangMeta(selectedLang);
        const langLabel = meta.label;
        const langShort = LANG_SHORT[selectedLang] || String(selectedLang || '').slice(0,2).toUpperCase();

        if (pillLangDesktop) pillLangDesktop.textContent = langShort;
        if (pillCurDesktop)  pillCurDesktop.textContent  = selectedCur;
        if (pillFlagDesktop) pillFlagDesktop.innerHTML = meta.flag ? '<img src="' + meta.flag + '" alt="' + langLabel + '">' : '';

        if (pillLangMobile) pillLangMobile.textContent = langShort;
        if (pillCurMobile)  pillCurMobile.textContent  = selectedCur;
        if (pillFlagMobile) pillFlagMobile.innerHTML = meta.flag ? '<img src="' + meta.flag + '" alt="' + langLabel + '">' : '';
    }

    function syncModalLanguage() {
        const meta = getLangMeta(selectedLang);
        if (langValueEl) langValueEl.textContent = meta.label;
        if (langFlagImg && meta.flag) { langFlagImg.src = meta.flag; langFlagImg.alt = meta.label; }
    }

    function syncModalCurrency() {
        // segmented currency buttons (EUR / USD)
        if (curSegment) {
            curSegment.querySelectorAll('.currency-btn').forEach(btn => {
                if ((btn.dataset.cur || '') === selectedCur) btn.classList.add('active');
                else btn.classList.remove('active');
            });
        }
    }

    document.querySelectorAll('#login-btn, #login-btn-mob, #login-btn-mobile-header, [data-login-trigger="1"]').forEach(function(btn) {
        btn.addEventListener('pointerdown', closeSettingsBeforeAuth, true);
        btn.addEventListener('click', closeSettingsBeforeAuth, true);
    });


    function lbOpenLoginModalFallback(e) {
        if (e && e.currentTarget && e.currentTarget.id === 'login-btn-mobile-header' && e.currentTarget.getAttribute('data-login-trigger') !== '1') return;
        window.__lbSuppressMobileLoginUntil = 0;
        const loginModal = document.getElementById('login_modal');
        if (!loginModal) return;

        window.setTimeout(function () {
            loginModal.style.removeProperty('display');
            loginModal.style.removeProperty('pointer-events');
            loginModal.removeAttribute('hidden');
            loginModal.setAttribute('aria-hidden', 'false');
            loginModal.classList.add('show');
            document.documentElement.classList.add('modal-open', 'auth-modal-open', 'login-modal-open');
            document.body.classList.add('overlay', 'modal-open', 'auth-modal-open', 'login-modal-open');
        }, 0);
    }

    document.querySelectorAll('#login-btn, #login-btn-mob, #login-btn-mobile-header[data-login-trigger="1"], [data-login-trigger="1"]').forEach(function(btn) {
        btn.addEventListener('click', lbOpenLoginModalFallback, false);
    });

    /* ── Desktop dropdown ──
       The pill opens a compact panel instead of the full-screen modal; picking a
       language or currency applies immediately (no Save step). */
    const setDrop     = document.getElementById('siteSettingsDrop');
    const setDropList = document.getElementById('lbSetdropLangs');
    const setDropCur  = document.getElementById('lbSetdropCur');
    const setDropSave = document.getElementById('lbSetdropSave');
    let   setDropBuilt = false;

    // Pending choices — only Save writes them back to selectedLang/selectedCur
    // and reloads, so picking a language no longer navigates immediately.
    let draftLang = selectedLang;
    let draftCur  = selectedCur;

    function isDesktopSettings() {
        return !window.matchMedia || window.matchMedia('(min-width: 768px)').matches;
    }

    function buildSetDrop() {
        if (setDropBuilt || !setDropList) return;
        setDropBuilt = true;

        Object.keys(LANGUAGE_META).forEach(function (code) {
            const meta = LANGUAGE_META[code];
            const btn  = document.createElement('button');
            btn.type = 'button';
            btn.className = 'lb-setdrop__opt' + (code === draftLang ? ' is-active' : '');
            btn.dataset.lang = code;
            btn.innerHTML =
                (meta.flag ? '<img src="' + meta.flag + '" alt="">' : '') +
                '<span></span><i class="fa-solid fa-check"></i>';
            btn.querySelector('span').textContent = meta.label;
            btn.addEventListener('click', function () {
                draftLang = code;
                syncSetDrop();
            });
            setDropList.appendChild(btn);
        });

        if (setDropCur) {
            setDropCur.querySelectorAll('.lb-setdrop__curbtn').forEach(function (btn) {
                btn.addEventListener('click', function () {
                    draftCur = btn.dataset.cur || 'EUR';
                    syncSetDrop();
                });
            });
        }

        if (setDropSave) {
            setDropSave.addEventListener('click', function () {
                const langChanged = draftLang !== selectedLang;
                const curChanged  = draftCur !== selectedCur;
                if (!langChanged && !curChanged) { closeSetDrop(); return; }

                selectedLang = draftLang;
                selectedCur  = draftCur;
                syncNavbarPills();
                syncModalLanguage();
                syncModalCurrency();

                setDropSave.disabled = true;

                // Currency first, then language — changeLanguage() reloads.
                if (curChanged && langChanged) changeCurrency(selectedCur, function () { changeLanguage(selectedLang); });
                else if (langChanged) changeLanguage(selectedLang);
                else changeCurrency(selectedCur);
            });
        }
    }

    function syncSetDrop() {
        if (setDropList) {
            setDropList.querySelectorAll('.lb-setdrop__opt').forEach(function (b) {
                b.classList.toggle('is-active', b.dataset.lang === draftLang);
            });
            const active = setDropList.querySelector('.lb-setdrop__opt.is-active');
            if (active) active.scrollIntoView({ block: 'nearest' });
        }
        if (setDropCur) {
            setDropCur.querySelectorAll('.lb-setdrop__curbtn').forEach(function (b) {
                b.classList.toggle('is-active', (b.dataset.cur || '') === draftCur);
            });
        }
        if (setDropSave) {
            setDropSave.disabled = (draftLang === selectedLang && draftCur === selectedCur);
        }
    }

    function closeSetDrop() {
        if (!setDrop) return;
        setDrop.classList.remove('is-open');
        setDrop.hidden = true;
        if (openBtnDesktop) openBtnDesktop.setAttribute('aria-expanded', 'false');
    }

    function openSetDrop() {
        if (!setDrop) return;
        // Discard an unsaved selection from a previous open.
        draftLang = selectedLang;
        draftCur  = selectedCur;
        buildSetDrop();
        syncSetDrop();
        setDrop.hidden = false;
        // Next frame so the transition runs from the hidden state.
        requestAnimationFrame(function () { setDrop.classList.add('is-open'); });
        if (openBtnDesktop) openBtnDesktop.setAttribute('aria-expanded', 'true');
    }

    if (setDrop) {
        setDrop.addEventListener('click', function (e) { e.stopPropagation(); });
        document.addEventListener('click', function (e) {
            if (setDrop.hidden) return;
            if (e.target.closest('#openSiteSettings')) return;
            closeSetDrop();
        });
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') closeSetDrop();
        });
    }

    if (openBtnDesktop) openBtnDesktop.addEventListener('click', function(e) {
        e.preventDefault();
        // Fall back to the modal when the dropdown is unavailable (or on phones).
        if (!setDrop || !isDesktopSettings()) { openSettings(false); return; }
        if (setDrop.hidden) openSetDrop(); else closeSetDrop();
    });
    if (openBtnMobile)  openBtnMobile.addEventListener('click', function(e) {
        e.preventDefault();
        e.stopPropagation();
        openSettings(true);
    });

    overlay.addEventListener('pointerdown', function(e) {
        e.stopPropagation();
    }, { passive: false });

    const settingsModalBox = overlay.querySelector('.site-settings-modal');
    if (settingsModalBox) {
        settingsModalBox.addEventListener('click', function(e) {
            e.stopPropagation();
        });
    }

    overlay.querySelectorAll('[data-close]').forEach(btn => {
        // Close only on the final click. Closing on pointerdown/touchend can leave the next event
        // targeting the page underneath and can make DevTools show the old fixed layer as "stuck".
        btn.addEventListener('click', closeSettings, { passive: false, capture: true });
    });
    overlay.addEventListener('click', (e) => { if (e.target === overlay) closeSettings(e); }, true);

    if (langToggle && langOptions) {
        langToggle.addEventListener('click', () => langOptions.classList.toggle('is-open'));
        langOptions.querySelectorAll('.settings-option').forEach(opt => {
            opt.addEventListener('click', () => {
                langOptions.querySelectorAll('.settings-option').forEach(o => o.classList.remove('active'));
                opt.classList.add('active');
                selectedLang = opt.dataset.lang || 'en';
                langOptions.classList.remove('is-open');
                syncModalLanguage();
            });
        });
    }

    // Currency segmented buttons (EUR / USD)
    if (curSegment) {
        curSegment.querySelectorAll('.currency-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                selectedCur = btn.dataset.cur || 'EUR';
                syncModalCurrency();
            });
        });
    }
    saveBtn.addEventListener('click', () => {
        const langChanged = selectedLang !== initialLang;
        const curChanged  = selectedCur !== initialCur;

        syncNavbarPills();

        if (!langChanged && curChanged) { changeCurrency(selectedCur); return; }

        if (langChanged) {
            if (curChanged) changeCurrency(selectedCur, () => changeLanguage(selectedLang));
            else changeLanguage(selectedLang);
            return;
        }

        closeSettings();
    });

    syncNavbarPills();
    syncModalLanguage();
    syncModalCurrency();
})();
</script>

<script>
(function(){
  var root = document.querySelector('[data-boosting-v2]');
  if(!root) return;

  var grid = root.querySelector('[data-gm-services-grid]');
  var gameLinks = root.querySelectorAll('[data-gm-game]');
  if(!grid || !gameLinks.length) return;

  // Boosting hero preview
  var boostHero    = root.querySelector('.gmBoostHero');
  var boostHeroBgs = boostHero ? boostHero.querySelectorAll('[data-boost-preview-bg]') : [];
  var boostHeroGame  = boostHero ? boostHero.querySelector('[data-boost-preview-game]')  : null;
  var boostHeroTitle = boostHero ? boostHero.querySelector('[data-boost-preview-title]') : null;
  var boostHeroSub   = boostHero ? boostHero.querySelector('[data-boost-preview-sub]')   : null;

  var boostHeroData = {
    lol: { game: 'League of Legends', title: '<?= t('Boosting Services') ?>', sub: '<?= t('Pro players · Fast results · Secure') ?>' },
    val: { game: 'Valorant',           title: '<?= t('Boosting Services') ?>', sub: '<?= t('Pro players · Fast results · Secure') ?>' },
    tft: { game: 'Teamfight Tactics',  title: '<?= t('Boosting Services') ?>', sub: '<?= t('Pro players · Fast results · Secure') ?>' }
  };

  function updateBoostHero(key){
    if(!boostHero) return;
    var d = boostHeroData[key] || boostHeroData.lol;
    boostHeroBgs.forEach(function(bg){
      bg.classList.toggle('is-active', bg.getAttribute('data-boost-preview-bg') === key);
    });
    if(boostHeroGame)  boostHeroGame.textContent  = d.game;
    if(boostHeroTitle) boostHeroTitle.textContent = d.title;
    if(boostHeroSub)   boostHeroSub.textContent   = d.sub;
  }

  var tpl = {
    lol: grid.innerHTML,
    tft: `<a href="/tft/rank-boost" class="mega-pill">
  <div class="pill-top"><span class="icon-circle"><img src="<?= ASSET_URL ?>/website/images/boost-forms/boost-type-icons/rank-boost.svg" class="mega-icon"></span><span class="label"><?= t('Rank Boost') ?></span></div>
  <span class="pill-sub"><?= t('Climb any division fast') ?></span>
</a>
<a href="/tft/win-boost" class="mega-pill">
  <div class="pill-top"><span class="icon-circle"><img src="<?= ASSET_URL ?>/website/images/boost-forms/boost-type-icons/win-boost.svg" class="mega-icon"></span><span class="label"><?= t('Win Boost') ?></span></div>
  <span class="pill-sub"><?= t('Buy individual wins') ?></span>
</a>
<a href="/tft/placements-boost" class="mega-pill">
  <div class="pill-top"><span class="icon-circle"><img src="<?= ASSET_URL ?>/website/images/boost-forms/boost-type-icons/placement-boost.svg" class="mega-icon"></span><span class="label"><?= t('Placements Boost') ?></span></div>
  <span class="pill-sub"><?= t('Start the season strong') ?></span>
</a>
<a href="/tft/double-up-boost" class="mega-pill">
  <div class="pill-top"><span class="icon-circle"><img src="<?= ASSET_URL ?>/website/images/boost-forms/boost-type-icons/clash-boost.svg" class="mega-icon"></span><span class="label"><?= t('Double Up Boost') ?></span></div>
  <span class="pill-sub"><?= t('Duo queue domination') ?></span>
</a>
<a href="/tft/coaching" class="mega-pill">
  <div class="pill-top"><span class="icon-circle"><img src="<?= ASSET_URL ?>/website/images/boost-forms/boost-type-icons/expert-coaching.svg" class="mega-icon"></span><span class="label"><?= t('Expert Coaching') ?></span></div>
  <span class="pill-sub"><?= t('1-on-1 with a pro') ?></span>
</a>`,

    val: `<a href="/val/rank-boost" class="mega-pill">
  <div class="pill-top"><span class="icon-circle"><img src="<?= ASSET_URL ?>/website/images/boost-forms/boost-type-icons/rank-boost.svg" class="mega-icon"></span><span class="label"><?= t('Rank Boost') ?></span><span class="gmServiceBadge gmServiceBadge--hot">HOT</span></div>
  <span class="pill-sub"><?= t('Climb any rank fast') ?></span>
</a>
<a href="/val/win-boost" class="mega-pill">
  <div class="pill-top"><span class="icon-circle"><img src="<?= ASSET_URL ?>/website/images/boost-forms/boost-type-icons/win-boost.svg" class="mega-icon"></span><span class="label"><?= t('Win Boost') ?></span></div>
  <span class="pill-sub"><?= t('Buy individual wins') ?></span>
</a>
<a href="/val/placements-boost" class="mega-pill">
  <div class="pill-top"><span class="icon-circle"><img src="<?= ASSET_URL ?>/website/images/boost-forms/boost-type-icons/placement-boost.svg" class="mega-icon"></span><span class="label"><?= t('Placement Boost') ?></span></div>
  <span class="pill-sub"><?= t('Start the season strong') ?></span>
</a>
<a href="/val/unrated-matches" class="mega-pill">
  <div class="pill-top"><span class="icon-circle"><img src="<?= ASSET_URL ?>/website/images/boost-forms/boost-type-icons/normal-matches.svg" class="mega-icon"></span><span class="label"><?= t('Unrated Matches') ?></span></div>
  <span class="pill-sub"><?= t('Play without rank impact') ?></span>
</a>
<a href="/val/coaching" class="mega-pill">
  <div class="pill-top"><span class="icon-circle"><img src="<?= ASSET_URL ?>/website/images/boost-forms/boost-type-icons/expert-coaching.svg" class="mega-icon"></span><span class="label"><?= t('Coaching') ?></span></div>
  <span class="pill-sub"><?= t('1-on-1 with a pro') ?></span>
</a>`
  };

  function setActive(key){
    gameLinks.forEach(function(a){
      var isActive = a.getAttribute('data-gm-game') === key;
      a.classList.toggle('is-active', isActive);
      a.setAttribute('aria-selected', isActive ? 'true' : 'false');
    });

    updateBoostHero(key);

    // animate services swap
    grid.classList.add('is-switching');
    window.requestAnimationFrame(function(){
      grid.innerHTML = tpl[key] || tpl.lol;
      window.requestAnimationFrame(function(){
        grid.classList.remove('is-switching');
      });
    });
  }

  gameLinks.forEach(function(a){
    var key = a.getAttribute('data-gm-game');
    a.addEventListener('mouseenter', function(){ setActive(key); });
    a.addEventListener('focus', function(){ setActive(key); });
    a.addEventListener('click', function(){ setActive(key); });
  });
})();
</script>

<script>
(function(){
  var root = document.querySelector('[data-accounts-v2]');
  if(!root) return;

  var grid = root.querySelector('[data-gm-services-grid]');
  var gameLinks = root.querySelectorAll('[data-gm-game]');
  if(!grid || !gameLinks.length) return;

  var tpl = {
    lol: grid.innerHTML,
    val: `
<a href="/val/premium-accounts" class="gmAccCard gmAccCard--smurf">
  <div class="gmAccCard__stripe"></div>
  <div class="gmAccCard__body">
    <div class="gmAccCard__head">
      <span class="gmAccCard__iconWrap"><i class="fas fa-user-ninja"></i></span>
      <span class="gmAccCard__titleGroup">
        <span class="gmAccCard__title"><?= t('Smurf Accounts') ?></span>
        <span class="gmAccCard__tagline"><?= t('Fresh starts. Fast delivery.') ?></span>
      </span>
      <span class="gmAccCard__popular"><i class="fas fa-fire"></i><?= t('Popular') ?></span>
    </div>
    <div class="gmAccCard__features">
      <span class="gmAccCard__feat"><i class="fas fa-check-circle"></i><?= t('Hand-leveled account') ?></span>
      <span class="gmAccCard__feat"><i class="fas fa-check-circle"></i><?= t('Unranked / low MMR') ?></span>
      <span class="gmAccCard__feat"><i class="fas fa-check-circle"></i><?= t('Lifetime warranty') ?></span>
    </div>
  </div>
  <div class="gmAccCard__cta">
    <span class="gmAccCard__arrow"><i class="fas fa-arrow-right"></i></span>
  </div>
</a>

<a href="javascript:void(0)" class="gmAccCard gmAccCard--ranked" aria-disabled="true" style="pointer-events:none;opacity:.40">
  <div class="gmAccCard__stripe"></div>
  <div class="gmAccCard__body">
    <div class="gmAccCard__head">
      <span class="gmAccCard__iconWrap"><img src="<?= ASSET_URL ?>/website/images/boost-forms/boost-type-icons/win-boost.svg" alt="" loading="lazy"></span>
      <span class="gmAccCard__titleGroup">
        <span class="gmAccCard__title"><?= t('Ranked Accounts') ?></span>
        <span class="gmAccCard__tagline"><?= t('Coming soon') ?></span>
      </span>
    </div>
    <div class="gmAccCard__features">
      <span class="gmAccCard__feat"><i class="fas fa-clock"></i><?= t('We\'re working on it') ?></span>
      <span class="gmAccCard__feat"><i class="fas fa-clock"></i><?= t('Available soon') ?></span>
    </div>
  </div>
  <div class="gmAccCard__cta">
    <span class="gmAccCard__price" style="opacity:.5"><?= t('Coming soon') ?></span>
    <span class="gmAccCard__arrow"><i class="fas fa-arrow-right"></i></span>
  </div>
</a>`
  };

  // Preview banner data per game
  var previewData = {
    lol: { game: 'League of Legends', title: '<?= t('Premium Accounts') ?>', sub: '<?= t('Hand-leveled · Instant delivery') ?>' },
    val: { game: 'Valorant',          title: '<?= t('Premium Accounts') ?>', sub: '<?= t('Hand-leveled · Instant delivery') ?>' }
  };

  var previewEl    = root.querySelector('.gmAccHero');
  var previewBgs   = previewEl ? previewEl.querySelectorAll('[data-preview-bg]') : [];
  var previewGame  = previewEl ? previewEl.querySelector('[data-preview-game]')  : null;
  var previewTitle = previewEl ? previewEl.querySelector('[data-preview-title]') : null;
  var previewSub   = previewEl ? previewEl.querySelector('[data-preview-sub]')   : null;

  function updatePreview(key){
    if(!previewEl) return;
    var d = previewData[key] || previewData.lol;
    // swap backgrounds
    previewBgs.forEach(function(bg){
      bg.classList.toggle('is-active', bg.getAttribute('data-preview-bg') === key);
    });
    // update text
    if(previewGame)  previewGame.textContent  = d.game;
    if(previewTitle) previewTitle.textContent = d.title;
    if(previewSub)   previewSub.textContent   = d.sub;
  }

  function setActive(key){
    gameLinks.forEach(function(a){
      var isActive = a.getAttribute('data-gm-game') === key;
      a.classList.toggle('is-active', isActive);
      a.setAttribute('aria-selected', isActive ? 'true' : 'false');
    });

    // update preview banner
    updatePreview(key);

    grid.classList.add('is-switching');
    window.requestAnimationFrame(function(){
      grid.innerHTML = tpl[key] || tpl.lol;
      window.requestAnimationFrame(function(){
        grid.classList.remove('is-switching');
      });
    });
  }

  gameLinks.forEach(function(a){
    var key = a.getAttribute('data-gm-game');
    a.addEventListener('mouseenter', function(){ setActive(key); });
    a.addEventListener('focus', function(){ setActive(key); });
    a.addEventListener('click', function(e){
      setActive(key);
      // navigation happens naturally via href
    });
  });
})();
</script>

<script>(function(){
  var root = document.querySelector('[data-coaching-v2]');
  if(!root) return;

  var grid      = root.querySelector('[data-gm-coach-grid]');
  var gameLinks = root.querySelectorAll('[data-gm-coach-game]');
  if(!gameLinks.length) return;

  var heroBgs    = root.querySelectorAll('[data-coach-preview-bg]');
  var heroGame   = root.querySelector('[data-coach-preview-game]');
  var heroTitle  = root.querySelector('[data-coach-preview-title]');
  var heroSub    = root.querySelector('[data-coach-preview-sub]');

  var heroData = {
    lol: { game: 'League of Legends', title: '<?= t('Expert Coaching') ?>', sub: '<?= t('Learn from pro players · Improve fast') ?>' },
    tft: { game: 'Teamfight Tactics',  title: '<?= t('Expert Coaching') ?>', sub: '<?= t('Learn from pro players · Improve fast') ?>' },
    val: { game: 'Valorant',           title: '<?= t('Expert Coaching') ?>', sub: '<?= t('Learn from pro players · Improve fast') ?>' }
  };

  var tpl = {
    lol: grid ? grid.innerHTML : '',
    tft: `<a href="/tft/coaching" class="mega-pill">
  <div class="pill-top"><span class="icon-circle"><img src="<?= ASSET_URL ?>/website/images/boost-forms/boost-type-icons/expert-coaching.svg" class="mega-icon"></span><span class="label"><?= t('Co-Pilot') ?></span><span class="gmServiceBadge gmServiceBadge--hot">HOT</span></div>
  <span class="pill-sub"><?= t('Play alongside a pro') ?></span>
  <ul class="gmCoachFeatures">
    <li><?= t('Live in-game coaching') ?></li>
    <li><?= t('Real-time tips &amp; callouts') ?></li>
    <li><?= t('Instant skill improvement') ?></li>
  </ul>
</a>
<a href="/tft/coaching" class="mega-pill">
  <div class="pill-top"><span class="icon-circle"><img src="<?= ASSET_URL ?>/website/images/boost-forms/boost-type-icons/expert-coaching.svg" class="mega-icon"></span><span class="label"><?= t('VOD Review') ?></span><span class="gmServiceBadge gmServiceBadge--popular">POPULAR</span></div>
  <span class="pill-sub"><?= t('Analyze your replays with a pro') ?></span>
  <ul class="gmCoachFeatures">
    <li><?= t('Deep replay analysis') ?></li>
    <li><?= t('Personalized improvement plan') ?></li>
    <li><?= t('Mistakes &amp; win conditions') ?></li>
  </ul>
</a>`,
    val: `<a href="/val/coaching" class="mega-pill">
  <div class="pill-top"><span class="icon-circle"><img src="<?= ASSET_URL ?>/website/images/boost-forms/boost-type-icons/expert-coaching.svg" class="mega-icon"></span><span class="label"><?= t('Co-Pilot') ?></span><span class="gmServiceBadge gmServiceBadge--hot">HOT</span></div>
  <span class="pill-sub"><?= t('Play alongside a pro') ?></span>
  <ul class="gmCoachFeatures">
    <li><?= t('Live in-game coaching') ?></li>
    <li><?= t('Real-time tips &amp; callouts') ?></li>
    <li><?= t('Instant skill improvement') ?></li>
  </ul>
</a>
<a href="/val/coaching" class="mega-pill">
  <div class="pill-top"><span class="icon-circle"><img src="<?= ASSET_URL ?>/website/images/boost-forms/boost-type-icons/expert-coaching.svg" class="mega-icon"></span><span class="label"><?= t('VOD Review') ?></span><span class="gmServiceBadge gmServiceBadge--popular">POPULAR</span></div>
  <span class="pill-sub"><?= t('Analyze your replays with a pro') ?></span>
  <ul class="gmCoachFeatures">
    <li><?= t('Deep replay analysis') ?></li>
    <li><?= t('Personalized improvement plan') ?></li>
    <li><?= t('Mistakes &amp; win conditions') ?></li>
  </ul>
</a>`
  };

  function setActive(key){
    gameLinks.forEach(function(a){
      var isActive = a.getAttribute('data-gm-coach-game') === key;
      a.classList.toggle('is-active', isActive);
      a.setAttribute('aria-selected', isActive ? 'true' : 'false');
    });
    // update hero
    var d = heroData[key] || heroData.lol;
    heroBgs.forEach(function(bg){ bg.classList.toggle('is-active', bg.getAttribute('data-coach-preview-bg') === key); });
    if(heroGame)  heroGame.textContent  = d.game;
    if(heroTitle) heroTitle.textContent = d.title;
    if(heroSub)   heroSub.textContent   = d.sub;
    // swap grid
    if(grid){
      grid.classList.add('is-switching');
      window.requestAnimationFrame(function(){
        grid.innerHTML = tpl[key] || tpl.lol;
        window.requestAnimationFrame(function(){ grid.classList.remove('is-switching'); });
      });
    }
  }

  gameLinks.forEach(function(a){
    var key = a.getAttribute('data-gm-coach-game');
    a.addEventListener('mouseenter', function(){ setActive(key); });
    a.addEventListener('focus',      function(){ setActive(key); });
    a.addEventListener('click',      function(){ setActive(key); });
  });
})();
</script>

<?php if ($lbHasGameNav): ?>
<script>
document.addEventListener('DOMContentLoaded', function () {
  document.documentElement.classList.add('lb-has-game-nav');
  document.body.classList.add('lb-has-game-nav');
});
</script>
<?php endif; ?>

<script id="lb-banner-offset-system-js">
(function () {
  var root = document.documentElement;
  var storageKeys = [
    '<?= isset($lb_ls_key) ? $lb_ls_key : "lb_season_banner_closed" ?>',
    'lb_season_banner_closed_v12',
    'lb_hide_gw_banner'
  ];

  function safeSet(key, value) {
    try { if (key) localStorage.setItem(key, value); } catch (e) {}
  }

  function safeGet(key) {
    try { return key ? localStorage.getItem(key) : null; } catch (e) { return null; }
  }

  function getBanner() {
    return document.getElementById('lbSaleBanner') || document.getElementById('lbGiveawayBanner');
  }

  function isVisible(el) {
    if (!el) return false;
    if (el.hidden) return false;
    var cs = window.getComputedStyle ? getComputedStyle(el) : el.currentStyle;
    if (!cs) return false;
    if (cs.display === 'none' || cs.visibility === 'hidden' || parseFloat(cs.opacity || '1') === 0) return false;
    return (el.offsetHeight || 0) > 0;
  }

  function setImportantStyle(el, prop, value) {
    if (!el || !el.style) return;
    try { el.style.setProperty(prop, value, 'important'); }
    catch (e) { el.style[prop] = value; }
  }

  function applyOffset(height) {
    height = Math.max(0, parseInt(height || 0, 10) || 0);
    root.style.setProperty('--lb-sale-h', height + 'px');
    root.style.setProperty('--mobile-banner-offset', height + 'px');

    var navs = document.querySelectorAll('nav.navbar-top, .navbar-top, nav.navbar-mobile, .navbar-mobile');
    for (var i = 0; i < navs.length; i++) {
      setImportantStyle(navs[i], 'top', height + 'px');
    }

    if (height === 0) root.classList.add('lb-sale-banner-hidden');
    else root.classList.remove('lb-sale-banner-hidden');

    try { window.dispatchEvent(new CustomEvent('lb:banner-layout-update', { detail: { height: height } })); } catch (e) {}
  }

  function syncOffset() {
    var banner = getBanner();
    var height = isVisible(banner) ? (banner.offsetHeight || 0) : 0;
    applyOffset(height);
  }

  function closeBanner() {
    var banner = getBanner();
    if (banner) {
      banner.hidden = true;
      banner.setAttribute('aria-hidden', 'true');
      banner.style.setProperty('display', 'none', 'important');
    }

    for (var i = 0; i < storageKeys.length; i++) safeSet(storageKeys[i], '1');
    applyOffset(0);

    setTimeout(function () { applyOffset(0); }, 25);
    setTimeout(function () { applyOffset(0); }, 100);
    setTimeout(function () { applyOffset(0); }, 300);
  }

  function init() {
    var banner = getBanner();
    var alreadyClosed = false;
    for (var i = 0; i < storageKeys.length; i++) {
      if (safeGet(storageKeys[i]) === '1') alreadyClosed = true;
    }

    if (alreadyClosed) {
      closeBanner();
    } else {
      syncOffset();
    }

    document.addEventListener('click', function (e) {
      var t = e.target;
      while (t) {
        if (t.getAttribute && t.getAttribute('data-lb-close') !== null) {
          e.preventDefault();
          e.stopPropagation();
          if (e.stopImmediatePropagation) e.stopImmediatePropagation();
          closeBanner();
          return false;
        }
        t = t.parentNode;
      }
    }, true);

    if (banner && window.MutationObserver) {
      new MutationObserver(function () { syncOffset(); }).observe(banner, {
        attributes: true,
        attributeFilter: ['style', 'class', 'hidden', 'aria-hidden']
      });
    }

    window.addEventListener('resize', syncOffset, { passive: true });
    window.addEventListener('load', syncOffset);
    setTimeout(syncOffset, 50);
    setTimeout(syncOffset, 250);
    setTimeout(syncOffset, 800);
  }

  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', init);
  else init();
})();
</script>

<script src="<?= ASSET_URL ?>/website/js/header-consolidated.js?v=<?= lb_header_asset_version("/public/assets/website/js/header-consolidated.js") ?>"></script>

<style id="lb-mobile-command-center-two-columns-final">@media (max-width:900px){html body .gmHeaderSearchModal.gmHeaderCommandCenter .gmHeaderSearchGrid.gmHeaderCommandGameGrid,html body .gmHeaderSearchModal.gmHeaderCommandCenter .gmHeaderCommandGameGrid,html body .gmHeaderCommandCenter .gmHeaderSearchGrid.gmHeaderCommandGameGrid,html body .gmHeaderCommandCenter .gmHeaderCommandGameGrid,html body .gmHeaderSearchGrid.gmHeaderCommandGameGrid{display:grid !important;grid-template-columns:repeat(2,minmax(0,1fr)) !important;gap:10px !important;width:100% !important;}
html body .gmHeaderSearchModal.gmHeaderCommandCenter .gmHeaderResult.gmHeaderCommandGame,html body .gmHeaderCommandCenter .gmHeaderResult.gmHeaderCommandGame,html body .gmHeaderResult.gmHeaderCommandGame{border-radius:17px !important;display:flex !important;flex-direction:column !important;align-items:flex-start !important;justify-content:center !important;gap:8px !important;flex-wrap:nowrap !important;}
html body .gmHeaderSearchModal.gmHeaderCommandCenter .gmHeaderCommandGameMain,html body .gmHeaderCommandCenter .gmHeaderCommandGameMain,html body .gmHeaderCommandGameMain{width:100% !important;max-width:100% !important;display:flex !important;flex-direction:column !important;align-items:flex-start !important;justify-content:center !important;gap:8px !important;min-width:0 !important;}
html body .gmHeaderSearchModal.gmHeaderCommandCenter .gmHeaderCommandGameIcon,html body .gmHeaderCommandCenter .gmHeaderCommandGameIcon,html body .gmHeaderResultIcon.gmHeaderCommandGameIcon{width:44px !important;height:44px !important;min-width:44px !important;flex:0 0 44px !important;border-radius:14px !important;}
html body .gmHeaderSearchModal.gmHeaderCommandCenter .gmHeaderCommandGameText,html body .gmHeaderCommandCenter .gmHeaderCommandGameText,html body .gmHeaderCommandGameText,html body .gmHeaderCommandGame .gmHeaderResultBody{width:100% !important;min-width:0 !important;max-width:100% !important;display:block !important;}
html body .gmHeaderSearchModal.gmHeaderCommandCenter .gmHeaderCommandGame .gmHeaderResultTitle,html body .gmHeaderCommandCenter .gmHeaderCommandGame .gmHeaderResultTitle,html body .gmHeaderCommandGame .gmHeaderResultTitle{display:block !important;width:100% !important;max-width:100% !important;font-size:12.5px !important;line-height:1.15 !important;white-space:normal !important;overflow:visible !important;text-overflow:clip !important;word-break:normal !important;overflow-wrap:normal !important;hyphens:none !important;}
html body .gmHeaderSearchModal.gmHeaderCommandCenter .gmHeaderCommandGame .gmHeaderCommandActions,html body .gmHeaderSearchModal.gmHeaderCommandCenter .gmHeaderCommandGame .gmHeaderGameActions,html body .gmHeaderCommandCenter .gmHeaderCommandGame .gmHeaderCommandActions,html body .gmHeaderCommandCenter .gmHeaderCommandGame .gmHeaderGameActions,html body .gmHeaderCommandGame .gmHeaderCommandActions,html body .gmHeaderCommandGame .gmHeaderGameActions{display:none !important;}
}
@media (max-width:380px){html body .gmHeaderSearchModal.gmHeaderCommandCenter .gmHeaderSearchGrid.gmHeaderCommandGameGrid,html body .gmHeaderSearchModal.gmHeaderCommandCenter .gmHeaderCommandGameGrid,html body .gmHeaderCommandCenter .gmHeaderSearchGrid.gmHeaderCommandGameGrid,html body .gmHeaderCommandCenter .gmHeaderCommandGameGrid,html body .gmHeaderSearchGrid.gmHeaderCommandGameGrid{gap:8px !important;}
html body .gmHeaderSearchModal.gmHeaderCommandCenter .gmHeaderResult.gmHeaderCommandGame,html body .gmHeaderCommandCenter .gmHeaderResult.gmHeaderCommandGame,html body .gmHeaderResult.gmHeaderCommandGame{min-height:112px !important;padding:11px 9px !important;}
html body .gmHeaderSearchModal.gmHeaderCommandCenter .gmHeaderCommandGame .gmHeaderResultTitle,html body .gmHeaderCommandCenter .gmHeaderCommandGame .gmHeaderResultTitle,html body .gmHeaderCommandGame .gmHeaderResultTitle{font-size:12px !important;}
}</style>

<style id="gm-header-search-modal-variant-3-final">@media (min-width:901px){.gmHeaderSearchTrigger{cursor:pointer !important;user-select:none !important;}
html.gmHeaderSearchOpen,body.gmHeaderSearchOpen{overflow:hidden !important;}
html.gmHeaderSearchOpen .navbar-top,body.gmHeaderSearchOpen .navbar-top{pointer-events:auto !important;z-index:100000 !important;}
html body .gmHeaderSearchOverlay,html body .gmHeaderSearchOverlay.is-open,body.gmHeaderSearchOpen .gmHeaderSearchOverlay.is-open,html.gmHeaderSearchOpen .gmHeaderSearchOverlay.is-open,body.gmHeaderSearchOpen .gmHeaderSearchOverlay.is-open.is-hero-open,html.gmHeaderSearchOpen .gmHeaderSearchOverlay.is-open.is-hero-open{position:fixed !important;inset:0 !important;z-index:2147483600 !important;display:flex !important;align-items:center !important;justify-content:center !important;padding:calc(var(--lb-sale-h,0px) + 92px) 32px 32px !important;opacity:1 !important;pointer-events:auto !important;overflow:hidden !important;}
html body .gmHeaderSearchOverlay:not(.is-open),body:not(.gmHeaderSearchOpen) .gmHeaderSearchOverlay,html:not(.gmHeaderSearchOpen) .gmHeaderSearchOverlay:not(.is-open){display:none !important;opacity:0 !important;pointer-events:none !important;}
html body .gmHeaderSearchModal.gmHeaderCommandCenter,body.gmHeaderSearchOpen .gmHeaderSearchOverlay.is-open .gmHeaderSearchModal.gmHeaderCommandCenter,html.gmHeaderSearchOpen .gmHeaderSearchOverlay.is-open .gmHeaderSearchModal.gmHeaderCommandCenter,body.gmHeaderSearchOpen .gmHeaderSearchOverlay.is-open.is-hero-open .gmHeaderSearchModal.gmHeaderCommandCenter,html.gmHeaderSearchOpen .gmHeaderSearchOverlay.is-open.is-hero-open .gmHeaderSearchModal.gmHeaderCommandCenter{width:min(1120px,calc(100vw - 64px)) !important;height:auto !important;max-height:min(760px,calc(100vh - var(--lb-sale-h,0px) - 136px)) !important;border-radius:28px !important;display:grid !important;grid-template-rows:auto auto auto minmax(0,1fr) !important;background:radial-gradient(900px 360px at 18% 0%,rgba(95,84,255,.22),transparent 62%),linear-gradient(180deg,rgba(15,20,39,.98),rgba(7,10,24,.98)) !important;border:1px solid rgba(116,132,255,.34) !important;box-shadow:0 40px 120px rgba(0,0,0,.72),0 0 70px rgba(80,96,255,.16) !important;overflow:hidden !important;transform:none !important;margin:0 !important;color:#fff !important;}
html body .gmHeaderSearchTop.gmHeaderCommandTop{padding:16px !important;background:rgba(255,255,255,.025) !important;border-bottom:1px solid rgba(255,255,255,.07) !important;display:flex !important;align-items:center !important;gap:12px !important;}
html body .gmHeaderSearchInputWrap.gmHeaderCommandInputWrap{height:58px !important;border-radius:18px !important;background:rgba(4,8,22,.88) !important;border:1px solid rgba(132,149,255,.38) !important;box-shadow:inset 0 1px 0 rgba(255,255,255,.05),0 0 0 4px rgba(98,105,255,.11) !important;}
html body .gmHeaderSearchInput{font-size:18px !important;font-weight:900 !important;}
html body .gmHeaderSearchClose.gmHeaderCommandClose{width:48px !important;height:48px !important;border-radius:16px !important;background:rgba(255,255,255,.07) !important;border:1px solid rgba(255,255,255,.12) !important;}
html body .gmHeaderCommandHero{padding:14px 18px !important;display:flex !important;align-items:center !important;justify-content:space-between !important;gap:16px !important;min-height:72px !important;background:linear-gradient(90deg,rgba(124,107,255,.10),rgba(0,194,255,.045)) !important;border-bottom:1px solid rgba(255,255,255,.06) !important;}
html body .gmHeaderCommandQuick{padding:14px 16px !important;display:grid !important;grid-template-columns:repeat(6,minmax(0,1fr)) !important;gap:10px !important;background:rgba(255,255,255,.014) !important;border-bottom:1px solid rgba(255,255,255,.06) !important;}
html body .gmHeaderCommandQuickCard{min-width:0 !important;min-height:54px !important;padding:9px 10px !important;border-radius:16px !important;}
html body .gmHeaderCommandQuickCard small{display:none !important;}
html body .gmHeaderCommandBody.gmHeaderSearchResults{padding:16px 18px 18px !important;max-height:none !important;overflow:auto !important;}
html body .gmHeaderCommandSectionHead{margin-bottom:12px !important;}
html body .gmHeaderCommandGameGrid{display:grid !important;grid-template-columns:repeat(4,minmax(0,1fr)) !important;gap:10px !important;}
html body .gmHeaderResult.gmHeaderCommandGame{min-height:64px !important;padding:10px 12px !important;border-radius:16px !important;background:linear-gradient(180deg,rgba(255,255,255,.055),rgba(255,255,255,.026)) !important;border:1px solid rgba(255,255,255,.09) !important;transform:none !important;}
html body .gmHeaderResult.gmHeaderCommandGame:hover,html body .gmHeaderResult.gmHeaderCommandGame.is-active{background:rgba(124,107,255,.18) !important;border-color:rgba(124,107,255,.50) !important;}
html body .gmHeaderCommandGameIcon{width:42px !important;height:42px !important;min-width:42px !important;border-radius:13px !important;}
html body .gmHeaderCommandActions,html body .gmHeaderGameActions{display:none !important;}
}
@media (min-width:901px) and (max-width:1220px){html body .gmHeaderCommandQuick{grid-template-columns:repeat(3,minmax(0,1fr)) !important;}
html body .gmHeaderCommandGameGrid{grid-template-columns:repeat(3,minmax(0,1fr)) !important;}
}</style>

<style id="lb-header-coming-soon-css">.gmHeaderCommandQuickCard{appearance:none;-webkit-appearance:none;border:0;text-align:left;font:inherit;cursor:pointer;}
.gmHeaderMarketplaceViewAll{display:inline-flex;align-items:center;justify-content:center;min-height:34px;padding:0 13px;border-radius:12px;border:1px solid rgba(92,139,255,.48);background:linear-gradient(180deg,rgba(45,86,158,.38),rgba(21,39,78,.48));color:#dbe7ff!important;font-weight:900;font-size:12px;text-decoration:none!important;white-space:nowrap;box-shadow:inset 0 1px 0 rgba(255,255,255,.08);}
.gmHeaderMarketplaceViewAll:hover{border-color:rgba(125,167,255,.78);background:linear-gradient(180deg,rgba(60,105,190,.52),rgba(26,52,102,.62));}
@media (max-width:720px){.gmHeaderMarketplaceSort{gap:6px;flex-wrap:wrap}
.gmHeaderMarketplaceViewAll{min-height:30px;padding:0 10px;font-size:11px}
}
.gmComingSoonBadge{position:relative;display:inline-flex;align-items:center;justify-content:center;width:max-content;min-width:42px;min-height:20px;padding:4px 9px;border-radius:999px;font-size:10px;font-weight:850;line-height:1;letter-spacing:.01em;text-transform:none;color:#ffe39b;background:linear-gradient(180deg,rgba(255,205,92,.145),rgba(255,205,92,.055));border:1px solid rgba(255,205,92,.48);box-shadow:0 3px 9px rgba(0,0,0,.20),inset 0 1px 0 rgba(255,255,255,.10);white-space:nowrap;pointer-events:none;}
.gmComingSoonBadge::before,.gmComingSoonBadge::after,.gmComingSoonBadge i{display:none!important;}
.gmComingSoonBadge--small{margin-left:auto;min-width:38px;min-height:18px;padding:3px 8px;font-size:9px;}
.gmComingSoonBadge--floating{position:absolute;right:12px;top:50%;transform:translateY(-50%);z-index:3;}
.navbar-top .gmUnifiedGame.is-coming-soon,.navbar-top .gmUnifiedBrowseGame.is-coming-soon,html body .gmHeaderResult.gmHeaderCommandGame.is-coming-soon,html body .gmHeaderResult.gmHeaderCommandGame.is-filter-soon{position:relative!important;border-color:rgba(255,255,255,.11)!important;background:linear-gradient(180deg,rgba(255,255,255,.052),rgba(255,255,255,.025))!important;}
.navbar-top .gmUnifiedGame.is-coming-soon:hover,.navbar-top .gmUnifiedBrowseGame.is-coming-soon:hover,html body .gmHeaderResult.gmHeaderCommandGame.is-coming-soon:hover,html body .gmHeaderResult.gmHeaderCommandGame.is-filter-soon:hover{border-color:rgba(255,205,92,.30)!important;background:radial-gradient(170px 50px at 100% 50%,rgba(255,205,92,.045),transparent 72%),linear-gradient(180deg,rgba(255,255,255,.060),rgba(255,255,255,.028))!important;}
.navbar-top .gmUnifiedGame.is-coming-soon::after,.navbar-top .gmUnifiedBrowseGame.is-coming-soon::after,html body .gmHeaderResult.gmHeaderCommandGame.is-coming-soon::after,html body .gmHeaderResult.gmHeaderCommandGame.is-filter-soon::after{display:none!important;}
.navbar-top .gmUnifiedGame.is-coming-soon .gmUnifiedGameIcon,.navbar-top .gmUnifiedBrowseGame.is-coming-soon .gmUnifiedBrowseIcon,html body .gmHeaderResult.gmHeaderCommandGame.is-coming-soon .gmHeaderCommandGameIcon,html body .gmHeaderResult.gmHeaderCommandGame.is-filter-soon .gmHeaderCommandGameIcon{filter:saturate(.94) brightness(.96);}
.navbar-top .gmUnifiedGame.is-coming-soon .gmUnifiedGameName,.navbar-top .gmUnifiedBrowseGame.is-coming-soon>span:not(.gmUnifiedBrowseIcon):not(.gmComingSoonBadge){color:rgba(255,255,255,.92)!important;}
.navbar-top .gmUnifiedGame.is-coming-soon{gap:8px;}
.navbar-top .gmUnifiedGame.is-coming-soon .gmComingSoonBadge{margin-top:6px;}
.navbar-top .gmUnifiedBrowseGame.is-coming-soon{padding-right:70px!important;}
.navbar-top .gmUnifiedBrowseGame.is-coming-soon .gmComingSoonBadge{position:absolute;right:12px;top:50%;transform:translateY(-50%);}
.navbar-top .gmUnifiedBrowseGame.is-coming-soon>span:not(.gmUnifiedBrowseIcon):not(.gmComingSoonBadge),html body .gmHeaderResult.gmHeaderCommandGame.is-coming-soon .gmHeaderResultTitle,html body .gmHeaderResult.gmHeaderCommandGame.is-filter-soon .gmHeaderResultTitle{min-width:0!important;overflow:hidden!important;text-overflow:ellipsis!important;white-space:nowrap!important;}
.gmHeaderResultMeta--soon{display:none!important;}
html body .gmHeaderResult.gmHeaderCommandGame.is-coming-soon .gmHeaderCommandGameMain,html body .gmHeaderResult.gmHeaderCommandGame.is-filter-soon .gmHeaderCommandGameMain{min-width:0!important;padding-right:70px!important;}
html body .gmHeaderResult.gmHeaderCommandGame.is-coming-soon .gmHeaderGameActions,html body .gmHeaderResult.gmHeaderCommandGame.is-filter-soon .gmHeaderGameActions{display:none!important;}
html body .gmHeaderResult.gmHeaderCommandGame:not(.is-coming-soon):not(.is-filter-soon)>.gmComingSoonBadge--floating{display:none!important;}
html body .gmHeaderResult.gmHeaderCommandGame.is-filter-soon>.gmComingSoonBadge--floating{display:inline-flex!important;}
@media(max-width:900px){.gmComingSoonBadge{min-width:38px;min-height:18px;padding:3px 8px;font-size:9px;}
html body .gmHeaderResult.gmHeaderCommandGame.is-coming-soon .gmHeaderCommandGameMain{padding-right:0!important;}
.gmComingSoonBadge--floating{position:static;transform:none;margin-left:58px;margin-top:0;margin-bottom:2px;}
.navbar-top .gmUnifiedBrowseGame.is-coming-soon{padding-right:12px!important;}
.navbar-top .gmUnifiedBrowseGame.is-coming-soon .gmComingSoonBadge{position:static;transform:none;margin-left:auto;}
}
@media(max-width:520px){.gmComingSoonBadge--floating{margin-left:0;}
html body .gmHeaderResult.gmHeaderCommandGame.is-coming-soon{padding-right:12px!important;}
}
@media (min-width:1025px){.navbar-top .right .lb-desktop-client-avatar-btn{position:relative !important;width:62px !important;min-width:62px !important;height:62px !important;padding:0 !important;border-radius:999px !important;display:inline-flex !important;align-items:center !important;justify-content:center !important;overflow:visible !important;background:radial-gradient(circle at 35% 22%,rgba(255,255,255,.22),rgba(255,255,255,0) 34%),linear-gradient(135deg,rgba(103,112,255,.98),rgba(77,92,244,.98)) !important;border:1px solid rgba(155,164,255,.62) !important;box-shadow:0 18px 42px rgba(87,96,255,.34),0 0 0 7px rgba(99,102,241,.10),inset 0 1px 0 rgba(255,255,255,.22) !important;color:#fff !important;font-size:22px !important;line-height:1 !important;transform:translateZ(0) !important;}
.navbar-top .right .lb-desktop-client-avatar-btn.has-client-avatar{background:rgba(9,12,28,.74) !important;border:2px solid rgba(118,130,255,.70) !important;box-shadow:0 18px 46px rgba(77,92,244,.34),0 0 0 7px rgba(99,102,241,.12),inset 0 1px 0 rgba(255,255,255,.14) !important;}
.navbar-top .right .lb-desktop-client-avatar-btn::after{content:"";position:absolute;right:2px;bottom:3px;width:15px;height:15px;border-radius:999px;background:#22c55e;border:3px solid #070a18;box-shadow:0 0 16px rgba(34,197,94,.60);z-index:4;}
.navbar-top .right .lb-desktop-client-avatar-img{position:relative !important;z-index:2 !important;width:100% !important;height:100% !important;display:block !important;object-fit:cover !important;border-radius:999px !important;}
.navbar-top .right .lb-desktop-client-avatar-glow{position:absolute;inset:-9px;border-radius:999px;background:radial-gradient(circle,rgba(99,102,241,.30),transparent 64%);opacity:.90;pointer-events:none;z-index:-1;}
.navbar-top .right .lb-desktop-client-avatar-btn:hover{transform:translateY(-1px) scale(1.04) !important;box-shadow:0 22px 54px rgba(87,96,255,.44),0 0 0 8px rgba(99,102,241,.14),inset 0 1px 0 rgba(255,255,255,.24) !important;}
}</style>


<script id="lb-marketplace-search-js">
/* ═══════════════════════════════════════════════════════════════
   LB MARKETPLACE SEARCH — inline dropdown controller
   ═══════════════════════════════════════════════════════════════ */
(function () {
  var root = document.getElementById('lbms');
  if (!root) return;

  var field   = document.getElementById('lbmsField');
  var input   = document.getElementById('lbmsInput');
  var panel   = document.getElementById('lbmsPanel');
  var grid    = document.getElementById('lbmsGrid');
  var empty   = document.getElementById('lbmsEmpty');
  var countEl = document.getElementById('lbmsCount');
  var clearBt = document.getElementById('lbmsClear');
  var sheetX  = document.getElementById('lbmsSheetClose');
  var categoryHeading = document.getElementById('lbmsCategoryHeading');
  var productHeading = document.getElementById('lbmsProductHeading');

  var cards = Array.prototype.slice.call(grid.querySelectorAll('[data-lbms-item]'));
  var tabs  = Array.prototype.slice.call(panel.querySelectorAll('[data-lbms-tab]'));
  var sorts = Array.prototype.slice.call(panel.querySelectorAll('[data-lbms-sort]'));

  var scrim = document.createElement('div');
  scrim.className = 'lbms__scrim';
  root.appendChild(scrim);

  // The desktop navbar is hidden on mobile, so the full-screen sheet is
  // portalled to <body> while it is open and put back afterwards.
  var anchor = document.createComment('lbms-anchor');
  root.parentNode.insertBefore(anchor, root);
  function portalOut() { if (root.parentNode !== document.body) document.body.appendChild(root); }
  function portalBack() { if (anchor.parentNode && root.parentNode !== anchor.parentNode) anchor.parentNode.insertBefore(root, anchor); }

  var state = { open: false, tab: 'all', sort: 'popular', q: '', cursor: -1 };
  var visible = [];
  var offerText = {
    one: <?= json_encode(t('offer')) ?>,
    many: <?= json_encode(t('offers')) ?>,
    soon: <?= json_encode(t('Coming soon')) ?>
  };

  var ALIASES = {
    lol: 'league of legends', league: 'league of legends',
    val: 'valorant', valo: 'valorant',
    tft: 'teamfight tactics',
    cod: 'call of duty', mw: 'call of duty', wz: 'call of duty',
    ow: 'overwatch', ow2: 'overwatch',
    rl: 'rocket league',
    gta: 'grand theft auto',
    coc: 'clash of clans',
    fn: 'fortnite',
    mc: 'minecraft',
    rp: 'riot points', vp: 'valorant points'
  };

  function isMobile() { return window.matchMedia('(max-width:1040px)').matches; }

  function terms(q) {
    q = (q || '').toLowerCase().trim().replace(/\s+/g, ' ');
    if (!q) return [];
    var out = [q];
    q.split(' ').forEach(function (w) {
      if (ALIASES[w] && out.indexOf(ALIASES[w]) === -1) out.push(ALIASES[w]);
    });
    return out;
  }

  function matchesQuery(card, list) {
    if (!list.length) return true;
    var hay = card.getAttribute('data-search') || '';
    for (var i = 0; i < list.length; i++) if (hay.indexOf(list[i]) !== -1) return true;
    return false;
  }

  function matchesTab(card) {
    if (state.tab === 'all') return true;
    var kind = card.getAttribute('data-lbms-item');
    if (state.tab === 'digital') return kind === 'digital';
    if (kind === 'digital') return false;
    var cats = ' ' + (card.getAttribute('data-cats') || '') + ' ';
    return cats.indexOf(' ' + state.tab + ' ') !== -1;
  }

  function currentOfferCount(card) {
    if (card.getAttribute('data-lbms-item') !== 'game') return 0;
    var attr = state.tab === 'all' ? 'data-offers' : 'data-offers-' + state.tab;
    return Math.max(0, parseInt(card.getAttribute(attr) || '0', 10));
  }

  function updateCardOfferDisplay(card) {
    if (card.getAttribute('data-lbms-item') !== 'game') return;
    var count = currentOfferCount(card);
    var isSoon = count <= 0;
    var meta = card.querySelector('.lbms__cardMeta');
    var soon = card.querySelector('.lbms__soon');

    card.classList.toggle('is-soon', isSoon);
    if (meta) {
      meta.textContent = isSoon
        ? offerText.soon
        : count.toLocaleString('en-US') + ' ' + (count === 1 ? offerText.one : offerText.many);
    }
    if (soon) soon.hidden = !isSoon;
  }

  function updateCardDestination(card) {
    if (card.getAttribute('data-lbms-item') !== 'game') return;

    var defaultHref = card.getAttribute('data-href-default') || card.getAttribute('data-href') || '/';
    var categoryHref = state.tab === 'all' ? '' : card.getAttribute('data-href-' + state.tab);
    var href = categoryHref || defaultHref;
    var link = card.querySelector('.lbms__cardMain');

    card.setAttribute('data-href', href);
    if (link) link.setAttribute('href', href);
  }

  function sortValue(card) {
    return state.sort === 'az'
      ? (card.getAttribute('data-name') || '')
      : currentOfferCount(card);
  }

  function render() {
    var list = terms(state.q);
    visible = [];

    cards.forEach(function (card) {
      updateCardOfferDisplay(card);
      updateCardDestination(card);
      var searchOnly = card.getAttribute('data-search-only') === '1';
      var ok = (!searchOnly || state.q.length > 0) && matchesTab(card) && matchesQuery(card, list);
      card.hidden = !ok;
      card.classList.remove('is-active');
      if (ok) visible.push(card);
    });

    // sort (DOM order only for what's visible)
    var sorted = visible.slice().sort(function (a, b) {
      if (state.sort === 'az') {
        return sortValue(a).localeCompare(sortValue(b));
      }
      var diff = sortValue(b) - sortValue(a);
      if (diff !== 0) return diff;
      return parseInt(a.getAttribute('data-order'), 10) - parseInt(b.getAttribute('data-order'), 10);
    });
    var productCards = sorted.filter(function (card) { return card.classList.contains('lbms__card--product'); });
    var categoryCards = sorted.filter(function (card) { return !card.classList.contains('lbms__card--product'); });
    var showProductGroups = state.q.trim().length > 0 && productCards.length > 0;

    if (categoryHeading) categoryHeading.hidden = !showProductGroups || categoryCards.length === 0;
    if (productHeading) productHeading.hidden = !showProductGroups;

    if (showProductGroups) {
      if (categoryCards.length && categoryHeading) grid.appendChild(categoryHeading);
      categoryCards.forEach(function (card) { grid.appendChild(card); });
      if (productHeading) grid.appendChild(productHeading);
      productCards.forEach(function (card) { grid.appendChild(card); });
    } else {
      sorted.forEach(function (card) { grid.appendChild(card); });
    }
    visible = sorted;

    empty.hidden = visible.length > 0;
    var word = visible.length === 1
      ? (countEl.getAttribute('data-one') || 'result')
      : (countEl.getAttribute('data-many') || 'results');
    countEl.textContent = visible.length + ' ' + word;
    state.cursor = -1;
    clearBt.hidden = state.q.length === 0;
  }

  function open() {
    if (state.open) return;
    state.open = true;
    root.classList.add('is-open');
    panel.hidden = false;
    input.setAttribute('aria-expanded', 'true');
    if (isMobile()) {
      portalOut();
      document.documentElement.classList.add('lbms-locked');
      document.body.classList.add('lbms-locked');
      setTimeout(function () { try { input.focus({ preventScroll: true }); } catch (e) { input.focus(); } }, 30);
    }
    render();
  }

  function close() {
    if (!state.open) return;
    state.open = false;
    root.classList.remove('is-open');
    panel.hidden = true;
    input.setAttribute('aria-expanded', 'false');
    input.blur();
    document.documentElement.classList.remove('lbms-locked');
    document.body.classList.remove('lbms-locked');
    portalBack();
    if (typeof releaseMega === 'function') releaseMega();
  }

  window.addEventListener('resize', function () {
    if (state.open && !isMobile()) close();
  });

  function moveCursor(step) {
    if (!visible.length) return;
    if (state.cursor >= 0 && visible[state.cursor]) visible[state.cursor].classList.remove('is-active');
    state.cursor = (state.cursor + step + visible.length) % visible.length;
    var card = visible[state.cursor];
    card.classList.add('is-active');
    card.scrollIntoView({ block: 'nearest' });
  }

  /* ── Events ─────────────────────────────────────────────── */
  field.addEventListener('click', function (e) {
    if (e.target.closest('.lbms__clear, .lbms__sheetClose')) return;
    input.focus();
    open();
  });
  input.addEventListener('focus', open);

  input.addEventListener('input', function () {
    state.q = input.value;
    if (!state.open) open();
    render();
  });

  input.addEventListener('keydown', function (e) {
    if (e.key === 'Escape') { close(); return; }
    if (e.key === 'ArrowDown') { e.preventDefault(); open(); moveCursor(1); return; }
    if (e.key === 'ArrowUp') { e.preventDefault(); moveCursor(-1); return; }
    if (e.key === 'Enter') {
      var target = state.cursor >= 0 ? visible[state.cursor] : visible[0];
      if (target) { e.preventDefault(); window.location.href = target.getAttribute('data-href'); }
    }
  });

  clearBt.addEventListener('click', function () {
    input.value = '';
    state.q = '';
    input.focus();
    render();
  });

  sheetX.addEventListener('click', close);
  scrim.addEventListener('click', close);

  tabs.forEach(function (tab) {
    tab.addEventListener('click', function () {
      state.tab = tab.getAttribute('data-lbms-tab');
      tabs.forEach(function (t) {
        var on = t === tab;
        t.classList.toggle('is-active', on);
        t.setAttribute('aria-selected', on ? 'true' : 'false');
      });
      render();
    });
  });

  sorts.forEach(function (btn) {
    btn.addEventListener('click', function () {
      state.sort = btn.getAttribute('data-lbms-sort');
      sorts.forEach(function (b) { b.classList.toggle('is-active', b === btn); });
      render();
    });
  });

  document.addEventListener('click', function (e) {
    if (!state.open || isMobile()) return;
    if (!root.contains(e.target)) close();
  });

  document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape' && state.open) { close(); return; }
    // "/" focuses the search from anywhere
    if (e.key === '/' && !state.open) {
      var tag = (e.target.tagName || '').toLowerCase();
      if (tag === 'input' || tag === 'textarea' || e.target.isContentEditable) return;
      e.preventDefault();
      input.focus();
      open();
    }
  });

  // External openers (mobile icon, marketplace mega menu button)
  var megaNav = null;
  var megaEl = null;
  function releaseMega() {
    if (megaEl) { megaEl.style.removeProperty('display'); megaEl = null; }
    if (megaNav) { megaNav.classList.remove('lbms-hide-mega'); megaNav = null; }
  }
  function hideMega(nav) {
    if (!nav) return;
    nav.classList.remove('is-market-open', 'is-open', 'open', 'active');
    nav.classList.add('lbms-hide-mega');
    megaNav = nav;
    megaEl = nav.querySelector('.gmUnifiedMega, .mega-dropdown');
    // inline !important beats every stylesheet rule, including the :hover ones
    if (megaEl) megaEl.style.setProperty('display', 'none', 'important');
    var trig = nav.querySelector('.gmUnifiedTrigger, .mega-link');
    if (trig && trig.blur) trig.blur();
  }
  var EXTERNAL_TRIGGERS = '[data-lbms-open], [onclick*="gmOpenNavSearch"], .gmUnifiedSearchMini, .mobile-search-icon';

  function openFromTrigger(trigger) {
    hideMega(trigger ? trigger.closest('.gmUnifiedNav, .nav-has-mega') : null);
    if (!isMobile()) { try { window.scrollTo({ top: 0, behavior: 'smooth' }); } catch (e) { window.scrollTo(0, 0); } }
    // Let the mega menu finish hiding before opening the search panel. Keeping
    // lbms-hide-mega active until close() also prevents :hover from reopening it.
    requestAnimationFrame(function () {
      open();
      setTimeout(function () { try { input.focus({ preventScroll: true }); } catch (err) { input.focus(); } }, 30);
    });
  }

  // Capture phase: runs before any inline onclick (e.g. the hero "Order Now"
  // button) and before other listeners, so nothing can swallow the click.
  document.addEventListener('click', function (e) {
    var trigger = e.target.closest ? e.target.closest(EXTERNAL_TRIGGERS) : null;
    if (!trigger) return;
    if (root.contains(trigger)) return;
    e.preventDefault();
    e.stopPropagation();
    if (e.stopImmediatePropagation) e.stopImmediatePropagation();
    openFromTrigger(trigger);
  }, true);

  // Landing page hero calls this global directly
  window.gmOpenNavSearch = function () { openFromTrigger(null); };
  window.addEventListener('load', function () {
    window.gmOpenNavSearch = function () { openFromTrigger(null); };
  });

  render();
})();
</script>

<style id="lb-header-desktop-redesign">
/* LB HEADER — DESKTOP RIGHT SIDE v4
   Racoon-Look, aber MIT Flagge, enger am Trennstrich und ~15% groesser
   (kompensiert die 0.88 Zoomstufe der Seite). */

@media (min-width:1025px){

  /* Container */
  nav.navbar-top .right{display:flex !important;align-items:center !important;gap:0 !important;flex-wrap:nowrap !important;}
  nav.navbar-top .right .dropdown-menu{display:flex !important;align-items:center !important;}
  nav.navbar-top .right .lb-client-tools{display:flex !important;align-items:center !important;gap:16px !important;}

  /* Trennstriche — Pill sitzt jetzt dicht dran */
  nav.navbar-top .right .dropdown-menu::after,
  nav.navbar-top .right .lb-client-tools::before{
    content:"";width:1px;height:34px;flex:0 0 1px;background:rgba(255,255,255,.13);
  }
  nav.navbar-top .right .dropdown-menu::after{margin-left:10px;}
  nav.navbar-top .right .lb-client-tools::before{margin-right:18px;}

  /* 1) Sprache / Waehrung — mit Flagge, ohne Box */
  nav.navbar-top .right .settings-pill{
    height:auto !important;min-height:0 !important;
    display:inline-flex !important;align-items:center !important;gap:11px !important;
    padding:7px 12px !important;margin:0 !important;
    border:0 !important;border-radius:12px !important;
    background:rgba(255,255,255,.06) !important;box-shadow:none !important;
    color:rgba(255,255,255,.82) !important;cursor:pointer !important;
    transition:color .16s ease,background .16s ease !important;
  }
  nav.navbar-top .right .settings-pill:hover{color:#fff !important;background:rgba(255,255,255,.11) !important;transform:none !important;}
  nav.navbar-top .right .settings-pill-flag{
    display:flex !important;
    width:30px !important;height:30px !important;flex:0 0 30px !important;
    border-radius:9px !important;overflow:hidden !important;background:transparent !important;
    box-shadow:0 0 0 1px rgba(255,255,255,.16) !important;
  }
  nav.navbar-top .right .settings-pill-flag img{width:100% !important;height:100% !important;object-fit:cover !important;display:block !important;}
  nav.navbar-top .right .settings-pill-text{display:inline-flex !important;align-items:center !important;gap:7px !important;}
  nav.navbar-top .right .settings-pill-lang,
  nav.navbar-top .right .settings-pill-cur{
    font-size:16px !important;font-weight:800 !important;
    letter-spacing:.05em !important;color:inherit !important;opacity:1 !important;
  }
  nav.navbar-top .right .settings-pill-lang::after{
    content:"|" !important;margin:0 0 0 7px !important;
    color:rgba(255,255,255,.22) !important;font-weight:400 !important;
  }
  nav.navbar-top .right .settings-pill-chevron{
    font-size:12px !important;color:rgba(255,255,255,.42) !important;
    transition:transform .16s ease !important;
  }
  nav.navbar-top .right .settings-pill[aria-expanded="true"] .settings-pill-chevron{transform:rotate(180deg) !important;}

  /* 2) Icon-Buttons — nackt */
  nav.navbar-top .right .lb-client-icon-btn{
    width:46px !important;min-width:46px !important;height:46px !important;min-height:46px !important;
    padding:0 !important;border:0 !important;border-radius:13px !important;
    background:rgba(255,255,255,.06) !important;box-shadow:none !important;
    transition:background .16s ease !important;
  }
  nav.navbar-top .right .lb-client-icon-btn i{
    font-size:22px !important;color:rgba(255,255,255,.82) !important;filter:none !important;
    transition:color .16s ease !important;
  }
  nav.navbar-top .right .lb-client-icon-btn:hover,
  nav.navbar-top .right .lb-client-tool.is-open .lb-client-icon-btn{
    background:rgba(255,255,255,.12) !important;border:0 !important;transform:none !important;box-shadow:none !important;
  }
  nav.navbar-top .right .lb-client-icon-btn:hover i,
  nav.navbar-top .right .lb-client-tool.is-open .lb-client-icon-btn i{color:#fff !important;}
  /* Counts can be 2-3 digits, so the badge is a pill that grows instead of a
     fixed circle that overlaps the bell. */
  nav.navbar-top .right .lb-client-icon-btn{overflow:visible !important;}
  nav.navbar-top .right .lb-client-badge{
    top:-5px !important;right:-5px !important;
    width:auto !important;min-width:20px !important;height:20px !important;
    padding:0 6px !important;border-radius:999px !important;
    font-size:11px !important;font-weight:800 !important;line-height:20px !important;
    letter-spacing:.01em !important;
    border:2px solid #0b0a1b !important;
    background:#e11d48 !important;
    box-shadow:0 4px 10px rgba(225,29,72,.35) !important;
  }

  /* 3) Profil — groesstes Element */
  nav.navbar-top .right .lb-client-avatar-toggle.lb-client-profile-summary{
    height:auto !important;min-height:0 !important;min-width:0 !important;
    display:flex !important;align-items:center !important;gap:13px !important;
    padding:5px 8px 5px 5px !important;margin-left:4px !important;
    border:0 !important;border-radius:16px !important;
    background:rgba(255,255,255,.06) !important;box-shadow:none !important;
    transition:background .16s ease !important;
  }
  nav.navbar-top .right .lb-client-avatar-toggle.lb-client-profile-summary:hover,
  nav.navbar-top .right .lb-client-profile.is-open .lb-client-avatar-toggle.lb-client-profile-summary{
    background:rgba(255,255,255,.11) !important;border:0 !important;transform:none !important;box-shadow:none !important;
  }
  nav.navbar-top .right .lb-profile-avatar{
    width:54px !important;height:54px !important;flex:0 0 54px !important;
    border-radius:999px !important;overflow:hidden !important;
    border:2px solid rgba(139,124,255,.75) !important;
    box-shadow:0 0 0 4px rgba(124,107,255,.13) !important;
    background:rgba(8,11,27,.8) !important;
  }
  nav.navbar-top .right .lb-profile-avatar img{width:100% !important;height:100% !important;object-fit:cover !important;}
  nav.navbar-top .right .lb-profile-avatar i{font-size:22px !important;color:#c7d2fe !important;}
  /* The booster status dot is a child of the avatar, so the avatar (and the
     toggle around it) must not clip. Higher specificity than the rule above. */
  nav.navbar-top .right .lb-profile-avatar.lb-avail-avatar{overflow:visible !important;}
  nav.navbar-top .right .lb-profile-avatar.lb-avail-avatar img{border-radius:999px !important;}
  nav.navbar-top .right .lb-client-avatar-toggle.lb-client-profile-summary{overflow:visible !important;}
  nav.navbar-top .right .lb-avail-avatar .lb-avail-avatar-dot{right:-4px !important;bottom:-4px !important;z-index:6 !important;}
  nav.navbar-top .right .lb-profile-meta{
    display:flex !important;flex-direction:column !important;justify-content:center !important;
    min-width:0 !important;max-width:170px !important;text-align:left !important;
  }
  nav.navbar-top .right .lb-profile-name{
    font-size:18px !important;font-weight:800 !important;line-height:1.15 !important;color:#fff !important;
  }
  nav.navbar-top .right .lb-profile-role{
    font-size:14px !important;font-weight:600 !important;line-height:1.2 !important;
    color:rgba(255,255,255,.46) !important;margin-top:2px !important;
    text-transform:none !important;letter-spacing:0 !important;
  }
  nav.navbar-top .right .lb-profile-chevron{
    width:18px !important;flex:0 0 18px !important;font-size:14px !important;
    color:rgba(255,255,255,.42) !important;margin-left:2px !important;
    transition:transform .16s ease !important;
  }
  nav.navbar-top .right .lb-client-profile.is-open .lb-profile-chevron{transform:rotate(180deg) !important;}

  /* 4) Login-CTA (ausgeloggt) */
  nav.navbar-top .right #login-btn.btn.primary{
    height:52px !important;min-height:52px !important;
    display:inline-flex !important;align-items:center !important;gap:10px !important;
    margin-left:18px !important;padding:0 26px !important;
    border:0 !important;border-radius:14px !important;
    color:#fff !important;font-size:17px !important;font-weight:700 !important;cursor:pointer !important;
    background:#6366F1 !important;background-image:none !important;
    box-shadow:0 8px 22px rgba(99,102,241,.30) !important;
    transition:transform .16s ease,box-shadow .16s ease !important;
  }
  nav.navbar-top .right #login-btn.btn.primary i{font-size:17px !important;}
  nav.navbar-top .right #login-btn.btn.primary:hover{
    transform:translateY(-1px) !important;background:#5558e8 !important;background-image:none !important;
    box-shadow:0 12px 30px rgba(99,102,241,.42) !important;
  }

  nav.navbar-top .right button:focus-visible{outline:2px solid #a78bfa !important;outline-offset:3px !important;}
}

/* Enge Desktops */
@media (min-width:1025px) and (max-width:1280px){
  nav.navbar-top .right .lb-client-tools{gap:10px !important;}
  nav.navbar-top .right .lb-client-tools::before{margin-right:12px !important;}
  nav.navbar-top .right .lb-profile-meta{display:none !important;}
  nav.navbar-top .right .lb-profile-avatar{width:46px !important;height:46px !important;flex:0 0 46px !important;}
  nav.navbar-top .right .settings-pill-lang{display:none !important;}
}

@media (prefers-reduced-motion:reduce){
  nav.navbar-top .right .settings-pill,
  nav.navbar-top .right .lb-client-icon-btn,
  nav.navbar-top .right #login-btn,
  nav.navbar-top .right .lb-client-avatar-toggle.lb-client-profile-summary{transition:none !important;}
}
</style>

<style id="lb-header-tablet-breakpoint-fix">
/* The desktop account/notification widgets are styled from 992px upward. Below
   that exact boundary use the real mobile header and explicitly size its profile. */
@media (min-width:768px) and (max-width:1024px) {
  html body nav.navbar-top { display:none !important; }
  html body nav.navbar-mobile {
    display:flex !important;align-items:center !important;justify-content:space-between !important;
    position:fixed !important;top:var(--lb-sale-h,0px) !important;left:0 !important;width:100% !important;
    min-height:68px !important;padding:10px 22px !important;box-sizing:border-box !important;
    z-index:1000010 !important;background:rgba(6,7,17,.96) !important;
    border-bottom:1px solid rgba(255,255,255,.07) !important;box-shadow:0 8px 24px rgba(0,0,0,.22) !important;
  }
  html body nav.navbar-mobile .logo img { width:42px !important;height:auto !important; }
  html body nav.navbar-mobile .right { display:flex !important;align-items:center !important;gap:10px !important; }
  html body nav.navbar-mobile .right .dropdown-menu { display:none !important; }
  html body nav.navbar-mobile .menu-icon,
  html body nav.navbar-mobile .mobile-search-icon {
    width:40px !important;height:40px !important;min-width:40px !important;padding:0 !important;box-sizing:border-box !important;
    display:inline-flex !important;align-items:center !important;justify-content:center !important;border-radius:11px !important;
    border:1px solid rgba(255,255,255,.11) !important;background:#110f1f !important;color:#fff !important;
    box-shadow:none !important;appearance:none !important;-webkit-appearance:none !important;
  }
  html body nav.navbar-mobile .mobile-search-icon i { color:#fff !important;font-size:17px !important;line-height:1 !important; }
  html body nav.navbar-mobile .menu-icon img { width:20px !important;height:20px !important; }
  html body nav.navbar-mobile #login-btn-mobile-header.mobile-top-profile-btn {
    width:44px !important;min-width:44px !important;height:44px !important;min-height:44px !important;
    padding:0 !important;border-radius:14px !important;overflow:hidden !important;display:flex !important;
    align-items:center !important;justify-content:center !important;
  }
  html body nav.navbar-mobile #login-btn-mobile-header .mobile-top-profile-avatar {
    width:100% !important;height:100% !important;display:block !important;object-fit:cover !important;border-radius:12px !important;
  }
}
</style>
