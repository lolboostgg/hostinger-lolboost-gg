

<?= $this->layout('admin/layouts/main', ['meta' => $meta]) ?>
<?= $this->start('styles') ?>
<style>

.admin-order-view .eg-shell{display:grid;grid-template-columns:minmax(0,1fr) 340px;gap:1rem;align-items:start}
@media(max-width:991px){.admin-order-view .eg-shell{grid-template-columns:1fr}}
.admin-order-view .eg-card{border-radius:1rem;overflow:hidden;border:1px solid rgba(255,255,255,.06);background:rgba(255,255,255,.02)}
.admin-order-view .eg-header{padding:1rem 1.05rem}
.admin-order-view .eg-topbar{display:flex;align-items:flex-start;justify-content:space-between;gap:1rem;flex-wrap:wrap}
.admin-order-view .eg-title{margin:0;font-size:1.15rem;font-weight:900;line-height:1.15}
.admin-order-view .eg-submeta{display:flex;flex-wrap:wrap;gap:.5rem;margin-top:.75rem}
.admin-order-view .eg-chip{display:inline-flex;align-items:center;gap:.4rem;padding:.42rem .75rem;border-radius:999px;border:1px solid rgba(255,255,255,.08);background:rgba(255,255,255,.04);font-size:.76rem;font-weight:800;color:rgba(255,255,255,.82)}
.admin-order-view .eg-actions{display:flex;gap:.5rem;flex-wrap:wrap}
.admin-order-view .eg-actions .btn{border-radius:999px;font-weight:800}
.admin-order-view .eg-hero{position:relative;overflow:hidden}
.admin-order-view .eg-hero-bg{position:absolute;inset:0;background:linear-gradient(135deg,rgba(168,85,247,.18),rgba(236,72,153,.10));pointer-events:none}
.admin-order-view .eg-hero-body{position:relative;padding:1rem 1.05rem;display:flex;align-items:center;justify-content:space-between;gap:1rem;flex-wrap:wrap}
.admin-order-view .eg-hero-left{display:flex;align-items:center;gap:.9rem;min-width:0}
.admin-order-view .eg-hero-avatar{width:68px;height:68px;border-radius:50%;overflow:hidden;border:2px solid rgba(255,255,255,.12);box-shadow:0 12px 36px rgba(0,0,0,.28);background:rgba(255,255,255,.04);flex:0 0 auto}
.admin-order-view .eg-hero-avatar img{width:100%;height:100%;object-fit:cover}
.admin-order-view .eg-hero-name{font-size:1.02rem;font-weight:900;line-height:1.1;margin:0}
.admin-order-view .eg-hero-role{display:flex;align-items:center;gap:.45rem;flex-wrap:wrap;margin-top:.45rem}
.admin-order-view .eg-pill-soft{display:inline-flex;align-items:center;gap:.35rem;padding:.32rem .62rem;border-radius:999px;background:rgba(255,255,255,.05);border:1px solid rgba(255,255,255,.10);font-size:.72rem;font-weight:800}
.admin-order-view .eg-stat-row{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:.65rem;padding:0 1.05rem 1rem}
@media(max-width:767px){.admin-order-view .eg-stat-row{grid-template-columns:repeat(2,minmax(0,1fr))}}
.admin-order-view .eg-stat{padding:.72rem .8rem;border-radius:.9rem;background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.08)}
.admin-order-view .eg-stat-l{font-size:.68rem;text-transform:uppercase;letter-spacing:.08em;color:rgba(255,255,255,.48);font-weight:800}
.admin-order-view .eg-stat-v{margin-top:.2rem;font-size:.92rem;font-weight:900;color:rgba(255,255,255,.92)}
.admin-order-view .eg-section-title{font-size:.9rem;font-weight:900;letter-spacing:.08em;text-transform:uppercase;margin:0}
.admin-order-view .eg-list-card .card-body{padding:.7rem .8rem}
.admin-order-view .eg-list-item{display:grid;grid-template-columns:42px 1fr;gap:.75rem;padding:.7rem;border-radius:.85rem;border:1px solid rgba(255,255,255,.06);background:rgba(255,255,255,.03)}
.admin-order-view .eg-list-item+.eg-list-item{margin-top:.55rem}
.admin-order-view .eg-list-ico{width:42px;height:42px;border-radius:14px;display:grid;place-items:center;background:rgba(0,0,0,.18);border:1px solid rgba(255,255,255,.08)}
.admin-order-view .eg-list-l{font-size:.72rem;text-transform:uppercase;letter-spacing:.08em;color:rgba(255,255,255,.42);font-weight:800}
.admin-order-view .eg-list-v{font-size:.9rem;font-weight:800;color:rgba(255,255,255,.9);margin-top:.15rem;word-break:break-word}
.admin-order-view .eg-side-stack{display:flex;flex-direction:column;gap:1rem}
.admin-order-view .eg-side-card .card-body{padding:.8rem}
.admin-order-view .eg-kv{display:flex;align-items:center;justify-content:space-between;gap:.75rem;padding:.72rem .2rem;border-bottom:1px solid rgba(255,255,255,.06)}
.admin-order-view .eg-kv:last-child{border-bottom:0}
.admin-order-view .eg-kv-l{font-size:.74rem;text-transform:uppercase;letter-spacing:.08em;color:rgba(255,255,255,.44);font-weight:800}
.admin-order-view .eg-kv-r{font-size:.86rem;font-weight:800;color:rgba(255,255,255,.9);text-align:right}
.admin-order-view .eg-side-actions{display:grid;grid-template-columns:1fr 1fr;gap:.55rem}
.admin-order-view .eg-side-actions .btn{border-radius:.85rem;font-weight:800}
@media(max-width:575px){.admin-order-view .eg-side-actions{grid-template-columns:1fr}}
.admin-order-view .eg-chat-card .card-header,.admin-order-view .eg-side-card .card-header,.admin-order-view .eg-list-card .card-header{padding:.8rem 1rem}
.admin-order-view .eg-empty{padding:1.25rem;text-align:center;color:rgba(255,255,255,.42);font-weight:700}

.eg-sp{display:inline-flex;align-items:center;gap:.38rem;padding:.28rem .82rem;border-radius:999px;font-size:.72rem;font-weight:800;letter-spacing:.06em;text-transform:uppercase;}
.eg-sp::before{content:"";width:6px;height:6px;border-radius:50%;flex-shrink:0;}
.eg-sp.paid{background:rgba(177,140,255,.12);border:1px solid rgba(177,140,255,.3);color:#b18cff;}.eg-sp.paid::before{background:#b18cff;}
.eg-sp.in_progress{background:rgba(78,161,255,.1);border:1px solid rgba(78,161,255,.3);color:#4ea1ff;}.eg-sp.in_progress::before{background:#4ea1ff;box-shadow:0 0 0 3px rgba(78,161,255,.2);}
.eg-sp.completed{background:rgba(31,230,198,.1);border:1px solid rgba(31,230,198,.3);color:#1fe6c6;}.eg-sp.completed::before{background:#1fe6c6;}
.eg-sp.unpaid{background:rgba(255,107,107,.1);border:1px solid rgba(255,107,107,.3);color:#ff6b6b;}.eg-sp.unpaid::before{background:#ff6b6b;}
.eg-sp.cancelled,.eg-sp.refunded{background:rgba(255,138,76,.1);border:1px solid rgba(255,138,76,.3);color:#ff8a4c;}.eg-sp.cancelled::before,.eg-sp.refunded::before{background:#ff8a4c;}

.eg-grid{display:grid;grid-template-columns:1fr 300px;gap:1.2rem;align-items:start;}
@media(max-width:991px){.eg-grid{grid-template-columns:1fr;}}

.eg-ov-list{list-style:none;margin:0;padding:0;}
.eg-ov-item{display:grid;grid-template-columns:1.3rem 1fr auto;align-items:center;gap:.5rem;padding:.52rem 0;border-bottom:1px solid rgba(255,255,255,.06);}
.eg-ov-item:last-child{border-bottom:0;}
.eg-ov-ico{font-size:.85rem;text-align:center;color:rgba(168,85,247,.45);}
.eg-ov-lbl{font-size:.72rem;font-weight:700;text-transform:uppercase;letter-spacing:.04em;color:rgba(255,255,255,.3);}
.eg-ov-val{font-weight:700;font-size:.83rem;text-align:right;color:rgba(255,255,255,.78);}

/* Chat */
.eg-chat-bg{height:clamp(280px,42vh,440px);overflow-y:auto;padding:1rem 1.15rem;display:flex;flex-direction:column;gap:9px;scrollbar-width:thin;scrollbar-color:rgba(168,85,247,.18) transparent;}
.eg-chat-bg::-webkit-scrollbar{width:3px;}.eg-chat-bg::-webkit-scrollbar-thumb{background:rgba(168,85,247,.25);border-radius:2px;}
.eg-m{display:flex;flex-direction:column;max-width:78%;}
.eg-m.me{align-self:flex-end;}.eg-m.them{align-self:flex-start;}.eg-m.sys{align-self:center;max-width:90%;}
.eg-m-head{display:flex;align-items:center;gap:7px;margin-bottom:4px;}
.eg-m.me .eg-m-head{flex-direction:row-reverse;}
.eg-m-av{width:30px;height:30px;border-radius:50%;flex-shrink:0;overflow:hidden;border:1.5px solid rgba(168,85,247,.3);background:rgba(168,85,247,.1);display:flex;align-items:center;justify-content:center;font-size:.68rem;font-weight:800;color:#c084fc;}
.eg-m-av img{width:100%;height:100%;object-fit:cover;border-radius:50%;}
.eg-m-av.mine{border-color:rgba(255,196,77,.4);background:rgba(255,196,77,.1);color:#ffc44d;}
.eg-m-av.client-av{border-color:rgba(78,161,255,.4);background:rgba(78,161,255,.1);color:#4ea1ff;}
.eg-m-name{font-size:.72rem;font-weight:800;color:rgba(255,255,255,.35);}
.eg-m-bbl{padding:.5rem .78rem;border-radius:11px;font-size:.83rem;line-height:1.5;word-break:break-word;color:rgba(255,255,255,.85);}
.eg-m.them .eg-m-bbl{background:rgba(168,85,247,.12);border:1px solid rgba(168,85,247,.18);border-top-left-radius:3px;}
.eg-m.me   .eg-m-bbl{background:rgba(255,196,77,.1);border:1px solid rgba(255,196,77,.2);border-top-right-radius:3px;}
.eg-m.sys  .eg-m-bbl{background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.08);border-radius:8px;font-size:.78rem;color:rgba(255,255,255,.45);text-align:center;}
.eg-m-ts{font-size:.62rem;color:rgba(255,255,255,.22);margin-top:.18rem;}
.eg-m.me .eg-m-ts{text-align:right;}
.eg-chat-empty{display:flex;flex-direction:column;align-items:center;justify-content:center;gap:.45rem;color:rgba(255,255,255,.22);padding:2rem;text-align:center;}

/* Admin send bar */
.eg-admin-footer{padding:.7rem 1rem;border-top:1px solid rgba(255,255,255,.07);display:flex;gap:.5rem;align-items:flex-end;}
.eg-admin-ta{flex:1;resize:none;min-height:36px;max-height:90px;background:rgba(255,255,255,.05);border:1px solid rgba(255,255,255,.1);border-radius:9px;padding:.43rem .73rem;color:rgba(255,255,255,.88);font-size:.83rem;line-height:1.4;outline:none;font-family:inherit;}
.eg-admin-ta:focus{border-color:rgba(168,85,247,.4);}
.eg-admin-send{padding:.45rem .9rem;border-radius:9px;background:linear-gradient(135deg,#a855f7,#ec4899);border:none;color:#fff;cursor:pointer;font-size:.82rem;font-weight:700;}
.eg-admin-send:disabled{opacity:.4;cursor:not-allowed;}

/* Sidebar cards */
.eg-eg-card{background:rgba(168,85,247,.04);border:1px solid rgba(168,85,247,.12);border-radius:12px;overflow:hidden;}
.eg-eg-banner{height:48px;background:linear-gradient(135deg,rgba(168,85,247,.35),rgba(236,72,153,.2));}
.eg-eg-body{padding:0 .9rem .85rem;}
.eg-eg-av{width:44px;height:44px;border-radius:50%;margin-top:-22px;border:2.5px solid rgba(14,15,24,.9);background:rgba(168,85,247,.15);display:flex;align-items:center;justify-content:center;font-size:.95rem;font-weight:800;color:#c084fc;overflow:hidden;}
.eg-eg-av img{width:100%;height:100%;object-fit:cover;border-radius:50%;}
</style>
<?= $this->end() ?>

<div class="admin-order-view">
<?php
$order      = $order ?? [];
$messages   = $messages ?? [];
$id         = (int)($order['id'] ?? 0);
$statusRaw  = strtoupper($order['status'] ?? 'UNPAID');
$statusKey  = strtolower(str_replace(' ', '_', $statusRaw));
$priceCents = (int)($order['price'] ?? 0);
$svcName    = htmlspecialchars($order['service_title'] ?? 'E-Girl Session');
$gameName   = !empty($order['game']) ? strtoupper(htmlspecialchars($order['game'])) : '';
$egirlName  = htmlspecialchars($order['egirl_username'] ?? '—');
$egirlIcon  = $order['egirl_icon'] ?? '';
$egirlInit  = strtoupper(substr(preg_replace('/[^A-Za-z0-9]/', '', $order['egirl_username'] ?? 'E') ?: 'E', 0, 1));
$clientName = htmlspecialchars($order['client_username'] ?? '—');
$clientIcon = $order['client_icon'] ?? '';
$clientInit = strtoupper(substr(preg_replace('/[^A-Za-z0-9]/', '', $order['client_username'] ?? 'C') ?: 'C', 0, 1));

$lastMsgTime = 0;
if (!empty($messages)) {
    $last = end($messages);
    $lastMsgTime = (int)($last['time'] ?? 0);
}
?>

<!-- Breadcrumb -->
<nav aria-label="breadcrumb" class="mb-3">
    <ol class="breadcrumb breadcrumb-no-gutter">
        <li class="breadcrumb-item"><a href="<?= ADMN_URL ?>/orders">Orders</a></li>
        <li class="breadcrumb-item"><a href="<?= ADMN_URL ?>/egirl/orders">E-Girl Bookings1</a></li>
        <li class="breadcrumb-item active">#eg<?= $id ?></li>
    </ol>
</nav>

<div class="eg-shell">
    <div class="d-flex flex-column gap-3">

        <div class="card eg-card">
            <div class="card-body eg-header">
                <div class="eg-topbar">
                    <div>
                        <div class="d-flex align-items-center gap-2 flex-wrap">
                            <h1 class="eg-title"><?= $svcName ?></h1>
                            <span class="eg-pill-soft">#eg<?= $id ?></span>
                            <span class="eg-sp <?= $statusKey ?>"><?= str_replace('_', ' ', $statusRaw) ?></span>
                        </div>
                        <div class="eg-submeta">
                            <span class="eg-chip"><i class="fa-solid fa-user"></i> <?= $clientName ?></span>
                            <span class="eg-chip"><i class="fa-solid fa-euro-sign"></i> €<?= number_format($priceCents / 100, 2) ?></span>
                            <span class="eg-chip"><i class="fa-solid fa-clock"></i> <?= (int)($order['unit_value'] ?? 1) ?> <?= htmlspecialchars($order['unit_type'] ?? 'hours') ?></span>
                            <?php if($gameName): ?><span class="eg-chip"><i class="fa-solid fa-gamepad"></i> <?= $gameName ?></span><?php endif; ?>
                        </div>
                    </div>
                    <div class="eg-actions">
                        <a href="<?= ADMN_URL ?>/egirl/orders" class="btn btn-white btn-sm"><i class="fa-solid fa-arrow-left me-1"></i>All Bookings</a>
                        <a href="<?= ADMN_URL ?>/egirl/<?= (int)($order['egirl_id'] ?? 0) ?>" class="btn btn-white btn-sm"><i class="fa-solid fa-user me-1"></i>View E-Girl</a>
                    </div>
                </div>
            </div>
        </div>

        <div class="card eg-card eg-hero">
            <div class="eg-hero-bg"></div>
            <div class="eg-hero-body">
                <div class="eg-hero-left">
                    <div class="eg-hero-avatar">
                        <?php if($egirlIcon): ?><img src="<?= htmlspecialchars($egirlIcon) ?>" alt=""><?php else: ?><div class="w-100 h-100 d-flex align-items-center justify-content-center"><?= $egirlInit ?></div><?php endif; ?>
                    </div>
                    <div>
                        <h2 class="eg-hero-name"><?= $egirlName ?></h2>
                        <div class="eg-hero-role">
                            <span class="eg-pill-soft">E-Girl</span>
                            <span class="eg-pill-soft">Booking #eg<?= $id ?></span>
                        </div>
                    </div>
                </div>
                <div class="eg-actions">
                    <?php if(!empty($order['egirl_id'])): ?>
                    <a href="<?= ADMN_URL ?>/egirl/<?= (int)$order['egirl_id'] ?>" class="btn btn-primary btn-sm"><i class="fa-solid fa-external-link me-1"></i>View Profile</a>
                    <?php endif; ?>
                </div>
            </div>
            <div class="eg-stat-row">
                <div class="eg-stat"><div class="eg-stat-l">Order</div><div class="eg-stat-v">#eg<?= $id ?></div></div>
                <div class="eg-stat"><div class="eg-stat-l">Client</div><div class="eg-stat-v"><?= $clientName ?></div></div>
                <div class="eg-stat"><div class="eg-stat-l">Duration</div><div class="eg-stat-v"><?= (int)($order['unit_value'] ?? 1) ?> <?= htmlspecialchars($order['unit_type'] ?? 'hours') ?></div></div>
                <div class="eg-stat"><div class="eg-stat-l">Booked</div><div class="eg-stat-v"><?= !empty($order['created_at']) ? date('d.m.Y', strtotime($order['created_at'])) : '—' ?></div></div>
            </div>
        </div>

        <div class="card eg-card eg-chat-card">
            <div class="card-header d-flex align-items-center justify-content-between">
                <h5 class="eg-section-title"><i class="fa-duotone fa-messages me-2"></i>Order Chat</h5>
                <span class="badge bg-soft-secondary text-secondary"><?= count($messages) ?> msg</span>
            </div>
            <div class="eg-chat-bg" id="egChatBody">
                <?php if (empty($messages)): ?>
                    <div class="eg-empty" id="egChatEmpty">No messages yet</div>
                <?php else:
                    $lS = ''; $lSid = 0;
                    foreach ($messages as $msg):
                        $senderType = $msg['sender'] ?? 'system';
                        $senderId   = (int)($msg['sender_id'] ?? 0);
                        $isClient   = ($senderType === 'client');
                        $isEgirl    = ($senderType === 'booster');
                        $isSystem   = ($senderType === 'system' || $senderType === 'admin');
                        $grp        = ($senderType === $lS && $senderId === $lSid);
                        $lS = $senderType; $lSid = $senderId;
                        $sN  = $isClient ? $clientName : ($isEgirl ? $egirlName : 'System');
                        $sI  = $msg['sender_icon'] ?? ($isClient ? $clientIcon : ($isEgirl ? $egirlIcon : ''));
                        $sX  = strtoupper(substr(preg_replace('/[^A-Za-z0-9]/', '', $sN) ?: 'X', 0, 1));
                        $sideClass = $isSystem ? 'sys' : ($isClient ? 'them' : 'me');
                        $avClass   = $isEgirl ? 'mine' : ($isClient ? 'client-av' : '');
                        $msgText   = $msg['raw'] ?? strip_tags($msg['content'] ?? '');
                        $isImg     = ($msg['type'] ?? '') === 'image' || preg_match('/\.(jpg|jpeg|png|gif|webp)(\?|$)/i', $msgText);
                        $ts        = !empty($msg['time']) ? date('d.m.Y H:i', (int)$msg['time']) : '';
                ?>
                <div class="eg-m <?= $sideClass ?>">
                    <?php if (!$grp && !$isSystem): ?>
                    <div class="eg-m-head">
                        <div class="eg-m-av <?= $avClass ?>">
                            <?php if ($sI): ?><img src="<?= htmlspecialchars($sI) ?>" alt=""><?php else: ?><?= $sX ?><?php endif; ?>
                        </div>
                        <div class="eg-m-name"><?= htmlspecialchars($sN) ?></div>
                    </div>
                    <?php endif; ?>
                    <div class="eg-m-bbl">
                        <?php if ($isImg): ?>
                            <a href="<?= htmlspecialchars($msgText) ?>" target="_blank" rel="noopener"><img src="<?= htmlspecialchars($msgText) ?>" style="max-width:200px;border-radius:8px;cursor:zoom-in" loading="lazy"></a>
                        <?php else: ?>
                            <?= nl2br(htmlspecialchars($msgText)) ?>
                        <?php endif; ?>
                    </div>
                    <div class="eg-m-ts"><?= $ts ?></div>
                </div>
                <?php endforeach; endif; ?>
            </div>
            <div class="eg-admin-footer">
                <textarea class="eg-admin-ta" id="egAdminIn" rows="1" placeholder="Type your message"></textarea>
                <button class="eg-admin-send" id="egAdminSend"><i class="fa-solid fa-paper-plane-top"></i></button>
            </div>
        </div>

        <div class="card eg-card eg-list-card">
            <div class="card-header"><h5 class="eg-section-title"><i class="fa-solid fa-layer-group me-2"></i>Session Details</h5></div>
            <div class="card-body">
                <div class="eg-list-item">
                    <div class="eg-list-ico"><i class="fa-solid fa-layer-group"></i></div>
                    <div><div class="eg-list-l">Service</div><div class="eg-list-v"><?= $svcName ?></div></div>
                </div>
                <div class="eg-list-item">
                    <div class="eg-list-ico"><i class="fa-solid fa-clock"></i></div>
                    <div><div class="eg-list-l">Duration</div><div class="eg-list-v"><?= (int)($order['unit_value'] ?? 1) ?> <?= htmlspecialchars($order['unit_type'] ?? 'hours') ?></div></div>
                </div>
                <div class="eg-list-item">
                    <div class="eg-list-ico"><i class="fa-solid fa-euro-sign"></i></div>
                    <div><div class="eg-list-l">Price</div><div class="eg-list-v">€<?= number_format($priceCents / 100, 2) ?></div></div>
                </div>
                <?php if(!empty($order['client_notes'])): ?>
                <div class="eg-list-item">
                    <div class="eg-list-ico"><i class="fa-solid fa-comment-lines"></i></div>
                    <div><div class="eg-list-l">Client Notes</div><div class="eg-list-v"><?= nl2br(htmlspecialchars($order['client_notes'])) ?></div></div>
                </div>
                <?php endif; ?>
            </div>
        </div>

    </div>

    <div class="eg-side-stack">

        <div class="card eg-card eg-side-card">
            <div class="card-header"><h5 class="eg-section-title">Overview</h5></div>
            <div class="card-body">
                <div class="eg-kv"><div class="eg-kv-l">Order ID</div><div class="eg-kv-r">#eg<?= $id ?></div></div>
                <div class="eg-kv"><div class="eg-kv-l">Status</div><div class="eg-kv-r"><span class="eg-sp <?= $statusKey ?>" style="font-size:.62rem"><?= str_replace('_', ' ', $statusRaw) ?></span></div></div>
                <div class="eg-kv"><div class="eg-kv-l">Total</div><div class="eg-kv-r">€<?= number_format($priceCents / 100, 2) ?></div></div>
                <div class="eg-kv"><div class="eg-kv-l">Booked</div><div class="eg-kv-r"><?= !empty($order['created_at']) ? date('d.m.Y H:i', strtotime($order['created_at'])) : '—' ?></div></div>
                <?php if(!empty($order['completed_at'])): ?><div class="eg-kv"><div class="eg-kv-l">Completed</div><div class="eg-kv-r"><?= date('d.m.Y H:i', strtotime($order['completed_at'])) ?></div></div><?php endif; ?>
            </div>
        </div>

        <div class="card eg-card eg-side-card">
            <div class="card-header"><h5 class="eg-section-title">Client</h5></div>
            <div class="card-body d-flex align-items-center gap-3">
                <div class="eg-eg-av" style="margin-top:0;width:42px;height:42px;flex-shrink:0;background:rgba(78,161,255,.1);border-color:rgba(78,161,255,.3)">
                    <?php if($clientIcon): ?><img src="<?= htmlspecialchars($clientIcon) ?>" alt=""><?php else: ?><?= $clientInit ?><?php endif; ?>
                </div>
                <div class="min-w-0 flex-grow-1">
                    <div style="font-weight:900;font-size:.95rem;color:rgba(255,255,255,.88)"><?= $clientName ?></div>
                    <div style="font-size:.75rem;color:rgba(255,255,255,.35)">Client</div>
                </div>
                <?php if(!empty($order['client_id'])): ?><a href="<?= ADMN_URL ?>/client/<?= (int)$order['client_id'] ?>" class="btn btn-white btn-xs"><i class="fa-solid fa-external-link"></i></a><?php endif; ?>
            </div>
        </div>

        <div class="card eg-card eg-side-card">
            <div class="card-header"><h5 class="eg-section-title">Order Actions</h5></div>
            <div class="card-body d-flex flex-column gap-3">
                <div class="eg-side-actions">
                    <form class="ajax-form" action="<?= AJAX_URL ?>"><input type="hidden" name="action" value="egirl_set_unpaid"><input type="hidden" name="order_id" value="<?= $id ?>"><button type="submit" class="btn btn-white btn-sm w-100">Set Unpaid</button></form>
                    <form class="ajax-form" action="<?= AJAX_URL ?>"><input type="hidden" name="action" value="egirl_set_paid"><input type="hidden" name="order_id" value="<?= $id ?>"><button type="submit" class="btn btn-white btn-sm w-100">Set Paid</button></form>
                    <form class="ajax-form" action="<?= AJAX_URL ?>"><input type="hidden" name="action" value="egirl_set_in_progress"><input type="hidden" name="order_id" value="<?= $id ?>"><button type="submit" class="btn btn-primary btn-sm w-100">In Progress</button></form>
                    <form class="ajax-form" action="<?= AJAX_URL ?>"><input type="hidden" name="action" value="egirl_set_completed"><input type="hidden" name="order_id" value="<?= $id ?>"><button type="submit" class="btn btn-success btn-sm w-100">Completed</button></form>
                    <form class="ajax-form" action="<?= AJAX_URL ?>"><input type="hidden" name="action" value="egirl_set_cancelled"><input type="hidden" name="order_id" value="<?= $id ?>"><button type="submit" class="btn btn-warning btn-sm w-100">Cancel</button></form>
                    <form class="ajax-form" action="<?= AJAX_URL ?>"><input type="hidden" name="action" value="egirl_set_refunded"><input type="hidden" name="order_id" value="<?= $id ?>"><button type="submit" class="btn btn-secondary btn-sm w-100">Refund</button></form>
                </div>
                <form class="ajax-form" action="<?= AJAX_URL ?>" onsubmit="return confirm('Delete this E-Girl order permanently?');">
                    <input type="hidden" name="action" value="egirl_delete_order">
                    <input type="hidden" name="order_id" value="<?= $id ?>">
                    <button type="submit" class="btn btn-danger btn-sm w-100"><i class="fa-solid fa-trash me-1"></i>Delete Order</button>
                </form>
            </div>
        </div>

    </div>
</div>

<?= $this->start('scripts') ?>
<script>
(function(){
    var AJAX = '<?= AJAX_URL ?>';
    var OID  = <?= $id ?>;
    var lastTime = <?= $lastMsgTime ?>;
    var body = document.getElementById('egChatBody');
    var inp  = document.getElementById('egAdminIn');
    var snd  = document.getElementById('egAdminSend');
    if(body) body.scrollTop = body.scrollHeight;

    function addMsg(text, cls, ts, icon, init, name, grp){
        var e = document.getElementById('egChatEmpty');if(e)e.remove();
        var isImg=/\.(jpg|jpeg|png|gif|webp)(\?|$)/i.test(text);
        var s = isImg
            ? '<a href="'+text+'" target="_blank" rel="noopener"><img src="'+text+'" style="max-width:200px;border-radius:8px;cursor:zoom-in" loading="lazy"></a>'
            : text.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/\n/g,'<br>');
        var av = icon?'<div class="eg-m-av '+cls+'"><img src="'+icon.replace(/"/g,'&quot;')+'" alt=""></div>'
                     :'<div class="eg-m-av '+cls+'">'+init+'</div>';
        var h = '<div class="eg-m '+cls+'">';
        if(!grp&&cls!=='sys') h+='<div class="eg-m-head">'+av+'<div class="eg-m-name">'+name+'</div></div>';
        h+='<div class="eg-m-bbl">'+s+'</div><div class="eg-m-ts">'+ts+'</div></div>';
        body.insertAdjacentHTML('beforeend',h);
        body.scrollTop=body.scrollHeight;
    }

    // Admin send via XHR (bypasses all jQuery hooks)
    function adminSend(){
        if(!inp||!snd)return;
        var txt=inp.value.trim();if(!txt)return;
        snd.disabled=true;
        var xhr=new XMLHttpRequest();
        xhr.open('POST',AJAX,true);
        xhr.setRequestHeader('Content-Type','application/x-www-form-urlencoded');
        xhr.onload=function(){
            inp.value='';inp.style.height='';
            snd.disabled=false;inp.focus();
            try{
                var r=JSON.parse(xhr.responseText);
                if(r&&r.success!==false&&txt){
                    var n=new Date(),t=String(n.getDate()).padStart(2,'0')+'.'
                        +String(n.getMonth()+1).padStart(2,'0')+'.'+n.getFullYear()
                        +' '+String(n.getHours()).padStart(2,'0')+':'+String(n.getMinutes()).padStart(2,'0');
                    addMsg(txt,'me',t,'','A','Admin',false);
                    if(r.time)lastTime=Math.max(lastTime,parseInt(r.time)||0);
                }
            }catch(e){}
        };
        xhr.onerror=function(){snd.disabled=false;};
        xhr.send('action=admin_egirl_chat_send&order_id='+OID+'&message='+encodeURIComponent(txt));
    }
    if(snd)snd.addEventListener('click',adminSend);
    if(inp){
        inp.addEventListener('keydown',function(e){if(e.key==='Enter'&&!e.shiftKey){e.preventDefault();adminSend();}});
        inp.addEventListener('input',function(){this.style.height='auto';this.style.height=Math.min(this.scrollHeight,88)+'px';});
    }

    // Poll every 3s (fallback only — chat_update pushes instantly when connected)
    var pS='',pSid=0;
    function pollEgirlMessages(){
        var xhr2=new XMLHttpRequest();
        xhr2.open('POST',AJAX,true);
        xhr2.setRequestHeader('Content-Type','application/x-www-form-urlencoded');
        xhr2.onload=function(){
            try{
                var r=JSON.parse(xhr2.responseText);
                if(r.messages&&r.messages.length){
                    r.messages.forEach(function(m){
                        var st=m.sender||'system';
                        var sid=parseInt(m.sender_id)||0;
                        var isEg=(st==='booster');
                        var isCl=(st==='client');
                        var grp=(st===pS&&sid===pSid);pS=st;pSid=sid;
                        var cls=isEg?'me':(isCl?'them':'sys');
                        var avCls=isEg?'mine':(isCl?'client-av':'');
                        var name=isEg?'<?= addslashes($egirlName) ?>':(isCl?'<?= addslashes($clientName) ?>':'System');
                        var icon=m.sender_icon||(isEg?'<?= addslashes($egirlIcon) ?>':(isCl?'<?= addslashes($clientIcon) ?>':''));
                        var init=isEg?'<?= $egirlInit ?>':(isCl?'<?= $clientInit ?>':'S');
                        var txt=m.raw||m.content||m.message||'';
                        var n=m.time?new Date(m.time*1000):new Date();
                        var t=String(n.getDate()).padStart(2,'0')+'.'
                            +String(n.getMonth()+1).padStart(2,'0')+'.'+n.getFullYear()
                            +' '+String(n.getHours()).padStart(2,'0')+':'+String(n.getMinutes()).padStart(2,'0');
                        addMsg(txt,cls,t,icon,init,name,grp);
                        lastTime=Math.max(lastTime,parseInt(m.time)||0);
                    });
                }
            }catch(e){}
        };
        xhr2.send('action=admin_egirl_chat_poll&order_id='+OID+'&after_time='+lastTime);
    }

    window.lbOrderViewChatUpdate = function (data) {
        if (!data || data.order_id === ('eg_' + OID)) {
            pollEgirlMessages();
        }
    };

    setInterval(function () {
        if (document.visibilityState !== 'visible') return;
        if (window.lbRealtimeConnected) return;
        pollEgirlMessages();
    }, 30000);

    setInterval(function () {
        if (document.visibilityState === 'visible' && window.lbRealtimeConnected) return;
        pollEgirlMessages();
    }, 60000);
})();
</script>
<?= $this->end() ?>

</div>
