<?php

if (!function_exists('lb_db_seller_total_sales')) {
    function lb_db_seller_total_sales(int $sellerId, int $fallback = 0): int
    {
        static $cache = [];

        if ($sellerId <= 0) {
            return max(0, $fallback);
        }
        if (array_key_exists($sellerId, $cache)) {
            return $cache[$sellerId];
        }

        global $db;
        if (!empty($db)) {
            try {
                $value = $db->cell(
                    "SELECT total_sales FROM seller_stats WHERE seller_id = ? LIMIT 1",
                    $sellerId
                );
                if ($value !== false && $value !== null) {
                    return $cache[$sellerId] = max(0, (int)$value);
                }
            } catch (Throwable $e) {
            }
        }

        if (function_exists('get_seller_total_sales')) {
            try {
                return $cache[$sellerId] = max(0, (int)get_seller_total_sales($sellerId));
            } catch (Throwable $e) {
            }
        }

        return $cache[$sellerId] = max(0, $fallback);
    }
}

/**
 * Component: website/components/seller/seller-footer
 * Central seller footer/profile renderer for account cards, item cards and view pages.
 * Usage after include_once:
 * echo lb_render_seller_footer($seller, ['variant' => 'account-card']);
 */

if (!function_exists('lb_seller_footer_slug_from_value')) {
    function lb_seller_footer_slug_from_value($slug = '', $username = ''): string
    {
        $value = trim((string)$slug);
        if ($value === '') $value = trim((string)$username);
        $value = html_entity_decode($value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $value = preg_replace('/\s+/', '-', $value);
        $value = preg_replace('/[^\pL\pN_-]+/u', '', (string)$value);
        $value = trim((string)$value, '-');
        return $value !== '' ? $value : trim((string)$username);
    }
}

if (!function_exists('lb_seller_footer_is_online')) {
    function lb_seller_footer_is_online(array $seller): bool
    {
        if (array_key_exists('is_online', $seller)) return (int)$seller['is_online'] === 1;
        if (array_key_exists('seller_is_online', $seller)) return (int)$seller['seller_is_online'] === 1;
        if (function_exists('seller_detail_is_online')) return seller_detail_is_online($seller);
        return false;
    }
}



if (!function_exists('lb_seller_footer_rank_priority')) {
    function lb_seller_footer_rank_priority(string $rankName): int
    {
        $rankKey = strtolower(trim($rankName));
        if ($rankKey === 'mythic seller') return 4;
        if ($rankKey === 'pro seller') return 3;
        if ($rankKey === 'expert seller') return 2;
        if ($rankKey === 'beginner') return 1;
        return 0;
    }
}

if (!function_exists('lb_seller_footer_rank_from_sales')) {
    function lb_seller_footer_rank_from_sales(int $sold): string
    {
        if ($sold >= 500) return 'Mythic Seller';
        if ($sold >= 100) return 'Pro Seller';
        if ($sold >= 10) return 'Expert Seller';
        return 'Beginner';
    }
}

if (!function_exists('lb_seller_footer_effective_rank')) {
    function lb_seller_footer_effective_rank(string $rankName, string $storedIcon, int $sold): array
    {
        $rankName = trim($rankName);
        $storedIcon = trim($storedIcon);
        $salesRank = lb_seller_footer_rank_from_sales($sold);

        if (lb_seller_footer_rank_priority($salesRank) > lb_seller_footer_rank_priority($rankName)) {
            return [$salesRank, ''];
        }

        if ($rankName === '') {
            return [$salesRank, ''];
        }

        return [$rankName, $storedIcon];
    }
}

if (!function_exists('lb_seller_footer_rank_meta')) {
    function lb_seller_footer_rank_meta($rankName = '', $storedIcon = ''): array
    {
        $rankName = trim((string)$rankName);
        $storedIcon = trim((string)$storedIcon);
        $title = $rankName !== '' ? $rankName : 'Verified Seller';

        $iconClass = $storedIcon;
        if ($iconClass !== '' && preg_match('/class\s*=\s*["\']([^"\']+)["\']/i', $iconClass, $m)) {
            $iconClass = trim((string)$m[1]);
        }
        $iconClass = trim(strip_tags($iconClass));
        $iconClass = preg_replace('/\s+/', ' ', (string)$iconClass);

        $color = '#94a3b8';
        $rankKey = strtolower($rankName);
        if (stripos($storedIcon, 'text-emerald') !== false || $rankKey === 'expert seller') $color = '#22c55e';
        elseif (stripos($storedIcon, 'text-violet') !== false || $rankKey === 'pro seller') $color = '#8b5cf6';
        elseif (stripos($storedIcon, 'text-amber') !== false || $rankKey === 'mythic seller') $color = '#fbbf24';
        elseif (stripos($storedIcon, 'text-slate') !== false || $rankKey === 'beginner') $color = '#94a3b8';

        $iconClass = preg_replace('/\btext-[^\s]+\b/i', '', (string)$iconClass);
        $iconClass = preg_replace('/\b(?:w|h|mr|ml|mt|mb|mx|my|p|px|py)-[^\s]+\b/i', '', (string)$iconClass);
        $iconClass = trim(preg_replace('/\s+/', ' ', (string)$iconClass));

        if ($iconClass === '' || !preg_match('/\b(?:fa[srlbd]?|fa-[a-z0-9-]+|ri-[a-z0-9-]+|bi|bi-[a-z0-9-]+|ph|ph-[a-z0-9-]+|icon-[a-z0-9-]+)\b/i', $iconClass)) {
            $iconClass = 'fa-solid fa-badge-check';
        }
        if (strpos($iconClass, 'fa-') !== false && strpos($iconClass, 'fa-solid') === false && strpos($iconClass, 'fa-regular') === false && strpos($iconClass, 'fa-light') === false && strpos($iconClass, 'fa-duotone') === false && strpos($iconClass, 'fa-brands') === false) {
            $iconClass = 'fa-solid ' . $iconClass;
        }

        return [
            'class' => $iconClass,
            'color' => $color,
            'title' => $title,
            'show'  => ($rankName !== '' || $storedIcon !== ''),
        ];
    }
}

if (!function_exists('lb_seller_footer_total_sales')) {
    function lb_seller_footer_total_sales(array $seller): int
    {
        $sellerId = (int)($seller['id'] ?? $seller['seller_id'] ?? 0);
        $fallback = 0;
        foreach (['seller_total_sales', 'total_sales', 'total_sold', 'seller_sold', 'sold'] as $key) {
            if (isset($seller[$key]) && is_numeric($seller[$key])) {
                $fallback = max($fallback, (int)$seller[$key]);
            }
        }
        return lb_db_seller_total_sales($sellerId, $fallback);
    }
}

if (!function_exists('lb_seller_footer_css')) {
    function lb_seller_footer_css(): string
    {
        static $printed = false;
        if ($printed) return '';
        $printed = true;
        return '<style>
.lb-seller-footer{position:relative;display:flex;align-items:center;justify-content:space-between;gap:14px;min-height:64px;margin-top:14px;padding:12px 14px;border-top:1px solid rgba(255,255,255,.09);border-radius:0 0 18px 18px;background:linear-gradient(180deg,rgba(255,255,255,.025),rgba(255,255,255,.055));overflow:visible;box-sizing:border-box;}
.lb-seller-footer--account,.lb-seller-footer--item{border-radius:0 0 18px 18px;margin-top:16px;margin-left:-14px;margin-right:-14px;margin-bottom:-12px;padding-left:16px;padding-right:16px;border-left:0;border-right:0;border-bottom:0;background:linear-gradient(180deg,rgba(255,255,255,.02),rgba(255,255,255,.065));}.lb-seller-footer--item{border-radius:0 0 14px 14px;}

.item-shop-card>.lb-seller-footer--item{margin-left:0;margin-right:0;margin-bottom:0;margin-top:0;padding-left:18px;padding-right:18px;}
@media(max-width:767px){.item-shop-card>.lb-seller-footer--item{padding-left:14px;padding-right:14px;}}
.lb-seller-footer--profile{border-radius:16px;border:1px solid rgba(99,102,241,.22);border-top:1px solid rgba(99,102,241,.22);padding:18px 20px;margin:0 0 24px;background:linear-gradient(180deg,rgba(255,255,255,.055),rgba(255,255,255,.035));}
.lb-seller-footer--mobile{margin-bottom:16px;}
.lb-seller-footer__left{display:flex;align-items:center;gap:10px;min-width:0;flex:1 1 auto;overflow:visible;}
.lb-seller-footer__avatar{width:38px;height:38px;min-width:38px;min-height:38px;border-radius:999px;object-fit:cover;object-position:center;display:block;border:2px solid rgba(150,109,255,.36);box-shadow:0 0 0 4px rgba(122,92,255,.08);}
.lb-seller-footer--profile .lb-seller-footer__avatar{width:52px;height:52px;min-width:52px;min-height:52px;}
.lb-seller-footer__avatar-placeholder{width:38px;height:38px;min-width:38px;border-radius:999px;display:grid;place-items:center;background:linear-gradient(135deg,rgba(99,102,241,.28),rgba(139,92,246,.18));border:2px solid rgba(150,109,255,.36);color:rgba(255,255,255,.7);}
.lb-seller-footer--profile .lb-seller-footer__avatar-placeholder{width:52px;height:52px;min-width:52px;}
.lb-seller-footer__meta{display:flex;flex-direction:column;gap:4px;min-width:0;overflow:visible;}
.lb-seller-footer__label{font-size:11px;line-height:1;color:rgba(255,255,255,.45);text-transform:uppercase;letter-spacing:.06em;font-weight:800;}
.lb-seller-footer__name{position:relative;display:inline-flex;align-items:center;gap:6px;min-width:0;max-width:100%;overflow:visible!important;color:#fff;text-decoration:none!important;font-weight:850;line-height:1.1;}
.lb-seller-footer__name-text{display:inline-block;min-width:0;max-width:150px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;font-size:14px;letter-spacing:0;text-transform:none;color:#fff;}
.lb-seller-footer--profile .lb-seller-footer__name-text{font-size:15px;max-width:220px;}
.lb-seller-footer__name:hover .lb-seller-footer__name-text{color:#c7d2fe;}
.lb-seller-footer__online{width:8px;height:8px;min-width:8px;min-height:8px;border-radius:999px;background:#22c55e;box-shadow:0 0 0 0 rgba(34,197,94,.72),0 0 14px rgba(34,197,94,.95);animation:lbSellerOnlinePulse 1.45s ease-out infinite;display:inline-block;flex:0 0 8px;}
@keyframes lbSellerOnlinePulse{0%{box-shadow:0 0 0 0 rgba(34,197,94,.72),0 0 14px rgba(34,197,94,.95)}70%{box-shadow:0 0 0 7px rgba(34,197,94,0),0 0 18px rgba(34,197,94,.9)}100%{box-shadow:0 0 0 0 rgba(34,197,94,0),0 0 14px rgba(34,197,94,.95)}}
.lb-seller-footer__verified{flex:0 0 auto;font-size:14px;line-height:1;cursor:pointer;filter:drop-shadow(0 0 8px currentColor);}
.lb-seller-footer--profile .lb-seller-footer__verified{font-size:16px;}
.lb-seller-footer__tooltip{position:absolute;left:0;bottom:calc(100% + 10px);transform:translateY(8px) scale(.96);transform-origin:left bottom;opacity:0;visibility:hidden;pointer-events:none;display:inline-flex;align-items:center;gap:8px;white-space:nowrap;padding:8px 12px;border-radius:11px;background:linear-gradient(180deg,rgba(20,24,48,.98),rgba(10,13,30,.98));border:1px solid rgba(255,255,255,.14);box-shadow:0 14px 30px rgba(0,0,0,.32);color:#fff;font-size:12px;font-weight:800;line-height:1;transition:opacity .18s ease,transform .18s ease,visibility .18s ease;z-index:99999;}
.lb-seller-footer__tooltip:after{content:"";position:absolute;left:18px;top:100%;border-width:6px;border-style:solid;border-color:rgba(10,13,30,.98) transparent transparent transparent;}
.lb-seller-footer__verified:hover + .lb-seller-footer__tooltip,.lb-seller-footer__verified:focus + .lb-seller-footer__tooltip{opacity:1;visibility:visible;transform:translateY(0) scale(1);}
.lb-seller-footer__right{display:inline-flex;align-items:center;justify-content:flex-end;gap:10px;flex:0 0 auto;white-space:nowrap;overflow:visible;}
.lb-seller-footer__stat{height:26px;min-height:26px;display:inline-flex;align-items:center;justify-content:center;gap:5px;padding:0 9px;border-radius:8px;background:rgba(255,255,255,.06);border:1px solid rgba(255,255,255,.075);font-size:11px;font-weight:850;line-height:1;color:rgba(255,255,255,.72);}
.lb-seller-footer--profile .lb-seller-footer__stat{height:38px;min-height:38px;min-width:50px;flex-direction:column;gap:2px;padding:0 12px;}
.lb-seller-footer__stat strong{font-size:12px;color:#fff;line-height:1;}
.lb-seller-footer--profile .lb-seller-footer__stat strong{font-size:15px;}
.lb-seller-footer__stat small{font-size:9px;color:rgba(255,255,255,.45);font-weight:800;text-transform:uppercase;letter-spacing:.04em;}
.lb-seller-footer__trusted{width:30px;min-width:30px;padding:0;color:#22c55e;background:rgba(34,197,94,.10);border-color:rgba(34,197,94,.32);}
.lb-seller-footer--profile .lb-seller-footer__trusted{width:50px;min-width:50px;}
.lb-seller-footer__trusted i{font-size:12px;margin:0;}
.lb-seller-footer--profile .lb-seller-footer__trusted i{font-size:14px;}
@media(max-width:420px){.lb-seller-footer{gap:8px;padding:11px 12px}.lb-seller-footer__name-text{max-width:105px}.lb-seller-footer__right{gap:8px}.lb-seller-footer__stat{padding:0 7px;font-size:10px}.lb-seller-footer__trusted{width:28px;min-width:28px;padding:0}.lb-seller-footer--profile{align-items:flex-start;flex-direction:column}.lb-seller-footer--profile .lb-seller-footer__right{width:100%;justify-content:flex-start}.lb-seller-footer--profile .lb-seller-footer__name-text{max-width:190px}}


/* Integrated full width seller footer, no hard inserted box look. */
body.ranked-accounts-page #accountsGrid.accounts-grid .account-card .lb-seller-footer,
body.ranked-accounts-page #accountsGrid .account-card .lb-seller-footer,
.ranked-accounts-page #accountsGrid.accounts-grid .account-card .lb-seller-footer,
.ranked-accounts-page #accountsGrid .account-card .lb-seller-footer,
body.ranked-accounts-page #accountsGrid.accounts-grid .account-card .seller-info,
body.ranked-accounts-page #accountsGrid .account-card .seller-info,
.ranked-accounts-page #accountsGrid.accounts-grid .account-card .seller-info,
.ranked-accounts-page #accountsGrid .account-card .seller-info{
  width:calc(100% + 48px) !important;
  max-width:none !important;
  min-height:66px !important;
  margin:14px -24px -24px !important;
  padding:14px 24px 16px !important;
  box-sizing:border-box !important;
  border-radius:0 0 22px 22px !important;
  border-top:1px solid rgba(255,255,255,.045) !important;
  border-left:0 !important;
  border-right:0 !important;
  border-bottom:0 !important;
  background:linear-gradient(180deg,rgba(255,255,255,.012) 0%,rgba(255,255,255,.032) 48%,rgba(255,255,255,.045) 100%) !important;
  box-shadow:inset 0 1px 0 rgba(255,255,255,.012) !important;
  overflow:visible !important;
}

body.ranked-accounts-page #accountsGrid.accounts-grid .account-card .lb-seller-footer__avatar,
body.ranked-accounts-page #accountsGrid .account-card .lb-seller-footer__avatar,
.ranked-accounts-page #accountsGrid.accounts-grid .account-card .lb-seller-footer__avatar,
.ranked-accounts-page #accountsGrid .account-card .lb-seller-footer__avatar,
body.ranked-accounts-page #accountsGrid.accounts-grid .account-card .seller-info__avatar,
body.ranked-accounts-page #accountsGrid .account-card .seller-info__avatar,
.ranked-accounts-page #accountsGrid.accounts-grid .account-card .seller-info__avatar,
.ranked-accounts-page #accountsGrid .account-card .seller-info__avatar{
  width:36px !important;
  height:36px !important;
  min-width:36px !important;
  min-height:36px !important;
}

body.ranked-accounts-page #accountsGrid.accounts-grid .account-card .lb-seller-footer__stat,
body.ranked-accounts-page #accountsGrid .account-card .lb-seller-footer__stat,
.ranked-accounts-page #accountsGrid.accounts-grid .account-card .lb-seller-footer__stat,
.ranked-accounts-page #accountsGrid .account-card .lb-seller-footer__stat,
body.ranked-accounts-page #accountsGrid.accounts-grid .account-card .seller-info__sold,
body.ranked-accounts-page #accountsGrid .account-card .seller-info__sold,
.ranked-accounts-page #accountsGrid.accounts-grid .account-card .seller-info__sold,
.ranked-accounts-page #accountsGrid .account-card .seller-info__sold{
  background:rgba(255,255,255,.045) !important;
  border-color:rgba(255,255,255,.06) !important;
}

body.ranked-accounts-page #accountsGrid.accounts-grid .account-card,
body.ranked-accounts-page #accountsGrid .account-card,
.ranked-accounts-page #accountsGrid.accounts-grid .account-card,
.ranked-accounts-page #accountsGrid .account-card{
  overflow:hidden !important;
}

@media(max-width:767px){
  body.ranked-accounts-page #accountsGrid.accounts-grid .account-card .lb-seller-footer,
  body.ranked-accounts-page #accountsGrid .account-card .lb-seller-footer,
  .ranked-accounts-page #accountsGrid.accounts-grid .account-card .lb-seller-footer,
  .ranked-accounts-page #accountsGrid .account-card .lb-seller-footer,
  body.ranked-accounts-page #accountsGrid.accounts-grid .account-card .seller-info,
  body.ranked-accounts-page #accountsGrid .account-card .seller-info,
  .ranked-accounts-page #accountsGrid.accounts-grid .account-card .seller-info,
  .ranked-accounts-page #accountsGrid .account-card .seller-info{
    width:calc(100% + 36px) !important;
    margin-left:-18px !important;
    margin-right:-18px !important;
    margin-bottom:-18px !important;
    padding-left:18px !important;
    padding-right:18px !important;
  }
}



/* Integrated full width seller footer, no hard inserted box look. */
body.ranked-accounts-page #accountsGrid.accounts-grid .account-card .lb-seller-footer,
body.ranked-accounts-page #accountsGrid .account-card .lb-seller-footer,
.ranked-accounts-page #accountsGrid.accounts-grid .account-card .lb-seller-footer,
.ranked-accounts-page #accountsGrid .account-card .lb-seller-footer,
body.ranked-accounts-page #accountsGrid.accounts-grid .account-card .seller-info,
body.ranked-accounts-page #accountsGrid .account-card .seller-info,
.ranked-accounts-page #accountsGrid.accounts-grid .account-card .seller-info,
.ranked-accounts-page #accountsGrid .account-card .seller-info{
  width:calc(100% + 48px) !important;
  max-width:none !important;
  min-height:66px !important;
  margin:14px -24px -24px !important;
  padding:14px 24px 16px !important;
  box-sizing:border-box !important;
  border-radius:0 0 22px 22px !important;
  border-top:1px solid rgba(255,255,255,.045) !important;
  border-left:0 !important;
  border-right:0 !important;
  border-bottom:0 !important;
  background:linear-gradient(180deg,rgba(255,255,255,.012) 0%,rgba(255,255,255,.032) 48%,rgba(255,255,255,.045) 100%) !important;
  box-shadow:inset 0 1px 0 rgba(255,255,255,.012) !important;
  overflow:visible !important;
}

body.ranked-accounts-page #accountsGrid.accounts-grid .account-card .lb-seller-footer__avatar,
body.ranked-accounts-page #accountsGrid .account-card .lb-seller-footer__avatar,
.ranked-accounts-page #accountsGrid.accounts-grid .account-card .lb-seller-footer__avatar,
.ranked-accounts-page #accountsGrid .account-card .lb-seller-footer__avatar,
body.ranked-accounts-page #accountsGrid.accounts-grid .account-card .seller-info__avatar,
body.ranked-accounts-page #accountsGrid .account-card .seller-info__avatar,
.ranked-accounts-page #accountsGrid.accounts-grid .account-card .seller-info__avatar,
.ranked-accounts-page #accountsGrid .account-card .seller-info__avatar{
  width:36px !important;
  height:36px !important;
  min-width:36px !important;
  min-height:36px !important;
}

body.ranked-accounts-page #accountsGrid.accounts-grid .account-card .lb-seller-footer__stat,
body.ranked-accounts-page #accountsGrid .account-card .lb-seller-footer__stat,
.ranked-accounts-page #accountsGrid.accounts-grid .account-card .lb-seller-footer__stat,
.ranked-accounts-page #accountsGrid .account-card .lb-seller-footer__stat,
body.ranked-accounts-page #accountsGrid.accounts-grid .account-card .seller-info__sold,
body.ranked-accounts-page #accountsGrid .account-card .seller-info__sold,
.ranked-accounts-page #accountsGrid.accounts-grid .account-card .seller-info__sold,
.ranked-accounts-page #accountsGrid .account-card .seller-info__sold{
  background:rgba(255,255,255,.045) !important;
  border-color:rgba(255,255,255,.06) !important;
}

body.ranked-accounts-page #accountsGrid.accounts-grid .account-card,
body.ranked-accounts-page #accountsGrid .account-card,
.ranked-accounts-page #accountsGrid.accounts-grid .account-card,
.ranked-accounts-page #accountsGrid .account-card{
  overflow:hidden !important;
}

@media(max-width:767px){
  body.ranked-accounts-page #accountsGrid.accounts-grid .account-card .lb-seller-footer,
  body.ranked-accounts-page #accountsGrid .account-card .lb-seller-footer,
  .ranked-accounts-page #accountsGrid.accounts-grid .account-card .lb-seller-footer,
  .ranked-accounts-page #accountsGrid .account-card .lb-seller-footer,
  body.ranked-accounts-page #accountsGrid.accounts-grid .account-card .seller-info,
  body.ranked-accounts-page #accountsGrid .account-card .seller-info,
  .ranked-accounts-page #accountsGrid.accounts-grid .account-card .seller-info,
  .ranked-accounts-page #accountsGrid .account-card .seller-info{
    width:calc(100% + 36px) !important;
    margin-left:-18px !important;
    margin-right:-18px !important;
    margin-bottom:-18px !important;
    padding-left:18px !important;
    padding-right:18px !important;
  }
}


</style>';
    }
}

if (!function_exists('lb_render_seller_footer')) {
    function lb_render_seller_footer(array $seller, array $options = []): string
    {
        $variant = (string)($options['variant'] ?? 'card');
        $label = (string)($options['label'] ?? 'Sold by');
        $showLabel = (bool)($options['show_label'] ?? in_array($variant, ['profile', 'mobile-profile'], true));
        $showTrustedText = (bool)($options['trusted_text'] ?? false);

        $sellerId = (int)($seller['id'] ?? $seller['seller_id'] ?? 0);
        $username = trim((string)($seller['username'] ?? $seller['seller_username'] ?? $seller['name'] ?? 'Seller'));
        $slug = lb_seller_footer_slug_from_value($seller['slug'] ?? $seller['seller_slug'] ?? '', $username);
        $linkBase = defined('BASE_URL') ? rtrim((string)BASE_URL, '/') : '';
        $link = $linkBase . '/sellers/' . rawurlencode($slug);
        $icon = trim((string)($seller['icon'] ?? $seller['seller_icon'] ?? ''));
        if ($icon === '') $icon = defined('ICON_URL') ? rtrim((string)ICON_URL, '/') . '/default.png' : (defined('ASSET_URL') ? rtrim((string)ASSET_URL, '/') . '/public/uploads/icons/default.png' : '');
        $rank = (string)($seller['rank'] ?? $seller['seller_rank'] ?? '');
        $rankIcon = (string)($seller['rank_icon'] ?? $seller['seller_rank_icon'] ?? '');
        $isOnline = lb_seller_footer_is_online($seller);
        $sold = lb_seller_footer_total_sales($seller);
        [$rank, $rankIcon] = lb_seller_footer_effective_rank($rank, $rankIcon, $sold);
        $rankMeta = lb_seller_footer_rank_meta($rank, $rankIcon);
        $isVerified = !empty($seller['is_active']) || !empty($seller['seller_is_active']) || !empty($rankMeta['show']);
        $trustedTitle = (string)($options['trusted_title'] ?? 'Trusted');

        $classes = ['lb-seller-footer'];
        if ($variant === 'account-card') $classes[] = 'lb-seller-footer--account';
        if ($variant === 'item-card') $classes[] = 'lb-seller-footer--item';
        if ($variant === 'profile') $classes[] = 'lb-seller-footer--profile';
        if ($variant === 'mobile-profile') { $classes[] = 'lb-seller-footer--profile'; $classes[] = 'lb-seller-footer--mobile'; }
        if (!empty($options['class'])) $classes[] = (string)$options['class'];

        ob_start();
        echo lb_seller_footer_css();
        ?>
        <div class="<?= htmlspecialchars(implode(' ', $classes), ENT_QUOTES, 'UTF-8') ?>">
            <div class="lb-seller-footer__left">
                <?php if ($icon !== ''): ?>
                    <img class="lb-seller-footer__avatar" src="<?= htmlspecialchars($icon, ENT_QUOTES, 'UTF-8') ?>" alt="<?= htmlspecialchars($username, ENT_QUOTES, 'UTF-8') ?>">
                <?php else: ?>
                    <span class="lb-seller-footer__avatar-placeholder"><i class="fas fa-user"></i></span>
                <?php endif; ?>
                <div class="lb-seller-footer__meta">
                    <?php if ($showLabel): ?><div class="lb-seller-footer__label"><?= function_exists('t') ? t($label) : htmlspecialchars($label, ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
                    <a class="lb-seller-footer__name" href="<?= htmlspecialchars($link, ENT_QUOTES, 'UTF-8') ?>" data-seller-id="<?= $sellerId ?>" data-seller-name="<?= htmlspecialchars($username, ENT_QUOTES, 'UTF-8') ?>" data-seller-slug="<?= htmlspecialchars($slug, ENT_QUOTES, 'UTF-8') ?>">
                        <span class="lb-seller-footer__name-text"><?= htmlspecialchars($username, ENT_QUOTES, 'UTF-8') ?></span>
                        <?php if ($isOnline): ?><span class="lb-seller-footer__online" title="Online" aria-label="Online"></span><?php endif; ?>
                        <?php if ($isVerified): ?>
                            <i class="<?= htmlspecialchars($rankMeta['class'], ENT_QUOTES, 'UTF-8') ?> lb-seller-footer__verified" style="color:<?= htmlspecialchars($rankMeta['color'], ENT_QUOTES, 'UTF-8') ?>;" tabindex="0" aria-label="<?= htmlspecialchars($rankMeta['title'], ENT_QUOTES, 'UTF-8') ?>"></i>
                            <span class="lb-seller-footer__tooltip">
                                <i class="<?= htmlspecialchars($rankMeta['class'], ENT_QUOTES, 'UTF-8') ?>" style="color:<?= htmlspecialchars($rankMeta['color'], ENT_QUOTES, 'UTF-8') ?>;"></i>
                                <?= htmlspecialchars($rankMeta['title'], ENT_QUOTES, 'UTF-8') ?>
                            </span>
                        <?php endif; ?>
                    </a>
                </div>
            </div>
            <div class="lb-seller-footer__right">
                <span class="lb-seller-footer__stat lb-seller-footer__sold" title="<?= htmlspecialchars(number_format($sold) . ' Sold', ENT_QUOTES, 'UTF-8') ?>">
                    <?php if ($variant === 'profile' || $variant === 'mobile-profile'): ?>
                        <strong><?= number_format($sold) ?></strong><small><?= function_exists('t') ? t('Sold') : 'Sold' ?></small>
                    <?php else: ?>
                        <?= number_format($sold) ?> <?= function_exists('t') ? t('Sold') : 'Sold' ?>
                    <?php endif; ?>
                </span>
                <span class="lb-seller-footer__stat lb-seller-footer__trusted" title="<?= htmlspecialchars($trustedTitle, ENT_QUOTES, 'UTF-8') ?>" aria-label="<?= htmlspecialchars($trustedTitle, ENT_QUOTES, 'UTF-8') ?>">
                    <?php if ($variant === 'profile' || $variant === 'mobile-profile'): ?>
                        <strong><i class="fas fa-thumbs-up"></i></strong><?php if ($showTrustedText): ?><small><?= function_exists('t') ? t('Trusted') : 'Trusted' ?></small><?php endif; ?>
                    <?php else: ?>
                        <i class="fas fa-thumbs-up"></i>
                    <?php endif; ?>
                </span>
            </div>
        </div>
        <?php
        return trim(ob_get_clean());
    }
}

?>
<style id="lb-seller-footer-redesign-2026">
body.ranked-accounts-page .account-card .lb-seller-footer,
body.ranked-accounts-page .account-card .seller-info{
  color:rgba(240,243,255,.82)!important;
}
body.ranked-accounts-page .account-card .lb-seller-footer__left,
body.ranked-accounts-page .account-card .seller-info__left{
  display:flex!important;
  align-items:center!important;
  min-width:0!important;
  gap:10px!important;
}
body.ranked-accounts-page .account-card .lb-seller-footer__avatar,
body.ranked-accounts-page .account-card .seller-info__avatar{
  width:34px!important;
  height:34px!important;
  min-width:34px!important;
  border-radius:11px!important;
  object-fit:cover!important;
  border:1px solid rgba(255,255,255,.12)!important;
  box-shadow:0 7px 18px rgba(0,0,0,.24)!important;
}
body.ranked-accounts-page .account-card .lb-seller-footer__name,
body.ranked-accounts-page .account-card .seller-info__name{
  color:#f5f7ff!important;
  font-size:12px!important;
  font-weight:850!important;
  text-decoration:none!important;
}
body.ranked-accounts-page .account-card .lb-seller-footer__label{
  color:rgba(220,225,255,.45)!important;
  font-size:9px!important;
  font-weight:850!important;
  letter-spacing:.08em!important;
  text-transform:uppercase!important;
}
body.ranked-accounts-page .account-card .lb-seller-footer__right,
body.ranked-accounts-page .account-card .seller-info__right{
  margin-left:auto!important;
  display:flex!important;
  align-items:center!important;
  gap:7px!important;
}
body.ranked-accounts-page .account-card .lb-seller-footer__stat,
body.ranked-accounts-page .account-card .seller-info__sold,
body.ranked-accounts-page .account-card .seller-info__rating{
  display:inline-flex!important;
  align-items:center!important;
  gap:5px!important;
  min-height:28px!important;
  padding:0 8px!important;
  border-radius:9px!important;
  background:rgba(255,255,255,.04)!important;
  border:1px solid rgba(255,255,255,.065)!important;
  color:rgba(237,240,255,.72)!important;
  font-size:10px!important;
  font-weight:800!important;
}
body.ranked-accounts-page .account-card .lb-seller-footer__stat i,
body.ranked-accounts-page .account-card .seller-info__rating i{color:#fbbf24!important;}
</style>


<style id="lb-seller-footer-readable-type-v7">
/* Seller footer remains the only source for seller footer presentation. */
@media (min-width:768px){
  body.ranked-accounts-page .account-card .lb-seller-footer__name,
  body.ranked-accounts-page .account-card .seller-info__name{
    font-size:13.5px!important;line-height:1.2!important;font-weight:900!important;
  }
  body.ranked-accounts-page .account-card .lb-seller-footer__label{
    font-size:10px!important;
  }
  body.ranked-accounts-page .account-card .lb-seller-footer__stat,
  body.ranked-accounts-page .account-card .seller-info__sold,
  body.ranked-accounts-page .account-card .seller-info__rating{
    font-size:11.5px!important;min-height:30px!important;padding:0 9px!important;
  }
}
</style>
