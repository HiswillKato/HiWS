<?php
/* The masthead and the opening of <main>. A page sets $title before requiring
   this file, and that is the only expectation. */
$title = $title ?? SITE_NAME;
$lede  = $lede ?? null;

$nav = [
    'index'   => ['Home', url()],
    'about'   => ['About', url('about')],
    'contact' => ['Contact', url('contact')],
];
?>
<!doctype html>
<html lang="en" data-bs-theme="light">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e($title) ?> &middot; <?= e(SITE_FULL_NAME) ?></title>
<meta name="description" content="<?= e(SITE_FULL_NAME . ' — ' . SITE_TAGLINE) ?>">
<meta name="keywords" content="hiws, hiswill web stack, hiswill, free web services, free web projects, web tools, online bible">
<meta name="author" content="Hiswill Kato">
<meta name="theme-color" content="#0d6efd">
<?php $script = basename((string) ($_SERVER['SCRIPT_NAME'] ?? 'index.php'), '.php'); ?>
<link rel="canonical" href="<?= e($script === 'index' ? url() : url($script)) ?>">
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
                <?php foreach ($nav as $page => [$label, $link]): ?>
                <li class="nav-item">
                    <a class="nav-link<?= here($page) ? ' active' : '' ?>" href="<?= e($link) ?>"
                       <?= here($page) ? 'aria-current="page"' : '' ?>><?= e($label) ?></a>
                </li>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>
</nav>

<main id="main" class="flex-grow-1">
