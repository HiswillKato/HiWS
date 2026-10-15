<?php
declare(strict_types=1);

require __DIR__ . '/../app/init.php';

$tool = tool('timestamp');

/* How far a moment sits from now, in the largest unit that still reads as a
   whole number — "3 weeks ago" rather than "21 days ago". */
function span(int $then, int $now): string
{
    $gap = abs($now - $then);
    $past = $then <= $now;
    $units = [
        'year'   => 31_536_000,
        'month'  => 2_592_000,
        'week'   => 604_800,
        'day'    => 86_400,
        'hour'   => 3_600,
        'minute' => 60,
    ];

    foreach ($units as $name => $seconds) {
        if ($gap >= $seconds) {
            $count = (int) round($gap / $seconds);
            $word = $count . ' ' . $name . ($count === 1 ? '' : 's');

            return $past ? $word . ' ago' : 'in ' . $word;
        }
    }

    return 'just now';
}

$now = time();
$given = posted('value');
$report = null;
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($given === '') {
        $error = 'Enter a timestamp or a date.';
    } else {
        /* A run of digits is taken as the timestamp itself; anything else is
           handed to the date parser and read as a date string. The false
           check is explicit, so a real timestamp of zero is not thrown away
           as though the parse had failed. */
        $stamp = null;

        if (preg_match('/^-?\d+$/', $given)) {
            $stamp = (int) $given;
        } else {
            $parsed = strtotime($given);

            if ($parsed !== false) {
                $stamp = $parsed;
            }
        }

        if ($stamp === null) {
            $error = 'That is neither a timestamp nor a date I can read — try 2026-10-15 or 15 Oct 2026.';
        } else {
            $report = [
                'Unix timestamp' => (string) $stamp,
                'ISO 8601 (UTC)' => gmdate('Y-m-d\TH:i:s\Z', $stamp),
                'RFC 2822'       => gmdate(DATE_RFC2822, $stamp),
                'Date (UTC)'     => gmdate('D, j M Y', $stamp),
                'Time (UTC)'     => gmdate('H:i:s', $stamp),
                'From now'       => span($stamp, $now),
            ];
        }
    }
}

require __DIR__ . '/../app/parts/tool.php';
?>

<div class="card bg-body rounded-4 border shadow-sm">
    <div class="card-body d-flex flex-wrap align-items-center justify-content-between gap-3">
        <div>
            <div class="text-body-secondary small text-uppercase">Right now</div>
            <div class="font-monospace fs-4"><?= $now ?></div>
        </div>
        <?= clip((string) $now, 'Copy') ?>
    </div>
</div>

<form method="post" class="card bg-body rounded-4 border shadow-sm p-4 mt-4">
    <label class="form-label fw-semibold" for="value">Timestamp or date</label>
    <input class="form-control" id="value" name="value" value="<?= e($given) ?>"
           placeholder="1760000000 or 2026-10-15 14:30" autofocus>
    <p class="form-text mb-0">A plain number is read as a Unix timestamp; anything else is read as a date.</p>

    <button class="btn btn-primary mt-4" type="submit">Convert</button>
</form>

<?php if ($error !== ''): ?>
    <?= notice($error) ?>
<?php elseif ($report !== null): ?>
    <?= values($report) ?>
<?php endif; ?>

<?php require __DIR__ . '/../app/parts/explain.php';
