<?php
require __DIR__ . '/includes/app.php';

$subjects = [
    'general'    => 'General question',
    'test-drive' => 'Book a test drive',
    'sourcing'   => 'Find me a specific vehicle',
    'leasing'    => 'Leasing',
    'financing'  => 'Financing',
    'trade-in'   => 'Trade-in / sell my car',
    'other'      => 'Something else',
];
$preset = (string) ($_GET['subject'] ?? '');
$preset = isset($subjects[$preset]) ? $preset : 'general';

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_valid()) {
        $errors['form'] = 'Your session expired — please try again.';
    } elseif (($spam = spam_check()) !== '') {
        $errors['form'] = $spam;
    } else {
        $name    = post_str('name', 100);
        $email   = post_str('email', 150);
        $phone   = post_str('phone', 40);
        $subject = post_str('subject', 30);
        $message = post_str('message', 4000);
        $contact = post_str('preferred_contact', 20);
        if (mb_strlen($name) < 2)          $errors['name'] = 'Please enter your name.';
        if (!valid_email($email))          $errors['email'] = 'Please enter a valid email address.';
        if ($phone !== '' && !valid_phone($phone)) $errors['phone'] = 'Please enter a valid phone number.';
        if (mb_strlen($message) < 10)      $errors['message'] = 'Tell me a little more (at least 10 characters).';
        if (!$errors) {
            lead_create($subject === 'test-drive' ? 'test_drive' : 'contact', [
                'name' => $name, 'email' => $email, 'phone' => $phone, 'message' => $message,
                'details' => array_filter(['subject' => $subjects[$subject] ?? $subject, 'preferred_contact' => $contact]),
            ]);
            flash_set('success', "Thanks {$name} — your message is in my inbox. I'll get back to you within a few hours during business hours.");
            redirect('contact#message');
        }
    }
}

$pageTitle       = 'Contact';
$metaDescription = 'Call, text or message ' . agent_name() . ' at ' . site_name() . '. ' . setting('address_line') . ', ' . setting('city') . '. ' . setting('phone') . '.';
$hero = [
    'image'  => 'assets/img/hero/home-day.jpg',
    'size'   => 'sm',
    'kicker' => 'Get in touch',
    'title'  => 'Let\'s talk cars.',
    'text'   => 'Call, text, WhatsApp or drop by — whichever is easiest for you.',
    'crumbs' => ['Contact' => null],
    'focus'  => '72% 25%',
];
require __DIR__ . '/includes/header.php';
require __DIR__ . '/includes/hero.php';
$hours = business_hours();
?>

<section class="section section-dark" id="message">
    <div class="container">
        <div class="row g-4 g-lg-5">
            <div class="col-lg-4">
                <div class="info-card mb-4">
                    <h3><i class="fa-solid fa-phone"></i>Direct line</h3>
                    <ul class="info-list">
                        <li><i class="fa-solid fa-phone"></i><a href="<?= e(phone_href(setting('phone'))) ?>"><?= e(setting('phone')) ?></a></li>
                        <?php if (setting('whatsapp') !== ''): ?><li><i class="fa-brands fa-whatsapp"></i><a href="https://wa.me/<?= e(preg_replace('/\D/', '', setting('whatsapp'))) ?>" target="_blank" rel="noopener">Message on WhatsApp</a></li><?php endif; ?>
                        <li><i class="fa-solid fa-envelope"></i><a href="mailto:<?= e(setting('email')) ?>"><?= e(setting('email')) ?></a></li>
                    </ul>
                </div>
                <div class="info-card mb-4">
                    <h3><i class="fa-solid fa-location-dot"></i>Showroom</h3>
                    <p class="mb-2"><?= e(setting('address_line')) ?><br><?= e(setting('city')) ?></p>
                    <p class="small text-silver mb-0">Serving <?= e(setting('service_area')) ?>. Home and office visits available across the GTA.</p>
                </div>
                <div class="info-card">
                    <h3><i class="fa-regular fa-clock"></i>Hours</h3>
                    <table class="hours-table">
                        <?php foreach ($hours as $day => $time): ?><tr><td><?= e($day) ?></td><td><?= e($time) ?></td></tr><?php endforeach; ?>
                    </table>
                    <p class="small text-silver mt-3 mb-0">Evenings and weekends by appointment — just ask.</p>
                </div>
            </div>
            <div class="col-lg-8">
                <?= flash_render() ?>
                <form method="post" action="<?= url('contact') ?>#message" class="form-card needs-validation" novalidate>
                    <?= form_guard_fields() ?>
                    <?php if (!empty($errors['form'])): ?><div class="alert alert-danger"><?= e($errors['form']) ?></div><?php endif; ?>
                    <h3>Send a message</h3>
                    <p class="form-intro">I read and answer every message personally.</p>
                    <div class="row g-3">
                        <div class="col-md-6"><label class="form-label" for="c-name">Full name *</label><input type="text" class="form-control<?= isset($errors['name']) ? ' is-invalid' : '' ?>" id="c-name" name="name" value="<?= e(old('name')) ?>" required><div class="invalid-feedback">Please enter your name.</div></div>
                        <div class="col-md-6"><label class="form-label" for="c-email">Email *</label><input type="email" class="form-control<?= isset($errors['email']) ? ' is-invalid' : '' ?>" id="c-email" name="email" value="<?= e(old('email')) ?>" required><div class="invalid-feedback">Please enter a valid email.</div></div>
                        <div class="col-md-6"><label class="form-label" for="c-phone">Phone</label><input type="tel" class="form-control<?= isset($errors['phone']) ? ' is-invalid' : '' ?>" id="c-phone" name="phone" value="<?= e(old('phone')) ?>"><div class="invalid-feedback"><?= e($errors['phone'] ?? '') ?></div></div>
                        <div class="col-md-6"><label class="form-label" for="c-subject">I'm reaching out about</label>
                            <select class="form-select" id="c-subject" name="subject">
                                <?php foreach ($subjects as $k => $label): ?><option value="<?= $k ?>"<?= old('subject', $preset) === $k ? ' selected' : '' ?>><?= e($label) ?></option><?php endforeach; ?>
                            </select></div>
                        <div class="col-12"><label class="form-label" for="c-msg">Message *</label><textarea class="form-control<?= isset($errors['message']) ? ' is-invalid' : '' ?>" id="c-msg" name="message" rows="5" required minlength="10" placeholder="<?= $preset === 'sourcing' ? 'Year, make, model, trim, colour, budget and timeline…' : ($preset === 'test-drive' ? 'Which vehicle, and when works for you?' : 'How can I help?') ?>"><?= e(old('message')) ?></textarea><div class="invalid-feedback"><?= e($errors['message'] ?? 'Please enter a message.') ?></div></div>
                        <div class="col-md-6"><label class="form-label" for="c-contact">Best way to reach you</label>
                            <select class="form-select" id="c-contact" name="preferred_contact">
                                <?php foreach (['Phone', 'Text', 'Email', 'WhatsApp'] as $c): ?><option<?= old('preferred_contact') === $c ? ' selected' : '' ?>><?= $c ?></option><?php endforeach; ?>
                            </select></div>
                        <div class="col-md-6 d-flex align-items-end"><button type="submit" class="btn btn-lah w-100"><i class="fa-solid fa-paper-plane me-2"></i>Send message</button></div>
                    </div>
                    <p class="form-text mt-3 mb-0">No spam, no lists. <a href="<?= url('privacy') ?>">Privacy policy</a>.</p>
                </form>
            </div>
        </div>
    </div>
</section>

<?php if (setting('google_maps_embed') !== ''): ?>
<section class="section-sm section-ink">
    <div class="container">
        <div class="map-embed">
            <iframe src="<?= e(setting('google_maps_embed')) ?>" loading="lazy" allowfullscreen referrerpolicy="no-referrer-when-downgrade" title="Map to <?= e(site_name()) ?>"></iframe>
        </div>
    </div>
</section>
<?php endif; ?>

<?php require __DIR__ . '/includes/footer.php'; ?>
