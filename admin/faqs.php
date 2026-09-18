<?php
require __DIR__ . '/includes/auth.php';
require_login();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_csrf();
    $id = (int) ($_POST['id'] ?? 0);
    if ($id && ($_POST['action'] ?? '') === 'delete') {
        db_query('DELETE FROM faqs WHERE id = ?', [$id]);
        flash_set('success', 'FAQ deleted.');
    } elseif ($id && ($_POST['action'] ?? '') === 'toggle') {
        db_query('UPDATE faqs SET is_published = 1 - is_published WHERE id = ?', [$id]);
        flash_set('success', 'Visibility updated.');
    }
    redirect('admin/faqs.php');
}
$rows = db_all('SELECT * FROM faqs ORDER BY sort_order ASC, id ASC');
$adminTitle = 'FAQs';
require __DIR__ . '/includes/header.php';
?>
<div class="d-flex justify-content-between align-items-center mb-3">
    <p class="text-muted mb-0"><?= count($rows) ?> questions · lower sort order shows first</p>
    <a href="<?= admin_url('faq-form.php') ?>" class="btn btn-primary"><i class="fa-solid fa-plus me-1"></i>Add question</a>
</div>
<div class="card">
    <div class="table-responsive">
        <table class="table mb-0 align-middle">
            <thead><tr><th>Order</th><th>Question</th><th>Answer</th><th>Visible</th><th></th></tr></thead>
            <tbody>
            <?php foreach ($rows as $f): ?>
                <tr>
                    <td><?= (int) $f['sort_order'] ?></td>
                    <td class="fw-semibold"><?= e($f['question']) ?></td>
                    <td class="text-muted-sm" style="max-width:460px"><?= e(excerpt($f['answer'], 140)) ?></td>
                    <td><form method="post"><?= csrf_field() ?><input type="hidden" name="id" value="<?= $f['id'] ?>"><input type="hidden" name="action" value="toggle"><button class="btn btn-sm <?= $f['is_published'] ? 'btn-success' : 'btn-outline-secondary' ?>"><?= $f['is_published'] ? 'Published' : 'Hidden' ?></button></form></td>
                    <td class="text-end text-nowrap">
                        <a href="<?= admin_url('faq-form.php?id=' . $f['id']) ?>" class="btn btn-sm btn-outline-secondary"><i class="fa-solid fa-pen"></i></a>
                        <form method="post" class="d-inline" data-confirm-submit="Delete this question?"><?= csrf_field() ?><input type="hidden" name="id" value="<?= $f['id'] ?>"><input type="hidden" name="action" value="delete"><button class="btn btn-sm btn-outline-danger"><i class="fa-solid fa-trash"></i></button></form>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
