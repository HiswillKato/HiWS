<?php
/* The top of a tool page. The page sets $tool — a row from tool() — and this
   file does the rest: the page's title, description and keywords, the
   masthead, and the tool's own heading. explain.php pairs with it and closes
   what is opened here. */

if ($tool === null) {
    /* A row that is not there: the address is wrong, or the tool has been
       retired. Answer honestly rather than fatalling on an empty array. */
    http_response_code(404);
    $title = 'Tool not found';

    require __DIR__ . '/header.php';
    ?>
    <section class="container py-5">
        <div class="row justify-content-center">
            <div class="col-lg-7 text-center">
                <p class="display-6 text-body-secondary mb-3"><?= icon('fa-solid fa-compass') ?></p>
                <h1 class="h3 mb-3">That tool is not here</h1>
                <p class="text-body-secondary">
                    It may have been renamed or retired. The full list is on the
                    <a href="<?= url('tools') ?>">tools page</a>.
                </p>
                <a class="btn btn-primary" href="<?= url('tools') ?>">
                    All tools <?= icon('fa-solid fa-arrow-right ms-1') ?>
                </a>
            </div>
        </div>
    </section>
    <?php
    require __DIR__ . '/footer.php';
    exit;
}

$title = $tool['title'];
$description = $tool['description'];
$keywords = $tool['keywords'] !== '' ? $tool['keywords'] : null;

require __DIR__ . '/header.php';
?>
<section class="container py-5">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?= url('tools') ?>">Tools</a></li>
            <li class="breadcrumb-item active" aria-current="page"><?= e($tool['title']) ?></li>
        </ol>
    </nav>

    <div class="row justify-content-center">
        <div class="col-lg-9">
            <div class="d-flex align-items-center gap-3 mb-4">
                <span class="badge bg-primary-subtle text-primary-emphasis rounded-3 p-3 fs-4">
                    <?= mark($tool['icon']) ?>
                </span>
                <div>
                    <h1 class="h3 mb-1"><?= e($tool['title']) ?></h1>
                    <p class="text-body-secondary mb-0"><?= e($tool['description']) ?></p>
                </div>
            </div>
