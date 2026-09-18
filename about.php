<?php
require __DIR__ . '/includes/app.php';

$pageTitle       = 'About ' . agent_name();
$metaDescription = agent_name() . ', ' . setting('agent_title') . ' at ' . site_name() . '. ' . excerpt(setting('agent_bio_short'), 140);
$reviews         = testimonials(2);
$hero = [
    'image'  => 'assets/img/hero/about.jpg',
    'size'   => 'md',
    'kicker' => 'About ' . site_name(),
    'title'  => e(setting('hero_about_title', 'A better way to buy a car')),
    'text'   => setting('hero_about_text'),
    'crumbs' => ['About' => null],
    'focus'  => '62% 30%',
];
require __DIR__ . '/includes/header.php';
require __DIR__ . '/includes/hero.php';
?>

<section class="section section-dark">
    <div class="container">
        <div class="row g-5 align-items-center">
            <div class="col-lg-5">
                <div class="about-portrait reveal">
                    <img src="<?= asset('assets/img/agent.jpg') ?>" alt="<?= e(agent_name()) ?>" width="900" height="900">
                    <div class="about-card"><i class="fa-solid fa-key"></i><div><strong><?= e(setting('cars_sold', '1,200')) ?>+</strong><span>Vehicles delivered</span></div></div>
                </div>
            </div>
            <div class="col-lg-7 ps-lg-5">
                <span class="section-kicker">My story</span>
                <h2 class="section-title"><?= e(agent_name()) ?></h2>
                <p class="text-red fw-semibold mb-3" style="letter-spacing:.08em;text-transform:uppercase;font-size:.8rem"><?= e(setting('agent_title')) ?> · OMVIC Registered</p>
                <div class="prose"><?= nl2p(setting('agent_bio')) ?></div>
                <div class="signature"><?= e(agent_name()) ?><small>Founder, <?= e(site_name()) ?></small></div>
                <div class="stat-inline">
                    <div><strong><?= e(setting('years_experience', '9')) ?>+</strong><span>Years experience</span></div>
                    <div><strong><?= e(setting('google_rating', '4.9')) ?></strong><span>Google rating</span></div>
                    <div><strong>100%</strong><span>Inspected inventory</span></div>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="section section-ink">
    <div class="container">
        <div class="section-head text-center">
            <span class="section-kicker">What I stand for</span>
            <h2 class="section-title">Three promises on every deal.</h2>
        </div>
        <div class="feature-grid">
            <div class="feature-card reveal"><span class="feature-icon"><i class="fa-solid fa-eye"></i></span><h3>Full transparency</h3><p>Inspection report, CARFAX, my cost basis if you ask. You'll never find out something about the car after you've bought it.</p></div>
            <div class="feature-card reveal reveal-delay-1"><span class="feature-icon"><i class="fa-solid fa-hand"></i></span><h3>Zero pressure</h3><p>No "today only" prices, no pushing add-ons in a back office. If the car isn't right for you, I'll tell you and keep looking.</p></div>
            <div class="feature-card reveal reveal-delay-2"><span class="feature-icon"><i class="fa-solid fa-handshake"></i></span><h3>Relationships over transactions</h3><p>Most of my business is repeat clients and their referrals. I'm the person you text when the check-engine light comes on two years later.</p></div>
        </div>
    </div>
</section>

<section class="section section-dark">
    <div class="container">
        <div class="row g-5 align-items-center flex-lg-row-reverse">
            <div class="col-lg-6">
                <div class="split-media reveal"><img src="<?= asset('assets/img/hero/home-day.jpg') ?>" alt="<?= e(agent_first_name()) ?> in the showroom" loading="lazy"></div>
            </div>
            <div class="col-lg-6">
                <span class="section-kicker">The process</span>
                <h2 class="section-title">What working with me looks like.</h2>
                <ul class="check-list mt-4">
                    <li><i class="fa-solid fa-circle-check"></i><div><strong class="text-white">A real conversation first.</strong> Budget, lifestyle, commute, family — the right car depends on how you actually live.</div></li>
                    <li><i class="fa-solid fa-circle-check"></i><div><strong class="text-white">Curated options, not a lot walk.</strong> I bring you two or three vehicles that fit, with the numbers already worked out.</div></li>
                    <li><i class="fa-solid fa-circle-check"></i><div><strong class="text-white">Independent verification.</strong> Take the car to your own mechanic. I'll arrange it and cover the inspection fee.</div></li>
                    <li><i class="fa-solid fa-circle-check"></i><div><strong class="text-white">Financing that fits.</strong> Multiple lender offers presented side by side, with the total cost of borrowing spelled out.</div></li>
                    <li><i class="fa-solid fa-circle-check"></i><div><strong class="text-white">Delivery and follow-up.</strong> Detailed, fuelled and delivered. I check in at one week, one month and one year.</div></li>
                </ul>
                <a href="<?= url('contact') ?>" class="btn btn-lah mt-4">Start the conversation</a>
            </div>
        </div>
    </div>
</section>

<section class="section section-ink">
    <div class="container">
        <div class="section-head text-center">
            <span class="section-kicker">Credentials</span>
            <h2 class="section-title">Licensed, registered, accountable.</h2>
        </div>
        <div class="row g-4 justify-content-center">
            <div class="col-md-4"><div class="info-card h-100 text-center"><i class="fa-solid fa-certificate text-red fa-2x mb-3"></i><h3 class="justify-content-center">OMVIC registered</h3><p class="mb-0 text-silver small">Every sale is compliant with the Ontario Motor Vehicle Dealers Act and covered by the compensation fund.</p></div></div>
            <div class="col-md-4"><div class="info-card h-100 text-center"><i class="fa-solid fa-shield-halved text-red fa-2x mb-3"></i><h3 class="justify-content-center">Licensed technician partner</h3><p class="mb-0 text-silver small">Independent Safety Standards Certificate on every vehicle before it is listed.</p></div></div>
            <div class="col-md-4"><div class="info-card h-100 text-center"><i class="fa-solid fa-building-columns text-red fa-2x mb-3"></i><h3 class="justify-content-center">Bank &amp; lender network</h3><p class="mb-0 text-silver small">Direct relationships with the major Canadian banks, credit unions and specialty lenders.</p></div></div>
        </div>
    </div>
</section>

<?php if ($reviews): ?>
<section class="section section-paper">
    <div class="container">
        <div class="row g-4 align-items-center">
            <div class="col-lg-4">
                <span class="section-kicker">In their words</span>
                <h2 class="section-title">Clients who became friends.</h2>
                <a href="<?= url('testimonials') ?>" class="btn btn-paper">Read all reviews</a>
            </div>
            <?php foreach ($reviews as $t): ?>
                <div class="col-lg-4 col-md-6">
                    <div class="testimonial-card">
                        <?= stars($t['rating']) ?>
                        <p class="testimonial-quote mt-3">"<?= e(excerpt($t['quote'], 220)) ?>"</p>
                        <div class="testimonial-meta"><span class="testimonial-avatar"><?= e(initials($t['name'])) ?></span><div><strong><?= e($t['name']) ?></strong><span><?= e($t['location']) ?></span></div></div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<?php require __DIR__ . '/includes/cta-band.php'; ?>
<?php require __DIR__ . '/includes/footer.php'; ?>
