<?php
declare(strict_types=1);

require __DIR__ . '/../app/init.php';

$tool = tool('image-rotate-flip');

$error = '';
$src = '';
$info = '';
$rotate = posted('rotate') ?: '0';
$flip = posted('flip') ?: 'none';
$format = posted('format') ?: 'keep';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $file = $_FILES['image'] ?? null;
    if (!is_array($file) || ($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) $error = 'Choose an image file.';
    elseif (($file['error'] ?? UPLOAD_ERR_OK) !== UPLOAD_ERR_OK || ($file['size'] ?? 0) > 5 * 1024 * 1024) $error = 'Upload failed or image is too large (limit 5 MB).';
    else {
        $tmp = (string) $file['tmp_name']; $det = @getimagesize($tmp);
        $map = [IMAGETYPE_JPEG=>'jpeg', IMAGETYPE_PNG=>'png', IMAGETYPE_GIF=>'gif', IMAGETYPE_WEBP=>'webp', IMAGETYPE_BMP=>'bmp'];
        if ($det === false || !isset($map[$det[2]])) $error = 'Unsupported image type.';
        elseif ($det[0] * $det[1] > 40_000_000) $error = 'Image dimensions are too large to process safely.';
        else {
            $im = match ($det[2]) {
                IMAGETYPE_JPEG => @imagecreatefromjpeg($tmp), IMAGETYPE_PNG => @imagecreatefrompng($tmp),
                IMAGETYPE_GIF => @imagecreatefromgif($tmp),
                IMAGETYPE_WEBP => function_exists('imagecreatefromwebp') ? @imagecreatefromwebp($tmp) : null,
                IMAGETYPE_BMP => function_exists('imagecreatefrombmp') ? @imagecreatefrombmp($tmp) : null,
                default => null,
            };
            if (!$im) $error = 'Could not read the image.';
            else {
                imagesavealpha($im, true);
                $deg = (int) $rotate;
                if (in_array($deg, [90,180,270], true)) {
                    $bg = imagecolorallocatealpha($im, 0,0,0,127);
                    $rot = imagerotate($im, $deg, $bg);
                    if ($rot instanceof GdImage) { imagedestroy($im); $im = $rot; imagesavealpha($im, true); }
                }
                if ($flip === 'h' && defined('IMG_FLIP_HORIZONTAL')) imageflip($im, IMG_FLIP_HORIZONTAL);
                elseif ($flip === 'v' && defined('IMG_FLIP_VERTICAL')) imageflip($im, IMG_FLIP_VERTICAL);
                elseif ($flip === 'b' && defined('IMG_FLIP_BOTH')) imageflip($im, IMG_FLIP_BOTH);
                $fmt = $format === 'keep' ? $map[$det[2]] : $format;
                ob_start();
                $ok = match ($fmt) {
                    'jpeg' => imagejpeg($im, null, 90), 'gif' => imagegif($im),
                    'webp' => function_exists('imagewebp') ? imagewebp($im, null, 90) : false,
                    'bmp' => function_exists('imagebmp') ? imagebmp($im) : false,
                    default => imagepng($im),
                };
                $data = ob_get_clean(); imagedestroy($im);
                if (!$ok || !is_string($data) || $data === '') $error = 'Could not encode the image.';
                else {
                    $mime = match($fmt){'jpeg'=>'image/jpeg','gif'=>'image/gif','webp'=>'image/webp','bmp'=>'image/bmp',default=>'image/png'};
                    $src = 'data:' . $mime . ';base64,' . base64_encode($data);
                    $info = strtoupper($fmt) . ', ' . bytes(strlen($data));
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
        <div class="col-md-4"><label class="form-label fw-semibold" for="rotate">Rotate</label>
            <select class="form-select" id="rotate" name="rotate">
                <?php foreach (['0'=>'No rotation','90'=>'90° clockwise','180'=>'180°','270'=>'270° clockwise'] as $k=>$v): ?><option value="<?= e($k) ?>"<?= $rotate===$k?' selected':'' ?>><?= e($v) ?></option><?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-4"><label class="form-label fw-semibold" for="flip">Flip</label>
            <select class="form-select" id="flip" name="flip">
                <?php foreach (['none'=>'None','h'=>'Horizontal','v'=>'Vertical','b'=>'Both'] as $k=>$v): ?><option value="<?= e($k) ?>"<?= $flip===$k?' selected':'' ?>><?= e($v) ?></option><?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-4"><label class="form-label fw-semibold" for="format">Output</label>
            <select class="form-select" id="format" name="format">
                <?php foreach (['keep'=>'Keep original','png'=>'PNG','jpeg'=>'JPEG','webp'=>'WebP'] as $k=>$v): ?><option value="<?= e($k) ?>"<?= $format===$k?' selected':'' ?>><?= e($v) ?></option><?php endforeach; ?>
            </select>
        </div>
    </div>
    <button class="btn btn-primary mt-3" type="submit">Apply</button>
</form>

<?php if ($error !== ''): ?><?= notice($error) ?>
<?php elseif ($src !== ''): ?>
    <p class="text-body-secondary mt-4 mb-0"><?= e($info) ?></p>
    <div class="card bg-body rounded-4 border shadow-sm mt-3">
        <div class="card-header bg-transparent d-flex justify-content-between align-items-center"><span class="fw-semibold">Result</span><a class="btn btn-sm btn-outline-secondary" href="<?= e($src) ?>" download="image-edited">Download</a></div>
        <div class="card-body text-center"><img src="<?= e($src) ?>" alt="Edited image" class="img-fluid rounded border" style="max-height:480px"></div>
    </div>
<?php endif; ?>

<?php require __DIR__ . '/../app/parts/explain.php'; ?>
