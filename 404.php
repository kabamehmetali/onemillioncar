<?php
if (!defined('APP_ROOT')) {
    require __DIR__ . '/includes/app.php';
}
http_response_code(404);
$pageTitle       = 'Page not found';
$metaDescription = 'That page could not be found.';
require __DIR__ . '/includes/header.php';
?>
<section class="page-404 section">
    <div class="container">
        <div class="code">404</div>
        <h1 class="section-title">This road doesn't go anywhere.</h1>
        <p class="section-lead mx-auto" style="max-width:520px">The page you're looking for may have been sold, moved or never existed. Let's get you back on track.</p>
        <div class="d-flex gap-2 justify-content-center flex-wrap mt-4">
            <a href="<?= url() ?>" class="btn btn-lah">Back to home</a>
            <a href="<?= url('inventory') ?>" class="btn btn-outline-light">Browse inventory</a>
        </div>
    </div>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
