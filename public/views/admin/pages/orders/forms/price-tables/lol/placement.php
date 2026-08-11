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
                            <th scope="col">Rank</th>
                            <th scope="col" class="text-end">EU Price</th>
                            <th scope="col" class="text-end">NA Price</th>
                        </tr>
                    </thead>

                    <tbody>

                        <?php foreach ($data['main'] as $tier => $values) : ?>
                            <?php if ($tier < 8 && $tier > 0) : ?>
                                <?php foreach ($values as $div => $val) : ?>

                                    <tr class="align-middle">
                                        <td class="border-<?= util_format_tier($tier, "lol", 1) ?>">
                                            <img src="<?= util_format_rank_img($tier, $div, "lol", "mini") ?>" class="me-2 pb-1" width="25px">

                                            <?= util_format_tier($tier, "lol") . " " . util_format_division($div, "lol") ?>
                                        </td>
                                        <td>
                                            <input type="number" class="form-control ms-auto text-end" style="width:100px" name="eu-<?= $tier . '-' . $div ?>" value="<?= util_format_price_input($val['eu']) ?>" placeholder="5.5" step="0.01">
                                        </td>
                                        <td>
                                            <input type="number" class="form-control ms-auto text-end" style="width:100px" name="na-<?= $tier . '-' . $div ?>" value="<?= util_format_price_input($val['na']) ?>" placeholder="5.5" step="0.01">
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else : ?>

                                <tr class="align-middle">
                                    <td class="border-<?= util_format_tier($tier, "lol", 1) ?>">
                                        <img src="<?= util_format_rank_img($tier, null, "lol", "mini") ?>" class="me-2 pb-1" width="25px">

                                        <?= util_format_tier($tier, "lol") ?>
                                    </td>
                                    <td>
                                        <input type="number" class="form-control ms-auto text-end" style="width:100px" name="eu-<?= $tier ?>" value="<?= util_format_price_input($values['eu']) ?>" placeholder="5.5" step="0.01">
                                    </td>
                                    <td>
                                        <input type="number" class="form-control ms-auto text-end" style="width:100px" name="na-<?= $tier ?>" value="<?= util_format_price_input($values['na']) ?>" placeholder="5.5" step="0.01">
                                    </td>
                                </tr>

                            <?php endif ?>

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