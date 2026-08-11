<div class="accordion">
    <!-- 1 -->
    <div class="accordion-item active">
        <div class="accordion-header">
            <h5><?= t('What Is A Net Win Boost?') ?></h5>
        </div>
        <div class="accordion-content" style="display: block;">
            <p><?= t('A Net Win Boost guarantees a specific number of net victories, meaning the difference between wins and losses achieved during the boost.') ?></p>
            <p><?= t('For every game the booster loses, they will play additional games until the required net wins are reached.') ?></p>
            <p>
                <p><strong><?= t('Example:') ?></strong></p>
            <p><?= t('– 2 Net Wins can result in either:') ?></p>
            <ul class="faq-example-list">
                <li><strong><?= t('2 Wins and 0 Losses') ?></strong></li>
                <li><strong><?= t('3 Wins and 1 Loss') ?></strong> <?= t('(since 3 − 1 = 2 net wins).') ?></li>
            </ul>

<p><?= t('This system ensures you always end up with the agreed number of net wins, regardless of any losses during the process.') ?></p>
        </div>
    </div>

    <!-- 2 -->
    <div class="accordion-item">
        <div class="accordion-header">
            <h5><?= t('Can I continue playing the game while my LoL Win Boost is in progress?') ?></h5>
        </div>
        <div class="accordion-content">
            <p><?= t('Yes, you can continue playing during your Win Boost. However, please coordinate with your booster via the customer dashboard to avoid logging in at the same time.') ?></p>
        </div>
    </div>

    <!-- 3 -->
    <div class="accordion-item">
        <div class="accordion-header">
            <h5><?= t('What happens if the Booster loses a game?') ?></h5>
        </div>
        <div class="accordion-content">
            <p><?= t('If the booster loses a game during a Win Boost, additional games will be played to secure the purchased number of wins.
                For example, if you buy 5 wins and the booster loses 3 games, service continues until you have 8 total games (5 wins and 3 losses).') ?></p>
        </div>
    </div>

    <!-- 4 -->
    <div class="accordion-item">
        <div class="accordion-header">
            <h5><?= t('Can I play with the booster in Duo Queue Boosting?') ?></h5>
        </div>
        <div class="accordion-content">
            <p><?= t('Yes. Select the “Duo” option in the extra options before clicking “Buy Boost Now”.') ?></p>
        </div>
    </div>

    <!-- 5 -->
    <div class="accordion-item">
        <div class="accordion-header">
            <h5><?= t('What if my MMR is higher?') ?></h5>
        </div>
        <div class="accordion-content">
            <p><?= t('Please select the average rank of your recent matches rather than only your displayed rank. For instance, if you are shown as Gold but play at a Platinum MMR, choose Platinum when purchasing a Win Boost. This ensures proper match difficulty and best results.') ?></p>
        </div>
    </div>

    <!-- 6 -->
   <div class="accordion-item">
  <div class="accordion-header">
    <h5><?= t('What if I reach a new division and still have net wins remaining?') ?></h5>
  </div>

  <div class="accordion-content">
    <p>
      <?= t('If you are below') ?>
      <strong><?= t('Diamond IV') ?></strong>
      <?= t(', all remaining Net Wins will still be played even after promotion.') ?>
    </p>

    <p>
      <?= t('Starting from') ?>
      <strong><?= t('Diamond IV') ?></strong>
      <?= t(', one additional Net Win will be deducted each time you are promoted to a higher division (e.g., Diamond IV → Diamond III).') ?>
    </p>

    <p>
      <strong><?= t('Example:') ?></strong><br>
      <?= t('If you buy') ?>
      <strong><?= t('10 Net Wins') ?></strong>
      <?= t('in') ?>
      <strong><?= t('Diamond IV') ?></strong>
      <?= t('and get promoted to') ?>
      <strong><?= t('Diamond III') ?></strong>
      <?= t('after your first win, one extra Net Win will be deducted.') ?>
    </p>
  </div>
</div>

<!-- 7 -->
<div class="accordion-item">
    <div class="accordion-header">
        <h5><?= t('How is Aegis of Valor handled?') ?></h5>
    </div>
    <div class="accordion-content">
        <p><?= t('League of Legends added a feature called Aegis of Valor.') ?></p>

        <p><?= t('It can increase the LP you gain for a win or protect you from losing LP after a defeat.') ?></p>

        <p><?= t('If a win gives double or very high LP because of Aegis of Valor, it will count as two wins.') ?></p>

        <p><?= t('This helps your order progress faster and keeps everything fair.') ?></p>
    </div>
</div>

</div>
