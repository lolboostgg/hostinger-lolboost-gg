<?php
/**
 * Shared FAQ renderer for Valorant boost forms.
 * Usage: $this->insert('website/components/faqs/val/_shared', ['items' => [...]]);
 */
$items = $items ?? [];
?>
<div class="lb-faq">
  <?php foreach ($items as $i => $it): ?>
    <details class="lb-faq-item" <?= $i === 0 ? 'open' : '' ?>>
      <summary class="lb-faq-q">
        <span><?= t($it['q']) ?></span>
        <span class="lb-faq-icon" aria-hidden="true"></span>
      </summary>
      <div class="lb-faq-a">
        <p><?= t($it['a']) ?></p>
      </div>
    </details>
  <?php endforeach; ?>
</div>
