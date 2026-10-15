<?php
declare(strict_types=1);

require __DIR__ . '/../app/init.php';

$tool = tool('json-format');

$given = posted('json');
$pretty = null;
$error = '';
$summary = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (trim($given) === '') {
        $error = 'Paste some JSON.';
    } else {
        $decoded = json_decode($given, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            $error = 'That is not valid JSON: ' . json_last_error_msg() . '.';
        } else {
            /* Slashes stay unescaped, because a URL reads far better as
               https://example.com than as https:\/\/example.com. */
            $pretty = (string) json_encode($decoded, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

            $list = is_array($decoded) && array_is_list($decoded);
            $kind = is_array($decoded) ? ($list ? 'a list' : 'an object') : get_debug_type($decoded);
            $count = is_array($decoded) ? count($decoded) : 1;

            $summary = 'Valid JSON — ' . $kind . ' with ' . $count . ' top-level ' . ($list ? 'items' : 'keys') . '.';
        }
    }
}

require __DIR__ . '/../app/parts/tool.php';
?>

<form method="post" class="card bg-body rounded-4 border shadow-sm p-4">
    <label class="form-label fw-semibold" for="json">JSON</label>
    <textarea class="form-control font-monospace" id="json" name="json" rows="10"
              placeholder='{"name": "HiWS"}' autofocus><?= e($given) ?></textarea>

    <button class="btn btn-primary mt-4" type="submit">Format</button>
</form>

<?php if ($error !== ''): ?>
    <?= notice($error) ?>
<?php elseif ($pretty !== null): ?>
    <p class="text-body-secondary mt-4 mb-0"><?= e($summary) ?></p>
    <?= output($pretty, 'Formatted JSON', true) ?>
<?php endif; ?>

<?php require __DIR__ . '/../app/parts/explain.php';
