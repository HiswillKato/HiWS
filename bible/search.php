<?php
declare(strict_types=1);

require __DIR__ . '/../app/init.php';

$version = translation();

$term = trim((string) ($_GET['q'] ?? ''));
$page = max(1, (int) ($_GET['page'] ?? 1));

/* Show the found word as found. The verse is escaped first and the term is
   written in afterwards, so what a visitor typed is never markup — only the
   <mark> this adds is. */
function highlight(string $text, string $term): string
{
    $safe = e($text);

    return (string) preg_replace('/' . preg_quote(e($term), '/') . '/i', '<mark>$0</mark>', $safe);
}

/* A reference is a way there, not a word to look for, so it is followed before
   any search runs. The pattern takes "psalm 23", "john 3:16", "1 cor 13" and
   "song of solomon 1" alike; anything else falls through to the search. */
if ($term !== '' && preg_match('/^([1-3]?\s*[a-z]+(?:\s+(?:of\s+)?[a-z]+)*)\s+(\d+)(?:\s*:\s*(\d+))?$/i', $term, $found)) {
    $book = book($version, trim($found[1]));

    if ($book !== null) {
        $to = 'bible/' . slug($book['book']) . '/' . (int) $found[2];

        if (!empty($found[3])) {
            $to .= '/' . (int) $found[3];
        }

        header('Location: ' . url($to), true, 302);
        exit;
    }
}

$total = 0;
$pages = 1;
$lines = [];

if ($term !== '') {
    $total = found_total($version, $term);
    $pages = max(1, (int) ceil($total / PER_PAGE));
    $page = min($page, $pages);
    $lines = found($version, $term, PER_PAGE, ($page - 1) * PER_PAGE);
}

$title = $term === '' ? 'Search the Bible' : 'Search for “' . $term . '”';
$description = 'Search every verse of the ' . BIBLE_VERSIONS[$version] . ' for a word or a phrase.';

/* The pager keeps the term and only moves the page number. */
$at = static fn(int $to): string => url('bible/search') . '?' . http_build_query(['q' => $term, 'page' => $to]);

require __DIR__ . '/../app/parts/header.php';
require __DIR__ . '/../app/parts/bible.php';
?>

<section class="container py-5">
    <div class="row justify-content-center text-center mb-4">
        <div class="col-lg-8">
            <h1 class="display-6 fw-semibold mb-3">Search the Bible</h1>
            <p class="text-body-secondary mb-0">
                A word, a phrase, or a reference such as <em>john 3:16</em> —
                searching the <?= e(BIBLE_VERSIONS[$version]) ?>.
            </p>
        </div>
    </div>

    <div class="row justify-content-center">
        <div class="col-lg-8">
            <form class="card bg-body rounded-4 border shadow-sm p-4" method="get"
                  action="<?= url('bible/search') ?>">
                <label class="form-label fw-semibold" for="q">Find</label>
                <div class="input-group">
                    <input class="form-control" id="q" name="q" value="<?= e($term) ?>"
                           placeholder="love  ·  john 3:16  ·  psalm 23" required autofocus>
                    <button class="btn btn-primary" type="submit">Search</button>
                </div>
            </form>

            <?php if ($term !== ''): ?>
                <p class="text-body-secondary mt-4 mb-3">
                    <?php if ($total === 0): ?>
                        Nothing matched <strong><?= e($term) ?></strong>. Try a shorter word, or
                        one of its other forms.
                    <?php else: ?>
                        <?= e(number_format($total)) ?>
                        <?= $total === 1 ? 'verse contains' : 'verses contain' ?>
                        <strong><?= e($term) ?></strong>
                        <?php if ($pages > 1): ?>
                            &middot; page <?= e((string) $page) ?> of <?= e((string) $pages) ?>
                        <?php endif; ?>
                    <?php endif; ?>
                </p>

                <?php if ($lines !== []): ?>
                    <div class="card bg-body rounded-4 border shadow-sm">
                        <div class="list-group list-group-flush">
                            <?php foreach ($lines as $line): ?>
                                <a class="list-group-item list-group-item-action bg-transparent"
                                   href="<?= e(address($line) . '/' . (int) $line['chapter'] . '/' . (int) $line['verse']) ?>">
                                    <span class="d-block small fw-semibold text-primary mb-1">
                                        <?= e($line['book']) ?> <?= e((string) $line['chapter']) ?>:<?= e((string) $line['verse']) ?>
                                    </span>
                                    <span class="d-block"><?= highlight($line['text'], $term) ?></span>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <?php if ($pages > 1): ?>
                        <nav class="mt-4" aria-label="Search results">
                            <ul class="pagination justify-content-center">
                                <li class="page-item<?= $page <= 1 ? ' disabled' : '' ?>">
                                    <a class="page-link" href="<?= e($at($page - 1)) ?>">Previous</a>
                                </li>
                                <li class="page-item<?= $page >= $pages ? ' disabled' : '' ?>">
                                    <a class="page-link" href="<?= e($at($page + 1)) ?>">Next</a>
                                </li>
                            </ul>
                        </nav>
                    <?php endif; ?>

                    <p class="text-body-secondary small text-center mb-0">
                        Results are whole verses, in the order they appear in the Bible.
                    </p>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</section>

<?php require __DIR__ . '/../app/parts/footer.php';
