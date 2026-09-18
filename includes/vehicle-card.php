<?php
/** Inventory card. Expects $v (vehicle row). */
$price   = vehicle_price($v);
$onSale  = vehicle_on_sale($v);
$badges  = [];
if ($v['status'] === 'pending') {
    $badges[] = ['Sale Pending', 'badge-pending'];
} elseif ($v['status'] === 'sold') {
    $badges[] = ['Sold', 'badge-sold'];
}
if ($v['condition'] === 'certified') {
    $badges[] = ['Certified', 'badge-certified'];
} elseif ($v['condition'] === 'new') {
    $badges[] = ['New', 'badge-certified'];
}
if ($onSale) {
    $badges[] = ['Price Drop', 'badge-sale'];
}
if ((int) $v['is_featured'] === 1 && $v['status'] === 'available') {
    $badges[] = ['Featured', 'badge-featured'];
}
?>
<article class="vehicle-card<?= $v['status'] === 'sold' ? ' is-sold' : '' ?>">
    <a href="<?= vehicle_url($v) ?>" class="vehicle-card-media" aria-label="<?= e(vehicle_title($v)) ?>">
        <img src="<?= e(vehicle_cover($v)) ?>" alt="<?= e(vehicle_title($v)) ?>" loading="lazy" width="800" height="500">
        <?php if ($badges): ?>
            <div class="vehicle-badges">
                <?php foreach ($badges as [$label, $class]): ?>
                    <span class="vehicle-badge <?= $class ?>"><?= e($label) ?></span>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        <span class="vehicle-card-km"><i class="fa-solid fa-road"></i><?= number($v['mileage']) ?> km</span>
    </a>
    <div class="vehicle-card-body">
        <div class="vehicle-card-head">
            <span class="vehicle-card-year"><?= e($v['year']) ?> · <?= e($v['body_type']) ?></span>
            <h3 class="vehicle-card-title"><a href="<?= vehicle_url($v) ?>"><?= e($v['make'] . ' ' . $v['model']) ?></a></h3>
            <?php if ($v['trim'] !== ''): ?><span class="vehicle-card-trim"><?= e($v['trim']) ?></span><?php endif; ?>
        </div>
        <div class="vehicle-card-price">
            <span class="price"><?= money($price) ?></span>
            <?php if ($onSale): ?><s class="price-old"><?= money($v['price']) ?></s><?php endif; ?>
            <span class="price-note">est. <?= money(estimate_biweekly($price)) ?> bi-weekly</span>
        </div>
        <ul class="vehicle-specs">
            <li title="Transmission"><i class="fa-solid fa-gears"></i><?= e($v['transmission']) ?></li>
            <li title="Drivetrain"><i class="fa-solid fa-circle-nodes"></i><?= e($v['drivetrain']) ?></li>
            <li title="Fuel"><i class="fa-solid fa-<?= $v['fuel_type'] === 'Electric' ? 'bolt' : ($v['fuel_type'] === 'Hybrid' || $v['fuel_type'] === 'Plug-in Hybrid' ? 'leaf' : 'gas-pump') ?>"></i><?= e($v['fuel_type']) ?></li>
            <li title="<?= e($v['exterior_color']) ?>"><span class="swatch" style="background:<?= e($v['color_hex']) ?>"></span><?= e($v['exterior_color'] ?: 'Colour') ?></li>
        </ul>
        <div class="vehicle-card-actions">
            <a href="<?= vehicle_url($v) ?>" class="btn btn-lah btn-sm">View Details</a>
            <a href="<?= vehicle_url($v) ?>#inquire" class="btn btn-ghost btn-sm"><i class="fa-regular fa-calendar-check me-1"></i>Test Drive</a>
        </div>
    </div>
</article>
