<?= $this->layout('admin/layouts/main', ['meta' => $meta]) ?>

<style>
.games-manager-card{background:#202527;border:1px solid rgba(255,255,255,.07);border-radius:14px;overflow:hidden;box-shadow:0 18px 50px rgba(0,0,0,.18)}
.games-manager-head{padding:20px 22px;border-bottom:1px solid rgba(255,255,255,.06);display:flex;align-items:center;justify-content:space-between;gap:16px;flex-wrap:wrap}
.games-title-wrap{display:flex;align-items:center;gap:10px;flex-wrap:wrap}.games-title-wrap h5{margin:0;font-weight:800;color:#fff}.games-count{background:rgba(139,92,246,.14);color:#c4b5fd;border:1px solid rgba(139,92,246,.25);border-radius:8px;padding:5px 9px;font-size:12px;font-weight:700}
.games-toolbar{padding:14px 22px;border-bottom:1px solid rgba(255,255,255,.06);display:flex;align-items:center;justify-content:space-between;gap:14px;flex-wrap:wrap;background:rgba(255,255,255,.015)}
.games-search{position:relative;min-width:260px;max-width:420px;flex:1}.games-search i{position:absolute;left:14px;top:50%;transform:translateY(-50%);color:#7d8a93;font-size:13px}.games-search input{width:100%;height:40px;border-radius:10px;border:1px solid rgba(255,255,255,.08);background:rgba(0,0,0,.18);color:#fff;padding:0 14px 0 38px;outline:none}.games-search input:focus{border-color:rgba(139,92,246,.55);box-shadow:0 0 0 3px rgba(139,92,246,.12)}
.games-hint{color:#8a969e;font-size:12px;display:flex;align-items:center;gap:7px}.games-hint i{color:#8b5cf6}
.games-table{margin:0;border-collapse:separate;border-spacing:0 10px;padding:8px 16px 16px}.games-table thead th{border:0!important;color:#87949d;text-transform:uppercase;font-size:11px;letter-spacing:.04em;font-weight:800;padding:12px 10px}.games-table tbody tr{background:#252b2e;transition:.16s ease;box-shadow:0 1px 0 rgba(255,255,255,.04) inset}.games-table tbody tr:hover{background:#2b3236;transform:translateY(-1px)}.games-table tbody tr.is-dragging{opacity:.45}.games-table tbody tr.is-hidden-game{opacity:.62}.games-table tbody td{border:0!important;padding:14px 10px;vertical-align:middle}.games-table tbody td:first-child{border-top-left-radius:12px;border-bottom-left-radius:12px}.games-table tbody td:last-child{border-top-right-radius:12px;border-bottom-right-radius:12px}
.drag-handle{width:30px;height:30px;border-radius:9px;display:inline-flex;align-items:center;justify-content:center;color:#7f8c95;background:rgba(255,255,255,.04);cursor:grab}.drag-handle:active{cursor:grabbing}.game-id{font-size:12px;color:#7d8a93;font-weight:700}.gm-icon{width:38px;height:38px;border-radius:11px;object-fit:contain;background:rgba(255,255,255,.06);border:1px solid rgba(255,255,255,.09);padding:3px;flex-shrink:0}.gm-fallback{width:38px;height:38px;border-radius:11px;display:none;align-items:center;justify-content:center;font-size:10px;font-weight:900;flex-shrink:0}.gm-name{font-weight:800;color:#eaf0f4;line-height:1.1}.gm-sub{font-size:12px;color:#89959d;margin-top:3px}.slug-pill{display:inline-flex;align-items:center;gap:6px;border:1px solid rgba(255,255,255,.08);background:rgba(0,0,0,.16);padding:6px 9px;border-radius:9px;color:#aab5bd;font-family:ui-monospace,SFMono-Regular,Menlo,Monaco,Consolas,monospace;font-size:12px}.svc-pill{display:inline-flex;align-items:center;gap:6px;border-radius:9px;padding:6px 9px;font-size:11px;font-weight:900;border:1px solid transparent;line-height:1}.svc-pill.is-active{box-shadow:0 0 0 1px rgba(255,255,255,.05) inset}.svc-pill.is-inactive{background:rgba(148,163,184,.08)!important;color:#7f8c95!important;border-color:rgba(148,163,184,.12);filter:grayscale(1);opacity:.78}.svc-pill .svc-dot{width:7px;height:7px;border-radius:999px;background:currentColor;box-shadow:0 0 10px currentColor}.svc-pill .svc-off{font-size:10px;font-weight:900;opacity:.7;text-transform:uppercase;margin-left:2px}.svc-summary{display:flex;align-items:center;gap:8px;margin-top:8px;font-size:11px;color:#8b98a1}.svc-summary strong{color:#d5dde3}.svc-legend{display:flex;align-items:center;gap:10px;color:#87949d;font-size:11px;font-weight:700}.svc-legend span{display:inline-flex;align-items:center;gap:5px}.svc-legend i{font-size:8px}.svc-legend .on{color:#22c55e}.svc-legend .off{color:#64748b}.sort-pill{min-width:34px;height:28px;border-radius:9px;display:inline-flex;align-items:center;justify-content:center;background:rgba(255,255,255,.05);color:#9ca9b2;font-weight:800;font-size:12px}.boost-count{min-width:30px;height:28px;border-radius:9px;display:inline-flex;align-items:center;justify-content:center;background:rgba(6,182,212,.14);color:#22d3ee;font-weight:900;font-size:12px;border:1px solid rgba(6,182,212,.18)}
.gm-switch{position:relative;display:inline-flex;align-items:center;gap:9px;cursor:pointer;user-select:none}.gm-switch input{display:none}.gm-switch-track{width:44px;height:24px;border-radius:99px;background:rgba(148,163,184,.22);border:1px solid rgba(255,255,255,.08);position:relative;transition:.18s ease}.gm-switch-track:before{content:"";position:absolute;width:18px;height:18px;left:2px;top:2px;border-radius:50%;background:#94a3b8;transition:.18s ease}.gm-switch input:checked+.gm-switch-track{background:rgba(16,185,129,.22);border-color:rgba(16,185,129,.36)}.gm-switch input:checked+.gm-switch-track:before{left:22px;background:#10b981}.gm-switch-text{font-size:12px;font-weight:800;color:#94a3b8}.gm-switch input:checked~.gm-switch-text{color:#34d399}.action-btn{width:34px;height:34px;border-radius:10px;display:inline-flex;align-items:center;justify-content:center;color:#95a1aa;background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.06);transition:.15s ease}.action-btn:hover{color:#fff;background:rgba(139,92,246,.18);border-color:rgba(139,92,246,.35)}.save-sort-state{font-size:12px;color:#8fa1ad;min-height:18px}
@media(max-width:900px){.games-table{min-width:980px}.games-search{min-width:100%}}
</style>

<div class="games-manager-card" id="gamesManager">
    <div class="games-manager-head">
        <div class="games-title-wrap">
            <h5>Games & Services</h5>
            <span class="games-count"><?= count($games) ?> games</span>
        </div>
        <a href="/admin-area/games/create" class="btn btn-primary btn-sm">
            <i class="fa-solid fa-plus me-1"></i> Add Game
        </a>
    </div>

    <?php if (isset($_GET['created'])): ?>
    <div class="alert alert-soft-success m-3 mb-0">
        <i class="fa-solid fa-circle-check me-2"></i>
        Game created successfully. Routes are now live.
    </div>
    <?php endif ?>

    <div class="games-toolbar">
        <div class="games-search">
            <i class="fa-solid fa-magnifying-glass"></i>
            <input type="search" id="gameSearch" placeholder="Search games, slugs or services...">
        </div>
        <div class="games-hint">
            <i class="fa-solid fa-grip-vertical"></i>
            Drag rows to change the order, changes save automatically.
        </div>
        <div class="svc-legend">
            <span><i class="fa-solid fa-circle on"></i> Active</span>
            <span><i class="fa-regular fa-circle off"></i> Inactive</span>
        </div>
        <div class="save-sort-state" id="saveSortState"></div>
    </div>

    <div class="table-responsive">
        <table class="table table-borderless table-nowrap table-align-middle games-table">
            <thead>
                <tr>
                    <th style="width:46px"></th>
                    <th style="width:62px">#</th>
                    <th>Game</th>
                    <th>Slug</th>
                    <th>Active Services</th>
                    <th class="text-center">Boost Forms</th>
                    <th class="text-center">Status</th>
                    <th class="text-center">Sort</th>
                    <th class="text-end">Action</th>
                </tr>
            </thead>
            <tbody id="gamesSortableBody">
                <?php foreach ($games as $game):
                    $services   = is_array($game['services'] ?? null) ? $game['services'] : [];
                    $activeSvcs = array_filter($services, fn($s) => (int)($s['status'] ?? 0) === 1);
                    $svcTypes   = array_column(array_values($activeSvcs), 'service_type');
                    $allSvcTypes = array_column(array_values($services), 'service_type');
                    $svcIcons   = [
                        'boosting' => 'fa-rocket',
                        'accounts' => 'fa-user-circle',
                        'items'    => 'fa-gift',
                        'coaching' => 'fa-headset',
                        'egirl'    => 'fa-users',
                    ];
                    $svcColors  = [
                        'boosting' => 'primary',
                        'accounts' => 'success',
                        'items'    => 'warning',
                        'coaching' => 'info',
                        'egirl'    => 'danger',
                    ];
                    $color   = $game['color_primary'] ?? '#8b5cf6';
                    $short   = strtoupper(substr($game['short_code'] ?: $game['slug'], 0, 3));
                    $iconUrl = !empty($game['icon']) ? $game['icon'] : '/public/assets/website/images/icons/' . $game['slug'] . '.png';
                    $searchText = strtolower(($game['name'] ?? '') . ' ' . ($game['slug'] ?? '') . ' ' . implode(' ', $allSvcTypes));
                ?>
                <tr draggable="true" data-game-id="<?= (int)$game['id'] ?>" data-search="<?= htmlspecialchars($searchText) ?>" class="<?= $game['status'] ? '' : 'is-hidden-game' ?>">
                    <td><span class="drag-handle" title="Drag to reorder"><i class="fa-solid fa-grip-vertical"></i></span></td>
                    <td><span class="game-id">#<?= (int)$game['id'] ?></span></td>
                    <td>
                        <div class="d-flex align-items-center gap-3">
                            <img class="gm-icon" src="<?= htmlspecialchars($iconUrl) ?>"
                                 alt="<?= htmlspecialchars($game['name']) ?>"
                                 onerror="this.style.display='none';this.nextElementSibling.style.display='inline-flex';">
                            <span class="gm-fallback" style="background:<?= htmlspecialchars($color) ?>22;border:1px solid <?= htmlspecialchars($color) ?>44;color:<?= htmlspecialchars($color) ?>;">
                                <?= htmlspecialchars($short) ?>
                            </span>
                            <div>
                                <div class="gm-name"><?= htmlspecialchars($game['name']) ?></div>
                                <div class="gm-sub"><?= count($svcTypes) ?> active services</div>
                            </div>
                        </div>
                    </td>
                    <td><span class="slug-pill"><i class="fa-solid fa-link"></i>/<?= htmlspecialchars($game['slug']) ?></span></td>
                    <td>
                        <div class="d-flex flex-wrap gap-1">
                            <?php if (empty($services)): ?>
                            <span class="text-muted small">No services configured</span>
                            <?php else: foreach ($services as $svc):
                                $t = $svc['service_type'] ?? '';
                                $isActive = (int)($svc['status'] ?? 0) === 1;
                                $tone = $svcColors[$t] ?? 'secondary';
                            ?>
                            <span class="svc-pill <?= $isActive ? 'is-active bg-soft-' . $tone . ' text-' . $tone : 'is-inactive' ?>" title="<?= ucfirst($t) ?> <?= $isActive ? 'active' : 'inactive' ?>">
                                <span class="svc-dot"></span>
                                <i class="fa-solid <?= $svcIcons[$t] ?? 'fa-bolt' ?>"></i>
                                <?= ucfirst($t) ?>
                                <?php if (!$isActive): ?><span class="svc-off">Off</span><?php endif ?>
                            </span>
                            <?php endforeach; endif ?>
                        </div>
                        <div class="svc-summary">
                            <span><strong><?= count($svcTypes) ?></strong> active</span>
                            <span>•</span>
                            <span><strong><?= max(0, count($services) - count($svcTypes)) ?></strong> inactive</span>
                        </div>
                    </td>
                    <td class="text-center"><span class="boost-count"><?= (int)($game['boost_form_count'] ?? 0) ?></span></td>
                    <td class="text-center">
                        <label class="gm-switch" title="Activate or deactivate game">
                            <input type="checkbox" class="game-status-toggle" data-game-id="<?= (int)$game['id'] ?>" <?= $game['status'] ? 'checked' : '' ?>>
                            <span class="gm-switch-track"></span>
                            <span class="gm-switch-text"><?= $game['status'] ? 'Live' : 'Hidden' ?></span>
                        </label>
                    </td>
                    <td class="text-center"><span class="sort-pill sort-value"><?= (int)$game['sort_order'] ?></span></td>
                    <td class="text-end">
                        <a href="/<?= htmlspecialchars($game['slug']) ?>" target="_blank" class="action-btn me-1" data-bs-toggle="tooltip" title="View live page">
                            <i class="fa-solid fa-arrow-up-right-from-square"></i>
                        </a>
                        <a href="/admin-area/games/<?= (int)$game['id'] ?>/edit" class="action-btn" data-bs-toggle="tooltip" title="Edit game & services">
                            <i class="fa-solid fa-pen-to-square"></i>
                        </a>
                    </td>
                </tr>
                <?php endforeach ?>
            </tbody>
        </table>
    </div>
</div>

<script>
(function() {
    const ajaxUrl = '<?= AJAX_URL ?>';
    const tbody = document.getElementById('gamesSortableBody');
    const state = document.getElementById('saveSortState');
    const search = document.getElementById('gameSearch');
    let draggedRow = null;
    let saveTimer = null;

    function showState(text) {
        if (state) state.textContent = text || '';
    }

    function postForm(fd) {
        return fetch(ajaxUrl, { method: 'POST', body: fd, credentials: 'same-origin' })
            .then(r => r.json().catch(() => ({success:false}))); 
    }

    function visibleRows() {
        return Array.from(tbody.querySelectorAll('tr')).filter(row => row.style.display !== 'none');
    }

    function updateSortNumbers() {
        Array.from(tbody.querySelectorAll('tr')).forEach((row, index) => {
            const el = row.querySelector('.sort-value');
            if (el) el.textContent = String(index + 1);
        });
    }

    function saveOrder() {
        clearTimeout(saveTimer);
        saveTimer = setTimeout(() => {
            const ids = Array.from(tbody.querySelectorAll('tr')).map(row => row.dataset.gameId).filter(Boolean);
            const fd = new FormData();
            fd.append('action', 'admin_reorder_games');
            ids.forEach(id => fd.append('game_ids[]', id));
            showState('Saving order...');
            postForm(fd).then(res => {
                showState(res && res.success ? 'Order saved.' : 'Could not save order.');
                setTimeout(() => showState(''), 1800);
            }).catch(() => {
                showState('Could not save order.');
                setTimeout(() => showState(''), 2200);
            });
        }, 250);
    }

    function getRowAfter(container, y) {
        const rows = visibleRows().filter(row => row !== draggedRow);
        let closest = { offset: Number.NEGATIVE_INFINITY, row: null };
        rows.forEach(row => {
            const box = row.getBoundingClientRect();
            const offset = y - box.top - box.height / 2;
            if (offset < 0 && offset > closest.offset) closest = { offset, row };
        });
        return closest.row;
    }

    tbody.addEventListener('dragstart', event => {
        const row = event.target.closest('tr');
        if (!row) return;
        draggedRow = row;
        row.classList.add('is-dragging');
        event.dataTransfer.effectAllowed = 'move';
        event.dataTransfer.setData('text/plain', row.dataset.gameId || '');
    });

    tbody.addEventListener('dragover', event => {
        event.preventDefault();
        if (!draggedRow) return;
        const after = getRowAfter(tbody, event.clientY);
        if (after == null) tbody.appendChild(draggedRow);
        else tbody.insertBefore(draggedRow, after);
        updateSortNumbers();
    });

    tbody.addEventListener('dragend', () => {
        if (!draggedRow) return;
        draggedRow.classList.remove('is-dragging');
        draggedRow = null;
        updateSortNumbers();
        saveOrder();
    });

    document.querySelectorAll('.game-status-toggle').forEach(toggle => {
        toggle.addEventListener('change', function() {
            const row = this.closest('tr');
            const label = this.closest('.gm-switch').querySelector('.gm-switch-text');
            const checked = this.checked;
            const fd = new FormData();
            fd.append('action', 'admin_toggle_game_status');
            fd.append('game_id', this.dataset.gameId);
            fd.append('status', checked ? '1' : '0');
            this.disabled = true;
            postForm(fd).then(res => {
                if (!res || !res.success) throw new Error('save failed');
                if (label) label.textContent = checked ? 'Live' : 'Hidden';
                if (row) row.classList.toggle('is-hidden-game', !checked);
            }).catch(() => {
                this.checked = !checked;
            }).finally(() => {
                this.disabled = false;
            });
        });
    });

    if (search) {
        search.addEventListener('input', function() {
            const q = this.value.trim().toLowerCase();
            Array.from(tbody.querySelectorAll('tr')).forEach(row => {
                row.style.display = !q || (row.dataset.search || '').includes(q) ? '' : 'none';
            });
        });
    }
})();
</script>
