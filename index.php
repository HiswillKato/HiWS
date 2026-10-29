<?php
declare(strict_types=1);

require __DIR__ . '/app/init.php';

$title = 'Simple tools for a faster web';
$description = 'HiWS builds focused, free web projects: practical tools, a readable Bible and SiFM, a secure browser file manager.';
$keywords = 'HiWS, free web tools, SiFM, file manager, Bible, developer tools';
$projects = projects();

require __DIR__ . '/app/parts/header.php';
?>

<section class="container py-5 py-lg-6">
    <div class="row align-items-center g-5">
        <div class="col-lg-7 reveal">
            <p class="eyebrow mb-3"><?= e(SITE_TAGLINE) ?></p>
            <h1 class="display-3 hero-name mb-4">Useful web projects.<br><span class="aurora">No unnecessary friction.</span></h1>
            <p class="lead text-body-secondary mb-4" style="max-width: 42rem">
                HiWS makes small, dependable tools for everyday work and learning. Open them in your browser,
                use what you need, and get on with your day.
            </p>
            <div class="d-flex flex-wrap gap-2">
                <a class="btn btn-primary btn-lg" href="#projects">Explore projects <?= icon('fa-solid fa-arrow-down ms-1') ?></a>
                <a class="btn btn-outline-primary btn-lg" href="<?= url('about') ?>">About HiWS <?= icon('fa-solid fa-arrow-right ms-1') ?></a>
            </div>
        </div>
        <div class="col-lg-5 reveal">
            <div class="p-4 p-lg-5 bg-body rounded-4 border shadow-sm position-relative overflow-hidden">
                <span class="display-1 text-primary opacity-25 position-absolute top-0 end-0 me-3 mt-2"><?= icon('fa-solid fa-sparkles') ?></span>
                <p class="eyebrow mb-4">Built to be opened</p>
                <div class="d-grid gap-3">
                    <div class="d-flex gap-3 align-items-start"><span class="badge bg-primary-subtle text-primary-emphasis rounded-3 p-2"><?= icon('fa-solid fa-bolt') ?></span><span><strong class="d-block">Fast to understand</strong><small class="text-body-secondary">Clear interfaces, focused features.</small></span></div>
                    <div class="d-flex gap-3 align-items-start"><span class="badge bg-primary-subtle text-primary-emphasis rounded-3 p-2"><?= icon('fa-solid fa-shield-halved') ?></span><span><strong class="d-block">Made with care</strong><small class="text-body-secondary">Thoughtful defaults and practical safeguards.</small></span></div>
                    <div class="d-flex gap-3 align-items-start"><span class="badge bg-primary-subtle text-primary-emphasis rounded-3 p-2"><?= icon('fa-solid fa-heart') ?></span><span><strong class="d-block">Free to use</strong><small class="text-body-secondary">Open projects for everyone.</small></span></div>
                </div>
            </div>
        </div>
    </div>
</section>

<section id="projects" class="container pb-5" aria-labelledby="projects-title">
    <div class="d-flex flex-wrap align-items-end justify-content-between gap-2 mb-4">
        <div><p class="eyebrow mb-2">The collection</p><h2 id="projects-title" class="h2 mb-0">Start with a project</h2></div>
        <a class="link-primary text-decoration-none" href="<?= url('contact') ?>">Suggest an idea <?= icon('fa-solid fa-arrow-right') ?></a>
    </div>
    <?php if ($projects === []): ?>
        <div class="alert alert-secondary" role="alert">Projects are being prepared. Check back soon.</div>
    <?php else: ?>
        <div class="row g-4">
            <?php foreach ($projects as $project): ?>
            <div class="col-md-6 col-lg-4">
                <article class="card h-100 shadow-sm reveal">
                    <div class="card-body d-flex flex-column p-4">
                        <span class="badge bg-primary-subtle text-primary-emphasis rounded-3 p-3 mb-4 align-self-start fs-5"><?= mark($project['icon']) ?></span>
                        <h3 class="h5 card-title"><?= e($project['name']) ?></h3>
                        <p class="card-text text-body-secondary flex-grow-1"><?= e($project['description']) ?></p>
                        <a class="btn btn-outline-primary align-self-start" href="<?= e(url($project['url'])) ?>">Open project <?= icon('fa-solid fa-arrow-right ms-1') ?></a>
                    </div>
                </article>
            </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>

<section class="container pb-5">
    <div class="p-4 p-lg-5 rounded-4 border bg-body-tertiary d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3">
        <div><p class="eyebrow mb-2">Have a useful idea?</p><h2 class="h4 mb-1">Good tools start with real problems.</h2><p class="text-body-secondary mb-0">Tell us what would make your work a little easier.</p></div>
        <a class="btn btn-primary" href="<?= url('contact') ?>">Get in touch <?= icon('fa-solid fa-arrow-right ms-1') ?></a>
    </div>
</section>

<?php require __DIR__ . '/app/parts/footer.php'; ?>
