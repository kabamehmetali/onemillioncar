<?php
require __DIR__ . '/includes/app.php';

$facets  = inventory_facets();
$filters = [
    'q'            => trim((string) ($_GET['q'] ?? '')),
    'make'         => (string) ($_GET['make'] ?? ''),
    'body_type'    => (string) ($_GET['body_type'] ?? ''),
    'transmission' => (string) ($_GET['transmission'] ?? ''),
    'fuel_type'    => (string) ($_GET['fuel_type'] ?? ''),
    'drivetrain'   => (string) ($_GET['drivetrain'] ?? ''),
    'condition'    => (string) ($_GET['condition'] ?? ''),
    'min_price'    => (int) ($_GET['min_price'] ?? 0),
    'max_price'    => (int) ($_GET['max_price'] ?? 0),
    'min_year'     => (int) ($_GET['min_year'] ?? 0),
    'max_year'     => (int) ($_GET['max_year'] ?? 0),
    'max_km'       => (int) ($_GET['max_km'] ?? 0),
    'sort'         => (string) ($_GET['sort'] ?? 'featured'),
    'status'       => (($_GET['status'] ?? '') === 'sold') ? 'sold' : 'available',
];
$page    = max(1, (int) ($_GET['page'] ?? 1));
$perPage = 9;
$result  = vehicles_search($filters, $page, $perPage);
$total   = $result['total'];
$pages   = (int) ceil($total / $perPage);
if ($page > 1 && $page > $pages) {
    redirect('inventory' . query_with(['page' => null]));
}

$active = [];
if ($filters['q'] !== '')            $active['q'] = 'Search: ' . $filters['q'];
if ($filters['make'] !== '')         $active['make'] = $filters['make'];
if ($filters['body_type'] !== '')    $active['body_type'] = $filters['body_type'];
if ($filters['transmission'] !== '') $active['transmission'] = $filters['transmission'];
if ($filters['fuel_type'] !== '')    $active['fuel_type'] = $filters['fuel_type'];
if ($filters['drivetrain'] !== '')   $active['drivetrain'] = $filters['drivetrain'];
if ($filters['condition'] !== '')    $active['condition'] = CONDITIONS[$filters['condition']] ?? $filters['condition'];
if ($filters['min_price'])           $active['min_price'] = 'From ' . money($filters['min_price']);
if ($filters['max_price'])           $active['max_price'] = 'Up to ' . money($filters['max_price']);
if ($filters['min_year'])            $active['min_year'] = $filters['min_year'] . ' or newer';
if ($filters['max_year'])            $active['max_year'] = $filters['max_year'] . ' or older';
if ($filters['max_km'])              $active['max_km'] = 'Under ' . number($filters['max_km']) . ' km';
if ($filters['status'] === 'sold')   $active['status'] = 'Recently sold';

$pageTitle       = $filters['make'] !== '' ? $filters['make'] . ' Inventory' : 'Inventory';
$metaDescription = 'Browse ' . $facets['count'] . ' inspected pre-owned vehicles from ' . site_name() . ' in Toronto and the GTA. Filter by make, body style, price and more.';
$hero = [
    'image'  => 'assets/img/hero/inventory.jpg',
    'size'   => 'sm',
    'kicker' => 'Inspected · Certified · Market-priced',
    'title'  => e(setting('hero_inventory_title', 'Current Inventory')),
    'text'   => setting('hero_inventory_text'),
    'crumbs' => ['Inventory' => null],
    'focus'  => '60% 25%',
];
require __DIR__ . '/includes/header.php';
require __DIR__ . '/includes/hero.php';

$priceSteps = price_steps();
?>

<section class="section-sm section-dark">
    <div class="container">
        <div class="row g-4">
            <aside class="col-lg-3">
                <form class="filter-panel" id="filterForm" method="get" action="<?= url('inventory') ?>">
                    <input type="hidden" name="sort" value="<?= e($filters['sort']) ?>">
                    <h2>Filter <a href="<?= url('inventory') ?>">Reset</a></h2>
                    <div class="mb-3">
                        <label for="f-q">Keyword</label>
                        <div class="input-group">
                            <input type="search" class="form-control" id="f-q" name="q" value="<?= e($filters['q']) ?>" placeholder="Make, model, colour…">
                            <button class="btn btn-lah" type="submit" aria-label="Search"><i class="fa-solid fa-magnifying-glass"></i></button>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="f-make">Make</label>
                        <select class="form-select" id="f-make" name="make">
                            <option value="">All makes</option>
                            <?php foreach ($facets['makes'] as $make => $n): ?>
                                <option value="<?= e($make) ?>"<?= $filters['make'] === $make ? ' selected' : '' ?>><?= e($make) ?> (<?= $n ?>)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="f-body">Body style</label>
                        <select class="form-select" id="f-body" name="body_type">
                            <option value="">All body styles</option>
                            <?php foreach ($facets['body_types'] as $bt => $n): ?>
                                <option value="<?= e($bt) ?>"<?= $filters['body_type'] === $bt ? ' selected' : '' ?>><?= e($bt) ?> (<?= $n ?>)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label for="f-minp">Min price</label>
                            <select class="form-select" id="f-minp" name="min_price">
                                <option value="">Any</option>
                                <?php foreach ($priceSteps as $p): ?><option value="<?= $p ?>"<?= $filters['min_price'] === $p ? ' selected' : '' ?>><?= money($p) ?></option><?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-6">
                            <label for="f-maxp">Max price</label>
                            <select class="form-select" id="f-maxp" name="max_price">
                                <option value="">Any</option>
                                <?php foreach ($priceSteps as $p): ?><option value="<?= $p ?>"<?= $filters['max_price'] === $p ? ' selected' : '' ?>><?= money($p) ?></option><?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label for="f-miny">Min year</label>
                            <select class="form-select" id="f-miny" name="min_year">
                                <option value="">Any</option>
                                <?php for ($y = (int) $facets['years']['hi']; $y >= (int) $facets['years']['lo']; $y--): ?><option value="<?= $y ?>"<?= $filters['min_year'] === $y ? ' selected' : '' ?>><?= $y ?></option><?php endfor; ?>
                            </select>
                        </div>
                        <div class="col-6">
                            <label for="f-maxy">Max year</label>
                            <select class="form-select" id="f-maxy" name="max_year">
                                <option value="">Any</option>
                                <?php for ($y = (int) $facets['years']['hi']; $y >= (int) $facets['years']['lo']; $y--): ?><option value="<?= $y ?>"<?= $filters['max_year'] === $y ? ' selected' : '' ?>><?= $y ?></option><?php endfor; ?>
                            </select>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="f-km">Max kilometres</label>
                        <select class="form-select" id="f-km" name="max_km">
                            <option value="">Any</option>
                            <?php foreach ([20000, 30000, 40000, 50000, 75000, 100000, 150000] as $km): ?><option value="<?= $km ?>"<?= $filters['max_km'] === $km ? ' selected' : '' ?>>Under <?= number($km) ?> km</option><?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="f-trans">Transmission</label>
                        <select class="form-select" id="f-trans" name="transmission">
                            <option value="">Any</option>
                            <?php foreach (TRANSMISSIONS as $t): ?><option value="<?= e($t) ?>"<?= $filters['transmission'] === $t ? ' selected' : '' ?>><?= e($t) ?></option><?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="f-fuel">Fuel</label>
                        <select class="form-select" id="f-fuel" name="fuel_type">
                            <option value="">Any</option>
                            <?php foreach (FUEL_TYPES as $t): ?><option value="<?= e($t) ?>"<?= $filters['fuel_type'] === $t ? ' selected' : '' ?>><?= e($t) ?></option><?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="f-drive">Drivetrain</label>
                        <select class="form-select" id="f-drive" name="drivetrain">
                            <option value="">Any</option>
                            <?php foreach (DRIVETRAINS as $t): ?><option value="<?= e($t) ?>"<?= $filters['drivetrain'] === $t ? ' selected' : '' ?>><?= e($t) ?></option><?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="f-cond">Condition</label>
                        <select class="form-select" id="f-cond" name="condition">
                            <option value="">Any</option>
                            <?php foreach (CONDITIONS as $k => $label): ?><option value="<?= e($k) ?>"<?= $filters['condition'] === $k ? ' selected' : '' ?>><?= e($label) ?></option><?php endforeach; ?>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-lah w-100">Apply filters</button>
                    <a href="<?= url('inventory?status=sold') ?>" class="d-block text-center small mt-3 text-silver">Recently sold</a>
                </form>
            </aside>

            <div class="col-lg-9">
                <div class="inventory-toolbar">
                    <div class="inventory-count">Showing <strong><?= $total ? (($page - 1) * $perPage + 1) . '–' . min($total, $page * $perPage) : 0 ?></strong> of <strong><?= $total ?></strong> vehicle<?= $total === 1 ? '' : 's' ?></div>
                    <label class="inventory-sort">Sort by
                        <select class="form-select form-select-sm" id="sortSelect" style="width:auto">
                            <?php foreach (['featured' => 'Featured', 'newest' => 'Newest arrivals', 'price_asc' => 'Price: low to high', 'price_desc' => 'Price: high to low', 'year_desc' => 'Year: newest', 'km_asc' => 'Kilometres: lowest'] as $k => $label): ?>
                                <option value="<?= $k ?>"<?= $filters['sort'] === $k ? ' selected' : '' ?>><?= $label ?></option>
                            <?php endforeach; ?>
                        </select>
                    </label>
                </div>

                <?php if ($active): ?>
                    <div class="active-filters">
                        <?php foreach ($active as $key => $label): ?>
                            <span class="active-filter"><?= e($label) ?> <a href="<?= e(url('inventory') . query_with([$key => null, 'page' => null])) ?>" aria-label="Remove filter"><i class="fa-solid fa-xmark"></i></a></span>
                        <?php endforeach; ?>
                        <a href="<?= url('inventory') ?>" class="active-filter">Clear all</a>
                    </div>
                <?php endif; ?>

                <?php if ($result['items']): ?>
                    <div class="vehicle-grid">
                        <?php foreach ($result['items'] as $v): ?>
                            <?php require __DIR__ . '/includes/vehicle-card.php'; ?>
                        <?php endforeach; ?>
                    </div>
                    <?= pagination($page, $total, $perPage) ?>
                <?php else: ?>
                    <div class="empty-state">
                        <i class="fa-solid fa-car-side"></i>
                        <h3>No vehicles match those filters</h3>
                        <p class="text-silver">Try widening your search — or tell me what you're after and I'll source it for you.</p>
                        <div class="d-flex gap-2 justify-content-center flex-wrap">
                            <a href="<?= url('inventory') ?>" class="btn btn-outline-light">Clear filters</a>
                            <a href="<?= url('contact?subject=sourcing') ?>" class="btn btn-lah">Request a vehicle</a>
                        </div>
                    </div>
                <?php endif; ?>

                <div class="info-card mt-5 d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3">
                    <div>
                        <h3 class="mb-1"><i class="fa-solid fa-crosshairs"></i>Don't see what you're looking for?</h3>
                        <p class="mb-0 text-silver">New vehicles arrive weekly and most requests are sourced within one to three weeks. Tell me the spec.</p>
                    </div>
                    <a href="<?= url('contact?subject=sourcing') ?>" class="btn btn-lah flex-shrink-0">Request a vehicle</a>
                </div>
            </div>
        </div>
    </div>
</section>

<?php require __DIR__ . '/includes/cta-band.php'; ?>
<?php require __DIR__ . '/includes/footer.php'; ?>
