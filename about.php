<?php
declare(strict_types=1);

require __DIR__ . '/app/init.php';

$title = 'About Us';

require __DIR__ . '/app/parts/header.php';
?>

<section class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-9">
            <h1 class="mb-4">About <?= e(SITE_NAME) ?></h1>

            <p class="lead">
                <?= e(SITE_FULL_NAME) ?> is where I keep the web projects I build for myself and
                share with anyone who finds them useful. Everything here is free to use.
            </p>

            <p>
                Some tools were written to solve a problem of my own, and you may never need them.
                Others — the online Bible, the file manager, the everyday web tools — are broad
                enough that I hope you do. Take what helps and leave the rest.
            </p>

            <div class="card bg-body rounded-4 border shadow-sm my-5">
                <div class="card-body p-4">
                    <h2 class="h4">About Hiswill</h2>
                    <p class="mb-3">
                        Hey there! I'm Hiswill, a web developer with a passion for crafting simple,
                        fast and useful websites. I have been coding since 2020 and I am always
                        excited to learn something new.
                    </p>
                    <p class="mb-3">
                        I build with HTML, CSS, JavaScript and PHP, and PHP is the one I enjoy most.
                        I am also a student, so I am constantly learning and growing.
                    </p>
                    <p class="mb-3">
                        I believe a website should be both pleasant to look at and easy to use.
                        That is why I keep my projects free for everyone — some of them are web
                        tools, and one of them is an online Bible.
                    </p>
                    <p class="mb-0">
                        I am a God-fearing person and I love working with others. I am open to
                        collaborating, and I keep building and updating, so check back for the
                        latest.
                    </p>
                </div>
            </div>

            <div class="d-flex flex-wrap gap-3">
                <a class="btn btn-primary" href="<?= url('contact') ?>">
                    <?= icon('fa-solid fa-envelope me-1') ?> Get in touch
                </a>
                <a class="btn btn-outline-secondary" href="<?= url() ?>">
                    <?= icon('fa-solid fa-layer-group me-1') ?> See the projects
                </a>
            </div>
        </div>
    </div>
</section>

<?php require __DIR__ . '/app/parts/footer.php'; ?>
