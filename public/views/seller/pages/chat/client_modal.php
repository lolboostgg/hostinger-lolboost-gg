<?php
/**
 * Custom seller chat modal (no Bootstrap required).
 * Expected include path:
 * /public/views/seller/pages/chat/client_modal.php
 */

$seller_id    = (int)($seller_id ?? 0);
$seller_name_raw = (string)($seller_name ?? 'Seller');
$seller_icon_raw = (string)($seller_icon ?? '');
$ref_type     = (string)($ref_type ?? 'none');
$ref_id       = (int)($ref_id ?? 0);
$chat_allowed = (bool)($chat_allowed ?? true);

if ($seller_id <= 0) { return; }

$h = static function ($v) {
    return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');
};
$seller_initials = strtoupper(mb_substr($seller_name_raw, 0, 2));
$ajax_url = defined('AJAX_URL') ? AJAX_URL : ((defined('BASE_URL') ? rtrim(BASE_URL, '/') : '') . '/ajax');
?>

<style>
.lbsc-lock{overflow:hidden!important}.lbsc-overlay{position:fixed;inset:0;z-index:99999;display:none;align-items:center;justify-content:center;padding:22px;background:rgba(5,4,14,.72);backdrop-filter:blur(8px)}.lbsc-overlay.is-open{display:flex}.lbsc-modal{width:min(820px,96vw);max-height:min(860px,94vh);display:flex;flex-direction:column;overflow:hidden;border-radius:22px;background:#11101d;border:1px solid rgba(125,98,255,.28);box-shadow:0 34px 100px rgba(0,0,0,.62);color:#fff}.lbsc-head{display:flex;align-items:center;justify-content:space-between;gap:16px;padding:22px 26px;background:linear-gradient(135deg,#17152a 0%,#241b55 45%,#6c3cff 100%)}.lbsc-seller{display:flex;align-items:center;gap:14px}.lbsc-avatar,.lbsc-avatar-fallback{width:56px;height:56px;border-radius:17px;border:1px solid rgba(255,255,255,.28);box-shadow:0 10px 28px rgba(0,0,0,.28)}.lbsc-avatar{object-fit:cover}.lbsc-avatar-fallback{display:flex;align-items:center;justify-content:center;background:rgba(255,255,255,.14);font-weight:900}.lbsc-name{font-size:22px;font-weight:900;line-height:1.1}.lbsc-status{display:flex;align-items:center;gap:7px;margin-top:6px;color:rgba(255,255,255,.74);font-size:13px}.lbsc-dot{width:10px;height:10px;border-radius:50%;background:#45df87;box-shadow:0 0 0 4px rgba(69,223,135,.14)}.lbsc-close{width:42px;height:42px;border-radius:14px;border:1px solid rgba(255,255,255,.18);background:rgba(255,255,255,.1);color:#fff;font-size:22px;cursor:pointer}.lbsc-close:hover{background:rgba(255,255,255,.18)}.lbsc-body{overflow:auto;background:radial-gradient(circle at 20% 10%,rgba(108,99,255,.12),transparent 28%),#0d0b18}.lbsc-compose-top{padding:24px 26px 16px}.lbsc-label{display:block;font-weight:800;margin-bottom:10px;color:#f4f1ff}.lbsc-textarea{width:100%;min-height:132px;resize:vertical;border-radius:15px;border:1px solid rgba(133,116,190,.28);background:#11101d;color:#fff;padding:16px;font-size:15px;outline:none}.lbsc-textarea:focus{border-color:#6b63ff;box-shadow:0 0 0 4px rgba(107,99,255,.15)}.lbsc-counter{text-align:right;margin-top:6px;font-size:12px;color:#948dae}.lbsc-online{display:flex;align-items:center;justify-content:center;gap:10px;margin-top:0;padding:13px;border:1px solid rgba(69,223,135,.14);border-top:0;border-radius:0 0 15px 15px;background:rgba(69,223,135,.11);color:#59e795;font-weight:800}.lbsc-guidelines{margin:20px 26px;border:1px solid rgba(133,116,190,.2);border-radius:16px;background:#12101c;overflow:hidden}.lbsc-agree{display:flex;gap:14px;align-items:flex-start;padding:18px 20px;cursor:pointer}.lbsc-agree input{width:22px;height:22px;accent-color:#655df4;margin-top:2px}.lbsc-agree strong{display:block;font-size:16px}.lbsc-agree span{display:block;margin-top:5px;color:#aba4c5;line-height:1.4}.lbsc-guide-toggle{margin:0 26px 0;border:1px solid rgba(133,116,190,.25);background:#1b1829;color:#fff;border-radius:14px;padding:12px 16px;font-weight:800;cursor:pointer}.lbsc-guide-grid{display:grid;grid-template-columns:1fr 1fr;gap:18px;padding:18px 26px 24px}.lbsc-card{border-radius:16px;border:1px solid rgba(255,255,255,.08);overflow:hidden}.lbsc-card.good{background:rgba(34,197,94,.13)}.lbsc-card.bad{background:rgba(239,68,68,.13)}.lbsc-card h4{margin:0;padding:14px 16px;font-size:16px}.lbsc-card.good h4{color:#4ade80}.lbsc-card.bad h4{color:#fb7185}.lbsc-card div{padding:13px 16px;border-top:1px solid rgba(255,255,255,.07);color:#f4f1ff}.lbsc-card.bad div{text-decoration:line-through;color:#ffccd2}.lbsc-footer{padding:18px 26px 24px;background:#0f0d1a;border-top:1px solid rgba(133,116,190,.18)}.lbsc-actions{display:grid;grid-template-columns:auto 1fr auto;gap:12px;align-items:center}.lbsc-file-btn,.lbsc-send{height:48px;border-radius:14px;border:1px solid rgba(133,116,190,.24);cursor:pointer}.lbsc-file-btn{width:52px;background:#141124;color:#d9d5ff}.lbsc-send{min-width:176px;border:0;background:linear-gradient(135deg,#5f64f6,#873df4);color:white;font-weight:900}.lbsc-send:disabled{opacity:.45;cursor:not-allowed}.lbsc-file-name{min-width:0;color:#9f97bc;font-size:13px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}.lbsc-alert{display:none;margin:0 26px 12px;padding:12px 14px;border-radius:12px;background:rgba(239,68,68,.14);border:1px solid rgba(239,68,68,.24);color:#fecdd3}.lbsc-success{display:none;margin:0 26px 12px;padding:12px 14px;border-radius:12px;background:rgba(34,197,94,.13);border:1px solid rgba(34,197,94,.23);color:#bbf7d0}.lbsc-floating{position:fixed;right:26px;bottom:26px;z-index:9999;width:58px;height:58px;border-radius:50%;border:0;background:linear-gradient(135deg,#5f64f6,#873df4);color:white;box-shadow:0 16px 38px rgba(95,100,246,.35);cursor:pointer;font-size:21px}.lbsc-floating:disabled{opacity:.45;cursor:not-allowed}@media(max-width:720px){.lbsc-overlay{padding:0;align-items:flex-end}.lbsc-modal{width:100%;max-height:94vh;border-radius:22px 22px 0 0}.lbsc-head{padding:18px}.lbsc-name{font-size:18px}.lbsc-guide-grid{grid-template-columns:1fr}.lbsc-actions{grid-template-columns:auto 1fr}.lbsc-send{grid-column:1/-1;width:100%}}
</style>

<button type="button" class="lbsc-floating" data-seller-chat-open <?= !$chat_allowed ? 'disabled' : '' ?> title="<?= $h(function_exists('t') ? t('Message Seller') : 'Message Seller') ?>">
    <i class="fa-solid fa-comment-dots"></i>
</button>

<div class="lbsc-overlay" id="sellerChatModal" aria-hidden="true">
    <div class="lbsc-modal" role="dialog" aria-modal="true" aria-labelledby="lbscTitle">
        <div class="lbsc-head">
            <div class="lbsc-seller">
                <?php if ($seller_icon_raw): ?>
                    <img class="lbsc-avatar" src="<?= $h($seller_icon_raw) ?>" alt="">
                <?php else: ?>
                    <div class="lbsc-avatar-fallback"><?= $h($seller_initials) ?></div>
                <?php endif; ?>
                <div>
                    <div class="lbsc-name" id="lbscTitle"><?= $h((function_exists('t') ? t('Message') : 'Message') . ' ' . $seller_name_raw) ?></div>
                    <div class="lbsc-status"><span class="lbsc-dot"></span><?= $h(function_exists('t') ? t('Online') : 'Online') ?></div>
                </div>
            </div>
            <button type="button" class="lbsc-close" data-seller-chat-close aria-label="Close">&times;</button>
        </div>

        <form id="lbSellerChatForm" enctype="multipart/form-data" autocomplete="off">
            <div class="lbsc-body">
                <div class="lbsc-compose-top">
                    <label class="lbsc-label" for="lbScMsgInput"><?= $h(function_exists('t') ? t('Message') : 'Message') ?></label>
                    <textarea class="lbsc-textarea" id="lbScMsgInput" name="message" maxlength="1500" placeholder="<?= $h(function_exists('t') ? t('Ask the seller about product details, budget, or safety') : 'Ask the seller about product details, budget, or safety') ?>"></textarea>
                    <div class="lbsc-counter"><span id="lbscCount">0</span>/1500</div>
                    <div class="lbsc-online"><span class="lbsc-dot"></span><?= $h(function_exists('t') ? t('Online') : 'Online') ?></div>
                </div>

                <div class="lbsc-alert" id="lbscError"></div>
                <div class="lbsc-success" id="lbscSuccess"><?= $h(function_exists('t') ? t('Your message was sent to the seller.') : 'Your message was sent to the seller.') ?></div>

                <label class="lbsc-guidelines lbsc-agree">
                    <input type="checkbox" id="lbGuidelinesAgree">
                    <span><strong><?= $h(function_exists('t') ? t('I understand and agree to follow platform guidelines') : 'I understand and agree to follow platform guidelines') ?></strong><span><?= $h(function_exists('t') ? t('I will keep all communication within the platform and not share personal information or arrange off-platform payments.') : 'I will keep all communication within the platform and not share personal information or arrange off-platform payments.') ?></span></span>
                </label>

                <button type="button" class="lbsc-guide-toggle" id="lbGuidelinesToggle">⌃ <?= $h(function_exists('t') ? t('Hide Messaging Guidelines') : 'Hide Messaging Guidelines') ?></button>
                <div class="lbsc-guide-grid" id="lbGuidelinesBody">
                    <div class="lbsc-card good"><h4>✓ <?= $h(function_exists('t') ? t('Good Examples') : 'Good Examples') ?></h4><div>Can you tell me if this account has any restrictions or limitations?</div><div>Can you share more details about what’s included?</div><div>Does this account come with full access and original email?</div></div>
                    <div class="lbsc-card bad"><h4>✕ <?= $h(function_exists('t') ? t('Avoid These') : 'Avoid These') ?></h4><div>Hello, are you there?</div><div>Let’s talk over Telegram instead of this chat.</div><div>Can I pay after you deliver the item?</div></div>
                </div>
            </div>

            <div class="lbsc-footer">
                <input type="file" id="lbScClientImgInput" name="image" accept="image/*" hidden>
                <div class="lbsc-actions">
                    <button type="button" class="lbsc-file-btn" id="lbScImgBtn"><i class="fa-regular fa-image"></i></button>
                    <div class="lbsc-file-name" id="lbScImgName"><?= $h(function_exists('t') ? t('No file selected') : 'No file selected') ?></div>
                    <button type="submit" class="lbsc-send" id="lbScSendBtn" disabled><i class="fa-solid fa-paper-plane"></i> <?= $h(function_exists('t') ? t('Send Message') : 'Send Message') ?></button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
(function(){
    'use strict';
    if (window.__lbSellerChatInit) return;
    window.__lbSellerChatInit = true;

    const cfg = {
        sellerId: <?= json_encode($seller_id) ?>,
        refType: <?= json_encode($ref_type) ?>,
        refId: <?= json_encode($ref_id) ?>,
        ajaxUrl: <?= json_encode($ajax_url) ?>,
        allowed: <?= $chat_allowed ? 'true' : 'false' ?>
    };

    function ready(fn){ document.readyState !== 'loading' ? fn() : document.addEventListener('DOMContentLoaded', fn); }

    ready(function(){
        const modal = document.getElementById('sellerChatModal');
        const form = document.getElementById('lbSellerChatForm');
        const input = document.getElementById('lbScMsgInput');
        const send = document.getElementById('lbScSendBtn');
        const agree = document.getElementById('lbGuidelinesAgree');
        const file = document.getElementById('lbScClientImgInput');
        const fileBtn = document.getElementById('lbScImgBtn');
        const fileName = document.getElementById('lbScImgName');
        const count = document.getElementById('lbscCount');
        const error = document.getElementById('lbscError');
        const success = document.getElementById('lbscSuccess');
        const guideToggle = document.getElementById('lbGuidelinesToggle');
        const guideBody = document.getElementById('lbGuidelinesBody');
        if (!modal || !form || !cfg.allowed) return;

        function openModal(){ modal.classList.add('is-open'); modal.setAttribute('aria-hidden','false'); document.documentElement.classList.add('lbsc-lock'); setTimeout(function(){ input && input.focus(); }, 50); }
        function closeModal(){ modal.classList.remove('is-open'); modal.setAttribute('aria-hidden','true'); document.documentElement.classList.remove('lbsc-lock'); }
        function showError(msg){ error.textContent = msg || 'Something went wrong.'; error.style.display = 'block'; success.style.display = 'none'; }
        function clearMsg(){ error.style.display = 'none'; success.style.display = 'none'; }
        function updateSend(){ send.disabled = !(agree && agree.checked) || (!input.value.trim() && !(file && file.files && file.files.length)); }

        document.addEventListener('click', function(e){
            const opener = e.target.closest('[data-seller-chat-open], [data-bs-target="#sellerChatModal"], [data-target="#sellerChatModal"], #lbSellerChatTrigger');
            if (opener) { e.preventDefault(); e.stopPropagation(); openModal(); return; }
            if (e.target.closest('[data-seller-chat-close]')) { e.preventDefault(); closeModal(); }
        }, true);

        modal.addEventListener('click', function(e){ if (e.target === modal) closeModal(); });
        document.addEventListener('keydown', function(e){ if (e.key === 'Escape' && modal.classList.contains('is-open')) closeModal(); });
        if (agree) agree.addEventListener('change', updateSend);
        if (input) input.addEventListener('input', function(){ count.textContent = input.value.length; updateSend(); clearMsg(); });
        if (fileBtn && file) fileBtn.addEventListener('click', function(){ file.click(); });
        if (file) file.addEventListener('change', function(){ fileName.textContent = file.files && file.files[0] ? file.files[0].name : <?= json_encode(function_exists('t') ? t('No file selected') : 'No file selected') ?>; updateSend(); });
        if (guideToggle && guideBody) guideToggle.addEventListener('click', function(){ const hidden = guideBody.style.display === 'none'; guideBody.style.display = hidden ? 'grid' : 'none'; guideToggle.textContent = (hidden ? '⌃ ' : '⌄ ') + (hidden ? <?= json_encode(function_exists('t') ? t('Hide Messaging Guidelines') : 'Hide Messaging Guidelines') ?> : <?= json_encode(function_exists('t') ? t('Show Messaging Guidelines') : 'Show Messaging Guidelines') ?>); });

        form.addEventListener('submit', function(e){
            e.preventDefault();
            clearMsg();
            if (!agree.checked) return showError(<?= json_encode(function_exists('t') ? t('Please accept the messaging guidelines first.') : 'Please accept the messaging guidelines first.') ?>);
            if (!input.value.trim() && !(file.files && file.files.length)) return showError(<?= json_encode(function_exists('t') ? t('Please enter a message or choose an image.') : 'Please enter a message or choose an image.') ?>);

            const fd = new FormData();
            fd.append('action', 'client_seller_chat_send');
            fd.append('seller_id', cfg.sellerId);
            fd.append('ref_type', cfg.refType);
            fd.append('ref_id', cfg.refId);
            fd.append('message', input.value.trim());
            if (file.files && file.files[0]) fd.append('image', file.files[0]);
            send.disabled = true;

            fetch(cfg.ajaxUrl, { method:'POST', body:fd, credentials:'same-origin' })
                .then(function(r){ return r.json().catch(function(){ return {success:false,message:'Invalid server response'}; }); })
                .then(function(res){
                    if (!res || res.success === false || res.status === 'error') throw new Error(res && (res.message || res.error) ? (res.message || res.error) : 'Message could not be sent.');
                    input.value = ''; count.textContent = '0'; if (file) file.value = ''; fileName.textContent = <?= json_encode(function_exists('t') ? t('No file selected') : 'No file selected') ?>; success.style.display = 'block';
                    setTimeout(closeModal, 900);
                })
                .catch(function(err){ showError(err.message); })
                .finally(updateSend);
        });

        updateSend();
    });
})();
</script>
