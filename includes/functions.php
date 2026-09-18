<?php
declare(strict_types=1);

/* ------------------------------------------------------------------ output */

function e(mixed $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/** Plain text with line breaks preserved, safely escaped. */
function nl2p(?string $text, string $class = ''): string
{
    $out = '';
    foreach (preg_split('/\R{2,}/', trim((string) $text)) ?: [] as $para) {
        if (trim($para) === '') {
            continue;
        }
        $out .= '<p' . ($class ? ' class="' . e($class) . '"' : '') . '>' . nl2br(e(trim($para))) . '</p>';
    }
    return $out;
}

/** @return string[] non-empty trimmed lines */
function lines(?string $text): array
{
    return array_values(array_filter(array_map('trim', preg_split('/\R/', (string) $text) ?: []), fn($l) => $l !== ''));
}

function money(float|int|string|null $amount, bool $cents = false): string
{
    $amount = (float) $amount;
    return '$' . number_format($amount, $cents ? 2 : 0);
}

function number(int|float|string|null $n): string
{
    return number_format((float) $n);
}

function excerpt(?string $text, int $length = 150): string
{
    $text = trim(preg_replace('/\s+/', ' ', strip_tags((string) $text)) ?? '');
    if (mb_strlen($text) <= $length) {
        return $text;
    }
    return rtrim(mb_substr($text, 0, $length)) . '…';
}

function slugify(string $text): string
{
    $text = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $text) ?: $text;
    $text = strtolower(trim(preg_replace('/[^A-Za-z0-9]+/', '-', $text) ?? '', '-'));
    return $text === '' ? 'item' : $text;
}

function stars(int|float|string $rating): string
{
    $rating = max(0, min(5, (int) round((float) $rating)));
    $html = '<span class="stars" aria-label="' . $rating . ' out of 5 stars">';
    for ($i = 1; $i <= 5; $i++) {
        $html .= '<i class="fa-solid fa-star' . ($i <= $rating ? '' : ' is-empty') . '"></i>';
    }
    return $html . '</span>';
}

function initials(string $name): string
{
    $parts = preg_split('/\s+/', trim($name)) ?: [];
    $out = '';
    foreach (array_slice($parts, 0, 2) as $p) {
        $out .= mb_strtoupper(mb_substr($p, 0, 1));
    }
    return $out ?: 'LA';
}

function time_ago(string $datetime): string
{
    $ts   = strtotime($datetime) ?: time();
    $diff = time() - $ts;
    if ($diff < 60) return 'just now';
    if ($diff < 3600) return floor($diff / 60) . ' min ago';
    if ($diff < 86400) return floor($diff / 3600) . ' h ago';
    if ($diff < 86400 * 7) return floor($diff / 86400) . ' d ago';
    return date('M j, Y', $ts);
}

/* -------------------------------------------------------------------- urls */

/**
 * URL prefix the site is served under ("/" or "/onemillioncar/"), worked out
 * by comparing the script's URL path with its filesystem path so the same
 * code runs at a domain root or inside a subfolder.
 */
function app_base(): string
{
    static $base = null;
    if ($base !== null) {
        return $base;
    }
    $root       = str_replace('\\', '/', realpath(APP_ROOT) ?: APP_ROOT);
    $scriptName = str_replace('\\', '/', (string) ($_SERVER['SCRIPT_NAME'] ?? ''));
    $scriptFile = str_replace('\\', '/', realpath((string) ($_SERVER['SCRIPT_FILENAME'] ?? '')) ?: '');
    if ($scriptName !== '' && $scriptFile !== '' && str_starts_with($scriptFile, $root)) {
        $relative = ltrim(substr($scriptFile, strlen($root)), '/');
        if ($relative !== '' && str_ends_with($scriptName, $relative)) {
            return $base = rtrim(substr($scriptName, 0, -strlen($relative)), '/') . '/';
        }
    }
    return $base = (defined('BASE_URL') ? BASE_URL : '/');
}

/** Absolute-path URL for a route or file inside the site. */
function url(string $path = ''): string
{
    if (preg_match('~^(?:https?:)?//|^(?:mailto:|tel:|#)~i', $path)) {
        return $path;
    }
    $path = ltrim($path, '/');
    if (!(defined('CLEAN_URLS') && CLEAN_URLS) && $path !== '') {
        // Map clean routes back to the real endpoints when rewrites are unavailable.
        // The fragment is split off first so links like 'services#buy' survive.
        $fragment = '';
        if (($hash = strpos($path, '#')) !== false) {
            $fragment = substr($path, $hash);
            $path     = substr($path, 0, $hash);
        }
        if (preg_match('~^inventory/([^/?]+)$~', $path, $m)) {
            $path = 'vehicle.php?slug=' . $m[1];
        } elseif (preg_match('~^([a-z0-9-]+)(\?.*)?$~', $path, $m) && is_file(APP_ROOT . '/' . $m[1] . '.php')) {
            $path = $m[1] . '.php' . ($m[2] ?? '');
        }
        $path .= $fragment;
    }
    return app_base() . $path;
}

/** Cache-busted URL for a CSS/JS/image file. */
function asset(string $path): string
{
    $file = APP_ROOT . '/' . ltrim($path, '/');
    $v    = is_file($file) ? '?v=' . filemtime($file) : '';
    return app_base() . ltrim($path, '/') . $v;
}

function vehicle_url(array $vehicle): string
{
    return url('inventory/' . rawurlencode((string) $vehicle['slug']));
}

function image_url(?string $path, string $fallback = 'assets/img/no-photo.jpg'): string
{
    $path = trim((string) $path);
    if ($path === '' || !is_file(APP_ROOT . '/' . $path)) {
        $path = $fallback;
    }
    return app_base() . ltrim($path, '/');
}

function absolute_url(string $path = ''): string
{
    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host   = $_SERVER['HTTP_HOST'] ?? 'localhost';
    return $scheme . '://' . $host . url($path);
}

function current_path(): string
{
    $uri  = (string) parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
    $base = app_base();
    if (str_starts_with($uri, $base)) {
        $uri = substr($uri, strlen($base));
    }
    return trim(preg_replace('/\.php$/', '', $uri) ?? '', '/');
}

function is_active(string $route): bool
{
    $current = current_path();
    if ($route === '') {
        return $current === '' || $current === 'index';
    }
    return $current === $route || str_starts_with($current, $route . '/');
}

function redirect(string $path): never
{
    header('Location: ' . url($path), true, 303);
    exit;
}

function query_with(array $overrides): string
{
    $params = array_merge($_GET, $overrides);
    $params = array_filter($params, fn($v) => $v !== null && $v !== '');
    return $params ? '?' . http_build_query($params) : '';
}

function phone_href(string $phone): string
{
    return 'tel:' . preg_replace('/[^0-9+]/', '', $phone);
}

/* ---------------------------------------------------------------- settings */

/**
 * @param bool $refresh Re-read the table, e.g. straight after settings_save().
 * @return array<string, string>
 */
function settings_all(bool $refresh = false): array
{
    static $cache = null;
    if ($cache === null || $refresh) {
        $cache = [];
        foreach (db_all('SELECT `key`, `value` FROM settings') as $row) {
            $cache[$row['key']] = (string) $row['value'];
        }
    }
    return $cache;
}

function setting(string $key, string $default = ''): string
{
    $all = settings_all();
    return (isset($all[$key]) && $all[$key] !== '') ? $all[$key] : $default;
}

function settings_save(array $pairs): void
{
    $stmt = db()->prepare('INSERT INTO settings (`key`, `value`) VALUES (?, ?) ON DUPLICATE KEY UPDATE `value` = VALUES(`value`)');
    foreach ($pairs as $key => $value) {
        $stmt->execute([$key, (string) $value]);
    }
    settings_all(true);
}

function site_name(): string
{
    return setting('site_name', 'Lucid Auto Haus');
}

function agent_name(): string
{
    return setting('agent_name', 'Bünyamin Akkaya');
}

function agent_first_name(): string
{
    return explode(' ', trim(agent_name()))[0];
}

/* ------------------------------------------------------------ session bits */

function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="_token" value="' . e(csrf_token()) . '">';
}

function csrf_valid(): bool
{
    $token = $_POST['_token'] ?? '';
    return is_string($token) && $token !== '' && hash_equals(csrf_token(), $token);
}

function flash_set(string $type, string $message): void
{
    $_SESSION['flash'][] = ['type' => $type, 'message' => $message];
}

/** @return array<int, array{type:string,message:string}> */
function flash_pull(): array
{
    $items = $_SESSION['flash'] ?? [];
    unset($_SESSION['flash']);
    return $items;
}

function flash_render(): string
{
    $html = '';
    foreach (flash_pull() as $f) {
        $type = in_array($f['type'], ['success', 'danger', 'warning', 'info'], true) ? $f['type'] : 'info';
        $icon = $type === 'success' ? 'fa-circle-check' : ($type === 'danger' ? 'fa-triangle-exclamation' : 'fa-circle-info');
        $html .= '<div class="alert alert-' . $type . ' alert-dismissible fade show" role="alert">'
            . '<i class="fa-solid ' . $icon . ' me-2"></i>' . e($f['message'])
            . '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>';
    }
    return $html;
}

/** Preserve submitted values on validation errors. */
function old(string $key, string $default = ''): string
{
    $v = $_POST[$key] ?? $default;
    return is_string($v) ? $v : $default;
}

function client_ip(): string
{
    return substr((string) ($_SERVER['REMOTE_ADDR'] ?? ''), 0, 45);
}

/* ------------------------------------------------------------ validation */

function post_str(string $key, int $max = 500): string
{
    $v = $_POST[$key] ?? '';
    return is_string($v) ? mb_substr(trim($v), 0, $max) : '';
}

function valid_email(string $email): bool
{
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

function valid_phone(string $phone): bool
{
    $digits = preg_replace('/\D/', '', $phone) ?? '';
    return strlen($digits) >= 7 && strlen($digits) <= 15;
}

/**
 * Honeypot + timing + reCAPTCHA check shared by every public form. Pass the
 * same $action here and to form_guard_fields() so Google can tell the forms
 * apart in its console; reCAPTCHA is skipped entirely when it is switched off.
 * Returns an error string, or '' when the submission looks human.
 */
function spam_check(string $action = ''): string
{
    if (($_POST['website'] ?? '') !== '') {
        return 'Submission rejected.';
    }
    $started = (int) ($_POST['_ts'] ?? 0);
    if ($started > 0 && time() - $started < 3) {
        return 'That was quick — please try again.';
    }
    return recaptcha_check($action);
}

function form_guard_fields(string $action = ''): string
{
    return csrf_field()
        . '<input type="hidden" name="_ts" value="' . time() . '">'
        . '<div class="hp-field" aria-hidden="true"><label>Website<input type="text" name="website" tabindex="-1" autocomplete="off"></label></div>'
        . recaptcha_field($action);
}

/* --------------------------------------------------------------- vehicles */

const BODY_TYPES    = ['Sedan', 'SUV', 'Coupe', 'Hatchback', 'Truck', 'Convertible', 'Wagon', 'Van', 'Minivan'];
const TRANSMISSIONS = ['Automatic', 'Manual', 'CVT', 'Dual-Clutch'];
const DRIVETRAINS   = ['AWD', 'FWD', 'RWD', '4WD'];
const FUEL_TYPES    = ['Gasoline', 'Diesel', 'Hybrid', 'Plug-in Hybrid', 'Electric'];
const CONDITIONS    = ['used' => 'Pre-Owned', 'certified' => 'Certified Pre-Owned', 'new' => 'New'];
const STATUSES      = ['available' => 'Available', 'pending' => 'Sale Pending', 'sold' => 'Sold'];

function vehicle_title(array $v): string
{
    return trim($v['year'] . ' ' . $v['make'] . ' ' . $v['model'] . ($v['trim'] !== '' ? ' ' . $v['trim'] : ''));
}

function vehicle_price(array $v): float
{
    return ($v['sale_price'] !== null && (float) $v['sale_price'] > 0) ? (float) $v['sale_price'] : (float) $v['price'];
}

function vehicle_on_sale(array $v): bool
{
    return $v['sale_price'] !== null && (float) $v['sale_price'] > 0 && (float) $v['sale_price'] < (float) $v['price'];
}

/** Estimated bi-weekly payment used on cards (84 months, 7.99%, no down). */
function estimate_biweekly(float $price, float $rate = 7.99, int $months = 84): float
{
    if ($price <= 0) {
        return 0;
    }
    $r = $rate / 100 / 12;
    $monthly = $r > 0 ? $price * $r / (1 - pow(1 + $r, -$months)) : $price / $months;
    return $monthly * 12 / 26;
}

/** @return array{items: array, total: int} */
function vehicles_search(array $f, int $page = 1, int $perPage = 9): array
{
    $where  = ['v.status <> "hidden"'];
    $params = [];

    $status = $f['status'] ?? 'available';
    if ($status === 'available') {
        $where[] = 'v.status IN ("available", "pending")';
    } elseif ($status !== 'all') {
        $where[] = 'v.status = ?';
        $params[] = $status;
    }
    if (!empty($f['q'])) {
        $where[] = '(CONCAT_WS(" ", v.year, v.make, v.model, v.trim, v.exterior_color, v.stock_number) LIKE ?)';
        $params[] = '%' . $f['q'] . '%';
    }
    foreach (['make', 'body_type', 'transmission', 'fuel_type', 'drivetrain', 'condition'] as $col) {
        if (!empty($f[$col])) {
            $where[]  = "v.`$col` = ?";
            $params[] = $f[$col];
        }
    }
    if (!empty($f['min_price'])) { $where[] = 'COALESCE(NULLIF(v.sale_price,0), v.price) >= ?'; $params[] = (float) $f['min_price']; }
    if (!empty($f['max_price'])) { $where[] = 'COALESCE(NULLIF(v.sale_price,0), v.price) <= ?'; $params[] = (float) $f['max_price']; }
    if (!empty($f['min_year']))  { $where[] = 'v.year >= ?'; $params[] = (int) $f['min_year']; }
    if (!empty($f['max_year']))  { $where[] = 'v.year <= ?'; $params[] = (int) $f['max_year']; }
    if (!empty($f['max_km']))    { $where[] = 'v.mileage <= ?'; $params[] = (int) $f['max_km']; }
    if (!empty($f['featured']))  { $where[] = 'v.is_featured = 1'; }

    $orders = [
        'newest'     => 'v.created_at DESC, v.id DESC',
        'price_asc'  => 'COALESCE(NULLIF(v.sale_price,0), v.price) ASC',
        'price_desc' => 'COALESCE(NULLIF(v.sale_price,0), v.price) DESC',
        'year_desc'  => 'v.year DESC, v.mileage ASC',
        'km_asc'     => 'v.mileage ASC',
        'featured'   => 'v.is_featured DESC, v.sort_order ASC, v.created_at DESC',
    ];
    $order = $orders[$f['sort'] ?? ''] ?? $orders['featured'];
    $whereSql = implode(' AND ', $where);

    $total  = (int) db_value("SELECT COUNT(*) FROM vehicles v WHERE $whereSql", $params);
    $offset = max(0, ($page - 1) * $perPage);
    $items  = db_all("SELECT v.* FROM vehicles v WHERE $whereSql ORDER BY $order LIMIT $perPage OFFSET $offset", $params);

    return ['items' => $items, 'total' => $total];
}

function vehicle_by_slug(string $slug): ?array
{
    return db_one('SELECT * FROM vehicles WHERE slug = ? LIMIT 1', [$slug]);
}

function vehicle_by_id(int $id): ?array
{
    return db_one('SELECT * FROM vehicles WHERE id = ? LIMIT 1', [$id]);
}

/** @return array<int, array> */
function vehicle_images(int $vehicleId): array
{
    return db_all('SELECT * FROM vehicle_images WHERE vehicle_id = ? ORDER BY sort_order ASC, id ASC', [$vehicleId]);
}

function vehicle_cover(array $v): string
{
    if (!empty($v['cover_image'])) {
        return image_url($v['cover_image']);
    }
    $first = db_value('SELECT path FROM vehicle_images WHERE vehicle_id = ? ORDER BY sort_order ASC, id ASC LIMIT 1', [(int) $v['id']]);
    return image_url($first ? (string) $first : null);
}

function featured_vehicles(int $limit = 6): array
{
    return db_all('SELECT * FROM vehicles WHERE status IN ("available","pending") ORDER BY is_featured DESC, sort_order ASC, created_at DESC LIMIT ' . (int) $limit);
}

function similar_vehicles(array $v, int $limit = 3): array
{
    $price = vehicle_price($v);
    return db_all(
        'SELECT * FROM vehicles WHERE id <> ? AND status IN ("available","pending")
         ORDER BY (body_type = ?) DESC, (make = ?) DESC, ABS(COALESCE(NULLIF(sale_price,0), price) - ?) ASC LIMIT ' . (int) $limit,
        [(int) $v['id'], $v['body_type'], $v['make'], $price]
    );
}

/** Distinct values used to build the inventory filters. */
function inventory_facets(): array
{
    static $facets = null;
    if ($facets !== null) {
        return $facets;
    }
    $live = 'status IN ("available","pending")';
    return $facets = [
        'makes'      => array_column(db_all("SELECT make, COUNT(*) n FROM vehicles WHERE $live GROUP BY make ORDER BY make"), 'n', 'make'),
        'body_types' => array_column(db_all("SELECT body_type, COUNT(*) n FROM vehicles WHERE $live GROUP BY body_type ORDER BY body_type"), 'n', 'body_type'),
        'years'      => db_one("SELECT MIN(year) lo, MAX(year) hi FROM vehicles WHERE $live") ?: ['lo' => 2015, 'hi' => (int) date('Y')],
        'prices'     => db_one("SELECT MIN(COALESCE(NULLIF(sale_price,0), price)) lo, MAX(COALESCE(NULLIF(sale_price,0), price)) hi FROM vehicles WHERE $live") ?: ['lo' => 0, 'hi' => 100000],
        'count'      => (int) db_value("SELECT COUNT(*) FROM vehicles WHERE $live"),
    ];
}

/** Price steps offered by the inventory filter selects. The nav price bands
 *  snap to these, so a band chosen from the nav survives the next filter
 *  submit instead of being silently dropped by inventory.php. */
function price_steps(): array
{
    return [15000, 20000, 25000, 30000, 35000, 40000, 50000, 60000, 75000, 100000, 150000];
}

/** Up to three price bands for the nav flyout: boundaries snapped to
 *  price_steps(), every band guaranteed to hold at least one live vehicle. */
function inventory_price_bands(): array
{
    static $bands = null;
    if ($bands !== null) {
        return $bands;
    }
    $prices = array_map(
        'floatval',
        array_column(db_all('SELECT COALESCE(NULLIF(sale_price,0), price) AS p FROM vehicles WHERE status IN ("available","pending") ORDER BY p'), 'p')
    );
    $total = count($prices);
    if ($total < 3) {
        return $bands = [];
    }
    $count = static fn (array $p, ?float $lo, ?float $hi): int => count(array_filter(
        $p,
        static fn (float $v): bool => ($lo === null || $v > $lo) && ($hi === null || $v <= $hi)
    ));
    /* pick the steps nearest the 1/3 and 2/3 quantiles that leave no band empty */
    $steps = array_values(array_filter(price_steps(), static fn (int $s): bool => $s > $prices[0] && $s < $prices[$total - 1]));
    $best  = null;
    foreach ($steps as $i => $c1) {
        foreach (array_slice($steps, $i + 1) as $c2) {
            $a = $count($prices, null, (float) $c1);
            $b = $count($prices, (float) $c1, (float) $c2);
            $c = $count($prices, (float) $c2, null);
            if ($a < 1 || $b < 1 || $c < 1) {
                continue;
            }
            /* most even split wins */
            $spread = max($a, $b, $c) - min($a, $b, $c);
            if ($best === null || $spread < $best['spread']) {
                $best = ['spread' => $spread, 'c1' => $c1, 'c2' => $c2, 'n' => [$a, $b, $c]];
            }
        }
    }
    if ($best === null) {
        return $bands = [];
    }
    return $bands = [
        ['label' => 'Under ' . money($best['c1']),                         'count' => $best['n'][0], 'q' => ['max_price' => $best['c1']]],
        ['label' => money($best['c1']) . ' – ' . money($best['c2']),       'count' => $best['n'][1], 'q' => ['min_price' => $best['c1'], 'max_price' => $best['c2']]],
        ['label' => money($best['c2']) . ' and up',                        'count' => $best['n'][2], 'q' => ['min_price' => $best['c2']]],
    ];
}

/* ------------------------------------------------------------------ leads */

function lead_create(string $type, array $data): int
{
    $id = db_insert('leads', [
        'type'       => $type,
        'status'     => 'new',
        'vehicle_id' => $data['vehicle_id'] ?? null,
        'name'       => $data['name'],
        'email'      => $data['email'],
        'phone'      => $data['phone'] ?? '',
        'message'    => $data['message'] ?? '',
        'details'    => json_encode($data['details'] ?? [], JSON_UNESCAPED_UNICODE),
        'ip'         => client_ip(),
    ]);
    lead_notify($type, $data);
    lead_sms_notify($type, $data);
    return $id;
}

/** Best-effort email notification; failures never block the visitor. The SMS
 *  counterpart lives in includes/sms.php. */
function lead_notify(string $type, array $data): void
{
    $to = setting('notify_email', setting('email'));
    if ($to === '' || !valid_email($to)) {
        return;
    }
    $subject = '[' . site_name() . '] New ' . strtolower(lead_type_label($type)) . ' from ' . $data['name'];
    $body = "Name: {$data['name']}\nEmail: {$data['email']}\nPhone: " . ($data['phone'] ?? '') . "\n";
    foreach ($data['details'] ?? [] as $k => $v) {
        $body .= ucwords(str_replace('_', ' ', (string) $k)) . ": $v\n";
    }
    $body .= "\n" . ($data['message'] ?? '') . "\n\n— Sent from " . absolute_url('admin/leads.php');
    $headers = 'From: ' . site_name() . ' <no-reply@' . ($_SERVER['HTTP_HOST'] ?? 'localhost') . ">\r\nReply-To: {$data['email']}\r\nContent-Type: text/plain; charset=UTF-8";
    try {
        @mail($to, $subject, $body, $headers);
    } catch (\Throwable) {
        // Mail is optional; leads are always stored in the database.
    }
}

/* ---------------------------------------------------------------- content */

function testimonials(int $limit = 0): array
{
    $sql = 'SELECT * FROM testimonials WHERE is_published = 1 ORDER BY sort_order ASC, created_at DESC';
    return db_all($sql . ($limit > 0 ? ' LIMIT ' . (int) $limit : ''));
}

function faqs(): array
{
    return db_all('SELECT * FROM faqs WHERE is_published = 1 ORDER BY sort_order ASC, id ASC');
}

function business_hours(): array
{
    return [
        'Monday – Friday' => setting('hours_weekdays', '9:00 AM – 7:00 PM'),
        'Saturday'        => setting('hours_saturday', '10:00 AM – 5:00 PM'),
        'Sunday'          => setting('hours_sunday', 'By appointment'),
    ];
}

function social_links(): array
{
    $links = [];
    foreach (['instagram' => 'fa-brands fa-instagram', 'facebook' => 'fa-brands fa-facebook-f', 'tiktok' => 'fa-brands fa-tiktok', 'youtube' => 'fa-brands fa-youtube', 'linkedin' => 'fa-brands fa-linkedin-in'] as $key => $icon) {
        $u = setting('social_' . $key);
        if ($u !== '' && preg_match('~^https?://~i', $u)) {
            $links[$key] = ['url' => $u, 'icon' => $icon, 'label' => ucfirst($key)];
        }
    }
    return $links;
}

/** Bootstrap pagination markup for the inventory grid. */
function pagination(int $page, int $total, int $perPage): string
{
    $pages = (int) ceil($total / max(1, $perPage));
    if ($pages <= 1) {
        return '';
    }
    $html = '<nav aria-label="Inventory pages"><ul class="pagination justify-content-center">';
    $link = fn(int $p, string $label, bool $disabled = false, bool $active = false) =>
        '<li class="page-item' . ($disabled ? ' disabled' : '') . ($active ? ' active' : '') . '">'
        . '<a class="page-link" href="' . e(query_with(['page' => $p > 1 ? $p : null])) . '">' . $label . '</a></li>';
    $html .= $link(max(1, $page - 1), '<i class="fa-solid fa-chevron-left"></i>', $page <= 1);
    for ($p = 1; $p <= $pages; $p++) {
        if ($pages > 7 && abs($p - $page) > 2 && $p !== 1 && $p !== $pages) {
            if ($p === 2 || $p === $pages - 1) {
                $html .= '<li class="page-item disabled"><span class="page-link">…</span></li>';
            }
            continue;
        }
        $html .= $link($p, (string) $p, false, $p === $page);
    }
    $html .= $link(min($pages, $page + 1), '<i class="fa-solid fa-chevron-right"></i>', $page >= $pages);
    return $html . '</ul></nav>';
}
