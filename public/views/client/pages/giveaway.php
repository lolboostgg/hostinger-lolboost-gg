<?= $this->layout('client/layouts/main', ['meta' => $meta]) ?>

<div class="row">
    <div class="col-12 col-xl-4">
        <div class="card mb-3">
            <div class="card-body">
                <h5 class="card-title">Your Tickets</h5>
                <?php if (!empty($giveaway)) : ?>
                    <div class="text-muted small mb-2">Current Giveaway</div>
                    <div class="fw-semibold fs-1"><?= (int)($my_tickets ?? 0) ?></div>
                    <div class="text-muted">More tickets = higher chance to win.</div>
                    <hr>
                    <div class="d-flex justify-content-between">
                        <span class="text-muted">Ends</span>
                        <span class="fw-semibold"><?= htmlspecialchars($giveaway['ends_at'] ?? '-') ?></span>
                    </div>
                <?php else : ?>
                    <div class="text-muted">There is no active giveaway right now.</div>
                    <div class="mt-2">Check back soon.</div>
                <?php endif; ?>

                <div class="mt-3">
                    <a class="btn btn-outline-primary w-100" href="<?= BASE_URL ?>/giveaway" target="_blank">
                        View Public Giveaway Page
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="col-12 col-xl-8">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="card-title mb-0">Leaderboard</h5>
                    <div class="text-muted small">Top 20</div>
                </div>

                <?php if (!empty($giveaway)) : ?>
                    <div class="table-responsive">
                        <table class="table align-middle">
                            <thead>
                                <tr>
                                    <th style="width:60px;">#</th>
                                    <th>Player</th>
                                    <th class="text-end" style="width:120px;">Tickets</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $rows = $leaderboard ?? [];
                                $top = array_slice($rows, 0, 20);
                                ?>
                                <?php if (!empty($top)) : ?>
                                    <?php foreach ($top as $i => $r) : ?>
                                        <tr>
                                            <td><?= $i + 1 ?></td>
                                            <td>
                                                <div class="d-flex align-items-center gap-2">
                                                    <img src="<?= htmlspecialchars($r['icon'] ?? '') ?>" style="width:32px;height:32px;border-radius:50%;object-fit:cover;" alt="">
                                                    <div class="fw-semibold">
                                                        <?= htmlspecialchars(function_exists('giveaway_mask_username') ? giveaway_mask_username($r['username'] ?? '') : ($r['username'] ?? '')) ?>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="text-end fw-semibold"><?= (int)($r['tickets'] ?? 0) ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else : ?>
                                    <tr><td colspan="3" class="text-muted">No participants yet.</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>

                    <?php if (!empty($leaderboard) && count($leaderboard) > 20) : ?>
                        <div class="text-muted small">
                            <?= count($leaderboard) ?> total participants. Showing Top 20. Then 1000+ more participants...
                        </div>
                    <?php endif; ?>
                <?php else : ?>
                    <div class="text-muted">No active giveaway.</div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
