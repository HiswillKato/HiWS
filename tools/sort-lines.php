<?php
declare(strict_types=1);

require __DIR__ . '/../app/init.php';

$tool = tool('sort-lines');

$given = posted('text');
$order = posted('order');
$trim = isset($_POST['trim']);
$dropEmpty = isset($_POST['empty']);
$unique = isset($_POST['unique']);
$fold = isset($_POST['fold']);
$result = null;
$counts = null;
$error = '';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $order = 'az';
    $trim = $dropEmpty = $unique = $fold = true;
} elseif (trim($given) === '') {
    $error = 'Paste a list, one line to a row.';
} else {
    if (!in_array($order, ['az', 'za', 'none'], true)) {
        $order = 'az';
    }

    $lines = preg_split('/\R/', $given) ?: [];

    if ($trim) {
        $lines = array_map('trim', $lines);
    }

    if ($dropEmpty) {
        $lines = array_filter($lines, static fn(string $line): bool => $line !== '');
    }

    if ($order !== 'none') {
        /* The case-blind comparison is the one people expect — Apple beside
           apple, not after every capital. */
        $fold ? usort($lines, 'strcasecmp') : sort($lines, SORT_STRING);

        if ($order === 'za') {
            $lines = array_reverse($lines);
        }
    }

    if ($unique) {
        $seen = [];
        $kept = [];

        foreach ($lines as $line) {
            $key = $fold ? mb_strtolower($line) : $line;

            if (!isset($seen[$key])) {
                $seen[$key] = true;
                $kept[] = $line;
            }
        }

        $lines = $kept;
    }

    $before = count(preg_split('/\R/', $given) ?: []);
    $result = implode("\n", $lines);
    $counts = [
        'Lines in'  => number_format($before),
        'Lines out' => number_format(count($lines)),
        'Removed'   => number_format($before - count($lines)),
    ];
}

require __DIR__ . '/../app/parts/tool.php';
?>

<form method="post" class="card bg-body rounded-4 border shadow-sm p-4">
    <label class="form-label fw-semibold" for="text">Lines</label>
    <textarea class="form-control font-monospace" id="text" name="text" rows="8" autofocus><?= e($given) ?></textarea>

    <div class="row g-3 align-items-end mt-1">
        <div class="col-sm-4">
            <label class="form-label fw-semibold" for="order">Sort</label>
            <select class="form-select" id="order" name="order">
                <option value="az" <?= $order === 'az' ? 'selected' : '' ?>>A to Z</option>
                <option value="za" <?= $order === 'za' ? 'selected' : '' ?>>Z to A</option>
                <option value="none" <?= $order === 'none' ? 'selected' : '' ?>>Leave the order alone</option>
            </select>
        </div>
        <div class="col-sm-8">
            <div class="d-flex flex-wrap gap-3">
                <?php foreach (['unique' => 'Remove duplicates', 'fold' => 'Ignore case', 'trim' => 'Trim spaces', 'empty' => 'Drop empty lines'] as $key => $label): ?>
                    <?php $checked = ['unique' => $unique, 'fold' => $fold, 'trim' => $trim, 'empty' => $dropEmpty][$key]; ?>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="<?= $key ?>" name="<?= $key ?>" <?= $checked ? 'checked' : '' ?>>
                        <label class="form-check-label" for="<?= $key ?>"><?= e($label) ?></label>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <button class="btn btn-primary mt-4" type="submit">Tidy up</button>
</form>

<?php if ($error !== ''): ?>
    <?= notice($error) ?>
<?php elseif ($result !== null): ?>
    <?= values($counts) ?>
    <?= output($result, 'Lines', true) ?>
<?php endif; ?>

<?php require __DIR__ . '/../app/parts/explain.php';
