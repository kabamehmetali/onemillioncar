<?php
require __DIR__ . '/includes/auth.php';
require_login();

$id   = (int) ($_GET['id'] ?? $_POST['id'] ?? 0);
$row  = $id ? db_one('SELECT * FROM testimonials WHERE id = ?', [$id]) : null;
if ($id && !$row) {
    flash_set('danger', 'Testimonial not found.');
    redirect('admin/testimonials.php');
}
$data   = $row ?: ['name' => '', 'location' => '', 'vehicle' => '', 'rating' => 5, 'quote' => '', 'is_published' => 1, 'sort_order' => 0];
$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_csrf();
    $data = [
        'name'         => post_str('name', 100),
        'location'     => post_str('location', 100),
        'vehicle'      => post_str('vehicle', 120),
        'rating'       => max(1, min(5, (int) ($_POST['rating'] ?? 5))),
        'quote'        => post_str('quote', 3000),
        'is_published' => isset($_POST['is_published']) ? 1 : 0,
        'sort_order'   => (int) ($_POST['sort_order'] ?? 0),
    ];
    if ($data['name'] === '')  $errors['name'] = 'Name is required.';
    if ($data['quote'] === '') $errors['quote'] = 'Quote is required.';
    if (!$errors) {
        if ($row) {
            db_update('testimonials', $data, 'id = ?', [$id]);
        } else {
            db_insert('testimonials', $data);
        }
        flash_set('success', 'Testimonial saved.');
        redirect('admin/testimonials.php');
    }
}
$adminTitle = $row ? 'Edit testimonial' : 'Add testimonial';
require __DIR__ . '/includes/header.php';
?>
<form method="post" class="card" style="max-width:820px">
    <?= csrf_field() ?><input type="hidden" name="id" value="<?= $id ?>">
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-6"><label class="form-label">Client name *</label><input type="text" name="name" class="form-control<?= isset($errors['name']) ? ' is-invalid' : '' ?>" value="<?= e($data['name']) ?>" required><div class="text-muted-sm">Use a first name and initial for privacy, e.g. "Priya S."</div></div>
            <div class="col-md-6"><label class="form-label">Location</label><input type="text" name="location" class="form-control" value="<?= e($data['location']) ?>" placeholder="Mississauga"></div>
            <div class="col-md-6"><label class="form-label">Vehicle purchased</label><input type="text" name="vehicle" class="form-control" value="<?= e($data['vehicle']) ?>" placeholder="2022 Audi Q5"></div>
            <div class="col-md-3"><label class="form-label">Rating</label><select name="rating" class="form-select"><?php for ($r = 5; $r >= 1; $r--): ?><option value="<?= $r ?>"<?= (int) $data['rating'] === $r ? ' selected' : '' ?>><?= $r ?> star<?= $r > 1 ? 's' : '' ?></option><?php endfor; ?></select></div>
            <div class="col-md-3"><label class="form-label">Sort order</label><input type="number" name="sort_order" class="form-control" value="<?= (int) $data['sort_order'] ?>"></div>
            <div class="col-12"><label class="form-label">Quote *</label><textarea name="quote" class="form-control<?= isset($errors['quote']) ? ' is-invalid' : '' ?>" rows="5" required><?= e($data['quote']) ?></textarea></div>
            <div class="col-12"><div class="form-check form-switch"><input class="form-check-input" type="checkbox" name="is_published" id="pub" value="1"<?= $data['is_published'] ? ' checked' : '' ?>><label class="form-check-label" for="pub">Published on the website</label></div></div>
        </div>
    </div>
    <div class="card-footer bg-white d-flex gap-2"><button class="btn btn-primary"><i class="fa-solid fa-floppy-disk me-1"></i>Save</button><a href="<?= admin_url('testimonials.php') ?>" class="btn btn-light">Cancel</a></div>
</form>
<?php require __DIR__ . '/includes/footer.php'; ?>
