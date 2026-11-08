<?php
declare(strict_types=1);

require __DIR__ . '/../app/init.php';

$tool = tool('json-to-text');

function json_flat_text(mixed $data, string $prefix = ''): array
{
    $lines = [];
    if (is_array($data)) {
        $list = array_is_list($data);
        foreach ($data as $k => $v) {
            $key = $list ? $prefix . '[' . $k . ']' : ($prefix === '' ? (string)$k : $prefix . '.' . $k);
            if (is_array($v)) $lines = array_merge($lines, json_flat_text($v, $key));
            else $lines[] = $key . ': ' . (is_bool($v) ? ($v ? 'true' : 'false') : ($v === null ? 'null' : (string)$v));
        }
    } elseif ($prefix !== '') {
        $lines[] = $prefix . ': ' . (is_scalar($data) || $data === null ? (string)$data : json_encode($data));
    }
    return $lines;
}

$given = posted('json');
$mode = posted('mode') ?: 'flat';
$error = '';
$output = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (trim($given) === '') {
        $error = 'Paste some JSON.';
    } else {
        $decoded = json_decode($given, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            $error = 'That is not valid JSON: ' . json_last_error_msg() . '.';
        } else {
            $output = $mode === 'pretty'
                ? (string) json_encode($decoded, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)
                : implode("\n", json_flat_text($decoded));
        }
    }
}

require __DIR__ . '/../app/parts/tool.php';
?>

<form method="post" class="card bg-body rounded-4 border shadow-sm p-4">
    <div class="mb-3">
        <label class="form-label fw-semibold" for="mode">Output</label>
        <select class="form-select" id="mode" name="mode">
            <option value="flat"<?= $mode === 'flat' ? ' selected' : '' ?>>Flattened key/value lines</option>
            <option value="pretty"<?= $mode === 'pretty' ? ' selected' : '' ?>>Pretty-printed JSON text</option>
        </select>
    </div>
    <label class="form-label fw-semibold" for="json">JSON</label>
    <textarea class="form-control font-monospace" id="json" name="json" rows="10" autofocus
              placeholder='{"user":{"name":"Ada"}}'><?= e($given) ?></textarea>
    <button class="btn btn-primary mt-3" type="submit">Convert to text</button>
</form>

<?php if ($error !== ''): ?><?= notice($error) ?>
<?php elseif ($output !== ''): ?><?= output($output, 'Text', true) ?>
<?php endif; ?>

<?php require __DIR__ . '/../app/parts/explain.php'; ?>
