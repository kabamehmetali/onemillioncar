<?php
require __DIR__ . '/includes/app.php';

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_valid()) {
        $errors['form'] = 'Your session expired — please try again.';
    } elseif (($spam = spam_check()) !== '') {
        $errors['form'] = $spam;
    } else {
        $name  = post_str('name', 100);
        $email = post_str('email', 150);
        $phone = post_str('phone', 40);
        $year  = post_str('year', 4);
        $make  = post_str('make', 60);
        $model = post_str('model', 80);
        $trim  = post_str('trim', 80);
        $km    = post_str('mileage', 12);
        $cond  = post_str('condition', 40);
        $owed  = post_str('owed', 40);
        $intent = post_str('intent', 40);
        $notes = post_str('message', 3000);
        if (mb_strlen($name) < 2) $errors['name'] = 'Please enter your name.';
        if (!valid_email($email)) $errors['email'] = 'Please enter a valid email address.';
        if (!valid_phone($phone)) $errors['phone'] = 'Please enter a valid phone number.';
        if (!preg_match('/^(19|20)\d{2}$/', $year)) $errors['year'] = 'Enter a 4-digit year.';
        if ($make === '')  $errors['make'] = 'Required.';
        if ($model === '') $errors['model'] = 'Required.';
        if ($km === '' || !ctype_digit(str_replace(',', '', $km))) $errors['mileage'] = 'Enter the kilometres as a number.';
        if (!$errors) {
            lead_create('trade_in', [
                'name' => $name, 'email' => $email, 'phone' => $phone, 'message' => $notes,
                'details' => array_filter(['vehicle' => trim("$year $make $model $trim"), 'mileage_km' => str_replace(',', '', $km), 'condition' => $cond, 'amount_owed' => $owed, 'intent' => $intent]),
            ]);
            flash_set('success', "Thanks {$name} — I've got the details on your {$year} {$make} {$model}. You'll have a firm number from me within 24 hours.");
            redirect('trade-in#appraisal');
        }
    }
}

$pageTitle       = 'Trade-In & Sell Your Car';
$metaDescription = 'Get a firm trade-in or cash offer for your vehicle within 24 hours. ' . site_name() . ' pays more because we retail what we buy. Toronto & GTA.';
$hero = [
    'image'  => 'assets/img/hero/services.jpg',
    'size'   => 'md',
    'kicker' => 'Trade-in · Sell outright',
    'title'  => 'Your car is worth more than the dealership offered.',
    'text'   => 'Because I retail what I buy instead of sending it to auction, I can pay closer to what your car is actually worth. Firm offer within 24 hours.',
    'buttons' => [['label' => 'Get my offer', 'url' => '#appraisal']],
    'crumbs' => ['Trade-In' => null],
    'focus'  => '55% 0%',
];
require __DIR__ . '/includes/header.php';
require __DIR__ . '/includes/hero.php';
?>

<section class="section section-dark">
    <div class="container">
        <div class="row g-5 align-items-center">
            <div class="col-lg-6">
                <span class="section-kicker">Why my offers are higher</span>
                <h2 class="section-title">Auction math vs. retail math.</h2>
                <p>When a franchise dealer appraises your trade, they price it for the wholesale auction because that's where most of them go. I price it for the retail lot — mine. That difference is often $1,500–$4,000 on a typical vehicle, and it goes to you.</p>
                <ul class="check-list mt-4">
                    <li><i class="fa-solid fa-circle-check"></i><div><strong class="text-white">Firm offer in 24 hours.</strong> Not a range, not "bring it in and we'll see".</div></li>
                    <li><i class="fa-solid fa-circle-check"></i><div><strong class="text-white">Trade or sell outright.</strong> No obligation to buy from me.</div></li>
                    <li><i class="fa-solid fa-circle-check"></i><div><strong class="text-white">HST advantage.</strong> Trading in means you pay tax only on the difference — real savings on top of the offer.</div></li>
                    <li><i class="fa-solid fa-circle-check"></i><div><strong class="text-white">Liens handled.</strong> Still owe money? I pay out the lender and handle the paperwork.</div></li>
                </ul>
            </div>
            <div class="col-lg-6">
                <div class="split-media reveal"><img src="<?= asset('assets/img/hero/inventory.jpg') ?>" alt="Handing over keys" loading="lazy" style="object-position: 60% 30%"></div>
            </div>
        </div>
    </div>
</section>

<section class="section section-ink" id="appraisal">
    <div class="container">
        <div class="row g-5">
            <div class="col-lg-4">
                <span class="section-kicker">Free appraisal</span>
                <h2 class="section-title">Tell me about your vehicle.</h2>
                <p class="section-lead">Fill in what you know. If you can, text a few photos and the VIN to <a href="<?= e(phone_href(setting('phone'))) ?>"><?= e(setting('phone')) ?></a> afterwards and I'll sharpen the number.</p>
                <div class="process-steps mt-4" style="grid-template-columns:1fr">
                    <div class="process-step"><h3>Submit details</h3><p>Two minutes, no visit required.</p></div>
                    <div class="process-step"><h3>Firm offer within 24h</h3><p>Valid for 7 days or 500 km.</p></div>
                    <div class="process-step"><h3>Quick inspection &amp; payment</h3><p>At my showroom or your driveway. Paid by bank draft or e-transfer.</p></div>
                </div>
            </div>
            <div class="col-lg-8">
                <?= flash_render() ?>
                <form method="post" action="<?= url('trade-in') ?>#appraisal" class="form-card needs-validation" novalidate>
                    <?= form_guard_fields() ?>
                    <?php if (!empty($errors['form'])): ?><div class="alert alert-danger"><?= e($errors['form']) ?></div><?php endif; ?>
                    <h3>Vehicle appraisal request</h3>
                    <p class="form-intro">Fields marked * are required.</p>
                    <div class="row g-3">
                        <div class="col-md-3"><label class="form-label" for="t-year">Year *</label><input type="text" inputmode="numeric" class="form-control<?= isset($errors['year']) ? ' is-invalid' : '' ?>" id="t-year" name="year" value="<?= e(old('year')) ?>" placeholder="2019" required pattern="(19|20)[0-9]{2}"><div class="invalid-feedback">Enter a 4-digit year.</div></div>
                        <div class="col-md-3"><label class="form-label" for="t-make">Make *</label><input type="text" class="form-control<?= isset($errors['make']) ? ' is-invalid' : '' ?>" id="t-make" name="make" value="<?= e(old('make')) ?>" placeholder="Honda" required><div class="invalid-feedback">Required.</div></div>
                        <div class="col-md-3"><label class="form-label" for="t-model">Model *</label><input type="text" class="form-control<?= isset($errors['model']) ? ' is-invalid' : '' ?>" id="t-model" name="model" value="<?= e(old('model')) ?>" placeholder="CR-V" required><div class="invalid-feedback">Required.</div></div>
                        <div class="col-md-3"><label class="form-label" for="t-trim">Trim</label><input type="text" class="form-control" id="t-trim" name="trim" value="<?= e(old('trim')) ?>" placeholder="Touring"></div>
                        <div class="col-md-4"><label class="form-label" for="t-km">Kilometres *</label><input type="text" inputmode="numeric" class="form-control<?= isset($errors['mileage']) ? ' is-invalid' : '' ?>" id="t-km" name="mileage" value="<?= e(old('mileage')) ?>" placeholder="85000" required><div class="invalid-feedback">Enter the kilometres.</div></div>
                        <div class="col-md-4"><label class="form-label" for="t-cond">Condition</label>
                            <select class="form-select" id="t-cond" name="condition">
                                <?php foreach (['Excellent — like new', 'Good — minor wear', 'Fair — some cosmetic or mechanical issues', 'Needs work'] as $c): ?><option<?= old('condition') === $c ? ' selected' : '' ?>><?= $c ?></option><?php endforeach; ?>
                            </select></div>
                        <div class="col-md-4"><label class="form-label" for="t-owed">Still owe money?</label>
                            <select class="form-select" id="t-owed" name="owed">
                                <?php foreach (['No — owned outright', 'Yes — financed', 'Yes — leased'] as $c): ?><option<?= old('owed') === $c ? ' selected' : '' ?>><?= $c ?></option><?php endforeach; ?>
                            </select></div>
                        <div class="col-md-6"><label class="form-label" for="t-name">Full name *</label><input type="text" class="form-control<?= isset($errors['name']) ? ' is-invalid' : '' ?>" id="t-name" name="name" value="<?= e(old('name')) ?>" required><div class="invalid-feedback">Please enter your name.</div></div>
                        <div class="col-md-6"><label class="form-label" for="t-email">Email *</label><input type="email" class="form-control<?= isset($errors['email']) ? ' is-invalid' : '' ?>" id="t-email" name="email" value="<?= e(old('email')) ?>" required><div class="invalid-feedback">Please enter a valid email.</div></div>
                        <div class="col-md-6"><label class="form-label" for="t-phone">Phone *</label><input type="tel" class="form-control<?= isset($errors['phone']) ? ' is-invalid' : '' ?>" id="t-phone" name="phone" value="<?= e(old('phone')) ?>" required><div class="invalid-feedback">Please enter your phone number.</div></div>
                        <div class="col-md-6"><label class="form-label" for="t-intent">I'd like to</label>
                            <select class="form-select" id="t-intent" name="intent">
                                <?php foreach (['Trade it toward another vehicle', 'Sell it outright', 'Not sure yet'] as $c): ?><option<?= old('intent') === $c ? ' selected' : '' ?>><?= $c ?></option><?php endforeach; ?>
                            </select></div>
                        <div class="col-12"><label class="form-label" for="t-msg">Notes (options, accidents, recent service, anything relevant)</label><textarea class="form-control" id="t-msg" name="message" rows="3"><?= e(old('message')) ?></textarea></div>
                        <div class="col-12"><button type="submit" class="btn btn-lah btn-lg w-100"><i class="fa-solid fa-calculator me-2"></i>Get my offer</button></div>
                    </div>
                    <p class="form-text mt-3 mb-0">No obligation. <a href="<?= url('privacy') ?>">Privacy policy</a>.</p>
                </form>
            </div>
        </div>
    </div>
</section>

<?php require __DIR__ . '/includes/cta-band.php'; ?>
<?php require __DIR__ . '/includes/footer.php'; ?>
