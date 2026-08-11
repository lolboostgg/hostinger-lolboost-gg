<?php
/**
 * GG-Girl Cards Partial — v6
 *
 * FIXES:
 *   - ASSET_URL used directly (same as view.php — no /public/ prefix)
 *   - Cover rendered as <img> tag (like view.php), not background-image
 *   - Language flags: same map + same ASSET_URL pattern as view.php
 *   - Game icons: same path as view.php
 *   - Sessions removed
 */

// ── Online/Offline map ─────────────────────────────────────────────────────────
// GG-Girls are booster accounts with is_egirl = 1, so they share the booster availability switch.
$__onlineEgirlMap = $__onlineEgirlMap ?? [];
if (empty($__onlineEgirlMap) && function_exists('lb_booster_online_map')) {
    $__onlineEgirlMap = lb_booster_online_map();
}

// ── Game icons — SAME as view.php ─────────────────────────────────────────────
$__gimap = [
    'lol' => 'league-of-legends',
    'val' => 'valorant',
    'tft' => 'teamfight-tactics',
];
$__gameLabels = [
    'lol' => 'League of Legends',
    'val' => 'Valorant',
    'tft' => 'Teamfight Tactics',
];
// Every boosting game a girl can select in her dashboard — otherwise Apex,
// Rocket League, CS2 … would silently drop off the card.
$__egGameOpts = function_exists('lb_egirl_game_options') ? lb_egirl_game_options() : [];
$__egGameIcon = static function (string $key) use ($__egGameOpts, $__gimap): string {
    if (!empty($__egGameOpts[$key]['icon'])) return (string)$__egGameOpts[$key]['icon'];
    if (isset($__gimap[$key])) return ASSET_URL . '/website/images/icons/' . $__gimap[$key] . '.png';
    return function_exists('util_game_icon_url') ? (string)util_game_icon_url($key) : '';
};
$__egGameLabel = static function (string $key) use ($__egGameOpts, $__gameLabels): string {
    return $__gameLabels[$key] ?? ($__egGameOpts[$key]['label'] ?? strtoupper($key));
};

// ── Language map — SAME as view.php ───────────────────────────────────────────
$__langImgMap = [
    'en'=>'en.png','de'=>'de.png','fr'=>'fr.png','es'=>'es.png','tr'=>'tr.png',
    'pt'=>'pt.png','it'=>'it.png','pl'=>'pl.png','ru'=>'ru.webp','nl'=>'nl.png',
    'sv'=>'sv.png','da'=>'da.webp','no'=>'no.webp','fi'=>'fi.webp','cs'=>'cz.webp',
    'ro'=>'ro.png','hu'=>'hu.webp','uk'=>'uk.png','ar'=>'ar.png','zh'=>'chinese.png',
    'ja'=>'ja.webp','ko'=>'ko.png','el'=>'el.png','hr'=>'hr.png','bg'=>'bg.webp',
    'vn'=>'vn.webp','ph'=>'ph.webp','th'=>'th.webp',
];
// ASSET_URL used directly — same as view.php line 751
$__langBase = ASSET_URL . '/core/main/img/languages/';
$__decodeCardText = static function ($text) {
    $text = (string)($text ?? '');
    for ($i = 0; $i < 3; $i++) {
        $decoded = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        if ($decoded === $text) {
            break;
        }
        $text = $decoded;
    }
    return $text;
};
?>

<style>
/* ═══════════════════════════════════════════════════
   EGC CARD v6  —  Egirl-themed, booster layout
   ═══════════════════════════════════════════════════ */
.egc-wrap { display:block; text-decoration:none; color:inherit; position:relative; }
.egc-wrap:hover { text-decoration:none; color:inherit; }

/* Card shell */
.egc-wrap { display:block; min-width:0; max-width:100%; width:100%; }
.egc {
    display:flex; flex-direction:column;
    background:#0f0b1e;
    border:1px solid rgba(236,72,153,.18);
    border-radius:20px;
    overflow:visible;
    position:relative;
    height:100%;
    min-width:0; max-width:100%;
    transition:transform .28s cubic-bezier(.22,1,.36,1), border-color .25s, box-shadow .25s;
}
.egc-wrap:hover .egc {
    transform:translateY(-7px);
    border-color:rgba(236,72,153,.5);
    box-shadow:0 22px 52px rgba(168,85,247,.22), 0 0 0 1px rgba(236,72,153,.12);
}

/* Cover — background-image inline style (same as booster-cards.php) */
.egc__cover {
    position:relative;
    width:100%; height:160px; flex-shrink:0;
    border-radius:20px 20px 0 0;
    overflow:hidden;
    background-size:cover;
    background-position:center top;
    background-color:#1a0530;
    transition:background-position .45s ease;
}
.egc-wrap:hover .egc__cover { background-position:center 20%; }

/* gradient overlay */
.egc__cover::after {
    content:''; position:absolute; inset:0; z-index:1; pointer-events:none;
    background:linear-gradient(to bottom, rgba(15,11,30,0) 15%, rgba(15,11,30,.55) 100%);
}

/* Status + game icons sit above overlay */
.egc__status,
.egc__games { z-index:5; }

/* Status pill */
.egc__status {
    position:absolute; top:12px; left:12px;
    display:inline-flex; align-items:center; gap:7px;
    padding:6px 15px; border-radius:999px;
    font-size:14px; font-weight:700; letter-spacing:.03em;
    backdrop-filter:blur(10px); -webkit-backdrop-filter:blur(10px);
    white-space:nowrap;
}
.egc__status.is-online  { background:rgba(34,197,94,.15); border:1px solid rgba(34,197,94,.35); color:#4ade80; }
.egc__status.is-offline { background:rgba(0,0,0,.50); border:1px solid rgba(255,255,255,.1); color:rgba(255,255,255,.4); }
.egc__status.is-offline { display:none !important; }

.egc__sdot { width:7px; height:7px; border-radius:50%; flex-shrink:0; }
.is-online  .egc__sdot { background:#22c55e; box-shadow:0 0 0 2px rgba(34,197,94,.25); animation:egcPulse 2s ease-in-out infinite; }
.is-offline .egc__sdot { background:rgba(255,255,255,.25); }
@keyframes egcPulse {
    0%,100% { box-shadow:0 0 0 2px rgba(34,197,94,.25); }
    50%      { box-shadow:0 0 0 5px rgba(34,197,94,.06); }
}

/* Game icons top-right — overlapping circles, same layout as the booster cards */
.egc__games { position:absolute; top:12px; right:12px; display:flex; align-items:center; z-index:12; }
.egc__gicon {
    position:relative;
    width:34px; height:34px; border-radius:50%;
    background:rgba(0,0,0,.55); border:1px solid rgba(255,255,255,.12);
    display:inline-flex; align-items:center; justify-content:center;
    backdrop-filter:blur(8px); -webkit-backdrop-filter:blur(8px);
    overflow:hidden; transition:border-color .15s, transform .15s, background .15s;
}
.egc__gicon + .egc__gicon,
.egc__gmore { margin-left:-8px; }
.egc__gicon:first-child { z-index:8; border-color:rgba(236,72,153,.7); box-shadow:0 0 0 2px rgba(15,11,30,.85); }
.egc__gicon:nth-child(2){z-index:7}
.egc__gicon:nth-child(3){z-index:6}
.egc__gicon:nth-child(4){z-index:5}
.egc-wrap:hover .egc__gicon { border-color:rgba(236,72,153,.4); }
.egc__gicon:hover { border-color:rgba(236,72,153,.85); background:rgba(236,72,153,.2); transform:translateY(-2px); z-index:10; }
.egc__gicon img { width:22px; height:22px; object-fit:contain; display:block; }
.egc__gmore {
    position:relative; z-index:4; width:34px; height:34px; border-radius:50%;
    display:inline-flex; align-items:center; justify-content:center;
    background:#1e0a2e; border:1px solid rgba(236,72,153,.35); color:#f9a8d4;
    box-shadow:0 0 0 2px rgba(15,11,30,.85);
    font-size:11px; font-weight:900; cursor:default; outline:none;
    transition:transform .15s, border-color .15s;
}
.egc__gmore:hover, .egc__gmore:focus { transform:translateY(-2px); border-color:rgba(236,72,153,.7); z-index:11; }

/* Global games tooltip — fixed, so no card overflow can clip it */
#egc-global-tooltip {
    position:fixed; z-index:99999; display:none;
    background:#1e0a2e; border:1px solid rgba(236,72,153,.5); border-radius:14px;
    padding:12px 15px; min-width:180px; max-width:320px;
    box-shadow:0 18px 44px rgba(0,0,0,.7);
    opacity:0; transform:translateY(6px); transition:opacity .15s ease, transform .15s ease;
    pointer-events:none;
}
#egc-global-tooltip.is-visible { opacity:1; transform:translateY(0); }
#egc-global-tooltip .egc-tt-title {
    display:block; font-size:11px; font-weight:800; text-transform:uppercase;
    letter-spacing:.09em; color:rgba(255,255,255,.38); margin-bottom:9px;
}
#egc-global-tooltip .egc-tt-list {
    display:grid; grid-template-columns:repeat(2, minmax(0,1fr)); gap:8px 18px;
    max-height:240px; overflow-y:auto;
}
#egc-global-tooltip .egc-tt-item {
    display:flex; align-items:center; gap:9px;
    font-size:13px; font-weight:700; color:rgba(255,255,255,.85);
}
#egc-global-tooltip .egc-tt-item img { width:24px; height:24px; border-radius:6px; object-fit:contain; flex-shrink:0; }

/* Avatar */
.egc__avatar {
    position:absolute;
    top:116px; left:18px; z-index:10;
    width:88px; height:88px; border-radius:50%;
    border:4px solid #0f0b1e;
    overflow:visible; background:#2a1040;
    box-shadow:0 6px 20px rgba(0,0,0,.65), 0 0 0 1px rgba(168,85,247,.3);
    transition:box-shadow .25s;
}
.egc-wrap:hover .egc__avatar { box-shadow:0 8px 28px rgba(0,0,0,.65), 0 0 0 2px rgba(236,72,153,.55); }
.egc__avatar img { width:100%; height:100%; object-fit:cover; object-position:center top; display:block; border-radius:50%; }
.egc__avatar-ph { width:100%; height:100%; display:flex; align-items:center; justify-content:center; font-size:38px; border-radius:50%; overflow:hidden; }

/* Avatar dot */
.egc__adot {
    position:absolute; bottom:4px; right:4px;
    width:16px; height:16px; border-radius:50%;
    border:3px solid #0f0b1e; z-index:11;
}
.egc__adot.is-online  { background:#22c55e; }
.egc__adot.is-offline { background:#4b5563; }

/* Body */
.egc__body { padding:54px 20px 20px; display:flex; flex-direction:column; gap:10px; flex:1; border-radius:0 0 20px 20px; overflow:hidden; }

/* Name line */
.egc__nameline { display:flex; align-items:center; gap:8px; flex-wrap:wrap; min-width:0; }
.egc__name { font-size:22px; font-weight:800; color:#fce7f3; letter-spacing:-.3px; line-height:1.2; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; min-width:0; }
.egc__verify { color:#ec4899; font-size:18px; filter:drop-shadow(0 0 5px rgba(236,72,153,.5)); }
.egc__ratingbadge {
    display:inline-flex; align-items:center; gap:5px;
    background:linear-gradient(135deg,rgba(236,72,153,.20),rgba(168,85,247,.12));
    border:1px solid rgba(236,72,153,.35);
    border-radius:999px; padding:4px 12px;
    font-size:15px; font-weight:800; color:#f9a8d4;
    box-shadow:0 4px 14px rgba(236,72,153,.14);
}
.egc__ratingbadge img { width:15px; height:15px; flex-shrink:0; filter:hue-rotate(-40deg) saturate(1.6); }
.egc__rcnt { color:rgba(249,168,212,.62); font-weight:600; }

/* Inline status */
.egc__statusinline {
    display:inline-flex; align-items:center; gap:7px;
    padding:5px 14px; border-radius:999px;
    font-size:14px; font-weight:700; align-self:flex-start;
}
.egc__statusinline.is-online  { background:rgba(34,197,94,.12); border:1px solid rgba(34,197,94,.3); color:#4ade80; }
.egc__statusinline.is-offline { background:rgba(0,0,0,.35); border:1px solid rgba(255,255,255,.08); color:rgba(255,255,255,.35); }
.egc__statusinline.is-offline { display:none !important; }

/* Bio */
.egc__bio { margin:0; font-size:15px; color:rgba(255,255,255,.55); line-height:1.6; display:-webkit-box; -webkit-line-clamp:2; -webkit-box-orient:vertical; overflow:hidden; word-break:break-word; }

/* Language flags */
.egc__langs { display:flex; align-items:center; flex-wrap:wrap; gap:6px; margin-top:auto; }
.egc__flag {
    display:inline-flex; align-items:center; justify-content:center;
    width:34px; height:34px; border-radius:9px; flex-shrink:0;
    background:rgba(10,10,24,.45);
    border:1px solid rgba(255,255,255,.13);
    box-shadow:0 2px 8px rgba(0,0,0,.45);
    overflow:hidden;
}
.egc__flag img { width:24px; height:17px; object-fit:cover; display:block; border-radius:3px; }

.egc__morelang {
    position:relative; display:inline-flex; align-items:center;
    background:rgba(255,255,255,.07); border:1px solid rgba(255,255,255,.12);
    border-radius:8px; padding:4px 10px; font-size:13px; font-weight:700;
    color:rgba(255,255,255,.55); cursor:default;
}
.egc__langtt {
    display:none; position:absolute; bottom:calc(100% + 8px); left:50%; transform:translateX(-50%);
    background:#1e0a2e; border:1px solid rgba(236,72,153,.3); border-radius:12px;
    padding:10px 13px; z-index:99; min-width:155px;
    box-shadow:0 12px 30px rgba(0,0,0,.65); white-space:nowrap;
}
.egc__morelang:hover .egc__langtt,
.egc__morelang:focus .egc__langtt { display:block; }
.egc__tttitle { display:block; font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:.08em; color:rgba(255,255,255,.35); margin-bottom:7px; }
.egc__ttlist  { display:flex; flex-direction:column; gap:5px; }
.egc__ttitem  { display:flex; align-items:center; gap:8px; font-size:13px; font-weight:600; color:rgba(255,255,255,.75); }
.egc__ttitem img { width:24px; height:17px; border-radius:6px; object-fit:cover; }
.egc__nolang  { font-size:14px; color:rgba(255,255,255,.3); }

/* Footer */
.egc__footer { display:flex; align-items:center; justify-content:space-between; padding-top:14px; border-top:1px solid rgba(255,255,255,.06); margin-top:8px; }
.egc__from  { font-size:12px; color:rgba(255,255,255,.28); text-transform:uppercase; letter-spacing:.1em; margin-bottom:4px; }
.egc__price { font-size:26px; font-weight:900; color:#fce7f3; letter-spacing:-.5px; line-height:1.1; }
.egc__book {
    display:inline-flex; align-items:center; gap:8px;
    padding:12px 24px; border-radius:999px;
    background:linear-gradient(135deg, #a855f7 0%, #ec4899 100%);
    color:#fff; font-size:15px; font-weight:700; letter-spacing:.02em;
    box-shadow:0 6px 20px rgba(168,85,247,.3);
    transition:transform .18s ease, box-shadow .18s ease, gap .18s ease;
    white-space:nowrap; border:none; cursor:pointer; flex-shrink:0;
}
.egc__book svg { transition:transform .18s ease; flex-shrink:0; }
.egc-wrap:hover .egc__book { transform:translateY(-2px); box-shadow:0 12px 30px rgba(168,85,247,.45); gap:11px; }
.egc-wrap:hover .egc__book svg { transform:translateX(3px); }

/* Empty */
.egc-empty-msg { grid-column:1/-1; text-align:center; padding:60px 20px; color:rgba(255,255,255,.35); font-size:17px; }
</style>

<?php if (empty($egirls)): ?>
    <p class="egc-empty-msg"><?= t('No E-Girls found with the selected filters.') ?></p>
<?php else:
    foreach ($egirls as $eg):
        $egirlId    = (int)($eg['booster_id'] ?? $eg['egirl_id'] ?? $eg['id'] ?? 0);
        $isOnline   = !empty($__onlineEgirlMap[$egirlId]);
        $statusText  = $isOnline ? t('Online') : t('Offline');
        $statusClass = $isOnline ? 'is-online' : 'is-offline';
        $bioRaw      = $__decodeCardText($eg['bio'] ?? $eg['description'] ?? '');
        $bio         = trim(strip_tags($bioRaw));
?>
<a href="<?= BASE_URL . '/egirls/' . $egirlId ?>" class="egc-wrap">
    <div class="egc"
         data-name="<?= htmlspecialchars(strtolower($eg['username'] ?? ''), ENT_QUOTES) ?>"
         data-games="<?= htmlspecialchars(strtolower($eg['games'] ?? ''), ENT_QUOTES) ?>"
         data-langs="<?= htmlspecialchars(strtolower($eg['languages'] ?? ''), ENT_QUOTES) ?>"
         data-voice="<?= !empty($eg['voice_chat']) ? '1' : '0' ?>"
         data-online="<?= $isOnline ? '1' : '0' ?>">

        <!-- ════ COVER — background-image inline style, exactly like booster-cards.php ════ -->
        <?php $__cover = !empty($eg['cover']) ? $eg['cover'] : ASSET_URL . '/core/main/img/banners/leona.jpeg'; ?>
        <div class="egc__cover"
             style="background-image: url('<?= htmlspecialchars($__cover, ENT_QUOTES) ?>');">


            <!-- Online / Offline pill -->
            <span class="egc__status <?= $statusClass ?>">
                <span class="egc__sdot"></span>
                <?= $statusText ?>
            </span>

            <!-- Game icons top-right — same ASSET_URL as view.php line 773 -->
            <?php
            $__egCardGames = [];
            foreach (explode('|', (string)($eg['games'] ?? '')) as $__g) {
                $__g = strtolower(trim($__g));
                if ($__g === '') continue;
                $__gi = $__egGameIcon($__g);
                if ($__gi === '') continue;
                $__egCardGames[$__g] = ['icon' => $__gi, 'label' => $__egGameLabel($__g)];
            }
            $__egShownGames  = array_slice($__egCardGames, 0, 4, true);
            $__egHiddenGames = array_slice($__egCardGames, 4, null, true);
            ?>
        </div>

        <?php if ($__egCardGames): ?>
            <!-- Rendered outside the cover: the cover clips overflow, which would
                 cut off the "+N" tooltip. -->
            <div class="egc__games">
                <?php foreach ($__egShownGames as $__g => $__gi): ?>
                    <span class="egc__gicon" title="<?= htmlspecialchars($__gi['label'], ENT_QUOTES) ?>">
                        <img src="<?= htmlspecialchars($__gi['icon'], ENT_QUOTES) ?>"
                             alt="<?= htmlspecialchars($__gi['label'], ENT_QUOTES) ?>"
                             onerror="this.closest('.egc__gicon').style.display='none'">
                    </span>
                <?php endforeach; ?>
                <?php if ($__egHiddenGames):
                    // Same mechanism as the booster cards: a single fixed tooltip
                    // element, so no parent's overflow can clip it.
                    $__egHiddenItems = array_values(array_map(static fn($__gi) => [
                        'label' => $__gi['label'],
                        'img'   => $__gi['icon'],
                    ], $__egHiddenGames));
                ?>
                    <span class="egc__gmore" tabindex="0"
                          data-games="<?= htmlspecialchars(json_encode($__egHiddenItems, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE), ENT_QUOTES, 'UTF-8') ?>"
                          aria-label="<?= count($__egHiddenGames) ?> more games">
                        +<?= count($__egHiddenGames) ?>
                    </span>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <!-- ════ AVATAR ════ -->
        <div class="egc__avatar">
            <?php if (!empty($eg['icon'])): ?>
                <img src="<?= $eg['icon'] ?>" alt="...">
            <?php else: ?>
                <span class="egc__avatar-ph">👧</span>
            <?php endif; ?>
            <span class="egc__adot <?= $statusClass ?>"></span>
        </div>

        <!-- ════ BODY ════ -->
        <div class="egc__body">

            <!-- Name + verified + rating -->
            <div class="egc__nameline">
                <span class="egc__name"><?= htmlspecialchars($eg['username'] ?? '', ENT_QUOTES) ?></span>
                <?php if (!empty($eg['verified'])): ?>
                    <i class="fa-solid fa-badge-check egc__verify"></i>
                <?php endif; ?>
                <?php if (!empty($eg['review_count']) && (int)$eg['review_count'] > 0): ?>
                    <span class="egc__ratingbadge">
                        <img src="<?= ASSET_URL ?>/website/images/boosters/star.svg" alt="★">
                        <?= number_format((float)($eg['review_avg'] ?? 0), 1) ?>
                        <span class="egc__rcnt">(<?= (int)$eg['review_count'] ?>)</span>
                    </span>
                <?php endif; ?>
            </div>

            <!-- Inline status badge -->
            <span class="egc__statusinline <?= $statusClass ?>">
                <span class="egc__sdot"></span>
                <?= $statusText ?>
            </span>

            <!-- Bio -->
            <?php if ($bio): ?>
                <p class="egc__bio"><?= htmlspecialchars($bio, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></p>
            <?php endif; ?>

            <!-- Language flags — same as view.php lines 748-764 -->
            <div class="egc__langs">
                <?php
                if (!empty($eg['languages'])) {
                    $__ll  = explode('|', $eg['languages']);
                    $__max = 3; $__n = 0; $__tot = count($__ll);

                    foreach ($__ll as $__lc) {
                        if ($__n >= $__max) break;
                        $__lc = trim($__lc);
                        if (!$__lc) continue;
                        $__lf = $__langImgMap[$__lc] ?? null;
                        if (!$__lf) continue;
                        $__ls = htmlspecialchars($__lc, ENT_QUOTES, 'UTF-8');
                        echo '<span class="egc__flag" title="' . strtoupper($__ls) . '">'
                           . '<img src="' . $__langBase . $__lf . '" alt="' . strtoupper($__ls) . '" onerror="this.closest(\'.egc__flag\').style.display=\'none\'">'
                           . '</span>';
                        $__n++;
                    }

                    if ($__tot > $__max) {
                        $__rem = $__tot - $__max;
                        $__rl  = array_slice($__ll, $__max);
                        echo '<span class="egc__morelang" tabindex="0">+' . (int)$__rem;
                        echo '<span class="egc__langtt"><span class="egc__tttitle">' . t('More languages') . '</span><span class="egc__ttlist">';
                        foreach ($__rl as $__r2) {
                            $__r2 = trim($__r2);
                            if (!$__r2 || !isset($__langImgMap[$__r2])) continue;
                            $__rs = htmlspecialchars($__r2, ENT_QUOTES, 'UTF-8');
                            echo '<span class="egc__ttitem">'
                               . '<img src="' . $__langBase . $__langImgMap[$__r2] . '" alt="' . strtoupper($__rs) . '">'
                               . '<span>' . strtoupper($__rs) . '</span>'
                               . '</span>';
                        }
                        echo '</span></span></span>';
                    }
                } else {
                    echo '<span class="egc__nolang">N/A</span>';
                }
                ?>
            </div>

            <!-- Footer: price + book -->
            <?php
                $__dispCur  = $eg['display_currency'] ?? ($_SESSION['currency'] ?? 'EUR');
                $__dispCents = (int)($eg['min_price_display'] ?? 0);
                // Fallback for cards rendered without routes.php conversion (e.g. direct includes)
                if ($__dispCents <= 0 && !empty($eg['min_price'])) {
                    $__dispCents = (int)$eg['min_price'];
                    if ($__dispCur === 'USD' && function_exists('get_exchange_rate')) {
                        $__rate = (float)get_exchange_rate();
                        if ($__rate > 0) $__dispCents = (int)round($__dispCents * $__rate);
                    }
                }
                $__dispSym = $__dispCur === 'USD' ? '$' : '€';
                $__dispSuf = $__dispCur === 'USD' ? ' USD' : '';
            ?>
            <div class="egc__footer">
                <div>
                    <div class="egc__from"><?= t('Starting from') ?></div>
                    <div class="egc__price">
                        <?= $__dispCents > 0 ? $__dispSym . number_format($__dispCents / 100, 2) . $__dispSuf : '—' ?>
                    </div>
                </div>
                <span class="egc__book">
                    <?= t('Book now') ?>
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                        <path d="M5 12h14M13 6l6 6-6 6" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </span>
            </div>

        </div><!-- /.egc__body -->
    </div><!-- /.egc -->
</a>
<?php endforeach; endif; ?>



<div id="egc-global-tooltip">
    <span class="egc-tt-title"></span>
    <div class="egc-tt-list"></div>
</div>

<script>
(function () {
    if (window.__egcGamesTooltipInit) { window.__egcGamesTooltipInit(); return; }

    var tt      = document.getElementById('egc-global-tooltip');
    var ttTitle = tt ? tt.querySelector('.egc-tt-title') : null;
    var ttList  = tt ? tt.querySelector('.egc-tt-list')  : null;
    var hideTimer;

    function show(trigger, title, items) {
        if (!tt || !items.length) return;
        clearTimeout(hideTimer);
        ttTitle.textContent = title;
        ttList.innerHTML = '';
        items.forEach(function (item) {
            var row = document.createElement('div');
            row.className = 'egc-tt-item';
            if (item.img) {
                var img = document.createElement('img');
                img.src = item.img; img.alt = '';
                img.onerror = function () { this.style.display = 'none'; };
                row.appendChild(img);
            }
            var span = document.createElement('span');
            span.textContent = item.label || '';
            row.appendChild(span);
            ttList.appendChild(row);
        });

        tt.style.display = 'block';
        tt.classList.remove('is-visible');

        var rect = trigger.getBoundingClientRect();
        var w = tt.offsetWidth, h = tt.offsetHeight;
        var left = rect.left + rect.width / 2 - w / 2;
        var top  = rect.top - h - 12;
        // Not enough room above (top row of the grid) → flip below the trigger.
        if (top < 8) top = rect.bottom + 12;
        left = Math.max(8, Math.min(left, window.innerWidth - w - 8));
        top  = Math.max(8, Math.min(top, window.innerHeight - h - 8));
        tt.style.left = left + 'px';
        tt.style.top  = top + 'px';
        requestAnimationFrame(function () { tt.classList.add('is-visible'); });
    }

    function hide() {
        if (!tt) return;
        tt.classList.remove('is-visible');
        hideTimer = setTimeout(function () { tt.style.display = 'none'; }, 160);
    }

    function init() {
        document.querySelectorAll('.egc__gmore').forEach(function (el) {
            if (el.dataset.ttInited) return;
            el.dataset.ttInited = '1';
            function open() {
                var games = [];
                try { games = JSON.parse(el.dataset.games || '[]'); } catch (e) {}
                show(el, 'More games', games);
            }
            el.addEventListener('mouseenter', open);
            el.addEventListener('focus', open);
            el.addEventListener('mouseleave', hide);
            el.addEventListener('blur', hide);
            // The card is a link — the "+N" chip must not navigate.
            el.addEventListener('click', function (e) { e.preventDefault(); e.stopPropagation(); });
        });
    }

    window.__egcGamesTooltipInit = init;
    init();
    window.addEventListener('scroll', hide, true);

    // Filters re-render the grid, so newly inserted cards need wiring too.
    var grid = document.querySelector('.egc-grid, #egirls, [data-egirls-grid]');
    if (grid) new MutationObserver(init).observe(grid, { childList: true, subtree: true });
})();
</script>
