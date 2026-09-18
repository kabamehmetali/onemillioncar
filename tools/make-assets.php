<?php
/**
 * Prepares the supplied artwork for the web:
 *   - trims the transparent margins off the logo and cuts out the LAH monogram
 *   - re-encodes the 1.8 MB hero PNGs as JPEGs
 *   - builds the favicon, the Open Graph card and the "no photo" fallback
 *
 * Usage: php tools/make-assets.php   (safe to re-run)
 */
declare(strict_types=1);
require __DIR__ . '/imagelib.php';

$root = dirname(__DIR__);
$src  = $root . '/images';
$out  = $root . '/assets/img';
@mkdir($out . '/hero', 0777, true);

echo "Logo\n";
$logo = load_image($src . '/logonobg.png');
imagealphablending($logo, false);
imagesavealpha($logo, true);
[$w, $h] = [imagesx($logo), imagesy($logo)];

// Bounding box of visible pixels and per-row occupancy (to split the monogram from the wordmark)
$minX = $w; $minY = $h; $maxX = 0; $maxY = 0;
$rowHas = array_fill(0, $h, false);
for ($y = 0; $y < $h; $y += 1) {
    for ($x = 0; $x < $w; $x += 1) {
        $a = (imagecolorat($logo, $x, $y) >> 24) & 0x7F;
        if ($a < 120) { // 127 = fully transparent
            $rowHas[$y] = true;
            if ($x < $minX) $minX = $x;
            if ($x > $maxX) $maxX = $x;
            if ($y < $minY) $minY = $y;
            if ($y > $maxY) $maxY = $y;
        }
    }
}
$pad = 12;
$cropW = $maxX - $minX + 1 + $pad * 2;
$cropH = $maxY - $minY + 1 + $pad * 2;
$trim = imagecreatetruecolor($cropW, $cropH);
imagealphablending($trim, false);
imagesavealpha($trim, true);
imagefill($trim, 0, 0, imagecolorallocatealpha($trim, 0, 0, 0, 127));
imagecopy($trim, $logo, 0, 0, $minX - $pad, $minY - $pad, $cropW, $cropH);
save_png($trim, $out . '/logo.png');

// Find the widest run of empty rows inside the trimmed box: monogram above, wordmark below
$bestStart = 0; $bestLen = 0; $runStart = null;
for ($y = $minY; $y <= $maxY + 1; $y++) {
    $empty = $y > $maxY ? false : !$rowHas[$y];
    if ($empty && $runStart === null) {
        $runStart = $y;
    } elseif (!$empty && $runStart !== null) {
        if ($y - $runStart > $bestLen) { $bestLen = $y - $runStart; $bestStart = $runStart; }
        $runStart = null;
    }
}
$splitY = $bestStart + intdiv($bestLen, 2);
$markMinX = $w; $markMaxX = 0;
for ($y = $minY; $y < $splitY; $y++) {
    for ($x = $minX; $x <= $maxX; $x++) {
        if ((((imagecolorat($logo, $x, $y) >> 24) & 0x7F)) < 120) {
            if ($x < $markMinX) $markMinX = $x;
            if ($x > $markMaxX) $markMaxX = $x;
        }
    }
}
$mw = $markMaxX - $markMinX + 1 + $pad * 2;
$mh = $splitY - $minY + $pad * 2;
$mark = imagecreatetruecolor($mw, $mh);
imagealphablending($mark, false);
imagesavealpha($mark, true);
imagefill($mark, 0, 0, imagecolorallocatealpha($mark, 0, 0, 0, 127));
imagecopy($mark, $logo, 0, 0, $markMinX - $pad, $minY - $pad, $mw, $mh);
save_png($mark, $out . '/logo-mark.png');

// Favicon: monogram on a black rounded square
foreach ([64 => 'favicon.png', 180 => 'apple-touch-icon.png', 512 => 'icon-512.png'] as $size => $name) {
    $ico = imagecreatetruecolor($size, $size);
    imagealphablending($ico, false);
    imagesavealpha($ico, true);
    imagefill($ico, 0, 0, imagecolorallocatealpha($ico, 0, 0, 0, 127));
    imagealphablending($ico, true);
    $r = (int) round($size * 0.2);
    $bg = imagecolorallocate($ico, 8, 8, 10);
    imagefilledrectangle($ico, $r, 0, $size - $r - 1, $size - 1, $bg);
    imagefilledrectangle($ico, 0, $r, $size - 1, $size - $r - 1, $bg);
    foreach ([[$r, $r], [$size - $r - 1, $r], [$r, $size - $r - 1], [$size - $r - 1, $size - $r - 1]] as [$cx, $cy]) {
        imagefilledellipse($ico, $cx, $cy, $r * 2, $r * 2, $bg);
    }
    $inner = (int) round($size * 0.72);
    $scale = min($inner / $mw, $inner / $mh);
    $dw = (int) round($mw * $scale);
    $dh = (int) round($mh * $scale);
    imagecopyresampled($ico, $mark, intdiv($size - $dw, 2), intdiv($size - $dh, 2), 0, 0, $dw, $dh, $mw, $mh);
    save_png($ico, $out . '/' . $name);
}

echo "Hero images\n";
$heroes = [
    'homehero.png'     => 'home.jpg',
    'homepagehero.png' => 'home-day.jpg',
    'carshero.png'     => 'inventory.jpg',
    'aboutushero.png'  => 'about.jpg',
    'aveneu.png'       => 'services.jpg',
    'hero3.jpeg'       => 'showroom.jpg',
];
foreach ($heroes as $from => $to) {
    $im = load_image($src . '/Heroimages/' . $from);
    $iw = imagesx($im); $ih = imagesy($im);
    $tw = min(1920, $iw); $th = (int) round($ih * $tw / $iw);
    $canvas = imagecreatetruecolor($tw, $th);
    imagecopyresampled($canvas, $im, 0, 0, 0, 0, $tw, $th, $iw, $ih);
    save_jpeg($canvas, $out . '/hero/' . $to, 82);
}

echo "Portrait\n";
$face = load_image($src . '/myface.jpeg');
$sq = imagecreatetruecolor(900, 900);
imagecopyresampled($sq, $face, 0, 0, 0, 0, 900, 900, imagesx($face), imagesy($face));
save_jpeg($sq, $out . '/agent.jpg', 86);

echo "Open Graph card\n";
$og = imagecreatetruecolor(1200, 630);
$show = load_image($src . '/Heroimages/hero3.jpeg');
$sw = imagesx($show); $sh = imagesy($show);
$scale = max(1200 / $sw, 630 / $sh);
$dw = (int) ceil($sw * $scale); $dh = (int) ceil($sh * $scale);
imagecopyresampled($og, $show, intdiv(1200 - $dw, 2), intdiv(630 - $dh, 2), 0, 0, $dw, $dh, $sw, $sh);
imagealphablending($og, true);
imagefilledrectangle($og, 0, 0, 1200, 630, col($og, [0, 0, 0], 70));
$lw = 360; $lh = (int) round($cropH * $lw / $cropW);
imagecopyresampled($og, $trim, intdiv(1200 - $lw, 2), intdiv(630 - $lh, 2) - 20, 0, 0, $lw, $lh, $cropW, $cropH);
save_jpeg($og, $out . '/og-default.jpg', 82);

echo "No-photo fallback\n";
$np = imagecreatetruecolor(1600, 1000);
gradient($np, [30, 31, 36], [12, 12, 14], 1600, 1000);
$mw2 = 260; $mh2 = (int) round($mh * $mw2 / $mw);
imagealphablending($np, true);
imagecopyresampled($np, $mark, intdiv(1600 - $mw2, 2), 330, 0, 0, $mw2, $mh2, $mw, $mh);
$label = 'PHOTOS COMING SOON';
$ts = text_size($label, 26);
text($np, $label, intdiv(1600 - $ts['w'], 2), 700, 26, [150, 152, 158]);
save_jpeg($np, $out . '/no-photo.jpg', 80);

echo "Done.\n";
