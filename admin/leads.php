<?php
require __DIR__ . '/includes/auth.php';
require_login();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_csrf();
    $id     = (int) ($_POST['id'] ?? 0);
    $action = (string) ($_POST['action'] ?? '');
    if ($id && $action === 'delete') {
        db_query('DELETE FROM leads WHERE id = ?', [$id]);
        flash_set('success', 'Lead deleted.');
    } elseif ($id && $action === 'status' && isset(LEAD_STATUSES[$_POST['status'] ?? ''])) {
        db_update('leads', ['status' => $_POST['status']], 'id = ?', [$id]);
        flash_set('success', 'Lead marked ' . LEAD_STATUSES[$_POST['status']] . '.');
    }
    redirect('admin/leads.php' . query_with([]));
}

$type    = (string) ($_GET['type'] ?? '');
$status  = (string) ($_GET['status'] ?? '');
$vehicle = (int) ($_GET['vehicle'] ?? 0);
$q       = trim((string) ($_GET['q'] ?? ''));
$where   = ['1=1'];
$params  = [];
if (isset(LEAD_TYPES[$type]))      { $where[] = 'l.type = ?';       $params[] = $type; }
if (isset(LEAD_STATUSES[$status])) { $where[] = 'l.status = ?';     $params[] = $status; }
if ($vehicle)                      { $where[] = 'l.vehicle_id = ?'; $params[] = $vehicle; }
if ($q !== '')                     { $where[] = '(l.name LIKE ? OR l.email LIKE ? OR l.phone LIKE ? OR l.message LIKE ?)'; array_push($params, "%$q%", "%$q%", "%$q%", "%$q%"); }
$rows   = db_all('SELECT l.*, v.year, v.make, v.model, v.slug FROM leads l LEFT JOIN vehicles v ON v.id = l.vehicle_id WHERE ' . implode(' AND ', $where) . ' ORDER BY FIELD(l.status, "new", "contacted", "closed"), l.created_at DESC LIMIT 300', $params);
$counts = array_column(db_all('SELECT status, COUNT(*) n FROM leads GROUP BY status'), 'n', 'status');

$adminTitle = 'Leads';
require __DIR__ . '/includes/header.php';
?>
<form class="d-flex flex-wrap gap-2 mb-3" method="get">
    <input type="search" class="form-control" name="q" value="<?= e($q) ?>" placeholder="Search name, email, phone, message…" style="min-width:240px;max-width:320px">
    <select class="form-select" name="type" style="width:auto"><option value="">All types</option><?php foreach (LEAD_TYPES as $k => $l): ?><option value="<?= $k ?>"<?= $type === $k ? ' selected' : '' ?>><?= $l ?></option><?php endforeach; ?></select>
    <select class="form-select" name="status" style="width:auto"><option value="">All statuses</option><?php foreach (LEAD_STATUSES as $k => $l): ?><option value="<?= $k ?>"<?= $status === $k ? ' selected' : '' ?>><?= $l ?> (<?= (int) ($counts[$k] ?? 0) ?>)</option><?php endforeach; ?></select>
    <?php if ($vehicle): ?><input type="hidden" name="vehicle" value="<?= $vehicle ?>"><span class="badge bg-secondary align-self-center">Filtered by vehicle <a href="<?= admin_url('leads.php') ?>" class="text-white ms-1">×</a></span><?php endif; ?>
    <button class="btn btn-outline-secondary"><i class="fa-solid fa-filter"></i></button>
</form>

<div class="card">
    <div class="table-responsive">
        <table class="table mb-0 align-middle">
            <thead><tr><th>Received</th><th>Type</th><th>Contact</th><th>Vehicle / subject</th><th>Status</th><th></th></tr></thead>
            <tbody>
            <?php if (!$rows): ?><tr><td colspan="6" class="text-center text-muted py-5">No leads match.</td></tr><?php endif; ?>
            <?php foreach ($rows as $l): $d = json_decode((string) $l['details'], true) ?: []; ?>
                <tr class="<?= $l['status'] === 'new' ? 'fw-semibold' : '' ?>">
                    <td class="text-nowrap text-muted-sm"><?= e(date('M j, g:i a', strtotime($l['created_at']))) ?></td>
                    <td><?= e(LEAD_TYPES[$l['type']] ?? $l['type']) ?></td>
                    <td><a href="<?= admin_url('lead-view.php?id=' . $l['id']) ?>"><?= e($l['name']) ?></a><div class="text-muted-sm fw-normal"><?= e($l['email']) ?><?= $l['phone'] ? ' · ' . e($l['phone']) : '' ?></div></td>
                    <td class="fw-normal"><?= $l['vehicle_id'] ? '<a href="' . admin_url('vehicle-form.php?id=' . $l['vehicle_id']) . '">' . e($l['year'] . ' ' . $l['make'] . ' ' . $l['model']) . '</a>' : e($d['vehicle'] ?? $d['subject'] ?? $d['vehicle_of_interest'] ?? '—') ?></td>
                    <td>
                        <form method="post" class="d-inline"><?= csrf_field() ?><input type="hidden" name="id" value="<?= $l['id'] ?>"><input type="hidden" name="action" value="status">
                            <select name="status" class="form-select form-select-sm" onchange="this.form.submit()" style="width:auto"><?php foreach (LEAD_STATUSES as $k => $lab): ?><option value="<?= $k ?>"<?= $l['status'] === $k ? ' selected' : '' ?>><?= $lab ?></option><?php endforeach; ?></select>
                        </form>
                    </td>
                    <td class="text-end text-nowrap">
                        <a href="<?= admin_url('lead-view.php?id=' . $l['id']) ?>" class="btn btn-sm btn-outline-secondary"><i class="fa-solid fa-eye"></i></a>
                        <form method="post" class="d-inline" data-confirm-submit="Delete this lead?"><?= csrf_field() ?><input type="hidden" name="id" value="<?= $l['id'] ?>"><input type="hidden" name="action" value="delete"><button class="btn btn-sm btn-outline-danger"><i class="fa-solid fa-trash"></i></button></form>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
