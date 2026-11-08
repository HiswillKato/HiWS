<?php
declare(strict_types=1);

require __DIR__ . '/../app/init.php';

$tool = tool('text-repeater');

$given = posted('text');
$times = posted('times') !== '' ? posted('times') : '5';
$separator = $_POST['separator'] ?? 'newline';
$error = '';
$output = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($given === '') {
        $error = 'Enter some text to repeat.';
    } elseif (!ctype_digit($times) || (int) $times < 1 || (int) $times > 10000) {
        $error = 'Choose a whole number of repeats from 1 to 10,000.';
    } elseif (strlen($given) * (int) $times > 1000000) {
        $error = 'That would produce more than 1,000,000 characters. Use a smaller text or fewer repeats.';
    } else {
        $join = match ($separator) {
            'space' => ' ',
            'comma' => ', ',
            'none' => '',
            default => "\n",
        };
        $output = implode($join, array_fill(0, (int) $times, $given));
    }
}

require __DIR__ . '/../app/parts/tool.php';
?>

<form method="post" class="card bg-body rounded-4 border shadow-sm p-4">
    <div class="row g-3">
        <div class="col-md-4">
            <label class="form-label fw-semibold" for="times">Repeats</label>
            <input class="form-control font-monospace" id="times" name="times" value="<?= e($times) ?>" inputmode="numeric">
        </div>
        <div class="col-md-8">
            <label class="form-label fw-semibold" for="separator">Separator</label>
            <select class="form-select" id="separator" name="separator">
                <option value="newline"<?= $separator === 'newline' ? ' selected' : '' ?>>New line</option>
                <option value="space"<?= $separator === 'space' ? ' selected' : '' ?>>Space</option>
                <option value="comma"<?= $separator === 'comma' ? ' selected' : '' ?>>Comma and space</option>
                <option value="none"<?= $separator === 'none' ? ' selected' : '' ?>>None</option>
            </select>
        </div>
    </div>
    <div class="mt-3">
        <label class="form-label fw-semibold" for="text">Text</label>
        <textarea class="form-control" id="text" name="text" rows="6" autofocus placeholder="Hello"><?= e($given) ?></textarea>
    </div>
    <button class="btn btn-primary mt-3" type="submit">Repeat</button>
</form>

<?php if ($error !== ''): ?>
    <?= notice($error) ?>
<?php elseif ($output !== ''): ?>
    <?= output($output, 'Repeated text', true) ?>
<?php endif; ?>

<?php require __DIR__ . '/../app/parts/explain.php'; ?>
