<?php /** Closing call to action used on most pages. */ ?>
<section class="cta-band">
    <div class="container">
        <div class="cta-band-inner">
            <div>
                <span class="section-kicker">Ready when you are</span>
                <h2 class="cta-band-title">Let's find your next car.</h2>
                <p class="cta-band-text">Tell me what you're looking for and I'll do the searching, the inspecting and the negotiating.</p>
            </div>
            <div class="cta-band-actions">
                <a href="<?= url('contact') ?>" class="btn btn-lah btn-lg">Get in Touch</a>
                <a href="<?= e(phone_href(setting('phone'))) ?>" class="btn btn-outline-light btn-lg"><i class="fa-solid fa-phone me-2"></i><?= e(setting('phone')) ?></a>
            </div>
        </div>
    </div>
</section>
