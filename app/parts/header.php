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
<html lang="en" data-bs-theme="light">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e($title) ?> &middot; <?= e(SITE_FULL_NAME) ?></title>
<meta name="description" content="<?= e($description ?? SITE_FULL_NAME . ' — ' . SITE_TAGLINE) ?>">
<meta name="keywords" content="<?= e($keywords ?? SITE_KEYWORDS) ?>">
<meta name="author" content="Hiswill Kato">
<meta name="theme-color" content="#0d6efd">
<?php /* The address as typed, without its query, so /tools/hash and
     /bible/john/3 each name themselves rather than their script. A trailing
     slash is dropped, because /tools and /tools/ are one page. */ ?>
<?php $path = trim((string) strtok((string) ($_SERVER['REQUEST_URI'] ?? '/'), '?'), '/'); ?>
<link rel="canonical" href="<?= e(url($path)) ?>">
<link rel="icon" href="<?= asset('images/favicon.svg') ?>" type="image/svg+xml">
<link rel="stylesheet" href="<?= asset('css/bootstrap.min.css') ?>">
<link rel="stylesheet" href="<?= asset('css/all.min.css') ?>">
<link rel="stylesheet" href="<?= asset('css/style.css') ?>">
</head>
<body class="d-flex flex-column min-vh-100 bg-body-tertiary">

<a class="visually-hidden-focusable p-3 bg-primary text-white" href="#main">Skip to content</a>

<nav class="navbar navbar-expand-lg bg-body border-bottom sticky-top" aria-label="Main">
    <div class="container">
        <a class="navbar-brand d-flex align-items-center gap-2 fw-semibold" href="<?= url() ?>">
            <span class="brand-mark"><?= icon('fa-solid fa-layer-group') ?></span>
            <span class="d-flex flex-column lh-1">
                <span><?= e(SITE_NAME) ?></span>
                <small class="text-body-secondary fw-normal" style="font-size:.7rem"><?= e(SITE_FULL_NAME) ?></small>
            </span>
        </a>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#menu"
                aria-controls="menu" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="menu">
            <ul class="navbar-nav ms-auto">
                <?php foreach (array_keys($nav) as $key): ?>
                <li class="nav-item">
                    <a class="nav-link<?= here($key) ? ' active' : '' ?>" href="<?= e($nav[$key]['at']) ?>"
                       <?= here($key) ? 'aria-current="page"' : '' ?>><?= e($nav[$key]['label']) ?></a>
                </li>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>
</nav>

<main id="main" class="flex-grow-1">
