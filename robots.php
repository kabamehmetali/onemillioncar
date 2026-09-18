<?php
require __DIR__ . '/includes/app.php';
header('Content-Type: text/plain; charset=UTF-8');
echo "User-agent: *\nDisallow: " . url('admin/') . "\nAllow: /\n\nSitemap: " . absolute_url('sitemap.xml') . "\n";
