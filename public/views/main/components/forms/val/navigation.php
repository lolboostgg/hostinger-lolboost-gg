<?php
$boost_types = [
    'rank-boost' => '<i class="fa-duotone fa-ranking-star me-2"></i> Rank <br/> Boost',
    'win-boost' => '<i class="fa-duotone fa-medal me-2"></i> Win <br/> Boost',
    'unrated-matches' => '<i class="fa-duotone fa-dice-d8 me-2"></i> Unrated <br/> Matches',
    'placements-boost' => '<i class="fa-duotone fa-compass me-2"></i> Placement <br/> Matches',
    'coaching' => '<i class="fa-duotone fa-graduation-cap me-2"></i>Expert <br/> Coaching',
];
?>
<section class="zindex-1 pt-4">
    <div class="container position-relative">
        <button id="slick-prev" class="slick-disabled">
            <i class="fa-regular fa-chevron-left"></i>
        </button>
        <ul class="nav nav-tabs mb-0 obj-rotate-anim nav-slider" role="tablist">
            <?php foreach ($boost_types as $slug => $name): ?>
                <li class="nav-item mb-0 " role="presentation">
                    <a href="<?= BASE_URL ?>/val/<?= $slug ?>"
                        class="nav-link text-nowrap text-center <?= $active == $slug ? 'active bg-gradient-primary' : '' ?>"
                        <?= $active == $slug ? 'aria-selected="true"' : '' ?>>
                        <?= $name ?>
                    </a>
                </li>
            <?php endforeach ?>
        </ul>
        <button id="slick-next">
            <i class="fa-regular fa-chevron-right"></i>
        </button>
    </div>
</section>