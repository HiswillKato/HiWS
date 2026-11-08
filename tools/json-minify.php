<?php
declare(strict_types=1);

require __DIR__ . '/../app/init.php';

$tool = tool('json-minify');

$given = posted('json');
$error = '';
$output = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (trim($given) === '') {
        $error = 'Paste some JSON.';
    } else {
        $decoded = json_decode($given);
        if (json_last_error() !== JSON_ERROR_NONE) {
            $error = 'That is not valid JSON: ' . json_last_error_msg() . '.';
        } else {
            $output = (string) json_encode($decoded, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
            $saved = strlen($given) - strlen($output);
            $pct = strlen($given) > 0 ? round(($saved / strlen($given)) * 100, 1) : 0;
            $summary = bytes(strlen($given)) . ' → ' . bytes(strlen($output)) . ' (' . $pct . '% saved)';
        }
    }
}

require __DIR__ . '/../app/parts/tool.php';
?>

<form method="post" class="card bg-body rounded-4 border shadow-sm p-4">
    <label class="form-label fw-semibold" for="json">JSON</label>
    <textarea class="form-control font-monospace" id="json" name="json" rows="10" autofocus
              placeholder='{ "name": "HiWS", "free": true }'><?= e($given) ?></textarea>
    <button class="btn btn-primary mt-3" type="submit">Minify JSON</button>
</form>

<?php if ($error !== ''): ?>
    <?= notice($error) ?>
<?php elseif ($output !== ''): ?>
    <p class="text-body-secondary mt-4 mb-0"><?= e($summary) ?></p>
    <?= output($output, 'Minified JSON', true) ?>
<?php endif; ?>

<?php require __DIR__ . '/../app/parts/explain.php'; ?>
