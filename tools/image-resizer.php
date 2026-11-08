<?php
declare(strict_types=1);

require __DIR__ . '/../app/init.php';

$tool = tool('image-resizer');

$error = '';
$src = '';
$info = '';
$width = posted('width') !== '' ? (int) posted('width') : 800;
$height = posted('height') !== '' ? (int) posted('height') : 0;
$keep = $_SERVER['REQUEST_METHOD'] !== 'POST' || isset($_POST['keep']);
$format = posted('format') ?: 'keep';

function img_load(string $tmp, int $type): ?GdImage
{
    return match ($type) {
        IMAGETYPE_JPEG => @imagecreatefromjpeg($tmp),
        IMAGETYPE_PNG => @imagecreatefrompng($tmp),
        IMAGETYPE_GIF => @imagecreatefromgif($tmp),
        IMAGETYPE_WEBP => function_exists('imagecreatefromwebp') ? @imagecreatefromwebp($tmp) : null,
        IMAGETYPE_BMP => function_exists('imagecreatefrombmp') ? @imagecreatefrombmp($tmp) : null,
        default => null,
    };
}

function img_dump(GdImage $im, string $fmt): ?string
{
    ob_start();
    $ok = match ($fmt) {
        'jpeg' => imagejpeg($im, null, 88),
        'gif' => imagegif($im),
        'webp' => function_exists('imagewebp') ? imagewebp($im, null, 88) : false,
        'bmp' => function_exists('imagebmp') ? imagebmp($im) : false,
        default => imagepng($im),
    };
    $data = ob_get_clean();
    return $ok && is_string($data) && $data !== '' ? $data : null;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $file = $_FILES['image'] ?? null;
    $width = max(1, min(8000, $width));
    $height = max(0, min(8000, $height));
    if (!is_array($file) || ($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
        $error = 'Choose an image file.';
    } elseif (($file['error'] ?? UPLOAD_ERR_OK) !== UPLOAD_ERR_OK || ($file['size'] ?? 0) > 5 * 1024 * 1024) {
        $error = 'Upload failed or image is too large (limit 5 MB).';
    } else {
        $tmp = (string) $file['tmp_name'];
        $det = @getimagesize($tmp);
        $map = [IMAGETYPE_JPEG=>'jpeg', IMAGETYPE_PNG=>'png', IMAGETYPE_GIF=>'gif', IMAGETYPE_WEBP=>'webp', IMAGETYPE_BMP=>'bmp'];
        if ($det === false || !isset($map[$det[2]])) {
            $error = 'Unsupported image type. Use JPEG, PNG, GIF, WebP or BMP.';
        } elseif ($det[0] * $det[1] > 40_000_000) {
            $error = 'Image dimensions are too large to process safely.';
        } else {
            $im = img_load($tmp, $det[2]);
            if (!$im) { $error = 'Could not read the image.'; }
            else {
                $ow = imagesx($im); $oh = imagesy($im);
                $nw = $width; $nh = $height;
                if ($keep || $nh <= 0) { $nh = max(1, (int) round($ow > 0 ? $nw * ($oh / $ow) : $nw)); }
                $dst = imagecreatetruecolor($nw, $nh);
                if (in_array($map[$det[2]], ['png','gif','webp'], true)) { imagealphablending($dst, false); imagesavealpha($dst, true); $t = imagecolorallocatealpha($dst, 0,0,0,127); imagefilledrectangle($dst,0,0,$nw,$nh,$t); }
                imagecopyresampled($dst, $im, 0,0,0,0, $nw,$nh, $ow,$oh);
                $fmt = $format === 'keep' ? $map[$det[2]] : $format;
                $data = img_dump($dst, $fmt);
                imagedestroy($im); imagedestroy($dst);
                if ($data === null) { $error = 'Could not encode the resized image in that format.'; }
                else {
                    $mime = match($fmt){'jpeg'=>'image/jpeg','gif'=>'image/gif','webp'=>'image/webp','bmp'=>'image/bmp',default=>'image/png'};
                    $src = 'data:' . $mime . ';base64,' . base64_encode($data);
                    $info = $ow . '×' . $oh . ' → ' . $nw . '×' . $nh . ', ' . strtoupper($fmt) . ', ' . bytes(strlen($data));
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
    <div class="row g-3 mt-1">
        <div class="col-md-3"><label class="form-label fw-semibold" for="width">Width (px)</label><input class="form-control" id="width" name="width" type="number" min="1" max="8000" value="<?= e((string)$width) ?>"></div>
        <div class="col-md-3"><label class="form-label fw-semibold" for="height">Height (px)</label><input class="form-control" id="height" name="height" type="number" min="0" max="8000" value="<?= e((string)$height) ?>"></div>
        <div class="col-md-3"><label class="form-label fw-semibold" for="format">Output format</label>
            <select class="form-select" id="format" name="format">
                <?php foreach (['keep'=>'Keep original','png'=>'PNG','jpeg'=>'JPEG','webp'=>'WebP','gif'=>'GIF','bmp'=>'BMP'] as $k=>$v): ?><option value="<?= e($k) ?>"<?= $format===$k?' selected':'' ?>><?= e($v) ?></option><?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-3 d-flex align-items-end"><div class="form-check mb-2"><input class="form-check-input" type="checkbox" name="keep" value="1" id="keep"<?= $keep?' checked':'' ?>><label class="form-check-label" for="keep">Keep aspect ratio</label></div></div>
    </div>
    <div class="form-text mt-2">Max upload 5 MB. Height 0 = auto from width. Processed on this server, not stored.</div>
    <button class="btn btn-primary mt-3" type="submit">Resize image</button>
</form>

<?php if ($error !== ''): ?><?= notice($error) ?>
<?php elseif ($src !== ''): ?>
    <p class="text-body-secondary mt-4 mb-0"><?= e($info) ?></p>
    <div class="card bg-body rounded-4 border shadow-sm mt-3">
        <div class="card-header bg-transparent d-flex justify-content-between align-items-center"><span class="fw-semibold">Resized image</span><a class="btn btn-sm btn-outline-secondary" href="<?= e($src) ?>" download="resized-image">Download</a></div>
        <div class="card-body text-center"><img src="<?= e($src) ?>" alt="Resized image" class="img-fluid rounded border" style="max-height:480px"></div>
    </div>
<?php endif; ?>

<?php require __DIR__ . '/../app/parts/explain.php'; ?>
