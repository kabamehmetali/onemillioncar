<?php
/**
 * Draws placeholder photos for every vehicle that does not have real ones yet:
 * a studio-lit side profile in the car's own colour, a mirrored second angle
 * and a wheel close-up. Writes uploads/vehicles/<slug>-1.jpg … -3.jpg, which
 * are the paths sql/seed.sql already references.
 *
 * Usage: php tools/make-placeholders.php [--force]
 * Existing files are kept unless --force is given, so admin uploads that
 * replaced a placeholder are never overwritten.
 */
declare(strict_types=1);
require __DIR__ . '/imagelib.php';

define('APP_ROOT', dirname(__DIR__));
require APP_ROOT . '/includes/config.php';
require APP_ROOT . '/includes/db.php';

$force = in_array('--force', $argv, true);
$dir   = APP_ROOT . '/uploads/vehicles';
@mkdir($dir, 0777, true);

const W = 1600;
const H = 1000;
const SS = 2; // supersampling factor

/**
 * Side-profile silhouette points for each body style, in a 1000 x 420 box
 * with the car facing left and its wheels resting on y = 360.
 * Returns [bodyPolygon, glassPolygons[], wheelCentres[], wheelRadius].
 */
function car_shape(string $body): array
{
    switch ($body) {
        case 'SUV':
        case 'Van':
        case 'Minivan':
        case 'Wagon':
            $poly  = [40,330, 40,260, 70,215, 190,205, 270,110, 330,90, 790,88, 880,100, 930,140, 950,215, 955,330, 930,345, 60,345];
            $glass = [[[280,205],[335,120],[520,118],[520,205]], [[535,205],[535,117],[790,115],[880,150],[900,205]]];
            $wheels = [[215, 345], [790, 345]];
            $r = 62;
            break;
        case 'Truck':
            $poly  = [40,330, 40,250, 65,205, 200,195, 275,105, 340,90, 560,90, 570,205, 960,205, 965,330, 940,345, 60,345];
            $glass = [[[285,200],[345,115],[440,112],[440,200]], [[455,200],[455,112],[555,112],[560,200]]];
            $wheels = [[215, 345], [800, 345]];
            $r = 64;
            break;
        case 'Hatchback':
            $poly  = [40,330, 40,255, 70,215, 200,205, 300,130, 380,110, 720,108, 820,125, 880,175, 905,240, 905,330, 880,345, 60,345];
            $glass = [[[300,205],[380,130],[540,128],[540,205]], [[555,205],[555,128],[720,128],[820,145],[870,205]]];
            $wheels = [[215, 345], [770, 345]];
            $r = 56;
            break;
        case 'Coupe':
            $poly  = [40,330, 40,260, 70,225, 240,212, 360,140, 460,120, 680,120, 790,150, 880,215, 950,235, 960,330, 930,345, 60,345];
            $glass = [[[360,212],[455,132],[690,132],[790,160],[850,212]]];
            $wheels = [[220, 345], [780, 345]];
            $r = 58;
            break;
        case 'Convertible':
            $poly  = [40,330, 40,260, 70,225, 250,212, 340,170, 390,185, 420,210, 940,210, 960,330, 930,345, 60,345];
            $glass = [[[340,208],[350,172],[390,185],[420,208]]];
            $wheels = [[220, 345], [780, 345]];
            $r = 58;
            break;
        default: // Sedan
            $poly  = [40,330, 40,255, 70,220, 230,208, 340,128, 440,112, 680,112, 800,130, 860,200, 950,220, 960,330, 930,345, 60,345];
            $glass = [[[340,205],[440,125],[560,123],[560,205]], [[575,205],[575,123],[690,123],[800,142],[845,205]]];
            $wheels = [[220, 345], [780, 345]];
            $r = 58;
    }
    return [$poly, $glass, $wheels, $r];
}

/**
 * Paint a car on $im. $ox,$oy is the top-left of the 1000x420 car box after
 * scaling by $s. $mirror flips the car to face right.
 */
function draw_car(GdImage $im, string $body, array $rgb, float $s, int $ox, int $oy, bool $mirror): void
{
    [$poly, $glassSet, $wheels, $r] = car_shape($body);
    $map = function (float $x, float $y) use ($s, $ox, $oy, $mirror): array {
        if ($mirror) {
            $x = 1000 - $x;
        }
        return [(int) round($ox + $x * $s), (int) round($oy + $y * $s)];
    };
    $flat = function (array $pts) use ($map): array {
        $out = [];
        for ($i = 0; $i < count($pts); $i += 2) {
            [$x, $y] = $map($pts[$i], $pts[$i + 1]);
            $out[] = $x; $out[] = $y;
        }
        return $out;
    };
    imagealphablending($im, true);

    // ground shadow
    [$sx, $sy] = $map(500, 355);
    for ($i = 12; $i >= 1; $i--) {
        imagefilledellipse($im, $sx, $sy + (int) (6 * $s), (int) (1000 * $s * ($i / 12)), (int) (90 * $s * ($i / 12)), col($im, [0, 0, 0], 118 - $i * 4));
    }

    // body with darker lower half
    $dark = rgb_mix($rgb, [0, 0, 0], 0.45);
    $body_pts = $flat($poly);
    imagefilledpolygon($im, $body_pts, col($im, $rgb));
    // lower body shading band
    $bandPts = $flat([40,300, 960,300, 960,330, 930,345, 60,345, 40,330]);
    imagefilledpolygon($im, $bandPts, col($im, $dark, 30));
    // glossy highlight: the body's own upper edge, offset downwards into a band
    $hi = rgb_mix($rgb, [255, 255, 255], 0.35);
    $upper = [];
    $lowerCorners = [];
    for ($i = 0; $i < count($poly); $i += 2) {
        if ($poly[$i + 1] === 330) {
            $lowerCorners[] = $i;
        }
    }
    if (count($lowerCorners) >= 2) {
        for ($i = $lowerCorners[0] + 2; $i < $lowerCorners[1]; $i += 2) {
            $upper[] = [$poly[$i], $poly[$i + 1]];
        }
        $band = [];
        foreach ($upper as [$x, $y]) { $band[] = $x; $band[] = $y; }
        foreach (array_reverse($upper) as [$x, $y]) { $band[] = $x; $band[] = $y + 16; }
        imagefilledpolygon($im, $flat($band), col($im, $hi, 72));
    }
    if ($body === 'Truck') {
        // bed rail and tailgate seam so the pickup reads as a pickup
        [$bx1, $by1] = $map(575, 205); [$bx2, $by2] = $map(960, 205);
        imagesetthickness($im, max(2, (int) (4 * $s)));
        imageline($im, $bx1, $by1 + (int) (10 * $s), $bx2, $by2 + (int) (10 * $s), col($im, $dark, 10));
        [$tx1, $ty1] = $map(575, 205); [$tx2, $ty2] = $map(575, 320);
        imageline($im, $tx1, $ty1, $tx2, $ty2, col($im, $dark, 10));
        imagesetthickness($im, 1);
    }

    // wheel arches (cut with background-ish dark colour), tyres and rims
    foreach ($wheels as [$wx, $wy]) {
        [$cx, $cy] = $map($wx, $wy);
        $R = (int) round($r * $s);
        imagefilledellipse($im, $cx, $cy, (int) ($R * 2.35), (int) ($R * 2.35), col($im, rgb_mix($rgb, [0, 0, 0], 0.7)));
        imagefilledellipse($im, $cx, $cy, $R * 2, $R * 2, col($im, [22, 22, 25]));
        imagefilledellipse($im, $cx, $cy, (int) ($R * 1.3), (int) ($R * 1.3), col($im, [150, 152, 158]));
        imagefilledellipse($im, $cx, $cy, (int) ($R * 1.1), (int) ($R * 1.1), col($im, [64, 65, 70]));
        for ($k = 0; $k < 5; $k++) {
            $a = $k * 2 * M_PI / 5 - M_PI / 2;
            $x2 = $cx + (int) (cos($a) * $R * 0.58);
            $y2 = $cy + (int) (sin($a) * $R * 0.58);
            imagesetthickness($im, max(2, (int) ($R * 0.16)));
            imageline($im, $cx, $cy, $x2, $y2, col($im, [190, 192, 198]));
        }
        imagesetthickness($im, 1);
        imagefilledellipse($im, $cx, $cy, (int) ($R * 0.32), (int) ($R * 0.32), col($im, [210, 212, 218]));
    }

    // glass
    foreach ($glassSet as $g) {
        $pts = [];
        foreach ($g as [$x, $y]) { $pts[] = $x; $pts[] = $y; }
        $gp = $flat($pts);
        imagefilledpolygon($im, $gp, col($im, [28, 34, 44]));
        imagefilledpolygon($im, $gp, col($im, [120, 150, 180], 95));
    }

    // door seam + handle
    $seamX = in_array($body, ['Coupe', 'Convertible'], true) ? 560 : 545;
    [$x1, $y1] = $map($seamX, 215);
    [$x2, $y2] = $map($seamX, 330);
    imageline($im, $x1, $y1, $x2, $y2, col($im, $dark, 20));
    [$hx, $hy] = $map($seamX + 40, 240);
    imagefilledrectangle($im, $hx, $hy, $hx + (int) (40 * $s), $hy + (int) (8 * $s), col($im, rgb_mix($rgb, [255,255,255], 0.25)));

    // headlight (front = left unless mirrored) and taillight
    $head = $flat([42,258, 80,240, 110,244, 80,268]);
    imagefilledpolygon($im, $head, col($im, [245, 246, 230]));
    $tail = $flat([958,232, 920,226, 916,248, 958,252]);
    imagefilledpolygon($im, $tail, col($im, [200, 30, 40]));

    // floor reflection (faint mirrored body)
    $refl = imagecreatetruecolor(imagesx($im), imagesy($im));
    imagealphablending($refl, false);
    imagesavealpha($refl, true);
    imagefill($refl, 0, 0, imagecolorallocatealpha($refl, 0, 0, 0, 127));
    imagealphablending($refl, true);
    $mirrorPts = [];
    for ($i = 0; $i < count($body_pts); $i += 2) {
        [$gx, $gy] = $map(500, 345);
        $mirrorPts[] = $body_pts[$i];
        $mirrorPts[] = $gy + (int) (($gy - $body_pts[$i + 1]) * 0.42);
    }
    imagefilledpolygon($refl, $mirrorPts, col($refl, $rgb, 112));
    imagecopy($im, $refl, 0, 0, 0, 0, imagesx($im), imagesy($im));
    imagedestroy($refl);
}

function studio_background(GdImage $im, int $w, int $h, array $tint, int $variant): void
{
    $top = rgb_mix([26, 27, 32], $tint, 0.08);
    $bot = [8, 8, 10];
    gradient($im, $top, $bot, $w, $h);
    // floor plane
    $floorY = (int) ($h * 0.72);
    imagealphablending($im, true);
    imagefilledrectangle($im, 0, $floorY, $w, $h, col($im, [14, 14, 17], 20));
    imageline($im, 0, $floorY, $w, $floorY, col($im, [60, 60, 66], 60));
    // spotlight
    $cx = $variant === 2 ? (int) ($w * 0.58) : (int) ($w * 0.46);
    glow($im, $cx, (int) ($h * 0.42), (int) ($w * 0.55), (int) ($h * 0.5), rgb_mix([120, 122, 130], $tint, 0.25), 96, 36);
    // light bars on the ceiling
    for ($i = 0; $i < 3; $i++) {
        $x1 = (int) ($w * (0.12 + $i * 0.3)) + ($variant === 2 ? 40 : 0);
        imagefilledrectangle($im, $x1, (int) ($h * 0.06), $x1 + (int) ($w * 0.16), (int) ($h * 0.06) + 6, col($im, [220, 222, 228], 60));
    }
}

function brand_stamp(GdImage $im, int $w, int $h, string $caption): void
{
    imagealphablending($im, true);
    $tag = 'LUCID AUTO HAUS';
    $ts  = text_size($tag, 20);
    text($im, $tag, $w - $ts['w'] - 44, $h - 40, 20, [190, 192, 198], 40);
    $cs = text_size($caption, 18);
    text($im, $caption, 44, $h - 40, 18, [150, 152, 158], 30);
}

$vehicles = db_all('SELECT * FROM vehicles ORDER BY id');
foreach ($vehicles as $v) {
    $rgb = hex_rgb($v['color_hex'] ?: '#3a3a3f');
    $caption = "{$v['year']} {$v['make']} {$v['model']}";
    for ($n = 1; $n <= 3; $n++) {
        $file = "$dir/{$v['slug']}-$n.jpg";
        if (is_file($file) && !$force) {
            continue;
        }
        $big = imagecreatetruecolor(W * SS, H * SS);
        studio_background($big, W * SS, H * SS, $rgb, $n);

        if ($n === 3) {
            // wheel close-up: draw the car large and let the front wheel fill the frame
            $s = 2.4 * SS;
            draw_car($big, $v['body_type'], $rgb, $s, (int) (-190 * $s) + (int) (W * SS * 0.1), (int) (H * SS * 0.05) - (int) (40 * $s), false);
        } else {
            $s = 1.35 * SS;
            $ox = (int) ((W * SS - 1000 * $s) / 2);
            $oy = (int) (H * SS * 0.50) - (int) (345 * $s) + (int) (H * SS * 0.16);
            draw_car($big, $v['body_type'], $rgb, $s, $ox, $oy, $n === 2);
        }
        $out = downsample($big, W, H);
        imagedestroy($big);
        brand_stamp($out, W, H, $caption . ($n === 3 ? ' · Detail' : ($n === 2 ? ' · Rear three-quarter' : ' · Profile')));
        save_jpeg($out, $file, 82);
        imagedestroy($out);
    }
}
echo "Done.\n";
