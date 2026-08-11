<?php
/**
 * pages/shifts.php — Shift Schedule
 * Route: admin-area/shifts
 */

$weekParam = $_GET['week'] ?? null;
$weekStart = $weekParam
    ? date('Y-m-d', strtotime('Monday this week', strtotime($weekParam)))
    : date('Y-m-d', strtotime('Monday this week'));
$weekEnd  = date('Y-m-d', strtotime($weekStart . ' +6 days'));
$prevWeek = date('Y-m-d', strtotime($weekStart . ' -7 days'));
$nextWeek = date('Y-m-d', strtotime($weekStart . ' +7 days'));

$shiftTypes = [
    'morning'   => ['label' => 'Morning',   'icon' => 'fa-sunrise',    'time' => '06:00–14:00', 'color' => 'warning'],
    'afternoon' => ['label' => 'Afternoon',  'icon' => 'fa-sun',        'time' => '14:00–22:00', 'color' => 'info'],
    'night'     => ['label' => 'Night',      'icon' => 'fa-moon-stars', 'time' => '22:00–06:00', 'color' => 'dark'],
    'custom'    => ['label' => 'Custom',     'icon' => 'fa-clock',      'time' => 'Custom',       'color' => 'secondary'],
];
$days = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];

$allShifts = $db->run(
    'SELECT s.*, a.username, a.icon as avatar
     FROM admin_shifts s
     JOIN admins a ON a.id = s.admin_id
     WHERE s.week_start = ?
     ORDER BY s.day ASC, s.start_time ASC',
    $weekStart
) ?: [];

$shiftsByDay = [];
foreach ($allShifts as $shift) {
    $shiftsByDay[$shift['day']][] = $shift;
}
$myShifts = array_values(array_filter($allShifts, fn($s) => $s['admin_id'] == ADMIN_DATA['id']));

$activeSessions = $db->run(
    "SELECT ss.*, a.username FROM admin_shift_sessions ss
     JOIN admins a ON a.id = ss.admin_id
     WHERE ss.status = 'active' ORDER BY ss.started_at DESC"
) ?: [];
?>
<?= $this->layout('admin/layouts/main', ['meta' => $meta]) ?>

<?= $this->section('styles') ?>
<style>
.shift-grid-header { font-size:.72rem; font-weight:600; text-transform:uppercase; letter-spacing:.05em; color:#8c98a4; text-align:center; margin-bottom:.5rem; }
.day-col {
    min-height:110px; border-radius:.5rem; background:#f9fafc;
    border:1.5px dashed #e7eaf3; padding:.5rem;
    transition:background .15s,border-color .15s; cursor:pointer;
}
.day-col:hover       { background:#eef3ff; border-color:#377dff; }
.day-col.has-shift   { background:#fff; border-style:solid; border-color:#e7eaf3; }
.day-col.today       { border-color:#377dff; background:#f0f5ff; }
.shift-entry {
    border-radius:.35rem; padding:.3rem .4rem; margin-bottom:.25rem;
    background:#f0f0f0; font-size:.72rem; position:relative;
}
.shift-entry.mine    { background:#eef3ff; border:1px solid #c5d5ff; }
.shift-time          { font-family:'Courier New',monospace; font-size:.68rem; background:rgba(0,0,0,.06); border-radius:3px; padding:1px 5px; }
.shift-avatar        { width:20px; height:20px; border-radius:50%; object-fit:cover; vertical-align:middle; margin-right:3px; }
.week-nav-btn {
    border-radius:50%; width:34px; height:34px; display:flex; align-items:center;
    justify-content:center; border:1px solid #e7eaf3; background:#fff; color:#1e2022;
    text-decoration:none; transition:all .15s; flex-shrink:0;
}
.week-nav-btn:hover  { background:#377dff; border-color:#377dff; color:#fff; }
.live-dot { width:8px; height:8px; border-radius:50%; background:#00c9a7; display:inline-block; animation:livePulse 1.5s infinite; }
@keyframes livePulse { 0%,100%{opacity:1;transform:scale(1)} 50%{opacity:.5;transform:scale(1.4)} }
.shift-type-radio    { display:none; }
.shift-type-label {
    border:1.5px solid #e7eaf3; border-radius:.5rem; padding:.65rem .75rem;
    display:flex; align-items:center; gap:.6rem; cursor:pointer;
    transition:all .15s; width:100%; background:#fff;
}
.shift-type-radio:checked + .shift-type-label { border-color:#377dff; background:#eef3ff; }
.shift-type-label:hover { border-color:#adc8ff; background:#f5f9ff; }
</style>
<?= $this->end() ?>

<main id="content" role="main" class="main">
<div class="content container-fluid">

    <!-- Page Header -->
    <div class="page-header mb-4">
        <div class="row align-items-center">
            <div class="col">
                <h1 class="page-header-title">
                    <i class="fa-duotone fa-calendar-clock me-2 text-primary"></i>Shift Schedule
                </h1>
                <p class="text-muted mb-0">Sign up for weekly shifts. Activity checks run randomly every 45–75 minutes.</p>
            </div>
            <div class="col-auto">
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modal-add-shift">
                    <i class="fa-duotone fa-plus me-1"></i> Add My Shift
                </button>
            </div>
        </div>
    </div>

    <!-- Active Sessions Banner -->
    <?php if (!empty($activeSessions)): ?>
    <div class="alert d-flex align-items-center gap-3 mb-4" style="background:#f0fff8;border:1px solid #b2ebd4;border-radius:.5rem;">
        <span class="live-dot"></span>
        <div>
            <strong><?= count($activeSessions) ?> admin(s) currently on shift:</strong>
            <?php foreach ($activeSessions as $s): ?>
            <span class="badge bg-soft-success text-success ms-1"><?= htmlspecialchars($s['username']) ?></span>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>

    <!-- Week Navigation -->
    <div class="d-flex align-items-center gap-3 mb-4">
        <a href="?week=<?= $prevWeek ?>" class="week-nav-btn">
            <i class="fa-solid fa-chevron-left" style="font-size:.8rem;"></i>
        </a>
        <div class="text-center flex-grow-1" style="max-width:200px;">
            <h5 class="mb-0"><?= date('d M', strtotime($weekStart)) ?> – <?= date('d M Y', strtotime($weekEnd)) ?></h5>
            <small class="text-muted">Week <?= date('W', strtotime($weekStart)) ?></small>
        </div>
        <a href="?week=<?= $nextWeek ?>" class="week-nav-btn">
            <i class="fa-solid fa-chevron-right" style="font-size:.8rem;"></i>
        </a>
        <a href="?" class="btn btn-sm btn-outline-secondary">Today</a>
    </div>

    <!-- Shift type legend -->
    <div class="d-flex flex-wrap gap-2 mb-4">
        <?php foreach ($shiftTypes as $k => $t): ?>
        <span class="badge bg-soft-<?= $t['color'] ?> text-<?= $t['color'] ?> px-3 py-2">
            <i class="fa-duotone <?= $t['icon'] ?> me-1"></i><?= $t['label'] ?>
            <span class="opacity-75 ms-1"><?= $t['time'] ?></span>
        </span>
        <?php endforeach; ?>
    </div>

    <!-- 7-Day Grid -->
    <div class="card mb-4">
        <div class="card-body p-3">
            <div class="row g-2">
                <?php foreach ($days as $dayIdx => $dayName):
                    $dateOfDay = date('Y-m-d', strtotime($weekStart . " +{$dayIdx} days"));
                    $isToday   = ($dateOfDay === date('Y-m-d'));
                    $dayShifts = $shiftsByDay[$dayIdx] ?? [];
                ?>
                <div class="col">
                    <div class="shift-grid-header">
                        <?= $dayName ?>
                        <div style="font-size:.78rem;font-weight:400;color:#1e2022;"><?= date('d', strtotime($dateOfDay)) ?></div>
                    </div>
                    <div class="day-col <?= !empty($dayShifts) ? 'has-shift' : '' ?> <?= $isToday ? 'today' : '' ?>"
                         onclick="openAddShift(<?= $dayIdx ?>)">

                        <?php if (empty($dayShifts)): ?>
                        <div class="text-center text-muted pt-2" style="font-size:.72rem;">
                            <i class="fa-duotone fa-plus opacity-40"></i><br>Add
                        </div>
                        <?php else: ?>
                        <?php foreach ($dayShifts as $shift):
                            $st   = $shiftTypes[$shift['shift_type']] ?? $shiftTypes['custom'];
                            $isMe = ($shift['admin_id'] == ADMIN_DATA['id']);
                        ?>
                        <div class="shift-entry <?= $isMe ? 'mine' : '' ?>"
                             onclick="event.stopPropagation();">
                            <div class="d-flex align-items-center justify-content-between">
                                <div>
                                    <img class="shift-avatar" src="<?= htmlspecialchars($shift['avatar']) ?>" alt="">
                                    <strong><?= htmlspecialchars($shift['username']) ?></strong>
                                </div>
                                <?php if ($isMe): ?>
                                <button class="btn btn-xs btn-soft-danger py-0 px-1 delete-shift"
                                        data-id="<?= $shift['id'] ?>"
                                        onclick="event.stopPropagation();" title="Remove">
                                    <i class="fa-duotone fa-xmark"></i>
                                </button>
                                <?php endif; ?>
                            </div>
                            <div class="mt-1">
                                <span class="shift-time"><?= date('H:i', strtotime($shift['start_time'])) ?>–<?= date('H:i', strtotime($shift['end_time'])) ?></span>
                                <span class="badge bg-soft-<?= $st['color'] ?> text-<?= $st['color'] ?> ms-1" style="font-size:.62rem;">
                                    <?= $st['label'] ?>
                                </span>
                            </div>
                        </div>
                        <?php endforeach; ?>
                        <div class="text-center mt-1" style="font-size:.7rem;color:#377dff;">
                            <i class="fa-duotone fa-plus"></i> add
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <!-- My Shifts This Week -->
    <?php if (!empty($myShifts)): ?>
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0"><i class="fa-duotone fa-list-check me-2"></i>My Shifts This Week</h5>
        </div>
        <div class="table-responsive">
            <table class="table table-sm table-hover mb-0">
                <thead><tr><th>Day</th><th>Date</th><th>Type</th><th>Hours</th><th>Notes</th><th></th></tr></thead>
                <tbody>
                <?php foreach ($myShifts as $shift):
                    $st      = $shiftTypes[$shift['shift_type']] ?? $shiftTypes['custom'];
                    $dayDate = date('Y-m-d', strtotime($weekStart . " +{$shift['day']} days"));
                ?>
                <tr>
                    <td><strong><?= $days[$shift['day']] ?></strong></td>
                    <td><?= date('d M', strtotime($dayDate)) ?></td>
                    <td>
                        <span class="badge bg-soft-<?= $st['color'] ?> text-<?= $st['color'] ?>">
                            <i class="fa-duotone <?= $st['icon'] ?> me-1"></i><?= $st['label'] ?>
                        </span>
                    </td>
                    <td><code><?= date('H:i', strtotime($shift['start_time'])) ?> – <?= date('H:i', strtotime($shift['end_time'])) ?></code></td>
                    <td><small class="text-muted"><?= htmlspecialchars($shift['notes'] ?? '–') ?></small></td>
                    <td>
                        <button class="btn btn-xs btn-soft-danger delete-shift" data-id="<?= $shift['id'] ?>">
                            <i class="fa-duotone fa-trash-xmark me-1"></i>Remove
                        </button>
                    </td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php endif; ?>

</div>
</main>

<!-- ============================================================ -->
<!-- Modal: Add Shift                                              -->
<!-- ============================================================ -->
<div id="modal-add-shift" class="modal fade" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title">
                    <i class="fa-duotone fa-calendar-plus me-2 text-primary"></i>Add My Shift
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="shift-week-start" value="<?= $weekStart ?>">

                <!-- Day selector -->
                <div class="mb-3">
                    <label class="form-label fw-semibold">Day</label>
                    <select id="shift-day" class="form-select">
                        <?php foreach ($days as $i => $d): ?>
                        <option value="<?= $i ?>"><?= $d ?>, <?= date('d M', strtotime($weekStart . " +{$i} days")) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Shift type selector -->
                <div class="mb-3">
                    <label class="form-label fw-semibold">Shift Type</label>
                    <div class="row g-2">
                        <?php foreach ($shiftTypes as $key => $type): ?>
                        <div class="col-6">
                            <input type="radio" name="shift_type_radio" id="stype-<?= $key ?>"
                                   class="shift-type-radio" value="<?= $key ?>"
                                   <?= $key === 'morning' ? 'checked' : '' ?>>
                            <label for="stype-<?= $key ?>" class="shift-type-label">
                                <i class="fa-duotone <?= $type['icon'] ?> text-<?= $type['color'] ?> fa-lg"></i>
                                <div>
                                    <div class="fw-semibold" style="font-size:.85rem;"><?= $type['label'] ?></div>
                                    <div class="text-muted" style="font-size:.72rem;"><?= $type['time'] ?></div>
                                </div>
                            </label>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Custom time (shown only for 'custom') -->
                <div id="custom-time-row" class="row g-2 mb-3" style="display:none;">
                    <div class="col-6">
                        <label class="form-label">Start Time</label>
                        <input type="time" id="custom-start" class="form-control" value="08:00">
                    </div>
                    <div class="col-6">
                        <label class="form-label">End Time</label>
                        <input type="time" id="custom-end" class="form-control" value="16:00">
                    </div>
                </div>

                <!-- Notes -->
                <div>
                    <label class="form-label">Notes <small class="text-muted fw-normal">(optional)</small></label>
                    <input type="text" id="shift-notes" class="form-control" placeholder="e.g. Only from 23:00">
                </div>
            </div>
            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn btn-ghost-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="btn-save-shift">
                    <i class="fa-duotone fa-floppy-disk me-1"></i>Save Shift
                </button>
            </div>
        </div>
    </div>
</div>

<?= $this->section('scripts') ?>
<script>
const AJAX_URL = '<?= AJAX_URL ?>';
const SHIFT_TIMES = {
    morning:   { start: '06:00', end: '14:00' },
    afternoon: { start: '14:00', end: '22:00' },
    night:     { start: '22:00', end: '06:00' },
    custom:    null,
};

// Show/hide custom time row
document.querySelectorAll('.shift-type-radio').forEach(r => {
    r.addEventListener('change', () => {
        document.getElementById('custom-time-row').style.display = r.value === 'custom' ? 'flex' : 'none';
    });
});

function openAddShift(day) {
    document.getElementById('shift-day').value = day;
    new bootstrap.Modal(document.getElementById('modal-add-shift')).show();
}

document.getElementById('btn-save-shift').addEventListener('click', function () {
    const type  = document.querySelector('.shift-type-radio:checked')?.value || 'morning';
    const times = type === 'custom'
        ? { start: document.getElementById('custom-start').value, end: document.getElementById('custom-end').value }
        : SHIFT_TIMES[type];

    fetch(AJAX_URL, {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: new URLSearchParams({
            action:     'admin_shift_add',
            week_start: document.getElementById('shift-week-start').value,
            day:        document.getElementById('shift-day').value,
            shift_type: type,
            start_time: times.start,
            end_time:   times.end,
            notes:      document.getElementById('shift-notes').value,
        })
    })
    .then(r => r.json())
    .then(d => {
        if (d.sendToast && window.create_toast) create_toast(d.sendToast.type, d.sendToast.title, d.sendToast.message);
        if (d.success) { bootstrap.Modal.getInstance(document.getElementById('modal-add-shift')).hide(); location.reload(); }
    });
});

document.querySelectorAll('.delete-shift').forEach(btn => {
    btn.addEventListener('click', function (e) {
        e.stopPropagation();
        if (!confirm('Remove this shift?')) return;
        fetch(AJAX_URL, {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: new URLSearchParams({ action: 'admin_shift_delete', id: this.dataset.id })
        })
        .then(r => r.json())
        .then(d => {
            if (d.sendToast && window.create_toast) create_toast(d.sendToast.type, d.sendToast.title, d.sendToast.message);
            if (d.success) location.reload();
        });
    });
});
</script>
<?= $this->end() ?>
