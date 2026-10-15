<?php
declare(strict_types=1);

require __DIR__ . '/../app/init.php';

$tool = tool('reading-time');

/* Seconds as minutes and seconds, the way a video length reads. */
function clock(int $seconds): string
{
    $minutes = intdiv($seconds, 60);
    $rest = $seconds % 60;

    if ($minutes === 0) {
        return $rest . ' sec';
    }

    return $minutes . ' min' . ($rest > 0 ? ' ' . $rest . ' sec' : '');
}

$given = posted('text');
$report = null;
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (trim($given) === '') {
        $error = 'Paste some text to time.';
    } else {
        $words = preg_split('/\s+/u', trim(strip_tags($given)), -1, PREG_SPLIT_NO_EMPTY) ?: [];
        $count = count($words);
        $sentences = max(1, (int) preg_match_all('/[.!?]+/u', $given));
        $paragraphs = max(1, count(array_filter(preg_split('/\R{2,}/', trim($given)) ?: [])));

        /* Two hundred words a minute reading silently, a hundred and thirty
           read aloud — round numbers, and close enough to be useful. */
        $report = [
            'Words'        => number_format($count),
            'Characters'   => number_format(mb_strlen($given)),
            'Sentences'    => number_format($sentences),
            'Paragraphs'   => number_format($paragraphs),
            'Reading time' => clock((int) ceil($count / 200 * 60)),
            'Speaking time' => clock((int) ceil($count / 130 * 60)),
        ];
    }
}

require __DIR__ . '/../app/parts/tool.php';
?>

<form method="post" class="card bg-body rounded-4 border shadow-sm p-4">
    <label class="form-label fw-semibold" for="text">Text</label>
    <textarea class="form-control" id="text" name="text" rows="8"
              placeholder="Paste an article, a chapter or a speech." autofocus><?= e($given) ?></textarea>

    <button class="btn btn-primary mt-4" type="submit">Time it</button>
</form>

<?php if ($error !== ''): ?>
    <?= notice($error) ?>
<?php elseif ($report !== null): ?>
    <p class="display-6 mt-4 mb-0 text-center">
        <span class="text-primary"><?= e($report['Reading time']) ?></span>
        <span class="text-body-secondary fs-5 d-block">to read, <?= e($report['Speaking time']) ?> aloud</span>
    </p>

    <?= values($report) ?>
<?php endif; ?>

<?php require __DIR__ . '/../app/parts/explain.php';
