<?php
/** Admin layout opener. Set $adminTitle before including. */
$adminTitle = $adminTitle ?? 'Admin';
$current    = basename($_SERVER['SCRIPT_NAME'] ?? '');
$me         = admin_user();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title><?= e($adminTitle) ?> · <?= e(site_name()) ?> Admin</title>
    <link rel="icon" type="image/png" href="<?= asset('assets/img/favicon.png') ?>">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet">
    <link href="<?= asset('admin/assets/admin.css') ?>" rel="stylesheet">
</head>
<body class="admin-body">
<div class="admin-shell">
    <aside class="admin-sidebar" id="adminSidebar">
        <a href="<?= admin_url() ?>" class="admin-brand">
            <img src="<?= asset('assets/img/logo-mark.png') ?>" alt="" width="44">
            <span><?= e(site_name()) ?><small>Admin panel</small></span>
        </a>
        <nav class="admin-nav">
            <?php foreach (admin_nav() as [$file, $icon, $label, $count]): ?>
                <a href="<?= admin_url($file) ?>" class="<?= $current === $file || ($file === 'vehicles.php' && $current === 'vehicle-form.php') || ($file === 'leads.php' && $current === 'lead-view.php') || ($file === 'testimonials.php' && $current === 'testimonial-form.php') || ($file === 'faqs.php' && $current === 'faq-form.php') ? 'active' : '' ?>">
                    <i class="fa-solid <?= $icon ?>"></i><?= e($label) ?>
                    <?php if ($count > 0): ?><span class="badge rounded-pill bg-danger ms-auto"><?= $count ?></span><?php endif; ?>
                </a>
            <?php endforeach; ?>
        </nav>
        <div class="admin-sidebar-footer">
            <a href="<?= url() ?>" target="_blank" rel="noopener"><i class="fa-solid fa-arrow-up-right-from-square"></i>View site</a>
            <a href="<?= admin_url('logout.php') ?>"><i class="fa-solid fa-right-from-bracket"></i>Log out</a>
        </div>
    </aside>
    <div class="admin-main">
        <header class="admin-topbar">
            <button class="btn btn-light d-lg-none" type="button" id="sidebarToggle" aria-label="Menu"><i class="fa-solid fa-bars"></i></button>
            <h1 class="admin-page-title"><?= e($adminTitle) ?></h1>
            <div class="admin-user"><i class="fa-solid fa-circle-user"></i><?= e($me['display_name'] ?: $me['username']) ?></div>
        </header>
        <main class="admin-content">
            <?= flash_render() ?>
