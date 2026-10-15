<?php
declare(strict_types=1);

require __DIR__ . '/../app/init.php';

$tool = tool('date-diff');

$from = posted('from');
$to = posted('to');
$report = null;
$error = '';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $from = date('Y-m-d');
    $to = date('Y-m-d', strtotime('+30 days'));
} elseif ($from === '' || $to === '') {
    $error = 'Fill in both dates.';
} else {
    $first = strtotime($from);
    $second = strtotime($to);

    if ($first === false || $second === false) {
        $error = 'One of those does not read as a date.';
    } else {
        /* Whichever date came first is the start, so the order they were
           typed in never matters and the counts stay positive. */
        $start = new DateTimeImmutable(date('Y-m-d', min($first, $second)));
        $end = new DateTimeImmutable(date('Y-m-d', max($first, $second)));
        $diff = $start->diff($end);
        $days = (int) $diff->days;

        /* Working days are counted one by one across the span — exact, and
           cheap for any ordinary gap. A span of decades falls back to the
           five-in-seven average rather than looping tens of thousands of
           times. */
        if ($days <= 20_000) {
            $weekdays = 0;

            for ($day = $start; $day < $end; $day = $day->modify('+1 day')) {
                if ((int) $day->format('N') <= 5) {
                    $weekdays++;
                }
            }
        } else {
            $weekdays = (int) round($days / 7 * 5);
        }

        $report = [
            'Days'         => number_format($days),
            'Working days' => number_format($weekdays),
            'Weekend days' => number_format($days - $weekdays),
            'Weeks'        => number_format($days / 7, 1),
            'Months'       => number_format($days / 30.437, 1),
            'Years'        => number_format($days / 365.25, 1),
            'Calendar'     => $diff->y . ' years, ' . $diff->m . ' months, ' . $diff->d . ' days',
        ];
    }
}

require __DIR__ . '/../app/parts/tool.php';
?>

<form method="post" class="card bg-body rounded-4 border shadow-sm p-4">
    <div class="row g-3">
        <div class="col-md-6">
            <label class="form-label fw-semibold" for="from">First date</label>
            <input class="form-control" type="date" id="from" name="from" value="<?= e($from) ?>" autofocus>
        </div>
        <div class="col-md-6">
            <label class="form-label fw-semibold" for="to">Second date</label>
            <input class="form-control" type="date" id="to" name="to" value="<?= e($to) ?>">
        </div>
    </div>

    <button class="btn btn-primary mt-4" type="submit">Compare</button>
</form>

<?php if ($error !== ''): ?>
    <?= notice($error) ?>
<?php elseif ($report !== null): ?>
    <?= values($report) ?>
<?php endif; ?>

<?php require __DIR__ . '/../app/parts/explain.php';
