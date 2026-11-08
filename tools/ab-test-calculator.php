<?php
declare(strict_types=1);

require __DIR__ . '/../app/init.php';

$tool = tool('ab-test-calculator');

function ab_z_prob(float $z): float
{
    /* Standard normal CDF approximation (Abramowitz & Stegun 26.2.17). */
    $x = abs($z);
    $t = 1.0 / (1.0 + 0.2316419 * $x);
    $d = 0.3989422804014327 * exp(-$x * $x / 2.0);
    $p = $d * $t * (0.319381530 + $t * (-0.356563782 + $t * (1.781477937 + $t * (-1.821255978 + $t * 1.330274429))));
    $cdf = $z >= 0 ? 1.0 - $p : $p;
    return $cdf;
}

$ca = posted('ca') !== '' ? posted('ca') : '120';
$va = posted('va') !== '' ? posted('va') : '3000';
$cb = posted('cb') !== '' ? posted('cb') : '150';
$vb = posted('vb') !== '' ? posted('vb') : '3000';
$error = '';
$result = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach ([['Control conversions', $ca], ['Control visitors', $va], ['Variant conversions', $cb], ['Variant visitors', $vb]] as [$label, $val]) {
        if ($val === '' || !ctype_digit($val)) {
            $error = $label . ' must be a whole number.';
            break;
        }
    }
    if ($error === '') {
        $caN = (int) $ca; $vaN = (int) $va; $cbN = (int) $cb; $vbN = (int) $vb;
        if ($vaN <= 0 || $vbN <= 0) {
            $error = 'Visitor counts must be greater than zero.';
        } elseif ($caN > $vaN || $cbN > $vbN) {
            $error = 'Conversions cannot be greater than visitors.';
        } else {
            $ra = $caN / $vaN;
            $rb = $cbN / $vbN;
            $pooled = ($caN + $cbN) / ($vaN + $vbN);
            $se = sqrt($pooled * (1 - $pooled) * (1 / $vaN + 1 / $vbN));
            $z = $se > 0 ? ($rb - $ra) / $se : 0.0;
            $p = $se > 0 ? 2 * (1 - ab_z_prob(abs($z))) : 1.0;
            $uplift = $ra > 0 ? (($rb - $ra) / $ra) * 100 : 0.0;
            $conf = (1 - $p) * 100;
            $result = [
                'Control rate' => number_format($ra * 100, 2) . '% (' . $caN . ' / ' . $vaN . ')',
                'Variant rate' => number_format($rb * 100, 2) . '% (' . $cbN . ' / ' . $vbN . ')',
                'Relative uplift' => ($uplift >= 0 ? '+' : '') . number_format($uplift, 2) . '%',
                'Z-score' => number_format($z, 4),
                'p-value (two-tailed)' => number_format($p, 4),
                'Confidence' => number_format(max(0, min(100, $conf)), 2) . '%',
                'Significant at 95%?' => $p < 0.05 ? 'Yes' : 'No',
            ];
        }
    }
}

require __DIR__ . '/../app/parts/tool.php';
?>

<form method="post" class="card bg-body rounded-4 border shadow-sm p-4">
    <div class="row g-3">
        <div class="col-md-6">
            <h2 class="h6 fw-semibold">Control (A)</h2>
            <label class="form-label" for="ca">Conversions</label>
            <input class="form-control font-monospace" id="ca" name="ca" value="<?= e($ca) ?>" inputmode="numeric">
            <label class="form-label mt-2" for="va">Visitors</label>
            <input class="form-control font-monospace" id="va" name="va" value="<?= e($va) ?>" inputmode="numeric">
        </div>
        <div class="col-md-6">
            <h2 class="h6 fw-semibold">Variant (B)</h2>
            <label class="form-label" for="cb">Conversions</label>
            <input class="form-control font-monospace" id="cb" name="cb" value="<?= e($cb) ?>" inputmode="numeric">
            <label class="form-label mt-2" for="vb">Visitors</label>
            <input class="form-control font-monospace" id="vb" name="vb" value="<?= e($vb) ?>" inputmode="numeric">
        </div>
    </div>
    <button class="btn btn-primary mt-3" type="submit">Calculate significance</button>
</form>

<?php if ($error !== ''): ?>
    <?= notice($error) ?>
<?php elseif ($result !== null): ?>
    <?= values($result) ?>
    <p class="text-body-secondary small mt-3 mb-0">Uses a two-proportion z-test on pooled variance. Statistical significance does not prove causation; run the test long enough and avoid peeking at partial results.</p>
<?php endif; ?>

<?php require __DIR__ . '/../app/parts/explain.php'; ?>
