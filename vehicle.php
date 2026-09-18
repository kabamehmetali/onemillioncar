<?php
require __DIR__ . '/includes/app.php';

$slug = trim((string) ($_GET['slug'] ?? ''));
$v    = $slug !== '' ? vehicle_by_slug($slug) : null;
if (!$v || $v['status'] === 'hidden') {
    http_response_code(404);
    require __DIR__ . '/404.php';
    exit;
}

$errors = [];
$sent   = false;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_valid()) {
        $errors['form'] = 'Your session expired — please try again.';
    } elseif (($spam = spam_check()) !== '') {
        $errors['form'] = $spam;
    } else {
        $name    = post_str('name', 100);
        $email   = post_str('email', 150);
        $phone   = post_str('phone', 40);
        $message = post_str('message', 3000);
        $kind    = post_str('kind', 20) === 'test_drive' ? 'test_drive' : 'inquiry';
        $when    = post_str('preferred_date', 40);
        $contact = post_str('preferred_contact', 20);
        if (mb_strlen($name) < 2)     $errors['name'] = 'Please enter your name.';
        if (!valid_email($email))     $errors['email'] = 'Please enter a valid email address.';
        if ($phone !== '' && !valid_phone($phone)) $errors['phone'] = 'Please enter a valid phone number.';
        if (!$errors) {
            lead_create($kind, [
                'vehicle_id' => (int) $v['id'],
                'name'       => $name,
                'email'      => $email,
                'phone'      => $phone,
                'message'    => $message,
                'details'    => array_filter([
                    'vehicle'           => vehicle_title($v) . ' (' . $v['stock_number'] . ')',
                    'preferred_date'    => $when,
                    'preferred_contact' => $contact,
                ]),
            ]);
            flash_set('success', $kind === 'test_drive'
                ? "Thanks {$name} — your test drive request is in. I'll confirm a time within a few hours."
                : "Thanks {$name} — I've received your message and will get back to you shortly.");
            redirect('inventory/' . $v['slug'] . '#inquire');
        }
    }
}

db_query('UPDATE vehicles SET views = views + 1 WHERE id = ?', [(int) $v['id']]);

$images  = vehicle_images((int) $v['id']);
$gallery = array_map(fn($img) => image_url($img['path']), $images);
if (!$gallery) {
    $gallery = [vehicle_cover($v)];
}
$price   = vehicle_price($v);
$onSale  = vehicle_on_sale($v);
$similar = similar_vehicles($v, 3);
$title   = vehicle_title($v);
$features = lines($v['features']);
$isSold  = $v['status'] === 'sold';

$pageTitle       = $title;
$metaDescription = excerpt($title . ' — ' . number($v['mileage']) . ' km, ' . $v['transmission'] . ', ' . $v['drivetrain'] . ', ' . $v['exterior_color'] . '. ' . money($price) . '. ' . $v['description'], 160);
$ogImage         = ltrim(parse_url($gallery[0], PHP_URL_PATH) ?: '', '/');
$ogImage         = str_starts_with($ogImage, ltrim(app_base(), '/')) ? substr($ogImage, strlen(ltrim(app_base(), '/'))) : $ogImage;
$canonical       = absolute_url('inventory/' . $v['slug']);
require __DIR__ . '/includes/header.php';
?>

<section class="vehicle-hero">
    <div class="container">
        <nav aria-label="breadcrumb" class="hero-crumbs">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="<?= url() ?>">Home</a></li>
                <li class="breadcrumb-item"><a href="<?= url('inventory') ?>">Inventory</a></li>
                <li class="breadcrumb-item"><a href="<?= url('inventory?make=' . rawurlencode($v['make'])) ?>"><?= e($v['make']) ?></a></li>
                <li class="breadcrumb-item active" aria-current="page"><?= e($v['year'] . ' ' . $v['model']) ?></li>
            </ol>
        </nav>
        <?= flash_render() ?>
        <div class="vehicle-layout">
            <div class="vehicle-gallery-col">
                <div class="vehicle-gallery" data-gallery='<?= e(json_encode($gallery, JSON_UNESCAPED_SLASHES)) ?>'>
                    <div class="gallery-main">
                        <img src="<?= e($gallery[0]) ?>" alt="<?= e($title) ?>" width="1600" height="1000" fetchpriority="high">
                        <?php if (count($gallery) > 1): ?>
                            <button class="gallery-nav prev" type="button" aria-label="Previous photo"><i class="fa-solid fa-chevron-left"></i></button>
                            <button class="gallery-nav next" type="button" aria-label="Next photo"><i class="fa-solid fa-chevron-right"></i></button>
                            <span class="gallery-count">1 / <?= count($gallery) ?></span>
                        <?php endif; ?>
                        <?php if ($isSold): ?><div class="vehicle-badges"><span class="vehicle-badge badge-sold">Sold</span></div>
                        <?php elseif ($v['status'] === 'pending'): ?><div class="vehicle-badges"><span class="vehicle-badge badge-pending">Sale Pending</span></div><?php endif; ?>
                    </div>
                    <?php if (count($gallery) > 1): ?>
                        <div class="gallery-thumbs">
                            <?php foreach ($gallery as $i => $src): ?>
                                <button type="button" class="<?= $i === 0 ? 'active' : '' ?>" aria-label="Photo <?= $i + 1 ?>"><img src="<?= e($src) ?>" alt="" loading="lazy"></button>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>

            </div>

            <aside class="vehicle-summary">
                    <span class="vehicle-card-year"><?= e($v['year']) ?> · <?= e(CONDITIONS[$v['condition']] ?? $v['condition']) ?> · Stock <?= e($v['stock_number'] ?: '—') ?></span>
                    <h1><?= e($v['make'] . ' ' . $v['model']) ?></h1>
                    <span class="trim"><?= e($v['trim']) ?></span>
                    <div class="summary-price">
                        <span class="price"><?= money($price) ?></span>
                        <?php if ($onSale): ?><s class="price-old"><?= money($v['price']) ?></s><?php endif; ?>
                        <span class="price-note">est. <strong><?= money(estimate_biweekly($price)) ?></strong> bi-weekly · plus HST &amp; licensing</span>
                    </div>
                    <ul class="summary-facts">
                        <li><i class="fa-solid fa-road"></i><?= number($v['mileage']) ?> km</li>
                        <li><i class="fa-solid fa-gears"></i><?= e($v['transmission']) ?></li>
                        <li><i class="fa-solid fa-circle-nodes"></i><?= e($v['drivetrain']) ?></li>
                        <li><i class="fa-solid fa-gas-pump"></i><?= e($v['fuel_type']) ?></li>
                        <li><i class="fa-solid fa-gauge-high"></i><?= e($v['engine'] ?: 'Engine —') ?></li>
                        <li><i class="fa-solid fa-palette"></i><?= e($v['exterior_color'] ?: '—') ?></li>
                    </ul>
                    <div class="summary-actions">
                        <?php if ($isSold): ?>
                            <a href="<?= url('contact?subject=sourcing') ?>" class="btn btn-lah">Find me one like this</a>
                            <a href="<?= url('inventory') ?>" class="btn btn-ghost">Browse available vehicles</a>
                        <?php else: ?>
                            <a href="#inquire" class="btn btn-lah"><i class="fa-regular fa-calendar-check me-2"></i>Book a test drive</a>
                            <a href="<?= e(phone_href(setting('phone'))) ?>" class="btn btn-outline-light"><i class="fa-solid fa-phone me-2"></i><?= e(setting('phone')) ?></a>
                            <a href="<?= url('trade-in') ?>" class="btn btn-ghost">Value my trade-in</a>
                        <?php endif; ?>
                    </div>
                    <div class="summary-contact">
                        <img src="<?= asset('assets/img/agent.jpg') ?>" alt="<?= e(agent_name()) ?>">
                        <div>
                            <strong><?= e(agent_name()) ?></strong>
                            <span><?= e(setting('agent_title')) ?></span>
                            <a href="mailto:<?= e(setting('email')) ?>?subject=<?= rawurlencode($title . ' (' . $v['stock_number'] . ')') ?>"><?= e(setting('email')) ?></a>
                        </div>
                    </div>
                    <div class="summary-meta">
                        <span><i class="fa-regular fa-eye me-1"></i><?= number($v['views'] + 1) ?> views</span>
                        <div class="share-links">
                            <button type="button" data-copy="<?= e(absolute_url('inventory/' . $v['slug'])) ?>" aria-label="Copy link"><i class="fa-solid fa-link"></i></button>
                            <a href="https://wa.me/?text=<?= rawurlencode($title . ' — ' . absolute_url('inventory/' . $v['slug'])) ?>" target="_blank" rel="noopener" aria-label="Share on WhatsApp"><i class="fa-brands fa-whatsapp"></i></a>
                            <a href="mailto:?subject=<?= rawurlencode($title) ?>&body=<?= rawurlencode('Take a look at this: ' . absolute_url('inventory/' . $v['slug'])) ?>" aria-label="Share by email"><i class="fa-regular fa-envelope"></i></a>
                        </div>
                    </div>
            </aside>

            <div class="vehicle-detail-col">
                <ul class="nav detail-tabs" role="tablist">
                    <li class="nav-item"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#tab-overview" type="button" role="tab">Overview</button></li>
                    <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-specs" type="button" role="tab">Specifications</button></li>
                    <?php if ($features): ?><li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-features" type="button" role="tab">Features</button></li><?php endif; ?>
                    <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-payment" type="button" role="tab">Payment</button></li>
                </ul>
                <div class="tab-content">
                    <div class="tab-pane fade show active" id="tab-overview" role="tabpanel">
                        <div class="prose">
                            <?= nl2p($v['description']) ?: '<p class="text-silver">Full description coming soon — call or message me for details on this vehicle.</p>' ?>
                        </div>
                        <div class="row g-3 mt-2">
                            <div class="col-sm-4"><div class="info-card h-100"><i class="fa-solid fa-clipboard-check text-red fa-lg mb-2"></i><strong class="d-block text-white">Safety certified</strong><span class="small text-silver">Inspected by a licensed technician</span></div></div>
                            <div class="col-sm-4"><div class="info-card h-100"><i class="fa-solid fa-file-lines text-red fa-lg mb-2"></i><strong class="d-block text-white">History report</strong><span class="small text-silver">Full CARFAX provided on request</span></div></div>
                            <div class="col-sm-4"><div class="info-card h-100"><i class="fa-solid fa-truck-fast text-red fa-lg mb-2"></i><strong class="d-block text-white">GTA delivery</strong><span class="small text-silver">Complimentary, detailed, full tank</span></div></div>
                        </div>
                    </div>
                    <div class="tab-pane fade" id="tab-specs" role="tabpanel">
                        <div class="row">
                            <div class="col-md-6">
                                <table class="spec-table">
                                    <tr><th>Year</th><td><?= e($v['year']) ?></td></tr>
                                    <tr><th>Make</th><td><?= e($v['make']) ?></td></tr>
                                    <tr><th>Model</th><td><?= e($v['model']) ?></td></tr>
                                    <tr><th>Trim</th><td><?= e($v['trim'] ?: '—') ?></td></tr>
                                    <tr><th>Body style</th><td><?= e($v['body_type']) ?></td></tr>
                                    <tr><th>Condition</th><td><?= e(CONDITIONS[$v['condition']] ?? $v['condition']) ?></td></tr>
                                    <tr><th>Kilometres</th><td><?= number($v['mileage']) ?> km</td></tr>
                                    <tr><th>Exterior colour</th><td><span class="swatch d-inline-block align-middle me-2" style="background:<?= e($v['color_hex']) ?>"></span><?= e($v['exterior_color'] ?: '—') ?></td></tr>
                                    <tr><th>Interior colour</th><td><?= e($v['interior_color'] ?: '—') ?></td></tr>
                                </table>
                            </div>
                            <div class="col-md-6">
                                <table class="spec-table">
                                    <tr><th>Engine</th><td><?= e($v['engine'] ?: '—') ?></td></tr>
                                    <tr><th>Horsepower</th><td><?= $v['horsepower'] ? e($v['horsepower']) . ' hp' : '—' ?></td></tr>
                                    <tr><th>Transmission</th><td><?= e($v['transmission']) ?></td></tr>
                                    <tr><th>Drivetrain</th><td><?= e($v['drivetrain']) ?></td></tr>
                                    <tr><th>Fuel type</th><td><?= e($v['fuel_type']) ?></td></tr>
                                    <tr><th>Fuel economy</th><td><?= e($v['fuel_economy'] ?: '—') ?></td></tr>
                                    <tr><th>Doors / Seats</th><td><?= e($v['doors']) ?> / <?= e($v['seats']) ?></td></tr>
                                    <tr><th>Stock #</th><td><?= e($v['stock_number'] ?: '—') ?></td></tr>
                                    <tr><th>VIN</th><td class="text-break"><?= e($v['vin'] ?: 'Available on request') ?></td></tr>
                                </table>
                            </div>
                        </div>
                    </div>
                    <?php if ($features): ?>
                    <div class="tab-pane fade" id="tab-features" role="tabpanel">
                        <ul class="feature-list">
                            <?php foreach ($features as $f): ?><li><i class="fa-solid fa-check"></i><?= e($f) ?></li><?php endforeach; ?>
                        </ul>
                    </div>
                    <?php endif; ?>
                    <div class="tab-pane fade" id="tab-payment" role="tabpanel">
                        <div class="calc-card" data-calculator>
                            <div class="row g-4">
                                <div class="col-md-5">
                                    <div class="calc-result"><strong data-calc-payment>$0</strong><span data-calc-freq>bi-weekly</span></div>
                                    <ul class="calc-breakdown">
                                        <li><span>Vehicle price</span><span><?= money($price) ?></span></li>
                                        <li><span>HST (13%)</span><span data-calc-tax>$0</span></li>
                                        <li><span>Amount financed</span><span data-calc-financed>$0</span></li>
                                        <li><span>Total interest</span><span data-calc-interest>$0</span></li>
                                        <li><span>Total cost of borrowing</span><span data-calc-total>$0</span></li>
                                    </ul>
                                </div>
                                <div class="col-md-7">
                                    <input type="hidden" name="calc_price" value="<?= (int) $price ?>">
                                    <div class="mb-3">
                                        <div class="range-row"><label class="form-label mb-0">Down payment</label><output data-range-out="calc_down" data-money>$0</output></div>
                                        <input type="range" class="form-range" name="calc_down" min="0" max="<?= (int) ($price * 0.5) ?>" step="500" value="<?= (int) min(3000, $price * 0.1) ?>">
                                    </div>
                                    <div class="mb-3">
                                        <div class="range-row"><label class="form-label mb-0">Trade-in value</label><output data-range-out="calc_trade" data-money>$0</output></div>
                                        <input type="range" class="form-range" name="calc_trade" min="0" max="<?= (int) ($price * 0.8) ?>" step="500" value="0">
                                    </div>
                                    <div class="mb-3">
                                        <div class="range-row"><label class="form-label mb-0">Interest rate (APR)</label><output data-range-out="calc_rate" data-percent>0%</output></div>
                                        <input type="range" class="form-range" name="calc_rate" min="0" max="19.99" step="0.01" value="<?= e(setting('finance_rate_default', '7.99')) ?>">
                                    </div>
                                    <div class="row g-3">
                                        <div class="col-6">
                                            <label class="form-label">Term</label>
                                            <select class="form-select" name="calc_term"><?php foreach ([24, 36, 48, 60, 72, 84, 96] as $m): ?><option value="<?= $m ?>"<?= $m === (int) setting('finance_term_default', '72') ? ' selected' : '' ?>><?= $m ?> months</option><?php endforeach; ?></select>
                                        </div>
                                        <div class="col-6">
                                            <label class="form-label">Frequency</label>
                                            <select class="form-select" name="calc_freq"><option value="biweekly">Bi-weekly</option><option value="monthly">Monthly</option><option value="weekly">Weekly</option></select>
                                        </div>
                                    </div>
                                    <div class="form-check mt-3"><input class="form-check-input" type="checkbox" id="vTax" name="calc_tax" checked><label class="form-check-label small" for="vTax">Include 13% HST in financed amount</label></div>
                                    <p class="form-text mt-3 mb-0">Estimate only, OAC. Excludes licensing. <a href="<?= url('financing') ?>">Get a real pre-approval →</a></p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</section>

<?php if (!$isSold): ?>
<section class="section section-ink" id="inquire">
    <div class="container">
        <div class="row g-5">
            <div class="col-lg-5">
                <span class="section-kicker">Interested?</span>
                <h2 class="section-title">Ask a question or book a drive.</h2>
                <p class="section-lead">I answer every message personally, usually within the hour during business hours. Prefer to talk? <a href="<?= e(phone_href(setting('phone'))) ?>"><?= e(setting('phone')) ?></a>.</p>
                <ul class="check-list mt-4">
                    <li><i class="fa-solid fa-circle-check"></i>Test drives at my showroom or your location</li>
                    <li><i class="fa-solid fa-circle-check"></i>Evenings and weekends by appointment</li>
                    <li><i class="fa-solid fa-circle-check"></i>Bring your mechanic — or I'll drive it to them</li>
                </ul>
            </div>
            <div class="col-lg-7">
                <form method="post" action="<?= vehicle_url($v) ?>#inquire" class="form-card needs-validation" novalidate>
                    <?= form_guard_fields() ?>
                    <?php if (!empty($errors['form'])): ?><div class="alert alert-danger"><?= e($errors['form']) ?></div><?php endif; ?>
                    <h3>About the <?= e($v['year'] . ' ' . $v['make'] . ' ' . $v['model']) ?></h3>
                    <p class="form-intro">Stock <?= e($v['stock_number']) ?> · <?= money($price) ?></p>
                    <div class="row g-3">
                        <div class="col-12">
                            <div class="btn-group w-100" role="group" aria-label="Request type">
                                <input type="radio" class="btn-check" name="kind" id="kind-inquiry" value="inquiry" <?= old('kind', 'inquiry') !== 'test_drive' ? 'checked' : '' ?>><label class="btn btn-ghost" for="kind-inquiry"><i class="fa-regular fa-comment me-2"></i>Ask a question</label>
                                <input type="radio" class="btn-check" name="kind" id="kind-drive" value="test_drive" <?= old('kind') === 'test_drive' ? 'checked' : '' ?>><label class="btn btn-ghost" for="kind-drive"><i class="fa-regular fa-calendar-check me-2"></i>Book a test drive</label>
                            </div>
                        </div>
                        <div class="col-md-6"><label class="form-label" for="i-name">Full name *</label><input type="text" class="form-control<?= isset($errors['name']) ? ' is-invalid' : '' ?>" id="i-name" name="name" value="<?= e(old('name')) ?>" required><div class="invalid-feedback"><?= e($errors['name'] ?? 'Please enter your name.') ?></div></div>
                        <div class="col-md-6"><label class="form-label" for="i-email">Email *</label><input type="email" class="form-control<?= isset($errors['email']) ? ' is-invalid' : '' ?>" id="i-email" name="email" value="<?= e(old('email')) ?>" required><div class="invalid-feedback"><?= e($errors['email'] ?? 'Please enter a valid email.') ?></div></div>
                        <div class="col-md-6"><label class="form-label" for="i-phone">Phone</label><input type="tel" class="form-control<?= isset($errors['phone']) ? ' is-invalid' : '' ?>" id="i-phone" name="phone" value="<?= e(old('phone')) ?>"><div class="invalid-feedback"><?= e($errors['phone'] ?? '') ?></div></div>
                        <div class="col-md-6"><label class="form-label" for="i-date">Preferred date / time</label><input type="text" class="form-control" id="i-date" name="preferred_date" value="<?= e(old('preferred_date')) ?>" placeholder="e.g. Saturday afternoon"></div>
                        <div class="col-12"><label class="form-label" for="i-msg">Message</label><textarea class="form-control" id="i-msg" name="message" rows="4" placeholder="Anything you'd like to know about this vehicle?"><?= e(old('message')) ?></textarea></div>
                        <div class="col-md-6">
                            <label class="form-label" for="i-contact">Best way to reach you</label>
                            <select class="form-select" id="i-contact" name="preferred_contact">
                                <?php foreach (['Phone', 'Text', 'Email', 'WhatsApp'] as $c): ?><option<?= old('preferred_contact') === $c ? ' selected' : '' ?>><?= $c ?></option><?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6 d-flex align-items-end"><button type="submit" class="btn btn-lah w-100">Send request</button></div>
                    </div>
                    <p class="form-text mt-3 mb-0">By submitting you agree to be contacted about this vehicle. No spam, ever. <a href="<?= url('privacy') ?>">Privacy policy</a>.</p>
                </form>
            </div>
        </div>
    </div>
</section>
<?php endif; ?>

<?php if ($similar): ?>
<section class="section section-dark">
    <div class="container">
        <div class="section-head-row">
            <div class="section-head"><span class="section-kicker">You may also like</span><h2 class="section-title">Similar vehicles</h2></div>
            <a href="<?= url('inventory?body_type=' . rawurlencode($v['body_type'])) ?>" class="btn btn-outline-light">More <?= e($v['body_type']) ?>s <i class="fa-solid fa-arrow-right ms-2"></i></a>
        </div>
        <div class="vehicle-grid">
            <?php foreach ($similar as $v): ?><?php require __DIR__ . '/includes/vehicle-card.php'; ?><?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<div class="lightbox" id="lightbox" role="dialog" aria-modal="true" aria-label="Photo viewer">
    <button type="button" class="lightbox-close" aria-label="Close"><i class="fa-solid fa-xmark"></i></button>
    <button class="gallery-nav prev" type="button" aria-label="Previous photo"><i class="fa-solid fa-chevron-left"></i></button>
    <img src="" alt="">
    <button class="gallery-nav next" type="button" aria-label="Next photo"><i class="fa-solid fa-chevron-right"></i></button>
</div>

<?php
$extraScripts = '<script type="application/ld+json">' . json_encode([
    '@context'        => 'https://schema.org',
    '@type'           => 'Vehicle',
    'name'            => $title,
    'image'           => array_map(fn($p) => absolute_url(ltrim(substr(parse_url($p, PHP_URL_PATH) ?: '', strlen(app_base())), '/')), $gallery),
    'brand'           => ['@type' => 'Brand', 'name' => $v['make']],
    'model'           => $v['model'],
    'vehicleModelDate'=> (string) $v['year'],
    'mileageFromOdometer' => ['@type' => 'QuantitativeValue', 'value' => (int) $v['mileage'], 'unitCode' => 'KMT'],
    'vehicleTransmission' => $v['transmission'],
    'fuelType'        => $v['fuel_type'],
    'color'           => $v['exterior_color'],
    'vehicleIdentificationNumber' => $v['vin'],
    'offers'          => ['@type' => 'Offer', 'price' => $price, 'priceCurrency' => 'CAD', 'availability' => $isSold ? 'https://schema.org/SoldOut' : 'https://schema.org/InStock', 'url' => absolute_url('inventory/' . $v['slug'])],
], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . '</script>';
require __DIR__ . '/includes/footer.php';
