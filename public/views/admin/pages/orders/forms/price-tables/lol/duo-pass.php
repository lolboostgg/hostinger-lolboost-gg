<?php
$duration_groups = [
    3 => '3 Hours',
    6 => '6 Hours',
    8 => '8 Hours',
];
?>

<?php foreach ($duration_groups as $duration => $duration_label) : ?>
    <div class="card mb-5">
        <div class="card-header">
            <h5 class="card-header-title">Duo Pass Pricing <span class="text-muted ms-1"><?= $duration_label ?></span></h5>
        </div>
        <div class="card-body">
            <div class="js-sticky-header">
                <div class="table-responsive">
                    <table class="table table-borderless table-thead-bordered table-align-middle mb-0">
                        <thead class="thead-light rounded">
                            <tr>
                                <th scope="col" style="width: 220px;">Rank</th>
                                <th scope="col" class="text-end" style="width: 110px;">EU</th>
                                <th scope="col" class="text-end" style="width: 110px;">NA</th>
                            </tr>
                        </thead>

                        <tbody>
                            <?php foreach ($data['main'] as $rank_id => $duration_values) : ?>
                                <?php $values = $duration_values[$duration] ?? ['eu' => '', 'na' => '']; ?>
                                <tr class="align-middle">
                                    <td class="border-<?= $rank_id > 0 ? util_format_tier($rank_id, 'lol', 1) : 'secondary' ?>" style="white-space: nowrap;">
                                        <?php if ($rank_id > 0) : ?>
                                            <img src="<?= util_format_rank_img($rank_id, null, 'lol', 'mini') ?>" class="me-2 pb-1" width="25px">
                                        <?php endif; ?>
                                        <?= util_get_lol_rank((int) $rank_id) ?>
                                    </td>

                                    <td>
                                        <input
                                            type="number"
                                            class="form-control ms-auto text-end"
                                            style="width:90px"
                                            name="eu-<?= $rank_id . '-' . $duration ?>"
                                            value="<?= isset($values['eu']) && $values['eu'] !== '' ? util_format_price_input($values['eu']) : '' ?>"
                                            placeholder="5.5"
                                            step="0.01">
                                    </td>

                                    <td>
                                        <input
                                            type="number"
                                            class="form-control ms-auto text-end"
                                            style="width:90px"
                                            name="na-<?= $rank_id . '-' . $duration ?>"
                                            value="<?= isset($values['na']) && $values['na'] !== '' ? util_format_price_input($values['na']) : '' ?>"
                                            placeholder="5.5"
                                            step="0.01">
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
<?php endforeach; ?>

<div class="card mb-5">
    <div class="card-header">
        <h5 class="card-header-title">Extra Options <span class="text-muted ms-1">%</span></h5>
    </div>
    <div class="card-body">
        <div class="js-sticky-header">
            <div class="table-responsive">
                <table class="table table-borderless table-thead-bordered table-align-middle mb-0">
                    <thead class="thead-light rounded">
                        <tr>
                            <th scope="col">Option Name</th>
                            <th scope="col" class="text-end" style="width: 140px;">Price (%)</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php foreach ($data['extra'] as $opt => $price) : ?>
                            <tr class="align-middle">
                                <td><?= util_format_replace_opt($opt) ?></td>
                                <td>
                                    <input
                                        type="number"
                                        class="form-control ms-auto text-end"
                                        style="width:100px"
                                        name="extra-<?= $opt ?>"
                                        value="<?= $price * 100 ?>"
                                        placeholder="5.5"
                                        max="100"
                                        step="0.01">
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="card mb-5">
    <div class="card-header">
        <h5 class="card-header-title">Completion Time</h5>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-sm-4">
                <input
                    type="number"
                    class="form-control"
                    name="completion-time"
                    value="<?= $data['completion_time'] ?? 1 ?>"
                    min="1"
                    step="1">
            </div>
        </div>
    </div>
</div>

<?php if (!empty($data['options'])) : ?>
    <?php foreach ($data['options'] as $opt_key => $opt_value) : ?>
        <input type="hidden" name="option-<?= $opt_key ?>" value="<?= $opt_value ?>">
    <?php endforeach; ?>
<?php endif; ?>
