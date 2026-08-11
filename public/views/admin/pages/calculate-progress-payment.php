<?php
$cpp = $GLOBALS['cpp'] ?? [];
$game = $cpp['game'] ?? 'lol';
$form_id = (int)($cpp['form_id'] ?? 0);
$post = $cpp['post'] ?? [];
$result = $cpp['result'] ?? null;
$errors = $cpp['errors'] ?? [];

function v($arr, $k, $d=''){ return htmlspecialchars($arr[$k] ?? $d); }
function eur($cents){ return number_format(((float)$cents)/100, 2, ',', '.'); }

$rank_forms = [1,5];
?>

<?= $this->layout('admin/layouts/main', [
  'meta' => [
    'title' => 'Calculate Progress Payment - Admin Area | LoLBoost.gg',
    'h1' => 'Calculate Progress Payment',
    'description' => 'Calculate progress, remaining price and fines (with discount + solo/duo).',
  ]
]) ?>

<style>
  .cpp-grid { display:grid; grid-template-columns: 1fr 1fr; gap: 16px; }
  @media (max-width: 1200px){ .cpp-grid { grid-template-columns: 1fr; } }
  .cpp-card { background: rgba(255,255,255,.03); border: 1px solid rgba(255,255,255,.06); border-radius: 14px; }
  .cpp-card .card-body { padding: 18px; }
  .cpp-label { font-size:.78rem; letter-spacing:.08em; text-transform:uppercase; color:rgba(255,255,255,.7); font-weight:800; }
  .cpp-sticky { position: sticky; top: 88px; }
  .cpp-kv { display:flex; justify-content:space-between; gap:12px; padding:10px 0; border-bottom:1px solid rgba(255,255,255,.06); }
  .cpp-kv:last-child { border-bottom:0; }
  .cpp-big { font-weight:900; }
  .cpp-sub { color:rgba(255,255,255,.65); font-size:.9rem; }
  .cpp-pill { padding:.35rem .7rem; border-radius:999px; border:1px solid rgba(255,255,255,.10); background:rgba(255,255,255,.04); font-weight:900; font-size:.75rem; letter-spacing:.08em; text-transform:uppercase; }
  .cpp-row { display:flex; flex-wrap:wrap; gap:14px; }
  .cpp-radio { display:flex; gap:16px; align-items:center; }
  .cpp-radio label { display:flex; gap:8px; align-items:center; margin:0; }
</style>

<div class="cpp-grid">

  <!-- LEFT -->
  <div class="cpp-card">
    <div class="card-body">

      <?php if (!empty($errors)): ?>
        <div class="alert alert-danger">
          <b>Error</b>
          <ul class="mb-0">
            <?php foreach ($errors as $e): ?><li><?= htmlspecialchars($e) ?></li><?php endforeach; ?>
          </ul>
        </div>
      <?php endif; ?>

      <form method="get" action="/admin-area/calculate-progress-payment" class="row g-3">
        <div class="col-6">
          <div class="cpp-label mb-1">Game</div>
          <select name="game" class="form-select" onchange="this.form.submit()">
            <option value="lol" <?= $game==='lol'?'selected':'' ?>>League of Legends</option>
            <option value="val" <?= $game==='val'?'selected':'' ?>>Valorant</option>
          </select>
        </div>
        <div class="col-6">
          <div class="cpp-label mb-1">Boost Form</div>
          <select name="form_id" class="form-select" onchange="this.form.submit()">
            <option value="0">— auswählen —</option>
            <?= util_load_boost_forms_select($form_id, $game) ?>
          </select>
        </div>
      </form>

      <hr style="border-color:rgba(255,255,255,.08);">

      <form method="post" action="/admin-area/calculate-progress-payment" class="row g-3">
        <input type="hidden" name="game" value="<?= htmlspecialchars($game) ?>">
        <input type="hidden" name="form_id" value="<?= (int)$form_id ?>">

        <div class="col-6">
          <div class="cpp-label mb-1">Server/Region</div>
          <select name="server" class="form-select">
            <?= util_load_server_select(v($post,'server', $game==='val'?'eu':'euw'), $game) ?>
          </select>
        </div>

        <div class="col-6">
          <div class="cpp-label mb-1">Booster %</div>
          <input name="booster_percent" class="form-control" value="<?= v($post,'booster_percent','70') ?>">
        </div>

        <div class="col-12">
          <div class="cpp-label mb-2">Queue Mode</div>
          <?php $qm = $post['queue_mode'] ?? 'solo'; ?>
          <div class="cpp-radio">
            <label><input type="radio" name="queue_mode" value="solo" <?= $qm==='solo'?'checked':'' ?>> <span>Solo</span></label>
            <label><input type="radio" name="queue_mode" value="duo"  <?= $qm==='duo'?'checked':'' ?>> <span>Duo</span></label>
          </div>
        </div>

        <div class="col-12">
          <div class="cpp-label mb-2">Extra Options</div>
          <div class="cpp-row">
            <label class="form-check"><input class="form-check-input" type="checkbox" name="is_priority" value="1" <?= !empty($post['is_priority'])?'checked':'' ?>> <span class="form-check-label">Priority</span></label>
            <label class="form-check"><input class="form-check-input" type="checkbox" name="is_streaming" value="1" <?= !empty($post['is_streaming'])?'checked':'' ?>> <span class="form-check-label">Streaming</span></label>
            <label class="form-check"><input class="form-check-input" type="checkbox" name="is_bonus_win" value="1" <?= !empty($post['is_bonus_win'])?'checked':'' ?>> <span class="form-check-label">Bonus Win</span></label>

            <?php if (($qm ?? 'solo') === 'solo'): ?>
              <label class="form-check"><input class="form-check-input" type="checkbox" name="is_solo_only" value="1" <?= !empty($post['is_solo_only'])?'checked':'' ?>> <span class="form-check-label">Solo Only</span></label>
            <?php else: ?>
              <label class="form-check"><input class="form-check-input" type="checkbox" name="is_hidden_duo" value="1" <?= !empty($post['is_hidden_duo'])?'checked':'' ?>> <span class="form-check-label">Hidden Duo</span></label>
            <?php endif; ?>
          </div>
        </div>

        <div class="col-12">
          <div class="cpp-label mb-2">Discount (customer)</div>
          <div class="row g-2">
            <div class="col-6">
              <div class="input-group">
                <span class="input-group-text">%</span>
                <input class="form-control" name="discount_percent" value="<?= v($post,'discount_percent','0') ?>">
              </div>
            </div>
            <div class="col-6">
              <div class="input-group">
                <span class="input-group-text">€</span>
                <input class="form-control" name="discount_fixed" value="<?= v($post,'discount_fixed','0') ?>">
              </div>
            </div>
          </div>
        </div>

        <?php if ($form_id > 0 && in_array($form_id, $rank_forms, true)): ?>
          <div class="col-12"><hr style="border-color:rgba(255,255,255,.08);"></div>
          <div class="col-12"><div class="cpp-label mb-2">Rank Progress (Start → Current → Target)</div></div>

          <div class="col-4">
            <div class="cpp-label mb-1">Start Tier</div>
            <select name="start_tier" class="form-select">
              <?= $game==='val' ? util_load_val_tier_select(0,10,v($post,'start_tier',0)) : util_load_lol_tier_select(0,10,v($post,'start_tier',0)) ?>
            </select>
          </div>
          <div class="col-4">
            <div class="cpp-label mb-1">Start Division</div>
            <select name="start_division" class="form-select">
              <?= $game==='val' ? util_load_val_division_select(v($post,'start_division',1)) : util_load_lol_division_select(v($post,'start_division',1)) ?>
            </select>
          </div>
          <div class="col-4">
            <div class="cpp-label mb-1"><?= $game==='val'?'Start RR':'Start LP' ?> (full)</div>
            <input class="form-control" name="<?= $game==='val'?'start_rr_full':'start_lp_full' ?>" value="<?= v($post, $game==='val'?'start_rr_full':'start_lp_full', '0') ?>">
          </div>

          <div class="col-4">
            <div class="cpp-label mb-1">Target Tier</div>
            <select name="end_tier" class="form-select">
              <?= $game==='val' ? util_load_val_tier_select(0,10,v($post,'end_tier',0)) : util_load_lol_tier_select(0,10,v($post,'end_tier',0)) ?>
            </select>
          </div>
          <div class="col-4">
            <div class="cpp-label mb-1">Target Division</div>
            <select name="end_division" class="form-select">
              <?= $game==='val' ? util_load_val_division_select(v($post,'end_division',1)) : util_load_lol_division_select(v($post,'end_division',1)) ?>
            </select>
          </div>
          <div class="col-4">
            <div class="cpp-label mb-1"><?= $game==='val'?'Target RR':'Target LP' ?> (full)</div>
            <input class="form-control" name="<?= $game==='val'?'end_rr_full':'end_lp_full' ?>" value="<?= v($post, $game==='val'?'end_rr_full':'end_lp_full', '0') ?>">
          </div>

          <div class="col-4">
            <div class="cpp-label mb-1">Current Tier</div>
            <select name="current_tier" class="form-select">
              <?= $game==='val' ? util_load_val_tier_select(0,10,v($post,'current_tier',v($post,'start_tier',0))) : util_load_lol_tier_select(0,10,v($post,'current_tier',v($post,'start_tier',0))) ?>
            </select>
          </div>
          <div class="col-4">
            <div class="cpp-label mb-1">Current Division</div>
            <select name="current_division" class="form-select">
              <?= $game==='val' ? util_load_val_division_select(v($post,'current_division',v($post,'start_division',1))) : util_load_lol_division_select(v($post,'current_division',v($post,'start_division',1))) ?>
            </select>
          </div>
          <div class="col-4">
            <div class="cpp-label mb-1"><?= $game==='val'?'Current RR':'Current LP' ?> (full)</div>
            <input class="form-control" name="<?= $game==='val'?'current_rr_full':'current_lp_full' ?>" value="<?= v($post, $game==='val'?'current_rr_full':'current_lp_full', '0') ?>">
          </div>

          <?php if ($game==='lol'): ?>
            <div class="col-6">
              <div class="cpp-label mb-1">LP Gain</div>
              <select name="lp_gain" class="form-select">
                <?= util_load_lp_gain_select(v($post,'lp_gain','20-24')) ?>
              </select>
            </div>
          <?php endif; ?>
        <?php endif; ?>

        <div class="col-12 d-flex justify-content-end">
          <button class="btn btn-primary px-4" type="submit" <?= $form_id<=0?'disabled':'' ?>>Calculate</button>
        </div>
      </form>
    </div>
  </div>

  <!-- RIGHT -->
  <div class="cpp-card cpp-sticky">
    <div class="card-body">
      <div class="d-flex align-items-center justify-content-between mb-2">
        <div>
          <div class="cpp-label">Ergebnis</div>
          <div class="cpp-sub">Always visible – no scrolling needed.</div>
        </div>
        <div class="cpp-pill">
          PROGRESS <?= (is_array($result) && !empty($result['ok'])) ? htmlspecialchars($result['progress_percent']).'%' : '0%' ?>
        </div>
      </div>

      <?php if (is_array($result) && !empty($result['ok'])): ?>
        <div class="cpp-kv"><div class="cpp-sub">Total (before discount)</div><div class="cpp-big">€<?= eur($result['total_before_discount']) ?></div></div>
        <div class="cpp-kv"><div class="cpp-sub">Total (after discount)</div><div class="cpp-big">€<?= eur($result['total_price']) ?></div></div>
        <div class="cpp-kv"><div class="cpp-sub">Completed</div><div class="cpp-big">€<?= eur($result['completed_price']) ?></div></div>
        <div class="cpp-kv"><div class="cpp-sub">Remaining</div><div class="cpp-big">€<?= eur($result['remaining_price']) ?></div></div>
        <div class="cpp-kv"><div class="cpp-sub">Fine</div><div class="cpp-big">€<?= eur($result['fine_price']) ?></div></div>
        <div class="cpp-kv"><div class="cpp-sub">Queue</div><div class="cpp-big"><?= htmlspecialchars(ucfirst($result['queue_mode'] ?? 'solo')) ?></div></div>

        <hr style="border-color:rgba(255,255,255,.08);">

        <div class="row g-2">
          <div class="col-4"><div class="cpp-sub">Booster Earned</div><div class="cpp-big">€<?= eur($result['booster_earned']) ?></div></div>
          <div class="col-4"><div class="cpp-sub">Booster Fine</div><div class="cpp-big">€<?= eur($result['booster_fine']) ?></div></div>
          <div class="col-4"><div class="cpp-sub">Booster Net</div><div class="cpp-big">€<?= eur($result['booster_net']) ?></div></div>
        </div>

        <div class="cpp-sub mt-2">
          Discount applied: <?= number_format((float)($result['discount_percent'] ?? 0), 2, ',', '.') ?>% + €<?= number_format((float)($result['discount_fixed'] ?? 0), 2, ',', '.') ?>
        </div>
      <?php else: ?>
        <div class="cpp-sub">Wähle links eine Boost Form und klicke <b>Calculate</b> – das Ergebnis erscheint hier rechts.</div>
      <?php endif; ?>
    </div>
  </div>

</div>
