<?php
require __DIR__ . '/includes/auth.php';
require_login();

$me     = admin_user();
$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_csrf();
    $full = db_one('SELECT * FROM users WHERE id = ?', [(int) $me['id']]);
    $display  = post_str('display_name', 100);
    $username = post_str('username', 50);
    $current  = (string) ($_POST['current_password'] ?? '');
    $new      = (string) ($_POST['new_password'] ?? '');
    $confirm  = (string) ($_POST['confirm_password'] ?? '');
    if (!preg_match('/^[A-Za-z0-9_.-]{3,50}$/', $username)) {
        $errors['username'] = 'Username: 3–50 letters, numbers, dots, dashes or underscores.';
    } elseif (db_value('SELECT id FROM users WHERE username = ? AND id <> ?', [$username, (int) $me['id']])) {
        $errors['username'] = 'That username is taken.';
    }
    $update = ['display_name' => $display, 'username' => $username];
    if ($new !== '' || $confirm !== '') {
        if (!password_verify($current, $full['password_hash'])) $errors['current_password'] = 'Current password is incorrect.';
        if (strlen($new) < 10)   $errors['new_password'] = 'Use at least 10 characters.';
        if ($new !== $confirm)   $errors['confirm_password'] = 'Passwords do not match.';
        if (!$errors) {
            $update['password_hash'] = password_hash($new, PASSWORD_DEFAULT);
        }
    }
    if (!$errors) {
        db_update('users', $update, 'id = ?', [(int) $me['id']]);
        flash_set('success', 'Account updated.' . (isset($update['password_hash']) ? ' Your password has been changed.' : ''));
        redirect('admin/account.php');
    }
}
$adminTitle = 'Account';
require __DIR__ . '/includes/header.php';
?>
<form method="post" class="card" style="max-width:620px" autocomplete="off">
    <?= csrf_field() ?>
    <div class="card-body">
        <?php if ($errors): ?><div class="alert alert-danger">Please fix the highlighted fields.</div><?php endif; ?>
        <div class="mb-3"><label class="form-label">Display name</label><input type="text" name="display_name" class="form-control" value="<?= e(old('display_name', $me['display_name'])) ?>"></div>
        <div class="mb-4"><label class="form-label">Username</label><input type="text" name="username" class="form-control<?= isset($errors['username']) ? ' is-invalid' : '' ?>" value="<?= e(old('username', $me['username'])) ?>" autocomplete="username"><div class="invalid-feedback"><?= e($errors['username'] ?? '') ?></div></div>
        <h2 class="h6 fw-bold">Change password</h2>
        <p class="text-muted-sm">Leave blank to keep your current password.<?= $me['username'] === 'admin' ? ' <strong class="text-danger">The seeded default password should be changed before this site goes live.</strong>' : '' ?></p>
        <div class="mb-3"><label class="form-label">Current password</label><input type="password" name="current_password" class="form-control<?= isset($errors['current_password']) ? ' is-invalid' : '' ?>" autocomplete="current-password"><div class="invalid-feedback"><?= e($errors['current_password'] ?? '') ?></div></div>
        <div class="row g-3">
            <div class="col-md-6"><label class="form-label">New password</label><input type="password" name="new_password" class="form-control<?= isset($errors['new_password']) ? ' is-invalid' : '' ?>" autocomplete="new-password"><div class="invalid-feedback"><?= e($errors['new_password'] ?? '') ?></div></div>
            <div class="col-md-6"><label class="form-label">Confirm new password</label><input type="password" name="confirm_password" class="form-control<?= isset($errors['confirm_password']) ? ' is-invalid' : '' ?>" autocomplete="new-password"><div class="invalid-feedback"><?= e($errors['confirm_password'] ?? '') ?></div></div>
        </div>
        <div class="text-muted-sm mt-3">Last login: <?= $me['last_login_at'] ? e(date('M j, Y g:i a', strtotime($me['last_login_at']))) : '—' ?></div>
    </div>
    <div class="card-footer bg-white"><button class="btn btn-primary"><i class="fa-solid fa-floppy-disk me-1"></i>Save account</button></div>
</form>
<?php require __DIR__ . '/includes/footer.php'; ?>
