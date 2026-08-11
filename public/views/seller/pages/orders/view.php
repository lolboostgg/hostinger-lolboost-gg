<?= $this->layout('seller/layouts/main', ['meta' => $meta]) ?>

<div class="row g-4">
  <div class="col-12 col-lg-5">
    <div class="card">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="card-header-title mb-0">Order #<?= htmlspecialchars($order['id'] ?? '-') ?></h5>
        <a class="btn btn-white btn-sm" href="<?= BASE_URL ?>/seller-area/orders">Back</a>
      </div>
      <div class="card-body">
        <div class="mb-2"><span class="text-muted">Client:</span> <?= htmlspecialchars($order['client_username'] ?? $order['client_email'] ?? '-') ?></div>
        <div class="mb-2"><span class="text-muted">Total:</span> <?= number_format(((int)($order['total_cents'] ?? 0))/100, 2) ?> €</div>
        <div class="mb-2"><span class="text-muted">Status:</span> <?= htmlspecialchars($order['status'] ?? '-') ?></div>
        <div class="mb-2"><span class="text-muted">Created:</span> <?= htmlspecialchars($order['created_at'] ?? '-') ?></div>
      </div>
    </div>
  </div>

  <div class="col-12 col-lg-7">
    <div class="card">
      <div class="card-header">
        <h5 class="card-header-title mb-0">Conversation</h5>
      </div>
      <div class="card-body" style="max-height: 420px; overflow:auto;">
        <?php if (empty($messages)) : ?>
          <div class="text-muted">No messages yet.</div>
        <?php else : ?>
          <?php foreach ($messages as $m) : ?>
            <div class="mb-3">
              <div class="small text-muted">
                <?= htmlspecialchars(($m['sender_type'] ?? 'user')) ?> • <?= htmlspecialchars($m['created_at'] ?? '') ?>
              </div>
              <div class="p-2 rounded bg-soft-dark">
                <?= nl2br(htmlspecialchars($m['message'] ?? '')) ?>
              </div>
            </div>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>
      <div class="card-footer">
        <form class="js-send-message d-flex gap-2" method="post" action="<?= BASE_URL ?>/app/core/ajax.php?action=order_send_message">
          <input type="hidden" name="conversation_id" value="<?= (int)($conversation['id'] ?? 0) ?>">
          <input class="form-control" name="message" placeholder="Write a message..." required>
          <button class="btn btn-primary" type="submit">Send</button>
        </form>
      </div>
    </div>
  </div>
</div>

<?= $this->start('scripts') ?>
<script>
document.querySelectorAll('.js-send-message').forEach(form=>{
  form.addEventListener('submit', async (e)=>{
    e.preventDefault();
    const res = await fetch(form.action, {method:'POST', body:new FormData(form)});
    const data = await res.json();
    if (data.redirectUrl) window.location.href = data.redirectUrl;
    else if (data.refreshPage) window.location.reload();
  });
});
</script>
<?php $this->end(); ?>
