<?php
declare(strict_types=1);

require __DIR__ . '/../app/init.php';

$tool = tool('random-number');

$min = posted('min');
$max = posted('max');
$count = posted('count');
$unique = isset($_POST['unique']);
$result = null;
$report = null;
$error = '';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $min = '1';
    $max = '100';
    $count = '5';
    $unique = true;
} else {
    $min = $min === '' ? '0' : $min;
    $max = $max === '' ? '100' : $max;
    $count = $count === '' ? '1' : $count;

    if (preg_match('/^-?\d+$/', $min) !== 1 || preg_match('/^-?\d+$/', $max) !== 1) {
        $error = 'Both ends of the range have to be whole numbers.';
    } elseif (!ctype_digit($count) || (int) $count < 1 || (int) $count > 1000) {
        $error = 'Draw between 1 and 1000 numbers.';
    } else {
        $low = (int) $min;
        $high = (int) $max;
        $size = (int) $count;

        if ($low > $high) {
            [$low, $high] = [$high, $low];
        }

        if ($unique && ($high - $low + 1) < $size) {
            $error = 'A range of ' . number_format($high - $low + 1)
                . ' whole numbers cannot yield ' . number_format($size) . ' different ones.';
        } else {
            if ($unique) {
                /* Drawn one at a time and kept only when new, which avoids
                   building the whole range in memory when it is very large. */
                $seen = [];

                while (count($seen) < $size) {
                    $seen[random_int($low, $high)] = true;
                }

                $numbers = array_keys($seen);
                sort($numbers);
            } else {
                $numbers = [];

                for ($i = 0; $i < $size; $i++) {
                    $numbers[] = random_int($low, $high);
                }
            }

            $result = implode(', ', $numbers);
            $report = [
                'Range'  => number_format($low) . ' to ' . number_format($high),
                'Drawn'  => number_format($size),
                'Unique' => $unique,
            ];
        }
    }
}

require __DIR__ . '/../app/parts/tool.php';
?>

<form method="post" class="card bg-body rounded-4 border shadow-sm p-4">
    <div class="row g-3 align-items-end">
        <div class="col-sm-4 col-lg-3">
            <label class="form-label fw-semibold" for="min">Lowest</label>
            <input class="form-control" id="min" name="min" value="<?= e($min) ?>" autofocus>
        </div>
        <div class="col-sm-4 col-lg-3">
            <label class="form-label fw-semibold" for="max">Highest</label>
            <input class="form-control" id="max" name="max" value="<?= e($max) ?>">
        </div>
        <div class="col-sm-4 col-lg-3">
            <label class="form-label fw-semibold" for="count">How many</label>
            <input class="form-control" id="count" name="count" inputmode="numeric" value="<?= e($count) ?>">
        </div>
        <div class="col-lg-3">
            <div class="form-check">
                <input class="form-check-input" type="checkbox" id="unique" name="unique" <?= $unique ? 'checked' : '' ?>>
                <label class="form-check-label" for="unique">No repeats</label>
            </div>
        </div>
    </div>

    <button class="btn btn-primary mt-4" type="submit">Roll</button>
</form>

<?php if ($error !== ''): ?>
    <?= notice($error) ?>
<?php elseif ($result !== null): ?>
    <?= output($result, 'Numbers', true) ?>
    <?= values($report) ?>
<?php endif; ?>

<?php require __DIR__ . '/../app/parts/explain.php';
