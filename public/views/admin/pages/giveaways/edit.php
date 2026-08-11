<?php
// Local fallbacks (do not require editing core config)
if (!defined('ADMN_URL')) {
    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    define('ADMN_URL', $scheme . '://' . ($_SERVER['HTTP_HOST'] ?? '') . '/admin-area');
}
if (!defined('AJAX_URL')) {
    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    define('AJAX_URL', $scheme . '://' . ($_SERVER['HTTP_HOST'] ?? '') . '/ajax');
}

$is_edit = !empty($giveaway) && !empty($giveaway['id']);
$gid = (int)($giveaway['id'] ?? 0);
$meta_title = $is_edit ? 'Edit Giveaway - Admin Area | LoLBoost.gg' : 'New Giveaway - Admin Area | LoLBoost.gg';
?>

<?= $this->layout('admin/layouts/main', [
    'meta' => [
        'title' => $meta_title,
        'h1' => 'Giveaway',
        'description' => 'Configure giveaway settings, prizes, and tickets.',
    ],
]) ?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h2 class="mb-0"><?= $is_edit ? 'Edit Giveaway' : 'New Giveaway' ?></h2>
            <div class="text-muted">One ticket per PAID invoice during an ACTIVE giveaway.</div>
        </div>
        <div class="d-flex gap-2">
            <a href="<?= ADMN_URL ?>/giveaways" class="btn btn-outline-secondary">Back</a>
            <?php if ($is_edit && strtoupper((string)($giveaway['status'] ?? '')) !== 'DRAWN') : ?>
                <button class="btn btn-outline-success js-draw" data-id="<?= $gid ?>">Draw Winners</button>
            <?php endif; ?>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-12 col-xl-7">
            <div class="card">
                <div class="card-body">
                    <form id="giveaway-form">
                        <input type="hidden" name="action" value="admin_giveaway_save">
                        <input type="hidden" name="id" value="<?= $gid ?>">

                        <div class="mb-3">
                            <label class="form-label">Title</label>
                            <input type="text" class="form-control" name="title" value="<?= htmlspecialchars($giveaway['title'] ?? '') ?>" placeholder="30 Day Giveaway">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <textarea class="form-control" name="description" rows="3" placeholder="Earn tickets with every purchase. More tickets = higher chance to win."><?= htmlspecialchars($giveaway['description'] ?? '') ?></textarea>
                        </div>

                        <div class="row g-2">
                            <div class="col-md-6">
                                <label class="form-label">Start (YYYY-MM-DD HH:MM:SS)</label>
                                <input type="text" class="form-control" name="starts_at" value="<?= htmlspecialchars($giveaway['starts_at'] ?? date('Y-m-d 00:00:00')) ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">End (YYYY-MM-DD HH:MM:SS)</label>
                                <input type="text" class="form-control" name="ends_at" value="<?= htmlspecialchars($giveaway['ends_at'] ?? date('Y-m-d 23:59:59', strtotime('+30 days'))) ?>">
                            </div>
                        </div>

                        <div class="row g-2 mt-1">
                            <div class="col-md-4">
                                <label class="form-label">Winners Count</label>
                                <input type="number" class="form-control" name="winners_count" min="1" value="<?= (int)($giveaway['winners_count'] ?? 3) ?>">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Status</label>
                                <select class="form-select" name="status">
                                    <?php $st = strtoupper((string)($giveaway['status'] ?? 'ACTIVE')); ?>
                                    <?php foreach (['DRAFT','ACTIVE','ENDED','DRAWN'] as $opt) : ?>
                                        <option value="<?= $opt ?>" <?= $st === $opt ? 'selected' : '' ?>><?= $opt ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <hr class="my-4">

                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">Prizes</h5>
                            <button type="button" class="btn btn-sm btn-outline-primary" id="add-prize">
                                <i class="fa-solid fa-plus me-1"></i>Add prize
                            </button>
                        </div>

                        <div id="prizes" class="mt-3">
                            <?php
                            $prizes = $prizes ?? [];
                            if (empty($prizes)) {
                                $prizes = [
                                    ['name' => '1st Place', 'description' => ''],
                                    ['name' => '2nd Place', 'description' => ''],
                                    ['name' => '3rd Place', 'description' => ''],
                                ];
                            }
                            ?>
                            <?php foreach ($prizes as $i => $p) : ?>
                                <div class="border rounded p-3 mb-2 prize-row">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <div class="fw-semibold">Prize #<?= (int)($i + 1) ?></div>
                                        <button type="button" class="btn btn-sm btn-outline-danger remove-prize">Remove</button>
                                    </div>
                                    <div class="mb-2">
                                        <label class="form-label">Name</label>
                                        <input type="text" class="form-control" name="prizes[<?= (int)$i ?>][name]" value="<?= htmlspecialchars($p['name'] ?? '') ?>" placeholder="1st Place - €50 gift card">
                                    </div>
                                    <div>
                                        <label class="form-label">Description</label>
                                        <input type="text" class="form-control" name="prizes[<?= (int)$i ?>][description]" value="<?= htmlspecialchars($p['description'] ?? '') ?>" placeholder="Optional">
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>

                        <div class="mt-3 d-flex justify-content-end">
                            <button type="submit" class="btn btn-primary">
                                <span class="spinner-border spinner-border-sm me-2 d-none" role="status"></span>
                                Save
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-12 col-xl-5">
            <?php if ($is_edit) : ?>
                <div class="card mb-3">
                    <div class="card-body">
                        <h5 class="mb-3">Manual Tickets</h5>
                        <form id="ticket-form" class="row g-2 align-items-end">
                            <input type="hidden" name="action" value="admin_giveaway_adjust_tickets">
                            <input type="hidden" name="giveaway_id" value="<?= $gid ?>">
                            <div class="col-md-5">
                                <label class="form-label">Client ID</label>
                                <input type="number" class="form-control" name="client_id" min="1" placeholder="123">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Delta</label>
                                <input type="number" class="form-control" name="delta" placeholder="+5 or -5">
                            </div>
                            <div class="col-12">
                                <label class="form-label">Note (optional)</label>
                                <input type="text" class="form-control" name="note" placeholder="Manual bonus">
                            </div>
                            <div class="col-12">
                                <button class="btn btn-outline-primary w-100" type="submit">Apply</button>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="card mb-3">
                    <div class="card-body">
                        <h5 class="mb-3">Top 20 Leaderboard</h5>
                        <div class="table-responsive">
                            <table class="table table-sm align-middle">
                                <thead>
                                    <tr>
                                        <th style="width:50px;">#</th>
                                        <th>Client</th>
                                        <th class="text-end" style="width:110px;">Tickets</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($leaderboard)) : ?>
                                        <?php foreach (array_slice($leaderboard, 0, 20) as $i => $r) : ?>
                                            <tr>
                                                <td><?= $i + 1 ?></td>
                                                <td>
                                                    <div class="d-flex align-items-center gap-2">
                                                        <img src="<?= htmlspecialchars($r['icon'] ?? '') ?>" style="width:28px;height:28px;border-radius:50%;object-fit:cover;" alt="">
                                                        <div>
                                                            <div class="fw-semibold"><?= htmlspecialchars($r['username'] ?? '') ?></div>
                                                            <div class="text-muted small">#<?= (int)($r['client_id'] ?? 0) ?></div>
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
                            <div class="text-muted small">Showing first 20 of <?= (int)count($leaderboard) ?> participants.</div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="card">
                    <div class="card-body">
                        <h5 class="mb-3">Winners</h5>
                        <?php if (!empty($winners)) : ?>
                            <ul class="list-group">
                                <?php foreach ($winners as $w) : ?>
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        <div class="d-flex align-items-center gap-2">
                                            <span class="badge bg-primary">#<?= (int)($w['rank'] ?? 0) ?></span>
                                            <img src="<?= htmlspecialchars($w['icon'] ?? '') ?>" style="width:28px;height:28px;border-radius:50%;object-fit:cover;" alt="">
                                            <span class="fw-semibold"><?= htmlspecialchars($w['username'] ?? '') ?></span>
                                            <span class="text-muted">(#<?= (int)($w['client_id'] ?? 0) ?>)</span>
                                        </div>
                                        <span class="fw-semibold"><?= (int)($w['tickets_at_draw'] ?? 0) ?> tickets</span>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        <?php else : ?>
                            <div class="text-muted">No winners yet.</div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php else : ?>
                <div class="alert alert-info">
                    Save the giveaway first to manage tickets and view the leaderboard.
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?= $this->start('scripts') ?>
<script>
  function ajax_toast_response(res) {
    try {
      const response = JSON.parse(res);

      if (response.sendToast) {
        create_toast(response.sendToast.type, response.sendToast.title, response.sendToast.message);
      }

      if (response.playSound) {
        var audio = new Audio(asset_url + '/core/dash/audio/' + response.playSound + '.mp3');
        audio.play();
      }

      if (response.redirect) {
        setTimeout(function(){ window.location.href = response.redirect; }, 800);
      }

      if (response.refreshPage) {
        setTimeout(function(){ location.reload(); }, 800);
      }
    } catch (e) {
      console.error(res);
    }
  }

  function renumber_prizes() {
    $('#prizes .prize-row').each(function(index) {
      $(this).find('.fw-semibold').first().text('Prize #' + (index + 1));
      $(this).find('input[name^="prizes"]').each(function () {
        const name = $(this).attr('name');
        const newName = name.replace(/prizes\[\d+\]/, 'prizes[' + index + ']');
        $(this).attr('name', newName);
      });
    });
  }

  $('#add-prize').on('click', function () {
    const idx = $('#prizes .prize-row').length;
    $('#prizes').append(
      `<div class="border rounded p-3 mb-2 prize-row">
        <div class="d-flex justify-content-between align-items-center mb-2">
          <div class="fw-semibold">Prize #${idx+1}</div>
          <button type="button" class="btn btn-sm btn-outline-danger remove-prize">Remove</button>
        </div>
        <div class="mb-2">
          <label class="form-label">Name</label>
          <input type="text" class="form-control" name="prizes[${idx}][name]" placeholder="1st Place - €50 gift card">
        </div>
        <div>
          <label class="form-label">Description</label>
          <input type="text" class="form-control" name="prizes[${idx}][description]" placeholder="Optional">
        </div>
      </div>`
    );
  });

  $(document).on('click', '.remove-prize', function () {
    $(this).closest('.prize-row').remove();
    renumber_prizes();
  });

  $('#giveaway-form').on('submit', function (e) {
    e.preventDefault();
    const $btn = $('#giveaway-form button[type="submit"]');
    $btn.prop('disabled', true);
    $btn.find('.spinner-border').removeClass('d-none');

    $.ajax({
      url: '<?= AJAX_URL ?>',
      type: 'POST',
      data: $(this).serialize(),
      success: function (res) {
        ajax_toast_response(res);
      },
      complete: function () {
        $btn.prop('disabled', false);
        $btn.find('.spinner-border').addClass('d-none');
      }
    });
  });

  $('#ticket-form').on('submit', function (e) {
    e.preventDefault();
    $.ajax({
      url: '<?= AJAX_URL ?>',
      type: 'POST',
      data: $(this).serialize(),
      success: function (res) { ajax_toast_response(res); }
    });
  });

  $(document).on('click', '.js-draw', function () {
    const id = $(this).data('id');
    if (!id) return;
    if (!confirm('Draw winners now? This will mark the giveaway as DRAWN.')) return;
    $.ajax({
      url: '<?= AJAX_URL ?>',
      type: 'POST',
      data: { action: 'admin_giveaway_draw', id: id },
      success: function (res) { ajax_toast_response(res); }
    });
  });
</script>
<?= $this->stop() ?>