<?php
require __DIR__ . '/includes/app.php';

$pageTitle       = site_name();
$metaDescription = setting('meta_description');
$featured        = featured_vehicles(6);
$reviews         = testimonials(3);
$facets          = inventory_facets();
$hero            = [
    'image'   => 'assets/img/hero/home.jpg',
    'focus'   => '76% 0%',
    'size'    => 'lg',
    'kicker'  => setting('hero_home_kicker'),
    'title'   => preg_replace('/\b(No games\.?)$/i', '<em>$1</em>', e(setting('hero_home_title'))),
    'text'    => setting('hero_home_text'),
    'buttons' => [
        ['label' => '<i class="fa-solid fa-car me-2"></i>Browse Inventory', 'url' => url('inventory')],
        ['label' => 'Book a Test Drive', 'url' => url('contact?subject=test-drive'), 'style' => 'outline'],
    ],
];
require __DIR__ . '/includes/header.php';
require __DIR__ . '/includes/hero.php';
?>

<div class="container hero-stats">
    <div class="hero-stats-inner">
        <div class="hero-stat"><i class="fa-solid fa-award"></i><div><strong data-count="<?= e(setting('years_experience', '9')) ?>" data-suffix="+"><?= e(setting('years_experience', '9')) ?>+</strong><span>Years in the business</span></div></div>
        <div class="hero-stat"><i class="fa-solid fa-key"></i><div><strong data-count="<?= (int) str_replace(',', '', setting('cars_sold', '1200')) ?>" data-suffix="+"><?= e(setting('cars_sold', '1,200')) ?>+</strong><span>Keys handed over</span></div></div>
        <div class="hero-stat"><i class="fa-solid fa-star"></i><div><strong data-count="<?= e(setting('google_rating', '4.9')) ?>"><?= e(setting('google_rating', '4.9')) ?></strong><span>Google rating</span></div></div>
        <div class="hero-stat"><i class="fa-solid fa-shield-halved"></i><div><strong>$0</strong><span>Admin or hidden fees</span></div></div>
    </div>
</div>

<section class="section section-dark" id="featured">
    <div class="container">
        <div class="section-head-row">
            <div class="section-head">
                <span class="section-kicker">Hand-picked inventory</span>
                <h2 class="section-title">Featured vehicles</h2>
                <p class="section-lead">Every vehicle is inspected by a licensed technician, priced against the live market and comes with a full history report. <?= (int) $facets['count'] ?> vehicles currently available.</p>
            </div>
            <a href="<?= url('inventory') ?>" class="btn btn-outline-light">View all inventory <i class="fa-solid fa-arrow-right ms-2"></i></a>
        </div>
        <?php if ($featured): ?>
            <div class="vehicle-grid">
                <?php foreach ($featured as $i => $v): ?>
                    <div class="reveal reveal-delay-<?= ($i % 3) + 1 ?>"><?php require __DIR__ . '/includes/vehicle-card.php'; ?></div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="empty-state"><i class="fa-solid fa-car"></i><h3>New inventory arriving</h3><p class="text-silver mb-0">Check back soon or tell me what you're looking for.</p></div>
        <?php endif; ?>
    </div>
</section>

<section class="section section-ink" id="why">
    <div class="container">
        <div class="section-head text-center">
            <span class="section-kicker">Why buy with <?= e(agent_first_name()) ?></span>
            <h2 class="section-title">A dealership experience, without the dealership.</h2>
            <p class="section-lead">One consultant, start to finish. No hand-offs to a finance office, no "let me check with my manager".</p>
        </div>
        <div class="feature-grid">
            <div class="feature-card reveal"><span class="feature-icon"><i class="fa-solid fa-magnifying-glass"></i></span><h3>Personally inspected</h3><p>I go through every car with a licensed technician before it is listed. Safety certified, CARFAX verified, nothing hidden.</p></div>
            <div class="feature-card reveal reveal-delay-1"><span class="feature-icon"><i class="fa-solid fa-tag"></i></span><h3>Transparent pricing</h3><p>Market-based prices you can check yourself. The number on the listing plus HST and licensing is the number you pay.</p></div>
            <div class="feature-card reveal reveal-delay-2"><span class="feature-icon"><i class="fa-solid fa-hand-holding-dollar"></i></span><h3>Financing for everyone</h3><p>Prime, near-prime or rebuilding — I work with every major lender in Canada and tell you the real rate up front.</p><a href="<?= url('financing') ?>" class="feature-link">Get pre-approved <i class="fa-solid fa-arrow-right"></i></a></div>
            <div class="feature-card reveal"><span class="feature-icon"><i class="fa-solid fa-crosshairs"></i></span><h3>Custom sourcing</h3><p>Don't see it here? Tell me the exact spec and I'll find it through dealer auctions, off-lease returns and my private network.</p><a href="<?= url('services#source') ?>" class="feature-link">How sourcing works <i class="fa-solid fa-arrow-right"></i></a></div>
            <div class="feature-card reveal reveal-delay-1"><span class="feature-icon"><i class="fa-solid fa-arrow-right-arrow-left"></i></span><h3>Top-dollar trade-ins</h3><p>Because I retail what I buy instead of sending it to auction, I can pay more for your current car. Firm number within 24 hours.</p><a href="<?= url('trade-in') ?>" class="feature-link">Value my vehicle <i class="fa-solid fa-arrow-right"></i></a></div>
            <div class="feature-card reveal reveal-delay-2"><span class="feature-icon"><i class="fa-solid fa-truck-fast"></i></span><h3>Delivered to your door</h3><p>Complimentary delivery anywhere in the GTA, detailed and with a full tank. Paperwork can be signed at your kitchen table.</p></div>
        </div>
    </div>
</section>

<section class="section section-dark" id="meet">
    <div class="container">
        <div class="row align-items-center g-5">
            <div class="col-lg-5">
                <div class="about-portrait reveal">
                    <img src="<?= asset('assets/img/agent.jpg') ?>" alt="<?= e(agent_name()) ?>, <?= e(setting('agent_title')) ?>" width="900" height="900">
                    <div class="about-card"><i class="fa-solid fa-certificate"></i><div><strong>OMVIC</strong><span>Registered</span></div></div>
                </div>
            </div>
            <div class="col-lg-7 ps-lg-5">
                <span class="section-kicker">Meet your consultant</span>
                <h2 class="section-title">Hi, I'm <?= e(agent_first_name()) ?>.</h2>
                <p class="lead-lg"><?= e(setting('agent_bio_short')) ?></p>
                <ul class="check-list two-col mt-4">
                    <li><i class="fa-solid fa-circle-check"></i>OMVIC-registered, fully licensed</li>
                    <li><i class="fa-solid fa-circle-check"></i><?= e(setting('years_experience', '9')) ?>+ years in automotive sales</li>
                    <li><i class="fa-solid fa-circle-check"></i>Every vehicle personally inspected</li>
                    <li><i class="fa-solid fa-circle-check"></i>Your call, text or email answered same day</li>
                </ul>
                <div class="d-flex flex-wrap gap-3 mt-4">
                    <a href="<?= url('about') ?>" class="btn btn-lah">My story</a>
                    <a href="<?= e(phone_href(setting('phone'))) ?>" class="btn btn-ghost"><i class="fa-solid fa-phone me-2"></i><?= e(setting('phone')) ?></a>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="section section-ink" id="process">
    <div class="container">
        <div class="section-head text-center">
            <span class="section-kicker">How it works</span>
            <h2 class="section-title">From first message to keys in hand.</h2>
        </div>
        <div class="process-steps">
            <div class="process-step reveal"><h3>Tell me what you need</h3><p>Budget, must-haves, timeline. A five-minute call or a text is enough to get started.</p></div>
            <div class="process-step reveal reveal-delay-1"><h3>I do the searching</h3><p>Pick from my inventory or let me source the exact spec. I only bring you cars I would buy myself.</p></div>
            <div class="process-step reveal reveal-delay-2"><h3>Inspect &amp; drive</h3><p>Full inspection report and history up front. Take it to your own mechanic — I encourage it.</p></div>
            <div class="process-step reveal reveal-delay-3"><h3>Sign &amp; drive</h3><p>Financing arranged, plates transferred, car detailed and delivered. No surprises at signing.</p></div>
        </div>
    </div>
</section>

<?php if ($reviews): ?>
<section class="section section-paper" id="reviews">
    <div class="container">
        <div class="section-head-row">
            <div class="section-head">
                <span class="section-kicker">Client stories</span>
                <h2 class="section-title">What drivers say</h2>
            </div>
            <div class="d-flex align-items-center gap-3 flex-wrap">
                <div class="rating-summary" style="background:#fff;border-color:#e3e4e9"><i class="fa-brands fa-google fa-lg" style="color:#4285f4"></i><div><strong style="color:#121216"><?= e(setting('google_rating', '4.9')) ?></strong> <span style="color:#5b5e66">/ 5 on Google</span></div></div>
                <a href="<?= url('testimonials') ?>" class="btn btn-paper">All reviews</a>
            </div>
        </div>
        <div class="row g-4">
            <?php foreach ($reviews as $i => $t): ?>
                <div class="col-md-4 reveal reveal-delay-<?= $i + 1 ?>">
                    <div class="testimonial-card">
                        <?= stars($t['rating']) ?>
                        <p class="testimonial-quote mt-3">"<?= e($t['quote']) ?>"</p>
                        <div class="testimonial-meta">
                            <span class="testimonial-avatar"><?= e(initials($t['name'])) ?></span>
                            <div><strong><?= e($t['name']) ?></strong><span><?= e($t['location']) ?><?= $t['vehicle'] ? ' · ' . e($t['vehicle']) : '' ?></span></div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<?php if ($facets['makes']): ?>
<div class="brand-strip">
    <div class="container">
        <ul class="brand-strip-list">
            <?php foreach (array_keys($facets['makes']) as $make): ?>
                <li><a href="<?= url('inventory?make=' . rawurlencode($make)) ?>"><?= e($make) ?></a></li>
            <?php endforeach; ?>
        </ul>
    </div>
</div>
<?php endif; ?>

<section class="section section-dark" id="finance">
    <div class="container">
        <div class="row g-5 align-items-center">
            <div class="col-lg-6">
                <span class="section-kicker">Financing made simple</span>
                <h2 class="section-title">Know your payment before you fall in love with the car.</h2>
                <p class="section-lead mb-4">Run the numbers on any vehicle in seconds. When you're ready, a two-minute pre-approval gives you a real rate without touching your credit score.</p>
                <ul class="check-list">
                    <li><i class="fa-solid fa-circle-check"></i>Rates from bank prime on newer vehicles</li>
                    <li><i class="fa-solid fa-circle-check"></i>Options for new-to-Canada, self-employed and rebuilding credit</li>
                    <li><i class="fa-solid fa-circle-check"></i>Terms from 24 to 96 months, no pre-payment penalties</li>
                </ul>
                <a href="<?= url('financing') ?>" class="btn btn-lah mt-4">Get pre-approved</a>
            </div>
            <div class="col-lg-6">
                <div class="calc-card reveal" data-calculator>
                    <div class="calc-result"><strong data-calc-payment>$0</strong><span data-calc-freq>bi-weekly</span></div>
                    <div class="row g-3">
                        <div class="col-6"><label class="form-label">Vehicle price</label><input type="number" class="form-control" name="calc_price" value="<?= $featured ? (int) vehicle_price($featured[0]) : 35000 ?>" min="0" step="100"></div>
                        <div class="col-6"><label class="form-label">Down payment</label><input type="number" class="form-control" name="calc_down" value="3000" min="0" step="100"></div>
                        <div class="col-6"><label class="form-label">Rate (APR %)</label><input type="number" class="form-control" name="calc_rate" value="<?= e(setting('finance_rate_default', '7.99')) ?>" min="0" max="30" step="0.01"></div>
                        <div class="col-6"><label class="form-label">Term</label><select class="form-select" name="calc_term"><?php foreach ([36, 48, 60, 72, 84, 96] as $m): ?><option value="<?= $m ?>"<?= $m === (int) setting('finance_term_default', '72') ? ' selected' : '' ?>><?= $m ?> months</option><?php endforeach; ?></select></div>
                        <div class="col-6"><label class="form-label">Frequency</label><select class="form-select" name="calc_freq"><option value="biweekly">Bi-weekly</option><option value="monthly">Monthly</option><option value="weekly">Weekly</option></select></div>
                        <div class="col-6 d-flex align-items-end"><div class="form-check"><input class="form-check-input" type="checkbox" id="calcTax" name="calc_tax" checked><label class="form-check-label small" for="calcTax">Include 13% HST</label></div></div>
                    </div>
                    <p class="form-text mt-3 mb-0">Estimate only. Final rate depends on lender approval, term and credit profile. OAC.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<?php require __DIR__ . '/includes/cta-band.php'; ?>
<?php require __DIR__ . '/includes/footer.php'; ?>
