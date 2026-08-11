<style>
/* ═══════════════════════════════════════════════
   LB Admin Modals — Dashboard Dark Theme Aligned
   Uses dashboard palette from theme-dark.min.css:
   body #1e2022, cards/modals #25282a, dropdown #2a2d30,
   borders #2f3235, text #c5c8cc, primary #5c4ae3
   ═══════════════════════════════════════════════ */
.lb-modal {
    --lb-bg: var(--bs-body-bg, #1e2022);
    --lb-surface: var(--bs-card-bg, #25282a);
    --lb-surface-2: var(--bs-gray-800, #2a2d30);
    --lb-border: var(--bs-border-color, #2f3235);
    --lb-border-soft: rgba(255,255,255,.07);
    --lb-text: var(--bs-body-color, #c5c8cc);
    --lb-title: #fff;
    --lb-muted: #91989e;
    --lb-primary: var(--bs-primary, #5c4ae3);
    --lb-radius: var(--bs-border-radius-lg, .75rem);
}

.lb-modal .modal-content {
    background: var(--lb-surface);
    border: 1px solid var(--lb-border);
    border-radius: var(--lb-radius);
    box-shadow: var(--bs-modal-box-shadow, 0rem .375rem 1.5rem rgba(30,32,34,.3));
    color: var(--lb-text);
    overflow: visible;
}

/* Hero header strip */
.lb-modal-hero {
    padding: 1.3125rem 1.3125rem 1rem;
    position: relative;
    background: var(--lb-surface);
    border-bottom: 1px solid var(--lb-border);
    border-radius: var(--lb-radius) var(--lb-radius) 0 0;
}
.lb-modal-hero::before {
    content: '';
    position: absolute;
    inset: 0;
    opacity: .08;
    pointer-events: none;
    border-radius: inherit;
}
.lb-modal-hero--green::before  { background: radial-gradient(ellipse at 30% 0%, var(--bs-success, #00c9a7), transparent 70%); }
.lb-modal-hero--red::before    { background: radial-gradient(ellipse at 30% 0%, var(--bs-danger, #ed4c78), transparent 70%); }
.lb-modal-hero--blue::before   { background: radial-gradient(ellipse at 30% 0%, var(--lb-primary), transparent 70%); }
.lb-modal-hero--orange::before { background: radial-gradient(ellipse at 30% 0%, var(--bs-warning, #f5ca99), transparent 70%); }

.lb-modal-hero-icon {
    width: 40px; height: 40px;
    border-radius: .75rem;
    display: flex; align-items: center; justify-content: center;
    font-size: 17px;
    flex: 0 0 auto;
    margin-right: 13px;
    background: rgba(255,255,255,.075);
    border: 1px solid var(--lb-border);
}
.lb-modal-hero--green  .lb-modal-hero-icon { color: var(--bs-success, #00c9a7); }
.lb-modal-hero--red    .lb-modal-hero-icon { color: var(--bs-danger, #ed4c78); }
.lb-modal-hero--blue   .lb-modal-hero-icon { color: var(--lb-primary); }
.lb-modal-hero--orange .lb-modal-hero-icon { color: var(--bs-warning, #f5ca99); }

.lb-modal-hero-title {
    font-size: 16px; font-weight: 700; color: var(--lb-title);
    letter-spacing: .01em; margin: 0 0 3px;
}
.lb-modal-hero-sub {
    font-size: 12px; color: var(--lb-muted); margin: 0;
}
.lb-modal-hero-close {
    position: absolute; top: 14px; right: 14px;
    width: 28px; height: 28px; border-radius: .3125rem;
    background: transparent; border: 1px solid transparent;
    color: rgba(255,255,255,.55); display: flex; align-items: center; justify-content: center;
    cursor: pointer; font-size: 12px; transition: background .14s, color .14s, border-color .14s;
}
.lb-modal-hero-close:hover {
    background: rgba(255,255,255,.075);
    border-color: var(--lb-border);
    color: #fff;
}

/* Body */
.lb-modal .modal-body {
    padding: 1.3125rem;
    background: var(--lb-surface);
    color: var(--lb-text);
}
.lb-modal .modal-body[style*="overflow-y:auto"] {
    scrollbar-width: thin;
    scrollbar-color: var(--bs-gray-400, #3d4145) var(--lb-surface);
}
.lb-modal .modal-body[style*="overflow-y:auto"]::-webkit-scrollbar { width: 8px; }
.lb-modal .modal-body[style*="overflow-y:auto"]::-webkit-scrollbar-track { background: var(--lb-surface); }
.lb-modal .modal-body[style*="overflow-y:auto"]::-webkit-scrollbar-thumb { background: var(--bs-gray-400, #3d4145); border-radius: 999px; }

/* Labels */
.lb-label {
    font-size: 11px; font-weight: 700; letter-spacing: .06em;
    text-transform: uppercase; color: var(--lb-muted);
    margin-bottom: 7px; display: block;
}

/* Inputs, native selects & textarea */
.lb-modal .form-control,
.lb-modal .form-select {
    background-color: var(--lb-bg) !important;
    border: 1px solid var(--lb-border) !important;
    border-radius: var(--bs-border-radius-sm, .3125rem) !important;
    color: #fff !important;
    font-size: 14px;
    padding: .6125rem 1rem;
    box-shadow: none !important;
    transition: border-color .14s, box-shadow .14s, background-color .14s;
}
.lb-modal .form-select {
    color: var(--lb-text) !important;
    padding-right: 2.25rem;
}
.lb-modal .form-control:focus,
.lb-modal .form-select:focus {
    border-color: var(--lb-border) !important;
    box-shadow: 0 0 1rem 0 var(--lb-border) !important;
    background-color: var(--lb-bg) !important;
    outline: none;
}
.lb-modal .form-control::placeholder { color: var(--lb-text) !important; opacity: .65; }
.lb-modal textarea.form-control { resize: vertical; min-height: 68px; }
.lb-modal input[readonly] { background-color: var(--lb-bg) !important; opacity: 1; }

/* Input group EUR tag */
.lb-modal .input-group-text {
    background: var(--lb-bg) !important;
    border: 1px solid var(--lb-border) !important;
    border-left: none !important;
    border-radius: 0 var(--bs-border-radius-sm, .3125rem) var(--bs-border-radius-sm, .3125rem) 0 !important;
    color: var(--lb-muted) !important;
    font-weight: 700; font-size: 12px; letter-spacing: .06em;
}
.lb-modal .input-group .form-control {
    border-right: none !important;
    border-radius: var(--bs-border-radius-sm, .3125rem) 0 0 var(--bs-border-radius-sm, .3125rem) !important;
}
.lb-modal .input-group:focus-within .form-control,
.lb-modal .input-group:focus-within .input-group-text { border-color: var(--lb-border) !important; }

/* Balance chip */
.lb-balance-chip {
    display: inline-flex; align-items: center; gap: 8px;
    padding: 7px 12px; border-radius: var(--bs-border-radius-sm, .3125rem);
    background: var(--lb-bg); border: 1px solid var(--lb-border);
    font-size: 12px; color: var(--lb-muted); font-weight: 600;
    margin-top: 9px;
}
.lb-balance-chip strong { color: #fff; font-weight: 800; }

/* ── Tom-Select dashboard override ────────────── */
.lb-modal .ts-wrapper { width: 100%; }
.lb-modal .ts-wrapper .ts-control {
    background: var(--lb-bg) !important;
    border: 1px solid var(--lb-border) !important;
    border-radius: var(--bs-border-radius-sm, .3125rem) !important;
    color: var(--lb-text) !important;
    padding: .6125rem 2.25rem .6125rem 1rem !important;
    box-shadow: none !important;
    cursor: pointer;
    min-height: calc(1.5em + 1.35rem);
}
.lb-modal .ts-wrapper.focus .ts-control,
.lb-modal .ts-wrapper .ts-control:focus {
    border-color: var(--lb-border) !important;
    box-shadow: 0 0 1rem 0 var(--lb-border) !important;
}
.lb-modal .ts-wrapper .ts-control .item { color: var(--lb-text) !important; }
.lb-modal .ts-wrapper .ts-control input { color: #fff !important; background: transparent !important; }
.lb-modal .ts-wrapper .ts-control input::placeholder { color: var(--lb-text) !important; opacity: .65; }
.lb-modal .ts-wrapper .ts-control::after { border-color: var(--lb-muted) transparent transparent !important; }

/* Tom-select dropdown panel */
.lb-modal .ts-wrapper .ts-dropdown,
.lb-modal .tom-select-custom .ts-dropdown.form-control,
.lb-modal .tom-select-custom .ts-dropdown.form-select {
    background: var(--lb-surface-2) !important;
    border: 0 !important;
    border-radius: var(--bs-border-radius, .5rem) !important;
    box-shadow: var(--bs-dropdown-box-shadow, 0rem .6125rem 2.5rem .6125rem rgba(30,32,34,.35)) !important;
    margin-top: .625rem !important;
    padding: .5rem !important;
    overflow: hidden;
    z-index: 1060;
}
.lb-modal .ts-wrapper .ts-dropdown .ts-dropdown-content { padding: 0 !important; }
.lb-modal .ts-wrapper .ts-dropdown .option,
.lb-modal .ts-wrapper .ts-dropdown .no-results,
.lb-modal .ts-wrapper .ts-dropdown .create {
    color: rgba(255,255,255,.70) !important;
    border-radius: var(--bs-border-radius-sm, .3125rem) !important;
    padding: .5rem 1rem !important;
    font-size: 13px !important;
    cursor: pointer;
    transition: background .10s, color .10s;
    background: transparent !important;
}
.lb-modal .ts-wrapper .ts-dropdown .option:hover,
.lb-modal .ts-wrapper .ts-dropdown .option.active,
.lb-modal .ts-wrapper .ts-dropdown .create:hover {
    background: rgba(255,255,255,.075) !important;
    color: #fff !important;
}
.lb-modal .ts-wrapper .ts-dropdown .option.selected {
    background: rgba(61,65,69,.30) !important;
    color: #fff !important;
    font-weight: 700;
}

/* Fine list styles */
.lb-fine-group { margin-bottom: 16px; }
.lb-fine-group-title {
    font-size: 10px; font-weight: 800; letter-spacing: .10em;
    text-transform: uppercase; margin: 0 0 7px 2px;
    display: flex; align-items: center; gap: 8px;
}
.lb-fine-group-title::after {
    content: ''; flex: 1; height: 1px;
    background: currentColor; opacity: .16;
}
.lb-fine-item {
    display: flex; align-items: center; gap: 11px;
    padding: 9px 11px; border-radius: var(--bs-border-radius, .5rem);
    border: 1px solid var(--lb-border);
    background: var(--lb-bg);
    cursor: pointer;
    transition: border-color .13s, background .13s, transform .12s;
    margin-bottom: 4px; user-select: none;
}
.lb-fine-item:hover {
    background: var(--lb-surface-2);
    border-color: var(--bs-gray-300, #4b5055);
    transform: translateX(2px);
}
.lb-fine-item.is-selected {
    border-color: var(--lb-primary);
    background: rgba(92,74,227,.12);
}
.lb-fine-emoji { font-size: 16px; flex: 0 0 22px; text-align: center; }
.lb-fine-text { flex: 1 1 auto; min-width: 0; }
.lb-fine-name { font-weight: 700; font-size: 13px; color: #fff; line-height: 1.2; }
.lb-fine-desc { font-size: 11px; color: var(--lb-muted); margin-top: 2px; line-height: 1.3; }
.lb-fine-badge {
    flex: 0 0 auto; font-weight: 800; font-size: 12px; color: var(--lb-text);
    background: rgba(255,255,255,.075); border: 1px solid var(--lb-border);
    border-radius: var(--bs-border-radius-sm, .3125rem); padding: 3px 9px; white-space: nowrap;
    transition: background .13s, border-color .13s, color .13s;
}
.lb-fine-item.is-selected .lb-fine-badge {
    background: var(--lb-primary); border-color: var(--lb-primary); color: #fff;
}

/* Footer */
.lb-modal .modal-footer {
    padding: .75rem 1.3125rem 1.125rem;
    border-top: 1px solid var(--lb-border);
    background: var(--lb-surface);
    border-radius: 0 0 var(--lb-radius) var(--lb-radius);
    gap: 8px;
}
.lb-modal .modal-footer .btn { border-radius: var(--bs-border-radius-sm, .3125rem); font-weight: 700; font-size: 13px; padding: 9px 20px; }

.lb-btn-success {
    background: var(--bs-success, #00c9a7) !important;
    border: 1px solid var(--bs-success, #00c9a7) !important;
    color: #fff !important;
    box-shadow: none !important;
    transition: filter .14s, transform .12s !important;
}
.lb-btn-success:hover { filter: brightness(1.08); transform: translateY(-1px); }

.lb-btn-danger {
    background: var(--bs-danger, #ed4c78) !important;
    border: 1px solid var(--bs-danger, #ed4c78) !important;
    color: #fff !important;
    box-shadow: none !important;
    transition: filter .14s, transform .12s !important;
}
.lb-btn-danger:hover { filter: brightness(1.08); transform: translateY(-1px); }

.lb-btn-cancel {
    background: rgba(255,255,255,.075) !important;
    border: 1px solid var(--lb-border) !important;
    color: var(--lb-text) !important;
}
.lb-btn-cancel:hover { background: rgba(255,255,255,.12) !important; color: #fff !important; }

.lb-field-row { margin-bottom: 15px; }
.lb-field-row:last-child { margin-bottom: 0; }
</style>


<!-- ═══════════════════════════════════════════
     Modal — Add Money
     ═══════════════════════════════════════════ -->
<div class="modal fade lb-modal" id="add_money_md" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width:440px;">
        <div class="modal-content">
            <form class="form ajax-form" action="<?= AJAX_URL ?>">
                <input type="hidden" name="action" value="admin_add_booster_money">
                <input type="hidden" name="id" value="<?= $data['id'] ?>">

                <div class="lb-modal-hero lb-modal-hero--green">
                    <div class="d-flex align-items-center">
                        <div class="lb-modal-hero-icon"><i class="fa-solid fa-circle-plus"></i></div>
                        <div>
                            <div class="lb-modal-hero-title">Add Money</div>
                            <div class="lb-modal-hero-sub">Add funds to the booster's balance</div>
                        </div>
                    </div>
                    <button type="button" class="lb-modal-hero-close" data-bs-dismiss="modal" aria-label="Close">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>

                <div class="modal-body">
                    <div class="lb-field-row">
                        <label class="lb-label">Reason</label>
                        <div class="tom-select-custom">
                            <select class="form-select" name="reason" autocomplete="off">
                                <option value="order_completion" selected>Completed Order</option>
                                <option value="private_order">Private Order</option>
                                <option value="progress_payment">Progress Payment</option>
                                <option value="payment_error">Payment Error</option>
                                <option value="client_tip">Client Tip</option>
                                <option value="other">Other</option>
                            </select>
                        </div>
                    </div>
                    <div class="lb-field-row">
                        <label class="lb-label">Note <span style="text-transform:none;font-weight:500;opacity:.6;">(optional)</span></label>
                        <textarea class="form-control" rows="2" placeholder="Details about the payment" name="note"></textarea>
                    </div>
                    <div class="lb-field-row">
                        <label class="lb-label">Amount</label>
                        <div class="input-group">
                            <input type="number" class="form-control" placeholder="0.00" name="amount" min="0" step="0.01">
                            <span class="input-group-text">EUR</span>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="submit" class="btn lb-btn-success">
                        <span class="indicator-label"><i class="fa-solid fa-circle-plus me-2"></i>Add Money</span>
                        <span class="indicator-progress"><span class="spinner-border spinner-border-sm align-middle me-2"></span>Processing…</span>
                        <span class="indicator-success"><i class="fa-solid fa-circle-check me-2"></i>Done</span>
                    </button>
                    <button type="button" data-bs-dismiss="modal" class="btn lb-btn-cancel">Cancel</button>
                </div>
            </form>
        </div>
    </div>
</div>


<!-- ═══════════════════════════════════════════
     Modal — Fine Booster
     ═══════════════════════════════════════════ -->
<div class="modal fade lb-modal" id="fine_booster_md" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg" style="max-width:640px;">
        <div class="modal-content">
            <form class="form ajax-form" action="<?= AJAX_URL ?>">
                <input type="hidden" name="action" value="admin_fine_booster">
                <input type="hidden" name="id" value="<?= $data['id'] ?>">

                <div class="lb-modal-hero lb-modal-hero--red">
                    <div class="d-flex align-items-center">
                        <div class="lb-modal-hero-icon"><i class="fa-solid fa-triangle-exclamation"></i></div>
                        <div>
                            <div class="lb-modal-hero-title">Fine Booster</div>
                            <div class="lb-modal-hero-sub">Select a violation or enter a custom fine below</div>
                        </div>
                    </div>
                    <button type="button" class="lb-modal-hero-close" data-bs-dismiss="modal" aria-label="Close">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>

                <div class="modal-body" style="max-height:72vh;overflow-y:auto;">

                    <?php
                    $fine_groups = [
                        ['label'=>'Major Violations','color'=>'#f87171','fines'=>[
                            ['emoji'=>'🚫','label'=>'Private Boosting or Coaching','desc'=>'Do not offer private boosting, coaching, or extra services. This also includes accepting tips for extra services.','amount'=>250,'reason'=>'Private Boosting or Coaching'],
                            ['emoji'=>'🤖','label'=>'Using Third-Party Programs','desc'=>'Do not use bots, scripts, or other third-party programs for orders or in-game actions.','amount'=>200,'reason'=>'Using Third-Party Programs'],
                            ['emoji'=>'💬','label'=>'Chat Ban Without Telling Admins','desc'=>"If the customer's account gets chat banned, you must tell admins immediately.",'amount'=>100,'reason'=>'Chat Ban Without Telling Admins'],
                            ['emoji'=>'📞','label'=>'Sharing Contact Info / Outside Channels','desc'=>'Do not share Discord, phone number, social media, or any other contact information with the customer.','amount'=>75,'reason'=>'Sharing Contact Info Outside Official Channels'],
                            ['emoji'=>'📤','label'=>'Sharing Orders Without Admin Approval','desc'=>'Do not give or sell orders to other boosters or other websites without admin approval.','amount'=>50,'reason'=>'Sharing Orders Without Admin Approval'],
                            ['emoji'=>'🔇','label'=>'Chat Ban on Customer Account','desc'=>"Any chat ban caused on the customer's account may result in a fine.",'amount'=>50,'reason'=>'Chat Ban on Customer Account'],
                        ]],
                        ['label'=>'Standard Violations','color'=>'#fbbf24','fines'=>[
                            ['emoji'=>'🌐','label'=>'Not Using a Working VPN','desc'=>'You must use a working VPN when it is required for the order.','amount'=>25,'reason'=>'Not Using a Working VPN'],
                            ['emoji'=>'😡','label'=>'Bad Behavior In-Game or Toward Customer','desc'=>'This includes flaming, griefing, intentional feeding, begging for tips, or being rude.','amount'=>25,'reason'=>'Bad Behavior In-Game or Toward Customer'],
                            ['emoji'=>'🛒','label'=>'Buying Items Without Approval','desc'=>"Solo Boost only. Do not buy, use, or change anything without permission.",'amount'=>25,'reason'=>'Buying Items Without Approval'],
                            ['emoji'=>'✉️','label'=>'Messaging People on Customer Account','desc'=>"Solo Boost only. Do not chat or message people from the customer's account.",'amount'=>20,'reason'=>'Messaging People on Customer Account'],
                            ['emoji'=>'⚠️','label'=>'Ignoring Order Details','desc'=>'Follow all order details, such as champions, role, summoner spells, streaming, offline mode, and other notes.','amount'=>20,'reason'=>'Ignoring Order Details'],
                        ]],
                        ['label'=>'Minor Violations','color'=>'#60a5fa','fines'=>[
                            ['emoji'=>'👻','label'=>'Not Using Offline Chat Mode','desc'=>'Solo or DuoQ Boost. Use offline mode when it is required.','amount'=>10,'reason'=>'Not Using Offline Chat Mode'],
                            ['emoji'=>'🔁','label'=>'Dropping Order Without Drop Token','desc'=>'Do not drop an order if you do not have a valid Drop Token.','amount'=>10,'reason'=>'Dropping Order Without Drop Token'],
                            ['emoji'=>'⚠️','label'=>'Asking Customer to Drop the Order','desc'=>'Do not ask the customer to drop the order because you do not have a Drop Token.','amount'=>10,'reason'=>'Asking Customer to Drop the Order'],
                        ]],
                        ['label'=>'Minor Operational Violations','color'=>'#94a3b8','fines'=>[
                            ['emoji'=>'🗑️','label'=>'Invalid Delete Game Request','desc'=>'Do not request a game deletion if the game does not qualify for deletion.','amount'=>5,'reason'=>'Invalid Delete Game Request'],
                            ['emoji'=>'📡','label'=>'Not Adding Duo Account for API Tracking','desc'=>'Do not hide or skip the DuoQ account if it is needed for tracking.','amount'=>5,'reason'=>'Not Adding Duo Account for API Tracking'],
                            ['emoji'=>'🎮','label'=>'Accepting DuoQ Without Duo Account','desc'=>'Do not accept a DuoQ order if you do not have a suitable DuoQ account ready or available soon.','amount'=>5,'reason'=>'Accepting DuoQ Without Duo Account Ready'],
                            ['emoji'=>'💬','label'=>'Not Messaging Customer After Claiming','desc'=>'After accepting an order, you must message the customer in the order chat.','amount'=>5,'reason'=>'Not Messaging Customer After Claiming'],
                            ['emoji'=>'⏰','label'=>'Unnecessary Delay After Accepting','desc'=>'Do not accept an order and then do nothing. Start, prepare, or update the customer/admins.','amount'=>5,'reason'=>'Unnecessary Delay After Accepting'],
                            ['emoji'=>'📸','label'=>'Not Sending Proof When Asked','desc'=>'Send screenshots, lobby proof, game proof, VPN proof, or other proof when admins ask for it.','amount'=>5,'reason'=>'Not Sending Proof When Asked'],
                            ['emoji'=>'🔄','label'=>'Wrong or Missing Order Status Updates','desc'=>'Keep the order status updated correctly. Report problems when needed.','amount'=>5,'reason'=>'Wrong or Missing Order Status Updates'],
                            ['emoji'=>'🚫','label'=>'Ignoring Admin Instructions','desc'=>'Follow admin instructions for active orders and customer problems.','amount'=>5,'reason'=>'Ignoring Admin Instructions'],
                        ]],
                    ];
                    ?>

                    <?php foreach ($fine_groups as $group): ?>
                    <div class="lb-fine-group">
                        <div class="lb-fine-group-title" style="color:<?= $group['color'] ?>"><?= $group['label'] ?></div>
                        <?php foreach ($group['fines'] as $fine): ?>
                        <div class="lb-fine-item"
                             data-amount="<?= $fine['amount'] ?>"
                             data-reason="<?= htmlspecialchars($fine['reason'], ENT_QUOTES) ?>"
                             onclick="lbSelectFine(this)">
                            <span class="lb-fine-emoji"><?= $fine['emoji'] ?></span>
                            <div class="lb-fine-text">
                                <div class="lb-fine-name"><?= htmlspecialchars($fine['label'], ENT_QUOTES) ?></div>
                                <div class="lb-fine-desc"><?= htmlspecialchars($fine['desc'], ENT_QUOTES) ?></div>
                            </div>
                            <span class="lb-fine-badge"><?= $fine['amount'] ?>€</span>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php endforeach; ?>

                    <!-- Divider -->
                    <div style="height:1px;background:rgba(255,255,255,.07);margin:20px 0 18px;"></div>

                    <div class="lb-field-row">
                        <label class="lb-label">Reason
                            <span style="font-weight:500;text-transform:none;opacity:.6;margin-left:4px;">— auto-filled from selection, editable</span>
                        </label>
                        <input type="text" class="form-control" name="reason" id="fineReasonInput"
                               placeholder="Select a preset above or type a custom reason" autocomplete="off">
                    </div>

                    <div class="lb-field-row">
                        <label class="lb-label">Note <span style="text-transform:none;font-weight:500;opacity:.6;">(optional)</span></label>
                        <textarea class="form-control" rows="2" placeholder="Additional details about the fine" name="note"></textarea>
                    </div>

                    <div class="lb-field-row">
                        <label class="lb-label">Fine Amount
                            <span style="font-weight:500;text-transform:none;opacity:.6;margin-left:4px;">— auto-filled, adjustable</span>
                        </label>
                        <div class="input-group">
                            <input type="number" class="form-control" placeholder="0.00" name="amount" id="fineAmountInput" min="0" step="0.01">
                            <span class="input-group-text">EUR</span>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="submit" class="btn lb-btn-danger">
                        <span class="indicator-label"><i class="fa-solid fa-gavel me-2"></i>Apply Fine</span>
                        <span class="indicator-progress"><span class="spinner-border spinner-border-sm align-middle me-2"></span>Processing…</span>
                        <span class="indicator-success"><i class="fa-solid fa-circle-check me-2"></i>Done</span>
                    </button>
                    <button type="button" data-bs-dismiss="modal" class="btn lb-btn-cancel">Cancel</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function lbSelectFine(el){
    document.querySelectorAll('#fine_booster_md .lb-fine-item').forEach(i => i.classList.remove('is-selected'));
    el.classList.add('is-selected');
    const amountInput = document.getElementById('fineAmountInput');
    const reasonInput = document.getElementById('fineReasonInput');
    if(amountInput) amountInput.value = el.getAttribute('data-amount');
    if(reasonInput) reasonInput.value = el.getAttribute('data-reason');
    const body = el.closest('.modal-body');
    if(body) setTimeout(() => body.scrollTo({top: body.scrollHeight, behavior: 'smooth'}), 80);
}
document.addEventListener('DOMContentLoaded', function(){
    const modal = document.getElementById('fine_booster_md');
    if(modal){
        modal.addEventListener('hidden.bs.modal', function(){
            document.querySelectorAll('#fine_booster_md .lb-fine-item').forEach(i => i.classList.remove('is-selected'));
            const a = document.getElementById('fineAmountInput');
            const r = document.getElementById('fineReasonInput');
            if(a) a.value = '';
            if(r) r.value = '';
        });
    }
});
</script>


<!-- ═══════════════════════════════════════════
     Modal — Withdraw Balance
     ═══════════════════════════════════════════ -->
<div class="modal fade lb-modal" id="withdraw_balance_md" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width:440px;">
        <div class="modal-content">
            <form class="form ajax-form" action="<?= AJAX_URL ?>">
                <input type="hidden" name="action" value="admin_withdraw_booster_balance">
                <input type="hidden" name="id" value="<?= $data['id'] ?>">

                <div class="lb-modal-hero lb-modal-hero--blue">
                    <div class="d-flex align-items-center">
                        <div class="lb-modal-hero-icon"><i class="fa-solid fa-money-check-dollar"></i></div>
                        <div>
                            <div class="lb-modal-hero-title">Withdraw Balance</div>
                            <div class="lb-modal-hero-sub">Process a payout from the booster's balance</div>
                        </div>
                    </div>
                    <button type="button" class="lb-modal-hero-close" data-bs-dismiss="modal" aria-label="Close">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>

                <div class="modal-body">
                    <div class="lb-field-row">
                        <label class="lb-label">Note <span style="text-transform:none;font-weight:500;opacity:.6;">(optional)</span></label>
                        <textarea class="form-control" rows="2" placeholder="Details about the payout" name="note"></textarea>
                    </div>
                    <div class="lb-field-row">
                        <label class="lb-label">Payout Amount</label>
                        <div class="input-group">
                            <input type="number" class="form-control" placeholder="0.00"
                                   value="<?= util_format_price_input($data['balance']) ?>"
                                   name="amount" min="0" max="<?= util_format_price_input($data['balance']) ?>" step="0.01">
                            <span class="input-group-text">EUR</span>
                        </div>
                        <div class="lb-balance-chip">
                            <i class="fa-solid fa-wallet" style="opacity:.6;"></i>
                            Current balance: <strong><?= util_format_price_display($data['balance']) ?> EUR</strong>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="submit" class="btn lb-btn-success">
                        <span class="indicator-label"><i class="fa-solid fa-money-check-dollar me-2"></i>Withdraw</span>
                        <span class="indicator-progress"><span class="spinner-border spinner-border-sm align-middle me-2"></span>Processing…</span>
                        <span class="indicator-success"><i class="fa-solid fa-circle-check me-2"></i>Done</span>
                    </button>
                    <button type="button" data-bs-dismiss="modal" class="btn lb-btn-cancel">Cancel</button>
                </div>
            </form>
        </div>
    </div>
</div>


<!-- ═══════════════════════════════════════════
     Modal — Ban Booster
     ═══════════════════════════════════════════ -->
<div class="modal fade lb-modal" id="ban_booster_md" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width:440px;">
        <div class="modal-content">
            <form class="form ajax-form" action="<?= AJAX_URL ?>">
                <input type="hidden" name="action" value="admin_ban_booster">
                <input type="hidden" name="id" value="<?= $data['id'] ?>">

                <div class="lb-modal-hero lb-modal-hero--orange">
                    <div class="d-flex align-items-center">
                        <div class="lb-modal-hero-icon"><i class="fa-solid fa-ban"></i></div>
                        <div>
                            <div class="lb-modal-hero-title">Ban Booster</div>
                            <div class="lb-modal-hero-sub">This action will restrict the booster's access</div>
                        </div>
                    </div>
                    <button type="button" class="lb-modal-hero-close" data-bs-dismiss="modal" aria-label="Close">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>

                <div class="modal-body">
                    <div class="lb-field-row">
                        <label class="lb-label">Reason</label>
                        <div class="tom-select-custom">
                            <select class="form-select" name="reason" autocomplete="off">
                                <option value="left_company">Left Company</option>
                                <option value="banned_client_account">Banned Client Account</option>
                                <option value="private_boosting">Private Boosting</option>
                                <option value="order_sharing">Order Sharing</option>
                                <option value="disrespected_staff">Disrespected Staff</option>
                                <option value="multiple_offenses">Multiple Offenses</option>
                                <option value="negative_balance">Negative Balance</option>
                                <option value="inactivity">Inactivity</option>
                                <option value="other">Other</option>
                            </select>
                        </div>
                    </div>
                    <div class="lb-field-row">
                        <label class="lb-label">Details <span style="text-transform:none;font-weight:500;opacity:.6;">(optional)</span></label>
                        <textarea class="form-control" rows="3" placeholder="Details about the ban" name="details"></textarea>
                    </div>

                    <!-- Warning box -->
                    <div style="display:flex;align-items:flex-start;gap:10px;padding:12px 14px;border-radius:11px;background:rgba(249,115,22,.08);border:1px solid rgba(249,115,22,.22);margin-top:4px;">
                        <i class="fa-solid fa-circle-exclamation" style="color:#fb923c;margin-top:2px;flex:0 0 auto;"></i>
                        <span style="font-size:12px;color:rgba(255,255,255,.65);line-height:1.45;">
                            Banning will immediately restrict the booster's access. Make sure you have reviewed all active orders before proceeding.
                        </span>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="submit" class="btn lb-btn-danger">
                        <span class="indicator-label"><i class="fa-solid fa-ban me-2"></i>Ban Booster</span>
                        <span class="indicator-progress"><span class="spinner-border spinner-border-sm align-middle me-2"></span>Processing…</span>
                        <span class="indicator-success"><i class="fa-solid fa-circle-check me-2"></i>Done</span>
                    </button>
                    <button type="button" data-bs-dismiss="modal" class="btn lb-btn-cancel">Cancel</button>
                </div>
            </form>
        </div>
    </div>
</div>


<!-- ═══════════════════════════════════════════
     Modal — Copy Onboarding Link
     ═══════════════════════════════════════════ -->
<div class="modal fade lb-modal" id="onboardingLinkModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width:420px;">
        <div class="modal-content">
            <div class="lb-modal-hero lb-modal-hero--blue">
                <div class="d-flex align-items-center">
                    <div class="lb-modal-hero-icon"><i class="fa-solid fa-link"></i></div>
                    <div>
                        <div class="lb-modal-hero-title">Copy Onboarding Link</div>
                        <div class="lb-modal-hero-sub">Automatic copy was blocked — use the button below</div>
                    </div>
                </div>
                <button type="button" class="lb-modal-hero-close" data-bs-dismiss="modal" aria-label="Close">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>
            <div class="modal-body">
                <div class="lb-field-row">
                    <label class="lb-label">Onboarding Link</label>
                    <input type="text" class="form-control" id="onboardingLinkField" readonly
                           style="font-family:monospace;font-size:12px;">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn lb-btn-success" id="copyOnboardingLinkBtn">
                    <i class="fa-duotone fa-copy me-2"></i>Copy Link
                </button>
                <button type="button" class="btn lb-btn-cancel" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
