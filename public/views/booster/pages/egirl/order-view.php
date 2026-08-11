<?= $this->layout('booster/layouts/main', ['meta' => $meta]) ?>

<style>
.eg-sp{display:inline-flex;align-items:center;gap:.38rem;padding:.26rem .82rem;border-radius:999px;font-size:.72rem;font-weight:800;letter-spacing:.06em;text-transform:uppercase;white-space:nowrap;}
.eg-sp::before{content:"";width:6px;height:6px;border-radius:50%;flex-shrink:0;}
.eg-sp.paid{background:rgba(168,85,247,.12);border:1px solid rgba(168,85,247,.3);color:#c084fc;}.eg-sp.paid::before{background:#a855f7;}
.eg-sp.in_progress{background:rgba(14,165,233,.1);border:1px solid rgba(14,165,233,.3);color:#38bdf8;}.eg-sp.in_progress::before{background:#0ea5e9;box-shadow:0 0 0 3px rgba(14,165,233,.2);}
.eg-sp.completed{background:rgba(34,197,94,.1);border:1px solid rgba(34,197,94,.3);color:#4ade80;}.eg-sp.completed::before{background:#22c55e;}
.eg-sp.unpaid{background:rgba(236,72,153,.1);border:1px solid rgba(236,72,153,.3);color:#f472b6;}.eg-sp.unpaid::before{background:#ec4899;}
.eg-sp.cancelled{background:rgba(245,202,153,.1);border:1px solid rgba(245,202,153,.3);color:#f5ca99;}.eg-sp.cancelled::before{background:#f5ca99;}
.eg-sp.refunded{background:rgba(148,163,184,.08);border:1px solid rgba(148,163,184,.2);color:#94a3b8;}.eg-sp.refunded::before{background:#94a3b8;}

.eg-head-card{border-radius:.75rem;overflow:hidden;margin-bottom:1.5rem;position:relative;}
.eg-head-cover{height:54px;background:linear-gradient(135deg,rgba(168,85,247,.35) 0%,rgba(236,72,153,.2) 60%,rgba(168,85,247,.1) 100%);position:relative;}
.eg-head-cover-tag{position:absolute;top:10px;left:16px;font-size:.68rem;font-weight:800;text-transform:uppercase;letter-spacing:.08em;color:rgba(255,255,255,.35);}
.eg-head-cover-av{position:absolute;bottom:-18px;left:16px;width:38px;height:38px;border-radius:50%;background:linear-gradient(135deg,rgba(168,85,247,.45),rgba(236,72,153,.3));border:2.5px solid rgba(12,10,22,.95);display:flex;align-items:center;justify-content:center;font-size:1rem;overflow:hidden;}
.eg-head-cover-av img{width:84%;height:84%;object-fit:contain;display:block;}
.eg-head-top{display:flex;align-items:center;justify-content:space-between;gap:1rem;padding:1.1rem 1.5rem 1.1rem 68px;flex-wrap:wrap;border-bottom:1px solid rgba(168,85,247,.1);}
.eg-head-left{display:flex;align-items:center;gap:.85rem;min-width:0;flex:1;}
.eg-head-h1{font-size:1.1rem;font-weight:900;color:rgba(255,255,255,.92);margin:0;line-height:1.25;}
.eg-head-id{font-size:.73rem;font-weight:600;color:rgba(255,255,255,.3);margin-left:.35rem;}
.eg-head-sub{margin-top:.2rem;display:flex;align-items:center;gap:.5rem;flex-wrap:wrap;}
.eg-head-actions{display:flex;align-items:center;gap:.45rem;}
.eg-head-meta{display:flex;align-items:center;gap:.45rem;flex-wrap:wrap;padding:.65rem 1.5rem;background:rgba(168,85,247,.03);}
.eg-mp{display:inline-flex;flex-direction:column;padding:.32rem .75rem;border-radius:8px;background:rgba(168,85,247,.07);border:1px solid rgba(168,85,247,.12);min-width:74px;}
.eg-mp .mpk{font-size:.6rem;font-weight:800;text-transform:uppercase;letter-spacing:.05em;color:rgba(255,255,255,.32);}
.eg-mp .mpv{font-size:.8rem;font-weight:700;color:rgba(255,255,255,.85);margin-top:.08rem;}
.eg-mp .mpv.g{color:#4ade80;}

.eg-ov-grid{display:grid;grid-template-columns:1fr 295px;gap:1.1rem;align-items:start;}
@media(max-width:991px){.eg-ov-grid{grid-template-columns:1fr;}}

.eg-abtn{display:inline-flex;align-items:center;gap:.4rem;padding:.48rem 1.05rem;border-radius:9px;font-weight:800;font-size:.82rem;border:none;cursor:pointer;white-space:nowrap;transition:opacity .15s;}
.eg-abtn:hover{opacity:.85;}.eg-abtn:disabled{opacity:.45;cursor:not-allowed;}
.eg-abtn.start{background:linear-gradient(135deg,#a855f7,#ec4899);color:#fff;}
.eg-abtn.finish{background:linear-gradient(135deg,#16a34a,#22c55e);color:#fff;box-shadow:0 4px 14px rgba(34,197,94,.28);}
.eg-abtn.pause{background:rgba(255,255,255,.06);color:#fff;border:1px solid rgba(255,255,255,.12);font-size:.75rem;padding:.4rem .85rem;}
.eg-abtn.poke{background:rgba(236,72,153,.12);color:#f9a8d4;border:1px solid rgba(236,72,153,.28);font-size:.75rem;padding:.4rem .85rem;}

.eg-abox{display:flex;align-items:center;justify-content:space-between;gap:1rem;flex-wrap:wrap;border-radius:.75rem;padding:.8rem 1.1rem;border:1px solid;margin-bottom:.9rem;}
.eg-abox.paid{background:rgba(168,85,247,.07);border-color:rgba(168,85,247,.28);}
.eg-abox.in_progress{background:rgba(34,197,94,.05);border-color:rgba(34,197,94,.28);}
.eg-abox.completed{background:rgba(34,197,94,.03);border-color:rgba(34,197,94,.16);}
.eg-abox .abt{font-size:.87rem;font-weight:800;color:rgba(255,255,255,.9);margin-bottom:.12rem;}
.eg-abox .abs{font-size:.73rem;color:rgba(255,255,255,.42);}


/* Chat */
.eg-chat-outer{display:flex;flex-direction:column;overflow:hidden;border-radius:0 0 .75rem .75rem;}
.eg-chat-body{height:clamp(270px,42vh,420px);overflow-y:auto;padding:1rem 1.15rem;display:flex;flex-direction:column;gap:9px;background:linear-gradient(180deg,rgba(168,85,247,.035),rgba(255,255,255,.025));scrollbar-width:thin;scrollbar-color:rgba(168,85,247,.2) transparent;}
.eg-chat-body::-webkit-scrollbar{width:3px;}.eg-chat-body::-webkit-scrollbar-thumb{background:rgba(168,85,247,.28);border-radius:2px;}
.eg-m{display:flex;flex-direction:column;max-width:78%;}
.eg-m.me{align-self:flex-end;}.eg-m.them{align-self:flex-start;}
.eg-m-head{display:flex;align-items:center;gap:7px;margin-bottom:4px;}
.eg-m.me .eg-m-head{flex-direction:row-reverse;}
.eg-m-av{width:32px;height:32px;border-radius:50%;flex-shrink:0;overflow:hidden;border:1.5px solid rgba(168,85,247,.3);background:rgba(168,85,247,.1);display:flex;align-items:center;justify-content:center;font-size:.7rem;font-weight:800;color:#c084fc;}
.eg-m-av img{width:100%;height:100%;object-fit:cover;border-radius:50%;}
.eg-m-av.mine{border-color:rgba(236,72,153,.4);}
.eg-m-name{font-size:.72rem;font-weight:800;color:rgba(255,255,255,.4);display:flex;align-items:center;gap:.38rem;flex-wrap:wrap;}
.eg-m.me .eg-m-name{color:rgba(244,114,182,.55);}
.eg-role-badge{display:inline-flex;align-items:center;justify-content:center;padding:.12rem .46rem;border-radius:999px;font-size:.56rem;font-weight:900;letter-spacing:.08em;text-transform:uppercase;border:1px solid rgba(255,255,255,.10);line-height:1.1}
.eg-role-admin{color:#ff8f8f;background:rgba(255,107,107,.10);border-color:rgba(255,107,107,.24);}
.eg-role-client{color:#7dc7ff;background:rgba(78,161,255,.10);border-color:rgba(78,161,255,.24);}
.eg-role-egirl{color:#f7a8d3;background:rgba(244,114,182,.12);border-color:rgba(244,114,182,.24);}
.eg-m-bbl{padding:.5rem .78rem;border-radius:11px;font-size:.83rem;line-height:1.5;word-break:break-word;color:rgba(255,255,255,.88);}
.eg-m.them .eg-m-bbl{background:rgba(168,85,247,.12);border:1px solid rgba(168,85,247,.18);border-top-left-radius:3px;}
.eg-m.me .eg-m-bbl{background:linear-gradient(135deg,rgba(168,85,247,.27),rgba(236,72,153,.2));border:1px solid rgba(168,85,247,.28);border-top-right-radius:3px;}
.eg-m-ts{font-size:.62rem;color:rgba(255,255,255,.28);margin-top:.18rem;}
.eg-m.me .eg-m-ts{text-align:right;}
.eg-chat-empty{flex:1;display:flex;flex-direction:column;align-items:center;justify-content:center;gap:.45rem;color:rgba(255,255,255,.28);}
.eg-chat-empty i{font-size:1.8rem;color:rgba(168,85,247,.22);}
.eg-chat-footer{display:flex;gap:.5rem;align-items:flex-end;padding:.6rem .9rem;border-top:1px solid rgba(168,85,247,.09);background:rgba(168,85,247,.02);flex-direction:column;gap:.4rem;position:relative;}
.eg-chat-ta{flex:1;resize:none;max-height:88px;min-height:36px;background:rgba(168,85,247,.07);border:1px solid rgba(168,85,247,.15);border-radius:9px;padding:.43rem .73rem;color:rgba(255,255,255,.88);font-size:.83rem;line-height:1.4;outline:none;font-family:inherit;transition:border-color .15s;}
.eg-chat-ta:focus{border-color:rgba(168,85,247,.42);}
.eg-chat-ta::placeholder{color:rgba(255,255,255,.22);}
.eg-chat-ta:disabled{opacity:.33;}
.eg-chat-send{width:36px;height:36px;border-radius:9px;flex-shrink:0;background:linear-gradient(135deg,#a855f7,#ec4899);border:none;color:#fff;cursor:pointer;display:flex;align-items:center;justify-content:center;font-size:.84rem;transition:opacity .15s;}
.eg-chat-send:hover{opacity:.85;}.eg-chat-send:disabled{opacity:.33;cursor:not-allowed;}
.eg-mc{font-size:.66rem;font-weight:700;padding:.13rem .47rem;border-radius:999px;background:rgba(168,85,247,.1);border:1px solid rgba(168,85,247,.2);color:#c084fc;}
.eg-emoji{background:transparent!important;border:none!important;padding:5px;border-radius:8px;cursor:pointer;font-size:20px;line-height:1;transition:background .1s;}
.eg-emoji:hover{background:rgba(168,85,247,.15)!important;}
#egEmojiPicker button{box-shadow:none!important;outline:none!important;}
.eg-chat-img{max-width:180px;border-radius:8px;cursor:zoom-in;display:block;}
#chatImageModal .modal-content{background:rgba(12,10,22,.98);border:1px solid rgba(168,85,247,.2);}
#chatImageModal .modal-header{border-bottom:1px solid rgba(255,255,255,.08);}
#chatImageModal .modal-body{padding:1rem;display:flex;align-items:center;justify-content:center;min-height:220px;}
#chatImageModalImg{max-width:100%;max-height:78vh;border-radius:12px;display:block;box-shadow:0 16px 50px rgba(0,0,0,.45);}

.eg-sidebar{display:flex;flex-direction:column;gap:.8rem;}
.eg-ov-list{list-style:none;margin:0;padding:0;display:flex;flex-direction:column;}

.eg-booking-info-title{display:flex;align-items:center;gap:.45rem;font-weight:900;color:#67e8f9;margin-bottom:.45rem}
.eg-booking-info-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(160px,1fr));gap:.45rem .9rem}
.eg-booking-info-item{display:flex;justify-content:space-between;gap:.75rem;border-bottom:1px solid rgba(255,255,255,.06);padding:.22rem 0}
.eg-booking-info-item span:first-child{font-size:.68rem;font-weight:900;text-transform:uppercase;letter-spacing:.05em;color:rgba(255,255,255,.42)}
.eg-booking-info-item span:last-child{font-weight:800;color:rgba(255,255,255,.9);text-align:right}
.eg-voice-order-box{background:linear-gradient(135deg,rgba(79,70,229,.12),rgba(168,85,247,.08));border:1px solid rgba(99,102,241,.34);border-radius:13px;padding:1rem 1.15rem;color:rgba(255,255,255,.78);}
.eg-voice-order-head{display:flex;align-items:center;gap:.55rem;font-weight:900;color:#fff;font-size:.95rem;margin-bottom:.65rem}
.eg-voice-order-head i{font-size:1.15rem;color:#6366f1}
.eg-voice-order-content{display:flex;align-items:center;justify-content:space-between;gap:1rem;flex-wrap:wrap}
.eg-voice-order-steps{display:flex;flex-direction:column;gap:.42rem;margin:0;padding:0;list-style:none;font-size:.83rem;line-height:1.35}
.eg-voice-order-step{display:flex;align-items:center;gap:.55rem}
.eg-voice-order-step-num{width:20px;height:20px;border-radius:50%;display:inline-flex;align-items:center;justify-content:center;flex:0 0 20px;background:rgba(99,102,241,.28);border:1px solid rgba(129,140,248,.38);color:#dbeafe;font-size:.72rem;font-weight:900}
.eg-voice-order-step.warn{color:#fecaca}
.eg-voice-order-step.warn .eg-voice-order-step-num{background:rgba(239,68,68,.16);border-color:rgba(248,113,113,.38);color:#fecaca}
.eg-voice-order-steps a{color:#c084fc;font-weight:900;text-decoration:none}
.eg-voice-order-btn{display:inline-flex;align-items:center;justify-content:center;gap:.4rem;border:0;border-radius:.8rem;background:#6366f1;color:#fff;font-weight:900;padding:.75rem 1.2rem;text-decoration:none;box-shadow:0 10px 24px rgba(99,102,241,.24)}
.eg-voice-order-btn:hover{color:#fff;opacity:.92}
.eg-game-icon{width:16px;height:16px;object-fit:contain;vertical-align:-3px;margin-right:.35rem}
.eg-ov-item{display:grid;grid-template-columns:1.4rem 1fr auto;align-items:center;gap:.5rem;padding:.58rem 0;border-bottom:1px solid rgba(168,85,247,.07);}
.eg-ov-item:last-child{border-bottom:0;}
.eg-ov-ico{font-size:.88rem;text-align:center;color:rgba(168,85,247,.5);}
.eg-ov-lbl{font-size:.73rem;font-weight:700;text-transform:uppercase;letter-spacing:.04em;color:rgba(255,255,255,.36);}
.eg-ov-val{font-weight:700;font-size:.83rem;text-align:right;color:rgba(255,255,255,.82);}
.eg-cli-card{background:rgba(168,85,247,.04);border:1px solid rgba(168,85,247,.12);border-radius:12px;overflow:hidden;}
.eg-cli-banner{height:48px;background:linear-gradient(135deg,rgba(168,85,247,.4),rgba(236,72,153,.22));}
.eg-cli-body{padding:0 .9rem .85rem;}
.eg-cli-av{width:44px;height:44px;border-radius:50%;margin-top:-22px;border:2.5px solid rgba(10,8,18,.9);background:linear-gradient(135deg,rgba(168,85,247,.3),rgba(236,72,153,.2));display:flex;align-items:center;justify-content:center;font-size:.95rem;font-weight:800;color:#c084fc;overflow:hidden;}
.eg-cli-av img{width:100%;height:100%;object-fit:cover;border-radius:50%;}
.eg-voice-badge{display:inline-flex;align-items:center;gap:.28rem;padding:.18rem .55rem;border-radius:999px;font-size:.7rem;font-weight:700;}
.eg-voice-yes{background:rgba(34,197,94,.1);border:1px solid rgba(34,197,94,.25);color:#4ade80;}
.eg-voice-no{background:rgba(255,255,255,.05);border:1px solid rgba(255,255,255,.09);color:rgba(255,255,255,.3);}
</style>

<?php
$order        = $order ?? [];
$messages     = $messages ?? [];
$id           = (int)($order['id'] ?? 0);
$statusRaw    = strtoupper($order['status'] ?? 'UNPAID');
$statusKey    = strtolower(str_replace(' ', '_', $statusRaw));
$hasClient    = !empty($order['client_username']) && $statusRaw !== 'UNPAID';
$clientInit   = $hasClient ? strtoupper(substr(preg_replace('/[^A-Za-z0-9]/', '', $order['client_username'] ?? 'C') ?: 'C', 0, 1)) : '';
$egirlName    = $order['egirl_username'] ?? $order['egirl_name'] ?? BOOSTER_DATA['username'] ?? 'E-Girl';
$egirlIcon    = $order['egirl_icon'] ?? $order['booster_icon'] ?? BOOSTER_DATA['icon'] ?? '';
$egirlInit    = strtoupper(substr(preg_replace('/[^A-Za-z0-9]/', '', $egirlName) ?: 'E', 0, 1));
$myBoosterId  = (int)(BOOSTER_ID ?? 0);
$myName       = BOOSTER_DATA['username'] ?? 'Me';
$myIcon       = BOOSTER_DATA['icon'] ?? '';
$myInit       = strtoupper(substr(preg_replace('/[^A-Za-z0-9]/', '', $myName) ?: 'E', 0, 1));
$isChatLocked = in_array($statusRaw, ['COMPLETED', 'CANCELLED', 'REFUNDED', 'UNPAID']);
$lastMsgTime  = !empty($messages) ? (int)(end($messages)['time'] ?? 0) : 0;
$hasVoice     = !empty($order['has_voice_chat']) || !empty($order['voice_chat']);
$svcNameRaw   = (string)($order['service_title'] ?? $order['service_type'] ?? 'GGirl Order');
$svcName      = htmlspecialchars($svcNameRaw);
$gameName     = !empty($order['game']) ? strtoupper(htmlspecialchars($order['game'])) : '';
$rawClientNotes = (string)($order['client_notes'] ?? '');
$bookingInfo = [];
if ($rawClientNotes !== '') {
    $notesBeforeData = preg_split('/\bDATA\s*:/i', $rawClientNotes, 2)[0] ?? $rawClientNotes;
    if (preg_match_all('/(?:^|\n)\s*([^:\n]+)\s*:\s*([^\n]+)/', $notesBeforeData, $matches, PREG_SET_ORDER)) {
        foreach ($matches as $match) {
            $key = trim((string)$match[1]);
            $value = trim((string)$match[2]);
            if ($key !== '' && $value !== '') {
                $bookingInfo[strtolower($key)] = $value;
            }
        }
    }
    if (preg_match('/\bDATA\s*:\s*(\{.*\})\s*$/is', $rawClientNotes, $jsonMatch)) {
        $jsonData = json_decode($jsonMatch[1], true);
        if (is_array($jsonData)) {
            $map = [
                'mode_title' => 'mode',
                'server' => 'server',
                'rank_label' => 'rank',
                'rank' => 'rank',
                'amount' => 'amount',
                'assignment' => 'assignment',
                'egirl_name' => 'assignment',
            ];
            foreach ($map as $jsonKey => $infoKey) {
                if (!empty($jsonData[$jsonKey]) && empty($bookingInfo[$infoKey])) {
                    $bookingInfo[$infoKey] = (string)$jsonData[$jsonKey];
                }
            }
        }
    }
}
$bookingMode = $bookingInfo['mode'] ?? ($order['mode_title'] ?? $order['service_title'] ?? 'Session');
$bookingServer = $bookingInfo['server'] ?? ($order['server'] ?? '—');
$bookingRank = $bookingInfo['rank'] ?? ($order['rank_label'] ?? $order['rank'] ?? '—');
$bookingAmount = $bookingInfo['amount'] ?? ($order['amount'] ?? $order['unit_value'] ?? '—');
if (is_numeric($bookingAmount)) {
    $bookingAmount = (int)$bookingAmount . ' ' . ((int)$bookingAmount === 1 ? 'game' : 'games');
}
$formatEgirlOrderTitle = static function(string $server, string $mode, string $amountText): string {
    $server = strtoupper(trim($server));
    $mode = trim(preg_replace('/\s+/', ' ', $mode));
    $mode = preg_replace('/\s+Game$/i', '', $mode);
    $amountText = trim(preg_replace('/\s+/', ' ', $amountText));
    $amountText = preg_replace_callback('/\b(game|games)\b/i', static function($m) { return ucfirst(strtolower($m[1])); }, $amountText);
    $parts = array_values(array_filter([$server !== '' && $server !== '—' ? $server : null, $mode !== '' ? $mode : null, $amountText !== '' && $amountText !== '—' ? $amountText : null]));
    return implode(' - ', $parts);
};
$orderDisplayTitle = $formatEgirlOrderTitle((string)$bookingServer, (string)$bookingMode, (string)$bookingAmount);
if ($orderDisplayTitle === '') $orderDisplayTitle = $svcNameRaw;
$svcName = htmlspecialchars($orderDisplayTitle);
$bookingAssignment = $bookingInfo['assignment'] ?? ($order['egirl_name'] ?? 'Any Available');
$unitValueRaw = (float)($order['unit_value'] ?? 1);
$unitTypeRaw  = strtolower((string)($order['unit_type'] ?? 'hours'));
$durationSeconds = (int)round(
    in_array($unitTypeRaw, ['minute','minutes','min','mins'], true) ? $unitValueRaw * 60
    : (in_array($unitTypeRaw, ['day','days'], true) ? $unitValueRaw * 86400 : $unitValueRaw * 3600)
);
$sessionEndAtRaw = $order['session_end_at'] ?? null;
if (!empty($order['is_paused'])) {
    $remainingSeconds = (int)($order['paused_remaining_seconds'] ?? $durationSeconds);
} else {
    $remainingSeconds = !empty($sessionEndAtRaw) ? max(0, strtotime($sessionEndAtRaw) - time()) : $durationSeconds;
}
$bookedDateTime = !empty($order['created_at']) ? date('d.m.Y · H:i', strtotime($order['created_at'])) : '—';
$orderCurrency  = strtoupper((string)($order['currency'] ?? 'EUR'));
$orderSymbol    = $orderCurrency === 'USD' ? '$' : '€';
$priceCents     = (int)($order['price'] ?? $order['price_cents'] ?? 0);
$cutPct         = (float)($order['egirl_cut'] ?? 0);
$priceEurCents  = (int)($order['price_eur'] ?? 0);
if ($priceEurCents <= 0) {
    $priceEurCents = $priceCents;
    if ($orderCurrency === 'USD') {
        $_rate = (float)(function_exists('get_exchange_rate') ? get_exchange_rate() : 0);
        if ($_rate > 0) $priceEurCents = (int)round($priceEurCents / $_rate);
    }
}
$cutEurCents   = $cutPct > 0 ? (int)floor($priceEurCents * $cutPct / 100) : 0;
$formatEurMoney = fn(int $c) => '€' . number_format($c / 100, 2);
?>

<!-- Header Card -->
<div class="card eg-head-card">
    <div class="eg-head-cover">
        <span class="eg-head-cover-tag">GG-Girl Order</span>
        <div class="eg-head-cover-av"><img src="<?= ASSET_URL ?>/website/images/gg-girl.svg" alt=""></div>
    </div>
    <div class="eg-head-top">
        <div class="eg-head-left">
            <div>
                <h1 class="eg-head-h1"><?= $svcName ?><span class="eg-head-id">#<?= $id ?></span></h1>
                <div class="eg-head-sub">
                    <span class="eg-sp <?= $statusKey ?>"><?= $statusRaw ?></span>
                    <?php if ($gameName): ?><span style="font-size:.7rem;color:rgba(255,255,255,.32);font-weight:600"><?= $gameName ?></span><?php endif; ?>
                </div>
            </div>
        </div>
        <div class="eg-head-actions">
            <?php if ($hasClient): ?>
                <button type="button" class="eg-abtn poke js-egirl-poke-client"><i class="fa-duotone fa-hand-point-up"></i>Poke Client</button>
            <?php endif; ?>
            <?php if ($statusKey === 'paid'): ?>
                <button type="button" class="eg-abtn start" id="btnAccept"><i class="fa-solid fa-play"></i>Start Order</button>
            <?php elseif ($statusKey === 'in_progress'): ?>
                <button type="button" class="eg-abtn pause" id="btnPauseSession"><i class="fa-solid fa-pause" style="font-size:.7rem"></i><span id="btnPauseLabel">Pause</span></button>
                <button type="button" class="eg-abtn finish" id="btnComplete"><i class="fa-solid fa-check"></i>Complete Order</button>
            <?php endif; ?>
        </div>
    </div>
    <div class="eg-head-meta">
        <div class="eg-mp"><span class="mpk">Order</span><span class="mpv">#<?= $id ?></span></div>
        <?php if ($cutEurCents > 0): ?><div class="eg-mp"><span class="mpk">Earning</span><span class="mpv g"><?= $formatEurMoney($cutEurCents) ?></span></div><?php endif; ?>
        <?php if ($hasClient): ?><div class="eg-mp"><span class="mpk">Client</span><span class="mpv" style="font-size:.76rem"><?= htmlspecialchars($order['client_username']) ?></span></div><?php endif; ?>
        <?php if (!empty($order['created_at'])): ?><div class="eg-mp"><span class="mpk">Booked</span><span class="mpv" style="font-size:.73rem"><?= $bookedDateTime ?></span></div><?php endif; ?>
    </div>
</div>

<div class="eg-ov-grid">
    <!-- Main column -->
    <div class="d-flex flex-column gap-3">

        <?php if ($statusKey !== 'unpaid'): ?>
        <!-- Voice Order Instructions -->
        <div class="eg-voice-order-box">
            <div class="eg-voice-order-head"><i class="fa-brands fa-discord"></i>Voice Order</div>
            <div class="eg-voice-order-content">
                <ol class="eg-voice-order-steps">
                    <li class="eg-voice-order-step"><span class="eg-voice-order-step-num">1</span><span>Join server</span></li>
                    <li class="eg-voice-order-step"><span class="eg-voice-order-step-num">2</span><span>Send customer invite, <a href="https://lolboost.gg/streaming" target="_blank" rel="noopener">lolboost.gg/streaming</a></span></li>
                    <li class="eg-voice-order-step"><span class="eg-voice-order-step-num">3</span><span>Complete order with voice</span></li>
                    <li class="eg-voice-order-step warn"><span class="eg-voice-order-step-num">4</span><span>No Discord DM, no adding, website chat only</span></li>
                </ol>
                <a class="eg-voice-order-btn" href="https://lolboost.gg/streaming" target="_blank" rel="noopener"><i class="fa-brands fa-discord"></i>Join Server</a>
            </div>
        </div>

        <!-- Chat Card -->
        <div class="card" style="overflow:hidden">
            <div class="card-header d-flex align-items-center justify-content-between" style="padding:.68rem 1.1rem">
                <h5 class="card-header-title mb-0" style="font-size:.86rem"><i class="fa-duotone fa-messages me-2" style="color:#a855f7"></i>Order Chat</h5>
                <span class="eg-mc" id="chatMsgCount"><?= count($messages) ?> msg<?= count($messages) !== 1 ? 's' : '' ?></span>
            </div>
            <div class="eg-chat-outer">
                <!-- Messages -->
                <div class="eg-chat-body" id="egChatBody">
                    <?php if (empty($messages)): ?>
                    <div class="eg-chat-empty" id="egChatEmpty"><i class="fa-duotone fa-messages"></i><span style="font-size:.8rem">No messages yet — say hi! 👋</span></div>
                    <?php else:
                        $lSid = 0; $lStype = '';
                        foreach ($messages as $msg):
                            $sSt   = $msg['sender'] ?? ($msg['sender_type'] ?? 'client');
                            $sSid  = (int)($msg['sender_id'] ?? 0);
                            $mine  = ($sSt === 'booster' && $sSid === $myBoosterId);
                            $grp   = ($sSid === $lSid && $sSt === $lStype);
                            $lSid  = $sSid; $lStype = $sSt;
                            $role  = strtolower((string)$sSt);
                            if ($mine) {
                                $sN = 'You'; $sI = $myIcon; $sX = $myInit; $roleLabel = 'GG-Girl'; $roleClass = 'eg-role-egirl';
                            } elseif ($role === 'admin' || $role === 'administrator') {
                                $sN = $msg['sender_name'] ?? 'Admin'; $sI = $msg['sender_icon'] ?? ''; $sX = 'A'; $roleLabel = 'Admin'; $roleClass = 'eg-role-admin';
                            } else {
                                $sN = $msg['sender_name'] ?? ($order['client_username'] ?? 'Client');
                                $sI = $msg['sender_icon'] ?? ($order['client_icon'] ?? '');
                                $sX = strtoupper(substr(preg_replace('/[^A-Za-z0-9]/', '', $sN) ?: 'C', 0, 1));
                                $roleLabel = 'Client'; $roleClass = 'eg-role-client';
                            }
                            $ts     = !empty($msg['time']) ? date('H:i', (int)$msg['time']) : '';
                            $rawMsg = $msg['raw'] ?? strip_tags($msg['content'] ?? '');
                            $isImg  = ($msg['type'] ?? '') === 'image' || preg_match('/\.(jpg|jpeg|png|gif|webp)(\?|$)/i', $rawMsg);
                    ?>
                    <div class="eg-m <?= $mine ? 'me' : 'them' ?>">
                        <?php if (!$grp): ?><div class="eg-m-head"><div class="eg-m-av <?= $mine ? 'mine' : '' ?>"><?php if ($sI): ?><img src="<?= htmlspecialchars($sI) ?>" alt=""><?php else: ?><?= $sX ?><?php endif; ?></div><div class="eg-m-name"><?= htmlspecialchars($sN) ?><span class="eg-role-badge <?= $roleClass ?>"><?= $roleLabel ?></span></div></div><?php endif; ?>
                        <div class="eg-m-bbl"><?php if ($isImg): ?><img src="<?= htmlspecialchars($rawMsg) ?>" class="eg-chat-img" data-chat-image="<?= htmlspecialchars($rawMsg) ?>" loading="lazy" alt="Chat image"><?php else: ?><?= nl2br(htmlspecialchars($rawMsg)) ?><?php endif; ?></div>
                        <div class="eg-m-ts"><?= $ts ?></div>
                    </div>
                    <?php endforeach; endif; ?>
                </div>

                <?php if (!$isChatLocked): ?>
                <!-- Chat Footer — NO <form> tag, pure XHR like client/booster views -->
                <div class="eg-chat-footer">
                    <!-- Image preview -->
                    <div class="d-none" id="egImgPreviewWrap" style="padding:.2rem 0">
                        <div style="position:relative;display:inline-block;max-width:130px;border-radius:9px;overflow:hidden;border:1px solid rgba(168,85,247,.25);">
                            <img id="egImgPreview" src="" alt="" style="width:130px;height:82px;object-fit:cover;display:block;">
                            <button type="button" id="egImgRemove" style="position:absolute;top:4px;right:4px;width:20px;height:20px;border-radius:50%;background:rgba(0,0,0,.75);border:none;color:#fff;font-size:.62rem;cursor:pointer;display:flex;align-items:center;justify-content:center;"><i class="fa-solid fa-xmark"></i></button>
                        </div>
                    </div>
                    <!-- Input row -->
                    <div style="display:flex;gap:.45rem;align-items:flex-end;width:100%">
                        <textarea class="eg-chat-ta" id="egChatIn" rows="1" placeholder="Type a message… (Enter to send)"></textarea>
                        <button type="button" id="egEmojiBtn" title="Emoji" style="width:34px;height:34px;border-radius:8px;flex-shrink:0;background:rgba(255,255,255,.06);border:1px solid rgba(255,255,255,.1);color:rgba(255,255,255,.5);cursor:pointer;display:flex;align-items:center;justify-content:center;font-size:.82rem;">
                            <i class="fa-regular fa-face-smile"></i>
                        </button>
                        <button type="button" id="egUploadBtn" title="Upload image" style="width:34px;height:34px;border-radius:8px;flex-shrink:0;background:rgba(255,255,255,.06);border:1px solid rgba(255,255,255,.1);color:rgba(255,255,255,.5);cursor:pointer;display:flex;align-items:center;justify-content:center;font-size:.82rem;">
                            <i class="fa-duotone fa-paperclip"></i>
                        </button>
                        <input type="file" id="egFileInput" accept="image/*" class="d-none">
                        <button type="button" class="eg-chat-send" id="egChatSend"><i class="fa-solid fa-paper-plane-top"></i></button>
                    </div>
                    <!-- Emoji picker -->
                    <div id="egEmojiPicker" class="d-none" style="position:absolute;bottom:72px;right:14px;z-index:1075;background:rgba(22,16,38,.98);border:1px solid rgba(168,85,247,.22);border-radius:13px;padding:9px;width:274px;max-width:calc(100vw - 28px);box-shadow:0 20px 55px rgba(0,0,0,.55);display:flex;flex-wrap:wrap;gap:5px;">
<button type="button" class="eg-emoji" data-emoji="😀">😀</button><button type="button" class="eg-emoji" data-emoji="😁">😁</button><button type="button" class="eg-emoji" data-emoji="😂">😂</button><button type="button" class="eg-emoji" data-emoji="🤣">🤣</button><button type="button" class="eg-emoji" data-emoji="😊">😊</button><button type="button" class="eg-emoji" data-emoji="😉">😉</button><button type="button" class="eg-emoji" data-emoji="😍">😍</button><button type="button" class="eg-emoji" data-emoji="😘">😘</button><button type="button" class="eg-emoji" data-emoji="😎">😎</button><button type="button" class="eg-emoji" data-emoji="🤔">🤔</button><button type="button" class="eg-emoji" data-emoji="😴">😴</button><button type="button" class="eg-emoji" data-emoji="😭">😭</button><button type="button" class="eg-emoji" data-emoji="😡">😡</button><button type="button" class="eg-emoji" data-emoji="👍">👍</button><button type="button" class="eg-emoji" data-emoji="👎">👎</button><button type="button" class="eg-emoji" data-emoji="🙏">🙏</button><button type="button" class="eg-emoji" data-emoji="🙌">🙌</button><button type="button" class="eg-emoji" data-emoji="👏">👏</button><button type="button" class="eg-emoji" data-emoji="🎉">🎉</button><button type="button" class="eg-emoji" data-emoji="🔥">🔥</button><button type="button" class="eg-emoji" data-emoji="💯">💯</button><button type="button" class="eg-emoji" data-emoji="✅">✅</button><button type="button" class="eg-emoji" data-emoji="❌">❌</button><button type="button" class="eg-emoji" data-emoji="⚡">⚡</button><button type="button" class="eg-emoji" data-emoji="⭐">⭐</button><button type="button" class="eg-emoji" data-emoji="💙">💙</button><button type="button" class="eg-emoji" data-emoji="💚">💚</button><button type="button" class="eg-emoji" data-emoji="💛">💛</button><button type="button" class="eg-emoji" data-emoji="💜">💜</button><button type="button" class="eg-emoji" data-emoji="🫡">🫡</button><button type="button" class="eg-emoji" data-emoji="🤝">🤝</button><button type="button" class="eg-emoji" data-emoji="🥳">🥳</button><button type="button" class="eg-emoji" data-emoji="❤">❤</button><button type="button" class="eg-emoji" data-emoji="🎮">🎮</button><button type="button" class="eg-emoji" data-emoji="💪">💪</button><button type="button" class="eg-emoji" data-emoji="🏆">🏆</button><button type="button" class="eg-emoji" data-emoji="👑">👑</button>
                    </div>
                </div>
                <?php else: ?>
                <div style="padding:.65rem 1rem;border-top:1px solid rgba(168,85,247,.09);background:rgba(168,85,247,.02);font-size:.76rem;color:rgba(255,255,255,.3);text-align:center;">
                    <i class="fa-solid fa-lock me-1" style="font-size:.65rem"></i>Chat locked — session <?= strtolower($statusRaw) ?>
                </div>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- Order Details -->
        <div class="card">
            <div class="card-header" style="padding:.68rem 1.1rem"><h5 class="card-header-title mb-0" style="font-size:.86rem"><i class="fa-duotone fa-scroll me-2" style="color:#a855f7"></i>Order Details</h5></div>
            <div class="card-body" style="padding:.7rem 1.1rem">
                <ul class="eg-ov-list">
                    <li class="eg-ov-item"><span class="eg-ov-ico"><i class="fa-solid fa-layer-group"></i></span><span class="eg-ov-lbl">Order</span><span class="eg-ov-val"><?= $svcName ?></span></li>
                    <?php if ($gameName): ?><li class="eg-ov-item"><span class="eg-ov-ico"><img class="eg-game-icon" src="<?= ASSET_URL ?>/website/images/icons/league-of-legends.png" alt="League of Legends"></span><span class="eg-ov-lbl">Game</span><span class="eg-ov-val">League of Legends</span></li><?php endif; ?>
                    <?php if ($cutEurCents > 0): ?><li class="eg-ov-item"><span class="eg-ov-ico"><i class="fa-solid fa-hand-holding-dollar"></i></span><span class="eg-ov-lbl">Earning</span><span class="eg-ov-val" style="color:#4ade80;font-weight:900"><?= $formatEurMoney($cutEurCents) ?></span></li><?php endif; ?>
                    <li class="eg-ov-item"><span class="eg-ov-ico"><i class="fa-solid fa-calendar"></i></span><span class="eg-ov-lbl">Booked</span><span class="eg-ov-val" style="font-size:.76rem;color:rgba(255,255,255,.45)"><?= !empty($order['created_at']) ? date('d.m.Y · H:i', strtotime($order['created_at'])) : '—' ?></span></li>
                    <?php if (!empty($order['claimed_at'])): ?><li class="eg-ov-item"><span class="eg-ov-ico"><i class="fa-solid fa-play"></i></span><span class="eg-ov-lbl">Started</span><span class="eg-ov-val" style="font-size:.76rem;color:rgba(255,255,255,.45)"><?= date('d.m.Y · H:i', strtotime($order['claimed_at'])) ?></span></li><?php endif; ?>
                    <?php if (!empty($order['completed_at'])): ?><li class="eg-ov-item"><span class="eg-ov-ico"><i class="fa-solid fa-flag-checkered"></i></span><span class="eg-ov-lbl">Finished</span><span class="eg-ov-val" style="font-size:.76rem;color:#4ade80"><?= date('d.m.Y · H:i', strtotime($order['completed_at'])) ?></span></li><?php endif; ?>
                </ul>
            </div>
        </div>
    </div>

    <!-- Sidebar -->
    <div class="eg-sidebar">
        <?php if ($statusKey === 'paid'): ?>
        <div class="eg-abox paid"><div><div class="abt"><i class="fa-duotone fa-rocket-launch me-2" style="color:#a855f7"></i>New Booking</div><div class="abs">Accept to begin the order.</div></div><button type="button" class="eg-abtn start" id="btnAcceptSide"><i class="fa-solid fa-play"></i>Start</button></div>
        <?php elseif ($statusKey === 'in_progress'): ?>
        <div class="eg-abox in_progress"><div><div class="abt"><i class="fa-duotone fa-circle-check me-2" style="color:#22c55e"></i>In Progress</div><div class="abs">Mark done when the booked game is finished.</div></div><button type="button" class="eg-abtn finish" id="btnCompleteSide"><i class="fa-solid fa-check"></i>Complete</button></div>
        <?php elseif ($statusKey === 'completed'): ?>
        <div class="eg-abox completed"><div><div class="abt" style="color:#4ade80"><i class="fa-duotone fa-circle-check me-2"></i>Completed</div><div class="abs">Earnings credited to balance.</div></div></div>
        <?php endif; ?>

        <!-- Client Card -->
        <div class="eg-cli-card">
            <div class="eg-cli-banner"></div>
            <div class="eg-cli-body">
                <?php if ($hasClient): ?>
                <div class="eg-cli-av"><?php if (!empty($order['client_icon'])): ?><img src="<?= htmlspecialchars($order['client_icon']) ?>" alt=""><?php else: ?><?= $clientInit ?><?php endif; ?></div>
                <div style="margin-top:.38rem;font-weight:800;font-size:.87rem;color:rgba(255,255,255,.9)"><?= htmlspecialchars($order['client_username']) ?></div>
                <div style="font-size:.67rem;color:rgba(255,255,255,.3);margin-top:.08rem">Client</div>
                <?php else: ?>
                <div class="eg-cli-av" style="opacity:.3">—</div>
                <div style="margin-top:.35rem;font-size:.76rem;color:rgba(255,255,255,.28)"><?= $statusRaw === 'UNPAID' ? 'Awaiting payment' : 'No client' ?></div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Summary Card -->
        <div class="card">
            <div class="card-header" style="padding:.62rem .95rem"><h5 class="card-header-title mb-0" style="font-size:.8rem"><i class="fa-duotone fa-receipt me-2" style="color:#a855f7"></i>Summary</h5></div>
            <div class="card-body" style="padding:.55rem .95rem">
                <ul class="eg-ov-list">
                    <li class="eg-ov-item"><span class="eg-ov-ico"><i class="fa-solid fa-hashtag"></i></span><span class="eg-ov-lbl">ID</span><span class="eg-ov-val">#<?= $id ?></span></li>
                    <li class="eg-ov-item"><span class="eg-ov-ico"><i class="fa-solid fa-circle-dot"></i></span><span class="eg-ov-lbl">Status</span><span class="eg-sp <?= $statusKey ?>" style="font-size:.59rem;padding:.09rem .45rem"><?= $statusRaw ?></span></li>
                    <?php if ($cutEurCents > 0): ?><li class="eg-ov-item"><span class="eg-ov-ico"><i class="fa-solid fa-hand-holding-dollar"></i></span><span class="eg-ov-lbl">Earning</span><span class="eg-ov-val" style="color:#4ade80"><?= $formatEurMoney($cutEurCents) ?></span></li><?php endif; ?>
                </ul>
            </div>
        </div>
    </div>
</div>

<!-- Chat Image Modal -->
<div class="modal fade" id="chatImageModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fa-duotone fa-image me-2" style="color:#a855f7"></i>Chat Image</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <img id="chatImageModalImg" src="" alt="Chat image preview">
            </div>
        </div>
    </div>
</div>

<?= $this->start('scripts') ?>
<script>
(function () {
    /* ── Helpers ── */
    function egHandleResponse(res, opts) {
        opts = opts || {};
        try { res = (typeof res === 'string') ? JSON.parse(res) : res; } catch (e) {}
        res = res || {};
        if (res.sendToast) {
            var fn = (typeof create_toast === 'function') ? create_toast : (typeof showToast === 'function' ? showToast : null);
            if (fn) fn(res.sendToast.type || 'success', res.sendToast.title || '', res.sendToast.message || '');
        }
        if (res.playSound && typeof play_sound === 'function') play_sound(res.playSound);
        if (res.redirectUrl && !opts.skipRedirect) setTimeout(function () { window.location.href = res.redirectUrl; }, opts.redirectDelay || 800);
        else if (res.refreshPage && !opts.skipRefresh) setTimeout(function () { window.location.reload(); }, opts.refreshDelay || 800);
        return res;
    }

    /* ── Constants ── */
    var AJAX         = '<?= AJAX_URL ?>';
    var OID          = <?= (int)$id ?>;
    var MID          = <?= (int)$myBoosterId ?>;
    var SEND_ACTION  = 'egirl_send_message';
    var MN           = <?= json_encode($myName) ?>;
    var MI           = <?= json_encode($myIcon) ?>;
    var MX           = <?= json_encode($myInit) ?>;
    var CN           = <?= json_encode($order['client_username'] ?? 'Client') ?>;
    var CI           = <?= json_encode($order['client_icon'] ?? '') ?>;
    var TIMER_START       = <?= (int)$remainingSeconds ?>;
    var TIMER_ENABLED     = <?= $statusKey === 'in_progress' ? 'true' : 'false' ?>;
    var TIMER_INIT_PAUSED = <?= !empty($order['is_paused']) ? 'true' : 'false' ?>;
    var TIMER_ORDER_ID    = <?= (int)$id ?>;
    var TIMER_END_TS      = <?= !empty($sessionEndAtRaw) ? (int)strtotime($sessionEndAtRaw) : (time() + (int)$remainingSeconds) ?>;


    var EG_CHAT_SCOPE = 'egirl:' + OID;
    window.__lbEgirlChatScopes = window.__lbEgirlChatScopes || {};
    if (window.__lbEgirlChatScopes[EG_CHAT_SCOPE]) return;
    window.__lbEgirlChatScopes[EG_CHAT_SCOPE] = true;

    var lastTime = <?= (int)$lastMsgTime ?>;

    /* ── DOM refs ── */
    var body = document.getElementById('egChatBody');
    var inp  = document.getElementById('egChatIn');
    var snd  = document.getElementById('egChatSend');
    var cnt  = document.getElementById('chatMsgCount');

    var egChatReloading = false;
    var egRenderedMessages = Object.create(null);

    function egNormRole(role) {
        role = String(role || '').toLowerCase();
        if (role === 'e-girl' || role === 'egirl') return 'booster';
        return role;
    }
    function egNormText(text) {
        return String(text || '').replace(/\r\n/g, '\n').trim();
    }
    function egMessageSig(role, senderId, text) {
        return egNormRole(role) + '|' + String(parseInt(senderId || 0) || 0) + '|' + egNormText(text);
    }
    function egMessageKey(key, role, senderId, text, ts) {
        if (key) return 'uuid:' + String(key);
        return 'fallback:' + egMessageSig(role, senderId, text) + '|' + String(ts || '');
    }

    function egDedupeDomMessages() {
        if (!body) return;
        var seen = Object.create(null);
        body.querySelectorAll('.eg-m').forEach(function (el) {
            var cls = el.classList.contains('me') ? 'me' : 'them';
            var txtEl = el.querySelector('.eg-m-bbl');
            var tsEl  = el.querySelector('.eg-m-ts');
            var txt = txtEl ? txtEl.textContent.replace(/\s+/g, ' ').trim() : '';
            var ts  = tsEl ? tsEl.textContent.trim() : '';
            var key = cls + '|' + txt + '|' + ts;
            if (seen[key]) el.remove();
            else seen[key] = true;
        });
    }

    /* ── Image modal ── */
    var chatImageModalEl  = document.getElementById('chatImageModal');
    var chatImageModalImg = document.getElementById('chatImageModalImg');
    var chatImageModal    = (chatImageModalEl && typeof bootstrap !== 'undefined' && bootstrap.Modal)
                            ? new bootstrap.Modal(chatImageModalEl) : null;
    document.addEventListener('click', function (e) {
        var img = e.target.closest('.eg-chat-img');
        if (!img) return;
        e.preventDefault();
        var src = img.getAttribute('data-chat-image') || img.getAttribute('src');
        if (!src) return;
        if (chatImageModalImg) chatImageModalImg.src = src;
        if (chatImageModal) chatImageModal.show();
    });
    if (chatImageModalEl) chatImageModalEl.addEventListener('hidden.bs.modal', function () {
        if (chatImageModalImg) chatImageModalImg.src = '';
    });

    if (body) body.scrollTop = body.scrollHeight;

    /* ── Chat render helpers ── */
    function escAttr(v) { return String(v || '').replace(/&/g, '&amp;').replace(/"/g, '&quot;').replace(/</g, '&lt;').replace(/>/g, '&gt;'); }
    function renderChatImage(url) { var s = escAttr(url); return '<img src="' + s + '" class="eg-chat-img" data-chat-image="' + s + '" loading="lazy" alt="Chat image">'; }
    function mkAv(icon, init, mine) {
        var c = 'eg-m-av' + (mine ? ' mine' : '');
        return icon ? '<div class="' + c + '"><img src="' + icon.replace(/"/g, '&quot;') + '" alt=""></div>'
                    : '<div class="' + c + '">' + init + '</div>';
    }
    function roleBadge(role) {
        role = String(role || '').toLowerCase();
        if (role === 'admin' || role === 'administrator') return '<span class="eg-role-badge eg-role-admin">Admin</span>';
        if (role === 'booster' || role === 'egirl' || role === 'e-girl') return '<span class="eg-role-badge eg-role-egirl">GG-Girl</span>';
        return '<span class="eg-role-badge eg-role-client">Client</span>';
    }
    function addMsg(txt, mine, t, icon, init, name, grp, role, key) {
        var renderedKey = egMessageKey(key, role, mine ? MID : 0, txt, t);
        if (renderedKey && egRenderedMessages[renderedKey]) return false;
        if (renderedKey) egRenderedMessages[renderedKey] = true;
        var e = document.getElementById('egChatEmpty'); if (e) e.remove();
        var isImg = /\.(jpg|jpeg|png|gif|webp)(\?|$)/i.test(txt) || txt.indexOf('<img ') === 0;
        var s = isImg ? renderChatImage(txt) : txt.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/\n/g, '<br>');
        var h = '<div class="eg-m ' + (mine ? 'me' : 'them') + '">';
        if (!grp) h += '<div class="eg-m-head">' + mkAv(icon, init, mine) + '<div class="eg-m-name">' + (mine ? 'You' : name.replace(/</g, '&lt;')) + roleBadge(role) + '</div></div>';
        h += '<div class="eg-m-bbl">' + s + '</div><div class="eg-m-ts">' + t + '</div></div>';
        if (body) { body.insertAdjacentHTML('beforeend', h); body.scrollTop = body.scrollHeight; }
        if (cnt) { var n = (parseInt(cnt.textContent) || 0) + 1; cnt.textContent = n + ' msg' + (n !== 1 ? 's' : ''); }
        return true;
    }

    /* ── Order action buttons ── */
    function doAccept(b) {
        if (!b) return;
        b.disabled = true; b.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Starting…';
        $.post(AJAX, { action: 'egirl_accept_order', order_id: OID }, function (r) {
            r = egHandleResponse(r, { skipRedirect: true, skipRefresh: true });
            if (r.success) { setTimeout(function () { location.reload(); }, 800); }
            else { b.disabled = false; b.innerHTML = '<i class="fa-solid fa-play"></i>Start Order'; }
        }).fail(function () {
            egHandleResponse({ sendToast: { type: 'danger', title: 'Error', message: 'Could not start the order. Please try again.' } }, { skipRedirect: true, skipRefresh: true });
            b.disabled = false; b.innerHTML = '<i class="fa-solid fa-play"></i>Start Order';
        });
    }
    function doComplete(b) {
        if (!b || !confirm('Mark this session as completed?')) return;
        b.disabled = true; b.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Completing…';
        $.post(AJAX, { action: 'egirl_complete_order', order_id: OID }, function (r) {
            r = egHandleResponse(r, { skipRedirect: true, skipRefresh: true });
            if (r.success) { setTimeout(function () { location.reload(); }, 800); }
            else { b.disabled = false; b.innerHTML = '<i class="fa-solid fa-check"></i>Complete Order'; }
        }).fail(function () {
            egHandleResponse({ sendToast: { type: 'danger', title: 'Error', message: 'Could not complete the order. Please try again.' } }, { skipRedirect: true, skipRefresh: true });
            b.disabled = false; b.innerHTML = '<i class="fa-solid fa-check"></i>Complete Order';
        });
    }
    ['btnAccept', 'btnAcceptSide'].forEach(function (id) {
        var el = document.getElementById(id); if (el) el.addEventListener('click', function () { doAccept(this); });
    });
    ['btnComplete', 'btnCompleteSide'].forEach(function (id) {
        var el = document.getElementById(id); if (el) el.addEventListener('click', function () { doComplete(this); });
    });

    function handlePokeResp(r){
        r = r || {};
        if (r.sendToast && typeof window.create_order_toast === 'function') {
            window.create_order_toast({ title: r.sendToast.title || 'Poke', messageHtml: String(r.sendToast.message || ''), iconClass: 'fa-duotone fa-hand-point-up', timeoutMs: 5500 });
        } else {
            egHandleResponse(r.sendToast ? r : { sendToast: { type: 'success', title: 'Poke sent', message: 'The client has been notified.' } }, { skipRedirect: true, skipRefresh: true });
        }
    }
    $('.js-egirl-poke-client').on('click', function(){
        var $btn = $(this);
        if ($btn.prop('disabled')) return;
        $btn.prop('disabled', true).addClass('is-loading');
        $.post(AJAX, { action: 'egirl_poke_client', order_id: OID }, function(r){ handlePokeResp(r); }, 'json')
          .fail(function(){ handlePokeResp({sendToast:{title:'Error',message:'Could not send poke.'}}); })
          .always(function(){ $btn.prop('disabled', false).removeClass('is-loading'); });
    });

    /* ── Emoji picker ── */
    var egEmoji  = document.getElementById('egEmojiBtn');
    var egPicker = document.getElementById('egEmojiPicker');
    if (egEmoji && egPicker) {
        egEmoji.addEventListener('click', function (e) { e.stopPropagation(); egPicker.classList.toggle('d-none'); });
        egPicker.addEventListener('click', function (e) {
            var b = e.target.closest('[data-emoji]'); if (!b) return;
            var em = b.getAttribute('data-emoji');
            if (inp) {
                var s = inp.selectionStart || 0, en2 = inp.selectionEnd || 0, v = inp.value;
                inp.value = v.slice(0, s) + em + v.slice(en2);
                inp.setSelectionRange(s + em.length, s + em.length);
                inp.focus();
            }
            egPicker.classList.add('d-none');
        });
        document.addEventListener('click', function (e) {
            if (egPicker.classList.contains('d-none')) return;
            if (!egPicker.contains(e.target) && !egEmoji.contains(e.target)) egPicker.classList.add('d-none');
        });
    }

    /* ── File upload + paste ── */
    var egUpBtn   = document.getElementById('egUploadBtn');
    var egFileIn  = document.getElementById('egFileInput');
    var egPrevWrap = document.getElementById('egImgPreviewWrap');
    var egPrevImg  = document.getElementById('egImgPreview');
    var egRemBtn   = document.getElementById('egImgRemove');
    var selectedFile = null;

    function setPreview(f) {
        selectedFile = f || null;
        if (!egPrevWrap || !egPrevImg) return;
        if (!selectedFile) { egPrevWrap.classList.add('d-none'); egPrevImg.src = ''; if (egFileIn) egFileIn.value = ''; return; }
        egPrevImg.src = URL.createObjectURL(selectedFile);
        egPrevWrap.classList.remove('d-none');
    }
    if (egUpBtn && egFileIn) {
        egUpBtn.addEventListener('click', function () { egFileIn.click(); });
        egFileIn.addEventListener('change', function () { var f = egFileIn.files && egFileIn.files[0]; if (f) setPreview(f); });
    }
    if (egRemBtn) egRemBtn.addEventListener('click', function () { setPreview(null); });

    function handlePaste(e) {
        var items = (e.clipboardData && e.clipboardData.items) ? Array.from(e.clipboardData.items) : [];
        var img = items.find(function (it) { return it && it.type && it.type.indexOf('image/') === 0; });
        if (!img) return;
        var f = img.getAsFile(); if (!f) return;
        e.preventDefault();
        setPreview(f);
    }
    if (inp) inp.addEventListener('paste', handlePaste);
    else document.addEventListener('paste', handlePaste);

    /* ── Send message — pure XHR, no form, no ajax-form ── */
    var egChatSending = false;
    function egChat_send() {
        if (!inp || !snd || egChatSending) return;
        var txt = inp.value.trim();
        if (!txt && !selectedFile) return;
        egChatSending = true;
        snd.disabled = true; inp.disabled = true;

        function unlock() {
            egChatSending = false;
            snd.disabled = false; inp.disabled = false;
            if (snd) snd.removeAttribute('disabled');
            if (inp) { inp.removeAttribute('disabled'); try { inp.focus(); } catch (e) {} }
        }
        function onDone(r) {
            try { if (typeof r === 'string') r = JSON.parse(r); } catch (e) { r = {}; }
            if (r && r.success !== false) {
                inp.value = ''; inp.style.height = '';
                setPreview(null);
                // No local reload here. The sent message is delivered back through WebSocket.
            }
            unlock();
        }
        function onFail() { unlock(); }

        var xhr = new XMLHttpRequest();
        xhr.open('POST', AJAX, true);
        xhr.onload = function () { onDone(xhr.responseText); };
        xhr.onerror = onFail;

        if (selectedFile) {
            var fd = new FormData();
            fd.append('action', SEND_ACTION);
            fd.append('order_id', OID);
            if (txt) fd.append('message', txt);
            fd.append('chat_image', selectedFile, selectedFile.name || 'image.png');
            xhr.send(fd);
        } else {
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
            xhr.send('action=' + encodeURIComponent(SEND_ACTION) + '&order_id=' + OID + '&message=' + encodeURIComponent(txt));
        }
    }

    if (snd) snd.addEventListener('click', egChat_send);
    if (inp) {
        inp.addEventListener('keydown', function (e) { if (e.key === 'Enter' && !e.shiftKey) { e.preventDefault(); egChat_send(); } });
        inp.addEventListener('input', function () { this.style.height = 'auto'; this.style.height = Math.min(this.scrollHeight, 88) + 'px'; });
    }

    /* ── Poll for new messages ── */
<?php if (!$isChatLocked): ?>
    function renderEgirlMessages(messages) {
        if (!body) return;
        messages = Array.isArray(messages) ? messages : [];
        body.innerHTML = '';
        egRenderedMessages = Object.create(null);
        var pS = 0, pT = '', maxTime = 0, rendered = 0;
        if (cnt) cnt.textContent = '0 msgs';
        if (!messages.length) {
            body.innerHTML = '<div class="eg-chat-empty" id="egChatEmpty"><i class="fa-duotone fa-messages"></i><span>No messages yet. Say hi 👋</span></div>';
            lastTime = 0;
            return;
        }
        messages.forEach(function (m) {
            var senderType = m.sender || m.sender_type || '';
            var mine       = parseInt(m.sender_id) === MID && (senderType === 'booster' || senderType === 'egirl' || senderType === 'e-girl');
            var isAdmin    = senderType === 'admin' || senderType === 'administrator';
            var icon  = m.sender_icon || (mine ? MI : (isAdmin ? '' : CI));
            var init  = mine ? MX : (isAdmin ? 'A' : CN.charAt(0).toUpperCase());
            var name  = mine ? 'You' : (m.sender_name || (isAdmin ? 'Admin' : CN));
            var role  = isAdmin ? 'admin' : (mine ? 'egirl' : 'client');
            var grp   = (parseInt(m.sender_id) === pS && senderType === pT);
            pS = parseInt(m.sender_id); pT = senderType;
            var raw = (m.raw || m.content || m.message) || '';
            var ts = m.time ? (function(u){ var d=new Date(u*1000); return String(d.getHours()).padStart(2,'0')+':'+String(d.getMinutes()).padStart(2,'0'); })(m.time) : '';
            if (addMsg(raw, mine, ts, icon, init, name, grp, role, m.uuid || 'poll:'+senderType+':'+String(m.sender_id||0)+':'+String(m.time||0)+':'+egNormText(raw))) rendered++;
            maxTime = Math.max(maxTime, parseInt(m.time) || 0);
        });
        lastTime = maxTime;
        if (cnt) cnt.textContent = rendered + ' msg' + (rendered !== 1 ? 's' : '');
        egDedupeDomMessages();
        body.scrollTop = body.scrollHeight;
    }
    function reloadEgirlMessages() {
        if (egChatReloading) return;
        egChatReloading = true;
        if (!window.lbRealtimeConnected && document.visibilityState === 'visible') $.ajax({ url: AJAX, type: 'POST', global: false, data: { action: 'egirl_poll_messages', order_id: OID, after_time: 0 } })
        .done(function (r) {
            if (typeof r === 'string') try { r = JSON.parse(r); } catch (e) {}
            renderEgirlMessages((r && r.messages) ? r.messages : []);
        }).always(function () { egChatReloading = false; });
    }
    function pollEgirlMessages() { reloadEgirlMessages(); }

    function egMatchRealtimeOrder(data) {
        if (!data) return true;
        var expected = 'eg_' + OID;
        var values = [data.order_id, data.eg_order_id, data.egirl_order_id, data.chat_order_id];
        for (var i = 0; i < values.length; i++) {
            if (values[i] === undefined || values[i] === null) continue;
            var v = String(values[i]);
            if (v === expected || v === String(OID)) return true;
            if (v.indexOf('eg_') === 0 && v.substring(3) === String(OID)) return true;
        }
        return false;
    }

    function egAppendRealtimeMessage(m) {
        if (!m || !body) return false;
        var senderType = String(m.sender || m.sender_type || '').toLowerCase();
        var senderId = parseInt(m.sender_id || 0) || 0;
        var mine = false;
        if (typeof MID !== 'undefined') {
            mine = senderId === MID && (senderType === 'booster' || senderType === 'egirl' || senderType === 'e-girl');
        } else if (typeof MY_ID !== 'undefined') {
            mine = senderId === MY_ID && senderType === 'client';
        }
        var isAdmin = senderType === 'admin' || senderType === 'administrator';
        var raw = (m.raw || m.content || m.message || '').toString();
        if (!raw) return false;
        var t = m.time ? new Date((parseInt(m.time) || 0) * 1000) : new Date();
        var ts = String(t.getHours()).padStart(2, '0') + ':' + String(t.getMinutes()).padStart(2, '0');
        var icon = m.sender_icon || '';
        var init = 'A';
        var name = m.sender_name || (isAdmin ? 'Admin' : 'User');
        var role = isAdmin ? 'admin' : senderType;
        if (typeof MID !== 'undefined') {
            icon = icon || (mine ? MI : (isAdmin ? '' : CI));
            init = mine ? MX : (isAdmin ? 'A' : CN.charAt(0).toUpperCase());
            name = mine ? 'You' : (m.sender_name || (isAdmin ? 'Admin' : CN));
            role = isAdmin ? 'admin' : (mine ? 'egirl' : 'client');
        } else if (typeof MY_ID !== 'undefined') {
            icon = icon || (mine ? MY_ICON : (isAdmin ? '' : EG_ICON));
            init = mine ? MY_INIT : (isAdmin ? 'A' : EG_INIT);
            name = mine ? MY_NAME : (m.sender_name || (isAdmin ? 'Admin' : EG_NAME));
            role = isAdmin ? 'admin' : (mine ? 'client' : 'egirl');
        }
        return addMsg(raw, mine, ts, icon, init, name, false, role, m.uuid || ('ws:' + senderType + ':' + senderId + ':' + String(m.time || 0) + ':' + egNormText(raw)));
    }

    function egRealtimeReload(data) {
        if (!egMatchRealtimeOrder(data)) return;
        var rendered = false;
        var list = [];
        if (data && Array.isArray(data.messages)) list = data.messages;
        else if (data && data.message) list = [data.message];
        else if (data && (data.raw || data.content)) list = [data];
        if (list.length) {
            list.forEach(function (m) { if (egAppendRealtimeMessage(m)) rendered = true; });
            if (rendered && typeof egDedupeDomMessages === 'function') egDedupeDomMessages();
            return;
        }
        // Fallback for older socket payloads only: one AJAX reload per WebSocket event, never interval polling.
        reloadEgirlMessages();
    }

    window.lbOrderViewChatUpdate = egRealtimeReload;

    function egBindRealtimeSocket() {
        var sock = window.lbSocket;
        if (!sock || sock.__lbEgirlOrderBound === OID) return true;
        sock.__lbEgirlOrderBound = OID;
        try { sock.off && sock.off('chat_update', egRealtimeReload); } catch (e) {}
        try { sock.on('chat_update', egRealtimeReload); } catch (e) {}
        try { sock.emit('join', 'boosters'); } catch (e) {}
        try { sock.emit('join', 'clients'); } catch (e) {}
        try { sock.emit('join', 'eg_' + OID); } catch (e) {}
        try { sock.emit('order:join', { order_id: 'eg_' + OID, egirl_order_id: OID, order_type: 'egirl_session' }); } catch (e) {}
        return true;
    }
    (function egBindRealtimeSocketRetry(i) {
        if (egBindRealtimeSocket()) return;
        if (i < 30) setTimeout(function () { egBindRealtimeSocketRetry(i + 1); }, 500);
    })(0);
<?php endif; ?>

    /* ── Order timer controls, only pause button handling remains ── */
    if (TIMER_ENABLED) {
        var paused          = TIMER_INIT_PAUSED;
        var pausedRemaining = TIMER_START;
        var endTs           = TIMER_END_TS;

        var timerEl   = document.getElementById('sessionTimer');
        var stateEl   = document.getElementById('sessionTimerState');
        var pauseBtn  = document.getElementById('btnPauseSession');
        var ringEl    = document.getElementById('timerRingFill');
        var pctEl     = document.getElementById('timerRingPct');
        var barEl     = document.getElementById('timerProgressFill');
        var elapsedEl = document.getElementById('timerElapsed');
        var pauseLbl  = document.getElementById('btnPauseLabel');
        var TOTAL     = TIMER_START;
        var CIRCUM    = 125.66;

        function fmt(sec) {
            return String(Math.floor(sec / 3600)).padStart(2, '0') + ':' +
                   String(Math.floor((sec % 3600) / 60)).padStart(2, '0') + ':' +
                   String(sec % 60).padStart(2, '0');
        }
        function getRemaining() {
            if (paused) return Math.max(0, pausedRemaining);
            return Math.max(0, Math.ceil(endTs - Date.now() / 1000));
        }
        function renderTimer() {
            var rem     = getRemaining();
            var elapsed = Math.max(0, TOTAL - rem);
            var pct     = TOTAL > 0 ? Math.min(100, Math.round(elapsed / TOTAL * 100)) : 0;
            if (timerEl) timerEl.textContent = fmt(rem);
            if (ringEl) {
                ringEl.style.strokeDashoffset = CIRCUM - (pct / 100 * CIRCUM);
                ringEl.style.stroke = rem < 300 ? '#f472b6' : (rem < 600 ? '#fbbf24' : '#a855f7');
            }
            if (pctEl) pctEl.textContent = pct + '%';
            if (barEl) barEl.style.width = pct + '%';
            if (elapsedEl) elapsedEl.textContent = fmt(elapsed) + ' elapsed';
            if (stateEl) {
                stateEl.textContent = paused ? 'Paused' : (rem > 0 ? 'Running' : 'Finished');
                stateEl.style.color = paused ? '#fbbf24' : (rem > 0 ? '#c084fc' : '#4ade80');
            }
            if (pauseBtn) {
                var lbl = paused ? 'Resume' : 'Pause';
                var ico = paused ? 'fa-play' : 'fa-pause';
                if (pauseLbl) pauseLbl.textContent = lbl;
                pauseBtn.innerHTML = '<i class="fa-solid ' + ico + '" style="font-size:.7rem"></i><span>' + lbl + '</span>';
            }
        }
        function savePauseState(shouldPause) {
            if (!pauseBtn) return;
            pauseBtn.disabled = true;
            var curr = getRemaining();
            $.post(AJAX, {
                action: shouldPause ? 'egirl_pause_session' : 'egirl_resume_session',
                order_id: TIMER_ORDER_ID,
                remaining_seconds: curr
            }, function (r) {
                r = egHandleResponse(r, { skipRedirect: true, skipRefresh: true });
                if (r && r.success) {
                    paused = !!shouldPause;
                    pausedRemaining = (typeof r.remaining_seconds !== 'undefined') ? (parseInt(r.remaining_seconds, 10) || 0) : curr;
                    if (!paused) endTs = Math.ceil(Date.now() / 1000) + pausedRemaining;
                    renderTimer();
                }
                pauseBtn.disabled = false;
            }).fail(function () {
                pauseBtn.disabled = false;
                egHandleResponse({ sendToast: { type: 'danger', title: 'Error', message: 'Request failed.' } }, { skipRedirect: true, skipRefresh: true });
            });
        }
        if (pauseBtn) pauseBtn.addEventListener('click', function () { savePauseState(!paused); });
        renderTimer();
        setInterval(renderTimer, 250);
    }

})();
</script>
<?= $this->end() ?>
