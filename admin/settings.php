<?php
require __DIR__ . '/includes/auth.php';
require_login();

/** Every editable setting, grouped into tabs. type: text | textarea | email | url | number */
$groups = [
    'business' => ['Business', [
        'site_name'        => ['Business name', 'text', 'Shown in the browser title, footer and emails.'],
        'tagline'          => ['Tagline', 'text', 'Appears after the business name in the home page title.'],
        'agent_name'       => ['Your name', 'text', 'Displayed on the About page, vehicle pages and the home page.'],
        'agent_title'      => ['Your title', 'text', 'e.g. Sales & Leasing Consultant'],
        'agent_bio_short'  => ['Short bio', 'textarea', 'One paragraph — used on the home page and in the footer.'],
        'agent_bio'        => ['Full bio', 'textarea', 'About page. Blank lines start new paragraphs.'],
        'years_experience' => ['Years of experience', 'number', ''],
        'cars_sold'        => ['Vehicles sold', 'text', 'e.g. 1,200'],
        'google_rating'    => ['Google rating', 'text', 'e.g. 4.9'],
    ]],
    'contact' => ['Contact', [
        'phone'            => ['Phone', 'text', 'Displayed everywhere; used for tap-to-call.'],
        'whatsapp'         => ['WhatsApp number', 'text', 'Digits only with country code, e.g. 14165550148. Leave blank to hide the WhatsApp button.'],
        'email'            => ['Public email', 'email', ''],
        'notify_email'     => ['Lead notification email', 'email', 'New leads are emailed here. Leave blank to only store them in the admin panel.'],
        'address_line'     => ['Street address', 'text', ''],
        'city'             => ['City, province, postal code', 'text', ''],
        'service_area'     => ['Service area', 'text', 'Shown in the top bar, e.g. Toronto · Mississauga · Brampton'],
        'hours_weekdays'   => ['Hours — Monday to Friday', 'text', ''],
        'hours_saturday'   => ['Hours — Saturday', 'text', ''],
        'hours_sunday'     => ['Hours — Sunday', 'text', ''],
        'google_maps_embed'=> ['Google Maps embed URL', 'url', 'From Google Maps → Share → Embed a map, copy only the src="…" URL. Leave blank to hide the map.'],
    ]],
    'social' => ['Social', [
        'social_instagram' => ['Instagram URL', 'url', ''],
        'social_facebook'  => ['Facebook URL', 'url', ''],
        'social_tiktok'    => ['TikTok URL', 'url', ''],
        'social_youtube'   => ['YouTube URL', 'url', ''],
        'social_linkedin'  => ['LinkedIn URL', 'url', ''],
    ]],
    'content' => ['Page text', [
        'hero_home_kicker'     => ['Home hero — small label', 'text', ''],
        'hero_home_title'      => ['Home hero — headline', 'text', 'If it ends with "No games." that phrase is highlighted in red.'],
        'hero_home_text'       => ['Home hero — supporting text', 'textarea', ''],
        'hero_inventory_title' => ['Inventory hero — headline', 'text', ''],
        'hero_inventory_text'  => ['Inventory hero — supporting text', 'textarea', ''],
        'hero_about_title'     => ['About hero — headline', 'text', ''],
        'hero_about_text'      => ['About hero — supporting text', 'textarea', ''],
        'footer_note'          => ['Footer legal note', 'textarea', ''],
        'meta_description'     => ['Default meta description', 'textarea', 'Used for search engines and social previews on pages without their own.'],
    ]],
    'finance' => ['Financing', [
        'finance_rate_default' => ['Default APR % in calculators', 'text', 'e.g. 7.99'],
        'finance_term_default' => ['Default term (months)', 'number', '36, 48, 60, 72, 84 or 96'],
    ]],
];

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_csrf();
    $pairs = [];
    foreach ($groups as $g => [$label, $fields]) {
        foreach ($fields as $key => [$fl, $type]) {
            if (!array_key_exists($key, $_POST) || !is_string($_POST[$key])) {
                continue;
            }
            $val = trim($_POST[$key]);
            if ($type === 'email' && $val !== '' && !valid_email($val)) {
                $errors[$key] = 'Enter a valid email address.';
            } elseif ($type === 'url' && $val !== '' && !preg_match('~^https?://~i', $val)) {
                $errors[$key] = 'Must start with http:// or https://';
            } elseif ($type === 'number' && $val !== '' && !is_numeric($val)) {
                $errors[$key] = 'Must be a number.';
            }
            $pairs[$key] = $val;
        }
    }
    if (!$errors) {
        settings_save($pairs);
        flash_set('success', 'Settings saved.');
        redirect('admin/settings.php' . (isset($_POST['_tab']) ? '?tab=' . rawurlencode((string) $_POST['_tab']) : ''));
    }
}
$tab        = (string) ($_GET['tab'] ?? 'business');
$tab        = isset($groups[$tab]) ? $tab : 'business';
$adminTitle = 'Settings';
require __DIR__ . '/includes/header.php';
$val = fn(string $k) => e($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST[$k]) && is_string($_POST[$k]) ? $_POST[$k] : setting($k));
?>
<form method="post">
    <?= csrf_field() ?>
    <input type="hidden" name="_tab" value="<?= e($tab) ?>" id="tabField">
    <?php if ($errors): ?><div class="alert alert-danger">Please fix the highlighted fields.</div><?php endif; ?>
    <ul class="nav nav-tabs mb-3" role="tablist">
        <?php foreach ($groups as $g => [$label]): ?>
            <li class="nav-item"><button class="nav-link<?= $g === $tab ? ' active' : '' ?>" data-bs-toggle="tab" data-bs-target="#tab-<?= $g ?>" type="button" role="tab" onclick="document.getElementById('tabField').value='<?= $g ?>'"><?= e($label) ?></button></li>
        <?php endforeach; ?>
    </ul>
    <div class="tab-content">
        <?php foreach ($groups as $g => [$label, $fields]): ?>
            <div class="tab-pane fade<?= $g === $tab ? ' show active' : '' ?>" id="tab-<?= $g ?>" role="tabpanel">
                <div class="card" style="max-width:900px">
                    <div class="card-body">
                        <?php foreach ($fields as $key => [$fl, $type, $help]): ?>
                            <div class="mb-3">
                                <label class="form-label" for="s-<?= $key ?>"><?= e($fl) ?></label>
                                <?php if ($type === 'textarea'): ?>
                                    <textarea class="form-control<?= isset($errors[$key]) ? ' is-invalid' : '' ?>" id="s-<?= $key ?>" name="<?= $key ?>" rows="<?= in_array($key, ['agent_bio'], true) ? 8 : 3 ?>"><?= $val($key) ?></textarea>
                                <?php else: ?>
                                    <input type="<?= $type === 'number' ? 'text' : ($type === 'url' ? 'url' : $type) ?>" class="form-control<?= isset($errors[$key]) ? ' is-invalid' : '' ?>" id="s-<?= $key ?>" name="<?= $key ?>" value="<?= $val($key) ?>">
                                <?php endif; ?>
                                <?php if (isset($errors[$key])): ?><div class="invalid-feedback"><?= e($errors[$key]) ?></div><?php endif; ?>
                                <?php if ($help): ?><div class="text-muted-sm"><?= e($help) ?></div><?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
    <div class="mt-3"><button class="btn btn-primary"><i class="fa-solid fa-floppy-disk me-1"></i>Save settings</button></div>
</form>
<?php require __DIR__ . '/includes/footer.php'; ?>
