<?php
declare(strict_types=1);

require __DIR__ . '/../app/init.php';

$tool = tool('remove-line-breaks');

const MODES = [
    'single'     => 'One line',
    'paragraphs' => 'Keep paragraph breaks',
    'comma'      => 'Comma separated',
];

$given = posted('text');
$mode = array_key_exists(posted('mode'), MODES) ? posted('mode') : 'single';
$result = null;
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (trim($given) === '') {
        $error = 'Paste some text with line breaks.';
    } else {
        $result = match ($mode) {
            'single' => trim((string) preg_replace('/\s*\R\s*/', ' ', $given)),
            /* Blank lines stay as the break between paragraphs; every single
               newline inside a paragraph becomes a space. */
            'paragraphs' => implode("\n\n", array_map(
                static fn(string $para): string => trim((string) preg_replace('/\s*\R\s*/', ' ', $para)),
                preg_split('/\R{2,}/', trim($given)) ?: []
            )),
            'comma' => trim((string) preg_replace('/\s*\R\s*/', ', ', $given)),
        };
    }
}

require __DIR__ . '/../app/parts/tool.php';
?>

<form method="post" class="card bg-body rounded-4 border shadow-sm p-4">
    <div class="row g-3">
        <div class="col-sm-4">
            <label class="form-label fw-semibold" for="mode">Join as</label>
            <select class="form-select" id="mode" name="mode">
                <?php foreach (MODES as $value => $label): ?>
                    <option value="<?= $value ?>" <?= $mode === $value ? 'selected' : '' ?>><?= e($label) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col">
            <label class="form-label fw-semibold" for="text">Text</label>
            <textarea class="form-control" id="text" name="text" rows="8" autofocus><?= e($given) ?></textarea>
        </div>
    </div>

    <button class="btn btn-primary mt-4" type="submit">Remove breaks</button>
</form>

<?php if ($error !== ''): ?>
    <?= notice($error) ?>
<?php elseif ($result !== null && $result !== ''): ?>
    <?= output($result, 'Result', true) ?>
<?php endif; ?>

<?php require __DIR__ . '/../app/parts/explain.php';
