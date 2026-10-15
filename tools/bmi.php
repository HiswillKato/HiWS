<?php
declare(strict_types=1);

require __DIR__ . '/../app/init.php';

$tool = tool('bmi');

const SYSTEMS = [
    'metric'   => 'Metric (kg, cm)',
    'imperial' => 'Imperial (lb, in)',
];

const BANDS = [
    ['Underweight', 0.0, 18.5],
    ['Normal weight', 18.5, 25.0],
    ['Overweight', 25.0, 30.0],
    ['Obesity', 30.0, INF],
];

$system = array_key_exists(posted('system'), SYSTEMS) ? posted('system') : 'metric';
$weight = posted('weight');
$height = posted('height');
$report = null;
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!is_numeric($weight) || !is_numeric($height)) {
        $error = 'Put a number in each box.';
    } else {
        $mass = (float) $weight;
        $tall = (float) $height;
        $imperial = $system === 'imperial';

        if ($mass <= 0 || $tall <= 0) {
            $error = 'Both weight and height have to be greater than zero.';
        } else {
            /* Everything is worked in kilograms and metres, so the two systems
               come to the same answer; only the display turns back. */
            $kg = $imperial ? $mass * 0.45359237 : $mass;
            $metres = $imperial ? $tall * 0.0254 : $tall / 100;
            $bmi = $kg / ($metres ** 2);

            $label = 'Obesity';

            foreach (BANDS as [$name, $low, $high]) {
                if ($bmi >= $low && $bmi < $high) {
                    $label = $name;
                    break;
                }
            }

            /* The healthy band for this height, in whatever unit was used. */
            $show = static fn(float $kilograms): string => $imperial
                ? trimmed($kilograms / 0.45359237)
                : trimmed($kilograms);

            $unit = $imperial ? 'lb' : 'kg';

            $report = [
                'BMI'           => trimmed(round($bmi, 1)),
                'Category'      => $label,
                'Healthy range' => $show(18.5 * $metres ** 2) . '–' . $show(24.9 * $metres ** 2) . ' ' . $unit,
            ];
        }
    }
}

require __DIR__ . '/../app/parts/tool.php';
?>

<form method="post" class="card bg-body rounded-4 border shadow-sm p-4">
    <div class="row g-3">
        <div class="col-12">
            <label class="form-label fw-semibold" for="system">Units</label>
            <select class="form-select" id="system" name="system">
                <?php foreach (SYSTEMS as $key => $name): ?>
                    <option value="<?= $key ?>" <?= $system === $key ? 'selected' : '' ?>><?= e($name) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-sm-6">
            <label class="form-label fw-semibold" for="weight">Weight</label>
            <input class="form-control" id="weight" name="weight" value="<?= e($weight) ?>" inputmode="decimal" autofocus>
        </div>
        <div class="col-sm-6">
            <label class="form-label fw-semibold" for="height">Height</label>
            <input class="form-control" id="height" name="height" value="<?= e($height) ?>" inputmode="decimal">
            <p class="form-text mb-0">Imperial height is in inches — 5 ft 9 in is 69.</p>
        </div>
    </div>

    <button class="btn btn-primary mt-4" type="submit">Calculate</button>
</form>

<?php if ($error !== ''): ?>
    <?= notice($error) ?>
<?php elseif ($report !== null): ?>
    <?= values($report) ?>
<?php endif; ?>

<?php require __DIR__ . '/../app/parts/explain.php';
