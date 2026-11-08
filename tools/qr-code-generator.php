<?php
declare(strict_types=1);

require __DIR__ . '/../app/init.php';

$tool = tool('qr-code-generator');

use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\RoundBlockSizeMode;
use Endroid\QrCode\Writer\PngWriter;

$text = posted('text');
$size = posted('size') !== '' ? (int) posted('size') : 300;
$margin = posted('margin') !== '' ? (int) posted('margin') : 10;
$ec = posted('ec') ?: 'M';
$error = '';
$src = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (trim($text) === '') {
        $error = 'Enter text or a URL to encode.';
    } elseif (strlen($text) > 4000) {
        $error = 'Text is too long for a QR code in this tool (limit 4,000 characters).';
    } elseif (!class_exists(Builder::class)) {
        $error = 'The QR library (endroid/qr-code) is not installed. Run composer install in the project root.';
    } else {
        $size = max(120, min(1000, $size));
        $margin = max(0, min(40, $margin));
        $ecLevel = match ($ec) {
            'L' => ErrorCorrectionLevel::Low,
            'Q' => ErrorCorrectionLevel::Quartile,
            'H' => ErrorCorrectionLevel::High,
            default => ErrorCorrectionLevel::Medium,
        };
        try {
            $result = (new Builder(
                writer: new PngWriter(),
                data: $text,
                encoding: new Encoding('UTF-8'),
                errorCorrectionLevel: $ecLevel,
                size: $size,
                margin: $margin,
                roundBlockSizeMode: RoundBlockSizeMode::Margin,
            ))->build();
            $src = $result->getDataUri();
        } catch (Throwable $e) {
            $error = 'Could not generate the QR code.';
        }
    }
}

require __DIR__ . '/../app/parts/tool.php';
?>

<form method="post" class="card bg-body rounded-4 border shadow-sm p-4">
    <label class="form-label fw-semibold" for="text">Text or URL</label>
    <textarea class="form-control font-monospace" id="text" name="text" rows="4" autofocus
              placeholder="https://hiws.dev/"><?= e($text) ?></textarea>
    <div class="row g-3 mt-1">
        <div class="col-md-4">
            <label class="form-label fw-semibold" for="ec">Error correction</label>
            <select class="form-select" id="ec" name="ec">
                <option value="L"<?= $ec==='L'?' selected':'' ?>>L (smallest)</option>
                <option value="M"<?= $ec==='M'?' selected':'' ?>>M (default)</option>
                <option value="Q"<?= $ec==='Q'?' selected':'' ?>>Q</option>
                <option value="H"<?= $ec==='H'?' selected':'' ?>>H (most robust)</option>
            </select>
        </div>
        <div class="col-md-4">
            <label class="form-label fw-semibold" for="size">Image size (px)</label>
            <input class="form-control" id="size" name="size" type="number" min="120" max="1000" value="<?= e((string)$size) ?>">
        </div>
        <div class="col-md-4">
            <label class="form-label fw-semibold" for="margin">Margin (px)</label>
            <input class="form-control" id="margin" name="margin" type="number" min="0" max="40" value="<?= e((string)$margin) ?>">
        </div>
    </div>
    <button class="btn btn-primary mt-3" type="submit">Generate QR code</button>
</form>

<?php if ($error !== ''): ?><?= notice($error) ?>
<?php elseif ($src !== ''): ?>
    <div class="card bg-body rounded-4 border shadow-sm mt-4">
        <div class="card-header bg-transparent d-flex justify-content-between align-items-center">
            <span class="fw-semibold">QR code</span>
            <a class="btn btn-sm btn-outline-secondary" href="<?= e($src) ?>" download="qrcode.png">Download PNG</a>
        </div>
        <div class="card-body text-center"><img src="<?= e($src) ?>" alt="QR code" class="img-fluid border rounded p-2 bg-white" style="max-width:<?= e((string) min($size, 520)) ?>px"></div>
    </div>
<?php endif; ?>

<?php require __DIR__ . '/../app/parts/explain.php'; ?>
