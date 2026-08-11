<tr>
    <td class="fw-500">
        <a href="<?= BSTR_URL ?>/order/<?= $row['order_id'] ?>">
            <?= util_format_boost_form($row) ?>
        </a>
    </td>
    <td class="fw-500">
        <a href="<?= BSTR_URL ?>/order/<?= $row['id'] ?>">
            #
            <?= $row['id'] ?>
        </a>
    </td>
    <?php
    $booster_price = calculate_booster_cut($row);
    ?>
    <td class="fw-500 booster-price" data-order="<?= $row['id'] ?>">
        <?= util_format_currency_display($row['currency']) . util_format_price_display($booster_price) ?>
    </td>
    <td class="fw-500" data-order="<?= $row['created_at'] ?>">
        <?= util_format_date_display($row['created_at']) ?>
    </td>
    <td class="text-end">
        <a href="<?= BSTR_URL ?>/order/<?= $row['id'] ?>" class="btn btn-white btn-sm">
            <i class="fa-duotone fa-eye me-1 fs-6"></i> View
        </a>
    </td>
</tr>