<?php
require __DIR__ . '/includes/auth.php';
require_login();
require __DIR__ . '/includes/upload.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_csrf();
    $id     = (int) ($_POST['id'] ?? 0);
    $action = (string) ($_POST['action'] ?? '');
    $v      = $id ? vehicle_by_id($id) : null;
    if ($v) {
        if ($action === 'delete') {
            foreach (vehicle_images($id) as $img) {
                delete_upload($img['path']);
            }
            db_query('DELETE FROM vehicles WHERE id = ?', [$id]);
            flash_set('success', vehicle_title($v) . ' deleted.');
        } elseif ($action === 'feature') {
            db_update('vehicles', ['is_featured' => $v['is_featured'] ? 0 : 1], 'id = ?', [$id]);
            flash_set('success', vehicle_title($v) . ($v['is_featured'] ? ' removed from' : ' added to') . ' featured.');
        } elseif ($action === 'status' && isset(STATUSES[$_POST['status'] ?? ''])) {
            db_update('vehicles', ['status' => $_POST['status']], 'id = ?', [$id]);
            flash_set('success', vehicle_title($v) . ' marked ' . STATUSES[$_POST['status']] . '.');
        }
    }
    redirect('admin/vehicles.php' . query_with([]));
}

$q      = trim((string) ($_GET['q'] ?? ''));
$status = (string) ($_GET['status'] ?? '');
$where  = ['1=1'];
$params = [];
if ($q !== '') {
    $where[]  = 'CONCAT_WS(" ", year, make, model, trim, stock_number, vin) LIKE ?';
    $params[] = "%$q%";
}
if ($status !== '' && (isset(STATUSES[$status]) || $status === 'hidden')) {
    $where[]  = 'status = ?';
    $params[] = $status;
}
$rows = db_all('SELECT v.*, (SELECT COUNT(*) FROM vehicle_images i WHERE i.vehicle_id = v.id) AS photo_count, (SELECT COUNT(*) FROM leads l WHERE l.vehicle_id = v.id) AS lead_count FROM vehicles v WHERE ' . implode(' AND ', $where) . ' ORDER BY FIELD(v.status, "available","pending","hidden","sold"), v.is_featured DESC, v.created_at DESC', $params);
$counts = array_column(db_all('SELECT status, COUNT(*) n FROM vehicles GROUP BY status'), 'n', 'status');

$adminTitle = 'Vehicles';
require __DIR__ . '/includes/header.php';
?>
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
    <form class="d-flex gap-2" method="get">
        <input type="search" class="form-control" name="q" value="<?= e($q) ?>" placeholder="Search make, model, stock #, VIN…" style="min-width:260px">
        <select class="form-select" name="status" style="width:auto">
            <option value="">All statuses (<?= array_sum($counts) ?>)</option>
            <?php foreach (STATUSES + ['hidden' => 'Hidden'] as $k => $label): ?><option value="<?= $k ?>"<?= $status === $k ? ' selected' : '' ?>><?= $label ?> (<?= (int) ($counts[$k] ?? 0) ?>)</option><?php endforeach; ?>
        </select>
        <button class="btn btn-outline-secondary" type="submit"><i class="fa-solid fa-magnifying-glass"></i></button>
    </form>
    <a href="<?= admin_url('vehicle-form.php') ?>" class="btn btn-primary"><i class="fa-solid fa-plus me-1"></i>Add vehicle</a>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table mb-0 align-middle">
            <thead><tr><th></th><th>Vehicle</th><th>Price</th><th>Km</th><th>Status</th><th class="text-center">Featured</th><th class="text-center">Photos</th><th class="text-center">Views</th><th class="text-center">Leads</th><th></th></tr></thead>
            <tbody>
            <?php if (!$rows): ?><tr><td colspan="10" class="text-center text-muted py-5">No vehicles found.</td></tr><?php endif; ?>
            <?php foreach ($rows as $v): ?>
                <tr>
                    <td><img src="<?= e(vehicle_cover($v)) ?>" class="thumb" alt=""></td>
                    <td>
                        <a href="<?= admin_url('vehicle-form.php?id=' . $v['id']) ?>" class="fw-semibold"><?= e(vehicle_title($v)) ?></a>
                        <div class="text-muted-sm"><?= e($v['stock_number']) ?> · <?= e($v['exterior_color']) ?> · <?= e(CONDITIONS[$v['condition']] ?? $v['condition']) ?></div>
                    </td>
                    <td class="text-nowrap"><?= money(vehicle_price($v)) ?><?= vehicle_on_sale($v) ? '<div class="text-muted-sm"><s>' . money($v['price']) . '</s></div>' : '' ?></td>
                    <td class="text-nowrap"><?= number($v['mileage']) ?></td>
                    <td>
                        <form method="post" class="d-inline">
                            <?= csrf_field() ?><input type="hidden" name="id" value="<?= $v['id'] ?>"><input type="hidden" name="action" value="status">
                            <select name="status" class="form-select form-select-sm" onchange="this.form.submit()" style="width:auto">
                                <?php foreach (STATUSES + ['hidden' => 'Hidden'] as $k => $label): ?><option value="<?= $k ?>"<?= $v['status'] === $k ? ' selected' : '' ?>><?= $label ?></option><?php endforeach; ?>
                            </select>
                        </form>
                    </td>
                    <td class="text-center">
                        <form method="post" class="d-inline"><?= csrf_field() ?><input type="hidden" name="id" value="<?= $v['id'] ?>"><input type="hidden" name="action" value="feature">
                            <button class="btn btn-sm <?= $v['is_featured'] ? 'btn-warning' : 'btn-outline-secondary' ?>" title="Toggle featured"><i class="fa-<?= $v['is_featured'] ? 'solid' : 'regular' ?> fa-star"></i></button>
                        </form>
                    </td>
                    <td class="text-center"><?= (int) $v['photo_count'] ?></td>
                    <td class="text-center"><?= number($v['views']) ?></td>
                    <td class="text-center"><?= (int) $v['lead_count'] ? '<a href="' . admin_url('leads.php?vehicle=' . $v['id']) . '">' . (int) $v['lead_count'] . '</a>' : '0' ?></td>
                    <td class="text-end text-nowrap">
                        <a href="<?= vehicle_url($v) ?>" target="_blank" class="btn btn-sm btn-outline-secondary" title="View on site"><i class="fa-solid fa-arrow-up-right-from-square"></i></a>
                        <a href="<?= admin_url('vehicle-form.php?id=' . $v['id']) ?>" class="btn btn-sm btn-outline-secondary" title="Edit"><i class="fa-solid fa-pen"></i></a>
                        <form method="post" class="d-inline" data-confirm-submit="Delete <?= e(vehicle_title($v)) ?> and all its photos? This cannot be undone."><?= csrf_field() ?><input type="hidden" name="id" value="<?= $v['id'] ?>"><input type="hidden" name="action" value="delete"><button class="btn btn-sm btn-outline-danger" title="Delete"><i class="fa-solid fa-trash"></i></button></form>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
