<?php
require __DIR__ . '/includes/app.php';

$pageTitle       = 'Services';
$metaDescription = 'Vehicle sales, custom sourcing, financing, leasing, trade-ins and delivery across Toronto and the GTA from ' . site_name() . '.';
$hero = [
    'image'  => 'assets/img/hero/services.jpg',
    'size'   => 'md',
    'kicker' => 'Everything under one roof',
    'title'  => 'Buy, sell, finance — with one person who has your back.',
    'text'   => 'From the first search to the keys in your hand, every step is handled personally.',
    'crumbs' => ['Services' => null],
    'focus'  => '55% 25%',
];
require __DIR__ . '/includes/header.php';
require __DIR__ . '/includes/hero.php';

$services = [
    ['id' => 'buy',      'icon' => 'fa-car',                   'title' => 'Buy a vehicle',          'lead' => 'Hand-picked pre-owned inventory, inspected and priced against the live market.',
        'text' => 'Every vehicle in my inventory has been through a licensed technician\'s inspection and comes with a Safety Standards Certificate and a full CARFAX. Prices are set against real-time market data and there are no admin, documentation or "market adjustment" fees — the listed price plus HST and licensing is what you pay.',
        'points' => ['Safety certified and CARFAX verified', 'Bring your own mechanic — encouraged', 'Extended warranty options explained plainly', 'Test drives at my showroom or your door'], 'cta' => ['Browse inventory', 'inventory']],
    ['id' => 'source',   'icon' => 'fa-crosshairs',            'title' => 'Custom vehicle sourcing', 'lead' => 'Tell me the exact spec. I find it, inspect it and bring it to you.',
        'text' => 'Not seeing the right car? Most of my sales start as a search request. I have access to dealer-only auctions, off-lease and fleet returns, and a network of independent dealers across Ontario and Quebec. You approve the vehicle and the price before I commit to anything, and my fee is built into a total you agree on up front.',
        'points' => ['Dealer auctions and off-lease returns', 'Specific colours, trims and packages', 'Rare and enthusiast vehicles', 'Typical turnaround one to three weeks'], 'cta' => ['Request a vehicle', 'contact?subject=sourcing']],
    ['id' => 'finance',  'icon' => 'fa-hand-holding-dollar',   'title' => 'Financing',               'lead' => 'Rates from bank prime. Options for every credit situation.',
        'text' => 'I work directly with the major Canadian banks, credit unions and specialty lenders. You see multiple offers side by side with the total cost of borrowing spelled out — not just a payment. A soft-check pre-approval takes two minutes and does not affect your credit score.',
        'points' => ['Prime, near-prime and rebuilding credit', 'New to Canada and self-employed programs', 'Terms from 24 to 96 months, open loans', 'No hidden lender or admin fees'], 'cta' => ['Get pre-approved', 'financing']],
    ['id' => 'lease',    'icon' => 'fa-file-signature',        'title' => 'Leasing',                 'lead' => 'Lower payments and a new car every few years, without the dealership run-around.',
        'text' => 'Leasing can make sense for newer vehicles and business use. I\'ll walk you through residuals, kilometre allowances and end-of-term options honestly, and compare a lease against financing on the same car so you can choose with the full picture.',
        'points' => ['Personal and business leases', 'Lease takeovers and buy-outs', 'Clear kilometre and wear guidance', 'Side-by-side comparison with financing'], 'cta' => ['Ask about leasing', 'contact?subject=leasing']],
    ['id' => 'trade',    'icon' => 'fa-arrow-right-arrow-left','title' => 'Trade-in & sell',         'lead' => 'A firm number within 24 hours — and I pay more because I retail what I buy.',
        'text' => 'Franchise dealers wholesale most trade-ins to auction and price them accordingly. I sell the vehicles I take in, so I can pay closer to retail. Send a few photos and details for a firm offer, trade it against your next car, or sell it to me outright. I handle the lien payout and paperwork.',
        'points' => ['Firm offer within 24 hours', 'Lien payouts handled', 'Trade or sell outright', 'HST savings when you trade'], 'cta' => ['Value my vehicle', 'trade-in']],
    ['id' => 'delivery', 'icon' => 'fa-truck-fast',            'title' => 'Delivery & after-care',   'lead' => 'Detailed, fuelled and delivered to your door. Then I stay in touch.',
        'text' => 'Complimentary delivery anywhere in the GTA; at cost elsewhere in Ontario, and I have shipped vehicles to buyers coast to coast. Every car is professionally detailed with a full tank. After the sale I check in at one week, one month and one year — and I\'m the person you call when anything comes up.',
        'points' => ['Free GTA delivery', 'Province-wide and cross-country shipping', 'Professional detail and full tank', 'One-week, one-month, one-year follow-ups'], 'cta' => ['Get in touch', 'contact']],
];
?>

<section class="section section-dark">
    <div class="container">
        <div class="feature-grid mb-5">
            <?php foreach ($services as $i => $s): ?>
                <a href="#<?= $s['id'] ?>" class="feature-card reveal reveal-delay-<?= ($i % 3) + 1 ?>"><span class="feature-icon"><i class="fa-solid <?= $s['icon'] ?>"></i></span><h3><?= e($s['title']) ?></h3><p><?= e($s['lead']) ?></p><span class="feature-link">Learn more <i class="fa-solid fa-arrow-down"></i></span></a>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<?php foreach ($services as $i => $s): ?>
<section class="section <?= $i % 2 ? 'section-dark' : 'section-ink' ?>" id="<?= $s['id'] ?>">
    <div class="container">
        <div class="row g-5 align-items-center <?= $i % 2 ? 'flex-lg-row-reverse' : '' ?>">
            <div class="col-lg-5">
                <div class="split-media reveal"><img src="<?= asset('assets/img/hero/' . ['services.jpg', 'showroom.jpg', 'home-day.jpg', 'inventory.jpg', 'about.jpg', 'home.jpg'][$i % 6]) ?>" alt="" loading="lazy" style="object-position: 65% 30%"></div>
            </div>
            <div class="col-lg-7">
                <span class="section-kicker"><?= e($s['title']) ?></span>
                <h2 class="section-title"><?= e($s['lead']) ?></h2>
                <p><?= e($s['text']) ?></p>
                <ul class="check-list two-col mt-4 mb-4">
                    <?php foreach ($s['points'] as $p): ?><li><i class="fa-solid fa-circle-check"></i><?= e($p) ?></li><?php endforeach; ?>
                </ul>
                <a href="<?= url($s['cta'][1]) ?>" class="btn btn-lah"><?= e($s['cta'][0]) ?></a>
            </div>
        </div>
    </div>
</section>
<?php endforeach; ?>

<?php require __DIR__ . '/includes/cta-band.php'; ?>
<?php require __DIR__ . '/includes/footer.php'; ?>
