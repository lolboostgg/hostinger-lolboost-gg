<?php
$breadcrumbType = strtolower(trim((string)($type ?? 'accounts'))) === 'items' ? 'items' : 'accounts';
$breadcrumbGameSlug = trim((string)($gameSlug ?? ''), '/');
$breadcrumbGameName = trim((string)($gameName ?? ''));
$breadcrumbCurrent = trim((string)($currentTitle ?? ''));

if ($breadcrumbGameSlug === '') {
    $breadcrumbGameSlug = 'league-of-legends';
}
if ($breadcrumbGameName === '') {
    $breadcrumbGameName = ucwords(str_replace('-', ' ', $breadcrumbGameSlug));
}

$breadcrumbSectionLabel = $breadcrumbType === 'items' ? 'Items' : 'Accounts';
$breadcrumbSectionHref = '/services/' . $breadcrumbType;
$breadcrumbGameHref = '/' . rawurlencode($breadcrumbGameSlug);
$breadcrumbListingHref = $breadcrumbGameHref . '/' . $breadcrumbType;
$breadcrumbListingLabel = $breadcrumbGameName . ' ' . $breadcrumbSectionLabel;
?>
<style>
.lb-market-breadcrumbs {
    display: flex;
    align-items: center;
    gap: 0;
    min-width: 0;
    margin: 0 0 18px;
    overflow-x: auto;
    scrollbar-width: none;
    white-space: nowrap;
    font-size: 13px;
    line-height: 1.4;
}
.lb-market-breadcrumbs::-webkit-scrollbar { display: none; }
.lb-market-breadcrumbs__item {
    display: inline-flex;
    align-items: center;
    min-width: 0;
    color: #9ba5bc;
    text-decoration: none;
    transition: color .18s ease;
}
a.lb-market-breadcrumbs__item:hover { color: #fff; }
.lb-market-breadcrumbs__separator {
    flex: 0 0 auto;
    padding: 0 11px;
    color: rgba(155, 165, 188, .38);
    font-size: 16px;
}
.lb-market-breadcrumbs__item--current {
    max-width: min(430px, 34vw);
    overflow: hidden;
    color: #fff;
    font-weight: 700;
    text-overflow: ellipsis;
}
@media (max-width: 767px) {
    .lb-market-breadcrumbs {
        margin-bottom: 14px;
        padding-bottom: 2px;
        font-size: 12px;
    }
    .lb-market-breadcrumbs__separator { padding: 0 8px; }
    .lb-market-breadcrumbs__item--current { max-width: 58vw; }
}
</style>
<nav class="lb-market-breadcrumbs" aria-label="Breadcrumb">
    <a class="lb-market-breadcrumbs__item" href="/">Home</a>
    <span class="lb-market-breadcrumbs__separator" aria-hidden="true">/</span>
    <a class="lb-market-breadcrumbs__item" href="<?= htmlspecialchars($breadcrumbSectionHref, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($breadcrumbSectionLabel, ENT_QUOTES, 'UTF-8') ?></a>
    <span class="lb-market-breadcrumbs__separator" aria-hidden="true">/</span>
    <a class="lb-market-breadcrumbs__item" href="<?= htmlspecialchars($breadcrumbGameHref, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($breadcrumbGameName, ENT_QUOTES, 'UTF-8') ?></a>
    <span class="lb-market-breadcrumbs__separator" aria-hidden="true">/</span>
    <a class="lb-market-breadcrumbs__item" href="<?= htmlspecialchars($breadcrumbListingHref, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($breadcrumbListingLabel, ENT_QUOTES, 'UTF-8') ?></a>
    <?php if ($breadcrumbCurrent !== ''): ?>
        <span class="lb-market-breadcrumbs__separator" aria-hidden="true">/</span>
        <span class="lb-market-breadcrumbs__item lb-market-breadcrumbs__item--current" aria-current="page" title="<?= htmlspecialchars($breadcrumbCurrent, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($breadcrumbCurrent, ENT_QUOTES, 'UTF-8') ?></span>
    <?php endif; ?>
</nav>
