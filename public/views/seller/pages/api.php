<?= $this->layout('seller/layouts/main', ['meta' => $meta ?? ['title' => 'Partner API - Seller Area | LoLBoost.gg']]) ?>

<?php
$api_keys     = $api_keys ?? [];
$webhooks     = $webhooks ?? [];
$webhook_logs = $webhook_logs ?? [];
$api_logs     = $api_logs ?? [];
$api_stats    = $api_stats ?? ['requests_24h' => 0, 'requests_7d' => 0, 'errors_7d' => 0];
$base = 'https://api.lolboost.gg';
?>

<div class="content container-fluid partner-api-v2">

  <!-- ══ HERO ══ -->
  <div class="pa-hero card mb-4">
    <div class="card-body p-4 p-lg-5">
      <div class="pa-hero-grid">
        <div>
          <div class="pa-eyebrow"><i class="fa-duotone fa-code"></i> Seller Integration</div>
          <h1 class="pa-title">Partner API</h1>
          <p class="pa-subtitle">List accounts and items from external seller systems, sync stock automatically and receive instant webhooks after sales or removals.</p>
        </div>
        <div class="pa-anti-card">
          <div class="pa-anti-icon"><i class="fa-duotone fa-shield-check"></i></div>
          <div>
            <div class="pa-anti-title">Anti double sale</div>
            <div class="pa-anti-text">Sold accounts and items get reported instantly by external_id.</div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <?php if (empty($api_keys)): ?>
    <div class="pa-empty-card card mb-4">
      <div class="card-body">
        <div class="pa-empty-icon"><i class="fa-duotone fa-key"></i></div>
        <h2>No API Keys Yet</h2>
        <p>No API keys have been created yet. Create one to start using the LoLBoost Partner API.</p>
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#key-modal"><i class="fa-regular fa-plus me-1"></i> New API Key</button>
      </div>
    </div>
  <?php endif; ?>

  <!-- ══ API KEYS ══ -->
  <?php if (!empty($api_keys)): ?>
    <div class="card mb-4">
      <div class="card-header">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
          <h5 class="card-header-title mb-0">API Keys</h5>
          <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#key-modal"><i class="fa-regular fa-plus me-1"></i> New API Key</button>
        </div>
      </div>
      <div class="table-responsive">
        <table class="table table-borderless table-thead-bordered table-nowrap table-align-middle card-table mb-0">
          <thead class="thead-light">
            <tr>
              <th>Name</th>
              <th>Key</th>
              <th>Abilities</th>
              <th>Last used</th>
              <th>Created</th>
              <th class="text-end">Action</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($api_keys as $key): ?>
              <tr>
                <td class="fw-bold"><?= esc($key['name'] ?? 'API Key') ?></td>
                <td><code><?= esc($key['key_prefix'] ?? '') ?>|**********</code></td>
                <td><span class="pa-pill pa-pill-purple">Full Access</span></td>
                <td><?= !empty($key['last_used_at']) ? esc($key['last_used_at']) : 'Never' ?></td>
                <td><?= !empty($key['created_at']) ? esc(date('d.m.Y H:i', strtotime($key['created_at']))) : 'Recently' ?></td>
                <td class="text-end">
                  <?php if (!empty($key['is_active'])): ?>
                    <form class="ajax-form d-inline" action="<?= AJAX_URL ?>" method="POST" onsubmit="return confirm('Disable this API key? Apps using it will lose access immediately.');">
                      <input type="hidden" name="action" value="seller_api_delete_key">
                      <input type="hidden" name="id" value="<?= (int)$key['id'] ?>">
                      <button type="submit" class="btn btn-sm btn-outline-danger" title="Disable key"><i class="fa-regular fa-trash-can"></i></button>
                    </form>
                  <?php else: ?>
                    <span class="pa-pill pa-pill-muted">Disabled</span>
                  <?php endif; ?>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  <?php endif; ?>

  <div class="row g-4 mb-4">

    <!-- ══ WEBHOOK ══ -->
    <div class="col-12 col-xl-6">
      <div class="card h-100">
        <div class="card-header">
          <h5 class="card-header-title mb-0">Sold Webhook</h5>
        </div>
        <div class="card-body">
          <form class="ajax-form" action="<?= AJAX_URL ?>" method="POST">
            <input type="hidden" name="action" value="seller_api_create_webhook">
            <label class="form-label">HTTPS Webhook URL</label>
            <div class="d-flex gap-2 flex-wrap">
              <input class="form-control" style="min-width:0;flex:1 1 220px;" name="url" placeholder="https://seller-domain.com/lolboost/webhook" required>
              <button class="btn btn-primary" type="submit">Save webhook</button>
            </div>
            <div class="form-text">Events sent: <code>account.sold</code>, <code>item.sold</code>, created, listed and archived events.</div>
          </form>

          <div class="table-responsive mt-4">
            <table class="table table-borderless table-thead-bordered table-nowrap table-align-middle mb-0">
              <thead class="thead-light">
                <tr><th>URL</th><th style="width:110px;">Status</th><th class="text-end" style="width:54px;"></th></tr>
              </thead>
              <tbody>
                <?php foreach ($webhooks as $hook): ?>
                  <tr>
                    <td class="pa-url"><?= esc($hook['url'] ?? '') ?></td>
                    <td><?= !empty($hook['is_active']) ? '<span class="pa-pill pa-pill-green">Active</span>' : '<span class="pa-pill pa-pill-muted">Disabled</span>' ?></td>
                    <td class="text-end">
                      <?php if (!empty($hook['is_active'])): ?>
                        <form class="ajax-form d-inline" action="<?= AJAX_URL ?>" method="POST" onsubmit="return confirm('Delete this webhook?');">
                          <input type="hidden" name="action" value="seller_api_delete_webhook">
                          <input type="hidden" name="id" value="<?= (int)$hook['id'] ?>">
                          <button type="submit" class="btn btn-sm btn-outline-danger"><i class="fa-regular fa-trash-can"></i></button>
                        </form>
                      <?php endif; ?>
                    </td>
                  </tr>
                <?php endforeach; ?>
                <?php if (empty($webhooks)): ?>
                  <tr><td colspan="3" class="text-center text-muted py-4">No webhook configured yet.</td></tr>
                <?php endif; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>

    <!-- ══ USAGE (stats only — the full log lives in "Activity" below, no need to repeat it here) ══ -->
    <div class="col-12 col-xl-6">
      <div class="card h-100">
        <div class="card-header">
          <h5 class="card-header-title mb-0">API Usage</h5>
        </div>
        <div class="card-body">
          <div class="pa-stat-grid">
            <div class="pa-stat">
              <span>Last 24h</span>
              <strong><?= (int)($api_stats['requests_24h'] ?? 0) ?></strong>
            </div>
            <div class="pa-stat">
              <span>Last 7 days</span>
              <strong><?= (int)($api_stats['requests_7d'] ?? 0) ?></strong>
            </div>
            <div class="pa-stat <?= ((int)($api_stats['errors_7d'] ?? 0) > 0) ? 'pa-stat-danger' : '' ?>">
              <span>Errors (7d)</span>
              <strong><?= (int)($api_stats['errors_7d'] ?? 0) ?></strong>
            </div>
          </div>

          <?php if (empty($api_logs)): ?>
            <div class="pa-empty-usage mt-3">
              <div class="pa-empty-icon"><i class="fa-duotone fa-chart-column"></i></div>
              <h2>No API Requests</h2>
              <p>You have not made any API requests yet.</p>
            </div>
          <?php else: ?>
            <div class="form-text mt-3 mb-0">Full request history is under <strong>Activity → API Requests</strong> below.</div>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>

  <!-- ══ DOCUMENTATION (tabbed — one example visible at a time) ══ -->
  <div class="card mb-4">
    <div class="card-header">
      <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div>
          <h5 class="card-header-title mb-0">API Documentation</h5>
          <div class="form-text mb-0">Full API documentation is available on docs.lolboost.gg.</div>
        </div>
        <div class="d-flex align-items-center gap-2 flex-wrap">
          <a class="btn btn-primary btn-sm" href="https://docs.lolboost.gg/api-reference/accounts/" target="_blank" rel="noopener">
            <i class="fa-regular fa-arrow-up-right-from-square me-1"></i> Open API Documentation
          </a>
          <div class="pa-tabs" id="pa-doc-tabs">
          <button type="button" class="active" data-doc="create">Create Account</button>
          <button type="button" data-doc="items-create">Create Item</button>
          <button type="button" data-doc="list">List</button>
          <button type="button" data-doc="update">Update</button>
          <button type="button" data-doc="delete">Delete</button>
          <button type="button" data-doc="bulk">Bulk</button>
            <button type="button" data-doc="webhook">Webhook</button>
          </div>
        </div>
      </div>
    </div>
    <div class="card-body">

      <div class="pa-doc-pane" data-doc-pane="create">
        <div class="pa-code-title">Create account offer</div>
<pre class="pa-code"><code>POST <?= esc($base) ?>/api/v1/accounts
Authorization: Bearer YOUR_API_KEY
Content-Type: application/json

{
  "external_id": "seller-account-123",
  "title": "League of Legends Account Diamond EUW",
  "game": "lol",
  "server": "EUW",
  "price": 4999,
  "status": "listed",
  "parameters": {
    "level": 30,
    "rank": "Diamond"
  },
  "credentials": {
    "login": "account-login",
    "password": "account-password",
    "email_login": "mail@example.com",
    "email_password": "mail-password"
  }
}</code></pre>
      </div>

      <div class="pa-doc-pane d-none" data-doc-pane="items-create">
        <div class="pa-code-title">Create item offer</div>
<pre class="pa-code"><code>POST <?= esc($base) ?>/api/v1/items
Authorization: Bearer YOUR_API_KEY
Content-Type: application/json

{
  "external_id": "seller-item-123",
  "title": "Event Pass EUW",
  "game": "league-of-legends",
  "type": "event-pass",
  "server": "EUW",
  "price": 949,
  "stock": 25,
  "status": "listed",
  "image_urls": [
    "https://example.com/item-image.png"
  ]
}</code></pre>
      </div>

      <div class="pa-doc-pane d-none" data-doc-pane="list">
        <div class="pa-code-title">List account offers</div>
<pre class="pa-code"><code>GET <?= esc($base) ?>/api/v1/accounts?filter[status]=listed&sort=-updated_at&per_page=15
Authorization: Bearer YOUR_API_KEY</code></pre>
        <div class="pa-code-title mt-4">List item offers</div>
<pre class="pa-code"><code>GET <?= esc($base) ?>/api/v1/items?filter[status]=listed&sort=-updated_at&per_page=15
Authorization: Bearer YOUR_API_KEY</code></pre>
        <div class="pa-code-title mt-4">Find by external ID</div>
<pre class="pa-code"><code>GET <?= esc($base) ?>/api/v1/accounts?filter[external_id]=seller-account-123
GET <?= esc($base) ?>/api/v1/items?filter[external_id]=seller-item-123
Authorization: Bearer YOUR_API_KEY</code></pre>
      </div>

      <div class="pa-doc-pane d-none" data-doc-pane="update">
        <div class="pa-code-title">Update account offer</div>
<pre class="pa-code"><code>PATCH <?= esc($base) ?>/api/v1/accounts/seller-account-123
Authorization: Bearer YOUR_API_KEY
Content-Type: application/json

{
  "price": 5999,
  "status": "listed"
}</code></pre>
        <div class="pa-code-title mt-4">Update item stock</div>
<pre class="pa-code"><code>PATCH <?= esc($base) ?>/api/v1/items/seller-item-123
Authorization: Bearer YOUR_API_KEY
Content-Type: application/json

{
  "price": 1199,
  "stock": 50,
  "status": "listed"
}</code></pre>
      </div>

      <div class="pa-doc-pane d-none" data-doc-pane="delete">
        <div class="pa-code-title">Archive account offer</div>
<pre class="pa-code"><code>DELETE <?= esc($base) ?>/api/v1/accounts/seller-account-123
Authorization: Bearer YOUR_API_KEY</code></pre>
        <div class="pa-code-title mt-4">Archive item offer</div>
<pre class="pa-code"><code>DELETE <?= esc($base) ?>/api/v1/items/seller-item-123
Authorization: Bearer YOUR_API_KEY</code></pre>
      </div>

      <div class="pa-doc-pane d-none" data-doc-pane="bulk">
        <div class="pa-code-title">Bulk remove accounts</div>
<pre class="pa-code"><code>POST <?= esc($base) ?>/api/v1/accounts/bulk-remove
Authorization: Bearer YOUR_API_KEY
Content-Type: application/json

{
  "external_ids": ["seller-account-1", "seller-account-2"]
}</code></pre>
        <div class="pa-code-title mt-4">Bulk remove items</div>
<pre class="pa-code"><code>POST <?= esc($base) ?>/api/v1/items/bulk-remove
Authorization: Bearer YOUR_API_KEY
Content-Type: application/json

{
  "external_ids": ["seller-item-1", "seller-item-2"]
}</code></pre>
        <div class="pa-code-title mt-4">Bulk update item stock</div>
<pre class="pa-code"><code>POST <?= esc($base) ?>/api/v1/items/bulk-update
Authorization: Bearer YOUR_API_KEY
Content-Type: application/json

{
  "items": [
    {"external_id": "seller-item-1", "stock": 10, "price": 949},
    {"external_id": "seller-item-2", "stock": 0, "status": "archived"}
  ]
}</code></pre>
      </div>

      <div class="pa-doc-pane d-none" data-doc-pane="webhook">
        <div class="pa-code-title">account.sold webhook payload</div>
<pre class="pa-code"><code>{
  "event": "account.sold",
  "external_id": "seller-account-123",
  "lolboost_account_id": 123,
  "sold_at": "2026-06-19 15:30:00"
}</code></pre>
        <div class="pa-code-title mt-4">item.sold webhook payload</div>
<pre class="pa-code"><code>{
  "event": "item.sold",
  "external_id": "seller-item-123",
  "lolboost_item_id": 456,
  "quantity": 1,
  "sold_at": "2026-06-19 15:30:00"
}</code></pre>
        <div class="pa-code-title mt-4">created and archived events</div>
<pre class="pa-code"><code>account.created, account.listed, account.archived
item.created, item.listed, item.updated, item.archived</code></pre>
      </div>

    </div>
  </div>

  <!-- ══ ACTIVITY (tabbed — API requests / webhook deliveries, no more duplicated tables) ══ -->
  <div class="card mb-4">
    <div class="card-header">
      <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
        <h5 class="card-header-title mb-0">Activity</h5>
        <div class="pa-tabs" id="pa-activity-tabs">
          <button type="button" class="active" data-activity="requests">API Requests</button>
          <button type="button" data-activity="webhooks">Webhook Deliveries</button>
        </div>
      </div>
    </div>

    <div class="table-responsive pa-activity-pane" data-activity-pane="requests">
      <table class="table table-borderless table-thead-bordered table-nowrap table-align-middle card-table mb-0">
        <thead class="thead-light">
          <tr><th>Date</th><th>Method</th><th>Endpoint</th><th>Type</th><th>Account</th><th>Item</th><th>External ID</th><th>HTTP</th><th>Duration</th></tr>
        </thead>
        <tbody>
          <?php foreach ($api_logs as $log): ?>
            <tr>
              <td><?= esc($log['created_at'] ?? '') ?></td>
              <td><span class="pa-pill pa-pill-purple"><?= esc($log['method'] ?? '') ?></span></td>
              <td class="pa-url"><code><?= esc($log['endpoint'] ?? '') ?></code></td>
              <td><?= esc($log['entity_type'] ?? 'account') ?></td>
              <td><?= !empty($log['account_id']) ? '#' . (int)$log['account_id'] : '-' ?></td>
              <td><?= !empty($log['item_id']) ? '#' . (int)$log['item_id'] : '-' ?></td>
              <td><code><?= esc($log['external_id'] ?? '') ?></code></td>
              <td><?= ((int)($log['status_code'] ?? 0) >= 400) ? '<span class="pa-pill pa-pill-red">' . (int)$log['status_code'] . '</span>' : '<span class="pa-pill pa-pill-green">' . (int)$log['status_code'] . '</span>' ?></td>
              <td><?= esc($log['duration_ms'] ?? '') ?> ms</td>
            </tr>
          <?php endforeach; ?>
          <?php if (empty($api_logs)): ?>
            <tr><td colspan="9" class="text-center text-muted py-4">No API request logs yet.</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>

    <div class="table-responsive pa-activity-pane d-none" data-activity-pane="webhooks">
      <table class="table table-borderless table-thead-bordered table-nowrap table-align-middle card-table mb-0">
        <thead class="thead-light">
          <tr><th>Date</th><th>Event</th><th>Type</th><th>Account</th><th>Item</th><th>HTTP</th><th>Status</th></tr>
        </thead>
        <tbody>
          <?php foreach ($webhook_logs as $log): ?>
            <tr>
              <td><?= esc($log['created_at'] ?? '') ?></td>
              <td><code><?= esc($log['event'] ?? '') ?></code></td>
              <td><?= esc($log['entity_type'] ?? 'account') ?></td>
              <td><?= !empty($log['account_id']) ? '#' . (int)$log['account_id'] : '-' ?></td>
              <td><?= !empty($log['item_id']) ? '#' . (int)$log['item_id'] : '-' ?></td>
              <td><?= esc($log['response_code'] ?? '') ?></td>
              <td><?= !empty($log['success']) ? '<span class="pa-pill pa-pill-green">Success</span>' : '<span class="pa-pill pa-pill-red">Failed</span>' ?></td>
            </tr>
          <?php endforeach; ?>
          <?php if (empty($webhook_logs)): ?>
            <tr><td colspan="7" class="text-center text-muted py-4">No webhook logs yet.</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<!-- ══ CREATE API KEY MODAL ══ -->
<!-- NOTE: intentionally NOT class="ajax-form" — this form needs its own two-step
     handling (show the generated key before reload), so it's wired up manually
     below. Adding "ajax-form" here would make the global handler fire a second,
     parallel request and surface a stray "Request failed" toast on top of the
     correct one. -->
<form id="create-api-key-form" action="<?= AJAX_URL ?>" method="POST">
  <input type="hidden" name="action" value="seller_api_create_key">
  <div class="modal fade" id="key-modal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width:560px;width:calc(100% - 2rem);">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="key-modal-title"><i class="fa-duotone fa-key me-2"></i>Create API Key</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">

          <div id="key-create-step">
            <label class="form-label" for="new-api-key-name">API Key name</label>
            <input class="form-control" id="new-api-key-name" name="name" placeholder="e.g. API Key 1" required>
            <div class="form-text">This will help you identify the key in your dashboard.</div>

            <label class="pa-check-row mt-4">
              <input type="checkbox" class="pa-chk" id="pa-terms-check" required>
              <span>By checking this box, I agree to the terms below.</span>
            </label>
            <div class="pa-terms mt-3">
              <div><i class="fa-solid fa-check"></i> I will keep my API Key secure and not share it with anyone else.</div>
              <div><i class="fa-solid fa-check"></i> I will use the LoLBoost API only for legitimate purposes.</div>
              <div><i class="fa-solid fa-check"></i> I understand that I am responsible for all actions taken using my API Key.</div>
              <div><i class="fa-solid fa-check"></i> I agree that LoLBoost may monitor API usage and revoke access after abuse.</div>
            </div>
          </div>

          <div class="d-none" id="key-result-step">
            <div class="pa-key-warning">
              <i class="fa-solid fa-triangle-exclamation"></i>
              <span>Copy and safely store your API Key now — it will only be shown once.</span>
            </div>
            <div class="pa-key-copy">
              <code id="new-api-key"></code>
              <button type="button" class="pa-key-copy-btn" id="copy-api-key" title="Copy to clipboard">
                <i class="fa-regular fa-copy"></i>
              </button>
            </div>
          </div>

        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-light" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary" id="create-token-btn"><i class="fa-regular fa-plus me-1"></i> Create Token</button>
          <button type="button" class="btn btn-primary d-none" id="confirm-token-btn" data-bs-dismiss="modal"><i class="fa-regular fa-check me-1"></i> Confirm</button>
        </div>
      </div>
    </div>
  </div>
</form>

<style>
/* ════════════════════════════════════════════
   PARTNER API — aligned with global seller-area
   design tokens (purple brand, dark cards)
   ════════════════════════════════════════════ */
.partner-api-v2 {
  --pa-purple:  #6d5cff;
  --pa-purple2: #b05cff;
  --pa-green:   #4ade80;
  --pa-red:     #fb7185;
}

/* Hero */
.pa-hero {
  background:
    radial-gradient(circle at 10% 0%, rgba(109,92,255,.18), transparent 28%),
    radial-gradient(circle at 96% 100%, rgba(176,92,255,.12), transparent 26%),
    var(--bs-card-bg) !important;
}
.pa-hero-grid { display: flex; align-items: center; justify-content: space-between; gap: 24px; }
.pa-eyebrow {
  display: flex; align-items: center; gap: 8px; color: #a78fff;
  font-size: .78rem; font-weight: 800; text-transform: uppercase; letter-spacing: .12em; margin-bottom: 12px;
}
.pa-title { font-size: clamp(1.8rem,4vw,2.6rem); line-height: 1.1; margin: 0 0 14px; font-weight: 800; }
.pa-subtitle { font-size: 1rem; color: rgba(255,255,255,.62); max-width: 720px; margin: 0; }
.pa-subtitle code { color: #c4b5fd; }
.pa-anti-card {
  min-width: 280px; display: flex; gap: 14px; align-items: center; padding: 18px;
  border-radius: 14px; border: 1px solid rgba(109,92,255,.28);
  background: linear-gradient(135deg, rgba(109,92,255,.16), rgba(255,255,255,.025));
}
.pa-anti-icon {
  width: 44px; height: 44px; border-radius: 12px; background: rgba(109,92,255,.22);
  display: flex; align-items: center; justify-content: center; color: #c4b5fd; font-size: 1.3rem; flex-shrink: 0;
}
.pa-anti-title { font-weight: 800; }
.pa-anti-text { font-size: .82rem; color: rgba(255,255,255,.55); margin-top: 2px; }

/* Empty states */
.pa-empty-card .card-body, .pa-empty-usage {
  min-height: 320px; display: flex; flex-direction: column; align-items: center; justify-content: center;
  text-align: center; padding: 44px;
}
.pa-empty-usage { min-height: 200px; border-radius: 14px; border: 1px solid rgba(255,255,255,.06); background: rgba(255,255,255,.02); padding: 28px; }
.pa-empty-icon { font-size: 2.6rem; color: rgba(255,255,255,.20); margin-bottom: 18px; }
.pa-empty-usage .pa-empty-icon { font-size: 2rem; margin-bottom: 10px; }
.pa-empty-card h2, .pa-empty-usage h2 { font-size: 1.4rem; font-weight: 800; margin: 0 0 12px; }
.pa-empty-usage h2 { font-size: 1.05rem; margin-bottom: 6px; }
.pa-empty-card p, .pa-empty-usage p { color: rgba(255,255,255,.55); font-size: .95rem; max-width: 480px; margin: 0 0 24px; }
.pa-empty-usage p { font-size: .85rem; margin-bottom: 0; }

/* Pills (consistent with other seller-area status badges) */
.pa-pill { display: inline-flex; align-items: center; padding: 4px 10px; border-radius: 999px; font-size: .74rem; font-weight: 800; border: 1px solid rgba(255,255,255,.10); background: rgba(255,255,255,.06); color: rgba(255,255,255,.75); }
.pa-pill-purple { background: rgba(109,92,255,.14); border-color: rgba(109,92,255,.28); color: #a78fff; }
.pa-pill-green  { background: rgba(74,222,128,.12); border-color: rgba(74,222,128,.24); color: var(--pa-green); }
.pa-pill-red    { background: rgba(251,113,133,.12); border-color: rgba(251,113,133,.24); color: var(--pa-red); }
.pa-pill-muted  { background: rgba(255,255,255,.05); border-color: rgba(255,255,255,.08); color: rgba(255,255,255,.45); }

.pa-url { word-break: break-all; }

/* API usage stats */
.pa-stat-grid { display: grid; grid-template-columns: repeat(3, minmax(0,1fr)); gap: 10px; }
.pa-stat { border: 1px solid rgba(255,255,255,.08); border-radius: 12px; background: rgba(255,255,255,.025); padding: 12px; }
.pa-stat span { display: block; color: rgba(255,255,255,.5); font-size: .72rem; font-weight: 800; text-transform: uppercase; letter-spacing: .05em; }
.pa-stat strong { display: block; margin-top: 6px; color: #fff; font-size: 1.3rem; }
.pa-stat-danger { border-color: rgba(251,113,133,.22); background: rgba(251,113,133,.05); }
.pa-stat-danger strong { color: var(--pa-red); }

/* Usage / Doc / Activity tabs (shared pill-tab look) */
.pa-tabs { display: flex; border: 1px solid rgba(255,255,255,.10); border-radius: 9px; overflow: hidden; flex-wrap: wrap; }
.pa-tabs button { height: 32px; padding: 0 14px; border: 0; background: transparent; color: rgba(255,255,255,.6); font-weight: 700; font-size: .8rem; white-space: nowrap; }
.pa-tabs button:not(:first-child) { border-left: 1px solid rgba(255,255,255,.10); }
.pa-tabs button.active { background: rgba(109,92,255,.16); color: #fff; }

/* Code blocks */
.pa-code-title { font-size: .78rem; font-weight: 800; color: #a78fff; text-transform: uppercase; letter-spacing: .08em; margin-bottom: 10px; }
.pa-code { margin: 0; background: rgba(0,0,0,.28); border: 1px solid rgba(255,255,255,.08); border-radius: 12px; padding: 16px; color: #e2d9ff; white-space: pre-wrap; overflow: auto; font-size: .85rem; }

/* Create-key modal contents */
.pa-check-row { display: flex; align-items: center; gap: 12px; background: rgba(255,255,255,.04); border-radius: 10px; padding: 14px 16px; font-weight: 700; cursor: pointer; }
.pa-chk {
  appearance: none; -webkit-appearance: none;
  width: 21px; height: 21px; border-radius: 6px; flex-shrink: 0;
  border: 1.5px solid rgba(255,255,255,.22); background: rgba(255,255,255,.06);
  cursor: pointer; position: relative; transition: background .12s, border-color .12s;
  display: inline-block; vertical-align: middle;
}
.pa-chk:hover { border-color: rgba(109,92,255,.6); background: rgba(109,92,255,.12); }
.pa-chk:checked { background: var(--pa-purple); border-color: var(--pa-purple); }
.pa-chk:checked::after {
  content: ''; position: absolute; left: 6px; top: 2px;
  width: 6px; height: 11px; border: 2px solid #fff; border-top: 0; border-left: 0;
  transform: rotate(45deg);
}
.pa-chk:focus-visible { outline: none; box-shadow: 0 0 0 3px rgba(109,92,255,.25); }
.pa-terms { max-height: 150px; overflow: auto; border: 1px solid rgba(255,255,255,.08); border-radius: 10px; padding: 12px 16px; color: rgba(255,255,255,.6); display: grid; gap: 10px; font-size: .87rem; }
.pa-terms i { color: #a78fff; margin-right: 8px; }

/* Key reveal step */
.pa-key-warning {
  display: flex; align-items: flex-start; gap: 10px;
  background: rgba(251,191,36,.10); border: 1px solid rgba(251,191,36,.28);
  border-radius: 10px; padding: 12px 14px; margin-bottom: 16px;
  color: #fde68a; font-size: .87rem; font-weight: 700; line-height: 1.4;
}
.pa-key-warning i { color: #fbbf24; margin-top: 2px; flex-shrink: 0; }
.pa-key-copy { display: flex; align-items: stretch; border: 1px solid rgba(255,255,255,.12); border-radius: 12px; overflow: hidden; background: rgba(0,0,0,.28); }
.pa-key-copy code { flex: 1; padding: 16px; word-break: break-all; color: #e2d9ff; font-size: .92rem; line-height: 1.5; }
.pa-key-copy-btn {
  border: 0; border-left: 1px solid rgba(255,255,255,.10); background: rgba(109,92,255,.16); color: #c4b5fd;
  padding: 0 22px; cursor: pointer; font-size: 1.05rem; flex-shrink: 0;
  display: flex; align-items: center; justify-content: center; transition: background .15s, color .15s;
}
.pa-key-copy-btn:hover { background: rgba(109,92,255,.30); color: #fff; }
.pa-key-copy-btn.is-copied { background: rgba(74,222,128,.22); color: #4ade80; }

/* Cancel button inside the create-key modal — default btn-outline-light reads
   too faint on this dark background, give it a visible fill + border. */
#key-modal .btn-outline-light {
  background: rgba(255,255,255,.06); border-color: rgba(255,255,255,.24); color: rgba(255,255,255,.88);
}
#key-modal .btn-outline-light:hover { background: rgba(255,255,255,.12); border-color: rgba(255,255,255,.4); color: #fff; }

@media (max-width: 768px) {
  .pa-hero-grid { display: block; }
  .pa-anti-card { margin-top: 22px; min-width: 0; }
  .pa-stat-grid { grid-template-columns: 1fr; }
}
</style>

<script>
(function () {

  // ── Shared helper for simple pill-tab groups (Documentation / Activity) ──
  function wireTabGroup(tabsSelector, paneSelector, dataKey) {
    var tabs = document.querySelectorAll(tabsSelector + ' button');
    tabs.forEach(function (btn) {
      btn.addEventListener('click', function () {
        tabs.forEach(function (b) { b.classList.remove('active'); });
        btn.classList.add('active');
        document.querySelectorAll(paneSelector).forEach(function (p) { p.classList.add('d-none'); });
        var key = btn.getAttribute('data-' + dataKey);
        var target = document.querySelector(paneSelector + '[data-' + dataKey + '-pane="' + key + '"]');
        if (target) target.classList.remove('d-none');
      });
    });
  }
  wireTabGroup('#pa-doc-tabs', '.pa-doc-pane', 'doc');
  wireTabGroup('#pa-activity-tabs', '.pa-activity-pane', 'activity');

  // ── Create API Key modal ──
  var modalEl    = document.getElementById('key-modal');
  var form       = document.getElementById('create-api-key-form');
  // NOTE: do NOT read form.action here — this form has a child field named
  // "action" (required by the backend dispatcher), and per the HTML spec a
  // form control named "action" shadows the form element's own .action
  // property. Reading form.action then returns that <input> element instead
  // of the URL string, and fetch() silently stringifies it to
  // "[object HTMLInputElement]", causing a 404. Use a separate constant instead.
  var AJAX_ENDPOINT = '<?= AJAX_URL ?>';
  var createStep = document.getElementById('key-create-step');
  var resultStep = document.getElementById('key-result-step');
  var createBtn  = document.getElementById('create-token-btn');
  var confirmBtn = document.getElementById('confirm-token-btn');
  var keyBox     = document.getElementById('new-api-key');
  var copyBtn    = document.getElementById('copy-api-key');
  var keyWasCreated = false;

  if (copyBtn) {
    copyBtn.addEventListener('click', function () {
      if (keyBox && navigator.clipboard) navigator.clipboard.writeText(keyBox.textContent || '');
      var icon = copyBtn.querySelector('i');
      copyBtn.classList.add('is-copied');
      if (icon) icon.className = 'fa-solid fa-check';
      setTimeout(function () {
        copyBtn.classList.remove('is-copied');
        if (icon) icon.className = 'fa-regular fa-copy';
      }, 1500);
    });
  }

  if (form) {
    form.addEventListener('submit', function (e) {
      e.preventDefault();
      if (createBtn.disabled) return;
      createBtn.disabled = true;

      fetch(AJAX_ENDPOINT, { method: 'POST', body: new FormData(form), credentials: 'same-origin' })
        .then(function (r) {
          if (!r.ok) throw new Error('HTTP ' + r.status);
          return r.json();
        })
        .then(function (json) {
          if (json && json.api_key) {
            keyWasCreated = true;
            if (keyBox) keyBox.textContent = json.api_key;
            if (createStep) createStep.classList.add('d-none');
            if (resultStep) resultStep.classList.remove('d-none');
            if (createBtn) createBtn.classList.add('d-none');
            if (confirmBtn) confirmBtn.classList.remove('d-none');
            if (navigator.clipboard) navigator.clipboard.writeText(json.api_key);
            if (json.sendToast && typeof create_toast === 'function') {
              create_toast(json.sendToast.type, json.sendToast.title, json.sendToast.message);
            }
            return;
          }
          if (json && json.sendToast && typeof create_toast === 'function') {
            create_toast(json.sendToast.type, json.sendToast.title, json.sendToast.message);
          }
          if (json && json.refreshPage) window.location.reload();
        })
        .catch(function (err) {
          console.error('seller_api_create_key request failed:', err);
          if (typeof create_toast === 'function') create_toast('danger', 'Error', 'Request failed. Please try again.');
        })
        .finally(function () { createBtn.disabled = false; });
    });
  }

  if (modalEl) {
    modalEl.addEventListener('hidden.bs.modal', function () {
      if (keyWasCreated) { window.location.reload(); return; }
      if (createStep) createStep.classList.remove('d-none');
      if (resultStep) resultStep.classList.add('d-none');
      if (createBtn) { createBtn.classList.remove('d-none'); createBtn.disabled = false; }
      if (confirmBtn) confirmBtn.classList.add('d-none');
      if (form) form.reset();
    });
  }
})();
</script>
