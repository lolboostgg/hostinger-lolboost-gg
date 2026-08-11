<?php
// ── Local fallbacks ───────────────────────────────────────────────────────
if (!defined('ADMN_URL')) {
    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    define('ADMN_URL', $scheme . '://' . ($_SERVER['HTTP_HOST'] ?? '') . '/admin-area');
}
if (!defined('AJAX_URL')) {
    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    define('AJAX_URL', $scheme . '://' . ($_SERVER['HTTP_HOST'] ?? '') . '/ajax');
}

// ── DB helper ─────────────────────────────────────────────────────────────
function bget(string $key, string $default = ''): string
{
    global $db;
    static $c = null;
    if ($c === null) {
        $c = [];
        try {
            $rows = $db->run("SELECT `key`, `value` FROM `site_settings` WHERE `key` LIKE 'banner_%'") ?: [];
            foreach ($rows as $r) { $c[$r['key']] = (string)$r['value']; }
        } catch (\Throwable $e) {}
    }
    return array_key_exists($key, $c) ? $c[$key] : $default;
}

// ── Load all settings ─────────────────────────────────────────────────────
$B = [
    'type'                    => bget('banner_type', 'progames'),
    // Pro Games
    'pg_badge_text'           => bget('banner_pg_badge_text',           'Just Launched'),
    'pg_badge_color'          => bget('banner_pg_badge_color',          '#8ea5ff'),
    'pg_badge_border'         => bget('banner_pg_badge_border',         'rgba(142,165,255,0.34)'),
    'pg_icon_url'             => bget('banner_pg_icon_url',             ''),
    'pg_title'                => bget('banner_pg_title',                '{ACCENT}Every Game, Every Deal{/ACCENT} — All In One Place'),
    'pg_title_color'          => bget('banner_pg_title_color',          '#ffffff'),
    'pg_accent_color'         => bget('banner_pg_accent_color',         '#8ea5ff'),
    'pg_bg_color_from'        => bget('banner_pg_bg_color_from',        '#0d1021'),
    'pg_bg_color_to'          => bget('banner_pg_bg_color_to',          '#171c3d'),
    'pg_bg_image'             => bget('banner_pg_bg_image',             ''),
    'pg_bg_image_opacity'     => bget('banner_pg_bg_image_opacity',     '0.18'),
    'pg_pill_1'               => bget('banner_pg_pill_1',               '50+ Games'),
    'pg_pill_2'               => bget('banner_pg_pill_2',               'Instant Delivery'),
    'pg_pill_3'               => bget('banner_pg_pill_3',               '24/7 Support'),
    'pg_pill_4'               => bget('banner_pg_pill_4',               ''),
    'pg_pill_5'               => bget('banner_pg_pill_5',               ''),
    'pg_cta_text'             => bget('banner_pg_cta_text',             'Explore Now'),
    'pg_cta_link'             => bget('banner_pg_cta_link',             '/'),
    'pg_cta_bg_from'          => bget('banner_pg_cta_bg_from',          '#7c83ff'),
    'pg_cta_bg_to'            => bget('banner_pg_cta_bg_to',            '#4f46e5'),
    'pg_cta_color'            => bget('banner_pg_cta_color',            '#ffffff'),
    'pg_mob_title'            => bget('banner_pg_mob_title',            'Just Launched'),
    'pg_mob_sub'              => bget('banner_pg_mob_sub',              'Every game, every deal — explore now'),
    'pg_mob_title_color'      => bget('banner_pg_mob_title_color',      '#ffffff'),
    'pg_mob_sub_color'        => bget('banner_pg_mob_sub_color',        'rgba(255,255,255,0.45)'),
    'pg_mob_cta_text'         => bget('banner_pg_mob_cta_text',         'Explore Now'),
    'pg_mob_cta_link'         => bget('banner_pg_mob_cta_link',         '/'),
    // Sale
    'sale_headline'           => bget('banner_sale_headline',           'NEW SEASON {ACCENT}SALE{/ACCENT}'),
    'sale_title_color'        => bget('banner_sale_title_color',        '#ffffff'),
    'sale_accent_from'        => bget('banner_sale_accent_from',        '#f59e0b'),
    'sale_accent_to'          => bget('banner_sale_accent_to',          '#ef4444'),
    'sale_sub'                => bget('banner_sale_sub',                '◈ Instant Booking · ◈ Diamond+ Boosters · ◈ 100% Safe'),
    'sale_sub_color'          => bget('banner_sale_sub_color',          'rgba(255,255,255,0.45)'),
    'sale_bg_color_from'      => bget('banner_sale_bg_color_from',      '#0b0f1e'),
    'sale_bg_color_to'        => bget('banner_sale_bg_color_to',        '#0b0f1e'),
    'sale_bg_image_left'      => bget('banner_sale_bg_image_left',      ''),
    'sale_bg_image_right'     => bget('banner_sale_bg_image_right',     ''),
    'sale_bg_image_opacity'   => bget('banner_sale_bg_image_opacity',   '0.50'),
    'sale_bg_left_pos_x_desktop'  => bget('banner_sale_bg_left_pos_x_desktop',  '50'),
    'sale_bg_left_pos_y_desktop'  => bget('banner_sale_bg_left_pos_y_desktop',  '50'),
    'sale_bg_right_pos_x_desktop' => bget('banner_sale_bg_right_pos_x_desktop', '50'),
    'sale_bg_right_pos_y_desktop' => bget('banner_sale_bg_right_pos_y_desktop', '50'),
    'sale_bg_left_pos_x_mobile'   => bget('banner_sale_bg_left_pos_x_mobile',   '50'),
    'sale_bg_left_pos_y_mobile'   => bget('banner_sale_bg_left_pos_y_mobile',   '50'),
    'sale_bg_right_pos_x_mobile'  => bget('banner_sale_bg_right_pos_x_mobile',  '50'),
    'sale_bg_right_pos_y_mobile'  => bget('banner_sale_bg_right_pos_y_mobile',  '50'),
    'sale_ends_at'            => bget('banner_sale_ends_at',            ''),
    'sale_countdown_label'    => bget('banner_sale_countdown_label',    'ENDS IN'),
    'sale_countdown_color'    => bget('banner_sale_countdown_color',    '#ffffff'),
    'sale_countdown_unit_bg'  => bget('banner_sale_countdown_unit_bg',  'rgba(245,158,11,0.08)'),
    'sale_countdown_border'   => bget('banner_sale_countdown_border',   'rgba(245,158,11,0.30)'),
    'sale_cta_1_text'         => bget('banner_sale_cta_1_text',         'TFT Boost'),
    'sale_cta_1_link'         => bget('banner_sale_cta_1_link',         '/tft/rank-boost'),
    'sale_cta_1_icon'         => bget('banner_sale_cta_1_icon',         ''),
    'sale_cta_1_bg_from'      => bget('banner_sale_cta_1_bg_from',      '#d97706'),
    'sale_cta_1_bg_to'        => bget('banner_sale_cta_1_bg_to',        '#b45309'),
    'sale_cta_2_text'         => bget('banner_sale_cta_2_text',         'Val Boost'),
    'sale_cta_2_link'         => bget('banner_sale_cta_2_link',         '/val/rank-boost'),
    'sale_cta_2_icon'         => bget('banner_sale_cta_2_icon',         ''),
    'sale_cta_2_bg_from'      => bget('banner_sale_cta_2_bg_from',      '#dc2626'),
    'sale_cta_2_bg_to'        => bget('banner_sale_cta_2_bg_to',        '#991b1b'),
    'sale_mob_pct'            => bget('banner_sale_mob_pct',            '20% OFF'),
    'sale_mob_pct_color_from' => bget('banner_sale_mob_pct_color_from', '#f59e0b'),
    'sale_mob_pct_color_to'   => bget('banner_sale_mob_pct_color_to',   '#ef4444'),
    'sale_mob_label'          => bget('banner_sale_mob_label',          'New Season Sale'),
    'sale_mob_label_color'    => bget('banner_sale_mob_label_color',    'rgba(255,255,255,0.45)'),
];

function toHex(string $c, string $fb = '#000000'): string {
    $c = trim($c);
    return preg_match('/^#[0-9a-fA-F]{3,8}$/', $c) ? $c : $fb;
}
?>
<?= $this->layout('admin/layouts/main', [
    'meta' => [
        'title'       => 'Banner Settings — Admin',
        'h1'          => 'Banner Settings',
        'description' => 'Manage the top announcement banner — texts, colors, images, countdown.',
    ],
]) ?>

<style>
.bs-section{border-radius:10px;border:1px solid rgba(255,255,255,.07);background:var(--bs-body-bg);margin-bottom:18px;}
.bs-head{padding:12px 16px 10px;border-bottom:1px solid rgba(255,255,255,.07);display:flex;align-items:center;gap:8px;font-weight:800;font-size:11px;text-transform:uppercase;letter-spacing:.08em;color:rgba(255,255,255,.45);}
.bs-head i{font-size:13px;color:rgba(255,255,255,.55);}
.bs-body{padding:16px;}
.bs-grid{display:grid;gap:12px;}
.g2{grid-template-columns:1fr 1fr;}
.g3{grid-template-columns:1fr 1fr 1fr;}
.g4{grid-template-columns:1fr 1fr 1fr 1fr;}
@media(max-width:900px){.g3,.g4{grid-template-columns:1fr 1fr;}}
@media(max-width:600px){.g2,.g3,.g4{grid-template-columns:1fr;}}
.type-pill{display:inline-flex;align-items:center;gap:8px;padding:9px 18px;border-radius:999px;border:1px solid rgba(255,255,255,.12);background:transparent;color:rgba(255,255,255,.55);font-size:13px;font-weight:700;cursor:pointer;transition:.15s;}
.type-pill:hover{border-color:rgba(255,255,255,.3);color:#fff;}
.type-pill.active{background:rgba(99,102,241,.18);border-color:rgba(99,102,241,.55);color:#a5b4fc;}
.cf{display:flex;align-items:center;gap:7px;}
.cf input[type=color]{width:34px;height:34px;border-radius:8px;border:1px solid rgba(255,255,255,.15);padding:2px;background:transparent;cursor:pointer;flex-shrink:0;}
.cf input[type=text]{flex:1;}
.img-row{display:flex;align-items:center;gap:8px;flex-wrap:wrap;margin-top:6px;}
.img-prev{width:72px;height:40px;border-radius:7px;border:1px solid rgba(255,255,255,.1);object-fit:cover;display:none;}
.img-prev.show{display:block;}
.img-rm{font-size:11px;color:#ef4444;background:none;border:none;cursor:pointer;padding:0;}

.img-drop{position:relative;display:flex;align-items:center;gap:10px;flex-wrap:wrap;width:100%;min-height:74px;padding:12px;border:1px dashed rgba(124,155,255,.28);border-radius:12px;background:rgba(124,155,255,.045);transition:.15s ease;cursor:pointer;}
.img-drop:hover,.img-drop.is-dragover,.img-drop.is-active{border-color:rgba(124,155,255,.70);background:rgba(124,155,255,.10);box-shadow:0 0 0 3px rgba(124,155,255,.06);}
.img-drop input[type=file]{position:absolute;inset:0;opacity:0;cursor:pointer;width:100%;height:100%;z-index:2;}
.img-drop-text{display:flex;flex-direction:column;gap:2px;min-width:180px;pointer-events:none;}
.img-drop-text strong{font-size:12px;font-weight:900;color:rgba(255,255,255,.78);}
.img-drop-text span{font-size:11px;color:rgba(255,255,255,.38);}
.img-drop .img-prev{display:block;width:84px;height:48px;opacity:.55;background:rgba(0,0,0,.22);}
.img-drop .img-prev.show{opacity:1;}
.img-drop .img-rm{position:relative;z-index:3;margin-left:auto;padding:6px 8px;border-radius:8px;background:rgba(239,68,68,.10);}
.icon-control{display:grid;grid-template-columns:1fr 1fr;gap:12px;align-items:start;}
.icon-fa-box{padding:12px;border-radius:12px;border:1px solid rgba(255,255,255,.08);background:rgba(255,255,255,.025);}
.icon-fa-preview{width:38px;height:38px;border-radius:10px;display:inline-flex;align-items:center;justify-content:center;background:rgba(245,158,11,.10);border:1px solid rgba(245,158,11,.25);color:#f59e0b;margin-right:8px;}
.icon-fa-help{font-size:11px;color:rgba(255,255,255,.38);margin-top:7px;line-height:1.35;}
.bg-position-card{margin-top:14px;padding:12px;border-radius:12px;border:1px solid rgba(255,255,255,.08);background:rgba(255,255,255,.025);}
.bg-position-title{display:flex;align-items:center;justify-content:space-between;gap:10px;margin-bottom:10px;font-size:11px;font-weight:900;letter-spacing:.08em;text-transform:uppercase;color:rgba(255,255,255,.55);}
.bg-position-title span{font-size:10px;color:rgba(255,255,255,.34);text-transform:none;letter-spacing:0;font-weight:700;}
.bg-pos-grid{display:grid;grid-template-columns:1fr 1fr;gap:12px;}
.bg-pos-control label{display:flex;align-items:center;justify-content:space-between;font-size:11px;font-weight:800;color:rgba(255,255,255,.62);margin-bottom:5px;}
.bg-pos-control label b{font-size:11px;color:rgba(255,255,255,.42);}
.bg-pos-control input[type=range]{width:100%;}
@media(max-width:720px){.bg-pos-grid{grid-template-columns:1fr;}}
.cf{position:relative;}
.cf input[type=color]{opacity:0;position:absolute;width:1px;height:1px;pointer-events:none;}
.color-swatch-btn{width:36px;height:36px;border-radius:10px;border:1px solid rgba(255,255,255,.16);box-shadow:inset 0 1px 0 rgba(255,255,255,.12);cursor:pointer;flex-shrink:0;background:#000;}
.color-pop{position:absolute;left:0;top:42px;z-index:30;width:236px;padding:12px;border-radius:14px;border:1px solid rgba(124,155,255,.22);background:#111827;box-shadow:0 22px 48px rgba(0,0,0,.45);display:none;}
.color-pop.is-open{display:block;}
.color-pop-title{font-size:10px;font-weight:900;letter-spacing:.12em;text-transform:uppercase;color:rgba(255,255,255,.45);margin-bottom:8px;}
.color-presets{display:grid;grid-template-columns:repeat(8,1fr);gap:6px;margin-bottom:10px;}
.color-preset{width:21px;height:21px;border-radius:7px;border:1px solid rgba(255,255,255,.20);cursor:pointer;}
.color-native-row{display:flex;gap:8px;align-items:center;}
.color-native-row input[type=color]{opacity:1;position:static;pointer-events:auto;width:42px;height:34px;border-radius:9px;}
@media(max-width:720px){.icon-control{grid-template-columns:1fr;}.color-pop{left:auto;right:0;}}

.rr{display:flex;align-items:center;gap:8px;}
.rr input[type=range]{flex:1;}
.rr span{font-size:12px;font-weight:700;color:rgba(255,255,255,.6);min-width:34px;}
.sep-label{display:block;font-size:10px;font-weight:800;letter-spacing:.1em;text-transform:uppercase;color:rgba(255,255,255,.28);margin:10px 0 4px;}
.banner-panel{display:none;}
#panel-progames,#panel-off,.bs-section:has(#type-switcher){display:none!important;}
.banner-panel.is-active{display:block;}
#banner-form>.row{align-items:start;}
@media(min-width:1200px){#banner-form>.row>.col-xl-4{width:100%;order:-1;}#banner-form>.row>.col-xl-8{width:100%;}.preview-grid{display:block;}.preview-card.is-mobile-preview{max-width:430px;margin:18px auto 14px;}}
#preview-wrap{position:relative;top:auto;border:1px solid rgba(124,155,255,.16);background:linear-gradient(180deg,rgba(124,155,255,.07),rgba(255,255,255,.025));border-radius:18px;padding:18px;box-shadow:0 20px 60px rgba(0,0,0,.22);}
.preview-card{border-radius:16px;overflow:hidden;margin-bottom:14px;background:rgba(8,12,24,.72);border:1px solid rgba(255,255,255,.08);box-shadow:0 16px 34px rgba(0,0,0,.28);}
.preview-real-scroll{width:100%;overflow:hidden;background:#0b0f1e;border:1px solid rgba(255,255,255,.07);border-top:none;border-radius:0 0 12px 12px;}
.preview-frame{border:none;display:block;background:#0b0f1e;}
#preview-frame-desktop{width:100%;height:86px;background:#0b0f1e;}
#preview-frame-mobile{width:445px;height:94px;background:#0b0f1e;}
.preview-mobile-shell{width:445px;max-width:100%;margin:0 auto;border-radius:22px;overflow:hidden;border:1px solid rgba(255,255,255,.10);box-shadow:0 20px 40px rgba(0,0,0,.35);background:#080b16;}
.prev-hint{font-size:11px;color:rgba(255,255,255,.38);text-align:center;margin-top:2px;}
.bs-section{box-shadow:0 10px 26px rgba(0,0,0,.16);}
.bs-body{background:linear-gradient(180deg,rgba(255,255,255,.018),rgba(0,0,0,.03));}
.form-control{border-radius:9px!important;}
#bs-toast{position:fixed;bottom:24px;right:24px;z-index:99999;padding:12px 20px;border-radius:10px;font-size:13px;font-weight:700;display:none;max-width:360px;}
#bs-toast.ok{background:#166534;color:#bbf7d0;border:1px solid #16a34a;}
#bs-toast.err{background:#7f1d1d;color:#fecaca;border:1px solid #dc2626;}


/* Sticky focused editor layout */
.editor-mode-tabs{display:flex;align-items:center;justify-content:center;gap:8px;margin-bottom:14px;flex-wrap:wrap;}
.editor-mode-btn{border:1px solid rgba(124,155,255,.22);background:rgba(255,255,255,.035);color:rgba(255,255,255,.58);border-radius:999px;padding:9px 18px;font-size:12px;font-weight:900;letter-spacing:.08em;text-transform:uppercase;cursor:pointer;transition:.16s ease;}
.editor-mode-btn:hover{color:#fff;border-color:rgba(124,155,255,.45);background:rgba(124,155,255,.10);}
.editor-mode-btn.is-active{color:#fff;background:linear-gradient(135deg,rgba(61,90,254,.62),rgba(106,29,232,.62));border-color:rgba(124,155,255,.70);box-shadow:0 10px 26px rgba(61,90,254,.20);}
.editor-mode-title{display:flex;align-items:center;justify-content:space-between;gap:12px;margin-bottom:12px;}
.editor-mode-title strong{font-size:12px;font-weight:900;letter-spacing:.10em;text-transform:uppercase;color:rgba(255,255,255,.72);}
.editor-mode-title span{font-size:11px;color:rgba(255,255,255,.40);}
@media(min-width:1200px){#banner-form>.row{display:block;}#banner-form>.row>.col-xl-4,#banner-form>.row>.col-xl-8{width:100%;max-width:100%;}.preview-card.is-mobile-preview{max-width:445px;margin:0 auto 14px;}}
#preview-wrap{position:sticky!important;top:0;z-index:50;margin-bottom:18px;-webkit-backdrop-filter:blur(16px);backdrop-filter:blur(16px);}
.preview-grid{display:block;}
body[data-editor-mode="desktop"] .preview-card.is-mobile-preview{display:none;}
body[data-editor-mode="mobile"] .preview-card:not(.is-mobile-preview){display:none;}
body[data-editor-mode="desktop"] .editor-setting-mobile{display:none!important;}
body[data-editor-mode="mobile"] .editor-setting-desktop{display:none!important;}
.editor-section-note{margin:-4px 0 14px;padding:12px 14px;border-radius:12px;border:1px solid rgba(124,155,255,.14);background:rgba(124,155,255,.06);color:rgba(255,255,255,.48);font-size:12px;}
@media(max-width:900px){#preview-wrap{top:0;padding:12px;border-radius:14px;}.editor-mode-title{display:block;text-align:center;}.editor-mode-title span{display:block;margin-top:4px;}}

/* Fixed header style preview */
.preview-fixed-spacer{height:178px;}
#preview-wrap{
    position:fixed!important;
    top:0!important;
    left:260px;
    right:0;
    z-index:10050;
    margin:0!important;
    padding:10px 18px 12px!important;
    border-radius:0!important;
    border:0!important;
    border-bottom:1px solid rgba(124,155,255,.20)!important;
    background:linear-gradient(180deg,rgba(16,20,31,.98),rgba(11,15,30,.96))!important;
    box-shadow:0 18px 48px rgba(0,0,0,.34)!important;
}
#preview-wrap .editor-mode-title{margin-bottom:8px;}
#preview-wrap .editor-mode-title strong{font-size:11px;}
#preview-wrap .editor-mode-title span{font-size:10px;}
#preview-wrap .editor-mode-tabs{position:absolute;right:18px;top:9px;margin:0;justify-content:flex-end;}
#preview-wrap .editor-mode-btn{padding:7px 13px;font-size:10px;}
#preview-wrap .preview-card{margin:0!important;border-radius:12px;box-shadow:none;}
#preview-wrap .preview-card:not(.is-mobile-preview){width:100%;}
#preview-wrap .preview-card:not(.is-mobile-preview) .preview-frame{width:100%!important;}
#preview-wrap .preview-card>div:first-child{display:none!important;}
#preview-wrap .preview-real-scroll{border-radius:12px!important;border:1px solid rgba(124,155,255,.18)!important;overflow:hidden!important;}
#preview-wrap .preview-mobile-shell{margin:0 auto!important;box-shadow:none!important;}
#preview-wrap .prev-hint{display:none;}
body[data-editor-mode="desktop"] #preview-wrap{min-height:116px;}
body[data-editor-mode="mobile"] #preview-wrap{min-height:146px;}
body[data-editor-mode="desktop"] .preview-fixed-spacer{height:132px;}
body[data-editor-mode="mobile"] .preview-fixed-spacer{height:162px;}
body[data-editor-mode="mobile"] #preview-wrap .editor-mode-tabs{position:static;margin-bottom:8px;justify-content:center;}
body[data-editor-mode="mobile"] #preview-wrap .editor-mode-title{text-align:center;display:block;margin-bottom:6px;}
@media(max-width:1199px){
    #preview-wrap{left:0;}
}
@media(max-width:700px){
    #preview-wrap{padding:8px 10px 10px!important;}
    #preview-wrap .editor-mode-tabs{position:static;margin-bottom:8px;justify-content:center;}
    #preview-wrap .editor-mode-title{text-align:center;display:block;margin-bottom:6px;}
    .preview-fixed-spacer,body[data-editor-mode="desktop"] .preview-fixed-spacer,body[data-editor-mode="mobile"] .preview-fixed-spacer{height:170px;}
}


.bs-datetime-wrap{position:relative;display:flex;gap:8px;align-items:center;}
.bs-datetime-wrap .form-control{flex:1;}
.bs-datetime-toggle{height:34px;border-radius:9px;border:1px solid rgba(255,255,255,.12);background:rgba(255,255,255,.05);color:#fff;padding:0 12px;font-size:12px;font-weight:800;display:inline-flex;align-items:center;gap:7px;cursor:pointer;white-space:nowrap;}
.bs-datetime-toggle:hover{background:rgba(255,255,255,.08);border-color:rgba(124,155,255,.35);}
.bs-datetime-pop{position:absolute;top:42px;left:0;z-index:100000;width:330px;border-radius:16px;border:1px solid rgba(124,155,255,.22);background:#111827;box-shadow:0 24px 70px rgba(0,0,0,.55);padding:14px;display:none;}
.bs-datetime-pop.is-open{display:block;}
.bs-cal-head{display:flex;align-items:center;justify-content:space-between;gap:10px;margin-bottom:12px;}
.bs-cal-title{font-size:13px;font-weight:900;color:#fff;letter-spacing:.03em;}
.bs-cal-nav{width:30px;height:30px;border-radius:9px;border:1px solid rgba(255,255,255,.10);background:rgba(255,255,255,.04);color:#fff;display:inline-flex;align-items:center;justify-content:center;cursor:pointer;}
.bs-cal-grid{display:grid;grid-template-columns:repeat(7,1fr);gap:5px;}
.bs-cal-weekday{font-size:10px;font-weight:900;text-transform:uppercase;color:rgba(255,255,255,.35);text-align:center;padding:4px 0;}
.bs-cal-day{height:34px;border-radius:9px;border:1px solid transparent;background:rgba(255,255,255,.035);color:rgba(255,255,255,.78);font-size:12px;font-weight:800;cursor:pointer;}
.bs-cal-day:hover{background:rgba(124,155,255,.14);border-color:rgba(124,155,255,.30);color:#fff;}
.bs-cal-day.is-muted{opacity:.25;}
.bs-cal-day.is-selected{background:linear-gradient(135deg,#3d5afe,#6a1de8);color:#fff;border-color:rgba(255,255,255,.18);}
.bs-time-row{display:grid;grid-template-columns:1fr 1fr;gap:8px;margin-top:12px;}
.bs-time-row label{font-size:10px;font-weight:900;text-transform:uppercase;letter-spacing:.08em;color:rgba(255,255,255,.38);margin-bottom:4px;display:block;}
.bs-time-row input{width:100%;height:34px;border-radius:9px;border:1px solid rgba(255,255,255,.10);background:rgba(0,0,0,.22);color:#fff;padding:0 10px;font-size:12px;font-weight:800;}
.bs-cal-actions{display:flex;gap:8px;margin-top:12px;}
.bs-cal-actions button{flex:1;height:34px;border-radius:9px;border:1px solid rgba(255,255,255,.10);font-size:12px;font-weight:900;cursor:pointer;}
.bs-cal-clear{background:rgba(255,255,255,.04);color:rgba(255,255,255,.65);}
.bs-cal-apply{background:linear-gradient(135deg,#3d5afe,#6a1de8);color:#fff;}
@media(max-width:520px){.bs-datetime-pop{width:min(330px,calc(100vw - 34px));left:0;}}

</style>
<style>
.bs-cal-day.is-disabled{opacity:.28;cursor:not-allowed;pointer-events:none;background:rgba(255,255,255,.025)!important;}
.bs-cal-day.is-today{outline:1px solid rgba(124,155,255,.55);}
</style>

<div class="container-fluid pb-5">
<div class="d-flex align-items-center justify-content-between mb-3">
    <div>
        <h2 class="mb-0">Banner Settings</h2>
        <div class="text-muted small">Edit the sale banner with a sticky live preview and focused desktop or mobile controls.</div>
    </div>
    <button class="btn btn-primary px-4" id="save-btn" type="button"><i class="fas fa-save me-2"></i>Save</button>
</div>

<form id="banner-form" enctype="multipart/form-data">
<input type="hidden" name="action" value="admin_banner_save">
<input type="hidden" name="banner_type" id="banner_type" value="sale">

<div class="row g-4">
<!-- RIGHT: Preview + Save -->
<div class="col-12 col-xl-4">
    <div id="preview-wrap">

        <div class="editor-mode-title">
            <strong>Sale Banner Editor</strong>
            <span>Choose a device, then edit the matching controls below.</span>
        </div>
        <div class="editor-mode-tabs" role="tablist" aria-label="Preview mode">
            <button type="button" class="editor-mode-btn is-active" data-editor-mode="desktop"><i class="fas fa-desktop me-1"></i> Edit Desktop</button>
            <button type="button" class="editor-mode-btn" data-editor-mode="mobile"><i class="fas fa-mobile-alt me-1"></i> Edit Mobile</button>
        </div>
        <div class="preview-grid">
        <div class="preview-card">
            <div style="display:flex;align-items:center;gap:8px;padding:10px 14px;background:rgba(255,255,255,.03);border:1px solid rgba(255,255,255,.07);border-radius:12px 12px 0 0;font-size:11px;font-weight:800;text-transform:uppercase;letter-spacing:.08em;color:rgba(255,255,255,.4);">
                <i class="fas fa-desktop" style="font-size:13px;"></i> Desktop Sale Banner
            </div>
            <div class="preview-real-scroll"><iframe id="preview-frame-desktop" class="preview-frame" title="Desktop Banner Preview" scrolling="no"></iframe></div>
        </div>

        <div class="preview-card is-mobile-preview">
            <div style="display:flex;align-items:center;gap:8px;padding:10px 14px;background:rgba(255,255,255,.03);border:1px solid rgba(255,255,255,.07);border-radius:12px 12px 0 0;font-size:11px;font-weight:800;text-transform:uppercase;letter-spacing:.08em;color:rgba(255,255,255,.4);">
                <i class="fas fa-mobile-alt" style="font-size:13px;"></i> Mobile Sale Banner
            </div>
            <div class="preview-mobile-shell">
                <iframe id="preview-frame-mobile" class="preview-frame" title="Mobile Banner Preview" scrolling="no"></iframe>
            </div>
        </div>
        </div>

        <div class="prev-hint">Sale banner only, click text in the preview to edit</div>
    </div>

    <div class="preview-fixed-spacer" aria-hidden="true"></div>

    <div class="bs-section mt-3">
        <div class="bs-body">
            <button class="btn btn-primary w-100" id="save-btn-2" type="button"><i class="fas fa-save me-2"></i>Save Changes</button>
            <p class="small text-muted mt-3 mb-0">
                On save, the LocalStorage close-key version auto-increments — visitors who previously dismissed the banner will see it again on next page load.
            </p>
        </div>
    </div>
</div>

<div class="col-12 col-xl-8">

<!-- Type -->
<div class="bs-section">
    <div class="bs-head"><i class="fas fa-toggle-on"></i>Active Banner</div>
    <div class="bs-body">
        <div class="d-flex gap-2 flex-wrap" id="type-switcher">
            <button type="button" class="type-pill <?= $B['type']==='progames'?'active':'' ?>" data-type="progames">🎮 Pro Games</button>
            <button type="button" class="type-pill <?= $B['type']==='sale'?'active':'' ?>" data-type="sale">🔥 Sale / Countdown</button>
            <button type="button" class="type-pill <?= $B['type']==='off'?'active':'' ?>" data-type="off">🚫 Off</button>
        </div>
    </div>
</div>

<!-- ═══════════ PRO GAMES ═══════════ -->
<div class="banner-panel <?= $B['type']==='progames'?'is-active':'' ?>" id="panel-progames">

<div class="bs-section">
    <div class="bs-head"><i class="fas fa-fill-drip"></i>Background</div>
    <div class="bs-body">
        <div class="bs-grid g2">
            <div><label class="form-label small">Gradient From</label>
                <div class="cf"><input type="color" value="<?= toHex($B['pg_bg_color_from'],'#0b0f1e') ?>" data-target="pg_bg_color_from">
                <input type="text" class="form-control form-control-sm" id="pg_bg_color_from" name="banner_pg_bg_color_from" value="<?= h($B['pg_bg_color_from']) ?>"></div></div>
            <div><label class="form-label small">Gradient To</label>
                <div class="cf"><input type="color" value="<?= toHex($B['pg_bg_color_to'],'#0b0f1e') ?>" data-target="pg_bg_color_to">
                <input type="text" class="form-control form-control-sm" id="pg_bg_color_to" name="banner_pg_bg_color_to" value="<?= h($B['pg_bg_color_to']) ?>"></div></div>
        </div>
        <div class="mt-3"><label class="form-label small">Background Image <span class="text-muted">(optional overlay)</span></label>
            <div class="img-row">
                <img id="pg_bg_image_prev" class="img-prev <?= $B['pg_bg_image']?'show':'' ?>" src="<?= h($B['pg_bg_image']) ?>">
                <input type="file" class="form-control form-control-sm" style="max-width:260px" name="banner_pg_bg_image_file" accept="image/*">
                <input type="hidden" name="banner_pg_bg_image" id="pg_bg_image" value="<?= h($B['pg_bg_image']) ?>">
                <button type="button" class="img-rm" data-clear="pg_bg_image" data-prev="pg_bg_image_prev">✕ Remove</button>
            </div>
            <div class="mt-2"><label class="form-label small">Opacity</label>
                <div class="rr"><input type="range" min="0" max="1" step="0.01" name="banner_pg_bg_image_opacity" id="pg_bg_image_opacity" value="<?= h($B['pg_bg_image_opacity']) ?>">
                <span id="pg_bg_image_opacity_lbl"><?= h($B['pg_bg_image_opacity']) ?></span></div></div>
        </div>
    </div>
</div>

<div class="bs-section">
    <div class="bs-head"><i class="fas fa-certificate"></i>Badge + Icon</div>
    <div class="bs-body">
        <div class="bs-grid g3">
            <div><label class="form-label small">Badge Text</label>
                <input type="text" class="form-control form-control-sm" name="banner_pg_badge_text" value="<?= h($B['pg_badge_text']) ?>"></div>
            <div><label class="form-label small">Text Color</label>
                <div class="cf"><input type="color" value="<?= toHex($B['pg_badge_color'],'#7c9bff') ?>" data-target="pg_badge_color">
                <input type="text" class="form-control form-control-sm" id="pg_badge_color" name="banner_pg_badge_color" value="<?= h($B['pg_badge_color']) ?>"></div></div>
            <div><label class="form-label small">Border Color</label>
                <div class="cf"><input type="color" value="<?= toHex($B['pg_badge_border'],'#3d5afe') ?>" data-target="pg_badge_border">
                <input type="text" class="form-control form-control-sm" id="pg_badge_border" name="banner_pg_badge_border" value="<?= h($B['pg_badge_border']) ?>"></div></div>
        </div>
        <div class="mt-3"><label class="form-label small">Badge Icon <span class="text-muted">(optional, shown left of text)</span></label>
            <div class="img-row">
                <img id="pg_icon_url_prev" class="img-prev <?= $B['pg_icon_url']?'show':'' ?>" src="<?= h($B['pg_icon_url']) ?>">
                <input type="file" class="form-control form-control-sm" style="max-width:260px" name="banner_pg_icon_url_file" accept="image/*">
                <input type="hidden" name="banner_pg_icon_url" id="pg_icon_url" value="<?= h($B['pg_icon_url']) ?>">
                <button type="button" class="img-rm" data-clear="pg_icon_url" data-prev="pg_icon_url_prev">✕ Remove</button>
            </div>
        </div>
    </div>
</div>

<div class="bs-section">
    <div class="bs-head"><i class="fas fa-heading"></i>Headline</div>
    <div class="bs-body">
        <label class="form-label small">Text — use <code>{ACCENT}…{/ACCENT}</code> for colored highlight</label>
        <input type="text" class="form-control" name="banner_pg_title" value="<?= h($B['pg_title']) ?>">
        <div class="bs-grid g2 mt-3">
            <div><label class="form-label small">Base Color</label>
                <div class="cf"><input type="color" value="<?= toHex($B['pg_title_color']) ?>" data-target="pg_title_color">
                <input type="text" class="form-control form-control-sm" id="pg_title_color" name="banner_pg_title_color" value="<?= h($B['pg_title_color']) ?>"></div></div>
            <div><label class="form-label small">Accent Color</label>
                <div class="cf"><input type="color" value="<?= toHex($B['pg_accent_color'],'#7c9bff') ?>" data-target="pg_accent_color">
                <input type="text" class="form-control form-control-sm" id="pg_accent_color" name="banner_pg_accent_color" value="<?= h($B['pg_accent_color']) ?>"></div></div>
        </div>
    </div>
</div>

<div class="bs-section">
    <div class="bs-head"><i class="fas fa-ellipsis"></i>Info Pills <small style="font-weight:500;text-transform:none;letter-spacing:0;margin-left:6px;">(leave empty to hide)</small></div>
    <div class="bs-body">
        <div class="bs-grid g2">
            <?php for ($n=1; $n<=5; $n++): ?>
            <div><label class="form-label small">Pill <?= $n ?></label>
                <input type="text" class="form-control form-control-sm" name="banner_pg_pill_<?= $n ?>" value="<?= h($B["pg_pill_$n"]) ?>" placeholder="Pill <?= $n ?>…"></div>
            <?php endfor; ?>
        </div>
    </div>
</div>

<div class="bs-section">
    <div class="bs-head"><i class="fas fa-arrow-right"></i>CTA Button</div>
    <div class="bs-body">
        <div class="bs-grid g2">
            <div><label class="form-label small">Button Text</label>
                <input type="text" class="form-control form-control-sm" name="banner_pg_cta_text" value="<?= h($B['pg_cta_text']) ?>"></div>
            <div><label class="form-label small">Button Link</label>
                <input type="text" class="form-control form-control-sm" name="banner_pg_cta_link" value="<?= h($B['pg_cta_link']) ?>"></div>
            <div><label class="form-label small">BG From</label>
                <div class="cf"><input type="color" value="<?= toHex($B['pg_cta_bg_from'],'#3d5afe') ?>" data-target="pg_cta_bg_from">
                <input type="text" class="form-control form-control-sm" id="pg_cta_bg_from" name="banner_pg_cta_bg_from" value="<?= h($B['pg_cta_bg_from']) ?>"></div></div>
            <div><label class="form-label small">BG To</label>
                <div class="cf"><input type="color" value="<?= toHex($B['pg_cta_bg_to'],'#6a1de8') ?>" data-target="pg_cta_bg_to">
                <input type="text" class="form-control form-control-sm" id="pg_cta_bg_to" name="banner_pg_cta_bg_to" value="<?= h($B['pg_cta_bg_to']) ?>"></div></div>
            <div><label class="form-label small">Text Color</label>
                <div class="cf"><input type="color" value="<?= toHex($B['pg_cta_color']) ?>" data-target="pg_cta_color">
                <input type="text" class="form-control form-control-sm" id="pg_cta_color" name="banner_pg_cta_color" value="<?= h($B['pg_cta_color']) ?>"></div></div>
        </div>
    </div>
</div>

<div class="bs-section">
    <div class="bs-head"><i class="fas fa-mobile-screen"></i>Mobile</div>
    <div class="bs-body">
        <div class="bs-grid g2">
            <div><label class="form-label small">Title</label>
                <input type="text" class="form-control form-control-sm" name="banner_pg_mob_title" value="<?= h($B['pg_mob_title']) ?>"></div>
            <div><label class="form-label small">Title Color</label>
                <div class="cf"><input type="color" value="<?= toHex($B['pg_mob_title_color']) ?>" data-target="pg_mob_title_color">
                <input type="text" class="form-control form-control-sm" id="pg_mob_title_color" name="banner_pg_mob_title_color" value="<?= h($B['pg_mob_title_color']) ?>"></div></div>
            <div><label class="form-label small">Subtitle</label>
                <input type="text" class="form-control form-control-sm" name="banner_pg_mob_sub" value="<?= h($B['pg_mob_sub']) ?>"></div>
            <div><label class="form-label small">Subtitle Color</label>
                <div class="cf"><input type="color" value="<?= toHex($B['pg_mob_sub_color'],'#888') ?>" data-target="pg_mob_sub_color">
                <input type="text" class="form-control form-control-sm" id="pg_mob_sub_color" name="banner_pg_mob_sub_color" value="<?= h($B['pg_mob_sub_color']) ?>"></div></div>
            <div><label class="form-label small">CTA Text</label>
                <input type="text" class="form-control form-control-sm" name="banner_pg_mob_cta_text" value="<?= h($B['pg_mob_cta_text']) ?>"></div>
            <div><label class="form-label small">CTA Link</label>
                <input type="text" class="form-control form-control-sm" name="banner_pg_mob_cta_link" value="<?= h($B['pg_mob_cta_link']) ?>"></div>
        </div>
    </div>
</div>

</div><!-- /panel-progames -->

<!-- ═══════════ SALE ═══════════ -->
<div class="banner-panel is-active" id="panel-sale">
<div class="editor-section-note">The preview stays pinned while you edit. Switch between desktop and mobile to show only the controls you need.</div>

<div class="bs-section">
    <div class="bs-head"><i class="fas fa-fill-drip"></i>Background</div>
    <div class="bs-body">
        <div class="bs-grid g2">
            <div><label class="form-label small">Gradient From</label>
                <div class="cf"><input type="color" value="<?= toHex($B['sale_bg_color_from'],'#0b0f1e') ?>" data-target="sale_bg_color_from">
                <input type="text" class="form-control form-control-sm" id="sale_bg_color_from" name="banner_sale_bg_color_from" value="<?= h($B['sale_bg_color_from']) ?>"></div></div>
            <div><label class="form-label small">Gradient To</label>
                <div class="cf"><input type="color" value="<?= toHex($B['sale_bg_color_to'],'#0b0f1e') ?>" data-target="sale_bg_color_to">
                <input type="text" class="form-control form-control-sm" id="sale_bg_color_to" name="banner_sale_bg_color_to" value="<?= h($B['sale_bg_color_to']) ?>"></div></div>
        </div>
        <div class="bs-grid g2 mt-3">
            <div><label class="form-label small">Left BG Image <span class="text-muted">(drag, paste, or upload)</span></label>
                <div class="img-drop" data-image-field="sale_bg_image_left" tabindex="0">
                    <img id="sale_bg_image_left_prev" class="img-prev <?= $B['sale_bg_image_left']?'show':'' ?>" src="<?= h($B['sale_bg_image_left']) ?>">
                    <div class="img-drop-text"><strong>Drop image here</strong><span>Upload, drag & drop, or paste with Ctrl+V</span></div>
                    <input type="file" name="banner_sale_bg_image_left_file" accept="image/*">
                    <input type="hidden" name="banner_sale_bg_image_left" id="sale_bg_image_left" value="<?= h($B['sale_bg_image_left']) ?>">
                    <button type="button" class="img-rm" data-clear="sale_bg_image_left" data-prev="sale_bg_image_left_prev">Remove</button>
                </div></div>
            <div><label class="form-label small">Right BG Image <span class="text-muted">(drag, paste, or upload)</span></label>
                <div class="img-drop" data-image-field="sale_bg_image_right" tabindex="0">
                    <img id="sale_bg_image_right_prev" class="img-prev <?= $B['sale_bg_image_right']?'show':'' ?>" src="<?= h($B['sale_bg_image_right']) ?>">
                    <div class="img-drop-text"><strong>Drop image here</strong><span>Upload, drag & drop, or paste with Ctrl+V</span></div>
                    <input type="file" name="banner_sale_bg_image_right_file" accept="image/*">
                    <input type="hidden" name="banner_sale_bg_image_right" id="sale_bg_image_right" value="<?= h($B['sale_bg_image_right']) ?>">
                    <button type="button" class="img-rm" data-clear="sale_bg_image_right" data-prev="sale_bg_image_right_prev">Remove</button>
                </div></div>
        </div>
        <div class="mt-2"><label class="form-label small">Images Opacity</label>
            <div class="rr"><input type="range" min="0" max="1" step="0.01" name="banner_sale_bg_image_opacity" id="sale_bg_image_opacity" value="<?= h($B['sale_bg_image_opacity']) ?>">
            <span id="sale_bg_image_opacity_lbl"><?= h($B['sale_bg_image_opacity']) ?></span></div></div>

        <div class="bg-position-card editor-setting-desktop">
            <div class="bg-position-title">Desktop Image Position <span>Adjust which part of each background image is visible</span></div>
            <div class="bg-pos-grid">
                <div class="bg-pos-control"><label>Left Image X <b id="sale_bg_left_pos_x_desktop_lbl"><?= h($B['sale_bg_left_pos_x_desktop']) ?>%</b></label><input class="pos-range" type="range" min="0" max="100" step="1" name="banner_sale_bg_left_pos_x_desktop" id="sale_bg_left_pos_x_desktop" value="<?= h($B['sale_bg_left_pos_x_desktop']) ?>"></div>
                <div class="bg-pos-control"><label>Left Image Y <b id="sale_bg_left_pos_y_desktop_lbl"><?= h($B['sale_bg_left_pos_y_desktop']) ?>%</b></label><input class="pos-range" type="range" min="0" max="100" step="1" name="banner_sale_bg_left_pos_y_desktop" id="sale_bg_left_pos_y_desktop" value="<?= h($B['sale_bg_left_pos_y_desktop']) ?>"></div>
                <div class="bg-pos-control"><label>Right Image X <b id="sale_bg_right_pos_x_desktop_lbl"><?= h($B['sale_bg_right_pos_x_desktop']) ?>%</b></label><input class="pos-range" type="range" min="0" max="100" step="1" name="banner_sale_bg_right_pos_x_desktop" id="sale_bg_right_pos_x_desktop" value="<?= h($B['sale_bg_right_pos_x_desktop']) ?>"></div>
                <div class="bg-pos-control"><label>Right Image Y <b id="sale_bg_right_pos_y_desktop_lbl"><?= h($B['sale_bg_right_pos_y_desktop']) ?>%</b></label><input class="pos-range" type="range" min="0" max="100" step="1" name="banner_sale_bg_right_pos_y_desktop" id="sale_bg_right_pos_y_desktop" value="<?= h($B['sale_bg_right_pos_y_desktop']) ?>"></div>
            </div>
        </div>

        <div class="bg-position-card editor-setting-mobile">
            <div class="bg-position-title">Mobile Image Position <span>Separate crop control for the mobile banner</span></div>
            <div class="bg-pos-grid">
                <div class="bg-pos-control"><label>Left Image X <b id="sale_bg_left_pos_x_mobile_lbl"><?= h($B['sale_bg_left_pos_x_mobile']) ?>%</b></label><input class="pos-range" type="range" min="0" max="100" step="1" name="banner_sale_bg_left_pos_x_mobile" id="sale_bg_left_pos_x_mobile" value="<?= h($B['sale_bg_left_pos_x_mobile']) ?>"></div>
                <div class="bg-pos-control"><label>Left Image Y <b id="sale_bg_left_pos_y_mobile_lbl"><?= h($B['sale_bg_left_pos_y_mobile']) ?>%</b></label><input class="pos-range" type="range" min="0" max="100" step="1" name="banner_sale_bg_left_pos_y_mobile" id="sale_bg_left_pos_y_mobile" value="<?= h($B['sale_bg_left_pos_y_mobile']) ?>"></div>
                <div class="bg-pos-control"><label>Right Image X <b id="sale_bg_right_pos_x_mobile_lbl"><?= h($B['sale_bg_right_pos_x_mobile']) ?>%</b></label><input class="pos-range" type="range" min="0" max="100" step="1" name="banner_sale_bg_right_pos_x_mobile" id="sale_bg_right_pos_x_mobile" value="<?= h($B['sale_bg_right_pos_x_mobile']) ?>"></div>
                <div class="bg-pos-control"><label>Right Image Y <b id="sale_bg_right_pos_y_mobile_lbl"><?= h($B['sale_bg_right_pos_y_mobile']) ?>%</b></label><input class="pos-range" type="range" min="0" max="100" step="1" name="banner_sale_bg_right_pos_y_mobile" id="sale_bg_right_pos_y_mobile" value="<?= h($B['sale_bg_right_pos_y_mobile']) ?>"></div>
            </div>
        </div>
    </div>
</div>

<div class="bs-section">
    <div class="bs-head"><i class="fas fa-heading"></i>Headline</div>
    <div class="bs-body">
        <label class="form-label small">Headline — <code>{ACCENT}…{/ACCENT}</code> = gradient</label>
        <input type="text" class="form-control" name="banner_sale_headline" value="<?= h($B['sale_headline']) ?>">
        <div class="bs-grid g3 mt-3">
            <div><label class="form-label small">Text Color</label>
                <div class="cf"><input type="color" value="<?= toHex($B['sale_title_color']) ?>" data-target="sale_title_color">
                <input type="text" class="form-control form-control-sm" id="sale_title_color" name="banner_sale_title_color" value="<?= h($B['sale_title_color']) ?>"></div></div>
            <div><label class="form-label small">Accent From</label>
                <div class="cf"><input type="color" value="<?= toHex($B['sale_accent_from'],'#f59e0b') ?>" data-target="sale_accent_from">
                <input type="text" class="form-control form-control-sm" id="sale_accent_from" name="banner_sale_accent_from" value="<?= h($B['sale_accent_from']) ?>"></div></div>
            <div><label class="form-label small">Accent To</label>
                <div class="cf"><input type="color" value="<?= toHex($B['sale_accent_to'],'#ef4444') ?>" data-target="sale_accent_to">
                <input type="text" class="form-control form-control-sm" id="sale_accent_to" name="banner_sale_accent_to" value="<?= h($B['sale_accent_to']) ?>"></div></div>
        </div>
    </div>
</div>

<div class="bs-section">
    <div class="bs-head"><i class="fas fa-hourglass-half"></i>Countdown Timer</div>
    <div class="bs-body">
        <div class="bs-grid g2">
            <div><label class="form-label small">End Date &amp; Time</label>
                <div class="bs-datetime-wrap" id="sale_datetime_picker">
                    <input type="hidden" name="banner_sale_ends_at" id="sale_ends_at" value="<?= h($B['sale_ends_at']) ?>">
                    <input type="text" class="form-control form-control-sm" id="sale_ends_at_display" value="<?= h($B['sale_ends_at']) ?>" placeholder="DD-MM-YY HH-MM-SS" autocomplete="off" inputmode="numeric">
                    <button type="button" class="bs-datetime-toggle" id="sale_datetime_toggle"><i class="fas fa-calendar-alt"></i> Pick date</button>
                    <div class="bs-datetime-pop" id="sale_datetime_pop">
                        <div class="bs-cal-head">
                            <button type="button" class="bs-cal-nav" data-cal-prev>‹</button>
                            <div class="bs-cal-title" id="sale_cal_title"></div>
                            <button type="button" class="bs-cal-nav" data-cal-next>›</button>
                        </div>
                        <div class="bs-cal-grid" id="sale_cal_grid"></div>
                        <div class="bs-time-row">
                            <div><label>Hour</label><input type="number" id="sale_cal_hour" min="0" max="23" step="1" value="23"></div>
                            <div><label>Minute</label><input type="number" id="sale_cal_minute" min="0" max="59" step="1" value="59"></div>
                        </div>
                        <div class="bs-cal-actions">
                            <button type="button" class="bs-cal-clear" id="sale_cal_clear">Clear</button>
                            <button type="button" class="bs-cal-apply" id="sale_cal_apply">Apply</button>
                        </div>
                    </div>
                </div>
                <div id="js-time-left" class="small text-info mt-1"></div></div>
            <div><label class="form-label small">Label above timer</label>
                <input type="text" class="form-control form-control-sm" name="banner_sale_countdown_label" value="<?= h($B['sale_countdown_label']) ?>"></div>
            <div><label class="form-label small">Number Color</label>
                <div class="cf"><input type="color" value="<?= toHex($B['sale_countdown_color']) ?>" data-target="sale_countdown_color">
                <input type="text" class="form-control form-control-sm" id="sale_countdown_color" name="banner_sale_countdown_color" value="<?= h($B['sale_countdown_color']) ?>"></div></div>
            <div><label class="form-label small">Box BG</label>
                <div class="cf"><input type="color" value="<?= toHex($B['sale_countdown_unit_bg'],'#1a1000') ?>" data-target="sale_countdown_unit_bg">
                <input type="text" class="form-control form-control-sm" id="sale_countdown_unit_bg" name="banner_sale_countdown_unit_bg" value="<?= h($B['sale_countdown_unit_bg']) ?>"></div></div>
            <div><label class="form-label small">Box Border</label>
                <div class="cf"><input type="color" value="<?= toHex($B['sale_countdown_border'],'#f59e0b') ?>" data-target="sale_countdown_border">
                <input type="text" class="form-control form-control-sm" id="sale_countdown_border" name="banner_sale_countdown_border" value="<?= h($B['sale_countdown_border']) ?>"></div></div>
        </div>
    </div>
</div>

<div class="bs-section">
    <div class="bs-head"><i class="fas fa-arrow-right"></i>CTA Button</div>
    <div class="bs-body">
        <div class="bs-grid g4">
            <div><label class="form-label small">Text</label>
                <input type="text" class="form-control form-control-sm" name="banner_sale_cta_1_text" value="<?= h($B['sale_cta_1_text']) ?>"></div>
            <div><label class="form-label small">Link</label>
                <input type="text" class="form-control form-control-sm" name="banner_sale_cta_1_link" value="<?= h($B['sale_cta_1_link']) ?>"></div>
            <div><label class="form-label small">BG From</label>
                <div class="cf"><input type="color" value="<?= toHex($B['sale_cta_1_bg_from'],'#d97706') ?>" data-target="sale_cta_1_bg_from">
                <input type="text" class="form-control form-control-sm" id="sale_cta_1_bg_from" name="banner_sale_cta_1_bg_from" value="<?= h($B['sale_cta_1_bg_from']) ?>"></div></div>
            <div><label class="form-label small">BG To</label>
                <div class="cf"><input type="color" value="<?= toHex($B['sale_cta_1_bg_to'],'#b45309') ?>" data-target="sale_cta_1_bg_to">
                <input type="text" class="form-control form-control-sm" id="sale_cta_1_bg_to" name="banner_sale_cta_1_bg_to" value="<?= h($B['sale_cta_1_bg_to']) ?>"></div></div>
        </div>
        <div class="mt-3 mb-1"><label class="form-label small">Button Icon <span class="text-muted">(upload image or Font Awesome class)</span></label>
            <div class="icon-control">
                <div class="img-drop" data-image-field="sale_cta_1_icon" tabindex="0">
                    <img id="sale_cta_1_icon_prev" class="img-prev <?= ($B['sale_cta_1_icon'] && strpos($B['sale_cta_1_icon'], 'fa') !== 0)?'show':'' ?>" src="<?= h($B['sale_cta_1_icon']) ?>">
                    <div class="img-drop-text"><strong>Drop icon image</strong><span>Upload, drag & drop, or paste with Ctrl+V</span></div>
                    <input type="file" name="banner_sale_cta_1_icon_file" accept="image/*">
                    <input type="hidden" name="banner_sale_cta_1_icon" id="sale_cta_1_icon" value="<?= h($B['sale_cta_1_icon']) ?>">
                    <button type="button" class="img-rm" data-clear="sale_cta_1_icon" data-prev="sale_cta_1_icon_prev">Remove</button>
                </div>
                <div class="icon-fa-box">
                    <span class="icon-fa-preview"><i id="sale_cta_1_fa_preview" class="<?= (strpos($B['sale_cta_1_icon'], 'fa') === 0) ? h($B['sale_cta_1_icon']) : 'fa-solid fa-arrow-right' ?>"></i></span>
                    <input type="text" class="form-control form-control-sm" id="sale_cta_1_icon_fa" placeholder="fas fa-bolt" value="<?= (strpos($B['sale_cta_1_icon'], 'fa') === 0) ? h($B['sale_cta_1_icon']) : '' ?>">
                    <div class="icon-fa-help">Enter a Font Awesome class, for example <code>fas fa-bolt</code>. This will replace the uploaded icon.</div>
                </div>
            </div></div>
    </div>
</div>

<div class="bs-section">
    <div class="bs-head"><i class="fas fa-mobile-screen"></i>Mobile</div>
    <div class="bs-body">
        <div class="bs-grid g2">
            <div><label class="form-label small">Headline / % Text</label>
                <input type="text" class="form-control form-control-sm" name="banner_sale_mob_pct" value="<?= h($B['sale_mob_pct']) ?>" placeholder="20% OFF"></div>
            <div><label class="form-label small">Gradient From → To</label>
                <div class="d-flex gap-2">
                    <div class="cf"><input type="color" value="<?= toHex($B['sale_mob_pct_color_from'],'#f59e0b') ?>" data-target="sale_mob_pct_color_from">
                    <input type="text" class="form-control form-control-sm" id="sale_mob_pct_color_from" name="banner_sale_mob_pct_color_from" value="<?= h($B['sale_mob_pct_color_from']) ?>"></div>
                    <div class="cf"><input type="color" value="<?= toHex($B['sale_mob_pct_color_to'],'#ef4444') ?>" data-target="sale_mob_pct_color_to">
                    <input type="text" class="form-control form-control-sm" id="sale_mob_pct_color_to" name="banner_sale_mob_pct_color_to" value="<?= h($B['sale_mob_pct_color_to']) ?>"></div>
                </div></div>
            <div><label class="form-label small">Sub-Label</label>
                <input type="text" class="form-control form-control-sm" name="banner_sale_mob_label" value="<?= h($B['sale_mob_label']) ?>"></div>
            <div><label class="form-label small">Label Color</label>
                <div class="cf"><input type="color" value="<?= toHex($B['sale_mob_label_color'],'#888') ?>" data-target="sale_mob_label_color">
                <input type="text" class="form-control form-control-sm" id="sale_mob_label_color" name="banner_sale_mob_label_color" value="<?= h($B['sale_mob_label_color']) ?>"></div></div>
        </div>
    </div>
</div>

</div><!-- /panel-sale -->

<!-- Off -->
<div class="banner-panel <?= $B['type']==='off'?'is-active':'' ?>" id="panel-off">
    <div class="bs-section"><div class="bs-body text-muted">Banner disabled — no banner will appear on the site.</div></div>
</div>

</div><!-- /col-left -->

</div><!-- /row -->
</form>

<div id="bs-toast"></div>

<?php
// Helper – HTML-escape shorthand
function h(string $s): string { return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); }
?>

<script>
(function(){
"use strict";

// ── Type switcher ─────────────────────────────────────────────────────────
var typeInput = document.getElementById('banner_type');
document.querySelectorAll('.type-pill').forEach(function(btn){
    btn.addEventListener('click', function(){
        typeInput.value = btn.dataset.type;
        document.querySelectorAll('.type-pill').forEach(function(b){ b.classList.remove('active'); });
        btn.classList.add('active');
        document.querySelectorAll('.banner-panel').forEach(function(p){ p.classList.remove('is-active'); });
        var p = document.getElementById('panel-' + btn.dataset.type);
        if (p) p.classList.add('is-active');
        schedulePreview();
    });
});

// ── Color pickers ─────────────────────────────────────────────────────────
document.querySelectorAll('input[type=color][data-target]').forEach(function(picker){
    var txt = document.getElementById(picker.dataset.target);
    if (!txt) return;
    picker.addEventListener('input', function(){ txt.value = picker.value; schedulePreview(); });
    txt.addEventListener('input', function(){
        if (/^#[0-9a-fA-F]{3,8}$/.test(txt.value.trim())) picker.value = txt.value.trim();
        schedulePreview();
    });
});

// ── Custom color picker UI ───────────────────────────────────────────────
(function(){
    var presets = ['#0b0f1e','#111827','#ffffff','#7c9bff','#3d5afe','#6a1de8','#f59e0b','#ef4444','#d97706','#b45309','#22c55e','#06b6d4','#a855f7','#ec4899','#94a3b8','#000000'];
    document.querySelectorAll('.cf').forEach(function(cf){
        var native = cf.querySelector('input[type=color][data-target]');
        var txt = native ? document.getElementById(native.dataset.target) : null;
        if (!native || !txt || cf.querySelector('.color-swatch-btn')) return;
        var btn = document.createElement('button');
        btn.type = 'button';
        btn.className = 'color-swatch-btn';
        btn.style.background = txt.value || native.value || '#000000';
        cf.insertBefore(btn, native);
        var pop = document.createElement('div');
        pop.className = 'color-pop';
        pop.innerHTML = '<div class="color-pop-title">Color presets</div><div class="color-presets"></div><div class="color-native-row"><span style="font-size:11px;color:rgba(255,255,255,.45);font-weight:800;">Custom</span></div>';
        var holder = pop.querySelector('.color-presets');
        presets.forEach(function(c){
            var p = document.createElement('button'); p.type='button'; p.className='color-preset'; p.style.background=c; p.title=c;
            p.addEventListener('click', function(){ txt.value=c; native.value=c; btn.style.background=c; txt.dispatchEvent(new Event('input',{bubbles:true})); pop.classList.remove('is-open'); });
            holder.appendChild(p);
        });
        pop.querySelector('.color-native-row').appendChild(native);
        cf.appendChild(pop);
        btn.addEventListener('click', function(e){ e.preventDefault(); document.querySelectorAll('.color-pop.is-open').forEach(function(x){ if(x!==pop)x.classList.remove('is-open'); }); pop.classList.toggle('is-open'); });
        native.addEventListener('input', function(){ btn.style.background = native.value; });
        txt.addEventListener('input', function(){ btn.style.background = txt.value; });
    });
    document.addEventListener('click', function(e){ if(!e.target.closest('.cf')) document.querySelectorAll('.color-pop.is-open').forEach(function(x){x.classList.remove('is-open');}); });
})();

// ── Range sliders ─────────────────────────────────────────────────────────
['pg_bg_image_opacity','sale_bg_image_opacity'].forEach(function(id){
    var r = document.getElementById(id);
    var l = document.getElementById(id + '_lbl');
    if (!r || !l) return;
    r.addEventListener('input', function(){ l.textContent = parseFloat(r.value).toFixed(2); schedulePreview(); });
});
document.querySelectorAll('.pos-range').forEach(function(r){
    var l = document.getElementById(r.id + '_lbl');
    function update(){ if(l) l.textContent = r.value + '%'; schedulePreview(); }
    r.addEventListener('input', update);
});

// ── Image upload, drag & drop, and clipboard paste ───────────────────────
var activeDropField = null;
function setImageValue(hiddenId, file){
    if (!file || !/^image\//.test(file.type || '')) return;
    var hidden = document.getElementById(hiddenId);
    var prev = document.getElementById(hiddenId + '_prev');
    var faInput = document.getElementById(hiddenId + '_fa');
    var reader = new FileReader();
    reader.onload = function(e){
        if (hidden) hidden.value = e.target.result;
        if (prev) { prev.src = e.target.result; prev.classList.add('show'); }
        if (faInput) faInput.value = '';
        schedulePreview();
    };
    reader.readAsDataURL(file);
}
document.querySelectorAll('input[type=file]').forEach(function(fi){
    fi.addEventListener('change', function(){
        if (!fi.files[0]) return;
        var hiddenId = fi.name.replace(/_file$/, '').replace(/^banner_/, '');
        setImageValue(hiddenId, fi.files[0]);
    });
});
document.querySelectorAll('.img-drop[data-image-field]').forEach(function(zone){
    var id = zone.dataset.imageField;
    zone.addEventListener('click', function(){ activeDropField = id; document.querySelectorAll('.img-drop').forEach(function(z){z.classList.remove('is-active');}); zone.classList.add('is-active'); });
    zone.addEventListener('focus', function(){ activeDropField = id; zone.classList.add('is-active'); });
    zone.addEventListener('dragover', function(e){ e.preventDefault(); activeDropField = id; zone.classList.add('is-dragover'); });
    zone.addEventListener('dragleave', function(){ zone.classList.remove('is-dragover'); });
    zone.addEventListener('drop', function(e){ e.preventDefault(); zone.classList.remove('is-dragover'); activeDropField = id; if(e.dataTransfer && e.dataTransfer.files[0]) setImageValue(id, e.dataTransfer.files[0]); });
});
document.addEventListener('paste', function(e){
    if (!activeDropField || !e.clipboardData) return;
    var items = e.clipboardData.items || [];
    for (var i=0; i<items.length; i++){
        if (items[i].type && items[i].type.indexOf('image/') === 0){
            setImageValue(activeDropField, items[i].getAsFile());
            e.preventDefault();
            break;
        }
    }
});
var faIconInput = document.getElementById('sale_cta_1_icon_fa');
if (faIconInput){
    faIconInput.addEventListener('input', function(){
        var v = faIconInput.value.trim();
        var hidden = document.getElementById('sale_cta_1_icon');
        var prev = document.getElementById('sale_cta_1_icon_prev');
        var iconPrev = document.getElementById('sale_cta_1_fa_preview');
        if (v && /^fa-/.test(v)) v = 'fa-solid ' + v;
        v = v.replace(/\bfas\b/g, 'fa-solid').replace(/\bfar\b/g, 'fa-regular').replace(/\bfab\b/g, 'fa-brands');
        if (hidden) hidden.value = v;
        if (prev) prev.classList.remove('show');
        if (iconPrev) iconPrev.className = v || 'fa-solid fa-arrow-right';
        schedulePreview();
    });
}

// ── Remove image buttons ──────────────────────────────────────────────────
document.querySelectorAll('.img-rm').forEach(function(btn){
    btn.addEventListener('click', function(){
        var h = document.getElementById(btn.dataset.clear);
        var p = document.getElementById(btn.dataset.prev);
        if (h) h.value = '';
        if (p) { p.src = ''; p.classList.remove('show'); }
        var fa = document.getElementById((btn.dataset.clear || '') + '_fa');
        if (fa) fa.value = '';
        schedulePreview();
    });
});

// ── All text inputs trigger preview ──────────────────────────────────────
document.querySelectorAll('#banner-form input:not([type=file]):not([type=color]), #banner-form textarea').forEach(function(el){
    el.addEventListener('input', function(){ schedulePreview(); });
});

// ── Inline edits from preview iframe ─────────────────────────────────────
var inlineSaveTimer = null;
var isSavingInline = false;
function scheduleInlineSave(){
    clearTimeout(inlineSaveTimer);
    inlineSaveTimer = setTimeout(function(){ doSave(true); }, 450);
}

window.addEventListener('message', function(ev){
    var pos = ev.data && ev.data.bannerPreviewPosition;
    if (!pos || !pos.side || !pos.mode) return;
    var prefix = pos.side === 'right' ? 'sale_bg_right_pos_' : 'sale_bg_left_pos_';
    var xEl = document.getElementById(prefix + 'x_' + pos.mode);
    var yEl = document.getElementById(prefix + 'y_' + pos.mode);
    if (xEl && yEl){
        xEl.value = Math.max(0, Math.min(100, Math.round(pos.x)));
        yEl.value = Math.max(0, Math.min(100, Math.round(pos.y)));
        xEl.dispatchEvent(new Event('input', { bubbles:true }));
        yEl.dispatchEvent(new Event('input', { bubbles:true }));
        scheduleInlineSave();
    }
});
window.addEventListener('message', function(ev){
    var d = ev.data && ev.data.bannerPreviewEdit;
    if (!d || !d.name) return;
    var el = document.querySelector('[name="'+d.name.replace(/"/g,'\"')+'"]');
    if (!el) return;
    el.value = d.value;
    el.dispatchEvent(new Event('input', { bubbles:true }));
    scheduleInlineSave();
});

// ── Countdown live display ────────────────────────────────────────────────
var endsAtHidden = document.getElementById('sale_ends_at');
var endsAtEl = document.getElementById('sale_ends_at_display') || endsAtHidden;
var tlEl = document.getElementById('js-time-left');
function pad(n){ return ('0'+n).slice(-2); }
function parseSaleDate(v){
    v = String(v || '').trim();
    var m = v.match(/^(\d{2})-(\d{2})-(\d{2})(?:[ T](\d{2})[-:](\d{2})(?:[-:](\d{2}))?)?$/);
    if (m){
        var yy = +m[3];
        var year = yy >= 70 ? 1900 + yy : 2000 + yy;
        var d1 = new Date(year, +m[2]-1, +m[1], +(m[4]||23), +(m[5]||59), +(m[6]||0));
        return isNaN(d1) ? null : d1;
    }
    m = v.match(/^(\d{4})-(\d{2})-(\d{2})(?:[ T](\d{2})[-:](\d{2})(?:[-:](\d{2}))?)?$/);
    if (m){
        var d2 = new Date(+m[1], +m[2]-1, +m[3], +(m[4]||23), +(m[5]||59), +(m[6]||0));
        return isNaN(d2) ? null : d2;
    }
    return null;
}
function formatSaleDateDisplay(d){ return pad(d.getDate())+'-'+pad(d.getMonth()+1)+'-'+String(d.getFullYear()).slice(-2)+' '+pad(d.getHours())+'-'+pad(d.getMinutes())+'-'+pad(d.getSeconds()); }
function formatSaleDateISO(d){ return d.getFullYear()+'-'+pad(d.getMonth()+1)+'-'+pad(d.getDate())+' '+pad(d.getHours())+':'+pad(d.getMinutes())+':'+pad(d.getSeconds()); }
function syncDateHidden(){
    if (!endsAtEl || !endsAtHidden) return true;
    var raw = endsAtEl.value.trim();
    if (!raw){ endsAtHidden.value = ''; return true; }
    var d = parseSaleDate(raw);
    if (!d) return false;
    endsAtHidden.value = formatSaleDateISO(d);
    return true;
}
function fmtLeft(ms){
    if (ms <= 0) return 'Expired';
    var s = Math.floor(ms/1000);
    var d = Math.floor(s/86400); s -= d*86400;
    var h = Math.floor(s/3600); s -= h*3600;
    var m = Math.floor(s/60); s -= m*60;
    return d+'d '+pad(h)+'h '+pad(m)+'m '+pad(s)+'s';
}
function tickLeft(){
    if (!endsAtEl || !tlEl) return;
    var raw = endsAtEl.value.trim();
    if (!raw){ tlEl.textContent = ''; if (endsAtHidden) endsAtHidden.value = ''; return; }
    var d = parseSaleDate(raw);
    if (!d){ tlEl.textContent = 'Invalid date format'; return; }
    if (endsAtHidden) endsAtHidden.value = formatSaleDateISO(d);
    tlEl.textContent = '→ '+fmtLeft(d - Date.now());
}
if (endsAtEl){
    var initialDate = parseSaleDate(endsAtHidden ? endsAtHidden.value : endsAtEl.value);
    if (initialDate) { endsAtEl.value = formatSaleDateDisplay(initialDate); if (endsAtHidden) endsAtHidden.value = formatSaleDateISO(initialDate); }
    endsAtEl.addEventListener('input', function(){ tickLeft(); schedulePreview(); });
    tickLeft();
    setInterval(tickLeft, 1000);
}

// ── Custom date and time picker ──────────────────────────────────────────
(function(){
    var wrap = document.getElementById('sale_datetime_picker');
    var input = document.getElementById('sale_ends_at_display') || document.getElementById('sale_ends_at');
    var toggle = document.getElementById('sale_datetime_toggle');
    var pop = document.getElementById('sale_datetime_pop');
    var grid = document.getElementById('sale_cal_grid');
    var title = document.getElementById('sale_cal_title');
    var hour = document.getElementById('sale_cal_hour');
    var minute = document.getElementById('sale_cal_minute');
    var apply = document.getElementById('sale_cal_apply');
    var clear = document.getElementById('sale_cal_clear');
    if (!wrap || !input || !toggle || !pop || !grid || !title) return;

    var todayBase = new Date();
    todayBase.setHours(0,0,0,0);
    var nowBase = new Date();
    var selected = parseDate(input.value) || parseDate((endsAtHidden && endsAtHidden.value) || '') || nowBase;
    var view = new Date(nowBase.getFullYear(), nowBase.getMonth(), 1);

    function clamp(n,min,max){ n=parseInt(n,10); if(isNaN(n)) n=min; return Math.max(min, Math.min(max, n)); }
    function parseDate(v){ return parseSaleDate(v); }
    function formatDate(d){ return formatSaleDateDisplay(d); }
    function syncFromInput(){
        var now = new Date();
        var d = parseDate(input.value) || (endsAtHidden ? parseDate(endsAtHidden.value) : null) || selected || now;
        selected = d;
        // Always open the picker at the current month.
        view = new Date(now.getFullYear(), now.getMonth(), 1);
        if (hour) hour.value = pad(d.getHours());
        if (minute) minute.value = pad(d.getMinutes());
    }
    function render(){
        title.textContent = view.toLocaleString('en-US', { month:'long', year:'numeric' });
        grid.innerHTML = '';
        ['Mon','Tue','Wed','Thu','Fri','Sat','Sun'].forEach(function(w){
            var el = document.createElement('div'); el.className = 'bs-cal-weekday'; el.textContent = w; grid.appendChild(el);
        });
        var first = new Date(view.getFullYear(), view.getMonth(), 1);
        var startOffset = (first.getDay() + 6) % 7;
        var start = new Date(view.getFullYear(), view.getMonth(), 1 - startOffset);
        for (var i=0; i<42; i++){
            var d = new Date(start.getFullYear(), start.getMonth(), start.getDate()+i);
            var btn = document.createElement('button');
            btn.type = 'button';
            btn.className = 'bs-cal-day';
            if (d.getMonth() !== view.getMonth()) btn.className += ' is-muted';
            var dayMid = new Date(d.getFullYear(), d.getMonth(), d.getDate());
            var todayMid = new Date(); todayMid.setHours(0,0,0,0);
            if (dayMid.getTime() === todayMid.getTime()) btn.className += ' is-today';
            if (dayMid < todayMid) { btn.className += ' is-disabled'; btn.disabled = true; }
            if (selected && d.getFullYear()===selected.getFullYear() && d.getMonth()===selected.getMonth() && d.getDate()===selected.getDate()) btn.className += ' is-selected';
            btn.textContent = d.getDate();
            btn.addEventListener('click', function(day){ return function(){
                selected = new Date(day.getFullYear(), day.getMonth(), day.getDate(), clamp(hour && hour.value,0,23), clamp(minute && minute.value,0,59), 0);
                view = new Date(day.getFullYear(), day.getMonth(), 1);
                input.value = formatSaleDateDisplay(selected);
                if (endsAtHidden) endsAtHidden.value = formatSaleDateISO(selected);
                input.dispatchEvent(new Event('input', { bubbles:true }));
                tickLeft();
                schedulePreview();
                render();
                closePicker();
            };}(d));
            grid.appendChild(btn);
        }
    }
    function openPicker(){ syncFromInput(); render(); pop.classList.add('is-open'); }
    function closePicker(){ pop.classList.remove('is-open'); }
    toggle.addEventListener('click', function(e){ e.preventDefault(); pop.classList.contains('is-open') ? closePicker() : openPicker(); });
    pop.querySelector('[data-cal-prev]').addEventListener('click', function(){ var minV = new Date(new Date().getFullYear(), new Date().getMonth(), 1); var nextV = new Date(view.getFullYear(), view.getMonth()-1, 1); if (nextV < minV) nextV = minV; view = nextV; render(); });
    pop.querySelector('[data-cal-next]').addEventListener('click', function(){ view.setMonth(view.getMonth()+1); render(); });
    apply.addEventListener('click', function(e){
        if (e) e.preventDefault();
        if (!selected) selected = new Date();
        selected.setHours(clamp(hour && hour.value,0,23), clamp(minute && minute.value,0,59), 0, 0);
        if (selected < new Date()) { selected = new Date(Date.now() + 5 * 60000); selected.setSeconds(0,0); }
        input.value = formatSaleDateDisplay(selected);
        if (endsAtHidden) endsAtHidden.value = formatSaleDateISO(selected);
        input.dispatchEvent(new Event('input', { bubbles:true }));
        tickLeft();
        schedulePreview();
        closePicker();
    });
    clear.addEventListener('click', function(){ input.value=''; input.dispatchEvent(new Event('input', { bubbles:true })); tickLeft(); schedulePreview(); closePicker(); });
    document.addEventListener('click', function(e){ if (!wrap.contains(e.target)) closePicker(); });
    input.addEventListener('focus', openPicker);
})();

// ── Live preview ──────────────────────────────────────────────────────────
var timer = null;
function schedulePreview(){ clearTimeout(timer); timer = setTimeout(buildPreview, 130); }

function gv(name){
    var el = document.querySelector('[name="'+name+'"]');
    return el ? el.value : '';
}
function gvImg(hiddenId){
    // prefer data-URI from preview image (freshly uploaded), else stored URL
    var prev = document.getElementById(hiddenId + '_prev');
    var h = document.getElementById(hiddenId);
    var hv = h ? h.value : '';
    if (hv && /^(fa[srbl]?\s|fa-)/.test(hv)) return hv;
    if (prev && prev.classList.contains('show') && prev.src) return prev.src;
    return hv;
}
function esc(s){ return String(s||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;'); }

function accentPG(raw, col){
    return raw.replace(/\{ACCENT\}([\s\S]*?)\{\/ACCENT\}/g,'<span style="color:'+esc(col)+'">$1</span>');
}
function accentSale(raw, f, t){
    return raw.replace(/\{ACCENT\}([\s\S]*?)\{\/ACCENT\}/g,
        '<span style="background:linear-gradient(90deg,'+esc(f)+','+esc(t)+');-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;text-shadow:none;">$1</span>');
}

function buildPreview(){
    var type = typeInput.value;
    [
        { id: 'preview-frame-desktop', mode: 'desktop', min: 86 },
        { id: 'preview-frame-mobile',  mode: 'mobile',  min: 94 }
    ].forEach(function(item){
        var frame = document.getElementById(item.id);
        if (!frame) return;
        var html = renderHTML(type, item.mode);
        var doc = frame.contentDocument || frame.contentWindow.document;
        doc.open(); doc.write(html); doc.close();
        setTimeout(function(){
            try{ frame.style.height = Math.max(item.min, frame.contentDocument.body.scrollHeight)+'px'; }catch(e){}
        }, 100);
    });
}

function renderBannerHTML(type, mode){
    mode = mode || 'desktop';
    var font = '<link href="https://fonts.googleapis.com/css?family=Montserrat:700,800,900&display=swap" rel="stylesheet">';
    var base = '<style>*{margin:0;padding:0;box-sizing:border-box;font-family:"Montserrat",sans-serif;}body{overflow:hidden;}</style>';

    if (type === 'off'){
        return '<!DOCTYPE html><html><head>'+font+base+'</head><body style="background:#0d111e;display:flex;align-items:center;justify-content:center;height:64px;"><span style="color:rgba(255,255,255,.25);font-size:12px;letter-spacing:.08em;font-weight:700;text-transform:uppercase;">Banner disabled</span></body></html>';
    }

    if (type === 'progames'){
        var bgF = gv('banner_pg_bg_color_from')||'#0b0f1e';
        var bgT = gv('banner_pg_bg_color_to')||'#0b0f1e';
        var bgImg = gvImg('pg_bg_image');
        var bgOp  = parseFloat(gv('banner_pg_bg_image_opacity')||'0.18');
        var badgeText  = gv('banner_pg_badge_text')||'Pro Games';
        var badgeColor = gv('banner_pg_badge_color')||'#7c9bff';
        var badgeBorder= gv('banner_pg_badge_border')||'rgba(99,120,255,.30)';
        var iconUrl    = gvImg('pg_icon_url');
        var titleRaw   = gv('banner_pg_title')||'';
        var titleColor = gv('banner_pg_title_color')||'#fff';
        var accentColor= gv('banner_pg_accent_color')||'#7c9bff';
        var pills = [1,2,3,4,5].map(function(n){ return gv('banner_pg_pill_'+n); }).filter(Boolean);
        var ctaText  = gv('banner_pg_cta_text')||'Book a Session';
        var ctaBgF   = gv('banner_pg_cta_bg_from')||'#3d5afe';
        var ctaBgT   = gv('banner_pg_cta_bg_to')||'#6a1de8';
        var ctaColor = gv('banner_pg_cta_color')||'#fff';

        if (mode === 'mobile') {
            var mobTitle = gv('banner_pg_mob_title') || titleRaw.replace(/\{\/?ACCENT\}/g,'');
            var mobSub = gv('banner_pg_mob_sub') || '';
            var mobTitleColor = gv('banner_pg_mob_title_color') || titleColor;
            var mobSubColor = gv('banner_pg_mob_sub_color') || 'rgba(255,255,255,.45)';
            var mobCta = gv('banner_pg_mob_cta_text') || ctaText;
            var mobBg = bgImg ? '<div style="position:absolute;inset:0;background:url(\''+esc(bgImg)+'\') center/cover no-repeat;opacity:'+bgOp+';pointer-events:none;"></div>' : '';
            return '<!DOCTYPE html><html><head>'+font+base+'</head><body style="background:linear-gradient(90deg,'+esc(bgF)+','+esc(bgT)+');position:relative;overflow:hidden;">'
                + mobBg
                + '<div style="position:relative;z-index:1;min-height:86px;display:flex;align-items:center;gap:14px;padding:14px 16px;">'
                + '<div style="min-width:0;flex:1;display:flex;flex-direction:column;gap:4px;">'
                + '<div style="display:inline-flex;align-items:center;gap:6px;margin-bottom:1px;color:'+esc(badgeColor)+';font-size:10px;font-weight:900;letter-spacing:.14em;text-transform:uppercase;white-space:nowrap;"><span style="width:7px;height:7px;border-radius:50%;background:'+esc(badgeColor)+';"></span>'+esc(badgeText)+'</div>'
                + '<div style="font-size:15px;line-height:1.05;font-weight:900;color:'+esc(mobTitleColor)+';letter-spacing:.02em;text-transform:uppercase;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">'+esc(mobTitle)+'</div>'
                + (mobSub ? '<div style="font-size:10px;font-weight:700;color:'+esc(mobSubColor)+';white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">'+esc(mobSub)+'</div>' : '')
                + '</div>'
                + '<a href="#" style="display:inline-flex;align-items:center;justify-content:center;padding:12px 18px;border-radius:8px;font-size:12px;font-weight:900;color:'+esc(ctaColor)+';text-decoration:none;background:linear-gradient(135deg,'+esc(ctaBgF)+','+esc(ctaBgT)+');letter-spacing:.05em;text-transform:uppercase;white-space:nowrap;flex-shrink:0;">'+esc(mobCta)+'</a>'
                + '</div></body></html>';
        }

        var headline = accentPG(esc(titleRaw), accentColor);
        var pillsHtml = pills.map(function(p){
            return '<span style="font-size:11px;font-weight:700;color:rgba(255,255,255,.55);letter-spacing:.04em;text-transform:uppercase;white-space:nowrap;">'+esc(p)+'</span>';
        }).join('<span style="width:3px;height:3px;border-radius:50%;background:rgba(255,255,255,.2);display:inline-block;margin:0 6px;vertical-align:middle;flex-shrink:0;"></span>');
        var iconHtml = iconUrl ? '<img src="'+esc(iconUrl)+'" style="width:18px;height:18px;border-radius:4px;object-fit:cover;margin-right:5px;flex-shrink:0;">' : '';
        var bgImgHtml = bgImg ? '<div style="position:absolute;inset:0;background:url(\''+esc(bgImg)+'\') center/cover no-repeat;opacity:'+bgOp+';pointer-events:none;"></div>' : '';

        return '<!DOCTYPE html><html><head>'+font+base+'</head><body style="background:linear-gradient(90deg,'+esc(bgF)+','+esc(bgT)+');position:relative;overflow:hidden;">'
            + bgImgHtml
            + '<div style="position:relative;z-index:1;display:flex;align-items:center;justify-content:space-between;padding:0 28px;min-height:76px;gap:16px;">'
            + '<div style="display:inline-flex;align-items:center;gap:7px;padding:7px 14px;border-radius:8px;background:rgba(99,120,255,.12);border:1px solid '+esc(badgeBorder)+';flex-shrink:0;">'
            + '<span style="width:8px;height:8px;border-radius:50%;background:'+esc(badgeColor)+';flex-shrink:0;"></span>'
            + iconHtml
            + '<span style="font-size:13px;font-weight:800;color:'+esc(badgeColor)+';text-transform:uppercase;letter-spacing:.12em;white-space:nowrap;">'+esc(badgeText)+'</span>'
            + '</div>'
            + '<div style="display:flex;flex-direction:column;align-items:center;gap:5px;flex:1;min-width:0;">'
            + '<div style="font-size:17px;font-weight:900;color:'+esc(titleColor)+';text-transform:uppercase;letter-spacing:.08em;white-space:nowrap;text-align:center;">'+headline+'</div>'
            + (pillsHtml ? '<div style="display:flex;align-items:center;flex-wrap:nowrap;gap:0;justify-content:center;">'+pillsHtml+'</div>' : '')
            + '</div>'
            + '<a href="#" style="display:inline-flex;align-items:center;gap:8px;padding:11px 26px;border-radius:9px;font-size:13px;font-weight:800;color:'+esc(ctaColor)+';text-decoration:none;background:linear-gradient(135deg,'+esc(ctaBgF)+','+esc(ctaBgT)+');letter-spacing:.06em;text-transform:uppercase;white-space:nowrap;flex-shrink:0;">'+esc(ctaText)+'</a>'
            + '</div></body></html>';
    }

    if (type === 'sale'){
        var bgF2 = gv('banner_sale_bg_color_from')||'#0b0f1e';
        var bgT2 = gv('banner_sale_bg_color_to')||'#0b0f1e';
        var bgImgL = gvImg('sale_bg_image_left');
        var bgImgR = gvImg('sale_bg_image_right');
        var bgOp2  = parseFloat(gv('banner_sale_bg_image_opacity')||'0.5');
        var hl2    = accentSale(esc(gv('banner_sale_headline')||''), gv('banner_sale_accent_from')||'#f59e0b', gv('banner_sale_accent_to')||'#ef4444');
        var tc2    = gv('banner_sale_title_color')||'#fff';
        var endsAt = gv('banner_sale_ends_at');
        var cdLabel= gv('banner_sale_countdown_label')||'ENDS IN';
        var cdColor= gv('banner_sale_countdown_color')||'#fff';
        var cdUbg  = gv('banner_sale_countdown_unit_bg')||'rgba(245,158,11,.08)';
        var cdBrd  = gv('banner_sale_countdown_border')||'rgba(245,158,11,.30)';
        var c1t = gv('banner_sale_cta_1_text'), c1l = gv('banner_sale_cta_1_link')||'#';
        var c1f = gv('banner_sale_cta_1_bg_from')||'#d97706', c1to= gv('banner_sale_cta_1_bg_to')||'#b45309';
        var c1i = gvImg('sale_cta_1_icon');

        if (mode === 'mobile') {
            var mobPct = gv('banner_sale_mob_pct') || '20% OFF';
            var mobPctFrom = gv('banner_sale_mob_pct_color_from') || gv('banner_sale_accent_from') || '#f59e0b';
            var mobPctTo = gv('banner_sale_mob_pct_color_to') || gv('banner_sale_accent_to') || '#ef4444';
            var mobLabel = gv('banner_sale_mob_label') || 'New Season Sale';
            var mobLabelColor = gv('banner_sale_mob_label_color') || 'rgba(255,255,255,.45)';
            var mobBgL = bgImgL ? '<div style="position:absolute;inset:0;right:35%;background:url(\''+esc(bgImgL)+'\') center/cover;-webkit-mask-image:linear-gradient(to right,black 35%,transparent 100%);mask-image:linear-gradient(to right,black 35%,transparent 100%);opacity:'+bgOp2+';"></div>' : '';
            var mobBgR = bgImgR ? '<div style="position:absolute;inset:0;left:35%;background:url(\''+esc(bgImgR)+'\') center/cover;-webkit-mask-image:linear-gradient(to left,black 35%,transparent 100%);mask-image:linear-gradient(to left,black 35%,transparent 100%);opacity:'+bgOp2+';"></div>' : '';
            return '<!DOCTYPE html><html><head>'+font+base+'</head><body style="background:linear-gradient(90deg,'+esc(bgF2)+','+esc(bgT2)+');position:relative;overflow:hidden;">'
                + mobBgL + mobBgR
                + '<div style="position:absolute;inset:0;background:linear-gradient(90deg,rgba(0,0,0,.78),rgba(0,0,0,.42),rgba(0,0,0,.78));"></div>'
                + '<div style="position:relative;z-index:1;min-height:86px;display:flex;align-items:center;gap:12px;padding:14px 16px;">'
                + '<div style="min-width:0;flex:1;display:flex;flex-direction:column;gap:4px;">'
                + '<div style="font-size:18px;line-height:1;font-weight:900;text-transform:uppercase;letter-spacing:.04em;background:linear-gradient(90deg,'+esc(mobPctFrom)+','+esc(mobPctTo)+');-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">'+esc(mobPct)+'</div>'
                + '<div style="font-size:10px;font-weight:800;color:'+esc(mobLabelColor)+';text-transform:uppercase;letter-spacing:.08em;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">'+esc(mobLabel)+'</div>'
                + '</div>'
                + '<a href="#" style="display:inline-flex;align-items:center;justify-content:center;padding:12px 18px;border-radius:8px;font-size:12px;font-weight:900;color:#fff;text-decoration:none;background:linear-gradient(135deg,'+esc(c1f)+','+esc(c1to)+');letter-spacing:.05em;text-transform:uppercase;white-space:nowrap;flex-shrink:0;">'+esc(c1t || c2t || 'Shop Now')+'</a>'
                + '</div></body></html>';
        }

        // Countdown
        var cdHtml = '';
        if (endsAt){
            var d = new Date(endsAt.replace(' ','T'));
            if (!isNaN(d)){
                var diff = Math.max(0, Math.floor((d - Date.now())/1000));
                var dd = Math.floor(diff/86400); diff -= dd*86400;
                var hh = Math.floor(diff/3600); diff -= hh*3600;
                var mm = Math.floor(diff/60); diff -= mm*60;
                var ss = diff;
                var uStyle = 'display:flex;flex-direction:column;align-items:center;background:'+cdUbg+';border:1px solid '+cdBrd+';border-radius:6px;padding:3px 10px;min-width:42px;';
                var nStyle = 'font-size:19px;font-weight:900;color:'+cdColor+';line-height:1;font-variant-numeric:tabular-nums;';
                var lStyle = 'font-size:8px;font-weight:800;color:rgba(245,158,11,.7);text-transform:uppercase;letter-spacing:.1em;margin-top:2px;';
                var sep2   = '<span style="font-size:19px;font-weight:900;color:rgba(245,158,11,.5);margin-bottom:10px;line-height:1;">:</span>';
                var unit2  = function(v,u){ return '<div style="'+uStyle+'"><span style="'+nStyle+'">'+v+'</span><span style="'+lStyle+'">'+u+'</span></div>'; };
                cdHtml = '<div style="display:flex;flex-direction:column;align-items:center;gap:3px;padding:0 14px;flex-shrink:0;">'
                    +'<div style="font-size:8px;font-weight:800;letter-spacing:.18em;text-transform:uppercase;color:rgba(245,158,11,.6);">'+esc(cdLabel)+'</div>'
                    +'<div style="display:flex;align-items:center;gap:4px;">'+unit2(String(dd),'D')+sep2+unit2(pad(hh),'H')+sep2+unit2(pad(mm),'M')+sep2+unit2(pad(ss),'S')+'</div>'
                    +'</div>';
            }
        }

        var ctaBtn = function(txt,bgf,bgt,icon){ if(!txt) return '';
            return '<a href="#" style="display:inline-flex;align-items:center;gap:7px;padding:10px 20px;border-radius:7px;font-size:12px;font-weight:900;color:#fff;text-decoration:none;background:linear-gradient(135deg,'+esc(bgf)+','+esc(bgt)+');letter-spacing:.08em;text-transform:uppercase;white-space:nowrap;position:relative;overflow:hidden;">'
                + (icon ? (/^fa[srbl]?\s|^fa-/.test(icon) ? '<i class="'+esc(icon)+'" style="font-size:15px;line-height:1;"></i>' : '<img src="'+esc(icon)+'" style="width:18px;height:18px;border-radius:4px;object-fit:cover;flex-shrink:0;">') : '')
                + esc(txt)+'</a>';
        };

        var bgLH = bgImgL ? '<div style="position:absolute;inset:0;right:50%;background:url(\''+esc(bgImgL)+'\') center/cover;-webkit-mask-image:linear-gradient(to right,black 40%,transparent 100%);mask-image:linear-gradient(to right,black 40%,transparent 100%);opacity:'+bgOp2+';pointer-events:none;"></div>' : '';
        var bgRH = bgImgR ? '<div style="position:absolute;inset:0;left:50%;background:url(\''+esc(bgImgR)+'\') center/cover;-webkit-mask-image:linear-gradient(to left,black 40%,transparent 100%);mask-image:linear-gradient(to left,black 40%,transparent 100%);opacity:'+bgOp2+';pointer-events:none;"></div>' : '';

        return '<!DOCTYPE html><html><head>'+font+base+'</head><body style="background:linear-gradient(90deg,'+esc(bgF2)+','+esc(bgT2)+');position:relative;overflow:hidden;">'
            + bgLH + bgRH
            + '<div style="position:absolute;inset:0;background:linear-gradient(90deg,rgba(0,0,0,.75) 0%,rgba(0,0,0,.4) 30%,rgba(0,0,0,.4) 70%,rgba(0,0,0,.75) 100%);pointer-events:none;"></div>'
            + '<div style="position:relative;z-index:1;display:flex;align-items:center;justify-content:center;padding:0 36px;min-height:64px;gap:24px;max-width:1400px;margin:0 auto;">'
            + '<div style="display:flex;flex-direction:column;gap:2px;flex-shrink:0;">'
            + '<div style="font-size:15px;font-weight:900;color:'+esc(tc2)+';text-transform:uppercase;letter-spacing:.08em;white-space:nowrap;">'+hl2+'</div>'
            + (sub2 ? '<div style="font-size:11px;font-weight:600;color:'+esc(subClr)+';white-space:nowrap;">'+esc(sub2)+'</div>' : '')
            + '</div>'
            + cdHtml
            + '<div style="display:flex;gap:10px;flex-shrink:0;">'
            + ctaBtn(c1t,c1f,c1to,c1i)+ctaBtn(c2t,c2f,c2to,c2i)
            + '</div>'
            + '</div></body></html>';
    }

    return '<!DOCTYPE html><html><head>'+font+base+'</head><body style="background:#111;"></body></html>';
}


function stripTagsForEdit(html){
    var tmp = document.createElement('div');
    tmp.innerHTML = html;
    return tmp.textContent || tmp.innerText || '';
}
function editableText(name, html, cls){
    return '<span class="pv-edit '+(cls||'')+'" data-edit="'+esc(name)+'" contenteditable="true" spellcheck="false">'+html+'</span>';
}
function editScript(){
    return '<script>(function(){'
        + 'function sync(el,saveNow){parent.postMessage({bannerPreviewEdit:{name:el.dataset.edit,value:el.innerText.trim(),save:!!saveNow}},"*");}'
        + 'document.querySelectorAll(".pv-edit").forEach(function(el){'
        + 'el.addEventListener("keydown",function(e){if(e.key==="Enter"){e.preventDefault();sync(el,false);el.blur();}});'
        + 'el.addEventListener("input",function(){sync(el,false);});'
        + 'el.addEventListener("blur",function(){sync(el,true);});'
        + '});'
        + 'var banner=document.getElementById("lbSaleBanner"); var mode=banner?(banner.classList.contains("is-mobile")?"mobile":"desktop"):"desktop";'
        + 'document.querySelectorAll("[data-bg-side]").forEach(function(layer){var dragging=false,startX=0,startY=0,baseX=50,baseY=50;function cur(){var m=(layer.style.backgroundPosition||"50% 50%").match(/([0-9.]+)%\\s+([0-9.]+)%/);return m?{x:parseFloat(m[1]),y:parseFloat(m[2])}:{x:50,y:50};}layer.addEventListener("pointerdown",function(e){dragging=true;try{layer.setPointerCapture(e.pointerId);}catch(_){}startX=e.clientX;startY=e.clientY;var c=cur();baseX=c.x;baseY=c.y;e.preventDefault();});layer.addEventListener("pointermove",function(e){if(!dragging||!banner)return;var rect=banner.getBoundingClientRect();var nx=Math.max(0,Math.min(100,baseX+(e.clientX-startX)/Math.max(1,rect.width)*100));var ny=Math.max(0,Math.min(100,baseY+(e.clientY-startY)/Math.max(1,rect.height)*100));layer.style.backgroundPosition=nx+"% "+ny+"%";parent.postMessage({bannerPreviewPosition:{side:layer.dataset.bgSide,mode:mode,x:nx,y:ny}},"*");});layer.addEventListener("pointerup",function(e){dragging=false;try{layer.releasePointerCapture(e.pointerId);}catch(_){}});});'
        + '})();<\/script>';
}
function editCSS(){
    return '.pv-edit{position:relative;border-radius:6px;outline:0;cursor:text;transition:.14s;}'
        + '.pv-edit:hover{box-shadow:0 0 0 1px rgba(124,155,255,.65),0 0 0 4px rgba(124,155,255,.14);background:rgba(124,155,255,.08);}'
        + '.pv-edit:hover:after{content:"\\270E";position:absolute;right:-26px;top:50%;transform:translateY(-50%);width:20px;height:20px;border-radius:50%;display:flex;align-items:center;justify-content:center;background:#625cff;color:#fff;font-size:11px;box-shadow:0 8px 18px rgba(0,0,0,.35);}'
        + '.pv-edit:focus{box-shadow:0 0 0 2px rgba(124,155,255,.95),0 0 0 6px rgba(124,155,255,.18);background:rgba(124,155,255,.12);}'
        + '.pv-edit:focus:after{content:"";}';
}
function saleCountdownHTML(compact){
    var endsAt = gv('banner_sale_ends_at');
    if (!endsAt) return '';
    var d = new Date(endsAt.replace(' ', 'T'));
    if (isNaN(d)) return '';
    var diff = Math.max(0, Math.floor((d - Date.now()) / 1000));
    var dd = Math.floor(diff / 86400); diff -= dd * 86400;
    var hh = Math.floor(diff / 3600); diff -= hh * 3600;
    var mm = Math.floor(diff / 60); diff -= mm * 60;
    var ss = diff;
    var label = esc(gv('banner_sale_countdown_label') || 'ENDS IN');
    var color = esc(gv('banner_sale_countdown_color') || '#fff');
    var bg = esc(gv('banner_sale_countdown_unit_bg') || 'rgba(245,158,11,.08)');
    var border = esc(gv('banner_sale_countdown_border') || 'rgba(245,158,11,.30)');
    function unit(v, u){
        return '<div class="cd-unit" style="background:'+bg+';border-color:'+border+'"><strong style="color:'+color+'">'+v+'</strong><small>'+u+'</small></div>';
    }
    if (compact){
        return '<span class="sale-mobile-timer">'+label+' '+String(dd)+'D · '+pad(hh)+'H · '+pad(mm)+'M · '+pad(ss)+'S</span>';
    }
    return '<div class="cd cd-line"><span>'+label+'</span><strong>'+String(dd)+'D</strong><i>·</i><strong>'+pad(hh)+'H</strong><i>·</i><strong>'+pad(mm)+'M</strong><i>·</i><strong>'+pad(ss)+'S</strong></div>';
}
function renderPreviewTopBanner(type, mode){
    if (type === 'off') return '';
    var bgF = gv('banner_sale_bg_color_from') || '#0b0f1e';
    var bgT = gv('banner_sale_bg_color_to') || '#0b0f1e';
    var bgL = gvImg('sale_bg_image_left');
    var bgR = gvImg('sale_bg_image_right');
    var bgOp = Math.max(0, Math.min(1, parseFloat(gv('banner_sale_bg_image_opacity') || '0.5') || 0));
    var suffix = mode === 'mobile' ? 'mobile' : 'desktop';
    var lX = gv('banner_sale_bg_left_pos_x_' + suffix) || '50';
    var lY = gv('banner_sale_bg_left_pos_y_' + suffix) || '50';
    var rX = gv('banner_sale_bg_right_pos_x_' + suffix) || '50';
    var rY = gv('banner_sale_bg_right_pos_y_' + suffix) || '50';
    var title = accentSale(esc(gv('banner_sale_headline') || 'NEW SEASON {ACCENT}SALE{/ACCENT}'), gv('banner_sale_accent_from') || '#f59e0b', gv('banner_sale_accent_to') || '#ef4444');
    var accent = gv('banner_sale_accent_from') || '#f59e0b';
    var accentTo = gv('banner_sale_accent_to') || '#ef4444';
    var titleColor = gv('banner_sale_title_color') || '#fff';
    var cdBorder = gv('banner_sale_countdown_border') || 'rgba(245,158,11,.30)';
    var ctaText = esc(gv('banner_sale_cta_1_text') || 'TFT Boost');
    var ctaFrom = gv('banner_sale_cta_1_bg_from') || '#d97706';
    var ctaTo = gv('banner_sale_cta_1_bg_to') || '#b45309';
    var ctaIcon = gvImg('sale_cta_1_icon');
    var ctaIconHtml = '';
    if (ctaIcon && /^fa-/.test(ctaIcon)) ctaIcon = 'fas ' + ctaIcon;
    if (ctaIcon && /^(fa[srbl]?\s|fa-)/.test(ctaIcon)) ctaIconHtml = '<i class="'+esc(ctaIcon)+'"></i>';
    else if (ctaIcon) ctaIconHtml = '<img class="lbSalePreview-cta-icon" src="'+esc(ctaIcon)+'" alt="">';
    var bgLayers = '';
    if (bgL) bgLayers += '<div class="lbNew-bg-tft" data-bg-side="left" aria-hidden="true" style="pointer-events:auto;cursor:move;background-image:url(\''+esc(bgL)+'\');background-position:'+esc(lX)+'% '+esc(lY)+'%;opacity:'+bgOp+';"></div>';
    if (bgR) bgLayers += '<div class="lbNew-bg-val" data-bg-side="right" aria-hidden="true" style="pointer-events:auto;cursor:move;background-image:url(\''+esc(bgR)+'\');background-position:'+esc(rX)+'% '+esc(rY)+'%;opacity:'+bgOp+';"></div>';
    bgLayers += '<div class="lbNew-bg-overlay" aria-hidden="true"></div>';

    if (mode === 'mobile'){
        var mobTitle = esc(gv('banner_sale_mob_pct') || '20% OFF');
        return '<div id="lbSaleBanner" class="preview-sale-banner is-mobile" role="region">'
            + '<div class="lb-sale-bar lbSalePG-bar" style="background:linear-gradient(90deg,'+esc(bgF)+','+esc(bgT)+');">'
            + bgLayers
            + '<div class="lb-sale-mobileRow">'
            + '<div class="lbNew-mob-left" style="min-width:0;flex:1;">'
            + '<div class="lbNew-mob-texts" style="min-width:0;">'
            + '<div class="lbNew-mob-pct" style="background:linear-gradient(90deg,'+esc(accent)+','+esc(accentTo)+');-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;">'+editableText('banner_sale_mob_pct', mobTitle)+'</div>'
            + saleCountdownMobilePreview()
            + '</div></div>'
            + '<a class="lbSalePG-mobile-cta" href="#" style="background:linear-gradient(135deg,'+esc(ctaFrom)+','+esc(ctaTo)+');">'+ctaIconHtml+editableText('banner_sale_cta_1_text', ctaText)+'</a>'
            + '<button class="lb-sale-close" type="button" aria-label="Close banner">✕</button>'
            + '</div></div></div>';
    }

    return '<div id="lbSaleBanner" class="preview-sale-banner" role="region">'
        + '<div class="lb-sale-bar lbSalePG-bar" style="background:linear-gradient(90deg,'+esc(bgF)+','+esc(bgT)+');">'
        + bgLayers
        + '<div class="lbPG-desktop lb-sale-desktopRow lbSalePG-desktop">'
        + '<div class="lbPG-left"><div class="lbPG-badge lbSalePG-badge" style="border-color:'+esc(cdBorder)+';">'
        + '<span class="lbPG-badge-ring lbSalePG-badge-ring" style="background:'+esc(accent)+';box-shadow:0 0 0 2px '+esc(accent)+'44;"></span>'
        + '<span class="lbPG-badge-text lbSalePG-badge-text" style="color:'+esc(accent)+';">'+editableText('banner_sale_mob_label', 'Sale')+'</span>'
        + '</div></div>'
        + '<div class="lbPG-center lbSalePG-center"><div class="lbPG-title lbSalePG-title" style="color:'+esc(titleColor)+';">'+editableText('banner_sale_headline', title)+'</div>'
        + saleCountdownDesktopPreview(accent)
        + '</div>'
        + '<div class="lbPG-right"><a class="lbPG-cta lbSalePG-cta" href="#" style="background:linear-gradient(135deg,'+esc(ctaFrom)+','+esc(ctaTo)+');color:#fff;">'+ctaIconHtml+'<span>'+editableText('banner_sale_cta_1_text', ctaText)+'</span><svg width="14" height="14" viewBox="0 0 14 14" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true"><path d="M2.5 7h9M8 3.5l3.5 3.5-3.5 3.5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg></a><button class="lbPG-close lb-sale-close" type="button" aria-label="Close banner">✕</button></div>'
        + '</div></div></div>';
}
function saleCountdownParts(){
    var endsAt = gv('banner_sale_ends_at');
    if (!endsAt) return null;
    var d = parseSaleDate(endsAt);
    if (!d) return null;
    var diff = Math.max(0, Math.floor((d - Date.now()) / 1000));
    var dd = Math.floor(diff / 86400); diff -= dd * 86400;
    var hh = Math.floor(diff / 3600); diff -= hh * 3600;
    var mm = Math.floor(diff / 60); diff -= mm * 60;
    var ss = diff;
    return {d:String(dd), h:pad(hh), m:pad(mm), s:pad(ss), label:esc(gv('banner_sale_countdown_label') || 'ENDS IN')};
}
function saleCountdownDesktopPreview(accent){
    var p = saleCountdownParts();
    if (!p) return '';
    return '<div class="lbSalePG-countdown-line">'
      + '<span class="lbSalePG-countdown-label">'+p.label+'</span>'
      + '<span class="lbSalePG-countdown-time">'
      + '<strong style="color:'+esc(accent)+';">'+p.d+'</strong>D<span>•</span>'
      + '<strong style="color:'+esc(accent)+';">'+p.h+'</strong>H<span>•</span>'
      + '<strong style="color:'+esc(accent)+';">'+p.m+'</strong>M<span>•</span>'
      + '<strong style="color:'+esc(accent)+';">'+p.s+'</strong>S'
      + '</span></div>';
}
function saleCountdownMobilePreview(){
    var p = saleCountdownParts();
    if (!p) return '<div class="lbNew-mob-label">'+editableText('banner_sale_mob_label', esc(gv('banner_sale_mob_label') || 'New Season Sale'))+'</div>';
    return '<div class="lbSalePG-mobile-timer">'+p.label+' <strong>'+p.d+'</strong>D<span class="sep">•</span><strong>'+p.h+'</strong>H<span class="sep">•</span><strong>'+p.m+'</strong>M<span class="sep">•</span><strong>'+p.s+'</strong>S</div>';
}
function renderHTML(type, mode){
    var font = '<link href="https://fonts.googleapis.com/css?family=Montserrat:500,600,700,800,900&display=swap" rel="stylesheet"><link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">';
    var base = '<style>*{box-sizing:border-box}html,body{margin:0;width:100%;height:100%;overflow:hidden;font-family:Montserrat,Arial,sans-serif;background:#090b19;color:#fff}'+editCSS()+stylesForSaleBlocks()+'</style>';
    return '<!DOCTYPE html><html><head>'+font+base+'</head><body>'+renderPreviewTopBanner('sale', mode)+editScript()+'</body></html>';
}
function stylesForSaleBlocks(){
    return '#lbSaleBanner,#lbSaleBanner *{font-family:Montserrat,Arial,sans-serif!important}#lbSaleBanner{width:100%;position:relative;overflow:hidden}#lbSaleBanner .lb-sale-bar{position:relative;overflow:hidden;display:flex;align-items:stretch;box-shadow:inset 0 -1px 0 rgba(255,255,255,.05)}'
    + '#lbSaleBanner .lbSalePG-bar{min-height:82px;background:#0b0f1e;border-bottom:1px solid rgba(245,158,11,.22);overflow:hidden;width:100%}#lbSaleBanner .lbSalePG-bar:before{content:"";position:absolute;inset:0;pointer-events:none;background:radial-gradient(ellipse 48% 120% at 16% 50%,rgba(245,158,11,.11) 0%,transparent 70%),radial-gradient(ellipse 48% 120% at 86% 50%,rgba(239,68,68,.10) 0%,transparent 72%)}'
    + '.lbNew-bg-tft{position:absolute;inset:0;right:50%;background-size:cover;background-position:center center;background-repeat:no-repeat;-webkit-mask-image:linear-gradient(to right,black 40%,transparent 100%);mask-image:linear-gradient(to right,black 40%,transparent 100%);pointer-events:none}.lbNew-bg-val{position:absolute;inset:0;left:50%;background-size:cover;background-position:center center;background-repeat:no-repeat;-webkit-mask-image:linear-gradient(to left,black 40%,transparent 100%);mask-image:linear-gradient(to left,black 40%,transparent 100%);pointer-events:none}.lbNew-bg-overlay{position:absolute;inset:0;background:linear-gradient(90deg,rgba(0,0,0,.80) 0%,rgba(0,0,0,.45) 30%,rgba(0,0,0,.45) 70%,rgba(0,0,0,.80) 100%);pointer-events:none}'
    + '#lbSaleBanner .lbPG-desktop{display:flex;width:100%;align-items:center;justify-content:space-between;padding:0 42px;gap:24px;position:relative;z-index:2;min-height:96px;max-width:100%;margin:0 auto;box-sizing:border-box}#lbSaleBanner .lbPG-left{display:flex;align-items:center;gap:10px;flex-shrink:0}#lbSaleBanner .lbPG-badge{display:flex;align-items:center;gap:6px;background:rgba(99,120,255,.12);border:1px solid rgba(99,120,255,.30);border-radius:8px;padding:7px 16px}#lbSaleBanner .lbPG-badge-ring{width:8px;height:8px;border-radius:50%;background:#5d7eff;box-shadow:0 0 0 2px rgba(93,126,255,.3);flex-shrink:0}#lbSaleBanner .lbPG-badge-text{font-size:14px;font-weight:800;color:#7c9bff;text-transform:uppercase;letter-spacing:.12em;white-space:nowrap}'
    + '#lbSaleBanner .lbSalePG-badge{background:rgba(245,158,11,.10);border-color:rgba(245,158,11,.30)}#lbSaleBanner .lbSalePG-badge-text{color:#f59e0b}#lbSaleBanner .lbPG-center{display:flex;flex-direction:column;align-items:center;gap:5px;flex:1;min-width:0;overflow:hidden}#lbSaleBanner .lbPG-title{font-size:21px;font-weight:900;color:#fff;text-transform:uppercase;letter-spacing:.10em;white-space:nowrap;line-height:1.1;max-width:100%;overflow:hidden;text-overflow:ellipsis}#lbSaleBanner .lbSalePG-title{text-shadow:0 0 20px rgba(245,158,11,.18)}'
    + '#lbSaleBanner .lbSalePG-countdown-line{display:inline-flex;align-items:center;justify-content:center;gap:7px;font-size:12px;font-weight:800;color:rgba(255,255,255,.55);letter-spacing:.045em;text-transform:uppercase;white-space:nowrap;font-variant-numeric:tabular-nums;max-width:100%;overflow:hidden;text-overflow:ellipsis}#lbSaleBanner .lbSalePG-countdown-label{color:rgba(245,158,11,.78);font-weight:900;letter-spacing:.12em}#lbSaleBanner .lbSalePG-countdown-time{display:inline-flex;align-items:center;gap:5px}#lbSaleBanner .lbSalePG-countdown-time strong{font-weight:1000;min-width:2ch;text-align:right}#lbSaleBanner .lbSalePG-countdown-time span{color:rgba(255,255,255,.24)}'
    + '#lbSaleBanner .lbPG-right{display:flex;align-items:center;gap:12px;flex-shrink:0}#lbSaleBanner .lbPG-cta{display:inline-flex;align-items:center;gap:8px;padding:13px 30px;border-radius:9px;font-size:13px;font-weight:800;color:#fff;text-decoration:none;text-transform:uppercase;letter-spacing:.06em;white-space:nowrap;flex-shrink:0;box-shadow:0 10px 24px rgba(245,158,11,.22),inset 0 1px 0 rgba(255,255,255,.15)}#lbSaleBanner .lbPG-cta svg{flex-shrink:0}#lbSaleBanner .lbPG-close,#lbSaleBanner .lb-sale-close{display:flex;align-items:center;justify-content:center;width:28px;height:28px;border-radius:50%;background:rgba(255,255,255,.08);border:1px solid rgba(255,255,255,.18);color:rgba(255,255,255,.7);font-size:13px;cursor:pointer;padding:0;flex-shrink:0;line-height:1}.lbSalePreview-cta-icon{width:18px;height:18px;border-radius:5px;object-fit:cover;flex-shrink:0}#lbSaleBanner .lbPG-cta i,#lbSaleBanner .lbSalePG-mobile-cta i{font-size:14px;line-height:1;}'
    + '#lbSaleBanner .lb-sale-mobileRow{display:flex;align-items:center;width:100%;padding:0 14px;gap:10px;position:relative;z-index:2;min-height:52px}#lbSaleBanner.is-mobile .lbSalePG-bar{min-height:52px;border-top-width:2px}#lbSaleBanner.is-mobile .lbNew-bg-tft,#lbSaleBanner.is-mobile .lbNew-bg-val{opacity:.6}#lbSaleBanner .lbNew-mob-left{display:flex;align-items:center;gap:8px;flex-shrink:0}#lbSaleBanner .lbNew-mob-texts{display:flex;flex-direction:column;gap:1px}#lbSaleBanner .lbNew-mob-pct{font-size:15px;font-weight:900;letter-spacing:.05em;text-transform:uppercase;background:linear-gradient(90deg,#f59e0b,#ef4444);-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}#lbSaleBanner .lbNew-mob-label{font-size:10px;font-weight:700;color:rgba(255,255,255,.45);white-space:nowrap;overflow:hidden;text-overflow:ellipsis}'
    + '#lbSaleBanner .lbSalePG-mobile-timer{display:block;margin-top:2px;font-size:11px;line-height:1.25;font-weight:800;color:rgba(255,255,255,.48);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;font-variant-numeric:tabular-nums}#lbSaleBanner .lbSalePG-mobile-timer strong{color:#f59e0b;font-weight:1000}#lbSaleBanner .lbSalePG-mobile-timer .sep{color:rgba(255,255,255,.24);padding:0 3px}#lbSaleBanner .lbSalePG-mobile-cta{display:inline-flex;align-items:center;justify-content:center;gap:7px;padding:11px 18px;border-radius:8px;font-size:12px;font-weight:900;color:#fff;text-decoration:none;letter-spacing:.05em;text-transform:uppercase;white-space:nowrap;flex-shrink:0;box-shadow:0 10px 24px rgba(245,158,11,.22),inset 0 1px 0 rgba(255,255,255,.15)}#lbSaleBanner .lb-sale-mobileRow .lb-sale-close{width:24px;height:24px;background:rgba(255,255,255,.12);border:1px solid rgba(255,255,255,.25);font-size:11px;color:rgba(255,255,255,.85)}';
}
function renderHeroShell(mode){
    if (mode === 'mobile'){
        return '<main class="page"><div class="stars"></div><div class="grid"></div><nav class="nav"><div class="hamb"></div><div class="logo"></div><div class="mlogin">Login</div></nav><section class="hero"><div class="tabs"><div class="tab active">LoL</div><div class="tab">VAL</div><div class="tab">TFT</div></div><div class="eyebrow">Premium Boosting Services</div><div class="h1">Rank Up in <b>League of Legends</b></div><div class="sub">Premium Rank Boosting, Coaching & Hand-Leveled Accounts powered by Challenger & Grandmaster...</div><div class="actions"><a class="btn1">🚀 Buy Boosting</a><a class="btn2">⚔ LoL Accounts</a></div><div class="rating"><span>★★★★★</span> Rated 4.9 by over 1000+ reviews</div><div class="bigtext">LEAGUE<br>OF LEGENDS</div><div class="char"></div><div class="chat"></div></section></main>';
    }
    return '<main class="page"><div class="stars"></div><div class="grid"></div><nav class="nav"><div class="logo"></div><div class="navlinks"><span>Games⌄</span><span>Boosters</span><span>Gamer Girls</span><span>Loyalty</span><span>Blog</span><span>Reviews</span></div><div class="navright"><div class="pill-dark">🇬🇧&nbsp; English / EUR⌄</div><div class="login">↪ Login</div></div></nav><section class="hero"><div><div class="tabs"><div class="tab active">🏅 League of Legends</div><div class="tab">🔻 Valorant</div><div class="tab">🏆 Teamfight Tactics</div></div><div class="eyebrow">Premium Boosting Services</div><div class="h1">Rank Up in <b>League of Legends</b></div><div class="sub">Premium Rank Boosting, Coaching & Hand-Leveled Accounts, powered by Challenger & Grandmaster players. Safe, fast, guaranteed.</div><div class="actions"><a class="btn1">🚀 Buy Boosting</a><a class="btn2">⚔ LoL Accounts</a></div><div class="rating"><span>★★★★★</span> Rated 4.9 by over 1000+ reviews</div></div><div class="art"><div class="bigtext">LEAGUE<br>OF LEGENDS</div><div class="char"></div><div class="bubble b1">🛡️&nbsp; 142 Online</div><div class="bubble b2">🏆&nbsp; 18 Hours</div><div class="bubble b3">⭐&nbsp; 4.9 / 5.0</div><div class="chat"></div></div></section></main>';
}


// ── Focused desktop/mobile editor mode ───────────────────────────────────
function setEditorMode(mode){
    mode = mode === 'mobile' ? 'mobile' : 'desktop';
    document.body.setAttribute('data-editor-mode', mode);
    document.querySelectorAll('.editor-mode-btn[data-editor-mode]').forEach(function(btn){
        btn.classList.toggle('is-active', btn.dataset.editorMode === mode);
    });
    try { localStorage.setItem('lb_banner_editor_mode', mode); } catch(e) {}
    schedulePreview();
}
function classifyEditorSections(){
    var salePanel = document.getElementById('panel-sale');
    if (!salePanel) return;
    salePanel.querySelectorAll('.bs-section').forEach(function(sec){
        var head = (sec.querySelector('.bs-head') ? sec.querySelector('.bs-head').textContent : '').toLowerCase();
        sec.classList.remove('editor-setting-desktop','editor-setting-mobile','editor-setting-shared');
        if (head.indexOf('mobile') !== -1) {
            sec.classList.add('editor-setting-mobile');
        } else if (head.indexOf('countdown') !== -1 || head.indexOf('cta') !== -1) {
            sec.classList.add('editor-setting-shared');
        } else {
            sec.classList.add('editor-setting-desktop');
        }
    });
}
document.querySelectorAll('.editor-mode-btn[data-editor-mode]').forEach(function(btn){
    btn.addEventListener('click', function(){ setEditorMode(btn.dataset.editorMode); });
});
classifyEditorSections();
setEditorMode((function(){ try { return localStorage.getItem('lb_banner_editor_mode') || 'desktop'; } catch(e){ return 'desktop'; } })());

buildPreview();
setInterval(function(){ if ((gv('banner_sale_ends_at')||'').trim()) buildPreview(); }, 1000);

// ── Save ──────────────────────────────────────────────────────────────────
var toast = document.getElementById('bs-toast');
function showToast(ok, msg){
    toast.className = ok ? 'ok' : 'err';
    toast.textContent = msg;
    toast.style.display = 'block';
    clearTimeout(toast._t);
    toast._t = setTimeout(function(){ toast.style.display = 'none'; }, 4000);
}
function doSave(isInline){
    if (typeof syncDateHidden === 'function' && !syncDateHidden()) { showToast(false, '❌ Invalid countdown end date format (use DD-MM-YY HH-MM-SS).'); return; }
    if (isSavingInline) return;
    isSavingInline = true;
    ['save-btn','save-btn-2'].forEach(function(id){
        var b = document.getElementById(id);
        if (b){ b.disabled = true; b.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Saving…'; }
    });
    var fd = new FormData(document.getElementById('banner-form'));
    fetch('<?= AJAX_URL ?>', { method:'POST', body:fd })
        .then(function(r){ return r.json(); })
        .then(function(j){
            showToast(j && j.status === 'ok', j && j.status === 'ok' ? (isInline ? '✅ Saved!' : '✅ Saved!') : '❌ '+(j&&j.message||'Error'));
        })
        .catch(function(){ showToast(false,'❌ Network error'); })
        .finally(function(){
            isSavingInline = false;
            ['save-btn','save-btn-2'].forEach(function(id){
                var b = document.getElementById(id);
                if (b){ b.disabled = false; b.innerHTML = '<i class="fas fa-save me-2"></i>Save Changes'; }
            });
        });
}
document.getElementById('save-btn').addEventListener('click', function(){ doSave(false); });
document.getElementById('save-btn-2').addEventListener('click', function(){ doSave(false); });

})();
</script>
