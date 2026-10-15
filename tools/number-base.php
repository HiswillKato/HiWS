<?php
declare(strict_types=1);

require __DIR__ . '/../app/init.php';

$tool = tool('number-base');

const BASES = [
    2  => 'Binary (2)',
    8  => 'Octal (8)',
    10 => 'Decimal (10)',
    16 => 'Hexadecimal (16)',
];

$given = posted('value');
$from = array_key_exists((int) posted('base'), BASES) ? (int) posted('base') : 10;
$result = null;
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    /* A 0x, 0b or 0o prefix is a hint, not part of the number, so it goes
       before the digits are checked against the chosen base. */
    $clean = strtolower((string) preg_replace('/^(0x|0b|0o)/i', '', trim($given)));
    $digits = '0123456789abcdefghijklmnopqrstuvwxyz';

    if ($given === '') {
        $error = 'Enter a number.';
    } elseif (strspn($clean, substr($digits, 0, $from)) !== strlen($clean) || $clean === '') {
        $error = 'That is not a base-' . $from . ' number.';
    } else {
        $value = (int) intval($clean, $from);

        $result = [
            'Binary'      => decbin($value),
            'Octal'       => decoct($value),
            'Decimal'     => (string) $value,
            'Hexadecimal' => strtoupper(dechex($value)),
        ];
    }
}

require __DIR__ . '/../app/parts/tool.php';
?>

<form method="post" class="card bg-body rounded-4 border shadow-sm p-4">
    <div class="row g-3 align-items-end">
        <div class="col">
            <label class="form-label fw-semibold" for="value">Number</label>
            <input class="form-control font-monospace" id="value" name="value" value="<?= e($given) ?>"
                   placeholder="255" autocomplete="off" autofocus>
        </div>
        <div class="col-sm-4">
            <label class="form-label fw-semibold" for="base">It is written in</label>
            <select class="form-select" id="base" name="base">
                <?php foreach (BASES as $value => $label): ?>
                    <option value="<?= $value ?>" <?= $from === $value ? 'selected' : '' ?>><?= e($label) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>

    <button class="btn btn-primary mt-4" type="submit">Convert</button>
</form>

<?php if ($error !== ''): ?>
    <?= notice($error) ?>
<?php elseif ($result !== null): ?>
    <?= values($result) ?>
<?php endif; ?>

<?php require __DIR__ . '/../app/parts/explain.php';
