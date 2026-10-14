<?php
declare(strict_types=1);

require __DIR__ . '/../app/init.php';

$version = translation();

$title = 'Read the Bible';
$description = 'Read the whole Bible online, free, in the King James Version or the '
    . 'World English Bible — book by book, chapter by chapter, with search.';

/* The canon, in reading order, in the translation being read. */
$books = books($version);

/* Everything before Malachi is the Old Testament; numbers are canonical, so
   the split is 39, not something counted here. */
$testaments = [
    'The Old Testament' => array_filter($books, static fn(array $row): bool => $row['number'] <= 39),
    'The New Testament' => array_filter($books, static fn(array $row): bool => $row['number'] > 39),
];

require __DIR__ . '/../app/parts/header.php';
require __DIR__ . '/../app/parts/bible.php';
?>

<section class="container py-5">
    <div class="row justify-content-center text-center mb-5">
        <div class="col-lg-8">
            <h1 class="display-6 fw-semibold mb-3">Read the Bible</h1>
            <p class="lead mb-0">
                <?= e(number_format(array_sum(array_column($books, 'verses')))) ?> verses,
                <?= e(number_format(array_sum(array_column($books, 'chapters')))) ?> chapters,
                <?= e((string) count($books)) ?> books —
                in the <strong><?= e(BIBLE_VERSIONS[$version]) ?></strong>.
            </p>
        </div>
    </div>

    <?php foreach ($testaments as $label => $rows): ?>
        <div class="mb-5">
            <h2 class="h6 text-uppercase text-body-secondary border-bottom pb-2 mb-3"><?= e($label) ?></h2>

            <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-4 g-2">
                <?php foreach ($rows as $row): ?>
                    <div class="col">
                        <a class="card h-100 bg-body rounded-4 border shadow-sm text-decoration-none text-reset"
                           href="<?= e(address($row)) ?>">
                            <div class="card-body py-3">
                                <span class="h6 d-block mb-1"><?= e($row['book']) ?></span>
                                <span class="text-body-secondary small">
                                    <?= e((string) $row['chapters']) ?> chapters &middot;
                                    <?= e(number_format((int) $row['verses'])) ?> verses
                                </span>
                            </div>
                        </a>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endforeach; ?>
</section>

<?php require __DIR__ . '/../app/parts/footer.php';
