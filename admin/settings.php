<?php
require __DIR__ . '/includes/auth.php';
require_login();

/** Every editable setting, grouped into tabs. type: text | textarea | email | url | number | phone | password | bool */
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
        'google_maps_url'  => ['Google Maps directions URL', 'url', 'The public Share link used by Get directions links.'],
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
    'sms' => ['SMS alerts', [
        'sms_enabled'        => ['Text me every new lead', 'bool', 'Contact messages, vehicle inquiries, test drives, trade-ins and financing applications are all texted the moment they are submitted. Leads are stored either way.'],
        'sms_notify_number'  => ['Send alerts to', 'phone', 'Your mobile number, e.g. +1 647 936 8096.'],
        'twilio_account_sid' => ['Twilio Account SID', 'text', 'Starts with AC — Twilio Console → Account Info.'],
        'twilio_auth_token'  => ['Twilio Auth Token', 'password', 'Leave blank to keep the token already saved. Rotate it in the Twilio Console if it is ever exposed.'],
        'twilio_from_number' => ['Twilio phone number', 'phone', 'The Twilio number the alert is sent from, e.g. +1 779 209 2992.'],
    ]],
    'recaptcha' => ['reCAPTCHA', [
        'recaptcha_enabled'    => ['Protect the public forms', 'bool', 'Contact, vehicle inquiry, test drive, trade-in and financing. Google scores each visitor invisibly — nobody is asked to click pictures of traffic lights.'],
        'recaptcha_site_key'   => ['Site key', 'text', 'From google.com/recaptcha/admin — the v3 key for this domain. It is public and appears in the page source.'],
        'recaptcha_secret_key' => ['Secret key', 'password', 'Leave blank to keep the key already saved. Never share it or commit it to the repository.'],
        'recaptcha_min_score'  => ['Minimum score', 'text', '0.0 is certainly a bot, 1.0 is certainly a person. 0.5 is Google’s default — lower it to 0.3 if real enquiries are being turned away.'],
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
            if ($type === 'password' && $val === '') {
                // Blank means "keep the stored secret" — the field is never pre-filled.
                continue;
            }
            if ($type === 'bool') {
                $pairs[$key] = $val === '1' ? '1' : '0';
                continue;
            }
            if ($type === 'email' && $val !== '' && !valid_email($val)) {
                $errors[$key] = 'Enter a valid email address.';
            } elseif ($type === 'url' && $val !== '' && !preg_match('~^https?://~i', $val)) {
                $errors[$key] = 'Must start with http:// or https://';
            } elseif ($type === 'number' && $val !== '' && !is_numeric($val)) {
                $errors[$key] = 'Must be a number.';
            } elseif ($type === 'phone' && $val !== '' && !valid_phone($val)) {
                $errors[$key] = 'Enter a valid phone number with its country code.';
            }
            $pairs[$key] = $type === 'phone' && $val !== '' ? sms_e164($val) : $val;
        }
    }
    if (isset($pairs['recaptcha_min_score']) && $pairs['recaptcha_min_score'] !== ''
        && (!is_numeric($pairs['recaptcha_min_score'])
            || (float) $pairs['recaptcha_min_score'] < 0 || (float) $pairs['recaptcha_min_score'] > 1)) {
        $errors['recaptcha_min_score'] = 'Enter a score between 0.0 and 1.0.';
    }
    if (!$errors) {
        settings_save($pairs);
        flash_set('success', 'Settings saved.');
        // "Send test SMS" saves first, so the test always uses what is on screen.
        if (isset($_POST['test_sms'])) {
            $c = sms_config();
            if ($c['to'] === '') {
                flash_set('danger', 'Enter the number to send alerts to first.');
            } else {
                $test = sms_send($c['to'], site_name() . ': SMS alerts are working. New leads will arrive here.', $c);
                if ($test['ok']) {
                    flash_set('success', 'Test message sent to ' . $c['to'] . '.');
                } else {
                    flash_set('danger', 'Twilio rejected the test: ' . $test['error']);
                }
            }
            redirect('admin/settings.php?tab=sms');
        }
        // The connection test also saves first, so it uses what is on screen.
        if (isset($_POST['test_recaptcha'])) {
            $test = recaptcha_test_connection();
            if ($test['ok']) {
                flash_set('success', 'This server can reach Google. ' . $test['note']);
            } else {
                flash_set('danger', $test['error']);
            }
            redirect('admin/settings.php?tab=recaptcha');
        }
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
    <?php /* Implicit submit for the Enter key, so a keyboard save can never
             pick the "send test SMS" button that appears earlier in the DOM. */ ?>
    <button type="submit" class="d-none" tabindex="-1" aria-hidden="true"></button>
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
                                <?php elseif ($type === 'bool'): ?>
                                    <select class="form-select" id="s-<?= $key ?>" name="<?= $key ?>">
                                        <option value="0"<?= $val($key) === '1' ? '' : ' selected' ?>>Off</option>
                                        <option value="1"<?= $val($key) === '1' ? ' selected' : '' ?>>On</option>
                                    </select>
                                <?php elseif ($type === 'password'): ?>
                                    <input type="password" class="form-control<?= isset($errors[$key]) ? ' is-invalid' : '' ?>" id="s-<?= $key ?>" name="<?= $key ?>" value="" autocomplete="new-password" placeholder="<?= setting($key) !== '' ? 'Saved — leave blank to keep it' : 'Not set' ?>">
                                <?php else: ?>
                                    <input type="<?= in_array($type, ['number', 'text'], true) ? 'text' : ($type === 'phone' ? 'tel' : $type) ?>" class="form-control<?= isset($errors[$key]) ? ' is-invalid' : '' ?>" id="s-<?= $key ?>" name="<?= $key ?>" value="<?= $val($key) ?>">
                                <?php endif; ?>
                                <?php if (isset($errors[$key])): ?><div class="invalid-feedback"><?= e($errors[$key]) ?></div><?php endif; ?>
                                <?php if ($help): ?><div class="text-muted-sm"><?= e($help) ?></div><?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                        <?php if ($g === 'recaptcha'): ?>
                            <hr>
                            <p class="text-muted-sm mb-2">Saves the fields above, then checks that this server can reach Google. Google will not confirm a secret key on its own, so the real test is submitting a form on the public site &mdash; and remember to list this domain on the key in the reCAPTCHA console.</p>
                            <button class="btn btn-outline-secondary" name="test_recaptcha" value="1" onclick="document.getElementById('tabField').value='recaptcha'"><i class="fa-solid fa-shield-halved me-1"></i>Save &amp; test connection</button>
                        <?php endif; ?>
                        <?php if ($g === 'sms'): ?>
                            <hr>
                            <p class="text-muted-sm mb-2">Saves the fields above, then sends one real text so you can confirm it arrives.</p>
                            <button class="btn btn-outline-secondary" name="test_sms" value="1" onclick="document.getElementById('tabField').value='sms'"><i class="fa-solid fa-paper-plane me-1"></i>Save &amp; send test SMS</button>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
    <div class="mt-3"><button class="btn btn-primary"><i class="fa-solid fa-floppy-disk me-1"></i>Save settings</button></div>
</form>
<?php require __DIR__ . '/includes/footer.php'; ?>
