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
    ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) ?>
    </script>
</head>
<body class="<?= e($bodyClass) ?>">
<a class="skip-link" href="#main">Skip to content</a>

<div class="topbar d-none d-lg-block">
    <div class="container d-flex justify-content-between align-items-center">
        <ul class="topbar-list">
            <li><i class="fa-solid fa-phone"></i><a href="<?= e(phone_href(setting('phone'))) ?>"><?= e(setting('phone')) ?></a></li>
            <li><i class="fa-solid fa-envelope"></i><a href="mailto:<?= e(setting('email')) ?>"><?= e(setting('email')) ?></a></li>
            <li><i class="fa-regular fa-clock"></i>Mon – Fri <?= e(setting('hours_weekdays')) ?></li>
        </ul>
        <ul class="topbar-list topbar-social">
            <li class="topbar-area"><i class="fa-solid fa-location-dot"></i><?= e(setting('service_area')) ?></li>
            <?php foreach (social_links() as $s): ?>
                <li><a href="<?= e($s['url']) ?>" target="_blank" rel="noopener" aria-label="<?= e($s['label']) ?>"><i class="<?= e($s['icon']) ?>"></i></a></li>
            <?php endforeach; ?>
        </ul>
    </div>
</div>

<header class="site-header" id="siteHeader">
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container">
            <a class="navbar-brand" href="<?= url() ?>" aria-label="<?= e(site_name()) ?> home">
                <img src="<?= asset('assets/img/logo-mark.png') ?>" alt="" class="brand-mark" width="56" height="42">
                <span class="brand-text"><span class="brand-lucid">Lucid</span><span class="brand-haus">Auto Haus</span></span>
            </a>
            <div class="d-flex align-items-center gap-2 order-lg-3">
                <a href="<?= url('inventory') ?>" class="btn btn-lah btn-sm d-none d-sm-inline-flex"><i class="fa-solid fa-car me-2"></i>View Inventory</a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav" aria-controls="mainNav" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="toggler-bars"><span></span><span></span><span></span></span>
                </button>
            </div>
            <div class="collapse navbar-collapse order-lg-2" id="mainNav">
                <ul class="navbar-nav mx-auto">
                    <?php foreach ($nav as $route => $label): ?>
                        <li class="nav-item"><a class="nav-link<?= is_active($route) ? ' active' : '' ?>" href="<?= url($route) ?>"<?= is_active($route) ? ' aria-current="page"' : '' ?>><?= e($label) ?></a></li>
                    <?php endforeach; ?>
                </ul>
                <div class="d-lg-none mobile-nav-extra">
                    <a href="<?= e(phone_href(setting('phone'))) ?>" class="btn btn-outline-light w-100 mb-2"><i class="fa-solid fa-phone me-2"></i><?= e(setting('phone')) ?></a>
                    <a href="<?= url('inventory') ?>" class="btn btn-lah w-100">View Inventory</a>
                </div>
            </div>
        </div>
    </nav>
</header>

<main id="main">
