<?= $this->layout('admin/layouts/main', ['meta' => ['title' => 'Edit Prize — Admin', 'h1' => 'Edit Prize', 'description' => 'Edit prize.'], 'contain' => true]); ?>

<?= $this->start('styles') ?>
<style>
.al-hero{border-radius:20px;border:1px solid rgba(255,255,255,.07);background:#25282a;padding:20px 24px;display:flex;align-items:center;justify-content:space-between;gap:14px;flex-wrap:wrap;margin-bottom:20px;box-shadow:0 2px 20px rgba(0,0,0,.22);}
.al-hero-left{display:flex;align-items:center;gap:14px;}
.al-hero-icon{width:44px;height:44px;border-radius:13px;background:linear-gradient(135deg,rgba(109,92,255,.25),rgba(176,92,255,.15));border:1px solid rgba(109,92,255,.25);display:flex;align-items:center;justify-content:center;font-size:1.1rem;color:#c4b5fd;flex-shrink:0;}
.al-hero-title{font-size:1.1rem;font-weight:950;color:rgba(255,255,255,.92);margin:0;}
.al-hero-sub{font-size:.8rem;color:rgba(255,255,255,.4);margin:2px 0 0;}
.pr-form-card{background:#25282a;border:1px solid rgba(255,255,255,.07);border-radius:20px;overflow:hidden;box-shadow:0 2px 20px rgba(0,0,0,.22);}
.pr-form-header{padding:18px 24px;border-bottom:1px solid rgba(255,255,255,.06);background:rgba(0,0,0,.1);display:flex;align-items:center;gap:10px;}
.pr-form-header-icon{width:32px;height:32px;border-radius:8px;background:rgba(109,92,255,.14);border:1px solid rgba(109,92,255,.25);display:flex;align-items:center;justify-content:center;color:#c4b5fd;font-size:.82rem;}
.pr-form-header-title{font-size:.92rem;font-weight:900;color:rgba(255,255,255,.82);}
.pr-form-body{padding:24px;}
.pr-form-footer{padding:16px 24px;border-top:1px solid rgba(255,255,255,.06);background:rgba(0,0,0,.08);display:flex;align-items:center;justify-content:space-between;gap:10px;}
.pr-field{margin-bottom:20px;}
.pr-field:last-child{margin-bottom:0;}
.pr-label{font-size:.72rem;font-weight:800;color:rgba(255,255,255,.4);text-transform:uppercase;letter-spacing:.07em;margin-bottom:7px;display:block;}
.pr-input{width:100%;background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.09);border-radius:11px;color:#fff;font-size:.9rem;padding:10px 14px;outline:none;transition:border-color .15s,box-shadow .15s;font-family:inherit;}
.pr-input:focus{border-color:rgba(109,92,255,.5);box-shadow:0 0 0 3px rgba(109,92,255,.13);}
.pr-input::placeholder{color:rgba(255,255,255,.2);}
.pr-input[type="file"]{padding:8px 14px;cursor:pointer;}
.pr-input[type="file"]::file-selector-button{background:rgba(109,92,255,.14);border:1px solid rgba(109,92,255,.3);color:#c4b5fd;border-radius:7px;padding:4px 12px;font-size:.78rem;font-weight:800;cursor:pointer;margin-right:10px;transition:background .12s;}
.pr-input[type="file"]::file-selector-button:hover{background:rgba(109,92,255,.24);}
.pr-input-hint{font-size:.72rem;color:rgba(255,255,255,.25);margin-top:5px;}
.pr-input-wrap{position:relative;display:flex;align-items:center;}
.pr-input-suffix{position:absolute;right:13px;font-size:.82rem;font-weight:700;color:rgba(255,255,255,.3);pointer-events:none;}
.pr-divider{height:1px;background:rgba(255,255,255,.05);margin:20px 0;}
/* Image preview */
.pr-img-preview{width:80px;height:80px;border-radius:12px;object-fit:cover;border:1px solid rgba(255,255,255,.1);margin-bottom:10px;display:block;}
/* Buttons */
.pr-btn-save{display:inline-flex;align-items:center;gap:7px;padding:10px 22px;border-radius:11px;background:rgba(109,92,255,.2);border:1px solid rgba(109,92,255,.4);color:#c4b5fd;font-size:.875rem;font-weight:800;cursor:pointer;transition:all .13s;font-family:inherit;}
.pr-btn-save:hover{background:rgba(109,92,255,.3);transform:translateY(-1px);}
.pr-btn-back{display:inline-flex;align-items:center;gap:6px;padding:10px 18px;border-radius:11px;background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.09);color:rgba(255,255,255,.5);font-size:.82rem;font-weight:700;text-decoration:none;transition:all .13s;}
.pr-btn-back:hover{background:rgba(255,255,255,.08);color:rgba(255,255,255,.8);}
</style>
<?= $this->end() ?>

<!-- Hero -->
<div class="al-hero">
    <div class="al-hero-left">
        <div class="al-hero-icon"><i class="fa-duotone fa-pen-to-square"></i></div>
        <div>
            <h2 class="al-hero-title">Edit Prize</h2>
            <p class="al-hero-sub">Editing: <strong style="color:rgba(255,255,255,.6);"><?= htmlspecialchars($data['name'] ?? '', ENT_QUOTES) ?></strong></p>
        </div>
    </div>
    <a href="<?= ADMN_URL ?>/prizes" class="pr-btn-back">
        <i class="fa-solid fa-arrow-left"></i> Back to list
    </a>
</div>

<form class="form ajax-form" action="<?= AJAX_URL ?>" method="POST" enctype="multipart/form-data">
    <input type="hidden" name="action" value="admin_update_prize">
    <input type="hidden" name="id" value="<?= (int)$data['id'] ?>">

    <div class="pr-form-card">
        <div class="pr-form-header">
            <div class="pr-form-header-icon"><i class="fa-duotone fa-gift"></i></div>
            <span class="pr-form-header-title">Prize Details</span>
        </div>
        <div class="pr-form-body">

            <div class="pr-field">
                <label for="nameLabel" class="pr-label">Name</label>
                <input type="text" class="pr-input" name="name" id="nameLabel" placeholder="e.g. Gaming Headset, Amazon Voucher…" value="<?= htmlspecialchars($data['name'] ?? '', ENT_QUOTES) ?>">
            </div>

            <div class="pr-divider"></div>

            <div class="pr-field">
                <label for="descriptionLabel" class="pr-label">Description</label>
                <input type="text" class="pr-input" name="description" id="descriptionLabel" placeholder="Short description shown to clients…" value="<?= htmlspecialchars($data['description'] ?? '', ENT_QUOTES) ?>">
                <div class="pr-input-hint">Shown in the LB Coins Store beneath the prize name.</div>
            </div>

            <div class="pr-divider"></div>

            <div class="pr-field">
                <label for="pointsLabel" class="pr-label">Coins Required</label>
                <div class="pr-input-wrap">
                    <input type="number" class="pr-input" name="points" id="pointsLabel" placeholder="0" value="<?= htmlspecialchars($data['points'] ?? '', ENT_QUOTES) ?>" style="padding-right:60px;">
                    <span class="pr-input-suffix">coins</span>
                </div>
                <div class="pr-input-hint">How many LB Coins a client needs to redeem this prize.</div>
            </div>

            <div class="pr-divider"></div>

            <div class="pr-field">
                <label for="imageInput" class="pr-label">Image</label>
                <?php if (!empty($data['image'])): ?>
                    <img src="<?= htmlspecialchars($data['image'], ENT_QUOTES) ?>" class="pr-img-preview" alt="Current image" id="prImgPreview">
                <?php else: ?>
                    <img src="" class="pr-img-preview" alt="" id="prImgPreview" style="display:none;">
                <?php endif; ?>
                <input type="file" class="pr-input" name="image" id="imageInput" accept="image/*">
                <div class="pr-input-hint">Recommended: square image, at least 200×200px.</div>
            </div>

        </div>
        <div class="pr-form-footer">
            <a href="<?= ADMN_URL ?>/prizes" class="pr-btn-back">
                <i class="fa-solid fa-xmark"></i> Cancel
            </a>
            <button type="submit" class="pr-btn-save">
                <span class="indicator-label"><i class="fa-duotone fa-floppy-disk"></i> Save Changes</span>
                <span class="indicator-progress"><span class="spinner-border spinner-border-sm align-middle"></span></span>
                <span class="indicator-success"><i class="fa-regular fa-circle-check"></i> Saved!</span>
            </button>
        </div>
    </div>
</form>

<?= $this->start('scripts') ?>
<script>
// Live image preview
document.getElementById('imageInput').addEventListener('change', function() {
    var file = this.files[0];
    if (!file) return;
    var preview = document.getElementById('prImgPreview');
    preview.src = URL.createObjectURL(file);
    preview.style.display = 'block';
});
</script>
<?= $this->end() ?>
