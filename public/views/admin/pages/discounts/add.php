<!-- Modal - New Discount -->
<div class="modal fade dc-modal" id="create_discount_md" aria-hidden="true" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form id="create_discount_form" class="form ajax-form" action="<?= AJAX_URL ?>">
                <input type="text" name="action" value="admin_add_discount" hidden>
                <div class="dc-modal-head">
                    <div class="dc-modal-title">
                        <div class="dc-modal-title-icon"><i class="fa-solid fa-ticket-simple"></i></div>
                        <div>
                            <h2>Create Discount</h2>
                            <p>Add a reusable checkout promotion code.</p>
                        </div>
                    </div>
                    <button type="button" class="dc-modal-close" data-bs-dismiss="modal" aria-label="Close"><i class="fa-solid fa-xmark"></i></button>
                </div>

                <div class="dc-modal-body">
                    <div class="dc-form-grid">
                        <div class="dc-form-full">
                            <label class="dc-label"><i class="fa-solid fa-barcode"></i> Discount Code</label>
                            <input type="text" class="form-control" placeholder="e.g. START15" name="code" autocomplete="off">
                        </div>

                        <div class="dc-form-full">
                            <label class="dc-label"><i class="fa-solid fa-calendar-days"></i> Start and Expiry Date</label>
                            <div class="input-group">
                                <div class="input-group-prepend input-group-text"><i class="fa-duotone fa-calendar-days"></i></div>
                                <input type="text" placeholder="Select start and expiry date" class="js-daterangepicker form-control daterangepicker-custom-input" name="date_range" data-hs-daterangepicker-options='{"parentEl":"#create_discount_md"}'>
                            </div>
                        </div>

                        <div class="dc-form-full">
                            <label class="dc-label"><i class="fa-solid fa-layer-group"></i> Services</label>
                            <div class="tom-select-custom tom-select-custom-with-tags">
                                <select class="js-select form-select" autocomplete="off" multiple data-hs-tom-select-options='{"placeholder":"Select services...","hideSearch":true}' name="services[]">
                                    <option value="">Select services...</option>
                                    <option value="boosting">Boosting</option>
                                    <option value="coaching">Coaching</option>
                                    <option value="account_shop">Account Shop</option>
                                    <option value="smurf_shop">Smurf Shop</option>
                                </select>
                            </div>
                        </div>

                        <div class="dc-form-full">
                            <label class="dc-label"><i class="fa-solid fa-percent"></i> Discount Amount</label>
                            <div class="dc-segment" role="group" aria-label="Discount amount type">
                                <input type="radio" name="is_fixed" value="0" id="is_fixed1" autocomplete="off" checked>
                                <label for="is_fixed1"><i class="fa-solid fa-percent"></i> Percentage</label>
                                <input type="radio" name="is_fixed" value="1" id="is_fixed2" autocomplete="off">
                                <label for="is_fixed2"><i class="fa-solid fa-euro-sign"></i> Fixed Amount</label>
                            </div>
                            <input type="number" class="form-control" placeholder="e.g. 15 or 8.40" name="amount" step="0.01">
                        </div>

                        <div>
                            <label class="dc-label"><i class="fa-solid fa-infinity"></i> Max Uses</label>
                            <input type="number" class="form-control text-center" placeholder="100" name="max_uses" value="100" min="1" max="1000000" step="1">
                        </div>
                        <div>
                            <label class="dc-label"><i class="fa-solid fa-user-check"></i> Max Uses Per Client</label>
                            <input type="number" class="form-control text-center" placeholder="5" name="max_uses_client" value="5" min="1" max="100" step="1">
                        </div>
                    </div>
                </div>

                <div class="dc-modal-footer">
                    <button type="button" data-bs-dismiss="modal" class="dc-btn"><i class="fa-solid fa-xmark"></i> Cancel</button>
                    <button type="submit" class="dc-btn dc-btn-primary" id="create_discount_submit">
                        <span class="indicator-label"><i class="fa-solid fa-plus"></i> Create Discount</span>
                        <span class="indicator-progress"><span class="spinner-border spinner-border-sm align-middle"></span></span>
                        <span class="indicator-success"><i class="fa-regular fa-circle-check"></i></span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
