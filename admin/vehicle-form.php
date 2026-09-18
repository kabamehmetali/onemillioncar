<?php
require __DIR__ . '/includes/auth.php';
require_login();
require __DIR__ . '/includes/upload.php';

$id = (int) ($_GET['id'] ?? $_POST['id'] ?? 0);
$v  = $id ? vehicle_by_id($id) : null;
if ($id && !$v) {
    flash_set('danger', 'Vehicle not found.');
    redirect('admin/vehicles.php');
}
$defaults = [
    'year' => (int) date('Y'), 'make' => '', 'model' => '', 'trim' => '', 'body_type' => 'SUV', 'condition' => 'used', 'status' => 'available',
    'price' => '', 'sale_price' => '', 'mileage' => '', 'transmission' => 'Automatic', 'drivetrain' => 'AWD', 'fuel_type' => 'Gasoline',
    'engine' => '', 'horsepower' => '', 'fuel_economy' => '', 'exterior_color' => '', 'interior_color' => '', 'color_hex' => '#3a3a3f',
    'doors' => 4, 'seats' => 5, 'vin' => '', 'stock_number' => '', 'description' => '', 'features' => '', 'is_featured' => 0, 'sort_order' => 0, 'cover_image' => '', 'slug' => '',
];
$data   = $v ? array_merge($defaults, $v) : $defaults;
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_csrf();
    $action = (string) ($_POST['action'] ?? 'save');

    // --- image-only actions on an existing vehicle
    if ($v && $action === 'delete_image') {
        $img = db_one('SELECT * FROM vehicle_images WHERE id = ? AND vehicle_id = ?', [(int) $_POST['image_id'], $id]);
        if ($img) {
            db_query('DELETE FROM vehicle_images WHERE id = ?', [(int) $img['id']]);
            delete_upload($img['path']);
            if ($v['cover_image'] === $img['path']) {
                $next = db_value('SELECT path FROM vehicle_images WHERE vehicle_id = ? ORDER BY sort_order, id LIMIT 1', [$id]);
                db_update('vehicles', ['cover_image' => (string) ($next ?? '')], 'id = ?', [$id]);
            }
            flash_set('success', 'Photo removed.');
        }
        redirect('admin/vehicle-form.php?id=' . $id . '#photos');
    }
    if ($v && $action === 'set_cover') {
        $img = db_one('SELECT * FROM vehicle_images WHERE id = ? AND vehicle_id = ?', [(int) $_POST['image_id'], $id]);
        if ($img) {
            db_update('vehicles', ['cover_image' => $img['path']], 'id = ?', [$id]);
            flash_set('success', 'Cover photo updated.');
        }
        redirect('admin/vehicle-form.php?id=' . $id . '#photos');
    }
    if ($v && $action === 'reorder') {
        foreach ((array) ($_POST['order'] ?? []) as $imgId => $pos) {
            db_query('UPDATE vehicle_images SET sort_order = ? WHERE id = ? AND vehicle_id = ?', [(int) $pos, (int) $imgId, $id]);
        }
        flash_set('success', 'Photo order saved.');
        redirect('admin/vehicle-form.php?id=' . $id . '#photos');
    }

    // --- main save
    foreach (array_keys($defaults) as $k) {
        if (isset($_POST[$k]) && is_string($_POST[$k])) {
            $data[$k] = trim($_POST[$k]);
        }
    }
    $data['is_featured'] = isset($_POST['is_featured']) ? 1 : 0;
    $data['year']        = (int) $data['year'];
    $data['mileage']     = (int) str_replace([',', ' '], '', (string) $data['mileage']);
    $data['price']       = (float) str_replace([',', '$', ' '], '', (string) $data['price']);
    $data['sale_price']  = $data['sale_price'] === '' ? null : (float) str_replace([',', '$', ' '], '', (string) $data['sale_price']);
    $data['horsepower']  = $data['horsepower'] === '' ? null : (int) $data['horsepower'];
    $data['doors']       = max(2, min(6, (int) $data['doors']));
    $data['seats']       = max(1, min(15, (int) $data['seats']));
    $data['sort_order']  = (int) $data['sort_order'];
    $data['vin']         = strtoupper(substr(preg_replace('/[^A-Za-z0-9]/', '', (string) $data['vin']) ?? '', 0, 17));
    if (!preg_match('/^#[0-9a-fA-F]{6}$/', (string) $data['color_hex'])) $data['color_hex'] = '#3a3a3f';
    if (!isset(CONDITIONS[$data['condition']])) $data['condition'] = 'used';
    if (!isset(STATUSES[$data['status']]) && $data['status'] !== 'hidden') $data['status'] = 'available';
    if (!in_array($data['body_type'], BODY_TYPES, true)) $data['body_type'] = 'Sedan';

    if ($data['year'] < 1980 || $data['year'] > (int) date('Y') + 1) $errors['year'] = 'Enter a valid year.';
    if ($data['make'] === '')  $errors['make'] = 'Make is required.';
    if ($data['model'] === '') $errors['model'] = 'Model is required.';
    if ($data['price'] <= 0)   $errors['price'] = 'Enter the asking price.';
    if ($data['sale_price'] !== null && $data['sale_price'] >= $data['price']) $errors['sale_price'] = 'Sale price must be lower than the regular price (or leave blank).';

    if (!$errors) {
        $slugBase = slugify($data['year'] . ' ' . $data['make'] . ' ' . $data['model'] . ' ' . $data['trim']);
        $slug     = $v && $v['slug'] !== '' && slugify($v['year'] . ' ' . $v['make'] . ' ' . $v['model'] . ' ' . $v['trim']) === $slugBase ? $v['slug'] : $slugBase;
        $n = 2;
        while (db_value('SELECT id FROM vehicles WHERE slug = ? AND id <> ?', [$slug, $id])) {
            $slug = $slugBase . '-' . $n++;
        }
        $data['slug'] = $slug;
        if ($data['stock_number'] === '') {
            $data['stock_number'] = 'LAH-' . str_pad((string) ((int) db_value('SELECT COALESCE(MAX(id),0) FROM vehicles') + 1000 + ($v ? 0 : 1)), 4, '0', STR_PAD_LEFT);
        }
        $save = array_intersect_key($data, $defaults);
        if ($v) {
            db_update('vehicles', $save, 'id = ?', [$id]);
        } else {
            $id = db_insert('vehicles', $save);
        }

        // Uploaded photos
        $uploadErrors = [];
        $saved = 0;
        foreach (files_array($_FILES['photos'] ?? []) as $file) {
            $err = null;
            $path = save_vehicle_image($file, $slug, $err);
            if ($path) {
                $max = (int) db_value('SELECT COALESCE(MAX(sort_order),0) FROM vehicle_images WHERE vehicle_id = ?', [$id]);
                db_insert('vehicle_images', ['vehicle_id' => $id, 'path' => $path, 'sort_order' => $max + 1]);
                $saved++;
            } elseif ($err) {
                $uploadErrors[] = ($file['name'] ?? 'file') . ': ' . $err;
            }
        }
        $cover = (string) db_value('SELECT cover_image FROM vehicles WHERE id = ?', [$id]);
        if ($cover === '' || !is_file(APP_ROOT . '/' . $cover)) {
            $first = db_value('SELECT path FROM vehicle_images WHERE vehicle_id = ? ORDER BY sort_order, id LIMIT 1', [$id]);
            if ($first) {
                db_update('vehicles', ['cover_image' => (string) $first], 'id = ?', [$id]);
            }
        }
        flash_set('success', ($v ? 'Vehicle updated.' : 'Vehicle added.') . ($saved ? " $saved photo(s) uploaded." : ''));
        foreach ($uploadErrors as $ue) {
            flash_set('danger', $ue);
        }
        redirect('admin/vehicle-form.php?id=' . $id);
    }
}

$images     = $v ? vehicle_images($id) : [];
$adminTitle = $v ? 'Edit ' . vehicle_title($v) : 'Add vehicle';
require __DIR__ . '/includes/header.php';
$field = fn(string $k) => e((string) ($data[$k] ?? ''));
$inv   = fn(string $k) => isset($errors[$k]) ? ' is-invalid' : '';
?>
<form method="post" enctype="multipart/form-data" class="needs-validation" novalidate>
    <?= csrf_field() ?>
    <input type="hidden" name="id" value="<?= $id ?>">
    <input type="hidden" name="action" value="save">
    <div class="row g-3">
        <div class="col-xl-8">
            <div class="card mb-3">
                <div class="card-header">Vehicle</div>
                <div class="card-body">
                    <?php if ($errors): ?><div class="alert alert-danger">Please fix the highlighted fields.</div><?php endif; ?>
                    <div class="row g-3">
                        <div class="col-md-2"><label class="form-label">Year *</label><input type="number" name="year" class="form-control<?= $inv('year') ?>" value="<?= $field('year') ?>" min="1980" max="<?= date('Y') + 1 ?>" required><div class="invalid-feedback"><?= e($errors['year'] ?? '') ?></div></div>
                        <div class="col-md-3"><label class="form-label">Make *</label><input type="text" name="make" class="form-control<?= $inv('make') ?>" value="<?= $field('make') ?>" list="makes" required><datalist id="makes"><?php foreach (['Acura','Audi','BMW','Cadillac','Chevrolet','Chrysler','Dodge','Ford','GMC','Genesis','Honda','Hyundai','Infiniti','Jaguar','Jeep','Kia','Land Rover','Lexus','Lincoln','Mazda','Mercedes-Benz','Mini','Mitsubishi','Nissan','Porsche','Ram','Subaru','Tesla','Toyota','Volkswagen','Volvo'] as $m): ?><option value="<?= $m ?>"><?php endforeach; ?></datalist></div>
                        <div class="col-md-3"><label class="form-label">Model *</label><input type="text" name="model" class="form-control<?= $inv('model') ?>" value="<?= $field('model') ?>" required></div>
                        <div class="col-md-4"><label class="form-label">Trim</label><input type="text" name="trim" class="form-control" value="<?= $field('trim') ?>" placeholder="e.g. xDrive M Sport"></div>
                        <div class="col-md-3"><label class="form-label">Body style</label><select name="body_type" class="form-select"><?php foreach (BODY_TYPES as $b): ?><option<?= $data['body_type'] === $b ? ' selected' : '' ?>><?= $b ?></option><?php endforeach; ?></select></div>
                        <div class="col-md-3"><label class="form-label">Condition</label><select name="condition" class="form-select"><?php foreach (CONDITIONS as $k => $l): ?><option value="<?= $k ?>"<?= $data['condition'] === $k ? ' selected' : '' ?>><?= $l ?></option><?php endforeach; ?></select></div>
                        <div class="col-md-3"><label class="form-label">Status</label><select name="status" class="form-select"><?php foreach (STATUSES + ['hidden' => 'Hidden (draft)'] as $k => $l): ?><option value="<?= $k ?>"<?= $data['status'] === $k ? ' selected' : '' ?>><?= $l ?></option><?php endforeach; ?></select></div>
                        <div class="col-md-3"><label class="form-label">Stock #</label><input type="text" name="stock_number" class="form-control" value="<?= $field('stock_number') ?>" placeholder="auto"></div>
                        <div class="col-md-3"><label class="form-label">Price (CAD) *</label><div class="input-group"><span class="input-group-text">$</span><input type="number" name="price" class="form-control<?= $inv('price') ?>" value="<?= $field('price') ?>" min="0" step="1" required></div><?php if (isset($errors['price'])): ?><div class="text-danger small"><?= e($errors['price']) ?></div><?php endif; ?></div>
                        <div class="col-md-3"><label class="form-label">Sale price</label><div class="input-group"><span class="input-group-text">$</span><input type="number" name="sale_price" class="form-control<?= $inv('sale_price') ?>" value="<?= $field('sale_price') ?>" min="0" step="1" placeholder="optional"></div><?php if (isset($errors['sale_price'])): ?><div class="text-danger small"><?= e($errors['sale_price']) ?></div><?php endif; ?></div>
                        <div class="col-md-3"><label class="form-label">Kilometres</label><input type="number" name="mileage" class="form-control" value="<?= $field('mileage') ?>" min="0"></div>
                        <div class="col-md-3"><label class="form-label">VIN</label><input type="text" name="vin" class="form-control text-uppercase" value="<?= $field('vin') ?>" maxlength="17"></div>
                    </div>
                </div>
            </div>

            <div class="card mb-3">
                <div class="card-header">Specifications</div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-3"><label class="form-label">Transmission</label><select name="transmission" class="form-select"><?php foreach (TRANSMISSIONS as $t): ?><option<?= $data['transmission'] === $t ? ' selected' : '' ?>><?= $t ?></option><?php endforeach; ?></select></div>
                        <div class="col-md-3"><label class="form-label">Drivetrain</label><select name="drivetrain" class="form-select"><?php foreach (DRIVETRAINS as $t): ?><option<?= $data['drivetrain'] === $t ? ' selected' : '' ?>><?= $t ?></option><?php endforeach; ?></select></div>
                        <div class="col-md-3"><label class="form-label">Fuel</label><select name="fuel_type" class="form-select"><?php foreach (FUEL_TYPES as $t): ?><option<?= $data['fuel_type'] === $t ? ' selected' : '' ?>><?= $t ?></option><?php endforeach; ?></select></div>
                        <div class="col-md-3"><label class="form-label">Horsepower</label><input type="number" name="horsepower" class="form-control" value="<?= $field('horsepower') ?>" min="0"></div>
                        <div class="col-md-6"><label class="form-label">Engine</label><input type="text" name="engine" class="form-control" value="<?= $field('engine') ?>" placeholder="e.g. 2.0L Turbo I4"></div>
                        <div class="col-md-6"><label class="form-label">Fuel economy</label><input type="text" name="fuel_economy" class="form-control" value="<?= $field('fuel_economy') ?>" placeholder="e.g. 8.4 L/100 km combined"></div>
                        <div class="col-md-4"><label class="form-label">Exterior colour</label><input type="text" name="exterior_color" class="form-control" value="<?= $field('exterior_color') ?>"></div>
                        <div class="col-md-2"><label class="form-label">Swatch</label><input type="color" name="color_hex" class="form-control form-control-color w-100" value="<?= $field('color_hex') ?>" title="Colour swatch shown on cards"></div>
                        <div class="col-md-4"><label class="form-label">Interior colour</label><input type="text" name="interior_color" class="form-control" value="<?= $field('interior_color') ?>"></div>
                        <div class="col-md-1"><label class="form-label">Doors</label><input type="number" name="doors" class="form-control" value="<?= $field('doors') ?>" min="2" max="6"></div>
                        <div class="col-md-1"><label class="form-label">Seats</label><input type="number" name="seats" class="form-control" value="<?= $field('seats') ?>" min="1" max="15"></div>
                    </div>
                </div>
            </div>

            <div class="card mb-3">
                <div class="card-header">Description &amp; features</div>
                <div class="card-body">
                    <div class="mb-3"><label class="form-label">Description</label><textarea name="description" class="form-control" rows="7" placeholder="History, condition, highlights. Blank lines start new paragraphs."><?= $field('description') ?></textarea></div>
                    <div><label class="form-label">Features <span class="text-muted-sm fw-normal">— one per line</span></label><textarea name="features" class="form-control" rows="8" placeholder="Heated seats&#10;Apple CarPlay&#10;Panoramic roof"><?= $field('features') ?></textarea></div>
                </div>
            </div>
        </div>

        <div class="col-xl-4">
            <div class="card mb-3">
                <div class="card-header">Publish</div>
                <div class="card-body">
                    <div class="form-check form-switch mb-3"><input class="form-check-input" type="checkbox" name="is_featured" id="feat" value="1"<?= $data['is_featured'] ? ' checked' : '' ?>><label class="form-check-label" for="feat">Featured on home page</label></div>
                    <div class="mb-3"><label class="form-label">Sort order</label><input type="number" name="sort_order" class="form-control" value="<?= $field('sort_order') ?>"><div class="text-muted-sm">Lower numbers appear first among featured vehicles.</div></div>
                    <div class="text-muted-sm mb-3">URL: <code>/inventory/<span id="slugPreview"><?= $field('slug') ?: '…' ?></span></code></div>
                    <button type="submit" class="btn btn-primary w-100"><i class="fa-solid fa-floppy-disk me-1"></i><?= $v ? 'Save changes' : 'Add vehicle' ?></button>
                    <?php if ($v): ?>
                        <a href="<?= vehicle_url($v) ?>" target="_blank" class="btn btn-outline-secondary w-100 mt-2"><i class="fa-solid fa-arrow-up-right-from-square me-1"></i>View on site</a>
                        <div class="text-muted-sm mt-3"><?= number($v['views']) ?> views · added <?= e(date('M j, Y', strtotime($v['created_at']))) ?></div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="card mb-3" id="photos">
                <div class="card-header">Photos</div>
                <div class="card-body">
                    <label class="form-label">Upload photos</label>
                    <input type="file" name="photos[]" id="photoPicker" class="form-control" accept="image/jpeg,image/png,image/webp" multiple>
                    <div class="text-muted-sm mt-1">JPEG or PNG, up to 12 MB each. Resized to 1600px automatically. <?= $v ? '' : 'You can add photos now or after saving.' ?></div>
                    <div id="photoPreview" class="mt-2"></div>
                </div>
            </div>
        </div>
    </div>
</form>

<?php if ($v): ?>
<div class="card mb-3">
    <div class="card-header d-flex justify-content-between align-items-center">Manage photos (<?= count($images) ?>) <?php if ($images): ?><button form="reorderForm" class="btn btn-sm btn-outline-secondary"><i class="fa-solid fa-arrow-down-1-9 me-1"></i>Save order</button><?php endif; ?></div>
    <div class="card-body">
        <?php if (!$images): ?><p class="text-muted mb-0">No photos yet — the placeholder image is shown on the site until you upload some.</p><?php endif; ?>
        <form method="post" id="reorderForm"><?= csrf_field() ?><input type="hidden" name="id" value="<?= $id ?>"><input type="hidden" name="action" value="reorder"></form>
        <div class="image-grid">
            <?php foreach ($images as $img): ?>
                <div class="image-tile<?= $v['cover_image'] === $img['path'] ? ' is-cover' : '' ?>">
                    <img src="<?= e(image_url($img['path'])) ?>" alt="">
                    <div class="tile-actions">
                        <input type="number" form="reorderForm" name="order[<?= $img['id'] ?>]" value="<?= (int) $img['sort_order'] ?>" class="form-control form-control-sm" title="Order">
                        <?php if ($v['cover_image'] !== $img['path']): ?>
                            <form method="post"><?= csrf_field() ?><input type="hidden" name="id" value="<?= $id ?>"><input type="hidden" name="action" value="set_cover"><input type="hidden" name="image_id" value="<?= $img['id'] ?>"><button class="btn btn-sm btn-outline-secondary" title="Set as cover"><i class="fa-regular fa-star"></i></button></form>
                        <?php else: ?><span class="badge bg-danger">Cover</span><?php endif; ?>
                        <form method="post" class="ms-auto" data-confirm-submit="Remove this photo?"><?= csrf_field() ?><input type="hidden" name="id" value="<?= $id ?>"><input type="hidden" name="action" value="delete_image"><input type="hidden" name="image_id" value="<?= $img['id'] ?>"><button class="btn btn-sm btn-outline-danger" title="Delete"><i class="fa-solid fa-trash"></i></button></form>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>
<?php endif; ?>
<?php require __DIR__ . '/includes/footer.php'; ?>
