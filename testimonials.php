<?php
require __DIR__ . '/includes/app.php';

$reviews         = testimonials();
$pageTitle       = 'Client Reviews';
$metaDescription = 'What drivers across Toronto and the GTA say about buying, selling and financing with ' . agent_name() . ' at ' . site_name() . '.';
$hero = [
    'image'  => 'assets/img/hero/about.jpg',
    'size'   => 'sm',
    'kicker' => 'Client stories',
    'title'  => 'Rated ' . e(setting('google_rating', '4.9')) . ' / 5 by the people who matter.',
    'text'   => 'Real clients, real vehicles, real experiences.',
    'crumbs' => ['Testimonials' => null],
    'focus'  => '62% 30%',
];
require __DIR__ . '/includes/header.php';
require __DIR__ . '/includes/hero.php';
?>
<section class="section section-paper">
    <div class="container">
        <div class="section-head-row">
            <div class="section-head"><span class="section-kicker">Reviews</span><h2 class="section-title"><?= count($reviews) ?> stories from the driver's seat</h2></div>
            <div class="rating-summary" style="background:#fff;border-color:#e3e4e9"><i class="fa-brands fa-google fa-lg" style="color:#4285f4"></i><div><strong style="color:#121216"><?= e(setting('google_rating', '4.9')) ?></strong> <span style="color:#5b5e66">/ 5 on Google</span></div></div>
        </div>
        <?php if ($reviews): ?>
            <div class="row g-4">
                <?php foreach ($reviews as $i => $t): ?>
                    <div class="col-lg-4 col-md-6 reveal reveal-delay-<?= ($i % 3) + 1 ?>">
                        <div class="testimonial-card">
                            <?= stars($t['rating']) ?>
                            <p class="testimonial-quote mt-3">"<?= e($t['quote']) ?>"</p>
                            <div class="testimonial-meta"><span class="testimonial-avatar"><?= e(initials($t['name'])) ?></span><div><strong><?= e($t['name']) ?></strong><span><?= e($t['location']) ?><?= $t['vehicle'] ? ' · ' . e($t['vehicle']) : '' ?></span></div></div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <p>Reviews coming soon.</p>
        <?php endif; ?>
    </div>
</section>
<?php require __DIR__ . '/includes/cta-band.php'; ?>
<?php require __DIR__ . '/includes/footer.php'; ?>
