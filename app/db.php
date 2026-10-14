<?php
declare(strict_types=1);

/* =========================================================================
   The database connection.

   MySQLi in its object style, the same choice the sibling sites make. One
   connection is opened per request and shared through db(), so a page never
   opens a second one by accident.
   ========================================================================= */

/* Since PHP 8.1 mysqli reports a failed connection by throwing rather than by
   setting connect_errno, so the try/catch is what makes the friendly page
   below reachable instead of a stack trace. */
try {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
} catch (mysqli_sql_exception) {
    $conn = null;
}

if ($conn === null || $conn->connect_errno) {
    /* The reason is deliberately not printed: it names the database account,
       and this page is shown to anyone who asked for a missing address. */
    error_log('HiWS: database connection failed — ' . ($conn->connect_error ?? 'unknown'));

    http_response_code(503);
    exit('<!doctype html><html lang="en"><head><meta charset="utf-8">'
        . '<meta name="viewport" content="width=device-width, initial-scale=1">'
        . '<title>Database problem | ' . SITE_NAME . '</title>'
        . '<style>body{font-family:system-ui,sans-serif;background:#f5f6fb;color:#171a2b;'
        . 'display:flex;min-height:100vh;align-items:center;justify-content:center;padding:24px}'
        . '.box{max-width:620px;background:#fff;border-radius:16px;padding:40px;'
        . 'box-shadow:0 18px 46px rgba(23,26,43,.10)}h1{font-size:24px;margin:0 0 12px}'
        . 'code{background:#f1f2f8;padding:2px 6px;border-radius:6px}a{color:#0d6efd}</style>'
        . '</head><body><div class="box">'
        . '<h1>The database is not reachable</h1>'
        . '<p>' . SITE_NAME . ' could not connect to its database. Check the details in '
        . '<code>app/config.php</code>, make sure MariaDB is running, and import the schema from '
        . '<code>database.sql</code>.</p>'
        . '<p><code>mysql -uroot -proot ' . DB_NAME . ' &lt; database.sql</code></p>'
        . '</div></body></html>');
}

$conn->set_charset('utf8mb4');

/* The one access point to the connection. Pages ask for it rather than
   reaching for the global, so the source of the handle is in one place. */
function db(): mysqli
{
    global $conn;

    return $conn;
}
