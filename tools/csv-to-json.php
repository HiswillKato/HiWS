<?php
declare(strict_types=1);

require __DIR__ . '/../app/init.php';

$tool = tool('csv-to-json');

$input = posted('csv');
$header = $_SERVER['REQUEST_METHOD'] !== 'POST' || isset($_POST['header']);
$output = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (trim($input) === '') {
        $error = 'Paste some CSV data to convert.';
    } elseif (strlen($input) > 500000) {
        $error = 'CSV is too large for this converter (limit 500,000 characters).';
    } else {
        $stream = fopen('php://temp', 'r+');
        if ($stream === false) {
            $error = 'Could not read the CSV data.';
        } else {
            fwrite($stream, $input);
            rewind($stream);
            $rows = [];
            $names = null;
            while (($row = fgetcsv($stream, 0, ',', '"', '\\')) !== false) {
                if ($row === [null]) {
                    continue;
                }
                if ($header && $names === null) {
                    $names = array_map(static fn($v): string => trim((string) $v), $row);
                    continue;
                }
                if ($header && $names !== null) {
                    $item = [];
                    foreach ($names as $i => $name) {
                        $item[$name !== '' ? $name : 'column_' . ($i + 1)] = $row[$i] ?? '';
                    }
                    $rows[] = $item;
                } else {
                    $rows[] = $row;
                }
                if (count($rows) >= 5000) {
                    break;
                }
            }
            fclose($stream);

            $json = json_encode($rows, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
            if ($json === false) {
                $error = 'The CSV was read but could not be encoded as JSON.';
            } else {
                $output = $json;
            }
        }
    }
}

require __DIR__ . '/../app/parts/tool.php';
?>

<form method="post" class="card bg-body rounded-4 border shadow-sm p-4">
    <label class="form-label fw-semibold" for="csv">CSV</label>
    <textarea class="form-control font-monospace" id="csv" name="csv" rows="10" autofocus
              placeholder="name,age&#10;Ada,36&#10;Grace,45"><?= e($input) ?></textarea>
    <div class="form-check mt-3">
        <input class="form-check-input" type="checkbox" name="header" value="1" id="header"<?= $header ? ' checked' : '' ?>>
        <label class="form-check-label" for="header">First row contains column names</label>
    </div>
    <button class="btn btn-primary mt-3" type="submit">Convert to JSON</button>
</form>

<?php if ($error !== ''): ?>
    <?= notice($error) ?>
<?php elseif ($output !== ''): ?>
    <div class="card bg-body rounded-4 border shadow-sm mt-4">
        <div class="card-header bg-transparent d-flex justify-content-between align-items-center">
            <span class="fw-semibold">JSON</span>
            <?= clip($output, 'Copy') ?>
        </div>
        <pre class="mb-0 p-3 small overflow-auto"><?= e($output) ?></pre>
    </div>
<?php endif; ?>

<?php require __DIR__ . '/../app/parts/explain.php'; ?>
