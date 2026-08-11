<?php
// Updated Dashboard:
// - Verified status is read LIVE from DB (boosters.verified), not from session BOOSTER_DATA
// - "Verify now" opens a modal with Personal Details + uploads (same action/fieldnames as personal-details.php)
// - Modal AUTO-opens on every page load when NOT VERIFIED
?>

<?= $this->layout('booster/layouts/main', [
  'meta' => [
    'title' => 'Dashboard - Booster Area | LoLBoost.gg',
    'h1' => 'Booster Dashboard',
    'description' => 'Access your profile, manage your orders and connect with the team.',
  ]
]) ?>

<?php
// Zentrale Booster Area Base URL
if (!defined('BSTR_URL')) {
  define('BSTR_URL', 'https://lolboost.gg/booster-area');
}

// Zentrale Icon Base URL
if (!defined('ICON_URL')) {
  define('ICON_URL', 'https://lolboost.gg/public/uploads/icons');
}

// =============================
// Staff dynamisch aus DB ziehen
// =============================

// Rollen (UI Mapping)
$roleUi = [
  1 => ['label' => 'Admin',            'icon' => 'fa fa-user fas fa-user fa-solid fa-user',                 'nameClass' => 'ov-name-red'],
  2 => ['label' => 'Owner',            'icon' => 'fa fa-crown fas fa-crown fa-solid fa-crown',               'nameClass' => 'ov-name-red'],
  3 => ['label' => 'Head Support',     'icon' => 'fa fa-shield fas fa-shield-alt fa-solid fa-shield',        'nameClass' => 'ov-name-green'],
  4 => ['label' => 'Booster Helper',   'icon' => 'fa fa-swords fas fa-swords fa-solid fa-swords',            'nameClass' => 'ov-name-lightgreen'],
  5 => ['label' => 'Sales Manager',    'icon' => 'fa fa-chart-line fas fa-chart-line fa-solid fa-chart-line','nameClass' => 'ov-name-blue'],
  6 => ['label' => 'Customer Support', 'icon' => 'fa fa-headset fas fa-headset fa-solid fa-headset',          'nameClass' => 'ov-name-purple'],
];

// Support Team im Booster Dashboard, nach Admin ID rollenbasiert.
// Nur diese IDs werden angezeigt, damit keine internen oder alten Admins auftauchen.
$adminRoleOverrides = [
  2  => 2, // Ricardo, Owner
  3  => 2, // Kevin, Owner
  12 => 3, // Jan, Head Support
  51 => 5, // SKRILL, Sales Manager
  23 => 6, // Sharlok, Customer Support
  24 => 6, // nototakuu, Customer Support
];

// Icon URL normalisieren: DB hat teils Dateiname, teils volle URL
if (!function_exists('admin_icon_url')) {
  function admin_icon_url($icon) {
    $icon = trim((string)$icon);

    // Default (wenn leer / NULL / default.png)
    if ($icon === '' || $icon === 'default.png') {
      return rtrim(ICON_URL, '/') . '/default.png';
    }

    // Voll-URL in DB
    if (preg_match('~^https?://~i', $icon)) {
      return $icon;
    }

    // Dateiname in DB -> ICON_URL/<filename>
    return rtrim(ICON_URL, '/') . '/' . ltrim($icon, '/');
  }
}

// Helper: leer, NULL oder nur Whitespaces?
if (!function_exists('ov_is_blank')) {
  function ov_is_blank($v): bool {
    if ($v === null) return true;
    if (is_string($v) && trim($v) === '') return true;
    return false;
  }
}

// Online/Offline helper for admins (best-effort across different schemas)
// Rules:
// - explicit boolean fields: is_online/online => true
// - status field equals "online" => true
// - last_seen/last_activity/updated_at within 5 minutes => true
if (!function_exists('ov_admin_is_online')) {
  function ov_admin_is_online(array $admin): bool {
    foreach (['is_online', 'online'] as $k) {
      if (array_key_exists($k, $admin)) {
        $v = $admin[$k];
        if (is_bool($v)) return $v;
        if (is_numeric($v)) return ((int)$v) === 1;
        if (is_string($v)) {
          $vv = strtolower(trim($v));
          if (in_array($vv, ['1', 'true', 'yes', 'online'], true)) return true;
          if (in_array($vv, ['0', 'false', 'no', 'offline'], true)) return false;
        }
      }
    }

    if (isset($admin['status']) && is_string($admin['status'])) {
      $s = strtolower(trim($admin['status']));
      if ($s === 'online') return true;
      if ($s === 'offline') return false;
    }

    $now = time();
    foreach (['last_seen', 'last_activity', 'last_active', 'updated_at'] as $k) {
      if (!empty($admin[$k])) {
        $ts = is_numeric($admin[$k]) ? (int)$admin[$k] : strtotime((string)$admin[$k]);
        if ($ts && ($now - $ts) <= 600) return true; // 10 minutes
      }
    }

    return false; // fallback
  }
}

// Admins 1x aus DB holen und anhand der Rollenliste anzeigen.
$dbAdmins = db_get_rows('admins', []);
$dbAdmins = is_array($dbAdmins) ? $dbAdmins : [];

$admins = [];
foreach ($dbAdmins as $a) {
  $adminId = (int)($a['id'] ?? 0);
  $uname   = trim((string)($a['username'] ?? ''));

  if ($adminId <= 0 || $uname === '') continue;

  // Nur Admins anzeigen, die für das Support Team gemappt sind.
  if (!isset($adminRoleOverrides[$adminId])) continue;

  $rid = (int)$adminRoleOverrides[$adminId];
  if (!isset($roleUi[$rid])) continue;

  $a['role_id'] = $rid;
  if (empty($a['icon'])) {
    $a['icon'] = 'default.png';
  }

  $admins[] = $a;
}

// Duplikate entfernen, falls die DB unerwartet doppelte Einträge liefert.
$seen = [];
$admins = array_values(array_filter($admins, function($a) use (&$seen) {
  $id = (int)($a['id'] ?? 0);
  if ($id <= 0 || isset($seen[$id])) return false;
  $seen[$id] = true;
  return true;
}));

// 4) Online/Offline: last activity aus admin_session_logs/admin_sessions ziehen (falls vorhanden)
// Wichtig: admin_sessions.created_at ist oft nur der Login-Zeitpunkt und wird danach nicht mehr aktualisiert.
// Deshalb priorisieren wir admin_session_logs.MAX(created_at) pro admin_id.
try {
  global $db;
  if (isset($db) && $db) {
    $adminIds = array_values(array_filter(array_map(function($a){
      return isset($a['id']) ? (int)$a['id'] : 0;
    }, $admins), fn($id) => $id > 0));

    if (!empty($adminIds)) {
      $placeholders = implode(',', array_fill(0, count($adminIds), '?'));

      // 1) logs: letzte Aktion (Online-Berechnung in SQL, um TZ-Probleme zu vermeiden)
      // 10 Minuten Fenster: auch wenn jemand "idle" ist, bleibt er online.
      $rows = $db->run(
        "SELECT admin_id,
                MAX(created_at) AS last_seen,
                (MAX(created_at) >= (NOW() - INTERVAL 10 MINUTE)) AS is_online
           FROM admin_session_logs
          WHERE admin_id IN ($placeholders)
          GROUP BY admin_id",
        ...$adminIds
      );
      $lastSeenByAdmin = [];
      $onlineByAdmin   = [];
      foreach ((array)$rows as $r) {
        $aid = (int)($r['admin_id'] ?? 0);
        $ls  = $r['last_seen'] ?? null;
        if ($aid > 0 && !ov_is_blank($ls)) {
          $lastSeenByAdmin[$aid] = $ls;
          $onlineByAdmin[$aid]   = ((int)($r['is_online'] ?? 0) === 1);
        }
      }

      // 2) sessions fallback: letzter Login (10 Minuten Fenster)
      $rows2 = $db->run(
        "SELECT admin_id,
                MAX(created_at) AS last_login,
                (MAX(created_at) >= (NOW() - INTERVAL 10 MINUTE)) AS is_online
           FROM admin_sessions
          WHERE admin_id IN ($placeholders)
          GROUP BY admin_id",
        ...$adminIds
      );
      $lastLoginByAdmin = [];
      $onlineLoginByAdmin = [];
      foreach ((array)$rows2 as $r) {
        $aid = (int)($r['admin_id'] ?? 0);
        $ls  = $r['last_login'] ?? null;
        if ($aid > 0 && !ov_is_blank($ls)) {
          $lastLoginByAdmin[$aid] = $ls;
          $onlineLoginByAdmin[$aid] = ((int)($r['is_online'] ?? 0) === 1);
        }
      }

      // In admins eintragen: last_seen bevorzugt logs, sonst last_login
      foreach ($admins as &$a) {
        $aid = (int)($a['id'] ?? 0);
        if ($aid <= 0) continue;
        if (isset($lastSeenByAdmin[$aid])) {
          $a['last_seen'] = $lastSeenByAdmin[$aid];
          $a['is_online'] = $onlineByAdmin[$aid] ?? false;
        } elseif (isset($lastLoginByAdmin[$aid])) {
          $a['last_seen'] = $lastLoginByAdmin[$aid];
          $a['is_online'] = $onlineLoginByAdmin[$aid] ?? false;
        }
      }
      unset($a);
    }
  }
} catch (Throwable $e) {
  // ignore; UI will fall back to offline
}


// 5) Sort admins by role first, then online status, then name.
$rolePriority = [2 => 0, 3 => 1, 5 => 2, 6 => 3, 4 => 4, 1 => 5];
usort($admins, function($a, $b) use ($rolePriority) {
  $ar = (int)($a['role_id'] ?? 999);
  $br = (int)($b['role_id'] ?? 999);
  $ap = $rolePriority[$ar] ?? 99;
  $bp = $rolePriority[$br] ?? 99;
  if ($ap !== $bp) return $ap <=> $bp;

  $ao = ov_admin_is_online($a) ? 1 : 0;
  $bo = ov_admin_is_online($b) ? 1 : 0;
  if ($ao !== $bo) return $bo <=> $ao;

  $an = mb_strtolower(trim((string)($a['username'] ?? '')));
  $bn = mb_strtolower(trim((string)($b['username'] ?? '')));
  return $an <=> $bn;
});

$adminsOnlineCount = count(array_filter($admins, fn($a) => ov_admin_is_online($a)));


// Admins whose personal coverage block is active right now.
$shiftAdmins = [];
try {
  global $db;

  if (isset($db) && $db) {
    if (function_exists('lb_support_shift_ensure_tables')) {
      lb_support_shift_ensure_tables();
    }

    $shiftNow = time();
    $currentShiftIds = [];
    $currentShiftRows = [];

    $parentRows = $db->run(
      "SELECT s.id, s.title, s.status, s.started_at, s.shift_date, s.start_time, s.end_time
         FROM support_shifts s
        WHERE s.status IN ('assigned','active','paused')
        ORDER BY s.shift_date ASC, s.start_time ASC, s.id ASC"
    ) ?: [];

    foreach ($parentRows as $parentRow) {
      $date = trim((string)($parentRow['shift_date'] ?? ''));
      $startRaw = trim((string)($parentRow['start_time'] ?? ''));
      $endRaw = trim((string)($parentRow['end_time'] ?? ''));
      if ($date === '' || $startRaw === '' || $endRaw === '') continue;

      $startTs = strtotime($date . ' ' . $startRaw);
      $endTs = strtotime($date . ' ' . $endRaw);
      if (!$startTs || !$endTs) continue;
      if ($endTs <= $startTs) $endTs += 86400;

      if ($shiftNow >= $startTs && $shiftNow < $endTs) {
        $shiftId = (int)($parentRow['id'] ?? 0);
        if ($shiftId > 0) {
          $currentShiftIds[] = $shiftId;
          $currentShiftRows[$shiftId] = $parentRow;
        }
      }
    }

    if (!empty($currentShiftIds)) {
      $placeholders = implode(',', array_fill(0, count($currentShiftIds), '?'));
      $seenShiftAdmins = [];

      $participantRows = $db->run(
        "SELECT p.shift_id, p.admin_id, p.status AS participant_status,
                p.planned_start_time, p.planned_end_time,
                a.username, a.icon
           FROM support_shift_participants p
           LEFT JOIN admins a ON a.id = p.admin_id
          WHERE p.shift_id IN ($placeholders)
            AND p.status IN ('assigned','active','paused')
            AND p.ended_at IS NULL
          ORDER BY p.shift_id ASC, p.id ASC",
        ...$currentShiftIds
      ) ?: [];
      $hasModernShiftParticipants = !empty($participantRows);

      foreach ($participantRows as $participantRow) {
        $adminId = (int)($participantRow['admin_id'] ?? 0);
        if ($adminId <= 0 || isset($seenShiftAdmins[$adminId])) continue;

        $shiftId = (int)($participantRow['shift_id'] ?? 0);
        $parent = $currentShiftRows[$shiftId] ?? [];
        $coverageDate = (string)($parent['shift_date'] ?? '');
        $coverageStartRaw = (string)(($participantRow['planned_start_time'] ?? '') ?: ($parent['start_time'] ?? ''));
        $coverageEndRaw = (string)(($participantRow['planned_end_time'] ?? '') ?: ($parent['end_time'] ?? ''));
        $coverageStartTs = strtotime($coverageDate . ' ' . $coverageStartRaw);
        $coverageEndTs = strtotime($coverageDate . ' ' . $coverageEndRaw);
        if (!$coverageStartTs || !$coverageEndTs) continue;
        if ($coverageEndTs <= $coverageStartTs) $coverageEndTs += 86400;
        if ($shiftNow < $coverageStartTs || $shiftNow >= $coverageEndTs) continue;

        $seenShiftAdmins[$adminId] = true;
        $shiftAdmins[] = $participantRow;
      }

      if (empty($shiftAdmins) && !$hasModernShiftParticipants) {
        try {
          $legacyRows = $db->run(
          "SELECT p.shift_id, p.admin_id, p.status AS participant_status,
                  a.username, a.icon
             FROM support_shift_admins p
             LEFT JOIN admins a ON a.id = p.admin_id
            WHERE p.shift_id IN ($placeholders)
              AND p.status IN ('active','paused')
              AND p.ended_at IS NULL
            ORDER BY p.shift_id ASC, p.id ASC",
          ...$currentShiftIds
        ) ?: [];

        foreach ($legacyRows as $legacyRow) {
          $adminId = (int)($legacyRow['admin_id'] ?? 0);
          if ($adminId <= 0 || isset($seenShiftAdmins[$adminId])) continue;
          $seenShiftAdmins[$adminId] = true;
          $shiftAdmins[] = $legacyRow;
        }
        } catch (Throwable $legacyError) {
        }
      }

      if (empty($shiftAdmins) && !$hasModernShiftParticipants) {
        $fallbackRows = $db->run(
          "SELECT s.id AS shift_id, s.assigned_admin_id AS admin_id,
                  s.status AS participant_status,
                  a.username, a.icon
             FROM support_shifts s
             LEFT JOIN admins a ON a.id = s.assigned_admin_id
            WHERE s.id IN ($placeholders)
              AND s.assigned_admin_id IS NOT NULL
            ORDER BY s.id ASC",
          ...$currentShiftIds
        ) ?: [];

        foreach ($fallbackRows as $fallbackRow) {
          $adminId = (int)($fallbackRow['admin_id'] ?? 0);
          if ($adminId <= 0 || isset($seenShiftAdmins[$adminId])) continue;
          $seenShiftAdmins[$adminId] = true;
          $shiftAdmins[] = $fallbackRow;
        }
      }
    }
  }
} catch (Throwable $shiftError) {
  $shiftAdmins = [];
}

// =============================
// VERIFIED + Personal Details (LIVE aus DB)
// =============================
$boosterId = (int)(defined('BOOSTER_ID') ? BOOSTER_ID : (BOOSTER_DATA['id'] ?? 0));

// boosters row live (verhindert Session-Stale)
$boosterRows = db_get_rows('boosters', ['id' => $boosterId]);
$boosterRows = is_array($boosterRows) ? $boosterRows : [];
$boosterRow  = $boosterRows[0] ?? [];

// verified = finale Admin-Freigabe
// docs_complete = Booster hat alle Pflichtdaten und Dokumente hinterlegt
// Für das Booster-Dashboard reicht docs_complete, damit das Verify Modal nicht weiter erscheint.
$isAdminVerified = ((int)($boosterRow['verified'] ?? 0) === 1);
$docsComplete    = ((int)($boosterRow['docs_complete'] ?? 0) === 1);
$isVerified      = ($isAdminVerified || $docsComplete);

// 1 Zeile pro Booster
$pdRows = db_get_rows('booster_personal_details', ['booster_id' => $boosterId]);
$pdRows = is_array($pdRows) ? $pdRows : [];
$pd     = $pdRows[0] ?? [];

// =============================
// Booster Payments KPIs (Lifetime / Fines / Tips)
// =============================
// amounts are stored in cents. We display everything in EUR, converting USD via get_exchange_rate().
$lifetime_eur = 0;
$lifetime_usd = 0;
$tips_eur = 0;
$tips_usd = 0;
$fines_eur = 0;
$fines_usd = 0;

try {
  global $db;

  // Lifetime earning = sum of all positive credits EXCEPT manual client tips (client_tip).
  $rows = $db->run(
    "SELECT\n        COALESCE(SUM(CASE WHEN currency='EUR' AND amount > 0 THEN amount ELSE 0 END), 0) AS eur_total,\n        COALESCE(SUM(CASE WHEN currency='USD' AND amount > 0 THEN amount ELSE 0 END), 0) AS usd_total\n     FROM booster_payments\n     WHERE booster_id = ? AND type <> 'client_tip'",
    $boosterId
  );
  $lifetime_eur = (int)($rows[0]['eur_total'] ?? 0);
  $lifetime_usd = (int)($rows[0]['usd_total'] ?? 0);

  // Received tips
  $rows = $db->run(
    "SELECT\n        COALESCE(SUM(CASE WHEN currency='EUR' THEN amount ELSE 0 END), 0) AS eur_total,\n        COALESCE(SUM(CASE WHEN currency='USD' THEN amount ELSE 0 END), 0) AS usd_total\n     FROM booster_payments\n     WHERE booster_id = ? AND type IN ('tip','client_tip') AND amount > 0",
    $boosterId
  );
  $tips_eur = (int)($rows[0]['eur_total'] ?? 0);
  $tips_usd = (int)($rows[0]['usd_total'] ?? 0);

  // Received fines (stored as negative amounts; we show absolute value)
  // Covers legacy/variants: type='fine', 'progress_payment_fine', or anything containing 'fine'.
  $rows = $db->run(
    "SELECT\n        COALESCE(SUM(CASE WHEN currency='EUR' THEN ABS(amount) ELSE 0 END), 0) AS eur_total,\n        COALESCE(SUM(CASE WHEN currency='USD' THEN ABS(amount) ELSE 0 END), 0) AS usd_total\n     FROM booster_payments\n     WHERE booster_id = ?\n       AND (type = 'fine' OR type = 'progress_payment_fine' OR type LIKE '%fine%')\n       AND amount < 0",
    $boosterId
  );
  $fines_eur = (int)($rows[0]['eur_total'] ?? 0);
  $fines_usd = (int)($rows[0]['usd_total'] ?? 0);
} catch (Throwable $e) {
  // fail silently; dashboard should still render
}

$xr = function_exists('get_exchange_rate') ? (float)get_exchange_rate() : 1.0;
if ($xr <= 0) { $xr = 1.0; }

$lifetime_total_eur = (int) round($lifetime_eur + ($lifetime_usd / $xr));
$tips_total_eur     = (int) round($tips_eur + ($tips_usd / $xr));
// Safety: tips should never be negative in the dashboard (tips reversals are treated separately)
if ($tips_total_eur < 0) { $tips_total_eur = 0; }
$fines_total_eur    = (int) round($fines_eur + ($fines_usd / $xr));
?>

<?php
// Banner + avatar variables
$bannerUrl = trim((string)(BOOSTER_DATA['cover'] ?? BOOSTER_DATA['banner_url'] ?? ''));
$bannerPosition = trim((string)(BOOSTER_DATA['cover_position'] ?? BOOSTER_DATA['banner_position'] ?? '50% 50%'));
if ($bannerPosition === '') $bannerPosition = '50% 50%';
$avatarSrc    = trim((string)(BOOSTER_DATA['icon'] ?? ''));
$avatarLetter = strtoupper(substr((string)(BOOSTER_DATA['username'] ?? 'B'), 0, 1));
$nexus_balance_cents = (int)(BOOSTER_DATA['balance'] ?? 0);
$nexus_frozen_cents  = function_exists('booster_insurance_frozen_cents')
  ? (int) booster_insurance_frozen_cents(BOOSTER_DATA)
  : (isset(BOOSTER_DATA['insurance_required_amount']) ? max(0, (int) BOOSTER_DATA['insurance_required_amount']) : 0);
$nexus_available_cents = function_exists('booster_available_for_payout_cents')
  ? (int) booster_available_for_payout_cents(BOOSTER_DATA)
  : max($nexus_balance_cents - $nexus_frozen_cents, 0);
$nexus_discord_id    = trim((string)(BOOSTER_DATA['discord_id'] ?? ''));
?>

<div class="card sdash-hero-card mb-3" style="overflow:hidden;border-radius:22px;">
  <div class="sdash-hero-banner" id="spHeroBanner" style="<?= $bannerUrl ? 'background-image:url('.htmlspecialchars($bannerUrl).');background-position:'.htmlspecialchars($bannerPosition).';background-size:cover;' : '' ?>">
    <div class="sdash-hero-banner-glow"></div>
    <div class="sdash-hero-banner-noise"></div>
    <button class="sdash-banner-edit-btn" data-bs-toggle="modal" data-bs-target="#upload-cover-modal">
      <i class="fa-duotone fa-image"></i> Change Banner
    </button>
  </div>
  <div class="sdash-hero-body px-4 py-3">
    <div class="d-flex align-items-center gap-3 flex-wrap">
      <div class="sdash-avatar-wrap" data-bs-toggle="modal" data-bs-target="#upload-icon-modal">
        <div class="sdash-avatar">
          <?php if (!empty(BOOSTER_DATA['icon'])): ?>
            <img src="<?= htmlspecialchars((string)BOOSTER_DATA['icon']) ?>" alt="avatar">
          <?php else: ?>
            <?= strtoupper(substr((string)BOOSTER_DATA['username'], 0, 1)) ?>
          <?php endif; ?>
        </div>
        <div class="sdash-avatar-pen"><i class="fa-solid fa-pen"></i></div>
      </div>
      <div class="flex-grow-1 min-width-0">
        <div class="sdash-hero-name">
          <?= htmlspecialchars((string)BOOSTER_DATA['username']) ?>
          <?php if ($isVerified): ?>
            <span class="sdash-chip sdash-chip--accent ms-1" style="font-size:.72rem;padding:3px 9px;">
              <i class="fa-duotone fa-badge-check"></i> Verified
            </span>
          <?php else: ?>
            <span class="sdash-chip sdash-chip--warning ms-1" style="font-size:.72rem;padding:3px 9px;">
              <i class="fa-duotone fa-clock"></i> Unverified
            </span>
          <?php endif; ?>
        </div>
        <div class="sdash-hero-sub mt-1">Booster Account</div>
      </div>

      <?php
        $heroOnlineAdmins = $shiftAdmins;
        $heroOnlinePreview = array_slice($heroOnlineAdmins, 0, 5);
        $heroOnlineNames = array_map(fn($a) => (string)($a['username'] ?? 'Admin'), $heroOnlineAdmins);
      ?>
      <div class="sdash-admin-preview">
        <div class="sdash-admin-preview-head">
          <span class="sdash-live-dot"></span>
          <strong><?= (int)count($heroOnlineAdmins) ?> support on shift</strong>
        </div>
        <div class="sdash-admin-preview-body">
          <div class="sdash-admin-stack">
            <?php foreach ($heroOnlinePreview as $ha): ?>
              <img src="<?= htmlspecialchars(admin_icon_url($ha['icon'] ?? '')) ?>" alt="<?= htmlspecialchars((string)($ha['username'] ?? 'Admin')) ?>">
            <?php endforeach; ?>
            <?php if (empty($heroOnlinePreview)): ?>
              <span class="sdash-admin-empty"><i class="fa-duotone fa-headset"></i></span>
            <?php endif; ?>
          </div>
          <div class="sdash-admin-preview-text">
            <b>On shift now</b>
            <small><?= !empty($heroOnlineNames) ? htmlspecialchars(implode(', ', $heroOnlineNames)) : 'No admin on shift' ?></small>
          </div>
        </div>
      </div>

      <div class="d-flex gap-2 flex-wrap ms-auto">
        <div class="sdash-stat-badge">
          <div class="sdash-stat-val"><?= util_format_price_display($nexus_available_cents) ?>€</div>
          <div class="sdash-stat-lbl">Available</div>
        </div>
        <div class="sdash-stat-badge">
          <div class="sdash-stat-val"><?= (int)($data['orders_completed'] ?? 0) ?></div>
          <div class="sdash-stat-lbl">Completed</div>
        </div>
        <div class="sdash-stat-badge">
          <div class="sdash-stat-val"><?= (int)($data['orders_in_progress'] ?? 0) ?></div>
          <div class="sdash-stat-lbl">Active</div>
        </div>
      </div>
    </div>
  </div>
</div>

<div class="seller-overview-v2">
  <div class="container-fluid" style="padding-left:0!important;padding-right:0!important;">

    <?php if (!$isVerified): ?>
    <div class="d-flex align-items-center gap-3 mb-3 px-1 py-2 rounded-3" style="background:rgba(251,191,36,.07);border:1px solid rgba(251,191,36,.2);padding:12px 16px!important;">
      <i class="fa-duotone fa-triangle-exclamation" style="color:#fbbf24;font-size:1.1rem;flex-shrink:0;"></i>
      <div style="font-size:.86rem;color:rgba(255,255,255,.8);">Account not verified. Complete verification to start receiving orders.</div>
      <button class="btn btn-warning btn-sm ms-auto" data-bs-toggle="modal" data-bs-target="#verify-now-modal">
        <i class="fa-duotone fa-id-card me-1"></i>Verify now
      </button>
    </div>
    <?php endif; ?>

    <!-- Clean Dashboard Overview -->
    <div class="dash-modern-grid mb-4">
      <div class="dash-stat dash-stat-main">
        <span>Available Balance</span>
        <strong><?= util_format_price_display($nexus_available_cents) ?> €</strong>
        <small>Ready for payout or Nexus top up</small>
      </div>
      <div class="dash-stat">
        <span>Lifetime Earned</span>
        <strong><?= util_format_price_display($lifetime_total_eur) ?> €</strong>
        <small>All completed orders</small>
      </div>
      <div class="dash-stat">
        <span>Tips Received</span>
        <strong><?= util_format_price_display($tips_total_eur) ?> €</strong>
        <small>Client tips total</small>
      </div>
      <div class="dash-stat dash-stat-warning">
        <span>Total Fines</span>
        <strong><?= util_format_price_display($fines_total_eur) ?> €</strong>
        <small>Lifetime deductions</small>
      </div>
    </div>

    <div class="dash-main-grid">
      <div class="card dash-card dash-activity-card">
        <div class="card-body p-4">
          <div class="dash-card-head">
            <div>
              <h3>Recent activity</h3>
              <p>Your latest payments and balance changes</p>
            </div>
            <a href="<?= htmlspecialchars(BSTR_URL . '/payments') ?>">View all</a>
          </div>
          <?php
            try {
              global $db;
              $recentPays = $db->run(
                "SELECT type, amount, currency, note, created_at FROM booster_payments WHERE booster_id = ? ORDER BY id DESC LIMIT 5",
                $boosterId
              );
            } catch (Throwable $e) { $recentPays = []; }
          ?>
          <?php if (!empty($recentPays)): ?>
            <div class="dash-activity-list mt-3">
              <?php foreach ($recentPays as $p):
                $amt    = (int)($p['amount'] ?? 0);
                $isPos  = $amt >= 0;
                $amtStr = ($isPos ? '+' : '−') . util_format_price_display(abs($amt)) . ' ' . strtoupper($p['currency'] ?? 'EUR');
                $type   = ucwords(str_replace('_', ' ', (string)($p['type'] ?? 'payment')));
                $note   = htmlspecialchars(trim((string)($p['note'] ?? '')));
                $date   = !empty($p['created_at']) ? date('d.m.Y · H:i', strtotime($p['created_at'])) : '—';
              ?>
                <div class="dash-activity-item <?= $isPos ? 'is-positive' : 'is-negative' ?>">
                  <div class="dash-activity-dot"></div>
                  <div class="dash-activity-text">
                    <strong><?= htmlspecialchars($type) ?></strong>
                    <span><?= $date ?><?= $note !== '' ? ' · ' . $note : '' ?></span>
                  </div>
                  <b><?= $amtStr ?></b>
                </div>
              <?php endforeach; ?>
            </div>
          <?php else: ?>
            <div class="sv2-empty-state mt-4">
              <i class="fa-duotone fa-inbox"></i>
              <div class="sv2-empty-title">No activity yet</div>
              <div class="sv2-empty-text">Your first order payment will appear here.</div>
            </div>
          <?php endif; ?>
        </div>
      </div>

      <div class="dash-side-stack">
        <div class="card dash-card">
          <div class="card-body p-4">
            <div class="dash-card-head mb-3">
              <div>
                <h3>Quick actions</h3>
                <p>Common booster tools</p>
              </div>
            </div>
            <div class="dash-action-grid">
              <a href="<?= htmlspecialchars(BSTR_URL . '/orders') ?>"><i class="fa-duotone fa-list-check"></i><strong>My Orders</strong><span>Browse and claim</span></a>
              <a href="<?= htmlspecialchars(BSTR_URL . '/payout-requests') ?>"><i class="fa-duotone fa-money-check-dollar"></i><strong>Payout</strong><span>Withdraw balance</span></a>
              <a href="<?= htmlspecialchars(BSTR_URL . '/payments') ?>"><i class="fa-duotone fa-receipt"></i><strong>Payments</strong><span>Full history</span></a>
              <a href="<?= htmlspecialchars(BSTR_URL . '/profile') ?>"><i class="fa-duotone fa-user-gear"></i><strong>Profile</strong><span>Settings</span></a>
            </div>
          </div>
        </div>

        <div class="card dash-card">
          <div class="card-body p-4">
            <div class="dash-card-head mb-3">
              <div>
                <h3>Orders overview</h3>
                <p>Current order status</p>
              </div>
              <a href="<?= htmlspecialchars(BSTR_URL . '/orders') ?>">Browse</a>
            </div>
            <div class="dash-orders-row"><span>In Progress</span><strong><?= (int)($data['orders_in_progress'] ?? 0) ?></strong></div>
            <div class="dash-orders-row"><span>Completed</span><strong><?= (int)($data['orders_completed'] ?? 0) ?></strong></div>
            <div class="dash-orders-row"><span>Total</span><strong><?= (int)($data['orders_total'] ?? 0) ?></strong></div>
          </div>
        </div>
      </div>
    </div>

    <div class="card dash-card dash-nexus-card sv2-section-gap" id="nexus-section">
      <div class="card-body p-4">
        <div class="dash-card-head dash-nexus-head mb-4">
          <div>
            <h3><i class="fa-duotone fa-bolt me-2" style="color:#a78bfa;"></i>Nexus Balance Top Up</h3>
            <p>Add custom Nexus balance with your Booster Balance, then buy your membership inside Nexus.</p>
          </div>
          <div class="dash-balance-pill">Available: <?= htmlspecialchars(util_format_price_display($nexus_available_cents)) ?>€</div>
        </div>

        <?php if (empty($nexus_discord_id)): ?>
        <div class="sv2-feed-item mb-3" style="border-color:rgba(88,101,242,.22);background:rgba(88,101,242,.06);">
          <i class="fa-brands fa-discord" style="color:#7289da;font-size:1rem;flex-shrink:0;margin-top:2px;"></i>
          <div class="sv2-feed-body">Link your Discord in <a href="<?= htmlspecialchars(BSTR_URL . '/profile') ?>" style="color:#a78bfa;">My Profile</a> to enable Nexus.</div>
        </div>
        <?php endif; ?>

        <div class="dash-nexus-split">
          <div class="dash-nexus-panel dash-topup-panel">
            <div class="dash-panel-kicker">Custom Top Up</div>
            <div class="mb-3">
              <label class="form-label nexus-field-label" for="nexus-amount-input">Amount to Top Up</label>
              <div class="nexus-amount-wrap">
                <span class="nexus-currency">€</span>
                <input
                  type="number"
                  class="form-control nexus-amount-input"
                  id="nexus-amount-input"
                  min="1"
                  max="<?= htmlspecialchars(number_format($nexus_available_cents / 100, 2, '.', '')) ?>"
                  step="0.01"
                  inputmode="decimal"
                  placeholder="Enter amount">
              </div>
            </div>

            <div class="nexus-quick-buttons mb-3">
              <button type="button" class="btn btn-sm btn-outline-primary" onclick="setNexusAmount(10)">€10</button>
              <button type="button" class="btn btn-sm btn-outline-primary" onclick="setNexusAmount(25)">€25</button>
              <button type="button" class="btn btn-sm btn-outline-primary" onclick="setNexusAmount(50)">€50</button>
              <button type="button" class="btn btn-sm btn-outline-primary" onclick="setNexusAmount(100)">€100</button>
              <button type="button" class="btn btn-sm btn-outline-primary" onclick="setNexusMaxAmount()">Max</button>
            </div>

            <div class="dash-receive-row mb-3">
              <span>You will receive</span>
              <strong id="nexus-preview">€0.00 Nexus Balance</strong>
            </div>

            <button class="btn btn-primary w-100 nexus-buy-btn" type="button" onclick="nexusOpenTopupModal()" <?= empty($nexus_discord_id) ? 'disabled' : '' ?>>
              <i class="fa-duotone fa-bolt me-2"></i>Top Up Nexus Balance
            </button>
          </div>

          <div class="dash-nexus-panel dash-tutorial-panel">
            <div class="dash-tutorial-title">
              <div><i class="fa-duotone fa-book-open"></i></div>
              <div>
                <strong>How to use Nexus</strong>
                <span>Simple steps after your top up</span>
              </div>
            </div>
            <a class="dash-download-btn" href="https://hex-nexus-app-production.up.railway.app/" target="_blank" rel="noopener">
              <i class="fa-duotone fa-download me-1"></i>Download App
            </a>
            <div class="dash-tutorial-steps">
              <div><b>1</b><span>Download Nexus and connect Discord.</span></div>
              <div><b>2</b><span>Add balance here with your Booster Balance.</span></div>
              <div><b>3</b><span>Open Nexus and choose your membership.</span></div>
              <div><b>4</b><span>Pay with <strong>Balance</strong> and use Account Lender.</span></div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="card dash-card dash-support-card sv2-section-gap">
      <div class="card-body p-4">
        <div class="dash-card-head mb-4">
          <div>
            <h3>Support team</h3>
            <p><span class="sv2-online-pulse"></span><?= (int)$adminsOnlineCount ?> member<?= $adminsOnlineCount === 1 ? '' : 's' ?> online right now</p>
          </div>
          <a href="https://discord.com/channels/926928301807771708/1207383976239702087" target="_blank" rel="noopener">Open ticket</a>
        </div>

        <?php
          $supportOnline = [];
          $supportOffline = [];
          foreach ($admins as $supportAdmin) {
            if (ov_admin_is_online($supportAdmin)) {
              $supportOnline[] = $supportAdmin;
            } else {
              $supportOffline[] = $supportAdmin;
            }
          }
        ?>

        <div class="dash-support-section">
          <div class="dash-support-label"><span>Online now</span><b><?= count($supportOnline) ?></b></div>
          <div class="dash-support-grid dash-support-grid-online">
            <?php foreach ($supportOnline as $a):
              $ui       = $roleUi[(int)($a['role_id'] ?? 0)] ?? ['label' => 'Support', 'nameClass' => ''];
              $name     = htmlspecialchars((string)($a['username'] ?? 'Admin'));
              $icon     = admin_icon_url($a['icon'] ?? '');
            ?>
              <div class="dash-support-member is-online">
                <img src="<?= htmlspecialchars($icon) ?>" alt="<?= $name ?>">
                <div>
                  <strong class="<?= htmlspecialchars($ui['nameClass']) ?>"><?= $name ?></strong>
                  <span><?= htmlspecialchars($ui['label']) ?></span>
                </div>
                <a href="https://discord.com/channels/926928301807771708/1207383976239702087" target="_blank" rel="noopener"><i class="fa-brands fa-discord"></i></a>
              </div>
            <?php endforeach; ?>
            <?php if (empty($supportOnline)): ?>
              <div class="sv2-empty-inline">No support members are online right now.</div>
            <?php endif; ?>
          </div>
        </div>

        <div class="dash-support-section mt-4">
          <div class="dash-support-label dash-support-label-muted"><span>Offline</span><b><?= count($supportOffline) ?></b></div>
          <div class="dash-support-grid dash-support-grid-offline">
            <?php foreach ($supportOffline as $a):
              $ui       = $roleUi[(int)($a['role_id'] ?? 0)] ?? ['label' => 'Support', 'nameClass' => ''];
              $name     = htmlspecialchars((string)($a['username'] ?? 'Admin'));
              $icon     = admin_icon_url($a['icon'] ?? '');
            ?>
              <div class="dash-support-member is-offline">
                <img src="<?= htmlspecialchars($icon) ?>" alt="<?= $name ?>">
                <div>
                  <strong class="<?= htmlspecialchars($ui['nameClass']) ?>"><?= $name ?></strong>
                  <span><?= htmlspecialchars($ui['label']) ?></span>
                </div>
                <button type="button" disabled><i class="fa-brands fa-discord"></i></button>
              </div>
            <?php endforeach; ?>
            <?php if (empty($supportOffline)): ?>
              <div class="sv2-empty-inline">Everyone is online.</div>
            <?php endif; ?>
          </div>
        </div>
      </div>
    </div>

  </div>
</div>

<!-- Seller-style CSS reused for hero banner -->
<style>
.sdash-hero-card { background:var(--bs-card-bg) !important; }
.sdash-hero-banner { min-height:160px; background-color:#111a44; background-image:radial-gradient(circle at 8% 0%,rgba(255,255,255,.10),transparent 22%),radial-gradient(circle at 92% 100%,rgba(56,189,248,.12),transparent 26%),linear-gradient(90deg,#111a44 0%,#312e81 48%,#0f172a 100%); background-size:cover; background-position:center; position:relative; overflow:hidden; }
.sdash-hero-banner-glow { position:absolute;inset:0;background:linear-gradient(90deg,rgba(129,140,248,.18),rgba(139,92,246,.12),rgba(14,165,233,.12));mix-blend-mode:screen;pointer-events:none; }
.sdash-hero-banner-noise { position:absolute;inset:0;background:linear-gradient(180deg,rgba(10,14,29,.0),rgba(10,14,29,.28) 100%);pointer-events:none; }
.sdash-banner-edit-btn { position:absolute;bottom:12px;right:14px;display:inline-flex;align-items:center;gap:.4rem;padding:.4rem .9rem;font-size:.8rem;font-weight:800;background:rgba(0,0,0,.45);border:1px solid rgba(255,255,255,.18);border-radius:8px;color:rgba(255,255,255,.85);cursor:pointer;backdrop-filter:blur(8px);transition:background .15s,border-color .15s,color .15s; }
.sdash-banner-edit-btn:hover { background:rgba(109,92,255,.35);border-color:rgba(109,92,255,.55);color:#fff; }
.sdash-hero-body { background:var(--bs-card-bg); }
.sdash-avatar-wrap { position:relative;cursor:pointer;display:inline-block;flex-shrink:0; }
.sdash-avatar { width:72px;height:72px;border-radius:50%;border:3px solid var(--bs-card-bg,#1e2028);background:linear-gradient(135deg,#2c3450,#22283a);display:flex;align-items:center;justify-content:center;font-size:1.7rem;font-weight:800;color:#60a5fa;overflow:hidden;box-shadow:0 8px 24px rgba(0,0,0,.30); }
.sdash-avatar img { width:100%;height:100%;object-fit:cover;border-radius:50%; }
.sdash-avatar-pen { position:absolute;bottom:0;right:0;width:22px;height:22px;border-radius:50%;background:#35383a;border:2px solid var(--bs-card-bg,#1e2028);display:flex;align-items:center;justify-content:center;color:#fff;font-size:.6rem;pointer-events:none; }
.sdash-hero-name { font-size:1.2rem;font-weight:950;color:rgba(255,255,255,.92); }
.sdash-hero-sub  { font-size:.82rem;color:rgba(255,255,255,.55); }
.sdash-stat-badge { display:inline-flex;flex-direction:column;align-items:center;background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.09);border-radius:12px;padding:.55rem .9rem;min-width:78px; }
.sdash-stat-val { font-size:1rem;font-weight:900;line-height:1.2;color:rgba(255,255,255,.92); }
.sdash-stat-lbl { font-size:.67rem;font-weight:700;color:rgba(255,255,255,.50);text-transform:uppercase;letter-spacing:.06em;margin-top:.2rem; }
.sdash-chip { display:inline-flex;align-items:center;gap:6px;padding:6px 10px;border-radius:999px;background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.10);color:rgba(255,255,255,.92);font-weight:900;font-size:.82rem; }
.sdash-chip--accent  { background:linear-gradient(135deg,rgba(109,92,255,.22),rgba(176,92,255,.14));border-color:rgba(109,92,255,.35); }
.sdash-chip--warning { background:rgba(251,191,36,.10);border-color:rgba(251,191,36,.28); }

.sdash-admin-preview { min-width:420px; max-width:560px; border-radius:16px; padding:12px 16px; background:rgba(255,255,255,.035); border:1px solid rgba(255,255,255,.10); box-shadow:none; }
.sdash-admin-preview-head { display:flex; align-items:center; gap:7px; color:#34d399; font-size:.66rem; font-weight:950; text-transform:uppercase; letter-spacing:.08em; margin-bottom:8px; }
.sdash-live-dot { width:7px; height:7px; border-radius:50%; background:#34d399; box-shadow:0 0 10px rgba(52,211,153,.75); }
.sdash-admin-preview-body { display:flex; align-items:center; gap:12px; min-width:0; }
.sdash-admin-stack { display:flex; align-items:center; flex-shrink:0; }
.sdash-admin-stack img { width:32px; height:32px; border-radius:50%; object-fit:cover; border:2px solid #24282b; margin-left:-7px; box-shadow:0 5px 14px rgba(0,0,0,.20); }
.sdash-admin-stack img:first-child { margin-left:0; }
.sdash-admin-empty { width:32px; height:32px; border-radius:50%; display:flex; align-items:center; justify-content:center; background:rgba(255,255,255,.06); color:rgba(255,255,255,.55); border:1px solid rgba(255,255,255,.10); }
.sdash-admin-preview-text { min-width:0; flex:1; }
.sdash-admin-preview-text b { display:block; color:rgba(255,255,255,.90); font-size:.84rem; line-height:1.15; white-space:nowrap; }
.sdash-admin-preview-text small { display:block; color:rgba(255,255,255,.58); font-size:.74rem; margin-top:3px; white-space:normal; line-height:1.25; }
@media(max-width:1199.98px){ .sdash-admin-preview { order:5; width:100%; max-width:none; min-width:0; } }

/* ══ Seller Overview V2 tokens ══ */
.seller-overview-v2 { --sv2-text:rgba(255,255,255,.94); --sv2-muted:rgba(255,255,255,.62); }
.seller-overview-v2 .card { background:var(--bs-card-bg) !important; border:var(--bs-card-border-width) solid var(--bs-card-border-color) !important; border-radius:22px !important; box-shadow:none !important; }
.seller-overview-v2 .card::before { display:none !important; content:none !important; }
.seller-overview-v2 .sv2-section-gap { margin-top:22px; }
.seller-overview-v2 .sv2-section-top { display:flex; align-items:flex-start; justify-content:space-between; gap:16px; }
.seller-overview-v2 .sv2-card-title  { color:var(--sv2-text); font-weight:900; font-size:.95rem; margin:0; }
.seller-overview-v2 .sv2-inline-link { color:rgba(255,255,255,.84); text-decoration:none; font-weight:800; }
.seller-overview-v2 .sv2-inline-link:hover { color:#fff; }
.seller-overview-v2 .sv2-muted-note  { color:var(--sv2-muted); font-size:.84rem; display:flex; align-items:center; gap:6px; }
.seller-overview-v2 .sv2-divider     { height:1px; background:rgba(255,255,255,.07); margin:18px 0; }

/* Redesigned Dashboard Layout */
.sv2-command-grid { display:grid; grid-template-columns:minmax(0,.95fr) minmax(520px,1.05fr); gap:18px; align-items:stretch; }
.sv2-bottom-grid { display:grid; grid-template-columns:minmax(0,1.1fr) minmax(360px,.9fr); gap:18px; align-items:start; }
.sv2-command-card { position:relative; overflow:hidden; }
.sv2-command-card::after { content:''; position:absolute; inset:auto -80px -90px auto; width:220px; height:220px; border-radius:50%; background:rgba(109,92,255,.08); pointer-events:none; }
.sv2-activity-card { background:linear-gradient(145deg,rgba(255,255,255,.035),rgba(255,255,255,.015)) !important; }
.sv2-nexus-card { background:radial-gradient(circle at 100% 0%,rgba(109,92,255,.20),transparent 34%),rgba(255,255,255,.02) !important; border-color:rgba(109,92,255,.18) !important; }
.sv2-soft-text { color:rgba(255,255,255,.45); font-size:.8rem; font-weight:700; }
.sv2-pill-link { display:inline-flex; align-items:center; justify-content:center; padding:7px 12px; border-radius:999px; background:rgba(255,255,255,.05); border:1px solid rgba(255,255,255,.09); color:rgba(255,255,255,.82); font-size:.78rem; font-weight:900; text-decoration:none; white-space:nowrap; }
.sv2-pill-link:hover { color:#fff; border-color:rgba(109,92,255,.35); background:rgba(109,92,255,.12); }
.sv2-mini-balance { color:#c4b5fd; background:rgba(109,92,255,.12); border:1px solid rgba(109,92,255,.22); border-radius:999px; padding:6px 10px; font-size:.72rem; font-weight:950; }
.sv2-timeline { display:grid; gap:10px; }
.sv2-timeline-item { display:flex; align-items:center; gap:12px; padding:13px 14px; border-radius:16px; background:rgba(0,0,0,.12); border:1px solid rgba(255,255,255,.07); }
.sv2-timeline-item.is-plus { border-left:3px solid rgba(74,222,128,.65); }
.sv2-timeline-item.is-minus { border-left:3px solid rgba(251,113,133,.65); }
.sv2-timeline-icon { width:34px; height:34px; border-radius:12px; display:flex; align-items:center; justify-content:center; flex-shrink:0; background:rgba(255,255,255,.05); color:rgba(255,255,255,.82); }
.sv2-timeline-body { flex:1; min-width:0; }
.sv2-action-hub { display:grid; grid-template-columns:repeat(4,minmax(0,1fr)); gap:14px; align-items:stretch; }
.sv2-hub-card { display:flex; align-items:center; gap:13px; min-height:86px; padding:16px; border-radius:18px; background:rgba(255,255,255,.025); border:1px solid rgba(255,255,255,.08); text-decoration:none; color:inherit; transition:transform .18s ease,border-color .18s ease,background .18s ease; }
.sv2-hub-card:hover { transform:translateY(-2px); background:rgba(109,92,255,.07); border-color:rgba(109,92,255,.28); }
.sv2-hub-card.is-primary { background:linear-gradient(135deg,rgba(109,92,255,.18),rgba(255,255,255,.025)); border-color:rgba(109,92,255,.28); }
.sv2-hub-icon { width:42px; height:42px; border-radius:14px; flex-shrink:0; display:flex; align-items:center; justify-content:center; color:#fff; background:linear-gradient(135deg,rgba(109,92,255,.36),rgba(176,92,255,.18)); border:1px solid rgba(109,92,255,.25); }
.sv2-hub-card strong { display:block; color:rgba(255,255,255,.92); font-size:.85rem; font-weight:950; }
.sv2-hub-card span { display:block; color:rgba(255,255,255,.48); font-size:.76rem; margin-top:3px; }
.sv2-order-mini { grid-column:1 / -1; padding:16px; border-radius:18px; background:linear-gradient(135deg,rgba(255,255,255,.04),rgba(255,255,255,.018)); border:1px solid rgba(255,255,255,.08); }
.sv2-order-mini-head { display:flex; align-items:center; justify-content:space-between; margin-bottom:12px; }
.sv2-order-mini-head strong { color:rgba(255,255,255,.9); font-size:.86rem; }
.sv2-order-mini-head a { color:#c4b5fd; font-weight:900; text-decoration:none; font-size:.78rem; }
.sv2-order-mini-grid { display:grid; grid-template-columns:repeat(3,1fr); gap:10px; }
.sv2-order-mini-grid span { display:flex; justify-content:space-between; align-items:center; padding:12px 14px; border-radius:14px; background:rgba(0,0,0,.12); border:1px solid rgba(255,255,255,.06); color:rgba(255,255,255,.55); font-size:.8rem; font-weight:800; }
.sv2-order-mini-grid b { color:rgba(255,255,255,.94); }
.sv2-support-groups { display:grid; grid-template-columns:repeat(2,minmax(0,1fr)); gap:14px; }
.sv2-support-group-card { padding:14px; border-radius:18px; background:rgba(0,0,0,.12); border:1px solid rgba(255,255,255,.07); }
.sv2-support-group-title { color:rgba(255,255,255,.42); text-transform:uppercase; letter-spacing:.08em; font-size:.68rem; font-weight:950; margin-bottom:10px; }
.sv2-support-mini-list { display:grid; gap:9px; }
.sv2-support-mini { display:flex; align-items:center; gap:10px; padding:10px; border-radius:14px; background:rgba(255,255,255,.025); border:1px solid rgba(255,255,255,.06); }
.sv2-support-mini.is-online { border-color:rgba(74,222,128,.16); background:rgba(74,222,128,.035); }
.sv2-support-mini.is-offline { opacity:.75; }
.sv2-support-mini img { width:36px; height:36px; border-radius:50%; object-fit:cover; border:1px solid rgba(255,255,255,.12); flex-shrink:0; }
.sv2-support-mini strong { display:block; font-size:.82rem; font-weight:950; line-height:1.2; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
.sv2-support-mini span { display:block; color:rgba(255,255,255,.45); font-size:.74rem; margin-top:2px; }
.sv2-support-mini .sv2-support-btn { width:34px; height:34px; border-radius:11px; margin-left:auto; }
.sv2-tutorial-panel { height:100%; }
.sv2-nexus-topup-inner { background:rgba(0,0,0,.12); }
.sv2-nexus-split { display:grid; grid-template-columns:minmax(0,.92fr) minmax(0,1.08fr); gap:16px; align-items:stretch; }
.sv2-nexus-box-kicker { color:#c4b5fd; font-size:.72rem; font-weight:950; text-transform:uppercase; letter-spacing:.08em; margin-bottom:6px; }
.sv2-nexus-box-text { color:rgba(255,255,255,.48); font-size:.8rem; line-height:1.45; margin:0 0 14px; }
.sv2-nexus-tutorial-side { margin-top:0 !important; height:100%; }
.sv2-nexus-compact-steps { grid-template-columns:1fr; gap:8px; }
.sv2-nexus-compact-steps .nexus-tutorial-step { padding:10px; }
.sv2-support-stage { display:block; }
.sv2-support-feature-card { background:radial-gradient(circle at 0% 0%,rgba(74,222,128,.12),transparent 30%),rgba(255,255,255,.02) !important; }
.sv2-support-status-block { padding:14px; border-radius:20px; background:rgba(0,0,0,.12); border:1px solid rgba(255,255,255,.07); }
.sv2-support-status-block.is-online-block { border-color:rgba(74,222,128,.18); background:linear-gradient(135deg,rgba(74,222,128,.055),rgba(0,0,0,.10)); }
.sv2-support-status-head { display:flex; align-items:center; justify-content:space-between; gap:12px; margin-bottom:12px; }
.sv2-support-status-head span { display:inline-flex; align-items:center; gap:8px; color:rgba(255,255,255,.86); font-size:.78rem; font-weight:950; text-transform:uppercase; letter-spacing:.08em; }
.sv2-support-status-head b { min-width:28px; height:28px; display:inline-flex; align-items:center; justify-content:center; border-radius:10px; background:rgba(255,255,255,.06); color:rgba(255,255,255,.86); font-size:.78rem; }
.sv2-support-feature-grid { display:grid; grid-template-columns:repeat(4,minmax(0,1fr)); gap:12px; }
.sv2-support-feature-grid.is-offline-grid { grid-template-columns:repeat(4,minmax(0,1fr)); }
.sv2-support-feature-member { display:flex; align-items:center; gap:12px; padding:14px; border-radius:18px; background:rgba(255,255,255,.025); border:1px solid rgba(255,255,255,.07); min-width:0; }
.sv2-support-feature-member.is-online { border-color:rgba(74,222,128,.20); background:rgba(74,222,128,.045); }
.sv2-support-feature-member.is-offline { opacity:.62; }
.sv2-support-avatar-wrap { position:relative; flex-shrink:0; }
.sv2-support-avatar-wrap img { width:46px; height:46px; border-radius:50%; object-fit:cover; border:1px solid rgba(255,255,255,.14); display:block; }
.sv2-support-avatar-wrap span { position:absolute; right:1px; bottom:1px; width:12px; height:12px; border-radius:50%; background:#4ade80; border:2px solid #202427; box-shadow:0 0 10px rgba(74,222,128,.65); }
.sv2-support-feature-member.is-offline .sv2-support-avatar-wrap span { background:rgba(255,255,255,.28); box-shadow:none; }
.sv2-support-member-main { min-width:0; flex:1; }
.sv2-support-member-main strong { display:block; font-size:.9rem; font-weight:950; line-height:1.2; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
.sv2-support-member-main small { display:block; color:rgba(255,255,255,.48); font-size:.74rem; margin-top:3px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
.sv2-support-feature-member .sv2-support-btn { margin-left:auto; }
@media(max-width:1399.98px){ .sv2-support-feature-grid,.sv2-support-feature-grid.is-offline-grid { grid-template-columns:repeat(2,minmax(0,1fr)); } }
@media(max-width:991.98px){ .sv2-nexus-split { grid-template-columns:1fr; } }
@media(max-width:575.98px){ .sv2-support-feature-grid,.sv2-support-feature-grid.is-offline-grid { grid-template-columns:1fr; } }

@media(max-width:1199.98px){ .sv2-command-grid,.sv2-bottom-grid { grid-template-columns:1fr; } .sv2-action-hub { grid-template-columns:repeat(2,1fr); } }
@media(max-width:767.98px){ .sv2-support-groups,.sv2-order-mini-grid { grid-template-columns:1fr; } }
@media(max-width:575.98px){ .sv2-action-hub { grid-template-columns:1fr; } }
.sv2-kpi-grid { display:grid; grid-template-columns:repeat(4,1fr); gap:14px; margin-bottom:22px; }
@media(max-width:900px){ .sv2-kpi-grid { grid-template-columns:repeat(2,1fr); } }
.sv2-kpi { border:1px solid rgba(255,255,255,.08); border-radius:18px; padding:18px; background:rgba(255,255,255,.02); position:relative; overflow:hidden; }
.sv2-kpi::before { content:''; position:absolute; top:-28px; right:-18px; width:80px; height:80px; border-radius:50%; opacity:.13; }
.sv2-kpi--purple { background:linear-gradient(135deg,rgba(109,92,255,.16),rgba(255,255,255,.03)); border-color:rgba(109,92,255,.22); }
.sv2-kpi--purple::before { background:#6d5cff; }
.sv2-kpi--green::before  { background:#22c55e; }
.sv2-kpi--amber::before  { background:#f59e0b; }
.sv2-kpi--blue::before   { background:#38bdf8; }
.sv2-kpi-label { color:var(--sv2-muted); font-size:.82rem; font-weight:800; text-transform:uppercase; letter-spacing:.08em; }
.sv2-kpi-value { color:var(--sv2-text); font-size:2rem; font-weight:900; margin-top:8px; line-height:1; }
.sv2-kpi-sub   { color:var(--sv2-muted); margin-top:6px; font-size:.84rem; }
.sv2-kpi--purple .sv2-kpi-value { color:#c4b5fd; }
.sv2-kpi--green  .sv2-kpi-value { color:#4ade80; }
.sv2-kpi--amber  .sv2-kpi-value { color:#fbbf24; }
.sv2-kpi--blue   .sv2-kpi-value { color:#7dd3fc; }
.sv2-feed { display:grid; gap:12px; }
.sv2-feed-item { display:flex; align-items:flex-start; gap:12px; padding:14px 16px; border:1px solid rgba(255,255,255,.08); border-radius:16px; background:rgba(255,255,255,.02); }
.sv2-feed-dot  { width:9px; height:9px; border-radius:50%; flex-shrink:0; margin-top:5px; }
.sv2-feed-dot--green { background:#4ade80; box-shadow:0 0 7px rgba(74,222,128,.5); }
.sv2-feed-dot--red   { background:#fb7185; box-shadow:0 0 7px rgba(251,113,133,.4); }
.sv2-feed-body  { flex:1; min-width:0; }
.sv2-feed-title { font-size:.86rem; font-weight:900; color:var(--sv2-text); }
.sv2-feed-meta  { font-size:.76rem; color:var(--sv2-muted); margin-top:3px; }
.sv2-feed-badge { flex-shrink:0; font-size:.76rem; font-weight:900; padding:5px 10px; border-radius:10px; white-space:nowrap; }
.sv2-feed-badge--green { background:rgba(74,222,128,.10); border:1px solid rgba(74,222,128,.22); color:#4ade80; }
.sv2-feed-badge--red   { background:rgba(251,113,133,.10); border:1px solid rgba(251,113,133,.22); color:#fb7185; }
.sv2-actions-2x2 { display:grid; grid-template-columns:1fr 1fr; gap:14px; }
.sv2-action-card { display:flex; gap:14px; align-items:flex-start; text-decoration:none; color:inherit; padding:18px; border-radius:18px; border:1px solid rgba(255,255,255,.08); background:rgba(255,255,255,.02); transition:.18s ease; }
.sv2-action-card:hover { transform:translateY(-2px); border-color:rgba(109,92,255,.30); box-shadow:0 18px 36px rgba(109,92,255,.10); }
.sv2-action-card-primary { background:linear-gradient(135deg,rgba(109,92,255,.18),rgba(255,255,255,.02)); border-color:rgba(109,92,255,.26); }
.sv2-action-icon { width:44px; height:44px; border-radius:14px; flex-shrink:0; display:flex; align-items:center; justify-content:center; background:linear-gradient(135deg,rgba(109,92,255,.26),rgba(176,92,255,.16)); border:1px solid rgba(109,92,255,.22); color:#fff; font-size:1rem; }
.sv2-action-title { color:var(--sv2-text); font-weight:900; font-size:.88rem; }
.sv2-action-text  { color:var(--sv2-muted); margin-top:4px; font-size:.82rem; }
.sv2-profile-line { display:flex; align-items:center; justify-content:space-between; gap:12px; padding:14px 16px; border-radius:16px; border:1px solid rgba(255,255,255,.08); background:rgba(255,255,255,.02); }
.sv2-profile-line span   { color:var(--sv2-muted); font-size:.84rem; }
.sv2-profile-line strong { color:var(--sv2-text); font-size:.9rem; font-weight:800; }
.sv2-online-pulse { display:inline-block; width:8px; height:8px; border-radius:50%; background:#4ade80; flex-shrink:0; box-shadow:0 0 0 0 rgba(74,222,128,.5); animation:sv2-pulse 2s infinite; }
@keyframes sv2-pulse { 0%{box-shadow:0 0 0 0 rgba(74,222,128,.5)} 70%{box-shadow:0 0 0 6px rgba(74,222,128,0)} 100%{box-shadow:0 0 0 0 rgba(74,222,128,0)} }
.sv2-support-list { display:grid; gap:12px; max-height:none; overflow:visible; }
.sv2-support-row { display:flex; align-items:center; justify-content:space-between; gap:12px; padding:14px 16px; border-radius:16px; border:1px solid rgba(255,255,255,.08); background:rgba(255,255,255,.02); }
.sv2-support-row.is-online  { border-color:rgba(74,222,128,.16); }
.sv2-support-row.is-offline { opacity:.78; }
.sv2-support-left   { display:flex; align-items:center; gap:12px; min-width:0; }
.sv2-support-avatar { width:42px; height:42px; border-radius:50%; object-fit:cover; border:1px solid rgba(255,255,255,.12); }
.sv2-support-name   { color:var(--sv2-text); font-weight:900; font-size:.88rem; }
.sv2-support-meta   { color:var(--sv2-muted); font-size:.78rem; margin-top:2px; }
.sv2-support-btn { width:40px; height:40px; border-radius:12px; flex-shrink:0; display:flex; align-items:center; justify-content:center; text-decoration:none; color:#fff; background:linear-gradient(135deg,rgba(109,92,255,.26),rgba(176,92,255,.16)); border:1px solid rgba(109,92,255,.22); }
.sv2-support-btn.is-disabled { opacity:.38; pointer-events:none; }
.sv2-empty-state { text-align:center; padding:44px 20px 20px; }
.sv2-empty-state i { font-size:2.4rem; color:rgba(255,255,255,.22); display:block; margin-bottom:14px; }
.sv2-empty-title { color:var(--sv2-text); font-weight:900; font-size:.9rem; }
.sv2-empty-text  { color:var(--sv2-muted); font-size:.84rem; margin-top:4px; }
.sv2-empty-inline { color:var(--sv2-muted); padding:6px 0; font-size:.84rem; }
.ov-name-red        { color:#f87171 !important; }
.ov-name-green      { color:#34d399 !important; }
.ov-name-lightgreen { color:#6ee7b7 !important; }
.ov-name-blue       { color:#60a5fa !important; }
.ov-name-purple     { color:#c084fc !important; }
@media(max-width:767.98px){ .sv2-actions-2x2{grid-template-columns:1fr;} .sv2-kpi-value{font-size:1.6rem;} }

/* Banner/Avatar upload modals (seller-style reused) */
.sdash-modal-preview-img  { width:60px;height:60px;border-radius:50%;object-fit:cover;border:2px solid rgba(255,255,255,.15); }
.sdash-modal-preview-letter { width:60px;height:60px;border-radius:50%;background:linear-gradient(135deg,#2c3450,#22283a);border:2px solid rgba(255,255,255,.12);display:flex;align-items:center;justify-content:center;font-size:1.5rem;font-weight:800;color:#60a5fa; }
.sdash-dropzone { display:block;border:1px dashed rgba(255,255,255,.20);border-radius:14px;padding:20px 18px;cursor:pointer;background:rgba(255,255,255,.025);text-align:center;transition:border-color .12s,background .12s,transform .08s;user-select:none; }
.sdash-dropzone:hover { border-color:rgba(109,92,255,.65);background:rgba(109,92,255,.08);transform:translateY(-1px); }
.sdash-dropzone-icon  { font-size:1.7rem;color:rgba(109,92,255,.75);display:block;margin-bottom:7px; }
.sdash-dropzone-title { font-weight:900;color:rgba(255,255,255,.9); }
.sdash-dropzone-hint  { font-size:.83rem;color:rgba(255,255,255,.42);margin-top:4px; }
.sdash-dropzone--banner { padding:18px; }
.sdash-banner-preview-wrap { border-radius:12px;overflow:hidden;border:1px solid rgba(255,255,255,.08); }
.sdash-banner-preview-img  { width:100%;aspect-ratio:4/1;object-fit:cover;display:block;min-height:80px; }
.sdash-banner-preview-placeholder { aspect-ratio:4/1;min-height:80px;display:flex;flex-direction:column;align-items:center;justify-content:center;gap:.5rem;color:rgba(255,255,255,.25);font-size:.85rem;background:rgba(255,255,255,.02); }
.sdash-banner-preview-placeholder i { font-size:2rem; }
.sdash-reposition-stage { position:relative;width:100%;aspect-ratio:4/1;min-height:80px;border-radius:12px;overflow:hidden;border:1px solid rgba(255,255,255,.12);cursor:grab;user-select:none;background:#0d0f1a; }
.sdash-reposition-stage:active { cursor:grabbing; }
.sdash-reposition-img { position:absolute;width:100%;height:100%;object-fit:cover;pointer-events:none; }
.sdash-reposition-crosshair { position:absolute;top:50%;left:50%;transform:translate(-50%,-50%);width:32px;height:32px; }
.sdash-reposition-crosshair::before,.sdash-reposition-crosshair::after { content:'';position:absolute;background:rgba(255,255,255,.55);border-radius:1px; }
.sdash-reposition-crosshair::before { width:1px;height:100%;left:50%;top:0; }
.sdash-reposition-crosshair::after  { height:1px;width:100%;top:50%;left:0; }
</style>


<style>
/* Clean Modern Dashboard */
.dash-modern-grid { display:grid; grid-template-columns:1.25fr 1fr 1fr 1fr; gap:14px; }
.dash-stat { min-height:118px; padding:20px; border-radius:22px; background:linear-gradient(145deg,rgba(255,255,255,.045),rgba(255,255,255,.018)); border:1px solid rgba(255,255,255,.08); position:relative; overflow:hidden; }
.dash-stat::after { content:''; position:absolute; right:-34px; top:-34px; width:100px; height:100px; border-radius:50%; background:rgba(109,92,255,.14); }
.dash-stat span { display:block; color:rgba(255,255,255,.52); font-size:.76rem; font-weight:950; text-transform:uppercase; letter-spacing:.08em; }
.dash-stat strong { display:block; margin-top:10px; color:rgba(255,255,255,.94); font-size:1.75rem; line-height:1; font-weight:950; }
.dash-stat small { display:block; margin-top:8px; color:rgba(255,255,255,.42); font-size:.8rem; }
.dash-stat-main { background:linear-gradient(145deg,rgba(109,92,255,.22),rgba(255,255,255,.025)); border-color:rgba(109,92,255,.30); }
.dash-stat-main strong { color:#c4b5fd; }
.dash-stat-warning strong { color:#fbbf24; }
.dash-main-grid { display:grid; grid-template-columns:minmax(0,1.45fr) minmax(330px,.75fr); gap:18px; align-items:stretch; }
.dash-side-stack { display:grid; gap:18px; }
.dash-card { border-radius:24px !important; background:linear-gradient(145deg,rgba(255,255,255,.038),rgba(255,255,255,.016)) !important; border:1px solid rgba(255,255,255,.08) !important; box-shadow:none !important; }
.dash-card::before, .dash-card::after { display:none !important; content:none !important; }
.dash-card-head { display:flex; align-items:flex-start; justify-content:space-between; gap:16px; }
.dash-card-head h3 { margin:0; color:rgba(255,255,255,.94); font-size:1rem; font-weight:950; }
.dash-card-head p { margin:5px 0 0; color:rgba(255,255,255,.48); font-size:.82rem; font-weight:700; }
.dash-card-head a { display:inline-flex; align-items:center; justify-content:center; min-height:32px; padding:7px 12px; border-radius:999px; color:rgba(255,255,255,.84); background:rgba(255,255,255,.05); border:1px solid rgba(255,255,255,.09); text-decoration:none; font-size:.76rem; font-weight:950; white-space:nowrap; }
.dash-card-head a:hover { color:#fff; background:rgba(109,92,255,.13); border-color:rgba(109,92,255,.32); }
.dash-activity-list { display:grid; gap:11px; }
.dash-activity-item { display:flex; align-items:center; gap:13px; min-height:64px; padding:13px 15px; border-radius:17px; background:rgba(0,0,0,.13); border:1px solid rgba(255,255,255,.07); }
.dash-activity-dot { width:10px; height:10px; border-radius:999px; flex-shrink:0; }
.dash-activity-item.is-positive .dash-activity-dot { background:#4ade80; box-shadow:0 0 0 4px rgba(74,222,128,.10); }
.dash-activity-item.is-negative .dash-activity-dot { background:#fb7185; box-shadow:0 0 0 4px rgba(251,113,133,.10); }
.dash-activity-text { flex:1; min-width:0; }
.dash-activity-text strong { display:block; color:rgba(255,255,255,.90); font-size:.86rem; font-weight:950; }
.dash-activity-text span { display:block; color:rgba(255,255,255,.45); font-size:.74rem; margin-top:3px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
.dash-activity-item b { flex-shrink:0; padding:6px 10px; border-radius:999px; background:rgba(255,255,255,.05); color:rgba(255,255,255,.86); font-size:.75rem; font-weight:950; }
.dash-action-grid { display:grid; grid-template-columns:1fr 1fr; gap:10px; }
.dash-action-grid a { min-height:86px; padding:15px; border-radius:18px; background:rgba(0,0,0,.12); border:1px solid rgba(255,255,255,.07); color:inherit; text-decoration:none; transition:.16s ease; }
.dash-action-grid a:hover { transform:translateY(-2px); background:rgba(109,92,255,.10); border-color:rgba(109,92,255,.25); }
.dash-action-grid i { width:34px; height:34px; display:flex; align-items:center; justify-content:center; border-radius:12px; color:#fff; background:linear-gradient(135deg,rgba(109,92,255,.40),rgba(176,92,255,.20)); margin-bottom:10px; }
.dash-action-grid strong { display:block; color:rgba(255,255,255,.90); font-size:.82rem; font-weight:950; }
.dash-action-grid span { display:block; color:rgba(255,255,255,.45); font-size:.74rem; margin-top:3px; }
.dash-orders-row { display:flex; align-items:center; justify-content:space-between; padding:13px 15px; border-radius:15px; background:rgba(0,0,0,.12); border:1px solid rgba(255,255,255,.07); margin-top:10px; }
.dash-orders-row span { color:rgba(255,255,255,.52); font-size:.82rem; font-weight:800; }
.dash-orders-row strong { color:rgba(255,255,255,.92); font-size:.92rem; font-weight:950; }
.dash-nexus-card { background:radial-gradient(circle at 100% 0%,rgba(109,92,255,.20),transparent 33%),linear-gradient(145deg,rgba(255,255,255,.04),rgba(255,255,255,.016)) !important; border-color:rgba(109,92,255,.18) !important; }
.dash-balance-pill { padding:9px 13px; border-radius:999px; background:rgba(109,92,255,.13); border:1px solid rgba(109,92,255,.27); color:#c4b5fd; font-size:.78rem; font-weight:950; white-space:nowrap; }
.dash-nexus-split { display:grid; grid-template-columns:minmax(0,1fr) minmax(0,1fr); gap:18px; align-items:stretch; }
.dash-nexus-panel { padding:20px; border-radius:22px; background:rgba(0,0,0,.13); border:1px solid rgba(255,255,255,.08); }
.dash-topup-panel { border-color:rgba(109,92,255,.20); }
.dash-panel-kicker { color:#c4b5fd; font-size:.76rem; font-weight:950; text-transform:uppercase; letter-spacing:.08em; margin-bottom:16px; }
.dash-receive-row { display:flex; align-items:center; justify-content:space-between; gap:12px; padding:14px 16px; border-radius:16px; background:rgba(255,255,255,.035); border:1px solid rgba(255,255,255,.08); }
.dash-receive-row span { color:rgba(255,255,255,.55); font-size:.84rem; }
.dash-receive-row strong { color:rgba(255,255,255,.94); font-weight:950; }
.dash-tutorial-panel { display:grid; grid-template-columns:1fr auto; gap:16px; align-content:start; }
.dash-tutorial-title { display:flex; align-items:center; gap:12px; }
.dash-tutorial-title > div:first-child { width:44px; height:44px; border-radius:15px; display:flex; align-items:center; justify-content:center; color:#fff; background:linear-gradient(135deg,rgba(109,92,255,.38),rgba(176,92,255,.20)); border:1px solid rgba(109,92,255,.30); }
.dash-tutorial-title strong { display:block; color:rgba(255,255,255,.92); font-size:.92rem; font-weight:950; }
.dash-tutorial-title span { display:block; color:rgba(255,255,255,.45); font-size:.78rem; margin-top:3px; }
.dash-download-btn { display:inline-flex; align-items:center; justify-content:center; height:42px; padding:0 14px; border-radius:14px; color:#fff; background:linear-gradient(135deg,#6d5cff,#7c3aed); text-decoration:none; font-weight:950; font-size:.82rem; white-space:nowrap; box-shadow:0 12px 26px rgba(109,92,255,.20); }
.dash-download-btn:hover { color:#fff; transform:translateY(-1px); }
.dash-tutorial-steps { grid-column:1 / -1; display:grid; gap:10px; margin-top:2px; }
.dash-tutorial-steps div { display:flex; align-items:flex-start; gap:11px; padding:12px; border-radius:15px; background:rgba(255,255,255,.035); border:1px solid rgba(255,255,255,.07); }
.dash-tutorial-steps b { width:24px; height:24px; border-radius:9px; display:flex; align-items:center; justify-content:center; flex-shrink:0; background:rgba(109,92,255,.25); border:1px solid rgba(109,92,255,.35); color:#fff; font-size:.75rem; }
.dash-tutorial-steps span { color:rgba(255,255,255,.66); font-size:.82rem; line-height:1.35; }
.dash-support-card { background:linear-gradient(145deg,rgba(74,222,128,.045),rgba(255,255,255,.015)) !important; }
.dash-support-card .dash-card-head p { display:flex; align-items:center; gap:7px; }
.dash-support-label { display:flex; align-items:center; justify-content:space-between; margin-bottom:12px; }
.dash-support-label span { color:rgba(255,255,255,.70); font-size:.78rem; font-weight:950; text-transform:uppercase; letter-spacing:.08em; }
.dash-support-label b { padding:3px 9px; border-radius:999px; background:rgba(74,222,128,.12); color:#4ade80; font-size:.78rem; }
.dash-support-label-muted b { background:rgba(255,255,255,.06); color:rgba(255,255,255,.45); }
.dash-support-grid { display:grid; grid-template-columns:repeat(4,minmax(0,1fr)); gap:12px; }
.dash-support-member { display:flex; align-items:center; gap:12px; min-height:82px; padding:14px; border-radius:18px; background:rgba(0,0,0,.13); border:1px solid rgba(255,255,255,.08); }
.dash-support-member.is-online { border-color:rgba(74,222,128,.20); background:linear-gradient(135deg,rgba(74,222,128,.06),rgba(0,0,0,.12)); }
.dash-support-member.is-offline { opacity:.58; }
.dash-support-member img { width:46px; height:46px; border-radius:50%; object-fit:cover; border:1px solid rgba(255,255,255,.12); flex-shrink:0; }
.dash-support-member div { flex:1; min-width:0; }
.dash-support-member strong { display:block; font-size:.87rem; font-weight:950; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
.dash-support-member span { display:block; color:rgba(255,255,255,.48); font-size:.74rem; margin-top:3px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
.dash-support-member a, .dash-support-member button { width:38px; height:38px; border-radius:13px; border:1px solid rgba(109,92,255,.24); background:rgba(109,92,255,.18); color:#fff; display:flex; align-items:center; justify-content:center; flex-shrink:0; }
.dash-support-member button { opacity:.45; }
.dash-support-grid-offline { grid-template-columns:repeat(4,minmax(0,1fr)); }
@media(max-width:1200px){
  .dash-modern-grid { grid-template-columns:repeat(2,1fr); }
  .dash-main-grid { grid-template-columns:1fr; }
  .dash-nexus-split { grid-template-columns:1fr; }
  .dash-support-grid, .dash-support-grid-offline { grid-template-columns:repeat(2,minmax(0,1fr)); }
}
@media(max-width:680px){
  .dash-modern-grid, .dash-action-grid, .dash-support-grid, .dash-support-grid-offline { grid-template-columns:1fr; }
  .dash-card-head, .dash-nexus-head, .dash-tutorial-panel { grid-template-columns:1fr; flex-direction:column; }
  .dash-balance-pill, .dash-download-btn { width:100%; }
}
</style>
<!-- =============================================
     NEXUS CONFIRM MODAL
     ============================================= -->
<div class="modal fade" id="nexus-confirm-modal" tabindex="-1" aria-labelledby="nexusConfirmLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered nexus-confirm-dialog">
    <div class="modal-content nexus-confirm-content">
      <div class="nexus-confirm-glow"></div>

      <div class="nexus-confirm-head">
        <div class="nexus-confirm-title-wrap">
          <div class="nexus-confirm-icon"><i class="fa-duotone fa-bolt"></i></div>
          <div>
            <h5 class="modal-title" id="nexusConfirmLabel">Confirm Nexus Top Up</h5>
            <p>Review your transfer before we add it to Nexus.</p>
          </div>
        </div>
        <button type="button" class="nexus-confirm-close" data-bs-dismiss="modal" aria-label="Close">
          <i class="fa-solid fa-xmark"></i>
        </button>
      </div>

      <div class="modal-body nexus-confirm-body">
        <div class="nexus-confirm-amount-card">
          <div class="nexus-confirm-amount-left">
            <div class="nexus-modal-preview-icon">
              <i class="fa-duotone fa-bolt" id="nexus-modal-icon"></i>
            </div>
            <div>
              <div class="nexus-confirm-small">You will receive</div>
              <div class="nexus-confirm-product" id="nexus-modal-plan-label">Nexus Balance Top Up</div>
            </div>
          </div>
          <div class="nexus-confirm-amount" id="nexus-modal-price">—</div>
        </div>

        <div class="nexus-confirm-flow">
          <div class="nexus-confirm-flow-item">
            <span>1</span>
            <div>
              <strong>Booster Balance</strong>
              <small>The amount is deducted from your available balance.</small>
            </div>
          </div>
          <div class="nexus-confirm-flow-item">
            <span>2</span>
            <div>
              <strong>Nexus Balance</strong>
              <small>The same amount is added to your Nexus account.</small>
            </div>
          </div>
          <div class="nexus-confirm-flow-item">
            <span>3</span>
            <div>
              <strong>Buy membership</strong>
              <small>Open Nexus and select Balance as payment method.</small>
            </div>
          </div>
        </div>

        <div class="nexus-confirm-warning">
          <i class="fa-duotone fa-triangle-exclamation"></i>
          <div>
            <strong>Final transfer</strong>
            <span>The amount is deducted immediately. Refunds are not possible after the transfer.</span>
          </div>
        </div>
      </div>

      <div class="nexus-confirm-actions">
        <button type="button" class="btn nexus-confirm-cancel" data-bs-dismiss="modal">Cancel</button>
        <button type="button" class="btn nexus-confirm-submit" id="nexus-confirm-btn" onclick="nexusConfirmPurchase()">
          <span id="nexus-btn-text"><i class="fa-duotone fa-bolt me-2"></i>Confirm top up</span>
          <span id="nexus-btn-loading" style="display:none;">
            <span class="spinner-border spinner-border-sm me-2" role="status"></span>Processing…
          </span>
        </button>
      </div>
    </div>
  </div>
</div>

<!-- Nexus Toast Container -->
<div id="nexus-toast-wrap" style="position:fixed;top:20px;right:20px;z-index:99999;display:flex;flex-direction:column;gap:10px;pointer-events:none;"></div>

<style>
/* Nexus Custom Top Up */
.nexus-topup-box {
  border-radius:18px; border:1px solid rgba(255,255,255,.08);
  background:rgba(255,255,255,.02); padding:18px;
}
.nexus-field-label { color:rgba(255,255,255,.78); font-size:.82rem; font-weight:900; margin-bottom:8px; }
.nexus-amount-wrap { position:relative; }
.nexus-currency { position:absolute; left:14px; top:50%; transform:translateY(-50%); color:rgba(255,255,255,.55); font-weight:900; z-index:2; }
.nexus-amount-input { padding-left:34px !important; border-radius:14px !important; font-weight:900 !important; }
.nexus-quick-buttons { display:flex; gap:8px; flex-wrap:wrap; }
.nexus-quick-buttons .btn { border-radius:999px !important; font-weight:900 !important; padding:5px 13px !important; }
.nexus-tutorial-box {
  padding:18px; border-radius:20px;
  background:linear-gradient(135deg,rgba(109,92,255,.09),rgba(255,255,255,.025));
  border:1px solid rgba(109,92,255,.18);
}
.nexus-tutorial-head { display:flex; align-items:center; justify-content:space-between; gap:14px; margin-bottom:16px; }
.nexus-tutorial-title-wrap { display:flex; align-items:center; gap:12px; min-width:0; }
.nexus-tutorial-icon {
  width:42px; height:42px; border-radius:14px; flex-shrink:0;
  display:flex; align-items:center; justify-content:center;
  background:linear-gradient(135deg,rgba(109,92,255,.28),rgba(176,92,255,.16));
  border:1px solid rgba(109,92,255,.3); color:#fff;
}
.nexus-tutorial-box h6 { color:rgba(255,255,255,.92); margin:0; }
.nexus-tutorial-box p { color:rgba(255,255,255,.46); margin:0; font-size:.8rem; }
.nexus-download-btn {
  display:inline-flex; align-items:center; justify-content:center; gap:5px; flex-shrink:0;
  padding:8px 12px; border-radius:12px; text-decoration:none;
  color:#fff; font-size:.82rem; font-weight:900;
  background:linear-gradient(135deg,#6d5cff,#7c3aed);
  border:1px solid rgba(255,255,255,.12);
  box-shadow:0 10px 24px rgba(109,92,255,.18);
}
.nexus-download-btn:hover { color:#fff; transform:translateY(-1px); box-shadow:0 14px 30px rgba(109,92,255,.25); }
.nexus-tutorial-grid { display:grid; grid-template-columns:repeat(2,minmax(0,1fr)); gap:10px; }
.nexus-tutorial-step {
  display:flex; align-items:flex-start; gap:10px; padding:12px;
  border-radius:15px; background:rgba(0,0,0,.14); border:1px solid rgba(255,255,255,.07);
}
.nexus-tutorial-step span {
  display:inline-flex; align-items:center; justify-content:center; width:24px; height:24px;
  border-radius:9px; flex-shrink:0;
  background:rgba(109,92,255,.28); border:1px solid rgba(109,92,255,.38);
  font-size:.75rem; font-weight:950; color:rgba(255,255,255,.95);
}
.nexus-tutorial-step strong { display:block; color:rgba(255,255,255,.86); font-size:.82rem; font-weight:950; line-height:1.25; }
.nexus-tutorial-step small { display:block; color:rgba(255,255,255,.48); font-size:.75rem; line-height:1.35; margin-top:2px; }
.nexus-step-download { border-color:rgba(109,92,255,.24); background:rgba(109,92,255,.09); }
.nexus-step-wide { grid-column:1 / -1; }
@media(max-width:575.98px){
  .nexus-tutorial-head { align-items:flex-start; flex-direction:column; }
  .nexus-download-btn { width:100%; }
  .nexus-tutorial-grid { grid-template-columns:1fr; }
}

/* Plan Cards Legacy */
.nexus-plan-card {
  border-radius:18px; border:1px solid rgba(255,255,255,.08);
  background:rgba(255,255,255,.02); padding:18px;
  display:flex; flex-direction:column; gap:16px; height:100%;
  transition:transform .18s ease,box-shadow .18s ease,border-color .18s ease;
}
.nexus-plan-card.nexus-plan-available { cursor:pointer; }
.nexus-plan-card.nexus-plan-available:hover {
  transform:translateY(-2px); border-color:rgba(109,92,255,.45);
  box-shadow:0 16px 40px rgba(109,92,255,.14);
}
.nexus-plan-card.nexus-plan-premium.nexus-plan-available:hover {
  border-color:rgba(176,92,255,.45); box-shadow:0 16px 40px rgba(176,92,255,.14);
}
.nexus-plan-card.nexus-plan-locked { opacity:.45; filter:grayscale(.4); pointer-events:none; }

.nexus-plan-top  { display:flex; align-items:center; gap:12px; }
.nexus-plan-icon {
  width:40px; height:40px; border-radius:14px; flex-shrink:0;
  display:flex; align-items:center; justify-content:center;
  background:linear-gradient(135deg,rgba(109,92,255,.22),rgba(176,92,255,.14));
  border:1px solid rgba(109,92,255,.28); font-size:1.1rem; color:rgba(255,255,255,.92);
}
.nexus-plan-premium .nexus-plan-icon {
  background:linear-gradient(135deg,rgba(176,92,255,.25),rgba(255,92,200,.12));
  border-color:rgba(176,92,255,.35);
}
.nexus-plan-name { font-weight:950; font-size:1rem; color:rgba(255,255,255,.92); line-height:1.2; }
.nexus-plan-sub  { font-size:.78rem; color:rgba(255,255,255,.38); margin-top:2px; }
.nexus-plan-badge { margin-left:auto; flex-shrink:0; padding:4px 10px; border-radius:999px; font-size:.73rem; font-weight:800; }
.nexus-badge-available { background:rgba(74,222,128,.12); border:1px solid rgba(74,222,128,.25); color:#4ade80; }
.nexus-badge-locked    { background:rgba(255,255,255,.05); border:1px solid rgba(255,255,255,.10); color:rgba(255,255,255,.35); }

.nexus-plan-bottom {
  display:flex; align-items:center; justify-content:space-between; gap:10px;
  padding-top:12px; border-top:1px solid rgba(255,255,255,.06);
}
.nexus-plan-price { font-size:1.35rem; font-weight:950; color:rgba(255,255,255,.92); letter-spacing:-.02em; }
.nexus-buy-btn    { border-radius:12px !important; font-weight:800 !important; font-size:.88rem !important; padding:7px 18px !important; }
.nexus-missing    { font-size:.82rem; color:rgba(255,255,255,.32); display:flex; align-items:center; gap:4px; }

/* ── Modal ───────────────────────────────── */
.nexus-confirm-dialog { max-width:560px; }
.nexus-confirm-content {
  position:relative; overflow:hidden; border-radius:26px !important;
  background:linear-gradient(180deg,#24272a 0%,#1f2225 100%);
  border:1px solid rgba(255,255,255,.09);
  box-shadow:0 26px 70px rgba(0,0,0,.58);
}
.nexus-confirm-glow {
  position:absolute; inset:-120px -120px auto auto; width:260px; height:260px;
  border-radius:50%; background:rgba(109,92,255,.22); filter:blur(32px); pointer-events:none;
}
.nexus-confirm-head {
  position:relative; display:flex; align-items:flex-start; justify-content:space-between;
  gap:18px; padding:24px 24px 14px;
}
.nexus-confirm-title-wrap { display:flex; align-items:center; gap:14px; min-width:0; }
.nexus-confirm-icon {
  width:46px; height:46px; border-radius:16px; flex-shrink:0;
  display:flex; align-items:center; justify-content:center;
  background:linear-gradient(135deg,rgba(109,92,255,.30),rgba(176,92,255,.18));
  border:1px solid rgba(139,92,246,.35); color:#fff; font-size:1.1rem;
}
.nexus-confirm-head h5 { color:rgba(255,255,255,.94); font-size:1.05rem; font-weight:950; margin:0; }
.nexus-confirm-head p { color:rgba(255,255,255,.48); font-size:.82rem; margin:4px 0 0; }
.nexus-confirm-close {
  width:36px; height:36px; border-radius:12px; border:1px solid rgba(255,255,255,.08);
  background:rgba(255,255,255,.035); color:rgba(255,255,255,.45);
  display:flex; align-items:center; justify-content:center; transition:.15s ease;
}
.nexus-confirm-close:hover { color:#fff; background:rgba(255,255,255,.08); }
.nexus-confirm-body { position:relative; padding:0 24px 18px; }
.nexus-confirm-amount-card {
  display:flex; align-items:center; justify-content:space-between; gap:18px;
  padding:18px; border-radius:20px;
  background:rgba(255,255,255,.035); border:1px solid rgba(255,255,255,.08);
}
.nexus-confirm-amount-left { display:flex; align-items:center; gap:14px; min-width:0; }
.nexus-modal-preview-icon {
  width:48px; height:48px; border-radius:16px; flex-shrink:0;
  display:flex; align-items:center; justify-content:center;
  background:linear-gradient(135deg,rgba(109,92,255,.26),rgba(176,92,255,.16));
  border:1px solid rgba(109,92,255,.32); font-size:1.15rem; color:rgba(255,255,255,.94);
}
.nexus-confirm-small { color:rgba(255,255,255,.45); font-size:.75rem; font-weight:800; text-transform:uppercase; letter-spacing:.07em; }
.nexus-confirm-product { color:rgba(255,255,255,.92); font-size:1rem; font-weight:950; margin-top:2px; }
.nexus-confirm-amount { color:#fff; font-size:1.55rem; line-height:1; font-weight:950; white-space:nowrap; }
.nexus-confirm-flow { display:grid; grid-template-columns:1fr; gap:10px; margin-top:14px; }
.nexus-confirm-flow-item {
  display:flex; align-items:flex-start; gap:12px; padding:13px 14px;
  border-radius:16px; background:rgba(109,92,255,.055); border:1px solid rgba(109,92,255,.12);
}
.nexus-confirm-flow-item span {
  width:26px; height:26px; border-radius:9px; flex-shrink:0;
  display:flex; align-items:center; justify-content:center;
  background:rgba(109,92,255,.28); border:1px solid rgba(109,92,255,.42);
  color:rgba(255,255,255,.95); font-size:.78rem; font-weight:950;
}
.nexus-confirm-flow-item strong { display:block; color:rgba(255,255,255,.88); font-size:.86rem; font-weight:950; }
.nexus-confirm-flow-item small { display:block; color:rgba(255,255,255,.50); font-size:.78rem; line-height:1.35; margin-top:2px; }
.nexus-confirm-warning {
  display:flex; gap:12px; align-items:flex-start; margin-top:14px; padding:14px 16px;
  border-radius:18px; background:rgba(245,158,11,.08); border:1px solid rgba(245,158,11,.22);
}
.nexus-confirm-warning i { color:#fbbf24; font-size:1rem; margin-top:2px; }
.nexus-confirm-warning strong { display:block; color:rgba(255,255,255,.88); font-size:.84rem; font-weight:950; }
.nexus-confirm-warning span { display:block; color:rgba(255,255,255,.56); font-size:.8rem; line-height:1.45; margin-top:2px; }
.nexus-confirm-actions {
  display:flex; justify-content:flex-end; gap:12px; padding:0 24px 24px;
}
.nexus-confirm-cancel {
  border-radius:14px !important; padding:10px 18px !important; font-weight:850 !important;
  background:rgba(255,255,255,.035) !important; border:1px solid rgba(255,255,255,.08) !important;
  color:rgba(255,255,255,.58) !important;
}
.nexus-confirm-cancel:hover { color:#fff !important; background:rgba(255,255,255,.07) !important; }
.nexus-confirm-submit {
  border-radius:14px !important; padding:10px 22px !important; font-weight:950 !important;
  background:linear-gradient(135deg,#6d5cff,#7c3aed) !important; border:1px solid rgba(255,255,255,.10) !important;
  color:#fff !important; box-shadow:0 12px 28px rgba(109,92,255,.20);
}
.nexus-confirm-submit:hover { transform:translateY(-1px); box-shadow:0 16px 34px rgba(109,92,255,.26); }
@media(max-width:575.98px){
  .nexus-confirm-head,.nexus-confirm-body,.nexus-confirm-actions { padding-left:18px; padding-right:18px; }
  .nexus-confirm-actions { flex-direction:column-reverse; }
  .nexus-confirm-actions .btn { width:100%; }
  .nexus-confirm-amount-card { align-items:flex-start; flex-direction:column; }
}

/* ── Self-contained Toast ────────────────── */
.nexus-toast {
  pointer-events:all;
  min-width:280px; max-width:360px;
  padding:14px 16px; border-radius:16px;
  display:flex; align-items:flex-start; gap:12px;
  font-size:.88rem; font-weight:500;
  backdrop-filter:blur(12px);
  box-shadow:0 8px 32px rgba(0,0,0,.45);
  animation:nexusToastIn .25s ease forwards;
}
.nexus-toast.nt-success { background:rgba(30,50,35,.96); border:1px solid rgba(74,222,128,.3); color:rgba(255,255,255,.9); }
.nexus-toast.nt-danger  { background:rgba(50,22,22,.96); border:1px solid rgba(255,80,80,.3);  color:rgba(255,255,255,.9); }
.nexus-toast.nt-warning { background:rgba(50,42,10,.96); border:1px solid rgba(255,193,7,.3);  color:rgba(255,255,255,.9); }
.nexus-toast-icon { font-size:1.15rem; flex-shrink:0; margin-top:1px; }
.nexus-toast.nt-success .nexus-toast-icon { color:#4ade80; }
.nexus-toast.nt-danger  .nexus-toast-icon { color:#f87171; }
.nexus-toast.nt-warning .nexus-toast-icon { color:#f5c84c; }
.nexus-toast-title   { font-weight:800; margin-bottom:2px; color:rgba(255,255,255,.95); }
.nexus-toast-msg     { color:rgba(255,255,255,.7); line-height:1.4; }
.nexus-toast-dismiss { margin-left:auto; flex-shrink:0; background:none; border:none; color:rgba(255,255,255,.4); cursor:pointer; padding:0; font-size:1rem; line-height:1; }
.nexus-toast-dismiss:hover { color:rgba(255,255,255,.8); }
@keyframes nexusToastIn {
  from { opacity:0; transform:translateX(24px); }
  to   { opacity:1; transform:translateX(0);    }
}
@keyframes nexusToastOut {
  from { opacity:1; transform:translateX(0);    }
  to   { opacity:0; transform:translateX(24px); }
}
</style>

<script>
(function () {

  /* ── Self-contained Toast ─────────────────────────── */
  function nexusToast(type, title, msg) {
    var icons = { success: 'fa-duotone fa-circle-check', danger: 'fa-duotone fa-circle-xmark', warning: 'fa-duotone fa-triangle-exclamation' };
    var wrap  = document.getElementById('nexus-toast-wrap');
    var el    = document.createElement('div');
    el.className = 'nexus-toast nt-' + (type || 'success');
    el.innerHTML =
      '<i class="' + (icons[type] || icons.success) + ' nexus-toast-icon"></i>' +
      '<div class="flex-grow-1">' +
        '<div class="nexus-toast-title">' + (title || '') + '</div>' +
        '<div class="nexus-toast-msg">'   + (msg   || '') + '</div>' +
      '</div>' +
      '<button class="nexus-toast-dismiss" onclick="this.closest(\'.nexus-toast\').remove()">&times;</button>';
    wrap.appendChild(el);
    setTimeout(function () {
      el.style.animation = 'nexusToastOut .3s ease forwards';
      setTimeout(function () { el.remove(); }, 320);
    }, 5000);
  }

  /* Custom Nexus Top Up */
  var selectedNexusAmount = 0;
  var nexusModal = null;
  var boosterAvailableCents = <?= (int)$nexus_available_cents ?>;

  function formatEuro(amount) {
    return '€' + Number(amount || 0).toFixed(2);
  }

  function getNexusAmount() {
    var input = document.getElementById('nexus-amount-input');
    if (!input) return 0;
    var raw = String(input.value || '').replace(',', '.');
    var amount = parseFloat(raw);
    return isNaN(amount) ? 0 : amount;
  }

  function updateNexusPreview() {
    var amount = getNexusAmount();
    var preview = document.getElementById('nexus-preview');
    if (preview) preview.textContent = formatEuro(amount) + ' Nexus Balance';
  }

  window.setNexusAmount = function (amount) {
    var input = document.getElementById('nexus-amount-input');
    if (!input) return;
    input.value = Number(amount || 0).toFixed(2);
    updateNexusPreview();
  };

  window.setNexusMaxAmount = function () {
    window.setNexusAmount(boosterAvailableCents / 100);
  };

  window.nexusOpenTopupModal = function () {
    var amount = getNexusAmount();
    var amountCents = Math.round(amount * 100);

    if (amountCents < 100) {
      nexusToast('warning', 'Enter an amount', 'The minimum top-up amount is €1.00.');
      return;
    }

    if (amountCents > boosterAvailableCents) {
      nexusToast('danger', 'Insufficient balance', 'You do not have enough available Booster Balance for this top-up.');
      return;
    }

    selectedNexusAmount = amount;
    document.getElementById('nexus-modal-plan-label').textContent = 'Nexus Balance Top Up';
    document.getElementById('nexus-modal-price').textContent = formatEuro(amount);
    document.getElementById('nexus-modal-icon').className = 'fa-duotone fa-bolt';
    if (!nexusModal) nexusModal = new bootstrap.Modal(document.getElementById('nexus-confirm-modal'));
    nexusModal.show();
  };

  window.nexusConfirmPurchase = function () {
    var amountCents = Math.round(Number(selectedNexusAmount || 0) * 100);
    if (amountCents < 100) return;

    var btnText    = document.getElementById('nexus-btn-text');
    var btnLoading = document.getElementById('nexus-btn-loading');
    var confirmBtn = document.getElementById('nexus-confirm-btn');
    btnText.style.display    = 'none';
    btnLoading.style.display = '';
    confirmBtn.disabled      = true;

    var fd = new FormData();
    fd.append('action', 'booster_buy_nexus');
    fd.append('amount', Number(selectedNexusAmount).toFixed(2));

    fetch('/ajax', { method: 'POST', body: fd })
      .then(function (r) { return r.json(); })
      .then(function (data) {
        btnText.style.display    = '';
        btnLoading.style.display = 'none';
        confirmBtn.disabled      = false;
        if (nexusModal) nexusModal.hide();
        if (data.sendToast) {
          nexusToast(data.sendToast.type, data.sendToast.title, data.sendToast.message);
        }
        if (data.success) setTimeout(function () { location.reload(); }, 2500);
      })
      .catch(function (err) {
        btnText.style.display    = '';
        btnLoading.style.display = 'none';
        confirmBtn.disabled      = false;
        console.error('Nexus top-up error', err);
        nexusToast('danger', 'Error', 'Network error. Please try again.');
      });
  };

  document.addEventListener('input', function (e) {
    if (e.target && e.target.id === 'nexus-amount-input') updateNexusPreview();
  });

})();
</script>

<?php if (!$isVerified): ?>
<!-- =========================
     VERIFY NOW MODAL
     - nutzt booster_update_booster_personals
     - gleiche Feldnamen wie personal-details.php
========================= -->
<form class="form ajax-form" action="<?= AJAX_URL ?>" method="POST" enctype="multipart/form-data">
  <input type="text" name="action" value="booster_update_booster_personals" hidden>
  <input type="text" name="id" value="<?= htmlspecialchars((string)$boosterId) ?>" hidden>

  <div id="verify-now-modal" class="modal fade" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable" style="max-width: 760px; width: calc(100% - 2rem);">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">
            <i class="fa-duotone fa-id-card me-2"></i> Verify your account
          </h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>

        <div class="modal-body">
          <div class="ov-muted mb-3">
            Fill in your personal details and upload the required photos. Your status updates to <b>Verified</b> only when everything is filled (not empty).
          </div>

          <div class="row g-3">
            <div class="col-12 col-md-6">
              <label class="form-label">Full Name</label>
              <input type="text" class="form-control" name="fullname"
                     value="<?= htmlspecialchars((string)($pd['fullname'] ?? '')) ?>"
                     placeholder="Full name" required>
            </div>

            <div class="col-12 col-md-6">
              <label class="form-label">Date of Birth</label>
              <!-- wie in personal-details.php: type=text -->
              <input type="text" class="form-control" name="dob"
                     value="<?= htmlspecialchars((string)($pd['dob'] ?? '')) ?>"
                     placeholder="DD-MM-YYYY" required>
            </div>

            <div class="col-12 col-md-6">
              <label class="form-label">Address</label>
              <input type="text" class="form-control" name="address"
                     value="<?= htmlspecialchars((string)($pd['address'] ?? '')) ?>"
                     placeholder="Address" required>
            </div>

            <div class="col-12 col-md-6">
              <label class="form-label">Country</label>
              <input type="text" class="form-control" name="country"
                     value="<?= htmlspecialchars((string)($pd['country'] ?? '')) ?>"
                     placeholder="Country" required>
            </div>

            <div class="col-12"><div class="ov-divider"></div></div>

            <!-- Uploads: wenn leer/whitespace -> required input -->
            <div class="col-12">
              <label class="form-label">Front ID Photo</label>
              <?php if (ov_is_blank($pd['id_front'] ?? null)): ?>
                <input type="file" name="id_front" class="form-control" accept="image/*" required>
              <?php else: ?>
                <div class="d-flex gap-2 flex-wrap">
                  <a href="<?= htmlspecialchars((string)$pd['id_front']) ?>" target="_blank" class="btn btn-outline-light">
                    View ID Front
                  </a>
                  <input type="file" name="id_front" class="form-control" accept="image/*" style="max-width:320px;">
                </div>
                <div class="ov-muted mt-1" style="font-size:.85rem;">Optional: upload a new file to replace it.</div>
              <?php endif; ?>
            </div>

            <div class="col-12">
              <label class="form-label">Back ID Photo</label>
              <?php if (ov_is_blank($pd['id_back'] ?? null)): ?>
                <input type="file" name="id_back" class="form-control" accept="image/*" required>
              <?php else: ?>
                <div class="d-flex gap-2 flex-wrap">
                  <a href="<?= htmlspecialchars((string)$pd['id_back']) ?>" target="_blank" class="btn btn-outline-light">
                    View ID Back
                  </a>
                  <input type="file" name="id_back" class="form-control" accept="image/*" style="max-width:320px;">
                </div>
                <div class="ov-muted mt-1" style="font-size:.85rem;">Optional: upload a new file to replace it.</div>
              <?php endif; ?>
            </div>

            <div class="col-12">
              <label class="form-label">Selfie</label>
              <?php if (ov_is_blank($pd['selfie'] ?? null)): ?>
                <input type="file" name="selfie" class="form-control" accept="image/*" required>
              <?php else: ?>
                <div class="d-flex gap-2 flex-wrap">
                  <a href="<?= htmlspecialchars((string)$pd['selfie']) ?>" target="_blank" class="btn btn-outline-light">
                    View Selfie
                  </a>
                  <input type="file" name="selfie" class="form-control" accept="image/*" style="max-width:320px;">
                </div>
                <div class="ov-muted mt-1" style="font-size:.85rem;">Optional: upload a new file to replace it.</div>
              <?php endif; ?>
            </div>

            <div class="col-12">
              <div class="ov-muted mt-2" style="font-size:.9rem;">
                <i class="fa-duotone fa-circle-info me-1"></i>
                Empty values (including blank strings) count as <b>not uploaded</b>.
              </div>
            </div>
          </div>
        </div>

        <div class="modal-footer">
          <button type="button" class="btn btn-outline-light" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary">
            <i class="fa-duotone fa-cloud-arrow-up me-1"></i> Submit verification
          </button>
        </div>
      </div>
    </div>
  </div>
</form>

<script>
  // Auto-open on every refresh when not verified
  document.addEventListener('DOMContentLoaded', function () {
    const el = document.getElementById('verify-now-modal');
    if (!el || !window.bootstrap) return;
    new bootstrap.Modal(el).show();
  });
</script>
<?php endif; ?>

<!-- Upload Icon Modal -->
<form class="ajax-form" action="<?= AJAX_URL ?>" method="post" enctype="multipart/form-data">
  <input type="hidden" name="action" value="booster_upload_profile_picture">

  <div id="upload-icon-modal" class="modal fade" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable ov-upload-modal">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Upload Icon</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>

        <div class="modal-body">
          <div class="d-flex align-items-center gap-3 mb-3">
            <img id="ovIconPreview"
                 src="<?= BOOSTER_DATA['icon'] ?>"
                 alt="Preview"
                 style="width:56px;height:56px;border-radius:50%;object-fit:cover;border:1px solid rgba(255,255,255,.15);">
            <div style="min-width:0;">
              <div style="font-weight:900;">Upload your file</div>
              <div class="ov-muted" style="font-size:.9rem;">PNG / JPG / WEBP - max 5MB</div>
            </div>
          </div>

          <label class="ov-dropzone" for="image_url_icon">
            <div class="ov-dropzone-title">Drag and drop or click to choose</div>
            <div class="ov-dropzone-hint">Recommended: square image</div>
          </label>

          <input class="form-control d-none" accept="image/*" type="file" name="image_url" id="image_url_icon">
        </div>

        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button id="ovIconSubmit" type="submit" class="btn btn-primary" disabled><i class="fa-solid fa-cloud-arrow-up me-1"></i>Upload &amp; Save</button>
        </div>
      </div>
    </div>
  </div>
</form>

<!-- Upload Cover Modal -->
<form class="ajax-form" action="<?= AJAX_URL ?>" method="post" enctype="multipart/form-data">
  <input type="hidden" name="action" value="booster_upload_cover">

  <div id="upload-cover-modal" class="modal fade" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable ov-upload-modal">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Change Cover</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>

        <div class="modal-body">
          <div class="mb-3">
            <div id="ovCoverPreviewStage" class="ov-cover-stage<?= !empty($bannerUrl) ? ' has-image' : '' ?>">
              <div id="ovCoverPreview"
                   class="ov-cover-preview<?= !empty($bannerUrl) ? ' is-visible' : '' ?>"
                   data-default-src="<?= htmlspecialchars($bannerUrl, ENT_QUOTES, 'UTF-8') ?>"
                   data-default-position="<?= htmlspecialchars($bannerPosition, ENT_QUOTES, 'UTF-8') ?>"
                   style="<?= !empty($bannerUrl) ? 'background-image:url(' . htmlspecialchars($bannerUrl, ENT_QUOTES, 'UTF-8') . ');background-position:' . htmlspecialchars($bannerPosition, ENT_QUOTES, 'UTF-8') . ';' : '' ?>"></div>
              <div id="ovCoverPreviewPlaceholder" class="ov-cover-placeholder<?= !empty($bannerUrl) ? ' d-none' : '' ?>">
                No cover uploaded yet
              </div>
            </div>
          </div>

          <div class="ov-cover-help mb-3">
            <i class="fa-solid fa-up-down-left-right"></i> Drag the image to adjust cover position, then save.
          </div>

          <div class="ov-cover-pos-row mb-3">
            <button type="button" class="btn btn-outline-light btn-sm" id="ovCoverChangeBtn">
              <i class="fa-solid fa-arrow-left me-1"></i>Change image
            </button>
            <div class="ov-cover-pos-readout" id="ovCoverPositionReadout"><?= htmlspecialchars($bannerPosition) ?></div>
          </div>

          <input type="hidden" name="cover_position" id="cover_position" value="<?= htmlspecialchars($bannerPosition, ENT_QUOTES, 'UTF-8') ?>">
          <input class="form-control d-none" accept="image/*" type="file" name="image_url" id="image_url_cover">
        </div>

        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button id="ovCoverSubmit" type="submit" class="btn btn-primary" disabled><i class="fa-solid fa-cloud-arrow-up me-1"></i>Upload &amp; Save</button>
        </div>
      </div>
    </div>
  </div>
</form>

<style>
  #upload-icon-modal .ov-upload-modal,
  #upload-cover-modal .ov-upload-modal{
    max-width: 440px;
    width: calc(100% - 2rem);
  }

  #upload-icon-modal .modal-body,
  #upload-cover-modal .modal-body,
  #verify-now-modal .modal-body{
    max-height: 70vh;
    overflow:auto;
  }

  #upload-icon-modal .ov-dropzone,
  #upload-cover-modal .ov-dropzone{
    display:block;
    border: 1px dashed rgba(255,255,255,.22);
    border-radius: 14px;
    padding: 18px;
    cursor:pointer;
    background: rgba(255,255,255,.03);
    transition: transform .08s ease, border-color .12s ease, background .12s ease;
    user-select:none;
    text-align:center;
  }

  #upload-icon-modal .ov-dropzone:hover,
  #upload-cover-modal .ov-dropzone:hover{
    border-color: rgba(120,102,255,.65);
    background: rgba(120,102,255,.08);
    transform: translateY(-1px);
  }

  #upload-icon-modal .ov-dropzone-title,
  #upload-cover-modal .ov-dropzone-title{ font-weight: 950; }

  #upload-icon-modal .ov-dropzone-hint,
  #upload-cover-modal .ov-dropzone-hint{ opacity:.7; font-size:.9rem; margin-top:4px; }

  #upload-cover-modal .ov-cover-stage{
    position:relative;
    width:100%;
    aspect-ratio:4/1;
    border-radius:12px;
    overflow:hidden;
    border:1px solid rgba(255,255,255,.12);
    background:rgba(255,255,255,.03);
  }

  #upload-cover-modal .ov-cover-preview{
    position:absolute;
    inset:0;
    display:none;
    background-repeat:no-repeat;
    background-size:cover;
    background-position:50% 50%;
    cursor:grab;
    touch-action:none;
  }

  #upload-cover-modal .ov-cover-preview.is-visible{ display:block; }
  #upload-cover-modal .ov-cover-preview.is-dragging{ cursor:grabbing; }

  #upload-cover-modal .ov-cover-placeholder{
    position:absolute;
    inset:0;
    display:flex;
    align-items:center;
    justify-content:center;
    color:rgba(255,255,255,.45);
    font-weight:700;
    border:1px dashed rgba(255,255,255,.18);
    border-radius:12px;
  }

  #upload-cover-modal .ov-cover-help{
    color:rgba(255,255,255,.72);
    font-size:.92rem;
    display:flex;
    align-items:center;
    gap:.55rem;
  }

  #upload-cover-modal .ov-cover-pos-row{
    display:flex;
    align-items:center;
    justify-content:space-between;
    gap:1rem;
  }

  #upload-cover-modal .ov-cover-pos-readout{
    color:rgba(255,255,255,.62);
    font-size:.9rem;
    white-space:nowrap;
  }
</style>

<script>
(function () {
  var hasFA = !!document.querySelector('link[href*="font-awesome"], link[href*="fontawesome"], link[href*="all.min.css"], link[href*="fontawesome-free"]');
  if (!hasFA) {
    var l = document.createElement('link');
    l.rel = 'stylesheet';
    l.href = 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css';
    l.crossOrigin = 'anonymous';
    if (document.head) document.head.appendChild(l);
  }
})();
</script>

<script>
(function(){
  function bindUploadPreview(config){
    const input = document.getElementById(config.inputId);
    const preview = document.getElementById(config.previewId);
    const submit = document.getElementById(config.submitId);
    const dz = document.querySelector(config.modalSelector + ' .ov-dropzone');
    const modal = document.querySelector(config.modalSelector);
    const placeholder = config.placeholderId ? document.getElementById(config.placeholderId) : null;
    const defaultSrc = config.defaultSrc || '';

    if(!input || !preview || !submit || !dz || !modal) return;

    function setFile(file){
      if(!file || !file.type || !file.type.startsWith('image/')) return;
      const dt = new DataTransfer();
      dt.items.add(file);
      input.files = dt.files;

      const url = URL.createObjectURL(file);
      preview.src = url;
      preview.style.display = '';
      if (placeholder) placeholder.style.display = 'none';
      submit.disabled = false;
    }

    input.addEventListener('change', function(){
      submit.disabled = !(input.files && input.files.length);
      if(input.files && input.files[0]) setFile(input.files[0]);
    });

    dz.addEventListener('dragover', function(e){ e.preventDefault(); dz.style.borderColor = 'rgba(120,102,255,.9)'; });
    dz.addEventListener('dragleave', function(){ dz.style.borderColor = 'rgba(255,255,255,.22)'; });
    dz.addEventListener('drop', function(e){
      e.preventDefault();
      dz.style.borderColor = 'rgba(255,255,255,.22)';
      const f = e.dataTransfer.files && e.dataTransfer.files[0];
      if(f) setFile(f);
    });

    modal.addEventListener('hidden.bs.modal', function(){
      input.value = '';
      submit.disabled = true;
      preview.src = defaultSrc;
      if (defaultSrc) {
        preview.style.display = '';
      } else {
        preview.style.display = 'none';
        if (placeholder) placeholder.style.display = 'flex';
      }
    });
  }

  bindUploadPreview({
    modalSelector: '#upload-icon-modal',
    inputId: 'image_url_icon',
    previewId: 'ovIconPreview',
    submitId: 'ovIconSubmit',
    defaultSrc: <?= json_encode((string)(BOOSTER_DATA['icon'] ?? '')) ?>
  });

  function bindCoverUpload(){
    const modal = document.getElementById('upload-cover-modal');
    const input = document.getElementById('image_url_cover');
    const preview = document.getElementById('ovCoverPreview');
    const placeholder = document.getElementById('ovCoverPreviewPlaceholder');
    const submit = document.getElementById('ovCoverSubmit');
    const hidden = document.getElementById('cover_position');
    const readout = document.getElementById('ovCoverPositionReadout');
    const changeBtn = document.getElementById('ovCoverChangeBtn');
    if(!modal || !input || !preview || !submit || !hidden || !readout || !changeBtn) return;

    const defaultSrc = preview.getAttribute('data-default-src') || '';
    const defaultPosition = preview.getAttribute('data-default-position') || '50% 50%';
    let posX = 50;
    let posY = 50;
    let drag = null;
    let initialPosition = defaultPosition;

    function clamp(v, min, max){ return Math.max(min, Math.min(max, v)); }
    function parsePosition(value){
      const m = String(value || '').match(/(-?\d+(?:\.\d+)?)%\s+(-?\d+(?:\.\d+)?)%/);
      if(!m) return { x: 50, y: 50 };
      return { x: clamp(parseFloat(m[1]) || 50, 0, 100), y: clamp(parseFloat(m[2]) || 50, 0, 100) };
    }
    function positionValue(){ return posX.toFixed(0) + '% ' + posY.toFixed(0) + '%'; }
    function syncPosition(){
      const value = positionValue();
      preview.style.backgroundPosition = value;
      hidden.value = value;
      readout.textContent = value;
      submit.disabled = !input.files.length && !defaultSrc ? true : (value === initialPosition && !input.files.length ? false : false);
    }
    function showPreview(src){
      preview.style.backgroundImage = src ? 'url("' + src.replace(/"/g, '\\"') + '")' : '';
      preview.classList.toggle('is-visible', !!src);
      placeholder.classList.toggle('d-none', !!src);
    }
    function setFile(file){
      if(!file || !file.type || !file.type.startsWith('image/')) return;
      const dt = new DataTransfer();
      dt.items.add(file);
      input.files = dt.files;
      showPreview(URL.createObjectURL(file));
      submit.disabled = false;
    }
    function resetState(){
      input.value = '';
      submit.disabled = !defaultSrc;
      const parsed = parsePosition(defaultPosition);
      posX = parsed.x;
      posY = parsed.y;
      syncPosition();
      initialPosition = hidden.value;
      showPreview(defaultSrc);
    }

    input.addEventListener('change', function(){
      if(input.files && input.files[0]) setFile(input.files[0]);
    });

    changeBtn.addEventListener('click', function(){
      input.click();
    });

    preview.addEventListener('pointerdown', function(e){
      if(!preview.classList.contains('is-visible')) return;
      drag = { startX: e.clientX, startY: e.clientY, x: posX, y: posY, w: Math.max(preview.clientWidth, 1), h: Math.max(preview.clientHeight, 1) };
      preview.classList.add('is-dragging');
      if (preview.setPointerCapture) preview.setPointerCapture(e.pointerId);
    });

    preview.addEventListener('pointermove', function(e){
      if(!drag) return;
      const deltaX = ((e.clientX - drag.startX) / drag.w) * 100;
      const deltaY = ((e.clientY - drag.startY) / drag.h) * 100;
      posX = clamp(drag.x + deltaX, 0, 100);
      posY = clamp(drag.y + deltaY, 0, 100);
      syncPosition();
      submit.disabled = false;
    });

    function stopDrag(e){
      if(!drag) return;
      drag = null;
      preview.classList.remove('is-dragging');
      if (preview.releasePointerCapture) { try { preview.releasePointerCapture(e.pointerId); } catch (err) {} }
    }

    preview.addEventListener('pointerup', stopDrag);
    preview.addEventListener('pointercancel', stopDrag);
    preview.addEventListener('lostpointercapture', function(){ drag = null; preview.classList.remove('is-dragging'); });

    modal.addEventListener('hidden.bs.modal', resetState);
    resetState();
  }

  bindCoverUpload();
})();
</script>

<style>
/* ===== Booster Overview v4 (scoped) ===== */
.booster-overview-v4{
  --ov-text: rgba(255,255,255,.92);
  --ov-muted: rgba(255,255,255,.62);
}
.booster-overview-v4 .ov-title{ font-weight: 850; letter-spacing: .2px; margin: 0; color: var(--ov-text); }
.booster-overview-v4 .ov-muted{ color: var(--ov-muted) !important; }
.booster-overview-v4 .ov-link{ color: rgba(255,255,255,.82); text-decoration:none; font-weight:800; }
.booster-overview-v4 .ov-link:hover{ color:#fff; }

.booster-overview-v4 .ov-divider{ height:1px; background: rgba(255,255,255,.06); margin:14px 0; }

.booster-overview-v4 .ov-avatar{
  width:34px; height:34px; border-radius:50%;
  margin-left:8px; border:1px solid rgba(255,255,255,.12);
}

/* großer Avatar klickbar */
.booster-overview-v4 .ov-profile-avatar-wrap{ cursor:pointer; }
.booster-overview-v4 .ov-profile-avatar-wrap .avatar-img{
  border-radius: 999px;
  object-fit: cover;
  transition: transform .12s ease, box-shadow .12s ease;
}
.booster-overview-v4 .ov-profile-avatar-wrap:hover .avatar-img{
  transform: translateY(-1px);
  box-shadow: 0 14px 28px rgba(0,0,0,.25);
}
.booster-overview-v4 .ov-profile-avatar-pen{
  position:absolute;
  bottom: 2px;
  right: 2px;
  width: 26px;
  height: 26px;
  border-radius: 50%;
  display:flex;
  align-items:center;
  justify-content:center;
  background-color: #35383a;
  border: 1px solid rgba(255,255,255,.20);
  color: #fff;
  pointer-events: none;
}

/* Abstände zwischen den großen Bereichen */
.booster-overview-v4 .ov-section-gap{ margin-top: 22px; }
@media (max-width: 992px){ .booster-overview-v4 .ov-section-gap{ margin-top: 18px; } }

/* Buttons */
.booster-overview-v4 .btn{ border-radius: 12px !important; font-weight: 900 !important; }
.booster-overview-v4 .btn-primary{ background:#5b4bff !important; border-color:#5b4bff !important; box-shadow: 0 14px 32px rgba(91,75,255,.20); }
.booster-overview-v4 .btn-outline-light{
  color: rgba(255,255,255,.92) !important;
  background: rgba(255,255,255,.04) !important;
  border-color: rgba(255,255,255,.18) !important;
}
.booster-overview-v4 .btn-outline-light:hover{
  background: rgba(255,255,255,.08) !important;
  border-color: rgba(109,92,255,.45) !important;
  box-shadow: 0 14px 30px rgba(109,92,255,.14);
  transform: translateY(-1px);
}

/* Chips */
.booster-overview-v4 .ov-chip{
  display:inline-flex; align-items:center; gap:8px;
  padding: 7px 10px;
  border-radius: 999px;
  background: rgba(255,255,255,.04);
  border: 1px solid rgba(255,255,255,.10);
  color: var(--ov-text);
  font-weight: 900;
  font-size: .85rem;
}
.booster-overview-v4 .ov-chip.ov-chip-accent{
  background: linear-gradient(135deg, rgba(109,92,255,.22), rgba(176,92,255,.14));
  border-color: rgba(109,92,255,.35);
  box-shadow: 0 10px 26px rgba(109,92,255,.14);
}
.booster-overview-v4 .ov-chip.ov-chip-danger{
  background: rgba(255, 82, 163, .10);
  border-color: rgba(255, 82, 163, .28);
  box-shadow: 0 10px 24px rgba(255, 82, 163, .10);
}

/* Stat Card */
.booster-overview-v4 .ov-stat{
  background: rgba(255,255,255,.02);
  border: 1px solid rgba(255,255,255,.08);
  border-radius: 16px;
  padding: 12px;
  display:flex;
  align-items:center;
  justify-content:space-between;
  gap: 10px;
  min-height: 72px;
}
.booster-overview-v4 .ov-stat:hover{ border-color: rgba(109,92,255,.35); box-shadow: 0 14px 28px rgba(109,92,255,.08); }
.booster-overview-v4 .ov-stat .left{ display:flex; align-items:center; gap:10px; min-width:0; }
.booster-overview-v4 .ov-stat .left i{
  width:34px; height:34px; border-radius:12px;
  display:flex; align-items:center; justify-content:center;
  background: linear-gradient(135deg, rgba(109,92,255,.28), rgba(176,92,255,.16));
  border: 1px solid rgba(109,92,255,.25);
  box-shadow: 0 10px 22px rgba(109,92,255,.10);
  color: rgba(255,255,255,.92);
}
.booster-overview-v4 .ov-stat .label{ font-weight: 900; color: var(--ov-text); font-size: .95rem; }
.booster-overview-v4 .ov-stat .hint{ font-size: .85rem; color: var(--ov-muted); }
.booster-overview-v4 .ov-stat .value{ font-weight: 950; color: var(--ov-text); }

/* Mini grid */
.booster-overview-v4 .ov-mini-grid{ display:grid; grid-template-columns: 1fr 1fr; gap:10px; }
.booster-overview-v4 .ov-mini{
  background: rgba(255,255,255,.02);
  border: 1px solid rgba(255,255,255,.08);
  border-radius: 16px;
  padding: 10px;
  display:flex;
  align-items:center;
  justify-content:space-between;
  gap: 10px;
}
.booster-overview-v4 .ov-mini .k{ color: var(--ov-muted); font-weight: 850; font-size: .85rem; display:flex; align-items:center; gap:8px; }
.booster-overview-v4 .ov-mini .k i{
  width:30px; height:30px; border-radius:12px;
  display:flex; align-items:center; justify-content:center;
  background: linear-gradient(135deg, rgba(109,92,255,.24), rgba(176,92,255,.14));
  border: 1px solid rgba(109,92,255,.25);
  box-shadow: 0 10px 20px rgba(109,92,255,.08);
  color: rgba(255,255,255,.9);
}
.booster-overview-v4 .ov-mini .v{ color: var(--ov-text); font-weight: 950; font-size: .95rem; white-space: nowrap; }
@media (max-width: 768px){
  .booster-overview-v4 .profile-mini{ flex-direction: column; align-items:flex-start; }
  .booster-overview-v4 .ov-mini-grid{ grid-template-columns: 1fr; }
}

/* Tiles */
.booster-overview-v4 .ov-tile-link{ text-decoration:none; }
.booster-overview-v4 .ov-tile{
  background: rgba(255,255,255,.02);
  border: 1px solid rgba(255,255,255,.08);
  border-radius: 16px;
  padding: 14px;
  display:flex;
  align-items:center;
  justify-content:space-between;
  gap: 12px;
  min-height: 76px;
}
.booster-overview-v4 .ov-tile:hover{ border-color: rgba(109,92,255,.35); box-shadow: 0 14px 28px rgba(109,92,255,.08); }

/* Primary tile (Payout Request) */
.booster-overview-v4 .ov-tile.ov-tile-primary{
  background: linear-gradient(135deg, rgba(91,75,255,.20), rgba(176,92,255,.12));
  border-color: rgba(109,92,255,.35);
  box-shadow: 0 18px 42px rgba(91,75,255,.14);
}
.booster-overview-v4 .ov-tile.ov-tile-primary:hover{
  border-color: rgba(109,92,255,.55);
  box-shadow: 0 22px 54px rgba(91,75,255,.18);
}
.booster-overview-v4 .ov-tile.ov-tile-primary .left i{
  background: rgba(255,255,255,.06);
  border-color: rgba(255,255,255,.14);
}

.booster-overview-v4 .ov-tile .left{ display:flex; align-items:center; gap:10px; min-width:0; }
.booster-overview-v4 .ov-tile .left i{
  width:38px; height:38px; border-radius:14px;
  display:flex; align-items:center; justify-content:center;
  background: rgba(255,255,255,.04);
  border: 1px solid rgba(255,255,255,.10);
  color: rgba(255,255,255,.92);
}
.booster-overview-v4 .ov-tile .label{ font-weight: 950; color: var(--ov-text); }
.booster-overview-v4 .ov-tile .hint{ font-size: .85rem; color: var(--ov-muted); }
.booster-overview-v4 .ov-tile .value{ font-weight: 950; color: var(--ov-text); white-space: nowrap; }

/* Callout */
.booster-overview-v4 .ov-callout{
  display:flex; align-items:center; justify-content:space-between; gap:12px;
  padding: 14px;
  border-radius: 16px;
  background: rgba(255,255,255,.02);
  border: 1px solid rgba(255,255,255,.08);
}
.booster-overview-v4 .ov-callout:hover{ border-color: rgba(109,92,255,.35); box-shadow: 0 14px 28px rgba(109,92,255,.06); }
.booster-overview-v4 .ov-callout-icon{
  width:38px; height:38px; border-radius:14px;
  display:flex; align-items:center; justify-content:center;
  background: linear-gradient(135deg, rgba(109,92,255,.20), rgba(176,92,255,.12));
  border: 1px solid rgba(109,92,255,.25);
  color: rgba(255,255,255,.92);
}
.booster-overview-v4 .ov-callout-title{ font-weight: 950; color: var(--ov-text); }
.booster-overview-v4 .ov-callout-btn{ border-radius:12px !important; font-weight:950 !important; white-space:nowrap; }
.booster-overview-v4 .ov-callout-compact{ padding: 12px; }

/* Admins */
.booster-overview-v4 .ov-admin-list{ display:flex; flex-direction:column; gap:10px; }
.booster-overview-v4 .ov-admin-row{
  background: rgba(255,255,255,.02);
  border: 1px solid rgba(255,255,255,.06);
  border-radius: 16px;
  padding: 12px 12px;
  display:flex;
  align-items:center;
  justify-content:space-between;
  gap: 12px;
}
.booster-overview-v4 .ov-admin-row:hover{
  background: rgba(255,255,255,.035);
  border-color: rgba(109,92,255,.25);
  box-shadow: 0 12px 26px rgba(109,92,255,.06);
}
.booster-overview-v4 .ov-admin-left{ display:flex; align-items:center; gap:12px; min-width:0; }
.booster-overview-v4 .ov-admin-avatar{
  width:44px; height:44px;
  border-radius: 14px;
  border: 1px solid rgba(255,255,255,.12);
  object-fit: cover;
}
.booster-overview-v4 .ov-admin-meta{ min-width:0; }
.booster-overview-v4 .ov-admin-lines{ display:flex; flex-direction:column; gap:8px; }
.booster-overview-v4 .ov-admin-line{ display:flex; align-items:center; gap:8px; }
.booster-overview-v4 .ov-admin-sub{ font-size:.85rem; }

.booster-overview-v4 .ov-admin-right{ display:flex; align-items:center; gap:10px; flex-shrink:0; }
.booster-overview-v4 .ov-admin-badge{
  padding: 6px 10px;
  border-radius: 999px;
  font-weight: 950;
  font-size: .8rem;
  color: rgba(255,255,255,.92);
  border: 1px solid rgba(255,255,255,.10);
  background: rgba(255,255,255,.03);
  white-space: nowrap;
}

.ov-admin-badge i{margin-right:8px; width:14px; text-align:center; display:inline-block; opacity:.95;}
.booster-overview-v4 .ov-admin-status{
  padding: 6px 10px;
  border-radius: 999px;
  font-weight: 950;
  font-size: .8rem;
  border: 1px solid rgba(255,255,255,.10);
  background: rgba(255,255,255,.03);
  color: rgba(255,255,255,.85);
  white-space: nowrap;
  display:inline-flex;
  align-items:center;
  gap: 8px;
}
.booster-overview-v4 .ov-admin-status::before{
  content:"";
  width: 8px;
  height: 8px;
  border-radius: 999px;
  display:inline-block;
}
.booster-overview-v4 .ov-admin-status.is-online::before{
  background: #5dcd82;
  box-shadow: 0 0 0 0 rgba(93,205,130,.55);
  animation: ovOnlinePulse 1.4s ease-out infinite;
}
.booster-overview-v4 .ov-admin-status.is-offline::before{
  background: rgba(255,255,255,.28);
}

@keyframes ovOnlinePulse{
  0%{ box-shadow: 0 0 0 0 rgba(93,205,130,.55); }
  70%{ box-shadow: 0 0 0 10px rgba(93,205,130,0); }
  100%{ box-shadow: 0 0 0 0 rgba(93,205,130,0); }
}

.booster-overview-v4 .ov-admin-action{
  height: 36px;
  padding: 0 12px;
  display:flex; align-items:center; justify-content:center;
  gap: 8px;
  border-radius: 12px;
  border: 1px solid rgba(109,92,255,.25);
  background: rgba(109,92,255,.12);
  color: rgba(255,255,255,.92);
  text-decoration:none;
  font-weight: 950;
  letter-spacing: .02em;
}
.booster-overview-v4 .ov-admin-action:hover{ background: rgba(109,92,255,.18); border-color: rgba(109,92,255,.40); }

/* Auf sehr kleinen Screens Text ausblenden */
@media (max-width: 420px){
  .booster-overview-v4 .ov-admin-action span{ display:none; }
  .booster-overview-v4 .ov-admin-action{ padding: 0 10px; }
}

/* Namen lesbar */
.booster-overview-v4 .ov-admin-name{
  color: rgba(255,255,255,.92) !important;
  background: none !important;
  -webkit-background-clip: initial !important;
  -webkit-text-fill-color: rgba(255,255,255,.92) !important;
  animation: none !important;
  font-weight: 950;
  font-size: 1rem;
  line-height: 1.1;
  overflow:hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

/* Rollen-Akzent (statt Dot neben Name, damit es nicht wie Online-Status wirkt) */
.booster-overview-v4 .ov-admin-row{ border-left-width: 3px; border-left-style: solid; border-left-color: rgba(255,255,255,.06); }
.booster-overview-v4 .ov-admin-row.ov-role-1{ border-left-color: #ef0405; }
.booster-overview-v4 .ov-admin-row.ov-role-2{ border-left-color: #ef0405; }
.booster-overview-v4 .ov-admin-row.ov-role-3{ border-left-color: #f5c84c; }
.booster-overview-v4 .ov-admin-row.ov-role-4{ border-left-color: #5dcd82; }

/* Profile */
.booster-overview-v4 .profile-mini{ display:flex; align-items:center; justify-content:space-between; gap:16px; }
.booster-overview-v4 .profile-mini .left{ display:flex; align-items:center; gap:14px; min-width:0; }
.booster-overview-v4 .profile-mini .name{ font-size:1.2rem; font-weight:950; color: var(--ov-text); line-height:1.1; margin:0; }
.booster-overview-v4 .profile-mini .email{ margin:4px 0 0 0; }

/* Cards: Standard Dashboard Background beibehalten */
.booster-overview-v4 .card{
  background: var(--bs-card-bg) !important;
  border: var(--bs-card-border-width) solid var(--bs-card-border-color) !important;
  border-radius: var(--bs-card-border-radius) !important;
  box-shadow: none !important;
  transform: none !important;
}
.booster-overview-v4 .card::before{ display:none !important; content:none !important; }
.booster-overview-v4 .card:hover{ box-shadow:none !important; transform:none !important; }

/* Polish: hover + KPI emphasis */
.ov-stat, .ov-tile, .ov-admin-row { transition: transform .18s ease, box-shadow .18s ease, border-color .18s ease, opacity .18s ease; }
.ov-stat:hover, .ov-tile-link:hover .ov-tile, .ov-admin-row:hover { transform: translateY(-2px); }

/* KPI tints */
.ov-stat-kpi.kpi-primary { border-color: rgba(120, 92, 255, .35); box-shadow: 0 0 0 1px rgba(120,92,255,.18) inset; }
.ov-stat-kpi.kpi-danger  { border-color: rgba(255, 92, 92, .30); box-shadow: 0 0 0 1px rgba(255,92,92,.14) inset; }
.ov-stat-kpi.kpi-success { border-color: rgba(74, 222, 128, .26); box-shadow: 0 0 0 1px rgba(74,222,128,.12) inset; }

/* Offline admin dim */
.ov-admin-row.is-offline-row { opacity: .68; }

/* Disable discord action */
.ov-admin-action.is-disabled { opacity: .55; cursor: not-allowed; pointer-events: none; }

/* Subtle pulse for online discord action */
.ov-admin-action-online { position: relative; }
.ov-admin-action-online::after {
  content: "";
  position: absolute;
  inset: -2px;
  border-radius: 999px;
  opacity: 0;
  animation: ovDiscordPulse 2.2s ease-in-out infinite;
  pointer-events: none;
}
@keyframes ovDiscordPulse {
  0%   { opacity: 0; transform: scale(.98); box-shadow: 0 0 0 0 rgba(120, 92, 255, .0); }
  35%  { opacity: .22; transform: scale(1.02); box-shadow: 0 0 0 10px rgba(120, 92, 255, .08); }
  100% { opacity: 0; transform: scale(1.06); box-shadow: 0 0 0 18px rgba(120, 92, 255, 0); }
}

</style>
