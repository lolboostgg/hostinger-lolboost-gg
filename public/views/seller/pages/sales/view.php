<?= $this->layout('seller/layouts/main', ['meta' => ['title' => 'Sale #' . ($sale['id'] ?? '') . ' - Seller Area | LoLBoost.gg']]) ?>

<?php
  $sale = $sale ?? [];
  $client = $client ?? null;
  $invoice = $invoice ?? null;
  $sale_id = (int)($sale['id'] ?? 0);

  $price_cents = (int)($invoice['total_price'] ?? $invoice['price'] ?? 0);
  $currency = $invoice['currency'] ?? 'EUR';
?>

<div class="row g-3">
    <div class="col-12 col-lg-5">
        <div class="card">
            <div class="card-header">
                <h5 class="card-header-title mb-0">Sale details</h5>
            </div>
            <div class="card-body">
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted">Account ID</span>
                    <span class="fw-semibold">#<?= (int)($sale['id'] ?? 0) ?></span>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted">Package</span>
                    <span class="fw-semibold"><?= esc($sale['package_name'] ?? '—') ?></span>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted">Sold at</span>
                    <span class="fw-semibold"><?= esc($sale['sold_at'] ?? '—') ?></span>
                </div>
                <div class="d-flex justify-content-between mb-3">
                    <span class="text-muted">Price</span>
                    <span class="fw-semibold"><?= $price_cents ? util_format_price_display($price_cents) . ' ' . esc($currency) : '—' ?></span>
                </div>

                <hr>

                <h6 class="mb-3">Buyer</h6>
                <?php if (!empty($client)): ?>
                    <div class="d-flex align-items-center">
                        <div class="avatar avatar-sm avatar-circle">
                            <?php $cicon = !empty($client['icon']) ? $client['icon'] : (ASSET_URL . '/core/main/img/logos/SVG/icon.svg'); ?>
                            <img class="avatar-img" src="<?= $cicon ?>" alt="<?= esc($client['username']) ?>">
                        </div>
                        <div class="ms-3">
                            <div class="fw-semibold"><?= esc($client['username']) ?></div>
                            <div class="small text-muted">#<?= (int)$client['id'] ?><?= !empty($client['email']) ? ' · ' . esc($client['email']) : '' ?></div>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="text-muted">No buyer linked.</div>
                <?php endif; ?>

                <hr>

                <h6 class="mb-3">Account credentials</h6>
                <div class="mb-2"><span class="text-muted">Login:</span> <span class="fw-semibold ms-2"><?= esc($sale['login'] ?? '') ?></span></div>
                <div class="mb-2"><span class="text-muted">Password:</span> <span class="fw-semibold ms-2"><?= esc($sale['password'] ?? '') ?></span></div>
                <?php if (!empty($sale['data'])): ?>
                    <div class="mb-0"><span class="text-muted">Notes:</span> <span class="ms-2"><?= nl2br(esc($sale['data'])) ?></span></div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="col-12 col-lg-7">
        <div class="card h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-header-title mb-0">Chat with buyer</h5>
                <button class="btn btn-soft-primary btn-sm" id="lbRefreshChat" type="button">
                    <i class="fa-duotone fa-rotate-right"></i>
                </button>
            </div>
            <div class="card-body d-flex flex-column" style="min-height: 540px;">
                <div id="lbChatBox" class="flex-grow-1 overflow-auto p-2" style="background: rgba(255,255,255,.02); border: 1px solid rgba(255,255,255,.08); border-radius: 12px;">
                    <div class="text-muted small">Loading messages…</div>
                </div>

                <form class="ajax-form mt-3" novalidate action="<?= AJAX_URL ?>" id="lbChatForm">
                    <input type="hidden" name="action" value="seller_sale_chat_send">
                    <input type="hidden" name="sale_id" value="<?= $sale_id ?>">

                    <div class="input-group">
                        <input type="text" class="form-control" name="message" id="lbChatMessage" placeholder="Type a message…" required>
                        <button class="btn btn-primary" type="submit">
                            <span class="indicator-label">Send</span>
                            <span class="indicator-progress"><span class="spinner-border spinner-border-sm align-middle"></span></span>
                            <span class="indicator-success"><i class="fa-regular fa-circle-check fs-5"></i></span>
                        </button>
                    </div>
                </form>

                <div class="small text-muted mt-2">Only the last 50 messages are loaded for performance.</div>
            </div>
        </div>
    </div>
</div>

<?= $this->start('scripts') ?>
<script>
    (function () {
        const SALE_ID = <?= (int)$sale_id ?>;
        const $box = document.getElementById('lbChatBox');
        const $refresh = document.getElementById('lbRefreshChat');
        const $msg = document.getElementById('lbChatMessage');

        function escHtml(str) {
            return String(str ?? '')
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#039;');
        }

        function render(messages) {
            if (!Array.isArray(messages) || messages.length === 0) {
                $box.innerHTML = '<div class="text-muted small">No messages yet.</div>';
                return;
            }

            let html = '';
            for (const m of messages) {
                const sender = escHtml(m.sender_name || m.sender || 'User');
                const time = m.time ? new Date(m.time * 1000).toLocaleString() : '';
                const content = m.content ? m.content : escHtml(m.raw || '');

                const isMe = (m.sender === 'seller');
                html += `
                  <div class="d-flex ${isMe ? 'justify-content-end' : 'justify-content-start'} mb-2">
                    <div class="p-2 ${isMe ? 'bg-primary text-white' : 'bg-light'}" style="max-width: 85%; border-radius: 12px;">
                      <div class="small ${isMe ? 'text-white-50' : 'text-muted'} d-flex justify-content-between gap-3">
                        <span>${sender}</span>
                        <span>${escHtml(time)}</span>
                      </div>
                      <div class="mt-1" style="word-break: break-word;">${content}</div>
                    </div>
                  </div>
                `;
            }
            $box.innerHTML = html;
            $box.scrollTop = $box.scrollHeight;
        }

        function load() {
            $.post('<?= AJAX_URL ?>', { action: 'sale_chat_get_messages', sale_id: SALE_ID, limit: 50 }, function (res) {
                try {
                    if (typeof res === 'string') res = JSON.parse(res);
                } catch (e) {}
                render(res && res.success ? res.messages : []);
            });
        }

        // validate + ajax submit
        HSBsValidation.init('#lbChatForm', {
            onSubmit: data => {
                data.event.preventDefault();
                $('#lbChatForm').submit(data);
            }
        });

        $refresh.addEventListener('click', load);
        load();
        setInterval(load, 6000);
    })();
</script>
<?= $this->end() ?>
