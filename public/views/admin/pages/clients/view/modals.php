<!--begin::Modal - Ban Client-->
<div class="modal fade" id="ban_client_md" aria-hidden="true">
    <!--begin::Modal dialog-->
    <div class="modal-dialog modal-dialog-centered">
        <!--begin::Modal content-->
        <div class="modal-content rounded">
            <div class="modal-close">
                <button type="button" class="btn btn-ghost-light btn-icon btn-sm" data-bs-dismiss="modal"
                    aria-label="Close">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>
            <!--begin:Form-->
            <form class="form ajax-form" action="<?= AJAX_URL ?>">
                <input type="text" name="action" value="admin_ban_client" hidden>
                <input type="text" name="id" value="<?= $data['id'] ?>" hidden>
                <!--begin::Modal body-->
                <div class="modal-body scroll-y">
                    <!--begin::Heading-->
                    <div class="mb-5 text-center">
                        <!--begin::Title-->
                        <h2 class="mb-3">Ban Client</h2>
                        <!--end::Title-->
                    </div>
                    <!--end::Heading-->
                    <!--begin::Input group-->
                    <div class="d-flex flex-column mb-4 fv-row">
                        <!--begin::Label-->
                        <label class="d-flex align-items-center fs-6 fw-semibold mb-2">
                            <span>Reason</span>
                            <i class="fas fa-exclamation-circle ms-2 fs-7" data-bs-toggle="tooltip"
                                title="Reason for the ban."></i>
                        </label>
                        <!--end::Label-->
                        <input type="text" name="reason" class="form-control border" placeholder="Reason for the ban">
                    </div>
                    <!--end::Input group-->
                    <!--begin::Input group-->
                    <div class="d-flex flex-column fv-row">
                        <!--begin::Label-->
                        <label class="fs-6 fw-semibold mb-2">Details
                        </label>
                        <!--end::Label-->
                        <textarea class="form-control border" rows="3" placeholder="Details about the ban"
                            name="details"></textarea>
                    </div>
                    <!--end::Input group-->

                </div>
                <!--end::Modal body-->
                <!--begin::Modal footer-->
                <div class="modal-footer justify-content-start gap-2">
                    <!--begin::Actions-->
                    <button type="submit" class="btn btn-danger">
                        <span class="indicator-label">
                            Ban Client
                        </span>
                        <span class="indicator-progress">
                            <span class="spinner-border spinner-border-sm align-middle"></span>
                        </span>
                        <span class="indicator-success">
                            <i class="fa-regular fa-circle-check fs-3"></i>
                        </span>
                    </button>
                    <button type="button" data-bs-dismiss="modal" class="btn btn-light">Cancel</button>
                    <!--end::Actions-->
                </div>
            </form>
            <!--end:Form-->
        </div>
        <!--end::Modal content-->
    </div>
    <!--end::Modal dialog-->
</div>
<!--end::Modal - Ban Client-->


<!--begin::Modal - Delete Client Account-->
<div class="modal fade" id="delete_client_md" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded">
            <div class="modal-close">
                <button type="button" class="btn btn-ghost-light btn-icon btn-sm" data-bs-dismiss="modal" aria-label="Close">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>
            <form class="form ajax-form" action="<?= AJAX_URL ?>">
                <input type="text" name="action" value="admin_delete_client" hidden>
                <input type="text" name="id" value="<?= $data['id'] ?>" hidden>
                <div class="modal-body scroll-y">
                    <div class="mb-5 text-center">
                        <h2 class="mb-3 text-danger">Delete Client Account</h2>
                        <p class="text-muted mb-0">
                            The client will no longer be able to log in. The email will be released for a new registration, while orders, chats and internal history stay visible for admins and boosters.
                        </p>
                    </div>
                    <div class="alert alert-soft-danger" role="alert">
                        This does not remove historical order data. The current login email will be moved to deleted_email and replaced internally.
                    </div>
                    <div class="d-flex flex-column mb-4 fv-row">
                        <label class="d-flex align-items-center fs-6 fw-semibold mb-2">
                            <span>Reason</span>
                            <i class="fas fa-exclamation-circle ms-2 fs-7" data-bs-toggle="tooltip" title="Reason for the account deletion."></i>
                        </label>
                        <input type="text" name="reason" class="form-control border" placeholder="Reason for the account deletion">
                    </div>
                    <div class="d-flex flex-column fv-row">
                        <label class="fs-6 fw-semibold mb-2">Details</label>
                        <textarea class="form-control border" rows="3" placeholder="Optional details about the account deletion" name="details"></textarea>
                    </div>
                </div>
                <div class="modal-footer justify-content-start gap-2">
                    <button type="submit" class="btn btn-danger">
                        <span class="indicator-label">Delete Account</span>
                        <span class="indicator-progress"><span class="spinner-border spinner-border-sm align-middle"></span></span>
                        <span class="indicator-success"><i class="fa-regular fa-circle-check fs-3"></i></span>
                    </button>
                    <button type="button" data-bs-dismiss="modal" class="btn btn-light">Cancel</button>
                </div>
            </form>
        </div>
    </div>
</div>
<!--end::Modal - Delete Client Account-->

<!--begin::Modal - Update Balance-->
<div class="modal fade" id="update_coins_modal" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width:560px;">
        <div class="modal-content rounded lb-balance-modal">
            <button type="button" class="lb-bm-close" data-bs-dismiss="modal" aria-label="Close">
                <i class="fa-solid fa-xmark"></i>
            </button>
            <form class="form ajax-form" action="<?= AJAX_URL ?>">
                <input type="text" name="action" value="admin_update_coins" hidden>
                <input type="text" name="id" value="<?= $data['id'] ?>" hidden>

                <div class="lb-bm-head">
                    <div class="lb-bm-icon"><i class="fa-duotone fa-wallet"></i></div>
                    <div>
                        <div class="lb-bm-eyebrow">Client Balance</div>
                        <h2 class="lb-bm-title">Update Balance</h2>
                        <p class="lb-bm-sub">Add or remove LB Coins and Reward Points for this client.</p>
                    </div>
                </div>

                <div class="lb-bm-body">
                    <div class="lb-bm-current">
                        <div class="lb-bm-current-card">
                            <span class="lb-bm-current-icon coin"><img src="<?= ASSET_URL ?>/core/main/img/coin.png" alt=""></span>
                            <div>
                                <small>LB Coins</small>
                                <strong><?= rtrim(rtrim(number_format((float)($data['points'] ?? 0), 2, '.', ''), '0'), '.') ?></strong>
                            </div>
                        </div>
                        <div class="lb-bm-current-card">
                            <span class="lb-bm-current-icon reward"><i class="fa-duotone fa-gift"></i></span>
                            <div>
                                <small>Reward Points</small>
                                <strong><?= rtrim(rtrim(number_format((float)($data['reward_points'] ?? 0), 2, '.', ''), '0'), '.') ?></strong>
                            </div>
                        </div>
                    </div>

                    <label class="lb-bm-label">Balance Type</label>
                    <div class="lb-bm-choice-grid mb-3">
                        <input type="radio" class="btn-check" name="wallet" value="coins" id="wallet_coins" autocomplete="off" checked>
                        <label class="lb-bm-choice" for="wallet_coins">
                            <span class="lb-bm-choice-icon"><img src="<?= ASSET_URL ?>/core/main/img/coin.png" alt=""></span>
                            <span><b>LB Coins</b><small>Shop wallet balance</small></span>
                        </label>

                        <input type="radio" class="btn-check" name="wallet" value="reward_points" id="wallet_reward_points" autocomplete="off">
                        <label class="lb-bm-choice" for="wallet_reward_points">
                            <span class="lb-bm-choice-icon"><i class="fa-duotone fa-gift"></i></span>
                            <span><b>Reward Points</b><small>Lootbox points</small></span>
                        </label>
                    </div>

                    <label class="lb-bm-label">Action</label>
                    <div class="lb-bm-toggle mb-3">
                        <input type="radio" class="btn-check" name="type" value="increment" id="increment" autocomplete="off" checked>
                        <label for="increment"><i class="fa-solid fa-plus"></i> Add</label>
                        <input type="radio" class="btn-check" name="type" value="decrement" id="decrement" autocomplete="off">
                        <label for="decrement"><i class="fa-solid fa-minus"></i> Remove</label>
                    </div>

                    <label class="lb-bm-label">Amount</label>
                    <div class="lb-bm-amount-wrap">
                        <input type="number" step="0.01" min="0" name="points" class="form-control lb-bm-amount" placeholder="Enter amount" required>
                    </div>
                    <div class="lb-bm-note"><i class="fa-duotone fa-circle-info"></i> Reward Points are used for lootboxes. LB Coins stay the normal shop coins.</div>
                </div>

                <div class="lb-bm-footer">
                    <button type="button" data-bs-dismiss="modal" class="btn lb-bm-cancel">Cancel</button>
                    <button type="submit" class="btn lb-bm-submit">
                        <span class="indicator-label"><i class="fa-duotone fa-check"></i> Update Balance</span>
                        <span class="indicator-progress"><span class="spinner-border spinner-border-sm align-middle"></span></span>
                        <span class="indicator-success"><i class="fa-regular fa-circle-check fs-3"></i></span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<style>
.lb-balance-modal{position:relative;overflow:hidden;background:linear-gradient(180deg,#25282a,#202326)!important;border:1px solid rgba(255,255,255,.08)!important;box-shadow:0 24px 80px rgba(0,0,0,.38)!important}.lb-balance-modal:before{content:"";position:absolute;inset:-120px -80px auto auto;width:260px;height:260px;border-radius:50%;background:rgba(109,92,255,.16);filter:blur(18px);pointer-events:none}.lb-bm-close{position:absolute;right:14px;top:14px;width:34px;height:34px;border:0;border-radius:10px;background:rgba(255,255,255,.05);color:rgba(255,255,255,.55);z-index:2}.lb-bm-close:hover{background:rgba(255,255,255,.1);color:#fff}.lb-bm-head{position:relative;z-index:1;display:flex;gap:14px;align-items:center;padding:24px 26px 18px;border-bottom:1px solid rgba(255,255,255,.07);background:rgba(255,255,255,.018)}.lb-bm-icon{width:48px;height:48px;border-radius:15px;display:flex;align-items:center;justify-content:center;background:rgba(109,92,255,.16);border:1px solid rgba(109,92,255,.28);color:#c4b5fd;font-size:1.15rem;flex-shrink:0}.lb-bm-eyebrow{font-size:.68rem;font-weight:950;text-transform:uppercase;letter-spacing:.1em;color:#9f8cff;margin-bottom:3px}.lb-bm-title{font-size:1.25rem;font-weight:950;color:rgba(255,255,255,.95);margin:0}.lb-bm-sub{font-size:.82rem;color:rgba(255,255,255,.46);margin:4px 0 0}.lb-bm-body{padding:20px 26px}.lb-bm-current{display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-bottom:18px}.lb-bm-current-card{display:flex;align-items:center;gap:10px;border:1px solid rgba(255,255,255,.08);background:rgba(255,255,255,.035);border-radius:14px;padding:12px}.lb-bm-current-icon{width:36px;height:36px;border-radius:11px;display:flex;align-items:center;justify-content:center;background:rgba(109,92,255,.12);border:1px solid rgba(109,92,255,.2);color:#c4b5fd;flex-shrink:0}.lb-bm-current-icon img{width:22px;height:22px;object-fit:contain}.lb-bm-current-card small{display:block;font-size:.66rem;color:rgba(255,255,255,.38);font-weight:900;text-transform:uppercase;letter-spacing:.06em}.lb-bm-current-card strong{display:block;font-size:1rem;color:rgba(255,255,255,.92);font-weight:950}.lb-bm-label{font-size:.7rem;font-weight:950;text-transform:uppercase;letter-spacing:.08em;color:rgba(255,255,255,.5);margin-bottom:8px}.lb-bm-choice-grid{display:grid;grid-template-columns:1fr 1fr;gap:10px}.lb-bm-choice{cursor:pointer;display:flex;gap:10px;align-items:center;border:1px solid rgba(255,255,255,.08);background:rgba(255,255,255,.035);border-radius:14px;padding:13px;transition:border-color .14s,background .14s,transform .14s}.lb-bm-choice:hover{background:rgba(255,255,255,.055);transform:translateY(-1px)}.btn-check:checked+.lb-bm-choice{border-color:rgba(109,92,255,.58);background:rgba(109,92,255,.16);box-shadow:0 0 0 3px rgba(109,92,255,.08)}.lb-bm-choice-icon{width:34px;height:34px;border-radius:10px;display:flex;align-items:center;justify-content:center;background:rgba(109,92,255,.12);color:#c4b5fd;flex-shrink:0}.lb-bm-choice-icon img{width:22px;height:22px;object-fit:contain}.lb-bm-choice b{display:block;color:rgba(255,255,255,.9);font-size:.85rem}.lb-bm-choice small{display:block;color:rgba(255,255,255,.42);font-size:.72rem;margin-top:1px}.lb-bm-toggle{display:grid;grid-template-columns:1fr 1fr;gap:8px;background:rgba(0,0,0,.14);border:1px solid rgba(255,255,255,.07);padding:5px;border-radius:14px}.lb-bm-toggle label{cursor:pointer;border-radius:10px;padding:10px;text-align:center;font-weight:900;color:rgba(255,255,255,.52);transition:background .14s,color .14s}.lb-bm-toggle label i{margin-right:5px}.lb-bm-toggle .btn-check:checked+label{background:rgba(109,92,255,.22);color:#fff}.lb-bm-amount{height:46px;background:rgba(255,255,255,.04)!important;border:1px solid rgba(255,255,255,.09)!important;border-radius:13px!important;color:#fff!important;font-size:1rem!important;font-weight:800!important}.lb-bm-amount:focus{border-color:rgba(109,92,255,.5)!important;box-shadow:0 0 0 3px rgba(109,92,255,.12)!important}.lb-bm-note{display:flex;gap:8px;margin-top:10px;padding:10px 12px;border-radius:12px;background:rgba(109,92,255,.08);border:1px solid rgba(109,92,255,.18);color:rgba(255,255,255,.58);font-size:.78rem;font-weight:700}.lb-bm-note i{color:#c4b5fd;margin-top:1px}.lb-bm-footer{display:flex;align-items:center;justify-content:flex-end;gap:10px;padding:16px 26px 22px;border-top:1px solid rgba(255,255,255,.07);background:rgba(0,0,0,.08)}.lb-bm-cancel{background:rgba(255,255,255,.05)!important;border:1px solid rgba(255,255,255,.08)!important;color:rgba(255,255,255,.72)!important;border-radius:12px!important;padding:10px 16px!important;font-weight:850!important}.lb-bm-submit{background:linear-gradient(135deg,#6d5cff,#a855f7)!important;color:#fff!important;border:0!important;border-radius:12px!important;padding:10px 18px!important;font-weight:950!important;box-shadow:0 12px 24px rgba(109,92,255,.22)}@media(max-width:575px){.lb-bm-current,.lb-bm-choice-grid{grid-template-columns:1fr}.lb-bm-head,.lb-bm-body,.lb-bm-footer{padding-left:18px;padding-right:18px}.lb-bm-footer{flex-direction:column-reverse;align-items:stretch}.lb-bm-footer .btn{width:100%}}
</style>
<!--end::Modal - Update Balance-->