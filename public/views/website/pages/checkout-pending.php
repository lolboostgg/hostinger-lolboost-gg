<?= $this->layout('website/layouts/master', ['meta' => $meta, 'bodyClass' => 'imprint-page thankyou']) ?>

<div class="header">
    <h1><?= t('Payment Pending ⏳') ?></h1>
</div>

<div class="container">
    <p><?= t('Thank you for choosing us. We\'re currently processing your
        payment, you will get an email once we\'re done.') ?></p>
    <a href="<?= BASE_URL ?>/discord" class="btn primary"><?= t('Join Discord') ?></a>
</div>