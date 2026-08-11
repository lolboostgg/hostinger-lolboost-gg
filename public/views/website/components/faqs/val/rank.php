<?php
$items = [
  [
    'q' => 'Is it safe to buy boosting?',
    'a' => 'We use privacy and security measures (VPN on request, manual play, no third‑party tools). We recommend using Duo Boost for the lowest risk.',
  ],
  [
    'q' => 'When does my order start?',
    'a' => 'Usually within a short time after payment and details submission. You will see the status in your dashboard.',
  ],
  [
    'q' => 'What happens if a game is lost?',
    'a' => 'We keep going until the purchased target (wins / rank / matches) is completed. Any extra games needed are on us.',
  ],
  [
    'q' => 'Can I get a refund?',
    'a' => 'Refunds depend on order progress. If the order has not started, we can usually refund. Contact support for details.',
  ],
];
?>
<?php $this->insert('website/components/faqs/val/_shared', ['items' => $items]) ?>
