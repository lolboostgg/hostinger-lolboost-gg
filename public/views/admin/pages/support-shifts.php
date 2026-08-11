<?= $this->layout('admin/layouts/main', [
    'meta' => [
        'title' => 'Support Shifts | Admin Area | LoLBoost.gg',
    ],
]) ?>

<?php
$data         = $data ?? [];
$shifts       = $data['shifts'] ?? [];
$activeShift  = $data['active_shift'] ?? [];
$stats        = $data['stats'] ?? [];
$admins       = $data['admins'] ?? [];
$templates    = $data['templates'] ?? [];
$isSuperAdmin = !empty($data['is_super_admin']);
$currentAdminId = function_exists('lb_support_shift_current_admin_id') ? (int)lb_support_shift_current_admin_id() : 0;
$fromRaw      = (string)($data['from'] ?? date('Y-m-d'));
$toRaw        = (string)($data['to']   ?? date('Y-m-d', strtotime('+6 days')));
$from         = htmlspecialchars($fromRaw, ENT_QUOTES);
$to           = htmlspecialchars($toRaw, ENT_QUOTES);

if (!function_exists('lb_ss_h')) {
    function lb_ss_h($v) { return htmlspecialchars((string)($v ?? ''), ENT_QUOTES, 'UTF-8'); }
}
if (!function_exists('lb_ss_5')) {
    function lb_ss_5($v) { return substr((string)$v, 0, 5); }
}
if (!function_exists('lb_ss_avatar')) {
    function lb_ss_avatar($icon, $name = 'A') {
        $icon = trim((string)($icon ?? ''));
        $name = trim((string)($name ?? 'A'));
        if ($icon !== '') return '<img class="ss-av" src="'.lb_ss_h($icon).'" alt="'.lb_ss_h($name).'">';
        return '<span class="ss-av ss-av-fb">'.lb_ss_h(mb_strtoupper(mb_substr($name ?: 'A', 0, 1))).'</span>';
    }
}
if (!function_exists('lb_ss_admin_icon')) {
    function lb_ss_admin_icon($admin) {
        if (!is_array($admin)) return '';
        foreach (['icon','avatar','profile_image','profile_picture','image','picture','photo','assigned_icon'] as $key) {
            if (!empty($admin[$key])) return (string)$admin[$key];
        }
        return '';
    }
}

if (!function_exists('lb_ss_badge_label')) {
    function lb_ss_badge_label($status) {
        $status = (string)$status;
        $labels = [
            'open' => 'Open',
            'assigned' => 'Assigned',
            'active' => 'Active',
            'completed' => 'Completed',
            'cancelled' => 'Cancelled',
            'past' => 'Past',
            'resume' => 'Resume',
            'paused' => 'Paused',
            'missed' => 'Missed',
        ];
        return $labels[$status] ?? ucwords(str_replace(['_', '-'], ' ', $status));
    }
}

if (!function_exists('lb_ss_worked')) {
    function lb_ss_worked($minutes) {
        $m = (int)$minutes;
        return intdiv($m, 60).'h '.str_pad($m % 60, 2, '0', STR_PAD_LEFT).'m';
    }
}

// Build day map
$days = [];
$cursor = new DateTime($fromRaw);
$endDt  = new DateTime($toRaw);
while ($cursor <= $endDt) {
    $days[$cursor->format('Y-m-d')] = [];
    $cursor->modify('+1 day');
}
foreach ($shifts as $s) {
    $k = (string)($s['shift_date'] ?? '');
    if (isset($days[$k])) $days[$k][] = $s;
}
ksort($days);

// Load all admins currently registered in the visible shifts.
$shiftParticipants = [];
try {
    global $db;
    $shiftIds = [];
    foreach ($shifts as $row) {
        $sid = (int)($row['id'] ?? 0);
        if ($sid > 0) $shiftIds[] = $sid;
    }
    $shiftIds = array_values(array_unique($shiftIds));
    if (!empty($shiftIds) && isset($db)) {
        $db->run("CREATE TABLE IF NOT EXISTS support_shift_participants (
            id INT UNSIGNED NOT NULL AUTO_INCREMENT,
            shift_id INT UNSIGNED NOT NULL,
            admin_id INT UNSIGNED NOT NULL,
            status VARCHAR(20) NOT NULL DEFAULT 'active',
            started_at DATETIME NULL,
            ended_at DATETIME NULL,
            planned_start_time TIME NULL,
            planned_end_time TIME NULL,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY idx_shift_admin (shift_id, admin_id),
            KEY idx_admin_status (admin_id, status),
            KEY idx_shift_status (shift_id, status)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        try { $db->run("ALTER TABLE support_shift_participants DROP INDEX uniq_shift_admin"); } catch (Throwable $e) {}
        try { $db->run("ALTER TABLE support_shift_participants ADD KEY idx_shift_admin (shift_id, admin_id)"); } catch (Throwable $e) {}
        foreach ([
            "ALTER TABLE support_shift_participants ADD COLUMN planned_start_time TIME NULL AFTER ended_at",
            "ALTER TABLE support_shift_participants ADD COLUMN planned_end_time TIME NULL AFTER planned_start_time"
        ] as $__sql) { try { $db->run($__sql); } catch (Throwable $e) {} }
        // Legacy participant migration is handled only in the AJAX backend.
        // The view must remain read-only, otherwise assigned_admin_id recreates
        // a full-shift participant after an individual hour is released.
        $placeholders = implode(',', array_fill(0, count($shiftIds), '?'));
        $participantRows = $db->run("SELECT p.shift_id, p.admin_id, p.status, p.started_at, p.ended_at, p.planned_start_time, p.planned_end_time,
                a.username, a.email, a.icon
            FROM support_shift_participants p
            LEFT JOIN admins a ON a.id = p.admin_id
            WHERE p.shift_id IN ($placeholders) AND p.status IN ('active','paused','assigned')
            ORDER BY p.started_at ASC, p.id ASC", ...$shiftIds) ?: [];
        foreach ($participantRows as $pr) {
            $shiftParticipants[(int)$pr['shift_id']][] = $pr;
        }
    }
} catch (Throwable $e) {
    $shiftParticipants = [];
}
foreach ($shifts as &$__shiftRow) {
    $__sid = (int)($__shiftRow['id'] ?? 0);
    $__shiftRow['participants'] = $shiftParticipants[$__sid] ?? [];
}
unset($__shiftRow);
foreach ($days as $__d => &$__dayRows) {
    foreach ($__dayRows as &$__dayShift) {
        $__sid = (int)($__dayShift['id'] ?? 0);
        $__dayShift['participants'] = $shiftParticipants[$__sid] ?? [];
    }
    unset($__dayShift);
}
unset($__dayRows);

$statsTotals = [
    'total' => 0,
    'completed' => 0,
    'active' => 0,
    'missed' => 0,
    'worked' => 0,
];
foreach ($stats as $sr) {
    $statsTotals['total'] += (int)($sr['total_shifts'] ?? 0);
    $statsTotals['completed'] += (int)($sr['completed_shifts'] ?? 0);
    $statsTotals['active'] += (int)($sr['active_shifts'] ?? 0);
    $statsTotals['missed'] += (int)($sr['not_started'] ?? 0);
    $statsTotals['worked'] += (int)($sr['worked_minutes'] ?? 0);
}
$statsLabel = 'Selected period';
$todayYmd = date('Y-m-d');
if ($fromRaw === $todayYmd && $toRaw === $todayYmd) {
    $statsLabel = 'Today';
} elseif ((strtotime($toRaw) - strtotime($fromRaw)) <= 6 * 86400) {
    $statsLabel = 'This week';
} elseif (substr($fromRaw, 0, 7) === substr($toRaw, 0, 7) && substr($fromRaw, -2) === '01') {
    $statsLabel = 'This month';
}


$plannerTotals = [
    'shifts' => 0,
    'assigned' => 0,
    'active' => 0,
    'missed' => 0,
    'scheduled_minutes' => 0,
];
$activeNowList = [];
$activeNowMap = [];
$nowTsPlanner = time();
foreach ($shifts as $ps) {
    $plannerTotals['shifts']++;
    $psStatus = (string)($ps['status'] ?? 'open');
    $psAssigned = !empty($ps['assigned_admin_id']);
    if ($psAssigned) $plannerTotals['assigned']++;
    if ($psStatus === 'active') {
        $plannerTotals['active']++;
        $participants = $ps['participants'] ?? [];
        $shiftDate = (string)($ps['shift_date'] ?? date('Y-m-d'));
        $shiftStartTs = strtotime($shiftDate.' '.($ps['start_time'] ?? '00:00:00'));

        if (!empty($participants)) {
            foreach ($participants as $participant) {
                // Match the booster header/dashboard: everybody scheduled in the
                // current block is shown, even before manually starting the shift.
                if (!in_array((string)($participant['status'] ?? ''), ['assigned','active','paused'], true)) continue;

                $pStartRaw = (string)($participant['planned_start_time'] ?: ($ps['start_time'] ?? '00:00:00'));
                $pEndRaw   = (string)($participant['planned_end_time'] ?: ($ps['end_time'] ?? '00:00:00'));
                $pStartTs  = strtotime($shiftDate.' '.$pStartRaw);
                $pEndTs    = strtotime($shiftDate.' '.$pEndRaw);
                if ($shiftStartTs && $pStartTs < $shiftStartTs) $pStartTs += 86400;
                if ($pEndTs <= $pStartTs) $pEndTs += 86400;

                // Only show the participant who is actually working at this moment.
                if ($nowTsPlanner < $pStartTs || $nowTsPlanner >= $pEndTs) continue;

                $adminKey = (int)($participant['admin_id'] ?? 0);
                if ($adminKey <= 0) $adminKey = 'name:'.strtolower((string)($participant['username'] ?? 'admin'));

                if (!isset($activeNowMap[$adminKey])) {
                    $activeNowMap[$adminKey] = [
                        'shift' => $ps,
                        'admin' => $participant,
                        'start_ts' => $pStartTs,
                        'end_ts' => $pEndTs,
                    ];
                } else {
                    $activeNowMap[$adminKey]['start_ts'] = min($activeNowMap[$adminKey]['start_ts'], $pStartTs);
                    $activeNowMap[$adminKey]['end_ts'] = max($activeNowMap[$adminKey]['end_ts'], $pEndTs);
                }
            }
        } elseif (!empty($ps['assigned_admin_id'])) {
            $pStartTs = strtotime($shiftDate.' '.($ps['start_time'] ?? '00:00:00'));
            $pEndTs = strtotime($shiftDate.' '.($ps['end_time'] ?? '00:00:00'));
            if ($pEndTs <= $pStartTs) $pEndTs += 86400;
            if ($nowTsPlanner >= $pStartTs && $nowTsPlanner < $pEndTs) {
                $adminKey = (int)$ps['assigned_admin_id'];
                $activeNowMap[$adminKey] = [
                    'shift' => $ps,
                    'admin' => [
                        'admin_id' => $adminKey,
                        'username' => $ps['assigned_username'] ?? 'Admin',
                        'icon' => $ps['assigned_icon'] ?? '',
                    ],
                    'start_ts' => $pStartTs,
                    'end_ts' => $pEndTs,
                ];
            }
        }
    }

    $psDate = (string)($ps['shift_date'] ?? $fromRaw);
    $psStartIso = (string)($ps['start_iso'] ?? '');
    $psEndIso = (string)($ps['end_iso'] ?? '');
    $psStart = $psStartIso ? strtotime($psStartIso) : strtotime($psDate.' '.($ps['start_time'] ?? '00:00'));
    $psEnd = $psEndIso ? strtotime($psEndIso) : strtotime($psDate.' '.($ps['end_time'] ?? '00:00'));
    if ($psStart && $psEnd) {
        if ($psEnd <= $psStart) $psEnd += 86400;
        $plannerTotals['scheduled_minutes'] += max(0, (int)(($psEnd - $psStart) / 60));
        if ($nowTsPlanner > $psEnd && in_array($psStatus, ['open','assigned'], true)) $plannerTotals['missed']++;
    }
}
$activeNowList = array_values($activeNowMap);
// "Active now" is shown together with individual admin chips, so the KPI must
// count the admins covering the current block rather than the parent shifts.
$plannerTotals['active'] = count($activeNowList);
$plannerRangeLabel = date('d.m.Y', strtotime($fromRaw)).' - '.date('d.m.Y', strtotime($toRaw));

$prevFrom = (new DateTime($fromRaw))->modify('-7 days')->format('Y-m-d');
$nextFrom = (new DateTime($fromRaw))->modify('+7 days')->format('Y-m-d');
$prevTo   = (new DateTime($prevFrom))->modify('+6 days')->format('Y-m-d');
$nextTo   = (new DateTime($nextFrom))->modify('+6 days')->format('Y-m-d');

$lb_ss_display_title = static function ($rawTitle, $startTs, $endTs): string {
    $title = trim((string)($rawTitle ?? ''));
    $normalized = mb_strtolower(preg_replace('/\s+/', ' ', $title), 'UTF-8');
    $startHm = $startTs > 0 ? date('H:i', $startTs) : '';
    $endHm = $endTs > 0 ? date('H:i', $endTs) : '';

    if (in_array($normalized, ['', 'support shift', 'morning support shift', 'evening support shift', 'day support shift', 'night support shift'], true)) {
        if ($startHm === '06:00' && $endHm === '14:00') return 'Morning Support';
        if ($startHm === '14:00' && $endHm === '22:00') return 'Day Support';
        if ($startHm === '22:00' && $endHm === '06:00') return 'Night Support';
    }

    if ($normalized === 'morning support shift') return 'Morning Support';
    if ($normalized === 'evening support shift' || $normalized === 'day support shift') return 'Day Support';
    if ($normalized === 'night support shift') return 'Night Support';

    return $title !== '' ? $title : 'Support Shift';
};
?>

<style>
:root {
    --ss-card:   #1e2123;
    --ss-card2:  #252a2d;
    --ss-border: rgba(255,255,255,.07);
    --ss-text:   rgba(255,255,255,.88);
    --ss-muted:  rgba(255,255,255,.42);
    --ss-purple: #6d5cff;
    --ss-teal:   #00c9a7;
    --ss-red:    #ed4c78;
}

.ss-wrap { color: var(--ss-text); }
.ss-week-loading { opacity:.45; pointer-events:none; transition:opacity .12s ease; }

/* hero */
.ss-hero {
    background: linear-gradient(135deg,#1a1d20,#1e2430);
    border: 1px solid rgba(109,92,255,.22);
    border-radius: 20px;
    padding: 28px 32px;
    margin-bottom: 24px;
    position: relative; overflow: hidden;
    box-shadow: 0 4px 32px rgba(0,0,0,.28);
}
.ss-hero::before {
    content:''; position:absolute; top:-60px; right:-60px;
    width:220px; height:220px; border-radius:50%;
    background: radial-gradient(circle,rgba(109,92,255,.12) 0%,transparent 70%);
    pointer-events: none;
}
.ss-hero-badge {
    display:inline-flex; align-items:center; gap:6px;
    background:rgba(109,92,255,.14); border:1px solid rgba(109,92,255,.28);
    color:#a78bfa; border-radius:100px; padding:4px 12px;
    font-size:11px; font-weight:700; margin-bottom:12px; letter-spacing:.03em;
}
.ss-hero h2 { font-size:22px; font-weight:900; color:rgba(255,255,255,.95); margin:0 0 6px; }
.ss-hero-sub { font-size:13px; color:rgba(255,255,255,.38); margin:0; }

/* active card */
.ss-active-card {
    background: rgba(255,255,255,.04);
    border: 1px solid rgba(255,255,255,.08);
    border-radius: 14px; padding: 18px 20px;
}
.ss-active-card.is-on { border-color:rgba(0,201,167,.22); background:rgba(0,201,167,.05); }
.ss-active-tag {
    display:inline-flex; align-items:center; gap:6px;
    font-size:10px; font-weight:800; text-transform:uppercase; letter-spacing:.07em;
    color:var(--ss-teal); margin-bottom:8px;
}
.ss-active-dot {
    width:6px; height:6px; border-radius:50%; background:var(--ss-teal);
    box-shadow:0 0 0 3px rgba(0,201,167,.15);
    animation: ssDot 1.8s ease-in-out infinite;
}
@keyframes ssDot { 0%,100%{box-shadow:0 0 0 0 rgba(0,201,167,.4)} 50%{box-shadow:0 0 0 5px rgba(0,201,167,0)} }
.ss-active-title { font-size:17px; font-weight:800; color:rgba(255,255,255,.92); margin-bottom:4px; }
.ss-active-range { font-size:13px; color:rgba(255,255,255,.45); }
.ss-timer { font-size:28px; font-weight:900; letter-spacing:-.04em; color:#fff; font-variant-numeric:tabular-nums; margin:10px 0 14px; }

/* toolbar */
.ss-bar {
    background:var(--ss-card); border:1px solid var(--ss-border);
    border-radius:20px; padding:14px 20px; margin-bottom:22px;
    display:flex; flex-wrap:wrap; align-items:center; justify-content:space-between; gap:12px;
}

/* buttons */
.ss-btn {
    display:inline-flex; align-items:center; gap:6px;
    height:38px; padding:0 16px; border-radius:10px;
    border:1px solid rgba(255,255,255,.1); background:rgba(255,255,255,.05);
    color:rgba(255,255,255,.72); font-size:13px; font-weight:600;
    cursor:pointer; text-decoration:none; white-space:nowrap; transition:all .12s;
}
.ss-btn:hover { background:rgba(255,255,255,.1); color:rgba(255,255,255,.92); border-color:rgba(255,255,255,.18); }
.ss-btn.primary { background:var(--ss-purple); border-color:var(--ss-purple); color:#fff; }
.ss-btn.primary:hover { background:#5c4ae3; border-color:#5c4ae3; }
.ss-btn.danger { background:rgba(237,76,120,.12); border-color:rgba(237,76,120,.28); color:#fb7185; }
.ss-btn.xs { height:30px; padding:0 10px; font-size:11.5px; border-radius:8px; }

/* week grid */
.ss-grid { display:grid; grid-template-columns:repeat(7,minmax(220px,1fr)); gap:12px; overflow-x:auto; padding-bottom:6px; }

.ss-day {
    background:var(--ss-card); border:1px solid var(--ss-border);
    border-radius:20px; overflow:hidden; display:flex; flex-direction:column;
}
.ss-day.is-today { border-color:rgba(109,92,255,.28); }
.ss-day-head {
    padding:12px 16px 10px; border-bottom:1px solid rgba(255,255,255,.055);
    background:rgba(255,255,255,.02);
    display:flex; align-items:center; justify-content:space-between;
}
.ss-day.is-today .ss-day-head { background:rgba(109,92,255,.06); border-bottom-color:rgba(109,92,255,.15); }
.ss-day-name { font-size:13px; font-weight:800; color:rgba(255,255,255,.92); }
.ss-day.is-today .ss-day-name { color:#c4b5fd; }
.ss-day-date { font-size:11px; color:var(--ss-muted); margin-top:1px; }
.ss-day-count {
    font-size:10.5px; font-weight:700; padding:3px 8px; border-radius:100px;
    background:rgba(109,92,255,.12); border:1px solid rgba(109,92,255,.22); color:#a78bfa;
}
.ss-day-body { padding:10px; flex:1; display:flex; flex-direction:column; gap:8px; }
.ss-empty-day {
    flex:1; display:flex; align-items:center; justify-content:center;
    color:rgba(255,255,255,.2); font-size:12.5px;
    border:1px dashed rgba(255,255,255,.07); border-radius:12px; min-height:64px;
}

/* coverage bar */
.ss-coverage { padding:10px 10px 0; }
.ss-cov-head {
    display:flex; align-items:center; justify-content:space-between;
    font-size:10.5px; color:var(--ss-muted); margin-bottom:5px; letter-spacing:.03em;
}
.ss-cov-head strong { color:rgba(255,255,255,.85); font-weight:700; }
.ss-cov-head.is-gap strong { color:#ffb4b4; }
.ss-cov-head.is-full strong { color:#7ee0c2; }
.ss-cov-bar {
    position:relative; height:14px; border-radius:6px; overflow:hidden;
    background:rgba(255,255,255,.04); border:1px solid rgba(255,255,255,.07);
    display:flex;
}
.ss-cov-seg {
    height:100%; border-right:1px solid rgba(0,0,0,.25);
    display:flex; align-items:center; justify-content:center;
    overflow:hidden; white-space:nowrap;
    font-size:9px; font-weight:700; color:rgba(0,0,0,.7);
    transition:filter .12s;
}
.ss-cov-seg:last-child { border-right:none; }
.ss-cov-seg:hover { filter:brightness(1.15); }
.ss-cov-seg.covered { background:rgba(0,201,167,.55); color:#00382d; }
.ss-cov-seg.open    { background:rgba(245,202,153,.32); color:rgba(245,202,153,.95); }
.ss-cov-seg.gap     {
    background:repeating-linear-gradient(45deg,
        rgba(237,76,120,.28) 0 6px,
        rgba(237,76,120,.10) 6px 12px);
    color:#ffb4b4;
}
.ss-cov-seg.overlap {
    background:rgba(109,92,255,.55); color:#1a1250;
    box-shadow:inset 0 0 0 1px rgba(255,255,255,.15);
}
.ss-cov-ticks {
    position:relative; height:12px; margin-top:4px;
    font-size:9px; color:rgba(255,255,255,.28); font-variant-numeric:tabular-nums;
}
.ss-cov-tick { position:absolute; transform:translateX(-50%); }
.ss-cov-tick:first-child { transform:translateX(0); }
.ss-cov-tick:last-child { transform:translateX(-100%); }
.ss-cov-legend {
    display:flex; gap:10px; flex-wrap:wrap;
    font-size:10px; color:var(--ss-muted); margin-top:6px;
}
.ss-cov-legend span { display:inline-flex; align-items:center; gap:4px; }
.ss-cov-legend i {
    width:8px; height:8px; border-radius:2px; display:inline-block;
    background:rgba(0,201,167,.55);
}
.ss-cov-legend i.open    { background:rgba(245,202,153,.5); }
.ss-cov-legend i.gap     { background:repeating-linear-gradient(45deg, rgba(237,76,120,.5) 0 3px, transparent 3px 6px); }
.ss-cov-legend i.overlap { background:rgba(109,92,255,.55); }

/* schedule template modal helpers */
.ss-tpl-row {
    display:grid; grid-template-columns:1.4fr .7fr .7fr .9fr auto;
    gap:8px; align-items:center;
    padding:9px 10px; border-radius:10px;
    background:rgba(255,255,255,.03); border:1px solid rgba(255,255,255,.06);
    margin-bottom:6px;
}
.ss-tpl-row.is-inactive { opacity:.55; }
.ss-tpl-row .form-select,
.ss-tpl-row .form-control { padding:6px 8px; font-size:12px; }
.ss-tpl-empty {
    text-align:center; padding:22px 12px; color:var(--ss-muted); font-size:12.5px;
    border:1px dashed rgba(255,255,255,.09); border-radius:10px;
}
.ss-tpl-week-toggle {
    display:flex; gap:4px; margin-bottom:10px; flex-wrap:wrap;
}
.ss-tpl-week-toggle button {
    padding:4px 10px; font-size:11px; font-weight:700;
    border-radius:100px; border:1px solid rgba(255,255,255,.12);
    background:rgba(255,255,255,.04); color:rgba(255,255,255,.7);
    cursor:pointer;
}
.ss-tpl-week-toggle button.is-active {
    background:rgba(109,92,255,.18); border-color:rgba(109,92,255,.4); color:#c4b5fd;
}
.ss-tpl-add {
    display:flex; align-items:center; gap:6px;
    padding:9px 12px; border-radius:10px;
    background:rgba(109,92,255,.08); border:1px dashed rgba(109,92,255,.35);
    color:#c4b5fd; font-weight:700; font-size:12px; cursor:pointer; width:100%;
    justify-content:center;
}
.ss-tpl-add:hover { background:rgba(109,92,255,.14); }

/* duration slider inside create modal */
.ss-dur-wrap { display:flex; align-items:center; gap:10px; }
.ss-dur-wrap input[type=range] {
    flex:1; -webkit-appearance:none; appearance:none;
    background:rgba(255,255,255,.08); height:4px; border-radius:4px; outline:none;
}
.ss-dur-wrap input[type=range]::-webkit-slider-thumb {
    -webkit-appearance:none; appearance:none;
    width:16px; height:16px; border-radius:50%;
    background:#a78bfa; cursor:pointer; border:0;
}
.ss-dur-wrap input[type=range]::-moz-range-thumb {
    width:16px; height:16px; border-radius:50%;
    background:#a78bfa; cursor:pointer; border:0;
}
.ss-dur-out {
    min-width:56px; text-align:right; font-size:13px; font-weight:800; color:#c4b5fd;
    font-variant-numeric:tabular-nums;
}
.ss-dur-derived {
    font-size:11px; color:var(--ss-muted); margin-top:5px;
    font-variant-numeric:tabular-nums;
}

/* shift card */
.ss-shift {
    background:rgba(255,255,255,.03); border:1px solid rgba(255,255,255,.07);
    border-radius:14px; padding:13px 13px 11px;
    transition:background .12s,border-color .12s,transform .1s;
}
.ss-shift:hover { background:rgba(109,92,255,.06); border-color:rgba(109,92,255,.2); transform:translateY(-1px); }
.ss-shift.is-active { border-color:rgba(0,201,167,.28); background:rgba(0,201,167,.05); }

.ss-shift-top { display:flex; align-items:flex-start; justify-content:space-between; gap:8px; margin-bottom:6px; }
.ss-shift-name { font-size:13px; font-weight:800; color:rgba(255,255,255,.9); line-height:1.2; }
.ss-shift-time { display:inline-flex; align-items:center; gap:5px; font-size:12px; color:rgba(255,255,255,.4); font-variant-numeric:tabular-nums; margin-bottom:7px; }

/* badges */
.ss-badge {
    display:inline-flex; align-items:center; gap:4px;
    font-size:10px; font-weight:800; text-transform:uppercase; letter-spacing:.05em;
    padding:3px 8px; border-radius:100px; white-space:nowrap; border:1px solid transparent;
}
.ss-badge.open      { color:rgba(255,255,255,.55); background:rgba(255,255,255,.06); border-color:rgba(255,255,255,.1); }
.ss-badge.assigned  { color:#a78bfa; background:rgba(109,92,255,.14); border-color:rgba(109,92,255,.28); }
.ss-badge.active    { color:var(--ss-teal); background:rgba(0,201,167,.12); border-color:rgba(0,201,167,.28); }
.ss-badge.active::before { content:''; width:5px; height:5px; border-radius:50%; background:var(--ss-teal); display:inline-block; animation:ssDot 1.8s infinite; }
.ss-badge.completed { color:rgba(255,255,255,.55); background:rgba(255,255,255,.07); border-color:rgba(255,255,255,.1); }
.ss-badge.cancelled { color:var(--ss-red); background:rgba(237,76,120,.12); border-color:rgba(237,76,120,.28); }
.ss-badge.past      { color:rgba(255,255,255,.3); background:rgba(255,255,255,.04); border-color:rgba(255,255,255,.07); }
.ss-badge.resume    { color:var(--ss-teal); background:rgba(0,201,167,.12); border-color:rgba(0,201,167,.28); }

.ss-who { display:flex; align-items:center; gap:7px; color:rgba(255,255,255,.52); font-size:12px; margin-bottom:8px; }
.ss-who-list { flex-wrap:wrap; align-items:flex-start; }
.ss-person-chip { display:inline-flex; align-items:center; gap:5px; max-width:100%; padding:2px 8px 2px 2px; border-radius:999px; background:rgba(109,92,255,.14); border:1px solid rgba(109,92,255,.28); color:rgba(255,255,255,.78); font-weight:700; }
.ss-person-more { display:inline-flex; align-items:center; height:24px; padding:0 8px; border-radius:999px; background:rgba(255,255,255,.07); border:1px solid rgba(255,255,255,.1); color:rgba(255,255,255,.58); font-weight:800; font-size:11px; }
.ss-av  { width:22px; height:22px; border-radius:50%; object-fit:cover; border:1px solid rgba(255,255,255,.1); flex-shrink:0; }
.ss-av-fb { display:inline-flex; align-items:center; justify-content:center; width:22px; height:22px; border-radius:50%; background:rgba(109,92,255,.2); color:#c4b5fd; font-weight:900; font-size:10px; flex-shrink:0; }

.ss-acts { display:flex; flex-wrap:wrap; gap:5px; }
.ss-note { font-size:11.5px; color:rgba(255,255,255,.3); font-style:italic; }

/* stats */
.ss-stats { background:var(--ss-card); border:1px solid var(--ss-border); border-radius:20px; overflow:hidden; box-shadow:0 4px 28px rgba(0,0,0,.24); }
.ss-stats .s-head { display:flex; align-items:center; justify-content:space-between; gap:14px; flex-wrap:wrap; padding:18px 24px; border-bottom:1px solid rgba(255,255,255,.06); background:rgba(255,255,255,.02); }
.ss-stats .s-title { font-size:15px; font-weight:800; color:rgba(255,255,255,.9); display:flex; align-items:center; gap:8px; }
.ss-stats .s-sub { font-size:11.5px; color:rgba(255,255,255,.36); margin-top:3px; }
.ss-stat-tabs { display:flex; align-items:center; gap:7px; flex-wrap:wrap; }
.ss-stat-tab { height:36px; padding:0 13px; border-radius:11px; border:1px solid rgba(255,255,255,.09); background:rgba(255,255,255,.04); color:rgba(255,255,255,.72); font-size:12px; font-weight:800; cursor:pointer; }
.ss-stat-tab:hover { background:rgba(255,255,255,.08); color:#fff; }
.ss-stat-tab.is-active { background:rgba(109,92,255,.18); border-color:rgba(109,92,255,.36); color:#c4b5fd; }
.ss-kpis { display:grid; grid-template-columns:repeat(5,minmax(120px,1fr)); gap:10px; padding:14px 18px; border-bottom:1px solid rgba(255,255,255,.055); }
.ss-kpi { border:1px solid rgba(255,255,255,.07); background:rgba(255,255,255,.035); border-radius:15px; padding:13px 14px; }
.ss-kpi .k-label { font-size:10px; font-weight:900; text-transform:uppercase; letter-spacing:.07em; color:rgba(255,255,255,.34); margin-bottom:7px; }
.ss-kpi .k-value { font-size:20px; font-weight:950; color:rgba(255,255,255,.92); line-height:1; }
.ss-kpi.total { border-color:rgba(109,92,255,.18); }
.ss-kpi.done { border-color:rgba(0,201,167,.20); }
.ss-kpi.active { border-color:rgba(0,201,167,.28); background:rgba(0,201,167,.045); }
.ss-kpi.missed { border-color:rgba(237,76,120,.20); }
.ss-kpi.hours { border-color:rgba(245,202,153,.18); }
.ss-stats table { width:100%; border-collapse:collapse; }
.ss-stats thead th { padding:10px 16px; text-align:left; font-size:10.5px; font-weight:800; text-transform:uppercase; letter-spacing:.06em; color:rgba(255,255,255,.3); border-bottom:1px solid rgba(255,255,255,.05); }
.ss-stats tbody td { padding:13px 16px; border-bottom:1px solid rgba(255,255,255,.04); color:rgba(255,255,255,.75); font-size:13px; }
.ss-stats tbody tr:last-child td { border-bottom:0; }
.ss-stats tbody tr:hover td { background:rgba(255,255,255,.025); }
.s-employee { display:flex; align-items:center; gap:10px; }
.s-employee .ss-av, .s-employee .ss-av-fb { width:32px; height:32px; }
.s-name { font-weight:850; color:rgba(255,255,255,.9); line-height:1.1; }
.s-email { font-size:11px; color:rgba(255,255,255,.35); margin-top:3px; }
.s-progress { min-width:170px; }
.s-progress-line { height:7px; border-radius:999px; background:rgba(255,255,255,.07); overflow:hidden; margin-top:6px; }
.s-progress-fill { height:100%; border-radius:999px; background:linear-gradient(90deg, rgba(0,201,167,.75), rgba(109,92,255,.85)); }
.s-progress-text { font-size:11px; color:rgba(255,255,255,.42); }
.ss-stats code { background:rgba(255,255,255,.07); padding:2px 7px; border-radius:6px; font-size:12px; color:rgba(255,255,255,.72); }
@media(max-width:900px){ .ss-kpis{grid-template-columns:repeat(2,minmax(0,1fr));} .ss-stats{overflow-x:auto;} .ss-stats table{min-width:760px;} }

/* modals */

#ssEndActionModal .modal-content { background:#25282a; border:1px solid rgba(255,255,255,.10); border-radius:20px; box-shadow:0 28px 70px rgba(0,0,0,.55); overflow:hidden; }
#ssEndActionModal .ss-end-body { padding:26px 24px 22px; }
#ssEndActionModal .ss-end-top { display:flex; gap:14px; align-items:flex-start; padding-right:34px; }
#ssEndActionModal .ss-end-icon { width:42px; height:42px; border-radius:14px; flex:0 0 auto; display:flex; align-items:center; justify-content:center; background:rgba(237,76,120,.14); border:1px solid rgba(237,76,120,.30); color:#ff6b96; }
#ssEndActionModal .ss-end-title { margin:0 0 6px; color:rgba(255,255,255,.94); font-size:18px; font-weight:900; }
#ssEndActionModal .ss-end-text { margin:0; color:rgba(255,255,255,.50); font-size:13px; line-height:1.55; }
#ssEndActionModal .ss-end-close { position:absolute; top:16px; right:16px; width:32px; height:32px; border-radius:11px; background:rgba(255,255,255,.05); border:1px solid rgba(255,255,255,.08); color:rgba(255,255,255,.42); display:flex; align-items:center; justify-content:center; cursor:pointer; }
#ssEndActionModal .ss-end-close:hover { background:rgba(255,255,255,.10); color:rgba(255,255,255,.82); }
#ssEndActionModal .ss-end-footer { display:flex; gap:10px; justify-content:flex-end; padding:16px 24px 22px; border-top:1px solid rgba(255,255,255,.07); background:rgba(255,255,255,.02); }
#ssEndActionModal .ss-end-btn { min-width:126px; height:42px; border-radius:12px; border:1px solid rgba(255,255,255,.10); background:rgba(255,255,255,.055); color:rgba(255,255,255,.76); font-size:13px; font-weight:800; cursor:pointer; transition:all .12s; }
#ssEndActionModal .ss-end-btn:hover { background:rgba(255,255,255,.10); color:#fff; }
#ssEndActionModal .ss-end-btn.danger { background:rgba(237,76,120,.16); border-color:rgba(237,76,120,.36); color:#ff6b96; }
#ssEndActionModal .ss-end-btn.danger:hover { background:rgba(237,76,120,.25); border-color:rgba(237,76,120,.50); color:#ff8aad; }
#ssEndActionModal.ss-end-fallback-open { display:block; background:rgba(0,0,0,.62); }
#ssEndActionModal.ss-end-fallback-open .modal-dialog { min-height:100%; display:flex; align-items:center; }
#ssCreateModal .modal-content, #ssAssignModal .modal-content {
    background:#1c1f21; border:1px solid rgba(255,255,255,.08); border-radius:20px;
    box-shadow:0 32px 80px rgba(0,0,0,.55); color:rgba(255,255,255,.88);
}
#ssCreateModal .modal-header, #ssCreateModal .modal-footer,
#ssAssignModal .modal-header, #ssAssignModal .modal-footer { border-color:rgba(255,255,255,.07); }
#ssCreateModal .modal-title, #ssAssignModal .modal-title { color:rgba(255,255,255,.92); font-weight:800; }
#ssCreateModal .form-label, #ssAssignModal .form-label { color:rgba(255,255,255,.55); font-size:12px; font-weight:600; }
#ssCreateModal .btn-close, #ssAssignModal .btn-close { filter:invert(1) grayscale(100%) brightness(70%); }
#ssCreateModal .form-control, #ssCreateModal .form-select,
#ssAssignModal .form-control, #ssAssignModal .form-select {
    background:rgba(255,255,255,.04) !important; border:1px solid rgba(255,255,255,.09) !important;
    color:rgba(255,255,255,.85) !important; border-radius:10px !important;
}
#ssCreateModal .form-control:focus, #ssCreateModal .form-select:focus,
#ssAssignModal .form-control:focus, #ssAssignModal .form-select:focus {
    border-color:rgba(109,92,255,.45) !important; box-shadow:0 0 0 3px rgba(109,92,255,.1) !important;
}


/* custom dark admin select */
.ss-admin-select { position:relative; }
.ss-admin-native { position:absolute !important; opacity:0 !important; pointer-events:none !important; width:1px !important; height:1px !important; }
.ss-admin-select__button {
    width:100%; min-height:44px; border-radius:12px; border:1px solid rgba(109,92,255,.34);
    background:rgba(255,255,255,.045); color:rgba(255,255,255,.86); padding:8px 12px;
    display:flex; align-items:center; justify-content:space-between; gap:12px; cursor:pointer;
    transition:background .14s,border-color .14s,box-shadow .14s;
}
.ss-admin-select__button:hover, .ss-admin-select.is-open .ss-admin-select__button {
    background:rgba(109,92,255,.10); border-color:rgba(109,92,255,.55); box-shadow:0 0 0 3px rgba(109,92,255,.10);
}
.ss-admin-select__current { display:flex; align-items:center; gap:10px; min-width:0; flex:1 1 auto; text-align:left; }
.ss-admin-select__icon {
    width:28px; height:28px; border-radius:9px; display:inline-flex; align-items:center; justify-content:center;
    background:rgba(109,92,255,.16); border:1px solid rgba(109,92,255,.26); color:#c4b5fd; flex:0 0 auto; overflow:hidden;
}
.ss-admin-select__icon img { width:100%; height:100%; object-fit:cover; display:block; }
.ss-admin-select__icon.is-open { background:rgba(255,255,255,.06); border-color:rgba(255,255,255,.10); color:rgba(255,255,255,.55); }
.ss-admin-select__text { min-width:0; display:flex; flex-direction:column; align-items:flex-start; justify-content:center; line-height:1.15; text-align:left; }
.ss-admin-select__name { color:rgba(255,255,255,.9); font-weight:850; font-size:13px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; text-align:left; }
.ss-admin-select__meta { margin-top:3px; color:rgba(255,255,255,.36); font-size:11px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; text-align:left; }
.ss-admin-select__chev { color:rgba(255,255,255,.42); font-size:12px; flex:0 0 auto; transition:transform .14s; }
.ss-admin-select.is-open .ss-admin-select__chev { transform:rotate(180deg); }
.ss-admin-select__menu {
    position:absolute; z-index:1090; left:0; right:0; top:calc(100% + 8px); display:none; padding:6px;
    max-height:260px; overflow:auto; border-radius:14px; background:#25282a; border:1px solid rgba(255,255,255,.10);
    box-shadow:0 18px 48px rgba(0,0,0,.48);
}
.ss-admin-select.is-open .ss-admin-select__menu { display:block; }
.ss-admin-select__menu::-webkit-scrollbar { width:6px; }
.ss-admin-select__menu::-webkit-scrollbar-thumb { background:rgba(255,255,255,.14); border-radius:999px; }
.ss-admin-select__option {
    width:100%; border:0; border-radius:11px; background:transparent; color:rgba(255,255,255,.78); padding:8px 9px;
    display:flex; align-items:center; gap:10px; text-align:left; cursor:pointer; transition:background .12s,color .12s;
}
.ss-admin-select__option:hover { background:rgba(109,92,255,.14); color:#fff; }
.ss-admin-select__option.is-selected { background:rgba(109,92,255,.22); color:#c4b5fd; }
.ss-admin-select__option .ss-admin-select__icon { width:26px; height:26px; border-radius:8px; }
.ss-admin-select__option .ss-admin-select__name { font-size:12.5px; }
.ss-admin-select__option .ss-admin-select__meta { font-size:10.5px; }

/* time/date pickers */
.ss-time-picker { position:relative; }
.ss-time-button { width:100%; min-height:42px; border-radius:10px; display:inline-flex; align-items:center; justify-content:space-between; gap:.75rem; background:rgba(255,255,255,.04); border:1px solid rgba(255,255,255,.09); color:rgba(255,255,255,.86); padding:.55rem .85rem; font-weight:800; font-variant-numeric:tabular-nums; }
.ss-time-button:hover { background:rgba(255,255,255,.075); color:#fff; }
.ss-time-popover { position:absolute; z-index:1086; top:calc(100% + 8px); left:0; width:210px; border-radius:16px; padding:12px; background:#25282a; border:1px solid rgba(255,255,255,.10); box-shadow:0 18px 48px rgba(0,0,0,.45); display:none; }
.ss-time-picker.is-open .ss-time-popover { display:block; }
.ss-time-cols { display:grid; grid-template-columns:1fr 1fr; gap:8px; }
.ss-time-col { max-height:210px; overflow-y:auto; }
.ss-time-col::-webkit-scrollbar { width:6px; } .ss-time-col::-webkit-scrollbar-thumb { background:rgba(255,255,255,.14); border-radius:999px; }
.ss-time-opt { width:100%; border:0; border-radius:10px; background:transparent; color:rgba(255,255,255,.74); padding:.5rem .35rem; font-weight:900; font-variant-numeric:tabular-nums; }
.ss-time-opt:hover { background:rgba(109,92,255,.15); color:#fff; }
.ss-time-opt.is-selected { background:rgba(109,92,255,.95); color:#fff; }

.ss-date-wrap { position:relative; }
.ss-date-button { width:100%; min-height:40px; border-radius:10px; display:inline-flex; align-items:center; justify-content:space-between; gap:.75rem; background:rgba(255,255,255,.04); border:1px solid rgba(255,255,255,.09); color:rgba(255,255,255,.86); padding:.55rem .85rem; font-weight:800; }
.ss-date-button:hover { background:rgba(255,255,255,.075); color:#fff; }
.ss-date-popover { position:absolute; z-index:1085; top:calc(100% + 8px); right:0; width:292px; padding:12px; border-radius:16px; background:#25282a; border:1px solid rgba(255,255,255,.10); box-shadow:0 18px 48px rgba(0,0,0,.45); color:rgba(255,255,255,.86); display:none; }
.ss-date-wrap.is-open .ss-date-popover { display:block; }
.ss-date-head { display:flex; align-items:center; justify-content:space-between; gap:.5rem; margin-bottom:10px; }
.ss-date-title { font-weight:900; color:#fff; font-size:.9rem; }
.ss-date-nav { border:0; width:32px; height:32px; border-radius:10px; background:rgba(255,255,255,.055); color:rgba(255,255,255,.74); display:inline-flex; align-items:center; justify-content:center; }
.ss-date-nav:hover { background:rgba(109,92,255,.18); color:#fff; }
.ss-date-weekdays,.ss-date-days { display:grid; grid-template-columns:repeat(7,1fr); gap:4px; }
.ss-date-weekdays span { text-align:center; color:rgba(255,255,255,.42); font-size:.72rem; font-weight:900; padding:4px 0; }
.ss-date-day { height:34px; border:0; border-radius:10px; background:transparent; color:rgba(255,255,255,.72); font-size:.82rem; font-weight:800; }
.ss-date-day:hover { background:rgba(109,92,255,.15); color:#fff; }
.ss-date-day.is-muted { color:rgba(255,255,255,.25); }
.ss-date-day.is-today { box-shadow:inset 0 0 0 1px rgba(109,92,255,.42); color:#c4b5fd; }
.ss-date-day.is-selected { background:rgba(109,92,255,.95); color:#fff; }
.ss-date-foot { display:flex; justify-content:space-between; margin-top:10px; padding-top:10px; border-top:1px solid rgba(255,255,255,.07); }
.ss-date-link { border:0; background:transparent; color:#9f92ff; font-weight:900; font-size:.78rem; }
.ss-date-link:hover { color:#fff; }



/* ── Review-style dashboard polish ───────────────────────────── */
:root {
    --ss-bg:     #1e2022;
    --ss-card:   #25282a;
    --ss-card2:  #25282a;
    --ss-border: rgba(255,255,255,.07);
    --ss-text:   rgba(255,255,255,.88);
    --ss-muted:  rgba(255,255,255,.42);
    --ss-purple: #6d5cff;
    --ss-purple2:#5c4ae3;
    --ss-lilac:  #c4b5fd;
    --ss-teal:   #00c9a7;
    --ss-red:    #ed4c78;
    --ss-amber:  #f5ca99;
}
.ss-wrap { color:var(--ss-text); }
.ss-hero {
    border-radius:20px;
    border:1px solid rgba(255,255,255,.07);
    background:#25282a;
    padding:14px 18px;
    margin-bottom:12px;
    box-shadow:0 2px 20px rgba(0,0,0,.22);
    overflow:visible;
}
.ss-hero::before { display:none; }
.ss-hero-inner {
    display:flex;
    align-items:center;
    justify-content:space-between;
    gap:18px;
    flex-wrap:wrap;
}
.ss-hero-left {
    display:flex;
    align-items:center;
    gap:16px;
    min-width:280px;
    flex:1 1 420px;
}
.ss-hero-icon {
    width:44px;
    height:44px;
    border-radius:13px;
    background:linear-gradient(135deg,rgba(109,92,255,.25),rgba(176,92,255,.15));
    border:1px solid rgba(109,92,255,.25);
    display:flex;
    align-items:center;
    justify-content:center;
    font-size:1.3rem;
    color:var(--ss-lilac);
    flex-shrink:0;
}
.ss-hero-kicker {
    display:inline-flex;
    align-items:center;
    gap:6px;
    padding:4px 10px;
    border-radius:99px;
    border:1px solid rgba(109,92,255,.28);
    background:rgba(109,92,255,.13);
    color:var(--ss-lilac);
    font-size:.68rem;
    font-weight:900;
    letter-spacing:.07em;
    text-transform:uppercase;
    margin-bottom:7px;
}
.ss-hero h2 { font-size:1.2rem; font-weight:950; color:rgba(255,255,255,.92); margin:0; }
.ss-hero-sub { font-size:.82rem; color:rgba(255,255,255,.4); margin:5px 0 0; }
.ss-hero-badge { display:none; }
.ss-hero-active {
    flex:1 1 520px;
    max-width:760px;
}
.ss-active-card {
    min-height:64px;
    padding:10px 12px;
    border-radius:16px;
    background:rgba(255,255,255,.035);
    border:1px solid rgba(255,255,255,.08);
    display:flex;
    align-items:center;
    justify-content:space-between;
    gap:14px;
    flex-wrap:wrap;
}
.ss-active-card.is-on {
    border-color:rgba(0,201,167,.28);
    background:rgba(0,201,167,.06);
}
.ss-active-main { min-width:200px; flex:1 1 230px; }
.ss-active-actions { display:flex; align-items:center; gap:10px; flex-wrap:wrap; }
.ss-active-tag { margin-bottom:5px; font-size:.66rem; letter-spacing:.07em; }
.ss-active-title { font-size:.95rem; margin-bottom:2px; }
.ss-active-range { font-size:.78rem; color:rgba(255,255,255,.45); }
.ss-timer {
    margin:0;
    padding:7px 12px;
    border-radius:12px;
    border:1px solid rgba(255,255,255,.08);
    background:rgba(255,255,255,.04);
    font-size:1rem;
    letter-spacing:0;
    line-height:1;
}
.ss-off-tag { color:rgba(255,255,255,.35); font-size:.68rem; text-transform:uppercase; letter-spacing:.07em; font-weight:800; margin-bottom:5px; }
.ss-off-title { font-size:.95rem; font-weight:850; color:rgba(255,255,255,.88); margin-bottom:2px; }
.ss-off-text { font-size:.78rem; color:rgba(255,255,255,.38); }
.ss-bar {
    background:#25282a;
    border:1px solid rgba(255,255,255,.07);
    border-radius:16px;
    padding:12px 16px;
    margin-bottom:16px;
    box-shadow:0 2px 16px rgba(0,0,0,.18);
}
.ss-btn {
    height:34px;
    padding:0 14px;
    border-radius:10px;
    border-color:rgba(255,255,255,.09);
    background:rgba(255,255,255,.04);
    color:rgba(255,255,255,.68);
    font-size:.78rem;
    font-weight:800;
}
.ss-btn.primary { background:rgba(109,92,255,.20); border-color:rgba(109,92,255,.40); color:var(--ss-lilac); }
.ss-btn.primary:hover { background:rgba(109,92,255,.30); border-color:rgba(109,92,255,.52); color:#fff; }
.ss-btn.danger { background:rgba(237,76,120,.10); border-color:rgba(237,76,120,.28); color:#ed4c78; }
.ss-btn.warning { background:rgba(245,202,153,.10); border-color:rgba(245,202,153,.30); color:#f5ca99; }
.ss-note-locked { color:#f5ca99; font-weight:800; max-width:126px; white-space:normal; line-height:1.15; }
.ss-btn.unassign { background:rgba(245,202,153,.10); border-color:rgba(245,202,153,.34); color:#ffd6a6; }
.ss-btn.unassign:hover { background:rgba(245,202,153,.18); border-color:rgba(245,202,153,.50); color:#fff0d8; }
.ss-btn.unassign.is-locked { opacity:1; cursor:pointer; background:rgba(245,202,153,.07); border-color:rgba(245,202,153,.25); color:#f5ca99; }
#ssInfoActionModal .modal-content { background:#25282a; border:1px solid rgba(255,255,255,.10); border-radius:20px; box-shadow:0 28px 70px rgba(0,0,0,.55); overflow:hidden; }
#ssInfoActionModal .ss-info-body { padding:26px 24px 22px; }
#ssInfoActionModal .ss-info-top { display:flex; gap:14px; align-items:flex-start; padding-right:34px; }
#ssInfoActionModal .ss-info-icon { width:42px; height:42px; border-radius:14px; flex:0 0 auto; display:flex; align-items:center; justify-content:center; background:rgba(245,202,153,.13); border:1px solid rgba(245,202,153,.32); color:#f5ca99; }
#ssInfoActionModal .ss-info-title { margin:0 0 6px; color:rgba(255,255,255,.94); font-size:18px; font-weight:900; }
#ssInfoActionModal .ss-info-text { margin:0; color:rgba(255,255,255,.56); font-size:13px; line-height:1.55; }
#ssInfoActionModal .ss-info-close { position:absolute; top:16px; right:16px; width:32px; height:32px; border-radius:11px; background:rgba(255,255,255,.05); border:1px solid rgba(255,255,255,.08); color:rgba(255,255,255,.42); display:flex; align-items:center; justify-content:center; cursor:pointer; }
#ssInfoActionModal .ss-info-close:hover { background:rgba(255,255,255,.10); color:rgba(255,255,255,.82); }
#ssInfoActionModal .ss-info-footer { display:flex; gap:10px; justify-content:flex-end; padding:16px 24px 22px; border-top:1px solid rgba(255,255,255,.07); background:rgba(255,255,255,.02); }
#ssInfoActionModal .ss-info-btn { min-width:126px; height:42px; border-radius:12px; border:1px solid rgba(255,255,255,.10); background:rgba(255,255,255,.055); color:rgba(255,255,255,.78); font-size:13px; font-weight:800; cursor:pointer; transition:all .12s; }
#ssInfoActionModal .ss-info-btn:hover { background:rgba(255,255,255,.10); color:#fff; }
#ssInfoActionModal.ss-info-fallback-open { display:block; background:rgba(0,0,0,.62); }
#ssInfoActionModal.ss-info-fallback-open .modal-dialog { min-height:100%; display:flex; align-items:center; }

.ss-btn.xs { height:28px; padding:0 10px; font-size:.72rem; }
.ss-grid {
    grid-template-columns:repeat(7,minmax(285px,1fr));
    gap:12px;
    overflow-x:auto;
    padding-bottom:8px;
}
.ss-day {
    background:#25282a;
    border:1px solid rgba(255,255,255,.07);
    border-radius:20px;
    box-shadow:0 2px 18px rgba(0,0,0,.18);
}
.ss-day-head { padding:11px 14px 9px; background:rgba(255,255,255,.03); }
.ss-day-name { font-size:.82rem; }
.ss-day-date { font-size:.72rem; }
.ss-day-count { color:var(--ss-lilac); background:rgba(109,92,255,.12); border-color:rgba(109,92,255,.28); }
.ss-day-body { padding:9px; gap:7px; }
.ss-shift { border-radius:14px; padding:10px 11px; background:rgba(255,255,255,.035); }
.ss-shift-top { margin-bottom:5px; }
.ss-shift-name { font-size:.82rem; }
.ss-shift-time { font-size:.76rem; margin-bottom:6px; }
.ss-who { margin-bottom:7px; font-size:.76rem; }
.ss-badge.assigned { color:var(--ss-lilac); background:rgba(109,92,255,.14); border-color:rgba(109,92,255,.28); }
.ss-badge.open { color:rgba(255,255,255,.55); background:rgba(255,255,255,.06); border-color:rgba(255,255,255,.1); }
.ss-badge.completed { color:var(--ss-teal); background:rgba(0,201,167,.10); border-color:rgba(0,201,167,.24); }
.ss-badge.past { color:var(--ss-amber); background:rgba(245,202,153,.10); border-color:rgba(245,202,153,.24); }
.ss-badge.resume { color:var(--ss-teal); background:rgba(0,201,167,.12); border-color:rgba(0,201,167,.28); }
.ss-stats { background:#25282a; border:1px solid rgba(255,255,255,.07); border-radius:20px; box-shadow:0 4px 32px rgba(0,0,0,.28); }
.ss-stats .s-head { padding:14px 18px; background:rgba(255,255,255,.03); }
.ss-stats .s-title { font-size:.95rem; }
.ss-stats thead th { padding:11px 16px; font-size:.68rem; color:rgba(255,255,255,.35); }
.ss-stats tbody td { padding:12px 16px; font-size:.84rem; }
#ssCreateModal .modal-content, #ssAssignModal .modal-content { background:#25282a; border:1px solid rgba(255,255,255,.10); }
#ssCreateModal .modal-header, #ssCreateModal .modal-footer, #ssAssignModal .modal-header, #ssAssignModal .modal-footer { background:rgba(255,255,255,.02); }

/* refined shift cards */
.ss-shift {
    padding:12px 12px 12px !important;
    min-height:188px;
    height:188px;
    display:flex;
    flex-direction:column;
    overflow:visible;
}
.ss-shift-headline {
    display:flex;
    align-items:flex-start;
    justify-content:space-between;
    gap:10px;
    margin-bottom:9px;
}
.ss-shift-name {
    padding-top:1px;
    line-height:1.25;
    display:-webkit-box;
    -webkit-line-clamp:2;
    -webkit-box-orient:vertical;
    overflow:hidden;
}
.ss-shift-meta {
    display:flex;
    flex-direction:column;
    gap:6px;
    margin-bottom:10px;
    min-height:46px;
}
.ss-shift-time,
.ss-who {
    margin:0 !important;
    min-height:20px;
}
.ss-shift-time i,
.ss-who > i {
    width:18px;
    min-width:18px;
    text-align:center;
    color:rgba(255,255,255,.30) !important;
}
.ss-who span {
    overflow:hidden;
    text-overflow:ellipsis;
    white-space:nowrap;
}
.ss-who-list{display:flex!important;align-items:center!important;gap:6px!important;flex-wrap:wrap!important;min-height:auto!important;}
.ss-who-list .ss-person-chip{display:inline-flex!important;align-items:center!important;gap:5px!important;max-width:100%!important;flex:0 1 auto!important;}
.ss-who-list .ss-person-chip span{white-space:nowrap!important;overflow:visible!important;text-overflow:clip!important;}
.ss-who-list .ss-av,.ss-who-list .ss-av-fb{width:22px!important;height:22px!important;flex:0 0 22px!important;}
.ss-acts {
    display:flex !important;
    align-items:center;
    justify-content:space-between;
    gap:8px !important;
    margin-top:auto;
}
.ss-acts-main,
.ss-acts-admin {
    display:flex;
    align-items:center;
    gap:6px;
    flex-wrap:wrap;
}
.ss-acts-main { min-width:0; }
.ss-acts-admin { justify-content:flex-end; margin-left:auto; }
.ss-note {
    min-height:28px;
    display:inline-flex;
    align-items:center;
    color:rgba(255,255,255,.34);
    white-space:nowrap;
}
.ss-badge {
    flex-shrink:0;
}

.ss-acts .ss-btn.xs {
    min-height:28px;
    padding:5px 10px;
    line-height:1.1;
}
@media (max-width: 575px) {
    .ss-acts { align-items:flex-start; flex-direction:column; }
    .ss-acts-admin { justify-content:flex-start; margin-left:0; }
}

@media (max-width:991px) {
    .ss-hero { padding:16px; }
    .ss-hero-left, .ss-hero-active { flex-basis:100%; }
    .ss-grid { grid-template-columns:repeat(7,82vw); }
}



/* ── Calendar layout refresh ─────────────────────────────────── */
.ss-grid {
    display:grid;
    grid-template-columns:repeat(7,minmax(300px,1fr));
    gap:14px;
    overflow-x:auto;
    padding:0 2px 10px;
    align-items:stretch;
}
.ss-grid::-webkit-scrollbar { height:10px; }
.ss-grid::-webkit-scrollbar-track { background:rgba(255,255,255,.035); border-radius:999px; }
.ss-grid::-webkit-scrollbar-thumb { background:rgba(255,255,255,.16); border-radius:999px; }
.ss-day {
    min-height:620px;
    background:linear-gradient(180deg,rgba(255,255,255,.035),rgba(255,255,255,.018));
    border:1px solid rgba(255,255,255,.075);
    border-radius:18px;
    overflow:hidden;
}
.ss-day.is-today {
    border-color:rgba(109,92,255,.48);
    box-shadow:0 0 0 1px rgba(109,92,255,.16),0 16px 34px rgba(0,0,0,.22);
}
.ss-day-head {
    min-height:64px;
    padding:13px 16px;
    background:rgba(255,255,255,.035);
    border-bottom:1px solid rgba(255,255,255,.075);
}
.ss-day.is-today .ss-day-head {
    background:linear-gradient(135deg,rgba(109,92,255,.16),rgba(109,92,255,.045));
}
.ss-day-name { font-size:.86rem; font-weight:950; }
.ss-day-date { font-size:.76rem; color:rgba(255,255,255,.38); }
.ss-day-count { min-width:26px; height:26px; display:inline-flex; align-items:center; justify-content:center; padding:0 8px; }
.ss-day-body {
    padding:10px;
    gap:10px;
    background:repeating-linear-gradient(
        to bottom,
        rgba(255,255,255,.018) 0,
        rgba(255,255,255,.018) 71px,
        rgba(255,255,255,.042) 72px
    );
}
.ss-shift {
    position:relative;
    min-height:178px !important;
    height:auto !important;
    display:flex;
    flex-direction:column;
    padding:13px 13px 12px 15px !important;
    border-radius:15px;
    background:rgba(255,255,255,.04);
    border:1px solid rgba(255,255,255,.09);
    box-shadow:0 10px 22px rgba(0,0,0,.14);
}
.ss-shift::before {
    content:'';
    position:absolute;
    left:0;
    top:12px;
    bottom:12px;
    width:3px;
    border-radius:0 999px 999px 0;
    background:rgba(255,255,255,.16);
}
.ss-shift:hover { transform:none; }
.ss-shift-open {
    border-color:rgba(255,255,255,.12) !important;
    background:rgba(255,255,255,.038) !important;
}
.ss-shift-open::before { background:rgba(255,255,255,.38); }
.ss-shift-assigned {
    border-color:rgba(109,92,255,.40) !important;
    background:rgba(109,92,255,.075) !important;
}
.ss-shift-assigned::before { background:#6d5cff; }
.ss-shift-active,
.ss-shift.is-active {
    border-color:rgba(0,201,167,.45) !important;
    background:rgba(0,201,167,.08) !important;
}
.ss-shift-active::before,
.ss-shift.is-active::before { background:#00c9a7; }
.ss-shift-past {
    border-color:rgba(245,202,153,.32) !important;
    background:rgba(245,202,153,.055) !important;
}
.ss-shift-past::before { background:#f5ca99; }
.ss-shift-completed {
    border-color:rgba(0,201,167,.30) !important;
    background:rgba(0,201,167,.045) !important;
}
.ss-shift-completed::before { background:#00c9a7; }
.ss-shift-resume,
.ss-shift-paused {
    border-color:rgba(0,201,167,.38) !important;
    background:rgba(0,201,167,.06) !important;
}
.ss-shift-resume::before,
.ss-shift-paused::before { background:#00c9a7; }
.ss-shift-cancelled {
    border-color:rgba(237,76,120,.34) !important;
    background:rgba(237,76,120,.055) !important;
}
.ss-shift-cancelled::before { background:#ed4c78; }
.ss-shift-headline { margin-bottom:10px; }
.ss-shift-name { font-size:.86rem; font-weight:950; }
.ss-shift-meta { min-height:52px; gap:7px; }
.ss-acts { min-height:32px; align-items:flex-end !important; margin-top:auto; padding-top:10px; }
.ss-week-form { margin:0; }
.ss-week-form .ss-date-wrap { min-width:160px; }
.ss-bar .ss-date-button { min-width:160px; }
.s-head .ss-week-form .ss-date-wrap { min-width:150px; }
@media (max-width:991px) {
    .ss-grid { grid-template-columns:repeat(7,84vw); }
    .ss-day { min-height:560px; }
}


/* FORCE NEW TEAM STATS DASHBOARD */
.ss-team-dashboard{background:#25282a!important;border:1px solid rgba(255,255,255,.07)!important;border-radius:22px!important;box-shadow:0 4px 32px rgba(0,0,0,.28)!important;overflow:hidden!important;margin-top:18px!important;}
.ss-team-head{display:flex!important;align-items:center!important;justify-content:space-between!important;gap:14px!important;flex-wrap:wrap!important;padding:16px 18px!important;border-bottom:1px solid rgba(255,255,255,.06)!important;background:rgba(255,255,255,.025)!important;}
.ss-team-title-wrap{display:flex!important;align-items:center!important;gap:12px!important;}
.ss-team-icon{width:38px!important;height:38px!important;border-radius:13px!important;background:rgba(109,92,255,.16)!important;border:1px solid rgba(109,92,255,.30)!important;color:#9b8cff!important;display:flex!important;align-items:center!important;justify-content:center!important;font-size:1rem!important;}
.ss-team-title{font-size:1rem!important;font-weight:950!important;color:rgba(255,255,255,.92)!important;line-height:1.1!important;}
.ss-team-sub{font-size:.74rem!important;color:rgba(255,255,255,.38)!important;margin-top:4px!important;}
.ss-team-kpis{display:grid!important;grid-template-columns:repeat(5,minmax(130px,1fr))!important;gap:10px!important;padding:14px 18px!important;border-bottom:1px solid rgba(255,255,255,.055)!important;}
.ss-team-kpi{min-height:74px!important;border-radius:16px!important;border:1px solid rgba(255,255,255,.08)!important;background:rgba(255,255,255,.035)!important;padding:13px 14px!important;display:flex!important;flex-direction:column!important;justify-content:space-between!important;}
.ss-team-kpi span{font-size:.66rem!important;font-weight:950!important;letter-spacing:.075em!important;text-transform:uppercase!important;color:rgba(255,255,255,.36)!important;}
.ss-team-kpi strong{font-size:1.25rem!important;font-weight:950!important;color:#fff!important;line-height:1!important;}
.ss-team-kpi.total{border-color:rgba(109,92,255,.24)!important;background:rgba(109,92,255,.055)!important;}
.ss-team-kpi.done{border-color:rgba(0,201,167,.24)!important;background:rgba(0,201,167,.045)!important;}
.ss-team-kpi.active{border-color:rgba(0,201,167,.34)!important;background:rgba(0,201,167,.07)!important;}
.ss-team-kpi.missed{border-color:rgba(237,76,120,.24)!important;background:rgba(237,76,120,.045)!important;}
.ss-team-kpi.hours{border-color:rgba(245,202,153,.22)!important;background:rgba(245,202,153,.04)!important;}
.ss-team-list{display:flex!important;flex-direction:column!important;}
.ss-team-row{display:grid!important;grid-template-columns:minmax(230px,1.05fr) minmax(230px,1fr) minmax(430px,1.6fr)!important;gap:18px!important;align-items:center!important;padding:14px 18px!important;border-bottom:1px solid rgba(255,255,255,.045)!important;}
.ss-team-row:last-child{border-bottom:0!important;}
.ss-team-row:hover{background:rgba(109,92,255,.035)!important;}
.ss-team-person{display:flex!important;align-items:center!important;gap:11px!important;min-width:0!important;}
.ss-team-person .ss-av,.ss-team-person .ss-av-fb{width:38px!important;height:38px!important;border-radius:13px!important;}
.ss-team-name{font-size:.9rem!important;font-weight:950!important;color:rgba(255,255,255,.92)!important;line-height:1.1!important;}
.ss-team-mail{font-size:.74rem!important;color:rgba(255,255,255,.34)!important;margin-top:4px!important;white-space:nowrap!important;overflow:hidden!important;text-overflow:ellipsis!important;max-width:230px!important;}
.ss-team-progress-top{display:flex!important;align-items:center!important;justify-content:space-between!important;gap:10px!important;font-size:.76rem!important;color:rgba(255,255,255,.52)!important;font-weight:800!important;}
.ss-team-progress-top strong{color:#c4b5fd!important;font-weight:950!important;}
.ss-team-progress-track{height:8px!important;border-radius:999px!important;background:rgba(255,255,255,.08)!important;overflow:hidden!important;margin-top:8px!important;}
.ss-team-progress-track span{display:block!important;height:100%!important;border-radius:inherit!important;background:linear-gradient(90deg,rgba(0,201,167,.86),rgba(109,92,255,.95))!important;min-width:2px!important;}
.ss-team-metrics{display:grid!important;grid-template-columns:repeat(5,minmax(72px,1fr))!important;gap:8px!important;}
.ss-team-metric{border-radius:13px!important;border:1px solid rgba(255,255,255,.075)!important;background:rgba(255,255,255,.035)!important;padding:9px 10px!important;min-height:54px!important;display:flex!important;flex-direction:column!important;justify-content:center!important;}
.ss-team-metric span{font-size:.62rem!important;letter-spacing:.07em!important;text-transform:uppercase!important;color:rgba(255,255,255,.34)!important;font-weight:950!important;margin-bottom:4px!important;}
.ss-team-metric strong{font-size:.9rem!important;color:rgba(255,255,255,.88)!important;font-weight:950!important;line-height:1!important;}
.ss-team-metric.done strong,.ss-team-metric.active strong{color:#00c9a7!important;}
.ss-team-metric.missed strong{color:#ed4c78!important;}
.ss-team-metric.hours strong{color:#f5ca99!important;}
.ss-team-empty{padding:28px!important;text-align:center!important;color:rgba(255,255,255,.35)!important;font-size:.86rem!important;}
@media(max-width:1200px){.ss-team-row{grid-template-columns:1fr!important;align-items:stretch!important}.ss-team-metrics{grid-template-columns:repeat(5,minmax(0,1fr))!important}.ss-team-kpis{grid-template-columns:repeat(3,minmax(0,1fr))!important}}
@media(max-width:700px){.ss-team-kpis,.ss-team-metrics{grid-template-columns:repeat(2,minmax(0,1fr))!important}.ss-team-head{align-items:stretch!important}.ss-stat-tabs{width:100%!important}.ss-stat-tab{flex:1!important}}


/* FINAL SUPPORT SHIFT POLISH */
.ss-planner-kpis{display:grid;grid-template-columns:repeat(5,minmax(120px,1fr));gap:10px;margin:0 0 14px;}
.ss-planner-kpi{min-height:68px;border-radius:16px;border:1px solid rgba(255,255,255,.08);background:#25282a;padding:12px 14px;display:flex;flex-direction:column;justify-content:space-between;box-shadow:0 3px 18px rgba(0,0,0,.18);}
.ss-planner-kpi span{font-size:.66rem;font-weight:950;letter-spacing:.07em;text-transform:uppercase;color:rgba(255,255,255,.36);}
.ss-planner-kpi strong{font-size:1.2rem;line-height:1;color:rgba(255,255,255,.95);font-weight:950;}
.ss-planner-kpi.week{border-color:rgba(109,92,255,.26);background:rgba(109,92,255,.07);}
.ss-planner-kpi.assigned{border-color:rgba(109,92,255,.34);background:rgba(109,92,255,.055);}
.ss-planner-kpi.active{border-color:rgba(0,201,167,.32);background:rgba(0,201,167,.06);}
.ss-planner-kpi.missed{border-color:rgba(237,76,120,.30);background:rgba(237,76,120,.055);}
.ss-planner-kpi.hours{border-color:rgba(245,202,153,.24);background:rgba(245,202,153,.045);}
.ss-active-list{display:flex;align-items:center;gap:8px;flex-wrap:wrap;margin:0 0 14px;padding:12px 14px;border-radius:16px;border:1px solid rgba(0,201,167,.18);background:rgba(0,201,167,.045);}
.ss-active-list-title{font-size:.72rem;font-weight:950;letter-spacing:.06em;text-transform:uppercase;color:#00c9a7;margin-right:4px;}
.ss-active-person{display:inline-flex;align-items:center;gap:8px;padding:6px 10px;border-radius:999px;border:1px solid rgba(255,255,255,.08);background:rgba(255,255,255,.04);font-size:.78rem;color:rgba(255,255,255,.78);}
.ss-active-person .ss-av{width:22px!important;height:22px!important;border-radius:999px!important;}
.ss-week-range{display:inline-flex;align-items:center;gap:8px;height:36px;padding:0 13px;border-radius:12px;border:1px solid rgba(255,255,255,.08);background:rgba(255,255,255,.035);color:rgba(255,255,255,.66);font-size:.78rem;font-weight:850;}
.ss-day.is-today{box-shadow:0 0 0 1px rgba(109,92,255,.42),0 10px 34px rgba(109,92,255,.08)!important;}
.ss-day.is-today .ss-day-head:after{content:'Today';display:inline-flex;align-items:center;height:22px;padding:0 8px;border-radius:999px;background:rgba(109,92,255,.18);border:1px solid rgba(109,92,255,.34);color:#c4b5fd;font-size:.62rem;font-weight:950;text-transform:uppercase;letter-spacing:.06em;margin-left:auto;margin-right:8px;}
.ss-shift{cursor:pointer;}
.ss-shift-open{border-color:rgba(255,255,255,.16)!important;background:linear-gradient(180deg,rgba(255,255,255,.055),rgba(255,255,255,.032))!important;}
.ss-shift-assigned{border-color:rgba(109,92,255,.52)!important;background:linear-gradient(180deg,rgba(109,92,255,.14),rgba(109,92,255,.055))!important;}
.ss-shift-active,.ss-shift.is-active{border-color:rgba(0,201,167,.58)!important;background:linear-gradient(180deg,rgba(0,201,167,.14),rgba(0,201,167,.06))!important;}
.ss-shift-past{border-color:rgba(245,202,153,.46)!important;background:linear-gradient(180deg,rgba(245,202,153,.13),rgba(245,202,153,.05))!important;}
.ss-shift-missed{border-color:rgba(237,76,120,.52)!important;background:linear-gradient(180deg,rgba(237,76,120,.13),rgba(237,76,120,.052))!important;}
.ss-shift-missed::before{background:#ed4c78!important;}
.ss-shift .ss-badge.missed{background:rgba(237,76,120,.16)!important;border-color:rgba(237,76,120,.38)!important;color:#ff6b96!important;}
.ss-detail-modal{position:fixed;inset:0;z-index:10050;display:none;align-items:center;justify-content:center;padding:18px;}
.ss-detail-modal.is-open{display:flex;}
.ss-detail-backdrop{position:absolute;inset:0;background:rgba(0,0,0,.62);backdrop-filter:blur(4px);}
.ss-detail-panel{position:relative;width:min(520px,100%);border-radius:20px;border:1px solid rgba(255,255,255,.10);background:#25282a;box-shadow:0 24px 80px rgba(0,0,0,.55);overflow:hidden;}
.ss-detail-head{display:flex;align-items:flex-start;justify-content:space-between;gap:14px;padding:18px 20px;border-bottom:1px solid rgba(255,255,255,.06);background:rgba(255,255,255,.025);}
.ss-detail-kicker{font-size:.68rem;font-weight:950;text-transform:uppercase;letter-spacing:.07em;color:#c4b5fd;margin-bottom:7px;}
.ss-detail-title{font-size:1.05rem;font-weight:950;color:#fff;line-height:1.15;}
.ss-detail-close{width:34px;height:34px;border-radius:11px;border:1px solid rgba(255,255,255,.08);background:rgba(255,255,255,.04);color:rgba(255,255,255,.58);}
.ss-detail-close:hover{background:rgba(255,255,255,.08);color:#fff;}
.ss-detail-body{padding:18px 20px;display:grid;gap:10px;}
.ss-detail-row{display:flex;align-items:center;justify-content:space-between;gap:16px;padding:10px 12px;border-radius:13px;border:1px solid rgba(255,255,255,.06);background:rgba(255,255,255,.03);}
.ss-detail-row span{font-size:.7rem;font-weight:950;text-transform:uppercase;letter-spacing:.06em;color:rgba(255,255,255,.35);}
.ss-detail-row strong{font-size:.86rem;color:rgba(255,255,255,.86);font-weight:900;text-align:right;}
@media(max-width:1200px){.ss-planner-kpis{grid-template-columns:repeat(3,minmax(0,1fr));}}
@media(max-width:700px){.ss-planner-kpis{grid-template-columns:repeat(2,minmax(0,1fr));}.ss-week-range{width:100%;justify-content:center;}}


.ss-person-time{font-size:10px;color:rgba(255,255,255,.52);margin-left:4px;font-variant-numeric:tabular-nums}
.ss-gap-row{display:flex;align-items:center;justify-content:space-between;gap:10px;margin-top:8px;padding:8px 9px;border:1px solid rgba(237,76,120,.28);background:rgba(237,76,120,.075);border-radius:10px;font-size:10.5px;line-height:1.25;color:#ff9ab7}
.ss-gap-row>span{display:flex;align-items:center;gap:6px;min-width:0}
.ss-gap-row .ss-btn{flex:0 0 auto;white-space:nowrap}
@media(max-width:700px){.ss-gap-row{align-items:stretch;flex-direction:column}.ss-gap-row .ss-btn{width:100%;justify-content:center}}

/* Expandable standard shift blocks */
.ss-shift-groups{display:grid;gap:8px;padding:9px 10px 10px}
.ss-day-body{display:none!important}
.ss-shift-group{border:1px solid rgba(255,255,255,.08);border-radius:13px;background:rgba(255,255,255,.025);overflow:hidden}
.ss-shift-group[open]{border-color:rgba(109,92,255,.28);background:rgba(109,92,255,.035)}
.ss-shift-summary{list-style:none;display:flex;align-items:center;justify-content:space-between;gap:10px;padding:10px 11px;cursor:pointer;user-select:none}
.ss-shift-summary::-webkit-details-marker{display:none}
.ss-shift-summary-main{display:flex;align-items:center;gap:9px;min-width:0}
.ss-shift-summary-icon{width:30px;height:30px;border-radius:9px;display:grid;place-items:center;background:rgba(109,92,255,.14);border:1px solid rgba(109,92,255,.25);color:#b9b0ff;flex:0 0 auto}
.ss-shift-summary-text{min-width:0}
.ss-shift-summary-text strong{display:block;font-size:11.5px;color:rgba(255,255,255,.9);white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.ss-shift-summary-text span{display:block;margin-top:2px;font-size:9.5px;color:rgba(255,255,255,.43);font-variant-numeric:tabular-nums}
.ss-shift-summary-side{display:flex;align-items:center;gap:7px;flex:0 0 auto}
.ss-shift-fill{font-size:9px;font-weight:900;padding:4px 7px;border-radius:999px;border:1px solid rgba(255,255,255,.08);color:rgba(255,255,255,.55);background:rgba(255,255,255,.04)}
.ss-shift-fill.is-full{color:#64ebcf;border-color:rgba(0,201,167,.26);background:rgba(0,201,167,.09)}
.ss-shift-chevron{color:rgba(255,255,255,.35);transition:transform .18s ease}
.ss-shift-group[open] .ss-shift-chevron{transform:rotate(180deg)}
.ss-shift-blocks-wrap{padding:0 10px 10px;border-top:1px solid rgba(255,255,255,.055)}
.ss-shift-toolbar{display:flex;align-items:center;justify-content:space-between;gap:8px;padding:9px 0 8px}
.ss-shift-toolbar-note{font-size:9.5px;color:rgba(255,255,255,.38)}
.ss-shift-toolbar-actions{display:flex;gap:5px;flex-wrap:wrap;justify-content:flex-end}
.ss-block-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:6px}
.ss-block{min-width:0;padding:8px;border:1px solid rgba(255,255,255,.07);border-radius:10px;background:rgba(255,255,255,.025)}
.ss-block.is-open{border-color:rgba(237,76,120,.22);background:rgba(237,76,120,.055)}
.ss-block.is-covered{border-color:rgba(0,201,167,.22);background:rgba(0,201,167,.065)}
.ss-block.is-own{border-color:rgba(109,92,255,.4);background:rgba(109,92,255,.12)}
.ss-block-top{display:flex;align-items:center;justify-content:space-between;gap:6px}
.ss-block-time{font-size:10px;font-weight:950;color:rgba(255,255,255,.76);font-variant-numeric:tabular-nums}
.ss-block-state{font-size:8px;font-weight:950;text-transform:uppercase;letter-spacing:.05em;color:rgba(255,255,255,.35)}
.ss-block-owner{display:flex;align-items:center;gap:5px;margin-top:6px;min-width:0;font-size:9.5px;font-weight:850;color:rgba(255,255,255,.72)}
.ss-block-owner img,.ss-block-owner .ss-mini-av{width:16px;height:16px;border-radius:50%;object-fit:cover;flex:0 0 auto}
.ss-block-owner .ss-mini-av{display:grid;place-items:center;background:rgba(109,92,255,.2);font-size:8px}
.ss-block-owner span{white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.ss-block-actions{display:flex;gap:4px;margin-top:7px}
.ss-block-actions .ss-btn{flex:1;justify-content:center;padding:5px 6px;font-size:9px}
.ss-range-btn{padding:5px 8px!important;font-size:9px!important}
@media(max-width:700px){.ss-block-grid{grid-template-columns:1fr}.ss-shift-toolbar{align-items:flex-start;flex-direction:column}.ss-shift-toolbar-actions{width:100%;justify-content:stretch}.ss-shift-toolbar-actions .ss-btn{flex:1;justify-content:center}}

/* COMPACT TOP OVERVIEW */
.ss-hero{margin-bottom:10px!important;border-radius:16px!important;}
.ss-hero-inner{padding:14px 16px!important;gap:14px!important;min-height:0!important;}
.ss-hero-icon{width:42px!important;height:42px!important;border-radius:13px!important;font-size:17px!important;}
.ss-hero-kicker{font-size:.64rem!important;margin-bottom:4px!important;}
.ss-hero h2{font-size:1.12rem!important;margin:0 0 3px!important;}
.ss-hero-sub{font-size:.76rem!important;line-height:1.35!important;max-width:680px!important;}
.ss-hero-active{min-width:360px!important;}
.ss-active-card{min-height:64px!important;padding:10px 12px!important;border-radius:14px!important;}
.ss-off-tag,.ss-active-tag{font-size:.62rem!important;margin-bottom:3px!important;}
.ss-off-title,.ss-active-title{font-size:.88rem!important;margin-bottom:2px!important;}
.ss-off-text,.ss-active-range{font-size:.72rem!important;}
.ss-bar{padding:10px 12px!important;margin-bottom:10px!important;border-radius:15px!important;}
.ss-bar .ss-btn{height:32px!important;padding:0 10px!important;font-size:.72rem!important;}
.ss-week-range{height:32px!important;font-size:.72rem!important;}
.ss-planner-kpis{gap:8px!important;margin-bottom:10px!important;}
.ss-planner-kpi{min-height:56px!important;padding:9px 11px!important;border-radius:14px!important;}
.ss-planner-kpi span{font-size:.58rem!important;}
.ss-planner-kpi strong{font-size:1rem!important;}
.ss-active-list{padding:8px 10px!important;margin-bottom:10px!important;border-radius:14px!important;gap:6px!important;}
.ss-active-list-title{font-size:.62rem!important;}
.ss-active-person{padding:4px 8px!important;font-size:.72rem!important;gap:6px!important;}
.ss-active-person .ss-av{width:19px!important;height:19px!important;}
.ss-active-person small{color:rgba(255,255,255,.42)!important;font-size:.68rem!important;}
@media(max-width:900px){.ss-hero-inner{align-items:stretch!important;}.ss-hero-active{min-width:0!important;width:100%!important;}.ss-planner-kpis{grid-template-columns:repeat(2,minmax(0,1fr))!important;}.ss-planner-kpi.week{grid-column:1/-1!important;}}


/* 2026 planner redesign: wider days, compact overview, side detail panel */
.ss-wrap{max-width:1680px!important;margin:0 auto!important;padding:14px 16px 28px!important}
.ss-hero{background:linear-gradient(135deg,rgba(109,92,255,.08),rgba(255,255,255,.025))!important}
.ss-hero-inner{display:grid!important;grid-template-columns:minmax(0,1fr) minmax(320px,520px)!important}
.ss-hero-sub{max-width:760px!important}
.ss-bar{position:sticky;top:58px;z-index:20;background:rgba(34,37,39,.96)!important;backdrop-filter:blur(14px)}
.ss-planner-kpis{grid-template-columns:minmax(220px,1.4fr) repeat(2,minmax(120px,.7fr))!important}
.ss-planner-kpi.missed,.ss-planner-kpi.hours{display:none!important}
.ss-active-list{display:none!important}
.ss-active-kpi-summary{display:flex;align-items:center;gap:10px;min-width:0}
.ss-active-kpi-extra{display:flex;align-items:center;gap:8px;min-width:0;overflow:hidden}
.ss-active-kpi-avatars{display:flex;align-items:center;flex:0 0 auto;padding-left:3px}
.ss-active-kpi-avatars .ss-av{width:22px!important;height:22px!important;margin-left:-4px;border:2px solid #20332f!important;box-shadow:0 0 0 1px rgba(0,201,167,.18)}
.ss-active-kpi-names{min-width:0;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;font-size:.69rem!important;font-weight:800!important;letter-spacing:0!important;text-transform:none!important;color:rgba(255,255,255,.78)!important}
.ss-grid{display:grid!important;grid-template-columns:repeat(7,minmax(245px,1fr))!important;align-items:start!important;gap:12px!important;overflow-x:auto!important;scroll-snap-type:x proximity;padding-bottom:12px!important}
.ss-day{height:auto!important;min-height:0!important;align-self:start!important;scroll-snap-align:start;border-radius:18px!important;overflow:hidden}
.ss-day-head{padding:13px 15px!important}
.ss-day-body-new{padding:10px!important;gap:9px!important}
.ss-shift-group{border-radius:14px!important;transition:border-color .15s ease,background .15s ease,transform .15s ease}
.ss-shift-group:hover{transform:translateY(-1px);border-color:rgba(109,92,255,.24)}
.ss-shift-summary{padding:12px!important}
.ss-shift-summary-icon{width:34px!important;height:34px!important}
.ss-shift-summary-text strong{font-size:12.5px!important}
.ss-shift-summary-text span{font-size:10.5px!important}
.ss-shift-fill{font-size:9.5px!important;padding:5px 8px!important}
.ss-shift-group:not([open]) .ss-shift-blocks-wrap{display:none!important}

/* Side panel for hourly details */
.ss-shift-backdrop{position:fixed;inset:0;background:rgba(5,7,9,.52);backdrop-filter:blur(1px);z-index:1045;opacity:0;pointer-events:none;transition:opacity .18s ease}
body.ss-panel-open .ss-shift-backdrop{opacity:1;pointer-events:auto}
.ss-shift-group[open]{position:fixed!important;top:72px;right:18px;bottom:18px;width:min(600px,calc(100vw - 36px));z-index:1050;background:#202326!important;border:1px solid rgba(109,92,255,.42)!important;box-shadow:0 28px 90px rgba(0,0,0,.55);overflow:auto;border-radius:20px!important;transform:none!important}
.ss-shift-group[open] .ss-shift-summary{position:sticky;top:0;z-index:3;background:rgba(32,35,38,.96);backdrop-filter:blur(12px);padding:16px 54px 14px 16px!important;border-bottom:1px solid rgba(255,255,255,.07)}
.ss-shift-group[open] .ss-shift-summary-icon{width:40px!important;height:40px!important;border-radius:12px!important}
.ss-shift-group[open] .ss-shift-summary-text strong{font-size:15px!important}
.ss-shift-group[open] .ss-shift-summary-text span{font-size:11.5px!important}
.ss-shift-group[open] .ss-shift-chevron{display:none}
.ss-panel-close{position:fixed;top:87px;right:34px;z-index:1060;width:32px;height:32px;border-radius:10px;border:1px solid rgba(255,255,255,.09);background:rgba(255,255,255,.06);color:#fff;display:none;align-items:center;justify-content:center;cursor:pointer}
body.ss-panel-open .ss-panel-close{display:flex}
.ss-shift-group[open] .ss-shift-blocks-wrap{display:block!important;padding:14px 16px 18px!important;border-top:0!important}
.ss-shift-group[open] .ss-shift-toolbar{padding:0 0 13px!important;align-items:center!important}
.ss-shift-group[open] .ss-shift-toolbar-note{font-size:11px!important;line-height:1.45;max-width:260px}
.ss-shift-group[open] .ss-block-grid{grid-template-columns:repeat(2,minmax(0,1fr))!important;gap:10px!important}
.ss-shift-group[open] .ss-block{padding:11px!important;border-radius:13px!important;min-height:112px;display:flex;flex-direction:column}
.ss-shift-group[open] .ss-block-time{font-size:12px!important}
.ss-shift-group[open] .ss-block-state{font-size:8.5px!important}
.ss-shift-group[open] .ss-block-owner{font-size:11px!important;margin-top:8px!important}
.ss-shift-group[open] .ss-block-actions{margin-top:auto!important;padding-top:10px!important}
.ss-shift-group[open] .ss-block-actions .ss-btn{height:30px;font-size:10px!important}
.ss-block.is-open{border-color:rgba(237,76,120,.42)!important;background:linear-gradient(180deg,rgba(237,76,120,.12),rgba(237,76,120,.045))!important;box-shadow:inset 0 0 0 1px rgba(237,76,120,.12)}
.ss-block.is-covered{border-color:rgba(75,158,255,.28)!important;background:linear-gradient(180deg,rgba(75,158,255,.085),rgba(75,158,255,.035))!important}
.ss-block.is-own{border-color:rgba(109,92,255,.48)!important;background:linear-gradient(180deg,rgba(109,92,255,.16),rgba(109,92,255,.06))!important}
.ss-block.is-open .ss-block-state{color:#ff8ba9!important}
.ss-block.is-own .ss-block-state{color:#c4b5fd!important}
.ss-block.is-covered .ss-block-state{color:#8ac6ff!important}

/* Compact team overview */
.ss-stats{margin-top:12px!important}
.ss-team-card{border-radius:18px!important}
.ss-team-head{padding:12px 14px!important}
.ss-team-title{font-size:.9rem!important}
.ss-team-sub{font-size:.68rem!important}

@media(max-width:1100px){
 .ss-hero-inner{grid-template-columns:1fr!important}.ss-hero-active{min-width:0!important}
 .ss-grid{grid-template-columns:repeat(7,minmax(270px,82vw))!important}
}
@media(max-width:700px){
 .ss-wrap{padding:10px 8px 22px!important}.ss-bar{top:50px}
 .ss-planner-kpis{grid-template-columns:1fr 1fr!important}.ss-planner-kpi.week{grid-column:1/-1}
 .ss-shift-group[open]{top:0;right:0;bottom:0;width:100vw;border-radius:0!important}
 .ss-panel-close{top:14px;right:14px}
 .ss-shift-group[open] .ss-block-grid{grid-template-columns:1fr!important}
 .ss-shift-group[open] .ss-shift-toolbar{align-items:stretch!important}
}

</style>

<div class="ss-shift-backdrop" id="ssShiftBackdrop"></div>
<button type="button" class="ss-panel-close" id="ssPanelClose" aria-label="Close shift details"><i class="fa-duotone fa-xmark"></i></button>

<div class="ss-wrap">

<div id="ss-toast-wrap" class="position-fixed bottom-0 end-0 p-3" style="z-index:9999;">
    <div id="ss-toast-el" class="toast align-items-center text-white border-0" role="alert">
        <div class="d-flex">
            <div class="toast-body fw-semibold" id="ss-toast-body"></div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
        </div>
    </div>
</div>

<!-- ── Hero ── -->
<div class="ss-hero">
    <div class="ss-hero-inner">
        <div class="ss-hero-left">
            <div class="ss-hero-icon"><i class="fa-duotone fa-headset"></i></div>
            <div>
                <div class="ss-hero-kicker"><i class="fa-duotone fa-calendar-clock"></i> Support Shifts</div>
                <h2>Support Coverage</h2>
                <p class="ss-hero-sub">Manage all support hours in one place, claim open blocks, cover gaps, and coordinate quick handovers.</p>
            </div>
        </div>
        <div class="ss-hero-active">
            <div class="ss-active-card <?= !empty($activeShift) ? 'is-on' : '' ?>">
                <?php if (!empty($activeShift)): ?>
                    <div class="ss-active-main">
                        <div class="ss-active-tag"><span class="ss-active-dot"></span> Active now</div>
                        <div class="ss-active-title"><?= lb_ss_h($lb_ss_display_title($activeShift['title'] ?? 'Support Shift', strtotime((string)($activeShift['start_iso'] ?? '')) ?: strtotime(date('Y-m-d').' '.($activeShift['start_time'] ?? '00:00:00')), strtotime((string)($activeShift['end_iso'] ?? '')) ?: strtotime(date('Y-m-d').' '.($activeShift['end_time'] ?? '00:00:00')))) ?></div>
                        <div class="ss-active-range js-rng"
                             data-si="<?= lb_ss_h($activeShift['start_iso'] ?? '') ?>"
                             data-ei="<?= lb_ss_h($activeShift['end_iso'] ?? '') ?>">
                            <?= lb_ss_5($activeShift['start_time']) ?> – <?= lb_ss_5($activeShift['end_time']) ?>
                        </div>
                    </div>
                    <div class="ss-active-actions">
                        <div class="ss-timer js-timer" data-sa="<?= lb_ss_h($activeShift['started_at'] ?? '') ?>">00:00:00</div>
                        <button class="ss-btn danger ss-act" data-action="support_shift_end"
                                data-shift-id="<?= (int)$activeShift['id'] ?>">
                            <i class="fa-duotone fa-stop"></i> End Shift
                        </button>
                    </div>
                <?php else: ?>
                    <div class="ss-active-main">
                        <div class="ss-off-tag">Ready to help</div>
                        <div class="ss-off-title">No active block right now</div>
                        <div class="ss-off-text">All open and occupied hours are visible below.</div>
                    </div>
                    <div class="ss-active-actions">
                        <a class="ss-btn primary" href="#ss-week"><i class="fa-duotone fa-calendar-days"></i> Open schedule</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- ── Toolbar ── -->
<div class="ss-bar" id="ss-week">
    <div class="d-flex gap-2 flex-wrap">
        <button type="button" class="ss-btn js-week-nav" data-from="<?= lb_ss_h($prevFrom) ?>" data-to="<?= lb_ss_h($prevTo) ?>">
            <i class="fa-duotone fa-chevron-left"></i> Prev week
        </button>
        <button type="button" class="ss-btn js-week-nav" data-from="<?= date('Y-m-d') ?>" data-to="<?= date('Y-m-d', strtotime('+6 days')) ?>">
            This week
        </button>
        <button type="button" class="ss-btn js-week-nav" data-from="<?= lb_ss_h($nextFrom) ?>" data-to="<?= lb_ss_h($nextTo) ?>">
            Next week <i class="fa-duotone fa-chevron-right"></i>
        </button>
        <span class="ss-week-range"><i class="fa-duotone fa-calendar-week"></i> <?= lb_ss_h($plannerRangeLabel) ?></span>
    </div>
    <div class="d-flex gap-2 align-items-center flex-wrap">
        <form class="d-flex gap-2 js-week-form" method="GET" action="<?= ADMN_URL ?>/support-shifts">
            <input type="hidden" class="js-date-in" name="from" value="<?= $from ?>" data-label="Week start">
            <input type="hidden" name="to" id="ss-to" value="<?= $to ?>">
        </form>
        <?php if ($isSuperAdmin): ?>
        <button class="ss-btn" data-bs-toggle="modal" data-bs-target="#ssTemplatesModal" title="Recurring weekly schedule">
            <i class="fa-duotone fa-calendar-week"></i> Standard schedule
        </button>
        <button class="ss-btn primary" data-bs-toggle="modal" data-bs-target="#ssCreateModal">
            <i class="fa-duotone fa-plus"></i> Create Shift
        </button>
        <?php endif; ?>
    </div>

</div>

<div class="ss-planner-kpis">
    <div class="ss-planner-kpi week"><span>Week</span><strong><?= lb_ss_h($plannerRangeLabel) ?></strong></div>
    <div class="ss-planner-kpi assigned"><span>Assigned</span><strong><?= (int)$plannerTotals['assigned'] ?></strong></div>
    <div class="ss-planner-kpi active"><span>Active now</span>
        <div class="ss-active-kpi-summary">
            <strong><?= (int)$plannerTotals['active'] ?></strong>
        <?php if (!empty($activeNowList)): ?>
        <?php
            $activeNowNames = [];
            foreach ($activeNowList as $as) {
                $asAdmin = $as['admin'] ?? [];
                $activeNowNames[] = (string)($asAdmin['username'] ?? 'Admin');
            }
            $activeNowNamesLabel = implode(' · ', $activeNowNames);
        ?>
        <div class="ss-active-kpi-extra" title="<?= lb_ss_h(implode(', ', $activeNowNames)) ?>">
            <div class="ss-active-kpi-avatars">
                <?php foreach ($activeNowList as $as):
                    $asAdmin = $as['admin'] ?? [];
                ?>
                    <?= lb_ss_avatar(lb_ss_admin_icon($asAdmin), $asAdmin['username'] ?? 'Admin') ?>
                <?php endforeach; ?>
            </div>
            <span class="ss-active-kpi-names"><?= lb_ss_h($activeNowNamesLabel) ?></span>
        </div>
        <?php endif; ?>
        </div>
    </div>
    <div class="ss-planner-kpi missed"><span>Missed</span><strong><?= (int)$plannerTotals['missed'] ?></strong></div>
    <div class="ss-planner-kpi hours"><span>Scheduled</span><strong><?= lb_ss_worked($plannerTotals['scheduled_minutes']) ?></strong></div>
</div>

<!-- ── Week Grid ── -->
<div class="ss-grid mb-4">
    <?php foreach ($days as $date => $dayShifts):
        $dt      = new DateTime($date);
        $isToday = ($date === date('Y-m-d'));
        // Compute coverage using the whole week's shifts, so overnight is respected.
        $coverageSegments = function_exists('lb_support_shift_coverage_segments')
            ? lb_support_shift_coverage_segments($shifts, $date) : [];
        $coverageTotals = function_exists('lb_support_shift_coverage_totals')
            ? lb_support_shift_coverage_totals($coverageSegments) : ['covered_min'=>0,'gap_min'=>1440,'open_min'=>0,'overlap_min'=>0,'total_min'=>1440];
        $covH = round(($coverageTotals['covered_min'] + $coverageTotals['open_min']) / 60, 1);
        $gapH = round($coverageTotals['gap_min'] / 60, 1);
        $covClass = $coverageTotals['gap_min'] === 0 ? 'is-full' : ($coverageTotals['gap_min'] > 0 ? 'is-gap' : '');
    ?>
    <div class="ss-day <?= $isToday ? 'is-today' : '' ?>">
        <div class="ss-day-head">
            <div>
                <div class="ss-day-name js-dow" data-date="<?= lb_ss_h($date) ?>"><?= lb_ss_h($dt->format('D')) ?></div>
                <div class="ss-day-date"><?= lb_ss_h($dt->format('d.m')) ?></div>
            </div>
            <span class="ss-day-count"><?= count($dayShifts) ?></span>
        </div>
        <div class="ss-coverage">
            <div class="ss-cov-head <?= $covClass ?>">
                <span>Coverage</span>
                <?php if ($coverageTotals['gap_min'] === 0): ?>
                    <strong><?= $covH ?> h &middot; full day</strong>
                <?php else: ?>
                    <strong><?= $covH ?> h &middot; <?= $gapH ?> h gap</strong>
                <?php endif; ?>
            </div>
            <div class="ss-cov-bar" role="img" aria-label="24 hour coverage bar">
                <?php foreach ($coverageSegments as $seg):
                    $w = max(0, ($seg['to_min'] - $seg['from_min']) / 1440 * 100);
                    if ($w <= 0) continue;
                    $cls = !empty($seg['is_gap']) ? 'gap'
                         : (!empty($seg['is_open']) ? 'open'
                         : (!empty($seg['is_overlap']) ? 'overlap' : 'covered'));
                    $fromStr = sprintf('%02d:%02d', intdiv($seg['from_min'],60), $seg['from_min']%60);
                    $toStr   = sprintf('%02d:%02d', intdiv($seg['to_min'],60),   $seg['to_min']%60);
                    $label   = !empty($seg['admins']) ? implode(' + ', $seg['admins']) : ($cls === 'open' ? 'Open shift' : 'Uncovered');
                    $tip     = $label . ' &middot; ' . $fromStr . '&ndash;' . $toStr;
                ?>
                <div class="ss-cov-seg <?= $cls ?>" style="width:<?= round($w, 3) ?>%" title="<?= lb_ss_h(strip_tags(str_replace(['&middot;','&ndash;'],['·','–'],$tip))) ?>"></div>
                <?php endforeach; ?>
            </div>
            <div class="ss-cov-ticks">
                <span class="ss-cov-tick" style="left:0%;">00</span>
                <span class="ss-cov-tick" style="left:25%;">06</span>
                <span class="ss-cov-tick" style="left:50%;">12</span>
                <span class="ss-cov-tick" style="left:75%;">18</span>
                <span class="ss-cov-tick" style="left:100%;">24</span>
            </div>
        </div>
        <?php
        // Three fixed standard shifts, each expandable into eight one-hour blocks.
        $standardGroups = [];
        foreach ($dayShifts as $__groupShift) {
            [$__gs, $__ge] = function_exists('lb_support_shift_datetime_window') ? lb_support_shift_datetime_window($__groupShift) : [0, 0];
            if ($__gs <= 0 || $__ge <= $__gs) continue;
            $standardGroups[] = ['shift' => $__groupShift, 'start' => $__gs, 'end' => $__ge];
        }
        usort($standardGroups, static fn($a, $b) => $a['start'] <=> $b['start']);
        ?>
        <div class="ss-shift-groups">
            <?php foreach ($standardGroups as $__gi => $__group):
                $__shift = $__group['shift'];
                $__groupStart = $__group['start'];
                $__groupEnd = $__group['end'];
                $__participants = is_array($__shift['participants'] ?? null) ? $__shift['participants'] : [];
                $__blocks = [];
                $__coveredCount = 0;
                for ($__bs = $__groupStart; $__bs < $__groupEnd; $__bs += 3600) {
                    $__be = min($__bs + 3600, $__groupEnd);
                    $__ownersByAdmin = [];
                    $__own = false;
                    foreach ($__participants as $__part) {
                        $__baseDate = (string)($__shift['shift_date'] ?? $date);
                        $__ps = strtotime($__baseDate.' '.(!empty($__part['planned_start_time']) ? $__part['planned_start_time'] : $__shift['start_time']));
                        $__pe = strtotime($__baseDate.' '.(!empty($__part['planned_end_time']) ? $__part['planned_end_time'] : $__shift['end_time']));
                        if ($__ps < $__groupStart) $__ps += 86400;
                        if ($__pe <= $__ps) $__pe += 86400;
                        if ($__bs < $__pe && $__be > $__ps) {
                            $__adminId = (int)($__part['admin_id'] ?? 0);
                            $__ownerKey = $__adminId > 0
                                ? 'id:'.$__adminId
                                : 'name:'.mb_strtolower(trim((string)($__part['username'] ?? 'Admin')), 'UTF-8');
                            if (!isset($__ownersByAdmin[$__ownerKey])) {
                                $__ownersByAdmin[$__ownerKey] = $__part;
                            }
                            if ($__adminId === $currentAdminId) $__own = true;
                        }
                    }
                    $__owners = array_values($__ownersByAdmin);
                    if (!empty($__owners)) $__coveredCount++;
                    $__blocks[] = ['start'=>$__bs, 'end'=>$__be, 'owners'=>$__owners, 'own'=>$__own];
                }
                $__totalBlocks = count($__blocks);
                $__full = $__totalBlocks > 0 && $__coveredCount === $__totalBlocks;
                $__title = $lb_ss_display_title($__shift['title'] ?? 'Support Shift', $__groupStart, $__groupEnd);
            ?>
            <details class="ss-shift-group">
                <summary class="ss-shift-summary">
                    <div class="ss-shift-summary-main">
                        <span class="ss-shift-summary-icon"><i class="fa-duotone fa-clock"></i></span>
                        <span class="ss-shift-summary-text">
                            <strong><?= lb_ss_h($__title) ?></strong>
                            <span><?= lb_ss_h(date('H:i', $__groupStart).' – '.date('H:i', $__groupEnd)) ?></span>
                        </span>
                    </div>
                    <span class="ss-shift-summary-side">
                        <span class="ss-shift-fill <?= $__full ? 'is-full' : '' ?>"><?= (int)$__coveredCount ?>/<?= (int)$__totalBlocks ?> covered</span>
                        <i class="fa-duotone fa-chevron-down ss-shift-chevron"></i>
                    </span>
                </summary>
                <div class="ss-shift-blocks-wrap">
                    <div class="ss-shift-toolbar">
                        <span class="ss-shift-toolbar-note">Claim a full shift, claim a custom range, or manage single hourly blocks.</span>
                        <span class="ss-shift-toolbar-actions">
                            <button type="button" class="ss-btn xs primary ss-range-btn js-range-open"
                                    data-shift-id="<?= (int)$__shift['id'] ?>"
                                    data-title="<?= lb_ss_h($__title) ?>"
                                    data-start="<?= lb_ss_h(date('H:i', $__groupStart)) ?>"
                                    data-end="<?= lb_ss_h(date('H:i', $__groupEnd)) ?>"
                                    data-mode="claim"><i class="fa-duotone fa-hand"></i> Claim range</button>
                            <?php if ($isSuperAdmin): ?>
                            <button type="button" class="ss-btn xs ss-range-btn js-range-open"
                                    data-shift-id="<?= (int)$__shift['id'] ?>"
                                    data-title="<?= lb_ss_h($__title) ?>"
                                    data-start="<?= lb_ss_h(date('H:i', $__groupStart)) ?>"
                                    data-end="<?= lb_ss_h(date('H:i', $__groupEnd)) ?>"
                                    data-mode="assign"><i class="fa-duotone fa-user-plus"></i> Assign range</button>
                            <button class="ss-btn xs danger ss-act" type="button" data-action="support_shift_delete" data-shift-id="<?= (int)$__shift['id'] ?>" data-confirm="Delete this standard shift and all assigned blocks?"><i class="fa-duotone fa-trash"></i></button>
                            <?php endif; ?>
                        </span>
                    </div>
                    <div class="ss-block-grid">
                        <?php foreach ($__blocks as $__block):
                            $__startHm = date('H:i', $__block['start']);
                            $__endHm = date('H:i', $__block['end']);
                            $__owners = $__block['owners'];
                            $__covered = !empty($__owners);
                            $__own = !empty($__block['own']);
                            $__class = $__own ? 'is-own' : ($__covered ? 'is-covered' : 'is-open');
                            $__ownerNames = array_values(array_unique(array_map(static fn($p) => (string)($p['username'] ?? 'Admin'), $__owners)));
                        ?>
                        <div class="ss-block <?= $__class ?>">
                            <div class="ss-block-top">
                                <span class="ss-block-time"><?= lb_ss_h($__startHm.' – '.$__endHm) ?></span>
                                <span class="ss-block-state"><?= $__own ? 'Your block' : ($__covered ? 'Assigned' : 'Coverage gap') ?></span>
                            </div>
                            <div class="ss-block-owner">
                                <?php if ($__covered):
                                    $__firstOwner = $__owners[0]; $__icon = lb_ss_admin_icon($__firstOwner); ?>
                                    <?= $__icon !== '' ? '<img src="'.lb_ss_h($__icon).'" alt="">' : '<span class="ss-mini-av">'.lb_ss_h(mb_strtoupper(mb_substr($__ownerNames[0] ?? 'A',0,1))).'</span>' ?>
                                    <span><?= lb_ss_h(implode(' + ', $__ownerNames)) ?></span>
                                <?php else: ?>
                                    <i class="fa-duotone fa-triangle-exclamation"></i><span>Open hour</span>
                                <?php endif; ?>
                            </div>
                            <div class="ss-block-actions">
                                <?php if (!$__covered): ?>
                                    <button type="button" class="ss-btn xs primary ss-act" data-action="support_shift_take_gap" data-shift-id="<?= (int)$__shift['id'] ?>" data-start-time="<?= lb_ss_h($__startHm) ?>" data-end-time="<?= lb_ss_h($__endHm) ?>" data-confirm="Claim <?= lb_ss_h($__startHm.'–'.$__endHm) ?>?">Claim</button>
                                    <?php if ($isSuperAdmin): ?>
                                    <button type="button" class="ss-btn xs js-range-open" data-shift-id="<?= (int)$__shift['id'] ?>" data-title="<?= lb_ss_h($__title) ?>" data-start="<?= lb_ss_h($__startHm) ?>" data-end="<?= lb_ss_h($__endHm) ?>" data-mode="assign">Assign</button>
                                    <?php endif; ?>
                                <?php elseif ($__own): ?>
                                    <button type="button" class="ss-btn xs danger ss-act" data-action="support_shift_release_hour" data-shift-id="<?= (int)$__shift['id'] ?>" data-start-time="<?= lb_ss_h($__startHm) ?>" data-end-time="<?= lb_ss_h($__endHm) ?>" data-confirm="Release <?= lb_ss_h($__startHm.'–'.$__endHm) ?>?">Release</button>
                                <?php else: ?>
                                    <button type="button" class="ss-btn xs primary ss-act" data-action="support_shift_join_hour" data-shift-id="<?= (int)$__shift['id'] ?>" data-start-time="<?= lb_ss_h($__startHm) ?>" data-end-time="<?= lb_ss_h($__endHm) ?>" data-confirm="Join <?= lb_ss_h($__startHm.'–'.$__endHm) ?> as additional support?">Join</button>
                                    <?php if ($isSuperAdmin): ?>
                                    <button type="button" class="ss-btn xs js-range-open" data-shift-id="<?= (int)$__shift['id'] ?>" data-title="<?= lb_ss_h($__title) ?>" data-start="<?= lb_ss_h($__startHm) ?>" data-end="<?= lb_ss_h($__endHm) ?>" data-mode="assign">Reassign</button>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </details>
            <?php endforeach; ?>
        </div>
        <div class="ss-day-body">
            <?php if (empty($dayShifts)): ?>
            <div class="ss-empty-day">No shifts</div>
            <?php endif; ?>
            <?php foreach ($dayShifts as $s):
                $status   = (string)($s['status'] ?? 'open');
                $participants = is_array($s['participants'] ?? null) ? $s['participants'] : [];

                // Live refresh per card, so newly joined admins are shown immediately.
                // This intentionally reads the participant table again instead of relying only on the controller data.
                try {
                    global $db;
                    $liveShiftId = (int)($s['id'] ?? 0);
                    if ($liveShiftId > 0 && isset($db)) {
                        $liveRows = $db->run("SELECT p.shift_id, p.admin_id, p.status, p.started_at, p.ended_at,
                                a.username, a.email, a.icon
                            FROM support_shift_participants p
                            LEFT JOIN admins a ON a.id = p.admin_id
                            WHERE p.shift_id = ? AND p.status IN ('active','paused')
                            ORDER BY p.started_at ASC, p.id ASC", $liveShiftId) ?: [];

                        // Compatibility fallback for older rows still stored in support_shift_admins.
                        try {
                            $legacyRows = $db->run("SELECT p.shift_id, p.admin_id, p.status, p.started_at, p.ended_at,
                                    a.username, a.email, a.icon
                                FROM support_shift_admins p
                                LEFT JOIN admins a ON a.id = p.admin_id
                                WHERE p.shift_id = ? AND p.status IN ('active','paused')
                                ORDER BY p.started_at ASC, p.id ASC", $liveShiftId) ?: [];
                            $liveRows = array_merge($liveRows, $legacyRows);
                        } catch (Throwable $e) {}

                        if (!empty($s['assigned_admin_id'])) {
                            $assignedRow = $db->run("SELECT ? AS shift_id, id AS admin_id, 'active' AS status, NULL AS started_at, NULL AS ended_at,
                                    username, email, icon
                                FROM admins WHERE id = ? LIMIT 1", $liveShiftId, (int)$s['assigned_admin_id']) ?: [];
                            $liveRows = array_merge($liveRows, $assignedRow);
                        }

                        $deduped = [];
                        foreach ($liveRows as $lr) {
                            $aid = (int)($lr['admin_id'] ?? 0);
                            if ($aid <= 0 || isset($deduped[$aid])) continue;
                            $deduped[$aid] = $lr;
                        }
                        if (!empty($deduped)) $participants = array_values($deduped);
                    }
                } catch (Throwable $e) {}

                $participantCount = count($participants);
                $assigned = !empty($s['assigned_username']) ? $s['assigned_username'] : 'Open shift';
                $isAssign = !empty($s['assigned_admin_id']) || $participantCount > 0;
                $startIso = (string)($s['start_iso'] ?? '');
                $endIso   = (string)($s['end_iso']   ?? '');
                // timing
                $startTs  = $startIso ? strtotime($startIso) : strtotime($date.' '.$s['start_time']);
                $endTs    = $endIso   ? strtotime($endIso)   : strtotime($date.' '.$s['end_time']);
                $nowTs    = time();
                $isPast   = ($endTs > 0 && $nowTs > $endTs && in_array($status,['open','assigned','completed'],true));
                $isCur    = ($startTs > 0 && $endTs > 0 && $nowTs >= $startTs && $nowTs <= $endTs);
                $isFut    = ($startTs > 0 && $nowTs < $startTs);
                $isOwnParticipant = false;
                $ownParticipant = null;
                foreach ($participants as $__p) { if ((int)($__p['admin_id'] ?? 0) === $currentAdminId) { $isOwnParticipant = true; $ownParticipant = $__p; break; } }
                $coverageIntervals = [];
                foreach ($participants as $__p) {
                    $__ps = strtotime($date.' '.(!empty($__p['planned_start_time']) ? $__p['planned_start_time'] : $s['start_time']));
                    $__pe = strtotime($date.' '.(!empty($__p['planned_end_time']) ? $__p['planned_end_time'] : $s['end_time']));
                    if ($__pe <= $__ps) $__pe += 86400;
                    $__ps = max($__ps, $startTs); $__pe = min($__pe, $endTs);
                    if ($__pe > $__ps) $coverageIntervals[] = [$__ps,$__pe];
                }
                usort($coverageIntervals, static fn($a,$b) => $a[0] <=> $b[0]);
                $coverageGaps = []; $coverageCursor = $startTs;
                foreach ($coverageIntervals as $__iv) {
                    if ($__iv[0] > $coverageCursor) $coverageGaps[] = [$coverageCursor,$__iv[0]];
                    $coverageCursor = max($coverageCursor,$__iv[1]);
                }
                if ($coverageCursor < $endTs) $coverageGaps[] = [$coverageCursor,$endTs];
                $isOwnAssigned = ($isOwnParticipant || ($isAssign && (int)($s['assigned_admin_id'] ?? 0) === $currentAdminId));
                $canResume = (in_array($status, ['completed','paused'], true) && $isOwnAssigned && $isCur);
                $canSelfUnassign = ($isOwnAssigned && $status === 'assigned' && $startTs > 0 && $nowTs < ($startTs - 86400));
                $isSelfUnassignLocked = ($isOwnAssigned && $status === 'assigned' && $startTs > 0 && $nowTs >= ($startTs - 86400) && $nowTs < $startTs);
                $canSelfEdit  = $canSelfUnassign;
                $canAdminEdit = ($isSuperAdmin && in_array($status, ['open','assigned'], true));
                $canEdit      = ($canSelfEdit || $canAdminEdit);
                $editDuration = function_exists('lb_support_shift_duration_minutes') ? lb_support_shift_duration_minutes($s) : 480;
                $editStartHM  = lb_ss_5($s['start_time'] ?? '00:00');
                $isMissed = ($endTs > 0 && $nowTs > $endTs && in_array($status, ['open','assigned'], true));
                $badgeK   = ($canResume ? 'resume' : ($isMissed ? 'missed' : (($isPast && $status!=='completed') ? 'past' : $status)));
            ?>
            <div class="ss-shift ss-shift-<?= lb_ss_h($badgeK) ?> <?= $status==='active'?'is-active':'' ?>">
                <div class="ss-shift-headline">
                    <div class="ss-shift-name"><?= lb_ss_h($s['title']) ?></div>
                    <span class="ss-badge <?= lb_ss_h($badgeK) ?>"><?= lb_ss_h(lb_ss_badge_label($badgeK)) ?></span>
                </div>

                <div class="ss-shift-meta">
                    <div class="ss-shift-time js-rng"
                         data-si="<?= lb_ss_h($startIso) ?>"
                         data-ei="<?= lb_ss_h($endIso) ?>">
                        <i class="fa-duotone fa-clock"></i>
                        <span><?= lb_ss_5($s['start_time']) ?> – <?= lb_ss_5($s['end_time']) ?></span>
                    </div>
                    <div class="ss-who ss-who-list">
                        <?php if ($participantCount > 0): ?>
                            <?php foreach ($participants as $idx => $participant): if ($idx >= 4) break; ?>
                                <span class="ss-person-chip">
                                    <?= lb_ss_avatar(lb_ss_admin_icon($participant), $participant['username'] ?? 'Admin') ?>
                                    <span><?= lb_ss_h($participant['username'] ?? 'Admin') ?></span>
                                    <small class="ss-person-time"><?= lb_ss_h(lb_ss_5(($participant['planned_start_time'] ?? null) ?: $s['start_time'])) ?>–<?= lb_ss_h(lb_ss_5(($participant['planned_end_time'] ?? null) ?: $s['end_time'])) ?></small>
                                </span>
                            <?php endforeach; ?>
                            <?php if ($participantCount > 4): ?><span class="ss-person-more">+<?= (int)($participantCount - 4) ?></span><?php endif; ?>
                        <?php elseif (!empty($s['assigned_admin_id'])): ?>
                            <span class="ss-person-chip">
                                <?= lb_ss_avatar($s['assigned_icon'] ?? '', $assigned) ?>
                                <span><?= lb_ss_h($assigned) ?></span>
                            </span>
                        <?php else: ?>
                            <i class="fa-duotone fa-user-headset"></i>
                            <span>Open shift</span>
                        <?php endif; ?>
                    </div>
                    <?php if ($participantCount > 0): ?>
                    <?php foreach ($coverageGaps as $__gap): ?>
                        <div class="ss-gap-row">
                            <span><i class="fa-duotone fa-triangle-exclamation"></i> Coverage gap <?= date('H:i',$__gap[0]) ?>–<?= date('H:i',$__gap[1]) ?></span>
                            <?php if (!$isOwnParticipant && $__gap[1] > time()): ?>
                                <button type="button" class="ss-btn xs primary ss-act" data-action="support_shift_take_gap" data-shift-id="<?= (int)$s['id'] ?>" data-start-time="<?= date('H:i',$__gap[0]) ?>" data-end-time="<?= date('H:i',$__gap[1]) ?>" data-confirm="Take over <?= date('H:i',$__gap[0]) ?>–<?= date('H:i',$__gap[1]) ?>?">Take over</button>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </div>

                <div class="ss-acts">
                    <div class="ss-acts-main">
                        <?php if (in_array($status, ['open','assigned'], true) && !$isOwnParticipant): ?>
                            <button class="ss-btn xs ss-act" data-action="support_shift_claim" data-shift-id="<?= (int)$s['id'] ?>">Claim</button>
                        <?php endif; ?>
                        <?php if ($status === 'active' && $isCur && !$isOwnParticipant): ?>
                            <button class="ss-btn xs primary ss-act" data-action="support_shift_claim" data-shift-id="<?= (int)$s['id'] ?>"><i class="fa-duotone fa-user-plus"></i> Join</button>
                        <?php endif; ?>
                        <?php if (in_array($status,['open','assigned','active','paused','completed'],true) || $canResume): ?>
                            <?php if (($isCur && $status !== 'active') || $canResume): ?>
                                <button class="ss-btn xs primary ss-act" data-action="support_shift_start" data-shift-id="<?= (int)$s['id'] ?>"><i class="fa-duotone fa-play"></i> <?= $canResume ? 'Resume' : ($status === 'paused' ? 'Resume' : 'Start') ?></button>
                            <?php elseif ($status === 'active' && $isCur && !$isOwnParticipant): ?>
                                <!-- Join button above handles active multi-admin shifts. -->
                            <?php elseif ($isPast): ?>
                                <span class="ss-note">Past shift</span>
                            <?php elseif ($isFut): ?>
                                <span class="ss-note">Starts later</span>
                            <?php else: ?>
                                <span class="ss-note">Not available</span>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                    <div class="ss-acts-admin">
                        <?php if ($canEdit): ?>
                            <button class="ss-btn xs js-shift-edit-open" type="button"
                                    data-shift-id="<?= (int)$s['id'] ?>"
                                    data-date="<?= lb_ss_h($date) ?>"
                                    data-title="<?= lb_ss_h($s['title'] ?? 'Support Shift') ?>"
                                    data-start="<?= lb_ss_h($editStartHM) ?>"
                                    data-duration="<?= (int)$editDuration ?>"
                                    data-assignee="<?= lb_ss_h($isAssign ? $assigned : 'Open shift') ?>"
                                    title="Edit start time & duration">
                                <i class="fa-duotone fa-pen-to-square"></i> Edit
                            </button>
                        <?php elseif ($isOwnAssigned && $status === 'assigned' && $startTs > 0 && $nowTs >= ($startTs - 86400) && $nowTs < $startTs): ?>
                            <button class="ss-btn xs is-locked js-unassign-locked" type="button" data-title="Edit not possible" data-message="Less than 24 hours before the shift starts. Please contact Ricardo or Kevin to change this shift.">
                                <i class="fa-duotone fa-pen-to-square"></i> Edit
                            </button>
                        <?php endif; ?>
                        <?php if ($isOwnParticipant && in_array($status,['assigned','active','paused'],true)): ?>
                            <button class="ss-btn xs js-participant-hours-open" type="button"
                                    data-shift-id="<?= (int)$s['id'] ?>"
                                    data-admin-id="<?= (int)$currentAdminId ?>"
                                    data-title="<?= lb_ss_h($s['title']) ?>"
                                    data-start="<?= lb_ss_h(lb_ss_5(($ownParticipant['planned_start_time'] ?? null) ?: $s['start_time'])) ?>"
                                    data-end="<?= lb_ss_h(lb_ss_5(($ownParticipant['planned_end_time'] ?? null) ?: $s['end_time'])) ?>">
                                <i class="fa-duotone fa-clock"></i> My hours
                            </button>
                        <?php endif; ?>
                        <?php if ($canSelfUnassign): ?>
                            <button class="ss-btn xs unassign ss-act" data-action="support_shift_unassign" data-shift-id="<?= (int)$s['id'] ?>" data-confirm="Unassign yourself from this shift?">Unassign</button>
                        <?php elseif ($isSelfUnassignLocked): ?>
                            <button class="ss-btn xs unassign is-locked js-unassign-locked" type="button" data-title="Unassign not possible" data-message="Unassign is not possible less than 24 hours before the shift starts. Please contact Ricardo or Kevin to change this shift.">Unassign</button>
                        <?php endif; ?>
                        <?php if ($isSuperAdmin && in_array($status,['open','assigned'],true)): ?>
                            <button class="ss-btn xs" data-bs-toggle="modal" data-bs-target="#ssAssignModal" data-shift-id="<?= (int)$s['id'] ?>">Assign</button>
                        <?php endif; ?>
                        <?php if ($isSuperAdmin): ?>
                            <button class="ss-btn xs danger ss-act" data-action="support_shift_delete" data-shift-id="<?= (int)$s['id'] ?>" data-confirm="Delete this shift?">Delete</button>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<!-- ── Team Stats Dashboard ── -->
<?php if ($isSuperAdmin): ?>
<div class="ss-team-dashboard mb-4" id="ssTeamDashboard">
    <div class="ss-team-head">
        <div class="ss-team-title-wrap">
            <div class="ss-team-icon"><i class="fa-duotone fa-chart-simple"></i></div>
            <div>
                <div class="ss-team-title">Team overview</div>
                <div class="ss-team-sub"><?= lb_ss_h($statsLabel) ?>, <?= date('d.m.Y', strtotime($fromRaw)) ?> to <?= date('d.m.Y', strtotime($toRaw)) ?></div>
            </div>
        </div>
        <div class="ss-stat-tabs">
            <button type="button" class="ss-stat-tab js-stat-range" data-range="today">Today</button>
            <button type="button" class="ss-stat-tab js-stat-range is-active" data-range="week">This week</button>
            <button type="button" class="ss-stat-tab js-stat-range" data-range="month">This month</button>
            <form class="d-flex gap-2 js-stats-form" method="GET" action="<?= ADMN_URL ?>/support-shifts">
                <input type="hidden" class="js-date-in" name="from" value="<?= $from ?>" data-label="Custom">
                <input type="hidden" name="to" id="ss-stats-to" value="<?= $to ?>">
            </form>
        </div>
    </div>

    <div class="ss-team-kpis">
        <div class="ss-team-kpi total"><span>Assigned shifts</span><strong><?= (int)$statsTotals['total'] ?></strong></div>
        <div class="ss-team-kpi done"><span>Completed</span><strong><?= (int)$statsTotals['completed'] ?></strong></div>
        <div class="ss-team-kpi active"><span>Active now</span><strong><?= (int)$statsTotals['active'] ?></strong></div>
        <div class="ss-team-kpi missed"><span>Missed</span><strong><?= (int)$statsTotals['missed'] ?></strong></div>
        <div class="ss-team-kpi hours"><span>Hours worked</span><strong><?= lb_ss_worked($statsTotals['worked']) ?></strong></div>
    </div>

    <div class="ss-team-list">
        <?php if (empty($stats)): ?>
            <div class="ss-team-empty">No stats for this period.</div>
        <?php endif; ?>
        <?php foreach ($stats as $row): ?>
            <?php
                $total = max(0, (int)($row['total_shifts'] ?? 0));
                $done = max(0, (int)($row['completed_shifts'] ?? 0));
                $activeCount = max(0, (int)($row['active_shifts'] ?? 0));
                $missed = max(0, (int)($row['not_started'] ?? 0));
                $pct = $total > 0 ? min(100, round(($done / $total) * 100)) : 0;
            ?>
            <div class="ss-team-row">
                <div class="ss-team-person">
                    <?= lb_ss_avatar(lb_ss_admin_icon($row), $row['username'] ?? 'Admin') ?>
                    <div>
                        <div class="ss-team-name"><?= lb_ss_h($row['username'] ?: 'Admin') ?></div>
                        <div class="ss-team-mail"><?= lb_ss_h($row['email'] ?: '') ?></div>
                    </div>
                </div>

                <div class="ss-team-progress">
                    <div class="ss-team-progress-top">
                        <span><?= $done ?> / <?= $total ?> completed</span>
                        <strong><?= $pct ?>%</strong>
                    </div>
                    <div class="ss-team-progress-track"><span style="width:<?= $pct ?>%;"></span></div>
                </div>

                <div class="ss-team-metrics">
                    <div class="ss-team-metric"><span>Assigned</span><strong><?= $total ?></strong></div>
                    <div class="ss-team-metric done"><span>Done</span><strong><?= $done ?></strong></div>
                    <div class="ss-team-metric active"><span>Active</span><strong><?= $activeCount ?></strong></div>
                    <div class="ss-team-metric missed"><span>Missed</span><strong><?= $missed ?></strong></div>
                    <div class="ss-team-metric hours"><span>Worked</span><strong><?= lb_ss_worked((int)($row['worked_minutes'] ?? 0)) ?></strong></div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>
<?php endif; ?>

</div><!-- /ss-wrap -->


<div class="ss-detail-modal" id="ssDetailModal" aria-hidden="true">
    <div class="ss-detail-backdrop" data-detail-close></div>
    <div class="ss-detail-panel" role="dialog" aria-modal="true">
        <div class="ss-detail-head">
            <div>
                <div class="ss-detail-kicker" id="ssDetailStatus">Shift</div>
                <div class="ss-detail-title" id="ssDetailTitle">Shift details</div>
            </div>
            <button type="button" class="ss-detail-close" data-detail-close><i class="fa-duotone fa-xmark"></i></button>
        </div>
        <div class="ss-detail-body">
            <div class="ss-detail-row"><span>Date</span><strong id="ssDetailDate"></strong></div>
            <div class="ss-detail-row"><span>Time</span><strong id="ssDetailTime"></strong></div>
            <div class="ss-detail-row"><span>Assigned to</span><strong id="ssDetailAdmin"></strong></div>
        </div>
    </div>
</div>

<!-- ── Modals ── -->
<?php if ($isSuperAdmin): ?>
<div class="modal fade" id="ssCreateModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form class="modal-content ss-form">
            <input type="hidden" name="action" value="support_shift_create">
            <div class="modal-header">
                <h5 class="modal-title">Create Shift</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3"><label class="form-label">Title</label><input type="text" class="form-control" name="title" value="Support Shift" required></div>
                <div class="mb-3"><label class="form-label">Date</label><input type="hidden" class="js-date-in" name="shift_date" value="<?= date('Y-m-d') ?>" data-label="Shift date" required></div>
                <div class="row">
                    <div class="col-6 mb-3"><label class="form-label">Start time (Berlin)</label><input type="hidden" class="js-time-in js-create-start" name="start_time" value="09:00" required></div>
                    <div class="col-6 mb-3">
                        <label class="form-label">Duration</label>
                        <div class="ss-dur-wrap">
                            <input type="range" min="1" max="12" step="1" value="8" class="js-create-duration">
                            <span class="ss-dur-out"><span class="js-create-duration-out">8</span> h</span>
                        </div>
                        <input type="hidden" name="duration_minutes" class="js-create-duration-min" value="480">
                        <div class="ss-dur-derived">Ends at <span class="js-create-end">17:00</span> (Berlin)</div>
                    </div>
                </div>
                <div class="mb-1">
                    <label class="form-label">Assign to</label>
                    <select class="form-select js-admin-select" name="assigned_admin_id">
                        <option value="0" data-icon="open">Open shift</option>
                        <?php foreach ($admins as $a): ?>
                        <option value="<?= (int)$a['id'] ?>" data-name="<?= lb_ss_h($a['username']) ?>" data-meta="<?= lb_ss_h($a['email']) ?>" data-avatar="<?= lb_ss_h(lb_ss_admin_icon($a)) ?>"><?= lb_ss_h($a['username']) ?>, <?= lb_ss_h($a['email']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="ss-btn" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="ss-btn primary">Create</button>
            </div>
        </form>
    </div>
</div>

<!-- ── Standard three-shift generator ── -->
<div class="modal fade" id="ssTemplatesModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content ss-form">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fa-duotone fa-calendar-week"></i> Standard shifts</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p class="text-muted mb-3" style="font-size:12.5px;">
                    Create the three standard open shifts for every selected day. Supporters can claim them afterwards.
                    Existing matching shifts are kept and will not be duplicated.
                </p>

                <div style="display:grid; gap:8px; margin-bottom:16px;">
                    <div class="ss-detail-row"><span>Morning shift</span><strong>06:00 &ndash; 14:00</strong></div>
                    <div class="ss-detail-row"><span>Day shift</span><strong>14:00 &ndash; 22:00</strong></div>
                    <div class="ss-detail-row"><span>Night shift</span><strong>22:00 &ndash; 06:00</strong></div>
                </div>

                <div class="d-flex align-items-center gap-2 flex-wrap">
                    <span style="font-size:12.5px; color:var(--ss-muted);">Create for:</span>
                    <select class="form-select js-tpl-apply-days" style="max-width:170px; font-size:12.5px;">
                        <option value="1">today</option>
                        <option value="7" selected>next 7 days</option>
                        <option value="14">next 14 days</option>
                        <option value="21">next 21 days</option>
                        <option value="28">next 28 days</option>
                    </select>
                    <button type="button" class="ss-btn primary js-tpl-apply">
                        <i class="fa-duotone fa-calendar-plus"></i> Create shifts
                    </button>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="ss-btn" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- ── Edit shift modal ── -->
<div class="modal fade" id="ssEditModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form class="modal-content ss-form" id="ssEditForm">
            <input type="hidden" name="action" value="support_shift_edit_time">
            <input type="hidden" name="shift_id" id="ssEditShiftId" value="">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fa-duotone fa-pen-to-square"></i> Edit shift time</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="ss-detail-row" style="margin-bottom:10px;">
                    <span>Shift</span><strong id="ssEditTitle">Support Shift</strong>
                </div>
                <div class="ss-detail-row" style="margin-bottom:10px;">
                    <span>Date</span><strong id="ssEditDate">&mdash;</strong>
                </div>
                <div class="ss-detail-row" style="margin-bottom:14px;">
                    <span>Assigned to</span><strong id="ssEditAssignee">&mdash;</strong>
                </div>

                <div class="row">
                    <div class="col-6 mb-3">
                        <label class="form-label">Start time (Berlin)</label>
                        <input type="hidden" class="js-time-in js-edit-start" name="start_time" value="09:00" required>
                    </div>
                    <div class="col-6 mb-3">
                        <label class="form-label">Duration</label>
                        <div class="ss-dur-wrap">
                            <input type="range" min="1" max="12" step="1" value="8" class="js-edit-duration">
                            <span class="ss-dur-out"><span class="js-edit-duration-out">8</span> h</span>
                        </div>
                        <input type="hidden" name="duration_minutes" class="js-edit-duration-min" value="480">
                        <div class="ss-dur-derived">Ends at <span class="js-edit-end">17:00</span> (Berlin)</div>
                    </div>
                </div>

                <div style="font-size:11.5px; color:var(--ss-muted); background:rgba(255,255,255,.03); padding:8px 10px; border-radius:8px; border:1px solid rgba(255,255,255,.05);">
                    <i class="fa-duotone fa-circle-info"></i>
                    Changing the times only affects <em>this</em> shift. Your standard schedule stays the same.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="ss-btn" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="ss-btn primary"><i class="fa-duotone fa-save"></i> Save</button>
            </div>
        </form>
    </div>
</div>

<div class="modal fade" id="ssParticipantHoursModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <form class="modal-content ss-form">
            <input type="hidden" name="action" value="support_shift_adjust_participant_hours">
            <input type="hidden" name="shift_id" id="ss-ph-shift-id">
            <input type="hidden" name="admin_id" id="ss-ph-admin-id">
            <div class="modal-header">
                <h5 class="modal-title">Adjust my hours</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="small text-muted mb-3" id="ss-ph-title"></div>
                <div class="row">
                    <div class="col-6"><label class="form-label">Start</label><input class="form-control" type="time" name="start_time" id="ss-ph-start" required></div>
                    <div class="col-6"><label class="form-label">End</label><input class="form-control" type="time" name="end_time" id="ss-ph-end" required></div>
                </div>
                <div class="mt-3 small text-muted">Shortening your hours creates a visible coverage gap that another supporter can take over.</div>
            </div>
            <div class="modal-footer">
                <button type="button" class="ss-btn" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="ss-btn primary">Save hours</button>
            </div>
        </form>
    </div>
</div>

<div class="modal fade" id="ssRangeModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <form class="modal-content ss-form">
            <input type="hidden" name="action" value="support_shift_assign_range">
            <input type="hidden" name="shift_id" id="ss-range-shift-id">
            <div class="modal-header">
                <h5 class="modal-title" id="ss-range-modal-title">Claim time range</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="small text-muted mb-3" id="ss-range-shift-title"></div>
                <div class="row">
                    <div class="col-6"><label class="form-label">Start</label><input class="form-control" type="time" step="3600" name="start_time" id="ss-range-start" required></div>
                    <div class="col-6"><label class="form-label">End</label><input class="form-control" type="time" step="3600" name="end_time" id="ss-range-end" required></div>
                </div>
                <?php if ($isSuperAdmin): ?>
                <div class="mt-3" id="ss-range-admin-wrap">
                    <label class="form-label">Supporter</label>
                    <select class="form-select js-admin-select" name="assigned_admin_id" id="ss-range-admin">
                        <option value="<?= (int)$currentAdminId ?>">Myself</option>
                        <?php foreach ($admins as $a): ?>
                        <option value="<?= (int)$a['id'] ?>" data-name="<?= lb_ss_h($a['username']) ?>" data-meta="Admin" data-avatar="<?= lb_ss_h(lb_ss_admin_icon($a)) ?>"><?= lb_ss_h($a['username']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <?php else: ?>
                <input type="hidden" name="assigned_admin_id" value="<?= (int)$currentAdminId ?>">
                <?php endif; ?>
                <div class="mt-3 small text-muted">The selected period must be inside this standard shift. Occupied hours must be released first before they can be assigned again.</div>
            </div>
            <div class="modal-footer">
                <button type="button" class="ss-btn" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="ss-btn primary" id="ss-range-submit">Save range</button>
            </div>
        </form>
    </div>
</div>

<!-- Hidden form used by JS to submit template save/delete/apply -->
<form id="ssTplForm" style="display:none;">
    <input type="hidden" name="action" value="">
    <input type="hidden" name="template_id" value="">
    <input type="hidden" name="admin_id" value="">
    <input type="hidden" name="weekday" value="">
    <input type="hidden" name="start_time" value="">
    <input type="hidden" name="duration_minutes" value="">
    <input type="hidden" name="title" value="Support Shift">
    <input type="hidden" name="active" value="1">
    <input type="hidden" name="from" value="">
    <input type="hidden" name="days" value="">
</form>
<div class="modal fade" id="ssAssignModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <form class="modal-content ss-form">
            <input type="hidden" name="action" value="support_shift_assign">
            <input type="hidden" name="shift_id" id="ss-assign-id">
            <div class="modal-header">
                <h5 class="modal-title">Assign Shift</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <select class="form-select js-admin-select" name="assigned_admin_id">
                    <option value="0" data-icon="open">Open shift</option>
                    <?php foreach ($admins as $a): ?>
                    <option value="<?= (int)$a['id'] ?>" data-name="<?= lb_ss_h($a['username']) ?>" data-meta="Admin" data-avatar="<?= lb_ss_h(lb_ss_admin_icon($a)) ?>"><?= lb_ss_h($a['username']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="modal-footer">
                <button type="button" class="ss-btn" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="ss-btn primary">Save</button>
            </div>
        </form>
    </div>
</div>

<div class="modal fade" id="ssEndActionModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content">
            <button type="button" class="ss-end-close" data-ss-end-close aria-label="Close">
                <i class="fa-solid fa-xmark"></i>
            </button>
            <div class="ss-end-body">
                <div class="ss-end-top">
                    <div class="ss-end-icon"><i class="fa-solid fa-power-off"></i></div>
                    <div>
                        <h5 class="ss-end-title">End Shift?</h5>
                        <p class="ss-end-text">Are you sure you want to end your current shift? The timer will stop immediately.</p>
                    </div>
                </div>
            </div>
            <div class="ss-end-footer">
                <button type="button" class="ss-end-btn" data-ss-end-close>Keep Working</button>
                <button type="button" class="ss-end-btn danger" id="ssEndActionConfirmBtn">End Shift</button>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<div class="modal fade" id="ssInfoActionModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content">
            <button type="button" class="ss-info-close" data-ss-info-close aria-label="Close">
                <i class="fa-solid fa-xmark"></i>
            </button>
            <div class="ss-info-body">
                <div class="ss-info-top">
                    <div class="ss-info-icon"><i class="fa-solid fa-triangle-exclamation"></i></div>
                    <div>
                        <h5 class="ss-info-title" id="ssInfoActionTitle">Unassign not possible</h5>
                        <p class="ss-info-text" id="ssInfoActionText">Unassign is not possible less than 24 hours before the shift starts. Please contact Ricardo or Kevin to change this shift.</p>
                    </div>
                </div>
            </div>
            <div class="ss-info-footer">
                <button type="button" class="ss-info-btn" data-ss-info-close>Got it</button>
            </div>
        </div>
    </div>
</div>

<script>
(function(){
    const AJAX   = '<?= AJAX_URL ?>';
    const locale = navigator.language || 'en-GB';
    function pad(n){ return String(n).padStart(2,'0'); }

    /* ── range labels (local timezone) ── */
    function fmtRange(si,ei){
        if(!si||!ei) return '';
        const s=new Date(si), e=new Date(ei);
        if(isNaN(s)||isNaN(e)) return '';
        const o={hour:'2-digit',minute:'2-digit',hour12:false};
        return s.toLocaleTimeString(locale,o).replace(/^24:/,'00:')+' – '+e.toLocaleTimeString(locale,o).replace(/^24:/,'00:');
    }
    document.querySelectorAll('.js-rng').forEach(function(el){
        const r=fmtRange(el.dataset.si,el.dataset.ei);
        if(r){
            if(el.classList.contains('ss-shift-time')){
                el.innerHTML='<i class="fa-duotone fa-clock" style="color:rgba(255,255,255,.3);"></i> '+r;
            } else {
                el.textContent=r;
            }
        }
    });

    /* ── day-of-week labels ── */
    document.querySelectorAll('.js-dow').forEach(function(el){
        if(el.dataset.date){
            const d=new Date(el.dataset.date+'T12:00:00');
            el.textContent=d.toLocaleDateString(locale,{weekday:'short'});
        }
    });

    /* ── active shift timer + automatic end at planned shift end ── */
    const timer=document.querySelector('.js-timer');
    let ssAutoEndRunning = false;

    function autoEndActiveShift(){
        if(ssAutoEndRunning) return;
        const endBtn=document.querySelector('.ss-active-card.is-on .ss-act[data-action="support_shift_end"]');
        if(!endBtn || !endBtn.dataset.shiftId) return;
        ssAutoEndRunning = true;
        post('support_shift_auto_end',{shift_id:endBtn.dataset.shiftId}).then(function(res){
            if(res&&res.success){ toast('success',res.message||'Shift ended automatically.'); setTimeout(function(){ location.reload(); },500); }
            else { ssAutoEndRunning = false; }
        }).catch(function(){ ssAutoEndRunning = false; });
    }

    if(timer&&timer.dataset.sa){
        const start=new Date(String(timer.dataset.sa).replace(' ','T')).getTime();
        const range=document.querySelector('.ss-active-card.is-on .js-rng[data-ei]');
        const end=range&&range.dataset.ei ? new Date(range.dataset.ei).getTime() : NaN;
        if(!isNaN(start)){
            setInterval(function(){
                if(!isNaN(end) && Date.now() >= end){
                    timer.textContent='Ended';
                    autoEndActiveShift();
                    return;
                }
                const diff=Math.max(0,Math.floor((Date.now()-start)/1000));
                timer.textContent=pad(Math.floor(diff/3600))+':'+pad(Math.floor((diff%3600)/60))+':'+pad(diff%60);
            },1000);
            if(!isNaN(end)){
                const delay=Math.max(0,end-Date.now()+750);
                window.setTimeout(autoEndActiveShift,delay);
            }
        }
    }

    /* ── toast ── */
    function toast(type,msg){
        const el=document.getElementById('ss-toast-el');
        const body=document.getElementById('ss-toast-body');
        if(!el||!body) return;
        el.className='toast align-items-center text-white border-0 bg-'+(type==='success'?'success':type==='danger'?'danger':'secondary');
        body.textContent=msg;
        if(typeof bootstrap!=='undefined') bootstrap.Toast.getOrCreateInstance(el,{delay:3500}).show();
    }

    /* ── ajax ── */
    function post(action,data){
        const fd=new FormData(); fd.append('action',action);
        Object.keys(data||{}).forEach(function(k){ fd.append(k,data[k]); });
        return fetch(AJAX,{method:'POST',body:fd,credentials:'same-origin'}).then(function(r){ return r.json(); });
    }
    function postForm(form){ return fetch(AJAX,{method:'POST',body:new FormData(form),credentials:'same-origin'}).then(function(r){ return r.json(); }); }

    /* ── form submit ── */
    document.addEventListener('submit',function(e){
        const form=e.target.closest('.ss-form');
        if(!form) return;
        e.preventDefault();
        postForm(form).then(function(res){
            if(res&&res.success){ toast('success',res.message||'Saved.'); if(res.refreshPage) setTimeout(function(){ location.reload(); },600); }
            else toast('danger',(res&&res.message)||'Request failed.');
        }).catch(function(){ toast('danger','Request failed.'); });
    });

    /* ── action buttons ── */
    let pendingEndActionBtn = null;

    function openEndShiftModal(btn){
        pendingEndActionBtn = btn;
        const m=document.getElementById('ssEndActionModal');
        if(!m){ runEndShift(btn); return; }
        if(typeof bootstrap!=='undefined' && bootstrap.Modal){
            bootstrap.Modal.getOrCreateInstance(m).show();
        } else {
            m.classList.add('show','ss-end-fallback-open');
            m.removeAttribute('aria-hidden');
            document.body.classList.add('modal-open');
        }
    }

    function closeEndShiftModal(){
        const m=document.getElementById('ssEndActionModal');
        if(!m) return;
        if(typeof bootstrap!=='undefined' && bootstrap.Modal){
            const inst=bootstrap.Modal.getInstance(m);
            if(inst) inst.hide();
        }
        m.classList.remove('show','ss-end-fallback-open');
        m.setAttribute('aria-hidden','true');
        document.body.classList.remove('modal-open');
    }

    function openInfoActionModal(title, message){
        const m=document.getElementById('ssInfoActionModal');
        if(!m){ toast('warning', message || 'Action is not possible.'); return; }
        const t=document.getElementById('ssInfoActionTitle');
        const body=document.getElementById('ssInfoActionText');
        if(t) t.textContent = title || 'Notice';
        if(body) body.textContent = message || 'This action is not possible right now.';
        if(typeof bootstrap!=='undefined' && bootstrap.Modal){
            bootstrap.Modal.getOrCreateInstance(m).show();
        } else {
            m.classList.add('show','ss-info-fallback-open');
            m.removeAttribute('aria-hidden');
            document.body.classList.add('modal-open');
        }
    }

    function closeInfoActionModal(){
        const m=document.getElementById('ssInfoActionModal');
        if(!m) return;
        if(typeof bootstrap!=='undefined' && bootstrap.Modal){
            const inst=bootstrap.Modal.getInstance(m);
            if(inst) inst.hide();
        }
        m.classList.remove('show','ss-info-fallback-open');
        m.setAttribute('aria-hidden','true');
        document.body.classList.remove('modal-open');
    }

    function runEndShift(btn){
        if(!btn) return;
        const confirmBtn=document.getElementById('ssEndActionConfirmBtn');
        if(confirmBtn) confirmBtn.disabled = true;
        post(btn.dataset.action,{shift_id:btn.dataset.shiftId,start_time:btn.dataset.startTime||'',end_time:btn.dataset.endTime||''}).then(function(res){
            if(confirmBtn) confirmBtn.disabled = false;
            closeEndShiftModal();
            if(res&&res.success){ toast('success',res.message||'Shift ended.'); setTimeout(function(){ location.reload(); },500); }
            else toast('danger',(res&&res.message)||'Request failed.');
        }).catch(function(){
            if(confirmBtn) confirmBtn.disabled = false;
            toast('danger','Request failed.');
        });
    }

    document.addEventListener('click',function(e){
        const closeBtn=e.target.closest('[data-ss-end-close]');
        if(closeBtn){ e.preventDefault(); closeEndShiftModal(); return; }

        const infoCloseBtn=e.target.closest('[data-ss-info-close]');
        if(infoCloseBtn){ e.preventDefault(); closeInfoActionModal(); return; }

        const lockedUnassignBtn=e.target.closest('.js-unassign-locked');
        if(lockedUnassignBtn){
            e.preventDefault();
            openInfoActionModal(lockedUnassignBtn.dataset.title || 'Unassign not possible', lockedUnassignBtn.dataset.message || 'Unassign is not possible less than 24 hours before the shift starts. Please contact Ricardo or Kevin to change this shift.');
            return;
        }

        if(e.target&&e.target.id==='ssEndActionConfirmBtn'){
            e.preventDefault();
            runEndShift(pendingEndActionBtn);
            return;
        }

        const hoursBtn=e.target.closest('.js-participant-hours-open');
        if(hoursBtn){
            e.preventDefault();
            document.getElementById('ss-ph-shift-id').value=hoursBtn.dataset.shiftId||'';
            document.getElementById('ss-ph-admin-id').value=hoursBtn.dataset.adminId||'';
            document.getElementById('ss-ph-title').textContent=hoursBtn.dataset.title||'Support Shift';
            document.getElementById('ss-ph-start').value=hoursBtn.dataset.start||'';
            document.getElementById('ss-ph-end').value=hoursBtn.dataset.end||'';
            if(typeof bootstrap!=='undefined') bootstrap.Modal.getOrCreateInstance(document.getElementById('ssParticipantHoursModal')).show();
            return;
        }

        const rangeBtn=e.target.closest('.js-range-open');
        if(rangeBtn){
            e.preventDefault();
            const mode=rangeBtn.dataset.mode||'claim';
            document.getElementById('ss-range-shift-id').value=rangeBtn.dataset.shiftId||'';
            document.getElementById('ss-range-shift-title').textContent=rangeBtn.dataset.title||'Support Shift';
            document.getElementById('ss-range-start').value=rangeBtn.dataset.start||'';
            document.getElementById('ss-range-end').value=rangeBtn.dataset.end||'';
            document.getElementById('ss-range-modal-title').textContent=mode==='assign'?'Assign time range':'Claim time range';
            document.getElementById('ss-range-submit').textContent=mode==='assign'?'Assign range':'Claim range';
            const aw=document.getElementById('ss-range-admin-wrap');
            if(aw) aw.style.display=mode==='assign'?'block':'none';
            const as=document.getElementById('ss-range-admin');
            if(as && mode!=='assign') as.value='<?= (int)$currentAdminId ?>';
            if(typeof bootstrap!=='undefined') bootstrap.Modal.getOrCreateInstance(document.getElementById('ssRangeModal')).show();
            return;
        }

        const btn=e.target.closest('.ss-act');
        if(btn){
            e.preventDefault();
            if(btn.dataset.confirm && !window.confirm(btn.dataset.confirm)) return;
            if(btn.dataset.action === 'support_shift_end'){
                openEndShiftModal(btn);
                return;
            }
            post(btn.dataset.action,{shift_id:btn.dataset.shiftId,start_time:btn.dataset.startTime||'',end_time:btn.dataset.endTime||''}).then(function(res){
                if(res&&res.success){ toast('success',res.message||'Done.'); if(res.refreshPage) setTimeout(function(){ location.reload(); },600); }
                else toast('danger',(res&&res.message)||'Request failed.');
            }).catch(function(){ toast('danger','Request failed.'); });
            return;
        }

        const ab=e.target.closest('[data-bs-target="#ssAssignModal"]');
        if(ab){ const inp=document.getElementById('ss-assign-id'); if(inp) inp.value=ab.dataset.shiftId||''; }
    });



    /* custom dark admin selects */
    function initAdminSelect(select){
        if(!select||select.dataset.adminSelectReady==='1') return;
        select.dataset.adminSelectReady='1';
        select.classList.add('ss-admin-native');
        const wrap=document.createElement('div');
        wrap.className='ss-admin-select';
        const btn=document.createElement('button');
        btn.type='button';
        btn.className='ss-admin-select__button';
        const menu=document.createElement('div');
        menu.className='ss-admin-select__menu';
        select.parentNode.insertBefore(wrap,select);
        wrap.appendChild(select);
        wrap.appendChild(btn);
        wrap.appendChild(menu);

        function optionData(opt){
            const raw=(opt.textContent||'').trim();
            const parts=raw.split(',');
            const isOpen=String(opt.value)==='0';
            return {
                value:opt.value,
                name:opt.dataset.name || (isOpen ? 'Open shift' : (parts[0]||raw).trim()),
                meta:opt.dataset.meta || (isOpen ? 'No admin assigned' : (parts.slice(1).join(',').trim() || 'Admin')),
                avatar:opt.dataset.avatar || '',
                open:isOpen
            };
        }
        function esc(v){ return String(v||'').replace(/[&<>'"]/g,function(c){ return {'&':'&amp;','<':'&lt;','>':'&gt;',"'":'&#39;','"':'&quot;'}[c]; }); }
        function icon(d){
            if(d.avatar&&!d.open) return '<img src="'+esc(d.avatar)+'" alt="'+esc(d.name)+'">';
            return d.open ? '<i class="fa-duotone fa-user-clock"></i>' : '<i class="fa-duotone fa-user-shield"></i>';
        }
        function rowHtml(d){
            return '<span class="ss-admin-select__icon '+(d.open?'is-open':'')+'">'+icon(d)+'</span><span class="ss-admin-select__text"><span class="ss-admin-select__name">'+esc(d.name)+'</span><span class="ss-admin-select__meta">'+esc(d.meta)+'</span></span>';
        }
        function render(){
            const selected=optionData(select.options[select.selectedIndex]||select.options[0]);
            btn.innerHTML='<span class="ss-admin-select__current">'+rowHtml(selected)+'</span><i class="fa-duotone fa-chevron-down ss-admin-select__chev"></i>';
            let html='';
            Array.prototype.forEach.call(select.options,function(opt){
                const d=optionData(opt);
                html+='<button type="button" class="ss-admin-select__option '+(opt.selected?'is-selected':'')+'" data-value="'+d.value+'">'+rowHtml(d)+'</button>';
            });
            menu.innerHTML=html;
        }
        btn.addEventListener('click',function(e){
            e.stopPropagation();
            document.querySelectorAll('.ss-admin-select.is-open').forEach(function(o){ if(o!==wrap) o.classList.remove('is-open'); });
            wrap.classList.toggle('is-open');
            render();
        });
        menu.addEventListener('click',function(e){
            e.stopPropagation();
            const opt=e.target.closest('[data-value]');
            if(!opt) return;
            select.value=opt.dataset.value;
            select.dispatchEvent(new Event('change',{bubbles:true}));
            wrap.classList.remove('is-open');
            render();
        });
        select.addEventListener('change',render);
        render();
    }
    document.querySelectorAll('.js-admin-select').forEach(initAdminSelect);

    /* ── date picker ── */
    const FL='en-GB';
    function pymd(v){ const p=String(v||'').split('-').map(Number); return(p.length===3&&p[0]&&p[1]&&p[2])?new Date(p[0],p[1]-1,p[2]):new Date(); }
    function ymd(d){ return d.getFullYear()+'-'+pad(d.getMonth()+1)+'-'+pad(d.getDate()); }
    function fbd(v){ const d=pymd(v); return d.toLocaleDateString(FL,{month:'short',day:'2-digit',year:'numeric'}); }
    function initDP(input){
        if(!input||input.dataset.dpReady==='1') return; input.dataset.dpReady='1';
        const wrap=document.createElement('div'); wrap.className='ss-date-wrap';
        const btn=document.createElement('button'); btn.type='button'; btn.className='ss-date-button';
        btn.innerHTML='<span></span><i class="fa-duotone fa-calendar"></i>';
        const pop=document.createElement('div'); pop.className='ss-date-popover';
        wrap.appendChild(btn); wrap.appendChild(pop); input.parentNode.insertBefore(wrap,input.nextSibling); wrap.appendChild(input);
        let view=pymd(input.value);
        function render(){
            const sel=pymd(input.value),today=new Date();
            btn.querySelector('span').textContent=input.value?fbd(input.value):(input.dataset.label||'Select date');
            const first=new Date(view.getFullYear(),view.getMonth(),1),start=new Date(first);
            start.setDate(first.getDate()-first.getDay());
            let h='<div class="ss-date-head"><button type="button" class="ss-date-nav" data-nav="prev"><i class="fa-duotone fa-chevron-left"></i></button><div class="ss-date-title">'+first.toLocaleDateString(FL,{month:'long',year:'numeric'})+'</div><button type="button" class="ss-date-nav" data-nav="next"><i class="fa-duotone fa-chevron-right"></i></button></div>';
            h+='<div class="ss-date-weekdays"><span>Su</span><span>Mo</span><span>Tu</span><span>We</span><span>Th</span><span>Fr</span><span>Sa</span></div><div class="ss-date-days">';
            for(let i=0;i<42;i++){ const d=new Date(start); d.setDate(start.getDate()+i); const c=['ss-date-day']; if(d.getMonth()!==view.getMonth()) c.push('is-muted'); if(ymd(d)===ymd(today)) c.push('is-today'); if(ymd(d)===ymd(sel)) c.push('is-selected'); h+='<button type="button" class="'+c.join(' ')+'" data-date="'+ymd(d)+'">'+d.getDate()+'</button>'; }
            h+='</div><div class="ss-date-foot"><button type="button" class="ss-date-link" data-clear="1">Clear</button><button type="button" class="ss-date-link" data-today="1">Today</button></div>';
            pop.innerHTML=h;
        }
        btn.addEventListener('click',function(e){ e.stopPropagation(); document.querySelectorAll('.ss-date-wrap.is-open').forEach(function(o){ if(o!==wrap) o.classList.remove('is-open'); }); wrap.classList.toggle('is-open'); render(); });
        pop.addEventListener('click',function(e){
            e.stopPropagation();
            const nav=e.target.closest('[data-nav]'); if(nav){ view.setMonth(view.getMonth()+(nav.dataset.nav==='next'?1:-1)); render(); return; }
            const day=e.target.closest('[data-date]'); if(day){ input.value=day.dataset.date; input.dispatchEvent(new Event('change',{bubbles:true})); wrap.classList.remove('is-open'); render(); return; }
            if(e.target.closest('[data-today]')){ const t=new Date(); input.value=ymd(t); view=t; input.dispatchEvent(new Event('change',{bubbles:true})); wrap.classList.remove('is-open'); render(); return; }
            if(e.target.closest('[data-clear]')){ input.value=''; input.dispatchEvent(new Event('change',{bubbles:true})); wrap.classList.remove('is-open'); render(); return; }
        });
        input.addEventListener('change',function(){
            btn.querySelector('span').textContent=input.value?fbd(input.value):(input.dataset.label||'Select date');
            const weekForm = input.closest('.js-week-form');
            if (weekForm && input.name === 'from' && typeof loadWeek === 'function') {
                const from = weekForm.querySelector('[name="from"]')?.value || '';
                const to = weekForm.querySelector('[name="to"]')?.value || '';
                if (from && to) window.setTimeout(function(){ loadWeek(from,to); }, 0);
            }
        });
        render();
    }
    document.querySelectorAll('.js-date-in').forEach(initDP);

    /* ── time picker ── */
    function nt(v){ const m=String(v||'00:00').match(/^(\d{1,2}):(\d{1,2})/); if(!m) return '00:00'; return pad(Math.max(0,Math.min(23,parseInt(m[1],10))))+':'+pad(Math.max(0,Math.min(59,parseInt(m[2],10)))); }
    function initTP(input){
        if(!input||input.dataset.tpReady==='1') return; input.dataset.tpReady='1'; input.value=nt(input.value);
        const wrap=document.createElement('div'); wrap.className='ss-time-picker';
        const btn=document.createElement('button'); btn.type='button'; btn.className='ss-time-button'; btn.innerHTML='<span></span><i class="fa-duotone fa-clock"></i>';
        const pop=document.createElement('div'); pop.className='ss-time-popover';
        wrap.appendChild(btn); wrap.appendChild(pop); input.parentNode.insertBefore(wrap,input.nextSibling); wrap.appendChild(input);
        function render(){ const v=nt(input.value),parts=v.split(':'); btn.querySelector('span').textContent=v; let h='<div class="ss-time-cols"><div class="ss-time-col">'; for(let i=0;i<24;i++) h+='<button type="button" class="ss-time-opt'+(pad(i)===parts[0]?' is-selected':'')+'" data-hour="'+pad(i)+'">'+pad(i)+'</button>'; h+='</div><div class="ss-time-col">'; for(let m=0;m<60;m+=5) h+='<button type="button" class="ss-time-opt'+(pad(m)===parts[1]?' is-selected':'')+'" data-minute="'+pad(m)+'">'+pad(m)+'</button>'; h+='</div></div>'; pop.innerHTML=h; }
        btn.addEventListener('click',function(e){ e.stopPropagation(); document.querySelectorAll('.ss-time-picker.is-open').forEach(function(o){ if(o!==wrap) o.classList.remove('is-open'); }); wrap.classList.toggle('is-open'); render(); });
        pop.addEventListener('click',function(e){ e.stopPropagation(); const hr=e.target.closest('[data-hour]'),mn=e.target.closest('[data-minute]'),parts=nt(input.value).split(':'); if(hr) parts[0]=hr.dataset.hour; if(mn) parts[1]=mn.dataset.minute; if(hr||mn){ input.value=parts[0]+':'+parts[1]; input.dispatchEvent(new Event('change',{bubbles:true})); render(); } });
        input.addEventListener('change',function(){ input.value=nt(input.value); btn.querySelector('span').textContent=input.value; });
        render();
    }
    document.querySelectorAll('.js-time-in').forEach(initTP);


    /* ── realtime week navigation, keeps URL clean ── */
    let ssWeekIsLoading = false;

    function syncWeekToInputs() {
        document.querySelectorAll('.js-date-in[name="from"]').forEach(function(inp){
            if (inp.dataset.weekSyncReady === '1') return;
            inp.dataset.weekSyncReady = '1';
            inp.addEventListener('change',function(){
                const d=pymd(this.value); d.setDate(d.getDate()+6);
                const to=document.getElementById('ss-to')||document.getElementById('ss-stats-to');
                if(to) to.value=ymd(d);
            });
        });
    }

    function reInitWeekControls(){
        document.querySelectorAll('.js-date-in').forEach(initDP);
        syncWeekToInputs();
    }

    function setWeekLoading(state){
        document.querySelectorAll('.ss-bar,.ss-grid,.ss-stats').forEach(function(el){
            el.classList.toggle('ss-week-loading', !!state);
        });
    }

    function loadWeek(from,to){
        if(ssWeekIsLoading || !from || !to) return;
        ssWeekIsLoading = true;
        setWeekLoading(true);
        const base = '<?= ADMN_URL ?>/support-shifts';
        const url = base + '?from=' + encodeURIComponent(from) + '&to=' + encodeURIComponent(to) + '&partial=week';
        fetch(url, { headers: { 'X-Requested-With':'XMLHttpRequest' }, credentials:'same-origin' })
            .then(function(r){ if(!r.ok) throw new Error('Week request failed'); return r.text(); })
            .then(function(html){
                const doc = new DOMParser().parseFromString(html, 'text/html');
                const newBar = doc.querySelector('.ss-bar');
                const newGrid = doc.querySelector('.ss-grid');
                const oldBar = document.querySelector('.ss-bar');
                const oldGrid = document.querySelector('.ss-grid');
                const newKpis = doc.querySelector('.ss-planner-kpis');
                const oldKpis = document.querySelector('.ss-planner-kpis');
                const newActiveList = doc.querySelector('.ss-active-list');
                const oldActiveList = document.querySelector('.ss-active-list');
                if(!newBar || !newGrid || !oldBar || !oldGrid) throw new Error('Week markup missing');
                oldBar.replaceWith(newBar);
                if(newKpis && oldKpis) oldKpis.replaceWith(newKpis);
                if(oldActiveList && newActiveList) oldActiveList.replaceWith(newActiveList);
                if(oldActiveList && !newActiveList) oldActiveList.remove();
                if(!oldActiveList && newActiveList) oldGrid.parentNode.insertBefore(newActiveList, oldGrid);
                oldGrid.replaceWith(newGrid);
                reInitWeekControls();
            })
            .catch(function(){
                toast('error','Could not load the selected week.');
            })
            .finally(function(){
                ssWeekIsLoading = false;
                setWeekLoading(false);
            });
    }

    document.addEventListener('click',function(e){
        const btn = e.target.closest('.js-week-nav');
        if(!btn) return;
        e.preventDefault();
        loadWeek(btn.dataset.from, btn.dataset.to);
    });

    document.addEventListener('submit',function(e){
        const form = e.target.closest('.js-week-form');
        if(!form) return;
        e.preventDefault();
        const from = form.querySelector('[name="from"]')?.value || '';
        const to = form.querySelector('[name="to"]')?.value || '';
        loadWeek(from,to);
    });

    function statRange(range){
        const today = new Date();
        const from = new Date(today);
        const to = new Date(today);
        if(range === 'week'){
            const day = (today.getDay() + 6) % 7;
            from.setDate(today.getDate() - day);
            to.setDate(from.getDate() + 6);
        }
        if(range === 'month'){
            from.setDate(1);
            to.setMonth(from.getMonth() + 1, 0);
        }
        return [ymd(from), ymd(to)];
    }

    function setStatsLoading(state){
        const box = document.querySelector('#ssTeamDashboard');
        if(box) box.classList.toggle('ss-week-loading', !!state);
    }

    function loadStats(from,to,activeRange){
        if(!from || !to) return;
        setStatsLoading(true);
        const base = '<?= ADMN_URL ?>/support-shifts';
        const url = base + '?from=' + encodeURIComponent(from) + '&to=' + encodeURIComponent(to) + '&partial=week';
        fetch(url, { headers: { 'X-Requested-With':'XMLHttpRequest' }, credentials:'same-origin' })
            .then(function(r){ if(!r.ok) throw new Error('Stats request failed'); return r.text(); })
            .then(function(html){
                const doc = new DOMParser().parseFromString(html, 'text/html');
                const fresh = doc.querySelector('#ssTeamDashboard');
                const current = document.querySelector('#ssTeamDashboard');
                if(!fresh || !current) throw new Error('Stats markup missing');
                current.replaceWith(fresh);
                document.querySelectorAll('.js-stat-range').forEach(function(b){
                    b.classList.toggle('is-active', b.dataset.range === activeRange);
                });
                reInitWeekControls();
            })
            .catch(function(){
                toast('error','Could not load the selected stats.');
            })
            .finally(function(){
                setStatsLoading(false);
            });
    }

    document.addEventListener('click',function(e){
        const btn = e.target.closest('.js-stat-range');
        if(!btn) return;
        e.preventDefault();
        const range = btn.dataset.range || 'week';
        document.querySelectorAll('.js-stat-range').forEach(function(b){ b.classList.remove('is-active'); });
        btn.classList.add('is-active');
        const dates = statRange(range);
        loadStats(dates[0], dates[1], range);
    });


    /* shift card detail modal removed: cards no longer open a modal on click */

    /* ── close pickers on outside click ── */
    document.addEventListener('click',function(){ document.querySelectorAll('.ss-date-wrap.is-open,.ss-time-picker.is-open,.ss-admin-select.is-open').forEach(function(w){ w.classList.remove('is-open'); }); });

    /* ── from→to sync ── */
    syncWeekToInputs();

    /* ═════════════════════════════════════════════════════════════════
       CREATE-MODAL: duration slider drives derived end time
       ═════════════════════════════════════════════════════════════════ */
    function computeEndTime(startStr, durationHours){
        const m = String(startStr||'09:00').match(/^(\d{1,2}):(\d{1,2})/);
        if(!m) return '17:00';
        let mins = parseInt(m[1],10) * 60 + parseInt(m[2],10) + durationHours * 60;
        mins = ((mins % 1440) + 1440) % 1440;
        return pad(Math.floor(mins/60)) + ':' + pad(mins%60);
    }
    function refreshCreateEnd(){
        const startInp = document.querySelector('.js-create-start');
        const durInp   = document.querySelector('.js-create-duration');
        const durOut   = document.querySelector('.js-create-duration-out');
        const endOut   = document.querySelector('.js-create-end');
        const durMin   = document.querySelector('.js-create-duration-min');
        if(!startInp || !durInp) return;
        const h = parseInt(durInp.value, 10) || 8;
        if(durOut) durOut.textContent = h;
        if(durMin) durMin.value = h * 60;
        if(endOut) endOut.textContent = computeEndTime(startInp.value, h);
    }
    document.querySelectorAll('.js-create-duration').forEach(function(inp){
        inp.addEventListener('input', refreshCreateEnd);
        inp.addEventListener('change', refreshCreateEnd);
    });
    document.querySelectorAll('.js-create-start').forEach(function(inp){
        inp.addEventListener('change', refreshCreateEnd);
    });
    refreshCreateEnd();

    /* ═════════════════════════════════════════════════════════════════
       TEMPLATES MODAL: tabs + save/delete/apply + add row
       ═════════════════════════════════════════════════════════════════ */

    function tplPost(action, extras){
        const form = document.getElementById('ssTplForm');
        if(!form) return Promise.resolve();
        const set = function(name, val){
            const f = form.querySelector('[name="'+name+'"]');
            if(f) f.value = val === undefined || val === null ? '' : val;
        };
        // Reset relevant fields
        ['template_id','admin_id','weekday','start_time','duration_minutes','from','days'].forEach(function(k){ set(k,''); });
        set('active', 1);
        set('title', 'Support Shift');
        set('action', action);
        Object.keys(extras || {}).forEach(function(k){ set(k, extras[k]); });

        const fd = new FormData(form);
        return fetch(AJAX, { method:'POST', body:fd, credentials:'same-origin' })
            .then(function(r){ return r.json().catch(function(){ return {}; }); });
    }

    // Tab switch
    document.addEventListener('click', function(e){
        const btn = e.target.closest('.js-tpl-day');
        if(!btn) return;
        const wd = btn.dataset.wd;
        document.querySelectorAll('.js-tpl-day').forEach(function(b){ b.classList.toggle('is-active', b.dataset.wd === wd); });
        document.querySelectorAll('.js-tpl-panel').forEach(function(p){ p.style.display = p.dataset.wd === wd ? '' : 'none'; });
    });

    // Save existing row
    document.addEventListener('click', function(e){
        const btn = e.target.closest('.js-tpl-save');
        if(!btn) return;
        const id = btn.dataset.id;
        const row = btn.closest('.ss-tpl-row');
        if(!row) return;
        const panel = row.closest('.js-tpl-panel');
        const wd = panel ? panel.dataset.wd : '';
        const adminId = row.querySelector('.js-tpl-admin').value;
        const startTime = row.querySelector('.js-tpl-start').value;
        const durMin = row.querySelector('.js-tpl-duration').value;

        btn.disabled = true;
        tplPost('support_shift_template_save', {
            template_id: id,
            admin_id: adminId,
            weekday: wd,
            start_time: startTime,
            duration_minutes: durMin,
        }).then(function(res){
            btn.disabled = false;
            if(res && res.success){
                if(window.location) window.location.reload();
            } else {
                alert((res && res.message) || 'Save failed.');
            }
        });
    });

    // Delete row
    document.addEventListener('click', function(e){
        const btn = e.target.closest('.js-tpl-delete');
        if(!btn) return;
        if(!confirm('Delete this template? Already-created shifts will not be removed.')) return;
        const id = btn.dataset.id;
        btn.disabled = true;
        tplPost('support_shift_template_delete', { template_id: id }).then(function(res){
            btn.disabled = false;
            if(res && res.success){
                if(window.location) window.location.reload();
            } else {
                alert((res && res.message) || 'Delete failed.');
            }
        });
    });

    // Add new template row (creates immediately with sensible defaults, then reloads)
    document.addEventListener('click', function(e){
        const btn = e.target.closest('.js-tpl-add');
        if(!btn) return;
        const wd = btn.dataset.wd;
        // Choose the first admin option available
        const firstAdmin = document.querySelector('#ssTemplatesModal .js-tpl-admin option, #ssCreateModal .js-admin-select option[value]:not([value="0"])');
        let adminId = '';
        if(firstAdmin){ adminId = firstAdmin.value; }
        if(!adminId){
            // Fallback: read admins from create-modal select
            const opts = document.querySelectorAll('#ssCreateModal select[name="assigned_admin_id"] option');
            for(let i=0;i<opts.length;i++){ if(opts[i].value && opts[i].value !== '0'){ adminId = opts[i].value; break; } }
        }
        if(!adminId){ alert('No admin available to assign.'); return; }

        btn.disabled = true;
        tplPost('support_shift_template_save', {
            template_id: 0,
            admin_id: adminId,
            weekday: wd,
            start_time: '09:00',
            duration_minutes: 480,
        }).then(function(res){
            btn.disabled = false;
            if(res && res.success){
                if(window.location) window.location.reload();
            } else {
                alert((res && res.message) || 'Add failed.');
            }
        });
    });

    // Apply templates
    document.addEventListener('click', function(e){
        const btn = e.target.closest('.js-tpl-apply');
        if(!btn) return;
        const daysSel = document.querySelector('.js-tpl-apply-days');
        const days = daysSel ? daysSel.value : '7';
        const today = new Date();
        const from = today.getFullYear() + '-' + pad(today.getMonth()+1) + '-' + pad(today.getDate());
        btn.disabled = true;
        tplPost('support_shift_template_apply', {
            from: from,
            days: days,
        }).then(function(res){
            btn.disabled = false;
            if(res && res.success){
                alert(res.message || 'Applied.');
                if(window.location) window.location.reload();
            } else {
                alert((res && res.message) || 'Apply failed.');
            }
        });
    });

    /* ═════════════════════════════════════════════════════════════════
       EDIT-MODAL: open with prefilled values, duration→end live update
       ═════════════════════════════════════════════════════════════════ */
    function refreshEditEnd(){
        const startInp = document.querySelector('.js-edit-start');
        const durInp   = document.querySelector('.js-edit-duration');
        const durOut   = document.querySelector('.js-edit-duration-out');
        const endOut   = document.querySelector('.js-edit-end');
        const durMin   = document.querySelector('.js-edit-duration-min');
        if(!startInp || !durInp) return;
        const h = parseInt(durInp.value, 10) || 8;
        if(durOut) durOut.textContent = h;
        if(durMin) durMin.value = h * 60;
        if(endOut) endOut.textContent = computeEndTime(startInp.value, h);
    }
    document.querySelectorAll('.js-edit-duration').forEach(function(inp){
        inp.addEventListener('input', refreshEditEnd);
        inp.addEventListener('change', refreshEditEnd);
    });
    document.querySelectorAll('.js-edit-start').forEach(function(inp){
        inp.addEventListener('change', refreshEditEnd);
    });

    // Open Edit modal — prefill from button data-attributes
    document.addEventListener('click', function(e){
        const btn = e.target.closest('.js-shift-edit-open');
        if(!btn) return;
        e.preventDefault();

        const shiftId = btn.dataset.shiftId || '';
        const date    = btn.dataset.date || '';
        const title   = btn.dataset.title || 'Support Shift';
        const start   = btn.dataset.start || '09:00';
        const durMin  = parseInt(btn.dataset.duration || '480', 10);
        const durH    = Math.min(12, Math.max(1, Math.round(durMin / 60)));
        const assignee = btn.dataset.assignee || '—';

        document.getElementById('ssEditShiftId').value = shiftId;
        document.getElementById('ssEditTitle').textContent = title;
        document.getElementById('ssEditDate').textContent  = date;
        document.getElementById('ssEditAssignee').textContent = assignee;

        const startInp = document.querySelector('.js-edit-start');
        const durInp   = document.querySelector('.js-edit-duration');
        if(startInp){
            startInp.value = start;
            if(startInp.dataset.tpReady === '1'){
                // Trigger time picker to refresh its visual button
                startInp.dispatchEvent(new Event('change', {bubbles:true}));
            }
        }
        if(durInp){ durInp.value = durH; }
        refreshEditEnd();

        // Show modal via Bootstrap
        const modalEl = document.getElementById('ssEditModal');
        if(modalEl && window.bootstrap && window.bootstrap.Modal){
            const inst = window.bootstrap.Modal.getOrCreateInstance(modalEl);
            inst.show();
        } else if (modalEl) {
            modalEl.classList.add('show');
            modalEl.style.display = 'block';
        }
    });

    // Submit Edit form via AJAX
    document.addEventListener('submit', function(e){
        const form = e.target.closest('#ssEditForm');
        if(!form) return;
        e.preventDefault();

        const fd = new FormData(form);
        const submitBtn = form.querySelector('button[type=submit]');
        if(submitBtn) submitBtn.disabled = true;

        fetch(AJAX, { method:'POST', body:fd, credentials:'same-origin' })
            .then(function(r){ return r.json().catch(function(){ return {}; }); })
            .then(function(res){
                if(submitBtn) submitBtn.disabled = false;
                if(res && res.success){
                    if(window.location) window.location.reload();
                } else {
                    alert((res && res.message) || 'Save failed.');
                }
            })
            .catch(function(){
                if(submitBtn) submitBtn.disabled = false;
                alert('Network error.');
            });
    });



    /* Shift detail side panel */
    const shiftBackdrop = document.getElementById('ssShiftBackdrop');
    const shiftPanelClose = document.getElementById('ssPanelClose');
    function syncShiftPanel(){
        const openPanel = document.querySelector('.ss-shift-group[open]');
        document.body.classList.toggle('ss-panel-open', !!openPanel);
    }
    document.addEventListener('toggle', function(e){
        const details = e.target;
        if(!details || !details.classList || !details.classList.contains('ss-shift-group')) return;
        if(details.open){
            document.querySelectorAll('.ss-shift-group[open]').forEach(function(other){
                if(other !== details) other.open = false;
            });
        }
        syncShiftPanel();
    }, true);
    function closeShiftPanel(){
        const openPanel = document.querySelector('.ss-shift-group[open]');
        if(openPanel) openPanel.open = false;
        syncShiftPanel();
    }
    if(shiftBackdrop) shiftBackdrop.addEventListener('click', closeShiftPanel);
    if(shiftPanelClose) shiftPanelClose.addEventListener('click', closeShiftPanel);
    document.addEventListener('keydown', function(e){ if(e.key === 'Escape') closeShiftPanel(); });
    syncShiftPanel();
})();
</script>
