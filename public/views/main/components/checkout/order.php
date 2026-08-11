<div class="d-flex align-items-center justify-content-between gap-2 bg-secondary px-5 py-2">
    <?php
    switch ($data['form_id']) {
        case 1:
    ?>
            <div class="d-flex flex-column gap-0 align-items-center">
                <img src="<?= util_rank_img('lol', 'mini', $data['start_tier']) ?>" width="50px" alt="">
                <?php if ($data['start_tier'] != 0) : ?>
                    <span class="fw-500"><?= util_format_rank_advanced($data['start_tier'], $data['start_division'], 'lol') . ' ' . $data['start_lp'] . ' ' . 'LP' ?></span>
                <?php endif; ?>
            </div>
            <i class="fa-solid fa-arrow-right-long"></i>
            <div class="d-flex flex-column gap-0 align-items-center">
                <img src="<?= util_rank_img('lol', 'mini', $data['end_tier']) ?>" width="50px" alt="">
                <?php if ($data['end_tier'] != 8) : ?>
                    <span class="fw-500"><?= util_format_rank_advanced($data['end_tier'], $data['end_division'], 'lol') ?></span>
                <?php else : ?>
                    <span class="fw-500"><?= util_format_rank_advanced($data['end_tier'], $data['end_division'], 'lol') . ' ' . $data['end_lp'] . ' ' . 'LP' ?></span>
                <?php endif; ?>
            </div>
        <?php
            break;
        case 2:
        ?>
            <div class="d-flex flex-column gap-0 align-items-center">
                <img src="<?= util_rank_img('lol', 'mini', $data['start_tier']) ?>" width="50px" alt="">
                <?php if ($data['start_tier'] != 8) : ?>
                    <span class="fw-500"><?= util_format_rank_advanced($data['start_tier'], $data['start_division'], 'lol') ?></span>
                <?php else : ?>
                    <span class="fw-500"><?= util_format_rank_advanced($data['start_tier'], $data['start_division'], 'lol') . ' ' . $data['start_lp'] . ' ' . 'LP' ?></span>
                <?php endif; ?>
            </div>
            <i class="fa-solid fa-arrow-right-long text-primary"></i>
            <div class="d-flex flex-column gap-0 align-items-center">
                <span class="fw-bold fs-1 lh-1"><?= $data['matches'] ?></span>
                <span class="fw-500">Wins</span>
            </div>
        <?php
            break;
        case 3:
        ?>
            <div class="d-flex flex-column gap-0 align-items-center">
                <img src="<?= util_rank_img('lol', 'mini', $data['start_tier']) ?>" width="50px" alt="">
                <span class="fw-500"><?= util_format_rank_advanced($data['start_tier'], $data['start_division'], 'lol') ?></span>
            </div>
            <i class="fa-solid fa-arrow-right-long text-primary"></i>
            <div class="d-flex flex-column gap-0 align-items-center">
                <span class="fw-bold fs-1 lh-1"><?= $data['matches'] ?></span>
                <span class="fw-500">Matches</span>
            </div>
        <?php
            break;
        case 4:
        ?>
            <div class="d-flex flex-column gap-0 align-items-center justify-content-center mx-auto">
                <span class="fw-bold fs-1 lh-1"><?= $data['matches'] ?></span>
                <span class="fw-500">Matches</span>
            </div>
        <?php
            break;
        case 5:
        ?>
            <div class="d-flex flex-column gap-0 align-items-center">
                <img src="<?= util_rank_img('val', 'mini', $data['start_tier']) ?>" width="50px" alt="">
                <?php if ($data['start_tier'] != 0) : ?>
                    <span class="fw-500"><?= util_format_rank_advanced($data['start_tier'], $data['start_division'], 'val') . ' ' . $data['start_rr'] . ' ' . 'RR' ?></span>
                <?php endif; ?>
            </div>
            <i class="fa-solid fa-arrow-right-long"></i>
            <div class="d-flex flex-column gap-0 align-items-center">
                <img src="<?= util_rank_img('val', 'mini', $data['end_tier']) ?>" width="50px" alt="">
                <?php if ($data['end_tier'] != 8) : ?>
                    <span class="fw-500"><?= util_format_rank_advanced($data['end_tier'], $data['end_division'], 'val') ?></span>
                <?php else : ?>
                    <span class="fw-500"><?= util_format_rank_advanced($data['end_tier'], $data['end_division'], 'val') . ' ' . $data['end_rr'] . ' ' . 'RR' ?></span>
                <?php endif; ?>
            </div>
        <?php
            break;
        case 6:
        ?>
            <div class="d-flex flex-column gap-0 align-items-center">
                <img src="<?= util_rank_img('val', 'mini', $data['start_tier']) ?>" width="50px" alt="">
                <?php if ($data['start_tier'] != 8) : ?>
                    <span class="fw-500"><?= util_format_rank_advanced($data['start_tier'], $data['start_division'], 'val') ?></span>
                <?php else : ?>
                    <span class="fw-500"><?= util_format_rank_advanced($data['start_tier'], $data['start_division'], 'val') . ' ' . $data['start_rr'] . ' ' . 'RR' ?></span>
                <?php endif; ?>
            </div>
            <i class="fa-solid fa-arrow-right-long text-primary"></i>
            <div class="d-flex flex-column gap-0 align-items-center">
                <span class="fw-bold fs-1 lh-1"><?= $data['matches'] ?></span>
                <span class="fw-500">Wins</span>
            </div>
        <?php
            break;
        case 7:
        ?>
            <div class="d-flex flex-column gap-0 align-items-center">
                <img src="<?= util_rank_img('val', 'mini', $data['start_tier']) ?>" width="50px" alt="">
                <span class="fw-500"><?= util_format_rank_advanced($data['start_tier'], $data['start_division'], 'val') ?></span>
            </div>
            <i class="fa-solid fa-arrow-right-long text-primary"></i>
            <div class="d-flex flex-column gap-0 align-items-center">
                <span class="fw-bold fs-1 lh-1"><?= $data['matches'] ?></span>
                <span class="fw-500">Matches</span>
            </div>
        <?php
            break;
        case 8:
        ?>
            <div class="d-flex flex-column gap-0 align-items-center justify-content-center mx-auto">
                <span class="fw-bold fs-1 lh-1"><?= $data['matches'] ?></span>
                <span class="fw-500">Matches</span>
            </div>
        <?php
            break;
        case 9:
        ?>
            <div class="d-flex flex-column gap-0 align-items-center">
                <img src="<?= util_rank_img('lol', 'mini', $data['start_tier']) ?>" width="50px" alt="">
                <?php if ($data['start_tier'] != 8) : ?>
                    <span class="fw-500"><?= util_format_rank_advanced($data['start_tier'], $data['start_division'], 'lol') ?></span>
                <?php else : ?>
                    <span class="fw-500"><?= util_format_rank_advanced($data['start_tier'], $data['start_division'], 'lol') . ' ' . $data['start_lp'] . ' ' . 'LP' ?></span>
                <?php endif; ?>
            </div>
            <i class="fa-solid fa-arrow-right-long text-primary"></i>
            <div class="d-flex flex-column gap-0 align-items-center">
                <span class="fw-bold fs-1 lh-1"><?= $data['matches'] ?></span>
                <span class="fw-500">Matches</span>
            </div>
        <?php
            break;
        case 15:
        ?>
            <div class="d-flex flex-column gap-0 align-items-center justify-content-center mx-auto">
                <span class="fw-bold fs-1 lh-1"><?= $data['hours'] ?></span>
                <span class="fw-500">Hours</span>
            </div>
        <?php
            break;
        case 16:
        ?>
            <div class="d-flex flex-column gap-0 align-items-center justify-content-center mx-auto">
                <span class="fw-bold fs-1 lh-1"><?= $data['hours'] ?></span>
                <span class="fw-500">Hours</span>
            </div>
    <?php
            break;
    }
    ?>
</div>