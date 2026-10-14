<?php
/* The closing column and the page's end. Pairs with header.php. */
$legal = [
    'disclaimer' => 'Disclaimer',
    'privacy'    => 'Privacy Policy',
    'terms'      => 'Terms &amp; Conditions',
];
?>
</main>

<footer class="bg-body border-top mt-5 py-5">
    <div class="container">
        <div class="row g-4">
            <div class="col-lg-6">
                <h2 class="h5 d-flex align-items-center gap-2">
                    <span class="brand-mark"><?= icon('fa-solid fa-layer-group') ?></span>
                    <?= e(SITE_FULL_NAME) ?>
                </h2>
                <p class="text-body-secondary mb-2"><?= e(SITE_TAGLINE) ?></p>
                <p class="text-body-secondary small mb-0">
                    A project by <a href="<?= url('about') ?>">Hiswill Kato</a>.
                    Free to use, since <?= e((string) SITE_YEAR) ?>.
                </p>
            </div>

            <div class="col-6 col-lg-3">
                <h2 class="h6 text-uppercase text-body-secondary">Explore</h2>
                <ul class="list-unstyled mb-0">
                    <li><a class="link-body-emphasis text-decoration-none" href="<?= url() ?>">Home</a></li>
                    <li><a class="link-body-emphasis text-decoration-none" href="<?= url('about') ?>">About</a></li>
                    <li><a class="link-body-emphasis text-decoration-none" href="<?= url('contact') ?>">Contact</a></li>
                </ul>
            </div>

            <div class="col-6 col-lg-3">
                <h2 class="h6 text-uppercase text-body-secondary">Legal</h2>
                <ul class="list-unstyled mb-0">
                    <?php foreach ($legal as $page => $label): ?>
                    <li><a class="link-body-emphasis text-decoration-none" href="<?= url($page) ?>"><?= $label ?></a></li>
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

<script src="<?= asset('js/bootstrap.bundle.min.js') ?>" defer></script>
</body>
</html>
