<?php
require __DIR__ . '/includes/auth.php';
require_login();

$id  = (int) ($_GET['id'] ?? $_POST['id'] ?? 0);
$row = $id ? db_one('SELECT * FROM faqs WHERE id = ?', [$id]) : null;
if ($id && !$row) {
    flash_set('danger', 'FAQ not found.');
    redirect('admin/faqs.php');
}
$data   = $row ?: ['question' => '', 'answer' => '', 'is_published' => 1, 'sort_order' => (int) db_value('SELECT COALESCE(MAX(sort_order),0)+1 FROM faqs')];
$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_csrf();
    $data = [
        'question'     => post_str('question', 255),
        'answer'       => post_str('answer', 5000),
        'is_published' => isset($_POST['is_published']) ? 1 : 0,
        'sort_order'   => (int) ($_POST['sort_order'] ?? 0),
    ];
    if ($data['question'] === '') $errors['question'] = 'Required.';
    if ($data['answer'] === '')   $errors['answer'] = 'Required.';
    if (!$errors) {
        if ($row) {
            db_update('faqs', $data, 'id = ?', [$id]);
        } else {
            db_insert('faqs', $data);
        }
        flash_set('success', 'FAQ saved.');
        redirect('admin/faqs.php');
    }
}
$adminTitle = $row ? 'Edit question' : 'Add question';
require __DIR__ . '/includes/header.php';
?>
<form method="post" class="card" style="max-width:820px">
    <?= csrf_field() ?><input type="hidden" name="id" value="<?= $id ?>">
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-9"><label class="form-label">Question *</label><input type="text" name="question" class="form-control<?= isset($errors['question']) ? ' is-invalid' : '' ?>" value="<?= e($data['question']) ?>" required></div>
            <div class="col-md-3"><label class="form-label">Sort order</label><input type="number" name="sort_order" class="form-control" value="<?= (int) $data['sort_order'] ?>"></div>
            <div class="col-12"><label class="form-label">Answer *</label><textarea name="answer" class="form-control<?= isset($errors['answer']) ? ' is-invalid' : '' ?>" rows="6" required><?= e($data['answer']) ?></textarea><div class="text-muted-sm">Blank lines start new paragraphs.</div></div>
            <div class="col-12"><div class="form-check form-switch"><input class="form-check-input" type="checkbox" name="is_published" id="pub" value="1"<?= $data['is_published'] ? ' checked' : '' ?>><label class="form-check-label" for="pub">Published on the website</label></div></div>
        </div>
    </div>
    <div class="card-footer bg-white d-flex gap-2"><button class="btn btn-primary"><i class="fa-solid fa-floppy-disk me-1"></i>Save</button><a href="<?= admin_url('faqs.php') ?>" class="btn btn-light">Cancel</a></div>
</form>
<?php require __DIR__ . '/includes/footer.php'; ?>
