<?php
require __DIR__ . '/includes/app.php';

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_valid()) {
        $errors['form'] = 'Your session expired — please try again.';
    } elseif (($spam = spam_check()) !== '') {
        $errors['form'] = $spam;
    } else {
        $name   = post_str('name', 100);
        $email  = post_str('email', 150);
        $phone  = post_str('phone', 40);
        $budget = post_str('budget', 40);
        $down   = post_str('down_payment', 40);
        $credit = post_str('credit', 40);
        $employ = post_str('employment', 60);
        $income = post_str('income', 40);
        $vehicle = post_str('vehicle', 160);
        $trade  = post_str('trade_in', 10) === 'yes' ? 'Yes' : 'No';
        $notes  = post_str('message', 3000);
        if (mb_strlen($name) < 2) $errors['name'] = 'Please enter your name.';
        if (!valid_email($email)) $errors['email'] = 'Please enter a valid email address.';
        if (!valid_phone($phone)) $errors['phone'] = 'Please enter a valid phone number.';
        if ($budget === '')       $errors['budget'] = 'Choose a budget range.';
        if ($credit === '')       $errors['credit'] = 'Choose the closest option.';
        if (!$errors) {
            lead_create('financing', [
                'name' => $name, 'email' => $email, 'phone' => $phone, 'message' => $notes,
                'details' => array_filter(['budget' => $budget, 'down_payment' => $down, 'credit_rating' => $credit, 'employment' => $employ, 'monthly_income' => $income, 'vehicle_of_interest' => $vehicle, 'has_trade_in' => $trade]),
            ]);
            flash_set('success', "Thanks {$name} — your pre-approval request is in. I'll review it and call you with real numbers, usually the same business day.");
            redirect('financing#apply');
        }
    }
}

$pageTitle       = 'Financing & Pre-Approval';
$metaDescription = 'Auto financing in Toronto and the GTA for every credit situation. Rates from bank prime, terms to 96 months, two-minute pre-approval with no credit impact.';
$hero = [
    'image'  => 'assets/img/hero/showroom.jpg',
    'size'   => 'md',
    'kicker' => 'Financing · Leasing · Pre-approval',
    'title'  => 'Real rates, real numbers — before you shop.',
    'text'   => 'Multiple lender offers side by side, the total cost of borrowing spelled out, and a pre-approval that doesn\'t touch your credit score.',
    'buttons' => [['label' => 'Apply for pre-approval', 'url' => '#apply'], ['label' => 'Payment calculator', 'url' => '#calculator', 'style' => 'outline']],
    'crumbs' => ['Financing' => null],
    'focus'  => '50% 0%',
];
require __DIR__ . '/includes/header.php';
require __DIR__ . '/includes/hero.php';
?>

<section class="section section-dark">
    <div class="container">
        <div class="section-head text-center">
            <span class="section-kicker">How it works</span>
            <h2 class="section-title">Financing without the finance office.</h2>
            <p class="section-lead">I shop your application to lenders that fit your profile, present the offers honestly, and never bury fees in the payment.</p>
        </div>
        <div class="feature-grid">
            <div class="feature-card reveal"><span class="feature-icon"><i class="fa-solid fa-building-columns"></i></span><h3>Prime borrowers</h3><p>Established credit? Rates from bank prime on newer vehicles, with the freedom to pay the loan down early without penalty.</p></div>
            <div class="feature-card reveal reveal-delay-1"><span class="feature-icon"><i class="fa-solid fa-arrow-trend-up"></i></span><h3>Rebuilding credit</h3><p>Past bankruptcy, consumer proposal or missed payments? Specialty lenders with realistic terms — and a plan to refinance to a better rate in 12–18 months.</p></div>
            <div class="feature-card reveal reveal-delay-2"><span class="feature-icon"><i class="fa-solid fa-passport"></i></span><h3>New to Canada &amp; self-employed</h3><p>Programs that accept work permits, PR status and alternative income verification. No Canadian credit history required for some lenders.</p></div>
        </div>
        <div class="process-steps mt-5">
            <div class="process-step reveal"><h3>Two-minute application</h3><p>Basic details, no SIN required for a soft pre-approval.</p></div>
            <div class="process-step reveal reveal-delay-1"><h3>I shop the lenders</h3><p>Your file goes only to lenders that fit — one credit pull, not ten.</p></div>
            <div class="process-step reveal reveal-delay-2"><h3>Offers side by side</h3><p>Rate, term, payment and total cost for each option, explained plainly.</p></div>
            <div class="process-step reveal reveal-delay-3"><h3>Sign &amp; drive</h3><p>Paperwork completed at the showroom or your kitchen table.</p></div>
        </div>
    </div>
</section>

<section class="section section-ink" id="calculator">
    <div class="container">
        <div class="row g-5">
            <div class="col-lg-5">
                <span class="section-kicker">Payment calculator</span>
                <h2 class="section-title">Run the numbers on any budget.</h2>
                <p class="section-lead mb-4">Move the sliders to see how price, down payment, rate and term change your payment. Figures include 13% HST when checked and exclude licensing.</p>
                <div class="info-card">
                    <h3><i class="fa-solid fa-lightbulb"></i>Good to know</h3>
                    <ul class="info-list">
                        <li><i class="fa-solid fa-check"></i>A larger down payment lowers both the payment and the total interest more than a longer term does.</li>
                        <li><i class="fa-solid fa-check"></i>Trading in reduces the HST you pay — tax is charged on the difference.</li>
                        <li><i class="fa-solid fa-check"></i>All loans I arrange are open: pay extra or pay it off early with no penalty.</li>
                    </ul>
                </div>
            </div>
            <div class="col-lg-7">
                <div class="calc-card reveal" data-calculator>
                    <div class="row g-4">
                        <div class="col-md-5">
                            <div class="calc-result"><strong data-calc-payment>$0</strong><span data-calc-freq>bi-weekly</span></div>
                            <ul class="calc-breakdown">
                                <li><span>HST (13%)</span><span data-calc-tax>$0</span></li>
                                <li><span>Amount financed</span><span data-calc-financed>$0</span></li>
                                <li><span>Total interest</span><span data-calc-interest>$0</span></li>
                                <li><span>Total repaid</span><span data-calc-total>$0</span></li>
                            </ul>
                        </div>
                        <div class="col-md-7">
                            <div class="mb-3"><div class="range-row"><label class="form-label mb-0">Vehicle price</label><output data-range-out="calc_price" data-money>$0</output></div><input type="range" class="form-range" name="calc_price" min="5000" max="150000" step="500" value="35000"></div>
                            <div class="mb-3"><div class="range-row"><label class="form-label mb-0">Down payment</label><output data-range-out="calc_down" data-money>$0</output></div><input type="range" class="form-range" name="calc_down" min="0" max="50000" step="500" value="3000"></div>
                            <div class="mb-3"><div class="range-row"><label class="form-label mb-0">Trade-in value</label><output data-range-out="calc_trade" data-money>$0</output></div><input type="range" class="form-range" name="calc_trade" min="0" max="60000" step="500" value="0"></div>
                            <div class="mb-3"><div class="range-row"><label class="form-label mb-0">Interest rate (APR)</label><output data-range-out="calc_rate" data-percent>0%</output></div><input type="range" class="form-range" name="calc_rate" min="0" max="19.99" step="0.01" value="<?= e(setting('finance_rate_default', '7.99')) ?>"></div>
                            <div class="row g-3">
                                <div class="col-6"><label class="form-label">Term</label><select class="form-select" name="calc_term"><?php foreach ([24, 36, 48, 60, 72, 84, 96] as $m): ?><option value="<?= $m ?>"<?= $m === (int) setting('finance_term_default', '72') ? ' selected' : '' ?>><?= $m ?> months</option><?php endforeach; ?></select></div>
                                <div class="col-6"><label class="form-label">Frequency</label><select class="form-select" name="calc_freq"><option value="biweekly">Bi-weekly</option><option value="monthly">Monthly</option><option value="weekly">Weekly</option></select></div>
                            </div>
                            <div class="form-check mt-3"><input class="form-check-input" type="checkbox" id="fTax" name="calc_tax" checked><label class="form-check-label small" for="fTax">Include 13% HST</label></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="section section-dark" id="apply">
    <div class="container">
        <div class="row g-5">
            <div class="col-lg-4">
                <span class="section-kicker">Pre-approval</span>
                <h2 class="section-title">Apply in two minutes.</h2>
                <p class="section-lead">This is a soft inquiry — it does not affect your credit score. I'll review your details personally and call you with options, usually the same business day.</p>
                <ul class="check-list mt-4">
                    <li><i class="fa-solid fa-lock"></i>Your information is never shared beyond the lenders you approve.</li>
                    <li><i class="fa-solid fa-lock"></i>No SIN or documents required at this stage.</li>
                    <li><i class="fa-solid fa-lock"></i>No obligation to buy.</li>
                </ul>
            </div>
            <div class="col-lg-8">
                <?= flash_render() ?>
                <form method="post" action="<?= url('financing') ?>#apply" class="form-card needs-validation" novalidate>
                    <?= form_guard_fields() ?>
                    <?php if (!empty($errors['form'])): ?><div class="alert alert-danger"><?= e($errors['form']) ?></div><?php endif; ?>
                    <h3>Financing pre-approval</h3>
                    <p class="form-intro">Fields marked * are required.</p>
                    <div class="row g-3">
                        <div class="col-md-6"><label class="form-label" for="f-name">Full name *</label><input type="text" class="form-control<?= isset($errors['name']) ? ' is-invalid' : '' ?>" id="f-name" name="name" value="<?= e(old('name')) ?>" required><div class="invalid-feedback"><?= e($errors['name'] ?? 'Please enter your name.') ?></div></div>
                        <div class="col-md-6"><label class="form-label" for="f-email">Email *</label><input type="email" class="form-control<?= isset($errors['email']) ? ' is-invalid' : '' ?>" id="f-email" name="email" value="<?= e(old('email')) ?>" required><div class="invalid-feedback"><?= e($errors['email'] ?? 'Please enter a valid email.') ?></div></div>
                        <div class="col-md-6"><label class="form-label" for="f-phone">Phone *</label><input type="tel" class="form-control<?= isset($errors['phone']) ? ' is-invalid' : '' ?>" id="f-phone" name="phone" value="<?= e(old('phone')) ?>" required><div class="invalid-feedback"><?= e($errors['phone'] ?? 'Please enter your phone number.') ?></div></div>
                        <div class="col-md-6"><label class="form-label" for="f-budget">Vehicle budget *</label>
                            <select class="form-select<?= isset($errors['budget']) ? ' is-invalid' : '' ?>" id="f-budget" name="budget" required>
                                <option value="">Select…</option>
                                <?php foreach (['Under $15,000', '$15,000 – $25,000', '$25,000 – $35,000', '$35,000 – $50,000', '$50,000 – $75,000', 'Over $75,000'] as $b): ?><option<?= old('budget') === $b ? ' selected' : '' ?>><?= $b ?></option><?php endforeach; ?>
                            </select><div class="invalid-feedback">Choose a budget range.</div></div>
                        <div class="col-md-6"><label class="form-label" for="f-down">Down payment</label>
                            <select class="form-select" id="f-down" name="down_payment">
                                <?php foreach (['$0', 'Under $2,500', '$2,500 – $5,000', '$5,000 – $10,000', 'Over $10,000'] as $d): ?><option<?= old('down_payment') === $d ? ' selected' : '' ?>><?= $d ?></option><?php endforeach; ?>
                            </select></div>
                        <div class="col-md-6"><label class="form-label" for="f-credit">Credit rating (your best guess) *</label>
                            <select class="form-select<?= isset($errors['credit']) ? ' is-invalid' : '' ?>" id="f-credit" name="credit" required>
                                <option value="">Select…</option>
                                <?php foreach (['Excellent (750+)', 'Good (680–749)', 'Fair (600–679)', 'Poor (below 600)', 'No credit history / New to Canada', 'Not sure'] as $c): ?><option<?= old('credit') === $c ? ' selected' : '' ?>><?= $c ?></option><?php endforeach; ?>
                            </select><div class="invalid-feedback">Choose the closest option.</div></div>
                        <div class="col-md-6"><label class="form-label" for="f-employ">Employment</label>
                            <select class="form-select" id="f-employ" name="employment">
                                <?php foreach (['Full-time employed', 'Part-time employed', 'Self-employed', 'Retired', 'Student', 'Other'] as $c): ?><option<?= old('employment') === $c ? ' selected' : '' ?>><?= $c ?></option><?php endforeach; ?>
                            </select></div>
                        <div class="col-md-6"><label class="form-label" for="f-income">Monthly income (before tax)</label>
                            <select class="form-select" id="f-income" name="income">
                                <?php foreach (['Prefer not to say', 'Under $2,500', '$2,500 – $4,000', '$4,000 – $6,000', '$6,000 – $9,000', 'Over $9,000'] as $c): ?><option<?= old('income') === $c ? ' selected' : '' ?>><?= $c ?></option><?php endforeach; ?>
                            </select></div>
                        <div class="col-md-8"><label class="form-label" for="f-vehicle">Vehicle of interest</label><input type="text" class="form-control" id="f-vehicle" name="vehicle" value="<?= e(old('vehicle', (string) ($_GET['vehicle'] ?? ''))) ?>" placeholder="e.g. 2022 BMW 330i, or 'compact SUV under $40k'"></div>
                        <div class="col-md-4"><label class="form-label d-block">Trade-in?</label>
                            <div class="btn-group w-100" role="group">
                                <input type="radio" class="btn-check" name="trade_in" id="t-no" value="no" <?= old('trade_in', 'no') === 'no' ? 'checked' : '' ?>><label class="btn btn-ghost" for="t-no">No</label>
                                <input type="radio" class="btn-check" name="trade_in" id="t-yes" value="yes" <?= old('trade_in') === 'yes' ? 'checked' : '' ?>><label class="btn btn-ghost" for="t-yes">Yes</label>
                            </div></div>
                        <div class="col-12"><label class="form-label" for="f-msg">Anything else I should know?</label><textarea class="form-control" id="f-msg" name="message" rows="3"><?= e(old('message')) ?></textarea></div>
                        <div class="col-12"><button type="submit" class="btn btn-lah btn-lg w-100"><i class="fa-solid fa-paper-plane me-2"></i>Submit pre-approval</button></div>
                    </div>
                    <p class="form-text mt-3 mb-0">Soft inquiry only. By submitting you consent to being contacted about financing options. <a href="<?= url('privacy') ?>">Privacy policy</a>.</p>
                </form>
            </div>
        </div>
    </div>
</section>

<?php require __DIR__ . '/includes/cta-band.php'; ?>
<?php require __DIR__ . '/includes/footer.php'; ?>
