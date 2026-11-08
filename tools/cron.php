<?php
declare(strict_types=1);

require __DIR__ . '/../app/init.php';

$tool = tool('cron');

/* Parse one cron field into a set of allowed integer values, or null if it is
   not in a form this explainer understands. */
function cron_field(string $field, int $min, int $max, array $names = []): ?array
{
    $field = trim($field);
    if ($field === '') {
        return null;
    }
    $upper = strtoupper($field);
    foreach ($names as $name => $value) {
        $upper = str_replace(strtoupper($name), (string) $value, $upper);
    }

    $values = [];
    foreach (explode(',', $upper) as $part) {
        $part = trim($part);
        if ($part === '') {
            return null;
        }
        $step = 1;
        if (str_contains($part, '/')) {
            [$part, $step_str] = explode('/', $part, 2);
            if (!ctype_digit($step_str) || (int) $step_str < 1) {
                return null;
            }
            $step = (int) $step_str;
        }
        if ($part === '*') {
            $start = $min;
            $end = $max;
        } elseif (preg_match('/^(-?\d+)-(-?\d+)$/', $part, $m)) {
            $start = (int) $m[1];
            $end = (int) $m[2];
        } elseif (preg_match('/^-?\d+$/', $part)) {
            $start = (int) $part;
            $end = str_contains($upper, '/') && !str_contains($part, '-') && explode('/', $field, 2)[0] !== '*' ? $start : $start;
            if ($step > 1 && $part !== '*' && !str_contains(explode('/', $upper, 2)[0] ?? '', '-')) {
                $end = $max;
            }
        } else {
            return null;
        }

        if ($start < $min || $end > $max || $start > $end) {
            return null;
        }
        for ($i = $start; $i <= $end; $i += $step) {
            $values[$i] = true;
        }
    }

    $out = array_keys($values);
    sort($out);

    return $out === [] ? null : $out;
}

/* Describe one field in plain language. */
function cron_describe(array $values, int $min, int $max, string $unit, string $every): string
{
    if (count($values) === ($max - $min + 1)) {
        return $every;
    }
    if (count($values) <= 8) {
        return $unit . ' ' . implode(', ', array_map('strval', $values));
    }

    return $unit . ' (' . count($values) . ' values)';
}

/* Find the next run times by walking forward minute by minute, capped so a
   bad expression cannot spin forever. */
function cron_next(array $fields, int $count = 5): array
{
    [$minutes, $hours, $doms, $months, $dows] = $fields;
    $allowed_minute = array_fill_keys($minutes, true);
    $allowed_hour = array_fill_keys($hours, true);
    $allowed_dom = array_fill_keys($doms, true);
    $allowed_month = array_fill_keys($months, true);
    $allowed_dow = array_fill_keys($dows, true);

    $time = (int) (floor(time() / 60) * 60) + 60;
    $limit = $time + 366 * 86400;
    $found = [];

    while ($time <= $limit && count($found) < $count) {
        $parts = getdate($time);
        $month = (int) $parts['mon'];
        $dom = (int) $parts['mday'];
        $hour = (int) $parts['hours'];
        $minute = (int) $parts['minutes'];
        $dow = (int) $parts['wday'];

        if (!isset($allowed_month[$month])) {
            $time += 3600;
            continue;
        }
        if (!isset($allowed_dom[$dom]) || !isset($allowed_dow[$dow]) || !isset($allowed_hour[$hour])) {
            $time += 60;
            continue;
        }
        if (isset($allowed_minute[$minute])) {
            $found[] = date('D, j M Y H:i T', $time);
        }
        $time += 60;
    }

    return $found;
}

$expression = posted('expression');
$error = '';
$summary = [];
$next = [];
$parts_out = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $expression = trim(preg_replace('/\s+/', ' ', $expression) ?? '');
    $parts = $expression === '' ? [] : explode(' ', $expression);
    if (count($parts) < 5) {
        $error = 'Enter five fields: minute, hour, day of month, month, day of week (for example, */15 9-17 * * 1-5).';
    } elseif (count($parts) > 5) {
        $error = 'This explainer reads the standard five fields. Leave out the seconds or year field.';
    } else {
        $month_names = ['JAN' => 1, 'FEB' => 2, 'MAR' => 3, 'APR' => 4, 'MAY' => 5, 'JUN' => 6, 'JUL' => 7, 'AUG' => 8, 'SEP' => 9, 'OCT' => 10, 'NOV' => 11, 'DEC' => 12];
        $dow_names = ['SUN' => 0, 'MON' => 1, 'TUE' => 2, 'WED' => 3, 'THU' => 4, 'FRI' => 5, 'SAT' => 6];
        $minutes = cron_field($parts[0], 0, 59);
        $hours = cron_field($parts[1], 0, 23);
        $doms = cron_field($parts[2], 1, 31);
        $months = cron_field($parts[3], 1, 12, $month_names);
        $dows = cron_field($parts[4], 0, 7, $dow_names);
        if ($dows !== null) {
            $dows = array_values(array_unique(array_map(static fn(int $v): int => $v === 7 ? 0 : $v, $dows)));
            sort($dows);
        }

        if ($minutes === null || $hours === null || $doms === null || $months === null || $dows === null) {
            $error = 'Could not read that expression. Use numbers, *, ranges like 1-5, lists like 1,15,30, or steps like */10.';
        } else {
            $parts_out = [
                ['Minute', $parts[0], cron_describe($minutes, 0, 59, 'At minute', 'Every minute')],
                ['Hour', $parts[1], cron_describe($hours, 0, 23, 'At hour', 'Every hour')],
                ['Day of month', $parts[2], cron_describe($doms, 1, 31, 'On day', 'Every day of the month')],
                ['Month', $parts[3], cron_describe($months, 1, 12, 'In month', 'Every month')],
                ['Day of week', $parts[4], cron_describe($dows, 0, 6, 'On weekday', 'Every day of the week')],
            ];
            $next = cron_next([$minutes, $hours, $doms, $months, $dows], 5);
        }
    }
}

require __DIR__ . '/../app/parts/tool.php';
?>

<form method="post" class="card bg-body rounded-4 border shadow-sm p-4">
    <label class="form-label fw-semibold" for="expression">Cron expression</label>
    <div class="input-group">
        <input class="form-control font-monospace" id="expression" name="expression" value="<?= e($expression) ?>"
               placeholder="*/15 9-17 * * 1-5" required autofocus>
        <button class="btn btn-primary" type="submit">Explain</button>
    </div>
    <p class="form-text mb-0 mt-2">Standard five fields: <code>minute hour day-of-month month day-of-week</code>. Supports <code>*</code>, lists, ranges and steps.</p>
</form>

<?php if ($error !== ''): ?>
    <?= notice($error) ?>
<?php elseif ($parts_out !== []): ?>
    <div class="card bg-body rounded-4 border shadow-sm mt-4">
        <div class="table-responsive">
            <table class="table table-sm align-middle mb-0">
                <thead>
                    <tr>
                        <th scope="col">Field</th>
                        <th scope="col">Value</th>
                        <th scope="col">Meaning</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($parts_out as $row): ?>
                        <tr>
                            <th scope="row" class="fw-semibold"><?= e($row[0]) ?></th>
                            <td><code><?= e($row[1]) ?></code></td>
                            <td><?= e($row[2]) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="card bg-body rounded-4 border shadow-sm mt-4">
        <div class="card-header bg-transparent fw-semibold">Next runs</div>
        <?php if ($next === []): ?>
            <div class="p-3 text-body-secondary">No run times found in the next year. Check the day and month fields together.</div>
        <?php else: ?>
            <ul class="list-group list-group-flush">
                <?php foreach ($next as $when): ?>
                    <li class="list-group-item"><code><?= e($when) ?></code></li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </div>
<?php endif; ?>

<?php require __DIR__ . '/../app/parts/explain.php'; ?>
