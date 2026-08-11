<?php
// Public Giveaway Page (Jobs-style layout)
// Expected variables from route:
//   $meta
//   $giveaway (active giveaway or null)
//   $leaderboard (array)
//   $winners (array)
//   $last_drawn_giveaway (array|null)

$maskUsername = function ($username) {
    if (function_exists('giveaway_mask_username')) {
        return giveaway_mask_username($username);
    }
    $u = (string) $username;
    if ($u === '') return '';
    if (strlen($u) <= 2) return $u[0] . '*';
    return $u[0] . str_repeat('*', max(1, strlen($u) - 2)) . substr($u, -1);
};

$top = array_slice($leaderboard ?? [], 0, 20);
$hasMore = !empty($leaderboard) && count($leaderboard) > 20;
$last = $last_drawn_giveaway ?? null;
?>

<?= $this->layout('website/layouts/master', ['meta' => $meta, 'bodyClass' => 'jobs-page giveaway-page']) ?>

<header>
    <div class="content">
        <h1><?= t('Giveaway') ?></h1>

        <p><?= t('Earn 1 ticket with every paid purchase. More tickets = higher chance to win.') ?></p>
    </div>
</header>

<div class="requirements-sec">
    <h2><?= t('Current Giveaway') ?></h2>
    <h6><?= t('Follow the leaderboard live. When the giveaway ends, winners are drawn based on tickets (more tickets = higher chance).') ?></h6>

    <div class="requirement-cards">
        <div class="card">
            <img src="<?= ASSET_URL ?>/website/images/jobs/general.svg" alt="general_icon">
            <h4><?= t('Giveaway Details') ?></h4>
            <p><?= t('Active campaign') ?></p>

            <div class="list-box">
                <?php if (!empty($giveaway)) : ?>
                    <p><?= t('Current giveaway:') ?> <strong><?= htmlspecialchars($giveaway['title'] ?? 'Giveaway') ?></strong></p>

                    <?php if (!empty($giveaway['description'])) : ?>
                        <p style="margin-top:10px;"><?= htmlspecialchars($giveaway['description']) ?></p>
                    <?php endif; ?>

                    <ul>
                        <li><?= t('Ends:') ?> <strong><?= htmlspecialchars($giveaway['ends_at'] ?? '-') ?></strong></li>
                        <li><?= t('Winners:') ?> <strong><?= (int)($giveaway['winners_count'] ?? 0) ?></strong></li>
                    </ul>
                <?php else : ?>
                    <p><?= t('There is no active giveaway right now.') ?></p>
                    <ul>
                        <li><?= t('Check back soon.') ?></li>
                        <li><?= t('Tickets are collected during active giveaways only.') ?></li>
                    </ul>
                <?php endif; ?>
            </div>

            <h5><?= t('How it works') ?></h5>
            <div class="earning-boxes">
                <div class="box">
                    <h6><?= t('Paid order = ticket') ?></h6>
                    <p><?= t('Each paid invoice gives you 1 ticket for the active giveaway.') ?></p>
                </div>
                <div class="box">
                    <h6><?= t('Refund/Unpaid') ?></h6>
                    <p><?= t('If an order is refunded or becomes unpaid, the ticket is revoked.') ?></p>
                </div>
            </div>
        </div>

        <div class="card">
            <img src="<?= ASSET_URL ?>/website/images/jobs/booster.svg" alt="booster_icon">
            <h4><?= t('Leaderboard') ?></h4>
            <p><?= t('Top 20') ?></p>

            <div class="list-box">
                <?php if (!empty($giveaway)) : ?>
                    <?php if (!empty($top)) : ?>
                        <p><?= t('Top participants right now:') ?></p>
                        <ul>
                            <?php foreach ($top as $i => $r) : ?>
                                <li style="display:flex;align-items:center;gap:10px;justify-content:space-between;">
                                    <span style="display:flex;align-items:center;gap:10px;min-width:0;">
                                        <span style="opacity:.8;min-width:24px;">#<?= $i + 1 ?></span>
                                        <img src="<?= htmlspecialchars($r['icon'] ?? '') ?>" alt="" style="width:26px;height:26px;border-radius:50%;object-fit:cover;">
                                        <strong style="white-space:nowrap;overflow:hidden;text-overflow:ellipsis;max-width:220px;">
                                            <?= htmlspecialchars($maskUsername($r['username'] ?? '')) ?>
                                        </strong>
                                    </span>
                                    <strong><?= (int)($r['tickets'] ?? 0) ?></strong>
                                </li>
                            <?php endforeach; ?>
                        </ul>

                        <?php if ($hasMore) : ?>
                            <p style="margin-top:12px;opacity:.9;">
                                <?= t('Showing Top 20. Then 1000+ more participants...') ?>
                            </p>
                        <?php endif; ?>
                    <?php else : ?>
                        <p><?= t('No participants yet.') ?></p>
                        <ul>
                            <li><?= t('Be the first to enter by placing a paid order.') ?></li>
                        </ul>
                    <?php endif; ?>
                <?php else : ?>
                    <p><?= t('No active giveaway.') ?></p>
                    <ul>
                        <li><?= t('Leaderboard will appear when a giveaway is active.') ?></li>
                    </ul>
                <?php endif; ?>
            </div>

            <a href="/profile/giveaway" class="btn primary" style="margin-top:14px;display:inline-block;">
                <?= t('Open your tickets') ?>
            </a>
        </div>

        <div class="card">
            <img src="<?= ASSET_URL ?>/website/images/jobs/elite.svg" alt="elite_icon">
            <h4><?= t('Latest Winners') ?></h4>
            <p><?= t('Most recent draw') ?></p>

            <div class="list-box">
                <?php if (!empty($winners)) : ?>
                    <?php if (!empty($last)) : ?>
                        <p><?= t('From:') ?> <strong><?= htmlspecialchars($last['title'] ?? 'Giveaway') ?></strong></p>
                    <?php endif; ?>
                    <ul>
                        <?php foreach ($winners as $w) : ?>
                            <li style="display:flex;align-items:center;gap:10px;justify-content:space-between;">
                                <span style="display:flex;align-items:center;gap:10px;min-width:0;">
                                    <img src="<?= htmlspecialchars($w['icon'] ?? '') ?>" alt="" style="width:26px;height:26px;border-radius:50%;object-fit:cover;">
                                    <strong style="white-space:nowrap;overflow:hidden;text-overflow:ellipsis;max-width:220px;">
                                        <?= htmlspecialchars($maskUsername($w['username'] ?? '')) ?>
                                    </strong>
                                </span>
                                <span style="opacity:.9;"><strong><?= (int)($w['tickets_at_draw'] ?? 0) ?></strong> <?= t('tickets') ?></span>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php else : ?>
                    <p><?= t('No winners announced yet.') ?></p>
                    <ul>
                        <li><?= t('Winners are displayed after the giveaway is drawn.') ?></li>
                    </ul>
                <?php endif; ?>
            </div>

            <h5><?= t('Fair draw') ?></h5>
            <div class="earning-boxes">
                <div class="box">
                    <h6><?= t('Weighted chance') ?></h6>
                    <p><?= t('More tickets increase your chance of winning.') ?></p>
                </div>
                <div class="box">
                    <h6><?= t('Resets each giveaway') ?></h6>
                    <p><?= t('Tickets reset when a giveaway is drawn and a new one starts.') ?></p>
                </div>
            </div>
        </div>
    </div>
</div>


