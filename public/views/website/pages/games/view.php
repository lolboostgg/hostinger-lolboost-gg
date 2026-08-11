<?= $this->layout('website/layouts/master', ['meta' => $meta, 'bodyClass' => 'game-detail-page']) ?>

<?= $this->start('styles') ?>
<style>
    .game-hero,
    .game-detail-section {
        max-width: 1080px;
        margin: 0 auto;
        padding: 0 20px;
    }

    .game-hero {
        padding-top: 72px;
        padding-bottom: 26px;
    }

    .game-hero-card,
    .game-detail-card {
        background: linear-gradient(180deg, rgba(19, 25, 41, .96), rgba(10, 14, 24, .98));
        border: 1px solid rgba(124, 92, 252, .16);
        box-shadow: 0 16px 48px rgba(0,0,0,.24);
        border-radius: 24px;
        padding: 28px;
    }

    .game-hero-top {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 18px;
        margin-bottom: 20px;
    }

    .game-hero-icon {
        width: 72px;
        height: 72px;
        border-radius: 20px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: rgba(255,255,255,.05);
        border: 1px solid rgba(255,255,255,.08);
        overflow: hidden;
        margin-bottom: 18px;
    }

    .game-status {
        display: inline-flex;
        align-items: center;
        padding: 8px 12px;
        border-radius: 999px;
        font-size: .8rem;
        font-weight: 700;
        letter-spacing: .04em;
        text-transform: uppercase;
        background: rgba(59, 130, 246, .14);
        color: #93c5fd;
        white-space: nowrap;
    }

    .game-hero h1 {
        color: #fff;
        font-size: clamp(2rem, 4vw, 3.2rem);
        line-height: 1.05;
        margin: 0 0 14px;
    }

    .game-hero p {
        margin: 0;
        color: rgba(255,255,255,.74);
        font-size: 1.02rem;
        line-height: 1.7;
        max-width: 780px;
    }

    .game-actions {
        display: flex;
        flex-wrap: wrap;
        gap: 12px;
        margin-top: 24px;
    }

    .game-actions a {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-height: 46px;
        padding: 0 18px;
        border-radius: 999px;
        text-decoration: none;
        font-weight: 700;
        transition: transform .16s ease, opacity .16s ease;
    }

    .game-actions a:hover {
        transform: translateY(-2px);
    }

    .game-actions .primary {
        background: linear-gradient(135deg, #7c5cfc, #5b7cff);
        color: #fff;
    }

    .game-actions .secondary {
        background: rgba(255,255,255,.06);
        color: #fff;
        border: 1px solid rgba(255,255,255,.1);
    }

    .game-detail-section {
        padding-bottom: 28px;
    }

    .game-detail-card h2 {
        margin: 0 0 16px;
        color: #fff;
        font-size: 1.35rem;
    }

    .game-detail-card p {
        color: rgba(255,255,255,.72);
        line-height: 1.7;
        margin: 0;
    }

    .game-services {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(170px, 1fr));
        gap: 12px;
        margin-top: 18px;
    }

    .game-service-pill {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 14px 16px;
        border-radius: 16px;
        background: rgba(255,255,255,.05);
        border: 1px solid rgba(255,255,255,.08);
        color: #fff;
        font-weight: 600;
    }

    .game-service-pill::before {
        content: '•';
        color: #7c5cfc;
        font-size: 1.2rem;
        line-height: 1;
    }

    .game-next-steps {
        margin: 0;
        padding-left: 20px;
        color: rgba(255,255,255,.74);
        line-height: 1.8;
    }

    .game-next-steps li + li {
        margin-top: 6px;
    }
</style>
<?= $this->end('styles') ?>

<?php $services = $game['service_tags'] ?? []; ?>

<section class="game-hero">
    <div class="game-hero-card">
        <div class="game-hero-top">
            <div>
                <div class="game-hero-icon"><?= lb_render_game_icon_html($game['slug'], 2.5, $game['name']) ?></div>
                <h1><?= htmlspecialchars($game['name'], ENT_QUOTES) ?></h1>
                <p><?= htmlspecialchars($game['description'] ?? '', ENT_QUOTES) ?></p>
            </div>
            <span class="game-status"><?= t('Upcoming') ?></span>
        </div>

        <div class="game-actions">
            <a class="primary" href="<?= BASE_URL ?>/contact"><?= t('Request This Game') ?></a>
            <a class="secondary" href="<?= BASE_URL ?>/games"><?= t('Back to All Games') ?></a>
        </div>
    </div>
</section>

<section class="game-detail-section">
    <div class="game-detail-card">
        <h2><?= t('Suggested Services') ?></h2>
        <p><?= t('This landing page is already ready for navigation, homepage cards and SEO indexing. When you decide to activate checkout for this title, these are the service blocks that fit best first.') ?></p>

        <?php if (!empty($services)): ?>
            <div class="game-services">
                <?php foreach ($services as $service): ?>
                    <div class="game-service-pill"><?= htmlspecialchars($service, ENT_QUOTES) ?></div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>

<section class="game-detail-section">
    <div class="game-detail-card">
        <h2><?= t('Technical Next Steps') ?></h2>
        <ol class="game-next-steps">
            <li><?= t('Add one or more rows for this game in your boost_forms table.') ?></li>
            <li><?= t('Upload matching pricing JSON files under public/uploads/private/boost-forms/.') ?></li>
            <li><?= t('Create either a dedicated form template or a shared generic form for the game.') ?></li>
            <li><?= t('Add a real logo SVG under public/assets/global/games/ if you want a branded icon instead of the fallback initials badge.') ?></li>
        </ol>
    </div>
</section>
