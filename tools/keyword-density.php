<?php
declare(strict_types=1);

require __DIR__ . '/../app/init.php';

$tool = tool('keyword-density');

/* The words that carry no meaning on their own. Leaving them out is what makes
   the rest of the list show what a page is about rather than how it is glued
   together. */
const STOPWORDS = [
    'a', 'an', 'and', 'are', 'as', 'at', 'be', 'been', 'but', 'by', 'can', 'do',
    'does', 'for', 'from', 'had', 'has', 'have', 'he', 'her', 'his', 'how', 'i',
    'if', 'in', 'into', 'is', 'it', 'its', 'just', 'me', 'more', 'most', 'my',
    'no', 'not', 'of', 'on', 'or', 'our', 'out', 'she', 'so', 'such', 'than',
    'that', 'the', 'their', 'them', 'then', 'there', 'these', 'they', 'this',
    'to', 'up', 'was', 'we', 'were', 'what', 'when', 'which', 'who', 'will',
    'with', 'would', 'you', 'your',
];

$given = posted('text');
$ignoreCommon = isset($_POST['ignore']);
$report = null;
$top = [];
$total = 0;
$error = '';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $ignoreCommon = true;
} elseif (trim($given) === '') {
    $error = 'Paste some text to analyse.';
} else {
    $words = preg_split('/[^\p{L}\p{N}\']+/u', mb_strtolower($given), -1, PREG_SPLIT_NO_EMPTY) ?: [];
    $words = array_filter($words, static fn(string $word): bool => mb_strlen($word) > 1);

    $total = count($words);
    $counts = array_count_values($words);

    if ($ignoreCommon) {
        $counts = array_diff_key($counts, array_flip(STOPWORDS));
    }

    arsort($counts);
    $top = array_slice($counts, 0, 25, true);

    $report = [
        'Words'          => number_format($total),
        'Unique words'   => number_format(count(array_unique($words))),
        'Distinct terms' => number_format(count($counts)),
        'Top word'       => $top === [] ? '' : (string) array_key_first($top),
    ];
}

require __DIR__ . '/../app/parts/tool.php';
?>

<form method="post" class="card bg-body rounded-4 border shadow-sm p-4">
    <label class="form-label fw-semibold" for="text">Text</label>
    <textarea class="form-control" id="text" name="text" rows="8" autofocus><?= e($given) ?></textarea>

    <div class="form-check mt-3">
        <input class="form-check-input" type="checkbox" id="ignore" name="ignore" <?= $ignoreCommon ? 'checked' : '' ?>>
        <label class="form-check-label" for="ignore">Ignore common words</label>
    </div>

    <button class="btn btn-primary mt-4" type="submit">Analyse</button>
</form>

<?php if ($error !== ''): ?>
    <?= notice($error) ?>
<?php elseif ($report !== null): ?>
    <?= values($report) ?>

    <?php if ($top !== []): ?>
        <div class="card bg-body rounded-4 border shadow-sm mt-4">
            <div class="table-responsive">
                <table class="table table-sm align-middle mb-0">
                    <thead>
                        <tr>
                            <th scope="col">Word</th>
                            <th scope="col" class="text-end">Count</th>
                            <th scope="col" style="min-width: 8rem">Density</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($top as $word => $count): ?>
                            <?php $density = $total === 0 ? 0.0 : $count / $total * 100; ?>
                            <tr>
                                <th scope="row" class="fw-normal"><?= e((string) $word) ?></th>
                                <td class="text-end"><?= number_format($count) ?></td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="progress flex-grow-1" style="height: .4rem">
                                            <div class="progress-bar" style="width: <?= min(100, $density * 4) ?>%"></div>
                                        </div>
                                        <span class="text-body-secondary small" style="min-width: 3.5rem"><?= number_format($density, 2) ?>%</span>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php endif; ?>
<?php endif; ?>

<?php require __DIR__ . '/../app/parts/explain.php';
