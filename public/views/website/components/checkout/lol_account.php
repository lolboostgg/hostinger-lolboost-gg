<style>
    .checkout-clean-summary {
        display: flex;
        flex-direction: column;
        gap: 0;
    }

    .checkout-clean-summary .checkout-selected-account {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 0 0 14px;
        margin: 0 0 14px;
        border-bottom: 1px solid rgba(255, 255, 255, .07);
    }

    .checkout-clean-summary .checkout-game-icon-wrap {
        width: 44px;
        height: 44px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex: 0 0 44px;
        background: rgba(99,102,241,.14);
        border: 1px solid rgba(99,102,241,.3);
        overflow: hidden;
    }

    .checkout-clean-summary .checkout-game-icon {
        width: 100%;
        height: 100%;
        object-fit: cover;
        display: block;
    }

    .checkout-clean-summary .checkout-account-label {
        color: rgba(255, 255, 255, .45);
        font-size: 10px;
        font-weight: 700;
        line-height: 1;
        margin-bottom: 6px;
        text-transform: uppercase;
        letter-spacing: .06em;
    }

    .checkout-clean-summary .checkout-account-title {
        color: #fff;
        font-size: 13px;
        font-weight: 700;
        line-height: 1.4;
    }

</style>

<div class="checkout-clean-summary">
    <?php
    $checkout_account_title = trim((string)($data['title'] ?? $data['name'] ?? ''));

    // The account game is stored in selling_accounts.game as a slug.
    // Prefer joined game data when available, then fall back to the account slug.
    $checkout_game_slug = strtolower(trim((string)(
        $data['checkout_game_slug']
        ?? $data['game_slug']
        ?? $data['game']
        ?? ''
    )));

    // selling_accounts.game may contain short internal codes.
    // Normalize them to the actual icon filenames used in /website/images/icons/.
    $game_slug_aliases = [
        'lol' => 'league-of-legends',
        'league' => 'league-of-legends',
        'league_of_legends' => 'league-of-legends',
        'val' => 'valorant',
        'tft' => 'teamfight-tactics',
        'teamfight_tactics' => 'teamfight-tactics',
        'cod' => 'call-of-duty',
        'call_of_duty' => 'call-of-duty',
    ];

    if (isset($game_slug_aliases[$checkout_game_slug])) {
        $checkout_game_slug = $game_slug_aliases[$checkout_game_slug];
    }

    $checkout_game_name = trim((string)($data['checkout_game_name'] ?? ''));

    $known_games = [
        'league-of-legends' => 'League of Legends',
        'valorant' => 'Valorant',
        'teamfight-tactics' => 'Teamfight Tactics',
        'call-of-duty' => 'Call of Duty',
    ];

    if ($checkout_game_name === '' && isset($known_games[$checkout_game_slug])) {
        $checkout_game_name = $known_games[$checkout_game_slug];
    }

    if ($checkout_game_name === '' && $checkout_game_slug !== '') {
        $checkout_game_name = ucwords(str_replace('-', ' ', $checkout_game_slug));
    }

    // Game icons use the database slug as their filename.
    $checkout_game_icon = '';
    if ($checkout_game_slug !== '') {
        $checkout_game_icon = rtrim((string)ASSET_URL, '/')
            . '/website/images/icons/'
            . rawurlencode($checkout_game_slug)
            . '.png';
    }

    if ($checkout_account_title !== ''):
        ?>
        <div class="checkout-selected-account">
            <?php if ($checkout_game_icon !== ''): ?>
                <div class="checkout-game-icon-wrap">
                    <img class="checkout-game-icon" src="<?= htmlspecialchars($checkout_game_icon, ENT_QUOTES, 'UTF-8') ?>" alt="<?= htmlspecialchars($checkout_game_name !== '' ? $checkout_game_name : 'Game', ENT_QUOTES, 'UTF-8') ?>">
                </div>
            <?php endif; ?>
            <div class="checkout-account-copy">
                <div class="checkout-account-label"><?= t('Selected Account') ?></div>
                <div class="checkout-account-title"><?= htmlspecialchars($checkout_account_title, ENT_QUOTES, 'UTF-8') ?></div>
            </div>
        </div>
    <?php endif; ?>
    <div class="checkout-benefit-pills">
    <?php if ($data['delivery_type'] == 'instant'): ?>
        <div class="checkout-benefit-pill"><i class="fa-solid fa-check"></i><span><?= t('Ready to play in seconds') ?></span></div>
        <div class="checkout-benefit-pill"><i class="fa-solid fa-check"></i><span><?= t('Full access (email & password changeable)') ?></span></div>
        <div class="checkout-benefit-pill"><i class="fa-solid fa-check"></i><span><?= t('Free warranty and support') ?></span></div>
    <?php else: ?>
        <div class="checkout-benefit-pill"><i class="fa-solid fa-check"></i><span><?= t('Secure manual delivery process') ?></span></div>
        <div class="checkout-benefit-pill"><i class="fa-solid fa-check"></i><span><?= t('Claim via Live Chat for fastest access') ?></span></div>
        <div class="checkout-benefit-pill"><i class="fa-solid fa-check"></i><span><?= t('Login details also sent to your email within 60 minutes') ?></span></div>
    <?php endif; ?>
    </div>
</div>
