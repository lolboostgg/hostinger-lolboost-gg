<?= $this->layout('admin/layouts/main', ['meta' => [
    'title'       => 'Onboarding Applications — Admin Area | LoLBoost.gg',
    'h1'          => 'Onboarding Applications',
    'description' => 'Review completed onboarding applications in one place.',
]]) ?>

<?php
$rows = $rows ?? [];

if (empty($rows)) {
    $legacy = $applications ?? [];
    if (!empty($legacy)) {
        foreach ($legacy as $r) {
            $role = (string)($r['role'] ?? 'booster');
            $type = str_contains($role, 'seller') ? 'seller' : (str_contains($role, 'girl') ? 'egirl' : 'booster');
            $rows[] = [
                'type' => $type,
                'id' => $r['id'] ?? '',
                'user_id' => $r['id'] ?? '',
                'username' => $r['fullname'] ?? $r['username'] ?? '—',
                'fullname' => $r['fullname'] ?? '—',
                'email' => $r['email'] ?? '—',
                'country' => $r['country'] ?? '—',
                'address' => $r['address'] ?? '',
                'created_at' => $r['created_at'] ?? null,
                'status' => $r['status'] ?? 'pending',
                'id_front' => $r['id_front'] ?? '',
                'id_back' => $r['id_back'] ?? '',
                'selfie' => $r['selfie'] ?? '',
            ];
        }
    }
}

$h = fn($v) => htmlspecialchars((string)($v ?? ''), ENT_QUOTES, 'UTF-8');
$typeMeta = [
    'booster' => ['label' => 'Booster', 'icon' => 'fa-user-shield', 'class' => 'app-type--booster', 'url' => ADMN_URL . '/booster/'],
    'egirl'   => ['label' => 'E-Girl',   'icon' => 'fa-heart',       'class' => 'app-type--egirl',   'url' => ADMN_URL . '/egirl/'],
    'seller'  => ['label' => 'Seller',   'icon' => 'fa-store',       'class' => 'app-type--seller',  'url' => ADMN_URL . '/seller/'],
];
$counts = ['all' => count($rows), 'booster' => 0, 'egirl' => 0, 'seller' => 0, 'pending' => 0, 'completed' => 0, 'rejected' => 0];
foreach ($rows as $r) {
    $t = $r['type'] ?? 'booster';
    if (isset($counts[$t])) $counts[$t]++;
    $status = strtolower((string)($r['status'] ?? 'pending'));
    if (isset($counts[$status])) $counts[$status]++;
}
?>

<?= $this->start('styles') ?>
<style>
/* Coins History inspired compact admin design */
.app-page-head{display:flex;align-items:center;justify-content:space-between;gap:14px;margin-bottom:14px;}
.al-hero{border-radius:20px;border:1px solid rgba(255,255,255,.07);background:#25282a;padding:20px 24px;display:flex;align-items:center;justify-content:space-between;gap:14px;flex-wrap:wrap;margin-bottom:14px;box-shadow:0 2px 20px rgba(0,0,0,.22);}
.al-hero-main{display:flex;align-items:center;gap:14px;min-width:0;}
.al-hero-icon{width:44px;height:44px;border-radius:13px;background:linear-gradient(135deg,rgba(109,92,255,.22),rgba(109,92,255,.07));border:1px solid rgba(109,92,255,.28);display:flex;align-items:center;justify-content:center;font-size:1.1rem;color:#a99bff;flex-shrink:0;}
.al-hero-title{font-size:1.1rem;font-weight:950;color:rgba(255,255,255,.92);margin:0;}
.al-hero-sub{font-size:.8rem;color:rgba(255,255,255,.4);margin:2px 0 0;}
.al-hero .btn{border-radius:12px;font-weight:900;box-shadow:0 8px 24px rgba(109,92,255,.2);}
.ch-stats{display:grid;grid-template-columns:repeat(auto-fit,minmax(150px,1fr));gap:12px;margin-bottom:16px;}
.ch-stat{background:#25282a;border:1px solid rgba(255,255,255,.07);border-radius:16px;padding:16px 18px;display:flex;align-items:center;gap:12px;box-shadow:0 2px 16px rgba(0,0,0,.2);transition:transform .15s;}
.ch-stat:hover{transform:translateY(-2px);}
.ch-stat-icon{width:40px;height:40px;border-radius:11px;display:flex;align-items:center;justify-content:center;font-size:1rem;flex-shrink:0;background:rgba(109,92,255,.14);border:1px solid rgba(109,92,255,.22);color:#c4b5fd;}
.ch-stat-label{font-size:.68rem;font-weight:700;color:rgba(255,255,255,.35);text-transform:uppercase;letter-spacing:.06em;margin-bottom:2px;}
.ch-stat-value{font-size:1.25rem;font-weight:900;color:rgba(255,255,255,.9);line-height:1.2;}
.al-toolbar-card{border-radius:16px;border:1px solid rgba(255,255,255,.07);background:#25282a;padding:12px 16px;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:10px;margin-bottom:16px;box-shadow:0 2px 16px rgba(0,0,0,.18);}
.al-toolbar-left{display:flex;align-items:center;gap:16px;flex-wrap:wrap;min-width:0;}
.al-filter-group{display:flex;align-items:center;gap:8px;flex-wrap:wrap;}
.al-filter-label{display:inline-flex;align-items:center;gap:6px;font-size:.68rem;font-weight:800;color:rgba(255,255,255,.35);text-transform:uppercase;letter-spacing:.07em;white-space:nowrap;}
.al-filter-label i{color:rgba(109,92,255,.9);font-size:.75rem;}
.al-pills{display:flex;gap:6px;flex-wrap:wrap;}
.app-pill{display:inline-flex;align-items:center;gap:.3rem;padding:5px 13px;border-radius:99px;font-size:.78rem;font-weight:800;cursor:pointer;border:1px solid rgba(255,255,255,.09);background:rgba(255,255,255,.04);color:rgba(255,255,255,.6);transition:all .12s;user-select:none;line-height:1.25;}
.app-pill:hover{background:rgba(255,255,255,.08);color:rgba(255,255,255,.85);}
.app-pill.active{background:rgba(109,92,255,.18);border-color:rgba(109,92,255,.45);color:#c4b5fd;}
.al-search-wrap{position:relative;display:flex;align-items:center;}
.al-search-wrap input{background:rgba(255,255,255,.05)!important;border:1px solid rgba(255,255,255,.09)!important;border-radius:10px!important;color:#fff!important;font-size:.82rem!important;padding:7px 12px 7px 34px!important;outline:none!important;width:260px;transition:border-color .15s;min-height:38px;}
.al-search-wrap input:focus{border-color:rgba(109,92,255,.45)!important;box-shadow:0 0 0 3px rgba(109,92,255,.08)!important;}
.al-search-wrap input::placeholder{color:rgba(255,255,255,.25);}
.al-search-icon{position:absolute;left:10px;color:rgba(255,255,255,.3);font-size:.78rem;pointer-events:none;z-index:2;}
.al-table-wrap{background:#25282a;border:1px solid rgba(255,255,255,.07);border-radius:20px;overflow:hidden;box-shadow:0 2px 20px rgba(0,0,0,.2);margin-bottom:14px;}
.app-table-head{padding:16px 18px 0;display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap;}
.app-table-title{font-weight:950;color:rgba(255,255,255,.9);font-size:1rem;}
.app-table-sub{font-size:.78rem;color:rgba(255,255,255,.4);margin-top:2px;}
.app-completed-badge{display:inline-flex;align-items:center;gap:6px;border-radius:999px;background:rgba(109,92,255,.12);border:1px solid rgba(109,92,255,.24);color:#c4b5fd;font-size:.72rem;font-weight:900;padding:6px 10px;}
#applicationsTable{width:100%;border-collapse:collapse;margin:0!important;}
#applicationsTable thead tr{border-bottom:1px solid rgba(255,255,255,.06);}
#applicationsTable thead th{padding:12px 16px!important;font-size:.68rem!important;font-weight:800!important;color:rgba(255,255,255,.35)!important;text-transform:uppercase;letter-spacing:.07em;white-space:nowrap;background:rgba(0,0,0,.12)!important;border:0!important;}
#applicationsTable tbody tr{border-bottom:1px solid rgba(255,255,255,.04);transition:background .12s;}
#applicationsTable tbody tr:last-child{border-bottom:none;}
#applicationsTable tbody tr:hover{background:rgba(255,255,255,.025);}
#applicationsTable td{padding:13px 16px!important;font-size:.875rem;color:rgba(255,255,255,.82);vertical-align:middle;border:0!important;}
.app-type{display:inline-flex;align-items:center;gap:6px;border-radius:999px;padding:6px 10px;font-size:.76rem;font-weight:900;border:1px solid transparent;white-space:nowrap;}
.app-type--booster{background:rgba(88,101,242,.14);border-color:rgba(88,101,242,.35);color:#aab2ff;}
.app-type--egirl{background:rgba(236,72,153,.14);border-color:rgba(236,72,153,.35);color:#ff9bd0;}
.app-type--seller{background:rgba(34,197,94,.13);border-color:rgba(34,197,94,.30);color:#86efac;}
.app-status{display:inline-flex;align-items:center;gap:6px;border-radius:999px;padding:6px 10px;font-size:.74rem;font-weight:900;text-transform:capitalize;white-space:nowrap;}
.app-status.pending{background:rgba(250,204,21,.13);color:#facc15;border:1px solid rgba(250,204,21,.28);}
.app-status.completed,.app-status.approved{background:rgba(34,197,94,.13);color:#86efac;border:1px solid rgba(34,197,94,.28);}
.app-status.rejected,.app-status.declined{background:rgba(244,63,94,.13);color:#fb7185;border:1px solid rgba(244,63,94,.28);}
.app-docs{display:flex;gap:6px;flex-wrap:wrap;}
.app-docs a{display:inline-flex;align-items:center;gap:5px;padding:5px 8px;border-radius:8px;background:rgba(255,255,255,.05);border:1px solid rgba(255,255,255,.09);font-size:.72rem;color:rgba(255,255,255,.75);text-decoration:none;}
.app-docs a:hover{color:#c4b5fd;border-color:rgba(109,92,255,.34);background:rgba(109,92,255,.1);}
.app-hired{display:inline-flex;align-items:center;gap:7px;max-width:100%;padding:4px 9px 4px 4px;border-radius:999px;background:rgba(109,92,255,.10);border:1px solid rgba(109,92,255,.24);}
.app-hired__avatar{width:22px;height:22px;border-radius:50%;object-fit:cover;flex:0 0 auto;background:rgba(255,255,255,.06);}
.app-hired__name{min-width:0;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;font-size:.74rem;font-weight:800;color:#c4b5fd;}
.app-empty{padding:54px 18px;text-align:center;color:rgba(255,255,255,.48);}
.app-empty i{display:block;font-size:2rem;margin-bottom:10px;color:rgba(255,255,255,.25);}
.ob-custom-select{position:relative;margin-bottom:1rem;}
.ob-custom-select-btn{width:100%;height:46px;border:1px solid rgba(255,255,255,.10);background:rgba(0,0,0,.18);color:#fff;border-radius:12px;padding:0 13px;display:flex;align-items:center;justify-content:space-between;gap:12px;font-weight:800;}
.ob-custom-select-btn:hover,.ob-custom-select.open .ob-custom-select-btn{border-color:rgba(109,92,255,.55);background:rgba(109,92,255,.08);}
.ob-custom-select-current{display:flex;align-items:center;gap:9px;min-width:0;}
.ob-custom-select-current i{color:#8d7cff;}
.ob-custom-select-menu{position:absolute;z-index:30;left:0;right:0;top:calc(100% + 8px);display:none;background:#242629;border:1px solid rgba(255,255,255,.10);border-radius:14px;box-shadow:0 18px 50px rgba(0,0,0,.45);padding:6px;}
.ob-custom-select.open .ob-custom-select-menu{display:block;}
.ob-custom-option{width:100%;border:0;background:transparent;color:rgba(255,255,255,.76);display:flex;align-items:center;gap:10px;border-radius:10px;padding:10px 11px;font-weight:800;text-align:left;}
.ob-custom-option i{width:18px;text-align:center;color:rgba(255,255,255,.45);}
.ob-custom-option:hover{background:rgba(255,255,255,.06);color:#fff;}
.ob-custom-option.active{background:rgba(109,92,255,.18);color:#fff;}
.ob-custom-option.active i{color:#8d7cff;}
/* Onboarding link modal */
.ob-link-modal .modal-dialog{max-width:720px;}
.ob-link-modal .modal-content{border-radius:22px;border:1px solid rgba(255,255,255,.09);background:#25282a;box-shadow:0 26px 80px rgba(0,0,0,.55);overflow:visible;}
.ob-link-modal .modal-header{padding:18px 20px 14px;border-bottom:1px solid rgba(255,255,255,.06);align-items:flex-start;}
.ob-link-modal-title{display:flex;align-items:center;gap:12px;}
.ob-link-modal-icon{width:38px;height:38px;border-radius:12px;display:flex;align-items:center;justify-content:center;background:rgba(109,92,255,.16);border:1px solid rgba(109,92,255,.28);color:#c4b5fd;}
.ob-link-modal h5{font-weight:950;margin:0;color:rgba(255,255,255,.94);}
.ob-link-modal-sub{font-size:.76rem;color:rgba(255,255,255,.42);margin-top:2px;}
.ob-link-modal .btn-close{width:38px;height:38px;border-radius:12px;background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.10);opacity:1;filter:invert(1) grayscale(1);background-size:13px;}
.ob-link-modal .modal-body{padding:18px 20px 20px;}
.ob-link-grid{display:grid;grid-template-columns:250px 1fr;gap:14px;align-items:start;}
.ob-link-panel{border-radius:16px;border:1px solid rgba(255,255,255,.07);background:rgba(0,0,0,.12);padding:14px;}
.ob-link-panel-label{display:flex;align-items:center;gap:7px;font-size:.68rem;font-weight:900;text-transform:uppercase;letter-spacing:.07em;color:rgba(255,255,255,.38);margin-bottom:9px;}
.ob-link-panel-label i{color:#8d7cff;}
.ob-link-result{display:flex;flex-direction:column;gap:10px;}
.ob-link-result-head{display:flex;align-items:center;justify-content:space-between;gap:10px;flex-wrap:wrap;}
.ob-link-result-title{font-size:.9rem;font-weight:900;color:rgba(255,255,255,.9);}
.ob-link-result-badge{border-radius:999px;background:rgba(34,197,94,.12);border:1px solid rgba(34,197,94,.25);color:#86efac;font-size:.68rem;font-weight:900;padding:5px 9px;margin-left:auto;}
/* Role picker: one click per role creates the link right away. */
.ob-step-label{display:flex;align-items:center;gap:9px;font-size:.72rem;font-weight:900;text-transform:uppercase;letter-spacing:.07em;color:rgba(255,255,255,.42);margin-bottom:11px;}
.ob-step-num{display:grid;place-items:center;width:20px;height:20px;border-radius:50%;background:rgba(109,92,255,.2);border:1px solid rgba(139,124,255,.4);color:#c4b5fd;font-size:.68rem;}
.ob-role-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:10px;}
.ob-role-card{display:flex;flex-direction:column;align-items:center;gap:9px;padding:16px 12px;border-radius:16px;border:1px solid rgba(255,255,255,.08);background:rgba(0,0,0,.16);color:#fff;cursor:pointer;transition:border-color .15s,background .15s,transform .15s;}
.ob-role-card:hover:not(:disabled){border-color:rgba(139,124,255,.45);background:rgba(109,92,255,.1);transform:translateY(-2px);}
.ob-role-card.active{border-color:rgba(139,124,255,.6);background:rgba(109,92,255,.16);}
.ob-role-card:disabled{opacity:.55;cursor:default;transform:none;}
.ob-role-card.is-loading .ob-role-cta{opacity:.5;}
.ob-role-icon{display:grid;place-items:center;width:44px;height:44px;border-radius:13px;font-size:1.05rem;}
.ob-role-icon.booster{background:rgba(88,101,242,.14);border:1px solid rgba(88,101,242,.26);color:#aab2ff;}
.ob-role-icon.egirl{background:rgba(236,72,153,.14);border:1px solid rgba(236,72,153,.26);color:#ff9bd0;}
.ob-role-icon.seller{background:rgba(34,197,94,.14);border:1px solid rgba(34,197,94,.26);color:#86efac;}
.ob-role-name{font-size:.92rem;font-weight:900;}
.ob-role-cta{font-size:.68rem;font-weight:900;text-transform:uppercase;letter-spacing:.05em;color:rgba(255,255,255,.45);}
.ob-role-card.active .ob-role-cta{color:#c4b5fd;}
.ob-link-result-block{margin-top:20px;padding-top:18px;border-top:1px solid rgba(255,255,255,.07);}
.ob-link-field-wrap{position:relative;}
.ob-link-field{width:100%;min-height:118px!important;resize:vertical;border-radius:14px!important;border:1px solid rgba(255,255,255,.09)!important;background:rgba(0,0,0,.2)!important;color:rgba(255,255,255,.88)!important;padding:13px 14px!important;font-size:.82rem!important;line-height:1.45!important;word-break:break-all;white-space:pre-wrap;font-family:ui-monospace,SFMono-Regular,Menlo,Monaco,Consolas,monospace;}
.ob-link-field:focus{border-color:rgba(109,92,255,.48)!important;box-shadow:0 0 0 3px rgba(109,92,255,.08)!important;}
.ob-link-actions{display:grid;grid-template-columns:1fr 1fr 1fr;gap:8px;}
.ob-link-actions .btn{border-radius:12px;font-weight:900;min-height:42px;}
.ob-link-help{font-size:.76rem;color:rgba(255,255,255,.38);line-height:1.45;margin-top:10px;}
.ob-link-modal .modal-footer{padding:14px 20px 18px;border-top:1px solid rgba(255,255,255,.06);}
.ob-link-modal .modal-footer .btn{border-radius:12px;font-weight:900;min-height:42px;padding-left:18px;padding-right:18px;}
.onboarding-link-copy-group textarea{min-height:46px;resize:none;line-height:1.35;word-break:break-all;}
@media only screen and (max-width:1200px){.al-table-wrap{overflow-x:auto;}#applicationsTable{min-width:980px;}.al-toolbar-card{align-items:stretch}.al-toolbar-left{width:100%;}.al-search-wrap{width:100%;}.al-search-wrap input{width:100%;}}
@media(max-width:767.98px){.ob-link-modal .modal-dialog{max-width:none;margin:10px;}.ob-link-grid{grid-template-columns:1fr;}.ob-link-panel{padding:12px;}.ob-link-field{min-height:132px!important;font-size:.78rem!important;}.ob-link-actions{grid-template-columns:1fr;}.ob-role-grid{grid-template-columns:1fr;}.ob-role-card{flex-direction:row;justify-content:flex-start;padding:12px;}.ob-role-card .ob-role-cta{margin-left:auto;}.ob-link-modal .modal-footer{display:grid;grid-template-columns:1fr;}.ob-link-modal .modal-footer .btn{width:100%;}}
@media(max-width:575.98px){.al-hero{padding:16px;}.al-hero-main{align-items:flex-start}.al-hero .btn{width:100%;}.ch-stats{grid-template-columns:1fr 1fr;}.ch-stat{padding:13px}.al-toolbar-left{gap:10px}.al-filter-group{width:100%;align-items:flex-start;flex-direction:column;}.app-pill{padding:7px 11px}.onboarding-link-copy-group{display:grid!important;grid-template-columns:1fr!important;gap:8px!important;}.onboarding-link-copy-group .btn{width:100%;border-radius:12px!important;min-height:44px;}.onboarding-link-copy-group textarea{border-radius:12px!important;min-height:74px;font-size:.82rem;}}
</style>
<?= $this->end() ?>

<!-- Hero -->
<div class="al-hero">
    <div class="al-hero-main">
        <div class="al-hero-icon"><i class="fa-duotone fa-clipboard-check"></i></div>
        <div>
            <h2 class="al-hero-title">Onboarding Applications</h2>
            <p class="al-hero-sub">Only completed onboarding submissions are shown here.</p>
        </div>
    </div>
    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#generateOnboardingModal">
        <i class="fa-duotone fa-link me-1"></i> Generate Onboarding Link
    </button>
</div>

<!-- Stats -->
<div class="ch-stats">
    <div class="ch-stat">
        <div class="ch-stat-icon"><i class="fa-duotone fa-layer-group"></i></div>
        <div><div class="ch-stat-label">All</div><div class="ch-stat-value"><?= (int)$counts['all'] ?></div></div>
    </div>
    <div class="ch-stat">
        <div class="ch-stat-icon" style="background:rgba(88,101,242,.12);border-color:rgba(88,101,242,.22);color:#aab2ff;"><i class="fa-duotone fa-user-shield"></i></div>
        <div><div class="ch-stat-label">Boosters</div><div class="ch-stat-value"><?= (int)$counts['booster'] ?></div></div>
    </div>
    <div class="ch-stat">
        <div class="ch-stat-icon" style="background:rgba(236,72,153,.12);border-color:rgba(236,72,153,.22);color:#ff9bd0;"><i class="fa-duotone fa-heart"></i></div>
        <div><div class="ch-stat-label">E-Girls</div><div class="ch-stat-value"><?= (int)$counts['egirl'] ?></div></div>
    </div>
    <div class="ch-stat">
        <div class="ch-stat-icon" style="background:rgba(34,197,94,.12);border-color:rgba(34,197,94,.22);color:#86efac;"><i class="fa-duotone fa-store"></i></div>
        <div><div class="ch-stat-label">Sellers</div><div class="ch-stat-value"><?= (int)$counts['seller'] ?></div></div>
    </div>
</div>

<div class="al-table-wrap app-card">
    <div class="app-table-head">
        <div>
            <div class="app-table-title">All Onboarding Applications</div>
            <div class="app-table-sub">Filter completed onboarding submissions by role or review status.</div>
        </div>
        <span class="app-completed-badge"><i class="fa-duotone fa-badge-check"></i> Completed onboarding only</span>
    </div>

    <div class="al-toolbar-card" style="margin:16px 18px;">
        <div class="al-toolbar-left">
            <div class="al-filter-group">
                <span class="al-filter-label"><i class="fa-duotone fa-layer-group"></i> Type</span>
                <div class="al-pills" id="typeFilter">
                    <button type="button" class="app-pill active" data-type="all">All</button>
                    <button type="button" class="app-pill" data-type="booster">Boosters</button>
                    <button type="button" class="app-pill" data-type="egirl">E-Girls</button>
                    <button type="button" class="app-pill" data-type="seller">Sellers</button>
                </div>
            </div>
            <div class="al-filter-group">
                <span class="al-filter-label"><i class="fa-duotone fa-circle-check"></i> Status</span>
                <div class="al-pills" id="statusFilter">
                    <button type="button" class="app-pill active" data-status="all">All</button>
                    <button type="button" class="app-pill" data-status="pending">Pending</button>
                    <button type="button" class="app-pill" data-status="completed">Completed</button>
                    <button type="button" class="app-pill" data-status="rejected">Rejected</button>
                </div>
            </div>
        </div>
        <div class="al-search-wrap">
            <i class="fa-solid fa-magnifying-glass al-search-icon"></i>
            <input type="search" id="appSearchMobile" placeholder="Search name, email, country">
        </div>
    </div>

<?php if (!empty($rows)): ?>
    <div class="table-responsive">
        <table class="table table-borderless table-thead-bordered table-align-middle card-table mb-0" id="applicationsTable">
            <thead class="thead-light">
                <tr>
                    <th>Type</th>
                    <th>ID</th>
                    <th>User</th>
                    <th>Contact</th>
                    <th>Country</th>
                    <th>Documents</th>
                    <th>Hired By</th>
                    <th>Status</th>
                    <th>Applied</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($rows as $r): ?>
                    <?php
                    $type = $r['type'] ?? 'booster';
                    $meta = $typeMeta[$type] ?? $typeMeta['booster'];
                    $status = strtolower((string)($r['status'] ?? 'pending'));
                    $id = (int)($r['id'] ?? $r['user_id'] ?? 0);
                    $userId = (int)($r['user_id'] ?? $id);
                    $viewUrl = ($type === 'seller') ? (ADMN_URL . '/seller/' . $userId) : ($meta['url'] . $userId);
                    // "Hired by" = the admin whose one-time onboarding link the applicant used.
                    // Older applications predate that tracking, so the column can be empty.
                    $hiredById = (int)($r['hired_by_admin_id'] ?? 0);
                    $hiredByName = trim((string)($r['hired_by_username'] ?? ''));
                    $hiredByIcon = trim((string)($r['hired_by_icon'] ?? ''));
                    if ($hiredByIcon === '') {
                        $hiredByIcon = (defined('ICON_URL') ? ICON_URL : '') . '/03ce541a1f4bf8b06c924439ffcc8173.png';
                    }
                    $searchText = strtolower(trim(($meta['label'] ?? '') . ' ' . ($r['username'] ?? '') . ' ' . ($r['fullname'] ?? '') . ' ' . ($r['email'] ?? '') . ' ' . ($r['country'] ?? '') . ' ' . $hiredByName));
                    ?>
                    <tr data-type="<?= $h($type) ?>" data-status="<?= $h($status) ?>" data-search="<?= $h($searchText) ?>" id="app-row-<?= $h($type) ?>-<?= $userId ?>">
                        <td><span class="app-type <?= $h($meta['class']) ?>"><i class="fa-duotone <?= $h($meta['icon']) ?>"></i> <?= $h($meta['label']) ?></span></td>
                        <td class="text-muted">#<?= $userId ?></td>
                        <td>
                            <div class="fw-700"><?= $h($r['username'] ?? '—') ?></div>
                            <div class="text-muted small"><?= $h($r['fullname'] ?? '—') ?></div>
                        </td>
                        <td>
                            <div class="text-muted small"><?= $h($r['email'] ?? '—') ?></div>
                            <?php if (!empty($r['discord'])): ?><div class="text-muted small"><i class="fa-brands fa-discord me-1"></i><?= $h($r['discord']) ?></div><?php endif; ?>
                        </td>
                        <td class="text-muted"><?= $h($r['country'] ?? '—') ?></td>
                        <td>
                            <div class="app-docs">
                                <?php if (!empty($r['id_front'])): ?><a href="<?= $h($r['id_front']) ?>" target="_blank"><i class="fa-duotone fa-id-card"></i>Front</a><?php endif; ?>
                                <?php if (!empty($r['id_back'])): ?><a href="<?= $h($r['id_back']) ?>" target="_blank"><i class="fa-duotone fa-id-card"></i>Back</a><?php endif; ?>
                                <?php if (!empty($r['selfie'])): ?><a href="<?= $h($r['selfie']) ?>" target="_blank"><i class="fa-duotone fa-camera"></i>Selfie</a><?php endif; ?>
                                <?php if (empty($r['id_front']) && empty($r['id_back']) && empty($r['selfie'])): ?><span class="text-muted small">—</span><?php endif; ?>
                            </div>
                        </td>
                        <td>
                            <?php if ($hiredById > 0): ?>
                                <span class="app-hired" title="Admin #<?= $hiredById ?>">
                                    <img class="app-hired__avatar" src="<?= $h($hiredByIcon) ?>" alt="" loading="lazy">
                                    <span class="app-hired__name"><?= $h($hiredByName !== '' ? $hiredByName : ('Admin #' . $hiredById)) ?></span>
                                </span>
                            <?php else: ?>
                                <span class="text-muted small">—</span>
                            <?php endif; ?>
                        </td>
                        <td><span class="app-status <?= $h($status) ?>"><i class="fa-solid fa-circle" style="font-size:.45rem"></i><?= $h($status ?: 'pending') ?></span></td>
                        <td class="text-muted"><?= !empty($r['created_at']) ? date('d.m.Y H:i', strtotime((string)$r['created_at'])) : '—' ?></td>
                        <td class="text-end">
                            <div class="d-flex gap-2 justify-content-end align-items-center">
                                <a href="<?= $h($viewUrl) ?>" class="btn btn-sm btn-white"><i class="fa-duotone fa-eye me-1"></i> View</a>
                                <?php if ($type === 'seller' && $status === 'pending'): ?>
                                    <form class="ajax-form d-inline" action="<?= AJAX_URL ?>" method="POST">
                                        <input type="hidden" name="action" value="admin_approve_seller">
                                        <input type="hidden" name="id" value="<?= $userId ?>">
                                        <button type="submit" class="btn btn-sm btn-success"><i class="fa-duotone fa-check me-1"></i> Approve</button>
                                    </form>
                                    <form class="ajax-form d-inline" action="<?= AJAX_URL ?>" method="POST">
                                        <input type="hidden" name="action" value="admin_decline_seller">
                                        <input type="hidden" name="id" value="<?= $userId ?>">
                                        <button type="submit" class="btn btn-sm btn-danger"><i class="fa-duotone fa-xmark me-1"></i> Decline</button>
                                    </form>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php else: ?>
        <div class="app-empty"><i class="fa-duotone fa-inbox"></i>No applications found.</div>
    <?php endif; ?>
</div>

<div class="modal fade ob-link-modal" id="generateOnboardingModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <div class="ob-link-modal-title">
                    <div class="ob-link-modal-icon"><i class="fa-duotone fa-link"></i></div>
                    <div>
                        <h5 class="modal-title">Generate Onboarding Link</h5>
                        <div class="ob-link-modal-sub">Create a fresh invite link for Booster, E Girl or Seller onboarding.</div>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="onboardingType" value="">

                <!-- Step 1: one click per role creates the link right away. -->
                <div class="ob-step-label"><span class="ob-step-num">1</span> Who is this link for?</div>
                <div class="ob-role-grid" id="onboardingRoleGrid">
                    <button type="button" class="ob-role-card" data-value="booster" data-label="Booster">
                        <span class="ob-role-icon booster"><i class="fa-duotone fa-user-shield"></i></span>
                        <span class="ob-role-name">Booster</span>
                        <span class="ob-role-cta"><i class="fa-duotone fa-link me-1"></i>Create link</span>
                    </button>
                    <button type="button" class="ob-role-card" data-value="egirl" data-label="E-Girl">
                        <span class="ob-role-icon egirl"><i class="fa-duotone fa-heart"></i></span>
                        <span class="ob-role-name">E-Girl</span>
                        <span class="ob-role-cta"><i class="fa-duotone fa-link me-1"></i>Create link</span>
                    </button>
                    <button type="button" class="ob-role-card" data-value="seller" data-label="Seller">
                        <span class="ob-role-icon seller"><i class="fa-duotone fa-store"></i></span>
                        <span class="ob-role-name">Seller</span>
                        <span class="ob-role-cta"><i class="fa-duotone fa-link me-1"></i>Create link</span>
                    </button>
                </div>

                <!-- Step 2: only appears once a link exists. -->
                <div class="ob-link-result-block" id="onboardingResultBlock" hidden>
                    <div class="ob-step-label"><span class="ob-step-num">2</span> Copy the link
                        <span class="ob-link-result-badge" id="onboardingGeneratedBadge"><i class="fa-solid fa-check"></i> <span id="onboardingResultRole">Ready</span></span>
                    </div>
                    <div class="ob-link-field-wrap">
                        <textarea class="form-control ob-link-field" id="onboardingLinkField" readonly rows="3"></textarea>
                    </div>
                    <div class="ob-link-actions">
                        <button type="button" class="btn btn-primary" id="copyOnboardingLinkBtn"><i class="fa-duotone fa-copy me-1"></i> Copy link</button>
                        <button type="button" class="btn btn-white" id="selectOnboardingLinkBtn"><i class="fa-duotone fa-i-cursor me-1"></i> Select link</button>
                        <button type="button" class="btn btn-white" id="regenerateOnboardingLinkBtn"><i class="fa-duotone fa-rotate me-1"></i> New link</button>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-white" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<?= $this->start('scripts') ?>
<script>
(function(){
    let currentType = 'all';
    let currentStatus = 'all';
    const search = document.getElementById('appSearchMobile') || document.getElementById('appSearch');
    const rows = Array.from(document.querySelectorAll('#applicationsTable tbody tr'));

    const onboardingTypeField = document.getElementById('onboardingType');

    function applyFilters(){
        const q = (search?.value || '').trim().toLowerCase();
        rows.forEach(row => {
            const okType = currentType === 'all' || row.dataset.type === currentType;
            const rowStatus = row.dataset.status || 'pending';
            const okStatus = currentStatus === 'all' || rowStatus === currentStatus || (currentStatus === 'rejected' && rowStatus === 'declined');
            const okSearch = !q || (row.dataset.search || '').includes(q);
            row.style.display = (okType && okStatus && okSearch) ? '' : 'none';
        });
    }

    document.querySelectorAll('#typeFilter .app-pill').forEach(btn => {
        btn.addEventListener('click', () => {
            document.querySelectorAll('#typeFilter .app-pill').forEach(b => b.classList.remove('active'));
            btn.classList.add('active');
            currentType = btn.dataset.type || 'all';
            applyFilters();
        });
    });

    document.querySelectorAll('#statusFilter .app-pill').forEach(btn => {
        btn.addEventListener('click', () => {
            document.querySelectorAll('#statusFilter .app-pill').forEach(b => b.classList.remove('active'));
            btn.classList.add('active');
            currentStatus = btn.dataset.status || 'all';
            applyFilters();
        });
    });

    search?.addEventListener('input', applyFilters);

    function getAjaxAction(type){
        if (type === 'egirl') return 'get_egirl_onboarding_url';
        if (type === 'seller') return 'get_seller_onboarding_url';
        return 'get_onboarding_url';
    }

    async function copyText(text){
        if (!text) return false;

        if (navigator.clipboard && window.isSecureContext) {
            try {
                await navigator.clipboard.writeText(text);
                return true;
            } catch(e) {}
        }

        const area = document.createElement('textarea');
        area.value = text;
        area.setAttribute('readonly', 'readonly');
        area.style.position = 'fixed';
        area.style.top = '0';
        area.style.left = '0';
        area.style.width = '1px';
        area.style.height = '1px';
        area.style.opacity = '0';
        document.body.appendChild(area);

        area.focus({ preventScroll: true });
        area.select();
        area.setSelectionRange(0, area.value.length);

        let ok = false;
        try { ok = document.execCommand('copy'); } catch(e) {}
        area.remove();
        return ok;
    }

    function selectGeneratedLink(){
        const field = document.getElementById('onboardingLinkField');
        if (!field || !field.value) return;
        try {
            field.focus({ preventScroll: true });
            field.select();
            field.setSelectionRange(0, field.value.length);
        } catch(e) {}
    }

    // One click on a role card creates the link immediately — no separate
    // "select type, then press Generate" step any more.
    function generateOnboardingLink(card){
        const type = card.dataset.value || 'booster';
        const label = card.dataset.label || 'Booster';
        if (onboardingTypeField) onboardingTypeField.value = type;

        document.querySelectorAll('#onboardingRoleGrid .ob-role-card').forEach(c => {
            c.classList.toggle('active', c === card);
            c.disabled = true;
        });
        card.classList.add('is-loading');

        $.ajax({
            type: 'POST',
            url: '<?= AJAX_URL ?>',
            dataType: 'text',
            data: { action: getAjaxAction(type) },
            success: async function(res){
                if (typeof res === 'string') {
                    const cleaned = res.replace(/\s*null\s*$/, '').trim();
                    try { res = JSON.parse(cleaned); } catch(e) { res = {}; }
                }
                const token = res?.token || res?.data?.token || '';
                let url = res?.url || res?.link || res?.onboarding_url || res?.data?.url || '';
                if (!url && token) {
                    const path = type === 'seller' ? '/seller-onboarding' : (type === 'egirl' ? '/egirl-onboarding' : '/onboarding');
                    url = window.location.origin + path + '?t=' + encodeURIComponent(token);
                }
                if (!url) {
                    console.error('Onboarding link response:', res);
                    if (typeof create_toast === 'function') create_toast('danger', 'Error', 'Could not generate onboarding link.');
                    return;
                }
                document.getElementById('onboardingLinkField').value = url;
                const block = document.getElementById('onboardingResultBlock');
                if (block) block.hidden = false;
                const roleLabel = document.getElementById('onboardingResultRole');
                if (roleLabel) roleLabel.textContent = label + ' link';
                selectGeneratedLink();
                const copied = await copyText(url);
                if (typeof create_toast === 'function') create_toast('success', 'Success', copied ? (label + ' link created and copied.') : (label + ' link created.'));
            },
            error: function(){
                if (typeof create_toast === 'function') create_toast('danger', 'Error', 'Could not generate onboarding link.');
            },
            complete: function(){
                document.querySelectorAll('#onboardingRoleGrid .ob-role-card').forEach(c => {
                    c.disabled = false;
                    c.classList.remove('is-loading');
                });
            }
        });
    }

    document.querySelectorAll('#onboardingRoleGrid .ob-role-card').forEach(card => {
        card.addEventListener('click', () => generateOnboardingLink(card));
    });

    document.getElementById('regenerateOnboardingLinkBtn')?.addEventListener('click', function(){
        const active = document.querySelector('#onboardingRoleGrid .ob-role-card.active');
        if (active) generateOnboardingLink(active);
    });

    // Every open starts clean so nobody copies a stale link by accident.
    document.getElementById('generateOnboardingModal')?.addEventListener('show.bs.modal', function(){
        const block = document.getElementById('onboardingResultBlock');
        if (block) block.hidden = true;
        const field = document.getElementById('onboardingLinkField');
        if (field) field.value = '';
        if (onboardingTypeField) onboardingTypeField.value = '';
        document.querySelectorAll('#onboardingRoleGrid .ob-role-card').forEach(c => c.classList.remove('active'));
    });

    document.getElementById('selectOnboardingLinkBtn')?.addEventListener('click', function(){
        selectGeneratedLink();
        if (typeof create_toast === 'function') create_toast('success', 'Selected', 'Full link selected.');
    });

    document.getElementById('copyOnboardingLinkBtn')?.addEventListener('click', async function(){
        const url = document.getElementById('onboardingLinkField')?.value || '';
        selectGeneratedLink();
        const copied = await copyText(url);
        if (typeof create_toast === 'function') create_toast(copied ? 'success' : 'danger', copied ? 'Copied' : 'Error', copied ? 'Link copied.' : 'No link to copy.');
    });
})();
</script>
<?= $this->end() ?>
