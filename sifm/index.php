<?php
declare(strict_types=1);

require __DIR__ . '/../app/init.php';

$title = 'SiFM — Simple File Manager';
$description = 'SiFM is an open-source, web-based PHP file manager: browse, upload, edit, '
    . 'download, copy, move, rename and delete files from one simple interface.';
$keywords = 'sifm, simple file manager, php file manager, web file manager, open source, mit, hiswill kato';

/* The manager itself and the project it comes from. The app lives beside this
   page rather than behind it: the root of the host is the project's home, and
   /sifm.php is the tool. */
$app = url('sifm/sifm.php');
$source = 'https://github.com/HiswillKato/SiFM';

/* The feature list is the README's, kept in its two groups. Each row is an
   icon, a heading and one line saying what the feature does. */
$groups = [
    'File management' => [
        ['fa-folder-open', 'View files and folders', 'Browse directories with size, permissions and last-modified dates at a glance.'],
        ['fa-folder-plus', 'Create files and folders', 'Add new files or folders wherever they belong, to keep a structure organised.'],
        ['fa-file-pen', 'Edit files', 'Change text files in the browser using the built-in editor, with syntax highlighting.'],
        ['fa-upload', 'Upload files', 'Send files from your computer to the server, one at a time or several at once.'],
        ['fa-download', 'Download files', 'Grab a single file, or select several and download them together as a ZIP archive.'],
        ['fa-copy', 'Copy, move and rename', 'Duplicate, relocate or rename files and folders without leaving the manager.'],
        ['fa-trash-can', 'Delete files and folders', 'Remove anything after a confirmation step. Deletion is permanent.'],
    ],
    'Advanced features' => [
        ['fa-key', 'Permissions management', 'Change file and folder permissions (chmod) to control access on shared servers.'],
        ['fa-box-archive', 'Backup and restore', 'Create ZIP backups of files and folders for protection against mistakes.'],
        ['fa-file-zipper', 'Extract files', 'Unzip archives and reach their contents without a separate tool.'],
        ['fa-magnifying-glass', 'Search', 'Find files and folders quickly by keyword or wildcard, even in large collections.'],
        ['fa-code', 'PHP syntax checking', 'Check the syntax of PHP files and catch errors directly in the interface.'],
        ['fa-circle-info', 'File information', 'See size, MIME type, modification date and ownership for any file.'],
    ],
];

/* The README's installation steps, in the order a new user meets them. */
$steps = [
    ['Download the script', 'Get sifm.php from the project on GitHub.'],
    ['Put it on a server', 'Place it in the document root of a PHP-capable web server, or in any folder a browser can reach.'],
    ['Edit the settings', 'Open the file and adjust the constants near the top: name, version, administrator credentials and the size limits.'],
    ['Open it in a browser', 'Visit sifm.php on that server and sign in.'],
];

require __DIR__ . '/../app/parts/header.php';
?>

<section class="container py-5">
    <div class="p-4 p-lg-5 mb-5 bg-body rounded-4 border shadow-sm reveal">
        <div class="row align-items-center g-4">
            <div class="col-lg-8">
                <p class="eyebrow mb-2">Open source &middot; MIT</p>
                <h1 class="display-4 hero-name mb-3">SiFM <span class="aurora">Simple File Manager</span></h1>
                <p class="lead mb-3">
                    SiFM is an open-source, web-based PHP file manager for managing files and folders
                    through a simple interface. It is lightweight, user-friendly and versatile &mdash;
                    built for developers and everyday users alike.
                </p>
                <div class="d-flex flex-wrap gap-2">
                    <a class="btn btn-primary btn-lg" href="<?= e($app) ?>">
                        Open the file manager <?= icon('fa-solid fa-arrow-right ms-1') ?>
                    </a>
                    <a class="btn btn-outline-primary btn-lg" href="<?= e($source) ?>"
                       rel="noopener noreferrer" target="_blank">
                        <?= icon('fa-brands fa-github me-1') ?> View on GitHub
                    </a>
                </div>
                <p class="text-body-secondary small mt-3 mb-0">
                    One PHP file &middot; no database &middot; free under the MIT licence
                </p>
            </div>
            <div class="col-lg-4 text-lg-end d-none d-lg-block">
                <span class="display-1 text-primary opacity-25"><?= icon('fa-solid fa-folder-tree') ?></span>
            </div>
        </div>
    </div>

    <?php foreach ($groups as $label => $features): ?>
        <div class="mb-5">
            <h2 class="h6 text-uppercase text-body-secondary border-bottom pb-2 mb-3"><?= e($label) ?></h2>

            <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-3">
                <?php foreach ($features as [$featureIcon, $featureTitle, $featureText]): ?>
                    <div class="col">
                        <div class="card h-100 bg-body rounded-4 border shadow-sm">
                            <div class="card-body d-flex gap-3">
                                <span class="badge bg-primary-subtle text-primary-emphasis rounded-3 p-2 align-self-start fs-5">
                                    <?= mark($featureIcon) ?>
                                </span>
                                <span>
                                    <span class="h6 d-block mb-1"><?= e($featureTitle) ?></span>
                                    <span class="text-body-secondary small d-block"><?= e($featureText) ?></span>
                                </span>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endforeach; ?>

    <div class="row g-4 mb-5">
        <div class="col-lg-7">
            <h2 class="h5 mb-3">Run it on your own server</h2>
            <ol class="list-group list-group-numbered rounded-4 border shadow-sm overflow-hidden">
                <?php foreach ($steps as [$stepTitle, $stepText]): ?>
                    <li class="list-group-item bg-body d-flex gap-3 py-3">
                        <span class="ms-2">
                            <span class="d-block fw-semibold"><?= e($stepTitle) ?></span>
                            <span class="text-body-secondary small"><?= e($stepText) ?></span>
                        </span>
                    </li>
                <?php endforeach; ?>
            </ol>
        </div>

        <div class="col-lg-5">
            <div class="card h-100 bg-body rounded-4 border shadow-sm">
                <div class="card-body p-4">
                    <h2 class="h5 mb-3"><?= icon('fa-solid fa-circle-info text-primary me-2') ?>About the project</h2>
                    <p class="text-body-secondary">
                        SiFM is written and maintained by <strong>Hiswill Kato</strong>, and is free to
                        use, modify and redistribute under the MIT licence. Questions, feedback and
                        contributions are welcome.
                    </p>
                    <ul class="list-unstyled d-grid gap-2 mb-4">
                        <li>
                            <?= icon('fa-solid fa-envelope text-body-secondary me-2') ?>
                            <a href="mailto:hiswillkato@gmail.com">hiswillkato@gmail.com</a>
                        </li>
                        <li>
                            <?= icon('fa-brands fa-github text-body-secondary me-2') ?>
                            <a href="<?= e($source) ?>" rel="noopener noreferrer" target="_blank">HiswillKato/SiFM</a>
                        </li>
                        <li>
                            <?= icon('fa-solid fa-scale-balanced text-body-secondary me-2') ?>
                            MIT licence
                        </li>
                    </ul>
                    <a class="btn btn-outline-primary" href="<?= e($app) ?>">
                        Launch SiFM <?= icon('fa-solid fa-arrow-right ms-1') ?>
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

<?php require __DIR__ . '/../app/parts/footer.php';
