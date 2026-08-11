<?= $this->layout('website/layouts/master', ['meta' => $meta, 'bodyClass' => 'games-catalog-page']) ?>

<?= $this->start('styles') ?>
<style>
    .games-hero,
    .games-section {
        max-width: 1200px;
        margin: 0 auto;
        padding: 0 20px;
    }

    .games-hero {
        padding-top: 72px;
        padding-bottom: 28px;
        text-align: center;
    }

    .games-hero .eyebrow {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 8px 14px;
        border-radius: 999px;
        background: rgba(124, 92, 252, .12);
        color: #7c5cfc;
        font-size: .85rem;
        font-weight: 700;
        letter-spacing: .04em;
        text-transform: uppercase;
        margin-bottom: 18px;
    }

    .games-hero h1 {
        font-size: clamp(2rem, 4vw, 3.4rem);
        line-height: 1.05;
        margin: 0 0 14px;
        color: #fff;
    }

    .games-hero p {
        max-width: 820px;
        margin: 0 auto;
        color: rgba(255,255,255,.72);
        font-size: 1.05rem;
        line-height: 1.7;
    }

    .games-section {
        padding-bottom: 42px;
    }

    .games-section h2 {
        color: #fff;
        font-size: 1.6rem;
        margin: 0 0 18px;
    }

    .games-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 18px;
    }

    .game-card {
        display: flex;
        flex-direction: column;
        gap: 16px;
        min-height: 100%;
        padding: 22px;
        border-radius: 22px;
        text-decoration: none;
        background: linear-gradient(180deg, rgba(19, 25, 41, .96), rgba(10, 14, 24, .98));
        border: 1px solid rgba(124, 92, 252, .16);
        box-shadow: 0 12px 40px rgba(0, 0, 0, .22);
        transition: transform .16s ease, border-color .16s ease, box-shadow .16s ease;
    }

    .game-card:hover {
        transform: translateY(-3px);
        border-color: rgba(124, 92, 252, .4);
        box-shadow: 0 18px 50px rgba(0, 0, 0, .28);
    }

    .game-card-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
    }

    .game-card-icon {
        width: 54px;
        height: 54px;
        border-radius: 16px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: rgba(255,255,255,.05);
        border: 1px solid rgba(255,255,255,.08);
        overflow: hidden;
        flex-shrink: 0;
    }

    .game-card-badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 6px 10px;
        border-radius: 999px;
        font-size: .78rem;
        font-weight: 700;
        letter-spacing: .04em;
        text-transform: uppercase;
    }

    .game-card-badge.live {
        background: rgba(34, 197, 94, .14);
        color: #86efac;
    }

    .game-card-badge.upcoming {
        background: rgba(59, 130, 246, .14);
        color: #93c5fd;
    }

    .game-card h3 {
        margin: 0;
        color: #fff;
        font-size: 1.2rem;
    }

    .game-card p {
        margin: 0;
        color: rgba(255,255,255,.72);
        line-height: 1.65;
        font-size: .97rem;
    }

    .game-card-tags {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        margin-top: auto;
    }

    .game-card-tags span {
        display: inline-flex;
        align-items: center;
        padding: 7px 10px;
        border-radius: 999px;
        background: rgba(255,255,255,.05);
        color: rgba(255,255,255,.78);
        font-size: .82rem;
        border: 1px solid rgba(255,255,255,.08);
    }

    .game-card-cta {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        color: #fff;
        font-weight: 700;
        margin-top: 4px;
    }
</style>
<?= $this->end('styles') ?>

<?php
$renderCard = function (array $game) {
    $status = $game['status'] ?? 'upcoming';
    $cta = $status === 'live' ? t('Open Services') : t('View Page');
    $tags = array_slice($game['service_tags'] ?? [], 0, 4);
    ?>
    <a class="game-card" href="<?= htmlspecialchars(lb_game_href($game['slug']), ENT_QUOTES) ?>">
        <div class="game-card-head">
            <div class="game-card-icon"><?= lb_render_game_icon_html($game['slug'], 2, $game['name']) ?></div>
            <span class="game-card-badge <?= $status === 'live' ? 'live' : 'upcoming' ?>">
                <?= $status === 'live' ? t('Live') : t('Upcoming') ?>
            </span>
        </div>

        <div>
            <h3><?= htmlspecialchars($game['name'], ENT_QUOTES) ?></h3>
        </div>

        <p><?= htmlspecialchars($game['description'] ?? '', ENT_QUOTES) ?></p>

        <?php if (!empty($tags)): ?>
            <div class="game-card-tags">
                <?php foreach ($tags as $tag): ?>
                    <span><?= htmlspecialchars($tag, ENT_QUOTES) ?></span>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <div class="game-card-cta"><?= $cta ?> <span aria-hidden="true">→</span></div>
    </a>
    <?php
};
?>

<section class="games-hero">
    <div class="eyebrow"><?= t('Multi-Game Expansion') ?></div>
    <h1><?= t('Add More Games to LoLBoost.gg') ?></h1>
    <p><?= t('This page gives you one clean place to surface both your live titles and the next games you want to launch. You can link this from your homepage, navbar or footer right away, then connect real boost forms per game later.') ?></p>
</section>

<section class="games-section">
    <h2><?= t('Live Right Now') ?></h2>
    <div class="games-grid">
        <?php foreach ($liveGames as $game): ?>
            <?php $renderCard($game); ?>
        <?php endforeach; ?>
    </div>
</section>

<section class="games-section">
    <h2><?= t('Next Games') ?></h2>
    <div class="games-grid">
        <?php foreach ($upcomingGames as $game): ?>
            <?php $renderCard($game); ?>
        <?php endforeach; ?>
    </div>
</section>
