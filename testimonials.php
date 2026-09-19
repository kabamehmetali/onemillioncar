<?php
require __DIR__ . '/includes/app.php';

$reviews         = reviews_for_display();
$reviewSummary   = google_review_summary();
$pageTitle       = 'Client Reviews';
$metaDescription = 'What drivers across Toronto and the GTA say about buying, selling and financing with ' . agent_name() . ' at ' . site_name() . '.';
$hero = [
    'image'  => 'assets/img/hero/about.jpg',
    'size'   => 'sm',
    'kicker' => 'Client stories',
    'title'  => 'Rated ' . e($reviewSummary['rating_label']) . ' / 5 by the people who matter.',
    'text'   => 'Real clients, real vehicles, real experiences.',
    'crumbs' => ['Testimonials' => null],
    'focus'  => '62% 0%',
];
require __DIR__ . '/includes/header.php';
require __DIR__ . '/includes/hero.php';
?>
<section class="section section-paper">
    <div class="container">
        <div class="section-head-row">
            <div class="section-head"><span class="section-kicker">Reviews</span><h2 class="section-title"><?= reviews_are_from_google($reviews) ? 'Featured Google reviews' : count($reviews) . ' stories from the driver\'s seat' ?></h2></div>
            <a class="rating-summary" style="background:#fff;border-color:#e3e4e9" href="<?= e($reviewSummary['maps_url']) ?>" target="_blank" rel="noopener"><i class="fa-brands fa-google fa-lg" style="color:#4285f4"></i><div><strong style="color:#121216"><?= e($reviewSummary['rating_label']) ?></strong> <span style="color:#5b5e66">/ 5<?= $reviewSummary['count'] ? ' · ' . number($reviewSummary['count']) . ' reviews' : '' ?></span></div></a>
        </div>
        <?php if (reviews_are_from_google($reviews)): ?><p class="google-review-notice google-review-notice-top"><span class="gmp-attribution" translate="no">Google Maps</span> selects and orders these reviews by relevance. Reviews are not verified by Google, but Google checks for and removes fake content when it is identified. <a href="https://support.google.com/contributionpolicy/answer/7400114" target="_blank" rel="noopener">Learn about the review policy</a>.</p><?php endif; ?>
        <?php if ($reviews): ?>
            <div class="row g-4">
                <?php foreach ($reviews as $i => $t): ?>
                    <div class="col-lg-4 col-md-6 reveal reveal-delay-<?= ($i % 3) + 1 ?>">
                        <?php $reviewExcerpt = 0; require __DIR__ . '/includes/review-card.php'; ?>
                    </div>
                <?php endforeach; ?>
            </div>
            <?php if (reviews_are_from_google($reviews)): ?><div class="text-center mt-4"><a class="btn btn-paper" href="<?= e($reviewSummary['maps_url']) ?>" target="_blank" rel="noopener">View all <?= number($reviewSummary['count']) ?> reviews on <span translate="no">Google Maps</span> <i class="fa-solid fa-arrow-up-right-from-square ms-1" aria-hidden="true"></i></a></div><?php endif; ?>
        <?php else: ?>
            <p>Reviews coming soon.</p>
        <?php endif; ?>
    </div>
</section>
<?php require __DIR__ . '/includes/cta-band.php'; ?>
<?php require __DIR__ . '/includes/footer.php'; ?>
