<?php
declare(strict_types=1);

require __DIR__ . '/../app/init.php';

$tool = tool('comma-separator');

$given = posted('text');
$mode = posted('mode') ?: 'lines-to-comma';
$quote = $_SERVER['REQUEST_METHOD'] !== 'POST' || isset($_POST['quote']);
$error = '';
$output = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (trim($given) === '') {
        $error = 'Enter some items.';
    } else {
        $wrap = static function (string $v) use ($quote): string {
            $v = trim($v);
            if (!$quote) { return $v; }
            if (str_contains($v, ',') || str_contains($v, '"') || str_contains($v, "\n")) {
                return '"' . str_replace('"', '""', $v) . '"';
            }
            return $v;
        };
        if ($mode === 'lines-to-comma') {
            $items = preg_split('/\R/', $given) ?: [];
            $items = array_values(array_filter(array_map('trim', $items), static fn($v) => $v !== ''));
            $output = implode(', ', array_map($wrap, $items));
        } elseif ($mode === 'comma-to-lines') {
            $items = str_getcsv($given, ',', '"', '\\');
            $items = array_values(array_filter(array_map('trim', $items), static fn($v) => $v !== ''));
            $output = implode("\n", $items);
        } elseif ($mode === 'lines-to-pipe') {
            $items = preg_split('/\R/', $given) ?: [];
            $items = array_values(array_filter(array_map('trim', $items), static fn($v) => $v !== ''));
            $output = implode(' | ', $items);
        } else {
            $items = preg_split('/\s*[;|]\s*/', $given) ?: [];
            $items = array_values(array_filter(array_map('trim', $items), static fn($v) => $v !== ''));
            $output = implode("\n", $items);
        }
    }
}

require __DIR__ . '/../app/parts/tool.php';
?>

<form method="post" class="card bg-body rounded-4 border shadow-sm p-4">
    <div class="mb-3">
        <label class="form-label fw-semibold" for="mode">Convert</label>
        <select class="form-select" id="mode" name="mode">
            <option value="lines-to-comma"<?= $mode === 'lines-to-comma' ? ' selected' : '' ?>>Lines to comma-separated</option>
            <option value="comma-to-lines"<?= $mode === 'comma-to-lines' ? ' selected' : '' ?>>Comma-separated to lines</option>
            <option value="lines-to-pipe"<?= $mode === 'lines-to-pipe' ? ' selected' : '' ?>>Lines to pipe-separated</option>
            <option value="delim-to-lines"<?= $mode === 'delim-to-lines' ? ' selected' : '' ?>>Semicolon/pipe to lines</option>
        </select>
    </div>
    <label class="form-label fw-semibold" for="text">Input</label>
    <textarea class="form-control font-monospace" id="text" name="text" rows="8" autofocus
              placeholder="apple&#10;banana&#10;cherry"><?= e($given) ?></textarea>
    <div class="form-check mt-3">
        <input class="form-check-input" type="checkbox" name="quote" value="1" id="quote"<?= $quote ? ' checked' : '' ?>>
        <label class="form-check-label" for="quote">Quote items that contain commas when joining</label>
    </div>
    <button class="btn btn-primary mt-3" type="submit">Convert</button>
</form>

<?php if ($error !== ''): ?>
    <?= notice($error) ?>
<?php elseif ($output !== ''): ?>
    <?= output($output, 'Result', true) ?>
<?php endif; ?>

<?php require __DIR__ . '/../app/parts/explain.php'; ?>
