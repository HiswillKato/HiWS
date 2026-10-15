<?php
declare(strict_types=1);

require __DIR__ . '/../app/init.php';

$tool = tool('unit-converter');

/* Every unit is stored as how many base units it is worth — metres, kilograms,
   litres and so on. Temperature is the exception, because it has an offset as
   well as a scale, so it is handled on its own. */
const GROUPS = [
    'Length' => [
        'Metre (m)' => 1.0, 'Kilometre (km)' => 1000.0, 'Centimetre (cm)' => 0.01,
        'Millimetre (mm)' => 0.001, 'Micrometre (µm)' => 0.000001, 'Mile (mi)' => 1609.344,
        'Yard (yd)' => 0.9144, 'Foot (ft)' => 0.3048, 'Inch (in)' => 0.0254,
        'Nautical mile (nmi)' => 1852.0,
    ],
    'Mass' => [
        'Kilogram (kg)' => 1.0, 'Gram (g)' => 0.001, 'Milligram (mg)' => 0.000001,
        'Tonne (t)' => 1000.0, 'Pound (lb)' => 0.45359237, 'Ounce (oz)' => 0.028349523125,
        'Stone (st)' => 6.35029318,
    ],
    'Volume' => [
        'Litre (L)' => 1.0, 'Millilitre (mL)' => 0.001, 'Cubic metre (m³)' => 1000.0,
        'Gallon (US)' => 3.785411784, 'Quart (US)' => 0.946352946, 'Pint (US)' => 0.473176473,
        'Fluid ounce (US)' => 0.0295735295625, 'Cup (US)' => 0.2365882365,
    ],
    'Area' => [
        'Square metre (m²)' => 1.0, 'Square kilometre (km²)' => 1000000.0,
        'Square foot (ft²)' => 0.09290304, 'Square yard (yd²)' => 0.83612736,
        'Acre' => 4046.8564224, 'Hectare (ha)' => 10000.0,
    ],
    'Speed' => [
        'Metre per second (m/s)' => 1.0, 'Kilometre per hour (km/h)' => 0.27777778,
        'Mile per hour (mph)' => 0.44704, 'Knot (kn)' => 0.51444444,
    ],
    'Data' => [
        'Byte (B)' => 1.0, 'Kilobyte (kB)' => 1000.0, 'Kibibyte (KiB)' => 1024.0,
        'Megabyte (MB)' => 1000000.0, 'Mebibyte (MiB)' => 1048576.0,
        'Gigabyte (GB)' => 1000000000.0, 'Gibibyte (GiB)' => 1073741824.0,
    ],
];
const TEMPS = ['Celsius (°C)', 'Fahrenheit (°F)', 'Kelvin (K)'];

/* Every unit the two selects offer, mapped to the group it belongs to and —
   for the multiplicative ones — the factor. */
function catalogue(): array
{
    $all = [];

    foreach (GROUPS as $group => $units) {
        foreach ($units as $label => $factor) {
            $all[$label] = [$group, $factor];
        }
    }

    foreach (TEMPS as $label) {
        $all[$label] = ['Temperature', 1.0];
    }

    return $all;
}

function to_celsius(float $value, string $unit): float
{
    return match ($unit) {
        'Fahrenheit (°F)' => ($value - 32) * 5 / 9,
        'Kelvin (K)'      => $value - 273.15,
        default           => $value,
    };
}

function from_celsius(float $celsius, string $unit): float
{
    return match ($unit) {
        'Fahrenheit (°F)' => $celsius * 9 / 5 + 32,
        'Kelvin (K)'      => $celsius + 273.15,
        default           => $celsius,
    };
}

$catalogue = catalogue();
$names = array_keys($catalogue);

$from = posted('from');
$to = posted('to');
$give = posted('value');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $from = 'Metre (m)';
    $to = 'Foot (ft)';
    $give = '1';
}

$from = in_array($from, $names, true) ? $from : $names[0];
$to = in_array($to, $names, true) ? $to : $names[1];
$answer = null;
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    [$fromGroup, $fromFactor] = $catalogue[$from];
    [$toGroup, $toFactor] = $catalogue[$to];

    if (!is_numeric($give)) {
        $error = 'Enter a number to convert.';
    } elseif ($fromGroup !== $toGroup) {
        $error = 'Those are different kinds of measurement — pick two of the same kind.';
    } elseif ($fromGroup === 'Temperature') {
        $answer = from_celsius(to_celsius((float) $give, $from), $to);
    } else {
        $answer = (float) $give * $fromFactor / $toFactor;
    }
}

require __DIR__ . '/../app/parts/tool.php';
?>

<form method="post" class="card bg-body rounded-4 border shadow-sm p-4">
    <div class="row g-3 align-items-end">
        <div class="col-sm-3">
            <label class="form-label fw-semibold" for="value">Amount</label>
            <input class="form-control" id="value" name="value" value="<?= e($give) ?>" autocomplete="off" autofocus>
        </div>
        <div class="col-sm-9">
            <div class="row g-2">
                <div class="col">
                    <label class="form-label fw-semibold" for="from">From</label>
                    <select class="form-select" id="from" name="from">
                        <?php foreach (GROUPS as $group => $units): ?>
                            <optgroup label="<?= e($group) ?>">
                                <?php foreach (array_keys($units) as $label): ?>
                                    <option <?= $from === $label ? 'selected' : '' ?>><?= e($label) ?></option>
                                <?php endforeach; ?>
                            </optgroup>
                        <?php endforeach; ?>
                        <optgroup label="Temperature">
                            <?php foreach (TEMPS as $label): ?>
                                <option <?= $from === $label ? 'selected' : '' ?>><?= e($label) ?></option>
                            <?php endforeach; ?>
                        </optgroup>
                    </select>
                </div>
                <div class="col">
                    <label class="form-label fw-semibold" for="to">To</label>
                    <select class="form-select" id="to" name="to">
                        <?php foreach (GROUPS as $group => $units): ?>
                            <optgroup label="<?= e($group) ?>">
                                <?php foreach (array_keys($units) as $label): ?>
                                    <option <?= $to === $label ? 'selected' : '' ?>><?= e($label) ?></option>
                                <?php endforeach; ?>
                            </optgroup>
                        <?php endforeach; ?>
                        <optgroup label="Temperature">
                            <?php foreach (TEMPS as $label): ?>
                                <option <?= $to === $label ? 'selected' : '' ?>><?= e($label) ?></option>
                            <?php endforeach; ?>
                        </optgroup>
                    </select>
                </div>
            </div>
        </div>
    </div>

    <button class="btn btn-primary mt-4" type="submit">Convert</button>
</form>

<?php if ($error !== ''): ?>
    <?= notice($error) ?>
<?php elseif ($answer !== null): ?>
    <?php
    /* The same amount shown in every unit of its group, so a conversion never
       has to be done twice. */
    $all = [$to => trimmed($answer)];

    foreach ($catalogue as $label => [$group, $factor]) {
        if ($group !== $fromGroup || $label === $to) {
            continue;
        }

        $all[$label] = $fromGroup === 'Temperature'
            ? trimmed(from_celsius(to_celsius((float) $give, $from), $label))
            : trimmed((float) $give * $fromFactor / $factor);
    }
    ?>

    <p class="h3 mt-4 mb-0">
        <?= e(trimmed((float) $give)) ?> <?= e($from) ?> =
        <span class="text-primary"><?= e(trimmed($answer)) ?> <?= e($to) ?></span>
    </p>

    <?= values($all, 'The same amount in every ' . e(strtolower($fromGroup)) . ' unit') ?>
<?php endif; ?>

<?php require __DIR__ . '/../app/parts/explain.php';
