<?php
/**
 * Image upload handling for vehicle photos.
 * Every accepted file is decoded and re-encoded with GD, which strips any
 * embedded payload, normalises orientation and caps the size at 1600px.
 */
declare(strict_types=1);

const UPLOAD_MAX_BYTES = 12 * 1024 * 1024;

/**
 * @param array $file one entry from $_FILES (already split for multi-uploads)
 * @return string|false relative path such as uploads/vehicles/slug-20260917-ab12cd.jpg
 */
function save_vehicle_image(array $file, string $slug, ?string &$error = null): string|false
{
    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
        return false;
    }
    if ($file['error'] !== UPLOAD_ERR_OK) {
        $error = 'Upload failed (code ' . $file['error'] . ').';
        return false;
    }
    if ($file['size'] > UPLOAD_MAX_BYTES) {
        $error = 'File is larger than 12 MB.';
        return false;
    }
    if (!is_uploaded_file($file['tmp_name'])) {
        $error = 'Invalid upload.';
        return false;
    }
    $mime = (new finfo(FILEINFO_MIME_TYPE))->file($file['tmp_name']) ?: '';
    $info = @getimagesize($file['tmp_name']);
    if (!$info || !in_array($mime, ['image/jpeg', 'image/png', 'image/webp', 'image/gif'], true)) {
        $error = 'Only JPEG, PNG, WebP or GIF images are accepted.';
        return false;
    }
    $src = match ($mime) {
        'image/jpeg' => @imagecreatefromjpeg($file['tmp_name']),
        'image/png'  => @imagecreatefrompng($file['tmp_name']),
        'image/gif'  => @imagecreatefromgif($file['tmp_name']),
        'image/webp' => function_exists('imagecreatefromwebp') ? @imagecreatefromwebp($file['tmp_name']) : false,
        default      => false,
    };
    if (!$src) {
        $error = 'The image could not be read' . ($mime === 'image/webp' ? ' (this server cannot decode WebP — please upload JPEG or PNG)' : '') . '.';
        return false;
    }

    // Respect EXIF orientation from phones
    if ($mime === 'image/jpeg' && function_exists('exif_read_data')) {
        $exif = @exif_read_data($file['tmp_name']);
        $o = (int) ($exif['Orientation'] ?? 1);
        if ($o === 3)     $src = imagerotate($src, 180, 0);
        elseif ($o === 6) $src = imagerotate($src, -90, 0);
        elseif ($o === 8) $src = imagerotate($src, 90, 0);
    }

    $w = imagesx($src);
    $h = imagesy($src);
    $max = 1600;
    if ($w > $max || $h > $max) {
        $scale = min($max / $w, $max / $h);
        $nw = (int) round($w * $scale);
        $nh = (int) round($h * $scale);
        $dst = imagecreatetruecolor($nw, $nh);
        imagefill($dst, 0, 0, imagecolorallocate($dst, 255, 255, 255));
        imagecopyresampled($dst, $src, 0, 0, 0, 0, $nw, $nh, $w, $h);
        imagedestroy($src);
        $src = $dst;
    } elseif ($mime !== 'image/jpeg') {
        // flatten transparency onto white
        $dst = imagecreatetruecolor($w, $h);
        imagefill($dst, 0, 0, imagecolorallocate($dst, 255, 255, 255));
        imagecopy($dst, $src, 0, 0, 0, 0, $w, $h);
        imagedestroy($src);
        $src = $dst;
    }

    $dir = APP_ROOT . '/uploads/vehicles';
    if (!is_dir($dir) && !mkdir($dir, 0777, true)) {
        $error = 'Upload folder could not be created.';
        return false;
    }
    if (!is_writable($dir)) {
        $error = 'Upload folder is not writable (uploads/vehicles). On this machine run: chmod -R o+w uploads';
        return false;
    }
    $name = slugify($slug) . '-' . date('Ymd-His') . '-' . substr(bin2hex(random_bytes(4)), 0, 6) . '.jpg';
    if (!imagejpeg($src, "$dir/$name", 86)) {
        $error = 'The image could not be saved.';
        imagedestroy($src);
        return false;
    }
    imagedestroy($src);
    @chmod("$dir/$name", 0666);
    return 'uploads/vehicles/' . $name;
}

/** Split $_FILES['photos'] (multiple) into one entry per file. */
function files_array(array $files): array
{
    $out = [];
    if (!isset($files['name']) || !is_array($files['name'])) {
        return $files ? [$files] : [];
    }
    foreach ($files['name'] as $i => $name) {
        $out[] = ['name' => $name, 'type' => $files['type'][$i], 'tmp_name' => $files['tmp_name'][$i], 'error' => $files['error'][$i], 'size' => $files['size'][$i]];
    }
    return $out;
}

function delete_upload(string $relative): void
{
    $relative = ltrim($relative, '/');
    if (str_starts_with($relative, 'uploads/vehicles/') && !str_contains($relative, '..')) {
        $file = APP_ROOT . '/' . $relative;
        if (is_file($file)) {
            @unlink($file);
        }
    }
}
