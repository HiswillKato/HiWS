<?php
declare(strict_types=1);

require __DIR__ . '/../app/init.php';

$tool = tool('reverse-text');

const MODES = [
    'characters' => 'By character',
    'words'      => 'By word',
    'lines'      => 'By line',
];

$given = posted('text');
$mode = array_key_exists(posted('mode'), MODES) ? posted('mode') : 'characters';
$result = null;
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($given === '') {
        $error = 'Enter some text to reverse.';
    } else {
        /* Split with the unicode flag, so a multi-byte letter counts as one
           character and is not torn in half. */
        $result = match ($mode) {
            'words' => implode(' ', array_reverse(preg_split('/\s+/u', trim($given), -1, PREG_SPLIT_NO_EMPTY) ?: [])),
            'lines' => implode("\n", array_reverse(preg_split('/\R/', $given) ?: [])),
            default => implode('', array_reverse(preg_split('//u', $given, -1, PREG_SPLIT_NO_EMPTY) ?: [])),
        };
    }
}

require __DIR__ . '/../app/parts/tool.php';
?>

<form method="post" class="card bg-body rounded-4 border shadow-sm p-4">
    <div class="row g-3">
        <div class="col-sm-4">
            <label class="form-label fw-semibold" for="mode">Reverse</label>
            <select class="form-select" id="mode" name="mode">
                <?php foreach (MODES as $value => $label): ?>
                    <option value="<?= $value ?>" <?= $mode === $value ? 'selected' : '' ?>><?= e($label) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col">
            <label class="form-label fw-semibold" for="text">Text</label>
            <textarea class="form-control" id="text" name="text" rows="6" autofocus><?= e($given) ?></textarea>
        </div>
    </div>

    <button class="btn btn-primary mt-4" type="submit">Reverse</button>
</form>

<?php if ($error !== ''): ?>
    <?= notice($error) ?>
<?php elseif ($result !== null && $result !== ''): ?>
    <?= output($result, MODES[$mode], true) ?>
<?php endif; ?>

<?php require __DIR__ . '/../app/parts/explain.php';
