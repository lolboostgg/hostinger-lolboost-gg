<?php
$discounts = is_array($data ?? null) ? $data : [];
$h = fn($v) => htmlspecialchars((string)($v ?? ''), ENT_QUOTES, 'UTF-8');
$totalDiscounts = count($discounts);
$activeDiscounts = count(array_filter($discounts, fn($d) => (int)($d['status'] ?? 0) === 1));
$inactiveDiscounts = max(0, $totalDiscounts - $activeDiscounts);
$totalUses = 0;
foreach ($discounts as $d) $totalUses += (int)($d['uses'] ?? 0);
$serviceIcon = static function(string $service): string {
    $key = strtolower(trim($service));
    return match ($key) {
        'boosting' => 'fa-solid fa-bolt',
        'coaching' => 'fa-solid fa-user-graduate',
        'account_shop' => 'fa-solid fa-user-shield',
        'smurf_shop' => 'fa-solid fa-mask',
        default => 'fa-solid fa-tag',
    };
};
$serviceLabel = static function(string $service): string {
    return util_format_default_type($service);
};
?>
<?= $this->layout('admin/layouts/main', ['meta' => ['title' => 'Discounts List - Admin Area | LoLBoost.gg', 'h1' => 'Discounts List', 'description' => 'Manage and edit the Discounts List.']]) ?>

<?= $this->start('styles') ?>
<link rel="stylesheet" href="<?= ASSET_URL ?>/origin/dash/vendor/tom-select/dist/css/tom-select.bootstrap5.css">
<link rel="stylesheet" href="<?= ASSET_URL ?>/origin/dash/vendor/daterangepicker/daterangepicker.css">
<style>
.dc-page{--dc-panel:#25282a;--dc-border:rgba(255,255,255,.075);--dc-muted:rgba(255,255,255,.44);--dc-soft:rgba(255,255,255,.055);--dc-purple:#8b5cf6;--dc-cyan:#38bdf8;}
.dc-hero{border:1px solid var(--dc-border);border-radius:22px;background:radial-gradient(circle at 8% 0%,rgba(139,92,246,.18),transparent 34%),linear-gradient(180deg,rgba(255,255,255,.035),rgba(255,255,255,.01)),var(--dc-panel);padding:22px 24px;margin-bottom:16px;display:flex;align-items:center;justify-content:space-between;gap:16px;flex-wrap:wrap;}
.dc-hero-left{display:flex;align-items:center;gap:14px;min-width:0}.dc-hero-icon{width:48px;height:48px;border-radius:15px;background:linear-gradient(135deg,rgba(139,92,246,.26),rgba(56,189,248,.14));border:1px solid rgba(139,92,246,.32);display:flex;align-items:center;justify-content:center;color:#c4b5fd;font-size:1.18rem;flex-shrink:0}.dc-hero-title{font-size:1.25rem;font-weight:950;color:#fff;margin:0;letter-spacing:-.035em}.dc-hero-sub{font-size:.84rem;color:var(--dc-muted);margin-top:3px}.dc-hero-actions{display:flex;gap:10px;align-items:center;flex-wrap:wrap}.dc-btn{display:inline-flex;align-items:center;justify-content:center;gap:.45rem;border-radius:12px;border:1px solid rgba(255,255,255,.1);background:rgba(255,255,255,.045);color:rgba(255,255,255,.72);font-size:.82rem;font-weight:850;text-decoration:none;padding:9px 13px;transition:background .15s,border-color .15s,color .15s,transform .12s;cursor:pointer}.dc-btn:hover{background:rgba(255,255,255,.09);color:#fff;transform:translateY(-1px)}.dc-btn-primary{background:linear-gradient(135deg,#8b5cf6,#c026d3);border-color:rgba(255,255,255,.08);color:#fff;box-shadow:0 10px 24px rgba(139,92,246,.22)}.dc-btn-primary:hover{color:#fff;filter:brightness(1.06)}.dc-btn-danger:hover{background:rgba(251,113,133,.14);border-color:rgba(251,113,133,.3);color:#fb7185}.dc-btn-success:hover{background:rgba(74,222,128,.12);border-color:rgba(74,222,128,.28);color:#4ade80}.dc-btn-warning:hover{background:rgba(250,204,21,.12);border-color:rgba(250,204,21,.28);color:#facc15}.dc-btn-icon{width:34px;height:34px;padding:0;border-radius:10px}.dc-stats{display:grid;grid-template-columns:repeat(auto-fit,minmax(170px,1fr));gap:12px;margin-bottom:16px}.dc-stat{border:1px solid var(--dc-border);border-radius:18px;background:var(--dc-panel);padding:15px 16px}.dc-stat-inner{display:flex;align-items:center;gap:12px}.dc-stat-icon{width:40px;height:40px;border-radius:13px;background:rgba(139,92,246,.14);border:1px solid rgba(139,92,246,.25);display:flex;align-items:center;justify-content:center;color:#c4b5fd}.dc-stat-label{font-size:.72rem;font-weight:850;text-transform:uppercase;letter-spacing:.075em;color:rgba(255,255,255,.35)}.dc-stat-value{font-size:1.18rem;font-weight:950;color:#fff;line-height:1.1;margin-top:2px}.dc-panel{border:1px solid var(--dc-border);border-radius:22px;background:var(--dc-panel);overflow:hidden}.dc-panel-head{padding:14px 16px;border-bottom:1px solid rgba(255,255,255,.06);display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap;background:rgba(255,255,255,.018)}.dc-panel-title{font-size:.95rem;font-weight:950;color:#fff;display:flex;align-items:center;gap:.5rem}.dc-tools{display:flex;align-items:center;gap:9px;flex-wrap:wrap}.dc-search{position:relative;width:min(340px,80vw)}.dc-search i{position:absolute;left:12px;top:50%;transform:translateY(-50%);color:rgba(255,255,255,.34);font-size:.82rem}.dc-search input{width:100%;background:rgba(255,255,255,.055);border:1px solid rgba(255,255,255,.1);border-radius:12px;color:#fff;padding:9px 12px 9px 36px;font-size:.86rem;outline:none}.dc-search input:focus{border-color:rgba(139,92,246,.48);box-shadow:0 0 0 3px rgba(139,92,246,.12)}.dc-filter-pills{display:flex;gap:7px;align-items:center;flex-wrap:wrap}.dc-filter{border:1px solid rgba(255,255,255,.09);background:rgba(255,255,255,.04);color:rgba(255,255,255,.62);border-radius:999px;padding:7px 12px;font-size:.76rem;font-weight:850;cursor:pointer}.dc-filter:hover,.dc-filter.active{background:rgba(139,92,246,.16);border-color:rgba(139,92,246,.38);color:#c4b5fd}.dc-filter[data-filter=active].active{background:rgba(74,222,128,.12);border-color:rgba(74,222,128,.3);color:#4ade80}.dc-filter[data-filter=inactive].active{background:rgba(250,204,21,.12);border-color:rgba(250,204,21,.3);color:#facc15}.dc-table-wrap{overflow:auto}.dc-table{width:100%;border-collapse:collapse;min-width:1060px}.dc-table thead tr{background:rgba(255,255,255,.025);border-bottom:1px solid rgba(255,255,255,.06)}.dc-table th{padding:11px 16px;font-size:.68rem;font-weight:950;color:rgba(255,255,255,.36);text-transform:uppercase;letter-spacing:.08em;white-space:nowrap}.dc-table td{padding:13px 16px;vertical-align:middle;font-size:.84rem;color:rgba(255,255,255,.76);border-bottom:1px solid rgba(255,255,255,.045)}.dc-table tbody tr:hover{background:rgba(139,92,246,.055)}.dc-code{display:flex;align-items:center;gap:10px;min-width:190px}.dc-code-badge{width:38px;height:38px;border-radius:12px;background:rgba(139,92,246,.16);border:1px solid rgba(139,92,246,.27);display:flex;align-items:center;justify-content:center;color:#c4b5fd}.dc-code-main{font-weight:950;color:#fff;letter-spacing:.02em}.dc-code-sub{font-size:.72rem;color:rgba(255,255,255,.34);margin-top:2px}.dc-usage{min-width:130px}.dc-usage-top{display:flex;align-items:center;justify-content:space-between;gap:10px;font-size:.78rem;font-weight:850;color:rgba(255,255,255,.68)}.dc-progress{height:6px;border-radius:99px;background:rgba(255,255,255,.08);overflow:hidden;margin-top:7px}.dc-progress span{display:block;height:100%;border-radius:inherit;background:linear-gradient(90deg,#8b5cf6,#38bdf8);min-width:2px}.dc-services{display:flex;gap:6px;align-items:center;flex-wrap:wrap;max-width:280px}.dc-service{display:inline-flex;align-items:center;gap:5px;padding:5px 8px;border-radius:999px;background:rgba(255,255,255,.055);border:1px solid rgba(255,255,255,.08);font-size:.72rem;font-weight:850;color:rgba(255,255,255,.68)}.dc-status{display:inline-flex;align-items:center;gap:6px;padding:5px 10px;border-radius:999px;font-size:.72rem;font-weight:900}.dc-status.active{background:rgba(74,222,128,.12);border:1px solid rgba(74,222,128,.28);color:#4ade80}.dc-status.inactive{background:rgba(250,204,21,.12);border:1px solid rgba(250,204,21,.28);color:#facc15}.dc-amount{font-weight:950;color:#fff;font-size:.93rem}.dc-date{font-size:.78rem;color:rgba(255,255,255,.58);white-space:nowrap}.dc-actions{display:flex;align-items:center;gap:7px;justify-content:flex-end}.dc-empty{padding:54px 22px;text-align:center;color:rgba(255,255,255,.42)}.dc-empty i{font-size:2.6rem;opacity:.3;display:block;margin-bottom:12px}.dc-hidden{display:none!important}
/* Shared modal redesign */
.dc-modal .modal-dialog{max-width:720px}.dc-modal .modal-content{border-radius:24px!important;border:1px solid rgba(255,255,255,.08)!important;background:#202326!important;color:#fff;box-shadow:0 30px 90px rgba(0,0,0,.7);overflow:hidden}.dc-modal-head{display:flex;align-items:center;justify-content:space-between;gap:12px;padding:18px 20px;border-bottom:1px solid rgba(255,255,255,.07);background:radial-gradient(circle at 12% 0%,rgba(139,92,246,.18),transparent 38%),rgba(255,255,255,.025)}.dc-modal-title{display:flex;align-items:center;gap:12px}.dc-modal-title-icon{width:42px;height:42px;border-radius:14px;background:rgba(139,92,246,.16);border:1px solid rgba(139,92,246,.28);display:flex;align-items:center;justify-content:center;color:#c4b5fd}.dc-modal-title h2{font-size:1.02rem;font-weight:950;margin:0;color:#fff}.dc-modal-title p{font-size:.76rem;color:rgba(255,255,255,.42);margin:2px 0 0}.dc-modal-close{width:34px;height:34px;border-radius:11px;border:1px solid rgba(255,255,255,.1);background:rgba(255,255,255,.05);color:rgba(255,255,255,.7);display:flex;align-items:center;justify-content:center}.dc-modal-body{padding:18px 20px}.dc-form-grid{display:grid;grid-template-columns:1fr 1fr;gap:14px}.dc-form-full{grid-column:1/-1}.dc-label{display:flex;align-items:center;gap:6px;font-size:.72rem;font-weight:950;color:rgba(255,255,255,.48);text-transform:uppercase;letter-spacing:.07em;margin-bottom:6px}.dc-input,.dc-modal .form-control,.dc-modal .form-select{width:100%;background:rgba(255,255,255,.055)!important;border:1px solid rgba(255,255,255,.11)!important;border-radius:12px!important;color:#fff!important;padding:10px 13px!important;font-size:.88rem!important;outline:none}.dc-input:focus,.dc-modal .form-control:focus,.dc-modal .form-select:focus{border-color:rgba(139,92,246,.52)!important;box-shadow:0 0 0 3px rgba(139,92,246,.12)!important}.dc-modal .input-group-text{background:rgba(255,255,255,.055)!important;border:1px solid rgba(255,255,255,.11)!important;border-right:0!important;color:rgba(255,255,255,.45)!important;border-radius:12px 0 0 12px!important}.dc-modal .input-group .form-control{border-radius:0 12px 12px 0!important}.dc-segment{display:grid;grid-template-columns:1fr 1fr;gap:8px;margin-bottom:9px}.dc-segment input{display:none}.dc-segment label{display:flex;align-items:center;justify-content:center;gap:7px;border:1px solid rgba(255,255,255,.1);background:rgba(255,255,255,.04);border-radius:12px;padding:9px 10px;color:rgba(255,255,255,.62);font-size:.8rem;font-weight:850;cursor:pointer}.dc-segment input:checked+label{background:rgba(139,92,246,.22);border-color:rgba(139,92,246,.52);color:#fff}.dc-modal-footer{display:flex;align-items:center;justify-content:space-between;gap:10px;padding:14px 20px;border-top:1px solid rgba(255,255,255,.07);background:rgba(255,255,255,.02)}.dc-modal .ts-control{background:rgba(255,255,255,.055)!important;border:1px solid rgba(255,255,255,.11)!important;border-radius:12px!important;color:#fff!important;min-height:42px}.dc-modal .ts-dropdown{background:#1e2028!important;border:1px solid rgba(139,92,246,.35)!important;color:#fff!important}.dc-modal .item{background:rgba(139,92,246,.25)!important;color:#fff!important;border-radius:8px!important}.daterangepicker{background:#1e2028!important;border-color:rgba(139,92,246,.35)!important;color:#fff!important}.daterangepicker .calendar-table{background:#1e2028!important;border-color:rgba(255,255,255,.08)!important}.daterangepicker td.off,.daterangepicker td.off.in-range,.daterangepicker td.off.start-date,.daterangepicker td.off.end-date{background:#1e2028!important;color:rgba(255,255,255,.25)!important}.daterangepicker td.available:hover,.daterangepicker th.available:hover{background:rgba(139,92,246,.18)!important}.daterangepicker td.active,.daterangepicker td.active:hover{background:#8b5cf6!important}.daterangepicker .drp-buttons{border-color:rgba(255,255,255,.08)!important}
@media(max-width:768px){.dc-form-grid{grid-template-columns:1fr}.dc-hero{align-items:stretch}.dc-hero-actions{width:100%}.dc-btn-primary{width:100%}.dc-panel-head{align-items:stretch}.dc-tools,.dc-search{width:100%}}
</style>
<?= $this->end() ?>

<div class="dc-page">
    <div class="dc-hero">
        <div class="dc-hero-left">
            <div class="dc-hero-icon"><i class="fa-solid fa-percent"></i></div>
            <div>
                <h1 class="dc-hero-title">Discounts</h1>
                <div class="dc-hero-sub">Create, monitor and manage checkout discount codes.</div>
            </div>
        </div>
        <div class="dc-hero-actions">
            <button type="button" data-bs-toggle="modal" data-bs-target="#create_discount_md" class="dc-btn dc-btn-primary"><i class="fa-solid fa-plus"></i> Add Discount</button>
        </div>
    </div>

    <div class="dc-stats">
        <div class="dc-stat"><div class="dc-stat-inner"><div class="dc-stat-icon"><i class="fa-solid fa-tags"></i></div><div><div class="dc-stat-label">Total Codes</div><div class="dc-stat-value"><?= (int)$totalDiscounts ?></div></div></div></div>
        <div class="dc-stat"><div class="dc-stat-inner"><div class="dc-stat-icon" style="color:#4ade80"><i class="fa-solid fa-circle-check"></i></div><div><div class="dc-stat-label">Active</div><div class="dc-stat-value"><?= (int)$activeDiscounts ?></div></div></div></div>
        <div class="dc-stat"><div class="dc-stat-inner"><div class="dc-stat-icon" style="color:#facc15"><i class="fa-solid fa-circle-pause"></i></div><div><div class="dc-stat-label">Inactive</div><div class="dc-stat-value"><?= (int)$inactiveDiscounts ?></div></div></div></div>
        <div class="dc-stat"><div class="dc-stat-inner"><div class="dc-stat-icon" style="color:#38bdf8"><i class="fa-solid fa-cart-shopping"></i></div><div><div class="dc-stat-label">Total Uses</div><div class="dc-stat-value"><?= (int)$totalUses ?></div></div></div></div>
    </div>

    <div class="dc-panel">
        <div class="dc-panel-head">
            <div class="dc-panel-title"><i class="fa-solid fa-ticket"></i> Discount Codes</div>
            <div class="dc-tools">
                <div class="dc-search"><i class="fa-solid fa-magnifying-glass"></i><input id="dcSearch" type="search" placeholder="Search code, service or status..."></div>
                <div class="dc-filter-pills" role="group" aria-label="Discount status filter">
                    <button type="button" class="dc-filter active" data-filter="all">All</button>
                    <button type="button" class="dc-filter" data-filter="active">Active</button>
                    <button type="button" class="dc-filter" data-filter="inactive">Inactive</button>
                </div>
            </div>
        </div>
        <div class="dc-table-wrap">
            <?php if (empty($discounts)): ?>
                <div class="dc-empty"><i class="fa-solid fa-ticket"></i><div style="font-weight:900;color:rgba(255,255,255,.62);margin-bottom:4px;">No discounts yet</div><div>Create your first discount code to offer promotions.</div></div>
            <?php else: ?>
            <table class="dc-table" id="discounts_table">
                <thead>
                    <tr>
                        <th>Code</th><th>Usage</th><th>Services</th><th>Status</th><th class="text-end">Amount</th><th class="text-end">Starts</th><th class="text-end">Expires</th><th class="text-end">Created</th><th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody id="dcDiscountRows">
                    <?php foreach ($discounts as $row) :
                        $maxUses = max(1, (int)($row['max_uses'] ?? 1));
                        $uses = max(0, (int)($row['uses'] ?? 0));
                        $usagePct = min(100, round(($uses / $maxUses) * 100));
                        $active = (int)($row['status'] ?? 0) === 1;
                        $services = array_filter(array_map('trim', explode(',', (string)($row['services'] ?? ''))));
                        $amount = ((int)($row['is_fixed'] ?? 0) === 0) ? (($row['amount'] ?? 0) . '%') : (util_format_price_display($row['amount']) . util_format_currency_display('EUR'));
                        $searchBlob = strtolower(trim(($row['code'] ?? '') . ' ' . ($active ? 'active' : 'inactive') . ' ' . implode(' ', $services) . ' ' . $amount));
                    ?>
                    <tr data-search="<?= $h($searchBlob) ?>" data-status="<?= $active ? 'active' : 'inactive' ?>">
                        <td>
                            <div class="dc-code">
                                <div class="dc-code-badge"><i class="fa-solid fa-ticket-simple"></i></div>
                                <div><div class="dc-code-main"><?= $h($row['code'] ?? '') ?></div><div class="dc-code-sub">#<?= (int)($row['id'] ?? 0) ?></div></div>
                            </div>
                        </td>
                        <td>
                            <div class="dc-usage"><div class="dc-usage-top"><span><?= (int)$uses ?> / <?= (int)$maxUses ?></span><span><?= (int)$usagePct ?>%</span></div><div class="dc-progress"><span style="width:<?= (int)$usagePct ?>%"></span></div></div>
                        </td>
                        <td><div class="dc-services"><?php foreach ($services as $service): ?><span class="dc-service"><i class="<?= $h($serviceIcon($service)) ?>"></i><?= $h($serviceLabel($service)) ?></span><?php endforeach; ?></div></td>
                        <td><span class="dc-status <?= $active ? 'active' : 'inactive' ?>"><i class="fa-solid <?= $active ? 'fa-circle-check' : 'fa-circle-pause' ?>"></i><?= $active ? 'Active' : 'Inactive' ?></span></td>
                        <td class="text-end"><span class="dc-amount"><?= $h($amount) ?></span></td>
                        <td class="text-end"><span class="dc-date"><?= util_format_date_display($row['starts_at']) ?></span></td>
                        <td class="text-end"><span class="dc-date"><?= util_format_date_display($row['expires_at']) ?></span></td>
                        <td class="text-end"><span class="dc-date"><?= util_format_date_display($row['created_at']) ?></span></td>
                        <td class="text-end">
                            <div class="dc-actions">
                                <a href="#" data-bs-toggle="modal" data-bs-target="#edit_discount_md" data-bs-json='<?= $h(json_encode($row, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)) ?>' class="dc-btn dc-btn-icon" title="Edit"><i class="fa-duotone fa-edit"></i></a>
                                <?php if ($active) : ?>
                                    <a href="#" data-id="<?= (int)($row['id'] ?? 0) ?>" data-action="admin_disable_discount" class="dc-btn dc-btn-icon dc-btn-warning" title="Disable"><i class="fa-duotone fa-ban"></i></a>
                                <?php else : ?>
                                    <a href="#" data-id="<?= (int)($row['id'] ?? 0) ?>" data-action="admin_enable_discount" class="dc-btn dc-btn-icon dc-btn-success" title="Enable"><i class="fa-solid fa-check"></i></a>
                                    <a href="#" data-id="<?= (int)($row['id'] ?? 0) ?>" data-action="admin_delete_discount" class="dc-btn dc-btn-icon dc-btn-danger" title="Delete"><i class="fa-solid fa-trash-alt"></i></a>
                                <?php endif ?>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <div class="dc-empty dc-hidden" id="dcNoResults"><i class="fa-solid fa-magnifying-glass"></i><div style="font-weight:900;color:rgba(255,255,255,.62);margin-bottom:4px;">No discounts found</div><div>Try changing the search or status filter.</div></div>
            <?php endif; ?>
        </div>
    </div>

    <?= $this->insert('admin/pages/discounts/add') ?>
    <?= $this->insert('admin/pages/discounts/edit') ?>
</div>

<?= $this->start('scripts') ?>
<script src="<?= ASSET_URL ?>/origin/dash/vendor/tom-select/dist/js/tom-select.complete.min.js"></script>
<script src="<?= ASSET_URL ?>/origin/dash/vendor/daterangepicker/moment.min.js"></script>
<script src="<?= ASSET_URL ?>/origin/dash/vendor/daterangepicker/daterangepicker.js"></script>
<script>
$(document).on('ready', function() {
    var start = moment();
    var end = moment().add(30, 'days');
    function cb(start, end) { $('.js-daterangepicker').val(start.format('MMMM D, YYYY') + ' - ' + end.format('MMMM D, YYYY')); }
    HSCore.components.HSDaterangepicker.init('#create_discount_md .js-daterangepicker', { startDate: start, endDate: end }, cb);
    HSCore.components.HSDaterangepicker.init('#edit_discount_form .js-daterangepicker');
    cb(start, end);
    HSCore.components.HSTomSelect.init('#create_discount_md .js-select');
    HSCore.components.HSTomSelect.init('#edit_discount_form .js-select');

    var currentFilter = 'all';
    var $search = $('#dcSearch');
    function applyDiscountFilter(){
        var q = String($search.val() || '').toLowerCase().trim();
        var visible = 0;
        $('#dcDiscountRows tr').each(function(){
            var $row = $(this);
            var statusOk = currentFilter === 'all' || $row.data('status') === currentFilter;
            var searchOk = !q || String($row.data('search') || '').indexOf(q) !== -1;
            var show = statusOk && searchOk;
            $row.toggle(show);
            if (show) visible++;
        });
        $('#dcNoResults').toggleClass('dc-hidden', visible !== 0);
        $('#discounts_table').toggleClass('dc-hidden', visible === 0);
    }
    $search.on('input', applyDiscountFilter);
    $('.dc-filter').on('click', function(){
        $('.dc-filter').removeClass('active');
        $(this).addClass('active');
        currentFilter = $(this).data('filter') || 'all';
        applyDiscountFilter();
    });

    const edit_discount_md = document.getElementById('edit_discount_md');
    if (edit_discount_md) {
        edit_discount_md.addEventListener('show.bs.modal', event => {
            let dc_btn = event.relatedTarget;
            let dc_json = dc_btn.getAttribute('data-bs-json');
            let edit_data = JSON.parse(dc_json);
            $('#edit_discount_form input[name="id"]').val(edit_data.id).trigger('change');
            $('#edit_discount_form input[name="code"]').val(edit_data.code).trigger('change');
            if (edit_data.is_fixed == 1) {
                $('#edit_discount_form #is_fixed4').prop('checked', true).trigger('change');
                $('#edit_discount_form #is_fixed3').prop('checked', false).trigger('change');
                $('#edit_discount_form input[name="amount"]').val((edit_data.amount / 100).toFixed(2));
            } else {
                $('#edit_discount_form #is_fixed3').prop('checked', true).trigger('change');
                $('#edit_discount_form #is_fixed4').prop('checked', false).trigger('change');
                $('#edit_discount_form input[name="amount"]').val(edit_data.amount);
            }
            let starts_at_date = moment(edit_data.starts_at);
            let expires_at_date = moment(edit_data.expires_at);
            $('#edit_discount_form input[name="date_range"]').data('daterangepicker').setStartDate(starts_at_date);
            $('#edit_discount_form input[name="date_range"]').data('daterangepicker').setEndDate(expires_at_date);
            var select = $('#edit_discount_form .js-select')[0];
            if (select && select.tomselect) select.tomselect.clear(true);
            $('#edit_discount_form .js-select option').prop('selected', false);
            if (edit_data.services != null) {
                var services = edit_data.services.split(',');
                services.forEach(function(service) {
                    $('#edit_discount_form .js-select option[value="' + service + '"]').prop('selected', true).trigger('change');
                    if (select && select.tomselect) select.tomselect.addItem(service, true);
                });
            }
            $('#edit_discount_form input[name="max_uses"]').val(edit_data.max_uses).trigger('change');
            $('#edit_discount_form input[name="max_uses_client"]').val(edit_data.max_uses_client).trigger('change');
        });
    }
});
</script>
<?= $this->end() ?>
