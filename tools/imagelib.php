<?php
/**
 * Shared GD helpers for the asset and placeholder generators.
 * Run from the CLI only: php tools/make-assets.php
 */
declare(strict_types=1);

function lib_font(): ?string
{
    foreach ([
        '/System/Library/Fonts/Supplemental/Arial Bold.ttf',
        '/Library/Fonts/Arial Bold.ttf',
        'C:/Windows/Fonts/arialbd.ttf',
        '/usr/share/fonts/truetype/dejavu/DejaVuSans-Bold.ttf',
    ] as $f) {
        if (is_file($f)) {
            return $f;
        }
    }
    return null;
}

/** @return array{0:int,1:int,2:int} */
function hex_rgb(string $hex): array
{
    $hex = ltrim($hex, '#');
    if (strlen($hex) === 3) {
        $hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
    }
    return [hexdec(substr($hex, 0, 2)), hexdec(substr($hex, 2, 2)), hexdec(substr($hex, 4, 2))];
}

function rgb_mix(array $a, array $b, float $t): array
{
    return [
        (int) round($a[0] + ($b[0] - $a[0]) * $t),
        (int) round($a[1] + ($b[1] - $a[1]) * $t),
        (int) round($a[2] + ($b[2] - $a[2]) * $t),
    ];
}

function col(GdImage $im, array $rgb, int $alpha = 0): int
{
    return imagecolorallocatealpha($im, max(0, min(255, $rgb[0])), max(0, min(255, $rgb[1])), max(0, min(255, $rgb[2])), $alpha);
}

/** Vertical gradient fill. */
function gradient(GdImage $im, array $top, array $bottom, int $w, int $h): void
{
    for ($y = 0; $y < $h; $y++) {
        $c = col($im, rgb_mix($top, $bottom, $y / max(1, $h - 1)));
        imageline($im, 0, $y, $w, $y, $c);
    }
}

/** Soft radial glow (spotlight) drawn with concentric alpha-blended ellipses. */
function glow(GdImage $im, int $cx, int $cy, int $rx, int $ry, array $rgb, int $strength = 90, int $steps = 40): void
{
    imagealphablending($im, true);
    for ($i = $steps; $i >= 1; $i--) {
        $t = $i / $steps;
        $alpha = 127 - (int) round((127 - (127 - $strength)) * (1 - $t) ** 1.6);
        $alpha = max(0, min(127, $alpha));
        imagefilledellipse($im, $cx, $cy, (int) ($rx * 2 * $t), (int) ($ry * 2 * $t), col($im, $rgb, $alpha));
    }
}

function text(GdImage $im, string $s, int $x, int $y, int $size, array $rgb, int $alpha = 0, float $angle = 0.0): void
{
    $font = lib_font();
    if ($font) {
        imagettftext($im, $size, $angle, $x, $y, col($im, $rgb, $alpha), $font, $s);
    } else {
        imagestring($im, 5, $x, $y - 12, $s, col($im, $rgb, $alpha));
    }
}

/** @return array{w:int,h:int} */
function text_size(string $s, int $size): array
{
    $font = lib_font();
    if (!$font) {
        return ['w' => strlen($s) * 9, 'h' => 15];
    }
    $b = imagettfbbox($size, 0, $font, $s);
    return ['w' => abs($b[2] - $b[0]), 'h' => abs($b[7] - $b[1])];
}

function save_jpeg(GdImage $im, string $path, int $quality = 84): void
{
    if (!is_dir(dirname($path))) {
        mkdir(dirname($path), 0777, true);
    }
    imagejpeg($im, $path, $quality);
    echo "  wrote " . str_replace(dirname(__DIR__) . '/', '', $path) . ' (' . round(filesize($path) / 1024) . " KB)\n";
}

function save_png(GdImage $im, string $path): void
{
    if (!is_dir(dirname($path))) {
        mkdir(dirname($path), 0777, true);
    }
    imagealphablending($im, false);
    imagesavealpha($im, true);
    imagepng($im, $path, 6);
    echo "  wrote " . str_replace(dirname(__DIR__) . '/', '', $path) . ' (' . round(filesize($path) / 1024) . " KB)\n";
}

function load_image(string $path): GdImage
{
    $info = getimagesize($path);
    $im = match ($info[2] ?? 0) {
        IMAGETYPE_JPEG => imagecreatefromjpeg($path),
        IMAGETYPE_PNG  => imagecreatefrompng($path),
        IMAGETYPE_GIF  => imagecreatefromgif($path),
        default        => throw new RuntimeException("Unsupported image: $path"),
    };
    if (!$im) {
        throw new RuntimeException("Cannot read $path");
    }
    return $im;
}

/** Downsample a supersampled canvas to its final size (cheap anti-aliasing). */
function downsample(GdImage $big, int $w, int $h): GdImage
{
    $out = imagecreatetruecolor($w, $h);
    imagealphablending($out, false);
    imagesavealpha($out, true);
    imagecopyresampled($out, $big, 0, 0, 0, 0, $w, $h, imagesx($big), imagesy($big));
    return $out;
}
