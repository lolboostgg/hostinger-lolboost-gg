<?= $this->layout('admin/layouts/main', ['meta' => $meta]) ?>
<?php
$isNew     = ($form === null);
$formId    = $isNew ? null : (int)$form['id'];
$gameId    = (int)$game['id'];
$gameSlug  = $game['slug'] ?? '';
$gameName  = $game['name'] ?? '';
$saveUrl   = $isNew
    ? '/admin-area/games/' . $gameId . '/boost-forms/create'
    : '/admin-area/games/' . $gameId . '/boost-form-save';

$fName     = $form['name']        ?? '';
$fNameLong = $form['name_long']   ?? '';
$fSlug     = $form['slug']        ?? '';
$fType     = $form['type']        ?? 'rank';
$fDesc     = $form['description'] ?? '';
$fStatus   = (int)($form['status'] ?? 1);
$fUuid     = $form['uuid']        ?? '';
$fJson     = $form['json']        ?? [];

$_scMap = ['league-of-legends'=>'lol','valorant'=>'val','teamfight-tactics'=>'tft',
           'lol-classic'=>'lol_classic','league-of-legends-classic'=>'lol_classic',
           'apex-legends'=>'apex','overwatch-2'=>'ow2','rocket-league'=>'rl',
           'marvel-rivals'=>'rivals','call-of-duty'=>'cod','fortnite'=>'fortnite',
           'counter-strike-2'=>'cs2wm'];
$gc = $_scMap[$gameSlug] ?? ($game['short_code'] ?? $gameSlug);

$presets = [
    'lol'     => ['ranks'=>[1=>'Iron',2=>'Bronze',3=>'Silver',4=>'Gold',5=>'Platinum',6=>'Emerald',7=>'Diamond',8=>'Master',9=>'Grandmaster',10=>'Challenger'],'divisions'=>4,'div_order'=>'desc','points_label'=>'LP','server'=>true,'platform'=>false,'current_points'=>true,'lp_gain'=>true,'queue_type'=>true,'solo_duo'=>true,'server_opts'=>['euw'=>'EU-West','na'=>'North America','eune'=>'EU-Nordic & East','me'=>'Middle East','br'=>'Brazil','oce'=>'Oceania','ru'=>'Russia','tr'=>'Turkey'],'platform_opts'=>['pc'=>'PC'],'queue_opts'=>['solo_/_duo'=>'Ranked Solo/Duo','flexq'=>'Ranked Flex Queue'],'lp_gain_opts'=>['30+'=>'30+ LP / Win','25-29'=>'25-29 LP / Win','20-24'=>'20-24 LP / Win','10-19'=>'10-19 LP / Win'],'points_opts'=>['0-20'=>'0-20 LP','21-40'=>'21-40 LP','41-60'=>'41-60 LP','61-80'=>'61-80 LP','81-100'=>'81-100 LP'],'regions'=>['eu','na']],
    'val'     => ['ranks'=>[1=>'Iron',2=>'Bronze',3=>'Silver',4=>'Gold',5=>'Platinum',6=>'Diamond',7=>'Ascendant',8=>'Immortal',9=>'Radiant'],'divisions'=>3,'div_order'=>'asc','points_label'=>'RR','server'=>true,'platform'=>false,'current_points'=>true,'lp_gain'=>false,'queue_type'=>false,'solo_duo'=>true,'server_opts'=>['eu'=>'Europe','na'=>'North America','ap'=>'Asia Pacific','latam'=>'Latin America','br'=>'Brazil'],'platform_opts'=>['pc'=>'PC'],'queue_opts'=>[],'lp_gain_opts'=>[],'points_opts'=>['0-20'=>'0-20 RR','21-40'=>'21-40 RR','41-60'=>'41-60 RR','61-80'=>'61-80 RR','81-100'=>'81-100 RR'],'regions'=>['eu','na']],
    'tft'     => ['ranks'=>[1=>'Iron',2=>'Bronze',3=>'Silver',4=>'Gold',5=>'Platinum',6=>'Emerald',7=>'Diamond',8=>'Master',9=>'Grandmaster',10=>'Challenger'],'divisions'=>4,'div_order'=>'desc','points_label'=>'LP','server'=>true,'platform'=>false,'current_points'=>true,'lp_gain'=>false,'queue_type'=>false,'solo_duo'=>false,'server_opts'=>['euw'=>'EU-West','na'=>'North America','eune'=>'EU-Nordic & East'],'platform_opts'=>['pc'=>'PC'],'queue_opts'=>[],'lp_gain_opts'=>[],'points_opts'=>['0-20'=>'0-20 LP','21-40'=>'21-40 LP','41-60'=>'41-60 LP','61-80'=>'61-80 LP','81-100'=>'81-100 LP'],'regions'=>['eu','na']],
    'lol_classic' => [
        'ranks'=>[0=>'Unranked',1=>'Salt',2=>'Wood',3=>'Silver',4=>'Gold',5=>'Platinum',6=>'Diamond',7=>'Legend'],
        'rank_divs'=>[0=>0,1=>4,2=>4,3=>4,4=>4,5=>4,6=>4,7=>0],
        'divisions'=>4,'div_order'=>'desc','points_label'=>'LP','server'=>true,'platform'=>false,
        'current_points'=>true,'lp_gain'=>false,'queue_type'=>true,'solo_duo'=>true,
        'server_opts'=>['euw'=>'EU-West','na'=>'North America','eune'=>'EU-Nordic & East'],
        'platform_opts'=>['pc'=>'PC'],'queue_opts'=>['solo_/_duo'=>'Ranked Solo/Duo','flexq'=>'Ranked Flex Queue'],
        'lp_gain_opts'=>[],'points_opts'=>['0-20'=>'0-20 LP','21-40'=>'21-40 LP','41-60'=>'41-60 LP','61-80'=>'61-80 LP','81-100'=>'81-100 LP'],
        'regions'=>['eu','na'],
        'icons'=>[
            0=>'/public/assets/website/images/lol-classic/ranks/unranked.webp',
            1=>'/public/assets/website/images/lol-classic/ranks/salt.webp',
            2=>'/public/assets/website/images/lol-classic/ranks/wood.webp',
            3=>'/public/assets/website/images/lol-classic/ranks/silver.webp',
            4=>'/public/assets/website/images/lol-classic/ranks/gold.webp',
            5=>'/public/assets/website/images/lol-classic/ranks/platinum.webp',
            6=>'/public/assets/website/images/lol-classic/ranks/diamond.webp',
            7=>'/public/assets/website/images/lol-classic/ranks/legend.webp',
        ],
    ],
    'apex'    => ['ranks'=>[1=>'Rookie',2=>'Bronze',3=>'Silver',4=>'Gold',5=>'Platinum',6=>'Diamond',7=>'Master'],'divisions'=>4,'div_order'=>'desc','points_label'=>'RP','server'=>true,'platform'=>true,'current_points'=>true,'lp_gain'=>true,'queue_type'=>false,'solo_duo'=>true,'server_opts'=>['eu'=>'Europe','na'=>'North America','as'=>'Asia'],'platform_opts'=>['pc'=>'PC','playstation'=>'PlayStation','xbox'=>'Xbox','switch'=>'Switch'],'queue_opts'=>[],'lp_gain_opts'=>['30+'=>'30+ RP / Win','25-29'=>'25-29 RP / Win','20-24'=>'20-24 RP / Win','10-19'=>'10-19 RP / Win'],'points_opts'=>['0-20'=>'0-20 RP','21-40'=>'21-40 RP','41-60'=>'41-60 RP','61-80'=>'61-80 RP','81-100'=>'81-100 RP'],'regions'=>['eu','na']],
    'ow2'     => ['ranks'=>[1=>'Bronze',2=>'Silver',3=>'Gold',4=>'Platinum',5=>'Diamond',6=>'Master',7=>'Grandmaster',8=>'Champion'],'divisions'=>3,'div_order'=>'desc','points_label'=>'SR','server'=>true,'platform'=>true,'current_points'=>true,'lp_gain'=>false,'queue_type'=>false,'solo_duo'=>true,'server_opts'=>['eu'=>'Europe','na'=>'North America','as'=>'Asia'],'platform_opts'=>['pc'=>'PC','playstation'=>'PlayStation','xbox'=>'Xbox'],'queue_opts'=>[],'lp_gain_opts'=>[],'points_opts'=>['0-333'=>'0-333 SR','334-666'=>'334-666 SR','667-999'=>'667-999 SR'],'regions'=>['eu','na']],
    'rl'      => ['ranks'=>[1=>'Bronze',2=>'Silver',3=>'Gold',4=>'Platinum',5=>'Diamond',6=>'Champion',7=>'Grand Champion',8=>'Supersonic Legend'],'divisions'=>3,'div_order'=>'asc','points_label'=>'MMR','server'=>true,'platform'=>true,'current_points'=>false,'lp_gain'=>false,'queue_type'=>true,'solo_duo'=>true,'server_opts'=>['eu'=>'Europe','na'=>'North America','as'=>'Asia-Pacific'],'platform_opts'=>['pc'=>'PC','playstation'=>'PlayStation','xbox'=>'Xbox','switch'=>'Switch'],'queue_opts'=>['3v3'=>'3v3 (Standard)','2v2'=>'2v2','1v1'=>'1v1'],'lp_gain_opts'=>[],'points_opts'=>[],'regions'=>['eu','na']],
    'rivals'  => ['ranks'=>[1=>'Bronze',2=>'Silver',3=>'Gold',4=>'Platinum',5=>'Diamond',6=>'Celestial',7=>'Eternity',8=>'Grand Master'],'divisions'=>4,'div_order'=>'desc','points_label'=>'RP','server'=>true,'platform'=>true,'current_points'=>true,'lp_gain'=>true,'queue_type'=>true,'solo_duo'=>true,'server_opts'=>['eu'=>'Europe','na'=>'North America','as'=>'Asia'],'platform_opts'=>['pc'=>'PC','playstation'=>'PlayStation','xbox'=>'Xbox'],'queue_opts'=>['standard'=>'3v3 (Standard)','competitive'=>'3v3 (Competitive)'],'lp_gain_opts'=>['30+'=>'30+ RP / Win','25-29'=>'25-29 RP / Win','20-24'=>'20-24 RP / Win','10-19'=>'10-19 RP / Win'],'points_opts'=>['0-20'=>'0-20 RP','21-40'=>'21-40 RP','41-60'=>'41-60 RP','61-80'=>'61-80 RP','81-100'=>'81-100 RP'],'regions'=>['eu','na']],
    'cod'     => ['ranks'=>[1=>'Bronze',2=>'Silver',3=>'Gold',4=>'Platinum',5=>'Diamond',6=>'Crimson',7=>'Iridescent',8=>'Top 250'],'divisions'=>3,'div_order'=>'desc','points_label'=>'SR','server'=>true,'platform'=>true,'current_points'=>true,'lp_gain'=>false,'queue_type'=>false,'solo_duo'=>true,'server_opts'=>['eu'=>'Europe','na'=>'North America'],'platform_opts'=>['pc'=>'PC','playstation'=>'PlayStation','xbox'=>'Xbox'],'queue_opts'=>[],'lp_gain_opts'=>[],'points_opts'=>['0-333'=>'0-333 SR','334-666'=>'334-666 SR','667-999'=>'667-999 SR'],'regions'=>['eu','na']],
    'fortnite'=> ['ranks'=>[1=>'Bronze',2=>'Silver',3=>'Gold',4=>'Platinum',5=>'Diamond',6=>'Elite',7=>'Champion',8=>'Unreal'],'divisions'=>3,'div_order'=>'asc','points_label'=>'XP','server'=>true,'platform'=>true,'current_points'=>false,'lp_gain'=>false,'queue_type'=>false,'solo_duo'=>true,'server_opts'=>['eu'=>'Europe','na'=>'North America','as'=>'Asia'],'platform_opts'=>['pc'=>'PC','playstation'=>'PlayStation','xbox'=>'Xbox','switch'=>'Switch'],'queue_opts'=>[],'lp_gain_opts'=>[],'points_opts'=>[],'regions'=>['eu','na']],
    // Wingman Boost only — the Counter-Strike 2 Premier/Rank Boost form's ranks & options
    // are hardcoded in lb_generic_game_rank_config() / rank-dynamic.php's $_gameFieldDefaults
    // and are authoritative regardless of what's saved here. This preset just pre-fills a
    // brand-new "wingman-boosting" form so the admin doesn't have to type all 17 ranks by hand.
    'cs2wm'   => ['ranks'=>[1=>'Silver I',2=>'Silver II',3=>'Silver III',4=>'Silver IV',5=>'Silver Elite',6=>'Silver Elite Master',7=>'Gold Nova I',8=>'Gold Nova II',9=>'Gold Nova III',10=>'Gold Nova Master',11=>'Master Guardian I',12=>'Master Guardian II',13=>'Master Guardian Elite',14=>'Distinguished Master Guardian',15=>'Legendary Eagle',16=>'Legendary Eagle Master',17=>'Supreme Master First Class'],'divisions'=>0,'div_order'=>'asc','points_label'=>'Rank','server'=>false,'platform'=>false,'current_points'=>false,'lp_gain'=>false,'queue_type'=>true,'solo_duo'=>true,'server_opts'=>[],'platform_opts'=>['pc'=>'PC'],'queue_opts'=>['Overpass'=>'Overpass','Vertigo'=>'Vertigo','Nuke'=>'Nuke','Inferno'=>'Inferno'],'lp_gain_opts'=>[],'points_opts'=>[],'regions'=>['eu','na'],
        'icons'=>[1=>'silver-1',2=>'silver-2',3=>'silver-3',4=>'silver-4',5=>'silver-elite',6=>'silver-elite-master',7=>'gold-nova-1',8=>'gold-nova-2',9=>'gold-nova-3',10=>'gold-nova-master',11=>'master-guardian-1',12=>'master-guardian-2',13=>'master-guardian-elite',14=>'distinguished-master-guardian',15=>'legendary-eagle',16=>'legendary-eagle-master',17=>'supreme-master-first-class']],
];
// Pre-fill rank icon URLs from the preset's file-name map (only used for brand-new forms —
// once a form has its own saved rank_icons, that always wins).
$_presetIconMap = $presets[$gc]['icons'] ?? null;
$_presetIcons = [];
if ($_presetIconMap !== null) {
    foreach ($_presetIconMap as $_pTier => $_pFile) {
        $_presetIcons[$_pTier] = strpos((string)$_pFile, '/') === 0
            ? (string)$_pFile
            : ASSET_URL . '/website/images/boosting/ranks/' . $gameSlug . '/' . $_pFile . '.webp';
    }
}
$preset = $presets[$gc] ?? ['ranks'=>[1=>'Tier 1',2=>'Tier 2',3=>'Tier 3',4=>'Tier 4',5=>'Tier 5',6=>'Tier 6',7=>'Tier 7',8=>'Tier 8'],'divisions'=>4,'div_order'=>'desc','points_label'=>'LP','server'=>true,'platform'=>false,'current_points'=>true,'lp_gain'=>true,'queue_type'=>false,'solo_duo'=>true,'server_opts'=>['eu'=>'Europe','na'=>'North America'],'platform_opts'=>['pc'=>'PC'],'queue_opts'=>[],'lp_gain_opts'=>['30+'=>'30+ LP / Win','25-29'=>'25-29 LP / Win','20-24'=>'20-24 LP / Win','10-19'=>'10-19 LP / Win'],'points_opts'=>['0-20'=>'0-20 LP','21-40'=>'21-40 LP','41-60'=>'41-60 LP','61-80'=>'61-80 LP','81-100'=>'81-100 LP'],'regions'=>['eu','na']];

$savedCfg      = $fJson['form_config'] ?? [];
$savedRanks    = !empty($savedCfg['ranks']) ? $savedCfg['ranks'] : $preset['ranks'];
$savedDivs     = isset($savedCfg['divisions']) ? (int)$savedCfg['divisions'] : $preset['divisions'];
$savedRankDivs = !empty($savedCfg['rank_divs']) && is_array($savedCfg['rank_divs'])
    ? $savedCfg['rank_divs']
    : ($preset['rank_divs'] ?? []);
$isLolClassicEditor = $gc === 'lol_classic';
if ($isLolClassicEditor) {
    // The Classic ladder is fixed by the live checkout/calculators. Older JSON
    // may still claim that Unranked or Legend has four divisions.
    $savedRanks = $preset['ranks'];
    $savedRankDivs = $preset['rank_divs'];
}
$savedDivOrder = $savedCfg['div_order'] ?? $preset['div_order'];
$savedIcons    = $fJson['rank_icons'] ?? ($isNew ? $_presetIcons : []);
$existingMain  = $fJson['main'] ?? [];
$existingExtra = $fJson['extra'] ?? [];
$regions       = $preset['regions'];

$optServer    = $savedCfg['show_server']          ?? $preset['server'];
$optPlatform  = $savedCfg['show_platform']        ?? $preset['platform'];
$optPoints    = $savedCfg['show_current_points']  ?? $preset['current_points'];
$optLpGain    = $savedCfg['show_lp_gain']         ?? $preset['lp_gain'];
$optQueue     = $savedCfg['show_queue_type']      ?? $preset['queue_type'];
$optSoloDuo   = $savedCfg['show_solo_duo']        ?? $preset['solo_duo'];

$serverOpts   = !empty($fJson['server_options'])   ? $fJson['server_options']   : $preset['server_opts'];
$platformOpts = !empty($fJson['platform_options']) ? $fJson['platform_options'] : $preset['platform_opts'];
$queueOpts    = !empty($fJson['queue_options'])    ? $fJson['queue_options']    : $preset['queue_opts'];
$lpGainOpts   = !empty($fJson['lp_gain_options'])  ? $fJson['lp_gain_options']  : $preset['lp_gain_opts'];
$pointsOpts   = !empty($fJson['points_options'])   ? $fJson['points_options']   : $preset['points_opts'];

// Load saved custom extras from JSON, fall back to defaults
$_savedExtras = !empty($fJson['extra_config']) && is_array($fJson['extra_config']) ? $fJson['extra_config'] : [];
$allExtras = !empty($_savedExtras) ? $_savedExtras : [
    'is_duo'              => ['label'=>'Duo Queue',                'icon'=>'fa-users',        'def'=>0.65, 'enabled'=>isset($existingExtra['is_duo'])],
    'is_priority'         => ['label'=>'Priority Boost',           'icon'=>'fa-bolt',         'def'=>0.25, 'enabled'=>isset($existingExtra['is_priority'])],
    'is_streaming'        => ['label'=>'Stream Games',             'icon'=>'fa-video',        'def'=>0.15, 'enabled'=>isset($existingExtra['is_streaming'])],
    'is_solo_only'        => ['label'=>'Solo Only Queue',          'icon'=>'fa-user',         'def'=>0.20, 'enabled'=>isset($existingExtra['is_solo_only'])],
    'is_hidden_duo'       => ['label'=>'Hidden Duo',               'icon'=>'fa-eye-slash',    'def'=>0.20, 'enabled'=>isset($existingExtra['is_hidden_duo'])],
    'is_champions_roles'  => ['label'=>'Heroes / Roles Selection', 'icon'=>'fa-chess-knight', 'def'=>0.10, 'enabled'=>isset($existingExtra['is_champions_roles'])],
    'bonus_win_extra_fee' => ['label'=>'+1 Bonus Win',             'icon'=>'fa-trophy',       'def'=>0.10, 'enabled'=>isset($existingExtra['bonus_win_extra_fee'])],
    'is_coaching'         => ['label'=>'Coaching Add-on',          'icon'=>'fa-headset',      'def'=>0.15, 'enabled'=>isset($existingExtra['is_coaching'])],
];
$formTypes = ['rank'=>'Rank Boost','win'=>'Win Boost','placement'=>'Placement Boost','coaching'=>'Coaching','normal'=>'Normal Matches','arena'=>'Arena Boost','clash'=>'Clash Boost','level'=>'Level Boost','mastery'=>'Champion Mastery','match'=>'Matches'];

function fmtOpts(array $opts): string {
    $lines = [];
    foreach ($opts as $v => $l) {
        $lines[] = is_int($v) ? (string)$l : $v . '=' . $l;
    }
    return implode("\n", $lines);
}
?>

<style>
/* ── Custom checkbox ─────────────────────────────────────── */
.lb-check-wrap { display:inline-flex; align-items:center; gap:8px; cursor:pointer; user-select:none; }
.lb-check-wrap input[type=checkbox] { display:none; }
.lb-check-box {
    width:18px; height:18px; border-radius:5px;
    border:2px solid rgba(255,255,255,.15);
    background:rgba(255,255,255,.04);
    display:flex; align-items:center; justify-content:center;
    transition:all .18s; flex-shrink:0;
}
.lb-check-wrap input:checked + .lb-check-box {
    background:#5c4ae3; border-color:#5c4ae3;
    box-shadow:0 0 0 3px rgba(92,74,227,.2);
}
.lb-check-box::after {
    content:''; width:5px; height:8px;
    border:2px solid #fff; border-top:0; border-left:0;
    transform:rotate(45deg) translate(-1px,-1px);
    display:none;
}
.lb-check-wrap input:checked + .lb-check-box::after { display:block; }
.lb-check-label { font-size:12.5px; font-weight:600; color:var(--bs-body-color,#9ca3af); transition:color .15s; }
.lb-check-wrap:hover .lb-check-label { color:#e2e8f0; }
.lb-check-wrap input:checked ~ .lb-check-label { color:#e2e8f0; }

/* ── Section title ──────────────────────────────────────── */
.bfe-sec-title {
    font-size:10px; font-weight:800; text-transform:uppercase; letter-spacing:.09em;
    color:rgba(255,255,255,.3); margin:18px 0 10px;
    display:flex; align-items:center; gap:6px;
}
.bfe-sec-title i { color:#7c69f5; font-size:10px; }

/* ── Page layout ─────────────────────────────────────────── */
.bfe-layout { display:grid; grid-template-columns:1fr 300px; gap:16px; align-items:start; }
@media(max-width:1100px){ .bfe-layout{ grid-template-columns:1fr; } }

/* ── Rank rows ───────────────────────────────────────────── */
.rank-rows { display:flex; flex-direction:column; gap:5px; }
.rank-row {
    display:grid;
    grid-template-columns:24px 1fr 90px 40px 30px;
    gap:6px; align-items:center;
    padding:7px 10px;
    background:rgba(255,255,255,.025);
    border:1px solid rgba(255,255,255,.06);
    border-radius:10px;
    transition:border-color .15s, background .15s;
}
.rank-row:hover { border-color:rgba(92,74,227,.35); background:rgba(92,74,227,.04); }
.rank-row__num { font-size:11px; font-weight:800; color:#7c69f5; text-align:center; }
.rank-row__name input {
    width:100%; background:rgba(255,255,255,.05);
    border:1px solid rgba(255,255,255,.08); border-radius:7px;
    padding:5px 9px; color:#e2e8f0; font-size:12px; font-weight:600;
    font-family:inherit; transition:border-color .15s;
}
.rank-row__name input:focus { outline:none; border-color:rgba(92,74,227,.5); background:rgba(92,74,227,.07); }
.rank-row__divs { position:relative; }
.rank-row__divs select {
    width:100%; background:rgba(255,255,255,.05);
    border:1px solid rgba(255,255,255,.08); border-radius:7px;
    padding:4px 24px 4px 8px; color:#e2e8f0; font-size:11px; cursor:pointer;
    font-family:inherit; appearance:none; -webkit-appearance:none;
    transition:border-color .15s, background .15s;
}
.rank-row__divs select:focus { outline:none; border-color:rgba(92,74,227,.5); background:rgba(92,74,227,.06); }
.rank-row__divs select:hover { border-color:rgba(255,255,255,.18); }
.rank-row__divs::after {
    content:'078'; font-family:'Font Awesome 6 Free'; font-weight:900;
    position:absolute; right:8px; top:50%; transform:translateY(-50%);
    font-size:9px; color:#7c69f5; pointer-events:none;
}

/* ── Icon zone ───────────────────────────────────────────── */
.rank-row__icon {
    width:36px; height:36px; border-radius:8px;
    border:1.5px dashed rgba(255,255,255,.14);
    background:rgba(255,255,255,.03);
    cursor:pointer; display:flex; align-items:center; justify-content:center;
    overflow:hidden; transition:all .2s; position:relative; flex-shrink:0;
}
.rank-row__icon:hover { border-color:#7c69f5; background:rgba(92,74,227,.12); }
.rank-row__icon img { width:26px; height:26px; object-fit:contain; display:block; }
.rank-row__icon .riz-ph { font-size:12px; color:rgba(255,255,255,.2); }
.rank-row__icon .riz-overlay {
    position:absolute; inset:0;
    background:rgba(92,74,227,.7);
    display:flex; align-items:center; justify-content:center;
    opacity:0; transition:opacity .15s;
    font-size:9px; font-weight:800; color:#fff; text-align:center; line-height:1.3;
}
.rank-row__icon:hover .riz-overlay { opacity:1; }

/* ── Del button ──────────────────────────────────────────── */
.rank-row__del {
    width:26px; height:26px; border-radius:6px;
    background:rgba(239,68,68,.07);
    border:1px solid rgba(239,68,68,.18); color:#ef4444;
    font-size:10px; cursor:pointer;
    display:flex; align-items:center; justify-content:center;
    transition:all .15s; flex-shrink:0;
}
.rank-row__del:hover { background:rgba(239,68,68,.18); }

/* ── URL field on hover ──────────────────────────────────── */
.rank-row__urlfield { display:none; grid-column:2/-1; margin-top:3px; }
.rank-row:hover .rank-row__urlfield,
.rank-row.url-open .rank-row__urlfield { display:block; }
.rank-row__urlfield input {
    width:100%; background:rgba(255,255,255,.04);
    border:1px solid rgba(255,255,255,.08); border-radius:7px;
    padding:4px 9px; color:#94a3b8; font-size:10.5px;
    font-family:'Courier New',monospace; transition:border-color .15s;
}
.rank-row__urlfield input:focus { outline:none; border-color:rgba(92,74,227,.4); color:#e2e8f0; }

/* ── Preview chips ───────────────────────────────────────── */
.rank-preview-chips { display:flex; flex-wrap:wrap; gap:5px; padding:8px 14px 10px; }
.rpc {
    display:inline-flex; align-items:center; gap:4px;
    padding:3px 9px; border-radius:20px;
    border:1px solid rgba(255,255,255,.08);
    background:rgba(255,255,255,.03);
    font-size:11px; font-weight:700; color:rgba(255,255,255,.6);
}
.rpc img { width:14px; height:14px; object-fit:contain; }

/* ── Div-order toggle ────────────────────────────────────── */
.divorder-seg {
    display:inline-flex; border-radius:8px; overflow:hidden;
    border:1px solid rgba(255,255,255,.1); background:rgba(255,255,255,.03);
}
.divorder-seg label {
    display:flex; align-items:center; gap:4px;
    padding:4px 12px; font-size:10.5px; font-weight:700;
    color:rgba(255,255,255,.4); cursor:pointer; transition:all .15s; user-select:none;
}
.divorder-seg label:first-child { border-right:1px solid rgba(255,255,255,.08); }
.divorder-seg input { display:none; }
.divorder-seg input:checked + span { color:#a5b4fc; }
.divorder-seg label:has(input:checked) { background:rgba(92,74,227,.2); color:#a5b4fc; }

/* ── Pricing table ───────────────────────────────────────── */
.bfe-price-table { width:100%; border-collapse:collapse; font-size:12px; }
.bfe-price-table th {
    padding:7px 12px; text-align:left;
    font-size:10px; font-weight:800; text-transform:uppercase; letter-spacing:.07em;
    color:rgba(255,255,255,.3); border-bottom:1px solid rgba(255,255,255,.06);
}
.bfe-price-table td { padding:4px 8px; border-bottom:1px solid rgba(255,255,255,.03); vertical-align:middle; }
.bfe-price-table tr:last-child td { border-bottom:none; }
.bfe-price-table td:first-child { color:rgba(255,255,255,.45); font-size:11px; padding-left:14px; white-space:nowrap; }
.bfe-price-inp {
    width:96px; background:rgba(255,255,255,.05);
    border:1px solid rgba(255,255,255,.08); border-radius:7px;
    padding:4px 8px; color:#e2e8f0; font-size:12px; text-align:right;
    font-family:inherit;
}
.bfe-price-inp:focus { outline:none; border-color:rgba(92,74,227,.5); background:rgba(92,74,227,.07); }

/* ── Form options (inline toggle) ───────────────────────── */
.opt-rows-grid { display:grid; grid-template-columns:1fr 1fr; gap:8px; }
.opt-row-wrap {
    border-radius:10px; border:1px solid rgba(255,255,255,.07);
    background:rgba(255,255,255,.02); overflow:hidden;
}
.opt-row-header {
    display:flex; align-items:center; gap:10px;
    padding:10px 13px; cursor:pointer;
}
.opt-row-header:hover { background:rgba(255,255,255,.03); }
.opt-row-icon { width:28px; height:28px; border-radius:7px; background:rgba(92,74,227,.1); color:#a5b4fc; display:flex; align-items:center; justify-content:center; font-size:10px; flex-shrink:0; }
.opt-row-label { flex:1; font-size:12px; font-weight:700; color:#cbd5e1; }
.opt-row-body { display:none; padding:0 13px 12px; border-top:1px solid rgba(255,255,255,.04); }
.opt-row-body.open { display:block; }
.opt-row-body .cfg-lbl { font-size:9.5px; font-weight:800; text-transform:uppercase; letter-spacing:.07em; color:rgba(255,255,255,.3); display:block; margin:9px 0 4px; }
.opt-row-body textarea {
    width:100%; background:rgba(255,255,255,.04);
    border:1px solid rgba(255,255,255,.07); border-radius:7px;
    padding:6px 8px; color:#94a3b8; font-size:10.5px;
    line-height:1.6; resize:none; font-family:'Courier New',monospace;
}
.opt-row-body textarea:focus { outline:none; border-color:rgba(92,74,227,.35); }

/* ── Extras grid ─────────────────────────────────────────── */
.extras-2col { display:grid; grid-template-columns:1fr 1fr; gap:7px; }
.extra-card {
    display:flex; align-items:center; gap:9px;
    padding:10px 12px; border-radius:10px;
    border:1px solid rgba(255,255,255,.07);
    background:rgba(255,255,255,.02);
}
.extra-card__ico {
    width:28px; height:28px; border-radius:7px;
    background:rgba(92,74,227,.1); color:#a5b4fc;
    display:flex; align-items:center; justify-content:center;
    font-size:10px; flex-shrink:0;
}
.extra-card__body { flex:1; min-width:0; }
.extra-card__name { font-size:11.5px; font-weight:800; color:#e2e8f0; }
.extra-card__controls { display:flex; align-items:center; gap:5px; margin-top:4px; }
.xpci {
    width:56px; background:rgba(255,255,255,.05);
    border:1px solid rgba(255,255,255,.09); border-radius:6px;
    padding:2px 6px; color:#e2e8f0; font-size:11px; text-align:right;
    font-family:inherit;
}
.xpci:focus { outline:none; border-color:rgba(92,74,227,.4); }
.pct-lbl { font-size:10px; color:rgba(255,255,255,.3); }

/* ── LP mods ─────────────────────────────────────────────── */
.lp-mods-row { display:flex; flex-wrap:wrap; gap:10px; margin-top:6px; }
.lp-mod {
    display:flex; flex-direction:column; gap:3px; align-items:center;
    padding:8px 10px; border-radius:9px;
    border:1px solid rgba(255,255,255,.07); background:rgba(255,255,255,.02);
}
.lp-mod label { font-size:9.5px; font-weight:800; text-transform:uppercase; letter-spacing:.06em; color:rgba(255,255,255,.3); }
.lp-mod-val { display:flex; align-items:center; gap:3px; }

/* ── JSON box ────────────────────────────────────────────── */
#bfeJsonPreview {
    font-family:'Courier New',monospace; font-size:10.5px;
    color:rgba(255,255,255,.35); max-height:220px; overflow-y:auto;
    padding:10px 12px; background:rgba(0,0,0,.25);
    border-radius:8px; white-space:pre; line-height:1.65;
}
#jsonStatus.ok  { color:#4ade80; font-size:11px; margin-top:4px; }
#jsonStatus.err { color:#ef4444; font-size:11px; margin-top:4px; }

/* ── Small text input ────────────────────────────────────── */
.bfe-mini-input {
    background:rgba(255,255,255,.05);
    border:1px solid rgba(255,255,255,.1);
    border-radius:7px; padding:3px 8px;
    color:#e2e8f0; font-size:12px; font-family:inherit;
}
.bfe-mini-input:focus { outline:none; border-color:rgba(92,74,227,.45); }

/* ── Custom select option colors ─────────────────────────── */
select option {
    background:#1a1d2e !important;
    color:#e2e8f0 !important;
    padding:6px 10px;
}
select option:checked,
select option:hover {
    background:#3730a3 !important;
    color:#fff !important;
}

/* ── Custom select wrapper (divs) ───────────────────────── */
.lb-custom-sel-wrap { position:relative; }
.lb-custom-sel-wrap select {
    appearance:none; -webkit-appearance:none;
    width:100%; background:rgba(255,255,255,.05);
    border:1px solid rgba(255,255,255,.08); border-radius:7px;
    padding:4px 26px 4px 8px; color:#e2e8f0; font-size:11px;
    cursor:pointer; font-family:inherit; transition:border-color .15s, background .15s;
}
.lb-custom-sel-wrap select:focus { outline:none; border-color:rgba(92,74,227,.5); background:rgba(92,74,227,.06); }
.lb-custom-sel-wrap select:hover { border-color:rgba(255,255,255,.18); }
.lb-custom-sel-wrap::after {
    content:'\f078'; font-family:'Font Awesome 6 Free'; font-weight:900;
    position:absolute; right:8px; top:50%; transform:translateY(-50%);
    font-size:9px; color:#7c69f5; pointer-events:none; line-height:1;
}


/* ── V6 polished admin redesign ───────────────────────────── */
.bfe-hero{display:flex;justify-content:space-between;gap:18px;align-items:center;margin:0 0 18px;padding:20px 22px;border:1px solid rgba(255,255,255,.08);border-radius:18px;background:linear-gradient(135deg,rgba(124,105,245,.18),rgba(20,184,166,.06)),rgba(255,255,255,.025);box-shadow:0 18px 50px rgba(0,0,0,.22)}
.bfe-hero__kicker{font-size:11px;font-weight:900;letter-spacing:.12em;text-transform:uppercase;color:#a5b4fc;margin-bottom:5px}.bfe-hero__title{font-size:24px;font-weight:900;color:#fff;margin:0}.bfe-hero__sub{font-size:13px;color:rgba(255,255,255,.55);margin-top:4px}.bfe-hero__actions{display:flex;gap:9px;align-items:center;flex-wrap:wrap}.bfe-hero .btn{border-radius:11px;font-weight:800}.bfe-layout{grid-template-columns:minmax(0,1fr) 330px;gap:20px}.bfe-layout>.bfe-side{position:sticky;top:18px}.bfe-layout .card{border:1px solid rgba(255,255,255,.08)!important;border-radius:16px!important;background:rgba(255,255,255,.035)!important;box-shadow:0 14px 40px rgba(0,0,0,.18);overflow:hidden}.bfe-layout .card-header{background:linear-gradient(180deg,rgba(255,255,255,.045),rgba(255,255,255,.015))!important;border-bottom:1px solid rgba(255,255,255,.07)!important;padding:16px 18px}.bfe-layout .card-body{padding:18px}.bfe-layout .form-control,.bfe-layout .form-select,.bfe-mini-input,.lb-custom-sel-wrap select{border-radius:10px!important;border:1px solid rgba(255,255,255,.1)!important;background:rgba(8,12,18,.45)!important;color:#f8fafc!important}.bfe-layout .form-control:focus,.bfe-layout .form-select:focus,.bfe-mini-input:focus{border-color:rgba(124,105,245,.65)!important;box-shadow:0 0 0 3px rgba(124,105,245,.14)!important}.rank-row{border-radius:12px;background:rgba(8,12,18,.36);box-shadow:inset 0 1px 0 rgba(255,255,255,.03)}.rank-row:hover{transform:translateY(-1px);box-shadow:0 10px 30px rgba(0,0,0,.16),inset 0 1px 0 rgba(255,255,255,.04)}.rank-row__icon{border-radius:10px;background:rgba(124,105,245,.08)}.extra-card{border-radius:14px!important;background:rgba(8,12,18,.35)!important;border:1px solid rgba(255,255,255,.075)!important}.extra-card:hover{border-color:rgba(124,105,245,.28)!important;background:rgba(124,105,245,.07)!important}.extra-card__ico{border-radius:12px!important;background:linear-gradient(135deg,rgba(124,105,245,.22),rgba(20,184,166,.12))!important}.rank-preview-chips{background:rgba(8,12,18,.28);border-bottom:1px solid rgba(255,255,255,.06);padding:12px 18px}.pricing-table-wrap,.bfe-json-box{border-radius:14px!important;background:rgba(8,12,18,.28)!important;border:1px solid rgba(255,255,255,.07)!important}.divorder-seg label span{border-radius:9px!important}.bfe-savebar{position:sticky;bottom:16px;z-index:5;border:1px solid rgba(124,105,245,.25);background:rgba(20,23,28,.88);backdrop-filter:blur(12px);border-radius:16px;padding:12px 14px;box-shadow:0 18px 45px rgba(0,0,0,.28)}
@media(max-width:1100px){.bfe-layout{grid-template-columns:1fr}.bfe-layout>.bfe-side{position:static}.bfe-hero{align-items:flex-start;flex-direction:column}}



/* ── V7 full visual pass for Boost Form Editor ───────────── */
.bfe-hero{border-radius:22px!important;padding:24px 26px!important;background:radial-gradient(circle at 12% 0%,rgba(124,105,245,.28),transparent 34%),linear-gradient(135deg,rgba(124,105,245,.16),rgba(20,184,166,.075)),rgba(255,255,255,.025)!important}.bfe-hero__title{font-size:27px!important;letter-spacing:-.035em}.bfe-layout{gap:22px!important}.bfe-layout .card{border-radius:19px!important;background:linear-gradient(180deg,rgba(255,255,255,.045),rgba(255,255,255,.020))!important;border-color:rgba(255,255,255,.08)!important}.bfe-layout .card-header{padding:18px 20px!important;background:rgba(8,12,18,.32)!important}.bfe-layout .card-body{padding:20px!important}.bfe-sec-title{padding:8px 10px;margin:20px 0 12px!important;border-radius:11px;background:rgba(124,105,245,.07);border:1px solid rgba(124,105,245,.12);color:rgba(255,255,255,.55)!important}.rank-row,.extra-card,.pricing-table-wrap,.bfe-json-box{border-radius:16px!important}.rank-row{padding:10px!important;background:linear-gradient(180deg,rgba(255,255,255,.035),rgba(255,255,255,.015))!important;border:1px solid rgba(255,255,255,.075)!important}.rank-row__icon{width:42px!important;height:42px!important}.extra-card{padding:15px!important}.bfe-price-table th{background:rgba(124,105,245,.08)!important;color:#c4b5fd!important}.bfe-price-table td{padding:7px 8px!important}.bfe-savebar{border-radius:18px!important;padding:14px 16px!important}.bfe-layout .btn{border-radius:12px!important;font-weight:850!important}.bfe-layout .form-label{font-size:11px!important;text-transform:uppercase;letter-spacing:.075em;color:rgba(255,255,255,.50)!important}

</style>

<div class="container-fluid py-3">

  <!-- Breadcrumb -->
  <nav aria-label="breadcrumb" class="mb-3">
    <ol class="breadcrumb">
      <li class="breadcrumb-item"><a href="/admin-area/games">Games</a></li>
      <li class="breadcrumb-item"><a href="/admin-area/games/<?= $gameId ?>/edit"><?= htmlspecialchars($gameName) ?></a></li>
      <li class="breadcrumb-item active"><?= $isNew ? 'New Boost Form' : htmlspecialchars($fName) ?></li>
    </ol>
  </nav>


  <div class="bfe-hero">
    <div>
      <div class="bfe-hero__kicker"><?= htmlspecialchars($gameName) ?> Boost Form</div>
      <h1 class="bfe-hero__title"><?= $isNew ? 'Create Boost Form' : 'Edit ' . htmlspecialchars($fName ?: 'Boost Form') ?></h1>
      <div class="bfe-hero__sub">Configure form settings, ranks, pricing, options and frontend JSON in one cleaner workspace.</div>
    </div>
    <div class="bfe-hero__actions">
      <a class="btn btn-ghost-secondary btn-sm" href="/admin-area/games/<?= $gameId ?>/edit"><i class="fa-solid fa-arrow-left me-1"></i>Back to Game</a>
      <?php if (!$isNew && $fSlug): ?><a class="btn btn-ghost-secondary btn-sm" target="_blank" href="/<?= htmlspecialchars($gameSlug) ?>/<?= htmlspecialchars($fSlug) ?>"><i class="fa-solid fa-arrow-up-right-from-square me-1"></i>Preview</a><?php endif ?>
    </div>
  </div>

  <?php if (isset($_GET['created'])): ?><div class="alert alert-soft-success py-2 mb-3"><i class="fa-solid fa-circle-check me-2"></i>Form created — configure pricing below and save.</div><?php endif ?>
  <?php if (isset($_GET['saved'])): ?><div class="alert alert-soft-success py-2 mb-3"><i class="fa-solid fa-circle-check me-2"></i>Saved successfully.</div><?php endif ?>
  <?php if (isset($_GET['error'])): ?><div class="alert alert-soft-danger py-2 mb-3"><i class="fa-solid fa-triangle-exclamation me-2"></i>Name and slug are required.</div><?php endif ?>

  <form method="POST" action="<?= $saveUrl ?>">
    <?php if ($formId): ?><input type="hidden" name="fid" value="<?= $formId ?>"><?php endif ?>
    <input type="hidden" name="pricing_json" id="hiddenPricingJson">

    <div class="bfe-layout">

      <!-- ══ LEFT ══════════════════════════════════════════════════ -->
      <div>

        <!-- Form Settings -->
        <div class="card mb-4">
          <div class="card-header d-flex align-items-center justify-content-between">
            <h5 class="card-header-title"><i class="fa-solid fa-gear me-2" style="color:#7c69f5"></i>Form Settings</h5>
            <select class="form-select form-select-sm" name="status" style="width:auto">
              <option value="1" <?= $fStatus===1?'selected':'' ?>>● Active</option>
              <option value="2" <?= $fStatus===2?'selected':'' ?>>◌ Draft</option>
              <option value="0" <?= $fStatus===0?'selected':'' ?>>○ Inactive</option>
            </select>
          </div>
          <div class="card-body">
            <div class="row g-3">
              <div class="col-md-4">
                <label class="form-label">Name *</label>
                <input type="text" class="form-control" name="name" value="<?= htmlspecialchars($fName) ?>" placeholder="Rank Boost" required oninput="autoFill(this.value)">
              </div>
              <div class="col-md-5">
                <label class="form-label">Long Name <span class="text-muted small">(H1)</span></label>
                <input type="text" class="form-control" name="name_long" id="fNameLong" value="<?= htmlspecialchars($fNameLong) ?>" placeholder="<?= htmlspecialchars($gameName) ?> Rank Boost">
              </div>
              <div class="col-md-3">
                <label class="form-label">Form Type</label>
                <select class="form-select" name="type">
                  <?php foreach ($formTypes as $v=>$l): ?><option value="<?= $v ?>" <?= $fType===$v?'selected':'' ?>><?= $l ?></option><?php endforeach ?>
                </select>
              </div>
              <div class="col-md-5">
                <label class="form-label">URL Slug *</label>
                <div class="input-group">
                  <span class="input-group-text text-muted small">/<?= htmlspecialchars($gameSlug) ?>/</span>
                  <input type="text" class="form-control" name="slug" id="fSlug" value="<?= htmlspecialchars($fSlug) ?>" placeholder="rank-boost" required>
                </div>
              </div>
              <div class="col-md-7">
                <label class="form-label">Description</label>
                <textarea class="form-control" name="description" rows="1"><?= htmlspecialchars($fDesc) ?></textarea>
              </div>
            </div>
          </div>
        </div>

        <!-- Ranks -->
        <div class="card mb-4">
          <div class="card-header d-flex align-items-center justify-content-between">
            <h5 class="card-header-title d-flex align-items-center gap-2">
              <i class="fa-solid fa-medal" style="color:#7c69f5"></i>Ranks
              <span class="badge bg-soft-secondary text-secondary" id="rankCountBadge"><?= count($savedRanks) ?></span>
            </h5>
            <div class="d-flex align-items-center gap-3">
              <div class="d-flex align-items-center gap-2">
                <span class="text-muted" style="font-size:11px;font-weight:700">Div. order:</span>
                <div class="divorder-seg">
                  <label title="IV lowest · I highest (LoL, Apex, Rivals)">
                    <input type="radio" name="divorder_ui" value="desc" <?= $savedDivOrder!=='asc'?'checked':'' ?>>
                    <span>IV→I</span>
                  </label>
                  <label title="I lowest · III highest (Valorant, RL)">
                    <input type="radio" name="divorder_ui" value="asc" <?= $savedDivOrder==='asc'?'checked':'' ?>>
                    <span>I→III</span>
                  </label>
                </div>
              </div>
              <button type="button" class="btn btn-ghost-secondary btn-sm" onclick="addRank()">
                <i class="fa-solid fa-plus me-1"></i>Add Rank
              </button>
            </div>
          </div>

          <!-- Live preview chips -->
          <div class="rank-preview-chips" id="rankPreviewChips"></div>

          <div class="card-body pt-2">
            <div class="rank-rows" id="rankRows"></div>
          </div>
        </div>

        <!-- Pricing -->
        <div class="card mb-4">
          <div class="card-header d-flex align-items-center justify-content-between">
            <h5 class="card-header-title">
              <i class="fa-solid fa-euro-sign me-2" style="color:#7c69f5"></i>Pricing
              <span class="text-muted fw-normal ms-2" style="font-size:11px;text-transform:none;letter-spacing:0">Enter prices in € (e.g. 9.99)</span>
            </h5>
            <div class="d-flex align-items-center gap-2">
              <span class="text-muted" style="font-size:11px">Points label:</span>
              <input class="bfe-mini-input" id="cfgPointsLabel" value="<?= htmlspecialchars($fJson['points_label'] ?? $preset['points_label']) ?>" style="width:52px;text-align:center">
              <input type="number" class="bfe-mini-input" id="cfgCompTime" value="<?= (int)($fJson['completion_time'] ?? 4) ?>" min="1" title="Completion time in hours" style="width:60px" placeholder="hrs">
              <button type="button" class="btn btn-ghost-secondary btn-sm" onclick="fillTestPrices()">
                <i class="fa-solid fa-wand-magic-sparkles me-1"></i>Fill test
              </button>
            </div>
          </div>
          <div class="table-responsive">
            <table class="bfe-price-table">
              <thead>
                <tr>
                  <th>From → To</th>
                  <?php foreach ($regions as $r): ?><th><?= strtoupper($r) ?> (€)</th><?php endforeach ?>
                </tr>
              </thead>
              <tbody id="priceTableBody"></tbody>
            </table>
          </div>

        </div>

        <!-- Form Options -->
        <div class="card mb-4">
          <div class="card-header d-flex align-items-center justify-content-between">
            <h5 class="card-header-title"><i class="fa-solid fa-sliders me-2" style="color:#7c69f5"></i>Form Options</h5>
            <span class="text-muted" style="font-size:11px">What appears below the rank selector</span>
          </div>
          <div class="card-body">
            <div class="opt-rows-grid">

              <?php
              $optDefs = [
                  ['id'=>'Server',   'icon'=>'fa-server',        'chk'=>$optServer,   'opts'=>$serverOpts,  'label'=>'Server / Region'],
                  ['id'=>'Platform', 'icon'=>'fa-desktop',       'chk'=>$optPlatform, 'opts'=>$platformOpts,'label'=>'Platform'],
                  ['id'=>'Points',   'icon'=>'fa-chart-bar',     'chk'=>$optPoints,   'opts'=>$pointsOpts,  'label'=>'Current Points'],
                  ['id'=>'LpGain',   'icon'=>'fa-arrow-trend-up','chk'=>$optLpGain,   'opts'=>$lpGainOpts,  'label'=>'LP / Points Gain'],
                  ['id'=>'Queue',    'icon'=>'fa-gamepad',       'chk'=>$optQueue,    'opts'=>$queueOpts,   'label'=>'Queue Type'],
                  ['id'=>'SoloDuo',  'icon'=>'fa-users',         'chk'=>$optSoloDuo,  'opts'=>[],           'label'=>'Solo / Duo Toggle'],
              ];
              foreach ($optDefs as $od):
              ?>
              <div class="opt-row-wrap" id="optWrap_<?= $od['id'] ?>">
                <div class="opt-row-header" onclick="toggleOptRow('<?= $od['id'] ?>')">
                  <div class="opt-row-icon"><i class="fa-solid <?= $od['icon'] ?>"></i></div>
                  <label class="lb-check-wrap opt-row-label" onclick="event.stopPropagation();toggleOptRow('<?= $od['id'] ?>')">
                    <input type="checkbox" id="opt<?= $od['id'] ?>" <?= $od['chk']?'checked':'' ?> onchange="onOptChange('<?= $od['id'] ?>')">
                    <div class="lb-check-box"></div>
                    <span class="lb-check-label"><?= $od['label'] ?></span>
                  </label>
                </div>
                <?php if ($od['id'] !== 'SoloDuo'): ?>
                <div class="opt-row-body <?= $od['chk'] ? 'open' : '' ?>" id="optBody_<?= $od['id'] ?>">
                  <?php
                  $showRows = in_array($od['id'], ['Server','Platform','Points','LpGain','Queue']);
                  $hasDiscount = in_array($od['id'], ['LpGain','Points']); // show % discount column
                  if ($showRows):
                  $colTpl = $hasDiscount ? '90px 1fr 58px 14px 22px' : '90px 1fr 22px';
                  ?>
                  <?php if ($hasDiscount): ?>
                  <div style="display:grid;grid-template-columns:<?= $colTpl ?>;gap:5px;padding:0 0 3px" class="opt-dyn-head">
                    <span style="font-size:9px;font-weight:800;color:rgba(255,255,255,.25);text-transform:uppercase">Value</span>
                    <span style="font-size:9px;font-weight:800;color:rgba(255,255,255,.25);text-transform:uppercase">Label</span>
                    <span style="font-size:9px;font-weight:800;color:rgba(255,255,255,.25);text-transform:uppercase">Disc.%</span>
                  </div>
                  <?php endif ?>
                  <div id="optRows_<?= $od['id'] ?>" style="display:flex;flex-direction:column;gap:4px">
                    <?php
                    // For LpGain: merge lp_gain_options (labels) with lp_gain (% values)
                    // For Points: merge points_options (labels) with start_lp (% values)
                    $discountSource = $od['id']==='LpGain' ? ($fJson['lp_gain'] ?? []) : ($fJson['start_lp'] ?? []);
                    foreach ($od['opts'] as $_ok => $_ol):
                      $_disc = $hasDiscount ? (int)round(($discountSource[$_ok] ?? 0) * 100) : 0;
                    ?>
                    <div class="opt-dyn-row" style="display:grid;grid-template-columns:<?= $colTpl ?>;gap:5px;align-items:center">
                      <input type="text" class="form-control form-control-sm opt-row-key" value="<?= htmlspecialchars($_ok) ?>" placeholder="value" style="font-family:monospace;font-size:11px">
                      <input type="text" class="form-control form-control-sm opt-row-lbl" value="<?= htmlspecialchars($_ol) ?>" placeholder="Label">
                      <?php if ($hasDiscount): ?>
                      <input type="number" class="bfe-mini-input opt-row-disc" value="<?= $_disc ?>" min="-100" max="100" step="1" style="text-align:right" title="Discount %">
                      <span style="font-size:10px;color:rgba(255,255,255,.25)">%</span>
                      <?php endif ?>
                      <button type="button" class="rank-row__del" style="width:22px;height:22px;font-size:9px" onclick="this.closest('.opt-dyn-row').remove();refreshJson()"><i class="fa-solid fa-xmark"></i></button>
                    </div>
                    <?php endforeach ?>
                  </div>
                  <button type="button" class="btn btn-ghost-secondary btn-sm mt-2" onclick="addOptRow('<?= $od['id'] ?>',<?= $hasDiscount?'true':'false' ?>)">
                    <i class="fa-solid fa-plus me-1"></i>Add option
                  </button>
                  <?php endif ?>
                </div>
                <?php endif ?>
              </div>
              <?php endforeach ?>

            </div>
          </div>
        </div>

        <!-- Extra Options -->
        <div class="card mb-4">
          <div class="card-header d-flex align-items-center justify-content-between">
            <h5 class="card-header-title"><i class="fa-solid fa-puzzle-piece me-2" style="color:#7c69f5"></i>Extra Options & Fees</h5>
            <div class="d-flex align-items-center gap-2">
              <span class="text-muted" style="font-size:11px">% added to base price</span>
              <button type="button" class="btn btn-ghost-secondary btn-sm" onclick="addExtraOption()">
                <i class="fa-solid fa-plus me-1"></i>Add Option
              </button>
            </div>
          </div>
          <div class="card-body">
            <div class="extras-2col" id="extrasGrid">
              <?php foreach ($allExtras as $key => $ex):
                // $ex['def'] is the percentage (0.0–3.0), $ex['enabled'] is the on/off toggle
                $defPct = is_numeric($ex['def'] ?? null) ? (float)$ex['def'] : 0.10;
                $val    = isset($existingExtra[$key]) ? (float)$existingExtra[$key] : $defPct;
                // 'enabled' from extra_config is the source of truth when present
                if (array_key_exists('enabled', $ex)) {
                    $on = (bool)$ex['enabled'];
                } else {
                    $on = isset($existingExtra[$key]) && $existingExtra[$key] > 0;
                }
                $pctDisplay = (int)round($val * 100); ?>
              <div class="extra-card" data-key="<?= htmlspecialchars($key) ?>">
                <div class="extra-card__ico" id="extraIco_<?= htmlspecialchars($key) ?>" onclick="cycleExtraIcon('<?= htmlspecialchars($key) ?>')" style="cursor:pointer" title="Click to change icon"><i class="fa-solid <?= htmlspecialchars($ex['icon'] ?? 'fa-star') ?>"></i></div>
                <div class="extra-card__body">
                  <input type="text" class="extra-lbl-inp" data-key="<?= htmlspecialchars($key) ?>"
                         value="<?= htmlspecialchars($ex['label'] ?? $key) ?>"
                         style="background:transparent;border:none;border-bottom:1px solid rgba(255,255,255,.08);color:#e2e8f0;font-size:11.5px;font-weight:800;width:100%;padding:1px 0;font-family:inherit"
                         onblur="this.style.borderColor='rgba(255,255,255,.08)'"
                         onfocus="this.style.borderColor='rgba(92,74,227,.5)'">
                  <div class="extra-card__controls">
                    <label class="lb-check-wrap">
                      <input type="checkbox" class="extra-cb" data-key="<?= htmlspecialchars($key) ?>" <?= $on?'checked':'' ?>>
                      <div class="lb-check-box"></div>
                      <span class="lb-check-label" style="font-size:11px">On</span>
                    </label>
                    <input type="number" class="xpci extra-pct" data-key="<?= htmlspecialchars($key) ?>" value="<?= $pctDisplay ?>" min="0" max="300" step="1">
                    <span class="pct-lbl">%</span>
                    <button type="button" class="rank-row__del ms-1" style="width:22px;height:22px;font-size:9px" onclick="removeExtra('<?= htmlspecialchars($key) ?>')" title="Remove"><i class="fa-solid fa-xmark"></i></button>
                  </div>
                  <?php
                  $cls = $ex['class'] ?? ($ex['css_class'] ?? '');
                  ?>
                  <div style="display:flex;align-items:center;gap:4px;margin-top:5px">
                    <span style="font-size:9.5px;color:rgba(255,255,255,.3);font-weight:800;text-transform:uppercase;letter-spacing:.06em">Visible:</span>
                    <?php foreach([''=>'Both','solo-option'=>'Solo only','duo-option'=>'Duo only'] as $clsVal=>$clsLbl): ?>
                    <label style="display:inline-flex;align-items:center;gap:3px;padding:2px 7px;border-radius:5px;border:1px solid rgba(255,255,255,.08);background:rgba(255,255,255,.03);cursor:pointer;font-size:10px;font-weight:700;color:<?= $cls===$clsVal?'#a5b4fc':'rgba(255,255,255,.4)' ?>;<?= $cls===$clsVal?'background:rgba(92,74,227,.15);border-color:rgba(92,74,227,.3)':'' ?>">
                      <input type="radio" name="extra_cls_<?= htmlspecialchars($key) ?>" class="extra-cls" data-key="<?= htmlspecialchars($key) ?>" value="<?= $clsVal ?>" <?= $cls===$clsVal?'checked':'' ?> style="display:none">
                      <?= $clsLbl ?>
                    </label>
                    <?php endforeach ?>
                  </div>
                </div>
              </div>
              <?php endforeach ?>
            </div>

            <!-- Heroes / Roles / Agents selection config -->
            <?php
            $heroesKey = null;
            foreach ($allExtras as $_ek => $_ex) {
                if (isset($_ex['has_selection']) && $_ex['has_selection']) { $heroesKey = $_ek; break; }
                $lbl = strtolower($_ex['label'] ?? '');
                if (strpos($lbl,'hero')!==false||strpos($lbl,'role')!==false||strpos($lbl,'champion')!==false||strpos($lbl,'agent')!==false) { $heroesKey = $_ek; break; }
            }
            $selectionItems = $fJson['selection_items'][$heroesKey ?? 'is_champions_roles'] ?? [];
            $selectionLabel = $heroesKey ? ($allExtras[$heroesKey]['label'] ?? 'Heroes / Roles') : 'Heroes / Roles';
            ?>
            <div class="bfe-sec-title" style="margin-top:18px"><i class="fa-solid fa-chess-knight"></i><?= htmlspecialchars($selectionLabel) ?> Items</div>
            <div style="font-size:11px;color:rgba(255,255,255,.3);margin-bottom:8px">
              Add selectable options (e.g. heroes, roles, agents). Leave empty to disable the picker.
            </div>
            <div id="selectionItemsWrap">
              <div id="selectionRows" style="display:flex;flex-direction:column;gap:5px;margin-bottom:8px">
                <?php foreach ($selectionItems as $_si): ?>
                <div class="sel-row" style="display:grid;grid-template-columns:1fr 40px 26px;gap:6px;align-items:center">
                  <input type="text" class="sel-name form-control form-control-sm" value="<?= htmlspecialchars($_si['name']??$_si) ?>">
                  <div style="position:relative">
                    <img class="sel-icon-preview" src="<?= htmlspecialchars($_si['icon']??'') ?>" style="width:28px;height:28px;border-radius:6px;border:1px solid rgba(255,255,255,.1);background:rgba(255,255,255,.04);object-fit:contain;cursor:pointer;display:<?= !empty($_si['icon'])?'block':'none' ?>" onerror="this.style.display='none'">
                    <div class="sel-icon-empty" style="width:28px;height:28px;border-radius:6px;border:1.5px dashed rgba(255,255,255,.15);background:rgba(255,255,255,.03);display:flex;align-items:center;justify-content:center;cursor:pointer;font-size:10px;color:rgba(255,255,255,.2);<?= !empty($_si['icon'])?'display:none':'' ?>" onclick="triggerSelIcon(this)">
                      <i class="fa-solid fa-image"></i>
                    </div>
                    <input type="text" class="sel-icon-url" value="<?= htmlspecialchars($_si['icon']??'') ?>" placeholder="icon URL" style="display:none" oninput="updateSelIconPreview(this)">
                  </div>
                  <button type="button" class="rank-row__del" onclick="this.closest('.sel-row').remove()" style="width:26px;height:26px;font-size:10px"><i class="fa-solid fa-xmark"></i></button>
                </div>
                <?php endforeach ?>
              </div>
              <button type="button" class="btn btn-ghost-secondary btn-sm" onclick="addSelectionRow()">
                <i class="fa-solid fa-plus me-1"></i>Add Item
              </button>
            </div>

            <?php if (!empty($preset['lp_gain_opts'])): ?>
            <div class="bfe-sec-title"><i class="fa-solid fa-arrow-trend-up"></i>LP / RP Gain Modifiers</div>
            <div class="lp-mods-row">
              <?php foreach ($preset['lp_gain_opts'] as $k=>$l):
                $lv = $fJson['lp_gain'][$k] ?? 0; ?>
              <div class="lp-mod">
                <label><?= htmlspecialchars($k) ?></label>
                <div class="lp-mod-val">
                  <input type="number" class="xpci lp-mod-inp" data-key="<?= htmlspecialchars($k) ?>" value="<?= (int)round($lv*100) ?>" min="-100" max="100" step="1">
                  <span class="pct-lbl">%</span>
                </div>
              </div>
              <?php endforeach ?>
            </div>
            <?php endif ?>

            <?php if (!empty($preset['points_opts'])): ?>
            <div class="bfe-sec-title"><i class="fa-solid fa-chart-bar"></i>Current Points Discount</div>
            <div class="lp-mods-row">
              <?php foreach ($preset['points_opts'] as $k=>$l):
                $pv = $fJson['start_lp'][$k] ?? 0; ?>
              <div class="lp-mod">
                <label><?= htmlspecialchars($k) ?></label>
                <div class="lp-mod-val">
                  <input type="number" class="xpci start-lp-inp" data-key="<?= htmlspecialchars($k) ?>" value="<?= (int)round($pv*100) ?>" min="-100" max="100" step="1">
                  <span class="pct-lbl">%</span>
                </div>
              </div>
              <?php endforeach ?>
            </div>
            <?php endif ?>
          </div>
        </div>

      </div><!-- /LEFT -->

      <!-- ══ SIDEBAR ════════════════════════════════════════════════ -->
      <div>

        <!-- Actions -->
        <div class="card mb-4">
          <div class="card-body">
            <button type="submit" class="btn btn-primary w-100 mb-2" onclick="buildJson()">
              <i class="fa-solid fa-floppy-disk me-2"></i><?= $isNew?'Create Form':'Save Changes' ?>
            </button>
            <?php if (!$isNew): ?>
            <a href="/<?= htmlspecialchars($gameSlug) ?>/<?= htmlspecialchars($fSlug) ?>" target="_blank" class="btn btn-ghost-secondary w-100 mb-2">
              <i class="fa-solid fa-arrow-up-right-from-square me-1"></i>Preview Live Page
            </a>
            <?php endif ?>
            <a href="/admin-area/games/<?= $gameId ?>/edit" class="btn btn-ghost-secondary w-100">
              <i class="fa-solid fa-arrow-left me-1"></i>Back
            </a>
          </div>
        </div>

        <!-- Info -->
        <div class="card mb-4">
          <div class="card-header"><h5 class="card-header-title"><i class="fa-solid fa-circle-info me-2" style="color:#7c69f5"></i>Info</h5></div>
          <div class="card-body">
            <dl class="row row-cols-2 g-2 mb-0" style="font-size:12px">
              <dt class="col text-muted fw-normal">Game</dt><dd class="col"><code><?= htmlspecialchars($gameSlug) ?></code></dd>
              <dt class="col text-muted fw-normal">Code</dt><dd class="col"><code><?= htmlspecialchars($gc) ?></code></dd>
              <?php if ($fUuid): ?>
              <dt class="col text-muted fw-normal">UUID</dt><dd class="col" style="overflow:hidden"><code style="font-size:9.5px"><?= substr($fUuid,0,22) ?>…</code></dd>
              <?php endif ?>
            </dl>
          </div>
        </div>

        <!-- JSON preview -->
        <div class="card">
          <div class="card-header d-flex align-items-center justify-content-between">
            <h5 class="card-header-title"><i class="fa-solid fa-code me-2" style="color:#7c69f5"></i>JSON</h5>
            <button type="button" class="btn btn-ghost-secondary btn-sm" id="rawToggleBtn" onclick="toggleRawJson()">Raw ↓</button>
          </div>
          <div id="jsonPreviewWrap">
            <div id="bfeJsonPreview"></div>
          </div>
          <div id="jsonRawWrap" style="display:none" class="card-body pt-2">
            <textarea class="form-control" id="rawJsonTA" rows="8" style="font-family:'Courier New',monospace;font-size:11px;resize:vertical"></textarea>
            <div id="jsonStatus"></div>
            <button type="button" class="btn btn-ghost-secondary btn-sm w-100 mt-2" onclick="importRawJson()">
              <i class="fa-solid fa-upload me-1"></i>Import JSON
            </button>
          </div>
        </div>

      </div><!-- /SIDEBAR -->
    </div>
  </form>
</div>

<script>
// ── PHP → JS data ────────────────────────────────────────────────────────────
var GC          = <?= json_encode($gc) ?>;
var ASSET_URL   = <?= json_encode(ASSET_URL) ?>;
var SAVED_RANKS = <?= json_encode($savedRanks) ?>;
var SAVED_DIVS  = <?= (int)$savedDivs ?>;
var SAVED_RANK_DIVS = <?= json_encode($savedRankDivs ?: new stdClass()) ?>;
var SAVED_ICONS = <?= json_encode($savedIcons ?: new stdClass()) ?>;
var EXIST_MAIN  = <?= json_encode($existingMain ?: new stdClass()) ?>;
var EXIST_JSON  = <?= json_encode($fJson ?: new stdClass(), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?>;
var FORM_TYPE   = <?= json_encode($fType) ?>;
var IS_LOL_CLASSIC = GC === 'lol_classic';
var REGIONS     = <?= json_encode($regions) ?>;

var iconState = {};
Object.keys(SAVED_ICONS).forEach(function(k){ iconState[k] = SAVED_ICONS[k]; });

// ── Auto-fill ─────────────────────────────────────────────────────────────────
function autoFill(n) {
    var s = document.getElementById('fSlug'), l = document.getElementById('fNameLong');
    if (s && !s.dataset.m) s.value = n.toLowerCase().trim().replace(/[^a-z0-9]+/g,'-').replace(/^-+|-+$/g,'');
    if (l && !l.dataset.m) l.value = n;
}
document.getElementById('fSlug').addEventListener('input', function(){ this.dataset.m='1'; });
document.getElementById('fNameLong').addEventListener('input', function(){ this.dataset.m='1'; });

// ── Options toggle ────────────────────────────────────────────────────────────
function toggleOptRow(id) {
    var cb   = document.getElementById('opt'+id);
    var body = document.getElementById('optBody_'+id);
    if (!cb) return;
    cb.checked = !cb.checked;
    if (body) body.classList.toggle('open', cb.checked);
    refreshJson();
}
function onOptChange(id) {
    var cb   = document.getElementById('opt'+id);
    var body = document.getElementById('optBody_'+id);
    if (body && cb) body.classList.toggle('open', cb.checked);
    refreshJson();
}

// ── Div order ─────────────────────────────────────────────────────────────────
function getDivOrder() {
    var r = document.querySelector('input[name="divorder_ui"]:checked');
    return r ? r.value : 'desc';
}
document.querySelectorAll('input[name="divorder_ui"]').forEach(function(r){
    r.addEventListener('change', buildPriceTable);
});

// ── Rank rows ─────────────────────────────────────────────────────────────────
function esc(s){ return String(s).replace(/&/g,'&amp;').replace(/"/g,'&quot;').replace(/</g,'&lt;').replace(/>/g,'&gt;'); }

function buildRankRows(ranksObj, savedDivs, iconsObj, rankDivsObj) {
    var c = document.getElementById('rankRows'); c.innerHTML = '';
    rankDivsObj = rankDivsObj || {};
    var tiers = Object.keys(ranksObj).map(Number).sort(function(a,b){return a-b;});
    tiers.forEach(function(t){
        var key = String(t);
        var rowDivs = Object.prototype.hasOwnProperty.call(rankDivsObj, key) ? parseInt(rankDivsObj[key], 10) : savedDivs;
        if (isNaN(rowDivs)) rowDivs = savedDivs;
        c.appendChild(makeRankRow(t, ranksObj[t], rowDivs, iconsObj[t]||iconsObj[String(t)]||''));
    });
    updatePreviewChips();
    updateRankCount();
}

function makeRankRow(tier, name, divs, iconUrl) {
    var row = document.createElement('div');
    row.className = 'rank-row'; row.dataset.tier = tier;
    var imgStyle = iconUrl ? '' : 'display:none';
    var phStyle  = iconUrl ? 'display:none' : '';
    row.innerHTML =
        '<div class="rank-row__num">' + tier + '</div>' +
        '<div class="rank-row__name"><input type="text" class="rank-name" data-tier="' + tier + '" value="' + esc(name) + '" placeholder="Rank ' + tier + '" oninput="onRankName(this)"></div>' +
        '<div class="rank-row__divs lb-custom-sel-wrap">' +
            '<select class="divs-sel" data-tier="' + tier + '" onchange="buildPriceTable();updatePreviewChips()">' +
                '<option value="0">No divs</option>' +
                '<option value="3"' + (divs===3?' selected':'') + '>3 divs</option>' +
                '<option value="4"' + (divs===4?' selected':'') + '>4 divs</option>' +
            '</select>' +
        '</div>' +
        '<div class="rank-row__icon" data-tier="' + tier + '"' +
             ' ondragover="event.preventDefault();this.classList.add(\'drag-over\')"' +
             ' ondragleave="this.classList.remove(\'drag-over\')"' +
             ' ondrop="handleDrop(event,' + tier + ')"' +
             ' onclick="pickIconFile(' + tier + ')"' +
             ' title="Click to upload · Drag & drop · Paste URL below">' +
            '<img id="iconImg_' + tier + '" src="' + esc(iconUrl) + '" style="' + imgStyle + '" onerror="this.style.display=\'none\'">' +
            '<span class="riz-ph" id="iconPh_' + tier + '" style="' + phStyle + '"><i class="fa-solid fa-image"></i></span>' +
            '<div class="riz-overlay">Upload<br>/Drop<br>/URL</div>' +
        '</div>' +
        '<button type="button" class="rank-row__del" onclick="removeRank(' + tier + ')" title="Remove"><i class="fa-solid fa-xmark"></i></button>' +
        '<div class="rank-row__urlfield"><input type="text" class="icon-url-inp" data-tier="' + tier + '" value="' + esc(iconUrl) + '" placeholder="Paste icon URL or drag image above…" oninput="applyIconUrl(' + tier + ',this.value)"></div>';
    return row;
}

function onRankName(inp) {
    var tier = inp.dataset.tier;
    // Auto-suggest icon if none set
    var img = document.getElementById('iconImg_' + tier);
    if (img && (!img.src || img.style.display === 'none')) {
        var slug = inp.value.toLowerCase().replace(/\s+/g,'-').replace(/[^a-z0-9-]/g,'');
        var auto = ASSET_URL + '/core/main/img/' + GC + '/ranks/mini/' + slug + '.webp';
        applyIconUrl(tier, auto);
        var urlInp = document.querySelector('.icon-url-inp[data-tier="'+tier+'"]');
        if (urlInp) urlInp.value = auto;
    }
    updatePreviewChips();
    buildPriceTable();
}

function pickIconFile(tier) {
    var inp = document.createElement('input'); inp.type='file'; inp.accept='image/*';
    inp.onchange = function(){ if(this.files[0]) readFileToUrl(tier, this.files[0]); };
    inp.click();
}
function handleDrop(ev, tier) {
    ev.preventDefault();
    var zone = document.querySelector('.rank-row__icon[data-tier="'+tier+'"]');
    if (zone) zone.classList.remove('drag-over');
    if (ev.dataTransfer.files && ev.dataTransfer.files[0]) {
        readFileToUrl(tier, ev.dataTransfer.files[0]);
    } else {
        var url = ev.dataTransfer.getData('text/uri-list') || ev.dataTransfer.getData('text/plain');
        if (url) applyIconUrl(tier, url.trim());
    }
}
function readFileToUrl(tier, file) {
    var r = new FileReader();
    r.onload = function(e){ applyIconUrl(tier, e.target.result); };
    r.readAsDataURL(file);
}
function applyIconUrl(tier, url) {
    iconState[tier] = url;
    var img = document.getElementById('iconImg_' + tier);
    var ph  = document.getElementById('iconPh_' + tier);
    if (img) { img.src = url; img.style.display = url ? '' : 'none'; }
    if (ph)  ph.style.display = url ? 'none' : '';
    updatePreviewChips(); refreshJson();
}

function addRank() {
    var rows = document.querySelectorAll('.rank-row');
    var maxT = rows.length ? Math.max.apply(null, Array.from(rows).map(function(r){return parseInt(r.dataset.tier);})) : 0;
    var t = maxT + 1;
    var commonDivs = getGlobalDivCount();
    if (typeof commonDivs === 'undefined' || commonDivs === null || isNaN(commonDivs)) commonDivs = SAVED_DIVS || 4;
    document.getElementById('rankRows').appendChild(makeRankRow(t, 'Tier '+t, commonDivs, ''));
    updatePreviewChips(); updateRankCount(); buildPriceTable();
}
function removeRank(tier) {
    var row = document.querySelector('.rank-row[data-tier="'+tier+'"]');
    if (row) { row.remove(); delete iconState[tier]; updatePreviewChips(); updateRankCount(); buildPriceTable(); }
}
function updateRankCount() {
    var b = document.getElementById('rankCountBadge');
    if (b) b.textContent = document.querySelectorAll('.rank-row').length;
}

function updatePreviewChips() {
    var bar = document.getElementById('rankPreviewChips'); bar.innerHTML = '';
    document.querySelectorAll('.rank-row').forEach(function(row) {
        var tier = row.dataset.tier;
        var nameInp = row.querySelector('.rank-name');
        var name = (nameInp ? nameInp.value.trim() : '') || ('Tier ' + tier);
        var imgEl = document.getElementById('iconImg_'+tier);
        var hasSrc = imgEl && imgEl.src && imgEl.src !== window.location.href && imgEl.style.display !== 'none';
        var src = hasSrc ? imgEl.src : '';
        var chip = document.createElement('div'); chip.className = 'rpc';
        if (src) chip.innerHTML = '<img src="'+src+'" onerror="this.style.display=\'none\'">';
        chip.appendChild(document.createTextNode(name));
        bar.appendChild(chip);
    });
}

// ── Helpers ───────────────────────────────────────────────────────────────────
function getRanks() {
    var r = {};
    document.querySelectorAll('.rank-row').forEach(function(row){
        var t = parseInt(row.dataset.tier);
        r[t] = (row.querySelector('.rank-name')||{value:'Tier '+t}).value.trim() || 'Tier '+t;
    });
    return r;
}
function getTiers() {
    return Object.keys(getRanks()).map(Number).sort(function(a,b){return a-b;});
}
function getDivCount(tier) {
    var row = document.querySelector('.rank-row[data-tier="'+tier+'"]');
    if (!row) {
        var key = String(tier);
        if (SAVED_RANK_DIVS && Object.prototype.hasOwnProperty.call(SAVED_RANK_DIVS, key)) {
            var saved = parseInt(SAVED_RANK_DIVS[key], 10);
            return isNaN(saved) ? SAVED_DIVS : saved;
        }
        return SAVED_DIVS;
    }
    var sel = row.querySelector('.divs-sel');
    return sel ? parseInt(sel.value) : 0;
}
function getGlobalDivCount() {
    var counts = {};
    document.querySelectorAll('.divs-sel').forEach(function(s){
        var v = parseInt(s.value); counts[v]=(counts[v]||0)+1;
    });
    var best=0, bc=0;
    Object.keys(counts).forEach(function(v){ if(counts[v]>bc){ bc=counts[v]; best=parseInt(v); } });
    return best;
}

// ── Pricing table ─────────────────────────────────────────────────────────────
var DIV_DESC = { desc: {3:{1:'III',2:'II',3:'I'}, 4:{1:'IV',2:'III',3:'II',4:'I'}}, asc: {3:{1:'I',2:'II',3:'III'}, 4:{1:'I',2:'II',3:'III',4:'IV'}} };
function divLabel(d, total, order) {
    var map = (DIV_DESC[order]||DIV_DESC['desc'])[total];
    return map ? (map[d]||'D'+d) : 'D'+d;
}

function getPointThresholdsForPricing(tier) {
    // No-div ranks use LoL Master-style threshold pricing:
    // Rank < 100 LP, Rank < 200 LP, ... instead of current-point ranges.
    var existing = (EXIST_MAIN && EXIST_MAIN[tier]) ? EXIST_MAIN[tier] : {};
    var found = [];
    Object.keys(existing || {}).forEach(function(k){
        var m = String(k).match(/^lt[_-]?(\d+)$/i) || String(k).match(/^<(\d+)$/);
        if (m) {
            found.push(parseInt(m[1], 10));
        } else if (IS_LOL_CLASSIC && /^\d+$/.test(String(k)) && parseInt(k, 10) >= 100) {
            found.push(parseInt(k, 10));
        }
    });
    if (found.length) {
        found = Array.from(new Set(found)).filter(Boolean).sort(function(a,b){return a-b;});
        return found;
    }
    var thresholds = [];
    for (var i = 100; i <= 1500; i += 100) thresholds.push(i);
    return thresholds;
}
function getPointsLabelForPricing() {
    var el = document.getElementById('cfgPointsLabel');
    return (el && el.value ? el.value.trim() : '') || 'LP';
}
function buildPointPriceRows(tier, nextTier, ranks, order, tbody) {
    var label = getPointsLabelForPricing();
    var thresholds = getPointThresholdsForPricing(tier);
    thresholds.forEach(function(limit) {
        // LoL Classic's live calculators use numeric LP bucket keys (100, 200,
        // 300...). Generic rank forms use lt_100-style transition buckets.
        var key = IS_LOL_CLASSIC ? String(limit) : ('lt_' + limit);
        var suffix = IS_LOL_CLASSIC && FORM_TYPE === 'win'
            ? ' at ' + limit + ' ' + label + ' (price / win)'
            : ' < ' + limit + ' ' + label;
        var fromLabel = (ranks[tier] || ('Tier ' + tier)) + suffix;
        var ex = (EXIST_MAIN[tier] && EXIST_MAIN[tier][key])
            ? EXIST_MAIN[tier][key]
            : (EXIST_MAIN[tier] && EXIST_MAIN[tier]['lt_' + limit] ? EXIST_MAIN[tier]['lt_' + limit] : null);
        tbody.appendChild(makePriceRow(fromLabel, key, '', 0, tier, key, ex, 0, order, 'points_threshold'));
    });
}
function buildFlatTierPriceRow(tier, ranks, tbody) {
    var label = (ranks[tier] || ('Tier ' + tier))
        + (FORM_TYPE === 'win' ? ' (price / win)' : ' (flat price)');
    var ex = (EXIST_MAIN && EXIST_MAIN[tier]) ? EXIST_MAIN[tier] : null;
    tbody.appendChild(makePriceRow(label, '', '', 0, tier, '', ex, 0, getDivOrder(), 'flat'));
}

function buildPriceTable() {
    var tiers = getTiers(), ranks = getRanks(), order = getDivOrder();
    var tbody = document.getElementById('priceTableBody'); tbody.innerHTML = '';
    tiers.forEach(function(tier, ti) {
        var divs = getDivCount(tier), next = tiers[ti+1];
        if (divs === 0) {
            if (IS_LOL_CLASSIC && tier === 0) {
                buildFlatTierPriceRow(tier, ranks, tbody);
            } else {
                buildPointPriceRows(tier, next, ranks, order, tbody);
            }
        } else {
            for (var d=1; d<=divs; d++) {
                if (ti===tiers.length-1 && d===divs) continue;
                var ex2 = (EXIST_MAIN[tier] && EXIST_MAIN[tier][d]) ? EXIST_MAIN[tier][d] : null;
                var nextDivs = next ? getDivCount(next) : 0;
                var toLabel = (d < divs)
                    ? (ranks[tier]||'') + ' ' + divLabel(d+1, divs, order)
                    : (next ? (ranks[next]||'') + (nextDivs > 0 ? (' ' + divLabel(1, nextDivs, order)) : '') : '');
                if (!toLabel) continue;
                var fromLabel = (ranks[tier]||'Tier '+tier) + ' ' + divLabel(d, divs, order);
                tbody.appendChild(makePriceRow(fromLabel, d, toLabel, 0, tier, d, ex2, divs, order, 'division'));
            }
        }
    });
    refreshJson();
}
function makePriceRow(fromLabel, fromDiv, toLabel, toDiv, tier, d, existing, divs, order, kind) {
    var tr = document.createElement('tr');
    var cells = '<td>' + esc(fromLabel) + (toLabel ? ' → ' + esc(toLabel) : '') + '</td>';
    REGIONS.forEach(function(r) {
        var cents = existing ? (parseInt(existing[r])||0) : 0;
        var val = cents ? (cents / 100).toFixed(2) : '';
        cells += '<td><input type="number" class="bfe-price-inp" data-tier="'+tier+'" data-div="'+d+'" data-region="'+r+'" data-divs="'+divs+'" data-kind="'+(kind||'division')+'" value="'+val+'" min="0" step="0.01" placeholder="0.00" oninput="refreshJson()"></td>';
    });
    tr.innerHTML = cells;
    return tr;
}

function buildMainPricing() {
    var main = {};
    document.querySelectorAll('.bfe-price-inp').forEach(function(inp) {
        var tier=inp.dataset.tier, div=inp.dataset.div, r=inp.dataset.region, divs=parseInt(inp.dataset.divs);
        var euros = parseFloat(inp.value) || 0;
        var v = Math.round(euros * 100); // store as cents
        if (!v) return;
        if (!div||div==='0'){ if(!main[tier])main[tier]={}; main[tier][r]=v; }
        else { if(!main[tier])main[tier]={}; if(!main[tier][div])main[tier][div]={}; main[tier][div][r]=v; }
    });
    return main;
}

function fillTestPrices() {
    var base=5.00, i=0;
    document.querySelectorAll('.bfe-price-inp').forEach(function(inp){ if(!inp.value){ inp.value=(base+(i*0.50)).toFixed(2); i++; } });
    refreshJson();
}

// ── Parse options textarea ────────────────────────────────────────────────────
function parseOpts(taId) {
    var ta = document.getElementById(taId);
    if (!ta) return {};
    var lines = ta.value.trim().split('\n').map(function(s){return s.trim();}).filter(Boolean);
    var obj = {};
    lines.forEach(function(line){
        var eq = line.indexOf('=');
        if (eq>0) { obj[line.slice(0,eq).trim()] = line.slice(eq+1).trim(); }
        else { obj[line] = line; }
    });
    return obj;
}

// ── Form option rows ──────────────────────────────────────────────────────────
function readOptRows(id) {
    var result = {};
    var container = document.getElementById('optRows_' + id);
    if (!container) return result;
    container.querySelectorAll('.opt-dyn-row').forEach(function(row) {
        var k = (row.querySelector('.opt-row-key')||{value:''}).value.trim();
        var l = (row.querySelector('.opt-row-lbl')||{value:''}).value.trim();
        if (k) result[k] = l || k;
    });
    return result;
}

// ── Assemble JSON ─────────────────────────────────────────────────────────────
function assembleJson() {
    var ranks = getRanks();
    var rankDivs = {};
    document.querySelectorAll('.divs-sel').forEach(function(s){ rankDivs[s.dataset.tier]=parseInt(s.value); });
    var extra = buildExtras();
    var extraConfig = buildExtraConfig();
    // LP Gain discounts from Form Options rows (opt-row-disc column)
    var lpGain={};
    document.querySelectorAll('#optRows_LpGain .opt-dyn-row').forEach(function(row){
        var k=(row.querySelector('.opt-row-key')||{value:''}).value.trim();
        var d=row.querySelector('.opt-row-disc');
        var v=d?parseInt(d.value)||0:0;
        if(k) lpGain[k]=v/100;
    });
    // Current Points discounts from Form Options rows
    var startLp={};
    document.querySelectorAll('#optRows_Points .opt-dyn-row').forEach(function(row){
        var k=(row.querySelector('.opt-row-key')||{value:''}).value.trim();
        var d=row.querySelector('.opt-row-disc');
        var v=d?parseInt(d.value)||0:0;
        if(k) startLp[k]=v/100;
    });
    var rankIcons={};
    document.querySelectorAll('.icon-url-inp').forEach(function(i){ if(i.value.trim()) rankIcons[i.dataset.tier]=i.value.trim(); });

    // Preserve fields the visual editor does not manage (for example apex_from,
    // specialised limits and imported metadata) instead of deleting them on save.
    var j = JSON.parse(JSON.stringify(EXIST_JSON || {}));
    Object.assign(j, {
        completion_time: parseInt(document.getElementById('cfgCompTime').value)||4,
        points_label:    document.getElementById('cfgPointsLabel').value.trim()||'LP',
        points_min:      0,
        points_step:     1,
        main:            buildMainPricing(),
        extra:           extra,
        rank_names:      ranks,
        division_count:  getGlobalDivCount(),
        server_options:  readOptRows('Server'),
        platform_options:readOptRows('Platform'),
        queue_options:   (function(){
            var fromRows = readOptRows('Queue');
            if (Object.keys(fromRows).length) return fromRows;
            var q={};
            document.querySelectorAll('#queueTypeRows .queue-dyn-row').forEach(function(row){
                var k=(row.querySelector('.queue-key')||{value:''}).value.trim();
                var l=(row.querySelector('.queue-lbl')||{value:''}).value.trim();
                if(k) q[k]=l||k;
            });
            return q;
        })(),
        lp_gain_options: (function(){
            var fromRows = readOptRows('LpGain');
            if (Object.keys(fromRows).length) return fromRows;
            var g={};
            document.querySelectorAll('#lpGainRows .lp-dyn-row').forEach(function(row){
                var k=(row.querySelector('.lp-gain-key')||{value:''}).value.trim();
                var l=(row.querySelector('.lp-gain-lbl')||{value:''}).value.trim();
                if(k) g[k]=l||k;
            });
            return g;
        })(),
        points_options:  (function(){
            var fromRows = readOptRows('Points');
            if (Object.keys(fromRows).length) return fromRows;
            var p={};
            document.querySelectorAll('#pointsDiscountRows .pts-dyn-row').forEach(function(row){
                var k=(row.querySelector('.pts-key')||{value:''}).value.trim();
                var l=(row.querySelector('.pts-lbl')||{value:''}).value.trim();
                if(k) p[k]=l||k;
            });
            return p;
        })(),
        form_config: {
            show_server:         !!(document.getElementById('optServer')||{}).checked,
            show_platform:       !!(document.getElementById('optPlatform')||{}).checked,
            show_current_points: !!(document.getElementById('optPoints')||{}).checked,
            show_lp_gain:        !!(document.getElementById('optLpGain')||{}).checked,
            show_queue_type:     !!(document.getElementById('optQueue')||{}).checked,
            show_solo_duo:       !!(document.getElementById('optSoloDuo')||{}).checked,
            div_order:           getDivOrder(),
            ranks:               ranks,
            rank_divs:           rankDivs,
            divisions:           getGlobalDivCount(),
        }
    });
    if (IS_LOL_CLASSIC) {
        j.apex_from = 7;
    }
    if (Object.keys(rankIcons).length) j.rank_icons = rankIcons;
    if (Object.keys(lpGain).some(function(k){return lpGain[k]!==0;})) j.lp_gain = lpGain;
    if (Object.keys(startLp).some(function(k){return startLp[k]!==0;})) j.start_lp = startLp;
    j.extra_config = buildExtraConfig();
    // Save selection items (heroes/roles/agents)
    var selItems = buildSelectionItems();
    if (selItems.length) {
        // Find the heroes/roles key
        var heroKey = 'is_champions_roles';
        document.querySelectorAll('.extra-card').forEach(function(card) {
            var lbl = ((card.querySelector('.extra-lbl-inp')||{value:''}).value||'').toLowerCase();
            if (lbl.indexOf('hero')>=0||lbl.indexOf('role')>=0||lbl.indexOf('champion')>=0||lbl.indexOf('agent')>=0) {
                heroKey = card.dataset.key;
            }
        });
        j.selection_items = {}; j.selection_items[heroKey] = selItems;
    }
    return j;
}

function buildJson() {
    var j = assembleJson();
    document.getElementById('hiddenPricingJson').value = JSON.stringify(j);
}

function refreshJson() {
    var el = document.getElementById('bfeJsonPreview');
    if (el) el.textContent = JSON.stringify(assembleJson(), null, 2);
}

// ── Raw JSON editor ───────────────────────────────────────────────────────────
function toggleRawJson() {
    var rw = document.getElementById('jsonRawWrap'), pv = document.getElementById('jsonPreviewWrap');
    var btn = document.getElementById('rawToggleBtn');
    var open = rw.style.display !== 'none';
    rw.style.display = open ? 'none' : '';
    pv.style.display = open ? '' : 'none';
    btn.textContent  = open ? 'Raw ↓' : 'Raw ↑';
    if (!open) document.getElementById('rawJsonTA').value = JSON.stringify(assembleJson(), null, 2);
}
function importRawJson() {
    try {
        var d = JSON.parse(document.getElementById('rawJsonTA').value);
        EXIST_MAIN = d.main || {};
        buildPriceTable();
        document.getElementById('jsonStatus').className = 'ok';
        document.getElementById('jsonStatus').textContent = '✓ Imported successfully';
    } catch(e) {
        document.getElementById('jsonStatus').className = 'err';
        document.getElementById('jsonStatus').textContent = '✗ ' + e.message;
    }
}

// ── Extra Options management ─────────────────────────────────────────────────
var ICON_OPTIONS = [
    'fa-bolt','fa-users','fa-video','fa-user','fa-eye-slash','fa-chess-knight',
    'fa-trophy','fa-headset','fa-star','fa-shield-halved','fa-crown','fa-fire',
    'fa-rocket','fa-gamepad','fa-clock','fa-chart-line','fa-wand-magic-sparkles',
    'fa-lock','fa-comment','fa-person-running',
];
var _extraCounter = 0;

function addExtraOption() {
    _extraCounter++;
    var key = 'custom_extra_' + _extraCounter;
    var grid = document.getElementById('extrasGrid');
    var div = document.createElement('div');
    div.className = 'extra-card'; div.dataset.key = key;
    div.innerHTML =
        '<div class="extra-card__ico" id="extraIco_'+key+'" onclick="cycleExtraIcon(\'' + key + '\')" style="cursor:pointer" title="Click to change icon"><i class="fa-solid fa-star"></i></div>' +
        '<div class="extra-card__body">' +
            '<input type="text" class="extra-lbl-inp" data-key="'+key+'" value="New Option"' +
                ' style="background:transparent;border:none;border-bottom:1px solid rgba(255,255,255,.08);color:#e2e8f0;font-size:11.5px;font-weight:800;width:100%;padding:1px 0;font-family:inherit"' +
                ' onfocus="this.style.borderColor=\'rgba(92,74,227,.5)\'" onblur="this.style.borderColor=\'rgba(255,255,255,.08)\'">' +
            '<div class="extra-card__controls">' +
                '<label class="lb-check-wrap">' +
                    '<input type="checkbox" class="extra-cb" data-key="'+key+'" checked>' +
                    '<div class="lb-check-box"></div>' +
                    '<span class="lb-check-label" style="font-size:11px">On</span>' +
                '</label>' +
                '<input type="number" class="xpci extra-pct" data-key="'+key+'" value="10" min="0" max="300" step="1">' +
                '<span class="pct-lbl">%</span>' +
                '<button type="button" class="rank-row__del ms-1" style="width:22px;height:22px;font-size:9px" onclick="removeExtra(\'' + key + '\')" title="Remove"><i class="fa-solid fa-xmark"></i></button>' +
            '</div>' +
            '<div style="display:flex;align-items:center;gap:4px;margin-top:5px">' +
                '<span style="font-size:9.5px;color:rgba(255,255,255,.3);font-weight:800;text-transform:uppercase;letter-spacing:.06em">Visible:</span>' +
                '<label style="display:inline-flex;align-items:center;gap:3px;padding:2px 7px;border-radius:5px;border:1px solid rgba(92,74,227,.3);background:rgba(92,74,227,.15);cursor:pointer;font-size:10px;font-weight:700;color:#a5b4fc">' +
                    '<input type="radio" name="extra_cls_'+key+'" class="extra-cls" data-key="'+key+'" value="" checked style="display:none">Both' +
                '</label>' +
                '<label style="display:inline-flex;align-items:center;gap:3px;padding:2px 7px;border-radius:5px;border:1px solid rgba(255,255,255,.08);background:rgba(255,255,255,.03);cursor:pointer;font-size:10px;font-weight:700;color:rgba(255,255,255,.4)">' +
                    '<input type="radio" name="extra_cls_'+key+'" class="extra-cls" data-key="'+key+'" value="solo-option" style="display:none">Solo only' +
                '</label>' +
                '<label style="display:inline-flex;align-items:center;gap:3px;padding:2px 7px;border-radius:5px;border:1px solid rgba(255,255,255,.08);background:rgba(255,255,255,.03);cursor:pointer;font-size:10px;font-weight:700;color:rgba(255,255,255,.4)">' +
                    '<input type="radio" name="extra_cls_'+key+'" class="extra-cls" data-key="'+key+'" value="duo-option" style="display:none">Duo only' +
                '</label>' +
            '</div>' +
        '</div>';
    grid.appendChild(div);
    refreshJson();
    // Focus the label input
    var inp = div.querySelector('.extra-lbl-inp');
    if (inp) { inp.focus(); inp.select(); }
}

function removeExtra(key) {
    var card = document.querySelector('.extra-card[data-key="'+key+'"]');
    if (card) { card.remove(); refreshJson(); }
}

var _extraIconIdx = {};
function cycleExtraIcon(key) {
    if (!_extraIconIdx[key]) _extraIconIdx[key] = 0;
    _extraIconIdx[key] = (_extraIconIdx[key] + 1) % ICON_OPTIONS.length;
    var ico = document.getElementById('extraIco_' + key);
    if (ico) {
        var iconClass = ICON_OPTIONS[_extraIconIdx[key]];
        ico.innerHTML = '<i class="fa-solid ' + iconClass + '"></i>';
    }
    refreshJson();
}

function buildExtras() {
    var extra = {};
    document.querySelectorAll('.extra-cb').forEach(function(cb) {
        var k = cb.dataset.key;
        var pct = parseInt((document.querySelector('.extra-pct[data-key="'+k+'"]') || {value:0}).value) || 0;
        if (cb.checked && pct > 0) extra[k] = pct / 100;
    });
    return extra;
}

function buildExtraConfig() {
    var cfg = {};
    document.querySelectorAll('.extra-card').forEach(function(card) {
        var key    = card.dataset.key;
        var lblInp = card.querySelector('.extra-lbl-inp');
        var cb     = card.querySelector('.extra-cb');
        var pctInp = card.querySelector('.extra-pct');
        var icoEl  = card.querySelector('.extra-card__ico i');
        var clsInp = card.querySelector('.extra-cls:checked');
        var iconClass = icoEl ? (icoEl.className.replace('fa-solid ','').trim()) : 'fa-star';
        cfg[key] = {
            label:   lblInp ? lblInp.value.trim() : key,
            icon:    iconClass,
            def:     parseInt(pctInp ? pctInp.value : '10') / 100,
            enabled: cb ? cb.checked : false,
            class:   clsInp ? clsInp.value : '',
        };
    });
    return cfg;
}

// ── Heroes/Roles/Agents selection rows ───────────────────────────────────────
function addSelectionRow() {
    var rows = document.getElementById('selectionRows');
    var div = document.createElement('div');
    div.className = 'sel-row';
    div.style.cssText = 'display:grid;grid-template-columns:1fr 40px 26px;gap:6px;align-items:center';

    var nameInp = document.createElement('input');
    nameInp.type = 'text'; nameInp.className = 'sel-name form-control form-control-sm';
    nameInp.placeholder = 'Hero / Role name';

    var iconWrap = document.createElement('div');
    iconWrap.style.position = 'relative';

    var previewImg = document.createElement('img');
    previewImg.className = 'sel-icon-preview';
    previewImg.src = '';
    previewImg.style.cssText = 'width:28px;height:28px;border-radius:6px;border:1px solid rgba(255,255,255,.1);background:rgba(255,255,255,.04);object-fit:contain;cursor:pointer;display:none';
    previewImg.onerror = function(){ this.style.display='none'; };

    var emptyDiv = document.createElement('div');
    emptyDiv.className = 'sel-icon-empty';
    emptyDiv.style.cssText = 'width:28px;height:28px;border-radius:6px;border:1.5px dashed rgba(255,255,255,.15);background:rgba(255,255,255,.03);display:flex;align-items:center;justify-content:center;cursor:pointer;font-size:10px;color:rgba(255,255,255,.2)';
    emptyDiv.innerHTML = '<i class="fa-solid fa-image"></i>';
    emptyDiv.onclick = function(){ triggerSelIcon(this); };

    var urlInp = document.createElement('input');
    urlInp.type = 'text'; urlInp.className = 'sel-icon-url';
    urlInp.placeholder = 'icon URL'; urlInp.style.display = 'none';
    urlInp.oninput = function(){ updateSelIconPreview(this); };

    iconWrap.appendChild(previewImg);
    iconWrap.appendChild(emptyDiv);
    iconWrap.appendChild(urlInp);

    var delBtn = document.createElement('button');
    delBtn.type = 'button'; delBtn.className = 'rank-row__del';
    delBtn.style.cssText = 'width:26px;height:26px;font-size:10px';
    delBtn.innerHTML = '<i class="fa-solid fa-xmark"></i>';
    delBtn.onclick = function(){ this.closest('.sel-row').remove(); };

    div.appendChild(nameInp);
    div.appendChild(iconWrap);
    div.appendChild(delBtn);
    rows.appendChild(div);
    nameInp.focus();
}

function triggerSelIcon(emptyDiv) {
    // Toggle URL input visibility
    var wrap = emptyDiv.parentElement;
    var urlInp = wrap.querySelector('.sel-icon-url');
    if (urlInp) {
        urlInp.style.display = urlInp.style.display === 'none' ? 'block' : 'none';
        if (urlInp.style.display !== 'none') {
            urlInp.style.cssText = 'display:block;position:absolute;bottom:-34px;left:0;width:220px;z-index:10;background:#1a1d2e;border:1px solid rgba(92,74,227,.4);border-radius:6px;padding:4px 7px;color:#e2e8f0;font-size:10px';
            urlInp.focus();
        }
    }
    // Also allow file pick
    var filePick = document.createElement('input');
    filePick.type = 'file'; filePick.accept = 'image/*';
    filePick.onchange = function() {
        if (!this.files[0]) return;
        var reader = new FileReader();
        reader.onload = function(e) {
            updateSelIconPreviewByWrap(wrap, e.target.result);
        };
        reader.readAsDataURL(this.files[0]);
    };
    filePick.click();
}

function updateSelIconPreview(urlInput) {
    updateSelIconPreviewByWrap(urlInput.parentElement, urlInput.value.trim());
}

function updateSelIconPreviewByWrap(wrap, url) {
    var preview = wrap.querySelector('.sel-icon-preview');
    var empty   = wrap.querySelector('.sel-icon-empty');
    var urlInp  = wrap.querySelector('.sel-icon-url');
    if (preview) { preview.src = url; preview.style.display = url ? '' : 'none'; }
    if (empty)   { empty.style.display = url ? 'none' : 'flex'; }
    if (urlInp)  { urlInp.value = url; }
}

function buildSelectionItems() {
    var rows = document.querySelectorAll('#selectionRows .sel-row');
    var items = [];
    rows.forEach(function(row) {
        var name = (row.querySelector('.sel-name') || {value:''}).value.trim();
        var icon = (row.querySelector('.sel-icon-url') || {value:''}).value.trim();
        if (!icon) {
            var img = row.querySelector('.sel-icon-preview');
            if (img && img.src && img.style.display !== 'none') icon = img.src;
        }
        if (name) items.push(icon ? {name:name, icon:icon} : {name:name});
    });
    return items;
}

// ── Init ──────────────────────────────────────────────────────────────────────
document.addEventListener('DOMContentLoaded', function() {
    buildRankRows(SAVED_RANKS, SAVED_DIVS, SAVED_ICONS, SAVED_RANK_DIVS);
    buildPriceTable();

    // ── Dynamic row helpers ────────────────────────────────────────────────────
    window.addOptRow = function(id, hasDiscount) {
        var container = document.getElementById('optRows_' + id);
        if (!container) return;
        var cols = hasDiscount ? '90px 1fr 58px 14px 22px' : '90px 1fr 22px';
        var row = document.createElement('div');
        row.className = 'opt-dyn-row';
        row.style.cssText = 'display:grid;grid-template-columns:'+cols+';gap:5px;align-items:center';

        var k = document.createElement('input');
        k.type='text'; k.className='form-control form-control-sm opt-row-key';
        k.placeholder='value'; k.style.cssText='font-family:monospace;font-size:11px';
        row.appendChild(k);

        var l = document.createElement('input');
        l.type='text'; l.className='form-control form-control-sm opt-row-lbl';
        l.placeholder='Label';
        row.appendChild(l);

        if (hasDiscount) {
            var d = document.createElement('input');
            d.type='number'; d.className='bfe-mini-input opt-row-disc';
            d.value='0'; d.min='-100'; d.max='100'; d.step='1';
            d.style.textAlign='right'; d.title='Discount %';
            row.appendChild(d);
            var pct = document.createElement('span');
            pct.style.cssText='font-size:10px;color:rgba(255,255,255,.25)';
            pct.textContent='%';
            row.appendChild(pct);
        }

        var del = document.createElement('button');
        del.type='button'; del.className='rank-row__del';
        del.style.cssText='width:22px;height:22px;font-size:9px';
        del.innerHTML='<i class="fa-solid fa-xmark"></i>';
        del.onclick=function(){ this.closest('.opt-dyn-row').remove(); refreshJson(); };
        row.appendChild(del);

        container.appendChild(row);
        k.focus();
        refreshJson();
    };

    function makeInputRow(containerClass, rowClass, w1, ph1, w2, ph2, hasVal, valClass) {
        var row = document.createElement('div');
        row.className = rowClass + ' d-flex align-items-center gap-2 mb-1';
        var k = document.createElement('input');
        k.type='text'; k.className='form-control form-control-sm ' + (rowClass.replace('-dyn-row','') + '-key').trim();
        k.placeholder=ph1; k.style.width=w1;
        var l = document.createElement('input');
        l.type='text'; l.className='form-control form-control-sm ' + (rowClass.replace('-dyn-row','') + '-lbl').trim();
        l.placeholder=ph2; l.style.flex='1';
        row.appendChild(k); row.appendChild(l);
        if (hasVal) {
            var v = document.createElement('input');
            v.type='number'; v.className='bfe-mini-input ' + valClass;
            v.value='0'; v.min='-100'; v.max='100'; v.step='1'; v.style.cssText='width:62px;text-align:right';
            var pct = document.createElement('span');
            pct.style.cssText='font-size:10px;color:rgba(255,255,255,.3)'; pct.textContent='%';
            row.appendChild(v); row.appendChild(pct);
        }
        var del = document.createElement('button');
        del.type='button'; del.className='rank-row__del'; del.style.cssText='width:24px;height:24px;font-size:9px';
        del.innerHTML='<i class="fa-solid fa-xmark"></i>';
        del.onclick=function(){ this.closest('.'+rowClass).remove(); refreshJson(); };
        row.appendChild(del);
        return row;
    }


    // Visible (Solo/Duo) radio - delegate on the RADIO INPUT directly
    // The radios are hidden but clicking their label fires 'change' on the input
    document.addEventListener('change', function(e) {
        if (!e.target || !e.target.classList.contains('extra-cls')) return;
        var radio = e.target;
        var card  = radio.closest('.extra-card');
        if (!card) return;
        syncExtraClsStyles(card);
        refreshJson();
    });
    // Track "already checked" to allow deselecting via a second click
    document.addEventListener('mousedown', function(e) {
        var lbl = e.target.closest('label');
        if (!lbl) return;
        var radio = lbl.querySelector('input.extra-cls');
        if (!radio || !radio.checked) return;
        // Mark: this radio IS checked before the click toggles it
        lbl.dataset.deselecting = '1';
    });
    document.addEventListener('click', function(e) {
        var lbl = e.target.closest('label');
        if (!lbl || lbl.dataset.deselecting !== '1') return;
        lbl.dataset.deselecting = '0';
        var radio = lbl.querySelector('input.extra-cls');
        if (!radio || radio.value === '') return; // 'both' can't be deselected
        var card = radio.closest('.extra-card');
        if (!card) return;
        // Reset to 'both'
        card.querySelectorAll('.extra-cls').forEach(function(r){ r.checked = false; });
        var bothR = card.querySelector('.extra-cls[value=""]');
        if (bothR) bothR.checked = true;
        syncExtraClsStyles(card);
        refreshJson();
    });

    function syncExtraClsStyles(card) {
        card.querySelectorAll('.extra-cls').forEach(function(r) {
            var lbl = r.closest('label');
            if (!lbl) return;
            if (r.checked) {
                lbl.style.color='#a5b4fc'; lbl.style.background='rgba(92,74,227,.15)'; lbl.style.borderColor='rgba(92,74,227,.3)';
            } else {
                lbl.style.color='rgba(255,255,255,.4)'; lbl.style.background='rgba(255,255,255,.03)'; lbl.style.borderColor='rgba(255,255,255,.08)';
            }
        });
    }
    // Init: style existing checked radios
    document.querySelectorAll('.extra-card').forEach(function(card) {
        syncExtraClsStyles(card);
    });

        document.addEventListener('input', function(e) {
        if (e.target.classList.contains('bfe-price-inp') ||
            e.target.id === 'cfgCompTime' ||
            e.target.id === 'cfgPointsLabel') refreshJson();
    });
    // Initial JSON render
    refreshJson();
});
</script>
