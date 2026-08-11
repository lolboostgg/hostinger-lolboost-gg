<?= $this->layout('seller/layouts/main', ['meta' => ['title' => 'Add Account | LoLBoost.gg', 'h1' => 'List New Account', 'description' => 'Add an account to the marketplace.']]) ?>

<?php require_once dirname(__DIR__) . '/_seller_rank.php'; ?>

<?php $effective_fee = seller_effective_fee_from_rank(is_array($seller_data ?? null) ? $seller_data : []); ?>

<div class="alert alert-soft-primary mb-4">
    <i class="fa-duotone fa-circle-info me-2"></i>
    You earn <strong><?= number_format(100 - $effective_fee, 1) ?>%</strong> of the sale price after the platform fee of <?= number_format($effective_fee, 1) ?>%.
</div>

<form class="ajax-form" action="<?= AJAX_URL ?>" method="POST" enctype="multipart/form-data">
    <input type="hidden" name="action" value="seller_create_account">
    <div class="row g-4">
        <div class="col-lg-8">

            <!-- Game selector -->
            <div class="card mb-4">
                <div class="card-header"><h5 class="card-header-title"><i class="fa-duotone fa-gamepad me-2"></i>Game</h5></div>
                <div class="card-body">
                    <div class="d-flex gap-3 flex-wrap">
                        <?php
                        $_games = function_exists('util_get_all_games') ? util_get_all_games(true) : [];
                        $_allowedRaw = trim((string)($seller_data['allowed_games'] ?? ''));
                        $_allowedSlugs = $_allowedRaw !== '' ? array_filter(array_map('trim', explode(',', $_allowedRaw))) : [];
                        if (!empty($_allowedSlugs)) {
                            $_games = array_values(array_filter($_games, function($g) use ($_allowedSlugs) {
                                return in_array((string)($g['slug'] ?? ''), $_allowedSlugs, true);
                            }));
                        }
                        if (empty($_games)) {
                            $_games = [
                                ['slug'=>'league-of-legends','name'=>'League of Legends'],
                                ['slug'=>'valorant','name'=>'Valorant'],
                            ];
                        }
                        $_first = true;
                        foreach ($_games as $_g):
                            $_slug = (string)($_g['slug'] ?? 'league-of-legends');
                            $_short = function_exists('util_account_short_game_code') ? util_account_short_game_code($_slug) : $_slug;
                            $_submit = in_array($_short, ['lol','val'], true) ? $_short : $_slug;
                            $_name = (string)($_g['name'] ?? $_slug);
                        ?>
                        <label class="d-flex align-items-center gap-2 cursor-pointer">
                            <input type="radio" name="game" value="<?= htmlspecialchars($_submit) ?>" <?= $_first ? 'checked' : '' ?> onchange="switchGame(this.value)">
                            <span class="fw-semibold"><?= htmlspecialchars($_name) ?></span>
                        </label>
                        <?php $_first = false; endforeach; ?>
                    </div>
                    <div class="form-text mt-2">Dynamic games are saved with their own game slug, not as LoL.</div>
                </div>
            </div>

            <div class="card mb-4">
                <div class="card-header"><h5 class="card-header-title"><i class="fa-duotone fa-circle-info me-2"></i>Account Details</h5></div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label">Title <span class="text-danger">*</span></label>
                        <input type="text" name="title" class="form-control" placeholder="e.g. Diamond I EUW – 80k BE" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description <span class="text-danger">*</span></label>
                        <textarea name="description" class="form-control" rows="5" placeholder="Describe the account..." required></textarea>
                    </div>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Price (€) <span class="text-danger">*</span></label>
                            <input type="number" name="price" class="form-control" step="0.01" min="1" placeholder="e.g. 29.99" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Server <span class="text-danger">*</span></label>
                            <!-- LoL servers -->
                            <select name="server" id="serverLol" class="form-select game-field game-field-lol" required>
                                <option value="">Select server</option>
                                <?php foreach (['euw'=>'EUW','eune'=>'EUNE','na'=>'NA','kr'=>'KR','tr'=>'TR','br'=>'BR','lan'=>'LAN','las'=>'LAS','oce'=>'OCE','jp'=>'JP','ru'=>'RU'] as $v=>$l): ?>
                                    <option value="<?= $v ?>"><?= $l ?></option>
                                <?php endforeach ?>
                            </select>
                            <!-- Val servers -->
                            <select name="server" id="serverVal" class="form-select game-field game-field-val" disabled style="display:none">
                                <option value="">Select server</option>
                                <?php foreach (['eu'=>'EU','na'=>'NA','ap'=>'AP','br'=>'BR','latam'=>'LATAM','kr'=>'KR'] as $v=>$l): ?>
                                    <option value="<?= $v ?>"><?= $l ?></option>
                                <?php endforeach ?>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card mb-4">
                <div class="card-header"><h5 class="card-header-title"><i class="fa-duotone fa-ranking-star me-2"></i>Rank Information</h5></div>
                <div class="card-body">

                    <!-- LoL Rank Fields -->
                    <div id="lolRankFields" class="game-field game-field-lol">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label">Current Rank</label>
                                <select name="current_rank" class="form-select">
                                    <?php foreach (['0'=>'Unranked','1'=>'Iron','2'=>'Bronze','3'=>'Silver','4'=>'Gold','5'=>'Platinum','6'=>'Emerald','7'=>'Diamond','8'=>'Master','9'=>'Grandmaster','10'=>'Challenger'] as $v=>$l): ?>
                                        <option value="<?= $v ?>"><?= $l ?></option>
                                    <?php endforeach ?>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Division</label>
                                <select name="current_division" class="form-select">
                                    <option value="0">—</option>
                                    <option value="1">IV</option><option value="2">III</option><option value="3">II</option><option value="4">I</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">LP</label>
                                <input type="number" name="current_lp" class="form-control" min="0" max="100" placeholder="e.g. 75">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Level</label>
                                <input type="number" name="level" class="form-control" min="1" placeholder="e.g. 30">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Blue Essence</label>
                                <input type="number" name="blue_essence" class="form-control" min="0" placeholder="e.g. 80000">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Riot Points</label>
                                <input type="number" name="riot_points" class="form-control" min="0" placeholder="e.g. 0">
                            </div>
                        </div>
                    </div>

                    <!-- Valorant Rank Fields -->
                    <div id="valRankFields" class="game-field game-field-val" style="display:none">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label">Current Rank</label>
                                <select name="val_rank" class="form-select">
                                    <?php foreach (['0'=>'Unranked','1'=>'Iron','2'=>'Bronze','3'=>'Silver','4'=>'Gold','5'=>'Platinum','6'=>'Diamond','7'=>'Ascendant','8'=>'Immortal','9'=>'Radiant'] as $v=>$l): ?>
                                        <option value="<?= $v ?>"><?= $l ?></option>
                                    <?php endforeach ?>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Peak Rank</label>
                                <select name="val_peak_rank" class="form-select">
                                    <?php foreach (['0'=>'Unranked','1'=>'Iron','2'=>'Bronze','3'=>'Silver','4'=>'Gold','5'=>'Platinum','6'=>'Diamond','7'=>'Ascendant','8'=>'Immortal','9'=>'Radiant'] as $v=>$l): ?>
                                        <option value="<?= $v ?>"><?= $l ?></option>
                                    <?php endforeach ?>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Act</label>
                                <input type="text" name="val_act" class="form-control" placeholder="e.g. Episode 10 Act 2">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Level</label>
                                <input type="number" name="level" class="form-control" min="1" placeholder="e.g. 120">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Agents (kommagetrennt)</label>
                                <input type="text" name="agents[]" class="form-control" placeholder="Jett, Reyna, Omen">
                                <div class="form-text">Hauptagenten des Accounts</div>
                            </div>
                        </div>
                    </div>

                    <?php if (!empty($_games)): ?>
                        <?php foreach ($_games as $_g): ?>
                            <?php
                                $_slug = (string)($_g['slug'] ?? '');
                                $_short = function_exists('util_account_short_game_code') ? util_account_short_game_code($_slug) : $_slug;
                                $_submit = in_array($_short, ['lol','val'], true) ? $_short : $_slug;
                                if (in_array($_submit, ['lol','val','league-of-legends','valorant'], true)) continue;
                                $_schemaHtml = function_exists('util_render_account_upload_fields') ? util_render_account_upload_fields($_slug) : '';
                                if (trim($_schemaHtml) === '') continue;
                                $_safeClass = preg_replace('/[^a-z0-9_-]/i', '-', $_submit);
                            ?>
                            <div class="game-field game-field-dynamic game-field-dynamic-<?= htmlspecialchars($_safeClass, ENT_QUOTES, 'UTF-8') ?>" data-game="<?= htmlspecialchars($_submit, ENT_QUOTES, 'UTF-8') ?>" style="display:none">
                                <?= $_schemaHtml ?>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>

                </div>
            </div>
            <div class="card">
                <div class="card-header"><h5 class="card-header-title"><i class="fa-duotone fa-images me-2"></i>Screenshots <span class="text-danger">*</span></h5></div>
                <div class="card-body">
                    <input type="file" name="images[]" class="form-control" multiple accept="image/*" required>
                    <div class="form-text">Upload at least 1 screenshot. PNG/JPG, max 8 MB each.</div>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header"><h5 class="card-header-title"><i class="fa-duotone fa-key me-2"></i>Credentials</h5></div>
                <div class="card-body">
                    <p class="text-muted small mb-3">Only revealed to buyer after purchase.</p>
                    <div class="mb-3"><label class="form-label">In-Game Name</label><input type="text" name="in_game_name" class="form-control" placeholder="Summoner name"></div>
                    <div class="mb-3"><label class="form-label">Login</label><input type="text" name="login" class="form-control" placeholder="Username or email"></div>
                    <div class="mb-3"><label class="form-label">Password</label><input type="text" name="password" class="form-control" placeholder="Account password"></div>
                    <div class="mb-3"><label class="form-label">Account Email</label><input type="text" name="email" class="form-control" placeholder="Linked email"></div>
                    <div class="mb-3"><label class="form-label">Email Password</label><input type="text" name="email_password" class="form-control" placeholder="Email password"></div>
                    <div class="mb-3"><label class="form-label">Delivery Instructions</label><textarea name="delivery_instructions" class="form-control" rows="3" placeholder="Any notes for the buyer..."></textarea></div>
                    <div class="mb-3">
                        <label class="form-label">Delivery Type</label>
                        <select name="delivery_type" class="form-select"><option value="manual">Manual</option><option value="instant">Instant</option></select>
                    </div>
                    <div class="mb-4 form-check">
                        <input type="checkbox" name="has_2fa" id="has_2fa" value="1" class="form-check-input">
                        <label for="has_2fa" class="form-check-label">Account has 2FA enabled</label>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fa-duotone fa-cloud-arrow-up me-2"></i> List Account for Sale
                    </button>
                </div>
            </div>
        </div>
    </div>
</form>

<script>
function switchGame(game) {
    var isLol = (game === 'lol' || game === 'league-of-legends');
    var isVal = (game === 'val' || game === 'valorant');

    document.querySelectorAll('.game-field-lol').forEach(el => {
        el.style.display = isLol ? '' : 'none';
        el.querySelectorAll('select, input, textarea').forEach(f => { f.disabled = !isLol; });
    });
    document.querySelectorAll('.game-field-val').forEach(el => {
        el.style.display = isVal ? '' : 'none';
        el.querySelectorAll('select, input, textarea').forEach(f => { f.disabled = !isVal; });
    });
    document.querySelectorAll('.game-field-dynamic').forEach(el => {
        var active = (el.getAttribute('data-game') === game);
        el.style.display = active ? '' : 'none';
        el.querySelectorAll('select, input, textarea').forEach(f => { f.disabled = !active; });
    });

    var serverLol = document.getElementById('serverLol');
    var serverVal = document.getElementById('serverVal');
    if (serverLol) serverLol.name = isLol ? 'server' : '';
    if (serverVal) serverVal.name = isVal ? 'server' : '';
}
// Init on load
var checkedGame = document.querySelector('input[name="game"]:checked');
switchGame(checkedGame ? checkedGame.value : 'lol');
</script>
