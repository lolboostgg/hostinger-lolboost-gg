<?= $this->layout('admin/layouts/main', ['meta' => ['title' => 'Add Prize — Admin', 'h1' => 'Add Prize', 'description' => 'Create new prize.'], 'contain' => true]) ?>

<?= $this->start('styles') ?>
<style>
.al-hero{border-radius:20px;border:1px solid rgba(255,255,255,.07);background:#25282a;padding:20px 24px;display:flex;align-items:center;justify-content:space-between;gap:14px;flex-wrap:wrap;margin-bottom:20px;box-shadow:0 2px 20px rgba(0,0,0,.22);}
.al-hero-left{display:flex;align-items:center;gap:14px;}
.al-hero-icon{width:44px;height:44px;border-radius:13px;background:linear-gradient(135deg,rgba(251,191,36,.2),rgba(251,191,36,.08));border:1px solid rgba(251,191,36,.25);display:flex;align-items:center;justify-content:center;font-size:1.1rem;color:#fbbf24;flex-shrink:0;}
.al-hero-title{font-size:1.1rem;font-weight:950;color:rgba(255,255,255,.92);margin:0;}
.al-hero-sub{font-size:.8rem;color:rgba(255,255,255,.4);margin:2px 0 0;}
.pr-form-card{background:#25282a;border:1px solid rgba(255,255,255,.07);border-radius:20px;overflow:hidden;box-shadow:0 2px 20px rgba(0,0,0,.22);}
.pr-form-header{padding:18px 24px;border-bottom:1px solid rgba(255,255,255,.06);background:rgba(0,0,0,.1);display:flex;align-items:center;gap:10px;}
.pr-form-header-icon{width:32px;height:32px;border-radius:8px;background:rgba(251,191,36,.12);border:1px solid rgba(251,191,36,.22);display:flex;align-items:center;justify-content:center;color:#fbbf24;font-size:.82rem;}
.pr-form-header-title{font-size:.92rem;font-weight:900;color:rgba(255,255,255,.82);}
.pr-form-body{padding:24px;}
.pr-form-footer{padding:16px 24px;border-top:1px solid rgba(255,255,255,.06);background:rgba(0,0,0,.08);display:flex;align-items:center;justify-content:space-between;gap:10px;}
.pr-field{margin-bottom:20px;}
.pr-field:last-child{margin-bottom:0;}
.pr-label{font-size:.72rem;font-weight:800;color:rgba(255,255,255,.4);text-transform:uppercase;letter-spacing:.07em;margin-bottom:7px;display:block;}
.pr-input{width:100%;background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.09);border-radius:11px;color:#fff;font-size:.9rem;padding:10px 14px;outline:none;transition:border-color .15s,box-shadow .15s;font-family:inherit;}
.pr-input:focus{border-color:rgba(251,191,36,.45);box-shadow:0 0 0 3px rgba(251,191,36,.09);}
.pr-input::placeholder{color:rgba(255,255,255,.2);}
.pr-input[type="file"]{padding:8px 14px;cursor:pointer;}
.pr-input[type="file"]::file-selector-button{background:rgba(251,191,36,.12);border:1px solid rgba(251,191,36,.28);color:#fbbf24;border-radius:7px;padding:4px 12px;font-size:.78rem;font-weight:800;cursor:pointer;margin-right:10px;transition:background .12s;}
.pr-input[type="file"]::file-selector-button:hover{background:rgba(251,191,36,.22);}
.pr-input-hint{font-size:.72rem;color:rgba(255,255,255,.25);margin-top:5px;}
.pr-input-wrap{position:relative;display:flex;align-items:center;}
.pr-input-suffix{position:absolute;right:13px;font-size:.82rem;font-weight:700;color:rgba(255,255,255,.3);pointer-events:none;}
.pr-divider{height:1px;background:rgba(255,255,255,.05);margin:20px 0;}
/* Image preview */
.pr-img-preview{width:80px;height:80px;border-radius:12px;object-fit:cover;border:1px solid rgba(255,255,255,.1);margin-bottom:10px;display:none;}
/* Buttons */
.pr-btn-add{display:inline-flex;align-items:center;gap:7px;padding:10px 22px;border-radius:11px;background:rgba(251,191,36,.14);border:1px solid rgba(251,191,36,.35);color:#fbbf24;font-size:.875rem;font-weight:800;cursor:pointer;transition:all .13s;font-family:inherit;}
.pr-btn-add:hover{background:rgba(251,191,36,.22);transform:translateY(-1px);}
.pr-btn-back{display:inline-flex;align-items:center;gap:6px;padding:10px 18px;border-radius:11px;background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.09);color:rgba(255,255,255,.5);font-size:.82rem;font-weight:700;text-decoration:none;transition:all .13s;}
.pr-btn-back:hover{background:rgba(255,255,255,.08);color:rgba(255,255,255,.8);}
</style>
<?= $this->end() ?>

<!-- Hero -->
<div class="al-hero">
    <div class="al-hero-left">
        <div class="al-hero-icon"><i class="fa-solid fa-plus"></i></div>
        <div>
            <h2 class="al-hero-title">New Prize</h2>
            <p class="al-hero-sub">Add a new item to the LB Coins Store</p>
        </div>
    </div>
    <a href="<?= ADMN_URL ?>/prizes" class="pr-btn-back">
        <i class="fa-solid fa-arrow-left"></i> Back to list
    </a>
</div>

<form class="form ajax-form" action="<?= AJAX_URL ?>" method="POST" enctype="multipart/form-data">
    <input type="hidden" name="action" value="admin_add_prize">

    <div class="pr-form-card">
        <div class="pr-form-header">
            <div class="pr-form-header-icon"><i class="fa-solid fa-plus"></i></div>
            <span class="pr-form-header-title">Prize Details</span>
        </div>
        <div class="pr-form-body">

            <div class="pr-field">
                <label for="nameLabel" class="pr-label">Name</label>
                <input type="text" class="pr-input" name="name" id="nameLabel" placeholder="e.g. Gaming Headset, Amazon Voucher…">
            </div>

            <div class="pr-divider"></div>

            <div class="pr-field">
                <label for="descriptionLabel" class="pr-label">Description</label>
                <input type="text" class="pr-input" name="description" id="descriptionLabel" placeholder="Short description shown to clients…">
                <div class="pr-input-hint">Shown in the LB Coins Store beneath the prize name.</div>
            </div>

            <div class="pr-divider"></div>

            <div class="pr-field">
                <label for="pointsLabel" class="pr-label">Coins Required</label>
                <div class="pr-input-wrap">
                    <input type="number" class="pr-input" name="points" id="pointsLabel" placeholder="0" style="padding-right:60px;">
                    <span class="pr-input-suffix">coins</span>
                </div>
                <div class="pr-input-hint">How many LB Coins a client needs to redeem this prize.</div>
            </div>

            <div class="pr-divider"></div>

            <div class="pr-field">
                <label for="imageInput" class="pr-label">Image</label>
                <img src="" class="pr-img-preview" alt="" id="prImgPreview">
                <input type="file" class="pr-input" name="image" id="imageInput" accept="image/*">
                <div class="pr-input-hint">Recommended: square image, at least 200×200px.</div>
            </div>

        </div>
        <div class="pr-form-footer">
            <a href="<?= ADMN_URL ?>/prizes" class="pr-btn-back">
                <i class="fa-solid fa-xmark"></i> Cancel
            </a>
            <button type="submit" class="pr-btn-add">
                <span class="indicator-label"><i class="fa-solid fa-plus"></i> Add Prize</span>
                <span class="indicator-progress"><span class="spinner-border spinner-border-sm align-middle"></span></span>
                <span class="indicator-success"><i class="fa-regular fa-circle-check"></i> Created!</span>
            </button>
        </div>
    </div>
</form>

<?= $this->start('scripts') ?>
<script>
document.getElementById('imageInput').addEventListener('change', function() {
    var file = this.files[0];
    if (!file) return;
    var preview = document.getElementById('prImgPreview');
    preview.src = URL.createObjectURL(file);
    preview.style.display = 'block';
});
</script>
<?= $this->end() ?>
