<?php
/* The masthead and the opening of <main>. A page sets $title before requiring
   this file, and that is the only expectation. */
$title = $title ?? SITE_NAME;

/* Sections, in the order a visitor meets them. here() compares the path, so a
   page inside /bible/ lights up the Bible link without naming it here.

   The label and the address are named keys rather than a list, and the loop
   below walks the keys: a part that spread a list into ordinary variables
   would leave the page's own variables holding the last one it read. */
$nav = [
    'index'   => ['label' => 'Home', 'at' => url()],
    'bible'   => ['label' => 'Bible', 'at' => url('bible')],
    'tools'   => ['label' => 'Tools', 'at' => url('tools')],
    'about'   => ['label' => 'About', 'at' => url('about')],
    'contact' => ['label' => 'Contact', 'at' => url('contact')],
];
?>
<!doctype html>
<html lang="en" data-theme="dark" data-bs-theme="dark">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e($title) ?> &middot; <?= e(SITE_NAME) ?></title>
<meta name="description" content="<?= e($description ?? SITE_FULL_NAME . ' — ' . SITE_TAGLINE) ?>">
<meta name="keywords" content="<?= e($keywords ?? SITE_KEYWORDS) ?>">
<meta name="author" content="Hiswill Kato">
<meta name="theme-color" content="#0b0908">
<meta name="theme-color" content="#fdf3ea" media="(prefers-color-scheme: light)">
<?php /* The address as typed, without its query, so /tools/hash and
      /bible/john/3 each name themselves rather than their script. A trailing
      slash is dropped, because /tools and /tools/ are one page. On a project
      subdomain the same address keeps that host, so the project is canonical
      where it actually lives. */ ?>
<link rel="canonical" href="<?= e(current_url()) ?>">
<link rel="icon" href="<?= asset('images/favicon.svg') ?>" type="image/svg+xml">
<script>
/* Settle the theme before the first paint, so the page never flashes the wrong
   colour. A stored choice wins; otherwise the visitor's system decides. The
   `js` class lets the stylesheet hide the reveal blocks only when scripting is
   on to bring them back. */
(function () {
    var root = document.documentElement;
    var theme;

    root.classList.add('js');

    try {
        theme = localStorage.getItem('hiws-theme');
    } catch (error) {
        theme = null;
    }

    if (theme !== 'dark' && theme !== 'light') {
        theme = window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
    }

    root.setAttribute('data-theme', theme);
    root.setAttribute('data-bs-theme', theme);
})();
</script>
<link rel="stylesheet" href="<?= asset('css/bootstrap.min.css') ?>">
<link rel="stylesheet" href="<?= asset('css/all.min.css') ?>">
<link rel="stylesheet" href="<?= asset('css/style.css') ?>">
</head>
<body class="d-flex flex-column min-vh-100">

<a class="visually-hidden-focusable" href="#main">Skip to content</a>

<header class="site-header">
    <nav class="navbar navbar-expand-lg" aria-label="Main">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center gap-2" href="<?= url() ?>">
                <span class="brand-mark"><?= icon('fa-solid fa-layer-group') ?></span>
                <span class="d-flex flex-column">
                    <span class="brand-name"><?= e(SITE_NAME) ?></span>
                    <span class="brand-tagline d-none d-sm-block"><?= e(SITE_FULL_NAME) ?></span>
                </span>
            </a>

            <?= toggle('ms-auto d-lg-none') ?>

            <button class="navbar-toggler border-0 shadow-none" type="button" data-bs-toggle="collapse"
                    data-bs-target="#menu" aria-controls="menu" aria-expanded="false" aria-label="Menu">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="menu">
                <ul class="navbar-nav ms-auto align-items-lg-center gap-lg-1 mt-3 mt-lg-0">
                    <?php foreach (array_keys($nav) as $key): ?>
                    <li class="nav-item">
                        <a class="nav-link<?= here($key) ? ' active' : '' ?>" href="<?= e($nav[$key]['at']) ?>"
                           <?= here($key) ? 'aria-current="page"' : '' ?>><?= e($nav[$key]['label']) ?></a>
                    </li>
                    <?php endforeach; ?>

                    <li class="nav-item d-none d-lg-block ms-lg-2">
                        <?= toggle('btn-sm') ?>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
</header>

<main id="main" class="flex-grow-1">
