<?php
declare(strict_types=1);

require __DIR__ . '/../app/init.php';

$version = translation();

$title = 'Bible Facts';
$description = 'How the numbers fall in the ' . BIBLE_VERSIONS[$version] . ' — books, chapters, '
    . 'verses, the two testaments, and the words that come up most.';

/* Everything here is counted by the database for the translation being read,
   so the figures follow the reader rather than being written down once. */
$all = measure($version, 1, 66);
$old = measure($version, 1, 39);
$new = measure($version, 40, 66);
$ends = extremes($version);

/* found_total() is the count the search page itself makes, so the two can
   never disagree about how often a word appears. */
$mentions = [];

foreach (['God', 'Lord', 'Jesus', 'love', 'faith', 'grace'] as $word) {
    $mentions[$word] = found_total($version, $word);
}

$verses = max(1, (int) $all['verses']);

/* Where a verse lives, as a link. */
$open = static fn(array $row): string =>
    address($row) . '/' . (int) $row['chapter'] . '/' . (int) $row['verse'];

require __DIR__ . '/../app/parts/header.php';
require __DIR__ . '/../app/parts/bible.php';
?>

<section class="container py-5">
    <div class="row justify-content-center text-center mb-5">
        <div class="col-lg-8">
            <h1 class="display-6 fw-semibold mb-3">Bible Facts</h1>
            <p class="text-body-secondary mb-0">
                What is actually in the <?= e(BIBLE_VERSIONS[$version]) ?>, counted rather
                than remembered.
            </p>
        </div>
    </div>

    <div class="row row-cols-1 row-cols-md-3 g-3 mb-5">
        <?php
        $totals = [
            'Books'    => (int) $all['books'],
            'Chapters' => (int) $all['chapters'],
            'Verses'   => (int) $all['verses'],
        ];

        foreach ($totals as $label => $value):
            ?>
            <div class="col">
                <div class="card h-100 bg-body rounded-4 border shadow-sm text-center">
                    <div class="card-body">
                        <p class="display-6 fw-semibold mb-0"><?= e(number_format($value)) ?></p>
                        <p class="text-body-secondary text-uppercase small mb-0"><?= e($label) ?></p>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <h2 class="h6 text-uppercase text-body-secondary border-bottom pb-2 mb-3">The two testaments</h2>

    <div class="card bg-body rounded-4 border shadow-sm mb-5">
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead>
                    <tr>
                        <th scope="col">Testament</th>
                        <th scope="col" class="text-end">Books</th>
                        <th scope="col" class="text-end">Chapters</th>
                        <th scope="col" class="text-end">Verses</th>
                        <th scope="col" class="text-end">Share</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $parts = ['Old Testament' => $old, 'New Testament' => $new];

                    foreach ($parts as $label => $part):
                        $share = round((int) $part['verses'] / $verses * 100, 1);
                        ?>
                        <tr>
                            <th scope="row" class="fw-semibold"><?= e($label) ?></th>
                            <td class="text-end"><?= e((string) $part['books']) ?></td>
                            <td class="text-end"><?= e((string) $part['chapters']) ?></td>
                            <td class="text-end"><?= e(number_format((int) $part['verses'])) ?></td>
                            <td class="text-end text-body-secondary"><?= e((string) $share) ?>%</td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <h2 class="h6 text-uppercase text-body-secondary border-bottom pb-2 mb-3">How often a word comes up</h2>

    <div class="card bg-body rounded-4 border shadow-sm mb-5">
        <div class="card-body">
            <?php foreach ($mentions as $word => $count): ?>
                <?php $share = (int) round($count / $verses * 100); ?>
                <div class="mb-3">
                    <div class="d-flex justify-content-between small mb-1">
                        <span class="fw-semibold"><?= e($word) ?></span>
                        <span class="text-body-secondary">
                            <?= e(number_format($count)) ?> verses &middot; <?= e((string) $share) ?>%
                        </span>
                    </div>
                    <div class="progress" role="progressbar" aria-label="Verses mentioning <?= e($word) ?>"
                         aria-valuenow="<?= e((string) $share) ?>" aria-valuemin="0" aria-valuemax="100">
                        <div class="progress-bar" style="width:<?= e((string) max(1, $share)) ?>%"></div>
                    </div>
                </div>
            <?php endforeach; ?>

            <p class="text-body-secondary small mb-0">
                A verse is counted once for each word it contains, so the shares add
                up to more than 100% between them.
            </p>
        </div>
    </div>

    <h2 class="h6 text-uppercase text-body-secondary pb-2 mb-3 border-bottom">The shortest and the longest verse</h2>

    <div class="row row-cols-1 row-cols-md-2 g-3">
        <?php foreach ($ends as $label => $row): ?>
            <?php if ($row === null): ?>
                <?php continue; ?>
            <?php endif; ?>
            <div class="col">
                <div class="card h-100 bg-body rounded-4 border shadow-sm">
                    <div class="card-body">
                        <p class="text-body-secondary text-uppercase small mb-2"><?= e($label) ?></p>
                        <p class="mb-3"><?= e($row['text']) ?></p>
                        <a class="small text-decoration-none" href="<?= e($open($row)) ?>">
                            <?= e($row['book'] . ' ' . $row['chapter'] . ':' . $row['verse']) ?>
                            <?= icon('fa-solid fa-arrow-right ms-1') ?>
                        </a>
                    </div>
                    <div class="card-footer bg-transparent text-body-secondary small">
                        <?= e(number_format((int) $row['size'])) ?> characters
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</section>

<?php require __DIR__ . '/../app/parts/footer.php';
