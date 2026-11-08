<?php
declare(strict_types=1);

require __DIR__ . '/../app/init.php';

$tool = tool('base64-to-image');

$given = posted('data');
$error = '';
$src = '';
$info = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (trim($given) === '') {
        $error = 'Paste a Base64 data URI or raw Base64 image data.';
    } elseif (strlen($given) > 3000000) {
        $error = 'Input is too large for this tool (limit 3,000,000 characters).';
    } else {
        $raw = trim($given);
        $mime = 'image/png';
        if (preg_match('/^data:(image\/[a-zA-Z0-9.+-]+);base64,(.+)$/s', $raw, $m)) {
            $mime = strtolower($m[1]);
            $raw = $m[2];
        }
        $allowed = ['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'image/bmp'];
        if (!in_array($mime, $allowed, true)) {
            $error = 'Unsupported image type in data URI. Use JPEG, PNG, GIF, WebP or BMP.';
        } else {
            $raw = preg_replace('/\s+/', '', $raw) ?? $raw;
            $bin = base64_decode($raw, true);
            if ($bin === false || $bin === '') {
                $error = 'That is not valid Base64 data.';
            } else {
                $tmp = tempnam(sys_get_temp_dir(), 'hiwsimg');
                if ($tmp === false || file_put_contents($tmp, $bin) === false) {
                    $error = 'Could not process the image data.';
                } else {
                    $detected = @getimagesize($tmp);
                    @unlink($tmp);
                    $typeMap = [IMAGETYPE_JPEG => 'image/jpeg', IMAGETYPE_PNG => 'image/png', IMAGETYPE_GIF => 'image/gif', IMAGETYPE_WEBP => 'image/webp', IMAGETYPE_BMP => 'image/bmp'];
                    if ($detected === false || !isset($typeMap[$detected[2]])) {
                        $error = 'The decoded data is not a supported image.';
                    } else {
                        $mime = $typeMap[$detected[2]];
                        $src = 'data:' . $mime . ';base64,' . base64_encode($bin);
                        $info = $detected[0] . '×' . $detected[1] . ', ' . $mime . ', ' . bytes(strlen($bin));
                    }
                }
            }
        }
    }
}

require __DIR__ . '/../app/parts/tool.php';
?>

<form method="post" class="card bg-body rounded-4 border shadow-sm p-4">
    <label class="form-label fw-semibold" for="data">Base64 data URI or Base64</label>
    <textarea class="form-control font-monospace" id="data" name="data" rows="10" autofocus
              placeholder="data:image/png;base64,iVBORw0KGgo..."><?= e($given) ?></textarea>
    <div class="form-text">Paste a full <code>data:image/...;base64,...</code> URI or raw Base64 image bytes. Data is decoded on this server and not stored.</div>
    <button class="btn btn-primary mt-3" type="submit">Convert to image</button>
</form>

<?php if ($error !== ''): ?>
    <?= notice($error) ?>
<?php elseif ($src !== ''): ?>
    <p class="text-body-secondary mt-4 mb-0"><?= e($info) ?></p>
    <div class="card bg-body rounded-4 border shadow-sm mt-3">
        <div class="card-header bg-transparent d-flex justify-content-between align-items-center">
            <span class="fw-semibold">Image</span>
            <a class="btn btn-sm btn-outline-secondary" href="<?= e($src) ?>" download="image">Download</a>
        </div>
        <div class="card-body text-center"><img src="<?= e($src) ?>" alt="Decoded image" class="img-fluid rounded border" style="max-height:480px"></div>
    </div>
<?php endif; ?>

<?php require __DIR__ . '/../app/parts/explain.php'; ?>
