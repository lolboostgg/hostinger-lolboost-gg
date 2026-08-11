<div class="card mb-5">
    <div class="card-header">
        <h5 class="card-header-title">Boost Form Pricing <span class="text-muted ms-1">EUR</span></h5>
    </div>
    <div class="card-body">
        <div class="js-sticky-header">
            <!-- Table -->

            <div class="table-responsive">
                <table class="table table-lg table-borderless table-thead-bordered table-nowrap table-align-middle">
                    <thead class="thead-light rounded">
                        <tr>
                            <th scope="col">Game Mode</th>
                            <th scope="col" class="text-end">EU Price</th>
                            <th scope="col" class="text-end">NA Price</th>
                        </tr>
                    </thead>

                    <tbody>

                        <?php foreach ($data['main'] as $game_mode => $values) : ?>

                            <tr class="align-middle">
                                <td class="border-diamond">
                                    <?= util_format_default_type($game_mode) ?>
                                </td>
                                <td>
                                    <input type="number" class="form-control ms-auto text-end" style="width:100px;" name="eu-<?= $game_mode ?>" value="<?= util_format_price_input($values['eu']) ?>" placeholder="5.5" step="0.01">
                                </td>
                                <td>
                                    <input type="number" class="form-control ms-auto text-end" style="width:100px;" name="na-<?= $game_mode ?>" value="<?= util_format_price_input($values['na']) ?>" placeholder="5.5" step="0.01">
                                </td>
                            </tr>

                        <?php endforeach; ?>

                    </tbody>
                </table>
            </div>
            <!-- End Table -->

        </div>
    </div>
</div>

<div class="card mb-5">
    <div class="card-header">
        <h5 class="card-header-title">Extra Options <span class="text-muted ms-1">%</span></h5>
    </div>
    <div class="card-body">
        <div class="js-sticky-header">
            <!-- Table -->

            <div class="table-responsive">
                <table class="table table-lg table-borderless table-thead-bordered table-nowrap table-align-middle">
                    <thead class="thead-light rounded">
                        <tr>
                            <th scope="col">Option Name</th>
                            <th scope="col" class="text-end">Price (%)</th>
                        </tr>
                    </thead>

                    <tbody>

                        <?php foreach ($data['extra'] as $opt => $price) : ?>
                            <tr class="align-middle">
                                <td>
                                    <?= util_format_replace_opt($opt) ?>
                                </td>
                                <td>
                                    <input type="number" class="form-control ms-auto text-end" style="width:100px;" name="extra-<?= $opt ?>" value="<?= $price * 100 ?>" placeholder="5.5" max="100" step="0.01">
                                </td>
                            </tr>

                        <?php endforeach; ?>

                    </tbody>
                </table>
            </div>
            <!-- End Table -->

        </div>
    </div>
</div>