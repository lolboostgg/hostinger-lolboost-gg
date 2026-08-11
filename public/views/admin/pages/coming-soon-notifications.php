<?= $this->layout('admin/layouts/main', ['meta' => [
    'title' => 'Coming Soon Notifications | Admin | LoLBoost',
    'h1' => 'Coming Soon Notifications',
    'description' => 'Clients waiting for upcoming games and services.',
]]) ?>

<?php
$h = static fn($value) => htmlspecialchars((string)($value ?? ''), ENT_QUOTES, 'UTF-8');
$notifications = is_array($notifications ?? null) ? $notifications : [];
$games = is_array($games ?? null) ? $games : [];
$services = is_array($services ?? null) ? $services : [];
$stats = is_array($stats ?? null) ? $stats : [];
$formatDate = static function ($value): string {
    $value = trim((string)$value);
    if ($value === '') return '—';
    try {
        return (new DateTime($value))->format('d.m.Y H:i') . ' Uhr';
    } catch (Throwable $e) {
        return $value;
    }
};
$serviceLabels = [
    'accounts' => 'Accounts',
    'top-ups' => 'Top Ups',
    'items' => 'Items',
    'boosting' => 'Boosting',
];
?>

<?= $this->start('styles') ?>
<link rel="stylesheet" href="<?= ASSET_URL ?>/origin/dash/vendor/tom-select/dist/css/tom-select.css">
<style>
.csn-stats{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:12px;margin-bottom:16px}
.csn-stat,.csn-panel{background:#25282a;border:1px solid rgba(255,255,255,.07);border-radius:16px;box-shadow:0 2px 18px rgba(0,0,0,.18)}
.csn-stat{padding:16px 18px;display:flex;align-items:center;gap:12px}
.csn-stat-icon{width:40px;height:40px;border-radius:11px;display:flex;align-items:center;justify-content:center;background:rgba(124,92,255,.14);color:#a78bfa;flex:0 0 40px}
.csn-stat-label{font-size:.68rem;font-weight:800;color:rgba(255,255,255,.35);text-transform:uppercase;letter-spacing:.06em}
.csn-stat-value{font-size:1.35rem;font-weight:900;color:rgba(255,255,255,.92)}
.csn-filter{padding:14px 16px;margin-bottom:16px;display:grid;grid-template-columns:minmax(280px,1fr) 230px 190px max-content;gap:10px;align-items:center}
.csn-filter input.csn-control,.csn-filter select.csn-control{width:100%;height:40px;background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.09);border-radius:10px;color:rgba(255,255,255,.85);padding:0 12px;font-size:.82rem;outline:none}
.csn-filter input.csn-control:focus,.csn-filter select.csn-control:focus{border-color:rgba(124,92,255,.5);box-shadow:0 0 0 3px rgba(124,92,255,.09)}
.csn-filter select.csn-control option{background:#25282a;color:#fff}
.csn-filter-actions{display:flex;align-items:center;gap:8px;justify-content:flex-end;white-space:nowrap}
.csn-filter .ts-wrapper{display:block!important;width:100%!important;min-width:0}
.csn-filter .ts-control{width:100%!important;height:40px!important;min-height:40px!important;background:rgba(255,255,255,.04)!important;border:1px solid rgba(255,255,255,.09)!important;border-radius:10px!important;color:#fff!important;padding:9px 34px 9px 12px!important;box-shadow:none!important;line-height:20px!important}
.csn-filter .ts-wrapper.single .ts-control::after{right:13px!important;border-color:rgba(255,255,255,.45) transparent transparent!important}
.csn-filter .ts-control .item{max-width:100%;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}
.csn-filter .ts-wrapper.focus .ts-control{border-color:rgba(124,92,255,.5)!important;box-shadow:0 0 0 3px rgba(124,92,255,.09)!important}
.csn-filter .ts-dropdown{z-index:50!important;width:100%!important;background:#25282a!important;border:1px solid rgba(255,255,255,.1)!important;border-radius:10px!important;margin-top:5px!important;overflow:hidden;box-shadow:0 14px 34px rgba(0,0,0,.35)!important}
.csn-filter .ts-dropdown .option{padding:9px 12px!important;color:rgba(255,255,255,.72)!important}.csn-filter .ts-dropdown .active{background:rgba(124,92,255,.15)!important;color:#fff!important}
.csn-filter .ts-dropdown-content{max-height:260px!important}
.csn-table-wrap{overflow:auto}
.csn-table{width:100%;border-collapse:collapse;min-width:1100px}
.csn-table th{padding:12px 14px;background:rgba(0,0,0,.12);border-bottom:1px solid rgba(255,255,255,.06);font-size:.66rem;color:rgba(255,255,255,.35);text-transform:uppercase;letter-spacing:.06em;white-space:nowrap}
.csn-table td{padding:13px 14px;border-bottom:1px solid rgba(255,255,255,.045);font-size:.82rem;color:rgba(255,255,255,.75);vertical-align:middle}
.csn-table tr:last-child td{border-bottom:0}.csn-table tbody tr:hover{background:rgba(255,255,255,.025)}
.csn-email{color:#fff;font-weight:750}.csn-game-cell{display:flex;align-items:center;gap:10px}.csn-game-icon{width:36px;height:36px;object-fit:cover;border-radius:9px;background:rgba(255,255,255,.05);border:1px solid rgba(255,255,255,.08)}.csn-game-icon-fallback{display:flex;align-items:center;justify-content:center;color:#a78bfa}.csn-game{color:#fff;font-weight:750}.csn-muted{font-size:.72rem;color:rgba(255,255,255,.35)}
.csn-client{display:flex;align-items:center;gap:7px;color:#fff;text-decoration:none;font-size:.82rem;font-weight:800}.csn-client:hover{color:#c4b5fd}.csn-client-avatar{width:20px;height:20px;border-radius:50%;object-fit:cover}.csn-client-email{margin-top:3px;font-size:.75rem;color:rgba(255,255,255,.4)}
.csn-game-service{margin-top:2px;color:#a78bfa;font-size:.72rem;font-weight:750}
.csn-mail-status{margin-top:4px;font-size:.69rem;font-weight:750;color:#fbbf24}.csn-mail-status.is-sent{color:#34d399}
.csn-empty{padding:48px 20px;text-align:center;color:rgba(255,255,255,.32)}
@media(max-width:1100px){.csn-stats{grid-template-columns:repeat(2,minmax(0,1fr))}.csn-filter{grid-template-columns:minmax(220px,1fr) 200px 170px}.csn-filter-actions{grid-column:1/-1;justify-content:flex-end}}
@media(max-width:600px){.csn-stats,.csn-filter{grid-template-columns:1fr}.csn-filter .csn-search{grid-column:auto}}
</style>
<?= $this->end() ?>

<div class="csn-stats">
  <div class="csn-stat"><span class="csn-stat-icon"><i class="fa-duotone fa-bell"></i></span><div><div class="csn-stat-label">Registrations</div><div class="csn-stat-value"><?= number_format((int)($stats['total'] ?? 0)) ?></div></div></div>
  <div class="csn-stat"><span class="csn-stat-icon"><i class="fa-duotone fa-envelope"></i></span><div><div class="csn-stat-label">Unique emails</div><div class="csn-stat-value"><?= number_format((int)($stats['unique_emails'] ?? 0)) ?></div></div></div>
  <div class="csn-stat"><span class="csn-stat-icon"><i class="fa-duotone fa-gamepad-modern"></i></span><div><div class="csn-stat-label">Games</div><div class="csn-stat-value"><?= number_format((int)($stats['games'] ?? 0)) ?></div></div></div>
  <div class="csn-stat"><span class="csn-stat-icon"><i class="fa-duotone fa-calendar-week"></i></span><div><div class="csn-stat-label">Last 7 days</div><div class="csn-stat-value"><?= number_format((int)($stats['last_7_days'] ?? 0)) ?></div></div></div>
</div>

<form class="csn-panel csn-filter" method="get" id="comingSoonFilters">
  <input class="csn-control csn-search" type="search" name="q" value="<?= $h($search ?? '') ?>" placeholder="Search email, client or game…">
  <select class="csn-control js-csn-select" name="game" autocomplete="off">
    <option value="">All games</option>
    <?php foreach ($games as $row): ?>
      <option value="<?= $h($row['game_slug']) ?>" <?= ($game ?? '') === ($row['game_slug'] ?? '') ? 'selected' : '' ?>><?= $h($row['game_name'] ?: $row['game_slug']) ?> (<?= (int)$row['total'] ?>)</option>
    <?php endforeach; ?>
  </select>
  <select class="csn-control js-csn-select" name="service" autocomplete="off">
    <option value="">All services</option>
    <?php foreach ($services as $row): $key = (string)($row['service_type'] ?? ''); ?>
      <option value="<?= $h($key) ?>" <?= ($service ?? '') === $key ? 'selected' : '' ?>><?= $h($serviceLabels[$key] ?? ucwords(str_replace(['-', '_'], ' ', $key))) ?> (<?= (int)$row['total'] ?>)</option>
    <?php endforeach; ?>
  </select>
  <div class="csn-filter-actions">
    <button class="btn btn-primary btn-sm" type="submit" name="export" value="emails"><i class="fa-solid fa-file-csv me-1"></i>Export Emails</button>
    <?php if (($search ?? '') !== '' || ($game ?? '') !== '' || ($service ?? '') !== ''): ?><a class="btn btn-ghost-secondary btn-sm" href="<?= ADMN_URL ?>/coming-soon-notifications">Reset</a><?php endif; ?>
  </div>
</form>

<?php if (!empty($queryError)): ?>
  <div class="alert alert-soft-danger">The notifications could not be loaded.</div>
<?php endif; ?>

<div class="csn-panel csn-table-wrap">
  <?php if ($notifications): ?>
    <table class="csn-table">
      <thead><tr><th>Email / Client</th><th>Game / Service</th><th>Registered</th></tr></thead>
      <tbody>
      <?php foreach ($notifications as $row):
        $serviceKey = (string)($row['service_type'] ?? '');
        $gameIcon = function_exists('util_game_icon_url')
          ? (string)util_game_icon_url((string)($row['game_slug'] ?? ''))
          : trim((string)($row['game_icon'] ?? ''));
        $clientIcon = trim((string)($row['client_icon'] ?? ''));
      ?>
        <tr>
          <td>
            <?php if (!empty($row['client_id'])): ?>
              <a class="csn-client" href="<?= ADMN_URL ?>/client/<?= (int)$row['client_id'] ?>">
                <?php if ($clientIcon !== ''): ?><img class="csn-client-avatar" src="<?= $h($clientIcon) ?>" alt=""><?php else: ?><i class="fa-solid fa-circle-user"></i><?php endif; ?>
                <?= $h($row['client_username'] ?: 'Client #' . (int)$row['client_id']) ?>
              </a>
              <div class="csn-client-email"><?= $h($row['email']) ?></div>
            <?php else: ?>
              <span class="csn-email"><?= $h($row['email']) ?></span>
              <div class="csn-muted">No client account</div>
            <?php endif; ?>
          </td>
          <td><div class="csn-game-cell">
            <?php if ($gameIcon !== ''): ?><img class="csn-game-icon" src="<?= $h($gameIcon) ?>" alt=""><?php else: ?><span class="csn-game-icon csn-game-icon-fallback"><i class="fa-solid fa-gamepad-modern"></i></span><?php endif; ?>
            <div>
              <div class="csn-game"><?= $h($row['game_name'] ?: $row['game_slug']) ?></div>
              <div class="csn-game-service"><?= $h($serviceLabels[$serviceKey] ?? ucwords(str_replace(['-', '_'], ' ', $serviceKey))) ?></div>
            </div>
          </div></td>
          <td>
            <div><?= $h($formatDate($row['created_at'] ?? '')) ?></div>
            <?php if (!empty($row['notified_at'])): ?>
              <div class="csn-mail-status is-sent"><i class="fa-solid fa-circle-check me-1"></i>Notified <?= $h($formatDate($row['notified_at'])) ?></div>
            <?php else: ?>
              <div class="csn-mail-status"><i class="fa-solid fa-clock me-1"></i>Waiting for listings</div>
            <?php endif; ?>
          </td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  <?php else: ?>
    <div class="csn-empty"><i class="fa-duotone fa-bell-slash fa-2x mb-3"></i><div>No matching registrations found.</div></div>
  <?php endif; ?>
</div>

<?= $this->start('scripts') ?>
<script src="<?= ASSET_URL ?>/origin/dash/vendor/tom-select/dist/js/tom-select.complete.min.js"></script>
<script>
(function(){
  var form = document.getElementById('comingSoonFilters');
  if (!form) return;
  var search = form.querySelector('input[name="q"]');
  var timer;
  function applyFilters(){ form.querySelectorAll('[name="export"]').forEach(function(el){ el.disabled = true; }); form.submit(); }
  form.querySelectorAll('.js-csn-select').forEach(function(el){
    if (window.TomSelect && !el.tomselect) {
      new TomSelect(el, {searchField:['text'], allowEmptyOption:true, onChange:function(){ applyFilters(); }});
    } else {
      el.addEventListener('change', applyFilters);
    }
  });
  if (search) search.addEventListener('input', function(){
    clearTimeout(timer);
    timer = setTimeout(applyFilters, 350);
  });
})();
</script>
<?= $this->end() ?>
