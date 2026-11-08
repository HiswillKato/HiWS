<?php
declare(strict_types=1);

require __DIR__ . '/../app/init.php';

$tool = tool('json-to-csv');

$given = posted('json');
$header = $_SERVER['REQUEST_METHOD'] !== 'POST' || isset($_POST['header']);
$error = '';
$output = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (trim($given) === '') {
        $error = 'Paste a JSON array.';
    } elseif (strlen($given) > 500000) {
        $error = 'JSON is too large for this converter (limit 500,000 characters).';
    } else {
        $decoded = json_decode($given, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            $error = 'That is not valid JSON: ' . json_last_error_msg() . '.';
        } elseif (!is_array($decoded) || !array_is_list($decoded)) {
            $error = 'Provide a JSON array (a list of objects or rows).';
        } elseif ($decoded === []) {
            $error = 'The JSON array is empty.';
        } else {
            $stream = fopen('php://temp', 'r+');
            if ($stream === false) {
                $error = 'Could not create CSV output.';
            } else {
                $first = $decoded[0];
                $isObjects = is_array($first) && !array_is_list($first);
                $columns = $isObjects ? array_keys($first) : array_keys(array_values((array) $first));
                foreach ($decoded as $row) {
                    if (is_array($row) && !array_is_list($row)) {
                        foreach (array_keys($row) as $k) { if (!in_array($k, $columns, true)) { $columns[] = $k; } }
                    }
                }
                if ($header) {
                    fputcsv($stream, $isObjects ? $columns : array_map(static fn($i) => 'column_' . ($i + 1), array_keys($columns)), ',', '"', '');
                }
                $count = 0;
                foreach ($decoded as $row) {
                    if (is_array($row) && !array_is_list($row)) {
                        $line = [];
                        foreach ($columns as $col) { $v = $row[$col] ?? ''; $line[] = is_scalar($v) || $v === null ? (string) $v : json_encode($v, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); }
                        fputcsv($stream, $line, ',', '"', '');
                    } elseif (is_array($row)) {
                        $line = array_map(static fn($v) => is_scalar($v) || $v === null ? (string) $v : json_encode($v, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES), $row);
                        fputcsv($stream, $line, ',', '"', '');
                    } else {
                        fputcsv($stream, [is_scalar($row) ? (string) $row : json_encode($row)], ',', '"', '');
                    }
                    if (++$count >= 5000) { break; }
                }
                rewind($stream);
                $output = (string) stream_get_contents($stream);
                fclose($stream);
            }
        }
    }
}

require __DIR__ . '/../app/parts/tool.php';
?>

<form method="post" class="card bg-body rounded-4 border shadow-sm p-4">
    <label class="form-label fw-semibold" for="json">JSON array</label>
    <textarea class="form-control font-monospace" id="json" name="json" rows="10" autofocus
              placeholder='[{"name":"Ada","age":36},{"name":"Grace","age":45}]'><?= e($given) ?></textarea>
    <div class="form-check mt-3">
        <input class="form-check-input" type="checkbox" name="header" value="1" id="header"<?= $header ? ' checked' : '' ?>>
        <label class="form-check-label" for="header">Include header row (object keys)</label>
    </div>
    <button class="btn btn-primary mt-3" type="submit">Convert to CSV</button>
</form>

<?php if ($error !== ''): ?>
    <?= notice($error) ?>
<?php elseif ($output !== ''): ?>
    <?= output($output, 'CSV', true) ?>
<?php endif; ?>

<?php require __DIR__ . '/../app/parts/explain.php'; ?>
