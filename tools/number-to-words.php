<?php
declare(strict_types=1);

require __DIR__ . '/../app/init.php';

$tool = tool('number-to-words');

function n2w_under_1000(int $n): string
{
    $ones = ['', 'one','two','three','four','five','six','seven','eight','nine','ten','eleven','twelve','thirteen','fourteen','fifteen','sixteen','seventeen','eighteen','nineteen'];
    $tens = ['', '', 'twenty','thirty','forty','fifty','sixty','seventy','eighty','ninety'];
    $parts = [];
    if ($n >= 100) { $parts[] = $ones[intdiv($n, 100)] . ' hundred'; $n %= 100; }
    if ($n >= 20) { $parts[] = $tens[intdiv($n, 10)] . ($n % 10 ? '-' . $ones[$n % 10] : ''); }
    elseif ($n > 0) { $parts[] = $ones[$n]; }
    return implode(' ', $parts);
}

function number_to_words(string $input): string
{
    $negative = str_starts_with($input, '-');
    $s = ltrim($input, '+-');
    if (!preg_match('/^\d{1,66}$/', $s)) {
        throw new InvalidArgumentException('Enter a whole number (digits only, optional leading minus).');
    }
    $s = ltrim($s, '0') ?: '0';
    if ($s === '0') { return 'zero'; }
    $scales = ['', ' thousand', ' million', ' billion', ' trillion', ' quadrillion', ' quintillion'];
    $groups = [];
    while ($s !== '') {
        $groups[] = (int) substr($s, -3);
        $s = substr($s, 0, -3);
    }
    $parts = [];
    foreach ($groups as $i => $g) {
        if ($g === 0) { continue; }
        $scale = $scales[$i] ?? (' ×10^' . ($i * 3));
        $parts[] = n2w_under_1000($g) . $scale;
    }
    $words = implode(' ', array_reverse($parts));
    return ($negative ? 'negative ' : '') . $words;
}

$given = posted('number');
$error = '';
$output = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (trim($given) === '') {
        $error = 'Enter a whole number.';
    } else {
        try {
            $output = number_to_words(trim($given));
        } catch (InvalidArgumentException $e) {
            $error = $e->getMessage();
        }
    }
}

require __DIR__ . '/../app/parts/tool.php';
?>

<form method="post" class="card bg-body rounded-4 border shadow-sm p-4">
    <label class="form-label fw-semibold" for="number">Number</label>
    <input class="form-control font-monospace" id="number" name="number" value="<?= e($given) ?>" placeholder="1234567" autofocus>
    <div class="form-text">Whole numbers, including negative values and very large integers.</div>
    <button class="btn btn-primary mt-3" type="submit">Convert to words</button>
</form>

<?php if ($error !== ''): ?>
    <?= notice($error) ?>
<?php elseif ($output !== ''): ?>
    <?= output($output, 'Words') ?>
<?php endif; ?>

<?php require __DIR__ . '/../app/parts/explain.php'; ?>
