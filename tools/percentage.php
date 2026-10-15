<?php
declare(strict_types=1);

require __DIR__ . '/../app/init.php';

$tool = tool('percentage');

const MODES = [
    'of'     => 'What is A% of B?',
    'part'   => 'A is what percent of B?',
    'change' => 'Percentage change from A to B',
    'more'   => 'Increase B by A%',
    'less'   => 'Decrease B by A%',
];

$mode = array_key_exists(posted('mode'), MODES) ? posted('mode') : 'of';
$first = posted('first');
$second = posted('second');
$value = null;
$note = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!is_numeric($first) || !is_numeric($second)) {
        $error = 'Put a number in each box.';
    } else {
        $a = (float) $first;
        $b = (float) $second;
        $plain = static fn(float $x): string => trimmed($x);

        if ($mode === 'of') {
            $value = $a / 100 * $b;
            $note = $plain($a) . '% of ' . $plain($b) . ' is ' . $plain($value) . '.';
        } elseif ($mode === 'part') {
            if ($b == 0.0) {
                $error = 'The second number cannot be zero — nothing is a percentage of nothing.';
            } else {
                $value = $a / $b * 100;
                $note = $plain($a) . ' is ' . $plain($value) . '% of ' . $plain($b) . '.';
            }
        } elseif ($mode === 'change') {
            if ($a == 0.0) {
                $error = 'The starting value cannot be zero — the change is measured from it.';
            } else {
                $value = ($b - $a) / abs($a) * 100;
                $note = 'From ' . $plain($a) . ' to ' . $plain($b) . ' is a '
                    . $plain(abs($value)) . '% ' . ($value >= 0 ? 'increase' : 'decrease') . '.';
            }
        } elseif ($mode === 'more') {
            $value = $b * (1 + $a / 100);
            $note = $plain($b) . ' increased by ' . $plain($a) . '% is ' . $plain($value) . '.';
        } else {
            $value = $b * (1 - $a / 100);
            $note = $plain($b) . ' decreased by ' . $plain($a) . '% is ' . $plain($value) . '.';
        }
    }
}

require __DIR__ . '/../app/parts/tool.php';
?>

<form method="post" class="card bg-body rounded-4 border shadow-sm p-4">
    <div class="row g-3">
        <div class="col-12">
            <label class="form-label fw-semibold" for="mode">Work out</label>
            <select class="form-select" id="mode" name="mode">
                <?php foreach (MODES as $key => $label): ?>
                    <option value="<?= $key ?>" <?= $mode === $key ? 'selected' : '' ?>><?= e($label) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-sm-6">
            <label class="form-label fw-semibold" for="first">First number (A)</label>
            <input class="form-control" id="first" name="first" value="<?= e($first) ?>" inputmode="decimal" autofocus>
        </div>
        <div class="col-sm-6">
            <label class="form-label fw-semibold" for="second">Second number (B)</label>
            <input class="form-control" id="second" name="second" value="<?= e($second) ?>" inputmode="decimal">
        </div>
    </div>

    <button class="btn btn-primary mt-4" type="submit">Calculate</button>
</form>

<?php if ($error !== ''): ?>
    <?= notice($error) ?>
<?php elseif ($value !== null): ?>
    <div class="card bg-body rounded-4 border shadow-sm mt-4">
        <div class="card-body text-center py-4">
            <div class="display-6 fw-semibold text-primary"><?= e(trimmed($value)) ?></div>
            <p class="text-body-secondary mb-0 mt-2"><?= e($note) ?></p>
            <div class="mt-3"><?= clip(trimmed($value)) ?></div>
        </div>
    </div>
<?php endif; ?>

<?php require __DIR__ . '/../app/parts/explain.php';
