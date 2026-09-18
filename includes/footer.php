<?php
/** Shared footer, scripts and page closer. */
$hours = business_hours();
?>
</main>

<footer class="site-footer">
    <div class="container">
        <div class="row g-4 g-lg-5">
            <div class="col-lg-4">
                <a href="<?= url() ?>" class="footer-brand">
                    <img src="<?= asset('assets/img/logo.png') ?>" alt="<?= e(site_name()) ?>" width="150" height="98">
                </a>
                <p class="footer-blurb"><?= e(setting('agent_bio_short')) ?></p>
                <?php if (social_links()): ?>
                    <ul class="social-list">
                        <?php foreach (social_links() as $s): ?>
                            <li><a href="<?= e($s['url']) ?>" target="_blank" rel="noopener" aria-label="<?= e($s['label']) ?>"><i class="<?= e($s['icon']) ?>"></i></a></li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
            <div class="col-6 col-lg-2">
                <h6 class="footer-heading">Explore</h6>
                <ul class="footer-links">
                    <li><a href="<?= url('inventory') ?>">Inventory</a></li>
                    <li><a href="<?= url('inventory?sort=newest') ?>">New Arrivals</a></li>
                    <li><a href="<?= url('about') ?>">About <?= e(agent_first_name()) ?></a></li>
                    <li><a href="<?= url('testimonials') ?>">Testimonials</a></li>
                    <li><a href="<?= url('faq') ?>">FAQ</a></li>
                    <li><a href="<?= url('contact') ?>">Contact</a></li>
                </ul>
            </div>
            <div class="col-6 col-lg-2">
                <h6 class="footer-heading">Services</h6>
                <ul class="footer-links">
                    <li><a href="<?= url('services#buy') ?>">Buy a Vehicle</a></li>
                    <li><a href="<?= url('trade-in') ?>">Sell or Trade-In</a></li>
                    <li><a href="<?= url('financing') ?>">Financing</a></li>
                    <li><a href="<?= url('services#lease') ?>">Leasing</a></li>
                    <li><a href="<?= url('services#source') ?>">Vehicle Sourcing</a></li>
                    <li><a href="<?= url('services#delivery') ?>">Delivery</a></li>
                </ul>
            </div>
            <div class="col-lg-4">
                <h6 class="footer-heading">Visit or Call</h6>
                <ul class="footer-contact">
                    <li><i class="fa-solid fa-location-dot"></i><span><?= e(setting('address_line')) ?><br><?= e(setting('city')) ?></span></li>
                    <li><i class="fa-solid fa-phone"></i><a href="<?= e(phone_href(setting('phone'))) ?>"><?= e(setting('phone')) ?></a></li>
                    <li><i class="fa-solid fa-envelope"></i><a href="mailto:<?= e(setting('email')) ?>"><?= e(setting('email')) ?></a></li>
                </ul>
                <ul class="footer-hours">
                    <?php foreach ($hours as $day => $time): ?>
                        <li><span><?= e($day) ?></span><span><?= e($time) ?></span></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
    </div>
    <div class="footer-bottom">
        <div class="container d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2">
            <p class="mb-0">&copy; <?= date('Y') ?> <?= e(site_name()) ?>. All rights reserved. <a href="<?= url('privacy') ?>">Privacy</a> · <a href="<?= url('admin/login.php') ?>" rel="nofollow">Admin</a></p>
            <p class="mb-0 footer-legal"><?= e(setting('footer_note')) ?></p>
        </div>
    </div>
</footer>

<?php if (setting('whatsapp') !== ''): ?>
    <a href="https://wa.me/<?= e(preg_replace('/\D/', '', setting('whatsapp'))) ?>?text=<?= rawurlencode('Hi ' . agent_first_name() . ', I found you on ' . site_name() . ' and I have a question about a vehicle.') ?>" class="whatsapp-fab" target="_blank" rel="noopener" aria-label="Chat on WhatsApp">
        <i class="fa-brands fa-whatsapp"></i>
    </a>
<?php endif; ?>
<button class="back-to-top" id="backToTop" aria-label="Back to top"><i class="fa-solid fa-chevron-up"></i></button>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
<script src="<?= asset('assets/js/scripts.js') ?>"></script>
<?= $extraScripts ?? '' ?>
</body>
</html>
