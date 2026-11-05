<?php
declare(strict_types=1);

/* =========================================================================
   sitemap.xml — every page worth indexing, built as it is asked for.

   The address a crawler expects is /sitemap.xml; the root .htaccess turns
   that into this script, so no file has to be written or rebuilt when the
   site grows. What goes into it is read from where the site already keeps
   it: the projects and tools tables, and the books of the Bible — so a new
   tool, or a book's chapters, appear here without this file being touched.

   The main site's own pages are files rather than rows, so nothing can read
   them off a table; the six of them are named below, and a page that goes up
   at the root joins that list.

   No <lastmod>: nothing here knows when a page really changed — a tool's row
   carries no date and neither does a verse. A date invented for every entry
   would be one a crawler learns to ignore, which is worse than no date.
   ========================================================================= */

require __DIR__ . '/app/init.php';

/* The main site's own pages, in the order they are met: the two the
   navigation names, then the three the footer does. 404 is not a page
   anyone should open on purpose, and sitemap.php is this file, not a page
   of its own — neither belongs in the list. */
$flat = ['about', 'contact', 'disclaimer', 'privacy', 'terms'];

$urls = [url()];

foreach ($flat as $page) {
    $urls[] = url($page);
}

/* The three project homes — tools, bible, sifm — read off the cards on the
   home page, so a project hidden from that page (status = 0) leaves the
   sitemap with its row. */
foreach (projects() as $project) {
    $urls[] = url($project['url']);
}

/* Every published tool, on the host that serves it. */
foreach (tools() as $tool) {
    $urls[] = url('tools/' . $tool['url']);
}

/* The Bible: its three other sections, then every book, then every chapter
   of every book. A chapter is what a reader actually opens, so those are the
   addresses that matter; books() already carries how many each book has. */
$urls[] = url('bible/search');
$urls[] = url('bible/facts');
$urls[] = url('bible/jics');

foreach (books(BIBLE_DEFAULT) as $book) {
    $at = address($book);
    $urls[] = $at;

    for ($each = 1; $each <= (int) $book['chapters']; $each++) {
        $urls[] = $at . '/' . $each;
    }
}

header('Content-Type: application/xml; charset=UTF-8');

echo '<?xml version="1.0" encoding="UTF-8"?>' . PHP_EOL;
echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . PHP_EOL;

foreach ($urls as $at) {
    echo '  <url><loc>' . e($at) . '</loc></url>' . PHP_EOL;
}

echo '</urlset>' . PHP_EOL;
