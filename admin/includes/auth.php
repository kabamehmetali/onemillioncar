<?php
/**
 * Admin bootstrap: loads the app, then exposes login helpers.
 * Every admin page starts with:  require __DIR__ . '/includes/auth.php'; require_login();
 */
declare(strict_types=1);

require dirname(__DIR__, 2) . '/includes/app.php';

function admin_url(string $path = ''): string
{
    return url('admin/' . ltrim($path, '/'));
}

function admin_user(): ?array
{
    static $user = false;
    if ($user === false) {
        $id = (int) ($_SESSION['admin_id'] ?? 0);
        $user = $id > 0 ? db_one('SELECT id, username, display_name, last_login_at FROM users WHERE id = ?', [$id]) : null;
    }
    return $user;
}

function require_login(): void
{
    if (!admin_user()) {
        $_SESSION['admin_after_login'] = $_SERVER['REQUEST_URI'] ?? '';
        header('Location: ' . admin_url('login.php'), true, 303);
        exit;
    }
}

/** Throttle: a growing pause after repeated failures, then a 15-minute lock. */
function login_throttle(): ?string
{
    $fails = (int) ($_SESSION['login_fails'] ?? 0);
    $until = (int) ($_SESSION['login_locked_until'] ?? 0);
    if ($until > time()) {
        return 'Too many failed attempts. Try again in ' . (int) ceil(($until - time()) / 60) . ' minute(s).';
    }
    if ($fails >= 3) {
        sleep(min(5, $fails - 2));
    }
    return null;
}

function login_attempt(string $username, string $password): bool
{
    $user = db_one('SELECT * FROM users WHERE username = ? LIMIT 1', [$username]);
    if ($user && password_verify($password, $user['password_hash'])) {
        session_regenerate_id(true);
        $_SESSION['admin_id'] = (int) $user['id'];
        unset($_SESSION['login_fails'], $_SESSION['login_locked_until']);
        db_query('UPDATE users SET last_login_at = NOW() WHERE id = ?', [(int) $user['id']]);
        if (password_needs_rehash($user['password_hash'], PASSWORD_DEFAULT)) {
            db_query('UPDATE users SET password_hash = ? WHERE id = ?', [password_hash($password, PASSWORD_DEFAULT), (int) $user['id']]);
        }
        return true;
    }
    $_SESSION['login_fails'] = ($_SESSION['login_fails'] ?? 0) + 1;
    if ($_SESSION['login_fails'] >= 10) {
        $_SESSION['login_locked_until'] = time() + 900;
        $_SESSION['login_fails'] = 0;
    }
    return false;
}

/** Abort the request unless the POST carries a valid CSRF token. */
function require_csrf(): void
{
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && !csrf_valid()) {
        http_response_code(403);
        exit('Invalid or expired form token. Go back, reload the page and try again.');
    }
}

function admin_nav(): array
{
    $newLeads = (int) db_value('SELECT COUNT(*) FROM leads WHERE status = "new"');
    return [
        ['index.php',        'fa-gauge-high',      'Dashboard',    0],
        ['vehicles.php',     'fa-car',             'Vehicles',     0],
        ['leads.php',        'fa-inbox',           'Leads',        $newLeads],
        ['testimonials.php', 'fa-star',            'Testimonials', 0],
        ['faqs.php',         'fa-circle-question', 'FAQs',         0],
        ['settings.php',     'fa-sliders',         'Settings',     0],
        ['account.php',      'fa-user-gear',       'Account',      0],
    ];
}

const LEAD_TYPES = ['contact' => 'Message', 'inquiry' => 'Vehicle inquiry', 'test_drive' => 'Test drive', 'trade_in' => 'Trade-in', 'financing' => 'Financing'];
const LEAD_STATUSES = ['new' => 'New', 'contacted' => 'Contacted', 'closed' => 'Closed'];

function lead_badge(string $status): string
{
    $map = ['new' => 'danger', 'contacted' => 'warning text-dark', 'closed' => 'secondary'];
    return '<span class="badge bg-' . ($map[$status] ?? 'secondary') . '">' . e(LEAD_STATUSES[$status] ?? $status) . '</span>';
}
