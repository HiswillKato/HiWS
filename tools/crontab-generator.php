<?php
declare(strict_types=1);

require __DIR__ . '/../app/init.php';

$tool = tool('crontab-generator');

/* Build one cron field from a simple mode + value. Values are trusted only as
   form input and validated against allowed characters/ranges. */
function cron_gen_field(string $mode, string $value, string $start, string $end, string $step, int $min, int $max): ?string
{
    $clean = static function (string $v) use ($min, $max): ?string {
        $v = trim($v);
        if ($v === '') {
            return null;
        }
        if (!preg_match('/^[0-9,\-*\/]+$/', $v)) {
            return null;
        }
        foreach (preg_split('/[,\-\/]/', $v) ?: [] as $part) {
            if ($part === '' || $part === '*') {
                continue;
            }
            if (!ctype_digit($part) || (int) $part < $min || (int) $part > $max) {
                return null;
            }
        }
        return $v;
    };

    return match ($mode) {
        'every' => '*',
        'at' => $clean($value),
        'step' => ($step !== '' && ctype_digit($step) && (int) $step >= 1 && (int) $step <= $max) ? '*/' . (int) $step : null,
        'range' => ($start !== '' && $end !== '' && ctype_digit($start) && ctype_digit($end) && (int) $start >= $min && (int) $end <= $max && (int) $start <= (int) $end)
            ? ((int) $start . '-' . (int) $end . (($step !== '' && ctype_digit($step) && (int) $step > 1) ? '/' . (int) $step : ''))
            : null,
        'custom' => $clean($value),
        default => '*',
    };
}

$fields = [
    'minute' => ['label' => 'Minute', 'min' => 0, 'max' => 59, 'hint' => '0-59'],
    'hour'   => ['label' => 'Hour', 'min' => 0, 'max' => 23, 'hint' => '0-23'],
    'dom'    => ['label' => 'Day of month', 'min' => 1, 'max' => 31, 'hint' => '1-31'],
    'month'  => ['label' => 'Month', 'min' => 1, 'max' => 12, 'hint' => '1-12'],
    'dow'    => ['label' => 'Day of week', 'min' => 0, 'max' => 7, 'hint' => '0-7 (Sun=0 or 7)'],
];

$state = [];
foreach ($fields as $key => $meta) {
    $state[$key] = [
        'mode'  => posted($key . '_mode') !== '' ? posted($key . '_mode') : 'every',
        'value' => posted($key . '_value'),
        'start' => posted($key . '_start'),
        'end'   => posted($key . '_end'),
        'step'  => posted($key . '_step'),
    ];
}

$expression = '';
$error = '';
$parts_out = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $parts = [];
    foreach ($fields as $key => $meta) {
        $s = $state[$key];
        $part = cron_gen_field($s['mode'], $s['value'], $s['start'], $s['end'], $s['step'], $meta['min'], $meta['max']);
        if ($part === null) {
            $error = 'Check the ' . strtolower($meta['label']) . ' field. Use valid values within ' . $meta['hint'] . '.';
            break;
        }
        $parts[$key] = $part;
    }
    if ($error === '' && $parts !== []) {
        $expression = implode(' ', [$parts['minute'], $parts['hour'], $parts['dom'], $parts['month'], $parts['dow']]);
        $parts_out = [
            ['Minute', $parts['minute']],
            ['Hour', $parts['hour']],
            ['Day of month', $parts['dom']],
            ['Month', $parts['month']],
            ['Day of week', $parts['dow']],
        ];
    }
}

require __DIR__ . '/../app/parts/tool.php';
?>

<form method="post" class="card bg-body rounded-4 border shadow-sm p-4">
    <div class="row g-3">
        <?php foreach ($fields as $key => $meta): $s = $state[$key]; ?>
            <div class="col-12">
                <div class="border rounded-3 p-3">
                    <div class="d-flex flex-wrap align-items-center gap-2 mb-2">
                        <span class="fw-semibold"><?= e($meta['label']) ?></span>
                        <span class="text-body-secondary small">(<?= e($meta['hint']) ?>)</span>
                    </div>
                    <div class="row g-2 align-items-end">
                        <div class="col-sm-4 col-md-3">
                            <label class="form-label small mb-1" for="<?= e($key) ?>_mode">Mode</label>
                            <select class="form-select form-select-sm" id="<?= e($key) ?>_mode" name="<?= e($key) ?>_mode">
                                <?php foreach (['every' => 'Every', 'at' => 'At specific value(s)', 'step' => 'Every N', 'range' => 'Range', 'custom' => 'Custom'] as $val => $label): ?>
                                    <option value="<?= e($val) ?>"<?= $s['mode'] === $val ? ' selected' : '' ?>><?= e($label) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-sm-8 col-md-3">
                            <label class="form-label small mb-1" for="<?= e($key) ?>_value">Value / custom</label>
                            <input class="form-control form-control-sm font-monospace" id="<?= e($key) ?>_value" name="<?= e($key) ?>_value" value="<?= e($s['value']) ?>" placeholder="e.g. 0,15,30">
                        </div>
                        <div class="col-4 col-md-2">
                            <label class="form-label small mb-1" for="<?= e($key) ?>_start">Start</label>
                            <input class="form-control form-control-sm font-monospace" id="<?= e($key) ?>_start" name="<?= e($key) ?>_start" value="<?= e($s['start']) ?>">
                        </div>
                        <div class="col-4 col-md-2">
                            <label class="form-label small mb-1" for="<?= e($key) ?>_end">End</label>
                            <input class="form-control form-control-sm font-monospace" id="<?= e($key) ?>_end" name="<?= e($key) ?>_end" value="<?= e($s['end']) ?>">
                        </div>
                        <div class="col-4 col-md-2">
                            <label class="form-label small mb-1" for="<?= e($key) ?>_step">Step (N)</label>
                            <input class="form-control form-control-sm font-monospace" id="<?= e($key) ?>_step" name="<?= e($key) ?>_step" value="<?= e($s['step']) ?>">
                        </div>
                    </div>
                    <div class="form-text small mb-0">Every = <code>*</code>, At = use <em>Value</em> (e.g. <code>5</code> or <code>0,30</code>), Every N = <code>*/N</code>, Range = start-end or start-end/step, Custom = raw field.</div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
    <button class="btn btn-primary mt-3" type="submit">Generate crontab</button>
</form>

<?php if ($error !== ''): ?>
    <?= notice($error) ?>
<?php elseif ($expression !== ''): ?>
    <div class="card bg-body rounded-4 border shadow-sm mt-4">
        <div class="card-header bg-transparent d-flex justify-content-between align-items-center">
            <span class="fw-semibold">Crontab expression</span>
            <?= clip($expression, 'Copy') ?>
        </div>
        <div class="p-3">
            <code class="fs-5 text-break"><?= e($expression) ?></code>
            <div class="mt-3">
                <span class="text-body-secondary small d-block mb-1">Example crontab line</span>
                <div class="d-flex justify-content-between align-items-start gap-2">
                    <code class="text-break"><?= e($expression . ' /path/to/command') ?></code>
                    <?= clip($expression . ' /path/to/command') ?>
                </div>
            </div>
        </div>
    </div>

    <div class="card bg-body rounded-4 border shadow-sm mt-4">
        <div class="table-responsive">
            <table class="table table-sm align-middle mb-0">
                <thead><tr><th scope="col">Field</th><th scope="col">Value</th></tr></thead>
                <tbody>
                    <?php foreach ($parts_out as $row): ?>
                        <tr><th scope="row" class="fw-semibold"><?= e($row[0]) ?></th><td><code><?= e($row[1]) ?></code></td></tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <p class="text-body-secondary small mt-3 mb-0">Want to check what an expression means? Use the <a href="<?= url('tools/cron') ?>">Cron Expression Explainer</a>.</p>
<?php endif; ?>

<?php require __DIR__ . '/../app/parts/explain.php'; ?>
