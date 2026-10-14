<?php
declare(strict_types=1);

/* =========================================================================
   Shared helpers. Every function here earns its place by being called from
   more than one page — anything used once lives on that page instead.
   ========================================================================= */

/* -------------------------------------------------------------------------
   1. Text and addresses
   ------------------------------------------------------------------------- */

/* The one escaping point. Anything printed into HTML goes through here, so a
   stray quote in a project title cannot break the page. */
function e(mixed $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

/* An absolute address. With BASE_URL empty the name comes from the request,
   so the site answers to hiws, www.hiws or the real domain alike — but only
   for names on HOSTS, so a forged Host header cannot rewrite our links. */
function url(string $path = ''): string
{
    if (BASE_URL !== '') {
        $base = rtrim(BASE_URL, '/');
    } else {
        $name = trim(preg_replace('~:\d{1,5}$~', '', (string) ($_SERVER['HTTP_HOST'] ?? '')));
        $known = HOSTS === [] || in_array($name, HOSTS, true);

        if ($name === '' || !$known) {
            $name = 'localhost';
        }

        $scheme = (($_SERVER['HTTPS'] ?? '') === 'on' || ($_SERVER['SERVER_PORT'] ?? null) === '443')
            ? 'https'
            : 'http';

        $base = $scheme . '://' . $name;
    }

    return $path === '' ? $base : $base . '/' . ltrim($path, '/');
}

function asset(string $path): string
{
    return url('assets/' . ltrim($path, '/'));
}

/* A Font Awesome icon by its full class name, e.g. icon('fa-solid fa-lock').
   The name is escaped like any other value, so the helper is safe to call
   with a string that came out of the database. */
function icon(string $name): string
{
    return '<i class="' . e($name) . '" aria-hidden="true"></i>';
}

/* A URL-safe version of a title, used for project and tool addresses. */
function slug(string $text): string
{
    return trim(preg_replace('/[^a-z0-9]+/', '-', strtolower(trim($text))) ?? '', '-');
}

/* The first part of a longer piece, cut on a word where one is found. */
function cut(string $text, int $length = 150): string
{
    $text = trim(preg_replace('/\s+/', ' ', strip_tags($text)) ?? '');

    if (mb_strlen($text) <= $length) {
        return $text;
    }

    $short = mb_substr($text, 0, $length);
    $space = mb_strrpos($short, ' ');

    return ($space === false ? $short : mb_substr($short, 0, $space)) . '…';
}

/* Mark the current page in the navigation. Compares the script name, so
   /about and /about.php agree without either being hard-coded. */
function here(string $page): bool
{
    $current = basename((string) ($_SERVER['SCRIPT_NAME'] ?? ''), '.php');

    return $current === $page;
}

/* -------------------------------------------------------------------------
   2. The contact form
   ------------------------------------------------------------------------- */

/* One hidden field, and the matching check. The token lives in the session,
   so a form posted from another site — which has no session — is refused. */
function token(): string
{
    if (empty($_SESSION['token'])) {
        $_SESSION['token'] = bin2hex(random_bytes(16));
    }

    return $_SESSION['token'];
}

function token_field(): string
{
    return '<input type="hidden" name="token" value="' . e(token()) . '">';
}

function token_ok(?string $sent): bool
{
    return is_string($sent) && $sent !== '' && hash_equals(token(), $sent);
}
