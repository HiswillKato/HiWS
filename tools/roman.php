<?php
declare(strict_types=1);

require __DIR__ . '/../app/init.php';

$tool = tool('roman');

const NUMERALS = [
    1000 => 'M', 900 => 'CM', 500 => 'D', 400 => 'CD', 100 => 'C', 90 => 'XC',
    50 => 'L', 40 => 'XL', 10 => 'X', 9 => 'IX', 5 => 'V', 4 => 'IV', 1 => 'I',
];

function to_roman(int $number): string
{
    $out = '';

    foreach (NUMERALS as $value => $letter) {
        while ($number >= $value) {
            $out .= $letter;
            $number -= $value;
        }
    }

    return $out;
}

/* The shape is checked first, so "IIII" and "VX" are refused rather than
   summed into a number they were never meant to be. */
function from_roman(string $letters): ?int
{
    $letters = strtoupper($letters);

    if (preg_match('/^(?=[MDCLXVI])M{0,3}(CM|CD|D?C{0,3})(XC|XL|L?X{0,3})(IX|IV|V?I{0,3})$/', $letters) !== 1) {
        return null;
    }

    $map = ['I' => 1, 'V' => 5, 'X' => 10, 'L' => 50, 'C' => 100, 'D' => 500, 'M' => 1000];
    $total = 0;
    $previous = 0;

    for ($i = strlen($letters) - 1; $i >= 0; $i--) {
        $value = $map[$letters[$i]];
        $total += $value < $previous ? -$value : $value;
        $previous = $value;
    }

    return $total;
}

$given = posted('value');
$result = null;
$label = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $given = trim($given);

    if ($given === '') {
        $error = 'Enter a number or a Roman numeral.';
    } elseif (ctype_digit($given)) {
        if ((int) $given < 1 || (int) $given > 3999) {
            $error = 'Roman numerals here cover 1 to 3999.';
        } else {
            $result = to_roman((int) $given);
            $label = 'Roman numeral';
        }
    } else {
        $number = from_roman($given);

        if ($number === null) {
            $error = 'That is not a Roman numeral — only I, V, X, L, C, D and M in their proper order.';
        } else {
            $result = (string) $number;
            $label = 'Number';
        }
    }
}

require __DIR__ . '/../app/parts/tool.php';
?>

<form method="post" class="card bg-body rounded-4 border shadow-sm p-4">
    <label class="form-label fw-semibold" for="value">Number or Roman numeral</label>
    <div class="input-group">
        <input class="form-control font-monospace" id="value" name="value" value="<?= e($given) ?>"
               placeholder="1994 or MCMXCIV" autocomplete="off" autofocus>
        <button class="btn btn-primary" type="submit">Convert</button>
    </div>
    <p class="form-text mb-0 mt-2">Digits go one way, letters the other — it works out which.</p>
</form>

<?php if ($error !== ''): ?>
    <?= notice($error) ?>
<?php elseif ($result !== null): ?>
    <?= output($result, $label) ?>
<?php endif; ?>

<?php require __DIR__ . '/../app/parts/explain.php';
