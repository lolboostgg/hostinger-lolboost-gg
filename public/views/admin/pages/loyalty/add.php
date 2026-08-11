<?= $this->layout('admin/layouts/main', ['meta' => ['title' => 'Add Loyalty Rank — Admin', 'h1' => 'Add Loyalty Rank', 'description' => 'Create new loyalty rank.'], 'contain' => true]) ?>

<?= $this->start('styles') ?>
<style>
/* ── Hero ── */
.al-hero{border-radius:20px;border:1px solid rgba(255,255,255,.07);background:#25282a;padding:20px 24px;display:flex;align-items:center;justify-content:space-between;gap:14px;flex-wrap:wrap;margin-bottom:20px;box-shadow:0 2px 20px rgba(0,0,0,.22);}
.al-hero-left{display:flex;align-items:center;gap:14px;}
.al-hero-icon{width:44px;height:44px;border-radius:13px;background:linear-gradient(135deg,rgba(74,222,128,.2),rgba(74,222,128,.08));border:1px solid rgba(74,222,128,.25);display:flex;align-items:center;justify-content:center;font-size:1.1rem;color:#4ade80;flex-shrink:0;}
.al-hero-title{font-size:1.1rem;font-weight:950;color:rgba(255,255,255,.92);margin:0;}
.al-hero-sub{font-size:.8rem;color:rgba(255,255,255,.4);margin:2px 0 0;}
/* ── Card ── */
.lr-form-card{background:#25282a;border:1px solid rgba(255,255,255,.07);border-radius:20px;overflow:hidden;box-shadow:0 2px 20px rgba(0,0,0,.22);}
.lr-form-header{padding:18px 24px;border-bottom:1px solid rgba(255,255,255,.06);background:rgba(0,0,0,.1);display:flex;align-items:center;gap:10px;}
.lr-form-header-icon{width:32px;height:32px;border-radius:8px;background:rgba(74,222,128,.12);border:1px solid rgba(74,222,128,.22);display:flex;align-items:center;justify-content:center;color:#4ade80;font-size:.82rem;}
.lr-form-header-title{font-size:.92rem;font-weight:900;color:rgba(255,255,255,.82);}
.lr-form-body{padding:24px;}
.lr-form-footer{padding:16px 24px;border-top:1px solid rgba(255,255,255,.06);background:rgba(0,0,0,.08);display:flex;align-items:center;justify-content:space-between;gap:10px;}
/* ── Field groups ── */
.lr-field{margin-bottom:20px;}
.lr-field:last-child{margin-bottom:0;}
.lr-label{font-size:.72rem;font-weight:800;color:rgba(255,255,255,.4);text-transform:uppercase;letter-spacing:.07em;margin-bottom:7px;display:block;}
.lr-input{width:100%;background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.09);border-radius:11px;color:#fff;font-size:.9rem;padding:10px 14px;outline:none;transition:border-color .15s,box-shadow .15s;font-family:inherit;}
.lr-input:focus{border-color:rgba(74,222,128,.45);box-shadow:0 0 0 3px rgba(74,222,128,.1);}
.lr-input::placeholder{color:rgba(255,255,255,.2);}
.lr-input-hint{font-size:.72rem;color:rgba(255,255,255,.25);margin-top:5px;}
/* ── Prefix/suffix ── */
.lr-input-wrap{position:relative;display:flex;align-items:center;}
.lr-input-prefix{position:absolute;left:13px;font-size:.88rem;font-weight:700;color:rgba(255,255,255,.3);pointer-events:none;}
.lr-input-suffix{position:absolute;right:13px;font-size:.88rem;font-weight:700;color:rgba(255,255,255,.3);pointer-events:none;}
.lr-input-wrap .lr-input{padding-left:28px;}
.lr-input-wrap .lr-input.has-suffix{padding-right:30px;}
/* ── Buttons ── */
.lr-btn-add{display:inline-flex;align-items:center;gap:7px;padding:10px 22px;border-radius:11px;background:rgba(74,222,128,.14);border:1px solid rgba(74,222,128,.35);color:#4ade80;font-size:.875rem;font-weight:800;cursor:pointer;transition:all .13s;font-family:inherit;}
.lr-btn-add:hover{background:rgba(74,222,128,.22);transform:translateY(-1px);}
.lr-btn-back{display:inline-flex;align-items:center;gap:6px;padding:10px 18px;border-radius:11px;background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.09);color:rgba(255,255,255,.5);font-size:.82rem;font-weight:700;text-decoration:none;transition:all .13s;}
.lr-btn-back:hover{background:rgba(255,255,255,.08);color:rgba(255,255,255,.8);}
/* ── Divider ── */
.lr-divider{height:1px;background:rgba(255,255,255,.05);margin:20px 0;}
</style>
<?= $this->end() ?>

<!-- Hero -->
<div class="al-hero">
    <div class="al-hero-left">
        <div class="al-hero-icon"><i class="fa-solid fa-plus"></i></div>
        <div>
            <h2 class="al-hero-title">New Loyalty Rank</h2>
            <p class="al-hero-sub">Create a new cashback tier for loyal clients</p>
        </div>
    </div>
    <a href="<?= ADMN_URL ?>/loyalty" class="lr-btn-back">
        <i class="fa-solid fa-arrow-left"></i> Back to list
    </a>
</div>

<!-- Form -->
<form class="form ajax-form" action="<?= AJAX_URL ?>" method="POST">
    <input type="hidden" name="action" value="admin_add_loyalty_rank">

    <div class="lr-form-card">
        <div class="lr-form-header">
            <div class="lr-form-header-icon"><i class="fa-solid fa-plus"></i></div>
            <span class="lr-form-header-title">Rank Details</span>
        </div>
        <div class="lr-form-body">

            <!-- Name -->
            <div class="lr-field">
                <label for="nameLabel" class="lr-label">Rank Name</label>
                <input type="text" class="lr-input" name="name" id="nameLabel"
                    placeholder="e.g. Gold, Platinum, Diamond…">
                <div class="lr-input-hint">This name is shown to clients on the loyalty page.</div>
            </div>

            <div class="lr-divider"></div>

            <!-- Target Amount -->
            <div class="lr-field">
                <label for="target_amountLabel" class="lr-label">Target Amount</label>
                <div class="lr-input-wrap">
                    <span class="lr-input-prefix">€</span>
                    <input type="number" class="lr-input" name="target_amount" id="target_amountLabel"
                        placeholder="0">
                </div>
                <div class="lr-input-hint">Minimum cumulative spend to reach this tier.</div>
            </div>

            <div class="lr-divider"></div>

            <!-- Cashback -->
            <div class="lr-field">
                <label for="cashbackLabel" class="lr-label">Cashback Percentage</label>
                <div class="lr-input-wrap">
                    <input type="number" class="lr-input has-suffix" name="cashback" id="cashbackLabel"
                        placeholder="0">
                    <span class="lr-input-suffix">%</span>
                </div>
                <div class="lr-input-hint">Percentage of each order returned as loyalty credit.</div>
            </div>

        </div>
        <div class="lr-form-footer">
            <a href="<?= ADMN_URL ?>/loyalty" class="lr-btn-back">
                <i class="fa-solid fa-xmark"></i> Cancel
            </a>
            <button type="submit" class="lr-btn-add">
                <span class="indicator-label"><i class="fa-solid fa-plus"></i> Add Loyalty Rank</span>
                <span class="indicator-progress"><span class="spinner-border spinner-border-sm align-middle"></span></span>
                <span class="indicator-success"><i class="fa-regular fa-circle-check"></i> Created!</span>
            </button>
        </div>
    </div>
</form>
