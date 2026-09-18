<?php
require __DIR__ . '/includes/app.php';
header('Content-Type: application/xml; charset=UTF-8');
$urls = [
    ['', '1.0', 'daily'], ['inventory', '0.9', 'daily'], ['about', '0.7', 'monthly'], ['services', '0.7', 'monthly'],
    ['financing', '0.7', 'monthly'], ['trade-in', '0.7', 'monthly'], ['testimonials', '0.5', 'monthly'], ['faq', '0.5', 'monthly'],
    ['contact', '0.6', 'monthly'], ['privacy', '0.2', 'yearly'],
];
echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
foreach ($urls as [$path, $prio, $freq]) {
    echo '  <url><loc>' . e(absolute_url($path)) . '</loc><changefreq>' . $freq . '</changefreq><priority>' . $prio . '</priority></url>' . "\n";
}
foreach (db_all('SELECT slug, updated_at FROM vehicles WHERE status IN ("available","pending") ORDER BY id') as $row) {
    echo '  <url><loc>' . e(absolute_url('inventory/' . $row['slug'])) . '</loc><lastmod>' . date('Y-m-d', strtotime($row['updated_at'])) . '</lastmod><changefreq>weekly</changefreq><priority>0.8</priority></url>' . "\n";
}
echo '</urlset>';
