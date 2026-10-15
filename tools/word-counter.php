<?php
declare(strict_types=1);

require __DIR__ . '/../app/init.php';

$tool = tool('word-counter');

$given = posted('text');
$report = null;
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (trim($given) === '') {
        $error = 'Paste some text to count.';
    } else {
        $plain = strip_tags($given);

        /* A word is a run of letters, digits, apostrophes or hyphens — which
           is nearer what a person means than splitting on spaces alone. */
        $words = preg_split('/[\p{L}\p{N}\'’-]+/u', $plain, -1, PREG_SPLIT_NO_EMPTY) ?: [];
        $sentences = preg_split('/[.!?]+/u', trim($plain), -1, PREG_SPLIT_NO_EMPTY) ?: [];
        $paragraphs = array_filter(preg_split('/\R+/', trim($plain)) ?: [], static fn(string $p): bool => trim($p) !== '');

        $report = [
            'Words'                     => number_format(count($words)),
            'Characters'                => number_format(mb_strlen($given)),
            'Characters without spaces' => number_format(mb_strlen((string) preg_replace('/\s+/u', '', $given))),
            'Sentences'                 => number_format(count($sentences)),
            'Paragraphs'                => number_format(count($paragraphs)),
            'Lines'                     => number_format(substr_count($given, "\n") + 1),
            'Average words a sentence'  => count($sentences) > 0 ? number_format(count($words) / count($sentences), 1) : '',
        ];
    }
}

require __DIR__ . '/../app/parts/tool.php';
?>

<form method="post" class="card bg-body rounded-4 border shadow-sm p-4">
    <label class="form-label fw-semibold" for="text">Text</label>
    <textarea class="form-control" id="text" name="text" rows="9" autofocus><?= e($given) ?></textarea>

    <button class="btn btn-primary mt-4" type="submit">Count</button>
</form>

<?php if ($error !== ''): ?>
    <?= notice($error) ?>
<?php elseif ($report !== null): ?>
    <?= values($report) ?>
<?php endif; ?>

<?php require __DIR__ . '/../app/parts/explain.php';
