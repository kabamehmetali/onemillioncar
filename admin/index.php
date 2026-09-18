<?php
require __DIR__ . '/includes/auth.php';
require_login();

$stats = [
    'available' => (int) db_value('SELECT COUNT(*) FROM vehicles WHERE status = "available"'),
    'pending'   => (int) db_value('SELECT COUNT(*) FROM vehicles WHERE status = "pending"'),
    'sold'      => (int) db_value('SELECT COUNT(*) FROM vehicles WHERE status = "sold"'),
    'value'     => (float) db_value('SELECT COALESCE(SUM(COALESCE(NULLIF(sale_price,0), price)),0) FROM vehicles WHERE status IN ("available","pending")'),
    'new_leads' => (int) db_value('SELECT COUNT(*) FROM leads WHERE status = "new"'),
    'leads_30'  => (int) db_value('SELECT COUNT(*) FROM leads WHERE created_at >= NOW() - INTERVAL 30 DAY'),
    'views'     => (int) db_value('SELECT COALESCE(SUM(views),0) FROM vehicles'),
];
$recentLeads = db_all('SELECT l.*, v.year, v.make, v.model FROM leads l LEFT JOIN vehicles v ON v.id = l.vehicle_id ORDER BY l.created_at DESC LIMIT 8');
$topVehicles = db_all('SELECT v.*, (SELECT COUNT(*) FROM leads l WHERE l.vehicle_id = v.id) AS lead_count FROM vehicles v WHERE v.status <> "sold" ORDER BY v.views DESC LIMIT 5');
$adminTitle  = 'Dashboard';
require __DIR__ . '/includes/header.php';
?>
<div class="row g-3 mb-4">
    <div class="col-sm-6 col-xl-3"><div class="card stat-card"><i class="fa-solid fa-car"></i><div><strong><?= $stats['available'] ?></strong><span>Available vehicles<?= $stats['pending'] ? ' · ' . $stats['pending'] . ' pending' : '' ?></span></div></div></div>
    <div class="col-sm-6 col-xl-3"><div class="card stat-card"><i class="fa-solid fa-sack-dollar"></i><div><strong><?= money($stats['value']) ?></strong><span>Inventory value</span></div></div></div>
    <div class="col-sm-6 col-xl-3"><div class="card stat-card"><i class="fa-solid fa-inbox"></i><div><strong><?= $stats['new_leads'] ?></strong><span>New leads · <?= $stats['leads_30'] ?> in 30 days</span></div></div></div>
    <div class="col-sm-6 col-xl-3"><div class="card stat-card"><i class="fa-solid fa-eye"></i><div><strong><?= number($stats['views']) ?></strong><span>Vehicle page views</span></div></div></div>
</div>

<div class="row g-3">
    <div class="col-xl-7">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">Latest leads <a href="<?= admin_url('leads.php') ?>" class="btn btn-sm btn-outline-secondary">All leads</a></div>
            <div class="table-responsive">
                <table class="table mb-0">
                    <thead><tr><th>When</th><th>Type</th><th>Name</th><th>Vehicle</th><th>Status</th></tr></thead>
                    <tbody>
                    <?php if (!$recentLeads): ?><tr><td colspan="5" class="text-center text-muted py-4">No leads yet. Once visitors submit a form it shows up here.</td></tr><?php endif; ?>
                    <?php foreach ($recentLeads as $l): ?>
                        <tr>
                            <td class="text-nowrap text-muted-sm"><?= e(time_ago($l['created_at'])) ?></td>
                            <td><?= e(LEAD_TYPES[$l['type']] ?? $l['type']) ?></td>
                            <td><a href="<?= admin_url('lead-view.php?id=' . $l['id']) ?>"><?= e($l['name']) ?></a></td>
                            <td class="text-muted-sm"><?= $l['vehicle_id'] ? e($l['year'] . ' ' . $l['make'] . ' ' . $l['model']) : '—' ?></td>
                            <td><?= lead_badge($l['status']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-xl-5">
        <div class="card mb-3">
            <div class="card-header d-flex justify-content-between align-items-center">Most viewed vehicles <a href="<?= admin_url('vehicle-form.php') ?>" class="btn btn-sm btn-primary"><i class="fa-solid fa-plus me-1"></i>Add vehicle</a></div>
            <div class="table-responsive">
                <table class="table mb-0">
                    <thead><tr><th>Vehicle</th><th class="text-end">Views</th><th class="text-end">Leads</th></tr></thead>
                    <tbody>
                    <?php foreach ($topVehicles as $v): ?>
                        <tr>
                            <td><a href="<?= admin_url('vehicle-form.php?id=' . $v['id']) ?>"><?= e(vehicle_title($v)) ?></a><div class="text-muted-sm"><?= money(vehicle_price($v)) ?> · <?= e(STATUSES[$v['status']] ?? $v['status']) ?></div></td>
                            <td class="text-end"><?= number($v['views']) ?></td>
                            <td class="text-end"><?= (int) $v['lead_count'] ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card">
            <div class="card-body">
                <h2 class="h6 fw-bold">Quick links</h2>
                <div class="d-flex flex-wrap gap-2">
                    <a href="<?= admin_url('vehicle-form.php') ?>" class="btn btn-sm btn-outline-primary"><i class="fa-solid fa-plus me-1"></i>New vehicle</a>
                    <a href="<?= admin_url('settings.php') ?>" class="btn btn-sm btn-outline-secondary"><i class="fa-solid fa-sliders me-1"></i>Site settings</a>
                    <a href="<?= admin_url('testimonials.php') ?>" class="btn btn-sm btn-outline-secondary"><i class="fa-solid fa-star me-1"></i>Testimonials</a>
                    <a href="<?= url() ?>" target="_blank" class="btn btn-sm btn-outline-secondary"><i class="fa-solid fa-arrow-up-right-from-square me-1"></i>View site</a>
                </div>
                <?php if (setting('notify_email') === ''): ?>
                    <div class="alert alert-warning small mt-3 mb-0"><i class="fa-solid fa-triangle-exclamation me-1"></i>Lead email notifications are off — set a <strong>notification email</strong> under Settings → Contact.</div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
