<?php
require __DIR__ . '/includes/auth.php';
require_login();

$id = (int) ($_GET['id'] ?? 0);
$l  = db_one('SELECT l.*, v.year, v.make, v.model, v.slug, v.stock_number FROM leads l LEFT JOIN vehicles v ON v.id = l.vehicle_id WHERE l.id = ?', [$id]);
if (!$l) {
    flash_set('danger', 'Lead not found.');
    redirect('admin/leads.php');
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_csrf();
    if (isset(LEAD_STATUSES[$_POST['status'] ?? ''])) {
        db_update('leads', ['status' => $_POST['status']], 'id = ?', [$id]);
        flash_set('success', 'Status updated.');
    }
    redirect('admin/lead-view.php?id=' . $id);
}
$details    = json_decode((string) $l['details'], true) ?: [];
$adminTitle = LEAD_TYPES[$l['type']] . ' from ' . $l['name'];
require __DIR__ . '/includes/header.php';
?>
<div class="row g-3">
    <div class="col-lg-8">
        <div class="card mb-3">
            <div class="card-header d-flex justify-content-between align-items-center"><span><?= e(LEAD_TYPES[$l['type']]) ?> <?= lead_badge($l['status']) ?></span><span class="text-muted-sm"><?= e(date('F j, Y \a\t g:i a', strtotime($l['created_at']))) ?></span></div>
            <div class="card-body">
                <dl class="lead-detail row">
                    <div class="col-md-6"><dt>Name</dt><dd><?= e($l['name']) ?></dd></div>
                    <div class="col-md-6"><dt>Email</dt><dd><a href="mailto:<?= e($l['email']) ?>"><?= e($l['email']) ?></a></dd></div>
                    <div class="col-md-6"><dt>Phone</dt><dd><?= $l['phone'] ? '<a href="' . e(phone_href($l['phone'])) . '">' . e($l['phone']) . '</a>' : '—' ?></dd></div>
                    <?php if ($l['vehicle_id']): ?><div class="col-md-6"><dt>Vehicle</dt><dd><a href="<?= admin_url('vehicle-form.php?id=' . $l['vehicle_id']) ?>"><?= e($l['year'] . ' ' . $l['make'] . ' ' . $l['model']) ?></a> <span class="text-muted-sm">(<?= e($l['stock_number']) ?>)</span></dd></div><?php endif; ?>
                    <?php foreach ($details as $k => $val): ?>
                        <div class="col-md-6"><dt><?= e(ucfirst(str_replace('_', ' ', (string) $k))) ?></dt><dd><?= e((string) $val) ?></dd></div>
                    <?php endforeach; ?>
                </dl>
                <?php if (trim((string) $l['message']) !== ''): ?>
                    <dt class="text-muted-sm fw-normal">Message</dt>
                    <div class="border rounded p-3 bg-light"><?= nl2br(e($l['message'])) ?></div>
                <?php endif; ?>
                <div class="text-muted-sm mt-3">IP <?= e($l['ip'] ?: '—') ?></div>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card mb-3">
            <div class="card-header">Actions</div>
            <div class="card-body">
                <form method="post" class="mb-3"><?= csrf_field() ?>
                    <label class="form-label">Status</label>
                    <div class="input-group"><select name="status" class="form-select"><?php foreach (LEAD_STATUSES as $k => $lab): ?><option value="<?= $k ?>"<?= $l['status'] === $k ? ' selected' : '' ?>><?= $lab ?></option><?php endforeach; ?></select><button class="btn btn-primary">Save</button></div>
                </form>
                <a href="mailto:<?= e($l['email']) ?>?subject=<?= rawurlencode('Re: your ' . strtolower(LEAD_TYPES[$l['type']]) . ($l['vehicle_id'] ? ' — ' . $l['year'] . ' ' . $l['make'] . ' ' . $l['model'] : '')) ?>" class="btn btn-outline-secondary w-100 mb-2"><i class="fa-solid fa-reply me-1"></i>Reply by email</a>
                <?php if ($l['phone']): ?><a href="<?= e(phone_href($l['phone'])) ?>" class="btn btn-outline-secondary w-100 mb-2"><i class="fa-solid fa-phone me-1"></i>Call <?= e($l['phone']) ?></a><?php endif; ?>
                <a href="<?= admin_url('leads.php') ?>" class="btn btn-light w-100">← Back to leads</a>
            </div>
        </div>
    </div>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
