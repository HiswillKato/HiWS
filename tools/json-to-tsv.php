<?php
declare(strict_types=1);

require __DIR__ . '/../app/init.php';

$tool = tool('json-to-tsv');

$given = posted('json');
$header = $_SERVER['REQUEST_METHOD'] !== 'POST' || isset($_POST['header']);
$error = '';
$output = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (trim($given) === '') {
        $error = 'Paste a JSON array.';
    } else {
        $decoded = json_decode($given, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            $error = 'That is not valid JSON: ' . json_last_error_msg() . '.';
        } elseif (!is_array($decoded) || !array_is_list($decoded) || $decoded === []) {
            $error = 'Provide a non-empty JSON array (a list of objects or rows).';
        } else {
            $cell = static function ($v): string {
                $s = is_scalar($v) || $v === null ? (string) $v : (string) json_encode($v, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
                return str_replace(["\t", "\r", "\n"], [' ', ' ', ' '], $s);
            };
            $lines = [];
            $first = $decoded[0];
            $isObjects = is_array($first) && !array_is_list($first);
            $columns = $isObjects ? array_keys($first) : [];
            if ($isObjects) {
                foreach ($decoded as $row) { if (is_array($row) && !array_is_list($row)) foreach (array_keys($row) as $k) if (!in_array($k, $columns, true)) $columns[] = $k; }
                if ($header) $lines[] = implode("\t", array_map($cell, $columns));
                foreach (array_slice($decoded, 0, 5000) as $row) {
                    $line = [];
                    foreach ($columns as $col) $line[] = $cell(is_array($row) ? ($row[$col] ?? '') : '');
                    $lines[] = implode("\t", $line);
                }
            } else {
                foreach (array_slice($decoded, 0, 5000) as $row) $lines[] = implode("\t", array_map($cell, is_array($row) ? $row : [$row]));
            }
            $output = implode("\n", $lines) . "\n";
        }
    }
}

require __DIR__ . '/../app/parts/tool.php';
?>

<form method="post" class="card bg-body rounded-4 border shadow-sm p-4">
    <label class="form-label fw-semibold" for="json">JSON array</label>
    <textarea class="form-control font-monospace" id="json" name="json" rows="10" autofocus
              placeholder='[{"name":"Ada","age":36}]'><?= e($given) ?></textarea>
    <div class="form-check mt-3">
        <input class="form-check-input" type="checkbox" name="header" value="1" id="header"<?= $header ? ' checked' : '' ?>>
        <label class="form-check-label" for="header">Include header row (object keys)</label>
    </div>
    <button class="btn btn-primary mt-3" type="submit">Convert to TSV</button>
</form>

<?php if ($error !== ''): ?><?= notice($error) ?>
<?php elseif ($output !== ''): ?><?= output($output, 'TSV', true) ?>
<?php endif; ?>

<?php require __DIR__ . '/../app/parts/explain.php'; ?>
