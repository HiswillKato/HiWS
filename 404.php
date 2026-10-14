<?php
declare(strict_types=1);

require __DIR__ . '/app/init.php';

http_response_code(404);

$title = 'Page not found';

require __DIR__ . '/app/parts/header.php';
?>

<section class="container py-5">
    <div class="row justify-content-center text-center">
        <div class="col-lg-7">
            <p class="display-1 fw-semibold text-primary opacity-25 mb-0">404</p>
            <h1 class="mb-3">That page is not here</h1>
            <p class="lead text-body-secondary mb-4">
                The address you asked for does not exist, or it has moved.
                Nothing is broken — the page simply is not at this address.
            </p>
            <div class="d-flex flex-wrap gap-3 justify-content-center">
                <a class="btn btn-primary" href="<?= url() ?>">
                    <?= icon('fa-solid fa-house me-1') ?> Back to home
                </a>
                <a class="btn btn-outline-secondary" href="<?= url('contact') ?>">
                    <?= icon('fa-solid fa-envelope me-1') ?> Tell me about it
                </a>
            </div>
        </div>
    </div>
</section>

<?php require __DIR__ . '/app/parts/footer.php'; ?>
