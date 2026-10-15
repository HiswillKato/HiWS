<?php
declare(strict_types=1);

require __DIR__ . '/../app/init.php';

$tool = tool('password-strength');

/* Seconds spoken the way a person would say them. Past a million the number
   stops meaning anything, so it flips to scientific notation. */
function duration(float $seconds): string
{
    if ($seconds < 1) {
        return 'less than a second';
    }

    foreach (['years' => 31_557_600, 'days' => 86_400, 'hours' => 3_600, 'minutes' => 60, 'seconds' => 1] as $name => $size) {
        if ($seconds >= $size) {
            $value = $seconds / $size;

            if ($value >= 1e9) {
                $power = (int) floor(log10($value));

                return number_format($value / 10 ** $power, 1) . ' × 10^' . $power . ' ' . $name;
            }

            return number_format($value, $value >= 10 ? 0 : 1) . ' ' . $name;
        }
    }

    return 'less than a second';
}

$given = posted('password');
$report = null;
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($given === '') {
        $error = 'Type a password to measure.';
    } else {
        /* How large an alphabet the password draws on, and how many guesses a
           fast offline attack makes in a second — a round billion per second
           per GPU is a fair modern figure. */
        $pool = 0;
        $missing = [];

        foreach ([
            '[a-z]'       => [26, 'lowercase letters'],
            '[A-Z]'       => [26, 'uppercase letters'],
            '[0-9]'       => [10, 'digits'],
            '[^a-zA-Z0-9]' => [33, 'symbols'],
        ] as $pattern => [$size, $label]) {
            if (preg_match('/' . $pattern . '/', $given) === 1) {
                $pool += $size;
            } else {
                $missing[] = $label;
            }
        }

        $pool = max($pool, 1);
        $log10 = strlen($given) * log10($pool);
        $bits = (int) round($log10 / log10(2));
        $seconds = 10 ** ($log10 - 10 - log10(2));

        $band = match (true) {
            $bits < 28 => 'Very weak',
            $bits < 36 => 'Weak',
            $bits < 60 => 'Reasonable',
            $bits < 80 => 'Strong',
            default    => 'Very strong',
        };

        $report = [
            'Length'      => strlen($given) . ' characters',
            'Alphabet'    => $pool . ' possible characters',
            'Entropy'     => $bits . ' bits',
            'Guesses'     => number_format(10 ** $log10, 0) . ' combinations',
            'Time to crack' => duration($seconds) . ' (at a billion guesses a second)',
            'Verdict'     => $band,
        ];
    }
}

require __DIR__ . '/../app/parts/tool.php';
?>

<form method="post" class="card bg-body rounded-4 border shadow-sm p-4">
    <label class="form-label fw-semibold" for="password">Password</label>
    <div class="input-group">
        <input class="form-control font-monospace" id="password" name="password" type="text"
               value="<?= e($given) ?>" autocomplete="off" autofocus>
        <button class="btn btn-primary" type="submit">Measure</button>
    </div>
    <p class="form-text mb-0 mt-2">Checked here, never stored or sent anywhere.</p>
</form>

<?php if ($error !== ''): ?>
    <?= notice($error) ?>
<?php elseif ($report !== null): ?>
    <?= values($report) ?>

    <?php $percent = min(100, (int) round($bits / 100 * 100)); ?>
    <div class="progress mt-4" role="progressbar" aria-label="Password entropy"
         aria-valuenow="<?= $percent ?>" aria-valuemin="0" aria-valuemax="100" style="height: .5rem">
        <div class="progress-bar" style="width: <?= $percent ?>%"></div>
    </div>

    <?php if ($missing !== []): ?>
        <p class="text-body-secondary small mt-3 mb-0">
            Missing <?= e(implode(', ', $missing)) ?>. Length matters more than variety,
            but both help.
        </p>
    <?php endif; ?>
<?php endif; ?>

<?php require __DIR__ . '/../app/parts/explain.php';
