<?= $this->layout('admin/layouts/main', ['meta' => ['title' => 'Add Seller Rank - Admin Area | LoLBoost.gg', 'h1' => 'Add Seller Rank', 'description' => 'Create a new seller rank.'], 'contain' => true]) ?>

<form class="form ajax-form" action="<?= AJAX_URL ?>" method="POST">
    <input type="hidden" name="action" value="admin_add_seller_rank">

    <div class="card">
        <div class="card-header">
            <h3 class="card-header-title">New Seller Rank Form</h3>
        </div>

        <div class="card-body">
            <div class="row mb-4">
                <label for="nameLabel" class="col-sm-3 col-form-label form-label">Name</label>
                <div class="col-sm-9">
                    <input type="text" class="form-control" name="name" id="nameLabel" placeholder="Beginner Seller" aria-label="Name" required>
                </div>
            </div>

            <div class="row mb-4">
                <label for="minSalesLabel" class="col-sm-3 col-form-label form-label">Minimum Sales</label>
                <div class="col-sm-9">
                    <input type="number" min="0" class="form-control" name="min_sales" id="minSalesLabel" placeholder="20" aria-label="Minimum Sales" required>
                    <small class="text-muted">Seller gets this rank automatically as soon as total sales reaches this value.</small>
                </div>
            </div>

            <div class="row mb-4">
                <label for="feePercentLabel" class="col-sm-3 col-form-label form-label">Fee Percentage</label>
                <div class="col-sm-9">
                    <input type="number" min="0" max="100" step="0.01" class="form-control" name="fee_percent" id="feePercentLabel" placeholder="15" aria-label="Fee Percentage" required>
                </div>
            </div>

            <div class="row mb-4">
                <label for="iconLabel" class="col-sm-3 col-form-label form-label">Icon</label>
                <div class="col-sm-9">
                    <input type="text" class="form-control" name="icon" id="iconLabel" placeholder="https://.../expert.png or fa-solid fa-crown" aria-label="Icon">
                </div>
            </div>

            <div class="row mb-0">
                <label for="sortOrderLabel" class="col-sm-3 col-form-label form-label">Sort Order</label>
                <div class="col-sm-9">
                    <input type="number" min="0" class="form-control" name="sort_order" id="sortOrderLabel" placeholder="0" aria-label="Sort Order">
                </div>
            </div>
        </div>

        <div class="card-footer d-flex gap-2">
            <button type="submit" class="btn btn-primary">
                <span class="indicator-label">Add Seller Rank</span>
                <span class="indicator-progress">
                    <span class="spinner-border spinner-border-sm align-middle"></span>
                </span>
                <span class="indicator-success">
                    <i class="fa-regular fa-circle-check fs-3"></i>
                </span>
            </button>

            <a href="<?= ADMN_URL ?>/seller-ranks" class="btn btn-white">Back to Seller Ranks</a>
        </div>
    </div>
</form>
