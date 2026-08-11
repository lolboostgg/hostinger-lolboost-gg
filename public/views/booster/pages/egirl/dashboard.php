<?= $this->layout('booster/layouts/main', ['meta' => $meta]) ?>

<?php $egSharedActiveTab = 'overview'; $egSharedHideNav = true; ?>
<?= $this->insert('booster/pages/egirl/_shared') ?>


<style>
/* ══ KPI tiles — same visual language as the booster dashboard's sv2-kpi tiles ══ */
.eg-kpi-grid { display:grid; grid-template-columns:repeat(4,1fr); gap:14px; margin:1.5rem 0; }
@media(max-width:900px){ .eg-kpi-grid { grid-template-columns:repeat(2,1fr); } }
@media(max-width:767.98px){ .eg-kpi-value{font-size:1.6rem;} }
.eg-kpi { border:1px solid var(--bs-border-color); border-radius:18px; padding:18px; background:rgba(255,255,255,.02); position:relative; overflow:hidden; }
.eg-kpi::before { content:''; position:absolute; top:-28px; right:-18px; width:80px; height:80px; border-radius:50%; opacity:.13; }
.eg-kpi--purple { background:linear-gradient(135deg,rgba(109,92,255,.16),rgba(255,255,255,.03)); border-color:rgba(109,92,255,.22); }
.eg-kpi--purple::before { background:#6d5cff; }
.eg-kpi--green  { background:linear-gradient(135deg,rgba(34,197,94,.13),rgba(255,255,255,.03)); border-color:rgba(34,197,94,.20); }
.eg-kpi--green::before  { background:#22c55e; }
.eg-kpi--blue   { background:linear-gradient(135deg,rgba(56,189,248,.13),rgba(255,255,255,.03)); border-color:rgba(56,189,248,.20); }
.eg-kpi--blue::before   { background:#38bdf8; }
.eg-kpi--pink   { background:linear-gradient(135deg,rgba(244,114,182,.16),rgba(255,255,255,.03)); border-color:rgba(244,114,182,.22); }
.eg-kpi--pink::before   { background:#f472b6; }
.eg-kpi-label { color:var(--eg-muted); font-size:.82rem; font-weight:800; text-transform:uppercase; letter-spacing:.08em; }
.eg-kpi-value { color:var(--eg-text); font-size:2rem; font-weight:900; margin-top:8px; line-height:1; }
.eg-kpi-sub   { color:var(--eg-muted); margin-top:6px; font-size:.84rem; }
.eg-kpi--purple .eg-kpi-value { color:#c4b5fd; }
.eg-kpi--green  .eg-kpi-value { color:#4ade80; }
.eg-kpi--blue   .eg-kpi-value { color:#7dd3fc; }
.eg-kpi--pink   .eg-kpi-value { color:#f9a8d4; }

.eg-section-top { display:flex; align-items:flex-start; justify-content:space-between; gap:16px; }
.eg-card-title { color:var(--eg-text); font-weight:900; font-size:.95rem; margin:0; }
.eg-inline-link { color:rgba(255,255,255,.84); text-decoration:none; font-weight:800; }
.eg-inline-link:hover { color:#fff; }

/* ══ Activity feed ══ */
.eg-feed { display:grid; gap:12px; }
.eg-feed-item { display:flex; align-items:flex-start; gap:12px; padding:14px 16px; border:1px solid var(--bs-border-color); border-radius:16px; background:rgba(255,255,255,.02); }
.eg-feed-dot  { width:9px; height:9px; border-radius:50%; flex-shrink:0; margin-top:5px; }
.eg-feed-dot--green { background:#4ade80; box-shadow:0 0 7px rgba(74,222,128,.5); }
.eg-feed-dot--red   { background:#fb7185; box-shadow:0 0 7px rgba(251,113,133,.4); }
.eg-feed-body  { flex:1; min-width:0; }
.eg-feed-title { font-size:.86rem; font-weight:900; color:var(--eg-text); }
.eg-feed-meta  { font-size:.76rem; color:var(--eg-muted); margin-top:3px; }
.eg-feed-badge { flex-shrink:0; font-size:.76rem; font-weight:900; padding:5px 10px; border-radius:10px; white-space:nowrap; }
.eg-feed-badge--green { background:rgba(74,222,128,.10); border:1px solid rgba(74,222,128,.22); color:#4ade80; }
.eg-feed-badge--red   { background:rgba(251,113,133,.10); border:1px solid rgba(251,113,133,.22); color:#fb7185; }

/* ══ Quick actions ══ */
.eg-actions-2x2 { display:grid; grid-template-columns:1fr 1fr; gap:14px; }
@media(max-width:575.98px){ .eg-actions-2x2{grid-template-columns:1fr;} }
.eg-action-card { display:flex; gap:14px; align-items:flex-start; text-decoration:none; color:inherit; padding:18px; border-radius:18px; border:1px solid var(--bs-border-color); background:rgba(255,255,255,.02); transition:.18s ease; }
.eg-action-card:hover { transform:translateY(-2px); border-color:rgba(244,114,182,.30); box-shadow:0 18px 36px rgba(244,114,182,.10); }
.eg-action-card-primary { background:linear-gradient(135deg,rgba(244,114,182,.16),rgba(255,255,255,.02)); border-color:rgba(244,114,182,.26); }
.eg-action-icon { width:44px; height:44px; border-radius:14px; flex-shrink:0; display:flex; align-items:center; justify-content:center; background:linear-gradient(135deg,rgba(244,114,182,.26),rgba(168,85,247,.16)); border:1px solid rgba(244,114,182,.22); color:#fff; font-size:1rem; }
.eg-action-title { color:var(--eg-text); font-weight:900; font-size:.88rem; }
.eg-action-text  { color:var(--eg-muted); margin-top:4px; font-size:.82rem; }

/* ══ Profile-line tiles + empty state ══ */
.eg-profile-line { display:flex; align-items:center; justify-content:space-between; gap:12px; padding:14px 16px; border-radius:16px; border:1px solid var(--bs-border-color); background:rgba(255,255,255,.02); }
.eg-profile-line span   { color:var(--eg-muted); font-size:.84rem; }
.eg-profile-line strong { color:var(--eg-text); font-size:.9rem; font-weight:800; }
.eg-empty-state2 { text-align:center; padding:44px 20px 20px; }
.eg-empty-state2 i { font-size:2.4rem; color:rgba(255,255,255,.22); display:block; margin-bottom:14px; }
.eg-empty-title2 { color:var(--eg-text); font-weight:900; font-size:.9rem; }
.eg-empty-text2  { color:var(--eg-muted); font-size:.84rem; margin-top:4px; }
</style>

<div class="content container-fluid">

    <!-- ══ KPI TILES ══ -->
    <div class="eg-kpi-grid">
        <div class="eg-kpi eg-kpi--purple">
            <div class="eg-kpi-label">Available for Payout</div>
            <div class="eg-kpi-value"><?= function_exists('util_format_price_display') ? util_format_price_display($balance_cents) : number_format($balance_cents/100,2) ?> €</div>
            <div class="eg-kpi-sub">Ready for payout</div>
        </div>
        <div class="eg-kpi eg-kpi--green">
            <div class="eg-kpi-label">Lifetime Earned</div>
            <div class="eg-kpi-value"><?= function_exists('util_format_price_display') ? util_format_price_display($lifetime_earned_cents ?? 0) : number_format(($lifetime_earned_cents ?? 0)/100,2) ?> €</div>
            <div class="eg-kpi-sub">
                <?php if (!empty($lifetime_deductions_cents)): ?>
                    −<?= function_exists('util_format_price_display') ? util_format_price_display($lifetime_deductions_cents) : number_format($lifetime_deductions_cents/100,2) ?> € in deductions
                <?php else: ?>
                    All completed sessions
                <?php endif; ?>
            </div>
        </div>
        <div class="eg-kpi eg-kpi--blue">
            <div class="eg-kpi-label">Active Sessions</div>
            <div class="eg-kpi-value"><?= (int)$stats['orders_active'] ?></div>
            <div class="eg-kpi-sub">Currently in progress</div>
        </div>
        <div class="eg-kpi eg-kpi--pink">
            <div class="eg-kpi-label">Total Bookings</div>
            <div class="eg-kpi-value"><?= (int)$stats['orders_total'] ?></div>
            <div class="eg-kpi-sub"><?= (int)$stats['orders_completed'] ?> completed</div>
        </div>
    </div>

    <!-- ══ ROW 2: Activity + Quick Actions ══ -->
    <div class="row g-3 align-items-stretch mb-3">

        <!-- Activity / Recent Payments -->
        <div class="col-12 col-xl-7">
            <div class="card h-100">
                <div class="card-body p-4">
                    <div class="eg-section-top">
                        <h3 class="eg-card-title mb-0">Recent activity</h3>
                        <a href="<?= htmlspecialchars(BSTR_URL . '/egirl-payments') ?>" class="eg-inline-link">View all</a>
                    </div>
                    <?php
                        try {
                            global $db;
                            $egRecentPays = $db->run(
                                "SELECT type, amount, currency, note, created_at FROM egirl_payments WHERE egirl_id = ? ORDER BY id DESC LIMIT 5",
                                BOOSTER_ID
                            );
                        } catch (Throwable $e) { $egRecentPays = []; }
                    ?>
                    <?php if (!empty($egRecentPays)): ?>
                        <div class="eg-feed mt-3">
                            <?php foreach ($egRecentPays as $p):
                                $amt    = (int)($p['amount'] ?? 0);
                                $isPos  = !in_array(strtolower((string)($p['type'] ?? '')), ['deduction'], true);
                                $amtStr = ($isPos ? '+' : '−') . (function_exists('util_format_price_display') ? util_format_price_display(abs($amt)) : number_format(abs($amt)/100,2)) . ' ' . strtoupper($p['currency'] ?? 'EUR');
                                $type   = ucwords(str_replace('_', ' ', (string)($p['type'] ?? 'payment')));
                                $note   = htmlspecialchars(trim((string)($p['note'] ?? '')));
                                $date   = !empty($p['created_at']) ? date('d.m.Y · H:i', strtotime($p['created_at'])) : '—';
                            ?>
                                <div class="eg-feed-item">
                                    <div class="eg-feed-dot <?= $isPos ? 'eg-feed-dot--green' : 'eg-feed-dot--red' ?>"></div>
                                    <div class="eg-feed-body">
                                        <div class="eg-feed-title"><?= htmlspecialchars($type) ?></div>
                                        <div class="eg-feed-meta"><?= $date ?><?= $note !== '' ? ' · ' . $note : '' ?></div>
                                    </div>
                                    <span class="eg-feed-badge <?= $isPos ? 'eg-feed-badge--green' : 'eg-feed-badge--red' ?>"><?= $amtStr ?></span>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="eg-empty-state2 mt-4">
                            <i class="fa-duotone fa-inbox"></i>
                            <div class="eg-empty-title2">No activity yet</div>
                            <div class="eg-empty-text2">Your first session payout will appear here.</div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Right: Quick Actions + Orders -->
        <div class="col-12 col-xl-5 d-flex flex-column gap-3">

            <!-- Quick Actions -->
            <div class="card">
                <div class="card-body p-4">
                    <div class="eg-section-top mb-3">
                        <h3 class="eg-card-title mb-0">Quick actions</h3>
                    </div>
                    <div class="eg-actions-2x2">
                        <a href="<?= htmlspecialchars(BSTR_URL . '/egirl-panel') ?>" class="eg-action-card eg-action-card-primary">
                            <div class="eg-action-icon"><i class="fa-duotone fa-list-check"></i></div>
                            <div>
                                <div class="eg-action-title">Booking Panel</div>
                                <div class="eg-action-text">Claim open bookings</div>
                            </div>
                        </a>
                        <a href="<?= htmlspecialchars(BSTR_URL . '/egirl-payout') ?>" class="eg-action-card">
                            <div class="eg-action-icon"><i class="fa-duotone fa-money-check-dollar"></i></div>
                            <div>
                                <div class="eg-action-title">Request payout</div>
                                <div class="eg-action-text">Withdraw balance</div>
                            </div>
                        </a>
                        <a href="<?= htmlspecialchars(BSTR_URL . '/egirl-services') ?>" class="eg-action-card">
                            <div class="eg-action-icon"><i class="fa-duotone fa-layer-group"></i></div>
                            <div>
                                <div class="eg-action-title">My Services</div>
                                <div class="eg-action-text">Manage offerings</div>
                            </div>
                        </a>
                        <a href="<?= htmlspecialchars(BSTR_URL . '/egirl-profile') ?>" class="eg-action-card">
                            <div class="eg-action-icon"><i class="fa-duotone fa-user-gear"></i></div>
                            <div>
                                <div class="eg-action-title">My Profile</div>
                                <div class="eg-action-text">Settings & info</div>
                            </div>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Orders summary -->
            <div class="card flex-grow-1">
                <div class="card-body p-4">
                    <div class="eg-section-top mb-3">
                        <h3 class="eg-card-title mb-0">Bookings overview</h3>
                        <a href="<?= htmlspecialchars(BSTR_URL . '/egirl-panel') ?>" class="eg-inline-link">Browse →</a>
                    </div>
                    <div class="d-grid gap-2">
                        <?php
                            $egOrderTiles = [
                                ['label' => 'Active',    'val' => (int)($stats['orders_active'] ?? 0),    'color' => '#7dd3fc'],
                                ['label' => 'Completed', 'val' => (int)($stats['orders_completed'] ?? 0), 'color' => '#4ade80'],
                                ['label' => 'Total',     'val' => (int)($stats['orders_total'] ?? 0),     'color' => 'rgba(255,255,255,.6)'],
                            ];
                            foreach ($egOrderTiles as $tile):
                        ?>
                        <div class="eg-profile-line">
                            <span><?= $tile['label'] ?></span>
                            <strong style="color:<?= $tile['color'] ?>;"><?= $tile['val'] ?></strong>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <!-- Payout CTA -->
                    <div class="d-flex align-items-center justify-content-between mt-3 p-3 rounded-3" style="background:rgba(244,114,182,.08);border:1px solid rgba(244,114,182,.2);">
                        <div>
                            <div style="font-size:.72rem;font-weight:800;text-transform:uppercase;letter-spacing:.07em;color:rgba(255,255,255,.4);margin-bottom:3px;">Payout available</div>
                            <div style="font-size:1rem;font-weight:900;color:#f9a8d4;"><?= function_exists('util_format_price_display') ? util_format_price_display($balance_cents) : number_format($balance_cents/100,2) ?> EUR</div>
                        </div>
                        <a href="<?= htmlspecialchars(BSTR_URL . '/egirl-payout') ?>" class="btn btn-primary btn-sm">Request</a>
                    </div>
                </div>
            </div>

        </div>
    </div>
    <div class="card">
        <div class="card-header d-flex align-items-center justify-content-between">
            <h5 class="card-header-title">Recent Bookings</h5>
            <a href="<?= BSTR_URL ?>/egirl-orders" class="eg-inline-link">View All</a>
        </div>
        <div class="card-body p-0">
            <?php if (empty($recent_orders)): ?>
                <div class="text-center py-5 text-muted">
                    <i class="fa-duotone fa-calendar fa-2x mb-2 d-block"></i>
                    No bookings yet. Make sure your profile is complete and your services are set up!
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-borderless table-align-middle mb-0">
                        <thead class="thead-light">
                            <tr>
                                <th>Client</th>
                                <th>Service</th>
                                <th>Price</th>
                                <th>Status</th>
                                <th>Date</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recent_orders as $o): ?>
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <?php if ($o['client_icon']): ?>
                                                <img src="<?= htmlspecialchars($o['client_icon']) ?>" class="avatar avatar-xs rounded-circle" alt="">
                                            <?php endif; ?>
                                            <?= htmlspecialchars($o['client_username'] ?? '—') ?>
                                        </div>
                                    </td>
                                    <td><?= htmlspecialchars($o['service_title'] ?? $o['service_type']) ?></td>
                                    <td><?php $oCur = strtoupper((string)($o['currency'] ?? 'EUR')); $oSym = function_exists('util_currency_symbol') ? util_currency_symbol($oCur) : ($oCur === 'USD' ? '$' : '€'); ?><?= $oSym ?><?= function_exists('util_format_price_display') ? util_format_price_display((int)$o['price']) : number_format($o['price']/100,2) ?> <span class="text-muted"><?= htmlspecialchars($oCur) ?></span></td>
                                    <td>
                                        <?php
                                            $statusColors = ['PAID'=>'primary','IN_PROGRESS'=>'info','COMPLETED'=>'success','UNPAID'=>'danger','CANCELLED'=>'warning','REFUNDED'=>'secondary'];
                                            $sc = $statusColors[$o['status']] ?? 'secondary';
                                        ?>
                                        <span class="badge bg-soft-<?= $sc ?> text-<?= $sc ?>"><?= htmlspecialchars($o['status']) ?></span>
                                    </td>
                                    <td><?= date('M j, Y', strtotime($o['created_at'])) ?></td>
                                    <td>
                                        <a href="<?= BSTR_URL ?>/egirl-order/<?= $o['id'] ?>" class="btn btn-sm btn-white">View</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
