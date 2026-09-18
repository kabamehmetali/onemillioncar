<?php
require __DIR__ . '/includes/app.php';
$pageTitle       = 'Privacy Policy';
$metaDescription = 'How ' . site_name() . ' collects, uses and protects your personal information.';
require __DIR__ . '/includes/header.php';
?>
<section class="section section-dark">
    <div class="container" style="max-width:860px">
        <nav aria-label="breadcrumb" class="hero-crumbs"><ol class="breadcrumb"><li class="breadcrumb-item"><a href="<?= url() ?>">Home</a></li><li class="breadcrumb-item active">Privacy</li></ol></nav>
        <span class="section-kicker">Legal</span>
        <h1 class="section-title">Privacy policy</h1>
        <p class="text-silver">Last updated <?= date('F j, Y', filemtime(__FILE__)) ?></p>
        <div class="prose">
            <h2>What I collect</h2>
            <p>When you submit a form on this site — a vehicle inquiry, test drive request, trade-in appraisal, financing pre-approval or general message — I collect the information you enter: your name, email address, phone number and the details of your request. The site also records the date and time of the submission and your IP address to help prevent abuse.</p>
            <h2>How it is used</h2>
            <p>Your information is used only to respond to your request and, where you have asked for it, to arrange financing, appraise a vehicle or schedule a test drive. Financing details are shared only with lenders you approve. I do not sell, rent or trade your information to anyone.</p>
            <h2>Marketing</h2>
            <p>I do not add you to any mailing list automatically. If you would like to hear about new arrivals that match what you are looking for, tell me and I will keep you posted — and stop the moment you ask.</p>
            <h2>Retention and security</h2>
            <p>Inquiries are stored in a password-protected system accessible only to <?= e(site_name()) ?>. Records are kept as long as needed to serve you and to meet legal record-keeping obligations for vehicle sales in Ontario, then deleted.</p>
            <h2>Cookies</h2>
            <p>This site uses a single session cookie to keep forms secure. It contains no personal data and expires when you close your browser. Embedded maps and fonts are loaded from Google, which may set its own cookies under its privacy policy.</p>
            <h2>Your rights</h2>
            <p>You can ask to see, correct or delete the information I hold about you at any time by emailing <a href="mailto:<?= e(setting('email')) ?>"><?= e(setting('email')) ?></a>.</p>
            <h2>Contact</h2>
            <p><?= e(site_name()) ?><br><?= e(setting('address_line')) ?>, <?= e(setting('city')) ?><br><?= e(setting('phone')) ?> · <?= e(setting('email')) ?></p>
        </div>
    </div>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
