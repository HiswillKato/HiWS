<?php
declare(strict_types=1);

require __DIR__ . '/../app/init.php';

$tool = tool('xml-format');

$input = posted('xml');
$output = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (trim($input) === '') {
        $error = 'Paste some XML to format.';
    } elseif (strlen($input) > 500000) {
        $error = 'XML is too large for this formatter (limit 500,000 characters).';
    } else {
        $prev = libxml_use_internal_errors(true);
        $dom = new DOMDocument('1.0', 'UTF-8');
        $dom->preserveWhiteSpace = false;
        $dom->formatOutput = true;
        $loaded = $dom->loadXML($input, LIBXML_NONET | LIBXML_NOCDATA);
        $errors = libxml_get_errors();
        libxml_clear_errors();
        libxml_use_internal_errors($prev);

        if (!$loaded) {
            $message = $errors !== [] ? trim($errors[0]->message) : 'The XML could not be parsed.';
            $line = $errors !== [] ? (int) $errors[0]->line : 0;
            $error = 'Invalid XML' . ($line > 0 ? ' (line ' . $line . ')' : '') . ': ' . $message;
        } else {
            $output = $dom->saveXML() ?: '';
            if ($output === '') {
                $error = 'The XML was parsed but could not be formatted.';
            }
        }
    }
}

require __DIR__ . '/../app/parts/tool.php';
?>

<form method="post" class="card bg-body rounded-4 border shadow-sm p-4">
    <label class="form-label fw-semibold" for="xml">XML</label>
    <textarea class="form-control font-monospace" id="xml" name="xml" rows="12" autofocus
              placeholder="&lt;root&gt;&lt;item&gt;value&lt;/item&gt;&lt;/root&gt;"><?= e($input) ?></textarea>
    <button class="btn btn-primary mt-3" type="submit">Format / validate</button>
</form>

<?php if ($error !== ''): ?>
    <?= notice($error) ?>
<?php elseif ($output !== ''): ?>
    <div class="card bg-body rounded-4 border shadow-sm mt-4">
        <div class="card-header bg-transparent d-flex justify-content-between align-items-center">
            <span class="fw-semibold">Formatted XML</span>
            <?= clip($output, 'Copy') ?>
        </div>
        <pre class="mb-0 p-3 small overflow-auto"><?= e($output) ?></pre>
    </div>
<?php endif; ?>

<?php require __DIR__ . '/../app/parts/explain.php'; ?>
