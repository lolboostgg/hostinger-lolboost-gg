<div class="col-lg-12">
    <div class="d-grid gap-3 gap-lg-5">
        <!-- Form -->
        <form class="form ajax-form" action="<?= AJAX_URL ?>" method="POST" enctype="multipart/form-data">
            <input type="text" name="action" value="admin_update_booster_personals" hidden>
            <input type="text" name="id" value="<?= $data['id'] ?>" hidden>
            <!-- Card -->
            <div class="card">
                <!-- Header -->
                <div class="card-header">
                    <h4 class="card-header-title">Personal Details</h4>
                </div>
                <!-- End Header -->
                <div class="card-body">
    <div class="row g-4">
        <!-- Personal info -->
        <div class="col-lg-6">
            <div class="row mb-4">
                <label for="fullnameLabel" class="col-sm-4 col-form-label form-label">Full Name</label>
                <div class="col-sm-8">
                    <input type="text" class="form-control" name="fullname"
                        value="<?= $data['personal_details']['fullname'] ?? '' ?>" id="fullnameLabel"
                        placeholder="Full name" aria-label="Full name">
                </div>
            </div>

            <div class="row mb-4">
                <label for="dobLabel" class="col-sm-4 col-form-label form-label">Date of Birth</label>
                <div class="col-sm-8">
                    <!-- type="text" avoids showing both the browser-native date picker and any JS date picker your theme initializes -->
                    <input type="text" class="form-control" name="dob"
                        value="<?= $data['personal_details']['dob'] ?? '' ?>" id="dobLabel"
                        placeholder="TT.MM.JJJJ" aria-label="Date of Birth" autocomplete="bday">
                </div>
            </div>

            <div class="row mb-4">
                <label for="addressLabel" class="col-sm-4 col-form-label form-label">Address</label>
                <div class="col-sm-8">
                    <input type="text" class="form-control" name="address"
                        value="<?= $data['personal_details']['address'] ?? '' ?>" id="addressLabel"
                        placeholder="Address" aria-label="Address">
                </div>
            </div>

            <div class="row mb-0">
                <label for="countryLabel" class="col-sm-4 col-form-label form-label">Country</label>
                <div class="col-sm-8">
                    <input type="text" class="form-control" name="country"
                        value="<?= $data['personal_details']['country'] ?? '' ?>" id="countryLabel"
                        placeholder="Country" aria-label="Country">
                </div>
            </div>
        </div>

        <!-- Uploads + previews -->
        <div class="col-lg-6">
            <div class="border rounded p-3 h-100">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <div>
                        <h5 class="mb-0">Documents</h5>
                        <div class="text-muted small">Upload images and preview them before saving.</div>
                    </div>
                </div>

                <!-- Front ID -->
                <div class="mb-4">
                    <label for="id_frontLabel" class="form-label">Front ID Photo</label>
                    <input type="file" name="id_front" id="id_frontLabel" class="form-control" accept="image/*" aria-label="Front ID Photo">
                    <div class="d-flex flex-wrap align-items-center gap-2 mt-2">
                        <?php if (!empty($data['personal_details']['id_front'])): ?>
                            <button type="button" class="btn btn-sm btn-primary js-doc-preview"
                                data-bs-toggle="modal" data-bs-target="#lbDocPreviewModal"
                                data-doc-title="ID Front" data-doc-href="<?= $data['personal_details']['id_front'] ?>">
                                View current
                            </button>
                            <img src="<?= $data['personal_details']['id_front'] ?>" alt="Current ID Front"
                                class="img-thumbnail" style="width:110px;height:70px;object-fit:cover;">
                        <?php else: ?>
                            <span class="badge bg-secondary">No file uploaded</span>
                        <?php endif; ?>

                        <img id="lbPreview_id_front" alt="New upload preview" class="img-thumbnail d-none"
                            style="width:110px;height:70px;object-fit:cover;">
                        <span id="lbPreviewText_id_front" class="text-muted small d-none">New file selected</span>
                    </div>
                </div>

                <!-- Back ID -->
                <div class="mb-4">
                    <label for="id_backLabel" class="form-label">Back ID Photo</label>
                    <input type="file" name="id_back" id="id_backLabel" class="form-control" accept="image/*" aria-label="Back ID Photo">
                    <div class="d-flex flex-wrap align-items-center gap-2 mt-2">
                        <?php if (!empty($data['personal_details']['id_back'])): ?>
                            <button type="button" class="btn btn-sm btn-primary js-doc-preview"
                                data-bs-toggle="modal" data-bs-target="#lbDocPreviewModal"
                                data-doc-title="ID Back" data-doc-href="<?= $data['personal_details']['id_back'] ?>">
                                View current
                            </button>
                            <img src="<?= $data['personal_details']['id_back'] ?>" alt="Current ID Back"
                                class="img-thumbnail" style="width:110px;height:70px;object-fit:cover;">
                        <?php else: ?>
                            <span class="badge bg-secondary">No file uploaded</span>
                        <?php endif; ?>

                        <img id="lbPreview_id_back" alt="New upload preview" class="img-thumbnail d-none"
                            style="width:110px;height:70px;object-fit:cover;">
                        <span id="lbPreviewText_id_back" class="text-muted small d-none">New file selected</span>
                    </div>
                </div>

                <!-- Selfie -->
                <div class="mb-0">
                    <label for="selfieLabel" class="form-label">Selfie</label>
                    <input type="file" name="selfie" id="selfieLabel" class="form-control" accept="image/*" aria-label="Selfie">
                    <div class="d-flex flex-wrap align-items-center gap-2 mt-2">
                        <?php if (!empty($data['personal_details']['selfie'])): ?>
                            <button type="button" class="btn btn-sm btn-primary js-doc-preview"
                                data-bs-toggle="modal" data-bs-target="#lbDocPreviewModal"
                                data-doc-title="Selfie" data-doc-href="<?= $data['personal_details']['selfie'] ?>">
                                View current
                            </button>
                            <img src="<?= $data['personal_details']['selfie'] ?>" alt="Current Selfie"
                                class="img-thumbnail" style="width:110px;height:70px;object-fit:cover;">
                        <?php else: ?>
                            <span class="badge bg-secondary">No file uploaded</span>
                        <?php endif; ?>

                        <img id="lbPreview_selfie" alt="New upload preview" class="img-thumbnail d-none"
                            style="width:110px;height:70px;object-fit:cover;">
                        <span id="lbPreviewText_selfie" class="text-muted small d-none">New file selected</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

                <div class="card-footer">
                    <button type="submit" class="btn btn-primary">
                        <span class="indicator-label">
                            Update Settings
                        </span>
                        <span class="indicator-progress">
                            <span class="spinner-border spinner-border-sm align-middle"></span>
                        </span>
                        <span class="indicator-success">
                            <i class="fa-regular fa-circle-check fs-3"></i>
                        </span>
                    </button>
                </div>
            </div>
            <!-- End Card -->
        
</form>

<!-- Document Preview Modal -->
<div class="modal fade" id="lbDocPreviewModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content bg-dark border border-dark">
            <div class="modal-header border-0">
                <h5 class="modal-title" id="lbDocPreviewTitle">Preview</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body pt-0">
                <div class="ratio ratio-16x9 d-none" id="lbDocPreviewFrameWrap">
                    <iframe id="lbDocPreviewFrame" src="" style="border:0;width:100%;height:100%;" allowfullscreen></iframe>
                </div>
                <img id="lbDocPreviewImg" src="" alt="Document preview" class="img-fluid rounded d-none" style="max-height:70vh;display:block;margin:0 auto;">
                <div class="text-center text-muted small mt-3">
                    <a href="#" target="_blank" class="link-light text-decoration-underline d-none" id="lbDocPreviewOpen">Open in new tab</a>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
/* Dark theme for common date pickers (daterangepicker + bootstrap-datepicker) */
.daterangepicker{background:#1f232a;border:1px solid #2b313a;color:#fff;box-shadow:0 10px 30px rgba(0,0,0,.35);}
.daterangepicker:before,.daterangepicker:after{border-bottom-color:#2b313a !important;}
.daterangepicker .calendar-table{background:#1f232a;border:0;}
.daterangepicker .calendar-table table{background:transparent;}
.daterangepicker th, .daterangepicker td{color:#fff;}
.daterangepicker td.off, .daterangepicker td.off.in-range, .daterangepicker td.off.start-date, .daterangepicker td.off.end-date{color:rgba(255,255,255,.35);background:transparent;}
.daterangepicker td.available:hover, .daterangepicker th.available:hover{background:#2b313a;color:#fff;}
.daterangepicker td.in-range{background:rgba(91,92,226,.18);color:#fff;}
.daterangepicker td.active, .daterangepicker td.active:hover{background:#5b5ce2;color:#fff;}
.daterangepicker .drp-buttons{border-top:1px solid #2b313a;background:#1b1f24;}
.daterangepicker .drp-buttons .btn{color:#fff;border-color:#2b313a;}
.daterangepicker .drp-buttons .btn:hover{background:#2b313a;}
.daterangepicker select.monthselect, .daterangepicker select.yearselect{background:#1f232a;color:#fff;border:1px solid #2b313a;border-radius:.375rem;padding:.25rem .5rem;}
.daterangepicker select.monthselect:focus, .daterangepicker select.yearselect:focus{outline:none;box-shadow:0 0 0 .2rem rgba(91,92,226,.25);}

/* bootstrap-datepicker (fallback) */
.datepicker, .datepicker-dropdown{background:#1f232a;border:1px solid #2b313a;color:#fff;box-shadow:0 10px 30px rgba(0,0,0,.35);}
.datepicker table{background:transparent;}
.datepicker table tr td, .datepicker table tr th{color:#fff;}
.datepicker table tr td.old, .datepicker table tr td.new{color:rgba(255,255,255,.35);}
.datepicker table tr td:hover, .datepicker table tr th:hover{background:#2b313a;}
.datepicker table tr td.active, .datepicker table tr td.active:hover{background:#5b5ce2;color:#fff;}
.datepicker table tr td.today, .datepicker table tr td.today:hover{background:rgba(91,92,226,.18);color:#fff;}
.datepicker table tr td.disabled, .datepicker table tr td.disabled:hover{color:rgba(255,255,255,.25);background:transparent;}
</style>

<script>
(function(){
    function isPdf(url){
        return (url || '').toLowerCase().split('?')[0].endsWith('.pdf');
    }
function bindImageInput(inputId, imgId, textId){
    var input = document.getElementById(inputId);
    var img = document.getElementById(imgId);
    var txt = document.getElementById(textId);
    if(!input || !img) return;

    input.addEventListener('change', function(){
        var file = input.files && input.files[0] ? input.files[0] : null;
        if(!file){
            img.classList.add('d-none');
            img.removeAttribute('src');
            if(txt) txt.classList.add('d-none');
            return;
        }
        if(!file.type || file.type.indexOf('image/') !== 0){
            img.classList.add('d-none');
            img.removeAttribute('src');
            if(txt){ txt.textContent = 'Selected file is not an image'; txt.classList.remove('d-none'); }
            return;
        }
        var url = URL.createObjectURL(file);
        img.setAttribute('src', url);
        img.classList.remove('d-none');
        if(txt){ txt.textContent = 'New file: ' + file.name; txt.classList.remove('d-none'); }
    });
}

// Live previews for newly selected files
bindImageInput('id_frontLabel', 'lbPreview_id_front', 'lbPreviewText_id_front');
bindImageInput('id_backLabel', 'lbPreview_id_back', 'lbPreviewText_id_back');
bindImageInput('selfieLabel', 'lbPreview_selfie', 'lbPreviewText_selfie');

    document.addEventListener('click', function(e){
        var btn = e.target.closest('.js-doc-preview');
        if(!btn) return;
        var href = btn.getAttribute('data-doc-href') || '';
        var title = btn.getAttribute('data-doc-title') || 'Preview';

        var modalTitle = document.getElementById('lbDocPreviewTitle');
        var img = document.getElementById('lbDocPreviewImg');
        var frameWrap = document.getElementById('lbDocPreviewFrameWrap');
        var frame = document.getElementById('lbDocPreviewFrame');
        var open = document.getElementById('lbDocPreviewOpen');

        if(modalTitle) modalTitle.textContent = title;

        // reset
        if(img){ img.classList.add('d-none'); img.removeAttribute('src'); }
        if(frameWrap){ frameWrap.classList.add('d-none'); }
        if(frame){ frame.removeAttribute('src'); }
        if(open){ open.classList.remove('d-none'); open.setAttribute('href', href); }

        if(!href || href.indexOf('javascript:') === 0){
            if(open){ open.classList.add('d-none'); }
            return;
        }

        if(isPdf(href)){
            if(frameWrap) frameWrap.classList.remove('d-none');
            if(frame) frame.setAttribute('src', href);
        }else{
            if(img) { img.classList.remove('d-none'); img.setAttribute('src', href); }
        }
    });

    // Clean up when modal closes
    var m = document.getElementById('lbDocPreviewModal');
    if(m){
        m.addEventListener('hidden.bs.modal', function(){
            var img = document.getElementById('lbDocPreviewImg');
            var frame = document.getElementById('lbDocPreviewFrame');
            if(img){ img.classList.add('d-none'); img.removeAttribute('src'); }
            if(frame){ frame.removeAttribute('src'); }
        });
    }
})();
</script>

    </div>
</div>