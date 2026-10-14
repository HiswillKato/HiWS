<?php
declare(strict_types=1);

require __DIR__ . '/../app/init.php';

$title = 'Web Tools';
$description = 'Free online tools for developers and everyone else — DNS, IP and WHOIS lookups, SSL checks, hashes, colours, currencies and more.';

$tools = tools();

/* One pass groups them. tools() already returns the rows in category order, so
   the groups come out in that order too and the index needs no second query. */
$groups = [];

foreach ($tools as $row) {
    $groups[$row['category']][] = $row;
}

require __DIR__ . '/../app/parts/header.php';
?>

<section class="container py-5">
    <div class="row justify-content-center text-center mb-5">
        <div class="col-lg-8">
            <h1 class="display-6 fw-semibold mb-3">Online Web Tools</h1>
            <p class="lead mb-0">
                <?= count($tools) ?> free tools for the everyday jobs of building and running a
                website. No account, no sign-up — open one and use it.
            </p>
        </div>
    </div>

    <?php foreach ($groups as $category => $rows): ?>
        <div class="mb-5">
            <h2 class="h6 text-uppercase text-body-secondary border-bottom pb-2 mb-3">
                <?= e($category) ?>
            </h2>

            <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-3">
                <?php foreach ($rows as $row): ?>
                    <div class="col">
                        <a class="card h-100 bg-body rounded-4 border shadow-sm text-decoration-none text-reset"
                           href="<?= url('tools/' . $row['url']) ?>">
                            <div class="card-body d-flex gap-3">
                                <span class="badge bg-primary-subtle text-primary-emphasis rounded-3 p-2 align-self-start fs-5">
                                    <?= mark($row['icon']) ?>
                                </span>
                                <span>
                                    <span class="h6 d-block mb-1"><?= e($row['title']) ?></span>
                                    <span class="text-body-secondary small d-block"><?= e($row['description']) ?></span>
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
