<?php
require __DIR__ . '/includes/auth.php';
require_login();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_csrf();
    $id = (int) ($_POST['id'] ?? 0);
    if ($id && ($_POST['action'] ?? '') === 'delete') {
        db_query('DELETE FROM testimonials WHERE id = ?', [$id]);
        flash_set('success', 'Testimonial deleted.');
    } elseif ($id && ($_POST['action'] ?? '') === 'toggle') {
        db_query('UPDATE testimonials SET is_published = 1 - is_published WHERE id = ?', [$id]);
        flash_set('success', 'Visibility updated.');
    }
    redirect('admin/testimonials.php');
}
$rows = db_all('SELECT * FROM testimonials ORDER BY sort_order ASC, created_at DESC');
$adminTitle = 'Testimonials';
require __DIR__ . '/includes/header.php';
?>
<div class="d-flex justify-content-between align-items-center mb-3">
    <p class="text-muted mb-0"><?= count($rows) ?> testimonials · lower sort order shows first</p>
    <a href="<?= admin_url('testimonial-form.php') ?>" class="btn btn-primary"><i class="fa-solid fa-plus me-1"></i>Add testimonial</a>
</div>
<div class="card">
    <div class="table-responsive">
        <table class="table mb-0 align-middle">
            <thead><tr><th>Order</th><th>Client</th><th>Quote</th><th>Rating</th><th>Visible</th><th></th></tr></thead>
            <tbody>
            <?php foreach ($rows as $t): ?>
                <tr>
                    <td><?= (int) $t['sort_order'] ?></td>
                    <td><strong><?= e($t['name']) ?></strong><div class="text-muted-sm"><?= e($t['location']) ?><?= $t['vehicle'] ? ' · ' . e($t['vehicle']) : '' ?></div></td>
                    <td class="text-muted-sm" style="max-width:420px"><?= e(excerpt($t['quote'], 120)) ?></td>
                    <td class="text-warning text-nowrap"><?= str_repeat('★', (int) $t['rating']) ?></td>
                    <td><form method="post"><?= csrf_field() ?><input type="hidden" name="id" value="<?= $t['id'] ?>"><input type="hidden" name="action" value="toggle"><button class="btn btn-sm <?= $t['is_published'] ? 'btn-success' : 'btn-outline-secondary' ?>"><?= $t['is_published'] ? 'Published' : 'Hidden' ?></button></form></td>
                    <td class="text-end text-nowrap">
                        <a href="<?= admin_url('testimonial-form.php?id=' . $t['id']) ?>" class="btn btn-sm btn-outline-secondary"><i class="fa-solid fa-pen"></i></a>
                        <form method="post" class="d-inline" data-confirm-submit="Delete this testimonial?"><?= csrf_field() ?><input type="hidden" name="id" value="<?= $t['id'] ?>"><input type="hidden" name="action" value="delete"><button class="btn btn-sm btn-outline-danger"><i class="fa-solid fa-trash"></i></button></form>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
