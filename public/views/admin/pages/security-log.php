<?= $this->layout('admin/layouts/main', ['meta' => $meta]) ?>

<?= $this->start('styles') ?>
<style>
/* ── Design tokens (matching reviews.php) ─────────────────────────────────── */
/* bg: #1e2022 | card: #25282a | border: rgba(255,255,255,.07) | text: rgba(255,255,255,.8) */
/* accent: #6d5cff | danger: #ed4c78 | amber: #f5ca99 | teal: #00c9a7 */

/* ── Hero card ── */
.sl-hero {
  border-radius: 20px;
  border: 1px solid rgba(255,255,255,.07);
  background: #25282a;
  padding: 28px 32px;
  display: flex; align-items: center; justify-content: space-between;
  flex-wrap: wrap; gap: 14px;
  margin-bottom: 14px;
  box-shadow: 0 2px 20px rgba(0,0,0,.22);
}
.sl-hero-left  { display: flex; align-items: center; gap: 18px; }
.sl-hero-icon  {
  width: 52px; height: 52px; border-radius: 15px;
  background: linear-gradient(135deg, rgba(237,76,120,.22), rgba(237,76,120,.10));
  border: 1px solid rgba(237,76,120,.28);
  display: flex; align-items: center; justify-content: center;
  font-size: 1.3rem; color: #f4829f; flex-shrink: 0;
}
.sl-hero-title { font-size: 1.2rem; font-weight: 950; color: rgba(255,255,255,.92); margin: 0; }
.sl-hero-sub   { font-size: .82rem; color: rgba(255,255,255,.4); margin: 5px 0 0; }
.sl-stat-row   { display: flex; gap: 10px; flex-wrap: wrap; }
.sl-stat {
  display: inline-flex; align-items: center; gap: 7px;
  padding: 7px 16px; border-radius: 12px;
  border: 1px solid rgba(255,255,255,.07);
  background: rgba(255,255,255,.03);
  font-size: .82rem; color: rgba(255,255,255,.45);
}
.sl-stat strong { color: rgba(255,255,255,.85); font-weight: 800; }
.sl-stat.danger { border-color: rgba(237,76,120,.28); background: rgba(237,76,120,.09); color: #f4829f; }
.sl-stat.danger strong { color: #f4829f; }

/* ── Toolbar ── */
.sl-toolbar {
  border-radius: 16px;
  border: 1px solid rgba(255,255,255,.07);
  background: #25282a;
  padding: 12px 16px;
  display: flex; align-items: center; justify-content: space-between;
  flex-wrap: wrap; gap: 10px;
  margin-bottom: 8px;
  box-shadow: 0 2px 16px rgba(0,0,0,.18);
}

/* ── Pills (source filter) ── */
.sl-pills { display: flex; gap: 6px; flex-wrap: wrap; }
.sl-pill {
  display: inline-flex; align-items: center; gap: .3rem;
  padding: 5px 14px; border-radius: 99px;
  font-size: .78rem; font-weight: 800; cursor: pointer;
  border: 1px solid rgba(255,255,255,.09);
  background: rgba(255,255,255,.04);
  color: rgba(255,255,255,.6);
  transition: background .12s, border-color .12s, color .12s;
  user-select: none; text-decoration: none;
}
.sl-pill:hover { background: rgba(255,255,255,.08); color: rgba(255,255,255,.85); }
.sl-pill.active { background: rgba(109,92,255,.18); border-color: rgba(109,92,255,.45); color: #c4b5fd; }
.sl-pill[data-source=""].active { background: rgba(255,255,255,.08); border-color: rgba(255,255,255,.18); color: rgba(255,255,255,.85); }
.sl-pill[data-source="history"].active { background: rgba(0,201,167,.12); border-color: rgba(0,201,167,.28); color: #00c9a7; }
.sl-pill[data-source="log"].active     { background: rgba(109,92,255,.14); border-color: rgba(109,92,255,.32); color: #c4b5fd; }
.sl-pill-flag.active { background: rgba(237,76,120,.14); border-color: rgba(237,76,120,.32); color: #f4829f; }

/* ── Search ── */
.sl-search-wrap { position: relative; }
.sl-search-wrap input {
  background: rgba(255,255,255,.04) !important;
  border: 1px solid rgba(255,255,255,.09) !important;
  border-radius: 10px !important;
  color: rgba(255,255,255,.85) !important;
  padding: 7px 12px 7px 34px !important;
  font-size: .84rem !important;
  width: 240px;
  transition: border-color .15s, box-shadow .15s;
}
.sl-search-wrap input:focus {
  border-color: rgba(109,92,255,.45) !important;
  box-shadow: 0 0 0 3px rgba(109,92,255,.10) !important;
  outline: none !important;
}
.sl-search-wrap input::placeholder { color: rgba(255,255,255,.25) !important; }
.sl-search-icon {
  position: absolute; left: 10px; top: 50%; transform: translateY(-50%);
  color: rgba(255,255,255,.35); font-size: .8rem; pointer-events: none;
}

/* ── Table ── */
.sl-table-wrap {
  border: 1px solid rgba(255,255,255,.07);
  border-radius: 20px;
  overflow: hidden;
  background: #25282a;
  box-shadow: 0 4px 32px rgba(0,0,0,.28);
}
.sl-table { width: 100%; border-collapse: collapse; }
.sl-table thead tr { background: rgba(255,255,255,.03); border-bottom: 1px solid rgba(255,255,255,.06); }
.sl-table thead th {
  padding: 11px 14px;
  font-size: .68rem; font-weight: 900; color: rgba(255,255,255,.35);
  text-transform: uppercase; letter-spacing: .07em; white-space: nowrap; user-select: none;
}
.sl-table tbody tr { border-bottom: 1px solid rgba(255,255,255,.04); transition: background .1s; }
.sl-table tbody tr:last-child { border-bottom: none; }
.sl-table tbody tr:hover { background: rgba(255,255,255,.025); }
.sl-table tbody tr.sl-flagged { background: rgba(237,76,120,.05); }
.sl-table tbody tr.sl-flagged:hover { background: rgba(237,76,120,.09) !important; }
.sl-table tbody td { padding: 10px 14px; vertical-align: middle; font-size: .84rem; color: rgba(255,255,255,.8); }

/* ── IP badges ── */
.sl-ip {
  font-family: 'Courier New', monospace;
  font-size: .78rem;
  padding: .2rem .55rem;
  border-radius: 7px;
  background: rgba(255,255,255,.05);
  border: 1px solid rgba(255,255,255,.09);
  display: inline-block;
  max-width: 220px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;
  vertical-align: middle;
}
.sl-ip.flagged  { background: rgba(237,76,120,.12); border-color: rgba(237,76,120,.3); color: #f4829f; }
.sl-ip.prev     { background: transparent; border-color: rgba(255,255,255,.06); color: rgba(255,255,255,.3); text-decoration: line-through; }

/* ── Source badge ── */
.sl-src-badge {
  display: inline-flex; align-items: center; gap: .3rem;
  padding: .2rem .55rem; border-radius: 99px;
  font-size: .7rem; font-weight: 800; white-space: nowrap;
}
.sl-src-history { background: rgba(0,201,167,.10); border: 1px solid rgba(0,201,167,.25); color: #00c9a7; }
.sl-src-log     { background: rgba(109,92,255,.12); border: 1px solid rgba(109,92,255,.28); color: #c4b5fd; }

/* ── Time gap ── */
.sl-gap {
  display: inline-block; padding: .2rem .55rem; border-radius: 7px;
  font-size: .76rem; font-weight: 700;
  background: rgba(245,202,153,.10); border: 1px solid rgba(245,202,153,.22); color: #f5ca99;
}
.sl-gap.fast { background: rgba(237,76,120,.12); border-color: rgba(237,76,120,.28); color: #f4829f; }

/* ── Flag badge ── */
.sl-flag-badge {
  display: inline-flex; align-items: center; gap: .3rem;
  padding: .22rem .6rem; border-radius: 99px;
  font-size: .68rem; font-weight: 900; letter-spacing: .05em;
  background: rgba(237,76,120,.15); border: 1px solid rgba(237,76,120,.35); color: #f4829f;
  white-space: nowrap;
}
.sl-muted { color: rgba(255,255,255,.25); }

/* ── Booster link ── */
.sl-booster-link { color: #c4b5fd; text-decoration: none; font-weight: 700; font-size: .82rem; }
.sl-booster-link:hover { color: #fff; text-decoration: underline; }

/* ── Footer / pagination ── */
.sl-footer {
  display: flex; align-items: center; justify-content: space-between;
  flex-wrap: wrap; gap: 14px; padding: 16px 20px;
}
.sl-footer-left, .sl-footer-right { display: flex; align-items: center; gap: 12px; flex-wrap: wrap; }
.sl-page-summary, .sl-page-chip {
  display: inline-flex; align-items: center; gap: 8px;
  min-height: 40px; padding: 6px 12px; border-radius: 12px;
  border: 1px solid rgba(255,255,255,.08);
  background: rgba(255,255,255,.035);
}
.sl-page-summary { color: rgba(255,255,255,.8); font-size: .82rem; font-weight: 700; }
.sl-page-summary strong { color: rgba(255,255,255,.96); }
.sl-page-chip {
  color: #c4b5fd; font-size: .8rem; font-weight: 800;
  border-color: rgba(109,92,255,.22); background: rgba(109,92,255,.10);
}
.sl-page-chip strong { color: rgba(255,255,255,.96); }
.sl-pagination { display: flex; align-items: center; gap: 6px; flex-wrap: wrap; }
.sl-pg-btn {
  min-width: 38px; height: 38px; padding: 0 12px; border-radius: 12px;
  border: 1px solid rgba(255,255,255,.09); background: rgba(255,255,255,.04);
  color: rgba(255,255,255,.74); font-size: .82rem; font-weight: 800;
  display: inline-flex; align-items: center; justify-content: center; gap: 7px;
  cursor: pointer; transition: background .12s, border-color .12s, transform .12s; user-select: none;
}
.sl-pg-btn:hover:not(.sl-pg-disabled) { background: rgba(255,255,255,.09); border-color: rgba(255,255,255,.16); transform: translateY(-1px); }
.sl-pg-btn.sl-pg-active  { background: rgba(109,92,255,.22); border-color: rgba(109,92,255,.40); color: #c4b5fd; }
.sl-pg-btn.sl-pg-disabled { opacity: .35; cursor: not-allowed; pointer-events: none; }
.sl-pg-btn.sl-pg-nav     { padding-inline: 14px; background: rgba(255,255,255,.055); }
.sl-pg-btn.sl-pg-nav span { font-size: .76rem; letter-spacing: .03em; text-transform: uppercase; }
.sl-pg-ellipsis { color: rgba(255,255,255,.3); font-size: .85rem; padding: 0 2px; line-height: 38px; user-select: none; }

/* ── Empty state ── */
.sl-empty { text-align: center; padding: 64px 24px; color: rgba(255,255,255,.35); }
.sl-empty i { font-size: 2.8rem; margin-bottom: 12px; display: block; opacity: .3; }
</style>
<?= $this->end() ?>

<?php
if (!function_exists('sl_fmt_diff')) {
  function sl_fmt_diff($secs) {
    if ($secs === null) return null;
    if ($secs < 60)   return $secs . 's';
    if ($secs < 3600) return floor($secs / 60) . 'm ' . ($secs % 60) . 's';
    $h = floor($secs / 3600); $m = floor(($secs % 3600) / 60);
    return $h . 'h ' . $m . 'm';
  }
}
if (!function_exists('sl_parse_device')) {
  function sl_parse_device($j) {
    $d = json_decode((string)$j, true);
    if (!is_array($d)) return ['os' => '', 'browser' => ''];
    return ['os' => trim((string)($d['os'] ?? '')), 'browser' => trim((string)($d['browser'] ?? ''))];
  }
}

$rows         = $rows         ?? [];
$totalRows    = $totalRows    ?? 0;
$flagCount    = $flagCount    ?? 0;
$page         = (int)($page   ?? 1);
$totalPages   = (int)($totalPages ?? 1);
$perPage      = (int)($perPage ?? 50);
$search       = $search       ?? '';
$onlyFlags    = $onlyFlags    ?? false;
$sourceFilter = $sourceFilter ?? '';
$boosterNames = $boosterNames ?? [];

if (!function_exists('sl_url')) {
  function sl_url($overrides = []) {
    $p = [];
    if (!empty($_GET['booster']))      $p['booster']      = $_GET['booster'];
    if (!empty($_GET['only_flagged'])) $p['only_flagged'] = '1';
    // 'history' is default — only add to URL if not history
    $src = $_GET['source'] ?? 'history';
    if ($src !== 'history') $p['source'] = $src;
    foreach ($overrides as $k => $v) {
      if ($v === null || $v === '') unset($p[$k]);
      else $p[$k] = $v;
    }
    // Don't add source=history to URL (it's the default)
    if (isset($p['source']) && $p['source'] === 'history') unset($p['source']);
    $qs = http_build_query($p);
    return ADMN_URL . '/security-log' . ($qs ? '?' . $qs : '');
  }
}
?>

<!-- ── Hero ─────────────────────────────────────────────────── -->
<div class="sl-hero">
  <div class="sl-hero-left">
    <div class="sl-hero-icon">
      <i class="fa-duotone fa-shield-exclamation"></i>
    </div>
    <div>
      <p class="sl-hero-title">Security Log</p>
      <p class="sl-hero-sub">
        Login history from <code>booster_sessions_history</code> +
        <code>booster_session_logs</code> — IP changes highlighted automatically.
      </p>
    </div>
  </div>
  <div class="sl-stat-row">
    <div class="sl-stat danger">
      <i class="fa-solid fa-triangle-exclamation"></i>
      <strong><?= number_format($flagCount) ?></strong>
      IP change<?= $flagCount !== 1 ? 's' : '' ?>
    </div>
    <div class="sl-stat">
      <i class="fa-regular fa-database"></i>
      <strong><?= number_format($totalRows) ?></strong>
      record<?= $totalRows !== 1 ? 's' : '' ?>
    </div>
  </div>
</div>

<!-- ── Toolbar ───────────────────────────────────────────────── -->
<div class="sl-toolbar">
  <!-- Source pills (server-side links) -->
  <div class="sl-pills">
    <a href="<?= htmlspecialchars(sl_url(['source' => 'history', 'page' => null])) ?>"
       class="sl-pill <?= $sourceFilter === 'history' ? 'active' : '' ?>" data-source="history">
      <i class="fa-regular fa-clock-rotate-left"></i> Login History
    </a>
    <a href="<?= htmlspecialchars(sl_url(['source' => 'log', 'page' => null])) ?>"
       class="sl-pill <?= $sourceFilter === 'log' ? 'active' : '' ?>" data-source="log">
      <i class="fa-regular fa-wave-pulse"></i> Session Logs
    </a>
    <a href="<?= htmlspecialchars(sl_url(['source' => 'all', 'page' => null])) ?>"
       class="sl-pill <?= $sourceFilter === 'all' ? 'active' : '' ?>" data-source="all">
      <i class="fa-regular fa-layer-group"></i> All sources
    </a>
    <a href="<?= htmlspecialchars(sl_url(['only_flagged' => $onlyFlags ? null : '1', 'page' => null])) ?>"
       class="sl-pill sl-pill-flag <?= $onlyFlags ? 'active' : '' ?>">
      <i class="fa-solid fa-triangle-exclamation"></i> IP changes only
    </a>
  </div>

  <!-- Search (debounced, submits via URL) -->
  <form method="GET" action="<?= htmlspecialchars(ADMN_URL . '/security-log') ?>" id="slSearchForm" style="margin:0;">
    <?php if ($onlyFlags):    ?><input type="hidden" name="only_flagged" value="1"><?php endif; ?>
    <?php if ($sourceFilter): ?><input type="hidden" name="source" value="<?= htmlspecialchars($sourceFilter) ?>"><?php endif; ?>
    <div class="sl-search-wrap">
      <i class="fa-regular fa-magnifying-glass sl-search-icon"></i>
      <input type="text"
             id="slSearchInput"
             name="booster"
             value="<?= htmlspecialchars($search) ?>"
             placeholder="Search booster name…"
             list="sl-blist"
             autocomplete="off">
      <datalist id="sl-blist">
        <?php foreach ($boosterNames as $bn): ?>
          <option value="<?= htmlspecialchars($bn['username']) ?>">
        <?php endforeach; ?>
      </datalist>
    </div>
  </form>
</div>

<!-- ── Table card ───────────────────────────────────────────── -->
<div class="sl-table-wrap">
  <?php if (!empty($rows)): ?>
  <table class="sl-table" id="slTable">
    <thead>
      <tr>
        <th style="width:36px;">#</th>
        <th>Booster</th>
        <th>Source</th>
        <th>IP Address</th>
        <th>Previous IP</th>
        <th>Time Gap</th>
        <th>Location</th>
        <th>Device</th>
        <th>Login Time</th>
        <th>Flag</th>
      </tr>
    </thead>
    <tbody>
      <?php
        $rowOffset = ($page - 1) * $perPage;
      ?>
      <?php foreach ($rows as $idx => $row): ?>
        <?php
          $ipChanged = (bool)($row['ip_changed'] ?? false);
          $device    = sl_parse_device($row['device_info'] ?? '');
          $loc       = implode(', ', array_filter([
            trim((string)($row['city']    ?? '')),
            trim((string)($row['country'] ?? '')),
          ]));
          $timeDiff  = $row['time_diff'] ?? null;
          $isFast    = $timeDiff !== null && $timeDiff < 3600;
          $source    = $row['source'] ?? 'history';
          $deviceStr = trim(($device['os'] ?: '') . ($device['os'] && $device['browser'] ? ' · ' : '') . ($device['browser'] ?: ''));
        ?>
        <tr class="<?= $ipChanged ? 'sl-flagged' : '' ?>"
            data-source="<?= htmlspecialchars($source) ?>"
            data-flagged="<?= $ipChanged ? '1' : '0' ?>">
          <td class="sl-muted" style="font-size:.74rem;"><?= $rowOffset + $idx + 1 ?></td>

          <td>
            <a href="<?= ADMN_URL ?>/booster/<?= (int)$row['booster_id'] ?>"
               class="sl-booster-link" target="_blank">
              <?= htmlspecialchars((string)($row['booster_username'] ?? '#' . $row['booster_id'])) ?>
            </a>
          </td>

          <td>
            <?php if ($source === 'history'): ?>
              <span class="sl-src-badge sl-src-history">
                <i class="fa-regular fa-clock-rotate-left" style="font-size:.65rem;"></i> History
              </span>
            <?php else: ?>
              <span class="sl-src-badge sl-src-log">
                <i class="fa-regular fa-wave-pulse" style="font-size:.65rem;"></i> Log
              </span>
            <?php endif; ?>
          </td>

          <td>
            <span class="sl-ip <?= $ipChanged ? 'flagged' : '' ?>" title="<?= htmlspecialchars($row['ip_address'] ?: '') ?>">
              <?= htmlspecialchars($row['ip_address'] ?: '—') ?>
            </span>
          </td>

          <td>
            <?php if ($ipChanged && ($row['prev_ip'] ?? '') !== ''): ?>
              <span class="sl-ip prev" title="<?= htmlspecialchars($row['prev_ip']) ?>">
                <?= htmlspecialchars($row['prev_ip']) ?>
              </span>
            <?php else: ?>
              <span class="sl-muted">—</span>
            <?php endif; ?>
          </td>

          <td>
            <?php $diffLabel = sl_fmt_diff($timeDiff); ?>
            <?php if ($diffLabel): ?>
              <span class="sl-gap <?= $isFast ? 'fast' : '' ?>"><?= $diffLabel ?></span>
            <?php else: ?>
              <span class="sl-muted">—</span>
            <?php endif; ?>
          </td>

          <td style="font-size:.82rem;">
            <?php if ($loc): ?>
              <i class="fa-regular fa-location-dot me-1" style="opacity:.4;"></i><?= htmlspecialchars($loc) ?>
            <?php else: ?>
              <span class="sl-muted">—</span>
            <?php endif; ?>
          </td>

          <td style="font-size:.8rem; color: rgba(255,255,255,.55);">
            <?= $deviceStr ? htmlspecialchars($deviceStr) : '<span class="sl-muted">—</span>' ?>
          </td>

          <td style="font-size:.79rem; white-space:nowrap; color: rgba(255,255,255,.45);">
            <?= htmlspecialchars((string)($row['created_at'] ?? '—')) ?>
          </td>

          <td>
            <?php if ($ipChanged): ?>
              <span class="sl-flag-badge">
                <i class="fa-solid fa-triangle-exclamation" style="font-size:.62rem;"></i> IP CHANGE
              </span>
            <?php else: ?>
              <span class="sl-muted">—</span>
            <?php endif; ?>
          </td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>

  <!-- Footer / Server-side pagination -->
  <?php if ($totalPages > 0): ?>
  <div class="sl-footer">
    <div class="sl-footer-left">
      <div class="sl-page-summary">
        <?php
          $start = ($page - 1) * $perPage + 1;
          $end   = min($page * $perPage, $totalRows);
        ?>
        Showing <strong><?= number_format($start) ?>–<?= number_format($end) ?></strong>
        of <strong><?= number_format($totalRows) ?></strong> records
      </div>
      <div class="sl-page-chip">
        Page <strong><?= $page ?> / <?= max($totalPages, 1) ?></strong>
      </div>
    </div>
    <?php if ($totalPages > 1): ?>
    <div class="sl-footer-right">
      <div class="sl-pagination">
        <!-- Prev -->
        <a href="<?= htmlspecialchars(sl_url(['page' => $page - 1])) ?>"
           class="sl-pg-btn sl-pg-nav <?= $page <= 1 ? 'sl-pg-disabled' : '' ?>">
          <i class="fas fa-chevron-left" style="font-size:.65rem;"></i><span>Prev</span>
        </a>

        <?php
          $delta = 2;
          $pgStart = max(1, $page - $delta);
          $pgEnd   = min($totalPages, $page + $delta);
        ?>
        <?php if ($pgStart > 1): ?>
          <a href="<?= htmlspecialchars(sl_url(['page' => 1])) ?>" class="sl-pg-btn">1</a>
          <?php if ($pgStart > 2): ?><span class="sl-pg-ellipsis">…</span><?php endif; ?>
        <?php endif; ?>

        <?php for ($p = $pgStart; $p <= $pgEnd; $p++): ?>
          <a href="<?= htmlspecialchars(sl_url(['page' => $p])) ?>"
             class="sl-pg-btn <?= $p === $page ? 'sl-pg-active' : '' ?>"><?= $p ?></a>
        <?php endfor; ?>

        <?php if ($pgEnd < $totalPages): ?>
          <?php if ($pgEnd < $totalPages - 1): ?><span class="sl-pg-ellipsis">…</span><?php endif; ?>
          <a href="<?= htmlspecialchars(sl_url(['page' => $totalPages])) ?>" class="sl-pg-btn"><?= $totalPages ?></a>
        <?php endif; ?>

        <!-- Next -->
        <a href="<?= htmlspecialchars(sl_url(['page' => $page + 1])) ?>"
           class="sl-pg-btn sl-pg-nav <?= $page >= $totalPages ? 'sl-pg-disabled' : '' ?>">
          <span>Next</span><i class="fas fa-chevron-right" style="font-size:.65rem;"></i>
        </a>
      </div>
    </div>
    <?php endif; ?>
  </div>
  <?php endif; ?>

  <?php else: ?>
    <div class="sl-empty">
      <i class="fa-regular fa-shield-check"></i>
      No login history found.
    </div>
  <?php endif; ?>
</div>

<?= $this->start('scripts') ?>
<script>
$(document).ready(function () {
  // ── Debounced search → submits form after 400ms pause ──────────
  var searchTimer = null;
  $('#slSearchInput').on('input', function () {
    clearTimeout(searchTimer);
    var $input = $(this);
    searchTimer = setTimeout(function () {
      $('#slSearchForm').submit();
    }, 400);
  });
});
</script>
<?= $this->end() ?>
