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
                            <?php if ($tier < 8) : ?>
                                <?php foreach ($values as $div => $val) : ?>
                                    <tr class="align-middle">
                                        <td class="border-<?= util_format_tier($tier, "val", 1) ?>">
                                            <img src="<?= util_format_rank_img($tier, $div, "val", "mini") ?>" class="me-2 pb-1" width="25px">
                                            <?= util_format_tier($tier, "val") ?> <?= util_format_division($div, "val") ?> &gt;
                                            <?php if ($div == 3) : ?>
                                                <?= util_format_tier($tier + 1, "val") ?>
                                                <?= ($tier != 7) ? util_format_division(1, "val") : null ?>
                                            <?php else : ?>
                                                <?= util_format_tier($tier, "val") ?>
                                                <?= util_format_division($div + 1, "val") ?>
                                            <?php endif ?>
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
                                <?php foreach ($values as $div => $val) : ?>
                                    <tr class="align-middle">
                                        <td class="border-<?= util_format_tier($tier, "val", 1) ?>">
                                            <img src="<?= util_format_rank_img($tier, null, "val", "mini") ?>" class="me-2 pb-1" width="25px">
                                            <?= util_format_tier($tier, "val") ?> &lt; <?= $div ?> RR
                                        </td>
                                        <td>
                                            <input type="number" class="form-control ms-auto text-end" style="width:100px" name="eu-<?= $tier . '-' . $div ?>" value="<?= util_format_price_input($val['eu']) ?>" placeholder="5.5" step="0.01">
                                        </td>
                                        <td>
                                            <input type="number" class="form-control ms-auto text-end" style="width:100px" name="na-<?= $tier . '-' . $div ?>" value="<?= util_format_price_input($val['na']) ?>" placeholder="5.5" step="0.01">
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
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
        <h5 class="card-header-title">RR Discount Pricing <span class="text-muted ms-1">%</span></h5>
    </div>
    <div class="card-body">
        <div class="js-sticky-header">
            <!-- Table -->

            <div class="table-responsive">
                <table class="table table-lg table-borderless table-thead-bordered table-nowrap table-align-middle">
                    <thead class="thead-light rounded">
                        <tr>
                            <th scope="col">RR</th>
                            <th scope="col" class="text-end">Discount (%)</th>
                        </tr>
                    </thead>

                    <tbody>

                        <?php foreach ($data['start_rr'] as $rr => $val) : ?>
                            <tr class="align-middle ">
                                <td>
                                    <?= $rr ?> RR
                                </td>
                                <td>
                                    <input type="number" class="form-control ms-auto text-end" style="width:100px;" name="rr-<?= $rr ?>" value="<?= $val * 100 ?>" placeholder="5.5" max="100" step="0.01">
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