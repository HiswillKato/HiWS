<?php
declare(strict_types=1);

require __DIR__ . '/../app/init.php';

$tool = tool('image-converter');

$error = '';
$src = '';
$info = '';
$format = posted('format') ?: 'png';
$quality = posted('quality') !== '' ? (int) posted('quality') : 88;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $file = $_FILES['image'] ?? null;
    $quality = max(1, min(100, $quality));
    if (!is_array($file) || ($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) $error = 'Choose an image file.';
    elseif (($file['error'] ?? UPLOAD_ERR_OK) !== UPLOAD_ERR_OK || ($file['size'] ?? 0) > 5 * 1024 * 1024) $error = 'Upload failed or image is too large (limit 5 MB).';
    else {
        $tmp = (string) $file['tmp_name']; $det = @getimagesize($tmp);
        $map = [IMAGETYPE_JPEG=>'jpeg', IMAGETYPE_PNG=>'png', IMAGETYPE_GIF=>'gif', IMAGETYPE_WEBP=>'webp', IMAGETYPE_BMP=>'bmp'];
        if ($det === false || !isset($map[$det[2]])) $error = 'Unsupported image type.';
        elseif ($det[0] * $det[1] > 40_000_000) $error = 'Image dimensions are too large to process safely.';
        else {
            $im = match ($det[2]) {
                IMAGETYPE_JPEG => @imagecreatefromjpeg($tmp),
                IMAGETYPE_PNG => @imagecreatefrompng($tmp),
                IMAGETYPE_GIF => @imagecreatefromgif($tmp),
                IMAGETYPE_WEBP => function_exists('imagecreatefromwebp') ? @imagecreatefromwebp($tmp) : null,
                IMAGETYPE_BMP => function_exists('imagecreatefrombmp') ? @imagecreatefrombmp($tmp) : null,
                default => null,
            };
            if (!$im) $error = 'Could not read the image.';
            else {
                if (in_array($format, ['jpeg','bmp'], true)) {
                    $bg = imagecreatetruecolor(imagesx($im), imagesy($im));
                    $white = imagecolorallocate($bg, 255,255,255); imagefill($bg,0,0,$white);
                    imagecopy($bg,$im,0,0,0,0,imagesx($im),imagesy($im)); imagedestroy($im); $im = $bg;
                } else { imagealphablending($im, in_array($format,['gif'],true)); imagesavealpha($im, true); }
                ob_start();
                $ok = match ($format) {
                    'jpeg' => imagejpeg($im, null, $quality),
                    'gif' => imagegif($im),
                    'webp' => function_exists('imagewebp') ? imagewebp($im, null, $quality) : false,
                    'bmp' => function_exists('imagebmp') ? imagebmp($im) : false,
                    default => imagepng($im),
                };
                $data = ob_get_clean(); imagedestroy($im);
                if (!$ok || !is_string($data) || $data === '') $error = 'Could not encode the image in that format on this server.';
                else {
                    $mime = match($format){'jpeg'=>'image/jpeg','gif'=>'image/gif','webp'=>'image/webp','bmp'=>'image/bmp',default=>'image/png'};
                    $src = 'data:' . $mime . ';base64,' . base64_encode($data);
                    $info = $det[0].'×'.$det[1].', '.$map[$det[2]].' → '.strtoupper($format).', '.bytes(strlen($data));
                }
            }
        }
    }
}

require __DIR__ . '/../app/parts/tool.php';
?>

<form method="post" enctype="multipart/form-data" class="card bg-body rounded-4 border shadow-sm p-4">
    <label class="form-label fw-semibold" for="image">Image file</label>
    <input class="form-control" type="file" id="image" name="image" accept="image/*">
    <div class="row g-3 mt-3">
        <div class="col-md-6"><label class="form-label fw-semibold" for="format">Convert to</label>
            <select class="form-select" id="format" name="format">
                <?php foreach (['png'=>'PNG','jpeg'=>'JPEG','webp'=>'WebP','gif'=>'GIF','bmp'=>'BMP'] as $k=>$v): ?><option value="<?= e($k) ?>"<?= $format===$k?' selected':'' ?>><?= e($v) ?></option><?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-6"><label class="form-label fw-semibold" for="quality">Quality (JPEG/WebP)</label><input class="form-control" id="quality" name="quality" type="number" min="1" max="100" value="<?= e((string)$quality) ?>"></div>
    </div>
    <div class="form-text mt-2">Max upload 5 MB. Processed on this server, not stored. Transparency may be lost when converting to JPEG/BMP.</div>
    <button class="btn btn-primary mt-3" type="submit">Convert image</button>
</form>

<?php if ($error !== ''): ?><?= notice($error) ?>
<?php elseif ($src !== ''): ?>
    <p class="text-body-secondary mt-4 mb-0"><?= e($info) ?></p>
    <div class="card bg-body rounded-4 border shadow-sm mt-3">
        <div class="card-header bg-transparent d-flex justify-content-between align-items-center"><span class="fw-semibold">Converted image</span><a class="btn btn-sm btn-outline-secondary" href="<?= e($src) ?>" download="converted.<?= e($format==='jpeg'?'jpg':$format) ?>">Download</a></div>
        <div class="card-body text-center"><img src="<?= e($src) ?>" alt="Converted image" class="img-fluid rounded border" style="max-height:480px"></div>
    </div>
<?php endif; ?>

<?php require __DIR__ . '/../app/parts/explain.php'; ?>
