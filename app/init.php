<?php
declare(strict_types=1);

/* =========================================================================
   START UP — the first line of every page.

   The pieces load in the order they depend on each other: settings, then the
   connection, then the helpers, then the reads. Nothing else is expected of
   a page than to require this one file and then print.
   ========================================================================= */

require __DIR__ . '/config.php';
require __DIR__ . '/db.php';
require __DIR__ . '/functions.php';
require __DIR__ . '/data.php';

error_reporting(SHOW_ERRORS ? E_ALL : 0);
ini_set('display_errors', SHOW_ERRORS ? '1' : '0');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/* One handler, so an unexpected failure becomes a tidy page and one line in
   the log instead of a blank screen. The detail is only shown while
   SHOW_ERRORS is on, which is the local-machine setting. */
set_exception_handler(static function (Throwable $problem): void {
    error_log('HiWS: ' . $problem->getMessage() . ' in ' . $problem->getFile() . ':' . $problem->getLine());

    http_response_code(500);

    $detail = SHOW_ERRORS
        ? '<pre class="small text-start p-3 bg-body-tertiary rounded-3">'
            . e($problem->getMessage() . "\n" . $problem->getFile() . ':' . $problem->getLine())
            . '</pre>'
        : '';

    exit('<!doctype html><html lang="en" data-bs-theme="light"><meta charset="utf-8">'
        . '<title>Something went wrong | ' . SITE_NAME . '</title>'
        . '<link rel="stylesheet" href="' . asset('css/bootstrap.min.css') . '">'
        . '<body class="bg-body"><div class="container py-5" style="max-width:44rem">'
        . '<h1 class="h3">Something went wrong on our side</h1>'
        . '<p class="text-body-secondary">The problem has been logged. Head back to the '
        . '<a href="' . url() . '">home page</a> and try again.</p>'
        . $detail
        . '</div></body></html>');
});
