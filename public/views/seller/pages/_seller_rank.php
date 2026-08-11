<?php
if (!function_exists('seller_rank_rules')) {
    function seller_rank_rules(): array {
        return [
            [
                'key' => 'beginner',
                'label' => 'Beginner',
                'min_sales' => 0,
                'fee_percent' => 15.0,
                'icon_class' => 'fa-solid fa-badge-check text-slate-400',
                'color' => '#94a3b8',
                'next' => 'Expert Seller',
                'next_sales' => 20,
            ],
            [
                'key' => 'expert-seller',
                'label' => 'Expert Seller',
                'min_sales' => 20,
                'fee_percent' => 12.0,
                'icon_class' => 'fa-solid fa-badge-check text-emerald-500',
                'color' => '#22c55e',
                'next' => 'Pro Seller',
                'next_sales' => 40,
            ],
            [
                'key' => 'pro-seller',
                'label' => 'Pro Seller',
                'min_sales' => 40,
                'fee_percent' => 10.0,
                'icon_class' => 'fa-solid fa-badge-check text-violet-500',
                'color' => '#8b5cf6',
                'next' => 'Mythic Seller',
                'next_sales' => 80,
            ],
            [
                'key' => 'mythic-seller',
                'label' => 'Mythic Seller',
                'min_sales' => 80,
                'fee_percent' => 8.0,
                'icon_class' => 'fa-solid fa-badge-check text-amber-400',
                'color' => '#fbbf24',
                'next' => null,
                'next_sales' => null,
            ],
        ];
    }
}

if (!function_exists('seller_rank_normalize')) {
    function seller_rank_normalize($value): string {
        $value = strtolower(trim((string)$value));
        if ($value === '') {
            return '';
        }

        $value = preg_replace('/[^a-z0-9]+/', '-', $value);
        $value = trim((string)$value, '-');

        $aliases = [
            'beginner-seller' => 'beginner',
            'expert' => 'expert-seller',
            'expert-seller' => 'expert-seller',
            'pro' => 'pro-seller',
            'professional' => 'pro-seller',
            'pro-seller' => 'pro-seller',
            'mythic' => 'mythic-seller',
            'mythic-seller' => 'mythic-seller',
        ];

        return $aliases[$value] ?? $value;
    }
}

if (!function_exists('seller_rank_rule_from_value')) {
    function seller_rank_rule_from_value($value): ?array {
        $normalized = seller_rank_normalize($value);
        if ($normalized === '') {
            return null;
        }

        foreach (seller_rank_rules() as $rule) {
            if ($normalized === (string)$rule['key'] || $normalized === seller_rank_normalize($rule['label'])) {
                return $rule;
            }
        }

        return null;
    }
}

if (!function_exists('seller_rank_index')) {
    function seller_rank_index(?array $rank): int {
        if (!$rank) {
            return -1;
        }

        foreach (seller_rank_rules() as $index => $rule) {
            if (($rank['key'] ?? null) === $rule['key'] || ($rank['label'] ?? null) === $rule['label']) {
                return $index;
            }
        }

        return -1;
    }
}

if (!function_exists('seller_total_sales')) {
    function seller_total_sales(array $sellerData = [], ?int $fallback = null): int {
        foreach (['total_sales', 'completed_sales', 'completed_orders', 'seller_sold', 'sold_accounts', 'sold'] as $key) {
            if (isset($sellerData[$key]) && $sellerData[$key] !== '' && $sellerData[$key] !== null) {
                return max(0, (int)$sellerData[$key]);
            }
        }

        if ($fallback !== null) {
            return max(0, $fallback);
        }

        try {
            global $db;
            $sellerId = (int)($sellerData['id'] ?? 0);
            if ($sellerId > 0 && isset($db)) {
                // Unified: selling_accounts + selling_items sold_count + admin_id 51 bonus for seller #28
                $adminBonus = 0;
                if ($sellerId === 28) {
                    try {
                        $adminBonus = (int)$db->single("SELECT COUNT(*) FROM accounts WHERE admin_id = 51 AND status = 1 AND client_id IS NOT NULL");
                    } catch (\Throwable $e) {}
                }

                try {
                    $count = (int)$db->single("
                        SELECT
                            COALESCE((SELECT COUNT(*) FROM selling_accounts WHERE seller_id = {$sellerId} AND sold = 1 AND client_id IS NOT NULL AND client_id <> 0), 0)
                            +
                            COALESCE((SELECT SUM(sold_count) FROM selling_items WHERE seller_id = {$sellerId}), 0)
                    ");
                    return max(0, $count + $adminBonus);
                } catch (\Throwable $e) {}
            }
        } catch (\Throwable $e) {
        }

        return 0;
    }
}

if (!function_exists('seller_rank_apply_stored_icon')) {
    function seller_rank_apply_stored_icon(array $rank, array $sellerData = []): array {
        $stored = trim((string)($sellerData['rank_icon'] ?? ''));
        if ($stored !== '') {
            $rank['icon_class'] = $stored;
        }

        if (empty($rank['icon_class'])) {
            $rank['icon_class'] = 'fa-solid fa-badge-check text-slate-400';
        }

        return $rank;
    }
}

if (!function_exists('seller_rank_from_sales')) {
    function seller_rank_from_sales(int $sales): array {
        $sales = max(0, $sales);
        $current = seller_rank_rules()[0];
        foreach (seller_rank_rules() as $rule) {
            if ($sales >= (int)$rule['min_sales']) {
                $current = $rule;
            }
        }
        $current['sales'] = $sales;
        $current['earn_rate'] = round(100 - (float)$current['fee_percent'], 2);
        $current['sales_to_next'] = $current['next_sales'] !== null ? max(0, (int)$current['next_sales'] - $sales) : 0;
        $current['progress_percent'] = 100;
        $current['icon_class'] = $current['icon_class'] ?? 'fa-solid fa-badge-check text-slate-400';

        $rules = seller_rank_rules();
        $currentIndex = 0;
        foreach ($rules as $idx => $rule) {
            if ($rule['label'] === $current['label']) {
                $currentIndex = $idx;
                break;
            }
        }
        $prevSales = (int)($rules[$currentIndex]['min_sales'] ?? 0);
        $nextSales = $current['next_sales'] !== null ? (int)$current['next_sales'] : null;
        if ($nextSales !== null && $nextSales > $prevSales) {
            $current['progress_percent'] = min(100, max(0, (($sales - $prevSales) / ($nextSales - $prevSales)) * 100));
        }

        return $current;
    }
}

if (!function_exists('seller_resolved_rank')) {
    function seller_resolved_rank(array $sellerData = [], ?int $fallbackSales = null): array {
        $sales = seller_total_sales($sellerData, $fallbackSales);
        $salesRank = seller_rank_from_sales($sales);

        $storedRank = null;
        foreach (['seller_rank', 'rank', 'rank_name', 'seller_rank_name', 'tier', 'seller_tier', 'level', 'seller_level'] as $key) {
            if (isset($sellerData[$key]) && trim((string)$sellerData[$key]) !== '') {
                $storedRank = seller_rank_rule_from_value($sellerData[$key]);
                if ($storedRank) {
                    break;
                }
            }
        }

        $resolved = $salesRank;
        if ($storedRank && seller_rank_index($storedRank) > seller_rank_index($salesRank)) {
            $resolved = $storedRank;
        }

        $resolved = seller_rank_apply_stored_icon($resolved, $sellerData);
        $resolved['sales'] = $sales;
        $resolved['earn_rate'] = round(100 - (float)$resolved['fee_percent'], 2);
        $resolved['sales_to_next'] = $resolved['next_sales'] !== null ? max(0, (int)$resolved['next_sales'] - $sales) : 0;
        $resolved['progress_percent'] = $resolved['next_sales'] !== null && (int)$resolved['next_sales'] > (int)$resolved['min_sales']
            ? min(100, max(0, (($sales - (int)$resolved['min_sales']) / ((int)$resolved['next_sales'] - (int)$resolved['min_sales'])) * 100))
            : 100;

        return $resolved;
    }
}

if (!function_exists('seller_effective_fee_from_rank')) {
    function seller_effective_fee_from_rank(array $sellerData = [], ?int $fallbackSales = null): float {
        if (isset($sellerData['custom_fee_percent']) && $sellerData['custom_fee_percent'] !== '' && $sellerData['custom_fee_percent'] !== null) {
            return (float)$sellerData['custom_fee_percent'];
        }

        $rank = seller_resolved_rank($sellerData, $fallbackSales);
        return (float)$rank['fee_percent'];
    }
}
