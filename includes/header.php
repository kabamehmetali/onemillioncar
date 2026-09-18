<?php
/**
 * Shared <head>, navigation and page opener.
 * Pages set $pageTitle, $metaDescription, optional $ogImage / $canonical / $bodyClass before including this file.
 */
$pageTitle       = $pageTitle       ?? site_name();
$metaDescription = $metaDescription ?? setting('meta_description');
$ogImage         = $ogImage         ?? 'assets/img/og-default.jpg'; // site-relative path
$canonical       = $canonical       ?? absolute_url(current_path());
$bodyClass       = $bodyClass       ?? '';
$fullTitle       = ($pageTitle === site_name()) ? site_name() . ' — ' . setting('tagline', 'Pre-Owned Vehicles') : $pageTitle . ' | ' . site_name();
$nav = [
    ''            => 'Home',
    'inventory'   => 'Inventory',
    'services'    => 'Services',
    'financing'   => 'Financing',
    'trade-in'    => 'Trade-In',
    'about'       => 'About',
    'contact'     => 'Contact',
];

/* Inventory flyout / drawer sub-menu model. Built from live facets, so it can
   never advertise an empty bucket; with no stock the disclosure is not rendered
   at all and Inventory stays a plain link. */
$navFacets = inventory_facets();
$navStock  = (int) ($navFacets['count'] ?? 0);

$navBodies = [];
foreach (($navFacets['body_types'] ?? []) as $navKey => $navCount) {
    if ((string) $navKey !== '' && (int) $navCount > 0) {
        $navBodies[(string) $navKey] = (int) $navCount;
    }
}
$navBodyIcons = [
    'SUV'         => 'fa-car-rear',
    'Sedan'       => 'fa-car-side',
    'Hatchback'   => 'fa-car-side',
    'Coupe'       => 'fa-car-side',
    'Convertible' => 'fa-car-side',
    'Wagon'       => 'fa-car-side',
    'Truck'       => 'fa-truck-pickup',
    'Van'         => 'fa-van-shuttle',
    'Minivan'     => 'fa-van-shuttle',
];

$navPriceBands = inventory_price_bands();
$navHasFlyout = $navStock > 0 && ($navBodies !== [] || $navPriceBands !== []);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($fullTitle) ?></title>
    <meta name="description" content="<?= e($metaDescription) ?>">
    <link rel="canonical" href="<?= e($canonical) ?>">
    <meta property="og:type" content="website">
    <meta property="og:site_name" content="<?= e(site_name()) ?>">
    <meta property="og:title" content="<?= e($fullTitle) ?>">
    <meta property="og:description" content="<?= e($metaDescription) ?>">
    <meta property="og:image" content="<?= e(preg_match('~^https?://~', $ogImage) ? $ogImage : absolute_url($ogImage)) ?>">
    <meta property="og:url" content="<?= e($canonical) ?>">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="theme-color" content="#0a0a0c">
    <link rel="icon" type="image/png" href="<?= asset('assets/img/favicon.png') ?>">
    <link rel="apple-touch-icon" href="<?= asset('assets/img/apple-touch-icon.png') ?>">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@500;600;700;800&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet">
    <link href="<?= asset('assets/css/styles.css') ?>" rel="stylesheet">
    <script type="application/ld+json">
    <?= json_encode([
        '@context'    => 'https://schema.org',
        '@type'       => 'AutoDealer',
        'name'        => site_name(),
        'url'         => absolute_url(),
        'logo'        => absolute_url('assets/img/logo.png'),
        'image'       => absolute_url('assets/img/og-default.jpg'),
        'telephone'   => setting('phone'),
        'email'       => setting('email'),
        'address'     => ['@type' => 'PostalAddress', 'streetAddress' => setting('address_line'), 'addressLocality' => setting('city'), 'addressCountry' => 'CA'],
        'areaServed'  => setting('service_area'),
        'openingHours'=> ['Mo-Fr ' . setting('hours_weekdays'), 'Sa ' . setting('hours_saturday')],
        'founder'     => ['@type' => 'Person', 'name' => agent_name(), 'jobTitle' => setting('agent_title')],
    ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>
    </script>
</head>
<body class="<?= e($bodyClass) ?>">
<a class="skip-link" href="#main">Skip to content</a>

<?php /* Two-rail fixed chrome: a contact strip that retracts on scroll and a nav
         row that condenses. .header-spacer holds the flow space it vacated. */ ?>
<header class="site-header" id="siteHeader">

    <div class="topbar-row">
        <div class="container topbar-inner">
            <ul class="topbar-list">
                <li><i class="fa-solid fa-phone" aria-hidden="true"></i><a href="<?= e(phone_href(setting('phone'))) ?>"><?= e(setting('phone')) ?></a></li>
                <li><i class="fa-solid fa-envelope" aria-hidden="true"></i><a href="mailto:<?= e(setting('email')) ?>"><?= e(setting('email')) ?></a></li>
                <li class="topbar-hours"><i class="fa-regular fa-clock" aria-hidden="true"></i>Mon&nbsp;–&nbsp;Fri <?= e(setting('hours_weekdays')) ?></li>
            </ul>
            <ul class="topbar-list topbar-social">
                <li class="topbar-area"><i class="fa-solid fa-location-dot" aria-hidden="true"></i><?= e(setting('service_area')) ?></li>
                <?php foreach (social_links() as $s): ?>
                    <li class="topbar-icon"><a href="<?= e($s['url']) ?>" target="_blank" rel="noopener" aria-label="<?= e($s['label']) ?>"><i class="<?= e($s['icon']) ?>" aria-hidden="true"></i></a></li>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>

    <div class="nav-row">
        <div class="container nav-inner">
            <a class="navbar-brand" href="<?= url() ?>" aria-label="<?= e(site_name()) ?> — home">
                <img src="<?= asset('assets/img/logo-mark.png') ?>" alt="" class="brand-mark" width="56" height="42">
                <span class="brand-text"><span class="brand-lucid">Lucid</span><span class="brand-haus">Auto Haus</span></span>
            </a>

            <nav class="nav-rail-wrap" aria-label="Primary">
                <ul class="nav-rail" id="navRail">
                    <?php foreach ($nav as $route => $label): ?>
                        <?php $navFly = ($route === 'inventory' && $navHasFlyout); ?>
                        <li class="nav-cell<?= $navFly ? ' has-flyout' : '' ?>">
                            <a class="nav-rail-link<?= is_active($route) ? ' active' : '' ?>" href="<?= url($route) ?>"<?= is_active($route) ? ' aria-current="page"' : '' ?>><span class="nav-label"><?= e($label) ?></span></a>
                            <?php if ($navFly): ?>
                                <button class="nav-disclosure" type="button" id="navInventoryTrigger" aria-expanded="false" aria-controls="navInventoryPanel" aria-label="Browse inventory by category">
                                    <i class="fa-solid fa-chevron-down" aria-hidden="true"></i>
                                </button>
                                <div class="nav-flyout" id="navInventoryPanel" inert>
                                    <div class="container nav-flyout-track">
                                        <div class="flyout-card">
                                            <div class="flyout-grid">
                                                <?php if ($navBodies): ?>
                                                    <div class="flyout-col" style="--c: 0">
                                                        <p class="flyout-title">Body style</p>
                                                        <ul class="flyout-list">
                                                            <?php foreach ($navBodies as $navBody => $navBodyCount): ?>
                                                                <li><a class="flyout-link" href="<?= e(url('inventory?' . http_build_query(['body_type' => $navBody]))) ?>">
                                                                    <span class="flyout-link-main"><i class="fa-solid <?= e($navBodyIcons[$navBody] ?? 'fa-car') ?>" aria-hidden="true"></i><?= e($navBody) ?></span>
                                                                    <span class="flyout-count"><?= e(number($navBodyCount)) ?></span>
                                                                </a></li>
                                                            <?php endforeach; ?>
                                                        </ul>
                                                    </div>
                                                <?php endif; ?>
                                                <?php if ($navPriceBands): ?>
                                                    <div class="flyout-col" style="--c: 1">
                                                        <p class="flyout-title">Budget</p>
                                                        <ul class="flyout-list">
                                                            <?php foreach ($navPriceBands as $navBand): ?>
                                                                <li><a class="flyout-link" href="<?= e(url('inventory?' . http_build_query($navBand['q']))) ?>">
                                                                    <span class="flyout-link-main"><?= e($navBand['label']) ?></span>
                                                                    <span class="flyout-count"><?= e(number($navBand['count'])) ?></span>
                                                                </a></li>
                                                            <?php endforeach; ?>
                                                        </ul>
                                                        <p class="flyout-note">CAD, before HST &amp; licensing.</p>
                                                    </div>
                                                <?php endif; ?>
                                                <div class="flyout-col" style="--c: 2">
                                                    <p class="flyout-title">Browse</p>
                                                    <ul class="flyout-list">
                                                        <li><a class="flyout-link" href="<?= url('inventory') ?>"><span class="flyout-link-main">All available</span><span class="flyout-count"><?= e(number($navStock)) ?></span></a></li>
                                                        <li><a class="flyout-link" href="<?= url('inventory?sort=newest') ?>"><span class="flyout-link-main">New arrivals</span></a></li>
                                                        <li><a class="flyout-link" href="<?= url('inventory?sort=price_asc') ?>"><span class="flyout-link-main">Lowest price first</span></a></li>
                                                        <li><a class="flyout-link" href="<?= url('inventory?status=sold') ?>"><span class="flyout-link-main">Recently sold</span></a></li>
                                                    </ul>
                                                </div>
                                            </div>
                                            <div class="flyout-foot">
                                                <span class="flyout-stock"><strong><?= e(number($navStock)) ?></strong> vehicles in stock &middot; inspected, market-priced, no admin fees</span>
                                                <a class="flyout-all" href="<?= url('inventory') ?>">View all inventory <i class="fa-solid fa-arrow-right-long" aria-hidden="true"></i></a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </li>
                    <?php endforeach; ?>
                    <li class="nav-datum" aria-hidden="true"></li>
                </ul>
            </nav>

            <div class="nav-actions">
                <a class="nav-phone" href="<?= e(phone_href(setting('phone'))) ?>">
                    <i class="fa-solid fa-phone-volume" aria-hidden="true"></i>
                    <span class="nav-phone-text">
                        <span class="nav-phone-kicker">Direct line</span>
                        <span class="nav-phone-number"><?= e(setting('phone')) ?></span>
                    </span>
                </a>
                <a class="nav-call" href="<?= e(phone_href(setting('phone'))) ?>" aria-label="Call <?= e(setting('phone')) ?>">
                    <i class="fa-solid fa-phone" aria-hidden="true"></i>
                </a>
                <a href="<?= url('inventory') ?>" class="btn btn-lah btn-sm nav-cta">
                    <i class="fa-solid fa-car" aria-hidden="true"></i><span><span class="nav-cta-verb">View </span>Inventory</span>
                </a>
                <button class="nav-toggle" type="button" id="navToggle" aria-expanded="false" aria-controls="navDrawer" aria-label="Open menu">
                    <span class="toggler-bars" aria-hidden="true"><span></span><span></span><span></span></span>
                </button>
            </div>
        </div>
    </div>


    <div class="header-filament" aria-hidden="true"><span></span></div>
</header>

<?php /* keeps the space the fixed chrome used to occupy in flow */ ?>
<div class="header-spacer" aria-hidden="true"></div>

<div class="nav-scrim" id="navScrim"></div>

<div class="nav-drawer" id="navDrawer" role="dialog" aria-modal="true" aria-label="Site menu" tabindex="-1" inert>
    <div class="drawer-head">
        <span class="drawer-eyebrow"><?= e(site_name()) ?></span>
        <button class="drawer-close" type="button" id="navDrawerClose" aria-label="Close menu"><i class="fa-solid fa-xmark" aria-hidden="true"></i></button>
    </div>

    <div class="drawer-scroll">
        <nav class="drawer-nav" aria-label="Primary (mobile)">
            <ul class="drawer-list">
                <?php $navI = 0; foreach ($nav as $route => $label): $navI++; ?>
                    <li class="drawer-item" style="--i: <?= (int) $navI ?>">
                        <div class="drawer-row">
                            <a class="drawer-link<?= is_active($route) ? ' active' : '' ?>" href="<?= url($route) ?>"<?= is_active($route) ? ' aria-current="page"' : '' ?>>
                                <span class="drawer-index"><?= e(str_pad((string) $navI, 2, '0', STR_PAD_LEFT)) ?></span>
                                <span class="drawer-label"><?= e($label) ?></span>
                            </a>
                            <?php if ($route === 'inventory' && $navHasFlyout): ?>
                                <button class="drawer-expand" type="button" data-drawer-expand aria-expanded="false" aria-controls="drawerInventory" aria-label="Show inventory categories">
                                    <i class="fa-solid fa-plus" aria-hidden="true"></i>
                                </button>
                            <?php endif; ?>
                        </div>
                        <?php if ($route === 'inventory' && $navHasFlyout): ?>
                            <div class="drawer-sub" id="drawerInventory">
                                <ul class="drawer-sub-list">
                                    <?php foreach ($navBodies as $navBody => $navBodyCount): ?>
                                        <li><a href="<?= e(url('inventory?' . http_build_query(['body_type' => $navBody]))) ?>">
                                            <span><i class="fa-solid <?= e($navBodyIcons[$navBody] ?? 'fa-car') ?>" aria-hidden="true"></i><?= e($navBody) ?></span>
                                            <span class="flyout-count"><?= e(number($navBodyCount)) ?></span>
                                        </a></li>
                                    <?php endforeach; ?>
                                    <?php foreach ($navPriceBands as $navBand): ?>
                                        <li><a href="<?= e(url('inventory?' . http_build_query($navBand['q']))) ?>"><span><i class="fa-solid fa-tag" aria-hidden="true"></i><?= e($navBand['label']) ?></span><span class="flyout-count"><?= e(number($navBand['count'])) ?></span></a></li>
                                    <?php endforeach; ?>
                                    <li><a href="<?= url('inventory?sort=newest') ?>"><span><i class="fa-solid fa-bolt" aria-hidden="true"></i>New arrivals</span></a></li>
                                    <li><a href="<?= url('inventory?status=sold') ?>"><span><i class="fa-solid fa-circle-check" aria-hidden="true"></i>Recently sold</span></a></li>
                                </ul>
                            </div>
                        <?php endif; ?>
                    </li>
                <?php endforeach; ?>
            </ul>
        </nav>

        <div class="drawer-meta">
            <a class="drawer-contact" href="<?= e(phone_href(setting('phone'))) ?>">
                <i class="fa-solid fa-phone" aria-hidden="true"></i>
                <span><span class="drawer-contact-kicker">Call direct</span><span class="drawer-contact-value"><?= e(setting('phone')) ?></span></span>
            </a>
            <a class="drawer-contact" href="mailto:<?= e(setting('email')) ?>">
                <i class="fa-solid fa-envelope" aria-hidden="true"></i>
                <span><span class="drawer-contact-kicker">Email</span><span class="drawer-contact-value"><?= e(setting('email')) ?></span></span>
            </a>
            <p class="drawer-fineprint"><i class="fa-regular fa-clock" aria-hidden="true"></i><span>Mon&nbsp;–&nbsp;Fri <?= e(setting('hours_weekdays')) ?><br>Sat <?= e(setting('hours_saturday')) ?></span></p>
            <p class="drawer-fineprint"><i class="fa-solid fa-location-dot" aria-hidden="true"></i><span><?= e(setting('service_area')) ?></span></p>
        </div>
    </div>

    <div class="drawer-foot">
        <a href="<?= url('inventory') ?>" class="btn btn-lah w-100"><i class="fa-solid fa-car me-2" aria-hidden="true"></i>View Inventory</a>
        <?php if (social_links()): ?>
            <ul class="drawer-social">
                <?php foreach (social_links() as $s): ?>
                    <li><a href="<?= e($s['url']) ?>" target="_blank" rel="noopener" aria-label="<?= e($s['label']) ?>"><i class="<?= e($s['icon']) ?>" aria-hidden="true"></i></a></li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </div>
</div>

<main id="main">
