<?php
declare(strict_types=1);

require __DIR__ . '/app/init.php';

$title = 'Home';

require __DIR__ . '/app/parts/header.php';
?>

<section class="container py-5">
    <div class="p-5 mb-5 bg-body rounded-4 border shadow-sm">
        <div class="row align-items-center g-4">
            <div class="col-lg-8">
                <h1 class="display-5 fw-semibold mb-3">Welcome to <?= e(SITE_NAME) ?>!</h1>
                <p class="lead mb-3">
                    <?= e(SITE_FULL_NAME) ?> crafts web projects that anyone can use, free of charge.
                    They are built for people who are new to the web as much as for developers — the
                    web moves fast, and finding the right tool should not be the hard part.
                </p>
                <a class="btn btn-primary btn-lg" href="<?= url('about') ?>">
                    Learn more <?= icon('fa-solid fa-arrow-right ms-1') ?>
                </a>
            </div>
            <div class="col-lg-4 text-lg-end d-none d-lg-block">
                <span class="display-1 text-primary opacity-25"><?= icon('fa-solid fa-layer-group') ?></span>
            </div>
        </div>
    </div>

    <div class="d-flex align-items-center justify-content-between mb-3">
        <h2 class="h3 mb-0"><?= e(SITE_NAME) ?> Projects</h2>
        <a class="link-primary text-decoration-none" href="<?= url('contact') ?>">Suggest one <?= icon('fa-solid fa-arrow-right') ?></a>
    </div>

    <?php $projects = projects(); ?>

    <?php if ($projects === []): ?>
        <div class="alert alert-secondary mb-0" role="alert">
            Nothing is listed yet. The first projects go up shortly.
        </div>
    <?php else: ?>
        <div class="row g-4">
            <?php foreach ($projects as $project): ?>
            <div class="col-md-6 col-lg-4">
                <div class="card h-100 shadow-sm">
                    <div class="card-body d-flex flex-column">
                        <span class="badge bg-primary-subtle text-primary-emphasis rounded-3 p-3 mb-3 align-self-start fs-5">
                            <?= icon('fa-solid ' . ($project['icon'] !== '' ? $project['icon'] : 'fa-cube')) ?>
                        </span>
                        <h3 class="h5 card-title"><?= e($project['name']) ?></h3>
                        <p class="card-text text-body-secondary"><?= e($project['description']) ?></p>
                        <a class="btn btn-outline-primary mt-auto align-self-start" href="<?= url($project['url']) ?>">
                            Visit <?= icon('fa-solid fa-arrow-right ms-1') ?>
                        </a>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>

<?php require __DIR__ . '/app/parts/footer.php'; ?>
