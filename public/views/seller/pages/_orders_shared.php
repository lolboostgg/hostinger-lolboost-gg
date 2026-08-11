<?php
/**
 * Shared helpers for the seller order lists (Items, Top Ups, Digital Goods).
 * Every list used to carry its own near-identical copy of these, which is how
 * the money/status/date formatting drifted apart between the three pages.
 */

if (!function_exists('sol_money')) {
    /** Rows store cents in some tables and euros in others; normalize to euros. */
    function sol_money($value): float
    {
        $n = (float)$value;
        return abs($n) >= 100 ? $n / 100 : $n;
    }
}

if (!function_exists('sol_relative')) {
    function sol_relative($raw): string
    {
        $ts = $raw ? strtotime((string)$raw) : 0;
        if (!$ts) return '—';
        $days = (int)floor((time() - $ts) / 86400);
        if ($days < 1)  return 'today';
        if ($days === 1) return '1 day ago';
        if ($days < 7)  return $days . ' days ago';
        if ($days < 14) return '1 week ago';
        if ($days < 30) return (int)floor($days / 7) . ' weeks ago';
        if ($days < 60) return '1 month ago';
        return (int)floor($days / 30) . ' months ago';
    }
}

if (!function_exists('sol_status')) {
    /** Maps every marketplace's raw status onto one shared badge vocabulary. */
    function sol_status($raw): array
    {
        $s = strtolower(trim((string)$raw));

        if (in_array($s, ['refunded', 'refund', 'cancelled', 'canceled', 'failed', 'chargeback'], true)) {
            return ['key' => 'cancelled', 'label' => 'Cancelled', 'tone' => 'is-danger', 'icon' => 'fa-solid fa-rotate-left'];
        }
        if (in_array($s, ['unpaid', 'pending', 'awaiting_payment'], true)) {
            return ['key' => 'unpaid', 'label' => 'Unpaid', 'tone' => 'is-warn', 'icon' => 'fa-solid fa-clock'];
        }
        if (in_array($s, ['paid'], true)) {
            return ['key' => 'paid', 'label' => 'Paid', 'tone' => 'is-paid', 'icon' => 'fa-solid fa-credit-card'];
        }
        if (in_array($s, ['delivered', 'shipped'], true)) {
            return ['key' => 'delivered', 'label' => 'Delivered', 'tone' => 'is-delivered', 'icon' => 'fa-solid fa-truck-fast'];
        }
        if (in_array($s, ['completed', 'complete', 'success', 'fulfilled'], true)) {
            return ['key' => 'completed', 'label' => 'Completed', 'tone' => '', 'icon' => 'fa-solid fa-check'];
        }

        return ['key' => 'sold', 'label' => 'Sold', 'tone' => '', 'icon' => 'fa-solid fa-check'];
    }
}

if (!function_exists('sol_filters')) {
    /**
     * "All" plus one pill per status that actually occurs, so a list never shows
     * a filter that can only ever return an empty table.
     */
    function sol_filters(array $rows): array
    {
        $order = [
            'unpaid'    => ['label' => 'Unpaid',    'icon' => 'fa-solid fa-clock'],
            'paid'      => ['label' => 'Paid',      'icon' => 'fa-solid fa-credit-card'],
            'delivered' => ['label' => 'Delivered', 'icon' => 'fa-solid fa-truck-fast'],
            'completed' => ['label' => 'Completed', 'icon' => 'fa-solid fa-check'],
            'sold'      => ['label' => 'Sold',      'icon' => 'fa-solid fa-check'],
            'cancelled' => ['label' => 'Cancelled', 'icon' => 'fa-solid fa-rotate-left'],
        ];

        $present = [];
        foreach ($rows as $row) {
            $key = strtolower((string)($row['status']['key'] ?? ''));
            if ($key !== '') $present[$key] = true;
        }

        $filters = [['key' => '', 'label' => 'All', 'icon' => 'fa-solid fa-layer-group']];
        foreach ($order as $key => $meta) {
            if (isset($present[$key])) {
                $filters[] = ['key' => $key, 'label' => $meta['label'], 'icon' => $meta['icon']];
            }
        }

        return $filters;
    }
}

if (!function_exists('sol_visible_rows')) {
    /**
     * Unpaid orders are not real sales yet — the buyer only reached checkout.
     * Showing them made the seller lists disagree with the payout balance, so
     * every list drops them and starts at "Paid".
     */
    function sol_visible_rows(array $rows): array
    {
        return array_values(array_filter($rows, static function ($row) {
            return strtolower((string)($row['status']['key'] ?? '')) !== 'unpaid';
        }));
    }
}

if (!function_exists('sol_client_icon')) {
    /**
     * clients.icon is inconsistent across the marketplaces: full URL, a path
     * relative to /public, a bare filename, or empty for guest checkouts. Any of
     * those but the first rendered a broken image (and the initial-letter
     * fallback), so normalize to a usable URL and fall back to the default
     * avatar every other area already uses.
     */
    function sol_client_icon($icon): string
    {
        $icon = trim((string)$icon);
        // default.png is the neutral user avatar. default1.png is the LoLBoost logo
        // (used for System chat messages) and must never stand in for a person.
        $default = 'https://lolboost.gg/public/uploads/icons/default.png';

        if ($icon === '') {
            return $default;
        }
        if (preg_match('~^https?://~i', $icon)) {
            return $icon;
        }
        if ($icon[0] === '/') {
            return rtrim((string)(defined('BASE_URL') ? BASE_URL : ''), '/') . $icon;
        }

        // Bare filename: the client avatars all live in the same upload folder.
        return rtrim((string)(defined('BASE_URL') ? BASE_URL : ''), '/') . '/public/uploads/icons/' . ltrim($icon, '/');
    }
}

if (!function_exists('sol_poke_client_button')) {
    /** Shared poke action for seller order detail views only. */
    function sol_poke_client_button(int $orderId, string $refType): string
    {
        if ($orderId <= 0) return '';
        $type = htmlspecialchars($refType, ENT_QUOTES, 'UTF-8');
        return '<button type="button" class="av-btn-success js-seller-poke-client"'
            . ' data-id="' . $orderId . '" data-ref-type="' . $type . '">'
            . '<i class="fa-solid fa-hand-point-up"></i> Poke Client</button>';
    }
}
