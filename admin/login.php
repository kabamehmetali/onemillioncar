<?php
require __DIR__ . '/includes/auth.php';

if (admin_user()) {
    header('Location: ' . admin_url(), true, 303);
    exit;
}
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_valid()) {
        $error = 'Your session expired. Please try again.';
    } elseif (($throttle = login_throttle()) !== null) {
        $error = $throttle;
    } else {
        $username = post_str('username', 50);
        $password = (string) ($_POST['password'] ?? '');
        if ($username !== '' && $password !== '' && login_attempt($username, $password)) {
            $next = (string) ($_SESSION['admin_after_login'] ?? '');
            unset($_SESSION['admin_after_login']);
            $safeNext = ($next !== '' && str_starts_with($next, app_base() . 'admin/') && !str_contains($next, 'login')) ? $next : admin_url();
            header('Location: ' . $safeNext, true, 303);
            exit;
        }
        $error = 'Incorrect username or password.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>Sign in · <?= e(site_name()) ?> Admin</title>
    <link rel="icon" type="image/png" href="<?= asset('assets/img/favicon.png') ?>">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet">
    <link href="<?= asset('admin/assets/admin.css') ?>" rel="stylesheet">
</head>
<body class="admin-body">
<div class="login-page">
    <form method="post" class="login-card" action="<?= admin_url('login.php') ?>">
        <?= csrf_field() ?>
        <img src="<?= asset('assets/img/logo-mark.png') ?>" alt="<?= e(site_name()) ?>">
        <h1 class="h5 text-center mb-1"><?= e(site_name()) ?></h1>
        <p class="text-center text-muted-sm mb-4">Sign in to the admin panel</p>
        <?php if ($error): ?><div class="alert alert-danger py-2 small"><?= e($error) ?></div><?php endif; ?>
        <div class="mb-3">
            <label class="form-label" for="username">Username</label>
            <input type="text" class="form-control" id="username" name="username" value="<?= e(old('username')) ?>" autocomplete="username" autofocus required>
        </div>
        <div class="mb-4">
            <label class="form-label" for="password">Password</label>
            <input type="password" class="form-control" id="password" name="password" autocomplete="current-password" required>
        </div>
        <button type="submit" class="btn btn-primary w-100"><i class="fa-solid fa-right-to-bracket me-2"></i>Sign in</button>
        <p class="text-center text-muted-sm mt-4 mb-0"><a href="<?= url() ?>" class="text-secondary">← Back to website</a></p>
    </form>
</div>
</body>
</html>
