<?php
$st = $data ?? [];
$percent = (int)($st['percent'] ?? 0);
$steps = $st['steps'] ?? [];
$missing_keys = $st['missing'] ?? [];
$booster = $st['booster'] ?? [];
$profile = $st['profile'] ?? [];
$ack = (int)($st['setup_settings_ack'] ?? 0);
$games = $st['games'] ?? array_values(array_filter(explode('|', (string)($booster['games'] ?? ''))));
if (!is_array($games) || empty($games)) $games = ['lol'];
$games = array_values(array_unique(array_filter(array_map('strval', $games))));
$has_lol = in_array('lol', $games, true);
$has_val = in_array('val', $games, true);
$has_tft = in_array('tft', $games, true);
$dynamic_games = array_values(array_diff($games, ['lol', 'val', 'tft']));
$dynamic_game_profiles = lb_booster_game_profiles((int)($booster['id'] ?? (defined('BOOSTER_ID') ? BOOSTER_ID : 0)));

$map = [
  'discord'          => 1,
  'profile_picture'  => 2,
  'banner'           => 2,
  'languages'        => 3,
  'settings_ack'     => 3,
  'servers'          => 4,
  'timezone'         => 4,
  'description'      => 4,
  'payout'           => 6,
];
if ($has_lol) {
  $map['lol_rank'] = 5;
  $map['champions'] = 5;
  $map['roles'] = 5;
}
if ($has_val) {
  $map['val_rank'] = 5;
  $map['agents'] = 5;
}
if ($has_tft) {
  $map['tft_rank'] = 5;
}

$missing_steps = [];
foreach ($missing_keys as $k) if (isset($map[$k])) $missing_steps[] = $map[$k];
$missing_steps = array_values(array_unique($missing_steps));
sort($missing_steps);

$step = isset($_GET['step']) ? max(1, intval($_GET['step'])) : (count($missing_steps) ? $missing_steps[0] : 1);

// Skip languages step only when ack=1 and languages are already done
$languages_done = !empty($steps['languages']['done']);
if ($step === 3 && $ack === 1 && $languages_done) $step = (count($missing_steps) ? $missing_steps[0] : 4);

// Completed boosters may reopen a specific setup step to maintain their game profiles.
if (!empty($st['complete']) && !isset($_GET['step'])) { redirect_url('booster-area/dashboard'); exit; }

$langs_selected = is_string($booster['languages'] ?? null) ? array_values(array_filter(explode('|', $booster['languages']))) : [];
$servers_selected = is_string($profile['servers'] ?? null) ? array_values(array_filter(explode('|', $profile['servers']))) : [];
$champs_selected = is_string($profile['champions'] ?? null) ? array_values(array_filter(explode('|', $profile['champions']))) : [];
$roles_selected  = is_string($profile['roles'] ?? null) ? array_values(array_filter(explode('|', $profile['roles']))) : [];
$agents_selected = is_string($profile['agents'] ?? null) ? array_values(array_filter(explode('|', $profile['agents']))) : [];

$lol_rank = explode('|', (string)($profile['lol_rank'] ?? '7|4'));
$curTier = intval($lol_rank[0] ?? 7);
$curDiv  = intval($lol_rank[1] ?? 4);
$allowedTiers = [7,8,9,10];
if (!in_array($curTier, $allowedTiers, true)) $curTier = 7;
if ($curDiv < 1 || $curDiv > 4) $curDiv = 4;

$val_rank = explode('|', (string)($profile['val_rank'] ?? '6|3'));
$valTier = intval($val_rank[0] ?? 6);
$valDiv  = intval($val_rank[1] ?? 3);
$allowedValTiers = [6,7,8,9];
if (!in_array($valTier, $allowedValTiers, true)) $valTier = 6;
if ($valDiv < 1 || $valDiv > 3) $valDiv = 3;

$tft_rank = explode('|', (string)($profile['tft_rank'] ?? '7|4'));
$tftTier = intval($tft_rank[0] ?? 7);
$tftDiv  = intval($tft_rank[1] ?? 4);
$allowedTftTiers = [7,8,9,10];
if (!in_array($tftTier, $allowedTftTiers, true)) $tftTier = 7;
if ($tftDiv < 1 || $tftDiv > 4) $tftDiv = 4;

$game_labels = ['lol' => 'League of Legends', 'val' => 'Valorant', 'tft' => 'Teamfight Tactics'];
$game_names = array_values(array_map(function ($g) use ($game_labels) {
  return $game_labels[$g] ?? (function_exists('util_game_display_name') ? util_game_display_name($g) : strtoupper($g));
}, $games));
$step5_title = count($game_names) === 1
  ? ('Step 5: ' . $game_names[0])
  : 'Step 5: Game Setup';
$show_game_section_titles = count($game_names) > 1;

// media status
$default_icon = 'https://lolboost.gg/public/uploads/icons/default.png';
$icon_url = trim((string)($booster['icon'] ?? $default_icon));
$icon_done = ($icon_url !== '' && $icon_url !== $default_icon && strpos($icon_url, '/uploads/icons/default.png') === false);

$cover_raw = $booster['cover'] ?? null;
$cover_done = !(is_null($cover_raw) || (is_string($cover_raw) && trim($cover_raw) === ''));
$cover_preview = $cover_done ? (string)$cover_raw : (ASSET_URL . '/core/main/img/banners/leona.jpeg');
?>
<?= $this->layout('booster/layouts/main', ['meta' => ['title' => 'Profile Setup - Booster Area | LoLBoost.gg'], 'contain' => true]) ?>

<?= $this->start('styles') ?>
<link rel="stylesheet" href="<?= ASSET_URL ?>/origin/dash/vendor/tom-select/dist/css/tom-select.bootstrap5.css">
<style>
  .setup-wrap { max-width: 1100px; margin: 0 auto; }
  .setup-progress {
    width: 54px; height: 54px; border-radius: 999px;
    display:flex; align-items:center; justify-content:center;
    border: 2px solid rgba(120,90,255,.35);
    background: rgba(120,90,255,.08);
    font-weight: 700;
  }
  .setup-step { padding: 12px 14px; border-radius: 12px; background: rgba(255,255,255,.03); border: 1px solid rgba(255,255,255,.06); }
  .setup-step + .setup-step { margin-top: 10px; }
  .setup-step .dot { width: 10px; height: 10px; border-radius: 99px; display:inline-block; margin-right: 10px; }
  .dot.no { background: #e74c3c; }
  .setup-card { border-radius: 16px; overflow: visible; }
  .setup-card .card-body { overflow: visible; }

  .media-preview{
    width: 100%; height: 160px; border-radius: 14px;
    background: rgba(0,0,0,.2);
    border: 1px solid rgba(255,255,255,.08);
    overflow: hidden;
  }
  .media-preview img{ width:100%; height:100%; object-fit: cover; display:block; }

  /* TomSelect dark + overlay fix */
  .ts-control, .ts-wrapper.single .ts-control, .ts-wrapper.multi .ts-control{
    background: rgba(0,0,0,.25) !important;
    border-color: rgba(255,255,255,.10) !important;
    color: #e9e9ef !important;
    min-height: 44px;
  }
  .ts-control .item{ background: rgba(120,90,255,.18) !important; border: 1px solid rgba(120,90,255,.35) !important; color:#fff !important; }
  .ts-control input{ color:#e9e9ef !important; }
  .ts-dropdown{
    background: rgba(18,18,22,.98) !important;
    border-color: rgba(255,255,255,.10) !important;
    color:#e9e9ef !important;
    z-index: 9999 !important;
  }
  .ts-dropdown .option{ color:#e9e9ef !important; }
  .ts-dropdown .active{ background: rgba(120,90,255,.25) !important; }
  /* Native <select> (timezone, ranks, payout method): dark control + dark option list */
  .setup-wrap select.form-select{
    background-color: rgba(0,0,0,.25) !important;
    color: #e9e9ef !important;
    border-color: rgba(255,255,255,.10) !important;
  }
  .setup-wrap select.form-select:focus{
    border-color: rgba(120,90,255,.55) !important;
    box-shadow: 0 0 0 .2rem rgba(120,90,255,.20) !important;
  }
  /* The option popup itself — most browsers honor these on <option>/<optgroup> */
  .setup-wrap select.form-select option,
  .setup-wrap select.form-select optgroup{
    background-color: #17171c !important;
    color: #e9e9ef !important;
  }
  .setup-wrap select.form-select option:checked,
  .setup-wrap select.form-select option:hover{
    background-color: rgba(120,90,255,.30) !important;
    color: #fff !important;
  }
</style>
<?= $this->end() ?>

<div class="setup-wrap">
  <div class="d-flex align-items-center justify-content-between mb-4">
    <div>
      <h1 class="page-header-title mb-1">Profile Setup</h1>
      <div class="text-muted">Complete all required steps to unlock your Booster Dashboard.</div>
    </div>
    <div class="setup-progress"><?= $percent ?>%</div>
  </div>

  <div class="row g-4">
    <div class="col-lg-4">
      <div class="card setup-card">
        <div class="card-header">
          <div class="d-flex align-items-center justify-content-between">
            <h4 class="card-header-title mb-0">Missing</h4>
            <span class="badge bg-primary"><?= count($missing_keys) ?>/<?= count($steps) ?></span>
          </div>
        </div>
        <div class="card-body">
          <?php if (count($missing_keys) === 0): ?>
            <div class="text-muted">All steps completed.</div>
          <?php else: ?>
            <?php foreach ($missing_keys as $k): ?>
              <?php if (!isset($steps[$k])) continue; ?>
              <div class="setup-step">
                <div class="d-flex align-items-center justify-content-between">
                  <div class="d-flex align-items-center">
                    <span class="dot no"></span>
                    <span class="fw-semibold"><?= esc($steps[$k]['label'] ?? $k) ?></span>
                  </div>
                  <span class="text-danger fw-semibold">Missing</span>
                </div>
              </div>
            <?php endforeach; ?>
          <?php endif; ?>
        </div>
      </div>

      <div class="alert alert-soft-primary mt-3 mb-0">
        You can’t access other pages until everything is completed. Only missing fields are shown.
      </div>
    </div>

    <div class="col-lg-8">
      <div class="card setup-card">
        <div class="card-header d-flex align-items-center justify-content-between">
          <h4 class="card-header-title mb-0">
            <?php
              $titles = [
                1 => 'Step 1: Connect Discord',
                2 => 'Step 2: Profile Picture & Banner',
                3 => 'Step 3: Languages & Preferences',
                4 => 'Step 4: Servers, Timezone & Description',
                5 => $step5_title,
                6 => 'Step 6: Payout Settings',
              ];
              echo esc($titles[$step] ?? 'Profile Setup');
            ?>
          </h4>
          <span class="badge bg-primary"><?= (int)$step ?>/6</span>
        </div>

        <div class="card-body">

          <?php if ($step === 1): ?>
            <p class="text-muted mb-4">Connect your Discord account to access orders and communicate with the team.</p>
            <?php if (!empty($booster['discord_id'])): ?>
              <div class="alert alert-soft-success mb-3">Discord is connected.</div>
              <div class="d-flex justify-content-end">
                <a class="btn btn-primary" href="?step=<?= (count($missing_steps)?$missing_steps[0]:2) ?>">Next</a>
              </div>
            <?php else: ?>
              <a class="btn btn-primary" href="/auth/discord/connect?booster_id=<?= (int)BOOSTER_ID ?>">
                <i class="fa-brands fa-discord me-2"></i>Connect Discord
              </a>
            <?php endif; ?>

          <?php elseif ($step === 2): ?>
            <p class="text-muted mb-4">Upload a profile picture and banner. These are required to be publicly visible.</p>

            <div class="row g-4">
              <div class="col-md-6">
                <div class="fw-semibold mb-2 d-flex align-items-center justify-content-between">
                  <span>Profile picture (required)</span>
                  <?php if ($icon_done): ?><span class="badge bg-success">Done</span><?php else: ?><span class="badge bg-danger">Missing</span><?php endif; ?>
                </div>
                <div class="media-preview mb-3" style="height:140px;">
                  <img src="<?= esc($icon_url ?: $default_icon) ?>" alt="Icon" style="object-fit:contain;background:rgba(0,0,0,.2);">
                </div>

                <input id="iconFile" class="form-control" accept="image/*" type="file">
                <button id="uploadIconBtn" type="button" class="btn btn-primary mt-3 w-100">Upload profile picture</button>
                <div class="text-muted small mt-2">PNG/JPG recommended. Avoid blurry images.</div>
              </div>

              <div class="col-md-6">
                <div class="fw-semibold mb-2 d-flex align-items-center justify-content-between">
                  <span>Banner (required)</span>
                  <?php if ($cover_done): ?><span class="badge bg-success">Done</span><?php else: ?><span class="badge bg-danger">Missing</span><?php endif; ?>
                </div>
                <div class="media-preview mb-3">
                  <img src="<?= esc($cover_preview) ?>" alt="Banner">
                </div>

                <input id="coverFile" class="form-control" accept="image/*" type="file">
                <button id="uploadCoverBtn" type="button" class="btn btn-primary mt-3 w-100">Upload banner</button>
                <div class="text-muted small mt-2">Use a wide image (e.g. 1920×480).</div>
              </div>
            </div>

            <div class="d-flex justify-content-end gap-2 mt-4">
              <a class="btn btn-outline-secondary" href="?step=1">Back</a>
              <button id="step2MediaNext" type="button" class="btn btn-primary" <?= ($icon_done && $cover_done) ? '' : 'disabled' ?>>Next</button>
            </div>

          <?php elseif ($step === 3): ?>
            <p class="text-muted">Select your languages. Preferences below are optional.</p>

            <label class="form-label fw-semibold mt-3">Languages (required)</label>
            <select id="setupLanguages" name="languages[]" multiple class="form-select">
              <?= util_load_languages_select($langs_selected) ?>
            </select>

            <div class="mt-4 d-flex flex-column gap-3">
              <div class="d-flex align-items-center justify-content-between">
                <div>
                  <div class="fw-semibold">Show profile on Boosters page</div>
                  <div class="text-muted small">Controls public visibility.</div>
                </div>
                <div class="form-check form-switch m-0">
                  <input class="form-check-input js-toggle" type="checkbox" data-field="show_profile" <?= ((int)($booster['show_profile'] ?? 0) === 1) ? 'checked' : '' ?>>
                </div>
              </div>

              <div class="d-flex align-items-center justify-content-between">
                <div>
                  <div class="fw-semibold">Receive boosting/coaching requests</div>
                  <div class="text-muted small">Allow clients to send requests.</div>
                </div>
                <div class="form-check form-switch m-0">
                  <input class="form-check-input js-toggle" type="checkbox" data-field="boost_requests" <?= ((int)($booster['boost_requests'] ?? 0) === 1) ? 'checked' : '' ?>>
                </div>
              </div>
            </div>

            <div class="d-flex justify-content-end gap-2 mt-4">
              <a class="btn btn-outline-secondary" href="?step=2">Back</a>
              <button id="step3Next" type="button" class="btn btn-primary">Next</button>
            </div>

          <?php elseif ($step === 4): ?>
            <label class="form-label fw-semibold">Servers (required)</label>
            <select id="setupServers" name="servers[]" multiple class="form-select mb-4">
              <?= util_load_servers_select($servers_selected) ?>
            </select>

            <div class="row g-3">
              <div class="col-md-6">
                <label class="form-label fw-semibold">Timezone (required)</label>
                <select id="setupTimezone" class="form-select">
                  <?= util_load_timezones_select($profile['timezone'] ?? '') ?>
                </select>
              </div>
            </div>

            <div class="mt-3">
              <label class="form-label fw-semibold">Description (required)</label>
              <textarea id="setupDescription" class="form-control" rows="4" placeholder="Write a short introduction about your experience..."><?= esc($profile['description'] ?? '') ?></textarea>
            </div>

            <div class="d-flex justify-content-end gap-2 mt-4">
              <a class="btn btn-outline-secondary" href="?step=3">Back</a>
              <button id="step4Next" type="button" class="btn btn-primary">Next</button>
            </div>

          <?php elseif ($step === 5): ?>
            <?php if ($show_game_section_titles): ?>
              <p class="text-muted mb-4">Complete the setup fields for each game you boost.</p>
            <?php endif; ?>

            <?php if ($has_lol): ?>
              <?php if ($show_game_section_titles): ?><h5 class="mb-3">League of Legends</h5><?php endif; ?>
              <div class="row g-3">
                <div class="col-md-8">
                  <label class="form-label fw-semibold">Rank (required)</label>
                  <select id="rankTier" class="form-select">
                    <?php
                      $tiers = [7=>'Diamond',8=>'Master',9=>'Grandmaster',10=>'Challenger'];
                      foreach ($tiers as $k=>$v){
                        $sel = ($k===$curTier) ? 'selected' : '';
                        echo "<option value=\"{$k}\" {$sel}>" . esc($v) . "</option>";
                      }
                    ?>
                  </select>
                </div>
                <div class="col-md-4" id="divWrap">
                  <label class="form-label fw-semibold">Division</label>
                  <select id="rankDiv" class="form-select">
                    <?php
                      $divs = [4=>'IV',3=>'III',2=>'II',1=>'I'];
                      foreach ($divs as $k=>$v){
                        $sel = ($k===$curDiv) ? 'selected' : '';
                        echo "<option value=\"{$k}\" {$sel}>" . esc($v) . "</option>";
                      }
                    ?>
                  </select>
                </div>
              </div>

              <div class="mt-4">
                <label class="form-label fw-semibold">Champions (required)</label>
                <select id="setupChampions" name="champions[]" multiple class="form-select">
                  <?= util_load_champions_select($champs_selected) ?>
                </select>
              </div>

              <div class="mt-4 <?= ($has_val || $has_tft) ? 'mb-5' : '' ?>">
                <label class="form-label fw-semibold">Roles (required)</label>
                <select id="setupRoles" name="roles[]" multiple class="form-select">
                  <?php
                    $roleMap = ['TopLane'=>'Top','Jungle'=>'Jungle','MidLane'=>'Mid','AdCarry'=>'ADC','Support'=>'Support'];
                    foreach ($roleMap as $k=>$v){
                      $sel = in_array($k, $roles_selected, true) ? 'selected' : '';
                      echo "<option value=\"" . esc($k) . "\" {$sel}>" . esc($v) . "</option>";
                    }
                  ?>
                </select>
              </div>
            <?php endif; ?>

            <?php if ($has_val): ?>
              <?php if ($show_game_section_titles): ?><h5 class="mb-3">Valorant</h5><?php endif; ?>
              <div class="row g-3">
                <div class="col-md-8">
                  <label class="form-label fw-semibold">Rank (required)</label>
                  <select id="valRankTier" class="form-select">
                    <?php
                      $valTiers = [6=>'Diamond',7=>'Ascendant',8=>'Immortal',9=>'Radiant'];
                      foreach ($valTiers as $k=>$v){
                        $sel = ($k===$valTier) ? 'selected' : '';
                        echo "<option value=\"{$k}\" {$sel}>" . esc($v) . "</option>";
                      }
                    ?>
                  </select>
                </div>
                <div class="col-md-4" id="valDivWrap">
                  <label class="form-label fw-semibold">Division</label>
                  <select id="valRankDiv" class="form-select">
                    <?php
                      $valDivs = [3=>'III',2=>'II',1=>'I'];
                      foreach ($valDivs as $k=>$v){
                        $sel = ($k===$valDiv) ? 'selected' : '';
                        echo "<option value=\"{$k}\" {$sel}>" . esc($v) . "</option>";
                      }
                    ?>
                  </select>
                </div>
              </div>

              <div class="mt-4 <?= $has_tft ? 'mb-5' : '' ?>">
                <label class="form-label fw-semibold">Agents (required)</label>
                <select id="setupAgents" name="agents[]" multiple class="form-select">
                  <?= util_load_agents_select($agents_selected) ?>
                </select>
              </div>
            <?php endif; ?>

            <?php if ($has_tft): ?>
              <?php if ($show_game_section_titles): ?><h5 class="mb-3">Teamfight Tactics</h5><?php endif; ?>
              <div class="row g-3">
                <div class="col-md-8">
                  <label class="form-label fw-semibold">Rank (required)</label>
                  <select id="tftRankTier" class="form-select">
                    <?php
                      $tftTiers = [7=>'Diamond',8=>'Master',9=>'Grandmaster',10=>'Challenger'];
                      foreach ($tftTiers as $k=>$v){
                        $sel = ($k===$tftTier) ? 'selected' : '';
                        echo "<option value=\"{$k}\" {$sel}>" . esc($v) . "</option>";
                      }
                    ?>
                  </select>
                </div>
                <div class="col-md-4" id="tftDivWrap">
                  <label class="form-label fw-semibold">Division</label>
                  <select id="tftRankDiv" class="form-select">
                    <?php
                      $tftDivs = [4=>'IV',3=>'III',2=>'II',1=>'I'];
                      foreach ($tftDivs as $k=>$v){
                        $sel = ($k===$tftDiv) ? 'selected' : '';
                        echo "<option value=\"{$k}\" {$sel}>" . esc($v) . "</option>";
                      }
                    ?>
                  </select>
                </div>
              </div>
            <?php endif; ?>

            <?php foreach ($dynamic_games as $dynamic_game):
              $dynamic_label = util_game_display_name($dynamic_game);
              $dynamic_icon = util_game_icon_url($dynamic_game);
              $dynamic_config = lb_generic_game_rank_config($dynamic_game) ?? [];
              $dynamic_ranks = (array)($dynamic_config['ranks'] ?? []);
              $dynamic_saved = (array)($dynamic_game_profiles[$dynamic_game] ?? []);
              $dynamic_specialties = lb_booster_game_specialty_options($dynamic_game);
              $dynamic_specialty_label = (string)($dynamic_specialties[0]['label'] ?? 'Specialties');
            ?>
              <div class="mt-4 pt-4 border-top dynamic-game-profile" data-game="<?= esc($dynamic_game) ?>">
                <h5 class="mb-3 d-flex align-items-center gap-2">
                  <?php if ($dynamic_icon): ?><img src="<?= esc($dynamic_icon) ?>" alt="" style="width:28px;height:28px;object-fit:contain"><?php endif; ?>
                  <?= esc($dynamic_label) ?>
                </h5>
                <div class="row g-3">
                  <div class="col-md-6">
                    <label class="form-label fw-semibold">Rank</label>
                    <select class="form-select js-dynamic-rank">
                      <option value="0">Unranked</option>
                      <?php foreach ($dynamic_ranks as $tier => $rank_name): ?>
                        <option value="<?= (int)$tier ?>" <?= (int)($dynamic_saved['rank_tier'] ?? 0) === (int)$tier ? 'selected' : '' ?>><?= esc($rank_name) ?></option>
                      <?php endforeach; ?>
                    </select>
                  </div>
                  <div class="col-md-6">
                    <label class="form-label fw-semibold">Division</label>
                    <select class="form-select js-dynamic-division">
                      <option value="0">None</option>
                      <?php foreach ([1=>'I',2=>'II',3=>'III',4=>'IV',5=>'V'] as $division => $division_name): ?>
                        <option value="<?= $division ?>" <?= (int)($dynamic_saved['rank_division'] ?? 0) === $division ? 'selected' : '' ?>><?= $division_name ?></option>
                      <?php endforeach; ?>
                    </select>
                  </div>
                </div>
                <?php if ($dynamic_specialties): ?>
                  <div class="mt-3">
                    <label class="form-label fw-semibold"><?= esc($dynamic_specialty_label) ?></label>
                    <select multiple class="form-select js-dynamic-specialties">
                      <?php foreach ($dynamic_specialties as $specialty): ?>
                        <option value="<?= esc($specialty['key']) ?>" <?= in_array($specialty['key'], (array)($dynamic_saved['specialties'] ?? []), true) ? 'selected' : '' ?>><?= esc($specialty['name']) ?></option>
                      <?php endforeach; ?>
                    </select>
                  </div>
                <?php endif; ?>
              </div>
            <?php endforeach; ?>

            <div class="d-flex justify-content-end gap-2 mt-4">
              <a class="btn btn-outline-secondary" href="?step=4">Back</a>
              <button id="step5Next" type="button" class="btn btn-primary">Next</button>
            </div>

          <?php else: ?>
            <p class="text-muted">Add at least one payout method and make sure one is set as default. You can do it right here.</p>

            <div class="row g-3">
              <div class="col-md-6">
                <label class="form-label fw-semibold">Method</label>
                <select id="payoutMethod" class="form-select">
                  <option value="crypto">Crypto (USDC on Solana)</option>
                  <option value="bank_transfer">Bank transfer</option>
                </select>
              </div>
              <div class="col-md-6 d-flex align-items-end">
                <div class="form-check form-switch m-0">
                  <input id="payoutDefault" class="form-check-input" type="checkbox" checked>
                  <label class="form-check-label ms-2" for="payoutDefault">Set as default</label>
                </div>
              </div>
            </div>

            <div id="payoutFields" class="mt-3">
              <div class="text-muted small">Loading payout fields...</div>
            </div>

            <div id="payoutFeedback" class="alert mt-3 d-none" role="alert"></div>

            <div class="d-flex justify-content-end gap-2 mt-4">
              <a class="btn btn-outline-secondary" href="?step=5">Back</a>
              <button id="savePayoutBtn" type="button" class="btn btn-primary">Save payout method</button>
            </div>

            <div class="mt-3">
              <a class="btn btn-link p-0" href="/booster-area/payout">Open payout settings (optional)</a>
            </div>

          <?php endif; ?>

        </div>
      </div>
    </div>
  </div>
</div>

<?= $this->start('scripts') ?>
<script src="<?= ASSET_URL ?>/origin/dash/vendor/tom-select/dist/js/tom-select.complete.min.js"></script>
<script>
  const initTS = (id) => {
    const el = document.getElementById(id);
    if (!el) return null;
    return new TomSelect(el, { plugins: ['remove_button'], persist: false, create: false });
  };

  const tsLanguages = initTS('setupLanguages');
  const tsServers   = initTS('setupServers');
  const tsChamps    = initTS('setupChampions');
  const tsRoles     = initTS('setupRoles');
  const tsAgents    = initTS('setupAgents');
  document.querySelectorAll('.js-dynamic-specialties').forEach(function(el){
    new TomSelect(el, { plugins: ['remove_button'], persist: false, create: false });
  });

  async function postToAjax(fd){
    const res = await fetch('<?= AJAX_URL ?>', { method: 'POST', body: fd });
    const txt = await res.text();
    try { return JSON.parse(txt); } catch(e) { throw new Error(txt); }
  }
  function fdBase(field){
    const fd = new FormData();
    fd.append('action','booster_profile_quick_update');
    fd.append('field', field);
    return fd;
  }

  // avoid dropdown overlay weirdness on scroll
  document.addEventListener('scroll', () => {
    try { tsServers && tsServers.close(); } catch(e){}
    try { tsLanguages && tsLanguages.close(); } catch(e){}
    try { tsChamps && tsChamps.close(); } catch(e){}
    try { tsRoles && tsRoles.close(); } catch(e){}
    try { tsAgents && tsAgents.close(); } catch(e){}
  }, true);

  // step 2 uploads (use existing ajax actions)
  async function uploadFile(action, file){
    if (!file) { alert('Please select a file first.'); return false; }
    const fd = new FormData();
    fd.append('action', action);
    fd.append('image_url', file);
    const res = await fetch('<?= AJAX_URL ?>', { method: 'POST', body: fd });
    const txt = await res.text();
    try { JSON.parse(txt); } catch(e) {}
    return true;
  }

  document.getElementById('uploadIconBtn')?.addEventListener('click', async () => {
    const f = document.getElementById('iconFile')?.files?.[0];
    try {
      await uploadFile('booster_upload_profile_picture', f);
      window.location.href='?step=2';
    } catch(e){ console.error(e); window.location.reload(); }
  });

  document.getElementById('uploadCoverBtn')?.addEventListener('click', async () => {
    const f = document.getElementById('coverFile')?.files?.[0];
    try {
      await uploadFile('booster_upload_cover', f);
      window.location.href='?step=2';
    } catch(e){ console.error(e); window.location.reload(); }
  });

  document.getElementById('step2MediaNext')?.addEventListener('click', () => {
    // button is disabled if not done, but keep safety
    window.location.href='?step=3';
  });

  // toggles save instantly
  document.querySelectorAll('.js-toggle').forEach(el => {
    el.addEventListener('change', async () => {
      const fd = fdBase(el.dataset.field);
      fd.append('value', el.checked ? 1 : 0);
      try { await postToAjax(fd); } catch(e) {}
    });
  });

  // Step 3 (Languages) => save + ack
  document.getElementById('step3Next')?.addEventListener('click', async () => {
    const sel = document.getElementById('setupLanguages');
    const values = sel ? Array.from(sel.selectedOptions).map(o => o.value) : [];
    if (!values.length) { alert('Please select at least one language.'); return; }
    try {
      let fd = fdBase('languages');
      values.forEach(v => fd.append('languages[]', v));
      const j = await postToAjax(fd);
      if (!j.success) throw new Error('save failed');
      fd = fdBase('setup_settings_ack'); fd.append('value','1');
      await postToAjax(fd);
      window.location.href='?step=4';
    } catch(e) { console.error(e); window.location.reload(); }
  });

  // Step 4 (Servers/Timezone/Description)
  document.getElementById('step4Next')?.addEventListener('click', async () => {
    const serversSel = document.getElementById('setupServers');
    const servers = serversSel ? Array.from(serversSel.selectedOptions).map(o=>o.value) : [];
    const tz = document.getElementById('setupTimezone')?.value || '';
    const desc = document.getElementById('setupDescription')?.value || '';
    if (!servers.length) { alert('Please select at least one server.'); return; }
    if (!tz) { alert('Please select a timezone.'); return; }
    if (!desc.trim()) { alert('Please write a short description.'); return; }
    try {
      let fd = fdBase('servers'); servers.forEach(v=>fd.append('servers[]',v));
      await postToAjax(fd);
      fd = fdBase('timezone'); fd.append('value', tz);
      await postToAjax(fd);
      fd = fdBase('description'); fd.append('value', desc);
      await postToAjax(fd);
      window.location.href='?step=5';
    } catch(e){ console.error(e); window.location.reload(); }
  });

  // Step 5 game specific setup
  const setupGames = <?= json_encode($games) ?>;

  function syncLolDivision(){
    const tier = parseInt(document.getElementById('rankTier')?.value || '7',10);
    const divWrap = document.getElementById('divWrap');
    const show = (tier === 7);
    if (divWrap) divWrap.style.display = show ? '' : 'none';
    if (!show) {
      const div = document.getElementById('rankDiv');
      if (div) div.value = '4';
    }
  }

  function syncValDivision(){
    const tier = parseInt(document.getElementById('valRankTier')?.value || '6',10);
    const divWrap = document.getElementById('valDivWrap');
    const show = (tier === 6 || tier === 7);
    if (divWrap) divWrap.style.display = show ? '' : 'none';
    if (!show) {
      const div = document.getElementById('valRankDiv');
      if (div) div.value = '3';
    }
  }

  function syncTftDivision(){
    const tier = parseInt(document.getElementById('tftRankTier')?.value || '7',10);
    const divWrap = document.getElementById('tftDivWrap');
    const show = (tier === 7);
    if (divWrap) divWrap.style.display = show ? '' : 'none';
    if (!show) {
      const div = document.getElementById('tftRankDiv');
      if (div) div.value = '4';
    }
  }

  document.getElementById('rankTier')?.addEventListener('change', syncLolDivision);
  document.getElementById('valRankTier')?.addEventListener('change', syncValDivision);
  document.getElementById('tftRankTier')?.addEventListener('change', syncTftDivision);
  syncLolDivision();
  syncValDivision();
  syncTftDivision();

  document.getElementById('step5Next')?.addEventListener('click', async () => {
    try {
      const fd = new FormData();
      fd.append('action', 'booster_update_profile');

      if (setupGames.includes('lol')) {
        const tier = document.getElementById('rankTier')?.value || '7';
        const div = (parseInt(tier,10) === 7) ? (document.getElementById('rankDiv')?.value || '4') : '0';
        const champsSel = document.getElementById('setupChampions');
        const champs = champsSel ? Array.from(champsSel.selectedOptions).map(o=>o.value) : [];
        const rolesSel = document.getElementById('setupRoles');
        const roles = rolesSel ? Array.from(rolesSel.selectedOptions).map(o=>o.value) : [];

        if (!champs.length) { alert('Please select at least one champion.'); return; }
        if (!roles.length) { alert('Please select at least one role.'); return; }

        fd.append('lol', '1');
        fd.append('lol_rank_0', tier);
        fd.append('lol_rank_1', div);
        champs.forEach(v => fd.append('champions[]', v));
        roles.forEach(v => fd.append('roles[]', v));
      }

      if (setupGames.includes('val')) {
        const tier = document.getElementById('valRankTier')?.value || '6';
        const div = (parseInt(tier,10) <= 7) ? (document.getElementById('valRankDiv')?.value || '3') : '0';
        const agentsSel = document.getElementById('setupAgents');
        const agents = agentsSel ? Array.from(agentsSel.selectedOptions).map(o=>o.value) : [];

        if (!agents.length) { alert('Please select at least one agent.'); return; }

        fd.append('val', '1');
        fd.append('val_rank_0', tier);
        fd.append('val_rank_1', div);
        agents.forEach(v => fd.append('agents[]', v));
      }

      if (setupGames.includes('tft')) {
        const tier = document.getElementById('tftRankTier')?.value || '7';
        const div = (parseInt(tier,10) === 7) ? (document.getElementById('tftRankDiv')?.value || '4') : '0';

        fd.append('tft', '1');
        fd.append('tft_rank_0', tier);
        fd.append('tft_rank_1', div);
      }

      const gameProfiles = {};
      document.querySelectorAll('.dynamic-game-profile').forEach(function(section) {
        const specialtySelect = section.querySelector('.js-dynamic-specialties');
        gameProfiles[section.dataset.game] = {
          rank_tier: parseInt(section.querySelector('.js-dynamic-rank')?.value || '0', 10),
          rank_division: parseInt(section.querySelector('.js-dynamic-division')?.value || '0', 10),
          specialties: specialtySelect ? Array.from(specialtySelect.selectedOptions).map(o => o.value) : []
        };
      });
      if (Object.keys(gameProfiles).length) fd.append('game_profiles', JSON.stringify(gameProfiles));

      const res = await fetch('<?= AJAX_URL ?>', { method: 'POST', body: fd });
      const txt = await res.text();
      let json = {};
      try { json = JSON.parse(txt); } catch (e) {}
      if (json.validationErrors) throw new Error(txt || 'validation failed');
      window.location.href='?step=6';
    } catch(e){ console.error(e); window.location.reload(); }
  });

  // Step 6 payout
  const pm = document.getElementById('payoutMethod');
  const payoutFields = document.getElementById('payoutFields');
  const payoutFeedback = document.getElementById('payoutFeedback');
  let payoutSchema = null;

  function setPayoutFeedback(message, type = 'danger') {
    if (!payoutFeedback) return;
    payoutFeedback.className = `alert alert-${type} mt-3`;
    payoutFeedback.textContent = message || '';
    payoutFeedback.classList.toggle('d-none', !message);
  }

  function escapeHtml(value) {
    return String(value ?? '').replace(/[&<>'"]/g, (char) => ({
      '&': '&amp;',
      '<': '&lt;',
      '>': '&gt;',
      "'": '&#039;',
      '"': '&quot;'
    }[char]));
  }

  function renderPayoutFields(schema) {
    if (!payoutFields) return;
    if (!schema || !Array.isArray(schema.fields)) {
      payoutFields.innerHTML = '<div class="alert alert-danger mb-0">Payout fields could not be loaded.</div>';
      return;
    }

    const fieldsHtml = schema.fields.map((field) => {
      const col = field.col || 'col-md-6';
      const name = field.name || '';
      const label = field.label || name;
      const placeholder = field.placeholder || '';
      const required = field.required ? 'required' : '';
      const requiredMark = field.required ? ' <span class="text-danger">*</span>' : '';
      const value = field.default || '';
      return `
        <div class="${escapeHtml(col)}">
          <label class="form-label fw-semibold" for="payoutField_${escapeHtml(name)}">${escapeHtml(label)}${requiredMark}</label>
          <input id="payoutField_${escapeHtml(name)}" class="form-control" data-payout-field="${escapeHtml(name)}" name="${escapeHtml(name)}" value="${escapeHtml(value)}" placeholder="${escapeHtml(placeholder)}" ${required}>
        </div>`;
    }).join('');

    payoutFields.innerHTML = `
      <div class="row g-3">${fieldsHtml}</div>
      ${schema.description ? `<div class="text-muted small mt-2">${escapeHtml(schema.description)}</div>` : ''}
    `;
  }

  async function loadPayoutSchema() {
    if (!pm) return;
    setPayoutFeedback('');
    const fd = new FormData();
    fd.append('action', 'booster_get_payout_method_schema');
    fd.append('method', pm.value || 'crypto');

    try {
      const res = await fetch('<?= AJAX_URL ?>', { method: 'POST', body: fd });
      const txt = await res.text();
      const json = JSON.parse(txt || '{}');
      payoutSchema = json.schema || null;
      renderPayoutFields(payoutSchema);
    } catch (e) {
      console.error(e);
      renderPayoutFields(null);
    }
  }

  pm?.addEventListener('change', loadPayoutSchema);
  loadPayoutSchema();

  document.getElementById('savePayoutBtn')?.addEventListener('click', async () => {
    const btn = document.getElementById('savePayoutBtn');
    const method = pm?.value || 'crypto';
    const makeDefault = document.getElementById('payoutDefault')?.checked ? 1 : 0;
    const fd = new FormData();
    fd.append('action','booster_save_payout_method');
    fd.append('method', method);
    fd.append('make_default', String(makeDefault));

    document.querySelectorAll('[data-payout-field]').forEach((field) => {
      fd.append(field.dataset.payoutField, field.value || '');
    });

    try {
      btn?.setAttribute('disabled', 'disabled');
      setPayoutFeedback('');
      const res = await fetch('<?= AJAX_URL ?>', { method: 'POST', body: fd });
      const txt = await res.text();
      let json = {};
      try { json = JSON.parse(txt || '{}'); } catch (e) {}

      if (json.success || json.refreshPage || json.sendToast?.type === 'success') {
        window.location.reload();
        return;
      }

      setPayoutFeedback(json.sendToast?.message || 'Payout method could not be saved. Please check all required fields.');
      btn?.removeAttribute('disabled');
    } catch(e) {
      console.error(e);
      setPayoutFeedback('Connection error. Please try again.');
      btn?.removeAttribute('disabled');
    }
  });
</script>
<?= $this->end() ?>
