<?php
// ── Online/Offline map ────────────────────────────────────────────────────────
// Driven by the booster's own Online/Away/Offline switch, see app/core/presence.php.
$__onlineBoosterMap = function_exists('lb_booster_online_map') ? lb_booster_online_map() : [];

// ── Val agents JSON (icon URLs) ───────────────────────────────────────────────
$__agentsData = [];
try {
    $__agentsJson = SYS_PATH . '/public/uploads/lists/val-agents.json';
    if (file_exists($__agentsJson)) {
        $__agentsData = json_decode(file_get_contents($__agentsJson), true) ?? [];
    }
} catch (Throwable $e) {}

// ── Val rank tier → name ──────────────────────────────────────────────────────
$__valRankNames = [
    0 => 'Unranked', 1 => 'Iron', 2 => 'Bronze', 3 => 'Silver',
    4 => 'Gold', 5 => 'Platinum', 6 => 'Diamond',
    7 => 'Ascendant', 8 => 'Immortal', 9 => 'Radiant',
];
// Val rank image path pattern (same as LoL but for val)
// File: ASSET_URL/core/main/img/val/ranks/{tier}.png
// Fallback to text if image missing

// ── Game icon map ─────────────────────────────────────────────────────────────
$__gameIconMap = [
    'lol' => 'league-of-legends',
    'val' => 'valorant',
    'tft' => 'teamfight-tactics',
];
$__gameLabels = [
    'lol' => 'League of Legends',
    'val' => 'Valorant',
    'tft' => 'Teamfight Tactics',
];

// ── selected_game passed from filter (e.g. 'val') ────────────────────────────
// This lets us show val stats even for lol|val boosters when val filter is active
$__filterGame = strtolower(trim($selected_game ?? $_POST['game'] ?? ''));

// -- Completed orders helper (same logic as booster view) ----------------------
if (!function_exists('bc_booster_order_stats')) {
    function bc_booster_order_stats(int $boosterId): array {
        $stats = ['total' => 0, 'completed' => 0, 'in_progress' => 0, 'source' => 'fallback'];
        if ($boosterId <= 0) return $stats;

        $tableCandidates      = ['orders', 'booster_orders', 'boost_orders', 'orders_panel'];
        $boosterColCandidates = ['booster_id', 'assigned_booster_id', 'current_booster_id', 'worker_id', 'boosterId'];
        $statusColCandidates  = ['status', 'order_status', 'state'];

        global $db, $pdo, $conn, $database;
        $runSelect = function(string $sql, array $params) use ($db, $pdo, $conn, $database) {
            if (isset($db) && is_object($db) && method_exists($db, 'run')) {
                return $db->run(str_replace(':bid', (string)(int)$params[':bid'], $sql));
            }

            $p = null;
            if (isset($pdo) && $pdo instanceof \PDO) $p = $pdo;
            elseif (isset($conn) && $conn instanceof \PDO) $p = $conn;
            elseif (isset($database) && $database instanceof \PDO) $p = $database;

            if ($p) {
                $st = $p->prepare($sql);
                $st->execute($params);
                return $st->fetchAll(\PDO::FETCH_ASSOC);
            }

            throw new \RuntimeException('No DB handle');
        };

        foreach ($tableCandidates as $table) {
            foreach ($boosterColCandidates as $bcol) {
                foreach ($statusColCandidates as $scol) {
                    try {
                        $rows = $runSelect("SELECT {$scol} AS st, COUNT(*) AS c FROM {$table} WHERE {$bcol} = :bid GROUP BY {$scol}", [':bid' => $boosterId]);
                        if (!is_array($rows) || empty($rows)) return $stats;

                        $total = $completed = $inprog = 0;
                        foreach ($rows as $r) {
                            $st = strtolower(trim((string)($r['st'] ?? '')));
                            $c  = (int)($r['c'] ?? 0);
                            $total += $c;

                            if (
                                in_array($st, ['completed', 'complete', 'finished', 'done', 'success'], true) ||
                                str_contains($st, 'complete') ||
                                str_contains($st, 'finish')
                            ) {
                                $completed += $c;
                            } elseif (
                                in_array($st, ['in_progress', 'in progress', 'progress', 'active', 'ongoing', 'running', 'started'], true) ||
                                str_contains($st, 'progress') ||
                                str_contains($st, 'active') ||
                                str_contains($st, 'ongoing')
                            ) {
                                $inprog += $c;
                            }
                        }

                        return [
                            'total'       => $total,
                            'completed'   => $completed,
                            'in_progress' => $inprog,
                            'source'      => "{$table}.{$bcol}.{$scol}",
                        ];
                    } catch (\Throwable $e) {}
                }
            }
        }

        return $stats;
    }
}

?>
<style>
.booster-card .completed-orders-badge {
    display:inline-flex;
    align-items:center;
    justify-content:center;
    gap:.42rem;
    width:max-content;
    margin-top:.52rem;
    padding:.38rem .78rem;
    border-radius:999px;
    border:1.5px solid rgba(255,255,255,.92);
    background:rgba(255,255,255,.035);
    color:#fff;
    font-size:.82rem;
    font-weight:800;
    line-height:1;
    letter-spacing:.01em;
    white-space:nowrap;
    box-shadow:0 0 0 1px rgba(255,255,255,.04) inset, 0 .45rem 1rem rgba(0,0,0,.26), 0 0 1.1rem rgba(255,255,255,.08);
    text-shadow:0 0 .55rem rgba(255,255,255,.18);
}
.booster-card .completed-orders-badge i {
    color:#fff;
    font-size:1rem;
    line-height:1;
}
.booster-card .booster-timezone {
    display:flex;
    align-items:center;
    gap:.38rem;
    width:max-content;
    max-width:100%;
    margin-top:.38rem;
    color:rgba(235,232,255,.68);
    font-size:.76rem;
    font-weight:650;
    line-height:1.2;
}
.booster-card .booster-timezone i {
    color:#8b78ff;
    font-size:.72rem;
}
.booster-card .booster-timezone span {
    overflow:hidden;
    text-overflow:ellipsis;
    white-space:nowrap;
}
@media (max-width: 767px) {
    .booster-card .completed-orders-badge {
        margin-top:.42rem;
        padding:.32rem .62rem;
        font-size:.76rem;
        gap:.34rem;
    }
    .booster-card .completed-orders-badge i { font-size:.92rem; }
}
</style>

<?php if (empty($boosters)): ?>
    <p class="no-boosters"><?= t('No boosters found with the selected filters.') ?></p>
<?php else:
    foreach ($boosters as $booster):
        $boosterId   = (int)($booster['booster_id'] ?? $booster['id'] ?? 0);
        $__orderStats = bc_booster_order_stats($boosterId);
        $__completedOrders = (int)($__orderStats['completed'] ?? 0);
        $isOnline    = !empty($__onlineBoosterMap[$boosterId]);
        $statusText  = $isOnline ? t('Online') : t('Offline');
        $statusClass = $isOnline ? 'online' : 'offline';

        // ── Parse booster's games ─────────────────────────────────────────────
        $__gamesRaw  = strtolower(trim($booster['games'] ?? 'lol'));
        $__gameList  = array_values(array_filter(array_map('trim', explode('|', $__gamesRaw))));
        if (empty($__gameList)) $__gameList = ['lol'];

        $__hasLol = in_array('lol', $__gameList);
        $__hasVal = in_array('val', $__gameList);
        $__hasTft = in_array('tft', $__gameList);

        // ── Decide which game's stats to SHOW on card ─────────────────────────
        // If filter is active → show that game's stats (if booster has it)
        // Otherwise → show primary (first) game
        if ($__filterGame !== '' && in_array($__filterGame, $__gameList, true)) $__showGame = $__filterGame;
        else $__showGame = $__gameList[0];

        $__dynamicProfiles = lb_booster_game_profiles($boosterId);
        $__dynamicProfile = (array)($__dynamicProfiles[$__showGame] ?? []);
        $__dynamicConfig = lb_generic_game_rank_config($__showGame) ?? [];
        $__dynamicTier = (int)($__dynamicProfile['rank_tier'] ?? 0);
        $__dynamicDivision = (int)($__dynamicProfile['rank_division'] ?? 0);
        $__dynamicHasRanks = !empty($__dynamicConfig['ranks']) && is_array($__dynamicConfig['ranks']);
        $__dynamicRankName = $__dynamicHasRanks && $__dynamicTier > 0
            ? (string)(($__dynamicConfig['ranks'] ?? [])[$__dynamicTier] ?? '')
            : '';
        $__dynamicSpecialtyOptions = lb_booster_game_specialty_options($__showGame);
        $__dynamicSpecialtyMap = array_column($__dynamicSpecialtyOptions, null, 'key');

        // ── Parse val_rank: "tier|division" e.g. "9|1" ───────────────────────
        $__valTier = 0; $__valDiv = 0;
        if (!empty($booster['val_rank'])) {
            $__vp = explode('|', $booster['val_rank']);
            $__valTier = (int)($__vp[0] ?? 0);
            $__valDiv  = (int)($__vp[1] ?? 0);
        }
        $__valRankName = $__valRankNames[$__valTier] ?? 'Unranked';
        $__valDivName  = ($__valTier > 0 && $__valTier < 7 && $__valDiv > 0)
            ? ' ' . ['I'=>'I','1'=>'I',2=>'II',3=>'III',4=>'IV'][$__valDiv] ?? ''
            : '';
        $__valRankLabel = $__valRankName . $__valDivName;

        // ── Parse TFT rank ────────────────────────────────────────────────────
        $__tftTier = 0;
        if (!empty($booster['tft_rank'])) {
            $__tp = explode('|', $booster['tft_rank']);
            $__tftTier = (int)($__tp[0] ?? 0);
        }

        $__lolTier = 0;
        if (!empty($booster['lol_rank'])) {
            $__lp = explode('|', $booster['lol_rank']);
            $__lolTier = (int)($__lp[0] ?? 0);
        }

        // ── Parse agents ──────────────────────────────────────────────────────
        $__agents = [];
        if (!empty($booster['agents'])) {
            $__agents = array_values(array_filter(array_map('trim', explode('|', $booster['agents']))));
        }

        // ── Cover games list (for icons + rank box) ───────────────────────────
        // Keep the actively filtered game first, then show a compact preview of
        // the remaining games. The full remainder is available in the +N tooltip.
        $__coverGames = $__gameList;
        if (in_array($__showGame, $__coverGames, true)) {
            $__coverGames = array_values(array_unique(array_merge([$__showGame], $__coverGames)));
        }
        $__visibleCoverGames = array_slice($__coverGames, 0, 4);
        $__hiddenCoverGames = array_slice($__coverGames, 4);
?>
        <a href="<?= BASE_URL . '/boosters/' . $boosterId ?>" class="cover-link">
            <div class="booster-card"
                 data-pg-prices="<?= htmlspecialchars($booster['pg_prices'] ?? '{}', ENT_QUOTES) ?>"
                 data-service-prices="<?= htmlspecialchars($booster['service_prices'] ?? '{}', ENT_QUOTES) ?>"
                 data-description="<?= htmlspecialchars(strip_tags($booster['description'] ?? ''), ENT_QUOTES) ?>"
                 data-timezone="<?= htmlspecialchars($booster['timezone'] ?? '', ENT_QUOTES) ?>">

                <!-- ═══ COVER ═══ -->
                <div class="cover"
                     style="background-image: url('<?= $booster['cover'] ?? ASSET_URL . '/core/main/img/banners/leona.jpeg' ?>');">

                    <!-- Status pill — top left -->
                    <span class="cover-status <?= $statusClass ?>">
                        <span class="sdot"></span>
                        <?= $statusText ?>
                    </span>

                    <!-- Game icons + Rank — top right -->
                    <div class="cover-games">
                        <!-- Game icons row (top) -->
                        <div class="cover-game-icons">
                            <?php foreach ($__visibleCoverGames as $__g):
                                $__gIcon = util_game_icon_url($__g);
                                $__gLabel = util_game_display_name($__g);
                                if ($__gIcon === '') continue; ?>
                                <span class="cover-game-icon" title="<?= htmlspecialchars($__gLabel, ENT_QUOTES) ?>">
                                    <img src="<?= htmlspecialchars($__gIcon, ENT_QUOTES) ?>"
                                         alt="<?= htmlspecialchars($__gLabel, ENT_QUOTES) ?>">
                                </span>
                            <?php endforeach; ?>
                            <?php if ($__hiddenCoverGames):
                                $__hiddenGameItems = array_map(static fn($__game) => [
                                    'label' => util_game_display_name($__game),
                                    'img' => util_game_icon_url($__game),
                                ], $__hiddenCoverGames);
                            ?>
                                <span class="cover-game-more" tabindex="0" data-games="<?= htmlspecialchars(json_encode($__hiddenGameItems, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE), ENT_QUOTES, 'UTF-8') ?>" aria-label="<?= count($__hiddenCoverGames) ?> more games">
                                    +<?= count($__hiddenCoverGames) ?>
                                </span>
                            <?php endif; ?>
                        </div>

                        <!-- Rank icon — hidden for val, shown for lol/tft -->
                        <?php
                        $__dynamicRankIcon = (!in_array($__showGame, ['lol', 'tft', 'val'], true) && $__dynamicTier > 0)
                            ? lb_booster_game_rank_icon_url($__showGame, $__dynamicTier)
                            : '';
                        $__hasCoverRank =
                            ($__showGame === 'lol' && $__lolTier > 0) ||
                            ($__showGame === 'tft' && $__tftTier > 0) ||
                            (!in_array($__showGame, ['lol', 'tft', 'val'], true) && $__dynamicHasRanks && $__dynamicTier > 0 && $__dynamicRankName !== '' && $__dynamicRankIcon !== '');
                        ?>
                        <?php if ($__hasCoverRank): ?>
                        <div class="rank-box">
                            <?php if ($__showGame === 'tft'): ?>
                                <img class="rank_icon"
                                     src="<?= ASSET_URL ?>/core/main/img/lol/ranks/max/<?= $__tftTier ?>.png"
                                     alt="TFT rank"
                                     onerror="this.src='<?= ASSET_URL ?>/core/main/img/lol/ranks/max/0.png'">
                            <?php elseif ($__showGame === 'lol'): ?>
                                <img class="rank_icon"
                                     src="<?= ASSET_URL ?>/core/main/img/lol/ranks/max/<?= $__lolTier ?>.png"
                                     alt="rank-icon"
                                     onerror="this.src='<?= ASSET_URL ?>/core/main/img/lol/ranks/max/0.png'">
                            <?php else: ?>
                                <img class="rank_icon" src="<?= htmlspecialchars($__dynamicRankIcon, ENT_QUOTES) ?>" alt="<?= htmlspecialchars($__dynamicRankName, ENT_QUOTES) ?>" onerror="this.parentElement.remove()">
                            <?php endif; ?>
                        </div>
                        <?php endif; ?>
                    </div>

                </div><!-- /.cover -->

                <!-- ═══ AVATAR ═══ -->
                <div class="avatar">
                    <img src="<?= $booster['icon'] ?>" alt="...">
                    <span class="booster-online-dot <?= $statusClass ?>"></span>
                </div>

                <!-- ═══ DETAILS ═══ -->
                <div class="details">
                    <div class="top">
                        <div>
                            <h5>
                                <span class="name-text"><?= htmlspecialchars($booster['username'] ?? '', ENT_QUOTES) ?></span>
                                <i class="fa-solid fa-badge-check verify-icon"></i>
                                <span class="rating-badge">
                                    <img src="<?= ASSET_URL ?>/website/images/boosters/star.svg" alt="star">
                                    <?= number_format((float)($booster['rating'] ?? 0), 1) ?>
                                    <?php if (!empty($booster['review_count'])): ?>
                                        <span class="review-count">(<?= (int)$booster['review_count'] ?>)</span>
                                    <?php endif; ?>
                                </span>
                            </h5>
                            <?php $__boosterTimezone = trim((string)($booster['timezone'] ?? '')); ?>
                            <?php if ($__boosterTimezone !== ''): ?>
                                <div class="booster-timezone" title="<?= htmlspecialchars($__boosterTimezone, ENT_QUOTES) ?>">
                                    <i class="fa-solid fa-earth-europe" aria-hidden="true"></i>
                                    <span><?= htmlspecialchars($__boosterTimezone, ENT_QUOTES) ?></span>
                                </div>
                            <?php endif; ?>
                            <div class="completed-orders-badge" title="<?= t('Completed orders') ?>">
                                <i class="fa-solid fa-circle-check"></i>
                                <span><?= $__completedOrders ?> <?= t('Completed Orders') ?></span>
                            </div>
                        </div>
                    </div>

                    <!-- ── MID ── -->
                    <?php
                    $__hasRoles = !empty($booster['roles']);
                    $__showMid =
                        ($__showGame === 'val' && $__valTier > 0) ||
                        ($__showGame === 'tft' && ($__tftTier > 0 || $__hasRoles)) ||
                        ($__showGame === 'lol' && $__hasRoles) ||
                        (!in_array($__showGame, ['lol', 'tft', 'val'], true) && $__dynamicHasRanks && $__dynamicTier > 0 && $__dynamicRankName !== '');
                    ?>
                    <?php if ($__showMid): ?>
                    <div class="mid">
                    <?php if ($__showGame === 'val'): ?>
                        <?php if ($__valTier > 0): ?>
                            <span class="bc-val-rank-badge tier-<?= $__valTier ?>">
                                <img src="<?= ASSET_URL ?>/core/main/img/val/ranks/mini/<?= $__valTier ?>.png"
                                     alt="<?= htmlspecialchars($__valRankName, ENT_QUOTES) ?>"
                                     onerror="this.style.display='none'">
                                <?= htmlspecialchars($__valRankLabel, ENT_QUOTES) ?>
                            </span>
                        <?php endif; ?>
                    <?php elseif ($__showGame === 'tft'): ?>
                        <?php
                        $__tftRankNames = [0=>'Unranked',1=>'Iron',2=>'Bronze',3=>'Silver',4=>'Gold',5=>'Platinum',6=>'Diamond',7=>'Master',8=>'Grandmaster',9=>'Challenger'];
                        ?>
                        <?php if ($__tftTier > 0): ?>
                            <span class="bc-val-rank-badge tier-tft">
                                <?= htmlspecialchars($__tftRankNames[$__tftTier] ?? '', ENT_QUOTES) ?> TFT
                            </span>
                        <?php endif; ?>
                        <?php if (!empty($booster['roles'])):
                            foreach (explode('|', $booster['roles']) as $role): ?>
                                <span class="role-icon">
                                    <img src="<?= ASSET_URL ?>/core/main/img/lol/roles/<?= htmlspecialchars($role, ENT_QUOTES) ?>.png" alt="<?= htmlspecialchars($role, ENT_QUOTES) ?>">
                                </span>
                        <?php endforeach; endif; ?>
                    <?php elseif ($__showGame === 'lol'): ?>
                        <?php if (!empty($booster['roles'])): ?>
                            <?php foreach (explode('|', $booster['roles']) as $role): ?>
                                <span class="role-icon">
                                    <img src="<?= ASSET_URL ?>/core/main/img/lol/roles/<?= htmlspecialchars($role, ENT_QUOTES) ?>.png" alt="<?= htmlspecialchars($role, ENT_QUOTES) ?>">
                                </span>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    <?php else: ?>
                        <span class="bc-val-rank-badge tier-dynamic">
                            <?= htmlspecialchars($__dynamicRankName, ENT_QUOTES) ?><?= $__dynamicDivision > 0 ? ' ' . htmlspecialchars(['','I','II','III','IV','V'][$__dynamicDivision] ?? '', ENT_QUOTES) : '' ?>
                        </span>
                    <?php endif; ?>
                    </div>
                    <?php endif; ?>

                    <!-- ── BOTTOM: items + languages ── -->
                    <div class="bottom">
                        <?php
                        $__dynamicSpecialties = (array)($__dynamicProfile['specialties'] ?? []);
                        $__hasCardItems =
                            ($__showGame === 'val' && !empty($__agents)) ||
                            (in_array($__showGame, ['lol', 'tft'], true) && !empty($booster['champions'])) ||
                            (!in_array($__showGame, ['lol', 'tft', 'val'], true) && !empty($__dynamicSpecialtyOptions) && !empty($__dynamicSpecialties));
                        ?>
                        <?php if ($__hasCardItems): ?>
                        <div class="champions">
                        <?php
                        if ($__showGame === 'val') {
                            // Agents in bottom row
                            $__maxA   = 3; $__shownA = 0; $__totalA = count($__agents);
                            foreach ($__agents as $__ag) {
                                if ($__shownA >= $__maxA) break;
                                $__agKey  = trim($__ag);
                                $__agIcon = $__agentsData[$__agKey]['icon'] ?? '';
                                $__agName = $__agentsData[$__agKey]['name'] ?? $__agKey;
                                if (!$__agIcon) { $__shownA++; continue; }
                                echo '<img class="champion-icon" src="' . htmlspecialchars($__agIcon, ENT_QUOTES) . '" alt="' . htmlspecialchars($__agName, ENT_QUOTES) . '" title="' . htmlspecialchars($__agName, ENT_QUOTES) . '" onerror="this.style.display=\'none\'">';
                                $__shownA++;
                            }
                            if ($__totalA > $__maxA) {
                                $__remA    = $__totalA - $__maxA;
                                $__remList = array_slice($__agents, $__maxA);
                                echo '<span class="more-champions-icon" tabindex="0">+' . $__remA;
                                echo '<span class="champs-tooltip"><span class="tt-title">' . t('More Agents') . '</span><span class="tt-list">';
                                foreach ($__remList as $__ra) {
                                    $__raKey  = trim($__ra);
                                    $__raIcon = $__agentsData[$__raKey]['icon'] ?? '';
                                    $__raName = $__agentsData[$__raKey]['name'] ?? $__raKey;
                                    echo '<span class="tt-item">';
                                    if ($__raIcon) echo '<img src="' . htmlspecialchars($__raIcon, ENT_QUOTES) . '" alt="' . htmlspecialchars($__raName, ENT_QUOTES) . '">';
                                    echo '<span>' . htmlspecialchars($__raName, ENT_QUOTES) . '</span></span>';
                                }
                                echo '</span></span></span>';
                            }
                        } elseif ($__showGame === 'lol' || $__showGame === 'tft') {
                            // Champions (LoL / TFT)
                            if (!empty($booster['champions'])) {
                                $champions   = explode('|', $booster['champions']);
                                $max_display = 3; $displayed = 0; $total = count($champions);
                                foreach ($champions as $champion) {
                                    if ($displayed >= $max_display) break;
                                    echo '<img class="champion-icon" src="' . LOL_CHAMP_URL . '/' . htmlspecialchars($champion, ENT_QUOTES) . '.png" alt="' . htmlspecialchars($champion, ENT_QUOTES) . '">';
                                    $displayed++;
                                }
                                if ($total > $max_display) {
                                    $remaining      = $total - $max_display;
                                    $remaining_list = array_slice($champions, $max_display);
                                    echo '<span class="more-champions-icon" tabindex="0">+' . $remaining;
                                    echo '<span class="champs-tooltip"><span class="tt-title">' . t('More champions') . '</span><span class="tt-list">';
                                    foreach ($remaining_list as $__c) {
                                        $cName = htmlspecialchars($__c, ENT_QUOTES, 'UTF-8');
                                        echo '<span class="tt-item"><img src="' . LOL_CHAMP_URL . '/' . $cName . '.png" alt="' . $cName . '"><span>' . $cName . '</span></span>';
                                    }
                                    echo '</span></span></span>';
                                }
                            }
                        } else {
                            $__specialties = $__dynamicSpecialties;
                            $__maxSpecialties = 3;
                            foreach (array_slice($__specialties, 0, $__maxSpecialties) as $__specialtyKey) {
                                $__specialty = $__dynamicSpecialtyMap[$__specialtyKey] ?? null;
                                if (!$__specialty) continue;
                                echo '<img class="champion-icon" src="' . htmlspecialchars($__specialty['icon'], ENT_QUOTES) . '" alt="' . htmlspecialchars($__specialty['name'], ENT_QUOTES) . '" title="' . htmlspecialchars($__specialty['name'], ENT_QUOTES) . '">';
                            }
                            if (count($__specialties) > $__maxSpecialties) echo '<span class="more-champions-icon">+' . (count($__specialties) - $__maxSpecialties) . '</span>';
                        }
                        ?>
                        </div>
                        <?php endif; ?>

                        <div class="languages">
                            <?php
                            if (!empty($booster['languages'])) {
                                $languages   = explode('|', $booster['languages']);
                                $max_lang    = 3; $shown = 0; $total_langs = count($languages);
                                foreach ($languages as $lang) {
                                    if ($shown >= $max_lang) break;
                                    $langSafe = htmlspecialchars(trim($lang), ENT_QUOTES, 'UTF-8');
                                    echo '<span class="lang-icon" title="' . $langSafe . '"><img src="' . ASSET_URL . '/core/main/img/languages/' . $langSafe . '.png" alt="' . $langSafe . '"></span>';
                                    $shown++;
                                }
                                if ($total_langs > $max_lang) {
                                    $remaining      = $total_langs - $max_lang;
                                    $remaining_list = array_slice($languages, $max_lang);
                                    echo '<span class="more-lang-icon" tabindex="0">+' . (int)$remaining;
                                    echo '<span class="langs-tooltip"><span class="tt-title">' . t('More languages') . '</span><span class="tt-list">';
                                    foreach ($remaining_list as $__l) {
                                        $lName = htmlspecialchars(trim($__l), ENT_QUOTES, 'UTF-8');
                                        echo '<span class="tt-item"><img src="' . ASSET_URL . '/core/main/img/languages/' . $lName . '.png" alt="' . $lName . '"><span>' . $lName . '</span></span>';
                                    }
                                    echo '</span></span></span>';
                                }
                            } else {
                                echo '<span class="more-lang-icon">N/A</span>';
                            }
                            ?>
                        </div>
                    </div>

                </div><!-- /.details -->
            </div><!-- /.booster-card -->
        </a>
<?php endforeach; endif; ?>
<script>
(function(){
    var tt      = document.getElementById('bc-global-tooltip');
    var ttTitle = tt ? tt.querySelector('.bc-tt-title') : null;
    var ttList  = tt ? tt.querySelector('.bc-tt-list')  : null;
    var hideTimer;

    function showTooltip(trigger, title, items) {
        if (!tt || !items.length) return;
        clearTimeout(hideTimer);
        ttTitle.textContent = title;
        ttList.innerHTML = '';
        items.forEach(function(item) {
            var el   = document.createElement('div');
            el.className = 'bc-tt-item';
            if (item.img) {
                var img  = document.createElement('img');
                img.src  = item.img; img.alt = item.label;
                el.appendChild(img);
            }
            var span = document.createElement('span');
            span.textContent = item.label;
            el.appendChild(span);
            ttList.appendChild(el);
        });

        tt.style.display = 'block';
        tt.classList.remove('is-visible');

        var zoom = 1;
        try {
            var bcr = document.documentElement.getBoundingClientRect();
            if (document.documentElement.offsetWidth && bcr.width)
                zoom = bcr.width / document.documentElement.offsetWidth;
            if (!zoom || isNaN(zoom) || zoom <= 0) zoom = 1;
        } catch(e) { zoom = 1; }

        var rect  = trigger.getBoundingClientRect();
        var ttW   = tt.offsetWidth;
        var ttH   = tt.offsetHeight;
        var cx    = (rect.left + rect.width  / 2) / zoom;
        var ty    = rect.top / zoom;
        var left  = cx - ttW / 2;
        var top   = ty - ttH - 12;
        var vpW   = window.innerWidth / zoom;
        var vpH   = window.innerHeight / zoom;
        var below = false;
        if (top < 8) {
            top = rect.bottom / zoom + 12;
            below = true;
        }
        if (top + ttH > vpH - 8) top = Math.max(8, vpH - ttH - 8);
        left = Math.max(8, Math.min(left, vpW - ttW - 8));
        tt.style.left = left + 'px';
        tt.style.top  = top  + 'px';
        tt.classList.toggle('is-below', below);
        var arrowLeft = Math.max(16, Math.min(cx - left, ttW - 16));
        tt.style.setProperty('--arrow-left', arrowLeft + 'px');
        requestAnimationFrame(function() { tt.classList.add('is-visible'); });
    }

    function hideTooltip() {
        if (!tt) return;
        tt.classList.remove('is-visible');
        hideTimer = setTimeout(function() { tt.style.display = 'none'; }, 150);
    }

    function initTooltips() {
        document.querySelectorAll('.cover-game-more').forEach(function(el) {
            if (el.dataset.ttInited) return;
            el.dataset.ttInited = '1';
            function openGamesTooltip() {
                var games = [];
                try { games = JSON.parse(el.dataset.games || '[]'); } catch (e) {}
                showTooltip(el, 'More Games', games);
            }
            el.addEventListener('mouseenter', openGamesTooltip);
            el.addEventListener('focus', openGamesTooltip);
            el.addEventListener('mouseleave', hideTooltip);
            el.addEventListener('blur', hideTooltip);
        });
        document.querySelectorAll('.more-champions-icon').forEach(function(el) {
            if (el.dataset.ttInited) return;
            el.dataset.ttInited = '1';
            var inner = el.querySelector('.champs-tooltip');
            if (inner) inner.style.cssText = 'display:none!important;visibility:hidden;';
            el.addEventListener('mouseenter', function() {
                var rows = [];
                if (inner) inner.querySelectorAll('.tt-item').forEach(function(row) {
                    var img  = row.querySelector('img');
                    var name = row.querySelector('span:last-child');
                    rows.push({ img: img ? img.src : '', label: name ? name.textContent.trim() : '' });
                });
                var titleEl = inner ? inner.querySelector('.tt-title') : null;
                showTooltip(el, titleEl ? titleEl.textContent : 'More', rows);
            });
            el.addEventListener('mouseleave', hideTooltip);
        });
        document.querySelectorAll('.more-lang-icon').forEach(function(el) {
            if (el.dataset.ttInited) return;
            el.dataset.ttInited = '1';
            var inner = el.querySelector('.langs-tooltip');
            if (inner) inner.style.cssText = 'display:none!important;visibility:hidden;';
            el.addEventListener('mouseenter', function() {
                var rows = [];
                if (inner) inner.querySelectorAll('.tt-item').forEach(function(row) {
                    var img  = row.querySelector('img');
                    var name = row.querySelector('span:last-child');
                    rows.push({ img: img ? img.src : '', label: name ? name.textContent.trim() : '' });
                });
                showTooltip(el, 'More Languages', rows);
            });
            el.addEventListener('mouseleave', hideTooltip);
        });
    }

    if (tt) {
        tt.addEventListener('mouseenter', function(){ clearTimeout(hideTimer); });
        tt.addEventListener('mouseleave', hideTooltip);
    }

    var styleEl = document.createElement('style');
    styleEl.textContent = '#bc-global-tooltip::after { left: var(--arrow-left, 50%); transform: translateX(-50%) rotate(45deg); }';
    document.head.appendChild(styleEl);

    initTooltips();
    var grid = document.getElementById('boosters');
    if (grid) new MutationObserver(function() { initTooltips(); }).observe(grid, { childList: true, subtree: true });
})();
</script>
