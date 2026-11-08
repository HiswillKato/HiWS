<?php
declare(strict_types=1);

require __DIR__ . '/../app/init.php';

$tool = tool('tsv-to-json');

$input = posted('tsv');
$header = $_SERVER['REQUEST_METHOD'] !== 'POST' || isset($_POST['header']);
$output = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (trim($input) === '') {
        $error = 'Paste some TSV data.';
    } elseif (strlen($input) > 500000) {
        $error = 'TSV is too large for this converter (limit 500,000 characters).';
    } else {
        $lines = preg_split('/\R/', $input) ?: [];
        $rows = [];
        $names = null;
        foreach ($lines as $line) {
            if ($line === '') { continue; }
            $row = explode("\t", $line);
            if ($header && $names === null) {
                $names = array_map(static fn($v) => trim((string) $v), $row);
                continue;
            }
            if ($header && $names !== null) {
                $item = [];
                foreach ($names as $i => $name) { $item[$name !== '' ? $name : 'column_' . ($i + 1)] = $row[$i] ?? ''; }
                $rows[] = $item;
            } else {
                $rows[] = $row;
            }
            if (count($rows) >= 5000) { break; }
        }
        $json = json_encode($rows, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        $output = $json !== false ? $json : '';
        if ($output === '') { $error = 'Could not encode the TSV as JSON.'; }
    }
}

require __DIR__ . '/../app/parts/tool.php';
?>

<form method="post" class="card bg-body rounded-4 border shadow-sm p-4">
    <label class="form-label fw-semibold" for="tsv">TSV (tab-separated values)</label>
    <textarea class="form-control font-monospace" id="tsv" name="tsv" rows="10" autofocus
              placeholder="name&#9;age&#10;Ada&#9;36"><?= e($input) ?></textarea>
    <div class="form-check mt-3">
        <input class="form-check-input" type="checkbox" name="header" value="1" id="header"<?= $header ? ' checked' : '' ?>>
        <label class="form-check-label" for="header">First row contains column names</label>
    </div>
    <button class="btn btn-primary mt-3" type="submit">Convert to JSON</button>
</form>

<?php if ($error !== ''): ?><?= notice($error) ?>
<?php elseif ($output !== ''): ?>
    <div class="card bg-body rounded-4 border shadow-sm mt-4">
        <div class="card-header bg-transparent d-flex justify-content-between align-items-center"><span class="fw-semibold">JSON</span><?= clip($output, 'Copy') ?></div>
        <pre class="mb-0 p-3 small overflow-auto"><?= e($output) ?></pre>
    </div>
<?php endif; ?>

<?php require __DIR__ . '/../app/parts/explain.php'; ?>
