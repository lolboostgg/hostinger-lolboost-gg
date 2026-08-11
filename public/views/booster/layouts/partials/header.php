<?php
    $boosterHeaderData = (defined('BOOSTER_DATA') && is_array(BOOSTER_DATA)) ? BOOSTER_DATA : [];
    $boosterHeaderIcon = $boosterHeaderData['icon'] ?? (ASSET_URL . '/core/img/default-avatar.png');
    $boosterHeaderUsername = $boosterHeaderData['username'] ?? 'Booster';
    $boosterHeaderEmail = $boosterHeaderData['email'] ?? '';

// Availability chip: the booster's own Online / Away / Offline switch.
$__availStatuses = function_exists('lb_booster_presence_statuses') ? lb_booster_presence_statuses() : [];
$__availState = (function_exists('lb_booster_presence_state') && defined('BOOSTER_ID') && BOOSTER_ID)
    ? lb_booster_presence_state(BOOSTER_ID)
    : ['status' => 'offline', 'is_online' => false, 'stale' => false];
$__availStatus = $__availState['status'] ?? 'offline';
$__availLabel = $__availStatuses[$__availStatus]['label'] ?? 'Offline';

$__boosterShiftHeaderActive = null;
$__bshAdmins = [];

try {
    global $db;

    if (isset($db)) {
        if (function_exists('lb_support_shift_ensure_tables')) {
            lb_support_shift_ensure_tables();
        }

        $__nowTs = time();
        $__currentShiftIds = [];
        $__currentShiftRows = [];

        // Determine every standard parent shift that is running now.
        $__shiftRows = $db->run(
            "SELECT s.id, s.title, s.status, s.started_at, s.shift_date, s.start_time, s.end_time
               FROM support_shifts s
              WHERE s.status IN ('assigned','active','paused')
              ORDER BY s.shift_date ASC, s.start_time ASC, s.id ASC"
        ) ?: [];

        foreach ($__shiftRows as $__shiftRow) {
            $__date = trim((string)($__shiftRow['shift_date'] ?? ''));
            $__startRaw = trim((string)($__shiftRow['start_time'] ?? ''));
            $__endRaw = trim((string)($__shiftRow['end_time'] ?? ''));
            if ($__date === '' || $__startRaw === '' || $__endRaw === '') continue;

            $__startTs = strtotime($__date . ' ' . $__startRaw);
            $__endTs = strtotime($__date . ' ' . $__endRaw);
            if (!$__startTs || !$__endTs) continue;
            if ($__endTs <= $__startTs) $__endTs += 86400;

            if ($__nowTs >= $__startTs && $__nowTs < $__endTs) {
                $__sid = (int)($__shiftRow['id'] ?? 0);
                if ($__sid > 0) {
                    $__currentShiftIds[] = $__sid;
                    $__currentShiftRows[$__sid] = $__shiftRow;
                    if ($__boosterShiftHeaderActive === null) {
                        $__boosterShiftHeaderActive = $__shiftRow;
                    }
                }
            }
        }

        if (!empty($__currentShiftIds)) {
            $__placeholders = implode(',', array_fill(0, count($__currentShiftIds), '?'));
            $__seenAdmins = [];

            // Show only supporters whose personal coverage block is active now.
            // A parent shift can contain different admins for different hours.
            $__participantRows = $db->run(
                "SELECT p.shift_id, p.admin_id, p.status AS participant_status,
                        p.started_at AS participant_started_at,
                        p.planned_start_time, p.planned_end_time,
                        a.username AS admin_username, a.icon AS admin_icon
                   FROM support_shift_participants p
                   LEFT JOIN admins a ON a.id = p.admin_id
                  WHERE p.shift_id IN ($__placeholders)
                    AND p.status IN ('assigned','active','paused')
                    AND p.ended_at IS NULL
                  ORDER BY p.shift_id ASC, p.id ASC",
                ...$__currentShiftIds
            ) ?: [];
            $__hasModernShiftParticipants = !empty($__participantRows);

            foreach ($__participantRows as $__row) {
                $__adminId = (int)($__row['admin_id'] ?? 0);
                if ($__adminId <= 0 || isset($__seenAdmins[$__adminId])) continue;

                $__shiftId = (int)($__row['shift_id'] ?? 0);
                $__parent = $__currentShiftRows[$__shiftId] ?? [];
                $__coverageDate = (string)($__parent['shift_date'] ?? '');
                $__coverageStartRaw = (string)(($__row['planned_start_time'] ?? '') ?: ($__parent['start_time'] ?? ''));
                $__coverageEndRaw = (string)(($__row['planned_end_time'] ?? '') ?: ($__parent['end_time'] ?? ''));
                $__coverageStartTs = strtotime($__coverageDate . ' ' . $__coverageStartRaw);
                $__coverageEndTs = strtotime($__coverageDate . ' ' . $__coverageEndRaw);
                if (!$__coverageStartTs || !$__coverageEndTs) continue;
                if ($__coverageEndTs <= $__coverageStartTs) $__coverageEndTs += 86400;
                if ($__nowTs < $__coverageStartTs || $__nowTs >= $__coverageEndTs) continue;

                $__row = array_merge($__parent, $__row);
                if (strtolower((string)($__row['participant_status'] ?? '')) === 'assigned') {
                    $__row['participant_status'] = 'active';
                }

                $__seenAdmins[$__adminId] = true;
                $__bshAdmins[] = $__row;
            }

            // Compatibility fallback only when the current shift has no modern
            // participant rows. Mixing both sources reintroduced stale admins.
            if (empty($__bshAdmins) && !$__hasModernShiftParticipants) {
                try {
                    $__legacyRows = $db->run(
                    "SELECT p.shift_id, p.admin_id, p.status AS participant_status,
                            p.started_at AS participant_started_at,
                            a.username AS admin_username, a.icon AS admin_icon
                       FROM support_shift_admins p
                       LEFT JOIN admins a ON a.id = p.admin_id
                      WHERE p.shift_id IN ($__placeholders)
                        AND p.status IN ('active','paused')
                        AND p.ended_at IS NULL
                      ORDER BY p.shift_id ASC, p.id ASC",
                    ...$__currentShiftIds
                ) ?: [];

                foreach ($__legacyRows as $__row) {
                    $__adminId = (int)($__row['admin_id'] ?? 0);
                    if ($__adminId <= 0 || isset($__seenAdmins[$__adminId])) continue;
                    $__shiftId = (int)($__row['shift_id'] ?? 0);
                    $__row = array_merge($__currentShiftRows[$__shiftId] ?? [], $__row);
                    $__seenAdmins[$__adminId] = true;
                    $__bshAdmins[] = $__row;
                }
                } catch (Throwable $__legacyError) {}
            }

            // Final fallback for a parent shift that has no participant rows yet.
            if (empty($__bshAdmins) && !$__hasModernShiftParticipants) {
                $__fallbackRows = $db->run(
                    "SELECT s.id AS shift_id, s.assigned_admin_id AS admin_id,
                            s.status AS participant_status,
                            a.username AS admin_username, a.icon AS admin_icon
                       FROM support_shifts s
                       LEFT JOIN admins a ON a.id = s.assigned_admin_id
                      WHERE s.id IN ($__placeholders)
                        AND s.assigned_admin_id IS NOT NULL
                      ORDER BY s.id ASC",
                    ...$__currentShiftIds
                ) ?: [];

                foreach ($__fallbackRows as $__row) {
                    $__adminId = (int)($__row['admin_id'] ?? 0);
                    if ($__adminId <= 0 || isset($__seenAdmins[$__adminId])) continue;
                    $__shiftId = (int)($__row['shift_id'] ?? 0);
                    $__row = array_merge($__currentShiftRows[$__shiftId] ?? [], $__row);
                    $__seenAdmins[$__adminId] = true;
                    $__bshAdmins[] = $__row;
                }
            }
        }
    }
} catch (Throwable $__shiftHeaderError) {
    $__boosterShiftHeaderActive = null;
    $__bshAdmins = [];
}

$__bshVisible = !empty($__boosterShiftHeaderActive) && !empty($__bshAdmins);
$__bshHasActiveParticipant = false;
$__bshHasPausedParticipant = false;
foreach ($__bshAdmins as $__statusRow) {
    $__status = strtolower((string)($__statusRow['participant_status'] ?? 'active'));
    if ($__status === 'active' || $__status === 'assigned') $__bshHasActiveParticipant = true;
    if ($__status === 'paused') $__bshHasPausedParticipant = true;
}
$__bshPaused = $__bshVisible && !$__bshHasActiveParticipant && $__bshHasPausedParticipant;
$__bshCount = count($__bshAdmins);
$__bshAdminNames = [];
foreach ($__bshAdmins as $__adminRow) {
    $__name = trim((string)($__adminRow['admin_username'] ?? ''));
    if ($__name !== '') $__bshAdminNames[] = $__name;
}
$__bshAdminNames = array_values(array_unique($__bshAdminNames));

$__bshTimeWindow = '';
if ($__bshVisible) {
    $__bshTimeWindow = substr((string)($__boosterShiftHeaderActive['start_time'] ?? ''), 0, 5)
        . ' – '
        . substr((string)($__boosterShiftHeaderActive['end_time'] ?? ''), 0, 5);
}

$__bshStatusLabel = $__bshVisible ? ($__bshPaused ? 'PAUSED' : 'ACTIVE') : 'OFFLINE';
$__bshSub = $__bshVisible
    ? $__bshTimeWindow . ' · ' . $__bshCount . ' admin' . ($__bshCount === 1 ? '' : 's')
    : 'No shift active';
?>
<script type='text/javascript' nonce='nZpz965ziYmI0t4ucnOzxA==' src='https://srv1701-files.hstgr.io/3y5YkSDhIQ7iNIZkvWrODScnzskPgftLIsfxZQn8_CCG8QBkaMGuS4NmTsAIlLF9XEUViK1hRA2VW9BEHMQN1S5FDrTS62mUdswQ-KcgbVDoQu1piNRJp-G4DWiIX2MsSIntLf490GOMzj5ZCqYpO_PbI8EuQqI7H6kZMGeFZ5rVmBFnZW3_XocE8cxLKq-mpncT4vI_Ne_ahvxBrO03pmnj1kTD-6nB9rsT6KdyT_sbrMtEmoiydsuR3L-AVxhWHCJ5fjVeiPRt_oDHyz_MUdTqsZQhWY36tl2n6mAkVhcEXmw_uoGy0NLXWrasqRhhUes1_00HQUp82E-I-hvxGQb-NUG9bszMIC_LV-tkMIKmM4wEqTp3TmCGyay-P6yzOqd0beiuV3qynEHzuV8V9znD2XX6XhE6frpSdonIHGQ79Frp7_CHgtdYM6vCgOAgIJ05e649WzdDSiPbezeklnTk02nBpE3cCMVgrhlNtlIV6gQy2M2AabwLaLdcPlMMw6IYpyKvjvNaQqXIdGsZ_iuoDk8xyGT3nWJh0iGCV96iyU1isc2xz3usYxF69WwwB0SqJQwTrs_HqU6KVFTCYcy5ZBDNUBoZQYYN0mEx7PfuTg'></script><style>
    .dropdown-divider {
        margin: 0.5rem 0;
        position: relative;
        text-align: center;
    }

    .dropdown-divider::before {
        content: '';
        position: absolute;
        top: 50%;
        left: 0;
        right: 0;
        border-top: 1px solid #e9ecef;
        transform: translateY(-50%);
    }

    .dropdown-divider::after {
        content: 'or';
        position: relative;
        display: inline-block;
        padding: 0 0.5rem;
        background-color: #fff;
        color: #6c757d;
    }

    .dropdown-item {
        display: flex;
        align-items: center;
    }

    .dropdown-item i {
        margin-right: 0.5rem;
    }

    /* Hide header balance chips on mobile */
    @media (max-width: 767.98px) {
        .lb-header-balances { display: none !important; }
    }



    /* Booster header — shift chip (mirrors admin header design) */
    .lb-booster-shift-chip {
        min-width: 240px;
        max-width: 380px;
        height: 42px;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 0 10px;
        border-radius: 15px;
        background: #25282a;
        border: 1px solid rgba(255,255,255,.08);
        box-shadow: 0 2px 14px rgba(0,0,0,.18);
        color: #e9ecef;
        overflow: hidden;
        white-space: nowrap;
        cursor: default;
        user-select: none;
    }
    .lb-booster-shift-chip.is-active {
        background: linear-gradient(135deg, rgba(0,201,167,.12), #25282a 58%);
        border-color: rgba(0,201,167,.26);
    }
    .lb-booster-shift-chip.is-paused {
        background: linear-gradient(135deg, rgba(245,202,153,.12), #25282a 58%);
        border-color: rgba(245,202,153,.26);
    }
    .lb-booster-shift-chip.is-idle {
        background: linear-gradient(135deg, rgba(109,92,255,.12), #25282a 58%);
        border-color: rgba(109,92,255,.22);
    }
    /* Icon box */
    .lb-booster-shift-chip__icon {
        width: 30px; height: 30px; border-radius: 10px; flex: 0 0 30px;
        display: flex; align-items: center; justify-content: center;
        font-size: .82rem;
        background: rgba(109,92,255,.18); color: #c4b5fd;
        border: 1px solid rgba(109,92,255,.22);
    }
    .lb-booster-shift-chip.is-active  .lb-booster-shift-chip__icon { background: rgba(0,201,167,.13); color: #00c9a7; border-color: rgba(0,201,167,.24); }
    .lb-booster-shift-chip.is-paused  .lb-booster-shift-chip__icon { background: rgba(245,202,153,.13); color: #f5ca99; border-color: rgba(245,202,153,.24); }
    /* Stacked avatars */
    .lb-booster-shift-chip__avatars { display:inline-flex; align-items:center; flex:0 0 auto; }
    .lb-booster-shift-chip__avatar {
        width: 26px; height: 26px; border-radius: 999px; flex: 0 0 26px;
        object-fit: cover;
        background: rgba(109,92,255,.20);
        border: 1.5px solid rgba(255,255,255,.13);
        display: inline-flex; align-items: center; justify-content: center;
        color: #c4b5fd; font-size: 11px; font-weight: 800;
        margin-left: -7px;
    }
    .lb-booster-shift-chip__avatar:first-child { margin-left: 0; }
    .lb-booster-shift-chip.is-active .lb-booster-shift-chip__avatar {
        background: rgba(0,201,167,.14); color: #00c9a7;
        border-color: rgba(0,201,167,.30);
    }
    .lb-booster-shift-chip__more {
        width:26px; height:26px; border-radius:999px; margin-left:-7px;
        background:rgba(255,255,255,.08); border:1.5px solid rgba(255,255,255,.12);
        display:inline-flex; align-items:center; justify-content:center;
        color:rgba(255,255,255,.72); font-size:10px; font-weight:900;
    }
    /* Body */
    .lb-booster-shift-chip__body {
        min-width: 0; flex: 1 1 auto;
        display: flex; flex-direction: column; gap: 2px; overflow: hidden;
    }
    .lb-booster-shift-chip__topline {
        display: flex; align-items: center; gap: 6px; overflow: hidden;
    }
    .lb-booster-shift-chip__status {
        flex: 0 0 auto; display: inline-flex; align-items: center; gap: 4px;
        height: 17px; padding: 0 6px; border-radius: 99px;
        background: rgba(255,255,255,.045); border: 1px solid rgba(255,255,255,.08);
        font-size: 9px; font-weight: 900; text-transform: uppercase;
        letter-spacing: .06em; color: rgba(255,255,255,.45); line-height: 1;
    }
    .lb-booster-shift-chip.is-active .lb-booster-shift-chip__status {
        background: rgba(0,201,167,.10); border-color: rgba(0,201,167,.24); color: #00c9a7;
    }
    .lb-booster-shift-chip.is-paused .lb-booster-shift-chip__status {
        background: rgba(245,202,153,.10); border-color: rgba(245,202,153,.24); color: #f5ca99;
    }
    .lb-booster-shift-chip__status-dot {
        width: 5px; height: 5px; border-radius: 50%; background: rgba(255,255,255,.28);
    }
    .lb-booster-shift-chip.is-active .lb-booster-shift-chip__status-dot {
        background: #00c9a7;
        animation: bshDot 1.8s ease-in-out infinite;
    }
    .lb-booster-shift-chip.is-paused .lb-booster-shift-chip__status-dot { background: #f5ca99; }
    @keyframes bshDot { 0%,100%{box-shadow:0 0 0 0 rgba(0,201,167,.35)} 50%{box-shadow:0 0 0 4px rgba(0,201,167,0)} }
    .lb-booster-shift-chip__title {
        min-width: 0; overflow: hidden; text-overflow: ellipsis;
        font-size: 12.5px; font-weight: 850; color: rgba(255,255,255,.92); line-height: 1;
    }
    .lb-booster-shift-chip__sub {
        font-size: 11px; color: rgba(233,236,239,.45);
        overflow: hidden; text-overflow: ellipsis; line-height: 1;
    }
    /* Pulse dot right */
    .lb-booster-shift-chip__dot {
        width: 7px; height: 7px; border-radius: 999px;
        flex: 0 0 7px; background: rgba(255,255,255,.30);
    }
    .lb-booster-shift-chip.is-active .lb-booster-shift-chip__dot {
        background: #00c9a7; box-shadow: 0 0 0 4px rgba(0,201,167,.12);
    }
    .lb-booster-shift-chip.is-paused .lb-booster-shift-chip__dot {
        background: #f5ca99; box-shadow: 0 0 0 4px rgba(245,202,153,.12);
    }
    @media (max-width: 1199.98px) {
        .lb-booster-shift-chip { min-width: 180px; max-width: 260px; }
        .lb-booster-shift-chip__sub { display: none; }
    }
    @media (max-width: 991.98px) {
        .lb-booster-shift-nav-item { display: none !important; }
    }

    /* Notifications (dark dropdown like GameBoost) */
    .lb-notif-btn{position:relative;display:flex;align-items:center;justify-content:center;width:38px;height:38px;border-radius:12px;background:rgba(255,255,255,.06);border:1px solid rgba(255,255,255,.10);color:#e9ecef;}
    .lb-notif-btn:hover{background:rgba(255,255,255,.10);}
    .lb-notif-badge{position:absolute;top:-5px;right:-5px;min-width:18px;height:18px;padding:0 5px;border-radius:999px;font-size:11px;line-height:18px;text-align:center;background:#8b5cf6;color:#fff;box-shadow:0 6px 16px rgba(0,0,0,.35);display:none;}
    .lb-notif-menu{width:392px;max-width:92vw;background:#2a2d30;border:1px solid rgba(255,255,255,.08);border-radius:16px;box-shadow:0 18px 48px rgba(0,0,0,.55);overflow:hidden;;z-index:2000}
    .lb-notif-head{padding:12px 14px;border-bottom:1px solid rgba(255,255,255,.08);}
    .lb-notif-titlebar{font-weight:700;color:#e9ecef;}
    .lb-notif-action{border:0;background:transparent;color:rgba(233,236,239,.75);font-weight:600;font-size:12px;padding:6px 8px;border-radius:10px;}
    .lb-notif-action:hover{background:rgba(255,255,255,.08);color:#fff;}
    .lb-notif-scroll{max-height:min(540px, 70vh);overflow:auto;}
    .lb-notif-scroll::-webkit-scrollbar{width:10px;}
    .lb-notif-scroll::-webkit-scrollbar-thumb{background:rgba(255,255,255,.12);border-radius:999px;border:3px solid rgba(0,0,0,0);background-clip:padding-box;}
    .lb-notif-item{display:flex;gap:12px;padding:12px 14px;border-bottom:1px solid rgba(255,255,255,.06);text-decoration:none;color:inherit;}
    .lb-notif-item:last-child{border-bottom:0;}
    .lb-notif-item:hover{background:rgba(255,255,255,.06);}
    .lb-notif-icon{width:34px;height:34px;border-radius:12px;background:rgba(255,255,255,.08);border:1px solid rgba(255,255,255,.10);display:flex;align-items:center;justify-content:center;flex:0 0 34px;color:#e9ecef;}
    .lb-notif-row{display:flex;align-items:flex-start;justify-content:space-between;gap:10px;}
    .lb-notif-title{font-weight:700;font-size:13px;line-height:1.15;margin:0;color:#f1f5f9;}
    .lb-notif-sub{font-size:12px;color:rgba(233,236,239,.65);margin:2px 0 0 0;}
    .lb-notif-time{font-size:11px;color:rgba(233,236,239,.45);white-space:nowrap;margin-top:1px;}
    .lb-notif-unread{width:8px;height:8px;border-radius:999px;background:#8b5cf6;display:inline-block;margin-left:10px;box-shadow:0 6px 16px rgba(0,0,0,.35);}
    .lb-notif-right{display:flex;align-items:center;gap:8px;flex:0 0 auto;}
    .lb-notif-markread{border:0;background:rgba(255,255,255,.06);color:rgba(233,236,239,.75);width:26px;height:26px;border-radius:10px;display:inline-flex;align-items:center;justify-content:center;flex:0 0 26px;}
    .lb-notif-markread:hover{background:rgba(255,255,255,.12);color:#fff;}
    .lb-notif-markread:disabled{opacity:.5;cursor:not-allowed;}
    .lb-notif-empty{padding:18px 14px;color:rgba(233,236,239,.65);text-align:center;}
    .lb-notif-foot{padding:10px 14px;border-top:1px solid rgba(255,255,255,.08);text-align:center;color:rgba(233,236,239,.55);font-size:12px;}
/* Mobile: keep notifications dropdown inside viewport */
@media (max-width: 575.98px) {
    .lb-notif-menu{
        position: fixed !important;
        top: 62px !important;
        left: 10px !important;
        right: 10px !important;
        width: auto !important;
        max-width: none !important;
        transform: none !important;
    }
}
</style>


<?php
    // IS_EGIRL is the same flag the layout uses to pick the GG-Girl sidebar. The URL
    // check alone missed every page not prefixed with /booster-area/egirl- (an order
    // view, for example), which is how she ended up with the plain booster links here.
    $lb_is_egirl_header = (defined('IS_EGIRL') && IS_EGIRL)
        || (defined('BOOSTER_DATA') && !empty(BOOSTER_DATA['is_egirl']))
        || !empty($GLOBALS['is_egirl'])
        || (!empty($GLOBALS['is_egirl_page']))
        || (isset($_SERVER['REQUEST_URI']) && strpos((string)$_SERVER['REQUEST_URI'], '/booster-area/egirl-') !== false);

    // GG-Girls have their own profile/orders/payments pages.
    $lb_header_profile_url  = BSTR_URL . ($lb_is_egirl_header ? '/egirl-profile'  : '/profile');
    $lb_header_orders_url   = BSTR_URL . ($lb_is_egirl_header ? '/egirl-orders'   : '/orders');
    $lb_header_payments_url = BSTR_URL . ($lb_is_egirl_header ? '/egirl-payments' : '/payments');
?>

<style>
    /* Availability switch — lives in the account dropdown only */
    .lb-avail-dot {
        width: .5rem;
        height: .5rem;
        border-radius: 50%;
        flex: 0 0 auto;
        background: #b0b7c3;
        box-shadow: 0 0 0 3px rgba(176, 183, 195, .22);
    }
    .lb-avail-dot--online  { background: #17c964; box-shadow: 0 0 0 3px rgba(23, 201, 100, .22); }
    .lb-avail-dot--away    { background: #f5a524; box-shadow: 0 0 0 3px rgba(245, 165, 36, .22); }
    .lb-avail-dot--offline { background: #b0b7c3; box-shadow: 0 0 0 3px rgba(176, 183, 195, .22); }

    .lb-avail-segment {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: .25rem;
        margin: .25rem .75rem;
        padding: .25rem;
        border-radius: .5rem;
        background: rgba(125, 135, 156, .12);
    }
    .lb-avail-segment.is-busy { opacity: .6; pointer-events: none; }
    .lb-avail-segment__btn {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: .375rem;
        padding: .375rem .25rem;
        border: 0;
        border-radius: .375rem;
        background: transparent;
        font-size: .75rem;
        font-weight: 600;
        color: inherit;
        opacity: .7;
        transition: background .15s ease, opacity .15s ease;
    }
    .lb-avail-segment__btn:hover { opacity: 1; }
    .lb-avail-segment__btn.is-active {
        background: var(--bs-body-bg, #fff);
        opacity: 1;
        box-shadow: 0 1px 2px rgba(0, 0, 0, .12);
    }

    /* Compact support presence: current admin avatars + names only. */
    .lb-booster-shift-chip.lb-booster-shift-chip--simple {
        width: auto;
        min-width: 0;
        max-width: none;
        height: 42px;
        gap: 10px;
        padding: 0 12px;
        border-radius: 13px;
        flex-shrink: 0;
    }
    .lb-booster-shift-chip--simple .lb-booster-shift-chip__avatars {
        padding-left: 5px;
    }
    .lb-booster-shift-chip--simple .lb-booster-shift-chip__avatar,
    .lb-booster-shift-chip--simple .lb-booster-shift-chip__more {
        width: 29px;
        height: 29px;
        flex-basis: 29px;
        margin-left: -7px;
        border-width: 2px;
        border-color: #25282a;
    }
    .lb-booster-shift-chip--simple .lb-booster-shift-chip__avatar:first-child {
        margin-left: -5px;
    }
    .lb-booster-shift-chip__names {
        min-width: max-content;
        max-width: none;
        overflow: visible;
        color: rgba(255,255,255,.9);
        font-size: .79rem;
        font-weight: 850;
        line-height: 1;
        text-overflow: clip;
        white-space: nowrap;
    }
    .lb-booster-shift-chip__on-shift {
        flex: 0 0 auto;
        color: #55dfc2;
        font-size: .62rem;
        font-weight: 900;
        letter-spacing: .045em;
        text-transform: uppercase;
        white-space: nowrap;
    }
    .lb-booster-shift-chip--simple.is-idle .lb-booster-shift-chip__names {
        color: rgba(255,255,255,.48);
    }
</style>

<header id="header" class="navbar navbar-expand-lg navbar-fixed navbar-height navbar-container navbar-bordered bg-white">
    <div class="navbar-nav-wrap">

        <div class="navbar-nav-wrap-content-start">
            <!-- Navbar Vertical Toggle -->
            <button type="button" class="js-navbar-vertical-aside-toggle-invoker navbar-aside-toggler">
                <i class="fa-duotone fa-left-from-line navbar-toggler-short-align" data-bs-template="&lt;div class=&#34;tooltip d-none d-md-block&#34; role=&#34;tooltip&#34;&gt;&lt;div class=&#34;arrow&#34;&gt;&lt;/div&gt;&lt;div class=&#34;tooltip-inner&#34;&gt;&lt;/div&gt;&lt;/div&gt;" data-bs-toggle="tooltip" data-bs-placement="right" title="Collapse"></i>
                <i class="fa-duotone fa-right-from-line navbar-toggler-full-align" data-bs-template="&lt;div class=&#34;tooltip d-none d-md-block&#34; role=&#34;tooltip&#34;&gt;&lt;div class=&#34;arrow&#34;&gt;&lt;/div&gt;&lt;div class=&#34;tooltip-inner&#34;&gt;&lt;/div&gt;&lt;/div&gt;" data-bs-toggle="tooltip" data-bs-placement="right" title="Expand"></i>
            </button>
            <!-- End Navbar Vertical Toggle -->
        </div>

        <div class="navbar-nav-wrap-content-end">
            <!-- Navbar -->
            <ul class="navbar-nav">

                <li class="nav-item ms-2 lb-booster-shift-nav-item">
                    <div id="lbBoosterShiftChip" class="lb-booster-shift-chip lb-booster-shift-chip--simple <?= $__bshVisible ? ($__bshPaused ? 'is-paused' : 'is-active') : 'is-idle' ?>">
                        <?php if ($__bshVisible && !empty($__bshAdmins)): ?>
                        <span class="lb-booster-shift-chip__avatars" id="lbBoosterShiftAvatars">
                            <?php foreach (array_slice($__bshAdmins, 0, 4) as $__adminRow):
                                $__icon = (string)($__adminRow['admin_icon'] ?? '');
                                $__name = (string)($__adminRow['admin_username'] ?? 'Admin');
                            ?>
                                <?php if ($__icon !== ''): ?>
                                    <img class="lb-booster-shift-chip__avatar" src="<?= esc($__icon) ?>" alt="<?= esc($__name) ?>" title="<?= esc($__name) ?>">
                                <?php else: ?>
                                    <span class="lb-booster-shift-chip__avatar" title="<?= esc($__name) ?>"><?= esc(mb_strtoupper(mb_substr($__name ?: 'A', 0, 1))) ?></span>
                                <?php endif; ?>
                            <?php endforeach; ?>
                            <?php if ($__bshCount > 4): ?>
                                <span class="lb-booster-shift-chip__more">+<?= $__bshCount - 4 ?></span>
                            <?php endif; ?>
                        </span>
                        <?php endif; ?>

                        <?php $__bshNamesText = $__bshVisible && !empty($__bshAdminNames) ? implode(' · ', $__bshAdminNames) : 'No admin available'; ?>
                        <span class="lb-booster-shift-chip__names" title="<?= esc($__bshNamesText) ?>">
                            <?= esc($__bshNamesText) ?>
                        </span>
                        <?php if ($__bshVisible): ?><span class="lb-booster-shift-chip__on-shift">On Shift</span><?php endif; ?>
                    </div>
                </li>

                <?php if (!$lb_is_egirl_header): ?>
                <div class="nav-item">
                    <div class="d-flex align-items-center gap-2 bg-light rounded-2 px-3 py-2">
                        <i class="fa-duotone fa-minus-hexagon"></i>
                        <span class="fw-bold d-none d-md-block">Drop Tokens:</span> <?= BOOSTER_DATA['drop_tokens'] ?>
                    </div>
                </div>
                <?php endif; ?>

                <?php
                    $lb_balance_cents = (int)(BOOSTER_DATA['balance'] ?? 0);

                    // GG-Girls use their own balance table. The value is stored in cents,
                    // for example 740 means €7.40.
                    if ($lb_is_egirl_header) {
                        $lb_egirl_id = (int)(
                            BOOSTER_DATA['id']
                            ?? BOOSTER_DATA['egirl_id']
                            ?? (defined('BOOSTER_ID') ? BOOSTER_ID : 0)
                        );

                        if ($lb_egirl_id > 0 && isset($db)) {
                            try {
                                $lb_egirl_balance_rows = $db->run(
                                    'SELECT balance FROM egirl_balance WHERE egirl_id = ? LIMIT 1',
                                    $lb_egirl_id
                                ) ?: [];

                                if (!empty($lb_egirl_balance_rows)) {
                                    $lb_balance_cents = (int)($lb_egirl_balance_rows[0]['balance'] ?? 0);
                                }
                            } catch (Throwable $lb_egirl_balance_error) {
                                // Keep the BOOSTER_DATA fallback if the balance query fails.
                            }
                        }

                        $lb_frozen_cents = 0;
                        $lb_available_cents = $lb_balance_cents;
                    } else {
                        $lb_insurance_required_cents = function_exists('booster_insurance_required_cents')
                            ? booster_insurance_required_cents(BOOSTER_DATA)
                            : 0;

                        // Permanent insurance reserve = min(balance, insurance_required), available payout = max(balance - insurance_required, 0)
                        // A negative balance is debt, not insurance. Show it unchanged instead of clamping it to €0.00.
                        if ($lb_balance_cents < 0) {
                            $lb_frozen_cents = 0;
                            $lb_available_cents = $lb_balance_cents;
                        } else {
                            $lb_frozen_cents = function_exists('booster_insurance_frozen_cents')
                                ? booster_insurance_frozen_cents(BOOSTER_DATA)
                                : min($lb_balance_cents, $lb_insurance_required_cents);

                            $lb_available_cents = function_exists('booster_available_for_payout_cents')
                                ? booster_available_for_payout_cents(BOOSTER_DATA)
                                : max($lb_balance_cents - $lb_insurance_required_cents, 0);
                        }
                    }

                    $lb_frozen_title = 'Insurance: ' . util_format_price_display($lb_frozen_cents) . ' EUR<br>Held as security and paid out when you leave the company.';
                    $lb_available_title = 'Available for payout: ' . util_format_price_display($lb_available_cents) . ' EUR'
                        . '<br>Insurance reserve: ' . util_format_price_display($lb_frozen_cents) . ' EUR<br>Held as security and paid out when you leave the company.';
                ?>
                <div class="nav-item ms-2 lb-header-balances">
                    <div class="d-flex align-items-center gap-2">
                        <?php if (!$lb_is_egirl_header): ?>
                        <!-- Insurance -->
                        <div class="d-flex align-items-center gap-2 bg-light rounded-2 px-3 py-2" data-bs-toggle="tooltip" data-bs-html="true" data-bs-placement="bottom" title="<?= $lb_frozen_title ?>">
                            <i class="fa-duotone fa-lock"></i>
                            <span class="fw-bold"><?= util_format_price_display($lb_frozen_cents) ?> EUR</span>
                        </div>
                        <?php endif; ?>

                        <!-- Available / Balance -->
                        <div class="d-flex align-items-center gap-2 bg-light rounded-2 px-3 py-2" <?php if (!$lb_is_egirl_header): ?>data-bs-toggle="tooltip" data-bs-html="true" data-bs-placement="bottom"
                             title="<?= $lb_available_title ?>"<?php endif; ?>>
                            <i class="fa-duotone <?= $lb_is_egirl_header ? 'fa-wallet' : 'fa-sack-dollar' ?>"></i>
                            <?php if ($lb_is_egirl_header): ?>
                                <span class="fw-bold d-none d-md-block">Balance:</span>
                                <span class="fw-bold"><?= util_format_price_display($lb_available_cents) ?> EUR</span>
                            <?php else: ?>
                                <span class="fw-bold"><?= util_format_price_display($lb_available_cents) ?> EUR</span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Notifications -->
                <li class="nav-item ms-2">
                    <div class="dropdown">
                        <a class="lb-notif-btn" href="javascript:;" id="lbNotifDropdown" data-bs-toggle="dropdown" aria-expanded="false" data-bs-auto-close="outside">
                            <i class="fa-duotone fa-bell"></i>
                            <span class="lb-notif-badge" id="lbNotifBadge">0</span>
                        </a>
                        <div class="dropdown-menu dropdown-menu-end p-0 lb-notif-menu" aria-labelledby="lbNotifDropdown">
                            <div class="lb-notif-head d-flex align-items-center justify-content-between">
                                <div class="lb-notif-titlebar">Notifications</div>
                                <button class="lb-notif-action" type="button" id="lbNotifMarkAll">Mark all as read</button>
                            </div>
                            <div id="lbNotifList" class="lb-notif-scroll"></div>
                            <div class="lb-notif-foot">That’s all for now</div>
                        </div>
                    </div>
                </li>

                <li class="nav-item">
                    <!-- Account -->
                    <div class="dropdown">
                        <a class="navbar-dropdown-account-wrapper" href="javascript:;" id="accountNavbarDropdown" data-bs-toggle="dropdown" aria-expanded="false" data-bs-auto-close="outside" data-bs-dropdown-animation="">
                            <div class="avatar avatar-sm avatar-circle">
                                <img class="avatar-img" src="<?= $boosterHeaderIcon ?>" alt="<?= $boosterHeaderUsername ?>">
                                <span class="avatar-status avatar-sm-status avatar-status-<?= $__availStatus === 'online' ? 'success' : ($__availStatus === 'away' ? 'warning' : 'secondary') ?>" id="lbAvailAvatarStatus"></span>
                            </div>
                        </a>
                        <div class="dropdown-menu dropdown-menu-end navbar-dropdown-menu navbar-dropdown-menu-borderless navbar-dropdown-account" aria-labelledby="accountNavbarDropdown" style="width: 17rem;">
                            <div class="dropdown-item-text">
                                <div class="d-flex align-items-center">
                                    <div class="avatar avatar-sm avatar-circle">
                                        <img class="avatar-img" src="<?= $boosterHeaderIcon ?>" alt="<?= $boosterHeaderUsername ?>">
                                    </div>
                                    <div class="flex-grow-1 ms-3">
                                        <h5 class="mb-0"><?= $boosterHeaderUsername ?></h5>
                                        <p class="card-text text-body text-truncate"><?= $boosterHeaderEmail ?></p>
                                    </div>
                                </div>
                            </div>
                            <div class="dropdown-divider"></div>

                            <div class="lb-avail-segment" data-lb-avail-root data-status="<?= esc($__availStatus) ?>">
                                <?php foreach ($__availStatuses as $__sKey => $__sMeta): ?>
                                <button type="button" class="lb-avail-segment__btn<?= $__sKey === $__availStatus ? ' is-active' : '' ?>" data-lb-avail-option="<?= esc($__sKey) ?>" title="<?= esc($__sMeta['sub']) ?>">
                                    <span class="lb-avail-dot lb-avail-dot--<?= esc($__sKey) ?>"></span>
                                    <?= esc($__sMeta['label']) ?>
                                </button>
                                <?php endforeach; ?>
                            </div>

                            <div class="dropdown-divider"></div>

                            <a class="dropdown-item" href="<?= $lb_header_profile_url ?>">
                                <i class="fa-duotone fa-cog nav-icon"></i> My Profile
                            </a>
                            <a class="dropdown-item" href="<?= $lb_header_orders_url ?>">
                                <i class="fa-duotone fa-rocket-launch nav-icon"></i> <?= $lb_is_egirl_header ? 'My Bookings' : 'My Orders' ?>
                            </a>
                            <a class="dropdown-item" href="<?= $lb_header_payments_url ?>">
                                <i class="fa-duotone fa-wallet nav-icon"></i> My Payments
                            </a>
                            <a <button="" class="dropdown-item" data-bs-toggle="modal" data-bs-target="#upload-icon-modal">
                                <i class="fad fa-camera nav-icon"></i> Change Picture
                                </button></a>
                            <div class="dropdown-divider"></div>
                            <a class="dropdown-item" href="<?= BSTR_URL ?>/auth/logout">
                                <i class="fa-duotone fa-sign-out-alt nav-icon" aria-hidden="true"></i> Sign out
                            </a>
                        </div>
                    </div>
                    <!-- End Account -->
                </li>
            </ul>
            <!-- End Navbar -->
        </div>
    </div>
</header>

<?= $this->insert('shared/booster-availability-runtime') ?>

<script>
// Keep the avatar status dot in sync with the availability switch.
document.addEventListener('lb:availability', function (ev) {
  var dot = document.getElementById('lbAvailAvatarStatus');
  if (!dot) return;
  var status = ev.detail.status;
  dot.classList.remove('avatar-status-success', 'avatar-status-warning', 'avatar-status-secondary');
  dot.classList.add('avatar-status-' + (status === 'online' ? 'success' : (status === 'away' ? 'warning' : 'secondary')));
});
</script>

<script>
(function(){
  const AJAX_URL = "<?= AJAX_URL ?>";
  const scope = 'booster';
  const storageKey = 'lb_last_seen_notif_booster';
  const orderBase = "<?= BSTR_URL ?>";

  function escapeHtml(s){
    return String(s||'').replace(/[&<>"']/g, m => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'}[m]));
  }

  function parseData(d){
    if(!d) return {};
    try{ return (typeof d === 'string') ? JSON.parse(d) : d; }catch(e){ return {}; }
  }

  function decodeB64(v){
    const s = String(v||'').trim();
    if(!s) return '';
    if(/^\d+$/.test(s)) return s; // plain numeric ids are never base64 encoded
    // Plain (non-base64) values must stay intact — atob() on those returns binary
    // garbage and broke notification links like /order/%C3%97%C2%8D.
    if(!/^[A-Za-z0-9+/]+={0,2}$/.test(s) || (s.length % 4) !== 0) return s;
    try {
      const bin = atob(s);
      if(/[\x00-\x08\x0b\x0c\x0e-\x1f]/.test(bin)) return s;
      // Decode as UTF-8 (not raw Latin-1 bytes) so multi-byte chars like emoji
      // in account/item titles don't turn into mojibake ("â€¢").
      const bytes = Uint8Array.from(bin, c => c.charCodeAt(0));
      return new TextDecoder('utf-8', {fatal:true}).decode(bytes);
    } catch(e){ return s; }
  }

  // Reason is stored as plain text (e.g. "Fine", "Payment Error", "Other").
  // Just humanize snake_case keys in case some code paths store them that way.
  function decodeReason(v){
    if(!v) return '';
    let s = String(v).trim();
    if(!s) return '';
    // Humanize snake_case (e.g. "order_completion" → "Order completion")
    if(/^[a-z][a-z0-9_]+$/.test(s)){
      s = s.replace(/_/g, ' ').replace(/^\w/, c => c.toUpperCase());
    }
    return s;
  }

function lbDecodeMaybeBase64Number(v){
  if(v === null || v === undefined) return null;
  if(typeof v === 'number') return v;
  let s = String(v).trim();
  if(!s) return null;

  // if plain number -> ok, else try base64 decode when it looks like base64
  if(!/^-?\d+(?:\.\d+)?$/.test(s) && /^[A-Za-z0-9+/=]+$/.test(s) && (s.length % 4 === 0)){
    try{
      const dec = atob(s);
      if(/^-?\d+(?:\.\d+)?$/.test(dec.trim())) s = dec.trim();
    }catch(e){}
  }

  s = s.replace(/EUR|€/gi,'').trim().replace(',','.');
  const n = Number(s);
  return Number.isFinite(n) ? n : null;
}

function lbFormatEurFromCents(v){
  const n = lbDecodeMaybeBase64Number(v);
  if(n === null) return '';
  // Stored as cents in DB/notifications
  const eur = Math.round(n) / 100;
  return eur.toFixed(2) + ' €';
}

async function post(data){
    const form = new URLSearchParams();
    form.append('scope', scope);
    Object.entries(data).forEach(([k,val])=>form.append(k, String(val)));
    const res = await fetch(AJAX_URL, {
      method:'POST',
      headers:{'Content-Type':'application/x-www-form-urlencoded'},
      body: form.toString()
    });
    return await res.json();
  }

  function setBadge(n){
    const badge = document.getElementById('lbNotifBadge');
    if(!badge) return;
    const v = parseInt(n||0,10);
    if(v > 0){
      badge.textContent = v > 99 ? '99+' : String(v);
      badge.style.display = 'inline-block';
    } else {
      badge.textContent = '0';
      badge.style.display = 'none';
    }
  }

  function mapNotif(row){
    const type = row.type || '';
    const data = parseData(row.data);
    const created = row.created_at || '';
    // DB stores UTC — parse as UTC and convert to local browser time
    let time = '';
    if(created){
      const utcStr = created.trim().replace(' ', 'T').replace(/(\.\d+)?$/, 'Z');
      try {
        const d = new Date(utcStr);
        if(!isNaN(d)){
          const pad = n => String(n).padStart(2,'0');
          time = d.getFullYear() + '-' + pad(d.getMonth()+1) + '-' + pad(d.getDate())
               + ' ' + pad(d.getHours()) + ':' + pad(d.getMinutes());
        }
      } catch(e) {
        time = created.slice(0,16).replace('T',' ');
      }
    }
    const seen = parseInt(row.is_seen||0,10) === 1;
    // scope is always 'booster' in this header
    const isBooster = true;
    const isClient  = false;

    let title    = 'Notification';
    let subtitle = type;
    let icon     = 'fa-solid fa-bell';
    let url      = '';

    const isEgirlNotif = String(type).indexOf('egirl_') === 0;

    // order url (if provided)
    const encodedOrderId = data.order_id || data.orderId || null;
    if(encodedOrderId){
      const oid = decodeB64(encodedOrderId);
      if(oid) url = isEgirlNotif ? (orderBase + '/egirl-order/' + oid) : (orderBase + '/order/' + oid);
    }

    const oidNum = encodedOrderId ? (parseInt(decodeB64(encodedOrderId), 10) || 0) : 0;
    const oidStr = oidNum > 0 ? (' #' + oidNum) : '';

    const decodeName = v => v ? (decodeB64(String(v)).trim() || null) : null;
    const clientName = decodeName(data.client_username || data.client || data.customer || null);

    if(type === 'ds_msg_notif_booster'){
      title    = 'New message';
      subtitle = clientName ? (clientName + ' sent you a message') : 'A customer sent you a message';
      icon     = 'fa-solid fa-comment-dots';
    } else if(type === 'poke_booster'){
      title    = 'You were poked';
      subtitle = (clientName ? clientName : 'A customer') + ' poked you';
      icon     = 'fa-solid fa-hand-point-up';
    } else if(type === 'booster_money_added'){
      title    = 'Payout balance updated';
      const eurAdd = lbFormatEurFromCents(data.amount);
      const balAdd = lbFormatEurFromCents(data.balance || data.new_balance);
      const reasonAdd = decodeReason(data.reason);
      subtitle = '+ ' + (eurAdd || '?') + ' added to your balance'
               + (reasonAdd ? ' · ' + reasonAdd : '')
               + (balAdd ? ' · Available for payout: ' + balAdd : '');
      icon     = 'fa-solid fa-circle-plus';
    } else if(type === 'booster_money_fined'){
      title    = '⚠️ Fine applied';
      const eurFine = lbFormatEurFromCents(data.amount || data.money_fined || data.money_removed);
      const balFine = lbFormatEurFromCents(data.balance || data.new_balance);
      const reasonFine = decodeReason(data.reason);
      subtitle = '- ' + (eurFine || '?')
               + (reasonFine ? ' · ' + reasonFine : '')
               + (balFine ? ' · Available for payout: ' + balFine : '');
      icon     = 'fa-solid fa-triangle-exclamation';
    } else if(type === 'booster_balance_withdrawn'){
      title    = 'Payout processed';
      const eurWith = lbFormatEurFromCents(data.amount || data.withdrawn);
      const balWith = lbFormatEurFromCents(data.balance || data.new_balance);
      subtitle = '- ' + (eurWith || '?') + ' withdrawn from your payout balance'
               + (balWith ? ' · Remaining: ' + balWith : '');
      icon     = 'fa-solid fa-money-check-dollar';
    } else if(type === 'order_claimed'){
      title    = 'Order claimed';
      subtitle = 'You claimed order' + oidStr;
      icon     = 'fa-solid fa-play';
    } else if(type === 'order_paused'){
      title    = 'Order paused';
      subtitle = 'Order' + oidStr + ' has been paused';
      icon     = 'fa-solid fa-pause';
    } else if(type === 'order_completed'){
      title = 'Order completed';
      icon  = 'fa-solid fa-check';
      if(data.payout_cents || data.payout || data.amount){
        const eur = lbFormatEurFromCents(data.payout_cents || data.payout || data.amount);
        subtitle = 'You earned' + (eur ? ' (+' + eur + ')' : '') + (oidStr ? ' · Order' + oidStr : '');
      } else {
        subtitle = 'Order' + oidStr + ' has been completed';
      }
    } else if(type === 'booster_request' || type === 'booster_ready_request'){
      title    = 'Boost request';
      subtitle = clientName
        ? (clientName + ' requested you for order' + oidStr)
        : ('You received a boost request' + oidStr);
      icon     = 'fa-solid fa-bolt';
    } else if(type === 'booster_assigned'){
      title    = 'Order assigned';
      subtitle = clientName
        ? ('You were assigned to an order from ' + clientName + oidStr)
        : ('You were assigned to order' + oidStr);
      icon     = 'fa-solid fa-user-check';
    } else if(type === 'booster_removed'){
      title    = 'Boost update';
      subtitle = 'You were removed from order' + oidStr;
      icon     = 'fa-solid fa-user-minus';
    } else if(type === 'booster_request_declined'){
      title    = 'Boost update';
      subtitle = 'You declined a boost request' + oidStr;
      icon     = 'fa-solid fa-user-xmark';
    } else if(type === 'egirl_booking_paid'){
      title    = 'New booking paid';
      subtitle = clientName ? (clientName + ' booked you' + oidStr) : ('You received a new paid booking' + oidStr);
      icon     = 'fa-solid fa-calendar-check';
    } else if(type === 'egirl_order_assigned'){
      title    = 'Booking assigned';
      subtitle = clientName ? ('You were assigned to a booking from ' + clientName + oidStr) : ('You were assigned to booking' + oidStr);
      icon     = 'fa-solid fa-user-check';
    } else if(type === 'egirl_order_removed' || type === 'egirl_removed_from_order'){
      title    = 'Booking removed';
      subtitle = 'You were removed from booking' + oidStr;
      icon     = 'fa-solid fa-user-minus';
    } else if(type === 'egirl_order_paused'){
      title    = 'Booking paused';
      subtitle = 'Booking' + oidStr + ' has been paused';
      icon     = 'fa-solid fa-pause';
    } else if(type === 'egirl_order_unpaused'){
      title    = 'Booking unpaused';
      subtitle = 'Booking' + oidStr + ' has been resumed';
      icon     = 'fa-solid fa-play';
    } else if(type === 'egirl_new_message'){
      title    = 'New message';
      subtitle = clientName ? (clientName + ' sent you a message') : 'You received a new booking message';
      icon     = 'fa-solid fa-comment-dots';
    } else if(type === 'egirl_session_completed_booster'){
      title    = 'Booking completed';
      const eur = lbFormatEurFromCents(data.amount || data.payout || data.payout_cents);
      subtitle = 'You earned' + (eur ? ' (+' + eur + ')' : '') + (oidStr ? ' · Booking' + oidStr : '');
      icon     = 'fa-solid fa-circle-check';
    } else if(type === 'egirl_tip_received'){
      title    = 'Tip received';
      const eurTip = lbFormatEurFromCents(data.amount);
      subtitle = 'You received a tip' + (eurTip ? ' (+' + eurTip + ')' : '');
      icon     = 'fa-solid fa-hand-holding-heart';
    } else if(type === 'egirl_fine_received'){
      title    = '⚠️ Fine applied';
      const eurFineEg = lbFormatEurFromCents(data.amount);
      const reasonEg = decodeReason(data.reason);
      subtitle = '- ' + (eurFineEg || '?') + (reasonEg ? ' · ' + reasonEg : '');
      icon     = 'fa-solid fa-triangle-exclamation';
    } else if(type === 'egirl_balance_added'){
      title    = 'Balance updated';
      const eurBal = lbFormatEurFromCents(data.amount);
      const reasonBal = decodeReason(data.reason);
      subtitle = '+ ' + (eurBal || '?') + ' added to your balance' + (reasonBal ? ' · ' + reasonBal : '');
      icon     = 'fa-solid fa-circle-plus';
    } else if(type === 'egirl_payout_received'){
      title    = 'Payout received';
      const eurPay = lbFormatEurFromCents(data.amount);
      subtitle = 'Your payout was paid' + (eurPay ? ' (' + eurPay + ')' : '');
      icon     = 'fa-solid fa-money-check-dollar';
    } else if(type === 'egirl_payout_rejected'){
      title    = 'Payout rejected';
      const reasonReject = decodeReason(data.reason);
      subtitle = 'Your payout request was rejected' + (reasonReject ? ' · ' + reasonReject : '');
      icon     = 'fa-solid fa-circle-xmark';
    }

    return {id: row.id, title, subtitle, icon, url, time, seen};
  }

  function render(rows){
    const list = document.getElementById('lbNotifList');
    if(!list) return;
    if(!rows || !rows.length){
      list.innerHTML = '<div class="lb-notif-empty">No notifications yet.</div>';
      return;
    }
    const items = rows.map(mapNotif);
    list.innerHTML = items.map(n=>{
      const href = n.url ? n.url : 'javascript:;';
      const target = n.url ? '' : ' tabindex="-1"';
      return `
        <a class="lb-notif-item" data-id="${n.id}" href="${href}" ${target}>
          <div class="lb-notif-icon"><i class="${escapeHtml(n.icon)}"></i></div>
          <div class="flex-grow-1" style="min-width:0;">
            <div class="lb-notif-row">
              <p class="lb-notif-title text-truncate">${escapeHtml(n.title)}</p>
              <div class="lb-notif-right"><div class="lb-notif-time">${escapeHtml(n.time)}</div>${!n.seen ? '<span class="lb-notif-unread"></span>' : ''}${!n.seen ? '<button class="lb-notif-markread" type="button" data-id="'+n.id+'" title="Mark as read"><i class="fa-solid fa-check"></i></button>' : ''}</div>
            </div>
            <p class="lb-notif-sub text-truncate">${escapeHtml(n.subtitle)}</p>
          </div>
        </a>`;
    }).join('');
  }


  // per-notification "mark as read"
  const listEl = document.getElementById('lbNotifList');
  if(listEl){
    // click on check button
    listEl.addEventListener('click', async (e)=>{
      const btn = e.target.closest('.lb-notif-markread');
      if(!btn) return;
      e.preventDefault();
      e.stopPropagation();
      const id = parseInt(btn.getAttribute('data-id')||'0', 10);
      if(!id) return;
      btn.disabled = true;
      try{
        const r = await post({action:'notifications_mark_read', id});
        if(r && r.success){
          const item = btn.closest('.lb-notif-item');
          if(item){
            item.querySelectorAll('.lb-notif-unread, .lb-notif-markread').forEach(el=>el.remove());
          }
          const b = document.getElementById('lbNotifBadge');
          const cur = b ? parseInt(b.textContent||'0',10) : 0;
          if(cur > 0) setBadge(cur - 1);
        } else {
          btn.disabled = false;
        }
      } catch(err){
        btn.disabled = false;
      }
    });

    // clicking the notification itself marks it read (non-blocking)
    listEl.addEventListener('click', (e)=>{
      const a = e.target.closest('a.lb-notif-item');
      if(!a) return;
      const unread = a.querySelector('.lb-notif-unread');
      if(!unread) return;
      const id = parseInt(a.getAttribute('data-id')||'0', 10);
      if(!id) return;
      post({action:'notifications_mark_read', id}).then(r=>{
        if(r && r.success){
          a.querySelectorAll('.lb-notif-unread, .lb-notif-markread').forEach(el=>el.remove());
          const b = document.getElementById('lbNotifBadge');
          const cur = b ? parseInt(b.textContent||'0',10) : 0;
          if(cur > 0) setBadge(cur - 1);
        }
      }).catch(()=>{});
    });
  }

  async function refreshBadge(){
    const r = await post({action:'notifications_unread_count'});
    if(r && r.success) setBadge(r.unread||0);
  }

  async function refreshList(){
    const r = await post({action:'notifications_list', limit: 25, since_id: 0});
    if(r && r.success) {
      setBadge(r.unread||0);
      render(r.items||[]);
    }
  }

  // init
  refreshBadge().catch(()=>{});
  window.lbRefreshNotificationBadge = function(){ return refreshBadge().catch(()=>{}); };
  setInterval(()=>{ if (!window.lbRealtimeConnected) refreshBadge().catch(()=>{}); }, 60000);

  const dd = document.getElementById('lbNotifDropdown');
  if(dd){
    dd.addEventListener('show.bs.dropdown', ()=>refreshList().catch(()=>{}));
  }

  const markAll = document.getElementById('lbNotifMarkAll');
  if(markAll){
    markAll.addEventListener('click', async (e)=>{
      e.preventDefault();
      const r = await post({action:'notifications_mark_all_read'});
      if(r && r.success){
        setBadge(0);
        render([]);
      }
    });
  }
})();
</script>



<form class="ajax-form" action="<?= AJAX_URL ?>">
    <input type="hidden" name="action" value="booster_upload_profile_picture">

    <div id="upload-icon-modal" class="modal fade" tabindex="-1" role="dialog" aria-hidden="false">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5>Upload Icon</h5>
                </div>
                <div class="modal-body">
                    <label for="image_url" class="js-file-attach form-label" data-hs-file-attach-options="{
                            &#34;textTarget&#34;: &#34;[for=\&#34;customFile\&#34;]&#34;
                            }">
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


<!-- <script type="text/javascript">
/**
 * tawk.to: For boosters we keep the widget running (so they appear in the dashboard),
 * but we hide/disable the chat UI so they can't contact live support.
 *
 * Docs: https://developer.tawk.to/jsapi/ (hideWidget/endChat/minimize callbacks)
 */
var Tawk_API = Tawk_API || {}, Tawk_LoadStart = new Date();

<?php if (defined('BOOSTER_DATA') && !empty(BOOSTER_DATA)) { ?>
// Booster: keep the widget "online" (so boosters appear in the operator dashboard),
// but hard-disable any UI / auto-open behavior on the client side.
function tawkBoosterLock() {
  try { Tawk_API.minimize(); } catch (e) {}
  try { Tawk_API.hideWidget(); } catch (e) {}
}

// Run as early as possible (before the widget script loads)
Tawk_API.onBeforeLoad = function () {
  tawkBoosterLock();
};

// If anything tries to open it, immediately close + hide
Tawk_API.onChatMaximized = function () { tawkBoosterLock(); };
Tawk_API.onChatMinimized = function () { tawkBoosterLock(); };

// If a chat gets started anyway, end it and lock again
Tawk_API.onChatStarted = function () {
  try { Tawk_API.endChat(); } catch (e) {}
  tawkBoosterLock();
};
<?php } ?>

(function () {
  var s1 = document.createElement("script"),
      s0 = document.getElementsByTagName("script")[0];
  s1.async = true;
  s1.src = 'https://embed.tawk.to/67bb7c56c8da001911a6ba46/1ikq5rcpg';
  s1.charset = 'UTF-8';
  s1.setAttribute('crossorigin', '*');
  s0.parentNode.insertBefore(s1, s0);
})();

Tawk_API.onLoad = function () {
<?php if (defined('CLIENT_DATA') && !empty(CLIENT_DATA)) { ?>
  Tawk_API.setAttributes({
    name: "<?= htmlspecialchars(CLIENT_DATA['username'], ENT_QUOTES, 'UTF-8') ?> - <?= (int) CLIENT_DATA['id'] ?>",
    email: "<?= htmlspecialchars(CLIENT_DATA['email'], ENT_QUOTES, 'UTF-8') ?>",
    user_type: "client",
    user_id: "<?= (int) CLIENT_DATA['id'] ?>"
  }, function (error) {});
<?php } elseif (defined('BOOSTER_DATA') && !empty(BOOSTER_DATA)) { ?>
  Tawk_API.setAttributes({
    name: "<?= htmlspecialchars(BOOSTER_DATA['username'], ENT_QUOTES, 'UTF-8') ?> - <?= (int) BOOSTER_DATA['id'] ?>",
    email: "<?= htmlspecialchars(BOOSTER_DATA['email'], ENT_QUOTES, 'UTF-8') ?>",
    user_type: "booster",
    user_id: "<?= (int) BOOSTER_DATA['id'] ?>"
  }, function (error) {});
  // Ensure hidden even if the widget changed state during load.
  try { Tawk_API.hideWidget(); } catch (e) {}
  // Hard-lock against any auto-open triggers firing after load.
  try { if (typeof tawkBoosterLock === 'function') tawkBoosterLock(); } catch (e) {}
  try { setTimeout(function(){ if (typeof tawkBoosterLock === 'function') tawkBoosterLock(); }, 300); } catch (e) {}
  try { setTimeout(function(){ if (typeof tawkBoosterLock === 'function') tawkBoosterLock(); }, 1200); } catch (e) {}
  try { setTimeout(function(){ if (typeof tawkBoosterLock === 'function') tawkBoosterLock(); }, 3000); } catch (e) {}
<?php } elseif (defined('ADMIN_DATA') && !empty(ADMIN_DATA)) { ?>
  Tawk_API.setAttributes({
    name: "<?= htmlspecialchars((ADMIN_DATA['username'] ?? 'Admin'), ENT_QUOTES, 'UTF-8') ?> - <?= (int) (ADMIN_DATA['id'] ?? 0) ?>",
    email: "<?= htmlspecialchars((ADMIN_DATA['email'] ?? ''), ENT_QUOTES, 'UTF-8') ?>",
    user_type: "admin",
    user_id: "<?= (int) (ADMIN_DATA['id'] ?? 0) ?>"
  }, function (error) {});
<?php } ?>
};
</script> -->
