<?php
declare(strict_types=1);

require __DIR__ . '/../app/init.php';

$tool = tool('image-to-base64');

$error = '';
$output = '';
$mime = '';
$info = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $file = $_FILES['image'] ?? null;
    if (!is_array($file) || ($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
        $error = 'Choose an image file to convert.';
    } elseif (($file['error'] ?? UPLOAD_ERR_OK) !== UPLOAD_ERR_OK) {
        $error = 'The upload failed. Try a smaller image.';
    } elseif (($file['size'] ?? 0) > 2 * 1024 * 1024) {
        $error = 'Image is too large for this tool (limit 2 MB).';
    } else {
        $tmp = (string) $file['tmp_name'];
        $detected = @getimagesize($tmp);
        $allowed = [IMAGETYPE_JPEG => 'image/jpeg', IMAGETYPE_PNG => 'image/png', IMAGETYPE_GIF => 'image/gif', IMAGETYPE_WEBP => 'image/webp', IMAGETYPE_BMP => 'image/bmp'];
        if ($detected === false || !isset($allowed[$detected[2]])) {
            $error = 'That file is not a supported image (JPEG, PNG, GIF, WebP or BMP).';
        } else {
            $data = file_get_contents($tmp);
            if ($data === false) {
                $error = 'Could not read the uploaded image.';
            } else {
                $mime = $allowed[$detected[2]];
                $b64 = base64_encode($data);
                $output = 'data:' . $mime . ';base64,' . $b64;
                $info = $detected[0] . '×' . $detected[1] . ', ' . $mime . ', ' . bytes(strlen($data)) . ' → ' . bytes(strlen($output)) . ' as data URI';
            }
        }
    }
}

require __DIR__ . '/../app/parts/tool.php';
?>

<form method="post" enctype="multipart/form-data" class="card bg-body rounded-4 border shadow-sm p-4">
    <label class="form-label fw-semibold" for="image">Image file</label>
    <input class="form-control" type="file" id="image" name="image" accept="image/jpeg,image/png,image/gif,image/webp,image/bmp">
    <div class="form-text">JPEG, PNG, GIF, WebP or BMP. Maximum 2 MB. The image is converted on this server and not stored.</div>
    <button class="btn btn-primary mt-3" type="submit">Convert to Base64</button>
</form>

<?php if ($error !== ''): ?>
    <?= notice($error) ?>
<?php elseif ($output !== ''): ?>
    <p class="text-body-secondary mt-4 mb-0"><?= e($info) ?></p>
    <div class="card bg-body rounded-4 border shadow-sm mt-3">
        <div class="card-header bg-transparent fw-semibold">Preview</div>
        <div class="card-body"><img src="<?= e($output) ?>" alt="Uploaded image preview" class="img-fluid rounded border" style="max-height:320px"></div>
    </div>
    <?= output($output, 'Base64 data URI', true) ?>
<?php endif; ?>

<?php require __DIR__ . '/../app/parts/explain.php'; ?>
