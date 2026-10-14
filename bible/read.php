<?php
declare(strict_types=1);

require __DIR__ . '/../app/init.php';

$version = translation();

$asked = trim((string) ($_GET['book'] ?? ''));
$wanted = (int) ($_GET['chapter'] ?? 0);
$verse = (int) ($_GET['verse'] ?? 0);

/* One of the two ways to be wrong is a book this translation does not carry.
   The other is a chapter outside its book, which is checked below. Both answer
   the truth — 404 — rather than an empty page. */
function lost(string $heading, string $line): never
{
    http_response_code(404);

    $title = $heading;

    require __DIR__ . '/../app/parts/header.php';
    require __DIR__ . '/../app/parts/bible.php';
    ?>
    <section class="container py-5">
        <div class="row justify-content-center">
            <div class="col-lg-7 text-center">
                <p class="display-6 text-body-secondary mb-3"><?= icon('fa-solid fa-book-open') ?></p>
                <h1 class="h3 mb-3"><?= e($heading) ?></h1>
                <p class="text-body-secondary"><?= e($line) ?></p>
                <a class="btn btn-primary" href="<?= url('bible') ?>">
                    All the books <?= icon('fa-solid fa-arrow-right ms-1') ?>
                </a>
            </div>
        </div>
    </section>
    <?php
    require __DIR__ . '/../app/parts/footer.php';
    exit;
}

$book = $asked === '' ? null : book($version, $asked);

if ($book === null) {
    lost('That book is not in this translation', 'Check the spelling, or pick one from the list.');
}

$number = (int) $book['number'];
$name = (string) $book['book'];
$chapters = chapters($version, $number);

if ($wanted > $chapters) {
    lost($name . ' has no chapter ' . $wanted, $name . ' runs to ' . $chapters . ' chapters.');
}

/* A whole book, or one chapter of it. The verse is optional and only decides
   which line is lit up. */
$lines = $wanted < 1 ? [] : chapter($version, $number, $wanted);

$title = $wanted < 1 ? $name : $name . ' ' . $wanted;
$description = $wanted < 1
    ? 'All ' . $chapters . ' chapters of ' . $name . ' in the ' . BIBLE_VERSIONS[$version] . '.'
    : 'Read ' . $name . ' chapter ' . $wanted . ' in the ' . BIBLE_VERSIONS[$version] . '.';

if ($verse > 0) {
    $title .= ':' . $verse;
}

require __DIR__ . '/../app/parts/header.php';
require __DIR__ . '/../app/parts/bible.php';
?>

<section class="container py-5">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?= url('bible') ?>">Bible</a></li>
            <?php if ($wanted < 1): ?>
                <li class="breadcrumb-item active" aria-current="page"><?= e($name) ?></li>
            <?php else: ?>
                <li class="breadcrumb-item"><a href="<?= e(address($book)) ?>"><?= e($name) ?></a></li>
                <li class="breadcrumb-item active" aria-current="page">Chapter <?= e((string) $wanted) ?></li>
            <?php endif; ?>
        </ol>
    </nav>

    <?php if ($wanted < 1): ?>
        <?php /* The book, as a wall of its chapters. */ ?>
        <h1 class="h3 mb-1"><?= e($name) ?></h1>
        <p class="text-body-secondary mb-4">
            <?= e((string) $chapters) ?> chapters in the <?= e(BIBLE_VERSIONS[$version]) ?>
        </p>

        <div class="row row-cols-4 row-cols-sm-6 g-2">
            <?php for ($each = 1; $each <= $chapters; $each++): ?>
                <div class="col">
                    <a class="btn btn-outline-secondary w-100"
                       href="<?= e(address($book) . '/' . $each) ?>"><?= e((string) $each) ?></a>
                </div>
            <?php endfor; ?>
        </div>
    <?php else: ?>
        <div class="d-flex align-items-center justify-content-between gap-2 mb-4">
            <?php if ($wanted > 1): ?>
                <a class="btn btn-outline-secondary" href="<?= e(address($book) . '/' . ($wanted - 1)) ?>">
                    <?= icon('fa-solid fa-chevron-left') ?><span class="visually-hidden">Chapter <?= e((string) ($wanted - 1)) ?></span>
                </a>
            <?php else: ?>
                <span class="btn btn-outline-secondary disabled" aria-disabled="true"><?= icon('fa-solid fa-chevron-left') ?></span>
            <?php endif; ?>

            <h1 class="h3 mb-0 text-center"><?= e($name) ?> <?= e((string) $wanted) ?></h1>

            <?php if ($wanted < $chapters): ?>
                <a class="btn btn-outline-secondary" href="<?= e(address($book) . '/' . ($wanted + 1)) ?>">
                    <?= icon('fa-solid fa-chevron-right') ?><span class="visually-hidden">Chapter <?= e((string) ($wanted + 1)) ?></span>
                </a>
            <?php else: ?>
                <span class="btn btn-outline-secondary disabled" aria-disabled="true"><?= icon('fa-solid fa-chevron-right') ?></span>
            <?php endif; ?>
        </div>

        <div class="card bg-body rounded-4 border shadow-sm">
            <div class="card-body p-4">
                <?php foreach ($lines as $line): ?>
                    <?php $lit = (int) $line['verse'] === $verse; ?>
                    <p class="mb-3<?= $lit ? ' bg-warning-subtle rounded-3 p-2' : '' ?>"
                       id="v<?= e((string) $line['verse']) ?>"<?= $lit ? ' data-verse' : '' ?>>
                        <sup class="text-body-secondary fw-semibold me-1"><?= e((string) $line['verse']) ?></sup><?= e($line['text']) ?>
                    </p>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mt-3">
            <a class="link-body-emphasis text-decoration-none small" href="<?= e(address($book)) ?>">
                <?= icon('fa-solid fa-list me-1') ?>All <?= e((string) $chapters) ?> chapters of <?= e($name) ?>
            </a>
            <span class="text-body-secondary small">
                <?= e(BIBLE_VERSIONS[$version]) ?>
            </span>
        </div>

        <?php if ($verse > 0): ?>
            <script src="<?= asset('js/verse.js') ?>" defer></script>
        <?php endif; ?>
    <?php endif; ?>
</section>

<?php require __DIR__ . '/../app/parts/footer.php';
