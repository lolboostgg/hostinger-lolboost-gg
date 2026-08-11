<?php
/**
 * pages/shift-sessions.php — Shift Session History
 * Route: admin-area/shifts/sessions
 */
?>
<?= $this->layout('admin/layouts/main', ['meta' => $meta]) ?>

<?= $this->section('styles') ?>
<style>
.session-active { background: #f0fff8 !important; }
.dur-chip { font-family:'Courier New',monospace; font-size:.78rem; background:#f9fafc; border:1px solid #e7eaf3; border-radius:4px; padding:2px 8px; }
</style>
<?= $this->end() ?>

<main id="content" role="main" class="main">
<div class="content container-fluid">

    <div class="page-header mb-4">
        <div class="row align-items-center">
            <div class="col">
                <h1 class="page-header-title">
                    <i class="fa-duotone fa-list-timeline me-2 text-primary"></i>Shift Sessions
                </h1>
                <p class="text-muted mb-0">History of all admin shift sessions and activity check results.</p>
            </div>
            <div class="col-auto">
                <a href="<?= ADMN_URL ?>/shifts" class="btn btn-outline-primary">
                    <i class="fa-duotone fa-calendar-week me-1"></i> Schedule
                </a>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Admin</th>
                        <th>Started</th>
                        <th>Ended</th>
                        <th>Duration</th>
                        <th>Activity Checks</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (empty($sessions)): ?>
                <tr><td colspan="7" class="text-center text-muted py-5">No shift sessions yet.</td></tr>
                <?php else: ?>
                <?php foreach ($sessions as $s):
                    $mins      = max(0, (int)$s['duration_mins']);
                    $h         = floor($mins / 60);
                    $m         = $mins % 60;
                    $durStr    = ($h > 0 ? $h . 'h ' : '') . $m . 'min';
                    $total     = (int)$s['total_checks'];
                    $missed    = (int)$s['missed_checks'];
                    $confirmed = $total - $missed;
                ?>
                <tr class="<?= $s['status'] === 'active' ? 'session-active' : '' ?>">
                    <td><small class="text-muted"><?= $s['id'] ?></small></td>
                    <td>
                        <div class="d-flex align-items-center gap-2">
                            <img src="<?= htmlspecialchars($s['avatar']) ?>"
                                 style="width:28px;height:28px;border-radius:50%;object-fit:cover;" alt="">
                            <span><?= htmlspecialchars($s['username']) ?></span>
                        </div>
                    </td>
                    <td>
                        <div><?= date('d M Y', strtotime($s['started_at'])) ?></div>
                        <small class="text-muted"><?= date('H:i', strtotime($s['started_at'])) ?></small>
                    </td>
                    <td>
                        <?php if ($s['ended_at']): ?>
                        <div><?= date('d M Y', strtotime($s['ended_at'])) ?></div>
                        <small class="text-muted"><?= date('H:i', strtotime($s['ended_at'])) ?></small>
                        <?php else: ?>
                        <span class="text-muted">—</span>
                        <?php endif; ?>
                    </td>
                    <td><span class="dur-chip"><?= $durStr ?></span></td>
                    <td>
                        <?php if ($total > 0): ?>
                        <span class="badge bg-soft-success text-success me-1"><?= $confirmed ?> ✓</span>
                        <?php if ($missed > 0): ?>
                        <span class="badge bg-soft-danger text-danger"><?= $missed ?> missed</span>
                        <?php endif; ?>
                        <?php else: ?>
                        <span class="text-muted" style="font-size:.8rem;">None yet</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if ($s['status'] === 'active'): ?>
                        <span class="badge bg-soft-success text-success">
                            <i class="fa-solid fa-circle fa-xs me-1"></i>Active
                        </span>
                        <?php elseif ($s['status'] === 'ended'): ?>
                        <span class="badge bg-soft-secondary text-secondary">Ended</span>
                        <?php else: ?>
                        <span class="badge bg-soft-warning text-warning">Abandoned</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

</div>
</main>
