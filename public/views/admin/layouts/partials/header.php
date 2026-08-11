<?php
$__supportShiftHeaderActive = null;
if (!function_exists('lb_support_shift_header_iso')) {
    function lb_support_shift_header_iso(array $shift, string $which = 'start'): string
    {
        $tz = new DateTimeZone('Europe/Berlin');
        $date = (string)($shift['shift_date'] ?? date('Y-m-d'));
        $time = (string)($which === 'end' ? ($shift['end_time'] ?? '00:00:00') : ($shift['start_time'] ?? '00:00:00'));
        $startTime = (string)($shift['start_time'] ?? '00:00:00');
        $dt = new DateTime($date . ' ' . $time, $tz);
        if ($which === 'end' && strcmp(substr($time, 0, 5), substr($startTime, 0, 5)) <= 0) {
            $dt->modify('+1 day');
        }
        return $dt->format(DateTime::ATOM);
    }
}
if (function_exists('lb_support_shift_can_access') && lb_support_shift_can_access() && function_exists('lb_support_shift_current_admin_id')) {
    try {
        global $db;
        if (isset($db)) {
            if (function_exists('lb_support_shift_ensure_tables')) lb_support_shift_ensure_tables();
            $sid = (int) lb_support_shift_current_admin_id();
            if ($sid > 0) {
                // Primary: find active shift via support_shift_participants
                try {
                    $__myRows = $db->run(
                        "SELECT s.*, p.planned_start_time AS participant_start_time,
                                p.planned_end_time AS participant_end_time
                         FROM support_shift_participants p
                         INNER JOIN support_shifts s ON s.id = p.shift_id
                         WHERE p.admin_id = ? AND p.status IN ('active','paused')
                           AND ? >= TIMESTAMP(s.shift_date, COALESCE(p.planned_start_time, s.start_time))
                           AND ? < (CASE
                               WHEN COALESCE(p.planned_end_time, s.end_time) <= COALESCE(p.planned_start_time, s.start_time)
                               THEN DATE_ADD(TIMESTAMP(s.shift_date, COALESCE(p.planned_end_time, s.end_time)), INTERVAL 1 DAY)
                               ELSE TIMESTAMP(s.shift_date, COALESCE(p.planned_end_time, s.end_time))
                           END)
                         ORDER BY p.started_at DESC LIMIT 1",
                        $sid, date('Y-m-d H:i:s'), date('Y-m-d H:i:s')
                    ) ?: [];
                    if (!empty($__myRows[0])) $__supportShiftHeaderActive = $__myRows[0];
                } catch (Throwable $e) {}
                // Fallback: legacy assigned_admin_id column
                if (empty($__supportShiftHeaderActive)) {
                    $__modernRows = $db->run(
                        "SELECT id FROM support_shift_participants
                         WHERE admin_id = ? AND status IN ('assigned','active','paused')
                         LIMIT 1",
                        $sid
                    ) ?: [];
                    if (empty($__modernRows)) {
                        $__fbRows = $db->run(
                            "SELECT * FROM support_shifts WHERE assigned_admin_id = ? AND status IN ('active','paused') ORDER BY started_at DESC LIMIT 1",
                            $sid
                        ) ?: [];
                        $__supportShiftHeaderActive = $__fbRows[0] ?? null;
                    }
                }
                // Load co-admins in the same shift
                if (!empty($__supportShiftHeaderActive)) {
                    $__shiftId = (int)($__supportShiftHeaderActive['id'] ?? 0);
                    if ($__shiftId > 0) {
                        $__seen = [$sid => true];
                        try {
                            $__coRows = $db->run(
                                "SELECT p.admin_id, a.username, a.icon
                                 FROM support_shift_participants p
                                 INNER JOIN support_shifts s ON s.id = p.shift_id
                                 LEFT JOIN admins a ON a.id = p.admin_id
                                 WHERE p.shift_id = ? AND p.admin_id != ? AND p.status IN ('active','paused')
                                   AND ? >= TIMESTAMP(s.shift_date, COALESCE(p.planned_start_time, s.start_time))
                                   AND ? < (CASE
                                       WHEN COALESCE(p.planned_end_time, s.end_time) <= COALESCE(p.planned_start_time, s.start_time)
                                       THEN DATE_ADD(TIMESTAMP(s.shift_date, COALESCE(p.planned_end_time, s.end_time)), INTERVAL 1 DAY)
                                       ELSE TIMESTAMP(s.shift_date, COALESCE(p.planned_end_time, s.end_time))
                                   END)
                                 ORDER BY p.started_at ASC",
                                $__shiftId, $sid, date('Y-m-d H:i:s'), date('Y-m-d H:i:s')
                            ) ?: [];
                            foreach ($__coRows as $__cr) {
                                $__cid = (int)($__cr['admin_id'] ?? 0);
                                if ($__cid > 0 && !isset($__seen[$__cid])) { $__seen[$__cid] = true; $__ssCoAdmins[] = $__cr; }
                            }
                        } catch (Throwable $e) {}
                        if (empty($__coRows)) {
                            try {
                                $__modernShiftRows = $db->run(
                                "SELECT id FROM support_shift_participants
                                 WHERE shift_id = ? AND status IN ('assigned','active','paused')
                                 LIMIT 1",
                                $__shiftId
                            ) ?: [];
                            if (empty($__modernShiftRows)) {
                                $__coRows2 = $db->run(
                                    "SELECT p.admin_id, a.username, a.icon
                                     FROM support_shift_admins p
                                     LEFT JOIN admins a ON a.id = p.admin_id
                                     WHERE p.shift_id = ? AND p.admin_id != ? AND p.status IN ('active','paused')
                                     ORDER BY p.started_at ASC",
                                    $__shiftId, $sid
                                ) ?: [];
                                foreach ($__coRows2 as $__cr) {
                                    $__cid = (int)($__cr['admin_id'] ?? 0);
                                    if ($__cid > 0 && !isset($__seen[$__cid])) { $__seen[$__cid] = true; $__ssCoAdmins[] = $__cr; }
                                }
                            }
                            } catch (Throwable $e) {}
                        }
                    }
                }
            }
        }
    } catch (Throwable $e) {
        $__supportShiftHeaderActive = null;
        $__ssCoAdmins = [];
    }
}
$__ssStartedTs  = (!empty($__supportShiftHeaderActive['started_at'])) ? strtotime($__supportShiftHeaderActive['started_at']) : 0;
$__ssNowTs      = time();
$__ssVisible    = !empty($__supportShiftHeaderActive) && $__ssStartedTs > 0;
$__ssStatus     = $__ssVisible ? strtolower((string)($__supportShiftHeaderActive['status'] ?? 'active')) : 'idle';
$__ssPaused     = $__ssStatus === 'paused';
$__ssCanAccess  = function_exists('lb_support_shift_can_access') && lb_support_shift_can_access();
?>
<style>
/* ── dropdown divider (original) ── */
.dropdown-divider{margin:.5rem 0;position:relative;text-align:center}
.dropdown-divider::before{content:'';position:absolute;top:50%;left:0;right:0;border-top:1px solid #e9ecef;transform:translateY(-50%)}
.dropdown-divider::after{content:'or';position:relative;display:inline-block;padding:0 .5rem;background-color:#fff;color:#6c757d}
.dropdown-item{display:flex;align-items:center}
.dropdown-item i{margin-right:.5rem}

/* ── shift header chip, dashboard compact rebuild ── */
#header.navbar,
#header .navbar-nav-wrap {
    min-height: 56px !important;
    height: 56px !important;
}
#header .navbar-nav-wrap-content-end,
#header .navbar-nav {
    height: 56px !important;
    align-items: center !important;
}
#ssHeaderChip {
    width: 520px;
    max-width: calc(100vw - 760px);
    min-width: 460px;
    height: 42px;
    min-height: 42px;
    display: inline-flex;
    align-items: center;
    gap: 10px;
    padding: 0 10px;
    border-radius: 15px;
    background: #25282a;
    border: 1px solid rgba(255,255,255,.08);
    box-shadow: 0 2px 14px rgba(0,0,0,.18);
    cursor: default;
    user-select: none;
    white-space: nowrap;
    overflow: hidden;
}
#ssHeaderChip.is-active {
    background: linear-gradient(135deg, rgba(0,201,167,.12), #25282a 58%);
    border-color: rgba(0,201,167,.26);
}
#ssHeaderChip.is-paused {
    background: linear-gradient(135deg, rgba(245,202,153,.12), #25282a 58%);
    border-color: rgba(245,202,153,.26);
}
#ssHeaderChip.is-idle {
    background: linear-gradient(135deg, rgba(109,92,255,.12), #25282a 58%);
    border-color: rgba(109,92,255,.22);
}
#ssHeaderChip .sh-icon {
    width: 30px;
    height: 30px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: .82rem;
    flex: 0 0 30px;
    background: rgba(109,92,255,.18);
    color: #c4b5fd;
    border: 1px solid rgba(109,92,255,.22);
}
#ssHeaderChip.is-active .sh-icon {
    background: rgba(0,201,167,.13);
    color: #00c9a7;
    border-color: rgba(0,201,167,.24);
}
#ssHeaderChip.is-paused .sh-icon {
    background: rgba(245,202,153,.13);
    color: #f5ca99;
    border-color: rgba(245,202,153,.24);
}
#ssHeaderChip .sh-body {
    min-width: 0;
    flex: 1 1 auto;
    display: flex;
    flex-direction: column;
    gap: 3px;
    overflow: hidden;
}
#ssHeaderChip .sh-topline,
#ssHeaderChip .sh-subline {
    display: flex;
    align-items: center;
    min-width: 0;
    overflow: hidden;
}
#ssHeaderChip .sh-topline { gap: 8px; }
#ssHeaderChip .sh-subline { gap: 6px; }
#ssHeaderChip .sh-status {
    flex: 0 0 auto;
    display: inline-flex;
    align-items: center;
    gap: 5px;
    height: 18px;
    padding: 0 7px;
    border-radius: 99px;
    background: rgba(255,255,255,.045);
    border: 1px solid rgba(255,255,255,.08);
    font-size: 9px;
    font-weight: 900;
    text-transform: uppercase;
    letter-spacing: .06em;
    color: rgba(255,255,255,.48);
    line-height: 1;
}
#ssHeaderChip .sh-status .sh-dot {
    width: 5px;
    height: 5px;
    border-radius: 50%;
    background: rgba(255,255,255,.28);
}
#ssHeaderChip.is-active .sh-status {
    background: rgba(0,201,167,.10);
    border-color: rgba(0,201,167,.24);
    color: #00c9a7;
}
#ssHeaderChip.is-active .sh-dot {
    background: #00c9a7;
    box-shadow: 0 0 0 3px rgba(0,201,167,.13);
    animation: shDot 1.8s ease-in-out infinite;
}
#ssHeaderChip.is-paused .sh-status {
    background: rgba(245,202,153,.10);
    border-color: rgba(245,202,153,.24);
    color: #f5ca99;
}
#ssHeaderChip.is-paused .sh-dot { background: #f5ca99; }
@keyframes shDot { 0%,100%{box-shadow:0 0 0 0 rgba(0,201,167,.35)} 50%{box-shadow:0 0 0 5px rgba(0,201,167,0)} }
#ssHeaderChip .sh-title {
    min-width: 0;
    overflow: hidden;
    text-overflow: ellipsis;
    font-size: 12.5px;
    font-weight: 850;
    color: rgba(255,255,255,.92);
    line-height: 1;
}
#ssHeaderChip .sh-window,
#ssHeaderChip .sh-meta {
    overflow: hidden;
    text-overflow: ellipsis;
    font-size: 11px;
    color: rgba(255,255,255,.42);
    line-height: 1;
}
#ssHeaderChip .sh-meta::before {
    content: "•";
    margin-right: 6px;
    color: rgba(255,255,255,.23);
}
#ssHeaderChip .sh-actions {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    flex: 0 0 auto;
}
.sh-btn {
    height: 27px;
    padding: 0 10px;
    border-radius: 9px;
    border: 1px solid rgba(255,255,255,.10);
    background: rgba(255,255,255,.045);
    color: rgba(255,255,255,.74);
    font-size: 11px;
    font-weight: 800;
    line-height: 1;
    cursor: pointer;
    white-space: nowrap;
    transition: background .12s, border-color .12s, color .12s;
}
.sh-btn:hover { background: rgba(255,255,255,.09); color: #fff; border-color: rgba(255,255,255,.16); }
.sh-btn.primary { background: rgba(109,92,255,.18); border-color: rgba(109,92,255,.36); color: #c4b5fd; }
.sh-btn.primary:hover { background: rgba(109,92,255,.28); border-color: rgba(109,92,255,.48); }
.sh-btn.danger { background: rgba(237,76,120,.10); border-color: rgba(237,76,120,.25); color: #ed4c78; }
.sh-btn.danger:hover { background: rgba(237,76,120,.18); border-color: rgba(237,76,120,.38); color: #ff7b9b; }
@media (min-width: 1600px) { #ssHeaderChip { width: 560px; max-width: 560px; } }
@media (max-width: 1500px) { #ssHeaderChip { width: 460px; min-width: 420px; max-width: 34vw; } }
@media (max-width: 1320px) { #ssHeaderChip { width: 340px; min-width: 320px; max-width: 32vw; } #ssHeaderChip .sh-meta { display:none; } }
@media (max-width: 1100px) { #ssHeaderChip { width: 270px; min-width: 250px; } #ssHeaderChip .sh-window { display:none; } }
@media (max-width: 850px)  { #ssHeaderChip { display:none; } }

/* ── Login modal ── */
#ssLoginModal .modal-content {
    background: #1c1f21;
    border: 1px solid rgba(255,255,255,.08);
    border-radius: 20px;
    box-shadow: 0 32px 80px rgba(0,0,0,.55);
    overflow: hidden;
}
#ssLoginModal .ssl-header {
    padding: 24px 24px 0;
}
#ssLoginModal .ssl-badge {
    display: inline-flex; align-items: center; gap: 6px;
    background: rgba(109,92,255,.14); border: 1px solid rgba(109,92,255,.28);
    color: #a78bfa; border-radius: 100px;
    padding: 4px 10px; font-size: 11px; font-weight: 700;
    margin-bottom: 14px;
}
#ssLoginModal .ssl-title {
    font-size: 17px; font-weight: 800; color: rgba(255,255,255,.92); margin: 0 0 6px;
}
#ssLoginModal .ssl-sub {
    font-size: 13px; color: rgba(255,255,255,.42); margin: 0;
}
#ssLoginModal .ssl-body { padding: 20px 24px; }
#ssLoginModal .ssl-footer {
    padding: 0 24px 24px;
    display: flex; gap: 10px;
}
#ssLoginModal .ssl-no-shift {
    flex: 1;
    background: rgba(255,255,255,.04); border: 1px solid rgba(255,255,255,.08);
    color: rgba(255,255,255,.55); border-radius: 12px;
    padding: 0 16px; height: 44px;
    font-size: 13px; font-weight: 600;
    cursor: pointer; transition: all .12s;
}
#ssLoginModal .ssl-no-shift:hover { background: rgba(255,255,255,.08); color: rgba(255,255,255,.8); }
#ssLoginModal .ssl-open-btn {
    flex: 1;
    background: #6d5cff; border: 1px solid #6d5cff; color: #fff;
    border-radius: 12px; height: 44px;
    font-size: 13px; font-weight: 700;
    text-decoration: none; display: flex; align-items: center; justify-content: center;
    transition: background .12s;
}
#ssLoginModal .ssl-open-btn:hover { background: #5c4ae3; border-color: #5c4ae3; }
#ssLoginModal .ssl-close-btn {
    position: absolute; top: 18px; right: 20px;
    width: 28px; height: 28px; border-radius: 50%;
    background: rgba(255,255,255,.06); border: 1px solid rgba(255,255,255,.08);
    display: flex; align-items: center; justify-content: center;
    cursor: pointer; color: rgba(255,255,255,.45); font-size: 13px;
    transition: all .12s;
}
#ssLoginModal .ssl-close-btn:hover { background: rgba(255,255,255,.12); color: rgba(255,255,255,.8); }

/* Shift choice card */
.ssl-shift-card {
    display: flex; align-items: center; gap: 12px;
    background: rgba(255,255,255,.04); border: 1px solid rgba(255,255,255,.07);
    border-radius: 14px; padding: 13px 14px;
    cursor: pointer; width: 100%; text-align: left;
    transition: all .15s; margin-bottom: 8px;
}
.ssl-shift-card:last-child { margin-bottom: 0; }
.ssl-shift-card:hover { background: rgba(109,92,255,.09); border-color: rgba(109,92,255,.32); }
.ssl-shift-card .ssc-icon {
    width: 36px; height: 36px; border-radius: 10px; flex-shrink: 0;
    background: rgba(109,92,255,.16); border: 1px solid rgba(109,92,255,.22);
    display: flex; align-items: center; justify-content: center;
    color: #a78bfa; font-size: .95rem;
}
.ssl-shift-card.morning   .ssc-icon { background: rgba(251,191,36,.12); border-color: rgba(251,191,36,.2); color: #fbbf24; }
.ssl-shift-card.afternoon .ssc-icon { background: rgba(59,130,246,.12); border-color: rgba(59,130,246,.2); color: #60a5fa; }
.ssl-shift-card.night     .ssc-icon { background: rgba(139,92,246,.14); border-color: rgba(139,92,246,.22); color: #a78bfa; }
.ssl-shift-card .ssc-info { flex: 1; min-width: 0; }
.ssl-shift-card .ssc-title { font-size: 13.5px; font-weight: 700; color: rgba(255,255,255,.88); }
.ssl-shift-card .ssc-time  { font-size: 12px; color: rgba(255,255,255,.38); margin-top: 2px; }
.ssl-shift-card .ssc-start {
    padding: 5px 12px; border-radius: 8px; font-size: 12px; font-weight: 700;
    background: #6d5cff; color: #fff; flex-shrink: 0; transition: background .12s;
}
.ssl-shift-card:hover .ssc-start { background: #5c4ae3; }
.ssl-no-shifts {
    background: rgba(255,255,255,.03); border: 1px solid rgba(255,255,255,.06);
    border-radius: 12px; padding: 18px; text-align: center;
    color: rgba(255,255,255,.35); font-size: 13px;
}

/* ── Activity check modal ── */
#ssCheckModal .modal-content {
    background: #1c1f21; border: 1px solid rgba(255,255,255,.08);
    border-radius: 20px; box-shadow: 0 32px 80px rgba(0,0,0,.55); overflow: hidden;
}
#ssCheckModal .ssc-body { padding: 28px 24px; text-align: center; }
#ssCheckModal .ssc-emoji { font-size: 2.5rem; margin-bottom: 12px; }
#ssCheckModal .ssc-title { font-size: 18px; font-weight: 800; color: rgba(255,255,255,.92); margin-bottom: 6px; }
#ssCheckModal .ssc-sub   { font-size: 13px; color: rgba(255,255,255,.4); margin-bottom: 20px; }
#ssCheckModal .ssc-progress {
    height: 4px; border-radius: 4px; background: rgba(255,255,255,.07);
    overflow: hidden; margin-bottom: 24px;
}
#ssCheckModal .ssc-progress-fill {
    height: 100%; border-radius: 4px;
    background: linear-gradient(90deg, #6d5cff, #34d399);
    transition: width 1s linear;
}
#ssCheckModal .ssc-confirm {
    width: 100%; height: 48px; border-radius: 14px;
    background: #6d5cff; border: 0; color: #fff;
    font-size: 14px; font-weight: 700; cursor: pointer;
    display: flex; align-items: center; justify-content: center; gap: 8px;
    transition: background .12s;
}
#ssCheckModal .ssc-confirm:hover { background: #5c4ae3; }

/* ── End shift modal ── */
#ssEndShiftModal .modal-content {
    background:#1c1f21;
    border:1px solid rgba(255,255,255,.09);
    border-radius:20px;
    box-shadow:0 32px 80px rgba(0,0,0,.55);
    overflow:hidden;
}
#ssEndShiftModal .sse-body { padding:26px 24px 22px; }
#ssEndShiftModal .sse-top { display:flex; gap:14px; align-items:flex-start; padding-right:34px; }
#ssEndShiftModal .sse-icon {
    width:42px; height:42px; border-radius:14px; flex:0 0 auto;
    display:flex; align-items:center; justify-content:center;
    background:rgba(237,76,120,.14); border:1px solid rgba(237,76,120,.30);
    color:#ff6b96; font-size:1rem;
}
#ssEndShiftModal .sse-title { margin:0 0 6px; color:rgba(255,255,255,.94); font-size:18px; font-weight:900; }
#ssEndShiftModal .sse-text { margin:0; color:rgba(255,255,255,.48); font-size:13px; line-height:1.55; }
#ssEndShiftModal .sse-close {
    position:absolute; top:16px; right:16px; width:32px; height:32px; border-radius:11px;
    background:rgba(255,255,255,.05); border:1px solid rgba(255,255,255,.08);
    color:rgba(255,255,255,.38); display:flex; align-items:center; justify-content:center;
    cursor:pointer; transition:all .12s;
}
#ssEndShiftModal .sse-close:hover { background:rgba(255,255,255,.10); color:rgba(255,255,255,.78); }
#ssEndShiftModal .sse-footer {
    display:flex; gap:10px; justify-content:flex-end; padding:16px 24px 22px;
    border-top:1px solid rgba(255,255,255,.07); background:rgba(255,255,255,.02);
}
#ssEndShiftModal .sse-btn {
    min-width:126px; height:42px; border-radius:12px; border:1px solid rgba(255,255,255,.10);
    background:rgba(255,255,255,.055); color:rgba(255,255,255,.76);
    font-size:13px; font-weight:800; cursor:pointer; transition:all .12s;
}
#ssEndShiftModal .sse-btn:hover { background:rgba(255,255,255,.10); color:#fff; }
#ssEndShiftModal .sse-btn.danger { background:rgba(237,76,120,.16); border-color:rgba(237,76,120,.36); color:#ff6b96; }
#ssEndShiftModal .sse-btn.danger:hover { background:rgba(237,76,120,.25); border-color:rgba(237,76,120,.50); color:#ff8aad; }

@media (max-width: 1100px) { #ssHeaderChip .sh-meta { display: none; } }
@media (max-width: 850px)  { #ssHeaderChip .sh-window { display: none; } }
</style>

<header id="header" class="navbar navbar-expand-lg navbar-fixed navbar-height navbar-container navbar-bordered bg-white">
    <div class="navbar-nav-wrap">
        <!-- Logo -->
        <a class="navbar-brand" href="<?= BASE_URL ?>/admin-area/dashboard" aria-label="LoLBoost.gg">
            <img class="navbar-brand-logo" src="<?= ASSET_URL ?>/core/main/img/logos/PNG/logo-light.png?v6" alt="Logo" data-hs-theme-appearance="light">
            <img class="navbar-brand-logo" src="<?= ASSET_URL ?>/core/main/img/logos/PNG/logo-dark.png?v6"  alt="Logo" data-hs-theme-appearance="dark">
            <img class="navbar-brand-logo" src="<?= ASSET_URL ?>/core/main/img/logos/PNG/logo-dark.png?v6"  alt="Logo" data-hs-theme-appearance="default">
        </a>

        <div class="navbar-nav-wrap-content-start">
            <button type="button" class="js-navbar-vertical-aside-toggle-invoker navbar-aside-toggler">
                <i class="fa-duotone fa-left-from-line navbar-toggler-short-align"
                   data-bs-template='<div class="tooltip d-none d-md-block" role="tooltip"><div class="arrow"></div><div class="tooltip-inner"></div></div>'
                   data-bs-toggle="tooltip" data-bs-placement="right" title="Collapse"></i>
                <i class="fa-duotone fa-right-from-line navbar-toggler-full-align"
                   data-bs-template='<div class="tooltip d-none d-md-block" role="tooltip"><div class="arrow"></div><div class="tooltip-inner"></div></div>'
                   data-bs-toggle="tooltip" data-bs-placement="right" title="Expand"></i>
            </button>
        </div>

        <div class="navbar-nav-wrap-content-end">
            <ul class="navbar-nav align-items-center gap-2">

                <?php if ($__ssCanAccess): ?>
                <li class="nav-item d-none d-md-flex">
                    <div id="ssHeaderChip" class="is-<?= $__ssVisible ? ($__ssPaused ? 'paused' : 'active') : 'idle' ?>"
                         data-shift-id="<?= $__ssVisible ? (int)($__supportShiftHeaderActive['id'] ?? 0) : 0 ?>">

                        <div class="sh-icon">
                            <i class="fa-duotone fa-headset"></i>
                        </div>

                        <?php if ($__ssVisible && !empty($__ssCoAdmins)): ?>
                        <div class="sh-co-admins" style="display:inline-flex;align-items:center;gap:0;flex:0 0 auto;">
                            <?php foreach (array_slice($__ssCoAdmins, 0, 3) as $__ca): ?>
                            <?php $__caIcon = (string)($__ca['icon'] ?? ''); $__caName = (string)($__ca['username'] ?? 'Admin'); ?>
                            <?php if ($__caIcon !== ''): ?>
                            <img src="<?= esc($__caIcon) ?>" alt="<?= esc($__caName) ?>" title="<?= esc($__caName) ?> on shift"
                                 style="width:24px;height:24px;border-radius:50%;object-fit:cover;border:1.5px solid rgba(0,201,167,.35);margin-left:-6px;background:#25282a;">
                            <?php else: ?>
                            <span title="<?= esc($__caName) ?> on shift"
                                  style="width:24px;height:24px;border-radius:50%;border:1.5px solid rgba(0,201,167,.35);margin-left:-6px;background:rgba(0,201,167,.15);color:#00c9a7;display:inline-flex;align-items:center;justify-content:center;font-size:9px;font-weight:900;">
                                <?= esc(mb_strtoupper(mb_substr($__caName, 0, 1))) ?>
                            </span>
                            <?php endif; ?>
                            <?php endforeach; ?>
                            <?php if (count($__ssCoAdmins) > 3): ?>
                            <span style="width:24px;height:24px;border-radius:50%;border:1.5px solid rgba(255,255,255,.12);margin-left:-6px;background:rgba(255,255,255,.07);color:rgba(255,255,255,.6);display:inline-flex;align-items:center;justify-content:center;font-size:9px;font-weight:900;">
                                +<?= count($__ssCoAdmins) - 3 ?>
                            </span>
                            <?php endif; ?>
                        </div>
                        <?php endif; ?>

                        <div class="sh-body">
                            <div class="sh-topline">
                                <div class="sh-status">
                                    <span class="sh-dot"></span>
                                    <span id="ssHeaderStatusText"><?= $__ssVisible ? ($__ssPaused ? 'PAUSED' : 'ACTIVE') : 'OFF SHIFT' ?></span>
                                </div>
                                <div class="sh-title" id="ssHeaderTitle">
                                    <?= $__ssVisible ? esc($__supportShiftHeaderActive['title'] ?? 'Shift') : 'No active shift' ?>
                                </div>
                            </div>
                            <div class="sh-subline">
                                <div class="sh-window" id="ssHeaderWindow"
                                     data-start-iso="<?= $__ssVisible ? esc(lb_support_shift_header_iso($__supportShiftHeaderActive, 'start')) : '' ?>"
                                     data-end-iso="<?= $__ssVisible ? esc(lb_support_shift_header_iso($__supportShiftHeaderActive, 'end')) : '' ?>">
                                    <?= $__ssVisible ? esc(substr((string)($__supportShiftHeaderActive['start_time'] ?? ''), 0, 5) . ' – ' . substr((string)($__supportShiftHeaderActive['end_time'] ?? ''), 0, 5)) : 'Ready to start' ?>
                                </div>
                                <div class="sh-meta" id="ssHeaderMeta"
                                     data-started-ts="<?= (int)$__ssStartedTs ?>"
                                     data-server-now-ts="<?= (int)$__ssNowTs ?>">
                                    <?php if ($__ssVisible && !empty($__ssCoAdmins)): ?>
                                        <?= count($__ssCoAdmins) + 1 ?> admins on shift
                                    <?php else: ?>
                                        <?= $__ssVisible ? 'Loading…' : 'Open shift controls' ?>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <div class="sh-actions">
                            <button type="button" class="sh-btn primary" id="ssHeaderStartBtn" <?= $__ssVisible ? 'style="display:none;"' : '' ?>>Start</button>
                            <button type="button" class="sh-btn" id="ssHeaderPauseBtn" <?= !$__ssVisible ? 'style="display:none;"' : '' ?>>
                                <?= $__ssPaused ? 'Resume' : 'Pause' ?>
                            </button>
                            <button type="button" class="sh-btn danger" id="ssHeaderEndBtn" <?= !$__ssVisible ? 'style="display:none;"' : '' ?>>End</button>
                        </div>
                    </div>
                </li>
                <?php endif; ?>

                <li class="nav-item">
                    <div class="dropdown">
                        <a class="navbar-dropdown-account-wrapper" href="javascript:;"
                           id="accountNavbarDropdown" data-bs-toggle="dropdown"
                           aria-expanded="false" data-bs-auto-close="outside" data-bs-dropdown-animation>
                            <div class="avatar avatar-sm avatar-circle">
                                <img class="avatar-img" src="<?= ADMIN_DATA['icon'] ?>" alt="<?= ADMIN_DATA['username'] ?>">
                                <span class="avatar-status avatar-sm-status avatar-status-success"></span>
                            </div>
                        </a>
                        <div class="dropdown-menu dropdown-menu-end navbar-dropdown-menu navbar-dropdown-menu-borderless navbar-dropdown-account"
                             aria-labelledby="accountNavbarDropdown" style="width:17rem;">
                            <div class="dropdown-item-text">
                                <div class="d-flex align-items-center">
                                    <div class="avatar avatar-sm avatar-circle">
                                        <img class="avatar-img" src="<?= ADMIN_DATA['icon'] ?>" alt="<?= ADMIN_DATA['username'] ?>">
                                    </div>
                                    <div class="flex-grow-1 ms-3">
                                        <h5 class="mb-0"><?= ADMIN_DATA['username'] ?></h5>
                                        <p class="card-text text-body text-truncate"><?= ADMIN_DATA['email'] ?></p>
                                    </div>
                                </div>
                            </div>
                            <div class="dropdown-divider"></div>
                            <a><button class="dropdown-item" data-bs-toggle="modal" data-bs-target="#upload-icon-modal">
                                <i class="fad fa-camera nav-icon"></i> Change Picture
                            </button></a>
                            <div class="dropdown-divider"></div>
                            <a class="dropdown-item" href="<?= ADMN_URL ?>/auth/logout">
                                <i class="fa-duotone fa-sign-out-alt nav-icon"></i> Sign out
                            </a>
                        </div>
                    </div>
                </li>
            </ul>
        </div>
    </div>
</header>

<!-- Upload icon modal -->
<form class="ajax-form" action="<?= AJAX_URL ?>">
    <input type="hidden" name="action" value="admin_upload_profile_picture">
    <div id="upload-icon-modal" class="modal fade" tabindex="-1" role="dialog" aria-hidden="false">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header"><h5>Upload Icon</h5></div>
                <div class="modal-body">
                    <label for="image_url" class="js-file-attach form-label"
                           data-hs-file-attach-options='{"textTarget":"[for=\"customFile\"]"}'>
                        Upload your file
                    </label>
                    <input class="form-control" accept="image/*" type="file" name="image_url" id="image_url">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Submit</button>
                </div>
            </div>
        </div>
    </div>
</form>

<?php if ($__ssCanAccess): ?>

<!-- ── Modal: Working on a shift? ── -->
<div class="modal fade" id="ssLoginModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width:460px;">
        <div class="modal-content" style="position:relative;">
            <button type="button" class="ssl-close-btn" data-bs-dismiss="modal">
                <i class="fa-solid fa-xmark"></i>
            </button>
            <div class="ssl-header">
                <div class="ssl-badge">
                    <i class="fa-duotone fa-headset"></i> Support Shift
                </div>
                <h4 class="ssl-title">Working on a shift, or not?</h4>
                <p class="ssl-sub">Select the shift you are working on and start it, or continue without starting a shift.</p>
            </div>
            <div class="ssl-body">
                <div id="ssLoginShiftList">
                    <div class="ssl-no-shifts">
                        <i class="fa-duotone fa-circle-notch fa-spin me-2"></i>Loading available shifts…
                    </div>
                </div>
            </div>
            <div class="ssl-footer">
                <button type="button" class="ssl-no-shift" id="ssNotWorkingBtn">
                    Continue without shift
                </button>
                <a class="ssl-open-btn" href="<?= ADMN_URL ?>/support-shifts">
                    <i class="fa-duotone fa-calendar-clock me-2"></i>Open shift planner
                </a>
            </div>
        </div>
    </div>
</div>

<!-- ── Modal: Activity check ── -->
<div class="modal fade" id="ssCheckModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered" style="max-width:380px;">
        <div class="modal-content">
            <div class="ssc-body">
                <div class="ssc-emoji">👋</div>
                <div class="ssc-title">Still there?</div>
                <div class="ssc-sub">Stay active during your shift and respond to activity checks when they appear.</div>
                <div class="ssc-progress">
                    <div class="ssc-progress-fill" id="ssCheckProgress" style="width:100%;"></div>
                </div>
                <button type="button" class="ssc-confirm" id="ssCheckConfirmBtn">
                    <i class="fa-duotone fa-hand-wave"></i> I'm Here!
                </button>
            </div>
        </div>
    </div>
</div>


<!-- ── Modal: End shift ── -->
<div class="modal fade" id="ssEndShiftModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width:430px;">
        <div class="modal-content" style="position:relative;">
            <button type="button" class="sse-close" data-bs-dismiss="modal" aria-label="Close">
                <i class="fa-solid fa-xmark"></i>
            </button>
            <div class="sse-body">
                <div class="sse-top">
                    <div class="sse-icon"><i class="fa-duotone fa-power-off"></i></div>
                    <div>
                        <h4 class="sse-title">End Shift</h4>
                        <p class="sse-text">Are you sure you want to end your current shift? The timer will stop immediately, but the shift can still be resumed while the scheduled time window is active.</p>
                    </div>
                </div>
            </div>
            <div class="sse-footer">
                <button type="button" class="sse-btn" data-bs-dismiss="modal">Keep Working</button>
                <button type="button" class="sse-btn danger" id="ssEndShiftConfirmBtn">End Shift</button>
            </div>
        </div>
    </div>
</div>

<script>
(function(){
    const AJAX    = '<?= AJAX_URL ?>';
    const locale  = navigator.language || 'en-GB';
    // Pre-init from PHP so buttons work before async loadState() returns
    let activeShift = <?php if ($__ssVisible && !empty($__supportShiftHeaderActive)): ?><?= json_encode([
        'id'            => (int)($__supportShiftHeaderActive['id'] ?? 0),
        'title'         => (string)($__supportShiftHeaderActive['title'] ?? 'Support Shift'),
        'status'        => (string)($__supportShiftHeaderActive['status'] ?? 'active'),
        'start_time'    => (string)($__supportShiftHeaderActive['start_time'] ?? ''),
        'end_time'      => (string)($__supportShiftHeaderActive['end_time'] ?? ''),
        'started_at_ts' => $__ssStartedTs,
        'server_now_ts' => $__ssNowTs,
        'start_iso'     => lb_support_shift_header_iso($__supportShiftHeaderActive, 'start'),
        'end_iso'       => lb_support_shift_header_iso($__supportShiftHeaderActive, 'end'),
    ]) ?><?php else: ?>null<?php endif; ?>;
    let pendingCheck = null;
    let pendingEndShiftId = null;
    let timerIv      = null;
    let checkIv      = null;
    const ACTIVITY_SOUND_SRC = '<?= ASSET_URL ?>/core/dash/audio/wake_up.mp3';

    /*
     * Request safety for Hostinger WAF:
     * multiple admin tabs must not all fire support_shift_state / presence pings at once.
     * One visible tab becomes the temporary request master, and requests are throttled across all tabs.
     */
    const SS_TAB_ID = (function(){
        try {
            let id = sessionStorage.getItem('lb_ss_tab_id');
            if (!id) {
                id = Math.random().toString(36).slice(2) + Date.now().toString(36);
                sessionStorage.setItem('lb_ss_tab_id', id);
            }
            return id;
        } catch(e) {
            return Math.random().toString(36).slice(2) + Date.now().toString(36);
        }
    })();

    function ssCanSend(lockKey, lastKey, ttlMs, minGapMs) {
        if (document.visibilityState !== 'visible') return false;

        const now = Date.now();
        let last = 0;
        try { last = parseInt(localStorage.getItem(lastKey) || '0', 10) || 0; } catch(e) { last = 0; }
        if (last && now - last < minGapMs) return false;

        let lock = {};
        try { lock = JSON.parse(localStorage.getItem(lockKey) || '{}'); } catch(e) { lock = {}; }

        if (!lock.tabId || !lock.expires || lock.expires < now || lock.tabId === SS_TAB_ID) {
            try {
                localStorage.setItem(lockKey, JSON.stringify({ tabId: SS_TAB_ID, expires: now + ttlMs }));
                localStorage.setItem(lastKey, String(now));
            } catch(e) {}
            return true;
        }

        return false;
    }

    function playActivityCheckSound(){
        try {
            if (typeof window.lbTryPlaySound === 'function') {
                window.lbTryPlaySound('wake_up', 'activity_check_' + (pendingCheck && pendingCheck.id ? pendingCheck.id : Date.now()));
                return;
            }
            const audio = new Audio(ACTIVITY_SOUND_SRC);
            audio.preload = 'auto';
            audio.play().catch(function(){});
        } catch(e) {}
    }

    /* ── helpers ── */
    function post(action, data){
        const fd = new FormData();
        fd.append('action', action);
        Object.keys(data||{}).forEach(function(k){ fd.append(k, data[k]); });
        return fetch(AJAX,{method:'POST',body:fd,credentials:'same-origin'}).then(function(r){ return r.json(); });
    }
    function pad(n){ return String(n).padStart(2,'0'); }
    function fmtRange(si, ei){
        if (!si||!ei) return '';
        const s=new Date(si), e=new Date(ei);
        if(isNaN(s)||(isNaN(e))) return '';
        const o={hour:'2-digit',minute:'2-digit',hour12:false};
        return s.toLocaleTimeString(locale,o).replace(/^24:/,'00:')+' – '+e.toLocaleTimeString(locale,o).replace(/^24:/,'00:');
    }
    function shortDur(s){
        s=Math.max(0,parseInt(s||0,10));
        const h=Math.floor(s/3600),m=Math.floor((s%3600)/60);
        return h>0?h+'h '+m+'m':m+'m';
    }
    function shiftSlot(shift){
        const st=String(shift.start_time||'').slice(0,5);
        if(st==='06:00') return 'morning';
        if(st==='14:00') return 'afternoon';
        if(st==='22:00') return 'night';
        return '';
    }
    function slotIcon(slot){
        return {morning:'fa-sunrise',afternoon:'fa-sun',night:'fa-moon-stars'}[slot]||'fa-headset';
    }

    /* ── header chip DOM ── */
    const chip       = document.getElementById('ssHeaderChip');
    const statusText = document.getElementById('ssHeaderStatusText');
    const titleEl    = document.getElementById('ssHeaderTitle');
    const windowEl   = document.getElementById('ssHeaderWindow');
    const metaEl     = document.getElementById('ssHeaderMeta');
    const startBtn   = document.getElementById('ssHeaderStartBtn');
    const pauseBtn   = document.getElementById('ssHeaderPauseBtn');
    const endBtn     = document.getElementById('ssHeaderEndBtn');

    function setIdle(){
        if(chip)     chip.className='is-idle', chip.id='ssHeaderChip';
        if(statusText) statusText.textContent='OFF SHIFT';
        if(titleEl)  titleEl.textContent='No active shift';
        if(windowEl) windowEl.textContent='Ready to start';
        if(metaEl)   metaEl.textContent='Open shift controls';
        if(startBtn) startBtn.style.display='';
        if(pauseBtn) pauseBtn.style.display='none';
        if(endBtn)   endBtn.style.display='none';
        clearInterval(timerIv);
    }

    function startTimer(){
        if(!activeShift||!activeShift.id){ setIdle(); return; }
        const shift = activeShift;
        const isPaused = String(shift.status||'').toLowerCase()==='paused';
        const startedTs = parseInt(shift.started_at_ts||'0',10);
        const srvNow    = parseInt(shift.server_now_ts||shift.now_ts||'0',10) || Math.floor(Date.now()/1000);
        const browserLoad = Math.floor(Date.now()/1000);
        const endIso    = shift.end_iso||'';
        const endMs     = endIso ? new Date(endIso).getTime() : 0;

        if(chip)       chip.className = 'is-'+(isPaused?'paused':'active'), chip.id='ssHeaderChip';
        if(statusText) statusText.textContent = isPaused?'PAUSED':'ACTIVE';
        if(titleEl)    titleEl.textContent = shift.title||'Support Shift';
        if(windowEl){
            const r = fmtRange(shift.start_iso||'', shift.end_iso||'');
            if(r) windowEl.textContent = r;
        }
        if(startBtn) startBtn.style.display='none';
        if(pauseBtn){ pauseBtn.style.display=''; pauseBtn.textContent = isPaused?'Resume':'Pause'; }
        if(endBtn)   endBtn.style.display='';

        clearInterval(timerIv);
        function render(){
            if(!metaEl||!startedTs) return;
            const nowTs = srvNow+(Math.floor(Date.now()/1000)-browserLoad);
            const elapsed = Math.max(0, nowTs-startedTs);
            let rem='';
            if(endMs&&!isNaN(endMs)){
                rem=' · '+shortDur(Math.max(0,Math.floor(endMs/1000)-nowTs))+' left';
            }
            metaEl.textContent = shortDur(elapsed)+' elapsed'+rem;
        }
        render();
        timerIv = setInterval(render, 15000);
    }

    /* ── login modal ── */
    function renderLoginModal(shifts){
        const list = document.getElementById('ssLoginShiftList');
        if(!list) return;
        if(!shifts||!shifts.length){
            list.innerHTML='<div class="ssl-no-shifts">No shift is available right now.</div>';
            return;
        }
        list.innerHTML='';
        shifts.forEach(function(shift){
            const slot  = shiftSlot(shift);
            const icon  = slotIcon(slot);
            const range = fmtRange(shift.start_iso||'', shift.end_iso||'')
                       || (String(shift.start_time||'').slice(0,5)+' – '+String(shift.end_time||'').slice(0,5));
            const card = document.createElement('button');
            card.type='button';
            card.className='ssl-shift-card'+(slot?' '+slot:'');
            card.innerHTML=
                '<div class="ssc-icon"><i class="fa-duotone '+icon+'"></i></div>'
                +'<div class="ssc-info">'
                  +'<div class="ssc-title">'+(shift.title||'Support Shift')+'</div>'
                  +'<div class="ssc-time">'+range+' · your browser timezone</div>'
                +'</div>'
                +'<div class="ssc-start">'+(String(shift.status||'').toLowerCase()==='completed'?'Resume':'Start')+'</div>';
            card.addEventListener('click',function(){
                card.style.opacity='.5'; card.style.pointerEvents='none';
                post('support_shift_start',{shift_id:shift.id}).then(function(res){
                    if(res&&res.success) window.location.reload();
                    else { card.style.opacity=''; card.style.pointerEvents=''; alert((res&&res.message)||'Request failed.'); }
                }).catch(function(){ card.style.opacity=''; card.style.pointerEvents=''; alert('Request failed.'); });
            });
            list.appendChild(card);
        });
    }

    /* ── activity check modal ── */
    function showCheckModal(){
        if(!pendingCheck||!pendingCheck.id) return;
        const modalEl=document.getElementById('ssCheckModal');
        if(!modalEl||typeof bootstrap==='undefined') return;
        const modal=bootstrap.Modal.getOrCreateInstance(modalEl);
        modal.show();
        playActivityCheckSound();

        const fillEl  = document.getElementById('ssCheckProgress');
        const TOTAL   = 10*60;
        let   secsLeft = TOTAL;
        clearInterval(checkIv);
        function tick(){
            secsLeft--;
            if(fillEl) fillEl.style.width=Math.max(0,secsLeft/TOTAL*100)+'%';
            if(secsLeft<=0){ clearInterval(checkIv); modal.hide(); }
        }
        checkIv=setInterval(tick,1000);
    }

    /* ── state polling ── */
    function loadState(showLogin){
        if (!ssCanSend('lb_ss_state_master', 'lb_ss_state_last_at', 90000, 30000)) {
            return Promise.resolve(null);
        }

        return post('support_shift_state',{}).then(function(res){
            if(!res||!res.success) return null;
            activeShift  = res.active_shift||null;
            pendingCheck = res.pending_check||null;
            window.supportShiftAvailableShifts = res.available_shifts||[];
            startTimer();
            if(pendingCheck&&pendingCheck.id) showCheckModal();
            if(showLogin&&(!activeShift||!activeShift.id)&&sessionStorage.getItem('ssLoginDone')!=='1'){
                renderLoginModal(res.available_shifts||[]);
                const m=document.getElementById('ssLoginModal');
                if(m&&typeof bootstrap!=='undefined') bootstrap.Modal.getOrCreateInstance(m).show();
            }
            return res;
        }).catch(function(){ return null; });
    }

    /* ── button events ── */
    document.addEventListener('click',function(e){
        /* Start */
        if(e.target&&e.target.id==='ssHeaderStartBtn'){
            e.preventDefault();
            renderLoginModal(window.supportShiftAvailableShifts||[]);
            const m=document.getElementById('ssLoginModal');
            if(m&&typeof bootstrap!=='undefined') bootstrap.Modal.getOrCreateInstance(m).show();
        }
        /* Pause/Resume */
        if(e.target&&e.target.id==='ssHeaderPauseBtn'){
            e.preventDefault();
            let shiftId = (activeShift && activeShift.id) ? activeShift.id : null;
            if(!shiftId){ const c=document.getElementById('ssHeaderChip'); if(c&&c.dataset.shiftId) shiftId=parseInt(c.dataset.shiftId,10)||null; }
            if(!shiftId) return;
            const p = activeShift ? String(activeShift.status||'').toLowerCase()==='paused' : document.getElementById('ssHeaderChip')?.classList.contains('is-paused');
            post(p?'support_shift_resume':'support_shift_pause',{shift_id:shiftId})
                .then(function(res){
                    if(res&&res.success){ if(typeof window.create_toast==='function') window.create_toast('success','Done',res.message||'Done.'); setTimeout(function(){ window.location.reload(); },600); }
                    else { if(typeof window.create_toast==='function') window.create_toast('danger','Error',(res&&res.message)||'Request failed.'); else alert((res&&res.message)||'Request failed.'); }
                })
                .catch(function(){ alert('Request failed.'); });
        }
        /* End */
        if(e.target&&e.target.id==='ssHeaderEndBtn'){
            e.preventDefault();
            let shiftId = (activeShift && activeShift.id) ? activeShift.id : null;
            if(!shiftId){ const c=document.getElementById('ssHeaderChip'); if(c&&c.dataset.shiftId) shiftId=parseInt(c.dataset.shiftId,10)||null; }
            if(!shiftId) return;
            pendingEndShiftId = shiftId;
            const m=document.getElementById('ssEndShiftModal');
            if(m&&typeof bootstrap!=='undefined') bootstrap.Modal.getOrCreateInstance(m).show();
        }
        if(e.target&&e.target.id==='ssEndShiftConfirmBtn'){
            e.preventDefault();
            // Get shift_id: prefer pendingEndShiftId, fall back to activeShift, then chip attribute
            let id = pendingEndShiftId || (activeShift && activeShift.id) || null;
            if(!id){ const c=document.getElementById('ssHeaderChip'); if(c&&c.dataset.shiftId) id=parseInt(c.dataset.shiftId,10)||null; }
            if(!id){ alert('Could not determine shift ID. Please reload the page.'); return; }
            e.target.disabled = true;
            post('support_shift_end',{shift_id:id})
                .then(function(res){
                    e.target.disabled = false;
                    const m=document.getElementById('ssEndShiftModal');
                    if(m&&typeof bootstrap!=='undefined') bootstrap.Modal.getInstance(m)?.hide();
                    if(res&&res.success){
                        activeShift=null; pendingEndShiftId=null;
                        if(typeof window.create_toast==='function') window.create_toast('success','Shift ended','Your shift has been ended.');
                        setTimeout(function(){ window.location.reload(); }, 800);
                    } else {
                        if(typeof window.create_toast==='function') window.create_toast('danger','Error',(res&&res.message)||'Request failed.');
                        else alert((res&&res.message)||'Request failed.');
                    }
                })
                .catch(function(){
                    e.target.disabled = false;
                    if(typeof window.create_toast==='function') window.create_toast('danger','Error','Request failed.');
                    else alert('Request failed.');
                });
        }
        /* Not working */
        if(e.target&&e.target.id==='ssNotWorkingBtn'){
            post('support_shift_not_working',{}).finally(function(){
                sessionStorage.setItem('ssLoginDone','1');
                const m=document.getElementById('ssLoginModal');
                if(m&&typeof bootstrap!=='undefined') bootstrap.Modal.getInstance(m)?.hide();
            });
        }
        /* Confirm check */
        if(e.target&&e.target.id==='ssCheckConfirmBtn'&&pendingCheck&&pendingCheck.id){
            clearInterval(checkIv);
            post('support_shift_confirm_check',{check_id:pendingCheck.id}).then(function(res){
                if(res&&res.success){
                    pendingCheck=null;
                    const m=document.getElementById('ssCheckModal');
                    if(m&&typeof bootstrap!=='undefined') bootstrap.Modal.getInstance(m)?.hide();
                } else alert((res&&res.message)||'Request failed.');
            }).catch(function(){ alert('Request failed.'); });
        }
    });

    /* ── init ── */
    document.addEventListener('DOMContentLoaded',function(){
        startTimer();
        // Keep the shift picker available through the header Start button, but
        // do not open it automatically on every page load or in every new tab.
        loadState(false);
        setInterval(function(){ loadState(false); }, 90000);

        function supportShiftPresencePing(){
            if (!ssCanSend('lb_ss_presence_master', 'lb_ss_presence_last_at', 90000, 60000)) return;
            try {
                const fd = new FormData();
                fd.append('action', 'support_shift_presence_ping');
                fetch(<?= json_encode(AJAX_URL) ?>, { method: 'POST', body: fd, credentials: 'same-origin' }).catch(function(){});
            } catch(e) {}
        }
        supportShiftPresencePing();
        setInterval(supportShiftPresencePing, 60000);

        let visibleRefreshTimer = null;
        document.addEventListener('visibilitychange', function(){
            if (document.visibilityState !== 'visible') return;
            clearTimeout(visibleRefreshTimer);
            visibleRefreshTimer = setTimeout(function(){
                loadState(false);
                supportShiftPresencePing();
            }, 2500);
        });

        window.addEventListener('focus', function(){
            clearTimeout(visibleRefreshTimer);
            visibleRefreshTimer = setTimeout(function(){
                loadState(false);
                supportShiftPresencePing();
            }, 3500);
        });

        /* Wire up window ISO data from PHP for initial render */
        const win = document.getElementById('ssHeaderWindow');
        if(win&&win.dataset.startIso&&win.dataset.endIso){
            const r=fmtRange(win.dataset.startIso,win.dataset.endIso);
            if(r) win.textContent=r;
        }
    });
})();
</script>
<?php endif; ?>
