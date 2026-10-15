<?php
/* The closing column and the page's end. Pairs with header.php. */
$legal = [
    'disclaimer' => 'Disclaimer',
    'privacy'    => 'Privacy Policy',
    'terms'      => 'Terms &amp; Conditions',
];
?>
</main>

<footer class="site-footer mt-5">
    <div class="container py-5">
        <div class="row g-4 g-lg-5">
            <div class="col-lg-6">
                <a class="navbar-brand d-inline-flex align-items-center gap-2 mb-3" href="<?= url() ?>">
                    <span class="brand-mark"><?= icon('fa-solid fa-layer-group') ?></span>
                    <span class="brand-name"><?= e(SITE_NAME) ?></span>
                </a>
                <p class="text-body-secondary mb-2" style="max-width: 30rem"><?= e(SITE_TAGLINE) ?></p>
                <p class="text-body-secondary small mb-0">
                    A project by <a href="<?= url('about') ?>">Hiswill Kato</a>.
                    Free to use, since <?= e((string) SITE_YEAR) ?>.
                </p>
            </div>

            <div class="col-6 col-lg-3">
                <h2 class="eyebrow mb-3">Explore</h2>
                <ul class="list-unstyled d-grid gap-2 mb-0">
                    <li><a class="link-body-emphasis text-decoration-none" href="<?= url() ?>">Home</a></li>
                    <li><a class="link-body-emphasis text-decoration-none" href="<?= url('bible') ?>">Bible</a></li>
                    <li><a class="link-body-emphasis text-decoration-none" href="<?= url('tools') ?>">Tools</a></li>
                    <li><a class="link-body-emphasis text-decoration-none" href="<?= url('about') ?>">About</a></li>
                    <li><a class="link-body-emphasis text-decoration-none" href="<?= url('contact') ?>">Contact</a></li>
                </ul>
            </div>

            <div class="col-6 col-lg-3">
                <h2 class="eyebrow mb-3">Legal</h2>
                <ul class="list-unstyled d-grid gap-2 mb-0">
                    <?php /* Walked by key, so the loop leaves nothing of its own
                         behind in a page that has already been rendered. */ ?>
                    <?php foreach (array_keys($legal) as $key): ?>
                    <li><a class="link-body-emphasis text-decoration-none" href="<?= url($key) ?>"><?= $legal[$key] ?></a></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>

        <hr class="my-4">

        <p class="text-body-secondary small mb-0">
            &copy; <?= e((string) SITE_YEAR) ?> &ndash; <?= e(date('Y')) ?> <?= e(SITE_NAME) ?>.
            Built by Hiswill Kato.
        </p>
    </div>
</footer>

<button id="top" class="btn btn-primary rounded-circle" hidden aria-label="Back to top">
    <?= icon('fa-solid fa-arrow-up') ?>
</button>

<script src="<?= asset('js/bootstrap.bundle.min.js') ?>" defer></script>
<script src="<?= asset('js/theme.js') ?>" defer></script>
<script src="<?= asset('js/copy.js') ?>" defer></script>
</body>
</html>
