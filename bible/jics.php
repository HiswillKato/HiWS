<?php
declare(strict_types=1);

require __DIR__ . '/../app/init.php';

$version = translation();

$title = 'Jesus is coming soon';
$description = 'The promise the Bible closes on — Revelation 22:12–15, in the '
    . BIBLE_VERSIONS[$version] . '.';

/* The last words of the Bible, taken from the same chapter reader the rest of
   /bible uses, so this page cannot drift from the text. */
$book = book($version, 'revelation');

$promise = $book === null ? [] : array_values(array_filter(
    chapter($version, (int) $book['number'], 22),
    static fn(array $line): bool => (int) $line['verse'] >= 12 && (int) $line['verse'] <= 15
));

require __DIR__ . '/../app/parts/header.php';
require __DIR__ . '/../app/parts/bible.php';
?>

<section class="container py-5">
    <div class="row justify-content-center text-center mb-5">
        <div class="col-lg-8">
            <p class="display-5 text-primary mb-3"><?= icon('fa-solid fa-crown') ?></p>
            <h1 class="display-6 fw-semibold mb-3">Jesus is coming soon</h1>
            <p class="lead mb-0">
                The Bible ends on a promise, not a warning. These are the last four
                verses before it closes.
            </p>
        </div>
    </div>

    <div class="row justify-content-center">
        <div class="col-lg-8">
            <?php if ($promise === []): ?>
                <div class="alert alert-warning mb-0">
                    The text of Revelation is not in this translation. Try the other one.
                </div>
            <?php else: ?>
                <div class="card bg-body rounded-4 border shadow-sm">
                    <div class="card-body p-4 p-md-5">
                        <?php foreach ($promise as $line): ?>
                            <p class="mb-4 lead<?= (int) $line['verse'] === 12 ? ' fw-semibold' : '' ?>">
                                <sup class="text-primary fw-semibold me-1"><?= e((string) $line['verse']) ?></sup><?= e($line['text']) ?>
                            </p>
                        <?php endforeach; ?>
                    </div>

                    <div class="card-footer bg-transparent d-flex flex-wrap align-items-center justify-content-between gap-2">
                        <span class="text-body-secondary small">
                            Revelation 22:12&ndash;15, <?= e(BIBLE_VERSIONS[$version]) ?>
                        </span>

                        <?php if ($book !== null): ?>
                            <a class="btn btn-primary btn-sm"
                               href="<?= e(address($book) . '/22/12') ?>">
                                Read the whole chapter <?= icon('fa-solid fa-arrow-right ms-1') ?>
                            </a>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="text-center mt-4">
                    <a class="btn btn-outline-secondary" href="<?= e(url('bible/search') . '?q=' . rawurlencode('come quickly')) ?>">
                        <?= icon('fa-solid fa-magnifying-glass me-1') ?>
                        Where else does the Bible say so?
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>

<?php require __DIR__ . '/../app/parts/footer.php';
