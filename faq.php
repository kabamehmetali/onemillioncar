<?php
require __DIR__ . '/includes/app.php';

$items           = faqs();
$pageTitle       = 'Frequently Asked Questions';
$metaDescription = 'Answers about inspections, financing, trade-ins, fees, delivery and warranties from ' . site_name() . '.';
$hero = [
    'image'  => 'assets/img/hero/showroom.jpg',
    'size'   => 'sm',
    'kicker' => 'Straight answers',
    'title'  => 'Questions people ask before they buy.',
    'text'   => 'If yours isn\'t here, call or message me — I answer everything.',
    'crumbs' => ['FAQ' => null],
    'focus'  => '50% 30%',
];
require __DIR__ . '/includes/header.php';
require __DIR__ . '/includes/hero.php';
?>
<section class="section section-dark">
    <div class="container">
        <div class="row g-5">
            <div class="col-lg-4">
                <span class="section-kicker">FAQ</span>
                <h2 class="section-title">The fine print, without the fine print.</h2>
                <p class="section-lead">Everything about how I work, what's included and what it costs.</p>
                <div class="info-card mt-4">
                    <h3><i class="fa-solid fa-comments"></i>Still have a question?</h3>
                    <p class="small text-silver">Text or call — I usually reply within the hour during business hours.</p>
                    <a href="<?= e(phone_href(setting('phone'))) ?>" class="btn btn-lah btn-sm me-2"><i class="fa-solid fa-phone me-2"></i>Call</a>
                    <a href="<?= url('contact') ?>" class="btn btn-ghost btn-sm">Message</a>
                </div>
            </div>
            <div class="col-lg-8">
                <?php if ($items): ?>
                <div class="accordion accordion-lah" id="faqAccordion">
                    <?php foreach ($items as $i => $f): ?>
                        <div class="accordion-item">
                            <h3 class="accordion-header" id="faq-h-<?= $f['id'] ?>">
                                <button class="accordion-button<?= $i === 0 ? '' : ' collapsed' ?>" type="button" data-bs-toggle="collapse" data-bs-target="#faq-<?= $f['id'] ?>" aria-expanded="<?= $i === 0 ? 'true' : 'false' ?>" aria-controls="faq-<?= $f['id'] ?>"><?= e($f['question']) ?></button>
                            </h3>
                            <div id="faq-<?= $f['id'] ?>" class="accordion-collapse collapse<?= $i === 0 ? ' show' : '' ?>" data-bs-parent="#faqAccordion">
                                <div class="accordion-body"><?= nl2p($f['answer']) ?></div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                <?php else: ?><p>Questions coming soon.</p><?php endif; ?>
            </div>
        </div>
    </div>
</section>
<?php
$extraScripts = $items ? '<script type="application/ld+json">' . json_encode([
    '@context'   => 'https://schema.org',
    '@type'      => 'FAQPage',
    'mainEntity' => array_map(fn($f) => ['@type' => 'Question', 'name' => $f['question'], 'acceptedAnswer' => ['@type' => 'Answer', 'text' => $f['answer']]], $items),
], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . '</script>' : '';
require __DIR__ . '/includes/cta-band.php';
require __DIR__ . '/includes/footer.php';
