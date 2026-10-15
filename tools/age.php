<?php
declare(strict_types=1);

require __DIR__ . '/../app/init.php';

$tool = tool('age');

$birth = posted('birth');
$until = posted('until');
$report = null;
$error = '';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $until = date('Y-m-d');
} elseif ($birth === '') {
    $error = 'Enter a date of birth.';
} else {
    $from = strtotime($birth);
    $to = $until === '' ? time() : strtotime($until);

    if ($from === false) {
        $error = 'That date of birth does not read as a date.';
    } elseif ($to === false) {
        $error = 'That "as of" date does not read as a date.';
    } elseif ($to < $from) {
        $error = 'The "as of" date falls before the date of birth.';
    } else {
        /* Days are counted from midnight to midnight, so a birthday reads
           right whatever the time of day is when the page is used. */
        $start = new DateTimeImmutable(date('Y-m-d', $from));
        $end = new DateTimeImmutable(date('Y-m-d', $to));
        $diff = $start->diff($end);

        /* The next birthday, built from the "as of" date's year and rolled
           forward a year when it has already been and gone. */
        $next = $end->setDate((int) $end->format('Y'), (int) $start->format('n'), (int) $start->format('j'));

        if ($next < $end) {
            $next = $next->modify('+1 year');
        }

        $days = (int) $diff->days;

        $report = [
            'Age'           => $diff->y . ' years, ' . $diff->m . ' months, ' . $diff->d . ' days',
            'Total days'    => number_format($days),
            'Total weeks'   => number_format($days / 7, 1),
            'Total months'  => number_format($days / 30.437, 1),
            'Next birthday' => (int) $end->diff($next)->days . ' days away',
        ];
    }
}

require __DIR__ . '/../app/parts/tool.php';
?>

<form method="post" class="card bg-body rounded-4 border shadow-sm p-4">
    <div class="row g-3">
        <div class="col-md-6">
            <label class="form-label fw-semibold" for="birth">Date of birth</label>
            <input class="form-control" type="date" id="birth" name="birth" value="<?= e($birth) ?>" autofocus>
        </div>
        <div class="col-md-6">
            <label class="form-label fw-semibold" for="until">As of</label>
            <input class="form-control" type="date" id="until" name="until" value="<?= e($until) ?>">
            <p class="form-text mb-0">Leave as today to work out a current age.</p>
        </div>
    </div>

    <button class="btn btn-primary mt-4" type="submit">Work it out</button>
</form>

<?php if ($error !== ''): ?>
    <?= notice($error) ?>
<?php elseif ($report !== null): ?>
    <?= values($report) ?>
<?php endif; ?>

<?php require __DIR__ . '/../app/parts/explain.php';
